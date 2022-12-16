<?php

use Siluet\App;

$router->mount("/api", function () use ($router) {
    $router->get("/auth/login", "AuthController@login");
    $router->get("/auth/customer/login", "AuthController@customer_login");
    $router->get("/auth/customer/register", "AuthController@customer_registration");
    $router->mount(
        "/setting",
        function () use ($router) {
            $router->get("/", "SettingController@index");
            $router->post("/", "SettingController@save");
        }
    );
    $router->mount(
        "/building",
        function () use ($router) {
            $router->delete("/(\d+)", "BuildingController@delete");
            $router->put("/(\d+)", "BuildingController@edit");
            $router->get("/(\d+)", "BuildingController@getId");
            $router->get("/", "BuildingController@index");
            $router->post("/", "BuildingController@add");
        }
    );
    $router->mount(
        "/booking",
        function () use ($router) {
            $router->get("/check/(\d+)", "BookingController@check");
            $router->get("/(\d+)", "BookingController@get");
            $router->post("/(\d+)", "BookingController@add");
            $router->get("/", "BookingController@index");
        }
    );
});

$router->get("/image/([^/]+)", function ($filename) {
    $file = __DIR__ . "/../uploaded/$filename";
    if (file_exists($file)) {
        App::$response->getBody()->write(file_get_contents($file));
        App::$response = App::$response->withHeader("Content-Type", mime_content_type($file));
        App::finish();
    } else {
        App::$response->getBody()->write("Nothing here");
        App::finish();
    }
});
