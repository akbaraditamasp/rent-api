<?php

namespace Siluet;

use Kekos\MultipartFormDataParser\Parser;
use Respect\Validation\Validator;

class BodyParser
{
    public static function boot()
    {
        if (
            App::$request->getHeaderLine("content-type") === "application/json"
        ) {
            if (Validator::json()->validate(App::$request->getBody()->getContents())) {
                $input = json_decode(App::$request->getBody()->getContents(), TRUE);
                App::$request = App::$request->withParsedBody($input);
            }
        } else if (
            !in_array(
                App::$request->getMethod(),
                ["POST", "GET"]
            )
        ) {
            $parser = Parser::createFromRequest(App::$request, App::$uploaded, App::$stream);
            App::$request = $parser->decorateRequest(App::$request);
        }
    }
}
