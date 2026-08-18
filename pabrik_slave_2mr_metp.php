<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

$nmkode=makeOption($dbname,'pabrik_5mr_metp','kode,nama');

$arrtgl=rangeTanggalarr($tgl1,$tgl2);
$spantgl=count($arrtgl);

$stream="";

//  style=width:300%
$stream.="<table cellspacing=1 class=sortable cellpadding=1 border=0>";
$stream.="<thead>";

$stream.="<tr>";
$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['nourut']."</td>";
$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</td>";
$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['kuantitas']."</td>";
$stream.="<td align=center colspan='".$spantgl."' align=center>PH</td>";
$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['rerata']."</td>";
$stream.="</tr>";

$stream.="<tr>";
foreach($arrtgl as $tgl){
	$stream.="<td align=center>".intval(substr(tanggalnormal($tgl),0,2))."</td>";
}
$stream.="</tr>";
$stream.="</thead>";



########################################################################################
#############prepare data
########################################################################################



##produksi
$str="select * from ".$dbname.".pabrik_mr_metp where tanggal between '".$tgl1."' and '".$tgl2."' and unit='".$unit."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$arrkode[$bar['kode']]=$bar['kode'];
	$nilai[$bar['kode']][$bar['tanggal']]=$bar['nilai'];
}


$str="select * from ".$dbname.".pabrik_5mr_metp";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$setup[$bar['kode']]=$bar['nilai'];
}


echo"<pre>";
//print_r($nilai);
echo"</pre>";
########################################################################################
#############tampilkan data
########################################################################################

foreach($arrkode as $kode){
	@$no+=1;
	$stream.="<tr class=rowcontent>";	
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td>".$nmkode[$kode]."</td>";
		$stream.="<td align=right>".number_format($setup[$kode],2)."</td>";
		foreach($arrtgl as $tgl){
			$stream.="<td align=right>".number_format($nilai[$kode][$tgl])."</td>";
			$tkode[$kode]+=$nilai[$kode][$tgl];
			//cari banyak data perkode
			if($nilai[$kode][$tgl]!=''){
				@$jumdata[$kode]+=1;
			}
		}
		$stream.="<td align=right>".number_format($tkode[$kode]/$jumdata[$kode])."</td>";
	$stream.="</tr>";	
}










$stream.="</table>";







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
        $nop_="MILL_EFFLUENT_TREATMENT_PLANT".$unit;
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