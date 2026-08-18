<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method = checkPostGet('method', '');
$kdorg = checkPostGet('kdorg', '');
$divisi = checkPostGet('divisi', '');
$per2 = checkPostGet('per2', '');


$tahun=substr($per2,0,4);
$per1=$tahun.'-01';
$tgl1=$tahun.'-01-01';

##ambil tanggal akhir
$str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$per2."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
    $tgl2=$bar['tanggalsampai'];

#bentuk untuk bgt..
$expblnbgt=  explode('-', $per2);
$blnbgt=$expblnbgt[1];


    

if($kdorg=='')
{
    echo"Warning: Unit tidak boleh kosong"; 
    exit;
}
$stream="<div class='table-scroll'>";
if ($method == 'excel1') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";

} else {
    $stream.= "<table class=sortable cellspacing=1 cellpadding=5>";
}

$stream.="
    <thead>
        <tr class=rowheader>
    <th align='center'  width='20' rowspan='4'>No</th>
    <th align='center'  width='30' rowspan='4'>".$_SESSION['lang']['divisi']."</th>
    <th align='center'  width='29' rowspan='4'>".$_SESSION['lang']['blok']."</th>
    <th align='center'  width='18' rowspan='4'>".$_SESSION['lang']['thntnm']."</th>
    <th align='center'  width='29' rowspan='4'>".$_SESSION['lang']['luas']."</th>
    <th align='center'  width='50' rowspan='4'>".$_SESSION['lang']['jenisbibit']."</th>
    <th align='center'  width='37' rowspan='4'>".$_SESSION['lang']['statusblok']."</th>
    <th align='center'  colspan='5' rowspan='2'>".$_SESSION['lang']['produksi']."</th>
	
	<th align='center'  colspan='15'>".$_SESSION['lang']['jhk']."</th>
	
    <th align='center'  colspan='15'>".$_SESSION['lang']['biaya']."</th>
    <th align='center'  colspan='6'>Cost per ".$_SESSION['lang']['satuan']."</th>
	<th align='center'  colspan='15'>".$_SESSION['lang']['jhk']."/HA</th>
  </tr>
  
  <tr>
	<th align='center'  colspan='6'>".$_SESSION['lang']['bi']."</th>
    <th align='center'  colspan='6'>".$_SESSION['lang']['sbi']."</th>
    <th align='center'  colspan='3' rowspan='2'>".$_SESSION['lang']['budget']." ".$_SESSION['lang']['setahun']."</th>
  
    <th align='center'  colspan='6'>".$_SESSION['lang']['bi']."</th>
    <th align='center'  colspan='6'>".$_SESSION['lang']['sbi']."</th>
    <th align='center'  colspan='3' rowspan='2'>Budget ".$_SESSION['lang']['setahun']."</th>
    <th align='center'  colspan='4'>".$_SESSION['lang']['sbi']."</th>
    <th align='center'  colspan='2' rowspan='2'>".$_SESSION['lang']['setahun']."</th>
	
	<th align='center'  colspan='6'>".$_SESSION['lang']['bi']."</th>
    <th align='center'  colspan='6'>".$_SESSION['lang']['sbi']."</th>
    <th align='center'  colspan='3' rowspan='2'>Budget ".$_SESSION['lang']['setahun']."</th>
  </tr>
  <tr>

  
    <th align='center'  colspan='2'>".$_SESSION['lang']['bi']."</th>
    <th align='center'  colspan='2'>".$_SESSION['lang']['sbi']."</th>
    <th align='center'  width='48' rowspan='2'>Budget ".$_SESSION['lang']['setahun']." (Kg)</th>
	
	 <th align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</th>
    <th align='center'  colspan='3'>Budget</th>
    <th align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</th>
    <th align='center'  colspan='3'>Budget</th>
	
	
    <th align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</th>
    <th align='center'  colspan='3'>Budget</th>
    <th align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</th>
    <th align='center'  colspan='3'>Budget</th>
    <th align='center'  colspan='2'>".$_SESSION['lang']['realisasi']."</th>
    <th align='center'  colspan='2'>Budget</th>
	
	 <th align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</th>
    <th align='center'  colspan='3'>Budget</th>
    <th align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</th>
    <th align='center'  colspan='3'>Budget</th>
	
  </tr>
  <tr>
    <th align='center'  width='31'>Real Kg</th>
    <th align='center'  width='47'>Budget Kg</th>
    <th align='center'  width='31'>Real Kg</th>
    <th align='center'  width='47'>Budget Kg</th>
	
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	
	
    <th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total Biaya</th>
    <th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total Biaya</th>
    <th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total Biaya</th>
    <th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total Biaya</th>
    <th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total Biaya</th>
    <th align='center'  width='51'>Prod (Rp/Kg)</th>
    <th align='center'  width='50'>".$_SESSION['lang']['pemel']." (Rp/Ha)</th>
    <th align='center'  width='51'>Prod (Rp/Kg)</th>
    <th align='center'  width='50'>".$_SESSION['lang']['pemel']." (Rp/Ha)</th>
    <th align='center' >Prod (Rp/Kg)</th>
    <th align='center' >Pemel (Rp/Ha)</th>
	
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	<th align='center'  width='37'>".$_SESSION['lang']['panen']."</th>
    <th align='center'  width='37'>".$_SESSION['lang']['pemel']."</th>
    <th align='center'  width='35'>Total</th>
	
  </tr>
        ";
$stream.="
        </tr>
    </thead>
 <tbody>";


###
#prepare data
###
	$where='';
if($divisi!=''){
	$where.= " and kodeorg like '".$divisi."%'";
}  else {
	$where.= " and kodeorg like '".$kdorg."%'";
}


$str="select substr(kodeorg,1,6) as divisi,jenisbibit,kodeorg as blok,tahuntanam as tahuntanam,"
        . " statusblok as statusblok,luasareaproduktif as luasareaproduktif"
        . " from ".$dbname.".setup_blok_tahunan where  tahun='".str_replace('-', '', $per2)."'  ".$where." ";
// echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($res);
        //exit('Error :'.$numrows);
