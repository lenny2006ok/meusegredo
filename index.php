<?php
// index.php

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';

use MeuSegredo\Core\Router;
use MeuSegredo\Core\Security;

Security::initSession();

$router = new Router();

// Rotas públicas
$router->get('/', 'HomeController@index');
$router->get('/trending', 'HomeController@trending');
$router->get('/ranking', 'HomeController@ranking');
$router->get('/secrets/create', 'SecretController@create');
$router->post('/secrets/store', 'SecretController@store');
$router->get('/secrets/success', 'SecretController@success');
$router->get('/secrets/{id}/{slug}', 'SecretController@show');
$router->get('/secrets/category/{category}', 'SecretController@category');

// Rotas AJAX (API)
$router->post('/api/secret/like', 'ApiController@like');
$router->post('/api/secret/dislike', 'ApiController@dislike');
$router->post('/api/comment/store', 'CommentController@store');
$router->post('/api/comment/like', 'ApiController@commentLike');
$router->get('/api/feed/load', 'ApiController@loadMore');
$router->post('/api/secret/report', 'ApiController@report');

// Rotas admin
$router->get('/admin/login', 'AdminController@login');
$router->post('/admin/login', 'AdminController@loginPost');
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->post('/admin/reports/action', 'AdminController@reportAction');
$router->post('/admin/logout', 'AdminController@logout');

// Páginas estáticas
$router->get('/terms', 'PageController@terms');
$router->get('/privacy', 'PageController@privacy');
$router->get('/about', 'PageController@about');

$router->dispatch();
