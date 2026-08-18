<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Kontrakbeli
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->kontrakbeli = load_class('Kontrak_beli', $this->pathIndex);
    }
    function data()
    {
        $data =  $this->kontrakbeli->getData();
        if (count($data) == 0) {
            $this->response['status'] = 404;
            $this->response['error'] = true;
            $this->response['message'] = "Data Tidak ada";
            return $this->response;
        }
        return count($data);
    }
}