if($numrows==0){
    $str="select substr(kodeorg,1,6) as divisi,jenisbibit,kodeorg as blok,tahuntanam as tahuntanam,"
        . " statusblok as statusblok,luasareaproduktif as luasareaproduktif"
        . " from ".$dbname.".setup_blok where 1=1 ".$where." ";
// echo $str;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
}
while($bar=$res->fetch())
{
    $kdblok[$bar['blok']]=$bar['blok'];
    $kddivisi[$bar['divisi']]=$bar['divisi'];
    $tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
    
    $listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
    
    $luas[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['luasareaproduktif'];
    $status[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['statusblok'];
    $jenisbibit[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jenisbibit'];
    
}


$str="select substr(kodeblok,1,6) as divisi,kodeblok as blok,thntnm as tahuntanam,"
        . " statusblok as statusblok,hathnini"
        . " from ".$dbname.".bgt_blok where kodeblok like '".$kdorg."%'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $kdblok[$bar['blok']]=$bar['blok'];
    $kddivisi[$bar['divisi']]=$bar['divisi'];
    $tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
    
    $listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
	$luas[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['hathnini'];
	$status[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['statusblok'];
}


#######################################
########## P R O D U K S I ############
#######################################
$str=" select * from ".$dbname.".kebun_spb_detail_vw where tanggal like '".$per2."%' and divisi like '".$kdorg."%'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$prdbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
}

#sdbi
$str=" select * from ".$dbname.".kebun_spb_detail_vw where tanggal  between '".$tgl1."' and '".$tgl2."' and divisi like '".$kdorg."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$prdsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
}




##bgt Budget produksi Kg ambil dari table =  bgt_produksi_kbn_kg_vw 
#bi
#sdbi
$addstr="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10)
    {
        $isi="kg0".$i;
    }
    else 
    {
        $isi="kg".$i;
    }
    if($i<intval($blnbgt))
    {
        $addstr.=$isi."+";
    }
    else
    {
        $addstr.=$isi;
    }
}
$addstr.=")";

$addstrthn="(";
for($i=1;$i<=12;$i++)
{
    if($i<10)
    {
        $isi="kg0".$i;
    }
    else 
    {
        $isi="kg".$i;
    }
    if($i<12)
    {
        $addstrthn.=$isi."+";
    }
    else
    {
        $addstrthn.=$isi;
    }
}
$addstrthn.=")";

$str=" select tahunbudget,kodeunit,divisi,kodeblok,thntnm,kg".$blnbgt." as bi,".$addstr." as jumlahbi,".$addstrthn." as jumlahthn "
        . " from ".$dbname.".bgt_produksi_kbn_kg_vw "
        . " where divisi like '".$kdorg."%' and tahunbudget='".$tahun."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $prdsdbibgt[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]=$bar['jumlahbi'];
	$prdsetahunbgt[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]=$bar['jumlahthn'];
	$prdbibgt[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]=$bar['bi'];
    
}

#######################################
#############  B I A Y A ##############
#######################################

###BI
##real
#pnn
$str="select sum(a.jumlah) as jumlah, a.noakun,a.tanggal,a.periode,a.kodeblok,substr(a.kodeblok,1,6) as divisi,"
        . "b.tahuntanam from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b "
        . " on a.kodeblok=b.kodeorg where a.noakun like '611%' and a.kodeorg='".$kdorg."' and a.periode='".$per2."' 
		group by substr(a.kodeblok,1,6), b.tahuntanam, a.kodeblok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$bybipnn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
}
#rwt
$str="select sum(a.jumlah) as jumlah,a.noakun,a.tanggal,a.periode,a.kodeblok,substr(a.kodeblok,1,6) as divisi,"
        . "b.tahuntanam from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b "
        . " on a.kodeblok=b.kodeorg where (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280224')) "
        . " and a.kodeorg='".$kdorg."' and a.periode='".$per2."' group by substr(a.kodeblok,1,6), b.tahuntanam, a.kodeblok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$bybirwt[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
}

##bgt
#pnn
$str=" select a.*,b.thntnm,substr(a.kodeorg,1,6) as divisi from "
        . " ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok "
        . " where a.kodeorg like '".$kdorg."%' and a.tahunbudget='".$tahun."' and a.noakun like '611%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$bybibgtpnn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rp'.$blnbgt];
    
}

#rwt
$str=" select a.*,b.thntnm,substr(a.kodeorg,1,6) as divisi from "
        . " ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok "
        . " where a.kodeorg like '".$kdorg."%' and a.tahunbudget='".$tahun."' and "
        . " (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280222')) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$bybibgtrwt[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rp'.$blnbgt];
    
}

###s/d BI
##real
#pnn
$str="select sum(a.jumlah) as jumlah,a.noakun,a.tanggal,a.periode,a.kodeblok,substr(a.kodeblok,1,6) as divisi,"
        . "b.tahuntanam from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b "
        . " on a.kodeblok=b.kodeorg where a.noakun like '611%' and a.kodeorg='".$kdorg."' and"
        . " a.tanggal  between '".$tgl1."' and '".$tgl2."' group by substr(a.kodeblok,1,6), b.tahuntanam, a.kodeblok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$bysdbipnn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
}

#rwt
$str="select sum(a.jumlah) as jumlah,a.noakun,a.tanggal,a.periode,a.kodeblok,substr(a.kodeblok,1,6) as divisi,"
        . "b.tahuntanam from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b "
        . " on a.kodeblok=b.kodeorg where (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280222')) 
			and a.kodeorg='".$kdorg."' and"
        . " a.tanggal  between '".$tgl1."' and '".$tgl2."' group by substr(a.kodeblok,1,6), b.tahuntanam, a.kodeblok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$bysdbirwt[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
}

##bgt
#pnn

$addstr="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10)
    {
        $isi="rp0".$i;
    }
    else 
    {
        $isi="rp".$i;
    }
    if($i<intval($blnbgt))
    {
        $addstr.=$isi."+";
    }
    else
    {
        $addstr.=$isi;
    }
}
$addstr.=")";


$addstrthn="(";
for($i=1;$i<=12;$i++)
{
    if($i<10)
    {
        $isi="rp0".$i;
    }
    else 
    {
        $isi="rp".$i;
    }
    if($i<12)
    {
        $addstrthn.=$isi."+";
    }
    else
    {
        $addstrthn.=$isi;
    }
}
$addstrthn.=")";


