<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
$blokStatus=array();
$_SESSION['tmp']['kebun']['tipeTrans'] = 'BKM';
#==== Status Blok Validation
if($_SESSION['org']['period']['start']==''){
	$val1="<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Silahkan buat periode akutansi untuk unit ".$_SESSION['empl']['lokasitugas']." terlebih dahulu</span>";
	exit($val1);
}
if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
	$val2="<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Lokasi tugas anda di : ".$_SESSION['empl']['tipelokasitugas'].", silahkan pindah ke KEBUN terlebih dahulu.</span>";
	exit($val2);
}
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src='js/kebun_bkm.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript1.2 src='js/option.js?v=<?php echo time(); ?>'></script>
<link rel="stylesheet" type="text/css" href="lib/MagnificPopup/magnific-popup.css">
<script type="text/javascript" src="lib/MagnificPopup/jquery.magnific-popup.js"></script>
<script>
	function popupimage() {
		alertify.closeAll();
		$('.popup-img').magnificPopup({
			type 		: 'image',
			removalDelay: 300,
			mainClass	: 'mfp-fade',
			mainClass: 'mfp-fade',
			gallery: {
				enabled: true
			},
			zoom: {
				enabled: true,
				duration: 300,
				easing: 'ease-in-out',
				opener: function (openerElement) {
					return openerElement.is('img') ? openerElement : openerElement.find('img');
				}
			},
		});
	}
</script>
<?php
#validasi jika buka tab baru dengan jenis yg beda
$statusawal=$_SESSION['tmp']['kebun']['tipeTrans'];

$where=$wh="";
$where= " and induk = '".$_SESSION['empl']['lokasitugas']."'";
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorg.="<option value=".$_SESSION['empl']['lokasitugas'].">".$_SESSION['empl']['lokasitugas']." - ".getNamaOrg($_SESSION['empl']['lokasitugas'])."</option>";

// Get Kode Jabatan
$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABKRN'"; 
$res = fetchdata($str);
@$arrjab = explode(',', $res[0]['nilai']);

# Divisi
$wh="";
//$wh=" and induk in (".getOrgDetail(2).")";
$wh=" and induk = '".$_SESSION['empl']['lokasitugas']."'";
$optdivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdiv = "<option value=''>&nbsp;</option>";

