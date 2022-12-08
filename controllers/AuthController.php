<?php

namespace Controller;

use Model\User;
use Respect\Validation\Validator as v;
use Siluet\App;
use Siluet\Auth;
use Siluet\Validation;

class AuthController
{
    public function login()
    {
        Validation::validate([
            "query" => [
                "username" => v::stringType()->notEmpty(),
                "password" => v::stringType()->notEmpty(),
            ]
        ]);
        App::controller(function () {
            /**
             * @var User
             */
            $user = User::where("username", App::$request->getQueryParams()["username"])->firstOrFail();
            if (!password_verify(App::$request->getQueryParams()["password"], $user->password)) {
                App::$response = App::$response->withStatus(401);
                return ["error" => "Unauthorized"];
            }

            $token = Auth::makeToken($user);

            return $user->toArray() + ["token" => $token];
        });
    }
}