$str=" select a.noakun,a.tahunbudget,a.kodeorg,b.thntnm,substr(a.kodeorg,1,6) as divisi,".$addstr." as bi,".$addstrthn." as thn "
        . " from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b "
        . " on a.kodeorg=b.kodeblok where a.kodeorg like '".$kdorg."%' and a.tahunbudget='".$tahun."'"
        . " and a.noakun like '611%' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$bysdbibgtpnn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['bi'];
	@$bybgtsetahunpnn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['thn'];
}

$str=" select a.noakun,a.tahunbudget,a.kodeorg,b.thntnm,substr(a.kodeorg,1,6) as divisi,".$addstr." as bi,".$addstrthn." as thn "
        . " from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b "
        . " on a.kodeorg=b.kodeblok where a.kodeorg like '".$kdorg."%' and a.tahunbudget='".$tahun."'"
        . " and (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280222')) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$bysdbibgtrwt[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['bi'];
	@$bybgtsetahunrwt[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['thn'];
}



##sumber 1
$str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,hk 
	from  ".$dbname.".kebun_hk_panen_detail_vw where divisi like '".$kdorg."%' and tanggal like '".$per2."%' and jurnal=1 ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$hkbipnnbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['hk'];
}

##sumber 2
$str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,jumlahhk 
	from  ".$dbname.".kebun_perawatan_detail_vw where unit = '".$kdorg."' and tanggal like '".$per2."%'
	and jurnal=1 and left(noakun,3)='611' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$hkbipnnbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahhk'];
}


$str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,jumlahhk 
	from  ".$dbname.".kebun_perawatan_detail_vw where unit = '".$kdorg."' and tanggal like '".$per2."%'
	and jurnal=1 and left(noakun,3)!='611' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$hkbirwtbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahhk'];
}



#sumber 1
$str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,hk 
	from  ".$dbname.".kebun_hk_panen_detail_vw where divisi like '".$kdorg."%' and tanggal between '".$tgl1."' and '".$tgl2."' and jurnal=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$hkbipnnsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['hk'];
}

#sumber2
$str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,jumlahhk 
	from  ".$dbname.".kebun_perawatan_detail_vw where unit = '".$kdorg."' and tanggal between '".$tgl1."' and '".$tgl2."'
	and jurnal=1 and left(noakun,3)='611' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$hkbipnnsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahhk'];
}

 

$str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,jumlahhk 
	from  ".$dbname.".kebun_perawatan_detail_vw where unit = '".$kdorg."' and tanggal between '".$tgl1."' and '".$tgl2."'
	and jurnal=1 and left(noakun,3)!='611' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$hkbirwtsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahhk'];
}


#BGT
$kuncipnn="(";
$str="SELECT distinct a.kodeorg,substr(a.kodeorg,1,6) as divisi, a.kodebudget, a.kegiatan, a.jumlah, a.satuanj,a.kunci,b.thntnm
		FROM ".$dbname.".bgt_budget a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok
		where a.kodebudget in('SDM-PHL','SDM-KHT','SDM-KBL')
		and a.kodeorg like '".$kdorg."%' and a.satuanj='HK'  and (a.kegiatan='611010201' or a.kegiatan='611010202' or a.kegiatan='611010204')  and  a.tahunbudget='".$tahun."'";

	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=owlBaris($res);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$rowkuncipnn+=1;
	@$hkpnnbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['jumlah'];
	if($rowkuncipnn==$row)
	{
		$kuncipnn.="'".$bar['kunci']."'";
	}
	else
	{
		$kuncipnn.="'".$bar['kunci']."',";
	}
}
$kuncipnn.=")";


$kuncirwt="(";
$str="SELECT distinct a.kodeorg,substr(a.kodeorg,1,6) as divisi, a.kodebudget, a.kegiatan, a.jumlah, a.satuanj,a.kunci,b.thntnm
		FROM ".$dbname.".bgt_budget a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok
		where a.kodebudget in('SDM-PHL','SDM-KHT','SDM-KBL')
		and a.kodeorg like '".$kdorg."%' and a.satuanj='HK' and  a.tahunbudget='".$tahun."'
		and (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280222')) ";

	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=owlBaris($res);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$rowkuncirwt+=1;
	@$hkrwtbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['jumlah'];
	if($rowkuncirwt==$row)
	{
		$kuncirwt.="'".$bar['kunci']."'";
	}
	else
	{
		$kuncirwt.="'".$bar['kunci']."',";
	}
}
$kuncirwt.=")";





$addstr="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10)
    {
        $isi="rp0".$i;
    }
    else 
    {
        $isi="rp".$i;
    }
    if($i<intval($blnbgt))
    {
        $addstr.=$isi."+";
    }
    else
    {
        $addstr.=$isi;
    }
}
$addstr.=")";



$addstrthn="(";
for($i=1;$i<=12;$i++)
{
    if($i<10)
    {
        $isi="rp0".$i;
    }
    else 
    {
        $isi="rp".$i;
    }
    if($i<12)
    {
        $addstrthn.=$isi."+";
    }
    else
    {
        $addstrthn.=$isi;
    }
}
$addstrthn.=")";


if($kuncipnn=='()')
{
	$kuncipnn="('')";
}


if($kuncirwt=='()')
{
	$kuncirwt="('')";
}

$kuncipnn=str_replace(',)', ')', $kuncipnn);
$kuncirwt=str_replace(',)', ')', $kuncirwt);

