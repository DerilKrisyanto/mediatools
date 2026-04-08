#!/bin/bash

# ═══════════════════════════════════════════════════════════
# Enhanced File Converter - Quick Installation Script
# Version: 1.0
# ═══════════════════════════════════════════════════════════

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║  Enhanced File Converter - Installation Script           ║"
echo "║  Version 11.0 - High-Fidelity Edition                    ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

check_command() {
    if command -v $1 &> /dev/null; then
        print_success "$1 is installed"
        return 0
    else
        print_warning "$1 is NOT installed"
        return 1
    fi
}

# ═══════════════════════════════════════════════════════════
# Step 1: Check Prerequisites
# ═══════════════════════════════════════════════════════════

print_step "Checking prerequisites..."
echo ""

# Check Python
if check_command python3; then
    PYTHON_VERSION=$(python3 --version 2>&1)
    echo "  → $PYTHON_VERSION"
else
    print_error "Python 3 is required. Please install it first."
    exit 1
fi

# Check PHP
if check_command php; then
    PHP_VERSION=$(php --version | head -n 1)
    echo "  → $PHP_VERSION"
else
    print_error "PHP is required. Please install it first."
    exit 1
fi

# Check Composer
check_command composer
echo ""

# Check LibreOffice (optional but recommended)
if check_command soffice; then
    LO_VERSION=$(soffice --version 2>&1 | head -n 1)
    echo "  → $LO_VERSION"
else
    print_warning "LibreOffice not found. Fallback conversion will not work."
    echo "  → Install: sudo apt-get install libreoffice"
fi
echo ""

# Check Ghostscript (optional but recommended)
if check_command gs; then
    GS_VERSION=$(gs --version 2>&1)
    echo "  → Ghostscript $GS_VERSION"
else
    print_warning "Ghostscript not found. PDF preview will not work."
    echo "  → Install: sudo apt-get install ghostscript"
fi
echo ""

# ═══════════════════════════════════════════════════════════
# Step 2: Install Python Dependencies
# ═══════════════════════════════════════════════════════════

print_step "Installing Python dependencies..."
echo ""

PYTHON_PACKAGES=(
    "pdfplumber"
    "python-docx"
    "openpyxl"
    "pdf2docx"
)

for package in "${PYTHON_PACKAGES[@]}"; do
    echo "Installing $package..."
    pip3 install "$package" --break-system-packages --quiet
    if [ $? -eq 0 ]; then
        print_success "$package installed"
    else
        print_warning "$package installation failed (may already be installed)"
    fi
done
echo ""

# Optional packages
print_step "Installing optional packages (for enhanced features)..."
echo ""

OPTIONAL_PACKAGES=(
    "camelot-py[base]"
    "opencv-python-headless"
)

for package in "${OPTIONAL_PACKAGES[@]}"; do
    echo "Installing $package (optional)..."
    pip3 install "$package" --break-system-packages --quiet 2>/dev/null
    if [ $? -eq 0 ]; then
        print_success "$package installed"
    else
        print_warning "$package installation failed (optional - not critical)"
    fi
done
echo ""

# ═══════════════════════════════════════════════════════════
# Step 3: Backup Existing Files
# ═══════════════════════════════════════════════════════════

print_step "Backing up existing files..."
echo ""

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="./backup_$TIMESTAMP"

mkdir -p "$BACKUP_DIR"

# Backup controller if exists
if [ -f "app/Http/Controllers/Tools/FileConverterController.php" ]; then
    cp "app/Http/Controllers/Tools/FileConverterController.php" "$BACKUP_DIR/"
    print_success "Backed up FileConverterController.php"
fi

# Backup JS if exists
if [ -f "public/js/fileconverter.js" ]; then
    cp "public/js/fileconverter.js" "$BACKUP_DIR/"
    print_success "Backed up fileconverter.js"
fi

# Backup CSS if exists
if [ -f "public/css/fileconverter.css" ]; then
    cp "public/css/fileconverter.css" "$BACKUP_DIR/"
    print_success "Backed up fileconverter.css"
fi

if [ "$(ls -A $BACKUP_DIR)" ]; then
    print_success "Backup created in $BACKUP_DIR"
else
    print_warning "No existing files to backup"
    rmdir "$BACKUP_DIR"
fi
echo ""

# ═══════════════════════════════════════════════════════════
# Step 4: Copy Enhanced Files
# ═══════════════════════════════════════════════════════════

print_step "Deploying enhanced files..."
echo ""

# Ensure directories exist
mkdir -p app/Http/Controllers/Tools
mkdir -p public/js
mkdir -p public/css
mkdir -p storage/app/file_converter
mkdir -p storage/app/py_scripts
mkdir -p storage/app/conversion_previews

