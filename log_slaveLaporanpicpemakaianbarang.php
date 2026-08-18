<?php
// error_reporting(1);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
require_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;



$proses =$_POST['proses'];if($proses == ''){$proses = $_GET['proses'];};
$param	=$_POST;if(count($param)==0){$param = $_GET;}

$optnamabarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$optnamakaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optorganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optsetup_blok = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');
$optsetup_kegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$opttanggal_transaksi = makeOption($dbname, 'log_transaksiht', 'notransaksi,tanggal');

switch($proses){
	case'preview':

		$tab ="";
		if($param['tipeprint']=='excel'){$brd="border=1";}else{$brd="";}
		$tab.="<table class=sortable cellspacing=1 ".$brd." cellpadding=5 style='width:100%'>
		<thead>
			<tr class=rowheader style='text-align:center'>
				<th>No.</th>
				<th>No. Transaksi</th>
				<th>" . $_SESSION['lang']['materialcode'] . "</th>
				<th>" . $_SESSION['lang']['materialname'] . "</th>
				<th>" . $_SESSION['lang']['namakaryawan'] . "</th>
				<th>Presentase Harga (%)</th>
				<th>" . $_SESSION['lang']['jumlah'] . " Pakai</th>
				<th>Total Rupiah</th>
				<th>" . $_SESSION['lang']['divisi'] . "</th>
				<th>" . $_SESSION['lang']['status'] . " Blok</th>
				<th>" . $_SESSION['lang']['blok'] . "</th>
				<th>" . $_SESSION['lang']['kegiatan'] . "</th>
			</tr>  
		</thead><tbody>";

		$tanggalmulai = substr($param['periode'],6,4)."-".substr($param['periode'],3,2)."-".substr($param['periode'],0,2);
		$tanggalsampai = substr($param['periode2'],6,4)."-".substr($param['periode2'],3,2)."-".substr($param['periode2'],0,2);

		$where="";
		if($param['unit'] != ''){
			$where.=" and a.notransaksi like '%".$param['unit']."%' ";
		}
		if($param['kodebarang'] != ''){
			$where.=" and b.kodebarang = '".$param['kodebarang']."' ";
		}
		$str = "select a.*,b.statusblok from ".$dbname.".log_pemakaianpresentase a left join log_transaksi_vw b 
		on a.notransaksi=b.notransaksi and a.kodebarang=b.kodebarang and a.kodekegiatan=b.kodekegiatan 
		where b.notransaksi != ''
		and b.tanggal>='" . $tanggalmulai . "' and b.tanggal<='" . $tanggalsampai . "'
		".$where."
		order by notransaksi asc";
		$res=fetchdata($str);
		foreach($res as $val){
			
			$blok = ($optorganisasi[$val['kodeblok']] != '') ? $optorganisasi[$val['kodeblok']] : $optsetup_blok[$val['kodeblok']];

			// ambil beban karyawan dari jurnaldt
			$strx="select debet from ".$dbname.".keu_jurnaldt_vw where noreferensi = '".$val['notransaksi']."' and nik = '".$val['karyawanid']."' 
			and kodebarang = '".$val['kodebarang']."' and debet != '0' and keterangan like 'Pemakaian PIC%' limit 1 ";
			$resx=fetchData($strx);
			$nilaiRP = $resx[0]['debet'];

			$style='';
			if($nilaiRP == ''){

				// ambil data transaksiht
				$strx="select * from ".$dbname.".log_transaksiht where notransaksi = '".$val['notransaksi']."' ";
				$resx=fetchData($strx);
				$periode = substr($resx[0]['tanggal'],0,7);
				$kodegudang = $resx[0]['kodegudang'];

				// ambil hargarata
				$strx="select hargarata from ".$dbname.".log_5saldobulanan where periode = '".$periode."' and kodegudang = '".$kodegudang."' 
				and kodebarang = '".$val['kodebarang']."'";
				$resx=fetchData($strx);
				$hargarata = $resx[0]['hargarata'];

				$nilaiRP = ($hargarata * ($val['presentase']/100) * $val['jumlah']);
				
				$style='background-color:orange';

			}


			$no+=1;
			$tab.="<tr class=rowcontent style='".$style."'>
				<td align='center'>" . $no . "</td>
				<td align='center'>" . $val['notransaksi'] . "</td>
				<td align='center'>" . $val['kodebarang'] . "</td>
				<td align='center'>" . $optnamabarang[$val['kodebarang']] . "</td>
				<td align='center'>".$optnik[$val['karyawanid']]." - " . $optnamakaryawan[$val['karyawanid']] . "</td>
				<td align='center'>" . numberformat_kasih_koma($val['presentase']) . "</td>
				<td align='center'>" . numberformat_kasih_koma($val['jumlah']) . "</td>
				<td align='center'>" . number_format($nilaiRP,2) . "</td>
				<td align='center'>".$val['divisi']." - " . $optorganisasi[$val['divisi']] . "</td>
				<td align='center'>" . $val['statusblok'] . "</td>
				<td align='center'>" .  $blok . "</td>
				<td align='center'>".$val['kodekegiatan']." - " . $optsetup_kegiatan[$val['kodekegiatan']] . "</td>
			</tr>";
		}
		$tab.="</tbody>
			</table>
		</div>";

		if($param['tipeprint']=='html'){
			echo $tab;
		}else{
			// $nop_="lap_picpemakainbarang_" . $param['unit']."_".$param['kodebarang']."_".$param['periode'] ."_".$param['periode2']  ;
			// $dte = date("YmdHis");
    		// $nop = "lap_picpemakainbarang".$dte;
			
			$nop = "lap_picpemakainbarang.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("lap_picpemakainbarang", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}

	break;

}

