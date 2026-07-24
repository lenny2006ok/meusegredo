<?php
namespace MeuSegredo\Controllers;

use MeuSegredo\Core\Controller;
use MeuSegredo\Core\Security;
use MeuSegredo\Models\Secret;
use MeuSegredo\Helpers\Filter;
use MeuSegredo\Helpers\RateLimiter;
use MeuSegredo\Helpers\Captcha;

class SecretController extends Controller {
    public function create() {
        $this->render('secrets/create', [
            'title' => 'Revelar um Segredo - MeuSegredo.Shop'
        ]);
    }

    public function store() {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRF($csrfToken)) {
            $this->setFlash('Token CSRF inválido ou expirado. Tente novamente.', 'error');
            $this->redirect('/secrets/create');
        }

        $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';
        if (!Captcha::verify($turnstileResponse)) {
            $this->setFlash('Por favor, complete a verificação do Captcha.', 'error');
            $this->redirect('/secrets/create');
        }

        $rateLimiter = new RateLimiter();
        $config = require __DIR__ . '/../../config/env.php';
        $maxPosts = isset($config['MAX_POSTS_PER_IP_PER_DAY']) ? (int)$config['MAX_POSTS_PER_IP_PER_DAY'] : 5;

        if (!$rateLimiter->canPerform('post', $maxPosts)) {
            $this->setFlash('Você atingiu o limite máximo de segredos postados hoje. Tente amanhã!', 'error');
            $this->redirect('/secrets/create');
        }

        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $category = $_POST['category'] ?? 'outros';
        $pseudonym = $_POST['pseudonym'] ?? '';
        $city = $_POST['city'] ?? '';
        $age = $_POST['age'] ?? '';

        if (empty($title) || empty($content)) {
            $this->setFlash('O título e o conteúdo são obrigatórios.', 'error');
            $this->redirect('/secrets/create');
        }

        if (strlen($title) > 150) {
            $this->setFlash('O título deve ter no máximo 150 caracteres.', 'error');
            $this->redirect('/secrets/create');
        }

        if (strlen($content) > 5000) {
            $this->setFlash('O conteúdo deve ter no máximo 5000 caracteres.', 'error');
            $this->redirect('/secrets/create');
        }

        if (($config['AUTO_FILTER_ENABLED'] ?? 'true') === 'true') {
            if (Filter::hasOffensive($title) || Filter::hasOffensive($content)) {
                $this->setFlash('Seu segredo contém palavras bloqueadas pelos nossos filtros de moderação.', 'error');
                $this->redirect('/secrets/create');
            }
        }

        $secretModel = new Secret();
        $id = $secretModel->createSecret($title, $content, $category, $pseudonym, $city, $age);

        if ($id) {
            $rateLimiter->logAction('post');
            $this->setFlash('Seu segredo foi publicado com sucesso e de forma 100% anônima!', 'success');
            $this->redirect("/secrets/success?id={$id}");
        } else {
            $this->setFlash('Ocorreu um erro interno ao salvar seu segredo. Tente novamente mais tarde.', 'error');
            $this->redirect('/secrets/create');
        }
    }

    public function show($params) {
        $id = isset($params['id']) ? (int)$params['id'] : 0;

        $secretModel = new Secret();
        $data = $secretModel->getWithComments($id);

        if (!$data || $data['secret']['status'] !== 'active') {
            http_response_code(404);
            $this->render('not-found', [
                'title' => 'Segredo não encontrado - MeuSegredo.Shop'
            ]);
            return;
        }

        $this->render('secrets/view', [
            'title' => $data['secret']['title'] . ' - MeuSegredo.Shop',
            'secret' => $data['secret'],
            'comments' => $data['comments']
        ]);
    }

    public function category($params) {
        $category = isset($params['category']) ? Security::sanitize($params['category']) : 'outros';

        $secretModel = new Secret();
        $posts = $secretModel->getByCategory($category, 10, 0);

        $this->render('home/index', [
            'title' => 'Categoria ' . ucfirst($category) . ' - MeuSegredo.Shop',
            'posts' => $posts,
            'filter' => 'category',
            'selectedCategory' => $category
        ]);
    }

    public function success() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $this->render('secrets/success', [
            'title' => 'Segredo enviado! - MeuSegredo.Shop',
            'id' => $id
        ]);
    }
}
