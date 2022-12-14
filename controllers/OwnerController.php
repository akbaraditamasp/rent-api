<?php

namespace Controller;

use Model\User;
use Respect\Validation\Validator as v;
use Siluet\App;
use Siluet\Auth;
use Siluet\Validation;

class OwnerController
{
    public function add()
    {
        // Auth::validate();
        ["username" => $username, "password" => $password] = Validation::validate([
            "body" => [
                "username" => v::stringType()->notEmpty(),
                "password" => v::stringType()->notEmpty()
            ]
        ]);
        App::controller(function () use ($username, $password) {
            $user = new User();
            $user->username = $username;
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->is_owner = true;

            $user->save();

            return $user->toArray();
        });
    }

    public function delete($id)
    {
        Auth::validate();
        App::controller(function () use ($id) {
            $user = User::findOrFail($id);
            $user->delete();

            return $user->toArray();
        });
    }

    public function index()
    {
        Auth::validate();
        App::controller(function () {
            $limit = App::$request->getQueryParams()["limit"] ?? 5;
            $page = App::$request->getQueryParams()["page"] ?? 1;
            $offset = (((int) $page) - 1) * ((int) $limit);

            $user = User::where("is_owner", true)->withCount("buildings");

            return [
                "pageTotal" => ceil($user->count() / ((int) $limit)),
                "rows" => $user->skip($offset)->take($limit)->get()
            ];
        });
    }
}
