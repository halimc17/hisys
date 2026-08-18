<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>
<script language=javascript1.2 src='js/kebun_2produksidivisi.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?

//BOX ATAS

$optinti="";
$optinti.="<option value=''>".$_SESSION['lang']['all']."</option>";
if(trim($_SESSION['org']['tipeinduk'])=='HOLDING')//user holding dapat menempatkan dimana saja
{
    $str="select distinct left(kodeunit,3) as kodeunit from ".$dbname.".bgt_produksi_kbn_kg_vw order by kodeunit asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$optinti.="<option value='".$bar->kodeunit."'>".$bar->kodeunit."</option>";	
	}
}

// $sUnit	="SELECT namaorganisasi,kodeorganisasi,induk,tipe FROM ".$dbname.".organisasi WHERE tipe in ('KEBUN')ORDER BY kodeorganisasi";
// 	$qUnit	=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
// 	$qUnit->setFetchMode(PDO::FETCH_ASSOC);
// 	while($rUnit=$qUnit->fetch()){
// 		@$optxxx    .="<option value=".$rUnit['kodeorganisasi'].">".$rUnit['kodeorganisasi']." - ".$rUnit['namaorganisasi']."</option>";
// 	}

$optxxx="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optxxx.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optxxx.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optxxx.="</optgroup>";
		$optorgsch.="</optgroup>";
	}
}


$opthari="";
$hari="SELECT tanggal FROM kebun_rekappnn";
$res=fetchdata($hari);
foreach ($res as $key => $val) {
    $opthari.="<option value='".$val['tanggal']."'>".$val['tanggal']."</option>";
}

$optperiode="";
$periode="SELECT DISTINCT LEFT(tanggal,10) AS tanggal FROM pabrik_timbangan where LEFT(tanggal,10) !='0000-00-00' order by tanggal";
$res=fetchdata($periode);
foreach ($res as $key => $val) {
    $optperiode.="<option value='".$val['tanggal']."'>".$val['tanggal']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('kebun_2produksidivisi').'</span><br>');
$arrht= "###unit###periode";
echo "<fieldset style='float: left;'>";
echo "<legend><b>Form</b></legend>";
echo "<table>";



echo "<tr>
        <td>".$_SESSION['lang']['kodeorg']."</td>
        <td>:</td>
        <td>
            <select class=select2 id=kebun style=width:200px;>
            ".$optxxx."
            </select>
        </td>
</tr>";

echo "<tr>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td>
         
				<input type='text' class='myinputtext' id='periode' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:195px;'   readonly/> 
				
        </td>
</tr>";


echo"<tr>
		<td align=center colspan=2></td>
		<td>
		<button class=mybutton onclick=\"preview('html',event)\">".$_SESSION['lang']['preview']."</button>
		<button class=mybutton onclick=\"preview('excel',event)\">".$_SESSION['lang']['excel']."</button>
		
		</td>
</tr>";


echo "</table></fieldset>";
// echo "</table>";

// BOX ATAS

CLOSE_BOX('','');

// PREVIEW

OPEN_BOX();
echo "<div id=container style='min-height:450px';></div>";

CLOSE_BOX('','');
echo close_body();

?>