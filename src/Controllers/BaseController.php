<?php

declare(strict_types=1);

namespace App\Controllers;

abstract class BaseController
{
    /**
     * Bezpečně načte a dekóduje JSON z těla HTTP požadavku.
     */
    protected function getJsonInput(): array
    {
        $rawContent = file_get_contents('php://input');

        if (empty($rawContent)) {
            return [];
        }

        $data = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Neplatný JSON formát: ' . json_last_error_msg()
            ], 400);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Standardizovaná JSON odpověď.
     */
    protected function jsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}