<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2cs').'</span>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>


<script>


function changediv(unit) {
	param = 'unit='+unit.value;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('divisi').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_2cs.php?proses=changediv', param, respon);
}

function changediv2(unit) {
	param = 'unit='+unit.value;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('divisi2').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_2cs.php?proses=changediv', param, respon);
}

</script>

<?
$optorg=$optper='';
$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";
//$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'",'2','0',true);

$str="select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah=''";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."' 
		order by kodeorganisasi asc ";
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$count = 0;
$firstUnit = "";
while($bar=$res->fetch()){
	if($count==0){
		$firstUnit = $bar['kodeorganisasi'];
	}
	
	$s="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	$count++;
}

$optDiv="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
	$s="";
	if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">".$bar['namaorganisasi']."</option>";
}

$optDiv2="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optDiv2.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
		$s="";
	if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optDiv2.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">".$bar['namaorganisasi']."</option>";
}

$optTt="";
$str = "select distinct(a.tahuntanam) as tt from ".$dbname.".setup_blok a 
		left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
		where b.induk like '%".@key($optPT)."%' and (a.tahuntanam!='0' or a.tahuntanam!='') 
		order by a.tahuntanam asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optTt.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
    $optTt.="<option value=" . $bar['tt'] . ">".$bar['tt']."</option>";
}


$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrIP=array("I"=>"INTI","P"=>"PLASMA");
$optIP="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrIP as $res => $bar)
{
	$optIP.="<option value=".$res.">".$bar."</option>";
}

$frm[0]='';
$frm[1]='';

$arr1 = "##pt##kdorg##tgl1##tgl2##divisi##tt##ip";
$frm[0]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','".$_SESSION['lang']['all']."')  style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi onchange=getThnTnm(this,'tt','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optDiv . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select id=tt style=\"width:164px;\">" . $optTt . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['intiplasma'] . "</td>
                    <td>:</td>
                    <td><select id=ip style=\"width:164px;\">" . $optIP . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10'  readonly>
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10'  readonly></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2cs','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2cs.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'; >
</div></fieldset>"; //<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>

$arr2 = "##pt2##kdorg2##per1##per2##divisi2##tt2##ip2";
$frm[1]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt2 onchange=getUnitThnTnm(this,'kdorg2,tt2','divisi2','".$_SESSION['lang']['all']."') style=\"width:159px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select onchange=getAfdThnTnm(this,'divisi2,tt2','".$_SESSION['lang']['all']."') id=kdorg2 style=\"width:159px;\">" . $optorg . "</select></td>
                </tr>
				
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi2 onchange=getThnTnm(this,'tt2','".$_SESSION['lang']['all']."') style=\"width:159px;\">" . $optDiv2 . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select id=tt2 style=\"width:159px;\">" . $optTt . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['intiplasma'] . "</td>
                    <td>:</td>
                    <td><select id=ip2 style=\"width:159px;\">" . $optIP . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td>
                        <select id=per1 style=\"width:67px;\">" . $optper . "</select>
                        s/d <select id=per2 style=\"width:67px;\">" . $optper . "</select>
                    </td>
                </tr>
                
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2cs_v2','" . $arr2 . "','printContainerv2') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2cs_v2.php','" . $arr2 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainerv2' style='overflow:auto;height:400px;width:100%'; >
</div></fieldset>"; //<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>




$hfrm[0]=$_SESSION['lang']['tanggal'];
$hfrm[1]=$_SESSION['lang']['periode'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,'100%');	

CLOSE_BOX();
echo close_body();
?>