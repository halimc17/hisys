<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');

$_POST['proses']==''?$proses=$_GET['proses']:$proses=$_POST['proses'];


$arrNmkary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$arrKeputusan=array("0"=>$_SESSION['lang']['diajukan'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak']);
$where=" tanggal='".$tglijin."' and karyawanid='".$krywnId."'";
$optJumlah=makeOption($dbname, 'pta_dt', 'notransaksi,jumlah');
$arragama=getEnum($dbname,'sdm_ijin','jenisijin');
$optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optBarang=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNamaAkun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$_POST['notransaksi']==''?$notransaksi=$_GET['notransaksi']:$notransaksi=$_POST['notransaksi'];
$krywnId=$_POST['krywnId'];
$stat=$_POST['stat'];
$ket=$_POST['ket'];
$perKe=$_POST['perKe'];
$txtCari=$_POST['txtCari'];
$arrKeputusan=array("0"=>"Belum Ada Status","1"=>"Setuju","2"=>"Ditolak");
	
    switch($proses)
	{
		
		
		case'loadData':
                    if($_POST['txtCari']=='')
                    {
                        $periodeAktif=date("Y-m");
                        $where="substr(tanggal,1,7)='".$periodeAktif."'";
                    }
                    else
                    {
                        $where="notransaksi like '%".$_POST['txtCari']."%'";
                    }
		$limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		
        $ql2="select count(*) as jmlhrow from ".$dbname.".pta_ht where ".$where."  order by `tanggal` desc";// echo $ql2;
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
		while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
		}
		
		//$slvhc="select * from ".$dbname.".sdm_ijin where  karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."') order by `tanggal` desc limit ".$offset.",".$limit." ";
        $slvhc="select * from ".$dbname.".pta_ht where ".$where."   order by `tanggal` desc limit ".$offset.",".$limit." ";
        $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
        $qlvhc->setFetchMode(PDO::FETCH_ASSOC);
		$user_online=$_SESSION['standard']['userid'];
		while($rlvhc=$qlvhc->fetch())
		{
                    $no+=1;
                    $sData="select sum(rupiah) as rupiah from ".$dbname.".pta_dt where notransaksi='".$rlvhc['notransaksi']."'";
                    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                    $qData->setFetchMode(PDO::FETCH_ASSOC);
                    $rData=$qData->fetch();
                    echo"
                    <tr class=rowcontent>
                    <td>".$no."</td>
                    <td>".$rlvhc['notransaksi']."</td>
                    <td>".$rlvhc['penjelasan']."</td>
                    <td align=right>".number_format($rData['rupiah'],2)."</td>";
                   
                    if(($rlvhc['persetujuan5']==$_SESSION['standard']['userid'])&&($rlvhc['status5']==0))
                    {
                         echo"<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('".$rlvhc['notransaksi']."',event)\"></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppTolak('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan5']."','5',event)>".$_SESSION['lang']['ditolak']."</button></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppSetuju('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan5']."','5',event)>".$_SESSION['lang']['disetujui']."</button></td>";
                    }
                    elseif(($rlvhc['persetujuan4']==$_SESSION['standard']['userid'])&&($rlvhc['status4']==0))
                    { 
                        echo"<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('".$rlvhc['notransaksi']."',event)\"></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppTolak('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan4']."','4',event)>".$_SESSION['lang']['ditolak']."</button></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=appSetuju('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan4']."','4')>".$_SESSION['lang']['disetujui']."</button></td>";
                    }
                    elseif(($rlvhc['persetujuan3']==$_SESSION['standard']['userid'])&&($rlvhc['status3']==0))
                    {
                        echo"<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('".$rlvhc['notransaksi']."',event)\"></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppTolak('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan3']."','3',event)>".$_SESSION['lang']['ditolak']."</button></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppSetuju('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan3']."','3',event)>".$_SESSION['lang']['disetujui']."</button></td>";
                    }
                    elseif(($rlvhc['persetujuan2']==$_SESSION['standard']['userid'])&&($rlvhc['status2']==0))
                    {
                        echo"<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('".$rlvhc['notransaksi']."',event)\"></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppTolak('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan2']."','2',event)>".$_SESSION['lang']['ditolak']."</button></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppSetuju('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan2']."','2',event)>".$_SESSION['lang']['disetujui']."</button></td>";
                    }
                    elseif(($rlvhc['persetujuan1']==$_SESSION['standard']['userid'])&&($rlvhc['status1']==0))
                    {
                        echo"<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('".$rlvhc['notransaksi']."',event)\"></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppTolak('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan1']."','1',event)>".$_SESSION['lang']['ditolak']."</button></td>";
                        echo"<td align=center><button class=mybutton id=dtlForm onclick=showAppSetuju('".$rlvhc['notransaksi']."','".$rlvhc['persetujuan1']."','1',event)>".$_SESSION['lang']['disetujui']."</button></td>";
                    }
                    else
                    {
                        echo"<td align=center colspan=3><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('".$rlvhc['notransaksi']."',event)\"></td>";
                    }
            }//end while
		echo"
		</tr><tr class=rowheader><td colspan=9 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		break;
        case'appSetuju':
                if($stat=='2')
                {
                    if($ket=='')
                    {
                        exit("Error:Catatan Tidak Boleh Kosong!!!");
                    }
                    $sUpdate="update ".$dbname.".pta_ht set status".$perKe."='2',catatan".$perKe."='".$ket."' 
                     where persetujuan".$perKe."='".$krywnId."' and status".$perKe."='0' and notransaksi='".$notransaksi."'";
                }
                elseif($stat=='1')
                {
                    $sUpdate="update ".$dbname.".pta_ht set status".$perKe."='1',catatan".$perKe."='".$ket."' 
                     where persetujuan".$perKe."='".$krywnId."' and status".$perKe."='0' and notransaksi='".$notransaksi."'";
                }
                elseif($stat=='3')
                {
                    $forwrd=$perKe+1;
                    if($forwrd>5)
                    {
                        exit("Error:Limit pengajuan hanya 5 saja");
                    }
                    $sKary="select distinct persetujuan".$perKe." from ".$dbname.".pta_ht where notransaksi='".$notransaksi."'";
                    $qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
                    $qKary->setFetchMode(PDO::FETCH_ASSOC);
                    $rKary=$qKary->fetch();
                    $sUpdate="update ".$dbname.".pta_ht set status".$perKe."='1',catatan".$perKe."='".$ket."',persetujuan".$forwrd."='".$krywnId."' 
                     where persetujuan".$perKe."='".$rKary['persetujuan'.$perKe]."' and status".$perKe."='0' and notransaksi='".$notransaksi."'";
                }

            try{
                $owlPDO->exec($sUpdate);
                if($stat=='1')
                    {
                        $to=getUserEmail($krywnId); 
                            $subject="[Notifikasi] Persetujuan PTA ";
                                $body="<html>
                                        <head>
                                        <body>
                                        <dd>Dengan Hormat,</dd><br>
                                        <br>
                                        Pada hari ini karyawan A/n ".$_SESSION['empl']['name']." mengajukan persetujuan PTA 
                                        No.".$notransaksi." kepada bapak/ibu, untuk menindaklanjuti silahkan click link dibawah.
                                        <br>
                                        <br>
                                        <br>
                                        Regards,<br>
                                        Owl-Plantation System.
                                        </body>
                                        </head>
                                    </html>";
                                $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;  
                    }                
            }catch (PDOException $e){
                    echo "error : ".$e->getMessage();
            } 

		break;
		case'getForm':
                    $optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $sKary="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan 
                          where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and karyawanid!='".$krywnId."' order by namakaryawan asc";
                    $qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
                    $qKary->setFetchMode(PDO::FETCH_ASSOC);
                    while($rKary=$qKary->fetch())
                    {
                        $optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
                    }
                    $tab.="<fieldset><legend>".$notransaksi."</legend>";
                    $tab.="<table cellpadding=1 cellspacing=1 border=0>";
                    $tab.="<tr><td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td><td><select id=dtKary>".$optKary."</select></td></tr>";
                    $tab.="<tr><td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td><textarea id=koments onkeypress=return tanpa_kutip(event)></textarea></td></tr>";
                    $tab.="<tr><td colspan=3 align=center><button class=mybutton onclick=saveAjukan('".$notransaksi."','".$perKe."')>".$_SESSION['lang']['diajukan']."</button></td></tr></table>";
                    $tab.="</fieldset>";
                    echo $tab;
                 
                break;

		case'prevPdf':
                class PDF extends FPDF
{
	
