<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$per=checkPostGet('periode','');
$pt=checkPostGet('pt','');
$pph0=checkPostGet('pph0','');
$tipekar=checkPostGet('tipekar','');
$tipe = checkPostGet('tipe', '');

switch($method)
{
 case 'loadLaporan':
   


$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

$nmkar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$npwp=  makeOption($dbname, 'datakaryawan', 'karyawanid,npwp');






if($tipe=='excel'){
    $border=0;
} else {
    $border=1;
}

$stream.="<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";

$stream.="
<thead>
<tr class=rowheader>
<td>blnpajak</td>
<td>thnpajak</td>
<td>pembetulan</td>
<td>npwp</td>
<td>nama</td>
<td>kodepjk</td>
<td>bruto</td>
<td>pph</td>
<td>kodeneg</td>
</tr>
</thead>
<tbody>
";

if ($pph0==1) {
    $whr=" and idkomponen in ('42') ";
}else{
    $whr=" and idkomponen not in ('42') and karyawanid not in (select karyawanid from ".$dbname.".sdm_gaji_vw where periodegaji = '".$per."' and kodeorg like '%".$pt."%' and idkomponen in ('42') and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where tipekaryawan = '".$tipekar."' )) ";
}

$orgx='';
$str="select kodeorganisasi from ".$dbname.".organisasi where induk = '".$pt."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
  if($orgx=='')
  {
    $orgx="'".$bar['kodeorganisasi']."'";
  }
  else
  {
    $orgx.=",'".$bar['kodeorganisasi']."'";
  }
}


$str="select * from ".$dbname.".sdm_gaji_vw where periodegaji = '".$per."' and kodeorg in (".$orgx.") ".$whr." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where tipekaryawan = '".$tipekar."' ) and jumlah != 0 ";
//exit($str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $karid[$bar['karyawanid']]=$bar['karyawanid'];
    $periode[$bar['karyawanid']]=$bar['periodegaji'];
    $pph21[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
}


$strr="select * from ".$dbname.".sdm_gaji_vw where periodegaji = '".$per."' and kodeorg in (".$orgx.") and idkomponen in (select id from ".$dbname.".sdm_ho_component where plus=1 and pph21=1 ) ";
$ress=$owlPDO->query($strr) or die(print " Gagal: ".PDOException::getMessage());
$ress->setFetchMode(PDO::FETCH_ASSOC);
while($barr=$ress->fetch()){
    $bruto[$barr['karyawanid']]+=$barr['jumlah'];
}

foreach ($karid as $value) {
    $stream.="
    <tr class=rowcontent>
    <td>".substr($periode[$value], 5,2)."</td>
    <td>".substr($periode[$value], 0,4)."</td>
    <td>0</td>
    <td>".$npwp[$value]."</td>
    <td>".$nmkar[$value]."</td>
    <td>21-100-01</td>
    <td align=right>".$bruto[$value]."</td>
    <td align=right>".$pph21[$value]['42']."</td>
    <td></td>
    </tr>
    ";
}
         
        
$stream.="</tbody></table>";
    
    if($tipe=='excel'){
             
            header("Cache-Control: must-revalidate");
            header("Pragma: must-revalidate");
            header("Content-type: application/vnd.ms-excel");
            header("Content-disposition: attachment; filename=laporanpajak-".$pt."-".$per.".csv");

            echo"Blnpajak;Thnpajak;Pembetulan;Npwp;Nama;Kodepjk;Bruto;Pph;Kodeneg\n";

            if ($pph0==1) {
                $whr=" and idkomponen in ('42') ";
            }else{
                $whr=" and idkomponen not in ('42') and karyawanid not in (select karyawanid from ".$dbname.".sdm_gaji_vw where periodegaji = '".$per."' and kodeorg like '%".$pt."%' and idkomponen in ('42') and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where tipekaryawan = '".$tipekar."' )) ";
            }

            $str="select * from ".$dbname.".sdm_gaji_vw where periodegaji = '".$per."' and kodeorg like '%".$pt."%' ".$whr." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where tipekaryawan = '".$tipekar."' ) and jumlah != 0 ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $karid[$bar['karyawanid']]=$bar['karyawanid'];
                $periode[$bar['karyawanid']]=$bar['periodegaji'];
                $pph21[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
            }


            $strr="select * from ".$dbname.".sdm_gaji_vw where periodegaji = '".$per."' and kodeorg like '%".$pt."%' and idkomponen in (select id from ".$dbname.".sdm_ho_component where plus=1 ) ";
            $ress=$owlPDO->query($strr) or die(print " Gagal: ".PDOException::getMessage());
            $ress->setFetchMode(PDO::FETCH_ASSOC);
            while($barr=$ress->fetch()){
                $bruto[$barr['karyawanid']]=$barr['jumlah'];
            }

            foreach ($karid as $value) {
                echo"".substr($periode[$value], 5,2).";".substr($periode[$value], 0,4).";0;".$npwp[$value].";".$nmkar[$value].";21-100-01;".$bruto[$value].";".$pph21[$value]['42'].";\n";
            }     
        }
        else
        {
              echo $stream;
        }
          
        
    break;

        default:
        break;
}


?>