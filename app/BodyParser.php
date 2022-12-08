<?php

namespace Siluet;

use Bramus\Router\Router;
use \Restful\Parser;

class BodyParser
{
    public static function boot()
    {
        if (App::$request->getHeaderLine("content-type") === "application/json") {
            $input = json_decode(App::$request->getBody()->getContents(), TRUE);
            App::$request = App::$request->withParsedBody($input);
        }
    }
}
