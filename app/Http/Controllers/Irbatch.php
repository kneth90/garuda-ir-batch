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
    var $db;
    public function __construct()
    {
        //
        $this->db = app('db');
    }

    public function index(){
        return "index";
    }

    public function send_facing_data(){
        if(isset($_POST['data_facing'])){
            $res_product_ir_label = $this->db->table("product_ir_label")
                ->select("product_label", "product_id")
                ->get();
            $res_product_ir_label = $res_product_ir_label->keyBy("product_label");
            $res_product_ir_label = $res_product_ir_label->map(function($value){
                return $value->product_id;
            });

            $data = $_POST['data_facing'];
            $data = json_decode($data);

            $visit_id = $data->visit_id;

            foreach ($data->data as $key=>$value){
                $category = $key;
                $data_per_category = $value;

                foreach ($data_per_category as $key_2 => $value_2){
                    if(isset($res_product_ir_label[$key_2])) {
                        echo "INSERT INTO report_facing (visit_id, product_id, value_facing) VALUES(" . $visit_id . ", " . $res_product_ir_label[$key_2] . ", " . $value_2 . ")";


                        /*$this->db->table("report_facing_product")
                                    ->insert(["visit_id" => $visit_id, "product_id" => $res_product_ir_label[$key_2] , "value_facing" => $value_2]);*/

                        $this->db->insert("INSERT INTO report_facing_product (visit_id, product_id, value_facing) VALUES(?,?,?) ON DUPLICATE KEY UPDATE value_facing = ?", [$visit_id, $res_product_ir_label[$key_2], $value_2, $value_2]);
                    }
                    else{
                        echo $key_2 . ' label tdk tersedia di DB';
                    }
                }
            }
        }
    }

    public function do_upload_json(){
        if(isset($_POST['date']) AND $_FILES['json_file']){
            $file = $_FILES['json_file'];
            $t_date = str_replace("-", "", $_POST['date']);

            $path_to_save = env("PATH_PHOTO_DISPLAY", "") . "/" . $t_date . "/" . $file['name'];
            move_uploaded_file($file['tmp_name'], $path_to_save);
        }
    }

    public function getImageToUpload(){
        if(isset($_GET['date'])) {
            $t_date = str_replace("-", "", $_GET['date']);
            $t_date_string = $_GET['date'];
            $t_date_plus1 = str_replace( "-", "",date("Y-m-d", strtotime($t_date_string . " +1 DAY")));
            $t_date_plus2 = str_replace( "-", "",date("Y-m-d", strtotime($t_date_string . " +2 DAY")));
            $t_date_plus3 = str_replace( "-", "",date("Y-m-d", strtotime($t_date_string . " +3 DAY")));
            $t_date_min1 = str_replace("-", "", date("Y-m-d", strtotime($t_date_string . " -1 DAY")));
            $PHOTO_PATH = env("PATH_PHOTO_DISPLAY", "") . "/" . $t_date;
            $PHOTO_PATH_PLUS1 = env("PATH_PHOTO_DISPLAY", "") . "/" . $t_date_plus1;
            $PHOTO_PATH_PLUS2 = env("PATH_PHOTO_DISPLAY", "") . "/" . $t_date_plus2;
            $PHOTO_PATH_PLUS3 = env("PATH_PHOTO_DISPLAY", "") . "/" . $t_date_plus3;
            $PHOTO_PATH_MIN1 = env("PATH_PHOTO_DISPLAY", "") . "/" . $t_date_min1;
            $arr_ret = array();

            $images = glob($PHOTO_PATH . '/*.{jpg}', GLOB_BRACE);

            foreach ($images as $img) {
                $t_json = str_replace(".jpg", ".json", $img);
                if (!file_exists($t_json)) {
                    $t_file_name_only = str_replace($PHOTO_PATH . "/", "", $t_json);
                    array_push($arr_ret, $t_file_name_only);
                }
            }

//            $images_plus1 = glob($PHOTO_PATH_PLUS1 . '/*.{jpg}', GLOB_BRACE);
//
//            foreach ($images_plus1 as $img) {
//                $t_json = str_replace(".jpg", ".json", $img);
//                if (!file_exists($t_json)) {
//                    $t_file_name_only = str_replace($PHOTO_PATH_PLUS1 . "/", "", $t_json);
//                    array_push($arr_ret, $t_file_name_only);
//                }
//            }
//
//            $images_plus2 = glob($PHOTO_PATH_PLUS2 . '/*.{jpg}', GLOB_BRACE);
//
//            foreach ($images_plus2 as $img) {
//                $t_json = str_replace(".jpg", ".json", $img);
//                if (!file_exists($t_json)) {
//                    $t_file_name_only = str_replace($PHOTO_PATH_PLUS1 . "/", "", $t_json);
//                    array_push($arr_ret, $t_file_name_only);
//                }
//            }
//
//            $images_plus3 = glob($PHOTO_PATH_PLUS3 . '/*.{jpg}', GLOB_BRACE);
//
//            foreach ($images_plus3 as $img) {
//                $t_json = str_replace(".jpg", ".json", $img);
//                if (!file_exists($t_json)) {
//                    $t_file_name_only = str_replace($PHOTO_PATH_PLUS3. "/", "", $t_json);
//                    array_push($arr_ret, $t_file_name_only);
//                }
//            }
//
//            $images_min1 = glob($PHOTO_PATH_MIN1 . '/*.{jpg}', GLOB_BRACE);
//
//            foreach ($images_min1 as $img) {
//                $t_json = str_replace(".jpg", ".json", $img);
//                if (!file_exists($t_json)) {
//                    $t_file_name_only = str_replace($PHOTO_PATH_MIN1 . "/", "", $t_json);
//                    array_push($arr_ret, $t_file_name_only);
//                }
//            }

            return $arr_ret;
        }
        else{
            return null;
        }
    }

    public function getImageToUpload2(){
        if(isset($_GET['start']) AND isset($_GET['end'])) {
            ini_set("memory_limit", "1024M");

            $start_date = $_GET['start'];   $end_date = $_GET['end'];

            $arr_ret = array();

            $res = $this->db->table("visit")
                ->select($this->db->raw("date(start_datetime) as date_visit,rdh.category_id, photo_path"))
                ->join("report_display_header as rdh", "visit.visit_id", "=", "rdh.visit_id")
                ->join("report_display_detail as rdd", "rdh.report_header_id", "=", "rdd.report_header_id")
                ->join("report_photo", "rdd.report_detail_id", "=", "report_photo.report_id")
                ->where("visit.start_datetime", ">=", "$start_date 13:00:00")
                ->where("visit.start_datetime", "<=", "$end_date 13:59:59");

            $res = $res->get();

            foreach ($res as $v) {
                array_push($arr_ret, array('date' => $v->date_visit, "category" => $v->category_id,  "photo" => $v->photo_path));
            }

            return $res;
        }
    }

    public function getImageToDownload(){
        if(isset($_GET['start']) AND isset($_GET['end'])){
            ini_set("memory_limit", "1024M");
            $arr_ret = array();
            $category_list = $this->get_category();

            $start_date = $_GET['start'];   $end_date = $_GET['end'];

            $res = $this->db->table("visit")
                ->select($this->db->raw("visit.visit_id, rdh.report_header_id , store_id, date(start_datetime) as date_visit,rdh.category_id, photo_row_size, photo_column_size, rdd.photo_row_number, rdd.photo_column_number, rdd.created, photo_path"))
                ->join("report_display_header as rdh", "visit.visit_id", "=", "rdh.visit_id")
                ->join("report_display_detail as rdd", "rdh.report_header_id", "=", "rdd.report_header_id")
                ->join("report_photo", "rdd.report_detail_id", "=", "report_photo.report_id")
                ->where("visit.start_datetime", ">=", "$start_date 00:00:00")
                ->where("visit.start_datetime", "<=", "$end_date 23:59:59");

            $res = $res->get();


            foreach ($res as $v){
                $t_display_object =  array();

                if(!isset($arr_ret[$v->visit_id])){
                    $arr_ret[$v->visit_id] = new Report_display($v->visit_id , $category_list, $v->date_visit);
                }

                if(is_null($arr_ret[$v->visit_id]->report_display[$v->category_id])){
                    $arr_ret[$v->visit_id]->report_display[$v->category_id] = new Report_display_header($v->report_header_id, $v->category_id, $v->photo_row_size, $v->photo_column_size);
                }

                $arr_ret[$v->visit_id]->report_display[$v->category_id]->add_photos($v->photo_row_number, $v->photo_column_number, $v->photo_path);

            }
            return response()->json($arr_ret);
        }
        else{
            return null;
        }

    }

    private function get_category(){
        $res = $this->db->table('product_category')->get();

        return $res;
    }
}


class Report_display{
    public $visit_id;
    public $report_display;
    public $start_date;

    public function __construct($visit_id, $category_list, $date_visit){
        $this->visit_id = $visit_id;
        $this->start_date = $date_visit;
        $this->report_display = array();
        foreach ($category_list as $v){
            $this->report_display[$v->category_id] = null;
        }
    }

    public function add_display($arr_display){
        array_push($this->report_display, $arr_display);
    }
}

class Report_display_header{
    var $_id;
    var $photos;
    var $category_id;
    var $row_num;
    var $col_num;

    public function __construct($_id, $category_id, $row_num, $col_num){
        $this->_id = $_id;
        $this->category_id = $category_id;
        $this->row_num = $row_num;
        $this->col_num = $col_num;
        for($i = 0; $i < $row_num; $i++){
            for($j = 0; $j < $col_num; $j++){
                $this->photos[$i][$j] = null;
            }
        }
    }

    public function add_photos($row, $col, $photos_url){
        $this->photos[$row-1][$col-1] = $photos_url;
    }

}

class Report_display_detail{
    var $photo;
    var $row;
    var $col;

    public function index($photo, $row, $col){
        $this->photo = $photo;
        $this->row = $row;
        $this->col = $col;
    }

}
