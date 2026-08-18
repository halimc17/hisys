<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
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
					No. PR/SR  : <input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>
					".$_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>
					<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>
				</fieldset>
			</td>
		</tr>
	</table> 
</div>";

CLOSE_BOX();

echo "<div id=\"list_pp\">";

OPEN_BOX();

echo"<!--<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>-->
	<!--<img src='images/pdf.jpg' onclick=\"masterPDF('log_prapoht','','','log_print_pdf_pp',event)\" width='20' height='20' />-->
	<!--<img onclick=\"javascript:print()\" style='width: 20px; height: 20px; cursor: pointer;' title='Print Page' src='images/printer.png'>-->
	
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
			<th align='center'>Action</th>
		</tr>
		</thead>
		<tbody id='contain'>
			<script>loadData(0)</script>
		</tbody>
	</table>
	<fieldset style=float:left>
	<legend>Note</legend>
	<table>
		<tr>
			<td style='width:20px;background:#42f5c2'>&nbsp;</td>
			<td>:</td>
			<td>Ada beberapa item yang di close oleh departemen purchasing</td>
		</tr>
		<tr>
			<td style='width:20px;background:orange'>&nbsp;</td>
			<td>:</td>
			<td>Ditolak oleh Approval</td>
		</tr>
	</table>
	</div>
</fieldset>
";

CLOSE_BOX(); //2 C
echo"</div>";


echo"<div id='form_pp' style='display:none;'>";
OPEN_BOX();

/* Organisasi */
$optBagian = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sStr = selectQuery($dbname, "organisasi", "kodeorganisasi", "LENGTH(kodeorganisasi) = 4 AND kodeorganisasi IN (".getOrgDetail(2).")", "inti DESC");
$qStr = fetchData($sStr);
foreach($qStr as $val){
    $optBagian .= "<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."</option>";
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
$optkar="";
$chk="0";
$str="select karyawanid,namakaryawan,nik,bagian from ".$dbname.".datakaryawan where lokasitugas='".$unitkerja."' and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$as."') order by namakaryawan asc";
$res=fetchdata($str);
foreach($res as $val){
	$nmdept = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$val['bagian']."'");
	if($_SESSION['standard']['userid']==$val['karyawanid']){
		$chk='1';
	}
	if($_SESSION['standard']['userid']==$val['karyawanid']){
		$optkar.="<option value='".$val['karyawanid']."' selected>".$val['namakaryawan']." (".$nmdept[$val['bagian']].")</option>";	
		$sendiri=true;
	}else{
		$sendiri=false;
		@$optkar.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." (".$nmdept[$val['bagian']].")</option>";											
	}
}
if($sendiri==false){
	$optkar.="<option value='".$_SESSION['standard']['userid']."' selected>".getKary($_SESSION['standard']['userid'])." (".$nmdept[getKary($_SESSION['standard']['userid'],'bagian')].")</option>";	
}


if($chk=='0'){
	if($_SESSION['empl']['tipekaryawan']=='0'){
		$nmdept = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$_SESSION['empl']['bagian']."'");
		$optkar.="<option value='".$_SESSION['standard']['userid']."' selected>".$_SESSION['empl']['name']." (".$nmdept[$_SESSION['empl']['bagian']].")</option>";
	}
}

echo"<fieldset>
	<legend>".$_SESSION['lang']['header']."</legend>
	<table cellspacing='1' border='0' id='opl' class='urutaninputan'>
		<tr>
			<td class='bintang'>".$_SESSION['lang']['namaorganisasi']."</td>
			<td>:</td>
			<td>
				<select id='kd_bag' style='width:200px' onchange=\"getrequester()\">".$optBagian."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select id='tipe' style='width:200px'>".$opttipe."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nopp']."</td>
			<td>:</td>
			<td>
				<input type='text' id='nopp' class='myinputtext' disabled='disabled' style='width:196px' />
			</td>
		</tr>		
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<!--<input type='text' class='myinputtext' id='tgl_pp' name='tgl_pp' value='".tanggalnormal($as)."' readonly='readonly' style='width:196px'/>-->
				<input type=text class=myinputtext style='width:195px;' id=tgl_pp onkeypress='return tanpa_kutip(event)' onmousemove='setCalendar(this.id)'  value='".tanggalnormal($as)."'>
			</td>
		</tr>
		<tr>
			<td>Requester</td>
			<td>:</td>
			<td>
				<select id=requester style='width:180px'>".$optkar."</select>
				<img id='requester' onclick=z.elSearch('requester',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
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

<fieldset>
<legend>".$_SESSION['lang']['detail']."</legend>

<div id='detailTable' style='display:none;'>
<!-- content detail pp-->
</div>

<div id='tmbl_all'>
<b>No. PR/SR : <label id=detail_kode></label></b><p />
<table class='sortable' cellspacing='1' border='0' >
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
			<select id='prioritas'>".$optpri."</select>
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

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' cellpadding='3' border='0'>
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
			</fieldset><fieldset><legend>".$_SESSION['lang']['list']." File Upload</legend>
				<table class='sortable' cellspacing='1' border='0' cellpadding=5>
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