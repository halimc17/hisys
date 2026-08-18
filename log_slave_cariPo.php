<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
if (isTransactionPeriod()) //check if transaction period is normal
{
	$nopo = $_POST['nopo'];
	$nosj = $_POST['nosj'];

	##Get PT
	// $optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".substr($_POST['gudang'],0,4)."'");
	// $gudang = $optPt[substr($_POST['gudang'],0,4)];
	$gudang = substr($_POST['gudang'], 0, 4);


	#= cari tipe unit
	$str = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $gudang . "'";
	$res = fetchdata($str);
	foreach ($res as $bar) {
		$tipeunit = $bar['tipe'];
		$kodept = $bar['induk'];
	}




	#= franco
	$str = "select * from " . $dbname . ".setup_franco";
	$res = fetchdata($str);
	foreach ($res as $bar) {
		$namafranco[$bar['id_franco']] = $bar['franco_name'];
		$unitfranco[$bar['id_franco']] = $bar['kodeunit'];
	}

	// echo $tipeunit._.$kodept;

	echo "<table cellspacing=1 border=0 class=sortable width=100% cellpadding=5>
        <thead>
		<tr class=rowheader><th align=center>No</th>
			<th align=center>" . $_SESSION['lang']['nopo'] . "</th>
			<th align=center>" . $_SESSION['lang']['tanggal'] . " PO/SO</th>
			<th align=center>" . $_SESSION['lang']['tanggal'] . "" . $_SESSION['lang']['nosj'] . "</th>
			<th align=center>" . $_SESSION['lang']['purchaser'] . "</th>
			<th align=center>" . $_SESSION['lang']['franco'] . "</th>
		</tr>
		</thead>
		</tbody>";

	//   if($tipeunit=='KANWIL'){
	// 	 $str="select * from ".$dbname.".log_poht where nopo like '%".$nopo."%' and kodeorg='".$kodept."' and stat_release='1' and (closed='0' or (closed='1' and keteranganclose like '%No. Ref PR/SR :%')) and tipepo in ('PO') order by tanggal desc,nopo desc";  
	//   } else {
	// 	   $str="select * from ".$dbname.".log_poht where nopo like '%".$nopo."%' and kodeunit like '%".$gudang."%' and stat_release='1' and (closed='0' or (closed='1' and keteranganclose like '%No. Ref PR/SR :%')) and tipepo in ('PO') order by tanggal desc,nopo desc"; 
	//   }

	if ($nosj != '') {
		$str = "select a.*,b.tanggalkirim as tanggalsj from " . $dbname . ".log_poht a left join " . $dbname . ".log_suratjalan_vw b on a.nopo=b.nopo where a.nopo like '%" . $nopo . "%' and b.franco like '%" . $gudang . "%' and a.stat_release='1' and (a.closed='0' or (a.closed='1' and a.keteranganclose like '%No. Ref PR/SR :%')) and a.tipepo in ('PO') and b.nosj='" . $nosj . "' group by b.nopo,b.nosj order by a.tanggal desc,a.nopo desc";
	} else {
		$str = "select * from " . $dbname . ".log_po_vw where nopo like '%" . $nopo . "%' and kodeunit like '%" . $gudang . "%' and stat_release='1' and (closed='0' or (closed='1' and keteranganclose like '%No. Ref PR/SR :%')) and tipepo in ('PO') and idFranco not in (select id_franco from " . $dbname . ".setup_franco where substr(kodeunit,4,1)='O') and kodebarang != '380' group by nopo order by tanggal desc,nopo desc";
	}




	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no = 0;
	while ($bar = $res->fetch()) {
		//ambil userid purchaser
		$purchaser = '';
		if (!empty($bar->purchaser)) {
			$strv = "select namauser from " . $dbname . ".user where karyawanid=" . $bar->purchaser;
			$resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
			$resv->setFetchMode(PDO::FETCH_OBJ);
			while ($barv = $resv->fetch()) {
				$purchaser = $barv->namauser;
			}
		}

		$strx = "select jumlahpesan, jmlhstlhclose, kodebarang, satuan from " . $dbname . ".log_podt where nopo='" . $bar->nopo . "'";
		$resx = fetchdata($strx);
		$counthasil = 0;
		foreach ($resx as $valx) {
			$strxx = "select kodebarang, sum(jumlah) as jumlahterima, satuan from " . $dbname . ".log_transaksidt where nopo='" . $bar->nopo . "' and kodebarang='" . $valx['kodebarang'] . "'";
			$resxx = fetchdata($strxx);
			$jlhterima = ($resxx[0]['jumlahterima'] == '' ? 0 : $resxx[0]['jumlahterima']);

			if ($valx['satuan'] != $resxx[0]['satuan']) { // Satuan PO tidak sama dengan Transaksi?
				//konversi satuan jika satuan default kodebarang tidak sama dengan satuan po
				$str1 = "select jumlah from " . $dbname . ".log_5stkonversi 
                 where darisatuan='" . $resxx[0]['satuan'] . "' and satuankonversi='" . $valx['satuan'] . "'
                 and kodebarang='" . $resxx[0]['kodebarang'] . "'";
				$res3 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
				$res3->setFetchMode(PDO::FETCH_OBJ);
				while ($bar2 = $res3->fetch()) {
					$valx['jumlahpesan'] = round(($valx['jumlahpesan'] / $bar2->jumlah), 6); //mengkonversi satuan
				}
			}
			$hasil = $valx['jumlahpesan'] - $jlhterima - $valx['jmlhstlhclose'];
			if ($hasil > 0) {
				$counthasil++;
			}
		}



		$flag = 0;
		$notransit = '';
		// cek syarat byar CBD
		if ($bar->syaratbayar == 'CBD') {
			$strbyr = "select sum(jumlah) as jmlbyr from " . $dbname . ".keu_kasbankdtht_vw where nodok='" . $bar->nopo . "'";
			$resbyr = fetchdata($strbyr);
			foreach ($resbyr as $valbyr) {
				$jmbyr = $valbyr['jmlbyr'];
			}

			if ($jmbyr > 0) {
				$flag = 0;
			} else {
				$flag = 2;
			}
		} else {
			// cek frangko po
			$orgfrangko = makeOption($dbname, 'setup_franco', 'id_franco,kodeunit', "id_franco='" . $bar->idFranco . "'");
			if (substr($orgfrangko[$bar->idFranco], 3, 1) == 'O') {

				$strtransit = "select nosj as notransaksi from " . $dbname . ".log_transit a left join log_suratjalan_vw b on a.nopo=b.nopo where b.nopo='" . $bar->nopo . "' and b.posting='1'";
				$restransit = fetchdata($strtransit);
				foreach ($restransit as $valtransit) {
					$notransit = $valtransit['notransaksi'];
				}
				//echo $bar->nopo.'cccc'.$flag.'xxx'.$notransit.'<br>';

				if ($notransit == '') {
					$flag = 3;
				} else {
					$flag = 0;
				}
			} else {
				$flag = 0;
			}

			// cek pembayar DP
			// $strdp="select sum(rupiah) as rpdp from ".$dbname.".log_potermin where nopo='".$bar->nopo."' and tipe='0'";
			// $resdp=fetchdata($strdp);
			// foreach($resdp as $valdp){
			// 	$rpdp=$valdp['rpdp'];
			// }

			// if ($rpdp > 0) {
			// 	$strbyr="select sum(jumlah) as jmlbyr from ".$dbname.".keu_kasbankdtht_vw where nodok='".$bar->nopo."'";
			// 	$resbyr=fetchdata($strbyr);
			// 	foreach($resbyr as $valbyr){
			// 		$jmbyr=$valbyr['jmlbyr'];
			// 	}

			// 	if ($jmbyr >= $rpdp) {
			// 		$flag=0;
			// 	}else{
			// 		$flag=1;
			// 	}
			// }


		}

		if ($counthasil > 0) {
			$no += 1;
			$arrdata = explode(',', $unitfranco[$bar->idFranco]);
			foreach ($arrdata as $key) {
				$arrunit[$key] = $key;
			}
			$bgcolor = '';
			if ($flag == 0) {
				echo "<tr class=rowcontent  style='cursor:pointer;' title='Klik untuk menarik data PO' onclick=goPickPo('" . $bar->nopo . "')>";
			}
			// if(in_array($gudang,$arrunit)){
			// 	echo"<tr class=rowcontent  style='cursor:pointer;' title='Klik untuk menarik data PO' onclick=goPickPo('".$bar->nopo."')>";

			// }else{
			// 	echo"<tr class=rowcontent  title='Data tidak dapat ditarik dikarenakan franco hanya untuk unit ".$unitfranco[$bar->idFranco]." ' >";
			// 	$bgcolor="bgcolor=red";
			// }
			if ($flag == 0) {
				echo "<td align=center " . $bgcolor . ">" . $no . "</td>";
				echo "<td align=left " . $bgcolor . ">" . $bar->nopo . "</td>
			<td " . $bgcolor . ">" . tanggalnormal($bar->tanggal) . "</td>
			<td " . $bgcolor . ">" . tanggalnormal($bar->tanggalsj) . "</td>
			<td " . $bgcolor . ">" . $purchaser . "</td>
			<td " . $bgcolor . ">" . $namafranco[$bar->idFranco] . "</td>
			<td hidden>" . $unitfranco[$bar->idFranco] . "</td>
			<td hidden>" . $gudang . "</td>
		</tr>
		";
			}
			$arrunit = array();
		}
	}

	echo "</tbody>
	     <tfoot>
		 </tfoot>
		 </table>";
} else {
	echo " Error: Transaction Period missing";
}
