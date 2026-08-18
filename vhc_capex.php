<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script   language=javascript1.2 src='js/vhc_capex.js'></script>
<?
include('master_mainMenu.php');

$_SESSION['bgimage'] = array();

$jenisApp = "CB";

$optSub="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by induk, tipe, namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
    $optunit.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

if($_SESSION['language']=='EN')
{
    $dd='namatipe1 as namatipe';
}
else
{
	$dd='namatipe';
}

$str="select kodetipe, ".$dd." from ".$dbname.".sdm_5tipeasset where kodetipe in ('BG','IS') order by kodetipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$subtipe="";
while($bar=$res->fetch())
{
	if($no==0){
		$subtipe = $bar->kodetipe;
	}
    $optaset.="<option value='".$bar->kodetipe."'>".$bar->kodetipe." - ".$bar->namatipe."</option>";
	$no++;
}

//List SubAsset
$str="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$subtipe."' order by namasub";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optSub.="<option value='".$bar->kodesub."'>".$bar->namasub."</option>";
}

$kamusjenis['AK']='Aktiva Dalam Konstruksi / Activa Under Construction';
$kamusjenis['PB']='Pabrikasi';

$optjenis="";
$arrjenis=getEnum($dbname,'project','tipe');
foreach($arrjenis as $kei=>$fal)
{
	if($fal=='PB')
    {
		#Pabrikasi  belum aktif  karena akunnya belum ada, pastikan akunnya sudah ada dan didaftar  pada parameter jurnal dengan kode
		#PAB       
    } 
    else
	{
		$optjenis.="<option value='".$kei."'>".$fal." ".$kamusjenis[$fal]."</option>";
    }
}

$optKel="";
$arrKel=getEnum($dbname,'spl_capexbangunan','kelompok');
foreach($arrKel as $kel)
{
	$optKel.="<option value='".$kel."'>".$kel."</option>";
} 

$optSatuan = makeOption($dbname,'setup_satuan','satuan,satuan');

//jenis biaya
$optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

## List Status
$arrStatus = getEnum($dbname,'spl_capexbangunan','statusbg');
foreach($arrStatus as $key)
{
	$optStatus.="<option value='".$key."'>".$key."</option>";
}

## List Tipe Bg
$arrStatus = getEnum($dbname,'spl_capexbangunan','tipebg');
foreach($arrStatus as $key)
{
	$optTipeBg.="<option value='".$key."'>".$key."</option>";
}

