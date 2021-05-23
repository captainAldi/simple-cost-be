<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get("/coba/get-all-cost", "CostController@getAllCost");
$router->get("/coba/get-total-summary-cost", "CostController@getTotalSummaryCost");
$router->get("/coba/get-all-cost-detail", "CostController@getAllCostDetail");

$router->post("/coba/create-cost", "CostController@createCost");
$router->delete("/coba/delete-cost/{id}", "CostController@deleteCost");
$router->patch("/coba/update-cost/{id}", "CostController@updateCost");
