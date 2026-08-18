<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$periode = checkPostGet('periodeBeli', '');
$proses = checkPostGet('proses', '');
$pt = checkPostGet('pt', '');
$nopo = checkPostGet('nopo', '');
$kdsup = checkPostGet('kdsup', '');
$nmsup = checkPostGet('nmsup', '');

$whbrg = '';
$nmBrg = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', $whbrg);
$namaBarangCari = checkPostGet('namaBarangCari', '');

switch ($proses) {
	case'getListBarang':
        echo"<fieldset  style='float:left;' >
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>Search </td>

                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
                                    <button class=mybutton onclick=cariListBarang()>cari</button>
                            <td>
                        <tr>
                    </table>
  
                    <table id=listCariBarang width=100% class=sortable>
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>" . $_SESSION['lang']['supplier'] . "</td>
                            <td>" . $_SESSION['lang']['namasupplier'] . "</td>
                    </tr></thead>";

        if ($namaBarangCari == '') {
            
        } else {
			$i = "select * from " . $dbname . ".log_5supplier where namasupplier like '%" . $namaBarangCari . "%'";
			$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
			$n->setFetchMode(PDO::FETCH_ASSOC);
            while ($d = $n->fetch()) {
				// $whBrg = "kodebarang='" . $d['kodebarang'] . "'";
                $no+=1;
                echo"<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('" . $d['supplierid'] . "','" . $nmBrg[$d['supplierid']] . "');\">
						<td align=center>" . $no . "</td>
						<td>" . $d['supplierid'] . "</td>
						<td>" . $nmBrg[$d['supplierid']] . "</td>
						
				</tr>";
            }
        }
        echo"</table>
        </fieldset>";
        break;
	
	case'getnopo':
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
		{ 
			$optpo="<option value=''>".$_SESSION['lang']['all']."</option>";
			
			$str="select distinct nopo as nopo from ".$dbname.".log_poht where tglrelease like '".$periode."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT')";
			$str=$owlPDO->query($str);
			$str->setFetchMode(PDO::FETCH_OBJ);
			$optpo="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
			while($bar=$str->fetch())
			{
				$optpo.="<option value='".$bar->nopo."'>".$bar->nopo."</option>";
			}
			
		} else {
			   
			$optpo="<option value=''>".$_SESSION['lang']['all']."</option>";
			
			$str="select distinct nopo as nopo from ".$dbname.".log_poht where lokalpusat=1 and tglrelease like '".$periode."%' and kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' )";
			$str=$owlPDO->query($str);
			$str->setFetchMode(PDO::FETCH_OBJ);
			$optpo="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
			while($bar=$str->fetch())
			{
				$optpo.="<option value='".$bar->nopo."'>".$bar->nopo."</option>";
			}

		}
	echo $optpo;
	break;




    case'preview':
        $whi = '';
        echo"<table cellspacing=1 border=0 class=sortable><thead><tr class=rowheader>
            <td>" . $_SESSION['lang']['nourut'] . "</td>
            <td>" . $_SESSION['lang']['nm_perusahaan'] . "</td>
            <td>" . $_SESSION['lang']['namasupplier'] . "</td>
            <td>" . $_SESSION['lang']['tanggalRelease'] . "</td>
            <td>" . $_SESSION['lang']['nopo'] . "</td>
            <td>" . $_SESSION['lang']['subtotal'] . "</td>
            <td>" . $_SESSION['lang']['nilaippn'] . "</td>
            <td>" . $_SESSION['lang']['grnd_total'] . "</td>
        </tr></thead><tbody>";
        if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
            $whi.="";
        } else {
            $whi.=" and lokalpusat=1";
        }

        if ($pt != '') {
            $whi.=" and kodeorg='" . $pt . "'";
        }

        if ($nopo != '') {
            $whi.=" and nopo like '%" . $nopo . "%' ";
        }

        if ($kdsup != '') {
            $whi.=" and kodesupplier='" . $kdsup . "' ";
        }

        $sPembelian = "select * from " . $dbname . ".log_poht where tglrelease like '%" . $periode . "%' "
                . " " . $whi . " ";
		
		$qPembelian=$owlPDO->query($sPembelian) or die(print " Gagal: ".PDOException::getMessage());
		$qPembelian->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($qPembelian);
		if ($row > 0) {
            $nohtml = 0;
            while ($rPembelian = $qPembelian->fetch()) {
                $nohtml+=1;
                if (strlen($rPembelian['kodeorg']) == 1) {
                    $kdOrg = substr($rPembelian['nopo'], -3);
                } else {
                    $kdOrg = $rPembelian['kodeorg'];
                }
                $sComp = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $kdOrg . "'";
                $qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
				$qComp->setFetchMode(PDO::FETCH_ASSOC);
				$rComp = $qComp->fetch();

                $sSupplier = "select namasupplier from " . $dbname . ".log_5supplier where supplierid='" . $rPembelian['kodesupplier'] . "'";
				$qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
				$qSupplier->setFetchMode(PDO::FETCH_ASSOC);
                $rSupplier = $qSupplier->fetch();
                $test = "noPo" . "##" . $rPembelian['nopo'];

                echo"<tr style=cursor:pointer class=rowcontent onclick=\"masterPDF('log_poht','" . $rPembelian['nopo'] . "','','log_slave_print_detail_po','event')\" title=Click>
			
                        
                        <td align=center>" . $nohtml . "</td>
                        <td>" . $rComp['namaorganisasi'] . "</td>
			<td>" . $rSupplier['namasupplier'] . "</td>
			<td align=center>" . tanggalnormal($rPembelian['tglrelease']) . "</td>
			<td>" . $rPembelian['nopo'] . "</td>
			<td align=right>" . number_format($rPembelian['subtotal'], 2) . "</td>
			<td align=right>" . number_format($rPembelian['ppn'], 2) . "</td>
			<td align=right>" . number_format($rPembelian['nilaipo'], 2) . "</td>
			</tr>";
            }
        } else {
            echo"<tr class=rowcontent><td colspan=8 align=center>Not Found</td></tr>";
        }
        echo"</tbody></table>";
        break;
    case'pdf':
        $nopo = $_GET['noPo'];

        class PDF extends FPDF {

            function Header() {
                global $conn;
                global $dbname;
                global $userid;
                global $posted;
                global $tanggal;
                global $norek_sup;
                global $npwp_sup;
                global $nm_kary;
                global $nm_pt;
                global $nopo;
                global $owlPDO;


                $str = "select * from " . $dbname . ".log_poht  where nopo='" . $nopo . "'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				$bar = $res->fetch();

                //ambil nama pt
                $str1 = "select * from " . $dbname . ".organisasi where induk='MHO' and tipe='PT'";
                $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				while ($bar1 = $res1->fetch()) {
                    $namapt = $bar1->namaorganisasi;
                    $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
                    $telp = $bar1->telepon;
                }
                $sql = "select * from " . $dbname . ".log_5supplier where supplierid='" . $bar->kodesupplier . "'";
				$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
				$query->setFetchMode(PDO::FETCH_OBJ);
				$res = $query->fetch();

                $sql2 = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid='" . $bar->purchaser . "'";
                $query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
				$query2->setFetchMode(PDO::FETCH_OBJ);
				$res2 = $query2->fetch();

                $sql3 = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $bar->kodeorg . "'";
				$query3=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
				$query3->setFetchMode(PDO::FETCH_OBJ);
                $res3 = $query3->fetch();

                $norek_sup = $res->rekening;
                $npwp_sup = $res->npwp;
                $nm_kary = $res2->namakaryawan;
                $nm_pt = $res3->namaorganisasi;
                $path = 'images/logo.jpg';
                $this->Image($path, 15, 5, 18);
                $this->SetFont('Arial', 'B', 10);
                $this->SetFillColor(255, 255, 255);
                $this->SetX(40);
                $this->Cell(60, 5, $namapt, 0, 1, 'L');
                $this->SetX(40);
                $this->Cell(60, 5, $alamatpt, 0, 1, 'L');
                $this->SetX(40);
                $this->Cell(60, 5, "Tel: " . $telp, 0, 1, 'L');
                $this->Ln();
                $this->Cell(30, 4, "KEPADA YTH :", 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                
				$this->Cell(35, 4, $_SESSION['lang']['nm_perusahaan'], 0, 0, 'L');
                $this->Cell(40, 4, ": " . $res->namasupplier, 0, 1, 'L');
                $this->Cell(35, 4, $_SESSION['lang']['alamat'], 0, 0, 'L');
                $this->Cell(40, 4, ": " . $res->alamat, 0, 1, 'L');
                $this->Cell(35, 4, $_SESSION['lang']['telp'], 0, 0, 'L');
                $this->Cell(40, 4, ": " . $res->telepon, 0, 1, 'L');
                $this->Cell(35, 4, $_SESSION['lang']['fax'], 0, 0, 'L');
                $this->Cell(40, 4, ": " . $res->fax, 0, 1, 'L');

                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'U', 15);
                $this->SetY(60);
                $this->Cell(190, 5, strtoupper("Purchase Order"), 0, 1, 'C');
                
				$this->SetFont('Arial', '', 6);
                $this->SetY(27);
                $this->SetX(163);
                $this->Cell(30, 10, 'PRINT TIME : ' . date('d-m-Y H:i:s'), 0, 1, 'L');
                $this->Line(10, 27, 200, 27);
                $this->SetY(70);
                $this->SetFont('Arial', '', 9);
                $this->Cell(10, 4, "No.", 0, 0, 'L');
                $this->Cell(20, 4, ": " . $bar->nopo, 0, 1, 'L');
                $this->SetY(70);
                $this->SetX(145);
                $this->Cell(20, 4, "Tanggal PO.", 0, 0, 'L');
                $this->Cell(20, 4, ": " . tanggalnormal($bar->tanggal), 0, 1, 'L');
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }

        }

        $pdf = new PDF('P', 'mm', 'A4');
        $pdf->AddPage();

        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(8, 5, 'No', 1, 0, 'L', 1);
        $pdf->Cell(60, 5, $_SESSION['lang']['namabarang'], 1, 0, 'C', 1);
        $pdf->Cell(30, 5, $_SESSION['lang']['spesifikasi'], 1, 0, 'C', 1);
        $pdf->Cell(15, 5, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
        $pdf->Cell(10, 5, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
        $pdf->Cell(20, 5, $_SESSION['lang']['kurs'], 1, 0, 'C', 1);
        $pdf->Cell(25, 5, $_SESSION['lang']['hargasatuan'], 1, 0, 'C', 1);
        $pdf->Cell(25, 5, 'Total', 1, 1, 'C', 1);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 9);

        $str = "select * from " . $dbname . ".log_podt a inner join " . $dbname . ".log_poht b on a.nopo=b.nopo  where a.nopo='" . $nopo . "'";
		$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$re->setFetchMode(PDO::FETCH_OBJ);
        $no = 0;
        while ($bar = $re->fetch()) {
            $no+=1;

            $kodebarang = $bar->kodebarang;
            $jumlah = $bar->jumlahpesan;
            $harga_sat = $bar->hargasbldiskon;
            $total = $jumlah * $harga_sat;
            $namabarang = '';
            if ($bar->matauang == 1)
                $kr = "IDR";
            else
                $kr = "USD";
            $strv = "select * from " . $dbname . ".log_5masterbarang a left join " . $dbname . ".log_5photobarang b on a.kodebarang=b.kodebarang 
		   		  left join " . $dbname . ".log_5stkonversi c on b.kodebarang=c.kodebarang where a.kodebarang='" . $bar->kodebarang . "'";
			$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
			$resv->setFetchMode(PDO::FETCH_OBJ);
            while ($barv = $resv->fetch()) {
                $namabarang = $barv->namabarang;
                $satuan = $barv->satuankonversi;
                $spek = $barv->spesifikasi;
            }
            $pdf->Cell(8, 5, $no, 1, 0, 'L', 1);
            $pdf->Cell(60, 5, $namabarang, 1, 0, 'L', 1);
            $pdf->Cell(30, 5, $spek, 1, 0, 'L', 1);
            $pdf->Cell(15, 5, number_format($jumlah, 2, '.', ','), 1, 0, 'R', 1);
            $pdf->Cell(10, 5, $satuan, 1, 0, 'L', 1);
            $pdf->Cell(20, 5, $kr, 1, 0, 'C', 1);
            $pdf->Cell(25, 5, number_format($harga_sat, 2, '.', ','), 1, 0, 'C', 1);
            $pdf->Cell(25, 5, number_format($total, 2, '.', ','), 1, 1, 'R', 1);
        }
        $slopoht = "select * from " . $dbname . ".log_poht where nopo='" . $nopo . "'";
        $qlopoht=$owlPDO->query($slopoht) or die(print " Gagal: ".PDOException::getMessage());
		$qlopoht->setFetchMode(PDO::FETCH_OBJ);
		$rlopoht = $qlopoht->fetch();
        $sb_tot = $rlopoht->subtotal;
        $nil_diskon = $rlopoht->nilaidiskon;
        $nppn = $rlopoht->ppn;
        $stat_release = $rlopoht->stat_release;
        $user_release = $rlopoht->useridreleasae;
        $gr_total = ($sb_tot - $nil_diskon) + $nppn;
        $pdf->Cell(168, 5, $_SESSION['lang']['subtotal'], 1, 0, 'C', 1);
        $pdf->Cell(25, 5, number_format($rlopoht->subtotal, 2, '.', ','), 1, 1, 'R', 1);
        $pdf->Cell(168, 5, 'Discount(%)', 1, 0, 'C', 1);
        $pdf->Cell(25, 5, $rlopoht->diskonpersen, 1, 1, 'R', 1);
        $pdf->Cell(168, 5, 'PPh/PPN(%)', 1, 0, 'C', 1);
        $pdf->Cell(25, 5, number_format($rlopoht->ppn, 2, '.', ','), 1, 1, 'R', 1);
        $pdf->Cell(168, 5, $_SESSION['lang']['grnd_total'], 1, 0, 'C', 1);
        $pdf->Cell(25, 5, number_format($gr_total, 2, '.', ','), 1, 1, 'R', 1);
        $pdf->Ln();
        $pdf->Cell(30, 4, $_SESSION['lang']['tgl_kirim'], 0, 0, 'L');
        $pdf->Cell(40, 4, ": " . tanggalnormald($rlopoht->tanggalkirim), 0, 1, 'L');
        $pdf->Cell(30, 4, $_SESSION['lang']['almt_kirim'], 0, 0, 'L');
        $pdf->Cell(40, 4, ": " . $rlopoht->lokasipengiriman, 0, 1, 'L');
        $pdf->Cell(30, 4, $_SESSION['lang']['syaratPem'], 0, 0, 'L');
        $pdf->Cell(40, 4, ": " . $rlopoht->syaratbayar, 0, 1, 'L');
        $pdf->Cell(30, 4, $_SESSION['lang']['norekeningbank'], 0, 0, 'L');
        $pdf->Cell(40, 4, ": " . $norek_sup, 0, 1, 'L');
        $pdf->Cell(30, 4, $_SESSION['lang']['npwp'], 0, 0, 'L');
        $pdf->Cell(40, 4, ": " . $npwp_sup, 0, 1, 'L');
        $pdf->Cell(30, 4, $_SESSION['lang']['purchaser'], 0, 0, 'L');
        $pdf->Cell(40, 4, ": " . $nm_kary, 0, 1, 'L');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Cell(193, 4, $nm_pt, 0, 0, 'R');
		
//footer================================
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        if (($stat_release == '0') && ($user_release == '0000000000')) {
            $pdf->SetFont('Arial', 'U', 9);
            $pdf->Cell(187, 4, 'UNRELEASE PO, Please Contact Your Purchasing Manager', 0, 0, 'R');
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 9);
        } else {
            $pdf->Cell(187, 4, '( .......................................... )', 0, 0, 'R');
            $pdf->Ln();
            $pdf->Cell(160, 4, 'Jabatan :', 0, 0, 'R');
        }
        $pdf->Output();

        break;
		
    case'excel':
		$whi="";
        if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
            $whi.="";
        } else {
            $whi.=" and lokalpusat=1";
        }


        if ($pt != '') {
            $whi.=" and kodeorg='" . $pt . "'";
        }

        if ($nopo != '') {
            $whi.=" and nopo like '%" . $nopo . "%' ";
        }

        if ($kdsup != '') {
            $whi.=" and kodesupplier='" . $kdsup . "' ";
        }

        $strx = "select * from " . $dbname . ".log_poht where tglrelease like '%" . $periode . "%' "
                . " " . $whi . " ";


        $stream = "
			<table>
			<tr><td colspan=8 align=center>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['pembelianBarang'] . "</td></tr>
			<tr><td colspan=8 align=center>Periode : " . $periode . "</td></tr>
			</table>
			<table border=1>
			<tr>
				<td bgcolor=#DEDEDE align=center >No.</td>
				<td bgcolor=#DEDEDE align=center >" . $_SESSION['lang']['nm_perusahaan'] . "</td>
				<td bgcolor=#DEDEDE align=center >" . $_SESSION['lang']['namasupplier'] . "</td>
				<td bgcolor=#DEDEDE align=center >" . $_SESSION['lang']['tanggalRelease'] . "</td>
				<td bgcolor=#DEDEDE align=center >" . $_SESSION['lang']['nopo'] . "</td>
				<td bgcolor=#DEDEDE align=center >" . $_SESSION['lang']['subtotal'] . "</td>
				<td bgcolor=#DEDEDE align=center >" . $_SESSION['lang']['nilaippn'] . "</td>
				<td bgcolor=#DEDEDE align=center >" . $_SESSION['lang']['grnd_total'] . "</td>";
        $stream.="</tr>";

        $query=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($query);
		if ($row > 0) {
            $no = 0;
            while ($res = $query->fetch()) {
                $no+=1;
                if (strlen($res['kodeorg']) == 1) {
                    $kdOrg = substr($res['nopo'], -3);
                } else {
                    $kdOrg = $res['kodeorg'];
                }
                $sComp = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $kdOrg . "'";
				$qComp=$owlPDO->query($sComp) or die(print " Gagal: ".PDOException::getMessage());
				$qComp->setFetchMode(PDO::FETCH_ASSOC);
                $rComp = $qComp->fetch();

                $sSupplier = "select namasupplier from " . $dbname . ".log_5supplier where supplierid='" . $res['kodesupplier'] . "'";
				$qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
				$qSupplier->setFetchMode(PDO::FETCH_ASSOC);
                $rSupplier = $qSupplier->fetch();
                $stream.="<tr>
				<td>" . $no . "</td>
				<td>" . $rComp['namaorganisasi'] . "</td>
				<td>" . $rSupplier['namasupplier'] . "</td>
				<td>" . tanggalnormal($res['tglrelease']) . "</td>
				<td>" . $res['nopo'] . "</td>
				<td align=right>" . $res['subtotal'] . "</td>
				<td align=right>" . number_format($res['ppn'], 2) . "</td>
				<td align=right>" . number_format($res['nilaipo'], 2) . "</td>";
            }
        } else {
            $stream.="<tr><td colpsan=8>Not Found</td></tr>";
        }

        $stream.="</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];

        $nop_ = "PembelianBarang";
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
    default:
        break;
}
?>