<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$proses   = checkPostGet('proses', '');
$periode2  = checkPostGet('periode2', '');
$kdorg    = checkPostGet('kdorg', '');

$arrbi   =explode('-',$periode2); 
$tahun    =$arrbi[0]; 
$bulan    =$arrbi[1];

$periode1 = $tahun."-01";

$tglawalbi = $periode2."-01";
$tglawalsdbi = $periode1."-01";

$tglakhirbi = tglakhir($tglawalbi);
$tglakhirsdbi = tglakhir($tglawalsdbi);



if ($kdorg == '') {
    echo"Warning: Unit tidak boleh kosong";
    exit;
}


if ($proses == 'excel') {
    $tab = "<table class=sortable cellspacing=1 border=1>";
} else {
	$tab.="<div class='menu'>
			<div id='btninscmnt' class='menu-item'>Insert Comment</div>
			<div id='btnshowcmn' class='menu-item'>Show Comment</div>
			<div id='btnreloadframe' class='menu-item'>Reload Frame</div>
		</div>";
    $tab .= "<div class='table-scroll'>";
    $tab .= "<table class=sortable cellspacing=1 width=100%>";
}

$wh=$whx="";
$wh=" and a.kodeorg like '".$kdorg."%'";
$whx=" and a.kodeblok like '".$kdorg."%'";

$str = "select * from " . $dbname . ".setup_blok a where 1=1 ".$wh."";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$luastotal+= $bar['luasareaproduktif'];
    if(strlen(substr($bar['kodeorg'],0,6))==6){
        @$luasTotalDiv[substr($bar['kodeorg'],0,6)]['divactual']+=$bar['luasareaproduktif'];
    }
}

$str = "select * from " . $dbname . ".bgt_blok a where 1=1 ".$whx." and tahunbudget='".$tahun."'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$luasbgt+= $bar['hathnini'];
    if(strlen(substr($bar['kodeblok'],0,6))==6){
        @$luasTotalDiv[substr($bar['kodeblok'],0,6)]['divbgt']+=$bar['hathnini'];
    }
    
}

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan=4>No</th>
            <th align=center rowspan=4>" . $_SESSION['lang']['noakun'] . "</th>
            <th align=center rowspan=3>" . $_SESSION['lang']['akun'] . "</th>
            <th align=center colspan=5>Bulan Ini</th>
            <th align=center colspan=5>S/D Bulan Ini</th>
            <th align=center rowspan=2 colspan=2>" . $_SESSION['lang']['budget'] . "<br>" . $_SESSION['lang']['setahun'] . "</th>
        </tr>    
        <tr class=rowheader>
            <th align=center colspan=2>" . $_SESSION['lang']['realisasi'] . "</th>
            <th align=center colspan=2>" . $_SESSION['lang']['budget'] . "</th>
            <th align=center rowspan=3>%</th>
            <th align=center colspan=2>" . $_SESSION['lang']['realisasi'] . "</th>
            <th align=center colspan=2>" . $_SESSION['lang']['budget'] . "</th>
            <th align=center rowspan=3>%</th>
		</tr>    
		</tr>    
        <tr class=rowheader>
            <th align=center>Total</th>
            <th align=center>Rp/Ha</th>
            <th align=center>Total</th>
            <th align=center>Rp/Ha</th>
            <th align=center>Total</th>
            <th align=center>Rp/Ha</th>
            <th align=center>Total</th>
            <th align=center>Rp/Ha</th>
			<th align=center>Total</th>
            <th align=center>Rp/Ha</th>
            
        <tr class=rowheader>
            <th align=center rowspan=1>Luas - Ha</th>
            <th align=center colspan=2>".@number_format($luastotal,2)."</th>
            <th align=center colspan=2>".@number_format($luasbgt,2)."</th>
			<th align=center colspan=2>".@number_format($luastotal,2)."</th>
            <th align=center colspan=2>".@number_format($luasbgt,2)."</th>
            <th align=center colspan=2>".@number_format($luasbgt,2)."</th>
			
        ";

$tab.="</tr></thead><tbody>";

