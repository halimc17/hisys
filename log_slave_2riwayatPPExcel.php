<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$totalSmaData=$dsetujui=$dtolak=$dmenungguKptsn=$blmDiajukan=$pros=0;
//======================================
$proses =	isset($_GET['proses'])? $_GET['proses']: '';
$nopp=		isset($_GET['nopp'])? $_GET['nopp']: '';
$tglSdt=	isset($_GET['tglSdt'])? tanggalsystem($_GET['tglSdt']): '';
$statusPP=	isset($_GET['statPP'])? $_GET['statPP']: '';
$periode=	isset($_GET['periode'])? $_GET['periode']: '';
$lokBeli=	isset($_GET['lokBeli'])? $_GET['lokBeli']: '';
//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
$namapt='COMPANY NAME';
$resChat=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$resChat->setFetchMode(PDO::FETCH_OBJ);
while($bar=$resChat->fetch())
{
        $namapt=strtoupper($bar->namaorganisasi);
}
                $stream="<table  cellspacing='1' border='0'>
                                <tr><td colspan=10  align=center>".strtoupper($_SESSION['lang']['riwayatPP'])."</td></tr>
                                <tr><td colspan=3  align='left'>".$_SESSION['lang']['user'].":".$_SESSION['standard']['username']."</td></tr>
                                <tr><td colspan=3  align='left'>".$_SESSION['lang']['tanggal'].":".date('d-m-Y H:i:s')."</td></tr></table>
                                <table  cellspacing='1' border='1'>
                                <tr>
                                <td bgcolor=#DEDEDE align=center>No.</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nopp']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodevhc']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodebarang']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namabarang']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jmlhDiminta']."</td> 
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['disetujui']."</td>    
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</td>    
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['status']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nopo']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tgl_po']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['status']." </td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namasupplier']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['rapbNo']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
                                </tr></table><table  cellspacing='1' border='1'>";


                if(($nopp=='')&&($tglSdt=='')&&($statusPP=='')&&($periode=='')&&($lokBeli=='')&&($_GET['supplier_id']=='')&&($_GET['txNmbrg']==''))
                {
                    $tglSkrng=date("Y-m");	
                    $sql="select a.*,b.*,a.keterangan as keterangan,a.kodevhc as kodevhc FROM ".$dbname.".log_prapodt a left join ".$dbname.".log_prapoht b on a.nopp=b.nopp where substr(b.tanggal,1,7)='".$tglSkrng."'  order by a.nopp desc ";
                    
                }
                else
                {
                    if($tglSdt!='')
                    {
                        $where=" where b.tanggal='".$tglSdt."'";
                    }
                    else
                    {
                        $where=" where a.nopp!=''";
                    }
                     if($statusPP!='')
                    {
                        if($statusPP=='3')
                        {
                           if($tglSdt=='')
                            {
                               if($periode=='')

                            {
                                exit("Error: Periode Tidak Boleh Kosong");
                            }
                             else {
                                 $where="where  a.create_po!=''  and substr(b.tanggal,1,7) = '".$periode."' ";
                            }
                            }

                        }
                        elseif($statusPP=='4')
                        {
                            if($tglSdt=='')
                            {
                                if($periode=='')
                                {
                                    exit("Error: Periode Tidak Boleh Kosong");
                                }
                                else {
                                    $where="where  a.create_po=''  and substr(b.tanggal,1,7) = '".$periode."'";
                                }
                            }

                        }
                        elseif(($statusPP=='1')||($statusPP!='2'))
                         {
                            if($tglSdt=='')
                            {
                            if($periode!='')
                            {$where=" where b.close='".$statusPP."' and substr(b.tanggal,1,7) = '".$periode."'";  }
                            else
                            {$where.=" and b.close='".$statusPP."'";}      
                            }
                        }
                    }
                    elseif($periode!='')
                    {
                        if($tglSdt=='')
                        {
                        $where=" where substr(b.tanggal,1,7)='".$periode."'";
                        }
                    }
                    if($lokBeli!='')
                    {
                        $where.=" and lokalpusat= '".$lokBeli."'";
                    }
                    if($nopp!='')
                    {
                        $where.=" and b.nopp like '%".$nopp."%'";
                    }
                    if($_GET['supplier_id']!='')
                    {
                        //exit("Error:masuk");
                        $where.=" and a.nopp in (select distinct nopp from ".$dbname.".log_po_vw where kodesupplier='".$_GET['supplier_id']."')";
                    }
                    if($_GET['txNmbrg']!='')
                    {
                        $where.=" and kodebarang in (select distinct kodebarang from ".$dbname.".log_5masterbarang where namabarang like '%".$_GET['txNmbrg']."%')";
                    }

                    $sql="select a.*,b.*,a.keterangan as keterangan,a.kodevhc as kodevhc FROM ".$dbname.".log_prapodt a left join ".$dbname.".log_prapoht b on a.nopp=b.nopp  ".$where." order by a.nopp desc ";
                }

                        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                        $query->setFetchMode(PDO::FETCH_ASSOC);

                        $row=owlBaris($query);
                        if($row>0)
                        {
							           $no=0;
                                while($res=$query->fetch())
                                {
                                        $no+=1;
                                        //get data nopp
                                        $dtolak=0;
                                         if($res['close']=='2')
                                        {
                                            if(!is_null($res['tglp5']))
                                            {
                                               $tgl=tanggalnormal($res['tglp5']) ;
                                            }
                                            else if(!is_null($res['tglp4']))
                                            {
                                                $tgl=tanggalnormal($res['tglp4']) ;
                                            }
                                            else if(!is_null($res['tglp3']))
                                            {
                                                $tgl=tanggalnormal($res['tglp3']) ;
                                            }
                                            else if(!is_null($res['tglp2']))
                                            {
                                                $tgl=tanggalnormal($res['tglp2']) ;
                                            }
                                            else if(!is_null($res['tglp1']))
                                            {
                                                $tgl=tanggalnormal($res['tglp1']) ;
                                            }
                                            if($res['status']=='3')
                                            {
                                                $statPp=$_SESSION['lang']['ditolak'].",".$tgl;
                                                $npo="";
                                                $dtolak+=1;
                                            }
                                            else  if($res['status']=='0')
                                            {

                                                $statPp=$_SESSION['lang']['disetujui'].",".$tgl;
                                                $npo="Purchasing Process";
                                                $pros+=1;
                                            }
                                        }
                                        else if($res['close']=='1')
                                        {

                                            if(!is_null($res['hasilpersetujuan5']))
                                            {
                                               $tgl=tanggalnormal($res['tglp5']) ;
                                               if($res['hasilpersetujuan5']=='1')
                                               {
                                                    $statPp=$_SESSION['lang']['disetujui'].",".$tgl;
                                               }
                                               else if($res['hasilpersetujuan5']=='0')
                                               {
                                                 $statPp=$_SESSION['lang']['wait_approval'];  
                                                   $npo="";
                                                   $dmenungguKptsn+=1;
                                               }
                                               else if($res['hasilpersetujuan5']=='3')
                                               {
                                                    $statPp=$_SESSION['lang']['ditolak'].",".$tgl;
                                                    $dtolak=1;
                                                     $npo="";
                                               }
                                            }
                                            else if(!is_null($res['hasilpersetujuan4']))
                                            {
                                                $tgl=tanggalnormal($res['tglp4']) ;
                                               if($res['hasilpersetujuan4']=='1')
                                               {
                                                    $statPp=$_SESSION['lang']['disetujui'].",".$tgl;
                                               }
                                               else if($res['hasilpersetujuan4']=='3')
                                               {
                                                   $statPp=$_SESSION['lang']['ditolak'].",".$tgl;
                                                   $dtolak=1;
                                                    $npo="";
                                               }
                                               else if($res['hasilpersetujuan4']=='0')
                                               {
                                                   $statPp=$_SESSION['lang']['wait_approval']; 
                                                   $npo="";
                                                   $dmenungguKptsn+=1;
                                               }
                                            }
                                            else if(!is_null($rTgl['hasilpersetujuan3']))
                                            {
                                                $tgl=tanggalnormal($res['tglp3']);
                                                 if($res['hasilpersetujuan3']=='1')
                                                   {
                                                        $statPp=$_SESSION['lang']['disetujui'].",".$tgl;
                                                   }
                                                   else if($res['hasilpersetujuan3']=='3')
                                                   {
                                                       $statPp=$_SESSION['lang']['ditolak'].",".$tgl;
                                                       $dtolak=1;
                                                   }
                                                   else if($res['hasilpersetujuan3']=='0')
                                                   {
                                                       $statPp=$_SESSION['lang']['wait_approval']; 
                                                       $npo="";
                                                       $dmenungguKptsn+=1;
                                                   }
                                            }
                                            else if(!is_null($res['hasilpersetujuan2']))
                                            {
                                                $tgl=tanggalnormal($res['tglp2']) ;
                                                if($res['hasilpersetujuan2']=='1')
                                                   {
                                                        $statPp=$_SESSION['lang']['disetujui'].",".$tgl;
                                                   }
                                                   else if($res['hasilpersetujuan2']=='3')
                                                   {
                                                       $statPp=$_SESSION['lang']['ditolak'].",".$tgl;
                                                       $dtolak=1;
                                                        $npo="";
                                                   }
                                                   else if($res['hasilpersetujuan2']=='0')
                                                   {
                                                       $statPp=$_SESSION['lang']['wait_approval']; 
                                                        $npo="";
                                                       $dmenungguKptsn+=1;
                                                   }
                                            }
                                            else if(!is_null($res['hasilpersetujuan1']))
                                            {
                                                $tgl=tanggalnormal($res['tglp1']) ;
                                                if($res['hasilpersetujuan1']=='1')
                                                   {
                                                        $statPp=$_SESSION['lang']['disetujui'].",".$tgl;
                                                   }
                                                   else if($res['hasilpersetujuan1']=='3')
                                                   {
                                                       $statPp=$_SESSION['lang']['ditolak'].",".$tgl;
                                                       $dtolak=1;
                                                       $npo="";
                                                   }
                                                   else if($res['hasilpersetujuan1']=='0')
                                                   {
                                                       $statPp=$_SESSION['lang']['wait_approval'];  
                                                       $dmenungguKptsn+=1;
                                                        $npo="";
                                                   }
                                            }
                                        }
                                        else if(($res['close']==0)||($res['close']==''))
                                        {
                                                //$statBrg="Belum Diajukan";
                                                $statPp=$_SESSION['lang']['belumdiajukan'];
                                                 $npo="";
                                                $blmDiajukan+=1;
                                        }


                                        $statPo='';//default
                                        //get data barang
                                        $sBrg="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
                                        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                                        $qBrg->setFetchMode(PDO::FETCH_ASSOC);                                        
                                        $rBrg=$qBrg->fetch();

                                        //get data po and all related					
                                        $sDet="select nopo from ".$dbname.".log_podt  where nopp='".$res['nopp']."' and kodebarang='".$res['kodebarang']."'"; 
                                        $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
                                        $qDet->setFetchMode(PDO::FETCH_ASSOC);                                          
                                        $rDet=$qDet->fetch();

                                        $sPo2="select * from ".$dbname.".log_poht  where nopo='".$rDet['nopo']."'"; 
                                        $qPo2=$owlPDO->query($sPo2) or die(print " Gagal: ".PDOException::getMessage());
                                        $qPo2->setFetchMode(PDO::FETCH_ASSOC); 

                                        $rPo2=$qPo2->fetch();
                                        $tglPO='';
                                        if($rDet['nopo']!='')
                                        {
                                            if($rPo2['tanggal']!=0000-00-00)
                                            {$tglPO=tanggalnormal($rPo2['tanggal']);}

                                            $sSup="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$rPo2['kodesupplier']."'";
                                            $qSup=$owlPDO->query($sSup) or die(print " Gagal: ".PDOException::getMessage());
                                            $qSup->setFetchMode(PDO::FETCH_ASSOC);
                                            $rSup=$qSup->fetch();

                                            $sRapb="select notransaksi,tanggal from ".$dbname.".log_transaksi_vw 
                                                    where nopo='".$rPo2['nopo']."' and kodebarang='".$res['kodebarang']."'";
                                            $qRapb=$owlPDO->query($sRapb) or die(print " Gagal: ".PDOException::getMessage());
                                            $qRapb->setFetchMode(PDO::FETCH_ASSOC);

                                            $rRapb= $qRapb->fetch();
                                            if($rPo2['statuspo']=='3')
                                            {
                                                $tglR="";
                                                $statPo=$_SESSION['lang']['disetujui'].",".$tgl;
                                                if($rRapb['notransaksi']!='')
                                                {
                                                    $tglR=tanggalnormal($rRapb['tanggal']);
                                                    $statPo="Brg Sdh Di gudang ,".$tglR;
                                                }   
                                            }
                                            else if($rPo2['statuspo']=='2')
                                            {
                                                    $accept=0;
                                                    for($i=1;$i<4;$i++)
                                                    {
                                                            if($rPo2['hasilpersetujuan'.$i]=='2')
                                                            {
                                                                    $accept=2;
                                                                    $tgl=tanggalnormal($rPo2['tglp'.$i]);
                                                                    break;
                                                            }
                                                            elseif($rPo2['hasilpersetujuan'.$i]=='1')
                                                            {
                                                                    $accept=1;

                                                            }
                                                    }
                                                    if($accept=='2') {
                                                            //echo"<td colspan=3>".$_SESSION['lang']['ditolak']."</td>";
                                                            $statPo=$_SESSION['lang']['ditolak'].",".$tgl;
                                                    } elseif($accept=='1') {
                                                            //echo"<td colspan=3>".$_SESSION['lang']['disetujui']."</td>";
                                                            $statPo=$_SESSION['lang']['disetujui'].",".$tgl;
                                                    }

                                            }
                                            else if($rPo2['statuspo']=='1')
                                            {
                                                    for($i=1;$i<4;$i++)
                                                    {
                                                            if($rPo2['tglp'.$i]=='')
                                                            {
                                                                    $j=$i-1;
                                                                    if($j!=0)
                                                                    {
                                                                            $tgl=tanggalnormal($rPo2['tglp'.$j]);
                                                                            if($rPo2['hasilpersetujuan'.$j]==2)
                                                                            {
                                                                                    $statPo="Persetujuan".$j.", ".$_SESSION['lang']['ditolak'].$tgl;
                                                                            }
                                                                            elseif($rPo2['hasilpersetujuan'.$j]==1)
                                                                            {
                                                                                    $statPo="Persetujuan".$j.", ".$_SESSION['lang']['disetujui'].$tgl;
                                                                            }
                                                                    }
                                                                    break;
                                                             }
                                                      }

                                            }
                                            else if($rPo2['statuspo']=='0')
                                            {

                                                    $statPo="Approval Process";
                                            }

                                        }

                                        if($rDet['nopo']!='')
                                        {
                                                $res['lokalpusat']==0?$npo=$rPo2['nopo']:$npo=$rPo2['nopo'];
                                                $dsetujui+=1;
                                        }
                                        else
                                        {
                                                $npo="";
                                                $tglPO="";
                                                $statPo="";
                                                $rSup['namasupplier']="";
                                                $rRapb['notransaksi']="";
                                                $rRapb['tanggal']="0000-00-00";
                                        }
                               if($res['hasilpersetujuan1']==3 or $res['hasilpersetujuan2']==3 or $res['hasilpersetujuan3']==3 or $res['hasilpersetujuan4']==3 or $res['hasilpersetujuan5']==3 or $res['status']==3)
                                {
                                   $npo=''; 
                                }



                                        $stream.="<tr class=rowcontent>
                                                <td>".$no."</td>
                                                <td>".$res['nopp']."</td>
                                                <td>".tanggalnormal($res['tanggal'])."</td>
                                                <td>".$res['kodevhc']."</td>
                                                <td>".$res['kodebarang']."</td>
                                                <td>".$rBrg['namabarang']."</td>
                                                <td>".$rBrg['satuan']."</td>
                                                <td align=right>".$res['jumlah']."</td>
                                                <td align=right>".$res['realisasi']."</td>
                                                <td align=left>".$res['keterangan']."</td>
                                                ";
                                                $stream.="<td>".$statPp."</td>";

                                                $stream.="
                                                <td>".$npo."</td>
                                                <td>".$tglPO."</td>";
                                                $stream.="<td>".$statPo."</td>";		
                                                $stream.="<td>".$rSup['namasupplier']."</td>
                                                <td>".$rRapb['notransaksi']."</td>";
                                                if(($rRapb['tanggal']!=0000-00-00))
                                                {
                                                        $stream.="<td>".tanggalnormal($rRapb['tanggal'])."</td></tr>";
                                                }
                                                else
                                                {
                                                        $stream.="<td></td></tr>";
                                                }
                                }
                                $stream.="</table>";
                        }
                        else
                        {
                                $stream.="<tr class=rowcontent><td colspan=16 align=center>Not Found</td></tr></table>";		
                        }
                         $stream.="<tr class=rowcontent><td colspan=16 align=left><table cellpadding=1 cellspacing=1 border=1 class=sortable>";
                                $stream.="<thead><tr class=rowheader>";
                                $stream.="<td  bgcolor=#DEDEDE align=center>Purchased</td>";
                                $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['ditolak']."</td>";
                                $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['wait_approval']."</td>";
                                $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['belumdiajukan']."</td>";
                                $stream.="<td bgcolor=#DEDEDE align=center>Purchasing Process</td>";
                                $stream.="<td bgcolor=#DEDEDE align=center>Total</td>";
                                $stream.="</thead><tbody>";
                                $totalSmaData=$dtolak+$dmenungguKptsn+$blmDiajukan+($pros-$dsetujui)+$dsetujui;
                                $stream.="<tr class=rowcontent>";
                                $stream.="<td align=right>".$dsetujui."</td>";
                                $stream.="<td align=right>".$dtolak."</td>";
                                $stream.="<td align=right>".$dmenungguKptsn."</td>";
                                $stream.="<td align=right>".$blmDiajukan."</td>";
                                $stream.="<td align=right>".($pros-$dsetujui)."</td>";
                                $stream.="<td align=right>".$totalSmaData."</td>";
                                $stream.="</tr>";
                                $stream.="</tbody></table></tr>";


        //echo "warning:".$strx;
//=================================================

        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

$nop_="ReportRiwayatPermintaanBarang".date('YmdHis');
if(strlen($stream)>0)
{
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
}
?>