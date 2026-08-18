<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$proses  = checkPostGet('proses','');
$lksiTgs = $_SESSION['empl']['lokasitugas'];
$kdeOrg  = checkPostGet('kdeOrg','');
$kdOrg   = checkPostGet('kdOrg','');
$tgl1    = tanggalsystem(checkPostGet('tgl1',''));
$tgl2    = tanggalsystem(checkPostGet('tgl2',''));
$tgl_1   = tanggalsystem(checkPostGet('tgl_1',''));
$tgl_2   = tanggalsystem(checkPostGet('tgl_2',''));
$periode = checkPostGet('periode','');
$kdUnit  = checkPostGet('kdUnit','');
$pilihan = checkPostGet('pilihan','');
$pilihan2= checkPostGet('pilihan2','');
$pilihan3= checkPostGet('pilihan3','');

$periodeGaji=$periode;
$periode=explode('-',$periode);
$total=0;
if(!$kdOrg)$kdOrg=$_SESSION['empl']['lokasitugas'];

function dates_inbetween($date1, $date2){
    $day = 60*60*24;
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between
    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);
    for($x = 1; $x < $days_diff; $x++){
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }
    $dates_array[] = date('Y-m-d',$date2);
    return $dates_array;
}

if(($tgl_1!='')&&($tgl_2!='')){	
	$tgl1=$tgl_1;
	$tgl2=$tgl_2;
}
$test = dates_inbetween($tgl1, $tgl2);




// get namaorganisasi =========================================================================
        $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kdOrg."' ";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while($rOrg=$qOrg->fetch())
        {
                $nmOrg=$rOrg['namaorganisasi'];
        }
        if(!$nmOrg)$nmOrg=$kdOrg;
        //ambil where untuk data karyawan
        if($kdOrg!='')
        {
                $kodeOrg=$kdOrg;
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
                {
                        $where=" and lokasitugas = '".$kodeOrg."'";
                        $where2=" and substr(kodeorg,1,4)='".$kdOrg."'";
                }
                else
                {
                        if(strlen($kdOrg)>4)
                        {
                                $where=" and subbagian='".$kdOrg."'";
                                $where2=" and kodeorg='".$kdOrg."'";
                        }
                        else
                        {
                                $where=" and lokasitugas='".$kdOrg."'";
                                $where2=" and substr(kodeorg,1,4)='".$kdOrg."'";
                        }
                }
        }
        else
        {
                $kodeOrg=$_SESSION['empl']['lokasitugas'];
                $where=" and lokasitugas='".$kodeOrg."'";
        }
//echo $sKebun.' '.$where; exit;

// pilihan 2
if($pilihan2=='semua'){
        $where3 = '';
}else
if($pilihan2=='bulanan'){
        $where3 = ' and a.sistemgaji = \'Bulanan\' ';
}else
if($pilihan2=='harian'){
        $where3 = ' and a.sistemgaji = \'Harian\' ';
}

// pilihan 3
if($pilihan3=='semua')
        $where4 = '';
else
        $where4 = " and a.bagian = '".$pilihan3."' ";
// building array: jabatan =========================================================================	
         $strJ="select * from ".$dbname.".sdm_5jabatan";
		 $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
	     $resJ->setFetchMode(PDO::FETCH_OBJ);
        while($barJ=$resJ->fetch())
        {
                $jab[$barJ->kodejabatan]=$barJ->namajabatan;
        }
// building array: bagian =========================================================================	
         $strJ="select * from ".$dbname.".sdm_5departemen";
		 $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
		 $resJ->setFetchMode(PDO::FETCH_OBJ);
        while($barJ=$resJ->fetch())
        {
                $bag[$barJ->kode]=$barJ->nama;
        }
        $dzArr=array();
        $tot=array();
        $total=0;
// ambil data lembur konversi ================================================================================
        $resData=array();
        $sGetLembur="select jamaktual, jamlembur,tipelembur from ".$dbname.".sdm_5lembur where kodeorg = '".$kodeOrg."'";
        $rGetLembur=fetchData($sGetLembur);
        foreach($rGetLembur as $row => $kar)
        {
            $GetLembur[$kar['jamaktual']][$kar['tipelembur']]=$kar['jamlembur'];
        }  
		
