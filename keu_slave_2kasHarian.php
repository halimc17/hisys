<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/biReport.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

$level = $_GET['level'];
if(isset($_GET['mode'])) {
    $mode = $_GET['mode'];
} else {
    $mode = 'preview';
}
if($mode=='pdf') {
    $param = $_GET;
    unset($param['mode']);
    unset($param['level']);
} else {
    $param = $_POST;
}


if($param['noakun']=='1110101' and $param['bank']==''){
	exit("Warning:Bank masih kosong");
}

if($param['noakun']!='1110101' and $param['bank']!=''){
	exit("Warning:Harap kosongkan pilihan bank");
}

if($param['bank']!=''){
	$whbank=" and rekening='".$param['bank']."'";
}





// Kode Organisasi
if(!isset($param['kodeorg'])) $param['kodeorg']=$_SESSION['empl']['lokasitugas'];

# Validasi Periode
$tglhi = date('d-m-Y');

$periode1 = $param['periode_from'];
$periode2 = $param['periode_until'];
#1. Empty
if($periode1=='' or $periode2=='') {
    echo 'Warning : Transaction period required';
    exit;
}
#2. Range Terbalik
if(tanggalsystem($periode1)>tanggalsystem($periode2)) {
    $tmp = $periode1;
    $periode1 = $periode2;
    $periode2 = $tmp;
}

//ambil namaunit
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
$namagudang='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $namagudang=strtoupper($bar->namaorganisasi);
}

//ambil nama manager
$str1="select namakaryawan, kodejabatan from ".$dbname.".datakaryawan where kodejabatan='18' and lokasitugas ='".$param['kodeorg']."' and tanggalkeluar='0000-00-00'";
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $namamillmgr=$bar->namakaryawan;
        $jabatanmillmgr=$bar->kodejabatan;
}

//ambil nama KTU
$str1="select namakaryawan, kodejabatan from ".$dbname.".datakaryawan where kodejabatan='21' and lokasitugas ='".$param['kodeorg']."' and tanggalkeluar='0000-00-00'";
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $namaktu=$bar->namakaryawan;
        $jabatanktu=$bar->kodejabatan;
}

$namajabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');

