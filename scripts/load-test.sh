#!/bin/bash

##############################################################################
# Load Test Setup Script - 3,000 Phones
# Purpose: Automate setup and execution of load testing for VoIP monitoring
##############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Banner
echo -e "${BLUE}"
echo "============================================================"
echo "  VoIP Monitoring System - Load Test Setup (3,000 phones)"
echo "============================================================"
echo -e "${NC}"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: Must be run from Laravel root directory${NC}"
    exit 1
fi

# Function to print status
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check prerequisites
echo -e "${BLUE}Checking prerequisites...${NC}"

# Check PHP
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed"
    exit 1
fi
print_status "PHP installed: $(php -v | head -n 1)"

# Check Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed"
    exit 1
fi
print_status "Composer installed"

# Check database connection
if ! php artisan db:show &> /dev/null; then
    print_error "Database connection failed"
    exit 1
fi
print_status "Database connection OK"

# Menu
echo ""
echo -e "${BLUE}What would you like to do?${NC}"
echo "1) Setup - Seed 3,000 phones"
echo "2) Test - Run performance tests"
echo "3) Cleanup - Remove load test data"
echo "4) Full Suite - Setup + Test + Cleanup"
echo "5) Exit"
echo ""
read -p "Enter choice [1-5]: " choice

case $choice in
    1)
        echo -e "\n${BLUE}=== SETUP: Seeding 3,000 Phones ===${NC}\n"
        
        # Check existing data
        EXISTING_COUNT=$(php artisan tinker --execute="echo \App\Models\Devices::count();" 2>/dev/null | tail -n 1)
        echo "Current device count: $EXISTING_COUNT"
        
        if [ "$EXISTING_COUNT" -gt 100 ]; then
            print_warning "Database already has $EXISTING_COUNT devices"
            read -p "Continue anyway? (y/N): " confirm
            if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
                echo "Cancelled."
                exit 0
            fi
        fi
        
        echo -e "\n${YELLOW}This will take 2-5 minutes...${NC}\n"
        
        # Run seeder with increased memory
        php -d memory_limit=1024M artisan db:seed --class=LoadTest3000PhonesSeeder
        
        if [ $? -eq 0 ]; then
            print_status "Seeding completed successfully!"
            
            # Show statistics
            echo -e "\n${BLUE}Current Database Statistics:${NC}"
            php artisan tinker --execute="
                echo 'Total Devices: ' . \App\Models\Devices::count() . PHP_EOL;
                echo 'Total Extensions: ' . \App\Models\Extensions::count() . PHP_EOL;
                echo 'Online Devices: ' . \App\Models\Devices::where('status', 'online')->count() . PHP_EOL;
                echo 'Offline Devices: ' . \App\Models\Devices::where('status', 'offline')->count() . PHP_EOL;
            "
        else
            print_error "Seeding failed!"
            exit 1
        fi
        ;;
        
    2)
        echo -e "\n${BLUE}=== TESTING: Running Performance Tests ===${NC}\n"
        
        # Check if we have enough data
        DEVICE_COUNT=$(php artisan tinker --execute="echo \App\Models\Devices::count();" 2>/dev/null | tail -n 1)
        
        if [ "$DEVICE_COUNT" -lt 3000 ]; then
            print_error "Not enough test data! Current devices: $DEVICE_COUNT"
            echo "Run option 1 first to seed 3,000 phones"
            exit 1
        fi
        
        print_status "Found $DEVICE_COUNT devices - ready for testing"
        
        echo -e "\n${YELLOW}Choose test type:${NC}"
        echo "1) Manual performance tests (recommended)"
        echo "2) Pest test suite (may have issues)"
        echo "3) Both"
        read -p "Enter choice [1-3]: " test_choice
        
        case $test_choice in
            1)
                echo -e "\n${YELLOW}Running Manual Performance Tests...${NC}\n"
                ;;
            2)
                echo -e "\n${YELLOW}Running Pest Test Suite...${NC}\n"
                php artisan test tests/Feature/LoadTest3000PhonesTest.php
                ;;
            3)
                echo -e "\n${YELLOW}Running Manual Performance Tests...${NC}\n"
                # Manual tests will run below
                ;;
            *)
                echo -e "\n${YELLOW}Running Manual Performance Tests...${NC}\n"
                ;;
        esac
        
        # Manual performance tests (run for options 1 and 3)
        if [ "$test_choice" != "2" ]; then
            echo "Testing database queries..."
            php artisan tinker --execute="
            \$start = microtime(true);
            \$count = \App\Models\Devices::count();
            \$time = round((microtime(true) - \$start) * 1000, 2);
            echo '✓ Count all devices: ' . \$time . 'ms (' . \$count . ' devices)' . PHP_EOL;

            \$start = microtime(true);
            \$online = \App\Models\Devices::where('status', 'online')->count();
            \$time = round((microtime(true) - \$start) * 1000, 2);
            echo '✓ Count online devices: ' . \$time . 'ms (' . \$online . ' online)' . PHP_EOL;

            \$start = microtime(true);
            \$networks = \App\Models\Network::with('devices')->limit(20)->get();
            \$time = round((microtime(true) - \$start) * 1000, 2);
            echo '✓ Get 20 networks with devices: ' . \$time . 'ms' . PHP_EOL;

            \$start = microtime(true);
            \$buildings = \App\Models\Building::with('networks.devices')->limit(10)->get();
            \$time = round((microtime(true) - \$start) * 1000, 2);
            echo '✓ Get 10 buildings with networks: ' . \$time . 'ms' . PHP_EOL;
