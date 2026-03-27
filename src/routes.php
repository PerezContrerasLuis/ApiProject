<?php

header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Ruta GET /test
if ($path === '/test' && $method === 'GET') {
    echo json_encode([
        "message" => "Welcome to ApiProject",
        "version" => "1.0.0"
    ]);
    exit;
}

// Ruta 404 por defecto
http_response_code(404);
echo json_encode(['error' => 'Ruta no encontrada']);
exit;
