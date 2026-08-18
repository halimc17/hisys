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
if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
	$val2="<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Lokasi tugas anda di : ".$_SESSION['empl']['tipelokasitugas'].", silahkan pindah ke KEBUN terlebih dahulu.</span>";
	exit($val2);
}

?>
<script language=javascript1.2 src='js/kebun_panenx.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
$where=$wh="";
/* $str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
if(count($adm)==0){
} */
$where= " and induk = '".$_SESSION['empl']['lokasitugas']."'";
$wh= " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
# Organisasi
$optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' ".$wh."";
$res = fetchData($str);
foreach($res as $key => $val){
	if($_SESSION['empl']['lokasitugas']==$val['kodeorganisasi']){
		$optorg.="<option value=".$val['kodeorganisasi']." selected >".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}else{		
		$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}
}

# Divisi
$optdiv = "<option value=''>&nbsp;</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' ".$where."";
$res = fetchData($str);
foreach($res as $key => $val){
	$n="";
	if($_SESSION['empl']['subbagian']==$val['kodeorganisasi']){
		$n="selected";
	}
	$optdiv.="<option value=".$val['kodeorganisasi']." ".$n.">".$val['kodeorganisasi']."</option>";	
}

# Posting
$arrPos=array("0"=>"Not Posted","1"=>"Posted");
$optPos="<option value=''>&nbsp;</option>";
foreach($arrPos as $key => $val){
	@$optPos.="<option value=".$key.">".$val."</option>";
}

# Periode
$optprd = "<option value=''>&nbsp;</option>";
$wh="";
if($_SESSION['empl']['subbagian']!=''){
	$wh=" and b.kodeorg like '".$_SESSION['empl']['subbagian']."%'";
}
$str="select DISTINCT (substr(a.tanggal,1,7)) as prd from ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi where a.kodeorg = '".$_SESSION['empl']['lokasitugas']."' ".$wh." order by prd desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
	#$optprd.="<option value=".$val['prd']." ".$n.">".$val['prd']."</option>";	
}
$no=0;
foreach($data as $thn => $vprd){
	$optprd.="<option value=".$thn." ".$n.">".$thn."</option>";			
	foreach($vprd as $prd){
		$no+=1;$n="";
		if($no==1){
			$n="selected";
		}
		$optprd.="<option value=".$prd." ".$n.">".$prd."</option>";			
	}
}


# === Option mandor dan kerani ===
$divisix='';
$whereKary=" and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
if($_SESSION['empl']['subbagian']!=''){
	#$divisix=" and a.subbagian='".$_SESSION['empl']['subbagian']."'";
}

$optMandor=$optAsst=$optMandor1=$optKerani= "<option value=''>&nbsp;</option>";

# === Option mandor dan kerani ===
$optAsst=$optMandor1=$optKerani= "<option value=''>&nbsp;</option>";

$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg ='".$_SESSION['empl']['lokasitugas']."' and tipe='PNN'";
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['kolom']=='mandor'){
		$mdr=$bar['jabatan'];
	}
	if($bar['kolom']=='mandor1'){
		$mdr1=$bar['jabatan'];
	}
	if($bar['kolom']=='kerani'){
		$krn=$bar['jabatan'];
	}
	if($bar['kolom']=='asst'){
		$asst=$bar['jabatan'];
	}
}

# Mandor
$d=$n="";
if($mdr!=''){
	$whr=" and a.kodejabatan in (".$mdr.")";
}else{
	$whr=" and b.namajabatan like '%mandor%' and b.namajabatan not like '%mandor%1%'";
}

$qMandor = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by  b.namajabatan,a.namakaryawan asc";
$res=fetchdata($qMandor);
foreach($res as $row){
	$dkary="";
	if($row['subbagian']!=$param['divisi']){
		$dkary=" [ ".$row['subbagian']." ]";
	}
	$d=$row['namajabatan'];
	if($d!=$n){			
		$optMandor.="<optgroup label='".$d."'>";
	}
	
	if($param['nikmandor']==$row['karyawanid']){
		$optMandor.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
	}else{			
		$optMandor.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
	}
	
	$n=$d;
	if($d!=$n){
		$optMandor.="</optgroup>";
	}
}

# Mandor 1
if($mdr1!=''){
	$whr=" and a.kodejabatan in (".$mdr1.")";
}else{
	$whr=" and b.namajabatan like '%mandor%I%' ";
}
$qMandor1 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
	"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by  b.namajabatan,a.namakaryawan asc";
$d=$n="";
$res=fetchdata($qMandor1);
foreach($res as $row){
	$dkary="";
	if($row['subbagian']!=$param['divisi']){
		$dkary=" [ ".$row['subbagian']." ]";
	}
	$d=$row['namajabatan'];
	if($d!=$n){			
		$optMandor1.="<optgroup label='".$d."'>";
	}
	
	if($param['nikmandor1']==$row['karyawanid']){
		$optMandor1.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
	}else{			
		$optMandor1.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]".$dkary."</option>";
	}	
	$n=$d;
	if($d!=$n){
		$optMandor1.="</optgroup>";
	}
}

