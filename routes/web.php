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

// OAuth Google

$router->get('oauth/google/login', 'AuthController@redirectToProvider');
$router->get('oauth/google/callback', 'AuthController@handleProviderCallback');

// API
$router->group(['prefix' => 'api/v1/' ], function() use ($router) {


    // Logged In
    $router->group(['middleware' => ['login'] ], function() use ($router) {
        
        $router->get("/cost/get-all", "CostController@getAllCost");
        $router->get("/cost/get-total-summary", "CostController@getTotalSummaryCost");
        $router->get("/cost/get-history/{id}", "CostController@getHistoryCost");


    });

    // Logged In and Admin
    $router->group(['middleware' => ['login', 'admin'] ], function() use ($router) {
        
        $router->post("/cost/create", "CostController@createCost");
        $router->delete("/cost/delete/{id}", "CostController@deleteCost");
        $router->patch("/cost/update/{id}", "CostController@updateCost");

    });

});
