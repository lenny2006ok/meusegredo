<?php
namespace MeuSegredo\Controllers;

use MeuSegredo\Core\Controller;
use MeuSegredo\Core\Security;
use MeuSegredo\Models\Comment;
use MeuSegredo\Helpers\Filter;
use MeuSegredo\Helpers\RateLimiter;

class CommentController extends Controller {
    public function store() {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']);

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::validateCSRF($csrfToken)) {
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
            }
            $this->setFlash('Token CSRF inválido.', 'error');
            $this->redirect('/');
        }

        $rateLimiter = new RateLimiter();
        $config = require __DIR__ . '/../../config/env.php';
        $maxComments = isset($config['MAX_COMMENTS_PER_IP_PER_DAY']) ? (int)$config['MAX_COMMENTS_PER_IP_PER_DAY'] : 50;

        if (!$rateLimiter->canPerform('comment', $maxComments)) {
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => 'Limite de comentários diários atingido.'], 429);
            }
            $this->setFlash('Limite de comentários atingido.', 'error');
            $this->redirect('/');
        }

        $secretId = $_POST['secret_id'] ?? 0;
        $parentId = $_POST['parent_id'] ?? null;
        if (empty($parentId)) {
            $parentId = null;
        }
        $content = $_POST['content'] ?? '';
        $pseudonym = $_POST['pseudonym'] ?? '';

        if (empty($content) || empty($secretId)) {
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => 'Conteúdo é obrigatório.'], 400);
            }
            $this->setFlash('O conteúdo do comentário é obrigatório.', 'error');
            $this->redirect("/secrets/{$secretId}/slug");
        }

        if (strlen($content) > 2000) {
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => 'Comentário muito longo (máximo 2000 caracteres).'], 400);
            }
            $this->setFlash('Comentário muito longo.', 'error');
            $this->redirect("/secrets/{$secretId}/slug");
        }

        if (($config['AUTO_FILTER_ENABLED'] ?? 'true') === 'true') {
            if (Filter::hasOffensive($content)) {
                if ($isAjax) {
                    return $this->json(['success' => false, 'message' => 'O comentário contém termos impróprios.'], 400);
                }
                $this->setFlash('Seu comentário contém termos impróprios.', 'error');
                $this->redirect("/secrets/{$secretId}/slug");
            }
        }

        $commentModel = new Comment();
        $id = $commentModel->createComment($secretId, $parentId, $content, $pseudonym);

        if ($id) {
            $rateLimiter->logAction('comment');
            $newComment = $commentModel->findById($id);

            if ($isAjax) {
                return $this->json([
                    'success' => true,
                    'message' => 'Comentário adicionado com sucesso!',
                    'comment' => $newComment
                ]);
            }

            $this->setFlash('Comentário enviado!', 'success');
            $this->redirect("/secrets/{$secretId}/slug");
        } else {
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => 'Erro interno ao salvar comentário.'], 500);
            }
            $this->setFlash('Erro interno ao salvar comentário.', 'error');
            $this->redirect("/secrets/{$secretId}/slug");
        }
    }
}
