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

#opsi filter unit: hanya unit sesuai detail akses user
$optunsch = "<option value=''>".$_SESSION['lang']['all']."</option>";
foreach (getOrgDetail(1) as $kodeunit=>$namaunit)
{
	$optunsch .= "<option value='".$kodeunit."'>".$kodeunit." - ".$namaunit."</option>";
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
				<td><select class=select2 id=unitsch onchange='loaddata(0)' style=\"width:150px;\">" . $optunsch . "</select></td>
				
				<td>" . $_SESSION['lang']['jenis'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=jenissch onchange='loaddata(0)' style=\"width:150px;\">" . $optjns . "</select></td>
				<td>" . $_SESSION['lang']['project'] . "</td> 
				<td>:</td>
				<td><input id=projectsch onkeypress='enterkey(event,loaddata)' class=myinputtext style=\"width:150px;box-sizing:border-box;\"></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['koderekanan'] . "</td> 
				<td>:</td>
				<td><input id=koderekanansch onkeypress='enterkey(event,loaddata)' class=myinputtext style=\"width:150px;box-sizing:border-box;\"></td>
				
				<td>" . $_SESSION['lang']['nomor'] . "</td> 
				<td>:</td>
				<td><input id=nohaksch onkeypress='enterkey(event,loaddata)' class=myinputtext onkeydown=\"upperCaseF(this)\" style=\"width:150px;box-sizing:border-box;\"></td>
				<td>" . $_SESSION['lang']['status'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=statussch onchange='loaddata(0)' style=\"width:150px;\">" . $optstatus . "</select></td>
			</tr>
			";
echo"<tr><td colspan=9><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
	<button class=mybutton onclick=exportExcelSPK()>" . $_SESSION['lang']['excel'] . "</button>
	<button class=mybutton onclick=exportPdfSPK()>PDF</button></td></tr></table>";
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
				<th align=center colspan='7'>" . $_SESSION['lang']['action'] . "</th>
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
	<style>#tblheader td{white-space:nowrap;} #tblheader input.myinputtext, #tblheader input.myinputtextnumber{height:23px;box-sizing:border-box;}</style>
	<table id=tblheader cellspacing=1 cellpadding=2 border=0 style='width:1040px;'>
		<tr style=display:none>
			<td>Pendukung</td> 
			<td>:</td> 
			<td colspan=50><input type=checkbox id=pendukung>  <i><b>Info : Jika checkbox di tick maka Pengajuan SPK ini selanjutnya tidak akan muncul di Kontrak Perjanjian Kerja dan BAPP</i></b></td> 
		</tr>
		<tr style=display:none><td colspan=50><hr></td></tr>
		<tr>
			<td>" . $_SESSION['lang']['nomor'] . "</td>
			<td>:</td>
			<td><input id=notransaksi class=myinputtext disabled style=\"width:200px;box-sizing:border-box;\"></td>
			<td class=bintang style='padding-left:16px;'>" . $_SESSION['lang']['kategori'] . "&nbsp;</td>
			<td>:</td>
			<td><select class=select2 style=\"width:200px;\" id=kategori onchange=getjenis();>" . $optkategori . "</select></td>
			<td class=bintang style='padding-left:16px;'>" . $_SESSION['lang']['jenis'] . "&nbsp;</td>
			<td>:</td>
			<td><select class=select2 onchange=\"getunit();getnotransaksi();getnopoltipeangkut();\" style=\"width:200px;\" id=jenis>" . $optjns . "</select></td>
		</tr>
		<tr>
			<td class=bintang>" . $_SESSION['lang']['pt'] . "&nbsp;</td>
			<td>:</td>
			<td><select class=select2 onchange=\"getunit();\" style=\"width:200px;\" id=pt>" . $optpt . "</select></td>
			<td class=bintang style='padding-left:16px;'>" . $_SESSION['lang']['unit'] . "&nbsp;</td>
			<td>:</td>
			<td><select class=select2 onchange=\"getdivisi();\" style=\"width:200px;\" id=unit>" . $optun . "</select></td>
			<td id=labeldivisi style='padding-left:16px;'>" . $_SESSION['lang']['divisi'] . "&nbsp;</td>
			<td>:</td>
			<td><select class=select2 onchange=\"getsubunit()\" style=\"width:200px;\" id=divisi>".$optdiv."</select></td>
			<td hidden>
				<img id='imgkoderekanan' onclick=z.elSearch('divisi',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td class=bintang>" . $_SESSION['lang']['tanggal'] . "&nbsp;</td>
			<td>:</td>
			<td><input onchange=getnotransaksi() id='tanggalsurat' type='text' style='width:200px;box-sizing:border-box;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/></td>
			<td class=bintang style='padding-left:16px;'>Nama " . $_SESSION['lang']['project'] . " / Nama SPK &nbsp;</td>
			<td>:</td>
			<td><input id=project class=myinputtext style=\"width:200px;box-sizing:border-box;\"></td>
			<td class=bintang style='padding-left:16px;'>" . $_SESSION['lang']['koderekanan'] . "&nbsp;</td>
			<td>:</td>
			<td><select class=select2 style=\"width:200px;\" id=koderekanan onchange=getJenisSup(0)>" . $optsup . "</select></td>
			<td hidden><img id='imgkoderekanan' onclick=z.elSearch('koderekanan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
		</tr>
		<tr id=lbljumlahhm style='display:none'>
			<td>Total HM</td>
			<td>:</td>
			<td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:200px;box-sizing:border-box;\" placeholder='Input Total HM' value='' id=jlhhm onkeyup=\"z.numberFormat('jlhhm',2);\" maxlength=8></td>
		</tr>
		<tr id=lblperbandingan style='display:none'>
			<td>No Perbandingan</td>
			<td>:</td>
			<td><input type=text id=noperbandingan class=myinputtext style=\"width:200px;box-sizing:border-box;\" placeholder='Click No Perbandingan' onclick=\"carinoperbandingan('".$_SESSION['lang']['find']."','<div id=formPencariandata></div>',event)\" readonly/></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['supplier'] . "&nbsp;</td>
			<td>:</td>
			<td nowrap><select class=select2 title='Click tombol Refresh jika data tidak muncul.' style=\"width:178px;\" id=jenissupplier>" . $optjenissupplier . "</select>
				<img onclick=getJenisSup(0) class='zImgBtn' src='images/refresh2.png' title='Refresh' style='position:relative;top:3px;left:3px;'>
			</td>
			<td style='padding-left:16px;'>" . $_SESSION['lang']['tanggalmulai'] . "&nbsp;</td>
			<td>:</td>
			<td nowrap>
				<input onchange=jumlahhari() id='tanggaldari' type='text' style='width:85px;box-sizing:border-box;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
				s/d
				<input onchange=jumlahhari() id='tanggalsampai' type='text' style='width:85px;box-sizing:border-box;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
			</td>
			<td style='padding-left:16px;'>Jangka Waktu</td>
			<td>:</td>
			<td><input id=jangkawaktu class=myinputtext style=\"width:200px;box-sizing:border-box;\"></td>
		</tr>
		<tr>
			<td>Retensi (%)</td>
			<td>:</td>
			<td nowrap><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:40px;box-sizing:border-box;\" value='' id=retensi onkeyup=\"z.numberFormat('retensi',2);\" maxlength=\"3\">
				&nbsp;SPK Lama&nbsp;<select class=select2 style=\"width:100px;\" id=notransaksiold></select></td>
			<td style='padding-left:16px;'>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['nilai'] . " " . $_SESSION['lang']['kontrak'] . " (Rp)</td>
			<td>:</td>
			<td><input id=rupiah_1 class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:200px;box-sizing:border-box;cursor:pointer\" onkeyup=\"z.numberFormat('rupiah_1',2);\"></td>
			<td style='padding-left:16px;'>" . $_SESSION['lang']['pajak'] . " (%)</td>
			<td>:</td>
			<td nowrap>
				<select class=select2 style=\"width:110px;\" id=jenispajak>".$optpajak."</select>
				<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='%' style=\"width:50px;box-sizing:border-box;\" value='' id=nilaipajak onkeyup=\"z.numberFormat('nilaipajak',2);\" maxlength=4>
				<img src='images/plus.png' class='zImgBtn' title='Tambah Pajak' onclick=addpajak(); style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nopol']."</td>
			<td>:</td>
			<td nowrap><input id=nopol class=myinputtext maxlength=9 onkeydown=\"upperCaseF(this)\" style=\"width:90px;box-sizing:border-box;\">
				<input id=supir class=myinputtext placeholder='Sopir' onkeydown=\"upperCaseF(this)\" style=\"width:85px;box-sizing:border-box;\">
				<img src='images/plus.png' id='tmblnopol' class='zImgBtn' title='Tambah Nopol' onclick=addnopol('1'); style='position:relative;top:3px;left:3px;'>
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td id=contpajak valign=top></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td id=contnopol valign=top></td>
		</tr>
		<tr>
			<td valign=top>" . $_SESSION['lang']['spesifikasi'] . "<br>" . $_SESSION['lang']['pekerjaan'] . "</td>
			<td valign=top>:</td>
			<td colspan=7><textarea rows='3' maxlength=1024 id=spesifikasi type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:100%;box-sizing:border-box;\"></textarea></td>
		</tr>
		<tr style=display:none>
			<td>" . $_SESSION['lang']['bagian'] . "</td>
			<td><select class=select2 onchange=\"getnotransaksi()\" style=\"width:200px;\" id=bagian>" . @$optdept . "</select></td>
			<td>" . $_SESSION['lang']['denda'] . "</td>
			<td><input id=denda class=myinputtext style=\"width:100px;\"></td>
			<td>Perjanjian Induk</td>
			<td><input id=perjanjianinduk class=myinputtext style=\"width:195px;\"></td>
			<td>Perjanjian Perubahan</td>
			<td><input id=perjanjianperubahan class=myinputtext style=\"width:195px;\"></td>
			<td>Garansi</td>
			<td><input id=garansi class=myinputtext placeholder='Garansi' style=\"width:197px;\"></td>
			<td>" . $_SESSION['lang']['termin'] . " (%)</td>
			<td>
				<input type=text disabled class=myinputtextnumber  onkeypress='return angka_doang(event)' placeholder='Ke?' style=\"width:30px;\" value='1' id=terminke  >
				<input type=text   class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='%' style=\"width:40px;\" value='' id=persentermin  >
				<input type=text  class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='Rp' style=\"width:110px;\" value='' id=rptermin  disabled>
			</td>
			<td><input id=kettermin onblur=\"settermin()\" disabled class=myinputtext placeholder='Keterangan' style=\"width:195px;\"></td>
			<td id=conttermin></td>
			<td>Tipe Angkut</td>
			<td><select class=select2  style=\"width:120px;\" id=tipeangkut>" . $opttipeangkut . "</select></td>
		</tr>
		<tr><td colspan=9><hr></td></tr>
		<tr><td colspan=9><i><b><font size=3px style=color:red;><b>*</b></font>) Kolom yang wajib terisi.</b></i></td></tr>
		<tr>
			<td colspan=9 align=center><input type=hidden id=method value='insert'>
				<button id=tomboldetail class=mybutton onclick=save()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batal class=mybutton onclick=cleardetail()>" . $_SESSION['lang']['cancel'] . "</button>
				<button class=mybutton onclick=showupload(event)>" . $_SESSION['lang']['upload'] . "</button>
			</td>
		</tr>
	</table></fieldset>";
CLOSE_BOX();
	
echo"<div id=detail style=display:none>";
OPEN_BOX();	
	echo"<fieldset id='tabledetail'><legend>Detail</legend>
			<table style='width:1040px;'>
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
		<div id=loaddatadetail style='width:1040px;'></div>
		
		
		";
CLOSE_BOX();
echo"</div>";
		
echo"</div>";
echo close_body();
?>