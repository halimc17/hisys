<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/keu_kasdanbank.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<!--deklarasi untuk option-->
<?php
$optunit = $optunitsch = $optakunht = $optakunhtsch = $opttipetransaksi = $optmatauang = $optunitpenerima = $optnorekpenerima = $optrekening = $optpembayaran = $optdepart = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

#= untuk unit ht
$arrunit = array();
$arrunit = getOrgDetail(1);
foreach ($arrunit as $val => $nama) {
	$d = getNamaOrg($val, 'induk');
	if ($d != $n) {
		$optunit .= "<optgroup label='" . $d . " - " . getNamaOrg($d) . "'>";
		$optunitsch .= "<optgroup label='" . $d . " - " . getNamaOrg($d) . "'>";
	}

	# Ga Perlu di Pake
	if ($val == $_SESSION['empl']['lokasitugas']) {
		$optunitsch .= "<option value='" . $val . "' selected>" . $val . " - " . $nama . "</option>";
	} else {
		$optunitsch .= "<option value='" . $val . "' >" . $val . " - " . $nama . "</option>";
	}
	# End

	$optunit .= "<option value='" . $val . "' >" . $val . " - " . $nama . "</option>";

	$n = $d;
	if ($d != $n) {
		$optunit .= "</optgroup>";
		$optunitsch .= "</optgroup>";
	}
}

$arrtipe = array('0' => 'Belum Dibayar', '1' => 'Sudah Dibayar');
foreach ($arrtipe as $key => $data) {
	$optpembayaran .= "<option value='" . $key . "'>" . $data . "</option>";
}

# Pakai Onchange sesuai lokasi tugas
$optakunht = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

#= untuk coa ht
$arrtipeunit = array();
$arrtipeunit = getOrgDetail(10);
$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbank = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . implode("','", $arrtipeunit) . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$_SESSION['empl']['tipelokasitugas']}' OR a.pemilik IN ('" . implode("','", $arrtipeunit) . "')))) GROUP BY a.noakun";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optakunht .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

#= tipe transaksi masuk/keluar
$arrtransaksi = array("M" => "Masuk", "K" => "Keluar");
foreach ($arrtransaksi as $val => $nama) {
	$opttipetransaksi .= "<option value='" . $val . "'>" . $nama . "</option>";
}

$arrtransaksi = array("DPP", "PAJAKPPN", "PAJAKPPH");
foreach ($arrtransaksi as $val) {
	@$optketerangan3 .= "<option value='" . $val . "'>" . $val . "</option>";
}

#= mata uang
$str = "select * from " . $dbname . ".setup_matauang";
$res = fetchdata($str);
foreach ($res as $bar) {
	if ($bar['kode'] == 'IDR') {
		$optmatauang .= "<option value='" . $bar['kode'] . "' selected>" . $bar['matauang'] . "</option>";
		$defaultkurs = '1';
	} else {
		$optmatauang .= "<option value='" . $bar['kode'] . "'>" . $bar['matauang'] . "</option>";
		$defaultkurs = '';
	}
}

$emodul = "KB";
@$arrmodul = getmodulefil($emodul);
foreach ($arrmodul as $key => $val) {
	@$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
}

$opthutangunit = $optalokasi = $optaruskas = $optcustomer = $optsupplier = $optakundt = $optkegiatan = $optnik = $optadk = $optvhc = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$optflaghutangunit = '';
$arrtransaksi = array("0" => "Tidak", "1" => "Ya");
foreach ($arrtransaksi as $val => $nama) {
	$optflaghutangunit .= "<option value='" . $val . "'>" . $nama . "</option>";
}



$str = "select * from " . $dbname . ".pmn_4customer";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optcustomer .= "<option value='" . $bar['kodecustomer'] . "'>" . $bar['kodecustomer'] . " - " . $bar['namacustomer'] . "</option>";
}

$str = "select * from " . $dbname . ".log_5supplier where status=1";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optsupplier .= "<option value='" . $bar['supplierid'] . "'>" . $bar['namasupplier'] . "</option>";
}

#= Buat lebih dari sama dengan 1 aja
#= Ada yang ubah js entah kemana
if (count(getOrgDetail(1)) >= 1) {
	$styleOnChange = "";
	$styleOnChange = "onchange=getakunpengirim()";
}


#<!--HEADER UNTUK BUAT BARU SAMA LIST-->