$str = "select sum(jumlah) as jumlah,noakun, periode  from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and noakun like '7%' ".$wh." and periode between '".$periode1."' and  '".$periode2."' group by noakun, periode";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$arrakun[$bar['noakun']] = $bar['noakun'];
	if($bar['periode']==$periode2){
		@$realbi[$bar['noakun']] += $bar['jumlah'];
	}
	@$realsdbi[$bar['noakun']] += $bar['jumlah'];
}

$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";
$str=" select tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$wh." and tahunbudget = '".$tahun."' and noakun like '7%' and kodebudget='UMUM'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$arrakun[$bar['noakun']] = $bar['noakun'];
	
	@$bgtbi[$bar['noakun']] += $bar['bi'];
	@$bgtsdbi[$bar['noakun']] += $bar['sdbi'];
	@$bgtthn[$bar['noakun']] += $bar['rupiah'];
}

$kdpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$kdorg."'");
$kdreg=makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".$kdorg."'");


$str = "select * from ".$dbname.".kebun_2commentreport a where 1=1 and unit='".$kdorg."' and periode <= '".$periode2."' and periode like '".$tahun."%'";
$res = fetchdata($str);
foreach($res as $bar){
	$kdunit=substr($bar['unit'],0,4);
	$substr='7';
	$showcomment[$kdunit][substr($bar['kegiatan'],0,$substr)][$bar['bi']][$bar['act']][]=array('id'=>$bar['id'],'user'=>$bar['createdby'],'comment'=>substr($bar['comment'],0,40));
	$showcomment[$kdunit][substr($bar['kegiatan'],0,$substr)]['sdbi'][$bar['act']][]=array('id'=>$bar['id'],'user'=>$bar['createdby'],'comment'=>substr($bar['comment'],0,40));
}