$str="select b.thntnm,a.kodeorg,substr(a.kodeorg,1,6) as divisi,rp".$blnbgt." as rpbi,".$addstr." as rpsdbi,".$addstrthn." as rpthn 
		from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok "
        . " where a.tahunbudget='".$tahun."' and a.kodeorg like '".$kdorg."%' and a.kunci in ".$kuncipnn." "; 		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$rppnnbgtbi[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpbi'];
	@$rppnnbgtsdbi[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpsdbi'];
	@$rppnnbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpthn'];
}


$str="select b.thntnm,a.kodeorg,substr(a.kodeorg,1,6) as divisi,rp".$blnbgt." as rpbi,".$addstr." as rpsdbi,".$addstrthn." as rpthn 
		from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok "
        . " where a.tahunbudget='".$tahun."' and a.kodeorg like '".$kdorg."%' and a.kunci in ".$kuncirwt." "; 		

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$rprwtbgtbi[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpbi'];
	@$rprwtbgtsdbi[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpsdbi'];
	@$rprwtbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpthn'];
}



//$romawi=array("1"=>"I","2"=>"II","3"=>"III","4"=>"IV","5"=>"V","6"=>"VI","7"=>"VII","8"=>"VIII",,"9"=>"IX");

array_multisort($kddivisi,SORT_ASC);
array_multisort($tahuntanam,SORT_ASC);
array_multisort($kdblok,SORT_ASC);

foreach($kddivisi as $divisi)
{
    foreach($tahuntanam as $thntnm)
    {
        if(@$listtahuntanam[$divisi][$thntnm]!='')
        {
            foreach($kdblok as $blok)
            {
                if(@$listblok[$divisi][$thntnm][$blok]!='')
                {
					
					@$hkbibgtpnn[$divisi][$thntnm][$blok]=($rppnnbgtbi[$divisi][$thntnm][$blok]/$rppnnbgtthn[$divisi][$thntnm][$blok])*$hkpnnbgtthn[$divisi][$thntnm][$blok];
					@$hkbibgtrwt[$divisi][$thntnm][$blok]=($rprwtbgtbi[$divisi][$thntnm][$blok]/$rprwtbgtthn[$divisi][$thntnm][$blok])*$hkrwtbgtthn[$divisi][$thntnm][$blok];
					
					@$hksdbibgtpnn[$divisi][$thntnm][$blok]=($rppnnbgtsdbi[$divisi][$thntnm][$blok]/$rppnnbgtthn[$divisi][$thntnm][$blok])*$hkpnnbgtthn[$divisi][$thntnm][$blok];
					@$hksdbibgtrwt[$divisi][$thntnm][$blok]=($rprwtbgtsdbi[$divisi][$thntnm][$blok]/$rprwtbgtthn[$divisi][$thntnm][$blok])*$hkrwtbgtthn[$divisi][$thntnm][$blok];
					
					@$no+=1;
                    $stream.="
                    <tr class=rowcontent style=cursor:pointer; title='click detail' onclick=html2('".$listblok[$divisi][$thntnm][$blok]."','".$per2."')>
                        <td align=center>".$no."</td>
                        <td align=center>".(substr($divisi,4,2))."</td>
                        <td align=center>".getNamaOrg($listblok[$divisi][$thntnm][$blok])."</td>    
                        <td align=center>".$listtahuntanam[$divisi][$thntnm]."</td>
                        <td align=right>".@hidezerodecimal($luas[$divisi][$thntnm][$blok],2)."</td>     
                        <td>".$jenisbibit[$divisi][$thntnm][$blok]."</td>     
                        <td align=left>".$status[$divisi][$thntnm][$blok]."</td>       
						
                        <td align=right>".@hidezerodecimal(nantodouble($prdbi[$divisi][$thntnm][$blok]),2)."</td>    
                        <td align=right>".@hidezerodecimal(nantodouble($prdbibgt[$divisi][$thntnm][$blok]),2)."</td>    
                        <td align=right>".@hidezerodecimal(nantodouble($prdsdbi[$divisi][$thntnm][$blok]),2)."</td>
                        <td align=right>".@hidezerodecimal(nantodouble($prdsdbibgt[$divisi][$thntnm][$blok]),2)."</td>   
                        <td align=right>".@hidezerodecimal(nantodouble($prdsetahunbgt[$divisi][$thntnm][$blok]),2)."</td>   
						
						<td align=right>".@hidezerodecimal(nantodouble($hkbipnnbi[$divisi][$thntnm][$blok]),2)."</td>  
						<td align=right>".@hidezerodecimal(nantodouble($hkbirwtbi[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkbipnnbi[$divisi][$thntnm][$blok]+$hkbirwtbi[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnn[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkbibgtrwt[$divisi][$thntnm][$blok]),2)."</td>						
						<td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnn[$divisi][$thntnm][$blok]+$hkbibgtrwt[$divisi][$thntnm][$blok]),2)."</td>
						
						<td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbi[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkbirwtsdbi[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbi[$divisi][$thntnm][$blok]+$hkbirwtsdbi[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnn[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtrwt[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnn[$divisi][$thntnm][$blok]+$hksdbibgtrwt[$divisi][$thntnm][$blok]),2)."</td>
						
						<td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthn[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkrwtbgtthn[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthn[$divisi][$thntnm][$blok]+$hkrwtbgtthn[$divisi][$thntnm][$blok]),2)."</td>
						
                        <td align=right>".@hidezerodecimal(nantodouble($bybipnn[$divisi][$thntnm][$blok]))."</td>
                        <td align=right>".@hidezerodecimal(nantodouble($bybirwt[$divisi][$thntnm][$blok]))."</td>    
                        <td align=right>".@hidezerodecimal(nantodouble($bybipnn[$divisi][$thntnm][$blok]+$bybirwt[$divisi][$thntnm][$blok]))."</td>                             
                        <td align=right>".@hidezerodecimal(nantodouble($bybibgtpnn[$divisi][$thntnm][$blok]))."</td>   
                        <td align=right>".@hidezerodecimal(nantodouble($bybibgtrwt[$divisi][$thntnm][$blok]))."</td>       
                        <td align=right>".@hidezerodecimal(nantodouble($bybibgtpnn[$divisi][$thntnm][$blok]+$bybibgtrwt[$divisi][$thntnm][$blok]))."</td>      
                        

                        <td align=right>".@hidezerodecimal(nantodouble($bysdbipnn[$divisi][$thntnm][$blok]))."</td>  
                        <td align=right>".@hidezerodecimal(nantodouble($bysdbirwt[$divisi][$thntnm][$blok]))."</td> 
                        <td align=right>".@hidezerodecimal(nantodouble($bysdbipnn[$divisi][$thntnm][$blok]+$bysdbirwt[$divisi][$thntnm][$blok]))."</td>                  
                        <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtpnn[$divisi][$thntnm][$blok]))."</td>  
                        <td align=right>".@hidezerodecimal($bysdbibgtrwt[$divisi][$thntnm][$blok])."</td>  
                        <td align=right>".@hidezerodecimal($bysdbibgtpnn[$divisi][$thntnm][$blok]+$bysdbibgtrwt[$divisi][$thntnm][$blok])."</td>      
                        
                        <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnn[$divisi][$thntnm][$blok]))."</td>  
                        <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunrwt[$divisi][$thntnm][$blok]))."</td>  
                        <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnn[$divisi][$thntnm][$blok]+$bybgtsetahunrwt[$divisi][$thntnm][$blok]))."</td>      
                        
						<td align=right>".@hidezerodecimal(nantodouble($bysdbipnn[$divisi][$thntnm][$blok]/$prdsdbi[$divisi][$thntnm][$blok]),2)."</td>  
						<td align=right>".@hidezerodecimal(nantodouble($bysdbirwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]))."</td>
						
						<td align=right>".@hidezerodecimal(nantodouble($bysdbibgtpnn[$divisi][$thntnm][$blok]/$prdsdbibgt[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($bysdbibgtrwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]))."</td>	
						
						<td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnn[$divisi][$thntnm][$blok]/$prdsetahunbgt[$divisi][$thntnm][$blok]),2)."</td>	
						<td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunrwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]))."</td>	
						
						<td align=right>".@hidezerodecimal(nantodouble($hkbipnnbi[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>  
						<td align=right>".@hidezerodecimal(nantodouble($hkbirwtbi[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble(($hkbipnnbi[$divisi][$thntnm][$blok]+$hkbirwtbi[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkbibgtrwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>						
						<td align=right>".@hidezerodecimal(nantodouble(($hkbibgtpnn[$divisi][$thntnm][$blok]+$hkbibgtrwt[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok]),2)."</td>
						
						<td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbi[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal($hkbirwtsdbi[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble(($hkbipnnsdbi[$divisi][$thntnm][$blok]+$hkbirwtsdbi[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtrwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble(($hksdbibgtpnn[$divisi][$thntnm][$blok]+$hksdbibgtrwt[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok]),2)."</td>
						
						<td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble($hkrwtbgtthn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]),2)."</td>
						<td align=right>".@hidezerodecimal(nantodouble(($hkpnnbgtthn[$divisi][$thntnm][$blok]+$hkrwtbgtthn[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok]),2)."</td>
                    
                    </tr>";
                    @$luastt[$divisi][$thntnm]+=nantodouble($luas[$divisi][$thntnm][$blok]);
                    @$prdbitt[$divisi][$thntnm]+=nantodouble($prdbi[$divisi][$thntnm][$blok]);
                    @$prdbibgttt[$divisi][$thntnm]+=nantodouble($prdbibgt[$divisi][$thntnm][$blok]);
                    @$prdsdbitt[$divisi][$thntnm]+=nantodouble($prdsdbi[$divisi][$thntnm][$blok]);
                    @$prdsdbibgttt[$divisi][$thntnm]+=nantodouble($prdsdbibgt[$divisi][$thntnm][$blok]);
                    @$prdsetahunbgttt[$divisi][$thntnm]+=nantodouble($prdsetahunbgt[$divisi][$thntnm][$blok]);
					
					
					@$hkbipnnbitt[$divisi][$thntnm]+=nantodouble($hkbipnnbi[$divisi][$thntnm][$blok]);
					@$hkbirwtbitt[$divisi][$thntnm]+=nantodouble($hkbirwtbi[$divisi][$thntnm][$blok]);
					@$hkbipnnsdbitt[$divisi][$thntnm]+=nantodouble($hkbipnnsdbi[$divisi][$thntnm][$blok]);
					@$hkbirwtsdbitt[$divisi][$thntnm]+=nantodouble($hkbirwtsdbi[$divisi][$thntnm][$blok]);	
					
					
					@$hkbibgtpnntt[$divisi][$thntnm]+=nantodouble($hkbibgtpnn[$divisi][$thntnm][$blok]);
					@$hkbibgtrwttt[$divisi][$thntnm]+=nantodouble($hkbibgtrwt[$divisi][$thntnm][$blok]);
					@$hksdbibgtpnntt[$divisi][$thntnm]+=nantodouble($hksdbibgtpnn[$divisi][$thntnm][$blok]);
					@$hksdbibgtrwttt[$divisi][$thntnm]+=nantodouble($hksdbibgtrwt[$divisi][$thntnm][$blok]);
					
					@$hkpnnbgtthntt[$divisi][$thntnm]+=nantodouble($hkpnnbgtthn[$divisi][$thntnm][$blok]);
					@$hkrwtbgtthntt[$divisi][$thntnm]+=nantodouble($hkrwtbgtthn[$divisi][$thntnm][$blok]);
					
					
                    @$bybipnntt[$divisi][$thntnm]+=nantodouble($bybipnn[$divisi][$thntnm][$blok]);
                    @$bybirwttt[$divisi][$thntnm]+=nantodouble($bybirwt[$divisi][$thntnm][$blok]);
                    @$bybibgtpnntt[$divisi][$thntnm]+=nantodouble($bybibgtpnn[$divisi][$thntnm][$blok]);
                    @$bybibgtrwttt[$divisi][$thntnm]+=nantodouble($bybibgtrwt[$divisi][$thntnm][$blok]);
                    
                    @$bysdbipnntt[$divisi][$thntnm]+=nantodouble($bysdbipnn[$divisi][$thntnm][$blok]);
                    @$bysdbirwttt[$divisi][$thntnm]+=nantodouble($bysdbirwt[$divisi][$thntnm][$blok]);
                    @$bysdbibgtpnntt[$divisi][$thntnm]+=nantodouble($bysdbibgtpnn[$divisi][$thntnm][$blok]);
                    @$bysdbibgtrwttt[$divisi][$thntnm]+=nantodouble($bysdbibgtrwt[$divisi][$thntnm][$blok]);
                    
                    @$bybgtsetahunpnntt[$divisi][$thntnm]+=nantodouble($bybgtsetahunpnn[$divisi][$thntnm][$blok]);
                    @$bybgtsetahunrwttt[$divisi][$thntnm]+=nantodouble($bybgtsetahunrwt[$divisi][$thntnm][$blok]);
                    
                }
            }
            #subtotal tt
            $stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=4>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['tahuntanam']."  ".$thntnm."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($luastt[$divisi][$thntnm]),2)."</td>
                    <td align=center></td>   
                    <td align=center></td>   
                    <td align=right>".@hidezerodecimal(nantodouble($prdbitt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($prdbibgttt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($prdsdbitt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($prdsdbibgttt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($prdsetahunbgttt[$divisi][$thntnm]),2)."</td>
					
					
                    <td align=right>".@hidezerodecimal(nantodouble($hkbipnnbitt[$divisi][$thntnm]),2)."</td>  
                    <td align=right>".@hidezerodecimal(nantodouble($hkbirwtbitt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbipnnbitt[$divisi][$thntnm]+$hkbirwtbitt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnntt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbibgtrwttt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnntt[$divisi][$thntnm]+$hkbibgtrwttt[$divisi][$thntnm]),2)."</td>

                    <td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbitt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbirwtsdbitt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbitt[$divisi][$thntnm]+$hkbirwtsdbitt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnntt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hksdbibgtrwttt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnntt[$divisi][$thntnm]+$hksdbibgtrwttt[$divisi][$thntnm]),2)."</td>
					
                    <td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthntt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkrwtbgtthntt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthntt[$divisi][$thntnm]+$hkrwtbgtthntt[$divisi][$thntnm]),2)."</td>
						
                        
                    <td align=right>".@hidezerodecimal(nantodouble($bybipnntt[$divisi][$thntnm]))."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($bybirwttt[$divisi][$thntnm]))."</td>    
                    <td align=right>".@hidezerodecimal(nantodouble($bybipnntt[$divisi][$thntnm]+$bybirwttt[$divisi][$thntnm]))."</td>   
                    <td align=right>".@hidezerodecimal(nantodouble($bybibgtpnntt[$divisi][$thntnm]))."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($bybibgtrwttt[$divisi][$thntnm]))."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($bybibgtpnntt[$divisi][$thntnm]+$bybibgtrwttt[$divisi][$thntnm]))."</td>        
                    
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbipnntt[$divisi][$thntnm]))."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbirwttt[$divisi][$thntnm]))."</td>    
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbipnntt[$divisi][$thntnm]+$bysdbirwttt[$divisi][$thntnm]))."</td>  
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtpnntt[$divisi][$thntnm]))."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtrwttt[$divisi][$thntnm]))."</td>    
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtpnntt[$divisi][$thntnm]+$bysdbibgtrwttt[$divisi][$thntnm]))."</td>  
                    
                    <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnntt[$divisi][$thntnm]))."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunrwttt[$divisi][$thntnm]))."</td>    
                    <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnntt[$divisi][$thntnm]+$bybgtsetahunrwttt[$divisi][$thntnm]))."</td>  
					
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbipnntt[$divisi][$thntnm]/$prdsdbitt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbirwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]))."</td>

                    <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtpnntt[$divisi][$thntnm]/$prdsdbibgttt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtrwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]))."</td>	
					
                    <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnntt[$divisi][$thntnm]/$prdsetahunbgttt[$divisi][$thntnm]),2)."</td>	
                    <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunrwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]))."</td>	 


					<td align=right>".@hidezerodecimal(nantodouble($hkbipnnbitt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>  
                    <td align=right>".@hidezerodecimal(nantodouble($hkbirwtbitt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble(($hkbipnnbitt[$divisi][$thntnm]+$hkbirwtbitt[$divisi][$thntnm])/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbibgtrwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble(($hkbibgtpnntt[$divisi][$thntnm]+$hkbibgtrwttt[$divisi][$thntnm])/$luastt[$divisi][$thntnm]),2)."</td>

                    <td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbitt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkbirwtsdbitt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble(($hkbipnnsdbitt[$divisi][$thntnm]+$hkbirwtsdbitt[$divisi][$thntnm])/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hksdbibgtrwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble(($hksdbibgtpnntt[$divisi][$thntnm]+$hksdbibgtrwttt[$divisi][$thntnm])/$luastt[$divisi][$thntnm]),2)."</td>
					
                    <td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble($hkrwtbgtthntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]),2)."</td>
                    <td align=right>".@hidezerodecimal(nantodouble(($hkpnnbgtthntt[$divisi][$thntnm]+$hkrwtbgtthntt[$divisi][$thntnm])/$luastt[$divisi][$thntnm]),2)."</td>	
					
                </tr>";        
            @$luasdiv[$divisi]+=nantodouble($luastt[$divisi][$thntnm]);
            @$prdbidiv[$divisi]+=nantodouble($prdbitt[$divisi][$thntnm]);
            @$prdbibgtdiv[$divisi]+=nantodouble($prdbibgttt[$divisi][$thntnm]);
            @$prdsdbidiv[$divisi]+=nantodouble($prdsdbitt[$divisi][$thntnm]);
            @$prdsdbibgtdiv[$divisi]+=nantodouble($prdsdbibgttt[$divisi][$thntnm]);
            @$prdsetahunbgtdiv[$divisi]+=nantodouble($prdsetahunbgttt[$divisi][$thntnm]);
			
            @$hkbipnnbidiv[$divisi]+=nantodouble($hkbipnnbitt[$divisi][$thntnm]);
            @$hkbirwtbidiv[$divisi]+=nantodouble($hkbirwtbitt[$divisi][$thntnm]);
            @$hkbipnnsdbidiv[$divisi]+=nantodouble($hkbipnnsdbitt[$divisi][$thntnm]);
            @$hkbirwtsdbidiv[$divisi]+=nantodouble($hkbirwtsdbitt[$divisi][$thntnm]);

            @$hkbibgtpnndiv[$divisi]+=nantodouble($hkbibgtpnntt[$divisi][$thntnm]);
            @$hkbibgtrwtdiv[$divisi]+=nantodouble($hkbibgtrwttt[$divisi][$thntnm]);
            @$hksdbibgtpnndiv[$divisi]+=nantodouble($hksdbibgtpnntt[$divisi][$thntnm]);
            @$hksdbibgtrwtdiv[$divisi]+=nantodouble($hksdbibgtrwttt[$divisi][$thntnm]);

            @$hkpnnbgtthndiv[$divisi]+=nantodouble($hkpnnbgtthntt[$divisi][$thntnm]);
            @$hkrwtbgtthndiv[$divisi]+=nantodouble($hkrwtbgtthntt[$divisi][$thntnm]);
			
            @$bybipnndiv[$divisi]+=nantodouble($bybipnntt[$divisi][$thntnm]);
            @$bybirwtdiv[$divisi]+=nantodouble($bybirwttt[$divisi][$thntnm]);
            @$bybibgtpnndiv[$divisi]+=nantodouble($bybibgtpnntt[$divisi][$thntnm]);
            @$bybibgtrwtdiv[$divisi]+=nantodouble($bybibgtrwttt[$divisi][$thntnm]);
            
            @$bysdbipnndiv[$divisi]+=nantodouble($bysdbipnntt[$divisi][$thntnm]);
            @$bysdbirwtdiv[$divisi]+=nantodouble($bysdbirwttt[$divisi][$thntnm]);
            @$bysdbibgtpnndiv[$divisi]+=nantodouble($bysdbibgtpnntt[$divisi][$thntnm]);
            @$bysdbibgtrwtdiv[$divisi]+=nantodouble($bysdbibgtrwttt[$divisi][$thntnm]);
            
            @$bybgtsetahunpnndiv[$divisi]+=nantodouble($bybgtsetahunpnntt[$divisi][$thntnm]);
            @$bybgtsetahunrwtdiv[$divisi]+=nantodouble($bybgtsetahunrwttt[$divisi][$thntnm]);
            
        }
    }
    
    $stream.="
        <tr bgcolor=#48D1CC>
            <td align=left colspan=4>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['divisi']." ".$divisi."</td>
            <td align=right>".@hidezerodecimal(nantodouble($luasdiv[$divisi]),2)."</td>
            <td align=center></td>       
            <td align=center></td>       
            <td align=right>".@hidezerodecimal(nantodouble($prdbidiv[$divisi]),2)."</td>
            <td align=right>".@hidezerodecimal(nantodouble($prdbibgtdiv[$divisi]),2)."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($prdsdbidiv[$divisi]),2)."</td>
            <td align=right>".@hidezerodecimal(nantodouble($prdsdbibgtdiv[$divisi]),2)."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($prdsetahunbgtdiv[$divisi]),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($hkbipnnbidiv[$divisi]),2)."</td>  
			<td align=right>".@hidezerodecimal(nantodouble($hkbirwtbidiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbipnnbidiv[$divisi]+$hkbirwtbidiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnndiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbibgtrwtdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnndiv[$divisi]+$hkbibgtrwtdiv[$divisi]),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbidiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbirwtsdbidiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbidiv[$divisi]+$hkbirwtsdbidiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnndiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtrwtdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnndiv[$divisi]+$hksdbibgtrwtdiv[$divisi]),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthndiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkrwtbgtthndiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthndiv[$divisi]+$hkrwtbgtthndiv[$divisi]),2)."</td>
						
            <td align=right>".@hidezerodecimal(nantodouble($bybipnndiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bybirwtdiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bybipnndiv[$divisi]+$bybirwtdiv[$divisi]))."</td>   
            <td align=right>".@hidezerodecimal(nantodouble($bybibgtpnndiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bybibgtrwtdiv[$divisi]))."</td>   
            <td align=right>".@hidezerodecimal(nantodouble($bybibgtpnndiv[$divisi]+$bybibgtrwtdiv[$divisi]))."</td>   
                
            <td align=right>".@hidezerodecimal(nantodouble($bysdbipnndiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bysdbirwtdiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bysdbipnndiv[$divisi]+$bysdbirwtdiv[$divisi]))."</td> 
            <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtpnndiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtrwtdiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bysdbibgtpnndiv[$divisi]+$bysdbibgtrwtdiv[$divisi]))."</td>  
                
            <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnndiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunrwtdiv[$divisi]))."</td>  
            <td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnndiv[$divisi]+$bybgtsetahunrwtdiv[$divisi]))."</td> 
			
			<td align=right>".@hidezerodecimal(nantodouble($bysdbipnndiv[$divisi]/$prdsdbidiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($bysdbirwtdiv[$divisi]/$luasdiv[$divisi]))."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($bysdbibgtpnndiv[$divisi]/$prdsdbibgtdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($bysdbibgtrwtdiv[$divisi]/$luasdiv[$divisi]))."</td>	
			
			<td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunpnndiv[$divisi]/$prdsetahunbgtdiv[$divisi]),2)."</td>	
			<td align=right>".@hidezerodecimal(nantodouble($bybgtsetahunrwtdiv[$divisi]/$luasdiv[$divisi]))."</td>	 

			
			
			<td align=right>".@hidezerodecimal(nantodouble($hkbipnnbidiv[$divisi]/$luasdiv[$divisi]),2)."</td>  
			<td align=right>".@hidezerodecimal(nantodouble($hkbirwtbidiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($hkbipnnbidiv[$divisi]+$hkbirwtbidiv[$divisi])/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbibgtpnndiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbibgtrwtdiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($hkbibgtpnndiv[$divisi]+$hkbibgtrwtdiv[$divisi])/$luasdiv[$divisi]),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($hkbipnnsdbidiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkbirwtsdbidiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($hkbipnnsdbidiv[$divisi]+$hkbirwtsdbidiv[$divisi])/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtpnndiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hksdbibgtrwtdiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($hksdbibgtpnndiv[$divisi]+$hksdbibgtrwtdiv[$divisi])/$luasdiv[$divisi]),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($hkpnnbgtthndiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($hkrwtbgtthndiv[$divisi]/$luasdiv[$divisi]),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($hkpnnbgtthndiv[$divisi]+$hkrwtbgtthndiv[$divisi])/$luasdiv[$divisi]),2)."</td>
			

        </tr>";
    @$gtluas+=nantodouble($luasdiv[$divisi]);
    @$gtprdbi+=nantodouble($prdbidiv[$divisi]);
    @$gtprdbibgt+=nantodouble($prdbibgtdiv[$divisi]);
    @$gtprdsdbi+=nantodouble($prdsdbidiv[$divisi]);
    @$gtprdsdbibgt+=nantodouble($prdsdbibgtdiv[$divisi]);
    @$gtprdsetahunbgt+=nantodouble($prdsetahunbgtdiv[$divisi]); 
	
	
	@$gthkbipnnbi+=nantodouble($hkbipnnbidiv[$divisi]);
	@$gthkbirwtbi+=nantodouble($hkbirwtbidiv[$divisi]);
	@$gthkbipnnsdbi+=nantodouble($hkbipnnsdbidiv[$divisi]);
	@$gthkbirwtsdbi+=nantodouble($hkbirwtsdbidiv[$divisi]);
	
	
	@$gthkbibgtpnn+=nantodouble($hkbibgtpnndiv[$divisi]);
	@$gthkbibgtrwt+=nantodouble($hkbibgtrwtdiv[$divisi]);
	@$gthksdbibgtpnn+=nantodouble($hksdbibgtpnndiv[$divisi]);
	@$gthksdbibgtrwt+=nantodouble($hksdbibgtrwtdiv[$divisi]);
	
	@$gthkpnnbgtthn+=nantodouble($hkpnnbgtthndiv[$divisi]);
	@$gthkrwtbgtthn+=nantodouble($hkrwtbgtthndiv[$divisi]);
	
	
	
    @$gtbybipnn+=nantodouble($bybipnndiv[$divisi]);
    @$gtbybirwt+=nantodouble($bybirwtdiv[$divisi]);
    @$gtbybibgtpnn+=nantodouble($bybibgtpnndiv[$divisi]);
    @$gtbybibgtrwt+=nantodouble($bybibgtrwtdiv[$divisi]);
    
    @$gtbysdbipnn+=nantodouble($bysdbipnndiv[$divisi]);
    @$gtbysdbirwt+=nantodouble($bysdbirwtdiv[$divisi]);
    @$gtbysdbibgtpnn+=nantodouble($bysdbibgtpnndiv[$divisi]);
    @$gtbysdbibgtrwt+=nantodouble($bysdbibgtrwtdiv[$divisi]);
    
    @$gtbybgtsetahunpnn+=nantodouble($bybgtsetahunpnndiv[$divisi]);
    @$gtbybgtsetahunrwt+=nantodouble($bybgtsetahunrwtdiv[$divisi]);
}
$stream.="
        <tr bgcolor=#009999>
            <td align=left colspan=4>".$_SESSION['lang']['grnd_total']." ".$kdorg."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtluas),2)."</td>
            <td align=center></td>   
            <td align=center></td>   
            <td align=right>".@hidezerodecimal(nantodouble($gtprdbi),2)."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtprdbibgt),2)."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtprdsdbi),2)."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtprdsdbibgt),2)."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtprdsetahunbgt),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($gthkbipnnbi),2)."</td>  
			<td align=right>".@hidezerodecimal(nantodouble($gthkbirwtbi),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbipnnbi+$gthkbirwtbi),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbibgtpnn),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbibgtrwt),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbibgtpnn+$gthkbibgtrwt),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($gthkbipnnsdbi),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbirwtsdbi),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbipnnsdbi+$gthkbirwtsdbi),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthksdbibgtpnn),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthksdbibgtrwt),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthksdbibgtpnn+$gthksdbibgtrwt),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($gthkpnnbgtthn),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkrwtbgtthn),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkpnnbgtthn+$gthkrwtbgtthn),2)."</td>
			
                
            <td align=right>".@hidezerodecimal(nantodouble($gtbybipnn))."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtbybirwt))."</td>    
            <td align=right>".@hidezerodecimal(nantodouble($gtbybipnn+$gtbybirwt))."</td>        
            <td align=right>".@hidezerodecimal(nantodouble($gtbybibgtpnn))."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtbybibgtrwt))."</td>    
            <td align=right>".@hidezerodecimal(nantodouble($gtbybibgtpnn+$gtbybibgtrwt))."</td>      
                
            <td align=right>".@hidezerodecimal(nantodouble($gtbysdbipnn))."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtbysdbirwt))."</td>    
            <td align=right>".@hidezerodecimal(nantodouble($gtbysdbipnn+$gtbysdbirwt))."</td>        
            <td align=right>".@hidezerodecimal(nantodouble($gtbysdbibgtpnn))."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtbysdbibgtrwt))."</td>    
            <td align=right>".@hidezerodecimal(nantodouble($gtbysdbibgtpnn+$gtbysdbibgtrwt))."</td>    
                
            <td align=right>".@hidezerodecimal(nantodouble($gtbybgtsetahunpnn))."</td>
            <td align=right>".@hidezerodecimal(nantodouble($gtbybgtsetahunrwt))."</td>    
            <td align=right>".@hidezerodecimal(nantodouble($gtbybgtsetahunpnn+$gtbybgtsetahunrwt))."</td> 
			
			<td align=right>".@hidezerodecimal(nantodouble($gtbysdbipnn/$gtprdsdbi),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gtbysdbirwt/$gtluas))."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gtbysdbibgtpnn/$gtprdsdbibgt),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gtbysdbibgtrwt/$gtluas))."</td>	
			<td align=right>".@hidezerodecimal(nantodouble($gtbybgtsetahunpnn/$gtprdsetahunbgt),2)."</td>	
			<td align=right>".@hidezerodecimal(nantodouble($gtbybgtsetahunrwt/$gtluas))."</td>	
            
			
			<td align=right>".@hidezerodecimal(nantodouble($gthkbipnnbi/$gtluas),2)."</td>  
			<td align=right>".@hidezerodecimal(nantodouble($gthkbirwtbi/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($gthkbipnnbi+$gthkbirwtbi)/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbibgtpnn/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbibgtrwt/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($gthkbibgtpnn+$gthkbibgtrwt)/$gtluas),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($gthkbipnnsdbi/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkbirwtsdbi/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($gthkbipnnsdbi+$gthkbirwtsdbi)/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthksdbibgtpnn/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthksdbibgtrwt/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($gthksdbibgtpnn+$gthksdbibgtrwt)/$gtluas),2)."</td>
			
			<td align=right>".@hidezerodecimal(nantodouble($gthkpnnbgtthn/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble($gthkrwtbgtthn/$gtluas),2)."</td>
			<td align=right>".@hidezerodecimal(nantodouble(($gthkpnnbgtthn+$gthkrwtbgtthn)/$gtluas),2)."</td>
			
        </tr>";    

$stream.="
 </tbody>
     </table></div>";

switch ($method) {
######PREVIEW
    case 'html1':
		echo"
			<button id=tomboldetail class=mybutton onclick=html1()>Level 1</button> 
		";
		
		echo"<br>";
		
		echo "
			<button id=tomboldetail class=mybutton onclick=excel1(event)>" . $_SESSION['lang']['excel'] . " 1</button>   
		";
		
        echo $stream;
        break;

######EXCEL	
    case 'excel1':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "BIAYA_DAN_PRODUKSI_PERBLOK_LV1_" . $kdorg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
        break;
}
?>