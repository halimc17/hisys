<?//@Copy nangkoelframework?><?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/vhc_vlkpakai.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$opttipe=$optOrg=$optvhc=$optbrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}	

$str = "SELECT kodevhc,detailvhc FROM ".$dbname.".vhc_5master where kodeorg='".$_SESSION['empl']['lokasitugas']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optvhc.="<option value='".$bar['kodevhc']."'>".$bar['kodevhc']." - ".$bar['detailvhc']."</option>";
}

$str = "SELECT kodebarang,namabarang FROM ".$dbname.".log_5masterbarang where namabarang like 'Ban %' and kodebarang not like '9%' order by kodebarang asc";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbrg.="<option value='".$bar['kodebarang']."'>".$bar['kodebarang']." - ".$bar['namabarang']."</option>";
}
//$opttipe="<option value=''>Pemakaian</option>";
$opttipe.="<option value='1'>Pemasangan</option>";
$opttipe.="<option value='2'>Pelepasan</option>";

?>


<?php
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>Vulkanisir Tyre</span>');
}else{
	OPEN_BOX('','<span class=judul>Ban Vulkanisir</span>');
	
}
echo "<br><br>";


$arr="##unit##kdbrg##tgl1##tgl2##kdvhc##tipe";
$frm[0].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select id=unit style=\"width:163px;\" >".$optOrg."</select></td>
        </tr>
		<tr>
			<td>".$_SESSION['lang']['tipetransaksi']."</td>
			<td>:</td>
			<td><select id=tipe style=\"width:150px;\" >".$opttipe."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodevhc']."</td>
			<td>:</td>
			<td><select id=kdvhc style=\"width:150px;\" >".$optvhc."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodebarang']."</td>
			<td>:</td>
			<td><select id=kdbrg style='width:163px;'>".$optbrg."</select>
				<img id=kdbrg_find onclick=z.elSearch('kdbrg',event) class=resicon src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
			</td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
			s/d
			<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
		</tr>	

	
	<tr>
		<td><td><td>
		<button onclick=zPreview('vhc_slave_2vlk','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'vhc_slave_2vlk.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>		
		<button onclick=batalRep() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[0].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:300px;max-width:1150'; >
</div></fieldset>";


$hfrm[0]=$_SESSION['lang']['laporan'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();




?>