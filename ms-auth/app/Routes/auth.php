<?php

use App\Controllers\AuthController;

$controller = new AuthController();

$app->post('/login', [$controller, 'login']);
$app->post('/logout', [$controller, 'logout']);
$app->get('/validar', [$controller, 'validar']);

