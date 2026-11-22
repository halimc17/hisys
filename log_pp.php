<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_pp').'</span>');
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<link rel=stylesheet type='text/css' href='style/generic.css'>
<script language=javascript src='js/generic.js'></script>
<script language=javascript src='js/vhc_detailkmhm.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_pp.js?v=<?php echo time(); ?>" /></script>

<?php
$user_id=$_SESSION['standard']['userid'];
if($user_id=='' or $user_id==0000000000)
{
	echo "Error : You do not have organization code and license to create PR/SR";
	CLOSE_BOX();
	echo close_body();
	exit;
}

##PENCARIAN
$tahunSkrg=date('Y');
$arrTahun=array($tahunSkrg=>$tahunSkrg);
foreach(fetchdata("select distinct year(tanggal) as thn from ".$dbname.".log_prapoht where tanggal>'0000-00-00'") as $val){
	$arrTahun[$val['thn']]=$val['thn'];
}
krsort($arrTahun);
$optTahun="<option value=''>Semua Tahun</option>";
foreach($arrTahun as $thn){
	$optTahun.="<option value='".$thn."'".($thn==$tahunSkrg?" selected":"").">".$thn."</option>";
}

#unit yang punya PR/SR dan boleh diakses
$optUnitCari="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(fetchdata("select distinct unit from ".$dbname.".log_prapoht where unit in (".getOrgDetail(2).") order by unit") as $val){
	$optUnitCari.="<option value='".htmlspecialchars($val['unit'],ENT_QUOTES)."'>".htmlspecialchars($val['unit'])." - ".htmlspecialchars(getNamaOrg($val['unit']))."</option>";
}

$optTipeCari="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(array('PR'=>'Purchase Request (INV)','SR'=>'Service Request (JASA)','CP'=>'Capex Request (ASET)','NR'=>'Non-Inventory Request (NON-INV)') as $k=>$v){
	$optTipeCari.="<option value='".$k."'>".$v."</option>";
}

$optStatusCari="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(array('draft'=>'Belum Diajukan','menunggu'=>'Menunggu Persetujuan','disetujui'=>'Disetujui','ditolak'=>'Ditolak','return'=>'PR/SR Return') as $k=>$v){
	$optStatusCari.="<option value='".$k."'>".$v."</option>";
}

$optProgresCari="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(array('belum'=>'Menunggu Verifikasi Purchasing','alokasi'=>'Dialokasi ke Purchaser','rfq'=>'Perbandingan Harga (RFQ)','sebagian'=>'Sebagian PO','selesai'=>'Selesai PO','tolakitem'=>'Ada Item Ditolak Purchasing') as $k=>$v){
	$optProgresCari.="<option value='".$k."'>".$v."</option>";
}

#pembuat PR/SR (urut menurut nama)
$optDibuatCari="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(fetchdata("select distinct h.dibuat, k.namakaryawan from ".$dbname.".log_prapoht h left join ".$dbname.".datakaryawan k on k.karyawanid=h.dibuat where h.unit in (".getOrgDetail(2).") order by k.namakaryawan") as $val){
	$optDibuatCari.="<option value='".$val['dibuat']."'>".htmlspecialchars($val['namakaryawan']!=''?$val['namakaryawan']:$val['dibuat'])."</option>";
}

$optKelompokCari="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(fetchdata("select distinct kode,kelompok from ".$dbname.".log_5klbarang order by kode asc") as $val){
	$optKelompokCari.="<option value='".htmlspecialchars($val['kode'],ENT_QUOTES)."'>".htmlspecialchars($val['kode']." - ".$val['kelompok'])."</option>";
}

