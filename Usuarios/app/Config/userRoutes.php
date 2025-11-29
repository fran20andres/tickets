<?php

use App\Controllers\UserController;

return function ($app) {

    // LOGIN
    $app->post('/login', [UserController::class, 'login']);

    // REGISTER
    $app->post('/register', [UserController::class, 'register']);

    // LIST USERS (ADMIN ONLY)
    $app->get('/users', [UserController::class, 'getUsers']);

    // UPDATE USER (ADMIN ONLY)
    $app->put('/users/{id}', [UserController::class, 'updateUser']);

    // DELETE USER (ADMIN ONLY)
    $app->delete('/users/{id}', [UserController::class, 'deleteUser']);
    
    // LOGOUT
    $app->post('/logout', [UserController::class, 'logout']);

};
