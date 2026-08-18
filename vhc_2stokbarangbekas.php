<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>

<?php
if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe='WORKSHOP'";   
}else{
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='WORKSHOP'";

}
$optOrg="";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";        
}	

$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$iSup="SELECT distinct(a.kodebarang) as kodebarang, b.namabarang, b.satuan FROM ".$dbname.".vhc_stokbarangbekas a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where inactive='0' order by namabarang asc";
$nSup=$owlPDO->query($iSup) or die(print " Gagal: ".PDOException::getMessage());
$nSup->setFetchMode(PDO::FETCH_ASSOC);
while($dSup=$nSup->fetch()){
    $optBrg.="<option value=".$dSup['kodebarang'].">".$dSup['kodebarang']." - ".$dSup['namabarang']."</option>";
}

$arr="##kdOrgRep##kdBrgRep##tgl1Rep##tgl2Rep";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<script language=javascript>
function batal(){
	document.getElementById('kdOrgRep').value='';
	document.getElementById('kdBrgRep').value='';
	document.getElementById('tgl1Rep').value='';	
	document.getElementById('tgl2Rep').value='';
	document.getElementById('printContainer').innerHTML='';	
}
function form(){
    width = '720';
    height = '';
    content = "<fieldset><div id=containerd style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog5(title, content, width, height, ev); 
}


function detail(kodeorg,tanggal,kodebarang){
    form();
	param = 'proses=detail'+'&kodeorg='+kodeorg+'&tanggal='+tanggal+'&kodebarang='+kodebarang;
    tujuan = 'vhc_slave_2stokbarangbekas.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('containerd').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
</script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('vhc_2stokbarangbekas').'</span><br>');
echo "<fieldset style='float:left;'><legend><b>Form</b></legend>
	<table>
        <tr>
		<td>".$_SESSION['lang']['kodeorganisasi']."</td>
		<td>:</td>
		<td><select id=kdOrgRep style=\"width:162px;\" >".$optOrg."</select></td>
        </tr>
	<tr>
		<td>".$_SESSION['lang']['namabarang']."</td>
		<td>:</td>
		<td><select id=kdBrgRep style='width:162px;'>".$optBrg."</select>
		<img id='kdBrgRep' onclick=z.elSearch('kdBrgRep',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
		s/d
		<input type='text' class='myinputtext' id='tgl2Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>
	<tr>
		<td colspan=100></td>
	</tr>
	<tr>
		<td><td><td>
		<button onclick=zPreview('vhc_slave_2stokbarangbekas','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'vhc_slave_2stokbarangbekas.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"<fieldset style='clear:both;max-width:1150'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1150'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();
?>