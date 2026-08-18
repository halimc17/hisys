<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('kontrak payung').'</span>');
?>

<script language="javascript" src="js/log_kontrakpayung.js?v=1"></script>

<?php

$_SESSION['ktrkpayung'] = array();
$_SESSION['ktrkpayungimg'] = array();

echo"<table cellspacing=1 border=0>
	<tr valign=moiddle>
		<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/newfile.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td>
		<fieldset>
			<legend>" . $_SESSION['lang']['find']."</legend>
			<table>
				<tr>
					<td>No ".$_SESSION['lang']['kontrak']."</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' id='kontrakcari' name='kontrak' onkeypress='return tanpa_kutip(event)' style='width:145px;'' maxlength='45' />
					</td>
					
					<td style='padding-left:10px;'>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>
					</td>
					
					<td style='padding-left:10px;'>
						<button class=mybutton onclick=\"loadData(0)\">".$_SESSION['lang']['find']."</button>
					</td>
				</tr>
			</table>
		</fieldset>
		</td>
	</tr>
</table>";
CLOSE_BOX();

## BEGIN LIST DATA ##
echo"<div id='listData'>";
OPEN_BOX();

echo"<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<table cellspacing='1' cellpadding='3' border='0' class='sortable'>
		<thead>
        <tr class='rowheader'>
			<td align='center'>No.</td>
            <td align='center'>".$_SESSION['lang']['kontrak']."</td>
			<td align='center'>".$_SESSION['lang']['tanggal']." Buat</td>
			<td align='center'>".$_SESSION['lang']['tanggal']." Awal</td>     
            <td align='center'>".$_SESSION['lang']['tanggal']." Akhir</td>    
            <td align='center'>".$_SESSION['lang']['supplier']."</td>  
            <td align='center'>Status</td>	 
            <td align='center'>".$_SESSION['lang']['note']."</td>
            <td align='center' colspan=2>Action</td>
		</tr>
		</thead>
		<tbody id='contain'><script>loadData(0)</script></tbody>
        </table>
    </fieldset>";

CLOSE_BOX();
echo"</div>";
## END LIST DATA ##

## BEGIN INPUT FORM ##
echo"<div id='headher' style='display:none'>";
OPEN_BOX();

## GET LIST SUPPLIER
$str="select * from ".$dbname.".log_5supplier where status=1 order by namasupplier";
$res=fetchdata($str);
foreach($res as $val){
	$optSupplier.="<option value='".$val['supplierid']."'>".$val['namasupplier']." (".$val['supplierid'].")</option>";
}

## GET LIST STATUS
$status= array('1' =>'Aktif' ,'0' =>'Tidak Aktif' );
foreach ($status as $sts=>$val) {
    $optstatus.="<option value='".$sts."'>".$val."</option>";
}


## GET MASTER BARANG
$str="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where left(kodebarang,1) in ('3','8','9') order by namabarang asc";
$res=fetchdata($str);
foreach($res as $val){
	$optbrg.="<option value='".$val['kodebarang']."'>".$val['namabarang']."</option>";
}

$optalamat=$optnpwp=$optbank=$optwktpyrh=$optalmtkrm=$opttop="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

## GET WAKTU PENYERAHAN
$str="select kode,nama from ".$dbname.".log_5delivtime order by kode asc";
$res=fetchdata($str);
foreach($res as $val){
	$optwktpyrh.="<option value='".$val['kode']."'>".$val['nama']."</option>";
}

## GET LOKASI PENGIRIMAN BARANG DAN TAGIHAN
$str="select id_franco,franco_name from ".$dbname.".setup_franco";
$res=fetchdata($str);
foreach($res as $val){
	$optalmtkrm.="<option value='".$val['id_franco']."'>".$val['franco_name']."</option>";
}

## GET SYARAT BAYAR
$str="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar order by keterangan asc";
$res=fetchdata($str);
foreach($res as $val){
	$opttop.="<option value='".$val['kode']."'>".$val['keterangan']." (".$val['jenis'].")</option>";
}

