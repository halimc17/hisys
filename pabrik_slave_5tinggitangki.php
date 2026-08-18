<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$kodeorg=checkPostGet('kodeorg','');
$kodetangki=checkPostGet('kodetangki','');
$tinggi=checkPostGet('tinggi','0');
$volume=checkPostGet('volume','0');
$beda=checkPostGet('beda','0');



switch($proses){
	case 'insert':
		if($kodeorg==''||$kodetangki==''||$tinggi==''||$volume==''||$beda==''){
			echo 'Gagal : Semua field harus diisi.';
		}else{
			$strCount="select * from ".$dbname.".pabrik_5tinggitangki where ".
				"millcode='".$kodeorg."' and kodetangki='".$kodetangki."' and tinggi='".$tinggi."'";
           $qryCount=$owlPDO->query($strCount) or die(print " Gagal: ".PDOException::getMessage());
            $numRows=owlBaris($qryCount);
			if($numRows>=1){
				echo "Gagal : Item ini sudah ada didatabase.";
			}else{
				$str="insert into ".$dbname.".pabrik_5tinggitangki(millcode,kodetangki,tinggi,volume,beda,updateby) values ('".$kodeorg."','".$kodetangki."','".$tinggi."','".$volume."','".$beda."','".$_SESSION['standard']['userid']."')";
                              try
                                {
                                    $owlPDO->exec($str);  
                                    loadData();
                                }
                                catch (PDOException $e) 
                                {
                                    print " Gagal  !: " . $e->getMessage() . "\n"; 
                                    die(); 
                                }
			}
		}
	break;

	case 'update':
		$str="update ".$dbname.".pabrik_5tinggitangki set tinggi='".$tinggi."', volume='".$volume."', beda='".$beda."', updateby='".$_SESSION['standard']['userid']."' where millcode='".$kodeorg."' and kodetangki='".$kodetangki."' and tinggi='".$tinggi."'";
                try
                {
                    $owlPDO->exec($str);  
                    loadData();
                }
                catch (PDOException $e) 
                {
                    print " Gagal  !: " . $e->getMessage() . "\n"; 
                    die(); 
                }
            
	break;
	
	case 'loadData':
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".pabrik_5tinggitangki where millcode='".$_SESSION['empl']['lokasitugas']."' ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=9>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="select * from ".$dbname.".pabrik_5tinggitangki where millcode='".$_SESSION['empl']['lokasitugas']."' order by tinggi asc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $qry->setFetchMode(PDO::FETCH_OBJ);
            while($res=$qry->fetch()){
                $no+=1;
				$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$res->updateby."'");
                $tab.="<tr class=rowcontent>
						<td style='text-align:center;'>".$no."</td>
						<td>".$res->millcode."</td>
						<td>".$res->kodetangki."</td>
						<td style='text-align:right;'>".number_format($res->tinggi)."</td>
						<td style='text-align:right;'>".number_format($res->volume)."</td>
						<td style='text-align:right;'>".number_format($res->beda)."</td>
						<td style='text-align:right;'>".$nmKar[$res->updateby]."</td>
						<td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->millcode."','".$res->kodetangki."','".$res->tinggi."','".$res->volume."','".$res->beda."')\"></td>
						<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$res->millcode."','".$res->kodetangki."','".$res->tinggi."')\"></td>
					   </tr>";
            }
            $totrows=ceil($jlhbrs/$limit);

            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=9 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".pabrik_5tinggitangki where millcode='".$kodeorg."' and kodetangki='".$kodetangki."' and tinggi='".$tinggi."'";
                try
                {
                    $owlPDO->exec($str);  
                    loadData();
                }
                catch (PDOException $e) 
                {
                    print " Gagal  !: " . $e->getMessage() . "\n"; 
                    die(); 
                }
	break;
	
	case 'pdf':
		class masterpdf extends FPDF {
			function Header() {
				global $conn;
				global $dbname;
				
				$width = $this->w - $this->lMargin - $this->rMargin;
				$height = 12;
				$this->SetFont('Arial','B',8);
				$this->Cell(20,$height,$_SESSION['org']['namaorganisasi'],'',1,'L');
				$this->SetFont('Arial','B',12);
		
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['tinggitangki']),'',1,'C');
				$this->SetFont('Arial','B',8);
				$this->Cell(415,$height,' ','',0,'R');
				$this->Cell(40,$height,$_SESSION['lang']['tanggal'],'',0,'L');
				$this->Cell(5,$height,':','',0,'L');
				$this->Cell(40,$height,date('d-m-Y H:i'),'',1,'L');
				$this->Cell(415,$height,' ','',0,'R');
				$this->Cell(40,$height,$_SESSION['lang']['page'],'',0,'L');
				$this->Cell(8,$height,':','',0,'L');
				$this->Cell(15,$height,$this->PageNo(),'',1,'L');
		
				$this->Cell(100,$height,'','',0,'L');
				$this->Cell(315,$height,' ','',0,'R');
				$this->Cell(40,$height,$_SESSION['lang']['user'],'',0,'L');
				$this->Cell(8,$height,':','',0,'L');
				$this->Cell(20,$height,$_SESSION['standard']['username'],'',1,'L');
				$this->Ln();
        
				$this->Cell(70,1.5*$height,$_SESSION['lang']['kodeorganisasi'],'TBLR',0,'C');
				$this->Cell(70,1.5*$height,$_SESSION['lang']['kodetangki'],'TBR',0,'C');
				$this->Cell(80,1.5*$height,'Tinggi (Cm)','TBR',0,'C');
				$this->Cell(80,1.5*$height,$_SESSION['lang']['volume']." (liter)",'TBR',0,'C');
				$this->Cell(80,1.5*$height,$_SESSION['lang']['beda']." (liter)",'TBR',0,'C');
				$this->Ln();
			}
		}

		#====================== Prepare PDF Setting
		$pdf = new masterpdf('P','pt','A4');
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$height = 14;
		$pdf->SetFont('Arial','',8);
		$pdf->AddPage();
                
                
                $kodeorg=$_SESSION['empl']['lokasitugas'];
		# Generate Data
		$str="select * from ".$dbname.".pabrik_5tinggitangki where millcode = '".$kodeorg."'";
		$result = fetchData($str);
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $numRows=owlBaris($res);
		if($numRows==0){
			$pdf->Cell(380,$height,$_SESSION['lang']['errdatanotexist'],'BRL',0,'C');
		}else{
			foreach($result as $data) {
				$pdf->Cell(70,$height,$data['millcode'],'BRL',0,'L');
				$pdf->Cell(70,$height,$data['kodetangki'],'BRL',0,'L');
				$pdf->Cell(80,$height,$data['tinggi'],'BRL',0,'R');
				$pdf->Cell(80,$height,$data['volume'],'BRL',0,'R');
				$pdf->Cell(80,$height,$data['beda'],'BRL',0,'R');
				$pdf->Ln();
			}
		}
		
		# Print Out
		$pdf->Output();
	break;

    default:
    break;
}
?>