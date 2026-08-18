<?//@Copy nangkoelframework
//-----------------ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();


?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/sdm_3uangmakan.js'></script>

<?

$frm[0]='';


$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="SELECT distinct periode FROM ".$dbname.".sdm_5periodegaji order by periode desc limit 10";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch())
{
        $optper.="<option value=".$data['periode'].">".$data['periode']."</option>";
}			


if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."'"
        . "and kodeorganisasi not like '%HO%' ";
}
else
{
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'"
        . "and kodeorganisasi not like '%HO%' ";
} 
 $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch())
 {
        $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}


$optTipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTipe="select * from ".$dbname.".sdm_5tipekaryawan where id not in ('0','7','8') ";
$nTipe=$owlPDO->query($iTipe) or die(print " Gagal: ".PDOException::getMessage());
$nTipe->setFetchMode(PDO::FETCH_ASSOC);
while($dTipe=$nTipe->fetch())
{
    $optTipe.="<option value=".$dTipe['id'].">".$dTipe['tipe']."</option>";
}

include('master_mainMenu.php');

$frm[0]='';
OPEN_BOX('','<span class=judul>'.getMenu('sdm_3uangmakan').'<br></span>');

if($_SESSION['language']=='EN'){
	$hfrm[0]='Fixed Premium';
}else{
	$hfrm[0]='Premi Tetap';
}
$arr="##per##unit##tipe##rupiah";


$arrtpremitetap="##perpremitetap##unitpremitetap##tipepremitetap";

$frm[0].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=perpremitetap style='width:125px;'>".$optper."</select></td>
	</tr>";

$frm[0].="  <tr>
            <td>".$_SESSION['lang']['unitkerja']."</td>
            <td>:</td>
            <td><select id=unitpremitetap  style='width:125px;'>".$optOrg."</select></td>
	</tr>";

$frm[0].="  <tr>
            <td>".$_SESSION['lang']['tipekaryawan']."</td>
            <td>:</td>
            <td><select id=tipepremitetap style='width:125px;'>".$optTipe."</select></td>
	</tr>";	
	


	
$frm[0].="	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('sdm_slave_3premitetap','".$arrtpremitetap."','printContainerpremitetap') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		
		
		<button onclick=batalpremitetap() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[0].="
<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainerpremitetap'>
</div></fieldset>";




//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();


?>
