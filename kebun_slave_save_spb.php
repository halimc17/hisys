<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('config/connection2.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
include_once('lib/zFunction.php');

$proses       =checkPostGet('proses','');
$blok         =checkPostGet('blok','');
$noSpb        =checkPostGet('noSpb','');
$tipe         =checkPostGet('tipe','');
$tanggal      =tanggalsystem(checkPostGet('tgl',''));
$bjrHsl       =checkPostGet('bjr','');
$jjngHsl      =intval(checkPostGet('jjng',0));
$brondolanHsl =intval(checkPostGet('brondolan',0));
$user_online  =$_SESSION['standard']['userid'];
$kdOrg        =checkPostGet('kdOrg','');
$idDiv        =checkPostGet('idDiv','');
$matang       =checkPostGet('matang','');
$mentah       =checkPostGet('mentah','');
$busuk        =checkPostGet('busuk','');
$lwtmatang    =checkPostGet('lwtmatang','');
$kdOrg        =checkPostGet('kdOrg','');
$oldBlok      =checkPostGet('oldBlok','');
$oldTph       =checkPostGet('oldTph','');
$oldPemanen   =checkPostGet('oldPemanen','');
$oldQrcode    =checkPostGet('oldQrcode','');
$oldSesi      =checkPostGet('oldSesi','');
$kgwb         =checkPostGet('kgwb',0);
$intex        =checkPostGet('intex','');
$pks          =checkPostGet('pks','');
$kerani       =checkPostGet('kerani','');
$idDiv2       =checkPostGet('kodeDiv','');
$nospbunpost  =checkPostGet('nospb','');
$kodeorgunpost=checkPostGet('kodeorg','');
$notransaksi  =checkPostGet('notransaksi','');
$tkbm         =checkPostGet('tkbm','');
$jjgtk        =checkPostGet('jjgtk','');
$brdtk        =checkPostGet('brdtk','');
$kendaraan    =checkPostGet('kendaraan','');
$sesi         =checkPostGet('sesi','');
$kendaraantk  =checkPostGet('kendaraantk','');
$sesitk       =checkPostGet('sesitk','');
$kontanan     =checkPostGet('kontanan','');
$kegiatan     =checkPostGet('kegiatan','');
$tahuntanam   =checkPostGet('tahuntanam','');
$tglpanen     =tanggalsystemn(checkPostGet('tglpanen',''));
$oldtglpanen  =tanggalsystemn(checkPostGet('oldtglpanen',''));
$status_spb   =checkPostGet('status_spb','');

$petani       =checkPostGet('petani','');
$jjgpetani    =checkPostGet('jjgpetani','');
$brdpetani    =checkPostGet('brdpetani','');
$referensimb    =checkPostGet('referensimb','');
$referensisearch    =checkPostGet('referensisearch','');

$txtSearch    =checkPostGet('txtSearch','');
$txtDiv       =checkPostGet('txtDiv','');
$txtTgl       =tanggalsystem(checkPostGet('txtTgl',''));

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}


$jab          =getPostingJabatan('spat');

$sReg="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".$_SESSION['empl']['lokasitugas']."'";

$qReg=$owlPDO->query($sReg) or die(print " Gagal: ".PDOException::getMessage());
$qReg->setFetchMode(PDO::FETCH_ASSOC);
$rReg=$qReg->fetch();