	function Header()
	{
	    $path='images/logo.jpg';
	    $this->Image($path,9,2,18);	
		$this->SetFont('Arial','B',10);
		$this->SetFillColor(255,255,255);	
		$this->SetY(22);   
	    $this->Cell(60,5,$_SESSION['org']['namaorganisasi'],0,1,'C');	 
		$this->SetFont('Arial','',15);
	    $this->Cell(190,5,'',0,1,'C');
		$this->SetFont('Arial','',6); 
		$this->SetY(30);
		$this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
		$this->Line(10,32,200,32);	   
		 
	}
	
	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}

}
//data ht
$sHt="select distinct * from ".$dbname.".pta_ht where notransaksi='".$notransaksi."'";
$qHt=$owlPDO->query($sHt) or die(print " Gagal: ".PDOException::getMessage());
$qHt->setFetchMode(PDO::FETCH_ASSOC);
$rHt=$qHt->fetch();
//data hk
$sHk="select distinct kodekegiatan,noakun,sum(jumlah) as jumlah,satuanj,alokasibiaya,sum(rupiah) as rupiah   from ".$dbname.".pta_dt 
      where notransaksi='".$notransaksi."' and jenispta='HK' group by kodekegiatan order by notransaksi asc";
$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
$qHk->setFetchMode(PDO::FETCH_ASSOC);

