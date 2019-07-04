<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\DB as DB;

use Laravel\Lumen\Routing\Controller as BaseController;
use Symfony\Component\VarDumper\Cloner\Data;

class Irbatch2 extends BaseController
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

    public function send_ir_data(){
        if (isset($_POST['data'])){
            $data = json_decode($_POST['data']);
            //var_dump($data);

            foreach ($data as $key=>$v){
                $visit_id = $v->visit_id;
                $category_id = $v->category_id;
                $facing_data = $v->facing_data;

                $planogram_data = $v->planogram_data;
                $planogram_value = $planogram_data->display_status ? 1 : 0;

                $qry_facing = "INSERT INTO report_facing_product (visit_id, product_id, value_facing) VALUES ";
                $icount = 0;
                foreach ($facing_data as $k_facing => $v_facing){
                    $sprintf_format = " ('%s', %d, %d) ";
                    if($icount > 0) $qry_facing .= ", ";
                    $qry_facing .= sprintf($sprintf_format, $visit_id, $k_facing, $v_facing);

                    $icount++;
                    //$qry = "INSERT INTO report_facing_product (visit_id, product_id, value_facing) VALUES ('$visit_id',$k_facing ,$v_facing) ON DUPLICATE KEY UPDATE value_facing = $v_facing";
                    //echo $qry . "<br/>\n";
                }
                $qry_facing .= " ON DUPLICATE KEY UPDATE value_facing = VALUES(value_facing)";

                $qry_display = "INSERT INTO report_display_compliance (visit_id, category_id, is_comply) VALUES  ('$visit_id', $category_id, $planogram_value) ON DUPLICATE KEY UPDATE is_comply = $planogram_value";



                if($icount > 0)    {
                    $this->db->insert($qry_facing);
                    echo $qry_facing;
                }

                $this->db->insert($qry_display);
                echo $qry_display;

            }
        }
        echo date("Y-m-d H:i:s");

    }

    public function get_display_by_visit(){
        header('Content-Type: application/json');
        $visit_id = isset($_POST['visit_id']) ? $_POST['visit_id'] : -1;

        if($visit_id != -1){
            $res_visit = $this->db->table("report_visit_display_photo")
                                ->where("visit_id", "=", $visit_id)
                                ->orderBy("category_id")
                                ->get();

            if(count($res_visit) > 0){
                $t_first = $res_visit[0];
                $t_display_data = new Data_display($t_first->visit_id, $t_first->tanggal, $t_first->photo_column_size, $t_first->photo_row_size);

                foreach ($res_visit as $v){
                    if(!isset($t_display_data->grid_display[$v->category_id])){
                        $t_display_data->add_grid($v->photo_column_size, $v->photo_row_size, $v->category_id);
                    }

                    $t_display_data->set_photo($v->photo_column_number, $v->photo_row_number, $v->photo_path, $v->category_id);
                }

                echo json_encode($t_display_data);
            }
        }
    }

    public function get_visit_to_display_batch(){
        $date = isset($_POST['date']) ? $_POST['date'] : -1;

        if($date != -1){
            header('Content-Type: application/json');
            /* get visit row */
            $res_visit = $this->db->table("report_visit_display_photo")
                            ->where("tanggal", "=", $date)
                            ->orderBy("visit_id")
                            ->orderBy("category_id")
                            ->get();

            $t_current_display_data = null;
            $t_curr_visit_id = null;


            $arr_result = array();
            foreach ($res_visit as $v){
                // ketika looping raw data yg diorder by visit_id, row selanjutnya bukan visit_id yg sama
                if(is_null($t_curr_visit_id) OR $t_curr_visit_id != $v->visit_id){

                    //array_push($arr_result, new Data_display($v->visit_id, $v->photo_column_size, $v->photo_row_size));
                    if(!is_null($t_current_display_data))    array_push($arr_result, $t_current_display_data);

                    $t_current_display_data = new Data_display($v->visit_id, $v->tanggal, $v->photo_column_size, $v->photo_row_size);
                }

                if(!isset($t_current_display_data->grid_display[$v->category_id])){
                    $t_current_display_data->add_grid($v->photo_column_size, $v->photo_row_size, $v->category_id);
                }

                $t_current_display_data->set_photo($v->photo_column_number, $v->photo_row_number, $v->photo_path, $v->category_id);
                $t_curr_visit_id = $v->visit_id;
            }
            array_push($arr_result, $t_current_display_data);

            return $arr_result;
        }
    }
}

class Data_display{
    var $visit_id;
    var $tanggal;
    var $grid_display = array();

    public function  __construct($visit_id, $tanggal, $col_size, $row_size){
        $this->visit_id = $visit_id;
        $this->tanggal = $tanggal;
        //$this->grid_display = new Grid_display($col_size, $row_size);
        $this->grid_display = array();
    }

    public function add_grid($col_size, $row_size, $category){
        $this->grid_display[$category] = new Grid_display($col_size, $row_size, $category);

    }

    public function set_photo($col, $row, $photo, $category){
        if(isset($this->grid_display[$category])){

        }
        else{

        }

        if(!is_null($photo))    $this->grid_display[$category]->set_grid_photo($col, $row, $photo);
    }



}

class Grid_display{
    var $row_size;
    var $col_size;
    var $category;
    var $grid;

    public function __construct($row_size, $col_size, $category){
        $this->row_size = $row_size;
        $this->col_size = $col_size;
        $this->category = $category;

        for($i_cl = 1; $i_cl <= $col_size; $i_cl++){
            for($i_rw = 1; $i_rw <= $row_size; $i_rw++){
                $this->grid[$i_cl][$i_rw] = "empty";
            }
        }
    }

    public function set_grid_photo($col, $row, $photo){
        if(isset($this->grid[$col][$row]))  $this->grid[$col][$row] = $photo;
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
