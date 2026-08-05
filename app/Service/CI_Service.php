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
        return $this;
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