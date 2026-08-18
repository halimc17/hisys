<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$tglInv = "";
$urlefil=checkPostGet('urlefil','0');
$optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$optnmakun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmkapal=makeOption($dbname, 'pmn_5kapalponton', 'kode,nama');

$arrHead = setheadreport('',$dataH['kodeorg']);
$path=$arrHead['logopalma'];

    $str = "select * from ".$dbname.".".$table." where noinvoice='".$column."' ";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $bar=$res->fetch();
        $kodept=$bar['kodept'];
        $kodecustomer=$bar['kodecustomer'];
        $nokontrak=$bar['nokontrak'];
        $noinvoice=$bar['noinvoice'];
        $tanggalinvoice=$bar['tanggal'];
        $bulaninvoice=substr($bar['tanggal'],5,2);
        $namabulaninvoice=numToMonth(substr($bar['tanggal'],5,2),'I','long');
        $tahuninvoice=substr($bar['tanggal'],0,4);
        $kuantitas=$bar['kuantitas'];
        $kodebarang=$bar['kodebarang'];
        $nilaiinvoice=$bar['nilaiinvoice'];
        $matauang=$bar['matauang'];
        $noakun=$bar['bayarke'];
        $ttd=$bar['ttd'];
        $jenis=$bar['jenis'];
        $berikat=$bar['berikat'];
        $createby=$bar['createby'];
        $jenisinvoice=$bar['jenisinvoice'];
        $transport=$bar['transport'];
        // $hargasatuan=@($nilaiinvoice/$kuantitas);
        $nilaippn=$bar['nilaippn'];
        $keterangantambahan=$bar['keterangantambahan'];
        $nodo=$bar['nodo'];
        $nofakturpajak=$bar['nofakturpajak'];
        $noreferensi=$bar['noreferensi'];
        $npwppt=$bar['npwpunit'];
        // $totalnilaiinvoice=$nilaiinvoice+$nilaippn;
    
			
			
        $keteranganpinalti1=$bar['keterangan1'];
        $rupiahpinalti1=$bar['rupiah1']*-1;
        $keteranganpinalti2=$bar['keterangan2'];
        $rupiahpinalti2=$bar['rupiah2']*-1;
        $keteranganpinalti3=$bar['keterangan3'];
        $rupiahpinalti3=$bar['rupiah3']*-1;
        $keteranganpinalti4=$bar['keterangan4'];
        $rupiahpinalti4=$bar['rupiah4']*-1;
        $keteranganpinalti5=$bar['keterangan5'];
        $rupiahpinalti5=$bar['rupiah5']*-1;
        $keteranganpinalti6=$bar['keterangan6'];
        $rupiahpinalti6=$bar['rupiah6']*-1;
        $keteranganpinalti7=$bar['keterangan7'];
        $rupiahpinalti7=$bar['rupiah7']*-1;
        $keteranganpinalti8=$bar['keterangan8'];
        $rupiahpinalti8=$bar['rupiah8'];
        
        $totalpinalti=$rupiahpinalti1+$rupiahpinalti2+$rupiahpinalti3+$rupiahpinalti4+$rupiahpinalti5+$rupiahpinalti6+$rupiahpinalti7+$rupiahpinalti8;
        
        // exit("Error:".$ppnpinalti);
			
		if($tanggalinvoice<'2022-04-01'){
			$persentasesatu='1.1';
			$persentasedua='0.1';
			$persentasekata='10%';
		}else{
			$persentasesatu='1.11';
			$persentasedua='0.11';
			$persentasekata='11%';
		}	
			
		$str="select count(*) as jumlah,sum(nilaiinvoice) as nilaiinvoice from ".$dbname.".keu_penagihanht 
				where nokontrak='".$nokontrak."' and tanggal<'".$tanggalinvoice."' ";
		// echo $str;exit();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlahinvoice=$bar['jumlah'];
			$jumlahrpinvoice=$bar['nilaiinvoice'];
		
		#= data datakaryawan
		$str="select * from ".$dbname.".datakaryawan where karyawanid='".$ttd."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namakaryawan=$bar['namakaryawan'];
			
		#= data kontrak
		$str="select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$nokontrak."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tanggalkontrak=$bar['tanggalkontrak'];
			$kuantitaskontrak=$bar['kuantitaskontrak'];
			$franco=$bar['franco'];
			$hargasatuan=$bar['hargasatuan'];
			$tipepenjualan=$bar['tipepenjualan'];
			$ppnkontrak=$bar['ppn'];
			if($ppnkontrak==1){
				$hargasatuan=$hargasatuan/$persentasesatu;
			}
			
		$str="select sum(jumlah) as kg from ".$dbname.".pmn_bast where nokontrak='".$nokontrak."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			if($jenisinvoice=='PL'){
				$kuantitas=$bar['kg'];
			}
			
			
		#= data nodo	
		
		$str="select * from ".$dbname.".keu_penagihandt_kapalponton where noinvoice='".$column."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if($bar['jenis']=='KPL'){
				$namakapal.="<br> ".$nmkapal[$bar['kode']];
			}
			if($bar['jenis']=='PNT'){
				$namaponton.="<br> ".$nmkapal[$bar['kode']];
			}
			if($bar['jenis']=='TRK'){
				$namatruck.="<br> ".$nmkapal[$bar['kode']];
			}
		}
			
		
			
		#= data franco	
		$str="select * from ".$dbname.".pmn_5franco where id_franco='".$franco."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namafranco=$bar['franco_name'];
			
			if($namafranco==''){
				$namafranco='&nbsp;';
			}
		
		$str="select sum(nilairupiah) as totx from ".$dbname.".keu_penagihandt where noinvoice='".$column."' group by noinvoice";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$totnilrup=$bar['totx'];

		#= query data pt	
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$alamatpt=$bar['alamat'];
			$wilayahkotapt=$bar['wilayahkota'];
			$kodepospt=$bar['kodepos'];
			$teleponpt=$bar['telepon'];
			$namapt=$bar['namaorganisasi'];
			
		#= npwp	
		// $str="select * from ".$dbname.".setup_org_npwp where kodeorg='".$kodept."' and inisial='JKT'";
		// $str="select * from ".$dbname.".setup_org_npwp where kodeorg='".$kodept."' and defaultppn='1'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();	
			// $npwppt=$bar['npwp'];

		#= query akun bank
		$str="select * from ".$dbname.".keu_5akunbank where noakun='".$noakun."'";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$namabank=$bar['namabank'];
			$rekening=$bar['rekening'];
			$cabang=$bar['cabang'];
			$atasnama =$bar['atasnama'];

			
		#= query akun bank
		$str="select * from ".$dbname.".keu_5daftarbank where kodebank='".$namabank."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$namabank=$bar['namabank'];
			
		$str = "select * from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$alamatcustomer=$bar['alamat'];	
			$namacustomer=$bar['namacustomer'];		
			$telpcustomer=$bar['telepon'];	
			$faxcustomer=$bar['fax'];		
			$kotacustomer=$bar['kota'];		
			
		#= query nama barang	
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$namabarang[$bar['kodebarang']]=$bar['namabarang'];
			$satuanbarang[$bar['kodebarang']]=$bar['satuan'];
		}

		#= query mata uang
		$str="select * from ".$dbname.".setup_matauang";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$namamatauang[$bar['kode']]=$bar['matauang'];
		}
	
	