echo "<div id='action_list'>
	<table>
		<tr valign=top>
		<td align=center valign=middle style='width:100px;cursor:pointer;' onclick=displayFormInput()>
		<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	<td align=center valign=middle style='width:100px;cursor:pointer;' onclick=showalllist(0)>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	</td>
			<td>
				<fieldset>
					<legend>".$_SESSION['lang']['find']."</legend>
					<table border=0 cellpadding=1 cellspacing=1>
						<tr>
							<td>Tahun</td>
							<td>:</td>
							<td style='width:150px'><select id=filterTahun class=select2 style='width:100%' onchange='cariData(0)'>".$optTahun."</select></td>
							<td style='padding-left:15px'>No. PR/SR</td>
							<td>:</td>
							<td style='width:170px'><input type=text id=txtsearch maxlength=30 class=myinputtext style='width:100%;box-sizing:border-box' onkeyup=\"enterkey(event,function(){cariData(0)})\"></td>
							<td style='padding-left:15px'>".$_SESSION['lang']['tanggal']."</td>
							<td>:</td>
							<td style='width:110px'><input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false; style='width:100%;box-sizing:border-box' maxlength=10 readonly/></td>
							<td style='padding-left:15px'>".$_SESSION['lang']['unit']."</td>
							<td>:</td>
							<td style='width:200px'><select id=filterUnit class=select2 style='width:100%' onchange='cariData(0)'>".$optUnitCari."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['tipe']."</td>
							<td>:</td>
							<td><select id=filterTipe class=select2 style='width:100%' onchange='cariData(0)'>".$optTipeCari."</select></td>
							<td style='padding-left:15px'>".$_SESSION['lang']['status']."</td>
							<td>:</td>
							<td><select id=filterStatus class=select2 style='width:100%' onchange='cariData(0)'>".$optStatusCari."</select></td>
							<td style='padding-left:15px'>Progres</td>
							<td>:</td>
							<td><select id=filterProgres class=select2 style='width:100%' onchange='cariData(0)'>".$optProgresCari."</select></td>
							<td style='padding-left:15px'>".$_SESSION['lang']['dbuat_oleh']."</td>
							<td>:</td>
							<td><select id=filterDibuat class=select2 style='width:100%' onchange='cariData(0)'>".$optDibuatCari."</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kelompokbarang']."</td>
							<td>:</td>
							<td colspan=4><select id=filterKelompok class=select2 style='width:100%' onchange='cariData(0)'>".$optKelompokCari."</select></td>
							<td colspan=6 style='padding-left:15px'>
								<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>
								<button class=mybutton onclick=exportExcelPP()>".$_SESSION['lang']['excel']."</button>
								<button class=mybutton onclick=exportPdfPP()>PDF</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
				<td valign=top>
					<fieldset id=fsWarnaStatus style='display:flex;flex-direction:column'>
						<legend>Warna Status</legend>
					<div style='flex:1;display:flex;flex-direction:column;justify-content:space-between'>
						<span style='display:block;margin:2px 0;padding:1px 10px;font-size:13px;line-height:18px;border-radius:3px;text-align:center;background:#ffeb9c;color:#9c5700;font-weight:bold' title='Masih draft, belum diajukan untuk persetujuan'>Belum Diajukan</span>
						<span style='display:block;margin:2px 0;padding:1px 10px;font-size:13px;line-height:18px;border-radius:3px;text-align:center;background:#ddebf7;color:#1f4e78;font-weight:bold' title='Sudah diajukan, menunggu keputusan approval'>Menunggu Persetujuan</span>
						<span style='display:block;margin:2px 0;padding:1px 10px;font-size:13px;line-height:18px;border-radius:3px;text-align:center;background:#c6efce;color:#006100;font-weight:bold' title='Sudah disetujui dan diproses purchasing'>Disetujui</span>
						<span style='display:block;margin:2px 0;padding:1px 10px;font-size:13px;line-height:18px;border-radius:3px;text-align:center;background:#ffc7ce;color:#9c0006;font-weight:bold' title='Ditolak oleh approval'>Ditolak</span>
						<span style='display:block;margin:2px 0;padding:1px 10px;font-size:13px;line-height:18px;border-radius:3px;text-align:center;background:#fce4d6;color:#a04000;font-weight:bold' title='Dikembalikan ke pembuat'>PR/SR Return</span>
					</div>
					</fieldset>
				</td>
		</tr>
	</table>
