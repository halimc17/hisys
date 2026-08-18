<?
error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
$frm = array('','','');
?>


<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zSearch.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script type="text/javascript" src="js/vhc_pekerjaan.js?v=<?php echo time(); ?>"></script>
<script>
dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";

$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
	
</script>
<?php
global $kd_org;

$soptOrg='';    
$sorg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KEBUN','KANWIL','PABRIK') and kodeorganisasi='".substr($_SESSION['empl']['lokasitugas'],0,4)."' order by namaorganisasi";
$soptOrg='';
$res=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rorg=$res->fetch()){
	$kd_org=$rorg['kodeorganisasi'];
	$soptOrg.="<option '".($rorg['kodeorganisasi']==$kd_org?'selected=selected':'')."' value=".$rorg['kodeorganisasi']." >".$rorg['namaorganisasi']."</option>";
}
//----tab pertama...
$sjvch="select jenisvhc,namajenisvhc from ".$dbname.".vhc_5jenisvhc order by namajenisvhc";
$optJnsvhc='';
$res=$owlPDO->query($sjvch) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rjvch=$res->fetch()){
	$optJnsvhc.="<option value=".$rjvch['jenisvhc'].">".$rjvch['jenisvhc']."-".$rjvch['namajenisvhc']."</option>";
}

$strak="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe = 'TRAKSI' order by induk, namaorganisasi ";
$optTraksi='';
$res=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rtrak=$res->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$rtrak['kodeorganisasi']."'");
	$d=$induk[$rtrak['kodeorganisasi']];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optTraksi.="<optgroup label='".$nmorg[$d]."'>";
	}
	
	if(substr($rtrak['kodeorganisasi'],0,4)==$_SESSION['empl']['lokasitugas']){
		$optTraksi.="<option value=".$rtrak['kodeorganisasi']." selected>".$rtrak['kodeorganisasi']."-".$rtrak['namaorganisasi']."</option>";
	}else{		
		$optTraksi.="<option value=".$rtrak['kodeorganisasi'].">".$rtrak['kodeorganisasi']."-".$rtrak['namaorganisasi']."</option>";
	}
	$n=$d;
	if($d!=$n){
		$optTraksi.="</optgroup>";
	}
}

$arrOpt=array("KM","HM","M3");
$optSatuanvhc='';
foreach($arrOpt as $brs => $isi){
	$optSatuanvhc.="<option value=".$isi.">".$isi."</option>";
}

$optJnsBBMvhc='';
$optJnsBBMvhc="<option value=''></option>";
$where=" `kelompokbarang` in ('351','312')";
$sbrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where ".$where."";
$res=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rbrg=$res->fetch()){
	if($rbrg['kodebarang']=='351010003'){
		$optJnsBBMvhc.="<option value='".$rbrg['kodebarang']."' selected>".$rbrg['kodebarang']."-".$rbrg['namabarang']."</option>";
	}else{
		$optJnsBBMvhc.="<option value=".$rbrg['kodebarang'].">".$rbrg['kodebarang']."-".$rbrg['namabarang']."</option>";
	}
}

$arrPremi=array("Non Premi","Premi");
$optStatPremi='';
foreach($arrPremi as $brs => $isi){
	$optStatPremi.="<option value=".$brs.">".$isi."</option>";
}

$lksiTgs=substr($_SESSION['empl']['lokasitugas'],0,4);
$optper='';
for($x=0;$x<=3;$x++){
	$dt=mktime(0,0,0,0,15,date('Y')+$x);
	$optper.="<option value=".date("Y",$dt).">".date("Y",$dt)."</option>";
}

$optOrg2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg2="select kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
$res=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg2=$res->fetch()){
	$optOrg2.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['kodeorganisasi']."</option>";
}

$optStatusLst="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrStatus=array("0"=>"Belum Posting","1"=>"Sudah diposting");
foreach($arrStatus as $lstStatus=>$vwStatus){
    $optStatusLst.="<option value='".$lstStatus."'>".$vwStatus."</option>";
}
//<button class=mybutton id=create_new name=create_new onclick=createNew() >".$_SESSION['lang']['new']."</button>

