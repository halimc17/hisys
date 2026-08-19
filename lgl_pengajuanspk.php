<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript1.2 src='js/lgl_pengajuanspk.js?v=<?php echo time(); ?>'></script>
<script>
    dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";
    $(document).ready(function() {
        $('.select2').select2({
            dropdownAutoWidth:true
        });
    });
</script>
<?php
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$optpt =$optun=$optjns= "<option value=''></option>";
$where=$wherex='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where.="";
}else{
	$where.=" and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."'";
	$wherex.=" and kodeorg = '".$_SESSION['empl']['kodeorganisasi']."'";

}
$str = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$where." order by namaorganisasi asc ";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$optunit = $optunit2 = "<option value=''></option>";
$optdiv = "<option value=''></option>";

$str="select a.nopo from ".$dbname.".log_podt a left join ".$dbname.".log_poht b on a.nopo=b.nopo where b.stat_release='1' and a.spk='1'";
// $str = "select * from ".$dbname.".log_prapo_vw where 1=1 ".$wherex." and nopp like '%".$_SESSION['empl']['lokasitugas']."%' and close='2' and nopp not in (select distinct(nopp) from log_podt) order by nopp asc "; //exit('error'.$str);
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optdiv.="<option value=".$bar['nopo'].">".$bar['nopo']."</option>";
}
$str = "select CONCAT(kodetipe,kodesub) as kode,namasub from ".$dbname.".sdm_5subtipeasset order by kode asc";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optdiv.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['namasub']."</option>";
}

$str = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 and char_length(kodeorganisasi)=4 order by namaorganisasi asc ";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optdiv.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$wh = '';
$wh.=" and tipe in('AFDELING','STATION')";
$str = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$wh." order by namaorganisasi asc ";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optdiv.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
##PROJECT##
$str="select * from ".$dbname.".project where posting='0' order by kode asc"; //exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optdiv.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['nama']."</option>";
}
$optun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$optjenissupplier=$optdept="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str="select * from ".$dbname.".sdm_5departemen order by nama";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optdept.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['nama']."</option>";
}
$opttipeangkut="<option value=''></option>";
// $str="select * from ".$dbname.".setup_5jenislangsir order by kode";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
	// $opttipeangkut.="<option value=".$bar['id'].">".$bar['kode']." - ".$bar['keterangan']."</option>";
// }
$optsup="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str="select * from ".$dbname.".log_5supplier where status='1' order by namasupplier asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optsup.="<option value=".$bar['supplierid'].">".$bar['badanusaha'].". ".$bar['namasupplier']."</option>";
}

#cuma untuk jual tbs
$strcust="select * from ".$dbname.".pmn_4customer where kodecustomer in (select kodecustomer from ".$dbname.".pmn_4komoditi)";
//echo $strcust;
$rtrcust=fetchData($strcust);
foreach ($rtrcust as $key => $val) {
	//$optsup.="<option value=".$val['kodecustomer'].">".$val['kodecustomer']." - ".$val['namacustomer']."</option>";
}

$optkategori="<option value=''></option>";
$arrtipe=getEnum($dbname,'lgl_pengajuanspkht','kategori');
foreach( $arrtipe as $key => $val){
	if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
		if($val=='PUSAT'){
			$optkategori.="<option value=".$val.">".$val."</option>";			
		}
	}else{
		if($val=='LOKAL'){
			$optkategori.="<option value=".$val.">".$val."</option>";			
		}
	}
}

$optjns="<option value=''>Pilih Data</option>";
$arrtipe=getEnum($dbname,'lgl_pengajuanspkht','jenis');
foreach( $arrtipe as $key => $val){
	$optjns.="<option value=".$val.">".$val."</option>";
}


$optpajak="<option value=''></option>";
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='PPHSPK'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$arrpajak=explode(",",$bar['nilai']);
#$optpajak.="<option value='1171101'>1171101 - ".$nmakun['1171101']."</option>";
foreach( $arrpajak as $key => $val){
	$optpajak.="<option value=".$val.">".$val." - ".$nmakun[$val]."</option>";
}

