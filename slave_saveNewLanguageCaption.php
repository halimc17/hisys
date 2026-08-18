<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

	$legend		=$_POST['legend'];
	$location 	=$_POST['location'];
	$arg	    =explode("##",$_POST['arg']);
	$cont	    =explode("##",$_POST['cont']);
//insert new caption to all language
   $str="insert into ".$dbname.".bahasa(legend,location,";
   for($x=0;$x<count($arg);$x++)
   {
   	 if($x==0)
	  $str.=$arg[$x];
//	 if($x==(count($arg)-1))
//	  $str.=$arg[$x];
	 else 
	  $str.=",".$arg[$x];
   }	
   $str.=") values('".$legend."','".$location."',";
   for($x=0;$x<count($cont);$x++)
   {
   	 if($x==0)
	  $str.="'".$cont[$x]."'";
//	 if($x==(count($cont)-1))
//	  $str.=",'".$cont[$x]."'";	  
	 else 
	  $str.=",'".$cont[$x]."'";
   }
   $str.=")"; 
 
	try{
	   $owlPDO->exec($str); //insert hedaer	
	}catch (PDOException $e){
		echo" Error add language:".$e->getMessage();;
	} 
?>