$whrtgl=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tgl1."')";		
// ambil data lembur ================================================================================
if($pilihan=='rupiah'){
     $sPeople="SELECT a.subbagian,b.karyawanid as karyawanid, b.tanggal as tanggal, b.uangkelebihanjam as uangkelebihanjam, a.nik, a.namakaryawan, a.bagian, a.kodejabatan,a.lokasitugas
                          FROM ".$dbname.".sdm_lemburdt b
                          LEFT JOIN ".$dbname.".datakaryawan a on a.karyawanid = b.karyawanid
                          WHERE b.tanggal between  '".$tgl1."' and '".$tgl2."' ".$where2." ".$where3." ".$where4." and b.uangkelebihanjam>0 order by a.subbagian asc,a.namakaryawan asc";
}else{
        $dakarbulanan=0;
        $str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' ".$where." and periodegaji='".$periodeGaji."' "; #exit('error'.$str);
        $res = fetchdata($str);
        if(count($res)>0)
        { 
        $dakarbulanan=1;
        }
        
        if($dakarbulanan==0){
                $sPeople="SELECT a.subbagian,b.karyawanid as karyawanid, b.tanggal as tanggal, b.jamaktual as uangkelebihanjam , b.tipelembur, a.nik, a.namakaryawan, a.bagian, a.kodejabatan,a.lokasitugas
                                FROM ".$dbname.".sdm_lemburdt b 
                                LEFT JOIN ".$dbname.".datakaryawan a on a.karyawanid = b.karyawanid
                                WHERE b.tanggal between  '".$tgl1."' and '".$tgl2."' ".$where2." ".$where3." ".$where4." ".$whrtgl." and b.jamaktual>0 order by a.subbagian asc,a.namakaryawan asc";
        }else{
                $sPeople="SELECT a.subbagian,b.karyawanid as karyawanid, b.tanggal as tanggal, b.jamaktual as uangkelebihanjam , b.tipelembur, a.nik, a.namakaryawan, a.bagian, a.kodejabatan,a.lokasitugas
                FROM ".$dbname.".sdm_lemburdt b 
                LEFT JOIN ".$dbname.".datakaryawan_hist a on a.karyawanid = b.karyawanid
                WHERE b.tanggal between  '".$tgl1."' and '".$tgl2."' ".$where2." ".$where3." ".$where4." ".$whrtgl." and approval_status='8' and version_type='B'  and periodegaji='".$periodeGaji."' and b.jamaktual>0 order by a.subbagian asc,a.namakaryawan asc";
        }
}
// echo $sPeople; exit;

//exit("Error".$sPeople);
		$query=$owlPDO->query($sPeople) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$query->fetch())
        {
        $dzArr[$res['karyawanid']]['id']=$res['karyawanid'];
        if(($res['subbagian']=='')||is_null($res['subbagian'])){
            $res['subbagian']=$res['lokasitugas'];
        }
        $dzArr[$res['karyawanid']]['sb']=$res['subbagian'];

        $dzArr[$res['karyawanid']]['nm']=$res['namakaryawan'];
        $dzArr[$res['karyawanid']]['bg']=$res['nik'];
        $dzArr[$res['karyawanid']]['jb']=$jab[$res['kodejabatan']];
        $dzArr[$res['karyawanid']][$res['karyawanid']]=$res['karyawanid'];
        
        if($pilihan!='jam_lembur'){
                    $dzArr[$res['karyawanid']][$res['tanggal']]=$res['uangkelebihanjam']; 

                }else{
                    $dzArr[$res['karyawanid']][$res['tanggal']]=$GetLembur[$res['uangkelebihanjam']][$res['tipelembur']];

                } 
        }
        
        
        //klo ada server busy di sini pasti karena tidak filter kodeorg $kdOrg
        $iGaji="select jumlah,karyawanid from ".$dbname.".sdm_5gajipokok where tahun='".substr($periodeGaji,0,4)."' "
                        . " and idkomponen=1";
		$nGaji=$owlPDO->query($iGaji) or die(print " Gagal: ".PDOException::getMessage());
		$nGaji->setFetchMode(PDO::FETCH_ASSOC);
        while($dGaji=  $nGaji->fetch())
        {
            $gajiPokok[$dGaji['karyawanid']]=$dGaji['jumlah'];
        }
        
        


