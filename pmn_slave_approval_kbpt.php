<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$session_id  = $_SESSION['standard']['userid'];
$karyawanid  = checkPostGet('karyawanid', $session_id);
$method      = checkPostGet('method', '');
$proses      = checkPostGet('proses', '');
$level       = checkPostGet('level', '');
$notransaksi = checkPostGet('notransaksi', '');
$kolom       = checkPostGet('kolom', '');
$comment     = checkPostGet('comment', '');
$userid      = checkPostGet('userid', '');
$tglskrng    = date("Y-m-d H:i:s");

$jenisApp = 'KBPT';
$table    = 'kebun_tbsjual';

switch ($method) {
case 'getdetail':
	case 'KBPT':

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
				<thead>
				<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td align=center>".$_SESSION['lang']['unit']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']." Diajukan</td>
					<td align=center>Total Sebelum</td>
					<td align=center>Total Sesudah</td>
					<td align=center>Keterangan Revisi</td>
					<td align=center>Detail</td>
					<td align=center colspan=3>Action</td>
				</tr>
				</thead>
				<tbody>";

		$str = "select * from ".$dbname.".approval where jenispersetujuan='".$jenisApp."' and status='0' and karyawanid='".$karyawanid."' order by tanggal asc";
		$res = fetchdata($str);

		$no = 0;
		foreach ($res as $bar) {
			$notransaksix = $bar['notransaksi'];
			$levelnow     = $bar['level'];

			// Ambil baris yang benar-benar masih menunggu approval (revstatus=9) untuk notransaksi ini.
			$strDt = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksix."' and revstatus='9' order by tanggalspb asc, nokendaraan asc";
			$resDt = fetchdata($strDt);
			if (empty($resDt)) {
				// Sudah tidak ada baris pending (kemungkinan sudah diproses sebelumnya), lewati.
				continue;
			}

			$unit    = $resDt[0]['unit'];
			$kodeorg = $resDt[0]['kodero'];

			$keteranganRev = '';
			foreach ($resDt as $d) {
				if ($keteranganRev == '') {
					$keteranganRev = $d['revketerangan'];
				}
			}

			// Total Sebelum/Sesudah di sini itu total PENJUALAN SATU BA/notransaksi INI SELURUHNYA
			// (bukan cuma baris yang dikoreksi) - biar keliatan dampak koreksinya ke total BA.
			$strTotalBA = "select sum(totalrp) as totalsebelum,
					sum(case when revstatus='9' then revtotalrp else totalrp end) as totalsesudah
					from ".$dbname.".".$table." where notransaksi='".$notransaksix."'";
			$resTotalBA = fetchdata($strTotalBA);
			$totalLama = isset($resTotalBA[0]['totalsebelum']) ? (float)$resTotalBA[0]['totalsebelum'] : 0;
			$totalBaru = isset($resTotalBA[0]['totalsesudah']) ? (float)$resTotalBA[0]['totalsesudah'] : 0;

			$no++;
			$tab.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=left>".$notransaksix."</td>
					<td align=left>".$unit."</td>
					<td align=left>".tanggalnormal($bar['tanggal'])."</td>
					<td align=right>".number_format($totalLama, 2)."</td>
					<td align=right>".number_format($totalBaru, 2)."</td>
					<td align=left>".htmlspecialchars($keteranganRev)."</td>
					<td align=center>
						<img src=images/zoom.png class=resicon height='25' title='Lihat Detail' onclick=\"detailKoreksiKBPT('".$notransaksix."');\">
					</td>
					<td style='text-align:center'>
						<button class=mybutton onclick=\"getdataKBPT('".$notransaksix."','".$levelnow."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
						<button class=mybutton onclick=\"revisiKBPT('".$notransaksix."','".$levelnow."')\">Revisi</button>
					</td>
					<td style='text-align:center'>
						<button class=mybutton onclick=\"tolakKBPT('".$notransaksix."','".$levelnow."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>
				</tr>";
		}

		$tab.="</tbody>
				<tfoot></tfoot>
				</table>
			</fieldset>";

		// Tidak di-echo di sini: file ini di-include dari log_slave_approval.php lewat
		// case 'KBPT', yang berbagi scope $tab dan sudah echo $tab sendiri di akhir
		// (setelah switch($proses) selesai). Kalau di-echo di sini juga, hasilnya dobel.
	break;
	break;

	case 'get_form_approval_KBPT':

		$strDt = "select unit, kodero from ".$dbname.".".$table." where notransaksi='".$notransaksi."' and revstatus='9' limit 1";
		$resDt = fetchdata($strDt);
		if (empty($resDt)) {
			echo "Warning:Data koreksi untuk transaksi ini sudah diproses sebelumnya (disetujui/ditolak).";
			exit;
		}
		$kodeorg = $resDt[0]['kodero'];

		$countApp = getCountApproval($jenisApp, $kodeorg);

		if ($kolom == $countApp) {
			$tab.="<div id=approve>
				<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$notransaksi."  />
				<table cellspacing=1 border=0>
					<tr>
						<td colspan=3>Approved - koreksi akan langsung diterapkan ke transaksi</td>
					</tr>
					<tr>
						<td colspan=3><hr></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['note']."</td>
						<td>:</td>
						<td>
							<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
						</td>
					</tr>
					<tr>
						<td colspan=3 align=center>
							<button id=Ajukan class=mybutton onclick=nextapprovalKBPT('approved') >Approved</button>
						</td>
					</tr>
				</table>
			</div>";
		} else {
			$level = $kolom + 1;
			$arrListApp = listApprove($level, $jenisApp, $kodeorg);

			$optKry = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			foreach ($arrListApp as $val) {
				if ($val['karyawanid'] == $karyawanid) {
					continue;
				}
				$optKry .= "<option value='".$val['karyawanid']."'>".$val['nama'].($val['lokasitugas'] != '' ? " [".$val['lokasitugas']."]" : "")."</option>";
			}

			$tab.="<div id=test style=display:block>
				<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$notransaksi."  />
				<input hidden id=kolom value=".$kolom."  />
				<table cellspacing=1 border=0>
					<tr>
						<td colspan=3>Submit to the next approval :</td>
					</tr>
					<tr>
						<td colspan=3><hr></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['namakaryawan']."</td>
						<td>:</td>
						<td valign=top>
							<select id=user_id name=user_id  style=\"width:150px;\">".$optKry."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['note']."</td>
						<td>:</td>
						<td>
							<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=nextapprovalKBPT() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
						</td>
					</tr>
				</table>
			</div>";
		}

		echo $tab;
	break;

	case 'insert_nextapprovalKBPT':
		try {
			$owlPDO->beginTransaction();

			$strDt = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' and revstatus='9'";
			$resDt = fetchdata($strDt);
			if (empty($resDt)) {
				throw new PDOException("Sudah di Approved/Ditolak");
			}
			$kodeorg = $resDt[0]['kodero'];

			$countApp  = getCountApproval($jenisApp, $kodeorg);
			$arrDetail = detailApprove($kolom, $notransaksi, $jenisApp);
			$level     = $kolom + 1;

			if ($kolom != $countApp) {
				if ($userid == '') {
					throw new PDOException("Pilih dulu siapa yang akan approve");
				}
				if ($userid == $arrDetail['karyawanid']) {
					throw new PDOException(getNamaKaryawan($userid)." Sudah di gunakan");
				}

				$str = "insert into ".$dbname.".approval (notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
						values ('".$notransaksi."','".$jenisApp."','".$level."','".$userid."','0','','','')";
				$owlPDO->exec($str);

				$strx = "update ".$dbname.".approval set status='1', komentar='".addslashes($comment)."', tanggal='".$tglskrng."'
						where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);

				$str = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and karyawanid!='".$karyawanid."' and level='".$kolom."' and status='0'";
				$owlPDO->exec($str);
			} else {
				// Level terakhir - approval selesai, terapkan koreksi ke data transaksi yang sesungguhnya.
				$strx = "update ".$dbname.".approval set status='1', komentar='".addslashes($comment)."', tanggal='".$tglskrng."'
						where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);

				$str = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and karyawanid!='".$karyawanid."' and level='".$kolom."' and status='0'";
				$owlPDO->exec($str);

				// Snapshot dulu nilai sebelum koreksi ke kolom old*, baru timpa kolom asli dengan
				// nilai baru. Urutan SET di bawah ini penting: oldtahuntanam/oldrpkg dibaca dari
				// tahuntanam/rpkg SEBELUM baris itu sendiri ditimpa di SET yang sama. Total lama
				// gak disimpan terpisah, tinggal dihitung ulang oldrpkg*kgnetto pas ditampilkan.
				$str = "update ".$dbname.".".$table." set
						oldtahuntanam = tahuntanam,
						oldrpkg = rpkg,
						tahuntanam = revtahuntanam,
						rpkg = revrpkg,
						totalrp = revtotalrp,
						revstatus = '1',
						updateby = '".$karyawanid."',
						updatetime = '".$tglskrng."'
						where notransaksi='".$notransaksi."' and revstatus='9'";
				$owlPDO->exec($str);
			}

			$owlPDO->commit();
			echo "OK";
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;

	case 'revisiKBPT':
		echo"<div id=revisi_form>
			<input hidden id=notransaksi value=".$notransaksi."  />
			<table cellspacing=1 border=0>
			<tr>
				<td colspan=3>Kirim untuk Revisi - transaksi ini akan dibuka lagi ke pengaju supaya diedit</td>
			</tr>
			<tr>
				<td colspan=3><hr></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['note']."</td>
				<td>:</td>
				<td>
					<input style=width:200px type=text id=cmnt_revisi name=cmnt_revisi class=myinputtext onClick=\"return tanpa_kutip(event)\" placeholder='Alasan minta revisi...' />
				</td>
			</tr>
			<tr>
				<td colspan=3 align=center>
					<button class=mybutton onclick=\"insertrevisiKBPT(".$kolom.")\" >Kirim untuk Revisi</button>
				</td>
			</tr>
			</table>
			</div>";
	break;

	case 'insertrevisiKBPT':
		try {
			$owlPDO->beginTransaction();

			$str = "update ".$dbname.".approval set status='3', komentar='".addslashes($comment)."', tanggal='".$tglskrng."'
					where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
			$owlPDO->exec($str);

			$str = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and karyawanid!='".$karyawanid."' and level='".$kolom."' and status='0'";
			$owlPDO->exec($str);

			// revstatus=3 = "perlu revisi" - rev* dibiarkan (jadi draft yang bisa dibuka lagi & diedit
			// sama pengaju), real tahuntanam/rpkg/totalrp tidak disentuh.
			$str = "update ".$dbname.".".$table." set revstatus='3' where notransaksi='".$notransaksi."' and revstatus='9'";
			$owlPDO->exec($str);

			$owlPDO->commit();
			echo "OK";
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;

	case 'tolakKBPT':
		echo"<div id=rejected_form>
			<input hidden id=notransaksi value=".$notransaksi."  />
			<table cellspacing=1 border=0>
			<tr>
				<td colspan=3>Rejection</td>
			</tr>
			<tr>
				<td colspan=3><hr></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['note']."</td>
				<td>:</td>
				<td>
					<input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" />
				</td>
			</tr>
			<tr>
				<td colspan=3 align=center>
					<button class=mybutton onclick=\"inserttolakKBPT(".$kolom.")\" >".$_SESSION['lang']['ditolak']."</button>
				</td>
			</tr>
			</table>
			</div>";
	break;

	case 'inserttolakKBPT':
		try {
			$owlPDO->beginTransaction();

			$str = "update ".$dbname.".approval set status='2', komentar='".addslashes($comment)."', tanggal='".$tglskrng."'
					where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
			$owlPDO->exec($str);

			$str = "delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and karyawanid!='".$karyawanid."' and level='".$kolom."' and status='0'";
			$owlPDO->exec($str);

			// Rev* dibiarkan (jadi jejak apa yang diajukan & ditolak), cuma real tahuntanam/rpkg/totalrp yang tidak disentuh.
			$str = "update ".$dbname.".".$table." set revstatus='2' where notransaksi='".$notransaksi."' and revstatus='9'";
			$owlPDO->exec($str);

			$owlPDO->commit();
			echo "OK";
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
}
?>
