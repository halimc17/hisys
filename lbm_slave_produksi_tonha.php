<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');

//$arr="##klmpkBrg##kdUnit##periode##lokasi##statId##purId";
$sKlmpk="select kode,kelompok from ".$dbname.".log_5klbarang order by kode";
$qKlmpk = $owlPDO->query($sKlmpk) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk = $qKlmpk->fetch()) {
    $rKelompok[$rKlmpk['kode']]=$rKlmpk['kelompok'];
}
$optNmOrang=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optInduk=makeOption($dbname, 'organisasi','kodeorganisasi,induk');

$kdUnit = checkPostGet('kdUnit','');
$periode = checkPostGet('periode','');
$judul = checkPostGet('judul','');
$afdId = checkPostGet('afdId','');

$unitId=$_SESSION['lang']['all'];
$nmPrshn="Holding";
$purchaser=$_SESSION['lang']['all'];
if($periode=='')
{
    exit("Error: ".$_SESSION['lang']['periode']." required");
}
if($kdUnit!='')
{
    $unitId=$optNmOrg[$kdUnit];
}
else
{
    exit("Error:".$_SESSION['lang']['unit']." required");
}
$thn=explode("-",$periode);

//buat bi dan sbi
    if(strlen($thn[1])<2)
    {
        $field="kg0".$thn[1];
    }
    else
    {
        $field="kg".$thn[1];
    }
  
for($asr5=1;$asr5<=$thn[1];$asr5++)
{
    
        if(strlen($asr5)<2)
        {
            if($asr5==1)
            {
                $field5="kg0".$asr5;
            }
            else
            {
             $field5.="+kg0".$asr5;
            }
        }
        else
        {
            $field5.="+kg".$asr5;
        }
   
}

//array tahun tanam
$sThnTnm="select thntnm,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw where kodeunit='".$kdUnit."' and tahunbudget='".$thn[0]."'  order by thntnm asc,kodeblok asc";
if($afdId!='')
{
    $sThnTnm="select thntnm,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw where 	
              kodeblok like '".$afdId."%' and tahunbudget='".$thn[0]."'  order by thntnm asc,kodeblok asc";
}
$dtThnTnm = array();
$qThnTnm = $owlPDO->query($sThnTnm) or die(print " Gagal: " . PDOException::getMessage());
$qThnTnm->setFetchMode(PDO::FETCH_ASSOC);
while ($rThnTnm = $qThnTnm->fetch()) {	
	if(strlen($rThnTnm['thntnm'])=='4')
    {
		$dzKodeorg[$rThnTnm['kodeblok']]=$rThnTnm['kodeblok'];
		$dzKdBlok[$rThnTnm['thntnm']][$rThnTnm['kodeblok']]=$rThnTnm['kodeblok'];
		if(!in_array($rThnTnm['thntnm'],$dtThnTnm)){
			$dtThnTnm[]=$rThnTnm['thntnm'];
		}
    }
}

//potensi produksi

//ambil luas dan thn taman dari budget
$sLuas="select hathnini as luas,thntnm,kodeblok from ".$dbname.".bgt_blok 
        where substr(kodeblok,1,4)='".$kdUnit."' and tahunbudget='".$thn[0]."'";
if($afdId!='')
{
    $sLuas="select hathnini as luas,thntnm,kodeblok from ".$dbname.".bgt_blok 
        where substr(kodeblok,1,6)='".$afdId."' and tahunbudget='".$thn[0]."'";
}
$qLuas = $owlPDO->query($sLuas) or die(print " Gagal: " . PDOException::getMessage());
$qLuas->setFetchMode(PDO::FETCH_ASSOC);
while ($rLuas = $qLuas->fetch()) {	
	@$lsAnggran[$rLuas['thntnm']]+=$rLuas['luas'];
	@$lsAnggranBlok[$rLuas['thntnm']][$rLuas['kodeblok']]+=$rLuas['luas'];
}
//ambil luas dan thn taman dari setup_blok
$sLuasRealisasi="select (luasareaproduktif) as luas,tahuntanam,kodeorg from ".$dbname.".setup_blok 
                 where substr(kodeorg,1,4)='".$kdUnit."'";
if($afdId!='')
{
    $sLuasRealisasi="select (luasareaproduktif) as luas,tahuntanam,kodeorg from ".$dbname.".setup_blok 
                     where substr(kodeorg,1,6)='".$afdId."'";
}
$qLuasRealisasi = $owlPDO->query($sLuasRealisasi) or die(print " Gagal: " . PDOException::getMessage());
$qLuasRealisasi->setFetchMode(PDO::FETCH_ASSOC);
while ($rLuasRealisasi = $qLuasRealisasi->fetch()) {	
    @$lsRealisasi[$rLuasRealisasi['tahuntanam']]+=$rLuasRealisasi['luas'];
    @$lsRealisasiBlok[$rLuasRealisasi['tahuntanam']][$rLuasRealisasi['kodeorg']]+=$rLuasRealisasi['luas'];
}
//ambil data kilogram dari budget
$sKgTaon="select (kgsetahun) as kgstaun,thntnm,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw 
          where tahunbudget='".$thn[0]."' and kodeunit='".$kdUnit."'";
