<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$param = $_GET;
if(!empty($_GET)){$param=$_GET;}else{$param=$_POST;}
$proses = $param['proses'];
$tipe=$param['tipe'];

$notran=$param['notransaksi'];

/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$col1 = 'tanggal,nik,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpenalty,upahpremi,premibasis,rupiahpenalty,luaspanen';
$cols[] = explode(',',$col1);
//$query = selectQuery($dbname,'kebun_prestasi',$col1,
//    "notransaksi='".$param['notransaksi']."'");
$query="select ".$col1." from ".$dbname.".kebun_prestasi_mobile a left join ".$dbname.".kebun_aktifitas_mobile b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";
//exit("Error".$query);
$data[] = fetchData($query);
$align[] = explode(",","L,L,L,R,R,R,R,R");
$length[] = explode(",","10,10,15,10,10,15,15,15");



//getNamakaryawan
$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan)
{
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}



$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi order by namaorganisasi asc";
//exit("Error".$sOrg);
$rDataOrg=fetchData($sOrg);
foreach($rDataOrg as $brOrg =>$rNamaOrg){
    $rNmOrg[$rNamaOrg['kodeorganisasi']]=$rNamaOrg['namaorganisasi'];
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
    default:
    echo "Error : Atribut not Defined";
    exit;
    break;
}
$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);

// Init Total
$totJanjang=$totUpahKerja=$totUpahKerjapenalty=$totUpahPremi=0;
$totUpahPremibasis=$totUpahDenda=$totLuas=$totSisa=0;