switch($level) {
    case '0':
        # Data
        # Range Akun Kas
        $whereAKB = "kodeaplikasi='GL' and aktif=1 and jurnalid in ('KK','KM')";
        $queryAKB = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,sampaidebet',$whereAKB);
        $optAKB = fetchData($queryAKB);
        $mulaidebet = $optAKB[0]['noakundebet'];
        $sampaidebet = $optAKB[0]['sampaidebet'];
        if($optAKB[1]['noakundebet']<$mulaidebet or $mulaidebet=='') {
            $mulaidebet = $optAKB[1]['noakundebet'];
        }
        if($optAKB[1]['sampaidebet']>$sampaidebet) {
            $sampaidebet = $optAKB[1]['sampaidebet'];
        }
        #jika no akun dipilih(tidak seluruhnya)
        if($param['noakun']!=0)
        {
           $mulaidebet= $param['noakun'];
           $sampaidebet=$param['noakun'];
        }

        # Header Kas Bank
        if(isset($param['kodeorg']))
            $kodeorg=$param['kodeorg'];
        else
            $kodeorg=$_SESSION['empl']['lokasitugas'];

        $cols = "noakun,notransaksi,tipetransaksi,tanggal,jumlah,keterangan,rekening";
        $where = "tanggal>='".tanggalsystem($periode1)."' and tanggal<='".
            tanggalsystem($periode2)."' and noakun>='".$mulaidebet.
            "' and noakun<='".$sampaidebet."' and ".
            "kodeorg='".$kodeorg."' and posting=1 ".$whbank." ";
			#= indra lepas posting nanti
        $query = selectQuery($dbname,'keu_kasbankht',$cols,$where,"tanggal, notransaksi asc");
        $resH = fetchData($query);
        if(empty($resH)) {
            // echo 'Warning : No data found';
            // exit;
        }

        # Summary Saldo sebelumnya
        $persbl=substr(tanggalsystem($periode1),0,6);
        $cols2 = "sum(jumlah) as jumlah,tipetransaksi";
        $where2 = "tanggal<'".tanggalsystem($periode1)."' and tanggal>='".$persbl."01"."' 
            and noakun>='".$mulaidebet.
            "' and noakun<='".$sampaidebet."' and ".
            "kodeorg='".$kodeorg."' and posting=1 ".$whbank." group by tipetransaksi";
        $query2 = selectQuery($dbname,'keu_kasbankht',$cols2,$where2,"tanggal,notransaksi asc");
        $saldoAwal=0;
        $res=$owlPDO->query($query2) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            if($bar->tipetransaksi=='M')
                $saldoAwal+=$bar->jumlah;
            else
                 $saldoAwal-=$bar->jumlah;
        }

       $query1 = "select b.jumlah, b.tipetransaksi from ".$dbname.".keu_kasbankdt b 
                      left join ".$dbname.".keu_kasbankht a on b.notransaksi=a.notransaksi
                      where b.noakun = '".$param['noakun']."' and b.kodeorg='".$kodeorg."' and
                      a.tanggal>='".$persbl."01"."' and a.tanggal<'".tanggalsystem($periode1)."' ".$whbank." order by a.tanggal, a.notransaksi asc ";
        $res=$owlPDO->query($query1) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            if($bar->tipetransaksi=='K')
                $saldoAwal+=$bar->jumlah;
            else
                $saldoAwal-=$bar->jumlah;
        }           

        #ambil saldo akhir bl lalu
        $thnawl=substr($persbl,0,4);
        $blnawl=substr($persbl,4,2);
		if($param['bank']!='') {
			$queryx="select sum(awal".$blnawl.") as jumlah from ".$dbname.".keu_saldobank where norek='".$param['bank']."' 
			and kodeorg='".$kodeorg."' and periode='".$persbl."'";
		} else {
			$queryx="select sum(awal".$blnawl.") as jumlah from ".$dbname.".keu_saldobulanan where noakun>='".$mulaidebet.
            "' and noakun<='".$sampaidebet."' and ".
            "kodeorg='".$kodeorg."' and periode='".$persbl."'";
		}
        $res=$owlPDO->query($queryx) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $saldoAwal+=$bar->jumlah;
        }
        # Rearrange Data & Total
        $saldoKK=0;$saldoKM=0;
        $data = array();
        foreach($resH as $key=>$row) {
            $data[$key] = array(
                'no'=>$key+1,
                'tanggal'=>tanggalnormal($row['tanggal']),
                'notransaksi'=>$row['notransaksi'],
                'keterangan'=>$row['keterangan'],
                'saldokm'=>'',
                'saldokk'=>'',
                'saldoakhir'=>''
            );
            if($row['tipetransaksi']=='K') {
                $data[$key]['saldokk'] = $row['jumlah'];
                $saldoKK+=$row['jumlah'];
            } else {
                $data[$key]['saldokm'] = $row['jumlah'];
                $saldoKM+=$row['jumlah'];
            }
            $z=$key;
        }

   // paling terakhir                
   $query1 = "select b.jumlah, b.tipetransaksi,b.keterangan2,b.notransaksi,a.tanggal from ".$dbname.".keu_kasbankdt b 
              left join ".$dbname.".keu_kasbankht a on b.notransaksi=a.notransaksi
              where b.noakun = '".$param['noakun']."' and b.kodeorg='".$kodeorg."' and
              a.tanggal>='".tanggalsystem($periode1)."' and a.tanggal<='".tanggalsystem($periode2)."' and posting=1 order by a.tanggal, a.notransaksi asc";
    $resH1 = fetchData($query1);
            foreach($resH1 as $key=>$row) {
                $z+=1;
            $data[$z] = array(
                'no'=>$z+1,
                'tanggal'=>tanggalnormal($row['tanggal']),
                'notransaksi'=>$row['notransaksi'],
                'keterangan'=>$row['keterangan2'],
                'saldokm'=>'',
                'saldokk'=>'',
                'saldoakhir'=>''
            );
            if($row['tipetransaksi']=='M') {
                $data[$z]['saldokk'] = $row['jumlah'];
                $saldoKK+=$row['jumlah'];
            } else {
                $data[$z]['saldokm'] = $row['jumlah'];
                $saldoKM+=$row['jumlah'];
            }

        }

if(!empty($data)) foreach($data as $c=>$key) {
    $sort_tangg[] = $key['tanggal'];
    $sort_debet[] = $key['saldokm'];
}

