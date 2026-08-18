<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

	$newlang	=$_POST['newlang'];
	$name 		=$_POST['langname'];
	$def		=$_POST['def'];
//add column to lang table
    $str="alter table ".$dbname.".bahasa add column ".$newlang." text";
    $berhasil='false';
	try{
	   $owlPDO->exec($str); //insert hedaer	
	   $berhasil='true';
	}catch (PDOException $e){
		$berhasil='false';
		$err=" Error add language:".$e->getMessage();;
	}    
   if($berhasil=='true'){
   	   //set value= def to new language
	   $str1="update ".$dbname.".bahasa set ".$newlang."=".$def;
	   $owlPDO->exec($str1);
	   $str2="insert into ".$dbname.".namabahasa
	          (code,name) values('".$newlang."','".$name."')";
	   $owlPDO->exec($str2);		  
	   
	   //get language column list to display 
	   $sta="select * from ".$dbname.".namabahasa";
		$res=$owlPDO->query($sta) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$langlist="";
		while($bar=$res->fetch())
		{
			$langlist.=" &nbsp &nbsp<a href=# onclick=loadLang('".$bar->code."')>".$bar->name."</a>";	   
		}
echo" <fieldset style='width:850px;'>
	  <legend>".$_SESSION['lang']['availlang']."</legend>
	  ".$langlist."
	  </fieldset>";
   }
	else
	{
		echo $err;
	}
?>
