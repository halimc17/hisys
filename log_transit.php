<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_transit').'</span>');
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<script language=javascript src='js/vhc_detailkmhm.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_transit.js?v=1" /></script>

<?php
##PENCARIAN
$_SESSION['transitpo']=array();
echo "<div id='action_list'>
	<table>
		<tr valign=middle>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
		<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	</td>
			<td>
				<fieldset>
					<legend>".$_SESSION['lang']['find']."</legend>
					<table>
						<tr>
							<td>".$_SESSION['lang']['notransaksi']."</td>
							<td><input type=text id=crnotransaksi size=25 maxlength=30 class=myinputtext></td>
							
							<td style='padding-left:20px'>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text id=crtanggal size=10 maxlength=10 class=myinputtext onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; readonly></td>
							
							<td>
								<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table> 
</div>";

CLOSE_BOX();

###############################
####### BEGIN LIST DATA #######
###############################
echo "<div id='list_transit'>";

OPEN_BOX();

echo"<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div style='overflow:auto;'>
	<table class='sortable' cellspacing='1' cellpadding=3 border='0' style='width:100%'>
		<thead>
		<tr class=rowheader>
			<td align='center'>No.</td>
			<td align='center'>".$_SESSION['lang']['notransaksi']."</td>
			<td align='center'>".$_SESSION['lang']['pt']."</td>
			<td align='center'>".$_SESSION['lang']['unit']."</td>
			<td align='center'>".$_SESSION['lang']['nopo']."</td> 
			<td align='center'>".$_SESSION['lang']['tanggal']."</td> 
			<td align='center'>".$_SESSION['lang']['dibuat']."</td>
			<td align='center'>".$_SESSION['lang']['posted']."</td> 
			<td align='center'>Action</td>
		</tr>
		</thead>
		<tbody id='contain'>
			<script>loaddata(0)</script>
		</tbody>
	 <tfoot>
	 </tfoot>
	 </table>
	</div>
</fieldset>";

CLOSE_BOX();
echo"</div>";
#############################
####### END LIST DATA #######
#############################

##########################
####### BEGIN FORM #######
##########################
echo"<div id='form_transit' style='display:none;'>";
OPEN_BOX();

## GET UNIT
$optunit='';
// $arrorgdet = getOrgDetail(1);
// foreach($arrorgdet as $key=>$val){
	// $subro = substr($key,2,2);
	// if($subro=='RO'){
		// $optunit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	// }
// }


// $str="select * from ".$dbname.".organisasi where tipe in ('KEBUN','KANWIL','PABRIK')";
// $str="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
// $res=$owlPDO->query($str);
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
	// $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";		
// }

// $optunit='';
// $arrorgdet = getOrgDetail(1);
// $no=0;
// foreach($arrorgdet as $key=>$val){
// 	$no++;
// 	if($no==1){
// 		$unitkerja = $key;
// 	}
// 	$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";	
// }


$optunit='';
// $str="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$optunit.="<option value=''>Pilih Data</option>";		

$str="select * from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL') and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";		
}

echo"<fieldset>
	<legend>".$_SESSION['lang']['header']."</legend>
	<table cellspacing='1' cellpadding=2 border='0' id='opl'>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td>
				<input type='text' id='notransaksi' class='myinputtext' disabled='disabled' style='width:250px;' /> <font color=red>* Otomatis</td>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select id='unit' style='width:255px;' onchange='cleardt()'>".$optunit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggal' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:250px;text-align:center' />
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top'>".$_SESSION['lang']['listpo']."</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<button class=mybutton id='addpo' onclick=\"addpo(event)\">".$_SESSION['lang']['find']."</button>
				<table class=sortable cellspacing=1 cellpadding=2 border=0>
					<thead>
					<tr class=rowheader style='text-align:center'>
						<td>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['nopo']."</td>
						<td>".$_SESSION['lang']['nopp']."</td>
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>".$_SESSION['lang']['satuan']."</td>
						<td>".$_SESSION['lang']['jumlah']." PO</td>
						<td>".$_SESSION['lang']['jumlah']." Realisasi</td>
						<td>".$_SESSION['lang']['jumlahditerima']."</td>
					</tr>
					</thead>
					<tbody id='listpo'>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top'>".$_SESSION['lang']['keterangan']."</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<textarea id='keterangan' style='width:235px;' onkeypress='return tanpa_kutip(event);'></textarea>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type='hidden' id='method' value='insert' />
				<input type='hidden' id='user_id' name='user_id' value='".$_SESSION['standard']['userid']."' />
				<button class=mybutton id='dtl_pem' onclick=\"simpan()\">".$_SESSION['lang']['save']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo "</div>";
########################
####### END FORM #######
########################

// $optBagian='';
// $arrorgdet = getOrgDetail(1);
// foreach($arrorgdet as $key=>$val){
	// $optBagian.="<option value='".$key."'>".$key." - ".$val."</option>";	
// }

