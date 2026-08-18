<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

$tmp=explode(',',$_GET['column']);
$nofaktur=$tmp[0];

class PDF extends FPDF
{

        function Header()
        {
        }
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            //$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }

}
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whBrg);
$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

    $pdf=new PDF('P','mm','A4');
    $halaman=$pdf->PageNo();
    for($halaman=1;$halaman<=4;$halaman++)
    {

        $pdf->AddPage();
        $iHt="select * from ".$dbname.".pmn_faktur where nofaktur='".$nofaktur."' ";
        $nHt=$owlPDO->query($iHt) or die(print " Gagal: ".PDOException::getMessage());
        $nHt->setFetchMode(PDO::FETCH_ASSOC);
        $dHt=$nHt->fetch();
        $noKontrak = $dHt['nokontrak'];

        $qStatus = "SELECT b.statusberikat FROM ".$dbname.".pmn_kontrakjual a
                LEFT JOIN ".$dbname.".pmn_4customer b ON a.koderekanan=b.kodecustomer
                where a.nokontrak='".$noKontrak."'";
        $resStatus = fetchData($qStatus);
        $sBerikat = $resStatus[0]['statusberikat'];

        $height=5;
        $height2=6;
        $pdf->SetFont('Arial','',6); 
        $pdf->SetXY(130,10);

        if($halaman==1)
        {
            $isiLampiran="Lembar ke-1 : Untuk Pembelian BKP / Penerimaan JKP sebagai bukti";
        }
        else if($halaman==2)
        {
            $isiLampiran="Lembar ke-2 : Untuk Penjual BKP / Pemberi JKP sebagai bukti";
        }
        else if($halaman==3)
        {
            $isiLampiran="Lembar ke-3 : Untuk Kantor Pelayanan Pajak dalam hal";
        }
        else
        {
            $isiLampiran="Lembar ke-4 : Untuk Arsip";
        }

        $pdf->Cell(68,$height,$isiLampiran,RLT,0,'L');
        $pdf->Ln($height-2);
        $pdf->SetX(130);
                if($halaman==1)
        {
            $isiLampiran2='                       Pajak Masukan';
        }
        else if($halaman==2)
        {
            $isiLampiran2='                       Pajak Keluaran';
        }
        else if($halaman==3)
        {
            $isiLampiran2='                       Penyerahan BKP / JKP dilakukan';
        }
        else
        {
            $isiLampiran2='';
        }
        $pdf->Cell(68,$height,$isiLampiran2,'RL',0,'L');
        $pdf->Ln($height-2);
                if($halaman==1)
        {
            $isiLampiran3='';
        }
        else if($halaman==2)
        {
            $isiLampiran3='';
        }
        else if($halaman==3)
        {
            $isiLampiran3='                       kepada pemungut PPn';
        }
        else
        {
            $isiLampiran3='';
        }
        $pdf->SetX(130);
        $pdf->Cell(68,$height,$isiLampiran3,BRL,1,'L');


        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(180,$height,'FAKTUR PAJAK',0,1,'C');	

        $pdf->Ln();	

        $pdf->SetFont('Arial','',8); 
        $pdf->Cell(70,$height,'Kode dan Nomor Seri Faktur pajak',LTB,0,'L');
        $pdf->Cell(5,$height,':',TB,0,'L');
                if($sBerikat==1) {
                        $pdf->Cell(105,$height,'070.'.$dHt['nofaktur'],RTB,0,'L');
                } else {
                        $pdf->Cell(105,$height,'010.'.$dHt['nofaktur'],RTB,0,'L');
                }

        $pdf->Ln($height2);


        $namaPt=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
        $alamatPt= makeOption($dbname, 'setup_org_npwp', 'kodeorg,alamatnpwp');
        $npwp=  makeOption($dbname, 'setup_org_npwp', 'kodeorg,npwp');

        $pdf->SetFont('Arial','',8); 
        $pdf->Cell(180,$height,'PENGUSAHA KENA PAJAK',LTR,1,'L');
        $pdf->Cell(70,$height,'Nama',L,0,'L');
        $pdf->Cell(5,$height,':',0,0,'L');
        $pdf->Cell(105,$height,$namaPt[$dHt['kodept']],R,0,'L');
        $akhirXb=$pdf->GetX(); 
        $pdf->Ln();

        $akhirXa=$pdf->GetX();
        $pdf->Cell(70,$height,'Alamat',L,0,'L');
        $pdf->Cell(5,$height,':',0,0,'L');
        $akhirYa=$pdf->GetY();
        $pdf->MultiCell(105, $height, $alamatPt[$dHt['kodept']],0,'L');
        $akhirYb=$pdf->GetY();

        $pdf->Line($akhirXa, $akhirYa, $akhirXa, $akhirYb);
        $pdf->Line($akhirXb, $akhirYa, $akhirXb, $akhirYb);

        $pdf->Cell(70,$height,'N.P.W.P',LB,0,'L');
        $pdf->Cell(5,$height,':',B,0,'L');
        $pdf->Cell(105,$height,$npwp[$dHt['kodept']],RB,0,'L');

        $pdf->Ln($height2);

        $pembeli=  makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,koderekanan');
        $ppnKontrak=  makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,ppn');
        $namaPembeli=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
        $alamatPembeli=makeOption($dbname, 'pmn_4customer', 'kodecustomer,alamat');
        $alamatNpwp=makeOption($dbname, 'pmn_4customer', 'kodecustomer,alamatnpwp',"alamatnpwp is not null and alamatnpwp!=''");
        $npwpPembeli=makeOption($dbname, 'pmn_4customer', 'kodecustomer,npwp');

        $pdf->Cell(180,$height,'PEMBELI BARANG KENA PAJAK/PENERIMA JASA KENA PAJAK',LTR,1,'L');
        $pdf->Cell(70,$height,'Nama',L,0,'L');
        $pdf->Cell(5,$height,':',0,0,'L');
        $pdf->Cell(105,$height,$namaPembeli[$pembeli[$dHt['nokontrak']]],R,0,'L');
        $akhirXd=$pdf->GetX(); 
        $pdf->Ln();

        $akhirXc=$pdf->GetX();
        $pdf->Cell(70,$height,'Alamat',L,0,'L');
        $pdf->Cell(5,$height,':',0,0,'L');
        $akhirYc=$pdf->GetY();
        $alamatStr = isset($alamatNpwp[$pembeli[$dHt['nokontrak']]])? $alamatNpwp[$pembeli[$dHt['nokontrak']]]: $alamatPembeli[$pembeli[$dHt['nokontrak']]];
        $pdf->MultiCell(105, $height,$alamatStr,0,'L');
        $akhirYd=$pdf->GetY();

        $pdf->Line($akhirXc, $akhirYc, $akhirXc, $akhirYd);
        $pdf->Line($akhirXd, $akhirYc, $akhirXd, $akhirYd);

        //$npwpPembeli
        $pdf->Cell(70,$height,'N.P.W.P',LB,0,'L');
        $pdf->Cell(5,$height,':',B,0,'L');
        $pdf->Cell(105,$height,$npwpPembeli[$pembeli[$dHt['nokontrak']]],RB,0,'L');

        $pdf->Ln($height2);

                // Invoice saat ini
        $iInv="select * from ".$dbname.".keu_penagihanht where nokontrak='".$dHt['nokontrak']."' and noinvoice='".$dHt['noinvoice']."' ";
        $nInv=$owlPDO->query($iInv) or die(print " Gagal: ".PDOException::getMessage());
        $nInv->setFetchMode(PDO::FETCH_ASSOC);
        $dInv=$nInv->fetch();

        #buat cek penagihan keberapa
        $iCekInv="select count(*) as jumlah from ".$dbname.".keu_penagihanht where nokontrak='".$dHt['nokontrak']."' 
                and tanggal < '".$dInv['tanggal']."'";
        $nCekInv=$owlPDO->query($iCekInv) or die(print " Gagal: ".PDOException::getMessage());
        $nCekInv->setFetchMode(PDO::FETCH_ASSOC);
        $dCekInv=  $nCekInv->fetch();
            $jumlahInv=$dCekInv['jumlah'];

            if($jumlahInv<1)
            {
                $termin=makeOption($dbname,'pmn_5terminbayar','kode,satu');
            }
            else
            {
                $termin=makeOption($dbname,'pmn_5terminbayar','kode,dua');
            }


        $terminBayar=makeOption($dbname,'pmn_kontrakjual','nokontrak,kdtermin');
        $komoditi=  makeOption($dbname,'pmn_kontrakjual', 'nokontrak,kodebarang');
        $namaKomoditi= makeOption($dbname,'log_5masterbarang', 'kodebarang,namabarang','kelompokbarang=400');
        $qty=  makeOption($dbname,'pmn_kontrakjual', 'nokontrak,kuantitaskontrak');
        $harga=  makeOption($dbname,'pmn_kontrakjual', 'nokontrak,hargasatuan');
        $mataUang=  makeOption($dbname,'pmn_kontrakjual', 'nokontrak,matauang');
        $simbolMataUang=makeOption($dbname,'setup_matauang', 'kode,simbol');

        $substp=$dInv['jenis'];
                if($substp=='Jumlah Harga Jual'){
                        if($ppnKontrak[$dHt['nokontrak']]==1){
                                $jumlahRp = ($harga[$dHt['nokontrak']]*$qty[$dHt['nokontrak']]) / 1.1;
                        }else{
                                $jumlahRp = ($harga[$dHt['nokontrak']]*$qty[$dHt['nokontrak']]);
                        }
                }else{
                        $jumlahRp=$dInv['nilaiinvoice'];
                }

        if($ppnKontrak[$dHt['nokontrak']]==0){
                        $hargasatuan=$harga[$dHt['nokontrak']];
                }else{
                        $hargasatuan=$harga[$dHt['nokontrak']]/1.1;
                }


        $isi="Pembayaran ".$termin[$terminBayar[$dHt['nokontrak']]]."% untuk ".$namaKomoditi[$komoditi[$dHt['nokontrak']]]." ";
        $isi.="".number_format($dInv['kuantitas'])." KG ";

        if($mataUang[$dHt['nokontrak']]=='IDR')
        {
            $kursTampil='1';
        }
        else
        {
            $kursTampil=$dHt['kurs'];
        }

        $iFaktur="select * from ".$dbname.".pmn_faktur where nofaktur='".$nofaktur."' ";
        $nFaktur=$owlPDO->query($iFaktur) or die(print " Gagal: ".PDOException::getMessage());
        $nFaktur->setFetchMode(PDO::FETCH_ASSOC);
        $dFaktur=$nFaktur->fetch();

                $substp=$dInv['jenis'];
                if($substp=='Jumlah Harga Jual') {
                        // Potongan sebelumnya
                        $sPotS="select sum(rupiah1+rupiah2+rupiah3+rupiah4+rupiah5+rupiah6+rupiah7-rupiah8) as potongan,sum(rupiah8) as tambahan,noinvoice 
                                from ".$dbname.".keu_penagihanht where nokontrak='".$dHt['nokontrak']."' group by noinvoice";

                        $resPot = fetchData($sPotS);
                        $arrPot = array();
                        $potonganS = $potonganS1 = 0;
                        foreach($resPot as $row) {
                                if($dHt['noinvoice']!=$row['noinvoice']) {
                                        $arrPot[$row['noinvoice']] = $row['potongan'] - $row['tambahan'];
                                        $potonganS += $row['potongan']- $row['tambahan'];
                                        $potonganS1 += $row['potongan']- $row['tambahan'];
                                } else {
                                        $arrPot[$row['noinvoice']] -= $row['tambahan'];
                                        $potonganS -= $row['tambahan'];
                                }
                        }
                }

        $pdf->Cell(10,$height,'No.',TRL,0,'C');
        $pdf->Cell(100,$height,'Nama Barang Kena Pajak/',TRL,0,'C');
        $pdf->Cell(70,$height,'Harga Jual/Penggantian/Uang Muka/Termin',1,1,'C');
        $pdf->Cell(10,$height,'Urut',BRL,0,'C');
        $pdf->Cell(100,$height,'Jasa Kena Pajak',BRL,0,'C');
        $pdf->Cell(35,$height,'Valas*)',BRL,0,'C');
        $pdf->Cell(35,$height,'Rp.',BRL,1,'C');

        $pdf->Cell(10,$height,'1',TRL,0,'C');
        //$pdf->Cell(100,$height,'Pembayaran 90% untuk CPO 2M @Rp.6000',RTL,0,'L');
        $pdf->Cell(100,$height,$isi,RTL,0,'L');
        $pdf->Cell(35,$height,number_format(floatval($kursTampil),2),RL,0,'R');
        $pdf->Cell(35,$height,number_format(floatval($jumlahRp),2),RL,1,'R');


        $pdf->Cell(10,$height,'',RL,0,'C');
        $pdf->Cell(100,$height,"@ ".$simbolMataUang[$mataUang[$dHt['nokontrak']]]." ".number_format(floatval($hargasatuan),2),RL,0,'L');
        $pdf->Cell(35,$height,'',RL,0,'C');
        $pdf->Cell(35,$height,'',RL,1,'C');


        $pdf->Cell(10,$height,'',RL,0,'C');
        $pdf->Cell(100,$height,"No. Invoice : ".$dHt['noinvoice'],RL,0,'L');
        $pdf->Cell(35,$height,'',RL,0,'C');
        $pdf->Cell(35,$height,'',RL,1,'C');

        if(isset($arrPot) and !empty($arrPot)) {
                        $pdf->Cell(10,$height,'',RL,0,'C');
                        $pdf->Cell(100,$height,'',RL,0,'L');
                        $pdf->Cell(35,$height,'',RL,0,'C');
                        $pdf->Cell(35,$height,'',RL,1,'C');
                        foreach($arrPot as $inv=>$pot) {
                                if($pot!=0) {
                                        $txtPot = ($pot<0) ? number_format($pot*(-1),2): "(".number_format($pot,2).")";
                                        $pdf->Cell(10,$height,'',RL,0,'C');
                                        $pdf->Cell(100,$height,"Klaim Invoice: ".$inv,RL,0,'L');
                                        $pdf->Cell(35,$height,'',RL,0,'C');
                                        $pdf->Cell(35,$height,$txtPot,RL,1,'R');
                                }
                        }
                }

        $pdf->Cell(10,$height*10,'',BRL,0,'C');
        $pdf->Cell(100,$height*10,'',BRL,0,'LT');
        $pdf->Cell(35,$height*10,'',BRL,0,'C');
        $pdf->Cell(35,$height*10,'',BRL,1,'C');

        $pdf->Ln(-5);
        $pdf->Ln($height2);

        //echo $substp;
        if($substp=='Jumlah Harga Jual')
        {
            $pdf->Cell(26,$height,'Jumlah Harga Jual / ','LBT',0,'L');
            $x1=$pdf->GetX();
            $y1=$pdf->GetY()+2.5;
            $pdf->Cell(18,$height,'Penggantian / ','TB',0,'L');
            $pdf->Cell(17,$height,'Uang Muka / ','TB',0,'L');
            $pdf->Cell(20,$height,'Termin**','BT',0,'L');
            $x2=$pdf->GetX()-9;
            $y2=$pdf->GetY()+2.5;
            $pdf->Line($x1, $y1, $x2, $y2);
            $pdf->Cell(29,$height,'','RBT',0,'L');

        }
        else if ($substp=='Penggantian')
        {
            $x1=$pdf->GetX();
            $y1=$pdf->GetY()+2.5;
            $pdf->Cell(26,$height,'Jumlah Harga Jual / ','BLT',0,'L');
            $x2=$pdf->GetX();
            $y2=$pdf->GetY()+2.5;
            $pdf->Line($x1, $y1, $x2, $y2);
            $pdf->Cell(18,$height,'Penggantian / ','BT',0,'L');
             $x3=$pdf->GetX();
            $y3=$pdf->GetY()+2.5;
            $pdf->Cell(17,$height,'Uang Muka / ','BT',0,'L');
            $pdf->Cell(20,$height,'Termin**','BT',0,'L');
            $x4=$pdf->GetX()-9;
            $y4=$pdf->GetY()+2.5;
            $pdf->Line($x3, $y3, $x4, $y4);
            $pdf->Cell(29,$height,'','RBT',0,'L');
        }
        else if ($substp=='Uang Muka')
        {
            $x1=$pdf->GetX();
            $y1=$pdf->GetY()+3;
            $pdf->Cell(26,$height,'Jumlah Harga Jual / ','LBT',0,'L');
            $pdf->Cell(18,$height,'Penggantian / ','BT',0,'L');
            $x2=$pdf->GetX();
            $y2=$pdf->GetY()+2.5;
            $pdf->Line($x1, $y1, $x2, $y2);

            $pdf->Cell(17,$height,'Uang Muka / ','BT',0,'L');
            $x3=$pdf->GetX();
            $y3=$pdf->GetY()+3;
            $pdf->Cell(20,$height,'Termin**','BT',0,'L');
            $x4=$pdf->GetX()-9;
            $y4=$pdf->GetY()+2.5;
            $pdf->Line($x3, $y3, $x4, $y4);
            $pdf->Cell(29,$height,'','RBT',0,'L');
        }
        else//Termin
        {
            $x1=$pdf->GetX()+2;
            $y1=$pdf->GetY()+2.5;
            $pdf->Cell(26,$height,'Jumlah Harga Jual / ','LBT',0,'L');
            $pdf->Cell(18,$height,'Penggantian / ','BT',0,'L');
            $pdf->Cell(17,$height,'Uang Muka / ','BT',0,'L');
            $x2=$pdf->GetX();
            $y2=$pdf->GetY()+2.5;
            $pdf->Line($x1, $y1, $x2, $y2);
            $pdf->Cell(20,$height,'Termin**','BT',0,'L');
            $pdf->Cell(29,$height,'','RBT',0,'L');
        }
        //Penggantian
        //
        //
        $sPot="select sum(rupiah1+rupiah2+rupiah3+rupiah4+rupiah5+rupiah6+rupiah7) as potongan 
                        from ".$dbname.".keu_penagihanht where nokontrak='".$dHt['nokontrak']."' and noinvoice='".$dHt['noinvoice']."'";		
        $qPot=$owlPDO->query($sPot) or die(print " Gagal: ".PDOException::getMessage());
        $qPot->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$qPot->fetch();
        $potongan=$bar['potongan'];		

        if(isset($potonganS)) $jumlahRp -= $potonganS;

                //$pdf->Cell(110,$height,'Jumlah Harga Jual/Penggantian/Uang Muka/Termin**',1,0,'LT');
        $pdf->Cell(35,$height,'',1,0,'C');
        $pdf->Cell(35,$height,number_format($jumlahRp,2),1,0,'R');
        $pdf->Ln($height2);
        $pdf->Cell(110,$height,'Dikurangi Potongan Harga',1,0,'LT');
        $pdf->Cell(35,$height,'',1,0,'C');
        $pdf->Cell(35,$height,number_format($potongan,2),1,0,'R');
        $pdf->Ln($height2);
        $ppnInv=  makeOption($dbname, 'keu_penagihanht', 'noinvoice,nilaippn');

        $uangMuka=0;
        if($substp=='Jumlah Harga Jual')
        {
                        //ambil nilai invoice yang nokontrak dan noinvoice tidak sama dengan ini
                        $sPot="select sum(nilaiinvoice) as uangmuka 
                        from ".$dbname.".keu_penagihanht where nokontrak='".$dHt['nokontrak']."' and noinvoice!='".$dHt['noinvoice']."'";
                        $qPot=$owlPDO->query($sPot) or die(print " Gagal: ".PDOException::getMessage());
                        $qPot->setFetchMode(PDO::FETCH_ASSOC);
                        $bPot=$qPot->fetch();
                        $uangMuka=$bPot['uangmuka'];

                        if(isset($potonganS1)) $uangMuka -= $potonganS1;
                }
        $pdf->Cell(110,$height,'Dikurangi Uang Muka yang telah diterima',1,0,'LT');
        $pdf->Cell(35,$height,'',1,0,'C');
        $pdf->Cell(35,$height,number_format($uangMuka,2),1,0,'R');

        $pdf->Ln($height2);

        $pdf->Cell(110,$height,'Dasar Pengenaan Pajak',1,0,'LT');
        $pdf->Cell(35,$height,'',1,0,'C');
        $pdf->Cell(35,$height,number_format($jumlahRp-$uangMuka-$potongan,2),1,0,'R');

        $pdf->Ln($height2);

        //$jumlahPpn=$ppnInv[$dHt['noinvoice']]/100*$jumlahRp;
        $jumlahPpn=($jumlahRp-$uangMuka-$potongan)*10/100;


        $pdf->Cell(110,$height,"PPN = 10% Dasar Pengenaan Pajak",1,0,'LT');
        $pdf->Cell(35,$height,'',1,0,'C');
        $pdf->Cell(35,$height,number_format($jumlahPpn,2),1,0,'R');

                $tglInv = explode('-',$dInv['tanggal']);
        $tanggalHr=$tglInv[2];
        $bulanHr=$tglInv[1];
        $tahunHr=$tglInv[0];
        //($int,$lang='E',$format='short')
        $nmBulan=numToMonth($bulanHr,'I','long');


        $untukTtd=" Jakarta ".$tanggalHr." ".$nmBulan." ".$tahunHr." ";

        $pdf->Ln($height2);
        $pdf->Cell(180,$height,'',RLT,1,'L');
        $pdf->Cell(10,$height,'',RL,0,'L');
        $pdf->Cell(20,$height,'TARIF',1,0,'C');
        $pdf->Cell(40,$height,'DPP',1,0,'C');
        $pdf->Cell(40,$height,'PPn BM',1,0,'C');
        $pdf->Cell(70,$height,$untukTtd,R,1,'C');

        for($i=1;$i<=3;$i++)
        {
            $pdf->Cell(10,$height,'',RL,0,'L');
            $pdf->Cell(20,$height,'....%',1,0,'L');
            $pdf->Cell(40,$height,'Rp..........',1,0,'L');
            $pdf->Cell(40,$height,'Rp..........',1,0,'L');
            $pdf->Cell(70,$height,'',R,1,'C');
        }
        $pdf->Cell(10,$height,'',RL,0,'L');
        $pdf->Cell(20,$height,'....%',RLB,0,'L');
        $pdf->Cell(40,$height,'Rp..........',RLB,0,'L');
        $pdf->Cell(40,$height,'Rp..........',RLB,0,'L');
        $pdf->Cell(70,$height,'',R,1,'C');

        $pdf->Cell(10,$height,'',RL,0,'L');
        $pdf->Cell(20,$height,'',RLB,0,'L');
        $pdf->Cell(40,$height,'Total',RLB,0,'R');
        $pdf->Cell(40,$height,'Rp..........',RLB,0,'L');
        $pdf->SetFont('Arial','U',8);
        $pdf->Cell(70,$height,'Aswanda Kurniawan',R,1,'C');
        //$pdf->Cell(70,$height,'Rizki Daslia',R,1,'C');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(110,$height,'',L,0,'L');
        $pdf->Cell(70,$height,'Manager',R,1,'C');

        $pdf->Cell(180,$height,'Catatan :',RL,1,'L');
        $pdf->Cell(180,$height,'Kurs Rp. :',BRL,1,'L');

        $pdf->Cell(180,$height,'* Diisi apabila penyerahan menggunakan mata uang asing',0,1,'L');
        $pdf->Cell(180,$height,'** Coret yang tidak perlu',0,0,'L');

    }            
    $pdf->Output();
?>