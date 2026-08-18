<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

empty($_POST['proses']) ? $proses = isset($_GET['proses']) ? $_GET['proses'] : '' : $proses = $_POST['proses'];
empty($_POST['unitDt']) ? $unitDt = isset($_GET['unitDt']) ? $_GET['unitDt'] : '' : $unitDt = $_POST['unitDt'];
empty($_POST['gudang']) ? $gudang = isset($_GET['gudang']) ? $_GET['gudang'] : '' : $gudang = $_POST['gudang'];
empty($_POST['periode']) ? $periode = isset($_GET['periode']) ? $_GET['periode'] : '' : $periode = $_POST['periode'];

$chksaldoawal = checkPostGet('chksaldoawal','');
$chkmasuk = checkPostGet('chkmasuk','');
$chkkeluar = checkPostGet('chkkeluar','');
$chksaldo = checkPostGet('chksaldo','');

$optNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNmSat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
if ($proses != 'getGudang') {
    $where = '';
    if ($unitDt == '') {
        exit("Error : Unit Tidak Boleh Kosong");
    } else {
        $where.="and kodegudang like '" . $unitDt . "%'";
    }

    if ($periode != '') {
        $where.=" and periode='" . $periode . "'";
    }
    $tab = '';
    if ($proses == 'excel') {
        $tab = " <table class=sortable cellspacing=1 border=1 width=100%>
	     <thead>
		    <tr>
			  <td  bgcolor=#DEDEDE  align=center>No.</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['unit'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['periode'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['namabarang'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['satuan'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['jenis'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['stokminimum'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['saldoawal'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['masuk'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['keluar'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['saldo'] . "</td>
			  <td  bgcolor=#DEDEDE  align=center>".$_SESSION['lang']['nilaisaldokurang']."</td>
			  
			</tr>  
		 </thead><tbody>";
    }

    if ($periode == '') {
        $sData = "select distinct sum(saldoawalqty) as saldoawalqty,sum(qtymasuk) as qtymasuk,
                sum(qtykeluar) as qtykeluar,sum(saldoakhirqty) as saldoakhirqty,periode,kodebarang 
                from " . $dbname . ".log_5saldobulanan where kodegudang!='' " . $where . " and (qtymasuk !=0 and qtykeluar !=0 or saldoakhirqty !=0)
                group by kodebarang,left(kodegudang,4)";
        $qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        while ($rData = $qData->fetch()) {
            $dtPeriode[$rData['periode']] = $rData['periode'];
            $lstKdBrg[$rData['kodebarang']] = $rData['kodebarang'];
            $dtKdBarang[$rData['periode']][$rData['kodebarang']] = $rData['kodebarang'];
            $dtAwal[$rData['periode'] . $rData['kodebarang']] = $rData['saldoawalqty'];
            $dtMasuk[$rData['periode'] . $rData['kodebarang']] = $rData['qtymasuk'];
            $dtKeluar[$rData['periode'] . $rData['kodebarang']] = $rData['qtykeluar'];
            $dtAkhir[$rData['periode'] . $rData['kodebarang']] = $rData['saldoakhirqty'];
        }
    } else {
        $sData = "select distinct distinct sum(saldoawalqty) as saldoawalqty,sum(qtymasuk) as qtymasuk,
                sum(qtykeluar) as qtykeluar,sum(saldoakhirqty) as saldoakhirqty,periode,kodebarang  
                from " . $dbname . ".log_5saldobulanan where kodegudang!='' " . $where . " and (qtymasuk !=0 and qtykeluar !=0 or saldoakhirqty !=0)
                group by kodebarang,left(kodegudang,4)";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        while ($rData = $qData->fetch()) {
            $dtPeriode[$rData['periode']] = $rData['periode'];
            $lstKdBrg[$rData['kodebarang']] = $rData['kodebarang'];
            $dtKdBarang[$rData['periode']][$rData['kodebarang']] = $rData['kodebarang'];
            $dtAwal[$rData['periode'] . $rData['kodebarang']] = $rData['saldoawalqty'];
            $dtMasuk[$rData['periode'] . $rData['kodebarang']] = $rData['qtymasuk'];
            $dtKeluar[$rData['periode'] . $rData['kodebarang']] = $rData['qtykeluar'];
            $dtAkhir[$rData['periode'] . $rData['kodebarang']] = $rData['saldoakhirqty'];
        }
    }
    $chekDt = count($dtPeriode);
    if ($chekDt == 0) {
        exit("Error:Data Kosong");
    }

    $no = 0;
    foreach ($dtPeriode as $dtIsi) {
        foreach ($lstKdBrg as $dtBrg) {
            if (!empty($dtKdBarang[$dtIsi][$dtBrg])) {
				$showhide = "hide";
				$showhide2 = "hide";
				$showhide3 = "hide";
				$showhide4 = "hide";
				if($chksaldoawal == '1'){
					$showhide = 'show';
				}else{
					if(number_format($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]],3) <= 0){
						$showhide = 'hide';
					}else{
						$showhide = 'show';
					}
				}
				
				if($chkmasuk == '1'){
					$showhide2 = 'show';
				}else{
					if(number_format($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]],3) <= 0){
						$showhide2 = 'hide';
					}else{
						$showhide2 = 'show';
					}
				}
				
				if($chkkeluar == '1'){
					$showhide3 = 'show';
				}else{
					if(number_format($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]],3) <= 0){
						$showhide3 = 'hide';
					}else{
						$showhide3 = 'show';
					}
				}
				
				if($chksaldo == '1'){
					$showhide4 = 'show';
				}else{
					if(number_format($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]],3) <= 0){
						$showhide4 = 'hide';
					}else{
						$showhide4 = 'show';
					}
				}
				
				if($showhide=='show' && $showhide2=='show' && $showhide3=='show' && $showhide4=='show'){
					$no+=1;
					$tglSkrg = date('Y-m-d H:i:s');
					$tab.="<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"detailMutasiBarang3(event,'" . $dtIsi . "','" . $unitDt . "','" . $dtKdBarang[$dtIsi][$dtBrg] . "','" . $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]] . "','" . $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]] . "');\">";
					$tab.="<td align=center style='width:30px;'>" . $no . "</td>";
					$tab.="<td align=center >" . $unitDt . "</td>";
					if ($proses != 'excel') {
						$tab.="<td align=center >" . $gudang . "</td>";
					}
					$tab.="<td align=center >" . $dtIsi . "</td>";
					$tab.="<td align=center >" . $dtKdBarang[$dtIsi][$dtBrg] . "</td>";
					$tab.="<td>" . $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]] . "</td>";
					$tab.="<td align=center >" . $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]] . "</td>";
					$tab.="<td align=center>" . getNamaBrg($dtKdBarang[$dtIsi][$dtBrg],'jenis') . "</td>";
					$tab.="<td></td>";
					if (substr($dtKdBarang[$dtIsi][$dtBrg], 0, 3) == '312') {
						$tab.="<td align=right class=firsttd >" . number_format($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 3) . "</td>"; //saldo awal
						$tab.="<td align=right class=firsttd >" . number_format($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 3) . "</td>"; //saldo masuk
						$tab.="<td align=right  class=firsttd >" . number_format($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 3) . "</td>"; //saldo keluar
						$tab.="<td align=right  class=firsttd >" . number_format($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 3) . "</td>"; //saldo akhir  
						// $tab.="<td align=right  class=firsttd ></td>"; //saldo akhir  
					
					} else {
						$tab.="<td align=right class=firsttd >" . number_format($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2) . "</td>"; //saldo awal
						$tab.="<td align=right class=firsttd >" . number_format($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2) . "</td>"; //saldo masuk
						$tab.="<td align=right  class=firsttd >" . number_format($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2) . "</td>"; //saldo keluar
						$tab.="<td align=right  class=firsttd >" . number_format($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2) . "</td>"; //saldo akhir  
						 
					}
					$tab.="<td></td>";
				}
            }
        }
    }
}



