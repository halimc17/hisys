<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript1.2 src='js/kebun_5premibmtbs.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>
<?php
$_SESSION['fee']=array();
/* $optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
if(count($adm)>0 or $_SESSION['empl']['tipelokasitugas']=='HOLDING'){
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
    #$optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}
 */

$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optunit.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}	
}

// echo "<pre>";
// print_r($_SESSION['orgdet']);
// echo "</pre>";

$optdivisi = "<option value=''></option>";
$sql = "SELECT * FROM " . $dbname . ".organisasi where 1=1 ".$where ." and tipe='AFDELING' order by kodeorganisasi asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optdivisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}


$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi ='BM' and kodeparameter ='BMTBS'";
$bmkeg=fetchdata($str);

$optkegiatan = "<option value=''></option>";
$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan where kodekegiatan in (".$bmkeg[0]['nilai'].") and status='1' order by kodekegiatan asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$d=$bar['noakun'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
		$optkegiatan.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	
    $optkegiatan.="<option value=" . $bar['kodekegiatan'] . ">" . $bar['kodekegiatan'] . " - " . strtoupper($bar['namakegiatan']) . "</option>";
	$n=$d;
	if($d!=$n){			
		$optkegiatan.="</optgroup>";
	}
}

$optjenispremi="<option value='KERJA'>KERJA</option>";
$optjenispremi.="<option value='LIBUR'>LIBUR</option>";
$optjenispremi.="<option value='LIBUR NASIONAL'>LIBUR NASIONAL</option>";


OPEN_BOX('','<span class=judul>'.getMenu('kebun_5hargaangkut').'</span>');
echo"<div><fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
		<table style=font-weight:bold>
			<tr>
				<td>".$_SESSION['lang']['kodeorganisasi']."</td>
				<td>:</td>
				<td><select id=unit onchange=gettahuntanam(this.id) style=\"width:150px;\">" . $optunit . "</select></td>
			
			
				<td>".$_SESSION['lang']['divisi']."</td>
				<td>:</td>
				<td><select id=divisi  style=\"width:150px;\">" . $optdivisi . "</select></td>
				
			</tr><tr>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>:</td>
				<td><select id=kegiatan  style=\"width:150px;\">" . $optkegiatan . "</select></td>
				
			
				<td>Angkut (Rp/Kg)</td>
				<td>:</td>
				<td><input type=number min='1' class=myinputtextnumber style='width:145px' id=rpangkut onkeypress='return angka_doang(event)'></td>
				
			</tr><tr hidden>
				<td>".$_SESSION['lang']['denda']."</td>
				<td>:</td>
				<td><input type=number min='1' class=myinputtextnumber style='width:145px' id=denda onkeypress='return angka_doang(event)'></td>
			
				<td>".$_SESSION['lang']['toleransi']." (%)</td>
				<td>:</td>
				<td><input type=number min='1' class=myinputtextnumber style='width:145px' id=toleransi onkeypress='return angka_doang(event)'></td>
			</tr><tr>
				<td>".$_SESSION['lang']['tanggalberlaku']."</td>
				<td>:</td>
				<td><input type='text' readonly=readonly class='myinputtext' id='tglberlaku' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:145px;' /></td>

				<td>".$_SESSION['lang']['jenis']." Hari</td>
				<td>:</td>
				<td><select id=jenispremi  style=\"width:150px;\">" . $optjenispremi . "</select></td>

			</tr><tr>
				<td colspan=2><input hidden id=method value=insert></td>
				<td><button class=mybutton onclick=savedetail()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=bataldetail()>".$_SESSION['lang']['cancel']."</button>
					</td>
			</tr>
		</table>
	
</fieldset></div>";
CLOSE_BOX();
?>
<?php
OPEN_BOX();
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset style='float: left;'>
							<legend>".$_SESSION['lang']['find']."</legend>
							".$_SESSION['lang']['unit']." / ".$_SESSION['lang']['divisi']." : 
							<input type=text class=myinputtext style='width:145px' id=find_divisi onkeypress='enterkey(event,loaddata)'>
							
							&nbsp;
							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
							
						</fieldset>
					</td> 
				</tr>
			</table>
		<table border=0 cellpadding=1 class=sortable cellspacing=1>
				<thead>
					<tr class=rowheader style=font-weight:bold>
						<td align=center rowspan=2>No</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['kodeorganisasi']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['divisi']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['kegiatan']."</td> 
						<td align=center rowspan=2 width=50px>Angkut (Rp/Kg)</td> 
						<td align=center rowspan=2 width=50px hidden>".$_SESSION['lang']['denda']."</td> 
						<td align=center rowspan=2 width=50px  hidden>".$_SESSION['lang']['toleransi']." (%)</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['tanggalberlaku']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['jenis']." Hari</td> 
						<td align=center rowspan=2 colspan=2>".$_SESSION['lang']['action']."</td> 
					</tr>
				</thead>
			<tbody id=container>
				<script>loaddata(0)</script>
			</tbody>
        </table>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>