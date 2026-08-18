<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
$_SESSION['pajak'] = array();
OPEN_BOX('','<span class=judul>'.getMenu('log_kontrakjasa.php').'</span>');
?>
<script type="text/javascript" src="js/log_kontrakjasa.js?v=3" /></script>
<?php
## BEGIN HEADER AND SEARCH ##
echo"<div id='action_list'>
	<table>
		<tr valign=moiddle>
			<td align=center style='width:100px;cursor:pointer;' onclick=displayforminput()>
				<img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
			</td>
			<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
			<td>
				<fieldset><legend>".$_SESSION['lang']['find']."</legend>
				".$_SESSION['lang']['notransaksi']." : <input type=text id=scnotransaksi size=25 maxlength=30 class=myinputtext>
				<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
				</fieldset>
			</td>
		</tr>
	</table>
</div>";
CLOSE_BOX();
## END HEADER AND SEARCH ##

OPEN_BOX();
## BEGIN LIST DATA ##
echo"<div id='listdata'>
	<div class='table-scroll' style='min-height:65vh'>
	<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>
		<thead>
		<tr style='text-align:center;font-weight:bold'>
			<th>".$_SESSION['lang']['nourut']."</th>
			<th>".$_SESSION['lang']['notransaksi']."</th>
			<th>".$_SESSION['lang']['tanggal']."</th>
			<th>".$_SESSION['lang']['supplier']."</th>
			<th>".$_SESSION['lang']['deskripsi']."</th>
			<th>".$_SESSION['lang']['status']." BAPP</th>
			<th>".$_SESSION['lang']['updateby']."</th>
			<th>".$_SESSION['lang']['status']."</th>
			<th align='center' colspan=6>Action</th>
		</tr>
		</thead>
		<tbody id='contain'></tbody>
		<tfoot id='containft'></tfoot>
		<script>loaddata(0);</script>
	</table>
	</div>
</div>";
## END LIST DATA ##

