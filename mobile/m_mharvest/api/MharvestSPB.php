<?php
defined('BASEPATH') or exit('No direct script access allowed');
class MharvestSPB
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct(){
        $this->mspb = load_class('Mspb', $this->pathIndex);
    }
    function spb(){
        if(!$check = $this->mspb->init()){
            $data =  $this->mspb->addHeader($this->user);
            return $data;
        }else{
            return $check;
        }
    }
    function spbdetail(){
        $data =  $this->mspb->addDetail($this->user);
        return $data;
    }
    function spbtkbm(){
        $data =  $this->mspb->addTkbm();
        return $data;
    }
    function spbcheck(){
        $data =  $this->mspb->checkdatarow();
        return $data;
    }
    //standard
    function getdata(){
        $data =  $this->mspb->getdspb_notposted($this->user);
        return $data;
    }
    function getdatadetail(){
        $data =  $this->mspb->getspbdetail_notposted($this->user);
        return $data;
    }
    function postERP(){
        return $this->mspb->updateFlag();
    }
    // TPB Not Connected
    function getdatanotpb(){
        // get Only collect NORMAL and double handling not from TPB
        $data =  $this->mspb->getdspb_notpostedNC($this->user);
        return $data;
    }
    // TPB Unconnected
    function getonlytpb(){
        // get Only collect double handling from TPB
        $data =  $this->mspb->getdspb_onlyTPBNC($this->user);
        return $data;
    }
    function getDataFullNC(){
        $data =  $this->mspb->getdspb_notpostedNC($this->user);
        $data =  array_merge($data,$this->mspb->getdspb_onlyTPBNC($this->user));
        return $data;
    }

    function getonlytpbdetail(){
        // get data Hierarchy by SPB Number
        $data =  $this->mspb->getdspb_onlyTPBDetailNC($this->user);
        return $data;
    }
    function postERPNC(){
        return $this->mspb->updateFlagNC();
    }
}
