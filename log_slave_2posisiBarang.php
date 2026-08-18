<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$nopo = checkPostGet('nopo','');
$noPo = checkPostGet('noPo','');
$proses = checkPostGet('proses','');

$nmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');

$listOrg = getOrgDetail(4);

$border="";
$bgCol="";
if ($proses == 'excel') {
    $border = "border=1";
    $bgCol = "bgcolor=#999999 ";
}

##isi priv
$stream = "<table cellspacing='1' " . $border . " class='sortable' style='font-size:12px;'>
			<thead>
                            <tr class=rowheader>
                                <td align=center " . $bgCol . " rowspan=2>" . $_SESSION['lang']['nourut'] . "</td>
                                <td align=center " . $bgCol . " colspan=2>" . $_SESSION['lang']['daftarbarang'] . "</td>
                                <td align=center " . $bgCol . " colspan=2>" . $_SESSION['lang']['po'] . "</td>
                                <td align=center " . $bgCol . " colspan=2>BAPB</td>
                                <td align=center " . $bgCol . " colspan=2>Packing List</td>
                                <td align=center " . $bgCol . " colspan=6>" . $_SESSION['lang']['suratjalan'] . "</td>
                              </tr>
                              <tr class=rowheader>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['kodebarang'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['namabarang'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['nourut'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['tanggal'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['nourut'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['tanggal'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['nourut'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['tanggal'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['nourut'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['tanggalkirim'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['expeditor'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['pengirim'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['diterimaoleh'] . "</td>
                                <td align=center " . $bgCol . ">" . $_SESSION['lang']['tanggaltiba'] . "</td>
                              </tr>	
			</thead>
		<tbody>";





//kdbarang nopo
$aPo = "select kodebarang,nopo,tanggal from " . $dbname . ".log_po_vw where nopo='" . $nopo . "' and kodeorg in (" . $listOrg . ")";
$bPo=$owlPDO->query($aPo) or die(print " Gagal: ".PDOException::getMessage());
$bPo->setFetchMode(PDO::FETCH_ASSOC);
while ($cPo = $bPo->fetch()) {
    //nobpb
    $aBpb = "select notransaksi,tanggal from " . $dbname . ".log_transaksi_vw where nopo='" . $cPo['nopo'] . "' and kodebarang='" . $cPo['kodebarang'] . "' and tipetransaksi='1' ";
	$bBpb=$owlPDO->query($aBpb) or die(print " Gagal: ".PDOException::getMessage());
	$bBpb->setFetchMode(PDO::FETCH_ASSOC);
    $cBpb = $bBpb->fetch();

    $aPl = "select notransaksi,tanggal from " . $dbname . ".log_packing_vw where nopo='" . $cPo['nopo'] . "' and kodebarang='" . $cPo['kodebarang'] . "' ";
	$bPl=$owlPDO->query($aPl) or die(print " Gagal: ".PDOException::getMessage());
	$bPl->setFetchMode(PDO::FETCH_ASSOC);
    $cPl = $bPl->fetch();
    $nPl = $cPl['notransaksi'];
    $tPl = $cPl['tanggal'];

    //SJ
    $aSj = "select * from " . $dbname . ".log_suratjalan_vw where nopo='" . $cPo['nopo'] . "' and kodebarang='" . $cPo['kodebarang'] . "' ";
	$bSj=$owlPDO->query($aSj) or die(print " Gagal: ".PDOException::getMessage());
	$bSj->setFetchMode(PDO::FETCH_ASSOC);
    $cSj = $bSj->fetch();
    $nSj = $cSj['nosj'];
    $tglSj = $cSj['tanggal'];
    $tglKSj = $cSj['tanggalkirim'];
    $tglTSj = $cSj['tanggaltiba'];
    $eSj = $cSj['expeditor'];
    $pSj = $cSj['pengirim'];
    $tSj = $cSj['penerima'];


    $xSj = "select * from " . $dbname . ".log_suratjalan_vw where  kodebarang='" . $nPl . "' ";
	$ySj=$owlPDO->query($xSj) or die(print " Gagal: ".PDOException::getMessage());
	$ySj->setFetchMode(PDO::FETCH_ASSOC);
    $zSj = $ySj->fetch();
    $nSj1 = $zSj['nosj'];
    $tglSj1 = $zSj['tanggal'];
    $tglKSj1 = $zSj['tanggalkirim'];
    $tglTSj1 = $zSj['tanggaltiba'];
    $eSj1 = $cSj['expeditor'];
    $pSj1 = $cSj['pengirim'];
    $tSj1 = $cSj['penerima'];


    if ($nPl == '' || $nPl == 'NULL') {
        $nSj = $nSj;
        $tglSj = $tglSj;
        $tglKSj = $tglKSj;
        $tglTSj = $tglTSj;
        $eSj = $eSj;
        $pSj = $pSj;
        $tSj = $tSj;
    } else {
        $nSj = $nSj1;
        $tglSj = $tglSj1;
        $tglKSj = $tglKSj1;
        $tglTSj = $tglTSj1;
        $eSj = $eSj1;
        $pSj = $pSj1;
        $tSj = $tSj1;
    }




    $no+=1;
    $stream.="
			<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td>" . $cPo['kodebarang'] . "</td>
				<td>" . $nmBarang[$cPo['kodebarang']] . "</td>
				<td>" . $cPo['nopo'] . "</td>
				<td>" . tanggalnormal($cPo['tanggal']) . "</td>
				
				<td>" . $cBpb['notransaksi'] . "</td>
				<td>" . tanggalnormal($cBpb['tanggal']) . "</td>
				
				<td>" . $nPl . "</td>
				<td>" . tanggalnormal($tPl) . "</td>
				
				<td>" . $nSj . "</td>
				<td>" . tanggalnormal($tglSj) . "</td>
				<td>" . (isset($nmSup[$eSj]) ? $nmSup[$eSj] : '') . "</td>
				<td>" . (isset($nmKar[$pSj]) ? $nmKar[$pSj] : '') . "</td>
				
				<td>" . $tSj . "</td>
				<td>" . tanggalnormal($tglTSj) . "</td>
				
				
			</tr>";
}
$stream.="</tbody></table>";



switch ($proses) {
    case'goCariPo':
        echo"<fiedset>
			<table width=100% cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
					<td align=center>" . $_SESSION['lang']['nopo'] . "</td>
				</tr>
		</thead>
		</tbody>";  

        $i = "select distinct(nopo) as nopo from " . $dbname . ".log_po_vw where  nopo like '%" . $noPo . "%' and kodeorg in (" . $listOrg . ")";
		$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        while ($d = $n->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"goPickPO('" . $d['nopo'] . "');\">
					<td align=center>" . $no . "</td>
					<td>" . $d['nopo'] . "</td>
				</tr>";
        }
        echo "</table></fieldset>";
        break;








######HTML
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':

        $stream.="Print Time : " . date('H:i:s, d/m/Y') . "<br>By : " . $_SESSION['empl']['name'];
        $tglSkrg = date("Ymd");
        $nop_ = "Laporan Posisi Barang" . $tglSkrg;
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
        break;






    default;
}
?>