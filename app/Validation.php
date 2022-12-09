<?php

namespace Siluet;

use EndyJasmi\Cuid;
use Laminas\Diactoros\UploadedFile;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

class Validation
{
    public static function validate(array $rules)
    {
        $body = App::$request->getParsedBody();
        $query = App::$request->getQueryParams();
        $file = App::$request->getUploadedFiles();

        $data = [];
        try {
            foreach ($rules["body"] ?? [] as $field => $rule) {
                $data[$field] = $body[$field] ?? null;
                $rule->assert($body[$field] ?? null);
            }

            foreach ($rules["query"] ?? [] as $field => $rule) {
                $data[$field] = $query[$field] ?? null;
                $rule->assert($query[$field] ?? null);
            }

            foreach ($rules["file"] ?? [] as $field => $rule) {
                /**
                 * @var ?UploadedFile $uploaded
                 */
                $uploaded = isset($file[$field]) ? $file[$field] : null;

                if (Validator::objectType()->attribute("file", Validator::file())->validate($uploaded)) {
                    Validator::objectType()->attribute("file", $rule)->assert($uploaded);
                    $data[$field] = $uploaded;
                } else if (Validator::objectType()->attribute("stream")->validate($uploaded)) {
                    $cache = sys_get_temp_dir() . "/" . Cuid::cuid();
                    stream_copy_to_stream($uploaded->getStream()->detach(), fopen($cache, "w"));

                    $uploadedTmp = new UploadedFile($cache, $uploaded->getSize(), $uploaded->getError(), $uploaded->getClientFilename());

                    $rule->assert($cache);

                    $data[$field] = $uploadedTmp;
                } else {
                    $rule->assert($uploaded);

                    $data[$field] = $uploaded;
                }
            }
        } catch (NestedValidationException $e) {
            App::$response->getBody()->write(json_encode($e->getMessages()));
            App::$response = App::$response->withStatus(400)->withHeader("Content-Type", "application/json");
            App::finish();
        }

        return $data;
    }
}