## List Pekerjaan
$arrStatus = getEnum($dbname,'spl_capexbangunan','pekerjaan');
foreach($arrStatus as $key)
{
	$optPekerjaan.="<option value='".$key."'>".$key."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('vhc_capex').'</span><br>');

echo"<fieldset style='float:left;'>
	<legend>Form</legend>
    <table cellspacing=1 border=0>
		<tr>
			<td align=left>".$_SESSION['lang']['aset']."</td>
			<td>:</td>
			<td>
				<select id=aset style='width:200px;' onchange='getsubtipeasset()'>".$optaset."</select>
			</td>
			
			<td align=left style='padding-left:15px'>".$_SESSION['lang']['pekerjaan']."</td>
			<td>:</td>
			<td>
				<select id=pekerjaan style='width:200px;' onchange=\"getjbiaya('','','ht')\">".$optPekerjaan."</select>
			</td>
			
			<td align=left style='padding-left:15px;vertical-align:top' rowspan='7'>
				<fieldset style='float:left;'>
				<legend>".$_SESSION['lang']['persetujuan']."</legend>
				<table cellspacing=1 border=0 id='tablepersetujuan'>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td align=left>".$_SESSION['lang']['unitkerja']."</td>
			<td>:</td>
			<td>
				<select id=unit style='width:200px;' onchange=\"getjbiaya('','','ht')\">".$optunit."</select>
			</td>
			
			<td align=left style='padding-left:15px;display:none'>".$_SESSION['lang']['status']."</td>
			<td style='display:none'>:</td>
			<td style='display:none'>
				<select id=statusbg style='width:200px;'>".$optStatus."</select>
			</td>
			<td align=left style='padding-left:15px'>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select id=tipebg style='width:200px;'>".$optTipeBg."</select>
			</td>
		</tr>
		<tr>
			<td align=left>Sub Asset</td>
			<td>:</td>
			<td>
				<select id=sub style='width:200px;'>".$optSub."</select>
			</td>
			
			<td align=left style='padding-left:15px;vertical-align:top' rowspan='4' colspan=3>
				<fieldset style='float:left;'>
				<legend>Upload File</legend>
				<table class=sortable cellspacing=1 border=0>
					<thead> 
					<tr>
						<td align=center>".$_SESSION['lang']['nourut']."</td>
						<td align=center>".$_SESSION['lang']['namafile']."</td>
						<td align=center>".$_SESSION['lang']['action']."</td>
					</tr>
					</thead>
					<tbody id=containerupload></tbody>
					<tbody>
					<tr>
						<td colspan=2>
							<input type='file' name='upload' id='upload' class=mybutton>
						</td>
						<td style='text-align:center'>
							<img src=images/plus.png class=resicon id='addfile'  title='Add File ' onclick=\"addfile();\">
						</td>
					</tr>
					</tbody>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td align=left>".$_SESSION['lang']['jenis']."</td>
			<td>:</td>
			<td>
				<select id=jenis style='width:200px;'>".$optjenis."</select>
			</td>
		</tr>
		<tr>
			<td align=left>".$_SESSION['lang']['jenisbiaya']."</td>
			<td>:</td>
			<td>
				<select style=width:200px id=jenisbiaya>".$optjb."</select>
			</td>
		</tr>
		<tr>
			<td align=left>".$_SESSION['lang']['nama']."</td>
			<td>:</td>
			<td>
				<input type=text id=nama onkeydown=\"upperCaseF(this)\" class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:195px;'>
			</td>
		</tr>
		<tr>
			<td align=left>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input style='width:85px;' id=tanggalmulai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y').">
				s/d <input style='width:80px;' id=tanggalselesai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y').">
			</td>
		</tr>
		<tr>
			<td colspan=7 style='text-align:center'>
				<input type=hidden value=insert id=method>
				<input type=hidden value='' id=kode>
				<button class=mybutton id='btnsimpan' onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>	 
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();


OPEN_BOX();
echo"<fieldset>
	<legend>".$_SESSION['lang']['find']."</legend>
	<table border=0>
		<tr>
			<td>".$_SESSION['lang']['nama']."</td>
			<td>:</td>
			<td>
				<input type=text id='namacr' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' />
			</td>
			<td>".$_SESSION['lang']['unit'] . "</td>
			<td>:</td>
			<td>
				<input type=text class='myinputtext' id='unitcr'  size='12' maxlength='10' />
			</td>
			<td>".$_SESSION['lang']['kode']."</td>
			<td>:</td>
			<td>
				<input type=text id='kodecr' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' />
			</td>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

echo"<div id=dataDisimpan>
<fieldset>
	<legend>".$_SESSION['lang']['datatersimpan']."</legend>
	<div style='height:350px;overflow:auto;'>
    <table class=sortable border=0 cellspacing=1 width=100%>
		<thead> 
		<tr>
			<td align=center>".$_SESSION['lang']['kode']."</td>
			<td align=center>".$_SESSION['lang']['unit']."</td>
			<td align=center>Sub Asset</td>
			<td align=center>".$_SESSION['lang']['jenis']."</td>
			<td align=center>".$_SESSION['lang']['nama']."</td>
			<td align=center style='min-width:80px'>".$_SESSION['lang']['tanggalmulai']."</td>
			<td align=center style='min-width:80px'>".$_SESSION['lang']['tanggalsampai']."</td>
			<td align=center>File Pendukung</td>
			<td align=center>Pemenang Tender</td>
			<td align=center>".$_SESSION['lang']['updateby']."</td>";
			$countApp = getCountApproval($jenisApp,'');
			for($i=1;$i<=$countApp;$i++){
				echo"<td align=center>".$_SESSION['lang']['persetujuan']. "".$i."</td>";
			}
			echo"<td align=center width=70px>".$_SESSION['lang']['action']."</td>
			<td align=center width=55px>".$_SESSION['lang']['print']."</td>
		</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
		</tbody>
		<tfoot id='footData'>
		</tfoot>
	</table>
	</div>
</fieldset></div>";

$frmdt="<div id=detailInput style=display:none>
<fieldset>
	<legend>".$_SESSION['lang']['detail']."</legend>
	<table cellpadding=1 cellspacing=1 border=0>
		<thead>
		<tr>
			<td align=center>".$_SESSION['lang']['kode']."</td>
			<td align=center>".$_SESSION['lang']['deskripsi']."</td>
			<td align=center>".$_SESSION['lang']['namakegiatan']."</td>
			<td align=center>".$_SESSION['lang']['satuan']."</td>
			<td align=center>".$_SESSION['lang']['volume']."</td>
			<td align=center>".$_SESSION['lang']['hargasatuan']."</td>
			<td align=center>HK</td>
			<td align=center>Rp HK</td>
			<td align=center>".$_SESSION['lang']['bobot']." %</td>
			<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>
			<td align=center>".$_SESSION['lang']['tanggalsampai']."</td>
			<td align=center>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody>
		<tr class=rowcontent>
			<td>
				<input type=text id=kdProj class=myinputtext maxlength=20 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;' disabled>
			</td>
			<td>
				<input type=select id='deskripsiKeg' class=myinputtext oninput=cekdeskripsi(this.value,event) list=listdeskripsi style='width:250px;'></input>
				<datalist id=listdeskripsi></datalist> 
			</td>
			<td>
				<input type=text id=namaKeg class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:250px;'>
			</td>
			<td>".makeElement('satKeg','select',"",array(),$optSatuan)."</td>
			<td>
				<input type=text id=volKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\">
			</td>
			<td>
				<input type=text id=hargaKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:70px;\">
			</td>
			<td>
				<input type=text id=hkKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\">
			</td>
			<td>
				<input type=text id=rupiahhkKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:70px;\">
			</td>
			<td>
				<input type=text id=bobotKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\">
			</td>
			<td>
				<input style='width:80px;' id=tanggalMulai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y').">
			</td>
			<td>
				<input style='width:80px;' id=tanggalSampai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y').">
			</td>
			<td style='width:40px;' align=center>
				<img src='images/save.png' class='zImgBtn' style='cursor:pointer;' onclick=addDetail() />
			</td>
		</tr>
		</tbody>
	</table>
	<button class=mybutton onclick=doneSlsi()>".$_SESSION['lang']['selesai']."</button>
</fieldset>
<input type=hidden id=kegId />
<div>

<fieldset><legend>".$_SESSION['lang']['list']."</legend>
	<table cellpadding=1 cellspacing=1 border=0 class=sortable>
		<thead>
		<tr>
			<td align=center>".$_SESSION['lang']['kode']."</td>
			<td align=center>".$_SESSION['lang']['deskripsi']."</td>
			<td align=center>".$_SESSION['lang']['namakegiatan']."</td>
			<td align=center>".$_SESSION['lang']['satuan']."</td>
			<td align=center>".$_SESSION['lang']['volume']."</td>
			<td align=center>".$_SESSION['lang']['hargasatuan']."</td>
			<td align=center>HK</td>
			<td align=center>Rp HK</td>
			<td align=center>".$_SESSION['lang']['bobot']." %</td>
			<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>
			<td align=center>".$_SESSION['lang']['tanggalsampai']."</td>
			<td align=center>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id=printDat>
		</tbody>
	</table>
</fieldset>
</div>";

echo $frmdt;
echo"</div>";
CLOSE_BOX();
echo close_body();
?>