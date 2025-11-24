#!/bin/bash

################################################################################
# UPRM VoIP Monitoring System - Installation Script
# 
# This script automates the setup and installation of the UPRM VoIP Monitoring
# System on Ubuntu servers.
#
# Requirements:
# - Ubuntu 24.04+ / Debian 11+
# - Internet connection
#
# Usage:
#   sudo bash install.sh
#
# Author: TRIATEK TEAM
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
APP_DIR="/var/www/voip_mon"
USER="$(whoami)"
WEB_USER="www-data"
PHP_VERSION="8.3"
DB_NAME="voip_monitoring"
DB_USER="voip_app"
DB_PASSWORD="admin"

#SipXcom server varaible
SIPXCOM_SERVER="sipxcom.uprm.edu"

#Postfix variables
DOMAIN="uprm.edu"
RELAY="[mail2.uprm.edu]"
MYHOST="${SUBDOMINIO}.${DOMAIN}"
MYDOMAIN="${DOMAIN}"
MYORIGIN="${MYDOMAIN}"

# SSH Key Variables
SSH_DIR="/var/www/.ssh"
SSH_KEY="${SSH_DIR}/id_rsa_voip_auto"
REMOTE_USER="estudiante"   
REMOTE_HOST="sipxcom.uprm.edu"   

# Backup Directory
BACKUP_DIR="/var/backups/monitoring"


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

################################################################################
# Installation Steps
################################################################################

step_0_welcome() {
    clear
    print_header "📞 UPRM VoIP Monitoring System - Installation"
    
    cat << "EOF"
    ╔═══════════════════════════════════════════════════════════════╗
    ║           UPRM VoIP Monitoring System Installer               ║
    ║                                                               ║
    ║  This script will install and configure:                      ║
    ║  • PHP 8.3 with required extensions                           ║
    ║  • MariaDB 11.x                                               ║
    ║                                                               ║
    ║  • Composer                                                   ║
    ║  • Laravel application dependencies                           ║
    ║  • Automated cron jobs for ETL & notifications                ║
    ║  • Apache web server (optional)                               ║
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

step_2_install_apache(){
    print_header "STEP 2: Installing Apache2"

    sudo apt-get install apache2
    sudo ufw app list
    sudo ufw allow 'Apache Full'
    if sudo ufw status | grep -q "Status: active"; then
        print_success "Apache2 installed and firewall configured"
    else
        sudo ufw enable
        print_success "Apache2 installed and firewall enabled"
    fi

    print_success "Apache2 installation complete"

}

step_3_install_mariadb() {
    print_header "STEP 3: Installing MariaDB"

    if command -v mariadb &> /dev/null || command -v mysql &> /dev/null; then
        print_info "MariaDB/MySQL already installed"
    else
        print_info "Installing MariaDB server..."
        apt-get install mariadb-server mariadb-client
        sudo mariadb-secure-installation
        print_success "MariaDB installation complete"
    fi

}

step_4_install_php_extensions() {
    print_header "STEP 4: Installing PHP $PHP_VERSION Extensions"
    
    print_info "Installing PHP and extensions..."

    sudo apt install php libapache2-mod-php 
    apt-get install\
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
    
    print_success "PHP $PHP_VERSION and extensions installed"
}

step_5_clone_repository() {
    print_header "STEP 5: Cloning Application Repository"
    
     # Check if app directory exists, if not clone it
    if [[ ! -d "$APP_DIR" ]]; then
        print_info "Application directory not found, cloning repository..."
        
        #Create application directory
        sudo mkdir -p $APP_DIR

        print_info "Setting ownership to $USER..."
        chown $USER:$USER $APP_DIR

        read -p "Enter GitHub repository URL [https://github.com/Gadiel99/uprm_voip_monitoring_system.git]: " repo_url
        repo_url=${repo_url:-https://github.com/Gadiel99/uprm_voip_monitoring_system.git} 

        
        # Clone repository
        if git clone "$repo_url" "$APP_DIR"; then
            print_success "Repository cloned successfully"
        else
            print_error "Failed to clone repository"
            exit 1
        fi
    else
        print_info "Application directory already exists"
    fi
}

step_6_configure_apache(){
    print_header "STEP 6: Configuring Apache2 for Laravel Application"
    
    # Create Apache config file
    cat > /etc/apache2/sites-available/voip-mon.conf << EOF
<VirtualHost *:80> << 'EOF'
    ServerName voipmonitor.uprm.edu
    ServerAdmin webmaster@uprm.edu
    DocumentRoot /var/www/voip_mon/public
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined

</VirtualHost>

<Directory /var/www/voip_mon/public>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
    sudo a2enmod rewrite
    sudo a2ensite voip-mon
    sudo a2dissite 000-default
    sudo systemctl reload apache2

 print_success "Apache configured (HTTP only - HTTPS will be configured after SSL certificate)"

}

step_7_configure_mariadb(){
    print_header "STEP 7: Configuring Database"
    
    # Check if database already exists and is accessible
    if mariadb -e "USE ${DB_NAME};" 2>/dev/null; then
        print_info "Database '${DB_NAME}' already exists"
        
        # Try to get existing user password from .env if it exists
        if [[ -f "$APP_DIR/.env" ]] && grep "^DB_PASSWORD=" "$APP_DIR/.env"; then
            
            existing_password=$(grep "^DB_PASSWORD=" "$APP_DIR/.env" | cut -d '=' -f2)
            
            if [[ -n "$existing_password" ]]; then
                DB_PASSWORD="$existing_password"
                print_info "Using existing database password from .env"
            fi
            
        fi
        
        # Test connection with existing credentials
        if [[ -n "$DB_PASSWORD" ]] && mariadb -u"${DB_USER}" -p"${DB_PASSWORD}" -e "USE ${DB_NAME} -h 127.0.0.1;" 2>/dev/null; then
            print_success "Database connection verified with existing credentials"
            return 0
        fi
    fi
    
    # Generate database password if not set
    if [[ -z "$DB_PASSWORD" ]]; then
        DB_PASSWORD=$(generate_password)
        print_info "Generated database password: $DB_PASSWORD"
    fi
    
    print_info "Creating database and user..."
    
    # Create database and user (IF NOT EXISTS handles re-runs)
    mariadb -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};" 2>/dev/null || true
    mariadb -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASSWORD}';" 2>/dev/null || true
    mariadb -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'127.0.0.1';" 2>/dev/null || true
    mariadb -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    
    # Test connection
    if mariadb -u"${DB_USER}" -p"${DB_PASSWORD}" -e "USE ${DB_NAME} -h 127.0.0.1; " 2>/dev/null; then
        print_success "Database '$DB_NAME' created and accessible"
    else
        print_error "Database connection test failed"
        exit 1
    fi
}

