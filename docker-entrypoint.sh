#!/bin/bash
set -e

echo "Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Start Symfony's built-in server
exec php -S 0.0.0.0:8000 -t public