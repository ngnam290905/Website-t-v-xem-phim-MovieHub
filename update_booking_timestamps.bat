@echo off
echo ========================================
echo Cap nhat timestamps cho bang dat_ve
echo ========================================
echo.

echo Chay migration...
php artisan migrate --path=database/migrations/2024_01_01_000001_add_timestamps_to_dat_ve_table.php --force

echo.
echo ========================================
echo Hoan thanh!
echo ========================================
pause