while($rHk=$qHk->fetch())
{
    if($rHk['noakun']!='')
    {
    $hkNoakun[$rHk['noakun']]=$rHk['noakun'];
    //$hkJumlah[$notransaksi][$rHk['noakun']]=$rHk['jumlah'];
    $hkJumlah[$notransaksi][$rHk['noakun']]=$rHk['rupiah'];
    $hkKegiatan[$notransaksi][$rHk['noakun']]=$rHk['kodekegiatan'];
    $hkSatuan[$notransaksi][$rHk['noakun']]=$rHk['satuanj'];
    $hkAlokasi[$notransaksi][$rHk['noakun']]=$rHk['alokasibiaya'];
    }
}

//data material
$sHk="select distinct kodebarang,noakun,sum(jumlah) as jumlah,satuanj,alokasibiaya,sum(rupiah) as rupiah   from ".$dbname.".pta_dt 
      where notransaksi='".$notransaksi."' and jenispta='MATERIAL' group by kodekegiatan order by notransaksi asc";
$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
$qHk->setFetchMode(PDO::FETCH_ASSOC);
while($rHk=$qHk->fetch())
{
    if($rHk['noakun']!='')
    {
    $matNoakun[$rHk['noakun']]=$rHk['noakun'];
    $matJumlahBrg[$notransaksi][$rHk['noakun']]=$rHk['jumlah'];
    $matJumlah[$notransaksi][$rHk['noakun']]=$rHk['rupiah'];
    $matKegiatan[$notransaksi][$rHk['noakun']]=$rHk['kodebarang'];
    $matSatuan[$notransaksi][$rHk['noakun']]=$rHk['satuanj'];
    $matAlokasi[$notransaksi][$rHk['noakun']]=$rHk['alokasibiaya'];
    }
}
//data ,HM
$sHk="select distinct kodevhc,noakun,sum(jumlah) as jumlah,satuanj,alokasibiaya,sum(rupiah) as rupiah   from ".$dbname.".pta_dt 
      where notransaksi='".$notransaksi."' and jenispta='HM' group by kodekegiatan order by notransaksi asc";
