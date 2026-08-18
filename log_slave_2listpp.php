<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_GET['proses'];
//$arr="##txtNopp##tgl_cari##periode##lokBeli##txtNmBrg##supplier_id##stat_id";
$txtNopp	= checkPostGet('txtNopp','');
$supplier_id= checkPostGet('supplier_id','');
$periode	= checkPostGet('periode','');
$lokBeli	= checkPostGet('lokBeli','');
$stat_id	= checkPostGet('stat_id','');
$txtNmBrg	= checkPostGet('txtNmBrg','');
$tgl_cari	= !empty($_POST['tgl_cari'])? tanggalsystem($_POST['tgl_cari']): (isset($_GET['tgl_cari'])? tanggalsystem($_GET['tgl_cari']): '');

$whr='';
if($txtNopp!='')                     
{
    $whr.=" and nopp like '".$txtNopp."%'";
}
if($supplier_id!='')
{
    $whr.=" and kodesupplier='".$supplier_id."'";
}
if($tgl_cari!='')
{
    $whr.=" and tanggal='".$tgl_cari."'";
}
else if($periode!='')
{
    $whr.=" and tanggal like '".$periode."%'";
}
if($lokBeli!='')
{
    $whr.=" and lokalpusat='".$lokBeli."'";
}
if($txtNmBrg!='')
{
    $whr.=" and namabarang like '".$txtNmBrg."%'";
}
if($stat_id=='0')
{
    $whr.=" and statuspo!='3'";
}
elseif($stat_id=='1'){
     $whr.=" and statuspo='3'";
}
$sData="select distinct nopo,nopp,jumlahpesan,namabarang,kodebarang,satuan
        from ".$dbname.".log_po_vw where nopp!='' ".$whr."";
//echo $sData;
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
$dtNopp=array();
while($rData=$qData->fetch()){
    $sNodok="select notransaksi from ".$dbname.".log_transaksi_vw
             where nopo='".$rData['nopo']."' and kodebarang='".$rData['kodebarang']."'";
    $qNodok=$owlPDO->query($sNodok) or die(print " Gagal: ".PDOException::getMessage());
    $qNodok->setFetchMode(PDO::FETCH_ASSOC);
    $rNodok=$qNodok->fetch();
 
    if(!isset($ared) or $ared!=$rData['nopp'])
    {
        $ared=$rData['nopp'];
        $er=1;
    }
	setIt($rNodok2['kodebarang'],'');
	setIt($rNodok2['realisasi'],0);
    $dtRealisai[$rData['nopp']][$rNodok2['kodebarang']]=$rNodok2['realisasi'];
    $dtNopp[$rData['nopp']]=$rData['nopp'];
    $dtNopo[$rData['nopp']][$er]=$rData['nopo'];
    $dtNmBrg[$rData['nopp']][$er]=$rData['namabarang'];
    $dtSatuan[$rData['nopp']][$er]=$rData['satuan'];
    $dtKdBrg[$rData['nopp']][$er]=$rData['kodebarang'];
    $dtJmlh[$rData['nopp']][$er]=$rData['jumlahpesan'];
    $dtNodok[$rData['nopo']][$er]=$rNodok['notransaksi'];
    $jmlhRow[$rData['nopp']]=$er;
    $er+=1;
}
$bdr=0;
if($proses=='excel')
{
    $bdr=1;
    $bg="align=center bgcolor=#DEDEDE";
} else {
	$bg="";
}
$tab="<table cellpadding=1 cellspacing=1 border=".$bdr." class=sortable>";
$tab.="<thead>";
$tab.="<tr>";
$tab.="<td ".$bg.">".$_SESSION['lang']['nopp']."</td>";
$tab.="<td ".$bg.">".$_SESSION['lang']['kodebarang']."</td>";
$tab.="<td ".$bg.">".$_SESSION['lang']['namabarang']."</td>";
//$tab.="<td>".$_SESSION['lang']['jumlah']." Realisasi</td>";
$tab.="<td ".$bg.">".$_SESSION['lang']['jmlhPesan']."</td>";
$tab.="<td ".$bg.">".$_SESSION['lang']['satuan']."</td>";
$tab.="<td ".$bg.">".$_SESSION['lang']['nopo']."</td>";
$tab.="<td ".$bg.">".$_SESSION['lang']['nodok']."</td></tr></thead><tbody>";
foreach($dtNopp as $lstData=>$lstPP)
{
    if($jmlhRow[$lstPP]==1)
    {
        $adert=1;
        $tab.="<tr class=rowcontent><td>".$lstPP."</td>";
        $tab.="<td>".$dtKdBrg[$lstPP][$adert]."</td>";
        $tab.="<td>".$dtNmBrg[$lstPP][$adert]."</td>";
        //$tab.="<td>".$dtRealisai[$lstPP][$dtKdBrg[$lstPP][$adert]]."</td>";
        $tab.="<td>".$dtJmlh[$lstPP][$adert]."</td>";
        $tab.="<td>".$dtSatuan[$lstPP][$adert]."</td>";
        $tab.="<td>".$dtNopo[$lstPP][$adert]."</td>";
        $tab.="<td>".$dtNodok[$dtNopo[$lstPP][$adert]][$adert]."</td></tr>";    
    }
    else
    {
        for($adert=1;$adert<=$jmlhRow[$lstPP];$adert++)
        {
            $tab.="<tr class=rowcontent>";
            if($adert==1)
            {
                $drs=$lstPP;
                $tab.="<td>".$lstPP."</td>";
                
            }
             else if($adert==2)
            {
                $tab.="<td rowspan=".($jmlhRow[$lstPP]-1).">&nbsp;</td>";
            }
           // $tab.="<td>".$dtRealisai[$lstPP][$dtKdBrg[$lstPP][$adert]]."</td>";
            $tab.="<td>".$dtKdBrg[$lstPP][$adert]."</td>";
            $tab.="<td>".$dtNmBrg[$lstPP][$adert]."</td>";
            $tab.="<td>".$dtJmlh[$lstPP][$adert]."</td>";
            $tab.="<td>".$dtSatuan[$lstPP][$adert]."</td>";
            $tab.="<td>".$dtNopo[$lstPP][$adert]."</td>";
            $tab.="<td>".$dtNodok[$dtNopo[$lstPP][$adert]][$adert]."</td></tr>";    
            
        }
    }
}
$tab.="</tbody></table>";

	switch($proses)
	{
		case'preview':
                      echo $tab;
		break;
		case'excel':
			
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="LaporanListPp";
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
		
		default:
		break;
	}


?>