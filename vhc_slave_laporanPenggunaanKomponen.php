<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$comId = checkPostGet('comId','');
$kdVhc = checkPostGet('kdVhc','');
$jenisVhc = checkPostGet('jenisVhc','');
$period = checkPostGet('period','');
$tglAwal = tanggalsystem(checkPostGet('tglAwal',''));
$tglAkhir = tanggalsystem(checkPostGet('tglAkhir',''));


$jenisVhcx=  makeOption($dbname, 'vhc_5master', 'kodevhc,jenisvhc');

$nmJenis=  makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
switch($proses){
        case'getKdvhc':
        $optOrg=makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
        $optKvhc="<option value=''>".$_SESSION['lang']['all']."</option>";
        if($comId!=''){
            $where.=" and kodetraksi='".$comId."'";
        }
        if($jenisVhc!=''){
            $where.=" and jenisvhc='".$jenisVhc."'";
        }
        $skdVhc="select distinct jenisvhc from ".$dbname.".vhc_5master where 1=1 ".$where." order by jenisvhc";
		// echo "warning:".$skdVhc;
        $qkdVhc=$owlPDO->query($skdVhc) or die(print " Gagal: ".PDOException::getMessage());
        $qkdVhc->setFetchMode(PDO::FETCH_ASSOC);
        while($rkdVhc=$qkdVhc->fetch()){
			$optKvhc.="<option value='".$rkdVhc['jenisvhc']."'>".$rkdVhc['jenisvhc']." - ".$optOrg[$rkdVhc['jenisvhc']]."</option>";
        }
        echo $optKvhc;
        break;
		case'getKdvhcx':
		$optKvhc="<option value=''>".$_SESSION['lang']['all']."</option>";
		$where=" and kodetraksi='".$comId."'";
		$skdVhc="select kodevhc from ".$dbname.".vhc_5master where jenisvhc='".$jenisVhc."' ".$where.""; //echo "warning:".$skdVhc;
		$qkdVhc=$owlPDO->query($skdVhc) or die(print " Gagal: ".PDOException::getMessage());
		$qkdVhc->setFetchMode(PDO::FETCH_ASSOC);
		while($rkdVhc=$qkdVhc->fetch()){
			$e="";
			if(getNopol($rkdVhc['kodevhc'])!=''){
				$e.= " - ".getNopol($rkdVhc['kodevhc']);
			}
			if(getNopol($rkdVhc['kodevhc'],'d')!=''){
				$e.= " - ".getNopol($rkdVhc['kodevhc'],'d');
			}
			
			$optKvhc.="<option value='".$rkdVhc['kodevhc']."'>".$rkdVhc['kodevhc'].$e."</option>";
		}
		echo $optKvhc;
		break;
        case'get_result':
            if($comId=='')
            {
                echo"warning:Working unit required";
                exit();
            }
            if($tglAkhir==''||$tglAwal=='')
            {
                echo"warning: Date required";
                exit();
            }
        echo"
			<table cellspacing=1 border=0 cellpadding=5 class=sortable>
			<thead>
			<tr class=rowheader>
				<th align=center>No.</th>
				<th align=center>".$_SESSION['lang']['notransaksi']."</th>
				<th align=center>".$_SESSION['lang']['noreferensi']."</th>
				<th align=center>".$_SESSION['lang']['tanggalmasuk']."</th>
				<th align=center>".$_SESSION['lang']['kodevhc']."</th>
				<th align=center>".$_SESSION['lang']['nopol']."</th>
				<th align=center>".$_SESSION['lang']['detail']."</th>
				<th align=center>".$_SESSION['lang']['jenisvch']." - ".$_SESSION['lang']['namajenisvhc']."</th>
				<th align=center width=50px>".$_SESSION['lang']['downtime']."</th>
				<th align=center>".$_SESSION['lang']['mekanik']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center>".$_SESSION['lang']['keterangan']."</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
            </tr>
        </thead>
        <tbody>";

        if($jenisVhc!='')
        {
         $where=" and kodevhc in (select distinct kodevhc from ".$dbname.".vhc_5master where jenisvhc='".$jenisVhc."' and kodetraksi='".$comId."')";   
         if($kdVhc!='')
        {
            $where=" and kodevhc='".$kdVhc."'";
        }
        }else{
         $where=" and kodevhc in (select distinct kodevhc from ".$dbname.".vhc_5master where kodetraksi='".$comId."')";   
        }
        $sql="select a.noreferensi,a.tanggal,a.kodevhc,a.downtime,a.posting,a.notransaksi,b.kodebarang,b.jumlah,b.satuan,b.keterangan from ".$dbname.".vhc_penggantianht a left join ".$dbname.".vhc_penggantiandt b on a.notransaksi=b.notransaksi 
            where a.kodeorg like '%".substr($comId, 0,4)."%' and a.tanggal between  '".$tglAwal."' and '".$tglAkhir."' ".$where."";
        $qRvhc=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($qRvhc);
        
        if($row>0){
            $qRvhc->setFetchMode(PDO::FETCH_ASSOC);
			$no=0;
            while($rRvhc=$qRvhc->fetch()){
				$no+=1;
				
				$iKar="select karyawanid from ".$dbname.".vhc_penggantiandt_karyawan where notransaksi='".$rRvhc['notransaksi']."' ";
				$nKar=fetchData($iKar);
				$isiKar=array();
				if(count($nKar)>0){					
					$x=0;$nama="";
					foreach($nKar as $dKar){
						$x++;
						$nama.=$x.". ".getNamaKaryawan($dKar['karyawanid'])."<br>";
					}
					$isiKar[$rRvhc['notransaksi']]=$nama;
				}
				
				
				echo"
					<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td>".$rRvhc['notransaksi']."</td>
					<td>".$rRvhc['noreferensi']."</td>
					<td>".tanggalnormal($rRvhc['tanggal'])."</td>
					<td>".$rRvhc['kodevhc']."</td>
					<td>".getNopol($rRvhc['kodevhc'])."</td>
					<td>".getNopol($rRvhc['kodevhc'],'d')."</td>
					<td>".$jenisVhcx[$rRvhc['kodevhc']].' - '.$nmJenis[$jenisVhcx[$rRvhc['kodevhc']]]."</td>
					<td align=right>".$rRvhc['downtime']."</td>";
				echo"<td>".$isiKar[$rRvhc['notransaksi']]."</td>
					<td>".$rRvhc['kodebarang']."</td>
					<td>".getNamaBrg($rRvhc['kodebarang'])."</td>
					<td>".$rRvhc['satuan']."</td>
					<td>".$rRvhc['jumlah']."</td>
					<td>".$rRvhc['keterangan']."</td>";
				echo"<td align=center>";
				$rRvhc['posting']=='1'?$imgt="<img src='images/buttongreen.png'  title='Sudah Posting' class='resicon'/>":$imgt="<img src='images/hot.png' title='Belum Posting' />";
				echo $imgt;
				echo"</td>";
				echo"
						</tr>
				";
			}
        }
        else
        {
                echo"<tr class=rowcontent align=center><td colspan=14>".$_SESSION['lang']['datanotfound']."</td></tr>";
        }
        echo"</tbody></table></div>";
        break;
        case'get_result_cari':
        $sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang=".$kdBrg."";
        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
        $rBrg=$qBrg->fetch();

        $sRvhc="select a.tanggal,a.kodevhc,b.* from ".$dbname.".vhc_penggantianht a inner join ".$dbname.".vhc_penggantiandt b on a.notransaksi=b.notransaksi where 
        b.kodebarang='".$kdBrg."' order by a.tanggal asc "; //echo "warning:".$sRvhc;
        $qRvhc=$owlPDO->query($sRvhc) or die(print " Gagal: ".PDOException::getMessage());
        $qRvhc->setFetchMode(PDO::FETCH_ASSOC);
        while($rRvhc=$qRvhc->fetch())
        {
            $no+=1;
            echo"
                <tr class=rowcontent>
                    <td>".$no."</td>
                    <td align=center>".$rRvhc['notransaksi']."</td>
                    <td align=center>".$rRvhc['kodevhc']."</td>
                    <td align=center>".tanggalnormal($rRvhc['tanggal'])."</td>
                    <td align=center >".$rRvhc['kodebarang']."</td>
                    <td align=center>".$rRvhc['satuan']."</td>
                    <td align=center>".$rRvhc['jumlah']."</td>
                    <td align=center>".$rRvhc['keterangan']."</td>
                </tr><input type=hidden id=kd_br name=kd_brg value=".$rRvhc['kodebarang']." />
                ";
        }
        break;
        
        case'getExcel':
            if($comId=='')
            {
                echo"warning: Working unit required";
                exit();
            }
            if($tglAkhir==''||$tglAwal=='')
            {
                echo"warning: Date required";
                exit();
            }
            $str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".substr($comId,0,4)."'";
            $namapt='COMPANY NAME';
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                $namapt=strtoupper($bar->namaorganisasi);
            }

            $stream="
            <table>
            <tr><td colspan=8 align=center>".strtoupper($_SESSION['lang']['laporanPenggunaanKomponen'])."</td></tr>";
            if($comId!='')
            {
                $stream.="<tr><td colspan=3>".$_SESSION['lang']['unit'].":".$namapt."</td></tr>";
            }
            if($kdVhc!='')
            {
                $stream.="<tr><td colspan=3>".$_SESSION['lang']['kodevhc'].":".$kdVhc."</td></tr>";
            }

                $stream.="<tr><td colspan=3>".$_SESSION['lang']['periode'].":".$_GET['tglAwal']."-".$_GET['tglAkhir']."</td></tr>";

            $stream.="<tr><td colspan=3>&nbsp;</td></tr>
            </table>
            <table border=1>
            <tr>
            <td bgcolor=#DEDEDE align=center>No.</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggalmasuk']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodevhc']." - ".$_SESSION['lang']['namajenisvhc']."</td>
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jenisvch']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['downtime']."</td>
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['mekanik']."</td>
                


            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namabarang']."</td>
            ";
            $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>	
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."</td>	
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</td>	
            </tr>";

        if($jenisVhc!='')
        {
         $where=" and kodevhc in (select distinct kodevhc from ".$dbname.".vhc_5master where jenisvhc='".$jenisVhc."' and kodetraksi='".$comId."')";   
         if($kdVhc!='')
        {
            $where=" and kodevhc='".$kdVhc."'";
        }
        }else{
         $where=" and kodevhc in (select distinct kodevhc from ".$dbname.".vhc_5master where kodetraksi='".$comId."')";   
        }
        $sql="select a.tanggal,a.kodevhc,a.downtime,a.posting,a.notransaksi,b.kodebarang,b.jumlah,b.satuan,b.keterangan from ".$dbname.".vhc_penggantianht a left join ".$dbname.".vhc_penggantiandt b on a.notransaksi=b.notransaksi 
            where a.kodeorg like '%".substr($comId, 0,4)."%' and a.tanggal between  '".$tglAwal."' and '".$tglAkhir."' ".$where."";
            $qRvhc=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
            $row=owlBaris($qRvhc);
            if($row>1)
            {
                $qRvhc->setFetchMode(PDO::FETCH_ASSOC);
                while($rRvhc=$qRvhc->fetch())
                {
                        $no+=1;
                        $sbrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rRvhc['kodebarang']."'";
                        $qbrg=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qbrg->setFetchMode(PDO::FETCH_ASSOC);
                        $rbrg=$qbrg->fetch();
                        
                        
                        $iKar="select a.karyawanid,b.namakaryawan from ".$dbname.".vhc_penggantiandt_karyawan a left join ".$dbname.".datakaryawan b"
                                . " on a.karyawanid=b.karyawanid where notransaksi='".$rRvhc['notransaksi']."' ";
                        
                        $nKar=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
                        $nKar->setFetchMode(PDO::FETCH_ASSOC);
                        $isiKar="";
                        $isiKar[$rRvhc['notransaksi']]=isset($isiKar[$rRvhc['notransaksi']])?$isiKar[$rRvhc['notransaksi']]:'';
                        while($dKar=$nKar->fetch())
                        {
                            $isiKar[$rRvhc['notransaksi']].="".$dKar['namakaryawan'].", ";
                        }
                        $stream.="	
                            <tr class=rowcontent>
                                <td>".$no."</td>
                                <td>".$rRvhc['notransaksi']."</td>
                                <td>".tanggalnormal($rRvhc['tanggal'])."</td>
                                <td>".$rRvhc['kodevhc']."</td>
                                    <td>".$jenisVhcx[$rRvhc['kodevhc']].' - '.$nmJenis[$jenisVhcx[$rRvhc['kodevhc']]]."</td>
                                <td align=right>".$rRvhc['downtime']."</td>


                                <td align=right>".$isiKar[$rRvhc['notransaksi']]."</td>

                                <td>".$rRvhc['kodebarang']."</td>
                                <td>".$rbrg['namabarang']."</td>
                                <td>".$rRvhc['satuan']."</td>
                                <td>".$rRvhc['jumlah']."</td>
                                <td>".$rRvhc['keterangan']."</td>
                            </tr>";
                }
            }
            else
            {
                $stream.="<tr class=rowcontent><td colspan=14>".$_SESSION['lang']['datanotfound']."</td></tr>";
            }

            $stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            $nop_="ReportComponentUsage_".$dte;
            $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                         gzwrite($gztralala, $stream);
                         gzclose($gztralala);
                         echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls.gz';
                            </script>";

        break;
         case 'pdf':
			if($comId=='')
            {
                echo"warning: Working unit required";
                exit();
            }
            if($tglAkhir==''||$tglAwal=='')
            {
                echo"warning: Date required";
                exit();
            }
        class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $comId;
                                global $kdVhc;
                                global $jenisVhc;
                                global $period;
                                global $tglAkhir;
                                global $tglAwal;

                # Alamat & No Telp
                $arrHead = setheadreport(substr($comId,0,4));
				
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(110);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(110); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(110); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
				
                $this->Ln();
                $this->SetFont('Arial','',8);
                                if($comId!='')
                                {
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$comId,'',0,'L');
                                }
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['user'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height, $_SESSION['standard']['username'],0,0,'L');
                $this->Ln();
                                if($comId!='')
                                {

                                $query2 = selectQuery($dbname,'organisasi','namaorganisasi',
                                "kodeorganisasi='".$comId."'");
                                $orgData2 = fetchData($query2);
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['unitkerja'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$orgData2[0]['namaorganisasi'],'',0,'L');
                                }
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,date('d-m-Y H:i:s'),'',1,'L');		

                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'].":".$_GET['tglAwal']."-".$_GET['tglAkhir'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$period,'',0,'L');


                $this->Ln();
                $this->SetFont('Arial','U',12);
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['laporanPenggunaanKomponen']),0,1,'C');	
                $this->Ln();	
                 $this->SetFont('Arial','',8);
                $this->SetFillColor(220,220,220);
                                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                                $this->Cell(15/100*$width,$height,$_SESSION['lang']['notransaksi'],1,0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
                                $this->Cell(13/100*$width,$height,$_SESSION['lang']['kodevhc'],1,0,'C',1);
                                $this->Cell(25/100*$width,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);
                                $this->Cell(6/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['jumlah'],1,0,'C',1);
                                $this->Cell(18/100*$width,$height,$_SESSION['lang']['keterangan'],1,1,'C',1);

            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
                $pdf->AddPage();

                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',7);	
		
		if($jenisVhc!='')
        {
         $where=" and kodevhc in (select distinct kodevhc from ".$dbname.".vhc_5master where jenisvhc='".$jenisVhc."' and kodetraksi='".$comId."')";   
         if($kdVhc!='')
        {
            $where=" and kodevhc='".$kdVhc."'";
        }
        }else{
         $where=" and kodevhc in (select distinct kodevhc from ".$dbname.".vhc_5master where kodetraksi='".$comId."')";   
        }
        $sql="select a.tanggal,a.kodevhc,a.downtime,a.posting,a.notransaksi,b.kodebarang,b.jumlah,b.satuan,b.keterangan from ".$dbname.".vhc_penggantianht a left join ".$dbname.".vhc_penggantiandt b on a.notransaksi=b.notransaksi 
            where a.kodeorg like '%".substr($comId, 0,4)."%' and a.tanggal between '".$tglAwal."' and '".$tglAkhir."' ".$where."";
        $qRvhc=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($qRvhc);
        if($row>1)
        {
                $no=0;
                $qRvhc->setFetchMode(PDO::FETCH_ASSOC);
                while($rRvhc=$qRvhc->fetch())
                {
                        $sbrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rRvhc['kodebarang']."'";
                        $qbrg=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qbrg->setFetchMode(PDO::FETCH_ASSOC);
                        $rbrg=$qbrg->fetch();
                        $no+=1;
                        $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                        $pdf->Cell(15/100*$width,$height,$rRvhc['notransaksi'],1,0,'L',1);
                        $pdf->Cell(10/100*$width,$height,tanggalnormal($rRvhc['tanggal']),1,0,'C',1);
                        $pdf->Cell(13/100*$width,$height,$rRvhc['kodevhc'],1,0,'L',1);
                        $pdf->Cell(25/100*$width,$height,$rbrg['namabarang'],1,0,'L',1);
                        $pdf->Cell(6/100*$width,$height,$rRvhc['satuan'],1,0,'C',1);
                        $pdf->Cell(10/100*$width,$height,$rRvhc['jumlah'],1,0,'R',1);
                        $pdf->Cell(18/100*$width,$height,$rRvhc['keterangan'],1,1,'L',1);

                }
        }
        else
        {
                $pdf->Cell(100/100*$width,$height,$_SESSION['lang']['datanotfound'],1,1,'C',1);
        }
        $pdf->Output();
        break;
        default:
        break;
}
?>