<?php
ini_set('display_errors',0);
error_reporting(0);

require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$aruskaspjd='121009';
$aruskasbbm='118001';
$rekeningbank  = checkPostGet('rekeningbank', '');
$tipe          = checkPostGet('tipe', '');
$method        = checkPostGet('method', '');
$nopdo         = checkPostGet('nopdo', '');
$kepada         = checkPostGet('kepada', '');
$notransaksi   = checkPostGet('notransaksi', '');
$numrow   = checkPostGet('numrow', '');
$nourut         = checkPostGet('nourut', '');
$unit          = checkPostGet('unit', '');
$kodeorght=$unit;
$per           = checkPostGet('per', '');
$bag           = checkPostGet('bag', '');
$namafile           = checkPostGet('namafile', '');
$explnopdo     =  explode('/', $nopdo);
//upah
$noupah        = checkPostGet('noupah', '');
$divisiupah    = checkPostGet('divisiupah', '');
$tkupah        = checkPostGet('tkupah', '');
$tglupah       =tanggalsystemn(checkPostGet('tglupah',''));
$orang         = checkPostGet('orang', '');
$comp          = checkPostGet('comp', '');
$hkawal        = checkPostGet('hkawal', '');
$hktengah      = checkPostGet('hktengah', '');
$upahtengah    = checkPostGet('upahtengah', '');
$hkakhir       = checkPostGet('hkakhir', '');
$upahakhir     = checkPostGet('upahakhir', '');
$upahawal      = checkPostGet('upahawal', '');
$nourutupah    = checkPostGet('nourutupah', '');
$noakunupah = checkPostGet('noakunupah', '');
$rekeningbankupah = checkPostGet('rekeningbankupah', '');
//tutup upah
//kas
$nopdo     = checkPostGet('nopdo', '');
$unit     = checkPostGet('unit', '');
$notrankas     = checkPostGet('notrankas', '');
$notransaksix       = checkPostGet('notransaksix', '');
$noakunkas        = checkPostGet('noakunkas', '');
$noakunkasx        = checkPostGet('noakunkasx', '');
$noakunkasx        = checkPostGet('noakunkasx', '');
$noaruskasx        = checkPostGet('noaruskasx', '');
$noakunbayarx      = checkPostGet('noakunbayarx', '');
$ketkasx     = checkPostGet('ketkasx', '');
$jumlahkasx        = checkPostGet('jumlahkasx', '');
$checkedx        = checkPostGet('checkedx', '');
$nourutkas         = checkPostGet('nourutkas', '');

//tutup kas
//hutang
$notranhutang  = checkPostGet('notranhutang', '');
$suphutang     = checkPostGet('suphutang', '');
$pohutang      = checkPostGet('pohutang', '');
$nilpohutang   = checkPostGet('nilpohutang', '');
$ppnhutang     = checkPostGet('ppnhutang', '');
$pphhutang     = checkPostGet('pphhutang', '');
$kashutang     = checkPostGet('kashutang', '');
$sisahutang    = checkPostGet('sisahutang', '');
$cekhutang     = checkPostGet('cekhutang', '');
$nouruthutang  = checkPostGet('nouruthutang', '');
$noakunhutang  = checkPostGet('noakunhutang', '');
$noakunkashutang = checkPostGet('noakunkashutang', '');
$rekeningbankhutang = checkPostGet('rekeningbankhutang', '');
//
//bapp
$notranbapp    = checkPostGet('notranbapp', '');
$divisibapp    = checkPostGet('divisibapp', '');    
$nobapp        = checkPostGet('nobapp', ''); 
$supbapp       = checkPostGet('supbapp', ''); 
$kegbapp       = checkPostGet('kegbapp', ''); 
$tglbapp       =tanggalsystemn(checkPostGet('tglbapp',''));
$satbapp       = checkPostGet('satbapp', ''); 
$fisbapp       = checkPostGet('fisbapp', ''); 
$rpsatbapp     = checkPostGet('rpsatbapp', ''); 
$nilbapp       = checkPostGet('nilbapp', ''); 
$kasbapp       = checkPostGet('kasbapp', ''); 
$sisabapp      = checkPostGet('sisabapp', ''); 
$cekbapp       = checkPostGet('cekbapp', ''); 
$nourutbapp    = checkPostGet('nourutbapp', ''); 
$noakunbapp    = checkPostGet('noakunbapp', ''); 
$noakunkasbapp = checkPostGet('noakunkasbapp', '');
$rekeningbankbapp = checkPostGet('rekeningbankbapp', '');
///
//spk
$divisispk     = checkPostGet('divisispk', '');
$notranspk     = checkPostGet('notranspk', '');
$nospk         = checkPostGet('nospk', '');
$kdsupspk      = checkPostGet('kdsupspk', '');
$nmsupspk      = checkPostGet('nmsupspk', '');
$kegspk        = checkPostGet('kegspk', '');
$tglspk1       =tanggalsystemn(checkPostGet('tglspk1',''));
$tglspk2       =tanggalsystemn(checkPostGet('tglspk2',''));
$blokspk       = checkPostGet('blokspk', '');
$satspk        = checkPostGet('satspk', '');
$fisikspk      = checkPostGet('fisikspk', '');
$rptotspk      = checkPostGet('rptotspk', '');
$hargaspk      = checkPostGet('hargaspk', '');
$nourutspk     = checkPostGet('nourutspk', '');
$textcarisupspk= checkPostGet('textcarisupspk', '');
//tutup spk
//pad
$nourutpad     = checkPostGet('nourutpad', '');
$notranpad     = checkPostGet('notranpad', '');
$akunpad       = checkPostGet('akunpad', '');
$ketpad        = checkPostGet('ketpad', '');
$satpad        = checkPostGet('satpad', '');
$fisikpad      = checkPostGet('fisikpad', '');
$rupsatpad     = checkPostGet('rupsatpad', '');
$totpad        = checkPostGet('totpad', '');
$noakunpad = checkPostGet('noakunpad', '');
$rekeningbankpad = checkPostGet('rekeningbankpad', '');
//tutuppad

##BEGIN BBM##
$notranbbm  = checkPostGet('notranbbm', '');
$notransaksibbm = checkPostGet('notransaksibbm', '');
$karyawanid = checkPostGet('karyawanid', '');
$jlhbbm = checkPostGet('jlhbbm', '');
$pembayaran = checkPostGet('pembayaran', '');
$cekbbm = checkPostGet('cekbbm', '');
$nourutbbm = checkPostGet('nourutbbm', '');
$currRowbbm = checkPostGet('currRowbbm', '');
$noakunbbm = checkPostGet('noakunbbm', '');
$rekeningbankbbm = checkPostGet('rekeningbankbbm', '');
##END BBM##

##BEGIN IO##
$notranio  = checkPostGet('notranio', '');
$notransaksiio = checkPostGet('notransaksiio', '');
$kodevhc = checkPostGet('kodevhc', '');
$jenisbiaya = checkPostGet('jenisbiaya', '');
$biaya = checkPostGet('biaya', '');
$cekio = checkPostGet('cekio', '');
$nourutio = checkPostGet('nourutio', '');
$noakunio = checkPostGet('noakunio', '');
$rekeningbankio = checkPostGet('rekeningbankio', '');
##END IO##

##BEGIN PJD##
$notranpjd  = checkPostGet('notranpjd', '');
$unitpjd = checkPostGet('unitpjd', '');
$totalpjd = checkPostGet('totalpjd', '');
$ketpjd = checkPostGet('ketpjd', '');
$noakunpjd = checkPostGet('noakunpjd', '');
$rekeningbankpjd = checkPostGet('rekeningbankpjd', '');
##END PJD##

//lainnya
$nourutlnn     = checkPostGet('nourutlnn', '');
$notranlnn     = checkPostGet('notranlnn', '');
$akunlnn       = checkPostGet('akunlnn', '');
$ketlnn        = checkPostGet('ketlnn', '');
$satlnn        = checkPostGet('satlnn', '');
$fisiklnn      = checkPostGet('fisiklnn', '');
$rupsatlnn     = checkPostGet('rupsatlnn', '');
$totlnn        = checkPostGet('totlnn', '');
$noakunlnn = checkPostGet('noakunlnn', '');
$rekeningbanklnn = checkPostGet('rekeningbanklnn', '');
//tutuplainnya

