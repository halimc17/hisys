<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses   = checkPostGet('proses', '');
$kdorg    = checkPostGet('kdorg', '');
$regional = checkPostGet('regional', '');
$prd      = checkPostGet('prd', '');
$tipe     = checkPostGet('tipe', '');
$tipex    = checkPostGet('tipe', '');
$jam      = checkPostGet('jam', '');
$kapasitas= checkPostGet('kapasitas', '');

if($jam==0 or $jam==''){
	$jam=20;
}
if($kapasitas==0 or $kapasitas==''){
	$kapasitas=140;
}

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}

$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $prd;
$periode2 = $tahun."-12";

$rangeperiode = month_inbetween($periode1,$periode2);

$arrcode = array(
	'ffb'      =>'FFB',
	'ffbi'     =>'Inti',
	'ffbp'     =>'Plasma',
	'ffbswa'   =>'Swadaya',
	'ffbttl'   =>'Total FFB Received',
	'util'     =>'Utilisasi',
	'1'        =>'&nbsp;',
	'ffbproses'=>'FFB Processed',
	'2'        =>'&nbsp;',
	'pro'      =>'PRODUCTION',
	'cpo'      =>'CPO',
	'pk'       =>'PK',
	'pp'       =>'PP',
	'3'        =>'&nbsp;',
	'oer'      =>'OER',
	'oerpk'    =>'PK',
	'hk'       =>'Hari Kerja',
	'4'        =>'&nbsp;',
	'kapasitas'=>'Mill Kapasitas',
	'ffbhari'  =>'FFB/Hari'
);


$where='';
$where2='';
if($regional!=''){
	$whreg="and subregional='".$regional."'";
}

$listkodeorg = [];
$datakodeorg = [];

$str="select * from ".$dbname.".bgt_regional_assignment where 1=1 ".$whreg."";
$res = fetchdata($str);
foreach($res as $bar){
	if(getNamaOrg($bar['kodeunit'],'tipe')=='KEBUN' or getNamaOrg($bar['kodeunit'],'tipe')=='PABRIK'){		
		$datakodeorg[$bar['subregional']][$bar['kodeunit']]=$bar['kodeunit'];
		$listkodeorg[$bar['kodeunit']]=$bar['kodeunit'];
		$getregion[$bar['kodeunit']]=$bar['subregional'];
		$listreg[$bar['subregional']]=$bar['subregional'];		
	}
}

$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
$whhk=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
$whB=" and substr(a.millcode,1,4) in ('".implode("','",$listkodeorg)."')";
if($kdorg!=''){
	$where.=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2.=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$whhk.=" and substr(a.unit,1,4) ='".$kdorg."'";
	$whB=" and substr(a.millcode,1,4) ='".$kdorg."'";
}

$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)<=4";
$res = fetchdata($str);
foreach($res as $bar){
	$listip[$bar['kodeorganisasi']]=$bar['inti'];		
}


#ambil prd bgt
$e="";
for($i=1;$i<=12;$i++){
	$r="kg".addZero($i,2);
	$e.=",".$r." as '".$tahun."-".addZero($i,2)."'";
}
$str = " select kodeunit,divisi,thntnm,kodeblok".$e.",kgsetahun from ".$dbnamerpt.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." and tahunbudget = '".$tahun."'";
$res = fetchdatarpt($str);
foreach($res as $bar){
	foreach($rangeperiode as $periode){
		if($listip[substr($bar['kodeblok'],0,4)]=='1'){			
			$budget['ffbi'][$periode] += $bar[$periode]/1000;
		}
		if($listip[substr($bar['kodeblok'],0,4)]=='0'){			
			$budget['ffbp'][$periode] += $bar[$periode]/1000;
		}
	}
}

$str = " select * from ".$dbnamerpt.".bgt_produksi_pks_vw a where 1=1 ".$whB." and tahunbudget = '".$tahun."'";
$res = fetchdatarpt($str);
foreach($res as $bar){
	if($bar['kodeunit']=='tbsexternal'){		
		foreach($rangeperiode as $periode){
			$per = substr($periode,-2);
			$budget['ffbswa'][$periode] += $bar['olah'.$per]/1000;
		}
	}
	foreach($rangeperiode as $periode){
		$per = substr($periode,-2);
		$budget['cpo'][$periode] += $bar['kgcpo'.$per]/1000;
		$budget['pk'][$periode] += $bar['kgker'.$per]/1000;
		$budget['pp'][$periode] += ($bar['kgcpo'.$per]+$bar['kgker'.$per])/1000;
	}
}

