<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_2printqrcode').'</span>');
?>
<script language=javascript src='js/keu_2printqrcode.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$where='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where.="";
} else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$where.=" and induk ='".$_SESSION['empl']['kodeorganisasi']."' and tipe != 'HOLDING'";
}else{
	$where.=" and kodeorganisasi ='".$_SESSION['empl']['lokasitugas']."'";
}

$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)='4' ".$where." order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";

}

$str = "select * from " . $dbname . ".sdm_5tipeasset order by namatipe asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch()) {
    $opttipe.="<option value=" . $bar['kodetipe'] . ">" . $bar['kodetipe'] . " - ".$bar['namatipe']."</option>";
}

$str = "select * from " . $dbname . ".log_5klbarang order by kode asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optklbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch()) {
    $optklbarang.="<option value=" . $bar['kode'] . ">" . $bar['kode'] . " - ".$bar['kelompok']."</option>";
}

$frm[0]='';
$frm[1]='';
$frm[0]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:164px;\">" . $optunit . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['posisiasset'] . "</td>
                    <td>:</td>
                    <td><select id=posisiasset style=\"width:164px;\">" . $optunit . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tipeasset'] . "</td>
                    <td>:</td>
                    <td><select id=tipeasset onchange=getsub() style=\"width:164px;\">" . $opttipe . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['subtipeasset'] . "</td>
                    <td>:</td>
                    <td><select id=subtipeasset style=\"width:164px;\">
						<option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button class=mybutton onclick=asset('html')>".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=asset('pdf')>".$_SESSION['lang']['pdf']."</button>
				</tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
					<button class=mybutton onclick=generateqrcode('asset')>Generate</button>
					<button class=mybutton onclick=batal('asset')>".$_SESSION['lang']['cancel']."</button>
                    </td>
                </tr>
            </table>
</fieldset><div style=clear:both></div><hr>";
$frm[0].="<fieldset style='clear:both;min-height:400px'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer'>
</div></fieldset>";

$frm[1].="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
                    <td>" . $_SESSION['lang']['kelompokbarang'] . "</td>
                    <td>:</td>
                    <td><select id=klbarang onchange=getsubklbarang(this.value) style=\"width:164px;\">" . $optklbarang . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['subkelompokbarang'] . "</td>
                    <td>:</td>
                    <td><select id=subklbarang onchange=getkodebarang() style=\"width:164px;\">
						<option value=''>".$_SESSION['lang']['pilihdata']."</option>
						</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['kodebarang'] . "</td>
                    <td>:</td>
                    <td><select id=kodebarang style=\"width:164px;\">
						<option value=''>".$_SESSION['lang']['all']."</option>
						</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button class=mybutton onclick=barang('html')>".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=barang('pdf')>".$_SESSION['lang']['pdf']."</button>
				</tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
					<button class=mybutton onclick=generateqrcode('barang')>Generate</button>
					<button class=mybutton onclick=batal('barang')>".$_SESSION['lang']['cancel']."</button>
                    </td>
                </tr>
            </table>
</fieldset><div style=clear:both></div><hr>";
$frm[1].="<fieldset style='clear:both;min-height:400px'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainerBarang'>
</div></fieldset>";

$hfrm[0]=$_SESSION['lang']['daftarasset'];
$hfrm[1]=$_SESSION['lang']['materialmaster'];

drawTab('FRM',$hfrm,$frm,300,'100%');	
CLOSE_BOX();
echo close_body();
?>