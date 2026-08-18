<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if($_POST['periode']!='') {
    $param=$_POST;
}else{
    $param=$_GET;    
}
$proses=$_GET['proses'];
if(($proses=='excel')||($proses=='preview')){
   
        $brdr=0;
        $bgcoloraja='';
        if ($proses=='excel') {
            $bgcoloraja=" bgcolor=#DEDEDE ";
            $brdr=1;
        }

        $tab.="<table cellspacing=1 border=".$brdr." class=sortable>
                <thead class=rowheader>
                <tr ".$bgcoloraja.">
                <th align=center>".$_SESSION['lang']['afdeling']."</th>
                <th align=center>".$_SESSION['lang']['noakun']."</th>
                <th align=center>".$_SESSION['lang']['namaakun']."</th>
                <th align=center>".$_SESSION['lang']['rp'] . "</th>
                </tr></thead><tbody>";
        if(($_SESSION['empl']['tipelokasitugas']!='KANWIL')&&($_SESSION['empl']['tipelokasitugas']!='HOLDING')){
            $param['unitId']=$_SESSION['empl']['lokasitugas'];
        }else{
            if($param['unitId']==''){
                exit('warning: '.$_SESSION['lang']['unit']." ".$_SESSION['lang']['kosong']);
            }
        }
        if($param['intiPlasma']!=''){
            $whr=" and kodeblok in (select kodeorg from ".$dbname.".setup_blok where kodeorg like '".$param['unitId']."%' and intiplasma='".$param['intiPlasma']."') ";
        }
        if($param['periode']==''){
            exit('warning: '.$_SESSION['lang']['periode']." ".$_SESSION['lang']['kosong']);
        }
        $dtRow=array();
        $sData="select sum(jumlah) as jumlah,noakun,left(kodeblok,6) as divisi from ".$dbname.".keu_jurnaldt_vw where 
                tanggal like '".$param['periode']."%' and kodeorg='".$param['unitId']."' and left(noakun,1) in ('6','7') ".$whr." group by noakun,left(kodeblok,6)
                order by left(kodeblok,6),noakun asc";
        $rData=fetchdata($sData);
        if(count($rData)==0){
            exit('warning:'.$_SESSION['lang']['dataempty']);
        }
        foreach($rData as $row){
            $resKond=0;
            if(($row['divisi']=='')||(strlen($row['divisi'])==4)){
                $row['divisi']=$param['unitId'];
            }
            $dtUnit[$row['divisi']]=$row['divisi'];
            $dtAkun[$row['noakun']]=$row['noakun'];
            $dtRup[$row['divisi'].$row['noakun']]+=$row['jumlah'];
            $subTotal[$row['divisi']]+=$row['jumlah'];
            if(!empty($dtRow[$row['divisi']])){
                foreach($dtRow[$row['divisi']] as $baris=>$dt){
                    if($dt==$row['noakun']){
                        $resKond=1;
                    }
                }    
            }
            
            if($resKond==1){
                 continue;
            }
            $dtRow[$row['divisi']][]=$row['noakun'];    
            
        }
        
        $artampilan=false;
        foreach($dtUnit as $unitId){
            foreach ($dtAkun as $key) {
                if($dtRup[$unitId.$key]!=''){
                    $tab.="<tr class=rowcontent>";
                    if($tempId!=$unitId){
                        $tempId=$unitId;
                        $tab.="<td>".$unitId."</td>";
                        $artampilan=true;
                        $no=0;
                    }else{
                        if($artampilan==true){
                            $tab.="<td rowspan='".(count($dtRow[$unitId])-1)."'>&nbsp;</td>";
                            $artampilan=false;
                        }
                    }
                    $no+=1;
                    $nmAkun=makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$key."'");
                    $tab.="<td>".$key."</td>";
                    $tab.="<td>".$nmAkun[$key]."</td>";
                    $tab.="<td align=right>".number_format($dtRup[$unitId.$key],2)."</td>";
                    $tab.="</tr>";    
                    if(count($dtRow[$unitId])==$no){
                        $tab.="<tr class=rowcontent>";
                        $tab.="<td colspan=3>".$_SESSION['lang']['subtotal']." ".$unitId."</td>";
                        $tab.="<td align=right>".number_format($subTotal[$unitId],2)."</td>";
                        $tab.="</tr>";    
                        $grandTotal+=$subTotal[$unitId];
                    }
                }
            }
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($grandTotal,2)."</td>";
        $tab.="</tr>";    
        $tab.="</tbody></table>";
}
switch ($proses) {
    case'preview':
    echo $tab;
    break;
		
    case'pdf':
        $kdPt = $_GET['kdPt'];
        $kdSup = $_GET['kdSup'];
        $kdUnit = $_GET['kdUnit'];
        $tglDari = tanggalsystem($_GET['tglDr']);
        $tanggalSampai = tanggalsystem($_GET['tanggalSampai']);
        $lokBeli = $_GET['lokBeli'];
        
		if (($tglDari == '') || ($tanggalSampai == '')) {
            echo"warning:Tanggal Dari dan Sampai Tanggal Tidak Boleh Kosong";
            exit();
        } else {
            $where = "";
            if ($kdPt != '') {
                $where.=" and a.kodeorg='" . $kdPt . "'";
            }
            if ($kdUnit != '') {
                $where.=" and substring(b.nopp,16,4)='" . $kdUnit . "'";
            }
            if ($kdSup != "") {
                $where.=" and a.kodesupplier='" . $kdSup . "'";
            }
            if (($tglDr != '') || ($tanggalSampai != '')) {
                $where.=" and (a.tanggal between '" . $tglDari . "' and '" . tanggalsystem($_GET['tanggalSampai']) . "')";
            }
            if ($lokBeli != '') {
                $where.=" and lokalpusat='" . $lokBeli . "'";
            }
        }

        class PDF extends FPDF {

            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $kdPt;
                global $kdSup;
                global $kdUnit;
                global $tglDari;
                global $tanggalSampai;
                global $where;
                global $isi;
                global $owlPDO;

                $isi = array();
                if ($kdPt == "") {
                    $pt = 'MHO';
                } else {
                    $pt = $kdPt;
                }
				
                $sAlmat = "select namaorganisasi,alamat,telepon from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
				$qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
				$qAlamat->setFetchMode(PDO::FETCH_ASSOC);
                $rAlamat = $qAlamat->fetch();

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path = 'images/logo.jpg';
                $this->Image($path, $this->lMargin, $this->tMargin, 0, 55);
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(255, 255, 255);
                $this->SetX(100);
                $this->Cell($width - 100, $height, $rAlamat['namaorganisasi'], 0, 1, 'L');
                $this->SetX(100);
                $this->Cell($width - 100, $height, $rAlamat['alamat'], 0, 1, 'L');
                $this->SetX(100);
                $this->Cell($width - 100, $height, "Tel: " . $rAlamat['telepon'], 0, 1, 'L');
                $this->Line($this->lMargin, $this->tMargin + ($height * 4), $this->lMargin + $width, $this->tMargin + ($height * 4));
                $this->Ln();
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 11);
                $this->Cell($width, $height, $_SESSION['lang']['detPemb'], 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell($width, $height, "Periode : " . $_GET['tglDr'] . " s.d. " . $_GET['tanggalSampai'], 0, 1, 'C');
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 7);
                $this->SetFillColor(220, 220, 220);


                $this->Cell(3 / 100 * $width, $height, 'No', 1, 0, 'C', 1);
                $this->Cell(15 / 100 * $width, $height, $_SESSION['lang']['supplier'], 1, 0, 'C', 1);
                $this->Cell(12 / 100 * $width, $height, $_SESSION['lang']['nopo'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['tanggal'], 1, 0, 'C', 1);
                $this->Cell(22 / 100 * $width, $height, $_SESSION['lang']['namabarang'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['matauang'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
                $this->Cell(10 / 100 * $width, $height, $_SESSION['lang']['total'], 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['tanggal'] . " PP", 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['tanggal'] . " BAPB", 1, 1, 'C', 1);
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }

        }

        $pdf = new PDF('L', 'pt', 'A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 9;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 7);
        $sData = "select a.kodesupplier from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where a.statuspo>1 " . $where . " group by kodesupplier order by a.tanggal asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        while ($rData = $qData->fetch()) {
            $isi[] = $rData;
        }
        $totalAll = array('totalSemua' => 0);
        $no = $limit = 0;
        foreach ($isi as $test => $dt) {
            $no+=1;

            $i = 0;
            $afdC = false;
            $sNm = "select namasupplier from " . $dbname . ".log_5supplier where supplierid='" . $dt['kodesupplier'] . "'";
			$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
			$qNm->setFetchMode(PDO::FETCH_ASSOC);
            $rNm = $qNm->fetch();
            // if ($afdC == false) {
                // $pdf->Cell(3 / 100 * $width, $height, $no, 'TLR', 0, 'C', 1);
                // $pdf->Cell(15 / 100 * $width, $height, $rNm['namasupplier'], 'TLR', 0, 'C', 1);
            // }

            $sList = "select distinct a.tanggal,a.matauang,b.kodebarang,b.satuan,b.nopo,b.jumlahpesan,b.nopp,b.hargasatuan from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where a.kodesupplier='" . $dt['kodesupplier'] . "' and b.nopo!='NULL' and a.tanggal between '" . $tglDari . "' and '" . $tanggalSampai . "'";
			$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
			$qList->setFetchMode(PDO::FETCH_ASSOC);
            $grandTot = array('total' => 0);

            while ($rList = $qList->fetch()) {
                $limit++;
                $sBrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $rList['kodebarang'] . "'";
				$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
				$qBrg->setFetchMode(PDO::FETCH_ASSOC);
                $rBrg = $qBrg->fetch();
                if ($rList['matauang'] != 'IDR') {
                    $sKurs = "select kurs from " . $dbname . ".setup_matauangrate where kode='" . $rList['matauang'] . "' and daritanggal='" . $rList['tanggal'] . "'";
					$qKurs=$owlPDO->query($sKurs) or die(print " Gagal: ".PDOException::getMessage());
					$qKurs->setFetchMode(PDO::FETCH_ASSOC);
                    $rKurs = $qKurs->fetch();
                    if ($rKurs != '') {
                        $hrg = $rKurs['kurs'] * $rList['hargasatuan'];
                        $totHrg = $rList['jumlahpesan'] * $hrg;
                    } else {
                        if ($rList['matauang'] == 'USD') {
                            $hrg = $rList['hargasatuan'] * 8850;
                            $totHrg = $rList['jumlahpesan'] * $hrg;
                            $rList['matauang'] = "IDR";
                        } elseif ($rList['matauang'] == 'EUR') {
                            $hrg = $rList['hargasatuan'] * 12643;
                            $totHrg = $rList['jumlahpesan'] * $hrg;
                            $rList['matauang'] = "IDR";
                        } elseif (($rList['matauang'] == '') || ($rList['matauang'] == 'NULL')) {
                            $totHrg = $rList['jumlahpesan'] * $rList['hargasatuan'];
                        }
                    }
                } else {
                    $totHrg = $rList['jumlahpesan'] * $rList['hargasatuan'];
                }
                //$totHrg=$rList['jumlahpesan']*$rList['hargasatuan'];
                $grandTot['total']+=$totHrg;
                if ($rList['nopp'] != "") {
                    $sTgl = "select tanggal from " . $dbname . ".log_prapoht where nopp='" . $rList['nopp'] . "'";
					$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
					$qTgl->setFetchMode(PDO::FETCH_ASSOC);
                    $rTgl = $qTgl->fetch();

                    if (($rTgl['tanggal'] != "") || ($rTgl['tanggal'] != "000-00-00")) {
                        $tglPP = tanggalnormal($rTgl['tanggal']);
                    } else {
                        $tglPP = "";
                    }
                } else {
                    $tglPP = "";
                }
                if ($rList['nopo'] != "") {
                    $sTgl2 = "select tanggal from " . $dbname . ".log_transaksiht where nopo='" . $rList['nopo'] . "' and tipetransaksi=1";
					$qTgl2=$owlPDO->query($sTgl2) or die(print " Gagal: ".PDOException::getMessage());
					$qTgl2->setFetchMode(PDO::FETCH_ASSOC);
                    $rTgl2 = $qTgl2->fetch();
                    if ($rTgl2['tanggal'] != "") {
                        $tglBapb = tanggalnormal($rTgl2['tanggal']);
                    } else {
                        $tglBapb = "";
                    }
                } else {
                    $tglBapb = "";
                }
                if ($afdC == false) {
                    $i = 0;
					$pdf->Cell(3 / 100 * $width, $height, $no, 'TLR', 0, 'C', 1);
					$pdf->Cell(15 / 100 * $width, $height, $rNm['namasupplier'], 'TLR', 0, 'C', 1);
                    // $pdf->Cell(3 / 100 * $width, $height, '', 'LR', $align[$i], 1);
                    // $pdf->Cell(15 / 100 * $width, $height, '', 'LR', $align[$i], 1);
                    //$pdf->Cell($length[$i]/100*$width,$height,'','LR',$align[$i],1);
                    $i++;
					$afdC = true;
                } else {
					$pdf->Cell(3 / 100 * $width, $height, '', 'LR', $align[$i], 1);
                    $pdf->Cell(15 / 100 * $width, $height, '', 'LR', $align[$i], 1);
                }
                $pdf->Cell(12 / 100 * $width, $height, $rList['nopo'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, tanggalnormal($rList['tanggal']), 1, 0, 'C', 1);
                $pdf->Cell(22 / 100 * $width, $height, $rBrg['namabarang'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['matauang'], 1, 0, 'C', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['jumlahpesan'], 1, 0, 'R', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['satuan'], 1, 0, 'C', 1);
                $pdf->Cell(10 / 100 * $width, $height, number_format($totHrg, 2), 1, 0, 'R', 1);
                $pdf->Cell(7 / 100 * $width, $height, $tglPP, 1, 0, 'C', 1);
                $pdf->Cell(7 / 100 * $width, $height, $tglBapb, 1, 1, 'C', 1);
            }
            $totalAll['totalSemua']+=$grandTot['total'];
            if($grandTot['total'] != 0){
				$pdf->Cell(76 / 100 * $width, $height, "Sub Total", 1, 0, 'C', 1);
				$pdf->Cell(10 / 100 * $width, $height, number_format($grandTot['total'], 2), 1, 0, 'R', 1);
				$pdf->Cell(14 / 100 * $width, $height, '', 1, 1, 'R', 1);
			}
        }
        $pdf->Cell(76 / 100 * $width, $height, "Total", 1, 0, 'C', 1);
        $pdf->Cell(10 / 100 * $width, $height, number_format($totalAll['totalSemua'], 2), 1, 0, 'R', 1);
        $pdf->Cell(14 / 100 * $width, $height, '', 1, 1, 'R', 1);
        $pdf->Cell($width, $height, terbilang($totalAll['totalSemua'], 2), 1, 1, 'C', 1);


        $pdf->Output();
        break;
		
    case'excel':
        $tglSkrg=date("YmdHms");
        //$nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_sd_".per2;
        $nop_="biayaKebun__".$param['unitId']."__".$tglSkrg;
        if($param['unitId']==''){
            $nop_="biayaKebun__".$tglSkrg;
        }
        
        if(strlen($tab)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                                @unlink('tempExcel/'.$file);
                        }
                        }   
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$tab))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }   

        break;
}
?>