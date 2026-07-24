<?php
namespace MeuSegredo\Core;

class Controller {
    protected function render($view, $data = [], $layout = 'main') {
        extract($data);

        $config = require __DIR__ . '/../../config/env.php';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['flash_message'])) {
            $flash_message = $_SESSION['flash_message'];
            $flash_type = $_SESSION['flash_type'] ?? 'info';
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        }

        ob_start();
        $viewFile = __DIR__ . "/../../views/{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<p>Visualização não encontrada: {$view}</p>";
        }
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = __DIR__ . "/../../views/layouts/{$layout}.php";
            if (file_exists($layoutFile)) {
                include $layoutFile;
                return;
            }
        }

        echo $content;
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    protected function setFlash($message, $type = 'success') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
}
