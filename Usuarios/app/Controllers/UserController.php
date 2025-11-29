<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Token;
use App\Services\TokenService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{

    // ======================
    //  LOGIN
    // ======================
    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        $user = User::where('email', $data['email'])->first();

        if (!$user || $user->password !== $data['password']) {
            $response->getBody()->write(json_encode(["error" => "Credenciales inválidas"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = TokenService::generate($user->id);

        $response->getBody()->write(json_encode([
            "token" => $token->token,
            "role" => $user->role
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }


    // ======================
    // REGISTER
    // ======================
    public function register(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (!isset($data['name'], $data['email'], $data['password'], $data['role'])) {
            $response->getBody()->write(json_encode(["error" => "Datos incompletos"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (User::where('email', $data['email'])->exists()) {
            $response->getBody()->write(json_encode(["error" => "El correo ya existe"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => $data["password"],
            "role" => $data["role"]
        ]);

        $response->getBody()->write(json_encode([
            "message" => "Usuario registrado correctamente",
            "user" => $user
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }


    // ======================
    // GET USERS (ADMIN ONLY)
    // ======================
    public function getUsers(Request $request, Response $response)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $response->getBody()->write(json_encode(["error" => "Token faltante"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $tokenString = explode(" ", $authHeader)[1];
        $token = TokenService::validate($tokenString);

        if (!$token) {
            $response->getBody()->write(json_encode(["error" => "Token inválido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $user = User::find($token->user_id);

        if ($user->role !== "admin") {
            $response->getBody()->write(json_encode(["error" => "Acceso denegado: Solo admins"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $users = User::all();
        $response->getBody()->write($users->toJson());

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }


    // ======================
    // UPDATE USER (ADMIN ONLY)
    // ======================
    public function updateUser(Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $response->getBody()->write(json_encode(["error" => "Token faltante"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $tokenString = explode(" ", $authHeader)[1];
        $token = TokenService::validate($tokenString);

        if (!$token) {
            $response->getBody()->write(json_encode(["error" => "Token inválido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $authUser = User::find($token->user_id);

        if ($authUser->role !== "admin") {
            $response->getBody()->write(json_encode(["error" => "Acceso denegado: Solo admins"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $user = User::find($id);
        if (!$user) {
            $response->getBody()->write(json_encode(["error" => "Usuario no encontrado"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();

        $user->update([
            "name" => $data['name'] ?? $user->name,
            "email" => $data['email'] ?? $user->email,
            "role" => $data['role'] ?? $user->role,
        ]);

        $response->getBody()->write(json_encode([
            "message" => "Usuario actualizado correctamente",
            "user" => $user
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }


    // ======================
    // DELETE USER (ADMIN ONLY)
    // ======================
    public function deleteUser(Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        // 1. Validar token
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $response->getBody()->write(json_encode(["error" => "Token faltante"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $tokenString = explode(" ", $authHeader)[1];
        $token = TokenService::validate($tokenString);

        if (!$token) {
            $response->getBody()->write(json_encode(["error" => "Token inválido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // 2. Verificar rol admin
        $authUser = User::find($token->user_id);

        if ($authUser->role !== "admin") {
            $response->getBody()->write(json_encode(["error" => "Acceso denegado: Solo admins"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // 3. Buscar usuario
        $user = User::find($id);

        if (!$user) {
            $response->getBody()->write(json_encode(["error" => "Usuario no encontrado"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // 4. Eliminar
        $user->delete();

        $response->getBody()->write(json_encode([
            "message" => "Usuario eliminado correctamente"
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // ======================
    // LOGOUT
    // ======================
    public function logout(Request $request, Response $response)
    {
        // 1. Obtener encabezado Authorization
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $response->getBody()->write(json_encode(["error" => "Token faltante"]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $tokenString = explode(" ", $authHeader)[1];

        // 2. Validar token
        $token = TokenService::validate($tokenString);

        if (!$token) {
            $response->getBody()->write(json_encode(["error" => "Token inválido"]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // 3. Eliminar token de la BD (logout real)
        Token::where('token', $tokenString)->delete();

        $response->getBody()->write(json_encode([
            "message" => "Sesión cerrada correctamente"
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
