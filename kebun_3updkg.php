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
<script language=javascript src='js/kebun_3updkg.js'></script>

<?

$frm[0]='';

$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="SELECT distinct periode FROM ".$dbname.".sdm_5periodegaji order by periode desc limit 10";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);

while ($data=$qry->fetch())
{
        $optper.="<option value=".$data['periode'].">".$data['periode']."</option>";
}			

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."'"
        . "and tipe='KEBUN' ";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);

while ($data=$qry->fetch())
{
        $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}




include('master_mainMenu.php');


OPEN_BOX();
$arr="##tgl1##tgl2##unit";

$frm[0].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
	<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td>
                <input type=text onchange=getNodok() class=myinputtext id=tgl1 onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:100px;/>
                s/d <input type=text onchange=getNodok() class=myinputtext id=tgl2 onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:100px;/>
            </td>
	</tr>";

$frm[0].="  <tr>
            <td>Unit</td>
            <td>:</td>
            <td><select id=unit style='width:125px;'>".$optOrg."</select></td>
	</tr>";




	
$frm[0].="	<tr>
		<td colspan=100>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=100>
		<button onclick=zPreview('kebun_slave_3updkg','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[0].="
<fieldset><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 



$hfrm[0]='Uang Makan';
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();


?>