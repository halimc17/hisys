<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$traksiId = checkPostGet('traksiId','');
$periode = checkPostGet('periode','');
$afdId = checkPostGet('afdId','');

if($proses=='preview'||$proses=='excel'){

    if($traksiId!='')
    {
        $whr=" and  b.kodeorg='".$traksiId."'";
        $whrpab=" and kodeorg='".$traksiId."'";
    }
    if($afdId!='')
    {
        $whr=" and a.nospb like '%".$afdId."%'";
        $whrpab=" and nospb like '%".$afdId."%'";
    }

    if($periode=='')
    {
         exit("Error:Field Tidak Boleh Kosong");
    }
    $brd=0;
    if($proses=='excel')
    {
        $brd=1;
         $bgcoloraja="bgcolor=#DEDEDE align=center";
    }
	
$nospb=$afd=$tglkebun=$jjgkebun=$nospbkebun=array();	
	
#ambil spb kebun
$str="SELECT sum(a.kgwb) as kgwb, a.nospb,sum(a.jjg) as jjg,b.tanggal,substr(a.nospb,9,6) as afdeling,sum(kgwbnetto) as kgwbnetto, b.tahuntanam FROM ".$dbname.".kebun_spbdt_detail a
           left join ".$dbname.".kebun_spbht b on a.nospb=b.nospb where b.tanggal like '".$periode."%' ".$whr." group by a.nospb
           order by tanggal,nospb";
		  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	setIt($nospb[$bar->nospb],'');
	setIt($afd[$bar->nospb],'');
	setIt($tglkebun[$bar->nospb],'');	
	setIt($jjgkebun[$bar->nospb],0);
	setIt($nospbkebun[$bar->nospb],'');
	setIt($kgkebun[$bar->nospb],'');
	setIt($kgkebunnetto[$bar->nospb],'');
	setIt($tahuntanam[$bar->nospb],'');
    @$kgkebunnetto[$bar->nospb]+=$bar->kgwbnetto.' ';
    $nospb[$bar->nospb].=$bar->nospb.' ';
    $afd[$bar->nospb].=$bar->afdeling.' ';
    $tglkebun[$bar->nospb].=$bar->tanggal.' ';
    $jjgkebun[$bar->nospb]+=$bar->jjg.' ';
    $nospbkebun[$bar->nospb].=$bar->nospb.' ';
    $tahuntanam[$bar->nospb].=$bar->tahuntanam.' ';
    @$kgkebun[$bar->nospb]+=$bar->kgwb.' ';
}

#ambil  spb timbangan

$str="select nokendaraan,supir,nospb,(jumlahtandan1+jumlahtandan2+jumlahtandan3) as jjgpabrik,beratbersih as beratnormal,(beratbersih-kgpotsortasi) as beratnormalx,
                notransaksi,left(tanggal,10) as tanggal,(beratmasuk-beratkeluar) as beratbersih from ".$dbname.".pabrik_timbangan where 
          left(tanggal,10)!='' ".$whrpab." and nospb!='' and millcode = 'EXTM'
          and left(tanggal,10) like '".$periode."%' order by tanggal,nospb";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res->fetch()){
	setIt($nospb[$bar1->nospb],'');
	setIt($tglpabrik[$bar1->nospb],'');
	setIt($tiket[$bar1->nospb],'');
	setIt($nokendaraanx[$bar1->nospb],'');
	setIt($supirx[$bar1->nospb],'');
	setIt($beratbersih[$bar1->nospb],0);
	setIt($jjgpabrik[$bar1->nospb],'');
	setIt($nosppabrik[$bar1->nospb],'');
	setIt($beratnormal[$bar1->nospb],'');
    $nospb[$bar1->nospb].=$bar1->nospb.' ';
    $beratnormal[$bar1->nospb].=$bar1->beratnormal.' ';
    $tglpabrik[$bar1->nospb].=$bar1->tanggal.' ';
    $tiket[$bar1->nospb].=$bar1->notransaksi.' ';   
    $nokendaraanx[$bar1->nospb].=$bar1->nokendaraan.' ';   
    $supirx[$bar1->nospb].=$bar1->supir.' ';   
    $beratbersih[$bar1->nospb]+=$bar1->beratbersih.' ';  
    @$jjgpabrik[$bar1->nospb]+=$bar1->jjgpabrik.' ';    
    $nosppabrik[$bar1->nospb].=$bar1->nospb.' ';   
}
$bgcoloraja="";
$tab="
<table class=sortable cellspacing=1 cellpadding=5 border=".$brd." style='width:100%'>
<thead>
<tr>
	<th align=center colspan=8>".$_SESSION['lang']['kebun']."</th>
	<th align=center colspan=8>".$_SESSION['lang']['pabrik']."</th>
	<th align=center colspan=3>".$_SESSION['lang']['varian']."</th>
	<th align=center colspan=2>".$_SESSION['lang']['varian']." Persentase <br/> (%)</th>
