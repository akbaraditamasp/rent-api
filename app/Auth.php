<?php

namespace Siluet;

use Carbon\Carbon;
use Model\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Config\Definition\Exception\Exception;

class Auth
{
    public static ?User $user = null;

    public static function makeToken(User $user)
    {
        $payload = [
            "id" => $user->id,
            "exp" => Carbon::now()->addDay()->timestamp
        ];

        $jwt = JWT::encode($payload, $_ENV["JWT_KEY"] ?? "123456", 'HS256');

        return $jwt;
    }

    public static function validate($strict = true, $owner = false)
    {
        try {
            $token = App::$request->getHeaderLine("Authorization");
            $token = explode(" ", $token);

            $decoded = JWT::decode($token[1] ?? "", new Key($_ENV["JWT_KEY"] ?? "123456", 'HS256'));
            $decoded = (array) $decoded;

            static::$user = User::findOrFail($decoded["id"]);
            if (static::$user->is_owner && !$owner) {
                throw new Exception("Unauthorized");
            }
        } catch (\Exception $e) {
            if ($strict) {
                App::$response->getBody()->write(json_encode([
                    "error" => "Unauthorized"
                ]));
                App::$response = App::$response->withStatus(401)->withHeader("Content-Type", "application/json");
                App::finish();
            }
        }
    }
}
