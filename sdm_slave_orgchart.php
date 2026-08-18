<?
require_once('master_validation.php');
require_once('config/connection.php');

	$parent		=strtoupper(trim($_POST['parent']));
	$kdStruk	=strtoupper(trim($_POST['kdStruk']));
	$karyId         =trim($_POST['karyId']);
	$kdJbtn         =$_POST['kdJbtn'];
	$detail      =$_POST['detail'];
        $mailDt      =$_POST['mailDt'];
	$alokasi	=strtoupper(trim($_POST['alokasi']));
	 	
			
	//check if the same code and the same parent already exist
	$jum=0;//indicate not exist
	$exist=false;
	$s1="select count(*) from ".$dbname.".sdm_strukturjabatan where kodestruktur='".$kdStruk."' and induk='".$parent."'";
	$re1=$owlPDO->query($s1) or die(print " Gagal: ".PDOException::getMessage());
	$re1->setFetchMode(PDO::FETCH_NUM);
	while($row=$re1->fetch())
	{
		$jum=$row[0];
	}
	if($jum>0)
	  $exist=true;
	  
	if(!$exist){//then insert
		$st2="insert into ".$dbname.".sdm_strukturjabatan
		      (`induk`, `kodestruktur`, `karyawanid`, `kodejabatan`, `email`, `kodept`, `detail`, `lastuser`)
		values('".$parent."','".$kdStruk."','".$karyId."','".$kdJbtn."','".$mailDt."','".
		          $alokasi."',$detail,'".$_SESSION['standard']['username']."')";
	}
	else
	{//then update
	  $st2="update ".$dbname.".sdm_strukturjabatan
	        set	karyawanid='".$karyId."',
				kodejabatan	='".$kdJbtn."',
				email	='".$mailDt."',
				kodept	='".$alokasi."',
				detail = ".$detail.",
				lastuser	='".$_SESSION['standard']['username']."'
			 where kodestruktur	='".$kdStruk."'
			 and induk ='".$parent."'";	
	}
	
	try{
		$owlPDO->exec($st2); 
	}catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
?>