// sort
if(!empty($data))array_multisort($sort_tangg, SORT_ASC, $sort_debet, SORT_DESC, $data);        

        $dataShow = $data;
        $dataExcel = $data;
		
		foreach($dataShow as $key=>$row) {
                if($mode=='pdf') {
                        $row['saldokk']!='' ? $dataShow[$key]['saldokk'] = $row['saldokk'] : null;
                        $row['saldokm']!='' ? $dataShow[$key]['saldokm'] = $row['saldokm'] : null;
                }else{
                        $row['saldokk']!='' ? $dataShow[$key]['saldokk'] = $row['saldokk'] : null;
                        $row['saldokm']!='' ? $dataShow[$key]['saldokm'] = $row['saldokm'] : null;
                }
        }

        # Report Gen
        $theCols = array(
            $_SESSION['lang']['nourut'],
            $_SESSION['lang']['tanggal'],
            $_SESSION['lang']['notransaksi'],
            $_SESSION['lang']['keterangan'],
            $_SESSION['lang']['penerimaan'],
            $_SESSION['lang']['pengeluaran'],
            $_SESSION['lang']['saldoakhir'],
        );
        $align = explode(",",",C,C,L,R,R,R,R");

        # Total for not excel
        if($mode!='excel') {
                if($mode=='pdf') {
                        //$saldoTerbilang = $saldoKM-$saldoKK;
                        $saldoTerbilang =$saldoKM +$saldoAwal-$saldoKK;
                        $saldoSelisih = $saldoKM +$saldoAwal-$saldoKK;
                        //tambahan jamhari
                        $saldoKM = $saldoKM;
                        $saldoAwal =$saldoAwal;
                        //end tambahan
                        $saldoKK =$saldoKK;
                }else{//html
                        //$saldoTerbilang = $saldoKM-$saldoKK;
                        $saldoTerbilang = $saldoKM +$saldoAwal-$saldoKK;
                        $saldoSelisih = $saldoKM +$saldoAwal-$saldoKK;
                        //tambahan jamhari
                        $saldoKM = $saldoKM;
                        $saldoAwal = $saldoAwal;
                        //end tambahan
                        $saldoKK = $saldoKK;
                }


        } else {
            $saldoSelisih = $saldoKM +$saldoAwal - $saldoKK;
                $saldoKM = ($saldoKM+$saldoAwal);
                $saldoAwal = $saldoAwal;
                $saldoKK = $saldoKK;
        }
        break;
    default:
    break;
}

$tmpdata = $dataShow;
$sldAkhr = 0;
foreach($tmpdata as $key=>$row){
	if($key == 0){
		$dataShow[$key]['saldoakhir'] = $saldoAwal - $dataShow[$key]['saldokk'] + $dataShow[$key]['saldokm'];
		$sldAkhr = $saldoAwal - $dataShow[$key]['saldokk'] + $dataShow[$key]['saldokm'];
	}else{
		$dataShow[$key]['saldoakhir'] = $sldAkhr - $dataShow[$key]['saldokk'] + $dataShow[$key]['saldokm'];
		$sldAkhr = $sldAkhr - $dataShow[$key]['saldokk'] + $dataShow[$key]['saldokm'];
	}
}

$dataExcel = $dataShow;