switch($proses){
	case 'showtkbm':

	if(getindukPT($_SESSION['empl']['lokasitugas']) == 'PPP'){
					
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' and tipe in ('KEBUN')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$whereKary=" and lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";

	## UNTUK GROUP DMA
	}else{
	## Gini dulu urgent di kebun/ kalau sempat nanti diganti parameter aplikasi
	if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR' and tipe in ('KEBUN')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' and tipe in ('KEBUN')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$whereKary=" and lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
	}

	if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$whereKary=" and lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
		}
	}

	$opttk = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$str = "SELECT * FROM " . $dbname . ".datakaryawan where 1=1 and tipekaryawan != 0 ".$whereKary." order by namakaryawan";

	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$opttk.="<option value=" . $bar['karyawanid'] . ">" . $bar['nik'] . " - " . strtoupper($bar['namakaryawan']) . "</option>";
	}


	$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi ='BM' and kodeparameter ='BMTBS'";
	$bmkeg=fetchdata($str);


	$str = "SELECT * FROM " . $dbname . ".setup_kegiatan where kodekegiatan in (".$bmkeg[0]['nilai'].") order by kodekegiatan";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$optkendaraan.="<option value=" . $bar['kodekegiatan'] . ">" . $bar['kodekegiatan'] . " - " . strtoupper($bar['namakegiatan']) . "</option>";
	}

	@$optkontan.="<option value='KERJA'>KERJA</option>";
	@$optkontan.="<option value='KONTANAN'>KONTANAN</option>";

		$tab="";
		
		$tab.="<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>".$_SESSION['lang']['nospb']."</td>
				<td>:</td>
				<td>
					<label id='notransaksi' style='font-weight:bold'>".$notransaksi."</label>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['namakaryawan']."</td>
				<td>:</td>
				<td>
					<select class=select2 name='tkbm' style='width:200px' id='tkbm'>".$opttk."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>:</td>
				<td>
					<select class=select2 name='kendaraantk' style='width:200px' id='kendaraantk'>".$optkendaraan."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kontanan']."</td>
				<td>:</td>
				<td>
					<select class=select2 name='kontanan' style='width:200px' id='kontanan'>".$optkontan."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type='text' class='myinputtext' id='tgl_tkbm' name='tgl_tkbm' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:75px;' readonly/>
				</td>
			</tr>
			<tr>
				<td>Sesi</td>
				<td>:</td>
				<td>
					<input type='text' class='myinputtextnumber' onblur='changeosesitk()' onkeypress='return angka_doang(event)' value='1' style='width:75px' id='sesitk' />
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['janjang']."</td>
				<td>:</td>
				<td>
					<input type='text' class='myinputtextnumber' onblur='changeo()' onkeypress='return angka_doang(event)' value='0' style='width:75px' id='jjgtk' />
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['brondolan']."</td>
				<td>:</td>
				<td>
					<input type='text' class='myinputtextnumber' onblur='changeo()' onkeypress='return angka_doang(event)' value='0' style='width:75px' id='brdtk' />
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"simpanbm()\">Save</button>
				</td>
			</tr>
		</table>
		<p />";
		
		$tab.="
			<table class='sortable' cellpadding='5' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<th align='center' width=40px>No.</th>
					<th align='center' width=100px>".$_SESSION['lang']['nik2']."</th>
					<th align='center'>".$_SESSION['lang']['namakaryawan']."</th>
					<th align='center'>".$_SESSION['lang']['kegiatan']."</th>
					<th align='center'>".$_SESSION['lang']['tanggal']."</th>
					<th align='center'>Sesi</th>
					<th align='center'>".$_SESSION['lang']['janjang']."</th>
					<th align='center'>".$_SESSION['lang']['brondolan']."</th>
					<th align='center'>".$_SESSION['lang']['kontanan']."</th>
					<th align='center' width=30px>Action</th>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		";
		
		echo $tab;
	break;
	case 'showpetani':
	// kamus kud
	$str = "select a.afdeling, a.kodesupplier, b.namasupplier from ".$dbname.".kebun_5namakud a 
		left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid
		where 1 ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$kamusnamakud[$bar['afdeling']]=$bar['namasupplier'];
	}

	// ambil divisi spbdt
	$opttk = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$str = "select id, no_hamp, no_kavl, nama, afdeling from ".$dbname.".kebun_5kavling where afdeling in (
		select left(blok,6) as afdeling from ".$dbname.".kebun_spbdt where nospb = '".$notransaksi."') and aktif ='1'
		order by afdeling, no_hamp, no_kavl, nama ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$a=$kamusnamakud[$bar['afdeling']];
		if($a!=$m){			
			$opttk.="<optgroup label='".$a."'>";
		}
		
		$d=$bar['no_hamp'];
		if($d!=$n){			
			$opttk.="<optgroup label='Hamparan ".$d."'>";
		}
		$opttk.="<option value=".$bar['id'].">".$bar['no_kavl']." - ".$bar['nama']."</option>";
		// $opttk.="<option value=".$bar['id'].">".$kamusnamakud[$bar['afdeling']]." - ".$bar['no_hamp']." ".$bar['no_kavl']." - ".$bar['nama']."</option>";
		
		
		$n=$d;
		if($d!=$n){			
			$opttk.="</optgroup>";
		}
		$m=$a;
		if($a!=$m){			
			$opttk.="</optgroup>";
		}
	}
		$tab="";
		
		$tab.="<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>".$_SESSION['lang']['nospb']."</td>
				<td>:</td>
				<td>
					<label id='notransaksi' style='font-weight:bold'>".$notransaksi."</label>
				</td>
			</tr>
			<tr>
				<td>Nama Petani</td>
				<td>:</td>
				<td>
					<select name='petani' style='width:250px' class=select2 id='petani'>".$opttk."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['janjang']."</td>
				<td>:</td>
				<td>
					<input type='text' class='myinputtextnumber' onblur='changeopetani()' onkeypress='return angka_doang(event)' value='1' style='width:75px' id='jjgpetani' />
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['brondolan']."</td>
				<td>:</td>
				<td>
					<input type='text' class='myinputtextnumber' onblur='changeopetani()' onkeypress='return angka_doang(event)' value='0' style='width:75px' id='brdpetani' />
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"simpanpetani()\">Save</button>
				</td>
			</tr>
		</table>
		<p />";
		
		$tab.="
			<table class='sortable' cellpadding=5 cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center'>Hamparan</th>
					<th align='center'>Kavling</th>
					<th align='center'>".$_SESSION['lang']['nama']."</th>
					<th align='center'>".$_SESSION['lang']['janjang']."</th>
					<th align='center'>".$_SESSION['lang']['brondolan']."</th>
					<th align='center' width=30px>Action</th>
				</tr>
				</thead>
				<tbody id='listfilespetani'>
				</tbody>
			</table>
		</fieldset> ";
		
		echo $tab;
	break;	
	case'simpanbm':

		if($notransaksi == ''){
			exit("Warning : Simpan terlebih detail spb ");
		}

		#query pengecekan apakah FP aktif / tidak
		$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".$kdOrg."' and tanggal<='".tanggalsystemn(tanggalnormal($tanggal))."'";
		$res = fetchData($str);
		$statusfp    = $res[0]['status'];//1 aktif,0 tidak
		$tipevalidasi= $res[0]['tipevalidasi'];
		$detailexp   = explode(",",$res[0]['detailvalidasi']);
		foreach($detailexp as $vald){
			$detval[$vald]=$vald;
		}

		$arrUpload[]['nik'] = $tkbm;
		if($statusfp==1){
			validasifp($tipevalidasi,$detval,'PNN',$arrUpload,tanggalsystemn(tanggalnormal($tanggal)),'1');
		} else {
			if ($statusfp == '') {
				exit("Warning: Aktivasi Fingerprint belum ada<br>
						Silakan setup di menu SDM > SETUP > Aktivasi Fingerprint"
				);
			}
		}

		// Get Tanggal SPB HT
		$scek 		= "SELECT tanggal FROM {$dbname}.kebun_spbht WHERE nospb='".$notransaksi."'";
		$rcek 		= fetchData($scek)[0];
		$tglspbht	= $rcek['tanggal'];
		$exptglspbht= explode("-",$tglspbht);
		$prdtglspbht= $exptglspbht[0] . "-" . $exptglspbht[1];

		$tglmuat 	= tanggalsystemn(tanggalnormal($tanggal));
		$exptglmuat = explode("-",$tglmuat);
		$prdtglmuat = $exptglmuat[0] . "-" . $exptglmuat[1];

		// Jika periode bulan tidak sama dengan di spb header
		if ($prdtglmuat != $prdtglspbht) {
			exit("Warning: Periode bulan di tanggal muat tidak sama dengan periode bulan di tanggal spb header !");
		}

		$str="insert into ".$dbname.".kebun_spbbm (`nospb`,`karyawanid`,`kegiatan`,`tanggal`,`sesi`,`jjg_angkut`,`brondolan_angkut`,`kontanan`)
		values ('".$notransaksi."','".$tkbm."','".$kendaraantk."','".tanggalsystemn(tanggalnormal($tanggal))."','".$sesitk."','".$jjgtk."','".$brdtk."','".$kontanan."')";
		try{
			$owlPDO->exec($str); 
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	case'simpanpetani':
		// ambil dari kamus
		$sDet="select id,no_hamp,no_kavl,nama from ".$dbname.".kebun_5kavling where id='".$petani."'";
		$qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		while($rDet=$qDet->fetch()){
			$no_hamp=$rDet['no_hamp'];
			$no_kavl=$rDet['no_kavl'];
			$nama=$rDet['nama'];
		}
		$str="insert into ".$dbname.".kebun_spbpetani (`nospb`,`id_kavling`,`no_hamp`,`no_kavl`,`nama`,`janjang`,`brondolan`)
		values ('".$notransaksi."','".$petani."','".$no_hamp."','".$no_kavl."','".$nama."','".$jjgpetani."','".$brdpetani."')
		on duplicate key update `no_hamp`='".$no_hamp."', `no_kavl`='".$no_kavl."', `nama`= '".$nama."', `janjang`='".$jjgpetani."', `brondolan`='".$brdpetani."'";
		try{
			$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
	break;
	case'loadfiles':
		$no = 0;
		$tab = "";
		$str="select * from ".$dbname.".kebun_spbht where nospb = '".$notransaksi."'";
		$resv=fetchData($str);
		foreach($resv as $bar => $barv){
			$posting = $barv['posting'];	
		}
		
		$str="select a.*,b.nik,b.namakaryawan,c.namakegiatan from ".$dbname.".kebun_spbbm a 
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
		left join ".$dbname.".setup_kegiatan c on a.kegiatan=c.kodekegiatan 
		where nospb = '".$notransaksi."'";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=9 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		
		foreach($res as $bar){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['nik']."</td>";
			$tab.="<td>".$bar['namakaryawan']."</td>";
			$tab.="<td>".$bar['kegiatan']." - ".$bar['namakegiatan']."</td>";
			$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td>".$bar['sesi']."</td>";
			$tab.="<td align=right>".number_format($bar['jjg_angkut'])."</td>";
			$tab.="<td align=right>".number_format($bar['brondolan_angkut'])."</td>";
			$tab.="<td align=right>".$bar['kontanan']."</td>";
			
			if($posting==1){
				$tab.="<td></td>";
			}

			$totalJJG += $bar['jjg_angkut'];
			$totalBRD += $bar['brondolan_angkut'];
			$tab.="<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletebm('".$bar['karyawanid']."','".$bar['kegiatan']."','".tanggalnormal($bar['tanggal'])."','".$bar['sesi']."','".$notransaksi."');\"></td>";
			$tab.="</tr>";
		}
			$tab.="<tr class=rowcontent align=center>";
					$tab.="<td colspan=6><b>TOTAL</b></td>";
					$tab.="<td><b>".$totalJJG."</b></td>";
					$tab.="<td><b>".$totalBRD."</b></td>";
					$tab.="<td colspan=2></td>";
			$tab.="</tr>";
			
		echo $tab;
	break;
	case'loadfilespetani':
		$no = 0;
		$tab = "";
		$str="select * from ".$dbname.".kebun_spbht where nospb = '".$notransaksi."'";
		$resv=fetchData($str);
		foreach($resv as $bar => $barv){
			$posting = $barv['posting'];	
		}
		
		$str="select id_kavling,no_hamp,no_kavl,nama,janjang,brondolan from ".$dbname.".kebun_spbpetani a where nospb = '".$notransaksi."'";
		$res=fetchData($str);
		$kosong='';
		if(empty($res)){
			$kosong='x';
			$tab.="<tr class=rowcontent><td colspan=7 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		
		foreach($res as $bar){
			$no+=1;
			$tab.="<tr class=rowcontent>"; 
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['no_hamp']."</td>";
			$tab.="<td align=center>".$bar['no_kavl']."</td>";
			$tab.="<td>".$bar['nama']."</td>";
			$tab.="<td align=right>".number_format($bar['janjang'])."</td>";
			$tab.="<td align=right>".number_format($bar['brondolan'])."</td>";
			$total['janjang']+=$bar['janjang'];
			$total['brondolan']+=$bar['brondolan'];
			if($posting==1){
				$tab.="<td></td>";
			} 
			$tab.="<td align=center>
				<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editpetani('".$notransaksi."','".$bar['id_kavling']."','".$bar['janjang']."','".$bar['brondolan']."');\">
				<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletepetani('".$bar['id_kavling']."','".$notransaksi."');\">
			</td>";
			$tab.="</tr>";
		}
			$tab.="<tr class=rowcontent style=\"background-color:#CACFD2;font-weight:bold\">";
			$tab.="<td align=center colspan=4>TOTAL</td>";
			$tab.="<td align=right>".number_format($total['janjang'])."</td>";
			$tab.="<td align=right>".number_format($total['brondolan'])."</td>";
			
			$tab.="<td></td>";
			$tab.="</tr>";

		echo $tab."####".$kosong;
	break;
	case'deletebm':
		$str="delete from ".$dbname.".kebun_spbbm where nospb='".$notransaksi."' and karyawanid='".$tkbm."' and kegiatan='".$kendaraan."' and tanggal='".tanggalsystemn(tanggalnormal($tanggal))."' and sesi='".$sesi."'";
		try{
			$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
	break;
	case'deletepetani':
		// $qwe=explode('####',$petani); // diganti jadi id_kavling

		// $str="delete from ".$dbname.".kebun_spbpetani where nospb='".$notransaksi."' and no_hamp='".$qwe[0]."' and no_kavl='".$qwe[1]."'";
		$str="delete from ".$dbname.".kebun_spbpetani where nospb='".$notransaksi."' and id_kavling='".$petani."'";
		try{
			$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
	break;
	case'getPks':
		
		if($intex==0){
			$iPks="select * from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and"
				. " tipe='PABRIK' and namaorganisasi not like '%BULKING%'";

			$nPks=$owlPDO->query($iPks) or die(print " Gagal: ".PDOException::getMessage());
			$nPks->setFetchMode(PDO::FETCH_ASSOC);
			while($dPks=$nPks->fetch()){	
				if($pks==$dPks['kodeorganisasi']){
					$select="selected=selected";
				}else{
					$select="";
				}
				$optPks.="<option ".$select." value='".$dPks['kodeorganisasi']."'>".$dPks['namaorganisasi']."</option>";
			}
		}else if ($intex==1){
			$iPks="select * from ".$dbname.".organisasi where induk!='".$_SESSION['empl']['kodeorganisasi']."' and"
				. " tipe='PABRIK' and namaorganisasi not like '%BULKING%'";

			$nPks=$owlPDO->query($iPks) or die(print " Gagal: ".PDOException::getMessage());
			$nPks->setFetchMode(PDO::FETCH_ASSOC);
			while($dPks=$nPks->fetch()){		
				if($pks==$dPks['kodeorganisasi']){
					$select="selected=selected";
				}else{
					$select="";
				}
				
				$optPks.="<option ".$select." value='".$dPks['kodeorganisasi']."'>".$dPks['namaorganisasi']."</option>";
			}
		}else if ($intex==3){ 
			$iPks="select distinct b.* from ".$dbname.".pmn_4komoditi a left join ".$dbname.".pmn_4customer b
				ON a.kodecustomer=b.kodecustomer where a.kodebarang='40000003'  and b.kodecustomer is not null"; 
			$nPks=$owlPDO->query($iPks) or die(print " Gagal: ".PDOException::getMessage());
			$nPks->setFetchMode(PDO::FETCH_ASSOC);
			while($dPks=$nPks->fetch()){	
				if($pks==$dPks['kodecustomer']){
					$select="selected=selected";
				}else{
					$select="";
				}
				$optPks.="<option ".$select." value='".$dPks['kodecustomer']."'>".$dPks['namacustomer']."</option>";
			}
		}else if ($intex==4){
			$iPks="select * from ".$dbname.".kebun_5tphbesar where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
			$nPks=$owlPDO->query($iPks) or die(print " Gagal: ".PDOException::getMessage());
			$nPks->setFetchMode(PDO::FETCH_ASSOC);
			while($dPks=$nPks->fetch()){	
				if($pks==$dPks['kodeorganisasi']){
					$select="selected=selected";
				}else{
					$select="";
				}
				$optPks.="<option ".$select." value='".$dPks['notph']."'>".$dPks['notph']."</option>";
			}
		}else{
			$optPks.="<option value=''></option>";
		}
		echo $optPks;
		
	break;
	case'generateNo':
	$tgl=  date('Ymd');
	$bln = substr($tgl,4,2);
	$thn = substr($tgl,0,4);
	$lokasi=$_SESSION['empl']['lokasitugas'];
	$lokasi=substr($lokasi,0,4);
	$scOrg="select distinct tipe from ".$dbname.".organisasi where kodeorganisasi='".$lokasi."'";

	$qcOrg=$owlPDO->query($scOrg) or die(print " Gagal: ".PDOException::getMessage());
	$qcOrg->setFetchMode(PDO::FETCH_ASSOC);
	$rcOrg=$qcOrg->fetch();

	if(($rcOrg['tipe']=="KEBUN")||($rcOrg['tipe']=="KANWIL")){
		$nospb=$lokasi."/".date('Y')."/".date('m')."/";
		$ql="select `nospb` from ".$dbname.".`kebun_spbht` where nospb like '%".$nospb."%' order by `nospb` desc limit 0,1";

		$qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
		$qr->setFetchMode(PDO::FETCH_OBJ);
		$rp=$qr->fetch();
		$awal=substr($rp->nospb,-4,4);
		$awal=intval($awal);
		$cekbln=substr($rp->nospb,-7,2);
		$cekthn=substr($rp->nospb,-12,4);

		if($thn!=$cekthn){
			$awal=1;
		}else{
			$awal++;
		}
		$counter=addZero($awal,4);
		$nospb=$lokasi."/".$thn."/".$bln."/".$counter;
		echo $nospb;
	}else{
		echo"warning : Lokasi tugas Anda bukan di Kebun atau Traksi";
		exit();
	}
	break;
	
	case'amblBjr':
		$perlalu=periodelalu(substr($_POST['periode'],0,7));    
		
		##tambahkan pengecekan spb bulan lalu sudah terposting semua
		$iSpb="select count(*) as jumlah from ".$dbname.".kebun_spbht where kodeorg='".substr($blok,0,4)."' "
			. " and tanggal like '%".$perlalu."%' and posting=0 ";

		$nSpb=$owlPDO->query($iSpb) or die(print " Gagal: ".PDOException::getMessage());
		$nSpb->setFetchMode(PDO::FETCH_ASSOC);
		$dSpb=$nSpb->fetch();
			$belumposting=$dSpb['jumlah'];

		if($belumposting>0){
			//exit("Warning : Ada nomor spb yang belum di posting untuk ".substr($blok,0,4)." ");
		}
		
		#bentuk bjr dari tabel bjr    
		$sStpBlok="select bjr from ".$dbname.".kebun_5bjr where kodeorg='".$blok."' order by periode desc limit 1";
		$qStpBlok=$owlPDO->query($sStpBlok) or die(print " Gagal: ".PDOException::getMessage());
		$qStpBlok->setFetchMode(PDO::FETCH_ASSOC);
		$rStpBlok=$qStpBlok->fetch();
		$bjrtabel=$rStpBlok['bjr'];
		$isiBjr=$bjrtabel;
			## GET TPH
			$opttph.="<option value=''></option>";
			$str="select kode from ".$dbname.".kebun_5tph where kodeorg='".$blok."' order by kode asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if($param['tphdt']==$val['kode']){
					$opttph.="<option value='".$val['kode']."' selected>".substr($val['kode'],9,3)."-".$val['kode']."</option>";				
				}else{
					$opttph.="<option value='".$val['kode']."'>".substr($val['kode'],9,3)."-".$val['kode']."</option>";				
				}
			}
			
			echo number_format($isiBjr,2)."####".$opttph;
	break;
	
	case'cekData':
	try {
	$owlPDO->beginTransaction();

	// cek apakah sudah ada datanya
	$sCek="select nospb from ".$dbname.".kebun_spbdt where nospb='".$param['noSpb']."' and blok='".$param['blok']."' and pemanen='".$param['pemanendt']."'
	and tph='".$param['tphdt']."' and sesi='".$param['sesidt']."' and qrcode='".$param['noreferensidt']."' "; //echo "warning".$sCek;nospb
	$qCek=fetchdata($sCek);
	$rCek=count($qCek);
	if($rCek>0){
		exit("warning : Data sudah ada... ");
	}
	
	if($kgwb!=''){
		$kgwb=0;
	}
	
	$stsblok = makeOption($dbname,'setup_blok','indukblok,intiplasma',"indukblok='".$blok."'");
	if($stsblok[$blok]=='I'){		
		if($tglpanen=='--' or $tglpanen==''){
			throw new PDOException("Tanggal panen wajib diisi !");
		}
	}
	
	
	// $str=" select sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn_vw where blok='".$blok."'
	// 		and tanggal = '".$tglpanen."' and (posting='1' or posting='0') ";
	$str=" select sum(jjgbuahbesar) as jjgpanen from ".$dbname.".kebun_prestasi_vw where kodeorg='".$blok."'
			and tanggal = '".$tglpanen."'";
			 #exit("error".$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$jjgpanen=$bar['jjgpanen'];
		$jjgafkir=$bar['jjgafkir'];	

	$str=" select sum(jjg) as jjg from ".$dbname.".kebun_spbdt where blok='".$blok."' and tanggalpanen = '".$tglpanen."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$jjgspbvw=$bar['jjg'];
		
	$cekdata=$jjgpanen-$jjgafkir-$jjgspbvw-$jjngHsl;
	$jjgtersedia=$jjgpanen-$jjgafkir-$jjgspbvw;

	
	/* $str=" select * from ".$dbname.".kebun_spb_vw where blok='".$blok."' and tanggal > '".$tglpanen."'"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$err='';
	while($bar=$res->fetch()){
		$err.="No SPB =>".$bar['nospb'].", tanggal => ".$bar['tanggal']."\n";

	}
	if(!empty($err)){
		throw new PDOException("Blok ".$blok." sudah pernah diinput di tanggal yang lebih besar\n".$err."untuk melanjutkan silahkan hapus blok ".$blok." pada No SPB diatas !!!");
	}
	*/
	
	// #jika plasma boleh tidak pakai rekap panen
	// if($cekdata<0 and $stsblok[$blok]=='I'){
	// 	throw new PDOException("Jjg yg anda input melebihi jumlah Jjg yang tersedia.<br><br>Jjg tersedia (Restan + Panen - Kirim) = ".$jjgtersedia." Jjg<br><br>Silahkan Input dan Posting Jjg Panen melalui menu Kebun - Transaksi - Rekap Panen per Blok.");
	// }
	
	if($stsblok[$blok]=='I' and $kegiatan==''){
		#throw new PDOException("Kegiatan harus diisi !");
	}
	if($intex!='3'){
		validasiInput(substr($kdOrg,0,4),substr($blok,0,6),'spb',$tanggal,$exit='0');
	}
	
	$sCek="select nospb from ".$dbname.".kebun_spbht where nospb='".$noSpb."'"; //echo "warning".$sCek;nospb
	$qCek=fetchdata($sCek);
	$rCek=count($qCek);
	if($rCek<1){
		$sIns="insert into ".$dbname.".kebun_spbht (`nospb`,`noreferensi`, `kodeorg`, `tanggal`,`updateby`,`tujuan`,`penerimatbs`,`kerani`,`kontanan`,`tahuntanam`) values ('".$noSpb."','".$referensimb."','".$kdOrg."','".$tanggal."','".$user_online."','".$intex."','".$pks."','".$kerani."','".$kontanan."','".$tahuntanam."')"; 
		$owlPDO->exec($sIns); 
		

		$kgBjr=($jjngHsl*$bjrHsl);
		$sDetIns="insert into ".$dbname.".kebun_spbdt (nospb, qrcode,tanggalpanen,blok,tph,sesi,pemanen, jjg, bjr, brondolan, mentah, busuk, matang, lewatmatang,kgbjr,kgwb,kegiatan) values ('".$noSpb."','".$param['noreferensidt']."','".$tglpanen."','".$blok."','".$param['tphdt']."','".$param['sesidt']."','".$param['pemanendt']."','".$jjngHsl."','".$bjrHsl."','".$brondolanHsl."','".$mentah."','".$busuk."','".$matang."','".$lwtmatang."','".$kgBjr."','".$kgwb."','".$kegiatan."')";
		$owlPDO->exec($sDetIns); 
		

	}else{
		$cekPost="select distinct posting from ".$dbname.".kebun_spbht where nospb='".$noSpb."'";
		$qcekPost=$owlPDO->query($cekPost) or die(print " Gagal: ".PDOException::getMessage());
		$qcekPost->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=$qcekPost->fetch();
		if($rCek['posting']!=0){
			throw new PDOException("Nomor SPB Sudah di Posting");
		}
		$kgBjr=($jjngHsl*$bjrHsl);
		
		$sDetIns="insert into ".$dbname.".kebun_spbdt (nospb,qrcode,tanggalpanen, blok,tph,sesi,pemanen, jjg, bjr, brondolan, mentah, busuk, matang, lewatmatang,kgbjr,kgwb,kegiatan)
		values ('".$noSpb."','".$param['noreferensidt']."','".$tglpanen."','".$blok."','".$param['tphdt']."','".$param['sesidt']."','".$param['pemanendt']."','".$jjngHsl."','".$bjrHsl."','".$brondolanHsl."','".$mentah."','".$busuk."','".$matang."','".$lwtmatang."','".$kgBjr."','".$kgwb."','".$kegiatan."')";
		$owlPDO->exec($sDetIns); 

	}
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
	case'donwloadmobile':
	$databelumsingkronfull=array();
	$datatujuan=array();
	## Untuk DMA Ambil Semua Tujuannya
	if(getindukPT($_SESSION['empl']['lokasitugas']) != 'PPP'){

		## Ambil Detail Akses
		$dataspb1=array();
		$str = "select a.nospb,a.kodeorg,a.tanggal,a.penerimatbs,b.blok,b.nik,b.nospbref,b.tph,b.sesi,b.jjg,b.brondolan,b.tanggalpanen,b.nospbref,a.tujuan from " . $dbname2 . ".kebun_spbht_mobile a 
		left join " . $dbname2 . ".kebun_spbdt_mobile b on a.nospb=b.nospb
		where a.syn='1' and a.flag='0' and a.kodeorg in (".getOrgDetail(2).")";
		$res = fetchdata($str);
		foreach($res as $val){
			if($val['tujuan']=='0'){
				$datatujuan[$val['nospb']]='0';
			}elseif($val['tujuan']=='1'){
				$datatujuan[$val['nospb']]='1';
			}elseif($val['tujuan']=='2'){
				$datatujuan[$val['nospb']]='4';
			}elseif($val['tujuan']=='3'){
				$datatujuan[$val['nospb']]='3';
			}

			if($val['nospbref']!=''){
				$strxz = "select a.nospb,a.kodeorg,a.tanggal,a.penerimatbs,b.blok,b.nik,b.nospbref,b.tph,b.sesi,b.jjg,b.brondolan,b.tanggalpanen,a.syn from " . $dbname2 . ".kebun_spbht_mobile a 
				left join " . $dbname2 . ".kebun_spbdt_mobile b on a.nospb=b.nospb
				where a.nospb='".$val['nospbref']."'";
				$resxz = fetchdata($strxz);
				foreach($resxz as $valxz){
					if($valxz['syn']=='1'){
						$dataspb1[$val['nospb']][$val['kodeorg']][$val['tanggal']][$val['penerimatbs']][$valxz['tanggalpanen']][$valxz['nik']][$valxz['blok']][$valxz['tph']][$valxz['sesi']]['jjg']=$valxz['jjg'];
						$dataspb1[$val['nospb']][$val['kodeorg']][$val['tanggal']][$val['penerimatbs']][$valxz['tanggalpanen']][$valxz['nik']][$valxz['blok']][$valxz['tph']][$valxz['sesi']]['brondolan']=$valxz['brondolan'];
					}else{
							$databelumsingkronfull[$val['nospb']]=$val['nospb'];					
					}
				}

			}else{
				$dataspb1[$val['nospb']][$val['kodeorg']][$val['tanggal']][$val['penerimatbs']][$val['tanggalpanen']][$val['nik']][$val['blok']][$val['tph']][$val['sesi']]['jjg']=$val['jjg'];
				$dataspb1[$val['nospb']][$val['kodeorg']][$val['tanggal']][$val['penerimatbs']][$val['tanggalpanen']][$val['nik']][$val['blok']][$val['tph']][$val['sesi']]['brondolan']=$val['brondolan'];
			}
		}


		$dataspb2=array();
		$str = "select a.nospb,b.karyawanid,b.jjg,b.brondolan,b.sesi,b.kegiatan from " . $dbname2 . ".kebun_spbht_mobile a 
		left join " . $dbname2 . ".kebun_spbtkbm_mobile b on a.nospb=b.nospb
		where a.syn='1' and a.flag='0' and a.kodeorg in (".getOrgDetail(2).")";
		$res = fetchdata($str);
		foreach($res as $val){
			$dataspb2[$val['nospb']][$val['karyawanid']][$val['sesi']][$val['kegiatan']]['jjg']=$val['jjg'];
			$dataspb2[$val['nospb']][$val['karyawanid']][$val['sesi']][$val['kegiatan']]['brondolan']=$val['brondolan'];
		}

	}else{
		$dataspb1=array();
		$str = "select a.nospb,a.kodeorg,a.tanggal,a.penerimatbs,b.blok,b.nik,b.nospbref,b.tph,b.sesi,b.jjg,b.brondolan,b.tanggalpanen from " . $dbname2 . ".kebun_spbht_mobile a 
		left join " . $dbname2 . ".kebun_spbdt_mobile b on a.nospb=b.nospb
		where a.tujuan='2' and a.syn='1' and a.flag='0' ";
		$res = fetchdata($str);
		foreach($res as $val){
			if($val['tujuan']=='0'){
				$datatujuan[$val['nospb']]='0';
			}elseif($val['tujuan']=='1'){
				$datatujuan[$val['nospb']]='1';
			}elseif($val['tujuan']=='2'){
				$datatujuan[$val['nospb']]='4';
			}elseif($val['tujuan']=='3'){
				$datatujuan[$val['nospb']]='3';
			}
			$dataspb1[$val['nospb']][$val['kodeorg']][$val['tanggal']][$val['penerimatbs']][$val['tanggalpanen']][$val['nik']][$val['blok']][$val['tph']][$val['sesi']]['jjg']=$val['jjg'];
			$dataspb1[$val['nospb']][$val['kodeorg']][$val['tanggal']][$val['penerimatbs']][$val['tanggalpanen']][$val['nik']][$val['blok']][$val['tph']][$val['sesi']]['brondolan']=$val['brondolan'];
		}

		$dataspb2=array();
		$str = "select a.nospb,b.karyawanid,b.jjg,b.sesi,b.kegiatan from " . $dbname2 . ".kebun_spbht_mobile a 
		left join " . $dbname2 . ".kebun_spbtkbm_mobile b on a.nospb=b.nospb
		where a.tujuan='2' and a.syn='1' and a.flag='0' ";
		$res = fetchdata($str);
		foreach($res as $val){
			$dataspb2[$val['nospb']][$val['karyawanid']][$val['sesi']][$val['kegiatan']]['jjg']=$val['jjg'];
		}
	}
	

	$nospbbaruxx=array();
	foreach ($dataspb1 as $nospbx => $key1) {
		foreach ($key1 as $unitkodeorg => $key2) {
			foreach ($key2 as $tanggal => $key3x) {
				foreach ($key3x as $penerimatbs => $key3) {
					$tgl=  $tanggal;
					$bln = substr($tgl,5,2);
					$thn = substr($tgl,0,4);
					$lokasi=$unitkodeorg;
					$lokasi=substr($lokasi,0,4);
					$scOrg="select distinct tipe from ".$dbname.".organisasi where kodeorganisasi='".$lokasi."'";

					$qcOrg=$owlPDO->query($scOrg) or die(print " Gagal: ".PDOException::getMessage());
					$qcOrg->setFetchMode(PDO::FETCH_ASSOC);
					$rcOrg=$qcOrg->fetch();

						$nospb="/".$lokasi."/".$bln."/".$thn;
						$ql="select `nospb` from ".$dbname.".`kebun_spbht` where nospb like '%".$nospb."%' order by `nospb` desc limit 0,1";
						$qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
						$qr->setFetchMode(PDO::FETCH_OBJ);
						$rp=$qr->fetch();
						$awal=substr($rp->nospb,0,4);
						$awal=intval($awal);
						$cekbln=substr($rp->nospb,-7,2);
						$cekthn=substr($rp->nospb,-4,4);
						//echo $cekthn.'xxx';

						if($thn!=$cekthn){
							$awal=1;
						}else{
							$awal++;
						}
						$counter=addZero($awal,4);
						$nospbbaru=$counter."/".$lokasi."/".$bln."/".$thn;

					if(!isset($databelumsingkronfull[$nospbx])){
						$sIns="insert into ".$dbname.".kebun_spbht (`nospb`,`noreferensi`, `kodeorg`, `tanggal`,`updateby`,`tujuan`,`penerimatbs`,`kerani`,`kontanan`,`tahuntanam`) values ('".$nospbbaru."','".$nospbx."','".$unitkodeorg."','".$tanggal."','".$user_online."','".$datatujuan[$nospbx]."','".$penerimatbs."','','KERJA','0')"; 
						$owlPDO->exec($sIns);  
						$nospbbaruxx[$nospbx]=$nospbbaru;
						$str = "update ".$dbname2.".kebun_spbht_mobile set flag='1' where nospb ='".$nospbx. "'";
						$owlPDO->exec($str);
						foreach ($key3 as $tanggalpanen => $key4) {
							foreach ($key4 as $nik => $key5) {
								foreach ($key5 as $blok => $key6) {
									foreach ($key6 as $tph => $key7) {
										foreach ($key7 as $sesi => $val) {
											$sDetIns="insert into ".$dbname.".kebun_spbdt (nospb, qrcode,tanggalpanen,blok,tph,sesi,pemanen, jjg, bjr, brondolan, mentah, busuk, matang, lewatmatang,kgbjr,kgwb,kegiatan) values ('".$nospbbaru."','".$nospbref."','".$tanggalpanen."','".$blok."','".$tph."','".$sesi."','".$nik."','".$val['jjg']."','0','".$val['brondolan']."','0','0','0','0','0','0','')";
											$owlPDO->exec($sDetIns); 
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	foreach ($dataspb2 as $nospx => $key) {
		foreach ($key as $karyawanid => $key2) {
			foreach ($key2 as $sesi => $key3) {
				foreach ($key3 as $kegiatan => $val) {
					if(!isset($databelumsingkronfull[$nospx])){
						if(isset($nospbbaruxx[$nospx])){
							$str="insert into ".$dbname.".kebun_spbbm (`nospb`,`karyawanid`,`kegiatan`,`sesi`,`jjg_angkut`,`brondolan_angkut`,`kontanan`)
							values ('".$nospbbaruxx[$nospx]."','".$karyawanid."','".$kegiatan."','".$sesi."','".$val['jjg']."','".$val['brondolan']."','KERJA')";
							$owlPDO->exec($str); 
						}
					}
				}
			}
		}
	}
	
	break;
	case'loadNewData':


	$lokasi=$_SESSION['empl']['lokasitugas'];

	$limit=15;
	$page=0;
	if(isset($_POST['page'])){
		$page=intval($_POST['page']);
		if($page<0)
		$page=0;
	}

	$offset=@($page*$limit);
	$maxdisplay = @($page * $limit);
	
	$wherenew='';
	if($txtTgl!=''){
		$wherenew.=" and tanggal='".$txtTgl."' ";
	}
	if($txtDiv!=''){
		$wherenew.=" and nospb in (select nospb from ".$dbname.".kebun_spbdt where blok like '%".$txtDiv."%' ) ";
	}

	if($txtSearch!=''){
		$wherenew.=" and nospb like '%".$txtSearch."%' ";
	}

	if($status_spb != ''){
		$wherenew.=" and tujuan = '".$status_spb."' ";
	}

	if($referensisearch != ''){
		$wherenew.=" and noreferensi = '".$referensisearch."' ";
	}
	
	

	$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_spbht where `kodeorg` IN (".getOrgDetail(2).") ".$wherenew." order by tanggal desc";// echo $ql2;
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){	
		$jlhbrs= $jsl->jmlhrow;
	}
	
	$nopol=$supir=array();
	$str = "select nokendaraan,supir,nospb from " . $dbname . ".pabrik_timbangan where nospb in (select nospb from ".$dbname.".kebun_spbht where `kodeorg` IN (".getOrgDetail(2)."))";
	$res = fetchdata($str);
	foreach($res as $val){
		$nopol[$val['nospb']]=$val['nokendaraan'];
		$supir[$val['nospb']]=$val['supir'];
	}
	
	
	$intex=array("4"=>"TPH Besar","3"=>"External","0"=>"Internal","1"=>"Afiliasi");
	
	$no=0;
	$no = $maxdisplay;
	$slvhc="select * from ".$dbname.".kebun_spbht where kodeorg IN (".getOrgDetail(2).") ".$wherenew." order by tanggal desc limit ".$offset.",".$limit."";
	$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
	$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
	while($rlvhc=$qlvhc->fetch()){	
		$no+=1;
		$tgl=explode('-',tanggalnormal($rlvhc['tanggal']));
		$tglThn=$tgl[2];
		$tglBln=$tgl[1];
		$periode=$tglThn."-".$tglBln;

		$scek="select distinct * from ".$dbname.".kebun_spbdt where nospb='".$rlvhc['nospb']."' and substr(nospb,9,6)<>left(blok,6)";
		$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
		$rcek=owlBaris($qcek);
		
		$str="select sum(jjg) as tjjg, sum(kgwb) as kgwb, sum(kgwbnetto) as kgwbnetto, divisi from ".$dbname.".kebun_spb_vw where nospb='".$rlvhc['nospb']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
	
		$strv="select * from ".$dbname.".kebun_spbbm a where nospb='".$rlvhc['nospb']."'";
		$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
		$resv->setFetchMode(PDO::FETCH_ASSOC);
		$tkbm=array();
		$nokar='0';
		while($barv=$resv->fetch()){
			$nokar+=1;
			$tkbm[getNamaKaryawan($barv['karyawanid'])]=$nokar.". ".getNamaKaryawan($barv['karyawanid']);
		}

		// ambil divisi
		$str="select divcode,beratmasuk,beratkeluar,beratbersih,kgpotsortasi from ".$dbname.".pabrik_timbangan where nospb ='".$rlvhc['nospb']."'  limit 1";
		$getdivv=fetchdata($str);
		$idDivv = $getdivv[0]['divcode'];
		$bruto = $getdivv[0]['beratmasuk'] - $getdivv[0]['beratkeluar'] ;
		$netto = $getdivv[0]['beratbersih'];
		$sortasi = $getdivv[0]['kgpotsortasi'];

		if($idDivv == ''){
			$idDivv = $bar['divisi'];
		}
		
		$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$rlvhc['updateby']."'");
		$tab.="
		<tr class=rowcontent  id=tr_$no>
		<td valign=top align=center>".$no."</td>
		<td valign=top align=center>".$rlvhc['nospb']."</td>
		<td valign=top align=center>".$rlvhc['noreferensi']."</td>
		<td valign=top align=center>".$intex[$rlvhc['tujuan']]."</td>
		<td valign=top align=center>".tanggalnormal($rlvhc['tanggal'])."</td>
		<td valign=top align=center>".getNamaOrg($rlvhc['kodeorg'])."</td>
		<td valign=top align=left>".getNamaOrg($idDivv)."</td>
		<td valign=top align=center>".$nopol[$rlvhc['nospb']]."</td>
		<td valign=top align=left>".$supir[$rlvhc['nospb']]."</td>
		<td valign=top hidden align=center>".$rlvhc['kontanan']."</td>
		<td valign=top align=right>".number_format($bar['tjjg'])."</td>
		<td valign=top align=right>".number_format($bruto,2)."</td>
		<td valign=top align=right>".number_format($sortasi,2)."</td>
		<td valign=top align=right>".number_format($netto,2)."</td>
		<td valign=top align=left style=max-width:200px>".implode('<br>',$tkbm)."</td>
		<td valign=top align=left>".$nmkary[$rlvhc['updateby']]."</td>";
		
		foreach ($lstunitx as $key => $kdrg) {
			$unitx = $kdrg;
		}

		if($rlvhc['posting']==0 and $rlvhc['tujuan']=='4'){
			if(in_array($_SESSION['empl']['jabatan'],$jab)){
				$tab.="<td valign=top align=center width=30px>
				<img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30' onclick=\"postingDatanih('".$rlvhc['nospb']."'); title='Belum Di Posting';\" >
				</td>";
			}else{
				$tab.="<td></td>";
			}
		}elseif($rlvhc['posting']==0 and $rlvhc['tujuan']!='4'){
			$tab.="<td></td>";
		}else{
			if(in_array($_SESSION['empl']['jabatan'],$jab)){
				$icon="images/icons/04/16/04.png";
				$title="Posted";
				// $unpost="onclick=\"unpostingData('".$rlvhc['nospb']."','".$rlvhc['tujuan']."');\" ";
				if($rlvhc['tujuan']!='4'){
					$unpost="onclick=\"unposting('".$rlvhc['nospb']."','".$rlvhc['kodeorg']."');\" ";
				}else{
					$unpost="onclick=\"unpostingnih('".$rlvhc['nospb']."');\" ";
				}
				$tab.="<td valign=top align=center  width=30px>
						<img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." >
				</td>";
			}else{
				$icon="images/icons/04/16/02.png";
				$title="Posted";
				$unpost='';
				$tab.="<td valign=top align=center  width=30px>
						<img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." >
					</td>";

			}
		}
					
		# if($rlvhc['updateby']==$user_online){
		$hapus="";
		if($_SESSION['empl']['subbagian']!='' and $rlvhc['updateby']!=$user_online){
			$hapus="";
		}else{
			$hapus=" onclick=\"delData('".$rlvhc['nospb']."');\" ";
		}

		$strv="select id from ".$dbname.".kebun_spbpetani where nospb='".$rlvhc['nospb']."'";
		$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
		$resv->setFetchMode(PDO::FETCH_ASSOC);
		$adapetani='0';
		while($barv=$resv->fetch()){
			$adapetani+=$barv['id'];
		}
		
		if($rlvhc['posting']==0){

			$tab.="
			<td valign=top align=center width=30px>
				<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('".$rlvhc['nospb']."',
			'".tanggalnormal($rlvhc['tanggal'])."','1','".$periode."','".$rcek."','".$rlvhc['tujuan']."','".$rlvhc['penerimatbs']."','".$rlvhc['kerani']."','".$rlvhc['tahuntanam']."','".$rlvhc['noreferensi']."');\">
			</td>";

			$tab.="
			<td valign=top align=center width=30px>
				<img src=images/application/application_delete.png class=zImgBtn  title='Delete' ".$hapus.">
			</td>";

			$tab.="
			<td valign=top align=center width=30px>
				<img src=images/pdf.jpg class=zImgBtn  title='Print SPB' onclick=\"masterPDF('kebun_spbht','".$rlvhc['nospb']."','','kebun_spbPdf',event)\">
			</td>";

		}else{
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td></td>";
		}


		if($adapetani>0){
			$tab.=" <img src=images/pdf_gray.jpg class=zImgBtn  title='Print SPTBS' onclick=\"masterPDF('kebun_spbht','".$rlvhc['nospb']."','','kebun_spbPdfPetani',event)\">";
		}
		$tab.="</td><td valign=top align=center width=30px>
			<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"previewdata('".$rlvhc['nospb']."',event)\">
		</td>";

		## Proporsi Tahun Tanam (HANYA UNTUK DMA DAN TEMAN - TEMAN NYA, PALMA GAK BOLEH IKUT)
		if(getindukPT($_SESSION['empl']['lokasitugas']) != 'PPP'){

			$flagsimpan=makeOption($dbname,'kebun_proporsitahuntanam_spb','notransaksi,flag');

			if ($flagsimpan[$rlvhc['nospb']] == '1') {
				$st = "Saved";
				$stylex = "style='color:green';";
			} else {
				$st = "Not Saved";
				$stylex = "style='color:red';";
			}


			$tab.="<td ".$stylex." valign=top align=center><img src='images/plus.png' class=zImgBtn class=zImgBtn height='30'  title='Proporsi JJG' onclick=\"proporsitahuntanam('".$rlvhc['nospb']."');\" > <br> <span > <b> ".$st." </b> </span></td>";
		}else{
			$isi.="<td></td>";
		}

		$tab.="</td><td valign=top align=center width=30px>
			<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview detail' onclick=\"previewdata2('".$rlvhc['nospb']."','html',event)\">
			<img src=images/excel.jpg class=zImgBtn title='MS.Excel'onclick=\"previewdata2('".$rlvhc['nospb']."','excel',event)\">
		</td>";

	
	}
	
	$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr>
                     <tr><td colspan=20 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd.="<button class=mybutton onclick=loadData(" . ($page - 1) . ");>Prev</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd.="<button class=mybutton onclick=loadData(" . ($page + 1) . ");>Next</button>";
        }
        $footd.="</td>
            </tr>";
		echo $tab."####".$footd;	
	break;

	case'proporsitahuntanam':
		
		$flagsimpan=makeOption($dbname,'kebun_proporsitahuntanam_spb','notransaksi,flag');

        if($flagsimpan[$param['notransaksi']] == '1'){
            $status = "Data Saved";
            $st = "style='text-align:center;background-color:green'";
        }else{
            $status = "Not Saved";
            $st = "style='text-align:center;background-color:red'";
        }


        $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
            $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
            $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$param['notransaksi']."</td></tr>";
            $tab.="<tr ".$st."><td>".$_SESSION['lang']['status']."</td><td> :</td><td> ".$status."</td></tr>";
        $tab.="</tbody></table><br>";


        $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%><thead>";
        $tab.="<tr class=rowheader>";
            $tab.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['tanggal']." Spb</th>";
            $tab.="<th align=center>".$_SESSION['lang']['tanggal']." Panen</th>";
            $tab.="<th align=center>".$_SESSION['lang']['blok']." Besar</th>";
            $tab.="<th align=center>".$_SESSION['lang']['jjg']." SPB</th>";
            $tab.="<th align=center>".$_SESSION['lang']['brondolan']." SPB</th>";
            $tab.="<th align=center>".$_SESSION['lang']['blok']." Kecil</th>";
            $tab.="<th align=center>".$_SESSION['lang']['tahuntanam']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['jjg']." Proporsi</th>";
            $tab.="<th align=center>".$_SESSION['lang']['brondolan']." Proporsi</th>";
            $tab.="<th align=center>Action</th>";
        $tab.="</tr></thead><tbody>";

        $nour = '';
        ## Inputan proporsi panen (DMA)
        $str="select *,sum(jjg) as jumlahjjg, sum(brondolan) as jumlahbrondolan from ".$dbname.".kebun_spb_vw where nospb='".$param['notransaksi']."' group by blok ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);        
		while($bar=$res->fetch()){

			## Ambil Blok kecil
			$optBlokkecil="<option value=''>Pilih Data</option>";
			$strx="select * from ".$dbname.".setup_blok where indukblok ='".$bar['blok']."'";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);        
			while($barx=$resx->fetch()){
				$optBlokkecil.="<option value='".$barx['kodeorg']."'>".$barx['kodeorg']."</option>";
			}

            $nour++;
            $tab.="<tr class=rowcontent>";
                $tab.="<td >".$nour."</td>";
                $tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
                $tab.="<td align=center>".tanggalnormal($bar['tanggalpanen'])."</td>";
                $tab.="<td >".getNamaOrg($bar['blok'])."</td>";
                $tab.="<td hidden id=inputblok_".$nour.">".getNamaOrg($bar['blok'])."</td>";
                $tab.="<td align=right>".$bar['jumlahjjg']."</td>";
                $tab.="<td align=right>".number_format($bar['jumlahbrondolan'],2)."</td>";
				$tab.="<td align=center><select style=\"width:150px;\"  onchange=\"gettahuntanam(".$nour.")\" id=blokkecil_".$nour.">".$optBlokkecil."</select></td>";
                $tab.="<td  align=center><input disabled id=inputtahuntanam_".$nour." maxlength=4 class=myinputtextnumber placeholder='Tahun Tanam'onkeypress=\"return angka_doang(event)\" style=\"width:99%;align:center;\"></td>";
                $tab.="<td align=center><input id=inputjjgproporsi_".$nour." value='0' class=myinputtextnumber placeholder='JJG'onkeypress=\"return angka_doang(event)\" style=\"width:99%;align:center;\"></td>";
                $tab.="<td align=center><input id=inputbrondolanproporsi_".$nour." value='0' class=myinputtextnumber placeholder='Brondolan'onkeypress=\"return angka_doang(event)\" style=\"width:99%;align:center;\"></td>";
                if($bar['posting'] == '0'){
                    $tab.="<td align=center><img src='images/plus.png' class='zImgBtn' title='Save'; onclick=addproporsijjg('".$nour."','".$bar['nospb']."','".$bar['tanggalpanen']."','".$bar['blok']."'); style='position:relative;top:3px;left:3px;'></td>";
                }else{
                    $tab.="<td></td>";
                }
            $tab.="</tr>";

            $strx="select * from ".$dbname.".kebun_proporsitahuntanam_spb where notransaksi='".$bar['nospb']."' and tanggal ='".$bar['tanggalpanen']."' and kodeorg='".$bar['blok']."'";
				$resx=fetchdata($strx);
				if(count($resx) > 0){
                    $nour2 =1;
					foreach($resx as $valx){
                        $tab.="<tr style='text-align:center;background-color:#50edd2' class='rowcontent'>";
                            $tab.="<td colspan =6></td>";
                            $tab.="<td align=right>".$valx['blokkecil']."</td>";
                            $tab.="<td align=right>".$valx['tahuntanam']."</td>";
                            $tab.="<td align=right>".$valx['jjg']."</td>";
                            $tab.="<td align=right>".number_format($valx['brondolan'],2)."</td>";
                                if($bar['posting'] == '0'){
                                    $tab.="<td align=center><img src=images/application/application_delete.png class='zImgBtn' title='Delete' onclick=deleteproporsijjg('".$valx['id']."','".$bar['nospb']."'); style='position:relative;top:3px;left:3px;'></td>";
                                }else{
                                    $tab.="<td></td>";
                                }
                        $tab.="</tr>";

                        $ttljjgpro[$bar['notransaksi']][$bar['tanggalpanen']][$bar['kodeorg']][$nour] +=$valx['jjg'];
                        $ttljjgbro[$bar['notransaksi']][$bar['tanggalpanen']][$bar['kodeorg']][$nour] +=$valx['brondolan'];
                    }
                     $tab.="<tr style='text-align:center;background-color:cyan' class=rowcontent>";
                            $tab.="<td colspan=8><b>TOTAL [ ".$bar['blok']." ]</b></td>";
                            $tab.="<td align=center><b>".number_format($ttljjgpro[$bar['notransaksi']][$bar['tanggalpanen']][$bar['kodeorg']][$nour] ,2)."</b></td>";
                            $tab.="<td align=center><b>".number_format($ttljjgbro[$bar['notransaksi']][$bar['tanggalpanen']][$bar['kodeorg']][$nour] ,2)."</b></td>";
                            $tab.="<td></td>";
                    $tab.="</tr>";

					$gttotalprojjg +=$ttljjgpro[$bar['notransaksi']][$bar['tanggalpanen']][$bar['kodeorg']][$nour];
					$gttotalprobrd +=$ttljjgbro[$bar['notransaksi']][$bar['tanggalpanen']][$bar['kodeorg']][$nour];
                }

                @$totJanjangx+=$bar['jumlahjjg'];
                @$totBrondolx+=$bar['jumlahbrondolan'];

					if($bar['posting'] == '1'){
						$hdd = 'hidden';
					}else{
						$hdd = '';
					}

				 }

        $tab.="<tr class=rowcontent>";
                $tab.="<td align=center colspan=4><b>GRAND TOTAL</b></td>";
                $tab.="<td align=center><b>".number_format($totJanjangx,2)."</b></td>";
                $tab.="<td align=center><b>".number_format($totBrondolx,2)."</b></td>";
                $tab.="<td colspan=2></td>";
				$tab.="<td align=center><b>".number_format($gttotalprojjg,2)."</b></td>";
                $tab.="<td align=center><b>".number_format($gttotalprobrd,2)."</b></td>";
                $tab.="<td ></td>";
        $tab.="</tr>";
        $tab.="<tr ".$hdd." class=rowcontent>";
                $tab.="<td align=center colspan=13><button id=tomboldetail class=mybutton onclick=saveproporsi('".$param['notransaksi']."')>" . $_SESSION['lang']['save'] . "</button></td>";
        $tab.="</tr>";


        $tab.="</tbody></table>";


        echo $tab;
	break;

	case'saveproporsi':
        $notransaksi             = $param['notransaksi'];

        $str="select sum(jjg) as jumlahjjg, sum(brondolan) as jumlahbrondolan from ".$dbname.".kebun_spb_vw where nospb='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);        
        while($bar=$res->fetch()){
            $totalJanjangpanen = $bar['jumlahjjg'];
            $totalBrondolanpanen = $bar['jumlahbrondolan'];
        }

        $strx="select sum(jjg) as jumlahjjgpro, sum(brondolan) as jumlahbropro from ".$dbname.".kebun_proporsitahuntanam_spb where notransaksi='".$notransaksi."'";
        $resx=fetchdata($strx);
        foreach($resx as $valx){
            $totaljjgpro = $valx['jumlahjjgpro'];
            $totalbropro = $valx['jumlahbropro'];
        }

        if($totalJanjangpanen != $totaljjgpro ){
            exit("Warning : Total JJG PROPORSI = ".$totaljjgpro." tidak sama dengan Total JJG SPB = ".$totalJanjangpanen."");
        }

        if($totalBrondolanpanen != $totalbropro ){
            exit("Warning : Total BRONDOLAN PROPORSI = ".$totalbropro." tidak sama dengan Total BRONDOLAN SPB = ".$totalBrondolanpanen." ");
        }

        try {
            $owlPDO->beginTransaction();
             
            $str = "update " . $dbname . ".kebun_proporsitahuntanam_spb set flag = '1' where notransaksi = '".$notransaksi."'";
			$owlPDO->exec($str);

            $owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
    break;

	case'deleteproporsijjg':
		$noid             = $param['noid'];
        $notransaksi      = $param['notransaksi'];

        try {
            $owlPDO->beginTransaction();
                $str = "delete from " . $dbname . ".kebun_proporsitahuntanam_spb where id='".$noid."'";
                $owlPDO->exec($str);

                $str = "update " . $dbname . ".kebun_proporsitahuntanam_spb set flag = '0' where notransaksi = '".$notransaksi."'";
			    $owlPDO->exec($str);

            $owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
	break;

	case 'addproporsijjg':

        $notransaksi             = $param['notransaksi'];
        $tanggal                 = $param['tanggal'];
        $indukblok               = $param['kodeorg'];
        $karyawanid              = $param['karyawanid'];
        $inputjjgproporsi        = $param['inputjjgproporsi'];
        $inputbrondolanproporsi  = $param['inputbrondolanproporsi'];
        $inputtahuntanam         = $param['inputtahuntanam'];
        $inputblokkecil          = $param['inputblokkecil'];

		if($inputjjgproporsi == ''){
			$inputjjgproporsi =0;
		}

		if($inputbrondolanproporsi == ''){
			$inputbrondolanproporsi = 0;
		}

        $str="select sum(jjg) as jumlahjjg, sum(brondolan) as jumlahbrondolan from ".$dbname.".kebun_spb_vw where nospb ='".$notransaksi."' and blok = '".$indukblok."'  group by blok ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);        
		while($bar=$res->fetch()){
            $totalJanjangpanen   = $bar['jumlahjjg'];
            $totalBrondolanpanen = $bar['jumlahbrondolan'];
        }

        if($inputjjgproporsi >  $totalJanjangpanen){
            exit("Warning : Jumlah JJG PROPORSI tidak boleh lebih dari JJG SPB ");
        }

        if($inputbrondolanproporsi >  $totalBrondolanpanen){
            exit("Warning : Jumlah BRONDOLAN PROPORSI tidak boleh lebih dari BRONDOLAN SPB ");
        }

        if($inputtahuntanam == ''){
            exit("Warning : Tahun tanam wajib diisi ");
        }

        // if($inputjjgproporsi == '' || $inputjjgproporsi == '0'){
        //     exit("Warning : JJG wajib diisi dan harus lebih dari 0 ");
        // }

        $strx="select * from ".$dbname.".kebun_proporsitahuntanam_spb where notransaksi='".$notransaksi."' and tanggal ='".$tanggal."' and kodeorg='".$indukblok."' and tahuntanam = '".$inputtahuntanam."'";
        $resx=fetchdata($strx);
        if(count($resx) > 0){
            exit("Warning : Data sudah ada....");
        }

        $strx="select sum(jjg) as jumlahjjgpro, sum(brondolan) as jumlahbropro from ".$dbname.".kebun_proporsitahuntanam_spb where notransaksi='".$notransaksi."' and tanggal ='".$tanggal."' and kodeorg='".$indukblok."' ";
        $resx=fetchdata($strx);
        foreach($resx as $valx){
            $totaljjgpro = $valx['jumlahjjgpro'];
            $totalbropro = $valx['jumlahbropro'];
        }
        
        if(($inputjjgproporsi + $totaljjgpro) >  $totalJanjangpanen){
            exit("Warning : Gagal simpan, jumlah JJG PROPORSI yang sudah diinput melebihi JJG PANEN");
        }

        if(($inputbrondolanproporsi + $totalbropro) >  $totalBrondolanpanen){
            exit("Warning : Gagal simpan, jumlah BRONDOLAN PROPORSI yang sudah diinput melebihi BRONDOLAN PANEN");
        }

        try {
		$owlPDO->beginTransaction();
            

        $data = array(
					'notransaksi' => $notransaksi,
					'tanggal'     => $tanggal,
					'kodeorg'     => $indukblok,
					'blokkecil'   => $inputblokkecil,
					'tahuntanam'  => $inputtahuntanam,
					'jjg'         => $inputjjgproporsi,
					'brondolan'   => $inputbrondolanproporsi,
					'createby'    => $_SESSION['standard']['userid'],
					'createdate'  => date('Y-m-d H:i:s'),
					'updateby'    => $_SESSION['standard']['userid']				
				);
				


                $cols = array();
                foreach($data as $key=>$row) {
                        $cols[] = $key;
                }

				# Insert
				$query = insertQuery($dbname,'kebun_proporsitahuntanam_spb',$data,$cols);
				$owlPDO->exec($query);

                $str = "update " . $dbname . ".kebun_proporsitahuntanam_spb set flag = '0' where notransaksi = '".$notransaksi."'";
			    $owlPDO->exec($str);

        $owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
    break;

	case'gettahuntanam':
		
		$blok = $param['blok'];
		
		## Ambil Tahun tanam Blok kecil
			$strx="select * from ".$dbname.".setup_blok where kodeorg ='".$blok."'";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);        
			while($barx=$resx->fetch()){
				$tahuntanam = $barx['tahuntanam'];
			}

			echo $tahuntanam;
	break;


	case'tiketext':
		$str = "select * from ".$dbname.".kebun_spbht where nospb = '".$param['nospb']."'";
		$res = fetchData($str);
		foreach($res as $bar){
			$optpks=$bar['penerimatbs'];
			$kodeorg=$bar['kodeorg'];
		}
		
		$str="select * from ".$dbname.".pmn_4customer order by namacustomer asc";
		$res = fetchData($str);
		foreach($res as $key => $val){
			$nmpabrik[$val['kodecustomer']]=$val['namacustomer'];	
		}
		
	
		$beratbersih=0;
		$str = "select a.*, max(notransaksi) as notransaksi from ".$dbname.".pabrik_timbangan a where nospb = '".$param['nospb']."' and millcode = 'EXTM'";
		$res = fetchData($str);
		foreach($res as $bar){
			$param['notiket']=$bar['notransaksi'];
			$param['norefrensi']=$bar['norefrensi'];
			$param['jammasuk']=$bar['jammasuk'];
			$param['jamkeluar']=$bar['jamkeluar'];
			$param['beratmasuk']=$bar['beratmasuk'];
			$param['beratkeluar']=$bar['beratkeluar'];
			$param['potongan']=$bar['potongan'];
			$param['tanggal']=substr($bar['tanggal'],0,10);
		}
		
		$beratbersih=$param['beratmasuk']-$param['beratkeluar'];
		
		if($param['notiket']==''){
			$str = "select max(notransaksi) as notransaksi from ".$dbname.".pabrik_timbangan where millcode = 'EXTM'";
			$res = fetchData($str);
			if(count($res)>0){
				$param['notiket']="EX".addZero(intval(substr($res[0]['notransaksi'],2,5))+1,5);
			}else{
				$param['notiket']="EX00001";
			}
		}
		
		
		$tab.="<table width=100%>";
		$tab.="<tr>
					<td colspan=6 align=center style=font-weight:bold;background-color:".$warna.";height:30px>TIKET TIMBANG EXTERNAL</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><input id=custext style=display:none value=".$optpks.">
						<fieldset style=max-width:185px>".$nmpabrik[$optpks]."</fieldset>
					</td>
					
					<td>".$_SESSION['lang']['nospb']."</td>
					<td>:</td>
					<td style=width:195px;>
						<input id=nospbext class=myinputtext style=width:195px;height:20px;display:none; disabled value=\"".$param['nospb']."\">
						<fieldset style=max-width:185px>".$param['nospb']."</fieldset>
					</td>
				</tr>
				<tr>			
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td><input id=notiketext onkeydown=\"upperCaseF(this)\" disabled class=myinputtext style=width:195px;height:20px value=".$param['notiket']."></td>
				

					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td><input id=kodeorgext style=display:none value=".$kodeorg.">
						<fieldset style=max-width:185px>".getNamaOrg($kodeorg)."</fieldset>
					</td>
				</tr>";
				
				$tab.="<tr>	
					<td>Tiket Timbang</td>
					<td>:</td>
					<td><input type=text onkeydown=\"upperCaseF(this)\" id=noreff style=width:195px;height:20px class=myinputtext value=".$param['norefrensi']."></td>
					
					<td>Tanggal</td>
					<td>:</td>
					<td><input type=text class=myinputtext id=tanggalext onmousemove=setCalendar(this.id) onkeypress=return false; style=width:195px;height:20px maxlength=10 value=".$param['tanggal']."></td>
				</tr>
				<tr>	
					<td>Jam Masuk</td>
					<td>:</td>
					<td><input type=time onkeydown=\"upperCaseF(this)\" id=jammasuk style=width:195px;height:20px class=myinputtext value=".$param['jammasuk']."></td>
					
					<td>Jam Keluar</td>
					<td>:</td>
					<td><input type=time class=myinputtext id=jamkeluar onmousemove=setCalendar(this.id) onkeypress=return false; style=width:195px;height:20px maxlength=10 value=".$param['jamkeluar']."></td>
				</tr>
				<tr>	
					<td>".$_SESSION['lang']['masuk']." (Kg)</td>
					<td>:</td>
					<td><input type=text id=kgmasukext style=width:195px;height:20px class=myinputtextnumber onkeyup=\"z.numberFormat('kgmasukext','0');getkgnetto();\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" value=\"".$param['beratmasuk']."\"></td>
					
					<td>".$_SESSION['lang']['keluar']." (Kg)</td>
					<td>:</td>
					<td><input type=text id=kgkeluarext style=width:195px;height:20px class=myinputtextnumber onkeyup=\"z.numberFormat('kgkeluarext','0');getkgnetto();\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" value=\"".$param['beratkeluar']."\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['netto']." (Kg)</td>
					<td>:</td>
					<td><input type=text id=kgnettoext style=width:195px;height:20px class=myinputtextnumber disabled value=\"".$beratbersih."\"></td>
					
					<td>".$_SESSION['lang']['potongan']." (Kg)</td>
					<td>:</td>
					<td><input type=text id=potonganext style=width:195px;height:20px class=myinputtextnumber onkeyup=\"z.numberFormat('potonganext','0');getkgnetto();\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" value=\"".$param['potongan']."\"></td>
				</tr>
				<tr>
					<td colspan=6>&nbsp;</td>
				</tr>
				<tr>					
					<td align=center colspan='6' style=background-color:cyan;height:25px>
						<button style=width:100px;height:30px onclick=\"simpanext()\" class=mybutton>".$_SESSION['lang']['save']."</button>
						<button style=width:100px;height:30px onclick=\"batalext()\" class=mybutton>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		";
		echo $tab;
	break;
	case'unposting':
		$nospbexp = explode("/",$nospbunpost);
		$tahunexp = $nospbexp[3]."-".$nospbexp[2];

		// Cek Proses Premi Pemanen
		$msgErr = "";
		$countErr = 0;
		$sPostPnn = selectQuery($dbname,"kebun_3premipemanen","posting","nospb='".$nospbunpost."'","posting DESC",true);
		$rPostPnn = fetchData($sPostPnn);
		foreach ($rPostPnn as $val) {
			$postPnn = $val['posting'];
		}
		if ($postPnn == 1) {
			$msgErr.="Unposting tidak bisa dilakukan karena Proses Premi Pemanen Di No SPB ".$nospbunpost." sudah di proses.<br><br>";
			$countErr += 1;
		}

		// Cek Proses periode gaji
		$sPrdGj = "SELECT * FROM $dbname.sdm_5periodegaji WHERE kodeorg='".$kodeorgunpost."' and periode ='".$tahunexp."' and jenisgaji='H'";
		$rPrdGj =$owlPDO->query($sPrdGj) or die(print " Gagal: ".PDOException::getMessage());
		$rPrdGj->setFetchMode(PDO::FETCH_ASSOC);
		$barPrdGj = $rPrdGj->fetch();
		$sudahproses=$barPrdGj['sudahproses'];
		if ($sudahproses == 1) {
			$msgErr.="Unposting tidak bisa dilakukan karena Periode Gaji ".$tahunexp." unit ".$kodeorgunpost." sudah di proses.<br><br>";
			$countErr += 1;
		}

		// Cek Tutup Buku Periode Akuntansi
		$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$kodeorgunpost."' and periode ='".$tahunexp."'";
		$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$ttp->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$ttp->fetch();
		$tutup=$bar['tutupbuku'];
		if($tutup==1){
			$msgErr.="Unposting tidak bisa dilakukan karena periode akuntansi ".$tahunexp." unit ".$kodeorgunpost." sudah di tutup.";
			$countErr += 1;
		}
		if ($countErr > 0) {
			exit("Warning: ".$msgErr);
		}
		try {
			// $str = "update ".$dbname.".kebun_spbdt set kgwb='0', totalkg='0',kgwbnetto='0' where nospb ='".$nospbunpost. "'";
			// $owlPDO->exec($str);
			$str = "update ".$dbname.".kebun_spbht set posting='0', postingby='' where nospb ='".$nospbunpost. "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case'postingDatanih':
		
	    try {
			$str = "update ".$dbname.".kebun_spbht set posting='1', postingby='' where nospb ='".$noSpb. "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case'unpostingnih':
		
	    try {
			$str = "update ".$dbname.".kebun_spbht set posting='0', postingby='' where nospb ='".$noSpb. "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case'delData':
	try {
	$owlPDO->beginTransaction();
	
	$sCek="select posting,noreferensi from ".$dbname.".kebun_spbht where nospb='".$noSpb."'";

	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
	$rCek=$qCek->fetch();

	if($rCek['posting']=='1'){
		throw new PDOException("Nomor SPB ini sudah di Posting");
	}

	$sql="delete from ".$dbname.".kebun_spbht where nospb='".$noSpb."' ";
	$owlPDO->exec($sql);
	
	$sqlDet="delete from ".$dbname.".kebun_spbdt where nospb='".$noSpb."'";
	$owlPDO->exec($sqlDet); 
	
	if($rCek['noreferensi']!=''){
		$str = "update ".$dbname2.".kebun_spbht_mobile set flag='0' where nospb ='".$rCek['noreferensi']. "'";
		$owlPDO->exec($str);
	}
	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	
	break;

	case'postingData':
		try {
			$owlPDO->beginTransaction();

		$str = "update " . $dbname . ".kebun_spbht set posting='1', postingby ='".$_SESSION['standard']['userid']."' where nospb ='".$noSpb."' and tujuan ='".$intex."'"; 
		$owlPDO->exec($str); 

		$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		} 
	break;
	
	case'unpostingData':
		try {
			$owlPDO->beginTransaction();

		$str = "update " . $dbname . ".kebun_spbht set posting='0', postingby ='".$_SESSION['standard']['userid']."' where nospb ='".$noSpb."' and tujuan ='".$intex."'"; 
		$owlPDO->exec($str); 

		$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		} 
	break;
	
	
	case'updateData':
	try {
	$owlPDO->beginTransaction();
	$data=$_POST;
	
	$stsblok = makeOption($dbname,'setup_blok','kodeorg,intiplasma',"kodeorg='".$blok."'");
	if($stsblok[$blok]=='I'){		
		if($tglpanen=='--' or $tglpanen==''){
			throw new PDOException("Tanggal panen wajib diisi !");
		}
	}
	
	// $str=" select sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn_vw where blok='".$blok."'
	// 		and tanggal = '".$tglpanen."' and (posting='1' or posting='0')";
	$str=" select sum(jjgbuahbesar) as jjgpanen from ".$dbname.".kebun_prestasi_vw where kodeorg='".$blok."'
			and tanggal = '".$tglpanen."'";
			
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$jjgpanen=$bar['jjgpanen'];
		$jjgafkir=$bar['jjgafkir'];	

	$str=" select sum(jjg) as jjg from ".$dbname.".kebun_spb_vw where blok='".$blok."' and tanggalpanen = '".$tglpanen."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$jjgspbvw=$bar['jjg'];
		
	##ambil data sudah tersimpan, karena ini edit
	$str=" select jjg from ".$dbname.".kebun_spb_vw where  blok='".$oldBlok."' and tph='".$oldTph."' and sesi='".$oldSesi."' and qrcode='".$oldQrcode."' and pemanen='".$oldPemanen."' and nospb='".$data['noSpb']."'
			and tanggalpanen = '".$tglpanen."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$jjgbloklama=$bar['jjg'];	
		
	$cekdata=(floatval($jjgpanen)-floatval($jjgafkir)-floatval($jjgspbvw)-floatval($jjngHsl))+floatval($jjgbloklama);
	$jjgtersedia=(floatval($jjgpanen)-floatval($jjgafkir)-floatval($jjgspbvw))+floatval($jjgbloklama);
	
	#exit("error".$jjgpanen."_".$jjgafkir."_".$jjgspbvw."_".$jjngHsl."_".$jjgbloklama."_".$cekdata."_".$stsblok[$blok]);
	
	
	#jika plasma boleh tidak pakai rekap panen
	// if($cekdata<0 and $stsblok[$blok]=='I'){
	// 	throw new PDOException("Jjg yg anda input melebihi jumlah Jjg yang tersedia.<br><br>Jjg tersedia (Restan + Panen - Kirim) = ".$jjgtersedia." Jjg<br><br>Silahkan Input dan Posting Jjg Panen melalui menu Kebun - Transaksi - Rekap Panen per Blok.");
	// }
	
	if($stsblok[$blok]=='I' and $kegiatan==''){
		#throw new PDOException("Kegiatan harus diisi !");
	}
	if($intex!='3'){
		validasiInput(substr($blok,0,4),substr($blok,0,6),'spb',$tanggal,$exit='0');
	}
	
		$sCek="select distinct nospb from ".$dbname.".kebun_spbht where nospb='".$data['noSpb']."'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
		if($rCek>0){
			if(($data['jjng']=='') or ($data['brondolan']=='') or ($data['bjr']=='') ) {
				throw new PDOException("Jjg atau BJR tidak boleh kosong");
			}
			$cekPost="select distinct posting from ".$dbname.".kebun_spbht where nospb='".$data['noSpb']."' and posting=1";

			$qcekPost=$owlPDO->query($cekPost) or die(print " Gagal: ".PDOException::getMessage());
			$qcekPost->setFetchMode(PDO::FETCH_ASSOC);
			$rCek=$qcekPost->fetch();
			if($rCek['posting']!=0){
				throw new PDOException("Nomor SPB Sudah di Posting");
			}
			$kgBjr=($jjngHsl*$bjrHsl);
			$sUpHead="update ".$dbname.".kebun_spbht set tanggal='".$tanggal."',tujuan='".$intex."',penerimatbs='".$pks."' where nospb='".$data['noSpb']."'";
			$owlPDO->exec($sUpHead); 
			
			$sUpDetail="update ".$dbname.".kebun_spbdt set
			blok='".$blok."',tph='".$param['tphdt']."',sesi='".$param['sesidt']."',pemanen='".$param['pemanendt']."',qrcode='".$param['noreferensidt']."',jjg='".$jjngHsl."',bjr='".$bjrHsl."',brondolan='".$brondolanHsl."',mentah='".$mentah."',busuk='".$busuk."',matang='".$matang."',lewatmatang='".$lwtmatang."',kgbjr='".$kgBjr."',kgwb='".$kgwb."', tanggalpanen='".$tglpanen."', kegiatan='".$kegiatan."' 
			where nospb='".  $noSpb."' and blok='".$oldBlok."' and tph='".$oldTph."' and sesi='".$oldSesi."' and qrcode='".$oldQrcode."' and pemanen='".$oldPemanen."'  and tanggalpanen='".$oldtglpanen."'  "; #exit("error".$sUpDetail);		
			$owlPDO->exec($sUpDetail); 
			
				
		}else{
			
			$kgBjr=($jjngHsl*$bjrHsl);
			$sDetIns="insert into ".$dbname.".kebun_spbdt (nospb, qrcode,tanggalpanen,blok, tph, sesi, pemanen, jjg, bjr, brondolan, mentah, busuk, matang, lewatmatang,kgbjr,kgwb,kegiatan) values ('".$noSpb."','".$param['noreferensidt']."','".$tglpanen."','".$blok."','".$param['tphdt']."','".$param['sesidt']."','".$param['pemanendt']."','".$jjngHsl."','".$bjrHsl."','".$brondolanHsl."','".$mentah."','".$busuk."','".$matang."','".$lwtmatang."','".$kgBjr."','".$kgwb."','".$kegiatan."')";
			$owlPDO->exec($sDetIns); 
		}
		
		$sCek="select nospb from ".$dbname.".kebun_spbdt where nospb='".$param['noSpb']."' and blok='".$param['blok']."' and pemanen='".$param['pemanendt']."'
		and tph='".$param['tphdt']."' and sesi='".$param['sesidt']."' and qrcode='".$param['noreferensidt']."' "; //echo "warning".$sCek;nospb
		$qCek=fetchdata($sCek);
		$rCek=count($qCek);
		if($rCek>1){
			exit("warning : Data sudah ada... ");
		}
		
	$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	} 
	break;
	case'getDivData':
	$optOrg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	
	// ambil divisi
	if($param['nospb'] != ''){
		$str="select divcode from ".$dbname.".pabrik_timbangan where nospb ='".$param['nospb']."' and notransaksi like '%K%' limit 1";
		$getdivv=fetchdata($str);
		$idDiv = $getdivv[0]['divcode'];
	}

	if($idDiv==''){
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
		 where tipe='AFDELING' and kodeorganisasi LIKE '%".$kdOrg."%'";
		// exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";	
		}
		echo $optOrg;
	}else{
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
		 where tipe='AFDELING' and kodeorganisasi LIKE '%".$kdOrg."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optOrg.="<option value=".$bar['kodeorganisasi']." ".($bar['kodeorganisasi']==$idDiv?'selected':'').">".$bar['kodeorganisasi']."</option>";	
		}
		echo $optOrg;
	}
	break;
	
	//-------
	case'getkerani':
	// echo"warning:masuk";
	$str="select karyawanid,namakaryawan,nik, lokasitugas, subbagian, kodejabatan from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."' 
	and kodejabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan 
	where LOWER(namajabatan) like 'kerani buah%' or LOWER(namajabatan) like 'kerani panen%' 
	or LOWER(namajabatan) like 'kerani produksi%' or LOWER(namajabatan) like 'mandor%' or LOWER(namajabatan) like 'Kerani Transport') and lokasitugas not like '%M' order by namakaryawan asc";
	
	$namajabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
			$optkar.="<option value='".$bar['karyawanid']."'>".$bar['nik']." ".$bar['namakaryawan']." ".$bar['subbagian']." ".$namajabatan[$bar['kodejabatan']]." </option>";
		}		
		
		echo $optkar;
		break;
	//-------
	case'addSession':
	$_SESSION['temp']['nSpb']=$noSpb;
	echo "warning:".$_SESSION['temp']['nSpb'];
	exit();
	break;
	
	case'delDetail':
		try{
			$owlPDO->beginTransaction();
			
			$str="delete from ".$dbname.".kebun_spbdt where nospb='".$param['nospb']."' and qrcode='".$param['noreferensidt']."' and tanggalpanen='".$param['tglpanen']."' and pemanen='".$param['karyawanid']."' and blok='".$param['blok']."' and tph='".$param['tph']."' and sesi='".$param['sesi']."'";
			$owlPDO->exec($str); 

			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
	case'previewdata':
	
	
	$arrblok=$bjr=$bjrx=$jjg=$brondolan=$kgwbx=$mentah=$busuk=$matang=$lewatmatang=array();
	$tjjg=$tbrondolan=$tkgwbx=array();

	$sShwData2="select * from ".$dbname.".kebun_spbht where nospb='".$noSpb."' ";
	$qShwData2=$owlPDO->query($sShwData2) or die(print " Gagal: ".PDOException::getMessage());
	$qShwData2->setFetchMode(PDO::FETCH_ASSOC);
	$rShwData2=$qShwData2->fetch();
	$arrStat=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
	$stat=$arrStat[$rShwData2['posting']];
	$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$rShwData2['kerani']."'");
	echo"
	<table cellspacing=1 cellpadding=1 border=0>
	<tr><td>".$_SESSION['lang']['nospb']."</td><td>:</td><td>".$rShwData2['nospb']."</td></tr>
	<tr><td>".$_SESSION['lang']['tglNospb']."</td><td>:</td><td>".tanggalnormal($rShwData2['tanggal'])."</td></tr>
	<tr><td>".$_SESSION['lang']['kodeorg']."</td><td>:</td><td>".$rShwData2['kodeorg']." - ".getNamaOrg($rShwData2['kodeorg'])."</td></tr>
	<tr><td>".$_SESSION['lang']['status']."</td><td>:</td><td>".$stat."</td></tr>
	<tr><td>".$_SESSION['lang']['jenis']."</td><td>:</td><td>".$nmkeg[$rShwData2['kerani']]."</td></tr>
	</table><br />
	";
	echo"
	<table cellspacing=1 cellpadding=5 border=0 class=sortable>
	<thead>
	<tr class=rowheader>
	<th align=center>No</th>
	<th align=center>Tanggal Panen</th>
	<th align=center>".$_SESSION['lang']['blok']."</th>
	<th align=center>".$_SESSION['lang']['janjang']."</th>
	<th align=center hidden>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['kebun']."</th>
	<th align=center>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['kebun']."</th>
	<th align=center>".$_SESSION['lang']['brondolan']."</th>
	<th align=center>".$_SESSION['lang']['kg']." Sebelum Sortasi</th>
	<th align=center>".$_SESSION['lang']['bjr']." PKS Sebelum Sortasi</th>
	<th align=center>".$_SESSION['lang']['kg']." Setelah Sortasi</th>
	<th align=center>".$_SESSION['lang']['bjr']." PKS Setelah Sortasi</th>
	</tr></thead>
	<tbody>
	";
	
	$sShwData="select a.*,b.* from ".$dbname.".kebun_spbht a inner join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb where a.nospb='".$noSpb."' ";
	
	$qDet=$owlPDO->query($sShwData) or die(print " Gagal: ".PDOException::getMessage());
	$qDet->setFetchMode(PDO::FETCH_ASSOC);
	while($rDet=$qDet->fetch()){
		$arrblok[$rDet['blok']][$rDet['tanggalpanen']]=$rDet['tanggalpanen'];
		$bjr[$rDet['blok']][$rDet['tanggalpanen']]=$rDet['bjr'];
		$bjrx[$rDet['blok']]=$rDet['bjr'];
		@$jjg[$rDet['blok']][$rDet['tanggalpanen']]+=$rDet['jjg'];
		@$brondolan[$rDet['blok']][$rDet['tanggalpanen']]+=$rDet['brondolan'];
		$kgwbx[$rDet['blok']][$rDet['tanggalpanen']]=$rDet['kgwb'];
		$kgwbnet[$rDet['blok']][$rDet['tanggalpanen']]=$rDet['kgwbnetto'];
		$mentah[$rDet['blok']][$rDet['tanggalpanen']]=$rDet['mentah'];
		$busuk[$rDet['blok']][$rDet['tanggalpanen']]=$rDet['busuk'];
		$matang[$rDet['blok']][$rDet['tanggalpanen']]=$rDet['matang'];
		$lewatmatang[$rDet['blok']][$rDet['tanggalpanen']]=$rDet['lewatmatang'];
		
	}
	$no=0;
	foreach($arrblok as $blok => $valtgl){
		foreach($valtgl as $tglpanen){
			$no+=1;
			// $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$blok."'");
			$nmorg=makeOption($dbname,'organisasi','indukblok,namaorganisasi',"indukblok='".$blok."'");
			echo"<tr class=rowcontent>
			<td align=center >".$no."</td>
			<td align=center >".tanggalnormal($tglpanen)."</td>
			<td align=center >".$nmorg[$blok]."</td>
			<td align=right >".$jjg[$blok][$tglpanen]."</td>
			<td align=right >".$bjr[$blok][$tglpanen]."</td>
			<td align=right >".$brondolan[$blok][$tglpanen]."</td>
			<td align=right >".@number_format($kgwbx[$blok][$tglpanen],2)."</td>
			<td align=right >".@number_format($kgwbx[$blok][$tglpanen]/$jjg[$blok][$tglpanen],2)."</td>
			<td align=right >".@number_format($kgwbnet[$blok][$tglpanen],2)."</td>
			<td align=right >".@number_format($kgwbnet[$blok][$tglpanen]/$jjg[$blok][$tglpanen],2)."</td>
			</tr>
			";
			
			@$tjjg[$blok]+=$jjg[$blok][$tglpanen];
			@$tbrondolan[$blok]+=$brondolan[$blok][$tglpanen];
			@$tkgwbx[$blok]+=$kgwbx[$blok][$tglpanen];
			@$tkgwbnet[$blok]+=$kgwbnet[$blok][$tglpanen];
			
			@$gtjjg+=$jjg[$blok][$tglpanen];
			@$gtbrondolan+=$brondolan[$blok][$tglpanen];
			@$gtkgwbx+=$kgwbx[$blok][$tglpanen];
			@$gtkgwbnet+=$kgwbnet[$blok][$tglpanen];
		}
		echo"<tr class=rowcontent style=background-color:#FEF5E7;font-weight:bold>
			<td align=center colspan=2>SUB TOTAL</td>
			<td align=center >".$nmorg[$blok]."</td>
			<td align=right >".$tjjg[$blok]."</td>
			<td align=right >".$bjrx[$blok]."</td>
			<td align=right >".$tbrondolan[$blok]."</td>
			<td align=right >".@number_format($tkgwbx[$blok],2)."</td>
			<td align=right >".@number_format($tkgwbx[$blok]/$tjjg[$blok],2)."</td>
			<td align=right >".@number_format($tkgwbnet[$blok],2)."</td>
			<td align=right >".@number_format($tkgwbnet[$blok]/$tjjg[$blok],2)."</td>
			
			</tr>
			";	
	}
	echo"<tr class=rowcontent style=background-color:#CACFD2;font-weight:bold>
			<td align=center colspan=2>TOTAL</td>
			<td align=center></td>
			<td align=right >".$gtjjg."</td>
			<td align=right ></td>
			<td align=right >".$gtbrondolan."</td>
			<td align=right >".@number_format($gtkgwbx,2)."</td>
			<td align=right >".@number_format($gtkgwbx/$gtjjg,2)."</td>
			<td align=right >".@number_format($gtkgwbnet,2)."</td>
			<td align=right >".@number_format($gtkgwbnet/$gtjjg,2)."</td>
			
			</tr>
			";
	
	echo"</tbody></table><br>";
	
	echo"
	<table cellspacing=1 cellpadding=5 border=0 class=sortable>
	<thead>
	<tr class=rowheader>
	<th align='center' width=40px>No.</th>
	<th align='center' width=100px>".$_SESSION['lang']['nik2']."</th>
	<th align='center'>".$_SESSION['lang']['namakaryawan']."</th>
	<th align='center'>".$_SESSION['lang']['janjang']."</th>
	</tr></thead>
	<tbody>
	";
	$str="select a.*,b.nik,b.namakaryawan from ".$dbname.".kebun_spbbm a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where nospb = '".$noSpb."'";
	$res=fetchData($str);
	if(empty($res)){
		$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}
	$no='';
	foreach($res as $bar){
		$no+=1;
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center>".$bar['nik']."</td>";
		$tab.="<td>".$bar['namakaryawan']."</td>";
		$tab.="<td align=right>".number_format($bar['jjg_angkut'])."</td>";
		$tab.="</tr>";
	}
		
	echo $tab;
	echo"</table><br>";

	echo"
	<table cellspacing=1 border=0 cellpadding=5 class=sortable>
	<thead>
	<tr class=rowheader>
	<th align='center'>No.</th>
	<th align='center'>Hamparan</th>
	<th align='center'>Kavling</th>
	<th align='center'>".$_SESSION['lang']['nama']."</th>
	<th align='center'>".$_SESSION['lang']['janjang']."</th>
	<th align='center'>".$_SESSION['lang']['brondolan']."</th>
	<th align='center'>".$_SESSION['lang']['kg']." Sebelum Sortasi</th>
	<th align='center'>".$_SESSION['lang']['kg']." Setelah Sortasi</th>
	</tr></thead>
	<tbody>
	";
	$str="select nospb,no_hamp,no_kavl,nama,janjang,brondolan,kgwb,kgwbnetto from ".$dbname.".kebun_spbpetani where nospb = '".$noSpb."'";
	$res=fetchData($str);
	if(empty($res)){
		$tabp.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{		
		$no='0';
		foreach($res as $bar){
			$no+=1;
			$tabp.="<tr class=rowcontent>";
			$tabp.="<td align=center>".$no."</td>";
			$tabp.="<td align=center>".$bar['no_hamp']."</td>";
			$tabp.="<td align=center>".$bar['no_kavl']."</td>";
			$tabp.="<td>".$bar['nama']."</td>";
			$tabp.="<td align=right>".number_format($bar['janjang'])."</td>";
			$tabp.="<td align=right>".number_format($bar['brondolan'])."</td>";
			$tabp.="<td align=right>".number_format($bar['kgwb'],2)."</td>";
			$tabp.="<td align=right>".number_format($bar['kgwbnetto'],2)."</td>";
			$tabp.="</tr>";
			$totalp['janjang']+=$bar['janjang'];
			$totalp['brondolan']+=$bar['brondolan'];
			$totalp['kgwb']+=$bar['kgwb'];
			$totalp['kgwbnetto']+=$bar['kgwbnetto'];
		}
			$tabp.="<tr class=rowcontent style=background-color:#CACFD2;font-weight:bold>";
			$tabp.="<td align=center></td>";
			$tabp.="<td align=center></td>";
			$tabp.="<td align=center>Total</td>";
			$tabp.="<td></td>";
			$tabp.="<td align=right>".number_format($totalp['janjang'])."</td>";
			$tabp.="<td align=right>".number_format($totalp['brondolan'])."</td>";
			$tabp.="<td align=right>".number_format($totalp['kgwb'],2)."</td>";
			$tabp.="<td align=right>".number_format($totalp['kgwbnetto'],2)."</td>";
			$tabp.="</tr>";
			
	}
	echo $tabp;
	echo"</table>";
	break;
	case'previewdata2':

	$sShwData2="select * from ".$dbname.".kebun_spbht where nospb='".$noSpb."' ";
	$qShwData2=$owlPDO->query($sShwData2) or die(print " Gagal: ".PDOException::getMessage());
	$qShwData2->setFetchMode(PDO::FETCH_ASSOC);
	$rShwData2=$qShwData2->fetch();
	$arrStat=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
	$stat=$arrStat[$rShwData2['posting']];
	$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$rShwData2['kerani']."'");

	$tab="
	<table cellspacing=1 cellpadding=1 border=0>
	<tr><td>".$_SESSION['lang']['nospb']."</td><td>:</td><td>".$rShwData2['nospb']."</td></tr>
	<tr><td>".$_SESSION['lang']['tglNospb']."</td><td>:</td><td>".tanggalnormal($rShwData2['tanggal'])."</td></tr>
	<tr><td>".$_SESSION['lang']['kodeorg']."</td><td>:</td><td>".$rShwData2['kodeorg']." - ".getNamaOrg($rShwData2['kodeorg'])."</td></tr>
	</table><br />
	";
	
	$tab.="
	<table cellspacing=1 cellpadding=5 border=0 class=sortable>
	<thead>
	<tr class='rowheader'>
	<td align=center >No</td>
	<td align=center >".$_SESSION['lang']['noreferensi']."</td>
	<td align=center >".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['panen']."</td>
	<td align=center>".$_SESSION['	lang']['pemanen']."</td>
	<td align=center>".$_SESSION['lang']['blok']."</td>
	<td align=center>".$_SESSION['lang']['tph']."</td>
	<td align=center>Sesi</td>
	<td align=center >".$_SESSION['lang']['bjr'].' (Kg)'."</td>
	<td align=center >".$_SESSION['lang']['janjang']."</td>
	<td align=center >".$_SESSION['lang']['brondolan']."</td>
	</tr>
	</thead>
	<tbody>
	";

	$no=0;
	$arrblok=array();
	$str="select * from ".$dbname.".kebun_spbdt where nospb='".$noSpb."' order by blok desc";
	$res=fetchdata($str);
	$jlhitem=count($res);
	foreach($res as $val){
		if($tempblok!=''){
			if($tempblok!=$val['blok']){
				$tab.="<tr class=rowcontent style=background-color:#FEF5E7;font-weight:bold>
					<td align=center colspan=8>SUB TOTAL BLOK ".substr($tempblok,6,4)."</td>
					<td align=right >".number_format($ttljjg,0)."</td>
					<td align=right >".number_format($ttlbrd,2)."</td>
				</tr>";
				$ttljjg=$ttlbrd=0;
			}
		}
		$keterangan_tph=makeOption($dbname,'kebun_5tph','kode,keterangan');
		$no++;
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=left>".$val['qrcode']."</td>";
		$tab.="<td align=center nowrap>".tanggalnormal($val['tanggalpanen'])."</td>";
		$tab.="<td align=left>".getNK($val['pemanen'])." - ".getNK($val['pemanen'],'nik')."</td>";
		$tab.="<td align=left>".substr($val['blok'],6,4)."</td>";
		$tab.="<td align=left>".$keterangan_tph[$val['tph']]."</td>";
		$tab.="<td align=center>".($val['sesi']=='0'?'-':$val['sesi'])."</td>";
		$tab.="<td align=right>".number_format($val['bjr'],2)."</td>";
		$tab.="<td align=right>".number_format($val['jjg'],0)."</td>";
		$tab.="<td align=right>".number_format($val['brondolan'],2)."</td>";
		$tab.="</tr>";
		$tempblok=$val['blok'];
		$ttljjg+=$val['jjg'];
		$ttlbrd+=$val['brondolan'];
		
		if($no==$jlhitem){
			$tab.="<tr class=rowcontent style=background-color:#FEF5E7;font-weight:bold>
				<td align=center colspan=8>SUB TOTAL BLOK ".substr($tempblok,6,4)."</td>
				<td align=right >".number_format($ttljjg,0)."</td>
				<td align=right >".number_format($ttlbrd,2)."</td>
			</tr>";
		}
		
		$gttljjg+=$val['jjg'];
		$gttlbrd+=$val['brondolan'];
	}

	$tab.="<tr class=rowcontent style=background-color:#CACFD2;font-weight:bold>
	<td align=center colspan=8>TOTAL</td>
	<td align=right >".number_format($gttljjg,0)."</td>
	<td align=right >".number_format($gttlbrd,2)."</td>
	</tr>
	";	

	
	$tab.="</tbody></table><br>";
	if ($tipe == 'html') {
		// $tab.="</div>";
		echo $tab;
	} else {
		$tab.="Print Time : " .date('Y-m-d H:i:s')." <br> By : ".$_SESSION['empl']['name'];	
		$nop_ ="Laporan SPB DETAIL ".$noSpb." ";
		$css="";
		$dte=date("YmdHis");
		$nop = $nop_.".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet($nop_, $tab);				
		$xls->headers($nop);
		echo $xls->buildFile();
	}
	break;
	
	default:
	break;
}
?>