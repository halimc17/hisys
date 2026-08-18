<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

$notransaksi = checkPostGet('notransaksi', '');

//=============
//create Header
class PDF extends FPDF {
    function Header() {
        global $conn;
        global $dbname;
        global $notransaksi;
        global $karyawan;
        global $namaatasan;
        global $atasan;
        global $atasanlangsung;
        global $namalangsung;
        global $namahc;
        global $hc;
        global $namahr;
        global $hr;
        global $catatan;
        global $tglpersetujuan;
        global $kekuatan;
        global $perbaikandiperlukan;
        global $dir;
        global $dir1;
        global $dir2;
        global $dir3;
        global $rekomendasi;
        global $owlPDO;

        $str = "select * from " . $dbname . ".log_formcapex_ht where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();

        //header PDF
        $arrHead = setheadreport('',$bar->kodept);
        $path=$arrHead['logo'];

        $this->SetMargins(15,10,0); 
        $this->SetFont('Arial','B',9);
        $this->SetFillColor(255,255,255);
        $this->SetX(55);
          
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

$pdf=new PDF('L','mm','A4');
$height=4;
$pdf->AddPage();

$str = "select * from " . $dbname . ".log_formcapex_ht where notransaksi='".$notransaksi."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$bar = $res->fetch();
$tanggal=$bar->tanggal;
$tgl_periksa1=$bar->tgl_periksa1;
$tgl_periksa2=$bar->tgl_periksa2;
$tgl_budget=$bar->tgl_budget;
$tgl_menyetujui1=$bar->tgl_menyetujui1;
$tgl_menyetujui2=$bar->tgl_menyetujui2;
$dibuat_oleh=$bar->dibuat_oleh;
$diperiksa1=$bar->diperiksa1;
$diperiksa2=$bar->diperiksa2;
$budget=$bar->budget;
$menyetujui1=$bar->menyetujui1;
$menyetujui2=$bar->menyetujui2;

//penandatangan
$optdibuat = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$dibuat_oleh."'");
$optperiksa1 = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$diperiksa1."'");
$optperiksa2 = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$diperiksa2."'");
$optbudget = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$budget."'");
$optmenyetujui1 = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$menyetujui1."'");
$optmenyetujui2 = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$menyetujui2."'");

//kode jabatan
$optkdibuat = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$dibuat_oleh."'");
$optkperiksa1 = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$diperiksa1."'");
$optkperiksa2 = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$diperiksa2."'");
$optkbudget = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$budget."'");
$optkmenyetujui1 = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$menyetujui1."'");
$optkmenyetujui2 = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$menyetujui2."'");

//jabatan
$optjdibuat = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$optkdibuat[$dibuat_oleh]."'");
$optjperiksa1 = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$optkperiksa1[$diperiksa1]."'");
$optjperiksa2 = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$optkperiksa2[$diperiksa2]."'");
$optjbudget = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$optkbudget[$budget]."'");
$optjmenyetujui1 = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$optkmenyetujui1[$menyetujui1]."'");
$optjmenyetujui2 = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$optkmenyetujui2[$menyetujui2]."'");

$strorg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='".$bar->kodept."'";
$resorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
$resorg->setFetchMode(PDO::FETCH_OBJ);
$barorg = $resorg->fetch();

