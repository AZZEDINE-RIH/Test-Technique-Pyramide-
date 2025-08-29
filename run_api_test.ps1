# Script PowerShell pour exécuter le test API

# Vérifier si le serveur Laravel est en cours d'exécution
$serverRunning = $false
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000" -Method Head -TimeoutSec 2 -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        $serverRunning = $true
    }
} catch {
    $serverRunning = $false
}

# Si le serveur n'est pas en cours d'exécution, demander à l'utilisateur s'il souhaite le démarrer
if (-not $serverRunning) {
    Write-Host "Le serveur Laravel n'est pas en cours d'exécution." -ForegroundColor Yellow
    $startServer = Read-Host "Voulez-vous démarrer le serveur Laravel? (O/N)"
    
    if ($startServer -eq "O" -or $startServer -eq "o") {
        Write-Host "Démarrage du serveur Laravel..." -ForegroundColor Cyan
        Start-Process -FilePath "php" -ArgumentList "artisan", "serve" -NoNewWindow
        
        # Attendre que le serveur soit prêt
        Write-Host "Attente du démarrage du serveur..." -ForegroundColor Cyan
        Start-Sleep -Seconds 5
    } else {
        Write-Host "Le test API ne peut pas être exécuté sans serveur Laravel en cours d'exécution." -ForegroundColor Red
        exit
    }
}

# Exécuter le script de test API
Write-Host "\nExécution du test API..." -ForegroundColor Green
php test_api_flow.php

Write-Host "\nConsultez le guide complet dans le fichier API_TESTING_GUIDE_COMPLETE.md pour plus d'informations." -ForegroundColor Cyan