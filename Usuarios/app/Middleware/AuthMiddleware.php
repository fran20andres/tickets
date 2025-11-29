<?php
namespace App\Middleware;

use App\Models\User;
use App\Services\TokenService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware {

    private $requireAdmin;

    public function __construct($requireAdmin = false) {
        $this->requireAdmin = $requireAdmin;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {

        $authHeader = $request->getHeaderLine("Authorization");

        if (!$authHeader) {
            return $this->error("Token requerido", 401);
        }

        // Permitir formato "Bearer token123"
        $token = str_replace("Bearer ", "", $authHeader);

        $tokenData = TokenService::validate($token);

        if (!$tokenData) {
            return $this->error("Token inválido", 401);
        }

        $user = User::find($tokenData->user_id);

        if (!$user) {
            return $this->error("Usuario no encontrado", 401);
        }

        // Validar rol admin si se requiere
        if ($this->requireAdmin && $user->role !== "admin") {
            return $this->error("Acceso restringido solo para administradores", 403);
        }

        // ------------------------------
        // INYECTAR USUARIO EN EL REQUEST
        // ------------------------------
        $request = $request->withAttribute("user", $user);

        // Pasar el request modificado al siguiente middleware o ruta
        return $handler->handle($request);
    }

    private function error($message, $status): Response {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(["error" => $message]));
        return $response
            ->withStatus($status)
            ->withHeader("Content-Type", "application/json");
    }
}
