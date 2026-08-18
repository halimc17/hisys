<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$str=$owlPDO->prepare("select * from ".$dbname.".admin_list where username=?");
$str->execute([$_SESSION['standard']['username']]);
$str->setFetchMode(PDO::FETCH_OBJ);
$numrows = $str->rowCount();
//$numrows=owlBaris($str);

if($numrows==0) {
	exit("Error: you are not administrator, please login as administrator");
}