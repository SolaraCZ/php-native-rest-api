<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Validation\TaskValidator;
use PDO;

class TaskController extends BaseController
{
    private PDO $db;
    private TaskValidator $validator;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->validator = new TaskValidator();
    }

    /**
     * GET /api/tasks - Seznam všech úkolů
     */
    public function index(): void
    {
        $stmt = $this->db->query('SELECT * FROM tasks ORDER BY id DESC');
        $tasks = $stmt->fetchAll();

        // Přetypování completed na boolean pro čisté JSON API
        $formatted = array_map(function ($task) {
            $task['id'] = (int)$task['id'];
            $task['completed'] = (bool)$task['completed'];
            return $task;
        }, $tasks);

        $this->jsonResponse([
            'status' => 'success',
            'count' => count($formatted),
            'data' => $formatted
        ]);
    }

    /**
     * GET /api/tasks/{id} - Detail konkrétního úkolu
     */
    public function show(string $id): void
    {
        $task = $this->findTaskById($id);

        $task['id'] = (int)$task['id'];
        $task['completed'] = (bool)$task['completed'];

        $this->jsonResponse([
            'status' => 'success',
            'data' => $task
        ]);
    }

    /**
     * POST /api/tasks - Vytvoření nového úkolu (Status 201)
     */
    public function store(): void
    {
        $input = $this->getJsonInput();
        $validated = $this->validator->validate($input);

        if ($validated === null) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Validační chyba',
                'errors' => $this->validator->getErrors()
            ], 422);
        }

        $stmt = $this->db->prepare('
            INSERT INTO tasks (title, description, completed, created_at, updated_at) 
            VALUES (:title, :description, :completed, datetime("now"), datetime("now"))
        ');

        $stmt->execute([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'completed' => $validated['completed']
        ]);

        $newId = (int)$this->db->lastInsertId();

        $this->show((string)$newId); // Vrátí nově vytvořený úkol
    }

    /**
     * PUT /api/tasks/{id} - Aktualizace úkolu (Status 200)
     */
    public function update(string $id): void
    {
        $existing = $this->findTaskById($id);
        $input = $this->getJsonInput();

        $validated = $this->validator->validate($input, true);

        if ($validated === null) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Validační chyba',
                'errors' => $this->validator->getErrors()
            ], 422);
        }

        if (empty($validated)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Nebyly zadány žádné změny.'
            ], 400);
        }

        // Dynamické sestavení UPDATE dotazu podle předaných polí
        $fields = [];
        $params = ['id' => (int)$id];

        foreach ($validated as $key => $value) {
            $fields[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        $fields[] = 'updated_at = datetime("now")';

        $sql = 'UPDATE tasks SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $this->show($id);
    }

    /**
     * DELETE /api/tasks/{id} - Smazání úkolu (Status 200)
     */
    public function destroy(string $id): void
    {
        $this->findTaskById($id);

        $stmt = $this->db->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute(['id' => (int)$id]);

        $this->jsonResponse([
            'status' => 'success',
            'message' => "Úkol s ID {$id} byl úspěšně smazán."
        ]);
    }

    /**
     * Pomocná metoda pro bezpečné vyhledání úkolu nebo vrácení 404
     */
    private function findTaskById(string $id): array
    {
        if (!ctype_digit($id)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Neplatné ID úkolu.'], 400);
        }

        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute(['id' => (int)$id]);
        $task = $stmt->fetch();

        if (!$task) {
            $this->jsonResponse(['status' => 'error', 'message' => "Úkol s ID {$id} nebyl nalezen."], 404);
        }

        return $task;
    }
}