</tr>
            <tr class=rowheader>
            <th align=center ".$bgcoloraja.">No</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['divisi']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['nospb']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['tglNospb']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['tahuntanam']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['jjg']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['kg']." Bruto</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['kg']." Netto</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['tanggal']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['nospb']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['notransaksi']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['sopir']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['nopol']."</th>
			<th align=center ".$bgcoloraja.">".$_SESSION['lang']['jjg']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['kg']." Bruto</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['kg']." Netto</th>
			<th align=center ".$bgcoloraja.">".$_SESSION['lang']['jjg']."</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['kg']." Bruto</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['kg']." Netto</th>
            
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['kg']." Bruto</th>
            <th align=center ".$bgcoloraja.">".$_SESSION['lang']['kg']." Netto</th>
            </tr>
</thead><tbody>";

// echo "<pre>";
// print_r ($nospbkebun);
// echo "</pre>";

if(isset($nospb)){
    $no=0;
    foreach($nospb as $spb=>$val){
        $no++;
		if(!isset($nospbkebun[$spb])){
            $colorkebun="red title=\"SPB Kebun belum di input.\"";
        } else { 
			$colorkebun='#D1E3BA';
		}
        if(!isset($nosppabrik[$spb])){
            $colorpabrik="red title=\"SPB di PKS tidak ada, pastikan penulisan SPB di kebun sama antara nomor dan divisinya dengan PKS.\"";
        } else { 
			$colorpabrik='#CEDCDE';
		}
		
		setIt($afd[$spb],'');
		setIt($nospbkebun[$spb],'');
		setIt($tglkebun[$spb],'');
		setIt($jjgkebun[$spb],0);
		setIt($tglpabrik[$spb],'');
		setIt($nosppabrik[$spb],'');
		setIt($tiket[$spb],'');
		setIt($beratbersih[$spb],0);
		setIt($jjgpabrik[$spb],0);
		
		setIt($totaljjgkebun,0);
		setIt($totalberatbersih,0);
		setIt($totaljjgpabrik,0);
		
		$varjjg=0;$colorvarjjg='';
		$varjjg=$jjgkebun[$spb]-$jjgpabrik[$spb];
		if(abs($varjjg)>=1){
			$colorvarjjg="bgcolor=red";
		}
		
		$varkg=0;$colorvarkg='';
		@$varkg=$kgkebun[$spb]-$beratbersih[$spb];
		if(abs($varkg)>=1){
			$colorvarkg="bgcolor=red";
		}
		
		$varkgnetto=0;$colorvarkgnetto='';
		@$varkgnetto=$kgkebunnetto[$spb]-$beratnormal[$spb];
		if(abs($varkgnetto)>=1){
			$colorvarkgnetto="bgcolor=red";
		}   

        # {Persentase}
		$varkgpersentase=0;$colorvarkgpersentase='';
		@$varkgpersentase=$varkg/$beratbersih[$spb];
		if(abs($varkgpersentase)>=1){
			$colorvarkgpersentase="bgcolor=red";
		}
		
		$varkgnettopersentase=0;$colorvarkgnettopersentase='';
		@$varkgnettopersentase=$varkgnetto/$beratnormal[$spb];
		if(abs($varkgnettopersentase)>=1){
			$colorvarkgnettopersentase="bgcolor=red";
		}
		
		$tab.="<tr class=rowcontent>
			<td align=center >".$no."</td>
			<td bgcolor=".$colorkebun.">".$afd[$spb]."</td>
			<td bgcolor=".$colorkebun.">".$nospbkebun[$spb]."</td>
			<td bgcolor=".$colorkebun.">".tanggalnormal($tglkebun[$spb])."</td>
			<td bgcolor=".$colorkebun." align=center>".$tahuntanam[$spb]."</td>
			<td bgcolor=".$colorkebun." align=right>".@number_format($jjgkebun[$spb])."</td> 
			<td bgcolor=".$colorkebun." align=right>".@number_format($kgkebun[$spb])."</td> 
			<td bgcolor=".$colorkebun." align=right>".@number_format($kgkebunnetto[$spb])."</td> 
			<td bgcolor=".$colorpabrik.">".$tglpabrik[$spb]."</td>
			<td bgcolor=".$colorpabrik.">".$nosppabrik[$spb]."</td>
			<td  align=center bgcolor=".$colorpabrik.">".$tiket[$spb]."</td>
			<td  align=left bgcolor=".$colorpabrik.">".$supirx[$spb]."</td>
			<td  align=left bgcolor=".$colorpabrik.">".$nokendaraanx[$spb]."</td>
			<td align=right bgcolor=".$colorpabrik.">".@number_format($jjgpabrik[$spb])."</td>
			<td align=right bgcolor=".$colorpabrik.">".@number_format($beratbersih[$spb])."</td>
			<td align=right bgcolor=".$colorpabrik.">".@number_format($beratnormal[$spb])."</td>
			
			<td ".$colorvarjjg." align=right>".@number_format($varjjg)."</td> 
			<td ".$colorvarkg." align=right>".@number_format($varkg)."</td> 
			<td ".$colorvarkgnetto." align=right>".@number_format($varkgnetto)."</td> 

            <td ".$colorvarkgpersentase." align=right>".@number_format($varkgpersentase,2)."%</td> 
			<td ".$colorvarkgnettopersentase." align=right>".@number_format($varkgnettopersentase,2)."%</td> 
			
			</tr>";        
		
		
		@$totaljjgkebun+=$jjgkebun[$spb];
		@$totalkgkebun+=$kgkebun[$spb];
		@$totalberatbersih+=$beratbersih[$spb];
		@$totaljjgpabrik+=$jjgpabrik[$spb];     
		@$totalkgkebunnetto+=$kgkebunnetto[$spb];     
		@$totalberatnormal+=$beratnormal[$spb];     
    }
          $tab.="<tr class=rowcontent>
            
            <td colspan=5 align=center bgcolor=".$colorkebun.">Total</td>
            <td bgcolor=".$colorkebun." align=right>".number_format($totaljjgkebun)."</td> 
            <td bgcolor=".$colorkebun." align=right>".number_format($totalkgkebun)."</td> 
            <td bgcolor=".$colorkebun." align=right>".number_format($totalkgkebunnetto)."</td> 
            
            <td colspan=5 align=center bgcolor=".$colorpabrik.">Total</td>
            
            <td align=right bgcolor=".$colorpabrik.">".number_format($totaljjgpabrik)."</td>
            <td align=right bgcolor=".$colorpabrik.">".number_format($totalberatbersih)."</td>
            <td align=right bgcolor=".$colorpabrik.">".number_format($totalberatnormal)."</td>
			
			<td align=right>".number_format($totaljjgkebun-$totaljjgpabrik)."</td> 
            <td align=right>".number_format($totalkgkebun-$totalberatbersih)."</td> 
            <td align=right>".number_format($totalkgkebunnetto-$totalberatnormal)."</td> 
			
            <td align=center bgcolor=".$colorpabrik."></td>
            <td align=center bgcolor=".$colorpabrik."></td>

			</tr>";                
    
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
        $nop_="spbvstimbangan__".$traksiId."__".$periode;
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

        case'getPrd':
            //$traksiId
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select distinct left(tanggal,7) as periode from ".$dbname.".kebun_spbht 
               where kodeorg = '".$traksiId."' order by left(tanggal,7) desc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $optPeriode.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
        }
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
               where induk = '".$traksiId."' and tipe='afdeling' order by namaorganisasi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){            
			$optAfd.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
        }
        echo $optPeriode."####".$optAfd;
        break;
        default:
        break;
}
?>