OPEN_BOX('','<span class=judul>'.getMenu('vhc_pekerjaan').'</span>');
$frm[0].="<fieldset><legend>".$_SESSION['lang']['header']."</legend>";
$frm[0].="<table cellspacing=1 border=0><tr><td><table cellspacing=1 border=0>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
			<td><select id=KbnId name=KbnId onchange=\"createNew()\" onblur=getkodekend(); style=width:72px>".$optOrg2."</select>
				<input type=text id=no_trans name=no_trans disabled=disabled class=myinputtext style=width:140px; />
			</td>
		</tr>
		<!--<tr><td>".$_SESSION['lang']['thnKontrak']." </td><td>:</td>
			<td><select id=thnKntrk name=thnKntrk style='width:150px;' onchange=\"getKntrk('','')\"><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optper."</select> </td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['NoKontrak']."</td><td>:</td>
			<td><select id=noKntrk name=noKntrk style='width:150px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['statPremi']."</td><td>:</td>
			<td><select id=premiStat name=premiStat style=width:150px;>".$optStatPremi."</select></td>
		</tr>-->
		<tr>
			<td>".$_SESSION['lang']['kodetraksi']."</td><td>:</td>
			<td><select id=kodetraksi name=kodetraksi style=width:220px; onchange=\"getkodekend('')\"><option value=>".$_SESSION['lang']['pilihdata']."</option>".$optTraksi."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jenisvch']."</td><td>:</td>
			<td><select id=jns_vhc name=jns_vhc style=width:220px; onchange=\"get_kd('')\"><option value=>".$_SESSION['lang']['pilihdata']."</option>".$optJnsvhc."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodevhc']."</td><td>:</td>
			<td><select id=kde_vhc name=kde_vhc style=width:220px;><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td><td>:</td>
			<td><input type=text class=myinputtext id=tgl_pekerjaan name=tgl_pekerjaan onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:125px; readonly/></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['vhc_jenis_bbm']."</td><td>:</td>
			<td><select id=jns_bbm name=jns_bbm style=width:130px;>".$optJnsBBMvhc."</select>Jlh :<input type=text class=myinputtextnumber id=jmlh_bbm name=jmlh_bbm maxlength=60 value=0 onkeypress=\"return angka_doang(event);\" style=width:59px; /></td>
		</tr>
		<!--<tr>
			<td>".$_SESSION['lang']['vhc_jumlah_bbm']."</td><td>:</td>
			<td><input type=text class=myinputtextnumber id=jmlh_bbm name=jmlh_bbm maxlength=60 onkeypress=\"return angka_doang(event);\" style=width:150px; value=0/></td>
		</tr>-->
		
		<tr>
			<td><td>
			<td>
				<button class=mybutton id=save_kepala name=save_kepala onclick=save_header()  disabled >".$_SESSION['lang']['save']."</button>
				<button class=mybutton id=cancel_kepala name=cancel_kepala onclick=cancel_kepala_form() disabled >".$_SESSION['lang']['cancel']."</button>
				<button class=mybutton id=done_entry name=done_entry onclick=doneEntry() disabled >".$_SESSION['lang']['done']."</button>
				<input type=hidden id=proses name=proses value=insert_header >	
			</td>
		</tr>
	</table>