step_8_install_composer() {
    print_header "STEP 8: Installing Composer"

    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
        print_error "Invalid installer checksum"
        rm composer-setup.php
        exit 1
    fi

    php composer-setup.php 
    RESULT=$?
    rm composer-setup.php

    if [ $RESULT -ne 0 ]; then
        print_error "Composer installer failed"
        exit $RESULT
    fi

    # Move composer to a system path
    if command -v mv &>/dev/null; then
        mv composer.phar /usr/local/bin/composer 2>/dev/null || cp composer.phar /usr/local/bin/composer
        chmod +x /usr/local/bin/composer 2>/dev/null || true
    fi

    print_success "Composer installed"
}

step_9_setup_application() {
    print_header "STEP 9: Setting Up Laravel Application"
    
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
    sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" .env
    sed -i "s/^DB_PORT=.*/DB_PORT=3306/" .env
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env

    # Set ownership after dependencies are installed
    print_info "Setting directory ownership to $WEB_USER..."
    chown -R $WEB_USER:$WEB_USER "$APP_DIR"

    # Install PHP dependencies (run as root first to create vendor directory)
    print_info "Installing PHP dependencies (this may take a few minutes)..."
    composer install --optimize-autoloader
    
    chgrp -R $WEB_USER storage
    sudo chgrp -R $WEB_USER storage
    sudo chgrp -R $WEB_USER bootstrap/cache/

    print_success "Application dependencies installed"
}

step_10_run_migrations() {
    print_header "STEP 10: Preparing Database and Running Migrations with Seed"
    cd "$APP_DIR"
    print_info "Running migrations and seeding database..."
    php artisan migrate --seed 
    print_success "Database migrations and seeding completed"
}

