<?php
namespace App\Middleware;

use App\Models\User;
use App\Models\Token;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthMiddleware {

    public function __invoke(Request $request, Response $response, $next) {

        $token = $request->getHeaderLine("Authorization");

        if (!$token) {
            return $this->error($response, "Token requerido", 401);
        }

        $tokenData = Token::where("token", $token)->first();

        if (!$tokenData) {
            return $this->error($response, "Token inválido", 401);
        }

        $user = User::find($tokenData->user_id);

        if (!$user) {
            return $this->error($response, "Usuario no encontrado", 401);
        }

        $request = $request->withAttribute("user", $user);

        return $next($request, $response);
    }

    private function error($response, $msg, $status) {
        $response->getBody()->write(json_encode(["error" => $msg]));
        return $response->withHeader("Content-Type","application/json")->withStatus($status);
    }
}