$arrstat=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
$optstatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach( $arrstat as $key => $val){
	$optstatus.="<option value=".$key.">".$val."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('lgl_pengajuanspk').'</span>');
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
			<tr>
				<td hidden>" . $_SESSION['lang']['pt'] . "</td> 
				<td hidden>:</td>
				<td hidden><select class=select2 id=divsch onchange='loaddata(0)' style=\"width:150px;\">" . $optpt . "</select></td>
				
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=unitsch onchange='loaddata(0)' style=\"width:150px;\">" . $optun . "</select></td>
				
				<td>" . $_SESSION['lang']['jenis'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=jenissch onchange='loaddata(0)' style=\"width:150px;\">" . $optjns . "</select></td>
				<td>" . $_SESSION['lang']['project'] . "</td> 
				<td>:</td>
				<td><input id=projectsch onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:145px;\"></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['koderekanan'] . "</td> 
				<td>:</td>
				<td><input id=koderekanansch onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:145px;\"></td>
				
				<td>" . $_SESSION['lang']['nomor'] . "</td> 
				<td>:</td>
				<td><input id=nohaksch onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:145px;\"></td>
				<td>" . $_SESSION['lang']['status'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=statussch onchange='loaddata(0)' style=\"width:150px;\">" . $optstatus . "</select></td>
			</tr>
			<tr>
			</tr>
			";
echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</td></tr></table> ";
echo "</div>";
CLOSE_BOX();
echo"<div id=listData style=display:block >";
OPEN_BOX();
echo "<div>    
		<table cellpadding=3 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
				<th align=center>PT</th>
				<th align=center>" . $_SESSION['lang']['unit'] . "</th>
				<th align=center>" . $_SESSION['lang']['nomor'] . "</th>
				<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
				<th align=center>" . $_SESSION['lang']['koderekanan'] . "</th>
				<th align=center>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['project'] . "</th>
				<th align=center>" . $_SESSION['lang']['project'] . "</th>
				<th align=center>" . $_SESSION['lang']['rupiah'] . "</th>
				<th hidden align=center>Pendukung</th>
				<th align=center>" . $_SESSION['lang']['status'] . "</th>
				<th align=center width=40px>" . $_SESSION['lang']['status'] . " SPK</th>
				<th align=center width=40px>" . $_SESSION['lang']['status'] . " BAPP</th>
				<th align=center>" . $_SESSION['lang']['updateby'] . "</th>
				<th align=center colspan='6'>" . $_SESSION['lang']['action'] . "</th>
		</thead>
		 <tbody id=contain> 
			
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
		 </div><script>loaddata(0)</script>
	<input type=hidden id=jnsSupplierId />";
CLOSE_BOX();
echo "</div>";
echo "<div id=header style=display:none>";
OPEN_BOX();
echo "
	<fieldset>
	<legend>Header</legend>
	<table cellspacing=1 border=0 style='display: inline-block;vertical-align:top'>
		<tr style=display:none>
			<td>Pendukung</td> 
			<td>:</td> 
			<td colspan=50><input type=checkbox id=pendukung>  <i><b>Info : Jika checkbox di tick maka Pengajuan SPK ini selanjutnya tidak akan muncul di Kontrak Perjanjian Kerja dan BAPP</i></b></td> 
		</tr>
		<tr style=display:none><td colspan=50><hr></td></tr>
		<tr>
			<td>" . $_SESSION['lang']['nomor'] . "</td> 
			<td>:</td>
			<td colspan=4 style=\"width:200px;\"><input id=notransaksi class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" disabled style=\"width:195px;\"></td>
			<td class=bintang>" . $_SESSION['lang']['kategori'] . "&nbsp;</td> 
			<td>:</td>
			<td style=\"width:80px;\"><select class=select2 style=\"width:80px;\" id=kategori onchange=getjenis();>" . $optkategori . "</select></td>
			<td>" . $_SESSION['lang']['jenis'] . "&nbsp;&nbsp;<font size=2px style='color:red;vertical-align:middle;vertical-align:middle'><b>*</b></font>&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;<select class=select2 onchange=\"getunit();getnotransaksi();getnopoltipeangkut();\" style=\"width:79px;\" id=jenis>" . $optjns . "</select></td>
			<td class=bintang>" . $_SESSION['lang']['tanggal'] . "&nbsp;</td> 
			<td>:</td>
			<td>
				<input onchange=getnotransaksi() id='tanggalsurat' type='text' style='width:65px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
				<label id=lbljumlahhm style='display:none'>
					<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:125px;\" placeholder='Input Total HM' value='' id=jlhhm onkeyup=\"z.numberFormat('jlhhm',2);\" maxlength=8>
				</label>
				<label id=lblperbandingan style='display:none'>
					<input type=text id=noperbandingan class=myinputtext style=width:125px; placeholder='Click No Perbandingan' onclick=\"carinoperbandingan('".$_SESSION['lang']['find']."','<div id=formPencariandata></div>',event)\" readonly/>
				</label>
			</td>
		</tr>
		<tr>
			<td class=bintang>" . $_SESSION['lang']['pt'] . "&nbsp;</td> 
			<td>:</td>
			<td colspan=4><select class=select2 onchange=\"getunit();\" style=\"width:200px;\" id=pt>" . $optpt . "</select></td>
			<td class=bintang>" . $_SESSION['lang']['unit'] . "&nbsp;</td> 
			<td>:</td>
			<td colspan=2><select class=select2 onchange=\"getdivisi();\" style=\"width:227px;\" id=unit>" . $optun . "</select></td>
			<td id=labeldivisi>" . $_SESSION['lang']['divisi'] . "&nbsp;</td> 
			<td>:</td>
			<td colspan=4><select class=select2 onchange=\"getsubunit()\" style=\"width:200px;\" id=divisi>".$optdiv."</select>
			</td>
			<td hidden>
				<img id='imgkoderekanan' onclick=z.elSearch('divisi',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			
			<td class=bintang>" . $_SESSION['lang']['project'] . "&nbsp;</td> 
			<td>:</td>
			<td colspan=4><input id=project class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:195px;\"></td>
			
			<td class=bintang>" . $_SESSION['lang']['koderekanan'] . "&nbsp;</td> 
			<td>:</td>
			<td  colspan=2><select class=select2  style=\"width:227px;\" id=koderekanan onchange=getJenisSup(0)>" . $optsup . "</select></td>
			<td hidden><img id='imgkoderekanan' onclick=z.elSearch('koderekanan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
			</td>
			
			<td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['supplier']."&nbsp;</td> 
			<td>:</td>
			<td  colspan=2><select class=select2 title='Click tombol Refresh jika data tidak muncul.' style=\"width:200px;\" id=jenissupplier>" . $optjenissupplier . "</select>&nbsp;
				<img onclick=getJenisSup(0) class='zImgBtn' src='images/refresh2.png' title='Refresh' style='position:relative;top:3px;left:3px;'>
			</td>
			
		</tr>
		
		<t class=bintangr>
			<td>".$_SESSION['lang']['tanggalmulai']."&nbsp;</td> 
			<td>:</td>
			<td colspan=4>
				<input onchange=jumlahhari() id='tanggaldari' type='text' style='width:85px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
				s/d
				<input onchange=jumlahhari() id='tanggalsampai' type='text' style='width:82px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
			</td>
			<td>Jangka Waktu</td> 
			<td>:</td>
			<td colspan=2 width=200px><input id=jangkawaktu class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:222px;\"></td>
			
			
			<td>Retensi (%)</td> 
			<td>:</td>
			<td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:25px;\" value='' id=retensi  onkeyup=\"z.numberFormat('retensi',2);\" maxlength=\"3\">&nbsp;SPK Lama &nbsp;&nbsp;&nbsp;<select class=select2 style=\"width:100px;\" id=notransaksiold></select></td>
		</t>
		<tr>
			<td hidden>" . $_SESSION['lang']['bagian'] . "</td> 
			<td hidden>:</td>
			<td hidden colspan=4><select class=select2  onchange=\"getnotransaksi()\" style=\"width:200px;\" id=bagian>" . @$optdept . "</select></td>
			
			
			<td hidden>" . $_SESSION['lang']['denda'] . "</td> 
			<td hidden>:</td>
			<td hidden><input id=denda class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:100px;\"></td>
		
			<td hidden>Perjanjian Induk</td> 
			<td hidden>:</td>
			<td hidden colspan=4><input id=perjanjianinduk class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:195px;\"></td>
			
			<td hidden>Perjanjian Perubahan</td> 
			<td hidden>:</td>
			<td hidden colspan=4><input id=perjanjianperubahan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:195px;\"></td>
			
			
			<td hidden>Garansi</td>
			<td hidden>:</td>
			<td hidden colspan=4>
				<input id=garansi class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" placeholder='Garansi' style=\"width:197px;\"></td>
				
			<td hidden>" . $_SESSION['lang']['termin'] . " (%)</td>
			<td hidden>:</td>
			<td hidden colspan=4>
				<input type=text disabled class=myinputtextnumber  onkeypress='return angka_doang(event)' placeholder='Ke?' style=\"width:30px;\" value='1' id=terminke  >
				<input type=text   class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='%' style=\"width:40px;\" value='' id=persentermin  >
				
				<input type=text  class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='Rp' style=\"width:110px;\" value='' id=rptermin  disabled>
				
			</td>
			
		</tr>
		<tr>
			<td valign=top>" . $_SESSION['lang']['spesifikasi'] . "<br>" . $_SESSION['lang']['pekerjaan'] . "</td> 
			<td valign=top>:</td>
			<td colspan=18><textarea rows='3' maxlength=1024 id=spesifikasi type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:844px;\"></textarea></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['nilai'] . " " . $_SESSION['lang']['kontrak'] . " (Rp)</td>
			<td>:</td>
			
			<td colspan=4 valign=top><input id=rupiah_1 class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:195px;cursor:pointer\" onkeyup=\"z.numberFormat('rupiah_1',2);\">
			</td>
			
			<td>" . $_SESSION['lang']['pajak'] . " (%)</td>
			<td>:</td>
			<td colspan=2>
				<select class=select2 style=\"width:160px;\" id=jenispajak>".$optpajak."</select>&nbsp;&nbsp;
				<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='%' style=\"width:50px;\" value='' id=nilaipajak onkeyup=\"z.numberFormat('nilaipajak',2);\" maxlength=4>&nbsp;&nbsp;<img src='images/plus.png' class='zImgBtn' title='Tambah Pajak'; onclick=addpajak();style='position:relative;top:3px;left:3px;'>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			</td>
			<td>".$_SESSION['lang']['nopol']."</td> 
			<td>:</td>
			<td><input id=nopol class=myinputtext maxlength=9 onkeydown=\"upperCaseF(this)\" style=\"width:100px;align:left;\">
				<input id=supir class=myinputtext placeholder='Sopir' onkeydown=\"upperCaseF(this)\" style=\"width:95px;align:left;\"></td>
			<td width=20px><img src='images/plus.png' id='tmblnopol' class='zImgBtn' title='Tambah Nopol'; onclick=addnopol('1'); style='position:relative;top:3px;left:3px;'>
				</td>
		</tr>
		<tr><td colspan=8></td>
			<td colspan=5  valign=top align=left style=\"width:200px\" ></td>
				<td colspan=2>
				</td>
				<td colspan=5  style=display:none>
					<input id=kettermin onblur=\"settermin()\" disabled class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" placeholder='Keterangan' style=\"width:195px;\">
				</td>
				<td colspan=4  valign=top align=left style=\"width:200px\" id=conttermin></td>
				
				</tr>
		<tr>
			<td></td>
			<td></td>
			
			<td></td>
			<td></td>
			<td colspan=4></td>
			<td colspan=2 id=contpajak valign=top></td>
			<td></td>
			<td></td>
			<td colspan=2 id=contnopol valign=top></td>
		</tr>
		
		<tr>
			<td hidden>Tipe Angkut</td> 
			<td hidden>:</td>
			<td hidden colspan=4><select class=select2  style=\"width:120px;\" id=tipeangkut>" . $opttipeangkut . "</select></td>
		</tr>
		<tr><td colspan=18><hr></td></tr><tr><td colspan=2><i><b><font size=3px style=color:red;><b>*</b></font>) Kolom yang wajib terisi.</b></i></td></tr>
		<tr>
			<td colspan=7></td>
			<td colspan=11><input type=hidden id=method value='insert'>
				<button id=tomboldetail class=mybutton onclick=save()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batal class=mybutton onclick=cleardetail()>" . $_SESSION['lang']['cancel'] . "</button>
				<button class=mybutton onclick=showupload(event)>" . $_SESSION['lang']['upload'] . "</button>
			</td>
		</tr>
	</table></fieldset>";
CLOSE_BOX();
	
echo"<div id=detail style=display:none>";
OPEN_BOX();	
	echo"<fieldset id='tabledetail' style=float:left><legend>Detail</legend>
			<table >
				<tr>
					<td>" . $_SESSION['lang']['subunit'] . "</td> 
					<td>:</td>
					<td><select class=select2 style=\"width:250px;\" onchange=getsatuan(this) id=subunit></select></td>

					<td>&nbsp;" . $_SESSION['lang']['satuan'] . "</td> 
					<td>:</td>
					<td><select class=select2 style=\"width:110px;\" id=satuan></select>
					</td>
					
					<td>&nbsp;" . $_SESSION['lang']['volume'] . "</td> 
					<td>:</td>
					<td><input type=text onblur=getrupiah() class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:105px;\" value='' id=volume onkeyup=\"z.numberFormat('volume',2);\"></td>
					
					<td>" . $_SESSION['lang']['total'] . " Rp</td> 
					<td>:</td>
					<td><input id=total disabled class=myinputtextnumber style=\"width:100px;\" onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('total',2);\"></td>
					
				</tr><tr>
					<td>" . $_SESSION['lang']['kegiatan'] . "</td> 
					<td>:</td>
					<td><select class=select2 style=\"width:250px;\" onchange=getsatuan(this) id=kegiatan></select></td>
						
					<td>&nbsp;" . $_SESSION['lang']['hk'] . "</td> 
					<td>:</td>
					<td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:105px;\" value='' id=hk onkeyup=\"z.numberFormat('hk',2);\"></td>
					
					<td>Rp / " . $_SESSION['lang']['satuan'] . "</td> 
					<td>:</td>
					<td><input id=rppersat onblur=getrupiah() class=myinputtextnumber style=\"width:105px;\" onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('rppersat',2);\">
					</td>
					
					</tr><tr><td colspan=2>
					<input type=hidden id=methoddetail value='insertdetail'>
					<td><button class='mybutton' onclick='savedetail()'>" . $_SESSION['lang']['save'] . "</button>
						<button class='mybutton' onclick='cleardetaildt()'>" . $_SESSION['lang']['cancel'] . "</button></td>
					
				</tr>
			</table>
		</fieldset>
		<div style=clear:both></div>
		<div id=loaddatadetail></div>
		
		
		";
CLOSE_BOX();
echo"</div>";
		
echo"</div>";
echo close_body();
?>