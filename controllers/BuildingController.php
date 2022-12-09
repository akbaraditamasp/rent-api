<?php

namespace Controller;

use EndyJasmi\Cuid;
use Model\Building;
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
        Validation::validate([
            "body" => [
                "name" => v::stringType()->notEmpty(),
                "address" => v::stringType()->notEmpty(),
                "facilities" => v::arrayType()->each(v::stringType()->notEmpty()),
                "price" => v::numericVal()->notEmpty()
            ],
            "file" => [
                "pic" => v::optional(v::objectType()->attribute("file", v::image()))
            ]
        ]);
        App::controller(function () {
            $building = new Building();
            (Eloquent::getCapsule())->connection()->transaction(function () use ($building) {
                $body = App::$request->getParsedBody();
                $file = App::$request->getUploadedFiles()["pic"] ?? null;


                $building->name = $body["name"];
                $building->address = $body["address"];
                $building->facilities = $body["facilities"];
                $building->price = $body["price"];

                if ($file) {
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
            });

            return $building->toArray();
        });
    }
    
    public function delete($id) {
        Auth::validate();
        App::controller(function() use ($id) {
            /**
             * @var Building $building
             */
            $building = Building::findOrFail($id);

            (Eloquent::getCapsule())->getConnection()->transaction(function() use ($building) {
                $building->delete();

                if(file_exists(__DIR__."/../uploaded/".$building->pic)) {
                    unlink(__DIR__."/../uploaded/".$building->pic);
                }
            });

            return $building->toArray();
        });
    }

    public function index() {
        App::controller(function() {
            $limit = App::$request->getQueryParams()["limit"] ?? 5;
            $page = App::$request->getQueryParams()["page"] ?? 1;
            $offset = (((int) $page) - 1) * ((int) $limit);

            $buildings = Building::orderByDesc("created_at");

            return [
                "pageTotal" => ceil($buildings->count() / ((int) $limit)),
                "rows" => $buildings->get()
            ];
        });
    }
}