if($afdId!='')
{
   $sKgTaon="select (kgsetahun) as kgstaun,thntnm,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw 
          where tahunbudget='".$thn[0]."' and kodeblok like '".$afdId."%'"; 
}
$qKgTaon = $owlPDO->query($sKgTaon) or die(print " Gagal: " . PDOException::getMessage());
$qKgTaon->setFetchMode(PDO::FETCH_ASSOC);
while ($rKgTaon = $qKgTaon->fetch()) {	
    @$kgSthn[$rKgTaon['thntnm']]+=($rKgTaon['kgstaun']/1000)/$lsAnggran[$rKgTaon['thntnm']];
    @$kgSthnBlok[$rKgTaon['thntnm']][$rKgTaon['kodeblok']]+=($rKgTaon['kgstaun']/1000)/$lsAnggranBlok[$rKgTaon['thntnm']][$rKgTaon['kodeblok']];
}
//budget kg bi->bulan ini
$sKgTaonbi="select (".$field.") as kgstaun,thntnm,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw 
          where tahunbudget='".$thn[0]."' and kodeunit='".$kdUnit."'";
if($afdId!='')
{
    $sKgTaonbi="select (".$field.") as kgstaun,thntnm,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw 
          where tahunbudget='".$thn[0]."' and kodeblok like '".$afdId."%'";
}
//exit("Error:".$sKgTaonbi);
$qKgTaonbi = $owlPDO->query($sKgTaonbi) or die(print " Gagal: " . PDOException::getMessage());
$qKgTaonbi->setFetchMode(PDO::FETCH_ASSOC);
while ($rKgTaonbi = $qKgTaonbi->fetch()) {	
    @$kgSthnBi[$rKgTaonbi['thntnm']]+=($rKgTaonbi['kgstaun']/1000)/$lsAnggran[$rKgTaonbi['thntnm']];
    @$kgSthnBiBlok[$rKgTaonbi['thntnm']][$rKgTaonbi['kodeblok']]+=($rKgTaonbi['kgstaun']/1000)/$lsAnggranBlok[$rKgTaonbi['thntnm']][$rKgTaonbi['kodeblok']];
}
//budget kg sbi->sampai bulan ini
$sKgTaonsbi="select (".$field5.") as kgstaun,thntnm,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw 
          where tahunbudget='".$thn[0]."' and kodeunit='".$kdUnit."'";

if($afdId!='')
{
    $sKgTaonsbi="select (".$field5.") as kgstaun,thntnm,kodeblok from ".$dbname.".bgt_produksi_kbn_kg_vw 
          where tahunbudget='".$thn[0]."' and kodeblok like '".$afdId."%'";
}
$qKgTaonsbi = $owlPDO->query($sKgTaonsbi) or die(print " Gagal: " . PDOException::getMessage());
$qKgTaonsbi->setFetchMode(PDO::FETCH_ASSOC);
while ($rKgTaonsbi = $qKgTaonsbi->fetch()) {
    @$kgSthnsBi[$rKgTaonsbi['thntnm']]+=($rKgTaonsbi['kgstaun']/1000)/$lsAnggran[$rKgTaonsbi['thntnm']];
    @$kgSthnsBiBlok[$rKgTaonsbi['thntnm']][$rKgTaonsbi['kodeblok']]+=($rKgTaonsbi['kgstaun']/1000)/$lsAnggranBlok[$rKgTaonsbi['thntnm']][$rKgTaonsbi['kodeblok']];
}

//sensus ton
$sSensus="select (kgsensus) as kgsensus,tahuntanam,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
          where periode='".$periode."' and substr(blok,1,4)='".$kdUnit."'";
if($afdId!='')
{
    $sSensus="select (kgsensus) as kgsensus,tahuntanam,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
          where periode='".$periode."' and substr(blok,1,6)='".$afdId."'";
}
//exit("Error:".$sSensus);
$qSensus = $owlPDO->query($sSensus) or die(print " Gagal: " . PDOException::getMessage());
$qSensus->setFetchMode(PDO::FETCH_ASSOC);
while ($rSensus = $qSensus->fetch()) {
      @$biSensus[$rSensus['tahuntanam']]+=($rSensus['kgsensus']/1000)/$lsRealisasi[$rSensus['tahuntanam']];
      @$biSensusBlok[$rSensus['tahuntanam']][$rSensus['blok']]+=($rSensus['kgsensus']/1000)/$lsRealisasiBlok[$rSensus['tahuntanam']][$rSensus['blok']];
}

$sSensus="select (kgsensus) as kgsensus,tahuntanam,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
          where periode<='".$periode."' and substr(blok,1,4)='".$kdUnit."'";