switch ($proses) {
    case'getGudang':
        $optUnit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sUnit = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where 
            kodeorganisasi like '" . $unitDt . "%' and tipe like 'GUDANG%' order by namaorganisasi asc";
		$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
		$qUnit->setFetchMode(PDO::FETCH_ASSOC);
        while ($rUnit = $qUnit->fetch()) {
            $optUnit.="<option value='" . $rUnit['kodeorganisasi'] . "'>" . $rUnit['namaorganisasi'] . "</option>";
        }
        echo $optUnit;
        break;
    case'preview':

        echo $tab;
        break;
    case'excel':
        // $tab.="</tbody></table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
        // $dte = date("Hms");
        // $nop_ = "lapPersediaanFisikUnit_" . $dte;
        // $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        // gzwrite($gztralala, $tab);
        // gzclose($gztralala);
        // echo "<script language=javascript1.2>
	// window.location='tempExcel/" . $nop_ . ".xls.gz';
	// </script>";
	
	
		$tab.="</tbody></table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
		$dte = date("Hms");
        $nop_ = "lapPersediaanFisikUnit_" . $dte;
		
		if(strlen($tab)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$tab)) {
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		} 
	
	
	
	
        break;
    case'pdf':

//=================================================
        class PDF extends FPDF {

            function Header() {
                global $namapt;
                global $pt;
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(20, 5, $namapt, '', 1, 'L');
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(190, 5, strtoupper($_SESSION['lang']['laporanstok']), 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell(140, 5, ' ', '', 0, 'R');
                $this->Cell(15, 5, $_SESSION['lang']['tanggal'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, date('d-m-Y H:i'), 0, 1, 'L');
                $this->Cell(140, 5, ' ', '', 0, 'R');
                $this->Cell(15, 5, $_SESSION['lang']['page'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, $this->PageNo(), '', 1, 'L');
                $this->Cell(140, 5, ' ', '', 0, 'R');
                $this->Cell(15, 5, 'User', '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, $_SESSION['standard']['username'], '', 1, 'L');
                $this->Ln();
                $this->SetFont('Arial', '', 6);
                $this->Cell(5, 5, 'No.', 1, 0, 'C');
                $this->Cell(15, 5, $_SESSION['lang']['unit'], 1, 0, 'C');
                //$this->Cell(20,5,$_SESSION['lang']['sloc'],1,0,'C');
                $this->Cell(17, 5, $_SESSION['lang']['periode'], 1, 0, 'C');
                $this->Cell(18, 5, $_SESSION['lang']['kodebarang'], 1, 0, 'C');
                $this->Cell(45, 5, substr($_SESSION['lang']['namabarang'], 0, 30), 1, 0, 'C');
                $this->Cell(8, 5, $_SESSION['lang']['satuan'], 1, 0, 'C');
                $this->Cell(20, 5, $_SESSION['lang']['saldoawal'], 1, 0, 'C');
                $this->Cell(15, 5, $_SESSION['lang']['masuk'], 1, 0, 'C');
                $this->Cell(15, 5, $_SESSION['lang']['keluar'], 1, 0, 'C');
                $this->Cell(15, 5, $_SESSION['lang']['saldo'], 1, 1, 'C');
            }

        }

//================================

        $pdf = new PDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $nor = 0;
        foreach ($dtPeriode as $dtIsi) {
            foreach ($lstKdBrg as $dtBrg) {
                if (!empty($dtKdBarang[$dtIsi][$dtBrg])) {
                    $nor+=1;
                    $pdf->Cell(5, 5, $nor, 1, 0, 'C');
                    $pdf->Cell(15, 5, $unitDt, 1, 0, 'C');
                    //$pdf->Cell(20,5,$gudang,1,0,'C');
                    $pdf->Cell(17, 5, $dtIsi, 1, 0, 'C');
                    $pdf->Cell(18, 5, $dtKdBarang[$dtIsi][$dtBrg], 1, 0, 'L');
                    $pdf->Cell(45, 5, $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]], 1, 0, 'L');
                    $pdf->Cell(8, 5, $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]], 1, 0, 'L');
                    if (substr($dtKdBarang[$dtIsi][$dtBrg], 0, 3) == '312') {
                        $pdf->Cell(20, 5, number_format($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 3, '.', ','), 1, 0, 'R');
                        $pdf->Cell(15, 5, number_format($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 3, '.', ','), 1, 0, 'R');
                        $pdf->Cell(15, 5, number_format($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 3, '.', ','), 1, 0, 'R');
                        $pdf->Cell(15, 5, number_format($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 3, '.', ','), 1, 1, 'R');
                    } else {
                        $pdf->Cell(20, 5, number_format($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2, '.', ','), 1, 0, 'R');
                        $pdf->Cell(15, 5, number_format($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2, '.', ','), 1, 0, 'R');
                        $pdf->Cell(15, 5, number_format($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2, '.', ','), 1, 0, 'R');
                        $pdf->Cell(15, 5, number_format($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2, '.', ','), 1, 1, 'R');
                    }
                }
            }
        }

        $pdf->Output();

        break;
    case'detailData':

        $gudang = $_GET['unitDt'];
        $periode = $_GET['periode'];
        $kodebarang = $_GET['kodebarang'];
        $namabarang = $_GET['namabarang'];
        $satuan = $_GET['satuan'];