$pdf->Ln(2);   
$pdf->SetFillColor(220,220,220);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(270,5,strtoupper("Formulir Capex"),0,1,'C'); 
$pdf->Ln(10); 
$pdf->SetFont('Arial','',9); 
$pdf->SetX(170);  
$pdf->Cell(30, 5,"Tanggal Pengajuan", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(60, 5,tanggalnormal($bar->tanggal), 0,1, 'J'); 
$pdf->SetX(170);  
$pdf->Cell(30, 5,"No.Transaksi", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(60, 5,$notransaksi, 0,1, 'J');
$pdf->SetX(170);
$pdf->Cell(30, 5,"Company Name", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(60, 5,$barorg->namaorganisasi, 0,1, 'J');
$pdf->SetX(170);
$pdf->Cell(30, 5,"Location (BU)", 0, 'J');
$pdf->Cell(5, 5,":", 0,0, 'J');
$pdf->Cell(60, 5,$res2->bagian, 0,1, 'J');

$pdf->Ln(10);
$pdf->SetFont('Arial','',7);    
$pdf->SetFillColor(220,220,220);
$pdf->SetFont('Arial','B',8);
$yawal=$pdf->GetY();
$pdf->MultiCell(8,10,'No',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(23);
$pdf->MultiCell(40,10,'Nama Barang',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(63);
$pdf->Cell(20,5,'Kuantitas',1,1,'C',1);
$pdf->SetX(63);
$pdf->Cell(8,5,'Unit',1,0,'C',1);
$pdf->SetX(71);
$pdf->Cell(12,5,'Satuan',1,1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(83);
$pdf->MultiCell(20,10,'ETA',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(103);
$pdf->MultiCell(20,5,'Estimasi Harga satuan',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(123);
$pdf->MultiCell(20,10,'total',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(143);
$pdf->MultiCell(20,5,'Budget (Capex)',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(163);
$pdf->MultiCell(20,5,'Budget Balance',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(183);
$pdf->MultiCell(30,10,'Catatan',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(213);
$pdf->MultiCell(20,5,'Sub Tipe Asset',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(233);
$pdf->MultiCell(25,10,'Kode Asset',1,'C',1);
$pdf->SetY($yawal);
$pdf->SetX(258);
$pdf->MultiCell(25,10,'Nama Asset',1,'C',1);
$yawal1=$pdf->GetY();
$yawal=$pdf->GetY();

$no=0;
$no2=0;
$str = "select * from " . $dbname . ".log_formcapex_dt where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {

            //nama barang dan satuan
            $sSat="select satuan,namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
            $qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
            $qSat->setFetchMode(PDO::FETCH_ASSOC);
            $rSat=$qSat->fetch();
            $satuan=$rSat['satuan'];
            $namabarang=$rSat['namabarang'];

            //kode asset dan suptipe
            $sSat="select * from ".$dbname.".log_formcapex_assetcode where kodebarang='".$bar->kodebarang."' and notransaksi='".$notransaksi."'";
            $qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
            $qSat->setFetchMode(PDO::FETCH_ASSOC);
            $rSat=$qSat->fetch();
            $kodeasset=$rSat['kodeasset'];
            $subtipeasset=$rSat['subtipeasset'];
            $namaasset=$rSat['namaasset'];

            //nama suptipe
            $sSat="select namasub from ".$dbname.".sdm_5subtipeasset where kodetipe='".$kodeasset."' and kodesub='".$subtipeasset."'";
            $qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
            $qSat->setFetchMode(PDO::FETCH_ASSOC);
            $rSat=$qSat->fetch();
            $namasub=$rSat['namasub'];

            //nama suptipe
            $sSat="select kode from ".$dbname.".project where substr(kode,4,2)='".$kodeasset."' and subtipe='".$subtipeasset."' and nama='".$namaasset."' and keterangan='".$notransaksi."'";
            // echo $sSat;
            $qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
            $qSat->setFetchMode(PDO::FETCH_ASSOC);
            $rSat=$qSat->fetch();
            $kodeproject=$rSat['kode'];

            $no+=1;
            $pdf->SetFont('Arial','B',9);

            if ($no!=1){
                $yawal=$yakhir;
            }

            $pdf->SetY($yawal);
            $pdf->Cell(8,5,$no,0,0,'C');
            $pdf->MultiCell(40,5,$bar->kodebarang."\n".$namabarang,0,'J');
            $yakhir=$pdf->GetY();

            $pdf->SetY($yawal);
            $pdf->SetX(63);
            $pdf->Cell(8,5,$bar->jumlah,0,0,'C');
            $pdf->Cell(12,5,$satuan,0,0,'C');
            $pdf->Cell(20,5,tanggalnormal($bar->tanggal_eta),0,0,'C');
            $pdf->Cell(20,5,number_format($bar->hargasatuan),0,0,'R');
            $total=($bar->jumlah)*($bar->hargasatuan);
            $pdf->Cell(20,5,number_format($total),0,0,'R');
            @$totseluruh+=$total;
            $pdf->Cell(20,5,number_format($capex),0,0,'R');
            $totcapex+=$capex;
            $pdf->Cell(20,5,number_format($balance),0,0,'R');
            $totbalance+=$balance;
            $pdf->SetY($yawal);
            $pdf->SetX(183);
            $pdf->MultiCell(30,5,$bar->catatan,0,'J');
            $yakhir1=$pdf->GetY();

            if ($yakhir1>$yakhir){
                $yakhir=$yakhir1;
            }

            $pdf->SetY($yawal);
            $pdf->SetX(213);
            $pdf->MultiCell(20,5,$namasub,0,'J');
            $yakhir1=$pdf->GetY();

            if ($yakhir1>$yakhir){
                $yakhir=$yakhir1;
            }

            $pdf->SetY($yawal);
            $pdf->SetX(233);
            $pdf->Cell(25,5,$kodeproject,0,0,'C');
            $pdf->MultiCell(25,5,$namaasset,0,'J');
            $yakhir1=$pdf->GetY();

            if ($yakhir1>$yakhir){
                $yakhir=$yakhir1;
            }

            $pdf->Line(15, $yawal, 15, $yakhir);
            $pdf->Line(23, $yawal, 23, $yakhir);
            $pdf->Line(283, $yawal, 283, $yakhir);

            if($pdf->GetY() > 160) {
                $i=0;
                $pdf->Line(15,$yakhir,283,$yakhir);
                $yakhir=$yakhir-20;
                $yakhir=$pdf->GetY()-$yakhir;
                $yawal=$yakhir;
                //$akhirY=$akhirY+70;
                $pdf->AddPage();
                $pdf->Line(15,$yakhir,283,$yakhir);
            }
        }

        $pdf->Line(15, $yakhir+5, 283, $yakhir+5);


        $pdf->SetY($yakhir);
        $pdf->Cell(88,5,'','TL',0,'R');
        $pdf->Cell(20,5,'Grand Total','TBR',0,'R');
        $pdf->Cell(20,5,number_format($totseluruh),1,0,'R');
        $pdf->Cell(20,5,number_format($totcapex),1,0,'R');
        $pdf->Cell(20,5,number_format($totbalance),'TBR',0,'R');
        $pdf->Cell(100,5,'','TR',1,'R');

        $pdf->Ln(10);
        $pdf->Cell(27,5,'Keterangan',1,0,'C');
        $pdf->Cell(40,5,'Dibuat Oleh',1,0,'C');
        $pdf->Cell(40,5,'Diperiksa (1) Oleh',1,0,'C');
        $pdf->Cell(40,5,'Diperiksa (2) Oleh',1,0,'C');
        $pdf->Cell(40,5,'Budget Controller',1,0,'C');
        $pdf->Cell(40,5,'Menyetujui (Budget)',1,0,'C');
        $pdf->Cell(40,5,'Menyetujui (>= 50 Juta)',1,1,'C');

        $pdf->Cell(27,15,'TTD',1,0,'C');
        $pdf->Cell(40,15,'',1,0,'L');
        $pdf->Cell(40,15,'',1,0,'L');
        $pdf->Cell(40,15,'',1,0,'L');
        $pdf->Cell(40,15,'',1,0,'L');
        $pdf->Cell(40,15,'',1,0,'L');
        $pdf->Cell(40,15,'',1,1,'L');

        $pdf->Cell(27,5,'Tanggal',1,0,'C');
        $pdf->Cell(40,5,tanggalnormal($tanggal),1,0,'C');
        $pdf->Cell(40,5,tanggalnormal($tgl_periksa1),1,0,'C');
        $pdf->Cell(40,5,tanggalnormal($tgl_periksa2),1,0,'C');
        $pdf->Cell(40,5,tanggalnormal($tgl_budget),1,0,'C');
        $pdf->Cell(40,5,tanggalnormal($tgl_menyetujui1),1,0,'C');
        $pdf->Cell(40,5,tanggalnormal($tgl_menyetujui2),1,1,'C');

        $pdf->Cell(27,5,'Nama',1,0,'C');
        $pdf->Cell(40,5,$optdibuat[$dibuat_oleh],1,0,'C');
        $pdf->Cell(40,5,$optperiksa1[$diperiksa1],1,0,'C');
        $pdf->Cell(40,5,$optperiksa2[$diperiksa2],1,0,'C');
        $pdf->Cell(40,5,$optbudget[$budget],1,0,'C');
        $pdf->Cell(40,5,$optmenyetujui1[$menyetujui1],1,0,'C');
        $pdf->Cell(40,5,$optmenyetujui2[$menyetujui2],1,1,'C');

        $pdf->Cell(27,5,'Position',1,0,'C');
        $pdf->Cell(40,5,$optjdibuat[$optkdibuat[$dibuat_oleh]],1,0,'C');
        $pdf->Cell(40,5,$optjperiksa1[$optkperiksa1[$diperiksa1]],1,0,'C');
        $pdf->Cell(40,5,$optjperiksa2[$optkperiksa2[$diperiksa2]],1,0,'C');
        $pdf->Cell(40,5,$optjbudget[$optkbudget[$budget]],1,0,'C');
        $pdf->Cell(40,5,$optjmenyetujui1[$optkmenyetujui1[$menyetujui1]],1,0,'C');
        $pdf->Cell(40,5,$optjmenyetujui2[$optkmenyetujui2[$menyetujui2]],1,1,'C');


//     $pdf->SetFont('Arial', '', 9);
//     $pdf->SetY($ykekuatan);
//     $pdf->SetX(135);
//     $pdf->Cell(60, 5,"Kekuatan : ", 'BR', 1, 'C');
//     $pdf->SetX(135);
//     $pdf->MultiCell(60,5,$kekuatan,0,'J',0);

//     $pdf->SetY($yperbaikan);
//     $pdf->SetX(135);
//     $pdf->Cell(60, 5,"Perbaikan yang diperlukan : ", 'BR', 1, 'C');
//     $pdf->SetX(135);
//     $pdf->MultiCell(60,5,$perbaikandiperlukan,0,'J',0);

    
//     $pdf->SetX(15);
//     $pdf->SetY($akhiry);
//     $pdf->Ln(5);
//     $pdf->SetFont('Arial', 'B', 9);
//     $awalygaris=$pdf->GetY();
//     $awalxgaris=$pdf->GetX();
//     $pdf->Cell(25, 5,"Rekomendasi Atasan Langsung ** :", 0, 1, 'L');
//     $pdf->Cell(25, 5,"(Beri tanda centang pada salah satu pilihan di bawah ini)", 0, 1, 'L');
//     $pdf->SetFont('Arial', '', 9);
//     $y1=$pdf->GetY();
//     $pdf->Cell(8, 6,'', 1, 0, 'L');
//     $pdf->Cell(25, 6,"Diangkat", 0, 1, 'L');
//     $y2=$pdf->GetY();
//     $pdf->Cell(8, 6,'', 1, 0, 'L');
//     $pdf->Cell(25, 6,"Kontrak Diperpanjang", 0, 1, 'L');
//     $y3=$pdf->GetY();
//     $pdf->Cell(8, 6,'', 1, 0, 'L');
//     $pdf->Cell(25, 6,"Kontrak Diperbarui (harus melalui jeda/non aktif 30 hari)", 0, 1, 'L');
//     $y4=$pdf->GetY();
//     $pdf->Cell(8, 6,'', 1, 0, 'L');
//     $pdf->Cell(25, 6,"Pemutusan Hubungan Kerja", 0, 1, 'L');
//     $akhirygaris=$pdf->GetY();

//     $path='images/icons/Grey/GIF/action_check.gif';
//     if ($rekomendasi==1){
//         $pdf->Image($path, 16, $y1, 0);
//     }if ($rekomendasi==2){
//         $pdf->Image($path, 16, $y2, 0);
//     }if ($rekomendasi==3){
//         $pdf->Image($path, 16, $y3, 0);
//     }if ($rekomendasi==5){
//         $pdf->Image($path, 16, $y4, 0);
//     }


//     $pdf->SetY($awalygaris);
//     $pdf->SetX(110);
//     $pdf->Cell(85, 10,"Catt./koreksi dari pimpinan atasan langsung : ", 'T', 1, 'L');
//     $pdf->SetX(110);
//     $pdf->MultiCell(80,5,$catatan,0,'J',0);
//     $pdf->Line(110, $awalygaris, 110, $akhirygaris);
//     $pdf->Line(195, $awalygaris, 195, $akhirygaris);
//     $pdf->Line(110, $akhirygaris, 195, $akhirygaris);

//     $pdf->SetY($akhirygaris);
//     $pdf->Ln(10);
//     $pdf->SetFont('Arial', '', 9);
//     $pdf->Cell(25, 5,"Jakarta, ".tanggalnormal($tglpersetujuan), 0, 1, 'J');

//     $pdf->SetX(15);
//     $pdf->SetFont('Arial', '', 9);
//     $pdf->Cell(50, 5,"Mengajukan,", 0, 0, 'J');
//     $pdf->Cell(50, 5,"Menyetujui,", 0, 0, 'J');
//     $pdf->Cell(50, 5,"Mengetahui,", 0, 1, 'J');
//     $y=$pdf->GetY();
//     $pdf->Ln(20);
//     if ($dir!=''){
//         $pdf->Image($dir, 15, $y, 0, 18);
//     }
//     if ($dir1!=''){
//         $pdf->Image($dir1, 65, $y, 0, 18);
//     }
//     if ($dir2!=''){
//         $pdf->Image($dir2, 115, $y, 0, 18);
//     }
//     if ($dir3!=''){
//         $pdf->Image($dir3, 165, $y, 0, 18);
//     }
//     $pdf->SetX(15);
//     $pdf->Cell(50, 5,$namalangsung, 0, 0, 'J');
//     $pdf->Cell(50, 5,$namaatasan, 0, 0, 'J');
//     $pdf->Cell(50, 5,$namahc, 0, 0, 'J');
//     $pdf->Cell(50, 5,$namahr, 0, 1, 'J');
//     $pdf->SetX(15);
//     $pdf->Cell(50, 5,$atasanlangsung, 0, 0, 'J');
//     $pdf->Cell(50, 5,$atasan, 0, 0, 'J');
//     $pdf->Cell(50, 5,$hc, 0, 0, 'J');
//     $pdf->Cell(50, 5,$hr, 0, 1, 'J');

//     $pdf->Ln(15);
//     $pdf->SetFont('Arial', 'B', 9);
//     $pdf->Cell(50, 5,"Keterangan : ", 0, 1, 'L');
//     $pdf->SetFont('Arial', '', 9);
//     $pdf->Cell(90, 5,"Skala penilaian : 1 s/d 5 (angka bulat)", 0, 0, 'L');
//     $pdf->SetFont('Arial', 'B', 9);
//     $pdf->Cell(50, 5,"** Kriteria Rekomendasi Atasan Langsung : ", 0, 1, 'L');
//     $pdf->SetFont('Arial', '', 9);
//     $pdf->Cell(5, 5,"1 ", 0, 0, 'L');
//     $pdf->Cell(85, 5,": Kurang", 0, 0, 'L');
//     $pdf->Cell(5, 5,"1. ", 0, 0, 'L');
//     $pdf->Cell(50, 5,"Untuk Sikap Kerja (Attitude) minimal 3.", 0, 1, 'L');
//     $pdf->Cell(5, 5,"2 ", 0, 0, 'L');
//     $pdf->Cell(85, 5,": Cukup", 0, 0, 'L');
//     $pdf->Cell(5, 5,"2. ", 0, 0, 'L');
//     $pdf->Cell(50, 5,"Untuk Penguasaan Pekerjaan (Job Mistery) minimal 3.", 0, 1, 'L');
//     $pdf->Cell(5, 5,"3 ", 0, 0, 'L');
//     $pdf->Cell(85, 5,": Baik", 0, 0, 'L');
//     $pdf->Cell(5, 5,"3. ", 0, 0, 'L');
//     $pdf->Cell(50, 5,"Untuk People Managament minimal 3.", 0, 1, 'L');
//     $pdf->Cell(5, 5,"4 ", 0, 0, 'L');
//     $pdf->Cell(85, 5,": Baik Sekali", 0, 1, 'L');
//     $pdf->Cell(5, 5,"5 ", 0, 0, 'L');
//     $pdf->Cell(85, 5,": Istimewa", 0, 1, 'L');
//     $pdf->Cell(100, 5,"Penjelasan mengenai skala penilaian, Dapat dilihat di Norma Penilaian", 0, 1, 'L');

    $pdf->Output();