<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include_once('lib/rTable.php');
	echo open_body();
	include('master_mainMenu.php');	
	require_once('lib/zSelect2.php');	
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/lgl_2bapp.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
	OPEN_BOX('','<span class=judul><b>'.getMenu('lgl_2bapp').'</b><br><br></span>');

	$optpt	="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optunit=$optsupp="<option value='%%'>".$_SESSION['lang']['all']."</option>";

	$str = "SELECT kodeorganisasi,namaorganisasi FROM $dbname.organisasi WHERE tipe = 'PT' AND kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."' ORDER BY kodeorganisasi";
	$rst = fetchData($str);
	foreach ($rst as $bar) {
		$optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
	}

	$str="SELECT DISTINCT koderekanan FROM $dbname.lgl_pengajuanspkht";
	$res=fetchData($str);
	foreach ($res as $bar) {
		$optsupp.="<option value='".$bar['koderekanan']."'>".getNamaSupplier($bar['koderekanan'])."</option>";
	}
	
	echo"<fieldset style=float:left><legend><b>Filter </b></legend>
	<table>
		<tr>	
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select id=pt class=select2 style=\"width:180px;\" onchange=\"getunit(this);\">".$optpt."</select></td>

			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td><select id=unit class=select2 style=\"width:180px;\" >".$optunit."</select></td>

			<td hidden>".$_SESSION['lang']['kontraktor']."</td>
			<td hidden>:</td>
			<td hidden><select id=kontraktor class=select2 style=\"width:180px;\" >".$optsupp."</select></td>

			<td hidden>".$_SESSION['lang']['notransaksi']."</td>
			<td hidden>:</td>
			<td hidden><input id=notransaksi onkeypress='enterkey(event,loaddata)' class=myinputtext onkeydown=\"upperCaseF(this)\" style=\"width:178px;\"></td>
				
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input style=\"width:75px;text-align:center\" type='text' placeholder='Klik Tanggal' class='myinputtext' id='tgl1' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10'/>
				s/d  <input style=\"width:75px;text-align:center\" type='text' placeholder='Klik Tanggal' class='myinputtext' id='tgl2' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10'/>
			</td>

			<td colspan=2 align=left></td>
			<td  align=left colspan=20>
			<button onclick=preview('html') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
			<button onclick=preview('excel') class=mybutton name=preview id=excel>".$_SESSION['lang']['excel']."</button>
			<button onclick=preview('pdf') class=mybutton name=preview id=pdf>".$_SESSION['lang']['pdf']."</button>
			</td>
			<td colspan=2></td>
			<td align=center>
				<button onclick=batal() class=mybutton name=btnBatal id=btnBatal style='width:100%'>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
	</fieldset>";
	CLOSE_BOX();

	OPEN_BOX('','<span class=judul><b>List</b><br><br></span>');
	echo"<div id='printContainer' style='height:65vh;' class='table-scroll'></div>";
	CLOSE_BOX();
	echo close_body();
?>