<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mharvest{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct(){
        $this->mpanen = load_class('Mpanen', $this->pathIndex);
    }
    function panen(){
        if(!$check = $this->mpanen->init()){
            $data =  $this->mpanen->addHeader($this->user,'PNN');
            return $data;
        }else{
            return $check;
        }
    }
    function panendetail(){
        $data =  $this->mpanen->addDetail($this->user,'PNN');
        return $data;
    }
    function panengerdang(){
        $data =  $this->mpanen->addgerdang($this->user,'PNN');
        return $data;
    }
    function panenmutubuah(){
        $data =  $this->mpanen->addmutubuah($this->user);
        return $data;
    }
    function panencheck(){
        $data =  $this->mpanen->checkdatarow($this->user,'PNN');
        return $data;
    }
    function panenimage(){
        $data =  $this->mpanen->uploadImages($this->user,'PNN');
        return $data;
    }
    function tipepanen()
    {
        $data =  $this->mpanen->setup_tipepanen("WHERE (kodeorg = '" . substr($this->user['lokasitugas'], 0, 4) . "' OR kodeorg = 'GLOBAL') AND fungsi = 1 AND aktif = 1 ORDER BY kodejenis");
        return $data;
    }

    function getHeader(){
        return $this->mpanen->getHeaders($this->user,'PNN');
    }

    function getDetail(){
        return $this->mpanen->getDetails();
    }

    function postERP(){
        return $this->mpanen->updateFlag();
    }

    function rekaphancakpanen(){
        return $this->mpanen->rekapHancakPNN($this->user);
    }

    function hancakheaders(){
        return $this->mpanen->rekaphancakpanenheader($this->user);
    }

    function hancakdetails(){
        return $this->mpanen->rekaphancakpanendetail($this->user);
    }
}
