<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zSearch.js'></script>
<script language=javascript1.2 src='js/umm_2uploadpeta.js'></script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('umm_2uploadpeta').'</span><br>');
//Get Tipe Peta
$optTipePeta = "";
$str = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '0' order by keterangan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optTipePeta.="<option value=''></option>";
while($bar = $res->fetch()){
	$optTipePeta.="<option value='".$bar['id_tipepeta']."'>".$bar['keterangan']."</option>";
}

//Get All PT
$optPT = $optDivisi = $optKebun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".organisasi where tipe = 'PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$optPT .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$optsts='';
$arrsts=array(''=>'','0'=>'Aktif','1'=>'Non Aktif');
foreach($arrsts as $key => $val){
	$optsts.= "<option value='".$key."'>".$val."</option>";
}

echo"<table border=0>
		<tr valign=middle>
			<td align=center style='width:100px;cursor:pointer;' onclick=loaddata(0);batalscr();>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
			<td>
			
		<fieldset style='float:left'>
		<legend>".$_SESSION['lang']['find']."</legend>
		<table>
			<tr>
			
			<td>".$_SESSION['lang']['pt']."</td><td>:</td>
			<td><select id='ptscr' onchange=getkebunscr(); style=width:150px>".$optPT."</select>&nbsp;</td>
			
			<td>".$_SESSION['lang']['unit']."</td><td>:</td>
			<td><select id='unitscr' onchange=loaddata(0); style=width:150px></select>&nbsp;</td>
			
			<td>".$_SESSION['lang']['tipepeta']."</td><td>:</td>
			<td><select id='tipepetascr' onchange=loaddata(0); style=width:100px>".$optTipePeta."</select>&nbsp;</td>
			
			<td>".$_SESSION['lang']['status']."</td><td>:</td>
			<td><select  id='statusscr' onchange=loaddata(0); style=width:100px>".$optsts."</select>&nbsp;</td>
			
		</tr>
		<tr>
			<td>Nama Peta</td><td>:</td>
			<td><input onkeypress='enterkey(event,loaddata)' id=namapetascr style='width:145px' class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" >&nbsp;</td>
			
			<td>Nama File</td><td>:</td>
			<td><input onkeypress='enterkey(event,loaddata)' id=namafilescr style='width:145px' class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" >&nbsp;</td>
			
			<td>".$_SESSION['lang']['revisi']."</td><td>:</td>
			<td><input onkeypress='enterkey(event,loaddata)' id=revisiscr style='width:95px' class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" >&nbsp;</td>
			
			<td>Tanggal</td><td>:</td>
			<td><input onkeypress='enterkey(event,loaddata)' id=tglscr placeholder='2019-01-01' style='width:95px' class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" >&nbsp;</td>
			
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>
				<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
				<button class=mybutton onclick=batalscr()>".$_SESSION['lang']['cancel']."</button>
			</td>
			
		</tr>
		</table>
		</fieldset>
	</table>";
CLOSE_BOX();
OPEN_BOX();
echo"
<div style=clear:both></div>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='containerx' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='containerx' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<div id='containerx' style='overflow:auto;';>
			<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
				<tr align=center>
					<th>".$_SESSION['lang']['nourut']."</th>
					<th>".$_SESSION['lang']['pt']."</th>
					<th>".$_SESSION['lang']['unit']."</th>
					<th>".$_SESSION['lang']['tipepeta']."</th>
					<th>Nama Peta</th>
					<th>".$_SESSION['lang']['revisi']."</th>
					<th>Nama File</th>
					<th>Ukuran<br>MB</th>
					<th>".$_SESSION['lang']['status']."</th>
					<th>".$_SESSION['lang']['upload']." By</th>
					<th>Upload Time</th>
					<td colspan=1>".$_SESSION['lang']['action']."</th>
				</tr>
				</thead>
				<tbody id='container'>
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData> 
				</tfoot>
			</table>
	
	
	</div>
</div>";
CLOSE_BOX();
echo close_body();
?>