#!/bin/bash

################################################################################
# UPRM VoIP Monitoring System - Installation Script
# 
# This script automates the setup and installation of the UPRM VoIP Monitoring
# System on Ubuntu servers.
#
# Requirements:
# - Ubuntu 20.04+ / Debian 11+
# - Root or sudo access
# - Internet connection
#
# Usage:
#   sudo bash install.sh
#
# Author: UPRM VoIP Monitoring System Team
# Date: November 2025
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration Variables
APP_DIR="/var/www/uprm_voip_monitoring_system"
WEB_USER="www-data"
PHP_VERSION="8.3"
NODE_VERSION="20"
DB_NAME="voip_monitoring"
DB_USER="voip_app"
DB_PASSWORD=""

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "This script must be run as root or with sudo"
        exit 1
    fi
}

prompt_yn() {
    local message="$1"
    local default="${2:-n}"
    
    if [[ "$default" == "y" ]]; then
        read -p "$message [Y/n]: " response
        response=${response:-y}
    else
        read -p "$message [y/N]: " response
        response=${response:-n}
    fi
    
    [[ "$response" =~ ^[Yy]$ ]]
}

generate_password() {
    openssl rand -base64 16 | tr -d "=+/" | cut -c1-16
}

################################################################################
# Installation Steps
################################################################################

step_0_welcome() {
    clear
    print_header "📞 UPRM VoIP Monitoring System - Installation"
    
    cat << "EOF"
    ╔═══════════════════════════════════════════════════════════════╗
    ║           UPRM VoIP Monitoring System Installer              ║
    ║                                                               ║
    ║  This script will install and configure:                     ║
    ║  • PHP 8.3 with required extensions                          ║
    ║  • MariaDB 11.x                                              ║
    ║  • Node.js 20.x & NPM                                        ║
    ║  • Composer                                                  ║
    ║  • Laravel application dependencies                          ║
    ║  • Automated cron jobs for ETL & notifications               ║
    ║  • Apache web server (optional)                              ║
    ╚═══════════════════════════════════════════════════════════════╝
EOF
    
    echo ""
    if ! prompt_yn "Continue with installation?" "y"; then
        print_info "Installation cancelled."
        exit 0
    fi
}

step_1_check_system() {
    print_header "STEP 1: System Requirements Check"
    
    # Check OS
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release
        print_info "Detected OS: $NAME $VERSION"
        
        if [[ "$ID" != "ubuntu" ]] && [[ "$ID" != "debian" ]]; then
            print_warning "This script is designed for Ubuntu/Debian. Proceed with caution."
            if ! prompt_yn "Continue anyway?"; then
                exit 1
            fi
        fi
    fi
    
    # Check internet connection
    if ping -c 1 google.com &> /dev/null; then
        print_success "Internet connection verified"
    else
        print_error "No internet connection detected"
        exit 1
    fi
    
    # Check available disk space (need at least 2GB)
    available_space=$(df / | awk 'NR==2 {print $4}')
    if [[ $available_space -lt 2097152 ]]; then
        print_warning "Low disk space detected (less than 2GB available)"
    else
        print_success "Sufficient disk space available"
    fi
}

step_2_install_dependencies() {
    print_header "STEP 2: Installing System Dependencies"
    
    print_info "Updating package lists..."
    apt-get update -qq
    
    print_info "Installing base packages..."
    apt-get install -y -qq \
        software-properties-common \
        curl \
        wget \
        git \
        unzip \
        zip \
        ca-certificates \
        apt-transport-https \
        gnupg \
        lsb-release \
        supervisor \
        cron
    
    print_success "System dependencies installed"
}

step_3_install_php() {
    print_header "STEP 3: Installing PHP $PHP_VERSION"
    
    # Add PHP repository
    if ! grep -q "ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/*; then
        print_info "Adding PHP repository..."
        add-apt-repository ppa:ondrej/php -y
        apt-get update -qq
    fi
    
    print_info "Installing PHP and extensions..."
    apt-get install -y -qq \
        php${PHP_VERSION} \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-redis
    
    # Verify PHP installation
    if command -v php &> /dev/null; then
        PHP_INSTALLED_VERSION=$(php -r "echo PHP_VERSION;")
        print_success "PHP $PHP_INSTALLED_VERSION installed"
    else
        print_error "PHP installation failed"
        exit 1
    fi
}

