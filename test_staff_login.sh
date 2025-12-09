#!/bin/bash
echo "========== TEST STAFF LOGIN FLOW =========="
echo ""
echo "1. Checking database for staff user..."
curl -s "http://localhost/test-current-url" | head -5
echo ""
echo "2. Login routes exist:"
php artisan route:list --name=login 2>/dev/null | grep -E "login|admin.dashboard|admin.movies"
echo ""
echo "Done!"
