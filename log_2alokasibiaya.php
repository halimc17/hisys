<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');

?>

<script type="text/javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script>
    function zExceldetail(ev,tujuan,passParam)
	{
		judul='Report Excel';
			var passP = passParam.split('##');
		
		var param = "proses=exceldetail";
		for(i=0;i<passP.length;i++) {
		   // var tmp = document.getElementById(passP[i]);
			a=i;
			param += "&"+passP[a]+"="+passP[i+1];
		}
		
		printFile(param,tujuan,judul,ev)	
	}
	
	function printFile(param,tujuan,title,ev)
	{
	   tujuan=tujuan+"?"+param;  
	   width='700';
	   height='250';
	   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	   showDialog1(title,content,width,height,ev); 	
	}

	function tambahBarang(title,ev)
	{
		
		content= "<div id=formBarang style=\"max-height:250px;max-width:350;overflow:auto;\"></div>";
		title='Find Supplier';
		height='';
		width='';
		showDialog1(title,content,width,height,ev);	
		getListBarang();
	}
	
	function getListBarang()
	{
		
		param='proses=getListBarang';
		tujuan = 'log_slave_2alokasibiayaPembelian.php';
		post_response_text(tujuan, param, respog);		
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
									alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						//alert(con.responseText);
						document.getElementById('formBarang').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		} 
			
	}

	function cariListBarang()
	{
		namaBarangCari=document.getElementById('namaBarangCari').value;
		param='proses=getListBarang'+'&namaBarangCari='+namaBarangCari;
	  
		tujuan = 'log_slave_2alokasibiayaPembelian.php';
		post_response_text(tujuan, param, respog);		
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						//alert(con.responseText);
						document.getElementById('formBarang').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		} 
			
	}
	
	function moveDataBarang(kdsup,nmsup)
	{
		document.getElementById('kdsup').value=kdsup;
		document.getElementById('nmsup').value=nmsup;
		document.getElementById('listCariBarang').style.display='none';
		closeDialog();
	}
	
	function getnopo()
	{
		periode=document.getElementById('periodeBeli').value;
		param='proses=getnopo'+'&periodeBeli='+periode;
	  
		tujuan = 'log_slave_2alokasibiayaPembelian.php';
		post_response_text(tujuan, param, respog);		
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						//alert(con.responseText);
						document.getElementById('nopo').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		} 
	}
	
	
	
</script>

<?php
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $optPt="<option value=''>".$_SESSION['lang']['all']."</option>";
    $iPt="select * from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";
}
else 
{
    $iPt="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' ";
}
$nPt=$owlPDO->query($iPt) or die(print " Gagal: ".PDOException::getMessage());
$nPt->setFetchMode(PDO::FETCH_ASSOC);
while($dPt=  $nPt->fetch())
{
    $optPt.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['namaorganisasi']."</option>";
}

//===================

// if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
// { 
    // $optpo="<option value=''>".$_SESSION['lang']['all']."</option>";
    
    // $str="select distinct nopo as nopo from ".$dbname.".log_poht where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT')";
    // $str=$owlPDO->query($str);
	// $str->setFetchMode(PDO::FETCH_OBJ);
	// $optpo="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
	// while($bar=$str->fetch())
	// {
		// $optpo.="<option value='".$bar->nopo."'>".$bar->nopo."</option>";
	// }
	
// } else {
       
    // $optpo="<option value=''>".$_SESSION['lang']['all']."</option>";
	
    // $str="select distinct nopo as nopo from ".$dbname.".log_poht where kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' )";
    // $str=$owlPDO->query($str);
	// $str->setFetchMode(PDO::FETCH_OBJ);
	// $optpo="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
	// while($bar=$str->fetch())
	// {
		// $optpo.="<option value='".$bar->nopo."'>".$bar->nopo."</option>";
	// }

// }
$optpo="<option value=''></option>";
//===================

$arr="##periodeBeli##pt##kdsup##nopo";
$arr2="##periode";


$str="select distinct periode from ".$dbname.".log_5saldobulanan order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
	$optPeriode.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper('PURCHASED GOODS ALLOCATIONS').'</span>');
echo "<div>
<fieldset style='float: left;'>
<legend><b>".$_SESSION['lang']['form']."</b></legend>";
echo "<table cellspacing=1 border=0 >
<tr>
    <td><label>".$_SESSION['lang']['pt']."</label></td><td>:</td>
    <td><select id=pt name=pt style='width:203px'>".$optPt."</select></td>
</tr>

<tr>
    <td><label>".$_SESSION['lang']['periode']."</label></td><td>:</td>
    <td><select id=periodeBeli name=periode onchange=getnopo() style='width:75px'>".$optPeriode."</select></td>
</tr>


<tr>
	<td><label>".$_SESSION['lang']['nopo']."</label></td><td>:</td>
	<td><select id=nopo style='width:203px'>".$optpo."</select>
	<img id='kegiatan' onclick=z.elSearch('nopo',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
	</td>
</tr>

<tr>
	<td>".$_SESSION['lang']['supplier']."</td><td>:</td>
	<td><input type=text  id=kdsup disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:70px;\">
		<input type=text  id=nmsup disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:121px;\">
	<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$_SESSION['lang']['find']."',event)></td></td>
</tr>




<tr><td><td><td><button onclick=\"zPreview('log_slave_2alokasibiayaPembelian','".$arr."','printContainer')\" class=mybutton name=preview id=preview>Preview</button>
    <!--<button onclick=\"zPdf('sdm_slave_2slipGajiHarian','".$arr."','printContainer')\" class=mybutton name=preview  id=preview>PDF</button>-->
        <button onclick=\"zExcel(event,'log_slave_2alokasibiayaPembelian.php','".$arr."')\" class=mybutton name=preview id=preview>Excel</button></td></tr>

</table>
</fieldset>
</div>";

CLOSE_BOX();
OPEN_BOX();

echo "<div style='margin-bottom: 30px;'>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px'>

</div></fieldset>";
//===============================================
echo "</tbody></table></fieldset>";

//===============================================	
?>

<?php
CLOSE_BOX();
echo close_body();
?>