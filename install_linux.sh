#!/bin/bash
# MediaTools File Converter — Linux/VPS Production Setup v3
# Tested: Ubuntu 20.04 / 22.04 / 24.04

set -e

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; BLUE='\033[0;34m'; NC='\033[0m'
ok()   { echo -e "${GREEN}  [OK]${NC}  $1"; }
warn() { echo -e "${YELLOW}  [!]${NC}   $1"; }
err()  { echo -e "${RED}  [ERR]${NC} $1"; exit 1; }
step() { echo -e "\n${BLUE}━━ STEP $1 ━━${NC} $2"; }

LARAVEL_ROOT="${1:-$(pwd)}"
VENV_PATH="/var/www/mediatools/venv"

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║   MediaTools File Converter v3 — Linux Setup             ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "  Laravel root : $LARAVEL_ROOT"
echo "  Python venv  : $VENV_PATH"
echo ""

# ── 1. System packages ──────────────────────────────────────────
step 1 "Installing system packages"
sudo apt-get update -qq
sudo apt-get install -y -qq \
    python3 python3-pip python3-venv \
    libreoffice-core libreoffice-writer libreoffice-calc libreoffice-impress \
    ghostscript \
    libgl1 libglib2.0-0 \
    build-essential python3-dev \
    2>&1 | tail -5 || true
ok "System packages installed"

# ── 2. Python venv ───────────────────────────────────────────────
step 2 "Creating Python virtual environment"
sudo mkdir -p "$(dirname "$VENV_PATH")"
sudo python3 -m venv "$VENV_PATH"
sudo chown -R www-data:www-data "$VENV_PATH" 2>/dev/null || true
ok "venv at $VENV_PATH"

PY="${VENV_PATH}/bin/python3"
PIP="${VENV_PATH}/bin/pip"

sudo "$PIP" install --upgrade pip wheel setuptools -q
ok "pip upgraded"

# ── 3. Python packages ───────────────────────────────────────────
step 3 "Installing Python packages"
PKGS=(pdf2docx pdfplumber python-docx openpyxl python-pptx Pillow PyMuPDF img2pdf)
for pkg in "${PKGS[@]}"; do
    echo -n "  → $pkg ... "
    sudo "$PIP" install "$pkg" -q --no-warn-script-location && echo "OK" || echo "WARN (check manually)"
done
ok "Python packages done"

# ── 4. Deploy files ──────────────────────────────────────────────
step 4 "Deploying application files"
SCRIPTS_DIR="${LARAVEL_ROOT}/storage/app/py_scripts"
FC_DIR="${LARAVEL_ROOT}/storage/app/file_converter"
sudo mkdir -p "$SCRIPTS_DIR" "$FC_DIR"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# converter.py
if [ -f "${SCRIPT_DIR}/../python/converter.py" ]; then
    sudo cp "${SCRIPT_DIR}/../python/converter.py" "${SCRIPTS_DIR}/converter.py"
    ok "converter.py deployed"
elif [ -f "converter.py" ]; then
    sudo cp "converter.py" "${SCRIPTS_DIR}/converter.py"
    ok "converter.py deployed"
else
    warn "converter.py not found — copy manually to ${SCRIPTS_DIR}/converter.py"
fi

# Controller
if [ -f "${SCRIPT_DIR}/../php/FileConverterController.php" ]; then
    sudo mkdir -p "${LARAVEL_ROOT}/app/Http/Controllers/Tools"
    sudo cp "${SCRIPT_DIR}/../php/FileConverterController.php" \
        "${LARAVEL_ROOT}/app/Http/Controllers/Tools/FileConverterController.php"
    ok "FileConverterController.php deployed"
fi

# Frontend
if [ -f "${SCRIPT_DIR}/../frontend/fileconverter.js" ]; then
    sudo cp "${SCRIPT_DIR}/../frontend/fileconverter.js" "${LARAVEL_ROOT}/public/js/fileconverter.js"
    ok "fileconverter.js deployed"
fi
if [ -f "${SCRIPT_DIR}/../frontend/fileconverter.css" ]; then
    sudo cp "${SCRIPT_DIR}/../frontend/fileconverter.css" "${LARAVEL_ROOT}/public/css/fileconverter.css"
    ok "fileconverter.css deployed"
fi
if [ -f "${SCRIPT_DIR}/../frontend/index_blade.php" ]; then
    sudo mkdir -p "${LARAVEL_ROOT}/resources/views/tools/fileconverter"
    sudo cp "${SCRIPT_DIR}/../frontend/index_blade.php" \
        "${LARAVEL_ROOT}/resources/views/tools/fileconverter/index.blade.php"
    ok "index.blade.php deployed"
fi

# ── 5. Permissions ───────────────────────────────────────────────
step 5 "Setting permissions"
sudo chown -R www-data:www-data "${LARAVEL_ROOT}/storage" 2>/dev/null || true
sudo chmod -R 775 "$SCRIPTS_DIR" "$FC_DIR"
ok "Permissions set"

# ── 6. Update .env ───────────────────────────────────────────────
step 6 "Updating .env"
ENV_FILE="${LARAVEL_ROOT}/.env"
set_env() {
    local k="$1" v="$2"
    if [ -f "$ENV_FILE" ]; then
        if grep -q "^${k}=" "$ENV_FILE"; then
            sudo sed -i "s|^${k}=.*|${k}=${v}|" "$ENV_FILE"
        else
            echo "${k}=${v}" | sudo tee -a "$ENV_FILE" > /dev/null
        fi
        ok "Set ${k}=${v}"
    else
        warn ".env not found at $ENV_FILE"
    fi
}

LO_BIN="$(which soffice 2>/dev/null || echo soffice)"
set_env "PYTHON_BINARY" "$PY"
set_env "LIBREOFFICE_BINARY" "$LO_BIN"
set_env "LO_TIMEOUT" "180"

# ── 7. Clear caches ──────────────────────────────────────────────
step 7 "Clearing Laravel caches"
if [ -f "${LARAVEL_ROOT}/artisan" ]; then
    cd "$LARAVEL_ROOT"
    sudo -u www-data php artisan config:clear 2>/dev/null && ok "config cache cleared"
    sudo -u www-data php artisan cache:clear  2>/dev/null && ok "app cache cleared"
    sudo -u www-data php artisan view:clear   2>/dev/null && ok "view cache cleared"
fi

# ── 8. Verify ────────────────────────────────────────────────────
step 8 "Verifying Python packages"
"$PY" -c "
import pdf2docx, pdfplumber, docx, openpyxl, pptx, fitz, PIL, img2pdf
print('ALL OK')
" && ok "All Python packages verified" || warn "Some packages may need manual check"

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║               Installation Complete! ✓                   ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "  Python  : $PY"
echo "  Script  : ${SCRIPTS_DIR}/converter.py"
echo "  LO      : $LO_BIN"
echo ""
echo "  Test URL: https://yourdomain.com/file-converter"
echo ""
