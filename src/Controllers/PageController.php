<?php
namespace MeuSegredo\Controllers;

use MeuSegredo\Core\Controller;

class PageController extends Controller {
    public function terms() {
        $this->render('pages/terms', [
            'title' => 'Termos de Uso - MeuSegredo.Shop'
        ]);
    }

    public function privacy() {
        $this->render('pages/privacy', [
            'title' => 'Política de Privacidade - MeuSegredo.Shop'
        ]);
    }

    public function about() {
        $this->render('pages/about', [
            'title' => 'Sobre Nós - MeuSegredo.Shop'
        ]);
    }
}
