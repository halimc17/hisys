<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>

<script>
	function lihatDetail(unit,periode,ev)
	{
	   param='proses=detail&detailunit='+unit+'&detailperiode='+periode;
	   tujuan='kebun_slave_3LaporanBiayaPerBlok.php'+"?"+param;  
	   width='900';
	   height='400';

	   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	   showDialog1('Detail Jurnal Blok '+unit,content,width,height,ev); 
	}
</script>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
//inisialisasi periode
//$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sTgl="select distinct substr(periode,1,7) as periode from ".$dbname.".keu_jurnaldt_vw order by periode desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
$optPeriode="";
while($rTgl=$qTgl->fetch())
{
   $optPeriode.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $add="";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $add = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
}else
{
    $add="and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
//inisialisasi unit
//$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$sUnit="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' ".$add." order by namaorganisasi asc";
$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
$qUnit->setFetchMode(PDO::FETCH_ASSOC);
$optUnit="";
while($rUnit=$qUnit->fetch())
{
    $optUnit.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['kodeorganisasi']." - ".$rUnit['namaorganisasi']."</option>";
}


$arrOptIP = getEnum($dbname,'setup_blok','intiplasma');
$optIP = '';
$optIP .= "<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOptIP as $val){
	if($val=="I"){
		$optIP .= "<option value='".$val."'>Inti</option>";
	}else{
		$optIP .= "<option value='".$val."'>Plasma</option>";
	}
}



$arr="##periode##unit##intiplasma";
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['biaya']." / ".$_SESSION['lang']['blok']).'</span>');
?>
<div>
	<fieldset style="float: left;">
		<legend><b>Form</b></legend>
		<table cellspacing="1" border="0">
			<tr>
				<td><?echo $_SESSION['lang']['unit']?></td><td>:</td>
				<td>
					<select id="unit" name="unit" style="width:150px"><? echo $optUnit ?></select>
				</td>
			</tr>
			<tr>
				<td><?echo $_SESSION['lang']['periode']?></td><td>:</td>
				<td>
					<select id="periode" name="periode" style="width:150px;"><? echo $optPeriode ?></select>
				</td>
			</tr>
			
			<?php
				echo"<tr>
					<td>".$_SESSION['lang']['intiplasma']."</td><td>:</td>
					
					<td><select style=width:150px; id=intiplasma>".$optIP."</select></td>
				</tr>";
				?>
			<tr>
				<td colspan="2">
				<td colspan="2">
					<button onclick="zPreview('kebun_slave_3LaporanBiayaPerBlok','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><? echo $_SESSION['lang']['preview']; ?></button>
					<button onclick="zPdf('kebun_slave_3LaporanBiayaPerBlok','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><? echo $_SESSION['lang']['pdf']; ?></button>
					<button onclick="zExcel(event,'kebun_slave_3LaporanBiayaPerBlok.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview"><? echo $_SESSION['lang']['excel']; ?></button>
				</td>
				</td>
			</tr>
		</table>
	</fieldset>
	</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>	
		<div class='table-scroll'><div id='printContainer' style='height:350px;'></div>
		</div>
</div>

<?php
CLOSE_BOX();
echo close_body();
?>