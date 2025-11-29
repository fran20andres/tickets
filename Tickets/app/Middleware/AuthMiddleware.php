<?php
namespace App\Middleware;

use App\Models\User;
use App\Models\AuthToken;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthMiddleware {

    public function __invoke(Request $request, Response $response, $next) {

        $token = $request->getHeaderLine("Authorization");

        // No se envió token
        if (!$token) {
            $response->getBody()->write(json_encode([
                "error" => "Token requerido"
            ]));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(401);
        }

        // Buscar token en BD
        $tokenData = AuthToken::where("token", $token)->first();

        if (!$tokenData) {
            $response->getBody()->write(json_encode([
                "error" => "Token inválido"
            ]));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(401);
        }

        // Buscar usuario
        $user = User::find($tokenData->user_id);

        if (!$user) {
            $response->getBody()->write(json_encode([
                "error" => "Usuario no encontrado"
            ]));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(401);
        }

        // Inyectar usuario en el Request
        $request = $request->withAttribute("user", $user);

        // Continuar a la siguiente capa
        return $next($request, $response);
    }
}
