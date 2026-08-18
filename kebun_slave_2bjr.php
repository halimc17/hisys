<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');



$proses=checkPostGet('proses','');
$kdOrg=checkPostGet('kdOrg','');
$per=checkPostGet('per','');


function dates_inbetween($date1, $date2){

    $day = 60*60*24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);

    for($x = 1; $x < $days_diff; $x++){
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }

    $dates_array[] = date('Y-m-d',$date2);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[] = date('Y-m-d',$date1);        
    }
    return $dates_array;
}


$iTgl="select * from ".$dbname.".setup_periodeakuntansi where periode='".$per."' and kodeorg='".$kdOrg."' ";
$nTgl=$owlPDO->query($iTgl) or die(print " Gagal: ".PDOException::getMessage());
$nTgl->setFetchMode(PDO::FETCH_ASSOC);
$dTgl=$nTgl->fetch();
    $tgl1=$dTgl['tanggalmulai'];
    $tgl2=$dTgl['tanggalsampai'];



$test = dates_inbetween($tgl1, $tgl2);

/*echo"<pre>";
print_r($test);
echo"</pre>";*/

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$noAfd=0;
$iAfd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kdOrg."' and tipe='afdeling' ";
$nAfd=$owlPDO->query($iAfd) or die(print " Gagal: ".PDOException::getMessage());
$nAfd->setFetchMode(PDO::FETCH_NUM);
while($dAfd=$nAfd->fetch())
{
    $noAfd+=1;
    $kdAfd[$dAfd['kodeorganisasi']]=$dAfd['kodeorganisasi'];
    $nmAfd[$dAfd['kodeorganisasi']]=$dAfd['namaorganisasi'];
}

$iBjr="SELECT sum(a.totalkg)/sum(a.jjg) as bjr,tanggal,substr(blok,1,6) as afdeling
               FROM ".$dbname.".`kebun_spbdt` a left join ".$dbname.".kebun_spbht b on 
               a.nospb=b.nospb where blok like '%".$kdOrg."%'
               and tanggal like '%".$per."%' group by substr(blok,1,6),tanggal order by tanggal,substr(blok,1,6) asc";
$nBjr=$owlPDO->query($iBjr) or die(print " Gagal: ".PDOException::getMessage());
$nBjr->setFetchMode(PDO::FETCH_NUM);
while($dBjr=$nBjr->fetch())
{
    $bjr[$dBjr['tanggal']][$dBjr['afdeling']]=$dBjr['bjr'];
}




if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<table class=sortable cellspacing=1>";
}

$stream.="<thead class=rowheader>
    <tr class=rowheader>
       <td bgcolor=#CCCCCC rowspan=2 align=center>Tanggal</td>
       <td bgcolor=#CCCCCC colspan=".$noAfd." align=center>".$nmOrg[$kdOrg]."</td>
    </tr>
    <tr>";
   foreach($kdAfd as $afd)
   { 
       $stream.="<td bgcolor=#CCCCCC align=center>".$nmAfd[$afd]."</td>";
   }
   $stream.="</tr>";
   $stream.="</thead>";
   

   foreach($test as $ar => $isi)
    {
            $qwe=date('D', strtotime($isi));
            if($qwe=='Sun') 
            {
                $stream.="<tr class=rowcontent>";
                $stream.="<td align=center><font color=red>".substr($isi,8,2)."</font></td>";
                foreach($kdAfd as $afd)
                { 
                    setIt($bjr[$isi][$afd],'');
                    if($bjr[$isi][$afd]=='')
                        $isiBjr="";
                    else
                        $isiBjr=$bjr[$isi][$afd];
                    $stream.="<td  align=center>".number_format((float)$isiBjr,2)."</td>";
                }
                $stream.="</tr>";
            }
            else
            {   
                $stream.="<tr class=rowcontent>";
                 $stream.="<td align=center>".substr($isi,8,2)."</font></td>"; 
                 foreach($kdAfd as $afd)
                { 
                     setIt($bjr[$isi][$afd],'');
                     
                     
                      if($bjr[$isi][$afd]=='')
                      {
                        $isiBjr="";
                      }
                      else
                      {
                        $isiBjr=$bjr[$isi][$afd];
                      }
                    $stream.="<td  align=center>".number_format((float)$isiBjr,2)."</td>";
                }
                $stream.="</tr>";
            }
    }


$stream.="</thead>";
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
		$nop_="lapora_Rekap_Gaji_".$pt."_".$per;
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
			//closedir($handle);
		}     
		break;	
}
?>