$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') ".$wh." order by induk, kodeorganisasi";
$res = fetchData($str);
foreach($res as $key => $val){
	$s="";
	if($_SESSION['empl']['subbagian']==$val['kodeorganisasi']){
		$s="selected";
	}
	$d=$val['induk'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optdivisi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optdivisi.="<option value=".$val['kodeorganisasi']." ".$s.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
	$n=$d;
	if($d!=$n){
		$optdivisi.="</optgroup>";
	}
}

$arrdivsch = getOrgDetail(27);
foreach ($arrdivsch as $key => $val) {
	$s="";
	if($_SESSION['empl']['subbagian']==$key){
		$s="selected";
	}
	$optdiv.="<option value=".$key." ".$s.">".$key." - ".getNamaOrg($val)."</option>";
}

# Posting
$arrPos=array("0"=>"Not Posted","1"=>"Posted");
$optPos="<option value=''>&nbsp;</option>";
foreach($arrPos as $key => $val){
	$optPos.="<option value=".$key.">".$val."</option>";
}

# Periode
for($x=-2;$x<25;$x++){
	$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
	if(date("Y-m",$dt)==date("Y-m")){
		$select="selected";
	}else{
		$select="";
	}
	
	$optprd.="<option value=".date("Y-m",$dt)." ".$select.">".date("m-Y",$dt)."</option>";
}


## Verifikasi
$optverifikasisch="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrtipeVer=array('1'=>'Sudah Di Verifikasi','2'=>'Belum Di Verifikasi');
foreach($arrtipeVer as $val => $value){
	$optverifikasisch.="<option value='".$val."'>".$value."</option>";

}


## HIDE BUAT BARU KEGIATAN RAWAT (PERMINTAAN PALMA SESUAI TIKET SUPPORT)
$str = "select nilai,kodeorg from ".$dbname.".setup_parameterappl where kodeparameter='HDBMKP' and kodeorg = '".getindukPT($_SESSION['empl']['lokasitugas'])."'"; 
$res = fetchdata($str);
$get_jabatan = explode(',', $res[0]['nilai']);

$hidden_tombol = 'hidden';
if($res[0]['kodeorg'] == getindukPT($_SESSION['empl']['lokasitugas'])){
	if (in_array($_SESSION['empl']['kodejabatan'],$get_jabatan)) {
		$hidden_tombol = '';
	}
}else{
	$hidden_tombol = '';
}


OPEN_BOX('','<span class=judul>'.getMenu('kebun_bkm').'</span>','judul_header');	
# === Header dan Pencarian data ===
echo"<div id=action_list><input style=display:none value=".$statusawal." id=stsawal>";
echo"<table>
     <tr valign=middle>
	 <td align=center ".$hidden_tombol." style='width:100px;cursor:pointer;' onclick=add_new_data('kebun')>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
			   <td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
			   <td><input type=text class=myinputtext id=notransaksisch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>

			   <td>No BKM</td><td>:</td>
			    <td><input type=text class=myinputtext id=nobkmsch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:125px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>
				
				
				<td>" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select class='select2' id=divsch onchange='loaddata()' style=\"width:130px;border-color:blue;\">".$optdiv."</select></td>
				
				<td>" . $_SESSION['lang']['mandor'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext id=mandorsrc nkeypress=\"return_tanpa_kutip(event);\" style=\"width:126px;\"  onkeypress='enterkey(event,loaddata)' /> </td>
				
				<td>" . $_SESSION['lang']['status'] . " Verifikasi</td> 
				<td>:</td>
				<td><select class='select2' id=verifikasisch onchange='loaddata()' style=\"width:130px;border-color:blue;\">".$optverifikasisch."</select> </td>
				
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['tanggalmulai'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:130px;' class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
				</td>
				
				<td>" . $_SESSION['lang']['tanggalselesai'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:125px;' class='myinputtext' id='tglselesai' onmousemove='setCalendar(this.id)' onkeypress='return false'; readonly/>
				</td>
				
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select class='select2' id=periodesch onchange='loaddata()' style=\"width:130px;border-color:blue;\">".$optprd."</select>
				</td>
				
				<td>" . $_SESSION['lang']['posting'] . "</td> 
				<td>:</td>
				<td><select class='select2' id=postingsrc onchange='loaddata()' style=\"width:130px;\">".$optPos."</select>
				</td>
			
			   
			</tr>";

echo"<tr><td><td><td colspan=9><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button>  
<button id=loaddataexcel class=mybutton onclick=loaddataexcel()>" . $_SESSION['lang']['excel'] . "</button>

</td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();

echo "
	<div class='table-scroll' style=height:60vh>
		<table class='sortable' cellspacing='1' cellpadding='5' border='0' style=width:99.90%;>
			<thead>
				<tr class=rowheader>
					<th align=center width=40px>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center >No BKM</th>
					<th align=center >" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center >" . $_SESSION['lang']['sumber'] . "</th>
					<th align=center >" . $_SESSION['lang']['noreferensi'] . "</th>
					<th align=center >" . $_SESSION['lang']['kebun'] . "</th>
					<th align=center >" . $_SESSION['lang']['divisi'] . "</th>
					<th align=center >" . $_SESSION['lang']['hari'] . "</th>
					<th align=center nowrap>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center >" . $_SESSION['lang']['jhk'] . "</th>
					<th align=center >" . $_SESSION['lang']['upah'] . "</th>
					<th align=center >" . $_SESSION['lang']['premi'] . "</th>
					<th align=center >" . $_SESSION['lang']['mandor'] . "</th>
					<th align=center >" . $_SESSION['lang']['mandor'] . " 1</th>
					<th align=center >" . $_SESSION['lang']['kerani'] . "</th>
					<th align=center >" . $_SESSION['lang']['nikasisten'] . "</th>
					<th align=center >" . $_SESSION['lang']['kontanan'] . "</th>
					<th align=center >" . $_SESSION['lang']['diverifikasioleh'] . "</th>
					<th align=center >" . $_SESSION['lang']['waktuverifikasi'] . "</th>
					<th align=center >" . $_SESSION['lang']['updateby'] . "</th>
					<th align=center colspan='7'>" . $_SESSION['lang']['action'] . "</th>
			</thead>
			<tbody id=contain> 
				<script>loaddata(0)</script>
			</tbody>
			<tfoot id=footData>
			</tfoot>
		</table>
	</div>";
CLOSE_BOX();
echo "</div>";

# === Form header input data ===
echo "<div id=header style=display:none>";
OPEN_BOX('','','header_trans');
echo "<fieldset style=float:left>
		<legend>Header</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>Nomor BKM</td> 
				<td>:</td>
				<td><input disabled id=nobkm style='width:145px;' class='myinputtext'/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['kebun'] . "</td> 
				<td>:</td>
				<td><select class='select2' style=\"width:150px;\" onchange=getdivmdr('kebun'); id=kodeorg>".$optorg."</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select class=select2 onclick=hapuswarna(this.id); onchange=getdivmdr('divisi'); style=\"width:150px;\" id=divisi>".$optdivisi."</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['mandor'] . "</td> 
				<td>:</td>
				<td><select class='select2' style=\"width:150px;\" id=mandor>" . $optMandor . "</select>
					<!--<img id='mandor' onclick=z.elSearch('mandor',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>--></td>
				
				<td>&nbsp;" . $_SESSION['lang']['kerani'] . "</td> 
				<td>:</td>
				<td>
					<select class='select2' style=\"width:150px;\" id=kerani>" . $optKerani . "</select>
				</td>

				<td>&nbsp;" . $_SESSION['lang']['kontanan'] . "</td> 
				<td>:</td>
				<td>
					<input type='checkbox' id='kontanan' style='vertical-align:middle'/>
				</td>
									
				<td rowspan=3>
					<fieldset>
                            <b>".$_SESSION['lang']['keterangan']." :</b><br>
                            &nbsp;- <input type='checkbox' checked disabled> : Kontanan<br>
                            &nbsp;- <input type='checkbox' disabled> : Tidak Kontanan 
					</fieldset>
				</td>
			</tr> 
			<tr>
				<td>".$_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input id=notransaksi style='width:145px;' class='myinputtext' disabled/></td>
				
				<td>&nbsp;".$_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext style='width:145px;' id=tgl onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 readonly/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['mandor'] . " 1</td> 
				<td>:</td>
				<td><select class='select2' style=\"width:150px;\" id=mandor1>" . $optMandor1 . "</select>
					<!--<img id='mandor1' onclick=z.elSearch('mandor1',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>--></td>
				
				<td>&nbsp;" . $_SESSION['lang']['nikasisten'] . "</td> 
				<td>:</td>
				<td>
					<select class='select2' style=\"width:150px;\" id=asst>" . $optAsst . "</select>
				</td>
			</tr> 
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=simpanheader()>" . $_SESSION['lang']['save'] . "</button>
					<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=mode value='baru'>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";

# === Form Detail Input Data ===
echo"<div id=detail style=display:none>";
OPEN_BOX();
CLOSE_BOX();
echo"</div>";
echo close_body();
?>