if($afdId!='')
{
   $sSensus="select (kgsensus) as kgsensus,tahuntanam,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
          where periode<='".$periode."' and substr(blok,1,6)='".$afdId."'"; 
}
//echo $sSensus;
$qSensus = $owlPDO->query($sSensus) or die(print " Gagal: " . PDOException::getMessage());
$qSensus->setFetchMode(PDO::FETCH_ASSOC);
while ($rSensus = $qSensus->fetch()) {
    @$sbiSensus[$rSensus['tahuntanam']]+=($rSensus['kgsensus']/1000)/$lsRealisasi[$rSensus['tahuntanam']];
    @$sbiSensusBlok[$rSensus['tahuntanam']][$rSensus['blok']]+=($rSensus['kgsensus']/1000)/$lsRealisasiBlok[$rSensus['tahuntanam']][$rSensus['blok']];
}
if($thn[1]<7)
{
    $sSensus="select (kgsensus) as kgsensus,tahuntanam,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
      where periode<'".$thn[0]."-07' and substr(blok,1,4)='".$kdUnit."'";
    if($afdId!='')
    {
        $sSensus="select (kgsensus) as kgsensus,tahuntanam,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
                  where periode<'".$thn[0]."-07' and substr(blok,1,6)='".$afdId."'";
    }

$qSensus = $owlPDO->query($sSensus) or die(print " Gagal: " . PDOException::getMessage());
$qSensus->setFetchMode(PDO::FETCH_ASSOC);
while ($rSensus = $qSensus->fetch()) {
      @$senSmstr[$rSensus['tahuntanam']]+=($rSensus['kgsensus']/1000)/$lsRealisasi[$rSensus['tahuntanam']];
      @$senSmstrBlok[$rSensus['tahuntanam']][$rSensus['blok']]+=($rSensus['kgsensus']/1000)/$lsRealisasiBlok[$rSensus['tahuntanam']][$rSensus['blok']];
    } 
}
else if($thn[1]<13&&$thn[1]>6)
{
    $sSensus="select (kgsensus) as kgsensus,tahuntanam,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
      where periode>'".$thn[0]."-06' and substr(blok,1,4)='".$kdUnit."'";
    if($afdId!='')
    {
         $sSensus="select (kgsensus) as kgsensus,tahuntanam,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
                   where periode>'".$thn[0]."-06' and substr(blok,1,6)='".$afdId."'";
    }

$qSensus = $owlPDO->query($sSensus) or die(print " Gagal: " . PDOException::getMessage());
$qSensus->setFetchMode(PDO::FETCH_ASSOC);
while ($rSensus = $qSensus->fetch()) {
      @$senSmstr[$rSensus['tahuntanam']]+=($rSensus['kgsensus']/1000)/$lsRealisasi[$rSensus['tahuntanam']];
      @$senSmstrBlok[$rSensus['tahuntanam']][$rSensus['blok']]+=($rSensus['kgsensus']/1000)/$lsRealisasiBlok[$rSensus['tahuntanam']][$rSensus['blok']];
    } 
}

//REALISASI
$sRealaisasi="select (nettotimbangan) as realisasi,tahuntanam,blok from  ".$dbname.".kebun_spb_vs_rencana_blok_vw 
              where periode='".$periode."' and substr(blok,1,4)='".$kdUnit."'";
 if($afdId!='')
 {
     $sRealaisasi="select (nettotimbangan) as realisasi,tahuntanam,blok from  ".$dbname.".kebun_spb_vs_rencana_blok_vw 
                   where periode='".$periode."' and substr(blok,1,6)='".$afdId."'";
 }
$qRealisasi = $owlPDO->query($sRealaisasi) or die(print " Gagal: " . PDOException::getMessage());
$qRealisasi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRealisasi = $qRealisasi->fetch()) {	
    @$biRealisasi[$rRealisasi['tahuntanam']]+=($rRealisasi['realisasi']/1000)/$lsRealisasi[$rRealisasi['tahuntanam']];
    @$biRealisasiBlok[$rRealisasi['tahuntanam']][$rRealisasi['blok']]+=($rRealisasi['realisasi']/1000)/$lsRealisasiBlok[$rRealisasi['tahuntanam']][$rRealisasi['blok']];
}
$sRealaisasi="select (nettotimbangan) as realisasi,tahuntanam,blok from  ".$dbname.".kebun_spb_vs_rencana_blok_vw 
              where  periode<='".$periode."' and substr(blok,1,4)='".$kdUnit."'";
if($afdId!='')
{
   $sRealaisasi="select (nettotimbangan) as realisasi,tahuntanam,blok from  ".$dbname.".kebun_spb_vs_rencana_blok_vw 
              where  periode<='".$periode."' and substr(blok,1,6)='".$afdId."'"; 
}
$qRealisasi = $owlPDO->query($sRealaisasi) or die(print " Gagal: " . PDOException::getMessage());
$qRealisasi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRealisasi = $qRealisasi->fetch()) {	
    @$sbiRealisasi[$rRealisasi['tahuntanam']]+=($rRealisasi['realisasi']/1000)/$lsRealisasi[$rRealisasi['tahuntanam']];
    @$sbiRealisasiBlok[$rRealisasi['tahuntanam']][$rRealisasi['blok']]+=($rRealisasi['realisasi']/1000)/$lsRealisasiBlok[$rRealisasi['tahuntanam']][$rRealisasi['blok']];
}

//produksi tahun lalu
$thnLalu=$thn[0]-1;
$period=$thnLalu."-".$thn[1];
$sRealaisasi="select (nettotimbangan) as realisasi,tahuntanam,blok from  ".$dbname.".kebun_spb_vs_rencana_blok_vw 
              where  periode<='".$period."' and substr(blok,1,4)='".$kdUnit."'";
