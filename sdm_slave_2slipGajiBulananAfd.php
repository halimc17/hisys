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
$perod = checkPostGet('perod','');
$idKry = checkPostGet('idKry','');
$idAfd = checkPostGet('idAfd','');
$tPkary = checkPostGet('tPkary2','');
$kdBag2 = checkPostGet('kdBag2','');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$arrBln=array(1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"Mei",6=>"Jun",7=>"Jul",8=>"Agu",9=>"Sep",10=>"Okt",11=>"Nov",12=>"Des");
$rNmTipe=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$lksiTgs=substr($idAfd,0,4);
$digitformat=0;

#= komponen yang tidak termasuk di slip gaji
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='KOMGJEXSLP'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
	$exslip=$bar['nilai'];
$sOrg="select namaorganisasi,tipe,induk from ".$dbname.".organisasi where kodeorganisasi='".$idAfd."'";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$rOrg=$qOrg->fetch();
$path='images/logo.jpg';
  $nmTable="sdm_gaji_vw";
if($rOrg['tipe']=='HOLDING'){
  $nmTable="sdm_gajiho";
}


if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    if($idAfd!=''){
      if($rOrg['tipe']=='HOLDING'){
        $add="b.kodeorganisasi='".$rOrg['induk']."'";
      }else{
        $add="b.lokasitugas='".$idAfd."'";  
      }
    }
    else
    {
        exit("Error: Working unit required");
    }
    if($kdBag2!='')
    {
        $add.=" and b.bagian='".$kdBag2."'";
    }
}
else
{
    if(strlen($idAfd)<6)
    {
        if($rOrg['tipe']=='HOLDING'){
            $add="b.kodeorganisasi='".$idAfd."'";
        }else{
            // $add="b.lokasitugas='".$idAfd."' and (b.subbagian is null or b.subbagian='')";  
            $add="b.lokasitugas='".$idAfd."'";  
        }
    }
    else
    {
        $add="b.subbagian='".$idAfd."'";
    }

    if($kdBag2!='')
    {
        $add.=" and b.bagian='".$kdBag2."'";
    }
}
				$dtTipe="";
                if($tPkary!='')
                {
                    $dtTipe=" and b.tipekaryawan='".$tPkary."'";
                }
				
$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji 
		where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$perod."' and jenisgaji='B'";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
$rTgl=$qTgl->fetch();		

$nmbank= makeOption($dbname, 'datakaryawan', 'karyawanid,namabank');
$norek= makeOption($dbname, 'datakaryawan', 'karyawanid,norekeningbank');		
				
