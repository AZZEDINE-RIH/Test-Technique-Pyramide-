<?php

// Configuration
$baseUrl = 'http://localhost:8000/api';
$email = 'test_simple_' . time() . '@example.com';
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
    
    // Activer le débogage
    curl_setopt($curl, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($curl, CURLOPT_STDERR, $verbose);
    
    // Exécution
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    // Récupérer les informations de débogage
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    curl_close($curl);
    
    echo "\nCode HTTP: $httpCode\n";
    if ($error) {
        echo "Erreur: $error\n";
    }
    
    echo "\nDébogage cURL:\n$verboseLog\n";
    
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
echo "\n🚀 TEST SIMPLE DE MISE À JOUR D'UN PROJET\n";
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
    'title' => 'Projet de test simple',
    'description' => 'Description du projet de test simple'
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

// 3. Récupération du projet
echo "\n3. Récupération du projet\n";

$getProjectResult = makeRequest(
    "$baseUrl/projects/$projectId",
    'GET',
    [],
    ["Authorization: Bearer $token"]
);

if ($getProjectResult['code'] != 200) {
    echo "\n❌ Échec de la récupération du projet. Arrêt du test.\n";
    exit(1);
}

echo "\n✅ Projet récupéré avec succès!\n";

// 4. Mise à jour du projet avec POST et _method=PUT
echo "\n4. Mise à jour du projet avec POST et _method=PUT\n";

$updateData = [
    'title' => 'Projet de test simple mis à jour',
    'description' => 'Description mise à jour',
    '_method' => 'PUT'
];

$updateProjectResult = makeRequest(
    "$baseUrl/projects/$projectId",
    'POST',
    $updateData,
    ["Authorization: Bearer $token"]
);

if ($updateProjectResult['code'] >= 200 && $updateProjectResult['code'] < 300) {
    echo "\n✅ Projet mis à jour avec succès via POST et _method=PUT!\n";
} else {
    echo "\n❌ Échec de la mise à jour du projet via POST et _method=PUT.\n";
}

// 5. Mise à jour du projet avec X-HTTP-Method-Override
echo "\n5. Mise à jour du projet avec X-HTTP-Method-Override\n";

$updateData = [
    'title' => 'Projet de test simple mis à jour via X-HTTP-Method-Override',
    'description' => 'Description mise à jour via X-HTTP-Method-Override'
];

$updateProjectResult = makeRequest(
    "$baseUrl/projects/$projectId",
    'POST',
    $updateData,
    [
        "Authorization: Bearer $token",
        "X-HTTP-Method-Override: PUT"
    ]
);

if ($updateProjectResult['code'] >= 200 && $updateProjectResult['code'] < 300) {
    echo "\n✅ Projet mis à jour avec succès via X-HTTP-Method-Override!\n";
} else {
    echo "\n❌ Échec de la mise à jour du projet via X-HTTP-Method-Override.\n";
}

// 6. Mise à jour du projet avec PATCH
echo "\n6. Mise à jour du projet avec PATCH\n";

$updateData = [
    'title' => 'Projet de test simple mis à jour via PATCH',
    'description' => 'Description mise à jour via PATCH'
];

$updateProjectResult = makeRequest(
    "$baseUrl/projects/$projectId",
    'PATCH',
    $updateData,
    ["Authorization: Bearer $token"]
);

if ($updateProjectResult['code'] >= 200 && $updateProjectResult['code'] < 300) {
    echo "\n✅ Projet mis à jour avec succès via PATCH!\n";
} else {
    echo "\n❌ Échec de la mise à jour du projet via PATCH.\n";
}

echo "\n🎉 TEST SIMPLE TERMINÉ!\n";