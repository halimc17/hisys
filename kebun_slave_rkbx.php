<?php
ini_set('display_errors',0);
error_reporting(0);

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';
use Dompdf\Dompdf;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


$param                = $_POST;
$method               = checkPostGet('method', '');
$numrow               = checkPostGet('numrow', '');
$notransaksi          = checkPostGet('notransaksi', '');
$periode              = checkPostGet('periode', '');
$kodeorg              = checkPostGet('kodeorg', '');
$unit                 = checkPostGet('unit', '');
$divisi               = checkPostGet('divisi', '');
$kodebarang           = checkPostGet('kodebarang', '');
$compgaji             = checkPostGet('compgaji', '');
$tipe                 = checkPostGet('tipe', '');
$ket                  = checkPostGet('ket', '');
$admin                = checkPostGet('admin', '');

$kepada               = checkPostGet('kepada', '');


$blok                 = checkPostGet('blok', '');
$karyawanid           = checkPostGet('karyawanid', '');
$kegiatan             = checkPostGet('kegiatan', '');
$kodegudang           = checkPostGet('kodegudang', '');
$mandorsrc            = checkPostGet('mandorsrc', '');
$nobkmsch             = checkPostGet('nobkmsch', '');
$mode                 = checkPostGet('mode', '');
$tipetransaksi        = checkPostGet('tipetransaksi', '');
$luas                 = checkPostGet('luas', '');
$dosismat             = checkPostGet('dosismat', '');
$jlhmat               = checkPostGet('jlhmat', '');
$rpmat                = checkPostGet('rpmat', '');
$hargarata            = checkPostGet('hargarata', '');
$stok                 = checkPostGet('stok', '');
$boronganpnn          = checkPostGet('boronganpnn', '');
@$boronganpnn         =str_replace(",","",$boronganpnn);
@$dosismat            =str_replace(",","",$dosismat);
@$stok                =str_replace(",","",$stok);
@$jlhmat              =str_replace(",","",$jlhmat);
@$rpmat               =str_replace(",","",$rpmat);
@$hargarata           =str_replace(",","",$hargarata);

$persenkontangkut     = checkPostGet('persenkontangkut', '');
$tonkontangkut        = checkPostGet('tonkontangkut', '');
$hargakontangkut      = checkPostGet('hargakontangkut', '');
$rpkontangkut         = checkPostGet('rpkontangkut', '');
@$persenkontangkut    =str_replace(",","",$persenkontangkut);	
@$tonkontangkut       =str_replace(",","",$tonkontangkut);	
@$hargakontangkut     =str_replace(",","",$hargakontangkut);	
@$rpkontangkut        =str_replace(",","",$rpkontangkut);

$persenalong          = checkPostGet('persenalong', '');
$tonalong             = checkPostGet('tonalong', '');
$hargalong            = checkPostGet('hargalong', '');
$rpalong              = checkPostGet('rpalong', '');
@$persenalong         =str_replace(",","",$persenalong);	
@$tonalong            =str_replace(",","",$tonalong);	
@$hargalong           =str_replace(",","",$hargalong);	
@$rpalong             =str_replace(",","",$rpalong);
	
$persenmekanis          = checkPostGet('persenmekanis', '');
$tonmekanis             = checkPostGet('tonmekanis', '');
$hargamekanis            = checkPostGet('hargamekanis', '');
$rpmekanis              = checkPostGet('rpmekanis', '');
@$persenmekanis         =str_replace(",","",$persenmekanis);	
@$tonmekanis            =str_replace(",","",$tonmekanis);	
@$hargamekanis           =str_replace(",","",$hargamekanis);	
@$rpmekanis             =str_replace(",","",$rpmekanis);	

	
$pusingan                  = checkPostGet('pusingan', '');
$pusingan                  =str_replace(",","",$pusingan);
	
$kbl                  = checkPostGet('kbl', '');
$kbl                  =str_replace(",","",$kbl);
$kht                  = checkPostGet('kht', '');
$kht                  =str_replace(",","",$kht);
$khl                  = checkPostGet('khl', '');
$khl                  =str_replace(",","",$khl);

$output               = checkPostGet('output', '');
$upah                 = checkPostGet('upah', '');
$premi                = checkPostGet('premi', '');
@$output              =str_replace(",","",$output);
@$upah                =str_replace(",","",$upah);
@$premi               =str_replace(",","",$premi);
$luasbor              = checkPostGet('luasbor', '');
$rpperhabor           = checkPostGet('rpperhabor', '');
$rupiahbor            = checkPostGet('rupiahbor', '');
$dept                 = checkPostGet('dept', '');
@$luasbor             =str_replace(",","",$luasbor);
@$rpperhabor          =str_replace(",","",$rpperhabor);
@$rupiahbor           =str_replace(",","",$rupiahbor);

$rotasi               = checkPostGet('rotasi', '');
$akp                  = checkPostGet('akp', '');
$bjr                  = checkPostGet('bjr', '');
$jjg                  = checkPostGet('jjg', '');
$kg                   = checkPostGet('kg', '');
$upah                 = checkPostGet('upah', '');
$premi1               = checkPostGet('premi1', '');
$premi2               = checkPostGet('premi2', '');
$brondol              = checkPostGet('brondol', '');
$kgbrd                = checkPostGet('kgbrd', '');
$upahmdr              = checkPostGet('upahmdr', '');
$premimdr             = checkPostGet('premimdr', '');
$upahkrn              = checkPostGet('upahkrn', '');
$premikrn             = checkPostGet('premikrn', '');
$upahmdrsatu          = checkPostGet('upahmdrsatu', '');
$premimdrsatu         = checkPostGet('premimdrsatu', '');
$jabatan              = checkPostGet('jabatan', '');
$rotasi               =str_replace(",","",$rotasi);
$akp                  =str_replace(",","",$akp);
$bjr                  =str_replace(",","",$bjr);
$jjg                  =str_replace(",","",$jjg);
$kg                   =str_replace(",","",$kg);
$upah                 =str_replace(",","",$upah);
$premi1               =str_replace(",","",$premi1);
$premi2               =str_replace(",","",$premi2);
$kgbrd                =str_replace(",","",$kgbrd);
$brondol              =str_replace(",","",$brondol);
$upahmdr              =str_replace(",","",$upahmdr);
$premimdr             =str_replace(",","",$premimdr);
$upahkrn              =str_replace(",","",$upahkrn);
$premikrn             =str_replace(",","",$premikrn);
$upahmdrsatu          =str_replace(",","",$upahmdrsatu);
$premimdrsatu         =str_replace(",","",$premimdrsatu);

$jarakpks             = checkPostGet('jarakpks', '');
$persensendiri        = checkPostGet('persensendiri', '');
$kapasitas            = checkPostGet('kapasitas', '');
$trippks              = checkPostGet('trippks', '');
$km                   = checkPostGet('km', '');
$kgsendiri            = checkPostGet('kgsendiri', '');
$ttlrpsendiri         = checkPostGet('ttlrpsendiri', '');
$kgkont               = checkPostGet('kgkont', '');
$ttlrpkont            = checkPostGet('ttlrpkont', '');
$outputkgperhk        = checkPostGet('outputkgperhk', '');
$norma                = checkPostGet('norma', '');
$ttlkgbasis           = checkPostGet('ttlkgbasis', '');
$kgpremi              = checkPostGet('kgpremi', '');
$rpkgpremi            = checkPostGet('rpkgpremi', '');
$hargaborongan        = checkPostGet('hargaborongan', '');
$lembur               = checkPostGet('lembur', '');
$tt                   = checkPostGet('tt', '');
$jarakpks             =str_replace(",","",$jarakpks);
$persensendiri        =str_replace(",","",$persensendiri);
$kapasitas            =str_replace(",","",$kapasitas);
$trippks              =str_replace(",","",$trippks);
$km                   =str_replace(",","",$km);
$kgsendiri            =str_replace(",","",$kgsendiri);
$ttlrpsendiri         =str_replace(",","",$ttlrpsendiri);
$kgkont               =str_replace(",","",$kgkont);
$ttlrpkont            =str_replace(",","",$ttlrpkont);
$outputkgperhk        =str_replace(",","",$outputkgperhk);
$norma                =str_replace(",","",$norma);
$ttlkgbasis           =str_replace(",","",$ttlkgbasis);
$kgpremi              =str_replace(",","",$kgpremi);
$rpkgpremi            =str_replace(",","",$rpkgpremi);
$tt                   =str_replace(",","",$tt);
$hargaborongan        =str_replace(",","",$hargaborongan);
$lembur               =str_replace(",","",$lembur);
$jamlembur            = checkPostGet('jam', '');
$jamlembur            =str_replace(",","",$jamlembur);

$tkkbl                = checkPostGet('tkkbl', '');
$tkkbl                =str_replace(",","",$tkkbl);
$tkkht                = checkPostGet('tkkht', '');
$tkkht                =str_replace(",","",$tkkht);
$tkkhl                = checkPostGet('tkkhl', '');
$tkkhl                =str_replace(",","",$tkkhl);
$rpkbl                = checkPostGet('rpkbl', '');
$rpkbl                =str_replace(",","",$rpkbl);
$rpkht                = checkPostGet('rpkht', '');
$rpkht                =str_replace(",","",$rpkht);
$rpkhl                = checkPostGet('rpkhl', '');
$rpkhl                =str_replace(",","",$rpkhl);



#= tambahan untuk panen
$ttlupahmdr           =checkPostGet('ttlupahmdr', '');
$ttlupahmdr           =str_replace(",","",$ttlupahmdr);
$persenmdr            = checkPostGet('persenmdr', '');
$persenmdr            =str_replace(",","",$persenmdr);
$ttlupahkrn           = checkPostGet('ttlupahkrn', '');
$ttlupahkrn           =str_replace(",","",$ttlupahkrn);
$persenkrn            = checkPostGet('persenkrn', '');
$persenkrn            =str_replace(",","",$persenkrn);
$ttlupahmdr1          = checkPostGet('ttlupahmdr1', '');
$ttlupahmdr1          =str_replace(",","",$ttlupahmdr1);
$jlhmdrmdr1           = checkPostGet('jlhmdrmdr1', '');
$jlhmdrmdr1           =str_replace(",","",$jlhmdrmdr1);
$persenmdr1           = checkPostGet('persenmdr1', '');
$persenmdr1           =str_replace(",","",$persenmdr1);
$ttlhkpnn             = checkPostGet('ttlhkpnn', '');
$ttlhkpnn             =str_replace(",","",$ttlhkpnn);
$copypremibrd         = checkPostGet('copypremibrd', '');
$copypremibrd         =str_replace(",","",$copypremibrd);

$prestasi             = checkPostGet('prestasi', '');
$err                  = checkPostGet('err', '');
@$prestasi            =str_replace(",","",$prestasi);
@$param['qtymat']     =str_replace(",","",$param['qtymat']);
@$param['prestasi']   =str_replace(",","",$param['prestasi']);
@$param['jhk']        =str_replace(",","",$param['jhk']);
@$param['upah']       =str_replace(",","",$param['upah']);
@$param['premi']      =str_replace(",","",$param['premi']);

$divsch               = checkPostGet('divsch', '');
$notransaksisch       = checkPostGet('notransaksisch', '');
$postingsrc           = checkPostGet('postingsrc', '');
$periodesch           = checkPostGet('periodesch', '');
$kodebarang1          = checkPostGet('kodebarang1', '');
$jlhmat1              = checkPostGet('jlhmat1', '');
$rpmat1               = checkPostGet('rpmat1', '');
$jlhmat1              =str_replace(",","",$jlhmat1);
$rpmat1               =str_replace(",","",$rpmat1);
$kodebarang2          = checkPostGet('kodebarang2', '');
$jlhmat2              = checkPostGet('jlhmat2', '');
$rpmat2               = checkPostGet('rpmat2', '');
$jlhmat2              =str_replace(",","",$jlhmat2);
$rpmat2               =str_replace(",","",$rpmat2);

$kodebarang3          = checkPostGet('kodebarang3', '');
$jlhmat3              = checkPostGet('jlhmat3', '');
$rpmat3               = checkPostGet('rpmat3', '');
$jlhmat3              =str_replace(",","",$jlhmat3);
$rpmat3               =str_replace(",","",$rpmat3);

$kodebarang4          = checkPostGet('kodebarang4', '');
$jlhmat4              = checkPostGet('jlhmat4', '');
$rpmat4               = checkPostGet('rpmat4', '');
$jlhmat4              =str_replace(",","",$jlhmat4);
$rpmat4               =str_replace(",","",$rpmat4);

$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$nmorg=makeOption($dbname,'organisasi','indukblok,namaindukblok');

$optstatus=array("0"=>"Diperlukan Persetujuan","1"=>"Disetujui","2"=>"Dikoreksi","3"=>"Ditolak","9"=>"Proses Pengajuan");	 

#======================= Kegiatan =========================
	$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".setup_kegiatan
			where 1=1  and status='1' and substr(kodekegiatan,1,3) in ('621','611','126','128') order by kodekegiatan asc, namakegiatan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$d=substr($bar['kodekegiatan'],0,3);
		if($d!=$n){			
			$optKeg.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
		}
		$optKeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
		$n=$d;
		if($d!=$n){			
			$optKeg.="</optgroup>";
		}
	}
#======================= Kegiatan =========================
#=== ambil gaji KBT, KHT,KHL ===
#=== ambil gajinya cuma rata2 saja ===
$arrprd = explode("-",$periode);
$tahun = $arrprd[0];
$str="select * from ".$dbname.".sdm_5gajipokok a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid and a.tahun='".$tahun."' where a.tahun ='".$tahun."' and a.idkomponen='1' and b.lokasitugas='".$kodeorg."' and b.tanggalkeluar='0000-00-00'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$rpperhkkbt=$rpperhkkht=$rpperhkkhl=0;
while ($bar = $res->fetch()) {
	if($bar['tipekaryawan']==1){
		# KBT
		$rpperhkkbt=$bar['jumlah']/25;
	}else if($bar['tipekaryawan']==3){
		# KHT
		$rpperhkkht=$bar['jumlah']/25;
	}else if($bar['tipekaryawan']==4){
		# KHL
		$rpperhkkhl=$bar['jumlah']/25;
	}
}