//======================================
//ambil namapt
        $str = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $gudang . "'";
        $namapt = 'COMPANY NAME';
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $namapt = strtoupper($bar->namaorganisasi);
        }
//==========================get periode
        $str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi
      where left(kodeorg,4)='" . $gudang . "' and periode='" . $periode . "'";
        $awal = '';
        $akhir = '';
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $awal = $bar->tanggalmulai;
            $akhir = $bar->tanggalsampai;
        }

//ambil saldo awal===============================
        if ($gudang == '') {
            /* $str="select  sum(saldoakhirqty) as sawal,
              sum(nilaisaldoakhir) as sawalrp from
              ".$dbname.".log_5saldobulanan
              where kodebarang='".$kodebarang."'
              and periode='".$periode."'";
              //=========================================
              //ambil transaksi detail
              $strx="select a.*,b.idsupplier,b.tanggal,b.kodegudang,
              b.tipetransaksi
              from ".$dbname.".log_transaksidt a
              left join ".$dbname.".log_transaksiht b
              on a.notransaksi=b.notransaksi
              where kodebarang='".$kodebarang."'
              and kodept='".$rPt['induk']."'
              and b.tanggal>='".$awal."'
              and b.tanggal<='".$akhir."'
              and b.post=1
              order by tanggal,waktutransaksi";
             */
            $str = "select  sum(saldoawalqty) as sawal,
		  		sum(nilaisaldoawal) as sawalrp from 
				" . $dbname . ".log_5saldobulanan
				where kodebarang='" . $kodebarang . "'
				and periode='" . $periode . "' group by left(kodegudang,4)";
            //=========================================
            //ambil transaksi detail
            $strx = "select a.*,b.idsupplier,b.tanggal,b.kodegudang,
		      b.tipetransaksi 
		      from " . $dbname . ".log_transaksidt a
		      left join " . $dbname . ".log_transaksiht b
			  on a.notransaksi=b.notransaksi
			  where kodebarang='" . $kodebarang . "'
			  and kodegudang like '" . $gudang . "%'
			  and b.tanggal>='" . $awal . "'
			  and b.tanggal<='" . $akhir . "'
			  and b.post=1
			  order by tanggal,waktutransaksi ";
        } else {
            /* $str="select  sum(saldoakhirqty) as sawal,
              sum(nilaisaldoakhir) as sawalrp from
              ".$dbname.".log_5saldobulanan
              where kodebarang='".$kodebarang."'
              and periode='".$periode."'
              and kodegudang='".$gudang."'";
              //=========================================
              //ambil transaksi detail
              $strx="select a.*,b.idsupplier,b.tanggal,b.kodegudang,
              b.tipetransaksi
              from ".$dbname.".log_transaksidt a
              left join ".$dbname.".log_transaksiht b
              on a.notransaksi=b.notransaksi
              where kodebarang='".$kodebarang."'
              and kodept='".$rPt['induk']."'
              and kodegudang='".$gudang."'
              and b.tanggal>='".$awal."'
              and b.tanggal<='".$akhir."'
              and b.post=1
              order by tanggal,waktutransaksi";
             */
            $str = "select  sum(saldoawalqty) as sawal,
		  		sum(nilaisaldoawal) as sawalrp from 
				" . $dbname . ".log_5saldobulanan
				where kodebarang='" . $kodebarang . "'
				and periode='" . $periode . "'
				and kodegudang like '" . $gudang . "%' group by left(kodegudang,4)";

            //=========================================
            //ambil transaksi detail
            $strx = "select a.*,b.idsupplier,b.tanggal,b.kodegudang,
		      b.tipetransaksi
			  from " . $dbname . ".log_transaksidt a
		      left join " . $dbname . ".log_transaksiht b
			  on a.notransaksi=b.notransaksi
			  where kodebarang='" . $kodebarang . "'
			  
			  and kodegudang like '" . $gudang . "%'
			  and b.tanggal>='" . $awal . "'
			  and b.tanggal<='" . $akhir . "'
			  and b.post=1
			  order by tanggal,waktutransaksi";
//                exit('error: '.$strx);
        }
        $sawal = 0;
        $sawalrp = 0;
        $hargasawal = 0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $sawal = $bar->sawal;
            $sawalrp = $bar->sawalrp;
        }
        if ($sawal > 0)
            $hargasawal = $sawalrp / $sawal;

