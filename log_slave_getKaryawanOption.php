<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');
//=============================================

if(isTransactionPeriod()){
	//check if transaction period is normal
	$today = date("Y-m-d");
    $unit=$_POST['unit'];
    $subunit=$_POST['subunit'];
    $penerima=$_POST['penerima'];
    $bisakosong=$_POST['bisakosong'];
    $tanggal=tanggalsystem($_POST['tanggal']);
    if($penerima=='')$penerima='0';
    
	if($unit==''){
		echo "<option value=''></option>";
		exit;
	}
	//exit('Error'.$unit);
	if (strpos($unit, 'HO') !== false){
		$str="select karyawanid, namakaryawan, nik, subbagian, lokasitugas from ".$dbname.".datakaryawan 
        where (tanggalkeluar>= '".$tanggal."' or tanggalkeluar = '0000-00-00') and statuskaryawan != 'Keluar' and lokasitugas like '%HO'
        order by subbagian, namakaryawan"; 
	}else{
		if($bisakosong == '1'){
			$str="select karyawanid, namakaryawan, nik, subbagian, lokasitugas from ".$dbname.".datakaryawan 
				where (tanggalkeluar>= '".$tanggal."' or tanggalkeluar = '0000-00-00') and statuskaryawan != 'Keluar'
				order by subbagian, namakaryawan";        
		}else{
			$str="select karyawanid, namakaryawan, nik, subbagian, lokasitugas from ".$dbname.".datakaryawan 
				where (tanggalkeluar>= '".$tanggal."' or tanggalkeluar = '0000-00-00') and statuskaryawan != 'Keluar' and lokasitugas like '".$unit."%'
				order by subbagian, namakaryawan";        
		}
		
	}
    
    // if($penerima!='0'){
		// $str="select karyawanid, namakaryawan, nik from ".$dbname.".datakaryawan 
        // where (tanggalkeluar>= '".$today."' or tanggalkeluar = '0000-00-00') and statuskaryawan != 'Keluar' 
        // order by namakaryawan";                
    // }
	//exit("error".$str);
	$optsloc2 = "<option value=''></option>";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$optsloc="<option value=''></option>";
	$n='';
	while($bar=$res->fetch()){
		
		$lok = $bar->lokasitugas;
        $tampilan=$bar->namakaryawan;
		if($bar->subbagian!=''){
			$tampilan=$bar->namakaryawan;
			$lok = $bar->subbagian;			
		}
		$d=$lok;

		if($d!==$n && $n!==""){			
			$optsloc.="</optgroup>";
			$optsloc2.="</optgroup>";
		}

		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optsloc.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			$optsloc2.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		if($bar->karyawanid==$penerima){
			$optsloc.="<option value='".$bar->karyawanid."' selected>".$tampilan." - ".$bar->nik."</option>";
			$optsloc2.="<option value='".$bar->karyawanid."' selected>".$tampilan." - ".$bar->nik."</option>";
		}else{
			$optsloc.="<option value='".$bar->karyawanid."'>".$tampilan." - ".$bar->nik."</option>";
			$optsloc2.="<option value='".$bar->karyawanid."'>".$tampilan." - ".$bar->nik."</option>";
		}
		$n=$d;
		if($d!=$n){			
			$optsloc.="</optgroup>";
			$optsloc2.="</optgroup>";
		}
		
    }    
    // $optsloc.="<option value='masyarakat'>".$_SESSION['lang']['masyarakat']."</option>";
    // $optsloc2.="<option value='masyarakat'>".$_SESSION['lang']['masyarakat']."</option>";
    
	echo $optsloc."####".$optsloc2;
}
else
{
    echo " Error: Transaction Period missing";
}
?>