/** Output Format **/
switch($proses) {
    case 'pdf':
        
        $pdf=new zPdfMaster('P','pt','A4');
        $pdf->_noThead=true;
        $pdf->setAttr1($title,$align,$length,array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
        $pdf->AddPage();
        $pdf->Ln();
        $pdf->SetFillColor(255,255,255);  
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".$param['notransaksi'],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
		$getX = $pdf->GetX();
		$getY = $pdf->GetY();
        $pdf->MultiCell(10/100*$width,$height*3,$_SESSION['lang']['tanggal'],1,'C',1);
		$pdf->SetXY($getX+(10/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(15/100*$width,$height*3,$_SESSION['lang']['namakaryawan'],1,'C',1);
		$pdf->SetXY($getX+(15/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(13/100*$width,$height*3,$_SESSION['lang']['kodeorg'],1,'C',1);
		$pdf->SetXY($getX+(13/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(5/100*$width,$height*3,$_SESSION['lang']['jjg'],1,'C',1);
		$pdf->SetXY($getX+(5/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(6/100*$width,$height*3,$_SESSION['lang']['luas'],1,'C',1);
        $pdf->SetXY($getX+(6/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(8/100*$width,$height+6,$_SESSION['lang']['upahkerja'],1,'C',1);
		$pdf->SetXY($getX+(8/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(8/100*$width,$height,$_SESSION['lang']['upahpenalty'],1,'C',1);
		$pdf->SetXY($getX+(8/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(8/100*$width,$height+6,$_SESSION['lang']['premibasis'],1,'C',1);
		$pdf->SetXY($getX+(8/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(8/100*$width,$height+6,$_SESSION['lang']['upahpremi'],1,'C',1);
        $pdf->SetXY($getX+(8/100*$width),$getY);
		$getX = $pdf->GetX();
		$pdf->MultiCell(8/100*$width,$height+6,$_SESSION['lang']['rupiahpenalty'],1,'C',1);
		$pdf->SetXY($getX+(8/100*$width),$getY);
		$getX = $pdf->GetX();
        $pdf->MultiCell(8/100*$width,$height*3,$_SESSION['lang']['total'],1,'C',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
        $qData=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);        
        while($rData=$qData->fetch())
        {
            $pdf->Cell(10/100*$width,$height,tanggalnormal($rData['tanggal']),1,0,'C',1);
            $pdf->Cell(15/100*$width,$height,$RnamaKary[$rData['nik']],1,0,'L',1);
            $pdf->Cell(13/100*$width,$height,getNamaOrg($rData['kodeorg']),1,0,'C',1);
            $pdf->Cell(5/100*$width,$height,$rData['hasilkerja'],1,0,'R',1);
            $pdf->Cell(6/100*$width,$height,number_format($rData['luaspanen'],2),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['upahkerja'],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['upahpenalty'],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['premibasis'],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['upahpremi'],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($rData['rupiahpenalty'],0),1,0,'R',1);
            $sisa=$rData['upahkerja']-$rData['upahpenalty']+$rData['premibasis']+$rData['upahpremi']-$rData['rupiahpenalty'];
            $pdf->Cell(8/100*$width,$height,number_format($sisa,0),1,1,'R',1);
            $totJanjang+=$rData['hasilkerja'];
            $totUpahKerja+=$rData['upahkerja'];
            $totUpahKerjapenalty+=$rData['upahpenalty'];
            $totUpahPremi+=$rData['upahpremi'];
            $totUpahPremibasis+=$rData['premibasis'];
            $totUpahDenda+=$rData['rupiahpenalty'];
            $totLuas+=$rData['luaspanen'];
            $totSisa+=$sisa;
        }
        $pdf->Cell(38/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
        $pdf->Cell(5/100*$width,$height,number_format($totJanjang,0),1,0,'R',1);
        $pdf->Cell(6/100*$width,$height,number_format($totLuas,2),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahKerja,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahKerjapenalty,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahPremibasis,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahPremi,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totUpahDenda,0),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($totSisa,0),1,1,'R',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',8);
        $sAsis="select distinct nikmandor,nikmandor1,nikasisten,keranimuat,tanggal,kodeorg from ".$dbname.".kebun_aktifitas_mobile where notransaksi='".$param['notransaksi']."'";
        $qAsis=$owlPDO->query($sAsis) or die(print " Gagal: ".PDOException::getMessage());
        $qAsis->setFetchMode(PDO::FETCH_ASSOC);    
        $rAsis=$qAsis->fetch();
        setIt($RnamaKary[$rAsis['nikasisten']],'');
        setIt($RnamaKary[$rAsis['nikmandor1']],'');
        setIt($RnamaKary[$rAsis['nikmandor']],'');
        $pdf->ln(10);
        $pdf->Cell(85/100*$width,$height,$rAsis['kodeorg'].",".tanggalnormal($rAsis['tanggal']),0,1,'R',0);
        $pdf->ln(35);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['dbuat_oleh'],0,0,'C',0);        
        $pdf->Cell(29/100*$width,$height,$_SESSION['lang']['diperiksa'],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['disetujui'],0,1,'C',0);
        $pdf->ln(65);
        $pdf->SetFont('Arial','U',8);
        $pdf->Cell(28/100*$width,$height,$RnamaKary[$rAsis['nikasisten']],0,0,'C',0);        
        $pdf->Cell(29/100*$width,$height,$RnamaKary[$rAsis['nikmandor']],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$RnamaKary[$rAsis['nikmandor1']],0,1,'C',0);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['kerani'],0,0,'C',0);        
        $pdf->Cell(29/100*$width,$height,$_SESSION['lang']['mandor'],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['nikmandor1'],0,1,'C',0);
        $pdf->Output();
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
        
        $tab="<link rel=stylesheet type=text/css href=style/".$gen.">";
        $tab.="<style>
                    .resiconn {
                        width:12px;
                        height:12px;
                        cursor:pointer;
                        transition: all .1s ease-in-out;
                    }
                    
                    .resiconn:hover{
                        transform: scale(20);
                    }
                    
                </style>";
        //$tab.="<fieldset><legend>".$title."</legend>";
        $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$param['notransaksi']."</td></tr>";
        $tab.="</tbody></table>";
        $tab.="<br />".$titleDetail[0]."<br />";
        $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<th align=center>No</th>";
        $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['nik']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['nama']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['blok']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['tph']."</th>";
        $tab.="<th align=center>Photo</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['nospb']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['hasilkerja']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['luas']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['brondolan']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['upahkerja']."</th>";
        $tab.="<th  align=center>".$_SESSION['lang']['upahpenalty']."</th>";        
        $tab.="<th align=center>".$_SESSION['lang']['premibasis']." (Rp)</th>";
        $tab.="<th align=center>".$_SESSION['lang']['premlebihbasis']." (Rp)</th>";
        $tab.="<th align=center>Total ".$_SESSION['lang']['upahpremi']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['rupiahpenalty']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['total']."</th>";

        $tab.="</tr></thead><tbody>";
        
        
        // $isiQuery="a.notransaksi,a.nik,a.kodekegiatan,a.kodeorg as kodeblok,a.tahuntanam,a.hasilkerja,a.hasilkerjakg,
            // a.jumlahhk,a.norma,a.outputminimal,a.upahkerja,a.upahpenalty,a.upahpremi,a.premibasis,a.umr,a.statusblok,
            // a.pekerjaanpremi,a.penalti1,a.penalti2,a.penalti3,a.penalti4,a.penalti5,a.penalti6,a.penalti7,a.penalti8,
            // a.penalti9,a.penalti10,a.rupiahpenalty,a.luaspanen,a.kodesegment,a.brondolan,a.jjgpenalty,b.*";
        $str="select * from ".$dbname.".kebun_prestasi_mobile_vw where notransaksi='".$param['notransaksi']."' order by karyawanid asc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);        
        $no='';
        $resPhoto = array();
        $no= 0;
		while($bar=$res->fetch()){
				$no++;
                // if(!is_null($bar['photo'])){
                //     $resPhoto[] = [
                //         'formaticon' => 'jpg',
                //         'namafile' => $bar['photo'],
                //         'id' => '1'
                //     ];
                // }
                // if(!is_null($bar['photoakhir'])){
                //     $resPhoto[] = [
                //         'formaticon' => 'jpg',
                //         'namafile' => $bar['photoakhir'],
                //         'id' => '1'
                //     ];
                // }
                
				$bgcolor=$title=$color='';
				$strx = "select count(nik) as jmlkary, nik from " . $dbname . ".kebun_prestasi_mobile where notransaksi='".$bar['notransaksi']."' and nik='".$bar['karyawanid']."' group by nik";
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$barx = $resx->fetch();
				if(($bar['karyawanid']==$barx['nik']) and ($barx['jmlkary']>1)){
					$bgcolor="style=background-color:orange;";
					$title=" title = 'Karyawan Panen lebih dari 1 blok !'";
				}
					$tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td>".getKary($bar['karyawanid'],'nik')."</td>";
					$tab.="<td>".$RnamaKary[$bar['karyawanid']]."</td>";
					$tab.="<td>".getNamaOrg($bar['kodeorg'])."</td>";
                    $tab.="<td>".@$bar['tph']."</td>";
                    $tab.="<td style='text-align:center;cursor:pointer'><img title='Foto' class='resiconn' style='width:20px;height:20px;' src='{$bar['photo']}'></td>";
					$tab.="<td align=center>".$bar['nospb']."</td>";
					$tab.="<td align=right>".$bar['hasilkerja']."</td>";
					$tab.="<td align=right>".number_format($bar['luaspanen'],2)."</td>";
					$tab.="<td align=right>".number_format($bar['brondolan'],0)."</td>";
					$tab.="<td align=right>".number_format($bar['upahkerja'],0)."</td>";
					$tab.="<td align=right>".number_format($bar['upahpenalty'],0)."</td>";                
					$tab.="<td align=right>".number_format($bar['upahpremi'],0)."</td>";
					$tab.="<td align=right>".number_format($bar['upahpremilebihbasis'],0)."</td>";
					$totPremi = $bar['upahpremi'] + $bar['upahpremilebihbasis'];
					$tab.="<td align=right>".number_format($totPremi,0)."</td>";
					$tab.="<td align=right>".number_format($bar['rupiahpenalty'],0)."</td>";
					$sisa=($bar['upahkerja']-$bar['upahpenalty'])+($totPremi-$bar['rupiahpenalty']);
					$tab.="<td align=right>".number_format($sisa,0)."</td>";
                $tab.="</tr>";
                @$totJanjang+=$bar['hasilkerja'];
                @$totLuas+=$bar['luaspanen'];
                @$totUpahKerja+=$bar['upahkerja'];
                @$totUpahKerjapenalty+=$bar['upahpenalty'];
                @$totUpahPremi+=$bar['upahpremi'];
                @$totUpahPremiLebihBasis+=$bar['upahpremilebihbasis'];
                @$totPremiAll+=$totPremi;
                @$totUpahDenda+=$bar['rupiahpenalty'];
                @$totbrondolan+=$bar['brondolan'];
                @$totSisa+=$sisa;
                
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=8 align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($totJanjang,0)."</td>";
        $tab.="<td align=right>".number_format($totLuas,2)."</td>";
        $tab.="<td align=right>".number_format($totbrondolan,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerja,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerjapenalty,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahPremi,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahPremiLebihBasis,0)."</td>";
        $tab.="<td align=right>".number_format($totPremiAll,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahDenda,0)."</td>";
        $tab.="<td align=right>".number_format($totSisa,0)."</td>";
        $tab.="<td></td></tr></tbody></table>";
        
		// $tab.="<br><label>File Upload</label>
		// 	<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
		// 		<thead>
		// 		<tr class=rowheader>
		// 			<td align='center' width=30px>No.</td>
		// 			<td align='center' width=50px>File Type</td>
		// 			<td align='center'>Filename</td>
		// 			<td align='center' width=30px colspan=2>Action</td>
		// 		</tr>
		// 		</thead>
		// 		<tbody>";
		// 	$path = "fileupload/bkm/";
		// 	if(empty($resPhoto)){
		// 		$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		// 	}else{
		// 		$no=0;
		// 		foreach($resPhoto as $key=>$val){
		// 			$no++;
		// 			$tab.="<tr class=rowcontent>
		// 					<td style='text-align:center'>".$no."</td>";
		// 			$icon=seticonfile($val['formaticon']);
		// 			$tab.="<td style='text-align:center'>
		// 					<a href='".$val['namafile']."' download><img src=".$icon." class=resicon></a>
		// 				</td>";
		// 			$tab.="<td style='text-align:left;cursor:pointer'><img title='Foto' class='resiconn' style='width:20px;height:20px;' src='".$val['namafile']."'></td>";
					
		// 			$tab.="<td align=center width=30px colspan=2><a href='".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon title='download'></a></td>";
		// 			$tab.="</tr>";
		// 		}	
		// 	}	
		// 	$tab.="</tbody>
		// 	</table>
		// ";
        echo $tab;
        break;
    case 'excel':
        
        //$tab="<link rel=stylesheet type=text/css href=style/generic.css>";
        //$tab.="<fieldset><legend>".$title."</legend>";
        $tab.="<table border=1 cellpadding=5 cellspacing=1 class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td bgcolor=#CCCCCC>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td bgcolor=#CCCCCC>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$param['notransaksi']."</td></tr>";
		$tab.="</tbody></table>";
        $tab.="<br />".$titleDetail[0]."<br />";
        $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['hasilkerja']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['luas']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['upahkerja']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['upahpenalty']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['premibasis']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['premlebihbasis']." (Rp)</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>Total ".$_SESSION['lang']['upahpremi']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rupiahpenalty']."</td>";
        $tab.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="</tr></thead><tbody>";
        
        
        $str="select * from ".$dbname.".kebun_prestasi_vw where notransaksi='".$param['notransaksi']."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);        
        while($bar=$res->fetch()){            
                $tab.="<tr class=rowcontent>";
					$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td>".$RnamaKary[$bar['karyawanid']]."</td>";
					$tab.="<td>".getNamaOrg($bar['kodeorg'])."</td>";
					$tab.="<td align=right>".$bar['hasilkerja']."</td>";
					$tab.="<td align=right>".number_format($bar['luaspanen'],2)."</td>";
					$tab.="<td align=right>".number_format($bar['upahkerja'],0)."</td>";
					$tab.="<td align=right>".number_format($bar['upahpenalty'],0)."</td>";                
					$tab.="<td align=right>".number_format($bar['upahpremi'],0)."</td>";
					$tab.="<td align=right>".number_format($bar['upahpremilebihbasis'],0)."</td>";
					$totPremi = $bar['upahpremi'] + $bar['upahpremilebihbasis'];
					$tab.="<td align=right>".number_format($totPremi,0)."</td>";
					$tab.="<td align=right>".number_format($bar['rupiahpenalty'],0)."</td>";
					$sisa=($bar['upahkerja']-$bar['upahpenalty'])+($totPremi-$bar['rupiahpenalty']);
					$tab.="<td align=right>".number_format($sisa,0)."</td>";
                $tab.="</tr>";
                @$totJanjang+=$bar['hasilkerja'];
                @$totLuas+=$bar['luaspanen'];
                @$totUpahKerja+=$bar['upahkerja'];
                @$totUpahKerjapenalty+=$bar['upahpenalty'];
                @$totUpahPremi+=$bar['upahpremi'];
                @$totUpahPremiLebihBasis+=$bar['upahpremilebihbasis'];
                @$totPremiAll+=$totPremi;
                @$totUpahDenda+=$bar['rupiahpenalty'];
                @$totSisa+=$sisa;
                
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3 align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($totJanjang,0)."</td>";
        $tab.="<td align=right>".number_format($totLuas,2)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerja,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerjapenalty,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahPremi,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahPremiLebihBasis,0)."</td>";
        $tab.="<td align=right>".number_format($totPremiAll,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahDenda,0)."</td>";
        $tab.="<td align=right>".number_format($totSisa,0)."</td>";
        $tab.="</tr></tbody></table>";
        
        
     
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
                //$nop_="PNN:".$param['notransaksi'];
                $nop_="Laporan_PNN";
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
    default:
    break;
}
?>