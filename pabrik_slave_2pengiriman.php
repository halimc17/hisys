<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses']))
{
        $proses=$_POST['proses'];
}
else
{
        $proses=$_GET['proses'];
}

//$arr="##kdPabrik##tgl_1##tgl_2##kdCust##nkntrak##kdBrg";
$kdPabrik=checkPostGet('kdPabrik','');
$kdCust=checkPostGet('kdCust','');
$nkntrak=checkPostGet('nkntrak','');
$kdBrg=checkPostGet('kdBrg','');
$tgl_1=tanggalsystem(checkPostGet('tgl_1',''));
$tgl_2=tanggalsystem(checkPostGet('tgl_2',''));
$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$kdCustomer=checkPostGet('kdCustomer','');
$periode = checkPostGet('periode','-');
$wr="kodekelompok='S003'";
$optSupp=makeOption($dbname, 'log_5supplier', 'kodetimbangan,namasupplier',$wr);

$subtota=$subjjg=0;

switch($proses)
{
    case'preview':
        if($kdPabrik=='')
        {
                echo"warning: Please choose mill";
                exit();
        }
        if($tgl_1==''&&$tgl_2=='')
        {
                echo "warning:Date required";
                exit();
        }
        $whr="";
        if($kdBrg!=''){
                $whr.=" and kodebarang ='".$kdBrg."'";
        }
        if($nkntrak!=''){
                $whr.=" and nokontrak ='".$nkntrak."'";
        }
         if($kdCust!=''){
                $whr.=" and kodecustomer ='".$kdCust."'";
        }

        $sTimbangan="select kodebarang,notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,jammasuk,jamkeluar,beratmasuk,
                     substr(tanggal,1,10) as tanggal,supir,nokendaraan,nodo,nokontrak,kgpembeli,beratbersih,beratkeluar,nosipb from ".$dbname.".pabrik_timbangan
                     where  millcode='".$kdPabrik."' and kodebarang!='40000003' ".$whr." and  tanggal >= ".$tgl_1."000000 and tanggal<=".$tgl_2."235959"; 
                     //echo $sTimbangan;
        echo"<table cellspacing=1 border=0 class=sortable width=100%>
        <thead class=rowheader>
        <tr>
                <td align=center>No.</td>
                <td align=center>".$_SESSION['lang']['materialname']."</td>
                <td align=center>".$_SESSION['lang']['tanggal']."</td>
                <td align=center>".$_SESSION['lang']['transporter']."</td>
                <td align=center>".$_SESSION['lang']['vendor']."</td>
                <td align=center>".$_SESSION['lang']['noTiket']."</td>
                <td align=center>".$_SESSION['lang']['kodenopol']."</td>
                
                <td align=center>".$_SESSION['lang']['beratMasuk']."</td>
                <td align=center>".$_SESSION['lang']['beratKeluar']."</td>
                <td align=center>".$_SESSION['lang']['beratBersih']."</td>
				<td align=center width=70px>".$_SESSION['lang']['beratBersih']." ".substr($_SESSION['lang']['kodecustomer'],5)."</td>
                <td align=center>".$_SESSION['lang']['jammasuk']."</td>
                <td align=center>".$_SESSION['lang']['jamkeluar']."</td>
                <td align=center>".$_SESSION['lang']['sopir']."</td>
                <td align=center>".$_SESSION['lang']['nodo']."</td>
                <td align=center>".$_SESSION['lang']['NoKontrak']."</td>

        </tr>
        </thead>
        <tbody>";

        $qData=$owlPDO->query($sTimbangan) or die(print " Gagal: ".PDOException::getMessage());
        $brs=owlBaris($qData);
        if($brs>0)
        {
			$subtota=0;

                $qData->setFetchMode(PDO::FETCH_ASSOC);
                while($rData=$qData->fetch())
                {

                        $no+=1;

                        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rData['kodebarang']."'";

                        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                        $rBrg=$qBrg->fetch();
                        
                        $sKntrk="select koderekanan from ".$dbname.".pmn_kontrakjual where nokontrak='".$rData['nokontrak']."'";
                       
                        $qKntrk=$owlPDO->query($sKntrk) or die(print " Gagal: ".PDOException::getMessage());
                        $qKntrk->setFetchMode(PDO::FETCH_ASSOC);
                        $rKntrak=$qKntrk->fetch();
                        
                        $sNama="select namacustomer from ".$dbname.".pmn_4customer where kodecustomer='".$rKntrak['koderekanan']."'";
                        
                        $qNama=$owlPDO->query($sNama) or die(print " Gagal: ".PDOException::getMessage());
                        $qNama->setFetchMode(PDO::FETCH_ASSOC);
                        $rNama=$qNama->fetch();
                        
                        $sTrans="select TRPCODE from ".$dbname.".pabrik_mssipb where SIPBNO='".$rData['nosipb']."'";
                        
                        $qTrans=$owlPDO->query($sTrans) or die(print " Gagal: ".PDOException::getMessage());
                        $qTrans->setFetchMode(PDO::FETCH_ASSOC);
                        $rTrans=$qTrans->fetch();
						
                        setIt($optSupp[$rData['kodecustomer']],'');
						echo"
                        <tr class=rowcontent>
                        <td align=center>".$no."</td>
                        <td>".$rBrg['namabarang']."</td>
                        <td>".tanggalnormal($rData['tanggal'])."</td>
                        <td>".$optSupp[$rData['kodecustomer']]."</td>
                        <td>".$rNama['namacustomer']."</td>
                        <td>".$rData['notransaksi']."</td>
                        <td>".$rData['nokendaraan']."</td>
                        <td  align=right>".number_format($rData['beratmasuk'],2)."</td>
                        <td  align=right>".number_format($rData['beratkeluar'],2)."</td>
                        <td  align=right>".number_format($rData['netto'],2)."</td>
						<td  align=right>".number_format($rData['kgpembeli'],2)."</td>
                        <td>".$rData['jammasuk']."</td>
                        <td>".$rData['jamkeluar']."</td>
                        <td>".$rData['supir']."</td>
                        <td>".$rData['nosipb']."</td>
                        <td>".$rData['nokontrak']."</td>
                        </tr>";
                        $subtota+=$rData['netto'];
                        $ttlmasuk+=$rData['beratmasuk'];
                        $ttlkeluar+=$rData['beratkeluar'];
                        $ttlpembeli+=$rData['kgpembeli'];

                }
                echo"<tr class=rowcontent >
				<td colspan=7 align=center>TOTAL (KG)</td>
				<td align=right>".number_format($ttlmasuk,2)."</td>
				<td align=right>".number_format($ttlkeluar,2)."</td>
				<td align=right>".number_format($subtota,2)."</td>
				<td align=right>".number_format($ttlpembeli,2)."</td>
				<td></td><td></td><td></td><td></td><td></td>
				</tr>";

        }
        else
        {
                echo"<tr class=rowcontent><td colspan=16 align=center>".$_SESSION['lang']['datanotfound']."</td></tr>";
        }
        echo"</tbody></table>";
        break;
        case'pdf':
        $kdCust=$_GET['kdCust'];

        $nkntrak=$_GET['nkntrak'];
        $kdBrg=$_GET['kdBrg'];
		$tglPeriode=explode("-",$periode);
        $tanggal=$tglPeriode[1]."-".$tglPeriode[0];
        $tgl_1=tanggalsystem($_GET['tgl_1']);
        $tgl_2=tanggalsystem($_GET['tgl_2']);
        $kdPabrik=$_GET['kdPabrik'];

        $rNmBrg=array();
         class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $kdCust;
				global $nkntrak;
				global $kdBrg;
				global $kdPabrik;
				global $tgl_2;
				global $tgl_1;
				global $tglPeriode;
				global $tanggal;
				global $periode;
				global $rNamaSupp;
				global $rNmBrg;
                                global $owlPDO;



				$tglPeriode=explode("-",$periode);
				$tanggal=$tglPeriode[1]."-".$tglPeriode[0];

				$sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";

                                $qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
                                $qAlamat->setFetchMode(PDO::FETCH_ASSOC);
                                $rAlamat=$qAlamat->fetch();

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin-5,0,43);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(75);   
                $this->Cell($width-75,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
                $this->SetX(75); 		
                $this->Cell($width-75,$height,$rAlamat['alamat'],0,1,'L');	
                $this->SetX(75); 			
                $this->Cell($width-75,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();	
                $this->Ln();
                                $this->Ln();
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['rPengiriman']),0,1,'C');	
                                $this->SetFont('Arial','',8);
                                $sNm="select namasupplier,kodetimbangan from ".$dbname.".log_5supplier order by namasupplier asc";

                                $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
                                $qNm->setFetchMode(PDO::FETCH_ASSOC);
                                while($rNm=$qNm->fetch())
                                {
                                        $rNamaSupp[$rNm['kodetimbangan']]=$rNm;
                                }
                                $sBrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";

                                $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                                $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                                while($rBrg=$qBrg->fetch())
                                {
                                    $rNmBrg[$rBrg['kodebarang']]=$rBrg;
                                }
								setIt($rNmBrg[$kdBrg]['namabarang'],0);
								
                                if(($kdPabrik!='')&&($kdCust!=''))
                                {
                                    $this->Cell($width,$height, $_SESSION['lang']['rPengiriman']." : ".$kdPabrik." atas ".$rNmBrg[$kdBrg]['namabarang']." ".$_SESSION['lang']['ke']." ".$rNamaSupp[$kdCust]['namasupplier']." ".$_SESSION['lang']['periode']." :".$tgl_1."-".$tgl_2,0,1,'C');	
                                }
                                else
                                {
                                        $this->Cell($width,$height, $_SESSION['lang']['rPengiriman']." : ".$kdPabrik." atas ".$rNmBrg[$kdBrg]['namabarang']." ".$_SESSION['lang']['ke']." : ".$_SESSION['lang']['all'].", ".$_SESSION['lang']['periode']." :".tanggalnormal($tgl_1)." - ".tanggalnormal($tgl_2),0,1,'C');						
                                }
                                $this->Ln();$this->Ln();
                                $this->SetFont('Arial','B',6);	
                                $this->SetFillColor(220,220,220);

                                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                                $this->Cell(15/100*$width,$height,$_SESSION['lang']['materialname'],1,0,'C',1);
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);		
                                $this->Cell(15/100*$width,$height,$_SESSION['lang']['vendor'],1,0,'C',1);		
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['noTiket'],1,0,'C',1);	
                                $this->Cell(12/100*$width,$height,$_SESSION['lang']['kodenopol'],1,0,'C',1);	
                                $this->Cell(9/100*$width,$height,$_SESSION['lang']['beratBersih'],1,0,'C',1);	
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['sopir'],1,0,'C',1);			
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['nodo'],1,0,'C',1);
                                $this->Cell(12/100*$width,$height,$_SESSION['lang']['NoKontrak'],1,1,'C',1);
                                //$this->Cell(9/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,1,'C',1);	            
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
        $height = 9;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',6);


         if($kdPabrik=='')
         {
             echo"warning: Please choose mill";
             exit();
         }
         if($tgl_1==''&&$tgl_2=='')
         {
             echo "warning:Date required";
             exit();
         }
        $whr="";
         if($kdBrg!=''){
             $whr.=" and kodebarang ='".$kdBrg."'";
         }
         if($nkntrak!=''){
             $whr.=" and nokontrak ='".$nkntrak."'";
         }
          if($kdCust!=''){
             $whr.=" and kodecustomer ='".$kdCust."'";
         }

        $sTimbangan="select kodebarang,notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,jammasuk,jamkeluar,beratmasuk,
                     substr(tanggal,1,10) as tanggal,supir,nokendaraan,nodo,nokontrak,kgpembeli,beratbersih,beratkeluar,nosipb from ".$dbname.".pabrik_timbangan
                     where  millcode='".$kdPabrik."' and kodebarang!='40000003' ".$whr." and  tanggal >= ".$tgl_1."000000 and tanggal<=".$tgl_2."235959"; 

        $qList=$owlPDO->query($sTimbangan) or die(print " Gagal: ".PDOException::getMessage());
        $qList->setFetchMode(PDO::FETCH_ASSOC);
        while($rData=$qList->fetch())
        {
                    
                    $sKntrk="select koderekanan from ".$dbname.".pmn_kontrakjual where nokontrak='".$rData['nokontrak']."'";

                    $qKntrk=$owlPDO->query($sKntrk) or die(print " Gagal: ".PDOException::getMessage());
                    $qKntrk->setFetchMode(PDO::FETCH_ASSOC);
                    $rKntrak=$qKntrk->fetch();
                    
                    
                    
                    $sNama="select namacustomer from ".$dbname.".pmn_4customer where kodecustomer='".$rKntrak['koderekanan']."'";

                    $qNama=$owlPDO->query($sNama) or die(print " Gagal: ".PDOException::getMessage());
                    $qNama->setFetchMode(PDO::FETCH_ASSOC);
                    $rNama=$qNama->fetch();
                    
                    
                    $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rData['kodebarang']."'";

                    $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                    $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rBrg=$qBrg->fetch();


                        $no+=1;
                        $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                        $pdf->Cell(15/100*$width,$height,$rBrg['namabarang'],1,0,'L',1);
                        $pdf->Cell(8/100*$width,$height,tanggalnormal($rData['tanggal']),1,0,'C',1);		
                        $pdf->Cell(15/100*$width,$height,$rNama['namacustomer'],1,0,'L',1);		
                        $pdf->Cell(8/100*$width,$height,$rData['notransaksi'],1,0,'L',1);	
                        $pdf->Cell(12/100*$width,$height,$rData['nokendaraan'],1,0,'L',1);	
                        $pdf->Cell(9/100*$width,$height,number_format($rData['netto'],2),1,0,'R',1);	
                        $pdf->Cell(10/100*$width,$height,$rData['supir'],1,0,'L',1);			
                        $pdf->Cell(10/100*$width,$height,$rData['nosipb'],1,0,'L',1);
                        $pdf->Cell(12/100*$width,$height,$rData['nokontrak'],1,1,'L',1);
                       
                        $subtota+=$rData['netto'];
                        $subjjg+=$rData['jjg'];
                }
                $pdf->Cell(61/100*$width,$height,"Total",1,0,'R',1);
                $pdf->Cell(9/100*$width,$height,number_format($subtota,2),1,0,'R',1);
                $pdf->Cell(32/100*$width,$height,"",1,1,'C',1);
                //$pdf->Cell(10/100*$width,$height,number_format($subjjg,2),1,1,'R',1);


    $pdf->Output();
        break;
        case'excel':
         //   $arr="##kdPabrik##tgl_1##tgl_2##kdCust##nkntrak##kdBrg";
        $kdCust=$_GET['kdCust'];
        $nkntrak=$_GET['nkntrak'];
        $kdBrg=$_GET['kdBrg'];
        $tglPeriode=explode("-",$periode);
        $tanggal=$tglPeriode[1]."-".$tglPeriode[0];
        $tgl_1=tanggalsystem($_GET['tgl_1']);
        $tgl_2=tanggalsystem($_GET['tgl_2']);
        $kdPabrik=$_GET['kdPabrik'];
        $sNm="select namasupplier,kodetimbangan from ".$dbname.".log_5supplier order by namasupplier asc";

        $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
        $qNm->setFetchMode(PDO::FETCH_ASSOC);
        while($rNm=$qNm->fetch())
        {
                $rNamaSupp[$rNm['kodetimbangan']]=$rNm;
        }
        $sBrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";

        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
        while($rBrg=$qBrg->fetch())
        {
                $rNmBrg[$rBrg['kodebarang']]=$rBrg;
        }

        $tab.="<table cellspacing=\"1\" border=0><tr><td colspan=16 align=center>".strtoupper($_SESSION['lang']['rPengiriman'])."</td></tr>
        ";
		setIt($rNmBrg[$kdBrg]['namabarang'],'');
        if(($kdPabrik!='')&&($kdCust!=''))
        {
                $tab.="<tr><td colspan=2 align=right>".$_SESSION['lang']['pengirimanBrg']." </td><td colspan=14>:".$kdPabrik." atas ".$rNmBrg[$kdBrg]['namabarang']." ".$_SESSION['lang']['ke']." ".$rNamaSupp[$kdCust]['namasupplier']." ".$_SESSION['lang']['periode']." : ".$tgl_1."-".$tgl_2."</td></tr>";
        }
        else
        {
                $tab.="<tr><td colspan=2 align=right>".$_SESSION['lang']['pengirimanBrg']."</td><td colspan=14>".$kdPabrik." atas ".$rNmBrg[$kdBrg]['namabarang']." ".$_SESSION['lang']['ke']." ".$_SESSION['lang']['all']." ".$_SESSION['lang']['periode']." : ".tanggalnormal($tgl_1)."-".tanggalnormal($tgl_2)."</td></tr>";
        }
        $tab.="</table>";


        if($kdPabrik=='')
         {
             echo"warning: Please choose mill";
             exit();
         }
         if($tgl_1==''&&$tgl_2=='')
         {
             echo "warning: Date required";
             exit();
         }
        $whr="";
         if($kdBrg!=''){
             $whr.=" and kodebarang ='".$kdBrg."'";
         }
         if($nkntrak!=''){
             $whr.=" and nokontrak ='".$nkntrak."'";
         }
          if($kdCust!=''){
             $whr.=" and kodecustomer ='".$kdCust."'";
         }

        $sTimbangan="select kodebarang,notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,jammasuk,jamkeluar,beratmasuk,
                     substr(tanggal,1,10) as tanggal,supir,nokendaraan,nodo,nokontrak,kgpembeli,beratbersih,beratkeluar,nosipb from ".$dbname.".pabrik_timbangan
                     where  millcode='".$kdPabrik."' and kodebarang!='40000003' ".$whr." and  tanggal >= ".$tgl_1."000000 and tanggal<=".$tgl_2."235959"; 
        $tab.="<table cellspacing=1 border=1 class=sortable>
        <thead class=rowheader>
        <tr>
                <td bgcolor=#DEDEDE> No.</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['materialname']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['tanggal']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['transporter']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['vendor']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['noTiket']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['kodenopol']."</td>
                
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['beratMasuk']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['beratKeluar']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['beratBersih']."</td>
				<td bgcolor=#DEDEDE>".$_SESSION['lang']['beratBersih']." ".substr($_SESSION['lang']['kodecustomer'],5)."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['jammasuk']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['jamkeluar']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['sopir']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['nodo']."</td>
                <td bgcolor=#DEDEDE>".$_SESSION['lang']['NoKontrak']."</td>
        </tr>
        </thead>
        <tbody>";
        //notransaksi, tanggal, kodeorg, kodecustomer, bjr, jumlahtandan1, kodebarang, jammasuk, beratmasuk, jamkeluar, beratkeluar, nokendaraan, supir, nospb, petugassortasi, timbangonoff, statussortasi, nokontrak, nodo, intex, nosipb, thntm1, thntm2, thntm3, jumlahtandan2, jumlahtandan3, brondolan, username, millcode, beratbersih
        $sData="select notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,supir,nokendaraan,nodo,nokontrak from ".$dbname.".pabrik_timbangan where nokontrak!='' ".$where;
        //echo $sData;
        //echo "warning".$sData;exit()
        $qData=$owlPDO->query($sTimbangan) or die(print " Gagal: ".PDOException::getMessage());
        $brs=owlBaris($qData);
        if($brs>0){
                $qData->setFetchMode(PDO::FETCH_ASSOC);
                while($rData=$qData->fetch())
                {    
                        $no+=1;

                        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rData['kodebarang']."'";

                        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                        $rBrg=$qBrg->fetch();
                        
                        $sKntrk="select koderekanan from ".$dbname.".pmn_kontrakjual where nokontrak='".$rData['nokontrak']."'";

                        $qKntrk=$owlPDO->query($sKntrk) or die(print " Gagal: ".PDOException::getMessage());
                        $qKntrk->setFetchMode(PDO::FETCH_ASSOC);
                        $rKntrak=$qKntrk->fetch();
                        
                        $sNama="select namacustomer from ".$dbname.".pmn_4customer where kodecustomer='".$rKntrak['koderekanan']."'";

                        $qNama=$owlPDO->query($sNama) or die(print " Gagal: ".PDOException::getMessage());
                        $qNama->setFetchMode(PDO::FETCH_ASSOC);
                        $rNama=$qNama->fetch();
                        
                        $sTrans="select TRPCODE from ".$dbname.".pabrik_mssipb where SIPBNO='".$rData['nosipb']."'";

                        $qTrans=$owlPDO->query($sTrans) or die(print " Gagal: ".PDOException::getMessage());
                        $qTrans->setFetchMode(PDO::FETCH_ASSOC);
                        $rTrans=$qTrans->fetch();
						setIt($optSupp[$rData['kodecustomer']],'');
                        $tab.="
                        <tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rBrg['namabarang']."</td>    
                        <td>".tanggalnormal($rData['tanggal'])."</td>
                        <td>".$optSupp[$rData['kodecustomer']]."</td>
                        <!--<td>".$optSupp[$rTrans['TRPCODE']]."</td>-->
                        <td>".$rNama['namacustomer']."</td>
                        <td>".$rData['notransaksi']."</td>
                        <td>".$rData['nokendaraan']."</td>
                        
                        <td  align=right>".number_format($rData['beratmasuk'],2)."</td>
                        <td  align=right>".number_format($rData['beratkeluar'],2)."</td>
                        <td  align=right>".number_format($rData['netto'],2)."</td>
						<td  align=right>".number_format($rData['kgpembeli'],2)."</td>
                        <td>".$rData['jammasuk']."</td>
                        <td>".$rData['jamkeluar']."</td>
                        <td>".$rData['supir']."</td>
                        <td>".$rData['nosipb']."</td>
                        <td>".$rData['nokontrak']."</td>
                        </tr>";
						$subtota+=$rData['netto'];
                        $ttlmasuk+=$rData['beratmasuk'];
                        $ttlkeluar+=$rData['beratkeluar'];
                        $ttlpembeli+=$rData['kgpembeli'];

                }
                $tab.="<tr class=rowcontent >
				<td colspan=7 align=center>TOTAL (KG)</td>
				<td align=right>".number_format($ttlmasuk,2)."</td>
				<td align=right>".number_format($ttlkeluar,2)."</td>
				<td align=right>".number_format($subtota,2)."</td>
				<td align=right>".number_format($ttlpembeli,2)."</td>
				<td></td><td></td><td></td><td></td><td></td>
				</tr>";
						
						
						// $subtota+=$rData['netto'];

                // }
                // $tab.="<tr class=rowcontent ><td colspan=7 align=right>Total (KG)</td><td align=right>".number_format($subtota,2)."</td><td colspan=8 align=right>&nbsp;</td></tr>";

        }
        else
        {
                $tab.="<tr class=rowcontent><td colspan=16 align=center>".$_SESSION['lang']['datanotfound']."</td></tr>";
        }



                        $tab.="</tbody></table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
                        $tglSkrg=date("Ymd");
                        $nop_="LaporanPengiriman".$tglSkrg;
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
        case'getKontrakData':

         $sChek="select nokontrak from ".$dbname.".pmn_kontrakjual where koderekanan='".$kdCustomer."' order by nokontrak desc"; //and tanggalkontrak >= ".$tgl1." and tanggalkontrak<=".$tgl2."";