$str = " select * from ".$dbnamerpt.".bgt_hk a where 1=1 ".$whhk." and tahunbudget = '".$tahun."'";
$res = fetchdatarpt($str);
$temphkpks=0;
foreach($res as $bar){
	if(getNamaOrg($bar['unit'],'tipe')=='PABRIK'){		
		foreach($rangeperiode as $periode){
			$per = substr($periode,-2);
			$budget['hk'][$periode] = $bar['h'.$per];
			$temphkpks++;
		}
	}
	if($temphkpks==0){
		foreach($rangeperiode as $periode){
			$per = substr($periode,-2);
			$budget['hk'][$periode] = $bar['h'.$per];
		}
	}
}


$str = " select * from ".$dbnamerpt.".sdm_5harilibur a where kebun='GLOBAL' and keterangan='libur' and tanggal like '".$tahun."%'";
$res = fetchdatarpt($str);
$temphkpks=0;
foreach($res as $bar){
	$libur[substr($bar['tanggal'],0,7)]+=1;
}

foreach($rangeperiode as $periode){
	$jlhhari = cal_days_in_month(CAL_GREGORIAN,substr($periode,-2),substr($periode,0,4));
	if($periode<=$prd){		
		$actual['hk'][$periode] = $jlhhari-$libur[$periode];
	}
}


$str = "select sum(beratbersih) as kg,count(notransaksi) as rit, kodeorg,millcode,divcode,pengirim,namatransportir,substr(tanggal,1,7) as periode from ".$dbnamerpt.".pabrik_timbangan a where tanggal like '".$tahun."%' and substr(tanggal,1,7)<='".$prd."' and kodebarang='40000003' ".$whB." group by millcode,kodeorg, substr(tanggal,1,7)";
$res=fetchdatarpt($str);
foreach($res as $bar){
	if($listip[substr($bar['kodeorg'],0,4)]=='1'){			
		$actual['ffbi'][$bar['periode']] += $bar['kg']/1000;
	}elseif($listip[substr($bar['kodeorg'],0,4)]=='0'){			
		$actual['ffbp'][$bar['periode']] += $bar['kg']/1000;
	}else{
		$actual['ffbswa'][$bar['periode']] += $bar['kg']/1000;
	}
}

$str = "select sum(tbsmasuk) as tbsmasuk, sum(tbsdiolah) as tbsdiolah, sum(oer) as cpo,sum(oerpk) as pk, kodeorg,substr(tanggal,1,7) as periode from ".$dbnamerpt.".pabrik_produksi a where tanggal like '".$tahun."%' and substr(tanggal,1,7)<='".$prd."' ".$where." group by kodeorg, substr(tanggal,1,7)";
$res = fetchdatarpt($str);
foreach($res as $bar){
	$actual['ffbttl'][$bar['periode']] += $bar['tbsmasuk']/1000;
	$actual['cpo'][$bar['periode']] += $bar['cpo']/1000;
	$actual['pk'][$bar['periode']] += $bar['pk']/1000;
	$actual['pp'][$bar['periode']] += ($bar['cpo']+$bar['pk'])/1000;
	$actual['ffbproses'][$bar['periode']] += $bar['tbsdiolah']/1000;
}



if ($proses == 'excel') {
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<table class=sortable cellpadding=5 cellspacing=1>";
}

