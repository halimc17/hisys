<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/kebun_borongan.js?v=1.5'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
$_SESSION['bgimage'] = array();

#validasi jika buka tab baru dengan jenis yg beda
@$statusawal=$_SESSION['tmp']['kebun']['tipeTrans'];

# Organisasi
$optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
$res = fetchData($str);
$optorg.="<option value=".$res[0]['kodeorganisasi'].">".$res[0]['kodeorganisasi']." - ".$res[0]['namaorganisasi']."</option>";

# Divisi
$optdiv = "<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') and induk = '".$_SESSION['empl']['lokasitugas']."'";
$res = fetchData($str);
foreach($res as $key => $val){
	$optdiv.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']."</option>";	
}

# Posting
$arrPos=array("0"=>"Not Posted","1"=>"Posted");
$optPos="<option value=''></option>";
foreach($arrPos as $key => $val){
	$optPos.="<option value=".$key.">".$val."</option>";
}

# Periode
$optprd = "<option value=''></option>";
$str="select DISTINCT (substr(tanggal,1,7)) as prd from ".$dbname.".kebun_aktifitas where kodeorg = '".$_SESSION['empl']['lokasitugas']."' order by prd desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$optprd.="<option value=".$val['prd'].">".$val['prd']."</option>";	
}

@$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
@$where=" and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
$str = "select distinct(nikmandor) from ".$dbname.".kebun_aktifitas where 1=1 ".$where." order by nikmandor asc";
$optMdrsrc="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optMdrsrc.="<option value=".$bar['nikmandor'].">".@$nmkar[$bar['nikmandor']]."</option>";
}
	$optMdrsrc.="<option value='blank'>BLANK / KOSONG</option>";

# === Option mandor dan kerani ===
$whereKary=" and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
if($_SESSION['empl']['subbagian']!=''){
	$whereKary.=" and a.subbagian='".$_SESSION['empl']['subbagian']."'";
}

$optMandor=$optAsst=$optMandor1=$optKerani= "<option value=''></option>";
# Mandor
$qMandor = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan from ".$dbname.".datakaryawan a
	left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where b.namajabatan like '%Mandor%' ".$whereKary." order by a.namakaryawan asc";
$resMandor=$owlPDO->query($qMandor) or die(print " Gagal: ".PDOException::getMessage());
$resMandor->setFetchMode(PDO::FETCH_ASSOC);
while($row=$resMandor->fetch()){
	$optMandor.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."] ".$row['namajabatan']."</option>";
}

# Mandor 1
$qMandor1 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan from ".$dbname.".datakaryawan a ".
	"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where b.namajabatan like '%mandor%' ".$whereKary." order by a.namakaryawan asc";
$resMandor1=$owlPDO->query($qMandor1) or die(print " Gagal: ".PDOException::getMessage());
$resMandor1->setFetchMode(PDO::FETCH_ASSOC);
while($row=$resMandor1->fetch()){
	$optMandor1.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."] ".$row['namajabatan']."</option>";
}

# Asst
$qAsst = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan from ".$dbname.".datakaryawan a ".
	"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where (b.namajabatan like '%asst%' or "." b.namajabatan like '%asist%') ".$whereKary." order by a.namakaryawan asc";
$resAsst=$owlPDO->query($qAsst) or die(print " Gagal: ".PDOException::getMessage());
$resAsst->setFetchMode(PDO::FETCH_ASSOC);
while($row=$resAsst->fetch()){
	$optAsst.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."] ".$row['namajabatan']."</option>";
}

# Kerani
$qKerani = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan from ".$dbname.".datakaryawan a ".
	"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where (b.namajabatan like '%krani%' or "." b.namajabatan like '%kerani%' or b.namajabatan like '%clerk%') and a.lokasitugas not like '%M' ".$whereKary." order by a.namakaryawan asc";
$resKerani=$owlPDO->query($qKerani) or die(print " Gagal: ".PDOException::getMessage());
$resKerani->setFetchMode(PDO::FETCH_ASSOC);
while($row=$resKerani->fetch()){
	$optKerani.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."] ".$row['namajabatan']."</option>";
}
$arrsts=array('TM'=>'TM','TBM'=>'TBM');
foreach($arrsts as $key){
	@$statusblok.="<option value=".$key.">".$key."</option>";
}

	
OPEN_BOX('','<span class=judul>'.getMenu('kebun_borongan').'</span>','judul_header');
# === Header dan Pencarian data ===
echo"<div id=action_list><input style=display:none id=stsawal value=".$statusawal.">";
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
				<td><input type=text class=myinputtext id=divsch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /></td>
				
				<td>" . $_SESSION['lang']['status'] . "</td> 
				<td>:</td>
				<td><select id=statussch onchange='loaddata()' style=\"width:135px;\">".$statusblok."</select>
				</td>
								
				<td>" . $_SESSION['lang']['posting'] . "</td> 
				<td>:</td>
				<td><select id=postingsrc onchange='loaddata()' style=\"width:130px;\">".$optPos."</select>
				</td>

			</tr>
			<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:130px;' class='myinputtext' id='tglsch' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
				</td>
				
				<td>Kepala Pemborong</td> 
				<td>:</td>
				<td><input type=text class=myinputtext id=kepalaborongansch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' />
				</td>
				
				<td>Nomor Borongan</td> 
				<td>:</td>
				<td><input type=text class=myinputtext id=nomorborongansch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' />
				</td>
			
			   
			</tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();

echo "<fieldset>
		<legend>".$_SESSION['lang']['list'] . "</legend>
		<div>    
			<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
					<tr class=rowheader>
						<td align=center width=40px>" . $_SESSION['lang']['nourut'] . "</td>
						<td align=center >" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center >" . $_SESSION['lang']['unit'] . "</td>
						<td align=center >" . $_SESSION['lang']['divisi'] . "</td>
						<td align=center width=70px>Tanggal Input</td>
						<td align=center >Nomor Borongan</td>
						<td align=center >Kepala Pemborong</td>
						<td align=center >".$_SESSION['lang']['kegiatan']."</td>
						<td align=center >" . $_SESSION['lang']['updateby'] . "</td>
						<td align=center >No Pengajuan</td>
						<td align=center >Tgl Pengajuan</td>
						<td align=center colspan=2>Status</td>
						<td align=center>Jurnal</td>
						<td align=center>Tanggal Jurnal</td>
						<td align=center colspan='6'>" . $_SESSION['lang']['action'] . "</td>
				</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>
	 </fieldset>";
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
				<td><select style=\"width:150px;\" id=kodeorg>" . $optorg . "</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select style=\"width:150px;\" id=divisi>" . $optdiv . "</select></td>
				<td>&nbsp;" . $_SESSION['lang']['status'] . "</td> 
				<td>:</td>
				<td><select style=\"width:150px;\" id=statusblok>" . $statusblok . "</select></td>
				
			</tr> 
			<tr>
				<td>".$_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext style='width:145px;' id=tgl onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 /></td>
				
				
				<td>&nbsp;Kepala Pemborong</td> 
				<td>:</td>
				<td><input id=palaborong style='width:145px;' class='myinputtext'/></td>
				
				<td>&nbsp;Nomor Borongan</td> 
				<td>:</td>
				<td><input id=noborong style='width:145px;' class='myinputtext'/></td>
				
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