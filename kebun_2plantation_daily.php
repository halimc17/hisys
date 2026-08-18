<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script languange=javascript1.2 src='js/kebun_2plantation_daily.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>

<?
//KEBUN 
//$optkebun=$optdivisi=$frm[0] = $frm[1] = "";
$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdivisi=$optkebun="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optkebun.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

//divisi
$sData="select distinct periode from ".$dbname.".kebun_perawatan_vw 
		        where unit<>'' order by periode desc ";
		        //exit('error'.$sData);
		$rData=fetchData($sData);
foreach($rData as $row=>$isiDt){
	$optperiode.="<option value='".$isiDt['periode']."'>".$isiDt['periode']."</option>";
}
$arrTipe=array("0"=>"Daily","1"=>"SUMMARY");//,"2"=>"Annual"
foreach ($arrTipe as $key => $value) {
	$optTipe.="<option value='".$key."'>".$value."</option>";
}
$optKelompok="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrKelompok=array("TB"=>"Land Clearing","TBM"=>"Immature","TM"=>"Mature");
foreach ($arrKelompok as $key => $value) {
	$optKelompok.="<option value='".$key."'>".$value."</option>";
}


OPEN_BOX('','<span class=judul >'.getMenu('kebun_2plantation_daily').'</span>');
echo"<div style=clear:both;></div><fieldset style=float:left;>
	<legend>".$_SESSION['lang']['form']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>:</td>
			<td><select id=kebun style=width:150px onchange=getperiode()>".$optkebun."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td><select id=tipe name=tipe  style=width:150px onchange=gantiObjectnya()>".$optTipe."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kelompok']."</td>
			<td>:</td>
			<td><select id=groupId name=groupId  style=width:150px>".$optKelompok."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td><select id=divisi name=divisi  style=width:150px>".$optdivisi."</select></td>
			
			
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td><div id=isiPeriode1><select id=periode name=periode  style=width:150px>".$optperiode."</select></div>
			    <div id=isiPeriode2 style=display:none;><input type=text class=myinputtext id=tglSmp onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></div></td>
		</tr>
		


	 </table>
	 <input type=hidden id=method value='preview'>
	 <button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
	 <button class=mybutton onclick=excel(event,'kebun_slave_2plantation_daily.php')>".$_SESSION['lang']['excel']."</button>
	 <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset>";
	 
CLOSE_BOX();

OPEN_BOX();

echo"<fieldset style='clear:both;max-width:100%;'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
		<div id='awal'>
			<div id='container' style='overflow:auto;height:350px;max-width:100%;'>
			</div>
		</div>
    
		<div id='detailData' style='display:none'>
			<div id='isiData'>
			</div>
		</div>		
	</fieldset>";
	 
CLOSE_BOX();
echo close_body();
?>