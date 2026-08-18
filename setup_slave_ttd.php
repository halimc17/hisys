<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/formReport.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
$fileupload = checkPostGet('fileupload', '');
$file = checkPostGet('file', '');
$kar = checkPostGet('kar', '');
$kdpo = checkPostGet('kdpo', '');

$dir='fileupload/ttd';
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

switch($method){
	case'savefile':
		$fileupload = strtolower('.'.substr($_FILES['fileup']['name'],strripos($_FILES['fileup']['name'],'.')+1));
		$fileupload = $fileupload;
		if($fileupload=='.jpg' || $fileupload=='.jpeg' || $fileupload=='.png')
		{}
		else{
			exit("Warning : File yang di-izinkan hanya JPG,JPEG,PNG");
		}
		$filesize=$_FILES['fileup']['size'];
		
		if($filesize>=256000)
		{
			exit("Warning : Besar ukuran file maksimal 256 KB. ");
		}
		$path = $dir."/".basename($_FILES['fileup']['name']);
		if(move_uploaded_file($_FILES['fileup']['tmp_name'], $path)){
			#delete 1st
			$str="delete from ".$dbname.".`setup_ttd` where karyawanid='".$kar."'";
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}	
			
			$str="INSERT INTO ".$dbname.".`setup_ttd` (`karyawanid`, `file`,`kode`, `updateby`) 
				VALUES ('".$kar."', '".$path."','".$kdpo."','".$_SESSION['standard']['userid']."')";
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}	
		
		}
		echo $_SESSION['lang']['datatersimpan'];
		
	break;
	
	case'loaddata':
		echo"<div id=container>
				<table class=sortable cellspacing=1 border=0>
				 <thead>
						 <tr class=rowheader>
							<td align=center>No</td>
							<td align=center>".$_SESSION['lang']['karyawan']."</td>
							<td align=center>".$_SESSION['lang']['kode']."</td>
							<td align=center>".$_SESSION['lang']['photo']."</td> 
							<td align=center>".$_SESSION['lang']['action']."</td></tr>
						 </tr>
					</thead>
					<tbody>";
		$str="select * from ".$dbname.".setup_ttd";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
                    @$no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$nmkar[$bar['karyawanid']]."</td>";
					echo "<td align=left>".$bar['kode']."</td>";
					echo "<td align=left>".$bar['file']."</td>";
                    echo "<td align=center>
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['karyawanid']."');\">
                            </td>";
                    echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kode']."');\">
		}
	break;
	
	
	case'del':
		$str="delete from ".$dbname.".`setup_ttd` where karyawanid='".$kar."'";
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
		}	
	break;
	default;
}