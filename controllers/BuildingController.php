<?php

namespace Controller;

use EndyJasmi\Cuid;
use Model\Building;
use Model\Pic;
use Model\User;
use Respect\Validation\Validator as v;
use Siluet\App;
use Siluet\Auth;
use Siluet\Eloquent;
use Siluet\Validation;

class BuildingController
{
    public function add()
    {
        Auth::validate();
        $body = Validation::validate([
            "body" => [
                "name" => v::stringType()->notEmpty(),
                "address" => v::stringType()->notEmpty(),
                "facilities" => v::arrayType()->each(v::stringType()->notEmpty()),
                "price" => v::numericVal()->notEmpty(),
                "user_id" => v::numericVal()->notEmpty(),
            ],
            "file" => [
                "pics" => [v::optional(v::arrayType()), v::image()]
            ]
        ]);

        App::controller(function () use ($body) {
            /**
             * @var User $owner
             */
            $owner = User::where("id", $body["user_id"])->where("is_owner", true)->firstOrFail();

            $building = new Building();
            (Eloquent::getCapsule())->connection()->transaction(function () use ($building, $body, $owner) {
                $files = $body["pics"];
                $fileData = [];

                $building->name = $body["name"];
                $building->address = $body["address"];
                $building->facilities = $body["facilities"];
                $building->price = $body["price"];

                if (count($files)) {
                    foreach ($files as $file) {
                        $filename = sprintf(
                            '%s.%s',
                            Cuid::cuid(),
                            pathinfo($file->getClientFilename(), PATHINFO_EXTENSION)
                        );
                        $fileTmp = new Pic();
                        $fileTmp->path = $filename;

                        $fileData[] = [
                            "name" => $filename,
                            "file" => $file,
                            "model" => $fileTmp
                        ];
                    }
                }

                $owner->buildings()->save($building);

                foreach ($fileData as $file) {
                    $building->pics()->save($file["model"]);
                    $file["file"]->moveTo(__DIR__ . "/../uploaded/" . $file["name"]);
                }
            });

            return $building->toArray() + [
                "pics" => $building->pics()->get()->toArray(),
                "owner" => $owner->toArray()
            ];
        });
    }

    public function edit($id)
    {
        Auth::validate();
        $body = Validation::validate([
            "body" => [
                "name" => v::optional(v::stringType()->notEmpty()),
                "address" => v::optional(v::stringType()->notEmpty()),
                "facilities" => v::optional(v::arrayType()->each(v::stringType()->notEmpty())),
                "price" => v::optional(v::numericVal()->notEmpty()),
                "arrangePics" => v::optional(v::arrayType()->each(v::numericVal())),
                "user_id" => v::optional(v::numericVal()->notEmpty()),
            ],
            "file" => [
                "pics" => [v::optional(v::arrayType()), v::image()]
            ]
        ]);

        App::controller(function () use ($id, $body) {
            /**
             * @var Building $building
             */
            $building = Building::findOrFail($id);

            /**
             * @var User $owner
             */
            $owner = $building->owner;
            if ($body["user_id"]) {
                $owner = User::where("id", $body["user_id"])->where("is_owner", true)->firstOrFail();
            }
            (Eloquent::getCapsule())->connection()->transaction(function () use ($building, $body, $owner) {
                $files = $body["pics"];

                $building->name = $body["name"] ?? $building->name;
                $building->address = $body["address"] ?? $building->address;
                $building->facilities = $body["facilities"] ?? $building->facilities;
                $building->price = $body["price"] ?? $building->price;
                $building->user_id = $owner->id;

                if (count($files)) {
                    foreach ($files as $file) {
                        $filename = sprintf(
                            '%s.%s',
                            Cuid::cuid(),
                            pathinfo($file->getClientFilename(), PATHINFO_EXTENSION)
                        );
                        $fileTmp = new Pic();
                        $fileTmp->path = $filename;

                        $fileData[] = [
                            "name" => $filename,
                            "file" => $file,
                            "model" => $fileTmp
                        ];
                    }
                }

                $building->save();

                $pics = $building->pics()->get();
                if ($body["arrangePics"]) {
                    foreach ($pics as $pic) {
                        if (!in_array($pic->id, $body["arrangePics"])) {
                            $pic->delete();
                            if (file_exists(__DIR__ . "/../uploaded/" . $pic->path)) {
                                unlink(__DIR__ . "/../uploaded/" . $pic->path);
                            }
                        }
                    }
                }

                foreach ($fileData as $file) {
                    $building->pics()->save($file["model"]);
                    $file["file"]->moveTo(__DIR__ . "/../uploaded/" . $file["name"]);
                }
            });

            return [
                "pics" => $building->pics()->get()->toArray(),
                "owner" => $owner->toArray()
            ] + $building->toArray();
        });
    }

    public function delete($id)
    {
        Auth::validate();
        App::controller(function () use ($id) {
            /**
             * @var Building $building
             */
            $building = Building::findOrFail($id);

            (Eloquent::getCapsule())->getConnection()->transaction(function () use ($building) {
                $pics = $building->pics()->get();
                foreach ($pics as $pic) {
                    if (file_exists(__DIR__ . "/../uploaded/" . $pic->path)) {
                        unlink(__DIR__ . "/../uploaded/" . $pic->path);
                    }
                    $pic->delete();
                }

                $building->delete();
            });

            return $building->toArray();
        });
    }

    public function index()
    {
        Auth::validate(false, true);
        App::controller(function () {
            $limit = App::$request->getQueryParams()["limit"] ?? 5;
            $page = App::$request->getQueryParams()["page"] ?? 1;
            $offset = (((int) $page) - 1) * ((int) $limit);

            $buildings = Building::with(["pics", "owner"])->orderByDesc("created_at");
            if (Auth::$user->is_owner) {
                $buildings = Building::where("user_id", Auth::$user->id);
            }

            return [
                "pageTotal" => ceil($buildings->count() / ((int) $limit)),
                "rows" => $buildings->skip($offset)->take($limit)->get()
            ];
        });
    }

    public function getId($id)
    {
        App::controller(function () use ($id) {
            /**
             * @var Building $building
             */
            $building = Building::where("id", $id)->with(["pics"])->firstOrFail();

            return $building->toArray();
        });
    }
}
