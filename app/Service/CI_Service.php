<?php

namespace App\Service;

use App\Libraries\AT_Result;
use CodeIgniter\Config\Services;
class CI_Service
{
    public $cache;
    private $is_cache=false;
    protected $shop_code;
    protected $db;
    public function __construct($session_id="")
    {
        $this->db = db_connect("default");
        if($session_id !="" AND defined("SHOP_CODE")){
            $this->shop_code = SHOP_CODE;
            $this->db->setDatabase($this->shop_code);
        }else if($session_id=="" and defined("SHOP_CODE")){
            $this->shop_code = SHOP_CODE;
            $this->db->setDatabase($this->shop_code);

        }else if($session_id !=""){

            $this->cache = Services::cache();
            $this->set_is_cache(true);
            $cache_row = unserialize($this->getCache($session_id));

            if (isset($cache_row['fid'])) { //캐시로 처리
                if(!defined("FID")) define('FID', $cache_row['fid']);
                if(!defined("SHOP_CODE")) define('SHOP_CODE', $cache_row['fid']);
                $this->shop_code = $cache_row['fid'];
            } else { // db 조회후 캐시 등록
                $model = model('App\Models\LoginModel', true, $this->db);

                $result = $model->get_session_id($session_id);
//print_r($result);exit;
                $this->shop_code = $result['fid']??"";
                if(!defined("FID")) define('FID', $result['fid']);
                if(!defined("SHOP_CODE")) define('SHOP_CODE', $result['fid']);
                $this->setCache($result['session_id'], serialize($result));//파일세션에 기록
            }

            if ($this->shop_code != "") { //상점 DB로 변경
                $this->db->setDatabase($this->shop_code);
            }

            defined('AES_KEY') || define('AES_KEY', $this->shop_code . "_" . getenv('AES_KEY'));
            return $this;
        }else {
            $oResult = AT_Result::toFailData("잘못된 접근 입니다.");
            echo AT_Result::toJson($oResult);
            exit;
            //return $this;
        }
    }

    public function getCache($cachename)
    {
        $row = null;
        if($this->get_is_cache() == true) {
            $row = $this->cache->get($cachename);
        }
        return $row;
    }
    public function setCache($cachename,$data)
    {
        if($this->get_is_cache() == true) {
            $chche_time = 60 * 50;
            cache()->save($cachename, $data, $chche_time);
        }
        return $this;
    }
    public function get_is_cache()
    {
        return $this->is_cache;
    }

    public function set_is_cache($is_cache)
    {
        $this->is_cache = $is_cache;
        return $this;
    }
}