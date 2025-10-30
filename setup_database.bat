@echo off
echo Setting up MovieHub database...
echo.

echo Running migrations...
php artisan migrate:fresh

echo.
echo Seeding database with sample data...
php artisan db:seed

echo.
echo Database setup completed!
echo You can now run: php artisan serve
echo.
echo Note: If you encounter any errors, make sure your database is properly configured in .env file
echo.
pause
