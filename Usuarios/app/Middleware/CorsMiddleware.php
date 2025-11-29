<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;

class CorsMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        // Preflight OPTIONS requests
        if ($request->getMethod() === "OPTIONS") {
            $response = new \Slim\Psr7\Response();
            return $this->addCorsHeaders($response);
        }

        $response = $handler->handle($request);
        return $this->addCorsHeaders($response);
    }

    private function addCorsHeaders(Response $response): Response
    {
        return $response
            ->withHeader("Access-Control-Allow-Origin", "*")
            ->withHeader("Access-Control-Allow-Headers", "Content-Type, Authorization")
            ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS")
            ->withHeader("Access-Control-Allow-Credentials", "true");
    }
}
