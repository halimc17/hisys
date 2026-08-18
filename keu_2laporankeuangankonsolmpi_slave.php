<?php
ini_set('display_errors',0);
error_reporting(0);

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$param = $_POST;
$cparam=count($param);
if($cparam==0){
	$param=$_GET;
}

if($param['periode']=='' || $param['kodept']==''){
	exit("Warning:Periode / PT masih kosong");
}

#= ambil jumlah
$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodept']."'";
$res=fetchdata($str);
foreach($res as $bar){
	$namaorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

#= ambil unit inti / 
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res=fetchdata($str);
foreach($res as $bar){
	$unitinti[$bar['kodeorganisasi']]=$bar['inti'];
}

// echo"<pre>";
// print_r($unitinti);
// exit();

if($param['kodeunit']!=''){
	$whereunit=" and kodeorganisasi='".$param['kodeunit']."'";
}

$where=$whereinti=$whereintibgtproduksi='';

#= daftar unit dalam 1 pt
$where=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit.")";
$whereinti=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit."  and inti=1)";
$whereintibgtproduksikebun=" and substr(kodeunit,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit."  and inti=1)";
$whereintibgtblok=" and substr(kodeblok,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit."  and inti=1)";
$whereintibgtproduksipabrik=" and substr(millcode,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit."  and inti=1)";

$kodelaporan='MPI';

#= untuk judul laporan
$str="select * from ".$dbname.".keu_5mesinlaporanht where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	$judullaporan=$bar['ket1'];
}

$qwe        = explode('-',$param['periode']);
$tahun      = $qwe[0];
$tahunlalu  = $qwe[0]-1;
$periodlalu = $tahunlalu."-".$qwe[1];
$perlalu    = periodelalu($tahun.'-01');
$perlaludata=str_replace("-", "", $tahun.'-01'); 




#bentuk untuk bgt..
$blnbgt=$qwe[1];


// exit('error: '.$param['periode']);
#= bentuk array bulan
$arrayper = month_inbetween($tahun.'-01',$param['periode']);
// $arrperlalu            =month_inbetween($tahunlalu.'-01',$tahunlalu.'-12');
$count = 0;
$range = range(1,12);
foreach($range as $i){
	if($i<=intval($qwe[1])){
		$arrper[$tahun."-".addZero($i,2)]=$tahun."-".addZero($i,2);
		if($i==3){
			$arrper[$tahun."-".addZero($i,2)]=$tahun."-".addZero($i,2);
			$arrper[$tahun."-Q1"]=$tahun."-Q1";
		}if($i==6){
			$arrper[$tahun."-".addZero($i,2)]=$tahun."-".addZero($i,2);
			$arrper[$tahun."-Q2"]=$tahun."-Q2";
		}if($i==9){
			$arrper[$tahun."-".addZero($i,2)]=$tahun."-".addZero($i,2);
			$arrper[$tahun."-Q3"]=$tahun."-Q3";
		}if($i==12){
			$arrper[$tahun."-".addZero($i,2)]=$tahun."-".addZero($i,2);
			$arrper[$tahun."-Q4"]=$tahun."-Q4";
		}
	}
	
	$arrperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-".addZero($i,2);
	if($i==3){
		$arrperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-".addZero($i,2);
		$arrperlalu[$tahunlalu."-Q1"]=$tahunlalu."-Q1";
	}if($i==6){
		$arrperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-".addZero($i,2);
		$arrperlalu[$tahunlalu."-Q2"]=$tahunlalu."-Q2";
	}if($i==9){
		$arrperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-".addZero($i,2);
		$arrperlalu[$tahunlalu."-Q3"]=$tahunlalu."-Q3";
	}if($i==12){
		$arrperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-".addZero($i,2);
		$arrperlalu[$tahunlalu."-Q4"]=$tahunlalu."-Q4";
	}
	if($i<=3){
		$subper[$tahun."-".addZero($i,2)]=$tahun."-Q1";		
		$subperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-Q1";		
	}elseif($i>3 and $i<=6){
		$subper[$tahun."-".addZero($i,2)]=$tahun."-Q2";		
		$subperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-Q2";		
	}elseif($i>6 and $i<=9){
		$subper[$tahun."-".addZero($i,2)]=$tahun."-Q3";		
		$subperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-Q3";		
	}else{
		$subper[$tahun."-".addZero($i,2)]=$tahun."-Q4";		
		$subperlalu[$tahunlalu."-".addZero($i,2)]=$tahunlalu."-Q4";		
	}
	
	// $nomor=$i%3;
	// if($nomor==1){
		// $count=$count+1;
		// $datax["Q".$count][$tahun."-".addZero($i,2)]=$tahun."-".addZero($i,2);
	// }elseif($nomor==0){
		// $datax["Q".$count][$tahun."-".addZero($i,2)]=$tahun."-".addZero($i,2);
		// $datax["Q".$count][$tahun."-Q".$count]=$tahun."-Q".$count;
	// }else{
		// $datax["Q".$count][$tahun."-".addZero($i,2)]=$tahun."-".addZero($i,2);
	// }
}

array_push($arrper,$subper[$param['periode']]);

// foreach($arrper as $per){
	// echo $per."<br>";
// }
// echo"<pre>";
// print_r($arrper);
// exit();

$cspan                 = count($arrper)+count($arrperlalu)+11;
$nouruttemp            = '';
$daftarakun            = array();
$daftartotal           = array();
$jumlahdaftar          = array();
$jumlahdaftarkodejurnal= array();
$daftarakunkodejurnal  = array();

$pembagiton ='1000';
$pembagijuta='1000000';
$pembagiton ='1';
$pembagijuta='1';

#= push array per dengan tahun untuk budget
// $arrper[$tahun]=$tahun;
// $arrperlalu[$tahunlalu]=$tahunlalu;


#==== bentuk budget

$addstr="(";
$addstrbgtproduksikebun="(";
$addstrbgtproduksipabrik="(";
$addstrbgtproduksicpo="(";
$addstrbgtproduksipk="(";
for($i=1;$i<=intval($blnbgt);$i++) {
    if($i<10){
        $isi="rp0".$i;
        $isibgtproduksikebun="kg0".$i;
        $isibgtproduksipabrik="olah0".$i;
        $isibgtproduksicpo="kgcpo0".$i;
        $isibgtproduksipk="kgker0".$i;
    } else {
        $isi="rp".$i;
        $isibgtproduksikebun="kg".$i;
        $isibgtproduksipabrik="olah".$i;
        $isibgtproduksicpo="kgcpo".$i;
        $isibgtproduksipk="kgker".$i;
    }
    if($i<intval($blnbgt)) {
        $addstr.=$isi."+";
        $addstrbgtproduksikebun.=$isibgtproduksikebun."+";
        $addstrbgtproduksipabrik.=$isibgtproduksipabrik."+";
        $addstrbgtproduksicpo.=$isibgtproduksicpo."+";
        $addstrbgtproduksipk.=$isibgtproduksipk."+";
    } else {
        $addstr.=$isi;
        $addstrbgtproduksikebun.=$isibgtproduksikebun;
        $addstrbgtproduksipabrik.=$isibgtproduksipabrik;
        $addstrbgtproduksicpo.=$isibgtproduksicpo;
        $addstrbgtproduksipk.=$isibgtproduksipk;
    }
}
$addstr.=")";
$addstrbgtproduksikebun.=")";
$addstrbgtproduksipabrik.=")";
$addstrbgtproduksicpo.=")";
$addstrbgtproduksipk.=")";




$addstrthn="(";
$addstrthnbgtproduksikebun="(";
$addstrthnbgtproduksipabrik="(";
$addstrthnbgtproduksicpo="(";
$addstrthnbgtproduksipk="(";
for($i=1;$i<=12;$i++) {
    if($i<10) {
        $isi="rp0".$i;
        $isibgtproduksikebun="kg0".$i;
        $isibgtproduksipabrik="olah0".$i;
        $isibgtproduksicpo="kgcpo0".$i;
        $isibgtproduksipk="kgker0".$i;
    } else {
        $isi="rp".$i;
         $isibgtproduksikebun="kg".$i;
         $isibgtproduksipabrik="olah".$i;
         $isibgtproduksicpo="kgcpo".$i;
         $isibgtproduksipk="kgker".$i;
    } 
	if($i<12) {
        $addstrthn.=$isi."+";
        $addstrthnbgtproduksikebun.=$isibgtproduksikebun."+";
        $addstrthnbgtproduksipabrik.=$isibgtproduksipabrik."+";
        $addstrthnbgtproduksicpo.=$isibgtproduksicpo."+";
        $addstrthnbgtproduksipk.=$isibgtproduksipk."+";
    }  else {
        $addstrthn.=$isi;
        $addstrthnbgtproduksikebun.=$isibgtproduksikebun;
        $addstrthnbgtproduksipabrik.=$isibgtproduksipabrik;
        $addstrthnbgtproduksicpo.=$isibgtproduksicpo;
        $addstrthnbgtproduksipk.=$isibgtproduksipk;
    }
}
$addstrthn.=")";
$addstrthnbgtproduksikebun.=")";
$addstrthnbgtproduksipabrik.=")";
$addstrthnbgtproduksicpo.=")";
$addstrthnbgtproduksipk.=")";



#= budget produksi pks dan olah
$str="SELECT kodeunit,tahunbudget,
	
	sum".$addstrbgtproduksipabrik."/".$pembagiton." as tbsbi,
	sum".$addstrthnbgtproduksipabrik."/".$pembagiton." as tbsthn,
	
	sum".$addstrbgtproduksicpo."/".$pembagiton." as cpobi,
	sum".$addstrthnbgtproduksicpo."/".$pembagiton." as cpothn,
	
	sum".$addstrbgtproduksipk."/".$pembagiton." as pkbi,
	sum".$addstrthnbgtproduksipk."/".$pembagiton." as pkthn
	
	from ".$dbname.".bgt_produksi_pks_vw where 1=1 ".$whereintibgtproduksipabrik." and (tahunbudget='".$tahun."' or tahunbudget='".$tahunlalu."') group by kodeunit,tahunbudget ";
