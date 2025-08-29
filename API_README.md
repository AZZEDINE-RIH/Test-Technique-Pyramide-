# Guide de Test de l'API

Ce dossier contient plusieurs outils pour tester l'API du projet de gestion de tâches.

## Fichiers disponibles

1. **API_TESTING_GUIDE_COMPLETE.md** - Guide complet détaillant toutes les routes API avec des exemples de requêtes et réponses
2. **test_api_flow.php** - Script PHP qui teste automatiquement l'ensemble du flux de l'API
3. **run_api_test.ps1** - Script PowerShell pour faciliter l'exécution du test API

## Comment tester l'API

### Option 1: Utiliser le script PowerShell (recommandé)

Exécutez simplement le script PowerShell dans PowerShell :

```powershell
.\run_api_test.ps1
```

Ce script vérifiera si le serveur Laravel est en cours d'exécution et vous proposera de le démarrer si nécessaire, puis exécutera le test API complet.

### Option 2: Exécuter le script PHP directement

Assurez-vous que le serveur Laravel est en cours d'exécution (`php artisan serve`), puis exécutez :

```bash
php test_api_flow.php
```

### Option 3: Tester manuellement avec Postman ou un outil similaire

Suivez les instructions détaillées dans le fichier `API_TESTING_GUIDE_COMPLETE.md` pour tester chaque endpoint manuellement.

## Résolution des problèmes

Si vous rencontrez des problèmes lors des tests :

1. Vérifiez que le serveur Laravel est en cours d'exécution (`php artisan serve`)
2. Assurez-vous que toutes les migrations ont été exécutées (`php artisan migrate`)
3. Vérifiez que la table `personal_access_tokens` existe dans la base de données
4. Consultez les logs Laravel dans `storage/logs/laravel.log`

## Flux de test complet

Le script de test automatisé effectue les opérations suivantes dans l'ordre :

1. Enregistrement d'un utilisateur
2. Connexion
3. Création d'un projet
4. Liste des projets
5. Détails d'un projet
6. Mise à jour d'un projet
7. Création d'une tâche
8. Liste des tâches d'un projet
9. Mise à jour d'une tâche
10. Mise à jour du statut d'une tâche
11. Suppression d'une tâche
12. Suppression d'un projet
13. Déconnexion

Chaque étape affiche le code HTTP et la réponse JSON reçue, avec une indication de succès ou d'échec.