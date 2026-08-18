<?php
// ini_set('display_errors',0);
// error_reporting(0);

// session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$method        = checkPostGet('method','');
$periode       = checkPostGet('periode','');
$tampil        = checkPostGet('tampil','');
$pabrik        = checkPostGet('pabrik','');
$tanggal       = tanggalsystemn(checkPostGet('tanggal',''));
$sisatbskemarin= checkPostGet('sisatbskemarin','');
$tbsmasuk      = checkPostGet('tbsmasuk','');
$sisahariini   = checkPostGet('sisahariini','');
$tipe          = checkPostGet('tipe','');

$kdpt          = makeOption($dbname,'organisasi','kodeorganisasi,induk');
$nmorg         = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$arrnmtangki   = makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan',"kodeorg='".$pabrik."'");
$arrkapstangki = makeOption($dbname,'pabrik_5tangki','kodetangki,kapasitas',"kodeorg='".$pabrik."'");
$arrnmtangkicpo= makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan',"kodeorg='".$pabrik."' and komoditi='CPO'","2");
$result='';


switch($method){
	case 'preview':
	
		$str="select * from ".$dbname.".pabrik_produksi where tanggal like '".$periode."%' and kodeorg='".$pabrik."' order by tanggal desc";
		$res2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res2->setFetchMode(PDO::FETCH_ASSOC);
		while($datArr=$res2->fetch()){
			$tbs[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['tbsdiolah'];
			$jmOer[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['oer'];
			$jmOerPk[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['oerpk'];
		}
		
		$bg=" bgcolor=#DEDEDE";
		$brdr="0";
		$komanya=3;
		
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=".$brdr.">
			<thead>
			<tr class=rowheader>
				<th align=center ".$bg.">".$_SESSION['lang']['kodeorganisasi']."</th>
				<th align=center ".$bg.">".$_SESSION['lang']['tanggal']."</th>
				<th align=center ".$bg.">".$_SESSION['lang']['sisatbskemarin']."</th>
				<th align=center ".$bg.">".$_SESSION['lang']['masuk']."</th>
				<th align=center ".$bg.">".$_SESSION['lang']['tersedia']." (Kg)</th>
				<th align=center ".$bg.">".$_SESSION['lang']['produksi']." CPO (Kg)</th>
				<th align=center ".$bg.">".$_SESSION['lang']['produksi']." PK (Kg)</th>
				<th colspan=2 align=center  ".$bg.">".$_SESSION['lang']['print']."</th>
			</tr>
			</thead>
			<tbody>";

		if(count(fetchdata($str)) > 0){				
			$tgl=1;
			$cposdkem=0;
			$ffasdkem=0;
			$kotsdkem=0;
			$airsdkem=0;
				
			$kersdkem=0;
			$ffksdkem=0;
			$koksdkem=0;
			$aiksdkem=0;
			$ared=0;
				
			$sJamPeng=$sJamStag=0;
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch()){
				$tab.="<tr class=rowcontent>";
				$tab.="<td>".$bar->kodeorg."</td>";
				$tab.="<td>".tanggalnormal($bar->tanggal)."</td>";
				$tab.="<td align=right>".hidezerodecimal($bar->sisatbskemarin,0,'.',',')."</td>";
				$tab.="<td align=right>".hidezerodecimal($bar->tbsmasuk,0,'.',',')."</td>";
				$tab.="<td align=right>".@hidezerodecimal($bar->tbsmasuk+$bar->sisatbskemarin,0,'.',',')."</td>";
				$tab.="<td align=right>".@hidezerodecimal($bar->oer,0,'.',',')."</td>";
				$tab.="<td align=right>".@hidezerodecimal($bar->oerpk,0,'.',',')."</td>";
				
				$tab.="<td><img src='images/skyblue/zoom.png' class=resicon title='preview' onclick=laporanhtml('".tanggalnormal($bar->tanggal)."','".($bar->sisatbskemarin)."','".($bar->tbsmasuk)."','".$pabrik."','preview',event)></td>";
				$tab.="<td><img src='images/skyblue/excel.jpg' class=resicon title='Spreadsheet' onclick=laporanEXCEL('".tanggalnormal($bar->tanggal)."','".($bar->sisatbskemarin)."','".($bar->tbsmasuk)."','".$pabrik."','excel',event)></td>";
				$tab.="</tr>";
				
				$ttlmasuk+=$bar->tbsmasuk;
				$ttloer+=$bar->oer;
				$ttloerpk+=$bar->oerpk;
			}
			
			$tab.="<tr class=rowcontent style='font-weight:bold'>";
			$tab.="<td colspan=3 style='text-align:center'>".$_SESSION['lang']['total']."</td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($ttlmasuk)."</td>";
			$tab.="<td></td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($ttloer)."</td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($ttloerpk)."</td>";
			$tab.="<td colspan=2></td>";
			$tab.="</tr>";
		}else{
			$tab.="<tr class=rowcontent><td colspan=9 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}
		
		$tab.="	</tbody>
			<tfoot>
			</tfoot>
		</table>
		</fieldset>";
		
		echo $tab;
		break;
		
	case 'excel':
			
		$tahun     = substr($tanggal,0,4);
		$tglawalthn= $tahun."-01-01";
		$bulan     = substr($tanggal,5,2);
		$tglskrg   = substr($tanggal,-2);
		
	
		$blnhi       =substr($tanggal,0,7);
		$tglawalblnhi=$blnhi."-01";  
	
	
		$tglbesok = strtotime('+1 day',strtotime($tanggal));
		$tglbesok = date('Y-m-d', $tglbesok);

		$tglkemarin = strtotime('-1 day',strtotime($tanggal));
		$tglkemarin = date('Y-m-d', $tglkemarin);


		$tglblnkemarin = strtotime('-1 month',strtotime($tanggal));
		$tglblnkemarin = date('Y-m-d', $tglblnkemarin);

		$blnlaluhi=substr($tglblnkemarin,0,7);
		$tglawalblnhilalu=$blnlaluhi."-01";

		##################################
		##################################
		##################################
	
		#= kebun sendiri / inti
		$str="select * from ".$dbname.".pabrik_sortasi_vw where
		tanggal between '".$tglawalblnhi."' and '".$tanggal."' and millcode='".$pabrik."' 
		and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
		and intiplasma='INTI' and kodebarang='40000003' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if ($bar['persen'] != 0) {
			$sortasi[$bar['kodeorg']][$bar['kodefraksi']]+=$bar['persen'];
			$sortasi[$bar['kodeorg']][$bar['kodefraksi']]+=$bar['persen'];
			$sortasi[$bar['kodeorg']][$bar['kodefraksi']]+=$bar['persen'];
			$sortasi[$bar['kodeorg']][$bar['kodefraksi']]+=$bar['persen'];
			$sortasi[$bar['kodeorg']][$bar['kodefraksi']]+=$bar['persen'];
			$sortasi[$bar['kodeorg']][$bar['kodefraksi']]+=$bar['persen'];
			$rowdata[$bar['kodeorg']][$bar['kodefraksi']]+=1;
			}
		}


		// echo "<pre>";
		// print_r($sortasi);
		// echo "</pre>";


		#to hari ini
		// $str="select * from ".$dbname.".pabrik_timbangan_vw where 
		// tanggal between '".$tanggal."' and '".$tanggal."' and millcode='".$pabrik."' 
		// and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
		// and intiplasma='INTI' and kodebarang='40000003'";

		$str="select * from ".$dbname.".pabrik_timbangan_vw where 
		tanggal between '".$tanggal."' and '".$tanggal."' and millcode='".$pabrik."' 
		and intiplasma='INTI' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kdorgi[$bar['kodeorg']]=$bar['kodeorg'];
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsterimaintibrutohi[$bar['kodeorg']]+=$bar['beratbersih'];
				@$tbspotintihi[$bar['kodeorg']]+=$bar['kgpotsortasi'];
				@$tbsterimaintinettohi[$bar['kodeorg']]+=($bar['beratbersih']);
			}
			$rit+=1;
		}
		
		
		// $str="select * from ".$dbname.".pabrik_timbangan_vw where 
		// tanggal between '".$tanggal."' and '".$tanggal."' and millcode='".$pabrik."' 
		// and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
		// and intiplasma='KUD' and kodebarang='40000003'";
		$str="select * from ".$dbname.".pabrik_timbangan_vw where 
		tanggal between '".$tanggal."' and '".$tanggal."' and millcode='".$pabrik."' 
		and intiplasma='KUD' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kdorgp[$bar['kodeorg']]=$bar['kodeorg'];
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsterimaintibrutohip[$bar['kodeorg']]+=$bar['beratbersih'];
				@$tbspotintihip[$bar['kodeorg']]+=$bar['kgpotsortasi'];
				@$tbsterimaintinettohip[$bar['kodeorg']]+=($bar['beratbersih']);
			}
			$rit+=1;
		}

		#to bln ini
		$str="select * from ".$dbname.".pabrik_timbangan_vw where 
		tanggal between '".$tglawalblnhi."' and '".$tanggal."' and millcode='".$pabrik."' 
		and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
		and intiplasma='INTI' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kdorgi[$bar['kodeorg']]=$bar['kodeorg'];
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsterimaintibrutobi[$bar['kodeorg']]+=$bar['beratbersih'];
				@$tbspotintibi[$bar['kodeorg']]+=$bar['kgpotsortasi'];
				@$tbsterimaintinettobi[$bar['kodeorg']]+=($bar['beratbersih']);
			}
		}
		$str="select * from ".$dbname.".pabrik_timbangan_vw where 
		tanggal between '".$tglawalblnhi."' and '".$tanggal."' and millcode='".$pabrik."' 
		and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
		and intiplasma='KUD' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kdorgp[$bar['kodeorg']]=$bar['kodeorg'];
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsterimaintibrutobip[$bar['kodeorg']]+=$bar['beratbersih'];
				@$tbspotintibip[$bar['kodeorg']]+=$bar['kgpotsortasi'];
				@$tbsterimaintinettobip[$bar['kodeorg']]+=($bar['beratbersih']);
			}
		}
		
		#BUDGET INTI
		$jlhhari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
		$str = "select sum(kg".$bulan.") as bi, kodeunit, intiplasma from ".$dbname.".bgt_produksi_kebun where tahunbudget = '".$tahun."' and (kodeunit in ('".implode("','",$kdorgi)."') or kodeunit in ('".implode("','",$kdorgp)."')) group by kodeunit, intiplasma";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['intiplasma']=='I'){				
				$bgttbsihi[$bar['kodeunit']]+=($bar['bi']/$jlhhari);
				$bgttbsisdhi[$bar['kodeunit']]+=(($bar['bi']/$jlhhari)*intval($tglskrg));
				$bgttbsibi[$bar['kodeunit']]+=$bar['bi'];
			}
			if($bar['intiplasma']=='P'){				
				$bgttbsphi[$bar['kodeunit']]+=($bar['bi']/$jlhhari);
				$bgttbspsdhi[$bar['kodeunit']]+=(($bar['bi']/$jlhhari)*intval($tglskrg));
				$bgttbspbi[$bar['kodeunit']]+=$bar['bi'];
			}
		}
		
		$arrCust=array();
		#= external
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalthn."' and '".$tanggal."' 
		and millcode='".$pabrik."' and intex='0'  and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrCust[$bar['kodecustomer']]=$bar['kodecustomer'];
			if($bar['tanggal']==$tanggal){
			
				@$tbsterimaexternalbrutohi[$bar['kodecustomer']]+=$bar['beratbersih'];
				@$tbspotexternalhi[$bar['kodecustomer']]+=$bar['kgpotsortasi'];
				@$tbsterimaexternalnettohi[$bar['kodecustomer']]+=$bar['beratbersih'];
			}
			
			@$tbsterimaexternalbrutoti[$bar['kodecustomer']]+=$bar['beratbersih'];
			@$tbspotexternalti[$bar['kodecustomer']]+=$bar['kgpotsortasi'];
			@$tbsterimaexternalnettoti[$bar['kodecustomer']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
		and millcode='".$pabrik."'  and intex='0' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				$arrCust[$bar['kodecustomer']]=$bar['kodecustomer'];
				@$tbsterimaexternalbrutobi[$bar['kodecustomer']]+=$bar['beratbersih'];
				@$tbspotexternalbi[$bar['kodecustomer']]+=$bar['kgpotsortasi'];
				@$tbsterimaexternalnettobi[$bar['kodecustomer']]+=$bar['beratbersih'];
			}
			$rit+=1;
		}
		
		$arrCust['tbsexternal']='tbsexternal';
		$str = "select sum(olah".$bulan.") as bi, millcode from ".$dbname.".bgt_produksi_pks where tahunbudget = '".$tahun."' and millcode='".$pabrik."' and kodeunit='tbsexternal' group by millcode";
		$res = fetchdata($str);
		foreach($res as $bar){
			$bgttbsexthi['tbsexternal']+=($bar['bi']/$jlhhari);
			$bgttbsextsdhi['tbsexternal']+=(($bar['bi']/$jlhhari)*intval($tglskrg));
			$bgttbsextbi['tbsexternal']+=$bar['bi'];
		}
		
		#= bentuk total
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalthn."' and '".$tanggal."' 
		and millcode='".$pabrik."' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				@$tbsterimabrutohi+=$bar['beratbersih'];
				@$tbspothi+=$bar['kgpotsortasi'];
				@$tbsterimanettohi+=($bar['beratbersih']);
			}
			
			@$tbsterimabrutoti+=$bar['beratbersih'];
			@$tbspotti+=$bar['kgpotsortasi'];
			@$tbsterimanettoti+=($bar['beratbersih']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
		and millcode='".$pabrik."' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsterimabrutobi+=$bar['beratbersih'];
				@$tbspotbi+=$bar['kgpotsortasi'];
				@$tbsterimanettobi+=($bar['beratbersih']);
			}
			
		}
		
		
		
		##################################
		##################################
		##################################
		#produce cpo hari ini
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tanggal."' and '".$tanggal."' 
		and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			// @$hariolah+=1;
			if($bar['tanggal']==$tanggal){
				@$tbsawalhi=$bar['sisatbskemarin'];
				@$tbsolahhi=$bar['tbsdiolah'];
				@$tbssisahi=$bar['sisahariini'];
				@$cpoprodhi=$bar['oer'];
				@$kerprodhi=$bar['oerpk'];
				@$ffacpohi=$bar['ffa'];
				@$bkpkhi=$bar['ffapk'];
				@$vmcpohi=$bar['kadarair'];
				@$vmpkhi=$bar['kadarairpk'];
				@$dirtpohi=$bar['kadarkotoran'];
				@$dirtpkhi=$bar['kadarkotoranpk'];
				@$dobicpohi=$bar['kadarkotoran'];
				@$onsistemcpo=$bar['cpoonsistem'];
			}
			
			if($bar['tanggal']==$tglkemarin){
				@$cposawalhi=$bar['oer'];
				@$kersawalhi=$bar['oerpk'];
			}
		}

		$str="select * from ".$dbname.".pabrik_produksi where tanggal = '".$tglkemarin."' and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$onsistemcpokemarin=$bar['cpoonsistem'];
		}


		#produce cpo sampe dengan hari ini
		$tbsolahtanggal=array();
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
		and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsolahtanggal[$bar['tanggal']]=$bar['tbsdiolah'];
				@$tbsolahbi+=$bar['tbsdiolah'];
				@$cpoprodbi+=$bar['oer'];
				@$kerprodbi+=$bar['oerpk'];
				@$ffacpobi+=$bar['ffa'];
				$databaris+=1;
				@$ffaxcpo+=($bar['ffa']*$bar['oer']);

				$cpoprodx[$bar['tanggal']]+=$bar['oer'];
				$ffacpox[$bar['tanggal']]+=$bar['ffa'];

			}
		}

		// FFA TODATE = ((FFA TODATE KEMAREN X CPO PROD. TODATE KEMAREN) + (FFA TODAY X CPO PROD. TODAY) / PROD. CPO TODATE HARI INI)
		function nonan($angka){
			if(is_nan($angka))$angka=0;
			if(is_infinite($angka))$angka=0;

			return $angka;
		}		
		for ($x = 1; $x <= substr($tanggal,8,2); $x++) { // 2021-07-30 
			$xx=sprintf("%02d", $x);
			$tglx=substr($tanggal,0,7).'-'.$xx;

			$tglxmin1=date('Y-m-d', strtotime($tglx." -1 day"));
			$cpoprodtdy+=$cpoprodx[$tglx];
			$cpoprodtdx[$tglx]=$cpoprodtdy;
			$ffacpotdx[$tglx]=round(nonan((($ffacpotdx[$tglxmin1]*$cpoprodtdx[$tglxmin1])+($ffacpox[$tglx]*$cpoprodx[$tglx]))/$cpoprodtdx[$tglx]),2);

			// echo "</br>".$tglx;
			// echo "</br>".$tglx."ffacpomin1x:".$ffacpox[$tglxmin1]; // ffa cpo yesterday
			// echo "</br>".$tglx."cpoprodtdx:".$cpoprodtdx[$tglxmin1]; // cpo prod todate yesterday
			// echo "</br>".$tglx."ffacpox:".$ffacpox[$tglx]; // ffa cpo today
			// echo "</br>".$tglx."cpoprodx:".$cpoprodx[$tglx]; // cpo prod today
			// echo "</br>".$tglx."cpoprodtdx:".$cpoprodtdx[$tglx]; // cpo prod todate
			// echo "</br>".$tglx."ffacpotdx:".$ffacpotdx[$tglx]; // cpo prod todate
			$jujul[$tglx].=$ffacpotdx[$tglx]."=((".$ffacpotdx[$tglxmin1]."x".$cpoprodtdx[$tglxmin1].")+(".$ffacpox[$tglx]."x".$cpoprodx[$tglx]."))/".$cpoprodtdx[$tglx];
		} 		

		#produce cpo hari ini bulan lalu
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglblnkemarin."' and '".$tglblnkemarin."' 
		and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			// @$hariolah+=1;
			if($bar['tanggal']==$tanggal){
				@$tbsawalhi=$bar['sisatbskemarin'];
				@$tbsolahhi=$bar['tbsdiolah'];
				@$tbssisahi=$bar['sisahariini'];
				@$cpoprodhilalu=$bar['oer'];
				@$kerprodhilalu=$bar['oerpk'];
				@$ffacpohilalu=$bar['ffa'];
				@$bkpkhi=$bar['ffapk'];
				@$vmcpohi=$bar['kadarair'];
				@$vmpkhi=$bar['kadarairpk'];
				@$dirtpohi=$bar['kadarkotoran'];
				@$dirtpkhi=$bar['kadarkotoranpk'];
				@$dobicpohi=$bar['kadarkotoran'];
			
			}
			
			if($bar['tanggal']==$tglkemarin){
				@$cposawalhi=$bar['oer'];
				@$kersawalhi=$bar['oerpk'];
			}
			
		}
		#produce cpo sampe dengan hari ini bulan lalu
		$tbsolahtanggal=array();
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglawalblnhilalu."' and '".$tglblnkemarin."' 
		and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsolahtanggal[$bar['tanggal']]=$bar['tbsdiolah'];
				@$tbsolahbi+=$bar['tbsdiolah'];
				@$cpoprodbilalu+=$bar['oer'];
				@$kerprodbilalu+=$bar['oerpk'];
				@$ffacpobilalu+=$bar['ffa'];
				$databarislalu+=1;
			}
		}

	
		#tbsolah
		$str="select * from ".$dbname.".pabrik_pengolahan where tanggal between '".$tglawalthn."' and '".$tanggal."' 
			and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$lori[$bar['shift']]+=$bar['jumlahlori'];

		}


		$str="select * from ".$dbname.".pabrik_5konversilori where unit='".$pabrik."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$lorikonversi=$bar['kg'];
		}

		$arrhmpbbi=array();
		$str="select * from ".$dbname.".pabrik_pengolahan where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
			and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				@$hmpbhi+=$bar['jamdinasbruto'];
			}
				@$hmpbbi+=$bar['jamdinasbruto'];

		}

		$str="select * from ".$dbname.".pabrik_pengolahan where tanggal between '".$tglawalthn."' and '".$tanggal."' 
			and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
				@$hmpbti+=$bar['jamdinasbruto'];
				$arrhmpbbi[$bar['tanggal']]=$bar['jamdinasbruto'];
		}


		@$ttbshi=$tbsterimabrutohi+$tbsawalhi;
		@$kglorihi=@round($ttbshi/$tlorihi);
		
		@$kglorirestanhi=$lorirestanhi*$kglorihi;
		@$kglorisebelumhi=$lorisebelumhi*$kglorihi;
		@$kglorididalmhi=$lorididalmhi*$kglorihi;
		@$kglorisesudahhi=$lorisesudahhi*$kglorihi;
		@$tkgrestan=$tlorirestan*$kglorihi;
	
		
		
		#= ambil daftar Mesin Press yg masuk LHP
		$arrmsp=array();
		$arrmspx=array();
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PK' and	kodeparameter='LHPMSP'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrmspx=explode(',',$bar['nilai']);
			foreach($arrmspx as $key){
				$arrmsp[$key]=$key;
			}
	
		#= ambil daftar tangki yg masuk LHP
		#=cpo
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PK' and	kodeparameter='LHPSTOKCPO'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrtk=explode(',',$bar['nilai']);
			foreach($arrtk as $key){
				$arrtkcpo[$key]=$key;
			}
		#= pk
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PK' and	kodeparameter='LHPSTOKKER'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrtk=explode(',',$bar['nilai']);
			foreach($arrtk as $key){
				$arrtkker[$key]=$key;
			}
		

		$mspress = array();
		#= CPO STOK
		$str="select * from ".$dbname.".pabrik_hmmesin_vw where 
				tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
				and mesin in ('".implode("','",@$arrmsp)."')";
		//echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				

				$mspress[$bar['mesin']]['hmmesin']['hi']=$bar['hmmesin'];
				if($bar['hmmesin']!=0){
					$mspress[$bar['mesin']]['kapasitas']['hi']=$tbsolahhi;
				
				}
					
			}
			if($bar['hmmesin']!=0){

					$mspress[$bar['mesin']]['kapasitas']['bi']+=$tbsolahtanggal[$bar['tanggal']];
				
			}

					$mspress[$bar['mesin']]['hmmesin']['bi']+=$bar['hmmesin'];
			
		
		}	

		#= pengiriman
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalthn."' and '".$tanggal."' 
		and millcode='".$pabrik."' and kodebarang!='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				@$barangkirimhi[$bar['kodebarang']]+=$bar['beratbersih'];
			}
		
			@$barangkirimti[$bar['kodebarang']]+=$bar['beratbersih'];
		}

		#= pengiriman
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
		and millcode='".$pabrik."' and kodebarang!='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$barangkirimbi[$bar['kodebarang']]+=$bar['beratbersih'];
			}
			
		}
		
		// $str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
		// and millcode='".$pabrik."' and kodebarang!='40000003'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			
			// if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				// @$barangkirimbi[$bar['kodebarang']]+=$bar['beratbersih'];
			// }
			
		// }
		
		$theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }          
        $result="";
		
		if($tipe!='excel'){
			$result.="<link rel=stylesheet type=text/css href=style/".$gen.">";
		}


		$arraysort = array('F00' => 'mentah','F0' => 'mengkal','F1-F4' => 'terima', 'F5' => 'restan', 'F6' => 'busuk', 'LS' => 'panjang', 'BR' => 'mutu' );
			
			
		$result.="
		<table class=sortable cellspacing=1 cellpadding=5>
			<tr class=rowcontent>
				<td colspan=2><b>P.T.</b></td>
				<td><b>:</b></td>
				<td><b>".$nmorg[$kdpt[$pabrik]]."</b></td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2><b>PMKS</b></td>
				<td><b>:</td>
				<td><b>".$nmorg[$pabrik]." ( Tanggal : ".tanggalnormal($tanggal).")</b></td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2><b>Periode</b></td>
				<td><b>:</td>
				<td><b>".$blnhi."</b></td>
			</tr>
			</table>
		";
		
		// $result.="<br>";	
		
		$result.="
		<table class=sortable cellspacing=1 cellpadding=5>
		<thead>
		<tr class=rowheader>
			<th rowspan=2><b>No</b></th>
			<th rowspan=2></th>
			<th colspan=4 rowspan=2><b>FFB PROSES & PRODUKSI</b></th>
			<th colspan=7 align=center><b>QUALITY FFB GRADED</b></th>
			<th colspan=3 align=center><b>FRESHNESS FFB GRADED</b></th>
			<th colspan=2 align=center><b>TOTAL FFB RECEIVED</b></th>
			<th colspan=3 align=center><b>BUDGET</b></th>
			<th colspan=3 align=center><b>PERCENTAGE OF BUDGET</b></th>
			<th colspan=3 align=center><b>TOTAL TODAY FRESHNESS</b></th>
		</tr>";
		$result.="
		<tr class=rowheader>";
		foreach ($arraysort as $key => $valsort) {
			$result.="
			<th><b>".$key."</b></td>
			";
		}
		$result.="
			<th><b> <1-2 HARI </b></th>
			<th><b> 3 HARI </b></th>
			<th><b> >3 HARI </b></th>
			
			<th><b> TODAY </b></th>
			<th><b> TODATE </b></th>
			
			<th><b>TODAY</b></th>
			<th><b>TODATE</b></th>
			<th><b>MONTH</b></th>

			<th><b>TODAY</b></th>
			<th><b>TODATE</b></th>
			<th><b>MONTH</b></th>

			<th><b> <1-2 HARI </b></th>
			<th><b> 3 HARI </b></th>
			<th><b> >3 HARI </b></th>	
		</tr>
		</thead>";	



		#ffb graded plasma
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah <= '2' and kodebarang='40000003' and intiplasma='INTI' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
			
		while($bar=$res->fetch()){
			@$ffbgrade12[$bar['kodeorg']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah = '3' and kodebarang='40000003' and intiplasma='INTI' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		while($bar=$res->fetch()){
			@$ffbgrade3[$bar['kodeorg']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah > '3' and kodebarang='40000003' and intiplasma='INTI' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		while($bar=$res->fetch()){
			@$ffbgrade4[$bar['kodeorg']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}


		$result.="
		<tr class=rowcontent>
			<td align=center><b>A.</b></td>
			<td></td>
			<td colspan=25><b>FFB (Fresh Fruit Bunch)</b></td>
		</tr>";	
		$result.="<tr class=rowcontent>
		<td></td>
		<td></td>
		<td colspan=25>INTI</td>
		</tr>";
		$no=1;
		foreach ($kdorgi as $valkdorg) {
			$result.="
			<tr class=rowcontent>

			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>
			<td colspan=3>".$nmorg[$valkdorg]."</td>
			";
			
			foreach ($arraysort as $key => $valsort) {
				// $result.="<td>".($sortasi[$valkdorg][$valsort]/$rowdata[$valkdorg][$valsort])."</td>";
				$result.="<td></td>";
			}
			$result.="
			<td align=right>".hidezerodecimal(($ffbgrade12[$valkdorg]/$tbsterimaintinettohi[$valkdorg])*100)."</td>
			<td align=right>".hidezerodecimal(($ffbgrade3[$valkdorg]/$tbsterimaintinettohi[$valkdorg])*100)."</td>
			<td align=right>".hidezerodecimal(($ffbgrade4[$valkdorg]/$tbsterimaintinettohi[$valkdorg])*100)."</td>

			<td align=right>".hidezerodecimal($tbsterimaintinettohi[$valkdorg],2)."</td>
			<td align=right>".hidezerodecimal($tbsterimaintinettobi[$valkdorg],2)."</td>
			
			<td align=right>".hidezerodecimal($bgttbsihi[$valkdorg],0)."</td>
			<td align=right>".hidezerodecimal($bgttbsisdhi[$valkdorg],0)."</td>
			<td align=right>".hidezerodecimal($bgttbsibi[$valkdorg],0)."</td>
						
			<td align=right>".hidezerodecimal($tbsterimaintinettohi[$valkdorg]/$bgttbsihi[$valkdorg]*100,0)."</td>
			<td align=right>".hidezerodecimal($tbsterimaintinettobi[$valkdorg]/$bgttbsisdhi[$valkdorg]*100,0)."</td>
			<td align=right>".hidezerodecimal($tbsterimaintinettobi[$valkdorg]/$bgttbsibi[$valkdorg]*100,0)."</td>

			<td align=right>".hidezerodecimal($ffbgrade12[$valkdorg])."</td>
			<td align=right>".hidezerodecimal($ffbgrade3[$valkdorg])."</td>
			<td align=right>".hidezerodecimal($ffbgrade4[$valkdorg])."</td>

			</tr>";

			$totalhi+=$tbsterimaintinettohi[$valkdorg]+$tbsterimaplasmanettohi[$valkdorg];
			$totalbi+=$tbsterimaintinettobi[$valkdorg]+$tbsterimaplasmanettobi[$valkdorg];

		}


		#ffb graded plasma
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah <= '2' and kodebarang='40000003' and intiplasma='KUD' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
			
		while($bar=$res->fetch()){
			@$ffbgrade12p[$bar['kodeorg']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah = '3' and kodebarang='40000003' and intiplasma='KUD' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		while($bar=$res->fetch()){
			@$ffbgrade3p[$bar['kodeorg']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah > '3' and kodebarang='40000003' and intiplasma='KUD' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		while($bar=$res->fetch()){
			@$ffbgrade4p[$bar['kodeorg']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}


		$result.="<tr class=rowcontent>
		<td></td>
		<td></td>
		<td colspan=25>PLASMA</td>
		</tr>";
		$no=1;
		foreach ($kdorgp as $valkdorg) {
			$result.="
			<tr class=rowcontent>

			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>
			<td colspan=3>".$nmorg[$valkdorg]."</td>
			";
			
			foreach ($arraysort as $key => $valsort) {
				// $result.="<td>".($sortasi[$valkdorg][$valsort]/$rowdata[$valkdorg][$valsort])."</td>";
				$result.="<td></td>";
			}

			$result.="
			<td align=right>".hidezerodecimal(fixnan(($ffbgrade12p[$valkdorg]/$tbsterimaintinettohip[$valkdorg])*100))."</td>
			<td align=right>".hidezerodecimal(fixnan(($ffbgrade3p[$valkdorg]/$tbsterimaintinettohip[$valkdorg])*100))."</td>
			<td align=right>".hidezerodecimal(fixnan(($ffbgrade4p[$valkdorg]/$tbsterimaintinettohip[$valkdorg])*100))."</td>
			
			<td align=right>".hidezerodecimal($tbsterimaintinettohip[$valkdorg],2)."</td>
			<td align=right>".hidezerodecimal($tbsterimaintinettobip[$valkdorg],2)."</td>

			<td align=right>".hidezerodecimal($bgttbsphi[$valkdorg],0)."</td>
			<td align=right>".hidezerodecimal($bgttbspsdhi[$valkdorg],0)."</td>
			<td align=right>".hidezerodecimal($bgttbspbi[$valkdorg],0)."</td>

			<td align=right>".hidezerodecimal($tbsterimaintinettohip[$valkdorg]/$bgttbsphi[$valkdorg]*100,0)."</td>
			<td align=right>".hidezerodecimal($tbsterimaintinettobip[$valkdorg]/$bgttbspsdhi[$valkdorg]*100,0)."</td>
			<td align=right>".hidezerodecimal($tbsterimaintinettobip[$valkdorg]/$bgttbspbi[$valkdorg]*100,0)."</td>

			<td align=right>".hidezerodecimal($ffbgrade12p[$valkdorg])."</td>
			<td align=right>".hidezerodecimal($ffbgrade3p[$valkdorg])."</td>
			<td align=right>".hidezerodecimal($ffbgrade4p[$valkdorg])."</td>

			</tr>";

			$totalhi+=$tbsterimaintinettohip[$valkdorg]+$tbsterimaplasmanettohip[$valkdorg];
			$totalbi+=$tbsterimaintinettobip[$valkdorg]+$tbsterimaplasmanettobip[$valkdorg];

		}	
		
		#ffb graded eksternal
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah <= '2' and kodebarang='40000003' and intex='0' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
			
		while($bar=$res->fetch()){
			$arrCust[$bar['kodecustomer']]=$bar['kodecustomer'];
			@$ffbgrade12p[$bar['kodecustomer']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah = '3' and kodebarang='40000003' and intex='0' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		while($bar=$res->fetch()){
			$arrCust[$bar['kodecustomer']]=$bar['kodecustomer'];
			@$ffbgrade3p[$bar['kodecustomer']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and umurbuah > '3' and kodebarang='40000003' and intex='0' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		while($bar=$res->fetch()){
			$arrCust[$bar['kodecustomer']]=$bar['kodecustomer'];
			@$ffbgrade4p[$bar['kodecustomer']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}


		$result.="<tr class=rowcontent>
		<td></td>
		<td></td>
		<td colspan=25>SWADAYA</td>
		</tr>";
		$no=1;
		foreach ($arrCust as $valkdorg) {
			$nmorgcust=makeOption($dbname,"log_5suptimbangan_vw","kodetimbangan,namasupplier","kodetimbangan='".$valkdorg."'");
			$result.="
			<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";
			if($nmorgcust[$valkdorg]!=''){				
				$result.="<td colspan=3>".$nmorgcust[$valkdorg]."</td>";
			}else{
				$result.="<td colspan=3>TBS SWADAYA</td>";
			}
			
			foreach ($arraysort as $key => $valsort) {
				// $result.="<td>".($sortasi[$valkdorg][$valsort]/$rowdata[$valkdorg][$valsort])."</td>";
				$result.="<td></td>";
			}

			$result.="
			<td align=right>".hidezerodecimal(fixnan(($ffbgrade12p[$valkdorg]/$tbsterimaexternalnettohi[$valkdorg])*100))."</td>
			<td align=right>".hidezerodecimal(fixnan(($ffbgrade3p[$valkdorg]/$tbsterimaexternalnettohi[$valkdorg])*100))."</td>
			<td align=right>".hidezerodecimal(fixnan(($ffbgrade4p[$valkdorg]/$tbsterimaexternalnettohi[$valkdorg])*100))."</td>
			
			<td align=right>".hidezerodecimal($tbsterimaexternalnettohi[$valkdorg],2)."</td>
			<td align=right>".hidezerodecimal($tbsterimaexternalnettobi[$valkdorg],2)."</td>
			
			<td align=right>".hidezerodecimal($bgttbsexthi[$valkdorg],2)."</td>
			<td align=right>".hidezerodecimal($bgttbsextsdhi[$valkdorg],2)."</td>
			<td align=right>".hidezerodecimal($bgttbsextbi[$valkdorg],2)."</td>

			<td align=right>".hidezerodecimal($tbsterimaexternalnettohi[$valkdorg]/$bgttbsexthi[$valkdorg]*100,2)."</td>
			<td align=right>".hidezerodecimal($tbsterimaexternalnettobi[$valkdorg]/$bgttbsextsdhi[$valkdorg]*100,2)."</td>
			<td align=right>".hidezerodecimal($tbsterimaexternalnettobi[$valkdorg]/$bgttbsextbi[$valkdorg]*100,2)."</td>

			<td align=right>".hidezerodecimal($ffbgrade12p[$valkdorg])."</td>
			<td align=right>".hidezerodecimal($ffbgrade3p[$valkdorg])."</td>
			<td align=right>".hidezerodecimal($ffbgrade4p[$valkdorg])."</td>

			</tr>";

			$totalhi+=$tbsterimaexternalnettohi[$valkdorg];
			$totalbi+=$tbsterimaexternalnettobi[$valkdorg];
			
			$totalexthi+=$tbsterimaexternalnettohi[$valkdorg];
			$totalextbi+=$tbsterimaexternalnettobi[$valkdorg];
			$totalbgtexthi+=$bgttbsexthi[$valkdorg];
			$totalbgtextsdhi+=$bgttbsextsdhi[$valkdorg];
			$totalbgtextbi+=$bgttbsextbi[$valkdorg];
		}
		$result.="<tr class=rowcontent>";
		$result.="<td></td><td></td>
				<td colspan=14>SUB TOTAL SWADAYA</td>";
		$result.="
			<td align=right>".hidezerodecimal($totalexthi,2)."</td>
			<td align=right>".hidezerodecimal($totalextbi,2)."</td>
			
			<td align=right>".hidezerodecimal($totalbgtexthi,2)."</td>
			<td align=right>".hidezerodecimal($totalbgtextsdhi,2)."</td>
			<td align=right>".hidezerodecimal($totalbgtextbi,2)."</td>
			
			
			<td align=right>".hidezerodecimal($totalexthi/$totalbgtexthi*100,2)."</td>
			<td align=right>".hidezerodecimal($totalextbi/$totalbgtextsdhi*100,2)."</td>
			<td align=right>".hidezerodecimal($totalextbi/$totalbgtextbi*100,2)."</td>
			
			<td></td>
			<td></td>
			<td></td>
			
			";
	
		
		#############
		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=4>Grand Total - Ritase Terima</td>
			<td align=center colspan=10>".$rit." Rit</td>
			";
			$result.="
			<td>".hidezerodecimal($totalhi,2)."</td>
			<td>".hidezerodecimal($totalbi,2)."</td>
			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

		</tr>";	

		###############

		#ffb balance
		$str="select * from ".$dbname.".pabrik_produksi where tanggal='".$tanggal."' and kodeorg='".$pabrik."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
			
		while($bar=$res->fetch()){
			$ffbbalancehi+=$bar['sisatbskemarin'];
			$ffbproseshi+=$bar['tbsdiolah'];
		}

		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglawalblnhi."' and '".$tanggal."' and kodeorg='".$pabrik."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
			
		while($bar=$res->fetch()){
			$ffbprosesbi+=$bar['tbsdiolah'];
		}


		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=4>FFB Previous Day Balance</td>
			";
			foreach ($arraysort as $sortarray){
					$result.="
							<td></td>";
			}
			$result.="
			<td></td>
			<td></td>
			<td></td>
			
			
			<td align=right>".hidezerodecimal($ffbbalancehi)."</td>
			<td></td>
			
			
			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

		</tr>";	



		#########

		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=4>Total FFB To Process</td>
			";
			foreach ($arraysort as $sortarray){
					$result.="
							<td></td>";
			}
			$totaltoproses=$tbsawalhi+$totalhi;
			$result.="
			<td></td>
			<td></td>
			<td></td>
			
			
			<td align=right>".hidezerodecimal($totaltoproses)."</td>
			<td></td>
			
			
			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

		</tr>";	


		#####

		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=4>FFB Process Previous Month</td>
			";
			foreach ($arraysort as $sortarray){
					$result.="
							<td></td>";
			}
			$result.="
			<td></td>
			<td></td>
			<td></td>
			
			
			<td></td>
			<td></td>
			
			
			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

		</tr>";	

		#####

		#####

		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=4>FFB Process This Month</td>
			";
			foreach ($arraysort as $sortarray){
					$result.="
							<td></td>";
			}
			$result.="
			<td></td>
			<td></td>
			<td></td>
			
			
			<td align=right>".hidezerodecimal($ffbproseshi)."</td>
			<td align=right>".hidezerodecimal($ffbprosesbi)."</td>
	
			
			
			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

		</tr>";	

		#####

		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=4>Total FFB Balance L. Ramp + Lorry</td>
			";
			foreach ($arraysort as $sortarray){
					$result.="
							<td></td>";
			}
			$result.="
			<td></td>
			<td></td>
			<td></td>

			<td align=right>".hidezerodecimal($totaltoproses-$ffbproseshi)."</td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

		</tr>";	

		#####
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=4>FFB DI DALAM TRUCK (SORE)0 RIT X 8 MT</td>
			";
			foreach ($arraysort as $sortarray){
					$result.="
							<td></td>";
			}
			$result.="
			<td></td>
			<td></td>
			<td></td>
			
			
			<td></td>
			<td></td>
			
			
			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

		</tr>";	
		#####

		#####
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=4>GRAND TOTAL</td>
			";
			foreach ($arraysort as $sortarray){
					$result.="
							<td></td>";
			}
			$result.="
			<td></td>
			<td></td>
			<td></td>
			
			
			<td align=right>".hidezerodecimal($totaltoproses-$ffbproseshi)."</td>
			<td></td>
			
			
			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

			<td></td>
			<td></td>
			<td></td>

		</tr>";	
		#####
		
		
	
		##############################
		##
		##
		##############################

		$str="SELECT distinct(statasiun) as statasiun  FROM ".$dbname.".pabrik_rawatmesinht where 
				tanggal = '".$tanggal."' and pabrik='".$pabrik."' order by statasiun asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$liststasiun[$bar['statasiun']]=$bar['statasiun'];
		}

		$str="SELECT * FROM ".$dbname.".pabrik_rawatmesinht where tanggal = '".$tanggal."' 
				and pabrik='".$pabrik."' order by statasiun,mesin asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$listmesin[$bar['statasiun']][$bar['mesin']][$bar['notransaksi']] = $bar;
		}

		$str="select * from ".$dbname.".pabrik_rawatmesindt 
				where notransaksi in (SELECT notransaksi FROM ".$dbname.".pabrik_rawatmesinht where 
				tanggal = '".$tanggal."' 
				and pabrik='".$pabrik."') group by notransaksi,kodebarang";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$listbarang[$bar['kodebarang']]=$bar['kodebarang'];
			$barang[$bar['notransaksi']][]=$bar['kodebarang'];
			$satuanbarang[$bar['notransaksi']][]=$bar['satuan'];
			$jumlahbarang[$bar['notransaksi']][]=$bar['jumlah'];
		}

		if(is_array($listmesin)) {
			foreach ($listmesin as $stasiun=>$row) {
				foreach($row as $mesin=>$row2) {
					foreach($row2 as $notransaksi=>$list) {
						$listmesin[$stasiun][$mesin][$notransaksi]['barang'] =isset($barang[$notransaksi])?$barang[$notransaksi]:'';
					}
				}
			}
		} else {
			$listmesin='';
		}

		$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$nikKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
		$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

		$arrTipePerbaikan=array("prev"=>"Preventive Maintenance","kalibrasi"=>"Kalibrasi","project"=>"Project",
			"pabrikasi"=>"Pabrikasi","corrective"=>"Corrective Maintenance","service"=>"Service");
	
		$result.="
		<tr class=rowcontent>
			<td align=center rowspan=2><b>B.</b></td>
			<td rowspan=2></td>
			<td colspan=10 rowspan=2><b>CPO</b></td>
			<td colspan=2 align=center><b> TOTAL </b></td>

			<td colspan=13 rowspan=51 valign=top>
			<table class=sortable cellspacing=0 cellpadding=2 border=0 width=100%>
			<tr class=rowcontent>
			<td><b>Kendala Proses</b></td>
			</tr>";

		$result.="
			<tr class=rowcontent>
			<td>";

		$result.="<table class=sortable cellspacing=1 cellpadding=5>";
		$result.="<tr class=rowcontent>";
		$result.="<td align=center><b>No</td>";
		$result.="<td align=center style='width:70px;'><b>".$_SESSION['lang']['tanggal']."</td>";
		$result.="<td align=center><b>".$_SESSION['lang']['uraiankerusakan']."</td>";
		$result.="<td align=center><b>Spareparts Replaced</td>";
		$result.="<td align=center><b>Status</td>";
		$result.="</tr>";

		if(is_array($listmesin)){
			foreach ($listmesin as $stasiun=>$row){
				$result.="<tr class=rowcontent>";
				if(in_array($stasiun, $liststasiun)) {
					$result.="<td align=left colspan=2><b>STATION</b></td>";
					$result.="<td align=left colspan=3><b>".$stasiun." - ".$nmOrg[$stasiun]."</td>";
					$result.="</tr>"; 
					foreach($row as $mesin=>$row2) {
						$mesin=isset($mesin)?$mesin:'';
						$nmOrg[$mesin]=isset($nmOrg[$mesin])?$nmOrg[$mesin]:'';
						$result.="<tr class=rowcontent>";
						$result.="<td align=left colspan=2><b>MESIN</b></td>";
						$result.="<td align=left colspan=3><b>".$mesin." - ".$nmOrg[$mesin]."</b></td>";
						$result.="</tr>";
						$no=0;
						foreach($row2 as $notransaksi=>$list) {
							$no+=1;
							$i=0;
							$rowspan = count($list['barang']);
							$result.="<tr class=rowcontent>";
							$result.="<td rowspan='".$rowspan."' align=center>".$no."</td>";
							$result.="<td rowspan='".$rowspan."'>".tanggalnormal($list['tanggal'])."</td>";
							$result.="<td rowspan='".$rowspan."'>".$list['kegiatan']."</td>";
							
							if(empty($list['barang'])) {
								$result.="<td rowspan='".$rowspan."'></td>";
								$result.="<td rowspan='".$rowspan."'>".$list['statusketuntasan']."</td>";
							} else {
								foreach ($list['barang'] as $brg) {
									if($i>0) {
										$result.="<tr class=rowcontent>";
									}
									$result.="<td>".@$nmBrg[$brg]."</td>";
									$i++;
									if($i==1){
										$result.="<td rowspan='".$rowspan."'>".$list['statusketuntasan']."</td>";
									}
								}
							}
							$result.="</tr>";
						}
					}
				}
			}
		}
		



		$result.="</table>";

		$result.="</td></tr>
			</table>
			</td>
			
		</tr>
		<tr class=rowcontent>
		<td><b>TODAY</b></td>
		<td><b>TODATE</b></td>
		</tr>
		";	
	
		$no=0;	
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=9>Produced Previous Month</td>
			";
			$result.="
			<td align=right>".hidezerodecimal($cpoprodhilalu,2)."</td>
			<td align=right>".hidezerodecimal($cpoprodbilalu,2)."</td>
		</tr>";
	

		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=9>Produced This Month</td>
			";
			// $result.="
			// <td align=right>".hidezerodecimal(($onsistemcpo-$onsistemcpokemarin)+$cpoprodhi,2)."</td>
			// <td align=right>".hidezerodecimal($cpoprodbi+$onsistemcpo,2)."</td></tr>";
			$result.="
			<td align=right>".hidezerodecimal($cpoprodhi,2)."</td>
			<td align=right>".hidezerodecimal($cpoprodbi,2)."</td></tr>";

		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=9>FFA Quality Previous Month</td>
			";
			$result.="
			<td align=right>".hidezerodecimal(fixnan($ffacpohilalu,2))."</td>
			<td align=right>".hidezerodecimal(fixnan($ffacpobilalu/$databarislalu,2))."</td>
			
		</tr>";
	
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=9>FFA Quality This Month</td>
			";
			$result.="
			
			
			<td align=right>".hidezerodecimal($ffacpohi,2)."</td>
			<td title='".$jujul[$tanggal]."' align=right>".hidezerodecimal($ffacpotdx[$tanggal],2)."</td>
		</tr>";
			// <td align=right>".hidezerodecimal($ffacpobi/$databaris,2)."</td>


		#cpo despatch
		/*
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and kodebarang ='40000001' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
			
		while($bar=$res->fetch()){
			$despatchcpohi+=$bar['beratbersih'];
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."' and millcode='".$pabrik."' and kodebarang ='40000001' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
				
		while($bar=$res->fetch()){
			$despatchcpobi+=$bar['beratbersih'];
		}
		*/
		
		
		#= penjualan ambil dari BA
		$str="select *,substr(tanggal,1,10) as tanggaldata from ".$dbname.".pmn_bapengiriman_vw where substr(tanggal,1,10) between '".$tglawalblnhi."' and '".$tanggal."' and unit='".$pabrik."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['kodebarang']=='40000001'){
				if($tanggal==$bar['tanggaldata']){
					@$despatchcpohi+=$bar['jumlah'];
				}
				@$despatchcpobi+=$bar['jumlah'];
			}
			if($bar['kodebarang']=='40000002'){
				if($tanggal==$bar['tanggaldata']){
					@$despatchkerhi+=$bar['jumlah'];
					
				}
				@$despatchkerbi+=$bar['jumlah'];
			}
		}
		


		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=9>Despatch</td>
			";
			$result.="
			
			
			<td align=right>".hidezerodecimal($despatchcpohi,2)."</td>
			<td align=right>".hidezerodecimal($despatchcpobi,2)."</td>
		</tr>";

		###########################

		$result.="
		<tr class=rowcontent>
			<td align=center rowspan=2><b>C.</b></td>
			<td rowspan=2></td>
			<td colspan=10 rowspan=2><b>KERNEL</b></td>
			<td colspan=2 align=center><b>TOTAL</b></td>
		</tr>
		<tr class=rowcontent>
		<td><b>TODAY</b></td>
		<td><b>TODATE</b></td>
		</tr>
		";	
		$no=0;	
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=9>Produced Previous Month</td>
			";
			$result.="
			<td align=right>".hidezerodecimal($kerprodhilalu,2)."</td>
			<td align=right>".hidezerodecimal($kerprodbilalu,2)."</td>
		</tr>";
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=9>Produced This Month</td>
			";
			$result.="
			<td align=right>".hidezerodecimal($kerprodhi,2)."</td>
			<td align=right>".hidezerodecimal($kerprodbi,2)."</td>
		</tr>";


		#kernel despatch
		/*
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and kodebarang ='40000002' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
			
		while($bar=$res->fetch()){
			$despatchkerhi+=$bar['beratbersih'];
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."' and millcode='".$pabrik."' and kodebarang ='40000002' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
				
		while($bar=$res->fetch()){
			$despatchkerbi+=$bar['beratbersih'];
		}
		*/

		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=9>Despatch</td>
			";
			$result.="
			<td align=right>".hidezerodecimal($despatchkerhi,2)."</td>
			<td align=right>".hidezerodecimal($despatchkerbi,2)."</td>
		</tr>";



		###########################
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal='".$tanggal."' and millcode='".$pabrik."' and kodebarang ='40000004' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=1;		
		while($bar=$res->fetch()){
			$despatchefbhi+=$bar['beratbersih'];
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."' and millcode='".$pabrik."' and kodebarang ='40000004' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=1;		
		while($bar=$res->fetch()){
			$despatchefbbi+=$bar['beratbersih'];
		}

		$result.="
		<tr class=rowcontent>
			<td align=center rowspan=2><b>D.</b></td>
			<td rowspan=2rowspan=2></td>
			<td colspan=6 rowspan=2><b>EFB (TBK)</b></td>
		
		<td rowspan=2><b>TODAY</b></td>
		<td rowspan=2><b>TODATE</b></td>
		<td colspan=2><b>USB (%)</b></td>
		<td colspan=2><b>RAINFALL</b></td>
		</tr>

		<tr class=rowcontent>
		<td>TODAY</td>
		<td>TODATE</td>
		<td>TODAY</td>
		<td>TODATE</td>
		</tr>
		";	
		$no=0;	
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=6>Produced</td>
			";
			$result.="
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
		</tr>";
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=6>Despatch</td>
			";
			$result.="
			<td align=right>".hidezerodecimal($despatchefbhi,2)."</td>
			<td align=right>".hidezerodecimal($despatchefbbi,2)."</td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
		</tr>";

		##############################
		##
		##
		##############################
		
		$result.="
		<tr class=rowcontent>
			<td align=center><b>E.</b></td>
			<td></td>
			<td colspan=8><b>TBS OLAH</b></td>
			<td align=center><b>Shift Pagi</b></td>
			<td align=center><b>Shift Siang</b></td>
			<td align=center><b>Shift Malam</b></td>
			<td align=center><b>Total</b></td>
			
		</tr>";	
		
		$no=0;	
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=7>Lori</td>";

			
			@$total=$lori[1]+$lori[2]+$lori[3];

		$result.="
			<td align=right>".hidezerodecimal(@$lori[1])."</td>
			<td align=right>".hidezerodecimal(@$lori[2])."</td>
			<td align=right>".hidezerodecimal(@$lori[3])."</td>
			<td align=right>".hidezerodecimal(@$total)."</td>
			
		</tr>";
		
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=7>Tonase</td>";

			$result.="
			<td align=right>".hidezerodecimal(@$lori[1]*@$lorikonversi)."</td>
			<td align=right>".hidezerodecimal(@$lori[2]*@$lorikonversi)."</td>
			<td align=right>".hidezerodecimal(@$lori[3]*@$lorikonversi)."</td>
			<td align=right>".hidezerodecimal(@$total*@$lorikonversi)."</td>
			
		</tr>";

		$no=$no+1;


		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=7>Mill RH</td>

			<td></td>
			<td></td>
			<td></td>
			<td></td>
			
			
		</tr>";
		
		$no=$no+1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=7>Mill TPH</td>

			<td></td>
			<td></td>
			<td></td>
			<td></td>
			

		</tr>";

		$no=$no+1;
		$sftpg=$ffbproseshi/$lori[1];		
		$sftsg=$ffbproseshi/$lori[2];		
		$sftml=$ffbproseshi/$lori[3];

		if (is_infinite($sftpg)) {
			$sftpg=0;
		}

		if (is_infinite($sftsg)) {
			$sftsg=0;
		}

		if (is_infinite($sftml)) {
			$sftml=0;
		}

		$sfttot=$sftpg+$sftsg+$sftml;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no.".</td>
			<td colspan=7>B. Lori</td>

			<td align=right>".hidezerodecimal($sftpg)."</td>
			<td align=right>".hidezerodecimal($sftsg)."</td>
			<td align=right>".hidezerodecimal($sftml)."</td>
			<td align=right>".hidezerodecimal($sfttot)."</td>
			
			

		</tr>";


		######

		$result.="
		<tr class=rowcontent>
			<td align=center><b>F.</b></td>
			<td></td>
			<td colspan=12><b>CPO STOCK (TANK)</b></td>
		</tr>
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td></td>
			<td colspan=5><b>TANK</b></td>
			<td align=center><b>CAPACITY</b></td>
			<td align=center><b>TEMP</b></td>
			<td align=center><b>MT</b></td>
			<td align=center><b>FFA</b></td>
			<td align=center><b>MOIST</b></td>
			<td align=center><b>DIRT</b></td>
		</tr>
		";	
	

		#datacpotank
		$str="select * from ".$dbname.".pabrik_masukkeluartangki 
		where tanggal='".$tanggal."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='CPO') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=1;		
		while($bar=$res->fetch()){

		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";

			$result.="
			<td colspan=5>".$arrnmtangki[$bar['kodetangki']]."</td>
			<td align=right>".hidezerodecimal($arrkapstangki[$bar['kodetangki']])."</td>
			<td align=right>".hidezerodecimal($bar['suhu'])."</td>
			<td align=right>".hidezerodecimal($bar['kuantitas'])."</td>
			<td align=right>".hidezerodecimal($bar['cpoffa'])."</td>
			<td align=right>".hidezerodecimal($bar['cpokdair'])."</td>
			<td align=right>".hidezerodecimal($bar['cpokdkot'])."</td>
			";
			$result.="
		</tr>
		";
		}

		#cpoonsistem
		$str="select cpoonsistem from ".$dbname.".pabrik_produksi 
		where tanggal='".$tanggal."' and kodeorg='".$pabrik."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);		
		$bar=$res->fetch();

		if ($bar['cpoonsistem'] !=0) {
			$result.="
			<tr class=rowcontent>
			<td></td>
			<td></td>
			<td></td>
			<td colspan=5>CPO On Sistem</td>
			<td colspan=2></td>
			<td align=right>".hidezerodecimal($bar['cpoonsistem'])."</td>
			<td></td>
			<td></td>
			<td></td>
			</tr>";
		}

		###########

		$result.="
		<tr class=rowcontent>
			<td align=center><b>H.</b></td>
			<td></td>
			<td colspan=12><b>PK STOCK</b></td>
		</tr>
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td></td>
			<td colspan=7></td>
			
			<td align=center><b>BAG</b></td>
			<td align=center><b>MT</b></td>
			
			<td align=center><b>MOIST</b></td>
			<td align=center><b>DIRT</b></td>
			
		</tr>
		";	
	


		#datakerneltank
		$str="select * from ".$dbname.".pabrik_masukkeluartangki 
		where tanggal='".$tanggal."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='KER' and kodetangki like 'BK%') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=1;		
		while($bar=$res->fetch()){
			$kuantitasbk+=$bar['kernelquantity'];
			$kernelffabk+=$bar['kernelffa'];
			$kernelkdkotbk+=$bar['kernelkdkot'];
		}
			$result.="
			<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";
			$result.="
			<td colspan=7>STOCK IN KERNEL SILO</td>			
			<td> </td>
			<td align=right>".hidezerodecimal($kuantitasbk)."</td>
			<td align=right>".hidezerodecimal($kernelffabk)."</td>
			<td align=right>".hidezerodecimal($kernelkdkotbk)."</td>
			</tr>
			";

		$str="select * from ".$dbname.".pabrik_masukkeluartangki 
		where tanggal='".$tanggal."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='KER' and kodetangki like 'KS%') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);		
		while($bar=$res->fetch()){
			$kuantitasbs+=$bar['kernelquantity'];
			$kernelffabs+=$bar['kernelffa'];
			$kernelkdkotbs+=$bar['kernelkdkot'];

			$barisdata+=1;
		}
			$result.="
			<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";


			$result.="
			<td colspan=7>STOCK IN BULK SILO</td>

			<td></td>
			<td align=right>".hidezerodecimal($kuantitasbs)."</td>
			<td align=right>".hidezerodecimal(fixnan($kernelffabs/$barisdata,2))."</td>
			<td align=right>".hidezerodecimal(fixnan($kernelkdkotbs/$barisdata,2))."</td>

			</tr>
			";

		$str="select * from ".$dbname.".pabrik_masukkeluartangki 
		where tanggal='".$tanggal."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='KER' ) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);		
		while($bar=$res->fetch()){
			$kuantitas+=$bar['kuantitas'];
			$kernelffa+=$bar['kernelffa'];
			$kernelkdkot+=$bar['kernelkdkot'];
		}
			$result.="
			<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";


			$result.="
			<td colspan=7>STOCK IN STORE (EST)</td>

			<td></td>
			<td align=right>".hidezerodecimal($kuantitas)."</td>
			<td align=right>".hidezerodecimal($kernelffa)."</td>
			<td align=right>".hidezerodecimal($kernelkdkot)."</td>
			</tr>
			";


			$result.="
			<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";


			$result.="
			<td colspan=7>KERNEL ON FLOOR (EST)</td>

			<td> </td>
			<td> </td>
			<td> </td>
			<td> </td>

			</tr>
			";

			$result.="
			<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";

			$str="select * from ".$dbname.".pabrik_masukkeluartangki 
			where tanggal='".$tanggal."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='KER' and kodetangki like 'GK%') ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);		
			while($bar=$res->fetch()){
				$kuantitas+=$bar['kernelquantity'];
				$kernelffa+=$bar['kernelffa'];
				$kernelkdkot+=$bar['kernelkdkot'];
			}

			$result.="
			<td colspan=7>STORE KERNEL</td>

			<td> </td>
			<td align=right>".hidezerodecimal($kuantitas)."</td>
			<td align=right>".hidezerodecimal($kernelffa)."</td>
			<td align=right>".hidezerodecimal($kernelkdkot)."</td>
			</tr>
			";

			######


		$result.="
		<tr class=rowcontent>
			<td align=center><b>I.</b></td>
			<td></td>
			<td colspan=12><b>CANGKANG & FIBRE</b></td>
		</tr>
		<tr class=rowcontent>
			<td></td>
			<td></td>
		
			<td colspan=10></td>
			<td align=center><b>BAG</b></td>
			<td align=center><b>MT</b></td>
		</tr>
		";	
	
		#datacpotank
		// $str="select * from ".$dbname.".pabrik_stokbarang where tanggal='".$tanggal."' and kodebarang='' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		
		$no=1;		
		// while($bar=$res->fetch()){


		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";
			$result.="
			<td colspan=9>BULK / PABRIK (ESTIMASI)</td>
	
			<td> </td>
			<td> </td>
		</tr>
			";

		##tonbag
		$str="select * from ".$dbname.".pabrik_masukkeluartangki where tanggal = '".$tanggal."' and kodeorg ='".$pabrik."' and kodetangki like '%TB%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$TBMT=$bar['kernelquantity'];
		}

			$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";
			$result.="
			<td colspan=9>TON BAG (SIAP DESPATCH)</td>
		
			<td> </td>
			<td align=right>".hidezerodecimal($TBMT)."</td>
		</tr>
			";

		// }


		$no=1;

		$result.="
		<tr class=rowcontent>
			<td align=center><b>J.</b></td>
			<td></td>
			<td colspan=12><b>EFB STOCK (TBK)</b></td>
		</tr>
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td></td>
			<td colspan=10></td>
			<td align=center><b>MT</b></td>
		</tr>
		";	
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";
			$result.="
			<td colspan=10>Pabrik Estimasi</td>
			<td> </td>
			
		</tr>
			";



			$result.="
		<tr class=rowcontent>
			<td align=center><b>K.</b></td>
			<td></td>
			<td colspan=12><b>DATA</b></td>
		</tr>
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td colspan=10></td>
			<td><b>TODAY</b></td>
			<td><b>TODATE</b></td>
		</tr>
		";	
	
		#datacpotank
		$str="select * from ".$dbname.".pabrik_produksi where tanggal = '".$tanggal."' and kodeorg ='".$pabrik."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$tbsolahhi=$bar['tbsdiolah'];
			$cpoprodhi=$bar['oer'];
			$pkprodhi=$bar['oerpk'];
		}	
		@$cpooerhi=($cpoprodhi/$tbsolahhi)*100;
		@$pkoerhi=($pkprodhi/$tbsolahhi)*100;


		$tbsolahbi=0;
		$cpoprodbi=0;
		$pkprodbi=0;
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglawalblnhi."' and '".$tanggal."' and kodeorg ='".$pabrik."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$tbsolahbi+=$bar['tbsdiolah'];
			$cpoprodbi+=$bar['oer'];
			$pkprodbi+=$bar['oerpk'];
		}

		@$cpooerbi=($cpoprodbi/$tbsolahbi)*100;
		@$pkoerbi=($pkprodbi/$tbsolahbi)*100;
		
		$no=1;		
		$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";
			$result.="
			<td colspan=8>OER</td>
			<td> </td>
			<td align=right>".hidezerodecimal($cpooerhi,1)."</td>
			<td align=right>".hidezerodecimal($cpooerbi,1)."</td>
			
		</tr>
			";

			$result.="
		<tr class=rowcontent>
			<td></td>
			<td></td>
			<td align=center>".$no++.".</td>";
			$result.="
			<td colspan=8>KER</td>
			<td> </td>
			<td align=right>".hidezerodecimal($pkoerhi,1)."</td>
			<td align=right>".hidezerodecimal($pkoerbi,1)."</td>
		</tr>
			";

		



		$result.="</table>";	
		if($tipe=='preview'){
			echo $result;
		}else{
			$dte=date("YmdHis");
			$nop_="laporan_produksi_harian".$dte;
			if(strlen($result)>0){
				if ($handle = opendir('tempExcel')){
					while (false !== ($file = readdir($handle))){
						if ($file != "." && $file != ".."){
							@unlink('tempExcel/'.$file);
						}
					}
					closedir($handle);
				}
				
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$result)){
					echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				fclose($handle);
			}
		}
	break;
		
	case 'excel1':
			
		$tahun     = substr($tanggal,0,4);
		$tglawalthn= $tahun."-01-01";
		$bulan     = substr($tanggal,5,2);
		$tglskrg   = substr($tanggal,-2);
		
	
		$blnhi       =substr($tanggal,0,7);
		$tglawalblnhi=$blnhi."-01";  
	
	
		$tglbesok = strtotime('+1 day',strtotime($tanggal));
		$tglbesok = date('Y-m-d', $tglbesok);

		$tglkemarin = strtotime('-1 day',strtotime($tanggal));
		$tglkemarin = date('Y-m-d', $tglkemarin);


		$tglblnkemarin = strtotime('-1 month',strtotime($tanggal));
		$tglblnkemarin = date('Y-m-d', $tglblnkemarin);

		$blnlaluhi=substr($tglblnkemarin,0,7);
		$tglawalblnhilalu=$blnlaluhi."-01";
		$arrptca = array('CARE','LAPE','SMPE','TK1E','TK2E','TBBE','TMBE');
		$arrptkbl = array('KBLE');
		$arrptla = array('LANE','KKME');
		$arrptsab = array('S202507095');
		$arrkopkar = array('S202508039');
		############################################## BENTUK ARRAY ##############################################
		
		$arrsupp=array();
		#= external
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalthn."' and '".$tanggal."' 
		and millcode='".$pabrik."' and intex='0'  and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrsupp[$bar['kodesupplier']]=$bar['kodesupplier'];
			if($bar['tanggal']==$tanggal){
			
				@$tbsterimaexternalbrutohi+=$bar['beratbersih'];
				@$tbspotexternalhi[$bar['kodesupplier']]+=$bar['kgpotsortasi'];
				@$tbsterimaexternalnettohi[$bar['kodesupplier']]+=$bar['beratbersih'];
			}
			
			@$tbsterimaexternalbrutoti+=$bar['beratbersih'];
			@$tbspotexternalti[$bar['kodesupplier']]+=$bar['kgpotsortasi'];
			@$tbsterimaexternalnettoti[$bar['kodesupplier']]+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}

		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
		and millcode='".$pabrik."'  and intex='0' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				$arrsupp[$bar['kodesupplier']]=$bar['kodesupplier'];
				@$tbsterimaexternalbrutobi+=$bar['beratbersih'];
				@$tbspotexternalbi[$bar['kodesupplier']]+=$bar['kgpotsortasi'];
				@$tbsterimaexternalnettobi[$bar['kodesupplier']]+=$bar['beratbersih'];
			}
			$rit+=1;
		}
		
		#= bentuk Array TBS
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal like '".substr($tanggal,0,7)."%' and millcode='".$pabrik."' and kodebarang='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				if (in_array($bar['kodesupplier'], $arrptsab)) {
					$tbsterimabrutohi['SAB']+=$bar['beratbersih'];
					$tbspothi['SAB']+=$bar['kgpotsortasi'];
					$tbsterimanettohi['SAB']+=($bar['beratbersih']-$bar['kgpotsortasi']);
				}
				if (in_array($bar['kodeorg'], $arrptkbl)) {
					$tbsterimabrutohi['KBL']+=$bar['beratbersih'];
					$tbspothi['KBL']+=$bar['kgpotsortasi'];
					$tbsterimanettohi['KBL']+=($bar['beratbersih']-$bar['kgpotsortasi']);
				}
				if (in_array($bar['kodesupplier'], $arrkopkar)) {
					$tbsterimabrutohi['LA']+=$bar['beratbersih'];
					$tbspothi['LA']+=$bar['kgpotsortasi'];
					$tbsterimanettohi['LA']+=($bar['beratbersih']-$bar['kgpotsortasi']);
				}
				$tbsterimabrutohi['ALL']+=$bar['beratbersih'];
			}
			
			if($bar['tanggal']<=$tanggal){
				if (in_array($bar['kodesupplier'], $arrptsab)) {
					$tbsterimabrutobi['SAB']+=$bar['beratbersih'];
					$tbspotbi['SAB']+=$bar['kgpotsortasi'];
					$tbsterimanettobi['SAB']+=($bar['beratbersih']-$bar['kgpotsortasi']);
				}
				if (in_array($bar['kodeorg'], $arrptkbl)) {
					$tbsterimabrutobi['KBL']+=$bar['beratbersih'];
					$tbspotbi['KBL']+=$bar['kgpotsortasi'];
					$tbsterimanettobi['KBL']+=($bar['beratbersih']-$bar['kgpotsortasi']);
				}
				if (in_array($bar['kodeorg'], $arrkopkar)) {
					$tbsterimabrutobi['LA']+=$bar['beratbersih'];
					$tbspotbi['LA']+=$bar['kgpotsortasi'];
					$tbsterimanettobi['LA']+=($bar['beratbersih']-$bar['kgpotsortasi']);
				}
				$tbsterimabrutobi['ALL']+=$bar['beratbersih'];
			}

			$tbsterimabrutoti+=$bar['beratbersih'];
			$tbspotti+=$bar['kgpotsortasi'];
			$tbsterimanettoti+=($bar['beratbersih']-$bar['kgpotsortasi']);
		}
		//TBS CA
		$str="select * from ".$dbname.".kebun_spb_vw4 where tanggal like '".substr($tanggal,0,7)."%' and penerimatbs='".$pabrik."' and blok REGEXP '".implode("|",$arrptca)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				$tbsterimabrutohi['CA']+=$bar['kgwb'];
			}
			
			if($bar['tanggal']<=$tanggal){
				$tbsterimabrutobi['CA']+=$bar['kgwb'];
			}
		}
		//TBS LA
		$str="select * from ".$dbname.".kebun_spb_vw4 where tanggal like '".substr($tanggal,0,7)."%' and penerimatbs='".$pabrik."' and blok REGEXP '".implode("|",$arrptla)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				$tbsterimabrutohi['LA']+=$bar['kgwb'];
			}
			
			if($bar['tanggal']<=$tanggal){
				$tbsterimabrutobi['LA']+=$bar['kgwb'];
			}
		}
		##################################
		##################################
		##################################
		#produce cpo hari ini
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tanggal."' and '".$tanggal."' 
		and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			// @$hariolah+=1;
			if($bar['tanggal']==$tanggal){
				@$tbsawalhi=$bar['sisatbskemarin'];
				@$tbsolahhi=$bar['tbsdiolah'];
				@$tbssisahi=$bar['sisahariini'];
				@$cpoprodhi=$bar['oer'];
				@$kerprodhi=$bar['oerpk'];
				@$ffacpohi=$bar['ffa'];
				@$bkpkhi=$bar['ffapk'];
				@$vmcpohi=$bar['kadarair'];
				@$vmpkhi=$bar['kadarairpk'];
				@$dirtpohi=$bar['kadarkotoran'];
				@$dirtpkhi=$bar['kadarkotoranpk'];
				@$dobicpohi=$bar['kadarkotoran'];
				@$onsistemcpo=$bar['cpoonsistem'];
				@$loadinggudang=$bar['loadinggudang'];
			}
			
			if($bar['tanggal']==$tglkemarin){
				@$cposawalhi=$bar['oer'];
				@$kersawalhi=$bar['oerpk'];
			}
		}

		$str="select * from ".$dbname.".pabrik_produksi where tanggal = '".$tglkemarin."' and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$onsistemcpokemarin=$bar['cpoonsistem'];
		}

		#produce cpo sampe dengan hari ini
		$tbsolahtanggal=array();
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
		and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsolahtanggal[$bar['tanggal']]=$bar['tbsdiolah'];
				@$tbsolahbi+=$bar['tbsdiolah'];
				@$cpoprodbi+=$bar['oer'];
				@$kerprodbi+=$bar['oerpk'];
				@$ffacpobi+=$bar['ffa'];
				$databaris+=1;
				@$ffaxcpo+=($bar['ffa']*$bar['oer']);

				$cpoprodx[$bar['tanggal']]+=$bar['oer'];
				$ffacpox[$bar['tanggal']]+=$bar['ffa'];

			}
		}

		// FFA TODATE = ((FFA TODATE KEMAREN X CPO PROD. TODATE KEMAREN) + (FFA TODAY X CPO PROD. TODAY) / PROD. CPO TODATE HARI INI)
		function nonan($angka){
			if(is_nan($angka))$angka=0;
			if(is_infinite($angka))$angka=0;

			return $angka;
		}		
		for ($x = 1; $x <= substr($tanggal,8,2); $x++) { // 2021-07-30 
			$xx=sprintf("%02d", $x);
			$tglx=substr($tanggal,0,7).'-'.$xx;

			$tglxmin1=date('Y-m-d', strtotime($tglx." -1 day"));
			$cpoprodtdy+=$cpoprodx[$tglx];
			$cpoprodtdx[$tglx]=$cpoprodtdy;
			$ffacpotdx[$tglx]=round(nonan((($ffacpotdx[$tglxmin1]*$cpoprodtdx[$tglxmin1])+($ffacpox[$tglx]*$cpoprodx[$tglx]))/$cpoprodtdx[$tglx]),2);

			// echo "</br>".$tglx;
			// echo "</br>".$tglx."ffacpomin1x:".$ffacpox[$tglxmin1]; // ffa cpo yesterday
			// echo "</br>".$tglx."cpoprodtdx:".$cpoprodtdx[$tglxmin1]; // cpo prod todate yesterday
			// echo "</br>".$tglx."ffacpox:".$ffacpox[$tglx]; // ffa cpo today
			// echo "</br>".$tglx."cpoprodx:".$cpoprodx[$tglx]; // cpo prod today
			// echo "</br>".$tglx."cpoprodtdx:".$cpoprodtdx[$tglx]; // cpo prod todate
			// echo "</br>".$tglx."ffacpotdx:".$ffacpotdx[$tglx]; // cpo prod todate
			$jujul[$tglx].=$ffacpotdx[$tglx]."=((".$ffacpotdx[$tglxmin1]."x".$cpoprodtdx[$tglxmin1].")+(".$ffacpox[$tglx]."x".$cpoprodx[$tglx]."))/".$cpoprodtdx[$tglx];
		} 		

		#produce cpo hari ini bulan lalu
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglblnkemarin."' and '".$tglblnkemarin."' 
		and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			// @$hariolah+=1;
			if($bar['tanggal']==$tanggal){
				@$tbsawalhi=$bar['sisatbskemarin'];
				@$tbsolahhi=$bar['tbsdiolah'];
				@$tbssisahi=$bar['sisahariini'];
				@$cpoprodhilalu=$bar['oer'];
				@$kerprodhilalu=$bar['oerpk'];
				@$ffacpohilalu=$bar['ffa'];
				@$bkpkhi=$bar['ffapk'];
				@$vmcpohi=$bar['kadarair'];
				@$vmpkhi=$bar['kadarairpk'];
				@$dirtpohi=$bar['kadarkotoran'];
				@$dirtpkhi=$bar['kadarkotoranpk'];
				@$dobicpohi=$bar['kadarkotoran'];
			
			}
			
			if($bar['tanggal']==$tglkemarin){
				@$cposawalhi=$bar['oer'];
				@$kersawalhi=$bar['oerpk'];
			}
			
		}
		#produce cpo sampe dengan hari ini bulan lalu
		$tbsolahtanggal=array();
		$str="select * from ".$dbname.".pabrik_produksi where tanggal between '".$tglawalblnhilalu."' and '".$tglblnkemarin."' 
		and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if(substr($bar['tanggal'],0,7)==substr($tanggal,0,7)){
				@$tbsolahtanggal[$bar['tanggal']]=$bar['tbsdiolah'];
				@$tbsolahbi+=$bar['tbsdiolah'];
				@$cpoprodbilalu+=$bar['oer'];
				@$kerprodbilalu+=$bar['oerpk'];
				@$ffacpobilalu+=$bar['ffa'];
				$databarislalu+=1;
			}
		}
	
		#tbsolah
		$str="select * from ".$dbname.".pabrik_pengolahan where tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
			and kodeorg='".$pabrik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal'] == $tanggal){
				$jamolahhi+=$bar['jamdinasbruto'];
				$jamstaghi+=$bar['jamstagnasi'];
				$lorihi+=$bar['jumlahlori'];
			}
			$jamolahbi+=$bar['jamdinasbruto'];
			$jamstagbi+=$bar['jamstagnasi'];
			$loribi+=$bar['jumlahlori'];
		}

		@$ttbshi=$tbsterimabrutohi['ALL']+$tbsawalhi;
		@$kglorihi=@round($ttbshi/$tlorihi);
		
		@$kglorirestanhi=$lorirestanhi*$kglorihi;
		@$kglorisebelumhi=$lorisebelumhi*$kglorihi;
		@$kglorididalmhi=$lorididalmhi*$kglorihi;
		@$kglorisesudahhi=$lorisesudahhi*$kglorihi;
		@$tkgrestan=$tlorirestan*$kglorihi;
		
		#= ambil daftar Mesin Press yg masuk LHP
		$arrmsp=array();
		$arrmspx=array();
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PK' and	kodeparameter='LHPMSP'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrmspx=explode(',',$bar['nilai']);
			foreach($arrmspx as $key){
				$arrmsp[$key]=$key;
			}
	
		#= ambil daftar tangki yg masuk LHP
		#=cpo
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PK' and	kodeparameter='LHPSTOKCPO'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrtk=explode(',',$bar['nilai']);
			foreach($arrtk as $key){
				$arrtkcpo[$key]=$key;
			}
		#= pk
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PK' and	kodeparameter='LHPSTOKKER'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrtk=explode(',',$bar['nilai']);
			foreach($arrtk as $key){
				$arrtkker[$key]=$key;
			}
		

		$mspress = array();$mspress123=$mspress123kmrn=0;
		#= CPO STOK
		$str="select * from ".$dbname.".pabrik_hmmesin where 
				tanggal between '".$tglawalblnhi."' and '".$tanggal."' 
				and substation in ('".implode("','",@$arrmsp)."','CARM070001')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				$mspress123+=$bar['hourproses'];
				if($bar['substation'] == 'CARM070001'){
					$cbc+=$bar['hourproses'];
				}
				if($bar['hmmesin']!=0){
					$mspress[$bar['mesin']]['kapasitas']['hi']=$tbsolahhi;
				}
			}
			if($bar['tanggal']==$tglkemarin){
				$mspress123kmrn+=$bar['hourproses'];
			}
			if($bar['hmmesin']!=0){
				$mspress[$bar['mesin']]['kapasitas']['bi']+=$tbsolahtanggal[$bar['tanggal']];
			}
				$mspress[$bar['mesin']]['hmmesin']['bi']+=$bar['hmmesin'];
		}	

		#= pengiriman
		$str="select * from ".$dbname.".pabrik_timbangan_vw where tanggal between '".$tglawalblnhi."' and '".$tanggal."'  and millcode='".$pabrik."' and kodebarang!='40000003'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tanggal']==$tanggal){
				if($bar['wbcond']!='langsirgudang'){
					@$barangkirimhi[getinisialbrg($bar['kodebarang'])]+=$bar['beratbersih'];
				}
				@$suppliernya[getinisialbrg($bar['kodebarang'])]=getinisialcustomer($bar['kodecustomer']);
				@$nokontrak[getinisialbrg($bar['kodebarang'])]=$bar['nokontrak'];
				@$transportir[getinisialbrg($bar['kodebarang'])]=getNamaSupplier($bar['trpcode']);
				if($bar['wbcond']=='Return'){
					@$barangkirimhirjc[getinisialbrg($bar['kodebarang'])]+=$bar['beratbersih'];
				}
			}

			if($bar['tanggal'] <= $tanggal){
				if($bar['wbcond']!='langsirgudang'){
					@$barangkirimbi[getinisialbrg($bar['kodebarang'])]+=$bar['beratbersih'];
				}
			}
			@$barangkirimti[getinisialbrg($bar['kodebarang'])]+=$bar['beratbersih'];
		}

		#datacpotank
		$str="select * from ".$dbname.".pabrik_masukkeluartangki 
		where tanggal between '".$tglawalblnhi."' and '".$tanggal."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='CPO') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=1;		
		while($bar=$res->fetch()){
			if($bar['tanggal'] == $tanggal){
				$kuantitas[$bar['kodetangki']]=$bar['kuantitas'];
				$cpoffa[$bar['kodetangki']]=$bar['cpoffa'];
				$cpokdair[$bar['kodetangki']]=$bar['cpokdair'];
				$cpokdkot[$bar['kodetangki']]=$bar['cpokdkot'];
				$kuantitasall+=$bar['kuantitas'];
			}
			
			if($bar['tanggal']== $tglkemarin){
				$cpoffakmrn[$bar['kodetangki']]=$bar['cpoffa'];
				$cpokdairkmrn[$bar['kodetangki']]=$bar['cpokdair'];
				$cpokdkotkmrn[$bar['kodetangki']]=$bar['cpokdkot'];
			}
		}
		
		#datakertank
		$str="select * from ".$dbname.".pabrik_masukkeluartangki 
		where tanggal between '".$tglawalblnhi."' and '".$tanggal."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='KER') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=1;		
		while($bar=$res->fetch()){
			if($bar['tanggal'] == $tanggal){
				$kuantitasker=$bar['kernelquantity'];
				$kernelmoist+=$bar['kernelkdair'];
				$kernelffa+=$bar['kernelffa'];
				$kernelkdkot+=$bar['kernelkdkot'];
			}
			
			if($bar['tanggal']== $tglkemarin){
				$cpoffakmrn[$bar['kodetangki']]=$bar['cpoffa'];
				$cpokdairkmrn[$bar['kodetangki']]=$bar['cpokdair'];
				$cpokdkotkmrn[$bar['kodetangki']]=$bar['cpokdkot'];
			}
		}

		$str="select kuantitas from ".$dbname.".pabrik_masukkeluartangki 
		where tanggal ='".$tglkemarin."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='CPO') ";
		$kuantitasallkmrn=fetchdata($str)[0]['kuantitas'];

		$str="select kuantitas from ".$dbname.".pabrik_masukkeluartangki 
		where tanggal ='".$tglkemarin."' and kodeorg='".$pabrik."' and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi ='KER') ";
		$kuantitaskerallkmrn=fetchdata($str)[0]['kernelquantity'];

		$theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }          
        $result="";
		
		if($tipe!='excel'){
			$result.="<link rel=stylesheet type=text/css href=style/".$gen.">";
		}


		$arraysort = array('F00' => 'mentah','F0' => 'mengkal','F1-F4' => 'terima', 'F5' => 'restan', 'F6' => 'busuk', 'LS' => 'panjang', 'BR' => 'mutu' );
			
		$result.="
		<div align=center>
		<table class=sortable cellspacing=0 cellpadding=5>
			<tr class=rowcontent>
				<td colspan=10 align=center><b style='font-family:arial;font-size:24px'>LAPORAN HARIAN PRODUKSI PKS</b></td>
			</tr>
			<tr class=rowcontent>
				<td colspan=10 align=center><b style='font-family:arial;font-size:24px'>".$nmorg[$kdpt[$pabrik]]."</b></td></b></td>
			</tr>
			<tr class=rowcontent>
				<td colspan=10 align=center style='font-family:arial;font-size:16px'>".tglnmbln($tanggal,'I','long')."</td>
			</tr>
		</table>";
		///template DMA
		$result.= "
		<table class=sortable cellpadding=5 cellspacing=1 style='font-family:Century Gothic;font-size:12px'>
			<tr class=rowcontent>
				<td class='section-header' style='width:10px'><b>I.</b></td>
				<td class='section-header' colspan=3><b>TBS</b></td>
				<td class='section-header' style='font-family:Arial' colspan=2 align=center>S.d. Kemarin</td>
				<td class='section-header' style='font-family:Arial' colspan=2 align=center>Hari Ini</td>
				<td class='section-header' style='font-family:Arial' colspan=2 align=center>S.d. Hari Ini</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center style='width:10px'>1.</td>
				<td colspan=2>Sisa / Stock Awal TBS</td>
				<td align=right></td>
				<td align=center style='width:2px'></td>
				<td align=right><b>".hidezerodecimal($tbsawalhi,2)."</b></td>
				<td align=center>Kg</td>
				<td align=right></td>
				<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center>2.</td>
				<td colspan=2>TBS Masuk</td>
				<td align=right></td>
				<td align=center></td>
				<td align=right></td>
				<td align=center></td>
				<td align=right></td>
				<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td></td>
				<td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TBS CA</td>
				<td align=right>".number_format($tbsterimabrutobi['CA']-$tbsterimabrutohi['CA'],2)."</td>
				<td align=center>Kg</td>
				<td align=right>".number_format($tbsterimabrutohi['CA'],2)."</td>
				<td align=center>Kg</td>
				<td align=right>".number_format($tbsterimabrutobi['CA'],2)."</td>
				<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td></td>
				<td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TBS LA</td>
				<td align=right>".number_format($tbsterimabrutobi['LA']-$tbsterimabrutohi['LA'],2)."</td>
				<td align=center>Kg</td>
				<td align=right>".number_format($tbsterimabrutohi['LA'],2)."</td>
				<td align=center>Kg</td>
				<td align=right>".number_format($tbsterimabrutobi['LA'],2)."</td>
				<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td></td>
				<td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TBS KBL</td>
				<td align=right>".number_format($tbsterimabrutobi['KBL']-$tbsterimabrutohi['KBL'],2)."</td>
				<td align=center>Kg</td>
				<td align=right>".number_format($tbsterimabrutohi['KBL'],2)."</td>
				<td align=center>Kg</td>
				<td align=right>".number_format($tbsterimabrutobi['KBL'],2)."</td>
				<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td></td>
				<td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TBS SAB</td>
				<td align=right>".number_format($tbsterimabrutobi['SAB']-$tbsterimabrutohi['SAB'],2)."</td>
				<td align=center>Kg</td>
				<td align=right>".number_format($tbsterimabrutohi['SAB'],2)."</td>
				<td align=center>Kg</td>
				<td align=right>".number_format($tbsterimabrutobi['SAB'],2)."</td>
				<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center>3.</td>
				<td colspan=2>TBS Diterima</td>
				<td align=right><b>".number_format($tbsterimabrutobi['ALL']-$tbsterimabrutohi['ALL'],2)."</b></td>
				<td align=center>Kg</td>
				<td align=right><b>".number_format($tbsmasuk,2)."</b></td>
				<td align=center>Kg</td>
				<td align=right><b>".number_format($tbsterimabrutobi['ALL'],2)."</b></td>
				<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center>4.</td>
				<td colspan=2>TBS Kembali / Potongan Grading</td>
				<td align=right>-</td>
				<td align=center>Kg</td>
				<td align=right>-</td>
				<td align=center>Kg</td>
				<td align=right>-</td>
				<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center>5.</td>
				<td colspan=2>TBS Tersedia</td>
				<td align=right></td>
				<td align=center>Kg</td>
				<td align=right><b>".hidezerodecimal($tbsmasuk+$tbsawalhi,2)."</b></td>
				<td align=center>Kg</td>
				<td align=right></td>
				<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center>6.</td>
				<td colspan=2>TBS Diolah</td>
				<td align=right></td>
				<td align=center>Kg</td>
				<td align=right><b>".hidezerodecimal($tbsolahhi,2)."</b></td>
				<td align=center>Kg</td>
				<td align=right></td>
				<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td align=right></td>
				<td align=center>7.</td>
				<td colspan=2>Sisa / Stock Akhir TBS</td>
				<td align=right></td>
				<td align=center>Kg</td>
				<td align=right><b>".hidezerodecimal(($tbsmasuk+$tbsawalhi)-$tbsolahhi,2)."</b></td>
				<td align=center>Kg</td>
				<td align=right></td>
				<td align=center>Kg</td>
			</tr>
		 	<tr class=rowcontent>
		 		<td colspan=10>&nbsp;</td>
		 	</tr>
		 	<tr class=rowcontent>
		 		<td class='section-header' style='width:20px'><b>II.</b></td>
		 		<td class='section-header' colspan=3><b>HASIL OLAH</b></td>
		 		<td class='section-header' style='font-family:Arial' colspan=2 align=center>S.d. Kemarin</td>
		 		<td class='section-header' style='font-family:Arial' colspan=2 align=center>Hari Ini</td>
		 		<td class='section-header' style='font-family:Arial' colspan=2 align=center>S.d. Hari Ini</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>8.</td>
		 		<td colspan=2>CPO</td>
		 		<td align=right><b>".hidezerodecimal($cpoprodbi-$cpoprodhi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".hidezerodecimal($cpoprodhi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".hidezerodecimal($cpoprodbi)."</b></td>
		 		<td align=center>Kg</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>9.</td>
		 		<td colspan=2>TBS Diolah</td>
		 		<td align=right><b>".hidezerodecimal($tbsolahbi-$tbsolahhi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".hidezerodecimal($tbsolahhi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".hidezerodecimal($tbsolahbi)."</b></td>
		 		<td align=center>Kg</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>10.</td>
		 		<td colspan=2>Rendemen CPO</td>
		 		<td align=right><b>".number_format(@fixnan(hidezerodecimal(($cpoprodbi-$cpoprodhi)/($tbsolahbi-$tbsolahhi)*100),2) == 'nan'?0:@fixnan(($cpoprodbi-$cpoprodhi)/($tbsolahbi-$tbsolahhi)*100),2)."</b></td>
				<td align=center>%</td>
		 		<td align=right><b>".number_format(($cpoprodhi/$tbsolahhi)*100,2)."</b></td>
				<td align=center>%</td>
		 		<td align=right><b>".number_format(($cpoprodbi/$tbsolahbi)*100,2)."</b></td>
		 		<td align=center>%</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>11</td>
		 		<td colspan=2>Kernel</td>
		 		<td align=right><b>".hidezerodecimal($kerprodbi-$kerprodhi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".hidezerodecimal($kerprodhi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".hidezerodecimal($kerprodbi)."</b></td>
		 		<td align=center>Kg</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>12.</td>
		 		<td colspan=2>Rendemen Kernel</td>
		 		<td align=right><b>".number_format(@fixnan(hidezerodecimal(($kerprodbi-$kerprodhi)/($tbsolahbi-$tbsolahhi)*100),2)=='nan'?0:@fixnan(($kerprodbi-$kerprodhi)/($tbsolahbi-$tbsolahhi)*100),2)."</b></td>
				<td align=center>%</td>
		 		<td align=right><b>".number_format(($kerprodhi/$tbsolahhi)*100,2)."</b></td>
				<td align=center>%</td>
		 		<td align=right><b>".hidezerodecimal(($kerprodbi/$tbsolahbi)*100,2)."</b></td>
		 		<td align=center>%</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>13.</td>
		 		<td colspan=2>Jam Olah</td>
		 		<td align=right>".hidezerodecimal(($jamolahbi-$jamolahhi),2)."</td>
				<td align=center>Jam</td>
		 		<td align=right>".hidezerodecimal($jamolahhi,2)."</td>
				<td align=center>Jam</td>
		 		<td align=right>".hidezerodecimal($jamolahbi,2)."</td>
		 		<td align=center>Jam</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>14.</td>
		 		<td colspan=2>Stagnasi</td>
		 		<td align=right>".hidezerodecimal(($jamolahbi-$jamolahhi)-($mspress123kmrn/2),2)."</td>
				<td align=center>Jam</td>
		 		<td align=right>".hidezerodecimal($jamolahhi - ($mspress123/2),2)."</td>
				<td align=center>Jam</td>
		 		<td align=right>".hidezerodecimal(($jamolahbi - ($mspress123/2)) + ($mspress123kmrn/2),2)."</td>
		 		<td align=center>Jam</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>15.</td>
		 		<td colspan=2>Jam Efektif</td>
		 		<td align=right>".hidezerodecimal(($mspress123kmrn/2),2)."</td>
				<td align=center>Jam</td>
		 		<td align=right>".hidezerodecimal(($mspress123/2),2)."</td>
				<td align=center>Jam</td>
		 		<td align=right>".hidezerodecimal(($mspress123kmrn/2) + ($mspress123/2),2)."</td>
		 		<td align=center>Jam</td>
		 	</tr>
		 	<tr class=rowcontent>
				<td></td>
		 		<td align=center>16.</td>
		 		<td colspan=2>Kapasitas Pabrik</td>
		 		<td align=right></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".@fixnan($tbsolahhi/($mspress123/2),2)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".@fixnan($tbsolahbi/hidezerodecimal(($mspress123kmrn/2) + ($mspress123/2),2))."</b></td>
		 		<td align=center>Kg</td>
		 	</tr>
		 	<tr class=rowcontent>
			 	<td></td>
		 		<td align=center>17.</td>
		 		<td >Berat Rata-Rata</td>
		 		<td align=right>".hidezerodecimal($lorihi)." &nbsp;&nbsp;Set</td>
		 		<td align=left></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".hidezerodecimal(($tbsmasuk+$tbsawalhi)/$lorihi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right></td>
				<td align=center>Kg</td>
		 	</tr>
		 	<tr class=rowcontent>
			 	<td></td>
		 		<td align=center>18.</td>
		 		<td colspan=2>FFA Produksi</td>
		 		<td align=right></td>
				<td align=center>%</td>
		 		<td align=right>".$ffacpohi."</td>
				<td align=center>%</td>
		 		<td align=right></td>
				<td align=center>%</td>
		 	</tr>
		 	<tr class=rowcontent>
		 		<td colspan=10>&nbsp;</td>
		 	</tr>
			<tr class=rowcontent>
				<td class='section-header' style='width:20px'><b>III.</b></td>
				<td class='section-header' colspan=9><b>PRODUKSI CPO</b></td>
			</tr>";
			$noprod = 18;$nox=0;
			foreach ($arrnmtangkicpo as $nmtngki) {
				$noprod ++;
				$result.="
				<tr class=rowcontent>
					<td></td>
					<td colspan=2>".$nmtngki."</td>
					<td><u>Norma</u></td>
					<td class='section-header' style='font-family:Arial' colspan=2 align=center>S.d. Kemarin</td>
					<td class='section-header' style='font-family:Arial' colspan=2 align=center>Hari Ini</td>
					<td class='section-header' style='font-family:Arial' colspan=2 align=center>S.d. Hari Ini</td>
				</tr>
				<tr class=rowcontent>
					<td></td>
					<td align=center>".$noprod."</td>
					<td>% FFA</td>
					<td align=right>3.50 %</td>
					<td align=right>".$cpoffakmrn[array_keys($arrnmtangkicpo)[$nox]]."</td>
					<td align=center>%</td>
					<td align=right>".$cpoffa[array_keys($arrnmtangkicpo)[$nox]]."</td>
					<td align=center>%</td>
					<td align=right>".$cpoffa[array_keys($arrnmtangkicpo)[$nox]]."</td>
					<td align=center>%</td>
				</tr>";
				$noprod ++;
				$result.="
				<tr class=rowcontent>
					<td></td>
					<td align=center>".$noprod."</td>
					<td>% DIRT</td>
					<td align=right>0.01 %</td>
					<td align=right>".$cpokdkotkmrn[array_keys($arrnmtangkicpo)[$nox]]."</td>
					<td align=center>%</td>
					<td align=right>".$cpokdkot[array_keys($arrnmtangkicpo)[$nox]]."</td>
					<td align=center>%</td>
					<td align=right>".$cpokdkot[array_keys($arrnmtangkicpo)[$nox]]."</td>
					<td align=center>%</td>
				</tr>";
				$noprod ++;
				$result.="
				<tr class=rowcontent>
					<td></td>
					<td align=center>".$noprod."</td>
					<td>% MOISTURE</td>
					<td align=right>0.10 %</td>
					<td align=right>".$cpokdairkmrn[array_keys($arrnmtangkicpo)[$nox]]."</td>
					<td align=center>%</td>
					<td align=right>".number_format($cpokdair[array_keys($arrnmtangkicpo)[$nox]],2)."</td>
					<td align=center>%</td>
					<td align=right>".number_format($cpokdair[array_keys($arrnmtangkicpo)[$nox]],2)."</td>
					<td align=center>%</td>
				</tr>";
				$noprod ++;
				$result.="
				<tr class=rowcontent>
					<td></td>
					<td align=center>".$noprod."</td>
					<td>Stok CPO</td>
					<td align=right></td>
					<td align=right></td>
					<td align=center></td>
					<td align=right><b>".($nox == '2' || $nox == '02' || $nox == 2 || $nox == 02 ? $kuantitas[array_keys($arrnmtangkicpo)[$nox]] : number_format($kuantitas[array_keys($arrnmtangkicpo)[$nox]],2))."</b></td>
					<td align=center>Kg</td>
					<td align=right></td>
					<td align=center></td>
				</tr>";
				$nox++;
			}
			$result.="
		 	<tr class=rowcontent>
		 		<td colspan=10>&nbsp;</td>
		 	</tr>
			<tr class=rowcontent>
				<td class='section-header'><b>IV.</b></td>
				<td colspan='2' class='section-header'><b>PRODUKSI KERNEL</b></td>
				<td><u>Norma</u></td>
				<td class='section-header' style='font-family:Arial' colspan=2 align=center>S.d. Kemarin</td>
				<td class='section-header' style='font-family:Arial' colspan=2 align=center>Hari Ini</td>
				<td class='section-header' style='font-family:Arial' colspan=2 align=center>S.d. Hari Ini</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center>31</td>
				<td>A L B</td>
				<td align=right>1.00 %</td>
				<td align=right></td>
				<td align=center>%</td>
				<td align=right>".number_format($kernelffa,2)."</td>
				<td align=center>%</td>
				<td align=right></td>
				<td align=center>%</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center>32</td>
				<td>KOTORAN</td>
				<td align=right>7.00 %</td>
				<td align=right></td>
				<td align=center>%</td>
				<td align=right>".number_format($kernelkdkot,2)."</td>
				<td align=center>%</td>
				<td align=right></td>
				<td align=center>%</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
				<td align=center>33</td>
				<td>A I R</td>
				<td align=right>6.00 %</td>
				<td align=right></td>
				<td align=center>%</td>
				<td align=right>".number_format($kernelmoist,2)."</td>
				<td align=center>%</td>
				<td align=right></td>
				<td align=center>%</td>
			</tr>
		 	<tr class=rowcontent>
		 		<td colspan=10>&nbsp;</td>
		 	</tr>
			<tr class=rowcontent>
				<td class='section-header'><b>V.</b></td>
				<td colspan='9' class='section-header'><b>STOCK CPO</b></td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>34.</td>
		 		<td colspan=2>STOCK AWAL</td>
		 		<td align=right></td>
				<td align=center></td>
		 		<td align=right>".($kuantitasallkmrn != '' ? number_format($kuantitasallkmrn) : number_format($kuantitasall+$barangkirimhi['CPO']-$cpoprodhi))."</td>
				<td align=center>Kg</td>
		 		<td align=right></td>
		 		<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>35.</td>
		 		<td colspan=2>PRODUKSI</td>
		 		<td align=right></td>
				<td align=center></td>
		 		<td align=right><b>".number_format($cpoprodhi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right></td>
		 		<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>36.</td>
		 		<td colspan=2>PENGIRIMAN</td>
		 		<td align=right>".number_format($barangkirimbi['CPO']-$barangkirimhi['CPO'])."</td>
				<td align=center>Kg</td>
		 		<td align=right><b>".number_format($barangkirimhi['CPO'])."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".number_format($barangkirimbi['CPO']-$barangkirimhirjc['CPO'])."</b></td>
		 		<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>37.</td>
		 		<td colspan=2>TONASE REJECT</td>
		 		<td align=right>-</td>
				<td align=center>Kg</td>
		 		<td align=right><b>".number_format($barangkirimhirjc['CPO'])."</b></td>
				<td align=center>Kg</td>
		 		<td align=right></td>
		 		<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>38.</td>
		 		<td colspan=2>TONASE BONGKAR</td>
		 		<td align=right>-</td>
				<td align=center>Kg</td>
		 		<td align=right></td>
				<td align=center>Kg</td>
		 		<td align=right></td>
		 		<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>39.</td>
		 		<td colspan=2>STOCK AKHIR</td>
		 		<td align=right></td>
				<td align=center></td>
		 		<td align=right><b>".number_format($kuantitasall)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right></td>
		 		<td align=center></td>
			</tr>
		 	<tr class=rowcontent>
		 		<td colspan=10>&nbsp;</td>
		 	</tr>
			<tr class=rowcontent>
				<td class='section-header'><b>VI.</b></td>
				<td colspan='9' class='section-header'><b>STOCK KERNEL</b></td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>40.</td>
		 		<td colspan=2>STOCK AWAL</td>
		 		<td align=right></td>
				<td align=center></td>
		 		<td align=right>".($kuantitaskerallkmrn != '' ? number_format($kuantitaskerallkmrn) : number_format($kuantitasker+$barangkirimhi['PK']-$kerprodhi))."</td>
				<td align=center>Kg</td>
		 		<td align=right></td>
		 		<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>41.</td>
		 		<td colspan=2>PRODUKSI</td>
		 		<td align=right></td>
				<td align=center></td>
		 		<td align=right><b>".number_format($kerprodhi)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right></td>
		 		<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>42.</td>
		 		<td colspan=2>PENGIRIMAN</td>
		 		<td align=right>".number_format($barangkirimbi['PK']-$barangkirimhi['PK'])."</td>
				<td align=center>Kg</td>
		 		<td align=right><b>".number_format($barangkirimhi['PK'])."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b>".number_format($barangkirimbi['PK']-$barangkirimhirjc['PK'])."</b></td>
		 		<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>43.</td>
		 		<td colspan=2>TONASE REJECT</td>
		 		<td align=right>-</td>
				<td align=center>Kg</td>
		 		<td align=right><b>".number_format($barangkirimhirjc['PK'])."</b></td>
				<td align=center>Kg</td>
		 		<td align=right><b></b></td>
		 		<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>44.</td>
		 		<td colspan=2>TONASE BONGKAR</td>
		 		<td align=right>-</td>
				<td align=center>Kg</td>
		 		<td align=right><b></b></td>
				<td align=center>Kg</td>
		 		<td align=right><b></b></td>
		 		<td align=center>Kg</td>
			</tr>
			<tr class=rowcontent>
				<td></td>
		 		<td align=center>45.</td>
		 		<td colspan=2>STOCK AKHIR</td>
		 		<td align=right></td>
				<td align=center></td>
		 		<td align=right><b>".number_format($kuantitasker)."</b></td>
				<td align=center>Kg</td>
		 		<td align=right></td>
		 		<td align=center></td>
			</tr>
			";
		$result.="
		</table>
		</div>
		<div class='catatan' style='padding-left:60px'>
			<h4>CATATAN :</h4>
			<ol>
			<li>Volume Minyak di Oil Tank = &plusmn;  %, Sludge Tank = &plusmn;  %</li>
			<li>Volume Nut Hopper 1 = &plusmn;  %, 2 = &plusmn;  %, 3 = &plusmn;  %</li>
			<li>Volume Silo kernel 1 = &plusmn;  %, 2 = &plusmn;  %</li>
			<li>Analisa  menggunakan hasil analisa bagian atas</li>
			<li>Kapasitas Pabrik dari CBC = ".($cbc)." Jam / ".@fixnan($tbsolahhi/$cbc)." Kg</li>";
			if($barangkirimhi['PK']>0){
				$result.="<li>Loading KERNEL CA - ".$suppliernya['PK']." ".($nokontrak['PK'] != ''? explode('/',$nokontrak['PK'])[0]." - ".$transportir['PK'] : "")." = ".number_format($barangkirimhi['PK'])." Kg</li>";
			}
			if($barangkirimhi['CPO']>0){
				$result.="<li>Loading CPO CA - ".$suppliernya['CPO']." (".explode('/',$nokontrak['CPO'])[0]." - ".$transportir['CPO'].") = ".number_format($barangkirimhi['CPO'])." Kg</li>";
			}
			if($loadinggudang > 0){
				$result.="<li>Loading Kernel dari gudang  = ".number_format($loadinggudang)." Kg</li>";
			}

			$result.="
			<li>Total Stok kernel Gudang total: ".number_format($barangkirimhi['PK'] - $loadinggudang)." Kg</li>
			</ol>
		</div>";

		$result.="</table>";	

		if($tipe=='preview'){
			echo $result;
		}else{
			$dte=date("YmdHis");
			$nop_="laporan_produksi_harian".$dte;
			if(strlen($result)>0){
				if ($handle = opendir('tempExcel')){
					while (false !== ($file = readdir($handle))){
						if ($file != "." && $file != ".."){
							@unlink('tempExcel/'.$file);
						}
					}
					closedir($handle);
				}
				
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$result)){
					echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				fclose($handle);
			}
		}
	break;
}

function bulanx($tanggal){
    //get tgl persetujuan
    $tgl=explode('-', $tanggal);
    $tahun=$tgl[0];
    $bulan=$tgl[1];

    switch ($bulan) {
        case '01' :$bulan='Januari';break;
        case '02' :$bulan='Februari';break;
        case '03' :$bulan='Maret';break;
        case '04' :$bulan='April';break;
        case '05' :$bulan='Mei';break;
        case '06' :$bulan='Juni';break;
        case '07' :$bulan='Juli';break;
        case '08' :$bulan='Agustus';break;
        case '09' :$bulan='September';break;
        case '10' :$bulan='Oktober';break;
        case '11' :$bulan='November';break;
        case '12' :$bulan='Desember';break;
        default:
        break;
    }
    return $tgl=$bulan." ".$tahun;
}

?>