// echo"<pre>";
// print_r($budget);

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='2'>Description</th>";
			foreach($rangeperiode as $periode){				
				$tab.="<th align=center colspan=2>".$periode."</th>";				
			}
			$tab.="<th align=center colspan=2>Total</th>";				
        $tab.="</tr>";
        $tab.="<tr class=rowheader>";
			foreach($rangeperiode as $periode){				
				$tab.="<th align=center>Budget</th>";				
				$tab.="<th align=center>Actual</th>";				
			}
			$tab.="<th align=center>Budget</th>";				
			$tab.="<th align=center>Actual</th>";					
        $tab.="</tr>";
     $tab.="</thead>
	<tbody>";
	$ttlbudget=[];
	foreach($arrcode as $code => $name){	
		foreach($rangeperiode as $periode){
			$budget['kapasitas'][$periode]=$jam*$kapasitas*$budget['hk'][$periode];
			$actual['kapasitas'][$periode]=$jam*$kapasitas*$actual['hk'][$periode];
			if($code=='kapasitas'){				
				$ttlbudget[$code]+=@$budget[$code][$periode];
				$ttlactual[$code]+=@$actual[$code][$periode];
			}				
		}
	}	
	$style=array(
		'ffb'   =>'style=font-weight:bold;',
		'pro'   =>'style=font-weight:bold;',
		'ffbttl'=>'style=font-weight:bold;',
		'1'     =>'style=background-color:#faf7f7;',
		'2'     =>'style=background-color:#faf7f7;',
		'3'     =>'style=background-color:#faf7f7;',
		'4'     =>'style=background-color:#faf7f7;'
	);
	
	$clickit=array(
		"ffbi"  =>"style='color:blue;cursor:pointer;' title='click it'",
		"ffbp"  =>"style='color:blue;cursor:pointer;' title='click it'",
		"ffbswa"=>"style='color:blue;cursor:pointer;' title='click it'",
		"cpo"   =>"style='color:blue;cursor:pointer;' title='click it'",
		"pk"    =>"style='color:blue;cursor:pointer;' title='click it'"
	);
	
	foreach($arrcode as $code => $name){		
		$tab.="<tr class=rowcontent>";
		$tab.="<td nowrap ".$style[$code].">".$name."</td>";
		foreach($rangeperiode as $periode){
			$budget['ffbttl'][$periode]=$budget['ffbi'][$periode]+$budget['ffbp'][$periode]+$budget['ffbswa'][$periode];
			$budget['kapasitas'][$periode]=$jam*$kapasitas*$budget['hk'][$periode];
			$budget['ffbhari'][$periode]=$budget['ffbttl'][$periode]/$budget['hk'][$periode];
			$budget['util'][$periode]=$budget['ffbttl'][$periode]/$budget['kapasitas'][$periode]*100;
			$budget['ffbproses'][$periode]=$budget['ffbttl'][$periode];
			$budget['oer'][$periode]=$budget['cpo'][$periode]/$budget['ffbttl'][$periode]*100;
			$budget['oerpk'][$periode]=$budget['pk'][$periode]/$budget['ffbttl'][$periode]*100;
			
			$actual['kapasitas'][$periode]=$jam*$kapasitas*$actual['hk'][$periode];
			@$actual['ffbhari'][$periode]=@fixnan($actual['ffbttl'][$periode]/@$actual['hk'][$periode]);
			@$actual['util'][$periode]=@fixnan($actual['ffbttl'][$periode]/@$actual['kapasitas'][$periode]*100);
			$actual['oer'][$periode]=@fixnan($actual['cpo'][$periode]/$actual['ffbttl'][$periode]*100);
			$actual['oerpk'][$periode]=@fixnan($actual['pk'][$periode]/$actual['ffbttl'][$periode]*100);

			if($clickit[$code]!=''){
				$onclick[$code]="onclick=getdetail('".$code."','".$periode."','".$regional."','".$kdorg."')";
			}

			$tab.="<td align=right ".$style[$code]." ".$clickit[$code]." ".$onclick[$code].">".numb_format($budget[$code][$periode],2)."</td>";	
			$tab.="<td align=right ".$style[$code]." ".$clickit[$code]." ".$onclick[$code].">".numb_format($actual[$code][$periode],2)."</td>";
			
			#total kanan
			if($code!='kapasitas'){				
				$ttlbudget[$code]+=@$budget[$code][$periode];
				$ttlactual[$code]+=@$actual[$code][$periode];
			}
		}
		$ttlbudget['ffbhari']=@fixnan($ttlbudget['ffbttl']/$ttlbudget['hk']);
		$ttlbudget['oer']=@fixnan($ttlbudget['cpo']/$ttlbudget['ffbttl']*100);
		$ttlbudget['oerpk']=@fixnan($ttlbudget['pk']/$ttlbudget['ffbttl']*100);
		$ttlbudget['util']=$ttlbudget['ffbttl']/$ttlbudget['kapasitas']*100;
		
		$ttlactual['ffbhari']=@fixnan($ttlactual['ffbttl']/$ttlactual['hk']);
		$ttlactual['util']=$ttlactual['ffbttl']/$ttlactual['kapasitas']*100;
		$ttlactual['oer']=@fixnan($ttlactual['cpo']/$ttlactual['ffbttl']*100);
		$ttlactual['oerpk']=@fixnan($ttlactual['pk']/$ttlactual['ffbttl']*100);
		
		
		$tab.="<td align=right ".$style[$code].">".numb_format($ttlbudget[$code],2)."</td>";	
		$tab.="<td align=right ".$style[$code].">".numb_format($ttlactual[$code],2)."</td>";
		$tab.="</tr>";
	}
	
$tab.="</tbody></table>";

$tab.="<label><b>Note :</b></label><br>";
$tab.="<label>Mill Kapasitas = ".$kapasitas." TPH</label><br>";
$tab.="<label>Jalan per hari = ".$jam." Jam</label><br><br>";

switch ($proses) {
######PREVIEW
    case 'preview':
		echo $tab;
    break;

######EXCEL	
    case 'excel':
        $nop_ = 'olah budget vs actual';
		$print = $tab;
        if (strlen($print) > 0) {
			$print.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $print)) {
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

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e="";
	}else if(is_infinite($e)){
		$e="";
	}else{
		$e=$e;
	}
	$n = hidezerodecimal($e,$i);
	if($n==0 or $n==''){
		$n='';
	}
	
	return $n;
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}

?>