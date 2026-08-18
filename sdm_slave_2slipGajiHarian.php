<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses = checkPostGet('proses','');
$periode = checkPostGet('periode','');
$period = checkPostGet('period','');
$idKry = checkPostGet('idKry','');
$kdBag = checkPostGet('kdBag','');
$tPkary = checkPostGet('tPkary','');
$arrBln=array(1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"Mei",6=>"Jun",7=>"Jul",8=>"Agu",9=>"Sep",10=>"Okt",11=>"Nov",12=>"Des");
$rNmTipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

#= komponen yang tidak termasuk di slip gaji
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='KOMGJEXSLP'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
	$exslip=$bar['nilai'];

$whcatukdbag=$dtTipecatu ='';
if($kdBag!=''){
	$whcatukdbag=" and bagian='".$kdBag."'";
}

if($periode!=''&&$kdBag!='')
{
    $where="a.sistemgaji='Harian' and a.periodegaji='".$periode."' and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'
            and b.bagian='".$kdBag."'";
    $wherelalu="a.sistemgaji='Harian' and a.periodegaji='".periodelalu($periode)."' and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'
            and b.bagian='".$kdBag."'";
}
elseif($periode!='')
{
    $where="a.sistemgaji='Harian' and a.periodegaji='".$periode."'  
            and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
    $wherelalu="a.sistemgaji='Harian' and a.periodegaji='".periodelalu($periode)."'  
            and a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";

}
else
{
    $periode=$period;
    $where="a.sistemgaji='Harian' and a.periodegaji='".$periode."' and a.karyawanid='".$idKry."'";
    $wherelalu="a.sistemgaji='Harian' and a.periodegaji='".periodelalu($periode)."' and a.karyawanid='".$idKry."'";
}
$dtTipe=$dtTupecatu="";
if($tPkary!='')
{
    $dtTipe=" and b.tipekaryawan='".$tPkary."'";
	$dtTipecatu=" and tipekaryawan='".$tPkary."'";
}

$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji 
		where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$periode."' and jenisgaji='H'";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
$rTgl=$qTgl->fetch();
//$test = dates_inbetween($rTgl['tanggalmulai'], $rTgl['tanggalsampai']);

$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$gjthnlu=$bar['nilai'];

$nmbank= makeOption($dbname, 'datakaryawan', 'karyawanid,namabank');
$norek= makeOption($dbname, 'datakaryawan', 'karyawanid,norekeningbank');


