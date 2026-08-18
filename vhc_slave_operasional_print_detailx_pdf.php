<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = checkPostGet('proses','');
$tipe= checkPostGet('tipe','');
$jenis= checkPostGet('jenis','');
$param = $_GET;

if($_SESSION['language']=='EN'){
    $optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan1');
}else{
        $optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
}
$optSatKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
$optNamaKary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNIKary=makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$optNamaBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optGudang=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,total_hk,upahkerja,total_premi,umr';
$col1 = 'tanggal,kodekegiatan,a.alokasi,total_hasilkerja,total_hk,total_premi';
$cols[] = explode(',',$col1);
//$query = selectQuery($dbname,'vhc_spl_prestasi',$col1,
//    "notransaksi='".$param['notransaksi']."'");
$query="select ".$col1." from ".$dbname.".vhc_spl_prestasi a left join ".$dbname.".vhc_spl_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";
//exit("Error".$query);
$data[] = fetchData($query);
$align[] = explode(",","L,L,L,R,R,R,R,R");
$length[] = explode(",","10,10,15,10,10,15,15,15");

# Kehadiran
$col2 = 'nik,absensi,jhk,umr,insentif';
$cols[] = explode(',',$col2);
$query = selectQuery($dbname,'vhc_spl_kehadiran',$col2,
    "notransaksi='".$param['notransaksi']."'");
$data[] = fetchData($query);
$align[] = explode(",","L,L,R,R,R");
$length[] = explode(",","20,20,20,20,20");

# Pakai Material
$col3 = 'kodeorg,kodebarang,kwantitas,kwantitasha,hargasatuan';
$cols[] = explode(',',$col3);
$query = selectQuery($dbname,'kebun_pakaimaterial',$col3,
    "notransaksi='".$param['notransaksi']."'");
$data[] = fetchData($query);
$align[] = explode(",","L,L,R,R,R");
$length[] = explode(",","20,20,20,20,20");

//getNamakaryawan
$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan)
{
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}

switch($tipe) {
    case "LC":
        $title = strtoupper("Land Clearing");
        break;
    case "BBT":
	$title = strtoupper($_SESSION['lang']['pembibitan']);
	break;
    case "TBM":
	$title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
	break;
    case "TM":
	$title = strtoupper("UPKEEP-".$_SESSION['lang']['tm']);
	break;
	case "PNN":
	$title = strtoupper($_SESSION['lang']['panen']);
	break;
    case "TB":
	$title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
	break;
	break;
	case "BKM":
	$title = strtoupper("BUKU KEGIATAN MANDOR");
	break;
	case "SPL":
	$title = strtoupper("BKM SIPIL");
	break;
    default:
	echo "Error : Atribut not Defined";
	exit;
	break;
}
$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);

