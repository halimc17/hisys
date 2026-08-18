<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/log_suratjalan.js?v=<?php echo time(); ?>'></script>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('log_suratjalan').'</span>');
## SEARCH
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
							<td>".$_SESSION['lang']['nosj']."</td>
							<td>
								<input id='srcnosj' class='myinputtext' type='text' onkeypress='return tanpa_kutip(event)' value=''>
							</td>
							
							<td style='padding-left:20px'>No. Packing List</td>
							<td>
								<input id='srcnopl' class='myinputtext' type='text' onkeypress='return tanpa_kutip(event)' value=''>
							</td>
							
							<td style='padding-left:20px'>No. PR</td>
							<td>
								<input id='srcnopp' class='myinputtext' type='text' onkeypress='return tanpa_kutip(event)' value=''>
							</td>
							
							<td style='padding-left:20px'>No. PO</td>
							<td>
								<input id='srcnopo' class='myinputtext' type='text' onkeypress='return tanpa_kutip(event)' value=''>
							</td>
								
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


## LIST DATA ##
echo "<div id='listdata'>";
OPEN_BOX();
echo"
	<div style='overflow:auto;'>
	<table class='sortable' cellspacing='1' cellpadding=5 border='0' style='width:100%'>
		<thead>
		<tr class=rowheader>
			<td align='center'>No.</td>
			<td align='center'>".$_SESSION['lang']['nosj']."</td>
			<td align='center'>".$_SESSION['lang']['pt']."</td>
			<td align='center'>".$_SESSION['lang']['tanggal']."</td>
			<td align='center'>".$_SESSION['lang']['tgl_kirim']."</td>
			<td align='center'>Release</td> 
			<td align='center'>Gudang Tujuan</td> 
			<td align='center'>No. PO</td>
			<td align='center'>No. PR</td>
			<td align='center'>Action</td>
		</tr>
		</thead>
		<tbody id='contain'>
			<script>loaddata(0)</script>
		</tbody>
	 <tfoot>
	 </tfoot>
	 </table>
	</div>";
CLOSE_BOX();
echo"</div>";

## FORM ##
echo"<div id='forminput' style='display:none;'>";
OPEN_BOX();

## GET UNIT
$optunit='';
$arrorgdet = getOrgDetail(1);
$no=0;
$optunit2="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrorgdet as $key=>$val){
	$subro = substr($key,2,2);
	if($subro=='HO' || $subro=='RO' || $subro=='LO'){
		$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";
		$optunit2.="<option value='".$key."'>".$key." - ".$val."</option>";
		if($no==0){
			$loktugas.= "'".$key."'";
		}else{
			$loktugas.= ",'".$key."'";
		}
		$no++;
	}
}

## GET EXPEDITOR
$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi='EX' and kodeparameter='EXPD'";
$res=fetchdata($str);
$arrexp = explode(',',$res[0]['nilai']);

$optexpeditor = array();
$str = "select a.supplierid,a.namasupplier from ".$dbname.".log_5supplier a left join log_5supkelompok b on a.supplierid = b.supplierid
where b.tipe in ('".implode("','",$arrexp)."')";
$res=fetchData($str);
$optexpeditor="<option value=''>Internal</option>";	
$optexpeditor.="<option value='EXT'>External</option>";	
foreach($res as $val){
	$optexpeditor.="<option value='".$val['supplierid']."'>".$val['namasupplier']."</option>";	
}

## GET JENIS KENDARAAN
$str="select jenisvhc,namajenisvhc from ".$dbname.".vhc_5jenisvhc where kelompokvhc='KD'";
$res=fetchdata($str);
foreach($res as $val){
	$optjeniskedaraan.="<option value='".$val['jenisvhc']."'>".$val['namajenisvhc']."</option>";
}
$tmpKend = array('Colt Diesel','Fuso','Tronton','Buildup','Trailer','Kapal Laut','Kereta Api','Pesawat');
foreach($tmpKend as $val) {
	$optjeniskedaraan.="<option value='".$val."'>".$val."</option>";
}

## GET PENGIRIM
$optpengirim="";
$optKar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan  where lokasitugas in (".$loktugas.")";
$res=fetchdata($str);
foreach($res as $val){
	$optpengirim.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']."</option>";
}