$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
        $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
    $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        $rOrg=$qOrg->fetch();

        //periode gaji
        $bln=explode('-',$periode);
        $idBln=intval(@$bln[1]);  
          //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,e.plus from 
               ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_ho_component e on a.idkomponen=e.id 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode where ".$where." ".$dtTipe." ";
        $qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
    $qSlip->setFetchMode(PDO::FETCH_ASSOC);
    $rCek=owlBaris($qSlip);
    if($rCek>0)
        {
                while($rSlip=$qSlip->fetch())
                {
                    if($rSlip['karyawanid']!='')
                    {
                      $arrKary[$rSlip['karyawanid']]=$rSlip['karyawanid'];
                      $arrTipekary[$rSlip['karyawanid']]=$rSlip['tipekaryawan'];
                      $arrStatPjk[$rSlip['karyawanid']]=$rSlip['statuspajak'];
                      $arrRek[$rSlip['karyawanid']]=$rSlip['norekeningbank'];
                      $arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
                      $arrTglMsk[$rSlip['karyawanid']]=$rSlip['tanggalmasuk'];
                      $arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
                      $arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
                      $arrBag[$rSlip['karyawanid']]=$rSlip['bagian'];
                      $arrJbtn[$rSlip['karyawanid']]=$rSlip['namajabatan'];
                      $arrDept[$rSlip['karyawanid']]=$rSlip['nama'];
                      $arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];

                      if($rSlip['plus']=='1')
                      {
                      $arrValPlus[$rSlip['karyawanid']][$rSlip['idkomponen']]=$rSlip['jumlah'];
                      }
                      else
                      {
                      $arrValMinus[$rSlip['karyawanid']][$rSlip['idkomponen']]=$rSlip['jumlah'];
                      }

                      $arrafd[$rSlip['karyawanid']]=$rSlip['subbagian'];
                      $arrtipekar[$rSlip['karyawanid']]=$rSlip['tipekaryawan'];
                    }
                }

               //  $sSliplalu="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,e.plus from 
               //   ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               // left join ".$dbname.".sdm_ho_component e on a.idkomponen=e.id 
               //   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               //   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode where ".$wherelalu." ".$dtTipe."  and a.idkomponen  in (".$gjthnlu.")";
               //  $qSliplalu=$owlPDO->query($sSliplalu) or die(print " Gagal: ".PDOException::getMessage());
               //  $qSliplalu->setFetchMode(PDO::FETCH_ASSOC);
               //  $rCeklalu=owlBaris($qSliplalu);
               //  //echo $sSliplalu;
               //  if($rCeklalu>0)
               //  {
               //    while($rSliplalu=$qSliplalu->fetch())
               //    {
               //        if($rSliplalu['karyawanid']!='')
               //        {
               //          $arrKary[$rSliplalu['karyawanid']]=$rSliplalu['karyawanid'];
               //          $arrTipekary[$rSliplalu['karyawanid']]=$rSliplalu['tipekaryawan'];
               //          $arrStatPjk[$rSliplalu['karyawanid']]=$rSliplalu['statuspajak'];
               //          $arrRek[$rSliplalu['karyawanid']]=$rSliplalu['norekeningbank'];
               //          $arrKomp[$rSliplalu['karyawanid']]=$rSliplalu['idkomponen'];
               //          $arrTglMsk[$rSliplalu['karyawanid']]=$rSliplalu['tanggalmasuk'];
               //          $arrNik[$rSliplalu['karyawanid']]=$rSliplalu['nik'];
               //          $arrNmKary[$rSliplalu['karyawanid']]=$rSliplalu['namakaryawan'];
               //          $arrBag[$rSliplalu['karyawanid']]=$rSliplalu['bagian'];
               //          $arrJbtn[$rSliplalu['karyawanid']]=$rSliplalu['namajabatan'];
               //          $arrDept[$rSliplalu['karyawanid']]=$rSliplalu['nama'];
               //          $arrJmlh[$rSliplalu['karyawanid'].$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];

               //          if($rSliplalu['plus']=='1')
               //          {
               //          $arrValPlus[$rSliplalu['karyawanid']][$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];
               //          }
               //          else
               //          {
               //          $arrValMinus[$rSliplalu['karyawanid']][$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];
               //          }

               //          $arrafd[$rSliplalu['karyawanid']]=$rSliplalu['subbagian'];
               //          $arrtipekar[$rSliplalu['karyawanid']]=$rSliplalu['tipekaryawan'];
               //        }
               //    }
               //  }


                //array data komponen penambah dan pengurang

      $arrIdKompPls = array();
      $arrIdKompMin = array();
      $arrPlusId = array();
      $arrMinusId = array();


                $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=0 and b.jumlah!=0 and b.periodegaji='".$periode."' 
                   and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
                   and b.karyawanid like '%".$idKry."%' and a.id not in (".$exslip.",".$gjthnlu.")  order by a.id";
                $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
                $qKomp->setFetchMode(PDO::FETCH_ASSOC);
                while($rKomp=$qKomp->fetch())
                {
                      $arrIdKompMin[]=$rKomp['id'];
                      $arrNmKomMin[$rKomp['id']]=$rKomp['name'];

                      $arrMinusId[]=$rKomp['id'];
                      $arrMinusName[]=$rKomp['name'];
                }


                $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=0 and b.jumlah!=0 and b.periodegaji='".periodelalu($periode)."' 
                   and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
                   and b.karyawanid like '%".$idKry."%' and a.id in (".$gjthnlu.")  order by a.id";
                $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
                $qKomp->setFetchMode(PDO::FETCH_ASSOC);
                while($rKomp=$qKomp->fetch())
                {
                      $arrIdKompMin[]=$rKomp['id'];
                      $arrNmKomMin[$rKomp['id']]=$rKomp['name'];


                      $arrMinusId[]=$rKomp['id'];
                      $arrMinusName[]=$rKomp['name'];
                }


                 $arrPlusId=$arrMinusId;
                $arrPlusName=$arrMinusName;
                for($r=0;$r<count($arrMinusId);$r++)
                {
                     $arrPlusId[$r]='';
                     $arrPlusName[$r]='';
                }
                $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=1 and b.jumlah!=0 and b.periodegaji='".$periode."' 
                   and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.id not in ('26','28') 
                   and b.karyawanid like '%".$idKry."%' and a.id not in (".$exslip.",".$gjthnlu.") order by a.id";
                $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
                $qKomp->setFetchMode(PDO::FETCH_ASSOC);
                while($rKomp=$qKomp->fetch())
                {
                      $arrIdKompPls[]=$rKomp['id'];
                      $arrNmKomPls[$rKomp['id']]=$rKomp['name'];

                      $arrPlusId[]=$rKomp['id'];
                      $arrPlusName[]=$rKomp['name'];
                }
                //array data komponen penambah dan pengurang periode lalu
                $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=1 and b.jumlah!=0 and b.periodegaji='".periodelalu($periode)."' 
                   and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.id not in ('26','28') 
                   and b.karyawanid like '%".$idKry."%' and a.id in (".$gjthnlu.") order by a.id";
                $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
                $qKomp->setFetchMode(PDO::FETCH_ASSOC);
                while($rKomp=$qKomp->fetch())
                {
                      $arrIdKompPls[]=$rKomp['id'];
                      $arrNmKomPls[$rKomp['id']]=$rKomp['name'];

                      $arrPlusId[]=$rKomp['id'];
                      $arrPlusName[]=$rKomp['name'];
                }

               


                
  }
