<?php

namespace Siluet;

use Respect\Validation\Exceptions\NestedValidationException;

class Validation
{
    public static function validate(array $rules)
    {
        $body = App::$request->getParsedBody();
        $query = App::$request->getQueryParams();
        $file = App::$request->getUploadedFiles();
        try {
            foreach ($rules["body"] ?? [] as $field => $rule) {
                $rule->assert($body[$field] ?? null);
            }

            foreach ($rules["query"] ?? [] as $field => $rule) {
                $rule->assert($query[$field] ?? null);
            }

            foreach ($rules["file"] ?? [] as $field => $rule) {
                $rule->assert(isset($file[$field]) ? $file[$field] : null);
            }
        } catch (NestedValidationException $e) {
            App::$response->getBody()->write(json_encode($e->getMessages()));
            App::$response = App::$response->withStatus(400)->withHeader("Content-Type", "application/json");
            App::finish();
        }
    }
}
