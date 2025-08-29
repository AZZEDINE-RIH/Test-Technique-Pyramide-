# Script pour tester la mise à jour d'un projet

# Vérifier si le serveur Laravel est en cours d'exécution
$serverRunning = $false
$processes = Get-Process -Name php -ErrorAction SilentlyContinue

foreach ($process in $processes) {
    $commandLine = (Get-WmiObject Win32_Process -Filter "ProcessId = $($process.Id)").CommandLine
    if ($commandLine -like "*artisan serve*") {
        $serverRunning = $true
        break
    }
}

if (-not $serverRunning) {
    Write-Host "Le serveur Laravel n'est pas en cours d'exécution." -ForegroundColor Yellow
    $startServer = Read-Host "Voulez-vous démarrer le serveur Laravel? (O/N)"
    
    if ($startServer -eq "O" -or $startServer -eq "o") {
        Write-Host "Démarrage du serveur Laravel..." -ForegroundColor Cyan
        Start-Process powershell -ArgumentList "-Command cd '$PSScriptRoot'; php artisan serve"
        
        # Attendre que le serveur démarre
        Write-Host "Attente du démarrage du serveur..." -ForegroundColor Cyan
        Start-Sleep -Seconds 5
    } else {
        Write-Host "Le test ne peut pas être exécuté sans serveur Laravel en cours d'exécution." -ForegroundColor Red
        exit
    }
}

# Exécuter le script de test
Write-Host "Exécution du test de mise à jour d'un projet..." -ForegroundColor Green
php "$PSScriptRoot\test_project_update.php"

# Vérifier le code de sortie
if ($LASTEXITCODE -eq 0 -or $null -eq $LASTEXITCODE) {
    Write-Host "\nTest terminé avec succès!" -ForegroundColor Green
} else {
    Write-Host "\nLe test a échoué avec le code de sortie $LASTEXITCODE" -ForegroundColor Red
    Write-Host "Vérifiez les logs Laravel pour plus de détails: storage/logs/laravel.log" -ForegroundColor Yellow
}