switch($proses)
{
        case'preview':

        $path='images/logo.jpg';

        
                if($rCek>0)
        {

                foreach($arrKary as $dtKary)
                {/*
				<img src=".$path." width=60 height=35>&nbsp;*/

                    echo"<table cellspacing=1 border=0 width=500>
                    <tr><td> <h2>".$_SESSION['org']['namaorganisasi']."</h2></td></tr>
                    <tr style='border-bottom:#000 solid 2px; border-top:#000 solid 2px;'><td valign=top>
                    <table border=0 width=110%>
                    <tr><td width=49% valign=top><table border=0>
                    <tr><td colspan=3>PAY SLYP/SLIP GAJI: ".$arrBln[$idBln]."-".$bln[0]."</td></tr>
                    <tr><td>NIP/TMK</td><td>:</td><td>".$arrNik[$dtKary]."/".tanggalnormal($arrTglMsk[$dtKary])."</td></tr>
                    <tr><td>NAMA</td><td>:</td><td>".$arrNmKary[$dtKary]."</td></tr>
                    </table></td><td width=51% valign=top>
                    <table border=0>
                    <tr><td colspan=3>&nbsp;</td></tr>
                    <tr><td>UNIT/BAGIAN</td><td>:</td><td>".$rOrg['namaorganisasi']."/".$arrBag[$dtKary]."</td></tr>
                    <tr><td>JABATAN</td><td>:</td><td>".$arrJbtn[$dtKary]."</td></tr>
                    </table></td></tr>
                    </table>
                    </td></tr>
                    <tr>
                    <td>
                    <table width=100%>
                    <thead>
                    <tr><td align=center>PENAMBAH</td><td align=center>PENGURANG</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td valign=top>
                    <table width=100%>";
                     $arrPlus=Array();
                      $s=0;
                      foreach($arrIdKompPls as $idKompPls)
                      {
                        if(@$arrJmlh[$dtKary.$idKompPls]==''){
                                    setIt($arrJmlh[$dtKary.$idKompPls],0);
                                  }
                        if(intval($arrJmlh[$dtKary.$idKompPls])!=0){
                          echo"<tr><td>".$arrNmKomPls[$idKompPls]."</td><td>:Rp.</td><td align=right> ".@number_format($arrJmlh[$dtKary.$idKompPls],2)."</td></tr>";
                        }
                            $arrPlus[$s]=$arrJmlh[$dtKary.$idKompPls];
                            $s++;
                      }

                    echo"</table>
                    </td>
                    <td valign=top>
                    <table width=100%>";
                    $arrMin=Array();
                        $q=0;
					if(is_array($arrIdKompMin)){	
                        foreach($arrIdKompMin as $idKompMin)
                          {
                            if(@$arrJmlh[$dtKary.$idKompMin]==''){
                                    setIt($arrJmlh[$dtKary.$idKompMin],0);
                                  }
                            if(intval($arrJmlh[$dtKary.$idKompMin])!=0){
                              echo"<tr><td>".$arrNmKomMin[$idKompMin]."</td><td>:Rp.</td><td align=right> ".@number_format($arrJmlh[$dtKary.$idKompMin],2)."</td></tr>";
                            }
                                $arrMin[$q]=$arrJmlh[$dtKary.$idKompMin];
                                $q++;
                          }
					}	  
                    $gajiBersih=array_sum($arrPlus)-array_sum($arrMin);
                    echo"</table>
                    </td></tr>
                    <tr><td colspan=2><table width=100%>
                    <tr><td>Total Pendapatan</td><td>:Rp.</td><td align=right> ".@number_format(array_sum($arrPlus),2)."</td><td>Total Pengurangan</td><td>:Rp.</td><td align=right> ".@number_format(array_sum($arrMin),2)."</td></tr>
                    <tr><td>Gaji Bersih</td><td>:Rp.</td><td align=right> ".@number_format((array_sum($arrPlus)-array_sum($arrMin)),2)."</td><td>&nbsp;</td><td>&nbsp;</td><td align=right> &nbsp;</td></tr>
                    <tr><td>Terbilang</td><td>:</td><td colspan=4> ".terbilang($gajiBersih,2)." rupiah</td></tr></table></td></tr></tbody>
                    </table></td>
                    </tr>

                    <tr>
                    <td>
                    <table cellspacing=0>
                    <tr>
                    <hr>
                    <td>
                    Transfer Melalui :<br>".$nmbank[$dtKary]."<br>".$norek[$dtKary]."<br>".$arrNmKary[$dtKary]."</td>
                    </td>
                    </tr>
                    </table>
                  </tr>

                    <tr>
                    <td>&nbsp;</td>
                    </tr>
                    </table>
                    ";
        }
        }
        else
        {
                echo"Not Found";
        }
        break;
        case'pdf':


        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
//create Header

class PDF extends FPDF
{
var $col=0;
var $dbname;

function SetCol($col)
        {
            //Move position to a column
            $this->col=$col;
            $x=10+$col*100;
            $this->SetLeftMargin($x);
            $this->SetX($x);
        }

function AcceptPageBreak()
        { 
                        if($this->col<1)
                    {
                        //Go to next column
                        $this->SetCol($this->col+1);
                        $this->SetY(10);
                        return false;
                    }
                    else
                    {
                        //Go back to first column and issue page break
                                $this->SetCol(0);
                        return true;
                    }
        }

        function Header()
        {    
                $this->lMargin=10;  
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',5);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }
}
        $pdf=new PDF('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','',5);
//	$pdf->SetY(5);
//	$pdf->SetX(5);
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
        //periode gaji
        $bln=explode('-',$periode);
        $idBln=intval($bln[1]);	
         //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
       
		if($rCek>0)
        {
            
        foreach($arrKary as $dtKary)
        {
			@$st++;
                        //$pdf->Image('images/logo.jpg',$pdf->GetX(),$pdf->GetY(),10);
                        //$pdf->SetX($pdf->getX()+10);
						
						$yatas=$pdf->getY();
						if($st==1){
							$xkiriatas=$pdf->getX();
							$spasi='';
						}else{
							$xkiriatas=$pdf->getX()+1;
							$spasi=' ';
						}
						//$xkiriatas=$pdf->getX();
						$xkananatas=$xkiriatas+96;
						$pdf->Line($xkiriatas,$yatas,$xkananatas,$yatas);
						
						
						
						/*
                        $pdf->SetFont('Arial','B',8);	
                        $pdf->Cell(75,12,$_SESSION['org']['namaorganisasi'],0,1,'L');
                        $pdf->SetFont('Arial','',6);
                        $pdf->Cell(71,4,'PAY SLYP/SLIP GAJI : '.$arrBln[$idBln]."-".$bln[0]." ( ".$_SESSION['lang']['tanggal']." : ".substr(tanggalnormal($rTgl['tanggalmulai']),0,2)." s/d ".tanggalnormal($rTgl['tanggalsampai'])." )",'T',0,'L');
                        $pdf->SetFont('Arial','',6);
                                $pdf->Cell(25,4,'Printed on: '.date('d-m-Y: H:i:s'),"T",1,'R');
                        $pdf->SetFont('Arial','',6);		
						*/
						
						$pdf->SetFont('Arial','',8);	
                        $pdf->Cell(75,10,$spasi.''.$_SESSION['org']['namaorganisasi'],0,0,'L');
						$pdf->Ln(9);
						 $pdf->Cell(75,1,$_SESSION['empl']['kodeorganisasi'].' - '.$_SESSION['empl']['lokasitugas'],0,1,'L');
						//$pdf->Ln(10);
						$pdf->SetFont('Arial','B',8);	
						 $pdf->Cell(100,10,'Slip Gaji',0,1,'C');
                        $pdf->SetFont('Arial','',6);
						//$pdf->Ln();
                        $pdf->Cell(71,4,$_SESSION['lang']['slipGaji'].' : '.$arrBln[$idBln]."-".$bln[0]." ( ".$_SESSION['lang']['tanggal']." : ".substr(tanggalnormal($rTgl['tanggalmulai']),0,2)." s/d ".tanggalnormal($rTgl['tanggalsampai'])." )",'T',0,'L');
                        $pdf->SetFont('Arial','',6);
                        $pdf->Cell(25,4,'Printed on: '.date('d-m-Y: H:i:s'),"T",1,'R');
						
						
                        $pdf->SetFont('Arial','',6);		
                        $pdf->Cell(17,4,"NIK",0,0,'L');
                        $pdf->Cell(30,4,": ".$arrNik[$dtKary],0,0,'L');
                        
						if(@$nmorg[$arrafd[$dtKary]]==''){
							@$nmorg[$arrafd[$dtKary]]='Kantor';
						}
						$pdf->Cell(10,4,$_SESSION['lang']['divisi'],0,0,'L');	
                        $pdf->Cell(28,4,': '.$nmorg[$arrafd[$dtKary]],0,1,'L');		
                        
						$pdf->Cell(17,4,$_SESSION['lang']['namakaryawan'],0,0,'L');
                        $pdf->Cell(30,4,': '.$arrNmKary[$dtKary],0,0,'L');	
                        
						$pdf->Cell(10,4,$_SESSION['lang']['jabatan'],0,0,'L');
                        $pdf->Cell(28,4,': '.$arrJbtn[$dtKary],0,1,'L');	
						
						
						$pdf->Cell(17,4,$_SESSION['lang']['tipekaryawan'],0,0,'L');
                        $pdf->Cell(30,4,': '.$rNmTipe[$arrtipekar[$dtKary]],0,0,'L');	
						
							$pdf->Cell(10,4,$_SESSION['lang']['tmk'],0,0,'L');
                        $pdf->Cell(28,4,': '.tanggalnormal($arrTglMsk[$dtKary]),0,1,'L');	
								
								
								
								
                        $pdf->Cell(48,4,$_SESSION['lang']['penambah'],'TB',0,'C');
                        $pdf->Cell(48,4,$_SESSION['lang']['pengurang'],'TB',1,'C');
						
						
						
						
						
                        for($mn=0;$mn<count($arrPlusId);$mn++)
                        {
                                //if(intval(@$arrValPlus[$dtKary][$arrPlusId[$mn]])!=0){
                                 $pdf->Cell(25,4,$arrPlusName[$mn],0,0,'L');
                               // }else{
                                //     $pdf->Cell(25,4,"",0,0,'L');
                               // }
                                if($arrPlusName[$mn]=='')
                                {
                                  $pdf->Cell(5,4,"",0,0,'L');
                                  $pdf->Cell(18,4,'','R',0,'R');
                                }
                                else
                                {
                                    if($arrPlusId[$mn]=='')
                                    {
                                        $pdf->Cell(5,4,"",0,0,'L');
                                        $pdf->Cell(18,4,'','R',0,'R');
                                    }
                                    else
                                    {
                                        setIt($arrValPlus[$dtKary][$arrPlusId[$mn]],0);
                                        setIt($arrPlus[$dtKary],0);                                        
                                        //if(intval($arrValPlus[$dtKary][$arrPlusId[$mn]])!=0){
                                            $pdf->Cell(5,4,":Rp.",0,0,'L');
                                            $pdf->Cell(18,4,@number_format($arrValPlus[$dtKary][$arrPlusId[$mn]],2,'.',','),'R',0,'R');
                                            // }
												// else{
                                            // $pdf->Cell(5,4,"",0,0,'L');
                                            // $pdf->Cell(18,4,'','R',0,'R');
                                            // }
                                        $arrPlus[$dtKary]+=$arrValPlus[$dtKary][$arrPlusId[$mn]];
                                    }
                                }
                               // if(intval(@$arrValMinus[$dtKary][$arrMinusId[$mn]])!=0){
                                $pdf->Cell(25,4,@$arrMinusName[$mn],0,0,'L');
                               // }else{
                               //     $pdf->Cell(25,4,'',0,0,'L');
                               // }
                                if(@$arrMinusName[$mn]=='')
                                {
                                  $pdf->Cell(5,4,"",0,0,'L');
                                  $pdf->Cell(18,4,'',0,1,'R');
                                }
                                else
                                {
                                    if($arrMinusId[$mn]=='')
                                    {
                                      $pdf->Cell(5,4,"",0,0,'L');
                                       $pdf->Cell(18,4,'',0,1,'R');
                                    }
                                    else
                                    {
                                        setIt($arrValMinus[$dtKary][$arrMinusId[$mn]],0);
                                        setIt($arrMin[$dtKary],0);
                                        //if(intval($arrValMinus[$dtKary][$arrMinusId[$mn]])!=0){
                                            $pdf->Cell(5,4,":Rp.",0,0,'L');
                                            $pdf->Cell(18,4,@number_format(($arrValMinus[$dtKary][$arrMinusId[$mn]]),2,'.',','),0,1,'R');
                                        //}else{
                                        //    $pdf->Cell(5,4,"",0,0,'L');
                                         //   $pdf->Cell(18,4,'',0,1,'R');                                            
                                       // }
                                        $arrMin[$dtKary]+=$arrValMinus[$dtKary][$arrMinusId[$mn]];
                                    }
                                }
                        }
                                $pdf->Cell(25,4,'Total.Pendapatan','TB',0,'L');
                                $pdf->Cell(5,4,":Rp.",'TB',0,'L');
                                        $pdf->Cell(18,4,@number_format($arrPlus[$dtKary],2,'.',','),'TB',0,'R');
                                $pdf->Cell(25,4,'Total.Pengurangan','TB',0,'L');
                                $pdf->Cell(5,4,":Rp.",'TB',0,'L');
                                        $pdf->Cell(18,4,@number_format(($arrMin[$dtKary]),2,'.',','),'TB',1,'R');

                        $pdf->SetFont('Arial','B',6);
                        $pdf->Cell(25,4,'Gaji.Bersih',0,0,'L');
                        $pdf->Cell(5,4,":Rp.",0,0,'L');
                                $pdf->Cell(18,4,@number_format(($arrPlus[$dtKary]-($arrMin[$dtKary])),2,'.',','),0,0,'R');
                                $pdf->Cell(47,4,"",0,1,'L');
                                $terbilang=($arrPlus[$dtKary]-($arrMin[$dtKary]));
                                $blng=terbilang($terbilang,2)." rupiah";
                        $pdf->SetFont('Arial','',7);	
                        $pdf->Cell(25,4,'Terbilang',0,0,'L');
                        $pdf->Cell(5,4,":",0,0,'L');
                        
                        
                                //$pdf->MultiCell(58,4,$blng,0,'L');
                                $awalY=$pdf->GetY();
                                $pdf->MultiCell(58,4,$blng,0,'L');




                                $akhirY=$pdf->GetY();
                                $tinggiY=$akhirY-$awalY;
                                
                                if($tinggiY<=5)
                                {
                                    $pdf->Ln();
                                }
                                
                                
                       $pdf->Ln(3);	
						$pdf->Cell(25,2.5,'Natura',0,0,'L');
						$pdf->Cell(10,2.5,'Jumlah',0,0,'R');
						$pdf->Cell(10,2,'Satuan',0,1,'L');
						$pdf->Cell(50,2,'________________________________',0,1,'L');
						$pdf->Cell(25,5,'Natura - '.$_SESSION['empl']['kodeorganisasi'],0,0,'L');
						$pdf->Cell(10,4,@number_format($totalcatu[$dtKary],2),0,0,'R');
						$pdf->Cell(10,4,'Kg',0,1,'L');	

            $pdf->Cell(15,4,'',0,1,'L');
            $pdf->Cell(15,4,'Transfer Melalui :',0,1,'L');
            $pdf->Cell(15,4,$nmbank[$dtKary],0,1,'L');
            $pdf->Cell(15,4,$norek[$dtKary],0,1,'L');
            $pdf->Cell(15,4,$arrNmKary[$dtKary],0,1,'L');	
						
						$pdf->Ln(10);		 
                        $pdf->Cell(50,5,'(....................................)',0,0,'C');
						$pdf->Cell(50,5,'( '.$arrNmKary[$dtKary].' )',0,1,'C');
						$xkiribawah=$pdf->getX();
						$pdf->Cell(50,5,'KASIE',0,0,'C');
						$xkananbawah=$pdf->getX()+46;		
						$pdf->Cell(50,5,'Nama Karyawan',0,1,'C');	
						$pdf->Ln();
						$ybawaah=$pdf->GetY();
						
						//garis bawah
						$pdf->Line($xkiribawah,$ybawaah,$xkananbawah,$ybawaah);
						
						//garis kiri
						$pdf->Line($xkiriatas,$yatas,$xkiribawah,$ybawaah);
						
						//garis kanan
						$pdf->Line($xkananatas,$yatas,$xkananbawah,$ybawaah);
						
						
							  
                        // $pdf->SetFont('Arial','I',5);
                        // $pdf->Cell(96,4,'Note: This is computer generated system, signature is not required','T',1,'L');	
                        $pdf->SetFont('Arial','',6);	
                        $pdf->Ln();	
						//$pdf->Ln();	
                      //  $pdf->Ln();	
//                        if(($st%2)==0){
//                            $pdf->AcceptPageBreak();
//                        }
                        
						
						
                        if($pdf->GetY()>160 and $pdf->col<1)
                                $pdf->AcceptPageBreak();
                        if ($pdf->GetY()>160 and $pdf->col>0)
                           {
                                
                                //$pdf->lewat=true;
                                // $pdf->AcceptPageBreak();
                                //$pdf->SetY(277-$pdf->GetY());
                                $r=275-$pdf->GetY();
                                $pdf->Cell(80,$r,"",0,1,'L');
                                
                                //$pdf->ln();
                           }
                        //else   
                        //$pdf->lewat=false; 	

                        $pdf->cell(-1,3,'',0,0,'L');
                }
}
else
{
        //$pdf->Image('images/logo.jpg',$pdf->GetX(),$pdf->GetY(),10);
        //$pdf->SetX($pdf->getX()+8);
		$pdf->Ln();
        $pdf->SetFont('Arial','B',8);	
        $pdf->Cell(70,5,$_SESSION['org']['namaorganisasi'],0,1,'L');
        $pdf->SetFont('Arial','',5);	
        $pdf->Cell(60,3,'NOT FOUND','T',0,'L');
}
        $pdf->Output();

        break;
        
        case'excel':
        $bln=explode('-',$period);
        @$idBln=intval($bln[1]);	
		

 
		  
	
		
		
		  

                        $sPeriod="select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where jenisgaji='B' and periode='".$periode."' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
						$qPeriod=$owlPDO->query($sPeriod) or die(print " Gagal: ".PDOException::getMessage());
						$qPeriod->setFetchMode(PDO::FETCH_ASSOC);
                        $rPeriod=$qPeriod->fetch();
                        $mulai=tanggalnormal($rPeriod['tanggalmulai']);
                        $selesi=tanggalnormal($rPeriod['tanggalsampai']);

                        $stream.="
                        <table>
                        <tr><td colspan=9 align=center>List Data Gaji Harian, Unit : ".$_SESSION['empl']['lokasitugas']."</td></tr>
                        <tr><td colspan=9 align=center>Periode : ".$mulai." s.d ".$selesi."</td></tr>
                        </table>
                        <table border=1>
                        <tr>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>No.</td>
								<td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['divisi']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['nik2']."</td>
								<td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['namakaryawan']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['jabatan']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['tipekaryawan']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['statuspajak']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>No. Rekening</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2' width=50px>".$_SESSION['lang']['totLembur']."</td>
                                
                                ";
                        //absen di bayar
                        $shkdbyr="select distinct kodeabsen from ".$dbname.".sdm_5absensi where kelompok=1 order by kodeabsen";
                        $qhkdbyr=$owlPDO->query($shkdbyr) or die(print " Gagal: ".PDOException::getMessage());
						$qhkdbyr->setFetchMode(PDO::FETCH_ASSOC);
						$rowabs=owlBaris($qhkdbyr);
						//absen tidak di bayar
                        $shkdbyr2="select distinct kodeabsen from ".$dbname.".sdm_5absensi where kelompok=0 order by kodeabsen";
                        $qhkdbyr2=$owlPDO->query($shkdbyr2) or die(print " Gagal: ".PDOException::getMessage());
						$qhkdbyr2->setFetchMode(PDO::FETCH_ASSOC);
						$rowabs2=owlBaris($qhkdbyr2);
						
                        // $stream.="<td bgcolor=#DEDEDE align=center  colspan='".($rowabs+1)."'>".$_SESSION['lang']['hkdibayar']."</td>";
                        // $stream.="<td bgcolor=#DEDEDE align=center colspan='".($rowabs2+1)."'>".$_SESSION['lang']['hktdkdibayar']."</td>";
                        $plsCol=count($arrIdKompPls);
                        $minCol=count($arrIdKompMin);
                        
                        
                        $stream.="<td bgcolor=#DEDEDE align=center colspan='".($plsCol+1)."'>".$_SESSION['lang']['penambah']."</td>";
                        $stream.="<td bgcolor=#DEDEDE align=center colspan='".($minCol+1)."'>".$_SESSION['lang']['pengurang']."</td>";
                        $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>GAJI BERSIH</td></tr><tr>";
                        // while($rdbyr=$qhkdbyr->fetch()){
                           // $stream.="<td bgcolor=#DEDEDE align=center>".$rdbyr['kodeabsen']."</td>";
                            // $dtAbsByr[]=$rdbyr['kodeabsen'];
                        // }
                        // $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."</td>";
                        // while($rdbyr=$qhkdbyr2->fetch()){
                            // $stream.="<td bgcolor=#DEDEDE align=center>".$rdbyr['kodeabsen']."</td>";
                            // $dtAbsTdkByr[]=$rdbyr['kodeabsen'];
                        // }
                        //   $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."</td>";
                        foreach($arrIdKompPls as $lstKompPls)
                                {
                                    //$brsPlus++;
                                    $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomPls[$lstKompPls]."</td>";
                                    /*if($brsPlus==1)
                                    {
                                        $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[37]."</td>";
                                        $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[36]."</td>";
                                    }*/

                                }
                        $stream.="<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['totalPendapatan']."</td>";
                                
                                //indra
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
                                         $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[$lstKompMin]."</td>";
                                    //}
                                }			

                      $stream.="<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['totalPotongan']."</td></tr>";
					  
					  
		
			
				

                         //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        
        if($rCek>0)
        {
               
                $sTot="select tipelembur,jamaktual,karyawanid from ".$dbname.".sdm_lemburdt where substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' and tanggal like '".periodelalu($periode)."%'";
				$qTot=$owlPDO->query($sTot) or die(print " Gagal: ".PDOException::getMessage());
				$qTot->setFetchMode(PDO::FETCH_ASSOC);
                while($rTot=$qTot->fetch())
                {
                        $sJum="select jamlembur as totalLembur from ".$dbname.".sdm_5lembur where tipelembur='".$rTot['tipelembur']."'
                        and jamaktual='".$rTot['jamaktual']."' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
                        $qJum=$owlPDO->query($sJum) or die(print " Gagal: ".PDOException::getMessage());
						$qJum->setFetchMode(PDO::FETCH_ASSOC);
                        $rJum=$qJum->fetch();
                        @$jumTot[$rTot['karyawanid']]+=$rJum['totalLembur'];
                }
				

				
                //$peng1=37;
                //$peng2=36;
                    foreach($arrKary as $dtKary)
                    {			
                                $no+=1;
                                setIt($jumTot[$dtKary],0);
                                $stream.="<tr class=rowcontent>
                                <td>".$no."</td>
                                <td style='mso-number-format:\@;'>".$arrDept[$dtKary]."</td>
								<td style='mso-number-format:\@;'>".$arrNik[$dtKary]."</td>
								<td>".$arrNmKary[$dtKary]."</td>
                                <td>".$arrJbtn[$dtKary]."</td>
                                <td>".$rNmTipe[$arrTipekary[$dtKary]]."</td> 
                                <td>".$arrStatPjk[$dtKary]."</td>
								<td style='mso-number-format:\@;'>".$arrRek[$dtKary]."</td>
                                <td align=right>".@number_format($jumTot[$dtKary],2)."</td>
                                
                                ";
								@$ttlembur+=$jumTot[$dtKary];
								
								
                                // foreach($dtAbsByr as $dtJmlhAbsDbyr){
                                    // setIt($absen[$dtKary][$dtJmlhAbsDbyr],0);
                                    // setIt($totAbsen[$dtKary],0);
                                    // setIt($grTotDbyr[$dtJmlhAbsDbyr],0);
                                    // $stream.="<td align=right>".@number_format($absen[$dtKary][$dtJmlhAbsDbyr],2)."</td>";
                                    // $totAbsen[$dtKary]+=$absen[$dtKary][$dtJmlhAbsDbyr];
                                    // $ttabsen+=$absen[$dtKary][$dtJmlhAbsDbyr];
                                    // $tabsen[$dtJmlhAbsDbyr]+=$absen[$dtKary][$dtJmlhAbsDbyr];
                                    // $grTotDbyr[$dtJmlhAbsDbyr]+=$absen[$dtKary][$dtJmlhAbsDbyr];
                                // }
                                // $stream.="<td align=right>".@number_format($totAbsen[$dtKary],2)."</td>";
                                // foreach($dtAbsTdkByr as $dtTidakDbyr){
                                    // setIt($absen[$dtKary][$dtTidakDbyr],0);
                                    // setIt($totAbsenTdkDbyr[$dtKary],0);
                                    // setIt($grTotTdkDbyr[$dtTidakDbyr],0);
                                    // $stream.="<td align=right>".@number_format($absen[$dtKary][$dtTidakDbyr],2)."</td>";
                                    // $totAbsenTdkDbyr[$dtKary]+=$absen[$dtKary][$dtTidakDbyr];
                                    // $ttnabsen+=$absen[$dtKary][$dtTidakDbyr];
                                    // $tnabsen[$dtTidakDbyr]+=$absen[$dtKary][$dtTidakDbyr];
                                    // $grTotTdkDbyr[$dtTidakDbyr]+=$absen[$dtKary][$dtTidakDbyr];
                                // }
                                // $stream.="<td align=right>".@number_format($totAbsenTdkDbyr[$dtKary],2)."</td>";

                                $arrPlus=Array();
                                $s=0;
                                //$brsPlus2=0;
                                foreach($arrIdKompPls as $lstKompPls)
                                {
                                    if($arrJmlh[$dtKary.$lstKompPls]==''){
                                    setIt($arrJmlh[$dtKary.$lstKompPls],0);
                                    }
                                    $stream.="<td align=right>".@number_format($arrJmlh[$dtKary.$lstKompPls],2)."</td>";
                                    $arrPlus[$s]=$arrJmlh[$dtKary.$lstKompPls];
                                    $s++;
                                    //$brsPlus2++;
                                    /*if($brsPlus2==1)
                                    {
                                        setIt($arrJmlh[$dtKary.$peng1],0);
                                        setIt($arrJmlh[$dtKary.$peng2],0);
                                        $stream.="<td>-".@number_format($arrJmlh[$dtKary.$peng1],2)."</td>";
                                        $stream.="<td>-".@number_format($arrJmlh[$dtKary.$peng2],2)."</td>";
                                    }*/

                                }
                                $totDpt=array_sum($arrPlus);
                                //$totDpt=array_sum($arrPlus)-($arrJmlh[$dtKary.$peng1]+$arrJmlh[$dtKary.$peng2]);
                                $stream.="<td align=right>".@number_format($totDpt,2)."</td>";


                                $arrMin=Array();
                                $q=0;
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
                                         if(!isset($arrJmlh[$dtKary.$lstKompMin])){
                                         setIt($arrJmlh[$dtKary.$lstKompMin],0);
                                         }
                                         $stream.="<td align=right>".@number_format($arrJmlh[$dtKary.$lstKompMin])."</td>";
                                         $arrMin[$q]=$arrJmlh[$dtKary.$lstKompMin];
                                         $q++;
                                   // }
                                }
                                $gajiBersih=$totDpt-array_sum($arrMin);				

                                //$stream.="<td align=right>".@number_format(array_sum($arrPlus),2)."</td>";
                                $stream.="<td align=right>".@number_format(array_sum($arrMin),2)."</td>";
                                $stream.="<td align=right>".@number_format($gajiBersih,0)."</td></tr>";	

                                }
                                $stream.="<tr><td colspan=8 align=right>".$_SESSION['lang']['total']."</td>";

								//++++++++++++++++++++++++++++++++++++++++++++++++++
								$stream.="<td align=right>".@number_format($ttlembur,2)."</td>";
								// foreach($dtAbsByr as $dtJmlhAbsDbyr){
                                    // $stream.="<td align=right>".@number_format($tabsen[$dtJmlhAbsDbyr],2)."</td>";
                                // }
                                // $stream.="<td align=right>".@number_format($ttabsen,2)."</td>";
                                // foreach($dtAbsTdkByr as $dtTidakDbyr){
                                    // $stream.="<td align=right>".@number_format($tnabsen[$dtTidakDbyr],2)."</td>";
                                // }
                                // $stream.="<td align=right>".@number_format($ttnabsen,2)."</td>";
								
								//++++++++++++++++++++++++++++++++++++++++++++++++++

                                $s=0;
                                //$brsPlus2=0;
                                $arrPlus=array();
                                foreach($arrIdKompPls as $lstKompPls)
                                {
                                    setIt($arrTotal[$lstKompPls],0);
                                    $stream.="<td align=right>".@number_format($arrTotal[$lstKompPls],2)."</td>";
                                    $arrPlus[$s]=$arrTotal[$lstKompPls];
                                    $s++;
                                    //$brsPlus2++;
                                    /*if($brsPlus2==1)
                                    {
                                        setIt($arrTotal[$peng1],0);
                                        setIt($arrTotal[$peng2],0);
                                        $stream.="<td>-".@number_format($arrTotal[$peng1],2)."</td>";
                                        $stream.="<td>-".@number_format($arrTotal[$peng2],2)."</td>";
                                    }*/
                                }
                                $totDpt=array_sum($arrPlus);
                                //$totDpt=array_sum($arrPlus)-($arrTotal[$peng1]+$arrTotal[$peng2]);
                                $stream.="<td align=right>".@number_format($totDpt,2)."</td>";


                                $arrMin=Array();
                                $q=0;
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
                                        setIt($arrTotal[$lstKompMin],0);
                                         $stream.="<td align=right>".@number_format($arrTotal[$lstKompMin])."</td>";
                                         $arrMin[$q]=$arrTotal[$lstKompMin];
                                         $q++;
                                   // }
                                }
                                $gajiBersih=$totDpt-array_sum($arrMin);				

                                //$stream.="<td align=right>".@number_format(array_sum($arrPlus),2)."</td>";
                                $stream.="<td align=right>".@number_format(array_sum($arrMin),2)."</td>";
                                $stream.="<td align=right>".@number_format($gajiBersih,0)."</td>";	
                                $stream.="</tr>";
                }
                
                    //=================================================


                        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];
                        $dte=date("YmdHms");
                        $nop_="GajiHarian".$_SESSION['empl']['lokasitugas'].$dte;
                         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                         gzwrite($gztralala, $stream);
                         gzclose($gztralala);
                         echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls.gz';
                            </script>";

        break;
        default:
        break;
}
?>