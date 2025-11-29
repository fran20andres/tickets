<?php

use App\Controllers\UserController;
use App\Controllers\TokenController;
use App\Middleware\AuthMiddleware;

// Usuario normal
$app->post('/register', [UserController::class, 'register']);
$app->post('/login', [UserController::class, 'login']);

// CRUD de usuarios (solo con token)
$app->get('/users', [UserController::class, 'index'])->add(new AuthMiddleware());
$app->put('/users/{id}', [UserController::class, 'update'])->add(new AuthMiddleware());
$app->delete('/users/{id}', [UserController::class, 'delete'])->add(new AuthMiddleware());

// Controlador de Token
$app->post('/logout',  [TokenController::class, 'logout'])->add(new AuthMiddleware());
$app->get('/token/validate', [TokenController::class, 'validate'])->add(new AuthMiddleware());
$app->post('/token/generate', [TokenController::class, 'generate']); // sin middleware para pruebas
