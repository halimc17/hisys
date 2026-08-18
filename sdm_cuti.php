<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
	<script language=javascript src='js/sdm_5cuti.js?v=<?php echo time(); ?>'></script>
<?


## GET UNIT
$optlokasitugas='';
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optlokasitugas.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optlokasitugas.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optlokasitugas.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optlokasitugas.="</optgroup>";
	}
}


// $optlokasitugas="";
// if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
// 	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') and length(kodeorganisasi)=4 order by namaorganisasi";
//     $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//     $res->setFetchMode(PDO::FETCH_OBJ);
// 	while($bar=$res->fetch())
//     {
// 		$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
// 	}
// 	$tpkar = "0,1,2,3,4,5,6,7,8,9,10,11,12";
// }else{
// 	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi";
//     $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//     $res->setFetchMode(PDO::FETCH_OBJ);
// 	while($bar=$res->fetch())
//     {
// 		$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
// 	}
	$tpkar = "0,1,2,3,4,5,6,7,8,9,10,11,12";
// }

$optperiode='';
for($x=-1;$x<3;$x++)
{
	$dt=date('Y')-$x;
    $optperiode.="<option value='".$dt."'>".$dt."</option>";
}

$opttipekaryawan = '';
$strTipe = "select * from ".$dbname.".sdm_5tipekaryawan where id in (".$tpkar.") order by id";
$res=$owlPDO->query($strTipe) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opttipekaryawan.="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$res->fetch())
{
	$opttipekaryawan.="<option value='".$bar->id."'>".$bar->tipe."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_cuti').'</span><br>'); 
echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=2 cellspacing=1>
		<tr>
			<td>".$_SESSION['lang']['unitkerja']."</td>
			<td> : </td>
			<td>
				<select style='width:260px;' id=lokasitugas>".$optlokasitugas."</select>
			</td>
		</tr>
			 <td>".$_SESSION['lang']['tipekaryawan']."</td>
			 <td> : </td>
			 <td><select style='width:260px;' id=tipekaryawan>".$opttipekaryawan."</select></td>
		<tr>
            <td>".$_SESSION['lang']['periode']." </td>
			<td> : </td>
			<td><select style='width:260px;' id=periode>".$optperiode."</select></td>
		</tr>
		<tr>
            <td colspan=3>
				<button class=mybutton onclick=\"loadList(document.getElementById('lokasitugas').options[document.getElementById('lokasitugas').selectedIndex].value,document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value)\">".$_SESSION['lang']['lihat']." Data Cuti</button>
				<button class=mybutton onclick=prosesAwal()>Adjustment Cuti</button>
				<button class=mybutton onclick=\"Listadjs(document.getElementById('lokasitugas').options[document.getElementById('lokasitugas').selectedIndex].value,document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value)\">Lihat Adjustment Cuti</button>
			</td>
		</tr>	  
	</table></fieldset>";
	
CLOSE_BOX();
OPEN_BOX('','');
$arr[0]="<div id=containerlist1 style=';height:500px;overflow:auto'>
      </div>";
// $arr[1]="<div id=containerlist2 style='height:500px;overflow:auto'>
//       </div>";	  
$hfrm[0]=$_SESSION['lang']['data'];
// $hfrm[1]=$_SESSION['lang']['detail'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$arr,130,'100%');	  
CLOSE_BOX();
echo close_body();
?>