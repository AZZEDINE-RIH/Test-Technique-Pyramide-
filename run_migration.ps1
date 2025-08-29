# PowerShell script to run migrations and test API

Write-Host "Running migrations..."
cd "c:\Users\Chip Tech\Desktop\Test Pratique Pyramide\test_pratique"
php artisan migrate --force

Write-Host "\nPublishing Sanctum migrations..."
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag="sanctum-migrations"

Write-Host "\nRunning migrations again..."
php artisan migrate --force

Write-Host "\nTesting API..."
php test_api.php