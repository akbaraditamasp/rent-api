<?php

namespace Controller;

use Model\Customer;
use Model\User;
use Respect\Validation\Validator as v;
use Siluet\App;
use Siluet\Auth;
use Siluet\CustomerAuth;
use Siluet\Validation;

class AuthController
{
    public function login()
    {
        $query = Validation::validate([
            "query" => [
                "username" => v::stringType()->notEmpty(),
                "password" => v::stringType()->notEmpty(),
            ]
        ]);
        App::controller(function () use ($query) {
            /**
             * @var User
             */
            $user = User::where("username", $query["username"])->firstOrFail();
            if (!password_verify($query["password"], $user->password)) {
                App::$response = App::$response->withStatus(401);
                return ["error" => "Unauthorized"];
            }

            $token = Auth::makeToken($user);

            return $user->toArray() + ["token" => $token];
        });
    }

    public function customer_login()
    {
        $query = Validation::validate([
            "query" => [
                "email" => v::stringType()->notEmpty(),
                "password" => v::stringType()->notEmpty(),
            ]
        ]);
        App::controller(function () use ($query) {
            /**
             * @var Customer
             */
            $user = Customer::where("email", $query["email"])->firstOrFail();
            if (!password_verify($query["password"], $user->password)) {
                App::$response = App::$response->withStatus(401);
                return ["error" => "Unauthorized"];
            }

            $token = CustomerAuth::makeToken($user);

            return $user->toArray() + ["token" => $token];
        });
    }
}
