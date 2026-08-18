<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zSearch.js'></script>
<script language=javascript1.2 src='js/umm_uploadpeta.js'></script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('umm_uploadpeta').'</span><br>');
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

echo"<fieldset style='float:left'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
	<table border=0>
		<tr hidden>
			<td>".$_SESSION['lang']['id']."</td>
			<td>:</td>
			<td colspan=4>
				<input id=id class=myinputtext style=width:220px  disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td  colspan=4 style='padding-right:10px'>
				<select id='kodept3' style=width:225px onchange='getkebun3()'>".$optPT."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td colspan=4 style='padding-right:10px'>
				<select id=kebun3 style=width:225px >".$optKebun."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipepeta']."</td>
			<td>:</td>
			<td  colspan=4 style='padding-right:10px'>
				<select id=tipepeta style='width:225px;'>".$optTipePeta."</select>
			</td>
			<td><img id=tipepeta_find onclick=\"z.elSearch('tipepeta',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
		</tr>
		<tr>
			<td>Nama Peta</td>
			<td>:</td>
			<td colspan=4 style='padding-right:10px'>
				<input id=namapeta style='width:220px' class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" >
			</td>
		</tr>
		<tr>
			<td>Revisi Ke</td>
			<td>:</td>
			<td style=width:50px style='padding-right:10px'>
				<input type=text id=revisi style='width:50px' value='0' class=myinputtextnumber onblur=\"z.numberFormat('revisi')\" nkeypress='return_tanpa_kutip(event); onkeypress='return angka_doang(event)'/>
			</td>
			
			<td>Status :</td>
			<td style='padding-right:10px'>
				<select id='status' style=width:75px>".$optsts."</select>
			</td>
			
		</tr>
		<tr>
			<td>File</td>
			<td>:</td>
			<td colspan=4><input name=upload type=file id=upload size=25 class=mybutton></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=4>
				<input type=hidden id=method value='insert'>
				<button id=tblsimpan class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id='frm1'><fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<fieldset style=float:left>
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
	<div style=clear:both> <hr></div>
	<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
		<tr align=center>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>".$_SESSION['lang']['tipepeta']."</td>
			<td>Nama Peta</td>
			<td>".$_SESSION['lang']['revisi']."</td>
			<td>Nama File</td>
			<td>Ukuran<br>MB</td>
			<td>".$_SESSION['lang']['status']."</td>
			<td>".$_SESSION['lang']['upload']." By</td>
			<td>Upload Time</td>
			<td colspan=3>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id='container'>
			<script>loaddata(0)</script>
		</tbody>
		<tfoot id=footData> 
		</tfoot>
	</table>
</fieldset></div>";
CLOSE_BOX();
echo close_body();
?>