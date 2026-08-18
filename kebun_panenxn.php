<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
if($_SESSION['org']['period']['start']==''){
	$val1="<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Silahkan buat periode akutansi untuk unit ".$_SESSION['empl']['lokasitugas']." terlebih dahulu</span>";
	exit($val1);
}
/* 
if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
	$val2="<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Lokasi tugas anda di : ".$_SESSION['empl']['tipelokasitugas'].", silahkan pindah ke KEBUN terlebih dahulu.</span>";
	exit($val2);
}
 */
?>
<script language=javascript1.2 src='js/kebun_panenxn.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php
$where=$wh="";
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorg.="<option value=".$_SESSION['empl']['lokasitugas'].">".$_SESSION['empl']['lokasitugas']." - ".getNamaOrg($_SESSION['empl']['lokasitugas'])."</option>";

# Divisi
$wh="";
$wh=" and induk in (".getOrgDetail(2).")";
$optdiv = "<option value=''></option>";
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
		$optdiv.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optdiv.="<option value=".$val['kodeorganisasi']." ".$s.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
	$n=$d;
	if($d!=$n){
		$optdiv.="</optgroup>";
	}
}
$wh="";
$wh=" and induk in (".getOrgDetail(2).")";
$optdiv = "<option value=''></option>";
$optdivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
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
		$optdiv.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optdivisi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optdiv.="<option value=".$val['kodeorganisasi']." ".$s.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
	$optdivisi.="<option value=".$val['kodeorganisasi']." ".$s.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
	$n=$d;
	if($d!=$n){
		$optdiv.="</optgroup>";
		$optdivisi.="</optgroup>";
	}
}



# Posting
$arrPos=array("0"=>"Not Posted","1"=>"Posted");
$optPos="<option value=''></option>";
foreach($arrPos as $key => $val){
	@$optPos.="<option value=".$key.">".$val."</option>";
}

// # Periode
// $optprd = "<option value=''></option>";
// $wh="";
// $wh.=" and a.kodeorg in (".getOrgDetail(2).")";
// $str="select DISTINCT (substr(a.tanggal,1,7)) as prd from ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi where 1=1 ".$wh." and tipetransaksi='PNN' order by prd desc";
// $res = fetchData($str);
// foreach($res as $key => $val){
	// $data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
	// #$optprd.="<option value=".$val['prd']." ".$n.">".$val['prd']."</option>";	
// }
// $no=0;
// foreach($data as $thn => $vprd){
	// $n="";
	// $optprd.="<option value=".$thn." ".$n.">".$thn."</option>";			
	// foreach($vprd as $prd){
		// $no+=1;$n="";
		// if($no==1){
			// $n="selected";
		// }
		// $optprd.="<option value=".$prd." ".$n.">".$prd."</option>";			
	// }
// }

for($x=0;$x<25;$x++){
	$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
	$optprd.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}

// # mandor search
// $optmdrsch = "<option value=''></option>";
// $where=" and kodeorg in (".getOrgDetail(2).")";
// $str = "select distinct nikmandor from ".$dbname.".kebun_aktifitas where 1=1 ".$where."";
// $res = fetchData($str);
// foreach($res as $row){
	// $optkary=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$row['nikmandor']."'");
	// $optmdrsch.="<option value=".$row['nikmandor'].">".$optkary[$row['nikmandor']]."</option>";
// }

$arrJab=array("0"=>"Normal","1"=>"Banjir");
foreach($arrJab as $brs1 => $isi1){
	@$optStatus.="<option value=".$brs1.">".$isi1."</option>";
}


$kontanan = "<option value=''></option>";
$kontanan.= "<option value=''>Kerja</option>";
$kontanan.= "<option value='KONTAN'>Kontanan</option>";

