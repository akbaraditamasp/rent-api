<?php

namespace Siluet;

use Bramus\Router\Router;
use Medz\Cors\Cors as CorsLib;

class Cors
{
    public static function boot(Router $router, $config = [
        'allow-credentials' => false, // set "Access-Control-Allow-Credentials" 👉 string "false" or "true".
        'allow-headers'      => ['*'], // ex: Content-Type, Accept, X-Requested-With
        'expose-headers'     => [],
        'origins'            => ['*'], // ex: http://localhost
        'methods'            => ['*'], // ex: GET, POST, PUT, PATCH, DELETE
        'max-age'            => 0,
    ])
    {
        $cors = new CorsLib($config);
        $cors->setRequest('psr-7', App::$request);
        $cors->setResponse('psr-7', App::$response);
        $cors->handle();

        $router->before("GET|POST|PUT|DELETE|PATCH|OPTIONS", "/.*", function () use ($cors) {
            App::$response = $cors->getResponse();
        });
        $router->match("OPTIONS", "/.*", function () use ($cors) {
            App::$response = $cors->getResponse();
            App::finish();
        });
    }
}