/** Output Format **/
switch($proses) {
    case 'pdf':
        
        $pdf=new zPdfMaster('P','pt','A4');
        $pdf->_noThead=true;
        $pdf->setAttr1($title,$align,$length,array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',9);
        $pdf->Ln();
        $pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".$param['notransaksi'],0,1,'L',1);
        //'tanggal,kodekegiatan,a.alokasi,hasilkerja,total_hk,upahkerja,total_premi,umr';
       
        $sPres="select sum(a.insentif) as total_premi, sum(a.umr) as umr,sum(a.jhk) as total_hk,kodekegiatan,
                tanggal,b.alokasi,b.total_hasilkerja 
				from ".$dbname.".vhc_spl_kehadiran a 
				left join ".$dbname.".vhc_spl_prestasi b on a.notransaksi=b.notransaksi and a.nourut=b.nourut and a.nik=b.nik
                left join ".$dbname.".vhc_spl_aktifitas c on a.notransaksi=c.notransaksi 
				where a.notransaksi='".$param['notransaksi']."' group by a.notransaksi, kodekegiatan, b.alokasi order by kodekegiatan asc, b.alokasi asc";
       
        $pdf->Ln();
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell($width,$height,$titleDetail[0],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
        $pdf->Cell(31/100*$width,$height,$_SESSION['lang']['kodekegiatan'],1,0,'C',1);
        $pdf->Cell(13/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['hasilkerja'],1,0,'C',1);
        $pdf->Cell(6/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);
        $pdf->Cell(15/100*$width,$height,$_SESSION['lang']['premi'],1,0,'C',1);
        $pdf->Cell(15/100*$width,$height,$_SESSION['lang']['umr'],1,1,'C',1);
        $qPres=$owlPDO->query($sPres) or die(print " Gagal: ".PDOException::getMessage());
        $qPres->setFetchMode(PDO::FETCH_ASSOC);
        while($rPres=$qPres->fetch()){
			setIt($optKegiatan[$rPres['kodekegiatan']],'');
			setIt($optSatKegiatan[$rPres['kodekegiatan']],'');
			$pdf->SetFont('Arial','',7);
			$pdf->SetFillColor(255,255,255);
			$pdf->Cell(10/100*$width,$height,tanggalnormal($rPres['tanggal']),1,0,'C',1);
			$pdf->Cell(31/100*$width,$height,$optKegiatan[$rPres['kodekegiatan']],1,0,'L',1);
			$pdf->Cell(13/100*$width,$height,$rPres['alokasi'],1,0,'L',1);
			$pdf->Cell(10/100*$width,$height,$rPres['total_hasilkerja'],1,0,'R',1);
			$pdf->Cell(6/100*$width,$height,$optSatKegiatan[$rPres['kodekegiatan']],1,0,'C',1);
			$pdf->Cell(15/100*$width,$height,@hidezerodecimal($rPres['total_premi'],0),1,0,'R',1);
			$pdf->Cell(15/100*$width,$height,@hidezerodecimal($rPres['umr'],0),1,1,'R',1);
        }
        //$col2 = 'nik,absensi,jhk,umr,insentif';
        
        

        $pdf->Ln(30);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$titleDetail[1],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
        
        $pdf->Cell(5/100*$width,$height,"No.",1,0,'C',1);
        $pdf->Cell(45/100*$width,$height,$_SESSION['lang']['nik'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['absensi'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['jhk'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['umr'],1,0,'C',1);
		$pdf->Cell(10/100*$width,$height,$_SESSION['lang']['hasilkerja'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['insentif'],1,1,'C',1);
        $pdf->SetFont('Arial','',7);
        $pdf->SetFillColor(255,255,255);
        $totHk=$totUmr=$totIns=$tothslkrj=0;
        $no=0;
        $sKhdrn="select distinct * from ".$dbname.".vhc_spl_kehadiran where notransaksi='".$param['notransaksi']."'";
        $qKhdrn=$owlPDO->query($sKhdrn) or die(print " Gagal: ".PDOException::getMessage());
        $qKhdrn->setFetchMode(PDO::FETCH_ASSOC);
        while($rKhdrn=$qKhdrn->fetch())
        {        
            $no++;
            $pdf->Cell(5/100*$width,$height,$no,1,0,'C',1);
            $pdf->Cell(45/100*$width,$height,$optNIKary[$rKhdrn['nik']]." - ".$optNamaKary[$rKhdrn['nik']],1,0,'L',1);
            $pdf->Cell(10/100*$width,$height,$rKhdrn['absensi'],1,0,'C',1);
            $pdf->Cell(10/100*$width,$height,$rKhdrn['jhk'],1,0,'C',1);
            $pdf->Cell(10/100*$width,$height,@hidezerodecimal($rKhdrn['umr'],2),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,@hidezerodecimal($rKhdrn['hasilkerja'],2),1,0,'R',1);
            $pdf->Cell(10/100*$width,$height,@hidezerodecimal($rKhdrn['insentif'],2),1,1,'R',1);
            $totHk+=$rKhdrn['jhk'];
            $totUmr+=$rKhdrn['umr'];
            $totIns+=$rKhdrn['insentif'];
			$tothslkrj+=$rKhdrn['hasilkerja'];
        }
        
        $pdf->Cell(60/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$totHk,1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,@hidezerodecimal($totUmr,2),1,0,'R',1);
		 $pdf->Cell(10/100*$width,$height,@hidezerodecimal($tothslkrj,2),1,0,'R',1);
        $pdf->Cell(10/100*$width,$height,@hidezerodecimal($totIns,2),1,1,'R',1);
        
        // $pdf->Ln(30);
        // $pdf->SetFont('Arial','B',9);
        // $pdf->Cell($width,$height,$titleDetail[2],0,1,'L',1);
        // $pdf->SetFillColor(220,220,220);
        // $pdf->SetFont('Arial','B',8);
        
        // $pdf->Cell(5/100*$width,$height,"No.",1,0,'C',1);
        // $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);
        // $pdf->Cell(30/100*$width,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);
        // $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['kwantitas'],1,0,'C',1);
        // $pdf->Cell(15/100*$width,$height,$_SESSION['lang']['hasilkerjad'],1,0,'C',1);
        // $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['hargasatuan'],1,0,'C',1);
        // $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['sloc'],1,1,'C',1);
        // $pdf->SetFont('Arial','',7);
        // $pdf->SetFillColor(255,255,255);
        // $sMat="select distinct * from ".$dbname.".kebun_pakaimaterial where notransaksi='".$param['notransaksi']."'";
        // $qMat=$owlPDO->query($sMat) or die(print " Gagal: ".PDOException::getMessage());
        // $qMat->setFetchMode(PDO::FETCH_ASSOC);
		// $no3=0;
        // while($rMat=$qMat->fetch())
        // {        
        //     $no3++;
        //     $pdf->Cell(5/100*$width,$height,$no3,1,0,'C',1);
        //     $pdf->Cell(10/100*$width,$height,$rMat['kodeorg'],1,0,'C',1);
        //     $pdf->Cell(30/100*$width,$height,$optNamaBrg[$rMat['kodebarang']],1,0,'L',1);
        //     $pdf->Cell(10/100*$width,$height,$rMat['kwantitas'],1,0,'C',1);
        //     $pdf->Cell(15/100*$width,$height,$rMat['kwantitasha'],1,0,'R',1);
        //     $pdf->Cell(10/100*$width,$height,$rMat['hargasatuan'],1,0,'R',1);
        //     $pdf->Cell(20/100*$width,$height,$optGudang[$rMat['kodegudang']],1,1,'L',1);
        // }
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',8);
        $sAsis="select distinct nikmandor,nikmandor1,nikasisten,keranimuat,tanggal,kodeorg from ".$dbname.".vhc_spl_aktifitas where notransaksi='".$param['notransaksi']."'";
        $qAsis=$owlPDO->query($sAsis) or die(print " Gagal: ".PDOException::getMessage());
        $qAsis->setFetchMode(PDO::FETCH_ASSOC);
        $rAsis=$qAsis->fetch();
        setIt($RnamaKary[$rAsis['nikasisten']],'');
        setIt($RnamaKary[$rAsis['nikmandor1']],'');
        setIt($RnamaKary[$rAsis['nikmandor']],'');
        $pdf->ln(35);
        $pdf->Cell(85/100*$width,$height,$rAsis['kodeorg'].",".tanggalnormal($rAsis['tanggal']),0,1,'R',0);
        $pdf->ln(35);
        $pdf->Cell(33/100*$width,$height,$_SESSION['lang']['dstujui_oleh'],0,0,'C',0);
        $pdf->Cell(33/100*$width,$height,$_SESSION['lang']['diperiksa'],0,0,'C',0);
        $pdf->Cell(34/100*$width,$height,$_SESSION['lang']['dibuatoleh'],0,1,'C',0);
        $pdf->ln(65);
        $pdf->SetFont('Arial','U',8);
        $pdf->Cell(33/100*$width,$height,$RnamaKary[$rAsis['nikasisten']],0,0,'C',0);
        $pdf->Cell(33/100*$width,$height,$RnamaKary[$rAsis['nikmandor1']],0,0,'C',0);
        $pdf->Cell(34/100*$width,$height,$RnamaKary[$rAsis['nikmandor']],0,1,'C',0);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(33/100*$width,$height,$_SESSION['lang']['asisten'],0,0,'C',0);
        $pdf->Cell(33/100*$width,$height,$_SESSION['lang']['nikmandor1'],0,0,'C',0);
        $pdf->Cell(34/100*$width,$height,$_SESSION['lang']['nikmandor'],0,1,'C',0);
      
	
        $pdf->Output();
        break;
    case 'excel':
        break;
    case'html':
        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $men='menu.css';
          $gen='generic.css';
        }else if($theme=='red'){
          $men='menuRed.css';
          $gen='genericRed.css';  
        }else{
          $men='menuGray.css';
          $gen='genericGray.css';  
        }         
        $tab='';
		$tab.="<fieldset style=min-height:100%><legend>".$title."</legend>";
		if($jenis=='html'){
			$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
			$border="border=0";
		} else {
			$border="border=1";
		}
			$tab.="<table cellpadding=1 cellspacing=1 ".$border." class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td><b> ".$param['notransaksi']."</b></td></tr>";
        
        $tab.="</tbody></table>";
            
        
        $tab.="<br /><b>".$titleDetail[0]."<b><br />";
        $tab.="<table cellpadding=1 cellspacing=1 ".$border." class=sortable width=100%><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>No</td>";
        $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['namakegiatan']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['hasilkerjad']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['jhk']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['umr']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['total_premi']."</td>";
        $tab.="</tr></thead><tbody>";
         $sPres="select sum(a.insentif) as total_premi, sum(a.umr) as umr,sum(a.jhk) as total_hk,kodekegiatan,
                tanggal,b.kodeorg, sum(b.total_hasilkerja) as hasilkerja 
				from ".$dbname.".vhc_spl_kehadiran a 
				left join ".$dbname.".vhc_spl_prestasi b on a.notransaksi=b.notransaksi and a.nik=b.nik and a.nourut=b.nourut
                left join ".$dbname.".vhc_spl_aktifitas c on a.notransaksi=c.notransaksi 
				where a.notransaksi='".$param['notransaksi']."' group by a.notransaksi, kodekegiatan, b.kodeorg order by kodekegiatan asc, b.kodeorg asc"; //exit('error'. $sPres);
        $qPres=$owlPDO->query($sPres) or die(print " Gagal: ".PDOException::getMessage());
		$no=$thk=$tumr=$tpremi=$tpres=0;
        while($rPres=$qPres->fetch()){
			 $no+=1;
             $tab.="<tr class=rowcontent>";
             $tab.="<td align=center>".$no."</td>";
             $tab.="<td>".tanggalnormal($rPres['tanggal'])."</td>";
             $tab.="<td>".@$rPres['kodeorg']."</td>";
             $tab.="<td>".@$rPres['kodekegiatan']." - ".@$optKegiatan[$rPres['kodekegiatan']]."</td>";
             $tab.="<td>".@$optSatKegiatan[$rPres['kodekegiatan']]."</td>";
             $tab.="<td align=right>".@hidezerodecimal($rPres['hasilkerja'],2)."</td>";
             $tab.="<td align=right>".@hidezerodecimal($rPres['total_hk'],2)."</td>";
             $tab.="<td align=right>".@hidezerodecimal($rPres['umr'],0)."</td>";
             $tab.="<td align=right>".@hidezerodecimal($rPres['total_premi'],0)."</td>";
             $tab.="</tr>";

			 $thk+=$rPres['total_hk'];
			 $tumr+=$rPres['umr'];
			 $tpremi+=$rPres['total_premi'];
			 $tpres+=$rPres['hasilkerja'];
		}
			 
			 $tab.="<tr class=rowcontent>";
             $tab.="<td align=center colspan=5>".$_SESSION['lang']['total']."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($tpres,2)."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($thk,2)."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($tumr,2)."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($tpremi,2)."</td>";
             $tab.="</tr>";
			 
         $tab.="</table>";
         $tab.="<br /><b>".$titleDetail[1]."</b><br />";
      
            $tab.="<table cellpadding=1 cellspacing=1 ".$border." class=sortable width=100%><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<td align=center>No</td>";
            $tab.="<td align=center>".$_SESSION['lang']['kegiatan']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['blok']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['nama']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['absensi']."</td>";
			 $tab.="<td align=center>".$_SESSION['lang']['hasilkerjad']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['jhk']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['umr']."</td>";
            $tab.="<td align=center>".$_SESSION['lang']['total_premi']."</td>";
            $tab.="</tr></thead><tbody>";
            $totJhk=$totUmr=$totInsentif=$tothasilkerja=0;
            $sKhdrn="select a.nik, a.absensi, a.insentif, a.umr, jhk, kodekegiatan,tanggal,b.kodeorg,a.hasilkerja 
				from ".$dbname.".vhc_spl_kehadiran a 
				left join ".$dbname.".vhc_spl_prestasi b on a.notransaksi=b.notransaksi and a.nik=b.nik and a.nourut=b.nourut
                left join ".$dbname.".vhc_spl_aktifitas c on a.notransaksi=c.notransaksi 
				where a.notransaksi='".$param['notransaksi']."' order by kodekegiatan asc, b.kodeorg asc, nik asc"; 
            $qKhdrn=$owlPDO->query($sKhdrn) or die(print " Gagal: ".PDOException::getMessage());
            $qKhdrn->setFetchMode(PDO::FETCH_ASSOC);                       
            @$no='';
			while($rKhdrn=$qKhdrn->fetch()){
			 @$no+=1;
             $tab.="<tr class=rowcontent>";
             $tab.="<td align=center>".$no."</td>";
             $tab.="<td>".@$optKegiatan[$rKhdrn['kodekegiatan']]."</td>";
             $tab.="<td>".$rKhdrn['kodeorg']."</td>";
             $tab.="<td>".@$optNIKary[$rKhdrn['nik']]." - ".@$optNamaKary[$rKhdrn['nik']]."</td>";
             $tab.="<td align=center>".$rKhdrn['absensi']."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['hasilkerja'],2)."</td>";
             $tab.="<td align=right>".$rKhdrn['jhk']."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['umr'],2)."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['insentif'],2)."</td>";
             $tab.="</tr>";
             $totJhk+=$rKhdrn['jhk'];
             $totUmr+=$rKhdrn['umr'];
             $totInsentif+=$rKhdrn['insentif'];
			 $tothasilkerja+=$rKhdrn['hasilkerja'];
            }
             $tab.="<tr class=rowcontent>";
             $tab.="<td align=center colspan=5>".$_SESSION['lang']['total']."</td>";
			 $tab.="<td  align=right>".@hidezerodecimal($tothasilkerja,2)."</td>";
             $tab.="<td  align=right>".$totJhk."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($totUmr,2)."</td>";
             $tab.="<td  align=right>".@hidezerodecimal($totInsentif,2)."</td>";
             $tab.="</tr>";
         $tab.="</table><br/><b>".$titleDetail[2]."</b><br />";
        $tab.="<table cellpadding=1 cellspacing=1 ".$border." class=sortable width=100%><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>No</td>";
        $tab.="<td align=center>".$_SESSION['lang']['kegiatan']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['kodebarang']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['hasilkerjad']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['kwantitas']."</td>";
        $tab.="<td align=center hidden>".$_SESSION['lang']['hargasatuan']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['sloc']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['sloc']."</td>";
        $tab.="</tr></thead><tbody>";
        $sMat="select * from ".$dbname.".kebun_pakaimaterial where notransaksi='".$param['notransaksi']."'";
        $qMat=$owlPDO->query($sMat) or die(print " Gagal: ".PDOException::getMessage());
        $qMat->setFetchMode(PDO::FETCH_ASSOC);
        $no='';
		while($rMat=$qMat->fetch()){
			$no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$optKegiatan[$rMat['kodekegiatan']]."</td>";
            $tab.="<td>".$rMat['kodeorg']."</td>";
            $tab.="<td>".$rMat['kodebarang']."-".$optNamaBrg[$rMat['kodebarang']]."</td>";
            $tab.="<td align=right>".@hidezerodecimal($rMat['kwantitasha'],2)."</td>";
            $tab.="<td align=right>".@hidezerodecimal($rMat['kwantitas'],2)."</td>";
            $tab.="<td align=right hidden>".$rMat['hargasatuan']."</td>";
            $tab.="<td>".$optGudang[$rMat['kodegudang']]."</td>";
            $whr="notransaksireferensi='".$rMat['notransaksi']."'";
            $optTrnsGdng=makeOption($dbname,'log_transaksiht','notransaksireferensi,notransaksi',$whr);
            $tab.="<td>".(isset($optTrnsGdng[$rMat['notransaksi']]) ? $optTrnsGdng[$rMat['notransaksi']] : "")."</td>";
            $tab.="</tr>";
        }
        $tab.="</table><br />";
		
        if($jenis=='html'){
			echo $tab;
		} else {
			$not=str_replace('/','',$param['notransaksi']);
			$stream = $tab;
			$nop_ = "detail_".$not;
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
								parent.window.alert('Cant convert to excel format');
								</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
				}
				closedir($handle);
			}
		}
		
    break;
    default:
    break;
}
?>