//=================================================
        class PDF extends FPDF {

            function Header() {
                global $namapt;
                global $namapt;
                global $gudang;
                global $periode;
                global $kodebarang;
                global $namabarang;
                global $satuan;
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(20, 5, $namapt, '', 1, 'L');
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(190, 5, strtoupper($_SESSION['lang']['detailtransaksibarang']), 0, 1, 'C');
                $this->SetFont('Arial', '', 8);

                $this->Cell(35, 5, $_SESSION['lang']['unit'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, $namapt, '', 0, 'L');
                $this->Cell(15, 5, $_SESSION['lang']['tanggal'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, date('d-m-Y H:i'), 0, 1, 'L');
//		$this->Cell(140,5,' ','',0,'R');
                $this->Cell(35, 5, $_SESSION['lang']['namabarang'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, '[' . $kodebarang . ']' . $namabarang . '(' . $satuan . ')', '', 0, 'L');
                $this->Cell(15, 5, $_SESSION['lang']['page'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, $this->PageNo(), '', 1, 'L');

//			$this->Cell(140,5,' ','',0,'R');
                $this->Cell(35, 5, $_SESSION['lang']['periode'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, $periode, '', 0, 'L');
                $this->Cell(15, 5, 'User', '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, $_SESSION['standard']['username'], '', 1, 'L');
//	        $this->Ln();

                $this->SetFont('Arial', '', 6);
                $this->Cell(5, 5, 'No.', 1, 0, 'C');
                $this->Cell(35, 5, $_SESSION['lang']['sloc'], 1, 0, 'C');
                $this->Cell(20, 5, $_SESSION['lang']['tanggal'], 1, 0, 'C');
                $this->Cell(25, 5, $_SESSION['lang']['tipe'], 1, 0, 'C');
                $this->Cell(25, 5, $_SESSION['lang']['saldoawal'], 1, 0, 'C');
                $this->Cell(25, 5, $_SESSION['lang']['masuk'], 1, 0, 'C');
                $this->Cell(25, 5, $_SESSION['lang']['keluar'], 1, 0, 'C');
                $this->Cell(25, 5, $_SESSION['lang']['saldo'], 1, 1, 'C');
            }

        }

//================================

        $kamustipe[0] = 'Koreksi';
        $kamustipe[1] = 'Penerimaan';
        $kamustipe[2] = 'Pengembalian Pengeluaran';
        $kamustipe[3] = 'Penerimaan Mutasi';
        $kamustipe[4] = 'Adjustment Penerimaan';
        $kamustipe[5] = 'Pengeluaran';
        $kamustipe[6] = 'Pengembalian Penerimaan';
        $kamustipe[7] = 'Pengeluaran Mutasi';
        $kamustipe[8] = 'Adjustment Pengeluaran';

        $pdf = new PDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_OBJ);
		$no = 0;
        $saldo = $sawal;
        $masuk = 0;
        $keluar = 0;
        while ($barx = $resx->fetch()) {
            $no+=1;
            if ($barx->tipetransaksi < 5) {
                $saldo = $saldo + $barx->jumlah;
                $masuk = $barx->jumlah;
                $keluar = 0;
            } else {
                $saldo = $saldo - $barx->jumlah;
                $keluar = $barx->jumlah;
                $masuk = 0;
            }

            $pdf->Cell(5, 5, $no, '1', 0, 'C');
            $pdf->Cell(35, 5, $barx->kodegudang, '1', 0, 'C');
            $pdf->Cell(20, 5, tanggalnormal($barx->tanggal), '1', 0, 'C');
            $pdf->Cell(25, 5, $kamustipe[$barx->tipetransaksi], '1', 0, 'C');
            if (substr($kodebarang, 0, 3) == '312') {
                $pdf->Cell(25, 5, number_format($sawal, 3, '.', ','), '1', 0, 'R');
                $pdf->Cell(25, 5, number_format($masuk, 3, '.', ','), '1', 0, 'R');
                $pdf->Cell(25, 5, number_format($keluar, 3, '.', ','), '1', 0, 'R');
                $pdf->Cell(25, 5, number_format($saldo, 3, '.', ','), '1', 1, 'R');
            } else {
                $pdf->Cell(25, 5, number_format($sawal, 2, '.', ','), '1', 0, 'R');
                $pdf->Cell(25, 5, number_format($masuk, 2, '.', ','), '1', 0, 'R');
                $pdf->Cell(25, 5, number_format($keluar, 2, '.', ','), '1', 0, 'R');
                $pdf->Cell(25, 5, number_format($saldo, 2, '.', ','), '1', 1, 'R');
            }

            $sawal = $saldo;
        }
        $pdf->Output();
        break;
    default:
        break;
}
?>