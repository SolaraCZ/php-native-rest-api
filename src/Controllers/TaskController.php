<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;

class TaskController extends BaseController
{
    public function index(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM tasks ORDER BY id DESC');
        $tasks = $stmt->fetchAll();

        $this->jsonResponse([
            'status' => 'success',
            'data' => $tasks
        ]);
    }

    public function show(string $id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();

        if (!$task) {
            $this->jsonResponse(['error' => 'Task not found'], 404);
        }

        $this->jsonResponse([
            'status' => 'success',
            'data' => $task
        ]);
    }
}