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

# Download Composer installer
EXPECTED_HASH="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_HASH="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
if [[ "$EXPECTED_HASH" != "$ACTUAL_HASH" ]]
then
    echo "Installer corrupt. Hash mismatch."
    rm composer-setup.php
    exit 1
fi

echo "Installer verified."

# Run installer.
php composer-setup.php
rm composer-setup.php

# Place composer.phar in a directory in PATH
echo
read -p "Do you want to install Composer globally (requires sudo)? [y/N] " choice

if [[ "$choice" =~ ^[Yy] ]]
then
    GLOBAL_BIN=/usr/local/bin
    if command -v sudo >/dev/null 2>&1; then
        sudo mv composer.phar "$GLOBAL_BIN/composer"
        echo "Composer installed globally at '$GLOBAL_BIN/composer'"
    else
        echo "Error: 'sudo' is not available on this system."
        echo "Falling back to local installation."
        choice="n"
    fi
fi

if [[ ! "$choice" =~ ^[Yy] ]]; then
    LOCAL_BIN="$HOME/.local/bin"
    mkdir -p "$LOCAL_BIN"
    mv composer.phar "$LOCAL_BIN/composer"

    echo "Composer installed locally at '$LOCAL_BIN/composer'"

    if [[ ":$PATH:" != *":$LOCAL_BIN:"* ]]
    then
        cat <<EOF
'$LOCAL_BIN' not found in path. Appending the following lines to '~/.bashrc':

if [[ ":\$PATH:" != *":\$HOME/.local/bin:"* ]]
then export PATH="\$HOME/.local/bin\${PATH:+:}\$PATH"
fi

Restart your terminal for the changes to take effect.
EOF

    echo 'if [[ ":$PATH:" != *":$HOME/.local/bin:"* ]]
then export PATH="$HOME/.local/bin${PATH:+:}$PATH"
fi' >> ~/.bashrc
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
