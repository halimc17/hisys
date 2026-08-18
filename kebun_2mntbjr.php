<?
//@uhr
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2mntbjr').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?
$optOrg=$optper="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' order by kodeorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    //$optOrg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}


$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}


$frm[0]='';
$frm[1]='';


$arr1 = "##kdorg##prd";
$frm[1]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg style=\"width:180px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:180px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2mntbjr','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2mntbjr.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:55vh;'; >
</div></fieldset>";

$arr2 = "##kdorg1##tgl##tgl2";
$frm[0]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg1 style=\"width:180px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td>
                        <input style=\"width:70px;\" type='text' class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='23' maxlength='10' readonly> S/D
                        <input style=\"width:75px;\" type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='23' maxlength='10' readonly>
                    </td>
                    <td>
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2mntbjrharian','" . $arr2 . "','printContainerv2') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2mntbjrharian.php','" . $arr2 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainerv2' style='overflow:auto;height:55vh;'; >
</div></fieldset>";

$hfrm[1]='Bulanan';
$hfrm[0]='Harian';



//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,"100%");	

CLOSE_BOX();
echo close_body();
?>