//exit("Error".$sChek);
         $qChek=$owlPDO->query($sChek) or die(print " Gagal: ".PDOException::getMessage());
         $qChek->setFetchMode(PDO::FETCH_ASSOC);
         $brs=owlBaris($qChek);
         if($brs>0)
         {
             $optKontrak="<option value=''>".$_SESSION['lang']['all']."</opton>";
             while($rCheck=$qChek->fetch())
             {
                 $optKontrak.="<option value=".$rCheck['nokontrak'].">".$rCheck['nokontrak']."</option>";
             }
             echo $optKontrak;
         }
         else
         {
             $optKontrak="<option value=''>".$_SESSION['lang']['all']."</opton>";
             echo $optKontrak;
            //exit();
         }
        break;
        
        
        
        case'getCust':
            $rt=explode("-",checkPostGet('tgl1','-'));
            $rt2=explode("-",checkPostGet('tgl2','-'));
            $tgl1=$rt[2]."-".$rt[1]."-".$rt[0];
            $tgl2=$rt2[2]."-".$rt2[1]."-".$rt2[0];
        $optCust="<option value=''>".$_SESSION['lang']['all']."</option>";

        $sCust="select distinct a.kodecustomer,b.namasupplier from ".$dbname.".pabrik_timbangan a left join
                ".$dbname.".log_5supplier b on a.kodecustomer=b.kodetimbangan where 
                left(tanggal,10) between '".$tgl1."' and '".$tgl2."' and millcode='".$_POST['kdPabrik']."'
                and kodebarang='".$_POST['kdBrg']."'
                order by b.namasupplier asc";

        
        $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
        $qCust->setFetchMode(PDO::FETCH_ASSOC);
        while($rCust=$qCust->fetch())
        {
            $optCust.="<option value=".$rCust['kodecustomer'].">".$rCust['namasupplier']." [".$rCust['kodecustomer']."]</option>";
        }
        $optKontrak="<option value=''>".$_SESSION['lang']['all']."</opton>";
         $sChek="select distinct nokontrak from ".$dbname.".pabrik_timbangan where  
                 left(tanggal,10) between '".$tgl1."' and '".$tgl2."'  and millcode='".$_POST['kdPabrik']."' 
                 and kodebarang='".$_POST['kdBrg']."'
                 order by tanggal asc";

         $qChek=$owlPDO->query($sChek) or die(print " Gagal: ".PDOException::getMessage());
         $brs=owlBaris($qChek);
         if($brs>0)
         {
             $optKontrak="<option value=''>".$_SESSION['lang']['all']."</opton>";

             $qChek->setFetchMode(PDO::FETCH_ASSOC);
             while($rCheck=$qChek->fetch())
             {
                 $optKontrak.="<option value=".$rCheck['nokontrak'].">".$rCheck['nokontrak']."</option>";
             }
              
         }
        echo $optCust."####".$optKontrak;
        break;
}
?>