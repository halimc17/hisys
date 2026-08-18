<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setup_user extends OWL_Model{
    function selectData($select="*",$where="")
    {
        $result = array();
        $q = "select ".$select." from " . $this->db->dbname . ".user ".$where;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $result = $r;
        }
        return $result;
    }
}
