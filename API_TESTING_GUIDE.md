# Guide de Test de l'API

## Problème résolu

Le problème de l'API qui ne retournait pas de token lors de l'enregistrement a été résolu. Les causes principales étaient :

1. La table `personal_access_tokens` n'était pas créée dans la base de données
2. Le middleware Sanctum n'était pas correctement configuré

## Comment tester l'API

Voici comment tester l'API correctement :

### Avec Postman ou un outil similaire

1. **Enregistrement d'un utilisateur**
   - URL: `http://localhost:8000/api/register`
   - Méthode: `POST`
   - Headers:
     ```
     Content-Type: application/json
     Accept: application/json
     ```
   - Body (JSON):
     ```json
     {
         "name": "Test User",
         "email": "test@example.com",
         "password": "password123",
         "password_confirmation": "password123"
     }
     ```
   - Réponse attendue: Un JSON contenant les informations de l'utilisateur et un token

2. **Connexion d'un utilisateur**
   - URL: `http://localhost:8000/api/login`
   - Méthode: `POST`
   - Headers:
     ```
     Content-Type: application/json
     Accept: application/json
     ```
   - Body (JSON):
     ```json
     {
         "email": "test@example.com",
         "password": "password123"
     }
     ```
   - Réponse attendue: Un JSON contenant les informations de l'utilisateur et un token

3. **Accès aux routes protégées**
   - Headers à inclure pour toutes les routes protégées:
     ```
     Authorization: Bearer {token}
     Accept: application/json
     ```
   - Remplacez `{token}` par le token reçu lors de l'enregistrement ou de la connexion

### Avec le script de test

Vous pouvez également utiliser le script PHP `test_api.php` pour tester l'API :

```bash
php test_api.php
```

## Points importants à retenir

1. **L'en-tête Accept est crucial** : Toujours inclure `Accept: application/json` dans vos requêtes API
2. **Token d'authentification** : Pour les routes protégées, utilisez l'en-tête `Authorization: Bearer {token}`
3. **Format des données** : Envoyez toujours les données au format JSON avec l'en-tête `Content-Type: application/json`

## Dépannage

Si vous rencontrez des problèmes :

1. Vérifiez que le serveur Laravel est en cours d'exécution (`php artisan serve`)
2. Assurez-vous que les migrations ont été exécutées (`php artisan migrate`)
3. Vérifiez les logs Laravel dans `storage/logs/laravel.log`