switch ($proses) {
case 'preview': //exit("Error:ASD");

    if ($periodeGaji == '') {
        echo "warning: Period required";
        exit();
    }
// preview: nampilin header ================================================================================
	echo "<table cellspacing='1' cellpadding=5 border='0' style='width:100%;' class='sortable'  >
	<thead class=rowheader>
	<tr>
	<td align=center>No</td>
	<td align=center>".$_SESSION['lang']['subbagian']."</td>
	<td align=center>".$_SESSION['lang']['nik']."</td>
	<td align=center>".$_SESSION['lang']['nama']."</td>
	<td align=center>".$_SESSION['lang']['jabatan']."</td>";
	foreach($test as $ar => $isi) {
		$qwe = date('D', strtotime($isi));
		echo "<td width=5px align=center>";
		if ($qwe == 'Sun')
			echo "<font color=red>".substr($isi, 8, 2)."</font>";
		else
			echo(substr($isi, 8, 2));
		echo "</td>";
	}
	echo "<td align=center>".$_SESSION['lang']['jumlah']."</td>";
	if ($pilihan == 'rupiah') {
		echo "<td align=center>".$_SESSION['lang']['gaji']."</td>
		<td align=center width=90px>".$_SESSION['lang']['persen']." ".$_SESSION['lang']['lembur1']." (".$_SESSION['lang']['jumlah']." / ".$_SESSION['lang']['gaji'].")</td>";
	}
	echo "</tr></thead>
	<tbody>";
// preview: nampilin data ================================================================================
	foreach($dzArr as $idkar => $qwe) {
		$no += 1;
		echo "<tr class=rowcontent><td align=center>".$no."</td>
		<td>".$qwe['sb']."</td>
		<td>".$qwe['bg']."</td>
		<td>".$qwe['nm']."</td>
		<td>".$qwe['jb']."</td>";
		$zxc = 0;
			foreach($test as $ar => $isi) {
				setIt($qwe[$isi], 0);
				if ($qwe[$isi] != 0) {
					if ($pilihan == 'rupiah')
						echo "<td align=right>".number_format($qwe[$isi])."</td>";
					else
						$noid = "";
						$noid = makeOption($dbname, 'sdm_lemburht', 'kodeorg,id',"kodeorg='".$qwe['sb']."' and tanggal='".$isi."'");
						echo "<td align=right style=color:blue;cursor:pointer; onclick=showupload('".$noid[$qwe['sb']]."','".$qwe['sb']."','".$isi."'); title=\"Click untuk melihat file upload.\">".$qwe[$isi]."</td>";
				} else
					echo "<td align=right></td>";
				setIt($asd[$isi], 0);
				$zxc += $qwe[$isi];
				$asd[$isi] += $qwe[$isi];
			}
			if ($pilihan == 'rupiah') {
				echo "<td align=right>".number_format($zxc)."</td>";
				echo "<td align=right>".number_format($gajiPokok[$idkar])."</td>";
				$persen =  @ ($zxc / $gajiPokok[$idkar] * 100);
				echo "<td align=right>".number_format($persen, 2)."</td>";
			} else {
				echo "<td align=right>".$zxc."</td>";
			}

			echo "</tr>";
        }
// preview: nampilin total ================================================================================
        echo "<thead class=rowheader>
        <tr>
        <td colspan=5>".$_SESSION['lang']['total']."</td>";
        foreach($test as $ar => $isi) {
            setIt($asd[$isi], 0);
            if ($pilihan == 'rupiah')
                echo "<td align=right>".number_format($asd[$isi])."</td>";
            else
                echo "<td align=right>".$asd[$isi]."</td>";
            $total += $asd[$isi];
        }
        if ($pilihan == 'rupiah')
            echo "<td align=right>".number_format($total)."</td>";
        else
            echo "<td align=right>".$total."</td>";
        if ($pilihan == 'rupiah') {
            echo "<td></td><td></td>";
        }
        echo "</tbody></table>";
	break;
	case'pdf':
	if ($periodeGaji == '') {
		echo "warning: period required";
		exit();
	}
//create Header

class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $period;
                                global $periode;
                                global $kdOrg;
                                global $kdeOrg;
                                global $tgl1;
                                global $tgl2;
                                global $where;
                                global $jmlHari;
                                global $test;
                                global $nmOrg;
                                global $pilihan;
                                global $pilihan2;

                                $jmlHari=$jmlHari*1.5;
                                $cols=247.5;
                            # Alamat & No Telp
                $arrHead = setheadreport(substr($kdOrg,0,4));
				
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

                $this->SetFont('Arial','B',10);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanLembur']." (dalam ".$pilihan.") ".$pilihan2,'',0,'L');
                                $this->Ln();
                                $this->Cell($width,$height,strtoupper("Rekapitulasi Lembur Karyawan")." : ".$nmOrg,'',0,'C');
                                $this->Ln();
                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2),'',0,'C');
                                $this->Ln();
                $this->SetFont('Arial','B',7);
                $this->SetFillColor(220,220,220);
                                $this->Cell(2/100*$width,$height,'No',1,0,'C',1);   
                                $this->Cell(5/100*$width,$height,'NIK',1,0,'C',1);
                                $this->Cell(5/100*$width,$height,$_SESSION['lang']['nama'],1,0,'C',1);		
                                $this->Cell(5/100*$width,$height,$_SESSION['lang']['jabatan'],1,0,'C',1);	
                                foreach($test as $ar => $isi)
                                {
                                        $this->Cell(2.6/100*$width,$height,substr($isi,8,2),1,0,'C',1);	
                                        $akhirX=$this->GetX();
                                }	
                                $this->SetY($this->GetY());
                                $this->SetX($akhirX);
                                $this->Cell(4/100*$width,$height,$_SESSION['lang']['jumlah'],1,1,'C',1);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','Legal');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                if($pilihan=='rupiah')$pdf->SetFont('Arial','',5); else $pdf->SetFont('Arial','',7);

        foreach($dzArr as $qwe)
        {
                $no+=1;
                $pdf->Cell(2/100*$width,$height,$no,1,0,'C',1); 
                $pdf->Cell(5/100*$width,$height,$qwe['bg'],1,0,'L',1);
                $pdf->Cell(5/100*$width,$height,$qwe['nm'],1,0,'L',1);		
                $pdf->Cell(5/100*$width,$height,$qwe['jb'],1,0,'L',1);	
                $zxc=0;
                foreach($test as $ar => $isi)
                {
					setIt($qwe[$isi],0);
					setIt($asd[$isi],0);
					if($qwe[$isi]!=0){
							if($pilihan=='rupiah')$pdf->Cell(2.6/100*$width,$height,number_format($qwe[$isi]),1,0,'R',1); 
									else $pdf->Cell(2.6/100*$width,$height,number_format($qwe[$isi],2),1,0,'R',1);
					} else $pdf->Cell(2.6/100*$width,$height,'',1,0,'R',1);
					$zxc+=$qwe[$isi];
					$asd[$isi]+=$qwe[$isi];
					$akhirX=$pdf->GetX();
                }
//		if($pilihan=='rupiah')$pdf->Cell(4/100*$width,$height,number_format($qwe['tt']),1,1,'R',1);
//			else $pdf->Cell(4/100*$width,$height,number_format($qwe['tt'],1),1,1,'R',1);
                if($pilihan=='rupiah')$pdf->Cell(4/100*$width,$height,number_format($zxc),1,1,'R',1);
                        else $pdf->Cell(4/100*$width,$height,number_format($zxc,2),1,1,'R',1);
        }
                $pdf->Cell(17/100*$width,$height,"Total",1,0,'C',1);
                foreach($test as $ar => $isi)
                {
//					if($pilihan=='rupiah')$pdf->Cell(2.6/100*$width,$height,number_format($tot[$isi]),1,0,'R',1);
//						else $pdf->Cell(2.6/100*$width,$height,number_format($tot[$isi],1),1,0,'R',1);
//					$total+=$tot[$isi];	
                                        if($pilihan=='rupiah')$pdf->Cell(2.6/100*$width,$height,number_format($asd[$isi]),1,0,'R',1);
                                                else $pdf->Cell(2.6/100*$width,$height,number_format($asd[$isi],2),1,0,'R',1);
                                        $total+=$asd[$isi];	
                                        $akhirX=$pdf->GetX();
                }
                if($pilihan=='rupiah')$pdf->Cell(4/100*$width,$height,number_format($total),1,1,'R',1);
                        else $pdf->Cell(4/100*$width,$height,number_format($total,2),1,1,'R',1);


