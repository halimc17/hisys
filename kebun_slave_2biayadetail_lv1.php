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

if ($method == 'excel1') {
    $stream = "<table class=sortable cellspacing=1 border=1>";

} else {
    $stream = "<table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>
    <td align='center'  width='20' rowspan='4'>No</td>
    <td align='center'  width='30' rowspan='4'>".$_SESSION['lang']['divisi']."</td>
    <td align='center'  width='29' rowspan='4'>".$_SESSION['lang']['blok']."</td>
    <td align='center'  width='18' rowspan='4'>".$_SESSION['lang']['thntnm']."</td>
    <td align='center'  width='29' rowspan='4'>".$_SESSION['lang']['luas']."</td>
    <td align='center'  width='50' rowspan='4'>".$_SESSION['lang']['jenisbibit']."</td>
    <td align='center'  width='37' rowspan='4'>".$_SESSION['lang']['statusblok']."</td>
    <td align='center'  colspan='5' rowspan='2'>".$_SESSION['lang']['produksi']."</td>
	
	<td align='center'  colspan='15'>".$_SESSION['lang']['jhk']."</td>
	
    <td align='center'  colspan='15'>".$_SESSION['lang']['biaya']."</td>
    <td align='center'  colspan='6'>Cost per ".$_SESSION['lang']['satuan']."</td>
	<td align='center'  colspan='15'>".$_SESSION['lang']['jhk']."/HA</td>
  </tr>
  
  <tr>
	<td align='center'  colspan='6'>".$_SESSION['lang']['bi']."</td>
    <td align='center'  colspan='6'>".$_SESSION['lang']['sbi']."</td>
    <td align='center'  colspan='3' rowspan='2'>".$_SESSION['lang']['budget']." ".$_SESSION['lang']['setahun']."</td>
  
    <td align='center'  colspan='6'>".$_SESSION['lang']['bi']."</td>
    <td align='center'  colspan='6'>".$_SESSION['lang']['sbi']."</td>
    <td align='center'  colspan='3' rowspan='2'>Budget ".$_SESSION['lang']['setahun']."</td>
    <td align='center'  colspan='4'>".$_SESSION['lang']['sbi']."</td>
    <td align='center'  colspan='2' rowspan='2'>".$_SESSION['lang']['setahun']."</td>
	
	<td align='center'  colspan='6'>".$_SESSION['lang']['bi']."</td>
    <td align='center'  colspan='6'>".$_SESSION['lang']['sbi']."</td>
    <td align='center'  colspan='3' rowspan='2'>Budget ".$_SESSION['lang']['setahun']."</td>
  </tr>
  <tr>

  
    <td align='center'  colspan='2'>".$_SESSION['lang']['bi']."</td>
    <td align='center'  colspan='2'>".$_SESSION['lang']['sbi']."</td>
    <td align='center'  width='48' rowspan='2'>Budget ".$_SESSION['lang']['setahun']." (Kg)</td>
	
	 <td align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</td>
    <td align='center'  colspan='3'>Budget</td>
    <td align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</td>
    <td align='center'  colspan='3'>Budget</td>
	
	
    <td align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</td>
    <td align='center'  colspan='3'>Budget</td>
    <td align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</td>
    <td align='center'  colspan='3'>Budget</td>
    <td align='center'  colspan='2'>".$_SESSION['lang']['realisasi']."</td>
    <td align='center'  colspan='2'>Budget</td>
	
	 <td align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</td>
    <td align='center'  colspan='3'>Budget</td>
    <td align='center'  colspan='3'>".$_SESSION['lang']['realisasi']."</td>
    <td align='center'  colspan='3'>Budget</td>
	
  </tr>
  <tr>
    <td align='center'  width='31'>Real Kg</td>
    <td align='center'  width='47'>Budget Kg</td>
    <td align='center'  width='31'>Real Kg</td>
    <td align='center'  width='47'>Budget Kg</td>
	
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	
	
    <td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total Biaya</td>
    <td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total Biaya</td>
    <td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total Biaya</td>
    <td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total Biaya</td>
    <td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total Biaya</td>
    <td align='center'  width='51'>Prod (Rp/Kg)</td>
    <td align='center'  width='50'>".$_SESSION['lang']['pemel']." (Rp/Ha)</td>
    <td align='center'  width='51'>Prod (Rp/Kg)</td>
    <td align='center'  width='50'>".$_SESSION['lang']['pemel']." (Rp/Ha)</td>
    <td align='center' >Prod (Rp/Kg)</td>
    <td align='center' >Pemel (Rp/Ha)</td>
	
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	<td align='center'  width='37'>".$_SESSION['lang']['panen']."</td>
    <td align='center'  width='37'>".$_SESSION['lang']['pemel']."</td>
    <td align='center'  width='35'>Total</td>
	
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


// $str="select substr(kodeorg,1,6) as divisi,jenisbibit,kodeorg as blok,tahuntanam as tahuntanam,"
        // . " statusblok as statusblok,luasareaproduktif as luasareaproduktif"
        // . " from ".$dbname.".setup_blok where 1=1 ".$where." ";
// // echo $str;
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // $kdblok[$bar['blok']]=$bar['blok'];
    // $kddivisi[$bar['divisi']]=$bar['divisi'];
    // $tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
    
    // $listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
    // $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
    
    // $luas[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['luasareaproduktif'];
    // $status[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['statusblok'];
    // $jenisbibit[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jenisbibit'];
    
// }


// $str="select substr(kodeblok,1,6) as divisi,kodeblok as blok,thntnm as tahuntanam,"
        // . " statusblok as statusblok,hathnini"
        // . " from ".$dbname.".bgt_blok where kodeblok like '".$kdorg."%'  ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // $kdblok[$bar['blok']]=$bar['blok'];
    // $kddivisi[$bar['divisi']]=$bar['divisi'];
    // $tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
    
    // $listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
    // $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
	// $luas[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['hathnini'];
	// $status[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['statusblok'];
// }


// #######################################
// ########## P R O D U K S I ############
// #######################################
// $str=" select * from ".$dbname.".kebun_spb_vw where tanggal like '".$per2."%' and divisi like '".$kdorg."%'  ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$prdbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
// }

// #sdbi
// $str=" select * from ".$dbname.".kebun_spb_vw where tanggal  between '".$tgl1."' and '".$tgl2."' and divisi like '".$kdorg."%' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$prdsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
// }




// ##bgt Budget produksi Kg ambil dari table =  bgt_produksi_kbn_kg_vw 
// #bi
// #sdbi
// $addstr="(";
// for($i=1;$i<=intval($blnbgt);$i++)
// {
    // if($i<10)
    // {
        // $isi="kg0".$i;
    // }
    // else 
    // {
        // $isi="kg".$i;
    // }
    // if($i<intval($blnbgt))
    // {
        // $addstr.=$isi."+";
    // }
    // else
    // {
        // $addstr.=$isi;
    // }
// }
// $addstr.=")";

// $addstrthn="(";
// for($i=1;$i<=12;$i++)
// {
    // if($i<10)
    // {
        // $isi="kg0".$i;
    // }
    // else 
    // {
        // $isi="kg".$i;
    // }
    // if($i<12)
    // {
        // $addstrthn.=$isi."+";
    // }
    // else
    // {
        // $addstrthn.=$isi;
    // }
// }
// $addstrthn.=")";

// $str=" select tahunbudget,kodeunit,divisi,kodeblok,thntnm,kg".$blnbgt." as bi,".$addstr." as jumlahbi,".$addstrthn." as jumlahthn "
        // . " from ".$dbname.".bgt_produksi_kbn_kg_vw "
        // . " where divisi like '".$kdorg."%' and tahunbudget='".$tahun."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // $prdsdbibgt[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]=$bar['jumlahbi'];
	// $prdsetahunbgt[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]=$bar['jumlahthn'];
	// $prdbibgt[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]=$bar['bi'];
    
// }

// #######################################
// #############  B I A Y A ##############
// #######################################

// ###BI
// ##real
// #pnn
// $str="select a.jumlah,a.noakun,a.tanggal,a.periode,a.kodeblok,substr(a.kodeblok,1,6) as divisi,"
        // . "b.tahuntanam from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b "
        // . " on a.kodeblok=b.kodeorg where a.noakun like '611%' and a.kodeorg='".$kdorg."' and a.periode='".$per2."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$bybipnn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
// }
// #rwt
// $str="select a.jumlah,a.noakun,a.tanggal,a.periode,a.kodeblok,substr(a.kodeblok,1,6) as divisi,"
        // . "b.tahuntanam from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b "
        // . " on a.kodeblok=b.kodeorg where (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280199')) "
        // . " and a.kodeorg='".$kdorg."' and a.periode='".$per2."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$bybirwt[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
// }

// ##bgt
// #pnn
// $str=" select a.*,b.thntnm,substr(a.kodeorg,1,6) as divisi from "
        // . " ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok "
        // . " where a.kodeorg like '".$kdorg."%' and a.tahunbudget='".$tahun."' and a.noakun like '611%' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$bybibgtpnn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rp'.$blnbgt];
    
// }

// #rwt
// $str=" select a.*,b.thntnm,substr(a.kodeorg,1,6) as divisi from "
        // . " ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok "
        // . " where a.kodeorg like '".$kdorg."%' and a.tahunbudget='".$tahun."' and "
        // . " (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280199')) ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$bybibgtrwt[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rp'.$blnbgt];
    
// }

// ###s/d BI
// ##real
// #pnn
// $str="select a.jumlah,a.noakun,a.tanggal,a.periode,a.kodeblok,substr(a.kodeblok,1,6) as divisi,"
        // . "b.tahuntanam from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b "
        // . " on a.kodeblok=b.kodeorg where a.noakun like '611%' and a.kodeorg='".$kdorg."' and"
        // . " a.tanggal  between '".$tgl1."' and '".$tgl2."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$bysdbipnn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
// }

// #rwt
// $str="select a.jumlah,a.noakun,a.tanggal,a.periode,a.kodeblok,substr(a.kodeblok,1,6) as divisi,"
        // . "b.tahuntanam from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b "
        // . " on a.kodeblok=b.kodeorg where (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280199')) 
			// and a.kodeorg='".$kdorg."' and"
        // . " a.tanggal  between '".$tgl1."' and '".$tgl2."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$bysdbirwt[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
// }

// ##bgt
// #pnn

// $addstr="(";
// for($i=1;$i<=intval($blnbgt);$i++)
// {
    // if($i<10)
    // {
        // $isi="rp0".$i;
    // }
    // else 
    // {
        // $isi="rp".$i;
    // }
    // if($i<intval($blnbgt))
    // {
        // $addstr.=$isi."+";
    // }
    // else
    // {
        // $addstr.=$isi;
    // }
// }
// $addstr.=")";


// $addstrthn="(";
// for($i=1;$i<=12;$i++)
// {
    // if($i<10)
    // {
        // $isi="rp0".$i;
    // }
    // else 
    // {
        // $isi="rp".$i;
    // }
    // if($i<12)
    // {
        // $addstrthn.=$isi."+";
    // }
    // else
    // {
        // $addstrthn.=$isi;
    // }
// }
// $addstrthn.=")";


// $str=" select a.noakun,a.tahunbudget,a.kodeorg,b.thntnm,substr(a.kodeorg,1,6) as divisi,".$addstr." as bi,".$addstrthn." as thn "
        // . " from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b "
        // . " on a.kodeorg=b.kodeblok where a.kodeorg like '".$kdorg."%' and a.tahunbudget='".$tahun."'"
        // . " and a.noakun like '611%' ";

// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$bysdbibgtpnn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['bi'];
	// @$bybgtsetahunpnn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['thn'];
// }

// $str=" select a.noakun,a.tahunbudget,a.kodeorg,b.thntnm,substr(a.kodeorg,1,6) as divisi,".$addstr." as bi,".$addstrthn." as thn "
        // . " from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b "
        // . " on a.kodeorg=b.kodeblok where a.kodeorg like '".$kdorg."%' and a.tahunbudget='".$tahun."'"
        // . " and (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280199')) ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$bysdbibgtrwt[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['bi'];
	// @$bybgtsetahunrwt[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['thn'];
// }



// ##sumber 1
// $str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,hk 
	// from  ".$dbname.".kebun_hk_panen_vw where divisi like '".$kdorg."%' and tanggal like '".$per2."%' and jurnal=1 ";

// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$hkbipnnbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['hk'];
// }

// ##sumber 2
// $str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,jumlahhk 
	// from  ".$dbname.".kebun_perawatan_vw where unit = '".$kdorg."' and tanggal like '".$per2."%'
	// and jurnal=1 and left(noakun,3)='611' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$hkbipnnbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahhk'];
// }


// $str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,jumlahhk 
	// from  ".$dbname.".kebun_perawatan_vw where unit = '".$kdorg."' and tanggal like '".$per2."%'
	// and jurnal=1 and left(noakun,3)!='611' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$hkbirwtbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahhk'];
// }



// #sumber 1
// $str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,hk 
	// from  ".$dbname.".kebun_hk_panen_vw where divisi like '".$kdorg."%' and tanggal between '".$tgl1."' and '".$tgl2."' and jurnal=1";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$hkbipnnsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['hk'];
// }

// #sumber2
// $str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,jumlahhk 
	// from  ".$dbname.".kebun_perawatan_vw where unit = '".$kdorg."' and tanggal like '".$per2."%'
	// and jurnal=1 and left(noakun,3)='611' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$hkbipnnsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahhk'];
// }

 

// $str=" select substr(kodeorg,1,6) as divisi,kodeorg,tahuntanam,jumlahhk 
	// from  ".$dbname.".kebun_perawatan_vw where unit = '".$kdorg."' and tanggal between '".$tgl1."' and '".$tgl2."'
	// and jurnal=1 and left(noakun,3)!='611' ";

// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
    // @$hkbirwtsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahhk'];
// }


// #BGT
// $kuncipnn="(";
// $str="SELECT distinct a.kodeorg,substr(a.kodeorg,1,6) as divisi, a.kodebudget, a.kegiatan, a.jumlah, a.satuanj,a.kunci,b.thntnm
		// FROM ".$dbname.".bgt_budget a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok
		// where a.kodebudget in('SDM-PHL','SDM-KHT','SDM-KBL')
		// and a.kodeorg like '".$kdorg."%' and a.satuanj='HK'  and (a.kegiatan='611010101' or a.kegiatan='611020201')  and  a.tahunbudget='".$tahun."'";

	
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $row=owlBaris($res);
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
	// @$rowkuncipnn+=1;
	// @$hkpnnbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['jumlah'];
	// if($rowkuncipnn==$row)
	// {
		// $kuncipnn.="'".$bar['kunci']."'";
	// }
	// else
	// {
		// $kuncipnn.="'".$bar['kunci']."',";
	// }
// }
// $kuncipnn.=")";


// $kuncirwt="(";
// $str="SELECT distinct a.kodeorg,substr(a.kodeorg,1,6) as divisi, a.kodebudget, a.kegiatan, a.jumlah, a.satuanj,a.kunci,b.thntnm
		// FROM ".$dbname.".bgt_budget a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok
		// where a.kodebudget in('SDM-PHL','SDM-KHT','SDM-KBL')
		// and a.kodeorg like '".$kdorg."%' and a.satuanj='HK' and  a.tahunbudget='".$tahun."'
		// and (a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280199')) ";

	
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $row=owlBaris($res);
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
	// @$rowkuncirwt+=1;
	// @$hkrwtbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['jumlah'];
	// if($rowkuncirwt==$row)
	// {
		// $kuncirwt.="'".$bar['kunci']."'";
	// }
	// else
	// {
		// $kuncirwt.="'".$bar['kunci']."',";
	// }
// }
// $kuncirwt.=")";





// $addstr="(";
// for($i=1;$i<=intval($blnbgt);$i++)
// {
    // if($i<10)
    // {
        // $isi="rp0".$i;
    // }
    // else 
    // {
        // $isi="rp".$i;
    // }
    // if($i<intval($blnbgt))
    // {
        // $addstr.=$isi."+";
    // }
    // else
    // {
        // $addstr.=$isi;
    // }
// }
// $addstr.=")";



// $addstrthn="(";
// for($i=1;$i<=12;$i++)
// {
    // if($i<10)
    // {
        // $isi="rp0".$i;
    // }
    // else 
    // {
        // $isi="rp".$i;
    // }
    // if($i<12)
    // {
        // $addstrthn.=$isi."+";
    // }
    // else
    // {
        // $addstrthn.=$isi;
    // }
// }
// $addstrthn.=")";


// if($kuncipnn=='()')
// {
	// $kuncipnn="('')";
// }


// if($kuncirwt=='()')
// {
	// $kuncirwt="('')";
// }

// $kuncipnn=str_replace(',)', ')', $kuncipnn);
// $kuncirwt=str_replace(',)', ')', $kuncirwt);

// $str="select b.thntnm,a.kodeorg,substr(a.kodeorg,1,6) as divisi,rp".$blnbgt." as rpbi,".$addstr." as rpsdbi,".$addstrthn." as rpthn 
		// from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok "
        // . " where a.tahunbudget='".$tahun."' and a.kodeorg like '".$kdorg."%' and a.kunci in ".$kuncipnn." "; 		
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
	// @$rppnnbgtbi[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpbi'];
	// @$rppnnbgtsdbi[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpsdbi'];
	// @$rppnnbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpthn'];
// }


// $str="select b.thntnm,a.kodeorg,substr(a.kodeorg,1,6) as divisi,rp".$blnbgt." as rpbi,".$addstr." as rpsdbi,".$addstrthn." as rpthn 
		// from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok "
        // . " where a.tahunbudget='".$tahun."' and a.kodeorg like '".$kdorg."%' and a.kunci in ".$kuncirwt." "; 		

// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
	// @$rprwtbgtbi[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpbi'];
	// @$rprwtbgtsdbi[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpsdbi'];
	// @$rprwtbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeorg']]+=$bar['rpthn'];
// }



// //$romawi=array("1"=>"I","2"=>"II","3"=>"III","4"=>"IV","5"=>"V","6"=>"VI","7"=>"VII","8"=>"VIII",,"9"=>"IX");

// array_multisort($kddivisi,SORT_ASC);
// array_multisort($tahuntanam,SORT_ASC);
// array_multisort($kdblok,SORT_ASC);

// foreach($kddivisi as $divisi)
// {
    // foreach($tahuntanam as $thntnm)
    // {
        // if(@$listtahuntanam[$divisi][$thntnm]!='')
        // {
            // foreach($kdblok as $blok)
            // {
                // if(@$listblok[$divisi][$thntnm][$blok]!='')
                // {
					
					// @$hkbibgtpnn[$divisi][$thntnm][$blok]=($rppnnbgtbi[$divisi][$thntnm][$blok]/$rppnnbgtthn[$divisi][$thntnm][$blok])*$hkpnnbgtthn[$divisi][$thntnm][$blok];
					// @$hkbibgtrwt[$divisi][$thntnm][$blok]=($rprwtbgtbi[$divisi][$thntnm][$blok]/$rprwtbgtthn[$divisi][$thntnm][$blok])*$hkrwtbgtthn[$divisi][$thntnm][$blok];
					
					// @$hksdbibgtpnn[$divisi][$thntnm][$blok]=($rppnnbgtsdbi[$divisi][$thntnm][$blok]/$rppnnbgtthn[$divisi][$thntnm][$blok])*$hkpnnbgtthn[$divisi][$thntnm][$blok];
					// @$hksdbibgtrwt[$divisi][$thntnm][$blok]=($rprwtbgtsdbi[$divisi][$thntnm][$blok]/$rprwtbgtthn[$divisi][$thntnm][$blok])*$hkrwtbgtthn[$divisi][$thntnm][$blok];
					
					// @$no+=1;
                    // $stream.="
                    // <tr class=rowcontent style=cursor:pointer; title='click detail' onclick=html2('".$listblok[$divisi][$thntnm][$blok]."','".$per2."')>
                        // <td align=center>".$no."</td>
                        // <td align=center>".(substr($divisi,4,2))."</td>
                        // <td align=center>".$listblok[$divisi][$thntnm][$blok]."</td>    
                        // <td align=center>".$listtahuntanam[$divisi][$thntnm]."</td>
                        // <td align=right>".@number_format($luas[$divisi][$thntnm][$blok],2)."</td>     
                        // <td>".$jenisbibit[$divisi][$thntnm][$blok]."</td>     
                        // <td align=left>".$status[$divisi][$thntnm][$blok]."</td>       
                        // <td align=right>".@number_format($prdbi[$divisi][$thntnm][$blok],2)."</td>    
                        // <td align=right>".@number_format($prdbibgt[$divisi][$thntnm][$blok],2)."</td>    
                        // <td align=right>".@number_format($prdsdbi[$divisi][$thntnm][$blok],2)."</td>
                        // <td align=right>".@number_format($prdsdbibgt[$divisi][$thntnm][$blok],2)."</td>   
                        // <td align=right>".@number_format($prdsetahunbgt[$divisi][$thntnm][$blok],2)."</td>   
						
						// <td align=right>".@number_format($hkbipnnbi[$divisi][$thntnm][$blok],2)."</td>  
						// <td align=right>".@number_format($hkbirwtbi[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkbipnnbi[$divisi][$thntnm][$blok]+$hkbirwtbi[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkbibgtpnn[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkbibgtrwt[$divisi][$thntnm][$blok],2)."</td>						
						// <td align=right>".@number_format($hkbibgtpnn[$divisi][$thntnm][$blok]+$hkbibgtrwt[$divisi][$thntnm][$blok],2)."</td>
						
						// <td align=right>".@number_format($hkbipnnsdbi[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkbirwtsdbi[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkbipnnsdbi[$divisi][$thntnm][$blok]+$hkbirwtsdbi[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hksdbibgtpnn[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hksdbibgtrwt[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hksdbibgtpnn[$divisi][$thntnm][$blok]+$hksdbibgtrwt[$divisi][$thntnm][$blok],2)."</td>
						
						// <td align=right>".@number_format($hkpnnbgtthn[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkrwtbgtthn[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkpnnbgtthn[$divisi][$thntnm][$blok]+$hkrwtbgtthn[$divisi][$thntnm][$blok],2)."</td>
						
                        // <td align=right>".@number_format($bybipnn[$divisi][$thntnm][$blok])."</td>
                        // <td align=right>".@number_format($bybirwt[$divisi][$thntnm][$blok])."</td>    
                        // <td align=right>".@number_format($bybipnn[$divisi][$thntnm][$blok]+$bybirwt[$divisi][$thntnm][$blok])."</td>                             
                        // <td align=right>".@number_format($bybibgtpnn[$divisi][$thntnm][$blok])."</td>   
                        // <td align=right>".@number_format($bybibgtrwt[$divisi][$thntnm][$blok])."</td>       
                        // <td align=right>".@number_format($bybibgtpnn[$divisi][$thntnm][$blok]+$bybibgtrwt[$divisi][$thntnm][$blok])."</td>      
                        

                        // <td align=right>".@number_format($bysdbipnn[$divisi][$thntnm][$blok])."</td>  
                        // <td align=right>".@number_format($bysdbirwt[$divisi][$thntnm][$blok])."</td> 
                        // <td align=right>".@number_format($bysdbipnn[$divisi][$thntnm][$blok]+$bysdbirwt[$divisi][$thntnm][$blok])."</td>                  
                        // <td align=right>".@number_format($bysdbibgtpnn[$divisi][$thntnm][$blok])."</td>  
                        // <td align=right>".@number_format($bysdbibgtrwt[$divisi][$thntnm][$blok])."</td>  
                        // <td align=right>".@number_format($bysdbibgtpnn[$divisi][$thntnm][$blok]+$bysdbibgtrwt[$divisi][$thntnm][$blok])."</td>      
                        
                        // <td align=right>".@number_format($bybgtsetahunpnn[$divisi][$thntnm][$blok])."</td>  
                        // <td align=right>".@number_format($bybgtsetahunrwt[$divisi][$thntnm][$blok])."</td>  
                        // <td align=right>".@number_format($bybgtsetahunpnn[$divisi][$thntnm][$blok]+$bybgtsetahunrwt[$divisi][$thntnm][$blok])."</td>      
                        
						// <td align=right>".@number_format($bysdbipnn[$divisi][$thntnm][$blok]/$prdsdbi[$divisi][$thntnm][$blok],2)."</td>  
						// <td align=right>".@number_format($bysdbirwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok])."</td>
						
						// <td align=right>".@number_format($bysdbibgtpnn[$divisi][$thntnm][$blok]/$prdsdbibgt[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($bysdbibgtrwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok])."</td>	
						
						// <td align=right>".@number_format($bybgtsetahunpnn[$divisi][$thntnm][$blok]/$prdsetahunbgt[$divisi][$thntnm][$blok],2)."</td>	
						// <td align=right>".@number_format($bybgtsetahunrwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok])."</td>	
						
						// <td align=right>".@number_format($hkbipnnbi[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>  
						// <td align=right>".@number_format($hkbirwtbi[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format(($hkbipnnbi[$divisi][$thntnm][$blok]+$hkbirwtbi[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkbibgtpnn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkbibgtrwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>						
						// <td align=right>".@number_format(($hkbibgtpnn[$divisi][$thntnm][$blok]+$hkbibgtrwt[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok],2)."</td>
						
						// <td align=right>".@number_format($hkbipnnsdbi[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkbirwtsdbi[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format(($hkbipnnsdbi[$divisi][$thntnm][$blok]+$hkbirwtsdbi[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hksdbibgtpnn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hksdbibgtrwt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format(($hksdbibgtpnn[$divisi][$thntnm][$blok]+$hksdbibgtrwt[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok],2)."</td>
						
						// <td align=right>".@number_format($hkpnnbgtthn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format($hkrwtbgtthn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
						// <td align=right>".@number_format(($hkpnnbgtthn[$divisi][$thntnm][$blok]+$hkrwtbgtthn[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok],2)."</td>
                    
                    // </tr>";
                    // @$luastt[$divisi][$thntnm]+=$luas[$divisi][$thntnm][$blok];
                    // @$prdbitt[$divisi][$thntnm]+=$prdbi[$divisi][$thntnm][$blok];
                    // @$prdbibgttt[$divisi][$thntnm]+=$prdbibgt[$divisi][$thntnm][$blok];
                    // @$prdsdbitt[$divisi][$thntnm]+=$prdsdbi[$divisi][$thntnm][$blok];
                    // @$prdsdbibgttt[$divisi][$thntnm]+=$prdsdbibgt[$divisi][$thntnm][$blok];
                    // @$prdsetahunbgttt[$divisi][$thntnm]+=$prdsetahunbgt[$divisi][$thntnm][$blok];
					
					
					// @$hkbipnnbitt[$divisi][$thntnm]+=$hkbipnnbi[$divisi][$thntnm][$blok];
					// @$hkbirwtbitt[$divisi][$thntnm]+=$hkbirwtbi[$divisi][$thntnm][$blok];
					// @$hkbipnnsdbitt[$divisi][$thntnm]+=$hkbipnnsdbi[$divisi][$thntnm][$blok];
					// @$hkbirwtsdbitt[$divisi][$thntnm]+=$hkbirwtsdbi[$divisi][$thntnm][$blok];	
					
					
					// @$hkbibgtpnntt[$divisi][$thntnm]+=$hkbibgtpnn[$divisi][$thntnm][$blok];
					// @$hkbibgtrwttt[$divisi][$thntnm]+=$hkbibgtrwt[$divisi][$thntnm][$blok];
					// @$hksdbibgtpnntt[$divisi][$thntnm]+=$hksdbibgtpnn[$divisi][$thntnm][$blok];
					// @$hksdbibgtrwttt[$divisi][$thntnm]+=$hksdbibgtrwt[$divisi][$thntnm][$blok];
					
					// @$hkpnnbgtthntt[$divisi][$thntnm]+=$hkpnnbgtthn[$divisi][$thntnm][$blok];
					// @$hkrwtbgtthntt[$divisi][$thntnm]+=$hkrwtbgtthn[$divisi][$thntnm][$blok];
					
					
                    // @$bybipnntt[$divisi][$thntnm]+=$bybipnn[$divisi][$thntnm][$blok];
                    // @$bybirwttt[$divisi][$thntnm]+=$bybirwt[$divisi][$thntnm][$blok];
                    // @$bybibgtpnntt[$divisi][$thntnm]+=$bybibgtpnn[$divisi][$thntnm][$blok];
                    // @$bybibgtrwttt[$divisi][$thntnm]+=$bybibgtrwt[$divisi][$thntnm][$blok];
                    
                    // @$bysdbipnntt[$divisi][$thntnm]+=$bysdbipnn[$divisi][$thntnm][$blok];
                    // @$bysdbirwttt[$divisi][$thntnm]+=$bysdbirwt[$divisi][$thntnm][$blok];
                    // @$bysdbibgtpnntt[$divisi][$thntnm]+=$bysdbibgtpnn[$divisi][$thntnm][$blok];
                    // @$bysdbibgtrwttt[$divisi][$thntnm]+=$bysdbibgtrwt[$divisi][$thntnm][$blok];
                    
                    // @$bybgtsetahunpnntt[$divisi][$thntnm]+=$bybgtsetahunpnn[$divisi][$thntnm][$blok];
                    // @$bybgtsetahunrwttt[$divisi][$thntnm]+=$bybgtsetahunrwt[$divisi][$thntnm][$blok];
                    
                // }
            // }
            // #subtotal tt
            // $stream.="
                // <tr  bgcolor=#80FFFE>
                    // <td colspan=4>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['tahuntanam']."  ".$thntnm."</td>
                    // <td align=right>".@number_format($luastt[$divisi][$thntnm],2)."</td>
                    // <td align=center></td>   
                    // <td align=center></td>   
                    // <td align=right>".@number_format($prdbitt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($prdbibgttt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($prdsdbitt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($prdsdbibgttt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($prdsetahunbgttt[$divisi][$thntnm],2)."</td>
					
					
                    // <td align=right>".@number_format($hkbipnnbitt[$divisi][$thntnm],2)."</td>  
                    // <td align=right>".@number_format($hkbirwtbitt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbipnnbitt[$divisi][$thntnm]+$hkbirwtbitt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbibgtpnntt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbibgtrwttt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbibgtpnntt[$divisi][$thntnm]+$hkbibgtrwttt[$divisi][$thntnm],2)."</td>

                    // <td align=right>".@number_format($hkbipnnsdbitt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbirwtsdbitt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbipnnsdbitt[$divisi][$thntnm]+$hkbirwtsdbitt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hksdbibgtpnntt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hksdbibgtrwttt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hksdbibgtpnntt[$divisi][$thntnm]+$hksdbibgtrwttt[$divisi][$thntnm],2)."</td>
					
                    // <td align=right>".@number_format($hkpnnbgtthntt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkrwtbgtthntt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkpnnbgtthntt[$divisi][$thntnm]+$hkrwtbgtthntt[$divisi][$thntnm],2)."</td>
						
                        
                    // <td align=right>".@number_format($bybipnntt[$divisi][$thntnm])."</td>
                    // <td align=right>".@number_format($bybirwttt[$divisi][$thntnm])."</td>    
                    // <td align=right>".@number_format($bybipnntt[$divisi][$thntnm]+$bybirwttt[$divisi][$thntnm])."</td>   
                    // <td align=right>".@number_format($bybibgtpnntt[$divisi][$thntnm])."</td>
                    // <td align=right>".@number_format($bybibgtrwttt[$divisi][$thntnm])."</td>
                    // <td align=right>".@number_format($bybibgtpnntt[$divisi][$thntnm]+$bybibgtrwttt[$divisi][$thntnm])."</td>        
                    
                    // <td align=right>".@number_format($bysdbipnntt[$divisi][$thntnm])."</td>
                    // <td align=right>".@number_format($bysdbirwttt[$divisi][$thntnm])."</td>    
                    // <td align=right>".@number_format($bysdbipnntt[$divisi][$thntnm]+$bysdbirwttt[$divisi][$thntnm])."</td>  
                    // <td align=right>".@number_format($bysdbibgtpnntt[$divisi][$thntnm])."</td>
                    // <td align=right>".@number_format($bysdbibgtrwttt[$divisi][$thntnm])."</td>    
                    // <td align=right>".@number_format($bysdbibgtpnntt[$divisi][$thntnm]+$bysdbibgtrwttt[$divisi][$thntnm])."</td>  
                    
                    // <td align=right>".@number_format($bybgtsetahunpnntt[$divisi][$thntnm])."</td>
                    // <td align=right>".@number_format($bybgtsetahunrwttt[$divisi][$thntnm])."</td>    
                    // <td align=right>".@number_format($bybgtsetahunpnntt[$divisi][$thntnm]+$bybgtsetahunrwttt[$divisi][$thntnm])."</td>  
					
                    // <td align=right>".@number_format($bysdbipnntt[$divisi][$thntnm]/$prdsdbitt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($bysdbirwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm])."</td>

                    // <td align=right>".@number_format($bysdbibgtpnntt[$divisi][$thntnm]/$prdsdbibgttt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($bysdbibgtrwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm])."</td>	
					
                    // <td align=right>".@number_format($bybgtsetahunpnntt[$divisi][$thntnm]/$prdsetahunbgttt[$divisi][$thntnm],2)."</td>	
                    // <td align=right>".@number_format($bybgtsetahunrwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm])."</td>	 


					// <td align=right>".@number_format($hkbipnnbitt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>  
                    // <td align=right>".@number_format($hkbirwtbitt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format(($hkbipnnbitt[$divisi][$thntnm]+$hkbirwtbitt[$divisi][$thntnm])/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbibgtpnntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbibgtrwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format(($hkbibgtpnntt[$divisi][$thntnm]+$hkbibgtrwttt[$divisi][$thntnm])/$luastt[$divisi][$thntnm],2)."</td>

                    // <td align=right>".@number_format($hkbipnnsdbitt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkbirwtsdbitt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format(($hkbipnnsdbitt[$divisi][$thntnm]+$hkbirwtsdbitt[$divisi][$thntnm])/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hksdbibgtpnntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hksdbibgtrwttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format(($hksdbibgtpnntt[$divisi][$thntnm]+$hksdbibgtrwttt[$divisi][$thntnm])/$luastt[$divisi][$thntnm],2)."</td>
					
                    // <td align=right>".@number_format($hkpnnbgtthntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format($hkrwtbgtthntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                    // <td align=right>".@number_format(($hkpnnbgtthntt[$divisi][$thntnm]+$hkrwtbgtthntt[$divisi][$thntnm])/$luastt[$divisi][$thntnm],2)."</td>	
					
                // </tr>";        
            // @$luasdiv[$divisi]+=$luastt[$divisi][$thntnm];
            // @$prdbidiv[$divisi]+=$prdbitt[$divisi][$thntnm];
            // @$prdbibgtdiv[$divisi]+=$prdbibgttt[$divisi][$thntnm];
            // @$prdsdbidiv[$divisi]+=$prdsdbitt[$divisi][$thntnm];
            // @$prdsdbibgtdiv[$divisi]+=$prdsdbibgttt[$divisi][$thntnm];
            // @$prdsetahunbgtdiv[$divisi]+=$prdsetahunbgttt[$divisi][$thntnm];
			
            // @$hkbipnnbidiv[$divisi]+=$hkbipnnbitt[$divisi][$thntnm];
            // @$hkbirwtbidiv[$divisi]+=$hkbirwtbitt[$divisi][$thntnm];
            // @$hkbipnnsdbidiv[$divisi]+=$hkbipnnsdbitt[$divisi][$thntnm];
            // @$hkbirwtsdbidiv[$divisi]+=$hkbirwtsdbitt[$divisi][$thntnm];

            // @$hkbibgtpnndiv[$divisi]+=$hkbibgtpnntt[$divisi][$thntnm];
            // @$hkbibgtrwtdiv[$divisi]+=$hkbibgtrwttt[$divisi][$thntnm];
            // @$hksdbibgtpnndiv[$divisi]+=$hksdbibgtpnntt[$divisi][$thntnm];
            // @$hksdbibgtrwtdiv[$divisi]+=$hksdbibgtrwttt[$divisi][$thntnm];

            // @$hkpnnbgtthndiv[$divisi]+=$hkpnnbgtthntt[$divisi][$thntnm];
            // @$hkrwtbgtthndiv[$divisi]+=$hkrwtbgtthntt[$divisi][$thntnm];
			
            // @$bybipnndiv[$divisi]+=$bybipnntt[$divisi][$thntnm];
            // @$bybirwtdiv[$divisi]+=$bybirwttt[$divisi][$thntnm];
            // @$bybibgtpnndiv[$divisi]+=$bybibgtpnntt[$divisi][$thntnm];
            // @$bybibgtrwtdiv[$divisi]+=$bybibgtrwttt[$divisi][$thntnm];
            
            // @$bysdbipnndiv[$divisi]+=$bysdbipnntt[$divisi][$thntnm];
            // @$bysdbirwtdiv[$divisi]+=$bysdbirwttt[$divisi][$thntnm];
            // @$bysdbibgtpnndiv[$divisi]+=$bysdbibgtpnntt[$divisi][$thntnm];
            // @$bysdbibgtrwtdiv[$divisi]+=$bysdbibgtrwttt[$divisi][$thntnm];
            
            // @$bybgtsetahunpnndiv[$divisi]+=$bybgtsetahunpnntt[$divisi][$thntnm];
            // @$bybgtsetahunrwtdiv[$divisi]+=$bybgtsetahunrwttt[$divisi][$thntnm];
            
        // }
    // }
    
    // $stream.="
        // <tr bgcolor=#48D1CC>
            // <td align=left colspan=4>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['divisi']." ".$divisi."</td>
            // <td align=right>".@number_format($luasdiv[$divisi],2)."</td>
            // <td align=center></td>       
            // <td align=center></td>       
            // <td align=right>".@number_format($prdbidiv[$divisi],2)."</td>
            // <td align=right>".@number_format($prdbibgtdiv[$divisi],2)."</td>  
            // <td align=right>".@number_format($prdsdbidiv[$divisi],2)."</td>
            // <td align=right>".@number_format($prdsdbibgtdiv[$divisi],2)."</td>  
            // <td align=right>".@number_format($prdsetahunbgtdiv[$divisi],2)."</td>
			
			// <td align=right>".@number_format($hkbipnnbidiv[$divisi],2)."</td>  
			// <td align=right>".@number_format($hkbirwtbidiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbipnnbidiv[$divisi]+$hkbirwtbidiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbibgtpnndiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbibgtrwtdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbibgtpnndiv[$divisi]+$hkbibgtrwtdiv[$divisi],2)."</td>
			
			// <td align=right>".@number_format($hkbipnnsdbidiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbirwtsdbidiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbipnnsdbidiv[$divisi]+$hkbirwtsdbidiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hksdbibgtpnndiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hksdbibgtrwtdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hksdbibgtpnndiv[$divisi]+$hksdbibgtrwtdiv[$divisi],2)."</td>
			
			// <td align=right>".@number_format($hkpnnbgtthndiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkrwtbgtthndiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkpnnbgtthndiv[$divisi]+$hkrwtbgtthndiv[$divisi],2)."</td>
						
            // <td align=right>".@number_format($bybipnndiv[$divisi])."</td>  
            // <td align=right>".@number_format($bybirwtdiv[$divisi])."</td>  
            // <td align=right>".@number_format($bybipnndiv[$divisi]+$bybirwtdiv[$divisi])."</td>   
            // <td align=right>".@number_format($bybibgtpnndiv[$divisi])."</td>  
            // <td align=right>".@number_format($bybibgtrwtdiv[$divisi])."</td>   
            // <td align=right>".@number_format($bybibgtpnndiv[$divisi]+$bybibgtrwtdiv[$divisi])."</td>   
                
            // <td align=right>".@number_format($bysdbipnndiv[$divisi])."</td>  
            // <td align=right>".@number_format($bysdbirwtdiv[$divisi])."</td>  
            // <td align=right>".@number_format($bysdbipnndiv[$divisi]+$bysdbirwtdiv[$divisi])."</td> 
            // <td align=right>".@number_format($bysdbibgtpnndiv[$divisi])."</td>  
            // <td align=right>".@number_format($bysdbibgtrwtdiv[$divisi])."</td>  
            // <td align=right>".@number_format($bysdbibgtpnndiv[$divisi]+$bysdbibgtrwtdiv[$divisi])."</td>  
                
            // <td align=right>".@number_format($bybgtsetahunpnndiv[$divisi])."</td>  
            // <td align=right>".@number_format($bybgtsetahunrwtdiv[$divisi])."</td>  
            // <td align=right>".@number_format($bybgtsetahunpnndiv[$divisi]+$bybgtsetahunrwtdiv[$divisi])."</td> 
			
			// <td align=right>".@number_format($bysdbipnndiv[$divisi]/$prdsdbidiv[$divisi],2)."</td>
			// <td align=right>".@number_format($bysdbirwtdiv[$divisi]/$luasdiv[$divisi])."</td>
			
			// <td align=right>".@number_format($bysdbibgtpnndiv[$divisi]/$prdsdbibgtdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($bysdbibgtrwtdiv[$divisi]/$luasdiv[$divisi])."</td>	
			
			// <td align=right>".@number_format($bybgtsetahunpnndiv[$divisi]/$prdsetahunbgtdiv[$divisi],2)."</td>	
			// <td align=right>".@number_format($bybgtsetahunrwtdiv[$divisi]/$luasdiv[$divisi])."</td>	 

			
			
			// <td align=right>".@number_format($hkbipnnbidiv[$divisi]/$luasdiv[$divisi],2)."</td>  
			// <td align=right>".@number_format($hkbirwtbidiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format(($hkbipnnbidiv[$divisi]+$hkbirwtbidiv[$divisi])/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbibgtpnndiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbibgtrwtdiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format(($hkbibgtpnndiv[$divisi]+$hkbibgtrwtdiv[$divisi])/$luasdiv[$divisi],2)."</td>
			
			// <td align=right>".@number_format($hkbipnnsdbidiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkbirwtsdbidiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format(($hkbipnnsdbidiv[$divisi]+$hkbirwtsdbidiv[$divisi])/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hksdbibgtpnndiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hksdbibgtrwtdiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format(($hksdbibgtpnndiv[$divisi]+$hksdbibgtrwtdiv[$divisi])/$luasdiv[$divisi],2)."</td>
			
			// <td align=right>".@number_format($hkpnnbgtthndiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format($hkrwtbgtthndiv[$divisi]/$luasdiv[$divisi],2)."</td>
			// <td align=right>".@number_format(($hkpnnbgtthndiv[$divisi]+$hkrwtbgtthndiv[$divisi])/$luasdiv[$divisi],2)."</td>
			

        // </tr>";
    // @$gtluas+=$luasdiv[$divisi];
    // @$gtprdbi+=$prdbidiv[$divisi];
    // @$gtprdbibgt+=$prdbibgtdiv[$divisi];
    // @$gtprdsdbi+=$prdsdbidiv[$divisi];
    // @$gtprdsdbibgt+=$prdsdbibgtdiv[$divisi];
    // @$gtprdsetahunbgt+=$prdsetahunbgtdiv[$divisi]; 
	
	
	// @$gthkbipnnbi+=$hkbipnnbidiv[$divisi];
	// @$gthkbirwtbi+=$hkbirwtbidiv[$divisi];
	// @$gthkbipnnsdbi+=$hkbipnnsdbidiv[$divisi];
	// @$gthkbirwtsdbi+=$hkbirwtsdbidiv[$divisi];
	
	
	// @$gthkbibgtpnn+=$hkbibgtpnndiv[$divisi];
	// @$gthkbibgtrwt+=$hkbibgtrwtdiv[$divisi];
	// @$gthksdbibgtpnn+=$hksdbibgtpnndiv[$divisi];
	// @$gthksdbibgtrwt+=$hksdbibgtrwtdiv[$divisi];
	
	// @$gthkpnnbgtthn+=$hkpnnbgtthndiv[$divisi];
	// @$gthkrwtbgtthn+=$hkrwtbgtthndiv[$divisi];
	
	
	
    // @$gtbybipnn+=$bybipnndiv[$divisi];
    // @$gtbybirwt+=$bybirwtdiv[$divisi];
    // @$gtbybibgtpnn+=$bybibgtpnndiv[$divisi];
    // @$gtbybibgtrwt+=$bybibgtrwtdiv[$divisi];
    
    // @$gtbysdbipnn+=$bysdbipnndiv[$divisi];
    // @$gtbysdbirwt+=$bysdbirwtdiv[$divisi];
    // @$gtbysdbibgtpnn+=$bysdbibgtpnndiv[$divisi];
    // @$gtbysdbibgtrwt+=$bysdbibgtrwtdiv[$divisi];
    
    // @$gtbybgtsetahunpnn+=$bybgtsetahunpnndiv[$divisi];
    // @$gtbybgtsetahunrwt+=$bybgtsetahunrwtdiv[$divisi];
// }
// $stream.="
        // <tr bgcolor=#009999>
            // <td align=left colspan=4>".$_SESSION['lang']['grnd_total']." ".$kdorg."</td>
            // <td align=right>".@number_format($gtluas,2)."</td>
            // <td align=center></td>   
            // <td align=center></td>   
            // <td align=right>".@number_format($gtprdbi,2)."</td>
            // <td align=right>".@number_format($gtprdbibgt,2)."</td>
            // <td align=right>".@number_format($gtprdsdbi,2)."</td>
            // <td align=right>".@number_format($gtprdsdbibgt,2)."</td>
            // <td align=right>".@number_format($gtprdsetahunbgt,2)."</td>
			
			// <td align=right>".@number_format($gthkbipnnbi,2)."</td>  
			// <td align=right>".@number_format($gthkbirwtbi,2)."</td>
			// <td align=right>".@number_format($gthkbipnnbi+$gthkbirwtbi,2)."</td>
			// <td align=right>".@number_format($gthkbibgtpnn,2)."</td>
			// <td align=right>".@number_format($gthkbibgtrwt,2)."</td>
			// <td align=right>".@number_format($gthkbibgtpnn+$gthkbibgtrwt,2)."</td>
			
			// <td align=right>".@number_format($gthkbipnnsdbi,2)."</td>
			// <td align=right>".@number_format($gthkbirwtsdbi,2)."</td>
			// <td align=right>".@number_format($gthkbipnnsdbi+$gthkbirwtsdbi,2)."</td>
			// <td align=right>".@number_format($gthksdbibgtpnn,2)."</td>
			// <td align=right>".@number_format($gthksdbibgtrwt,2)."</td>
			// <td align=right>".@number_format($gthksdbibgtpnn+$gthksdbibgtrwt,2)."</td>
			
			// <td align=right>".@number_format($gthkpnnbgtthn,2)."</td>
			// <td align=right>".@number_format($gthkrwtbgtthn,2)."</td>
			// <td align=right>".@number_format($gthkpnnbgtthn+$gthkrwtbgtthn,2)."</td>
			
                
            // <td align=right>".@number_format($gtbybipnn)."</td>
            // <td align=right>".@number_format($gtbybirwt)."</td>    
            // <td align=right>".@number_format($gtbybipnn+$gtbybirwt)."</td>        
            // <td align=right>".@number_format($gtbybibgtpnn)."</td>
            // <td align=right>".@number_format($gtbybibgtrwt)."</td>    
            // <td align=right>".@number_format($gtbybibgtpnn+$gtbybibgtrwt)."</td>      
                
            // <td align=right>".@number_format($gtbysdbipnn)."</td>
            // <td align=right>".@number_format($gtbysdbirwt)."</td>    
            // <td align=right>".@number_format($gtbysdbipnn+$gtbysdbirwt)."</td>        
            // <td align=right>".@number_format($gtbysdbibgtpnn)."</td>
            // <td align=right>".@number_format($gtbysdbibgtrwt)."</td>    
            // <td align=right>".@number_format($gtbysdbibgtpnn+$gtbysdbibgtrwt)."</td>    
                
            // <td align=right>".@number_format($gtbybgtsetahunpnn)."</td>
            // <td align=right>".@number_format($gtbybgtsetahunrwt)."</td>    
            // <td align=right>".@number_format($gtbybgtsetahunpnn+$gtbybgtsetahunrwt)."</td> 
			
			// <td align=right>".@number_format($gtbysdbipnn/$gtprdsdbi,2)."</td>
			// <td align=right>".@number_format($gtbysdbirwt/$gtluas)."</td>
			// <td align=right>".@number_format($gtbysdbibgtpnn/$gtprdsdbibgt,2)."</td>
			// <td align=right>".@number_format($gtbysdbibgtrwt/$gtluas)."</td>	
			// <td align=right>".@number_format($gtbybgtsetahunpnn/$gtprdsetahunbgt,2)."</td>	
			// <td align=right>".@number_format($gtbybgtsetahunrwt/$gtluas)."</td>	
            
			
			// <td align=right>".@number_format($gthkbipnnbi/$gtluas,2)."</td>  
			// <td align=right>".@number_format($gthkbirwtbi/$gtluas,2)."</td>
			// <td align=right>".@number_format(($gthkbipnnbi+$gthkbirwtbi)/$gtluas,2)."</td>
			// <td align=right>".@number_format($gthkbibgtpnn/$gtluas,2)."</td>
			// <td align=right>".@number_format($gthkbibgtrwt/$gtluas,2)."</td>
			// <td align=right>".@number_format(($gthkbibgtpnn+$gthkbibgtrwt)/$gtluas,2)."</td>
			
			// <td align=right>".@number_format($gthkbipnnsdbi/$gtluas,2)."</td>
			// <td align=right>".@number_format($gthkbirwtsdbi/$gtluas,2)."</td>
			// <td align=right>".@number_format(($gthkbipnnsdbi+$gthkbirwtsdbi)/$gtluas,2)."</td>
			// <td align=right>".@number_format($gthksdbibgtpnn/$gtluas,2)."</td>
			// <td align=right>".@number_format($gthksdbibgtrwt/$gtluas,2)."</td>
			// <td align=right>".@number_format(($gthksdbibgtpnn+$gthksdbibgtrwt)/$gtluas,2)."</td>
			
			// <td align=right>".@number_format($gthkpnnbgtthn/$gtluas,2)."</td>
			// <td align=right>".@number_format($gthkrwtbgtthn/$gtluas,2)."</td>
			// <td align=right>".@number_format(($gthkpnnbgtthn+$gthkrwtbgtthn)/$gtluas,2)."</td>
			
        // </tr>";    

$stream.="
 </tbody>
     </table>";

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