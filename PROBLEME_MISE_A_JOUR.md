# Problème de mise à jour des projets

## Description du problème

Lors de la mise à jour d'un projet via une requête PUT à l'API, une page HTML est renvoyée au lieu d'une réponse JSON. Ce comportement est observé lorsqu'on utilise la méthode HTTP PUT pour mettre à jour un projet.

## Cause identifiée

Le problème est lié au middleware `EnsureFrontendRequestsAreStateful` de Laravel Sanctum qui applique le middleware `VerifyCsrfToken` aux requêtes API si elles sont détectées comme venant du frontend. Cela provoque une redirection vers une page HTML au lieu de renvoyer une réponse JSON en cas d'échec de validation CSRF.

## Solutions proposées

### Solution 1: Désactiver le middleware EnsureFrontendRequestsAreStateful

Dans le fichier `app/Http/Kernel.php`, commentez la ligne suivante dans le groupe de middleware 'api' :

```php
// \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
```

Cette solution désactive la vérification CSRF pour toutes les requêtes API, ce qui peut poser des problèmes de sécurité si l'API est utilisée depuis un navigateur.

### Solution 2: Utiliser une route POST alternative

Ajouter une route POST alternative pour la mise à jour des projets :

```php
// Dans routes/api.php
Route::middleware(['auth:sanctum', 'project.owner'])->post('projects/{id}/update', [ProjectController::class, 'update']);
```

Cette solution permet aux clients qui ne supportent pas les requêtes PUT ou qui ont des problèmes avec CSRF d'utiliser une route POST pour mettre à jour les projets.

### Solution 3: Configurer correctement les en-têtes CSRF

Assurez-vous que les requêtes PUT incluent les en-têtes CSRF nécessaires :

```php
// Dans le client
$headers = [
    'X-CSRF-TOKEN' => 'token_csrf',
    'X-XSRF-TOKEN' => 'token_xsrf',
];
```

Vous devez d'abord obtenir le token CSRF en appelant l'endpoint `sanctum/csrf-cookie`.

### Solution 4: Configurer correctement le fichier .env

Assurez-vous que les variables d'environnement suivantes sont correctement configurées dans le fichier `.env` :

```
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000
SESSION_DOMAIN=localhost
```

## Recommandation

La solution recommandée est d'utiliser la **Solution 2** (route POST alternative) car elle est la plus simple à mettre en œuvre et ne compromet pas la sécurité de l'application. Elle permet également de maintenir la compatibilité avec les clients qui ne supportent pas les requêtes PUT.

## Tests effectués

Nous avons testé les approches suivantes :

1. Requête PUT standard
2. Requête POST avec `_method=PUT`
3. Requête POST avec l'en-tête `X-HTTP-Method-Override: PUT`
4. Requête PATCH standard
5. Requête POST vers une route alternative

Seule la requête POST vers une route alternative a fonctionné correctement.

## Conclusion

Le problème de mise à jour des projets via la méthode PUT est lié à la configuration de Laravel Sanctum et à la vérification CSRF. La solution la plus simple est d'utiliser une route POST alternative pour la mise à jour des projets.