switch($proses)
{
        case'preview':



        //periode gaji
        $bln=explode('-',$perod);
        $idBln=intval($bln[1]);	
		
		
        //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama from 
               ".$dbname.".".$nmTable." a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
               where b.sistemgaji='Bulanan' and a.periodegaji='".$perod."' and ".$add." ".$dtTipe." order by b.namakaryawan asc";
       // echo $sSlip;
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
                    $arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
                    $arrTglMsk[$rSlip['karyawanid']]=$rSlip['tanggalmasuk'];
                    $arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
                    $arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
                    $arrBag[$rSlip['karyawanid']]=$rSlip['bagian'];
                    $arrJbtn[$rSlip['karyawanid']]=$rSlip['namajabatan'];
                    $arrDept[$rSlip['karyawanid']]=$rSlip['nama'];
                    $arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];
                    }
                }
          //array data komponen penambah dan pengurang
          $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".".$nmTable." b on a.id=b.idkomponen
                   where a.plus=1 and b.jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and a.id not in (".$exslip.") order by a.id";

          $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
              $arrIdKompPls[]=$rKomp['id'];
              $arrNmKomPls[$rKomp['id']]=$rKomp['name'];
          }

          $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
                   left join ".$dbname.".".$nmTable." b on a.id=b.idkomponen
                   where a.plus=0 and b.jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and a.id not in (".$exslip.") order by a.id";
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
              $arrIdKompMin[]=$rKomp['id'];
              $arrNmKomMin[$rKomp['id']]=$rKomp['name'];
          }
          // echo"<pre>";
          // print_r($arrIdKompMin);
          // echo"</pre>";
          // exit('warning'.$sKomp);
		  
		  #= natura
		$arrIdKompPls[]='60';
		$arrNmKomPls['60']='Natura';
		$str="SELECT * FROM ".$dbname.".sdm_catu where periodegaji='".$perod."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){
			$arrJmlh[$bar['karyawanid'].'60']=$bar['jumlahrupiah'];
		}
		  
		  
          $jmlhKary=count($arrKary);
          //exit("Error".$jmlhKary);
                  foreach($arrKary as $dtKary)
                  {
                      echo"<table cellspacing=1 border=0 width=500>
                        <tr style='border-bottom:#000 solid 2px; border-top:#000 solid 2px;'><td valign=top>
                        <table border=0 width=110%>
                        <tr><td width=49% valign=top><table border=0><tr><td colspan=3>".$_SESSION['lang']['slipGaji'].": ".$arrBln[$idBln]."-".$bln[0]."</td></tr>
                        <tr><td>".$_SESSION['lang']['nik']."/".$_SESSION['lang']['tmk']."</td><td>:</td><td>".$arrNik[$dtKary]."/".tanggalnormal($arrTglMsk[$dtKary])."</td></tr>
                        <tr><td>".$_SESSION['lang']['nama']."</td><td>:</td><td>".$arrNmKary[$dtKary]."</td></tr>
                        </table></td><td width=51% valign=top><table border=0>
                        <tr><td colspan=3>&nbsp;</td></tr>
                        <tr><td>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['bagian']."</td><td>:</td><td>".$rOrg['namaorganisasi']."/".$arrBag[$dtKary]."</td></tr>
                        <tr><td>".$_SESSION['lang']['jabatan']."</td><td>:</td><td>".$arrJbtn[$dtKary]."</td></tr>
                        </table></td></tr>
                        </table>
                        </td></tr>
                        <tr>
                        <td><table width=100%>
                        <thead>
                        <tr><td align=center>".$_SESSION['lang']['penambah']."</td><td align=center>".$_SESSION['lang']['pengurang']."</td>
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
                            if($arrJmlh[$dtKary.$lstKompPls]==''){
                                    setIt($arrJmlh[$dtKary.$lstKompPls],0);
                                  }
                          if(intval($arrJmlh[$dtKary.$idKompPls])!=0){  
                          echo"<tr><td>".$arrNmKomPls[$idKompPls]."</td><td>:Rp.</td><td align=right> ".number_format($arrJmlh[$dtKary.$idKompPls],$digitformat)."</td></tr>";
                          }
                            $arrPlus[$s]=$arrJmlh[$dtKary.$idKompPls];
                            $s++;
                      }
                      echo"</table></td>
                        <td valign=top>
                        <table width=100%>";
                    
                        $arrMin=Array();
                        $q=0;
                        foreach($arrIdKompMin as $idKompMin)
                          {
                                if($arrJmlh[$dtKary.$lstKompPls]==''){
                                    setIt($arrJmlh[$dtKary.$lstKompPls],0);
                                  }
                             if(intval($arrJmlh[$dtKary.$idKompMin])!=0){    
                              echo"<tr><td>".$arrNmKomMin[$idKompMin]."</td><td>:Rp.</td><td align=right> ".number_format($arrJmlh[$dtKary.$idKompMin],$digitformat)."</td></tr>";
                             }
                                $arrMin[$q]=$arrJmlh[$dtKary.$idKompMin];
                                $q++;
                          }
                          
                          $gajiBersih=array_sum($arrPlus)-array_sum($arrMin);
                          
                          echo"</table>
                        </td></tr>
                        <tr><td colspan=2><table width=100%>
                        <tr><td>Total Penambahan</td><td>:Rp.</td><td align=right> ".number_format(array_sum($arrPlus),2)."</td><td>Total Pengurangan</td><td>:Rp.</td><td align=right> ".number_format(array_sum($arrMin),$digitformat)."</td></tr>
                        <tr><td>Gaji Bersih</td><td>:Rp.</td><td align=right> ".number_format((array_sum($arrPlus)-array_sum($arrMin)),2)."</td><td>&nbsp;</td><td>&nbsp;</td><td align=right> &nbsp;</td></tr>
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
        //$perod=$_GET['perod'];
        //$idAfd=$_GET['idAfd'];
        //$idKry=$_GET['idKry'];
        //$kdBag2=$_GET['kdBag2'];

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
        //periode gaji
        $bln=explode('-',$perod);
        $idBln=intval($bln[1]);	

        //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        $sSlip="select distinct a.*,b.subbagian,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama from 
               ".$dbname.".".$nmTable." a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
               where b.sistemgaji='Bulanan' and a.periodegaji='".$perod."' and ".$add."  ".$dtTipe." order by b.namakaryawan asc";
			  
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
                    $arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
                    $arrTglMsk[$rSlip['karyawanid']]=$rSlip['tanggalmasuk'];
                    $arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
                    $arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
                    $arrBag[$rSlip['karyawanid']]=$rSlip['bagian'];
                    $arrJbtn[$rSlip['karyawanid']]=$rSlip['namajabatan'];
                    $arrDept[$rSlip['karyawanid']]=$rSlip['nama'];
                    $arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];
					$arrafd[$rSlip['karyawanid']]=$rSlip['subbagian'];
					$arrtipekar[$rSlip['karyawanid']]=$rSlip['tipekaryawan'];
					if($rSlip['idkomponen']==1){
						$arrhk[$rSlip['karyawanid']]=number_format($rSlip['hk'],2);
					}
					
                    }
                }

          //array data komponen penambah dan pengurang
          $sKomp="select id,name,plus from ".$dbname.".sdm_ho_component where plus=1 ";
          $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
              $arrIdKompPls[]=$rKomp['id'];
              $arrNmKomPls[$rKomp['id']][1]=$rKomp['name'];
          }
          $sKomp2="select distinct id,name,plus from ".$dbname.".sdm_ho_component
                   where plus=0  order by id";
		  $qKomp2=$owlPDO->query($sKomp2) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp2->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp2=$qKomp2->fetch())
          {
              $arrIdKompPls[]=$rKomp2['id'];
              $arrNmKomPls[$rKomp2['id']][0]=$rKomp2['name'];
          }
          //komponen
            $arrMinusId=Array();
            $arrMinusName=Array();
            $str="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".".$nmTable." b on a.id=b.idkomponen
                   where plus=0 and jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and id not in (".$exslip.") order by id";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
            {
                array_push($arrMinusId,$bar->id);
                array_push($arrMinusName,$bar->name);
            }
            //samakan
            $arrPlusId=$arrMinusId;
            $arrPlusName=$arrMinusName;
            //Kosongkan
            for($r=0;$r<count($arrMinusId);$r++)
            {
                 $arrPlusId[$r]='';
                 $arrPlusName[$r]='';
            }
            //$str="select  id,name from ".$dbname.".sdm_ho_component where plus='1' and id not in ('26','28') order by id";
            $str="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".".$nmTable." b on a.id=b.idkomponen
                   where plus=1 and jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and id not in (".$exslip.") order by id";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$n=-1;
            while($bar=$res->fetch())
            {
                $n+=1;
                $arrPlusId[$n]=$bar->id;
                $arrPlusName[$n]=$bar->name;
            }
           $arrValPlus=Array();
           $arrValMinus=Array();
           for($x=0;$x<count($arrPlusId);$x++)
           {
                $arrValPlus[$x]=0;
                $arrValMinus[$x]=0;
           }
           $str3="select jumlah,idkomponen,a.karyawanid,c.plus from ".$dbname.".".$nmTable." a 
                  left join ".$dbname.".sdm_ho_component c on a.idkomponen=c.id
                 where a.kodeorg='".substr($idAfd,0,4)."' and a.periodegaji='".$perod."' ";
           $res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
		   $res3->setFetchMode(PDO::FETCH_ASSOC);
           while($bar3=$res3->fetch())
           {
               if($bar3['plus']=='1')
               {
                    if($bar3['jumlah']!='')
                    {
                        $arrValPlus[$bar3['karyawanid']][$bar3['idkomponen']]=$bar3['jumlah'];
                    }
               }
               elseif($bar3['plus']=='0')
               {
                    if($bar3['jumlah']!='')
                    {
                        $arrValMinus[$bar3['karyawanid']][$bar3['idkomponen']]=$bar3['jumlah'];
                    }
               } 
            }	 

		$str="SELECT * FROM ".$dbname.".sdm_catu where periodegaji='".$perod."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){
			$arrPlusId[$n+1]='60';
			$arrPlusName[$n+1]='Natura';
			$arrValPlus[$bar['karyawanid']]['60']=$bar['jumlahrupiah'];
			$totalcatu[$bar['karyawanid']]=$bar['totalcatu'];
		}	
			
			
        foreach($arrKary as $dtKary)
        {
			@$st++;
                        //$pdf->Image('images/logo.jpg',$pdf->GetX(),$pdf->GetY(),10);
                       // $pdf->SetX($pdf->getX()+10);
					   
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
                        $pdf->Cell(28,4,': '.@$nmorg[$arrafd[$dtKary]],0,1,'L');		
                        
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
                         // if(intval(@$arrValPlus[$dtKary][$arrPlusId[$mn]])!=0){
                                $pdf->Cell(25,4,$arrPlusName[$mn],0,0,'L');
                         // }else{
                            // $pdf->Cell(25,4,'',0,0,'L'); 
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
                                     // if(intval($arrValPlus[$dtKary][$arrPlusId[$mn]])!=0){                                        
                                         $pdf->Cell(5,4,":Rp.",0,0,'L');
                                         $pdf->Cell(18,4,number_format($arrValPlus[$dtKary][$arrPlusId[$mn]],2,'.',','),'R',0,'R');
                                     // }else{
                                        // $pdf->Cell(5,4,"",0,0,'L');
                                        // $pdf->Cell(18,4,'','R',0,'R');                                         
                                    // }
                                        $arrPlus[$dtKary]+=$arrValPlus[$dtKary][$arrPlusId[$mn]];
                                    }
                                }
                                // if(intval(@$arrValMinus[$dtKary][$arrMinusId[$mn]])!=0){
                                     $pdf->Cell(25,4,@$arrMinusName[$mn],0,0,'L');
                                // }else{
                                    // $pdf->Cell(25,4,'',0,0,'L');
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
                                // if(intval($arrValMinus[$dtKary][$arrMinusId[$mn]])!=0){                                        
                                       $pdf->Cell(5,4,":Rp.",0,0,'L');
                                      $pdf->Cell(18,4,number_format(($arrValMinus[$dtKary][$arrMinusId[$mn]]),2,'.',','),0,1,'R');
                                // }else{
                                      // $pdf->Cell(5,4,"",0,0,'L');
                                      // $pdf->Cell(18,4,'',0,1,'R');                                    
                                // }
                                      $arrMin[$dtKary]+=$arrValMinus[$dtKary][$arrMinusId[$mn]];
                                    }
                                }
                        }
                                 $pdf->Cell(25,4,'Total.Pendapatan','TB',0,'L');
                                $pdf->Cell(5,4,":Rp.",'TB',0,'L');
                                        $pdf->Cell(18,4,number_format($arrPlus[$dtKary],2,'.',','),'TB',0,'R');
                                $pdf->Cell(25,4,'Total.Pengurangan','TB',0,'L');
                                $pdf->Cell(5,4,":Rp.",'TB',0,'L');
                                        $pdf->Cell(18,4,number_format(($arrMin[$dtKary]),2,'.',','),'TB',1,'R');

                        $pdf->SetFont('Arial','B',6);
                        $pdf->Cell(25,4,'Gaji.Bersih',0,0,'L');
                        $pdf->Cell(5,4,":Rp.",0,0,'L');
                                $pdf->Cell(18,4,number_format(($arrPlus[$dtKary]-($arrMin[$dtKary])),2,'.',','),0,0,'R');
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
						$pdf->Cell(10,4,number_format($totalcatu[$dtKary],2),0,0,'R');
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
                        if($pdf->GetY()>140 and $pdf->col<1)
                                $pdf->AcceptPageBreak();
                        if ($pdf->GetY()>140 and $pdf->col>0)
                           {
                                //$pdf->lewat=true;
                                // $pdf->AcceptPageBreak();
                                //$pdf->SetY(277-$pdf->GetY());
                                $r=275-$pdf->GetY();
                                $pdf->Cell(80,$r,'',0,1,'L');

                                //$pdf->ln();
                           }
                        //else   
                        //$pdf->lewat=false; 	

                        $pdf->cell(-1,3,'',0,0,'L');	
                }
}
else
{
        $pdf->Image('images/logo.jpg',$pdf->GetX(),$pdf->GetY(),10);
        $pdf->SetX($pdf->getX()+8);
        $pdf->SetFont('Arial','B',8);	
        $pdf->Cell(70,5,$_SESSION['org']['namaorganisasi'],0,1,'L');
        $pdf->SetFont('Arial','',5);	
        $pdf->Cell(60,3,'NOT FOUND','T',0,'L');
}
        $pdf->Output();

        break;

        case'excel':
        //periode gaji
        $bln=explode('-',$perod);
        $idBln=intval($bln[1]);	

          //array data komponen penambah dan pengurang
		  /*
          $sKomp="select id,name from ".$dbname.".sdm_ho_component where plus='1'  and id not in ('26','28') ";
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
              $arrIdKompPls[]=$rKomp['id'];
              $arrNmKomPls[$rKomp['id']]=$rKomp['name'];
          }
          $totPlus=count($arrIdKompPls);
          $brsPlus=0;
          $sKomp="select id,name from ".$dbname.".sdm_ho_component where plus='0'  ";
          $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
              $arrIdKompMin[]=$rKomp['id'];
              $arrNmKomMin[$rKomp['id']]=$rKomp['name'];
          }
		  */
		  
		  
		    #= bentuk baru komponen yg ada saja
			$str="select distinct idkomponen,e.name,e.plus from
			".$dbname.".".$nmTable." a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
			left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
			left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
			left join  ".$dbname.".sdm_ho_component e on a.idkomponen=e.id
			where b.sistemgaji='Bulanan' and idkomponen not in (".$exslip.") and a.periodegaji='".$perod."' and ".$add." ".$dtTipe."";
			// echo $str;
      $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar=$res->fetch()){
				if($bar['plus']>0){
					$arrIdKompPls[]=$bar['idkomponen'];
					$arrNmKomPls[$bar['idkomponen']]=$bar['name'];
				}else{
					$arrIdKompMin[]=$bar['idkomponen'];
					$arrNmKomMin[$bar['idkomponen']]=$bar['name'];
				}
			}
		  
		  
		  
		  // ".$add." ".$dtTipe."
		 // exit("Error".$add._.$dtTipe); 
		  
		 $arrIdKompPls[]='60';
		$str="SELECT * FROM ".$dbname.".sdm_catu b where periodegaji='".$perod."' and kodeorg='".$lksiTgs."' ".$dtTipe." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){
			// $arrIdKompPls[$n+1]='60';
			
			$arrNmKomPls['60']='Natura';
			$arrJmlh[$bar['karyawanid'].'60']=$bar['jumlahrupiah'];
			$arrTotal['60']+=$bar['jumlahrupiah'];
			// $totalcatu[$bar['karyawanid']]=$bar['totalcatu'];
			
		}		
		  

                        $sPeriod="select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where jenisgaji='B' and periode='".$perod."' and kodeorg='".substr($idAfd,0,4)."'";	
						$qPeriod=$owlPDO->query($sPeriod) or die(print " Gagal: ".PDOException::getMessage());
						$qPeriod->setFetchMode(PDO::FETCH_ASSOC);
                        $rPeriod=$qPeriod->fetch();
                        $mulai=tanggalnormal($rPeriod['tanggalmulai']);
                        $selesi=tanggalnormal($rPeriod['tanggalsampai']);

                        $stream.="
                        <table>
                        <tr><td colspan=15 align=center>List Data Gaji Harian, Unit : ".$idAfd."</td></tr>
                        <tr><td colspan=15 align=center>Periode : ".$mulai." s.d. ".$selesi."</td></tr>
                        </table>
                        <table border=1>
                        <tr>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>No.</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['namakaryawan']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['nik']."/".$_SESSION['lang']['tmk']."</td>";
                         // if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                         // {
                            $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['subbagian']."</td>";
                         // }
                         $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>No. Rekening</td>";
                         $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['tipekaryawan']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['totLembur']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['bagian']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['statuspajak']."</td>

                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['jabatan']."</td>";
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
						/*
                        $stream.="<td bgcolor=#DEDEDE align=center  colspan='".($rowabs+1)."'>".$_SESSION['lang']['hkdibayar']."</td>";
                        $stream.="<td bgcolor=#DEDEDE align=center colspan='".($rowabs2+1)."'>".$_SESSION['lang']['hktdkdibayar']."</td>";
						*/	
					   $plsCol=count($arrIdKompPls);
                        $minCol=count($arrIdKompMin);
                        $stream.="<td bgcolor=#DEDEDE align=center colspan='".($plsCol+1)."'>".$_SESSION['lang']['penambah']."</td>";
                        $stream.="<td bgcolor=#DEDEDE align=center colspan='".($minCol+1)."'>".$_SESSION['lang']['pengurang']."</td>";
                        $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>GAJI BERSIH</td></tr><tr>";
                        /*
						while($rdbyr=$qhkdbyr->fetch()){
                           $stream.="<td bgcolor=#DEDEDE align=center>".$rdbyr['kodeabsen']."</td>";
                            $dtAbsByr[]=$rdbyr['kodeabsen'];
                        }
                        $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."</td>";
                        while($rdbyr=$qhkdbyr2->fetch()){
                            $stream.="<td bgcolor=#DEDEDE align=center>".$rdbyr['kodeabsen']."</td>";
                            $dtAbsTdkByr[]=$rdbyr['kodeabsen'];
                        }
                           $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."</td>";
                        */
						foreach($arrIdKompPls as $lstKompPls)
                                {
                                    $brsPlus++;
                                    $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomPls[$lstKompPls]."</td>";
                                    /*if($brsPlus==1)
                                    {
                                        $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[37]."</td>";
                                        $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[36]."</td>";
                                    }*/

                                }
                        $stream.="<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['totalPendapatan']."</td>";

                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
                                         $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[$lstKompMin]."</td>";
                                    //}
                                }			

                      $stream.="<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['totalPotongan']."</td></tr>";

        //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.subbagian,
               b.norekeningbank from
               ".$dbname.".".$nmTable." a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
               where b.sistemgaji='Bulanan' and a.periodegaji='".$perod."' and ".$add." ".$dtTipe." order by b.subbagian asc,b.namakaryawan asc";
		$qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
		$qSlip->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=owlBaris($qSlip);
        if($rCek>0)
        {
                while($rSlip=$qSlip->fetch())
                {
                    if($rSlip['karyawanid']!='')
                    {
						setIt($arrTotal[$rSlip['idkomponen']],0);
						$arrKary[$rSlip['karyawanid']]=$rSlip['karyawanid'];
						$arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
						$arrTglMsk[$rSlip['karyawanid']]=$rSlip['tanggalmasuk'];
						$arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
						$arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
						$arrBag[$rSlip['karyawanid']]=$rSlip['bagian'];
						$arrJbtn[$rSlip['karyawanid']]=$rSlip['namajabatan'];
						$arrTipekary[$rSlip['karyawanid']]=$rSlip['tipekaryawan'];
						$arrStatPjk[$rSlip['karyawanid']]=$rSlip['statuspajak'];
						$arrDept[$rSlip['karyawanid']]=$rSlip['nama'];
						$arrSubbagian[$rSlip['karyawanid']]=$rSlip['subbagian'];
						$arrRek[$rSlip['karyawanid']]=$rSlip['norekeningbank'];
						$arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];
						$arrTotal[$rSlip['idkomponen']]+=$rSlip['jumlah'];
                    }
                }
                $sTot="select tipelembur,jamaktual,karyawanid from ".$dbname.".sdm_lemburdt where substr(kodeorg,1,4)='".substr($idAfd,0,4)."' and tanggal between '".$rPeriod['tanggalmulai']."' and '".$rPeriod['tanggalsampai']."'";	
				$qTot=$owlPDO->query($sTot) or die(print " Gagal: ".PDOException::getMessage());
				$qTot->setFetchMode(PDO::FETCH_ASSOC);
                while($rTot=$qTot->fetch())
                {
                        $sJum="select jamlembur as totalLembur from ".$dbname.".sdm_5lembur where tipelembur='".$rTot['tipelembur']."'
                        and jamaktual='".$rTot['jamaktual']."' and kodeorg='".substr($idAfd,0,4)."'";
                        $qJum=$owlPDO->query($sJum) or die(print " Gagal: ".PDOException::getMessage());
						$qJum->setFetchMode(PDO::FETCH_ASSOC);
                        $rJum=$qJum->fetch();
                        @$jumTot[$rTot['karyawanid']]+=$rJum['totalLembur'];
                }
                //$peng1=37;
               // $peng2=36;
                    foreach($arrKary as $dtKary)
                    {		
                        $no+=1;
                                $stream.="<tr class=rowcontent>
                                <td>".$no."</td>
                                <td>".$arrNmKary[$dtKary]."</td>";
                                $stream.="<td>'".$arrNik[$dtKary]."</td>";
                                $ocldt=9;
                                // if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                                // {
                                    $ocldt=10;
                                    $stream.="<td>".$arrSubbagian[$dtKary]."</td>";
                                // }
								setIt($jumTot[$dtKary],0);
                                $stream.="
                                <td>'".$arrRek[$dtKary]."</td>
                                <td>".$rNmTipe[$arrTipekary[$dtKary]]."</td>
                                <td>".$jumTot[$dtKary]."</td>
                                <td>".$arrDept[$dtKary]."</td> 
                                <td>".$arrStatPjk[$dtKary]."</td>
                                <td>".$arrJbtn[$dtKary]."</td>";
								/*
                                foreach($dtAbsByr as $dtJmlhAbsDbyr){
									setIt($brt[$dtKary][$dtJmlhAbsDbyr],0);
									setIt($totAbsen[$dtKary],0);
									setIt($grTotDbyr[$dtJmlhAbsDbyr],0);
                                    $stream.="<td align=right>".number_format($brt[$dtKary][$dtJmlhAbsDbyr])."</td>";
                                    $totAbsen[$dtKary]+=$brt[$dtKary][$dtJmlhAbsDbyr];
                                    $grTotDbyr[$dtJmlhAbsDbyr]+=$brt[$dtKary][$dtJmlhAbsDbyr];
                                }
                                $stream.="<td align=right>".number_format($totAbsen[$dtKary])."</td>";
                                foreach($dtAbsTdkByr as $dtTidakDbyr){
									setIt($brt[$dtKary][$dtTidakDbyr],0);
									setIt($totAbsenTdkDbyr[$dtKary],0);
									setIt($grTotTdkDbyr[$dtTidakDbyr],0);
                                    $stream.="<td align=right>".number_format($brt[$dtKary][$dtTidakDbyr])."</td>";
                                    $totAbsenTdkDbyr[$dtKary]+=$brt[$dtKary][$dtTidakDbyr];
                                     $grTotTdkDbyr[$dtTidakDbyr]+=$brt[$dtKary][$dtTidakDbyr];
                                }
                                $stream.="<td align=right>".number_format($totAbsenTdkDbyr[$dtKary])."</td>";
								*/
                                $arrPlus=Array();
                                $s=0;
                                $brsPlus2=0;
                                foreach($arrIdKompPls as $lstKompPls)
                                {
									                  if($arrJmlh[$dtKary.$lstKompPls]==''){
                                    setIt($arrJmlh[$dtKary.$lstKompPls],0);
                                    }
                                    $stream.="<td align=right>".number_format($arrJmlh[$dtKary.$lstKompPls],$digitformat)."</td>";
                                    $arrPlus[$s]=$arrJmlh[$dtKary.$lstKompPls];
                                    $s++;
                                    $brsPlus2++;
                                    /*if($brsPlus2==1)
                                    {
										setIt($arrJmlh[$dtKary.$peng1],0);
										setIt($arrJmlh[$dtKary.$peng2],0);
                                        $stream.="<td>-".number_format($arrJmlh[$dtKary.$peng1],2)."</td>";
                                        $stream.="<td>-".number_format($arrJmlh[$dtKary.$peng2],2)."</td>";
                                    }*/

                                }
                                $totDpt=array_sum($arrPlus);
                                //$totDpt=array_sum($arrPlus)-($arrJmlh[$dtKary.$peng1]+$arrJmlh[$dtKary.$peng2]);
                                $stream.="<td align=right>".number_format($totDpt,2)."</td>";


                                $arrMin=Array();
                                $q=0;
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                   // {
										                     if($arrJmlh[$dtKary.$lstKompPls]==''){
                                         setIt($arrJmlh[$dtKary.$lstKompPls],0);
                                         }
                                         $stream.="<td align=right>".number_format($arrJmlh[$dtKary.$lstKompMin],$digitformat)."</td>";
                                         $arrMin[$q]=$arrJmlh[$dtKary.$lstKompMin];
                                         $q++;
                                    //}
                                }
                                $gajiBersih=$totDpt-array_sum($arrMin);				

                                //$stream.="<td align=right>".number_format(array_sum($arrPlus),2)."</td>";
                                $stream.="<td align=right>".number_format(array_sum($arrMin),$digitformat)."</td>";
                                $stream.="<td align=right>".number_format($gajiBersih,$digitformat)."</td></tr>";	
                      }
                                // $stream.="<tr><td colspan=".($ocldt+$rowabs+$rowabs2+2)." align=right>".$_SESSION['lang']['total']."</td>";
                                $stream.="<tr><td colspan=".($ocldt)." align=right>".$_SESSION['lang']['total']."</td>";

                                $s=0;
                                $brsPlus2=0;
                                $arrPlus=array();
                                foreach($arrIdKompPls as $lstKompPls)
                                {
									setIt($arrTotal[$lstKompPls],0);
                                    $stream.="<td align=right>".number_format($arrTotal[$lstKompPls],$digitformat)."</td>";
                                    $arrPlus[$s]=$arrTotal[$lstKompPls];
                                    $s++;
                                    $brsPlus2++;
                                    /*if($brsPlus2==1)
                                    {
										setIt($arrTotal[$peng1],0);
										setIt($arrTotal[$peng2],0);
                                        $stream.="<td>-".number_format($arrTotal[$peng1],2)."</td>";
                                        $stream.="<td>-".number_format($arrTotal[$peng2],2)."</td>";
                                    }*/
                                }
                                $totDpt=array_sum($arrPlus);
                                //$totDpt=array_sum($arrPlus)-($arrTotal[$peng1]+$arrTotal[$peng2]);
                                $stream.="<td align=right>".number_format($totDpt,$digitformat)."</td>";

                                $arrMin=Array();
                                $q=0;
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
										setIt($arrTotal[$lstKompMin],0);
                                         $stream.="<td align=right>".number_format($arrTotal[$lstKompMin])."</td>";
                                         $arrMin[$q]=$arrTotal[$lstKompMin];
                                         $q++;
                                   // }
                                }
                                $gajiBersih=$totDpt-array_sum($arrMin);				

                                //$stream.="<td align=right>".number_format(array_sum($arrPlus),2)."</td>";
                                $stream.="<td align=right>".number_format(array_sum($arrMin),$digitformat)."</td>";
                                $stream.="<td align=right>".number_format($gajiBersih,$digitformat)."</td>";	
                                $stream.="</tr>";
                }
                else
                {
                    $stream.="<tr><td colspan=20>&nbsp;</td></tr>";
                }
                
                
                // exit("Error:".$stream);
                        // echo "warning:".$strx;
                        //=================================================

                        
                        $stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
                        $dte=date("YmdHms");
                        $nop_="GajiBulananAfdeling_".$_SESSION['empl']['lokasitugas'].$dte;
                         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                         gzwrite($gztralala, $stream);
                         gzclose($gztralala);
                         echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls.gz';
                            </script>";

        break;
         case'getPeriode':
            $optPeriode="<option value''>".$_SESSION['lang']['pilihdata']."</option>";
            $sPeriode="select periode from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($idAfd,1,4)."' and jenisgaji='B'";
			$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
			$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
            while($rPeriode=$qPeriode->fetch())
            {
                $optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
            }
            echo $optPeriode;
        break;
        case 'getDivisi':
            $optDivisi = "<option value=''>".$_SESSION['lang']['all']."</option>";
            if($idAfd != '')
            {
                $sDivisi = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                            where induk='".$idAfd."' and tipe not like '%GUDANG%'
                            order by namaorganisasi asc";
                $qDivisi = $owlPDO->query($sDivisi) or die(print " Gagal: ".PDOException::getMessage());
                $qDivisi->setFetchMode(PDO::FETCH_ASSOC);
                while($rDivisi = $qDivisi->fetch())
                {
                    $optDivisi .= "<option value='".$rDivisi['kodeorganisasi']."'>".$rDivisi['kodeorganisasi']." - ".$rDivisi['namaorganisasi']."</option>";
                }
            }
            echo $optDivisi;
        break;
        case 'getnama':
            $dakarbulanan=0;
            $str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$idAfd."' and periodegaji='".$period."' "; 
            $res = fetchdata($str);
            if(count($res)>0)
            { 
            $dakarbulanan=1;
            }
            if($dakarbulanan==0){
                $sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$idAfd."' and sistemgaji like '%Bulanan%' order by namakaryawan asc";
            }else{
                $sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan_hist where lokasitugas='".$idAfd."' and sistemgaji like '%Bulanan%' and periodegaji='".$period."' order by namakaryawan asc";
            }
            $qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
            $qKry->setFetchMode(PDO::FETCH_ASSOC);
            $optKry="";
            while($rKry=$qKry->fetch())
            {
                $optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']."</option>";
            }
            echo $optKry;
        break;
        default:
        break;
}
?>