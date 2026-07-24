<?php
namespace MeuSegredo\Controllers;

use MeuSegredo\Core\Controller;
use MeuSegredo\Models\Secret;

class HomeController extends Controller {
    public function index() {
        $secretModel = new Secret();
        $posts = $secretModel->getActive(10, 0);

        $this->render('home/index', [
            'title' => 'MeuSegredo.Shop - Compartilhe seus segredos anonimamente',
            'posts' => $posts,
            'filter' => 'recent'
        ]);
    }

    public function trending() {
        $secretModel = new Secret();
        $posts = $secretModel->getTrending(24, 10);

        $this->render('home/index', [
            'title' => 'Segredos em Alta - MeuSegredo.Shop',
            'posts' => $posts,
            'filter' => 'trending'
        ]);
    }

    public function ranking() {
        $secretModel = new Secret();
        $posts = $secretModel->getRanking(10);

        $this->render('home/ranking', [
            'title' => 'Ranking dos Segredos mais Curtidos - MeuSegredo.Shop',
            'posts' => $posts
        ]);
    }
}