step_4_install_composer() {
    print_header "STEP 4: Installing Composer"
    
    if command -v composer &> /dev/null; then
        print_info "Composer already installed, updating..."
        composer self-update --quiet
    else
        print_info "Downloading and installing Composer..."
        EXPECTED_CHECKSUM="$(wget -q -O - https://composer.github.io/installer.sig)"
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
        
        if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
            print_error "Invalid Composer installer checksum"
            rm composer-setup.php
            exit 1
        fi
        
        php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
        rm composer-setup.php
    fi
    
    if command -v composer &> /dev/null; then
        COMPOSER_VERSION=$(composer --version --no-ansi | grep -oP '\d+\.\d+\.\d+' | head -1)
        print_success "Composer $COMPOSER_VERSION installed"
    else
        print_error "Composer installation failed"
        exit 1
    fi
}

step_5_install_nodejs() {
    print_header "STEP 5: Installing Node.js $NODE_VERSION"
    
    if ! command -v node &> /dev/null || [[ $(node -v | cut -d'v' -f2 | cut -d'.' -f1) -lt $NODE_VERSION ]]; then
        print_info "Installing Node.js $NODE_VERSION..."
        curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -
        apt-get install -y -qq nodejs
    else
        print_info "Node.js already installed"
    fi
    
    if command -v node &> /dev/null; then
        NODE_INSTALLED_VERSION=$(node -v)
        NPM_INSTALLED_VERSION=$(npm -v)
        print_success "Node.js $NODE_INSTALLED_VERSION installed"
        print_success "NPM $NPM_INSTALLED_VERSION installed"
    else
        print_error "Node.js installation failed"
        exit 1
    fi
}

step_6_install_mariadb() {
    print_header "STEP 6: Installing MariaDB"
    
    if command -v mariadb &> /dev/null || command -v mysql &> /dev/null; then
        print_info "MariaDB/MySQL already installed"
    else
        print_info "Installing MariaDB server..."
        apt-get install -y -qq mariadb-server mariadb-client
        systemctl start mariadb
        systemctl enable mariadb
    fi
    
    if systemctl is-active --quiet mariadb || systemctl is-active --quiet mysql; then
        print_success "MariaDB service is running"
    else
        print_error "MariaDB service failed to start"
        exit 1
    fi
}

step_7_setup_database() {
    print_header "STEP 7: Configuring Database"
    
    # Generate database password if not set
    if [[ -z "$DB_PASSWORD" ]]; then
        DB_PASSWORD=$(generate_password)
        print_info "Generated database password: $DB_PASSWORD"
    fi
    
    print_info "Creating database and user..."
    
    # Create database and user
    mariadb -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
    mariadb -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';" 2>/dev/null || true
    mariadb -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';" 2>/dev/null || true
    mariadb -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    
    # Test connection
    if mariadb -u"${DB_USER}" -p"${DB_PASSWORD}" -e "USE ${DB_NAME};" 2>/dev/null; then
        print_success "Database '$DB_NAME' created and accessible"
    else
        print_error "Database connection test failed"
        exit 1
    fi
}

step_8_setup_application() {
    print_header "STEP 8: Setting Up Laravel Application"
    
    # Check if app directory exists
    if [[ ! -d "$APP_DIR" ]]; then
        print_error "Application directory not found: $APP_DIR"
        print_info "Please clone the repository to $APP_DIR first"
        exit 1
    fi
    
    cd "$APP_DIR"
    
    # Create .env file if it doesn't exist
    if [[ ! -f .env ]]; then
        print_info "Creating .env file..."
        if [[ -f .env.example ]]; then
            cp .env.example .env
        else
            print_error ".env.example file not found"
            exit 1
        fi
    fi
    
    # Update .env with database credentials
    print_info "Configuring environment variables..."
    sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=mariadb/" .env
    sed -i "s/^DB_HOST=.*/DB_HOST=localhost/" .env
    sed -i "s/^DB_PORT=.*/DB_PORT=3306/" .env
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
    
    # Install PHP dependencies
    print_info "Installing PHP dependencies (this may take a few minutes)..."
    sudo -u $WEB_USER composer install --no-interaction --prefer-dist --optimize-autoloader --quiet
    
    # Generate application key
    if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=base64:" .env | grep -q "base64:$"; then
        print_info "Generating application key..."
        php artisan key:generate --force
    fi
    
    # Install Node.js dependencies
    print_info "Installing Node.js dependencies..."
    sudo -u $WEB_USER npm install --silent
    
    # Build assets
    print_info "Building frontend assets..."
    sudo -u $WEB_USER npm run build
    
    print_success "Application dependencies installed"
}