$colspan=7;
if(count($arrakun)>0){
	$no=0;
	array_multisort($arrakun,SORT_ASC);
	foreach($arrakun as $akun){
		$no++;
		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$akun."'");
		
		
		$d=substr($akun,0,5);
		if($d!=$n){
			$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF;>";
			$tab.="<td valign=top align=center></td>";			
			$tab.="<td valign=top align=center>" . $d . "</td>";			
			$tab.="<td valign=top>" . getNamaAkun($d) . "</td>";			
			$tab.="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";			
		}
		$n=$d;
		
		$tab.="<tr class=rowcontent >";
		$tab.="<td valign=top align=center>" . $no . "</td>";
		$tab.="<td valign=top align=center>" . $akun . "</td>";
		$tab.="<td valign=top align=left>" . $nmakun[$akun] . "</td>";
		
		# bi
		$click=$adacomment=""; $flag='0'; $clickdetail="";
		$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
		if(!empty($showcomment[$kdorg][$akun]['bi']['real'])){
			$adacomment="class=has_sign";  $flag='1';
			$title=" title='".getKary($showcomment[$kdorg][$akun]['bi']['real'][0]['user'])."\n".$showcomment[$kdorg][$akun]['bi']['real'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
		}
		$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$kdorg."','".$periode2."','bi','real')\"";
		$clickdetail=" style=cursor:pointer;color:blue; ".$click." onclick=detailjurnal('".$kdpt[$kdorg]."','".$kdorg."','".tanggalnormal($tglawalbi)."','".tanggalnormal($tglakhirbi)."','".$akun."','".$akun."','".$kdreg[$kdorg]."')";
		
		$click=$adacomment=""; $flag='0';
		$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
		if(!empty($showcomment[$kdorg][$akun]['bi']['bgt'])){
			$adacomment="class=has_sign";  $flag='1';
			$title=" title='".getKary($showcomment[$kdorg][$akun]['bi']['bgt'][0]['user'])."\n".$showcomment[$kdorg][$akun]['bi']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
		}
		$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$kdorg."','".$periode2."','bi','bgt')\"";

		$detbgtbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$kdpt[$kdorg]."','".$kdorg."','','','','".$periode2."','','".$akun."','html','bi','budget')";

		$tab.="<td valign=top align=right ".$clickdetail.">" . @number_format($realbi[$akun]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($realbi[$akun]/$luastotal) . "</td>";
		$tab.="<td valign=top align=right ".$detbgtbi.">" . @number_format($bgtbi[$akun]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($bgtbi[$akun]/$luasbgt) . "</td>";
		@$persenbi=$realbi[$akun]/$bgtbi[$akun]*100;
		if($persenbi>100){$i=" style=font-weight:bold;color:red";}else{$i="";}
		$tab.="<td valign=top align=right ".$i.">" . @number_format($persenbi,2) . "</td>";
		
		# sdbi
		$click=$adacomment=""; $flag='0'; $clickdetail="";
		$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
		if(!empty($showcomment[$kdorg][$akun]['sdbi']['real'])){
			$adacomment="class=has_sign";  $flag='1';
			$title=" title='".getKary($showcomment[$kdorg][$akun]['sdbi']['real'][0]['user'])."\n".$showcomment[$kdorg][$akun]['sdbi']['real'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
		}
		$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$kdorg."','".$periode2."','sdbi','real')\"";
		$clickdetail=" style=cursor:pointer;color:blue; ".$click." onclick=detailjurnal('".$kdpt[$kdorg]."','".$kdorg."','".tanggalnormal($tglawalbi)."','".tanggalnormal($tglakhirbi)."','".$akun."','".$akun."','".$kdreg[$kdorg]."')";
		
		$click=$adacomment=""; $flag='0'; $clickdetail="";
		$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
		if(!empty($showcomment[$kdorg][$akun]['sdbi']['bgt'])){
			$adacomment="class=has_sign";  $flag='1';
			$title=" title='".getKary($showcomment[$kdorg][$akun]['sdbi']['bgt'][0]['user'])."\n".$showcomment[$kdorg][$akun]['sdbi']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
		}
		$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$kdorg."','".$periode2."','sdbi','bgt')\"";
		$detbgtsdbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$kdpt[$kdorg]."','".$kdorg."','','','','".$periode2."','','".$akun."','html','sdbi','budget')";
		
		$tab.="<td valign=top align=right ".$clickdetail.">" . @number_format($realsdbi[$akun]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($realsdbi[$akun]/$luastotal) . "</td>";
		$tab.="<td valign=top align=right ".$detbgtsdbi.">" . @number_format($bgtsdbi[$akun]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($bgtsdbi[$akun]/$luasbgt) . "</td>";
		@$persensdbi=$realbi[$akun]/$bgtbi[$akun]*100;
		if($persensdbi>100){$i=" style=font-weight:bold;color:red";}else{$i="";}
		
		# tahun
		$click=$adacomment=""; $flag='0'; $clickdetail="";
		$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
		if(!empty($showcomment[$kdorg][$akun]['thn']['bgt'])){
			$adacomment="class=has_sign";  $flag='1';
			$title=" title='".getKary($showcomment[$kdorg][$akun]['thn']['bgt'][0]['user'])."\n".$showcomment[$kdorg][$akun]['thn']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
		}
		$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$kdorg."','".$periode2."','thn','bgt')\"";
		$detbgtthn="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$kdpt[$kdorg]."','".$kdorg."','','','','".$periode2."','','".$akun."','html','thn','budget')";
		$tab.="<td valign=top align=right ".$i.">" . @number_format($persensdbi,2) . "</td>";
		$tab.="<td valign=top align=right ".$detbgtthn.">" . @number_format($bgtthn[$akun]) . "</td>";
		$tab.="<td valign=top align=right>" . @number_format($bgtthn[$akun]/$luasbgt) . "</td>";
		$tab.= "</tr>";
		
		@$ttlrealbi+=$realbi[$akun];
		@$ttlrealsdbi+=$realsdbi[$akun];
		@$ttlbgtbi+=$bgtbi[$akun];
		@$ttlbgtsdbi+=$bgtsdbi[$akun];
		@$ttlbgtthn+=$bgtthn[$akun];
		
	}
	
	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#E8DAEF>";
	$tab.="<td valign=top align=center colspan=3>T O T A L</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlrealbi) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlrealbi/$luastotal) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlbgtbi) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlbgtbi/$luasbgt) . "</td>";
	@$ttlpersenbi=$ttlrealbi/$ttlbgtbi*100;
	if($ttlpersenbi>100){$i=" style=font-weight:bold;color:red";}else{$i="";}
	$tab.="<td valign=top align=right ".$i.">" . @number_format($ttlpersenbi,2) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlrealsdbi) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlrealsdbi/$luastotal) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlbgtsdbi) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlbgtsdbi/$luasbgt) . "</td>";
	@$ttlpersensdbi=$ttlrealsdbi/$ttlbgtsdbi*100;
	if($ttlpersensdbi>100){$i=" style=font-weight:bold;color:red";}else{$i="";}
	$tab.="<td valign=top align=right ".$i.">" . @number_format($ttlpersensdbi,2) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlbgtthn) . "</td>";
	$tab.="<td valign=top align=right>" . @number_format($ttlbgtthn/$luasbgt) . "</td>";
	$tab.= "</tr>";
}