</td>
<td width=50px></td>
<td style='vertical-align:top'>";
		$frm[0].="Info :<br>Jika ingin mengedit tab <b>Detail Pekerjaan, Detail Operator</b><br>Click edit kemudian click <b>Simpan</b> pada tab <b>Header</b>";
		$frm[0].="</td></tr></table></fieldset>";
		
		$frm[0].="<div style=clear:both><hr></div>
		<fieldset  style=float:left><legend>".$_SESSION['lang']['find']." Data</legend>
			<table cellspacing=\"1\" border=\"0\"><tr>
				<td>".$_SESSION['lang']['notransaksi']." </td>
				<td><input type=\"text\" id='txtCari' onkeypress='return enter(event);' name='txtCari' style='width:130px' class=myinputtext /></td>
				<td>".$_SESSION['lang']['tanggal']." <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 readonly/></td>
				<td>".$_SESSION['lang']['kodevhc']." <input type=text class=myinputtext id=kodevhc_cari onkeypress='return enter(event);' size=10/></td>
				<td>".$_SESSION['lang']['status']." <select id=statusInputan>".$optStatusLst."</select></td>
				<td><button class=mybutton id=cariTransaksi name=cariTransaksi onclick=cariDataTransaksi()  >".$_SESSION['lang']['find']."</button>
				</td>
				<td><button class=mybutton  onclick=batalcariDataTransaksi()  >".$_SESSION['lang']['cancel']."</button>
		</td></tr></table></fieldset><div style=clear:both></div>
		<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		
		<table cellspacing=1 border=0 class=sortable cellpadding=5>
				<thead>
					<tr class=\"rowheader\">
						<th align=center style='width:50px'>No.</td>
						<th align=center>".$_SESSION['lang']['notransaksi']."</th>
						<th align=center>".$_SESSION['lang']['jenisvch']."</th>
						<th align=center>".$_SESSION['lang']['kodevhc']."</th>
						<th align=center>".$_SESSION['lang']['nopol']."</th>
						<th align=center>".$_SESSION['lang']['detail']."</th>
						<th align=center>".$_SESSION['lang']['tanggal']."</th>
						<th align=center>".$_SESSION['lang']['vhc_jenis_bbm']."</th>
						<th align=center style='width:40px'>".$_SESSION['lang']['vhc_jumlah_bbm']."</th>
						<th align=center style='width:60px' colspan=3>Action</th>
					</tr>
				</thead>
				<tbody id='contain'></tbody>
				<tfoot id='containfoot'></tfoot>
			</table>
		</fieldset>
	</fieldset>
<script>cariDataTransaksi()</script>";


//Detail Pekerjaan
$wh="";
if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
	$wh=" and (substr(noakun,1,3) in ('126','128','621','611') or substr(noakun,1,1) in ('7') or kelompok='EXT')";
}elseif($_SESSION['empl']['tipelokasitugas']=='PABRIK'){
	$wh=" and (substr(noakun,1,2) in ('63') or substr(noakun,1,1) in ('7') or kelompok='EXT')";
}elseif($_SESSION['empl']['tipelokasitugas']=='BULKING'){
	$wh=" and substr(noakun,1,2) in ('81')";
}else{
	$wh=" and (substr(noakun,1,2) in ('82') or noakun in ('7112001','7112004','7112001'))";
}

#PABRIK => %63%, 7%
#KANWIL => 82%
#RND => 82%
#TC => 82%
#BULKING => 81%

$sjnskrj="select * from ".$dbname.".vhc_kegiatan where tipe ='traksi' ".$wh." order by noakun asc";
$optJnsKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($sjnskrj) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rjnskrj=$res->fetch()){
	$d=substr($rjnskrj['kodekegiatan'],0,5);
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
		$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
	}
	$optJnsKerja.="<option value=".$rjnskrj['kodekegiatan'].">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
	$n=$d;
	if($d!=$n){
		$optJnsKerja.="</optgroup>";
	}
}



$optdept="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sdept="select * from ".$dbname.".sdm_5departemen where aktif ='1' order by nama asc";
$optdept="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($sdept) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rdept=$res->fetch()){
	$optdept.="<option value=".$rdept['kode'].">".$rdept['kode']." - ".$rdept['nama']."</option>";

}



$slokTgs="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi 
IN (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$_SESSION['empl']['regional']."' )
AND `tipe` NOT IN ('PT', 'BLOK', 'STATION', 'STENGINE','AFDELING')";

$optLokTugas='';
$res=$owlPDO->query($slokTgs) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rlokTgs=$res->fetch()){
	if(substr($rlokTgs['kodeorganisasi'],0,4)==$_SESSION['empl']['lokasitugas']){
		$optLokTugas.="<option value=".$rlokTgs['kodeorganisasi']." selected>".$rlokTgs['kodeorganisasi']." - ".$rlokTgs['namaorganisasi']."</option>";
	}else{
		$optLokTugas.="<option value=".$rlokTgs['kodeorganisasi'].">".$rlokTgs['kodeorganisasi']." - ".$rlokTgs['namaorganisasi']."</option>";
	}	
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1 and b.tipe in ('SUPPLIERTBS') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
}
		
