<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
ini_set('display_errors',0);
error_reporting(0);

$proses          = checkPostGet('proses','');
$prd             = checkPostGet('periodetahun','');
$periode         = substr($prd,0,4);
$unit            = checkPostGet('unittahun','');
$intiplasmatahun = checkPostGet('intiplasmatahun',''); 

$jenisbibit=makeOption($dbname,'setup_blok','kodeorg,jenisbibit');
$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

if($intiplasmatahun!=''){
    $inplas=" and intiplasma='".$intiplasmatahun."'";
}
// print_r($_POST);
#ambil  blok dan tahun tanam
$str="select kodeorg,tahuntanam,luasareaproduktif,intiplasma,jumlahpokok from ".$dbname.".setup_blok where kodeorg like '".$unit."%' ".$inplas." and statusblok='TM' order by substr(kodeorg,1,6) asc, tahuntanam asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $kodeblok[$bar->kodeorg]=$bar->kodeorg;
    $thntanam[$bar->kodeorg]=$bar->tahuntanam;
    $luas[$bar->kodeorg]=$bar->luasareaproduktif;
    $pokok[$bar->kodeorg]=$bar->jumlahpokok;
}
#ambil 
$str="select sum(kgwb) as kg, left(tanggal,7) as periode,blok from ".$dbname.".kebun_spb_vw4 where blok like '".$unit."%' and substr(tanggal,1,7) <= '".$prd."' and tanggal like '".$periode."%' group by blok,left(tanggal,7) order by left(tanggal,7),blok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $produksi[$bar->blok][$bar->periode]=$bar->kg;
	
	$kodeblok[$bar->blok]=$bar->blok;
	$thntanam[$bar->blok]=getBlok($bar->blok,'tahuntanam');
    $luas[$bar->blok]=getBlok($bar->blok,'luasareaproduktif');
    $pokok[$bar->blok]=getBlok($bar->blok,'jumlahpokok');
}
#ambil budget;
$str="select kodeblok,kg01,kg02,kg03,kg04,kg05,kg06,kg07,kg08,kg09,kg10,kg01,kg11,kg12 from ".$dbname.".bgt_produksi_kbn_kg_vw
          where tahunbudget=".$periode." and kodeunit='".$unit."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $budget[$bar->kodeblok][$periode."-01"]=$bar->kg01;
    $budget[$bar->kodeblok][$periode."-02"]=$bar->kg02;
    $budget[$bar->kodeblok][$periode."-03"]=$bar->kg03;
    $budget[$bar->kodeblok][$periode."-04"]=$bar->kg04;
    $budget[$bar->kodeblok][$periode."-05"]=$bar->kg05;
    $budget[$bar->kodeblok][$periode."-06"]=$bar->kg06;
    $budget[$bar->kodeblok][$periode."-07"]=$bar->kg07;
    $budget[$bar->kodeblok][$periode."-08"]=$bar->kg08;
    $budget[$bar->kodeblok][$periode."-09"]=$bar->kg09;
    $budget[$bar->kodeblok][$periode."-10"]=$bar->kg10;
    $budget[$bar->kodeblok][$periode."-11"]=$bar->kg11;
    $budget[$bar->kodeblok][$periode."-12"]=$bar->kg12;
	
	$kodeblok[$bar->kodeblok]=$bar->kodeblok;
	$thntanam[$bar->kodeblok]=getBlok($bar->kodeblok,'tahuntanam');
    $luas[$bar->kodeblok]=getBlok($bar->kodeblok,'luasareaproduktif');
    $pokok[$bar->kodeblok]=getBlok($bar->kodeblok,'jumlahpokok');
}

if($proses=='excel'){
    $border="border=1";
}

