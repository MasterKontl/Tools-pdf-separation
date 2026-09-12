#!/bin/bash

echo "Running migrations..."
php -d expose_php=Off artisan migrate --force

echo "Seeding database..."
php -d expose_php=Off artisan db:seed --class=PlanSeeder --force

echo "Starting queue worker in background..."
setsid php -d expose_php=Off -d upload_max_filesize=250M -d post_max_size=260M artisan queue:work --sleep=1 --tries=3 --max-time=3600 --verbose </dev/null >/tmp/queue-worker.log 2>&1 &

sleep 1
echo "Queue worker PID: $(pgrep -f 'queue:work' || echo 'not found')"

echo "Starting web server..."
exec php -d expose_php=Off -d upload_max_filesize=250M -d post_max_size=260M artisan serve --host=0.0.0.0 --port=$PORT
