<?php
namespace App\Controllers;

use App\Models\User;
use App\Services\TokenService;

class UserController {

    public function register($request, $response) {
        $data = $request->getParsedBody();

        $user = User::create([
            "name" => $data['name'],
            "email" => $data['email'],
            "password" => $data['password'], // opcional: hash
            "role" => $data['role']
        ]);

        return $response->withJson([
            "message" => "Usuario registrado correctamente",
            "user" => $user
        ], 201);
    }

    public function login($request, $response) {
        $data = $request->getParsedBody();

        $user = User::where('email', $data['email'])
                    ->where('password', $data['password'])
                    ->first();

        if (!$user) {
            return $response->withJson(["error" => "Credenciales incorrectas"], 401);
        }

        $token = TokenService::createToken($user->id);

        return $response->withJson([
            "message" => "Login exitoso",
            "token" => $token,
            "user" => $user
        ]);
    }

    public function index($request, $response) {
        return $response->withJson(User::all());
    }

    public function update($request, $response, $args) {
        $user = User::find($args["id"]);
        $data = $request->getParsedBody();

        if (!$user) {
            return $response->withJson(["error" => "Usuario no encontrado"], 404);
        }

        $user->update($data);

        return $response->withJson(["message" => "Usuario actualizado", "user" => $user]);
    }

    public function delete($request, $response, $args) {
        $deleted = User::destroy($args["id"]);

        if (!$deleted) {
            return $response->withJson(["error" => "Usuario no encontrado"], 404);
        }

        return $response->withJson(["message" => "Usuario eliminado"]);
    }
}
