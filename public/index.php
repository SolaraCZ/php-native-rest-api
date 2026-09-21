<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router\Router;
use App\Controllers\TaskController;

$router = new Router();

// Definice API tras
$router->get('/api/tasks', [TaskController::class, 'index']);
$router->get('/api/tasks/{id}', [TaskController::class, 'show']);

// Health-check endpoint
$router->get('/api/health', function () {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'timestamp' => date('c')]);
});

// Zpracování aktuálního požadavku
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);