<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$str=$owlPDO->query("select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'");
$str->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($str);
if($numrows==0) {
	exit("Error: you are not administrator, please login as administrator");
}