// echo $str;
// exit("");
$res=fetchdata($str);
foreach($res as $bar){
	#= data tahun ini
	if($bar['tahunbudget']==$tahun){
		#= masukan data
		#= tbs beli (kud dan external);
		if($unitinti[$bar['kodeunit']]=='0' || $bar['kodeunit']=='tbsexternal'){
			$dtthninibgt['41000']+=$bar['tbsthn'];
			$dtthninibgtytd['41000']+=$bar['tbsbi'];
		}
		if($unitinti[$bar['kodeunit']]=='1'){
			$dtthninibgt['43000']+=$bar['tbsthn'];
			$dtthninibgtytd['43000']+=$bar['tbsbi'];
		}
		
		$dtthninibgt['31000']+=$bar['cpothn'];
		$dtthninibgtytd['31000']+=$bar['cpobi'];
		$dtthninibgt['32000']+=$bar['pkthn'];
		$dtthninibgtytd['32000']+=$bar['pkbi'];
	}
	
	#= data tahun lalu
	if($bar['tahunbudget']==$tahunlalu){
		#= masukan data
		$dtthnlalubgt['31000']+=$bar['cpothn'];
		$dtthnlalubgtytd['31000']+=$bar['cpobi'];
		$dtthnlalubgt['32000']+=$bar['pkthn'];
		$dtthnlalubgtytd['32000']+=$bar['pkbi'];
		
		if($unitinti[$bar['kodeunit']]=='0' || $bar['kodeunit']=='tbsexternal'){
			$dtthnlalubgt['41000']+=$bar['tbsthn'];
			$dtthnlalubgtytd['41000']+=$bar['tbsbi'];
		}
		if($unitinti[$bar['kodeunit']]=='1'){
			$dtthnlalubgt['43000']+=$bar['tbsthn'];
			$dtthnlalubgtytd['43000']+=$bar['tbsbi'];
		}
		
	}
	//	@$dtthnini['35000'][$bar['periode']]+=$bar['kgwbnetto'];
}
#= tutup budget


#= budget produksi kebun
$str="SELECT tahunbudget,sum".$addstrbgtproduksikebun."/".$pembagiton." as bi,sum".$addstrthnbgtproduksikebun."/".$pembagiton." as thn from ".$dbname.".bgt_produksi_kebun where 1=1 ".$whereintibgtproduksikebun." and (tahunbudget='".$tahun."' or tahunbudget='".$tahunlalu."') group by tahunbudget ";
$res=fetchdata($str);
foreach($res as $bar){
	#= data tahun ini
	if($bar['tahunbudget']==$tahun){
		#= masukan data
		$dtthninibgt['35000']+=$bar['thn'];
		$dtthninibgtytd['35000']+=$bar['bi'];
	}
	
	#= data tahun lalu
	if($bar['tahunbudget']==$tahunlalu){
		#= masukan data
		$dtthnlalubgt['35000']+=$bar['thn'];
		$dtthnlalubgtytd['35000']+=$bar['bi'];
	}
	//	@$dtthnini['35000'][$bar['periode']]+=$bar['kgwbnetto'];
}
#= tutup budget



#= budget blok
$str="SELECT  sum(pokokthnini)/sum(hathnini) as akp,avg(('".$tahunlalu."'-thntnm)) as thnlalu,avg(('".$tahun."'-thntnm)) as thnini,tahunbudget,sum(hathnini) as luas,statusblok from ".$dbname.".bgt_blok where 1=1 ".$whereintibgtblok." and (tahunbudget='".$tahun."' or tahunbudget='".$tahunlalu."') and statusblok!='BBT' group by tahunbudget,statusblok ";
// echo $str;exit;
$res=fetchdata($str);
foreach($res as $bar){
	#= data tahun ini
	if($bar['tahunbudget']==$tahun){
		if($bar['statusblok']=='TM'){
			#= masukan data
			$dtthninibgt['51000']+=$bar['luas'];
			$dtthninibgtytd['51000']+=$bar['luas'];
		}else{
			$dtthninibgt['52000']+=$bar['luas'];
			$dtthninibgtytd['52000']+=$bar['luas'];
		}
		$dtthninibgt['54000']+=$bar['thnini'];
		$dtthninibgtytd['54000']+=$bar['thnini'];
		
		$dtthninibgt['55000']+=$bar['akp'];
		$dtthninibgtytd['55000']+=$bar['akp'];
	}
	
	#= data tahun lalu
	if($bar['tahunbudget']==$tahunlalu){
		if($bar['statusblok']=='TM'){
			#= masukan data
			$dtthnlalubgt['51000']+=$bar['luas'];
			$dtthnlalubgtytd['51000']+=$bar['luas'];
		}else{
			$dtthnlalubgt['52000']+=$bar['luas'];
			$dtthnlalubgtytd['52000']+=$bar['luas'];
		}
		$dtthnlalubgt['54000']+=$bar['thnlalu'];
		$dtthnlalubgtytd['54000']+=$bar['thnlalu'];
		
		$dtthnlalubgt['55000']+=$bar['akp'];
		$dtthnlalubgtytd['55000']+=$bar['akp'];
	}
	//	@$dtthnini['35000'][$bar['periode']]+=$bar['kgwbnetto'];
}
#= tutup budget


#====
$dtthnini=array();


#= ambil list laporan
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	$arrnourut[$bar['nourut']]=$bar['nourut'];
	$namanourut[$bar['nourut']]=$bar['keterangandisplay'];
	$noakuntotalnourut[$bar['nourut']]=$bar['noakundisplay'];
	$tipenourut[$bar['nourut']]=$bar['tipe'];
	$tampil[$bar['nourut']]=$bar['tampil'];
	$detail[$bar['nourut']]=$bar['detail'];
	$tipeunit[$bar['nourut']]=$bar['tipeunit'];

}

#= ambil jumlah kodejurnal
$str="select * from ".$dbname.".keu_5mesinlaporandt_kodejurnal where namalaporan='".$kodelaporan."' and tipe='realisasi' order by nourut asc";
$res=fetchdata($str);
foreach($res as $bar){
	@$jumlahdaftarkodejurnal[$bar['nourut']]++;
	if($nouruttemp==$bar['nourut']){
		@$daftarakunkodejurnal[$bar['nourut']].=",'".trim($bar['kodejurnal'])."'";
	}else{
		@$daftarakunkodejurnal[$bar['nourut']].="'".trim($bar['kodejurnal'])."'";
	}
	$nouruttemp=$bar['nourut'];
}



#= ambil jumlah kodebudget
$str="select * from ".$dbname.".keu_5mesinlaporandt_kodejurnal where namalaporan='".$kodelaporan."' and tipe='budget' order by nourut asc";
$res=fetchdata($str);
foreach($res as $bar){
	@$jumlahdaftarkodebudget[$bar['nourut']]++;
	if($nouruttemp==$bar['nourut']){
		@$daftarakunkodebudget[$bar['nourut']].=",'".trim($bar['kodejurnal'])."'";
	}else{
		@$daftarakunkodebudget[$bar['nourut']].="'".trim($bar['kodejurnal'])."'";
	}
	$nouruttemp=$bar['nourut'];
}


#= ambil daftar noakun
$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut asc";
$res=fetchdata($str);
foreach($res as $bar){
	@$jumlahdaftar[$bar['nourut']]++;
	if($nouruttemp==$bar['nourut']){
		@$daftarakun[$bar['nourut']].=",'".trim($bar['noakun'])."'";
	}else{
		@$daftarakun[$bar['nourut']].="'".trim($bar['noakun'])."'";
	}
	$nouruttemp=$bar['nourut'];
	
	$nakunvs[substr(trim($bar['noakun']),0,3)]=substr(trim($bar['noakun']),0,3);
	$arrgetnourut[$bar['noakun']]=$bar['nourut'];
}

