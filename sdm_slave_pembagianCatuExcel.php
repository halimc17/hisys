<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg=$_GET['kodeorg'];
$optJbtn=makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optTipe=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
            //ambil kamus karyawan
          $str="select a.nik,a.karyawanid,a.namakaryawan,a.subbagian,b.tipe,c.keterangan,a.kodecatu,a.statuspajak,a.tipekaryawan,a.kodejabatan,d.namajabatan
                  from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id                  
                  left join ".$dbname.".sdm_5catuporsi c on a.kodecatu=c.kode
                  left join ".$dbname.".sdm_5jabatan d on a.kodejabatan=d.kodejabatan    
                  where a.lokasitugas='".$kodeorg."' and tipekaryawan!=0";
          $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
          $res->setFetchMode(PDO::FETCH_OBJ);
          $kamusKar=array();
          while($bar=$res->fetch())
          {
              $kamusKar[$bar->karyawanid]['id']=$bar->karyawanid;
			  $kamusKar[$bar->karyawanid]['nik']=$bar->nik;
              $kamusKar[$bar->karyawanid]['nama']=$bar->namakaryawan;
              $kamusKar[$bar->karyawanid]['kodecatu']=$bar->kodecatu;
              $kamusKar[$bar->karyawanid]['tipekaryawan']=$bar->tipekaryawan;
              $kamusKar[$bar->karyawanid]['namatipe']=$bar->tipe;              
              $kamusKar[$bar->karyawanid]['kelompok']=$bar->keterangan;
              // $kamusKar[$bar->karyawanid]['kode']=$bar->statuspajak;
			  $kamusKar[$bar->karyawanid]['kode']=$bar->kodecatu;
              $kamusKar[$bar->karyawanid]['jabatan']=$bar->namajabatan;              
          }
switch ($_GET['aksi']){
    
    case 'excel':
       $stream="Daftar Catu beras Periode ".$_GET['periode']."<br>
                    Unit: ".$kodeorg."
                    <table class=sortable border=1 cellspacing=1>
                    <thead>
                    <tr class=rowheader>
                    <td bgcolor=#DEDEDE align=center>No.</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodeorg']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['subbagian']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['periode']."</td>
					 <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nik']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakaryawan']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tipe']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jabatan']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['status']."</td>
                    <td bgcolor=#DEDEDE align=center>Ltr/Hk</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']." HK</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']." Kg</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['hargasatuan']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."(Rp)</td>
                    </tr>
                    </thead>
                    <tbody>";
            $sData="select distinct * from ".$dbname.".sdm_catu where 
                    kodeorg='".$kodeorg."' and periodegaji='".$_GET['periode']."' order by subbagian, tipekaryawan asc";
					//echo $sData;exit();
            $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
            $qData->setFetchMode(PDO::FETCH_ASSOC);
			      $ttl=0;
            while($rData=$qData->fetch())
            {

                $no+=1;
                $stream.= "<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$kodeorg."</td> 	
                        <td>".$rData['subbagian']."</td>
                        <td>".$rData['periodegaji']."</td>
						<td>".$kamusKar[$rData['karyawanid']]['nik']."</td>
                        <td>".$kamusKar[$rData['karyawanid']]['nama']."</td>
                        <td>".$kamusKar[$rData['karyawanid']]['namatipe']."</td>
                        <td>".$kamusKar[$rData['karyawanid']]['jabatan']."</td>
                        <td>".$kamusKar[$rData['karyawanid']]['kode']."</td>                          
                        <td>".number_format($rData['catuperhk'],2,'.',',')."</td>
                        <td align=right>".number_format($rData['jumlahhk'],0,'.',',')."</td>
                        <td align=right>".number_format($rData['totalcatu'],2,'.',',')."</td>
                        <td align=right>".number_format($rData['hargacatu'],0,'.',',')."</td>     
                        <td align=right>".number_format($rData['jumlahrupiah'],0,'.',',')."</td>     
                        ";
            $ttl+=$rData['jumlahrupiah'];  
            }  
                $stream.= "<tr class=rowheader>
                        <td colspan=13>TOTAL</td>     
                        <td align=right>".number_format($ttl,0,'.',',')."</td>     
                        ";    
            $stream.="</tbody></table>";
            $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("Hms");
            $nop_="listDataCatuBeras__".$_GET['kodeorg']."__".$dte;
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