$tab.="</tbody></table></div>";
$brdt="";
$bgwrn="";
if ($proses == 'excel') {
    $brdt=" border=1";
    $bgwrn=" bgcolor=#E8DAEF";
}
$tab.="<br /><div class='table-scroll'><table class=sortable cellspacing=1 ".$brdt." width=100%>
    <thead>
        <tr class=rowheader>
            <th align=center ".$bgwrn." rowspan=3 >".$_SESSION['lang']['divisi']."</th>
            <th align=center ".$bgwrn." colspan=7>Bulan Ini</th>
            <th align=center ".$bgwrn." colspan=7>S/D Bulan Ini</th>
            <th align=center ".$bgwrn." rowspan=2 colspan=3>" . $_SESSION['lang']['budget'] . "<br>" . $_SESSION['lang']['setahun'] . "</th>
        </tr>    
        <tr class=rowheader>
            <th align=center ".$bgwrn." colspan=3>" . $_SESSION['lang']['realisasi'] . "</th>
            <th align=center ".$bgwrn." colspan=3>" . $_SESSION['lang']['budget'] . "</th>
            <th align=center ".$bgwrn." rowspan=2>%</th>
            <th align=center ".$bgwrn." colspan=3>" . $_SESSION['lang']['realisasi'] . "</th>
            <th align=center ".$bgwrn." colspan=3>" . $_SESSION['lang']['budget'] . "</th>
            <th align=center ".$bgwrn." rowspan=2>%</th>
		</tr>    
		</tr>    
        <tr class=rowheader>
            <th align=center ".$bgwrn.">Luas</th>
            <th align=center ".$bgwrn.">Total</th>
            <th align=center ".$bgwrn.">Rp/Ha</th>
            <th align=center ".$bgwrn.">Luas</th>
            <th align=center ".$bgwrn.">Total</th>
            <th align=center ".$bgwrn.">Rp/Ha</th>
            <th align=center ".$bgwrn.">Luas</th>
            <th align=center ".$bgwrn.">Total</th>
            <th align=center ".$bgwrn.">Rp/Ha</th>
            <th align=center ".$bgwrn.">Luas</th>
            <th align=center ".$bgwrn.">Total</th>
            <th align=center ".$bgwrn.">Rp/Ha</th>
            <th align=center ".$bgwrn.">Luas</th>
			<th align=center ".$bgwrn.">Total</th>
            <th align=center ".$bgwrn.">Rp/Ha</th></tr>	
        ";