step_9_run_migrations() {
    print_header "STEP 9: Running Database Migrations"
    
    cd "$APP_DIR"
    
    print_info "Running migrations..."
    php artisan migrate --force
    
    print_success "Database migrations completed"
}

step_10_set_permissions() {
    print_header "STEP 10: Setting File Permissions"
    
    cd "$APP_DIR"
    
    print_info "Setting ownership to $WEB_USER..."
    chown -R $WEB_USER:$WEB_USER "$APP_DIR"
    
    print_info "Setting directory permissions..."
    find "$APP_DIR" -type d -exec chmod 755 {} \;
    find "$APP_DIR" -type f -exec chmod 644 {} \;
    
    print_info "Setting storage and cache permissions..."
    chmod -R 775 storage bootstrap/cache
    chown -R $WEB_USER:$WEB_USER storage bootstrap/cache
    
    print_success "Permissions configured"
}

step_11_setup_cron() {
    print_header "STEP 11: Setting Up Cron Jobs"
    
    # Check if cron entry already exists
    if sudo -u $WEB_USER crontab -l 2>/dev/null | grep -q "schedule:run"; then
        print_info "Cron job already exists"
    else
        print_info "Installing Laravel scheduler cron job..."
        
        # Add cron entry
        (sudo -u $WEB_USER crontab -l 2>/dev/null; echo "* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1") | sudo -u $WEB_USER crontab -
        
        print_success "Cron job installed"
    fi
    
    # Ensure cron service is running
    systemctl enable cron
    systemctl start cron
    
    print_success "Cron service enabled"
}

step_12_optimize() {
    print_header "STEP 12: Optimizing Application"
    
    cd "$APP_DIR"
    
    print_info "Caching configuration..."
    php artisan config:cache
    
    print_info "Caching routes..."
    php artisan route:cache
    
    print_info "Caching views..."
    php artisan view:cache
    
    print_success "Application optimized"
}

step_13_install_apache() {
    print_header "STEP 13: Web Server Configuration (Optional)"
    
    if prompt_yn "Install and configure Apache?"; then
        if ! command -v apache2 &> /dev/null; then
            print_info "Installing Apache..."
            apt-get install -y -qq apache2 libapache2-mod-php${PHP_VERSION}
        fi
        
        # Enable required Apache modules
        print_info "Enabling Apache modules..."
        a2enmod rewrite
        a2enmod ssl
        a2enmod headers
        
        # Create Apache configuration
        print_info "Creating Apache configuration..."
        
        read -p "Enter domain name (or press Enter for localhost): " domain_name
        domain_name=${domain_name:-localhost}
        
        cat > /etc/apache2/sites-available/voip-monitoring.conf << EOF
<VirtualHost *:80>
    ServerName ${domain_name}
    ServerAdmin webmaster@${domain_name}
    DocumentRoot ${APP_DIR}/public

    <Directory ${APP_DIR}/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    # Logging
    ErrorLog \${APACHE_LOG_DIR}/voip-monitoring-error.log
    CustomLog \${APACHE_LOG_DIR}/voip-monitoring-access.log combined

    # Hide server information
    ServerSignature Off

    # PHP Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php${PHP_VERSION}-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
EOF
        
        # Enable site and disable default
        a2ensite voip-monitoring.conf
        a2dissite 000-default.conf
        
        # Enable proxy modules for PHP-FPM
        a2enmod proxy_fcgi setenvif
        a2enconf php${PHP_VERSION}-fpm
        
        # Test and reload Apache
        if apache2ctl configtest &> /dev/null; then
            systemctl enable apache2
            systemctl restart apache2
            print_success "Apache configured and running"
            print_info "Application accessible at: http://${domain_name}"
        else
            print_error "Apache configuration test failed"
        fi
    else
        print_info "Skipping Apache installation"
    fi
}