/*
        foreach($resData as $hslBrs => $hslAkhir)
        {	
                        if($hslAkhir[0]!='')
                        {
                                $no+=1;
                                $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                                $pdf->Cell(13/100*$width,$height,strtoupper($namakar[$hslAkhir[0]]),1,0,'L',1);		
                                $pdf->Cell(10/100*$width,$height,strtoupper($nmJabatan[$hslAkhir[0]]),1,0,'L',1);	
                                foreach($test as $barisTgl =>$isiTgl)
                                {
                                        $pdf->Cell(1.5/100*$width,$height,$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],1,0,'C',1);	
                                        $akhirX=$pdf->GetX();
                                        $brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
                                }
                                $a=0;
                                for(;$a<$lmit;$a++)
                                {
                                        $pdf->Cell(2/100*$width,$height,$brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']],1,0,'C',1);	
                                        $subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']];
                                }	
                                $pdf->Cell(5/100*$width,$height,$subtot[$hslAkhir[0]]['total'],1,1,'R',1);
                                $subtot[$hslAkhir[0]]['total']=0;
                        }	

        }
*/
        $pdf->Output();
        break;

        case'excel':
            if($periodeGaji=='')
        {
                echo"warning: Periode tidak boleh kosong";
                exit();
        }
        $colatas=count($test)+8;
        $stream.="<table border='0'>
		<tr>
		<td colspan='".$colatas."' align=center>".strtoupper("Overtime Recapitulation")." : ".$nmOrg." (dalam ".$pilihan.") ".$pilihan2."</td>
		</tr>
        <tr>
		<td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d ". tanggalnormal($tgl2)."</td>
		</tr></table>";

        $stream.="<table cellspacing='1' border='1' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td bgcolor=#DEDEDE>No</td>
        
        <td bgcolor=#DEDEDE>".$_SESSION['lang']['subbagian']."</td>
        <td bgcolor=#DEDEDE>NIK</td>
        <td bgcolor=#DEDEDE>".$_SESSION['lang']['nama']."</td>
        <td bgcolor=#DEDEDE>".$_SESSION['lang']['jabatan']."</td>
        ";
        foreach($test as $ar => $isi)
        {
                $qwe=date('D', strtotime($isi));
                $stream.="<td bgcolor=#DEDEDE width=5px align=center>";
                if($qwe=='Sun')$stream.="<font color=red>".substr($isi,8,2)."</font>"; else $stream.=(substr($isi,8,2)); 
                $stream.="</td>";
        }
        $stream.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['jumlah']."</td>";
        
        if($pilihan=='rupiah')
        {
           // $stream.="indra";
            $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['gaji']."</td>"
                    . "<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['persen']." ".$_SESSION['lang']['lembur1']." (".$_SESSION['lang']['jumlah']." / ".$_SESSION['lang']['gaji'].")</td>";
        }
            

        $stream.="</tr></thead><tbody>";