// $opttipe.="<option value='PR'>PR - Purchase Request</option>";
// $opttipe.="<option value='SR'>SR - Service Request</option>";

// $optpri="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $str="select * from ".$dbname.".log_5prioritas order by kode asc";
// $res=fetchData($str);
// foreach($res as $val){
    // $optpri.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
// }

// ##Tanggal Sekarang
// $as=date("Y-m-d");
// $as2=date('Y-m-d', strtotime('+7 days', strtotime($as)));

// echo"<fieldset>
	// <legend>".$_SESSION['lang']['header']."</legend>
	// <table cellspacing='1' border='0' id='opl'>
		// <tr>
			// <td>".$_SESSION['lang']['namaorganisasi']."</td>
			// <td>:</td>
			// <td>
				// <select id='kd_bag'>".$optBagian."</select>
			// </td>
		// </tr>
		// <tr>
			// <td>".$_SESSION['lang']['tipe']."</td>
			// <td>:</td>
			// <td>
				// <select id='tipe'>".$opttipe."</select>
			// </td>
		// </tr>
		// <tr>
			// <td>".$_SESSION['lang']['nopp']."</td>
			// <td>:</td>
			// <td>
				// <input type='text' id='nopp' class='myinputtext' disabled='disabled' style='width:145px;' />
			// </td>
		// </tr>		
		// <tr>
			// <td>".$_SESSION['lang']['tanggal']."</td>
			// <td>:</td>
			// <td>
				// <input type='text' class='myinputtext' id='tgl_pp' name='tgl_pp' value='".tanggalnormal($as)."' readonly='readonly' style='width:145px;' />
			// </td>
		// </tr>
		// <tr>
			// <td colspan=2></td>
			// <td>
				// <input type='hidden' id='method' value='insert' />
				// <input type='hidden' id='user_id' name='user_id' value='".$_SESSION['standard']['userid']."' />
				// <button class=mybutton id='dtl_pem' onclick=\"get_isi()\">".$_SESSION['lang']['save']."</button>
			// </td>
		// </tr>
	// </table>
// </fieldset>

// <fieldset>
// <legend>".$_SESSION['lang']['detail']."</legend>

// <div id='detailTable' style='display:none;'>
// <!-- content detail pp-->
// </div>

// <div id='tmbl_all'>
// <b>No. PR/SR : <label id=detail_kode></label></b><p />
// <table class='sortable' cellspacing='1' border='0' >
	// <thead>
	// <tr class=rowheader>
		// <td align='center'>".$_SESSION['lang']['kodebarang']."</td>
		// <td align='center' width=50px>".$_SESSION['lang']['jmlhDiminta']."</td>
		// <td align='center'>Prioritas</td>
		// <td align='center'>".$_SESSION['lang']['tanggalSdt']."</td>
		// <td align='center'>".$_SESSION['lang']['kodevhc']."</td>
		// <td align='center'>KM/HM</td>
		// <td align='center'>".$_SESSION['lang']['keterangan']."</td>
		// <td align='center'>Action</td>
	// </tr>
	// </thead>
	// <tbody id='detailBody'>
	// <tr class='rowcontent'>
		// <td style='text-align:center;vertical-align:top'>
			// <input type=text class=myinputtext style='width:80px' id=kd_brg disabled>
			// <img id='imgbarang' src='images/onebit_02.png' style='position:relative;top:3px;padding-right:5px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg onkeypress=enterkey(event,findBrg)><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div>',event)\";>
		// </td>
		// <td style='text-align:center;vertical-align:top'>
			// <input type=number min='1' class=myinputtextnumber style='width:80px' id=jmlhDiminta onkeypress='return angka_doang(event)'>
		// </td>
		// <td style='text-align:center;vertical-align:top'>
			// <select id='prioritas'>".$optpri."</select>
		// </td>
		// <td style='text-align:center;vertical-align:top'>
			// <input type=text class=myinputtext style='width:70px;text-align:center' id=tgl_sdt onkeypress='return tanpa_kutip(event)' onmousemove='setCalendar(this.id)' readonly=readonly value='".tanggalnormal($as2)."'>
		// </td>
		// <td style='text-align:center;vertical-align:top'>
			// <input type=text class=myinputtext style='width:100px' id=kd_vhc disabled>
			// <img src='images/onebit_02.png' style='position:relative;top:3px;padding-right:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchVhc('".$_SESSION['lang']['findvhc']."','<fieldset>Find : &nbsp;<input type=text class=myinputtext id=no_vhc onkeypress=enterkey(event,findVhc)><button class=mybutton onclick=findVhc()>Find</button></fieldset><div id=container></div>',event)\";>
			// <img id='showdocument' src='images/book_icon.gif' style='position:relative;top:3px;padding-right:5px;display:none' class=zImgBtn title=".$_SESSION['lang']['dokumen']." onclick=\"showdocsparepart();\">
		// </td>
		// <td style='text-align:center;vertical-align:top'>
			// <input type=number min='0' class=myinputtextnumber style='width:80px' id=kmhm onkeypress='return angka_doang(event)'>
		// </td>
		// <td style='text-align:center;vertical-align:top'>
			// <input type=text class=myinputtext style='width:150px' id=ket onkeypress='return tanpa_kutip(event)'>
		// </td>
		// <td style='text-align:center;vertical-align:top'>
			// <img id=detail_add title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/>
			// <img src=images/clear.png class=zImgBtn  title='Clear' onclick=\"cleardt();\" >
		// </td>
	// </tr>
	// <tr class='rowcontent' id='infodt' style='display:none'>
		// <td colspan=11>
			// <table>
				// <tr>
					// <td>".$_SESSION['lang']['namabarang']."</td>
					// <td>:</td>
					// <td colspan='2'>
						// <label id='lblnamabarang'></label>
					// </td>
				// </tr>
				// <tr>
					// <td>".$_SESSION['lang']['satuan']."</td>
					// <td>:</td>
					// <td colspan='2'>
						// <label id='lblsatuan'></label>
					// </td>
				// </tr>
				// <tr>
					// <td>".$_SESSION['lang']['stok']."</td>
					// <td>:</td>
					// <td colspan='2' style='text-align:right'>
						// <label id='lblstok'></label>
					// </td>
				// </tr>
				// <tr>
					// <td>".$_SESSION['lang']['hargasatuan']."</td>
					// <td>:</td>
					// <td>Rp.</td>
					// <td style='text-align:right'>
						// <label id='lblhargasat'></label>
					// </td>
				// </tr>
				// <tr>
					// <td>".$_SESSION['lang']['realisasi']."</td>
					// <td>:</td>
					// <td>Rp.</td>
					// <td style='text-align:right'>
						// <label id='lblrealisasi'></label>
					// </td>
				// </tr>
				// <tr>
					// <td>".$_SESSION['lang']['budget']."</td>
					// <td>:</td>
					// <td>Rp.</td>
					// <td style='text-align:right'>
						// <label id='lblbudget'></label>
					// </td>
				// </tr>
			// </table>
		// </td>
	// </tr>
	// </tbody>
	// <tr>
		// <td style='text-align:right;vertical-align:top' colspan=11>
			// <input type=hidden id='methoddt' value='insertdt'>
			// <button class=mybutton onclick='showupload(event)'>Upload Files</button>
			// <button class=mybutton onclick='cekdokumen()'>".$_SESSION['lang']['selesai']."</button>
			// <button id='dtBatal' class=mybutton onclick='reset_data()'>".$_SESSION['lang']['cancel']."</button>
		// </td>
	// </tr>