## GET GUDANG Tujuan
// $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%'";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG'";
$res=fetchdata($str);
foreach($res as $val){
	$optgudangtujuan.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
}

## GET TRANSPORTASI
$opttransportasi.="<option value='DARAT'>".$_SESSION['lang']['darat']."</option>";
$opttransportasi.="<option value='UDARA'>".$_SESSION['lang']['udara']."</option>";
$opttransportasi.="<option value='LAUT'>".$_SESSION['lang']['laut']."</option>";

echo"<fieldset>
	<legend>".$_SESSION['lang']['header']."</legend>
	<table cellspacing='1' cellpadding=2 border='0' id='opl'>
		<tr>
			<td>".$_SESSION['lang']['nosj']."</td>
			<td>:</td>
			<td>
				<input type='text' id='nosj' class='myinputtext' disabled='disabled' style='width:145px;' /> <font color=red>* Otomatis
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select id='unit' style='width:150px'>".$optunit."</select>
			</td>
			
			<td>".$_SESSION['lang']['supir']."</td>
			<td>:</td>
			<td>
				<input id='supir' class='myinputtext' type='text' onkeypress=\"return tanpa_kutip(event)\" style='width:145px'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggal' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:80px;text-align:center' />
			</td>
			
			<td>".$_SESSION['lang']['nohp'].' '.$_SESSION['lang']['supir']." / ".$_SESSION['lang']['expeditor']."</td>
			<td>:</td>
			<td>
				<input id='hpsupir' class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' style='width:145px'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tgl_kirim']."</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggalkirim' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:80px;text-align:center' />
			</td>
			
			<td>".$_SESSION['lang']['pengirim']."</td>
			<td>:</td>
			<td>
				<select id='pengirim' style='width:150px'>".$optpengirim."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['expeditor']."</td>
			<td>:</td>
			<td>
				<select id='expeditor' style='width:150px'>".$optexpeditor."</select>
				<img id='imgexpeditor' onclick=z.elSearch('expeditor',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
			
			<td>".$_SESSION['lang']['cek']."</td>
			<td>:</td>
			<td>
				<input id='cek' class='myinputtext' type='text' onkeypress=\"return tanpa_kutip(event)\" style='width:145px'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nopol']."</td>
			<td>:</td>
			<td>
				<input id='nopol' class='myinputtext' type='text' onkeypress=\"return tanpa_kutip(event)\" style='width:145px'>
			</td>
			
			<td>Gudang Tujuan</td>
			<td>:</td>
			<td>
				<select id='gudangtujuan' style='width:150px'>".$optgudangtujuan."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jeniskend']."</td>
			<td>:</td>
			<td>
				<select id='jeniskedaraan' style='width:150px'>".$optjeniskedaraan."</select>
			</td>
			
			<td>".$_SESSION['lang']['transportasi']."</td>
			<td>:</td>
			<td>
				<select id='transportasi' style='width:150px'>".$opttransportasi."</select>
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
</fieldset>

<div id='listdt' style='display:none;'>
	<fieldset>
		<button id='detailPo' class='mybutton' onclick=\"showPO(event)\">Add Detail from PO</button>
		<!--<button id='detailPl' class='mybutton' onclick=\"showPL(event)\">Add Detail from Package List</button>-->
		<legend>".$_SESSION['lang']['detail']."</legend>
		<table cellspacing='1' cellpadding=2 class='sortable'>
			<thead>
			<tr class=rowheader>
				<td align='center'>".$_SESSION['lang']['jenis']."</td>
				<td align='center'>".$_SESSION['lang']['kodebarang']."</td>
				<td align='center'>".$_SESSION['lang']['namabarang']."</td>
				<td align='center'>No. PO</td> 
				<td align='center'>No. PR</td> 
				<td align='center'>".$_SESSION['lang']['jumlah']."</td>
				<td align='center'>".$_SESSION['lang']['satuan']."</td> 
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='containdt'>
			<tr class=rowcontent>
				<td colspan=9 align=center>".$_SESSION['lang']['errdatanotexist']."</td>
			</tr>
			</tbody>
		</table>
	</fieldset>
</div>";

CLOSE_BOX();
echo "</div>";


echo close_body();	
?>