echo"<fieldset style='float:left'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td style='vertical-align:top'>
			<table cellspacing='1' border='0'>
				<tr>
					<td>".$_SESSION['lang']['namasupplier']."</td>
					<td>:</td>
					<td>
						<select id='supp' name='supp' style='width:200px;' onchange=\"getdatasupplier()\"><option value=''>".$_SESSION['lang']['pilihdata']."</option> ".$optSupplier."</select>
						<img id=supp onclick=z.elSearch('supp',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['alamat']." ".$_SESSION['lang']['supplier']."</td>
					<td>:</td>
					<td>
						<select id=alamat_sup style=\"width:200px;\">".$optalamat."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['npwp']." ".$_SESSION['lang']['supplier']."</td>
					<td>:</td>
					<td>
						<select id=npwp_sup style=\"width:200px;\">".$optnpwp."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['norekeningbank']."</td>
					<td>:</td>
					<td colspan=4>
						<select id=bank_acc style=\"width:200px;\">".$optbank."</select>
					</td>
				</tr>
				<tr>
					<td>No. ".$_SESSION['lang']['kontrak']."</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' id='kontrak' name='kontrak' onkeypress='return tanpa_kutip(event)'' style='width:200px;' maxlength='45' />
					</td>
				</tr>
				<tr>
					<td>". $_SESSION['lang']['tanggal'].' Buat'."</td>
					<td>:</td>
					<td>
						<input align='center' type=text class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' value='".date('d-m-Y')."' onkeypress='return false;' size='10' maxlength='10' style='width:60px;' readonly/>
					</td>
				</tr>
				<tr>
					<td>". $_SESSION['lang']['periode'].' kontrak'."</td>
					<td>:</td>
					<td>
						<input align='center' type=text class=myinputtext id=tgl1 onmousemove=setCalendar(this.id) onkeypress=\"return false;\" size=10 maxlength=10 style=width:60px; readonly value='".date('01-m-Y')."' />
						s/d
						<input align='center' type=text class=myinputtext id=tgl2 onmousemove=setCalendar(this.id) onkeypress=\"return false;\" size=10 maxlength=10 style=width:60px; readonly value='".date('d-m-Y')."' />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['waktupenyerahan']."</td>
					<td>:</td>
					<td>
						<select id=delivtime style=\"width:200px;\">".$optwktpyrh."</select>
					</td>
				</tr>
				
				<tr>
					<td>".$_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['barang']."</td>
					<td>:</td>
					<td>
						<select style='width:200px' id='tmpt_krm' name='tmpt_krm'>".$optalmtkrm."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['invoice']."</td>
					<td>:</td>
					<td>
						<select style='width:200px' id='invc_krm' name='tmpt_krm'>".$optalmtkrm."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['syaratPem']."</td>
					<td>:</td>
					<td>
						<select style='width:200px' id='term_pay' name='term_pay'>".$opttop."</select>
					</td>
					
				</tr>
				<tr>
					<td>PPn (%)</td>
					<td>:</td>
					<td>
						<input type=text id='ppN' name='ppN'  class='myinputtextnumber' style='width:100px' onkeyup='calculatePpn()'  maxlength='5'  onkeypress='return angka_doang(event)' onblur=\"getZero()\" />
					</td>
					
				</tr>
				<tr>
					<td>PPh (%)</td>
					<td>:</td>
					<td>
						<input type=text id='ppH' name='ppH'  class='myinputtextnumber' style='width:100px' onkeyup='calculatePph()'  maxlength='5'  onkeypress='return angka_doang(event)' onblur=\"getZero()\" />
					</td>
					
				</tr>
				<tr>
					<td>" .$_SESSION['lang']['note']."</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=cttn name=cttn onkeypress=return tanpa_kutip(event) style=width:145px; maxlength=100 />
					</td>
				</tr>
				<tr>
					<tr>
					<td>Status</td>
					<td>:</td>
					<td>
						<select id=status style=width:150px;>".$optstatus."</select>
					</td>
				</tr>
			</table>
			</td>
			<td style='vertical-align:top;padding-left:10px'>
			<fieldset style='float:left'>
			<legend>List Item Barang</legend>
			<table cellspacing='1' cellpadding='3' border='0' class='sortable'>
				<thead>
				<tr class='rowheader'>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>".$_SESSION['lang']['namabarang']."</td>
					<td>".$_SESSION['lang']['satuan']."</td>
					<td>".$_SESSION['lang']['kuantitas']."</td>
					<td>".$_SESSION['lang']['hargasatuan']."</td>
					<td>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody id='itemkontrak'></tbody>
				<tbody>
				<tr class=rowcontent>
					<td></td>
					<td><label id='lblkodebarang'></label></td>
					<td>
						<select id='kdbrg' name='kdbrg' style='width:150px;' onchange='getsatuan()'><option value=''></option>".$optbrg."</select><img id=kdbrg onclick=z.elSearch('kdbrg',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
					</td>
					<td><label id='lblsatuan'></label></td>
					<td>
						<input type=number min='1' class=myinputtextnumber style='width:50px' id=qty onkeypress='return angka_doang(event)' placeholder='0'>
					</td>
					<td>
						<input type=text id=harga class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('harga',2)\" style='width:80px' placeholder='0' />
					</td>
					<td style='text-align:center'>
						<img title='Tambah' class='resicon' onclick=\"additem()\" src='images/plus.png'>
					</td>
				</tr>
				</tbody>
			</table>
			</fieldset>
			<br>
			<fieldset style='float:left'>
			<legend>List File Upload</legend>
			<table class=sortable cellspacing=1 border=0>
				<thead> 
				<tr>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['namafile']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody id=containerupload></tbody>
				<tbody>
				<tr>
					<td colspan=2>
						<input type='file' name='upload' id='upload' class=mybutton>
					</td>
					<td style='text-align:center'>
						<img src=images/plus.png class=resicon id='addfile'  title='Add File ' onclick=\"submitfile();\">
					</td>
				</tr>
				</tbody>
			</table>
			</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan='2' style='text-align:center'>
				<button class=mybutton onclick=saveData()>" .$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=cancelSave()>". $_SESSION['lang']['cancel']."</button>
				<input type=hidden id=proses name=proses value=insert  />
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo "</div>";
## END INPUT FORM ##


echo close_body();
?>