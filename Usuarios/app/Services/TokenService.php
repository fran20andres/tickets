<?php
namespace App\Services;

use App\Models\AuthToken;

class TokenService {

    public static function generate($userId) {

        $token = bin2hex(random_bytes(32));

        return AuthToken::create([
            "user_id" => $userId,
            "token" => $token
        ]);
    }

    public static function invalidate($token) {
        AuthToken::where("token", $token)->delete();
    }

    public static function validate($token) {
        return AuthToken::where("token", $token)->first();
    }
}
