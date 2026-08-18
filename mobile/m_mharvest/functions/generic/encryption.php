<?
class Encryption {
    function encryptionId($param=""){
        return md5($param.PRODUCT_KEY); 
    }
    function encryptionProduct($param=""){
        return md5($param.PRODUCT_KEY); 
    }
}

?>