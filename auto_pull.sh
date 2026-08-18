#!/bin/bash

# Masuk ke direktori repository
cd /var/www/html/owl || exit

# Tarik perubahan terbaru dari branch main
git pull origin main >> /var/log/git-pull.log 2>&1

# (Opsional) Jalankan post-update command sesuai project jika ada:
# composer install --no-interaction --prefer-dist --optimize-autoloader
# npm install && npm run build