## BEGIN FORM INPUT ##
echo"<div id=forminput style='display:none'>";
	## GET PT
	$optpt='';
	$pt="";
	$no=0;
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by kodeorganisasi asc";
	$res=fetchdata($str);
	foreach($res as $val){
		if($val['kodeorganisasi']==$_SESSION['empl']['kodeorganisasi']){
			$pt=$val['kodeorganisasi'];
			$optpt.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
		}else{
			$optpt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
		}
	}
	
	## GET UNIT
	$optunit="";
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)='4' order by kodeorganisasi asc";
	$res=fetchdata($str);
	foreach($res as $val){
		if($val['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
			$optunit.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
		}else{
			$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
		}
	}
	
	## GET Supplier
	$optsupplier="";
	$str="select distinct(a.supplierid) as supplierid, b.namasupplier from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.status='1' and a.noakun='2110301' order by b.namasupplier asc";
	$res=fetchdata($str);
	foreach($res as $val){
		$optsupplier.="<option value='".$val['supplierid']."'>".$val['namasupplier']."</option>";
	}
	
	## GET PAJAK
	$optpajak.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='PPHSPK'";
	$res=fetchdata($str);
	$arrpajak=explode(",",$res[0]['nilai']);
	foreach( $arrpajak as $key => $val){
		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$val."'");
		$optpajak.="<option value=".$val.">".$val." - ".$nmakun[$val]."</option>";
	}
	
	## GET SATUAN
	$optsatuan.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select * from ".$dbname.".setup_satuan order by satuan asc";
	$res=fetchdata($str);
	foreach($res as $val){
		$optsatuan.="<option value=".$val['satuan'].">".$val['satuan']."</option>";
	}
	
	## GET CAT
	$optcat.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select * from ".$dbname.".log_5kategoribarang order by id asc";
	$res=fetchdata($str);
	foreach($res as $val){
		$optcat.="<option value=".$val['id'].">".$val['jenis']."</option>";
	}
	
	## GET TIPE
	$opttipe.="";
	$arrtipe=tipektrkjasa();
	foreach($arrtipe as $key=>$val){
		$opttipe.="<option value=".$key.">".$val."</option>";
	}
	
	## BEGIN FORM INPUT HEADER ##
	echo"<fieldset><legend>".$_SESSION['lang']['header']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td>
				<input type='text' id='notransaksi' class='myinputtext' disabled='disabled' style='width:250px;' /> <font color=red>*Otomatis
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']." Induk</td>
			<td>:</td>
			<td>
				<input type='text' id='notransaksiinduk' class='myinputtext' style='width:250px;' /> <img id='imgnotransaksiinduk' onclick=\"popupnotranindk(event)\" title='Cari No Transaksi Induk' class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top;min-width:120px;'>".$_SESSION['lang']['pt']."</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<select id='pt' onchange=\"getunit()\">".$optpt."</select>
				<img id='imgpt' onclick=z.elSearch('pt',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top;min-width:120px;'>".$_SESSION['lang']['unit']."</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<select id='unit'>".$optunit."</select>
				<img id='imgunit' onclick=z.elSearch('unit',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']." Kontrak</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggalkontrak' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:80px;text-align:center' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['deskripsi']."</td>
			<td>:</td>
			<td>
				<input type=hidden id=clne value='0'>
				<input id=deskripsi class=myinputtext onkeypress=\"return tanpa_kutip(event);\"  style=\"width:300px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['koderekanan']."</td>
			<td>:</td>
			<td>
				<select id='supplier'>".$optsupplier."</select>
				<img id='imgsupplier' onclick=z.elSearch('supplier',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggalmulai']."</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tgldari' value='".date('01-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:80px;text-align:center' /> s/d 
				<input type='text' class='myinputtext' id='tglsampai' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:80px;text-align:center' />
				<img src='images/clear.png' class='zImgBtn' title='Clear Periode Sampai' onclick=\"clearperiode();\" style='position:relative;top:3px;left:2px;'>
			</td>
		</tr>
		<tr>
			<td valign=top>".$_SESSION['lang']['spesifikasi']." ".$_SESSION['lang']['pekerjaan']."</td> 
			<td valign=top>:</td>
			<td><textarea rows='3' maxlength=1024 id=spesifikasi type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:305px;\"></textarea></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['uangmuka']."</td> 
			<td>:</td>
			<td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:100px;\" value='' id=uangmuka  onkeyup=\"z.numberFormat('uangmuka',2);\">
		</tr>
		<tr>
			<td>Retensi</td> 
			<td>:</td>
			<td>
			Persen : <input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:50px;\" id=retensipersen  onkeyup=\"z.numberFormat('retensipersen',2);\">
			&nbsp;&nbsp;&nbsp;
			Nilai : <input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:100px;\" id=retensinilai  onkeyup=\"z.numberFormat('retensinilai',2);\">
		</tr>
		<tr>
			<td style='vertical-align:top'>".$_SESSION['lang']['pajak']."</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<table class='sortable' cellspacing=1 cellpadding=1 border=0>
					<thead>
					<tr class=rowheader style=text-align:center>
						<td>".$_SESSION['lang']['namaakun']."</td>
						<td>".$_SESSION['lang']['pajak']." (%)</td>
						<td width=18px></td>
					</tr>
					</thead>
					
					<tbody id='listpajak'>
					<tr class=rowcontent>
						<td style='text-align:center' colspan=3>".$_SESSION['lang']['datanotfound']."</td>
					</tr>
					</tbody>
					
					<tbody>
					<tr class=rowcontent id='frmpajak'>
						<td>
							<select id=jenispajak>".$optpajak."</select>
						</td>
						<td>
							<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='%' style=\"width:50px;\" value='' id=nilaipajak onkeyup=\"z.numberFormat('nilaipajak',2);\">
						</td>
						<td>
							<img src='images/plus.png' class='zImgBtn' title='Tambah Pajak'; onclick=addpajak(); 	style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button id=tomboldetail class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button id=batal class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
	</fieldset>";
	## END FORM INPUT HEADER ##
	
	## BEGIN FORM INPUT DETAIL ##
	echo"<fieldset id='listitem' style='display:none'><legend>".$_SESSION['lang']['detail']."</legend>
		<div id='databarang'>
			<table class='sortable' border=0 cellpadding=3 cellspacing=1>
				<thead><tr class=rowheader style=text-align:center>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['tipe']."</td>
					<td>".$_SESSION['lang']['kategori']."</td>
					<td>".$_SESSION['lang']['kegiatan']."/".$_SESSION['lang']['material']."</td>
					<td>".$_SESSION['lang']['satuan']."</td>
					<td>Rp / ".$_SESSION['lang']['satuan']."</td>
					<td></td>
				</tr></thead>
				<tbody>
				<tr class='rowcontent' style='text-align:center'>
					<td></td>
					<td>
						<select id=tipedt>".$opttipe."</select>
					</td>
					<td>
						<select id=ketegoridt>".$optcat."</select>
					</td>
					<td>
						<input id=kegiatandt class=myinputtext placeholder='type here..' onkeypress=\"return tanpa_kutip(event);\"  style=\"width:300px;\">
					</td>
					<td>
						<select id=satuandt>".$optsatuan."</select>
						<img id='imgsatuandt' onclick=z.elSearch('satuandt',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;padding-right:5px;'>
					</td>
					<td>
						<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='0' style=\"width:100px;\" value='' id=rpdt onkeyup=\"z.numberFormat('rpdt',2);\">
					</td>
					<td>
						<img src='images/plus.png' class='zImgBtn' title='Add' onclick=adddt() style='position:relative;top:3px;left:3px;padding-right:5px;'>
					</td>
				</tr></tbody>
				<tbody id='listdt'></tbody>
			</table>
		</div>
	</fieldset>";
	## END FORM INPUT DETAIL ##
	
echo"</div>";
## END FORM INPUT ##
CLOSE_BOX();

echo close_body(); 
?>