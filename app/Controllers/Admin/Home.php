<?php

namespace App\Controllers\Admin;

use App\Libraries\AT_Cookie;

class Home extends BaseController
{
    private  $vdir;
    public function __construct()
    {
        parent::__construct();
        $this->vdir="Admin/";
        return $this;
    }

    public function getindex()
    {

        $data=array();
        echo $this->view("index",$data);
    }
}