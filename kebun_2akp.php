<?
//@uhr
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<style type="text/css">
    .table-scroll table {
        min-width: 0px !important;
    }
</style>
<script>
    function getMandor() {
        var kdorg = document.getElementById('kdorg0').value;
        var afdeling = document.getElementById('afdeling').value;
        var param = 'kebun=' + kdorg + '&divisi=' + afdeling + '&proses=getMandor';
        var tujuan = 'kebun_slave_getmandor.php';

        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    } else {
                        document.getElementById('mandor').innerHTML = con.responseText;
                        $('#mandor').select2();
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        post_response_text(tujuan, param, respon);
    }
</script>


<?
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2akp').'</span><br>');

$optOrg=$optper="";
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";

// $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' order by induk, namaorganisasi asc ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
// 	$d=$induk[$bar['kodeorganisasi']];
// 	if($d!=$n){			
// 		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
// 	}
//     $optOrg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// 	$n=$d;
// 	if($d!=$n){			
// 		$optOrg.="</optgroup>";
// 	}
// }

foreach(getOrgDetail(23) as $key => $val){
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

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}


$frm[0]='';
$frm[1]='';
$frm[2]='';

$arr0 = "##kdorg0##tgl1##tgl2##afdeling##mandor##tipe0";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg0 style=\"width:250px;\" onchange=\"getAfdeling(this,'afdeling','','AFDELING'); setTimeout(getMandor, 500);\">" . $optOrg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=afdeling style=\"width:250px;\" onchange=\"getMandor()\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['kemandoran'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=mandor style=\"width:250px;\"><option value=''>".$_SESSION['lang']['all']."</option></select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td>
                        <input type=text id=tgl1 class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:63px;>
                        ".$_SESSION['lang']['sd']."
                        <input type=text id=tgl2 class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:63px;>
                    </td>
                </tr>
                <tr>
                    <td colspan=2><input type=hidden id=tipe0 value=laporan></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2akp','" . $arr0 . "','printContainer0') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2akp.php','" . $arr0 . "') class=mybutton name=excel id=excel>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"
<div id='printContainer0' style='height:430px;max-width:100%'; ></div>";
/* 
$arr1 = "##kdorg##prd##tipe1";
$frm[1]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:250px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=prd style=\"width:250px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td colspan=2><input type=hidden id=tipe1 value=persen></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2akp','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2akp.php','" . $arr1 . "') class=mybutton name=excel id=excel>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='max-height:330px;max-width:100%'; >
</div></fieldset>";

$arr2 = "##kdorg1##prd1";
$frm[2]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg1 style=\"width:163px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=prd1 style=\"width:163px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2rotasi','" . $arr2 . "','printContainerv2') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2rotasi.php','" . $arr2 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainerv2' style='overflow:auto;height:400px;max-width:100%'; >
</div></fieldset>";

$arr3 = "##kdorg2##prd2";
$frm[3]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg2 style=\"width:163px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=prd2 style=\"width:163px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2output','" . $arr3 . "','printContainerv3') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2output.php','" . $arr3 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>


<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainerv3' style='overflow:auto;height:400px;max-width:100%'; >
</div></fieldset>";



    $hfrm[0]="AKP";
    $hfrm[1]=$_SESSION['lang']['persenakp'];
    $hfrm[2]=$_SESSION['lang']['rotasipanen'];
    $hfrm[3]=$_SESSION['lang']['outputpanen']; 




//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,"100%");	
 */
CLOSE_BOX();
echo close_body();
?>