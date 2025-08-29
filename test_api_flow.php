<?php
/**
 * Script de test automatisé pour l'API
 * Ce script teste l'ensemble du flux de l'API, de l'enregistrement jusqu'à la suppression d'un projet
 */

// Configuration
$base_url = 'http://localhost:8000/api';
$email = 'test' . time() . '@example.com'; // Email unique pour éviter les conflits
$password = 'password123';
$name = 'Test User';

// Fonction pour effectuer une requête cURL
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $curl = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        echo "\n\033[31mErreur cURL:\033[0m " . $err . "\n";
        return null;
    }
    
    return [
        'code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Fonction pour afficher le résultat d'une requête
function displayResult($step, $result) {
    echo "\n\033[1;36m=== $step ===\033[0m\n";
    
    if (!$result) {
        echo "\033[31mÉchec de la requête\033[0m\n";
        return false;
    }
    
    echo "Code HTTP: " . $result['code'] . "\n";
    
    if ($result['code'] >= 200 && $result['code'] < 300) {
        echo "\033[32mSuccès !\033[0m\n";
        echo "Réponse: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        return true;
    } else {
        echo "\033[31mÉchec !\033[0m\n";
        echo "Réponse: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        return false;
    }
}

// 1. Enregistrement d'un utilisateur
echo "\033[1;33m\nDébut du test de l'API\033[0m\n";

$registerData = [
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password
];

$registerResult = makeRequest("$base_url/register", 'POST', $registerData);
$registerSuccess = displayResult("1. Enregistrement d'un utilisateur", $registerResult);

if (!$registerSuccess) {
    echo "\033[31m\nL'enregistrement a échoué. Arrêt du test.\033[0m\n";
    exit(1);
}

$token = $registerResult['response']['token'];
echo "\033[32mToken obtenu: $token\033[0m\n";

// 2. Connexion
$loginData = [
    'email' => $email,
    'password' => $password
];

$loginResult = makeRequest("$base_url/login", 'POST', $loginData);
$loginSuccess = displayResult("2. Connexion", $loginResult);

if (!$loginSuccess) {
    echo "\033[31m\nLa connexion a échoué. Arrêt du test.\033[0m\n";
    exit(1);
}

$token = $loginResult['response']['token']; // Utiliser le nouveau token
echo "\033[32mNouveau token obtenu: $token\033[0m\n";

// 3. Création d'un projet
$projectData = [
    'title' => 'Projet de Test',
    'description' => 'Description du projet de test'
];

$createProjectResult = makeRequest("$base_url/projects", 'POST', $projectData, $token);
$createProjectSuccess = displayResult("3. Création d'un projet", $createProjectResult);

if (!$createProjectSuccess) {
    echo "\033[31m\nLa création du projet a échoué. Arrêt du test.\033[0m\n";
    exit(1);
}

$projectId = $createProjectResult['response']['project']['id'];
echo "\033[32mID du projet créé: $projectId\033[0m\n";

// 4. Liste des projets
$listProjectsResult = makeRequest("$base_url/projects", 'GET', null, $token);
displayResult("4. Liste des projets", $listProjectsResult);

// 5. Détails d'un projet
$projectDetailsResult = makeRequest("$base_url/projects/$projectId", 'GET', null, $token);
displayResult("5. Détails d'un projet", $projectDetailsResult);

// 6. Mise à jour d'un projet
$updateProjectData = [
    'title' => 'Projet de Test Mis à Jour',
    'description' => 'Description mise à jour du projet de test'
];

$updateProjectResult = makeRequest("$base_url/projects/$projectId", 'PUT', $updateProjectData, $token);
displayResult("6. Mise à jour d'un projet", $updateProjectResult);

// 7. Création d'une tâche pour un projet
$taskData = [
    'title' => 'Tâche de Test',
    'description' => 'Description de la tâche de test',
    'assigned_to' => $loginResult['response']['user']['id']
];

$createTaskResult = makeRequest("$base_url/projects/$projectId/tasks", 'POST', $taskData, $token);
$createTaskSuccess = displayResult("7. Création d'une tâche", $createTaskResult);

if (!$createTaskSuccess) {
    echo "\033[31m\nLa création de la tâche a échoué. Arrêt du test.\033[0m\n";
    exit(1);
}

$taskId = $createTaskResult['response']['task']['id'];
echo "\033[32mID de la tâche créée: $taskId\033[0m\n";

// 8. Liste des tâches d'un projet
$listTasksResult = makeRequest("$base_url/projects/$projectId/tasks", 'GET', null, $token);
displayResult("8. Liste des tâches d'un projet", $listTasksResult);

// 9. Mise à jour d'une tâche
$updateTaskData = [
    'title' => 'Tâche de Test Mise à Jour',
    'description' => 'Description mise à jour de la tâche de test',
    'assigned_to' => $loginResult['response']['user']['id']
];

$updateTaskResult = makeRequest("$base_url/tasks/$taskId", 'PUT', $updateTaskData, $token);
displayResult("9. Mise à jour d'une tâche", $updateTaskResult);

// 10. Mise à jour du statut d'une tâche
$updateStatusData = [
    'is_completed' => true
];

$updateStatusResult = makeRequest("$base_url/tasks/$taskId/status", 'PATCH', $updateStatusData, $token);
displayResult("10. Mise à jour du statut d'une tâche", $updateStatusResult);

// 11. Suppression d'une tâche
$deleteTaskResult = makeRequest("$base_url/tasks/$taskId", 'DELETE', null, $token);
displayResult("11. Suppression d'une tâche", $deleteTaskResult);

// 12. Suppression d'un projet
$deleteProjectResult = makeRequest("$base_url/projects/$projectId", 'DELETE', null, $token);
displayResult("12. Suppression d'un projet", $deleteProjectResult);

// 13. Déconnexion
$logoutResult = makeRequest("$base_url/logout", 'POST', null, $token);
displayResult("13. Déconnexion", $logoutResult);

echo "\n\033[1;32mTest terminé avec succès !\033[0m\n";