# Copy enhanced controller
if [ -f "FileConverterController_Enhanced.php" ]; then
    cp "FileConverterController_Enhanced.php" "app/Http/Controllers/Tools/FileConverterController.php"
    print_success "Deployed enhanced controller"
else
    print_error "FileConverterController_Enhanced.php not found!"
fi

# Copy enhanced JavaScript
if [ -f "fileconverter_enhanced.js" ]; then
    cp "fileconverter_enhanced.js" "public/js/fileconverter.js"
    print_success "Deployed enhanced JavaScript"
else
    print_error "fileconverter_enhanced.js not found!"
fi

# Copy enhanced CSS
if [ -f "fileconverter_enhanced.css" ]; then
    cp "fileconverter_enhanced.css" "public/css/fileconverter.css"
    print_success "Deployed enhanced CSS"
else
    print_error "fileconverter_enhanced.css not found!"
fi
echo ""

# ═══════════════════════════════════════════════════════════
# Step 5: Set Permissions
# ═══════════════════════════════════════════════════════════

print_step "Setting storage permissions..."
echo ""

chmod -R 775 storage/app/file_converter 2>/dev/null
chmod -R 775 storage/app/py_scripts 2>/dev/null
chmod -R 775 storage/app/conversion_previews 2>/dev/null

print_success "Permissions set"
echo ""

# ═══════════════════════════════════════════════════════════
# Step 6: Update .env Configuration
# ═══════════════════════════════════════════════════════════

print_step "Checking .env configuration..."
echo ""

if [ -f ".env" ]; then
    # Check if Python binary is set
    if grep -q "PYTHON_BINARY=" .env; then
        print_success "PYTHON_BINARY is already configured"
    else
        echo "" >> .env
        echo "# File Converter Configuration" >> .env
        echo "PYTHON_BINARY=python3" >> .env
        print_success "Added PYTHON_BINARY to .env"
    fi
    
    # Check conversion timeout
    if grep -q "CONVERSION_TIMEOUT=" .env; then
        print_success "CONVERSION_TIMEOUT is already configured"
    else
        echo "CONVERSION_TIMEOUT=300" >> .env
        print_success "Added CONVERSION_TIMEOUT to .env"
    fi
else
    print_warning ".env file not found"
fi
echo ""

# ═══════════════════════════════════════════════════════════
# Step 7: Clear Laravel Caches
# ═══════════════════════════════════════════════════════════

print_step "Clearing Laravel caches..."
echo ""

php artisan config:clear 2>/dev/null
php artisan cache:clear 2>/dev/null
php artisan route:clear 2>/dev/null
php artisan view:clear 2>/dev/null

print_success "Caches cleared"
echo ""

# ═══════════════════════════════════════════════════════════
# Step 8: Run Tests
# ═══════════════════════════════════════════════════════════

print_step "Running dependency check..."
echo ""

# Create a simple test script
cat > /tmp/test_deps.py << 'EOF'
import sys
import json

deps = {}
for name, mod in [
    ("pdfplumber", "pdfplumber"),
    ("python_docx", "docx"),
    ("openpyxl", "openpyxl"),
    ("pdf2docx", "pdf2docx"),
]:
    try:
        __import__(mod)
        deps[name] = True
    except ImportError:
        deps[name] = False

all_ok = all(deps.values())
print(json.dumps({"all_ok": all_ok, "deps": deps}, indent=2))
sys.exit(0 if all_ok else 1)
EOF

python3 /tmp/test_deps.py
if [ $? -eq 0 ]; then
    print_success "All Python dependencies are working!"
else
    print_warning "Some Python dependencies may be missing"
fi

rm /tmp/test_deps.py
echo ""

# ═══════════════════════════════════════════════════════════
# Final Summary
# ═══════════════════════════════════════════════════════════

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║              Installation Complete! ✓                     ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}✓ Enhanced File Converter has been installed successfully!${NC}"
echo ""
echo "Next steps:"
echo "  1. Visit your file converter page: /file-converter"
echo "  2. Test with a PDF containing tables"
echo "  3. Check the quality score and preview"
echo ""
echo "Key improvements:"
echo "  ✓ High-fidelity table preservation"
echo "  ✓ Preview before download"
echo "  ✓ Quality scoring system"
echo "  ✓ Smart fallback mechanism"
echo "  ✓ Real-time progress tracking"
echo ""
echo "Documentation: See ENHANCED_FILE_CONVERTER_DOCS.md"
echo ""
echo -e "${YELLOW}Note: If you encounter issues, check the logs at:${NC}"
echo "  - storage/logs/laravel.log"
echo "  - Browser console (F12)"
echo ""