"
        fi
        
        # Run Pest tests for option 3
        if [ "$test_choice" = "3" ]; then
            echo -e "\n${YELLOW}Running Pest Test Suite...${NC}\n"
            php artisan test tests/Feature/LoadTest3000PhonesTest.php || print_warning "Some tests were skipped or failed"
        fi        # Apache Bench tests (if available)
        if command -v ab &> /dev/null; then
            echo -e "\n${YELLOW}Running Apache Bench tests...${NC}\n"
            
            read -p "Server URL (default: http://localhost): " SERVER_URL
            SERVER_URL=${SERVER_URL:-http://localhost}
            
            echo "Testing dashboard with 100 requests, 10 concurrent..."
            ab -n 100 -c 10 -g dashboard_bench.tsv "$SERVER_URL/dashboard" 2>&1 | grep -E "(Requests per second|Time per request|Failed requests)"
            
            print_status "Benchmark complete! Results saved to dashboard_bench.tsv"
        else
            print_warning "Apache Bench not installed. Install with: sudo apt install apache2-utils"
        fi
        ;;
        
    3)
        echo -e "\n${BLUE}=== CLEANUP: Removing Load Test Data ===${NC}\n"
        
        # Show current count
        LOADTEST_COUNT=$(php artisan tinker --execute="echo \App\Models\Extensions::where('user_first_name', 'LoadTest')->count();" 2>/dev/null | tail -n 1)
        
        if [ "$LOADTEST_COUNT" -eq 0 ]; then
            print_warning "No LoadTest data found"
            exit 0
        fi
        
        echo "Found $LOADTEST_COUNT LoadTest extensions"
        print_warning "This will DELETE all LoadTest data (preserving Monzon and Prueba)"
        read -p "Are you sure? (y/N): " confirm
        
        if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
            echo "Cancelled."
            exit 0
        fi
        
        # Run cleanup
        php artisan db:seed --class=CleanupLoadTestDataSeeder
        
        if [ $? -eq 0 ]; then
            print_status "Cleanup completed successfully!"
            
            # Show final statistics
            echo -e "\n${BLUE}Remaining Database Statistics:${NC}"
            php artisan tinker --execute="
                echo 'Total Devices: ' . \App\Models\Devices::count() . PHP_EOL;
                echo 'Total Extensions: ' . \App\Models\Extensions::count() . PHP_EOL;
            "
        else
            print_error "Cleanup failed!"
            exit 1
        fi
        ;;
        
    4)
        echo -e "\n${BLUE}=== FULL SUITE: Setup + Test + Cleanup ===${NC}\n"
        
        print_warning "This will:"
        echo "  1. Seed 3,000 phones"
        echo "  2. Run all performance tests"
        echo "  3. Clean up test data"
        echo ""
        read -p "Continue? (y/N): " confirm
        
        if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
            echo "Cancelled."
            exit 0
        fi
        
        # Setup
        echo -e "\n${BLUE}[1/3] Seeding data...${NC}\n"
        php -d memory_limit=1024M artisan db:seed --class=LoadTest3000PhonesSeeder || exit 1
        
        # Wait a moment
        sleep 2
        
        # Test
        echo -e "\n${BLUE}[2/3] Running tests...${NC}\n"
        echo "Testing database queries..."
        php artisan tinker --execute="
            \$start = microtime(true);
            \$count = \App\Models\Devices::count();
            \$time = round((microtime(true) - \$start) * 1000, 2);
            echo '✓ Count all devices: ' . \$time . 'ms (' . \$count . ' devices)' . PHP_EOL;

            \$start = microtime(true);
            \$online = \App\Models\Devices::where('status', 'online')->count();
            \$time = round((microtime(true) - \$start) * 1000, 2);
            echo '✓ Count online devices: ' . \$time . 'ms (' . \$online . ' online)' . PHP_EOL;

            \$start = microtime(true);
            \$networks = \App\Models\Network::with('devices')->limit(20)->get();
            \$time = round((microtime(true) - \$start) * 1000, 2);
            echo '✓ Get 20 networks with devices: ' . \$time . 'ms' . PHP_EOL;
            " || print_warning "Some tests had issues"
        
        # Wait a moment
        sleep 2
        
        # Cleanup
        echo -e "\n${BLUE}[3/3] Cleaning up...${NC}\n"
        php artisan db:seed --class=CleanupLoadTestDataSeeder || exit 1
        
        echo -e "\n${GREEN}============================================================${NC}"
        echo -e "${GREEN}  Full test suite completed!${NC}"
        echo -e "${GREEN}============================================================${NC}\n"
        ;;
        
    5)
        echo "Exiting."
        exit 0
        ;;
        
    *)
        print_error "Invalid choice"
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}Done!${NC}"
echo ""
