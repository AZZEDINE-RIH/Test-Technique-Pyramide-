<?php

// Configuration
$baseUrl = 'http://localhost:8000/api';
$email = 'test_methods_' . time() . '@example.com';
$password = 'password123';

// Fonction pour effectuer des requêtes HTTP
function makeRequest($url, $method = 'GET', $data = [], $headers = [], $options = []) {
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
    
    // Options supplémentaires
    foreach ($options as $option => $value) {
        curl_setopt($curl, $option, $value);
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
echo "\n🚀 TEST DES MÉTHODES HTTP\n";
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
    'title' => 'Projet de test méthodes HTTP',
    'description' => 'Description du projet de test méthodes HTTP'
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

// 3. Test des différentes méthodes HTTP pour la mise à jour
echo "\n3. Test des différentes méthodes HTTP pour la mise à jour\n";

$updateData = [
    'title' => 'Projet mis à jour',
    'description' => 'Description mise à jour'
];

$methods = [
    // Méthode standard PUT
    [
        'name' => 'PUT standard',
        'method' => 'PUT',
        'url' => "$baseUrl/projects/$projectId",
        'data' => $updateData,
        'headers' => ["Authorization: Bearer $token"],
        'options' => []
    ],
    // POST avec _method=PUT
    [
        'name' => 'POST avec _method=PUT',
        'method' => 'POST',
        'url' => "$baseUrl/projects/$projectId?_method=PUT",
        'data' => $updateData,
        'headers' => ["Authorization: Bearer $token"],
        'options' => []
    ],
    // POST avec X-HTTP-Method-Override
    [
        'name' => 'POST avec X-HTTP-Method-Override',
        'method' => 'POST',
        'url' => "$baseUrl/projects/$projectId",
        'data' => $updateData,
        'headers' => [
            "Authorization: Bearer $token",
            "X-HTTP-Method-Override: PUT"
        ],
        'options' => []
    ],
    // PATCH standard
    [
        'name' => 'PATCH standard',
        'method' => 'PATCH',
        'url' => "$baseUrl/projects/$projectId",
        'data' => $updateData,
        'headers' => ["Authorization: Bearer $token"],
        'options' => []
    ],
    // Route directe POST
    [
        'name' => 'Route directe POST',
        'method' => 'POST',
        'url' => "$baseUrl/projects/$projectId/update",
        'data' => $updateData,
        'headers' => ["Authorization: Bearer $token"],
        'options' => []
    ]
];

foreach ($methods as $test) {
    echo "\n🔍 Test: {$test['name']}\n";
    
    $result = makeRequest(
        $test['url'],
        $test['method'],
        $test['data'],
        $test['headers'],
        $test['options'] ?? []
    );
    
    if ($result['code'] >= 200 && $result['code'] < 300) {
        echo "\n✅ {$test['name']} a réussi avec le code {$result['code']}\n";
    } else {
        echo "\n❌ {$test['name']} a échoué avec le code {$result['code']}\n";
        
        // Vérifier si la réponse contient du HTML (indiquant une redirection vers une page web)
        if (strpos($result['response'], '<!DOCTYPE html>') !== false) {
            echo "\n⚠️ La réponse contient du HTML, ce qui suggère une redirection vers une page web\n";
        }
    }
    
    echo "\n-----------------------------------\n";
}

echo "\n🎉 TEST DES MÉTHODES HTTP TERMINÉ!\n";