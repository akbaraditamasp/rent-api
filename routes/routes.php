<?php

$router->mount("/api", function () use ($router) {
    $router->get("/auth/login", "AuthController@login");
    $router->mount("/setting", function () use ($router) {
        $router->get("/", "SettingController@index");
        $router->post("/", "SettingController@save");
    });
    $router->mount("/building", function () use ($router) {
        $router->post("/", "BuildingController@add");
    });
});
