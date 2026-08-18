<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>

function batalin(){
	document.getElementById('gudang').value='';
	document.getElementById('sup').value='';
	document.getElementById('tgl').value='';
	document.getElementById('printContainer').innerHTML='';
}

function batalout(){
	document.getElementById('gudangout').value='';
	document.getElementById('tglout').value='';
	document.getElementById('printContainerout').innerHTML='';
}


</script>

<?
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul><B>'.strtoupper($_SESSION['lang']['masuk'].' '.$_SESSION['lang']['keluar'].' '.$_SESSION['lang']['material']).'</B></span><br>');

$tipetransaksi=$optgdg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '%".$_SESSION['empl']['lokasitugas']."%' 
		order by namaorganisasi asc ";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optgdg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$optsup.="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select supplierid,namasupplier from ".$dbname.".log_5supplier";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optsup.="<option value=".$bar['supplierid'].">".$bar['namasupplier']."</option>";
}

//$tipetransaksi.="<option value='1'>".$_SESSION['lang']['masuk']."</option>";
///$tipetransaksi.="<option value='5'>".$_SESSION['lang']['keluar']."</option>";

$frm[0]='';
$frm[1]='';
 
$arr="##gudang##tgl##sup";	
$frm[0].="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=gudang style=\"width:150px;\">".$optgdg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td>
						<input onkeypress=\"return tanpa_kutip(event)\" type=text class=myinputtext id=tgl name=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>
					</td>
                </tr>
				
				<tr>
                    <td>".$_SESSION['lang']['namasupplier']."</td>
                    <td>:</td>
                    <td>
						<select  id=sup style=\"width:150px;\">".$optsup."</select>
						<img  id='sup' onclick=z.elSearch('sup',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('log_slave_2bppb','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'log_slave_2bppb.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    <button onclick=batalin() class=mybutton name=batalin id=batalin>".$_SESSION['lang']['cancel']."</button>
                    
					</td>
                </tr>
            </table>
</fieldset>";

$frm[0].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";


$arrout="##gudangout##tglout";	
$frm[1].="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=gudangout style=\"width:150px;\">".$optgdg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td>
						<input onkeypress=\"return tanpa_kutip(event)\" type=text class=myinputtext id=tglout name=tglout onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>
					</td>
                </tr>
				
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('log_slave_2bppbout','".$arrout."','printContainerout') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'log_slave_2bppbout.php','".$arrout."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    <button onclick=batalout() class=mybutton name=batalin id=batalin>".$_SESSION['lang']['cancel']."</button>
					</td>
                </tr>
            </table>
</fieldset>";

$frm[1].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainerout' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";



$hfrm[0]=$_SESSION['lang']['masuk'];
$hfrm[1]=$_SESSION['lang']['keluar'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();					
?>