foreach($arrnourut as $nourut){
	//indra
	if($tipenourut[$nourut]=='Detail' and $nourut<30000 and (@$jumlahdaftar[$nourut]>0 || @$jumlahdaftar[$nourut]!='')){
		$listakun='';
		if(@$jumlahdaftar[$nourut]>0){
			$listakun=" and noakun in (".$daftarakun[$nourut].")";
		}
		
		
		#= realisasi
		$listkodejurnal='';
		if(@$jumlahdaftarkodejurnal[$nourut]>0){
			$listkodejurnal=" and kodejurnal in (".$daftarakunkodejurnal[$nourut].")";
		}
		
		#= budget
		$listkodebudget='';
		if(@$jumlahdaftarkodebudget[$nourut]>0){
			$listkodebudget=" and kodebudget in (".$daftarakunkodebudget[$nourut].")";
		}
		
		
		$wherejurnal=$where;
		if($tipeunit[$nourut]!=''){
			$notipeunit=0;
			$arrtipeunit=explode(',',$tipeunit[$nourut]);
			foreach($arrtipeunit as $key){
				if($notipeunit>0){
					@$daftartipeunit[$nourut].=",'".trim($key)."'";
				}else{
					@$daftartipeunit[$nourut].="'".trim($key)."'";
				}
				$notipeunit++;
			}
			$wheretipeunit=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' ".$whereunit."  and tipe in (".$daftartipeunit[$nourut]."))";
			$wherejurnal=$wheretipeunit;
		}
			
		#=========================== realisasi	
		$str="select kodejurnal, sum(jumlah) as jumlah,noakun,periode,substr(periode,1,4) as tahun from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal." ".$listakun."  ".$listkodejurnal."  and (periode like '".$tahun."%' or periode like '".$tahunlalu."%') group by periode,kodejurnal,noakun";
		$res=fetchdata($str);
		foreach($res as $bar){
			#= data tahun ini
			if($bar['tahun']==$tahun){
				#= masukan data
				$dtthnini[$nourut][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
				if ($bar['periode']<=$param['periode']){ 
					$dtthnini[$nourut][$subper[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
				}
				
				#$datac[$bar['noakun']][$bar['kodejurnal']]=$bar['kodejurnal'];
				@$datacek[$bar['noakun']][$bar['kodejurnal']][$bar['periode']]['mpi']+=$bar['jumlah']/$pembagijuta;
			}
			
			#= data tahun lalu
			if($bar['tahun']==$tahunlalu){
				#= masukan data
				//$dtthnlalu[$nourut]=$bar['jumlah']/$pembagijuta;
				$dtthnlalu[$nourut][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
				$dtthnlalu[$nourut][$subperlalu[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
			}
		}
		#= tutup realisasi
		
		# breakdown alokasi oh
		$str="select sum(jumlah) as jumlah, noakun, periode, substr(periode,1,4) as tahun from ".$dbname.".keu_jurnaldetailbyyoh where 1=1 ".$wherejurnal." ".$listakun." and (periode like '".$tahun."%' or periode like '".$tahunlalu."%') group by periode, noakun";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['tahun']==$tahun){
				$dtthnini[$nourut][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
				if ($bar['periode']<=$param['periode']){ 
					$dtthnini[$nourut][$subper[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
				}
				@$datacek[$bar['noakun']]['DOH'][$bar['periode']]['mpi']+=$bar['jumlah']/$pembagijuta;
			}
			if($bar['tahun']==$tahunlalu){
				$dtthnlalu[$nourut][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
				$dtthnlalu[$nourut][$subperlalu[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
			}
		}
		#= tutup breakdown alokasi oh
		
		
		
		#= budget
		$str="SELECT tahunbudget,sum".$addstr." as bi,sum".$addstrthn." as thn from ".$dbname.".bgt_budget_detail where 1=1 ".$wherejurnal." ".$listakun."  ".$listkodebudget." and (tahunbudget='".$tahun."' or tahunbudget='".$tahunlalu."') group by tahunbudget ";
		// exit("Error:$str");
		$res=fetchdata($str);
		foreach($res as $bar){
			#= data tahun ini
			if($bar['tahunbudget']==$tahun){
				#= masukan data
				$dtthninibgt[$nourut]+=$bar['thn']/$pembagijuta;
				$dtthninibgtytd[$nourut]+=$bar['bi']/$pembagijuta;
			}
			
			#= data tahun lalu
			if($bar['tahunbudget']==$tahunlalu){
				#= masukan data
				$dtthnlalubgt[$nourut]+=$bar['thn']/$pembagijuta;
				$dtthnlalubgtytd[$nourut]+=$bar['bi']/$pembagijuta;
			}
		}
		#= tutup budget
		
		
		
		
	}
}

#buat croscek
foreach($arrnourut as $nourut){
	if($nourut=='11998'){
		$listakun=" and (substr(noakun,1,3) in ('621','611') or substr(noakun,1,1) in ('7') or substr(noakun,1,2) in ('63','64'))";
		$listakun=" and substr(noakun,1,3) in ('".implode("','",$nakunvs)."')";
		$listakun=" and (substr(noakun,1,3) in ('621','611'))";
		$listkodejurnal='';
		$wherejurnal=$where;
		if($tipeunit[$nourut]!=''){
			$notipeunit=0;
			$arrtipeunit=explode(',',$tipeunit[$nourut]);
			foreach($arrtipeunit as $key){
				if($notipeunit>0){
					@$daftartipeunit[$nourut].=",'".trim($key)."'";
				}else{
					@$daftartipeunit[$nourut].="'".trim($key)."'";
				}
				$notipeunit++;
			}
			$wheretipeunit=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' ".$whereunit."  and tipe in (".$daftartipeunit[$nourut]."))";
			$wherejurnal=$wheretipeunit;
		}
		
		$str="select kodejurnal,noakun, sum(jumlah) as jumlah,noakun,periode,substr(periode,1,4) as tahun, kodejurnal from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal." ".$listakun."  ".$listkodejurnal."  and (periode like '".$tahun."%' or periode like '".$tahunlalu."%') group by periode, kodejurnal,noakun";
		// echo $str.";<br>";
		$res=fetchdata($str);
		foreach($res as $bar){
			#= data tahun ini
			if($bar['tahun']==$tahun){
				#= masukan data
				$dtthnini[$nourut][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
				if ($bar['periode']<=$param['periode']){ 
					$dtthnini[$nourut][$subper[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
				}
				
				$datac[$bar['noakun']][$bar['kodejurnal']]=$bar['kodejurnal'];
				$datacek[$bar['noakun']][$bar['kodejurnal']][$bar['periode']]['jurnal']+=$bar['jumlah']/$pembagijuta;
			}
			
			#= data tahun lalu
			if($bar['tahun']==$tahunlalu){
				#= masukan data
				//$dtthnlalu[$nourut]=$bar['jumlah']/$pembagijuta;
				$dtthnlalu[$nourut][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
				$dtthnlalu[$nourut][$subperlalu[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
			}
		}
		
		#DETAIL BREAKDOWN
		$str="select sum(jumlah) as jumlah,noakun,periode,substr(periode,1,4) as tahun from ".$dbname.".keu_jurnaldetailbyyoh where 1=1 ".$wherejurnal." ".$listakun." and (periode like '".$tahun."%' or periode like '".$tahunlalu."%') group by periode,noakun";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['tahun']==$tahun){
				$dtthnini[$nourut][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
				if ($bar['periode']<=$param['periode']){ 
					$dtthnini[$nourut][$subper[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
				}
				$datac[$bar['noakun']]['DOH']=$bar['kodejurnal'];
				$datacek[$bar['noakun']]['DOH'][$bar['periode']]['jurnal']+=$bar['jumlah']/$pembagijuta;
			}
			if($bar['tahun']==$tahunlalu){
				$dtthnlalu[$nourut][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
				$dtthnlalu[$nourut][$subperlalu[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
			}
		}
	}
}

# =========================== DATA CROSS CECK ==============================
if($param['tipe']=='excel'){
} else if ($param['tipe']=='pdf') {
}else{
$tab="<div id=contselisih style=display:none><table class=sortable cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr class=rowheader>";
		$tab.="<th align=center rowspan=2>" . $_SESSION['lang']['nomor'] . "</th>";
		$tab.="<th align=center rowspan=2>" . $_SESSION['lang']['akun'] . "</th>";
		$tab.="<th align=center rowspan=2>" . $_SESSION['lang']['kodejurnal'] . "</th>";
		foreach($arrayper as $prd){
			$tab.="<th align=center colspan=3>".$prd."</th>";
		}
$tab.="</tr>";
$tab.="<tr class=rowheader>";
	foreach($arrayper as $prd){
		$tab.="<th align=center>MPI</th>";
		$tab.="<th align=center>Jurnal</th>";
		$tab.="<th align=center>Varian</th>";
	}	
$tab.="</tr>";

$tab.="</thead>
<tbody>";
	ksort($datac);
	// echo"<pre>";
	// print_r($datac);
	// echo"</pre>";
	// exit()
	$varvsjurnal=array();
	foreach($datac as $noakun => $v1){
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center></td>";
		$tab.="<td align=left colspan=".((count($arrayper)*3)+3).">".$noakun." - ".getNamaAkun($noakun)."</td>";
		$tab.="</tr>";
		foreach($v1 as $kode){
			$nx++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $nx . "</td>";
			//$tab.="<td align=left>".$noakun." - ".getNamaAkun($noakun)."</td>";
			$tab.="<td align=center></td>";
			$tab.="<td align=left>" . $kode . "</td>";
			foreach($arrayper as $prd){
				$tab.="<td align=right>".number_format($datacek[$noakun][$kode][$prd]['mpi'])."</td>";
				$tab.="<td align=right>".number_format($datacek[$noakun][$kode][$prd]['jurnal'])."</td>";
				if(abs($datacek[$noakun][$kode][$prd]['jurnal']-$datacek[$noakun][$kode][$prd]['mpi'])>0){
					$warna="style=color:red";
				}else{
					$warna="";
				}
				
				$tab.="<td align=right ".$warna.">".number_format($datacek[$noakun][$kode][$prd]['jurnal']-$datacek[$noakun][$kode][$prd]['mpi'])."</td>";
				
				$total[$prd]['mpi']+=$datacek[$noakun][$kode][$prd]['mpi'];
				$total[$prd]['jurnal']+=$datacek[$noakun][$kode][$prd]['jurnal'];
			}
			$tab.="</tr>";
		}
	}
	
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center colspan=3>TOTAL</td>";
	foreach($arrayper as $prd){
		$tab.="<td align=right>".number_format($total[$prd]['mpi'])."</td>";
		$tab.="<td align=right>".number_format($total[$prd]['jurnal'])."</td>";
		if(abs($total[$prd]['jurnal']-$total[$prd]['mpi'])>0){
			$warna="style=color:red";
			$stylecol[$prd]="color:red;";
			$varvsjurnal[$prd]=abs($total[$prd]['jurnal']-$total[$prd]['mpi']);
		}else{
			$varvsjurnal[$prd]='';
			$stylecol[$prd]="";
			$warna="";
		}
		$tab.="<td align=right ".$warna.">".number_format($total[$prd]['jurnal']-$total[$prd]['mpi'])."</td>";
		
	}

$tab.="</tbody>";
$tab.="</table></div>";
echo $tab;
}
# =========================== DATA CROSS CECK ==============================

#= untuk data pendukung  kebun (data > 30000)
#= Production Volume (Ton) s/d Rubber Yield/ha 
#= Production Volume (Ton) 
#= CPO PK dari pabrik_produksi
$str="select sum(oer)/".$pembagiton." as cpo,sum(oerpk)/".$pembagiton."  as pk,substr(tanggal,1,7) as periode,substr(tanggal,1,4) as tahun  from ".$dbname.".pabrik_produksi  where (tanggal like '".$tahun."%' or tanggal like '".$tahunlalu."%')  ".$where." group by periode";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	@$dtthnini['31000'][$bar['periode']]+=$bar['cpo'];
	@$dtthnini['32000'][$bar['periode']]+=$bar['pk'];
	if ($bar['periode']<=$param['periode']){ 
		$dtthnini['31000'][$subper[$bar['periode']]]+=$bar['cpo'];
		$dtthnini['32000'][$subper[$bar['periode']]]+=$bar['pk'];
	}

	if($bar['tahun']==$tahunlalu){
		$dtthnlalu['31000'][$bar['periode']]+=$bar['cpo'];
		$dtthnlalu['32000'][$bar['periode']]+=$bar['pk'];
		
		$dtthnlalu['31000'][$subperlalu[$bar['periode']]]+=$bar['cpo'];
		$dtthnlalu['32000'][$subperlalu[$bar['periode']]]+=$bar['pk'];
	}
}

#= FFB / TBS ambil dari spb
$str="select sum(kgwb)/".$pembagiton."  as kgwb,sum(kgwbnetto)/".$pembagiton."  as kgwbnetto,substr(tanggal,1,7) as periode,substr(tanggal,1,4) as tahun  from ".$dbname.".kebun_spb_vw  where (tanggal like '".$tahun."%' or tanggal like '".$tahunlalu."%') ".$whereinti." group by periode";
$res=fetchdata($str);
foreach($res as $bar){
	@$dtthnini['35000'][$bar['periode']]+=$bar['kgwbnetto'];
	if ($bar['periode']<=$param['periode']){ 
		$dtthnini['35000'][$subper[$bar['periode']]]+=$bar['kgwbnetto'];
	}
	if($bar['tahun']==$tahunlalu){
		$dtthnlalu['35000'][$bar['periode']]+=$bar['kgwbnetto'];
		$dtthnlalu['35000'][$subperlalu[$bar['periode']]]+=$bar['kgwbnetto'];
	}
}

#= HA mature / TM immature/TBM
$str="select sum(luasareaproduktif) as luas,count(*) as jumblok,statusblok,tahun,substr(tahun,1,4) as tahun,substr(tahun,5,2) as bulan   from ".$dbname.".setup_blok_tahunan  where (tahun like '".$tahun."%' or tahun like '".$tahunlalu."%')  ".$whereinti." and statusblok in ('TM','TBM') group by statusblok,tahun order by setup_blok_tahunan.tahun";	
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['tahun']==$tahun){
		if($bar['statusblok']=='TM'){
			$dtthnini['51000'][$bar['tahun'].'-'.$bar['bulan']]=$bar['luas'];
			if ($bar['periode']<=$param['periode']){ 
				$dtthnini['51000'][$subper[$bar['tahun'].'-'.$bar['bulan']]]=$bar['luas'];
			}
			$tdtthnini['51000']=$bar['luas'];
		}
		if($bar['statusblok']=='TBM'){
			$dtthnini['52000'][$bar['tahun'].'-'.$bar['bulan']]=$bar['luas'];
			if ($bar['periode']<=$param['periode']){ 
				$dtthnini['52000'][$subper[$bar['tahun'].'-'.$bar['bulan']]]=$bar['luas'];
			}
			$tdtthnini['52000']=$bar['luas'];
		}	
	}
	if($bar['tahun']==$tahunlalu){
		#= masukan data
		if($bar['statusblok']=='TM'){
			$dtthnlalu['51000'][$bar['tahun'].'-'.$bar['bulan']]=$bar['luas'];
			$dtthnlalu['51000'][$subperlalu[$bar['tahun'].'-'.$bar['bulan']]]=$bar['luas'];
			$tdtthnlalu['51000']=$bar['luas'];
		}
		if($bar['statusblok']=='TBM'){
			$dtthnlalu['52000'][$bar['tahun'].'-'.$bar['bulan']]=$bar['luas'];
			$dtthnlalu['52000'][$subperlalu[$bar['tahun'].'-'.$bar['bulan']]]=$bar['luas'];
			$tdtthnlalu['52000']=$bar['luas'];
		}
	}	
}

#== average tanaman
$str="select  avg((".$tahunlalu."-tahuntanam)) as avgumurthnlalu,avg((".$tahun."-tahuntanam)) as avgumurthnini,tahun,substr(tahun,1,4) as tahun,substr(tahun,5,2) as bulan   from ".$dbname.".setup_blok_tahunan  where (tahun like '".$tahun."%' or tahun like '".$tahunlalu."%')  ".$whereinti." and statusblok in ('TM') group by tahun order by setup_blok_tahunan.tahun";	
// echo $str;exit();	
$res=fetchdata($str);
foreach($res as $bar){
	$dtthnini['54000'][$bar['tahun'].'-'.$bar['bulan']]=$bar['avgumurthnini'];
	if ($bar['periode']<=$param['periode']){ 
		$dtthnini['54000'][$subper[$bar['tahun'].'-'.$bar['bulan']]]=$bar['avgumurthnini'];
	}
	$tdtthnini['54000']=$bar['avgumurthnini'];
	if($bar['tahun']==$tahunlalu){
		$dtthnlalu['54000'][$bar['tahun'].'-'.$bar['bulan']]=$bar['avgumurthnlalu'];
		$dtthnlalu['54000'][$subperlalu[$bar['tahun'].'-'.$bar['bulan']]]=$bar['avgumurthnlalu'];
		$tdtthnlalu['54000']=$bar['avgumurthnlalu'];
	}
}

#==  Tree/Ha 
$str="select  sum(jumlahpokok)/sum(luasareaproduktif) as akp,tahun,substr(tahun,1,4) as tahun,substr(tahun,5,2) as bulan   from ".$dbname.".setup_blok_tahunan  where (tahun like '".$tahun."%' or tahun like '".$tahunlalu."%')  ".$whereinti." and statusblok in ('TM') group by tahun";	
$res=fetchdata($str);
foreach($res as $bar){
	$dtthnini['55000'][$bar['tahun'].'-'.$bar['bulan']]=$bar['akp'];

	if ($bar['periode']<=$param['periode']){ 
		$dtthnini['55000'][$subper[$bar['tahun'].'-'.$bar['bulan']]]=$bar['akp'];
	}
	$tdtthnini['55000']=$bar['akp'];
	if($bar['tahun']==$tahunlalu){
		$dtthnlalu['55000'][$bar['tahun'].'-'.$bar['bulan']]=$bar['akp'];
		$dtthnlalu['55000'][$subperlalu[$bar['tahun'].'-'.$bar['bulan']]]=$bar['akp'];
		$tdtthnlalu['55000']=$bar['akp'];
	}
}

#==  FFB Processed in Own Mill (Ton)
#= ambil data dari pabrik_timbangan

#= Terima Tbs Internal 
#= internal : intiplasma = inti ; intext=1
$str="select sum(beratbersih) as bruto,sum(kgpotsortasi) as sortasi,sum(beratbersih)-sum(kgpotsortasi) as netto,intex,intiplasma,substr(tanggal,1,7) as periode,substr(tanggal,1,4) as tahun  from ".$dbname.".pabrik_timbangan_vw   where kodebarang='40000003' and (tanggal like '".$tahun."%' or tanggal like '".$tahunlalu."%') and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' and tipe='PABRIK' ".$whereunit." ) group by intex,intiplasma,periode";
// echo $str;exit();
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['intex']==1 and $bar['intiplasma']=='INTI'){
		@$dtthnini['43000'][$bar['periode']]+=$bar['netto']/$pembagiton;
		if ($bar['periode']<=$param['periode']){ 
			$dtthnini['43000'][$subper[$bar['periode']]]+=$bar['netto']/$pembagiton;
		}
		if($bar['tahun']==$tahunlalu){
			$dtthnlalu['43000'][$bar['periode']]+=$bar['netto']/$pembagiton;
			$dtthnlalu['43000'][$subperlalu[$bar['periode']]]+=$bar['netto']/$pembagiton;
		}
	} else {
		@$dtthnini['41000'][$bar['periode']]+=$bar['netto']/$pembagiton;
		if ($bar['periode']<=$param['periode']){ 
			$dtthnini['41000'][$subper[$bar['periode']]]+=$bar['netto']/$pembagiton;
		}
		if($bar['tahun']==$tahunlalu){
			$dtthnlalu['41000'][$bar['periode']]+=$bar['netto']/$pembagiton;
			$dtthnlalu['41000'][$subperlalu[$bar['periode']]]+=$bar['netto']/$pembagiton;
		}
	}
}

# 21001 -Cost of FFB - Own Estate | sumber data kebun_tbsinternal
/*
$str="select floor(sum(totalrp)) as totalrp,substr(tanggal,1,7) as periode,substr(tanggal,1,4) as tahun from ".$dbname.".kebun_tbsinternal where unit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' and tipe='PABRIK' ".$whereunit.") and divisi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."') group by periode,notransaksi";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	@$dtthnini['21001'][$bar['periode']]+=$bar['totalrp']/$pembagijuta;
	if ($bar['periode']<=$param['periode']){ 
		$dtthnini['21001'][$subper[$bar['periode']]]+=$bar['totalrp']/$pembagijuta;
	}
	if($bar['tahun']==$tahunlalu){
		$dtthnlalu['21001'][$bar['periode']]+=$bar['totalrp']/$pembagijuta;
		$dtthnlalu['21001'][$subperlalu[$bar['periode']]]+=$bar['totalrp']/$pembagijuta;
	}
}
*/

## Hitung Budget AOP
/*
$str="SELECT * from ".$dbname.".bgt_budget_detail where left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' ".$whereunit.") and tahunbudget between '".($tahunlalu) ."' and '".$tahun."' order by noakun";
#= tambahin where unit
// exit();
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	$nourutnya=$arrgetnourut[$bar['noakun']];
	if ($bar['tahunbudget']==$tahunlalu) {
		for ($i=1; $i <=intval($qwe[1]) ; $i++) {  
			$dtthnlalubgt[$nourutnya]+=$bar['rp'.addZero($i,2)]/$pembagijuta;
		} 
	}
	// $tahunlalu2=$tahunlalu-1;
	if ($bar['tahunbudget']==$tahunlalu) {
		for ($i=1; $i <=intval($qwe[1]) ; $i++) {  
			$dtthnlaluaopbgt[$nourutnya]+=$bar['rp'.addZero($i,2)]/$pembagijuta;
		} 
	}
	
	if ($bar['tahunbudget']==$tahun) {
		for ($i=1; $i <=intval($qwe[1]) ; $i++) {  
			$dtthninibgt[$nourutnya]+=$bar['rp'.addZero($i,2)]/$pembagijuta;
		} 
	}

}

*/


// echo "<pre>";
// print_r($dtthnlalubgt);
// echo "</pre>";
// exit();
#berarti tahun lalu 2021 
if($tahun>='2022'){	
	$whthnlalu=" and substr(kodeunit,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."' ".$whereunit.")";
	$str = "select sum(jumlah) as jumlah, code, periode,jenis from ".$dbname.".keu_mpi where 1=1 ".$whthnlalu." group by kodeunit, code, periode,jenis";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['jenis']=='MPI'){			
			$dtthnlalu[$bar['code']][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
			$dtthnlalu[$bar['code']][$subperlalu[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
		}
		if($bar['jenis']=='GC'){			
			$dtthnlalu[$bar['code']][$bar['periode']]+=$bar['jumlah']/$pembagijuta;
			$dtthnlalu[$bar['code']][$subperlalu[$bar['periode']]]+=$bar['jumlah']/$pembagijuta;
		}
	}
}

#= buat total
foreach($arrnourut as $nourut){
	if($tipenourut[$nourut]=='Total'){
		$daftartotal=explode(',',$noakuntotalnourut[$nourut]);
		foreach($daftartotal as $key){
			$amin=substr($key,0,1);
			#= untuk budget
			if($amin=='-'){
				$key=str_replace('-','',$key); #= buat hilangin -
				@$dtthnlalubgtytd[$nourut]-=$dtthnlalubgtytd[$key];
				@$dtthnlalubgt[$nourut]-=$dtthnlalubgt[$key]; 
				@$dtthninibgt[$nourut]-=$dtthninibgt[$key];
				@$dtthninibgtytd[$nourut]-=$dtthninibgtytd[$key];  
			}else{
				@$dtthnlalubgtytd[$nourut]+=$dtthnlalubgtytd[$key];
				@$dtthnlalubgt[$nourut]+=$dtthnlalubgt[$key];
				@$dtthninibgt[$nourut]+=$dtthninibgt[$key];
				@$dtthninibgtytd[$nourut]+=$dtthninibgtytd[$key];  
			}
			
			#= untuk realisasi
			foreach($arrper as $per){
				if($amin=='-'){
					$key=str_replace('-','',$key);
					@$dtthnini[$nourut][$per]-=$dtthnini[$key][$per];
				}else{
					@$dtthnini[$nourut][$per]+=$dtthnini[$key][$per];
				}
				
				if($nourut=='19999'){
					@$dtthnini['21001'][$per]=$dtthnini['19999'][$per];
				}
			}
			foreach($arrperlalu as $per){
				if($amin=='-'){
					$key=str_replace('-','',$key);
					@$dtthnlalu[$nourut][$per]-=$dtthnlalu[$key][$per];
				}else{
					@$dtthnlalu[$nourut][$per]+=$dtthnlalu[$key][$per];
				}
				
				if($nourut=='19999'){
					@$dtthnini['21001'][$per]=$dtthnini['19999'][$per];
				}
				
			} 
		}
	}
	
	
	
}

$judul='';
if($param['tipe']=='excel'){
	$judul.="<table border=1 cellspacing=1 class='sortable nowrap'>";
}else{	
	$judul.="<table border=0 cellspacing=1 class='sortable nowrap'>";
}
$judul.="<thead>";
$judul.="<tr class=rowheader>";
$judul.="<th align=center colspan='".($cspan)."'><b>".strtoupper($namaorg[$param['kodept']]."<br>".$judullaporan)."</b></th>";
$judul.="</tr>";

$judul.="<tr class=rowheader>";
$judul.="<th align=center colspan=2 rowspan=2 ><b>".$_SESSION['lang']['keterangan']."</b></th>";
$judul.="<th align=center colspan=".(count($arrperlalu)+3)."><b>".$tahunlalu."</b></th>";
$judul.="<th align=center colspan=".(count($arrper)+6)."><b>".$tahun."</b></th>";
$judul.="</tr>";
$judul.="<tr class=rowheader>";

foreach($arrperlalu as $per){
	if(numToMonth(floatval(substr($per,5,2)),'I','long')=="undefined"){
		$judul.="<th align=center style='width:5%'><b>".substr($per,5,2)."</b></th>";
	}else{
		$judul.="<th align=center style='width:5%'  name=[".$subperlalu[$per]."]><b>".numToMonth(floatval(substr($per,5,2)),'I','long')."</b></th>";
	}
}
$judul.="<th align=center style='width:5%'><b>".$_SESSION['lang']['total']." Tahun</b></th>";
$judul.="<th align=center style='width:5%'><b> AOP YTD</b></th>";
$judul.="<th align=center style='width:5%'><b> AOP FY</b></th>";
foreach($arrper as $per){
	if(numToMonth(floatval(substr($per,5,2)),'I','long')=="undefined"){
		$judul.="<th align=center style='width:5%'><b>".substr($per,5,2)."</b></th>";
	}else{
		$judul.="<th align=center style='width:5%' name=[".$subper[$per]."]><b>".numToMonth(floatval(substr($per,5,2)),'I','long')."</b></th>";
	}
}
$judul.="<th align=center style='width:5%'><b>YTD</b></th>";
$judul.="<th align=center style='width:5%'><b>VARIANCE ACT vs LY</b></th>";
$judul.="<th align=center style='width:5%'><b>AOP YTD</b></th>";
$judul.="<th align=center style='width:5%'><b>VARIANCE ACT vs AOP </b></th>";
$judul.="<th align=center style='width:5%'><b>AOP FY</b></th>";
$judul.="<th align=center style='width:5%'><b>VARIANCE ACT vs AOP FY</b></th>";
$judul.="</tr>";
$judul.="</thead>";

// echo $judul;
// exit();

array_multisort($arrnourut,SORT_ASC);
// echo"<pre>";
// print_r($arrnourut);
$stream='';
$stream.=$judul;
foreach($arrnourut as $nourut){
	$style='';
	if($tampil[$nourut]=='1'){
		$stream.="<tr class='rowcontent nowrap'>";
		if($tipenourut[$nourut]=='Header'){
			if($nourut<50000){
				$stream.="<td nowrap align=left colspan='".($cspan)."'><b>".$namanourut[$nourut]."  </b></td>"; 
			}else{
				$stream.="<td nowrap align=left ".$style." colspan=2><b>".($namanourut[$nourut])."  </b></td>"; 
				
				#tahun lalu
				foreach($arrperlalu as $per){

					if ($per<=$periodlalu) {
						$arrytd2[$nourut]['thnlalu']+=$dtthnlalu[$nourut][$per];
						// $arrytd2[$nourut]['thnlalu']+=$dtthnlalu[$nourut][$per];
					}

					$expper = explode("-",$per);
					$style="";
					if(substr($expper[1],0,1)=="Q"){
						$style="style=background-color:#ccfffd;";
					}
					switch($nourut){
						case'56000':
							$stream.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtthnlalu['19999'][$per]/$dtthnlalu['35000'][$per]))."</td>"; 
						break;
						case'57000':
							$stream.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtthnlalu['35000'][$per]/$dtthnlalu['51000'][$per]))."</td>"; 
						break;
						default:
							$stream.="<td align=right ".$style."> ".@hidezerodecimal($dtthnlalu[$nourut][$per])."</td>"; 
						break;
					}
				}
				$style="style=background-color:#ccfffd;";
				switch($nourut){
					case'56000':
						$stream.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($tdtthnlalu['19999']/$tdtthnini['35000']))."</td>"; 
					break;
					case'57000':
						$stream.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($tdtthnlalu['35000']/$tdtthnini['51000']))."</td>"; 
					break;
					default:
						$stream.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($tdtthnlalu[$nourut]))."</td>"; 
					break;
				}
				#BUDGET
				switch($nourut){
					case'56000':
						$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal($dtthnlalubgtytd['19999']/$dtthnlalubgtytd['35000'])."</td>";
						$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal($dtthnlalubgt['19999']/$dtthnlalubgt['35000'])."</td>"; 
					break;
					case'57000':
						$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal($dtthnlalubgtytd['35000']/$dtthnlalubgtytd['51000'])."</td>";
						$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal($dtthnlalubgt['35000']/$dtthnlalubgt['51000'])."</td>"; 
					break;
					default:
						$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal($dtthnlalubgtytd[$nourut])."</td>";  
						$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal($dtthnlalubgt[$nourut])."</td>";  
					break;
				}
				
				foreach($arrper as $per){

					// if ($per<=$param['periode']) {
					// 	$arrytd2[$nourut]['thnini']+=$dtthnlalu[$nourut][$per];
					// }

					$expper = explode("-",$per);
					$style=""; 
					if(substr($expper[1],0,1)=="Q"){
						$style="style=background-color:#ccfffd;";
					}
					switch($nourut){
						case'56000':
							$stream.="<td align=right ".$style.">  ".@hidezerodecimal(fixnan($dtthnini['19999'][$per]/$dtthnini['35000'][$per]))."</td>"; 
						break;
						case'57000':
							$stream.="<td align=right ".$style.">  ".@hidezerodecimal(fixnan($dtthnini['35000'][$per]/$dtthnini['51000'][$per]))."</td>"; 
						break;
						default:
							$stream.="<td align=right ".$style."> ".@hidezerodecimal($dtthnini[$nourut][$per])."</td>"; 
						break; 
					}
				}
				
				
				$style="style=background-color:#ccfffd;";
				switch($nourut){
					case'56000':
						$stream.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($tdtthnini['19999']/$tdtthnini['35000']))."</td>"; 
					break;
					case'57000':
						$stream.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($tdtthnini['35000']/$tdtthnini['51000']))."</td>"; 
					break;
					default:
						$stream.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($tdtthnini[$nourut]))."</td>"; 
					break; 
				} 

				
				// $z1=$dtthnini[$nourut][$per];
				// $z2=$arrytd3[$nourut]['thnlalu'];
				// $varianceatvsly3=((($z1-$z2)/$z2)*100); 
				// $varianceactvaop3=((($z1-$dtthninibgt[$nourut])/$dtthninibgt[$nourut])*100);
				// $varianceactvaopthnlalu3=((($z1-$dtthnlalubgt[$nourut])/$dtthnlalubgt[$nourut])*100);
				
				// $x1=$tdtthnini[$nourut];
				// $x2=$arrytd2[$nourut]['thnlalu'];
				// $varianceatvsly2=((($x1-$x2)/$x2)*100);
				// $varianceactvaop2=((($x1-$dtthninibgt[$nourut])/$dtthninibgt[$nourut])*100);
				// $varianceactvaopthnlalu2=((($x1-$dtthnlalubgt[$nourut])/$dtthnlalubgt[$nourut])*100);
				
				$x1=$tdtthnini[$nourut];
				$x2=$arrytd2[$nourut]['thnlalu'];
				$varhead1=((($x1-$x3)/$x3)*100);
				$varhead2=((($x1-$dtthninibgtytd[$nourut])/$dtthninibgtytd[$nourut])*100);
				$varhead3=((($x1-$dtthninibgt[$nourut])/$dtthninibgt[$nourut])*100);


				$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal(fixnan($varhead1),2)."%  </td>"; 
				$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal($dtthninibgtytd[$nourut])." </td>"; 
				$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal(fixnan($varhead2),2)." %</td>"; 
				$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal($dtthninibgt[$nourut])." </td>"; 
				$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".hidezerodecimal(fixnan($varhead3),2)." %</td>";  
			}
			
		}
		if($tipenourut[$nourut]=='Detail'){
			$stream.="<td align=left style='width:1%'>".$nourut." </td>"; 
			$stream.="<td align=left style='width:30%'>".$namanourut[$nourut]." </td>"; 
		}
		if($tipenourut[$nourut]=='Total'){
			@$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
			//$stream.="<td align=left  style='width:2%'></td>"; 
			$stream.="<td nowrap align=left colspan=2 ".$style."><b>".$namanourut[$nourut]." </b></td>"; 
		}
		#= data untuk nilainya
		#= tahun sekarang
		if($tipenourut[$nourut]=='Total' || $tipenourut[$nourut]=='Detail'){ 

			#data tahun lalu per bulan
			foreach($arrperlalu as $per){
				/*
				if ($per<=$periodlalu) {
					$arrytd3[$nourut]['thnlalu']+=$dtthnlalu[$nourut][$per];
				}
				*/
				$expper = explode("-",$per);
				$stl="";
				if(substr($expper[1],0,1)=="Q"){
					$stl="style=background-color:#ccfffd;";
				}
				$stream.="<td align=right ".$style." ".$stl.">".@hidezerodecimal($dtthnlalu[$nourut][$per])."</td>"; 
				if ($per<=$periodlalu) {
					$arrytd[$nourut]['thnlalu']+=$dtthnlalu[$nourut][$per];
				}
				if(substr($expper[1],0,1)!="Q"){					
					@$tdtthnlalu[$nourut]+=$dtthnlalu[$nourut][$per];
				}
			}
			
			#Budget
			$stream.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($tdtthnlalu[$nourut])."</td>";  
			$stream.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtthnlalubgtytd[$nourut])."</td>";    
			$stream.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtthnlalubgt[$nourut])."</td>";   
			foreach($arrper as $per){
				$expper = explode("-",$per);
				$stl="style=cursor:pointer;";
				if(substr($expper[1],0,1)=="Q"){
					$stl.="background-color:#ccfffd;";
				}
				if($nourut=='11998'){
					#buat crosscek vs jurnal
					$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;cursor:pointer;".$stylecol[$per]."'";
					$stream.="<td align=right ".$style." title='Click untuk melihat detail' onclick=\"showvsjurnal();\">".@hidezerodecimal($varvsjurnal[$per])."</td>";
				}else{					
					$stream.="<td align=right ".$style." ".$stl." title='Click untuk melihat detail' onclick=\"detail('".$nourut."','".$per."','".$param['kodept']."','".$regional."','".$param['kodeunit']."','html','event');\">".@hidezerodecimal($dtthnini[$nourut][$per])."</td>";
				}
				
				if ($per<=$param['periode']) {
					$arrytd[$nourut]['thnini']+=$dtthnini[$nourut][$per];
				}

				if(substr($expper[1],0,1)!="Q"){
					@$tdtthnini[$nourut]+=$dtthnini[$nourut][$per];
				} 
			}
			#= akumulasi
			if($nourut!='11998'){				
				$stream.="<td align=right ".$style." style=background-color:#ccfffd> ".@hidezerodecimal($tdtthnini[$nourut])."</td>"; 
				
			}else{
				$stream.="<td ".$style."> </td>"; 
			} 

			#= Variance Act vs LY

			$a1=$tdtthnini[$nourut];
			$a3=$arrytd[$nourut]['thnlalu'];
			$var1=((($a1-$a3)/$a3)*100);
			$var2=((($a1-$dtthninibgtytd[$nourut])/$dtthninibgtytd[$nourut])*100);
			$var3=((($a1-$dtthninibgt[$nourut])/$dtthninibgt[$nourut])*100);

			#cobaa
			$stream.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($var1),2) ." %</td>"; 
			$stream.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($dtthninibgtytd[$nourut])) ."</td>";   
			$stream.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($var2),2) ." %</td>"; 
			$stream.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($dtthninibgt[$nourut])) ."</td>";   
			$stream.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($var3),2) ." %</td>"; 
		}
		$stream.="</tr>";	
	}
}
// $stream.="</table>";
if($param['tipe']=='excel'){
	$stream.="</table>";
}

