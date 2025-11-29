<?php
namespace App\Controllers;

use App\Models\User;
use App\Services\TokenService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthController {

    public function register(Request $req, Response $res) {

        // Leer JSON manualmente
        $body = $req->getBody()->getContents();
        $data = json_decode($body, true);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => password_hash($data["password"], PASSWORD_DEFAULT),
            "role" => $data["role"]
        ]);

        $res->getBody()->write(json_encode($user));
        return $res->withHeader("Content-Type", "application/json");
    }

    public function login(Request $req, Response $res) {

        // Leer JSON manualmente
        $body = $req->getBody()->getContents();
        $data = json_decode($body, true);

        $user = User::where("email", $data["email"])->first();

        if (!$user || !password_verify($data["password"], $user->password)) {
            $res->getBody()->write(json_encode(["error" => "Credenciales inválidas"]));
            return $res
                ->withHeader("Content-Type", "application/json")
                ->withStatus(401);
        }

        // Generar token
        $token = TokenService::generate($user->id);

        $respuesta = [
            "message" => "Login exitoso",
            "token" => $token->token,
            "user" => $user
        ];

        $res->getBody()->write(json_encode($respuesta));
        return $res->withHeader("Content-Type", "application/json");
    }

    public function logout(Request $req, Response $res) {

        // El token se toma del Header "Authorization"
        $token = $req->getHeaderLine("Authorization");

        if ($token) {
            TokenService::invalidate($token);
        }

        $res->getBody()->write(json_encode(["message" => "Logout exitoso"]));
        return $res->withHeader("Content-Type", "application/json");
    }
}