$arrHead = setheadreport('',$dataH['kodeorg']);
$path=$arrHead['logopalma'];

// Simpan HTML di variabel $tab
$tab = '
<style>
    body { font-family: Arial, sans-serif; font-size: 10pt; }
    .header { text-align: center; }
    .header img { float: left; width: 120px; }
    .invoice-title { text-align: center; font-size: 14pt; font-weight: bold; margin-top: 20px; letter-spacing: 3px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #000; padding: 4px; text-align: center; }
    .no-border td { border: none; }
    .right { text-align: right; }
</style>

<div class="header">
    <img src="data:image/png;base64,' . $path . '" alt="Logo">
    <h2>PT. DWI MITRA ADHIUSAHA</h2>
    <div>PERKEBUNAN KELAPA SAWIT<br>Jl. Manggis V Blok H No. 10</div>
</div>

<div class="invoice-title">INVOICE</div>

<table class="no-border">
    <tr><td>No Invoice</td><td>: 08a/PT.DMA-BJ/WNI/SJ/UNI/2025</td></tr>
    <tr><td>Kepada</td><td>: PT. MUSTIKA SEMEUJUK</td></tr>
    <tr><td>No. SPM</td><td>: 001/SPM-M-S/6ebab/DPB-TBS/PTDMA/2025</td></tr>
</table>

<p>Bersama ini kami sampaikan tagihan penjualan TBS ke kebun PT. Dwi Mitra Adhiusaha periode Juni 2025 dengan rincian sebagai berikut:</p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Jenis Pekerjaan</th>
            <th>Periode</th>
            <th>Jenis Calon</th>
            <th>Berat (Kg)</th>
            <th>Harga (Rp.)</th>
            <th>Jumlah (Rp.)</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Penjualan TBS</td><td>03/06/2025</td><td>TBS</td><td>26.602</td><td>3.200</td><td>85.127.400</td></tr>
        <tr><td>2</td><td>Penjualan TBS</td><td>10/06/2025</td><td>TBS</td><td>25.150</td><td>3.200</td><td>80.480.000</td></tr>
        <tr><td>3</td><td>Penjualan TBS</td><td>18/06/2025</td><td>TBS</td><td>9.367</td><td>3.120</td><td>29.242.140</td></tr>
        <tr><td colspan="6"><strong>Total</strong></td><td><strong>194.849.540</strong></td></tr>
    </tbody>
</table>

<table class="no-border">
    <tr><td class="right">Harga Jual (DPP)</td><td class="right">Rp. 194.849.540</td></tr>
    <tr><td class="right">PPN 11%</td><td class="right">Rp. 21.433.449</td></tr>
    <tr><td class="right">PPN 22</td><td class="right">Rp. 2.080.000</td></tr>
    <tr><td class="right"><strong>Total</strong></td><td class="right"><strong>Rp. 218.362.989</strong></td></tr>
</table>

<p>Terbilang: *** Dua ratus delapan belas juta tiga ratus enam puluh dua ribu sembilan ratus delapan puluh sembilan rupiah ***</p>

<p style="margin-top: 40px;">Hormat kami,<br><br><br><br><strong>Medii Harca</strong><br>Kepala Bagian Komersial</p>
';

		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
 
?>