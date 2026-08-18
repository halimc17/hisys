<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/kebun_2historypemupukan.js'></script>

<?php
include('master_mainMenu.php');


#divisi (kebun)
$optkebun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sKebun = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi  where tipe='KEBUN'";
$qKebun=$owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
$qKebun->setFetchMode(PDO::FETCH_ASSOC);
while ($rKebun = $qKebun->fetch()) {
    $optkebun.="<option value='" . $rKebun['kodeorganisasi'] . "'>" . $rKebun['namaorganisasi'] . "</option>";
}
$optblok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$opttahuntanam = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

//Form Pencarian
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Fertilization History').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('History Pemupukan').'</span>');
}


echo "<fieldset style='width:450px;'><legend><b>Form</b></legend>
<table>
	<tr>
		<td>" . $_SESSION['lang']['kebun'] . "</td>
		<td>:</td>
		<td><select id=kebun onchange=getBlok() style='width:200px;'>".$optkebun."</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['blok'] . "</td>
		<td>:</td>
		<td><select id=blok onchange=getTt() style='width:200px;'>".$optblok."</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['tahuntanam'] . "</td>
		<td>:</td>
		<td><select id=tahuntanam onchange=clearListData() style='width:200px;' disabled='true'>".$opttahuntanam."</select></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo "<fieldset>
	<legend><b>" . $_SESSION['lang']['list'] . "</b></legend>";
	
	echo"<table width=100%>
		<tr>
			<td width=50% style='vertical-align:top'>
				<div style='float:left;width:100%;'>
					<fieldset>
						<legend><b>". $_SESSION['lang']['pemupukan']."(".$_SESSION['lang']['aktual'].")</b></legend>
						<div id='table1' style='overflow:auto; max-height:550px;'></div>
						<p>
						<div id='graph1' style='overflow:auto; max-height:550px; max-width:580px;'></div>
					</fieldset>
				</div>
			</td>
			<td width=50% style='vertical-align:top'>
				<div style='float:right;width:100%;'>
					<fieldset>
						<legend><b>". $_SESSION['lang']['pemupukan']."(".$_SESSION['lang']['rekomendasi'].")</b></legend>
							<div id='table2' style='overflow:auto; max-height:550px;'></div>
							<p>
							<div id='graph2' style='overflow:auto; max-height:550px; max-width:580px;'></div>
					</fieldset>	
				</div>
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top'>
				<div style='float:left;width:100%;overflow:auto;'>
					<fieldset>
						<legend><b>". $_SESSION['lang']['produksi']."(".$_SESSION['lang']['aktual'].")</b></legend>
							<div id='table3' style='overflow:auto; max-height:550px;'></div>
							<p>
							<div id='graph3' style='overflow:auto; max-height:550px; max-width:580px;'></div>
					</fieldset>
				</div>
			</td>
			<td style='vertical-align:top'>
				<div style='float:right;width:100%;'>
					<fieldset>
						<legend><b>". $_SESSION['lang']['produksi']."(".$_SESSION['lang']['sensus'].")</b></legend>
							<div id='table4' style='overflow:auto; max-height:550px;'></div>
							<p>
							<div id='graph4' style='overflow:auto; max-height:550px; max-width:580px;'></div>
					</fieldset>	
				</div>
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top'>
				<div style='float:left;width:100%;'>
					<fieldset>
						<legend><b>".$_SESSION['lang']['produksi']."(".$_SESSION['lang']['budget'].")</b></legend>
							<div id='table5' style='overflow:auto; max-height:550px;'></div>
							<p>
							<div id='graph5' style='overflow:auto; max-height:550px; max-width:580px;'></div>
					</fieldset>
				</div>
			</td>
			<td style='vertical-align:top'>
				<div style='float:right;width:100%;'>
					<fieldset>
						<legend><b>". $_SESSION['lang']['curahHujan']."</b></legend>
							<div id='table6' style='overflow:auto; max-height:550px;'></div>
							<p>
							<div id='graph6' style='overflow:auto; max-height:550px; max-width:580px;'></div>
					</fieldset>	
				</div>
			</td>
		</tr>
	</table>";
	
	
	// /*==================
	// =-AKTUAL PEMUPUKAN-=
	// ==================*/
	// echo"<div style='float:left;width:49%;'>
			// <fieldset>
				// <legend><b>Aktual Pemupukan</b></legend>
				// <div id='table1' style='overflow:auto; max-height:550px;'></div>
				// <p>
				// <div id='graph1' style='overflow:auto; max-height:550px; max-width:580px;'></div>
			// </fieldset>
		// </div>";
	
	// /*===================
	// =-REKOMENDASI PUPUK-=
	// ===================*/
	// echo"<div style='float:right;width:49%;'>
		// <fieldset>
			// <legend><b>Rekomendasi Pupuk</b></legend>
				// <div id='table2' style='overflow:auto; max-height:550px;'></div>
				// <p>
				// <div id='graph2' style='overflow:auto; max-height:550px; max-width:580px;'></div>
		// </fieldset>	
	// </div>";
		
	// echo"<div style='clear:both;'></div>";
	
	// /*=================
	// =-AKTUAL PRODUKSI-=
	// =================*/
	// echo"<div style='float:left;width:49%;overflow:auto;'>
		// <fieldset>
			// <legend><b>Aktual Produksi</b></legend>
				// <div id='table3' style='overflow:auto; max-height:550px;'></div>
				// <p>
				// <div id='graph3' style='overflow:auto; max-height:550px; max-width:580px;'></div>
		// </fieldset>
	// </div>";
	
	// /*=================
	// =-SENSUS PRODUKSI-=
	// =================*/
	// echo"<div style='float:right;width:49%;'>
		// <fieldset>
			// <legend><b>Sensus Produksi</b></legend>
				// <div id='table4' style='overflow:auto; max-height:550px;'></div>
				// <p>
				// <div id='graph4' style='overflow:auto; max-height:550px; max-width:580px;'></div>
		// </fieldset>	
		// </div>";
		
	// echo"<div style='clear:both;'></div>";
	
	// /*=================
	// =-BUDGET PRODUKSI-=
	// =================*/
	// echo"<div style='float:left;width:49%;overflow:auto;'>
		// <fieldset>
			// <legend><b>Budget Produksi</b></legend>
				// <div id='table5' style='overflow:auto; max-height:550px;'></div>
				// <p>
				// <div id='graph5' style='overflow:auto; max-height:550px; max-width:580px;'></div>
		// </fieldset>
		// </div>";
	
	// /*=============
	// =-CURAH HUJAN-=
	// =============*/
	// echo"<div style='float:right;width:49%;'>
		// <fieldset>
			// <legend><b>Curah Hujan</b></legend>
				// <div id='table6' style='overflow:auto; max-height:550px;'></div>
				// <p>
				// <div id='graph6' style='overflow:auto; max-height:550px; max-width:580px;'></div>
		// </fieldset>	
		// </div>";
	
	// echo"</fieldset>";
echo"</fieldset>";
CLOSE_BOX();
echo close_body();
?>