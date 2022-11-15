<?php


use FastRoute\Route;

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

$router->group(['prefix' => 'auth'], function () use ($router){
    $router->post("/register", "AuthController@register");
    $router->post("/login", "AuthController@login");
});

$router->group(['middleware' => ['auth']], function ($router)
{
    $router->get('/posts', 'PostsController@index');
    $router->post('/posts', 'PostsController@store');
    $router->get('/post/{id}', 'PostsController@detail');
    $router->put('/post/{id}', 'PostsController@update');
    $router->delete('post/{id}', 'PostsController@delete');
    
});



