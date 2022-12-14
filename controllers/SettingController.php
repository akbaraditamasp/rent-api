<?php

namespace Controller;

use EndyJasmi\Cuid;
use Laminas\Diactoros\UploadedFile;
use Model\Setting;
use Psr\Http\Message\UploadedFileInterface;
use Respect\Validation\Validator as v;
use Siluet\App;
use Siluet\Auth;
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
        Auth::validate();
        $body = Validation::validate([
            "body" =>  v::optional(v::anyOf(v::stringType(), v::intType())),
            "file" => v::image()
        ]);
        App::controller(function () use ($body) {
            $settings = [];
            $db = (Eloquent::getCapsule())->getConnection();

            $db->transaction(
                function () use (&$settings, $body) {
                    foreach ($body as $key => $setting) {
                        /**
                         * @var Setting $set
                         */
                        $set = Setting::firstOrNew(["key" => $key]);
                        if (!($setting instanceof UploadedFile)) {
                            $set->value = $setting;
                            $set->save();
                            $settings[] = $set->toArray();
                        } else {
                            /**
                             * @var UploadedFileInterface $file
                             */
                            $file = $setting;

                            $filename = sprintf(
                                '%s.%s',
                                Cuid::cuid(),
                                pathinfo($file->getClientFilename(), PATHINFO_EXTENSION)
                            );
                            if ($set->value) {
                                $deleted = $set->value;
                            }
                            $set->value = $filename;
                            $set->save();
                            $settings[] = $set->toArray();

                            if ($deleted) {
                                if (file_exists(__DIR__ . "/../uploaded/" . $deleted)) {
                                    unlink(__DIR__ . "/../uploaded/" . $deleted);
                                }
                            }

                            $file->moveTo(__DIR__ . "/../uploaded/" . $filename);
                        }
                    }
                }
            );

            return $settings;
        });
    }
}
