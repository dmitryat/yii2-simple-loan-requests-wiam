#!/bin/sh


# Устанавливаем зависимости только если vendor/ не существует
if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1

    if [ $? -ne 0 ]; then
        echo "WARNING: Composer install failed" >&2
    else
        echo "Composer install completed successfully"
    fi
fi

echo "Waiting for database..."
wait-for-db.sh

echo "Running migrations..."
php /var/www/html/yii migrate --interactive=0 2>&1

#if [ $? -ne 0 ]; then
#    echo "WARNING: Migration failed, check logs above" >&2
#else
#    echo "Migrations completed successfully"
#fi

echo "Starting PHP-FPM..."
exec php-fpm