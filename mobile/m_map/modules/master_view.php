<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Master_view extends OWL_Controller{
    public function __construct(){
		parent::__construct();
	}
    function index($filepath){
        include($filepath);
    }
}
?>