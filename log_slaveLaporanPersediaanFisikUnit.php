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
$optshow = checkPostGet('optshow','');
$chkmasuk = checkPostGet('chkmasuk','');
$chkkeluar = checkPostGet('chkkeluar','');
$chksaldo = checkPostGet('chksaldo','');
$textcari = checkPostGet('textcari','');

$optNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNmSat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optNmOrganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$where = '';
if ($proses != 'getGudang') {
    if ($unitDt == '') {
        exit("Error : Unit Tidak Boleh Kosong");
    }
    if ($gudang != '') {
        $where.="and kodegudang like '" . $gudang . "%'";
    } else {
        exit("Error : Gudang Tidak Boleh Kosong");
    }
    if ($periode != '') {
        $where.=" and periode='" . $periode . "'";
    }
    if ($proses == 'excel') {
        $tab = " <table class=sortable cellspacing=1 border=1 width=100%>
			<thead>
                <tr>
					<td  bgcolor=#DEDEDE  align=center>No.</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['unit'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['sloc'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['periode'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['namabarang'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['satuan'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['jenis'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['saldoawal'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['masuk'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['keluar'] . "</td>
					<td  bgcolor=#DEDEDE  align=center>" . $_SESSION['lang']['saldo'] . "</td>
					
                </tr>
            </thead><tbody>";
    }
	if($textcari!=''){
		$wh=" and (kodebarang like '%".$textcari."%' or kodebarang in (select kodebarang from " . $dbname . ".log_5masterbarang where namabarang like '%".$textcari."%'))";
	}
    if($optshow == '1'){
        $wh=" and (a.kodebarang like '%".$textcari."%' or a.namabarang like '%".$textcari."%')";
                $sData = "SELECT 
                b.*, 
                COALESCE(b.qtymasuk, 0) as qtymasuk,
                COALESCE(b.qtykeluar, 0) as qtykeluar,
                COALESCE(b.saldoakhirqty, 0) as saldoakhirqty,
                COALESCE(b.saldoawalqty, 0) as saldoawalqty
                FROM 
                    ".$dbname.".log_5masterbarang a 
                LEFT JOIN 
                    log_5saldobulanan b 
                ON 
                    a.kodebarang = b.kodebarang
                AND 
                    b.periode = '".$periode."'
                WHERE 
                    b.kodegudang != '' 
                AND 
                    b.kodegudang LIKE '".$gudang."%' and substr(a.kodebarang,1,1)='3' ".$wh." ";

    }else{
        $sData = "select distinct * from " . $dbname . ".log_5saldobulanan a 
        where kodegudang!='' " . $where . " and (qtymasuk !='0' or qtykeluar !='0' or saldoakhirqty !='0') ".$wh."";
    }
	//exit("error");
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

    $chekDt = count($dtPeriode);
    if ($chekDt == 0) {
        exit("Error:Data Kosong");
    }

    $no = 0;
    $tab.='';
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
					$strmin="select stok from ".$dbname.".log_5minimunstok where gudang='".$gudang."' and kodebarang='".$dtKdBarang[$dtIsi][$dtBrg]."'";
					$resmin=fetchdata($strmin);
					$stokmin = ($resmin[0]['stok']==''?0:$resmin[0]['stok']);
					$vstokmin = "";
					$skstok = "";
					$bgcolormin = "";
					if($stokmin > 0){
						$vstokmin = ($stokmin==0?'':$stokmin);						
						$skstok = $dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]] - $vstokmin;
						if($skstok < 0){
							$bgcolormin="red";
						}
					}
					$no+=1;
					$tglSkrg = date('Y-m-d H:i:s');
					$pdf=" title='PDF' onclick=\"detailMutasiBarang2(event,'" . $unitDt . "','" . $dtIsi . "','" . $gudang . "','" . $dtKdBarang[$dtIsi][$dtBrg] . "','" . $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]] . "','" . $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]] . "','pdf');\"";
					$xls=" title='Excel' onclick=\"detailMutasiBarang2(event,'" . $unitDt . "','" . $dtIsi . "','" . $gudang . "','" . $dtKdBarang[$dtIsi][$dtBrg] . "','" . $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]] . "','" . $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]] . "','excel');\"";
					$tab.="<tr class=rowcontent style='cursor:pointer;background-color:".$bgcolormin."'>";
					$tab.="<td align=center ".$pdf.">" . $no . "</td>";
					$tab.="<td align=center ".$pdf.">" . $unitDt . "</td>";
					$tab.="<td align=center ".$pdf.">" . $gudang . "</td>";
					$tab.="<td align=center ".$pdf.">" . $dtIsi . "</td>";
					$tab.="<td align=center ".$pdf.">" . $dtKdBarang[$dtIsi][$dtBrg] . "</td>";
					$tab.="<td  ".$pdf.">" . $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]] . "</td>";
					$tab.="<td align=center ".$pdf.">" . $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]] . "</td>";
					$tab.="<td align=center ".$pdf.">" . getNamaBrg($dtKdBarang[$dtIsi][$dtBrg],'jenis') . "</td>";
					$tab.="<td align=right ".$pdf.">".numberformat_kasih_koma($vstokmin,2)."</td>";
					// $tab.="<td align=right ".$pdf.">" . number_format($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2) . "</td>"; //saldo awal
					// $tab.="<td align=right ".$pdf.">" . number_format($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]],2) . "</td>"; //saldo masuk
					// $tab.="<td align=right ".$xls.">" . number_format($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]],2) . "</td>"; //saldo keluar
					// $tab.="<td align=right ".$xls.">" . number_format($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2) . "</td>"; //saldo akhir  
					// $tab.="<td align=right ".$xls.">" . hidezerodecimal($skstok, 2) . "</td>"; //saldo akhir  
					$tab.="<td align=right ".$pdf.">" . numberformat_kasih_koma($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]]) . "</td>"; //saldo awal
					$tab.="<td align=right ".$pdf.">" . numberformat_kasih_koma($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]]) . "</td>"; //saldo masuk
					$tab.="<td align=right ".$xls.">" . numberformat_kasih_koma($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]]) . "</td>"; //saldo keluar
					$tab.="<td align=right ".$xls.">" . numberformat_kasih_koma($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]]) . "</td>"; //saldo akhir  
					$tab.="<td align=right ".$xls.">" . numberformat_kasih_koma($skstok) . "</td>"; //saldo akhir  
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
        $tab.="</tbody></table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "lapPersediaanFisikUnit_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
			window.location='tempExcel/" . $nop_ . ".xls.gz';
			</script>";
        break;

    case'pdf':

        //=================================================
        class PDF extends FPDF {

            function Header() {
                global $namapt;
                global $pt;
                global $gudang;
                global $owlPDO;
                global $dbname;
                global $optNmOrganisasi;
                global $periode;

                if($pt == ''){
                    $pt =getindukPT(getindukPT($gudang));
                }

                $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
                $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_OBJ);
                while($bar1=$res1->fetch())
                {
                  $namapt=$bar1->namaorganisasi;
                  $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                  $telp=$bar1->telepon;				 
                } 
                $arrHead = setheadreport('',$pt);
                $path=$arrHead['logopalma'];
                //$path='images/logo.jpg';
                $this->Image($path,15,5,25);	
                $this->SetFont('Arial','B',10);
                $this->SetFillColor(255,255,255);	
                $this->SetY(5);
                $this->SetX(40);   
                $this->Cell(60,5,$namapt,0,1,'L');	 
                $this->SetX(40); 		
                $this->MultiCell(150,5,$alamatpt,0,'L');
                $this->SetX(40); 			
                $this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
                $this->SetFont('Arial','',15);
                $this->SetY(25);	


                
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(190, 5, strtoupper($_SESSION['lang']['laporanstok']), 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell(35, 5, $_SESSION['lang']['unit'] . "", '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, '' . substr($gudang,0,4) . ' - ' .$optNmOrganisasi[substr($gudang,0,4)], '', 0, 'L');
                $this->Cell(15, 5, $_SESSION['lang']['tanggal'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, date('d-m-Y H:i'), 0, 1, 'L');
                $this->Cell(35, 5, 'Gudang', '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, '' . $gudang . ' - ' .$optNmOrganisasi[$gudang], '', 0, 'L');
                $this->Cell(15, 5, $_SESSION['lang']['page'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, $this->PageNo(), '', 1, 'L');
                $this->Cell(35, 5, $_SESSION['lang']['periode'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, $periode, '', 0, 'L');
                $this->Cell(15, 5, 'User', '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, $_SESSION['standard']['username'], '', 1, 'L');
                
                $this->Ln();
                $this->SetFont('Arial', '', 6);
                $this->Cell(5, 5, 'No.', 1, 0, 'C');
                $this->Cell(15, 5, $_SESSION['lang']['unit'], 1, 0, 'C');
                $this->Cell(20, 5, $_SESSION['lang']['sloc'], 1, 0, 'C');
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
                    $pdf->Cell(20, 5, $gudang, 1, 0, 'C');
                    $pdf->Cell(17, 5, $dtIsi, 1, 0, 'C');
                    $pdf->Cell(18, 5, $dtKdBarang[$dtIsi][$dtBrg], 1, 0, 'L');
                    $pdf->Cell(45, 5, $optNmBrg[$dtKdBarang[$dtIsi][$dtBrg]], 1, 0, 'L');
                    $pdf->Cell(8, 5, $optNmSat[$dtKdBarang[$dtIsi][$dtBrg]], 1, 0, 'L');
                    $pdf->Cell(20, 5, number_format($dtAwal[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2, '.', ','), 1, 0, 'R');
                    $pdf->Cell(15, 5, number_format($dtMasuk[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2, '.', ','), 1, 0, 'R');
                    $pdf->Cell(15, 5, number_format($dtKeluar[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2, '.', ','), 1, 0, 'R');
                    $pdf->Cell(15, 5, number_format($dtAkhir[$dtIsi . $dtKdBarang[$dtIsi][$dtBrg]], 2, '.', ','), 1, 1, 'R');
                }
            }
        }
        $pdf->Output();
        break;
    case'detailData':
        $pt = $_GET['unitDt'];
        $gudang = $_GET['gudang'];
        $periode = $_GET['periode'];
        $kodebarang = $_GET['kodebarang'];
        $namabarang = $_GET['namabarang'];
        $satuan = $_GET['satuan'];
        //======================================
        //ambil namapt
        $str = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
        $namapt = 'COMPANY NAME';
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $namapt = strtoupper($bar->namaorganisasi);
        }
        //==========================get periode
        $str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi
			  where kodeorg='" . $gudang . "' and periode='" . $periode . "'";
        $awal = '';
        $akhir = '';
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $awal = $bar->tanggalmulai;
            $akhir = $bar->tanggalsampai;
        }
        $sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
		$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
		$qPt->setFetchMode(PDO::FETCH_ASSOC);
        $rPt = $qPt->fetch();
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
			and periode='" . $periode . "'";
            //=========================================
            //ambil transaksi detail
            $strx = "select a.*,b.idsupplier,b.tanggal,b.kodegudang,
				b.tipetransaksi 
				from " . $dbname . ".log_transaksidt a
				left join " . $dbname . ".log_transaksiht b
				on a.notransaksi=b.notransaksi
				where kodebarang='" . $kodebarang . "'
				and kodept='" . $rPt['induk'] . "'
				and b.tanggal>='" . $awal . "'
				and b.tanggal<='" . $akhir . "'
				and b.post=1
				order by tanggal,tipetransaksi,waktutransaksi";
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
			and kodegudang='" . $gudang . "'";
            //=========================================
            //ambil transaksi detail
            $strx = "select a.kodeblok,e.namaorganisasi, b.gudangx, d.nopol, d.kodevhc, d.detailvhc, c.namakaryawan, a.*,b.idsupplier,b.tanggal,b.kodegudang,b.notransaksireferensi,
				b.tipetransaksi, b.notransaksi, b.nopo, b.namapenerima 
				from " . $dbname . ".log_transaksidt a
				left join " . $dbname . ".log_transaksiht b
                on a.notransaksi=b.notransaksi
                left join " . $dbname . ".datakaryawan c
				on c.karyawanid=b.namapenerima
                left join " . $dbname . ".vhc_5master d
                on d.kodevhc=a.kodemesin
                left join " . $dbname . ".organisasi e
                on e.kodeorganisasi=b.gudangx
				where a.kodebarang='" . $kodebarang . "'
				and kodept='" . $rPt['induk'] . "'
				and kodegudang='" . $gudang . "'
				and b.tanggal>='" . $awal . "'
				and b.tanggal<='" . $akhir . "'
				and b.post=1
				order by tanggal,tipetransaksi,waktutransaksi";
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
                global $pt;
                global $gudang;
                global $periode;
                global $kodebarang;
                global $namabarang;
                global $satuan;
                global $optNmOrganisasi;
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(20, 5, $namapt, '', 1, 'L');
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(190, 5, strtoupper($_SESSION['lang']['detailtransaksibarang']), 0, 1, 'C');
                $this->SetFont('Arial', '', 8);

                $this->Cell(35, 5, $_SESSION['lang']['unit'] . "", '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, '[' . $pt . '] ' .$optNmOrganisasi[$pt], '', 0, 'L');
                $this->Cell(15, 5, $_SESSION['lang']['tanggal'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, date('d-m-Y H:i'), 0, 1, 'L');
                $this->Cell(35, 5, $_SESSION['lang']['namabarang'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, '[' . $kodebarang . '] ' . $namabarang . ' (' . $satuan . ')', '', 0, 'L');
                $this->Cell(15, 5, $_SESSION['lang']['page'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, $this->PageNo(), '', 1, 'L');
                $this->Cell(35, 5, $_SESSION['lang']['periode'], '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(100, 5, $periode, '', 0, 'L');
                $this->Cell(15, 5, 'User', '', 0, 'L');
                $this->Cell(2, 5, ':', '', 0, 'L');
                $this->Cell(35, 5, $_SESSION['standard']['username'], '', 1, 'L');

                $this->SetFont('Arial', '', 6);
                $this->Cell(5, 5, 'No.', 1, 0, 'C');
                $this->Cell(10, 5, $_SESSION['lang']['sloc'], 1, 0, 'C');
                $this->Cell(35, 5, $_SESSION['lang']['divisi'], 1, 0, 'C');
                $this->Cell(13, 5, $_SESSION['lang']['tanggal'], 1, 0, 'C');
                $this->Cell(35, 5, $_SESSION['lang']['tipe'], 1, 0, 'C');
                $this->Cell(32, 5, $_SESSION['lang']['nopo'], 1, 0, 'C');
                $this->Cell(27, 5, $_SESSION['lang']['notransaksi'], 1, 0, 'C');

                $this->Cell(29, 5, 'No Mesin', 1, 0, 'C');
                $this->Cell(30, 5, 'Penerima', 1, 0, 'C');

                $this->Cell(15, 5, $_SESSION['lang']['saldoawal'], 1, 0, 'C');
                $this->Cell(15, 5, $_SESSION['lang']['masuk'], 1, 0, 'C');
                $this->Cell(15, 5, $_SESSION['lang']['keluar'], 1, 0, 'C');
                $this->Cell(15, 5, $_SESSION['lang']['saldo'], 1, 1, 'C');
            }

        }

        //================================
        $kamustipe[0] = 'Koreksi';
        $kamustipe[1] = 'Penerimaan';
        $kamustipe[2] = 'Pengembalian Pengeluaran';
        $kamustipe[3] = 'Penerimaan Mutasi';
        $kamustipe[4] = '';
        $kamustipe[5] = 'Pengeluaran';
        $kamustipe[6] = 'Pengembalian Penerimaan';
        $kamustipe[7] = 'Pengeluaran Mutasi';

        $pdf = new PDF('L', 'mm', 'A4');
        // $pdf->SetMargins(1.8, 1.8, 1.8);
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

            if ($barx->tipetransaksi == 1) {
                $v_no_po = $barx->nopo;
                $v_no_trans = $barx->notransaksi;
            } else {
                $v_no_po = ' ';
                $v_no_trans = $barx->notransaksi;
            }

            if ($barx->kodemesin != ''){
                $penerima=$barx->namakaryawan;
            }elseif ($barx->gudangx != ''){
                $penerima=$barx->namaorganisasi;
            }else{
                $penerima=$barx->namakaryawan;
            }

            if ($barx->kodemesin != ''){
                $kdmesin=$barx->kodemesin ."-". $barx->nopol;
            }else{
                $kdmesin='';
            }
            /* DATA */
            $pdf->Cell(5, 5, $no, '1', 0, 'C');
            $pdf->Cell(10, 5, $barx->kodegudang, '1', 0, 'C');
            $pdf->Cell(35, 5, $optNmOrganisasi[$barx->kodeblok], '1', 0, 'L');
            $pdf->Cell(13, 5, tanggalnormal($barx->tanggal), '1', 0, 'C');
            $pdf->Cell(35, 5, $kamustipe[$barx->tipetransaksi], '1', 0, 'C');
            $pdf->Cell(32, 5, $v_no_po, '1', 0, 'C');
            $pdf->Cell(27, 5, $v_no_trans, '1', 0, 'C');
            $pdf->Cell(29, 5, $kdmesin, '1', 0, 'C');
            $pdf->Cell(30, 5, $penerima, '1', 0, 'C');
            $pdf->Cell(15, 5, number_format($sawal, 2, '.', ','), '1', 0, 'R');
            $pdf->Cell(15, 5, number_format($masuk, 2, '.', ','), '1', 0, 'R');
            $pdf->Cell(15, 5, number_format($keluar, 2, '.', ','), '1', 0, 'R');
            $pdf->Cell(15, 5, number_format($saldo, 2, '.', ','), '1', 1, 'R');
            // $pdf->SetX(108);
            // $pdf->Cell(10, 10, 'BKM : ' . $barx->notransaksireferensi, 0, 1, 'C');
            $sawal = $saldo;
        }
        $pdf->Output();
        break;
	case'popupexcel':
		$pt        = checkPostGet('unitDt','');
		$gudang    = checkPostGet('gudang','');
		$periode   = checkPostGet('periode','');
		$kodebarang= checkPostGet('kodebarang','');
		$namabarang= checkPostGet('namabarang','');
		$satuan    = checkPostGet('satuan','');
        //======================================
		
        //ambil namapt
        $str = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
        $namapt = 'COMPANY NAME';
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $namapt = strtoupper($bar->namaorganisasi);
        }
        //==========================get periode
        $str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi
			  where kodeorg='" . $gudang . "' and periode='" . $periode . "'";
        $awal = '';
        $akhir = '';
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $awal = $bar->tanggalmulai;
            $akhir = $bar->tanggalsampai;
        }
        $sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
		$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
		$qPt->setFetchMode(PDO::FETCH_ASSOC);
        $rPt = $qPt->fetch();
        //ambil saldo awal===============================
        if ($gudang == '') {
            $str = "select  sum(saldoawalqty) as sawal,
			sum(nilaisaldoawal) as sawalrp from 
			" . $dbname . ".log_5saldobulanan
			where kodebarang='" . $kodebarang . "'
			and periode='" . $periode . "'";
            //=========================================
            //ambil transaksi detail
            $strx = "select a.*,b.idsupplier,b.tanggal,b.kodegudang,
				b.tipetransaksi 
				from " . $dbname . ".log_transaksidt a
				left join " . $dbname . ".log_transaksiht b
				on a.notransaksi=b.notransaksi
				where kodebarang='" . $kodebarang . "'
				and kodept='" . $rPt['induk'] . "'
				and b.tanggal>='" . $awal . "'
				and b.tanggal<='" . $akhir . "'
				and b.post=1
				order by tanggal,tipetransaksi,waktutransaksi";
        } else {
            $str = "select  sum(saldoawalqty) as sawal,
			sum(nilaisaldoawal) as sawalrp from 
			" . $dbname . ".log_5saldobulanan
			where kodebarang='" . $kodebarang . "'
			and periode='" . $periode . "'
			and kodegudang='" . $gudang . "'";
            //=========================================
            //ambil transaksi detail
            $strx = "select e.namaorganisasi, b.gudangx, d.nopol, d.kodevhc, d.detailvhc, c.namakaryawan, a.*,b.idsupplier,b.tanggal,b.kodegudang,b.notransaksireferensi,
				b.tipetransaksi, b.notransaksi, b.nopo, b.namapenerima 
				from " . $dbname . ".log_transaksidt a
				left join " . $dbname . ".log_transaksiht b
                on a.notransaksi=b.notransaksi
                left join " . $dbname . ".datakaryawan c
				on c.karyawanid=b.namapenerima
                left join " . $dbname . ".vhc_5master d
                on d.kodevhc=a.kodemesin
                left join " . $dbname . ".organisasi e
                on e.kodeorganisasi=b.gudangx
				where a.kodebarang='" . $kodebarang . "'
				and kodept='" . $rPt['induk'] . "'
				and kodegudang='" . $gudang . "'
				and b.tanggal>='" . $awal . "'
				and b.tanggal<='" . $akhir . "'
				and b.post=1
				order by tanggal,tipetransaksi,waktutransaksi";
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

        //================================
        $kamustipe[0] = 'Koreksi';
        $kamustipe[1] = 'Penerimaan';
        $kamustipe[2] = 'Pengembalian Pengeluaran';
        $kamustipe[3] = 'Penerimaan Mutasi';
        $kamustipe[4] = '';
        $kamustipe[5] = 'Pengeluaran';
        $kamustipe[6] = 'Pengembalian Penerimaan';
        $kamustipe[7] = 'Pengeluaran Mutasi';
		
		$tab="<table width=100%>";
		$tab.="<tr><td colspan=12>".$namapt."</td></tr>";
		$tab.="<tr><td colspan=12 align=center>".strtoupper($_SESSION['lang']['detailtransaksibarang'])."</td></tr>";
		$tab.="<tr>
					<td align=left>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td align=left colspan=4>".$pt."</td>
					
					<td align=left>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td align=left colspan=4>".date('d-m-Y H:i')."</td>
				</tr>";
		
		$tab.="<tr>
					<td align=left>".$_SESSION['lang']['namabarang']."</td>
					<td>:</td>
					<td align=left colspan=4>".$kodebarang . " - " . $namabarang . ' (' . $satuan . ")</td>
					
					<td align=left>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td align=left colspan=4>".$periode."</td>
				</tr>";
		$tab.="</table>";
		$tab.="
			<table class=sortable cellspacing=1 cellpadding=5 border=1 >
	     <thead>
			<tr>
			  <th align=center style='width:50px;'>No.</th>
			  <th align=center>".$_SESSION['lang']['gudang']."</th>
			  <th align=center>".$_SESSION['lang']['tanggal']."</th>
			  <th align=center>".$_SESSION['lang']['tipe']."</th>
			  <th align=center>".$_SESSION['lang']['po']."</th>
			  <th align=center>".$_SESSION['lang']['notransaksi']."</th>
			  <th align=center>".$_SESSION['lang']['mesin']."</th>
			  <th align=center>".$_SESSION['lang']['penerima']."</th>
			  <th align=center>".$_SESSION['lang']['saldoawal']."</th>
			  <th align=center>".$_SESSION['lang']['masuk']."</th>
			  <th align=center>".$_SESSION['lang']['keluar']."</th>
			  <th align=center>".$_SESSION['lang']['saldo']."</th>
			</tr>
		 </thead>
		";
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

            if ($barx->tipetransaksi == 1) {
                $v_no_po = $barx->nopo;
                $v_no_trans = $barx->notransaksi;
            } else {
                $v_no_po = ' ';
                $v_no_trans = $barx->notransaksi;
            }

            if ($barx->kodemesin != ''){
                $penerima=$barx->namakaryawan;
            }elseif ($barx->gudangx != ''){
                $penerima=$barx->namaorganisasi;
            }

            if ($barx->kodemesin != ''){
                $kdmesin=$barx->kodemesin ."-". $barx->nopol;
            }else{
                $kdmesin='';
            }
            /* DATA */
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=left>".$barx->kodegudang."</td>";
			$tab.="<td align=left>".tanggalnormal($barx->tanggal)."</td>";
            
            $tab.="<td align=left>".$kamustipe[$barx->tipetransaksi]."</td>";
            $tab.="<td align=left>".$v_no_po."</td>";
            $tab.="<td align=left>".$v_no_trans."</td>";
            $tab.="<td align=left>".$kdmesin."</td>";
            $tab.="<td align=left>".$penerima."</td>";
            $tab.="<td align=right>".number_format($sawal, 2, '.', ',')."</td>";
            $tab.="<td align=right>".number_format($masuk, 2, '.', ',')."</td>";
            $tab.="<td align=right>".number_format($keluar, 2, '.', ',')."</td>";
            $tab.="<td align=right>".number_format($saldo, 2, '.', ',')."</td>";
            $sawal = $saldo;
        }
        
		$stream = $tab;
		$tglSkrg=date("Ymd");
		$nop_="stok_".$tglSkrg;
		if(strlen($stream)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}    	
        break;	
    default:
        break;
}