OPEN_BOX('','<span class=judul>'.getMenu('kebun_panenxn').'</span>','judul_header');
# === Header dan Pencarian data ===
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data('kebun')>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
			   <td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
			   <td><input type=text class=myinputtext id=notransaksisch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>

				<td hidden>" . $_SESSION['lang']['mandor'] . "</td> 
				<td hidden>:</td>
				<td hidden><select id=mandorsrc onchange='loaddata()' style=\"width:130px;\">".$optmdrsch."</select></td>
			
				<td>" . $_SESSION['lang']['posting'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=postingsrc onchange='loaddata()' style=\"width:130px;\">".$optPos."</select></td>
				
				<td>" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=divsch onchange='loaddata()' style=\"width:130px;border-color:blue;\">".$optdiv."</select></td>
				
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['tanggalmulai'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' readonly=readonly style='width:130px;' class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
				</td>
				
				<td>" . $_SESSION['lang']['tanggalselesai'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' readonly=readonly style='width:125px;' class='myinputtext' id='tglselesai' onmousemove='setCalendar(this.id)' onkeypress='return false'; /></td>
				
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=periodesch onchange='loaddata()' style=\"width:130px;\">".$optprd."</select></td>
				
				
				<td hidden>" . $_SESSION['lang']['kontanan'] . "</td> 
				<td hidden>:</td>
				<td hidden><select id=kontanansch onchange='loaddata()' style=\"width:130px;\">".$kontanan."</select></td>
				
			</tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();

echo "
	<div class='table-scroll' style=height:60vh>
	<table class='sortable' cellspacing=1 cellpadding=5 border=0>
				<thead>
					<tr>
						<th align=center width=50px>" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center >" . $_SESSION['lang']['notransaksi'] . "</th>
						<th align=center hidden>" . $_SESSION['lang']['nospk'] . "</th>
						<th align=center >" . $_SESSION['lang']['noreferensi'] . "</th>
						<th align=center >" . $_SESSION['lang']['organisasi'] . "</th>
						<th align=center >" . $_SESSION['lang']['divisi'] . "</th>
						<th align=center >" . $_SESSION['lang']['hari'] . "</th>
						<th align=center width=75px>" . $_SESSION['lang']['tanggal'] . "</th>
						<th align=center >" . $_SESSION['lang']['jjg'] . "</th>
						<th align=center width=50px>" . $_SESSION['lang']['jhk'] . "</th>
						<th align=center >" . $_SESSION['lang']['upah'] . "</th>
						<th align=center >" . $_SESSION['lang']['premi'] . "</th>
						<th align=center >" . $_SESSION['lang']['denda'] . "</th>
						<th align=center >" . $_SESSION['lang']['mandor'] . "</th>
						<th align=center >" . $_SESSION['lang']['mandor'] . " 1</th>
						<th align=center >" . $_SESSION['lang']['keranipanen'] . "</th>
						<th align=center >" . $_SESSION['lang']['updateby'] . "</th>
						<th align=center colspan='7'>" . $_SESSION['lang']['action'] . "</th>
				</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>
	 ";
CLOSE_BOX();
echo "</div>";

$optjenis="<option value=''>HARIAN</option>";
$optjenis.="<option value='BOR'>BORONGAN</option>";


$namasupp=array();
$optsupp = "<option value=''></option>";
$sql = "SELECT a.* FROM " . $dbname . ".log_spkht a left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi where a.posting='0' and b.close='0' and b.jenis='PANENTBS' and a.kodeorg in (".getOrgDetail(24).") order by a.notransaksi asc";
$res = fetchdata($sql);
foreach($res as $bar){
	$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['koderekanan']."'");
	$optsupp.="<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
}

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
				<td><select class=select2 onclick=hapuswarna(this.id); onblur=getnotransaksi(); onchange=getdivmdr('kebun'); style=\"width:150px;\" id=kodeorg>" . $optorg . "</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select class=select2 onclick=hapuswarna(this.id); onchange=getdivmdr('divisi'); style=\"width:150px;\" id=divisi>".$optdivisi."</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['mandor'] . "</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=mandor>" . $optMandor . "</select></td>
				
				<td hidden>&nbsp;" . $_SESSION['lang']['jenis'] . "</td> 
				<td hidden>:</td>
				<td hidden><select style=\"width:150px;\" id=jenis onchange=getnospk();>" . $optjenis . "</select></td>
				<td hidden><img id='jenis' onclick=z.elSearch('jenis',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
				
			</tr> 
			<tr>
				
				<td>".$_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input id=notransaksi style='width:145px;' class='myinputtext' disabled/></td>
				
				<td>&nbsp;".$_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text readonly=readonly  onchange=getnotransaksi();hapuswarna(this.id); class=myinputtext style='width:145px;' id=tgl onmousemove=setCalendar(this.id); onkeypress=return false; maxlength=10 /></td>
				
				<td>&nbsp;" . $_SESSION['lang']['mandor'] . " 1</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=mandor1>" . $optMandor1 . "</select></td>
				
				
				<td>&nbsp;" . $_SESSION['lang']['keranipanen'] . "</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=kerani>" . $optKerani . "</select></td>
				
				
				<td hidden>&nbsp;" . $_SESSION['lang']['nikasisten'] . "</td> 
				<td hidden>:</td>
				<td hidden><select style=\"width:150px;\" id=asst>" . $optAsst . "</select>
					<img id='asst' onclick=z.elSearch('asst',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
				
				<td hidden>&nbsp;" . $_SESSION['lang']['nospk'] . "</td> 
				<td hidden>:</td>
				<td hidden><select style=\"width:150px;\" id=nospk disabled>" . $optsupp . "</select></td>
				<td hidden><img id='nospk' onclick=z.elSearch('nospk',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
				
					";
			$tampil='';
			$tampil=" style=display:none ";
			
			echo"<td ".$tampil.">&nbsp;" . $_SESSION['lang']['status'] . "</td> 
				<td ".$tampil.">:</td>
				<td ".$tampil."><select style=\"width:150px;\" id=status>" . $optStatus . "</select></td>";
				
		echo"</tr> 
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=addHeader()>" . $_SESSION['lang']['save'] . "</button>
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
echo"<div id=detailx style=display:none>";
OPEN_BOX();
echo"<div id=detail style=display:none>";
echo"</div>";
CLOSE_BOX();
echo"</div>";

echo close_body();
?>