$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
$qHk->setFetchMode(PDO::FETCH_ASSOC);
while($rHk=$qHk->fetch())
{
    if($rHk['noakun']!='')
    {
    $hmNoakun[$rHk['noakun']]=$rHk['noakun'];
    //$hmJumlah[$notransaksi][$rHk['noakun']]=$rHk['jumlah'];
    $hmJumlah[$notransaksi][$rHk['noakun']]=$rHk['rupiah'];
    $hmKegiatan[$notransaksi][$rHk['noakun']]=$rHk['kodevhc'];
    $hmSatuan[$notransaksi][$rHk['noakun']]=$rHk['satuanj'];
    $hmAlokasi[$notransaksi][$rHk['noakun']]=$rHk['alokasibiaya'];
    }
}
//data ,UMUM
$sHk="select distinct kodekegiatan,noakun,sum(jumlah) as jumlah,satuanj,alokasibiaya,sum(rupiah) as rupiah   from ".$dbname.".pta_dt 
      where notransaksi='".$notransaksi."' and jenispta='UMUM'  group by kodekegiatan order by notransaksi asc";
$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
$qHk->setFetchMode(PDO::FETCH_ASSOC);
while($rHk=$qHk->fetch())
{
    if($rHk['noakun']!='')
    {
    $genNoakun[$rHk['noakun']]=$rHk['noakun'];
    //$genJumlah[$notransaksi][$rHk['noakun']]=$rHk['jumlah'];
    $genJumlah[$notransaksi][$rHk['noakun']]=$rHk['rupiah'];
    $genKegiatan[$notransaksi][$rHk['noakun']]=$rHk['kodekegiatan'];
    $genSatuan[$notransaksi][$rHk['noakun']]=$rHk['satuanj'];
    $genAlokasi[$notransaksi][$rHk['noakun']]=$rHk['alokasibiaya'];
    }
}
$cekGen=count($genNoakun);
$cekHm=count($hmNoakun);
$cekMat=count($matNoakun);
$cekHK=count($hkNoakun);

	$pdf=new PDF('P','mm','A4');
	$pdf->SetFont('Arial','B',10);
	$pdf->AddPage();
	$pdf->SetY(40);
	$pdf->SetX(20);
	$pdf->SetFillColor(255,255,255); 
        $pdf->Cell(175,5,strtoupper("PERMINTAAN TAMBAHAN ANGGARAN"),0,1,'C');
        $pdf->Cell(170,5,"No.: ".$notransaksi,0,1,'C');
	$pdf->SetFont('Arial','',7);
		
        $kosong=0;
    	$pdf->Cell(170,5,$_SESSION['lang']['tanggal'],0,0,'R');	
        $pdf->Cell(15,5," : ".tanggalnormal($rHt['tanggal']),0,1,'R');	
        		
    	$pdf->Cell(30,5,$_SESSION['lang']['penjelasan']." :",0,1,'L');	
	$pdf->MultiCell(190, 5, $rHt['penjelasan'], TB, 'J', false);
        $pdf->ln(5);
        
        
        
	$pdf->ln(5);
        $pdf->Cell(20,5,"MATERIAL",0,1,'L');
        $pdf->Cell(20,5,$_SESSION['lang']['noakun'],1,0,'C');	
        $pdf->Cell(80,5,$_SESSION['lang']['kodevhc'],1,0,'C');
        $pdf->Cell(18,5,$_SESSION['lang']['jumlah'],1,0,'C');
        $pdf->Cell(15,5,$_SESSION['lang']['satuan'],1,0,'C');
        $pdf->Cell(25,5,$_SESSION['lang']['alokasi'],1,0,'C');
        $pdf->Cell(30,5,$_SESSION['lang']['jumlah']." (Rp.)",1,1,'C');
        if($cekMat!=0)
        {
            foreach($matNoakun as $lstNoakun)
            {
                $pdf->Cell(20,5,$lstNoakun,1,0,'C');	
                $pdf->Cell(80,5,$optBarang[$matKegiatan[$notransaksi][$lstNoakun]],1,0,'L');	
                $pdf->Cell(18,5,$matJumlahBrg[$notransaksi][$lstNoakun],1,0,'C');
                $pdf->Cell(15,5,$matSatuan[$notransaksi][$lstNoakun],1,0,'C');
                $pdf->Cell(25,5,$matAlokasi[$notransaksi][$lstNoakun],1,0,'C');
                $pdf->Cell(30,5,number_format($matJumlah[$notransaksi][$lstNoakun],2),1,1,'R');
                $matJmlh+=$matJumlah[$notransaksi][$lstNoakun];

            }
            $pdf->Cell(158,5,$_SESSION['lang']['jumlah'],1,0,'C');
            $pdf->Cell(30,5,number_format($matJmlh,2),1,1,'R');
        }
        else
        {
             $pdf->Cell(170,5,'',1,1,'C');
        }
       
    
	$pdf->ln(5);
    
	$grandTotal=$genJmlh+$hmJmlh+$matJmlh+$hkJmlh;
        $pdf->Ln();
        $pdf->Cell(140,5,$_SESSION['lang']['grnd_total'],0,0,'R');
        $pdf->Cell(30,5,number_format($grandTotal,2),1,1,'R');	
	
	
	$pdf->Ln(5);	
	
        
	$pdf->SetFont('Arial','B',8);		
        //$pdf->Cell(172,5,strtoupper($_SESSION['lang']['approval_status']),0,1,'L');
	$pdf->Cell(172,5,strtoupper($_SESSION['lang']['approval_status']),0,1,'L');	

		$pdf->Cell(25,5,strtoupper("Tahapan"),1,0,'C');
		$pdf->Cell(39,5,strtoupper($_SESSION['lang']['namakaryawan']),1,0,'C');			
		$pdf->Cell(42,5,strtoupper($_SESSION['lang']['status']),1,0,'C');
		$pdf->Cell(65,5,strtoupper($_SESSION['lang']['catatan']),1,1,'C');	
//pembuat                
        $pdf->Cell(25,5,'Dibuat Oleh',1,0,'L');
        $pdf->Cell(39,5,$arrNmkary[$rHt['dibuat']],1,0,'L');			
        $pdf->Cell(42,5,'',1,0,'L');
        $pdf->Cell(65,5,'',1,1,'L');
     
                
      $X[1]='Diketahui';
      $X[2]='Verifikasi';
      $X[3]='Disetujui';
      $X[4]='Disetujui';      
                $pdf->SetFont('Arial','',8);
	for($ard=1;$ard<5;$ard++)
        {
		$pdf->Cell(25,5,$X[$ard],1,0,'L');
		$pdf->Cell(39,5,$arrNmkary[$rHt['persetujuan'.$ard]],1,0,'L');			
		$pdf->Cell(42,5,$arrKeputusan[$rHt['status'.$ard]],1,0,'L');
		$pdf->Cell(65,5,$rHt['catatan'.$ard],1,1,'L');
        }
	


   $pdf->Ln();	
   $pdf->Ln();	
   $pdf->Ln();	
 	
					
//footer================================
    $pdf->Ln();		
	$pdf->Output();
            break;
            case'getExcel':
               			
                break;
		
		default:
		break;
	}


?>