step_11_install_postfix_and_setup() {
    print_header "STEP 11: Installing Postfix for Email Notifications"
    
    if command -v postfix &> /dev/null; then
        print_info "Postfix already installed"
    else
        print_info "Installing Postfix..."
        sudo apt install postfix
        print_success "Postfix installed"
    fi

    echo "Configuring Postfix:"
    echo "  myhostname = ${MYHOST}"
    echo "  mydomain   = ${MYDOMAIN}"
    echo "  myorigin   = ${MYORIGIN}"
    echo "  relayhost  = ${RELAY}"

    # Set values idempotently
    postconf -e "myhostname = ${MYHOST}"
    postconf -e "mydomain = ${MYDOMAIN}"
    postconf -e "myorigin = ${MYORIGIN}"
    postconf -e "relayhost = ${RELAY}"
    
    # Restart Postfix to apply changes
    systemctl restart postfix

    print_info "Configuring Postfix..."
}

step_12_install_certbot() {
    print_header "STEP 12: Installing Certbot for SSL"
    
    if command -v certbot &> /dev/null; then
        print_info "Certbot already installed"
    else
        print_info "Installing Certbot..."
        sudo snap install --classic certbot
        print_success "Certbot installed"
    fi

    print_info "Obtaining SSL certificate for voipmonitor.uprm.edu..."
    sudo ln -s /snap/bin/certbot /usr/bin/certbot
    sudo certbot --apache
}


step_13_setup_cron() {
    print_header "STEP 13: Setting Up Cron Jobs"
    
    # Make script executable
    sudo chmod +x /var/www/voip_mon/scripts/auto-import-voip-cron.sh

    # Ensure log directory exists and is owned by www-data
    sudo mkdir -p /var/www/voip_mon/storage/logs
    sudo chown -R www-data:www-data /var/www/voip_mon/storage/logs

    # Install the crontab entry for www-data (runs every 5 minutes)
    ( sudo crontab -u www-data -l 2>/dev/null; \
    echo "*/5 * * * * /var/www/voip_mon/scripts/auto-import-voip-cron.sh >> /var/www/voip_mon/storage/logs/auto-import-cron.log 2>&1" ) \
    | sudo crontab -u $WEB_USER -

    # Ensure cron is enabled and running
    sudo systemctl enable --now cron
    sudo systemctl restart cron

    # Verify the entry
    sudo -u $WEB_USER crontab -l
    
    print_success "Cron service enabled"
}

step_14_setup_ssh_key(){
    print_header "STEP 14: Setting up SSH key for auto-import"

    # Ensure ssh dir exists with correct perms
    mkdir -p "$SSH_DIR"
    chown $WEB_USER:$WEB_USER "$SSH_DIR"
    chmod 700 "$SSH_DIR"

    # Create key if missing (run ssh-keygen as web user so ownership is correct)
    if [[ -f "$SSH_KEY" ]]; then
        print_info "SSH key already exists: $SSH_KEY"
    else
        print_warning "Generating 4096-bit RSA key... This may take 30-60 seconds"
        print_info "Generating SSH key at $SSH_KEY (no passphrase)"
        sudo -u $WEB_USER ssh-keygen -t rsa -b 4096 -N "" -f "$SSH_KEY" || { print_error "ssh-keygen failed"; return 1; }
        chown $WEB_USER:$WEB_USER "$SSH_KEY" "$SSH_KEY.pub"
        chmod 600 "$SSH_KEY"
        chmod 644 "$SSH_KEY.pub"
        print_success "SSH key created"
    fi

    # If remote target provided, try to install public key (will prompt for remote password)
    if [[ -n "$REMOTE_USER" && -n "$REMOTE_HOST" ]]; then
        if command -v ssh-copy-id &>/dev/null; then
            print_info "Copying public key to ${REMOTE_USER}@${REMOTE_HOST} using ssh-copy-id"
            sudo -u $WEB_USER ssh-copy-id -i "${SSH_KEY}.pub" -o StrictHostKeyChecking=no "${REMOTE_USER}@${REMOTE_HOST}" && print_success "Public key installed on remote host" || print_error "Failed to install public key on remote"
        else
            print_info "ssh-copy-id not found — using manual install fallback"
            sudo -u $WEB_USER ssh -o StrictHostKeyChecking=no "${REMOTE_USER}@${REMOTE_HOST}" "mkdir -p ~/.ssh && chmod 700 ~/.ssh" || true
            sudo -u $WEB_USER scp "${SSH_KEY}.pub" "${REMOTE_USER}@${REMOTE_HOST}:/tmp/id_rsa_voip_auto.pub" && \
            sudo -u $WEB_USER ssh "${REMOTE_USER}@${REMOTE_HOST}" "cat /tmp/id_rsa_voip_auto.pub >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && rm /tmp/id_rsa_voip_auto.pub" \
            && print_success "Public key installed (manual)" || print_error "Manual public key install failed"
        fi
    else
        print_info "No remote target provided — public key contents:"
        sudo -u $WEB_USER cat "${SSH_KEY}.pub"
    fi
}

