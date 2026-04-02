#!/usr/bin/env bash
set -e  # Exit immediately on command failure

# -------------------------------------
# Install PHP Composer script
# -------------------------------------
#
# requirements: php version 7^ or higher

# Check if PHP is available
if ! command -v php >/dev/null 2>&1; then
    echo "Error: PHP is not installed. Please install PHP before running this script."
    exit 1
fi

# Download and verify the Composer installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

EXPECTED_HASH="c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47"

ACTUAL_HASH=$(php -r "echo hash_file('sha384', 'composer-setup.php');")

if [ "$EXPECTED_HASH" != "$ACTUAL_HASH" ]; then
    echo "Installer corrupt. Hash mismatch."
    rm composer-setup.php
    exit 1
fi

echo "Installer verified."
php composer-setup.php
rm composer-setup.php

# Ask where to install composer.phar
echo
read -p "Do you want to install Composer globally (requires sudo)? [y/N] " choice
if [[ "$choice" =~ ^[Yy]$ ]]; then
    if command -v sudo >/dev/null 2>&1; then
        if sudo -n true 2>/dev/null; then
            sudo mv composer.phar /usr/local/bin/composer
        else
            echo "Sudo access required. You may be prompted for your password."
            sudo mv composer.phar /usr/local/bin/composer
        fi
        echo "Composer installed globally at /usr/local/bin/composer"
    else
        echo "Error: 'sudo' is not available on this system."
        echo "Falling back to local installation."
        choice="n"
    fi
fi

if [[ ! "$choice" =~ ^[Yy]$ ]]; then
    LOCAL_BIN="$HOME/.local/bin"
    mkdir -p "$LOCAL_BIN"
    mv composer.phar "$LOCAL_BIN/composer"
    echo "Composer installed locally at $LOCAL_BIN/composer"
    if [[ ":$PATH:" != *":$LOCAL_BIN:"* ]]; then
        echo "export PATH=\"\$HOME/.local/bin:\$PATH\"" >> "$HOME/.bashrc"
        echo "Added ~/.local/bin to PATH in .bashrc. Restart your terminal or run:"
        echo "source ~/.bashrc"
    fi
fi

# Add vendor directory to .gitignore
echo "/vendor/" >> .gitignore

# Optionally the users can initialize a new composer project
read -p "Do you want to run 'composer init' to start a new project? [y/N] " init_choice
if [[ "$init_choice" =~ ^[Yy]$ ]]; then
    composer init
else
    echo "You can run 'composer init' later to create a new project."
fi

