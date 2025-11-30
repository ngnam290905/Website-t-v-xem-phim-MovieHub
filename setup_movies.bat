@echo off
echo Setting up movie management system...

echo.
echo 1. Running migrations...
php artisan migrate --force

echo.
echo 2. Seeding movie data...
php artisan db:seed --class=Database\\Seeders\\MovieSeeder

echo.
echo 3. Creating storage link...
php artisan storage:link

echo.
echo 4. Creating storage directories...
if not exist "storage\app\public\posters" mkdir "storage\app\public\posters"

echo.
echo Setup completed! You can now access:
echo - Admin panel: http://localhost:8000/admin/movies
echo - Login with: admin@example.com / password
echo.
pause