if($afdId!='')
{
   $sRealaisasi="select (nettotimbangan) as realisasi,tahuntanam,blok from  ".$dbname.".kebun_spb_vs_rencana_blok_vw 
              where  periode<='".$period."' and substr(blok,1,6)='".$afdId."'"; 
}
$qRealisasi = $owlPDO->query($sRealaisasi) or die(print " Gagal: " . PDOException::getMessage());
$qRealisasi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRealisasi = $qRealisasi->fetch()) {	
    @$prodThnLalusbi[$rRealisasi['tahuntanam']]+=($rRealisasi['realisasi']/1000)/$lsRealisasi[$rRealisasi['tahuntanam']];
    @$prodThnLalusbiBlok[$rRealisasi['tahuntanam']][$rRealisasi['blok']]+=($rRealisasi['realisasi']/1000)/$lsRealisasiBlok[$rRealisasi['tahuntanam']][$rRealisasi['blok']];
}
$sRealaisasi="select (nettotimbangan) as realisasi,tahuntanam,blok from  ".$dbname.".kebun_spb_vs_rencana_blok_vw 
              where  substr(periode,1,4)='".$thnLalu."' and substr(blok,1,4)='".$kdUnit."'";
if($afdId!='')
{
    $sRealaisasi="select (nettotimbangan) as realisasi,tahuntanam,blok from  ".$dbname.".kebun_spb_vs_rencana_blok_vw 
              where  substr(periode,1,4)='".$thnLalu."' and substr(blok,1,6)='".$afdId."'";
}
$qRealisasi = $owlPDO->query($sRealaisasi) or die(print " Gagal: " . PDOException::getMessage());
$qRealisasi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRealisasi = $qRealisasi->fetch()) {	
    @$prodThnLalu[$rRealisasi['tahuntanam']]+=($rRealisasi['realisasi']/1000)/$lsRealisasi[$rRealisasi['tahuntanam']];
    @$prodThnLaluBlok[$rRealisasi['tahuntanam']][$rRealisasi['blok']]+=($rRealisasi['realisasi']/1000)/$lsRealisasiBlok[$rRealisasi['tahuntanam']][$rRealisasi['blok']];
}
//potensi produksi
$sPotensi="select tahuntanam,klasifikasitanah,jenisbibit,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
           where  periode='".$periode."' and substr(blok,1,4)='".$kdUnit."' ";
if($afdId!='')
{
    $sPotensi="select tahuntanam,klasifikasitanah,jenisbibit,blok from ".$dbname.".kebun_spb_vs_rencana_blok_vw 
           where  periode='".$periode."' and substr(blok,1,6)='".$afdId."' ";
}
 //exit("error:".$sPotensi);
