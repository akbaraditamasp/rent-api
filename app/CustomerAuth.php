<?php

namespace Siluet;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Model\Customer;

class CustomerAuth
{
    public static ?Customer $user = null;

    public static function makeToken(Customer $user)
    {
        $payload = [
            "id_user" => $user->id
        ];

        $jwt = JWT::encode($payload, $_ENV["JWT_KEY"] ?? "123456", 'HS256');

        return $jwt;
    }

    public static function validate(bool $strict = true)
    {
        try {
            $token = App::$request->getHeaderLine("Authorization");
            $token = explode(" ", $token);

            $decoded = JWT::decode($token[1] ?? "", new Key($_ENV["JWT_KEY"] ?? "123456", 'HS256'));
            $decoded = (array) $decoded;

            static::$user = Customer::findOrFail($decoded["id_user"]);
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
