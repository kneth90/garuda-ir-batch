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

use App\Http\Controllers\Datautil;
use App\Http\Controllers\Irbatch2;

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
$router->post('/batch/send_display_data', 'Irbatch@send_display_data');
$router->get('/batch/get_report_visit_display', 'Irbatch@get_visit_to_display_batch');

$router->post('/batch2[/{report}]', function($report=0){
    if($report == "visit_display_by_date"){
        $t =  new Irbatch2();
        return $t->get_visit_to_display_batch();
    }
    else if ($report == "send_ir_data"){
        $t =  new Irbatch2();
        $t->send_ir_data();
    }
});


$router->get("/datautil[/{id}]", function($id=0){
    $t = new Datautil();
    return $t->index($id);
});

$router->post("/datautil[/{id}]", function($id=0){
    $t = new Datautil();
    return $t->index($id);
});




