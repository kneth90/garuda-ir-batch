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
            var_dump($_POST['data']);

        }
    }
}

