<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$kebun = checkPostGet('kebun3','');
$afdeling = checkPostGet('afdeling3','');
$periode = checkPostGet('periode3','');

if($proses=='preview'||$proses=='excel'){
    if($periode==''||$kebun==''){
        exit("Error: All field required");
    }
    
    $brd=0;
	$bgcoloraja="";
    if($proses=='excel'){
        $brd=1;
        $bgcoloraja="bgcolor=#DEDEDE align=center";
    }
    
    #ambil data taksasi
    $sTaksasi="select afdeling, tanggal, blok, seksi, hasisa, haesok, jmlhpokok, persenbuahmatang, jjgmasak, jjgoutput, hkdigunakan, bjr, (bjr*jjgmasak) as kg from ".$dbname.".kebun_taksasi 
        where afdeling like '".$kebun."%' and afdeling like '%".$afdeling."%' and tanggal like '".$periode."%' 
        order by tanggal, blok";
	$restaksasi=$owlPDO->query($sTaksasi) or die(print " Gagal: ".PDOException::getMessage());
	$restaksasi->setFetchMode(PDO::FETCH_OBJ);
    while($bar1=$restaksasi->fetch()){        
        $kunci=$bar1->tanggal.$bar1->blok;
        
        @$kurangdarienam=($bar1->haesok+$bar1->hasisa)/$bar1->hkdigunakan;
        if($kurangdarienam <= 6){
            $bisapanen=$bar1->hkdigunakan;
        }else{
            $bisapanen=ceil(($bar1->haesok+$bar1->hasisa)/6);
        }
		setIt($dzArr[$kunci]['hkpanen'],0);
		setIt($dzArr[$kunci]['counter'],0);
		setIt($dzArr[$kunci]['blok'],'');
		setIt($dzArr[$kunci]['seksi'],'');
		setIt($dzArr[$kunci]['hasisa'],0);
		setIt($dzArr[$kunci]['haesok'],0);
		setIt($dzArr[$kunci]['jmlhpokok'],0);
		setIt($dzArr[$kunci]['pbm'],0);
		setIt($dzArr[$kunci]['jjgmasak'],0);
		setIt($dzArr[$kunci]['hkdigunakan'],0);
		setIt($dzArr[$kunci]['kg'],0);
		setIt($dzArr[$kunci]['tanggal'],'');
        $dzArr[$kunci]['hkpanen']+=$bisapanen;    
        $dzArr[$kunci]['counter']+=1; // jumlahdata
        $dzArr[$kunci]['afdeling']=$bar1->afdeling;
        $dzArr[$kunci]['blok'].=$bar1->blok;
        $dzArr[$kunci]['tanggal'].=$bar1->tanggal;
        $dzArr[$kunci]['seksi'].=$bar1->seksi;
        $dzArr[$kunci]['hasisa']+=$bar1->hasisa;
        $dzArr[$kunci]['haesok']+=$bar1->haesok;
        $dzArr[$kunci]['jmlhpokok']+=$bar1->jmlhpokok;
        $dzArr[$kunci]['pbm']+=$bar1->persenbuahmatang;
        @$dzArr[$kunci]['persenbuahmatang']=$dzArr[$kunci]['pbm']/$dzArr[$kunci]['counter'];
        $dzArr[$kunci]['jjgmasak']+=$bar1->jjgmasak;
//        $dzArr[$kunci]['jjgoutput']+=$bar1->jjgoutput;
        $dzArr[$kunci]['hkdigunakan']+=$bar1->hkdigunakan;
        $dzArr[$kunci]['kg']+=$bar1->kg;
        @$dzArr[$kunci]['bjr']=$dzArr[$kunci]['kg']/$dzArr[$kunci]['jjgmasak'];
    }
    
	$tab="";
    if($proses=='excel'){
        $tab.= $_SESSION['lang']['laporan']." ".$_SESSION['lang']['rencanapanen']." ".$_SESSION['lang']['harian']."<br>Kebun: ".$kebun." ".$afdeling." ".$periode." ";
    }
    $tab.="
    <table cellpadding=5 cellspacing=1 border=".$brd." class=sortable>
    <thead>
    <tr>
        <th ".$bgcoloraja.">".$_SESSION['lang']['tanggal']."</th>";
        $tab.="<th ".$bgcoloraja.">".$_SESSION['lang']['section']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['blok']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['hasisa']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['haesok']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jumlahha']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jmlhpokok']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['persenbuahmatang']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jjgmasak']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jjgoutput']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jhk']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['bjr']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['taksasi']." (kg)</th>";
        $tab.="
    </tr>
    </thead>
    <tbody>";
    
    // With timestamp, this gets last day of April 2010 'Y-m-t'
//    $tanggalterakhir = date('t', strtotime($periode.'-01'));
    
        
        foreach($dzArr as $datanya){
        $tab.="<tr class=rowcontent>";
			setIt($datanya['kg'],0);
			setIt($datanya['hasisa'],0);
			setIt($datanya['haesok'],0);
			setIt($datanya['jmlhpokok'],0);
			setIt($datanya['jjgmasak'],0);
			setIt($datanya['hkdigunakan'],0);
			setIt($datanya['hkpanen'],0);
			setIt($datanya['bjr'],0);
			setIt($datanya['kg'],0);
			if($proses!='excel')$tab.="<td align=right>".tanggalnormal($datanya['tanggal'])."</td>";
            else $tab.="<td align=right>".$datanya['tanggal']."</td>";
            $jumlahha=$datanya['hasisa']+$datanya['haesok'];
            @$pbm=$datanya['jjgmasak']*100/$datanya['jmlhpokok'];
            @$jjgoutput=$datanya['jjgmasak']/$datanya['hkdigunakan'];
            $tab.="<td>".$datanya['seksi']."</td>
            <td>".getNamaOrg($datanya['blok'])."</td>
            <td align=right>".number_format($datanya['hasisa'],2)."</td>
            <td align=right>".number_format($datanya['haesok'],2)."</td>
            <td align=right>".number_format($jumlahha,2)."</td>
            <td align=right>".number_format($datanya['jmlhpokok'])."</td>
            <td align=right>".number_format($pbm,2)."</td>
            <td align=right>".number_format($datanya['jjgmasak'])."</td>
            <td align=right>".number_format($jjgoutput)."</td>
            <td align=right>".number_format($datanya['hkdigunakan'])."</td>
            <td align=right>".number_format($datanya['bjr'],2)."</td>
            <td align=right>".number_format($datanya['kg'])."</td>";            
        $tab.="</tr>";                        
                    
        }
           
    $tab.="</tbody></table></td></tr></tbody><table>";

}	
switch($proses)
{
    case'preview':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="taksasi_pertgl_perblok".$kebun."_".$afdeling."_".$periode;
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