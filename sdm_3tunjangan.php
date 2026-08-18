<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/zForm.php');
echo open_body();
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/sdm_3tunjangan.js?v=<?php echo time(); ?>'></script>



<?
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="SELECT distinct periode FROM ".$dbname.".sdm_5periodegaji where sudahproses=0 and kodeorg='".$_SESSION['empl']['lokasitugas']."'  order by periode desc limit 10";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch()){
        $optper.="<option value=".$data['periode'].">".$data['periode']."</option>";
}	
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."'";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

$optTipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTipe="select * from ".$dbname.".sdm_5tipekaryawan where id not in ('0','7','8') and aktif='1'";
$nTipe=$owlPDO->query($iTipe) or die(print " Gagal: ".PDOException::getMessage());
$nTipe->setFetchMode(PDO::FETCH_ASSOC);
while($dTipe=$nTipe->fetch()){
    $optTipe.="<option value=".$dTipe['id'].">".$dTipe['tipe']."</option>";
}



$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iJenis="select * from ".$dbname.".sdm_ho_component where id in ('28') ";
$nJenis=$owlPDO->query($iJenis) or die(print " Gagal: ".PDOException::getMessage());
$nJenis->setFetchMode(PDO::FETCH_ASSOC);
while($dJenis=$nJenis->fetch()){
    $optJenis.="<option value=".$dJenis['id'].">".$dJenis['name']."</option>";
}


$optGaji="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iGaji="select distinct(tahun) as tahun from ".$dbname.".sdm_5gajipokok where 1=1 order by tahun desc";
$nGaji=$owlPDO->query($iGaji) or die(print " Gagal: ".PDOException::getMessage());
$nGaji->setFetchMode(PDO::FETCH_ASSOC);
while($dGaji=$nGaji->fetch()){
    $optGaji.="<option value=".$dGaji['tahun'].">".$dGaji['tahun']."</option>";
}

$optstkawin="<option value=''>Seluruhnya</option>";
$arrsstk=getEnum($dbname,'datakaryawan','statusperkawinan');
foreach($arrsstk as $kei=>$fal)
{
        if($_SESSION['language']=='EN' && $fal=='Menikah')
            $fal='Married';
        if($_SESSION['language']=='EN' && $fal=='Janda')
               $fal='Widow';       
        if($_SESSION['language']=='EN' && $fal=='Duda')
               $fal='Widower';      
        if($_SESSION['language']=='EN' && $fal=='Lajang')
               $fal='Single';              
        $optstkawin.="<option value='".$kei."'>".$fal."</option>";
} 

?>

<?
include('master_mainMenu.php');
if($_SESSION['language']=='EN'){
   OPEN_BOX('','<span class=judul>'.strtoupper('Allowance Process').'</span><br>');
}else{
    OPEN_BOX('','<span class=judul>'.strtoupper('Proses Tunjangan').'</span><br>');
}
$arr="##unit##per##jenis##tipe##tahun##tgl##pengali##makan##kawin##bulanawal##bulanakhir";	

echo "<fieldset style=float:left><legend><b>Form</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unit onchange=uang() style='width:140px;'>".$optOrg."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td><select id=per style='width:140px;'>".$optper."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td><select id=jenis  style='width:140px;'>".$optJenis."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['tipekaryawan']."</td>
        <td>:</td>
        <td><select id=tipe style='width:140px;'>".$optTipe."</select></td>
    </tr>
    <tr>
        <td> Basis Gaji </td>
        <td>:</td>
        <td><select id=tahun style='width:140px;'>".$optGaji."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['tanggal']." Cut Off</td>
        <td>:</td>
        <td><input type=text class=myinputtext  id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:80px;\" readonly/></td>
    </tr>
    <tr hidden>
        <td>".$_SESSION['lang']['pengali']."</td>
        <td>:</td>
        <td><input type=text id=pengali value=1 disabled size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=5 style=\"width:80px;\"></td>
    </tr>
	<tr hidden>
        <td>Batas Awal ".$_SESSION['lang']['bulan']."</td>
        <td>:</td>
        <td><input type=text id=bulanawal value=1 size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=5 style=\"width:80px;\"></td>
    </tr>
	<tr hidden>
        <td>Batas Akhir ".$_SESSION['lang']['bulan']."</td>
        <td>:</td>
        <td><input type=text id=bulanakhir value=12 size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=5 style=\"width:80px;\"></td>
    </tr>
    <tr hidden>
        <td>".$_SESSION['lang']['uangmakan']."</td>
        <td>:</td>
        <td><input type=text id=makan value=0 disabled size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4 style=\"width:125px;\"></td>
    </tr>
    
    <tr hidden>
        <td >".$_SESSION['lang']['statusperkawinan']."</td>
        <td>:</td>
        <td><select id=kawin style='width:125px;'>".$optstkawin."</select></td>
    </tr>
    


    ";



	
echo "	<tr>
		<td colspan=3 align=right>
		<button onclick=zPreview('sdm_slave_3tunjangan','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'sdm_slave_3tunjangan.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo "
<fieldset ><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 

CLOSE_BOX();
echo close_body();




?>