</div>
<script>
$(document).ready(function(){
	$('#action_list .select2').select2({dropdownAutoWidth:true,width:'100%'});
	//kotak filter dan kotak Warna Status dibuat sama tinggi
	var fsAtas = $('#action_list fieldset');
	var tinggiAtas = Math.max(fsAtas.eq(0).outerHeight(), fsAtas.eq(1).outerHeight());
	fsAtas.css({'box-sizing':'border-box','height':tinggiAtas+'px'});
	//daftar pilihan select2 harus tampil di atas popup/dialog
	$('<style>.select2-container--open .select2-dropdown{z-index:100000;}</style>').appendTo('head');
});
</script>";

CLOSE_BOX();

echo "<div id=\"list_pp\">";

OPEN_BOX();

echo"<!--<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>-->
	<!--<img src='images/pdf.jpg' onclick=\"masterPDF('log_prapoht','','','log_print_pdf_pp',event)\" width='20' height='20' />-->
	<!--<img onclick=\"javascript:print()\" style='width: 20px; height: 20px; cursor: pointer;' title='Print Page' src='images/printer.png'>-->
	
	<style>#contain a#seeprog{color:inherit;font-weight:bold}</style>
		<div style='overflow:auto;'>
	<table class='sortable' cellspacing='1' cellpadding=5 border='0' style='width:100%;text-transform:uppercase;'>
		<thead>
		<tr class=rowheader>
			<th align='center'>No.</th>
			<th align='center'>".$_SESSION['lang']['pt']."</th>
			<th align='center'>".$_SESSION['lang']['unit']."</th>
			<th align='center'>No. PR/SR</th>
			<th align='center'>".$_SESSION['lang']['tipe']."</th> 
			<th align='center'>".$_SESSION['lang']['kelompokbarang']."</th> 
			<th align='center'>".$_SESSION['lang']['tanggal']."</th>
			<th align='center'>Chat</th>
			<th align='center'>".$_SESSION['lang']['dbuat_oleh']."</th>
			<th align='center'>".$_SESSION['lang']['status']."</th>
			<th align='center'>Progres</th>
			<th align='center'>Action</th>
		</tr>
		</thead>
		<tbody id='contain'>
			<script>loadData(0)</script>
		</tbody>
	</table>
	</div>
";

CLOSE_BOX(); //2 C
echo"</div>";


echo"<div id='form_pp' style='display:none;'>";
OPEN_BOX();

/* Organisasi */
$optBagian = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(fetchData("select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi) = 4 AND kodeorganisasi IN (".getOrgDetail(2).") order by inti DESC, kodeorganisasi ASC") as $val){
    $optBagian .= "<option value='".htmlspecialchars($val['kodeorganisasi'],ENT_QUOTES)."'>".htmlspecialchars($val['kodeorganisasi']." - ".$val['namaorganisasi'])."</option>";
}

$opttipe="";
$opttipe.="<option value='PR'>Purchase Request (INV)</option>";
$opttipe.="<option value='SR'>Service Request (JASA)</option>";
$opttipe.="<option value='CP'>Capex Request (ASET)</option>";
$opttipe.="<option value='NR'>Non-Inventory Request (NON-INV)</option>";

$optpri="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".log_5prioritas order by kode asc";
$res=fetchData($str);
foreach($res as $val){
    $optpri.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
}

##Tanggal Sekarang
$as=date("Y-m-d");
$as2=date('Y-m-d', strtotime('+7 days', strtotime($as)));

