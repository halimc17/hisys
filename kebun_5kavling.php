<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php'); 
?>
<script language=javascript1.2 src='js/kebun_5kavling.js?v=<?php echo time(); ?>'></script>
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
	$wh = " and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
}

$sql = "SELECT * FROM " . $dbname . ".organisasi where 1=1 ".$where ." and tipe='KEBUN' order by kodeorganisasi asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
		$i="selected";
	}else{
		$i="";
	}
    $optunit.="<option value=" . $bar['kodeorganisasi'] . " ".$i.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$sql = "SELECT a.*, b.namasupplier FROM ".$dbname.".kebun_5namakud a LEFT JOIN ".$dbname.".log_5supplier b ON a.kodesupplier = b.supplierid where a.kodeunit = '".$_SESSION['empl']['lokasitugas']."' order by a.afdeling asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optafdeling.="<option value=" . $bar['afdeling'] . ">" . $bar['afdeling'] . " - " . $bar['namasupplier'] . "</option>";
}

$arrsts=array('1'=>'Aktif','0'=>'Non Aktif');
foreach($arrsts as $key => $val){
	$i="";
	if($key=='1'){
		$i="selected";
	}
    @$optsts.="<option value=" . $key. " ".$i.">" . $val. "</option>";	
}

$optafd2="<option value=''></option>";
// $sorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='BLOK' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
// $qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
// $qorg->setFetchMode(PDO::FETCH_ASSOC);
// while($rorg=$qorg->fetch()){
// 	$select='';
// 	if($blok==$rorg['kodeorganisasi']){
// 		$select="selected";
//     }
//         $optafd2.="<option value='".$rorg['kodeorganisasi']."' ".$select.">".$rorg['namaorganisasi']."</option>";
// }


OPEN_BOX('','<span class=judul>'.getMenu('kebun_5kavling').'</span><br>');
echo"
	<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td><select id=unit style=\"width:150px;\" onchange=\"getafdeling('','','');\">" . $optunit . "</select></td>
				</tr>
				<tr>
					<td>Nama KUD Organisasi</td>
					<td>:</td>
					<td><select id=afdeling style=\"width:150px;\" onchange=\"getblok('');\">" . $optafdeling . "</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['blok']."</td>
					<td>:</td>
					<td><select id=blok style=\"width:150px;\">" . $optafd2 . "</select><img id=blok onclick=z.elSearch('blok',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
				</tr>
				<tr>
					<td>Hamparan</td>
					<td>:</td>
					<td><input id=hamparan onkeydown=\"upperCaseF(this)\" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>Kavling</td>
					<td>:</td>
					<td><input id=kavling onkeydown=\"upperCaseF(this)\" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tahuntanam']."</td>
					<td>:</td>
					<td><input id=tahuntanam onkeydown=\"upperCaseF(this)\" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['nama']."</td>
					<td>:</td>
					<td><input id=nama onkeydown=\"upperCaseF(this)\" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td><select id=status style=\"width:150px;\">" . $optsts . "</select></td>				
					<td align=center>
						<input id=method value='insert' type=hidden>
						<input id=id type=hidden>
						<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
						</td>
				</tr>
			</table>
		</fieldset>";
echo"
	<fieldset style=float:left>
    <legend>".$_SESSION['lang']['catatan']."</legend>
			<table>
				<tr>
					<td>1.</td>
					<td>Saat pertama kali menginput, Status dalam posisi Non Aktif</td>
				</tr>
				<tr>
					<td>2.</td>
					<td>Status akan menjadi aktif setelah proses persetujuan</td>
				</tr>
				<tr>
					<td>3.</td>
					<td>Bila ada perubahan, saat menginput data di SPB, data yang disimpan adalah data lama.</td>
				</tr>
				<tr>
					<td></td>
					<td>Untuk menginput menggunakan data baru, silakan menunggu proses persetujuan selesai, baru menginput data di SPB.</td>
				</tr>
			</table>
		</fieldset>";		
CLOSE_BOX();
?>
<?php
OPEN_BOX();
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 width=100% style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['find']."</legend>
							".$_SESSION['lang']['unit']." : <select id=find_unit style=\"width:150px;\">" . $optunit . "</select>&nbsp;
							".$_SESSION['lang']['nama']." : 
							<input id=find_nama class=myinputtext id='id' onkeypress='enterkey(event,find)'>
							
							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
							
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>