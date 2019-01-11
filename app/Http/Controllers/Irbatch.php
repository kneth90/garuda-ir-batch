<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\DB as DB;

class Irbatch extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index(){
        return "index";
    }

    public function getImageToDownload(){
        $arr_ret = array();

        $res = app('db')->table("visit")
                    ->select("visit.*")
                    ->join("report_display_header as rdh", "visit.visit_id", "=", "rdh.visit_id")
                    ->join("report_display_detail as rdd", "rdh.report_header_id", "=", "rdd.report_header_id")
                    ->where("visit.start_datetime", ">=", "2019-01-01 00:00:00")
                    ->where("visit.start_datetime", "<=", "2019-01-31 23:59:59")
                    ->get();

        foreach ($res as $v){
            $t_display_object =  array();
            if(!isset($arr_ret[$v->visit_id])){
                
            }
        }

        return response()->json($res);
    }
    //
}
