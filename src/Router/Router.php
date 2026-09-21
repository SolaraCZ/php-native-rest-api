<?php

declare(strict_types=1);

namespace App\Router;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $requestMethod, string $requestUri): void
    {
        // Odstranění query parametrů (/api/tasks?sort=desc -> /api/tasks)
        $uri = parse_url($requestUri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        $methodMatched = false;

        foreach ($this->routes as $route) {
            // Převod patternu /api/tasks/{id} na regulární výraz ^/api/tasks/([^/]+)$
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . rtrim($pattern, '/') . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                if ($route['method'] === $requestMethod) {
                    array_shift($matches); // Odstranění celé shody, ponechání jen extrahovaných parametrů
                    $this->executeHandler($route['handler'], $matches);
                    return;
                }
                $methodMatched = true;
            }
        }

        if ($methodMatched) {
            $this->jsonResponse(['error' => 'Method Not Allowed'], 405);
        } else {
            $this->jsonResponse(['error' => 'Route Not Found'], 404);
        }
    }

    private function executeHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        call_user_func_array($handler, $params);
    }

    private function jsonResponse(array $data, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }
}