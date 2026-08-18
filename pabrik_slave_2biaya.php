<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

/*
$proses=$_GET['proses'];
$kdorg=$_POST['kdorg'];
$per=$_POST['per'];
if($proses=='excel'){
    $kdorg=$_GET['kdorg'];
    $per=$_GET['per'];
}
*/

$proses = checkPostGet('proses','');
$kdorg = checkPostGet('kdorg','');
$per = checkPostGet('per','');


$tahun = substr($per, 0,4);
$arrper=month_inbetween($tahun.'-01',$per);
// echo"<pre>";
// print_r($arrper);

// echo $kdorg;

#= sortiran apakah pabrik / bulking
$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tipeorg[$bar['kodeorganisasi']]=$bar['tipe'];
}

// echo"<pre>";
// print_r($tipeorg);
// echo"</pre>";
/*
if($kdorg==''){
    $kdorgx ="kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING'))";
} elseif ($kdorg==''){
    $kdorgx="kodeorganisasi ='".$kdorg."'";
} else {
    $kdorgx="induk ='".$kdorg."'";
}
*/

if($kdorg==''){
    $kdorgx ="kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING'))";
} else {
    $kdorgx="induk ='".$kdorg."' or kodeorganisasi='".$kdorg."'";
}

if($kdorg==''){
    $kdorgy ="kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING'))";
} else {
    $kdorgy="kodeorg ='".$kdorg."'";
} 

if($tipeorg[$kdorg]==''){
	$akunsort=" and (left(noakun,1)='7' or left(noakun,1)='6' or left(noakun,1)='8')";
}elseif ($tipeorg[$kdorg]=='BULKING'){
	$akunsort=" and (left(noakun,1)='8')";
}else{
    $akunsort=" and (left(noakun,1)='7' or left(noakun,1)='6') and noakun not like '641%' ";
}
// echo $akunsort;
$namaakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<table class=sortable cellspacing=1 width=80%>";
}

$stream.="<thead class=rowheader>
    <tr class=rowheader>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['station']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['noakun']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namaakun']."</td>
       <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jumlah']."</td> 
       <td bgcolor=#CCCCCC align=center>todate</td>    
       ";
$stream.="</tr>";
$stream.="</thead>";




$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$kdorgx." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $org[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
    $nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];  
}

$org['']='';





/*
$iAkun="select distinct(noakun) as noakun from ".$dbname.".keu_jurnaldt_vw "
        . " where kodeorg='".$kdorg."' and periode='".$per."' order by noakun";
  */

//SUBSTR(pub_name,4,5)


if($kdorg==''){
	
	##akun digit 5
	$str="select substr(noakun,1,5) as noakunhead,kodeorg from ".$dbname.".keu_jurnaldt_vw where ".$kdorgy." and periode='".$per."' ".$akunsort."  group by substr(noakun,1,5),kodeorg order by noakun";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$akunhead[$bar['noakunhead']]=$bar['noakunhead'];
		$listakunhead[$bar['kodeorg']][$bar['noakunhead']]=$bar['noakunhead'];
	}
	
	#= list akun
	$str="select distinct(noakun) as noakun,kodeorg from ".$dbname.".keu_jurnaldt_vw "
        . " where ".$kdorgy." and periode='".$per."' ".$akunsort." order by noakun,kodeorg";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$akun[$bar['noakun']]=$bar['noakun'];
	}
	
	$str="select sum(debet)-sum(kredit) as jumlah,noakun,substr(noakun,1,5) as noakunhead,kodeorg 
			from ".$dbname.".keu_jurnaldt_vw
			where ".$kdorgy." and periode='".$per."' ".$akunsort." 
			 group by kodeorg,noakun  order by noakun";
			 // echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$akundata[$bar['kodeorg']][$bar['noakunhead']][$bar['noakun']]=$bar['noakun'];
		@$jumlah[$bar['kodeorg']][$bar['noakunhead']][$bar['noakun']]+=$bar['jumlah']; 
	}
	
	// if($_SESSION['standard']['username']=='tim.owl3'){
	// echo"<pre>";
	// print_r($str);
	// }

	$str="select sum(debet)-sum(kredit) as jumlah,noakun,substr(noakun,1,5) as noakunhead,kodeorg 
			from ".$dbname.".keu_jurnaldt_vw  
			where ".$kdorgy." and  periode in ('".implode("','",$arrper)."') ".$akunsort."  
			 group by kodeorg,noakun  order by noakun";
			 // echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		  $akundata[$bar['kodeorg']][$bar['noakunhead']][$bar['noakun']]=$bar['noakun'];
			@$jumlahtodate[$bar['kodeorg']][$bar['noakunhead']][$bar['noakun']]+=$bar['jumlah']; 
	}

} else {
	
   ##akun digit 5
	$str="select substr(noakun,1,5) as noakunhead,substr(kodeblok,1,6) as kodeblok "
			. " from ".$dbname.".keu_jurnaldt_vw where ".$kdorgy." and  periode in ('".implode("','",$arrper)."') ".$akunsort." "
			. " group by substr(noakun,1,5),substr(kodeblok,1,6) order by noakun";
			
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$akunhead[$bar['noakunhead']]=$bar['noakunhead'];
		$listakunhead[$bar['kodeblok']][$bar['noakunhead']]=$bar['noakunhead'];
	}

	#= list akun
	$str="select distinct(noakun) as noakun,substr(kodeblok,1,6) as kodeblok from ".$dbname.".keu_jurnaldt_vw "
        . " where ".$kdorgy." and  periode in ('".implode("','",$arrper)."') ".$akunsort." order by noakun,substr(kodeblok,1,6)";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$akun[$bar['noakun']]=$bar['noakun'];
	}
	
	
   $str="select sum(debet)-sum(kredit) as jumlah,noakun,substr(noakun,1,5) as noakunhead,substr(kodeblok,1,6) as kodeblok,kodeorg 
			from ".$dbname.".keu_jurnaldt_vw
			where ".$kdorgy." and periode='".$per."' ".$akunsort." 
			 group by substr(kodeblok,1,6),noakun  order by noakun";
			 // echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$akundata[$bar['kodeblok']][$bar['noakunhead']][$bar['noakun']]=$bar['noakun'];
		$jumlah[$bar['kodeblok']][$bar['noakunhead']][$bar['noakun']]=$bar['jumlah']; 
	}

	$str="select sum(debet)-sum(kredit) as jumlah,noakun,substr(noakun,1,5) as noakunhead,substr(kodeblok,1,6) as kodeblok,kodeorg 
			from ".$dbname.".keu_jurnaldt_vw  
			where ".$kdorgy." and  periode in ('".implode("','",$arrper)."') ".$akunsort."  
			 group by substr(kodeblok,1,6),noakun  order by noakun";
			 // echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		  $akundata[$bar['kodeblok']][$bar['noakunhead']][$bar['noakun']]=$bar['noakun'];
			$jumlahtodate[$bar['kodeblok']][$bar['noakunhead']][$bar['noakun']]=$bar['jumlah']; 
	}
}

