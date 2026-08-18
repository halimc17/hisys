<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
$sPt = "select b.namaorganisasi as namaorganisasi from " . $dbname . ".organisasi a 
		left join " . $dbname . ".organisasi b 
		on a.induk = b.kodeorganisasi
		where a.kodeorganisasi = '" . substr($_GET['kodept'], 0, 4) . "'";

$qPt = $owlPDO->query($sPt) or die(print " Gagal: " . PDOException::getMessage());
$qPt->setFetchMode(PDO::FETCH_ASSOC);
$rPt = $qPt->fetch();

@$totalBiaya = ($_GET['gaji'] * 1000) + ($_GET['lembur'] * 1000) + ($_GET['bbm'] * 1000) + ($_GET['sukucadang'] * 1000) + ($_GET['reparasi'] * 1000) + ($_GET['asuransi'] * 1000) + ($_GET['pajak'] * 1000) + ($_GET['penyusutan'] * 1000);
if ($_GET['hmkm'] == 0) {
    echo "Tidak ada Alokasi Biaya (Kosong)";
    exit();
}
@$rpHm = $totalBiaya / $_GET['hmkm'];
// print_r(substr($_GET['kodept'],5));
//=================================================
if (isset($_GET['type']) and $_GET['type'] == 'excel') {
    $border = 1;
} else {
    echo"<fieldset><legend>Print Excel</legend>
		 <img onclick=\"parent.detailKeExcel(event,'lbm_slave_transit_kendaraan_detail.php?type=excel&kodept=" . $_GET['kodept'] . "&periode=" . $_GET['periode'] . "&namakendaraan=" . $_GET['namakendaraan'] . "&gaji=" . $_GET['gaji'] . "&lembur=" . $_GET['lembur'] . "&bbm=" . $_GET['bbm'] . "&sukucadang=" . $_GET['sukucadang'] . "&reparasi=" . $_GET['reparasi'] . "&asuransi=" . $_GET['asuransi'] . "&pajak=" . $_GET['pajak'] . "&hmkm=" . $_GET['hmkm'] . "')\" src=images/excel.jpg class=resicon title='MS.Excel'>
		 </fieldset>";
    $border = 0;
}

$stream = "<table>
			<tr>
				<td><b>" . $rPt['namaorganisasi'] . "</b></td>
			</tr>
			<tr>
				<td><b>ALOKASI BIAYA</b></td>
			</tr>
		</table><p />";

$stream.="<table>
			<tr>
				<td>Periode</td>
				<td>:</td>
				<td>" . $_GET['periode'] . "</td>
			</tr>
			<tr>
				<td>Nama Vehicle</td>
				<td>:</td>
				<td>" . $_GET['namakendaraan'] . " (" . $_GET['kodept'] . ")</td>
			</tr>
		</table>
		<p />
		<table>
			<tr>
				<td>Gaji</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['gaji'] * 1000, 0) . "</td>
			</tr>
			<tr>
				<td>Premi/Lembur</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['lembur'] * 1000, 0) . "</td>
			</tr>
			<tr>
				<td>BBM/Pelumas</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['bbm'] * 1000, 0) . "</td>
			</tr>
			<tr>
				<td>Suku Cadang</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['sukucadang'] * 1000, 0) . "</td>
			</tr>
			<tr>
				<td>Reparasi</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['reparasi'] * 1000, 0) . "</td>
			</tr>
			<tr>
				<td>Asuransi</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['asuransi'] * 1000, 0) . "</td>
			</tr>
			<tr>
				<td>Pajak</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['pajak'] * 1000, 0) . "</td>
			</tr>
			<tr>
				<td>Penyusutan</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['penyusutan'] * 1000, 0) . "</td>
			</tr>
			<tr>
				<td><b>TOTAL</b></td>
				<td><b>:</b></td>
				<td style='text-align:right'><b>" . @number_format($totalBiaya, 0) . "</b></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>HM/KM</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($_GET['hmkm'], 0) . "</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>Rp/HM</td>
				<td>:</td>
				<td style='text-align:right'>" . @number_format($rpHm, 0) . "</td>
			</tr>
		</table><p />";

$getOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$sOrg = "select distinct(substring(a.alokasibiaya,1,4)) as alokasibiaya from " . $dbname . ".vhc_rundt a 
	left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
	where b.kodevhc='" . $_GET['kodept'] . "' 
	and b.tanggal like '" . $_GET['periode'] . "%'";

$rOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$rOrg->setFetchMode(PDO::FETCH_OBJ);
while ($bOrg = $rOrg->fetch()) {
    $rowData[$bOrg->alokasibiaya] = $bOrg->alokasibiaya;
}

$sList = "select a.notransaksi,a.alokasibiaya,a.keterangan,a.jumlah,b.tanggal,c.namakegiatan,c.noakun,c.kodekegiatan,a.kmhmawal 
		from " . $dbname . ".vhc_rundt a 
		left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi 
		left join " . $dbname . ".vhc_kegiatan c on a.jenispekerjaan = c.kodekegiatan
        where kodevhc='" . $_GET['kodept'] . "' 
        and b.tanggal like '" . $_GET['periode'] . "%' order by a.alokasibiaya asc";

$rList = $owlPDO->query($sList) or die(print " Gagal: " . PDOException::getMessage());
$rList->setFetchMode(PDO::FETCH_OBJ);
while ($bList = $rList->fetch()) {
    $myList = ($bList->notransaksi) . "" . ($bList->kmhmawal);
    $rowList[$myList] = $myList;
    $rowListDetail[$myList]['judul'] = substr($bList->alokasibiaya, 0, 4);
    $rowListDetail[$myList]['keterangan'] = $bList->keterangan;
    $rowListDetail[$myList]['hmkm'] = $bList->jumlah;
    $rowListDetail[$myList]['alokasibiaya'] = $bList->alokasibiaya;
    $rowListDetail[$myList]['jumlah'] = $bList->jumlah * $rpHm;
    $rowListDetail[$myList]['noakun'] = $bList->noakun;
    $rowListDetail[$myList]['namakegiatan'] = $bList->namakegiatan;
}

$akunkdari = '';
$akunksampai = '';
$strh = "select distinct noakundebet,sampaidebet  from " . $dbname . ".keu_5parameterjurnal where  jurnalid='LPVHC'";

$resh = $owlPDO->query($strh) or die(print " Gagal: " . PDOException::getMessage());
$resh->setFetchMode(PDO::FETCH_OBJ);
$barh = $resh->fetch(); {
    $akunkdari = $barh->noakundebet;
    $akunksampai = $barh->sampaidebet;
}
$unit = isset($unit) ? $unit : '';
$sJurnal = "select sum(debet) as jumlah, kodevhc from " . $dbname . ".keu_jurnaldt_vw where
		  kodevhc = '" . $_GET['kodept'] . "' 
		  and tanggal like '" . $_GET['periode'] . "%' and nojurnal like '%" . substr($unit, 0, 4) . "%'
		  and (noakun between '" . $akunkdari . "' and '" . $akunksampai . "') 
		  and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL)
		  group by kodevhc";

$qJurnal = $owlPDO->query($sJurnal) or die(print " Gagal: " . PDOException::getMessage());
$qJurnal->setFetchMode(PDO::FETCH_ASSOC);
$rJurnal = $qJurnal->fetch();


$sTotalJam = "select sum(jumlah) as jumlah,kodevhc from " . $dbname . ".vhc_rundt a left join 
       " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
      where b.tanggal like '" . $_GET['periode'] . "%' and b.kodevhc = '" . $_GET['kodept'] . "'
      group by b.kodevhc";

$qTotalJam = $owlPDO->query($sTotalJam) or die(print " Gagal: " . PDOException::getMessage());
$qTotalJam->setFetchMode(PDO::FETCH_ASSOC);
$rTotalJam = $qTotalJam->fetch();


