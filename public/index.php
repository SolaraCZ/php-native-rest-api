<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router\Router;
use App\Controllers\TaskController;

$router = new Router();

// CRUD Endpointy
$router->get('/api/tasks', [TaskController::class, 'index']);
$router->get('/api/tasks/{id}', [TaskController::class, 'show']);
$router->post('/api/tasks', [TaskController::class, 'store']);
$router->put('/api/tasks/{id}', [TaskController::class, 'update']);
$router->delete('/api/tasks/{id}', [TaskController::class, 'destroy']);

// Health-check
$router->get('/api/health', function () {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'timestamp' => date('c')]);
});

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);