step_15_optimize() {
    print_header "STEP 15: Optimizing Application"
    
    cd "$APP_DIR"
    
    print_info "Caching configuration..."
    php artisan config:cache
    
    print_info "Caching routes..."
    php artisan route:cache
    
    print_info "Caching views..."
    php artisan view:cache
    
    print_success "Application optimized"
}

step_16_final_checks() {
    print_header "STEP 16: Final System Checks"
    
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
    else
        print_info "Web server: Not detected"
    fi
}

step_17_create_backup_dir() {
    print_header "STEP 17: Creating backup directory /var/backups/monitoring"
    
    if [[ -d "$BACKUP_DIR" ]]; then
        print_info "Backup directory already exists: $BACKUP_DIR"
    else
        mkdir -p "$BACKUP_DIR"
        print_success "Created $BACKUP_DIR"
    fi
    
    # Set ownership and permissions (always, even if dir exists)
    chown -R $WEB_USER:$WEB_USER "$BACKUP_DIR"
    chmod -R 750 "$BACKUP_DIR"
    print_success "Set ownership to $WEB_USER with permissions 750"
}

step_18_verify_webapp() {
    print_header "STEP 18: Verifying Web Application"
    
    # Check Apache is running
    if systemctl is-active --quiet apache2; then
        print_success "Apache2 is running"
    else
        print_error "Apache2 is not running"
        return 1
    fi
    
    # Test local HTTP response
    if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|302"; then
        print_success "Web server responding to requests"
    else
        print_warning "Web server not responding correctly"
    fi
    
    # Check if site is enabled
    if [[ -L /etc/apache2/sites-enabled/voip-mon.conf ]]; then
        print_success "Site configuration enabled"
    else
        print_warning "Site configuration not enabled"
    fi
    
    # Display access URL
    server_ip=$(hostname -I | awk '{print $1}')
    echo ""
    print_info "Access your application at:"
    echo "  → http://${server_ip}"
    echo "  → http://voipmonitor.uprm.edu (if DNS configured)"
}

installation_complete() {
    print_header "✅ Installation Complete!"
    
    cat << EOF

╔════════════════════════════════════════════════════════════════╗
║                    Installation Summary                        ║
╠════════════════════════════════════════════════════════════════╣
║  Application Path: ${APP_DIR}                                  ║
║  Database Name:    ${DB_NAME}                                  ║
║  Database User:    ${DB_USER}                                  ║
║  Database Pass:    ${DB_PASSWORD}                              ║
║                                                                ║
║  PHP Version:      $(php -r "echo PHP_VERSION;")               ║
║  Web Server:       Apache2                                     ║
║  SSL:              Enabled with Certbot                        ║
╚════════════════════════════════════════════════════════════════╝

$(print_info "Save the database credentials in a secure location!")

EOF
}

################################################################################
# Main Installation Flow
################################################################################

main() {

    sudo apt update && sudo apt upgrade 

    # check_root
    
    step_0_welcome
    step_1_check_system
    step_2_install_apache
    step_3_install_mariadb
    step_4_install_php_extensions
    step_5_clone_repository
    step_6_configure_apache
    step_7_configure_mariadb
    step_8_install_composer
    step_9_setup_application
    step_10_run_migrations
    step_11_install_postfix_and_setup
    #step_12_install_certbot
    step_13_setup_cron
    step_14_setup_ssh_key "$REMOTE_USER" "$REMOTE_HOST"
    step_15_optimize
    step_16_final_checks
    step_17_create_backup_dir
    step_18_verify_webapp

    installation_complete
}

# Run main installation
main "$@"
