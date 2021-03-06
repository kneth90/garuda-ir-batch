<?php

namespace App\Http\Controllers;


use Laravel\Lumen\Routing\Controller as BaseController;

class Datautil extends BaseController
{
    var $db;

    public function __construct()
    {
        $this->db = app('db');
    }

    public function index($data_id = 0){
        if($data_id == 1){
            return $this->get_customer_id_to_store();
        }
        else if($data_id == 2){
            return $this->get_product_bosnetid_to_productid();
        }
        elseif($data_id == 101){
            return $this->set_customer_id_to_store_by_json();
        }
        elseif($data_id == 102){
            return $this->set_report_selling();
        }
        /* yagn dipake sekarang */
        elseif($data_id == 702){
            return $this->set_report_selling_2();
        }
        elseif($data_id == 601){
            return $this->update_batch_log();
        }
        elseif($data_id == 103){
            return $this->set_target_selling();
        }
        else if($data_id == 3){
            return $this->get_product_meta_data();
        }
    }


    private function get_product_meta_data(){
        $arr_ret = array();
        $res = $this->db->table("product_view_ir_label_to_id as l")
                        ->select("l.product_label", "p.*")
                        ->join("product_view_1 as p" ,"l.product_id", "p.product_id")
                        ->get();

        $res = $res->keyBy("product_label");

        return $res;
    }

    private function get_customer_id_to_store(){
        $res = $this->db->table("customer_id_to_store");
        $res = $res->get();

        $arr_ret = array();
        foreach($res as $v){
            if(!isset($arr_ret[$v->customer_id]))   $arr_ret[$v->customer_id] = array();
            array_push($arr_ret[$v->customer_id], $v->store_id);
        }
        return $arr_ret;
    }


    public function get_product_bosnetid_to_productid(){
        $res = $this->db->table("product")
                    ->select("bosnet_id", "product_id")
                    ->where("bosnet_id", "<>", 0)
                    ->get();
        $res = $res->keyBy("bosnet_id")->map(function($item, $key){
            return $item->product_id;
        });

        return $res;
    }


    private function set_target_selling(){
        if(isset($_POST['data'])){
            $data = json_decode($_POST['data']);

            $res_product = $this->db->table("product")
                                ->select("product_id", "product_code")
                                ->get();
            $res_product = $res_product->keyBy("product_code");
            $res_product = $res_product->map(function($item, $key){
                return $item->product_id;
            });

            $query = "INSERT INTO target_selling_tanpa_fk (customer_id, product_id , year, month, target) VALUES ";
            //$query = "INSERT INTO target_selling (customer_id, product_id , year, month, target) VALUES ";
            $i = 0;
            $sprintf_format = " ('%s', %d , %d, %d, %d) ";

            foreach ($data as $v){
                if(isset($res_product[$v->product_code])) {
                    $product_id = $res_product[$v->product_code];
                    if ($i == 0) {
                        $i = 1;
                    } else {
                        $query .= ",";
                    }
                    $query .= sprintf($sprintf_format, $v->costumer_id, $product_id, $v->year, $v->month, $v->target_sales);
                }
            }
            $query .= " ON DUPLICATE KEY UPDATE target = VALUES(target)";

            //echo $query . " <br/>";
            if($i > 0)  $this->db->insert($query);
            else echo "tidak ada data";
        }
        else{
            echo "xx";
        }
    }


    private function set_customer_id_to_store_by_json(){
        if(isset($_POST['data'])) {
            $res_product = $this->db->table("product")
                                    ->get();
            $res_product = $res_product->keyBy("product_code");
            $res_product = $res_product->map(function($item, $key){
                return $item->product_id;
            });


            $data = json_decode($_POST['data']);

            foreach ($data as $v) {
                foreach ($v->store_id as $w) {
                    if(isset($res_product[$v->product_code])){
                        echo "INSERT INTO product_store (product_id, store_id, standard_oos) VALUES (" . $res_product[$v->product_code] .", " . $w .", 3) \n";
                        //$t_res_insert = $this->db->insert("INSERT INTO product_store (product_id, store_id, standard_oos) VALUES (?,?,?) ON DUPLICATE KEY UPDATE product_id = ? , store_id = ?", [$res_product[$v->product_code], $w, 3, $res_product[$v->product_code], $w]);
                        //echo $t_res_insert->toSql();
                    }
                    else{
                        echo "NO PRODUCT CODE " . $v->product_code . "\n";
                    }

                }
            }
        }
    }


    private function set_report_selling(){
        echo "Te";
        if(isset($_POST['data'])) {
            $data = json_decode($_POST['data']);

            var_dump($data);

            foreach ($data as $v){
                $this->db->table("report_selling")
                        ->updateOrInsert(['tanggal' => $v->tanggal, 'costumer_id' => ''.$v->costumer_id, 'product' => $v->product]
                                        , ['sales_value' => $v->sales_value, 'sales_unit' => $v->sales_unit]);
            }

        }
    }

    private function set_report_selling_2(){
        if(isset($_POST['data'])) {
            $data = json_decode($_POST['data']);
            //var_dump($data);
            foreach ($data as $v){
                $t_key = $v[0];
                $t_costumer_id = $v[1];
                $t_product_id = $v[2];
                $t_tanggal = $v[3];
                $t_sales_value = $v[4];
                $t_sales_unit = $v[5];
                if($t_product_id != "null"){
                    $this->db->table("report_selling")
                        ->updateOrInsert(['id' => $t_key, 'tanggal' => $t_tanggal, 'costumer_id' => ''.$t_costumer_id, 'product' => $t_product_id]
                            , ['sales_value' => $t_sales_value, 'sales_unit' => $t_sales_unit]);
                }
            }
            echo "done send";
        }
    }

    private function update_batch_log(){
        header('Content-Type: application/json');
        $arr_return = array("status" => "failed", "ext" => array());
        $operation = isset($_POST['operation']) ? $_POST['operation'] : "-1";


        if($operation == "START_BATCH_SELLING"){
            $id = $this->db->table("batch_log")
                    ->insertGetId(["type" => "REPORT_SELLING", "start_time" => date("Y-m-d H:i:s")]);
            $arr_return["status"] = "success";
            $arr_return["ext"]["row_id"] =  $id;

        }
        else if($operation == "END_BATCH_SELLING"){
            $log_id = $_POST['log_id'];
            $end_datetime = date("Y-m-d H:i:s");

            $this->db->table("batch_log")
                    ->where("id", $log_id)
                    ->update(["end_time" => $end_datetime]);

            $arr_return["status"] = "success";
        }

        return $arr_return;
    }

}