step_14_final_checks() {
    print_header "STEP 14: Final System Checks"
    
    cd "$APP_DIR"
    
    # Check PHP version
    php_version=$(php -r "echo PHP_VERSION;")
    print_info "PHP Version: $php_version"
    
    # Check database connection
    if php artisan db:show &> /dev/null; then
        print_success "Database connection: OK"
    else
        print_warning "Database connection: Failed (check .env configuration)"
    fi
    
    # Check scheduled tasks
    print_info "Scheduled tasks:"
    php artisan schedule:list 2>/dev/null || print_warning "Could not list scheduled tasks"
    
    # Check cron job
    if sudo -u $WEB_USER crontab -l 2>/dev/null | grep -q "schedule:run"; then
        print_success "Cron job: Installed"
    else
        print_warning "Cron job: Not installed"
    fi
    
    # Check web server
    if systemctl is-active --quiet apache2; then
        print_success "Web server: Running (Apache)"
    elif systemctl is-active --quiet nginx; then
        print_success "Web server: Running (Nginx)"
    else
        print_info "Web server: Not detected"
    fi
}

step_15_create_admin() {
    print_header "STEP 15: Create Admin User (Optional)"
    
    if prompt_yn "Create an admin user?"; then
        cd "$APP_DIR"
        
        read -p "Admin name: " admin_name
        read -p "Admin email: " admin_email
        read -sp "Admin password: " admin_password
        echo
        
        # Create admin user via tinker
        php artisan tinker --execute="
            \$user = new App\Models\User();
            \$user->name = '$admin_name';
            \$user->email = '$admin_email';
            \$user->password = Hash::make('$admin_password');
            \$user->role = 'super_admin';
            \$user->save();
            echo 'Admin user created successfully';
        "
        
        print_success "Admin user created"
    fi
}

installation_complete() {
    clear
    print_header "✅ Installation Complete!"
    
    cat << EOF

╔════════════════════════════════════════════════════════════════╗
║                    Installation Summary                        ║
╠════════════════════════════════════════════════════════════════╣
║  Application Path: ${APP_DIR}
║  Database Name:    ${DB_NAME}
║  Database User:    ${DB_USER}
║  Database Pass:    ${DB_PASSWORD}
║
║  PHP Version:      $(php -r "echo PHP_VERSION;")
║  Composer:         $(composer --version --no-ansi | grep -oP '\d+\.\d+\.\d+' | head -1)
║  Node.js:          $(node -v)
║  NPM:              $(npm -v)
╚════════════════════════════════════════════════════════════════╝

$(print_info "Save the database credentials in a secure location!")

Next Steps:
-----------
1. Update .env file with additional configurations:
   $(print_info "MAIL_* settings for email notifications")
   $(print_info "APP_URL with your domain name")

2. Test the application:
   $(print_info "php artisan serve")
   $(print_info "Visit: http://localhost:8000")

3. Run ETL process:
   $(print_info "php artisan etl:run --import=/path/to/extracted/data")

4. Check scheduled tasks:
   $(print_info "php artisan schedule:list")

5. Monitor logs:
   $(print_info "tail -f storage/logs/laravel.log")

Documentation:
--------------
  • ETL & Cron Setup:  docs/CRON_SETUP.md
  • Email Alerts:      docs/EMAIL_NOTIFICATIONS.md
  • File Cleanup:      docs/FILE_CLEANUP_POLICY.md
  • Reports:           docs/REPORTS_FUNCTIONALITY.md

For support, visit: https://github.com/Gadiel99/uprm_voip_monitoring_system

EOF
}

################################################################################
# Main Installation Flow
################################################################################

main() {
    check_root
    
    step_0_welcome
    step_1_check_system
    step_2_install_dependencies
    step_3_install_php
    step_4_install_composer
    step_5_install_nodejs
    step_6_install_mariadb
    step_7_setup_database
    step_8_setup_application
    step_9_run_migrations
    step_10_set_permissions
    step_11_setup_cron
    step_12_optimize
    step_13_install_apache
    step_14_final_checks
    step_15_create_admin
    
    installation_complete
}

# Run main installation
main "$@"