// </table>

// <fieldset>
	// <legend>".$_SESSION['lang']['list']."</legend>
				// <table class='sortable' cellspacing='1' cellpadding='3' border='0'>
					// <thead>
					// <tr class=rowheader>
						// <td align='center'>No.</td>
						// <td align='center'>".$_SESSION['lang']['kodebarang']."</td>
						// <td align='center'>".$_SESSION['lang']['namabarang']."</td> 
						// <td align='center'>".$_SESSION['lang']['satuan']."</td> 
						// <td align='center'>".$_SESSION['lang']['jmlhDiminta']."</td>
						// <td align='center'>Prioritas</td>
						// <td align='center'>".$_SESSION['lang']['tanggalSdt']."</td>
						// <td align='center'>".$_SESSION['lang']['kodevhc']."</td>
						// <td align='center'>KM/HM</td>
						// <td align='center'>".$_SESSION['lang']['stock']."</td>
						// <td align='center'>".$_SESSION['lang']['hargasatuan']."</td>
						// <td align='center'>".$_SESSION['lang']['realisasi']."</td>
						// <td align='center'>".$_SESSION['lang']['budget']."</td>
						// <td align='center'>".$_SESSION['lang']['keterangan']."</td>
						// <td align='center' colspan=2>Action</td>
					// </tr>
					// </thead>
					// <tbody id='listprsr'>
					// </tbody>
				// </table>
			// </fieldset><fieldset><legend>".$_SESSION['lang']['list']." File Upload</legend>
				// <table class='sortable' cellspacing='1' border='0'>
					// <thead>
					// <tr class=rowheader>
						// <td align='center' width=50px>No.</td>
						// <td align='center' width=50px>File Type</td>
						// <td align='center' width=100px>Kriteria</td>
						// <td align='center'>Filename</td>
						// <td align='center' width=55px>Action</td>
					// </tr>
					// </thead>
					// <tbody id='listfilestop'>
					// </tbody>
				// </table>
// </fieldset> 

// </div>

// </fieldset>";

// CLOSE_BOX();
// echo "</div>";

// ##div persetujuan##
// echo"<div id='persetujuan' style='display:none;'>";
// OPEN_BOX();
// echo"<div id='persetujuandata'></div>";

// CLOSE_BOX();
// echo "</div>";
echo close_body(); 

?>