#====================================================================================================================================================
#============= RP SATUAN ============================================================================================================================
#====================================================================================================================================================
$streamrpsatuan='';
$streamrpsatuan.=$judul;
foreach($arrnourut as $nourut){
	$style='';
	if($tampil[$nourut]=='1' and $nourut<30000){
		$streamrpsatuan.="<tr class=rowcontent>";
		if($tipenourut[$nourut]=='Header'){
			$streamrpsatuan.="<td align=left colspan='".($cspan)."'><b> ".$namanourut[$nourut]."</b></td>"; 
		}
		if($tipenourut[$nourut]=='Detail'){
			$streamrpsatuan.="<td align=left style='width:1%'>".$nourut."</td>"; 
			$streamrpsatuan.="<td align=left style='width:30%'> ".$namanourut[$nourut]."</td>"; 
		}
		if($tipenourut[$nourut]=='Total'){
			@$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
			$streamrpsatuan.="<td align=left  style='width:2%'>".$nourut."</td>"; 
			$streamrpsatuan.="<td align=left ".$style."><b> ".$namanourut[$nourut]."</b></td>"; 
		}
		#= data untuk variancenya
		#= tahun sekarang
		if($tipenourut[$nourut]=='Total' || $tipenourut[$nourut]=='Detail'){
			$tdtthnini[$nourut]=0; $tdtthnlalu[$nourut]=0;$dtthnlaluytd[$nourut]=0;
			
			
			#tahun lalu
			foreach($arrperlalu as $per){
				$style="";
				if(substr($expper[1],0,1)=="Q"){
					$style="style=background-color:#ccfffd;";
				}
				switch($nourut){							
					#= by panen pembagi kg
					// case'11101':
					// case'11102':
					// case'11103':
					// case'11104':
					// case'11199': 
						// $dtsatuanthnlalu[$nourut][$per]=$dtthnlalu[$nourut][$per]/$dtthnlalu['35000'][$per];
						// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal(fixnan($dtsatuanthnlalu[$nourut][$per]))."</td>"; 
					// break;
					// case'11299':
					// case'11399':
					// case'12999':
						// $dtsatuanthnlalu[$nourut][$per]=$dtthnlalu[$nourut][$per]/$dtthnlalu['51000'][$per];
						// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal(fixnan($dtsatuanthnlalu[$nourut][$per]))."</td>"; 
					// break;
					#= pabrik
					case'21001':
						$dtsatuanthnlalu[$nourut][$per]=$dtthnlalu[$nourut][$per]/$dtthnlalu['43000'][$per];
						$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal(fixnan($dtsatuanthnlalu[$nourut][$per]))."</td>"; 
					break;	
					case'21002':
						$dtsatuanthnlalu[$nourut][$per]=$dtthnlalu[$nourut][$per]/$dtthnlalu['41000'][$per];
						$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal(fixnan($dtsatuanthnlalu[$nourut][$per]))."</td>";  
					break;	
					// case'21003':
					// case'21999':
						// $dtsatuanthnlalu[$nourut][$per]=$dtthnlalu[$nourut][$per]/$dtthnlalu['49999'][$per];
						// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal(fixnan($dtsatuanthnlalu[$nourut][$per]))."</td>"; 
					// break;
					default:
						if(substr($nourut,0,3)=='111'){
							$dtsatuanthnlalu[$nourut][$per]=$dtthnlalu[$nourut][$per]/$dtthnlalu['35000'][$per];
							$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal(fixnan($dtsatuanthnlalu[$nourut][$per]))."</td>"; 
						}else if(substr($nourut,0,1)=='1'){
							$dtsatuanthnlalu[$nourut][$per]=$dtthnlalu[$nourut][$per]/$dtthnlalu['51000'][$per];
							$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal(fixnan($dtsatuanthnlalu[$nourut][$per]))."</td>"; 
						}else if(substr($nourut,0,1)=='2'){
							$dtsatuanthnlalu[$nourut][$per]=$dtthnlalu[$nourut][$per]/$dtthnlalu['49999'][$per];
							$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal(fixnan($dtsatuanthnlalu[$nourut][$per]))."</td>"; 
						}
					break;
				}
				$expper = explode("-",$per);
				if(substr($expper[1],0,1)!="Q"){
					@$tdtthnlalu[$nourut]+=$dtthnlalu[$nourut][$per];
				}
				
				if ($per<=$periodlalu) {
					// $arrsatuanytd[$nourut]['thnlalu']+=$dtsatuanthnlalu[$nourut][$per];
					// exit("Error".$per._.$periodlalu);
					$tdtthnlaluytd[$nourut]+=$dtthnlalu[$nourut][$per];//indra
					$tdtthnlaluytd['43000']+=$dtthnlalu['43000'][$per];//indra
					$tdtthnlaluytd['41000']+=$dtthnlalu['41000'][$per];//indra
					$tdtthnlaluytd['35000']+=$dtthnlalu['35000'][$per];//indra
					$tdtthnlaluytd['51000']+=$dtthnlalu['51000'][$per];//indra
					$tdtthnlaluytd['49999']+=$dtthnlalu['49999'][$per];//indra
				}
				#= indra
			}
			
			#= ytd tahun lalu 
			$style="style=background-color:#ccfffd;";
			switch($nourut){
				
				// case'11101':
				// case'11102':
				// case'11103':
				// case'11104':
				// case'11199': 
					// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['35000'])."</td>"; 
					// $dtsatuanthnlalubgtytd[$nourut]=$dtthnlalubgtytd[$nourut]/$dtthnlalubgtytd['35000'];
					// $dtsatuanthnlalubgt[$nourut]=$dtthnlalubgt[$nourut]/$dtthnlalubgt['35000'];
					// $streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgtytd[$nourut])."</td>";
					// $streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgt[$nourut])."</td>";   
				// break;
				// case'11299':
				// case'11399':
				// case'12999':
					// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['51000'])."</td>"; 
					// $dtsatuanthnlalubgtytd[$nourut]=$dtthnlalubgtytd[$nourut]/$dtthnlalubgtytd['51000'];
					// $dtsatuanthnlalubgt[$nourut]=$dtthnlalubgt[$nourut]/$dtthnlalubgt['51000'];
					// $streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgtytd[$nourut])."</td>";
					// $streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgt[$nourut])."</td>";   
				// break;
				#= pabrik
				case'21001':
					$tdtsatuanthnlaluytd[$nourut]=$tdtthnlaluytd[$nourut]/$tdtthnlaluytd['43000']; //untuk ytd thn lalu
					$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['43000'])."</td>";
					$dtsatuanthnlalubgtytd[$nourut]=$dtthnlalubgtytd[$nourut]/$dtthnlalubgtytd['43000'];
					$dtsatuanthnlalubgt[$nourut]=$dtthnlalubgt[$nourut]/$dtthnlalubgt['43000'];
					$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgtytd[$nourut])."</td>";
					$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgt[$nourut])."</td>"; 	
				break;	
				case'21002':
					$tdtsatuanthnlaluytd[$nourut]=$tdtthnlaluytd[$nourut]/$tdtthnlaluytd['41000']; //untuk ytd thn lalu
					$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['41000'])."</td>";
					$dtsatuanthnlalubgtytd[$nourut]=$dtthnlalubgtytd[$nourut]/$dtthnlalubgtytd['41000'];
					$dtsatuanthnlalubgt[$nourut]=$dtthnlalubgt[$nourut]/$dtthnlalubgt['41000'];
					$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgtytd[$nourut])."</td>";
					$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgt[$nourut])."</td>"; 		
				break;	
				// case'21003':
				// case'21999':
					// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['49999'])."</td>";
					// $dtsatuanthnlalubgtytd[$nourut]=$dtthnlalubgtytd[$nourut]/$dtthnlalubgtytd['49999'];
					// $dtsatuanthnlalubgt[$nourut]=$dtthnlalubgt[$nourut]/$dtthnlalubgt['49999'];
					// $streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgtytd[$nourut])."</td>";
					// $streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgt[$nourut])."</td>"; 		
				// break;
				default:
					if(substr($nourut,0,3)=='111'){
						$tdtsatuanthnlaluytd[$nourut]=$tdtthnlaluytd[$nourut]/$tdtthnlaluytd['35000']; //untuk ytd thn lalu
						$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['35000'])."</td>"; 
						$dtsatuanthnlalubgtytd[$nourut]=$dtthnlalubgtytd[$nourut]/$dtthnlalubgtytd['35000'];
						$dtsatuanthnlalubgt[$nourut]=$dtthnlalubgt[$nourut]/$dtthnlalubgt['35000'];
						$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgtytd[$nourut])."</td>";
						$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgt[$nourut])."</td>";   
					}elseif(substr($nourut,0,1)=='1'){
						$tdtsatuanthnlaluytd[$nourut]=$tdtthnlaluytd[$nourut]/$tdtthnlaluytd['51000']; //untuk ytd thn lalu
						$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['51000'])."</td>"; 
						$dtsatuanthnlalubgtytd[$nourut]=$dtthnlalubgtytd[$nourut]/$dtthnlalubgtytd['51000'];
						$dtsatuanthnlalubgt[$nourut]=$dtthnlalubgt[$nourut]/$dtthnlalubgt['51000'];
						$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgtytd[$nourut])."</td>";
						$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgt[$nourut])."</td>";   
					}else if(substr($nourut,0,1)=='2'){
						$tdtsatuanthnlaluytd[$nourut]=$tdtthnlaluytd[$nourut]/$tdtthnlaluytd['49999']; //untuk ytd thn lalu
						$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['49999'])."</td>"; 
						$dtsatuanthnlalubgtytd[$nourut]=$dtthnlalubgtytd[$nourut]/$dtthnlalubgtytd['49999'];
						$dtsatuanthnlalubgt[$nourut]=$dtthnlalubgt[$nourut]/$dtthnlalubgt['49999'];
						$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgtytd[$nourut])."</td>";
						$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".@hidezerodecimal($dtsatuanthnlalubgt[$nourut])."</td>";   
					}
				break;
			}
			
			
			
			# Tahun Ini
			foreach($arrper as $per){
				$style="";
				if(substr($expper[1],0,1)=="Q"){
					$style="style=background-color:#ccfffd;";
				}
				switch($nourut){							
					#= by panen pembagi kg
					// case'11101':
					// case'11102':
					// case'11103':
					// case'11104':
					// case'11199': 
						// $dtsatuanthnini[$nourut][$per]=$dtthnini[$nourut][$per]/$dtthnini['35000'][$per];
						// $streamrpsatuan.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtsatuanthnini[$nourut][$per]))."</td>"; 
					// break;
					# Upkeep & Cultivation
					// case'11299':
					// case'11399':
					// case'12999':
						// $dtsatuanthnini[$nourut][$per]=$dtthnini[$nourut][$per]/$dtthnini['51000'][$per];
						// $streamrpsatuan.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtsatuanthnini[$nourut][$per]))."</td>";
					// break;
					#= pabrik
					case'21001':
						$dtsatuanthnini[$nourut][$per]=$dtthnini[$nourut][$per]/$dtthnini['43000'][$per];
						$streamrpsatuan.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtsatuanthnini[$nourut][$per]))."</td>";
					break;	
					case'21002':
						$dtsatuanthnini[$nourut][$per]=$dtthnini[$nourut][$per]/$dtthnini['41000'][$per];
						$streamrpsatuan.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtsatuanthnini[$nourut][$per]))."</td>";
					break;	
					// case'21003':
					// case'21999':
						// $dtsatuanthnini[$nourut][$per]=$dtthnini[$nourut][$per]/$dtthnini['49999'][$per];
						// $streamrpsatuan.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtsatuanthnini[$nourut][$per]))."</td>";
					// break;
					default:
						if(substr($nourut,0,3)=='111'){
							$dtsatuanthnini[$nourut][$per]=$dtthnini[$nourut][$per]/$dtthnini['35000'][$per];
							$streamrpsatuan.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtsatuanthnini[$nourut][$per]))."</td>"; 
						}else if(substr($nourut,0,1)=='1'){
							$dtsatuanthnini[$nourut][$per]=$dtthnini[$nourut][$per]/$dtthnini['51000'][$per];
							$streamrpsatuan.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtsatuanthnini[$nourut][$per]))."</td>"; 
						}else if(substr($nourut,0,1)=='2'){
							$dtsatuanthnini[$nourut][$per]=$dtthnini[$nourut][$per]/$dtthnini['49999'][$per];
							$streamrpsatuan.="<td align=right ".$style."> ".@hidezerodecimal(fixnan($dtsatuanthnini[$nourut][$per]))."</td>"; 
						}
					break;
				}
				$expper = explode("-",$per);
				if(substr($expper[1],0,1)!="Q"){
					// @$tdtthnini[$nourut]+=$dtthnini[$nourut][$per];
					@$tdtthnini[$nourut]+=$dtthnini[$nourut][$per];
					@$tdtsatuanthnini[$nourut]+=$dtsatuanthnini[$nourut][$per];
				}
			}
			
			#= ytd tahun ini
			$style="style=background-color:#ccfffd;";
			switch($nourut){
				// case'11101':
				// case'11102':
				// case'11103':
				// case'11104':
				// case'11199': 
					// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtsatuanthnini[$nourut])."</td>";
				// break;
				// case'11299':
				// case'11399':
				// case'12999':
					// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtsatuanthnini[$nourut])."</td>";
					
				// break;
				#= pabrik
				case'21001':
					#= budget satuan kolom AOP YTD
					$dtsatuanthninibgtytd[$nourut]=$dtthninibgtytd[$nourut]/$dtthninibgtytd['43000'];
					$dtsatuanthninibgt[$nourut]=$dtthninibgt[$nourut]/$dtthninibgt['43000'];
				
					$ttdtthnini[$nourut]=$tdtthnini[$nourut]/$tdtthnini['43000'];
					$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($ttdtthnini[$nourut])."</td>";
				break;	
				case'21002':
					#= budget satuan kolom AOP YTD
					$dtsatuanthninibgtytd[$nourut]=$dtthninibgtytd[$nourut]/$dtthninibgtytd['41000'];
					$dtsatuanthninibgt[$nourut]=$dtthninibgt[$nourut]/$dtthninibgt['41000'];
					
					$ttdtthnini[$nourut]=$tdtthnini[$nourut]/$tdtthnini['41000'];
					$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($ttdtthnini[$nourut])."</td>";
				break;	
				// case'21003':
				// case'21999':
					// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnini[$nourut]/$tdtthnini['49999'])."</td>";
				// break;
				default:
				
					if(substr($nourut,0,3)=='111'){
						
						#= budget satuan kolom AOP YTD
						$dtsatuanthninibgtytd[$nourut]=$dtthninibgtytd[$nourut]/$dtthninibgtytd['35000'];
						$dtsatuanthninibgt[$nourut]=$dtthninibgt[$nourut]/$dtthninibgt['35000'];
						
						$ttdtthnini[$nourut]=$tdtthnini[$nourut]/$tdtthnini['35000'];
						$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($ttdtthnini[$nourut])."</td>";
					}elseif(substr($nourut,0,1)=='1'){
						
						#= budget satuan kolom AOP YTD
						$dtsatuanthninibgtytd[$nourut]=$dtthninibgtytd[$nourut]/$dtthninibgtytd['51000'];
						$dtsatuanthninibgt[$nourut]=$dtthninibgt[$nourut]/$dtthninibgt['51000'];
						
						$ttdtthnini[$nourut]=$tdtthnini[$nourut]/$tdtthnini['51000'];
						$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($ttdtthnini[$nourut])."</td>";
					}else if(substr($nourut,0,1)=='2'){
						
						#= budget satuan kolom AOP YTD
						$dtsatuanthninibgtytd[$nourut]=$dtthninibgtytd[$nourut]/$dtthninibgtytd['49999'];
						$dtsatuanthninibgt[$nourut]=$dtthninibgt[$nourut]/$dtthninibgt['49999'];
						
						$ttdtthnini[$nourut]=$tdtthnini[$nourut]/$tdtthnini['49999'];
						$streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($ttdtthnini[$nourut])."</td>";
					}
				break;
				
				// $streamrpsatuan.="<td align=right ".$style.">".@hidezerodecimal($tdtthnlalu[$nourut]/$tdtthnlalu['35000'])."</td>"; 
			}
			
			#= act / realisasi ytd tahun ini
			$a1=$ttdtthnini[$nourut];
			
			#= act / realisasi ytd tahun lalu
			$a3=$tdtsatuanthnlaluytd[$nourut];
			
			#= VARIANCE ACT Vs LY
			$var1=((($a1-$a3)/$a3)*100);
			
			#=budget ytd tahun ini / AOP  ytd
			$b1=$dtsatuanthninibgtytd[$nourut];
			
			#= budget tahun ini / aop fy
			$b2=$dtsatuanthninibgt[$nourut];
			
			#=VARIANCE ACT Vs AOP | aktual tahun ini ytd vs bgt tahun ini ytd
			$var2=((($a1-$b1)/$b1)*100);
			
			#=VARIANCE ACT Vs AOP FY | aktual tahun ini ytd vs budget tahun ini 
			$var3=((($a1-$b2)/$b2)*100);
			
			#cobaa
			$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($var1),2) ." %</td>"; 
			$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($b1)) ."</td>";   
			$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($var2),2) ." %</td>"; 
			$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($b2)) ."</td>";   
			$streamrpsatuan.="<td align=right ".$style." style=background-color:#ccfffd>".hidezerodecimal(fixnan($var3),2) ." %</td>"; 
			
			
		}
		$streamrpsatuan.="</tr>";	
	}
}
$streamrpsatuan.="</table>";