// echo"<div id=action_list>";//buka div


echo "<div>"; //buka div
OPEN_BOX('', '<span class=judul>' . getMenu('keu_kasdanbank') . '</span>');
echo "<table border=0>
     <tr >
	 <td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
echo "<table>";
echo "
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:150px;\">
				</td>
				
				<td>" . $_SESSION['lang']['unit'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodeorgsch class='select2' style=\"width:154px;\"  onchange=getrekeningsch()>'" . $optunitsch . "'</select>
				</td>
				
				<td>" . $_SESSION['lang']['akun'] . " " . $_SESSION['lang']['kas'] . "/" . $_SESSION['lang']['bank'] . "</td>
				<td>:</td>		
				<td>
					<select id=noakunsch class='select2' style=\"width:154px;\" onchange=getrekeningsch()>'" . $optakunht . "'</select>
				</td>
				
				<td>" . $_SESSION['lang']['noinvoice'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=noinvoicesch placeholder='Inv Internal / Inv Supplier' size=50 class=myinputtext style=\"width:150px;\">
				</td>
				
			</tr>	
			<tr>	
				<td>" . $_SESSION['lang']['tipetransaksi'] . "</td>
				<td>:</td>		
				<td><select id=tipetransaksisch class='select2' style=\"width:154px;\">'" . $opttipetransaksi . "'</select></td>
				
				<td>" . $_SESSION['lang']['tanggal'] . "</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>			
				</td>
				
				<td>" . $_SESSION['lang']['rekening'] . "</td>
				<td>:</td>		
				<td>
					<select id=rekeningsch class='select2' style=\"width:154px;\">'" . $optrekening . "'</select>
				
				</td>
				<td>" . $_SESSION['lang']['supplier'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodesuppliersch class='select2' style=\"width:154px;\">'" . $optsupplier . "'</select>
					<img id=kodesuppliersch onclick=z.elSearch('kodesuppliersch',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
				</td>
			</tr>	
			<tr>
				<td>" . $_SESSION['lang']['dibuat'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=dibuatsch size=50 class=myinputtext style=\"width:150px;\">
				</td>
				<td>" . $_SESSION['lang']['keterangan'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=keterangansch size=50 class=myinputtext style=\"width:150px;\">
				</td>
				
				<td>" . $_SESSION['lang']['approval_status'] . "</td>
				<td>:</td>		
				<td>
					<select id=appstatus class='select2' style=\"width:154px;\">
						<option value=>" . $_SESSION['lang']['pilihdata'] . "</option>
						<option value=0>" . $_SESSION['lang']['belumdiajukan'] . "</option>
						<option value=9>Di " . $_SESSION['lang']['wait_approval'] . "</option>
						<option value=1>" . $_SESSION['lang']['disetujui'] . "</option>
						<option value=2>" . $_SESSION['lang']['ditolak'] . "</option>
						<option value=3>Di " . $_SESSION['lang']['koreksi'] . "</option>
					</select>
				</td>
				<td>" . $_SESSION['lang']['jumlah'] . "</td>
				<td>:</td>		
				<td><input class=myinputtextnumber id=jumlahsch style=\"width:150px;\" onkeypress='return angka_doang(event)' /></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['bayarkemasukdari'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=bayarkesch size=50 class=myinputtext style=\"width:150px;\">
				</td>	

				<td>" . $_SESSION['lang']['pembayaran'] . "</td>
				<td>:</td>		
				<td>
					<select id=pembayaransch class='select2' style=\"width:153px;\">'" . $optpembayaran . "'</select>
				</td>
				
				<td>Additional Column</td>
				<td>:</td>		
				<td>
					<select id=showandhideht class='select2' onchange=showandhideht(this.value); style=\"width:153px;border-color:red;\">
						<option value='0'>Hide</option>
						<option value='1'>Show</option>
					</select>
				</td>
				
			</tr>
			<tr>
			<td></td><td></td>
            <td colspan=3><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
						<button class=mybutton onclick=loaddatapdf()>" . $_SESSION['lang']['pdf'] . "</button>
						<button class=mybutton onclick=loaddataexcel()>" . $_SESSION['lang']['excel'] . "</button></td>
        </tr>
	</table>";
echo "</fieldset></td>";
// <td><input class=myinputtextnumber id=jumlahsch onkeyup=z.numberFormat('jumlahsch',2); style=\"width:150px;\" onkeypress='return angka_doang(event)' /></td>

echo "
     </tr>
	 </table> ";
CLOSE_BOX();
echo "</div>"; //tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
// echo"<div id=listdata style=display:none>";//buka list data
echo "<div id=listdata style=display:block>"; //buka list data
// OPEN_BOX();
OPEN_BOX('', '');
// OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span><br>');
echo " <div class=table-scroll style='height:65vh'>";
echo " <table cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
					<th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['tipe'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['unit'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['rekening'] . "</th>
					<!--<th  align=center rowspan=2>Auto Kas/Bank</th>
					<th  align=center rowspan=2>" . $_SESSION['lang']['unit'] . " " . $_SESSION['lang']['kodeorgpenerima'] . "</th>
					<th  align=center rowspan=2>" . $_SESSION['lang']['akun'] . " " . $_SESSION['lang']['kas'] . "/" . $_SESSION['lang']['bank'] . "  " . $_SESSION['lang']['kodeorgpenerima'] . "</th>
					<th align=center rowspan=2>" . @$_SESSION['lang']['autokb'] . "</th>-->
					<th align=center colspan=3>" . $_SESSION['lang']['jumlah'] . "</th> 
					<th align=center rowspan=2 style=width:250px>" . $_SESSION['lang']['keterangan'] . " </th> 
					<th align=center rowspan=2>" . $_SESSION['lang']['novoucher'] . " </th> 
					<th align=center rowspan=2>" . $_SESSION['lang']['bayarkemasukdari'] . "</th> 

					<th align=center rowspan=2>" . $_SESSION['lang']['approval_status'] . "</th> 
					<th align=center rowspan=2 colspan=5 style=width:50px>" . $_SESSION['lang']['action'] . " </th> 

					<th align=center name=colht0[] colspan=3>" . $_SESSION['lang']['fileupload'] . "</th> 
					<th align=center name=colht0[] colspan=4>" . $_SESSION['lang']['header'] . "</th> 
					<th align=center name=colht0[] colspan=4>" . $_SESSION['lang']['detail'] . "</th> 

					<th align=center name=colht0[] rowspan=2>" . $_SESSION['lang']['submitby'] . "</th>
					<th align=center name=colht0[] rowspan=2>" . $_SESSION['lang']['submittime'] . "</th>
                   
                </tr>  
				<tr class=rowheader>
					<th  align=center>" . $_SESSION['lang']['header'] . "</th>
					<th  align=center>" . $_SESSION['lang']['detail'] . "</th>
					<th  align=center>Balance</th>


					<th align=center name=colht1[]>" . $_SESSION['lang']['file'] . "</th> 
					<th align=center name=colht1[]>" . $_SESSION['lang']['createby'] . "</th> 
					<th align=center name=colht1[]>" . $_SESSION['lang']['createtime'] . "</th> 

					<th align=center name=colht1[]>" . $_SESSION['lang']['createby'] . "</th> 
					<th align=center name=colht1[]>" . $_SESSION['lang']['createtime'] . "</th> 
					<th align=center name=colht1[]>" . $_SESSION['lang']['updateby'] . "</th> 
					<th align=center name=colht1[]>" . $_SESSION['lang']['updatetime'] . "</th> 

					<th align=center name=colht1[]>" . $_SESSION['lang']['createby'] . "</th> 
					<th align=center name=colht1[]>" . $_SESSION['lang']['createtime'] . "</th> 
					<th align=center name=colht1[]>" . $_SESSION['lang']['updateby'] . "</th> 
					<th align=center name=colht1[]>" . $_SESSION['lang']['updatetime'] . "</th>  
                </tr>  
				
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
			<tfoot id=footData></tfoot>
             </table>";
echo "</div>";
CLOSE_BOX();
echo "</div>"; //tutup list data

/*
<td valign=top>".$_SESSION['lang']['keterangan']."</td>	
		<td valign=top>:</td>
		<td colspan=3 rowspan=6  valign=top><textarea rows='4' id=keterangan placeholder='keterangan header' type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:300px;\"></textarea>
		</td>
*/
#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('', '<span class=judul>' . $_SESSION['lang']['header'] . '</span><br>');

//jumlah dan kurs no 1 dan 2, agar mudah remove comma di js
$arrht = "###kurs###jumlah###notransaksi###tipetransaksi###kodeorg###noakun###tanggal###bayarkepada###keterangan###matauang###autokb###noakun2###namapenerima###norekpenerima###rekening";

// if($_SESSION['standard']['username']=='tim.owl3' || $_SESSION['standard']['username']=='vienny.silvertaria'){
$getkk = "<td style='display:none'>Tombol Pengambilan Data</td><td style='display:none'>:</td><td style='display:none'><button class=mybutton id=buttonkk onclick=getkk('Kas')>KK</button></td>";
// }


echo "<fieldset>";
// echo "<fieldset style=float:left>";
echo "<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>
<table cellspacing=1 border=0>


	<tr>
		<td class='bintang' style=\"width:175px;\">" . $_SESSION['lang']['notransaksi'] . "</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\"></td>
		
		<td class='bintang' style=\"width:175px;\">" . $_SESSION['lang']['matauang'] . " & " . $_SESSION['lang']['kurs'] . "</td>
		<td>:</td>		
		<td>
			<select id=matauang class='select2' style=\"width:95px;\" onchange=getkurs()>'" . $optmatauang . "'</select>
			<input class=myinputtextnumber id=kurs disabled style=\"width:50px;\" onkeypress='return angka_doang(event)' />
		</td>
		
		<td class='bintang'>" . $_SESSION['lang']['bayarkemasukdari'] . "</td>
		<td>:</td>		
		<td><input type=text id=bayarkepada class=myinputtext style=\"width:300px;\"></td>
		
		
	</tr>

	<tr>
		<td class='bintang'>" . $_SESSION['lang']['tipetransaksi'] . "</td>
		<td>:</td>		
		<td><select id=tipetransaksi class='select2' style=\"width:154px;\">'" . $opttipetransaksi . "'</select></td>
		
		<td class='bintang'>" . $_SESSION['lang']['jumlah'] . "</td>
		<td>:</td>
		<td><input class=myinputtextnumber id=jumlah disabled onkeyup=z.numberFormat('jumlah',2); style=\"width:150px;\" onkeypress='return angka_doang(event)' /></td>
		
		
		
		<td class='bintang'>" . $_SESSION['lang']['keterangan'] . "</td>
		<td>:</td>		
		<td><input type=text id=keterangan class=myinputtext style=\"width:300px;\"></td>
		
		
	</tr>	

	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['unit'] . "</td>
		<td>:</td>		
		<td>
			<select id=kodeorg " . $styleOnChange . " class='select2' style=\"width:154px;\">'" . $optunit . "'</select><img id=kodeorg onclick=z.elSearch('kodeorg',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
		</td>
		
		<td>Auto Kas/Bank</td>
		<td>:</td>
		<td style=cursor:pointer><input type='checkbox' id='autokb' onclick=pilihautokb()></td>
		
		" . $getkk . "
		
	</tr>
	
	
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['akun'] . " " . $_SESSION['lang']['kas'] . "/" . $_SESSION['lang']['bank'] . "</td>
		<td>:</td>		
		<td>
			<select id=noakun style=\"width:154px;\" class='select2' onchange=getrekening()>'" . $optakunht . "'</select>
		</td>
		
		<td>" . $_SESSION['lang']['unit'] . " " . $_SESSION['lang']['kodeorgpenerima'] . "</td>
		<td>:</td>		
		<td>
			<select id=namapenerima disabled class='select2' onchange=getakunpenerima() style=\"width:154px;\">'" . $optunit . "'</select>
		</td>
	</tr>	
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['rekening'] . "</td>
		<td>:</td>		
		<td>
			<select id=rekening class='select2' style=\"width:154px;\">'" . $optrekening . "'</select>
		</td>
		
		<td>" . $_SESSION['lang']['akun'] . " " . $_SESSION['lang']['kas'] . "/" . $_SESSION['lang']['bank'] . "  " . $_SESSION['lang']['kodeorgpenerima'] . "</td>
		<td>:</td>		
		<td>
			<select id=noakun2 disabled class='select2' onchange=getbank() style=\"width:154px;\">'" . $optakunht . "'</select>
			
		</td>
	</tr>
	
	
	<tr>
		<td class='bintang'>" . $_SESSION['lang']['tanggal'] . "</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal name=tanggal  style=\"width:150px;\" value=" . date('d-m-Y') . " readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>	
		</td>
		
		<td>" . $_SESSION['lang']['bank'] . "  " . $_SESSION['lang']['kodeorgpenerima'] . "</td>
		<td>:</td>		
		<td>
			<select id=norekpenerima disabled class='select2' style=\"width:154px;\">'" . $optnorekpenerima . "'</select>
			<img id=norekpenerima onclick=z.elSearch('norekpenerima',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
			
		</td>
	</tr>


	<tr>
		<td align=center colspan=2></td>
		<td>
			<button class=mybutton onclick=saveht('" . $arrht . "')>" . $_SESSION['lang']['save'] . "</button>&nbsp;
			<button id=batal class=mybutton onclick=cancelht()>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
		
	</tr>


	</table>
</fieldset>";

CLOSE_BOX();
echo "</div>"; //<input type=hidden id=method value='insertht'>	


#- <!--UNTUK BUAT FORM INPUT HEADER-->
$arrdt = "###jumlahdt###keterangan1###nodok###hutangunit1###pemilikhutang1###noaruskas";
$arrdt .= "###noakundt###keterangan2###kodekegiatan###kodeasset###nik###kodecustomer###kodesupplier###kodevhc###orgalokasi###notransaksi";
$arrdt .= "###methoddt###nourut###keterangan3###departemen";
$border = '0';
echo "<div id=detail style=display:none>";
// echo "<div id=detail style=display:block>";
OPEN_BOX('', '<span class=judul>' . $_SESSION['lang']['detail'] . '</span><br>');
$frm[0] = '';
$frm[1] = '';
$frm[2] = '';
$frm[0] .= "<fieldset>
<legend><b>Tools</b></legend>
<table>
<tr>
<td><button class=mybutton id=buttonap onclick=getap('AP')>AP</button></td>
<td><button class=mybutton id=buttonapmasuk onclick=getapmasuk('AP')>AP Masuk</button></td>
<td><button class=mybutton id=buttonar onclick=getar('AR')>AR</button></td>
<td><button class=mybutton id=buttonpdo onclick=showDetailFromPDO()>PDO</button></td>
<td><button class=mybutton id=buttonlain onclick=getlain('Lain')>Lain</button></td>
<td>Khusus untuk transaksi ayat silang / pindah buku abaikan tools ini</td>

</tr>
</table>

</fieldset>";

/*
$frm[0].="<fieldset>
<legend><b>Info Tools</b></legend>
<ol>
	<li>Tombol tools berguna untuk memudahkan dalam penginputan jika sumber data didapat dari transaksi AP / AR</li>
	<li>Jika tipe transaksi <b>keluar</b>, maka tombol AR tidak dapat digunakan, sebaliknya jika tipe transaksi <b>masuk</b>, maka tombol AP tidak dapat digunakan</li>
	<li>Tombol AP, bersumber dari transaksi tagihan AP / invoice AP dimenu : Keuangan->Transaksi->Tagihan (AP)</li>
	<li>Tombol AR, bersumber dari transaksi penagihan AR / invoice AR dimenu : Keuangan->Transaksi->Penagihan (AR)</li>
</ol>
</fieldset>";
*/

// echo "<fieldset>";
$frm[0] .= "<fieldset>";
$frm[0] .= "<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>";

$frm[0] .= "<table cellspacing=1 border=" . $border . ">
			<tr>
				<td style=\"width:175px;\">" . $_SESSION['lang']['noinvoice'] . "</td>
				<td> : </td>
				<td><input type=text id=keterangan1 readonly class=myinputtext style=\"width:150px;\"></td>
				
				<td style=\"width:175px;\">" . $_SESSION['lang']['namakegiatan'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodekegiatan class='select2' disabled style=\"width:154px;\">'" . $optkegiatan . "'</select>
				</td>
				<td class='bintang' valign=top>" . $_SESSION['lang']['keterangan'] . "</td>	
				<td valign=top>:</td>
				<td colspan=3 rowspan=4  valign=top><textarea rows='4' id=keterangan2 placeholder='keterangan detail' type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:300px;\"></textarea></td>		
			</tr>
			
			<tr>
				<td>" . $_SESSION['lang']['nodok'] . "</td>
				<td> : </td>
				<td>
					<input type=text id=nodok class=myinputtext style=\"width:150px;\">
				</td>
				
				<td>" . $_SESSION['lang']['aktivadalam'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodeasset class='select2' disabled style=\"width:154px;\">'" . $optadk . "'</select>
				</td>
			</tr>
			
			<tr>
				<td>" . $_SESSION['lang']['hutangunit'] . "</td>
				<td>:</td>		
				<td>
					<select id=hutangunit1 onchange=getpemilikhutang() class='select2' style=\"width:154px;\">'" . $optflaghutangunit . "'</select>
				</td>
			
				<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
				<td>:</td>		
				<td>
					<select id=nik  style=\"width:154px;\" class='select2' disabled>'" . $optnik . "'</select>
				</td>
			</tr>
			
			<tr>
				<td>" . $_SESSION['lang']['pemilikhutang'] . "</td>
				<td>:</td>		
				<td><select id=pemilikhutang1 class='select2' onchange=getoptdetail() style=\"width:154px;\">'" . $opthutangunit . "'</select>
				</td>
				
				
				<td>" . $_SESSION['lang']['customer'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodecustomer class='select2' disabled style=\"width:154px;\">'" . $optcustomer . "'</select>
				</td>
			</tr>
			
			<tr>
				<td class='bintang'>" . $_SESSION['lang']['akun'] . "</td>
				<td>:</td>		
				<td>
					<select id=noakundt class='select2' style=\"width:154px;\"  onchange=getaruskaskegiatan()>'" . $optakundt . "'</select>
				</td>
				
				<td>" . $_SESSION['lang']['namasupplier'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodesupplier class='select2' disabled style=\"width:154px;\">'" . $optsupplier . "'</select>
				</td>	
				
				<td class='bintang'>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['detail'] . "</td>
				<td>:</td>		
				<td>
					<select id=keterangan3 class='select2' style=\"width:154px;\">'" . $optketerangan3 . "'</select>
					</td>
			</tr>
			
			<tr>
				<td class='bintang'>" . $_SESSION['lang']['noaruskas'] . "</td>
				<td>:</td>		
				<td>
					<select id=noaruskas class='select2' style=\"width:154px;\">'" . $optaruskas . "'</select>
				</td>
			
				<td>" . $_SESSION['lang']['kodevhc'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodevhc class='select2' disabled style=\"width:154px;\">'" . $optvhc . "'</select>
				</td>

				<td>" . $_SESSION['lang']['departemen'] . "</td>
				<td>:</td>		
				<td>
					<select id=departemen class='select2' style=\"width:154px;\">'" . $optdepart . "'</select>
				</td>
				
			</tr>
			
			<tr>
				<td class='bintang'>" . $_SESSION['lang']['jumlah'] . "</td>
				<td>:</td>
				<td><input class=myinputtextnumber id=jumlahdt style=\"width: 150px;\" onkeyup=z.numberFormat('jumlahdt',2); onkeypress='return_tanpa_kutip_dan_sepasi(event)' /></td>
				
				<td>" . $_SESSION['lang']['alokasi'] . "</td>
				<td>:</td>		
				<td>
					<select id=orgalokasi class='select2' disabled style=\"width:154px;\">'" . $optalokasi . "'</select>
				</td>
			</tr>
			
		
			<tr hidden>
				<td colspan=6>
				methoddt<input  type=text id=methoddt value='insert' class=myinputtext style=\"width:150px;\">
				nourut<input type=text id=nourut readonly class=myinputtext style=\"width:150px;\">
			</tr>
			
			<tr>
				<td align=center></td>
				<td  colspan=9>
					<button class=mybutton onclick=savedt('" . $arrdt . "')>" . $_SESSION['lang']['save'] . "</button>&nbsp;
					<button id=batal class=mybutton onclick=canceldt()>" . $_SESSION['lang']['cancel'] . "</button>
					<button id=batal class=mybutton onclick=displaylist()>" . $_SESSION['lang']['selesai'] . "</button>
					<button onclick=\"showandhidedt(0)\" id=tombolshowdt class=mybutton>Show Column</button>
				</td>
			</tr>
		</table></fieldset>"; //<input type=hidden id=methodht value='insertdt'>	

// echo"<div id='listdatadetail'></div>";

$frm[0] .= "
    <fieldset>
            <legend><b>" . $_SESSION['lang']['list'] . "</b></legend>
            <table cellpading=2 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['noinvoice'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['nodok'] . " </th> 
                    <th  align=center>" . $_SESSION['lang']['hutangunit'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['pemilikhutang'] . " </th> 
                    <th  align=center>" . $_SESSION['lang']['noaruskas'] . " </th> 
					<th  align=center>" . $_SESSION['lang']['noakun'] . "</th>
                    <th  align=center>" . $_SESSION['lang']['jumlah'] . "</th>
					<th  align=center>" . $_SESSION['lang']['keterangan2'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['keterangan'] . " DPP/Pajak </th> 
					<th  align=center name=coldt0[]>" . $_SESSION['lang']['kodekegiatan'] . "</th>
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['kodeasset'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['nik'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['kodecustomer'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['kodesupplier'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['kodevhc'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['alokasi'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['departemen'] . " </th> 
                    <th  align=center name=coldt0[]>" . $_SESSION['lang']['lain'] . " </th> 
                    <th  align=center colspan=2>" . $_SESSION['lang']['action'] . " </th> 
                </tr>  
            </thead>
             <tbody id=listdatadt> 
             </tbody>
             </table>
	</fieldset>";

// $frm[1].="<fieldset style='float:left'>
// <legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['upload'] . "</legend>";
$frm[1] .= "<table cellspacing='1' border='0'>
			<tr>
				<td>" . $_SESSION['lang']['kriteria'] . "</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>" . $optkriteria . "</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload'>
				</td>
			</tr>
			<tr>
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='statusbar'></p>
					<p id='loaded_n_total'></p>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick='submitfile()'>Submit</button>
					<button class=mybutton onclick='loadfiles()'>Selesai</button>
				</td>
				
			</tr>
		</table>";
//$frm[1].="<fieldset>
//		<legend>".$_SESSION['lang']['list']."</legend>";
$frm[1] .= "<br><table class='sortable' cellspacing='1' border='0' cellpadding=5>
			<thead>
			<tr class=rowheader>
				<th align='center'>" . $_SESSION['lang']['nourut'] . "</th>
				<th align='center'>" . $_SESSION['lang']['tipedokumen'] . "</th>
				<th align='center'>" . $_SESSION['lang']['kriteria'] . "</th>
				<th align='center'>" . $_SESSION['lang']['namafile'] . "</th>
				<th align='center'>" . $_SESSION['lang']['ukurandokumen'] . "</th>
				<th align='center' colspan=2>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		</fieldset>";

$frm[2] .= "<div id=inputdata>";
$frm[2] .= "
	<fieldset style='float:left;width:30%;margin-right:15px;'><legend>" . $_SESSION['lang']['form'] . "</legend>
		<table border=0>
			<tr>
				<td>" . $_SESSION['lang']['file'] . "</td>
				<td>:</td>
				<td>
					<input type='file' name='uploaddt' id='uploaddt' >
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<button class=mybutton onclick=fileSelected('') style=width:84px;color:blue;>Preview</button>
					<!--<button class=mybutton id=formuploaddt onclick=formupload() style=width:60px;color:red;>Download Template</button>-->
				</td>
			</tr>
		</table>
		
	</fieldset>
	";

$frm[2] .= "
	<fieldset style='width:30%;height:50px;'><legend>Template Download</legend>
		<table border=0>
			<tr>
				<td></td>
				<td></td>
				<td>
					<a href='tempExcel/tempkasbank.xlsx' class=mybutton id=formuploaddt style=width:60px;>Download Template</a>
				</td>
			</tr>
		</table>
	</fieldset>
";

$frm[2] .= "</div>";

$bulan = range(1, 12);

#untuk inputan baru
$frm[2] .= "<br/>";
$frm[2] .= "<div style='clear:both;'></div>";
$frm[2] .= "<fieldset><legend>" . $_SESSION['lang']['preview'] . " Data Excel</legend>";
$frm[2] .= "<div id=contdetail style=display:none; class='table-scroll'>";
$frm[2] .= "</div>";
$frm[2] .= "</fieldset>";

$hfrm[0] = strtoupper($_SESSION['lang']['transaksi']);
$hfrm[1] = strtoupper($_SESSION['lang']['file']);
$hfrm[2] = strtoupper($_SESSION['lang']['upload']);
drawTab('FRM', $hfrm, $frm, 100, 'auto');
CLOSE_BOX();
echo "</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>