if (!empty($rowData)) {
    foreach ($rowData as $judul) {
        $getOrg[$judul] = isset($getOrg[$judul]) ? $getOrg[$judul] : '';
        $stream.="<table border=" . $border . " cellspacing=1 style='padding-top:10px;'>
		<tr>
			<td><b>" . $getOrg[$judul] . "</b></td>
		</tr>
	</table>
	<table class=sortable border=" . $border . " cellspacing=1 cellpadding=1>
		<thead>		
		<tr class=rowcontent>
			<td style='vertical-align:top;'>Divisi</td>
			<td style='vertical-align:top;'>No Akun</td>
			<td style='vertical-align:top;'>Nama Kegiatan</td>
			<td style='vertical-align:top;'>Blok</td>
			<td style='vertical-align:top;'>TT</td>
			<td style='vertical-align:top;'>HM/KM</td>
			<td style='vertical-align:top;'>Umum</td>
			<td style='vertical-align:top;'>TBM-0</td>
			<td style='vertical-align:top;'>TBM-1</td>
			<td style='vertical-align:top;'>TBM-2</td>
			<td style='vertical-align:top;'>TBM-3</td>
			<td style='vertical-align:top;'>TM</td>
			<td style='vertical-align:top;'>Total Biaya Alokasi</td>
		</tr>
		</thead>";
        foreach ($rowList as $list) {
            // echo $rowListDetail[$list]['judul']."<br>";
            if ($judul == $rowListDetail[$list]['judul']) {

                $rpunit = 0;
                @$rpunit = $rJurnal['jumlah'] / $rTotalJam['jumlah'];
                $hargaUnit = $rpunit * $rowListDetail[$list]['hmkm'];

                $sBlok = "select * from " . $dbname . ".setup_blok where
					kodeorg = '" . $rowListDetail[$list]['alokasibiaya'] . "'";

                $qBlok = $owlPDO->query($sBlok) or die(print " Gagal: " . PDOException::getMessage());
                $rowBlok = owlBaris($qBlok);
                $qBlok->setFetchMode(PDO::FETCH_ASSOC);
                $rBlok = $qBlok->fetch();
                if ($rowBlok <= 0) {

                    $divisi = "-";
                    $blok = "-";
                    $tt = "-";
                    $tbm0 = 0;
                    $tbm1 = 0;
                    $tbm2 = 0;
                    $tbm3 = 0;
                    $tm = 0;
                    $umum = $hargaUnit;
                } else {
                    $divisi = substr($getOrg[$rBlok['kodeorg']], 0, 6);
                    $blok = $getOrg[$rBlok['kodeorg']];
                    $tt = $rBlok['tahuntanam'];
                    if ($rBlok['statusblok'] == 'TBM') {
                        if ((substr($_GET['periode'], 0, 4) - $tt == 0)) {
                            $tbm0 = $hargaUnit;
                            $tbm1 = 0;
                            $tbm2 = 0;
                            $tbm3 = 0;
                        } else if ((substr($_GET['periode'], 0, 4) - $tt == 1)) {
                            $tbm0 = 0;
                            $tbm1 = $hargaUnit;
                            $tbm2 = 0;
                            $tbm3 = 0;
                        } else if ((substr($_GET['periode'], 0, 4) - $tt == 2)) {
                            $tbm0 = 0;
                            $tbm1 = 0;
                            $tbm2 = $hargaUnit;
                            $tbm3 = 0;
                        } else {
                            $tbm0 = 0;
                            $tbm1 = 0;
                            $tbm2 = 0;
                            $tbm3 = $hargaUnit;
                        }
                        $umum = 0;
                        $tm = 0;
                    } else if ($rBlok['statusblok'] == 'TM') {
                        $umum = 0;
                        $tbm0 = 0;
                        $tbm1 = 0;
                        $tbm2 = 0;
                        $tbm3 = 0;
                        $tm = $hargaUnit;
                    } else {
                        $umum = $hargaUnit;
                        $tbm0 = 0;
                        $tbm1 = 0;
                        $tbm2 = 0;
                        $tbm3 = 0;
                        $tm = 0;
                    }
                }
                $total1 = $umum + $tbm0 + $tbm1 + $tbm2 + $tbm3 + $tm;
                $dz[$list]['umum'] = $umum;
                $dz[$list]['tbm0'] = $tbm0;
                $dz[$list]['tbm1'] = $tbm1;
                $dz[$list]['tbm2'] = $tbm2;
                $dz[$list]['tbm3'] = $tbm3;
                $dz[$list]['tm'] = $tm;
                $dz[$list]['total1'] = $total1;

                $stream.="<tbody>
				<tr class=rowcontent>
					<td style='vertical-align:top;'>" . $divisi . "</td>
					<td style='vertical-align:top;'>" . $rowListDetail[$list]['noakun'] . "</td>
					<td style='vertical-align:top;'>" . $rowListDetail[$list]['namakegiatan'] . "</td>
					<td style='vertical-align:top;'>" . $blok . "</td>
					<td style='vertical-align:top; text-align:center;'>" . $tt . "</td>
					<td style='vertical-align:top; text-align:right;'>" . @number_format($rowListDetail[$list]['hmkm'], 2) . "</td>
					<td style='vertical-align:top; text-align:right;'>" . @number_format($umum, 2) . "</td>
					<td style='vertical-align:top; text-align:right;'>" . @number_format($tbm0, 2) . "</td>
					<td style='vertical-align:top; text-align:right;'>" . @number_format($tbm1, 2) . "</td>
					<td style='vertical-align:top; text-align:right;'>" . @number_format($tbm2, 2) . "</td>
					<td style='vertical-align:top; text-align:right;'>" . @number_format($tbm3, 2) . "</td>
					<td style='vertical-align:top; text-align:right;'>" . @number_format($tm, 2) . "</td>
					<td style='vertical-align:top; text-align:right;'>" . @number_format($total1, 2) . "</td>
				</tr>";
                setIt($subtotal[$judul]['hmkm'], 0);
                setIt($subtotal[$judul]['umum'], 0);
                setIt($subtotal[$judul]['tbm0'], 0);
                setIt($subtotal[$judul]['tbm1'], 0);
                setIt($subtotal[$judul]['tbm2'], 0);
                setIt($subtotal[$judul]['tbm3'], 0);
                setIt($subtotal[$judul]['tm'], 0);
                setIt($subtotal[$judul]['total1'], 0);
                $subtotal[$judul]['hmkm'] += $rowListDetail[$list]['hmkm'];
                $subtotal[$judul]['umum'] += $dz[$list]['umum'];
                $subtotal[$judul]['tbm0'] += $dz[$list]['tbm0'];
                $subtotal[$judul]['tbm1'] += $dz[$list]['tbm1'];
                $subtotal[$judul]['tbm2'] += $dz[$list]['tbm2'];
                $subtotal[$judul]['tbm3'] += $dz[$list]['tbm3'];
                $subtotal[$judul]['tm'] += $dz[$list]['tm'];
                $subtotal[$judul]['total1'] += $dz[$list]['total1'];

                //GranTotal
                setIt($grandtotal[$judul]['umum'], 0);
                setIt($grandtotal['hmkm'], 0);
                setIt($grandtotal['tbm0'], 0);
                setIt($grandtotal['tbm1'], 0);
                setIt($grandtotal['tbm2'], 0);
                setIt($grandtotal['tbm3'], 0);
                setIt($grandtotal['tm'], 0);
                setIt($grandtotal['total1'], 0);
                @$grandtotal['hmkm'] += $rowListDetail[$list]['hmkm'];
                @$grandtotal['umum'] += $dz[$list]['umum'];
                @$grandtotal['tbm0'] += $dz[$list]['tbm0'];
                @$grandtotal['tbm1'] += $dz[$list]['tbm1'];
                @$grandtotal['tbm2'] += $dz[$list]['tbm2'];
                @$grandtotal['tbm3'] += $dz[$list]['tbm3'];
                @$grandtotal['tm'] += $dz[$list]['tm'];
                @$grandtotal['total1'] += $dz[$list]['total1'];
            }
        }
        $stream.="<tr class=rowcontent>
				<td colspan=5 style='text-align:center'><b>SUB TOTAL</b></td>
				<td style='text-align:right'><b>" . @number_format($subtotal[$judul]['hmkm'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($subtotal[$judul]['umum'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($subtotal[$judul]['tbm0'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($subtotal[$judul]['tbm1'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($subtotal[$judul]['tbm2'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($subtotal[$judul]['tbm3'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($subtotal[$judul]['tm'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($subtotal[$judul]['total1'], 2) . "</b></td>
		</tr>";
    }
}
$stream .= "<tr class=rowcontent>
				<td colspan=13 style='height:10px;'>&nbsp;</td>
			</tr>
			<tr class=rowcontent>
				<td colspan=5 style='text-align:center'><b>GRANDTOTAL</b></td>
				<td style='text-align:right'><b>" . @number_format($grandtotal['hmkm'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($grandtotal['umum'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($grandtotal['tbm0'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($grandtotal['tbm1'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($grandtotal['tbm2'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($grandtotal['tbm3'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($grandtotal['tm'], 2) . "</b></td>
				<td style='text-align:right'><b>" . @number_format($grandtotal['total1'], 2) . "</b></td>
		</tr>
	</tbody>
	</table><p />";

if (isset($_GET['type']) and $_GET['type'] == 'excel') {
    $handle = '';
    $stream.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
    $nop_ = "Detail_jurnal_";
    // $nop_="Detail_jurnal_".$_GET['gudang']."_".$_GET['periode'];
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
                parent.window.alert('Can't convert to excel format');
                </script>";
            exit;
        } else {
            echo "<script language=javascript1.2>
                window.location='tempExcel/" . $nop_ . ".xls';
                </script>";
        }
        fclose($handle);
    }
} else {
    echo $stream;
}
?>