// echo"<pre>";
// print_r($dtthninibgtytd);
// echo"<pre>";

#==========================================================================
#============= GC NOTES ===================================================
#==========================================================================
$steamnotes='';
// $steamnotes.=$judul;
if($param['tipe']=='excel'){	
	$steamnotes.=$judul;
}else{
	$steamnotes.="<tr class=rowcontent>";
	$steamnotes.="<td align=center style=background-color:cyan colspan='".($cspan)."'><b>GC</b></td>"; 
	$steamnotes.="</tr>";
}
foreach($arrnourut as $nourut){
	$style='';
	if($detail[$nourut]=='1'){
		$steamnotes.="<tr class=rowcontent>";
			if($tipenourut[$nourut]=='Header'){
				$steamnotes.="<td align=left colspan='".($cspan)."'><b>".$namanourut[$nourut]."</b></td>"; 
			}
			if($tipenourut[$nourut]=='Detail'){
				$steamnotes.="<td align=left style='width:1%'>  ".$nourut."</td>"; 
				$steamnotes.="<td align=left style='width:30%'>  ".$namanourut[$nourut]."</td>"; 
			}
			if($tipenourut[$nourut]=='Total'){
				@$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
				$steamnotes.="<td align=left  style='width:2%'> </td>"; 
				$steamnotes.="<td align=left ".$style."><b>  ".$namanourut[$nourut]."</b></td>"; 
			}
			#= data untuk variancenya
			#= tahun sekarang
			$tdtthnini=$tdtthnlalu=array();
			if($tipenourut[$nourut]=='Total' || $tipenourut[$nourut]=='Detail'){
				$sty="style=background-color:#ccfffd;";
				
				#= akumulasi tahun lalu
				foreach($arrperlalu as $per){
					if ($per<=$periodlalu) {
						$dtthnlaluytdgc[$nourut]['thnlalu']+=$dtthnlalu[$nourut][$per];
					}
					
					$expper = explode("-",$per);
					$sty="";
					if(substr($expper[1],0,1)=="Q"){
						$sty="style=background-color:#ccfffd;";
					}
					$steamnotes.="<td align=right ".$style." ".$sty.">".@hidezerodecimal($dtthnlalu[$nourut][$per])."</td>"; 
					if(substr($expper[1],0,1)!="Q"){
						@$tdtthnlalu[$nourut]+=$dtthnlalu[$nourut][$per];
					}
				}
				#= akumulasi tahun lalu
				$steamnotes.="<td align=right ".$style." ".$sty."> ".@hidezerodecimal($tdtthnlalu[$nourut])."</td>"; 
				$steamnotes.="<td align=right ".$style." ".$sty."> ".@hidezerodecimal($dtthnlalubgtytd[$nourut])."</td>"; 
				$steamnotes.="<td align=right ".$style." ".$sty."> ".@hidezerodecimal($dtthnlalubgt[$nourut])."</td>";  
				foreach($arrper as $per){
					$expper = explode("-",$per);
					$sty="style=cursor:pointer;";
					if(substr($expper[1],0,1)=="Q"){
						$sty="style=background-color:#ccfffd;cursor:pointer;";
					}
					$steamnotes.="<td align=right ".$style." ".$sty." title='Click untuk melihat detail' onclick=\"detail('".$nourut."','".$per."','".$param['kodept']."','".$regional."','".$param['kodeunit']."','html','event');\">".@hidezerodecimal($dtthnini[$nourut][$per])."</td>"; 
					if(substr($expper[1],0,1)!="Q"){
						@$tdtthnini[$nourut]+=$dtthnini[$nourut][$per];
					}
				}
				
				
				/*
				$a1=$tdtthnini[$nourut];
				$a3=$arrytd[$nourut]['thnlalu'];
				$var1=((($a1-$a3)/$a3)*100);
				$var2=((($a1-$dtthninibgtytd[$nourut])/$dtthninibgtytd[$nourut])*100);
				$var3=((($a1-$dtthninibgt[$nourut])/$dtthninibgt[$nourut])*100);
				*/
				#= akumulasi tahun ini
				$gc1=$tdtthnini[$nourut];
				$gc2=$dtthnlaluytdgc[$nourut]['thnlalu'];
				$vargc1=((($gc1-$gc2)/$gc2)*100); 
				$vargc2=((($gc1-$dtthninibgtytd[$nourut])/$dtthninibgtytd[$nourut])*100);
				$vargc3=((($gc1-$dtthninibgt[$nourut])/$dtthninibgt[$nourut])*100);

				$sty="style=background-color:#ccfffd;";
				$steamnotes.="<td align=right ".$style." ".$sty."> ".@hidezerodecimal($tdtthnini[$nourut])."</td>"; 
				$steamnotes.="<td align=right ".$style." ".$sty."> ".hidezerodecimal(fixnan($vargc1),2)."%</td>";  
				$steamnotes.="<td align=right ".$style." ".$sty."> ".hidezerodecimal(fixnan($dtthninibgtytd[$nourut]))."</td>";  
				$steamnotes.="<td align=right ".$style." ".$sty."> ".hidezerodecimal(fixnan($vargc2),2)."%</td>"; 
				$steamnotes.="<td align=right ".$style." ".$sty."> ".hidezerodecimal(fixnan($dtthninibgt[$nourut]))."</td>";   
				$steamnotes.="<td align=right ".$style." ".$sty."> ".hidezerodecimal(fixnan($vargc3),2)."%</td>";  
			}
		$steamnotes.="</tr>";	
	}
}	
$steamnotes.="</table>";	



if($param['tipe']=='excel'){
	$nop=$judullaporan."_".$param['kodept']."_".$param['periode'].".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet("Rupiah_".$param['kodept']."_".$param['periode']."", $stream);
	$xls->addSheet("Rupiahpsatuan_".$param['kodept']."_".$param['periode']."", $streamrpsatuan);
	$xls->addSheet("GCnotes_".$param['kodept']."_".$param['periode']."", $steamnotes);
	// $xls->addSheet("Report", $tab2);
	$xls->headers($nop);
	echo $xls->buildFile();
} else if ($param['tipe']=='pdf') {
	$dompdf = new Dompdf();
	$dompdf->loadHtml($stream);
	$dompdf->setPaper('A4', 'landscape');
	$dompdf->render();
	$dompdf->stream("Neraca",array("Attachment"=>0));
} else {
	echo $stream.$steamnotes;
	// echo $streamrpsatuan;
	// echo $stream.$steamnotes;
	// echo $stream.$steamnotes;
	//echo $steamnotes;
	// echo $streamrpsatuan;
}


?>