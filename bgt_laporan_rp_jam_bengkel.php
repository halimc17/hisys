<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$sql ="SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='WORKSHOP' and induk='".$_SESSION['empl']['lokasitugas']."' ORDER BY kodeorganisasi";
$sql ="SELECT distinct tahunbudget FROM ".$dbname.".bgt_budget ORDER BY tahunbudget desc";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch())
			{
			$optThn.="<option value=".$data['tahunbudget'].">".$data['tahunbudget']."</option>";
			}


$optWs="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='WORKSHOP' ORDER BY kodeorganisasi";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch())
			{
			$optWs.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
			}
$arr="##thnbudget##kdWs";	

$optWs="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(17) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($key,0,4)."'");
	$d=$induk[substr($key,0,4)];
	if($d!=$n){			
		$optWs.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optWs.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optWs.="</optgroup>";
	}
}

?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript>
	function batal() {
	    document.getElementById('thnbudget').value = '';
	    document.getElementById('kdWs').value = '';
	    document.getElementById('printContainer').innerHTML = '';
	}

	function getDet(id) {
	    kdTrak = document.getElementById('kdTrak_' + id).getAttribute('value');
	    kdeWs = document.getElementById('kdeWs_' + id).getAttribute('value');
	    thnbudget = document.getElementById('thnbudget').options[document.getElementById('thnbudget').selectedIndex].value;
	    param = "kdTrak=" + kdTrak + "&brsKe=" + id + "&kdeWs=" + kdeWs + "&thnbudget=" + thnbudget;

	    tujuan = "bgt_slave_laporan_rp_jam_bengkel.php";
	    //alert(param);

	    function respon() {
	        if (con.readyState == 4) {
	            if (con.status == 200) {
	                busy_off();
	                if (!isSaveResponse(con.responseText)) {
	                    alert(con.responseText);
	                } else {
	                    // Success Response
	                    //	alert(con.responseText);
	                    document.getElementById('detail_' + id).innerHTML = con.responseText;
	                }
	            } else {
	                busy_off();
	                error_catch(con.status);
	            }
	        }
	    }
	    //  alert(fileTarget+'.php?proses=preview', param, respon);
	    post_response_text(tujuan + '?' + 'proses=getDetail', param, respon);
	}

	function printFile(param, tujuan, title, event) {
	    tujuan = tujuan + "?" + param;
	    width = '200';
	    height = '150';

	    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	        showDialog1(title, content, width, height, event);
	}

	function dataKeExcel(event, kdTrak, kdeWs, thnBudget) {
	    kodeTraksi = kdTrak;
	    kodeWs = kdeWs;
	    thnBudget = thnbudget;
	    param = 'kdTrak=' + kodeTraksi + '&kdWs=' + kodeWs + '&thnbudget=' + thnBudget + '&proses=ExcelAlokasi';
	    //alert (param);

	    tujuan = 'bgt_slave_laporan_rp_jam_bengkel.php';
	    judul = 'Report Ms.Excel';
	    // printFile(param,tujuan,judul,event);

	    tujuan = tujuan + "?" + param;
	    printnopopup(tujuan);
	}

	function closeDet(id) {
	    document.getElementById('detail_' + id).innerHTML = '';
	}

	function printFile2(param, tujuan, title, event) {
	    tujuan = tujuan + "?" + param;
	    width = '1200';
	    height = '450';
	    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"

	        showDialog1(title, content, width, height, event);
	}

	function dataKePdf(event, kdTrak, kdeWs, thnBudget) {
	    kodeTraksi = kdTrak;
	    kodeWs = kdeWs;
	    thnBudget = thnbudget;
	    param = 'kdTrak=' + kodeTraksi + '&kdWs=' + kodeWs + '&thnbudget=' + thnBudget + '&proses=pdfAlokasi';
	    //alert (param);

	    tujuan = 'bgt_slave_laporan_rp_jam_bengkel.php';
	    judul = 'Report Detail PDF ' + kdeWs + ' Tahun ' + thnBudget + ' ';
	    printFile2(param, tujuan, judul, event)
	    //alert (param);

	}

</script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_rp_jam_bengkel').'</span><br>');
?>

<div style="margin-bottom: 30px;">
<fieldset style="float:left;">
<legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>

<table border="0" cellspacing="1" >
    <tr><td><label><?php echo $_SESSION['lang']['budgetyear']?></label></td><td>:</td><td><select class='select2' id="thnbudget" name="thnbudget" style="width:175px;" ></option><?php echo $optThn?></select></td></tr>
    <tr><td><label><?php echo $_SESSION['lang']['workshop']?></label></td><td>:</td><td><select class='select2' id="kdWs" name="kdWs" style="width:175px;"></option><?php echo $optWs?></select></td></tr>

    <tr>
	<td></td>
	<td></td>
    <td colspan=2>
      
        <button onclick="zPreview('bgt_slave_laporan_rp_jam_bengkel','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['preview']?></button>
        <button onclick="zExcel(event,'bgt_slave_laporan_rp_jam_bengkel.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['excel']?></button>   
        <!--<button onclick="zPdf('bgt_slave_laporan_rp_jam_bengkel','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['pdf']?></button>-->
        <button onclick="batal()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>
</table>
</fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>

<div id='printContainer' style='overflow:auto;height:450px;'>
</div>

<?
CLOSE_BOX();
echo close_body();
?>