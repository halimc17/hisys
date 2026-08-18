<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$proses = checkPostGet('proses', '');
$periode = checkPostGet('periode', '');
$kodevhc = checkPostGet('kodevhc', '');
$kodebarang = checkPostGet('kodebarang', '');
switch ($proses) {
	case 'getdetailkmhm':
		$stream= "";
		$stream.= "<table class=sortable cellspacing=1 cellpadding=5 width=100%>";
		$stream.="
			<thead>
				<tr class=rowheader>
					<th align=center  rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center  rowspan='2'>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center  rowspan='2'>" . $_SESSION['lang']['kodebarang'] . "</th>
					<th align=center  rowspan='2'>" . $_SESSION['lang']['namabarang'] . "</th>
					<th align=center  rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center  rowspan='2'>KM/HM</th>
					<th align=center  rowspan='2'>" . $_SESSION['lang']['jumlah'] . "</th>
				</tr>
			</thead>
			<tbody>";

		$gtsrvhc=0;
		$no=0;
		$str = "select * from " . $dbname . ".log_zbahan_kendaraan_vw where kodevhc='" . $kodevhc . "' and tanggal like '" . $periode . "%' and kodebarang='".$kodebarang."' order by tanggal asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		while ($bar = $res->fetch()) {
			$no++;
			$stream.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$bar['notransaksi']."</td>
				<td align=center>".$bar['kodebarang']."</td>
				<td align=left>".$bar['namabarang']."</td>
				<td align=center>".$bar['tanggal']."</td>
				<td align=center>".$bar['kmhm']."</td>
				<td align=right>".number_format($bar['jumlah'],2)."</td>
			  </tr>";
			@$gtsrvhc+=$bar['jumlah'];
		}

		$stream.="
				<tr bgcolor=#00B366>
					<td align=left colspan='6'><b>" . $_SESSION['lang']['grnd_total'] . " " . $kodevhc . "</b></td>
					<td align=right><b>" . number_format($gtsrvhc,2) . "</b></td>";

		$stream.="</tr><thead>";
		$stream.="</tbody>
				  </table>";
        echo $stream;
	break;
}
?>