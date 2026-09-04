<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;

if(count($_POST)>0){	
	$param= $_POST;
}else{
	$param= $_GET;
}
$param          = $_POST;if(count($param)==0){$param = $_GET;}
$method = checkPostGet('method','');

$table='kebun_tbsjual';

$str = "select * from pmn_4customer";
$res=fetchdata($str);
foreach($res as $bar){
	$namacustomer[$bar['kodecustomer']]=$bar['namacustomer'];
}

#= ambil daftar unit didalam pt bentukan array
// $str = "select * from ".$dbname.".organisasi where (length(kodeorganisasi)=4 or length(kodeorganisasi)=3 or length(kodeorganisasi)=6) and inti=1 ";
// // echo $str;exit();
// $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $res->fetch()){
	// $kodept[$bar['kodeorganisasi']]=$bar['induk'];
	// $nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
	// if($bar['tipe']=='KANWIL'){
		// $kodero[$bar['induk']]=$bar['kodeorganisasi'];
	// }
// }


switch($method){
	
	case'excel':
	
		$str = "select * from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."' order by tanggalspb asc, nospb asc";
		$res = fetchdata($str);

		$tgl1excel1 = tanggalnormal($res[0]['tanggaltbs1']);
		$tgl1excel2 = tanggalnormal($res[0]['tanggaltbs2']);

		// ============================================================
		// HEADER
		// ============================================================
		$stream = "<table border=0>";
		$stream .= "<tr><td colspan=17><b>".$nmorg[$kodept[$res[0]['unit']]]."</td></tr>";
		$stream .= "<tr><td colspan=17><b>Commercial Division</td></tr>";
		$stream .= "<tr><td colspan=17 align=center><b>Rekapan Penjualan TBS Periode Tanggal ".tglnmbln($res[0]['tanggaltbs1'],'','')." s/d ".tglnmbln($res[0]['tanggaltbs2'],'','')."</td></tr>";
		$stream .= "<tr><td colspan=17 align=center><b>".$namacustomer[$res[0]['kodecustomer']]."</td></tr>";
		$stream .= "</table>";
		$stream .= "<br>";

		// Mapping nospb -> spbpabrik
		$sql = "select * from ".$dbname.".kebun_spb_penjualan_vw where tanggal like '%".substr($res[0]['tanggaltbs1'],0,7)."%'";
		$resSPBPabrik = fetchData($sql);

		$divtt = [];
		foreach ($resSPBPabrik as $v) {
				$divtt[$v['nospb']] = $v['spbpabrik'];
		}

		// ============================================================
		// SHEET 1: TABEL UTAMA (TBS)
		// ============================================================
		$stream .= "<table border=1>";
		$stream .= "<tr class=rowheader bgcolor=#D3D3D3>
						<td align=center><b>".$_SESSION['lang']['nourut']."</b></td>
						<td align=center><b>".$_SESSION['lang']['noTiket']."</b></td>
						<td align=center><b>".$_SESSION['lang']['nospb']."</b></td>
						<td align=center><b>".$_SESSION['lang']['nospb']." Pabrik</b></td>
						<td align=center><b>".$_SESSION['lang']['kodevhc']."</b></td>
						<td align=center><b>".$_SESSION['lang']['tanggal']."<br>PKS</b></td>
						<td align=center><b>".$_SESSION['lang']['tanggal']."<br>SPB</b></td>
						<td align=center><b>".$_SESSION['lang']['berat']." TBS</b></td>
						<td align=center><b>".$_SESSION['lang']['potongan']."</b></td>
						<td align=center><b>".$_SESSION['lang']['netto']."</b></td>
						<td align=center><b>".$_SESSION['lang']['jjg']."</b></td>
						<td align=center><b>".$_SESSION['lang']['blok']."</b></td>
						<td align=center><b> Inti/Plasma </td>
						<td align=center><b>".$_SESSION['lang']['tahuntanam']."</b></td>
						<td align=center><b>".$_SESSION['lang']['rpkg']."</b></td>
						<td align=center><b>".$_SESSION['lang']['total']." Rp.</b></td>
				</tr>";

		$no = 0;
		$tkgbruto = $tkgpotongan = $tkgnetto = $tjjg = $ttotalrp = 0;
		$subKgBrutoTgl = $subKgPotonganTgl = $subKgNettoTgl = $subJjgTgl = $subTotalRpTgl = 0;
		$rekapTGL = [];
		$rekapTT = [];
		$lastTglSPB = null;

		foreach ($res as $bar) {
				$tglSPB = $bar['tanggalspb'];

				// Baris SUBTOTAL setiap kali tanggal SPB berganti
				if ($lastTglSPB !== null && $lastTglSPB != $tglSPB) {
						$stream .= "<tr style='background-color:#E2F0D9;font-weight:bold;'>";
						$stream .= "<td align=center colspan=7>SUBTOTAL TANGGAL ".$lastTglSPB."</td>";
						$stream .= "<td align=right>".number_format($subKgBrutoTgl)."</td>";
						$stream .= "<td align=right>".number_format($subKgPotonganTgl)."</td>";
						$stream .= "<td align=right>".number_format($subKgNettoTgl)."</td>";
						$stream .= "<td align=right>".number_format($subJjgTgl)."</td>";
						$stream .= "<td></td><td></td><td></td><td></td>";
						$stream .= "<td align=right>".number_format($subTotalRpTgl)."</td>";
						$stream .= "</tr>";

						$subKgBrutoTgl = $subKgPotonganTgl = $subKgNettoTgl = $subJjgTgl = $subTotalRpTgl = 0;
				}

				$no++;
				$stream .= "<tr>";
				$stream .= "<td>".$no."</td>";
				$stream .= "<td>".$bar['notiket']."</td>";
				$stream .= "<td>".$bar['nospb']."</td>";
				$stream .= "<td>".($divtt[$bar['nospb']] ?? '')."</td>";
				$stream .= "<td>".$bar['nokendaraan']."</td>";
				$stream .= "<td>".$bar['tanggalpks']."</td>";
				$stream .= "<td>".$bar['tanggalspb']."</td>";
				$stream .= "<td align=right>".number_format($bar['kgbruto'])."</td>";
				$stream .= "<td align=right>".number_format($bar['kgpotongan'])."</td>";
				$stream .= "<td align=right>".number_format($bar['kgnetto'])."</td>";
				$stream .= "<td align=right>".number_format($bar['jjg'])."</td>";
				$stream .= "<td align=right>".$bar['blok']."</td>";
				$stream .= "<td align=right>".$bar['intiplasma']."</td>";
				$stream .= "<td align=right>".$bar['tahuntanam']."</td>";
				$stream .= "<td align=right>".number_format($bar['rpkg'])."</td>";
				$stream .= "<td align=right>".number_format($bar['totalrp'])."</td>";
				$stream .= "</tr>";

				$tkgbruto    += $bar['kgbruto'];
				$tkgpotongan += $bar['kgpotongan'];
				$tkgnetto    += $bar['kgnetto'];
				$tjjg        += $bar['jjg'];
				$ttotalrp    += $bar['totalrp'];

				// Akumulasi Subtotal per Tanggal SPB
				$subKgBrutoTgl    += $bar['kgbruto'];
				$subKgPotonganTgl += $bar['kgpotongan'];
				$subKgNettoTgl    += $bar['kgnetto'];
				$subJjgTgl        += $bar['jjg'];
				$subTotalRpTgl    += $bar['totalrp'];

				// Akumulasi Rekap per Tanggal SPB, dipecah per No Polisi
				$nopol  = $bar['nokendaraan'];
				if (!isset($rekapTGL[$tglSPB][$nopol])) {
						$rekapTGL[$tglSPB][$nopol] = [
								'kgbruto'    => 0,
								'kgpotongan' => 0,
								'kgnetto'    => 0,
								'jjg'        => 0,
								'totalrp'    => 0,
						];
				}
				$rekapTGL[$tglSPB][$nopol]['kgbruto']    += $bar['kgbruto'];
				$rekapTGL[$tglSPB][$nopol]['kgpotongan'] += $bar['kgpotongan'];
				$rekapTGL[$tglSPB][$nopol]['kgnetto']    += $bar['kgnetto'];
				$rekapTGL[$tglSPB][$nopol]['jjg']        += $bar['jjg'];
				$rekapTGL[$tglSPB][$nopol]['totalrp']    += $bar['totalrp'];

				// Akumulasi Rekap per Tahun Tanam
				$tt = $bar['tahuntanam'];
				if (!isset($rekapTT[$tt])) {
						$rekapTT[$tt] = [
								'kgbruto'    => 0,
								'kgpotongan' => 0,
								'kgnetto'    => 0,
								'jjg'        => 0,
								'totalrp'    => 0,
								'rpkg'       => $bar['rpkg'],
						];
				}
				$rekapTT[$tt]['kgbruto']    += $bar['kgbruto'];
				$rekapTT[$tt]['kgpotongan'] += $bar['kgpotongan'];
				$rekapTT[$tt]['kgnetto']    += $bar['kgnetto'];
				$rekapTT[$tt]['jjg']        += $bar['jjg'];
				$rekapTT[$tt]['totalrp']    += $bar['totalrp'];

				$lastTglSPB = $tglSPB;
		}

		// SUBTOTAL untuk tanggal SPB terakhir
		if ($lastTglSPB !== null) {
				$stream .= "<tr style='background-color:#E2F0D9;font-weight:bold;'>";
				$stream .= "<td align=center colspan=7>SUBTOTAL TANGGAL ".$lastTglSPB."</td>";
				$stream .= "<td align=right>".number_format($subKgBrutoTgl)."</td>";
				$stream .= "<td align=right>".number_format($subKgPotonganTgl)."</td>";
				$stream .= "<td align=right>".number_format($subKgNettoTgl)."</td>";
				$stream .= "<td align=right>".number_format($subJjgTgl)."</td>";
				$stream .= "<td></td><td></td><td></td><td></td>";
				$stream .= "<td align=right>".number_format($subTotalRpTgl)."</td>";
				$stream .= "</tr>";
		}

		$stream .= "<tr bgcolor=#FFD966>";
		$stream .= "<td align=center colspan=7><b>".$_SESSION['lang']['total']."</b></td>";
		$stream .= "<td align=right><b>".number_format($tkgbruto)."</b></td>";
		$stream .= "<td align=right><b>".number_format($tkgpotongan)."</b></td>";
		$stream .= "<td align=right><b>".number_format($tkgnetto)."</b></td>";
		$stream .= "<td align=right><b>".number_format($tjjg)."</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right><b>".number_format($ttotalrp)."</b></td>";
		$stream .= "</tr>";
		$stream .= "</table>";
		$stream .= "<br>";

		// ============================================================
		// SHEET 2: REKAP PER TANGGAL (per No Polisi)
		// ============================================================
		ksort($rekapTGL);

		$streamRekap = "<table border=0>";
		$streamRekap .= "<tr><td colspan=7 align=center><b>Rekap Per Tanggal Periode ".tglnmbln($res[0]['tanggaltbs1'],'','')." s/d ".tglnmbln($res[0]['tanggaltbs2'],'','')."</b></td></tr>";
		$streamRekap .= "</table><br>";

		$streamRekap .= "<table border=1>";
		$streamRekap .= "<tr bgcolor=#D3D3D3>
						<td align=center><b>Tanggal</b></td>
						<td align=center><b>No Polisi</b></td>
						<td align=center><b>Berat TBS (Kg)</b></td>
						<td align=center><b>Potongan (Kg)</b></td>
						<td align=center><b>Netto (Kg)</b></td>
						<td align=center><b>Janjang</b></td>
						<td align=center><b>Total (Rp)</b></td>
				</tr>";

		foreach ($rekapTGL as $tgl => $nopolArr) {
				ksort($nopolArr);
				$subKgBruto = $subKgPotongan = $subKgNetto = $subJjgTGL = $subTotalRpTGL = 0;

				foreach ($nopolArr as $nopol => $val) {
						$streamRekap .= "<tr>";
						$streamRekap .= "<td>".$tgl."</td>";
						$streamRekap .= "<td>".$nopol."</td>";
						$streamRekap .= "<td align=right>".number_format($val['kgbruto'])."</td>";
						$streamRekap .= "<td align=right>".number_format($val['kgpotongan'])."</td>";
						$streamRekap .= "<td align=right>".number_format($val['kgnetto'])."</td>";
						$streamRekap .= "<td align=right>".number_format($val['jjg'])."</td>";
						$streamRekap .= "<td align=right>".number_format($val['totalrp'], 2)."</td>";
						$streamRekap .= "</tr>";

						$subKgBruto    += $val['kgbruto'];
						$subKgPotongan += $val['kgpotongan'];
						$subKgNetto    += $val['kgnetto'];
						$subJjgTGL     += $val['jjg'];
						$subTotalRpTGL += $val['totalrp'];
				}

				if (count($nopolArr) > 1) {
						$streamRekap .= "<tr bgcolor=#E2F0D9>";
						$streamRekap .= "<td align=center colspan=2><b>SUBTOTAL ".$tgl."</b></td>";
						$streamRekap .= "<td align=right><b>".number_format($subKgBruto)."</b></td>";
						$streamRekap .= "<td align=right><b>".number_format($subKgPotongan)."</b></td>";
						$streamRekap .= "<td align=right><b>".number_format($subKgNetto)."</b></td>";
						$streamRekap .= "<td align=right><b>".number_format($subJjgTGL)."</b></td>";
						$streamRekap .= "<td align=right><b>".number_format($subTotalRpTGL, 2)."</b></td>";
						$streamRekap .= "</tr>";
				}
		}

		$streamRekap .= "<tr bgcolor=#FFD966>";
		$streamRekap .= "<td align=center colspan=2><b>".$_SESSION['lang']['total']."</b></td>";
		$streamRekap .= "<td align=right><b>".number_format($tkgbruto)."</b></td>";
		$streamRekap .= "<td align=right><b>".number_format($tkgpotongan)."</b></td>";
		$streamRekap .= "<td align=right><b>".number_format($tkgnetto)."</b></td>";
		$streamRekap .= "<td align=right><b>".number_format($tjjg)."</b></td>";
		$streamRekap .= "<td align=right><b>".number_format($ttotalrp, 2)."</b></td>";
		$streamRekap .= "</tr>";
		$streamRekap .= "</table>";

		// ============================================================
		// SHEET 3: REKAP PER TAHUN TANAM
		// ============================================================
		ksort($rekapTT);

		$streamRekapTT = "<table border=0>";
		$streamRekapTT .= "<tr><td colspan=7 align=center><b>Rekap Per Tahun Tanam Periode ".tglnmbln($res[0]['tanggaltbs1'],'','')." s/d ".tglnmbln($res[0]['tanggaltbs2'],'','')."</b></td></tr>";
		$streamRekapTT .= "</table><br>";

		$streamRekapTT .= "<table border=1>";
		$streamRekapTT .= "<tr bgcolor=#D3D3D3>
						<td align=center><b>Tahun Tanam</b></td>
						<td align=center><b>Berat TBS (Kg)</b></td>
						<td align=center><b>Potongan (Kg)</b></td>
						<td align=center><b>Netto (Kg)</b></td>
						<td align=center><b>Janjang</b></td>
						<td align=center><b>Harga/Kg</b></td>
						<td align=center><b>Total (Rp)</b></td>
				</tr>";

		foreach ($rekapTT as $tt => $val) {
				$streamRekapTT .= "<tr>";
				$streamRekapTT .= "<td align=center><b>".$tt."</b></td>";
				$streamRekapTT .= "<td align=right>".number_format($val['kgbruto'])."</td>";
				$streamRekapTT .= "<td align=right>".number_format($val['kgpotongan'])."</td>";
				$streamRekapTT .= "<td align=right>".number_format($val['kgnetto'])."</td>";
				$streamRekapTT .= "<td align=right>".number_format($val['jjg'])."</td>";
				$streamRekapTT .= "<td align=right>".number_format($val['rpkg'], 2)."</td>";
				$streamRekapTT .= "<td align=right>".number_format($val['totalrp'], 2)."</td>";
				$streamRekapTT .= "</tr>";
		}

		$streamRekapTT .= "<tr bgcolor=#FFD966>";
		$streamRekapTT .= "<td align=center><b>".$_SESSION['lang']['total']."</b></td>";
		$streamRekapTT .= "<td align=right><b>".number_format($tkgbruto)."</b></td>";
		$streamRekapTT .= "<td align=right><b>".number_format($tkgpotongan)."</b></td>";
		$streamRekapTT .= "<td align=right><b>".number_format($tkgnetto)."</b></td>";
		$streamRekapTT .= "<td align=right><b>".number_format($tjjg)."</b></td>";
		$streamRekapTT .= "<td align=right></td>";
		$streamRekapTT .= "<td align=right><b>".number_format($ttotalrp, 2)."</b></td>";
		$streamRekapTT .= "</tr>";
		$streamRekapTT .= "</table>";

		// ============================================================
		// OUTPUT KE EXCEL (3 SHEET)
		// ============================================================
		$nop = "TBS_".$param['notransaksi']."_".$tgl1excel1."_s/d_".$tgl1excel2.".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("Detail Penjualan TBS", $stream);
		$xls->addSheet("Rekap Per Tanggal", $streamRekap);
		$xls->addSheet("Rekap Tahun Tanam", $streamRekapTT);
		$xls->headers($nop);
		echo $xls->buildFile();
	break;

	case'posting':
		try {
			$owlPDO->beginTransaction();
			
			$str = "update ".$dbname.".".$table." set posting=1,postingby='".$_SESSION['standard']['userid']."',postingtime='".date('Y-m-d H:i:s')."' where notransaksi='".$param['notransaksi']."' ";
			// exit("Error:$str");
			$owlPDO->exec($str);
			
			$owlPDO->commit();			
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
			
	case'getcust':
						 
        $strkd=" select koderekanan from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['nokontrak']."'";
		$res=fetchdata($strkd);
		foreach($res as $bar){
			$optcust.="<option value=" . $bar['koderekanan'] . ">[".$bar['koderekanan']."] - " . $namacustomer[$bar['koderekanan']] . "</option>";
		}
 
		echo $optcust;
		// echo $optsupp."####".$optkk;
	break;

	case'deleteht':
		try {
			$owlPDO->beginTransaction();
			
			$str = "delete from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."' ";
			
			$owlPDO->exec($str);
			
			$owlPDO->commit();			
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;

	case'geteditht':
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$notransaksi=$bar['notransaksi'];
			$unit=$bar['unit'];
			$kodecustomer=$bar['kodecustomer'];
			$tanggal=$bar['tanggal'];
			$tanggaltbs1=$bar['tanggaltbs1'];
			$tanggaltbs2=$bar['tanggaltbs2'];
			$keteranganht=$bar['keteranganht'];
			$sortasi=$bar['statussortasi'];
			$kodero=$bar['kodero'];
			$nokontrak=$bar['nokontrak'];
		}
			
		
		echo $notransaksi."###".$unit."###".tanggalnormal($tanggal)."###".$kodecustomer."###".tanggalnormal($tanggaltbs1)."###".tanggalnormal($tanggaltbs2)."###".$keteranganht."###".$sortasi."###".$kodero."###".$nokontrak;
		
		// exit("Error:a");
	break;

  case'loaddata':
	
		$where=" 1=1 ";
		if($param['tanggalselesaisch']!='' and $param['tanggalmulaisch']!=''){
			$where.=" and tanggal between '".$param['tanggalmulaisch']."' and '".$param['tanggalselesaisch']."'";
		}
		if($param['notransaksisch']!=''){
			$where.=" and notransaksi like '%".$param['notransaksisch']."%'";
		}
		
		if($param['kodecustomersch']!=''){
			$where.=" and kodecustomer like '%".$param['kodecustomersch']."%'";
		}
		
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
		
		$offset = $page * $limit;
		// $str = "select count(*) as jlhbrs from ".$dbname.".".$table." where ".$where." group by notransaksi  ";
		$str = "select count(distinct(notransaksi)) as jlhbrs from ".$dbname.".".$table." where ".$where." and unit in (" . getOrgDetail(2) . ")";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jlhbrs = $bar['jlhbrs'];
		}
		
		$colspan=15;
		$no = 0;
		$no=$maxdisplay;
		$str = "select sum(kgbruto) as kgbruto,sum(kgnetto) as kgnetto,sum(kgpotongan) as kgpotongan,sum(totalrp) as totalrp,max(revstatus) as revstatus,tanggal,tanggaltbs1,tanggaltbs2,kodecustomer,keteranganht,unit,notransaksi,posting,createby,postingby,createtime,postingtime from ".$dbname.".".$table." where ".$where." and unit in (" . getOrgDetail(2) . ")  group by notransaksi order by tanggal desc,notransaksi desc  limit " . $offset . "," . $limit . " ";
		$res=fetchdata($str);
		foreach($res as $bar){
			
			#=datakaryawan
			$strdt="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid in ('".$bar['createby']."','".$bar['postingby']."') ";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namakaryawan[$bardt['karyawanid']]=$bardt['namakaryawan'];
			}

			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$bar['notransaksi']."</td>";
				$stream.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$stream.="<td>".$bar['unit']."</td>";
				$stream.="<td>".@$namacustomer[$bar['kodecustomer']]."</td>";
				$stream.="<td>".tanggalnormal($bar['tanggaltbs1'])." s/d ".tanggalnormal($bar['tanggaltbs2'])."</td>";
				$stream.="<td align=right>".number_format($bar['kgbruto'])."</td>";
				$stream.="<td align=right>".number_format($bar['kgnetto'])."</td>";
				$stream.="<td align=right>".number_format($bar['kgpotongan'])."</td>";
				$stream.="<td align=right>".number_format($bar['totalrp'], 2)."</td>";

				$statusRev = (int)$bar['revstatus'];
				switch ($statusRev) {
					case 9:
						$stream.="<td align=center><font color=blue>Menunggu Persetujuan</font></td>";
						break;
					case 1:
						$stream.="<td align=center><font color=green>Disetujui</font></td>";
						break;
					case 2:
						$stream.="<td align=center><font color=red>Ditolak</font></td>";
						break;
					default:
						$stream.="<td align=center>-</td>";
						break;
				}

				$stream.="<td>".$bar['keteranganht']."</td>";
				$stream.="<td>".@$namakaryawan[$bar['createby']]."</td>";
				$stream.="<td>".@$namakaryawan[$bar['postingby']]."</td>";
				
				$stream.="<td align=center>";
				if($bar['posting']==0){
					$stream.="<img src=images/application/application_edit.png class=resicon  caption='Edit' 
						onclick=\"editht('".$bar['notransaksi']."');\">";
					$stream.="&nbsp;&nbsp;&nbsp;<img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"deleteht('".$bar['notransaksi']."');\">";		
					$stream.="&nbsp;&nbsp;&nbsp;<img src=images/skyblue/posting.png class=resicon  title='Posting Data' onclick=\"posting('".$bar['notransaksi']."');\" >";
				} else{
					$stream.="&nbsp;&nbsp;&nbsp;<img src=images/skyblue/posted.png class=resicon  title='Posted'>";
				}
				$stream.="&nbsp;&nbsp;&nbsp;<img src=images/excel.jpg class=resicon  caption='Excel'  title='Excel  ".$bar['notransaksi']."' onclick=\"excel('".$bar['notransaksi']."');\">";
				// Koreksi cuma boleh untuk transaksi yang sudah posting - sebelum posting datanya
				// masih bisa diedit lewat menu edit biasa, jadi belum perlu alur approval koreksi.
				if($bar['posting']==1){
					$stream.="&nbsp;&nbsp;&nbsp;<img src=images/gear_64.png class=resicon  caption='Koreksi'  title='Koreksi  ".$bar['notransaksi']."' onclick=\"koreksi('".$bar['notransaksi']."');\">";
				}
				$stream.="</td>";
			$stream.="</tr>";
        }
		
		$footd.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		// $tab.="</table>";
		
		echo $stream."####".$footd;
	break;
	
	case'saveht':
	
		#= validasi apakah ada nokontrak dan customer tersebut di pmn_kontrakjual
		
		if($param['notransaksi']==''){
			// $str = "select count(*) as jumlah from ".$dbname.".pmn_kontrakjual  where  nokontrak='".$param['nokontrak']."' and koderekanan='".$param['kodecustomer']."'";	
			// $res=fetchdata($str);
			// foreach($res as $bar){
				// $jumlah=$bar['jumlah'];
			// }
		
			// if($jumlah<1){
				// exit("Warning:Tidak ada nomor kontrak ".$param['nokontrak']." dengan customer ".$namacustomer[$param['kodecustomer']]." ");
			// }
		
			$unit=$param['unit'];
			$tanggal=tanggalsystemn($param['tanggal']);
			$notransaksi = generatenotransaksitbsbeli();
		}else{
			$notransaksi=$param['notransaksi'];
		}
		echo $notransaksi;
	break;
	
	case'loaddatadt':
			$notransaksi = $param['notransaksi'];

			$tglAwalBaru  = tanggalsystemn($param['tanggaltbs1']);
			$tglAkhirBaru = tanggalsystemn($param['tanggaltbs2']);

			// 1. Cek Sumber Data
			$strcount = "SELECT count(*) as count FROM ".$dbname.".kebun_tbsjual WHERE notransaksi='".$notransaksi."'";
			$rescount = fetchdata($strcount);

			if ($rescount[0]['count'] == 0) {
					// Transaksi baru - cek dulu apakah periode tanggal ini (unit+customer yang sama)
					// sudah pernah ditarik jadi BA Penjualan lain, biar gak ke-double tarik tiket yang sama.
					$strCekSudahTarik = "select distinct notransaksi, tanggaltbs1, tanggaltbs2 from ".$dbname.".kebun_tbsjual
							where unit='".$param['unit']."' and kodecustomer='".$param['kodecustomer']."'
							and tanggaltbs1 <= '".$tglAkhirBaru."' and tanggaltbs2 >= '".$tglAwalBaru."'";
					$resCekSudahTarik = fetchdata($strCekSudahTarik);
					if (!empty($resCekSudahTarik)) {
							$daftarBentrok = [];
							foreach ($resCekSudahTarik as $bent) {
									$daftarBentrok[] = $bent['notransaksi']." (".tanggalnormal($bent['tanggaltbs1'])." s/d ".tanggalnormal($bent['tanggaltbs2']).")";
							}
							echo "Warning:Periode tanggal ".tanggalnormal($tglAwalBaru)." s/d ".tanggalnormal($tglAkhirBaru)." untuk unit & customer ini sudah pernah ditarik di BA Penjualan: ".implode(', ', $daftarBentrok).". Tidak bisa ditarik berulang.";
							exit;
					}
			}

			// Sumber data SELALU dari tabel timbangan asal (bukan dari kebun_tbsjual yang sudah
			// tersimpan) - baik transaksi baru maupun yang sudah pernah disimpan. Ini penting karena
			// 1 baris tersimpan di kebun_tbsjual cuma nyimpen 1 nospb wakil dari gabungan beberapa
			// tiket (truck+tanggal+tahun tanam yang sama), jadi kalau narik ulang berdasarkan nospb
			// yang tersimpan itu, tiket lain yang aslinya ikut digabung akan hilang dan angka jadi
			// lebih kecil / berubah setiap kali form ini dibuka ulang setelah simpan.
			$tabledata = "pabrik_timbangan_vw";
			$whrdata = "AND kodeorg='".$param['unit']."' AND tanggal BETWEEN '".$tglAwalBaru."' AND '".$tglAkhirBaru."' AND kodecustomer='".$param['kodecustomer']."'";

			// 2. Ambil Data Transaksi Utama
			$str = "SELECT nospb, tanggal, notiket FROM ".$dbname.".".$tabledata." WHERE 1=1 $whrdata";
			$res = fetchdata($str);

			if (empty($res)) {
					echo "Data tidak ditemukan.";
					exit;
			}

			$arrnospbpks = [];
			$pksData = [];
			foreach ($res as $bar) {
					$arrnospbpks[] = $bar['nospb'];
					$pksData[$bar['nospb']] = [
							'tanggal' => $bar['tanggal'],
							'notiket' => $bar['notiket']
					];
			}

			// 3. Ambil Detail SPB Penjualan
			$strSPB = "SELECT nospb, blok, nokendaraan, tanggal, kgwb, kgwbnetto, jjg, tahuntanam, intiplasma 
								FROM ".$dbname.".kebun_spb_penjualan_vw 
								WHERE nospb IN ('".implode("','", $arrnospbpks)."')
								ORDER BY tanggal ASC, nospb ASC";
			$resSPB = fetchdata($strSPB);

			// Kumpulkan list Tahun Tanam unik
			$listTahunTanam = [];
			foreach ($resSPB as $row) {
					if (!empty($row['tahuntanam'])) {
							$listTahunTanam[$row['tahuntanam']] = $row['tahuntanam'];
					}
			}

			// 4. Batch Query Harga TBS
			$hargaMaster = [];
			if (!empty($listTahunTanam)) {
					$strHarga = "SELECT tahuntanam, tanggal, tanggal2, harga 
											FROM ".$dbname.".pmn_hargajualtbs 
											WHERE kodeorg='".$param['unit']."' 
												AND kodecustomer='".$param['kodecustomer']."' 
												AND tahuntanam IN ('".implode("','", $listTahunTanam)."') 
												AND posting=1 
											ORDER BY tanggal DESC";
					$resHarga = fetchdata($strHarga);
					foreach ($resHarga as $h) {
							$hargaMaster[$h['tahuntanam']][] = $h;
					}
			}

			// 5. Olah Data & Buat Tampilan Tabel Utama
			$stream = "<div class='table-scroll' style='max-height: 900px; overflow-y: auto; overflow-x: auto; border: 1px solid #ccc; margin-bottom: 15px;'>";
			$stream .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>
				<thead>
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['nourut']."</th>
						<th align=center>".$_SESSION['lang']['noTiket']."</th>
						<th align=center>".$_SESSION['lang']['nospb']."</th>
						<th align=center>".$_SESSION['lang']['kodevhc']."</th>
						<th align=center>".$_SESSION['lang']['tanggal']."<br>PKS</th>
						<th align=center>".$_SESSION['lang']['tanggal']."<br>SPB</th>
						<th align=center>".$_SESSION['lang']['berat']." TBS</th>
						<th align=center>".$_SESSION['lang']['potongan']."</th>
						<th align=center>".$_SESSION['lang']['netto']."</th> 
						<th align=center>".$_SESSION['lang']['jjg']."</th> 
						<th align=center>".$_SESSION['lang']['bjr']."</th>
						<th hidden align=center>".$_SESSION['lang']['blok']."</th>
						<th align=center>".$_SESSION['lang']['tahuntanam']."</th>
						<th align=center>".$_SESSION['lang']['intiplasma']."</th>   
						<th align=center>*</th>    
					</tr>
				</thead>
				<tbody>";

			// Kelompokkan per truck+tanggal+tahun tanam - 1 baris tampilan = 1 baris yang kesimpan
			// ke kebun_tbsjual, BUKAN lagi 1 baris per tiket timbangan/blok. Ini biar tampilan &
			// data yang tersimpan persis sama kayak cara rekap grading manual (Excel) menggabungkan
			// semua tiket 1 truck di 1 tanggal jadi 1 baris. Kalau ternyata 1 gabungan itu dari
			// beberapa blok berbeda, blok-nya ditandai "Campur" (bukan digabung diam-diam) - nospb,
			// notiket, tanggal PKS, dan intiplasma diambil dari tiket PERTAMA dalam gabungan itu.
			$groups = [];
			$groupOrder = [];
			foreach ($resSPB as $bar) {
					$nospb  = $bar['nospb'];
					$keyGrp = $bar['tanggal'].'|'.$bar['nokendaraan'].'|'.$bar['tahuntanam'];
					if (!isset($groups[$keyGrp])) {
							$groups[$keyGrp] = [
									'tanggalspb'  => $bar['tanggal'],
									'nokendaraan' => $bar['nokendaraan'],
									'tahuntanam'  => $bar['tahuntanam'],
									'kgbruto'     => 0,
									'kgnetto'     => 0,
									'jjg'         => 0,
									'bloklist'    => [],
									'nospb'       => $nospb,
									'notiket'     => isset($pksData[$nospb]) ? $pksData[$nospb]['notiket'] : '',
									'tanggalpks'  => isset($pksData[$nospb]) ? $pksData[$nospb]['tanggal'] : '',
									'intiplasma'  => $bar['intiplasma'],
							];
							$groupOrder[] = $keyGrp;
					}
					$groups[$keyGrp]['kgbruto'] += (float)$bar['kgwb'];
					$groups[$keyGrp]['kgnetto'] += (float)$bar['kgwbnetto'];
					$groups[$keyGrp]['jjg']     += (float)$bar['jjg'];
					$groups[$keyGrp]['bloklist'][$bar['blok']] = $bar['blok'];
			}

			$no = 0;
			$nomax = count($groupOrder);
			$tdtkgbruto = $tdtkgpotongan = $tdtkgnetto = $tdtjjg = $tdttotalrp = 0;
			$subBruto = $subPotongan = $subNetto = $subJjg = $subTotalRp = 0;
			$errtampung = '';
			$lastTanggal = null;

			foreach ($groupOrder as $keyGrp) {
					$g = $groups[$keyGrp];
					$tglSPB = $g['tanggalspb'];

					// Subtotal jika berganti tanggal
					if ($lastTanggal !== null && $lastTanggal != $tglSPB) {
							$stream .= "<tr style='background-color: #e2f0d9; font-weight: bold;'>";
							$stream .= "<td align=center colspan=6>SUBTOTAL TANGGAL ".tanggalnormal($lastTanggal)."</td>";
							$stream .= "<td align=right>".number_format($subBruto)."</td>";
							$stream .= "<td align=right>".number_format($subPotongan)."</td>";
							$stream .= "<td align=right>".number_format($subNetto)."</td>";
							$stream .= "<td align=right>".number_format($subJjg)."</td>";
							$stream .= "<td align=right>".($subJjg > 0 ? number_format($subNetto / $subJjg, 2) : '0.00')."</td>";
							$stream .= "<td></td>";
							$stream .= "<td></td>";
							$stream .= "<td align=right>".number_format($subTotalRp, 2)."</td>";
							$stream .= "</tr>";

							$subBruto = $subPotongan = $subNetto = $subJjg = $subTotalRp = 0;
					}

					$no++;
					$nospb = $g['nospb'];
					$kodeblokList = array_values($g['bloklist']);
					$kodeblok = count($kodeblokList) > 1 ? 'Campur ('.implode(', ', $kodeblokList).')' : ($kodeblokList[0] ?? '-');
					$tglPKS = $g['tanggalpks'];
					$noTiket = $g['notiket'];
					$thnTanam = $g['tahuntanam'];

					// Netto dibulatkan ke kg genap DI LEVEL GABUNGAN (bukan per tiket) - samain sama
					// cara rekap grading manual (Excel). Potongan diturunkan dari bruto-netto(bulat)
					// biar bruto/potongan/netto tetap konsisten kalau dijumlah.
					$kgBruto = $g['kgbruto'];
					$kgNetto = round($g['kgnetto']);
					$kgPotongan = $kgBruto - $kgNetto;
					$jjg = $g['jjg'];
					$bjr = $jjg > 0 ? ($kgNetto / $jjg) : 0;

					// Pencocokan Harga
					$hargaPerKg = 0;
					if (isset($hargaMaster[$thnTanam])) {
							foreach ($hargaMaster[$thnTanam] as $h) {
									if ($h['tanggal'] <= $tglPKS && $h['tanggal2'] >= $tglPKS) {
											$hargaPerKg = $h['harga'];
											break;
									}
							}
					}

					$totalRp = $hargaPerKg * $kgNetto;

					// Akumulasi Subtotal Harian
					$subBruto += $kgBruto;
					$subPotongan += $kgPotongan;
					$subNetto += $kgNetto;
					$subJjg += $jjg;
					$subTotalRp += $totalRp;

					// Akumulasi Grand Total
					$tdtkgbruto += $kgBruto;
					$tdtkgpotongan += $kgPotongan;
					$tdtkgnetto += $kgNetto;
					$tdtjjg += $jjg;
					$tdttotalrp += $totalRp;

					$errHtml = "<td></td>";
					if ($hargaPerKg <= 0) {
							$errHtml = "<td><font color=red>Belum terdapat Harga untuk tahun tanam ".$thnTanam." atau belum disetujui</font></td>";
							$errtampung = "Belum terdapat Harga untuk tahun tanam ".$thnTanam;
					}

					$stream .= "<tr class=rowcontent id=row".$no.">";
					$stream .= "<td align=center>".$no."</td>";
					$stream .= "<td id=notiket".$no.">".$noTiket."</td>";
					$stream .= "<td id=nospb".$no.">".$nospb."</td>";
					$stream .= "<td id=nokendaraan".$no.">".$g['nokendaraan']."</td>";
					$stream .= "<td id=tanggalpks".$no.">".tanggalnormal($tglPKS)."</td>";
					$stream .= "<td id=tanggalspb".$no.">".tanggalnormal($g['tanggalspb'])."</td>";

					# Input Hidden Old (Dibutuhkan fungsi simpan JS)
					$stream .= "<td hidden align=right><input id=oldkgbruto".$no." type=text class=myinputtextnumber value='".number_format($kgBruto, 8, '.', '')."' onkeypress='return angka_doang(event)' /></td>";
					$stream .= "<td hidden align=right><input type=text id=oldkgpotongan".$no." class=myinputtextnumber value='".number_format($kgPotongan, 8, '.', '')."' onkeypress='return angka_doang(event)' /></td>";
					$stream .= "<td hidden align=right><input id=oldkgnetto".$no." type=text class=myinputtextnumber value='".number_format($kgNetto, 8, '.', '')."' onkeypress='return angka_doang(event)' /></td>";
					$stream .= "<td hidden align=right id=oldjjg".$no.">".number_format($jjg, 8, '.', '')."</td>";
					$stream .= "<td hidden align=right id=oldbjr".$no.">".number_format($bjr, 8, '.', '')."</td>";

					# Input Baru
					$stream .= "<td align=right><input disabled id=kgbruto".$no." type=text class='myinputtextnumber kgbrutox' value='".number_format($kgBruto, 2, '.', ',')."' onkeypress='return angka_doang(event)' onkeyup=\"hitungBruto('".$no."','".$nomax."');\" /></td>";
					$stream .= "<td align=right><input disabled type=text id=kgpotongan".$no." class='myinputtextnumber kgpotx' value='".number_format($kgPotongan, 2, '.', ',')."' onkeypress='return angka_doang(event)' onkeyup=\"hitungPotongan('".$no."','".$nomax."');\" /></td>";
					$stream .= "<td align=right><input disabled id=kgnetto".$no." type=text class='myinputtextnumber kgnettox' value='".number_format($kgNetto, 2, '.', ',')."' onkeypress='return angka_doang(event)' onkeyup=\"hitungNetto('".$no."','".$nomax."');\" /></td>";
					$stream .= "<td align=right id=jjg".$no.">".number_format($jjg)."</td>";
					$stream .= "<td align=right id=bjr".$no.">".number_format($bjr, 2)."</td>";
					$stream .= "<td hidden id=blok".$no.">".$kodeblok."</td>";
					$stream .= "<td align=right id=tahuntanam".$no.">".$thnTanam."</td>";
					$stream .= "<td id=intiplasma".$no." align='center'>".$g['intiplasma']."</td>";
					$stream .= "<td hidden align=right id=rpkg".$no.">".number_format($hargaPerKg, 2)."</td>";
					$stream .= "<td hidden align=right id=totalrp".$no." class='totalrpx'>".number_format($totalRp, 2)."</td>";
					$stream .= $errHtml;
					$stream .= "</tr>";

					$lastTanggal = $tglSPB;
			}

			// Subtotal Tanggal Terakhir
			if ($lastTanggal !== null) {
					$stream .= "<tr style='background-color: #e2f0d9; font-weight: bold;'>";
					$stream .= "<td align=center colspan=6>SUBTOTAL TANGGAL ".tanggalnormal($lastTanggal)."</td>";
					$stream .= "<td align=right>".number_format($subBruto)."</td>";
					$stream .= "<td align=right>".number_format($subPotongan)."</td>";
					$stream .= "<td align=right>".number_format($subNetto)."</td>";
					$stream .= "<td align=right>".number_format($subJjg)."</td>";
					$stream .= "<td align=right>".($subJjg > 0 ? number_format($subNetto / $subJjg, 2) : '0.00')."</td>";
					$stream .= "<td></td>";
					$stream .= "<td></td>";
					$stream .= "<td align=right>".number_format($subTotalRp, 2)."</td>";
					$stream .= "</tr>";
			}

			// Baris Grand Total
			$stream .= "<tr class=rowheader style='font-weight: bold;'>";
			$stream .= "<td align=center colspan=6>".$_SESSION['lang']['total']."</td>";
			$stream .= "<td id=ttlkgbruto align=right>".number_format($tdtkgbruto)."</td>";
			$stream .= "<td id=ttlkgpot align=right>".number_format($tdtkgpotongan)."</td>";
			$stream .= "<td id=ttlkgnetto align=right>".number_format($tdtkgnetto)."</td>";
			$stream .= "<td id=ttljjg align=right>".number_format($tdtjjg)."</td>";
			$stream .= "<td align=right>".($tdtjjg > 0 ? number_format($tdtkgnetto / $tdtjjg, 2) : '0.00')."</td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td id=ttlrp align=right>".number_format($tdttotalrp, 2)."</td>";
			$stream .= "</tr>";

			// Tombol Aksi
			$stream .= "<tr class=rowcontent>";
			if ($errtampung != '') {
					$stream .= "<td align=center colspan=20><font color=red><b>".$errtampung."</b></font> <button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button><button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button></td>";
			} else {
					$stream .= "<td align=center colspan=20><button id=save class=mybutton onclick=savedt(".$nomax.")>".$_SESSION['lang']['save']."</button> <button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button><button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button></td>";
			}
			$stream .= "</tr>";
			
			$stream .= "</tbody></table>";
			$stream .= "</div>"; // Tutup div scroll tabel utama di sini
			echo $stream;   
	break;
	
	case'loaddatadtori':
		
		$str = "select * from ".$dbname.".pabrik_timbangan_vw where kodeorg='".$param['unit']."' and tanggal between '".tanggalsystemn($param['tanggaltbs1'])."' and '".tanggalsystemn($param['tanggaltbs2'])."' and kodecustomer='".$param['kodecustomer']."'";	
		 
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnospbpks[$bar['nospb']]=$bar['nospb'];
			$dttanggalpks[$bar['nospb']]=$bar['tanggal'];
			$dtnotiket[$bar['nospb']]=$bar['notiket'];
		}
		
		$str = "select * from ".$dbname.".kebun_spb_penjualan_vw where (nospb in ('".implode("','",$arrnospbpks)."'))";	
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnospb[$bar['nospb']]=$bar['nospb'];
			$arrkodeblok[$bar['blok']]=$bar['blok'];
			$listkodeblok[$bar['nospb']][$bar['blok']]=$bar['blok'];
			$dtnokendaraan[$bar['nospb']][$bar['blok']]=$bar['nokendaraan'];
			$dttanggalspb[$bar['nospb']][$bar['blok']]=$bar['tanggal'];
			$dtkgbruto[$bar['nospb']][$bar['blok']]+=$bar['kgwb'];
			// if ($param['sortasi'] == '0'){
			// 	$dtkgnetto[$bar['nospb']][$bar['blok']]+=$bar['kgwb'];
			// 	$dtkgpotongan[$bar['nospb']][$bar['blok']]=0;
			// } else {
				# Tidak perlu ngecheck sortasi atau tidak karena tetap memakai timbangan buyer
				$dtkgnetto[$bar['nospb']][$bar['blok']]+=$bar['kgwbnetto'];
				$dtkgpotongan[$bar['nospb']][$bar['blok']]+=$bar['kgwb']-$bar['kgwbnetto'];
			// }
			$dtjjg[$bar['nospb']][$bar['blok']]=$bar['jjg'];
			$dttahuntanam[$bar['nospb']][$bar['blok']]=$bar['tahuntanam'];
			// $dttahuntanam[$bar['nospb']][$bar['blok']]=$bar['tahuntanamspbht'];
			$dtintiplasma[$bar['blok']]=$bar['intiplasma'];
			@$nomax+=1;
        }
		
		
		$stream.="<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader>
				 <th  align=center>".$_SESSION['lang']['nourut']."</th>
				 <th  align=center>".$_SESSION['lang']['noTiket']."</th>
				 <th  align=center>".$_SESSION['lang']['nospb']."</th>
				 <th  align=center>".$_SESSION['lang']['kodevhc']."</th>
				 <th  align=center>".$_SESSION['lang']['tanggal']."<br>PKS</th>
				 <th  align=center>".$_SESSION['lang']['tanggal']."<br>SPB</th>
				 <th  align=center>".$_SESSION['lang']['berat']." TBS</th>
				 <th  align=center>".$_SESSION['lang']['potongan']."</th>
				 <th  align=center>".$_SESSION['lang']['netto']."</th> 
				 <th  align=center>".$_SESSION['lang']['jjg']."</th> 
				 <th  align=center>".$_SESSION['lang']['blok']."</th>   
				 <th  align=center>".$_SESSION['lang']['tahuntanam']."</th>   
				 <th  align=center>".$_SESSION['lang']['intiplasma']."</th>   
				 <th  align=center>".$_SESSION['lang']['harga']."</th>   
				 <th  align=center>".$_SESSION['lang']['total']."</th>   
				 <th  align=center>*</th> 		
			</thead></tr>";
	
			foreach(@$arrnospb as $nospb){
				foreach($arrkodeblok as $kodeblok){
					if(@$listkodeblok[$nospb][$kodeblok]){
						@$no++;
						$stream.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
							$stream.="<td align=center>".$no."</td>";
							$stream.="<td id=notiket".$no.">".$dtnotiket[$nospb]."</td>";
							$stream.="<td id=nospb".$no.">".$nospb."</td>";
							$stream.="<td id=nokendaraan".$no.">".$dtnokendaraan[$nospb][$kodeblok]."</td>";
							$stream.="<td id=tanggalpks".$no.">".tanggalnormal($dttanggalpks[$nospb])."</td>";
							$stream.="<td id=tanggalspb".$no.">".tanggalnormal($dttanggalspb[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=kgbruto".$no.">".number_format($dtkgbruto[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=kgpotongan".$no.">".number_format($dtkgpotongan[$nospb][$kodeblok])."</td>";
							$stream.="<td disabled align=right id=kgnetto".$no.">".number_format($dtkgnetto[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=jjg".$no.">".number_format($dtjjg[$nospb][$kodeblok])."</td>";
							$stream.="<td id=blok".$no.">".$kodeblok."</td>";
							$stream.="<td align=right id=tahuntanam".$no.">".$dttahuntanam[$nospb][$kodeblok]."</td>";
							$stream.="<td id=intiplasma".$no." align='center'>".$dtintiplasma[$kodeblok]."</td>";
							// $str = "select * from ".$dbname.".pmn_kontrakjualdt_harga  where nokontrak='".$param['nokontrak']."' and tanggalkirim2>='".$dttanggalpks[$nospb]."' and tanggalkirim1<='".$dttanggalpks[$nospb]."' and tahuntanam='".$dttahuntanam[$nospb][$kodeblok]."' order by tanggalkirim1 desc limit 1";
							
							$str="select * from ".$dbname.".pmn_hargajualtbs where  kodeorg='".$param['unit']."' and kodecustomer='".$param['kodecustomer']."' and tahuntanam='".$dttahuntanam[$nospb][$kodeblok]."' and tanggal<='".$dttanggalpks[$nospb]."' and tanggal2>='".$dttanggalpks[$nospb]."'  and posting=1 order by tanggal desc limit 1";
							// echo $str;
							$res=fetchdata($str);
							foreach($res as $bar){
								$datarpkg[$bar['tahuntanam']]=$bar['harga'];
							}	
							$stream.="<td align=right id=rpkg".$no.">".number_format($datarpkg[$dttahuntanam[$nospb][$kodeblok]],2)."</td>";
								$dttotalrp[$nospb][$kodeblok]=$datarpkg[$dttahuntanam[$nospb][$kodeblok]]*$dtkgnetto[$nospb][$kodeblok];
							$stream.="<td align=right id=totalrp".$no.">".number_format($dttotalrp[$nospb][$kodeblok],2)."</td>";
							
							
							if($datarpkg[$dttahuntanam[$nospb][$kodeblok]]==0 || $datarpkg[$dttahuntanam[$nospb][$kodeblok]]==''){
								$stream.="<td><font color=red>Belum terdapat Harga untuk tahun tanam ".$dttahuntanam[$nospb][$kodeblok]." atau belum disetujui</font></td>";
								$errtampung=" Belum terdapat Harga untuk tahun tanam ".$dttahuntanam[$nospb][$kodeblok]."";
							} else{
								$stream.="<td></td>";		
							}		
							
						$stream.="</tr>";	
						
						$tdtkgmasuk+=$dtkgmasuk[$nospb][$kodeblok];
						$tdtkgkeluar+=$dtkgkeluar[$nospb][$kodeblok];
						$tdtkgbruto+=$dtkgbruto[$nospb][$kodeblok];
						$tdtkgpotongan+=$dtkgpotongan[$nospb][$kodeblok];
						$tdtkgnetto+=$dtkgnetto[$nospb][$kodeblok];
						$tdtjjg+=$dtjjg[$nospb][$kodeblok];
						$tdttotalrp+=$dttotalrp[$nospb][$kodeblok];
						
					}
				}
			}
	
			$stream.="<tr class=rowheader>";
				$stream.="<td align=center colspan=6>".$_SESSION['lang']['total']."</td>";
				$stream.="<td align=right>".@number_format($tdtkgbruto)."</td>";
				$stream.="<td align=right>".@number_format($tdtkgpotongan)."</td>";
				$stream.="<td align=right>".@number_format($tdtkgnetto)."</td>";
				$stream.="<td align=right>".@number_format($tdtjjg)."</td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right>".@number_format($tdttotalrp,2)."</td>";
				$stream.="<td align=right></td>";
			$stream.="</tr>";
			
			
			if($errtampung!=''){
				$stream.="<tr class=rowcontent>";
					$stream.="<td align=center colspan=20><font color=red><b>".$errtampung."</b></font><button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button><button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button></td>";
				$stream.="</tr>";	
			}else{
				$stream.="<tr class=rowcontent>";
					$stream.="<td align=center colspan=20><button  id=save class=mybutton onclick=savedt(".@$no.")>".$_SESSION['lang']['save']."</button>
					<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
					<button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button></td>";
				$stream.="</tr>";	
			// <button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button></td>";	
			}
			
			
			
		$stream.="</table>";
	
		
		echo $stream;
		
		
		// exit("Error");
		
		
	break;
	
	case'savedt':
	
		$param['rpkg']=str_replace(',', '',$param['rpkg']);
		$param['totalrp']=str_replace(',', '',$param['totalrp']);
		$param['kgbruto']=str_replace(',', '',$param['kgbruto']);
		$param['kgnetto']=str_replace(',', '',$param['kgnetto']);
		$param['kgpotongan']=str_replace(',', '',$param['kgpotongan']);
		$param['jjg']=str_replace(',', '',$param['jjg']);
		$param['bjr']=($param['jjg']>0 ? ($param['kgnetto']/$param['jjg']) : 0);

		# Data OLD
		$param['oldtotalrp']=(str_replace(',', '',$param['rpkg'])*str_replace(',', '',$param['oldkgnetto']));
		$param['oldkgbruto']=str_replace(',', '',$param['oldkgbruto']);
		$param['oldkgnetto']=str_replace(',', '',$param['oldkgnetto']);
		$param['oldkgpotongan']=str_replace(',', '',$param['oldkgpotongan']);
		$param['oldjjg']=str_replace(',', '',$param['oldjjg']);
		$param['oldbjr']=($param['oldjjg']>0 ? ($param['oldkgnetto']/$param['oldjjg']) : 0);
		
		if($param['sortasi'] == "") {
			$param['sortasi'] = 0;
		}
		
		try {
			$owlPDO->beginTransaction();
			
			#= delete 1st
			if($param['currRow']=='1'){
				#= delete 1st
				$str = "delete from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."'";
				// exit("Error:$str");
				$owlPDO->exec($str);
			} 
			
			$str = "insert into ".$dbname.".".$table." (notransaksi,tanggal,tanggaltbs1,tanggaltbs2,tanggalspb,tanggalpks,keteranganht,nospb,nokontrak,notiket,unit,blok,kgbruto,kgpotongan,kgnetto,jjg,bjr,tahuntanam,rpkg,totalrp,nokendaraan,kodecustomer,createby,createtime,updateby,updatetime,intiplasma,kodero,statussortasi,oldtotalrp,oldkgbruto,oldkgnetto,oldkgpotongan,oldjjg,oldbjr)
			VALUES ('".$param['notransaksi']."','".tanggalsystemn($param['tanggal'])."','".tanggalsystemn($param['tanggaltbs1'])."','".tanggalsystemn($param['tanggaltbs2'])."','".tanggalsystemn($param['tanggalspb'])."','".tanggalsystemn($param['tanggalpks'])."','".$param['keteranganht']."','".$param['nospb']."','".$param['nokontrak']."','".$param['notiket']."','".$param['unit']."','".$param['blok']."','".$param['kgbruto']."','".$param['kgpotongan']."','".$param['kgnetto']."','".$param['jjg']."','".$param['bjr']."','".$param['tahuntanam']."','".$param['rpkg']."','".$param['totalrp']."','".$param['nokendaraan']."','".$param['kodecustomer']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$param['intiplasma']."','".$param['kodero']."',".$param['sortasi'].",'".$param['oldtotalrp']."','".$param['oldkgbruto']."','".$param['oldkgnetto']."','".$param['oldkgpotongan']."','".$param['oldjjg']."','".$param['oldbjr']."')";
			$owlPDO->exec($str);
			
			$owlPDO->commit();			
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}	
	break;

	case 'koreksiTransaksi' :

		$notransaksi = $param['notransaksi'];

		$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' order by tanggalspb asc, nokendaraan asc";
		$res = fetchdata($str);

		if (empty($res)) {
				echo "Data tidak ditemukan.";
				exit;
		}

		// Koreksi cuma boleh untuk transaksi yang sudah posting.
		if ($res[0]['posting'] != 1) {
				echo "Warning:Transaksi ini belum di-posting, koreksi belum bisa dilakukan.";
				exit;
		}

		$unit         = $res[0]['unit'];
		$kodecustomer = $res[0]['kodecustomer'];
		$kodeorg 			= $res[0]['kodero'];

		// Satu notransaksi cuma boleh diajukan koreksi SEKALI seumur hidup - begitu pernah diajukan
		// (revstatus != 0, apapun hasilnya: masih pending/disetujui/ditolak), kunci SELURUH transaksi
		// ini, jangan tampilkan tabel edit lagi. Tampilan yang muncul beda-beda sesuai statusnya.
		// Kecuali revstatus=3 (Perlu Revisi - approver minta diperbaiki) : tabel edit dibuka LAGI,
		// pre-fill dari usulan sebelumnya, supaya pengaju bisa perbaiki & ajukan ulang.
		$strSudahAjukan = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' and revstatus != 0 order by tanggalspb asc, nokendaraan asc";
		$resSudahAjukan = fetchdata($strSudahAjukan);
		$statusRevAwal  = !empty($resSudahAjukan) ? (int)$resSudahAjukan[0]['revstatus'] : 0;
		$perluRevisi    = ($statusRevAwal == 3);

		// Data usulan sebelumnya (per truck+tanggal) buat pre-fill tabel edit kalau statusnya Perlu Revisi.
		$prevRevisi = [];
		$namaPemintaRevisi = '-';
		$komentarRevisi    = '';
		if ($perluRevisi) {
				foreach ($resSudahAjukan as $bar) {
						$keyPrev = $bar['tanggalspb'].'|'.$bar['nokendaraan'];
						if (!isset($prevRevisi[$keyPrev])) {
								$prevRevisi[$keyPrev] = [
										'tahuntanam' => $bar['revtahuntanam'],
										'keterangan' => $bar['revketerangan'],
								];
						}
				}

				$strMintaRevisi = "select b.namakaryawan, a.komentar from ".$dbname.".approval a
						left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
						where a.notransaksi = '".$notransaksi."' and a.jenispersetujuan = 'KBPT' and a.status = '3'
						order by a.tanggal desc limit 1";
				$resMintaRevisi = fetchdata($strMintaRevisi);
				if (!empty($resMintaRevisi)) {
						$namaPemintaRevisi = $resMintaRevisi[0]['namakaryawan'];
						$komentarRevisi    = $resMintaRevisi[0]['komentar'];
				}
		}

		if (!empty($resSudahAjukan) && !$perluRevisi) {
				$statusRev = $statusRevAwal;

				$strApproverPending = "select b.namakaryawan from ".$dbname.".approval a
						left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
						where a.notransaksi = '".$notransaksi."' and a.jenispersetujuan = 'KBPT'
						order by a.level desc limit 1";
				$resApproverPending  = fetchdata($strApproverPending);
				$namaApproverPending = isset($resApproverPending[0]['namakaryawan']) ? $resApproverPending[0]['namakaryawan'] : '-';

				// Kelompokkan per truck+tanggal, dari baris yang pernah diajukan koreksi.
				// Kalau sudah disetujui (revstatus=1), kolom asli (tahuntanam/rpkg/totalrp) sudah
				// ketiban nilai baru dan revtahuntanam/revtotalrp jadi sama saja dengan itu - nilai
				// "sebelum koreksi" yang asli disimpan di oldtahuntanam/oldrpkg (diisi otomatis pas
				// approval final). Total lama dihitung ulang oldrpkg*kgnetto (gak disimpan terpisah).
				// Makanya arah lama/baru dibalik kalau statusnya 1.
				$groupsAjukan = [];
				$orderAjukan  = [];
				foreach ($resSudahAjukan as $bar) {
						$key = $bar['tanggalspb'].'|'.$bar['nokendaraan'];

						if ($statusRev == 1) {
								$tahunLamaBar = $bar['oldtahuntanam'];
								$totalLamaBar = (float)$bar['oldrpkg'] * (float)$bar['kgnetto'];
								$tahunBaruBar = $bar['tahuntanam'];
								$totalBaruBar = (float)$bar['totalrp'];
						} else {
								$tahunLamaBar = $bar['tahuntanam'];
								$totalLamaBar = (float)$bar['totalrp'];
								$tahunBaruBar = $bar['revtahuntanam'];
								$totalBaruBar = (float)$bar['revtotalrp'];
						}

						if (!isset($groupsAjukan[$key])) {
								$groupsAjukan[$key] = [
										'tanggalspb'     => $bar['tanggalspb'],
										'nokendaraan'    => $bar['nokendaraan'],
										'tahuntanamlama' => [],
										'tahuntanambaru' => $tahunBaruBar,
										'totallama'      => 0,
										'totalbaru'      => 0,
										'kgnetto'        => 0,
										'keterangan'     => $bar['revketerangan'],
								];
								$orderAjukan[] = $key;
						}
						$groupsAjukan[$key]['tahuntanamlama'][$tahunLamaBar] = $tahunLamaBar;
						$groupsAjukan[$key]['totallama'] += $totalLamaBar;
						$groupsAjukan[$key]['totalbaru'] += $totalBaruBar;
						$groupsAjukan[$key]['kgnetto']   += (float)$bar['kgnetto'];
				}

				switch ($statusRev) {
						case 9:
								$judul  = "Pengajuan Koreksi Sedang Menunggu Persetujuan";
								$catatan = "Menunggu persetujuan dari: <b>".$namaApproverPending."</b>. Transaksi ini sudah pernah diajukan koreksi, tidak bisa diajukan lagi.";
								$labelTotal = "Total Kalau Disetujui";
								break;
						case 1:
								$judul  = "Koreksi Sudah Disetujui - Hasil Akhir";
								$catatan = "Disetujui oleh: <b>".$namaApproverPending."</b>.";
								$labelTotal = "Total Hasil Akhir";
								break;
						case 2:
								$judul  = "Pengajuan Koreksi Ditolak";
								$catatan = "Ditolak oleh: <b>".$namaApproverPending."</b>. Transaksi ini sudah pernah diajukan koreksi, tidak bisa diajukan lagi.";
								$labelTotal = "Total Yang Diajukan (Ditolak)";
								break;
						default:
								$judul  = "Pengajuan Koreksi Sebelumnya";
								$catatan = "Transaksi ini sudah pernah diajukan koreksi (status: ".$statusRev."), tidak bisa diajukan lagi.";
								$labelTotal = "Total";
								break;
				}

				$stream = "<div style='padding:10px;'>";
				$stream .= "<h3 style='color:#0000cc;margin-top:0;'>".$judul."</h3>";
				$stream .= "<p>".$catatan."</p>";

				$stream .= "<table cellpadding=5 cellspacing=1 border=1 class=sortable width=100%>
						<thead><tr class=rowheader style='text-align:center;font-weight:bold;'>
						<th>".$_SESSION['lang']['tanggal']."</th>
						<th>".$_SESSION['lang']['kodevhc']."</th>
						<th>".$_SESSION['lang']['tahuntanam']."</th>
						<th>Harga/Kg</th>
						<th>Total</th>
						<th>Keterangan Revisi</th>
						</tr></thead><tbody>";

				$totalLamaAll = $totalBaruAll = 0;
				foreach ($orderAjukan as $key) {
						$g = $groupsAjukan[$key];
						$ttLama        = implode(', ', array_values($g['tahuntanamlama']));
						$hargaLamaRata = $g['kgnetto'] > 0 ? ($g['totallama'] / $g['kgnetto']) : 0;
						$hargaBaruRata = $g['kgnetto'] > 0 ? ($g['totalbaru'] / $g['kgnetto']) : 0;
						$totalLamaAll += $g['totallama'];
						$totalBaruAll += $g['totalbaru'];

						$stream .= "<tr class=rowcontent>";
						$stream .= "<td align=center>".tanggalnormal($g['tanggalspb'])."</td>";
						$stream .= "<td align=center>".$g['nokendaraan']."</td>";
						$stream .= "<td align=center>".$ttLama." &rarr; <b>".$g['tahuntanambaru']."</b></td>";
						$stream .= "<td align=right>".number_format($hargaLamaRata, 2)." &rarr; <b>".number_format($hargaBaruRata, 2)."</b></td>";
						$stream .= "<td align=right>".number_format($g['totallama'], 2)." &rarr; <b>".number_format($g['totalbaru'], 2)."</b></td>";
						$stream .= "<td>".htmlspecialchars($g['keterangan'])."</td>";
						$stream .= "</tr>";
				}

				// Total Sebelum/Sesudah di sini itu total penjualan SATU BA/notransaksi ini
				// seluruhnya (bukan cuma baris/truck yang dikoreksi), biar keliatan dampak
				// koreksinya ke total BA. Kalau sudah disetujui (revstatus=1), kolom asli sudah
				// ketiban nilai baru, jadi "sebelum" direkonstruksi dari oldrpkg*kgnetto.
				if ($statusRev == 1) {
						$strTotalBA = "select
								sum(case when revstatus='1' then oldrpkg*kgnetto else totalrp end) as totalsebelum,
								sum(totalrp) as totalsesudah
								from ".$dbname.".".$table." where notransaksi='".$notransaksi."'";
				} else {
						$strTotalBA = "select
								sum(totalrp) as totalsebelum,
								sum(case when revstatus='".$statusRev."' then revtotalrp else totalrp end) as totalsesudah
								from ".$dbname.".".$table." where notransaksi='".$notransaksi."'";
				}
				$resTotalBA = fetchdata($strTotalBA);
				$totalSebelumBA = isset($resTotalBA[0]['totalsebelum']) ? (float)$resTotalBA[0]['totalsebelum'] : 0;
				$totalSesudahBA = isset($resTotalBA[0]['totalsesudah']) ? (float)$resTotalBA[0]['totalsesudah'] : 0;

				$stream .= "<tr class=rowheader style='font-weight:bold;background-color:#FFD966;'>";
				$stream .= "<td align=center colspan=4>Total Penjualan Sebelum Perubahan</td>";
				$stream .= "<td align=right>".number_format($totalSebelumBA, 2)."</td>";
				$stream .= "<td></td>";
				$stream .= "</tr>";
				$stream .= "<tr class=rowheader style='font-weight:bold;background-color:#FFD966;'>";
				$stream .= "<td align=center colspan=4>Total Penjualan Sesudah Perubahan (".$labelTotal.")</td>";
				$stream .= "<td align=right>".number_format($totalSesudahBA, 2)."</td>";
				$stream .= "<td></td>";
				$stream .= "</tr>";
				$stream .= "</tbody></table></div>";

				echo $stream;
			break;
		}

		// Daftar approver level 1 dari setup_approval jenispersetujuan='KBPT' untuk unit ini
		// di sini cuma menampilkan siapa saja approver level pertama untuk dipilih).
		$kodeapprovalKoreksi = 'KBPT';
		$strApprover = "select distinct a.karyawanid, b.namakaryawan from ".$dbname.".setup_approval a
				left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid and a.level = '1'
				where a.jenispersetujuan = '".$kodeapprovalKoreksi."' and a.kodeunit = '".$kodeorg."'
				order by b.namakaryawan asc";
		$resApprover = fetchdata($strApprover);
		$approverJs  = [];
		foreach ($resApprover as $a) {
				$approverJs[] = ['id' => $a['karyawanid'], 'nama' => $a['namakaryawan']];
		}

		// Ambil semua harga TBS yang berlaku untuk unit & customer ini,
		// dipakai untuk simulasi "kalau tahun tanam dikoreksi jadi X, harga & totalnya jadi berapa".
		$strHarga = "select tahuntanam, tanggal, tanggal2, harga from ".$dbname.".pmn_hargajualtbs where kodeorg='".$unit."' and kodecustomer='".$kodecustomer."' and posting=1 order by tahuntanam asc, tanggal desc";
		$resHarga = fetchdata($strHarga);

		$listTahunTanam = [];
		$hargaMasterJs  = [];
		foreach ($resHarga as $h) {
				$listTahunTanam[$h['tahuntanam']] = $h['tahuntanam'];
				$hargaMasterJs[] = [
						'tt'    => $h['tahuntanam'],
						'awal'  => $h['tanggal'],
						'akhir' => $h['tanggal2'],
						'harga' => (float)$h['harga'],
				];
		}
		// Pastikan tahun tanam yang sudah ada di transaksi tetap muncul di pilihan,
		// meski kebetulan tidak ada harga posting=1 untuk tahun tanam tsb.
		foreach ($res as $bar) {
				if (!empty($bar['tahuntanam'])) {
						$listTahunTanam[$bar['tahuntanam']] = $bar['tahuntanam'];
				}
		}
		ksort($listTahunTanam);

		// Kelompokkan baris per truck (nokendaraan) + tanggal SPB. Satu truck/tanggal biasanya
		// cuma 1 tahun tanam, tapi kalau ternyata truck yang sama di tanggal yang sama mengangkut
		// dari lebih dari 1 blok/tahun tanam berbeda, itu ditandai "Campur" (bukan langsung digabung diam-diam).
		// Catatan: kalau ada baris manapun yang revstatus=9/1/2, kode di atas sudah `break` duluan
		// (lihat blok cek pending), jadi titik ini cuma tercapai kalau memang belum ada pengajuan
		// aktif (revstatus=0) ATAU statusnya Perlu Revisi (revstatus=3, $perluRevisi=true).
		$groups = [];
		$groupOrder = [];
		foreach ($res as $bar) {
				$key = $bar['tanggalspb'].'|'.$bar['nokendaraan'];
				if (!isset($groups[$key])) {
						$groups[$key] = [
								'tanggalspb'  => $bar['tanggalspb'],
								'nokendaraan' => $bar['nokendaraan'],
								'tanggalpks'  => $bar['tanggalpks'],
								'tahuntanam'  => [],
								'kgnetto'     => 0,
								'totalrp'     => 0,
						];
						$groupOrder[] = $key;
				}
				$groups[$key]['tahuntanam'][$bar['tahuntanam']] = $bar['tahuntanam'];
				$groups[$key]['kgnetto'] += (float)$bar['kgnetto'];
				$groups[$key]['totalrp'] += (float)$bar['totalrp'];
		}

		// Data harga & daftar approver dikirim lewat elemen JSON (bukan <script> biasa) karena isi popup ini
		$stream = "<script type='application/json' id='hargaMasterKoreksiData'>".json_encode($hargaMasterJs)."</script>";
		$stream .= "<script type='application/json' id='approverListData'>".json_encode($approverJs)."</script>";

		if ($perluRevisi) {
				$stream .= "<div style='padding:10px;background-color:#FFF3CD;border:1px solid #ffc107;margin-bottom:10px;'>
						<b>Pengajuan koreksi sebelumnya diminta untuk direvisi oleh: ".htmlspecialchars($namaPemintaRevisi)."</b><br>
						Alasan: ".htmlspecialchars($komentarRevisi)."<br>
						Silakan perbaiki tahun tanam/keterangan di bawah lalu ajukan ulang (akan diproses mulai dari level 1 lagi).
					</div>";
		}

		$stream .= "<div class='table-scroll' style='max-height:600px;overflow-y:auto;overflow-x:auto;border:1px solid #ccc;'>";
		$stream .= "<table cellpadding=5 cellspacing=1 border=1 class=sortable width=100%>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold;'>
				<th rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2>".$_SESSION['lang']['tanggal']."</th>
				<th rowspan=2>".$_SESSION['lang']['kodevhc']."</th>
				<th rowspan=2>".$_SESSION['lang']['netto']."</th>
				<th colspan=3>Sebelum Koreksi</th>
				<th colspan=3>Setelah Koreksi</th>
				<th rowspan=2>Keterangan Revisi</th>
				<th rowspan=2>Info</th>
			</tr>
			<tr class=rowheader style='text-align:center;font-weight:bold;'>
				<th>".$_SESSION['lang']['tahuntanam']."</th>
				<th>Harga/Kg</th>
				<th>Total</th>
				<th>".$_SESSION['lang']['tahuntanam']."</th>
				<th>Harga/Kg</th>
				<th>Total</th>
			</tr>
			</thead><tbody>";

		$no = 0;
		$lastTgl = null;
		$totalLamaAll = 0;
		$totalBaruAll = 0;
		foreach ($groupOrder as $key) {
				$g = $groups[$key];
				$tglSPB = $g['tanggalspb'];

				if ($lastTgl !== null && $lastTgl != $tglSPB) {
						$stream .= "<tr><td colspan=12 style='background-color:#E2F0D9;'>&nbsp;</td></tr>";
				}

				$no++;
				$kgNetto   = $g['kgnetto'];
				$totalLama = $g['totalrp'];
				$totalLamaAll += $totalLama;
				$hargaLamaRata = $kgNetto > 0 ? ($totalLama / $kgNetto) : 0;

				$ttList  = array_values($g['tahuntanam']);
				$isMixed = count($ttList) > 1;
				$ttLamaLabel = $isMixed ? 'Campur ('.implode(', ', $ttList).')' : ($ttList[0] ?? '-');

				// Kalau statusnya Perlu Revisi, pre-select tahun tanam dari usulan sebelumnya
				// (bukan dari tahun tanam lama), supaya pengaju tinggal koreksi yang perlu diperbaiki saja.
				$ketRevisiVal      = '';
				$hargaBaruPrefill  = null;
				$totalBaruPrefill  = null;
				if ($perluRevisi && isset($prevRevisi[$key])) {
						$defaultTT    = $prevRevisi[$key]['tahuntanam'];
						$ketRevisiVal = $prevRevisi[$key]['keterangan'];

						// Hitung ulang harga & total di server (bukan andalkan onchange di browser),
						// karena popup ini di-inject lewat innerHTML - <script> di dalamnya gak jalan.
						foreach ($hargaMasterJs as $h) {
								if ($h['tt'] == $defaultTT && $g['tanggalpks'] >= $h['awal'] && $g['tanggalpks'] <= $h['akhir']) {
										$hargaBaruPrefill = $h['harga'];
										break;
								}
						}
						$totalBaruPrefill = ($hargaBaruPrefill !== null) ? $hargaBaruPrefill * $kgNetto : 0;
						if ($hargaBaruPrefill === null) {
								$hargaBaruPrefill = 0;
						}
				} else {
						$defaultTT = !$isMixed ? $ttList[0] : null;
				}

				$optTahunTanam = "<option value=''>-- Pilih --</option>";
				foreach ($listTahunTanam as $tt) {
						$sel = ($defaultTT !== null && $tt == $defaultTT) ? " selected" : "";
						$optTahunTanam .= "<option value='".$tt."'".$sel.">".$tt."</option>";
				}

				$stream .= "<tr class=rowcontent id=rowkoreksi".$no." data-mixed='".($isMixed ? 1 : 0)."'>";
				$stream .= "<td align=center>".$no."</td>";
				$stream .= "<td align=center id=kortanggal".$no.">".tanggalnormal($tglSPB)."</td>";
				$stream .= "<td align=center id=kornokendaraan".$no.">".$g['nokendaraan']."</td>";
				$stream .= "<td align=right id=kornetto".$no." data-netto='".$kgNetto."' data-tglpks='".$g['tanggalpks']."'>".number_format($kgNetto)."</td>";
				$stream .= "<td align=center id=kortahuntanamlama".$no.($isMixed ? " style='color:red;font-weight:bold;'" : "").">".$ttLamaLabel."</td>";
				$stream .= "<td align=right id=korhargalama".$no.">".number_format($hargaLamaRata, 2)."</td>";
				$stream .= "<td align=right id=kortotallama".$no.">".number_format($totalLama, 2)."</td>";
				$stream .= "<td align=center>
					<select id=kortahuntanam".$no." class=myinputtext style='width:90px;' onchange=\"hitungKoreksi(".$no.")\">".$optTahunTanam."</select>
				</td>";
				if ($hargaBaruPrefill !== null) {
						$hargaBaruCell = number_format($hargaBaruPrefill, 2);
						$totalBaruCell = number_format($totalBaruPrefill, 2);
						$validCell     = $hargaBaruPrefill > 0 ? '1' : '0';
						$colorCell     = $hargaBaruPrefill > 0 ? '' : 'red';
				} else {
						$hargaBaruCell = $isMixed ? '-' : number_format($hargaLamaRata, 2);
						$totalBaruCell = number_format($totalLama, 2);
						$validCell     = '1';
						$colorCell     = '';
				}
				$totalBaruAll += ($hargaBaruPrefill !== null) ? $totalBaruPrefill : $totalLama;
				$stream .= "<td align=right id=korhargabaru".$no.">".$hargaBaruCell."</td>";
				$stream .= "<td align=right id=kortotalbaru".$no." data-totallama='".number_format($totalLama, 2)."' data-valid='".$validCell."'".($colorCell != '' ? " style='color:$colorCell;'" : "").">".$totalBaruCell."</td>";
				$stream .= "<td align=center>
					<input type=text id=korketrevisi".$no." class=myinputtext style='width:150px;' maxlength=40 placeholder='Alasan koreksi...' value='".htmlspecialchars($ketRevisiVal)."'>
				</td>";
				$stream .= "<td align=center id=korket".$no."></td>";
				$stream .= "</tr>";

				$lastTgl = $tglSPB;
		}

		$stream .= "<tr class=rowheader style='font-weight:bold;'>";
		$stream .= "<td align=center colspan=6>".$_SESSION['lang']['total']."</td>";
		$stream .= "<td align=right>".number_format($totalLamaAll, 2)."</td>";
		$stream .= "<td></td><td></td>";
		$stream .= "<td align=right id=korgrandtotalbaru>".number_format($totalBaruAll, 2)."</td>";
		$stream .= "<td></td><td></td>";
		$stream .= "</tr>";

		$stream .= "</tbody></table></div>";
		// Update tahuntanam/harga/total/keterangan baru benar-benar terjadi setelah approval terakhir,
		$stream .= "<div align=center style='margin-top:10px;'>
			<button id=ajukanpersetujuan class=mybutton onclick=\"ajukanPersetujuanKoreksi('".$notransaksi."',".$no.")\">Ajukan Persetujuan</button>
		</div>";

		echo $stream;
	break;

	case 'insertApprovalKoreksi' :
		
		// Langsung update data + catat approval-nya jadi satu (sesuai arahan: gak pakai tabel
		$notransaksi  = $param['notransaksi'];
		$approverid   = $param['approverid'];
		$dataKoreksi  = json_decode($param['dataKoreksi'], true);

		if (empty($dataKoreksi)) {
				echo "Warning:Tidak ada data koreksi yang dikirim";
				exit;
		}

		if ($approverid == '') {
				echo "Warning:Pilih dulu siapa yang akan approve";
				exit;
		}

		try {
				$owlPDO->beginTransaction();

				$adaPerubahan = false;
				$sudahPending = 0;

				foreach ($dataKoreksi as $d) {
						$tglSPB      = tanggalsystemn($d['tanggal']);
						$nokendaraan = $d['nokendaraan'];
						$tahunBaru   = $d['tahuntanamBaru'];

						// Ambil referensi unit/customer/tanggal PKS + status koreksi terkini dari grup truck+tanggal ini
						$strRef = "select unit, kodecustomer, tanggalpks, revstatus from ".$dbname.".".$table."
								where notransaksi='".$notransaksi."' and nokendaraan='".$nokendaraan."' and tanggalspb='".$tglSPB."' limit 1";
						$resRef = fetchdata($strRef);
						if (empty($resRef)) {
								continue;
						}
						$ref = $resRef[0];

						// Jaga-jaga race condition: baris ini sudah keburu diajukan (revstatus=9) sebelum
						// request ini diproses - jangan ditimpa, jangan bisa diajukan berkali-kali.
						if ((int)$ref['revstatus'] === 9) {
								$sudahPending++;
								continue;
						}

						// Cari ulang harga tahun tanam baru di server (jangan percaya mentah-mentah nilai dari client)
						$strHarga = "select harga from ".$dbname.".pmn_hargajualtbs
								where kodeorg='".$ref['unit']."' and kodecustomer='".$ref['kodecustomer']."'
								and tahuntanam='".$tahunBaru."' and posting=1
								and tanggal<='".$ref['tanggalpks']."' and tanggal2>='".$ref['tanggalpks']."'
								order by tanggal desc limit 1";
						$resHarga  = fetchdata($strHarga);
						$hargaBaru = isset($resHarga[0]['harga']) ? (float)$resHarga[0]['harga'] : 0;

						// totalrp dihitung ulang PER BARIS pakai kgnetto masing-masing (bukan angka total gabungan
						// dari popup), supaya kalau 1 truck/tanggal ini sebenarnya lebih dari 1 baris (beda blok),
						// totalnya tidak dobel/ke-copy sama rata ke semua baris. kgnetto dibulatkan ke kg genap
						// dulu sebelum dikali harga, samain sama rekap Excel manual & transaksi baru.
						$str = "update ".$dbname.".".$table." set
								revtahuntanam ='".$tahunBaru."',
								revrpkg =".$hargaBaru.",
								revtotalrp = round(kgnetto) * ".$hargaBaru.",
								revketerangan = '".addslashes($d['keteranganRevisi'])."',
								revstatus = '9',
								revcreateby ='".$_SESSION['standard']['userid']."',
								revcreatetime ='".date('Y-m-d H:i:s')."'
								where notransaksi='".$notransaksi."' and nokendaraan='".$nokendaraan."' and tanggalspb='".$tglSPB."'";
						$owlPDO->exec($str);

						$adaPerubahan = true;
				}

				if (!$adaPerubahan) {
						$owlPDO->rollback();
						if ($sudahPending > 0) {
								echo "Warning:Semua baris yang diajukan sudah dalam status menunggu approval sebelumnya, tidak bisa diajukan berkali-kali.";
						} else {
								echo "Warning:Tidak ada baris yang berhasil diajukan.";
						}
						exit;
				}

				// Catat pengajuan approval-nya (sekali per pengajuan) - alasan koreksi disimpan per
				// baris di revketerangan, bukan di keteranganht kebun_tbsjual (itu keterangan header transaksi).
				$str = "insert into ".$dbname.".approval
						(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
						values (
								'".$notransaksi."',
								'KBPT',
								'1',
								'".$approverid."',
								'0',
								'',
								'',
								'".date('Y-m-d H:i:s')."'
						)";
				$owlPDO->exec($str);

				$owlPDO->commit();
				if ($sudahPending > 0) {
						echo "OK - ".$sudahPending." baris dilewati karena sudah menunggu approval sebelumnya.";
				} else {
						echo "OK";
				}
		} catch (PDOException $e) {
				$owlPDO->rollback();
				echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;

	default:
	break;
	
}

?>
