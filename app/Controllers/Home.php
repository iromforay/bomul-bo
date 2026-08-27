<?php

namespace App\Controllers;

use App\Service\ProductService;

class Home extends BaseController
{
    private  $vdir;
    public function __construct()
    {
        parent::__construct();
      //  $this->vdir="Admin/";
        return $this;
    }

    public function getindex()
    {

        $data=array();
        $data['common']['description'] = '공인중개사무소 전용 매물 홈페이지 제작·관리 서비스. 매물 등록부터 고객 상담까지 필요한 기능을 한 곳에서 관리하세요.';
        $data['products'] = (new ProductService())->get_usable_base_list();
        echo $this->view("default","index",$data);
    }
}