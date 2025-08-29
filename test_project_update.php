<?php

// Configuration
$baseUrl = 'http://localhost:8000/api';
$email = 'test_update_' . time() . '@example.com';
$password = 'password123';

// Fonction pour effectuer des requêtes HTTP
function makeRequest($url, $method = 'GET', $data = [], $headers = []) {
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
    
    return [
        'code' => $httpCode,
        'response' => $response ? json_decode($response, true) : $response,
        'error' => $error,
        'verbose' => $verboseLog
    ];
}

// Fonction pour afficher les résultats
function displayResult($step, $result) {
    echo "\n=== $step ===\n";
    echo "Code HTTP: {$result['code']}\n";
    
    if ($result['error']) {
        echo "Erreur: {$result['error']}\n";
    }
    
    if (isset($result['verbose'])) {
        echo "\nDébogage cURL:\n{$result['verbose']}\n";
    }
    
    if ($result['response']) {
        if (is_array($result['response'])) {
            echo "\nRéponse JSON: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "\nRéponse brute:\n{$result['response']}\n";
        }
    }
    
    return $result['code'] >= 200 && $result['code'] < 300;
}

// 1. Enregistrement d'un utilisateur
echo "\n🚀 TEST DE MISE À JOUR D'UN PROJET\n";
echo "\n1. Enregistrement d'un utilisateur\n";

$registerData = [
    'name' => 'Test User',
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password
];

$registerResult = makeRequest("$baseUrl/register", 'POST', $registerData);
$success = displayResult('Enregistrement', $registerResult);

if (!$success) {
    echo "\n❌ Échec de l'enregistrement. Arrêt du test.\n";
    exit(1);
}

// Récupération du token
$token = $registerResult['response']['token'] ?? null;

if (!$token) {
    echo "\n❌ Pas de token reçu. Arrêt du test.\n";
    exit(1);
}

echo "\n✅ Token reçu: $token\n";

// 2. Création d'un projet
echo "\n2. Création d'un projet\n";

$projectData = [
    'title' => 'Projet de test',
    'description' => 'Description du projet de test'
];

$createProjectResult = makeRequest(
    "$baseUrl/projects",
    'POST',
    $projectData,
    ["Authorization: Bearer $token"]
);

$success = displayResult('Création du projet', $createProjectResult);

if (!$success) {
    echo "\n❌ Échec de la création du projet. Arrêt du test.\n";
    exit(1);
}

// Récupération de l'ID du projet
$projectId = $createProjectResult['response']['data']['id'] ?? null;

if (!$projectId) {
    echo "\n❌ Pas d'ID de projet reçu. Arrêt du test.\n";
    exit(1);
}

echo "\n✅ Projet créé avec l'ID: $projectId\n";

// 3. Mise à jour du projet
echo "\n3. Mise à jour du projet\n";

$updateData = [
    'title' => 'Projet de test mis à jour',
    'description' => 'Description mise à jour',
    '_method' => 'PUT'
];

// Essayer avec POST et _method=PUT au lieu de PUT directement
$updateProjectResult = makeRequest(
    "$baseUrl/projects/$projectId",
    'POST',
    $updateData,
    ["Authorization: Bearer $token"]
);

// Si cela échoue, afficher plus d'informations sur l'erreur
if ($updateProjectResult['code'] >= 400) {
    echo "\nTentative avec POST et _method=PUT a échoué. Essayons avec X-HTTP-Method-Override...\n";
    
    // Essayer avec X-HTTP-Method-Override
    $updateProjectResult = makeRequest(
        "$baseUrl/projects/$projectId",
        'POST',
        [
            'title' => 'Projet de test mis à jour',
            'description' => 'Description mise à jour'
        ],
        [
            "Authorization: Bearer $token",
            "X-HTTP-Method-Override: PUT"
        ]
    );
}

$success = displayResult('Mise à jour du projet', $updateProjectResult);

if (!$success) {
    echo "\n❌ Échec de la mise à jour du projet.\n";
    echo "\nVérifiez les logs Laravel pour plus de détails: storage/logs/laravel.log\n";
    exit(1);
}

echo "\n✅ Projet mis à jour avec succès!\n";

// 4. Vérification de la mise à jour
echo "\n4. Vérification de la mise à jour\n";

$getProjectResult = makeRequest(
    "$baseUrl/projects/$projectId",
    'GET',
    [],
    ["Authorization: Bearer $token"]
);

$success = displayResult('Récupération du projet mis à jour', $getProjectResult);

if (!$success) {
    echo "\n❌ Échec de la récupération du projet mis à jour.\n";
    exit(1);
}

// Vérification des données mises à jour
$updatedTitle = $getProjectResult['response']['data']['title'] ?? null;
$updatedDescription = $getProjectResult['response']['data']['description'] ?? null;

if ($updatedTitle === $updateData['title'] && $updatedDescription === $updateData['description']) {
    echo "\n✅ Les données du projet ont été correctement mises à jour!\n";
} else {
    echo "\n❌ Les données du projet ne correspondent pas aux données mises à jour.\n";
    exit(1);
}

echo "\n🎉 TEST DE MISE À JOUR D'UN PROJET RÉUSSI!\n";