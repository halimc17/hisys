<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript1.2 src='js/kebun_5hargafee.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>
<?php
$_SESSION['fee']=array();
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
if(count($adm)>0 or $_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$where="";
	$wh="";
}else{
	$where = " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
	//$wh = " and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
}

$sql = "SELECT * FROM " . $dbname . ".organisasi where 1=1 ".$where ." and tipe='KEBUN' order by kodeorganisasi asc";
$qry = fetchdata($sql);
foreach($qry as $bar){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$induk[$bar['kodeorganisasi']];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

$optdivisi = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT * FROM " . $dbname . ".organisasi where 1=1 ".$where ." and tipe='AFDELING' order by kodeorganisasi asc";
$qry = fetchdata($sql);
foreach($qry as $bar){
    $optdivisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$optblok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT * FROM " . $dbname . ".setup_blok where 1=1 ".$wh." order by kodeorg asc";
$qry = fetchdata($sql);
foreach($qry as $bar){
    $optblok.="<option value=" . $bar['kodeorg'] . ">" . getNamaOrg($bar['kodeorg']) . "</option>";
}

$opttt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optttf = "<option value=''></option>";
$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok order by tahuntanam asc";
$qry = fetchdata($sql);
foreach($qry as $bar){
    $opttt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
    $optttf.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
}

$namaisi = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BMTBS' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
$bar=fetchData($str)[0];
$nama=explode(',',$bar['nilai']);

$optkeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($nama as $list => $isi){
	@$optkeg.="<option value=".$isi.">".$namaisi[$isi]."</option>";
}

$sql="";
$optjnskeg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

#$where="and (noakun like '611%' or noakun like '7%')";
$where="and (noakun like '7%')";

$sql = "SELECT * FROM " . $dbname . ".keu_5akun where length(noakun)=7 ".$where." order by noakun";
$qry = fetchdata($sql);
foreach($qry as $bar){
	$optjnskeg.="<option value=" . $bar['noakun'] . " ".$i.">" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan where noakun like '611%' order by noakun";
$qry = fetchdata($sql);
foreach($qry as $bar){
	$optjnskeg.="<option value=" . $bar['kodekegiatan'] . " ".$i.">" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
}


$optnamafee = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT * FROM " . $dbname . ".kebun_5namafee where status=1 order by nama asc";
$qry = fetchdata($sql);
foreach($qry as $bar){
	$optnamafee.="<option value=" . $bar['id'] . " ".$i.">" . $bar['nama'] . "</option>";
}

$sql = "SELECT distinct supplierid FROM " . $dbname . ".log_5supkelompok where status=1 AND TIPE='SUPPLIERTBSKUD'";
$qry = fetchdata($sql);
foreach($qry as $bar){
	$optnamafee.="<option value=" . $bar['supplierid'] . " ".$i.">" . getNamaSupplier($bar['supplierid']) . "</option>";
}


$optjenisfee = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>"; 
$optjenisfeex = "<option value=''>".$_SESSION['lang']['all']."</option>"; 
$arrjnsfee = getEnum($dbname,'kebun_5daftarfee','jenisfee');
foreach($arrjnsfee as $val){
	$optjenisfee.="<option value='".$val."'>".$val."</option>"; 			
	$optjenisfeex.="<option value='".$val."'>".$val."</option>"; 			
}

$optjns = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optjns.= "<option value='GLOBAL' selected>GLOBAL</option>";
$sql = "SELECT * FROM " . $dbname . ".vhc_5jenisvhc";
$qry = fetchdata($sql);
foreach($qry as $bar){
	if($bar['jenisvhc']==$jnskendht){
		$i="selected";
	}else{
		$i="";
	}
	$optjns.="<option value=" . $bar['jenisvhc'] . " ".$i.">" . $bar['jenisvhc'] . " - " . $bar['namajenisvhc'] . "</option>";
	$nmkend[$bar['jenisvhc']]=$bar['namajenisvhc'];
}

$nmkend['GLOBAL']='GLOBAL';
$optjnscr = "<option value=''>".$_SESSION['lang']['all']."</option>";
$sql = "SELECT distinct jenisvhc FROM " . $dbname . ".kebun_5daftarfee order by jenisvhc asc";
$qry = fetchdata($sql);
foreach($qry as $bar){
	$optjnscr.="<option value=" . $bar['jenisvhc'] . ">" . $bar['jenisvhc'] . " - ".$nmkend[$bar['jenisvhc']]."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('kebun_5hargafee').'</span>');
echo"<div><fieldset>
		<legend>".$_SESSION['lang']['form']."</legend>
			<table><td>
			<table style=font-weight:bold>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td><select id=unit onchange=gettahuntanam(this.id) style=\"width:150px;\">" . $optunit . "</select></td>
					
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select id=divisi onchange=gettahuntanam(this.id) style=\"width:150px;\">" . $optdivisi . "</select></td>
					
				</tr><tr>	
					<td hidden>TT</td>
					<td hidden>:</td>
					<td hidden><select id=tahuntanam onchange=gettahuntanam(this.id) style=\"width:150px;\">" . $opttt . "</select></td>
				
					<td>Jenis Kend</td>
					<td>:</td>
					<td><select id=jenisvhc style=\"width:150px;\">" . $optjns . "</select></td>
					
					<td>Blok</td>
					<td>:</td>
					<td><select id=blok  style=\"width:150px;\">" . $optblok . "</select>
						<img id='blok' onclick=z.elSearch('blok',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
						</td>
				</tr><tr>
					<td>".$_SESSION['lang']['nama']."</td>
					<td>:</td>
					<td><select id=namafee style=\"width:150px;\">" . $optnamafee . "</select>
						<img id='namafee' onclick=z.elSearch('namafee',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
						</td>
						
					<td>".$_SESSION['lang']['jenis']."</td>
					<td>:</td>
					<td><select id=jenisfee style=\"width:150px;\">" . $optjenisfee . "</select></td>
				</tr><tr>
					<td>".$_SESSION['lang']['akun']."</td>
					<td>:</td>
					<td><select id=jenis style=\"width:150px;\">" . @$optjnskeg . "</select>
						<img id='jenis' onclick=z.elSearch('jenis',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
						</td>
					<td>".$_SESSION['lang']['rupiah']."</td>
					<td>:</td>
					<td>
						<input style=width:145px type=text id=rpfee nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\">
					</td>
				</tr><tr>
					<td colspan=2><input id=method hidden value='insert'></td>
					<td colspan=5><button class=mybutton onclick=savedetail()>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=bataldetail()>".$_SESSION['lang']['cancel']."</button>
						</td>
				</tr>
			</table>
			</td>
			
			
			<td valign=top>			
				Info:<br>
				Khusus untuk jenis <b>tempunak</b> hanya berlaku untuk SDK3, dan nilai pada kolom <b>Rupiah</b> adalah <b>Persen</b>
			</td>
			</table>
	</fieldset><div>";
CLOSE_BOX();
?>
<?php
OPEN_BOX();
echo "
		<table border=0 cellpadding=5 cellspacing=1 style='display: inline-block;vertical-align:top'>
			<tr>
				<td>
					<fieldset>
						<legend>".$_SESSION['lang']['find']."</legend>
						".$_SESSION['lang']['nama']." : 
						<input style=width:145px type=text id=namacr nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext>
						 &nbsp;
						".$_SESSION['lang']['blok']." : 
						<input style=width:100px type=text id=blokcr nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext>
						 &nbsp;
						 
						Jenis Kend : 
						<select id=jeniskendcr  onchange=loaddata(0) style=\"width:100px;\">" . @$optjnscr . "</select>
						 &nbsp;
						 
						".$_SESSION['lang']['jenis']." : 
						<select id=jeniscr  onchange=loaddata(0) style=\"width:100px;\">" . @$optjenisfeex . "</select>
						 &nbsp;
						
						<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
						<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
						
					</fieldset>
				</td> 
			</tr>
		</table>
	
	<div id=container class='table-scroll' style=height:60vh> 
		<script>loaddata(0)</script>
	</div>
    ";
CLOSE_BOX();
echo close_body();                  
?>