$qPotensi = $owlPDO->query($sPotensi) or die(print " Gagal: " . PDOException::getMessage());
$qPotensi->setFetchMode(PDO::FETCH_ASSOC);
while ($rSensus = $qPotensi->fetch()) {	
      $umur=$thn[0]-$rSensus['tahuntanam'];
      $sPot="select kgproduksi from ".$dbname.".kebun_5stproduksi where jenisbibit='".$rSensus['jenisbibit']."' and klasifikasitanah='".$rSensus['klasifikasitanah']."' and umur='".$umur."'";
      // exit("error:".$sPot);
      		$qPot=$owlPDO->query($sPot) or die(print " Gagal: ".PDOException::getMessage());
		$qPot->setFetchMode(PDO::FETCH_ASSOC);
		$rPot=$qPot->fetch();
      @$potProdBlok[$rSensus['tahuntanam']][$rSensus['blok']]=(($lsRealisasiBlok[$rSensus['tahuntanam']][$rSensus['blok']]*$rPot['kgproduksi'])/1000)/$lsRealisasiBlok[$rSensus['tahuntanam']][$rSensus['blok']];
}
$varCek=count($dtThnTnm);
if($varCek<1)
{    
    //array tahun tanam, kalo di budget kosong, ambil dari setup_blok
    $sThnTnm="select distinct tahuntanam as thntnm from ".$dbname.".setup_blok where 	kodeorg like '".$kdUnit."%'  order by tahuntanam asc";
    if($afdId!='')
    {
        $sThnTnm="select distinct tahuntanam as thntnm from ".$dbname.".setup_blok where 	
                  kodeorg like '".$afdId."%' order by tahuntanam asc";
    }

	$qThnTnm = $owlPDO->query($sThnTnm) or die(print " Gagal: " . PDOException::getMessage());
    $qThnTnm->setFetchMode(PDO::FETCH_ASSOC);
    while ($rThnTnm = $qThnTnm->fetch()) {	
        if(strlen($rThnTnm['thntnm'])=='4')
        {
            $dtThnTnm[]=$rThnTnm['thntnm'];
        }
    }    
//    exit("Error:Data Kosong");
}
$brdr=0;
$bgcoloraja='';
$stylehidden = "style='display:none'";
$colspanhidden = "colspan=2";
@$cols=count($dataAfd)*3;
if($proses=='excel')
{
	$stylehidden = "";
	$colspanhidden = "colspan=4";
    $bgcoloraja="bgcolor=#DEDEDE align=center";
    $brdr=1;
    $tab.="
    <table>
    <tr><td colspan=8 align=left><b>".$_GET['judul']."</b></td><td colspan=3 align=right><b>".$_SESSION['lang']['bulan']." : ".substr(tanggalnormal($periode),1,7)."</b></td></tr>
    <tr><td colspan=8 align=left>".$_SESSION['lang']['unit']." : ".$optNmOrg[$kdUnit]." </td></tr>";
    if($afdId!='')
    {
        $tab.="<tr><td colspan=8 align=left>".$_SESSION['lang']['unit']." : ".$optNmOrg[$kdUnit]." </td></tr>";
    }
    $tab.="<tr><td colspan=8 align=left>&nbsp;</td></tr>
    </table>";
}

	$tab.="<table cellspacing=1 border=".$brdr." class=sortable>
	<thead class=rowheader>
	<tr>
        <td ".$bgcoloraja." rowspan=2>".$_SESSION['lang']['tahuntanam']."</td>
        <td ".$bgcoloraja." rowspan=2 id='titAfd' ".$stylehidden.">".$_SESSION['lang']['afdeling']."</td>
        <td ".$bgcoloraja." rowspan=2 id='titBlok' ".$stylehidden.">".$_SESSION['lang']['blok']."</td>
        <td ".$bgcoloraja." rowspan=2>".$_SESSION['lang']['umur']." (".$_SESSION['lang']['tahun'].")</td>
        <td ".$bgcoloraja." colspan=2>".$_SESSION['lang']['luas']." (Ha)</td>";
        $tab.="<td ".$bgcoloraja." colspan=3>".$_SESSION['lang']['anggaran']." (TON)</td>";
        $tab.="<td ".$bgcoloraja." colspan=2>".$_SESSION['lang']['sensus']." (TON)</td>";
        $tab.="<td ".$bgcoloraja." colspan=2>".$_SESSION['lang']['realisasi']." (TON)</td>";
        $tab.="<td ".$bgcoloraja." colspan=2>% VARIAN REAL VS CENSUS</td>";
        $tab.="<td ".$bgcoloraja." colspan=2>% VARIAN REAL VS BUDGET</td>";
        $tab.="<td ".$bgcoloraja." rowspan=2>".$_SESSION['lang']['sbi']." (".$_SESSION['lang']['tahunlalu'].")</td><td ".$bgcoloraja." rowspan=2>CENSUS  SM-I/II</td>";
        $tab.="<td ".$bgcoloraja." rowspan=2>".$_SESSION['lang']['tahunlalu']."</td><td ".$bgcoloraja." rowspan=2>Potency ".$_SESSION['lang']['produksi']."</td></tr>";
        $tab.="<tr><td ".$bgcoloraja." >".$_SESSION['lang']['anggaran']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['setahun']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['setahun']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['bi']."</td>
               <td ".$bgcoloraja." >".$_SESSION['lang']['sbi']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['bi']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['sbi']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['bi']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['sbi']."</td>
               <td ".$bgcoloraja." >".$_SESSION['lang']['bi']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['sbi']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['bi']."</td><td ".$bgcoloraja." >".$_SESSION['lang']['sbi']."</td></tr>";
        $tab.="</thead>
	<tbody>";
		$countDis = 0;
		$countChild = count($dzKodeorg);
		$countParent = count($dtThnTnm);
        foreach($dtThnTnm as $lstThnTnm)
        {
			$countDis++;
            $tab.="<tr class=rowcontent style='cursor:pointer' title='click to show details' onclick=\"details('".$countDis."','".$countChild."','".$countParent."')\"><td>".$lstThnTnm."</td>";
            $umur=$periode-$lstThnTnm;
            $tab.="<td align=right colspan=2 id=bodyAfd".$countDis." ".$stylehidden."></td>";
            $tab.="<td align=right>".$umur."</td>";
            $tab.="<td align=right>".number_format(@$lsAnggran[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$lsRealisasi[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$kgSthn[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$kgSthnBi[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$kgSthnsBi[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$biSensus[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$sbiSensus[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$biRealisasi[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$sbiRealisasi[$lstThnTnm],2)."</td>";
            @$snVsRealibi[$lstThnTnm]=$biRealisasi[$lstThnTnm]/$biSensus[$lstThnTnm]*100;
            @$snVsRealisbi[$lstThnTnm]=$sbiRealisasi[$lstThnTnm]/$sbiSensus[$lstThnTnm]*100;
            $tab.="<td align=right>".number_format($snVsRealibi[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format($snVsRealisbi[$lstThnTnm],2)."</td>";
            @$angVsRealibi[$lstThnTnm]=$biRealisasi[$lstThnTnm]/$kgSthnBi[$lstThnTnm]*100;
            @$angVsRealisbi[$lstThnTnm]=$sbiRealisasi[$lstThnTnm]/$kgSthnsBi[$lstThnTnm]*100;
            $tab.="<td align=right>".number_format($angVsRealibi[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format($angVsRealisbi[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$prodThnLalusbi[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$senSmstr[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$prodThnLalu[$lstThnTnm],2)."</td>";
            $tab.="<td align=right>".number_format(@$potProd[$lstThnTnm],2)."</td>";
            $tab.="</tr>";
            @$totLAngr+=$lsAnggran[$lstThnTnm];
            @$totLReali+=$lsRealisasi[$lstThnTnm];
            @$KgStaon+=($kgSthn[$lstThnTnm]*$lsAnggran[$lstThnTnm]);
            @$totKgStaon=$KgStaon/$totLAngr;
            @$totKgSthnBi+=$kgSthnBi[$lstThnTnm];
            @$totkgSthnsBi+=$kgSthnsBi[$lstThnTnm];
            @$totbiSensus+=$biSensus[$lstThnTnm];
            @$totsbiSensus+=$sbiSensus[$lstThnTnm];
            @$totbiRealisasi+=$biRealisasi[$lstThnTnm];
            @$totsbiRealisasi+=$sbiRealisasi[$lstThnTnm];
            @$totsnVsRealibi+=$snVsRealibi[$lstThnTnm];
            @$totsnVsRealisbi+=$snVsRealisbi[$lstThnTnm];
            @$totangVsRealibi+=$angVsRealibi[$lstThnTnm];
            @$totprodThnLalusbi+=$prodThnLalusbi[$lstThnTnm];
            @$totsenSmstr+=$senSmstr[$lstThnTnm];
            @$totprodThnLalu+=$prodThnLalu[$lstThnTnm];
            @$totpotProd+=$potProd[$lstThnTnm];
			$arrBlok = array();
			$countDisChild = 0;
			foreach($dzKodeorg as $val){
				if($val == @$dzKdBlok[$lstThnTnm][$val]){
					$countDisChild++;
					$tab.="<tr id=child_".$countDis."_".$countDisChild." ".$stylehidden." class=rowcontent>";
					if(in_array(substr($val,0,6),$arrBlok)){
						$tab.="<td colspan=2></td>";
					}else{
						$tab.="<td></td>";
						$tab.="<td>".substr($val,0,6)."</td>";
					}					
					$tab.="<td>".$val."</td>";
					$tab.="<td></td>";
					$tab.="<td style='text-align:right'>".number_format($lsAnggranBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format($lsRealisasiBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format($kgSthnBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format($kgSthnBiBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format($kgSthnsBiBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$biSensusBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$sbiSensusBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$biRealisasiBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$sbiRealisasiBlok[$lstThnTnm][$val],2)."</td>";
					@$snVsRealibiBlok[$lstThnTnm][$val]=$biRealisasiBlok[$lstThnTnm][$val]/$biSensusBlok[$lstThnTnm][$val]*100;
					@$snVsRealisbiBlok[$lstThnTnm][$val]=$sbiRealisasiBlok[$lstThnTnm][$val]/$sbiSensusBlok[$lstThnTnm][$val]*100;
					$tab.="<td style='text-align:right'>".number_format(@$snVsRealibiBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$snVsRealisbiBlok[$lstThnTnm][$val],2)."</td>";
					@$angVsRealibiBlok[$lstThnTnm][$val]=$biRealisasiBlok[$lstThnTnm][$val]/$kgSthnBiBlok[$lstThnTnm][$val]*100;
					@$angVsRealisbiBlok[$lstThnTnm][$val]=$sbiRealisasiBlok[$lstThnTnm][$val]/$kgSthnsBiBlok[$lstThnTnm][$val]*100;
					$tab.="<td style='text-align:right'>".number_format(@$angVsRealibiBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$angVsRealisbiBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$prodThnLalusbiBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$senSmstrBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$prodThnLaluBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="<td style='text-align:right'>".number_format(@$potProdBlok[$lstThnTnm][$val],2)."</td>";
					$tab.="</tr>";
					
					array_push($arrBlok, substr($val,0,6));
				}
			}
        }
            $tab.="<tr class=rowcontent><td  ".$colspanhidden." id='totRows'>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".number_format($totLAngr,2)."</td>";
            $tab.="<td align=right>".number_format($totLReali,2)."</td>";
            $tab.="<td align=right>".number_format($totKgStaon,2)."</td>";
            $tab.="<td align=right>".number_format($totKgSthnBi,2)."</td>";
            $tab.="<td align=right>".number_format($totkgSthnsBi,2)."</td>";
            $tab.="<td align=right>".number_format($totbiSensus,2)."</td>";
            $tab.="<td align=right>".number_format($totsbiSensus,2)."</td>";
            $tab.="<td align=right>".number_format($totbiRealisasi,2)."</td>";
            $tab.="<td align=right>".number_format($totsbiRealisasi,2)."</td>";
            $tab.="<td align=right>".number_format($totsnVsRealibi,2)."</td>";
            $tab.="<td align=right>".number_format($totsnVsRealisbi,2)."</td>";
            
            $tab.="<td align=right>".number_format($totangVsRealibi,2)."</td>";
            $tab.="<td align=right>".number_format($totangVsRealibi,2)."</td>";
            $tab.="<td align=right>".number_format($totprodThnLalusbi,2)."</td>";
            $tab.="<td align=right>".number_format($totsenSmstr,2)."</td>";
            $tab.="<td align=right>".number_format($totprodThnLalu,2)."</td>";
            $tab.="<td align=right>".number_format($totpotProd,2)."</td>";
            $tab.="</tr>";
        $tab.="</tbody></table>";
switch($proses)
{
	case'preview':
	echo $tab;
	break;
        case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("Hms");
        $nop_=$judul."_".$dte;
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $tab);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";	
	break;
        case'pdf':
      
           class PDF extends FPDF {
           function Header() {
            global $periode, $judul;
            global $dataAfd;
            global $kdUnit;
            global $optNmOrg;  
            global $dbname;
            global $afdId;

                $this->SetFont('Arial','B',8);
                $this->Cell($width,$height,strtoupper($judul),0,1,'L');
                $this->Cell(790,$height,$_SESSION['lang']['bulan'].' : '.substr(tanggalnormal($periode),1,7),0,1,'R');
                $tinggiAkr=$this->GetY();
                $ksamping=$this->GetX();
                $this->SetY($tinggiAkr+20);
                $this->SetX($ksamping);
                $this->Cell($width,$height,$_SESSION['lang']['unit'].' : '.$optNmOrg[$kdUnit],0,1,'L');
                if($afdId!='')
                {
                    //$this->Cell(790,$height,' ',0,1,'R');
                    $tinggiAkr=$this->GetY();
                    $ksamping=$this->GetX();
                    $this->SetY($tinggiAkr+20);
                    $this->SetX($ksamping);
                    $this->Cell($width,$height,$_SESSION['lang']['afdeling'].' : '.$optNmOrg[$afdId],0,1,'L');
                }
                $this->Cell(790,$height,' ',0,1,'R');
                $tinggiAkr=$this->GetY();
                $ksamping=$this->GetX();
                $this->SetY($tinggiAkr+20);
                $this->SetX($ksamping);
                $height = 15;
                $this->SetFillColor(220,220,220);
                $this->SetFont('Arial','B',7);
                $this->Cell(25,$height," ",TLR,0,'C',1);
                $this->Cell(50,$height,'',TLR,0,'C',1);
                $this->Cell(60,$height," ",TLR,0,'C',1);
                $this->Cell(150,$height," ",TLR,0,'C',1);
                $this->Cell(100,$height," ",TLR,0,'C',1);
                $this->Cell(100,$height," ",TLR,0,'C',1);
                $this->Cell(60,$height,"% VARIAN",TLR,0,'C',1);
                $this->Cell(70,$height,"% VARIAN",TLR,0,'C',1);
                $this->Cell(30,$height,$_SESSION['lang']['sbi'],TLR,0,'C',1);
                $this->Cell(55,$height," ",TLR,0,'C',1);
                $this->Cell(55,$height,'',TLR,0,'C',1);
                $this->Cell(40,$height," ",TLR,1,'C',1);
                
                $this->Cell(25,$height,$_SESSION['lang']['tahun'],LR,0,'C',1);
                $this->Cell(50,$height,$_SESSION['lang']['umur'],LR,0,'C',1);
//                $tinggiAkr=$this->GetY();
//                $ksamping=$this->GetX();
//                $this->SetY($tinggiAkr);
//                $this->SetX($ksamping-572);
                $this->Cell(60,$height,$_SESSION['lang']['luas']." (Ha)",LR,0,'C',1);
                $this->Cell(150,$height,$_SESSION['lang']['anggaran']." (TON)",LR,0,'C',1);
                $this->Cell(100,$height,"CENSUS (TON)",LR,0,'C',1);
                $this->Cell(100,$height,$_SESSION['lang']['realisasi']." (TON)",LR,0,'C',1);
                $this->Cell(60,$height,"REAL VS",LR,0,'C',1);
                $this->Cell(70,$height,"REAL VS",LR,0,'C',1);
                $this->Cell(30,$height,"(".$_SESSION['lang']['tahunlalu'].")",LR,0,'C',1);
                $this->Cell(55,$height,"CENSUS",LR,0,'C',1);
                $this->Cell(55,$height,'',LR,0,'C',1);
                $this->Cell(40,$height,"POTENCY",LR,1,'C',1);
                
                $this->Cell(25,$height,$_SESSION['lang']['tanam'],LR,0,'C',1);
                $this->Cell(50,$height,($_SESSION['lang']['tahunlalu']),LR,0,'C',1);
                $this->Cell(60,$height," ",LR,0,'C',1);
                $this->Cell(150,$height," ",LR,0,'C',1);
                $this->Cell(100,$height," ",LR,0,'C',1);
                $this->Cell(100,$height," ",LR,0,'C',1);
                $this->Cell(60,$height,"CNS",LR,0,'C',1);
                $this->Cell(70,$height,  strtoupper($_SESSION['lang']['anggaran']),LR,0,'C',1);
                $this->Cell(30,$height,'',LR,0,'C',1);
                $this->Cell(55,$height,"SM-I/II ",LR,0,'C',1);
                $this->Cell(55,$height,$_SESSION['lang']['tahunlalu'],LR,0,'C',1);
                $this->Cell(40,$height,$_SESSION['lang']['produksi'],LR,1,'C',1);
                
                $this->Cell(25,$height," ",BLR,0,'C',1);
                $this->Cell(50,$height," ",BLR,0,'C',1);
                 $this->SetFont('Arial','B',6);
                $this->Cell(30,$height,"BUDGET",TBLR,0,'C',1);
                $this->Cell(30,$height,"REAL",TBLR,0,'C',1);
                
                $this->Cell(50,$height,$_SESSION['lang']['setahun'],TBLR,0,'C',1);
                $this->Cell(50,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
                $this->Cell(50,$height,$_SESSION['lang']['sbi'],TBLR,0,'C',1);
                $this->Cell(50,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
                $this->Cell(50,$height,$_SESSION['lang']['sbi'],TBLR,0,'C',1);
                $this->Cell(50,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
                $this->Cell(50,$height,$_SESSION['lang']['sbi'],TBLR,0,'C',1);
                $this->Cell(30,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
                $this->Cell(30,$height,$_SESSION['lang']['sbi'],TBLR,0,'C',1);
                $this->Cell(35,$height,$_SESSION['lang']['bi'],TBLR,0,'C',1);
                $this->Cell(35,$height,$_SESSION['lang']['sbi'],TBLR,0,'C',1);
                $this->SetFont('Arial','B',7);
                $this->Cell(30,$height,"",BLR,0,'C',1);
                $this->Cell(55,$height," ",BLR,0,'C',1);
                $this->Cell(55,$height," ",BLR,0,'C',1);
                $this->Cell(40,$height," ",BLR,1,'C',1);
                 
          }
              function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo()." / {totalPages}",0,0,'L');
            }
            }
            //================================

            $pdf=new PDF('L','pt','A4');
			$pdf->AliasNbPages('{totalPages}');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 20;
            $pdf->AddPage();
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','B',6);
            foreach($dtThnTnm as $lstThnTnm)
            {
                $umur=$periode-$lstThnTnm;
                $pdf->Cell(25,$height,$lstThnTnm,1,0,'C',1);
                $pdf->Cell(50,$height,$umur,1,0,'R',1);
                $pdf->Cell(30,$height,number_format($lsAnggran[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(30,$height,number_format($lsRealisasi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(50,$height,number_format($kgSthn[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(50,$height,number_format($kgSthnBi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(50,$height,number_format($kgSthnsBi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(50,$height,number_format($biSensus[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(50,$height,number_format($sbiSensus[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(50,$height,number_format($biRealisasi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(50,$height,number_format($sbiRealisasi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(30,$height,number_format($snVsRealibi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(30,$height,number_format($snVsRealisbi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(35,$height,number_format($angVsRealibi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(35,$height,number_format($angVsRealisbi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(30,$height,number_format($prodThnLalusbi[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(55,$height,number_format($senSmstr[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(55,$height,number_format($prodThnLalu[$lstThnTnm],2),1,0,'R',1);
                $pdf->Cell(40,$height,number_format($potProd[$lstThnTnm],2),1,1,'R',1);
            
             }
            
$tab.="<tr class=rowcontent><td colspan=2>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".number_format($totLAngr,2)."</td>";
            $tab.="<td align=right>".number_format($totLReali,2)."</td>";
            $tab.="<td align=right>".number_format($totKgStaon,2)."</td>";
            $tab.="<td align=right>".number_format($totKgSthnBi,2)."</td>";
            $tab.="<td align=right>".number_format($totkgSthnsBi,2)."</td>";
            $tab.="<td align=right>".number_format($totbiSensus,2)."</td>";
            $tab.="<td align=right>".number_format($totsbiSensus,2)."</td>";
            $tab.="<td align=right>".number_format($totbiRealisasi,2)."</td>";
            $tab.="<td align=right>".number_format($totsbiRealisasi,2)."</td>";
            $tab.="<td align=right>".number_format($totsnVsRealibi,2)."</td>";
            $tab.="<td align=right>".number_format($totsnVsRealisbi,2)."</td>";
            
            $tab.="<td align=right>".number_format($totangVsRealibi,2)."</td>";
            $tab.="<td align=right>".number_format($totangVsRealibi,2)."</td>";
            $tab.="<td align=right>".number_format($totprodThnLalusbi,2)."</td>";
            $tab.="<td align=right>".number_format($totsenSmstr,2)."</td>";
            $tab.="<td align=right>".number_format($totprodThnLalu,2)."</td>";
            $tab.="<td align=right>".number_format($totpotProd,2)."</td>";
            $tab.="</tr>";
            $pdf->Cell(75,$height,$_SESSION['lang']['total'],1,0,'L',1);
            
            $pdf->Cell(30,$height,number_format($totLAngr,2),1,0,'R',1);
            $pdf->Cell(30,$height,number_format($totLReali,2),1,0,'R',1);
            $pdf->Cell(50,$height,number_format($totKgStaon,2),1,0,'R',1);
            $pdf->Cell(50,$height,number_format($totKgSthnBi,2),1,0,'R',1);
            $pdf->Cell(50,$height,number_format($totkgSthnsBi,2),1,0,'R',1);
            $pdf->Cell(50,$height,number_format($totbiSensus,2),1,0,'R',1);
            $pdf->Cell(50,$height,number_format($totsbiSensus,2),1,0,'R',1);
            $pdf->Cell(50,$height,number_format($totbiRealisasi,2),1,0,'R',1);
            $pdf->Cell(50,$height,number_format($totsbiRealisasi,2),1,0,'R',1);
            $pdf->Cell(30,$height,number_format($totsnVsRealibi,2),1,0,'R',1);
            $pdf->Cell(30,$height,number_format($totsnVsRealisbi,2),1,0,'R',1);
            $pdf->Cell(35,$height,number_format($totangVsRealibi,2),1,0,'R',1);
            $pdf->Cell(35,$height,number_format($totangVsRealibi,2),1,0,'R',1);
            $pdf->Cell(30,$height,number_format($prodThnLalusbi[$lstThnTnm],2),1,0,'R',1);
            $pdf->Cell(55,$height,number_format($totsenSmstr,2),1,0,'R',1);
            $pdf->Cell(55,$height,number_format($totprodThnLalu,2),1,0,'R',1);
            $pdf->Cell(40,$height,number_format($totpotProd,2),1,1,'R',1);
            $pdf->Output();	
                
                
            break;
	
            
	
	default:
	break;
}
      
?>