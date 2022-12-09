<?php

namespace Siluet;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;

class App
{
    public static ServerRequest $request;
    public static Response $response;
    public static UploadedFileFactory $uploaded;
    public static StreamFactory $stream;

    public static function boot()
    {
        $instance = new App();
    }

    public function __construct()
    {
        static::$request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
        static::$response = new Response();
        static::$uploaded = new UploadedFileFactory();
        static::$stream = new StreamFactory;
    }

    public static function finish()
    {
        (new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit(static::$response);
        exit();
    }

    public static function controller(Closure $app)
    {
        try {
            static::$response = static::$response->withHeader("Content-Type", "application/json");
            $payload = json_encode($app());
            static::$response->getBody()->write($payload);
            static::finish();
        } catch (ModelNotFoundException $e) {
            static::$response->getBody()->write(json_encode(
                ["error" => "Model not found"]
            ));
            static::$response = static::$response->withStatus(404)->withHeader("Content-Type", "application/json");
            static::finish();
        }
    }
}
