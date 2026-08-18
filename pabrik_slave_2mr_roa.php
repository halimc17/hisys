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

$nmjenis = makeOption($dbname, 'pabrik_5mr_roa_jenis', 'jenis,nama');
$nmparameter = makeOption($dbname, 'pabrik_5mr_roa_parameter', 'parameter,nama');
$nmkomponen = makeOption($dbname, 'pabrik_5mr_roa_komponen', 'komponen,nama');


$str="select * from ".$dbname.".pabrik_5mr_roa_formatlaporan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$arrjenis[$bar['jenis']]=$bar['jenis'];
	$arrparameter[$bar['parameter']]=$bar['parameter'];
	$arrkomponen[$bar['komponen']]=$bar['komponen'];
	$dataparameter[$bar['jenis']][$bar['parameter']]=$bar['parameter'];
	$datakomponen[$bar['jenis']][$bar['parameter']][$bar['komponen']]=$bar['komponen'];
}

$str="select avg(nilai) as nilai,komponen,jenis,parameter from ".$dbname.".pabrik_mr_roa 
		where tanggal between '".$tgl1."' and '".$tgl2."' and unit='".$unit."' 
		group by jenis,parameter,komponen";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$nilai[$bar['jenis']][$bar['parameter']][$bar['komponen']]=$bar['nilai'];
}

foreach($arrjenis as $jenis){
	$stream.="<fieldset style=float:left;border:none>";
	$stream.="<table cellspacing=1 class=sortable cellpadding=1 border=0>";
	$stream.="<thead>";
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
	$stream.="<td colspan=2 align=center>Description</td>";
	$stream.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['ratarata']."</td>";
	$stream.="</tr></thead>";
	$stream.="<tr class=rowcontent>";
	$stream.="<td colspan=3 align=center><b>".$nmjenis[$jenis]."</b></td>";
	$stream.="<td align=center></td>";
	$stream.="<td align=center></td>";
	$noparameter=0;	
	foreach($arrparameter as $parameter){
		if($dataparameter[$jenis][$parameter]!=''){
			$noparameter++;
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$noparameter."</td>";
			$stream.="<td colspan=2 align=left>".$nmparameter[$parameter]."</td>";
			$stream.="<td align=center></td>";
			$stream.="<td align=center></td>";
			$stream.="</tr>";
			foreach($arrkomponen as $komponen){
				$nilai[$jenis][$parameter]['ZZZ']=0;
				$tnilai[$jenis][$parameter]+=$nilai[$jenis][$parameter][$komponen];
				$nilai[$jenis][$parameter]['ZZZ']=$tnilai[$jenis][$parameter];
				if($datakomponen[$jenis][$parameter][$komponen]!=''){
					$stream.="<tr class=rowcontent>";
					$stream.="<td align=center></td>";
					$stream.="<td align=center>-</td>";
					$stream.="<td align=left>".$nmkomponen[$komponen]."</td>";
					$stream.="<td align=center>%</td>";
					$stream.="<td align=right>".$nilai[$jenis][$parameter][$komponen]."</td>";
					$stream.="</tr>";
				}
			}
		}
	}
	$stream.="</tr>";
	$stream.="<tbody></table></fieldset>";
}

switch($proses){
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="RESAULT_OF_ANALYSIS_".$unit;
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