//exit();



//blok~akun 5~akun 7

//  bgcolor=#66FF00
$grandTotal=0;

$grandTotaltodate=0;
foreach ($org as $kddiv){
    $subTotal[$kddiv]=0;
    $subTotaltodate[$kddiv]=0;
    if($kddiv==''){
        $nmorg[$kddiv]='Lain-Lain';
    }
    $stream.="<tr class=rowcontent>";
    $stream.="<td colspan=5><b>".$kddiv." - ".$nmorg[$kddiv]."</b></td>";
    $stream.="</tr>";	
    if(is_array($akunhead)){
		foreach($akunhead as $akunjudul){
			if(@$listakunhead[$kddiv][$akunjudul]!=''){
				$stream.="<tr class=rowcontent>";
				$stream.="<td colspan=5 bgcolor=#00FFFF>".$listakunhead[$kddiv][$akunjudul]." - ".@$namaakun[$listakunhead[$kddiv][$akunjudul]]."</td>"; 
				$stream.="</tr>";
			
				foreach ($akun as $noakun){
					if(@$akundata[$kddiv][$akunjudul][$noakun]!=''){
						//setIt($jumlah[$kddiv][$akunjudul][$noakun][1],0);
						//setIt($jumlah[$kddiv][$akunjudul][$noakun][2],0);
						$stream.="<tr class=rowcontent style=cursor:pointer; title='clickdetail' onclick=lihatDetail('".$noakun."','".$kddiv."','".$per."','".$kdorg."','html',event)>";
						$stream.="<td></td>";
						$stream.="<td>".$akundata[$kddiv][$akunjudul][$noakun]."</td>";
						$stream.="<td>".$namaakun[$akundata[$kddiv][$akunjudul][$noakun]]."</td>";
						$stream.="<td align=right>".number_format($jumlah[$kddiv][$akunjudul][$noakun],0)."</td>";
						$stream.="<td align=right>".number_format($jumlahtodate[$kddiv][$akunjudul][$noakun],0)."</td>"; 
						$stream.="</tr>";
					}
					@$total[$kddiv][$akunjudul]+=$jumlah[$kddiv][$akunjudul][$noakun];
                    @$totaltodate[$kddiv][$akunjudul]+=$jumlahtodate[$kddiv][$akunjudul][$noakun];
				}
				$stream.="<tr class=rowcontent>";#00FFFF
				$stream.="<td bgcolor=#00CCFF colspan=3 align=right>Total</td>";
				$stream.="<td bgcolor=#00CCFF align=right>".number_format($total[$kddiv][$akunjudul],0)."</td>"; 
				$stream.="<td bgcolor=#00CCFF align=right>".number_format($totaltodate[$kddiv][$akunjudul],0)."</td>";
               
				$stream.="</tr>";
			}
			@$subTotal[$kddiv]+=$total[$kddiv][$akunjudul];

            @$subTotaltodate[$kddiv]+=$totaltodate[$kddiv][$akunjudul];
			
		}
	}
    @$grandTotal+=$subTotal[$kddiv];

    @$grandTotaltodate+=$subTotaltodate[$kddiv];
    $stream.="<tr class=rowcontent>";
    $stream.="<td colspan=3 align=right  bgcolor=#0099FF>Sub Total</td>";
    $stream.="<td align=right bgcolor=#0099FF>".number_format($subTotal[$kddiv],0)."</td>"; 
    $stream.="<td align=right bgcolor=#0099FF>".number_format($subTotaltodate[$kddiv],0)."</td>"; 
    $stream.="</tr>";       
    
}
$stream.="<thead><tr class=rowheader>";
$stream.="<td colspan=3 align=right>Grand Total</td>";
$stream.="<td align=right>".number_format($grandTotal,0)."</td>"; 
$stream.="<td align=right>".number_format($grandTotaltodate,0)."</td>"; 
$stream.="</tr></thead>";   
//$stream.="<tr class=rowcontent>
//$stream.="

$stream.="<tbody></table>";
switch($proses)
{
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_";
        if(strlen($stream)>0)
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
                if(!fwrite($handle,$stream))
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