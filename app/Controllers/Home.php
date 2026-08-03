<?php

namespace App\Controllers;

use App\Controllers\Admin\BaseController;
use App\Libraries\AT_Cookie;

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
        echo $this->view("default","index",$data);
    }
}