# Asst
if($asst!=''){
	$whr=" and a.kodejabatan in (".$asst.")";
}else{
	$whr=" and (b.namajabatan like '%asst%' or "." b.namajabatan like '%asist%'  or namajabatan like '%assist%') and (namajabatan like '%div%'  or namajabatan like '%afd%' or namajabatan like '%kebun%' or namajabatan like '%rawat%' or namajabatan like '%pemel%' or namajabatan like '%panen%')";
}
$qAsst = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
	"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." ".$divisix." order by b.namajabatan,a.namakaryawan asc";
$d=$n="";
$res=fetchdata($qAsst);
foreach($res as $row){
	if($row['subbagian']!=''){
		$row['subbagian']=$row['subbagian'];
	}else{
		$row['subbagian']=$row['lokasitugas'];
	}
	$d=$row['namajabatan'];
	if($d!=$n){			
		$optAsst.="<optgroup label='".$d."'>";
	}
	$optAsst.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]</option>";
	$n=$d;
	if($d!=$n){
		$optAsst.="</optgroup>";
	}
}

# Kerani
if($krn!=''){
	$whr=" and a.kodejabatan in (".$krn.")";
}else{
	$whr=" and (b.namajabatan like '%krani%panen%' or "." b.namajabatan like '%kerani%panen%' or b.namajabatan like '%harves%clerk%') and (b.namajabatan not like '%account%' and b.namajabatan not like '%akunt%' and b.namajabatan not like '%Store%' and b.namajabatan not like '%gudang%' and b.namajabatan not like '%civil%') and a.lokasitugas not like '%M' ";
}
$qKerani = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
	"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by b.namajabatan,  a.namakaryawan asc";
$d=$n="";
$res=fetchdata($qKerani);
foreach($res as $row){
	if($row['subbagian']!=''){
		$row['subbagian']=$row['subbagian'];
	}else{
		$row['subbagian']=$row['lokasitugas'];
	}
	$d=$row['namajabatan'];
	if($d!=$n){			
		$optKerani.="<optgroup label='".$d."'>";
	}
	if($param['kerani']==$row['karyawanid']){
		$optKerani.="<option value=".$row['karyawanid']." selected>".$row['namakaryawan']." [".$row['nik']."]</option>";
	}else{			
		$optKerani.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."]</option>";
	}
	$n=$d;
	if($d!=$n){
		$optKerani.="</optgroup>";
	}
}

$arrJab=array("0"=>"Normal","1"=>"Banjir");
foreach($arrJab as $brs1 => $isi1){
	@$optStatus.="<option value=".$brs1.">".$isi1."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_panenx').'</span>','judul_header');
# === Header dan Pencarian data ===
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
			   <td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
			   <td><input type=text class=myinputtext id=notransaksisch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>

				<td>" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=divsch onchange='loaddata()' style=\"width:130px;\">".$optdiv."</select></td>
			
				<td>" . $_SESSION['lang']['posting'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=postingsrc onchange='loaddata()' style=\"width:130px;\">".$optPos."</select>
				</td>
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
				<td><select class=select2 id=periodesch onchange='loaddata()' style=\"width:130px;\">".$optprd."</select>
				</td>
				
			</tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();

echo "<!--<div class='table-scroll'>-->
			<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
				<thead>
					<tr class=rowheader>
						<th align=center width=30px>" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center >" . $_SESSION['lang']['notransaksi'] . "</th>
						<th align=center >No BKM</th>
						<th align=center >" . $_SESSION['lang']['noreferensi'] . "</th>
						<th align=center >" . $_SESSION['lang']['device'] . "</th>
						<th align=center >" . $_SESSION['lang']['sumber'] . "</th>
						<th align=center >" . $_SESSION['lang']['organisasi'] . "</th>
						<th align=center >" . $_SESSION['lang']['divisi'] . "</th>
						<th align=center >" . $_SESSION['lang']['hari'] . "</th>
						<th align=center >" . $_SESSION['lang']['tanggal'] . "</th>
						<th align=center >" . $_SESSION['lang']['jjg'] . "</th>
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
				<td>".$_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input id=notransaksi style='width:145px;' class='myinputtext' disabled/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=kodeorg>" . $optorg . "</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['mandor'] . "</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=mandor>" . $optMandor . "</select>
					<!--<img id='mandor' onclick=z.elSearch('mandor',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>--></td>
				
				<td>&nbsp;" . $_SESSION['lang']['keranipanen'] . "</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=kerani>" . $optKerani . "</select>
					<!--<img id='kerani' onclick=z.elSearch('kerani',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>--></td>
			</tr> 
			<tr>
				<td>Nomor BKM</td> 
				<td>:</td>
				<td><input disabled id=nobkm style='width:145px;' class='myinputtext'/></td>
				
				<td>&nbsp;".$_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext style='width:145px;' id=tgl onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 readonly/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['mandor'] . " 1</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=mandor1>" . $optMandor1 . "</select>
					<!--<img id='mandor1' onclick=z.elSearch('mandor1',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>--></td>
				
				<td style=display:none>&nbsp;" . $_SESSION['lang']['nikasisten'] . "</td> 
				<td style=display:none>:</td>
				<td style=display:none><select class=select2 style=\"width:150px;\" id=asst>" . $optAsst . "</select>
					<!--<img id='asst' onclick=z.elSearch('asst',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>--></td>";
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