// preview: nampilin data ================================================================================
        foreach($dzArr as $idkar=>$qwe)
        {	$no+=1;
                $stream.="<tr class=rowcontent><td>".$no."</td>
                <td>".$qwe['sb']."</td>
                <td>".$qwe['bg']."</td>
                <td>".$qwe['nm']."</td>
                <td>".$qwe['jb']."</td>
                ";
                $zxc=0;
                foreach($test as $ar => $isi)
                {
					setIt($qwe[$isi],0);
					setIt($asd[$isi],0);
                        if($qwe[$isi]!=0){
                                if($pilihan=='rupiah')$stream.="<td align=right>".number_format($qwe[$isi])."</td>"; 
                                        else $stream.="<td align=right>".number_format($qwe[$isi],2)."</td>";
                        } else $stream.="<td align=right></td>";
                        $zxc+=$qwe[$isi];
                        $asd[$isi]+=$qwe[$isi];
                }
                
                if($pilihan=='rupiah')
                {
                    $stream.="<td align=right>".number_format($zxc)."</td>";
                    $stream.="<td>".number_format($gajiPokok[$idkar])."</td>";
                    $persen=$zxc/$gajiPokok[$idkar]*100;
                    $stream.="<td>".number_format($persen,2)."</td>";
                }
                else 
                {
                    $stream.="<td align=right>".number_format($zxc,2)."</td>";
                }
                
                
                
//		if($pilihan=='rupiah')$stream.="<td align=right>".number_format($qwe['tt'])."</td>";
//			else $stream.="<td align=right>".number_format($qwe['tt'],1)."</td>";
                //if($pilihan=='rupiah')$stream.="<td align=right>".number_format($zxc)."</td>";
                  //      else $stream.="<td align=right>".number_format($zxc,1)."</td>";
                
                        
                        $stream.="</tr>";	
        }
