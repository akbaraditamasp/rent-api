<?php

namespace Controller;

use EndyJasmi\Cuid;
use Model\Building;
use Model\Pic;
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
                "price" => v::numericVal()->notEmpty()
            ],
            "file" => [
                "pics" => [v::image()]
            ]
        ]);

        App::controller(function () use ($body) {
            $building = new Building();
            (Eloquent::getCapsule())->connection()->transaction(function () use ($building, $body) {
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

                $building->save();

                foreach ($fileData as $file) {
                    $building->pics()->save($file["model"]);
                    $file["file"]->moveTo(__DIR__ . "/../uploaded/" . $file["name"]);
                }
            });

            return $building->toArray() + [
                "pics" => $building->pics()->get()->toArray()
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
                "price" => v::optional(v::numericVal()->notEmpty())
            ],
            "file" => [
                "pic" => v::optional(v::image())
            ]
        ]);
        App::controller(function () use ($id, $body) {
            /**
             * @var Building $building
             */
            $building = Building::findOrFail($id);
            (Eloquent::getCapsule())->connection()->transaction(function () use ($building, $body) {
                $file = $body["pic"];

                $building->name = $body["name"] ?? $building->name;
                $building->address = $body["address"] ?? $building->address;
                $building->facilities = $body["facilities"] ?? $building->facilities;
                $building->price = $body["price"] ?? $building->price;

                if ($file) {
                    $deleteFile = $building->pic;
                    $filename = sprintf(
                        '%s.%s',
                        Cuid::cuid(),
                        pathinfo($file->getClientFilename(), PATHINFO_EXTENSION)
                    );
                    $building->pic = $filename;
                }

                $building->save();

                if ($file) {
                    $file->moveTo(__DIR__ . "/../uploaded/" . $filename);
                }

                if (isset($deleteFile)) {
                    if (file_exists(__DIR__ . "/../uploaded/$deleteFile")) {
                        unlink(__DIR__ . "/../uploaded/$deleteFile");
                    }
                }
            });

            return $building->toArray();
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
                $building->delete();

                if (file_exists(__DIR__ . "/../uploaded/" . $building->pic)) {
                    unlink(__DIR__ . "/../uploaded/" . $building->pic);
                }
            });

            return $building->toArray();
        });
    }

    public function index()
    {
        App::controller(function () {
            $limit = App::$request->getQueryParams()["limit"] ?? 5;
            $page = App::$request->getQueryParams()["page"] ?? 1;
            $offset = (((int) $page) - 1) * ((int) $limit);

            $buildings = Building::with(["pics"])->orderByDesc("created_at");

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