$stream="
	  <table class=sortable cellpadding=5 cellspacing=1 ".$border.">
	   <thead>
		<tr class=rowheader>
		   <th rowspan=3 align=center>No</th>
		   <th rowspan=3 align=center>Kode Blok</th>
		   <th rowspan=3 align=center>Nama Blok</th>
		   <th rowspan=3 align=center>".$_SESSION['lang']['tahuntanam']."</th>
		   <th rowspan=3 align=center>".$_SESSION['lang']['jenisbibit']."</th>
		   <th rowspan=3 align=center>".$_SESSION['lang']['luas']."</th>
		   <th rowspan=3 align=center>".$_SESSION['lang']['pokok']."</th>
		   <th rowspan=3 align=center>".$_SESSION['lang']['sph']."</th>
		   <th colspan=5 align=center>Jan</th>
		   <th colspan=5 align=center>Feb</th>
		   <th colspan=5 align=center>Mar</th>
		   <th colspan=5 align=center>Apr</th>
		   <th colspan=5 align=center>Mei</th>
		   <th colspan=5 align=center>Jun</th>
		   <th colspan=5 align=center>Jul</th>
		   <th colspan=5 align=center>Aug</th>
		   <th colspan=5 align=center>Sep</th>
		   <th colspan=5 align=center>Okt</th>
		   <th colspan=5 align=center>Nop</th>
		   <th colspan=5 align=center>Dec</th>
		   <th colspan=8 align=center>Total</th>
		</tr>
		<tr class=rowheader>";
		for($i=1;$i<=12;$i++){
			$stream.="       
			<th align=center colspan=2>Kg</th>
			<th align=center colspan=2>Kg/Ha</th>  
			<th align=center rowspan=2>%</th>";  
		}
		
		#total kanan
		$stream.="       
			<th align=center colspan=3>Kg</th>
			<th align=center colspan=3>Kg/Ha</th>  
			<th align=center colspan=2>%</th>";
       
		$stream.="</tr><tr class=rowheader>";
			for($i=1;$i<=12;$i++){
				$stream.="       
				<th align=center>Bgt</th>
				<th align=center>Act</th>
				<th align=center>Bgt</th>
				<th align=center>Act</th>";  
			} 
		#total kanan
		$stream.="       
				<th align=center>Bgt Setahun</th>
				<th align=center>Bgt Sd/Bi</th>
				<th align=center>Act</th>
				<th align=center>Bgt Setahun</th>
				<th align=center>Bgt SDBI</th>
				<th align=center>Act</th> 
				<th align=center>Setahun</th> 
				<th align=center>Sd/Bi</th>"; 
				
       $stream.="      
            </tr>
            </thead>
            <tbody>";
			
	$detaillaporan = 'LAP0000004';
	$str = "select * from ".$dbname.".bi_5warnalaporan where idlap = '".$detaillaporan."' order by nilaiawal desc, nilaiakhir desc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$arrWarna = array(); $nomor=0;
	while($bar = $res->fetch()){
		$arrWarna[$bar['warna']]['opawal'] = $bar['opawal'];
		$arrWarna[$bar['warna']]['awal'] = $bar['nilaiawal'];
		$arrWarna[$bar['warna']]['opakhir'] = $bar['opakhir'];
		$arrWarna[$bar['warna']]['akhir'] = $bar['nilaiakhir'];
		$kelasprod[$bar['warna']]=$bar['keterangan'];
	}
		
	$no=1;
	$gtluas=0;
	if(isset($kodeblok)){
		foreach($kodeblok as $blk =>$val){
			$tbgts=0;$tps=0;$tbvs=0;$tpvs=0;
			$stream.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$blk."</td>
				<td>".$namaOrg[$blk]."</td>
				<td align=center>".$thntanam[$blk]."</td>
				<td>".$jenisbibit[$blk]."</td>
				<td align=right>".$luas[$blk]."</td>                   
				<td align=right>".$pokok[$blk]."</td>                   
				<td align=right>".hidezerodecimal($pokok[$blk]/$luas[$blk],2)."</td>                   
				";	
			$ttlluas+=$luas[$blk];
			$ttlpokok+=$pokok[$blk];
			for($x=1;$x<=12;$x++){
				$g=str_pad($x, 2, "0", STR_PAD_LEFT);
				setIt($budget[$val][$periode."-".$g],0);
				setIt($produksi[$val][$periode."-".$g],0);
				setIt($tt1[$x],0);
				setIt($tt2[$x],0);
				$penc[$val][$periode."-".$g]=fixnan($produksi[$val][$periode."-".$g]/$budget[$val][$periode."-".$g]*100);
				if($budget[$val][$periode."-".$g]==0 and $produksi[$val][$periode."-".$g]>0){
					$penc[$val][$periode."-".$g]=100;					
				}
				
				foreach($arrWarna as $key => $row){
					if(my_operator($penc[$val][$periode."-".$g],$row['awal'],$row['opawal']) && my_operator($penc[$val][$periode."-".$g],$row['akhir'],$row['opakhir'])){
						if($periode."-".$g<=$prd){							
							$listpencprd[$val][$periode."-".$g]=$key;
							$totalbycolor[substr($blk,0,6)][$key][$periode."-".$g]['ttl']++;
							$totalbycolor[substr($blk,0,6)][$key][$periode."-".$g]['bgt']+=$budget[$val][$periode."-".$g];
							$totalbycolor[substr($blk,0,6)][$key][$periode."-".$g]['act']+=$produksi[$val][$periode."-".$g];
							
							$gtbycolor[substr($blk,0,6)][$periode."-".$g]['ttl']++;
							$gtbycolor[substr($blk,0,6)][$periode."-".$g]['bgt']+=$budget[$val][$periode."-".$g];
							$gtbycolor[substr($blk,0,6)][$periode."-".$g]['act']+=$produksi[$val][$periode."-".$g];
							
							$gtbycolor[substr($blk,0,4)][$periode."-".$g]['ttl']++;
							$gtbycolor[substr($blk,0,4)][$periode."-".$g]['bgt']+=$budget[$val][$periode."-".$g];
							$gtbycolor[substr($blk,0,4)][$periode."-".$g]['act']+=$produksi[$val][$periode."-".$g];
							
							
							$totalbycolor[substr($blk,0,4)][$key][$periode."-".$g]['ttl']++;
							$totalbycolor[substr($blk,0,4)][$key][$periode."-".$g]['bgt']+=$budget[$val][$periode."-".$g];
							$totalbycolor[substr($blk,0,4)][$key][$periode."-".$g]['act']+=$produksi[$val][$periode."-".$g];
							
							$totalkananbycolor[substr($blk,0,4)][$key]['ttl'][$val]=1;
							$totalkananbycolor[substr($blk,0,4)][$key]['bgtsd']+=$budget[$val][$periode."-".$g];
							$totalkananbycolor[substr($blk,0,4)][$key]['act']+=$produksi[$val][$periode."-".$g];
							$totalkananbycolor[substr($blk,0,6)][$key]['ttl'][$val]=1;
							$totalkananbycolor[substr($blk,0,6)][$key]['bgtsd']+=$budget[$val][$periode."-".$g];
							$totalkananbycolor[substr($blk,0,6)][$key]['act']+=$produksi[$val][$periode."-".$g];
							
							$gtlbycolorblok[substr($blk,0,6)][$val]=1;
							$gtlbycolor[substr($blk,0,6)]['bgtsd']+=$budget[$val][$periode."-".$g];
							$gtlbycolor[substr($blk,0,6)]['act']+=$produksi[$val][$periode."-".$g];
							
							$gtlbycolorblok[substr($blk,0,4)][$val]=1;
							$gtlbycolor[substr($blk,0,4)]['bgtsd']+=$budget[$val][$periode."-".$g];
							$gtlbycolor[substr($blk,0,4)]['act']+=$produksi[$val][$periode."-".$g];							
						}
						$gtlbycolor[substr($blk,0,6)]['bgt']+=$budget[$val][$periode."-".$g];
						$gtlbycolor[substr($blk,0,4)]['bgt']+=$budget[$val][$periode."-".$g];
						$totalkananbycolor[substr($blk,0,4)][$key]['bgt']+=$budget[$val][$periode."-".$g];
						$totalkananbycolor[substr($blk,0,6)][$key]['bgt']+=$budget[$val][$periode."-".$g];
					}	
				}	
				
				$stream.="
					<td align=right>".hidezerodecimal($budget[$val][$periode."-".$g])."</td>
					<td align=right onclick=getdetailprd('".$val."','".$periode."-".$g."'); style=color:blue;cursor:pointer; title='Click untuk melihat detail.'>".hidezerodecimal($produksi[$val][$periode."-".$g])."</td>
					<td align=right>".@hidezerodecimal(fixnan($budget[$val][$periode."-".$g]/$luas[$val]))."</td>
					<td align=right>".@hidezerodecimal(fixnan($produksi[$val][$periode."-".$g]/$luas[$val]))."</td>
					<td align=right title='".$kelasprod[$listpencprd[$val][$periode."-".$g]]."' style=background-color:".$listpencprd[$val][$periode."-".$g].";>".@hidezerodecimal($penc[$val][$periode."-".$g],2)."</td>
					";
					$tbgts+=$budget[$val][$periode."-".$g];
					$tps+=$produksi[$val][$periode."-".$g];
					@$tbvs+=$budget[$val][$periode."-".$g]/$luas[$val];
					@$tpvs+=$produksi[$val][$periode."-".$g]/$luas[$val];
					$tt1[$x]+=$budget[$val][$periode."-".$g];
					$tt2[$x]+=$produksi[$val][$periode."-".$g];
					if($periode."-".$g<=$prd){
						$ttlbgtsdbi[$val]+=$budget[$val][$periode."-".$g];
						$gtttlbgtsdbi+=$budget[$val][$periode."-".$g];
					}
			   
			}
			
			$pencsdbikanan=fixnan($tps/$ttlbgtsdbi[$val]*100);
			if($ttlbgtsdbi[$val]==0 and $tps>0){
				$pencsdbikanan=100;
			}
			$pencthnkanan=fixnan($tps/$tbgts*100);
			if($tbgts==0 and $tps>0){
				$pencsdbikanan=100;
			}
			foreach($arrWarna as $key => $row){
				if(my_operator($pencsdbikanan,$row['awal'],$row['opawal']) && my_operator($pencsdbikanan,$row['akhir'],$row['opakhir'])){
					$listpencprd[$val]['sdbi']=$key;
				}
				if(my_operator($pencthnkanan,$row['awal'],$row['opawal']) && my_operator($pencthnkanan,$row['akhir'],$row['opakhir'])){
					if(substr($prd,-2)==12){
						$listpencprd[$val]['thn']=$key;
					}
				}	
			}	
			
			$stream.="<td align=right>".hidezerodecimal($tbgts)."</td>
				<td align=right>".hidezerodecimal($ttlbgtsdbi[$val])."</td>
				<td align=right>".hidezerodecimal(fixnan($tps))."</td>
				<td align=right>".hidezerodecimal(fixnan($tbvs))."</td>
				<td align=right>".hidezerodecimal(fixnan($ttlbgtsdbi[$val]/$luas[$val]))."</td>
				<td align=right>".hidezerodecimal(fixnan($tpvs))."</td>
				<td align=right style=background-color:".$listpencprd[$val]['thn'].";>".hidezerodecimal(fixnan($pencthnkanan),2)."</td>
				<td align=right style=background-color:".$listpencprd[$val]['sdbi'].";>".hidezerodecimal(fixnan($pencsdbikanan),2)."</td>
			</tr>";
			$gtluas+=$luas[$val];    
			$no++;            
		}
		$gtbgt=$gtpr=0;
		$stream.="<tr class=rowcontent>
        <td colspan=5 align=center>Total</td>
        <td align=right>".$gtluas."</td>
		<td align=right>".hidezerodecimal($ttlpokok)."</td>
		<td align=right>".hidezerodecimal(fixnan($ttlpokok/$gtluas),2)."</td>
		";
		foreach($tt1 as $idx=>$val){
			foreach($arrWarna as $key => $row){
				if(my_operator(fixnan($tt2[$idx]/$val*100),$row['awal'],$row['opawal']) && my_operator(fixnan($tt2[$idx]/$val*100),$row['akhir'],$row['opakhir'])){
					if($idx<=intval(substr($prd,-2))){							
						$listpencprd[$idx]=$key;
					}
				}	
			}	
			$stream.="
				<td align=right>".hidezerodecimal($val)."</td>
                <td align=right>".hidezerodecimal($tt2[$idx])."</td>
                <td align=right>".@hidezerodecimal(fixnan($val/$gtluas))."</td>                    
                <td align=right>".@hidezerodecimal(fixnan($tt2[$idx]/$gtluas))."</td>     
                <td align=right style=background-color:".$listpencprd[$idx].";>".hidezerodecimal(fixnan($tt2[$idx]/$val*100),2)."</td>";
			$gtbgt+=$val;
			$gtpr+=$tt2[$idx];
		}
		
		foreach($arrWarna as $key => $row){
			if(my_operator(fixnan($gtpr/$gtttlbgtsdbi*100),$row['awal'],$row['opawal']) && my_operator(fixnan($gtpr/$gtttlbgtsdbi*100),$row['akhir'],$row['opakhir'])){
				$listpencprd['ttlsdbi']=$key;
				if(substr($prd,-2)==12){
					$listpencprd['ttlthn']=$key;
				}
			}	
		}
		
		$stream.="<td align=right>".hidezerodecimal($gtbgt)."</td>
			<td align=right>".hidezerodecimal($gtttlbgtsdbi)."</td>
			<td align=right>".hidezerodecimal($gtpr)."</td>
			<td align=right>".@hidezerodecimal(fixnan($gtbgt/$gtluas))."</td>                    
			<td align=right>".hidezerodecimal(fixnan($gtttlbgtsdbi/$gtluas))."</td>
			<td align=right>".@hidezerodecimal(fixnan($gtpr/$gtluas))."</td>
			<td align=right style=background-color:".$listpencprd['ttlthn'].";>".hidezerodecimal(fixnan($gtpr/$gtbgt*100),2)."</td>
			<td align=right style=background-color:".$listpencprd['ttlsdbi'].";>".hidezerodecimal(fixnan($gtpr/$gtttlbgtsdbi*100),2)."</td>
			";         
		$stream.="</tr>";
	}

	$stream.="<tr class=rowcontent>";
	$stream.="<td colspan=".((12*5)+16)." style=background-color:#c3fadd>Rekapitulasi</td>";
	$stream.="</tr>";

	$stream.="<tr class=rowcontent>";
	$stream.="<td></td>";
	$stream.="<td colspan=7 style=background-color:#ebfaf7;text-align:center;font-style:italic;>Keterangan</td>";
	for($x=1;$x<=12;$x++){
		$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic;>Bgt</td>";		
		$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic;>Act</td>";		
		$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic; nowrap>% Act</td>";		
		$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic;>Jlh Blok</td>";
		$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic; nowrap>% Blok</td>";		
	}
	
	$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic;>Bgt Setahun</td>";		
	$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic;>Bgt Sd/Bi</td>";		
	$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic;>Act</td>";		
	$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic; colspan=3>% Act vs Setahun</td>";
	$stream.="<td style=background-color:#ebfaf7;text-align:center;font-style:italic; colspan=2>% Act vs Sd/Bi</td>";
	
	$stream.="</tr>";
	ksort($totalbycolor);
	foreach($totalbycolor as $kodeunit => $val1){
		$stream.="<tr class=rowcontent style=font-weight:bold>";
		$stream.="<td></td>";
		$stream.="<td colspan=7>".getNamaOrg($kodeunit)."</td>";
		for($x=1;$x<=12;$x++){
			$period=$periode."-".addzero($x,2);
			$stream.="<td align=right>".hidezerodecimal($gtbycolor[$kodeunit][$period]['bgt'])."</td>";
			$stream.="<td align=right>".hidezerodecimal($gtbycolor[$kodeunit][$period]['act'])."</td>";
			$stream.="<td align=right>".hidezerodecimal(fixnan($gtbycolor[$kodeunit][$period]['act']/$gtbycolor[$kodeunit][$period]['bgt']*100),2)."</td>";
			$stream.="<td align=center>".hidezerodecimal($gtbycolor[$kodeunit][$period]['ttl'])."</td>";
			$stream.="<td></td>";
		}		
		$stream.="<td align=right>".hidezerodecimal($gtlbycolor[$kodeunit]['bgt'])."</td>";
		$stream.="<td align=right>".hidezerodecimal($gtlbycolor[$kodeunit]['bgtsd'])."</td>";
		$stream.="<td align=right>".hidezerodecimal($gtlbycolor[$kodeunit]['act'])."</td>";
		$stream.="<td align=right colspan=3>".hidezerodecimal(fixnan($gtlbycolor[$kodeunit]['act']/$gtlbycolor[$kodeunit]['bgt']*100),2)."</td>";
		$stream.="<td align=right colspan=2>".hidezerodecimal(fixnan($gtlbycolor[$kodeunit]['act']/$gtlbycolor[$kodeunit]['bgtsd']*100),2)."</td>";
		
		
		$stream.="</tr>";
		foreach($kelasprod as $warna => $ket){
			$stream.="<tr class=rowcontent>";
			$stream.="<td></td>";
			$stream.="<td style=background-color:".$warna."></td>";
			$stream.="<td colspan=6>".$kelasprod[$warna]."</td>";
			for($x=1;$x<=12;$x++){
				$period=$periode."-".addzero($x,2);
				$stream.="<td align=right>".hidezerodecimal($val1[$warna][$period]['bgt'])."</td>";
				$stream.="<td align=right>".hidezerodecimal($val1[$warna][$period]['act'])."</td>";
				$stream.="<td align=right>".hidezerodecimal(fixnan($val1[$warna][$period]['act']/$gtbycolor[$kodeunit][$period]['bgt']*100),2)."</td>";
				$stream.="<td align=center>".hidezerodecimal($val1[$warna][$period]['ttl'])."</td>";
				$stream.="<td align=right>".hidezerodecimal(fixnan($val1[$warna][$period]['ttl']/$gtbycolor[$kodeunit][$period]['ttl']*100),2)."</td>";
			}
			
			$stream.="<td align=right>".hidezerodecimal($totalkananbycolor[$kodeunit][$warna]['bgt'])."</td>";
			$stream.="<td align=right>".hidezerodecimal($totalkananbycolor[$kodeunit][$warna]['bgtsd'])."</td>";
			$stream.="<td align=right>".hidezerodecimal($totalkananbycolor[$kodeunit][$warna]['act'])."</td>";
			$stream.="<td align=right colspan=3>".hidezerodecimal(fixnan($totalkananbycolor[$kodeunit][$warna]['act']/$gtlbycolor[$kodeunit]['bgt']*100),2)."</td>";
			$stream.="<td align=right colspan=2>".hidezerodecimal(fixnan($totalkananbycolor[$kodeunit][$warna]['act']/$gtlbycolor[$kodeunit]['bgtsd']*100),2)."</td>";
			
			$stream.="</tr>";
		}
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=".((12*5)+16).">&nbsp;</td>";
		$stream.="</tr>";
	}
	
	
	$stream.="</tbody><tfoot>";
    $stream.="</tfoot></table>";
	if($proses=='excel'){
		// $stream.="<br>Legend: <br><table>";
		// $no=0;
		// foreach($kelasprod as $color => $kete){
			// $no++;
			// $stream.="<tr class=rowcontent>
				// <td align=center>".$no."</td>
				// <td style=background-color:".$color."></td>
				// <td colspan=3>".$kete."</td>
			// ";         
			// $stream.="</tr>";
		// }
		// $stream.="</table>";
	}	
	
	// echo"<pre>";
	// print_r($totalkananbycolor);
	// echo"</pre>";
	
switch ($proses){
	case 'preview':
			echo $stream;
	break;
	case 'excel':
		$nop_="Trend_Produksi_".$unit."_".$periode;
		if(strlen($stream)>0){
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
	
		// if(strlen($stream)>0)
		// {
			 // $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
			 // gzwrite($gztralala, $stream);
			 // gzclose($gztralala);
			 // echo "<script language=javascript1.2>
				// window.location='tempExcel/".$nop_.".xls.gz';
				// </script>";
		// }
		 break;
}

function my_operator($a, $b, $char) {
	switch($char) {
		case '=': return $a == $b;
		case '<=': return $a <= $b;
		case '>=': return $a >= $b;
		case '<': return $a < $b;
		case '>': return $a > $b;
	}
}

function numbertohuruf($no){
	$range=range("A","Z");
	foreach($range as $n => $huruf){
		if(($n+1)==$no){
			return $huruf;
		}
	}
}

?>