##GET Requester
$nmdeptAll = makeOption($dbname,'sdm_5departemen','kode,nama');
if (!function_exists('ppNamaRequester')) {
	function ppNamaRequester($nama, $bagian, $mapDept)
	{
		$d = isset($mapDept[$bagian]) ? $mapDept[$bagian] : $bagian;
		return htmlspecialchars($nama) . ($d != '' ? ' (' . htmlspecialchars($d) . ')' : '');
	}
}
$optkar="";
$sendiri=false;
$str="select karyawanid,namakaryawan,nik,bagian from ".$dbname.".datakaryawan where lokasitugas='".$unitkerja."' and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$as."') order by namakaryawan asc";
$res=fetchdata($str);
foreach($res as $val){
	if($_SESSION['standard']['userid']==$val['karyawanid']){
		$sendiri=true;
		$optkar.="<option value='".$val['karyawanid']."' selected>".ppNamaRequester($val['namakaryawan'],$val['bagian'],$nmdeptAll)."</option>";
	}else{
		$optkar.="<option value='".$val['karyawanid']."'>".ppNamaRequester($val['namakaryawan'],$val['bagian'],$nmdeptAll)."</option>";
	}
}
#pengguna yang login selalu ada di pilihan, walau tidak termasuk daftar karyawan unit
if($sendiri==false){
	$optkar.="<option value='".$_SESSION['standard']['userid']."' selected>".ppNamaRequester(getKary($_SESSION['standard']['userid']),getKary($_SESSION['standard']['userid'],'bagian'),$nmdeptAll)."</option>";
}
#PPP (Palma): form isian dan List Data dibuat satu tepi kiri dan sama lebar (mengikuti tabel terlebar); PT lain tampilannya tidak diubah
$palmaTampilan=(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP');
$tbLebarPPP=$palmaTampilan?"style='width:100%'":"";
$fsListPPP=$palmaTampilan?"style='border:0;padding:0;margin:8px 0 0 0'":"";
$lgListPPP=$palmaTampilan?"style='padding-left:0;font-weight:bold'":"";
#pembungkus selebar tabel terlebar (bukan selebar layar): form isian dan List Data sama lebar
$wrapAwalPPP=$palmaTampilan?"<div style='display:inline-block;vertical-align:top'>":"";
$wrapAkhirPPP=$palmaTampilan?"</div>":"";
echo"<fieldset>
		<legend>".$_SESSION['lang']['header']."</legend>
		<table cellspacing='1' cellpadding='3' border='0' id='opl' class='urutaninputan'>
			<tr>
				<td class='bintang' style='white-space:nowrap'>".$_SESSION['lang']['namaorganisasi']."</td>
				<td>:</td>
				<td style='width:300px'>
					<select id='kd_bag' class='select2pp' style='width:100%' onchange=\"getrequester()\">".$optBagian."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>:</td>
				<td>
					<select id='tipe' class='select2pp' style='width:100%'>".$opttipe."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>:</td>
				<td>
					<input type='text' id='nopp' class='myinputtext' disabled='disabled' style='width:100%;box-sizing:border-box' />
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext style='width:100%;box-sizing:border-box' id=tgl_pp onkeypress='return tanpa_kutip(event)' onmousemove='setCalendar(this.id)'  value='".tanggalnormal($as)."'>
				</td>
			</tr>
			<tr>
				<td class='bintang'>Requester</td>
				<td>:</td>
				<td>
					<select id=requester class='select2pp' style='width:100%'>".$optkar."</select>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<input type='hidden' id='method' value='insert' />
					<input type='hidden' id='user_id' name='user_id' value='".$_SESSION['standard']['userid']."' />
					<button class=mybutton id='dtl_pem' onclick=\"get_isi()\">".$_SESSION['lang']['save']."</button>
				</td>
			</tr>
		</table>
	</fieldset>
	<script>
	$(document).ready(function(){
		//select2 untuk form PR/SR; lebar mengikuti kolom, daftar tampil di atas dialog
		$('#form_pp .select2pp').select2({dropdownAutoWidth:true,width:'100%'});
		//tinggi kolom isian disamakan dengan select2
		$('<style>#opl input.myinputtext{height:26px;box-sizing:border-box}</style>').appendTo('head');
	});
	</script>

<fieldset>
<legend>".$_SESSION['lang']['detail']."</legend>

<div id='detailTable' style='display:none;'>
<!-- content detail pp-->
</div>

<div id='tmbl_all'>
".$wrapAwalPPP."<b>No. PR/SR : <label id=detail_kode></label></b><p />
<table class='sortable' cellspacing='1' border='0' ".$tbLebarPPP.">
	<thead>
	<tr class=rowheader>
		<th align='center' colspan=2 class='bintang'>".$_SESSION['lang']['kodebarang']."</th>
		<th align='center' width=50px  class='bintang'>".$_SESSION['lang']['jmlhDiminta']."</th>
		<th align='center' class='bintang'>Prioritas</th>
		<th align='center'>".$_SESSION['lang']['tanggalSdt']."</th>
		<th align='center'>".$_SESSION['lang']['kodevhc']."</th>
		<th align='center'>KM/HM</th>		
		<th align='center' class='bintang'>".$_SESSION['lang']['keterangan']."</th>
		<th align='center'>".$_SESSION['lang']['cdproject']."</th>
		<th align='center'>Action</th>
	</tr>
	</thead>
	<tbody id='detailBody'>
	<tr class='rowcontent'>
		<td style='text-align:center;vertical-align:top'>
			<input type=text class=myinputtext style='width:80px' id=kd_brg disabled>
		</td>	
		<td style='text-align:center;vertical-align:top;width:30px'>	
			<img id='imgbarang' src='images/onebit_02.png' style='position:relative;top:3px;padding-right:5px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchBrg(event)\";>
		</td>
		<td style='text-align:center;vertical-align:top'>
			<input type=number min='1' class=myinputtextnumber style='width:80px' id=jmlhDiminta onkeypress='return angka_doang(event)'>
		</td>
		<td style='text-align:center;vertical-align:top'>
			<select id='prioritas' class='select2pp' style='width:190px'>".$optpri."</select>
		</td>
		<td style='text-align:center;vertical-align:top'>
			<input type=text class=myinputtext style='width:70px;text-align:center' id=tgl_sdt onkeypress='return tanpa_kutip(event)' onmousemove='setCalendar(this.id)' readonly=readonly value='".tanggalnormal($as2)."'>
		</td>
		<td style='text-align:center;vertical-align:top' nowrap>
			<input type=text class=myinputtext style='width:100px' id=kd_vhc disabled>
			<img src='images/onebit_02.png' style='position:relative;top:3px;padding-right:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchVhc('".$_SESSION['lang']['findvhc']."','Find : &nbsp;<input type=text class=myinputtext id=no_vhc onkeypress=enterkey(event,findVhc)><button class=mybutton onclick=findVhc()>Find</button><div style=clear:both></div><div class=table-scroll id=container></div>',event)\";>
			<img id='showdocument' src='images/book_icon.gif' style='position:relative;top:3px;padding-right:5px;display:none' class=zImgBtn title=".$_SESSION['lang']['dokumen']." onclick=\"showdocsparepart();\">
		</td>
		<td style='text-align:center;vertical-align:top'>
			<input type=number min='0' class=myinputtextnumber style='width:80px' id=kmhm onkeypress='return angka_doang(event)'>
		</td> 
		<td style='text-align:center;vertical-align:top'>
			<input type=text class=myinputtext style='width:150px' id=ket onkeypress='return tanpa_kutip(event)'>
		</td>
		<td style='text-align:center;vertical-align:top' nowrap> 
			<input type=text class=myinputtext style='width:100px' id=kd_project disabled>
			<img src='images/onebit_02.png' style='position:relative;top:3px;padding-right:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchVhc('".@$_SESSION['lang']['findproject']."','<fieldset>Find : &nbsp;<input type=text class=myinputtext id=no_project onkeypress=enterkey(event,findproject)><button class=mybutton onclick=findproject()>Find</button><br/> </fieldset><div id=container></div>',event)\";>
			<img id='showdocument' src='images/book_icon.gif' style='position:relative;top:3px;padding-right:5px;display:none' class=zImgBtn title=".$_SESSION['lang']['dokumen']." onclick=\"showdocsparepart();\">
		</td>

		<td style='text-align:center;vertical-align:top' nowrap>
			<img id=detail_add title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/>
			<img src=images/clear.png class=zImgBtn  title='Clear' onclick=\"cleardt();\" >
		</td>
	</tr>
	<tr class='rowcontent' id='infodt' style='display:none'>
		<td colspan=11>
			<table>
				<tr>
					<td>".$_SESSION['lang']['namabarang']."</td>
					<td>:</td>
					<td colspan='2'>
						<label id='lblnamabarang'></label>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['satuan']."</td>
					<td>:</td>
					<td colspan='2'>
						<label id='lblsatuan'></label>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['stok']."</td>
					<td>:</td>
					<td colspan='2' style='text-align:right'>
						<label id='lblstok'></label>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['hargasatuan']."</td>
					<td>:</td>
					<td>Rp.</td>
					<td style='text-align:right'>
						<label id='lblhargasat'></label>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['realisasi']."</td>
					<td>:</td>
					<td>Rp.</td>
					<td style='text-align:right'>
						<label id='lblrealisasi'></label>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['budget']."</td>
					<td>:</td>
					<td>Rp.</td>
					<td style='text-align:right'>
						<label id='lblbudget'></label>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['budget']."</td>
					<td>:</td>
					<td style='text-align:left' colspan=2>
						<label id='lblakunbgt'></label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	</tbody>
	<tr>
		<td style='text-align:right;vertical-align:top' colspan=11>
			<input type=hidden id='methoddt' value='insertdt'>
			<button class=mybutton onclick='showupload(event)'>Upload Files</button>
			<button class=mybutton onclick='cekdokumen()'>".$_SESSION['lang']['selesai']."</button>
			<button id='dtBatal' class=mybutton onclick='reset_data()'>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>

<fieldset ".$fsListPPP.">
	<legend ".$lgListPPP.">".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' cellpadding='3' border='0' ".$tbLebarPPP.">
					<thead>
					<tr class=rowheader>
						<th align='center'>No.</th>
						<th align='center'>".$_SESSION['lang']['kodebarang']."</th>
						<th align='center'>".$_SESSION['lang']['namabarang']."</th> 
						<th align='center'>".$_SESSION['lang']['satuan']."</th> 
						<th align='center'>".str_replace(" ","<br>",$_SESSION['lang']['jmlhDiminta'])."</th>
						<th align='center'>Prioritas</th>
						<th align='center'>".str_replace(" ","<br>",$_SESSION['lang']['tanggalSdt'])."</th>
						<th align='center'>".str_replace(" ","<br>",$_SESSION['lang']['kodevhc'])."</th>
						<th align='center'>KM/HM</th>
						<th align='center'>".$_SESSION['lang']['stock']."</th>
						<th align='center'>".str_replace(" ","<br>",$_SESSION['lang']['hargasatuan'])."</th>
						<th align='center'>".$_SESSION['lang']['realisasi']."</th>
						<th align='center'>".$_SESSION['lang']['budget']."</th>
						<th align='center'>".$_SESSION['lang']['keterangan']."</th>
						<th align='center'>".str_replace(" ","<br>",$_SESSION['lang']['cdproject'])."</th>
						<th align='center' colspan=2>Action</th>
					</tr>
					</thead>
					<tbody id='listprsr'>
					</tbody>
				</table>
			</fieldset><fieldset ".$fsListPPP."><legend ".$lgListPPP.">".$_SESSION['lang']['list']." File Upload</legend>
				<table class='sortable' cellspacing='1' border='0' cellpadding=5 ".$tbLebarPPP.">
					<thead>
					<tr class=rowheader>
						<th align='center' width=50px>No.</th>
						<th align='center' width=50px>File Type</th>
						<th align='center' width=100px>Kriteria</th>
						<th align='center'>Filename</th>
						<th align='center' width=55px>Action</th>
					</tr>
					</thead>
					<tbody id='listfilestop'>
					</tbody>
				</table>
</fieldset> 
".$wrapAkhirPPP."
</div>

</fieldset>";

CLOSE_BOX();
echo "</div>";

##div persetujuan##
echo"<div id='persetujuan' style='display:none;'>";
OPEN_BOX();
echo"<div id='persetujuandata'></div>";

CLOSE_BOX();
echo "</div>";
echo close_body();

?>