// preview: nampilin total ================================================================================
        $stream.="<thead class=rowheader>
        <tr>
        <td colspan=5>".$_SESSION['lang']['total']."</td>";
        foreach($test as $ar => $isi)
        {
//		if($pilihan=='rupiah')$stream.="<td align=right>".number_format($tot[$isi])."</td>";
//	 		else $stream.="<td align=right>".number_format($tot[$isi],1)."</td>";
                if($pilihan=='rupiah')$stream.="<td align=right>".number_format($asd[$isi])."</td>";
                        else $stream.="<td align=right>".number_format($asd[$isi],2)."</td>";
//		$total+=$tot[$isi];
                $total+=$asd[$isi];
        }
        if($pilihan=='rupiah')$stream.="<td align=right>".number_format($total)."</td>";
                else $stream.="<td align=right>".number_format($total,2)."</td>";
        
                
                
                
        //$stream.="</tbody></table>";
        
        if($pilihan=='rupiah')
        {
        $stream.="<td></td><td></td>";
        }
        
        $stream.="</tbody></table>";
	
        $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
		if(!empty($period))
		{
				$art=$period;
				$art=$art[1].$art[0];
		}
		if(!empty($periode))
		{
				$art=$periode;
				$art=$art[1].$art[0];
		}
		if(!empty($kdeOrg))
		{
				$kodeOrg=$kdeOrg;
		}
		if(!empty($kdOrg))
		{
				$kodeOrg=$kdOrg;
		}
		$nop_="RekapLembur".$art."__".$kodeOrg;
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
	
    case'getTgl':
            $add='';
        if($periode!='')
        {
                $tgl=$periode;
                $tanggal= count($tgl)>1? $tgl[0]."-".$tgl[1]: '';
        }
        elseif($period!='')
        {
                $tgl=$period;
                $tanggal= count($tgl)>1? $tgl[0]."-".$tgl[1]: '';
        }
        if($pilihan2=='bulanan')
        {
            $add=" and jenisgaji='B'";

        }
        if($pilihan2=='harian')
        {
            $add=" and jenisgaji='H'";

        }
        $sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where 
            kodeorg='".substr($kdUnit,0,4)."' and periode='".$tanggal."' ".$add."";
        $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
		$qTgl->setFetchMode(PDO::FETCH_ASSOC);
        $rTgl=$qTgl->fetch();
        echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
        break;
        case'getPeriode':
            //echo"warning:masuk";
            $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji  where kodeorg='".$kdOrg."' order by periode desc ";
            $optPeriode="<option value''>".$_SESSION['lang']['pilihdata']."</option>";
            $qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
			$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
            while($rPeriode=$qPeriode->fetch())
            {
                $optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
            }
            echo $optPeriode;
        break;
        case'getTombol':
        $arr="##kdOrg##periode##tgl1##tgl2##pilihan##pilihan2##pilihan3";
        $tombol="<button onclick=\"zPreview('sdm_slave_2laporanLembur','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button><button onclick=\"printpdf('sdm_slave_2laporanLembur','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button><button onclick=\"zExcel(event,'sdm_slave_2laporanLembur.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button><button onclick=\"Clear1()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>";
        if($_POST['pilihan']=='detail'){
            $tombol="<button onclick=\"zPreview('sdm_slave_2laporanLembur_detail','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
                     <button onclick=\"zExcel(event,'sdm_slave_2laporanLembur_detail.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
                     <button onclick=\"Clear1()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>";
        }
        echo $tombol;
        break;
        default:
        break;
}

?>