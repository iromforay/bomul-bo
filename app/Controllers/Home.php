<?php

namespace App\Controllers;

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