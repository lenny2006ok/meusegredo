<?php
namespace MeuSegredo\Controllers;

use MeuSegredo\Core\Controller;
use MeuSegredo\Models\Secret;
use MeuSegredo\Models\Comment;
use MeuSegredo\Models\Report;

class ApiController extends Controller {
    public function like() {
        $id = $_POST['id'] ?? 0;
        if (!$id) return $this->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $secretModel = new Secret();
        $success = $secretModel->handleLikeDislike($id, 'like');

        if ($success) {
            $secret = $secretModel->findById($id);
            return $this->json(['success' => true, 'likes' => $secret['likes']]);
        }
        return $this->json(['success' => false, 'message' => 'Você já votou neste segredo.'], 400);
    }

    public function dislike() {
        $id = $_POST['id'] ?? 0;
        if (!$id) return $this->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $secretModel = new Secret();
        $success = $secretModel->handleLikeDislike($id, 'dislike');

        if ($success) {
            $secret = $secretModel->findById($id);
            return $this->json(['success' => true, 'dislikes' => $secret['dislikes']]);
        }
        return $this->json(['success' => false, 'message' => 'Você já votou neste segredo.'], 400);
    }

    public function commentLike() {
        $id = $_POST['id'] ?? 0;
        if (!$id) return $this->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $commentModel = new Comment();
        $success = $commentModel->handleLikeDislike($id, 'like');

        if ($success) {
            $comment = $commentModel->findById($id);
            return $this->json(['success' => true, 'likes' => $comment['likes']]);
        }
        return $this->json(['success' => false, 'message' => 'Você já votou neste comentário.'], 400);
    }

    public function report() {
        $secretId = $_POST['secret_id'] ?? null;
        $commentId = $_POST['comment_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        $details = $_POST['details'] ?? '';

        if (empty($reason)) {
            return $this->json(['success' => false, 'message' => 'O motivo é obrigatório.'], 400);
        }

        $reportModel = new Report();
        if ($secretId) {
            $reportModel->reportSecret($secretId, $reason, $details);
        } elseif ($commentId) {
            $reportModel->reportComment($commentId, $reason, $details);
        } else {
            return $this->json(['success' => false, 'message' => 'ID do segredo ou do comentário é necessário.'], 400);
        }

        return $this->json(['success' => true, 'message' => 'Sua denúncia foi registrada e será analisada pelos moderadores. Obrigado!']);
    }

    public function loadMore() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 2;
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $secretModel = new Secret();
        if ($category) {
            $posts = $secretModel->getByCategory($category, $limit, $offset);
        } else {
            $posts = $secretModel->getActive($limit, $offset);
        }

        return $this->json(['success' => true, 'posts' => $posts]);
    }
}
