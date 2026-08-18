<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get POST Data
$kodeblok=checkPostGet('kodeblok','');
$supplierid=checkPostGet('supplierid','');
$nosertifikat=checkPostGet('nosertifikat','');
$proses=checkPostGet('proses','');
$afdeling=checkPostGet('afdeling','');
$unitplasma=checkPostGet('unitplasma','');
$kodeblokplasma=checkPostGet('kodeblokplasma','');
$hiddensupplierid=checkPostGet('hiddensupplierid','');

switch($proses)
{
	case 'simpan':
		$sCount = "select * from ".$dbname.".kebun_5kud where supplierid = '".$supplierid."' and kodeblok = '".$kodeblok."'";
		$res=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($res);
		if($numrows <= 0){
			$str="insert into ".$dbname.".kebun_5kud values ('".$supplierid."','".$kodeblok."','".$kodeblokplasma."','".$nosertifikat."')";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				die();
			}
		}else{
			exit("warning : Item sudah ada didatabase.");
		}
	break;
	
	case 'update':
		$str="update ".$dbname.".kebun_5kud set supplierid='".$supplierid."', nosertifikat='".$nosertifikat."' where kodeblokplasma='".$kodeblokplasma."' and kodeblok='".$kodeblok."' and supplierid='".$hiddensupplierid."'";
        try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			die();
		}
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".kebun_5kud where  kodeblokplasma='".$kodeblokplasma."' and kodeblok='".$kodeblok."' and supplierid='".$supplierid."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			die();
		}
	break;

	case 'getblokplasma':

		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='BLOK' and left(kodeorganisasi,4)='".$unitplasma."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$optSup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while($bar=$res->fetch())
		{
			if ($kodeblokplasma==$bar->kodeorganisasi) {
				$optSup.="<option value='".$bar->kodeorganisasi."' selected>".$bar->namaorganisasi."</option>";
			}else{
				$optSup.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
			}
			
		}

		echo $optSup;
	break;
	case'getunitPlasma':
		$optSup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".kebun_5plasma where supplierid='".$supplierid."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$optNmOrg=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$bar->kodeorganisasi."'");
			if ($unitplasma==$bar->kodeorganisasi) {
				$optSup.="<option value='".$bar->kodeorganisasi."' selected>".$bar->kodeorganisasi."-".$optNmOrg[$bar->kodeorganisasi]."</option>";
			}else{
				$optSup.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi."-".$optNmOrg[$bar->kodeorganisasi]."</option>";
			}
			
		}
		echo $optSup;
	break;
	
	default:
	break;
}

$str="select * from ".$dbname.".kebun_5kud t1, ".$dbname.".organisasi t2, ".$dbname.".log_5supplier t3
	 where t1.kodeblok=t2.kodeorganisasi and t1.supplierid=t3.supplierid and t2.induk='".$afdeling."' 
	 order by t1.kodeblok ASC";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;

echo"<fieldset id='search' style='margin-bottom:10px;float:left;clear:both'>
	<legend><b>List Data : KUD</b></legend>
	<div style=height:300px;overflow:auto;>
	 <table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%>
		<thead>
			<tr class=rowheader>
				<td>".$_SESSION['lang']['nomor']."</td>
				<td>".$_SESSION['lang']['kodeblok']."</td>
				<td>".$_SESSION['lang']['kodeblok']." Plasma</td>
				<td>".$_SESSION['lang']['kodesupplier']."</td>
				<td>".$_SESSION['lang']['namasupplier']." / KUD</td>
				<td>".$_SESSION['lang']['nosertifikat']."</td>
				<td colspan=2 style=text-align:center>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody>";
		
		while($bar=$res->fetch())
		{
			$no++;
			echo"<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$bar->kodeblok."</td>
					<td>".$bar->kodeblokplasma."</td>
					<td>".$bar->supplierid."</td>
					<td>".$bar->namasupplier."</td>
					<td>".$bar->nosertifikat."</td>
					<td><img class='zImgBtn' src='images/001_45.png' onclick=editRow('".$bar->kodeblok."','".$bar->supplierid."','".$bar->nosertifikat."','".substr($bar->kodeblokplasma,0,4)."','".$bar->kodeblokplasma."')></td>
					<td><img class='zImgBtn' src='images/delete_32.png' onclick=deleteitem('".$bar->kodeblok."','".$bar->supplierid."','".$bar->kodeblokplasma."')></td>
				</tr>";
		}
			
		
		"</tbody>
		<thead>
			<tr class=rowheader>
				<td colspan=6 height=10px></td>
			</tr>
		</thead>	
	</table></div>
	</fieldset>";



// #========Get Blok Ids============
// # Create Condition
// $where1 = "(tipe='BLOK' or tipe='BIBITAN') and induk='".$afdeling."'";

// # Get Org Data
// $query = selectQuery($dbname,'organisasi',"kodeorganisasi",$where1);
// $data = fetchData($query);
// # Create Condition for Table
// $where2 = array();
// foreach($data as $key=>$row) {
    // $where2[] = array('kodeorg'=>$row['kodeorganisasi']);
// }
// if(count($where2)<1)
// {
    // exit("Error:Tidak ada data");
// }
// $where2['sep'] = 'OR';

// #========Start Make Table
// # Prep
// $fieldStr = '##kodeorg##tahuntanam##luasareaproduktif';
// $fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

// # Get Data
// #$query = selectQuery($dbname,'setup_blok',"*",$where2);
// #$data = fetchData($query);

// # Set Header Name
// $head = array();
// $head[0]['name'] = $_SESSION['lang']['kodeorg'];
// $head[1]['name'] = $_SESSION['lang']['namasupplier'];
// $head[2]['name'] = $_SESSION['lang']['nosertifikat'];

// # Display Table
// $master = masterTableBlok($dbname,'setup_blok',1,$fieldArr,$head,$conSetting,$where2,
    // array(),'setup_slave_blok_pdf');
// try {
    // echo $master;
// } catch(Exception $e) {
    // echo "Create Table Error";
// }
?>