$tab.="</thead><tbody>";
foreach($luasTotalDiv as $rwDt=>$isi){
    //exit('warning'.$ttlbgtthn);
    @$prosReal=$ttlrealbi*($isi['divactual']/$luastotal);
    @$haDivReal=$prosReal/$isi['divactual'];
    @$prosBgt=$ttlbgtbi*($isi['divbgt']/$luasbgt);
    @$haDivBgt=$prosBgt/$isi['divbgt'];
    @$prsnBndng=($prosReal/$prosBgt)*100;
    $tab.="<tr class=rowcontent>";
    $tab.="<td>".$rwDt."</td>";
    $tab.="<td align=right>".@number_format($isi['divactual'],2)."</td>";
    $tab.="<td align=right>".@number_format($prosReal)."</td>";
    $tab.="<td align=right>".@number_format($haDivReal)."</td>";
    $tab.="<td align=right>".@number_format($isi['divbgt'],2)."</td>";
    $tab.="<td align=right>".@number_format($prosBgt)."</td>";
    $tab.="<td align=right>".@number_format($haDivBgt)."</td>";
    $tab.="<td align=right>".@number_format($prsnBndng,2)."</td>";
    @$totHaAllReal+=$isi['divactual'];
    @$totByAllReal+=$prosReal;
    @$totHaAllBgt+=$isi['divbgt'];
    @$totByAllBgt+=$prosBgt;

    @$prosRealsdbi=$ttlrealsdbi*($isi['divactual']/$luastotal);
    @$haDivRealsdbi=$prosRealsdbi/$isi['divactual'];
    @$prosBgtsdbi=$ttlbgtsdbi*($isi['divbgt']/$luasbgt);
    @$haDivBgtsdbi=$prosBgtsdbi/$isi['divbgt'];
    @$prsnBndngsdbi=($prosRealsdbi/$prosBgtsdbi)*100;
    $tab.="<td align=right>".@number_format($isi['divactual'],2)."</td>";
    $tab.="<td align=right>".@number_format($prosRealsdbi)."</td>";
    $tab.="<td align=right>".@number_format($haDivRealsdbi)."</td>";
    $tab.="<td align=right>".@number_format($isi['divbgt'],2)."</td>";
    $tab.="<td align=right>".@number_format($prosBgtsdbi)."</td>";
    $tab.="<td align=right>".@number_format($haDivBgtsdbi)."</td>";
    $tab.="<td align=right>".@number_format($prsnBndngsdbi,2)."</td>";
    $tab.="<td align=right>".@number_format($isi['divactual'],2)."</td>";
    
    @$totByAllRealSdbi+=$prosRealsdbi;
    @$totByAllBgtSdbi+=$prosBgtsdbi;

    @$prosBgtThnan=$ttlbgtthn*($isi['divbgt']/$luasbgt);
    $tab.="<td align=right>".number_format($prosBgtThnan,2)."</td>";
    @$haDivBgtThnan=$prosBgtThnan/$isi['divbgt'];
    $tab.="<td align=right>".number_format($haDivBgtThnan,2)."</td>";
    $tab.="</tr>";
    @$totbyall+=$prosBgtThnan;
}
@$haDivReal=$totHaAllReal/$totHaAllReal;
@$haDivBgt=$totByAllBgt/$totHaAllBgt;
if($totByAllBgt==0){$totByAllBgt=1;}

$prsnAll=($totByAllReal/$totByAllBgt)*100;
$tab.="<tr class=rowcontent><td>&nbsp;</td>";
$tab.="<td align=right>".number_format($totHaAllReal,2)."</td>";
$tab.="<td align=right>".number_format($totByAllReal)."</td>";
$tab.="<td align=right>".number_format($haDivReal)."</td>";
$tab.="<td align=right>".number_format($totHaAllBgt,2)."</td>";
$tab.="<td align=right>".number_format($totByAllBgt)."</td>";
$tab.="<td align=right>".number_format($haDivBgt)."</td>";
$tab.="<td align=right>".number_format($prsnAll,2)."</td>";
$tab.="<td align=right>".number_format($totHaAllReal,2)."</td>";
@$haDivRealsdbi=$totByAllRealSdbi/$totHaAllReal;
@$haDivBgtsdbi=$totByAllBgtSdbi/$totHaAllBgt;
@$prsnBndngsdbi=($totByAllRealSdbi/$totByAllBgtSdbi)*100;
$tab.="<td align=right>".number_format($totByAllRealSdbi)."</td>";
$tab.="<td align=right>".number_format($haDivRealsdbi)."</td>";
$tab.="<td align=right>".number_format($totHaAllBgt,2)."</td>";
$tab.="<td align=right>".number_format($totByAllBgtSdbi)."</td>";
$tab.="<td align=right>".number_format($haDivBgtsdbi)."</td>";
$tab.="<td align=right>".number_format($prsnBndngsdbi,2)."</td>";
$tab.="<td align=right>".number_format($totHaAllReal,2)."</td>";
$tab.="<td align=right>".number_format($totbyall,2)."</td>";
$haDivBgtThnan=($totbyall/$totHaAllReal);
$tab.="<td align=right>".number_format($haDivBgtThnan,2)."</td>";
$tab.="</tr></tbody></table></div>";

switch ($proses) {
######PREVIEW
    case 'preview':
        echo $tab;
        break;

######EXCEL	
    case 'excel':
        $tempnm = explode("/",$_SERVER['PHP_SELF']);
		$nop_ = "biaya_tidak_langsung_".substr($tempnm[2],0,strripos($tempnm[2],'.'));
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
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