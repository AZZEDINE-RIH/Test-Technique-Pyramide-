# Guide Complet de Test de l'API

Ce guide vous permettra de tester l'ensemble des fonctionnalités de l'API, de l'enregistrement d'un utilisateur jusqu'à la gestion des projets et des tâches.

## Prérequis

- Un outil comme Postman, Insomnia ou cURL pour tester les API
- Le serveur Laravel doit être en cours d'exécution (`php artisan serve`)

## 1. Authentification

### 1.1 Enregistrement d'un utilisateur

```
POST http://localhost:8000/api/register
```

Headers:
```
Content-Type: application/json
Accept: application/json
```

Body:
```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Réponse attendue (201 Created):
```json
{
    "user": {
        "name": "Test User",
        "email": "test@example.com",
        "updated_at": "2025-08-24T14:20:56.000000Z",
        "created_at": "2025-08-24T14:20:56.000000Z",
        "id": 1
    },
    "token": "1|fFfgKQpTJzG1ieGFpKCkSYQENktbJ25MKY3lxMll45cbbf73"
}
```

### 1.2 Connexion

```
POST http://localhost:8000/api/login
```

Headers:
```
Content-Type: application/json
Accept: application/json
```

Body:
```json
{
    "email": "test@example.com",
    "password": "password123"
}
```

Réponse attendue (200 OK):
```json
{
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "created_at": "2025-08-24T14:20:56.000000Z",
        "updated_at": "2025-08-24T14:20:56.000000Z"
    },
    "token": "2|fFfgKQpTJzG1ieGFpKCkSYQENktbJ25MKY3lxMll45cbbf74"
}
```

> **Important**: Conservez le token reçu pour l'utiliser dans les requêtes suivantes.

## 2. Gestion des Projets

Pour toutes les requêtes suivantes, vous devez inclure le token d'authentification dans l'en-tête :

```
Authorization: Bearer {votre_token}
Accept: application/json
```

### 2.1 Création d'un projet

```
POST http://localhost:8000/api/projects
```

Body:
```json
{
    "title": "Mon Premier Projet",
    "description": "Description détaillée du projet"
}
```

Réponse attendue (201 Created):
```json
{
    "project": {
        "title": "Mon Premier Projet",
        "description": "Description détaillée du projet",
        "user_id": 1,
        "updated_at": "2025-08-24T15:30:00.000000Z",
        "created_at": "2025-08-24T15:30:00.000000Z",
        "id": 1
    }
}
```

### 2.2 Liste des projets

```
GET http://localhost:8000/api/projects
```

Réponse attendue (200 OK):
```json
{
    "projects": [
        {
            "id": 1,
            "title": "Mon Premier Projet",
            "description": "Description détaillée du projet",
            "user_id": 1,
            "created_at": "2025-08-24T15:30:00.000000Z",
            "updated_at": "2025-08-24T15:30:00.000000Z"
        }
    ]
}
```

### 2.3 Détails d'un projet

```
GET http://localhost:8000/api/projects/1
```

Réponse attendue (200 OK):
```json
{
    "project": {
        "id": 1,
        "title": "Mon Premier Projet",
        "description": "Description détaillée du projet",
        "user_id": 1,
        "created_at": "2025-08-24T15:30:00.000000Z",
        "updated_at": "2025-08-24T15:30:00.000000Z"
    }
}
```

### 2.4 Mise à jour d'un projet

```
PUT http://localhost:8000/api/projects/1
```

Body:
```json
{
    "title": "Mon Projet Mis à Jour",
    "description": "Nouvelle description du projet"
}
```

Réponse attendue (200 OK):
```json
{
    "project": {
        "id": 1,
        "title": "Mon Projet Mis à Jour",
        "description": "Nouvelle description du projet",
        "user_id": 1,
        "created_at": "2025-08-24T15:30:00.000000Z",
        "updated_at": "2025-08-24T15:45:00.000000Z"
    }
}
```

## 3. Gestion des Tâches

### 3.1 Création d'une tâche pour un projet

```
POST http://localhost:8000/api/projects/1/tasks
```

Body:
```json
{
    "title": "Ma Première Tâche",
    "description": "Description détaillée de la tâche",
     "is_completed": false
}
```

Réponse attendue (201 Created):
```json
{
    "task": {
        "title": "Ma Première Tâche",
        "description": "Description détaillée de la tâche",
        "project_id": 1,
        "assigned_to": 1,
        "is_completed": false,
        "updated_at": "2025-08-24T16:00:00.000000Z",
        "created_at": "2025-08-24T16:00:00.000000Z",
        "id": 1
    }
}
```

### 3.2 Liste des tâches d'un projet

```
GET http://localhost:8000/api/projects/1/tasks
```

Réponse attendue (200 OK):
```json
{
    "tasks": [
        {
            "id": 1,
            "title": "Ma Première Tâche",
            "description": "Description détaillée de la tâche",
            "is_completed": false,
            "project_id": 1,
            "assigned_to": 1,
            "created_at": "2025-08-24T16:00:00.000000Z",
            "updated_at": "2025-08-24T16:00:00.000000Z"
        }
    ]
}
```

### 3.3 Mise à jour d'une tâche

```
PUT http://localhost:8000/api/tasks/1
```

Body:
```json
{
    "title": "Tâche Mise à Jour",
    "description": "Nouvelle description de la tâche",
    "assigned_to": 1
}
```

Réponse attendue (200 OK):
```json
{
    "task": {
        "id": 1,
        "title": "Tâche Mise à Jour",
        "description": "Nouvelle description de la tâche",
        "is_completed": false,
        "project_id": 1,
        "assigned_to": 1,
        "created_at": "2025-08-24T16:00:00.000000Z",
        "updated_at": "2025-08-24T16:15:00.000000Z"
    }
}
```

### 3.4 Mise à jour du statut d'une tâche

```
PATCH http://localhost:8000/api/tasks/1/status
```

Body:
```json
{
    "is_completed": true
}
```

Réponse attendue (200 OK):
```json
{
    "task": {
        "id": 1,
        "title": "Tâche Mise à Jour",
        "description": "Nouvelle description de la tâche",
        "is_completed": true,
        "project_id": 1,
        "assigned_to": 1,
        "created_at": "2025-08-24T16:00:00.000000Z",
        "updated_at": "2025-08-24T16:30:00.000000Z"
    }
}
```

### 3.5 Suppression d'une tâche

```
DELETE http://localhost:8000/api/tasks/1
```

Réponse attendue (200 OK):
```json
{
    "message": "Task deleted successfully"
}
```

## 4. Déconnexion

```
POST http://localhost:8000/api/logout
```

Réponse attendue (200 OK):
```json
{
    "message": "Logged out successfully"
}
```

## 5. Suppression d'un projet

```
DELETE http://localhost:8000/api/projects/1
```

Réponse attendue (200 OK):
```json
{
    "message": "Project deleted successfully"
}
```

## Conseils de dépannage

1. **Erreur 401 Unauthorized** : Vérifiez que vous avez bien inclus le token dans l'en-tête `Authorization: Bearer {token}`
2. **Erreur 403 Forbidden** : Vérifiez que vous êtes bien le propriétaire du projet ou que vous avez les permissions nécessaires
3. **Erreur 404 Not Found** : Vérifiez que l'ID du projet ou de la tâche existe
4. **Erreur 422 Unprocessable Entity** : Vérifiez les données envoyées dans le corps de la requête

### Problèmes connus et solutions

#### Problème avec la mise à jour des projets (PUT)

Si vous rencontrez des problèmes avec la requête `PUT /api/projects/{id}`, assurez-vous que :

1. Vous êtes bien authentifié avec un token valide
2. Vous êtes le propriétaire du projet que vous essayez de modifier
3. Vous incluez tous les champs requis dans le corps de la requête (`title` est obligatoire)
4. Vous utilisez le bon ID de projet dans l'URL

Consultez le fichier `API_TROUBLESHOOTING.md` pour plus de détails sur la résolution des problèmes avec l'API.

## Flux de test complet

Pour tester l'ensemble de l'API, suivez ces étapes dans l'ordre :

1. Enregistrez un utilisateur
2. Connectez-vous pour obtenir un token
3. Créez un projet
4. Consultez la liste des projets
5. Consultez les détails d'un projet
6. Mettez à jour un projet
7. Créez une tâche pour ce projet
8. Consultez la liste des tâches du projet
9. Mettez à jour une tâche
10. Mettez à jour le statut d'une tâche
11. Supprimez une tâche
12. Supprimez un projet
13. Déconnectez-vous

En suivant ce flux, vous aurez testé l'ensemble des fonctionnalités de l'API.
