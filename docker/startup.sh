#!/bin/bash
echo "Starting services..."

# Make sure the database exists
touch /var/www/database/database.sqlite
chmod 666 /var/www/database/database.sqlite

# Run migrations if needed
php /var/www/artisan migrate --force

# Run the price fetcher once at startup
php /var/www/artisan crypto:fetch-prices

# Start supervisor
exec supervisord -c /etc/supervisor/conf.d/laravel-worker.conf