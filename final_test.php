<?php

// Configuration
$baseUrl = 'http://localhost:8000/api';
$email = 'final_test_' . time() . '@example.com';
$password = 'password123';

// Fonction pour effectuer des requêtes HTTP
function makeRequest($url, $method = 'GET', $data = [], $headers = []) {
    echo "\nRequête: $method $url\n";
    if (!empty($data)) {
        echo "Données: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
    if (!empty($headers)) {
        echo "En-têtes: " . implode(", ", $headers) . "\n";
    }
    
    $curl = curl_init();
    
    // Configuration de base
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_VERBOSE, true);
    
    // Méthode HTTP
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    
    // Données
    if (!empty($data)) {
        $jsonData = json_encode($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($jsonData);
    }
    
    // En-têtes
    if (!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    
    // Exécution
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    echo "\nCode HTTP: $httpCode\n";
    if ($error) {
        echo "Erreur: $error\n";
    }
    
    echo "\nRéponse brute:\n$response\n";
    
    $jsonResponse = json_decode($response, true);
    if ($jsonResponse) {
        echo "\nRéponse JSON:\n" . json_encode($jsonResponse, JSON_PRETTY_PRINT) . "\n";
    }
    
    return [
        'code' => $httpCode,
        'response' => $response,
        'json' => $jsonResponse,
        'error' => $error
    ];
}

// 1. Enregistrement d'un utilisateur
echo "\n🚀 TEST FINAL DE MISE À JOUR DE PROJET\n";
echo "\n1. Enregistrement d'un utilisateur\n";

$registerData = [
    'name' => 'Test User',
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password
];

$registerResult = makeRequest("$baseUrl/register", 'POST', $registerData);

if ($registerResult['code'] != 201) {
    echo "\n❌ Échec de l'enregistrement. Arrêt du test.\n";
    exit(1);
}

// Récupération du token
$token = $registerResult['json']['token'] ?? null;

if (!$token) {
    echo "\n❌ Pas de token reçu. Arrêt du test.\n";
    exit(1);
}

echo "\n✅ Token reçu: $token\n";

// 2. Création d'un projet
echo "\n2. Création d'un projet\n";

$projectData = [
    'title' => 'Projet de test final',
    'description' => 'Description du projet de test final'
];

$createProjectResult = makeRequest(
    "$baseUrl/projects",
    'POST',
    $projectData,
    ["Authorization: Bearer $token"]
);

if ($createProjectResult['code'] != 201) {
    echo "\n❌ Échec de la création du projet. Arrêt du test.\n";
    exit(1);
}

// Récupération de l'ID du projet
$projectId = $createProjectResult['json']['data']['id'] ?? null;

if (!$projectId) {
    echo "\n❌ Pas d'ID de projet reçu. Arrêt du test.\n";
    exit(1);
}

echo "\n✅ Projet créé avec l'ID: $projectId\n";

// 3. Mise à jour du projet via PUT
echo "\n3. Mise à jour du projet via PUT\n";

$updateData = [
    'title' => 'Projet de test final mis à jour via PUT',
    'description' => 'Description mise à jour via PUT'
];

$updateProjectResult = makeRequest(
    "$baseUrl/projects/$projectId",
    'PUT',
    $updateData,
    ["Authorization: Bearer $token"]
);

if ($updateProjectResult['code'] >= 200 && $updateProjectResult['code'] < 300) {
    echo "\n✅ Projet mis à jour avec succès via PUT!\n";
} else {
    echo "\n❌ Échec de la mise à jour du projet via PUT.\n";
    echo "\nPassage au test de la route alternative...\n";
}

// 4. Mise à jour du projet via la route POST alternative
echo "\n4. Mise à jour du projet via la route POST alternative\n";

$updateData = [
    'title' => 'Projet de test final mis à jour via POST',
    'description' => 'Description mise à jour via POST'
];

$updateProjectResult = makeRequest(
    "$baseUrl/projects/$projectId/update",
    'POST',
    $updateData,
    ["Authorization: Bearer $token"]
);

if ($updateProjectResult['code'] >= 200 && $updateProjectResult['code'] < 300) {
    echo "\n✅ Projet mis à jour avec succès via la route POST alternative!\n";
} else {
    echo "\n❌ Échec de la mise à jour du projet via la route POST alternative.\n";
    exit(1);
}

// 5. Vérification de la mise à jour
echo "\n5. Vérification de la mise à jour\n";

$getProjectResult = makeRequest(
    "$baseUrl/projects/$projectId",
    'GET',
    [],
    ["Authorization: Bearer $token"]
);

if ($getProjectResult['code'] == 200) {
    echo "\n✅ Projet récupéré avec succès!\n";
    
    // Vérification des données mises à jour
    $title = $getProjectResult['json']['data']['title'] ?? null;
    $description = $getProjectResult['json']['data']['description'] ?? null;
    
    if ($title === $updateData['title'] && $description === $updateData['description']) {
        echo "\n✅ Les données du projet ont été correctement mises à jour!\n";
    } else {
        echo "\n❌ Les données du projet ne correspondent pas aux données mises à jour.\n";
        exit(1);
    }
} else {
    echo "\n❌ Échec de la récupération du projet.\n";
    exit(1);
}

echo "\n🎉 TEST FINAL TERMINÉ AVEC SUCCÈS!\n";