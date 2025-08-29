# Guide de dépannage pour l'API

## Problème : La requête PUT pour mettre à jour un projet ne fonctionne pas

Après analyse du code, plusieurs problèmes potentiels ont été identifiés qui pourraient empêcher la requête PUT de fonctionner correctement.

### Causes possibles et solutions

#### 1. Problème de middleware `project.owner`

La route `PUT /api/projects/{id}` est protégée par le middleware `project.owner` qui vérifie si l'utilisateur authentifié est bien le propriétaire du projet.

```php
Route::middleware('project.owner')->group(function () {
    Route::put('projects/{id}', [ProjectController::class, 'update']); // Update
    // ...
});
```

Le middleware `CheckProjectOwnership` vérifie:
- Si le projet existe
- Si l'utilisateur authentifié est le propriétaire du projet

**Solution**: Assurez-vous que:
- Vous êtes bien authentifié (token valide)
- Vous essayez de modifier un projet qui vous appartient
- L'ID du projet existe dans la base de données

#### 2. Problème de paramètre dans la route

Dans le middleware `CheckProjectOwnership`, le paramètre de route est récupéré avec `$request->route('id')`, mais dans la classe `ProjectRequest`, il est récupéré avec `$this->route('project')`.

**Solution**: Modifiez la classe `ProjectRequest` pour utiliser le bon paramètre:

```php
// Dans ProjectRequest.php
public function authorize(): bool
{
    // Pour store method, toujours autoriser
    if ($this->isMethod('post')) {
        return true;
    }

    // Pour update/delete, vérifier si l'utilisateur possède le projet
    $projectId = $this->route('id');
    $project = \App\Models\Project::find($projectId);
    return $project && Auth::id() === $project->user_id;
}
```

#### 3. Problème de validation des données

Assurez-vous que les données envoyées dans la requête PUT respectent les règles de validation définies dans `ProjectRequest`:

```php
$rules = [
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
];
```

**Solution**: Vérifiez que votre requête contient au moins le champ `title` et qu'il est une chaîne de caractères de moins de 255 caractères.

#### 4. Problème d'en-têtes HTTP

Assurez-vous d'inclure les bons en-têtes dans votre requête:

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {votre_token}
```

#### 5. Problème de méthode HTTP

Certains clients HTTP ou proxys peuvent avoir des problèmes avec la méthode PUT. Vous pouvez essayer d'utiliser la méthode POST avec le paramètre `_method=PUT`.

**Solution**: Utilisez la méthode POST avec le paramètre `_method=PUT` dans le corps de la requête ou comme paramètre d'URL.

### Correction appliquée

Le problème a été identifié dans la classe `ProjectRequest`. La méthode `authorize()` essayait de récupérer le projet avec `$this->route('project')`, mais la route est définie avec le paramètre `{id}` et non `{project}`.

La correction suivante a été appliquée :

```php
// Avant
$project = $this->route('project');

// Après
$projectId = $this->route('id');
$project = \App\Models\Project::find($projectId);
```

De plus, des logs ont été ajoutés dans le middleware `CheckProjectOwnership` et dans le contrôleur `ProjectController` pour faciliter le débogage.

## Comment tester correctement

1. Créez d'abord un projet avec votre utilisateur authentifié
2. Notez l'ID du projet créé
3. Essayez de mettre à jour ce projet avec la requête PUT

### Exemple de requête correcte

```
PUT http://localhost:8000/api/projects/1
Content-Type: application/json
Accept: application/json
Authorization: Bearer {votre_token}

{
    "title": "Mon Projet Mis à Jour",
    "description": "Nouvelle description du projet"
}
```

### Vérification des logs

Pour mieux comprendre le problème, vous pouvez ajouter des logs dans le middleware `CheckProjectOwnership` et dans la méthode `update` du `ProjectController`.

```php
// Dans CheckProjectOwnership.php
public function handle(Request $request, Closure $next): Response
{
    $projectId = $request->route('id');
    \Log::info('Checking project ownership', ['project_id' => $projectId, 'user_id' => Auth::id()]);
    
    // Reste du code...
}
```

```php
// Dans ProjectController.php
public function update(ProjectRequest $request, $id)
{
    \Log::info('Updating project', ['project_id' => $id, 'user_id' => Auth::id()]);
    
    // Reste du code...
}
```

Consultez ensuite les logs dans `storage/logs/laravel.log`.

## Conclusion

Si après avoir suivi ces étapes, la requête PUT ne fonctionne toujours pas, il peut y avoir un problème plus profond dans l'application. Dans ce cas, vous pouvez essayer de:

1. Vérifier les logs Laravel pour plus d'informations
2. Tester l'API avec un outil comme Postman qui vous donne plus de détails sur les erreurs
3. Ajouter plus de logs dans le code pour comprendre où exactement le problème se produit