<?php

namespace App\Libraries;

class Curl
{
    private $command;
    private $method;
    private $host;
    private $type;
    private $url;
    private $data;
    private $header;
    private $ssl = false;
    private $response;

    public function __construct()
    {
        $this->command="curl --connect-timeout 3 -s ";
        $this->set_method('get');
        $this->ssl=false;
        return $this;
    }
    public function set_type($type="text"){
        $this->type = $type;
        return $this;
    }
    public function set_host($host){
        $this->host = $host;
        return $this;
    }
    public function set_url($url){
        $this->url = $url;
        return $this;
    }
//    public function set_ssl($ssl){
//        $this->ssl = ($ssl) ? true : false;
//        return $this;
//    }
    public function set_method($method){
        $this->method = (strtolower($method) == "get" ) ? "GET" : "POST";
        return $this;
    }
    public function setdata($key="",$val="") {
        $this->data[] = $key."=".urlencode($val);
        return $this;
    }
    public function setheader($key="",$val=""){
        $this->header[] = "'".$key.": ".$val."'";
    }

    public function exec($debug=0) {
        $header = is_array($this->header) and sizeof($this->header)>0 ? "--header ". implode(" --header ",$this->header) : "";
        if($this->method =="GET"){
            $this->command.=" -X GET ".$header." --url '".$this->url."?".implode("&",$this->data)."'";
        } else{
            $data="";
            if($this->type =="json"){
                $data = escapeshellarg(implode("&",$this->data));
            }else{
                $data = "'".implode("&",$this->data)."'";
            }

            $this->command.=" -X POST ".$header." --url '".$this->url."' --data ".$data;
        }
        if($debug)  echo $this->command;

        exec($this->command,$response);

        $this->response = $response[0]?? "";
        if($debug) print_r($this->return_data());
        //echo $this->command."<BR>";
    }
    public function return_data(){

        return (array)json_decode($this->response);
    }
}