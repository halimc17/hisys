<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=$_POST['pt'];
$gudang=$_POST['gudang'];
$periode=$_POST['periode'];
$periodeKmrn=periodelalu($periode);

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $namapt=strtoupper($bar->namaorganisasi);
}
$whr=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
if($gudang!=''){
        $whr=" and kodeorg='".$gudang."'";
}
$str="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$periode."' ".$whr."";
//echo $str."____";
$currstart='';
$currend='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
$str="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$periodeKmrn."' ".$whr."";
$paststart='';
$pastend='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $paststart=$bar->tanggalmulai;
    $pastend=$bar->tanggalsampai;
}
$tgl="tanggal between '".$currstart."' and '".$currend."'";
$tgl2="tanggal between '".$paststart."' and '".$pastend."'";
$dtArus=array();
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='CASH FLOW DIRECT' order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
        $dtArus[]=$bar;
}
$rpdt=array();
$str1="select sum(debet) as debet,sum(kredit) as kredit,noaruskas from ".$dbname.".keu_jurnaldt_vw  where ".$tgl." ".$whr."  and nojurnal not like '%/M/%' group by noaruskas";
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rstr1=$res->fetch()){
        @$rpdbt[$rstr1['noaruskas']]+=$rstr1['debet'];
        @$rpkrt[$rstr1['noaruskas']]+=$rstr1['kredit'];
}
$str2="select sum(jumlah) as jumlah,noaruskas from ".$dbname.".keu_jurnaldt  where ".$tgl2." ".$whr."  and nojurnal not like '%/M/%' group by noaruskas";
$res=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rstr1=$res->fetch()){
        @$stAwal[$rstr1['noaruskas']]+=$rstr1['jumlah'];
}
 foreach($dtArus as $lstArus){
        if($lstArus['tipe']=='Header'){
                echo"<tr>
                          <td colspan=4>".$lstArus['keterangandisplay']."</td>
                        ";
                echo"</tr>"; 		
        }
        if($lstArus['tipe']=='Detail'){
                echo"<tr class=rowcontent>
                          <td>".$lstArus['nourut']."</td>
                          <td>".$lstArus['keterangandisplay']."</td>
                          <td align=right>".@number_format($rpdbt[$lstArus['nourut']],2,'.',',')."</td>
                          <td align=right>".@number_format($rpkrt[$lstArus['nourut']],2,'.',',')."</td>";
                          //<td align=right>".number_format($stAwal[$lstArus['nourut']],2,'.',',')."</td>
						  @$endbalance=$rpdbt[$lstArus['nourut']]-$rpkrt[$lstArus['nourut']];
                echo"<td align=right>".@number_format($endbalance,2,'.',',')."</td>
                        </tr>";
        }

 }
?>