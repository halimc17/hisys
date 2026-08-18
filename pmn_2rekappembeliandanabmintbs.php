<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/pmn_2rekappembeliandanabmintbs.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');

$_SESSION['tlistsupplier']=array();

OPEN_BOX("","<span class=judul>".getMenu('pmn_2rekappembeliandanabmintbs')."<br></span>");


$optnama=$opttipe=$optunit=$opttipetbs="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";

#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(1);
foreach($arrunit as $val=>$nama){
    $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
} 

$str = "SELECT DISTINCT tipe FROM ".$dbname.".pmn_hargabelitbs ORDER BY tipe ASC";
$res = fetchdata($str);
foreach ($res as $bar) {
	$opttipetbs .= "<option value='" . $bar['tipe'] . "'>" . $bar['tipe'] . "</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=fetchdata($str);
foreach($res as $bar){
	$optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

$str="select distinct(nama) as nama from ".$dbname.".kebun_spbpetani order by nama asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optnama.="<option value='".$bar['nama']."'>".$bar['nama']."</option>";
}

$optjenis="<option value='rekap'>Rekap Per KUD</option>";
$optjenis.="<option value='detail'>Rekap Per KUD Per Petani</option>";
$optjenis.="<option value='detail2'>Rekap Per KUD Per Petani (Pph)</option>";

echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td><select class='select2' id=kodeunit  style='width:183px;';>".$optunit."</select></td>
		</tr>
		<tr>		
			<td>".$_SESSION['lang']['tipe']." TBS</td>
			<td>:</td>
			<td><select class='select2' id=tipetbs  style='width:183px;'>".$opttipetbs."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>		
			<td>
				<input type=text class=myinputtext id=tanggalmulai placeholder='Mulai' name=tanggalmulai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
				s/d
				<input type=text class=myinputtext id=tanggalsampai placeholder='Sampai' name=tanggalsampai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>			
			</td>
      </tr>
	  <tr>
			<td>".$_SESSION['lang']['jenis']."</td>
			<td>:</td>
			<td><select class='select2' onchange=getnamakud(this.value) id=jenis  style='width:183px;'>".$optjenis."</select></td>
		</tr>
		<tr id=consupplier style=display:none>
			<td>".$_SESSION['lang']['supplier']."</td>
			<td>:</td>
			<td><select class='select2' id=supplier onchange='chooseTarget(this.value)' style='width:183px;'></select></td>
		</tr>
		<tr id=conlistsupplier>
			<td colspan=2></td>
			<td>
				<div id='listsupplier' style='width:250px'></div>
			</td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['nama']." Petani</td>
			<td>:</td>
			<td><select class='select2' id=petani  style='width:183px;'>".$optnama."</select></td>
		</tr>
      <tr>
        <td colspan=2></td>
        <td colspan=4>
			<button class=mybutton onclick=preview('html')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=preview('excel')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=preview('pdf')>".$_SESSION['lang']['pdf']."</button>
			<!--<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>-->
		</td>
      <tr>
     </table>
   
   </fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"<div  class='table-scroll' style='width:100%;height:65vh;overflow:auto;' id=container></div>";
CLOSE_BOX();
close_body();
?>