<?php
namespace MeuSegredo\Core;

class Router {
    private $routes = [];

    public function get($route, $controllerAction) {
        $this->addRoute('GET', $route, $controllerAction);
    }

    public function post($route, $controllerAction) {
        $this->addRoute('POST', $route, $controllerAction);
    }

    private function addRoute($method, $route, $controllerAction) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . rtrim($pattern, '/') . '/?$#';
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controllerAction' => $controllerAction
        ];
    }

    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        $requestUri = rtrim($requestUri, '/');
        if (empty($requestUri)) {
            $requestUri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                list($controllerName, $action) = explode('@', $route['controllerAction']);
                $fullControllerName = "MeuSegredo\\Controllers\\" . $controllerName;

                if (class_exists($fullControllerName)) {
                    $controllerInstance = new $fullControllerName();
                    if (method_exists($controllerInstance, $action)) {
                        call_user_func_array([$controllerInstance, $action], [$params]);
                        return;
                    }
                }

                $this->sendNotFound();
                return;
            }
        }

        $this->sendNotFound();
    }

    private function sendNotFound() {
        http_response_code(404);
        $notFoundFile = __DIR__ . '/../../views/not-found.php';
        if (file_exists($notFoundFile)) {
            include $notFoundFile;
        } else {
            echo "<!DOCTYPE html><html><head><title>404 - Não Encontrado</title><style>body { background-color: #0a0a0a; color: #f5f5f5; font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; } a { color: #e74c3c; text-decoration: none; }</style></head><body><h1>404 - Não Encontrado</h1><p>O segredo que você procura não pôde ser revelado.</p><p><a href='/'>Voltar ao início</a></p></body></html>";
        }
    }
}