switch($mode) {
    case 'pdf':
        /** Report Prep **/
        # Options
        $optJab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',
            "kodejabatan='".$_SESSION['empl']['kodejabatan']."'");

        $colPdf = array('nourut','tanggal','notransaksi','keterangan','penerimaan',
            'pengeluaran','saldoakhir');
        $title = $_SESSION['lang']['kasharian'];
        $length = explode(",","5,10,15,30,14,14,14");

        $pdf = new zPdfMaster('P','pt','A4');
        $pdf->setAttr1($title,$align,$length,$colPdf);
                $pdf->_finReport = true;
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
                $pdf->AddPage();

        $pdf->SetFillColor(255,255,255);

        # Saldo Awal
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($length[0]/100*$width,$height,'','TLR',0,'C',1);
        $pdf->Cell($length[1]/100*$width,$height,'','TLR',0,'C',1);        
        $pdf->Cell($length[2]/100*$width,$height,'','TLR',0,'C',1);        
        $pdf->Cell($length[3]/100*$width,$height,'Saldo Awal '.$periode1,'TLR',0,'C',1);
        $pdf->Cell($length[4]/100*$width,$height,'','TLR',0,'R',1);
        $pdf->Cell($length[5]/100*$width,$height,'','TLR',0,'R',1);
        $pdf->Cell($length[6]/100*$width,$height,@number_format($saldoAwal),'TLR',0,'R',1);
        $pdf->Ln();

        # Content
        $pdf->SetFont('Arial','',9);
        // foreach($dataShow as $key=>$row) {
            // $i=0;
            foreach($dataShow as $head=>$cont) {

                $nourut+=1;
                                $height=12;
                                $awalY=$pdf->GetY();
								$pdf->SetY($awalY);
                                $pdf->SetX(1000);
                                $pdf->MultiCell($length[2]/100*$width,$height,$cont['notransaksi'],0,'C');
                                $akhirYNotransaksi=$pdf->GetY();
								
                                $pdf->SetY($awalY);
                                $pdf->SetX(1000);
                                $pdf->MultiCell($length[3]/100*$width,$height,$cont['keterangan'],0,'L');
                                $akhirYKeterangan=$pdf->GetY();

                                // $pdf->SetY($awalY);
                                // $pdf->SetX(1000);
                                // $pdf->MultiCell($length[3]/100*$width,$height,$cont['km'],0,'C');
                                // $akhirYKm=$pdf->GetY();

                                // $pdf->SetY($awalY);
                                // $pdf->SetX(1000);
                                // $pdf->MultiCell($length[5]/100*$width,$height,$cont['kk'],0,'C');
                                // $akhirYKk=$pdf->GetY();

                                $akhirY = max($akhirYNotransaksi,$akhirYKeterangan);
                                $height2=$akhirY-$awalY;
                                $pdf->SetY($awalY);
                                // echo $akhirYKeterangan."__".$akhirYKm."__".$akhirYKk."__".$akhirY."__".$awalY."__".$height2."<br>";

                                $currentX=$pdf->GetX();
                $pdf->Cell($length[0]/100*$width,$height2,"",'LR',0,'R');
                $pdf->Cell($length[1]/100*$width,$height2,"",'LR',0,'C');
                $pdf->Cell($length[2]/100*$width,$height2,"",'LR',0,'L');
                $pdf->Cell($length[3]/100*$width,$height2,"",'LR',0,'R');
                $pdf->Cell($length[4]/100*$width,$height2,"",'LR',0,'R');
                $pdf->Cell($length[5]/100*$width,$height2,"",'LR',0,'R');
                $pdf->Cell($length[6]/100*$width,$height2,"",'LR',0,'R');

                                $pdf->SetY($awalY);
                                $pdf->SetX($currentX);
                                $pdf->MultiCell($length[0]/100*$width,$height,$nourut,0,'R');
                                
								$pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[0]/100*$width));
                                $currentX=$pdf->GetX();
                                $pdf->MultiCell($length[1]/100*$width,$height,$cont['tanggal'],0,'C');
								
								$pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[1]/100*$width));
                                $currentX=$pdf->GetX();
                                $pdf->MultiCell($length[2]/100*$width,$height,$cont['notransaksi'],0,'C');
                                
								$pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[2]/100*$width));
                                $currentX=$pdf->GetX();
                                $pdf->MultiCell($length[3]/100*$width,$height,$cont['keterangan'],0,'J');
                                
								$pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[3]/100*$width));
                                $currentX=$pdf->GetX();
                                $pdf->MultiCell($length[4]/100*$width,$height, @number_format($cont['saldokm']),0,'R');
                                
								$pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[4]/100*$width));
                                $currentX=$pdf->GetX();
                                $pdf->MultiCell($length[5]/100*$width,$height,  @number_format($cont['saldokk']),0,'R');
								
								$pdf->SetY($awalY);
                                $pdf->SetX($currentX+($length[5]/100*$width));
                                $currentX=$pdf->GetX();
                                $pdf->MultiCell($length[6]/100*$width,$height,  @number_format($cont['saldoakhir']),0,'R');
								
                                $pdf->Ln($height2-$height);

                                if($pdf->GetY()>770)
                                                $pdf->AddPage();
                                // $i++;
            }
        // }

        $lenJudul = $length[0]+$length[1]+$length[2]+$length[3];
        # Total
        $pdf->Cell($lenJudul/100*$width,$height,$_SESSION['lang']['jumlah'],'TLR',0,'C',1);
        $pdf->Cell($length[4]/100*$width,$height,number_format($saldoKM),'TLR',0,$align[4],1);
        $pdf->Cell($length[5]/100*$width,$height,number_format($saldoKK),'TLR',0,$align[4],1);
        $pdf->Cell($length[6]/100*$width,$height,number_format($saldoSelisih),'TLR',0,$align[5],1);
        $pdf->Ln();
        # Saldo
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($lenJudul/100*$width,$height,'','LRB',0,'C',1);
        $pdf->Cell($length[4]/100*$width,$height,'','LRB',0,$align[3],1);
        $pdf->Cell($length[5]/100*$width,$height,'','LRB',0,$align[4],1);
        $pdf->Cell($length[6]/100*$width,$height,'','LRB',0,$align[5],1);
        $pdf->Ln();
        # Jumlah
        /*$pdf->Cell($lenJudul/100*$width,$height,$_SESSION['lang']['jumlah'],'LR',0,'C',1);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell($length[4]/100*$width,$height,$saldoKM,'BLR',0,$align[3],1);
        $pdf->Cell($length[5]/100*$width,$height,'','BLR',0,$align[4],1);
        $pdf->Cell($length[6]/100*$width,$height,$saldoKM,'BLR',0,$align[5],1);
        $pdf->Ln();
        $pdf->Cell($lenJudul/100*$width,$height,'','L',0,$align[4],1);
        $pdf->Cell((100-$lenJudul)/100*$width,$height,'','TR',0,$align[4],1);

         */
        //$pdf->Ln();
		
		$width = 549;
        # Terbilang
        $pdf->SetFont('Arial','I',9);
        $pdf->MultiCell($width,$height,
            'Terbilang : [ '.terbilang($saldoTerbilang,0)." rupiah. ]",'LR','L');
        $pdf->Cell($width,$height,'','LR',0,$align[4],0);
        $pdf->Ln();

        # Tempat, Tanggal
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(2/3*$width,$height,'','L',0,'R',0);
        $pdf->Cell(1/3*$width,$height,$_SESSION['empl']['lokasitugas'].' , '.$tglhi,'R',0,'C',0);
        $pdf->Ln();

        # Mengetahui dll
        $pdf->Cell(1/3*$width,$height,$_SESSION['lang']['mengetahui'],'L',0,'C',0);
        $pdf->Cell(1/3*$width,$height,$_SESSION['lang']['diperiksa'],0,0,'C',0);
        $pdf->Cell(1/3*$width,$height,$_SESSION['lang']['dibuat'],'R',0,'C',0);
        $pdf->Ln();

        # Add few line
        $pdf->Cell($width,$height,'','LR',1,$align[4],0);
        $pdf->Cell($width,$height,'','LR',1,$align[4],0);
        $pdf->Cell($width,$height,'','LR',1,$align[4],0);

		if($namamillmgr!='' || $namaktu!='' || $jabatanmillmgr !='' || $jabatanktu!=''){
			
        # Nama
        $pdf->SetFont('Arial','BU',9);
        $pdf->Cell(1/3*$width,$height,$namamillmgr,'L',0,'C',0);
        $pdf->Cell(1/3*$width,$height,$namaktu,'',0,'C',0);
        $pdf->Cell(1/3*$width,$height,$_SESSION['empl']['name'],'R',0,'C',0);
        $pdf->Ln();

        # Jabatan
        $pdf->SetFont('Arial','I',9);
        $pdf->Cell(1/3*$width,$height,$namajabatan[$jabatanmillmgr],'LB',0,'C',0);
        $pdf->Cell(1/3*$width,$height,$namajabatan[$jabatanktu],'B',0,'C',0);
        $pdf->Cell(1/3*$width,$height,$optJab[$_SESSION['empl']['kodejabatan']],'RB',0,'C',0);
		
		}
		else{
			
		
		 # Nama
        $pdf->SetFont('Arial','BU',9);
        $pdf->Cell(1/3*$width,$height,'                  ','L',0,'C',0);
        $pdf->Cell(1/3*$width,$height,'                  ','',0,'C',0);
        $pdf->Cell(1/3*$width,$height,$_SESSION['empl']['name'],'R',0,'C',0);
        $pdf->Ln();

        # Jabatan
        $pdf->SetFont('Arial','I',9);
        $pdf->Cell(1/3*$width,$height,'','LB',0,'C',0);
        $pdf->Cell(1/3*$width,$height,'','B',0,'C',0);
        $pdf->Cell(1/3*$width,$height,$optJab[$_SESSION['empl']['kodejabatan']],'RB',0,'C',0);
		
		}
        
		$pdf->Output();
        break;
    default:
        # Redefine Align
        $alignPrev = array();
        foreach($align as $key=>$row) {
            switch($row) {
                case 'L':
                    $alignPrev[$key] = 'left';
                    break;
                case 'R':
                    $alignPrev[$key] = 'right';
                    break;
                case 'C':
                    $alignPrev[$key] = 'center';
                    break;
                default:
            }
        }

        /** Mode Header **/
        if($mode=='excel') {
            //tambahan jamhari
        // $sald=intval($saldoKM+$saldoAwal);
        // $saldoKM=number_format($sald,0);
        //end tambahan
            $tab = strtoupper($_SESSION['lang']['kasharian'])." : ".$namagudang."<br>".
        strtoupper($_SESSION['lang']['noakun'])." : ".$param['noakun']."<br>".
        strtoupper($_SESSION['lang']['periode'])." : ".$periode1." s/d ".$periode2.
        "<table border='1'>";
            $tab .= "<thead style=\"background-color:#222222\"><tr class='rowheader'>";
        } else {
            $tab = "<table id='kasharian' class='sortable'>";
            $tab .= "<thead><tr class='rowheader'>";
        }

        /** Generate Table **/
        foreach($theCols as $head) {
            $tab .= "<td align=center>".$head."</td>";
        }
        $tab .= "</tr></thead>";
        $tab .= "<tbody>";

        # Saldo Awal
        $tab .= "<tr class='rowcontent'>";
        $tab .= "<td></td><td align='center' colspan=3>Saldo Awal ".$periode1."</td>";
        $tab .= "<td></td><td></td><td align='right'>".number_format($saldoAwal,2)."</td>";
        $tab .= "</tr>";

        # Content




        foreach($data as $key=>$row) {
            $tab .= "<tr class='rowcontent'>";
            $i=0;
             @$nourut++;


            foreach($row as $head=>$cont) {

                if($mode=='excel') {

                        if($i==4 || $i==5 || $i==6){
                                $tab .= "<td align='".$alignPrev[$i]."'>".number_format(floatval($dataExcel[$key][$head]),2)."</td>";
                        }
                        else if ($i==0)
                        {
                            $tab .= "<td align='".$alignPrev[$i]."'>".$nourut."</td>";
                        }
                        else{
                                $tab .= "<td align='".$alignPrev[$i]."'>".$dataExcel[$key][$head]."</td>";
                        }

                } else {


                        if($head=='saldokm' || $head=='saldokk' || $head=='saldoakhir')
                        {
                            $tab .= "<td align='".$alignPrev[$i]."'>".($dataShow[$key][$head] != '' ? number_format($dataShow[$key][$head],2) : 0)."</td>";
                        }
                        else if ($i==0)
                        {
                            $tab .= "<td align='".$alignPrev[$i]."'>".$nourut."</td>";
                        }
                        else
                        {
                            $tab .= "<td align='".$alignPrev[$i]."'>".$dataShow[$key][$head]."</td>";
                        }

                }
                $i++;
            }
            $tab .= "</tr>";
        }

        # Grand Total

        $tab .= "<tr class='rowcontent'>";
        $tab .= "<td colspan='4' align='center'>".$_SESSION['lang']['jumlah']."</td>";
        $tab .= "<td align='right'>".number_format($saldoKM,2)."</td>";
        $tab .= "<td align='right'>".number_format($saldoKK,2)."</td>
			<td><b>".number_format($saldoSelisih,2)."</b></td>";
        $tab .= "</tr>";
        # Saldo
        // $tab .= "<tr class='rowcontent'>";
        // $tab .= "<td colspan='4' align='center'>".$_SESSION['lang']['saldo']."</td>";
        // $tab .= "<td align='right'></td>";
        // $tab .= "<td align='right'><b>".number_format($saldoSelisih,2)."</b></td><td></td>";
        // $tab .= "</tr>";
        # Jumlah
        /*$tab .= "<tr class='rowcontent'>";
        $tab .= "<td colspan='4' align='center'>".$_SESSION['lang']['jumlah']."</td>";
        $tab .= "<td align='right'>".number_format($saldoKM,2)."</td><td></td>";
        $tab .= "<td align='right'>".number_format($saldoKM,2)."</td>";
        $tab .= "</tr>";*/
        $tab .= "</tbody>";
        $tab .= "</table>";

        /** Output Type **/
        if($mode=='excel') {
            $stream = $tab;

            $nop_="KasHarian_".$kodeorg;
            if(strlen($stream)>0) {
                # Delete if exist
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/'.$file);
                        }
                    }	
                    closedir($handle);
                }

                # Write to File
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
                    echo "Error : Tidak bisa menulis ke format excel";
                    exit;
                } else {
                    echo $nop_;
                }
                fclose($handle);
            }
        } else {
            echo $tab;
        }
        break;
}
?>