switch ($method) {
	case'uploadpemel':
		$tab="";
		$tab.="<fieldset style=float:left><legend>Template Pemeliharaan Detail</legend>";
		$tab.="<table border=0>
				<tr>
					<td>Download : 
					<a href='fileupload/Tenplate RKB Pemeliharaan.xlsx' target='frame'>Templ_Upload</a>&nbsp;
					<a href='tool_slave_getExample.php?form=KEGIATAN' target='frame'>Master (Kegiatan, Blok, Barang)</a>&nbsp;
					</td>
				</tr><tr>
					<td colspan=3><hr></td>
				</tr><tr>
					<td colspan=3>
						<form id=frm name=frm enctype=multipart/form-data method=post>
							<input type=hidden name=jenisdata id=jenisdata value='PEMEL'>
							<input type=hidden name=kodeorgupload id=kodeorgupload value='".$kodeorg."'>
							<input type=hidden name=periodeupload id=periodeupload value='".$periode."'>
							<input type=hidden name=notransaksiupload id=notransaksiupload value='".$notransaksi."'>
							<input type=hidden name=MAX_FILE_SIZE value=1024000>
							File : <input name=filex type=file id=filex class=mybutton accept=\"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet\">
							<input type=hidden id='methodpemel' value='uploaddatapemel' />
							<input type=button class=mybutton id=previewupload value=".$_SESSION['lang']['save']." title='Submit this File' onclick=uploaddatapemel()>
							<input type=button class=mybutton  value=".$_SESSION['lang']['back']." title='Back' onclick=kembali()>
						</form>
					</td>
				</tr></table>";
		$tab.="</fieldset>";
		$tab.="<iframe frameborder=0 style=width:100%;height:500px; name=frame></iframe>";	

		echo $tab;
	break;
	case'uploadpemelmaterial':
		$tab="";
		$tab.="<fieldset style=float:left><legend>Template Pemeliharaan Material Detail</legend>";
		$tab.="<table border=0>
				<tr>
					<td>Download : 
					<a href='fileupload/Template RKB Pemeliharaan Material.xlsx' target='frame'>Templ_Upload</a>&nbsp;
					<a href='tool_slave_getExample.php?form=KEGIATAN' target='frame'>Master (Kegiatan, Blok, Barang)</a>&nbsp;
					</td>
				</tr><tr>
					<td colspan=3><hr></td>
				</tr><tr>
					<td colspan=3>
						<form id=frm name=frm enctype=multipart/form-data method=post>
							<input type=hidden name=jenisdata id=jenisdata value='PEMEL'>
							<input type=hidden name=kodeorgupload id=kodeorgupload value='".$kodeorg."'>
							<input type=hidden name=periodeupload id=periodeupload value='".$periode."'>
							<input type=hidden name=notransaksiupload id=notransaksiupload value='".$notransaksi."'>
							<input type=hidden name=MAX_FILE_SIZE value=1024000>
							File : <input name=filexm type=file id=filexm class=mybutton accept=\"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet\">
							<input type=hidden id='methodpemelmaterial' value='uploaddatapemelmaterial' />
							<input type=button class=mybutton id=previewupload value=".$_SESSION['lang']['save']." title='Submit this File' onclick=uploaddatapemelmaterial()>
							<input type=button class=mybutton  value=".$_SESSION['lang']['back']." title='Back' onclick=kembalipemelmaterial()>
						</form>
					</td>
				</tr></table>";
		$tab.="</fieldset>";
		$tab.="<iframe frameborder=0 style=width:100%;height:500px; name=frame></iframe>";
		
		echo $tab;
	break;
	
	case'uploaddatapemel':
		$data = $_POST;
		// print_r($data);
		// exit("Warning: ");
		if($_FILES['file']['error']==0){
			 $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$file = $_FILES['file']['tmp_name'];       
			if($filetype=='.xlsx'){
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null,true,true,true);
				$firsturut1=2;
				$i=1;

				try{
					foreach ($sheets as $sheet){
						if($i > $firsturut1){
							if($sheet['B'] !='' && $sheet['D'] !='' && $sheet['F'] != ''){
								$sCek = selectQuery($dbname,"kebun_rkbdt","*",
									"norkb='".$data['notransaksi']."' and kodekegiatan='".$sheet['B']."' 
									and blok='".$sheet['D']."' and divisi='".substr($sheet['D'],0,6)."'"
								);
								$rCek = fetchData($sCek);
								if (count($rCek) > 0) {
									$pesannotif = "\nKegiatan: ".$rCek[0]['kodekegiatan']." (".getNamaKeg($rCek[0]['kodekegiatan']).")\nBlok: ".$rCek[0]['blok']." (".getIndukBlok($rCek[0]['blok']).")\nDivisi: ".$rCek[0]['divisi']." (".getNamaOrg($rCek[0]['divisi']).")";
									exit("Warning: Data Sudah Pernah Diinput !".$pesannotif);
								}

								$str2="insert into ".$dbname.".kebun_rkbdt (
								`norkb`,
								`tipetransaksi`,
								`periode`,
								`kodeorg`,
								`divisi`,
								`kodekegiatan`,
								`blok`,
								`tahuntanam`,
								`hasilkerja`,
								`KBL`,
								`KHT`,
								`KHL`,
								`norma`,
								`upah`,
								`premi`,
								`hasilkerjaborongan`,
								`hargaborongan`,
								`rupiahborongan`,
								`updateby`)
								values (
								'".$data['notransaksi']."',
								'PEMEL',
								'".$data['periode']."',
								'".$data['unit']."',
								'".substr($sheet['D'],0,6)."',
								'".$sheet['B']."',
								'".$sheet['D']."',
								'',
								'".$sheet['G']."',
								'".$sheet['H']."',
								'".$sheet['I']."',
								'".$sheet['J']."',
								'".$sheet['G']."',
								'".$sheet['L']."',
								'".$sheet['M']."',
								'".$sheet['N']."',
								'".$sheet['O']."',
								'".$sheet['P']."',
								'".$_SESSION['standard']['userid']."'
								)";
								try{
									$owlPDO->exec($str2);
								}
								catch(PDOException $e){
									echo " Gagal," . addslashes($e->getMessage());
								}
							}else{
								continue;
							}
						}
						$i++;
					}
				}catch(PDOException $e){
					echo " Gagal," . addslashes($e->getMessage());
				}
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
	break;
	case'uploaddatapemelmaterial':
		$data = $_POST;
		// print_r($data);
		// exit("Warning: ");
		if($_FILES['file']['error']==0){
			 $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$file = $_FILES['file']['tmp_name'];       
			if($filetype=='.xlsx'){
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null,true,true,true);
				$firsturut1=1;
				$i=1;

				try{
					foreach ($sheets as $sheet){
						if($i > $firsturut1){
							if($sheet['B'] !='' && $sheet['D'] !='' && $sheet['F'] != ''){
								$sCek = selectQuery($dbname,"kebun_rkbmaterial","*",
									"norkb='".$data['notransaksi']."' and kodekegiatan='".$sheet['B']."' 
									and blok='".$sheet['D']."' and divisi='".substr($sheet['D'],0,6)."'
									and kodebarang='".$sheet['H']."'"
								);
								$rCek = fetchData($sCek);
								if (count($rCek) > 0) {
									$pesannotif = "\nKegiatan: ".$rCek[0]['kodekegiatan']." (".getNamaKeg($rCek[0]['kodekegiatan']).")\nMaterial: ".$rCek[0]['kodebarang']." (".getNamaBrg($rCek[0]['kodebarang']).")\nBlok: ".$rCek[0]['blok']." (".getIndukBlok($rCek[0]['blok']).")\nDivisi: ".$rCek[0]['divisi']." (".getNamaOrg($rCek[0]['divisi']).")";
									exit("Warning: Data Sudah Pernah Diinput !".$pesannotif);
								}

								// Ambil Harga Rata Per Kode barang
								$sHargarata=selectQuery($dbname,"log_5saldobulanan","hargarata","kodebarang='".$sheet['H']."' and kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($sheet['D'],0,4)."') and hargarata!=0","periode desc limit 1");
								$rHargarata = fetchData($sHargarata);
								$uplhargarata = $rHargarata[0]['hargarata'];
								if ($uplhargarata <= 0) {
									exit("Warning: Harga Material ".$sheet['H']." (".getNamaBrg($sheet['H']).") Belum Ada !!!");
								}
								
								$where=" and b.afdeling='".substr($sheet['D'],0,6)."'";
								// Stok gudang 
								$sSaldoAkhir = "select * from ".$dbname.".log_5saldobulanan a left join ".$dbname.".kebun_5gudangtransaksi b on a.kodegudang=b.kodegudang where a.kodebarang='".$sheet['H']."' and a.periode='".periodelalu($data['periode'])."' and a.kodegudang like '".substr($sheet['D'],0,4)."%' ".$where.""; 
								$rSaldoAkhir=$owlPDO->query($sSaldoAkhir) or die(print " Gagal: ".PDOException::getMessage());
								$rSaldoAkhir->setFetchMode(PDO::FETCH_ASSOC);
								while($bar=$rSaldoAkhir->fetch()){
									@$uplstok+=$bar['saldoakhirqty'];
								}
								
								// Transaksi belum posting
								$sPosted = "select * from ".$dbname.".log_transaksi_vw a left join ".$dbname.".kebun_5gudangtransaksi b on a.kodegudang=b.kodegudang where a.kodebarang='".$sheet['H']."' and a.kodegudang like '".substr($sheet['D'],0,4)."%' ".$where." and a.statusjurnal='0' and a.post='0' and substr(a.tanggal,1,7)<='".$data['periode']."'";
								$rPosted=$owlPDO->query($sPosted) or die(print " Gagal: ".PDOException::getMessage());
								$rPosted->setFetchMode(PDO::FETCH_ASSOC);
								while($bar=$rPosted->fetch()){
									if($bar['tipetransaksi']=='1' or $bar['tipetransaksi']=='2' or $bar['tipetransaksi']=='3'){
										@$upljmlmasuk+=$bar['jumlah'];
									}
									if($bar['tipetransaksi']=='5' or $bar['tipetransaksi']=='6' or $bar['tipetransaksi']=='7'){
										@$upljmlkeluar+=$bar['jumlah'];
									}
								}
								$uplsaldo = $uplstok+$upljmlmasuk-$upljmlkeluar;
								$jmlrpbrg = ($sheet['J'] * $uplhargarata);

								$str3="insert into ".$dbname.".kebun_rkbmaterial (
								`norkb`,
								`tipetransaksi`,
								`periode`,
								`kodeorg`,
								`divisi`,
								`kodekegiatan`,
								`blok`,
								`kodebarang`,
								`luas`,
								`kwantitas`,
								`hargasatuan`,
								`jumlahrp`,
								`saldo`) 
								values (
								'".$data['notransaksi']."',
								'PEMEL',
								'".$data['periode']."',
								'".$data['unit']."',
								'".substr($sheet['D'],0,6)."',
								'".$sheet['B']."',
								'".$sheet['D']."',
								'".$sheet['H']."',
								'".$sheet['F']."',
								'".$sheet['K']."',
								'".$uplhargarata."',
								'".$jmlrpbrg."',
								'".$uplsaldo."')";
								try{
									$owlPDO->exec($str3);
								}
								catch(PDOException $e){
									echo " Gagal," . addslashes($e->getMessage());
								}
							}else{
								continue;
							}
						}
						$i++;
					}
				}catch(PDOException $e){
					echo " Gagal," . addslashes($e->getMessage());
				}
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
	break;

	/* 
	case'uploaddata':
		$divisi = substr($blok,0,6);
		$validasidt = ($notransaksi!='' and $tipetransaksi!='' and $periode!='' and $kodeorg!='' and $divisi!='' and $kegiatan!='' and $blok!='' and ($luas!='' or $luasbor!='') and ($upah!='' or $premi!='' or $rupiahbor!=''));
		
		if($validasidt){
			#hapus dulu
			$str = "delete from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
			# Simpan detail
			$tt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blok."'");
			
			$output=$luas/($kbl+$kht+$khl);
			$rpperhabor=$rupiahbor/$luasbor;
			
			$str = "insert into " . $dbname . ".kebun_rkbdt (`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,`kodekegiatan`,`blok`,`tahuntanam`,`hasilkerja`,`KBL`,`KHT`,`KHL`,`norma`,`upah`,`premi`,`hasilkerjaborongan`,`hargaborongan`,`rupiahborongan`,`updateby`)
			values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."','".$kegiatan."','".$blok."','".$tt[$blok]."','".$luas."','".$kbl."','".$kht."','".$khl."','".$output."','".$upah."','".$premi."','".$luasbor."','".$rpperhabor."','".$rupiahbor."','".$_SESSION['standard']['userid']."')";
			if($ket==0){
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			}
		}
		
		for($i=1;$i<=4;$i++){
			if($i==1){
				$jlhmat=$jlhmat1; $rpmat=$rpmat1; $kodebarang=$kodebarang1;	
			}elseif($i==2){
				$jlhmat=$jlhmat2; $rpmat=$rpmat2; $kodebarang=$kodebarang2;
			}elseif($i==3){
				$jlhmat=$jlhmat3; $rpmat=$rpmat3; $kodebarang=$kodebarang3;
			}elseif($i==4){
				$jlhmat=$jlhmat4; $rpmat=$rpmat4; $kodebarang=$kodebarang4;
			}
			
			$validasi = ($notransaksi!='' and $tipetransaksi!='' and $periode!='' and $kodeorg!='' and $divisi!='' and $kegiatan!='' and $blok!='' and $kodebarang!='' and $luas!='' and $jlhmat!='' and ($rpmat!='' or $rpmat!='0'));
			if($validasi){
				$str = "delete from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."' and kodebarang='".$kodebarang."'";
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
				
				# Simpan material
				$where='';
				if($divisi!==''){
					$where=" and b.afdeling='".$divisi."'";
				}
				# Stok gudang
				$str = "select * from ".$dbname.".log_5saldobulanan a left join ".$dbname.".kebun_5gudangtransaksi b on a.kodegudang=b.kodegudang where a.kodebarang='".$kodebarang."' and a.periode='".periodelalu($periode)."' and a.kodegudang like '".$kodeorg."%' ".$where.""; 
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					@$stok+=$bar['saldoakhirqty'];
				}
				
				$hargarata=$rpmat/$jlhmat;

				$str = "insert into " . $dbname . ".kebun_rkbmaterial (`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,`kodekegiatan`,`blok`, `kodebarang`, `luas`, `kwantitas`, `hargasatuan`, `jumlahrp`,`saldo`)
				values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."','".$kegiatan."','".$blok."','".$kodebarang."','".$luas."','".$jlhmat."','".$hargarata."','".$rpmat."','".$stok."')";
				if($ket==0){
					try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
				}
			}
		} #tutup for
	break;
	*/

	#=buat ajukan
	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='RKB' and a.level='1' and a.kodeunit='".$unit."'  
				  order by b.namakaryawan asc";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	
		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:99%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	case'ajukan':
	
		try {
		$owlPDO->beginTransaction();
			if($kepada=='' or $notransaksi==''){
				throw new PDOException('Isikan nama penyetuju.');
			}
			//update flag menjadi 1
			$str = "update " . $dbname . ".kebun_rkbht set posting='1', statuspersetujuan='0' where norkb = '" . $notransaksi . "'";
			$owlPDO->exec($str);
			
			//cari dulu apakah sudah pernah di ajukan sebelumnya
			$tglhi = date("Ymd");
			$str="select * from ".$dbname.".approval where jenispersetujuan='RKB' and notransaksi='".$notransaksi."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				if($bar['notransaksi']!=''){
					# jika ada pindahkan ke table ini
					$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
					$owlPDO->exec($str);
				}
			}
			
			#kemudian setelah di pindah, hapus persetujuan lama
			$str="delete from ".$dbname.".approval where jenispersetujuan='RKB' and notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			//insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','".$notransaksi."','RKB','1','" . $kepada."','0','','','')";
			$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	
	
	case'simpanheader':		
		$data = $_POST;			
		#=== insert header ===
        if ($mode=='edit') {
            #exit("error Masuk Disini");
        } else {
			#===== buat nomor transaksi =====
			#=== Generate No Transaksi
			# Get Existing Data
			$fWhere = "periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."'";
			$fQuery = selectQuery($dbname,'kebun_rkbht','norkb',$fWhere);
			$tmpNo = fetchData($fQuery);
			
			# Generate No Transaksi
			if(count($tmpNo)==0) {
				# Get Max No Urut
				$maxNo = 0;
				foreach($tmpNo as $row) {
				$tmpRow = explode('/',$row['norkb']);
				@$noUrut = (int)$tmpRow[3];
				if($noUrut>$maxNo)
					$maxNo = $noUrut;
				}
				$currNo = addZero($maxNo+1,3);
				$notransaksi = str_replace("-","",$data['periode'])."/".$data['kodeorg']."/RKB/".$currNo;

				$str = "insert into " . $dbname . ".kebun_rkbht (`norkb`, `periode`, `kodeorg`, `posting`, `updateby`)
				values ('".$notransaksi."','".$data['periode']."','".$data['kodeorg']."','0','" . $_SESSION['standard']['userid'] . "')";
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
				
			} else {
				$notransaksi=$tmpNo[0]['norkb'];
			}
			
			echo $notransaksi;
			
		}
	break;
    case'detail':
        OPEN_BOX();
		#==== Form Judul Detail ====
		# Divisi
		$optDivisi=$whereX='';
		if($_SESSION['empl']['subbagian']!='' and strlen($_SESSION['empl']['subbagian'])=='6'){
			$optDivisi="<option value='".$_SESSION['empl']['subbagian']."'>".$_SESSION['empl']['subbagian']."</option>";
		}else if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			
			$optDivisi.="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			
			$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' ".$whereX." ";
			$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$resstr->setFetchMode(PDO::FETCH_ASSOC);
			while ($res = $resstr->fetch()) {
				$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}
		}else{
			$optDivisi.="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			
			$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') ".$whereX." and kodeorganisasi like '".$kodeorg."%'";
			$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$resstr->setFetchMode(PDO::FETCH_ASSOC);
			while ($res = $resstr->fetch()) {
				$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}
		}
		#=== TAB PRESTASI DAN KEHADIRAN ===
        $frm[0]="<table>
			<td valign=top>
			<fieldset style=float:left><legend>Notransaksi</legend>
				<table height=25px>
					<td>" . $_SESSION['lang']['jenis'] . "</td>
					<td><input id=tipetransaksi disabled class=myinputtext style=\"width:50px;\" value='PEMEL'></td>
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td><select style=\"width:150px;\" onchange=cleardetailall() id=divisi>".$optDivisi."</select></td>
					<td><button id=tombolsimpandetail class=mybutton onclick=inputdetail()>" . $_SESSION['lang']['save'] . "</button></td>
				</table>
			</fieldset>
			</td>
			
			<td valign=top>
			<fieldset style=float:left><legend>Rupiah / HK</legend>
				<table height=25px>
					<td>KBL</td>
					<td><input id=rpperhkkbl class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkbt."></td>
					<td>KHT</td>
					<td><input id=rpperhkkht class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkht."></td>
					<td>KHL</td>
					<td><input id=rpperhkkhl class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkhl."></td>
					
				</table>
			</fieldset>
			</td>
			
			<td valign=top>
			<fieldset style=float:left><legend>Upload Detail</legend>
				<table height=25px>
					<td><button class=mybutton onclick=uploadpemel()>" . $_SESSION['lang']['upload'] . "</button></td>
					<td><a href='fileupload/uploadrkb.pdf' download>
						<img class='resicon' src='images/info.png' title=\"Download Panduan\" style='position:relative;top:3px;left:3px;'>
					</a></td>
				</table>
			</fieldset>
			</td>
			<td valign=top>
			<fieldset style=float:left><legend>Upload Material Detail</legend>
				<table height=25px>
					<td><button class=mybutton onclick=uploadpemelmaterial()>" . $_SESSION['lang']['upload'] . "</button></td>
					<td><a href='fileupload/uploadrkb.pdf' download>
						<img class='resicon' src='images/info.png' title=\"Download Panduan\" style='position:relative;top:3px;left:3px;'>
					</a></td>
				</table>
			</fieldset>
			</td>
			
			</table>
			<fieldset>
			<legend>" . $_SESSION['lang']['detail'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable style=min-width:1275px>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$frm[0].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['pekerjaan']."</td>
				<td align=center ".$rows." width=120px>".$_SESSION['lang']['blok']."</td>
				<td align=center ".$rows." width=50px>".$_SESSION['lang']['luas'] . "</td>
				<td align=center ".$rows." width=30px>Output</td>
				<td align=center ".$rows." width=30px>Pusingan</td>
				<td align=center colspan=5 width=50px>Tenaga Kerja</td>
				<td align=center ".$rows." width=30px>Premi</td>
				<td align=center colspan=3 width=50px>Borongan</td>
				<td align=center colspan=6 >".$_SESSION['lang']['material']."</td>
				<td align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center width=45px>KBL</td>
				<td align=center width=45px>KHT</td>
				<td align=center width=45px>KHL</td>
				<td align=center width=45px>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center width=45px>".$_SESSION['lang']['rupiah'] . "</td>
				<td align=center width=45px>Luas</td>
				<td align=center width=45px>Rp/Ha</td>
				<td align=center width=45px>Rupiah</td>
				<td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center width=30px>Sat</td>
				<td align=center width=40px>Dosis</td>
				<td align=center width=40px>Jumlah</td>
				<td align=center width=50px>Rupiah</td>
				<td align=center>#</td>
			</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		#=== Isi input detail ===
		$frm[0].="<tbody id=inputdetail>
				<script>inputdetail()</script>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input detail ===	
        $frm[0].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
			<div id=loaddatadetail>
				<script>loaddatadetail()</script>
			</div></fieldset>";
			
		#=== TAB PANEN ===
		$frm[1]="<table>
				<td valign=top>
				<fieldset style=float:left><legend>Notransaksi</legend>
					<table height=25px>
						<td>" . $_SESSION['lang']['jenis'] . "</td>
						<td><input id=tipetransaksipnn disabled class=myinputtext style=\"width:50px;\" value='PANEN'></td>
						<td>" . $_SESSION['lang']['divisi'] . "</td>
						<td><select style=\"width:150px;\"  id=divisipnn>".$optDivisi."</select></td>
						<td><button id=tombolsimpandetailpnn class=mybutton onclick=inputdetailpnn()>" . $_SESSION['lang']['save'] . "</button></td>
					</table>
				</fieldset>
				</td>
				<td valign=top>
				<fieldset style=float:left><legend>Rupiah / HK</legend>
					<table height=25px>
						<td>KBL</td>
						<td><input id=rpperhkkblpnn onkeyup=\"proporsihkpanen();\" class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkbt."></td>
						<td>KHT</td>
						<td><input id=rpperhkkhtpnn onkeyup=\"proporsihkpanen();\" class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkht."></td>
						<td>KHL</td>
						<td><input id=rpperhkkhlpnn onkeyup=\"proporsihkpanen();\" class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkhl."></td>
						
					</table>
				</fieldset>
				</td>
				<td valign=top>
				<fieldset style=float:left><legend>Info</legend>
					<table height=25px>
						<td>Hanya Blok TM yang dimunculkan !</td>
					</table>
				</fieldset>
				</td>
			</table>
			<fieldset>
			<legend>" . $_SESSION['lang']['panen'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=4";	
			$frm[1].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['luas'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['sph'] . "</td>
				<td align=center ".$rows.">TT</td>
				<td align=center ".$rows.">".$_SESSION['lang']['pokok'] . "</td>
				<td align=center ".$rows." style=width:30px>".$_SESSION['lang']['rotasi'] . "</td>
				<td align=center ".$rows." style=width:30px>AKP<br>%</td>
				<td align=center ".$rows." style=width:30px>BJR</td>
				<td align=center ".$rows.">Jjg</td>
				<td align=center ".$rows.">Kg</td>
				<td align=center ".$rows." style=width:30px>Output<br>(Kg)</td>
				
				<td align=center colspan=16>".$_SESSION['lang']['biaya'] . "</td>
				<td align=center ".$rows.">Rp/Kg</td>
			</tr>
			<tr>
				<td align=center colspan=9>Pemanen</td>
				<td align=center colspan=6>Supervisi</td>
				<td align=center rowspan=3>Total Biaya</td>
			</tr>
			<tr>
				<td align=center colspan=3>HK</td>
				<td align=center rowspan=2>Upah</td>
				<td align=center colspan=4>Premi</td>
				<td align=center rowspan=2>Total</td>
				<td align=center style=width:70px colspan=2>Mandor Panen</td>
				<td align=center style=width:70px colspan=2>Kerani Panen</td>
				<!--- <td align=center colspan=3>Mandor 1</td> --->
				<td align=center colspan=2>Total</td>
			</tr>
			<tr>
				<td align=center style=width:30px>KBL</td>
				<td align=center style=width:30px>KHT</td>
				<td align=center style=width:30px>KHL</td>
				<!--- <td align=center>Sub TTL</td> --->
				<td align=center>1</td>
				<!--- <td align=center>2</td> --->
				<td align=center>Kutib Brd</td>
				<td align=center width=30px>Kg Brd</td> 
				<!--- <td align=center width=50px>Borongan</td> ---->  
				<td align=center>Sub TTL</td>
				<td align=center style=width:30px>Upah</td>
				<td align=center style=width:30px>Premi</td>
				<td align=center style=width:30px>Upah</td>
				<td align=center style=width:30px>Premi</td>
				<!--- <td align=center style=width:30px>Upah</td> ---->
				<!--- <td align=center colspan=2>Premi</td> ---->
				<td align=center>Upah</td>
				<td align=center>Premi</td>
			</tr>
			</thead>";
		#==== Form Judul panen ====
		
		#=== Isi input panen ===
		$frm[1].="<tbody id=inputdetailpnn>
				<script>inputdetailpnn()</script>
				</tbody></table></fieldset>";
		
		#=== List data tersimpan input panen ===	
        $frm[1].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['panen'] . "</legend>
			<div id=loaddatadetailpnn>
				<script>loaddatadetailpnn()</script>
			</div></fieldset>";
		
		#=== TAB ANGKUT ===
		$frm[2]="<table>
				<td valign=top>
				<fieldset style=float:left><legend>Notransaksi</legend>
					<table height=25px>
						<td>" . $_SESSION['lang']['jenis'] . "</td>
						<td><input id=tipetransaksiangkut disabled class=myinputtext style=\"width:50px;\" value='ANGKUT'></td>
						<td>" . $_SESSION['lang']['divisi'] . "</td>
						<td><select style=\"width:150px;\"  id=divisiangkut>".$optDivisi."</select></td>
						<td><button id=tombolsimpandetailangkut class=mybutton onclick=inputdetailangkut()>" . $_SESSION['lang']['save'] . "</button></td>
					</table>
				</fieldset>
				</td>
				<td valign=top>
				<fieldset style=float:left><legend>Rupiah / HK</legend>
					<table height=25px>
						<td>KBL</td>
						<td><input id=rpperhkkblangkut onkeyup=\"proporsihk();\" class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkbt."></td>
						<td>KHT</td>
						<td><input id=rpperhkkhtangkut onkeyup=\"proporsihk();\" class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkht."></td>
						<td>KHL</td>
						<td><input id=rpperhkkhlangkut onkeyup=\"proporsihk();\" class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkhl."></td>
						
					</table>
				</fieldset>
				</td>
				<td valign=top>
				<fieldset style=float:left><legend>Info</legend>
					<table height=25px>
						<td>Jika data Kg tidak muncul silahkan proses RKB Panen terlebih dahulu.</td>
					</table>
				</fieldset>
				</td>
			</table>
			<fieldset>
			<legend>Pengangkutan</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=4";	
			$frm[2].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">Blok</td>
				<td align=center ".$rows.">".$_SESSION['lang']['produksi'] . "</td>
				<td align=center ".$rows." width=30px>Jarak ke PKS KM</td>
				
				<td align=center rowspan=1 colspan=8>Angkutan Sendiri</td>
				<td align=center rowspan=1 colspan=4>Angkutan Kontrak</td>
				<td align=center rowspan=1 colspan=4>Langsir Manual</td>
				<td align=center rowspan=1 colspan=4>Langsir Mekanis</td>
				<td align=center rowspan=1 colspan=12>Biaya Bongkar Muat</td>
				<td align=center rowspan=1 colspan=2>Total<br>Biaya</td>
			</tr>
			<tr>
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kap<br>Kg</td>
				<td align=center rowspan=3>Trip<br>PKS</td>
				<td align=center rowspan=3>KM</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Rp/KM</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>

				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center colspan=7>Upah</td>
				<td align=center colspan=3>Premi</td>
				<td align=center colspan=2>Total</td>
				
				<td align=center rowspan=3>Rp</td>
				<td align=center rowspan=3>Rp/Kg</td>
			</tr>
			<tr>
				<td align=center rowspan=2>Output<br>Kg/HK</td>
				<!--- <td align=center rowspan=2>Basis<br>Kg/HK</td> --->
				<td align=center rowspan=1 colspan=4>HK</td>
				<!--- <td align=center rowspan=2>Total<br>Kg Basis</td> --->
				<td align=center rowspan=1 colspan=2>Total Upah</td>
				<td align=center rowspan=2>Kg</td>
				<td align=center rowspan=2>Rp/Kg</td>
				<td align=center rowspan=2>Rp</td>
				<td align=center rowspan=2>Rp</td>
				<td align=center rowspan=2>Rp/Kg</td>
			</tr>
			<tr>
				<td align=center >KBL</td>
				<td align=center >KHT</td>
				<td align=center >KHL</td>
				<td align=center >Total</td>
				<td align=center >Rp</td>
				<td align=center >Rp/Kg</td>
				
			</tr>
			
			</thead>";
		#==== Form Judul ANGKUT ====
		
		#=== Isi input ANGKUT ===
		$frm[2].="<tbody id=inputdetailangkut>
				<script>inputdetailangkut()</script>
				</tbody></table></fieldset>";
		
		#=== List data tersimpan input ANGKUT ===	
        $frm[2].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " Pengangkutan</legend>
			<div id=loaddatadetailangkut>
				<script>loaddatadetailangkut()</script>
			</div></fieldset>";
			
		#=== TAB UMUM ===
		$optDivisi=$whereX='';
		if($_SESSION['empl']['subbagian']!='' and strlen($_SESSION['empl']['subbagian'])=='6'){
			$optDivisi.="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$optDivisi.="<option value='UMUM'>UMUM - UMUM / KANTOR</option>";
			$optDivisi.="<option value='".$_SESSION['empl']['subbagian']."'>".$_SESSION['empl']['subbagian']."</option>";
		}else if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			$optDivisi.="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$optDivisi.="<option value='UMUM'>UMUM - UMUM / KANTOR</option>";
			$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' ".$whereX." ";
			$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$resstr->setFetchMode(PDO::FETCH_ASSOC);
			while ($res = $resstr->fetch()) {
				$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}
		}else{
			$optDivisi.="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$optDivisi.="<option value='UMUM'>UMUM - UMUM / KANTOR</option>";
			$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') ".$whereX." and kodeorganisasi like '".$kodeorg."%'";
			$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$resstr->setFetchMode(PDO::FETCH_ASSOC);
			while ($res = $resstr->fetch()) {
				$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
			}
		}
		
		$frm[3]="<table>
				<td valign=top>
				<fieldset style=float:left><legend>Notransaksi</legend>
					<table height=25px>
						<td>" . $_SESSION['lang']['jenis'] . "</td>
						<td><input id=tipetransaksiumm disabled class=myinputtext style=\"width:50px;\" value='UMUM'></td>
						<td>" . $_SESSION['lang']['divisi'] . "</td>
						<td><select style=\"width:150px;\" onchange=cleardetailallumm() id=divisiumm>".$optDivisi."</select></td>
						<td><button id=tombolsimpandetailumm class=mybutton onclick=inputdetailumm()>" . $_SESSION['lang']['save'] . "</button></td>
					</table>
				</fieldset>
				</td>
				<td valign=top>
				<fieldset style=float:left><legend>Rupiah / HK</legend>
					<table height=25px>
						<td>KBL</td>
						<td><input id=rpperhkkblumm  class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkbt."></td>
						<td>KHT</td>
						<td><input id=rpperhkkhtumm  class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkht."></td>
						<td>KHL</td>
						<td><input id=rpperhkkhlumm  class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkhl."></td>
						
					</table>
				</fieldset>
				</td>
				<td valign=top>
				<fieldset style=float:left><legend>Info</legend>
					<table height=25px>
						<td></td>
					</table>
				</fieldset>
				</td>
			</table>
			<fieldset>
			<legend>" . $_SESSION['lang']['umum'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable style='min-width:80%'>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$frm[3].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['jabatan'] . "</td>
				
				<td align=center rowspan=1 colspan=4>Tenaga Kerja</td>
				<td align=center rowspan=2 colspan=1>Upah</td>
				<td align=center rowspan=1 colspan=4>Lembur dan Premi</td>
				<td align=center rowspan=1 colspan=4>Material</td>
				<td align=center rowspan=2 colspan=1>Totah Rupiah</td>
				<td align=center rowspan=2 colspan=2>".$_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center>KBL</td>
				<td align=center>KHT</td>
				<td align=center>KHL</td>
				<td align=center>Total</td>
				<td align=center>Jam</td>
				<td align=center>Rp/Jam</td>
				<td align=center>Lembur</td>
				<td align=center>Premi</td>
				<td align=center width=150px>Nama</td>
				<td align=center width=30px>Sat</td>
				<td align=center width=40px>Jumlah</td>
				<td align=center width=20px>#</td>
				
			</tr>
			</thead>";
		#==== Form Judul UMUM ====
		
		#=== Isi input UMUM ===
		$frm[3].="<tbody id=inputdetailumm>
				<script>inputdetailumm()</script>
				</tbody></table></fieldset>";
		
		#=== List data tersimpan input UMUM ===	
        $frm[3].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['umum'] . "</legend>
			<div id=loaddatadetailumm>
				<script>loaddatadetailumm()</script>
			</div></fieldset>";
			
		// #=== TAB SUPPORT ===
		// $optSupp="<option value='SUPPORT'>Support</option>";
		// $frm[4]="<table>
		// 		<td valign=top>
		// 		<fieldset style=float:left><legend>Notransaksi</legend>
		// 			<table height=25px>
		// 				<td>" . $_SESSION['lang']['jenis'] . "</td>
		// 				<td><input id=tipetransaksisup disabled class=myinputtext style=\"width:50px;\" value='SUPPORT'></td>
		// 				<td>" . $_SESSION['lang']['divisi'] . "</td>
		// 				<td><select style=\"width:150px;\"  id=divisisup>".$optSupp."</select></td>
		// 				<td><button id=tombolsimpandetailsup class=mybutton onclick=inputdetailsup()>" . $_SESSION['lang']['save'] . "</button></td>
		// 			</table>
		// 		</fieldset>
		// 		</td>
		// 		<td valign=top>
		// 		<fieldset style=float:left><legend>Rupiah / HK</legend>
		// 			<table height=25px>
		// 				<td>KBL</td>
		// 				<td><input id=rpperhkkblsup class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkbt."></td>
		// 				<td>KHT</td>
		// 				<td><input id=rpperhkkhtsup class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkht."></td>
		// 				<td>KHL</td>
		// 				<td><input id=rpperhkkhlsup class=myinputtextnumber style=\"width:70px;\" value=".@$rpperhkkhl."></td>
						
		// 			</table>
		// 		</fieldset>
		// 		</td>
		// 	</table>
		// 	<fieldset>
		// 	<legend>" . $_SESSION['lang']['umum'] . "</legend>
		// 	<table border=0 cellpadding=1 cellspacing=1 class=sortable>
		// 	<thead><tr class=rowheader>";
			
		// 	$rows="rowspan=3";	
		// 	$frm[4].="<td align=center ".$rows." width=20px>No</td>
		// 		<td align=center ".$rows.">".$_SESSION['lang']['departemen'] . "</td>
		// 		<td align=center ".$rows.">".$_SESSION['lang']['jabatan'] . "</td>
		// 		<td align=center ".$rows.">Komponen Gaji</td>
		// 		<td align=center ".$rows." width=200px>Keterangan</td>
		// 		<td align=center colspan=13>Tipe Karyawan</td>
				
		// 		<td align=center rowspan=3 colspan=2>".$_SESSION['lang']['action'] . "</td>
		// 	</tr>
		// 	<tr>
		// 		<td align=center colspan=3>KBL</td>
		// 		<td align=center colspan=3>KHT</td>
		// 		<td align=center colspan=3>KHL</td>
		// 		<td align=center colspan=4>Total</td>
				
		// 	</tr>
		// 	<tr>
		// 		<td align=center>TK</td>
		// 		<td align=center>HK</td>
		// 		<td align=center>Rupiah</td>
		// 		<td align=center>TK</td>
		// 		<td align=center>HK</td>
		// 		<td align=center>Rupiah</td>
		// 		<td align=center>TK</td>
		// 		<td align=center>HK</td>
		// 		<td align=center>Rupiah</td>
		// 		<td align=center>TK</td>
		// 		<td align=center>HK</td>
		// 		<td align=center colspan=2>Rupiah</td>
				
		// 	</tr>
		// 	</thead>";
		// #==== Form Judul SUPPORT ====
		
		// #=== Isi input SUPPORT ===
		// $frm[4].="<tbody id=inputdetailsup>
		// 		<script>inputdetailsup()</script>
		// 		</tbody></table></fieldset>";
		
		// #=== List data tersimpan input SUPPORT ===	
        // $frm[4].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
		// 	<div id=loaddatadetailsup>
		// 		<script>loaddatadetailsup()</script>
		// 	</div></fieldset>";
			
		$hfrm[0]='Pemeliharaan';
		$hfrm[1]=$_SESSION['lang']['panen'];
		$hfrm[2]='Pengangkutan';
		$hfrm[3]=$_SESSION['lang']['umum'];
		// $hfrm[4]='Support';

		# draw tab, jangan ganti parameter pertama, krn dipakai di javascript
		drawTab('FRM',$hfrm,$frm,175,'100%');

        CLOSE_BOX();
		
	break;
	case'loaddatadetailsupport':
		$tab='';
		$tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
			
		$rows="rowspan=3";	
		$tab.="<th align=center ".$rows." width=20px>No</th>
			<th align=center ".$rows.">".$_SESSION['lang']['departemen'] . "</th>
			<th align=center ".$rows.">".$_SESSION['lang']['jabatan'] . "</th>
			<th align=center ".$rows.">Komponen Gaji</th>
			<th align=center ".$rows.">Keterangan</th>
			<th align=center colspan=12>Tipe Karyawan</th>
			
			<th align=center rowspan=3 width=30px>".$_SESSION['lang']['action'] . "</th>
		</tr>
		<tr>
			<th align=center colspan=3>KBL</th>
			<th align=center colspan=3>KHT</th>
			<th align=center colspan=3>KHL</th>
			<th align=center colspan=3>Total</th>
			
		</tr>
		<tr>
			<th align=center>TK</th>
			<th align=center>HK</th>
			<th align=center>Rupiah</th>
			<th align=center>TK</th>
			<th align=center>HK</th>
			<th align=center>Rupiah</th>
			<th align=center>TK</th>
			<th align=center>HK</th>
			<th align=center>Rupiah</th>
			<th align=center>TK</th>
			<th align=center>HK</th>
			<th align=center>Rupiah</th>
			
		</tr>
		</thead>";
		
		
		$where='';
		if($divisi!==''){
			$where=" and divisi='".$divisi."'";
		}
		$str = "select * from ".$dbname.".kebun_rkbsupport where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' 
		and periode='".$periode."' and kodeorg='".$kodeorg."' ".$where.""; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$data=array();
		while($bar=$res->fetch()){
			$data[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']]=$bar['tipekary'];
			@$tk[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']]+=$bar['tk'];
			@$hk[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']]+=$bar['hk'];
			@$rupiah[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']]+=$bar['rupiah'];
			$ketxx[$bar['dept']][$bar['jabatan']][$bar['compgaji']]=$bar['keterangan'];
			$tpkary[$bar['tipekary']]=$bar['tipekary'];
		}
		if(count($data)>0){
			$no='0';
			$optdept=makeOption($dbname,'sdm_5departemen','kode,nama');
			$optjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
			$optcompt=makeOption($dbname,'sdm_ho_component','id,name');
			foreach($data as $dept => $valjab){
				foreach($valjab as $jabatan => $valkompgaji){
					foreach($valkompgaji as $kompgaji => $valtipekary){
						$no++;
						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center>".$no."</td>";	
						$tab.="<td>".$optdept[$dept]."</td>";	
						$tab.="<td>".$optjab[$jabatan]."</td>";	
						$tab.="<td>".$optcompt[$kompgaji]."</td>";	
						$tab.="<td align=left width=200px>".$ketxx[$dept][$jabatan][$kompgaji]."</td>";	
						$ttlrp=$ttlhk=$ttltk='';
						foreach($tpkary as $tipekary){
							$tab.="<td width=34px align=right>".number_format($tk[$dept][$jabatan][$kompgaji][$tipekary])."</td>";	
							$tab.="<td width=43px align=right>".number_format($hk[$dept][$jabatan][$kompgaji][$tipekary])."</td>";	
							$tab.="<td width=62px align=right>".number_format($rupiah[$dept][$jabatan][$kompgaji][$tipekary])."</td>";
							@$ttltk+=$tk[$dept][$jabatan][$kompgaji][$tipekary];
							@$ttlhk+=$hk[$dept][$jabatan][$kompgaji][$tipekary];
							@$ttlrp+=$rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							
							@$stttk[$jabatan][$tipekary]+=$tk[$dept][$jabatan][$kompgaji][$tipekary];
							@$sttlhk[$jabatan][$tipekary]+=$hk[$dept][$jabatan][$kompgaji][$tipekary];
							@$sttlrp[$jabatan][$tipekary]+=$rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							
							@$gstttk[$jabatan]+=$tk[$dept][$jabatan][$kompgaji][$tipekary];
							@$gsttlhk[$jabatan]+=$hk[$dept][$jabatan][$kompgaji][$tipekary];
							@$gsttlrp[$jabatan]+=$rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							
							@$gtstttk[$tipekary]+=$tk[$dept][$jabatan][$kompgaji][$tipekary];
							@$gtsttlhk[$tipekary]+=$hk[$dept][$jabatan][$kompgaji][$tipekary];
							@$gtsttlrp[$tipekary]+=$rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							
						}
						$tab.="<td width=34px align=right>".number_format($ttltk)."</td>";	
						$tab.="<td width=44px align=right>".number_format($ttlhk)."</td>";	
						$tab.="<td width=81px align=right>".number_format($ttlrp)."</td>";	
						
						$tab.="<td width=20px align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
							onclick=\"deletedetailsupport('".$notransaksi."','" . $tipetransaksi. "','".$periode."','".$kodeorg."','".$divisi."','".$dept."','".$jabatan."','".$kompgaji."');\" ></td>";	
						$tab.="</tr>";
					}
					$tab.="<tr class=rowcontent style=background-color:cyan>";
					$tab.="<td colspan=2></td><td colspan=3 align=left>Sub Total ".$optjab[$jabatan]."</td>";
					foreach($tpkary as $tipekary){
						$tab.="<td align=right>".number_format($stttk[$jabatan][$tipekary])."</td>";	
						$tab.="<td align=right>".number_format($sttlhk[$jabatan][$tipekary])."</td>";							
						$tab.="<td align=right>".number_format($sttlrp[$jabatan][$tipekary])."</td>";							
					}
						$tab.="<td align=right>".number_format($gstttk[$jabatan])."</td>";	
						$tab.="<td align=right>".number_format($gsttlhk[$jabatan])."</td>";							
						$tab.="<td align=right>".number_format($gsttlrp[$jabatan])."</td>";	
						$tab.="<td align=right></td>";	
				}
			}
			$tab.="<tr class=rowcontent style=background-color:skyblue>";
			$tab.="<td colspan=5 align=center>Total</td>";	
			foreach($tpkary as $tipekary){
				$tab.="<td align=right>".number_format($gtstttk[$tipekary])."</td>";	
				$tab.="<td align=right>".number_format($gtsttlhk[$tipekary])."</td>";	
				$tab.="<td align=right>".number_format($gtsttlrp[$tipekary])."</td>";	
				
				@$gtttk+=$gtstttk[$tipekary];
				@$gtthk+=$gtsttlhk[$tipekary];
				@$gttrp+=$gtsttlrp[$tipekary];
			}
				$tab.="<td align=right>".number_format($gtttk)."</td>";	
				$tab.="<td align=right>".number_format($gtthk)."</td>";	
				$tab.="<td align=right>".number_format($gttrp)."</td>";	
				$tab.="<td align=right></td>";	
		}
		
		$tab.="</tbody>";
		$tab.="</table>";
		echo $tab;
	break;
	case'simpandetailsup':
		$validasidt = ($notransaksi!='' and $tipetransaksi!='' and $periode!='' and $kodeorg!='' and $divisi!='' and $dept!='' and $jabatan!='' and ($rpkbl!='' or $rpkht!='' or $rpkhl!=''));
		
		if($validasidt){
			$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and dept='".$dept."' and kodekegiatan='".$jabatan."'";
			$res = fetchData($str);
			if(count($res)==0){
				# Simpan detail
				$str = "insert into " . $dbname . ".kebun_rkbdt (`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,`dept`,`kodekegiatan`,`updateby`)
				values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."','".$dept."','".$jabatan."','".$_SESSION['standard']['userid']."')";
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			}	
		}
		
		$validasi = ($notransaksi!='' and $tipetransaksi!='' and $periode!='' and $kodeorg!='' and $divisi!='' and $dept!='' and $compgaji!='' and ($rpkbl!='' or $rpkht!='' or $rpkhl!='') and $jabatan!='');
		if($validasi){
			
			for ($i=1;$i<=3;$i++){
				if($i==1){
					$tipekary=1;
					$tk=$tkkbl;
					$hk=$kbl;
					$rupiah=$rpkbl;
				}elseif($i==2){
					$tipekary=3;
					$tk=$tkkht;
					$hk=$kht;
					$rupiah=$rpkht;
				}elseif($i==3){
					$tipekary=4;
					$tk=$tkkhl;
					$hk=$khl;
					$rupiah=$rpkhl;
				}
				$str = "insert into " . $dbname . ".kebun_rkbsupport (`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,`dept`, `jabatan`, `tipekary`, `compgaji`, `tk`, `hk`, `rupiah`,`keterangan`)
				values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."','".$dept."','".$jabatan."','".$tipekary."','".$compgaji."','".$tk."','".$hk."','".$rupiah."','".$ket."')"; #exit("error".$str);
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			}
		}
	break;	
	case'deletedetailsupport':
		
		$str = "select * from ".$dbname.".kebun_rkbsupport where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and dept='".$dept."' and jabatan='".$jabatan."'";
		$res = fetchData($str);
		if(count($res)<=3){
			$str = "delete from " . $dbname . ".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and dept='".$dept."' and kodekegiatan='".$jabatan."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}			
		}
	
		$str = "delete from " . $dbname . ".kebun_rkbsupport where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and dept='".$dept."' and jabatan='".$jabatan."' and compgaji='".$compgaji."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;	
	case'listsupport':
		$tab="";
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		$tab.="<tbody>";
				
		$str = "select * from ".$dbname.".kebun_rkbsupport where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and dept='".$dept."' and jabatan='".$jabatan."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$data=array();
		while($bar=$res->fetch()){
			$data[$bar['compgaji']][$bar['tipekary']]=$bar['tipekary'];
			$tk[$bar['compgaji']][$bar['tipekary']]=$bar['tk'];
			$hk[$bar['compgaji']][$bar['tipekary']]=$bar['hk'];
			$rupiah[$bar['compgaji']][$bar['tipekary']]=$bar['rupiah'];
			$ket[$bar['compgaji']]=$bar['keterangan'];
		}
		if(count($data)>0){
			foreach(@$data as $kompgaji => $valtipekary){
				$optcompt=makeOption($dbname,'sdm_ho_component','id,name',"id='".$kompgaji."'");
				$tab.="<tr class=rowcontent>";
				$tab.="<td width=119px>".$optcompt[$kompgaji]."</td>";	
				$tab.="<td width=202px>".$ket[$kompgaji]."</td>";	
				$ttlrp=$ttlhk=$ttltk='';
				foreach($valtipekary as $tipekary){
					$tab.="<td width=34px align=right>".number_format($tk[$kompgaji][$tipekary])."</td>";	
					$tab.="<td width=43px align=right>".number_format($hk[$kompgaji][$tipekary])."</td>";	
					$tab.="<td width=62px align=right>".number_format($rupiah[$kompgaji][$tipekary])."</td>";
					@$ttltk+=$tk[$kompgaji][$tipekary];
					@$ttlhk+=$hk[$kompgaji][$tipekary];
					@$ttlrp+=$rupiah[$kompgaji][$tipekary];
					
				}
				$tab.="<td width=34px align=right>".number_format($ttltk)."</td>";	
				$tab.="<td width=44px align=right>".number_format($ttlhk)."</td>";	
				$tab.="<td width=81px align=right>".number_format($ttlrp)."</td>";	
				$tab.="<td width=20px align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
					onclick=\"deletedetailsupport('".$notransaksi."','" . $tipetransaksi. "','".$periode."','".$kodeorg."','".$divisi."','".$dept."','".$jabatan."','" . $kompgaji. "');\" ></td>";	
				$tab.="</tr>";
			}			
		}else{
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=14></td>";
			$tab.="</tr>";
		}
		
		$tab.="</tbody>";
		$tab.="</table>";
		echo $tab;
	break;
	
	case'getjabsup':
		$str = "select * from ".$dbname.".sdm_5jabatan where kodejabatan in (select kodejabatan from ".$dbname.".datakaryawan where lokasitugas='".$kodeorg."' and tipekaryawan in ('1','2','3','4','5','6') and bagian='".$dept."' ) order by namajabatan asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optJab = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
		while($bar=$res->fetch()){
			$optJab.="<option value=".$bar['kodejabatan'].">".$bar['namajabatan']."</option>";
		}
		echo $optJab;
	break;
	case'getttltkhk':
		$tanggal=$periode."-01";
		$tahun=substr($periode,0,4);
		
		$jlhkarykbl=$jlhkarykht=$jlhkarykhl='0';
		$gpkbl=$gpkht=$gpkhl='0';
		# ambil datakaryawan
		$strx="select * from ".$dbname.".datakaryawan where  (tanggalkeluar>='" . $tanggal . "' or tanggalkeluar='0000-00-00') and lokasitugas='".$kodeorg."' and  bagian='".$dept."' and kodejabatan='".$jabatan."'";
		$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while($barx=$resx->fetch()){
			# ambil gaji
			$str = "select * from ".$dbname.".sdm_5gajipokok where karyawanid ='".$barx['karyawanid']."' and tahun='".$tahun."' and idkomponen='".$compgaji."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			
			if($barx['tipekaryawan']=='1'){
				$jlhkarykbl+=1;
				$gpkbl+=$bar['jumlah'];
			}else if($barx['tipekaryawan']=='3'){
				$jlhkarykht+=1;
				$gpkht+=$bar['jumlah'];
			}else if($barx['tipekaryawan']=='4'){
				$jlhkarykhl+=1;
				$gpkhl+=$bar['jumlah'];
			}
		}
		
		echo $jlhkarykbl."####".$jlhkarykht."####".$jlhkarykhl."####".$gpkbl."####".$gpkht."####".$gpkhl;
	break;
	case'inputdetailsup':
	$where='';
	$where.=" and lokasitugas='".$kodeorg."'";
	
	$str = "select * from ".$dbname.".sdm_5departemen where kode in (select bagian from ".$dbname.".datakaryawan where 1=1 ".$where." and tipekaryawan in ('1','2','3','4','5','6')) order by nama asc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$optdept = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
	while($bar=$res->fetch()){
		$optdept.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
	}
	
	$str = "select * from ".$dbname.".sdm_ho_component where plus='1' order by id asc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$optcomp = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
	while($bar=$res->fetch()){
		$optcomp.="<option value=".$bar['id'].">".$bar['name']."</option>";
	}

	echo"<tr class=rowcontent>";
	echo"<td valign=top id=no align=center>#</td>
			<td valign=top width=100px><select class=select2 style=width:100px id=dept onchange=getjabsup()>".$optdept."</select></td>
			<td valign=top width=120px><select class=select2 style=width:120px id=jabatansup onchange=listsupport()></select></td>
			<td valign=top width=120px><select class=select2 style=width:120px id=compgaji onchange=getttltkhk()>".$optcomp."</select></td>
			<td><input id=keterangan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:200px;\"></td>
			<td valign=top width=30px><input id=tkkbl onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>
			<td valign=top width=40px><input id=hkkbl title=\"Total TK x 25 HKE\" onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td valign=top width=60px><input id=rpkbl onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:60px;\"></td>
			
			<td valign=top width=30px><input id=tkkht onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>
			<td valign=top width=40px><input id=hkkht  title=\"Total TK x 25 HKE\" onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td valign=top width=60px><input id=rpkht onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:60px;\"></td>
			
			<td valign=top width=30px><input id=tkkhl onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>
			<td valign=top width=40px><input id=hkkhl title=\"Total TK x 25 HKE\" onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td valign=top width=60px><input id=rpkhl onkeyup=gettotalsup() class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:60px;\"></td>
			
			<td valign=top width=30px><input id=ttltksup disabled class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>
			<td valign=top width=40px><input id=ttlhksup disabled class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td valign=top width=100px colspan=2><input id=ttlrpsup disabled class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:100px;\"></td>
			
			
			
			<td align=center rowspan=2 valign=top width=20px>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"simpandetailsup('clear')\" src='images/save.png'/>
			</td>
			<td align=center rowspan=2 valign=top width=20px>
				<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetailallsup()\" src='images/clear.png'/>
			</td>
			
			</tr><tr class=rowcontent>
			<td></td><td></td><td></td>
			<td colspan=15>
				<div id=listsupport></div>
			</td>
			</tr><tr>
			<td colspan=18></td>
			<td align=center>
				<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetailsupport()\" src='images/refresh2.png'/>
			</td>
			<td align=center>
				<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
			</td>
        </tr>";
	break;
	case'inputdetailumm':
	$where='';
	if($divisi!=''){
		$where.=" and subbagian='".$divisi."'";
	}
	$where.=" and lokasitugas='".$kodeorg."'";
	
	$optJab="";
	$str = "select * from ".$dbname.".sdm_5jabatan where kodejabatan in (select kodejabatan from ".$dbname.".datakaryawan where 1=1 ".$where." and tipekaryawan in ('1','2','3','4','5','6')) order by namajabatan asc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$optJab = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
	while($bar=$res->fetch()){
		@$optJab.="<option value=".$bar['kodejabatan'].">".$bar['namajabatan']."</option>";
	}
	
	$optNmBrg="";
	$str = "select * from ".$dbname.".log_5masterbarang where inactive='0' and namabarang!='' and kelompokbarang < '800' order by namabarang asc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$optNmBrg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
	while($bar=$res->fetch()){
		$optNmBrg.="<option value=".$bar['kodebarang'].">".$bar['kodebarang']." - ".@$bar['namabarang']."</option>";
	}
	
	echo"<tr class=rowcontent>";
	echo"<td rowspan=2 valign=top id=no align=center>#</td>
			<td rowspan=2 valign=top><select class=select2 style=width:200px id=jabatanumm>".$optJab."</select>
			</td>
			<td rowspan=2 valign=top width=40px><input id=kblumm onkeyup=\"gettotalhkumm()\" class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top width=40px><input id=khtumm onkeyup=\"gettotalhkumm()\" class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top width=40px><input id=khlumm onkeyup=\"gettotalhkumm()\" class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top width=40px><input disabled id=ttlhkumm class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top width=70px><input disabled id=upahumm class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:70px;\"></td>
			
			<td rowspan=2 valign=top width=40px><input onkeyup=\"gettotalhkumm();\" id=jamumm class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top width=50px><input onkeyup=\"gettotalhkumm();\" id=rpjamumm class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
			<td rowspan=2 valign=top width=70px><input disabled id=rplbrumm class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:70px;\"></td>
			<td rowspan=2 valign=top width=70px><input onkeyup=\"z.numberFormat('rplpremiumm',2);\" id=rplpremiumm class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:70px;\"></td>
			
			<td valign=top><select class=select2 style=width:150px id=kodebarangumm onchange=getsatmat('umum')>".$optNmBrg."</select></td>
				
			<td valign=top width=30px><input id=satmatumm disabled class=myinputtext style=\"width:30px;\"></td>
			<td valign=top><input id=jlhmatumm class=myinputtextnumber style=\"width:40px;\"></td>
			<td valign=top align=center><img class='resicon' title='Tambah Material' id=tombolsimpanmaterialumm src='images/plus.png' onclick=simpandetailumm()></td>
			<td rowspan=2 valign=top id=ttlrpumm align=right></td>
			
			<td align=center rowspan=2 valign=top width=20px>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"simpandetailumm('clear')\" src='images/save.png'/>
			</td>
			<td align=center rowspan=2 valign=top width=20px>
				<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetailallumm()\" src='images/clear.png'/>
			</td>
			
			</tr><tr class=rowcontent>
			<td colspan=4>
				<div id=listmaterialumm></div>
			</td>
			</tr><tr>
			<td colspan=16></td>
			<td align=center>
				<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetailumm()\" src='images/refresh2.png'/>
			</td>
			<td align=center>
				<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
			</td>
        </tr>";
	break;
	case'loaddatadetailumm':
			$tab='';
			$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='min-width:80%'>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['divisi'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['jabatan'] . "</td>
				
				<td align=center rowspan=1 colspan=4>Tenaga Kerja</td>
				<td align=center rowspan=2 colspan=1>Upah</td>
				<td align=center rowspan=1 colspan=4>Lembur dan Premi</td>
				<td align=center rowspan=1 colspan=3>Material</td>
				<td align=center rowspan=2 colspan=1>Totah Rupiah</td>
				<td align=center rowspan=2 colspan=1>".$_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center>KBL</td>
				<td align=center>KHT</td>
				<td align=center>KHL</td>
				<td align=center>Total</td>
				<td align=center>Jam</td>
				<td align=center>Rp/Jam</td>
				<td align=center>Lembur</td>
				<td align=center>Premi</td>
				<td align=center width=150px>Nama</td>
				<td align=center width=30px>Sat</td>
				<td align=center width=40px>Jumlah</td>
				
			</tr>
			</thead>";
			$where='';
			if($divisi!==''){
				$where=" and divisi='".$divisi."'";
			}
			$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' 
			and periode='".$periode."' and kodeorg='".$kodeorg."' ".$where." order by divisi asc";
			// and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$nmjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$bar['kodekegiatan']."'");
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td valign=top align=center>" . $no . "</td>";
				if($bar['divisi']==''){
					$kodediv='UMUM';
				}else{
					$kodediv=$bar['divisi'];
				}
				$tab.="<td valign=top>".$kodediv."</td>";
				$tab.="<td valign=top>".$nmjab[$bar['kodekegiatan']]."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['KBL'])."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['KHT'])."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['KHL'])."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'])."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['upah'])."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['jamlembur'])."</td>";
				if($bar['jamlembur']!=0 && $bar['rplembur']!=0){
					$tab.="<td valign=top align=right>".number_format($bar['rplembur']/$bar['jamlembur'])."</td>";					
				}else{
					$tab.="<td valign=top align=right>0</td>";					
				}
				$tab.="<td valign=top align=right>".number_format($bar['rplembur'])."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['premi'])."</td>";
				$tab.="<td valign=top align=right colspan=3>";
					$strx = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$bar['norkb']."' and tipetransaksi='".$bar['tipetransaksi']."' and periode='".$bar['periode']."' and kodeorg='".$bar['kodeorg']."' and divisi='".$bar['divisi']."' and kodekegiatan='".$bar['kodekegiatan']."'"; 
					$jlh = fetchData($strx);
					$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
					$resx->setFetchMode(PDO::FETCH_ASSOC);
					$nox='';$ttlrpbahan='';
					if(count($jlh)>0){
						$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
						$tab.="<tbody>";
						while($barx=$resx->fetch()){
							$nox++;
							$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$barx['kodebarang']."'");
							$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$barx['kodebarang']."'");
							$tab.="<tr class=rowcontent>";
							$tab.="<td width=20px align=center>".$nox."</td>";
							if(strlen($optnmbrg[$barx['kodebarang']])>20){
								$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,20)." ...";
							}else{
								$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,20)."";
							}
							$tab.="<td width=150px>".$namabarang."</td>";
							$tab.="<td width=30px>".$nmsat[$barx['kodebarang']]."</td>";
							$tab.="<td width=40px align=right>".number_format($barx['kwantitas'],2)."</td>";
							$ttlrpbahan+=$barx['jumlahrp'];
						}
						$tab.="</tr>";
						$tab.="</tbody>";
						$tab.="</table>";					
					}
					
				$tab.="</td>";
				$totalrp=$bar['upah']+$bar['premi']+$bar['rplembur'];
				$tab.="<td valign=top align=right>".number_format($totalrp)."</td>";
				$tab.="<td align=center valign=top>";
				$tab.="
					<img src=images/application/application_delete.png class=resicon  title='Delete' 
					onclick=\"deletedetailumm('" . $bar['norkb'] . "','" . $bar['tipetransaksi'] . "','" . $bar['periode'] . "','".$bar['kodeorg']."','".$bar['divisi']."','".$bar['kodekegiatan']."','".$bar['blok']."');\" >
					</td>";
				
				@$ttlkbl+=$bar['KBL'];
				@$ttlkht+=$bar['KHT'];
				@$ttlkhl+=$bar['KHL'];
				@$ttlupah+=$bar['upah'];
				@$ttlpremi+=$bar['premi'];
				@$ttljamlembur+=$bar['jamlembur'];
				@$ttlrplembur+=$bar['rplembur'];
				@$gtrp+=$totalrp;
				
			}		
			$tab.="</tr>";
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=3 bgcolor=cyan align=center><b>TOTAL</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlkht)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlkhl)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl+$ttlkht+$ttlkhl)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlupah)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttljamlembur)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlrplembur/$ttljamlembur)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlrplembur)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlpremi)."</b></td>";
			$tab.="<td bgcolor=cyan align=right colspan=3></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_format($gtrp)."</b></td>";
			$tab.="<td bgcolor=cyan align=right></td>";
			$tab.="</tr>";
			$tab.="</table>";
			
		echo $tab;
	break;
	case'deletedetailumm':
		$str = "delete from " . $dbname . ".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		$str = "delete from " . $dbname . ".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
    break;
	case'daftarmaterialumm':
		$tab="";
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		$tab.="<tbody>";
				
		$str = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$bar['kodebarang']."'");
			$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$tab.="<tr class=rowcontent>";
			if(strlen($optnmbrg[$bar['kodebarang']])>20){
				$namabarang="".substr(ucfirst(strtolower($optnmbrg[$bar['kodebarang']])),0,20)." ...";
			}else{
				$namabarang="".substr(ucfirst(strtolower($optnmbrg[$bar['kodebarang']])),0,20)."";
			}
			$tab.="<td width=149px>".$namabarang."</td>";
			$tab.="<td width=34px>".$nmsat[$bar['kodebarang']]."</td>";
			$tab.="<td align=right>".number_format($bar['kwantitas'],2)."</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody>";
		$tab.="</table>";
		echo $tab;
	break;
	case'simpandetailumm':
		$validasidt = ($notransaksi!='' and $tipetransaksi!='' and $periode!='' and $kodeorg!='' and $kegiatan!='' and ($upah!='' or $premi!='' or $lembur!=''));
		
		if($validasidt){
			$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."'";
			$res = fetchData($str);
			if(count($res)==0){
				# Simpan detail
				$str = "insert into " . $dbname . ".kebun_rkbdt (`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,`kodekegiatan`,`KBL`,`KHT`,`KHL`,`upah`,`premi`,`jamlembur`,`rplembur`,`updateby`)
				values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."','".$kegiatan."','".$kbl."','".$kht."','".$khl."','".$upah."','".$premi."','".$jamlembur."','".$lembur."','".$_SESSION['standard']['userid']."')";
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			}	
		}
		
		$validasi = ($notransaksi!='' and $tipetransaksi!='' and $periode!='' and $kodeorg!='' and $kegiatan!='' and $kodebarang!='' and $jlhmat!='');
		if($validasi){
			$str = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and kodekegiatan='".$kegiatan."' and kodebarang='".$kodebarang."' and divisi='".$divisi."'";
			$res = fetchData($str);
			if(count($res)>0){
				exit("Warning : Data Sudah Ada.");
			}
			# Simpan material
			$str = "insert into " . $dbname . ".kebun_rkbmaterial (`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,`kodekegiatan`, `kodebarang`, `kwantitas`)
			values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."','".$kegiatan."','".$kodebarang."','".$jlhmat."')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}			
		}
	break;	
	case'inputdetailangkut':
	$tab="";
	$dtgtrp=array();$dtgtrpkg=array();$dthargaalong=array();$dthargaborongan=array();$dthasilkerjaborongan=array();$dtjarakpks=array();
	$dtkapasitas=array();$dtKBL=array();$dtkgpremi=array();$dtkgsendiri=array();$dtKHL=array();$dtKHT=array();$dtkm=array();$dtnorma=array();
	$dtoutputkgperhk=array();$dtpersenalong=array();$dtpersenkontrak=array();$dtpersensendiri=array();$dtpremi=array();$dtrpalong=array();
	$dtrpkgangkut=array();$dtrpkgpremi=array();$dtrpkmangkut=array();$dtrpupahkg=array();$dtrupiahborongan=array();$dtthk=array();
	$dttonalong=array();$dttrippks=array();$dttrp=array();$dttrpkg=array();$dtttlkgbasis=array();$dtttlrpsendiri=array();$dtupah=array();
	$kg=array();$tahuntanam=array();$normadt=array();

	# ambil data
	$str = "select * from ".$dbname.".kebun_rkbdt a where a.norkb='".$notransaksi."' and a.tipetransaksi='PANEN' and a.periode='".$periode."' and a.kodeorg='".$kodeorg."' and a.divisi='".$divisi."'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$blokdt[$bar['blok']]=$bar['blok'];
		@$kg[$bar['blok']]+=$bar['hasilkerjakg'];
		@$normadt[$bar['blok']]+=$bar['norma'];
	}
	
	#=indra
	$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' 
		and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$dtjarakpks[$bar['blok']]=$bar['jarakpks'];
		$dtpersensendiri[$bar['blok']]=$bar['persensendiri'];
		$dtkapasitas[$bar['blok']]=$bar['kapasitas'];
		$dttrippks[$bar['blok']]=$bar['trippks'];
		
		$dtkm[$bar['blok']]=$bar['km'];
		$dtkgsendiri[$bar['blok']]=$bar['kgsendiri'];
		
		$dtrpkgangkut[$bar['blok']]=$bar['ttlrpsendiri']/$bar['kgsendiri'];
		$dtrpkmangkut[$bar['blok']]=$bar['ttlrpsendiri']/$bar['km'];
		$dtttlrpsendiri[$bar['blok']]=$bar['ttlrpsendiri'];
		
		
		
		// $tab.="<td align=right>".number_format($bar[''])."</td>";
		// $tab.="<td align=right>".number_format($bar[''])."</td>";
		// $tab.="<td align=right>".number_format($bar[''])."</td>";
		// $tab.="<td align=right>".number_format($bar[''])."</td>";
		// $tab.="<td align=right>".number_format($bar[''])."</td>";
		
		
		// $dtpersenkontrak[$bar['blok']]=100-$bar['persensendiri'];
		$dtpersenkontrak[$bar['blok']]=$bar['persensendiri'];
		$dthasilkerjaborongan[$bar['blok']]=$bar['hasilkerjaborongan'];
		$dthargaborongan[$bar['blok']]=$bar['hargaborongan'];
		$dtrupiahborongan[$bar['blok']]=$bar['rupiahborongan'];
		
		
		$dtpersenalong[$bar['blok']]=$bar['persenalong'];
		$dttonalong[$bar['blok']]=$bar['tonalong'];
		$dthargaalong[$bar['blok']]=$bar['hargaalong'];
		$dtrpalong[$bar['blok']]=$bar['rpalong'];
		
		$dtpersenmekanis[$bar['blok']] 	= $bar['persenmekanis'];
		$dttonmekanis[$bar['blok']] 	= $bar['tonmekanis'];
		$dthargamekanis[$bar['blok']] 	= $bar['hargamekanis'];
		$dtrpmekanis[$bar['blok']] 		= $bar['rpmekanis'];

		
		$dtoutputkgperhk[$bar['blok']]=$bar['outputkgperhk'];
		$dtnorma[$bar['blok']]=$bar['norma'];
		$dtKBL[$bar['blok']]=$bar['KBL'];
		$dtKHT[$bar['blok']]=$bar['KHT'];
		$dtKHL[$bar['blok']]=$bar['KHL'];  
		
		$dtthk[$bar['blok']]=$bar['KBL']+$bar['KHT']+$bar['KHL'];
		$dtttlkgbasis[$bar['blok']]=$bar['ttlkgbasis'];
		$dtupah[$bar['blok']]=$bar['upah'];
		
		$dtrpupahkg[$bar['blok']]=$bar['upah']/$bar['ttlkgbasis'];
		$dtkgpremi[$bar['blok']]=$bar['kgpremi'];
		$dtrpkgpremi[$bar['blok']]=$bar['premi']/$bar['kgpremi'];
		$dtpremi[$bar['blok']]=$bar['premi'];
		
		$dttrp[$bar['blok']]=$bar['upah']+$bar['premi'];
		$dttrpkg[$bar['blok']]=($bar['upah']+$bar['premi'])/$bar['kgsendiri'];
		$dtgtrp[$bar['blok']]=$bar['upah']+$bar['premi']+$bar['rupiahborongan']+$bar['ttlrpsendiri'];
		$dtgtrpkg[$bar['blok']]=($bar['upah']+$bar['premi']+$bar['rupiahborongan']+$bar['ttlrpsendiri'])/$bar['hasilkerjakg'];
		
	}
	
		
	
	
	$no='';
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center colspan=25></td>";

	$tab.="<td><input id=persenkblangkut title='Isikan Persen' onkeyup=\"proporsihkangkut();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\" value=0></td>";
	$tab.="<td><input id=persenkhtangkut  title='Isikan Persen' onkeyup=\"proporsihkangkut();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\" value=0></td>";
	$tab.="<td><input id=persenkhlangkut title='Isikan Persen' onkeyup=\"proporsihkangkut();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\" value=0></td>";
	$tab.="<td align=center colspan=11></td>";
	$tab.="</tr>";
	if(count(@$blokdt)==0){
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=36>Silahkan proses RKB Panen terlebih dahulu.</td>";
		$tab.="</tr>";
	}else{
		foreach($blokdt as $tt){
			if($kg[$tt]!=''){
				$no++;
				$tab.="<tr class=rowcontent id=rowangkut".$no.">";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=center>".$nmorg[$tt]."</td>";
				$tab.="<td hidden align=center id=ttangkut".$no.">".$tt."</td>";
				$tab.="<td align=right id=ttlprodangkut".$no.">".number_format($normadt[$tt],2)."</td>";
				$tab.="<td><input id=jarakpksangkut".$no."  value='".$dtjarakpks[$tt]."' onkeyup=\"getkalkulasiangkut('".$no."');\"  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
				$tab.="<td style=\"width:30px;\"><input id=persensdrangkut".$no."  value='".$dtpersensendiri[$tt]."' onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
				$tab.="<td style=\"width:40px;\"><input id=kapsdrangkut".$no."  value='".$dtkapasitas[$tt]."' onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>";
				$tab.="<td align=right id=trippksangkut".$no.">".number_format($dttrippks[$tt])."</td>";
				$tab.="<td align=right id=kmangkut".$no.">".number_format($dtkm[$tt])."</td>";
				$tab.="<td align=right id=tonangkut".$no.">".number_format($dtkgsendiri[$tt])."</td>";
				
				$tab.="<td style=\"width:40px;\"><input id=rpkgangkut".$no." value='".number_format($dtrpkgangkut[$tt])."' onkeyup=\"getkalkulasiangkut('".$no."','rpkg');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>";
				$tab.="<td style=\"width:50px;\"><input id=rpkmangkut".$no." value='".number_format($dtrpkmangkut[$tt])."'  onkeyup=\"getkalkulasiangkut('".$no."','rpkm');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
				
				$tab.="<td align=right id=ttlrpsdrangkut".$no.">".number_format($dtttlrpsendiri[$tt])."</td>";
				
				// $tab.="<td align=right id=persenkontangkut".$no.">".number_format($dtpersenkontrak[$tt])."</td>";
				// $tab.="<td align=right id=tonkontangkut".$no.">".number_format($dthasilkerjaborongan[$tt])."</td>";
				$tab.="<td align=right>
					<input id=persenkontangkut".$no." value='".number_format($dtpersenkontrak[$tt])."' nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\">
				</td>";
				$tab.="<td align=right>
					<input id=tonkontangkut".$no." value='".number_format($dthasilkerjaborongan[$tt])."' nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\">
				</td>";
				$tab.="<td style=\"width:40px;\"><input id=rpkgkontangkut".$no."  value='".number_format($dthargaborongan[$tt])."'  onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>";
				$tab.="<td align=right id=ttlrpkontangkut".$no.">".number_format($dtrupiahborongan[$tt])."</td>";
				
				#along
				$tab.="<td align=center width=30px><input id=persenalong".$no."  value='".number_format($dtpersenalong[$tt])."'  onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
				$tab.="<td align=right id=tonalong".$no.">".number_format($dttonalong[$tt])."</td>";
				$tab.="<td style=\"width:40px;\"><input id=hargalong".$no."  value='".number_format($dthargaalong[$tt])."'  onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>";
				$tab.="<td align=right id=rpalong".$no.">".number_format($dtrpalong[$tt])."</td>";
				
				#mekanis
				$tab.="<td align=center width=30px><input id=persenmekanis".$no."  value='".number_format($dtpersenmekanis[$tt])."'  onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
				$tab.="<td align=right id=tonmekanis".$no.">".number_format($dttonmekanis[$tt])."</td>";
				$tab.="<td style=\"width:40px;\"><input id=hargamekanis".$no."  value='".number_format($dthargamekanis[$tt])."'  onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>";
				$tab.="<td align=right id=rpmekanis".$no.">".number_format($dtrpmekanis[$tt])."</td>";
				
				$tab.="<td style=\"width:40px;\"><input id=outputkghkangkut".$no."  value='".number_format($dtoutputkgperhk[$tt])."' onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>";
				// $tab.="<td style=\"width:40px;\"><input id=basiskghkangkut".$no."  value='".number_format($dtnorma[$tt])."'  onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>";
				$tab.="<td style=\"width:30px;\"><input disabled id=kblangkut".$no."  value='".number_format($dtKBL[$tt])."' onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
				$tab.="<td style=\"width:30px;\"><input disabled id=khtangkut".$no."  value='".number_format($dtKHT[$tt])."' onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
				$tab.="<td style=\"width:30px;\"><input disabled id=khlangkut".$no."  value='".number_format($dtKHL[$tt])."' onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
				
				$tab.="<td align=right id=ttlhkangkut".$no.">".number_format($dtthk[$tt])."</td>";
				// $tab.="<td align=right id=ttlkgbssangkut".$no.">".number_format($dtttlkgbasis[$tt])."</td>";
				$tab.="<td align=right id=ttlrphkangkut".$no.">".number_format($dtupah[$tt])."</td>";
				$tab.="<td align=right id=rpperhkhkangkut".$no.">".number_format($dtrpupahkg[$tt])."</td>";
				// $tab.="<td align=right id=kgpremiangkut".$no.">".number_format($dtkgpremi[$tt])."</td>";
				$tab.="<td align=right>
					<input id=kgpremiangkut".$no." value='".number_format($dtkgpremi[$tt])."' nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\">
				</td>";
				
				$tab.="<td style=\"width:40px;\"><input id=rpkgpremiangkut".$no."  value='".number_format($dtrpkgpremi[$tt])."' onkeyup=\"getkalkulasiangkut('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>";
				// $tab.="<td align=right id=ttlrppremiangkut".$no.">".number_format($dtpremi[$tt])."</td>";
				$tab.="<td align=right>
					<input id=ttlrppremiangkut".$no." value='".number_format($dtpremi[$tt])."' nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\">
				</td>";
				
				$tab.="<td align=right id=ttlupahpremiangkut".$no.">".number_format($dttrp[$tt])."</td>";
				$tab.="<td align=right id=rpkgupahpremiangkut".$no.">".number_format($dttrpkg[$tt])."</td>";
				$tab.="<td align=right id=gtangkut".$no.">".number_format($dtgtrp[$tt])."</td>";
				$tab.="<td align=right id=gtrpkgangkut".$no.">".number_format($dtgtrpkg[$tt])."</td>";
				
			}
		}
		$tab.="<input hidden id=jlhbrsangkut value=".$no.">";
		$tab.="</tr>";
		$tab.="<tr>";
		$tab.="<td align=center colspan=36><button class=mybutton onclick=simpanallangkut('".$no."')>" . $_SESSION['lang']['save'] . "</button></td>";
		$tab.="</tr>";		
	}
	echo $tab;
	break;
	case'loaddatadetailangkut':
			$tab='';
			$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
				<thead><tr class=rowheader>";
			$rows="rowspan=4";	
			$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['divisi'] . "</td>
				<td align=center ".$rows.">Blok</td>
				<td align=center ".$rows.">".$_SESSION['lang']['produksi'] . "</td>
				<td align=center ".$rows." width=30px>Jarak ke PKS KM</td>
				
				<td align=center rowspan=1 colspan=8>Angkutan Sendiri</td>
				<td align=center rowspan=1 colspan=4>Angkutan Kontrak</td>
				<td align=center rowspan=1 colspan=4>Langsir Manual</td>
				<td align=center rowspan=1 colspan=4>Langsir Mekanis</td>
				<td align=center rowspan=1 colspan=12>Biaya Bongkar Muat</td>
				<td align=center rowspan=1 colspan=2>Total<br>Biaya</td>
			</tr>
			<tr>
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kap<br>Kg</td>
				<td align=center rowspan=3>Trip<br>PKS</td>
				<td align=center rowspan=3>KM</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Rp/KM</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>

				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center colspan=7>Upah</td>
				<td align=center colspan=3>Premi</td>
				<td align=center colspan=2>Total</td>
				
				<td align=center rowspan=3>Rp</td>
				<td align=center rowspan=3>Rp/Kg</td>
			</tr>
			<tr>
				<td align=center rowspan=2>Output<br>Kg/HK</td>
				<td align=center rowspan=1 colspan=4>HK</td>
				<td align=center rowspan=1 colspan=2>Total Upah</td>
				<td align=center rowspan=2>Kg</td>
				<td align=center rowspan=2>Rp/Kg</td>
				<td align=center rowspan=2>Rp</td>
				<td align=center rowspan=2>Rp</td>
				<td align=center rowspan=2>Rp/Kg</td>
			</tr>
			<tr>
				<td align=center >KBL</td>
				<td align=center >KHT</td>
				<td align=center >KHL</td>
				<td align=center >Total</td>
				<td align=center >Rp</td>
				<td align=center >Rp/Kg</td>
				
			</tr>
			</thead>";
		$where='';
		if($divisi!==''){
			$where=" and divisi='".$divisi."'";
		}
		$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' 
		and periode='".$periode."' and kodeorg='".$kodeorg."' ".$where." order by divisi asc, tahuntanam asc";
		//and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while ($bar = $res->fetch()) {
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=center>".$bar['divisi']."</td>";
			$tab.="<td align=center>".$nmorg[$bar['blok']]."</td>";
			$tab.="<td align=right>".number_format($bar['hasilkerjakg'])."</td>";
			$tab.="<td align=right>".number_format($bar['jarakpks'])."</td>";
			$tab.="<td align=right>".number_format($bar['persensendiri'])."</td>";
			$tab.="<td align=right>".number_format($bar['kapasitas'])."</td>";
			$tab.="<td align=right>".number_format($bar['trippks'])."</td>";
			$tab.="<td align=right>".number_format($bar['km'])."</td>";
			$tab.="<td align=right>".number_format($bar['kgsendiri'])."</td>";
			if($bar['ttlrpsendiri']!=0){
				$tab.="<td align=right>".number_format($bar['ttlrpsendiri'] / @$bar['kgsendiri'])."</td>";
				$tab.="<td align=right>".number_format($bar['ttlrpsendiri'] / @$bar['km'])."</td>";
			}else{
				$tab.="<td align=right>0</td>";
				$tab.="<td align=right>0</td>";
			}
			$tab.="<td align=right>".number_format($bar['ttlrpsendiri'])."</td>";
			
			
			$tab.="<td align=right>".number_format($bar['persensendiri'])."</td>";
			$tab.="<td align=right>".number_format($bar['hasilkerjaborongan'])."</td>";
			$tab.="<td align=right>".number_format($bar['hargaborongan'])."</td>";
			$tab.="<td align=right>".number_format($bar['rupiahborongan'])."</td>";
			
			$tab.="<td align=right>".number_format($bar['persenkontrak'])."</td>";
			$tab.="<td align=right>".number_format($bar['tonkontrak'])."</td>";
			$tab.="<td align=right>".number_format($bar['hargakontrak'])."</td>";
			$tab.="<td align=right>".number_format($bar['rpkontrak'])."</td>";

			$tab.="<td align=right>".number_format($bar['persenalong'])."</td>";
			$tab.="<td align=right>".number_format($bar['tonalong'])."</td>";
			$tab.="<td align=right>".number_format($bar['hargaalong'])."</td>";
			$tab.="<td align=right>".number_format($bar['rpalong'])."</td>";
			
			$tab.="<td align=right>".number_format($bar['outputkgperhk'])."</td>";
			// $tab.="<td align=right>".number_format($bar['norma'])."</td>";
			$tab.="<td align=right>".number_format($bar['KBL'])."</td>";
			$tab.="<td align=right>".number_format($bar['KHT'])."</td>";
			$tab.="<td align=right>".number_format($bar['KHL'])."</td>";
			
			$tab.="<td align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'])."</td>";
			// $tab.="<td align=right>".number_format($bar['ttlkgbasis'])."</td>";
			$tab.="<td align=right>".number_format($bar['upah'])."</td>";
			// $tab.="<td align=right>".number_format($bar['upah']/$bar['ttlkgbasis'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['upah'])."</td>";
			$tab.="<td align=right>".number_format($bar['kgpremi'])."</td>";
			$tab.="<td align=right>".number_format($bar['rpkgpremi'])."</td>";
			$tab.="<td align=right>".number_format($bar['premi'])."</td>";
			
			$tab.="<td align=right>".number_format($bar['upah']+$bar['premi'])."</td>";
			$tab.="<td align=right>".number_format(($bar['upah']+$bar['premi'])/$bar['kgsendiri'])."</td>";
			$tab.="<td align=right>".number_format($bar['upah']+$bar['premi']+$bar['rupiahborongan']+$bar['ttlrpsendiri']+$bar['rpalong'])."</td>";
			$tab.="<td align=right>".number_format(($bar['upah']+$bar['premi']+$bar['rupiahborongan']+$bar['ttlrpsendiri']+$bar['rpalong'])/$bar['hasilkerjakg'])."</td>";
			
		}
	
		echo $tab;
	break;
	case'simpanallangkut':
		$kegiatan ='611020201';
		$str = "delete from " . $dbname . ".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$tt."'"; //exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		$str = "insert into " . $dbname . ".kebun_rkbdt (
		`norkb`,
		 `tipetransaksi`,
		`periode`,
		`kodeorg`,
		`divisi`,
		`kodekegiatan`,
		`blok`,
		`hasilkerjakg`,
		`KBL`,
		`KHT`,
		`KHL`,
		`norma`,
		`upah`,
		`premi`,
		`jarakpks`,
		`persensendiri`,
		`kapasitas`,
		`trippks`,
		`km`,
		`kgsendiri`,
		`ttlrpsendiri`,
		`hasilkerjaborongan`,
		`hargaborongan`,
		`rupiahborongan`,
		`outputkgperhk`,
		`kgpremi`,
		`rpkgpremi`,
		`updateby`,
		`persenkontrak`,
		`tonkontrak`,
		`hargakontrak`,
		`rpkontrak`,
		`persenalong`,
		`tonalong`,
		`hargaalong`,
		`rpalong`,
		`persenmekanis`,
		`tonmekanis`,
		`hargamekanis`,
		`rpmekanis`
		)
		values ('".$notransaksi."',
		'".$tipetransaksi."',
		'".$periode."',
		'".$kodeorg."',
		'".$divisi."',
		'".$kegiatan."',
		'".$tt."',
		'".$kg."',
		'".$kbl."',
		'".$kht."',
		'".$khl."',
		'".$norma."',
		'".$upah."',
		'".$premi."',
		'".$jarakpks."',
		'".$persensendiri."',
		'".$kapasitas."',
		'".$trippks."',
		'".$km."',
		'".$kgsendiri."',
		'".$ttlrpsendiri."',
		'".$kgkont."',
		'".$hargaborongan."',
		'".$ttlrpkont."',
		'".$outputkgperhk."',
		'".$kgpremi."',
		'".$rpkgpremi."',
		'".$_SESSION['standard']['userid']."',
		'".$persenkontangkut."',
		'".$tonkontangkut."',
		'".$hargakontangkut."',
		'".$rpkontangkut."',
		'".$persenalong."',
		'".$tonalong."',
		'".$hargalong."',
		'".$rpalong."',
		'".$persenmekanis."',
		'".$tonmekanis."',
		'".$hargamekanis."',
		'".$rpmekanis."'
		)"; 
		//exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
	
	case'inputdetailpnn':
	$tab="";
	
	$tt=array();$kodeblok=array();$luas=array();$pokok=array();$topo=array();
	
	$dtakp=$dtbjr=$dtborongan=$dtbrondolankg=$dtgtbiaya=$dthasilkerja=$dthasilkerjakg=$dtKBL=$dtKHL=$dtKHT=$dtnorma=$dtpremi=$dtpremibrondol=$dtpremikrn=$dtpremilebihbasis1=$dtpremilebihbasis2=$dtpremimdr=$dtpremimdr1=$dtrotasi=$dtrpperkg=$dtsubttlpre=$dttotalupahpre=$dtttlpremimandor=$dtttlupahmandor=$dtupah=$dtupahkrn=$dtupahmdr=$dtupahmdr1=$ttlhkpnn=array();
	
	# ambil data blok
	$str = "select * from ".$dbname.".setup_blok where indukblok like '".$divisi."%' and statusblok='TM' and luasareaproduktif>0 order by indukblok asc"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$kodeblok[$bar['indukblok']]=$bar['indukblok'];
		// $tt[$bar['kodeorg']]=$bar['tahuntanam'];
		$luas[$bar['indukblok']]+=$bar['luasareaproduktif'];
		$pokok[$bar['indukblok']]+=$bar['jumlahpokok'];
		// $topo[$bar['kodeorg']]=$bar['topografi'];
	}
	
	
	# ambil data bjr
	$str = "select * from ".$dbname.".kebun_5bjr where kodeorg like '".$divisi."%' group by kodeorg order by kodeorg asc"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$bjrset[$bar['kodeorg']]=$bar['bjr'];
	}
	
	// echo"<pre>";
	// print_r($kodeblok);
	// echo"</pre>";
	
	#= edit jika data sudah ada
	$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' 
			and periode='".$periode."' and kodeorg='".$kodeorg."'  and divisi='".$divisi."'";		
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$dtrotasi[$bar['blok']]=$bar['rotasi'];
		$dtakp[$bar['blok']]=$bar['akp'];
		$dthasilkerja[$bar['blok']]=$bar['hasilkerja'];
		$dthasilkerjakg[$bar['blok']]=$bar['hasilkerjakg'];
		$dtbrondolankg[$bar['blok']]=$bar['brondolankg'];
		$dtKBL[$bar['blok']]=$bar['KBL'];
		$dtKHT[$bar['blok']]=$bar['KHT'];
		$dtKHL[$bar['blok']]=$bar['KHL'];
		$dtbjr[$bar['blok']]=$bar['bjr'];
		$dtnorma[$bar['blok']]=$bar['norma'];
		$dtupah[$bar['blok']]=$bar['upah'];
		$dtpremi[$bar['blok']]=$bar['premi'];
		$dtpremilebihbasis1[$bar['blok']]=$bar['premilebihbasis1'];
		$dtpremilebihbasis2[$bar['blok']]=$bar['premilebihbasis2'];
		$dtpremibrondol[$bar['blok']]=$bar['premibrondol'];
		$dtborongan[$bar['blok']]=$bar['rupiahborongan'];
		$ttlhkpnn[$bar['blok']]=$bar['ttlhkpnn'];
		
		$dtsubttlpre[$bar['blok']]=$bar['premilebihbasis1']+$bar['premilebihbasis2']+$bar['premibrondol'];
		$dttotalupahpre[$bar['blok']]=$dtsubttlpre[$bar['blok']]+$dtupah[$bar['blok']];
		
		
							
		$dtupahmdr[$bar['blok']]=$bar['upahmdr'];
		$dtpremimdr[$bar['blok']]=$bar['premimdr'];
		$dtupahkrn[$bar['blok']]=$bar['upahkrn'];
		$dtpremikrn[$bar['blok']]=$bar['premikrn'];
		$dtupahmdr1[$bar['blok']]=$bar['upahmdr1'];
		$dtpremimdr1[$bar['blok']]=$bar['premimdr1'];
		
		$dtttlupahmandor[$bar['blok']]=$dtupahmdr[$bar['blok']]+$dtupahkrn[$bar['blok']]+$dtupahmdr1[$bar['blok']];
		$dtttlpremimandor[$bar['blok']]=$dtpremimdr[$bar['blok']]+$dtpremikrn[$bar['blok']]+$dtpremimdr1[$bar['blok']];
		
		$dtgtbiaya[$bar['blok']]=$dtttlupahmandor[$bar['blok']]+$dtttlpremimandor[$bar['blok']]+$dttotalupahpre[$bar['blok']];
		$dtrpperkg[$bar['blok']]=$dtgtbiaya[$bar['blok']]/$dthasilkerjakg[$bar['blok']];
	
		$dtttlupahmdr=$bar['ttlupahmdr'];
		$dtpersenmdr=$bar['persenmdr'];
		$dtttlupahkrn=$bar['ttlupahkrn'];
		$dtpersenkrn=$bar['persenkrn'];
		$dtttlupahmdr1=$bar['ttlupahmdr1'];
		$dtjlhmdrmdr1=$bar['jlhmdrmdr1'];
		$dtpersenmdr1=$bar['persenmdr1'];
		$dtcopypremibrd=$bar['copypremibrd'];
		
		
		@$dttotaljjg+=$bar['hasilkerja'];
		@$dttotalkg+=$bar['hasilkerjakg'];
		
		@$dttotalhk+=$bar['ttlhkpnn'];
		@$dttotalupahpnn+=$bar['upah'];
		@$dttotalpremi1pnn+=$bar['premilebihbasis1'];
		@$dttotalpremi2pnn+=$bar['premilebihbasis2'];
		
								
	}
	
	
	
	$no='';
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center colspan=6></td>";
	$tab.="<td align=center>
		<!--- <input id=copyrot onkeyup=\"copy('rotasi');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"> --->
	</td>";
	
	$tab.="<td align=center>
		<!--- <input id=copyakp onkeyup=\"copy('akp');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"> ---->
	</td>";
	
	$tab.="<td align=center colspan=2></td>";
	$tab.="<td align=center>
		<!--- <input id=copyoutput onkeyup=\"copy('output');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"> ---->
	</td>";
	
	$tab.="<td>
		<!---- <input id=persenkbl onkeyup=\"proporsihk();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\" disabled value=0> ---->
	</td>";
	$tab.="<td>
		<!---- <input id=persenkht onkeyup=\"proporsihk();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\" disabled value=0> ---->
	</td>";
	$tab.="<td>
		<!---- <input id=persenkhl onkeyup=\"proporsihk();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\" value=0> ---->
	</td>";
	$tab.="<td align=center colspan=3></td>";
	$tab.="<td align=center>
		<!---- <input id=copypremibrd value=".number_format($dtcopypremibrd)." onkeyup=\"copy('premibrd');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\" title='Isikan Persen (%)'> ---->
	</td>";
	$tab.="<td align=center colspan=3></td>";
	
	
	$tab.="<td align=center><input title='Isikan Total Rupiah Upah Mandor' id=ttlupahmdr value=".number_format($dtttlupahmdr)."  onclick=info('mdr'); onkeyup=\"z.numberFormat('ttlupahmdr',2);copy('mdr');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
	
	$tab.="<td align=center><input title='Isikan Persen Premi Mandor' id=persenmdr value=".number_format($dtpersenmdr)." onclick=info('prsnmdr'); onkeyup=\"z.numberFormat('persenmdr',2);copy('prsnmdr');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
	
	$tab.="<td align=center><input title='Isikan Total Rupiah Upah Kerani' id=ttlupahkrn  value=".number_format($dtttlupahkrn)."  onkeyup=\"z.numberFormat('ttlupahkrn',2);copy('krn');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
	$tab.="<td align=center><input title='Isikan Persen Premi Kerani' id=persenkrn  value=".number_format($dtpersenkrn)."  onkeyup=\"z.numberFormat('persenkrn',2);copy('prsnkrn');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
	
	
	// $tab.="<td align=center><input title='Isikan Total Rupiah Upah Mandor 1' id=ttlupahmdr1   value=".number_format($dtttlupahmdr1)."   onkeyup=\"z.numberFormat('ttlupahmdr1',2);copy('mdr1');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
	
	// $tab.="<td align=center style=\"width:25px;\">
	// 	<input title='Isikan Jumlah Mandor' id=jlhmdrmdr1 value=".number_format($dtjlhmdrmdr1)."  onkeyup=\"z.numberFormat('jlhmdrmdr1',2);copy('jlhmdr1');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:25px;\">
	// 	</td>
	// 	<td style=\"width:30px;\"><input title='Isikan Persen Premi Mandor 1' id=persenmdr1 value=".number_format($dtpersenmdr1)." onkeyup=\"z.numberFormat('persenmdr1',2);copy('prsnmdr1');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\">
	// </td>";
	
	$tab.="<td align=center colspan=5></td>";
	
	
	
	
	$tab.="</tr>";
	foreach($kodeblok as $blok){
		$basis=makeOption($dbname,'kebun_5basispanen2','kodeorg,basis',"kodeorg='".$blok."' and tahun='".$periode."'");
		$premilbpersen=makeOption($dbname,'kebun_5basispanen2','kodeorg,premilbpersen',"kodeorg='".$blok."' and tahun='".$periode."'");
		$rppremilb1=makeOption($dbname,'kebun_5basispanen2','kodeorg,premilebihbasis',"kodeorg='".$blok."' and tahun='".$periode."'");
		$rppremilb2=makeOption($dbname,'kebun_5basispanen2','kodeorg,premilebihbasis2',"kodeorg='".$blok."' and tahun='".$periode."'");
		$rpbrdperkg=makeOption($dbname,'kebun_5basispanen2','kodeorg,premibrondolan',"kodeorg='".$blok."' and tahun='".$periode."'");
		
		$title=$warna='';
		if(@$rppremilb1[$blok]==''){
			$title="title='Harga Premi Panen Kode Blok ".$blok.", Periode ".$periode."  belum ada.'";
			$warna="style=background-color:red";
		}
		$no++;
		$tab.="<tr class=rowcontent id=rowpnn".$no." ".$title." ".$warna.">";
		#$tab.="<td align=center>".$basis[$tt[$blok]]."</td>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td hidden id=blokpnn".$no.">".$blok."</td>";
		$tab.="<td >".$nmorg[$blok]."</td>";
		$tab.="<td align=right>".number_format($luas[$blok],2)."</td>";
		$tab.="<td align=right>".number_format($pokok[$blok]/$luas[$blok])."</td>";
		$tab.="<td align=right>0</td>";
		$tab.="<td id=pkkpnn".$no." align=right>".$pokok[$blok]."</td>";
		
		$tab.="<td><input id=rotpnn".$no." value='".@$dtrotasi[$blok]."' onkeyup=\"getkalkulasipnn('".$no."')\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		$tab.="<td><input id=akppnn".$no." value='".@$dtakp[$blok]."' onkeyup=\"getkalkulasipnn('".$no."')\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		
		if($dtbjr[$blok]==''){
			$dtbjr[$blok]=$bjrset[$blok];
		}
			
		$tab.="<td><input id=bjrpnn".$no." onkeyup=\"getkalkulasipnn('".$no."')\"  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\" value=".number_format($dtbjr[$blok],2)."></td>";
		
		
		
		$tab.="<td id=jjgpnn".$no." align=right>".number_format($dthasilkerja[$blok])."</td>";
		$tab.="<td id=kgpnn".$no." align=right>".number_format($dthasilkerjakg[$blok])."</td>";
		
	
		if($dtnorma[$blok]==''){
			$dtnorma[$blok]=0;
		}
		$tab.="<td><input id=outputpnn".$no."   value=".$dtnorma[$blok]." onkeyup=\"getkalkulasipnn('".$no."')\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\" ></td>";
		
		$tab.="<td><input id=kblpnn".$no." value='".$dtKBL[$blok]."' onkeyup=\"getkalkulasihk('".$no."');proporsihkpanen();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		$tab.="<td><input id=khtpnn".$no." value='".$dtKHT[$blok]."' onkeyup=\"getkalkulasihk('".$no."');proporsihkpanen();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		$tab.="<td><input id=khlpnn".$no." value='".$dtKHL[$blok]."' onkeyup=\"getkalkulasihk('".$no."');proporsihkpanen();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		
		// $tab.="<td id=ttlhkpnn".$no." width=40px align=right>".number_format($ttlhkpnn[$blok])."</td>";
		#$tab.="<td id=ttlrphkpnn".$no." width=40px align=right>".number_format($dtupah[$blok])."</td>";
		$tab.="<td id=ttlrphkpnn".$no." width=40px align=right>0</td>";
		
		$tab.="<td id=bsspnn".$no." hidden width=40px align=right>".@$basis[$blok]."</td>";
		$tab.="<td id=persenbsspnn".$no." hidden width=40px align=right>".@$premilbpersen[$blok]."</td>";
		$tab.="<td id=rppremilb1pnn".$no." hidden width=40px align=right>".@$rppremilb1[$blok]."</td>";
		$tab.="<td id=rppremilb2pnn".$no." hidden width=40px align=right>".@$rppremilb2[$blok]."</td>";
		$tab.="<td id=rppremibrdpnn".$no." hidden width=40px align=right>".@$rpbrdperkg[$blok]."</td>";
		
		$tab.="<td  width=40px align=right>
			<input id=premi1pnn".$no." value='".$dtpremilebihbasis1[$blok]."' onkeyup=\"proporsihkpanen();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\">
		</td>";
		// $tab.="<td  width=40px  id=premi1pnn".$no." align=right>".number_format($dtpremilebihbasis1[$blok])."</td>";
		// $tab.="<td  width=40px id=premi2pnn".$no." align=right>".number_format($dtpremilebihbasis2[$blok])."</td>";
		// $tab.="<td width=40px id=premibrdpnn".$no."  align=right>".number_format($dtpremibrondol[$blok])."</td>";
		$tab.="<td width=40px align=right>
			<input id=premibrdpnn".$no." value='".$dtpremibrondol[$blok]."' onkeyup=\"getkalkulasipnn('".$no."');\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\">
		</td>";
		// $tab.="<td><input id=borpnn".$no." disabled value='".$dtborongan[$blok]."' onkeyup=\"getkalkulasipnn('".$no."')\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>";
		$tab.="<td><input id=kgbrd".$no." value='".$dtbrondolankg[$blok]."' nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		$tab.="<td id=subttlpre".$no." align=right>".number_format($dtsubttlpre[$blok])."</td>";
		$tab.="<td id=totalupahpre".$no." align=right>".number_format($dttotalupahpre[$blok])."</td>";
		
		
		$tab.="<td id=upahmdr".$no." align=right>".number_format($dtupahmdr[$blok])."</td>";
		$tab.="<td id=premimdr".$no." align=right>".number_format($dtpremimdr[$blok])."</td>";
		$tab.="<td id=upahkrn".$no." align=right>".number_format($dtupahkrn[$blok])."</td>";
		$tab.="<td id=premikrn".$no." align=right>".number_format($dtpremikrn[$blok])."</td>";
		// $tab.="<td id=upahmdrsatu".$no." align=right>".number_format($dtupahmdr1[$blok])."</td>";
		// $tab.="<td colspan=2 id=premimdrsatu".$no." align=right>".number_format($dtpremimdr1[$blok])."</td>";
		
		$tab.="<td id=ttlupahmandor".$no." align=right>".number_format($dtttlupahmandor[$blok])."</td>";
		$tab.="<td id=ttlpremimandor".$no." align=right>".number_format($dtttlpremimandor[$blok])."</td>";
		$tab.="<td id=gtbiaya".$no." align=right>".number_format($dtgtbiaya[$blok])."</td>";
		$tab.="<td id=rpperkg".$no." align=right>".number_format($dtrpperkg[$blok],2)."</td>";
		
		
	}
	$tab.="<input hidden id=jlhbrs value=".$no.">";
	$tab.="</tr>";	


	
	$tab.="<tr class=rowcontent hidden>";
	$tab.="<td align=center colspan=2>TTL</td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td id=totaljjg align=right>".number_format($dttotaljjg)."</td>";
	$tab.="<td id=totalkg align=right>".number_format($dttotalkg)."</td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	
	
	
	$tab.="<td id=totalhk align=right>".number_format($dttotalhk)."</td>";
	$tab.="<td id=totalupahpnn align=right>".number_format($dttotalupahpnn)."</td>";
	$tab.="<td id=totalpremi1pnn align=right>".number_format($dttotalpremi1pnn)."</td>";
	$tab.="<td id=totalpremi2pnn align=right>".number_format($dttotalpremi2pnn)."</td>";
	$tab.="<td></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="<td ></td>";
	$tab.="</tr>";		
	
	$tab.="</tr>";		
	$tab.="<tr>";
	$tab.="<td align=center colspan=33><button class=mybutton onclick=simpanallpnn('".$no."')>" . $_SESSION['lang']['save'] . "</button></td>";
	$tab.="</tr>";		
	echo $tab;
	break;
	
	case'loaddatadetailpnn':
	$tab="";
	$tab.="
			<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
			<thead><tr class=rowheader>";
			
			$rows="rowspan=4";	
			$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['luas'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['sph'] . "</td>
				<td align=center ".$rows.">TT</td>
				<td align=center ".$rows.">".$_SESSION['lang']['pokok'] . "</td>
				<td align=center ".$rows." style=width:30px>".$_SESSION['lang']['rotasi'] . "</td>
				<td align=center ".$rows." style=width:30px>AKP<br>%</td>
				<td align=center ".$rows." style=width:30px>BJR</td>
				<td align=center ".$rows.">Jjg</td>
				<td align=center ".$rows.">Kg</td>
				<td align=center ".$rows." style=width:30px>Output<br>(Kg)</td>
				
				<td align=center colspan=16>".$_SESSION['lang']['biaya'] . "</td>
				<td align=center ".$rows.">Rp/Kg</td>
			</tr>
			<tr>
				<td align=center colspan=9>Pemanen</td>
				<td align=center colspan=6>Supervisi</td>
				<td align=center rowspan=3>Total Biaya</td>
			</tr>
			<tr>
				<td align=center colspan=3>HK</td>
				<td align=center rowspan=2>Upah</td>
				<td align=center colspan=4>Premi</td>
				<td align=center rowspan=2>Total</td>
				<td align=center style=width:70px colspan=2>Mandor Panen</td>
				<td align=center style=width:70px colspan=2>Kerani Panen</td>
				<!--- <td align=center colspan=3>Mandor 1</td> --->
				<td align=center colspan=2>Total</td>
			</tr>
			<tr>
				<td align=center style=width:30px>KBL</td>
				<td align=center style=width:30px>KHT</td>
				<td align=center style=width:30px>KHL</td>
				<!--- <td align=center>Sub TTL</td> --->
				<td align=center>1</td>
				<!--- <td align=center>2</td> --->
				<td align=center>Kutib Brd</td>
				<td align=center width=30px>Kg Brd</td> 
				<!--- <td align=center width=50px>Borongan</td> ---->  
				<td align=center>Sub TTL</td>
				<td align=center style=width:30px>Upah</td>
				<td align=center style=width:30px>Premi</td>
				<td align=center style=width:30px>Upah</td>
				<td align=center style=width:30px>Premi</td>
				<!--- <td align=center style=width:30px>Upah</td> ---->
				<!--- <td align=center colspan=2>Premi</td> ---->
				<td align=center>Upah</td>
				<td align=center>Premi</td>
			</tr>
			</thead>";
		$where='';
		if($divisi!==''){
			$where=" and divisi='".$divisi."'";
		}

		// $str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."'";
		$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' 
		and periode='".$periode."' and kodeorg='".$kodeorg."' ".$where."";
		$jlh = count(fetchdata($str));
		if($jlh>0){
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$no='';
			while ($bar = $res->fetch()) {
				$optluas=makeOption($dbname,'setup_blok','indukblok,luasareaproduktif',"indukblok='".$bar['blok']."'");
				$optpokok=makeOption($dbname,'setup_blok','indukblok,jumlahpokok',"indukblok='".$bar['blok']."'");
				// $opttt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['blok']."'");
				
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td >".$nmorg[$bar['blok']]."</td>";
				$tab.="<td align=right>".number_format($optluas[$bar['blok']],2)."</td>";
				$tab.="<td align=right>".number_format($optpokok[$bar['blok']]/$optluas[$bar['blok']],2)."</td>";
				$tab.="<td align=right>0</td>";
				$tab.="<td align=right>".number_format($optpokok[$bar['blok']])."</td>";
				$tab.="<td  align=right>".$bar['rotasi']."</td>";
				$tab.="<td  align=right>".$bar['akp']."</td>";
				$tab.="<td  align=right>".number_format($bar['bjr'],2)."</td>";
				$tab.="<td  align=right>".number_format($bar['hasilkerja'])."</td>";
				$tab.="<td  align=right>".number_format($bar['hasilkerjakg'])."</td>";
				$tab.="<td  align=right>".number_format($bar['norma'])."</td>";
				$tab.="<td  align=right>".number_format($bar['KBL'])."</td>";
				$tab.="<td  align=right>".number_format($bar['KHT'])."</td>";
				$tab.="<td  align=right>".number_format($bar['KHL'])."</td>";
				// $tab.="<td  align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'])."</td>";
				$tab.="<td  align=right>".number_format($bar['upah'])."</td>";
				$tab.="<td  align=right>".number_format($bar['premilebihbasis1'])."</td>";
				// $tab.="<td  align=right>".number_format($bar['premilebihbasis2'])."</td>";
				$tab.="<td  align=right>".number_format($bar['premibrondol'])."</td>";
				$tab.="<td  align=right>".number_format($bar['brondolankg'])."</td>";
				@$tpremi=$bar['premilebihbasis1']+$bar['premilebihbasis2']+$bar['premibrondol'];
				$tab.="<td  align=right>".number_format($tpremi)."</td>";
				$tupahpremi=$tpremi+$bar['upah'];
				$tab.="<td  align=right>".number_format($tupahpremi)."</td>";
				$tab.="<td  align=right>".number_format($bar['upahmdr'])."</td>";
				$tab.="<td  align=right>".number_format($bar['premimdr'])."</td>";
				$tab.="<td  align=right>".number_format($bar['upahkrn'])."</td>";
				$tab.="<td  align=right>".number_format($bar['premikrn'])."</td>";
				// $tab.="<td  align=right>".number_format($bar['upahmdr1'])."</td>";
				// $tab.="<td  align=right>".number_format($bar['premimdr1'])."</td>";
				$tupahpejabat=$bar['upahmdr']+$bar['upahkrn'];
				$tab.="<td  align=right>".number_format($tupahpejabat)."</td>";
				$tpremipejabat=$bar['premimdr']+$bar['premikrn'];
				$tab.="<td  align=right>".number_format($tpremipejabat)."</td>";
				$gttl=$tupahpremi+$tupahpejabat+$tpremipejabat;
				$tab.="<td  align=right>".number_format($gttl)."</td>";
				if($gttl>0){
					$tab.="<td  align=right>".number_format($gttl/$bar['hasilkerjakg'],2)."</td>";
				}else{
					$tab.="<td  align=right></td>";
				}
				
				
				@$t_luas+=$optluas[$bar['blok']];
				@$t_pokok+=$optpokok[$bar['blok']];
				@$t_hasilkerja+=$bar['hasilkerja'];
				@$t_hasilkerjakg+=$bar['hasilkerjakg'];
				@$t_KBL+=$bar['KBL'];
				@$t_KHT+=$bar['KHT'];
				@$t_KHL+=$bar['KHL'];
				@$t_upah+=$bar['upah'];
				@$t_premilebihbasis1+=$bar['premilebihbasis1'];
				@$t_premilebihbasis2+=$bar['premilebihbasis2'];
				@$t_premibrondol+=$bar['premibrondol'];
				@$t_brondolankg+=$bar['brondolankg'];
				@$t_upahmdr+=$bar['upahmdr'];
				@$t_premimdr+=$bar['premimdr'];
				@$t_upahkrn+=$bar['upahkrn'];
				@$t_premikrn+=$bar['premikrn'];
				@$t_upahmdr1+=$bar['upahmdr1'];
				@$t_premimdr1+=$bar['premimdr1'];
				
			}

			$tab.="</tr>";
			$tab.="<tr class=rowcontent style=background-color:cyan align=center>";
			$tab.="<td align=center colspan=2>T O T A L</td>";
			$tab.="<td align=right>".number_format($t_luas,2)."</td>";
			$tab.="<td align=right>".number_format($t_pokok/$t_luas,2)."</td>";
			$tab.="<td align=right></td>";
			$tab.="<td align=right>".number_format($t_pokok)."</td>";
			$tab.="<td  align=right></td>";
			$tab.="<td  align=right></td>";
			$tab.="<td  align=right>".number_format($t_bjr,2)."</td>";
			$tab.="<td  align=right>".number_format($t_hasilkerja)."</td>";
			$tab.="<td  align=right>".number_format($t_hasilkerjakg)."</td>";
			$tab.="<td  align=right></td>";
			$tab.="<td  align=right>".number_format($t_KBL)."</td>";
			$tab.="<td  align=right>".number_format($t_KHT)."</td>";
			$tab.="<td  align=right>".number_format($t_KHL)."</td>";
			// $tab.="<td  align=right>".number_format($t_KBL+$t_KHT+$t_KHL)."</td>";
			$tab.="<td  align=right>".number_format($t_upah)."</td>";
			$tab.="<td  align=right>".number_format($t_premilebihbasis1)."</td>";
			// $tab.="<td  align=right>".number_format($t_premilebihbasis2)."</td>";
			$tab.="<td  align=right>".number_format($t_premibrondol)."</td>";
			$tab.="<td  align=right>".number_format($t_brondolankg)."</td>";
			@$t_tpremi=$t_premilebihbasis1+$t_premibrondol;
			$tab.="<td  align=right>".number_format($t_tpremi)."</td>";
			$t_tupahpremi=$t_tpremi+$t_upah;
			$tab.="<td  align=right>".number_format($t_tupahpremi)."</td>";
			$tab.="<td  align=right>".number_format($t_upahmdr)."</td>";
			$tab.="<td  align=right>".number_format($t_premimdr)."</td>";
			$tab.="<td  align=right>".number_format($t_upahkrn)."</td>";
			$tab.="<td  align=right>".number_format($t_premikrn)."</td>";
			// $tab.="<td  align=right>".number_format($t_upahmdr1)."</td>";
			// $tab.="<td  align=right>".number_format($t_premimdr1)."</td>";
			$t_tupahpejabat=$t_upahmdr+$t_upahkrn;
			$tab.="<td  align=right>".number_format($t_tupahpejabat)."</td>";
			$t_tpremipejabat=$t_premimdr+$t_premikrn;
			$tab.="<td  align=right>".number_format($t_tpremipejabat)."</td>";
			$t_gttl=$t_tupahpremi+$t_tupahpejabat+$t_tpremipejabat;
			$tab.="<td  align=right>".number_format($t_gttl)."</td>";
			if($t_gttl>0){
				$tab.="<td  align=right>".number_format($t_gttl/$t_hasilkerjakg,2)."</td>";
			}else{
				$tab.="<td  align=right></td>";
			}
		}
		
		echo $tab;	
	break;
	case'simpanallpnn':
		$kodeJurnal = 'PNN02';
		$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='".$kodeJurnal."'");
		$resParam = fetchData($queryParam);
		$kegiatan =$resParam[0]['noakundebet'];

		$total = $upah+$premi1+$premi2+$brondol+$upahmdr+$premimdr+$upahkrn+$premikrn+$upahmdrsatu+$premimdrsatu;
		// exit("Warning: ".$total);
		if($total>0){			
			$str = "delete from " . $dbname . ".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		$tt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blok."'");
		$str = "insert into " . $dbname . ".kebun_rkbdt (
			`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,
			`kodekegiatan`,`blok`,`tahuntanam`,`rotasi`,`akp`,
			`hasilkerja`,`hasilkerjakg`,`bjr`,`KBL`,`KHT`,
			`KHL`,`norma`,`upah`,`premilebihbasis1`,
			`premibrondol`,`upahmdr`,`premimdr`,`upahkrn`,`premikrn`,
			`updateby`,`ttlhkpnn`,`ttlupahmdr`,`persenmdr`,
			`ttlupahkrn`,`persenkrn`,`brondolankg`)
		values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."',
		'".$kegiatan."','".$blok."','0','".$rotasi."','".$akp."',
		'".$jjg."','".$kg."','".$bjr."','".$kbl."','".$kht."',
		'".$khl."','".$output."','".$upah."','".$premi1."',
		'".$brondol."','".$upahmdr."','".$premimdr."','".$upahkrn."','".$premikrn."',
		'".$_SESSION['standard']['userid']."','".$ttlhkpnn."','".$ttlupahmdr."','".$persenmdr."',
		'".$ttlupahkrn."','".$persenkrn."','".$kgbrd."'
		)";
		
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal Simpan Panen !: " . $e->getMessage() . "\n";die();}
		
	break;
	case'getluas':
		// Get Pilihan Luas dan kelompok dari setup kegiatan
		$strkeg = "select pilihanluas,kelompok from ".$dbname.".setup_kegiatan where 1=1  and kodekegiatan='".$kegiatan."'";
		$reskeg=$owlPDO->query($strkeg) or die(print " Gagal: ".PDOException::getMessage());
		$reskeg->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$reskeg->fetch()){
			$pilluas=$bar['pilihanluas'];
			$statusblok=$bar['kelompok'];
			if ($statusblok == "PNN") {
				$statusblok="TM";
			} else {
				$statusblok=$bar['kelompok'];
			}
		}

		$str = "select * from ".$dbname.".setup_blok where 1=1 and indukblok='".$blok."' and statusblok='".$statusblok."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if ($pilluas == 0) {
				$luas=($bar['luasareaproduktif'] + $bar['luasareanonproduktif']);
			} elseif ($pilluas == 1) {
				$luas=($bar['luasbloking'] - $bar['lc']);
			} elseif ($pilluas == 2) {
				$luas=$bar['lc'];
			} else {
				$luas=$bar['luasareaproduktif'];
			}
		}
		echo $luas;
	break;
	case'getblok':
	#===================== Kode Blok ==========================
	if($divisi!=''){
		// @$whereBlok=" and substr(a.kodeorganisasi,1,6) = '".$divisi."'";
		@$whereBlok=" and substr(a.indukblok,1,6) = '".$divisi."'";
	}else{
		// @$whereBlok.= " and substr(a.kodeorganisasi,1,4) ='".$_SESSION['empl']['lokasitugas']."'";
		@$whereBlok.= " and substr(a.indukblok,1,4) ='".$_SESSION['empl']['lokasitugas']."'";
	}
	$optkel = makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok',"kodekegiatan='".$kegiatan."'");
	if($optkel[$kegiatan]=='PNN'){
		$kelompok = 'TM';
	}else{
		$kelompok = $optkel[$kegiatan];
	}
	
	@$whereBlok.=" and b.statusblok ='".$kelompok."'";
	
	$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	// $str = "select * from ".$dbname.".organisasi a 
	// 		left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
	// 		where a.tipe in('BLOK','BIBITAN') ".$whereBlok." and luasareaproduktif>0 order by a.kodeorganisasi asc"; 
	$str = "select a.indukblok, a.namaindukblok, b.statusblok from ".$dbname.".organisasi a 
			left join ".$dbname.".setup_blok b on a.indukblok = b.indukblok
			where a.tipe in('BLOK','BIBITAN') ".$whereBlok." and luasareaproduktif>0 
			group by a.indukblok
			order by a.indukblok asc"; 
	//exit("error".$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		// $optBlok.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
		$optBlok.="<option value=".$bar['indukblok'].">".$bar['namaindukblok']." - ".$bar['statusblok']."</option>";
	}
	#===================== Kode Blok ==========================
	$optmat="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".log_5masterbarang where inactive='0' and kodebarang in (select kodebarang from ".$dbname.".setup_kegiatannorma where kodekegiatan = '".$kegiatan."') order by namabarang asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optmat.="<option value=".$bar['kodebarang'].">".$bar['kodebarang']." - ".$bar['namabarang']."</option>";
	}
	
	echo $optBlok."###".$optmat;
	break;
	case'inputdetail':
	echo"<tr class=rowcontent>";
	echo"<td rowspan=2 valign=top id=no align=center>#</td>
			<td rowspan=2 valign=top><select class=select2 style=width:200px onchange=getblokandbarang() id=kegiatan>".$optKeg."</select>
			</td>
			
			<td rowspan=2 valign=top><select class=select2 style=width:120px onchange=getluas() id=blok></select></td>
			<td rowspan=2 valign=top><input id=luas class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
			<td rowspan=2 valign=top><input id=output class=myinputtextnumber disabled style=\"width:50px;\"></td>
			<td rowspan=2 valign=top><input id=pusingan nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top><input id=kbl onkeyup=\"z.numberFormat('kbl',2);gettotalhk();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top><input id=kht onkeyup=\"z.numberFormat('kht',2);gettotalhk();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top><input id=khl onkeyup=\"z.numberFormat('khl',2);gettotalhk();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			
			<td rowspan=2 valign=top><input id=jhk  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top><input id=jrphk  nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
			<td rowspan=2 valign=top><input id=premi class=myinputtextnumber style=\"width:50px;\"></td>
			<td rowspan=2 valign=top><input id=luasbor onkeyup=\"z.numberFormat('luasbor',2);\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top><input id=rpperhabor onkeyup=\"z.numberFormat('rpperhabor',2);gettotalbor();\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td rowspan=2 valign=top><input id=rupiahbor disabled class=myinputtextnumber style=\"width:60px;\"></td>
			
			<td valign=top><select class=select2 style=width:110px id=kodebarang onchange=getsatmat()></select></td>
			<td valign=top><input id=satmat disabled class=myinputtextnumber style=\"width:30px;\"></td>
			<td valign=top><input id=dosismat onkeyup=getrupiahmat('dosis') class=myinputtextnumber style=\"width:40px;\"></td>
			<td valign=top><input id=jlhmat onkeyup=getrupiahmat('jumlah') class=myinputtextnumber style=\"width:45px;\"></td>
			<td valign=top><input id=rpmat class=myinputtextnumber style=\"width:50px;\">
						   <input id=stok hidden></td>
			<td valign=top><img class='resicon' title='Tambah Material' id=tombolsimpanmaterial src='images/plus.png' onclick=simpandetail()>
							<input id=hargarata hidden>			
			</td>
			
			<td align=center rowspan=2 valign=top width=30px><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"simpandetail('clear')\" src='images/save.png'/>
			</td>
			<td align=center rowspan=2 valign=top  width=30px>	
				<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetailall()\" src='images/clear.png'/>
			</td>
			
			</tr><tr class=rowcontent>
			<td colspan=6>
				<div id=listmaterial></div>
			</td>
			</tr><tr>
			<td id=pfot colspan=20></td>
			<td  align=center>
				<input id=jlhbrs style=display:none>
				<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetail()\" src='images/refresh2.png'/>
			</td>
			<td  align=center>
				<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
			</td>
        </tr>";
	break;
	case'getsatmat':
		$str = "select * from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$satuan=$bar['satuan'];
		
		$str = "select * from ".$dbname.".log_5saldobulanan where kodebarang='".$kodebarang."' and kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."') and hargarata!=0 order by periode desc limit 1"; //exit("error".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$hargarata=$bar['hargarata'];
		
		$where='';
		if($divisi!==''){
			$where=" and b.afdeling='".$divisi."'";
		}
		# Stok gudang
		$str = "select * from ".$dbname.".log_5saldobulanan a left join ".$dbname.".kebun_5gudangtransaksi b on a.kodegudang=b.kodegudang where a.kodebarang='".$kodebarang."' and a.periode='".periodelalu($periode)."' and a.kodegudang like '".$kodeorg."%' ".$where.""; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$stok+=$bar['saldoakhirqty'];
		}
		
		# Transaksi belum posting
		$str = "select * from ".$dbname.".log_transaksi_vw a left join ".$dbname.".kebun_5gudangtransaksi b on a.kodegudang=b.kodegudang where a.kodebarang='".$kodebarang."' and a.kodegudang like '".$kodeorg."%' ".$where." and a.statusjurnal='0' and a.post='0' and substr(a.tanggal,1,7)<='".$periode."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tipetransaksi']=='1' or $bar['tipetransaksi']=='2' or $bar['tipetransaksi']=='3'){
				@$masuk+=$bar['jumlah'];
			}
			if($bar['tipetransaksi']=='5' or $bar['tipetransaksi']=='6' or $bar['tipetransaksi']=='7'){
				@$keluar+=$bar['jumlah'];
			}
		}
		
		$saldo=$stok+$masuk-$keluar;
		
		echo $satuan."####".$hargarata."####".$saldo;
	break;
	case'simpandetail':
		$validasidt = ($notransaksi!='' and $tipetransaksi!='' and $periode!='' and $kodeorg!='' and $divisi!='' and $kegiatan!='' and $blok!='' and ($luas!='' or $luasbor!='') and ($upah!='' or $premi!='' or $rupiahbor!=''));
		
		if($validasidt){
			$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."'";
			$res = fetchData($str);
			if(count($res)==0){
				# Simpan detail
				$tt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blok."'");
				
				$str = "insert into " . $dbname . ".kebun_rkbdt (`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,`kodekegiatan`,`blok`,`tahuntanam`,`hasilkerja`,`pusingan`,`KBL`,`KHT`,`KHL`,`norma`,`upah`,`premi`,`hasilkerjaborongan`,`hargaborongan`,`rupiahborongan`,`updateby`)
				values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."','".$kegiatan."','".$blok."','".$tt[$blok]."','".$luas."','".$pusingan."','".$kbl."','".$kht."','".$khl."','".$output."','".$upah."','".$premi."','".$luasbor."','".$rpperhabor."','".$rupiahbor."','".$_SESSION['standard']['userid']."')";
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			}	
		}
		
		$validasi = ($notransaksi!='' and $tipetransaksi!='' and $periode!='' and $kodeorg!='' and $divisi!='' and $kegiatan!='' and $blok!='' and $kodebarang!='' and $luas!='' and $jlhmat!='' and ($rpmat!='' or $rpmat!='0'));
		if($validasi){
			$str = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."' and kodebarang='".$kodebarang."'";
			$res = fetchData($str);
			if(count($res)>0){
				exit("Warning : Data Sudah Ada.");
			}
			# Simpan material
			$str = "insert into " . $dbname . ".kebun_rkbmaterial (`norkb`, `tipetransaksi`,`periode`,`kodeorg`,`divisi`,`kodekegiatan`,`blok`, `kodebarang`, `luas`, `kwantitas`, `hargasatuan`, `jumlahrp`,`saldo`)
			values ('".$notransaksi."','".$tipetransaksi."','".$periode."','".$kodeorg."','".$divisi."','".$kegiatan."','".$blok."','".$kodebarang."','".$luas."','".$jlhmat."','".$hargarata."','".$rpmat."','".$stok."')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}			
		}
	break;	
	case'daftarmaterial':
		$tab="";
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		$tab.="<tbody>";
				
		$str = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$bar['kodebarang']."'");
			$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$tab.="<tr class=rowcontent>";
			if(strlen($optnmbrg[$bar['kodebarang']])>15){
				$namabarang="".substr(ucfirst(strtolower($optnmbrg[$bar['kodebarang']])),0,15)."...";
			}else{
				$namabarang="".ucfirst(strtolower($optnmbrg[$bar['kodebarang']]))."";
			}
			$tab.="<td width=95px>".$namabarang."</td>";
			$tab.="<td width=30px>".ucfirst(strtolower($nmsat[$bar['kodebarang']]))."</td>";
			$tab.="<td width=40px align=right>".number_format($bar['kwantitas']/$bar['luas'],2)."</td>";
			$tab.="<td width=40px align=right>".number_format($bar['kwantitas'],2)."</td>";
			$tab.="<td width=50px align=right>".number_format($bar['jumlahrp'])."</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody>";
		$tab.="</table>";
		echo $tab;
	break;
	case'loaddatadetail':
	
	$tab="<table border=0 cellpadding=2 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
			$rows="rowspan=2";	
			$tab.="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['pekerjaan']."</th>
				<th align=center ".$rows.">".$_SESSION['lang']['blok']."</th>
				<th align=center ".$rows.">".$_SESSION['lang']['luas'] . "</th>
				<th align=center ".$rows.">Output</th>
				<th align=center ".$rows.">Pusingan</th>
				<th align=center colspan=5>Tenaga Kerja</th>
				<th align=center ".$rows.">Premi</th>
				<th align=center colspan=3 width=50px>Borongan</th>
				<th align=center colspan=6 >".$_SESSION['lang']['material']."</th>
				<th align=center ".$rows." width=30px>Total<br>Rupiah</th>
				<th align=center width=30px ".$rows.">" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr>
				<th align=center>KBL</th>
				<th align=center>KHT</th>
				<th align=center>KHL</th>
				<th align=center>".$_SESSION['lang']['jumlah'] . "</th>
				<th align=center>".$_SESSION['lang']['rupiah'] . "</th>
				<th align=center>Luas</th>
				<th align=center>Rp/Ha</th>
				<th align=center>Rupiah</th>
				<th align=center width=20px>No</th>
				<th align=center width=85px>".$_SESSION['lang']['nama']."</th>
				<th align=center width=30px>Sat</th>
				<th align=center width=40px>Dosis</th>
				<th align=center width=40px>Jumlah</th>
				<th align=center width=60px>Rupiah</th>
			</tr>
			</thead>";
		$where='';
		if($divisi!==''){
			$where=" and divisi='".$divisi."'";
		}
        $str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' 
		and periode='".$periode."' and kodeorg='".$kodeorg."' ".$where."";
		// and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td valign=top align=center>" . $no . "</td>";
			$tab.="<td valign=top>".$nmkeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td valign=top>".$nmorg[$bar['blok']]."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['hasilkerja'],2)."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['norma'],2)."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['pusingan'],2)."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['KBL'],2)."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['KHT'],2)."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['KHL'],2)."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'],2)."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['upah'])."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['premi'])."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['hasilkerjaborongan'],2)."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['hargaborongan'])."</td>";
			$tab.="<td valign=top align=right>".number_format($bar['rupiahborongan'])."</td>";
			$tab.="<td valign=top align=right colspan=6>";
				$strx = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' 
				and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$bar['divisi']."' 
				and kodekegiatan='".$bar['kodekegiatan']."' and blok='".$bar['blok']."'"; 
				$jlh = fetchData($strx);
				$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$nox='';$ttlrpbahan='';
				if(count($jlh)>0){
					$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
					$tab.="<tbody>";
					while($barx=$resx->fetch()){
						$nox++;
						$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$barx['kodebarang']."'");
						$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$barx['kodebarang']."'");
						$tab.="<tr class=rowcontent>";
						$tab.="<td width=20px align=center>".$nox."</td>";
						$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,16)."";
						$tab.="<td width=95px>".$namabarang."</td>";
						$tab.="<td width=30px>".$nmsat[$barx['kodebarang']]."</td>";
						if($barx['kwantitas']!=0){
							$tab.="<td width=40px align=right>".number_format($barx['kwantitas']/$barx['luas'],2)."</td>";
						}else{
							$tab.="<td width=40px align=right></td>";
						}
						$tab.="<td width=40px align=right>".number_format($barx['kwantitas'],2)."</td>";
						$tab.="<td width=60px align=right>".number_format($barx['jumlahrp'])."</td>";
						$ttlrpbahan+=$barx['jumlahrp'];
					}
					$tab.="</tr>";
					$tab.="</tbody>";
					$tab.="</table>";					
				}
				
			$tab.="</td>";
			$totalrp=$bar['upah']+$bar['premi']+$bar['rupiahborongan']+$ttlrpbahan;
			$tab.="<td valign=top align=right>".number_format($totalrp)."</td>";
			$tab.="<td align=center valign=top>";
			$tab.="
				<img src=images/application/application_delete.png class=resicon  title='Delete' 
				onclick=\"deletedetail('" . $bar['norkb'] . "','" . $bar['tipetransaksi'] . "','" . $bar['periode'] . "','".$bar['kodeorg']."','".$bar['divisi']."','".$bar['kodekegiatan']."','".$bar['blok']."');\" >
				</td>";
			
			@$ttlluas+=$bar['hasilkerja'];
			@$ttlpusingan+=$bar['pusingan'];
			@$ttlkbl+=$bar['KBL'];
			@$ttlkht+=$bar['KHT'];
			@$ttlkhl+=$bar['KHL'];
			@$ttlupah+=$bar['upah'];
			@$ttlpremi+=$bar['premi'];
			@$ttlluasbor+=$bar['hasilkerjaborongan'];
			@$ttlrpbor+=$bar['rupiahborongan'];
			@$ttlbahan+=$ttlrpbahan;
			@$gtrp+=$totalrp;
			
		}		
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=3 bgcolor=cyan align=center><b>TOTAL</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlluas,2)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format(($ttlkbl+$ttlkht+$ttlkhl)/$ttlluas,2)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlpusingan,2)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl,2)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlkht,2)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlkhl,2)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl+$ttlkht+$ttlkhl,2)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlupah)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlpremi)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlluasbor,2)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlrpbor/$ttlluasbor)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlrpbor)."</b></td>";
		$tab.="<td bgcolor=cyan align=right colspan=5></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($ttlbahan)."</b></td>";
		$tab.="<td bgcolor=cyan align=right><b>".@number_format($gtrp)."</b></td>";
		$tab.="<td bgcolor=cyan align=right></td>";
        $tab.="</tr>";
        $tab.="</table>";


        echo $tab;
	break;
	case'deletedetail':
		$str = "delete from " . $dbname . ".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		$str = "delete from " . $dbname . ".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='".$tipetransaksi."' and periode='".$periode."' and kodeorg='".$kodeorg."' and divisi='".$divisi."' and kodekegiatan='".$kegiatan."' and blok='".$blok."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
    break;
	
	
    case'deletehead':
        $str = "delete from " . $dbname . ".kebun_rkbht where norkb='".$notransaksi."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;

     case'loaddata':
		#validasi
        $where="";
		
		if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){			
			$where.= " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
		if ($notransaksisch != '') {
            $where.=" and norkb like '%" . $notransaksisch . "%' ";
        }
		if ($postingsrc != '') {
            $where.=" and posting ='" . $postingsrc . "' ";
        }
		if ($periodesch != '') {
            $where.=" and periode like '" . $periodesch . "%' ";
        }
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
		$tab = "";
        $no = $maxdisplay;
		
		#= where khusus
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_rkbht where 1=1 " . $where . "";
		#exit('error'.$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
		$optorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
        $str = "select * from " . $dbname . ".kebun_rkbht where 1=1 " . $where . " 
				order by norkb desc limit " . $offset . "," . $limit . ""; #exit('error'.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) { 
            $isi = '';
            $no+=1;
			$a=$xx='';
			$a=$no%2;
			if($a==0){
				//$xx.=" style=background-color:#F5EEF8";
			}
            $tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['norkb'] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . " - ".$optorg[$bar['kodeorg']]."</td>";
            $tab.="<td align=center>" . $bar['periode'] . "</td>";
            $tab.="<td align=center>" . $nmkar[$bar['updateby']] . "</td>";
            
			#persetujuan
			$warna='';
			if($bar['statuspersetujuan']=='3'){
				$warna=" style=background-color:red";
			}
			
			# approval		
			$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
			
			$strX = "select * from ".$dbname.".approval where notransaksi='".$bar['norkb']."' and jenispersetujuan='RKB' order by level desc limit 1";
			$resX = $owlPDO->query($strX) or die(print " Gagal: " . PDOException::getMessage());
			$resX->setFetchMode(PDO::FETCH_ASSOC);
			$barX = $resX->fetch();
			if($barX['tanggal']==''|| $barX['tanggal']=='0000-00-00 00:00:00'){
				$tngl='';
			}else{
				$tngl=tanggalnormal($barX['tanggal']);
			}
			$optnmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$barX['karyawanid']."'");
			$tab.="<td ".$warna.">".@$optnmkary[$barX['karyawanid']]."
					<br>".@$arrHsl[$barX['status']]." ".$tngl."
					<br>".$barX['komentar']."
					</td>";
			# end approval
			
			if($bar['posting']==0 or $bar['statuspersetujuan']=='3'){
				$isi.="<td align=center><img src=images/skyblue/edit.png class=resicon class=zImgBtn height='30'  title='edit Data Detail' onclick=\"edithead('".$bar['norkb']."','".$bar['kodeorg']."','".$bar['periode']."','".$bar['statuspersetujuan']."');\" ></td>";			
				$isi.="<td align=center><img src=images/skyblue/delete.png class=resicon class=zImgBtn height='30'  title='Delete' onclick=\"deletehead('".$bar['norkb']."');\" ></td>";
				$isi.="<td align=center><img src=images/skyblue/submit.jpg class=resicon class=zImgBtn height='30'  title='Ajukan' onclick=\"form_ajukan('".$bar['norkb']."','".$bar['kodeorg']."','".$no."');\" ></td>";			
			}elseif($bar['statuspersetujuan']=='1' and $admin=='1'){
				$isi.="<td align=center><img src=images/skyblue/edit.png class=resicon class=zImgBtn height='30'  title='edit Data Detail' onclick=\"edithead('".$bar['norkb']."','".$bar['kodeorg']."','".$bar['periode']."','".$bar['statuspersetujuan']."');\" ></td>";
				$isi.="<td align=center></td>";
				$isi.="<td align=center></td>";
			}else{
				$isi.="<td align=center></td>";
				$isi.="<td align=center></td>";
				$isi.="<td align=center></td>";
			
			}
			#$isi.="<td align=center><img src=images/skyblue/pdf.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['norkb']."','".$bar['divisi']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','pdf');\" ></td>";
			$isi.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['norkb']."','".$bar['divisi']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','html');\" ></td>";
			
			$isi.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailDataRekap('".$bar['norkb']."','".$bar['divisi']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','html');\" ></td>";
			
			
			$isi.="<td align=center><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['norkb']."','".$bar['divisi']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','excel');\" ></td>";


            $tab.=$isi;

            $tab.="</tr>";
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
                     <tr><td colspan=12 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $footd.="</td>
            </tr>";



        echo $tab . "####" . $footd;

	break;
	case'preview':
		if($tipe=='html'){
			$theme=$_SESSION['theme'];
			if($theme=='skyblue' || $theme==''){
			  $gen='generic.css';
			}else if($theme=='red'){
			  $gen='genericRed.css';  
			}else{
			  $gen='genericGray.css';  
			} 
			echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>
			";  
		}
		echo"<pre>";
		$where='';
		if($tipe=='excel' or $tipe=='pdf'){
			$where=" border=1 cellspacing=0 cellpadding=1 class=sortable width=100%";
		}else{
			$where=" border=0 cellspacing=1 cellpadding=1 class=sortable width=100%";
		}
		$tab="";
		$tab.="<hr>";
		
		$tab.="<span><b>Approval</b></span>";
		if($tipe=='excel' or $tipe=='pdf'){
			$tab.="<table  border=1 cellspacing=0 cellpadding=1 class=sortable>";
		}else{
			$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
		}
		$countApprove = getCountApproval('RKB',$kodeorg);
		$str=" select * from ".$dbname.".kebun_rkbht where  norkb='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$tab.= "<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>
				<td valign=top>".$nmkar[$bar['updateby']]."<br>
					".$bar['lastupdate']."</td>";
					
		for($i=1;$i<=$countApprove;$i++){
			$arrApp = detailApprove($i,$notransaksi,'RKB');
			if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
				$tngl='';
			}else{
				$tngl=tanggalnormal($arrApp['tanggal']);
			}
			
			if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
				$tab.= "<td valign=top>".$arrApp['nama']."
						<br>".$optstatus[$arrApp['status']]."
						<br>".$tngl."
						<br>".$arrApp['komentar']."
						</td>";
			}else{
				$tab.= "<td>&nbsp;</td>";
			}
		}
				
			
		$tab.= "</tbody></table>";
		$tab.="<br>";
		
		#status tolak
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		
		$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				if($tipe=='excel' or $tipe=='pdf'){
				$tab.="<table  border=1 cellspacing=0 cellpadding=1 class=sortable>";
			}else{
				$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
			}
				$tab.="<br>
						<thead>
						<tr style='font-weight:bold'>
							<td colspan='".(1+$val['level'])."'>Return / Tolak - ".$no."</td>
						</tr>
						<tr style='font-weight:bold'>
							<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
							for($i=1;$i<=$val['level'];$i++) {
								$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
							}
						$tab.="</tr>
					</thead>
					<tbody>
						<tr class=rowcontent>
							<td valign=top>".$nmkar[$bar['updateby']]."<br>
											".$bar['lastupdate']."</td>";
							for($i=1;$i<=$val['level'];$i++) {
								$strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
								$resx=fetchdata($strx);
								$color='';
								if($resx[0]['status']==3){
									$color=" style=background-color:red ";
								}
								$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
									<br>	
									".$arrHsl[$resx[0]['status']]."
									<br>	
									".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
									<br>	
									".$resx[0]['komentar']."
								</td>";
							}
						$tab.="</tr>
					</tbody>
					</table>";
			}
		}
		#end status tolak
		
		$tab2="<span><b>Pemeliharaan</b></span>";
		$tab2.="<table ".$where.">
			<thead><tr class=rowheader>";
			$rows="rowspan=2";	
			$tab2.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['pekerjaan']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['luas'] . "</td>
				<td align=center ".$rows.">Output</td>
				<td align=center colspan=5>Tenaga Kerja</td>
				<td align=center ".$rows.">Premi</td>
				<td align=center colspan=3>Borongan</td>
				<td align=center colspan=6 >".$_SESSION['lang']['material']."</td>
				<td align=center ".$rows.">Total<br>Rupiah</td>
			</tr>
			<tr>
				<td align=center>KBL</td>
				<td align=center>KHT</td>
				<td align=center>KHL</td>
				<td align=center>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center>".$_SESSION['lang']['rupiah'] . "</td>
				<td align=center>Luas</td>
				<td align=center>Rp/Ha</td>
				<td align=center>Rupiah</td>
				<td align=center width=20px>No</td>
				<td align=center width=85px>".$_SESSION['lang']['nama']."</td>
				<td align=center width=30px>Sat</td>
				<td align=center width=40px>Dosis</td>
				<td align=center width=50px>Jumlah</td>
				<td align=center width=80px>Rupiah</td>
			</tr>
			</thead>";
		
        $str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='PEMEL' 
		and periode='".$periode."' and kodeorg='".$kodeorg."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no+=1;
			$tab2.="<tr class=rowcontent>";
			$tab2.="<td valign=top align=center>" . $no . "</td>";
			$tab2.="<td valign=top>".$nmkeg[$bar['kodekegiatan']]."</td>";
			$tab2.="<td valign=top>".$bar['blok']."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['hasilkerja'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['norma'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['KBL'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['KHT'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['KHL'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['upah'])."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['premi'])."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['hasilkerjaborongan'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['hargaborongan'])."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['rupiahborongan'])."</td>";
			$tab2.="<td valign=top align=right colspan=6>";
				$strx = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='PEMEL' 
				and periode='".$periode."' and kodeorg='".$bar['kodeorg']."' and divisi='".$bar['divisi']."' 
				and kodekegiatan='".$bar['kodekegiatan']."' and blok='".$bar['blok']."'"; 
				$jlh = fetchData($strx);
				$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$nox='';$ttlrpbahan='';
				if(count($jlh)>0){
					$tab2.="<table ".$where.">";
					$tab2.="<tbody>";
					while($barx=$resx->fetch()){
						$nox++;
						$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$barx['kodebarang']."'");
						$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$barx['kodebarang']."'");
						$tab2.="<tr class=rowcontent>";
						$tab2.="<td align=center  width=20px>".$nox."</td>";
						if(strlen($optnmbrg[$barx['kodebarang']])>15){
							$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,15)."";
						}else{
							$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,15)."";
						}
						$tab2.="<td width=85px>".$namabarang."</td>";
						$tab2.="<td width=30px>".ucfirst(strtolower($nmsat[$barx['kodebarang']]))."</td>";
						if($barx['kwantitas']!=0){
							$tab2.="<td width=40px align=right>".number_format($barx['kwantitas']/$barx['luas'],2)."</td>";
						}else{
							$tab2.="<td width=40px align=right></td>";
						}
						$tab2.="<td width=50px align=right>".number_format($barx['kwantitas'],2)."</td>";
						$tab2.="<td width=80px align=right>".number_format($barx['jumlahrp'])."</td>";
						$ttlrpbahan+=$barx['jumlahrp'];
					}
					$tab2.="</tr>";
					$tab2.="</tbody>";
					$tab2.="</table>";					
				}
				
			$tab2.="</td>";
			$totalrp=$bar['upah']+$bar['premi']+$bar['rupiahborongan']+$ttlrpbahan;
			$tab2.="<td valign=top align=right>".number_format($totalrp)."</td>";
			
			@$ttlluas+=$bar['hasilkerja'];
			@$ttlkbl+=$bar['KBL'];
			@$ttlkht+=$bar['KHT'];
			@$ttlkhl+=$bar['KHL'];
			@$ttlupah+=$bar['upah'];
			@$ttlpremi+=$bar['premi'];
			@$ttlluasbor+=$bar['hasilkerjaborongan'];
			@$ttlrpbor+=$bar['rupiahborongan'];
			@$ttlbahan+=$ttlrpbahan;
			@$gtrp+=$totalrp;
			
		}		
		$tab2.="</tr>";
		$tab2.="<tr class=rowcontent>";
		$tab2.="<td colspan=3 bgcolor=cyan align=center><b>TOTAL</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlluas,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format(($ttlkbl+$ttlkht+$ttlkhl)/$ttlluas,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlkht,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlkhl,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl+$ttlkht+$ttlkhl,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlupah)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlpremi)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlluasbor,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlrpbor/$ttlluasbor)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlrpbor)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right colspan=5></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlbahan)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($gtrp)."</b></td>";
        $tab2.="</tr>";
        $tab2.="</table>";
		
		$tab2.="<hr>";
		
		
		$tab3="<span><b>Panen</b></span>";
		$tab3.="
			<table ".$where.">
			<thead><tr class=rowheader>";
			
			$rows="rowspan=4";	
			$tab3.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['luas'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['sph'] . "</td>
				<td align=center ".$rows.">TT</td>
				<td align=center ".$rows.">".$_SESSION['lang']['pokok'] . "</td>
				<td align=center ".$rows." >".$_SESSION['lang']['rotasi'] . "</td>
				<td align=center ".$rows." >AKP<br>%</td>
				<td align=center ".$rows." >BJR</td>
				<td align=center ".$rows.">Jjg</td>
				<td align=center ".$rows.">Kg</td>
				<td align=center ".$rows." >Output</td>
				
				<td align=center colspan=16>".$_SESSION['lang']['biaya'] . "</td>
				<td align=center ".$rows.">Rp/Kg</td>
			</tr>
			<tr>
				<td align=center colspan=9>Pemanen</td>
				<td align=center colspan=6>Supervisi</td>
				<td align=center rowspan=3>Total Biaya</td>
			</tr>
			<tr>
				<td align=center colspan=3>HK</td>
				<td align=center rowspan=2>Upah</td>
				<td align=center colspan=4>Premi</td>
				<td align=center rowspan=2>Total</td>
				<td align=center colspan=2>Mandor Panen</td>
				<td align=center colspan=2>Kerani Panen</td>
				<td align=center colspan=2>Total</td>
			</tr>
			<tr>
				<td align=center>KBL</td>
				<td align=center>KHT</td>
				<td align=center>KHL</td>
				<td align=center>1</td>
				<td align=center>Kutib Brd</td>
				<td align=center width=30px>Kg Brd</td>
				<td align=center>Sub TTL</td>
				<td align=center >Upah</td>
				<td align=center >Premi</td>
				<td align=center >Upah</td>
				<td align=center >Premi</td>
				<td align=center>Upah</td>
				<td align=center>Premi</td>
			</tr>
			</thead>";
			
		$t_luas='';
		$t_pokok='';
		$t_hasilkerja='';
		$t_hasilkerjakg='';
		$t_KBL='';
		$t_KHT='';
		$t_KHL='';
		$t_upah='';
		$t_premilebihbasis1='';
		$t_premilebihbasis2='';
		$t_premibrondol='';
		$t_rupiahborongan='';
		$t_upahmdr='';
		$t_premimdr='';
		$t_upahkrn='';
		$t_premikrn='';
		$t_upahmdr1='';
		$t_premimdr1='';

		$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='PANEN' 
		and periode='".$periode."' and kodeorg='".$kodeorg."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while ($bar = $res->fetch()) {
			$optluas=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$bar['blok']."'");
			$optpokok=makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$bar['blok']."'");
			$opttt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['blok']."'");
			
			$no+=1;
			$tab3.="<tr class=rowcontent>";
			$tab3.="<td align=center>" . $no . "</td>";
			$tab3.="<td >".$bar['blok']."</td>";
			$tab3.="<td align=right>".number_format($optluas[$bar['blok']],2)."</td>";
			$tab3.="<td align=right>".number_format($optpokok[$bar['blok']]/$optluas[$bar['blok']],2)."</td>";
			$tab3.="<td align=right>".$opttt[$bar['blok']]."</td>";
			$tab3.="<td align=right>".number_format($optpokok[$bar['blok']])."</td>";
			$tab3.="<td  align=right>".$bar['rotasi']."</td>";
			$tab3.="<td  align=right>".number_format($bar['akp'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['bjr'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['hasilkerja'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['hasilkerjakg'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['norma'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['KBL'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['KHT'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['KHL'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['upah'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premilebihbasis1'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premibrondol'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['brondolankg'])."</td>";
			@$tpremi=$bar['premilebihbasis1']+$bar['premilebihbasis2']+$bar['premibrondol']+$bar['rupiahborongan'];
			$tab3.="<td  align=right>".number_format($tpremi)."</td>";
			$tupahpremi=$tpremi+$bar['upah'];
			$tab3.="<td  align=right>".number_format($tupahpremi)."</td>";
			$tab3.="<td  align=right>".number_format($bar['upahmdr'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premimdr'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['upahkrn'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premikrn'])."</td>";
			$tupahpejabat=$bar['upahmdr']+$bar['upahkrn']+$bar['upahmdr1'];
			$tab3.="<td  align=right>".number_format($tupahpejabat)."</td>";
			$tpremipejabat=$bar['premimdr']+$bar['premikrn'];
			$tab3.="<td  align=right>".number_format($tpremipejabat)."</td>";
			$gttl=$tupahpremi+$tupahpejabat+$tpremipejabat;
			$tab3.="<td  align=right>".number_format($gttl)."</td>";
			if($gttl>0){
				$tab3.="<td  align=right>".number_format($gttl/$bar['hasilkerjakg'],2)."</td>";
			}else{
				$tab3.="<td  align=right></td>";
			}
			
			@$t_luas+=$optluas[$bar['blok']];
			@$t_pokok+=$optpokok[$bar['blok']];
			@$t_hasilkerja+=$bar['hasilkerja'];
			@$t_hasilkerjakg+=$bar['hasilkerjakg'];
			@$t_KBL+=$bar['KBL'];
			@$t_KHT+=$bar['KHT'];
			@$t_KHL+=$bar['KHL'];
			@$t_upah+=$bar['upah'];
			@$t_premilebihbasis1+=$bar['premilebihbasis1'];
			@$t_premilebihbasis2+=$bar['premilebihbasis2'];
			@$t_premibrondol+=$bar['premibrondol'];
			@$t_rupiahborongan+=$bar['rupiahborongan'];
			@$t_brondolankg+=$bar['brondolankg'];
			@$t_upahmdr+=$bar['upahmdr'];
			@$t_premimdr+=$bar['premimdr'];
			@$t_upahkrn+=$bar['upahkrn'];
			@$t_premikrn+=$bar['premikrn'];
			@$t_upahmdr1+=$bar['upahmdr1'];
			@$t_premimdr1+=$bar['premimdr1'];

		}
		$tab3.="</tr>";
		$tab3.="<tr class=rowcontent style=background-color:cyan align=center>";
		$tab3.="<td align=center colspan=2>T O T A L</td>";
		$tab3.="<td align=right>".number_format($t_luas,2)."</td>";
		$tab3.="<td align=right>".number_format($t_pokok/$t_luas,2)."</td>";
		$tab3.="<td align=right></td>";
		$tab3.="<td align=right>".number_format($t_pokok)."</td>";
		$tab3.="<td  align=right></td>";
		$tab3.="<td  align=right></td>";
		$tab3.="<td  align=right>".number_format($t_bjr,2)."</td>";
		$tab3.="<td  align=right>".number_format($t_hasilkerja)."</td>";
		$tab3.="<td  align=right>".number_format($t_hasilkerjakg)."</td>";
		$tab3.="<td  align=right></td>";
		$tab3.="<td  align=right>".number_format($t_KBL,2)."</td>";
		$tab3.="<td  align=right>".number_format($t_KHT,2)."</td>";
		$tab3.="<td  align=right>".number_format($t_KHL,2)."</td>";
		$tab3.="<td  align=right>".number_format($t_upah)."</td>";
		$tab3.="<td  align=right>".number_format($t_premilebihbasis1)."</td>";
		$tab3.="<td  align=right>".number_format($t_premibrondol)."</td>";
		$tab3.="<td  align=right>".number_format($t_brondolankg)."</td>";
		@$t_tpremi=$t_premilebihbasis1+$t_premibrondol;
		$tab3.="<td  align=right>".number_format($t_tpremi)."</td>";
		$t_tupahpremi=$t_tpremi+$t_upah;
		$tab3.="<td  align=right>".number_format($t_tupahpremi)."</td>";
		$tab3.="<td  align=right>".number_format($t_upahmdr)."</td>";
		$tab3.="<td  align=right>".number_format($t_premimdr)."</td>";
		$tab3.="<td  align=right>".number_format($t_upahkrn)."</td>";
		$tab3.="<td  align=right>".number_format($t_premikrn)."</td>";
		$t_tupahpejabat=$t_upahmdr+$t_upahkrn;
		$tab3.="<td  align=right>".number_format($t_tupahpejabat)."</td>";
		$t_tpremipejabat=$t_premimdr+$t_premikrn;
		$tab3.="<td  align=right>".number_format($t_tpremipejabat)."</td>";
		$t_gttl=$t_tupahpremi+$t_tupahpejabat+$t_tpremipejabat;
		$tab3.="<td  align=right>".number_format($t_gttl)."</td>";
		if($t_gttl>0){
			$tab3.="<td  align=right>".number_format($t_gttl/$t_hasilkerjakg,2)."</td>";
		}else{
			$tab3.="<td  align=right></td>";
		}
			
		
		$tab3.="</table>";
		
		$tab3.="<hr>";
		
		$tab4.="<span><b>Pengangkutan</b></span>";
		$tab4.="<table ".$where.">
				<thead><tr class=rowheader>";
			$rows="rowspan=4";	
			$tab4.="<td align=center ".$rows." width=20px>No</td>
				<th align=center ".$rows.">".$_SESSION['lang']['divisi'] . "</th>
				<td align=center ".$rows.">Blok</td>
				<td align=center ".$rows.">".$_SESSION['lang']['produksi'] . "</td>
				<td align=center ".$rows." >Jarak ke PKS KM</td>
				
				<td align=center rowspan=1 colspan=8>Angkutan Sendiri</td>
				<td align=center rowspan=1 colspan=4>Angkutan Kontrak</td>
				<td align=center rowspan=1 colspan=4>Langsir Manual</td>
				<td align=center rowspan=1 colspan=4>Langsir Mekanis</td>
				<td align=center rowspan=1 colspan=12>Biaya Bongkar Muat</td>
				<td align=center rowspan=1 colspan=2>Total<br>Biaya</td>
			</tr>
			<tr>
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kap<br>Kg</td>
				<td align=center rowspan=3>Trip<br>PKS</td>
				<td align=center rowspan=3>KM</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Rp/KM</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>

				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center colspan=7>Upah</td>
				<td align=center colspan=3>Premi</td>
				<td align=center colspan=2>Total</td>
				
				<td align=center rowspan=3>Rp</td>
				<td align=center rowspan=3>Rp/Kg</td>
			</tr>
			<tr>
				<td align=center rowspan=2>Output<br>Kg/HK</td>
				<td align=center rowspan=1 colspan=4>HK</td>
				<td align=center rowspan=1 colspan=2>Total Upah</td>
				<td align=center rowspan=2>Kg</td>
				<td align=center rowspan=2>Rp/Kg</td>
				<td align=center rowspan=2>Rp</td>
				<td align=center rowspan=2>Rp</td>
				<td align=center rowspan=2>Rp/Kg</td>
			</tr>
			<tr>
				<td align=center >KBL</td>
				<td align=center >KHT</td>
				<td align=center >KHL</td>
				<td align=center >Total</td>
				<td align=center >Rp</td>
				<td align=center >Rp/Kg</td>
				
			</tr>
			</thead>";
		
		$t_hasilkerjakg='';
		$t_trippks='';
		$t_km='';
		$t_kgsendiri='';
		$t_ttlrpsendiri='';
		$t_hasilkerjaborongan='';
		$t_rupiahborongan='';
		$t_KBL='';
		$t_KHT='';
		$t_KHL='';
		$t_ttlkgbasis='';
		$t_upah='';
		$t_kgpremi='';
		$t_premi='';
		$t_tonalong='';
		$t_rpalong='';

		$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='ANGKUT' 
		and periode='".$periode."' and kodeorg='".$kodeorg."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while ($bar = $res->fetch()) {
			$no+=1;
			$tab4.="<tr class=rowcontent>";
			$tab4.="<td align=center>" . $no . "</td>";
			$tab4.="<td align=center>".$bar['divisi']."</td>";
			$tab4.="<td align=center>".$bar['blok']."</td>";
			$tab4.="<td align=right>".number_format($bar['hasilkerjakg'])."</td>";
			$tab4.="<td align=right>".number_format($bar['jarakpks'])."</td>";
			$tab4.="<td align=right>".number_format($bar['persensendiri'])."</td>";
			$tab4.="<td align=right>".number_format($bar['kapasitas'])."</td>";
			$tab4.="<td align=right>".number_format($bar['trippks'])."</td>";
			$tab4.="<td align=right>".number_format($bar['km'])."</td>";
			$tab4.="<td align=right>".number_format($bar['kgsendiri'])."</td>";
			if($bar['ttlrpsendiri']!=0){
				$tab4.="<td align=right>".number_format($bar['ttlrpsendiri'] / @$bar['kgsendiri'])."</td>";
				$tab4.="<td align=right>".number_format($bar['ttlrpsendiri'] / @$bar['km'])."</td>";
			}else{
				$tab4.="<td align=right>0</td>";
				$tab4.="<td align=right>0</td>";
			}
			$tab4.="<td align=right>".number_format($bar['ttlrpsendiri'])."</td>";
			$tab4.="<td align=right>".number_format(100-$bar['persensendiri'])."</td>";
			$tab4.="<td align=right>".number_format($bar['hasilkerjaborongan'])."</td>";
			$tab4.="<td align=right>".number_format($bar['hargaborongan'])."</td>";
			$tab4.="<td align=right>".number_format($bar['rupiahborongan'])."</td>";
			
			$tab4.="<td align=right>".number_format($bar['persenalong'])."</td>";
			$tab4.="<td align=right>".number_format($bar['tonalong'])."</td>";
			$tab4.="<td align=right>".number_format($bar['hargaalong'])."</td>";
			$tab4.="<td align=right>".number_format($bar['rpalong'])."</td>";
			
			$tab4.="<td align=right>".number_format($bar['persenmekanis'])."</td>";
			$tab4.="<td align=right>".number_format($bar['tonmekanis'])."</td>";
			$tab4.="<td align=right>".number_format($bar['hargamekanis'])."</td>";
			$tab4.="<td align=right>".number_format($bar['rpmekanis'])."</td>";
			
			$tab4.="<td align=right>".number_format($bar['outputkgperhk'])."</td>";
			// $tab4.="<td align=right>".number_format($bar['norma'])."</td>";
			$tab4.="<td align=right>".number_format($bar['KBL'])."</td>";
			$tab4.="<td align=right>".number_format($bar['KHT'])."</td>";
			$tab4.="<td align=right>".number_format($bar['KHL'])."</td>";
			$tab4.="<td align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'])."</td>";
			// $tab4.="<td align=right>".number_format($bar['ttlkgbasis'])."</td>";
			$tab4.="<td align=right>".number_format($bar['upah'])."</td>";
			$tab4.="<td align=right>".number_format($bar['kgpremi'])."</td>";
			$tab4.="<td align=right>".number_format($bar['rpkgpremi'])."</td>";
			$tab4.="<td align=right>".number_format($bar['premi'])."</td>";
			$tab4.="<td align=right>".number_format($bar['upah']+$bar['premi'])."</td>";
			$tab4.="<td align=right>".number_format(($bar['upah']+$bar['premi'])/$bar['kgsendiri'])."</td>";
			$tab4.="<td align=right>".number_format($bar['upah']+$bar['premi']+$bar['rupiahborongan']+$bar['ttlrpsendiri'])."</td>";
			$tab4.="<td align=right>".number_format(($bar['upah']+$bar['premi']+$bar['rupiahborongan']+$bar['ttlrpsendiri'])/$bar['hasilkerjakg'])."</td>";
			
			@$t_hasilkerjakg+=$bar['hasilkerjakg'];
			@$t_trippks+=$bar['trippks'];
			@$t_km+=$bar['km'];
			@$t_kgsendiri+=$bar['kgsendiri'];
			@$t_ttlrpsendiri+=$bar['ttlrpsendiri'];
			@$t_hasilkerjaborongan+=$bar['hasilkerjaborongan'];
			@$t_rupiahborongan+=$bar['rupiahborongan'];
			@$t_KBL+=$bar['KBL'];
			@$t_KHT+=$bar['KHT'];
			@$t_KHL+=$bar['KHL'];
			@$t_ttlkgbasis+=$bar['ttlkgbasis'];
			@$t_upah+=$bar['upah'];
			@$t_kgpremi+=$bar['kgpremi'];
			@$t_rpkgpremi+=$bar['rpkgpremi'];
			@$t_premi+=$bar['premi'];
			@$t_tonalong+=$bar['tonalong'];
			@$t_rpalong+=$bar['rpalong'];
			@$t_tonmekanis+=$bar['tonmekanis'];
			@$t_rpmekanis+=$bar['rpmekanis'];
		}
		$tab4.="</tr>";
		$tab4.="<tr class=rowcontent  style=background-color:cyan>";
		$tab4.="<td align=center colspan=3>TOTAL</td>";
		$tab4.="<td align=right>".@number_format($t_hasilkerjakg)."</td>";
		$tab4.="<td align=right>".@number_format($t_km/$t_trippks/2)."</td>";
		$t_persensendiri=$t_kgsendiri/$t_hasilkerjakg;
		$tab4.="<td align=right>".@number_format($t_persensendiri*100)."</td>";
		$tab4.="<td align=right>".@number_format($t_kgsendiri/$t_trippks)."</td>";
		$tab4.="<td align=right>".@number_format($t_trippks)."</td>";
		$tab4.="<td align=right>".@number_format($t_km)."</td>";
		$tab4.="<td align=right>".@number_format($t_kgsendiri)."</td>";
		if($t_ttlrpsendiri!=0){
			$tab4.="<td align=right>".@number_format($t_ttlrpsendiri / @$t_kgsendiri)."</td>";
			$tab4.="<td align=right>".@number_format($t_ttlrpsendiri / @$t_km)."</td>";
		}else{
			$tab4.="<td align=right>0</td>";
			$tab4.="<td align=right>0</td>";
		}
		$tab4.="<td align=right>".@number_format($t_ttlrpsendiri)."</td>";
		$tab4.="<td align=right>".@number_format(100-($t_persensendiri*100))."</td>";
		$tab4.="<td align=right>".@number_format($t_hasilkerjaborongan)."</td>";
		$tab4.="<td align=right>".@number_format($t_rupiahborongan/$t_hasilkerjaborongan)."</td>";
		$tab4.="<td align=right>".@number_format($t_rupiahborongan)."</td>";
		
		$tab4.="<td align=right>".@number_format(($t_tonalong/$t_hasilkerjakg)*100)."</td>";
		$tab4.="<td align=right>".@number_format($t_tonalong)."</td>";
		$tab4.="<td align=right>".@number_format($t_rpalong/$t_tonalong)."</td>";
		$tab4.="<td align=right>".@number_format($t_rpalong)."</td>";

		$tab4.="<td align=right>".@number_format(($t_tonmekanis/$t_hasilkerjakg)*100)."</td>";
		$tab4.="<td align=right>".@number_format($t_tonmekanis)."</td>";
		$tab4.="<td align=right>".@number_format($t_rpmekanis/$t_tonmekanis)."</td>";
		$tab4.="<td align=right>".@number_format($t_rpmekanis)."</td>";
		
		$tab4.="<td align=right>".@number_format($t_hasilkerjakg/($t_KBL+$t_KHT+$t_KHL))."</td>";
		// $tab4.="<td align=right>".@number_format($t_ttlkgbasis/($t_KBL+$t_KHT+$t_KHL))."</td>";
		$tab4.="<td align=right>".@number_format($t_KBL)."</td>";
		$tab4.="<td align=right>".@number_format($t_KHT)."</td>";
		$tab4.="<td align=right>".@number_format($t_KHL)."</td>";
		$tab4.="<td align=right>".@number_format($t_KBL+$t_KHT+$t_KHL)."</td>";
		// $tab4.="<td align=right>".@number_format($t_ttlkgbasis)."</td>";
		$tab4.="<td align=right>".@number_format($t_upah)."</td>";
		$tab4.="<td align=right>".@number_format($t_upah/$t_ttlkgbasis,2)."</td>";
		$tab4.="<td align=right>".@number_format($t_kgpremi)."</td>";
		$tab4.="<td align=right>".@number_format($t_rpkgpremi)."</td>";
		$tab4.="<td align=right>".@number_format($t_premi)."</td>";
		$tab4.="<td align=right>".@number_format($t_upah+$t_premi)."</td>";
		$tab4.="<td align=right>".@number_format(($t_upah+$t_premi)/$t_kgsendiri)."</td>";
		$tab4.="<td align=right>".@number_format($t_upah+$t_premi+$t_rupiahborongan+$t_ttlrpsendiri)."</td>";
		$tab4.="<td align=right>".@number_format(($t_upah+$t_premi+$t_rupiahborongan+$t_ttlrpsendiri)/$t_hasilkerjakg)."</td>";
		$tab4.="</tr>";
        $tab4.="</table>";
		
		$tab4.="<hr>";
		
		$tab5.="<span><b>UMUM</b></span>";
		$tab5.="<table ".$where.">
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$tab5.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['jabatan'] . "</td>
				
				<td align=center rowspan=1 colspan=4>Tenaga Kerja</td>
				<td align=center rowspan=2 colspan=1>Upah</td>
				<td align=center rowspan=1 colspan=4>Lembur dan Premi</td>
				<td align=center rowspan=1 colspan=3>Material</td>
				<td align=center rowspan=2 colspan=1>Totah Rupiah</td>
			</tr>
			<tr>
				<td align=center>KBL</td>
				<td align=center>KHT</td>
				<td align=center>KHL</td>
				<td align=center>Total</td>
				<td align=center>Jam</td>
				<td align=center>Rp/Jam</td>
				<td align=center>Lembur</td>
				<td align=center>Premi</td>
				<td align=center>Nama</td>
				<td align=center>Sat</td>
				<td align=center>Jumlah</td>
				
			</tr>
			</thead>";
			$ttlkbl='';
			$ttlkht='';
			$ttlkhl='';
			$ttlupah='';
			$ttlpremi='';
			$ttljamlembur='';
			$ttlrplembur='';
			$gtrp='';

			$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='UMUM' 
			and periode='".$periode."' and kodeorg='".$kodeorg."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$nmjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$bar['kodekegiatan']."'");
				$no+=1;
				$tab5.="<tr class=rowcontent>";
				$tab5.="<td valign=top align=center>" . $no . "</td>";
				$tab5.="<td valign=top>".$nmjab[$bar['kodekegiatan']]."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['KBL'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['KHT'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['KHL'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['upah'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['jamlembur'])."</td>";
				if($bar['jamlembur']!=0 && $bar['rplembur']!=0){
					$tab5.="<td valign=top align=right>".number_format($bar['rplembur']/$bar['jamlembur'])."</td>";					
				}else{
					$tab5.="<td valign=top align=right>0</td>";					
				}
				$tab5.="<td valign=top align=right>".number_format($bar['rplembur'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['premi'])."</td>";
				$tab5.="<td valign=top align=right colspan=3>";
					$strx = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='UMUM' and periode='".$periode."' and kodeorg='".$bar['kodeorg']."' and divisi='".$bar['divisi']."' and kodekegiatan='".$bar['kodekegiatan']."'"; 
					$jlh = fetchData($strx);
					$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
					$resx->setFetchMode(PDO::FETCH_ASSOC);
					$nox='';$ttlrpbahan='';
					if(count($jlh)>0){
						$tab5.="<table ".$where.">";
						$tab5.="<tbody>";
						while($barx=$resx->fetch()){
							$nox++;
							$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$barx['kodebarang']."'");
							$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$barx['kodebarang']."'");
							$tab5.="<tr class=rowcontent>";
							$tab5.="<td align=center>".$nox."</td>";
							if(strlen($optnmbrg[$barx['kodebarang']])>12){
								$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,12)." ...";
							}else{
								$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,12)."";
							}
							$tab5.="<td>".$namabarang."</td>";
							$tab5.="<td >".$nmsat[$barx['kodebarang']]."</td>";
							$tab5.="<td align=right>".number_format($barx['kwantitas'],2)."</td>";
							$ttlrpbahan+=$barx['jumlahrp'];
						}
						$tab5.="</tr>";
						$tab5.="</tbody>";
						$tab5.="</table>";					
					}
					
				$tab5.="</td>";
				$totalrp=$bar['upah']+$bar['premi']+$bar['rplembur'];
				$tab5.="<td valign=top align=right>".number_format($totalrp)."</td>";
				
				@$ttlkbl+=$bar['KBL'];
				@$ttlkht+=$bar['KHT'];
				@$ttlkhl+=$bar['KHL'];
				@$ttlupah+=$bar['upah'];
				@$ttlpremi+=$bar['premi'];
				@$ttljamlembur+=$bar['jamlembur'];
				@$ttlrplembur+=$bar['rplembur'];
				@$gtrp+=$totalrp;
				
			}		
			$tab5.="</tr>";
			$tab5.="<tr class=rowcontent>";
			$tab5.="<td colspan=2 bgcolor=cyan align=center><b>TOTAL</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlkht)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlkhl)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl+$ttlkht+$ttlkhl)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlupah)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttljamlembur)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlrplembur/$ttljamlembur)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlrplembur)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlpremi)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right colspan=3></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($gtrp)."</b></td>";
			$tab5.="</tr>";
			$tab5.="</table>";
			
		
		$tab5.="<hr>";
		
		// $tab6.="<span><b>SUPPORT</b></span>";
		// $tab6.="<table ".$where.">";
			
		// $tab6.="<thead><tr class=rowheader>";
			
		// $rows="rowspan=3";	
		// $tab6.="<td align=center ".$rows." width=20px>No</td>
		// 	<td align=center ".$rows.">".$_SESSION['lang']['departemen'] . "</td>
		// 	<td align=center ".$rows.">".$_SESSION['lang']['jabatan'] . "</td>
		// 	<td align=center ".$rows.">Komponen Gaji</td>
		// 	<td align=center ".$rows.">Keterangan</td>
		// 	<td align=center colspan=12>Tipe Karyawan</td>
		// </tr>
		// <tr>
		// 	<td align=center colspan=3>KBL</td>
		// 	<td align=center colspan=3>KHT</td>
		// 	<td align=center colspan=3>KHL</td>
		// 	<td align=center colspan=3>Total</td>
			
		// </tr>
		// <tr>
		// 	<td align=center>TK</td>
		// 	<td align=center>HK</td>
		// 	<td align=center>Rupiah</td>
		// 	<td align=center>TK</td>
		// 	<td align=center>HK</td>
		// 	<td align=center>Rupiah</td>
		// 	<td align=center>TK</td>
		// 	<td align=center>HK</td>
		// 	<td align=center>Rupiah</td>
		// 	<td align=center>TK</td>
		// 	<td align=center>HK</td>
		// 	<td align=center>Rupiah</td>
			
		// </tr>
		// </thead>";
		// $str = "select * from ".$dbname.".kebun_rkbsupport where norkb='".$notransaksi."' and tipetransaksi='SUPPORT' 
		// and periode='".$periode."' and kodeorg='".$kodeorg."'"; 
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $data=array();
		// while($bar=$res->fetch()){
		// 	$data[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']]=$bar['tipekary'];
		// 	@$tk[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']]+=$bar['tk'];
		// 	@$hk[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']]+=$bar['hk'];
		// 	@$rupiah[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']]+=$bar['rupiah'];
		// 	@$ket[$bar['dept']][$bar['jabatan']][$bar['compgaji']]=$bar['keterangan'];
		// 	$tpkary[$bar['tipekary']]=$bar['tipekary'];
		// }
		// if(count($data)>0){
		// 	$no='';
		// 	$optdept=makeOption($dbname,'sdm_5departemen','kode,nama');
		// 	$optjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		// 	$optcompt=makeOption($dbname,'sdm_ho_component','id,name');
		// 	foreach($data as $dept => $valjab){
		// 		foreach($valjab as $jabatan => $valkompgaji){
		// 			foreach($valkompgaji as $kompgaji => $valtipekary){
		// 				$no++;
		// 				$tab6.="<tr class=rowcontent>";
		// 				$tab6.="<td align=center>".$no."</td>";	
		// 				$tab6.="<td>".$optdept[$dept]."</td>";	
		// 				$tab6.="<td>".$optjab[$jabatan]."</td>";	
		// 				$tab6.="<td>".$optcompt[$kompgaji]."</td>";	
		// 				$tab6.="<td align=left width=200px>".$ket[$dept][$jabatan][$kompgaji]."</td>";	
		// 				$ttlrp=$ttlhk=$ttltk='';
		// 				foreach($tpkary as $tipekary){
		// 					$tab6.="<td width=34px align=right>".number_format($tk[$dept][$jabatan][$kompgaji][$tipekary])."</td>";	
		// 					$tab6.="<td width=43px align=right>".number_format($hk[$dept][$jabatan][$kompgaji][$tipekary])."</td>";	
		// 					$tab6.="<td width=62px align=right>".number_format($rupiah[$dept][$jabatan][$kompgaji][$tipekary])."</td>";
		// 					@$ttltk+=$tk[$dept][$jabatan][$kompgaji][$tipekary];
		// 					@$ttlhk+=$hk[$dept][$jabatan][$kompgaji][$tipekary];
		// 					@$ttlrp+=$rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							
		// 					@$stttk[$jabatan][$tipekary]+=$tk[$dept][$jabatan][$kompgaji][$tipekary];
		// 					@$sttlhk[$jabatan][$tipekary]+=$hk[$dept][$jabatan][$kompgaji][$tipekary];
		// 					@$sttlrp[$jabatan][$tipekary]+=$rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							
		// 					@$gstttk[$jabatan]+=$tk[$dept][$jabatan][$kompgaji][$tipekary];
		// 					@$gsttlhk[$jabatan]+=$hk[$dept][$jabatan][$kompgaji][$tipekary];
		// 					@$gsttlrp[$jabatan]+=$rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							
		// 					@$gtstttk[$tipekary]+=$tk[$dept][$jabatan][$kompgaji][$tipekary];
		// 					@$gtsttlhk[$tipekary]+=$hk[$dept][$jabatan][$kompgaji][$tipekary];
		// 					@$gtsttlrp[$tipekary]+=$rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							
		// 				}
		// 				$tab6.="<td width=34px align=right>".number_format($ttltk)."</td>";	
		// 				$tab6.="<td width=44px align=right>".number_format($ttlhk)."</td>";	
		// 				$tab6.="<td width=81px align=right>".number_format($ttlrp)."</td>";	
		// 				$tab6.="</tr>";
		// 			}
		// 			$tab6.="<tr class=rowcontent style=background-color:cyan>";
		// 			$tab6.="<td colspan=2></td><td colspan=3 align=left>Sub Total ".$optjab[$jabatan]."</td>";
		// 			foreach($tpkary as $tipekary){
		// 				$tab6.="<td align=right>".number_format($stttk[$jabatan][$tipekary])."</td>";	
		// 				$tab6.="<td align=right>".number_format($sttlhk[$jabatan][$tipekary])."</td>";							
		// 				$tab6.="<td align=right>".number_format($sttlrp[$jabatan][$tipekary])."</td>";							
		// 			}
		// 				$tab6.="<td align=right>".number_format($gstttk[$jabatan])."</td>";	
		// 				$tab6.="<td align=right>".number_format($gsttlhk[$jabatan])."</td>";							
		// 				$tab6.="<td align=right>".number_format($gsttlrp[$jabatan])."</td>";	
		// 		}
		// 	}
		// 	$tab6.="<tr class=rowcontent style=background-color:skyblue>";
		// 	$tab6.="<td colspan=5 align=center>Total</td>";	
		// 	foreach($tpkary as $tipekary){
		// 		$tab6.="<td align=right>".number_format($gtstttk[$tipekary])."</td>";	
		// 		$tab6.="<td align=right>".number_format($gtsttlhk[$tipekary])."</td>";	
		// 		$tab6.="<td align=right>".number_format($gtsttlrp[$tipekary])."</td>";	
				
		// 		@$gtttk+=$gtstttk[$tipekary];
		// 		@$gtthk+=$gtsttlhk[$tipekary];
		// 		@$gttrp+=$gtsttlrp[$tipekary];
		// 	}
		// 		$tab6.="<td align=right>".number_format($gtttk)."</td>";	
		// 		$tab6.="<td align=right>".number_format($gtthk)."</td>";	
		// 		$tab6.="<td align=right>".number_format($gttrp)."</td>";	
				
		// }
		
		// $tab6.="</tbody>";
		// $tab6.="</table>";
		
		
		if($tipe=='html'){
			// echo $tab.$tab2.$tab3.$tab4.$tab5.$tab6;
			echo $tab.$tab2.$tab3.$tab4.$tab5;
		}elseif($tipe=='pdf'){
			// $stream = $tab.$tab2.$tab3.$tab4.$tab5.$tab6;
			$stream = $tab.$tab2.$tab3.$tab4.$tab5;
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('Legal', 'landscape');
			$dompdf->render();
			$dompdf->stream("RKB",array("Attachment"=>0));
		}else{
			$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
			
			$tempnm = explode("/",$_SERVER['PHP_SELF']);
			$nop = "Rencana Kerja Bulanan Periode ".$periode.".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("approval", $tab);
			$xls->addSheet("pemel", $tab2);
			$xls->addSheet("panen", $tab3);
			$xls->addSheet("angkut", $tab4);
			$xls->addSheet("umum", $tab5);
			// $xls->addSheet("support", $tab6);
			$xls->headers($nop);
			echo $xls->buildFile(); 
		}
		
	break;
	
	case'detailDataRekap':
		if($tipe=='html'){
			$theme=$_SESSION['theme'];
			if($theme=='skyblue' || $theme==''){
			  $gen='generic.css';
			}else if($theme=='red'){
			  $gen='genericRed.css';  
			}else{
			  $gen='genericGray.css';  
			} 
			echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>
			";  
		}
		//echo"<pre>";
		$where='';
		if($tipe=='excel' or $tipe=='pdf'){
			$where=" border=1 cellspacing=0 cellpadding=1 class=sortable width=100%";
		}else{
			$where=" border=0 cellspacing=1 cellpadding=3 class=sortable";
		}
		$tab="";
		$tab.="<hr>";
		
		$tab.="<span><b>Approval</b></span>";
		if($tipe=='excel' or $tipe=='pdf'){
			$tab.="<table  border=1 cellspacing=0 cellpadding=1 class=sortable>";
		}else{
			$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
		}
		$countApprove = getCountApproval('RKB',$kodeorg);
		$str=" select * from ".$dbname.".kebun_rkbht where  norkb='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$tab.= "<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>
				<td valign=top>".$nmkar[$bar['updateby']]."<br>
					".$bar['lastupdate']."</td>";
					
		for($i=1;$i<=$countApprove;$i++){
			$arrApp = detailApprove($i,$notransaksi,'RKB');
			if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
				$tngl='';
			}else{
				$tngl=tanggalnormal($arrApp['tanggal']);
			}
			
			if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
				$tab.= "<td valign=top>".$arrApp['nama']."
						<br>".$optstatus[$arrApp['status']]."
						<br>".$tngl."
						<br>".$arrApp['komentar']."
						</td>";
			}else{
				$tab.= "<td>&nbsp;</td>";
			}
		}
				
			
		$tab.= "</tbody></table>";
		$tab.="<br>";
		
		#status tolak
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		
		$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				if($tipe=='excel' or $tipe=='pdf'){
				$tab.="<table  border=1 cellspacing=0 cellpadding=1 class=sortable>";
			}else{
				$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
			}
				$tab.="<br>
						<thead>
						<tr style='font-weight:bold'>
							<td colspan='".(1+$val['level'])."'>Return / Tolak - ".$no."</td>
						</tr>
						<tr style='font-weight:bold'>
							<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
							for($i=1;$i<=$val['level'];$i++) {
								$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
							}
						$tab.="</tr>
					</thead>
					<tbody>
						<tr class=rowcontent>
							<td valign=top>".$nmkar[$bar['updateby']]."<br>
											".$bar['lastupdate']."</td>";
							for($i=1;$i<=$val['level'];$i++) {
								$strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
								$resx=fetchdata($strx);
								$color='';
								if($resx[0]['status']==3){
									$color=" style=background-color:red ";
								}
								$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
									<br>	
									".$arrHsl[$resx[0]['status']]."
									<br>	
									".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
									<br>	
									".$resx[0]['komentar']."
								</td>";
							}
						$tab.="</tr>
					</tbody>
					</table>";
			}
		}
		#end status tolak
		
		$tab2="<span><b>Pemeliharaan</b></span>";
		$tab2.="<table ".$where.">
			<thead><tr class=rowheader>";
			$rows="rowspan=2";	
			$tab2.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['divisi']."</td>
				<td align=center colspan=5>Tenaga Kerja</td>
				<td align=center ".$rows.">Premi</td>
				<td align=center>Borongan</td>
				<td align=center colspan=5 >".$_SESSION['lang']['material']."</td>
				<td align=center ".$rows.">Total<br>Rupiah</td>
			</tr>
			<tr>
				<td align=center>KBL</td>
				<td align=center>KHT</td>
				<td align=center>KHL</td>
				<td align=center>".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center>".$_SESSION['lang']['rupiah'] . "</td>
				<td align=center>Rupiah</td>
				<td align=center width=20px>No</td>
				<td align=center width=185px>".$_SESSION['lang']['nama']."</td>
				<td align=center width=30px>Sat</td>
				<td align=center width=50px>Jumlah</td>
				<td align=center width=80px>Rupiah</td>
			</tr>
			</thead>";
		
        $str = "select norkb,divisi,periode,sum(KBL) as KBL,sum(KHT) as KHT,sum(KHL) as KHL,sum(upah) as upah,sum(premi) as premi,sum(rupiahborongan) as rupiahborongan from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='PEMEL' and periode='".$periode."' and kodeorg='".$kodeorg."' group by divisi";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no+=1;
			$tab2.="<tr class=rowcontent>";
			$tab2.="<td valign=top align=center>" . $no . "</td>";
			$tab2.="<td valign=top>".$bar['divisi']."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['KBL'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['KHT'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['KHL'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'],2)."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['upah'])."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['premi'])."</td>";
			$tab2.="<td valign=top align=right>".number_format($bar['rupiahborongan'])."</td>";
			$tab2.="<td valign=top align=right colspan=5>";
				$strx = "select kodebarang,sum(kwantitas) as kwantitas,sum(jumlahrp) as jumlahrp from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='PEMEL' 
				and periode='".$periode."'  and divisi='".$bar['divisi']."' group by kodebarang"; 
				$jlh = fetchData($strx);
				$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$nox='';$ttlrpbahan='';
				if(count($jlh)>0){
					$tab2.="<table ".$where.">";
					$tab2.="<tbody>";
					while($barx=$resx->fetch()){
						$nox++;
						$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$barx['kodebarang']."'");
						$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$barx['kodebarang']."'");
						$tab2.="<tr class=rowcontent>";
						$tab2.="<td align=center  width=20px>".$nox."</td>";
						$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,15)."";
						$tab2.="<td width=185px>".$namabarang."</td>";
						$tab2.="<td width=30px>".ucfirst(strtolower($nmsat[$barx['kodebarang']]))."</td>";
						$tab2.="<td width=50px align=right>".number_format($barx['kwantitas'],2)."</td>";
						$tab2.="<td width=80px align=right>".number_format($barx['jumlahrp'])."</td>";
						$ttlrpbahan+=$barx['jumlahrp'];
					}
					$tab2.="</tr>";
					$tab2.="</tbody>";
					$tab2.="</table>";					
				}
				
			$tab2.="</td>";
			$totalrp=$bar['upah']+$bar['premi']+$bar['rupiahborongan']+$ttlrpbahan;
			$tab2.="<td valign=top align=right>".number_format($totalrp)."</td>";
			
			@$ttlluas+=$bar['hasilkerja'];
			@$ttlkbl+=$bar['KBL'];
			@$ttlkht+=$bar['KHT'];
			@$ttlkhl+=$bar['KHL'];
			@$ttlupah+=$bar['upah'];
			@$ttlpremi+=$bar['premi'];
			@$ttlluasbor+=$bar['hasilkerjaborongan'];
			@$ttlrpbor+=$bar['rupiahborongan'];
			@$ttlbahan+=$ttlrpbahan;
			@$gtrp+=$totalrp;
			
		}		
		$tab2.="</tr>";
		$tab2.="<tr class=rowcontent>";
		$tab2.="<td colspan=2 bgcolor=cyan align=center><b>TOTAL</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlkht,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlkhl,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl+$ttlkht+$ttlkhl,2)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlupah)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlpremi)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlrpbor)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right colspan=4></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($ttlbahan)."</b></td>";
		$tab2.="<td bgcolor=cyan align=right><b>".@number_format($gtrp)."</b></td>";
        $tab2.="</tr>";
        $tab2.="</table>";
		
		$tab2.="<hr>";
		
		
		$tab3="<span><b>Panen</b></span>";
		$tab3.="
			<table ".$where.">
			<thead><tr class=rowheader>";
			$rows="rowspan=4";	
			$tab3.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['divisi'] . "</td>
				<td align=center ".$rows.">TT</td>
				<td align=center ".$rows.">Jjg</td>
				<td align=center ".$rows.">Kg</td>
				<td align=center ".$rows." >Output</td>
				
				<td align=center colspan=20>".$_SESSION['lang']['biaya'] . "</td>
				<td align=center ".$rows.">Rp/Kg</td>
			</tr>
			<tr>
				<td align=center colspan=11>Pemanen</td>
				<td align=center colspan=8>Supervisi</td>
				<td align=center rowspan=3>Total Biaya</td>
			</tr>
			<tr>
				<td align=center colspan=4>HK</td>
				<td align=center rowspan=2>Upah</td>
				<td align=center colspan=5>Premi</td>
				<td align=center rowspan=2>Total</td>
				<td align=center colspan=2>Mandor Panen</td>
				<td align=center colspan=2>Kerani Panen</td>
				<td align=center colspan=2>Mandor 1</td>
				<td align=center colspan=2>Total</td>
			</tr>
			<tr>
				<td align=center>KBL</td>
				<td align=center>KHT</td>
				<td align=center>KHL</td>
				<td align=center>Sub TTL</td>
				<td align=center>1</td>
				<td align=center>2</td>
				<td align=center>Kutib Brd</td>
				<td align=center>Borongan</td>
				<td align=center>Sub TTL</td>
				<td align=center >Upah</td>
				<td align=center >Premi</td>
				<td align=center >Upah</td>
				<td align=center >Premi</td>
				<td align=center >Upah</td>
				<td align=center>Premi</td>
				<td align=center>Upah</td>
				<td align=center>Premi</td>
			</tr>
			</thead>";
			
		$t_luas='';
		$t_pokok='';
		$t_hasilkerja='';
		$t_hasilkerjakg='';
		$t_KBL='';
		$t_KHT='';
		$t_KHL='';
		$t_upah='';
		$t_premilebihbasis1='';
		$t_premilebihbasis2='';
		$t_premibrondol='';
		$t_rupiahborongan='';
		$t_upahmdr='';
		$t_premimdr='';
		$t_upahkrn='';
		$t_premikrn='';
		$t_upahmdr1='';
		$t_premimdr1='';

		$str = "select norkb,divisi,periode,tahuntanam,sum(KBL) as KBL,sum(KHT) as KHT,sum(KHL) as KHL,sum(upah) as upah,sum(premi) as premi,sum(rupiahborongan) as rupiahborongan,sum(hasilkerja) as hasilkerja,sum(hasilkerjakg) as hasilkerjakg,sum(premilebihbasis1) as premilebihbasis1,sum(premilebihbasis2) as premilebihbasis2,sum(upahmdr) as upahmdr,sum(premimdr) as premimdr,sum(upahkrn) as upahkrn,sum(premikrn) as premikrn,sum(upahmdr1) as upahmdr1,sum(premimdr1) as premimdr1,sum(premibrondol) as premibrondol from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='PANEN' and periode='".$periode."' and kodeorg='".$kodeorg."' group by divisi,tahuntanam";
		
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while ($bar = $res->fetch()) {
			$optluas=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$bar['blok']."'");
			$optpokok=makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$bar['blok']."'");
			$opttt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['blok']."'");
			
			$no+=1;
			$tab3.="<tr class=rowcontent>";
			$tab3.="<td align=center>" . $no . "</td>";
			$tab3.="<td >".$bar['divisi']."</td>";
			$tab3.="<td >".$bar['tahuntanam']."</td>";
			$tab3.="<td  align=right>".number_format($bar['hasilkerja'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['hasilkerjakg'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['norma'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['KBL'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['KHT'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['KHL'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'],2)."</td>";
			$tab3.="<td  align=right>".number_format($bar['upah'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premilebihbasis1'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premilebihbasis2'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premibrondol'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['rupiahborongan'])."</td>";
			@$tpremi=$bar['premilebihbasis1']+$bar['premilebihbasis2']+$bar['premibrondol']+$bar['rupiahborongan'];
			$tab3.="<td  align=right>".number_format($tpremi)."</td>";
			$tupahpremi=$tpremi+$bar['upah'];
			$tab3.="<td  align=right>".number_format($tupahpremi)."</td>";
			$tab3.="<td  align=right>".number_format($bar['upahmdr'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premimdr'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['upahkrn'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premikrn'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['upahmdr1'])."</td>";
			$tab3.="<td  align=right>".number_format($bar['premimdr1'])."</td>";
			$tupahpejabat=$bar['upahmdr']+$bar['upahkrn']+$bar['upahmdr1'];
			$tab3.="<td  align=right>".number_format($tupahpejabat)."</td>";
			$tpremipejabat=$bar['premimdr']+$bar['premikrn']+$bar['premimdr1'];
			$tab3.="<td  align=right>".number_format($tpremipejabat)."</td>";
			$gttl=$tupahpremi+$tupahpejabat+$tpremipejabat;
			$tab3.="<td  align=right>".number_format($gttl)."</td>";
			if($gttl>0){
				$tab3.="<td  align=right>".number_format($gttl/$bar['hasilkerjakg'],2)."</td>";
			}else{
				$tab3.="<td  align=right></td>";
			}
			
			@$t_luas+=$optluas[$bar['blok']];
			@$t_pokok+=$optpokok[$bar['blok']];
			@$t_hasilkerja+=$bar['hasilkerja'];
			@$t_hasilkerjakg+=$bar['hasilkerjakg'];
			@$t_KBL+=$bar['KBL'];
			@$t_KHT+=$bar['KHT'];
			@$t_KHL+=$bar['KHL'];
			@$t_upah+=$bar['upah'];
			@$t_premilebihbasis1+=$bar['premilebihbasis1'];
			@$t_premilebihbasis2+=$bar['premilebihbasis2'];
			@$t_premibrondol+=$bar['premibrondol'];
			@$t_rupiahborongan+=$bar['rupiahborongan'];
			@$t_upahmdr+=$bar['upahmdr'];
			@$t_premimdr+=$bar['premimdr'];
			@$t_upahkrn+=$bar['upahkrn'];
			@$t_premikrn+=$bar['premikrn'];
			@$t_upahmdr1+=$bar['upahmdr1'];
			@$t_premimdr1+=$bar['premimdr1'];

		}
		$tab3.="</tr>";
		$tab3.="<tr class=rowcontent style=background-color:cyan align=center>";
		$tab3.="<td align=center colspan=3>T O T A L</td>";
		$tab3.="<td  align=right>".number_format($t_hasilkerja)."</td>";
		$tab3.="<td  align=right>".number_format($t_hasilkerjakg)."</td>";
		$tab3.="<td  align=right></td>";
		$tab3.="<td  align=right>".number_format($t_KBL,2)."</td>";
		$tab3.="<td  align=right>".number_format($t_KHT,2)."</td>";
		$tab3.="<td  align=right>".number_format($t_KHL,2)."</td>";
		$tab3.="<td  align=right>".number_format($t_KBL+$t_KHT+$t_KHL,2)."</td>";
		$tab3.="<td  align=right>".number_format($t_upah)."</td>";
		$tab3.="<td  align=right>".number_format($t_premilebihbasis1)."</td>";
		$tab3.="<td  align=right>".number_format($t_premilebihbasis2)."</td>";
		$tab3.="<td  align=right>".number_format($t_premibrondol)."</td>";
		$tab3.="<td  align=right>".number_format($t_rupiahborongan)."</td>";
		@$t_tpremi=$t_premilebihbasis1+$t_premilebihbasis2+$t_premibrondol+$t_rupiahborongan;
		$tab3.="<td  align=right>".number_format($t_tpremi)."</td>";
		$t_tupahpremi=$t_tpremi+$t_upah;
		$tab3.="<td  align=right>".number_format($t_tupahpremi)."</td>";
		$tab3.="<td  align=right>".number_format($t_upahmdr)."</td>";
		$tab3.="<td  align=right>".number_format($t_premimdr)."</td>";
		$tab3.="<td  align=right>".number_format($t_upahkrn)."</td>";
		$tab3.="<td  align=right>".number_format($t_premikrn)."</td>";
		$tab3.="<td  align=right>".number_format($t_upahmdr1)."</td>";
		$tab3.="<td  align=right>".number_format($t_premimdr1)."</td>";
		$t_tupahpejabat=$t_upahmdr+$t_upahkrn+$t_upahmdr1;
		$tab3.="<td  align=right>".number_format($t_tupahpejabat)."</td>";
		$t_tpremipejabat=$t_premimdr+$t_premikrn+$t_premimdr1;
		$tab3.="<td  align=right>".number_format($t_tpremipejabat)."</td>";
		$t_gttl=$t_tupahpremi+$t_tupahpejabat+$t_tpremipejabat;
		$tab3.="<td  align=right>".number_format($t_gttl)."</td>";
		if($t_gttl>0){
			$tab3.="<td  align=right>".number_format($t_gttl/$t_hasilkerjakg,2)."</td>";
		}else{
			$tab3.="<td  align=right></td>";
		}
			
		
		$tab3.="</table>";
		
		$tab3.="<hr>";
		
		$tab4.="<span><b>Pengangkutan</b></span>";
		$tab4.="<table ".$where.">
				<thead><tr class=rowheader>";
			$rows="rowspan=4";	
			$tab4.="<td align=center ".$rows." width=20px>No</td>
				<th align=center ".$rows.">".$_SESSION['lang']['divisi'] . "</th>
				<td align=center ".$rows.">TT</td>
				<td align=center ".$rows.">".$_SESSION['lang']['produksi'] . "</td>
				<td align=center ".$rows." >Jarak ke PKS KM</td>
				
				<td align=center rowspan=1 colspan=8>Angkutan Sendiri</td>
				<td align=center rowspan=1 colspan=4>Angkutan Kontrak</td>
				<td align=center rowspan=1 colspan=4>Langsir Manual</td>
				<td align=center rowspan=1 colspan=14>Biaya Bongkar Muat</td>
				<td align=center rowspan=1 colspan=2>Total<br>Biaya</td>
			</tr>
			<tr>
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kap<br>Kg</td>
				<td align=center rowspan=3>Trip<br>PKS</td>
				<td align=center rowspan=3>KM</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Rp/KM</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center rowspan=3>%</td>
				<td align=center rowspan=3>Kg</td>
				<td align=center rowspan=3>Rp/Kg</td>
				<td align=center rowspan=3>Total Rp</td>
				
				<td align=center colspan=9>Upah</td>
				<td align=center colspan=3>Premi</td>
				<td align=center colspan=2>Total</td>
				
				<td align=center rowspan=3>Rp</td>
				<td align=center rowspan=3>Rp/Kg</td>
			</tr>
			<tr>
				<td align=center rowspan=2>Output<br>Kg/HK</td>
				<td align=center rowspan=2>Basis<br>Kg/HK</td>
				<td align=center rowspan=1 colspan=4>HK</td>
				<td align=center rowspan=2>Total<br>Kg Basis</td>
				<td align=center rowspan=1 colspan=2>Total Upah</td>
				<td align=center rowspan=2>Kg</td>
				<td align=center rowspan=2>Rp/Kg</td>
				<td align=center rowspan=2>Rp</td>
				<td align=center rowspan=2>Rp</td>
				<td align=center rowspan=2>Rp/Kg</td>
			</tr>
			<tr>
				<td align=center >KBL</td>
				<td align=center >KHT</td>
				<td align=center >KHL</td>
				<td align=center >Total</td>
				<td align=center >Rp</td>
				<td align=center >Rp/Kg</td>
				
			</tr>
			</thead>";
		
		$t_hasilkerjakg='';
		$t_trippks='';
		$t_km='';
		$t_kgsendiri='';
		$t_ttlrpsendiri='';
		$t_hasilkerjaborongan='';
		$t_rupiahborongan='';
		$t_KBL='';
		$t_KHT='';
		$t_KHL='';
		$t_ttlkgbasis='';
		$t_upah='';
		$t_kgpremi='';
		$t_premi='';
		$t_tonalong='';
		$t_rpalong='';

		$str = "select * from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='ANGKUT' 
		and periode='".$periode."' and kodeorg='".$kodeorg."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while ($bar = $res->fetch()) {
			$no+=1;
			$tab4.="<tr class=rowcontent>";
			$tab4.="<td align=center>" . $no . "</td>";
			$tab4.="<td align=center>".$bar['divisi']."</td>";
			$tab4.="<td align=center>".$bar['tahuntanam']."</td>";
			$tab4.="<td align=right>".number_format($bar['hasilkerjakg'])."</td>";
			$tab4.="<td align=right>".number_format($bar['jarakpks'])."</td>";
			$tab4.="<td align=right>".number_format($bar['persensendiri'])."</td>";
			$tab4.="<td align=right>".number_format($bar['kapasitas'])."</td>";
			$tab4.="<td align=right>".number_format($bar['trippks'])."</td>";
			$tab4.="<td align=right>".number_format($bar['km'])."</td>";
			$tab4.="<td align=right>".number_format($bar['kgsendiri'])."</td>";
			if($bar['ttlrpsendiri']!=0){
				$tab4.="<td align=right>".number_format($bar['ttlrpsendiri'] / @$bar['kgsendiri'])."</td>";
				$tab4.="<td align=right>".number_format($bar['ttlrpsendiri'] / @$bar['km'])."</td>";
			}else{
				$tab4.="<td align=right>0</td>";
				$tab4.="<td align=right>0</td>";
			}
			$tab4.="<td align=right>".number_format($bar['ttlrpsendiri'])."</td>";
			$tab4.="<td align=right>".number_format(100-$bar['persensendiri'])."</td>";
			$tab4.="<td align=right>".number_format($bar['hasilkerjaborongan'])."</td>";
			$tab4.="<td align=right>".number_format($bar['hargaborongan'])."</td>";
			$tab4.="<td align=right>".number_format($bar['rupiahborongan'])."</td>";
			
			$tab4.="<td align=right>".number_format($bar['persenalong'])."</td>";
			$tab4.="<td align=right>".number_format($bar['tonalong'])."</td>";
			$tab4.="<td align=right>".number_format($bar['hargaalong'])."</td>";
			$tab4.="<td align=right>".number_format($bar['rpalong'])."</td>";
			
			
			
			$tab4.="<td align=right>".number_format($bar['outputkgperhk'])."</td>";
			$tab4.="<td align=right>".number_format($bar['norma'])."</td>";
			$tab4.="<td align=right>".number_format($bar['KBL'])."</td>";
			$tab4.="<td align=right>".number_format($bar['KHT'])."</td>";
			$tab4.="<td align=right>".number_format($bar['KHL'])."</td>";
			$tab4.="<td align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'])."</td>";
			$tab4.="<td align=right>".number_format($bar['ttlkgbasis'])."</td>";
			$tab4.="<td align=right>".number_format($bar['upah'])."</td>";
			$tab4.="<td align=right>".number_format($bar['upah']/$bar['ttlkgbasis'],2)."</td>";
			$tab4.="<td align=right>".number_format($bar['kgpremi'])."</td>";
			$tab4.="<td align=right>".number_format($bar['premi']/$bar['kgpremi'])."</td>";
			$tab4.="<td align=right>".number_format($bar['premi'])."</td>";
			$tab4.="<td align=right>".number_format($bar['upah']+$bar['premi'])."</td>";
			$tab4.="<td align=right>".number_format(($bar['upah']+$bar['premi'])/$bar['kgsendiri'])."</td>";
			$tab4.="<td align=right>".number_format($bar['upah']+$bar['premi']+$bar['rupiahborongan']+$bar['ttlrpsendiri'])."</td>";
			$tab4.="<td align=right>".number_format(($bar['upah']+$bar['premi']+$bar['rupiahborongan']+$bar['ttlrpsendiri'])/$bar['hasilkerjakg'])."</td>";
			
			@$t_hasilkerjakg+=$bar['hasilkerjakg'];
			@$t_trippks+=$bar['trippks'];
			@$t_km+=$bar['km'];
			@$t_kgsendiri+=$bar['kgsendiri'];
			@$t_ttlrpsendiri+=$bar['ttlrpsendiri'];
			@$t_hasilkerjaborongan+=$bar['hasilkerjaborongan'];
			@$t_rupiahborongan+=$bar['rupiahborongan'];
			@$t_KBL+=$bar['KBL'];
			@$t_KHT+=$bar['KHT'];
			@$t_KHL+=$bar['KHL'];
			@$t_ttlkgbasis+=$bar['ttlkgbasis'];
			@$t_upah+=$bar['upah'];
			@$t_kgpremi+=$bar['kgpremi'];
			@$t_premi+=$bar['premi'];
			@$t_tonalong+=$bar['tonalong'];
			@$t_rpalong+=$bar['rpalong'];
		}
		$tab4.="</tr>";
		$tab4.="<tr class=rowcontent  style=background-color:cyan>";
		$tab4.="<td align=center colspan=3>TOTAL</td>";
		$tab4.="<td align=right>".@number_format($t_hasilkerjakg)."</td>";
		$tab4.="<td align=right>".@number_format($t_km/$t_trippks/2)."</td>";
		$t_persensendiri=$t_kgsendiri/$t_hasilkerjakg;
		$tab4.="<td align=right>".@number_format($t_persensendiri*100)."</td>";
		$tab4.="<td align=right>".@number_format($t_kgsendiri/$t_trippks)."</td>";
		$tab4.="<td align=right>".@number_format($t_trippks)."</td>";
		$tab4.="<td align=right>".@number_format($t_km)."</td>";
		$tab4.="<td align=right>".@number_format($t_kgsendiri)."</td>";
		if($t_ttlrpsendiri!=0){
			$tab4.="<td align=right>".@number_format($t_ttlrpsendiri / @$t_kgsendiri)."</td>";
			$tab4.="<td align=right>".@number_format($t_ttlrpsendiri / @$t_km)."</td>";
		}else{
			$tab4.="<td align=right>0</td>";
			$tab4.="<td align=right>0</td>";
		}
		$tab4.="<td align=right>".@number_format($t_ttlrpsendiri)."</td>";
		$tab4.="<td align=right>".@number_format(100-($t_persensendiri*100))."</td>";
		$tab4.="<td align=right>".@number_format($t_hasilkerjaborongan)."</td>";
		$tab4.="<td align=right>".@number_format($t_rupiahborongan/$t_hasilkerjaborongan)."</td>";
		$tab4.="<td align=right>".@number_format($t_rupiahborongan)."</td>";
		
		$tab4.="<td align=right>".@number_format(($t_tonalong/$t_hasilkerjakg)*100)."</td>";
		$tab4.="<td align=right>".@number_format($t_tonalong)."</td>";
		$tab4.="<td align=right>".@number_format($t_rpalong/$t_tonalong)."</td>";
		$tab4.="<td align=right>".@number_format($t_rpalong)."</td>";
		
		$tab4.="<td align=right>".@number_format($t_hasilkerjakg/($t_KBL+$t_KHT+$t_KHL))."</td>";
		$tab4.="<td align=right>".@number_format($t_ttlkgbasis/($t_KBL+$t_KHT+$t_KHL))."</td>";
		$tab4.="<td align=right>".@number_format($t_KBL)."</td>";
		$tab4.="<td align=right>".@number_format($t_KHT)."</td>";
		$tab4.="<td align=right>".@number_format($t_KHL)."</td>";
		$tab4.="<td align=right>".@number_format($t_KBL+$t_KHT+$t_KHL)."</td>";
		$tab4.="<td align=right>".@number_format($t_ttlkgbasis)."</td>";
		$tab4.="<td align=right>".@number_format($t_upah)."</td>";
		$tab4.="<td align=right>".@number_format($t_upah/$t_ttlkgbasis,2)."</td>";
		$tab4.="<td align=right>".@number_format($t_kgpremi)."</td>";
		$tab4.="<td align=right>".@number_format($t_premi/$t_kgpremi)."</td>";
		$tab4.="<td align=right>".@number_format($t_premi)."</td>";
		$tab4.="<td align=right>".@number_format($t_upah+$t_premi)."</td>";
		$tab4.="<td align=right>".@number_format(($t_upah+$t_premi)/$t_kgsendiri)."</td>";
		$tab4.="<td align=right>".@number_format($t_upah+$t_premi+$t_rupiahborongan+$t_ttlrpsendiri)."</td>";
		$tab4.="<td align=right>".@number_format(($t_upah+$t_premi+$t_rupiahborongan+$t_ttlrpsendiri)/$t_hasilkerjakg)."</td>";
		$tab4.="</tr>";
        $tab4.="</table>";
		
		$tab4.="<hr>";
		
		$tab5.="<span><b>UMUM</b></span>";
		$tab5.="<table ".$where.">
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$tab5.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['jabatan'] . "</td>
				
				<td align=center rowspan=1 colspan=4>Tenaga Kerja</td>
				<td align=center rowspan=2 colspan=1>Upah</td>
				<td align=center rowspan=1 colspan=4>Lembur dan Premi</td>
				<td align=center rowspan=1 colspan=3>Material</td>
				<td align=center rowspan=2 colspan=1>Totah Rupiah</td>
			</tr>
			<tr>
				<td align=center>KBL</td>
				<td align=center>KHT</td>
				<td align=center>KHL</td>
				<td align=center>Total</td>
				<td align=center>Jam</td>
				<td align=center>Rp/Jam</td>
				<td align=center>Lembur</td>
				<td align=center>Premi</td>
				<td align=center width=150px>Nama</td>
				<td align=center width=50px>Sat</td>
				<td align=center width=100px>Jumlah</td>
				
			</tr>
			</thead>";
			$ttlkbl='';
			$ttlkht='';
			$ttlkhl='';
			$ttlupah='';
			$ttlpremi='';
			$ttljamlembur='';
			$ttlrplembur='';
			$gtrp='';

			$str = "select kodeorg,divisi,norkb,kodekegiatan,periode,sum(KBL) as KBL,sum(KHT) as KHT,sum(KHL) as KHL,sum(upah) as upah,sum(jamlembur) as jamlembur,sum(rplembur) as rplembur from ".$dbname.".kebun_rkbdt where norkb='".$notransaksi."' and tipetransaksi='UMUM' 
			and periode='".$periode."' and kodeorg='".$kodeorg."' group by kodekegiatan";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$no="";
			while ($bar = $res->fetch()) {
				$nmjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$bar['kodekegiatan']."'");
				$no+=1;
				$tab5.="<tr class=rowcontent>";
				$tab5.="<td valign=top align=center>" . $no . "</td>";
				$tab5.="<td valign=top>".$nmjab[$bar['kodekegiatan']]."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['KBL'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['KHT'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['KHL'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['KBL']+$bar['KHT']+$bar['KHL'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['upah'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['jamlembur'])."</td>";
				if($bar['jamlembur']!=0 && $bar['rplembur']!=0){
					$tab5.="<td valign=top align=right>".number_format($bar['rplembur']/$bar['jamlembur'])."</td>";					
				}else{
					$tab5.="<td valign=top align=right>0</td>";					
				}
				$tab5.="<td valign=top align=right>".number_format($bar['rplembur'])."</td>";
				$tab5.="<td valign=top align=right>".number_format($bar['premi'])."</td>";
				$tab5.="<td valign=top align=right colspan=3>";
					$strx = "select * from ".$dbname.".kebun_rkbmaterial where norkb='".$notransaksi."' and tipetransaksi='UMUM' and periode='".$periode."' and kodeorg='".$bar['kodeorg']."' and divisi='".$bar['divisi']."' and kodekegiatan='".$bar['kodekegiatan']."'"; 
					$jlh = fetchData($strx);
					$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
					$resx->setFetchMode(PDO::FETCH_ASSOC);
					$nox='';$ttlrpbahan='';
					if(count($jlh)>0){
						$tab5.="<table ".$where.">";
						$tab5.="<tbody>";
						while($barx=$resx->fetch()){
							$nox++;
							$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$barx['kodebarang']."'");
							$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$barx['kodebarang']."'");
							$tab5.="<tr class=rowcontent>";
							$tab5.="<td align=center width=10px>".$nox."</td>";
							if(strlen($optnmbrg[$barx['kodebarang']])>12){
								$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,12)." ...";
							}else{
								$namabarang="".substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])),0,12)."";
							}
							$tab5.="<td >".$namabarang."</td>";
							$tab5.="<td width=50px>".$nmsat[$barx['kodebarang']]."</td>";
							$tab5.="<td align=right width=100px>".number_format($barx['kwantitas'],2)."</td>";
							$ttlrpbahan+=$barx['jumlahrp'];
						}
						$tab5.="</tr>";
						$tab5.="</tbody>";
						$tab5.="</table>";					
					}
					
				$tab5.="</td>";
				$totalrp=$bar['upah']+$bar['premi']+$bar['rplembur'];
				$tab5.="<td valign=top align=right>".number_format($totalrp)."</td>";
				
				@$ttlkbl+=$bar['KBL'];
				@$ttlkht+=$bar['KHT'];
				@$ttlkhl+=$bar['KHL'];
				@$ttlupah+=$bar['upah'];
				@$ttlpremi+=$bar['premi'];
				@$ttljamlembur+=$bar['jamlembur'];
				@$ttlrplembur+=$bar['rplembur'];
				@$gtrp+=$totalrp;
				
			}		
			$tab5.="</tr>";
			$tab5.="<tr class=rowcontent>";
			$tab5.="<td colspan=2 bgcolor=cyan align=center><b>TOTAL</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlkht)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlkhl)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlkbl+$ttlkht+$ttlkhl)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlupah)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttljamlembur)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlrplembur/$ttljamlembur)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlrplembur)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($ttlpremi)."</b></td>";
			$tab5.="<td bgcolor=cyan align=right colspan=3></td>";
			$tab5.="<td bgcolor=cyan align=right><b>".@number_format($gtrp)."</b></td>";
			$tab5.="</tr>";
			$tab5.="</table>";
			
		
		$tab5.="<hr>";
		
		$tab6.="<span><b>SUPPORT</b></span>";
		$tab6.="<table ".$where.">";
			
		$tab6.="<thead><tr class=rowheader>";
			
		$rows="rowspan=3";	
		$tab6.="<td align=center ".$rows." width=20px>No</td>
			<td align=center ".$rows.">".$_SESSION['lang']['departemen'] . "</td>
			<td align=center ".$rows.">".$_SESSION['lang']['jabatan'] . "</td>
			<td align=center ".$rows.">Komponen Gaji</td>
			<td align=center ".$rows.">Keterangan</td>
			<td align=center colspan=12>Tipe Karyawan</td>
		</tr>
		<tr>
			<td align=center colspan=3>KBL</td>
			<td align=center colspan=3>KHT</td>
			<td align=center colspan=3>KHL</td>
			<td align=center colspan=3>Total</td>
			
		</tr>
		<tr>
			<td align=center>TK</td>
			<td align=center>HK</td>
			<td align=center>Rupiah</td>
			<td align=center>TK</td>
			<td align=center>HK</td>
			<td align=center>Rupiah</td>
			<td align=center>TK</td>
			<td align=center>HK</td>
			<td align=center>Rupiah</td>
			<td align=center>TK</td>
			<td align=center>HK</td>
			<td align=center>Rupiah</td>
			
		</tr>
		</thead>";
		$str = "select * from ".$dbname.".kebun_rkbsupport where norkb='".$notransaksi."' and tipetransaksi='SUPPORT' 
		and periode='".$periode."' and kodeorg='".$kodeorg."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$data=array();
		while($bar=$res->fetch()){
			$data[$bar['dept']][$bar['jabatan']]=$bar['tipekary'];
			@$tk[$bar['dept']][$bar['jabatan']][$bar['tipekary']]+=$bar['tk'];
			@$hk[$bar['dept']][$bar['jabatan']][$bar['tipekary']]+=$bar['hk'];
			@$rupiah[$bar['dept']][$bar['jabatan']][$bar['tipekary']]+=$bar['rupiah'];
			@$ketxxx[$bar['dept']][$bar['jabatan']]=$bar['keterangan'];
			$tpkary[$bar['tipekary']]=$bar['tipekary'];
		}
		if(count($data)>0){
			$no='';
			$optdept=makeOption($dbname,'sdm_5departemen','kode,nama');
			$optjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
			$optcompt=makeOption($dbname,'sdm_ho_component','id,name');
			foreach($data as $dept => $valjab){
				foreach($valjab as $jabatan => $tipekary){
					$no++;
					$tab6.="<tr class=rowcontent>";
					$tab6.="<td align=center>".$no."</td>";	
					$tab6.="<td>".$optdept[$dept]."</td>";	
					$tab6.="<td>".$optjab[$jabatan]."</td>";	
					$tab6.="<td>".$optcompt[$kompgaji]."</td>";	
					$tab6.="<td align=left width=200px>".$ketxxx[$dept][$jabatan][$kompgaji]."</td>";	
					$ttlrp=$ttlhk=$ttltk='';
					foreach($tpkary as $tipekary){
						$tab6.="<td width=34px align=right>".number_format($tk[$dept][$jabatan][$tipekary])."</td>";	
						$tab6.="<td width=43px align=right>".number_format($hk[$dept][$jabatan][$tipekary])."</td>";	
						$tab6.="<td width=62px align=right>".number_format($rupiah[$dept][$jabatan][$tipekary])."</td>";
						@$ttltk+=$tk[$dept][$jabatan][$tipekary];
						@$ttlhk+=$hk[$dept][$jabatan][$tipekary];
						@$ttlrp+=$rupiah[$dept][$jabatan][$tipekary];
						
						
						@$gtstttk[$tipekary]+=$tk[$dept][$jabatan][$tipekary];
						@$gtsttlhk[$tipekary]+=$hk[$dept][$jabatan][$tipekary];
						@$gtsttlrp[$tipekary]+=$rupiah[$dept][$jabatan][$tipekary];
						
					}
					$tab6.="<td width=34px align=right>".number_format($ttltk)."</td>";	
					$tab6.="<td width=44px align=right>".number_format($ttlhk)."</td>";	
					$tab6.="<td width=81px align=right>".number_format($ttlrp)."</td>";	
					$tab6.="</tr>";
				}
			}
			$tab6.="<tr class=rowcontent style=background-color:skyblue>";
			$tab6.="<td colspan=5 align=center>Total</td>";	
			foreach($tpkary as $tipekary){
				$tab6.="<td align=right>".number_format($gtstttk[$tipekary])."</td>";	
				$tab6.="<td align=right>".number_format($gtsttlhk[$tipekary])."</td>";	
				$tab6.="<td align=right>".number_format($gtsttlrp[$tipekary])."</td>";	
				
				@$gtttk+=$gtstttk[$tipekary];
				@$gtthk+=$gtsttlhk[$tipekary];
				@$gttrp+=$gtsttlrp[$tipekary];
			}
				$tab6.="<td align=right>".number_format($gtttk)."</td>";	
				$tab6.="<td align=right>".number_format($gtthk)."</td>";	
				$tab6.="<td align=right>".number_format($gttrp)."</td>";	
				
		}
		
		$tab6.="</tbody>";
		$tab6.="</table>";
		
		
		if($tipe=='html'){
			echo $tab.$tab2.$tab3.$tab4.$tab5.$tab6;
		}elseif($tipe=='pdf'){
			$stream = $tab.$tab2.$tab3.$tab4.$tab5.$tab6;
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('Legal', 'landscape');
			$dompdf->render();
			$dompdf->stream("RKB",array("Attachment"=>0));
		}else{
			$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
			
			$tempnm = explode("/",$_SERVER['PHP_SELF']);
			$nop = substr($tempnm[2],0,strripos($tempnm[2],'.')).".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("approval", $tab);
			$xls->addSheet("pemel", $tab2);
			$xls->addSheet("panen", $tab3);
			$xls->addSheet("angkut", $tab4);
			$xls->addSheet("umum", $tab5);
			$xls->addSheet("support", $tab6);
			$xls->headers($nop);
			echo $xls->buildFile(); 
		}
		
	break;
	
}

?>	