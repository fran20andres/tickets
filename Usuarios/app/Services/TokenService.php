<?php
namespace App\Services;

use App\Models\Token;

class TokenService {

    public static function generate($userId) {

        $token = bin2hex(random_bytes(32));

        return Token::create([
            "user_id" => $userId,
            "token" => $token
        ]);
    }

    public static function invalidate($token) {
        Token::where("token", $token)->delete();
    }

    public static function validate($token) {
        return Token::where("token", $token)->first();
    }
}