#              = income
$nourutincome  = checkPostGet('nourutincome', '');
$notranincome  = checkPostGet('notranincome', '');
$notranincome2 = checkPostGet('notranincome2', '');
$akunincome    = checkPostGet('akunincome', '');
$ketincome     = checkPostGet('ketincome', '');
$satincome     = checkPostGet('satincome', '');
$fisikincome   = checkPostGet('fisikincome', '');
$rupsatincome  = checkPostGet('rupsatincome', '');
$totincome     = checkPostGet('totincome', '');
$noakunincome = checkPostGet('noakunincome', '');
$rekeningbankincome = checkPostGet('rekeningbankincome', '');
$thnsch        = checkPostGet('thnsch', '');
$tiperekap     = checkPostGet('tiperekap', '');
$noakunpil    = checkPostGet('noakunpil', ''); 
$arrtipekar    =  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$arrcompgaji   =  makeOption($dbname, 'sdm_ho_component', 'id,name');
$arrnmorg      =  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$kept          =  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$arrnmakun     =  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$arrnmsupp     =  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$arrnmcust     =  makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$arrnmkeg      =  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$arrnmaruskas  =  makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas');
$arrnmbrg      =  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optket=makeOption($dbname,'keu_5keterangan','id_ket,keterangan');
$str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$per."' limit 1 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$tglakhirper=$bar['tanggalsampai'];
$tglakhir=  explode('-', $tglakhirper);
if($bag=='I')
{
$tglawalper=$per.'-01';
}
else
{
$tglawalper=$per.'-16';	
}
$tglawal=   explode('-', $tglawalper); 
$tglawalbesok=$tglawal[2]+1;
$perawal=$tglawal[0].'-'.$tglawal[1];
$thn=substr($per,0,4);
$bln=substr($per,5,2);
$perkemarin=periodelalu($per);
$opt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optakun2=$optakun=$optsat=$optblok=$optaruskas=$optaruskas2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".keu_5akun where level=5 
	  and (left(noakun,3) in ('621', '631', '126', '128') or left(noakun,1) in ('7', '8'))
	  order by noakun asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." ".$bar['namaakun']."</option>";
}
$str="select * from ".$dbname.".keu_5akun where level=5 
	  and left(noakun,1) in ('7', '8') order by noakun asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optakun2.="<option value='".$bar['noakun']."'>".$bar['noakun']." ".$bar['namaakun']."</option>";
}
$str="select * from ".$dbname.".keu_5akun where level=5 
	  and left(noakun,1) in ('7', '8') order by noakun asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optakun2.="<option value='".$bar['noakun']."'>".$bar['noakun']." ".$bar['namaakun']."</option>";
}
#= arus kas
$where='';
$optTipe=makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$unit."'");
if($optTipe[$unit]=='HOLDING'){
	$where.=" and pemilik_aruskas in ('GLOBAL','HOLDING') and status='1' and level='3'";
} else if($optTipe[$unit]=='KANWIL'){
	$where.=" and pemilik_aruskas in ('GLOBAL','KANWIL') and status='1' and level='3'";
}else{
	$where.=" and pemilik_aruskas in ('GLOBAL','UNIT') and status='1' and level='3' and tipetransaksi='K'";
}
$str = "SELECT * FROM ".$dbname.".keu_5aruskas where 1=1 ".$where." order by noaruskas asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
	if($bar['jenis_pengeluaran']=='V'){
		$optaruskas2.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";		
	}
}
$optaruskas2="<option value=''></option>";
$str = "SELECT * FROM ".$dbname.".keu_5aruskas where 1=1 and status='1' and level='3' and tipetransaksi='M' order by noaruskas asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optaruskas2.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
}
#= satuan
$str = "SELECT * FROM ".$dbname.".setup_satuan";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optsat.="<option value=".$bar['satuan'].">".$bar['satuan']."</option>";
}
switch ($method) {

    case'lockTombol':
    	$isiDt=explode("/",$nopdo);
		$kodeorght=$isiDt[2];
        #ambil induk organisasi
        $strht="select tipe,induk from ".$dbname.".organisasi where  kodeorganisasi='".$kodeorght."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$tipe=$barht['tipe'];

		echo $tipe;
	break;

	case'htmlexcelrekap':
	$tbmhanStr="parent.";
	if($_POST['dariRekap']==1){
		$tbmhanStr="";
	}
		$stream.="";
		if ($tiperekap != 'excel') {
			$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
		}
		$stream.="<b> REKAPUTULASI PERMINTAAN DANA OPERASIONAL </b>";
		$stream.="<br>";
		$stream.="<b> NO : ".$nopdo." </b>";
		$stream.="<br>";
		$stream.="<br>";
		$stream.="<br>";
		if($tiperekap=='excel')
		{
			$border=" border=1";
		}
		else
		{
			$border=" border=0";
		}
		$stream.="
                <table cellpading=1 cellspacing=1 ".$border." class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td align=center>".$_SESSION['lang']['nourut']."</td>    
                        <td align=center colspan=3>".$_SESSION['lang']['jenisbiaya']."</td>  
                        <td align=center>".$_SESSION['lang']['realisasi']." ".$_SESSION['lang']['blnlalu']."</td>  
                        <td align=center>".$_SESSION['lang']['realisasi']." ".$_SESSION['lang']['blnini']."</td> 
                        <td align=center>".$_SESSION['lang']['selisih']." ".$_SESSION['lang']['blnini']."</td>   
                    </tr>
                </thead>";

     	$arrnopdo=explode('/', $nopdo);
     	$bag=$arrnopdo[3];
        #ambil data HT
        $strht="select kodeorg from ".$dbname.".keu_pdoht where  nopdo='".$nopdo."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$kodeorght=$barht['kodeorg'];

        #ambil induk organisasi
        $strht="select tipe from ".$dbname.".organisasi where  kodeorganisasi='".$unit."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$tipe=$barht['tipe'];
		
		## INCOME
		################################

		$noakunincome=array();
        $jumincomereallalu=array();
        $jumincomeestini=array();
        $jumincomerealini=array();
        $selisihincome=array();
        $jumincomeestdepan=array();

		#= cash income realisasi bulan lalu
		// $prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
		// $str="select noaruskas as noakun, kodeorg, sum(jumlah) as rupiah from ".$dbname.".keu_kasbankdtht_vw where tipetransaksi='M' and tanggal like '".$prdlalu."%' and kodeorg='".$unit."' group by noaruskas having sum(jumlah)>0 ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunincome[$bar['noakun']]=$bar['noakun'];
		// 	@$jumincomereallalu[$bar['noakun']]=$bar['rupiah'];
		// 	@$totjumincomereallalu+=$bar['rupiah'];
		// }

        #= cash income estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("-1 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%INCOME%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%'";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunincome[$bar['noakun']]=$bar['noakun'];
			@$jumincomereallalu[$bar['noakun']]=$bar['rupiah'];
			@$totjumincomereallalu+=$bar['rupiah'];
		}

		#= cash income estimasi bulan lalu
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%INCOME%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%'";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunincome[$bar['noakun']]=$bar['noakun'];
			@$jumincomeestini[$bar['noakun']]+=$bar['rupiah'];
			@$totjumincomeestini+=$bar['rupiah'];
		}

		// #= cash income realisasi bulan ini
		// $str="select noaruskas as noakun, kodeorg, sum(jumlah) as rupiah from ".$dbname.".keu_kasbankdtht_vw where tipetransaksi='M' and tanggal like '".$per."%' and kodeorg='".$unit."' group by noaruskas having sum(jumlah)>0 ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunincome[$bar['noakun']]=$bar['noakun'];
		// 	@$jumincomerealini[$bar['noakun']]=$bar['rupiah'];
		// 	@$totjumincomerealini+=$bar['rupiah'];
		// }

		// #= cash income estimasi bulan depan
		// $str="select * from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%INCOME%'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunincome[$bar['noakun']]=$bar['noakun'];
		// 	@$jumincomeestdepan[$bar['noakun']]+=$bar['rupiah'];
		// 	@$totjumincomeestdepan+=$bar['rupiah'];
		// }

		@$totselisihincome=$totjumincomereallalu-$totjumincomeestini;
		$stream.="
			<tr class=rowcontent>
				<td><b>1</b></td>
				<td colspan=3><b>Cash Income</b></td>
				<td align=right><b>".@number_format($totjumincomereallalu)."</b></td>
				<td align=right><b>".@number_format($totjumincomerealini)."</b></td>
				<td align=right><b>".@number_format($totselisihincome)."</b></td>
			</tr>";	
		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunincome)){
			foreach($noakunincome as $akunkas){
				$selisihincome[$akunkas]=$jumincomereallalu[$akunkas]-$jumincomeestini[$akunkas];
				if ($jumincomereallalu[$akunkas]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$kodeorght."','".$akunkas."','M','".$prdlalu."')\"";
				}
				if ($jumincomeestini[$akunkas]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$kodeorght."','".$akunkas."','M','".$per."')\"";
				}

				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmaruskas[$akunkas]."</td>
					<td align=right  title='List File Kas'>".@number_format($jumincomereallalu[$akunkas])."</td>
					<td align=rightt >".@number_format($jumincomeestini[$akunkas])."</td>
					<td align=right>".@number_format($selisihincome[$akunkas])."</td>
				</tr>";			
			}
		}

		################################
		################################
		

		## KAS
		################################

		$noakunkas=array();
        $jumkasreallalu=array();
        $jumkasestini=array();
        $jumkasrealini=array();
        $selisihkas=array();
        $jumkasestdepan=array();
		
		#= cash kas realisasi bulan lalu
		$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%kas%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' and noakun not in ('".$aruskaspjd."','".$aruskasbbm."')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunkas[$bar['noaruskas']]=$bar['noaruskas'];
			@$jumkasreallalu[$bar['noaruskas']]=$bar['rupiah'];
			@$totjumkasreallalu+=$bar['rupiah'];
		}

		#= cash kas estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%kas%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' and noakun not in ('".$aruskaspjd."','".$aruskasbbm."')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunkas[$bar['noaruskas']]=$bar['noaruskas'];
			@$jumkasestini[$bar['noaruskas']]+=$bar['rupiah'];
			@$totjumkasestini+=$bar['rupiah'];
		}

		
		
		@$totselisihkas=$totjumkasreallalu-$totjumkasestini;
		$stream.="
			<tr class=rowcontent style='cursor:pointer' onclick=\"".$tbmhanStr."lihatDetail('".$nopdo."','KAS',event)\" title='Display File KAS'>
				<td><b>2</b></td>
				<td colspan=3><b>Cash Kas</b></td>
				<td align=right><b>".@number_format($totjumkasreallalu)."</b></td>
				<td align=right><b>".@number_format($totjumkasestini)."</b></td>
				<td align=right><b>".@number_format($totselisihkas)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunkas)){
			foreach($noakunkas as $akunkas){
				$selisihkas[$akunkas]=$jumkasestini[$akunkas]-$jumkasrealini[$akunkas];
				if ($jumkasreallalu[$akunkas]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$kodeorght."','".$akunkas."','K','".$prdlalu."')\"";
				}
				if ($jumkasrealini[$akunkas]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$kodeorght."','".$akunkas."','K','".$per."')\"";
				}

				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmaruskas[$akunkas]."</td>
					<td align=right >".@number_format($jumkasreallalu[$akunkas])."</td>
					<td align=right >".@number_format($jumkasestini[$akunkas])."</td>
					<td align=right>".@number_format($selisihkas[$akunkas])."</td>
				</tr>";			
			}
		}

		
		################################
		################################

		
		## BBM
		################################

		$noakunbbm=array();
        $jumbbmreallalu=array();
        $jumbbmestini=array();
        $jumbbmrealini=array();
        $selisihbbm=array();
        $jumbbmestdepan=array();
		
		#= cash bbm realisasi bulan lalu
		$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%BBM%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' and noakun='".$aruskasbbm."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunbbm[$bar['noakun']]=$bar['noakun'];
			@$jumbbmreallalu[$bar['noakun']]+=$bar['rupiah'];
			@$totjumbbmreallalu+=$bar['rupiah'];
		}

		#= cash bbm estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%BBM%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' and noakun='".$aruskasbbm."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunbbm[$bar['noakun']]=$bar['noakun'];
			@$jumbbmestini[$bar['noakun']]+=$bar['rupiah'];
			@$totjumbbmestini+=$bar['rupiah'];
		}

		// #= cash bbm realisasi bulan ini
		// $str="select noaruskas as noakun, kodeorg, sum(jumlah) as rupiah from ".$dbname.".keu_kasbankdtht_vw where tipetransaksi='K' and tanggal like '".$per."%' and kodeorg='".$unit."' and noaruskas='".$aruskasbbm."' group by noaruskas ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunbbm[$bar['noakun']]=$bar['noakun'];
		// 	@$jumbbmrealini[$bar['noakun']]=$bar['rupiah'];
		// 	@$totjumbbmrealini+=$bar['rupiah'];
		// }

		// #= cash bbm estimasi bulan depan
		// $str="select * from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%BBM%' and noakun='".$aruskasbbm."' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunbbm[$bar['noakun']]=$bar['noakun'];
		// 	@$jumbbmestdepan[$bar['noakun']]+=$bar['rupiah'];
		// 	@$totjumbbmestdepan+=$bar['rupiah'];
		// }
		
		@$totselisihbbm=$totjumbbmreallalu-$totjumbbmestini;
		$stream.="
			<tr class=rowcontent>
				<td><b>3</b></td>
				<td colspan=3><b>".$_SESSION['lang']['bbm']."</b></td>
				<td align=right>".@number_format($totjumbbmreallalu)."</td>
				<td align=right>".@number_format($totjumbbmestini)."</td>
				<td align=right><b>".@number_format($totselisihbbm)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunbbm)){
			foreach($noakunbbm as $akunbbm){
				$selisihbbm[$akunbbm]=$jumbbmreallalu[$akunbbm]-$jumbbmestini[$akunbbm];
				if ($jumbbmreallalu[$akunbbm]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$kodeorght."','".$akunbbm."','K','".$prdlalu."')\"";
				}
				if ($jumbbmrealini[$akunbbm]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$kodeorght."','".$akunbbm."','K','".$per."')\"";
				}

				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmaruskas[$akunbbm]."</td>
					<td align=right >".@number_format($jumbbmreallalu[$akunbbm])."</td>
					<td align=right >".@number_format($jumbbmestini[$akunbbm])."</td>
					<td align=right>".@number_format($selisihbbm[$akunbbm])."</td>
				</tr>";			
			}
		}
		
		################################
		################################

		
		## PJD
		################################

		$noakunpjd=array();
        $jumpjdreallalu=array();
        $jumpjdestini=array();
        $jumpjdrealini=array();
        $selisihpjd=array();
        $jumpjdestdepan=array();
		
		#= cash pjd realisasi bulan lalu
		$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%PJD%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' and noakun='".$aruskaspjd."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunpjd[$bar['noakun']]=$bar['noakun'];
			@$jumpjdreallalu[$bar['noakun']]+=$bar['rupiah'];
			@$totjumpjdreallalu+=$bar['rupiah'];
		}

		#= cash pjd estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%PJD%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' and noakun='".$aruskaspjd."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunpjd[$bar['noakun']]=$bar['noakun'];
			@$jumpjdestini[$bar['noakun']]+=$bar['rupiah'];
			@$totjumpjdestini+=$bar['rupiah'];
		}

		
		@$totselisihpjd=$totjumpjdreallalu-$totjumpjdrealini;
		$stream.="
			<tr class=rowcontent  style='cursor:pointer' onclick=\"".$tbmhanStr."lihatDetail('".$nopdo."','PJD',event)\"  title='Display File Perjalanan Dinas'>
				<td><b>4</b></td>
				<td colspan=3><b>".$_SESSION['lang']['perdin']."</b></td>
				<td align=right><b>".@number_format($totjumpjdreallalu)."</b></td>
				<td align=right><b>".@number_format($totjumpjdestini)."</b></td>
				<td align=right><b>".@number_format($totselisihpjd)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunpjd)){
			foreach($noakunpjd as $akunpjd){
				$selisihpjd[$akunpjd]=$jumpjdreallalu[$akunpjd]-$jumpjdestini[$akunpjd];
				if ($jumpjdreallalu[$akunpjd]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunpjd."','K','".$prdlalu."')\"";
				}
				if ($jumpjdrealini[$akunpjd]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunpjd."','K','".$per."')\"";
				}

				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmaruskas[$akunpjd]."</td>
					<td align=right >".@number_format($jumpjdreallalu[$akunpjd])."</td>
					<td align=right >".@number_format($jumpjdestini[$akunpjd])."</td>
					<td align=right>".@number_format($selisihpjd[$akunpjd])."</td>
				</tr>";			
			}
		}	

		################################
		################################


		## UPAH
		################################	

		$noakunupah=array();
        $jumupahreallalu=array();
        $jumupahestini=array();
        $jumupahrealini=array();
        $selisihupah=array();
        $jumupahestdepan=array();
		
		#= cash upah realisasi bulan lalu

		$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%UPAH%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$komponengajiupah[$bar['komponengaji']]=$bar['komponengaji'];
			@$jumupahreallalu[$bar['komponengaji']]+=$bar['rupiah'];
			@$totjumupahreallalu+=$bar['rupiah'];
		}

		#= cash upah estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%UPAH%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunupah[$bar['komponengaji']]=$bar['komponengaji'];
			@$jumupahestini[$bar['komponengaji']]+=$bar['rupiah'];
			@$totjumupahestini+=$bar['rupiah'];
		}

		// #= cash upah realisasi bulan ini
		// $str="select * from ".$dbname.".sdm_gaji where kodeorg='".$unit."' and periodegaji='".$per."'
		// 		and idkomponen in (select id from ".$dbname.".sdm_ho_component where plus=1)";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunupah[$bar['komponengaji']]=$bar['komponengaji'];
		// 	@$jumupahrealini[$bar['komponengaji']]+=$bar['rupiah'];
		// 	@$totjumupahrealini+=$bar['rupiah'];
		// }

		// #= cash upah estimasi bulan depan
		// $str="select * from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%UPAH%' and komponengaji in (select id from ".$dbname.".sdm_ho_component where plus=1) ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunupah[$bar['komponengaji']]=$bar['komponengaji'];
		// 	@$jumupahestdepan[$bar['komponengaji']]+=$bar['rupiah'];
		// 	@$totjumupahestdepan+=$bar['rupiah'];
		// }
		
		@$totselisihupah=$totjumupahreallalu-$totjumupahestini;
		$stream.="
			<tr class=rowcontent>
				<td><b>5</b></td>
				<td colspan=3><b>".$_SESSION['lang']['gaji']."</b></td>
				<td align=right>".@number_format($totjumupahreallalu)."</td>
				<td align=right>".@number_format($totjumupahestini)."</td>
				<td align=right><b>".@number_format($totselisihupah)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunupah)){
			foreach($noakunupah as $akunupah){
				$selisihupah[$akunupah]=$jumupahreallalu[$akunupah]-$jumupahestini[$akunupah];
				if ($jumupahreallalu[$akunupah]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunupah."','K','".$prdlalu."')\"";
				}
				if ($jumupahestini[$akunupah]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunupah."','K','".$per."')\"";
				}

				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrcompgaji[$akunupah]."</td>
					<td align=right >".@number_format($jumupahreallalu[$akunupah])."</td>
					<td align=right >".@number_format($jumupahestini[$akunupah])."</td>
					<td align=right>".@number_format($selisihupah[$akunupah])."</td>
				</tr>";			
			}
		}	

		// ##data gaji bulan lalu
		// $str="select * from ".$dbname.".sdm_gaji where kodeorg='".$unit."' and periodegaji='".$perkemarin."'
		// 		and idkomponen in (select id from ".$dbname.".sdm_ho_component where plus=1) ";			
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$komponengaji[$bar['idkomponen']]=$bar['idkomponen'];
		// 	@$gajilalu[$bar['idkomponen']]+=$bar['jumlah'];
		// 	@$totgajilalu+=$bar['jumlah'];
		// }
		// $str="select * from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%UPAH%'
		// 		and komponengaji in (select id from ".$dbname.".sdm_ho_component where plus=1) ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$komponengaji[$bar['komponengaji']]=$bar['komponengaji'];
		// 	@$gajies[$bar['komponengaji']]+=$bar['rupiah'];
		// 	@$totgajies+=$bar['rupiah'];
		// }
		// @$totselisihgaji=abs($totgajies-$totgajilalu);
		// $stream.="
		// 	<tr class=rowcontent>
		// 		<td><b>3</b></td>
		// 		<td colspan=3><b>".$_SESSION['lang']['gaji']." / ".$_SESSION['lang']['upah']."</b></td>
		// 		<td align=right><b>".@number_format($totgajilalu)."</b></td>
		// 		<td align=right><b>".@number_format($totgajies)."</b></td>
		// 		<td></td>
		// 		<td align=right><b>".@number_format($totselisihgaji)."</b></td>
		// 	</tr>";	
		// if(isset($komponengaji))
		// foreach($komponengaji as $kompgaji){
		// 	@$selisih[$kompgaji]=abs($gajies[$kompgaji]-$gajilalu[$kompgaji]);
		// 	$stream.="
		// 		<tr class=rowcontent>
		// 			<td></td>
		// 			<td></td>
		// 			<td></td>
		// 			<td>".$arrcompgaji[$kompgaji]."</td>
		// 			<td  align=right>".@number_format($gajilalu[$kompgaji])."</td>
		// 			<td  align=right>".@number_format($gajies[$kompgaji])."</td>
		// 			<td></td>
		// 			<td  align=right>".@number_format($selisih[$kompgaji])."</td>
		// 		</tr>";
		// }

		################################
		################################

		## HUTANG
		################################

		$noakunhutang=array();
        $jumhutangreallalu=array();
        $jumhutangestini=array();
        $jumhutangrealini=array();
        $selisihhutang=array();
        $jumhutangestdepan=array();
		$noakunsup=array();

		$str=" select tipe,noakun from ".$dbname.".log_5klsupplier where tipe not in ('KONTRAKTOR','TRANSPORTIR') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunsup[$bar['noakun']]=$bar['noakun'];
		}
		
		//if (count($noakunsup)!=0) {
			#= cash hutang realisasi bulan lalu
			$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
			//select a.noakun, noaruskas, a.kodeorg, sum(jumlah) as rupiah from keu_kasbankdt a left join keu_tagihanht b on a.keterangan1=b.noinvoice where a.tanggal like '2018-03%' and a.noakun in ('2110101','2110301','2110201') and tipeinvoice !='k' and a.kodeorg='TJHO' group by noaruskas
			$str="select sum(rupiah) as rupiah,kodesupplier from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%HUTANG%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' group by kodesupplier";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$noakunhutang[$bar['noakun']]=$bar['noakun'];
				@$jumhutangreallalu[$bar['noakun']]+=$bar['rupiah'];
				@$totjumhutangreallalu+=$bar['rupiah'];
			}
		//}
		
		#= cash hutang estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select sum(rupiah) as rupiah,kodesupplier from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%HUTANG%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' group by kodesupplier";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunhutang[$bar['kodesupplier']]=$bar['kodesupplier'];
			@$jumhutangestini[$bar['kodesupplier']]+=$bar['rupiah'];
			@$totjumhutangestini+=$bar['rupiah'];
		}

		// if (count($noakunsup)!=0) {
		// 	#= cash hutang realisasi bulan ini
		// 	$str="select a.kodesupplier as noakun, sum(jumlah) as rupiah from ".$dbname.".keu_kasbankdtht_vw a where tipetransaksi='K' and a.tanggal like '".$per."%' and a.kodeorg='".$unit."' and jumlah>0 and a.noakun in ('".implode("','",$noakunsup)."')  group by kodesupplier ";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($bar=$res->fetch()){
		// 		$noakunhutang[$bar['noakun']]=$bar['noakun'];
		// 		@$jumhutangrealini[$bar['noakun']]+=$bar['rupiah'];
		// 		@$totjumhutangrealini+=$bar['rupiah'];
		// 	}
		// }
		

		// #= cash hutang estimasi bulan depan
		// $str="select sum(rupiah) as rupiah,kodesupplier from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%HUTANG%' and notransaksi like '%".$unit."%' group by kodesupplier ";
		// //echo $str;
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunhutang[$bar['kodesupplier']]=$bar['kodesupplier'];
		// 	@$jumhutangestdepan[$bar['kodesupplier']]+=$bar['rupiah'];
		// 	@$totjumhutangestdepan+=$bar['rupiah'];
		// }
		
		@$totselisihhutang=$totjumhutangreallalu-$totjumhutangestini;
		$stream.="
			<tr class=rowcontent>
				<td><b>6</b></td>
				<td colspan=3><b>".$_SESSION['lang']['hutang']." (Supplier)</b></td>
				<td align=right><b>".@number_format($totjumhutangreallalu)."</b></td>
				<td align=right><b>".@number_format($totjumhutangestini)."</b></td>
				<td align=right><b>".@number_format($totselisihhutang)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";	
		if(!empty($noakunhutang)){
			foreach($noakunhutang as $akunhutang){
				$selisihhutang[$akunhutang]=$jumhutangreallalu[$akunhutang]-$jumhutangestini[$akunhutang];
				if ($jumhutangreallalu[$akunhutang]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunhutang."','K','".$prdlalu."')\"";
				}
				if ($jumhutangestini[$akunhutang]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunhutang."','K','".$per."')\"";
				}

				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmsupp[$akunhutang]."</td>
					<td align=right >".@number_format($jumhutangreallalu[$akunhutang])."</td>
					<td align=right >".@number_format($jumhutangestini[$akunhutang])."</td>
					<td align=right>".@number_format($selisihhutang[$akunhutang])."</td>
				</tr>";			
			}
		}		

		// #= hutang
		// $str="select * from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' 
		// 		and notransaksi like '%HUTANG%'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$kdsup[$bar['kodesupplier']]=$bar['kodesupplier'];
		// 	$nomorpo[$bar['nodocument']]=$bar['nodocument'];
		// 	$listnopo[$bar['kodesupplier']][$bar['nodocument']]=$bar['nodocument'];
		// 	@$rppo[$bar['kodesupplier']][$bar['nodocument']]+=$bar['rupiah'];
		// 	@$totpersup[$bar['kodesupplier']]+=$bar['rupiah'];
		// 	@$tothutang+=$bar['rupiah'];
		// }
		// $stream.="
		// 	<tr class=rowcontent>
		// 		<td><b>4</b></td>
		// 		<td colspan=3><b>".$_SESSION['lang']['hutang']." (Supplier)</b></td>
		// 		<td align=right></td>
		// 		<td align=right><b>".@number_format($tothutang)."</b></td>
		// 		<td align=right></td>
		// 		<td align=right><b>".@number_format($tothutang)."</b></td>
		// 	</tr>";	
		// if(count(@$kdsup)>0){	
		// 	foreach($kdsup as $sup)
		// 	{
		// 		$stream.="
		// 			<tr class=rowcontent>
		// 				<td></td>
		// 				<td></td>
		// 				<td>".$arrnmsupp[$sup]."</td>
		// 				<td></td>
		// 				<td></td>
		// 				<td align=right>".@number_format($totpersup[$sup])."</td>
		// 				<td></td>
		// 				<td align=right>".@number_format($totpersup[$sup])."</td>
		// 			</tr>";	
		// 		foreach($nomorpo as $nopo)
		// 		{
		// 			if(@$listnopo[$sup][$nopo]!='')
		// 			{
		// 				$stream.="
		// 				<tr class=rowcontent>
		// 					<td></td>
		// 					<td></td>
		// 					<td></td>
		// 					<td>".$nopo."</td>
		// 					<td></td>
		// 					<td align=right>".@number_format($rppo[$sup][$nopo])."</td>
		// 					<td></td>
		// 					<td align=right>".@number_format($rppo[$sup][$nopo])."</td>
		// 				</tr>";	
		// 			}
		// 		}
		// 	}
		// }

		################################
		################################


		## BAPP
		################################

		$noakunbapp=array();
        $jumbappreallalu=array();
        $jumbappestini=array();
        $jumbapprealini=array();
        $selisihbapp=array();
        $jumbappestdepan=array();
		$noakunsup=array();

		$str=" select tipe,noakun from ".$dbname.".log_5klsupplier where tipe in ('KONTRAKTOR','TRANSPORTIR') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunsup[$bar['noakun']]=$bar['noakun'];
		}
		
		//if (count($noakunsup)!=0) {
			#= cash bapp realisasi bulan lalu
			$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
			$str="select kodesupplier,sum(rupiah) as rupiah from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%BAPP%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' group by kodesupplier ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$noakunbapp[$bar['noakun']]=$bar['noakun'];
				@$jumbappreallalu[$bar['noakun']]+=$bar['rupiah'];
				@$totjumbappreallalu+=$bar['rupiah'];
			}
		//}
		

		#= cash bapp estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select kodesupplier,sum(rupiah) as rupiah from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%BAPP%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%' group by kodesupplier ";
		//echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunbapp[$bar['kodesupplier']]=$bar['kodesupplier'];
			@$jumbappestini[$bar['kodesupplier']]+=$bar['rupiah'];
			@$totjumbappestini+=$bar['rupiah'];
		}

		// if (count($noakunsup)!=0) {
		// 	#= cash bapp realisasi bulan ini
		// 	$str="select a.kodesupplier as noakun, sum(jumlah) as rupiah from ".$dbname.".keu_kasbankdtht_vw a where tipetransaksi='K' and a.tanggal like '".$per."%' and a.kodeorg='".$unit."' and jumlah>0 and a.noakun in ('".implode("','",$noakunsup)."') group by kodesupplier ";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($bar=$res->fetch()){
		// 		$noakunbapp[$bar['noakun']]=$bar['noakun'];
		// 		@$jumbapprealini[$bar['noakun']]=$bar['rupiah'];
		// 		@$totjumbapprealini+=$bar['rupiah'];
		// 	}
		// }
		
		// #= cash bapp estimasi bulan depan
		// $str="select kodesupplier,sum(rupiah) as rupiah from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%BAPP%' and notransaksi like '%".$unit."%' group by kodesupplier ";
		// //echo "\n".$str;
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunbapp[$bar['kodesupplier']]=$bar['kodesupplier'];
		// 	@$jumbappestdepan[$bar['kodesupplier']]+=$bar['rupiah'];
		// 	@$totjumbappestdepan+=$bar['rupiah'];
		// }
		
		@$totselisihbapp=$totjumbappreallalu-$totjumbappestini;
		$stream.="
			<tr class=rowcontent>
				<td><b>7</b></td>
				<td colspan=3><b>BAPP</b></td>
				<td align=right>".@number_format($totjumbappreallalu)."</td>
				<td align=right>".@number_format($totjumbappestini)."</td>
				<td align=right><b>".@number_format($totselisihbapp)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunbapp)){
			foreach($noakunbapp as $akunbapp){
				$selisihbapp[$akunbapp]=$jumbappreallalu[$akunbapp]-$jumbappestini[$akunbapp];
				if ($jumbappreallalu[$akunbapp]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunbapp."','K','".$prdlalu."')\"";
				}
				if ($jumbappestini[$akunbapp]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunbapp."','K','".$per."')\"";
				}

				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmsupp[$akunbapp]."</td>
					<td align=right >".@number_format($jumbappreallalu[$akunbapp])."</td>
					<td align=right >".@number_format($jumbappestini[$akunbapp])."</td>
					<td align=right>".@number_format($selisihbapp[$akunbapp])."</td>
				</tr>";			
			}
		}

		#= Ijin Operasional
		################################	

		$noakunio=array();
        $jumioreallalu=array();
        $jumioestini=array();
        $jumiorealini=array();
        $selisihio=array();
        $jumioestdepan=array();
		$noakunsup=array();
		$noakunsup2=array();

		#= noaruskas pdo 2 bulan lalu untuk realisasi bulan lalu
		$str="select distinct noakun from ".$dbname.".keu_pdodt where  nopdo like '".$prd2blnlalu."%' and notransaksi like '%IO%' and notransaksi like '%".$unit."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunsup[$bar['noakun']]=$bar['noakun'];
		}
		
		//if (count($noakunsup)!=0) {
			#= cash io realisasi bulan lalu
			$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
			$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%IO%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%'";
			// echo $str;
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$noakunio[$bar['noakun']]=$bar['noakun'];
				@$jumioreallalu[$bar['noakun']]=$bar['rupiah'];
				@$totjumioreallalu+=$bar['rupiah'];
			}
		//}
		

		#= cash io estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%IO%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunio[$bar['noakun']]=$bar['noakun'];
			$noakunsup2[$bar['noakun']]=$bar['noakun'];
			@$jumioestini[$bar['noakun']]+=$bar['rupiah'];
			@$totjumioestini+=$bar['rupiah'];
		}

		// if (count($noakunsup2)!=0) {
		// 	#= cash io realisasi bulan ini
		// 	$str="select noaruskas as noakun, kodeorg, sum(jumlah) as rupiah from ".$dbname.".keu_kasbankdtht_vw where tipetransaksi='K' and tanggal like '".$per."%' and kodeorg='".$unit."' and noaruskas in ('".implode("','",$noakunsup2)."') group by noaruskas ";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($bar=$res->fetch()){
		// 		$noakunio[$bar['noakun']]=$bar['noakun'];
		// 		@$jumiorealini[$bar['noakun']]=$bar['rupiah'];
		// 		@$totjumiorealini+=$bar['rupiah'];
		// 	}
		// }

		// #= cash io estimasi bulan depan
		// $str="select * from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%IO%'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunio[$bar['noakun']]=$bar['noakun'];
		// 	@$jumioestdepan[$bar['noakun']]+=$bar['rupiah'];
		// 	@$totjumioestdepan+=$bar['rupiah'];
		// }
		
		@$totselisihio=$totjumioreallalu-$totjumioestini;
		$stream.="
			<tr class=rowcontent style='cursor:pointer' onclick=\"".$tbmhanStr."lihatDetail('".$nopdo."','IO',event)\" title='Display File Ijin Operasional'>
				<td><b>8</b></td>
				<td colspan=3><b>Ijin Operasional</b></td>
				<td align=right>".@number_format($totjumioreallalu)."</td>
				<td align=right>".@number_format($totjumioestini)."</td>
				<td align=right><b>".@number_format($totselisihio)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunio)){
			foreach($noakunio as $akunio){
				$selisihio[$akunio]=$jumioreallalu[$akunio]-$jumioestini[$akunio];
				if ($jumioreallalu[$akunio]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunio."','K','".$prdlalu."')\"";
				}
				if ($jumioestini[$akunio]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunio."','K','".$per."')\"";
				}

				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmaruskas[$akunio]."</td>
					<td align=right >".@number_format($jumioreallalu[$akunio])."</td>
					<td align=right >".@number_format($jumioestini[$akunio])."</td>
					<td align=right>".@number_format($selisihio[$akunio])."</td>
				</tr>";			
			}
		}

		#=PAD
		################################

		$noakunpad=array();
        $jumpadreallalu=array();
        $jumpadestini=array();
        $jumpadrealini=array();
        $selisihpad=array();
        $jumpadestdepan=array();
		$noakunsup=array();
		$noakunsup2=array();

		#= noaruskas pdo 2 bulan lalu untuk realisasi bulan lalu
		$str="select distinct noakun from ".$dbname.".keu_pdodt where  nopdo like '".$prd2blnlalu."%' and notransaksi like '%PAD%' and notransaksi like '%".$unit."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunsup[$bar['noakun']]=$bar['noakun'];
		}
		
		//if (count($noakunsup)!=0) {
			#= cash pad realisasi bulan lalu
			$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
			$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%PAD%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%'";
			// echo $str;
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$noakunpad[$bar['noakun']]=$bar['noakun'];
				@$jumpadreallalu[$bar['noakun']]+=$bar['rupiah'];
				@$totjumpadreallalu+=$bar['rupiah'];
			}
		//}
		

		#= cash pad estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%PAD%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunpad[$bar['noakun']]=$bar['noakun'];
			$noakunsup2[$bar['noakun']]=$bar['noakun'];
			@$jumpadestini[$bar['noakun']]+=$bar['rupiah'];
			@$totjumpadestini+=$bar['rupiah'];
		}

		// if (count($noakunsup2)!=0) {
		// 	#= cash pad realisasi bulan ini
		// 	$str="select noaruskas as noakun, kodeorg, sum(jumlah) as rupiah from ".$dbname.".keu_kasbankdtht_vw where tipetransaksi='K' and tanggal like '".$per."%' and kodeorg='".$unit."' and noaruskas in ('".implode("','",$noakunsup2)."') group by noaruskas ";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($bar=$res->fetch()){
		// 		$noakunpad[$bar['noakun']]=$bar['noakun'];
		// 		@$jumpadrealini[$bar['noakun']]=$bar['rupiah'];
		// 		@$totjumpadrealini+=$bar['rupiah'];
		// 	}
		// }

		// #= cash pad estimasi bulan depan
		// $str="select * from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%PAD%'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunpad[$bar['noakun']]=$bar['noakun'];
		// 	@$jumpadestdepan[$bar['noakun']]+=$bar['rupiah'];
		// 	@$totjumpadestdepan+=$bar['rupiah'];
		// }
		
		@$totselisihpad=$totjumpadreallalu-$totjumpadestini;
		$stream.="
			<tr class=rowcontent  style='cursor:pointer' onclick=\"".$tbmhanStr."lihatDetail('".$nopdo."','PAD',event)\" title='Display File PAD'>
				<td><b>9</b></td>
				<td colspan=3><b>PAD</b></td>
				<td align=right>".@number_format($totjumpadreallalu)."</td>
				<td align=right>".@number_format($totjumpadestini)."</td>
				<td align=right><b>".@number_format($totselisihpad)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunpad)){
			foreach($noakunpad as $akunpad){
				$selisihpad[$akunpad]=$jumpadreallalu[$akunpad]-$jumpadestini[$akunpad];
				if ($jumpadreallalu[$akunpad]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunpad."','K','".$prdlalu."')\"";
				}
				if ($jumpadestini[$akunpad]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunpad."','K','".$per."')\"";
				}
				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmaruskas[$akunpad]."</td>
					<td align=right >".@number_format($jumpadreallalu[$akunpad])."</td>
					<td align=right >".@number_format($jumpadrealini[$akunpad])."</td>
					<td align=right>".@number_format($selisihpad[$akunpad])."</td>
				</tr>";			
			}
		}


		#=Lainnya
		################################

		$noakunlnn=array();
        $jumlnnreallalu=array();
        $jumlnnestini=array();
        $jumlnnrealini=array();
        $selisihlnn=array();
        $jumlnnestdepan=array();
		$noakunsup=array();
		$noakunsup2=array();

		#= noaruskas pdo 2 bulan lalu untuk realisasi bulan lalu
		$str="select distinct noakun from ".$dbname.".keu_pdodt where  nopdo like '".$prd2blnlalu."%' and notransaksi like '%LNN%' and notransaksi like '%".$unit."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunsup[$bar['noakun']]=$bar['noakun'];
		}
		
		//if (count($noakunsup)!=0) {
			#= cash lnn realisasi bulan lalu
			$prdlalu2= date("Y-m",strtotime("-1 Month",strtotime($per)));
			$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%LNN%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%'";
			// echo $str;
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$noakunlnn[$bar['noakun']]=$bar['noakun'];
				@$jumlnnreallalu[$bar['noakun']]=$bar['rupiah'];
				@$totjumlnnreallalu+=$bar['rupiah'];
			}
		//}
		

		#= cash lnn estimasi bulan ini
		$prdlalu2= date("Ym",strtotime("0 Month",strtotime($per)));
		$str="select * from ".$dbname.".keu_pdodt where  nopdo like '".$prdlalu2."%' and nopdo like '%".$bag."%' and notransaksi like '%LNN%' and notransaksi like '%".$unit."%' and notransaksi like '%".$bag."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noakunlnn[$bar['noakun']]=$bar['noakun'];
			$noakunsup2[$bar['noakun']]=$bar['noakun'];
			@$jumlnnestini[$bar['noakun']]+=$bar['rupiah'];
			@$totjumlnnestini+=$bar['rupiah'];
		}

		// if (count($noakunsup2)!=0) {
		// 	#= cash lnn realisasi bulan ini
		// 	$str="select noaruskas as noakun, kodeorg, sum(jumlah) as rupiah from ".$dbname.".keu_kasbankdtht_vw where tipetransaksi='K' and tanggal like '".$per."%' and kodeorg='".$unit."' and noaruskas in ('".implode("','",$noakunsup2)."') group by noaruskas ";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($bar=$res->fetch()){
		// 		$noakunlnn[$bar['noakun']]=$bar['noakun'];
		// 		@$jumlnnrealini[$bar['noakun']]=$bar['rupiah'];
		// 		@$totjumlnnrealini+=$bar['rupiah'];
		// 	}
		// }

		// #= cash lnn estimasi bulan depan
		// $str="select * from ".$dbname.".keu_pdodt where  nopdo='".$nopdo."' and notransaksi like '%LNN%'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noakunlnn[$bar['noakun']]=$bar['noakun'];
		// 	@$jumlnnestdepan[$bar['noakun']]+=$bar['rupiah'];
		// 	@$totjumlnnestdepan+=$bar['rupiah'];
		// }
		
		@$totselisihlnn=$totjumlnnreallalu-$totjumlnnestini;
		$stream.="
			<tr class=rowcontent  style='cursor:pointer' onclick=\"".$tbmhanStr."lihatDetail('".$nopdo."','LNN',event)\"  title='Display File Lain-Lain'>
				<td><b>10</b></td>
				<td colspan=3><b>Lainnya</b></td>
				<td align=right>".@number_format($totjumlnnreallalu)."</td>
				<td align=right>".@number_format($totjumlnnestini)."</td>
				<td align=right><b>".@number_format($totselisihlnn)."</b></td>
			</tr>";	

		$ondetaillalu="";
		$ondetailini="";
		if(!empty($noakunlnn)){
			foreach($noakunlnn as $akunlnn){
				$selisihlnn[$akunlnn]=$jumlnnreallalu[$akunlnn]-$jumlnnestini[$akunlnn];
				if ($jumlnnreallalu[$akunlnn]>0) {
					$ondetaillalu="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunlnn."','K','".$prdlalu."')\"";
				}
				if ($jumlnnestini[$akunlnn]>0) {
					$ondetailini="style='cursor:pointer' onclick=\"".$tbmhanStr."detailrealisasi('".$unit."','".$akunlnn."','K','".$per."')\"";
				}
				$stream.="
				<tr class=rowcontent>
					<td ></td>
					<td colspan=3>".$arrnmaruskas[$akunlnn]."</td>
					<td align=right >".@number_format($jumlnnreallalu[$akunlnn])."</td>
					<td align=right >".@number_format($jumlnnestini[$akunlnn])."</td>
					<td align=right>".@number_format($selisihlnn[$akunlnn])."</td>
				</tr>";			
			}
		}
		################################
		
		################################

		
		@$totalrealbl=$totjumincomereallalu+$totjumkasreallalu+$totjumbbmreallalu+$totjumpjdreallalu+$totjumupahreallalu+$totjumhutangreallalu+$totjumbappreallalu+$totjumioreallalu+$totjumpadreallalu+$totjumlnnreallalu;
		@$totalestbi=$totjumincomeestini+$totjumkasestini+$totjumbbmestini+$totjumpjdestini+$totjumupahestini+$totjumhutangestini+$totjumbappestini+$totjumioestini+$totjumpadestini+$totjumlnnestini;
		// @$totalrealbi=$totjumincomerealini+$totjumkasrealini+$totjumbbmrealini+$totjumpjdrealini+$totjumupahrealini+$totjumhutangrealini+$totjumbapprealini+$totjumiorealini+$totjumpadrealini+$totjumlnnrealini;
		@$totalselisih=$totselisihincome+$totselisihkas+$totselisihbbm+$totselisihpjd+$totselisihupah+$totselisihhutang+$totselisihbapp+$totselisihio+$totselisihpad+$totselisihlnn;
		// @$totalestdepan=$totjumincomeestdepan+$totjumkasestdepan+$totjumbbmestdepan+$totjumpjdestdepan+$totjumupahestdepan+$totjumhutangestdepan+$totjumbappestdepan+$totjumioestdepan+$totjumpadestdepan+$totjumlnnestdepan;
		$stream.="
			<tr class=rowcontent>
				<td colspan=4><b>GRAND TOTAL</b></td>
				<td align=right><b>".@number_format($totalrealbl)."</b></td>
				<td align=right><b>".@number_format($totalestbi)."</b></td>
				<td align=right><b>".@number_format($totalselisih)."</b></td>
			</tr></table>";	
		
		$countApprove = getCountApproval('PDO',$unit);
		$str=" select * from ".$dbname.".keu_pdoht where  nopdo='".$nopdo."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();

		$stream.= "<table border=0 cellspacing=1 class=sortable width=100%>
		<thead>
		<tr style='font-weight:bold'>
			<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
			for($i=1;$i<=$countApprove;$i++){
				$stream.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
			}	
		$stream.= "
		</tr>
		</thead>
		<tbody>";

		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$arrHsl=array("9"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak']);
		$stream.= "<tr class=rowcontent>
				<td>".$nmkar[$bar['updateby']]."<br>
					".waktunormal($bar['updatetime'])."</td>";
			for($i=1;$i<=$countApprove;$i++){
				$strx="select * from ".$dbname.".setup_approval where jenispersetujuan='PDO' and level='".$i."' and kodeunit='".$bar['unit']."'";
				$resx=fetchData($strx);
				$tipeapp = $resx[0]['tipe'];
				$departemenapp = $resx[0]['departemen'];
				$tipekaryawanapp = $resx[0]['tipekaryawan'];
				$jabatanapp = $resx[0]['jabatan'];
				
				$arrApp = detailApprove($i,$nopdo,'PDO');
				
				if($tipeapp=='1' && $arrApp['status']!=''){
					if($arrApp['status']!='1'){
						if($departemenapp!=''){
							$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							$arrApp['nama'] = $opttipe[$departemenapp];
						}
						
						if($tipekaryawanapp!=''){
							$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							$arrApp['nama'] = $opttipe[$tipekaryawanapp];
						}
						
						if($jabatanapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							$arrApp['nama'] = $opttipe[$jabatanapp];
						}
					}
				}
				
				if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
					$tngl='';
				}else{
					$tngl=tanggalnormal($arrApp['tanggal']);
				}
				
				if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
					$stream.= "<td>".$arrApp['nama']."
						<br />".$arrHsl[$arrApp['status']]."
						<br />".$tngl."
						<br />".$arrApp['komentar']."
					</td>";
				}else{
					$stream.= "<td>&nbsp;</td>";
				}
			}
			
		
		$stream.= "</tbody>
		</table><hr>";

		if($tiperekap=='html')
		{
			echo $stream;
		}
		else
		{
			$tglSkrg = date("Ymd");
			$nop_ = "excel_pdo" . $unit;
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
							window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
        }
		}
	break;

	case'deletepad':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranpad."' "
                        . " and nourut='".$nourutpad."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'updatepad':
        $str="update ".$dbname.".keu_pdodt set noakun='".$akunpad."',rincian='".$ketpad."',
            tanggal='".$tglawalper."',satuan='".$satpad."',fisik='".$fisikpad."',rupiah='".$totpad."',noakunkas='".$noakunpad."',rekeningbank='".$rekeningbankpad."'
            where nopdo='".$nopdo."' and notransaksi='".$notranpad."' and nourut='".$nourutpad."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;    

    case 'datajumlahpad':

    	if ($noakunpad=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunpad=='1110101') {
			if ($rekeningbankpad=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

    	$periodesblm= date("Y-m",strtotime("-2 Month",strtotime($per)));
    	
    	if ($akunpad=='11700') {
    		$str="SELECT notransaksi,sum(totalrp) as rupiah,jenis FROM ".$dbname.".`lgl_pembebasanlahan` where periode <'".$per."' and 
    			periode>'".$periodesblm."' and kodeorg='".$unit."' and notransaksi not in (select nodocument FROM ".$dbname.".`keu_pdodt`) 
    			and posting=1 group by jenis";
    	}else{
    		$str="select notransaksi,sum(rupiah) as rupiah,concat(notransaksi,' - ',tujuan) as jenis from ".$dbname.".lgl_bansos  where kodeorg='".$unit."' and left(tanggal,7)<='".$per."' and posting=1 and statuspersetujuan='1' and notransaksi not in (select nodocument FROM ".$dbname.".`keu_pdodt`) group by notransaksi";
    	}
    	//exit('Warning : '.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()) {
			#cek apakah HT sudah di-insert
			$str2="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='PAD' limit 1";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2=$res2->fetch();
				$cekht=$bar2['jumlah'];
			if($cekht<=0){
				$strht="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('".$nopdo."', '".$notranpad."', '".$unit."', '".$per."', 'PAD','".$_SESSION['standard']['userid']."')";
				try{
					$owlPDO->exec($strht);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}

			#cek nourut
	        $str1="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranpad."'"
	            . " order by nourut desc limit 1 ";
	        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1=$res1->fetch();
	        $nourutbaru=$bar1['nourut']+1;
	        $strdt="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
	                `tanggal`, `satuan`,`fisik`, `rupiah`, `nodocument`,`noakunkas`, `rekeningbank`) 
	                VALUES ('".$nopdo."', '".$notranpad."', '".$nourutbaru."', '".$akunpad."', '".str_replace('####',' ',$bar['jenis'])."',
	                '".$tglawalper."', '".$satpad."', '".$fisikpad."','".$bar['rupiah']."','".$bar['notransaksi']."','".$noakunpad."','".$rekeningbankpad."')";
			try{
				$owlPDO->exec($strdt);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}

    break;

    case'savepad':

    	if ($noakunpad=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunpad=='1110101') {
			if ($rekeningbankpad=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		#cek apakah HT sudah di-insert
		$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='PAD' limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$cekht=$bar['jumlah'];
		if($cekht<=0){
			$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('".$nopdo."', '".$notranpad."', '".$unit."', '".$per."', 'PAD','".$_SESSION['standard']['userid']."')";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}	
        #cek nourut
        $str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranpad."'"
            . " order by nourut desc limit 1 ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
            $nourutbaru=$bar['nourut']+1;
        $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                `tanggal`, `satuan`,`fisik`, `rupiah`,`noakunkas`, `rekeningbank`) 
                VALUES ('".$nopdo."', '".$notranpad."', '".$nourutbaru."', '".$akunpad."', '".$ketpad."',
                '".$tglawalper."', '".$satpad."', '".$fisikpad."','".$totpad."','".$noakunpad."','".$rekeningbankpad."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'listpad':
       $stream.="<fieldset><legend><b>List PAD</b></legend >
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td align=center width=30px>".$_SESSION['lang']['nourut']."</td>   
                        <td align=center >".$_SESSION['lang']['noakun']."</td>        
                        <td align=center >".$_SESSION['lang']['noaruskas']."</td>        
                        <td align=center >".$_SESSION['lang']['keterangan']."</td>    
                        <td align=center width=40px hidden>".$_SESSION['lang']['satuan']."</td>
                        <td align=center width=50px hidden>".$_SESSION['lang']['kuantitas']."</td>
                        <td align=center width=60px hidden>".$_SESSION['lang']['rupiahsatuan']."</td>
                        <td align=center width=90px>".$_SESSION['lang']['rupiahsatuan']."</td>
                        <td align=center width=30px>".$_SESSION['lang']['action']."</td>
                    </tr>
                </thead>";
        //$notrankas=$explnopdo[0].'/'.$explnopdo[2].'/KAS/001';
        $str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%PAD%' ";
		//$str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranpad."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $stream.="<tr class=rowcontent>
                        <td align=center>".$no."</td>  
                        <td align=center>".@$arrnmakun[$bar['noakunkas']]."</td>  
                        <td align=left>".$arrnmaruskas[$bar['noakun']]."</td>        
                        <td align=left>".$bar['rincian']."</td>  
                        <td align=center hidden>".$bar['satuan']."</td>
                        <td align=right hidden>".@number_format($bar['fisik'])."</td>
                        <td align=right hidden>".@number_format($bar['rupiah']/$bar['fisik'])."</td>
                        <td align=right>".@number_format($bar['rupiah'])."</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                                onclick=\"editpad('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."',
                                    '".$bar['noakun']."','".$bar['rincian']."','".$bar['satuan']."','".$bar['fisik']."',
                                    '".$bar['rupiah']."','".$bar['noakunkas']."','".$bar['rekeningbank']."');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                                onclick=\"deletepad('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."');\">
                        </td>   
                </tr>";    
        }
        echo $stream;
    break;
	case'detailpad':
    $notranpad=$explnopdo[0].'/'.$explnopdo[2].'/PAD'.'/'.$explnopdo[3].'/001';

	$optaruskaspad="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "SELECT noaruskas,nama_aruskas FROM ".$dbname.".keu_5aruskas where 1=1 and noaruskas like '1270%' or noaruskas ='129009	' order by noaruskas asc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
	    $optaruskaspad.="<option value='".$bar['noaruskas']."'>".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
	}

    $optrek=$optkas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $str="select noakun,namaakun from ".$dbname.".keu_5akun where noakun in ('1112101','1112102','1110101')";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar=$res->fetch()) {
    	$optkas.="<option value='".$bar['noakun']."'>".$bar['namaakun']."</option>";
	}

	$str = "select * from ".$dbname.".keu_5akunbank";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
	    $wheredz =" kodebank='".$bar['namabank']."'";
	    $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
	    $optrek.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
	}

        $stream.="<fieldset><legend><b>Form Input</b></legend >";
		$stream.="
            ".$_SESSION['lang']['notransaksi']." : <input type=text id=notranpad disabled value='".$notranpad."' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext>
            ".$_SESSION['lang']['noakun']." : <select onchange='getrekeningpad()' id=noakunpad style=\"width:150px;\">".$optkas."</select>
            ".$_SESSION['lang']['rekening']." : <select id=rekeningbankpad style=\"width:150px;\">".$optrek."</select><hr>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td hidden>nourutdb</td>
                        <td align=center>".$_SESSION['lang']['aruskas']."</td>    
                        <td align=center>".$_SESSION['lang']['keterangan']."</td>    
                        <td align=center width=40px hidden>".$_SESSION['lang']['satuan']."</td>
                        <td align=center width=50px hidden>".$_SESSION['lang']['kuantitas']."</td>
                        <td align=center width=60px hidden>".$_SESSION['lang']['rupiahsatuan']."</td>
                        <td align=center>".$_SESSION['lang']['total']."</td>
                        <td align=center width=30px>".$_SESSION['lang']['action']."
                            </td>
                    </tr>
                </thead>";
        $stream.="    
                <tr class=rowcontent>
                    <td align=left hidden><input type=text id=nourutpad onkeypress=\"return tanpa_kutip(event)\" class=myinputtext></td>    
                    <td align=left>
						<select id=akunpad  style=width:250px onchange='datajumlahpad()' >'".$optaruskaspad."'</select>
						<img onclick=\"z.elSearch('akunpad',event)\" class=resicon src=images/onebit_02.png style=position:relative;top:5px>
					</td>    
                    <td align=left><input type=text id=ketpad class=myinputtext ></td>    
                    <td align=left hidden><select id=satpad>'".$optsat."'</select></td>
                    <td align=right hidden><input type=text id=fisikpad onkeyup=totalpad() onkeypress='return angka_doang(event)' class=myinputtextnumber   style=width:50px ></td>
                    <td align=left hidden><input type=text id=rupsatpad onkeyup=totalpad() onkeypress='return angka_doang(event)' class=myinputtextnumber   style=width:90px ></td>
                    <td align=center ><input type=text id=totpad onkeypress='return angka_doang(event)' class=myinputtextnumber style=width:150px ></td>
                    <td align=center width=30px>
						<img title=".$_SESSION['lang']['save']." class='zImgBtn' onclick='savepad()' src='images/save.png'>
						</td>
               </tr>
               <input type=hidden id=methodpad value='savepad'>";
        $stream.="</table></fieldset>";
		echo $stream;
    break;
	case'updatespk':
		//$rptotspk=$fisikspk*$hargaspk;
		$str="update ".$dbname.".keu_pdodt set nodocument='".$nospk."',kodesupplier='".$kdsupspk."',kegiatan='".$kegspk."',
				noakun='".substr($kegspk,0,7)."',rincian='".$arrnmkeg[$kegspk]."',tglmulai='".$tglspk1."',tglsampai='".$tglspk2."',
				kodeblok='".$blokspk."',satuan='".$satspk."',fisik='".$fisikspk."',rupiah='".$rptotspk."',divisi='".$divisispk."'
				where nopdo='".$nopdo."' and notransaksi='".$notranspk."' and nourut='".$nourutspk."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	case'deletespk':	 
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranspk."' "
                        . " and nourut='".$nourutspk."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    case'listspk':
		 $stream.="<fieldset style='float:left;'><legend><b>List Data SPK</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
                    <thead>
                        <tr>
                            <td align=center width=30px >".$_SESSION['lang']['nourut']."</td> 
							<td align=center width=150px >".$_SESSION['lang']['nospk']."</td>
							<td align=center width=280px >".$_SESSION['lang']['kegiatan']."</td>
                            <td align=center width=90px >".$_SESSION['lang']['blok']."</td>
                            <td align=center width=50px >".$_SESSION['lang']['satuan']."</td> 
                            <td align=center width=50px >".$_SESSION['lang']['kuantitas']."</td>    
                            <td align=center width=80px >".$_SESSION['lang']['harga']."</td>    
							<td align=center width=100px >".$_SESSION['lang']['rupiah']."</td>    
							<td align=center width=50px >".$_SESSION['lang']['action']."</td>   
                        </tr>
                    </thead>";
		$str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%SPK%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
			$harga=$bar['rupiah']/$bar['fisik'];
            $stream.="<tr class=rowcontent>";
            $stream.="<td align=center >".$no."</td>";
			$stream.="<td align=left >".$bar['nodocument']."</td>";
			$stream.="<td align=left >".$arrnmkeg[$bar['kegiatan']]."</td>";
            $stream.="<td align=center >".$bar['kodeblok']."</td>";
            $stream.="<td align=center >".$bar['satuan']."</td>";
            $stream.="<td align=right >".@number_format($bar['fisik'],2)."</td>";
            $stream.="<td align=right >".@number_format($harga)."</td>";
			$stream.="<td align=right >".@number_format($bar['rupiah'])."</td>";
			$stream.="<td align=center >
						<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                                 onclick=\"editspk('".$bar['divisi']."','".$bar['notransaksi']."','".$bar['nodocument']."',
								 '".$bar['kodesupplier']."','".$arrnmsupp[$bar['kodesupplier']]."','".$bar['kegiatan']."',
								 '".tanggalnormal($bar['tglmulai'])."','".tanggalnormal($bar['tglsampai'])."','".$bar['kodeblok']."','".$bar['satuan']."',
								 '".$bar['fisik']."','".$harga."','".$bar['rupiah']."','".$bar['nourut']."');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                                 onclick=\"deletespk('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."');\">
					</td>";
            $stream.="</tr>";
        }
		echo $stream;
    break;
    case'savespk':
			#cek apakah sudah di-input
			#parameter : blok,sup,keg,nopdo,notran,nospk
			$str="select count(*) as jumlah from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranspk."' 
				and kodeblok='".$blokspk."' and kodesupplier='".$kdsupspk."' and kegiatan='".$kegspk."' and nodocument='".$nospk."'
				order by nourut desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$cekdt=$bar['jumlah'];	
			if($cekdt>0)
			{
				exit("Warning : Data sudah pernah di-input");
			}				
			#cek apakah HT sudah di-insert
			$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."'
					and notransaksi='".$notranspk."' and tipepdo='SPK' limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$cekht=$bar['jumlah'];
			if($cekht<=0)
			{
				$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('".$nopdo."', '".$notranspk."', '".$unit."', '".$per."', 'SPK','".$_SESSION['standard']['userid']."')";
				try{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}	
			$str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranspk."'"
                . " order by nourut desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
                $nourutbaru=$bar['nourut']+1;
			//$rptotspk=$fisikspk*$hargaspk;
            $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`,`kegiatan`, `rincian`,
                    `nodocument`,`tglmulai`,`tglsampai`,`divisi`,`satuan`,`fisik`,
                    `rupiah`,`kodeblok`,`kodesupplier`) 
                    VALUES ('".$nopdo."', '".$notranspk."', '".$nourutbaru."','".substr($kegspk,0,7)."', '".$kegspk."', '".$arrnmkeg[$kegspk]."',
                    '".$nospk."','".$tglspk1."','".$tglspk2."','".$divisispk."','".$satspk."','".$fisikspk."',
                    '".$rptotspk."','".$blokspk."','".$kdsupspk."')";    
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
    break;    
    case'carisupspklist':
        $stream="";
        $stream.="<fieldset style='float:left;'><legend><b>List Data SPK</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
                    <thead>
                        <tr>
                            <td align=center width=30px >".$_SESSION['lang']['nourut']."</td>    
                            <td align=center width=100px >".$_SESSION['lang']['kode']."</td>
                            <td align=center width=100px >".$_SESSION['lang']['namasupplier']."</td> 
                            <td align=center width=50px >".$_SESSION['lang']['kota']."</td>    
                            <td align=center width=100px >".$_SESSION['lang']['alamat']."</td>    
                        </tr>
                    </thead>";
        $str=" select * from ".$dbname.".log_5supplier where left(kodekelompok,1)='K' and status=1 and namasupplier like '%".$textcarisupspk."%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click It' onclick=\"movesupspk('".$bar['supplierid']."','".$bar['namasupplier']."');\">";
            $stream.="<td>".$no."</td>";
            $stream.="<td>".$bar['supplierid']."</td>";
            $stream.="<td>".$bar['namasupplier']."</td>";
            $stream.="<td>".$bar['kota']."</td>";
            $stream.="<td>".$bar['alamat']."</td>";
            $stream.="</tr>";
        }
        echo $stream;
    break;    
    case'getbloknotranspk':
        $thn=substr($per,0,4);
        $per=  str_replace('-', '', $per);
        if($notranspk==''){
            $str=" select notransaksi from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%SPK%'"
                    . " and divisi='".$divisispk."'  "
                    . " order by notransaksi desc limit 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
                $nolama=$bar['notransaksi'];
            if($nolama==''){
				$str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%SPK%' "
                        . " order by notransaksi desc limit 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
                    $notran=$bar['notransaksi'];
                $num=  explode('/', $notran);
                $num=@$num[3]+1;
                if($num<10)
                    $num='00'.$num;   
                else if($num<100)
                   $num='0'.$num;
                else
                   $num=$num;
                $notranspkbaru=$per.'/'.$unit.'/SPK/'.$num;
            }
            else{
                $notranspkbaru=$nolama;
            }
        }
        else
        {
			$str=" select notransaksi from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%SPK%'"
                    . " and divisi='".$divisispk."'  "
                    . " order by notransaksi desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
                $nolama=$bar['notransaksi'];
			if($nolama=='')
            {
				$str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%SPK%' "
                        . " order by notransaksi desc limit 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
                    $notran=$bar['notransaksi'];
                $num=  explode('/', $notran);
                $num=@$num[3]+1;
                if($num<10)
                    $num='00'.$num;   
                else if($num<100)
                   $num='0'.$num;
                else
                   $num=$num;
                $notranspkbaru=$per.'/'.$unit.'/SPK/'.$num;
			}
			else{
				 $notranspkbaru=$nolama;
			}
        }
        ##bentuk blok
        $str="select * from ".$dbname.".organisasi where induk = '".$divisispk."'  order by kodeorganisasi asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($blokspk==$bar['kodeorganisasi']){
				$select="selected=selected";
			}
            else{
				$select="";
			}
            $optblok.="<option ".$select." value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
        }
        echo $notranspkbaru.'####'.$optblok;
    break;    
    case'deletebapp':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and nodocument='".$notranbapp."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'savebapp':

    	if ($noakunkasbapp=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunkasbapp=='1110101') {
			if ($rekeningbankbapp=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		if($notranbapp==''){
			exit("Warning:Data belum lengkap, silahkan proses ulang");
		}
        if($cekbapp==1){
			#cek apakah HT sudah di-insert
			$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' 
					and notransaksi='".$notranbapp."' and tipepdo='BAPP' limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$cekht=$bar['jumlah'];
			if($cekht<=0)
			{
				$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('".$nopdo."', '".$notranbapp."', '".$unit."', '".$per."', 'BAPP','".$_SESSION['standard']['userid']."')";
				try{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}	
            ##delete 1st
            $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbapp."' "
                        . " and nodocument='".$nobapp."' and divisi='".$divisibapp."' ";
            try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
            $str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbapp."'"
                . " order by nourut desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
            $nourutbaru=$bar['nourut']+1;
             
            $sCek="select left(kodekegiatan,7) as noakun,sum(jumlahrealisasi) as nilai from ".$dbname.".log_baspk where notransaksi='".$nobapp."' group by left(kodekegiatan,7)";
			$rCek=fetchData($sCek);
			if(count($rCek)!=0){
				$rData=fetchData($sCek);
				foreach ($rData as $key => $val) {
					$optArusKas=makeOption($dbname,"keu_5aruskas_detail","noakun,noaruskas","noakun='".$val['noakun']."'");					
					$dtArus[$optArusKas[$val['noakun']]]=$val['nilai'];
				}
			}else{
				$sCek="select noaruskas,sum(nilai) as nilai  from ".$dbname.".keu_tagihandt where noinvoice='".$nobapp."' and left(noakun,3) not in ('117','213') group by noaruskas";
				$rData=fetchData($sCek);
				
				if(count($rData)!=0){
					foreach ($rData as $key => $val) {
						//exit('warning'.($val['nilai']+$isiPPn)."-".$pengurangDt);
						$dtArus[$val['noaruskas']]=($sisabapp/count($rData));
					}	
				}
			}
			$nourutbaru=$bar['nourut']+1;
            foreach ($dtArus as $key => $val){
	            $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`,
	                    `nodocument`,`tanggal`,`divisi`,`rupiah`,`kodesupplier`,`rincian`,`noakunkas`,`rekeningbank`) 
	                    VALUES ('".$nopdo."', '".$notranbapp."', '".$nourutbaru."', '".$key."',
	                    '".$nobapp."','".$tglbapp."','".$divisibapp."','".$val."','".$supbapp."','".$arrnmsupp[$supbapp]."','".$noakunkasbapp."','".$rekeningbankbapp."')";    
	            try{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
				$nourutbaru+=1;
			}
        }
        else
        {
			$str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbapp."' "
                        . " and nodocument='".$nobapp."' and divisi='".$divisibapp."' ";
            try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
        }
    break; 
    case'listbapp':

    	#ambil data HT
        $strht="select kodeorg from ".$dbname.".keu_pdoht where  nopdo='".$nopdo."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$kodeorght=$barht['kodeorg'];

        #ambil induk organisasi
        $strht="select tipe from ".$dbname.".organisasi where  kodeorganisasi='".$kodeorght."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$tipe=$barht['tipe'];

		if ($barht['tipe']!='HOLDING') {
			continue;
		}

       $stream.="<fieldset><legend><b>List Data BAPP</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
                    <thead>
                        <tr>
                            <td align=center>".$_SESSION['lang']['nourut']."</td>    
                            <td align=center>".$_SESSION['lang']['noakun']."</td>    
                            <td align=center>".$_SESSION['lang']['kontraktor']."</td> 
                            <td align=center>".$_SESSION['lang']['noinvoice']."</td>							
							<td align=center>".$_SESSION['lang']['tanggal']." BAPP</td>
                            <td align=center>".$_SESSION['lang']['rupiah']."</td>";
		//$stream.="<td align=center width=100px >".$_SESSION['lang']['terbayar']."</td><td align=center width=100px >".$_SESSION['lang']['sisa']."</td>";
        $stream.="<td align=center colspan=2 >".$_SESSION['lang']['action']."</td>
                        </tr>
                    </thead>";
        $str=" select sum(rupiah) as rupiah,nodocument,tanggal,nodocument,kodesupplier,noakunkas,rekeningbank from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%BAPP%' group by nodocument ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $nobaspk[$bar['nodocument']]=$bar['nodocument'];
            $tgl[$bar['nodocument']]=$bar['tanggal'];
            $sisa[$bar['nodocument']]=$bar['rupiah']; 
            //$nourut[$bar['nodocument']]=$bar['nourut']; 
            $notran[$bar['nodocument']]=$bar['notransaksi'];  
            $nil[$bar['nodocument']]=$bar['rupiahreal']; 
			$divisi[$bar['nodocument']]=$bar['divisi']; 
			$sup[$bar['nodocument']]=$bar['kodesupplier']; 
			$noakunkas[$bar['nodocument']]=$bar['noakunkas']; 
			$rekeningbank[$bar['nodocument']]=$bar['rekeningbank']; 
        }
		if(!empty($nobaspk)){
			foreach($nobaspk as $noba){
				$kas[$noba]=abs($sisa[$noba]-$nil[$noba]);
				$no+=1;    
				$stream.="<tr class=rowcontent id=row".$no.">";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td align=left>".$arrnmakun[$noakunkas[$noba]]."</td>";
				$stream.="<td align=left>".$arrnmsupp[$sup[$noba]]."</td>";
				$stream.="<td align=left>".$noba."</td>";
				$stream.="<td align=center>".tanggalnormal($tgl[$noba])."</td>";
				//$stream.="<td align=right>".@number_format($nil[$noba])."</td>";
				$stream.="<td align=right>".@number_format($sisa[$noba])."</td>";
				//$stream.="<td align=right>".@number_format($sisa[$noba])."</td>";
				$stream.="<td align=center>
						<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
									 onclick=\"editbapp('".$notran[$noba]."','".$divisi[$noba]."','".$noakunkas[$noba]."','".$rekeningbank[$noba]."');\">
								<img src=images/application/application_delete.png class=zImgBtn title='Delete' 
									 onclick=\"deletebapp('".$nopdo."','".$noba."','');\">
						</td>";
				$stream.="</tr>";
			}
        }
	
			echo $stream;
	
    break;
    case'detailbapp':

    	#ambil data HT
        $strht="select kodeorg from ".$dbname.".keu_pdoht where  nopdo='".$nopdo."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$kodeorght=$barht['kodeorg'];

        #ambil induk organisasi
        $strht="select tipe,induk from ".$dbname.".organisasi where  kodeorganisasi='".$unit."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$tipe=$barht['tipe'];
		$induk=$barht['induk'];

		if ($barht['tipe']!='HOLDING') {
			continue;
		}

        $stream="";
        $stream.="<fieldset><legend><b>Detail BAPP</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                    <thead>
                        <tr>
                            <td align=center width=30px >".$_SESSION['lang']['nourut']."</td>    
                            <td align=center width=170px >".$_SESSION['lang']['noinvoice']."</td>							
                            <td align=center width=170px >".$_SESSION['lang']['kontraktor']."</td> 
							<td align=center width=80px >".$_SESSION['lang']['tanggal']." BAPP</td>
                            <td align=center width=100px >".$_SESSION['lang']['rupiah']."</td>
							<td align=center width=100px >".$_SESSION['lang']['terbayar']."</td>
							<td align=center width=100px >".$_SESSION['lang']['sisa']."</td>
                            <td align=center width=50px >".$_SESSION['lang']['action']." 
							<br><input type=checkbox id=cekallbapp onclick=cekallbapp()>
                            </td>
                        </tr>
                    </thead>
					<tbody id=contentdetailbapp>";
		#berdasarkan log baspk
		$noinvoice2=array();
		$sBapsk="select sum(a.jumlahrealisasi) as rphutang,a.notransaksi,b.koderekanan as kodesupplier,a.tanggal  from ".$dbname.".log_baspk a 
		         left join ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi 
		         where left(a.tanggal,7)<='".$per."' and left(b.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$induk."') and a.posting=1
		         group by a.notransaksi";
		 //echo $sBapsk;
		 $rBaspk=fetchData($sBapsk);
		 foreach ($rBaspk as $key => $val) {
		 	// $sCek="select * from ".$dbname.".keu_tagihanht where nopo='".$val['notransaksi']."'";
		 	// $rCek=fetchData($sCek);
		 	$sPdo2="select * from ".$dbname.".keu_pdodt where nodocument='".$val['notransaksi']."' and nopdo='".$nopdo."'";
		 	$rPdo2=fetchData($sPdo2);
		 	if(count($rPdo2)==1){
		 		continue;
		 	}
		 	// if(count($rCek)==1){
		 		continue;
		 	// }
		 		$optAkun=makeOption($dbname,"log_5supkelompok","supplierid,noakun","supplierid='".$val['kodesupplier']."' and tipe='KONTRAKTOR'");
			 	$noinvoice2[$val['notransaksi']]=$val['notransaksi'];
	            $sup2[$val['notransaksi']]=$val['kodesupplier'];
	            $tgl2[$val['notransaksi']]=$val['tanggal'];
	            $nil2[$val['notransaksi']]=$val['rphutang'];
	            $noakun2[$val['noinvoice']]=$$optAkun[$val['kodesupplier']];
	            $kas2[$val['noinvoice']]=0;
		 	
		 }
		 // echo"<pre>";
		 // print_r($noinvoice2);
		 // echo"</pre>";
		
		#berdasarkan tagihan dan kas bank yang belum terbayar
		$str="select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier,b.noakun,b.tanggal from ".$dbname.".keu_tagihanht b "
                . " left join ".$dbname.".keu_kasbankdtht_vw c on b.noinvoice=c.keterangan1"
                . " where b.kodeorg='".$induk."' and b.tipeinvoice in ('k','trs','tck') and b.nilaiinvoice>0 and c.jumlah>0 and left(b.tanggal,7)<='".$per."' and c.posting<>1
                 group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
        //exit('warning'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noinvoice[$bar['noinvoice']]=$bar['noinvoice'];
            $sup[$bar['noinvoice']]=$bar['kodesupplier'];
            $tgl[$bar['noinvoice']]=$bar['tanggal'];
            $nil[$bar['noinvoice']]=$bar['nilaiinvoice'];
            $noakun[$bar['noinvoice']]=$bar['noakun'];
            $kas[$bar['noinvoice']]=$bar['jumlah'];
        }

		// $str=" select * from ".$dbname.".keu_tagihanht where kodeorg='".$induk."' and tipeinvoice='k'  and left(tanggal,7)<='".$per."' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$noinvoice[$bar['noinvoice']]=$bar['noinvoice'];
		// 	$sup[$bar['noinvoice']]=$bar['kodesupplier'];
		// 	$tgl[$bar['noinvoice']]=$bar['tanggal'];
		// 	$nil[$bar['noinvoice']]=$bar['nilaiinvoice'];
		// 	$noakun[$bar['noinvoice']]=$bar['noakun'];
		// }
		// if(isset($noinvoice)){
		// 	$str=" select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1 in ('".implode("','",$noinvoice)."')   and left(tanggal,7)<='".$per."'";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($bar=$res->fetch()){
		// 		$kas[$bar['keterangan1']]=$bar['jumlah'];
		// 	}
		// }
		$str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbapp."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $nobaspksave[$bar['nodocument']]=1;
        }
		// $stream.="<tr class=rowcontent id=rowbapp".$no.">";
		// $stream.="<td align=center>".$no."</td>";
		// $stream.="<td align=left id=nobapp".$no." >".$noba."</td>";
		// $stream.="<tr  class=rowcontent>";
        // $stream.="<td colspan=12 align=right><button class=mybutton onclick=saveallbapp(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
        // $stream.="</tr>"; 
        $no=0;
        if(!empty($noinvoice2)){
			foreach($noinvoice2 as $noinv){
				@$sisa[$noinv]=abs($kas2[$noinv]-$nil2[$noinv]);

				if ($sisa[$noinv]!=0) {
					if(@$nobaspksave[$noinv]==1){
						$cek="checked=true";
					}else{
						$cek="";
					}
					$no+=1;    
					$stream.="<tr class=rowcontent id=rowbapp".$no.">";
						$stream.="<td align=center>".$no."</td>";
						$stream.="<td align=left id=nobapp".$no." >".$noinv."</td>";
						$stream.="<td align=left id=supbapp".$no." hidden>".$sup2[$noinv]."</td>";
						 $stream.="<td align=left>".$arrnmsupp[$sup2[$noinv]]."</td>";
						$stream.="<td align=center id=tglbapp".$no." >".tanggalnormal($tgl2[$noinv])."</td>";
						$stream.="<td align=right id=nilbapp".$no." >".@number_format($nil2[$noinv])."</td>";
						$stream.="<td align=right id=kasbapp".$no." >".@number_format($kas2[$noinv])."</td>";
						$stream.="<td align=right id=sisabapp".$no." >".@number_format($sisa[$noinv])."</td>";
						$stream.="<td align=center><input type=checkbox id=cekbapp".$no." ".$cek."></td>";
						$stream.="<input type=hidden id=noakunbapp".$no." value='".$noakun2[$noinv]."'></td>";
						$stream.="</tr>";
				}
			}
		} 
		if(!empty($noinvoice)){
			foreach($noinvoice as $noinv){
				@$sisa[$noinv]=abs($kas[$noinv]-$nil[$noinv]);

				if ($sisa[$noinv]!=0) {
					if(@$nobaspksave[$noinv]==1){
						$cek="checked=true";
					}else{
						$cek="";
					}
					$no+=1;    
					$stream.="<tr class=rowcontent id=rowbapp".$no.">";
						$stream.="<td align=center>".$no."</td>";
						$stream.="<td align=left id=nobapp".$no." >".$noinv."</td>";
						$stream.="<td align=left id=supbapp".$no." hidden>".$sup[$noinv]."</td>";
						 $stream.="<td align=left>".$arrnmsupp[$sup[$noinv]]."</td>";
						$stream.="<td align=center id=tglbapp".$no." >".tanggalnormal($tgl[$noinv])."</td>";
						$stream.="<td align=right id=nilbapp".$no." >".@number_format($nil[$noinv])."</td>";
						$stream.="<td align=right id=kasbapp".$no." >".@number_format($kas[$noinv])."</td>";
						$stream.="<td align=right id=sisabapp".$no." >".@number_format($sisa[$noinv])."</td>";
						$stream.="<td align=center><input type=checkbox id=cekbapp".$no." ".$cek."></td>";
						$stream.="<input type=hidden id=noakunbapp".$no." value='".$noakun[$noinv]."'></td>";
						$stream.="</tr>";
				}
			}
			$stream.="<tr  class=rowcontent>";
			$stream.="<td colspan=8 align=right><button class=mybutton onclick=saveallbapp(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
			$stream.="</tr>";  
		}
		/*
		if(!empty($nobaspk))
		{
			foreach($nobaspk as $noba)
			{
				@$sisa[$noba]=abs($kas[$noba]-$nil[$noba]);
				if($sisa[$noba]>0)
				{
					if(@$nobaspksave[$noba]==1)
					{
						$cek="checked=true";
					}
					else
					{
						$cek="";
					}
					@$rpsat[$noba]=$nil[$noba]/$fis[$noba];
					@$no+=1;    
					$stream.="<tr class=rowcontent id=rowbapp".$no.">";
					$stream.="<td align=center>".$no."</td>";
					$stream.="<td align=left id=nobapp".$no." >".$noba."</td>";
					$stream.="<td align=left id=supbapp".$no." hidden>".$sup[$noba]."</td>";
					 $stream.="<td align=left>".$arrnmsupp[$sup[$noba]]."</td>";
					$stream.="<td align=center id=tglbapp".$no." >".tanggalnormal($tgl[$noba])."</td>";
					$stream.="<td align=right id=nilbapp".$no." >".@number_format($nil[$noba])."</td>";
					$stream.="<td align=right id=kasbapp".$no." >".@number_format($kas[$noba])."</td>";
					$stream.="<td align=right id=sisabapp".$no." >".@number_format($sisa[$noba])."</td>";
					$stream.="<td align=center><input type=checkbox id=cekbapp".$no." ".$cek."></td>";
					$stream.="</tr>";
				}
			}
		}
        $stream.="<tr  class=rowcontent>";
        $stream.="<td colspan=12 align=right><button class=mybutton onclick=saveallbapp(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
        $stream.="</tr>";  
        */
        echo $stream;
    break;
    case'nobapp':
        $thn=substr($per,0,4);
        $per=  str_replace('-', '', $per);
        if($notranbapp=='')
        {
            ##cek apakah sudah pernah ada data diinput
            ##param : nopdo - periode - divisi - tipekaryawan
            $str=" select notransaksi from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%BAPP%'"
                    . " and divisi='".$divisibapp."'  "
                    . " order by notransaksi desc limit 1 ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
                $nolama=$bar['notransaksi'];
            if($nolama=='')
            {
               $str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%BAPP%' "
                        . " order by notransaksi desc limit 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
                    $notran=$bar['notransaksi'];
                $num=  explode('/', $notran);
                @$num=$num[3]+1;
                if($num<10)
                    $num='00'.$num;   
                else if($num<100)
                   $num='0'.$num;
                else
                   @$num=$num;
                $noupahbaru=$per.'/'.$unit.'/BAPP/'.$num;
            }
            else
            {
                $noupahbaru=$nolama;
            }
        }
        else{
			$str=" select notransaksi from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%BAPP%'"
                    . " and divisi='".$divisibapp."'  "
                    . " order by notransaksi desc limit 1 ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
                $nolama=$bar['notransaksi'];
			if($nolama==''){
               $str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%BAPP%' "
                        . " order by notransaksi desc limit 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
                    $notran=$bar['notransaksi'];
                $num=  explode('/', $notran);
                @$num=$num[3]+1;
                if($num<10)
                    $num='00'.$num;   
                else if($num<100)
                   $num='0'.$num;
                else
                   $num=$num;
                $noupahbaru=$per.'/'.$unit.'/BAPP/'.$num;
            }
            else{
                $noupahbaru=$nolama;
            }	
           // $noupahbaru=$notranbapp;
        }
        echo $noupahbaru;
    break;
    #######################################################################################
    #######################################################################################
    case'deletehutang':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and nodocument='".$notranhutang."'";
        try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'savehutang':

    	if ($noakunkashutang=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunkashutang=='1110101') {
			if ($rekeningbankhutang=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

        if($cekhutang==1) {
			#cek apakah HT sudah di-insert
			$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='HUTANG' limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$cekht=$bar['jumlah'];
			if($cekht<=0){
				$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('".$nopdo."', '".$notranhutang."', '".$unit."', '".$per."', 'HUTANG','".$_SESSION['standard']['userid']."')";
				try{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}		

            ##delete 1st
            $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranhutang."' "
                        . " and nodocument='".$pohutang."' ";
            try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
            $str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranhutang."'"
                . " order by nourut desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$sCek="select left(kodebarang,3) as klmpk,noakun,sum(hargasatuan*jumlah) as nilai  from ".$dbname.".log_transaksi_vw a left join ".$dbname.".log_5klbarang b on left(a.kodebarang,3)=b.kode 
			       where notransaksi='".$pohutang."' group by b.noakun";
			$rCek=fetchData($sCek);
			if(count($rCek)!=0){
				$rData=fetchData($sCek);
				foreach ($rData as $key => $val) {
					$optArusKas=makeOption($dbname,"keu_5aruskas_detail","noakun,noaruskas","noakun='".$val['noakun']."'");					
					$dtArus[$optArusKas[$val['noakun']]]=$val['nilai'];
				}
			}else{
				$sCek="select noaruskas,sum(nilai) as nilai  from ".$dbname.".keu_tagihandt where noinvoice='".$pohutang."' and left(noakun,3) not in ('117','213') group by noaruskas";
				$rData=fetchData($sCek);
				
				if(count($rData)!=0){
					foreach ($rData as $key => $val) {
						//exit('warning'.($val['nilai']+$isiPPn)."-".$pengurangDt);
						$dtArus[$val['noaruskas']]=($kashutang/count($rData));
					}	
				}
			}
                $nourutbaru=$bar['nourut']+1;
                foreach ($dtArus as $key => $val){
                	$str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                    `nodocument`,`tanggal`,`rupiah`,`kodesupplier`,`noakunkas`,`rekeningbank`) 
                    VALUES ('".$nopdo."', '".$notranhutang."', '".$nourutbaru."', '".$key."', '".$arrnmsupp[$suphutang]."',
                    '".$pohutang."','".$tglawalper."','".$val."','".$suphutang."','".$noakunkashutang."','".$rekeningbankhutang."')";    
		            try{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) {
					   print " Gagal  !: " . $e->getMessage() . "\n"; 
					   die(); 
					}
					$nourutbaru+=1;
                }
            
        }
        else {
            $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranhutang."' "
                        . " and nodocument='".$pohutang."' ";
            try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
        }
    break;    
    case'listhutang':

    	#ambil data HT
        $strht="select kodeorg from ".$dbname.".keu_pdoht where  nopdo='".$nopdo."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$kodeorght=$barht['kodeorg'];

        #ambil induk organisasi
        $strht="select tipe,induk from ".$dbname.".organisasi where  kodeorganisasi='".$kodeorght."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$tipe=$barht['tipe'];
		$induk=$barht['induk'];

		if ($barht['tipe']!='HOLDING') {
			continue;
		}

		// $stream.="<fieldset><legend><b>List ".$_SESSION['lang']['hutang']."</b></legend>
  //           <table cellpading=1 cellspacing=1 border=0 class=sortable>
  //               <thead>
  //                   <tr>
  //                       <td align=center rowspan=2 width=30px >".$_SESSION['lang']['nourut']."</td>    
  //                       <td align=center rowspan=2 width=180px >".$_SESSION['lang']['namasupplier']."</td>    
  //                       <td align=center rowspan=2 width=180px >".$_SESSION['lang']['nopo']."</td>
  //                       <td align=center colspan=4 width=50px >".$_SESSION['lang']['hutang']."</td>
  //                       <td align=center colspan=2 width=50px >".$_SESSION['lang']['pembayaran']."</td>
		// 				<td align=center rowspan=2 width=30px >".$_SESSION['lang']['action']."</td>
  //                   </tr>
  //                   <tr>
  //                       <td align=center width=80px >".$_SESSION['lang']['rupiah']."</td>
  //                       <td align=center width=80px >".$_SESSION['lang']['ppn']."</td>
  //                       <td align=center width=80px >PPh</td>
  //                       <td align=center width=80px >".$_SESSION['lang']['total']."</td>
  //                       <td align=center width=80px >".$_SESSION['lang']['terbayar']."</td>
  //                       <td align=center width=80px >".$_SESSION['lang']['sisa']."</td>
  //                   </tr>
  //               </thead>";
		$stream.="<fieldset><legend><b>List ".$_SESSION['lang']['hutang']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr>
                        <td align=center width=30px >".$_SESSION['lang']['nourut']."</td>    
                        <td align=center width=30px >".$_SESSION['lang']['noakun']."</td>    
                        <td align=center width=180px >".$_SESSION['lang']['namasupplier']."</td>    
                        <td align=center width=180px >".$_SESSION['lang']['nopo']."</td>
                        <td align=center width=50px >".$_SESSION['lang']['hutang']."</td>
						<td align=center colspan=2>".$_SESSION['lang']['action']."</td>
                    </tr>
                </thead>";

                $sData="select sum(rupiah) as rup,nodocument,rincian,noakunkas,rekeningbank from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranhutang."' group by nodocument order by nodocument";
                $rData=fetchData($sData);
                
                foreach ($rData as $key => $val) {
                	$no+=1;
                	$stream.="<tr class=rowcontent>";
                	$stream.="<td align=center>".$no."</td>";
                	$stream.="<td>".$arrnmakun[$val['noakunkas']]."</td>";
                	$stream.="<td>".$val['rincian']."</td>";
                	$stream.="<td>".$val['nodocument']."</td>";
                	$stream.="<td align=right>".number_format($val['rup'])."</td>";
					$stream.="<td align=center><img src=images/application/application_edit.png class=zImgBtn title='Edit' 
								 onclick=\"edithutang('".$nopdo."','".$notranhutang."','".$val['noakunkas']."','".$val['rekeningbank']."');\"></td>
							<td align=center><img src=images/application/application_delete.png class=zImgBtn title='Delete' 
								 onclick=\"deletehutang('".$nopdo."','".$val['nodocument']."','');\">
									</td>
									";
					$stream.="</tr>";
					$totHutang+=$val['rup'];
                }
                $stream.="<tr class=rowcontent>";
            	$stream.="<td colspan=4>".$_SESSION['lang']['total']."</td>";
            	$stream.="<td align=right>".number_format($totHutang)."</td>";
            	$stream.="<td colspan=2>&nbsp;</td>";
            	$stream.="</tr>";
        #data nopo
  //       $str=" select * from ".$dbname.".log_transaksi_vw where kodept='".$induk."' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
  //           $nilaipo[$bar['nopo']]=$bar['subtotal'];
  //           $totalpo[$bar['nopo']]=$bar['nilaipo'];
  //           $ppn[$bar['nopo']]=$bar['ppn'];
  //           $pph[$bar['nopo']]=$bar['pph'];
  //           $sup[$bar['nopo']]=$bar['kodesupplier'];
  //       }
  //       $str=" select a.nopo,b.noinvoice,b.nilaiinvoice,c.jumlah,(c.jumlah-b.nilaiinvoice) as selisih from ".$dbname.".log_po_terima_vw2 a "
  //               . " left join ".$dbname.".keu_tagihanht b on a.nopo=b.nopo "
  //               . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
  //               . " where a.kodeorg='".$induk."' and  ((c.jumlah-b.nilaiinvoice < 0) or (jumlah is NULL)) ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
  //           $noinv[$bar['nopo']]=$bar['noinvoice'];
  //           $nilaikas[$bar['nopo']]=$bar['jumlah'];
  //       }
  //       //$str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranhutang."' ";
		// $str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%HUTANG%' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
  //           $nomorpo[$bar['nodocument']]=$bar['nodocument'];
  //           $nourut[$bar['nodocument']]=$bar['nourut'];
  //       }

  //       #ambil statjurnal
  //       $kodeall=array();
  //       $kodejurnal=array();
  //       $kodetdkjurnal=array();
  //       $strsup="select kode,jurnal from ".$dbname.".keu_5jenistagihan where kode not in ('p','pj','poa','um','k','p21','p22','p23','p25','ps4') ";
		// $ressup=$owlPDO->query($strsup) or die(print " Gagal: ".PDOException::getMessage());
		// $ressup->setFetchMode(PDO::FETCH_ASSOC);
		// while ($barsup=$ressup->fetch()) {
		// 	if ($barsup['jurnal']==1) {
		// 		$kodejurnal[$barsup['kode']]=$barsup['kode'];
		// 	}
		// 	if ($barsup['jurnal']==0) {
		// 		$kodetdkjurnal[$barsup['kode']]=$barsup['kode'];
		// 	}
		// }

		// $str=" select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier from ".$dbname.".keu_tagihanht b "
  //               . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
  //               . " where b.kodeorg='".$induk."' and b.tipeinvoice in ('".implode("','",$kodejurnal)."') and b.nilaiinvoice>0 and c.jumlah>0 group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
  //           $noinvjurnal[$bar['noinvoice']]=$bar['noinvoice'];
  //           $nilaiht[$bar['noinvoice']]=$bar['nilaiinvoice'];
  //           $nilaikas[$bar['noinvoice']]=$bar['jumlah'];
  //           $noakunhutang[$bar['noinvoice']]=$bar['noakun'];
  //           $sup[$bar['noinvoice']]=$bar['kodesupplier'];
  //       }

  //       $str=" select b.noinvoice,b.noakun,b.nilai as pph from ".$dbname.".keu_tagihandt b where b.noinvoice in ('".implode("','",$noinvjurnal)."') and left(noakun,3)='213'";
  //       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
  //           $pph[$bar['noinvoice']]=$bar['pph']*-1;
  //       }

  //       $str=" select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier from ".$dbname.".keu_tagihanht b "
  //               . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
  //               . " where b.kodeorg='".$induk."' and b.tipeinvoice in ('".implode("','",$kodetdkjurnal)."') and b.nilaiinvoice>0 and c.jumlah>0 group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
  //           $noinvtdkjurnal[$bar['noinvoice']]=$bar['noinvoice'];
  //           $nilaiht[$bar['noinvoice']]=$bar['nilaiinvoice'];
  //           $nilaikas[$bar['noinvoice']]=$bar['jumlah'];
  //           $noakunhutang[$bar['noinvoice']]=$bar['noakun'];
  //           $sup[$bar['noinvoice']]=$bar['kodesupplier'];
  //       }

  //       $str=" select b.noinvoice,b.noakun,b.nilai from ".$dbname.".keu_tagihandt b where b.noinvoice in ('".implode("','",$noinvtdkjurnal)."') and (left(noakun,3)='213' or left(noakun,3)='117')";
  //       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	if (substr($bar['noakun'],0,3)=='213') {
		// 		$pph[$bar['noinvoice']]=$bar['nilai']*-1;
		// 	}else{
		// 		$ppn[$bar['noinvoice']]=$bar['nilai'];
		// 	} 
  //       }

  //       if(count(@$nomorpo)>0){
		// 	foreach($nomorpo as $nopo)
		// 	{

		// 		if ($nilaipo[$nopo]==0) {
		// 			$nilaipo[$nopo]=$nilaiht[$nopo]-$ppn[$nopo];
		//             $totalpo[$nopo]=$nilaiht[$nopo]+$ppn[$nopo]-$pph[$nopo];
		// 		}

		// 		$no+=1;
		// 		$sisa[$nopo]=abs($nilaikas[$nopo]-$totalpo[$nopo]);
		// 		$stream.="<tr class=rowcontent>";
		// 		$stream.="<td align=center>".$no."</td>";
		// 		$stream.="<td align=left>".$arrnmsupp[$sup[$nopo]]."</td>";
		// 		$stream.="<td align=center>".$nopo."</td>";
		// 		$stream.="<td align=right>".@number_format($nilaipo[$nopo])."</td>";
		// 		$stream.="<td align=right>".@number_format($ppn[$nopo])."</td>";
		// 		$stream.="<td align=right>".@number_format($pph[$nopo])."</td>";
		// 		$stream.="<td align=right>".@number_format($totalpo[$nopo])."</td>";
		// 		$stream.="<td align=right>".@number_format($nilaikas[$nopo])."</td>";
		// 		$stream.="<td align=right>".@number_format($sisa[$nopo])."</td>";
		// 		$stream.="
		// 						<td align=center>
		// 							<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
		// 								 onclick=\"edithutang('".$nopdo."','".$notranhutang."');\">
		// 							<img src=images/application/application_delete.png class=zImgBtn title='Delete' 
		// 								 onclick=\"deletehutang('".$nopdo."','".$notranhutang."','".$nourut[$nopo]."');\">
		// 						</td>
		// 						";
		// 		$stream.="</tr>";
		// 	}
		// }
		$stream.="</table></fieldset>";
	
            echo $stream;
	
    break;    
    case'detailhutang':
    	$isiDt=explode("/",$nopdo);
		$kodeorght=$isiDt[2];
        #ambil induk organisasi
        $strht="select tipe,induk from ".$dbname.".organisasi where  kodeorganisasi='".$unit."'";
		$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht=$resht->fetch();
		$tipe=$barht['tipe'];
		$induk=$barht['induk'];
		
		if ($barht['tipe']!='HOLDING') {
			continue;
		}

        $stream="";
        $stream.="<fieldset><legend><b>Detail ".$_SESSION['lang']['hutang']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr>
                        <td align=center rowspan=2 width=30px >".$_SESSION['lang']['nourut']."</td>    
                        <td align=center rowspan=2 width=180px >".$_SESSION['lang']['namasupplier']."</td>    
                        <td align=center rowspan=2 width=180px >".$_SESSION['lang']['nopo']."</td>
                        <td align=center colspan=4 width=50px >".$_SESSION['lang']['hutang']."</td>
                        <td align=center colspan=2 width=50px >".$_SESSION['lang']['pembayaran']."</td>
                            <td align=center rowspan=2 width=30px >".$_SESSION['lang']['action']."
                                <br><input type=checkbox id=cekallhutang onclick=cekallhutang()></td>
                    </tr>
                    <tr>
                        <td align=center width=80px >".$_SESSION['lang']['rupiah']."</td>
                        <td align=center width=80px >".$_SESSION['lang']['ppn']."</td>
                        <td align=center width=80px >PPh</td>
                        <td align=center width=80px >".$_SESSION['lang']['total']."</td>
                        <td align=center width=80px >".$_SESSION['lang']['terbayar']."</td>
                        <td align=center width=80px >".$_SESSION['lang']['sisa']."</td>
                    </tr>
                </thead><tbody id=contentdetailhutang>";
       // log_po_terima_vw
        #data nopo
        $str=" select * from ".$dbname.".log_poht where kodeorg='".$induk."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $nilaipo[$bar['nopo']]=$bar['subtotal'];
            $totalpo[$bar['nopo']]=$bar['nilaipo'];
            $ppn[$bar['nopo']]=$bar['ppn'];
            $pph[$bar['nopo']]=$bar['pph'];
            $sup[$bar['nopo']]=$bar['kodesupplier'];
        }
        #ambil noakun supplier untuk po
        $strsup="select noakun from ".$dbname.".log_5klsupplier where tipe='SUPPLIER'";
		$ressup=$owlPDO->query($strsup) or die(print " Gagal: ".PDOException::getMessage());
		$ressup->setFetchMode(PDO::FETCH_ASSOC);
		$barsup=$ressup->fetch();
		$noakunsupp=$barsup['noakun'];

        /*$str=" select a.nopo,b.noinvoice,b.nilaiinvoice,b.noakun,c.jumlah,(c.jumlah-b.nilaiinvoice) as selisih from ".$dbname.".log_po_terima_vw2 a "
                . " left join ".$dbname.".keu_tagihanht b on a.nopo=b.nopo "
                . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
                . " where a.kodeorg='".$induk."' and  ((c.jumlah-b.nilaiinvoice < 0) or (jumlah is NULL)) and c.jumlah>0 ";*/
        // $str=" select a.nopo,b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,sum(c.jumlah)-b.nilaiinvoice as selisih from ".$dbname.".log_po_terima_vw2 a "
        //         . " left join ".$dbname.".keu_tagihanht b on a.nopo=b.nopo "
        //         . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
        //         . " where a.kodeorg='".$induk."' and b.nilaiinvoice>0 and c.jumlah>0  "
        //         . "group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
        #ambil dari GR yang belum terdaftar pada tagihan
        $str="select sum(hargasatuan*jumlah) as nilaipo,notransaksi as nopo,idsupplier as kodesupplier from ".$dbname.".log_transaksi_vw where 
              tipetransaksi=1 and kodept='".$induk."' and left(tanggal,7)<='".$per."' group by notransaksi having sum(hargasatuan*jumlah)>0";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$sCek="select * from ".$dbname.".keu_tagihanht where notransaksi_gr='".$bar['nopo']."'";
			$rCek=fetchData($sCek);
			if(count($rCek)!=0){
				continue;
			}
			$periodeData=explode("-",$per);
			$sCek2="select * from ".$dbname.".keu_pdodt where nodocument='".$bar['nopo']."' and notransaksi like '".$periodeData[0].$periodeData[1]."%'";
			//exit('warning'.$sCek2);
			$rCek2=fetchData($sCek2);
			if(count($rCek2)!=0){
				continue;
			}
            $nomorpo[$bar['nopo']]=$bar['nopo'];
            $nilaipo[$bar['nopo']]=$bar['nilaipo'];
            $noinv[$bar['nopo']]=$bar['noinvoice'];
            $nilaikas[$bar['nopo']]=$bar['jumlah'];
            //$totalpo[$bar['nopo']]=$bar['jumlah'];
            $noakunhutang[$bar['nopo']]=$noakunsupp;
            $sup[$bar['nopo']]=$bar['kodesupplier'];
        }
        

        #ambil statjurnal
        $kodeall=array();
        $kodejurnal=array();
        $kodetdkjurnal=array();
        $strsup="select kode,jurnal from ".$dbname.".keu_5jenistagihan where kode not in ('um','k','p21','p22','p23','p25','ps4','upd') ";
		$ressup=$owlPDO->query($strsup) or die(print " Gagal: ".PDOException::getMessage());
		$ressup->setFetchMode(PDO::FETCH_ASSOC);
		while ($barsup=$ressup->fetch()) {
			if ($barsup['jurnal']==1) {
				$kodejurnal[$barsup['kode']]=$barsup['kode'];
			}
			if ($barsup['jurnal']==0) {
				$kodetdkjurnal[$barsup['kode']]=$barsup['kode'];
			}
		}

		$str=" select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier,b.noakun from ".$dbname.".keu_tagihanht b "
                . " left join ".$dbname.".keu_kasbankdtht_vw c on b.noinvoice=c.keterangan1"
                . " where b.kodeorg='".$induk."' and b.tipeinvoice in ('".implode("','",$kodejurnal)."') and b.nilaiinvoice>0 and c.jumlah>0 and left(b.tanggal,7)<='".$per."' and c.posting<>1
                 group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$sCek="select * from ".$dbname.".keu_pdodt where nodocument='".$bar['noinvoice']."'";
			$rCek=fetchData($sCek);
			if(count($rCek)!=0){
				continue;
			}
            $noinvtagihan[$bar['noinvoice']]=$bar['noinvoice'];
            $noinvjurnal[$bar['noinvoice']]=$bar['noinvoice'];
            $nilaiht[$bar['noinvoice']]=$bar['nilaiinvoice'];
            $nilaikas[$bar['noinvoice']]=$bar['jumlah'];
            $noakunhutang[$bar['noinvoice']]=$bar['noakun'];
            $sup[$bar['noinvoice']]=$bar['kodesupplier'];
            $noakunhutang[$bar['nopo']]=$bar['noakun'];
        }

        

        $str=" select b.noinvoice,b.noakun,b.nilai as pph from ".$dbname.".keu_tagihandt b where b.noinvoice in ('".implode("','",$noinvjurnal)."') and left(noakun,3)='213'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $pph[$bar['noinvoice']]=$bar['pph']*-1;
        }

        $str=" select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier from ".$dbname.".keu_tagihanht b "
                . " left join ".$dbname.".keu_kasbankdtht_vw c on b.noinvoice=c.keterangan1"
                . " where b.kodeorg='".$induk."' and b.tipeinvoice in ('".implode("','",$kodetdkjurnal)."') and b.nilaiinvoice>0 and c.jumlah>0  and left(b.tanggal,7)<='".$per."' 
                group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$sCek="select * from ".$dbname.".keu_pdodt where nodocument='".$bar['noinvoice']."'";
			$rCek=fetchData($sCek);
			if(count($rCek)!=0){
				continue;
			}
            $noinvtagihan[$bar['noinvoice']]=$bar['noinvoice'];
            $noinvtdkjurnal[$bar['noinvoice']]=$bar['noinvoice'];
            $nilaiht[$bar['noinvoice']]=$bar['nilaiinvoice'];
            $nilaikas[$bar['noinvoice']]=$bar['jumlah'];
            $noakunhutang[$bar['noinvoice']]=$bar['noakun'];
            $sup[$bar['noinvoice']]=$bar['kodesupplier'];
        }

        $str=" select b.noinvoice,b.noakun,b.nilai from ".$dbname.".keu_tagihandt b where b.noinvoice in ('".implode("','",$noinvtdkjurnal)."') and (left(noakun,3)='213' or left(noakun,3)='117')";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if (substr($bar['noakun'],0,3)=='213') {
				$pph[$bar['noinvoice']]=$bar['nilai']*-1;
			}else{
				$ppn[$bar['noinvoice']]=$bar['nilai'];
			} 
        }
        
        $str=" select nodocument from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranhutang."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $posave[$bar['nodocument']]=1;
        }
        
		if(isset($nomorpo))
        foreach($nomorpo as $nopo) {
            if(@$posave[$nopo]==1){
                $cek="checked=true";
            }
            else{
                $cek="";
            }
            $no+=1;
            $sisa[$nopo]=abs($nilaipo[$nopo]-$nilaikas[$nopo]);
            $stream.="<tr class=rowcontent id=rowhutang".$no.">";
            $stream.="<td align=center>".$no."</td>";
            $stream.="<td align=left id=suphutang".$no." hidden>".$sup[$nopo]."</td>";
            $stream.="<td align=left>".$arrnmsupp[$sup[$nopo]]."</td>";
            $stream.="<td align=left id=pohutang".$no.">".$nopo."</td>";
            $stream.="<td align=right>".@number_format($nilaipo[$nopo])."</td>";
            $stream.="<td align=right id=ppnhutang".$no.">".@number_format($ppn[$nopo])."</td>";
            $stream.="<td align=right id=pphhutang".$no.">".@number_format($pph[$nopo])."</td>";
            $stream.="<td align=right id=nilpohutang".$no.">".@number_format($totalpo[$nopo])."</td>";
            $stream.="<td align=right id=kashutang".$no.">".@number_format($nilaikas[$nopo])."</td>";
            $stream.="<td align=right id=sisahutang".$no.">".@number_format($sisa[$nopo])."</td>";
            $stream.="<td align=center><input type=checkbox id=cekhutang".$no." ".$cek."></td>";
            $stream.="<input type=hidden id=noakunhutang".$no." value='".$noakunhutang[$nopo]."'>";
            $stream.="</tr>";
        }

		if(isset($noinvtagihan))
        foreach($noinvtagihan as $invoice) {
            if(@$posave[$invoice]==1){
                $cek="checked=true";
            }
            else{
                $cek="";
            }
            $no+=1;
            $nilaipo[$invoice]=$nilaiht[$invoice]-$ppn[$invoice];
            $totalpo[$invoice]=$nilaiht[$invoice]+$ppn[$invoice]-$pph[$invoice];
            $sisa[$invoice]=abs($nilaikas[$invoice]-$totalpo[$invoice]);
            $stream.="<tr class=rowcontent id=rowhutang".$no.">";
            $stream.="<td align=center>".$no."</td>";
            $stream.="<td align=left id=suphutang".$no." hidden>".$sup[$invoice]."</td>";
            $stream.="<td align=left>".$arrnmsupp[$sup[$invoice]]."</td>";
            $stream.="<td align=left id=pohutang".$no.">".$invoice."</td>";
            $stream.="<td align=right>".@number_format($nilaipo[$invoice])."</td>";
            $stream.="<td align=right id=ppnhutang".$no.">".@number_format($ppn[$invoice])."</td>";
            $stream.="<td align=right id=pphhutang".$no.">".@number_format($pph[$invoice])."</td>";
            $stream.="<td align=right id=nilpohutang".$no.">".@number_format($totalpo[$invoice])."</td>";
            $stream.="<td align=right id=kashutang".$no.">".@number_format($nilaikas[$invoice])."</td>";
            $stream.="<td align=right id=sisahutang".$no.">".@number_format($sisa[$invoice])."</td>";
            $stream.="<td align=center><input type=checkbox id=cekhutang".$no." ".$cek."></td>";
            $stream.="<input type=hidden id=noakunhutang".$no." value='".$noakunhutang[$invoice]."'>";
            $stream.="</tr>";
        }
        $stream.="<tr  class=rowcontent>";
        $stream.="<td colspan=10 align=right ><button class=mybutton onclick=saveallhutang(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
        $stream.="</tr>";  
        if($optTipe[$unit]=='HOLDING'){
			echo $stream;
		}
    break;
    #################################################
    #################################################
    #################################################
    case'deleteincome':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranincome."' "
                        . " and nourut='".$nourutincome."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'updateincome':
        $str="update ".$dbname.".keu_pdodt set noakun='".$akunincome."',rincian='".$ketincome."',
            tanggal='".$tglawalper."',satuan='".$satincome."',fisik='".$fisikincome."',rupiah='".$totincome."',
            noakunkas='".$noakunincome."',rekeningbank='".$rekeningbankincome."'
            where nopdo='".$nopdo."' and notransaksi='".$notranincome."' and nourut='".$nourutincome ."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;    
	case'saveincome2':

    	if ($noakunincome=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunincome=='1110101') {
			if ($rekeningbankincome=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		#cek apakah HT sudah di-insert
		$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='INCOME'
				and notransaksi like '%INCOME/002' limit 1";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$cekht=$bar['jumlah'];
		if($cekht<=0){
			$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('".$nopdo."', '".$notranincome2."', '".$unit."', '".$per."', 'INCOME','".$_SESSION['standard']['userid']."')";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}		
		#delete 1st
		$strins="delete from ".$dbname.".`keu_pdodt` where nopdo='".$nopdo."' and notransaksi='".$notranincome2."'";
		try{
			$owlPDO->exec($strins);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		$str="select * from ".$dbname.".pmn_estimasipenerimaan where pt='".$kept[$unit]."' and periode='".$per."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			#cek nourut
			$strn="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranincome2."'"
				. " order by nourut desc limit 1 ";
			$resn=$owlPDO->query($strn) or die(print " Gagal: ".PDOException::getMessage());
			$resn->setFetchMode(PDO::FETCH_ASSOC);
			$barn=$resn->fetch();
				$nourutbaru=$barn['nourut']+1;
				$noakunx='';
				//noaruskas komoditi
                $sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='NAKON'";
                $rappl = fetchData($sappl);
                $noarus=$rappl[0]['nilai'];
                $noarus=explode(',', $noarus);
                
                switch ($bar['kodebarang']){
                    case '40000001':$noakunx=$noarus[0];break;
                    case '40000002':$noakunx=$noarus[1];break;
                    case '40000003':$noakunx=$noarus[2];break;
                    case '40000005':$noakunx=$noarus[3];break;
                    case '40000004':$noakunx=$noarus[4];break;
                    case '40000016':$noakunx=$noarus[5];break;
                }
			$strins="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
					`tanggal`, `satuan`,`fisik`, `rupiah`,`noakunkas`, `rekeningbank`) 
					VALUES ('".$nopdo."', '".$notranincome2."', '".$nourutbaru."', '".$noakunx."','Estimasi Masuk barang ".$arrnmbrg[$bar['kodebarang']]."',
					'".$bar['periode']."', 'KG', '".$bar['qty']."','".($bar['harga']*$bar['qty'])."','".$noakunincome."','".$rekeningbankincome."')";
			try{
				$owlPDO->exec($strins);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}
	break;
    case'saveincome':

    	if ($noakunincome=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunincome=='1110101') {
			if ($rekeningbankincome=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		#cek apakah HT sudah di-insert
		$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='INCOME'
				and notransaksi like '%INCOME/001' limit 1";			
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$cekht=$bar['jumlah'];
		if($cekht<=0)
		{
			$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('".$nopdo."', '".$notranincome."', '".$unit."', '".$per."', 'INCOME','".$_SESSION['standard']['userid']."')";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}	
        #cek nourut
        $str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranincome."'"
            . " order by nourut desc limit 1 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
            $nourutbaru=$bar['nourut']+1;
        $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                `tanggal`, `satuan`,`fisik`, `rupiah`,`noakunkas`, `rekeningbank`) 
                VALUES ('".$nopdo."', '".$notranincome."', '".$nourutbaru."', '".$akunincome."', '".$ketincome."',
                '".$tglawalper."', '".$satincome."','".$fisikincome."','".$totincome."','".$noakunincome."','".$rekeningbankincome."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'listincome':
		// style='float:left;'
       $stream.="<fieldset><legend><b>".$_SESSION['lang']['penerimaandana']."</b></legend >
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td align=center width=30px>".$_SESSION['lang']['nourut']."</td>    
                        <td align=center>".$_SESSION['lang']['noakun']."</td>    
                        <td align=center>".$_SESSION['lang']['aruskas']."</td>          
                        <td align=center>".$_SESSION['lang']['keterangan']."</td>    
                        <td align=center>".$_SESSION['lang']['satuan']."</td>
                        <td align=center>".$_SESSION['lang']['kuantitas']."</td>
                        <td align=center>".$_SESSION['lang']['rupiahsatuan']."</td>
                        <td align=center>".$_SESSION['lang']['total']."</td>
                        <td align=center width=30px>".$_SESSION['lang']['action']."</td>
                    </tr>
                </thead>";
        //$notrankas=$explnopdo[0].'/'.$explnopdo[2].'/KAS/001';
        $str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%INCOME%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $stream.="<tr class=rowcontent>
                        <td align=center>".$no."</td>
                        <td align=center>".$arrnmakun[$bar['noakunkas']]."</td>  
                        <td align=center>".$bar['noakun']." - ".(($arrnmaruskas[$bar['noakun']]=='') ? $arrnmakun[$bar['noakun']]:$arrnmaruskas[$bar['noakun']])."</td>           
                        <td align=left>".(($optket[$bar['rincian']]=='') ? $bar['rincian'] : $optket[$bar['rincian']])."</td>  
                        <td align=center>".$bar['satuan']."</td>
                        <td align=right>".@number_format($bar['fisik'])."</td>
                        <td align=right>".@number_format($bar['rupiah']/$bar['fisik'])."</td>
                        <td align=right>".@number_format($bar['rupiah'])."</td>";
              $stream.="<td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                                onclick=\"editincome('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."',
                                    '".$bar['noakun']."','".$bar['rincian']."','".$bar['satuan']."','".$bar['fisik']."',
                                    '".$bar['rupiah']/$bar['fisik']."','".$bar['rupiah']."','".$bar['noakunkas']."','".$bar['rekeningbank']."');\">";
                 $stream.="            <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                                onclick=\"deletekas('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."');\">
                        </td> 
                </tr>";    
        }
        echo $stream;
    break;
     case'detailincome':

    #ambil data HT
    $strht="select kodeorg from ".$dbname.".keu_pdoht where  nopdo='".$nopdo."'";
	$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
	$resht->setFetchMode(PDO::FETCH_ASSOC);
	$barht=$resht->fetch();
	$kodeorght=$barht['kodeorg'];

    #ambil induk organisasi
    $strht="select tipe from ".$dbname.".organisasi where  kodeorganisasi='".$kodeorght."'";
	$resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
	$resht->setFetchMode(PDO::FETCH_ASSOC);
	$barht=$resht->fetch();
	$tipe=$barht['tipe'];

    $optrek=$optkas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $str="select noakun,namaakun from ".$dbname.".keu_5akun where noakun in ('1112101','1112102','1110101')";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar=$res->fetch()) {
    	$optkas.="<option value='".$bar['noakun']."'>".$bar['namaakun']."</option>";
	}

	$str = "select * from ".$dbname.".keu_5akunbank";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
	    $wheredz =" kodebank='".$bar['namabank']."'";
	    $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
	    $optrek.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
	}

	$stream.="<fieldset><legend><b>Form</b></legend>";
        $notrankas=$explnopdo[0].'/'.$explnopdo[2].'/INCOME'.'/'.$explnopdo[3].'/001';
		$stream.="<fieldset style=float:left><legend><b>Input</b></legend>";
        $stream.="
            <table cellpading=1 cellspacing=1 border=0>
						<tr>
							<td>".$_SESSION['lang']['notransaksi']."</td>
							<td>:</td>
							<td align=left><input type=text id=notranincome style=width:150px disabled value='".$notrankas."' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext></td>
						</tr>
						<tr hidden>
							<td>nourutdb</td>
							<td>:</td>
							<td align=left><input type=text id=nourutincome onkeypress=\"return tanpa_kutip(event)\" class=myinputtext></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['noakun']."</td>
							<td>:</td>
							<td>
								<select onchange='getrekeningincome()' id=noakunincome style=\"width:155px;\">".$optkas."</select>
							</td>    
						</tr>
						<tr>
							<td>".$_SESSION['lang']['rekening']."</td>
							<td>:</td>
							<td>
								<select id=rekeningbankincome style=\"width:155px;\">".$optrek."</select>
							</td>    
						</tr>
						<tr>
							<td>".$_SESSION['lang']['aruskas']."</td>
							<td>:</td>
							<td>
								<select id=akunincome  style=width:155px onchange=getket('dana') >'".$optaruskas."'</select> 
								<img onclick=\"z.elSearch('akunincome',event)\" class=resicon src=images/onebit_02.png style=position:relative;top:5px>
							</td>    
						</tr>
						<tr>
							<td>".$_SESSION['lang']['keterangan']."</td>    
							<td>:</td>
							<td align=left>
							<select id=ketincome  style=width:155px >'".$opt."'</select></td>    
						</tr>
						<tr>
							<td>".$_SESSION['lang']['satuan']."</td>
							<td>:</td>
							<td><select id=satincome style=width:155px>'".$optsat."'</select></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kuantitas']."</td>
							<td>:</td>
							<td><input type=text id=fisikincome onkeyup=totalincome() onkeypress='return angka_doang(event)' class=myinputtextnumber  style=width:150px ></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['rupiahsatuan']."</td>
							<td>:</td>
							<td><input type=text id=rupsatincome onkeyup=totalincome() onkeypress='return angka_doang(event)' class=myinputtextnumber  style=width:150px ></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['total']."</td>
							<td>:</td>
							<td id=totincome align=center></td>
						</tr>";
					// if ($tipe=='HOLDING') { (DISURUH DILEPAS OLEH BANG INDRA BY GALANG)
						$stream.="<tr>
							<td></td>
							<td></td>
							<td><button class=mybutton onclick=saveincome()>".$_SESSION['lang']['save']."</button>
							<button class=mybutton onclick=batalincome()>".$_SESSION['lang']['cancel']."</button></td>
							<input type=hidden id=methodincome value='saveincome'>
						</tr>";
					// }
					
        $stream.="</table></fieldset>";
		$notrankas2=$explnopdo[0].'/'.$explnopdo[2].'/INCOME'.'/'.$explnopdo[3].'/002';
		$stream.="<fieldset style=float:left><legend><b>Otomatis</b></legend>";
		           $stream.="".$_SESSION['lang']['notransaksi']."  :  <input type=text id=notranincome2 disabled value='".$notrankas2."' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext>";

		if ($barht['tipe']=='HOLDING') {
			$stream.="<button class=mybutton onclick=saveincome2()>".$_SESSION['lang']['proses']."</button></td>
							<input type=hidden id=methodincome2 value='saveincome2'>";
		}
		
        $stream.="</fieldset>";
        $stream.="</fieldset>";
		echo $stream;
    break;
    #################################################
    #################################################
    #################################################

    case 'getrekening':
    	$optbank="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if ($noakunpil=='1110101') {
            $str = "select * from ".$dbname.".keu_5akunbank where pemilik='".$unit."'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $wheredz =" kodebank='".$bar['namabank']."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
                if ($rekeningbank==$bar['noakun']) {
                	$optbank.="<option value='".$bar['noakun']."' selected>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
                }else{
                	$optbank.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
                }
                
            }
		}

		echo $optbank;

    break;

    case 'getket':
    	$optket="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    	$str="select keterangan,id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$akunkas."'";
    	// exit('warning : '.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if ($ketkas!='') {
				$optket.="<option value=".$bar['id_ket']." selected>".$bar['keterangan']."</option>";
			}else{
				$optket.="<option value=".$bar['id_ket'].">".$bar['keterangan']."</option>";
			}
    		
		}
		echo $optket;
    break;


    case 'datajumlah':

    	$note="";
    	$jumlahbaris=array();
    	$periodesblm= date("Y-m",strtotime("-2 Month",strtotime($per)));

    	$str="select keterangan1 as notransaksi,noaruskas,sum(a.jumlah) as nilairupiah,b.rekening as rekening from ".$dbname.".keu_kasbankdt a 
    			left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi where noakun2a='".$noakunkas."' and a.kodeorg='".$unit."' 
    			and left(b.tanggal,7)<'".$per."' and left(b.tanggal,7)>'".$periodesblm."' and a.tipetransaksi='K' and noaruskas<>'' and noaruskas 
    			not in ('11700','11800')  and keterangan1 not in (select nodocument FROM ".$dbname.".`keu_pdodt`) and a.jumlah>0 group by noaruskas";
    	
    	//exit('Warning : '.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()) {
			#cek apakah HT sudah di-insert
			$str2="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and bagian='".$bag."' and tipepdo='KAS' limit 1";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2=$res2->fetch();
				$cekht=$bar2['jumlah'];
			if($cekht<=0)
			{
				$strht="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('".$nopdo."', '".$notrankas."', '".$unit."', '".$per."', 'KAS','".$_SESSION['standard']['userid']."')";
				try{
					$owlPDO->exec($strht);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}

			if ($noakunkas!='1110101') {
				$bar['rekening']='';
			}

			$strbrs="select count(*) as jumlahbaris,noakun from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and (notransaksi like '%PJD%' or notransaksi like '%BBM%') and noakun in ('10403','10404') group by noakun";
			$resbrs=$owlPDO->query($strbrs) or die(print " Gagal: ".PDOException::getMessage());
			$resbrs->setFetchMode(PDO::FETCH_ASSOC);
			while ($barbrs=$resbrs->fetch()) {
				$jumlahbaris[$barbrs['noakun']]=$barbrs['jumlahbaris'];
			}

			if ($bar['noaruskas']==$aruskasbbm || $bar['noaruskas']==$aruskaspjd) {
				if ($jumlahbaris[$bar['noaruskas']]==0) {
					$note="biaya BBM/PJD pada tab Bahan Bakar / Perjalanan Dinas belum diinput. Sedangkan ada biaya BBM dan PJD pada kas."; 	
					continue;
				}
			}

			#cek nourut
	        $str1="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notrankas."'"
	            . " order by nourut desc limit 1 ";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1=$res1->fetch();
	        $nourutbaru=$bar1['nourut']+1;
	        $strin="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`,`noakunkas`, `nourut`, `noakun`, `rincian`,
	                `tanggal`, `satuan`,`fisik`, `rupiah`, `nodocument`, `rekeningbank`) 
	                VALUES ('".$nopdo."', '".$notrankas."', '".$noakunkas."', '".$nourutbaru."', '".$bar['noaruskas']."',
	                '".$tglawalper."', '".$satkas."', '".$fisikkas."','".$bar['nilairupiah']."','".$bar['notransaksi']."','".$bar['rekening']."')";
			// exit('Warning : '.$strin);
			try{
				$owlPDO->exec($strin);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}
		
		#= arus kas
		$where='';
		if ($noakunkas=='1112102') {
			#KK
			$where.=" and (akses_rekening='KK' or akses_rekening='') ";
		}else{
			$where.=" and (akses_rekening='KB' or akses_rekening='')";
		}
		if($optTipe[$unit]=='HOLDING'){
			$where.=" and pemilik_aruskas in ('GLOBAL','HOLDING') and status='1' and level='3'";
		} else if($optTipe[$unit]=='KANWIL'){
			$where.=" and pemilik_aruskas in ('GLOBAL','KANWIL') and status='1' and level='3'";
		}else{
			$where.=" and pemilik_aruskas in ('GLOBAL','UNIT') and status='1' and level='3' and tipetransaksi='K'";
		}

		$optaruskas="";
		$optaruskas.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "SELECT * FROM ".$dbname.".keu_5aruskas where 1=1 ".$where." order by noaruskas asc";
		// exit('warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
		}

		$optbank="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if ($noakunkas=='1110101') {
            $str = "select * from ".$dbname.".keu_5akunbank where pemilik='".$unit."'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $wheredz =" kodebank='".$bar['namabank']."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
                $optbank.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
            }
		}

		echo $optaruskas."##".$optbank."##".$note;

    break;

    case'deletekas':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notrankas."' "
                        . " and nourut='".$nourutkas."' ";
        //exit('Error : '.$str);
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'updatekas':

    	##ambil jumlah rupiah yg diedit
    	$jumlahrupiahkasedit=0;
     	$str="select rupiah as jumlahrupiahkasedit from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notrankas."' and nourut='".$nourutkas ."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$jumlahrupiahkasedit=$bar['jumlahrupiahkasedit'];

    	$jumlahrupiahbbm=0;
	    $jumlahrupiahpjd=0;
	    $$jumlahrupiahkas=0;
	    if ($akunkas=='10403') {
	     	$str="select sum(rupiah) as jumlahrupiahbbm from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%BBM%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$jumlahrupiahbbm=$bar['jumlahrupiahbbm'];

	     	$str="select sum(rupiah) as jumlahrupiahkas from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%KAS%' and noakun='".$akunkas."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$jumlahrupiahkas=$bar['jumlahrupiahkas']-$jumlahrupiahkasedit+$totkas;

			if ($jumlahrupiahbbm<=0 || is_null($jumlahrupiahbbm) || $jumlahrupiahbbm=='') {
				exit('warning : Bahan bakar belum diinput.');
			}

			if ($jumlahrupiahkas>$jumlahrupiahbbm) {
				exit('warning : Jumlah kas melebihi dari tab bahan bakar.');
			}

	    } 

	    if ($akunkas=='10404') {
	     	$str="select sum(rupiah) as jumlahrupiahpjd from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%PJD%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$jumlahrupiahpjd=$bar['jumlahrupiahpjd'];

	     	$str="select sum(rupiah) as jumlahrupiahkas from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%KAS%' and noakun='".$akunkas."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$jumlahrupiahkas=$bar['jumlahrupiahkas']-$jumlahrupiahkasedit+$totkas;

			if ($jumlahrupiahpjd<=0 || is_null($jumlahrupiahpjd) || $jumlahrupiahpjd=='') {
				exit('warning : Perjalanan dinas belum diinput.');
			}

			if ($jumlahrupiahkas>$jumlahrupiahpjd) {
				exit('warning : Jumlah kas melebihi dari tab perjalanan dinas.');
			}
	    }

        $str="update ".$dbname.".keu_pdodt set noakun='".$akunkas."',rincian='".$ketkas."',tanggal='".$tglawalper."',
        satuan='".$satkas."',fisik='".$fisikkas."',rupiah='".$totkas."',rupiahdiajukan='".$totkas."'
            where nopdo='".$nopdo."' and notransaksi='".$notrankas."' and nourut='".$nourutkas ."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;    
    case'savekasx':

    	
		#cek apakah HT sudah di-insert
		$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='KAS' limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$cekht=$bar['jumlah'];
		if($cekht<=0)
		{
			$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('".$nopdo."', '".$notrankas."', '".$unit."', '".$per."', 'KAS','".$_SESSION['standard']['userid']."')";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}	
        #cek nourut
        $str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notrankas."'"
            . " order by nourut desc limit 1 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
        $nourutbaru=$bar['nourut']+1;
        $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`,`noakun`,`noaruskas`, `nourut`,`noakunkas`, `rincian`,
                `tanggal`, `rupiah`, `rupiahdiajukan`, `rekeningbank`, `nodocument`) 
                VALUES ('".$nopdo."', '".$notrankas."', '".$noakunbayarx."', '".$noaruskasx."', '".$nourutbaru."', '".$noakunkasx."', '".$ketkasx."',
                '".$tglawalper."','".$jumlahkasx."','".$jumlahkasx."','".$rekeningbank."','".$notransaksix."')";
		#exit("error".$str);
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'listkas':
		// style='float:left;'
       $stream.="<fieldset><legend><b>List transaksi Kas dan Bank</b></legend >
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td align=center width=30px>".$_SESSION['lang']['nourut']."</td>    
                        <td align=center>Nomor Document</td>    
                        <td align=center>Akun KAS</td>  
                        <td align=center>".$_SESSION['lang']['noaruskas']."</td>      
                        <td align=center>Akun Biaya</td>    
                        <td align=center>".$_SESSION['lang']['keterangan']."</td>    
                        <td align=center>Nilai Rupiah</td>
                        <td align=center width=50px>".$_SESSION['lang']['action']."</td>
                    </tr>
                </thead>";



        //$notrankas=$explnopdo[0].'/'.$explnopdo[2].'/KAS/001';
        $str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%KAS%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;

            $rupiahdiajukan=0;
            if ($bar['rupiahdiajukan']==0) {
            	$rupiahdiajukan=$bar['rupiah'];
            }else{
            	$rupiahdiajukan=$bar['rupiahdiajukan'];
            }

            $stream.="<tr class=rowcontent>
                        <td align=center>".$no."</td>
                        <td align=center>".$bar['nodocument']."</td> 
                        <td align=center>".@$arrnmakun[$bar['noakunkas']]."</td>    
                        <td align=left>".@$arrnmaruskas[$bar['noaruskas']]."</td>   
                        <td align=center>".@$arrnmakun[$bar['noakun']]."</td>       
                        <td align=left>".$bar['rincian']."</td>  
                        <td align=right>".@number_format($rupiahdiajukan)."</td>
                        <td align=center>
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                                onclick=\"deletekas('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."');\">
                        <img src=images/addplus.png title='Upload' class=resicon onclick=showupload('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."',event)>
                        </td> 
                	</tr>";    
        }
        echo $stream;
    break;

    case'savekasdisetujui':
        $str="update ".$dbname.".keu_pdodt set rupiah='".$kasdisetujui."'
            where nopdo='".$nopdo."' and notransaksi='".$notrankas."' and nourut='".$nourutkas ."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;

    case'detailkas':
    	$streams="<fieldset><legend><b>List Data Kas / Tunai</b></legend >
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td align=center width=30px>".$_SESSION['lang']['nourut']."</td>  
                        <td align=center>".$_SESSION['lang']['notransaksi']."</td>    
                        <td align=center>Akun KAS</td>  
                        <td align=center>".$_SESSION['lang']['noaruskas']."</td>
                        <td align=center>Akun Biaya</td>          
                        <td align=center>".$_SESSION['lang']['keterangan']."</td>    
                        <td align=center>Nilai Rupiah</td>
                        <td align=center width=50px>".$_SESSION['lang']['action']."</td>
                    </tr>
                </thead>
                <tbody>";
    	$wherex='';
    	$periodesblm= date("Y-m",strtotime("-1 Month",strtotime($per)));
        if($bag=='I')
        {
        	$wherex=" and c.periode='".$periodesblm."' and c.tanggal > '".$periodesblm."-15' ";
        }
        else
        {
        	$wherex=" and c.periode='".$per."' and c.tanggal <= ".$per."-15 ";
        }
        $str="select concat(nodocument,'/',noakun) as nounik from ".$dbname.".keu_pdodt where  notransaksi like '%KAS%' and nopdo like '%".$unit."%'";
        //echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$nounik='';
		while($bar=$res->fetch()){
			if($nounik=='')
			{
				$nounik="'".$bar['nounik']."'";
			}
			else
			{
				$nounik.=",'".$bar['nounik']."'";
			}
		}
		if($nounik!=='')
		{
			$wherex.= "and concat(a.notransaksi,'/',c.noakun) not in (".$nounik.") ";
		}
		$str="select a.notransaksi,a.noakun as noakunkas, c.noakun as noakunbayar , a.tipetransaksi, c.keterangan, c.noaruskas,sum(c.jumlah) as jumlah from ".$dbname.".keu_kasbankht a 
		left join ".$dbname.".keu_jurnaldt_vw c on a.notransaksi=c.noreferensi 
		where a.tipetransaksi='K' and a.noakun='".$noakunkas."' and a.kodeorg='".$unit."' and c.noakun!='".$noakunkas."' ".$wherex." group by a.notransaksi,c.noakun";
		//exit($str.'Error');
		$nox=0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nox+=1;
			$streams.="
                    <tr class=rowcontent id=row".$nox.">
                        <td align=center width=30px>".$nox."</td>    
                        <td align=center id='notransaksix_".$nox."'>".$bar['notransaksi']."</td>
                        <td align=center id='noakunkasx_".$nox."' hidden>".$bar['noakunkas']."</td>  
                        <td align=center>".$arrnmakun[$bar['noakunkas']]."</td>  
                        <td align=center id='noaruskasx_".$nox."' hidden>".$bar['noaruskas']."</td>  
                        <td align=center>".$arrnmaruskas[$bar['noaruskas']]."</td>  
                        <td align=center id='noakunbayarx_".$nox."' hidden>".$bar['noakunbayar']."</td> 
                        <td align=cente>".$arrnmakun[$bar['noakunbayar']]."</td>          
                        <td align=center id='ketkasx_".$nox."'>".$bar['keterangan']."</td>  
                        <td align=center id='jumlahkasx_".$nox."'>".$bar['jumlah']."</td>  
                        <td align=center width=50px><input type='checkbox' id='kascheck_".$nox."'></td>
                    </tr>";
		}
		$streams.="
                    <tr class=rowcontent>
                        <td colspan=7></td> 
                        <td align=center width=50px><button id='simpankas' class='mybutton' onclick='simpankas(".$nox.")'' >Simpan</button></td>
                    </tr>";
		$streams.="</tbody></fieldset>";
		echo $streams;
    break;
    ########################################################
    #################  T A B   U P A H  ####################
    ########################################################
    case'deleteupah':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$noupah."' "
                        . " and nourut='".$nourutupah."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
    case'saveupah':

    	if ($noakunupah=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunupah=='1110101') {
			if ($rekeningbankupah=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

        $str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$noupah."'"
            . " order by nourut desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
            $nourutbaru=$bar['nourut']+1;
        $rincian=$arrtipekar[$tkupah]." - ".$arrcompgaji[$comp];    
        if($comp!=''){
            if($upahawal!='0'){
				#cek apakah HT sudah di-insert
				$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."'
						and notransaksi='".$noupah."' and tipepdo='UPAH' limit 1";

				//exit($str.'Error');
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$cekht=$bar['jumlah'];
				if($cekht<=0){
					$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
					VALUES ('".$nopdo."', '".$noupah."', '".$unit."', '".$per."', 'UPAH','".$_SESSION['standard']['userid']."')";
					try{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) {
					   print " Gagal  !: " . $e->getMessage() . "\n"; 
					   die(); 
					}
				}
                #delete 1st
                $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$noupah."' "
                        . " and tipekaryawan='".$tkupah."' and divisi='".$divisiupah."' and komponengaji='".$comp."' ";
                try{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
                $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                        `divisi`,`tanggal`,`tipekaryawan`, `komponengaji`,`jumlahorang`, `satuan`, `fisikreal`,`fisik`,
                        `rupiahreal`, `rupiah`,`noakunkas`, `rekeningbank`) 
                        VALUES ('".$nopdo."', '".$noupah."', '".$nourutbaru."', '2160101', '".$rincian."',
                                '".$divisiupah."','".$tglupah."','".$tkupah."', '".$comp."', '".$orang."', 'HK','".$hkawal."','".$hkawal."',
                                '".$upahawal."', '".$upahawal."','".$noakunupah."', '".$rekeningbankupah."')";
                try{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
            }
        }
    break;    
    case'nopdo':
		##cek apakah data sudah posting atau belum
		$str=" select count(*) as posting from ".$dbname.".keu_pdoht where  kodeorg='".$unit."' and periode='".$per."' and bagian='".$bag."' and posting=1 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['posting']>0){
			exit("Warning : PDO untuk ".$unit." di bulan-tahun ".$per." periode ".$bag." sudah di posting ");
		}
        if($nopdo==''){
            $str=" select nopdo from ".$dbname.".keu_pdoht where  kodeorg='".$unit."' and periode='".$per."' and bagian='".$bag."' "
                    . " order by nopdo desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
                $nopdolama=$bar['nopdo'];
			if($nopdolama==''){
				$str=" select * from ".$dbname.".keu_pdoht where notransaksi like '".$thn."%' order by nopdo desc limit 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$notran=$bar['nopdo'];
				$num=  explode('/', $notran);
				$num=$num[4]+1;
				if($num<10)
					$num='00'.$num;   
				else if($num<100)
				   $num='0'.$num;
				else
				   $num=$num;
				$per=  str_replace('-', '', $per);
				$nopdobaru=$per.'/PDO/'.$unit.'/'.$bag.'/'.$num;
			}
			else{
				$nopdobaru=$nopdolama;
			}
        }
        else{
            $nopdobaru=$nopdo;
        }
        echo $nopdobaru;
    break;
    case'noupah':
        if($perawal!=$per){
            exit("Error: Tanggal diluar periode \nPeriode aktip adalah : ".$per. "");
        }
        $thn=substr($per,0,4);
        $per=  str_replace('-', '', $per);
        if($noupah=='')  {
            ##cek apakah sudah pernah ada data diinput
            ##param : nopdo - periode - divisi - tipekaryawan
            $str=" select notransaksi from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%UPAH%'"
                    . " and divisi='".$divisiupah."' and tipekaryawan='".$tkupah."' "
                    . " order by notransaksi desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
                $nolama=$bar['notransaksi'];
            if($nolama==''){
                //$str=" select * from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and tipepdo='UPAH' order by nopdo desc limit 1 ";
                $str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%UPAH%' "
                        . " order by notransaksi desc limit 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
                    $notran=$bar['notransaksi'];
                $num=  explode('/', $notran);
                $num=@$num[3]+1;
                if($num<10)
                    $num='00'.$num;   
                else if($num<100)
                   $num='0'.$num;
                else
                   $num=$num;
                //'201506/DUKE/PAD/001
                $noupahbaru=$per.'/'.$unit.'/UPAH/'.'/'.$bag.'/'.$num;
            } else {
                $noupahbaru=$nolama;
            }
        } else {
            $noupahbaru=$noupah;
        }
        echo $noupahbaru;
    break;
    case'detailupah':
		if($bag=='I'){
    	$periodex= date("m-Y",strtotime("-1 Month",strtotime($per)));
    	$periodexz= date("Y-m",strtotime("-1 Month",strtotime($per)));
		}
		else
		{
    	$periodex= date("m-Y",strtotime("0 Month",strtotime($per)));
    	$periodexz= date("Y-m",strtotime("0 Month",strtotime($per)));
		}
        $stream="";
        $stream.="<fieldset><legend><b>Detail Input</b></legend>";
        $stream.="
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                    <thead>
                        <tr>
                            <td align=center rowspan=2 width=30px >".$_SESSION['lang']['nourut']."</td>    
                            <td align=center rowspan=2 width=150px >".$_SESSION['lang']['namakomponen']."</td>    
                            <td align=center rowspan=2 width=50px >".$_SESSION['lang']['orang']."</td>
                            <td align=center colspan=2 width=50px >Real periode ".$periodex."</td>
                            <td align=center rowspan=2 width=50px >".$_SESSION['lang']['action']."</td>
                        </tr>
                        <tr>
                            <td align=center width=80px >".$_SESSION['lang']['hk']."</td>
                            <td align=center width=130px >".$_SESSION['lang']['rupiah']."</td>
                        </tr>
                    </thead>";
         //exit($stream);
        $lendivisi=strlen($divisiupah);
        	if($bag=='I')
        	{
        		$arrkompo= array();

        		$str="select * "
					. " from ".$dbname.".sdm_ho_component  "
					. " where id!='1' ";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						$arrkompo[$bar['id']]=$bar['plus'];
					}

				$wherexz='';
				if($unit!=$divisiupah)
				{
					$wherexz=" and b.subbagian='".$divisiupah."' ";
				}
				#ambil dari sdm_gaji
        		$str="select a.karyawanid,a.periodegaji,a.idkomponen, a.jumlah "
					. " from ".$dbname.".sdm_gaji a "
					. " left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid "
					. " where a.kodeorg='".$unit."' and b.lokasitugas='".$unit."' and "
					. " b.tipekaryawan='".$tkupah."' and a.idkomponen!='1' and a.periodegaji='".$periodexz."' ".$wherexz." ";
					//echo $str;
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						if($bar['karyawanid']!=''){
							$idcomp[$bar['idkomponen']]=$bar['idkomponen'];
							$karyawanid[$bar['idkomponen']][$bar['karyawanid']]=$bar['karyawanid'];
							@$rpawal[$bar['idkomponen']]+=$bar['jumlah'];
						}
					}
				$jumlahorang=array();
				foreach ($karyawanid as $key => $val) {
					foreach ($val as $key2 => $val2) {
						$jumlahorang[$key]+=1;
					}
				}
				
		        //array_multisort($idcomp,SORT_ASC);
		        $no=0;
		        $nox=0;
		        $stream.="<tr class=rowcontent >";
			            $stream.="<td align=center colspan='7' >Komponen Penambah</td>";
			            $stream.="</tr>";
		        foreach($idcomp as $idgaji)
		        {	//echo $idgaji.'<br>';
		            if($rpawal[$idgaji]>0 and $arrkompo[$idgaji]=='1')
		            {
			            $no+=1;
			            $stream.="<tr class=rowcontent id=row".$no.">";
			            $stream.="<td align=center>".$nox."</td>";
			            $stream.="<td align=left hidden><input type=text value='".$idgaji."' id=comp".$no." onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
			            $stream.="<td align=left >".$arrcompgaji[$idgaji]."</td>";
			            $stream.="<td align=right id=orang".$no.">".@$jumlahorang[$idgaji]."</td>";
			            $stream.="<td align=right id=hkawal".$no.">".@$jumlahorang[$idgaji]."</td>";
			            $stream.="<td align=right id=upahawal".$no.">".$rpawal[$idgaji]."</td>";
			            $stream.="<td align=center><button class=mybutton onclick=saveupah(".$no.") >".$_SESSION['lang']['save']."</button></td>";
			            $stream.="</tr>";

		            }
		        }
		         $stream.="<tr class=rowcontent >";
			            $stream.="<td align=center colspan='7' >Komponen Pengurang</td>";
			            $stream.="</tr>";

		        $nox=0;
		        foreach($arrkompo['0'] as $idgaji)
		        {
		            if($rpawal[$idgaji]>0 and $arrkompo[$idgaji]=='0')
		            {
			            $no+=1;
			            $stream.="<tr class=rowcontent id=row".$no.">";
			            $stream.="<td align=center>".$nox."</td>";
			            $stream.="<td align=left hidden><input type=text value='".$idgaji."' id=comp".$no." onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
			            $stream.="<td align=left >".$arrcompgaji[$idgaji]."</td>";
			            $stream.="<td align=right id=orang".$no.">".@$jumlahorang[$idgaji]."</td>";
			            $stream.="<td align=right id=hkawal".$no.">".@$jumlahorang[$idgaji]."</td>";
			            $stream.="<td align=right id=upahawal".$no.">".$rpawal[$idgaji]."</td>";
			            $stream.="<td align=center><button class=mybutton onclick=saveupah(".$no.") >".$_SESSION['lang']['save']."</button></td>";
			            $stream.="</tr>";

		            }
		        }

		        $stream.="<tr class=rowcontent>";
		        $stream.="<td colspan=10 align=center><button class=mybutton onclick=saveallupah(".$no.");>".$_SESSION['lang']['save']." ".$_SESSION['lang']['all']."</button></td>";
		        $stream.="</tr>";  

		        
        	}
        	else
        	{
        		//ambil GAPOK
        		$wherexz='';
        		if($unit!=$divisiupah)
        		{
        			$wherexz=" and b.subbagian='".$divisiupah."' ";
        		}

        		$idgaji=1;
        		$jumlahgapok=0;
        		$jumlahorang=0;
					$str = "select a.jumlah as gapok,a.karyawanid,b.namakaryawan,b.tipekaryawan,b.lokasitugas,b.subbagian from ".$dbname.".sdm_5gajipokok a left join 
						   ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
						   where a.tahun=".$thn." and b.tipekaryawan='".$tkupah."' 
						   and a.idkomponen in (select id from ".$dbname.".sdm_ho_component where id=1)
						   and b.lokasitugas='".$unit."' ".$subbagian."  and b.statuskaryawan != 'Keluar'
						   and  (b.tanggalkeluar>='".$tglawalper."' or b.tanggalkeluar='0000-00-00') and b.lokasitugas='".$unit."' ".$wherexz." ";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						while($bar=$res->fetch()){
							if($bar['karyawanid']!=''){
								$jumlahgapok+=$bar['gapok'];
								$jumlahorang+=1;
							}
					}
					$no=1;
					$stream.="<tr class=rowcontent id=row".$no.">";
		            $stream.="<td align=center>".$no."</td>";
		            $stream.="<td align=left hidden><input type=text value='".$idgaji."' id=comp".$no." onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
		            $stream.="<td align=left >".$arrcompgaji[$idgaji]."</td>";
		            $stream.="<td align=right id=orang".$no.">".$jumlahorang."</td>";
		            $stream.="<td align=right id=hkawal".$no.">".$jumlahorang."</td>";
		            $stream.="<td align=right id=upahawal".$no.">".$jumlahgapok."</td>";
		            if($jumlahgapok>0)
		            {
		            $stream.="<td align=center><button class=mybutton onclick=saveupah(".$no.") >".$_SESSION['lang']['save']."</button></td>";
		            }
		            else
		            {
		            $stream.="<td align=center></td>";
		            }
		            $stream.="</tr>";
        	}
			   $stream.="
                </table>";
        echo $stream;
    break;   
    case'listupah':
      // style='float:left;'
       $stream.="<fieldset><legend><b>List Data ".$_SESSION['lang']['upah']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:80%>
                    <thead>
                        <tr>
                            <td align=center rowspan=2 width=30px >".$_SESSION['lang']['nourut']."</td>    
                            <td align=center rowspan=2 width=100px >".$_SESSION['lang']['noakun']."</td>
                            <td align=center rowspan=2 width=100px >".$_SESSION['lang']['namakomponen']."</td>
                            <td align=center rowspan=2 width=50px >".$_SESSION['lang']['tipekaryawan']."</td>  
                            <td align=center rowspan=2 width=200px >".$_SESSION['lang']['divisi']."</td>    
                            <td align=center rowspan=2 width=30px >".$_SESSION['lang']['orang']."</td>
                            <td align=center colspan=2 width=50px >Real</td>
                            <td align=center rowspan=2 width=50px >".$_SESSION['lang']['action']."</td>
                        </tr>
                        <tr>
                            <td align=center width=30px >".$_SESSION['lang']['hk']."</td>
                            <td align=center width=70px >".$_SESSION['lang']['rupiah']."</td>
                        </tr>
                    </thead>";
        ##ambil data yang udah ada
        $str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%UPAH%' 
				order by divisi,tipekaryawan,komponengaji asc	";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $idcomp[$bar['komponengaji']]=$bar['komponengaji'];
            $tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
            $kddivisi[$bar['divisi']]=$bar['divisi'];
            $listtipekaryawan[$bar['komponengaji']][$bar['tipekaryawan']]=$bar['tipekaryawan'];
            $listkddivisi[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['divisi'];
            $rpawal[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['rupiahreal'];//premi # 32
            //$rpakhir[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['rupiah'];
            //$totalabsen[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['fisik'];
            $totalabsenreal[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['fisikreal'];
            $orang[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['jumlahorang'];
            $nopdolist[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['nopdo'];
            $noupahlist[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['notransaksi'];
            $nourut[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['nourut'];
            $tanggal[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['tanggal'];
            $noakunhutang[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['noakunkas'];
            $rekeningbankhutang[$bar['komponengaji']][$bar['tipekaryawan']][$bar['divisi']]=$bar['rekeningbank'];
        }
		//array_multisort($kddivisi,SORT_ASC);
		//array_multisort($tipekaryawan,SORT_ASC);
		if(!empty($idcomp))
		{
		array_multisort($idcomp,SORT_ASC);		
			foreach($idcomp as $idgaji)
			{
				foreach($tipekaryawan as $tipekar)
				{
					if(@$listtipekaryawan[$idgaji][$tipekar]!='')
					{
						foreach($kddivisi as $divisi)
						{
							if(@$listkddivisi[$idgaji][$tipekar][$divisi]!='')
							{
								//$totalabsentengah[$idgaji][$tipekar][$divisi]=$totalabsen[$idgaji][$tipekar][$divisi]-$totalabsenreal[$idgaji][$tipekar][$divisi];
								//$rptengah[$idgaji][$tipekar][$divisi]=$rpakhir[$idgaji][$tipekar][$divisi]-$rpawal[$idgaji][$tipekar][$divisi];
								$no+=1;
								$stream.="<tr class=rowcontent>";
								$stream.="<td align=center>".$no."</td>";
								$stream.="<td align=left >".$arrnmakun[$noakunhutang[$idgaji][$tipekar][$divisi]]."</td>";
								$stream.="<td align=left >".$arrcompgaji[$idgaji]."</td>";
								$stream.="<td align=center >".$arrtipekar[$tipekar]."</td>";
								$stream.="<td align=left >".$arrnmorg[$divisi]."</td>";
								$stream.="<td align=right id=orang".$no.">".@number_format($orang[$idgaji][$tipekar][$divisi])."</td>";
								$stream.="<td align=right>".@number_format($totalabsenreal[$idgaji][$tipekar][$divisi])."</td>";
								$stream.="<td align=right>".@number_format($rpawal[$idgaji][$tipekar][$divisi])."</td>";
							   $stream.="
								<td align=center>
									<img src=images/application/application_delete.png class=zImgBtn title='Delete' 
										 onclick=\"deleteupah('".$nopdolist[$idgaji][$tipekar][$divisi]."','".$noupahlist[$idgaji][$tipekar][$divisi]."','".$nourut[$idgaji][$tipekar][$divisi]."');\">
								</td>
								";
								$stream.="</tr>";
							}
						}
					}
				}
			}
		}
        $stream.="</table></fieldset>";
        echo $stream;
    break;
    case'loaddata':
        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
		$where="";

        if($thnsch!='') {
			$where.=" and periode like '".$thnsch."%' ";
        }
        $str="select count(*) as jmlhrow from ".$dbname.".keu_pdoht where kodeorg in (".getOrgDetail(2).") ".$where." group by nopdo  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	
        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".keu_pdoht where kodeorg in (".getOrgDetail(2).") ".$where." group by nopdo order by nopdo desc  limit ".$offset.",".$limit." ";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
			
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=center>".$bar['periode']."</td>";
			$tab.="<td align=left>".$bar['nopdo']."</td>";
            $tab.="
            <td align=right>";
			if($bar['posting']==1 and $bar['approval']==1){
				$tab.="
					<img src=images/skyblue/posted.png class=zImgOffBtn title='Posted');\">  
					<img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                     onclick=\"detail('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','html','event');\">  
					<img src=images/excel.jpg class=resicon title='MS.Excel' onclick=\"detailexcel('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','excel','event');\">       					
				";
			}
			else if($bar['posting']==0 and $bar['approval']!=1 and $bar['approval']!=9){
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"edit('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."');\">
                <img src='images/skyblue/submit.jpg' class='resicon' height='30' title='Ajukan ???'' onclick=form_ajukan('".$bar['nopdo']."','".$bar['kodeorg']."','1');>          
                <img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                     onclick=\"detail('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','html','event');\">
                <img src=images/excel.jpg class=resicon title='MS.Excel' 
                	 onclick=\"detailexcel('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','excel','event');\">         
				";
			}
			else if($bar['posting']==0 and $bar['approval']==9){
				$tab.="    
                <img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                     onclick=\"detail('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','html','event');\">
                <img src=images/excel.jpg class=resicon title='MS.Excel' 
                	 onclick=\"detailexcel('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','excel','event');\">         
				";
			}
			else{
				$tab.="
                <img src=images/skyblue/posting.png class=zImgBtn title='Posting' 
                     onclick=\"posting('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."');\">           
                <img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                     onclick=\"detail('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','html','event');\">
                <img src=images/excel.jpg class=resicon title='MS.Excel' 
                	 onclick=\"detailexcel('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','excel','event');\">         
				";
			}
            $tab.="</td>";
            $tab.="</tr>";
        }
        $totrows=ceil($jlhbrs/$limit);
        if($totrows==0){
                $totrows=1;
        }
        $isiRow='';
        for($er=1;$er<=$totrows;$er++){
                $sel = ($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd="
            <tr><td colspan=5 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
    case'form_ajukan';
		$kodeorg=$unit;
		
		
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='PDO' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";// exit('error'.$str);
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
	
	#= cari unit
	try {
	$owlPDO->beginTransaction();
	
		//update flag menjadi 1
        $str = "update " . $dbname . ".keu_pdoht set approval='9' where nopdo = '" . $notransaksi . "'";
		$owlPDO->exec($str);
		//insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','".$notransaksi."','PDO','1','" . $kepada."','0','','','')";
		$owlPDO->exec($str);
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
	case'deletehead':
		$str="delete from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and kodeorg='".$unit."' and periode='".$per."'  ";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
    case'posting':

    	$jumlahrupiahbbm=0;
	    $jumlahrupiahpjd=0;
	    $jumlahrupiahkas=0;
    	$strbrs="select count(*) as jumlahbaris,noakun from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%KAS%' and noakun in ('10403','10404') group by noakun";
		// exit('warning : '.$strbrs);
		$resbrs=$owlPDO->query($strbrs) or die(print " Gagal: ".PDOException::getMessage());
		$resbrs->setFetchMode(PDO::FETCH_ASSOC);
		while ($barbrs=$resbrs->fetch()) {
			if ($barbrs['jumlahbaris']>0) {
				if($bar['noakun']=='10403'){
					$str="select sum(rupiah) as jumlahrupiahbbm from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%BBM%'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
					$jumlahrupiahbbm=floatval($bar['jumlahrupiahbbm']);

			     	$str="select sum(rupiah) as jumlahrupiahkas from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%KAS%' and noakun='10403'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
					$jumlahrupiahkas=floatval($bar['jumlahrupiahkas']);

					if ($jumlahrupiahbbm>0 && $jumlahrupiahkas==0) {
						exit('warning : akun bbm pada tab kas belum diinput.');
					}
					if ($jumlahrupiahkas<$jumlahrupiahbbm) {
						exit('warning : Jumlah bbm pada tab kas < tab bahan bakar.');
					}
				}
		     	
				if($bar['noakun']=='10404'){
					$str="select sum(rupiah) as jumlahrupiahbbm from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%PJD%'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
					$jumlahrupiahbbm=floatval($bar['jumlahrupiahbbm']);

			     	$str="select sum(rupiah) as jumlahrupiahkas from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and left(tanggal,7)='".$per."' and notransaksi like '%KAS%' and noakun='10404'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
					$jumlahrupiahkas=floatval($bar['jumlahrupiahkas']);

					if ($jumlahrupiahbbm>0 && $jumlahrupiahkas==0) {
						exit('warning : akun pjd pada tab kas belum diinput.');
					}
					if ($jumlahrupiahkas<$jumlahrupiahbbm) {
						exit('warning : Jumlah pjd pada tab kas < tab perjalanan dinas.');
					}
				}
				

			}
		}

		#ambil data HT
		$nama = array('KAS' =>$_SESSION['lang']['kas'] ,'LNN' =>$_SESSION['lang']['lain'] ,'PJD' =>$_SESSION['lang']['perdin']);
        $str="select distinct tipepdo from ".$dbname.".keu_pdo_vw where  nopdo='".$nopdo."' and tipepdo in ('KAS','LNN','PJD')";
		$res=fetchData($str);
		foreach($res as $key=>$val){
			$strcek="select * from ".$dbname.".listfileupload where  notransaksi like '".$nopdo."%".$val['tipepdo']."%'";
			$rescek=fetchData($strcek);
			if(count($rescek)==0){
				exit('warning: Tab '.$nama[$val['tipepdo']].' wajib upload file');
			}
		}    	

		$str="update  ".$dbname.".keu_pdoht set posting='1',postingby='".$_SESSION['standard']['userid']."'
				,postingtime=now() where nopdo='".$nopdo."' and kodeorg='".$unit."' and periode='".$per."'  ";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	
	
	
	case'listbbm':
		$stream.="<fieldset><legend><b>List ".$_SESSION['lang']['bbm']."</b></legend >
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center width=30px>".$_SESSION['lang']['nourut']."</td>    
					<td align=center>".$_SESSION['lang']['noakun']."</td>  
					<td align=center>".$_SESSION['lang']['aruskas']."</td>        
					<td align=center>".$_SESSION['lang']['keterangan']."</td>    
					<td align=center>".$_SESSION['lang']['satuan']."</td>
					<td align=center>".$_SESSION['lang']['vhc_jumlah_bbm']."</td>
					<td align=center>".$_SESSION['lang']['total']."</td>
					<td align=center width=30px>".$_SESSION['lang']['action']."</td>
				</tr>
			</thead>";
		
		$str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%BBM%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['rincian']."'");
			$no+=1;
            $stream.="<tr class=rowcontent>
                        <td align=center>".$no."</td>
                        <td align=center>".$bar['noakunkas']." - ".@$arrnmakun[$bar['noakunkas']]."</td>    
                        <td align=left>".$bar['noakun']." - ".@$arrnmaruskas[$bar['noakun']]."</td>        
                        <td align=left>".$optNmKar[$bar['rincian']]."</td>  
                        <td align=center>".$bar['satuan']."</td>
                        <td align=right>".@number_format($bar['fisik'])."</td>
                        <td align=right>".@number_format($bar['rupiah'])."</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                                onclick=\"editbbm('".$nopdo."','".$notranbbm."','".$bar['noakunkas']."','".$bar['rekeningbank']."');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                                onclick=\"deletebbm('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."');\">
                        </td>   
                </tr>";    
        }
        echo $stream;
    break;
	
	case'detailbbm':

		if ($noakunbbm=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunbbm=='1110101') {
			if ($rekeningbankbbm=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$stream="";
        $stream.="<fieldset><legend><b>Detail ".$_SESSION['lang']['hutang']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr>
                        <td align=center width=30px >".$_SESSION['lang']['nourut']."</td>    
                        <td align=center>".$_SESSION['lang']['notransaksi']."</td>    
                        <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
                        <td align=center>".$_SESSION['lang']['vhc_jumlah_bbm']."</td>
                        <td align=center>".$_SESSION['lang']['pembayaran']."</td>
						<td align=center rowspan=2 width=30px >".$_SESSION['lang']['action']."<br>
							<input type=checkbox id=cekallbbm onclick=cekallbbm()>
						</td>
                    </tr>
                </thead><tbody id=contentdetailbbm>";
			
		$str=" select nodocument from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbbm."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
            $posave[$bar['nodocument']]=1;
        }
		
		$str="select a.notransaksi,a.karyawanid,b.jlhbbm, b.hargatotal as dibayar from ".$dbname.".sdm_penggantiantransport a 
			left join ".$dbname.".sdm_penggantiantransportdt b on a.notransaksi=b.notransaksi 
			where a.kodeorg='".$unit."' and a.periode='".$per."'";
		$res=fetchData($str);
		$no=0;
		foreach($res as $key=>$val)
		{
			if(@$posave[$val['notransaksi']]==1)
			{
				$cek="checked=true";
            }
            else
			{
				$cek="";
            }
			$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
			$no+=1;
            $stream.="<tr class=rowcontent id=rowbbm".$no.">";
            $stream.="<td align=center>".$no."</td>";
            $stream.="<td align=left id=notransaksibbm".$no.">".$val['notransaksi']."</td>";
            $stream.="<td align=center style='display:none' id=karyawanid".$no.">".$val['karyawanid']."</td>";
            $stream.="<td align=left>".$optNmKar[$val['karyawanid']]."</td>";
            $stream.="<td align=right id=jlhbbm".$no.">".@number_format($val['jlhbbm'])."</td>";
            $stream.="<td align=right id=pembayaran".$no.">".@number_format($val['dibayar'])."</td>";
            $stream.="<td align=center><input type=checkbox id=cekbbm".$no." ".$cek."></td>";
            $stream.="</tr>";
		}
        $stream.="<tr class=rowcontent>";
        $stream.="<td colspan=6 align=right >
			<button class=mybutton onclick=saveallbbm(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
        $stream.="</tr>";  
        echo $stream;
    break;
	
	case'savebbm':

		if ($noakunbbm=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunbbm=='1110101') {
			if ($rekeningbankbbm=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		if($cekbbm==1) 
		{
			#cek apakah HT sudah di-insert
			$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='BBM' limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$cekht=$bar['jumlah'];
			if($cekht<=0)
			{
				$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('".$nopdo."', '".$notranbbm."', '".$unit."', '".$per."', 'BBM','".$_SESSION['standard']['userid']."')";
				try
				{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) 
				{
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}	
			
			if($currRowbbm==1){
				##delete 1st
				$str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbbm."'";
				//exit("error".$str);
				try
				{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) 
				{
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}				
			}
			
			$optNoAkun = makeOption($dbname,'keu_5parameterjurnal','jurnalid,noakundebet',"jurnalid='PDO01'");
			$noakun = $optNoAkun['PDO01'];

			if ($noakun=='') {
				$noakun=$aruskasbbm;
			}
			
            $str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbbm."' order by nourut desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$nourutbaru=$bar['nourut']+1;
			
            $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                    `nodocument`,`tanggal`,`satuan`,`fisik`,`rupiah`,`noakunkas`,`rekeningbank`) 
                    VALUES ('".$nopdo."', '".$notranbbm."', '".$nourutbaru."', '".$noakun."', '".$karyawanid."',
                    '".$notransaksibbm."','".$tglawalper."','Liter','".$jlhbbm."','".$pembayaran."','".$noakunbbm."','".$rekeningbankbbm."')";    
			//exit("error".$str);
            try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) 
			{
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
        }
        else 
		{
            $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbbm."' and nodocument='".$notransaksibbm."' ";
            try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) 
			{
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
        }
    break;
	
	case'deletebbm':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranbbm."' and nourut='".$nourutbbm."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
	
	
	#############################################################
	
	case'listio':
		$stream.="<fieldset><legend><b>List Ijin Operasional</b></legend >
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center width=30px>".$_SESSION['lang']['nourut']."</td>    
					<td align=center>".$_SESSION['lang']['noakun']."</td>  
					<td align=center>".$_SESSION['lang']['notransaksi']."</td>  
					<td align=center>".$_SESSION['lang']['kodevhc']."</td>        
					<td align=center>".$_SESSION['lang']['jenisbiaya']."</td>    
					<td align=center>".$_SESSION['lang']['biaya']."</td>
					<td align=center width=50px>".$_SESSION['lang']['action']."</td>
				</tr>
			</thead>";
		
		$str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%IO%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optNmVhc = makeOption($dbname,'vhc_5master','kodevhc,detailvhc',"kodevhc='".$bar['rincian']."'");
			$optNmJns = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar['noakun']."'");
			$no+=1;
            $stream.="<tr class=rowcontent>
                        <td align=center>".$no."</td>
                        <td align=center>".@$arrnmakun[$bar['noakunkas']]."</td> 
                        <td align=center>".$bar['nodocument']."</td>    
                        <td align=left>".$bar['rincian']."-".$optNmVhc[$bar['rincian']]."</td>        
                        <td align=left>".$optNmJns[$bar['noakun']]."</td> 
                        <td align=right>".@number_format($bar['rupiah'])."</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                                onclick=\"editio('".$nopdo."','".$notranbbm."','".$bar['noakunkas']."','".$bar['rekeningbank']."');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                                onclick=\"deleteio('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."');\">
                        </td>   
                </tr>";    
        }
        echo $stream;
    break;
	
	case'detailio':

		if ($noakunio=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunio=='1110101') {
			if ($rekeningbankio=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$stream="";
        $stream.="<fieldset><legend><b>Detail Ijin Operasional</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr>
                        <td align=center width=30px >".$_SESSION['lang']['nourut']."</td> 
                        <td align=center>".$_SESSION['lang']['notransaksi']."</td>    
                        <td align=center>".$_SESSION['lang']['kodevhc']."</td>
                        <td align=center>".$_SESSION['lang']['jenisbiaya']."</td>
                        <td align=center>".$_SESSION['lang']['biaya']."</td>
						<td align=center rowspan=2 width=30px >".$_SESSION['lang']['action']."<br>
							<input type=checkbox id=cekallio onclick=cekallio()>
						</td>
                    </tr>
                </thead><tbody id=contentdetailio>";
			
		$str=" select nodocument,rincian from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranio."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
            $posave[$bar['nodocument']][$bar['rincian']]=1;
        }
		
		$str="select a.notransaksi,a.kodevhc,b.detailvhc,a.jenisbiaya,c.nama_aruskas,a.biaya 
			from ".$dbname.".vhc_byyijinops a 
			left join ".$dbname.".vhc_5master b on a.kodevhc=b.kodevhc 
			left join ".$dbname.".keu_5aruskas c on a.jenisbiaya=c.noaruskas 
			where a.kodeorg='".$unit."' and a.periode='".$per."'";
		$res=fetchData($str);
		$no=0;
		foreach($res as $key=>$val)
		{
			if(@$posave[$val['notransaksi']][$val['kodevhc']]==1)
			{
				$cek="checked=true";
            }
            else
			{
				$cek="";
            }
			$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
			$no+=1;
            $stream.="<tr class=rowcontent id=rowio".$no.">";
            $stream.="<td align=center>".$no."</td>";
            $stream.="<td align=left id=notransaksiio".$no.">".$val['notransaksi']."</td>";
            $stream.="<td align=center style='display:none' id=kodevhc".$no.">".$val['kodevhc']."</td>";
            $stream.="<td align=center>".$val['detailvhc']."</td>";
            $stream.="<td align=center style='display:none' id=jenisbiaya".$no.">".$val['jenisbiaya']."</td>";
            $stream.="<td align=center>".$val['nama_aruskas']."</td>";
            $stream.="<td align=center id=biaya".$no.">".@number_format($val['biaya'])."</td>";
            $stream.="<td align=center><input type=checkbox id=cekio".$no." ".$cek."></td>";
            $stream.="</tr>";
		}
        $stream.="<tr class=rowcontent>";
        $stream.="<td colspan=6 align=right >
			<button class=mybutton onclick=saveallio(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
        $stream.="</tr>";  
        echo $stream;
    break;
	
	case'saveio':

		if ($noakunio=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunio=='1110101') {
			if ($rekeningbankio=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		if($cekio==1) 
		{
			#cek apakah HT sudah di-insert
			$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='IO' limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$cekht=$bar['jumlah'];
			if($cekht<=0)
			{
				$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('".$nopdo."', '".$notranio."', '".$unit."', '".$per."', 'IO','".$_SESSION['standard']['userid']."')";
				try
				{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) 
				{
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}	
			
            ##delete 1st
            $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranio."' and nodocument='".$notransaksiio."' and rincian='".$kodevhc."'";
            try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) 
			{
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
			
			$str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranio."' order by nourut desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$nourutbaru=$bar['nourut']+1;
			
            $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                    `nodocument`,`tanggal`,`rupiah`,`noakunkas`,`rekeningbank`) 
                    VALUES ('".$nopdo."', '".$notranio."', '".$nourutbaru."', '".$jenisbiaya."', '".$kodevhc."',
                    '".$notransaksiio."','".$tglawalper."','".$biaya."','".$noakunio."','".$rekeningbankio."')";    
            try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) 
			{
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
        }
        else 
		{
            $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranio."' and nodocument='".$notransaksiio."' and rincian='".$kodevhc."'";
            try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) 
			{
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
        }
    break;
	
	case'deleteio':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranio."' and nourut='".$nourutio."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
	
	########################################################
	
	case'listpjd':
		$stream.="<fieldset><legend><b>List ".$_SESSION['lang']['perdin']."</b></legend >
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center width=30px>".$_SESSION['lang']['nourut']."</td>    
					<td align=center>".$_SESSION['lang']['unit']."</td>  
					<td align=center>".$_SESSION['lang']['periode']."</td>  
					<td align=center>".$_SESSION['lang']['noakun']."</td>  
					<td align=center>".$_SESSION['lang']['total']."</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td align=center width=50px>".$_SESSION['lang']['action']."</td>
				</tr>
			</thead>";
		
		$str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%".$unit."/PJD/%' order by tanggal desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$expnotran = explode('/',$bar['notransaksi']);
			$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$expnotran[1]."'");	
			$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['rincian']."'");	
			$no+=1;
            $stream.="<tr class=rowcontent>
                        <td align=center>".$no."</td>
                        <td>".$expnotran[1]."-".$optNmOrg[$expnotran[1]]."</td>
                        <td align=center>".substr($bar['tanggal'],0,7)."</td>
                        <td align=center>".$bar['noakunkas']." - ".@$arrnmakun[$bar['noakunkas']]."</td>
                        <td align=right>".@number_format($bar['rupiah'])."</td>
                        <td>".$bar['rincian']."</td>";
						
			if($per==substr($bar['tanggal'],0,7)){
				$stream.="<td align=center>
					<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
						onclick=\"editpjd('".$bar['notransaksi']."','".$bar['rupiah']."','".$bar['rincian']."','".$bar['rekeningbank']."','".$bar['noakunkas']."');\">
					<img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                                onclick=\"deletepjd('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."');\">
                    <img src=images/addplus.png title='Upload' class=resicon onclick=showupload('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."',event)>
				</td>";
			}else{
				$stream.="<td align=center><img src=images/addplus.png title='Upload' class=resicon onclick=showupload('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."',event)></td>";
			}
			
			$stream.="</tr>";    
        }
        echo $stream;
    break;
	
	case'insertpjd':

		if ($noakunpjd=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunpjd=='1110101') {
			if ($rekeningbankpjd=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$str="select * from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='PJD' and kodeorg='".$unit."' limit 1";
		$res=fetchData($str);
		if(count($res) > 0){
			exit("Gagal, Sudah anda transaksi PDO Perjalanan dinas untuk periode ".$per);
		}
		
		
		$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
		VALUES ('".$nopdo."', '".$notranpjd."', '".$unit."', '".$per."', 'PJD','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
			
			$str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `rincian`, `noakun`,
                `tanggal`, `rupiah`, `noakunkas`, `rekeningbank`) 
                VALUES ('".$nopdo."', '".$notranpjd."', '1', '".$ketpjd."', '".$aruskaspjd."',
                '".$tglawalper."','".$totalpjd."','".$noakunpjd."','".$rekeningbankpjd."')";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
	
	case'updatepjd':

		if ($noakunpjd=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunpjd=='1110101') {
			if ($rekeningbankpjd=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$str="update ".$dbname.".`keu_pdodt` set rupiah='".$totalpjd."',rincian='".$ketpjd."',noakunkas='".$noakunpjd."',rekeningbank='".$rekeningbankpjd."' where nopdo='".$nopdo."' and notransaksi='".$notranpjd."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
	
	case'deletepjd':
		$str="delete from ".$dbname.".`keu_pdoht` where nopdo='".$nopdo."' and notransaksi='".$notranpjd."'";
		try{
			$owlPDO->exec($str);
			
			$str="delete from ".$dbname.".`keu_pdodt` where nopdo='".$nopdo."' and notransaksi='".$notranpjd."'";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
	
	######################################################################
	case'detaillnn':

	    $optrek=$optkas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	    $str="select noakun,namaakun from ".$dbname.".keu_5akun where noakun in ('1112101','1112102','1110101')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()) {
	    	$optkas.="<option value='".$bar['noakun']."'>".$bar['namaakun']."</option>";
		}

		$str = "select * from ".$dbname.".keu_5akunbank";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
		    $wheredz =" kodebank='".$bar['namabank']."'";
		    $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
		    $optrek.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
		}

        $notranlnn=$explnopdo[0].'/'.$explnopdo[2].'/LNN'.'/'.$explnopdo[3].'/001';
        $stream.="<fieldset><legend><b>Form Input</b></legend >";
		$stream.="
            ".$_SESSION['lang']['notransaksi']." : <input type=text id=notranlnn disabled value='".$notranlnn."' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext>
            ".$_SESSION['lang']['noakun']." : <select onchange='getrekeninglnn()' id=noakunlnn style=\"width:150px;\">".$optkas."</select>
            ".$_SESSION['lang']['rekening']." : <select id=rekeningbanklnn style=\"width:150px;\">".$optrek."</select><hr>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td hidden>nourutdb</td>
                        <td align=center>".$_SESSION['lang']['aruskas']."</td>    
                        <td align=center>".$_SESSION['lang']['keterangan']."</td>    
                        <td align=center>".$_SESSION['lang']['satuan']."</td>
                        <td align=center>".$_SESSION['lang']['kuantitas']."</td>
                        <td align=center>".$_SESSION['lang']['rupiahsatuan']."</td>
                        <td align=center width=100px>".$_SESSION['lang']['total']."</td>
                        <td align=center width=30px>".$_SESSION['lang']['action']."
                            </td>
                    </tr>
                </thead>";
        $stream.="    
                <tr class=rowcontent>
                    <td align=left hidden><input type=text id=nourutlnn onkeypress=\"return tanpa_kutip(event)\" class=myinputtext></td>    
                    <td align=left>
						<select id=akunlnn  style=width:150px onchange=getket('lain')>'".$optaruskas."'</select>
						<img onclick=\"z.elSearch('akunlnn',event)\" class=resicon src=images/onebit_02.png style=position:relative;top:5px>
					</td>    
                    <td align=left><select id=ketlnn style=width:400px >'".$opt."'</select></td>    
                    <td align=left><select id=satlnn>'".$optsat."'</select></td>
                    <td align=right><input type=text id=fisiklnn onkeyup=totallnn() onkeypress='return angka_doang(event)' class=myinputtextnumber   style=width:50px ></td>
                    <td align=center><input type=text id=rupsatlnn onkeyup=totallnn() onkeypress='return angka_doang(event)' class=myinputtextnumber   style=width:90px ></td>
                    <td align=right id=totlnn></td>
                    <td align=center width=30px>
						<img title=".$_SESSION['lang']['save']." class='zImgBtn' onclick='savelnn()' src='images/save.png'>
						</td>
               </tr>
               <input type=hidden id=methodlnn value='savelnn'>";
        $stream.="</table></fieldset>";
		echo $stream;
    break;
	
	case'listlnn':
       $stream.="<fieldset><legend><b>List Lainnya</b></legend >
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=min-width:940px>
                <thead>
                    <tr class=rowheader>
                        <td align=center width=30px>".$_SESSION['lang']['nourut']."</td>    
                        <td align=center>".$_SESSION['lang']['noakun']."</td>  
                        <td align=center>".$_SESSION['lang']['aruskas']."</td>        
                        <td align=center>".$_SESSION['lang']['keterangan']."</td>    
                        <td align=center>".$_SESSION['lang']['satuan']."</td>
                        <td align=center>".$_SESSION['lang']['kuantitas']."</td>
                        <td align=center>".$_SESSION['lang']['rupiahsatuan']."</td>
                        <td align=center>".$_SESSION['lang']['total']."</td>
                        <td align=center width=50px>".$_SESSION['lang']['action']."</td>
                    </tr>
                </thead>";
        $str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%LNN%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $stream.="<tr class=rowcontent>
                        <td align=center>".$no."</td>
                        <td align=center>".$arrnmakun[$bar['noakunkas']]."</td>    
                        <td align=left>".$bar['noakun']." - ".$arrnmaruskas[$bar['noakun']]."</td>        
                        <td align=left>".$optket[$bar['rincian']]."</td>  
                        <td align=center>".$bar['satuan']."</td>
                        <td align=right>".@number_format($bar['fisik'])."</td>
                        <td align=right>".@number_format($bar['rupiah']/$bar['fisik'])."</td>
                        <td align=right>".@number_format($bar['rupiah'])."</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                                onclick=\"editlnn('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."',
                                    '".$bar['noakun']."','".$bar['rincian']."','".$bar['satuan']."','".$bar['fisik']."',
                                    '".$bar['rupiah']/$bar['fisik']."','".$bar['rupiah']."','".$bar['noakunkas']."','".$bar['rekeningbank']."');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                                onclick=\"deletelnn('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."');\">
                        	<img src=images/addplus.png title='Upload' class=resicon onclick=showupload('".$bar['nopdo']."','".$bar['notransaksi']."','".$bar['nourut']."',event)>
                        </td>   
                </tr>";    
        }
        echo $stream;
    break;
	
	case'savelnn':

    	if ($noakunlnn=='') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunlnn=='1110101') {
			if ($rekeningbanklnn=='') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		#cek apakah HT sudah di-insert
		$str="select count(*) as jumlah from ".$dbname.".keu_pdoht where nopdo='".$nopdo."' and periode='".$per."' and tipepdo='LNN' limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$cekht=$bar['jumlah'];
		if($cekht<=0){
			$str="INSERT INTO ".$dbname.".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('".$nopdo."', '".$notranlnn."', '".$unit."', '".$per."', 'LNN','".$_SESSION['standard']['userid']."')";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}	
        #cek nourut
        $str="select nourut from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranlnn."'"
            . " order by nourut desc limit 1 ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
            $nourutbaru=$bar['nourut']+1;
        $str="INSERT INTO ".$dbname.".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                `tanggal`, `satuan`,`fisik`, `rupiah`,`noakunkas`, `rekeningbank`) 
                VALUES ('".$nopdo."', '".$notranlnn."', '".$nourutbaru."', '".$akunlnn."', '".$ketlnn."',
                '".$tglawalper."', '".$satlnn."', '".$fisiklnn."','".$totlnn."', '".$noakunlnn."','".$rekeningbanklnn."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
	
	case'updatelnn':
        $str="update ".$dbname.".keu_pdodt set noakun='".$akunlnn."',rincian='".$ketlnn."',
            tanggal='".$tglawalper."',satuan='".$satlnn."',fisik='".$fisiklnn."',rupiah='".$totlnn."',noakunkas='".$noakunlnn."',rekeningbank='".$rekeningbanklnn."'
            where nopdo='".$nopdo."' and notransaksi='".$notranlnn."' and nourut='".$nourutlnn."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;
	
	case'deletelnn':
        $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranlnn."' "
                        . " and nourut='".$nourutlnn."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
    break;



    case 'showupload':
        $tab="";
        
        $tab.="<table cellspacing='1' border='0' id='uploadpopup'>
            <tr>
                <td>".$_SESSION['lang']['notransaksi']."</td>
                <td>:</td>
                <td>
                    <label id='notransupload' style='font-weight:bold'>".$notransaksi."</label>
                </td>
            </tr>
            <tr>
                <td>Filename</td>
                <td>:</td>
                <td>
                    <input type='file' name='upload' id='upload' class=mybutton>
                    <input type='hidden' id='nourutupload' value='".$nourut."'>
                    <input type='hidden' id='nopdoupload' value='".$nopdo."'>
                </td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td>
                    <button class=mybutton onclick=\"submitfile('".$nopdo."','".$notransaksi."','".$nourut."')\">Submit</button>
                </td>
            </tr>
        </table>
        <p />";
        
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>No.</td>
                    <td align='center'>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center'>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        </fieldset> ";
        
        echo $tab;
    break;
    
    case 'submitfile':
        $tgl = date("YmdHis");
        // exit("error : ".$tgl);
        $data = $_POST;
        
        if($data['fileupload']!='')
        {
            if($_FILES['file']['error']==0)
            {
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $newfilename = str_replace($filetype,'',$_FILES['file']['name']);
                $filename = $newfilename."_".$tgl."".$filetype;
                $file_tmpname = $_FILES['file']['tmp_name'];        
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar'))
                {
                    if($_FILES['file']['size'] <= 250000)
                    {   
                        $str = "insert into ".$dbname.".listfileupload (`id`, `notransaksi`, `namafile`, `formaticon`, `status`,`createdby`,`createdtime`) values ('','".$data['nopdo']."##".$data['notransaksi']."##".$data['nourut']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                        try
                        {
                            $owlPDO->exec($str);
                            move_uploaded_file($file_tmpname,"fileupload/pdo/$filename");
                        }
                        catch(PDOException $e)
                        {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                    else
                    {
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                }else{
                    exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
                }
            }
        }
    break;
    
    case 'loadfiles':
        $no = 0;
        $tab = "";
        $str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi = '".$notransaksi."' and nourut='".$nourut."'";
        $resv=fetchData($str);
        foreach($resv as $bar => $barv){
            $close = $barv['close'];    
        }
        
        $str="select * from ".$dbname.".listfileupload where notransaksi = '".$nopdo."##".$notransaksi."##".$nourut."' and status='1'";
        $res=fetchData($str);
        if(empty($res))
        {
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }
        else
        {
            foreach($res as $key=>$val)
            {
                $no++;
                $tab.="<tr id='ppDetailTable' class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                    
                if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/pdo/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.png')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/pdo/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.pdf')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/pdo/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/pdo/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/pdo/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
                    </td>";
                }
                else
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/pdo/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
                    </td>";
                }
                
                $tab.="<td style='text-align:left'>".$val['namafile']."</td>
                    <td align=center>
                        <a href='fileupload/pdo/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if($close==0){
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$nopdo."','".$notransaksi."','".$nourut."','".$val['namafile']."');\" >";
                }
                $tab."  </td>
                </tr>";
            }   
        }
        echo $tab;
    break;
    
    case 'deletefile':
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$nopdo."##".$notransaksi."##".$nourut."' and namafile='".$namafile."'";
        try
        {
            $owlPDO->exec($str);
            $path = "fileupload/pdo/".$namafile;
            unlink($path);
        }
        catch(PDOException $e)
        {
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    case 'lihatDetail':
        $no = 0;
        $tab = "";

        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>No.</td>
                    <td align='center'>".$_SESSION['lang']['pdo']."</td>
                    <td align='center'>".$_SESSION['lang']['notransaksi']."</td>
                    <td align='center'>".$_SESSION['lang']['nourut']."</td>
                    <td align='center'>".$_SESSION['lang']['aruskas']."</td>
                    <td align='center'>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center'>Action</td>
                </tr>
                </thead>
                <tbody>";
        $str="select * from ".$dbname.".listfileupload where notransaksi like '".$nopdo."%".$tipe."%' and status='1'";
        if(($tipe=='PAD')||($tipe=='IO')){
        	$sData="select nodocument from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%".$tipe."%'";	
        	$rData=fetchData($sData);
        	$listNodoc="";
        	if(count($rData)!=0){
        		foreach ($rData as $key => $val) {
	        		if($key==0){
	        			$listNodoc="'".$val['nodocument']."'";
	        		}else{
	        			$listNodoc.=",'".$val['nodocument']."'";
	        		}
	        	}
        	}
        	if($listNodoc!=""){
        		if($tipe=='PAD'){
        			$str="select * from ".$dbname.".listfile_lgl_bansos where notransaksi in (".$listNodoc.") and status='1'";	
        		}
        		if($tipe=='IO'){
        			$str="select * from ".$dbname.".listfilebyyijinops where notransaksi in (".$listNodoc.") and status='1'";	
        		}
        		
        	}else{
        		$str="select * from ".$dbname.".listfileupload where notransaksi = '".$listNodoc."' and status='1'";	
        	}
        }
        
        $res=fetchData($str);
        if(empty($res))
        {
            $tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }
        else
        {
            foreach($res as $key=>$val){
            	if($listNodoc!=""){
            		$strdt="select distinct notransaksi,noakun from ".$dbname.".keu_pdo_vw where nopdo='".$nopdo."' and nodocument='".$val['notransaksi']."'";
	        		$resdt=fetchData($strdt);	
	        		$notrans[0]=$nopdo;
	        		$notrans[1]=$val['notransaksi'];
	        		$notrans[2]=$resdt[0]['noakun'];
            	}else{
            		$notrans=explode('##', $val['notransaksi']);
	            	$strdt="select noakun from ".$dbname.".keu_pdodt where nopdo='".$notrans[0]."' and notransaksi='".$notrans[1]."' and nourut='".$notrans[2]."'";
	        		$resdt=fetchData($strdt);	
            	}
            	
                $no++;
                $tab.="<tr id='ppDetailTable' class=rowcontent>
                    <td style='text-align:center'>".$no."</td>
                    <td style='text-align:center'>".$notrans[0]."</td>
                    <td style='text-align:center'>".$notrans[1]."</td>
                    <td style='text-align:center'>".$notrans[2]."</td>
                    <td style='text-align:center'>".$arrnmaruskas[$resdt[0]['noakun']]."</td>";
                   $alamafolder="pdo";
                   if($tipe=='PAD'){
                   		$alamafolder="lgl_bansos";
                   }
                   if($tipe=='IO'){
                   		$alamafolder="ijin_ops";
                   }
                if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/".$alamafolder."/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.png')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/".$alamafolder."/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.pdf')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/".$alamafolder."/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/".$alamafolder."/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/".$alamafolder."/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
                    </td>";
                }
                else
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/".$alamafolder."/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
                    </td>";
                }
                
                $tab.="<td style='text-align:left'>".$val['namafile']."</td>
                    <td align=center>
                        <a href='fileupload/".$alamafolder."/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                $tab."  </td>
                </tr>";
            }   
        }
        $tab.="</tbody>
            </table>
        </fieldset>";

        echo $tab;
    break;

    case 'detailrealisasi':

    	$no = 0;
    	$total=0;
        $tab = "";

        $tab.="<table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>".$_SESSION['lang']['nourut']."</td>
                    <td align='center'>".$_SESSION['lang']['notransaksi']."</td>
                    <td align='center'>".$_SESSION['lang']['noreferensi']."</td>
                    <td align='center'>".$_SESSION['lang']['unit']."</td>
                    <td align='center'>".$_SESSION['lang']['noakun']."</td>
                    <td align='center'>".$_SESSION['lang']['rekening']."</td>
                    <td align='center'>".$_SESSION['lang']['tanggal']."</td>
                    <td align='center'>".$_SESSION['lang']['customer']."/".$_SESSION['lang']['supplier']."</td>
                    <td align='center'>".$_SESSION['lang']['keterangan']."</td>
                    <td align='center'>".$_SESSION['lang']['jumlah']."</td>
                </tr>
                </thead>
                <tbody>";

        $supplier="kodesupplier";
        if ($tipe=='M') {
        	$supplier="kodecustomer as kodesupplier";
        }

        $whr=" and noaruskas='".$akunkas."'";
        if (strlen($akunkas)>5) {
        	$str=" select noakun from ".$dbname.".log_5supkelompok where supplierid='".$akunkas."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$noakun=$bar['noakun'];
			
        	$whr=" and noakun='".$noakun."' and kodesupplier='".$akunkas."'";
        }
    	
    	#=realisasi
		$str="select notransaksi,keterangan1,kodeorg,noakun,rekening,tanggal,".$supplier.",keterangan,jumlah from ".$dbname.".keu_kasbankdtht_vw where tipetransaksi='".$tipe."' and tanggal like '".$per."%' and kodeorg='".$unit."' ".$whr;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no++;
            $strrek="select rekening,b.namabank as namabank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b  on a.namabank=b.kodebank where noakun='".$bar['rekening']."' ";
			$resrek=$owlPDO->query($strrek) or die(print " Gagal: ".PDOException::getMessage());
			$resrek->setFetchMode(PDO::FETCH_ASSOC);
			$barrek=$resrek->fetch();

            $tab.="<tr class=rowcontent>
                <td style='text-align:center'>".$no."</td>
                <td>".$bar['notransaksi']."</td>
                <td>".$bar['keterangan1']."</td>
                <td>".$bar['kodeorg']."</td>
                <td>".$bar['noakun']."<br>(".$arrnmakun[$bar['noakun']].")</td>
                <td>".$barrek['rekening']."<br>(".$barrek['namabank'].")</td>
                <td>".tanggalnormal($bar['tanggal'])."</td>
                <td>".$bar['kodesupplier']."<br>(".(($arrnmsupp[$bar['kodesupplier']]=='') ? $arrnmcust[$bar['kodesupplier']]:$arrnmsupp[$bar['kodesupplier']]).")</td>
                <td>".$bar['keterangan']."</td>
                <td align=right>".number_format($bar['jumlah'])."</td></tr>";
                $total+=$bar['jumlah'];
		}
		$tab.="<tr class=rowcontent>
			<td colspan=9>".$_SESSION['lang']['total']."</td>
			<td>".number_format($total)."</td>
			</tr>";

		$tab.="</tbody>
            </table>";

        echo $tab;

    break;

}
?>