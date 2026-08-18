<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
$param=$_POST;
if(isset($_GET['proses'])!=''){
    if($_GET['proses']=='excel'){
        $param=$_GET;
    }else{
        $param['proses']=$_GET['proses'];
    }
}

 $str="select * from ".$dbname.".sdm_5tipekaryawan
        where 1";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $tipekar[$bar->id]=$bar->id;
        $artitkr[$bar->id]=$bar->tipe;
    }
if($param['region']!=''){
        $str="select * from ".$dbname.".bgt_regional_assignment
            where regional = '".$param['region']."'";        
    }else{
        $str="select * from ".$dbname.".bgt_regional_assignment
            where 1";
        
    }
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        if($param['region']!=''){
            $regional[$bar->kodeunit]=$bar->kodeunit;            
        }else{
            $unitreg[$bar->kodeunit]=$bar->regional;
            $regional[$bar->regional]=$bar->regional;            
        }        
    }
    #buatlevelsatu
    if($param['ptId2']==''){
        $whr.="and a.kodeorganisasi in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='PT')";
        $whrd="and kodeorganisasi in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='PT')";
    }else{
        $whr.="and a.kodeorganisasi='".$param['ptId2']."'";
        $whrd="and kodeorganisasi='".$param['ptId2']."'";
    }
    if($param['unitId2']!=''){
        $whr.="and lokasitugas='".$param['unitId2']."'";
    }
    #buatleveldua
    if($param['region']!=''){
        $whr="";
        $whrd="";
        $whr="and lokasitugas in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$param['region']."')";
        $whrd="and lokasitugas in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$param['region']."')";
    }
        $sholding="select distinct count(karyawanid) as jmlhkary,tipekaryawan,lokasitugas from ".$dbname.".datakaryawan 
                   where left(tanggalmasuk,7) <= '".$param['prdIdDr2']."' and (left(tanggalkeluar,7)>='".$param['prdIdDr2']."' 
                   or left(tanggalkeluar,7)='0000-00') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') ".$whrd."
                   group by tipekaryawan,lokasitugas";
         //echo $sholding;
        $qholding=$owlPDO->query($sholding) or die(print " Gagal: ".PDOException::getMessage());
        $qholding->setFetchMode(PDO::FETCH_ASSOC);
        while($rholding=  $qholding->fetch()){
            if($param['region']!=''){
                $qwe=$rholding['lokasitugas'];
            }else{
                $qwe=$unitreg[$rholding['lokasitugas']];
            }
            $jumlahkar[$qwe][$rholding['tipekaryawan']]+=$rholding['jmlhkary'];
        }
        
    
$sdmGaji="select distinct count(b.karyawanid) as jmlhkary,tipekaryawan,a.kodeorganisasi,a.lokasitugas from ".$dbname.".datakaryawan a 
          left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
          (left(tanggalkeluar,7)>='".$param['prdIdDr2']."' or left(tanggalkeluar,7)='0000-00')
          and periodegaji='".$param['prdIdDr2']."'   ".$whr."
          and idkomponen=1
          group by tipekaryawan,lokasitugas";
 //exit("error:".$sdmGaji);
//echo $sdmGaji;
$qsdmGaji=$owlPDO->query($sdmGaji) or die(print " Gagal: ".PDOException::getMessage());
$qsdmGaji->setFetchMode(PDO::FETCH_ASSOC);
while($rsdmGaji=  $qsdmGaji->fetch()){
       if($param['region']!=''){
            $qwe=$rsdmGaji['lokasitugas'];
        }else{
            $qwe=$unitreg[$rsdmGaji['lokasitugas']];
        }
        $jumlahkar[$qwe][$rsdmGaji['tipekaryawan']]+=$rsdmGaji['jmlhkary'];
}
 
if($param['proses']!='excel'){
        $brd=0;
        $bgcolor="";
    }else{
        $tab.= $_SESSION['lang']['summary']." ".$_SESSION['lang']['karyawan']." ".$_SESSION['lang']['dari']." ".$_SESSION['lang']['gaji']."<br>Periode: ".$param['prdIdDr2']." ";
        $brd=1;
        $bgcolor="bgcolor=#DEDEDE";
    }
     if($param['region']==''){
        $param['region']=$_SESSION['lang']['regional'];
    }else{
        if($param['proses']!='excel')
        $tab.="<img onclick=level2excel(event,'sdm_slave_2summarykaryawan2.php','".$param['prdIdDr2']."','".$param['region']."') src=images/excel.jpg class=resicon title='MS.Excel'>";
    }
    $tab.="
    <table width=100% cellspacing=1 border=".$brd.">
    <thead>
    <tr  ".$bgcolor.">
        <td>".$_SESSION['lang']['tipekaryawan']."</td>";
        if(!empty($regional))foreach($regional as $reg)
            if($reg!='')
            $tab.="<td ".$bgcolor." align=center title='Click to details...' onclick=getlevel2('".$param['prdIdDr2']."','".$reg."')>".$reg."</td>";
        $tab.="
        <td align=center>".$_SESSION['lang']['total']."</td>
    </tr>        
    </thead>
    <tbody>";
    if(!empty($tipekar))foreach($tipekar as $tkr){
        $tab.="<tr class=rowcontent>
        <td>".$artitkr[$tkr]."</td>";
        $total[$tkr]=0;
        if(!empty($regional))foreach($regional as $reg){
            $tab.="<td align=right>".number_format($jumlahkar[$reg][$tkr])."</td>";
            $total[$tkr]+=$jumlahkar[$reg][$tkr];
            $totalgrand[$reg]+=$jumlahkar[$reg][$tkr];            
        }
        $tab.="
        <td align=right>".number_format($total[$tkr])."</td>
        </tr>";            
    }
    $tab.="<tr class=rowcontent>
    <td>".$_SESSION['lang']['total']."</td>";
    $totalnya=0;
    if(!empty($regional))foreach($regional as $reg){
        $tab.="<td align=right>".number_format($totalgrand[$reg])."</td>";
        $totalnya+=$totalgrand[$reg];            
    }
    $tab.="
    <td align=right>".number_format($totalnya)."</td>
    </tr>";            
    
    $tab.="</tbody></table>";

switch($param['proses'])
{
    case'preview':
         
        echo $tab;
    break;
    case'level1':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="summary_karyawan_".$param['prdIdDr2'];
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
    
}
?>