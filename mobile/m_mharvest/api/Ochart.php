<?
  defined('BASEPATH') or exit('No direct script access allowed');

  class Ochart {
    protected $pathIndex = "prc/";
    private $ochart;

    function __construct() {
      $this->ochart = load_class('Prc_ochartcollection', $this->pathIndex);
    }
    
    function uploadJson() {
      $data =  $this->ochart->uploadJsonFile();
      return $data;
    }
  }
