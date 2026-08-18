<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    include_once('lib/nangkoelib.php');
    include_once('lib/zLib.php');
    require_once('lib/fpdf.php');

    $proses=	!empty($_POST['proses'])? $_POST['proses']:$_GET['proses'];
    $pt=		!empty($_POST['pt'])? $_POST['pt']:$_GET['pt'];
    $unit=		!empty($_POST['unit'])? $_POST['unit']:$_GET['unit'];
    $kepada=	!empty($_POST['kepada'])? $_POST['kepada']:$_GET['kepada'];
    $tipe=		!empty($_POST['tipe'])? $_POST['tipe']:$_GET['tipe'];
    $tanggal=	!empty($_POST['tanggal'])? $_POST['tanggal']:$_GET['tanggal'];
    $sd=		!empty($_POST['sd'])? $_POST['sd']:$_GET['sd'];
    $tanggal=tanggalsystem($tanggal); 
    $tgldari=substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2);
    $sd=tanggalsystem($sd); 
    $tglsd=substr($sd,0,4).'-'.substr($sd,4,2).'-'.substr($sd,6,2);
    if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
        if(($tanggal=='')or($sd=='')){
                echo"Error: Date is obligatory."; exit;
        }
        if($tgldari>$tglsd){
                echo"Error: First date must smaller than the secon date."; exit;
        }
    }
    switch($proses){
    case 'load_unit_kpd':
        $opt_unit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $s_unit="select * from ".$dbname.".organisasi where induk='".$pt."' order by tipe, kodeorganisasi asc";
        $res=$owlPDO->query($s_unit) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($r_unit=$res->fetch()){
            $opt_unit.="<option value='".$r_unit['kodeorganisasi']."'>".$r_unit['kodeorganisasi']." - ".$r_unit['namaorganisasi']."</option>";  
            
        }
        echo $opt_unit;
        exit();	
    break;
    case 'load_kpd':
        $opt_kepada="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $s_kepada="select * from ".$dbname.".organisasi 
                where length(kodeorganisasi)=4 and kodeorganisasi != '".$unit."' 
                order by induk, namaorganisasi asc";
        $res=$owlPDO->query($s_kepada) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($r_kepada=$res->fetch()){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$r_kepada['kodeorganisasi']."'");
			$d=$induk[$r_kepada['kodeorganisasi']];
			if($d!=$n){			
				$opt_kepada.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}			
            $opt_kepada.="<option value='".$r_kepada['kodeorganisasi']."'>".$r_kepada['kodeorganisasi']." - ".$r_kepada['namaorganisasi']."</option>";
			
			$n=$d;
			if($d!=$n){			
				$opt_kepada.="</optgroup>";
			}
        }
        echo $opt_kepada;
        exit();
    break;
    }
    # Ambil akun r/k tujuan
    $listakun='(';
    $no=0;
    $s_rk = "select akunhutang,akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$kepada."'";
    $res=$owlPDO->query($s_rk) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($r_rk=$res->fetch()){
        $no+=1;
        $listakun.="'".$r_rk['akunhutang']."',";
        $listakun.="'".$r_rk['akunpiutang']."',";
    }
    $listakun=substr($listakun,0,-1);
    $listakun.=')';
    if($no==0)$listakun="('')";

    # Ambil transaksi
    if($tipe=='Kredit Note'){
        $kolom='kredit';
    }else{
        $kolom='debet';
    }


    /*
    $str = "select tanggal,noreferensi,keterangan,".$kolom." as kolom from ".$dbname.".keu_jurnaldt_vw 
                    where nojurnal in (select distinct nojurnal from ".$dbname.".keu_jurnaldt 
                    where noakun in ".$listakun." and tanggal between '".$tgldari."' and '".$tglsd."'
                    and kodeorg='".$unit."') and ".$kolom."!=0 ";
    */
    $str = "select * from ".$dbname.".keu_jurnaldt_vw 
                    where noakun in ".$listakun." and tanggal between '".$tgldari."' and '".$tglsd."'
                    and kodeorg='".$unit."'";

    if($proses=='excel'){
        $bg=" bgcolor=#DEDEDE";
        $brdr=1;
    }
    else{ 
        $bg="";
        $brdr=0;
    }

    if($proses=='excel'){
        $bgcoloraja="bgcolor=#DEDEDE ";
        $brdr=1;
        $stream="
        <table border=0 cellpadding=5>
        <tr><td align=center colspan=5><b>Laporan ".$_SESSION['lang']['debetkreditnote']."</b></td></tr>
        <tr>
            <td align=left>".$_SESSION['lang']['namapt']."</td>
            <td>:".$pt."</td>
            <td colspan=3 align=center>".$tipe."</td>
        </tr>
        <tr>
            <td align=left>".$_SESSION['lang']['unitkerja']."</td>
            <td colspan=4>:".$unit."</td>
        </tr>
        <tr>
            <td align=left>".$_SESSION['lang']['kepada']."</td>
            <td colspan=4>:".$kepada."</td>
        </tr>
        <tr>
            <td align=left>".$_SESSION['lang']['periode']."</td>
            <td colspan=4>:".substr($tanggal,6,2).'-'.substr($tanggal,4,2).'-'.substr($tanggal,0,4)." 
            s/d ".substr($sd,6,2).'-'.substr($sd,4,2).'-'.substr($sd,0,4)."</td>
        </tr><tr><td colspan=5></td></tr>
        </table>";
		$stream="<div style=overflow:auto; height:300px;>";
		$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$nmkpd	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kepada."'");
		$stream.="Laporan Debit Kredit Note<br>";
		$stream.="".$unit." - ".$nmorg[$unit]."<br>";
		$stream.="Kepada ".$kepada." - ".$nmkpd[$kepada]."<br>";
		$stream.="".tanggalnormal($tanggal)." s/d ".tanggalnormal($sd)."<br><br>";
    }

    $stream.="<table cellspacing='1'  cellpadding=5 border='".$brdr."' class='sortable'>
                <thead>
                    <tr class=rowheader>
                        <th align=center id=no>No.</th>
                        <th align=center id=tgl>".$_SESSION['lang']['tanggal']."</th>
                        <th align=center id=noref>".$_SESSION['lang']['noreferensi']."</th>
                        <th align=center id=noref>".$_SESSION['lang']['nojurnal']."</th>
                        <th align=center id=noref>".$_SESSION['lang']['kodejurnal']."</th>
                        <th align=center id=ket>".$_SESSION['lang']['keterangan']."</th>    
                        <th align=center id=kolom>".$_SESSION['lang']['jumlah']."</th>
                    </tr>
                </thead>
            <tbody>";
    $no=$jumlah=0;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch())
    {
        $no++;
        $stream.="<tr class=rowcontent>
                    <td align=center id=no>".$no."</td>
                    <td align=center id=tgl>".$bar['tanggal']."</td>
                    <td align=left id=noref>".$bar['nojurnal']."</td>
                    <td align=left id=noref>".$bar['noreferensi']."</td>
                    <td align=left id=noref>".$bar['kodejurnal']."</td>
                    <td align=left id=ket>".$bar['keterangan']."</td>
                    <td align=right id=kolom>".number_format($bar['jumlah'],2)."</td>
                </tr>";
        // $jumlah+=$r_transaksi['kolom'];
        @$jumlah+=$bar['jumlah'];
    }//      <td align=right id=kolom>".number_format($r_transaksi['kolom'],2)."</td>
    $stream.="<tr class=rowcontent><td colspan=6 align=center><b>".$_SESSION['lang']['jumlah']."</b></td>
            <td><b>".number_format($jumlah,2)."</b></td></tr>";
    $stream.="</tbody></table>";

    switch($proses){
    case 'preview':
        echo $stream;
    break;
    case 'excel':   
        $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
        $dte=date("YmdHis");
        $nop_="DebetKreditNote_".$kepada;
        if(strlen($stream)>0)
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
            if(!fwrite($handle,$stream))
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
    case'pdf':
    class PDF extends FPDF
    {
        function Header() 
        {
            global $conn;
            global $dbname;
            global $align;
            global $length;
            global $colArr;
                global $pt;
                global $unit;
                global $kepada;
                global $tanggal;
                global $sd;
                global $tipe;
                global $owlPDO;

            $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
            $orgData = fetchData($query);

            $width = $this->w - $this->lMargin - $this->rMargin;
            $height = 10;

            $this->SetFont('Arial','B',8);
            $this->SetFillColor(255,255,255);	
            $s_pt = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
            $res=$owlPDO->query($s_pt) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $r_pt = $res->fetch();
            $s_unit = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$unit."'";
            $res=$owlPDO->query($s_unit) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $r_unit = $res->fetch();
            $s_kpd = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kepada."'";
            $res=$owlPDO->query($s_kpd) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $r_kpd = $res->fetch();
            $this->Ln();
            $this->SetFont('Arial','B',8);		
            $this->SetX(30);  
            $this->Cell(450,$height,$r_pt['namaorganisasi'],'',1,'R',1);
            
            $this->SetFont('Arial','',7);
            $this->SetX(30);  
            $this->Cell(28,$height,($_SESSION['lang']['namapt']),'',0,'L',1);
            $this->Cell(5,$height,":",'',0,'C',1);
            $this->Cell(200,$height,$r_pt['namaorganisasi'],'',0,'L',1);
            $this->Cell(157,$height,$tipe,'',1,'L',1);
            
            $this->SetX(30); 
            $this->Cell(28,$height,($_SESSION['lang']['unit']),'',0,'L',1);
            $this->Cell(5,$height,":",'',0,'C',1);
            $this->Cell(200,$height,$r_unit['namaorganisasi'],'',0,'L',1);
            $this->Cell(157,$height,'','',1,'R',1);
            
            $this->SetX(30); 
            $this->Cell(28,$height,($_SESSION['lang']['kepada']),'',0,'L',1);
            $this->Cell(5,$height,":",'',0,'C',1);
            $this->Cell(200,$height,$r_kpd['namaorganisasi'],'',0,'L',1);
            $this->Cell(157,$height,'','',1,'R',1);
            
            $this->SetX(30); 
            $this->Cell(28,$height,ucfirst($_SESSION['lang']['periode']),'',0,'L',1);
            $this->Cell(5,$height,":",'',0,'C',1);
            $this->Cell(200,$height,substr($tanggal,6,2)."-".substr($tanggal,4,2)."-".substr($tanggal,0,4)." s/d ".
                substr($sd,6,2)."-".substr($sd,4,2)."-".substr($sd,0,4),'B',0,'L',1);
            $this->Cell(157,$height,'','',1,'R',1);
            
            $this->SetFont('Arial','',7);
            $this->SetFillColor(220,220,220);
            $this->SetX(30); 
            $this->Cell(20,$height,"No.",1,0,'C',1);	
            $this->Cell(50,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);		
            $this->Cell(120,$height,$_SESSION['lang']['noreferensi'],1,0,'C',1);	
            $this->Cell(270,$height,$_SESSION['lang']['keterangan'],1,0,'C',1);		
            $this->Cell(70,$height,$_SESSION['lang']['jumlah'],1,1,'C',1);
    }
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }
    }
    //================================
    $pdf=new PDF('P','pt','A4');
    $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
    $height = 10;
    $pdf->AddPage();
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Arial','',7);
    $no=0;
    $res=$owlPDO->query($s_transaksi) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($r_transaksi = $res->fetch()){
        $no++;
        $pdf->SetX(30);
        $pdf->Cell(20,$height,$no,1,0,'C',1);		
        $pdf->Cell(50,$height,$r_transaksi['tanggal'],1,0,'C',1);	
        $pdf->Cell(120,$height,$r_transaksi['noreferensi'],1,0,'L',1);		
        $pdf->Cell(270,$height,$r_transaksi['keterangan'],11,0,'L',1);
        $pdf->Cell(70,$height,number_format($r_transaksi['kolom'],2),1,1,'R',1);
    }
    $pdf->SetX(30);
    $pdf->Cell(460,$height,$_SESSION['lang']['jumlah'],1,0,'C',1);	
    $pdf->Cell(70,$height,number_format($jumlah,2),1,1,'R',1);	
    $pdf->Ln();$pdf->Ln();
    $pdf->SetX(30);
    // $pdf->Cell(20,$height,'CC:','TL',0,'L',1);
    // $pdf->Cell(180,$height,'','TR',0,'L',1);
    $pdf->Cell(16/100*$width,$height,'',0,0,'C',1);	
    $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['dibuat'],1,0,'C',1);	
    $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['diperiksa'],1,0,'C',1);		
    $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['disetujui'],1,1,'C',1);

    $pdf->SetX(30);
    // $pdf->Cell(20,$height,'','L',0,'L',1);
    // $pdf->Cell(180,$height,'- Accounting HO','R',0,'L',1);	
    $pdf->Cell(16/100*$width,$height,'',0,0,'C',1);	
    $pdf->Cell(20/100*$width,$height,'','TLR',0,'C',1);	
    $pdf->Cell(20/100*$width,$height,'','TLR',0,'C',1);		
    $pdf->Cell(20/100*$width,$height,'','TLR',1,'C',1);

    $pdf->SetX(30);
    // $pdf->Cell(20,$height,'','L',0,'L',1);
    // $pdf->Cell(180,$height,'- Arsip','R',0,'L',1);
    $pdf->Cell(16/100*$width,$height,'',0,0,'C',1);	
    $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);	
    $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);		
    $pdf->Cell(20/100*$width,$height,'','LR',1,'C',1);

    $pdf->SetX(30);
    // $pdf->Cell(20,$height,'','L',0,'L',1);
    // $pdf->Cell(180,$height,'','R',0,'L',1);
    $pdf->Cell(16/100*$width,$height,'',0,0,'C',1);
    $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);	
    $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);		
    $pdf->Cell(20/100*$width,$height,'','LR',1,'C',1);

    $pdf->SetX(30);
    // $pdf->Cell(20,$height,'','L',0,'L',1);
    // $pdf->Cell(180,$height,'','R',0,'L',1);
    $pdf->Cell(16/100*$width,$height,'',0,0,'C',1);
    $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
    $pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);		
    $pdf->Cell(20/100*$width,$height,'','LR',1,'C',1);


    $pdf->SetX(30);
    // $pdf->Cell(20,$height,'','BL',0,'L',1);
    // $pdf->Cell(180,$height,'','BR',0,'L',1);
    $pdf->Cell(16/100*$width,$height,'',0,0,'C',1);
    $pdf->Cell(20/100*$width,$height,$_SESSION['empl']['name'],1,0,'C',1);	
    $pdf->Cell(20/100*$width,$height,'KTU',1,0,'C',1);		
    $pdf->Cell(20/100*$width,$height,'ROA',1,1,'C',1);

    $pdf->Output();
    break;
    default:
    break;	
    }
?>