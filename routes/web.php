<?php

declare(strict_types=1);

// We cannot bind instances to a static closure.
// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic

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

$router->group(['middleware' => 'auth:shared_token'], function () use ($router): void {
    $router->post('diff', 'DiffController@create');
});
