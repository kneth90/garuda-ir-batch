<?php

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


$router->get('/xx', 'Irbatch@index');
$router->get('/tes', function(){
    $arr = array();

    $arr[1] = 'x';
    $arr[2] = 'x';

    return $arr;
});

$router->get('/batch/imagetodownload', 'Irbatch@getImageToDownload');
$router->get('/batch/imagetoupload', 'Irbatch@getImageToUpload');
$router->get('/batch/imagetoupload2', 'Irbatch@getImageToUpload2');
$router->post('/batch/sendJson', 'Irbatch@do_upload_json');
$router->post('/batch/send_facing_data', 'Irbatch@send_facing_data');