$sOrg="select * from ".$dbname.".kebun_5namakud where status='1' "; 	
$rOrg=fetchData($sOrg);			
foreach($rOrg as $row=>$lsDt){
	$optLokTugas.="<option value='".$lsDt['kodesupplier']."'>".$nmsupplier[$lsDt['kodesupplier']]."</option>";					                    
}	


$frm[1].="<fieldset><legend>".$_SESSION['lang']['vhc_detail_pekerjaan']."</legend>";
$frm[1].="<table cellspacing=1 border=0>

<tr>
	<td>".$_SESSION['lang']['notransaksi']."</td>
	<td>:</td>
	<td colspan=22><input type=text id=no_trans_pekerjaan name=no_trans_pekerjaan disabled=disabled class=myinputtext style=width:228px; /></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
	<td>:</td>
	<td colspan=22><select class=select2 id=jns_kerja name=jns_kerja onchange=getSatuan(this.value) style=width:232px;>".$optJnsKerja."</select><input type=hidden name=old_jnskerja id=old_jnskerja />
	<img id='jns_kerja' onclick=z.elSearch('jns_kerja',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
	</td>
</tr>

<tr>
	<td>".$_SESSION['lang']['lokasi']."</td>
	<td>:</td>
	<td colspan=22><select  class=select2 id=lokasi_kerja name=lokasi_kerja  style=width:232px; onchange=\"getBlok('','')\"><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optLokTugas."</select> <input type=hidden name=old_lokkerja id=old_lokkerja /></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['blok']."</td>
	<td>:</td>
	<td colspan=22><select id=blok  class=select2 name=blok style=width:232px; ><option value=''>".$_SESSION['lang']['pilihdata']."</option></select>&nbsp;<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
	<td>*Jika pekerjaan dilakukan di Kebun (Obligatory if activity location on estate)<td>
	<td> <input type=hidden name=old_blok id=old_blok /></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['department']."</td>
	<td>:</td>
	<td colspan=22><select class=select2 id=dept name=dept style=width:232px;>".$optdept."
	<img id='dept' onclick=z.elSearch('dept',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
	</td>
</tr>



<tr style='display:none'>
	<td>".$_SESSION['lang']['segment']."</td>
	<td>:</td>
	<td colspan=500><input type=hidden name=oldSegment id=oldSegment />".makeElement('kodesegment','searchSegment')."</td>
</tr>


<tr>
	<td>".$_SESSION['lang']['jumlahrit']."</td>
	<td>:</td>
	<td><input type=text class=myinputtextnumber id=jmlh_rit name=jmlh_rit maxlength=6 onclick=\"this.select();\" onkeypress=\"return angka_doang(event);\" style=width:85px; /></td>
	
	<td>".$_SESSION['lang']['prestasi']."</td>
	<td>:</td>
	<td><input type=text class=myinputtextnumber id=brt_muatan name=brt_muatan maxlength=6 onkeypress=\"return angka_doang(event);\" onclick=\"this.select();\" style=width:80px; />&nbsp;<span id='satuan'></span>
	<input hidden type=text class=myinputtextnumber id=oldbrt_muatan name=oldbrt_muatan maxlength=6 onkeypress=\"return angka_doang(event);\" style=width:80px; />&nbsp;<span id='satuan'>
	</td>
</tr>


<tr>
	<td>".$_SESSION['lang']['vhc_kmhm_awal']."</td>
	<td>:</td>
	<td><input type=text onkeyup=getjumlah('awal'); class=myinputtextnumber id=kmhm_awal name=kmhm_awal maxlength=8 onkeypress=\"return angka_doang(event);\" style=width:85px; /></td>

	<td>".$_SESSION['lang']['akhir']."</td>
	<td>:</td>
	<td><input type=text onkeyup=getjumlah('akhir'); class=myinputtextnumber id=kmhm_akhir name=kmhm_akhir maxlength=8  onkeypress=\"return angka_doang(event);\" onclick=\"this.select();\" style=width:80px; /></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['satuan']."</td>
	<td>:</td>
	<td><select id=stn name=stn style=width:89px;>".$optSatuanvhc."</select></td>
	
	<td>".$_SESSION['lang']['jumlah']."</td>
	<td>:</td>
	<td><input class=myinputtextnumber onkeyup=getjumlah('jumlah'); id=jlhhm name=jlhhm style=width:80px;></td>

	<td style='display:none'>".$_SESSION['lang']['biaya']." Rp</td>
	<td style='display:none'>:</td>
	<td style='display:none'><input type=text class=myinputtextnumber id=biaya name=biaya maxlength=45 onkeypress=\"return angka_doang(event);\" style=width:80px; /></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['keterangan']."</td>
	<td>:</td>
	<td colspan=22><input type=text class=myinputtext id=ket name=ket onkeypress=\"return tanpa_kutip(event);\" style=width:228px; /></td>
</tr>


<tr>
	<td><td><td colspan=6>	
		<button class=mybutton onclick=save_pekerjaan() >".$_SESSION['lang']['save']."</button>
		<button class=mybutton onclick=bersih_form_pekerjaan() >".$_SESSION['lang']['cancel']."</button>
		<button class=mybutton title=\"Refresh Data Tersimpan\" onclick=load_data_pekerjaan() >Refresh</button>
		<input type=hidden id=proses_pekerjaan name=proses_pekerjaan value=insert_pekerjaan />

</table>";

$frm[1].="</fieldset>";
$frm[1].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend><table cellpadding=5 cellspacing=1 border=0  class=sortable>
		<thead>
		<tr class=\"rowheader\">
		<th align=center>No.</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</th>
		<th align=center>".$_SESSION['lang']['alokasibiaya']."</th>
		<th align=center>".$_SESSION['lang']['department']."</th>
		<th align=center style='display:none'>".$_SESSION['lang']['segment']."</th>
		<th align=center>".$_SESSION['lang']['jumlahrit']."</th>
		<th align=center>".$_SESSION['lang']['prestasi']."</th>
        <th align=center>".$_SESSION['lang']['vhc_kmhm_awal']."</th>
		<th align=center>".$_SESSION['lang']['vhc_kmhm_akhir']."</th>
		<th align=center>".$_SESSION['lang']['jumlah']."</th>
		<th align=center>".$_SESSION['lang']['satuan']."</th>
		<th align=center style='display:none'>".$_SESSION['lang']['biaya']." (Rp.)</th>
		<th align=center>".$_SESSION['lang']['keterangan']."</th>
		<th align=center colspan=2>Action</th>
		</tr></thead><tbody id=containPekerja>
		";
$frm[1].="</tbody></table></fieldset>";

//karyawan
$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrPos=array("Operator","Helper","Sopir");
$optPosition='';
foreach($arrPos as $brs => $isi){
	$optPosition.="<option value=".$brs.">".$isi."</option>";
}
$frm[2].="<fieldset><legend>".$_SESSION['lang']['vhc_detail_operator']."</legend>";
$frm[2].="<table cellspacing=1 border=0>

<tr>
	<td>".$_SESSION['lang']['notransaksi']."</td>
	<td>:</td>
	<td><input type=text id=no_trans_opt name=no_trans_opt disabled=disabled class=myinputtext style=width:150px; /></td>
	
</tr>

<tr>
	<td>".$_SESSION['lang']['namakaryawan']."</td>
	<td>:</td>
	<td><select class=select2 id=kode_karyawan name=kode_karyawan style=width:154px; onchange=getUmr();>".$optKary."</select></select>
	<img id='kode_karyawan' onclick=z.elSearch('kode_karyawan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
	</td>
</tr>

<tr>
	<td>".$_SESSION['lang']['vhc_posisi']."</td>
	<td>:</td>
	<td><select id=posisi name=posisi onchange=getPremi(); style=width:154px;>".$optPosition."</select> &nbsp; => Pengisian <b>Operator</b> dan <b>Helper</b> mempengaruhi <b>nilai Premi</b></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['upahkerja']."</td>
	<td>:</td>
	<td><input type=text id=uphOprt name=uphOprt  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' /> &nbsp; => Harus di isi untuk karyawan internal (Obligatory if internal operator used)</td>
</tr>

<tr>
	<td>".$_SESSION['lang']['premi']."</td>
	<td>:</td>
	<td><input type=text id=prmiOprt onfocus=getPremi();  name=prmiOprt class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0 /> &nbsp; => Jika Hari Libur, maka nilai Premi = (Upah + Premi)</td>
</tr>

<tr hidden>
	<td>".$_SESSION['lang']['rupiahpenalty']."</td>
	<td>:</td>
	<td><input type=text id=pnltyOprt name=pnltyOprt class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value=0 /></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['keterangan']."</td>
	<td>:</td>
	<td>
		<input type=text class=myinputtext id=ketOprt name=ket maxlength=45 onkeypress=\"return tanpa_kutip(event);\" style=width:150px; />
	</td>
</tr>

<tr><td><td><td>
	<button class=mybutton onclick=save_operator() >".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=clear_operator() >".$_SESSION['lang']['cancel']."</button>
	<button class=mybutton id=tomboldetailpremi onclick=detailpremi() >".$_SESSION['lang']['detail']."</button>
	<input type=hidden name=prosesOpt id=prosesOpt value=insert_operator />

</td></tr>
</table>";

$frm[2].="</fieldset>";
$frm[2].="<fieldset id=contdetailpremi style=display:none>	
	<legend>".$_SESSION['lang']['detail']."</legend>
	<table cellspacing=1 border=0 class=sortable cellpadding=5>
		<thead>
		<tr class=\"rowheader\">
		<th align=center rowspan=2>No.</th>
		<th align=center rowspan=2>".$_SESSION['lang']['kegiatan']."</th>
		<th align=center rowspan=2>".$_SESSION['lang']['satuan']."</th>
		<th align=center rowspan=2>".$_SESSION['lang']['prestasi']."</th>
		<th align=center rowspan=2>HM/KM</th>
		<th align=center colspan=2>Rp / Sat</th>
		<th align=center colspan=2>Rupiah</th>
		<th align=center rowspan=2>Total Rupiah</th>
		</tr><tr class=\"rowheader\">
		<th align=center >Pres</th>
		<th align=center >HM/KM</th>
		<th align=center >Pres</th>
		<th align=center >HM/KM</th>
		</tr></thead><tbody id=containDetailOperator>
		";
$frm[2].="</tbody></table></fieldset>";

$frm[2].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend><table cellspacing=1 border=0 class=sortable cellpadding=5>
		<thead>
		<tr class=\"rowheader\">
		<th align=center >No.</th>
		<th align=center >".$_SESSION['lang']['notransaksi']."</th>
		<th align=center >".$_SESSION['lang']['namakaryawan']."</th>
		<th align=center >".$_SESSION['lang']['vhc_posisi']."</th>
		<th align=center >".$_SESSION['lang']['upahkerja']."</th>
		<th align=center >".$_SESSION['lang']['upahpremi']."</th>
		<th align=center >".$_SESSION['lang']['rupiahpenalty']."</th>
		<th align=center >".$_SESSION['lang']['keterangan']."</th>
		<th align=center >Action</th>
		</tr></thead><tbody id=containOperator>
		<script>//load_data_operator()</script>
		";
$frm[2].="</tbody></table></fieldset>";

//========================
$hfrm[0]=$_SESSION['lang']['header'];
$hfrm[1]=$_SESSION['lang']['vhc_detail_pekerjaan'];
$hfrm[2]=$_SESSION['lang']['vhc_detail_operator'];
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,'100%');
//===============================================	
CLOSE_BOX();
echo close_body();
?>