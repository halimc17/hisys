<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/fpdf.php');

	
$unit = checkPostGet('unit','');	
$divisi = checkPostGet('divisi','');	
$periode = checkPostGet('periode','');	
$noawal = checkPostGet('noawal','');	
$noakhir = checkPostGet('noakhir','');	
$jumlah = checkPostGet('jumlah','');	

$cariPt = checkPostGet('cariPt','');	
$cariStatus = checkPostGet('cariStatus','');	
$nofaktur = checkPostGet('nofaktur','');	
$tipe = checkPostGet('tipe','');	
$method = checkPostGet('method','');

$arrst=array("0"=>$_SESSION['lang']['tidakaktif'],"1"=>$_SESSION['lang']['aktif']);
@$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
@$namaPerusahaan=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
switch($method){	
	case'getnoawal':
		$tmpTgl = explode('-',$periode);
		$notran=$tmpTgl[0].$tmpTgl[1];
		
		$str = "select max(right(nobkm,3)) as nobkm from " . $dbname . ".kebun_nobkm where divisi ='".$divisi."' and periode ='".$periode."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        if(intval($bar['nobkm'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nobkm'])+1;
        }
        $notran=$notran."/".$divisi."/".addZero($noawal,3);
		echo $notran;
		
	break;
    case 'insert':
		$pawaltemp=substr($noawal,14,3);
		$nawal=substr($noawal,0,14);
			for($i=1;$i<=$jumlah;$i++){
				$n=$pawaltemp+($i-1);
				$n=addZero($n,3);
				$str="insert into ".$dbname.".kebun_nobkm (unit,divisi,periode,noawal,noakhir,nobkm,jumlah,status,updateby)
					values ('".$unit."','".$divisi."','".$periode."','".$noawal."','".$noakhir."','".$nawal.$n."','".$jumlah."','0','".$_SESSION['standard']['userid']."')";
					try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
    break;


    case'loadData':
        echo"
            <div id=container>
                <table class=sortable cellspacing=1 cellpadding=3 border=0>
                    <thead>
                         <tr class=rowheader>
                                 <td align=center>".$_SESSION['lang']['nourut']."</td>
                                 <td align=center>".$_SESSION['lang']['unit']."</td>
                                 <td align=center>".$_SESSION['lang']['divisi']."</td>
                                 <td align=center>".$_SESSION['lang']['periode']."</td>
                                 <td align=center>No BKM Awal</td>
                                 <td align=center>No BKM Akhir</td>
                                 <td align=center>".$_SESSION['lang']['jumlah']."</td>
                                 <td align=center>".$_SESSION['lang']['updateby']."</td>
                                 <td align=center colspan=2>".$_SESSION['lang']['action']."</td>
                         </tr>
                </thead>
                <tbody>";


                $limit=15;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
                $maxdisplay=($page*$limit);
				
				$whsubb='';
				if($_SESSION['empl']['subbagian']!=''){
					$whsubb=" and (updateby='".$_SESSION['standard']['userid']."' or divisi='".$_SESSION['empl']['subbagian']."')";
				}
				
                $ql2="select count(*) as jmlhrow from ".$dbname.".kebun_nobkm where divisi like '%".$cariPt."%' and periode like '%".$cariStatus."%' ".$whsubb."";
                $res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);                
                while($jsl=$res->fetch()){
                $jlhbrs= $jsl->jmlhrow;
                }
                
                $i="select distinct(noawal) as noawal, noakhir, unit, divisi, periode, jumlah, updateby  from ".$dbname.".kebun_nobkm where divisi like '%".$cariPt."%' and periode like '%".$cariStatus."%' ".$whsubb." order by noawal desc limit ".$offset.",".$limit.""; //exit('error'.$i);
                $res=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);      
                $no=$maxdisplay;
                while($d=$res->fetch()){
                    @$no+=1;
                    echo "<tr class=rowcontent id=tr_$no>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$d['unit']."</td>";
                    echo "<td align=left>".$d['divisi']."</td>";
                    echo "<td align=left>".$d['periode']."</td>";
                    echo "<td align=left>".$d['noawal']."</td>";
                    echo "<td align=left>".$d['noakhir']."</td>";
                    echo "<td align=right>".number_format($d['jumlah'])."</td>";
                    echo "<td align=left>".$nmKar[$d['updateby']]."</td>";
					echo"<td align=center><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['unit']."','".$d['divisi']."','".$d['noawal']."','".$d['noakhir']."');\"></td>";
					echo"<td align=center><img src=images/pdf.jpg class=zImgBtn title='print' onclick=\"printpdf('".$d['unit']."','".$d['divisi']."','".$d['noawal']."','".$d['noakhir']."',event);\"></td>";
                    echo "</tr>";
                }
				
				$totrows=ceil($jlhbrs/$limit);
				if($totrows==0)
				{
					$totrows=1;
				}
				$isiRow='';
				for($er=1;$er<=$totrows;$er++)
				{
				  $sel = ($page==$er-1)? 'selected': '';
				  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
				}
				
				
				echo"<tr><td colspan=9 align=center>";
				if($page<=0){
					echo"<button disabled class=mybutton onclick=cariBast(".($page-1).");>Prev</button>";
				}else{
					echo"<button class=mybutton onclick=cariBast(".($page-1).");>Prev</button>";
				}
				echo"<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
				if($page>=($er-2)){
					echo"<button disabled class=mybutton onclick=cariBast(".($page+1).");>Next</button>";
				}else{
					echo"<button class=mybutton onclick=cariBast(".($page+1).");>Next</button>";
				}
				echo"</td></tr>";
                echo"</tbody></table>";
		break;
		
        case 'delete':
			$dt="delete from ".$dbname.".kebun_nobkm where unit='".$unit."' and divisi='".$divisi."' and noawal='".$noawal."' and noakhir='".$noakhir."'";
		    try{$owlPDO->exec($dt); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
		
		case 'printpdf':
			class PDF extends FPDF{}
			$pdf=new PDF('L','mm','A4');
			
			$exawal = explode('/',$noawal);
			$awal = $exawal[2];
			
			$exakhir = explode('/',$noakhir);
			$akhir = $exakhir[2];
			
			$formatno = $exakhir[0]."/".$exakhir[1]."/";
			for($j=$awal;$j<=$akhir;$j++)
			{
				$pdf->AddPage();
				
				
				$arrHead = setheadreport($unit);
				$path=$arrHead['logo'];
				$pdf->Image($path,275,3,0,17);
				
				$namapt = getPT($dbname,$unit,false);
				$namapt = $namapt['nama'];

				$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
				$namunit = $optUnit[$unit];
				
				$pdf->SetY(5);
				$pdf->SetFont('Arial','B',10);
				$pdf->Cell(278,5,$namapt,0,1,'L');
				$pdf->Cell(210,7,$namunit,0,0,'L');
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(50,7,$formatno."".addZero($j,3),0,1,'C');
				
				$pdf->SetFont('Arial','B',10);
				$pdf->Cell(278,5,strtoupper('Buku Kegiatan Mandor(BKM)'),0,1,'C');
				
				$pdf->Cell(17,5,'Mandor : ',0,0,'L');
				$pdf->Cell(50,5,'__________________',0,0,'L');
				$pdf->Cell(20,5,'Mandor 1 : ',0,0,'L');
				$pdf->Cell(50,5,'__________________',0,0,'L');
				$pdf->Cell(20,5,'Kr Div : ',0,0,'L');
				$pdf->Cell(50,5,'__________________',0,0,'L');
				$pdf->Cell(20,5,'Tanggal : ',0,0,'L');
				$pdf->Cell(50,5,'__________________',0,1,'L');
				
				$pdf->SetFont('Arial','',9);
				$pdf->Cell(8,10,'No',1,0,'C');
				$pdf->Cell(32,10,'Nama Pekerjaan','TBR',0,'C');
				$pdf->Cell(32,10,'Blok','TBR',0,'C');
				
				$x = $pdf->GetX();
				$y = $pdf->GetY();
				$pdf->Cell(12,5,'Tahun','TR',0,'C');
				$pdf->SetXY($x,$y+5);
				$pdf->Cell(12,5,'Tanam','BR',0,'C');
				$pdf->SetXY($x+12,$y);
				
				$x = $pdf->GetX();
				$y = $pdf->GetY();
				$pdf->Cell(12,5,'Hasil','TR',0,'C');
				$pdf->SetXY($x,$y+5);
				$pdf->Cell(12,5,'Kerja','BR',0,'C');
				
				$pdf->SetXY($x+12,$y);
				$pdf->Cell(15,10,'Satuan','TBR',0,'C');
				
				$x = $pdf->GetX();
				$y = $pdf->GetY();
				$pdf->Cell(13,5,'Jumlah','TR',0,'C');
				$pdf->SetXY($x,$y+5);
				$pdf->Cell(13,5,'HK','BR',0,'C');
				
				$pdf->SetXY($x+13,$y);
				$pdf->Cell(20,10,'Keterangan','TBR',0,'C');
				
				$pdf->Cell(2,10,'',0,0,'C');
				$pdf->Cell(32,10,'Nama Pekerja','LTBR',0,'C');
				$pdf->Cell(15,10,'Status','TBR',0,'C');
				$pdf->Cell(10,10,'HK','TBR',0,'C');
				$pdf->Cell(12,10,'Premi','TBR',0,'C');
				
				$x = $pdf->GetX();
				$y = $pdf->GetY();
				$pdf->Cell(12,5,'Hasil','TR',0,'C');
				$pdf->SetXY($x,$y+5);
				$pdf->Cell(12,5,'Kerja','BR',0,'C');
				
				$pdf->SetXY($x+12,$y);
				$pdf->Cell(2,10,'',0,0,'C');
				
				$x = $pdf->GetX();
				$y = $pdf->GetY();
				$pdf->Cell(62,5,'Pemakaian Bahan',1,0,'C');
				$pdf->SetXY($x,$y+5);
				$pdf->Cell(35,5,'Nama','LBR',0,'C');
				$pdf->Cell(12,5,'Satuan','BR',0,'C');
				$pdf->Cell(15,5,'Jumlah','BR',0,'C');

				$pdf->Ln();
				
				$height = 5;
				for($i=1;$i<=25;$i++)
				{
					$pdf->Cell(8,$height,$i,'LBR',0,'C');
					$pdf->Cell(32,$height,'','BR',0,'C');
					$pdf->Cell(32,$height,'','BR',0,'C');
					$pdf->Cell(12,$height,'','BR',0,'C');
					$pdf->Cell(12,$height,'','BR',0,'C');
					$pdf->Cell(15,$height,'','BR',0,'C');
					$pdf->Cell(13,$height,'','BR',0,'C');
					$pdf->Cell(20,$height,'','BR',0,'C');
					
					$pdf->Cell(2,$height,'',0,0,'C');
					
					$pdf->Cell(32,$height,'','LBR',0,'C');
					$pdf->Cell(15,$height,'','BR',0,'C');
					$pdf->Cell(10,$height,'','BR',0,'C');
					$pdf->Cell(12,$height,'','BR',0,'C');
					$pdf->Cell(12,$height,'','BR',0,'C');
					
					$pdf->Cell(2,$height,'',0,0,'C');
					
					$pdf->Cell(35,$height,'','LBR',0,'C');
					$pdf->Cell(12,$height,'','BR',0,'C');
					$pdf->Cell(15,$height,'','BR',0,'C');
					$pdf->Ln();
				}
				
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(72.5,10,'Dibuat',0,0,'C');
				$pdf->Cell(72.5,10,'Di Periksa',0,0,'C');
				$pdf->Cell(72.5,10,'Di Periksa',0,0,'C');
				$pdf->Cell(72.5,10,'Di Ketahui',0,1,'C');
				
				$pdf->Ln(5);
				
				$pdf->Cell(72.5,5,'___________________',0,0,'C');
				$pdf->Cell(72.5,5,'___________________',0,0,'C');
				$pdf->Cell(72.5,5,'___________________',0,0,'C');
				$pdf->Cell(72.5,5,'___________________',0,1,'C');
				
				$pdf->Cell(72.5,5,'Mandor',0,0,'C');
				$pdf->Cell(72.5,5,'Asisten',0,0,'C');
				$pdf->Cell(72.5,5,'KTU',0,0,'C');
				$pdf->Cell(72.5,5,'Manager',0,1,'C');
				
				// $arrHead = setheadreport($unit);
				// $path=$arrHead['logo'];
				// $pdf->Image($path,20,7,0,22);
			}
			
			$pdf->Output();
		break;

		default:
		
		break;
}
?>
