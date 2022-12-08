<?php

namespace Controller;

use Model\Setting;
use Respect\Validation\Validator as v;
use Siluet\App;
use Siluet\Eloquent;
use Siluet\Validation;

class SettingController
{
    public function index()
    {
        App::controller(function () {
            $settings = Setting::all();

            return $settings->toArray();
        });
    }

    public function save()
    {
        Validation::validate([
            "body" => [
                "settings" => v::arrayType()
                    ->each(
                        v::arrayType()
                            ->keySet(
                                v::key("key", v::stringType()->notEmpty()),
                                v::key("value", v::optional(v::anyOf(v::stringType(), v::intType(), v::json()))),
                            )
                    )
            ]
        ]);
        App::controller(function () {
            $settings = [];
            $db = (Eloquent::getCapsule())->getConnection();

            $db->transaction(function () use (&$settings) {
                foreach (App::$request->getParsedBody()["settings"] as $setting) {
                    /**
                     * @var Setting
                     */
                    $set = Setting::firstOrNew(["key" => $setting["key"]]);
                    $set->value = $setting["value"];
                    $set->save();
                    $settings[] = $set->toArray();
                }
            });

            return $settings;
        });
    }
}
