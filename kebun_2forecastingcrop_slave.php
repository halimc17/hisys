<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;
error_reporting(0);

$regional = checkPostGet('regional','');
$periode = checkPostGet('periode','');
$method = checkPostGet('method','');
$tipeprint= checkPostGet('tipeprint','');

$tabel_0='bgt_regional_assignment';
$tabel_1='setup_blok';
$tabel_2='pabrik_timbangan';
$tabel_3='bgt_produksi_kbn_kg_vw';
$tabel_4='bgt_blok';
$tabel_5='kebun_rencanapanen_vw';
$tabel_6='bgt_produksi_pks_vw';


switch ($method) {
	case 'preview':
		$tab = '';
		$rowspan = '';
        if ($periode=='') {
            exit("Warning Periode Kosong !");
        }
		
        $tahun=substr($periode,0,4);
        $tahunv2=substr($periode,2,2);

        $bulan=substr($periode,5,2);
        $col=$bulan-1;
        $plus1=$bulan+1;## 12
        $plusplus3=$bulan+3;##14
        $plus1_2=$plusplus3+1;##15
        $plusplus3_2=$plusplus3+3;##17

        $a=$plusplus3-$bulan;
        $b=$plusplus3_2-$plusplus3;


		


		if ($tipeprint=='excel') {
			$tab.= "<table border=1 class=sortable cellpading=0 cellspacing=1>";
		}else{
			$tab.= "<table border=0 class=sortable cellpading=0 cellspacing=1>";
		}
		$tab.= "<thead><tr class=rowheader >";
			$tab.= "<th align=center rowspan=3>".$_SESSION['lang']['nourut']."</th>";
			$tab.= "<th align=center rowspan=3>Regional</th>";
			$tab.= "<th align=center rowspan=3 >ESTATE</th>";
			$tab.= "<th align=center rowspan=3>This Month  Mature  Hectare</th>";
			$tab.= "<th align=center rowspan=2 colspan=".$col.">Actual in tonnes (T)</th>";
			$tab.= "<th align=center colspan=3>This Month</th>";
			$tab.= "<th align=center colspan=6>Year To Date Harvested</th>";
			$tab.= "<th align=center colspan=3>".$tahun." Annual</th>";
            if ($plus1<=12) {
                $cc=(13-$plus1)+2;
                if ($cc>5) {
                    $cc=5;
                }
                $tab.= "<th align=center colspan=".$cc.">Prediction Crop 1st 3 Months Ahead</th>";
            }
            if ($plus1_2<=12) {
                $cc1=(13-$plus1_2)+2;
                if ($cc1>5) {
                    $cc1=5;
                }
                $tab.= "<th align=center colspan=".$cc1.">Prediction Crop 2st 3 Months Ahead</th>";
            }
			$tab.= "<th align=center colspan=3>Predict. To dt</th>";
		$tab.= "</tr>";
        $tab.= "<tr class=rowheader >";
			$tab.= "<th align=center rowspan=2 >Actual in tonnes (T)</th>";
			$tab.= "<th align=center rowspan=2 >Budplan in tonnes (T)</th>";
			$tab.= "<th align=center rowspan=2 >% Difference</th>";

			$tab.= "<th align=center rowspan=2 >Actual in tonnes (T)</th>";
			$tab.= "<th align=center rowspan=2 >Budplan in tonnes (T)</th>";
			$tab.= "<th align=center rowspan=2 >% Difference</th>";
            $tab.= "<th align=center colspan=3>Tonnes per Ha</th>";

			$tab.= "<th align=center rowspan=2 >Matures Hectares</th>";
			$tab.= "<th align=center rowspan=2 >Budplan in tonnes (T)</th>";
			$tab.= "<th align=center rowspan=2 >Budplan in tonnes (T/Ha)</th>";

            if ($plusplus3<=12) {
                $plusplus3=$plusplus3;
            }else {
                $plusplus3=12;
            }

            for ($i=$plus1; $i <=$plusplus3 ; $i++) { 
                $tab.= "<th align=center rowspan=2 >".numToMonth($i,'E','short')." ".$tahunv2."</th>";
            }
			if ($plus1<=12) {
                $tab.= "<th align=center rowspan=2 >Crop (T)</th>";
                $tab.= "<th align=center rowspan=2 >Crop (T/Ha)</th>";
            }

            if ($plusplus3_2<=12) {
                $plusplus3_2=$plusplus3_2;
            }else {
                $plusplus3_2=12;
            }

            for ($i=$plus1_2; $i <=$plusplus3_2 ; $i++) { 
                // if ($i<=12) {
                //     $zero=$i;
                //     $tahun1=$tahun;
                //     $th=substr($tahun1,2,2);
                // }else {
                //     $zero=$thnbaru[$i];
                //     $tahun1=$tahunn+1;
                //     $th=substr($tahun1,2,2);
                // }
                $tab.= "<th align=center rowspan=2 >".numToMonth($i,'E','short')." ".$tahunv2."</th>";
            }
            if ($plus1_2<=12) {
                $tab.= "<th align=center rowspan=2 >Crop (T)</th>";
                $tab.= "<th align=center rowspan=2 >Crop (T/Ha)</th>";
            }

            $tab.= "<th align=center rowspan=2 >".$tahun."</th>";
            $tab.= "<th align=center rowspan=2 >'+/_ % Act vs Bud</th>";
            $tab.= "<th align=center rowspan=2 >Y/Ha</th>";
			
		$tab.= "</tr>";

        $tab.= "<tr class=rowheader >";
        for ($i=1; $i < $bulan; $i++) { 
			$tab.= "<th align=center >".numToMonth($i,'E','short')."</th>";
        }
            $tab.= "<th align=center >Actual </th>";
            $tab.= "<th align=center >Budplan</th>";
            $tab.= "<th align=center >Difference</th>";
		$tab.= "</tr>";

		$tab.= "</tehead>";
		$tab.= "<tbody>";

        // echo"<pre>";
        // print_r($kg);
        // echo"</pre>";
        // exit();

        ## START OF QUERIES ##  
 
        $arrayZero=array();
        $arrayZero[1]='01';
        $arrayZero[2]='02';
        $arrayZero[3]='03';
        $arrayZero[4]='04';
        $arrayZero[5]='05';
        $arrayZero[6]='06';
        $arrayZero[7]='07';
        $arrayZero[8]='08';
        $arrayZero[9]='09';
        $arrayZero[10]='10';
        $arrayZero[11]='11';
        $arrayZero[12]='12';

        $thnbaru=array();
        $thnbaru[13]='01';
        $thnbaru[14]='02';
        $thnbaru[15]='03';
        $thnbaru[16]='04';
        $thnbaru[17]='05';
        $thnbaru[18]='06';
        $thnbaru[19]='07';
        $thnbaru[20]='08';
        $thnbaru[21]='09';
        $thnbaru[22]='10';
        $thnbaru[23]='11';
        $thnbaru[24]='12';
 
        if ($regional != '') {
            @$wh0 .= " and subregional='".$regional."'";
            @$wh1 .= " and left(a.kodeorg,4)= ";
            @$wh2 .= " and left(kodeblok,4) in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where subregional='".$regional."') ";
            @$wh3 .= " and left(kodeorg,4) in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where subregional='".$regional."') ";
            @$wh4 .= " and kodeunit in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where subregional='".$regional."') ";
            
        }   
        #Ambil Subregional
        $str="select subregional,kodeunit from ".$dbname.".".$tabel_0."
        where 1=1 ".@$wh0." and kodeunit in 
        (select kodeorganisasi from ".$dbname.".organisasi where tipe='kebun')
        order by subregional desc;";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            @$arrReg[$bar['subregional']]=$bar['subregional'];
            @$arrKode[$bar['subregional']][$bar['kodeunit']]=$bar['kodeunit'];
            if ($regional != '') {
                $arrArr=" '".$arrKode[$bar['subregional']][$bar['kodeunit']]."'";
            }else{
                $arrArr="";
            }            

            # Luas Planted dari setup_blok    
            $str="select left(a.kodeorg,4) as kodeorg,a.luasareaproduktif,b.subregional
            from ".$dbname.".".$tabel_1." a 
            left join ".$dbname.".".$tabel_0." b on left(a.kodeorg,4) = b.kodeunit  
            where 1=1 ".$wh1." ".$arrArr." order by a.kodeorg desc ;";

            $intiplasma23=makeOption($dbname,'pabrik_timbangan','kodeorg,intex',"kodeorg='".$arrKode[$bar['subregional']][$bar['kodeunit']]."'");
            $res = fetchdata($str);
            foreach ($res as $bar2) {
                @$arr[$bar2['subregional']][$intiplasma23[$bar2['kodeorg']]][$bar2['kodeorg']]=$bar2['kodeorg'];
                @$luas[$bar2['subregional']][$intiplasma23[$bar2['kodeorg']]][$bar2['kodeorg']]+=$bar2['luasareaproduktif'];
                @$arrr[$bar2['subregional']][$bar2['kodeorg']]=$bar2['kodeorg'];
            }
        }
        // echo"<pre>";
        // print_r($arrReg);
        // echo"</pre>";
        // exit();
        # Ambil Berat Bersih pabrik_timbangan
        $str="select a.beratbersih,a.intex,a.kodeorg,a.millcode,left(a.tanggal,7),substr(a.tanggal,6,2) as bulan
        ,b.subregional 
        from ".$dbname.".".$tabel_2." a
        left join ".$dbname.".".$tabel_0." b on a.kodeorg = b.kodeunit 
        where kodebarang='40000003' and millcode in 
        (select kodeunit from ".$dbname.".".$tabel_0." where 1=1 ".$wh0.") 
        and left(tanggal,7) between '".$tahun."-01' and '".$periode."'
        order by b.subregional desc";

        $Subregcode=makeOption($dbname,'bgt_regional_assignment','kodeunit,subregional');
        $OptInti=makeOption($dbname,'pabrik_timbangan','kodeorg,intex');
        $OptAntiInti=makeOption($dbname,'pabrik_timbangan','kodeorg,intiplasma',"intiplasma!='inti'");
        // $OptInti=makeOption($dbname,'pabrik_timbangan','kodeorg,intiplasma');
        $res = fetchdata($str);
        foreach ($res as $bar) {
            if ($bar['kodeorg']!='' and $bar['intex']!='') {
                @$intiKodeorg[$bar['subregional']][$bar['intex']][$bar['kodeorg']]=$bar['kodeorg'];
                @$netto[$bar['subregional']][$bar['intex']][$bar['kodeorg']][$bar['bulan']]+=$bar['beratbersih']/1000;
            }else{
                @$bar['intex']='SWADAYA';
                // @$bar['kodeorg']='SDKM';
                // @$intiKodeorg[$bar['subregional']][$bar['intex']][$bar['kodeorg']]=$bar['kodeorg'];
                // @$netto[$bar['subregional']][$bar['intex']][$bar['millcode']][$bar['bulan']]+=$bar['beratbersih']/1000;

                @$intiKodeorg[$Subregcode[$bar['millcode']]][$bar['intex']][$bar['kodeorg']]=$bar['millcode'];
                @$netto[$Subregcode[$bar['millcode']]][$bar['intex']][$bar['millcode']][$bar['bulan']]+=$bar['beratbersih']/1000;
            }
        }
        $arrinti=array('0'=>'INTI','1'=>'KUD','2'=>'SWADAYA');
        
        // echo"<pre>";
        // print_r($intiKodeorg);
        // echo"</pre>";

        #ambil prd bgt
        $e="(";
        for($i=1;$i<=intval($bulan);$i++){
            $r="kg".addZero($i,2);
            if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
        }
        $e.=")";
        $str=" select left(kodeblok,4) as kodeblok,".$e." as sdbi, kg".$bulan." as bi , kgsetahun 
        from ".$dbname.".".$tabel_3." a where 1=1 ".$wh2." and tahunbudget = '".$tahun."'";
        $res = fetchdata($str);
        foreach($res as $bar){	
            $kg[$Subregcode[$bar['kodeblok']]][$OptInti[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['bi']/1000;
            $kgsdbi[$Subregcode[$bar['kodeblok']]][$OptInti[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['sdbi']/1000;
            $kgsdbikg[$Subregcode[$bar['kodeblok']]][$OptInti[$bar['kodeblok']]][$bar['kodeblok']]+=($bar['sdbi']/1000)+($bar['bi']/1000);
            $kgsetahun[$Subregcode[$bar['kodeblok']]][$OptInti[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['kgsetahun']/1000;
        }

        #ambil prd bgt else untuk plasma/KUD dan swadaya
        $e="(";
        for($i=1;$i<=intval($bulan);$i++){
            $r="olah".addZero($i,2);
            if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
        }
        $e.=")";

        $str=" select kodeunit as kodeblok,kodeunit as intiplasma,millcode,".$e." as sdbi, olah".$bulan." as bi, kgolah as kgsetahun
        from ".$dbname.".".$tabel_6." a where tahunbudget = '".$tahun."'";
        $res = fetchdata($str);
        foreach($res as $bar){
            if ($bar['kodeblok']!='tbsexternal') {
                $kg[$Subregcode[$bar['kodeblok']]][$OptAntiInti[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['bi']/1000;
                $kgsdbi[$Subregcode[$bar['kodeblok']]][$OptAntiInti[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['sdbi']/1000;
                $kgsdbikg[$Subregcode[$bar['kodeblok']]][$OptAntiInti[$bar['kodeblok']]][$bar['kodeblok']]+=($bar['sdbi']/1000)+($bar['bi']/1000);
                $kgsetahun[$Subregcode[$bar['kodeblok']]][$OptAntiInti[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['kgsetahun']/1000;
            }else {
                $bar['intiplasma']='SWADAYA';
                $kg[$Subregcode[$bar['millcode']]][$bar['intiplasma']][$bar['millcode']]+=$bar['bi']/1000;
                $kgsdbi[$Subregcode[$bar['millcode']]][$bar['intiplasma']][$bar['millcode']]+=$bar['sdbi']/1000;
                $kgsdbikg[$Subregcode[$bar['millcode']]][$bar['intiplasma']][$bar['millcode']]+=($bar['sdbi']/1000)+($bar['bi']/1000);
                $kgsetahun[$Subregcode[$bar['millcode']]][$bar['intiplasma']][$bar['millcode']]+=$bar['kgsetahun']/1000;
            }	
        }
        
        // echo"<pre>";
        // print_r($kg);
        // echo"</pre>";
        // exit();

        #Ambil bgt_blok hathnini
        $str=" select left(kodeblok,4) as kodeblok,hathnini from ".$dbname.".".$tabel_4." where 1=1 ".$wh2." and tahunbudget = '".$tahun."'";
        $res = fetchData($str);
        foreach ($res as $bar) {
            $hathnini[$Subregcode[$bar['kodeblok']]][$OptInti[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['hathnini'];
        }

        # Ambil Prediction crop Kg Sensus
        $cr1=$bulan+1;
        $cr2=$bulan+3;
        $cr2nd1=$cr2+1;
        $cr2nd2=$cr2+3;
        $str=" select left(kodeorg,4) as kodeorg, kgsensus, left(tanggal,7),substr(tanggal,6,2) as bulan 
        from ".$dbname.".".$tabel_5." where 1=1 ".$wh3." and tahun = '".$tahun."' 
        and left(tanggal,7) between '".$tahun."-".addzero($cr1,2)."' and '".$tahun."-".addzero($cr2nd2,2)."' ";
        $res = fetchData($str);
        foreach ($res as $bar) {
            $cropsensus[$Subregcode[$bar['kodeorg']]][$OptInti[$bar['kodeorg']]][$bar['kodeorg']][$bar['bulan']]+=$bar['kgsensus']/1000;
        }

        #Ambil Else crop prediksi
        if ($cr2nd2<=12) {
            $cr2nd2=$cr2nd2;
        }else {
            $cr2nd2=13;
        }
        for ($i=$cr1; $i < $cr2nd2; $i++) {
            
            $str=" select left(kodeblok,4) as kodeblok, kg".addZero($i,2)." as bi
            from ".$dbname.".".$tabel_3." a where 1=1 ".$wh2." and tahunbudget = '".$tahun."';";
            $res = fetchdata($str);
            foreach($res as $bar){	
                $kgcroppr[$Subregcode[$bar['kodeblok']]][$OptInti[$bar['kodeblok']]][$bar['kodeblok']][$arrayZero[$i]]+=$bar['bi']/1000;
            }
        }

        #Ambil Else crop prediksi buat plasma dan Swadaya
        if ($cr2nd2<=12) {
            $cr2nd2=$cr2nd2;
        }else {
            $cr2nd2=13;
        }
        for ($i=$cr1; $i < $cr2nd2; $i++) {
            $str=" select kodeunit as kodeblok,kodeunit as intiplasma,millcode, olah".addZero($i,2)." as bi
            from ".$dbname.".".$tabel_6." a where tahunbudget = '".$tahun."'";
            $res = fetchdata($str);
            foreach($res as $bar){	
                if ($bar['kodeblok']!='tbsexternal') {
                    $kgcroppr[$Subregcode[$bar['kodeblok']]][$OptAntiInti[$bar['kodeblok']]][$bar['kodeblok']][$arrayZero[$i]]+=$bar['bi']/1000;
                }else {
                    $bar['intiplasma']='SWADAYA';
                    $kgcroppr[$Subregcode[$bar['millcode']]][$bar['intiplasma']][$bar['millcode']][$arrayZero[$i]]+=$bar['bi']/1000;
                }	
            }
        }
        
        foreach ($arrReg as $key) {
            foreach ($arrinti as $key1) {
                foreach ($intiKodeorg[$key][$key1] as $key2) {
                    // echo $key2."<br>";
                }
            }
        }
       
        ## END OF QUERIES        

        ## Start Row Content
        foreach ($arrReg as $key) { #1 subregional
            @$colbwh=count($arrr[$key])+3+1;
            @$baru=$colbwh+1;
            @$baru2+=$baru;
            if ($key=='SEKADAU') {$D1=count($arrr['SEKADAU']) +1 +3;}
            if ($key=='KAPUAS') {$D2=count($arrr['KAPUAS']) +$D1+3+1;}
            $D3=count($arr['BONTI']);
			$tab.= "<tr class=rowcontent>";
			$no++;
			$tab.= "<td style='vertical-align:top;text-align:center' rowspan=".$colbwh.">".$no."</td>";
			$tab.= "<td style='vertical-align:top;text-align:center' rowspan=".$colbwh.">".$key."</td>";
            foreach ($arrinti as $key1 => $val1) { #2 Intiplasma
                foreach ($intiKodeorg[$key][$key1] as $key2) { #3 kodeorg 
                    if ($key1!='1') {
                        @$d++; 
                        if($d!=1 && $d!=5 && $d!=13) { ## 4,8,4
                            $tab.= "</tr><tr class=rowcontent>";
                        }
                        $tab.= "<td align=center>".$key2."</td>";
                        $tab.= "<td align=right>".@number_format($luas[$key][$key1][$key2],2)."</td>";
                        for ($i=1; $i < $bulan; $i++) { ## bulan
                            $tab.= "<td align=right>".@number_format($netto[$key][$key1][$key2][$arrayZero[$i]],2)."</td>";
                        }
                        for ($i=$bulan; $i <=$bulan; $i++) { ## bulan
                            $tab.= "<td align=right>".@number_format($netto[$key][$key1][$key2][$i],2)."</td>";
                            $diff1[$key][$key1][$key2]=(($netto[$key][$key1][$key2][$i]-$kg[$key][$key1][$key2])/$kg[$key][$key1][$key2]*100);
                            
                        }
                        for ($i=1; $i <= $bulan; $i++) { ## Netto Setahun
                            $nettosetahun[$key][$key1][$key2]+=$netto[$key][$key1][$key2][$arrayZero[$i]];
                        }
                        $tab.= "<td align=right>".@number_format($kg[$key][$key1][$key2],2)."</td>";
                        $tab.= "<td align=right>".@number_format(fixnan($diff1[$key][$key1][$key2]),2)."</td>";
                        $tab.= "<td align=right>".@number_format($nettosetahun[$key][$key1][$key2],2)."</td>";
                        $tab.= "<td align=right>".@number_format($kgsdbikg[$key][$key1][$key2],2)." sdbi</td>";
                        
                        
                        $diff2[$key][$key1][$key2]=(($nettosetahun[$key][$key1][$key2]-$kgsdbikg[$key][$key1][$key2])/$kgsdbikg[$key][$key1][$key2]*100);
                        $nettoTHa[$key][$key1][$key2]=$nettosetahun[$key][$key1][$key2]/$luas[$key][$key1][$key2];
                        $kgTHa[$key][$key1][$key2]=$kgsdbikg[$key][$key1][$key2]/$luas[$key][$key1][$key2];
                        $diff3[$key][$key1][$key2]=$nettoTHa[$key][$key1][$key2]-$kgTHa[$key][$key1][$key2];
                        $kgTHa2[$key][$key1][$key2]=$kgsetahun[$key][$key1][$key2]/$hathnini[$key][$key1][$key2];
                        
                    

                        $tab.= "<td align=right>".@number_format(fixnan($diff2[$key][$key1][$key2]),2)."</td>";
                        $tab.= "<td align=right>".@number_format($nettoTHa[$key][$key1][$key2],2)."</td>";
                        $tab.= "<td align=right>".@number_format($kgTHa[$key][$key1][$key2],2)."</td>";
                        $tab.= "<td align=right>".@number_format($diff3[$key][$key1][$key2],2)."</td>";

                        $tab.= "<td align=right>".@number_format($hathnini[$key][$key1][$key2],2)."</td>";
                        $tab.= "<td align=right>".@number_format($kgsetahun[$key][$key1][$key2],2)."</td>";
                        $tab.= "<td align=right>".@number_format(fixnan($kgTHa2[$key][$key1][$key2]),2)."</td>";

                        if ($cr2<=12) {
                            $cr2=$cr2;
                        }else {
                            $cr2=12;
                        }

                        for ($i=$cr1; $i<=$cr2 ; $i++) { 
                            if ($cropsensus[$key][$key1][$key2][$arrayZero[$i]]=='') {
                                $tab.= "<td align=right>".@number_format($kgcroppr[$key][$key1][$key2][$arrayZero[$i]],2)."</td>";
                                $crt1st[$key][$key1][$key2]+=$kgcroppr[$key][$key1][$key2][$arrayZero[$i]];
                            }else {
                                $tab.= "<td align=right>".@number_format($cropsensus[$key][$key1][$key2][$arrayZero[$i]],2)."</td>";
                                $crt1st[$key][$key1][$key2]+=$cropsensus[$key][$key1][$key2][$arrayZero[$i]];
                            }
                        }
                        $crTHa[$key][$key1][$key2]=$crt1st[$key][$key1][$key2]/$luas[$key][$key1][$key2];
                        if ($cr1<=12) {
                            $tab.= "<td align=right>".@number_format($crt1st[$key][$key1][$key2],2)."</td>";
                            $tab.= "<td align=right>".@number_format($crTHa[$key][$key1][$key2],2)."</td>";
                        }    
                        if ($cr2nd2<=12) {
                            $cr2nd2=$cr2nd2;
                        }else {
                            $cr2nd2=12;
                        }

                        for ($i=$cr2nd1; $i<=$cr2nd2 ; $i++) { 
                            if ($cropsensus[$key][$key1][$key2][$arrayZero[$i]]=='') {
                                $tab.= "<td align=right>".@number_format($kgcroppr[$key][$key1][$key2][$arrayZero[$i]],2)."</td>";
                                $crt2st[$key][$key1][$key2]+=$kgcroppr[$key][$key1][$key2][$arrayZero[$i]];
                            }else {
                                $tab.= "<td align=right>".@number_format($cropsensus[$key][$key1][$key2][$arrayZero[$i]],2)."</td>";
                                $crt2st[$key][$key1][$key2]+=$cropsensus[$key][$key1][$key2][$arrayZero[$i]];
                            }
                        }
                        $crTHa2[$key][$key1][$key2]=$crt2st[$key][$key1][$key2]/$luas[$key][$key1][$key2];
                        $thnPredict[$key][$key1][$key2]=$nettosetahun[$key][$key1][$key2]+$crt1st[$key][$key1][$key2]+$crt2st[$key][$key1][$key2];
                        $actbud[$key][$key1][$key2]=(($thnPredict[$key][$key1][$key2]/$kgsetahun[$key][$key1][$key2])*100)-100;
                        $YHA[$key][$key1][$key2]=$thnPredict[$key][$key1][$key2]/$luas[$key][$key1][$key2];
                        if ($cr2nd1<=12) {    
                            $tab.= "<td align=right>".@number_format($crt2st[$key][$key1][$key2],2)."</td>";
                            $tab.= "<td align=right>".@number_format($crTHa2[$key][$key1][$key2],2)."</td>";
                        }    
                        $tab.= "<td align=right>".@number_format($thnPredict[$key][$key1][$key2],2)."</td>";
                        $tab.= "<td align=right>".@number_format(fixnan($actbud[$key][$key1][$key2]),2)."</td>";
                        $tab.= "<td align=right>".@number_format(fixnan($YHA[$key][$key1][$key2]),2)."</td>";

                        $tab.= "</tr>";
                    }

                    #SubTotal
                    $luasSubtotal[$key][$key1]+=$luas[$key][$key1][$key2];
                    $kgSubtotal[$key][$key1]+=$kg[$key][$key1][$key2];
                    for ($i=1; $i <= $bulan; $i++) { ## bulan SubTotal
                        $nettoSubtotal[$key][$key1][$arrayZero[$i]]+=$netto[$key][$key1][$key2][$arrayZero[$i]];
                    }
                    $diff1Subtotal[$key][$key1]=(($nettoSubtotal[$key][$key1][$bulan]-$kgSubtotal[$key][$key1])/$kgSubtotal[$key][$key1]*100);#
                    $nettosetahunSubtotal[$key][$key1]+=$nettosetahun[$key][$key1][$key2];
                    $kgsdbikgSubtotal[$key][$key1]+=$kgsdbikg[$key][$key1][$key2];

                    $diff2Subtotal[$key][$key1]=(($nettosetahunSubtotal[$key][$key1]-$kgsdbikgSubtotal[$key][$key1])/$kgsdbikgSubtotal[$key][$key1]*100);#
                    $nettoTHaSubtotal[$key][$key1]=$nettosetahunSubtotal[$key][$key1]/$luasSubtotal[$key][$key1];#

                    $kgTHa2[$key][$key1][$key2]=$kgsetahun[$key][$key1][$key2]/$hathnini[$key][$key1][$key2];

                    $kgTHaSubtotal[$key][$key1]=$kgsdbikgSubtotal[$key][$key1]/$luasSubtotal[$key][$key1];#
                    $diff3Subtotal[$key][$key1]=$nettoTHaSubtotal[$key][$key1]-$kgTHaSubtotal[$key][$key1];#

                    $hathniniSubtotal[$key][$key1]+=$hathnini[$key][$key1][$key2];
                    $kgsetahunSubtotal[$key][$key1]+=$kgsetahun[$key][$key1][$key2];

                    $kgTHa2Subtotal[$key][$key1]=$kgsetahunSubtotal[$key][$key1]/$hathniniSubtotal[$key][$key1];#

                    for ($i=$cr1; $i<=$cr2 ; $i++) { 
                        if ($cropsensus[$key][$key1][$key2][$arrayZero[$i]]=='') {
                            $PreStSubtotal[$key][$key1][$arrayZero[$i]]+=$kgcroppr[$key][$key1][$key2][$arrayZero[$i]];
                        }else {
                            $PreStSubtotal[$key][$key1][$arrayZero[$i]]+=$cropsensus[$key][$key1][$key2][$arrayZero[$i]];
                        }
                    }
                    $crt1stSubtotal[$key][$key1]+=$crt1st[$key][$key1][$key2];
                    $crTHaSubtotal[$key][$key1]=$crt1stSubtotal[$key][$key1]/$luasSubtotal[$key][$key1];#

                    for ($i=$cr2nd1; $i<=$cr2nd2 ; $i++) { 
                        if ($cropsensus[$key][$key1][$key2][$arrayZero[$i]]=='') {
                            $PreNdSubtotal[$key][$key1][$arrayZero[$i]]+=$kgcroppr[$key][$key1][$key2][$arrayZero[$i]];
                        }else {
                            $PreNdSubtotal[$key][$key1][$arrayZero[$i]]+=$cropsensus[$key][$key1][$key2][$arrayZero[$i]];
                        }
                    }
                    $crt2stSubtotal[$key][$key1]+=$crt2st[$key][$key1][$key2];
                    
                    $crTHa2Subtotal[$key][$key1]=$crt2stSubtotal[$key][$key1]/$luasSubtotal[$key][$key1];#
                    $thnPredictSubtotal[$key][$key1]=$nettosetahunSubtotal[$key][$key1]+$crt1stSubtotal[$key][$key1]+$crt2stSubtotal[$key][$key1];#
                    $actbudSubtotal[$key][$key1]=(($thnPredictSubtotal[$key][$key1]/$kgsetahunSubtotal[$key][$key1])*100)-100;#
                    $YHASubtotal[$key][$key1]=$thnPredictSubtotal[$key][$key1]/$luasSubtotal[$key][$key1];#
                }
                $tab.= "<tr class=rowcontent>";
                $tab.= "<td ><b>Sub_Total_".$val1."</td>";
                $tab.= "<td align=right><b>".number_format($luasSubtotal[$key][$key1],2)."</td>";
                for ($i=1; $i <= $bulan; $i++) { ## bulan
                    $tab.= "<td align=right><b>".@number_format($nettoSubtotal[$key][$key1][$arrayZero[$i]],2)."</td>";
                }
                $tab.= "<td align=right><b>".number_format($kgSubtotal[$key][$key1],2)."</td>";
                $tab.= "<td align=right><b>".number_format(fixnan($diff1Subtotal[$key][$key1]),2)."</td>";#
                $tab.= "<td align=right><b>".number_format($nettosetahunSubtotal[$key][$key1],2)."</td>";
                $tab.= "<td align=right><b>".number_format($kgsdbikgSubtotal[$key][$key1],2)."</td>";
                $tab.= "<td align=right><b>".number_format(fixnan($diff2Subtotal[$key][$key1]),2)."</td>";#
                $tab.= "<td align=right><b>".number_format(fixnan($nettoTHaSubtotal[$key][$key1]),2)."</td>";#
                $tab.= "<td align=right><b>".number_format(fixnan($kgTHaSubtotal[$key][$key1]),2)."</td>";#
                $tab.= "<td align=right><b>".number_format(fixnan($diff3Subtotal[$key][$key1]),2)."</td>";#
                $tab.= "<td align=right><b>".number_format($hathniniSubtotal[$key][$key1],2)."</td>";
                $tab.= "<td align=right><b>".number_format($kgsetahunSubtotal[$key][$key1],2)."</td>";
                $tab.= "<td align=right><b>".number_format(fixnan($kgTHa2Subtotal[$key][$key1]),2)."</td>";#
                for ($i=$cr1; $i<=$cr2 ; $i++) { 
                    $tab.= "<td align=right><b>".number_format($PreStSubtotal[$key][$key1][$arrayZero[$i]],2)."</td>";
                }
                if ($cr1<=12) {
                    $tab.= "<td align=right><b>".number_format($crt1stSubtotal[$key][$key1],2)."</td>";
                    $tab.= "<td align=right><b>".number_format(fixnan($crTHaSubtotal[$key][$key1]),2)."</td>";#
                }
                for ($i=$cr2nd1; $i<=$cr2nd2 ; $i++) { 
                    $tab.= "<td align=right><b>".number_format($PreNdSubtotal[$key][$key1][$arrayZero[$i]],2)."</td>";
                }
                if ($cr2nd1<=12) {
                    $tab.= "<td align=right><b>".number_format($crt2stSubtotal[$key][$key1],2)."</td>";
                    $tab.= "<td align=right><b>".number_format(fixnan($crTHa2Subtotal[$key][$key1]),2)."</td>"; #
                }    
                $tab.= "<td align=right><b>".number_format(fixnan($thnPredictSubtotal[$key][$key1]),2)."</td>";#
                $tab.= "<td align=right><b>".number_format(fixnan($actbudSubtotal[$key][$key1]),2)."</td>";#
                $tab.= "<td align=right><b>".number_format(fixnan($YHASubtotal[$key][$key1]),2)."</b></td>";#
                
                $tab.= "</tr>";

                ## += TOTAL
                $luasTotal[$key]+=$luasSubtotal[$key][$key1];
                for ($i=1; $i <= $bulan; $i++) { ## bulan SubTotal
                    $nettoTotal[$key][$arrayZero[$i]]+=$nettoSubtotal[$key][$key1][$arrayZero[$i]];
                }
                $kgTotal[$key]+=$kgSubtotal[$key][$key1];
                $diff1Total[$key]=(($nettoTotal[$key][$bulan]-$kgTotal[$key])/$kgTotal[$key]*100);#
                $nettosetahunTotal[$key]+=$nettosetahunSubtotal[$key][$key1];
                $kgsdbikgTotal[$key]+=$kgsdbikgSubtotal[$key][$key1];
                $diff2Total[$key]=(($nettosetahunTotal[$key]-$kgsdbikgTotal[$key])/$kgsdbikgTotal[$key]*100);#
                $nettoTHaTotal[$key]=$nettosetahunTotal[$key]/$luasTotal[$key];#
                $kgTHaTotal[$key]=$kgsdbikgTotal[$key]/$luasTotal[$key];#
                $diff3Total[$key]=$nettoTHaTotal[$key]-$kgTHaTotal[$key];#
                $hathniniTotal[$key]+=$hathniniSubtotal[$key][$key1];
                $kgsetahunTotal[$key]+=$kgsetahunSubtotal[$key][$key1];
                $kgTHa2Total[$key]=$kgsetahunTotal[$key]/$hathniniTotal[$key];#
                for ($i=$cr1; $i<=$cr2 ; $i++) { 
                    $PreStTotal[$key][$arrayZero[$i]]+=$PreStSubtotal[$key][$key1][$arrayZero[$i]];
                }
                $crt1stTotal[$key]+=$crt1stSubtotal[$key][$key1];
                $crTHaTotal[$key]=$crt1stTotal[$key]/$luasTotal[$key];#
                for ($i=$cr2nd1; $i<=$cr2nd2 ; $i++) { 
                    $PreNdTotal[$key][$arrayZero[$i]]+=$PreNdSubtotal[$key][$key1][$arrayZero[$i]];
                }
                $crt2stTotal[$key]+=$crt2stSubtotal[$key][$key1];
                $crTHa2Total[$key]=$crt2stTotal[$key]/$luasTotal[$key];#
                $thnPredictTotal[$key]=$nettosetahunTotal[$key]+$crt1stTotal[$key]+$crt2stTotal[$key];#
                $actbudTotal[$key]=(($thnPredictTotal[$key]/$kgsetahunTotal[$key])*100)-100;#
                $YHATotal[$key]=$thnPredictTotal[$key]/$luasTotal[$key];#
            }
            $tab.= "<tr class=rowcontent>";
            $tab.= "<td ><b>Total ".$key."</td>";
            $tab.= "<td align=right><b>".number_format($luasTotal[$key],2)."</b></td>";
            for ($i=1; $i <= $bulan; $i++) { ## bulan SubTotal
                $tab.= "<td align=right><b>".number_format($nettoTotal[$key][$arrayZero[$i]],2)."</b></td>";
            }
            $tab.= "<td align=right><b>".number_format($kgTotal[$key],2)."</b></td>";
            $tab.= "<td align=right><b>".number_format(fixnan($diff1Total[$key]),2)."</b></td>";#
            $tab.= "<td align=right><b>".number_format($nettosetahunTotal[$key],2)."</b></td>";
            $tab.= "<td align=right><b>".number_format($kgsdbikgTotal[$key],2)."</b></td>";
            $tab.= "<td align=right><b>".number_format(fixnan($diff2Total[$key]),2)."</b></td>";#
            $tab.= "<td align=right><b>".number_format(fixnan($nettoTHaTotal[$key]),2)."</b></td>";#
            $tab.= "<td align=right><b>".number_format(fixnan($kgTHaTotal[$key]),2)."</b></td>";#
            $tab.= "<td align=right><b>".number_format(fixnan($diff3Total[$key]),2)."</b></td>";#
            $tab.= "<td align=right><b>".number_format($hathniniTotal[$key],2)."</b></td>";
            $tab.= "<td align=right><b>".number_format($kgsetahunTotal[$key],2)."</b></td>";
            $tab.= "<td align=right><b>".number_format(fixnan($kgTHa2Total[$key]),2)."</b></td>";#
            for ($i=$cr1; $i<=$cr2 ; $i++) { 
                $tab.= "<td align=right><b>".number_format($PreStTotal[$key][$arrayZero[$i]],2)."</b></td>";
            }
            if ($cr1<=12) {
                $tab.= "<td align=right><b>".number_format($crt1stTotal[$key],2)."</b></td>";
                $tab.= "<td align=right><b>".number_format(fixnan($crTHaTotal[$key]),2)."</b></td>";#
            }
            for ($i=$cr2nd1; $i<=$cr2nd2 ; $i++) { 
                $tab.= "<td align=right><b>".number_format($PreNdTotal[$key][$arrayZero[$i]],2)."</b></td>";
            }
            if ($cr2nd1<=12) {
                $tab.= "<td align=right><b>".number_format($crt2stTotal[$key],2)."</b></td>";
                $tab.= "<td align=right><b>".number_format(fixnan($crTHa2Total[$key]),2)."</b></td>";#
            }
            $tab.= "<td align=right><b>".number_format(fixnan($thnPredictTotal[$key]),2)."</b></td>";#
            $tab.= "<td align=right><b>".number_format(fixnan($actbudTotal[$key]),2)."</b></td>";#
            $tab.= "<td align=right><b>".number_format(fixnan($YHATotal[$key]),2)."</b></td>";#
            
            $tab.= "</tr>";
            
            
            $tab.= "</tr>";
            
            ## += Grand TOTAL
            $luasGrandTotal+=$luasTotal[$key];
            for ($i=1; $i <= $bulan; $i++) { ## bulan SubTotal
                $nettoGrandTotal[$arrayZero[$i]]+=$nettoTotal[$key][$arrayZero[$i]];
            }
            $kgGrandTotal+=$kgTotal[$key];
            $diff1GrandTotal=(($nettoGrandTotal[$bulan]-$kgGrandTotal)/$kgGrandTotal*100);#
            $nettosetahunGrandTotal+=$nettosetahunTotal[$key];
            $kgsdbikgGrandTotal+=$kgsdbikgTotal[$key];
            $diff2GrandTotal=(($nettosetahunGrandTotal-$kgsdbikgGrandTotal)/$kgsdbikgGrandTotal*100);#
            $nettoTHaGrandTotal=$nettosetahunGrandTotal/$luasGrandTotal;#
            $kgTHaGrandTotal=$kgsdbikgGrandTotal/$luasGrandTotal;#
            $diff3GrandTotal=$nettoTHaGrandTotal-$kgTHaGrandTotal;#
            $hathniniGrandTotal+=$hathniniTotal[$key];
            $kgsetahunGrandTotal+=$kgsetahunTotal[$key];
            $kgTHa2GrandTotal=$kgsetahunGrandTotal/$hathniniGrandTotal;#
            for ($i=$cr1; $i<=$cr2 ; $i++) { 
                $PreStGrandTotal[$arrayZero[$i]]+=$PreStTotal[$key][$arrayZero[$i]];
            }
            $crt1stGrandTotal+=$crt1stTotal[$key];
            $crTHaGrandTotal=$crt1stGrandTotal/$luasGrandTotal;#
            for ($i=$cr2nd1; $i<=$cr2nd2 ; $i++) { 
                $PreNdGrandTotal[$arrayZero[$i]]+=$PreNdTotal[$key][$arrayZero[$i]];
            }
            $crt2stGrandTotal+=$crt2stTotal[$key];
            $crTHa2GrandTotal=$crt2stGrandTotal/$luasGrandTotal;#
            $thnPredictGrandTotal=$nettosetahunGrandTotal+$crt1stGrandTotal+$crt2stGrandTotal;
            $actbudGrandTotal=(($thnPredictGrandTotal/$kgsetahunGrandTotal)*100)-100;#
            $YHAGrandTotal=$thnPredictGrandTotal/$luasGrandTotal;#
        }
        $tab.= "<tr class=rowcontent>";
        $tab.="<td align=center><b>GRAND TOTAL</b></td>";
        $tab.= "<td align=right><b>".number_format($luasGrandTotal,2)."</td>";
        for ($i=1; $i <= $bulan; $i++) { ## bulan SubTotal
            $tab.= "<td align=right><b>".number_format($nettoGrandTotal[$arrayZero[$i]],2)."</td>";
        }
        $tab.= "<td align=right><b>".number_format($kgGrandTotal,2)."</td>";
        $tab.= "<td align=right><b>".number_format(fixnan($diff1GrandTotal),2)."</td>";#
        $tab.= "<td align=right><b>".number_format($nettosetahunGrandTotal,2)."</td>";
        $tab.= "<td align=right><b>".number_format($kgsdbikgGrandTotal,2)."</td>";
        $tab.= "<td align=right><b>".number_format(fixnan($diff2GrandTotal),2)."</td>";#
        $tab.= "<td align=right><b>".number_format(fixnan($nettoTHaGrandTotal),2)."</td>";#
        $tab.= "<td align=right><b>".number_format(fixnan($kgTHaGrandTotal),2)."</td>";#
        $tab.= "<td align=right><b>".number_format(fixnan($diff3GrandTotal),2)."</td>";#
        $tab.= "<td align=right><b>".number_format($hathniniGrandTotal,2)."</td>";
        $tab.= "<td align=right><b>".number_format($kgsetahunGrandTotal,2)."</td>";
        $tab.= "<td align=right><b>".number_format(fixnan($kgTHa2GrandTotal),2)."</td>";#
        for ($i=$cr1; $i<=$cr2 ; $i++) { 
            $tab.= "<td align=right><b>".number_format($PreStGrandTotal[$arrayZero[$i]],2)."</td>";
        }
        if ($cr1<=12) {
            $tab.= "<td align=right><b>".number_format($crt1stGrandTotal,2)."</td>";
            $tab.= "<td align=right><b>".number_format(fixnan($crTHaGrandTotal),2)."</td>";#
        }
        for ($i=$cr2nd1; $i<=$cr2nd2 ; $i++) { 
            $tab.= "<td align=right><b>".number_format($PreNdGrandTotal[$arrayZero[$i]],2)."</td>";
        }
        if ($cr2nd1<=12) {
            $tab.= "<td align=right><b>".number_format($crt2stGrandTotal,2)."</td>";
            $tab.= "<td align=right><b>".number_format(fixnan($crTHa2GrandTotal),2)."</td>";#
        }
        $tab.= "<td align=right><b>".number_format(fixnan($thnPredictGrandTotal),2)."</td>";#
        $tab.= "<td align=right><b>".number_format(fixnan($actbudGrandTotal),2)."</td>";#
        $tab.= "<td align=right><b>".number_format(fixnan($YHAGrandTotal),2)."</td>";#
        
        $tab.= "</tr>";

        
        ## End Row Content
       
		if($tipeprint=='html'){
			echo $tab;			
		}else if($tipeprint=='excel'){
			$tab.="</tbody></table>";
			
			$nop = "Perkiraan Panen.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("1", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}

	break;	
}

?>