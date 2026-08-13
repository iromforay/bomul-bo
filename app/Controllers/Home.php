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
        $data['products'] = (new ProductService())->get_usable_base_list();
        echo $this->view("default","index",$data);
    }
}