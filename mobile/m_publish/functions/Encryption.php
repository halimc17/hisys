<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Encryption {
    public function encryptionId($param=""){
        return md5($param.PRODUCT_KEY); 
    }
    public function encryptionProduct($param=""){
        return md5($param.PRODUCT_KEY); 
    }
}

?>