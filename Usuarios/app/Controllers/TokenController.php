<?php
namespace App\Controllers;

use App\Services\TokenService;

class TokenController {

    public function validate($request, $response) {
        $token = $request->getHeaderLine("Authorization");

        $data = TokenService::validateToken($token);

        if (!$data) {
            return $response->withJson(["valid" => false]);
        }

        return $response->withJson([
            "valid" => true,
            "user_id" => $data->user_id
        ]);
    }

    public function logout($request, $response) {
        $token = $request->getHeaderLine("Authorization");

        TokenService::deleteToken($token);

        return $response->withJson(["message" => "Sesión cerrada"]);
    }

    public function generate($request, $response) {
        $body = $request->getParsedBody();

        if (!isset($body["user_id"])) {
            return $response->withJson(["error" => "Falta user_id"], 400);
        }

        $token = TokenService::createToken($body["user_id"]);

        return $response->withJson([
            "message" => "Token generado",
            "token" => $token
        ]);
    }
}
