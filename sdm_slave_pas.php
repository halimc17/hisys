<?php
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;

$param = $_POST;
if(count($param)==0){$param = $_GET;}
$id       = checkPostGet('id','');
$method   = checkPostGet('method','');
$tipe     = checkPostGet('tipe','');
$nama     = checkPostGet('nama','');
$dept     = checkPostGet('dept','');
$tglnilai = tanggalsystemn(checkPostGet('tglnilai',''));
$thnnilai = checkPostGet('thnnilai','');
$kekuatan = checkPostGet('kekuatan','');
$kelemahan= checkPostGet('kelemahan','');
$tipeprint= checkPostGet('tipeprint','');
$jab      = getPostingJabatan('kpi'); 

switch ($method) {
	case 'simpan':
		try {
            $owlPDO->beginTransaction();
			if($param['atasanpenilai']==$param['penilai']){
				throw new PDOException("Atasan dan Penilai tidak boleh sama.");
			}
			
			$where = " and id='".$param['id']."'";
			$str = "delete from ".$dbname.".sdm_pas WHERE 1=1 ".$where."";
			$owlPDO->exec($str);
		
            $data = array(
				'id'             => $param['id'],
				'kelebihan'      => $param['kelebihan'],
				'usulankelebihan'=> $param['usulankelebihan'],
				'pica'           => $param['pica'],
				'kelemahan'      => $param['kelemahan'],
				'usulankelemahan'=> $param['usulankelemahan'],
				'catatankary'    => $param['catatankary'],
				'kehadiran'      => $param['kehadiran'],
				'atasanpenilai'  => $param['atasanpenilai'],
				'penilai'        => $param['penilai'],
				'nilaifinal'     => $param['nilaifinal'],
				'kategorifinal'  => $param['nilaifinalscore'],
				'posting'        => '0',
				'createdby'      => $_SESSION['standard']['userid'],
				'createdtime'    => date('Y-m-d H:i:s'),
				'updateby'       => $_SESSION['standard']['userid']
			);

           	$queryH = insertQuery($dbname,'sdm_pas',$data,array_keys($data)); #exit("error".$queryH);
			$owlPDO->exec($queryH);
         
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warningcode : " . addslashes($e->getMessage());
        }
	break;
	case'loaddatadetail':
		$str = "select * from ".$dbname.".sdm_kpi where karyawanid='".$param['karyawanid']."' and tahun='".$param['tahun']."' order by penilaian asc";
		$res = fetchdata($str);
		foreach($res as $val){
			$listp[$val['penilaian']]= $val['penilaian'];
		}
		
		$str = "select * from ".$dbname.".sdm_kpi where karyawanid='".$param['karyawanid']."' and tahun='".$param['tahun']."' and penilaian='".$param['penilaian']."' order by penilaian asc";
		$res = fetchdata($str);
		foreach($res as $val){
			$jabatan  = $val['jabatan'];
			$dept     = $val['dept'];
			$man      = $val['manmanagement'];
			$penilaian= $val['penilaian'];
			$periodedr= $val['periodedr'];
			$periodesd= $val['periodesd'];
			$idht     = $val['id'];
			$tanggal  = $val['tanggal'];
			
			$postingkpi = $val['posting'];
			$approvalkpi= $val['approval'];
			$namaatasan = $val['namaatasan'];
		}
		$namaman=array('Y'=>'YA','N'=>'TIDAK');
		
		//$tab.="<fieldset style=float:left>";
		//$tab.="<legend>".$_SESSION['lang']['karyawan']."</legend>";
		$tab.="<input hidden id=idht value=".$idht.">";
		$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>";
		$tab.="<tr class=rowcontent>
					<td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
					<td style=min-width:200px><b>".getKary($param['karyawanid'],'namakaryawan')."</b></td>
					
					
					<td>".$_SESSION['lang']['nik2']."</td><td>:</td>
					<td style=min-width:200px>".getKary($param['karyawanid'],'nik')."</td>
				</tr>";
		$tab.="<tr class=rowcontent>
					<td>".$_SESSION['lang']['lokasitugas']."</td><td>:</td>
					<td>".getNamaOrg(getHistKary($param['karyawanid'],$param['tahun'],'lokasitugas'))."</td>
					
					
					<td>".$_SESSION['lang']['jabatan']."</td><td>:</td>
					<td>".getNamaJabatan($jabatan)."</td>
				</tr>";
		$tab.="<tr class=rowcontent>
					<td>".$_SESSION['lang']['departemen']."</td><td>:</td>
					<td>".getNamaDept($dept)."</td>
					
					
					<td>Man Management</td><td>:</td>
					<td>".$namaman[$man]."</td>
				</tr>";
		$tab.="</table>";
		// $tab.="</fieldset>";
		$tab.="<div style=clear:both></div><br>";
		
		if($penilaian=='Q1'){
			$prev="";
		}else{
			$nilaike = intval(substr($param['penilaian'],-1))-1;
			if($listp['Q'.$nilaike]!=''){
				$prev="<button style=border-color:green;color:blue;font-weight:bold;background-color:#11f7cd; class=mybutton onclick=\"loaddatadetail('".$param['karyawanid']."','".$param['tahun']."','Q".$nilaike."');\">Prev Q".$nilaike."</button>";
			}else{
				$prev="";				
			}
		}
		if($penilaian=='Q4'){
			$next="";
		}else{
			$nilaike = intval(substr($param['penilaian'],-1))+1;
			if($listp['Q'.$nilaike]!=''){				
				$next="<button style=border-color:green;color:blue;font-weight:bold;background-color:#11f7cd; class=mybutton onclick=\"loaddatadetail('".$param['karyawanid']."','".$param['tahun']."','Q".$nilaike."');\">Q".$nilaike." Next</button>";
			}else{
				$next="";				
			}
		}
		
		
		$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>
				<thead>
					<tr class=rowheader>
						<th align=center rowspan=2 colspan=3>EVALUASI KOMPETENSI</th>
						<th align=center colspan=6>
							".$prev."&nbsp;&nbsp;
								".$penilaian." ( ".strtoupper(numToMonth($periodedr,"I","short"))." - ".strtoupper(numToMonth($periodesd,"I","short"))." ".$param['tahun'].")
							&nbsp;&nbsp;".$next."
						</th>
						<th align=center rowspan=2>NILAI<br>KUMULATIF<br>SCORE</th>
						<th align=center colspan=2>UMPAN BALIK</th>
					</tr>
					<tr class=rowheader>
						<th align=center>BOBOT<br>(%)</th>
						<th align=center>NILAI-1</th>
						<th align=center>SCORE-1</th>
						<th align=center>NILAI-2</th>
						<th align=center>SCORE-2</th>
						<th align=center>TOTAL<BR>SCORE</th>
						<th align=center>KELEBIHAN KARYAWAN YANG DAPAT DIDAYAGUNAKAN</th>
						<th align=center>USULAN PENDAYAGUNAAN (COACHING/MENTORING/dst)</th>
					</tr>
				</thead>
				";
			if($man=='Y'){
				$adaanakbuah="1";
			}else{
				$adaanakbuah="2";
			}
				
			$str = "select * from ".$dbname.".sdm_5pms where tipe='".$adaanakbuah."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['kriteria']=='KPI'){
					$bobotkpi=$bar['persen'];
				}
				if($bar['kriteria']=='Core Values'){
					$bobotcore=$bar['persen'];
				}
				if($bar['kriteria']=='Man Management'){
					$bobotman=$bar['persen'];
				}
			}
			
			
			$str = "select a.manmanagement, a.tahun, a.karyawanid, b.bobot, b.porsisendiri, 
			b.porsiatasan, c.nilaiatasan, c.proporsiatasan, 
			c.nilaisendiri, c.proporsisendiri, c.totalproporsi 
			from ".$dbname.".sdm_kpi a
			left join ".$dbname.".sdm_kpidt1 b on a.id=b.idht
			left join ".$dbname.".sdm_kpidt2 c on b.idkpi=c.iddt1
			where a.id='".$idht."'";
			$res = fetchdata($str);
			$count = 0;
			if(count($res)>0){						
				$count ++;
			}
			foreach($res as $val){
				$porsisendiri   += $val['porsisendiri'];
				$porsiatasan    += $val['porsiatasan'];
				$nilaiatasan    += $val['nilaiatasan'];
				$proporsiatasan += $val['proporsiatasan'];
				$nilaisendiri   += $val['nilaisendiri'];
				$proporsisendiri+= $val['proporsisendiri'];
				$totalproporsi  += $val['totalproporsi'];
			}
			
			$nilaike = substr($param['penilaian'],-1);
			if($nilaike>1){
				$range = range(1,($nilaike-1));
				foreach($range as $penilai){
					$str = "select a.manmanagement, a.tahun, a.karyawanid, b.bobot, b.porsisendiri, 
					b.porsiatasan, c.nilaiatasan, c.proporsiatasan, 
					c.nilaisendiri, c.proporsisendiri, c.totalproporsi 
					from ".$dbname.".sdm_kpi a
					left join ".$dbname.".sdm_kpidt1 b on a.id=b.idht
					left join ".$dbname.".sdm_kpidt2 c on b.idkpi=c.iddt1
					where a.karyawanid='".$param['karyawanid']."' and 
					a.tahun='".$param['tahun']."' and a.penilaian='Q".$penilai."'";
					$res = fetchdata($str);
					if(count($res)>0){						
						$count ++;
					}
					foreach($res as $bar){
						$nilaiatasanold[$penilai]    += $bar['nilaiatasan'];
						$proporsiatasanold[$penilai] += $bar['proporsiatasan'];
						$nilaisendiriold[$penilai]   += $bar['nilaisendiri'];
						$proporsisendiriold[$penilai]+= $bar['proporsisendiri'];
						$totalproporsiold[$penilai]  += $bar['totalproporsi'];
						$man 					      = $bar['manmanagement'];
						
					}
					if($man=='Y'){
						$adaanakbuah="1";
					}else{
						$adaanakbuah="2";
					}
					$str = "select * from ".$dbname.".sdm_5pms where tipe='".$adaanakbuah."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						if($bar['kriteria']=='KPI'){
							$bobotkpiold[$penilai]=$bar['persen'];
						}
						if($bar['kriteria']=='Core Values'){
							$bobotcoreold[$penilai]=$bar['persen'];
						}
						if($bar['kriteria']=='Man Management'){
							$bobotmanold[$penilai]=$bar['persen'];
						}
					}
					$scoreatasanold+=$proporsiatasanold[$penilai]*($bobotkpiold[$penilai]/100);
					$scoresendiriold+=$proporsisendiriold[$penilai]*($bobotkpiold[$penilai]/100);
				}
			}
			
			$scoreatasan=$proporsiatasan*($bobotkpi/100);
			$scoresendiri=$proporsisendiri*($bobotkpi/100);
			
			$str = "select * from ".$dbname.".sdm_pas where id='".$idht."'";
			$res = fetchdata($str);
			foreach($res as $val){
				$kelebihan      = $val['kelebihan'];
				$usulankelebihan= $val['usulankelebihan'];
				$pica           = $val['pica'];
				$kelemahan      = $val['kelemahan'];
				$usulankelemahan= $val['usulankelemahan'];
				$catatankary    = $val['catatankary'];
				$kehadiran      = $val['kehadiran'];
				$atasanpenilai  = $val['atasanpenilai'];
				$nilaifinal     = $val['nilaifinal'];
				$kategorifinal  = $val['kategorifinal'];
				$karypenilai    = $val['penilai'];
			}
			
			$blmlengkap=false; $atasblmisi=$ybsblmisi=""; $dataerr=array();
			if($porsiatasan>0 and $proporsiatasan<=0){
				$atasblmisi="style=background-color:red; title='Atasan belum melakukan penilaian.'";
				$blmlengkap=true; $dataerr[]="KPI, atasan belum melakukan penilaian.";
			}
			if($porsisendiri>0 and $proporsisendiri<=0){
				$ybsblmisi="style=background-color:red; title='Diri sendiri belum melakukan penilaian.'";
				$blmlengkap=true; $dataerr[]="KPI, diri sendiri belum melakukan penilaian.";
			}
			if($postingkpi==0){
				$blmlengkap=true; $dataerr[]="KPI, belum diposting.";
			}
			if($approvalkpi==0){
				$blmlengkap=true; $dataerr[]="KPI, belum disetujui.";
			}
			
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center width=30px>1</td>
				<td colspan=2>PENILAIAN HASIL KERJA (KPI)</td>
				<td align=center>".hidezerodecimal($bobotkpi,2)."</td>
				<td align=center ".$atasblmisi.">".hidezerodecimal($proporsiatasan,2)."</td>
				<td align=center ".$atasblmisi.">".hidezerodecimal($scoreatasan,2)."</td>
				<td align=center ".$ybsblmisi.">".hidezerodecimal($proporsisendiri,2)."</td>
				<td align=center ".$ybsblmisi.">".hidezerodecimal($scoresendiri,2)."</td>
				<td align=center>".hidezerodecimal($scoresendiri+$scoreatasan,2)."</td>
				<td align=center>".hidezerodecimal(($scoreatasan+$scoreatasanold+$scoresendiri+$scoresendiriold)/$count,2)."</td>
				<td rowspan=6><textarea onblur=showtombol(); id=kelebihan rows=9 style=width:340px>".$kelebihan."</textarea></td>
				<td rowspan=6><textarea onblur=showtombol(); id=usulankelebihan rows=9 style=width:340px>".$usulankelebihan."</textarea></td>
				</tr>";
			$kumulkpi=($scoreatasan+$scoreatasanold+$scoresendiri+$scoresendiriold)/$count;	
			
			$sql = "select distinct b.updateby
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='corevalue'";
			$res = fetchdata($sql);
			$atasblmisi="";$porsinilaiatasan='0';
			foreach($res as $bar){
				$porsinilaiatasan++;
			}
			
			if($porsinilaiatasan<2){
				$atasblmisi="style=background-color:#fac0c0; title='Atasan belum melakukan penilaian, maka nilai yang ada akan diambil.'";
				$blmlengkap=true;
			}
			
			$sql = "select posting, approval from ".$dbname.".sdm_corevalueandmanmanagement a where a.karyawanid='".$param['karyawanid']."' and  a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."' and a.jenis='corevalue'";
			$req = fetchdata($sql);
			if($req[0]['posting']==0){
				$blmlengkap=true; $dataerr[]="Core Value, belum diposting.";
			}
			if($req[0]['approval']==0){
				$blmlengkap=true; $dataerr[]="Core Value, belum disetujui.";
			}
			
			
			$maxpenilai="and penilai in (select max(b.penilai) as penilai
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='corevalue')";
			
			$str = "select sum(b.nilai) as jumlah, avg(b.nilai) as rata
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='corevalue' ".$maxpenilai."";
			$res = fetchdata($str);
			foreach($res as $val){
				$jlhcorevalue=$val['jumlah'];
				$avgcorevalue=$val['rata'];
			}
			
			if($nilaike>1){
				$range = range(1,($nilaike-1));
				foreach($range as $penilai){
					$str = "select sum(b.nilai) as jumlah, avg(b.nilai) as rata
					from ".$dbname.".sdm_corevalueandmanmanagement a
					left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
					where a.karyawanid='".$param['karyawanid']."' and 
					a.tahun='".$param['tahun']."' and a.penilaian='Q".$penilai."'
					and a.jenis='corevalue'";
					$res = fetchdata($str);
					foreach($res as $val){
						$jlhcorevalueold[$penilai]=$val['jumlah'];
						$avgcorevalueold[$penilai]=$val['rata'];
					}
					$corevalatasanold+=$avgcorevalueold[$penilai]*($bobotcoreold[$penilai]/100);
				}
			}
			$corevalatasan=$avgcorevalue*($bobotcore/100);
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>2</td>
				<td colspan=2>PENILAIAN KSP AGRO<br>CORE VALUES (FASTER)</td>
				<td align=center>".hidezerodecimal($bobotcore,2)."</td>
				<td align=center ".$atasblmisi.">".hidezerodecimal($avgcorevalue,2)."</td>
				<td align=center ".$atasblmisi.">".hidezerodecimal($corevalatasan,2)."</td>
				<td align=center>0</td>
				<td align=center>0</td>
				<td align=center>".hidezerodecimal($corevalatasan,2)."</td>
				<td align=center>".hidezerodecimal(($corevalatasan+$corevalatasanold)/$count,2)."</td>
				</tr>";
			$kumulcore=($corevalatasan+$corevalatasanold)/$count;
			
			
			$sql = "select distinct b.updateby
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='manmanagement'";
			$res = fetchdata($sql);
			$atasblmisi="";$porsinilaiatasan='0';
			foreach($res as $bar){
				$porsinilaiatasan++;
			}
			
			if($man=='Y' and $porsinilaiatasan<2){
				$atasblmisi="style=background-color:#fac0c0; title='Atasan belum melakukan penilaian, maka nilai yang ada akan diambil.'";
				$blmlengkap=true;
			}
			if($man=='Y'){				
				$sql = "select posting, approval from ".$dbname.".sdm_corevalueandmanmanagement where karyawanid='".$param['karyawanid']."' and  tahun='".$param['tahun']."' and penilaian='".$param['penilaian']."' and jenis='manmanagement'";
				$req = fetchdata($sql);
				if($req[0]['posting']==0){
					$blmlengkap=true; $dataerr[]="Man Management, belum diposting.";
				}
				if($req[0]['approval']==0){
					$blmlengkap=true; $dataerr[]="Man Management, belum disetujui.";
				}
			}
			
			
			$maxpenilai="and penilai in (select max(b.penilai) as penilai
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='manmanagement')";
			
			$str = "select sum(b.nilai) as jumlah, avg(b.nilai) as rata
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='manmanagement' ".$maxpenilai."";
			$res = fetchdata($str);
			foreach($res as $val){
				$jlhman=$val['jumlah'];
				$avgman=$val['rata'];
			}
			if($nilaike>1){
				$range = range(1,($nilaike-1));
				foreach($range as $penilai){
					$str = "select sum(b.nilai) as jumlah, avg(b.nilai) as rata
					from ".$dbname.".sdm_corevalueandmanmanagement a
					left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
					where a.karyawanid='".$param['karyawanid']."' and 
					a.tahun='".$param['tahun']."' and a.penilaian='Q".$penilai."'
					and a.jenis='manmanagement'";
					$res = fetchdata($str);
					foreach($res as $val){
						$jlhmanold[$penilai]=$val['jumlah'];
						$avgmanold[$penilai]=$val['rata'];
					}
					$manatasanold+=$avgmanold[$penilai]*($bobotmanold[$penilai]/100);
				}
			}
			$manatasan=$avgman*($bobotman/100);
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>3</td>
				<td colspan=2>PENILAIAN PENGELOLAAN ORANG</td>
				<td align=center>".hidezerodecimal($bobotman,2)."</td>
				<td align=center ".$atasblmisi.">".hidezerodecimal($avgman,2)."</td>
				<td align=center ".$atasblmisi.">".hidezerodecimal($manatasan,2)."</td>
				<td align=center>0</td>
				<td align=center>0</td>
				<td align=center>".hidezerodecimal($manatasan,2)."</td>
				<td align=center>".hidezerodecimal(($manatasan+$manatasanold)/$count,2)."</td>
				</tr>";
				$kumulman=($manatasan+$manatasanold)/$count;
				$ttlscore=$manatasan+$corevalatasan+$scoresendiri+$scoreatasan;
				
				$ttlkumul=$kumulkpi+$kumulcore+$kumulman;
				
			$tab.="<tr class=rowcontent style=vertical-align:top;font-weight:bold;>
				<td align=center></td>
				<td colspan=2>PERFORMANCE VALUE</td>
				<td align=center>".hidezerodecimal($bobotman+$bobotcore+$bobotkpi,2)."</td>
				<td align=center>".hidezerodecimal($avgman+$avgcorevalue+$proporsiatasan,2)."</td>
				<td align=center>".hidezerodecimal($manatasan+$corevalatasan+$scoreatasan,2)."</td>
				<td align=center>".hidezerodecimal($proporsisendiri,2)."</td>
				<td align=center>".hidezerodecimal($scoresendiri,2)."</td>
				<td align=center>".hidezerodecimal($ttlscore,2)."</td>
				<td align=center>".hidezerodecimal($ttlkumul,2)."</td>
				</tr>";	
			
			if($ttlscore<61){
				$n="KURANG";
			}elseif($ttlscore>=61 and $ttlscore<81){
				$n="CUKUP";
			}elseif($ttlscore>=81 and $ttlscore<91){
				$n="BAIK";
			}elseif($ttlscore>=91 and $ttlscore<110){
				$n="SANGAT BAIK";
			}elseif($ttlscore>110){
				$n="LUAR BIASA";
			}
			
			if($ttlkumul<61){
				$e="KURANG";
			}elseif($ttlkumul>=61 and $ttlkumul<81){
				$e="CUKUP";
			}elseif($ttlkumul>=81 and $ttlkumul<91){
				$e="BAIK";
			}elseif($ttlkumul>=91 and $ttlkumul<110){
				$e="SANGAT BAIK";
			}elseif($ttlkumul>110){
				$e="LUAR BIASA";
			}
			
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center></td>
				<td colspan=2><b>KATEGORI PENILAIAN</b></td>
				<td align=center colspan=6><b>".$n."</b></td>
				<td align=center><b>".$e."</b></td>
				</tr>";	
				
			$dis="disabled";
			if($_SESSION['standard']['userid']==$atasanpenilai){
				$dis="";
			}
			if($nilaifinal<=0){
				$nilaifinal=$ttlkumul;
				$kategorifinal=$e;
			}
			
			$tab.="<tr class=rowcontent style=vertical-align:top;cellpadding:1;>
				<td align=center></td>
				<td colspan=2><b>PERFORMANCE VALUE FINAL</b></td>
				<td align=center colspan=6 id=nilaifinalscore style=font-weight:bold;>".$kategorifinal."</td>
				<td align=center style=cellpadding:1;><input ".$dis." onkeyup=getfinalscore(this.value) onkeypress=\"return angka_doang(event);\" id=nilaifinal class=myinputtextnumber style=width:60px value=".$nilaifinal."></td>
				</tr>";	
			
			
			$tab.="<thead><tr class=rowheader style=vertical-align:top;>
				<th align=center colspan=10>CATATAN PENILAI (PICA KINERJA)</th>
				<th align=center>KELEMAHAN KARYAWAN YANG DAPAT DIKEMBANGKAN</th>
				<th align=center>USULAN PENGEMBANGAN (COACHING/MENTORING/dst)</th>
				</tr></thead>";
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center colspan=10><textarea onblur=showtombol(); id=pica rows=7 style=width:97%>".$pica."</textarea></td>
				<td align=center><textarea onblur=showtombol(); id=kelemahan rows=7 style=width:340px>".$kelemahan."</textarea></td>
				<td align=center><textarea onblur=showtombol(); id=usulankelemahan rows=7 style=width:340px>".$usulankelemahan."</textarea></td>
				</tr>";
			
			$tab.="<thead><tr class=rowheader style=vertical-align:top;>
				<th align=center colspan=10>CATATAN KARYAWAN YANG DINILAI</th>
				<th align=center>CATATAN KEHADIRAN SELAMA PERIODE PENILAIAN</th>
				<th align=center>FREKUENSI/JUMLAH HARI</th>
				</tr></thead>";
			
			if($kehadiran!=''){
				$default=$kehadiran;
			}else{				
				$default="Sakit : \nIjin : \nAlpa : \nTerlambat : \nIjin Pulang Awal : ";
			}
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center colspan=10><textarea onblur=showtombol(); id=catatankary rows=7 style=width:97%>".$usulankelemahan."</textarea></td>
				<td align=center colspan=2><textarea onblur=showtombol(); id=kehadiran rows=7 style=width:97%>".$default."</textarea></td>
				</tr>";
				
			$tab.="<thead><tr class=rowheader style=vertical-align:top;>
				<th align=center colspan=3>KRITERIA PENILAIAN</th>
				<th align=center colspan=7>ATASAN PENILAI</th>
				<th align=center>PENILAI</th>
				<th align=center>KARYAWAN YANG DINILAI</th>
				</tr></thead>";

			$default="Kurang : 0 - 60<br>Cukup :61 - 80<br>Baik : 81 - 90<br>Sangat Baik : 91 - 110<br>Luar Biasa : 110 - 120";	
			$tab.="<tr class=rowcontent>
				<td align=center>1</td>
				<td align=left>Kurang</td>
				<td align=left width=60px>0 - 60</td>
				<td align=center colspan=7 rowspan=4></td>
				<td align=center rowspan=4></td>
				<td rowspan=4></td>
				</tr>";
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>2</td>
				<td align=left>Cukup</td>
				<td align=left>61 - 80</td>
				</tr>";
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>3</td>
				<td align=left>Baik</td>
				<td align=left>81 - 90</td>
				</tr>";	
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>4</td>
				<td align=left>Sangat Baik</td>
				<td align=left>91 - 110</td>
				</tr>";
			$str = "SELECT atasanpenilai FROM ".$dbname.".sdm_pas where 1=1 and penilai='".$_SESSION['standard']['userid']."' ORDER BY createdtime desc limit 1";
			$res = fetchdata($str);
			foreach($res as $val){	
				$atasansimpan=$val['atasanpenilai'];
			}
			
			
			$str = "SELECT karyawanid,penilaian,tahun FROM ".$dbname.".sdm_kpi WHERE id	='".$idht."'";
			$res = fetchdata($str);
			$nama = $res[0]['karyawanid'];
			$penilaian = $res[0]['penilaian'];
			$tahun = $res[0]['tahun'];
			
			
			$optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$optkaratasan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			if($_SESSION['standard']['userid']!=$atasanpenilai){
				$whereKary.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tanggal."')";
			}
			$whereKary.= " and tipekaryawan in ('0') and (1=1 or karyawanid in (select atasanpenilai FROM ".$dbname.".sdm_pas) or karyawanid in (select penilai FROM ".$dbname.".sdm_pas) or karyawanid in (select createdby FROM ".$dbname.".sdm_pas))";
			$str = "SELECT * FROM ".$dbname.".datakaryawan where 1=1 ".$whereKary." ORDER BY kodejabatan, tipekaryawan, namakaryawan ASC";
			$res = fetchdata($str);
			foreach($res as $val){
				$d=$val['kodejabatan'];
				if($d!=$n){			
					$nmorg = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan',"kodejabatan='".$d."'");
					$optkar.="<optgroup label='".$nmorg[$d]."'>";
					$optkaratasan.="<optgroup label='".$nmorg[$d]."'>";
				}
				$p="";
				if($karypenilai==''){$karypenilai=$namaatasan;}
				if($karypenilai=='0000000000'){$karypenilai=$_SESSION['standard']['userid'];}
				if($karypenilai==$val['karyawanid']){
					$p="selected";
				}
				$optkar .= "<option value='".$val['karyawanid']."' ".$p.">".$val['namakaryawan']."</option>";
				
				$n="";
				if($atasanpenilai==$val['karyawanid'] or $atasansimpan==$val['karyawanid']){
					$n="selected";
				}
				$optkaratasan .= "<option value='".$val['karyawanid']."' ".$n.">".$val['namakaryawan']."</option>";
				$n=$d;
				if($d!=$n){
					$optkar.="</optgroup>";
					$optkaratasan.="</optgroup>";
				}
			}	
			
			$disabled="";
			if($_SESSION['standard']['userid']==$atasanpenilai){
				$disabled="disabled";
			}
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>5</td>
				<td align=left>Luar Biasa</td>
				<td align=left>110 - 120</td>
				<td align=center colspan=7><select ".$disabled." onchange=showtombol(); id='atasanpenilai' class='select2'>".$optkaratasan."</select></td>
				<td align=center><select ".$disabled." onchange=showtombol(); id='penilai' class='select2'>".$optkar."</select></td>
				<td align=center><b>".getKary($param['karyawanid'],'namakaryawan')."</b></td>
				</tr>";	
			$err=false;
			if(!empty($dataerr)){
				$err=true;
				$tab.="<tr class=rowcontent style=vertical-align:top;>
					<td colspan=20 style=background-color:#fac0c0>";
				$tab.="<h3>Daftar kesalahan :</h3>";
				$tab.="<ol>";
				foreach($dataerr as $key => $valerr){
					$tab.="<li>".$valerr."<br></li>";
				}
				$tab.="</ol>";
				$tab.="</td>";
				$tab.="</tr>";
			}	
						
			$tab.="</table>";
					
			$tab.="<center style=display:none; id=tombolsimpan>
				<button class=mybutton style=width:250px;height:30px;color:white;background-color:red; onclick=\"simpan('".$err."')\";>Simpan</button>
				<input id=cekperubahan style=display:none>
			</center>";
		echo $tab;
	break;
	
	case 'loaddata':
		$where = "";
		if ($param['post'] != '') {
			if ($param['post'] == '2') {
				$where .= " AND id in (select id from ".$dbname.".sdm_pas where approval = '1')";
			}else{				
				$where .= " AND id in (select id from ".$dbname.".sdm_pas where posting='".$param['post']."')";
			}
		}
		
		if ($nama != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".$nama."%')";
		}
		if ($param['atasan'] != '') {
			$where .= " AND atasanpenilai in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".$param['atasan']."%')";
		}
		if ($dept != '') {
			$where .= " AND dept='".$dept."'";
		}
		if ($thnnilai != ''){
			$where .= " AND tahun='".$thnnilai."'";
		}
		
		if ($param['unit'] != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas like '%".trim($param['unit'])."%')";
		}
		if ($param['gol'] != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where kodegolongan = '".trim($param['gol'])."')";
		}
		if ($param['penilaian'] != '') {
			$where .= " AND penilaian='".$param['penilaian']."'";
		}
		
		#jika sumbernya dari approval kita by id yang harus disetujui saja
		// exit('warning'.$_SESSION['approval']['pas']);
		if(!empty($_SESSION['approval']['pas'])){
			foreach($_SESSION['approval']['pas'] as $key => $value){
				$notr[$value['notransaksi']]=$value['notransaksi'];
			}
			$where .= "and id in ('".implode("','",$notr)."')";
		}else{			
			# jika dept HCM dan tipe kary staff dan lokasi tugas RO atau HO
			$userhcm=[];
			$str = "select * from ".$dbname.".setup_parameterappl where kodeparameter='KPI'";
			$req = fetchdata($str);
			foreach($req as $val){
				$arrusertemp=explode(",",$val['nilai']);				
				foreach($arrusertemp as $uname){					
					$userhcm[$uname]=$uname;
				}
			}
			if($userhcm[$_SESSION['standard']['userid']]!=''){
				if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
					$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas not like '%HO')";
				}else{
					$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas = '".$_SESSION['empl']['lokasitugas']."')";
				}
			}else{			
				if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
					$where .= " AND (karyawanid in (select karyawanid from ".$dbname.".datakaryawan where bagian = '".$_SESSION['empl']['bagian']."')";
					$where .= " or (karyawanid='".$_SESSION['standard']['userid']."' or createdby='".$_SESSION['standard']['userid']."'))";
				} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
					$where .= " AND (karyawanid in (select karyawanid from ".$dbname.".datakaryawan where bagian = '".$_SESSION['empl']['bagian']."')";
					$where .= " or (karyawanid='".$_SESSION['standard']['userid']."' or createdby='".$_SESSION['standard']['userid']."'))";
				} else {
					$where .= " AND (karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas = '".$_SESSION['empl']['lokasitugas']."'))";
					if ($thnnilai == ''){
						$where .= " AND tahun='".date('Y')."'";
					}
				}
			}
		}
		
		
		# pastikan hanya golongan dibawahnya saja yang muncul
		$nmgol = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$where.= " and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where kodegolongan in (select kodegolongan from ".$dbname.".sdm_5golongan where namagolongan < '".$nmgol[$_SESSION['empl']['kodegolongan']]."'))";
		
		$tab="<br><table border=0 cellspacing=1 cellpadding=5 class=sortable>
					<thead>
						<tr class=rowheader>
							<th align=center>".$_SESSION['lang']['nourut']."</th>
							<th align=center>".$_SESSION['lang']['tahun']."</th>
							<th align=center>".$_SESSION['lang']['periode']."</th>
							<th align=center>".$_SESSION['lang']['nik2']."</th>
							<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
							<th align=center>".$_SESSION['lang']['lokasitugas']."</th>
							<th align=center>".$_SESSION['lang']['jabatan']."</th>
							<th align=center>".$_SESSION['lang']['departemen']."</th>
							<th align=center>Penilai</th>
							<th align=center>Atasan</th>
							<th align=center title=\"Key Performance Indicator\">KPI</th>
							<th align=center title=\"Core Values\">CV</th>
							<th align=center title=\"Man Management\">MM</th>
							<th align=center title=\"Performance Appraisal Summary\">PAS</th>
							<th align=center>".$_SESSION['lang']['createby']."</th>
							<th align=center>".$_SESSION['lang']['updateby']."</th>
							<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
						</tr>
					</thead>
					<tbody>";

        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 17;

		$str = "SELECT COUNT(*) as jmlhrow FROM ".$dbname.".sdm_kpi WHERE 1=1 ".$where; 
        $res = fetchdata($str);
        foreach($res as $bar){
            $jlhbrs = $bar['jmlhrow'];
        }
        
		$str = "select * from ".$dbname.".sdm_kpidt1 where idht in (SELECT id FROM ".$dbname.".sdm_kpi WHERE 1=1 ".$where.")";
		$req = fetchdata($str);
		foreach($req as $val){
			$sql = "select * from ".$dbname.".sdm_kpidt2 where iddt1 ='".$val['idkpi']."' order by iddt1";
			$req = fetchdata($sql);
			foreach($req as $bar){
				$totalproporsi[$val['idht']]+=$bar['totalproporsi'];
			}
			$bobot[$val['idht']]+=$val['bobot'];
		}
		$arrstatus=array('0'=>'','1'=>'Disetujui','2'=>'Ditolak');
		
        $no = $offset+1;
		$str = "SELECT * FROM ".$dbname.".sdm_kpi
				WHERE 1=1 ".$where."
				ORDER BY periodesd DESC, id desc
				LIMIT ".$offset.",".$limit;
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			$ratamm=$ratacv=$idmm=$idcv="";
			$query = "select * from ".$dbname.".sdm_corevalueandmanmanagement where tahun='".$val['tahun']."' and karyawanid='".$val['karyawanid']."' and penilaian='".$val['penilaian']."'";
			$dataC = fetchdata($query);
			$cv="Belum Input";$mm="Belum Input"; $creatcv=$creatmm="";$statcvmm="x";
			$stat='x';
			if(count($dataC)==0){
			}else{
				foreach($dataC as $bar){
					if($bar['jenis']=='corevalue'){
						if($bar['posting']=='1'){
							$cv="Posted";
							$stat='y'; $statcvmm="y";
							$creatcv="createby ".getKary($bar['createby']);
						}else{
							$cv="Not Posted";
							$stat='x'; $statcvmm="x";
						}
						
						$idcv = $bar['id'];
						
						$query2 = "SELECT AVG(nilai) as nilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$bar['id']."' and penilai in (SELECT max(penilai) as penilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$bar['id']."') GROUP BY id";
						$rata2 = fetchdata($query2);
						$ratacv = $rata2[0]['nilai'];
					}
					if($val['manmanagement']=='Y'){	
						if($bar['jenis']=='manmanagement'){
							if($bar['posting']=='1'){
								$mm="Posted";
								$stat='y'; $statcvmm="y";
								$creatmm="createby ".getKary($bar['createby']);
							}else{
								$mm="Not Posted";
								$stat='x'; $statcvmm="x";
							}
						}else{
							$mm="Belum Input";
							$stat='x'; $statcvmm="x";
						}
						$idmm = $bar['id'];
						
						$query2 = "SELECT AVG(nilai) as nilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$bar['id']."' and penilai in (SELECT max(penilai) as penilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$bar['id']."') GROUP BY id";
						$rata2 = fetchdata($query2);
						$ratamm = $rata2[0]['nilai'];
					}else{
						//$stat='y'; 
						$mm="Tidak"; $statcvmm="y";
					}
				}
			}
			
			if($val['posting']=='0'){
				$kpi="Not Posted";
				$stat='x';
			}else{
				$kpi="Posted";
				//$stat='y';
			}
			
			$createdby=$createdtime=$updateby=$lastupdate=$posting=$atasanpenilai=$penilai=$nilaifinal=$approval=$posting="";
			$sql = "select * from ".$dbname.".sdm_pas where id='".$val['id']."'";
			$req = fetchdata($sql);
			foreach($req as $bar){
				$nilaifinal  =$bar['nilaifinal'];
				$penilai  =$bar['penilai'];
				$atasanpenilai  =$bar['atasanpenilai'];
				$approval  =$bar['approval'];
				
				$createdby  =$bar['createdby'];
				$createdtime=$bar['createdtime'];
				$updateby   =$bar['updateby'];
				$lastupdate =$bar['lastupdate'];
				if($bar['posting']=='0'){
					$pas="Not Posted";
				}else{
					$pas="Posted";
				}
				$posting=$bar['posting'];
			}
			$ccc = "style=cursor:pointer; title=\"Click.\"";
			if(count($req)==0){
				$ccc = "style=cursor:pointer;color:red; title=\"Belum diinput.\""; $pas="Belum Input";
			}
			$click="";
			if($stat=='y'){				
				$click = "onclick=\"loaddatadetail('".$val['karyawanid']."','".$val['tahun']."','".$val['penilaian']."');\"";
			}
			$tab.="<tr class=rowcontent ".$ccc.">
					<td ".$clickx." align=center>".$no."</td>
					<td ".$clickx." align=center>".$val['tahun']."</td>
					<td ".$clickx." align=left>".$val['penilaian']." (".numToMonth($val['periodedr'],"I","long")." - ".numToMonth($val['periodesd'],"I","long").")</td>
					<td ".$clickx.">".getKary($val['karyawanid'],'nik')."</td>
					<td ".$clickx.">".getKary($val['karyawanid'],'namakaryawan')."</td>";
					
			// $tab.="<td ".$click.">".getNamaOrg(getHistKary($val['karyawanid'],$val['tahun'],'lokasitugas'))."</td>";
			// $tab.="<td ".$click.">".getNamaJabatan(getHistKary($val['karyawanid'],$val['tahun'],'kodejabatan'))."</td>";
			// $tab.="<td ".$click.">".getNamaDept(getHistKary($val['karyawanid'],$val['tahun'],'bagian'))."</td>";
			
			$tab.="<td ".$clickx.">".getNamaOrg(getKary($val['karyawanid'],'lokasitugas'))."</td>";
			$tab.="<td ".$clickx.">".getNamaJabatan(getKary($val['karyawanid'],'kodejabatan'))."</td>";
			$tab.="<td ".$clickx.">".getNamaDept(getKary($val['karyawanid'],'bagian'))."</td>";
			$tab.="<td ".$clickx.">".getKary($penilai)."</td>";
			$tab.="<td ".$clickx.">".getKary($atasanpenilai)."<br>".$arrstatus[$approval]."</td>";
			
			// $tab.="<td ".$click." align=center>".$kpi."<br><font style=font-size:9px;font-style:italic;>createby ".getKary($val['createdby'])."</font></td>";
			// $tab.="<td ".$click." align=center>".$cv."<br><font style=font-size:9px;font-style:italic;>".$creatcv."</font></td>";
			// $tab.="<td ".$click." align=center>".$mm."<br><font style=font-size:9px;font-style:italic;>".$creatmm."</font></td>";
			// $tab.="<td ".$click." align=center>".$pas."</td>";
			
			$tab.="<td style=font-style:italic;color:blue; onclick=\"detailkpi('".$val['id']."');\" align=center>".$totalproporsi[$val['id']]."</td>";
			if($cv!='Belum Input'){
				$tab.="<td style=font-style:italic;color:blue; onclick=\"detailcvmm('".$idcv."');\" align=center>".hidezerodecimal($ratacv,2)."</td>";
			}else{
				$tab.="<td align=center>".$cv."<br><font style=font-size:9px;font-style:italic;>".$creatcv."</font></td>";
			}
			if($mm!='Belum Input' and $mm!='Tidak'){
				$tab.="<td style=font-style:italic;color:blue; onclick=\"detailcvmm('".$idmm."');\" align=center>".hidezerodecimal($ratamm,2)."</td>";
			}else{
				$tab.="<td align=center>".$mm."<br><font style=font-size:9px;font-style:italic;>".$creatmm."</font></td>";
			}
			if($pas!='Belum Input'){
				$tab.="<td style=font-style:italic;color:blue; onclick=\"detail('".$val['karyawanid']."','".$val['tahun']."','".$val['penilaian']."');\" align=center>".hidezerodecimal($nilaifinal,2)."</td>";
			}else{
				$tab.="<td ".$click." align=center>".$pas."</td>";
			}
			
			$tab.="<td align=center style=font-size:10px;>".getNamaKaryawan($createdby)."<br>".tanggalnormald($createdtime)."</td>
				<td align=center style=font-size:10px;>".getNamaKaryawan($updateby)."<br>".tanggalnormald($lastupdate)."</td>";
				if($stat=='y' and $posting==0){
					$tab.="<td align=center colspan=2>
						<img src=images/application/application_go.png class=zImgBtn title='Edit Data' caption='Edit' onclick=\"loaddatadetail('".$val['karyawanid']."','".$val['tahun']."','".$val['penilaian']."');\">
					</td>";
				}elseif($stat=='y' and $posting==1 and $atasanpenilai==$_SESSION['standard']['userid'] and $approval==0){
					$tab.="<td align=center><button style=color:red;border-color:red; class=mybutton title='Verifikasi' onclick=\"loaddatadetail('".$val['karyawanid']."','".$val['tahun']."','".$val['penilaian']."');\">Verify</button></td>";

					$tab.="<td align=center><button style=color:green;border-color:green; class=mybutton title='Approve' onclick=\"approve('".$val['id']."');\">Approve</button></td>";
				}else{
					$tab.="<td align=center colspan=2></td>";
				}
				if(count($req)>0 and $posting==0 and $statcvmm=='y'){
					$tab.="<td align=center><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$val['id']."');\" ></td>";
				}elseif($posting==1){
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$icon="images/icons/04/16/04.png";
						$title="Unposting";
						$unpost=" onclick=\"unposting('".$val['id']."');\" ";
					}else {
						$icon="images/icons/04/16/02.png";
						$title="Posted";
						$unpost='';
					}
					$tab.="<td align=center><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
				}else{
					$tab.="<td align=center></td>";
				}
				
				$tab.="<td align=center>
					<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdf('".$val['karyawanid']."','".$val['tahun']."','".$val['penilaian']."');\">
				</td>
				<td align=center>
					<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detail('".$val['karyawanid']."','".$val['tahun']."','".$val['penilaian']."');\">
				</td>
			</tr>";
            $no += 1;
		}

		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		$tab .= "</tbody></table>";

		echo $tab;
	break;
	case 'reject':
		$data = array(
			'status'   => '2',
			'komentar'   => $param['komentar'],
			'tanggal'  => date("Y-m-d H:i:s")
		);
		$where = "notransaksi = '".$param['id']."' and jenispersetujuan='PAS' and karyawanid='".$_SESSION['standard']['userid']."'";
		$query = updateQuery($dbname,'approval',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
		
		$data = array(
			'approval'   => '2',
			'posting'   => '0',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['id']."'";
		$query = updateQuery($dbname,'sdm_pas',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	
	case 'approve':
		$data = array(
			'status'   => '1',
			'tanggal'  => date("Y-m-d H:i:s")
		);
		$where = "notransaksi = '".$param['id']."' and jenispersetujuan='PAS' and karyawanid='".$_SESSION['standard']['userid']."'";
		$query = updateQuery($dbname,'approval',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
		
		
		$data = array(
			'approval'   => '1',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['id']."'";
		$query = updateQuery($dbname,'sdm_pas',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case'posting':
		$str = "select * from ".$dbname.".sdm_pas where id='".$param['id']."'";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("Warning: Silahkan input Performance Appraisal Summary (PAS) terlebih dahulu.");
		}
		foreach($res as $val){
			if($val['nilaifinal']==0){
				exit("Warning: Silahkan input Performance Appraisal Summary (PAS) terlebih dahulu.");
			}
		}
		
		$str = "delete from ".$dbname.".approval WHERE notransaksi = '".$param['id']."' and jenispersetujuan='PAS'"; #exit("error".$str);
		$owlPDO->exec($str);
		
		$str = "SELECT atasanpenilai FROM ".$dbname.".sdm_pas WHERE id='".$param['id']."'";
		$res = fetchdata($str);
		$param['namaatasan']=$res[0]['atasanpenilai'];
		
		$data = array(
			'notransaksi'     => $param['id'],
			'jenispersetujuan'=> 'PAS',
			'level'           => '1',
			'karyawanid'      => $param['namaatasan'],
			'status'          => '0'
		);

		$queryH = insertQuery($dbname,'approval',$data,array_keys($data)); #exit("error".$queryH);
		$owlPDO->exec($queryH);
		
		$data = array(
			'posting'   => '1',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['id']."'";
		$query = updateQuery($dbname,'sdm_pas',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case'unposting':
		$data = array(
			'posting'   => '0',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['id']."'";
		$query = updateQuery($dbname,'sdm_pas',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case'detail':
		$str = "select * from ".$dbname.".sdm_kpi where karyawanid='".$param['karyawanid']."' and tahun='".$param['tahun']."' order by penilaian asc";
		$res = fetchdata($str);
		foreach($res as $val){
			$listp[$val['penilaian']]= $val['penilaian'];
		}
		
		$str = "select * from ".$dbname.".sdm_kpi where karyawanid='".$param['karyawanid']."' and tahun='".$param['tahun']."' and penilaian='".$param['penilaian']."' order by penilaian asc";
		$res = fetchdata($str);
		foreach($res as $val){
			$jabatan  = $val['jabatan'];
			$dept     = $val['dept'];
			$man      = $val['manmanagement'];
			$penilaian= $val['penilaian'];
			$periodedr= $val['periodedr'];
			$periodesd= $val['periodesd'];
			$idht     = $val['id'];
			$tanggal  = $val['tanggal'];
		}
		$namaman=array('Y'=>'YA','N'=>'TIDAK');
		
		if($param['tipeprint']=='pdf'){	
			$kodeorg= getHistKary($param['karyawanid'],$param['tahun'],'lokasitugas');
			$arrHead= setheadreport('',$kodeorg);
			$path   = $arrHead['logo'];
			
			$tab="<div>
				<table cellspacing=0 border=0 width=100% align=center style=\"font-family:sans-serif;font-size:12px;\">
					<tr>
						<td rowspan=3 valign=center style='font-weight:bold;width:100px'><img src='".$path."' height='60' /></td>
						<td style=font-weight:bold;>".$arrHead['nama']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['alamat']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['telepon']."</td>
					</tr>
				</table>
			<hr>
			<table cellspacing=0 border=0 width=100% style='text-align:center' style=\"font-family:sans-serif;\">
				<tr>
					<td style=font-weight:bold;font-size:30px;>Performance Appraisal Summary (Managerial)</td>
				</tr>
			</table><br>";
		}
		//$tab.="<fieldset style=float:left>";
		//$tab.="<legend>".$_SESSION['lang']['karyawan']."</legend>";
		//$tab.="<input hidden id=idht value=".$idht.">";
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=0 cellspacing=0 cellpadding=2 class=sortable style='font-size:13px;font-family:sans-serif;'>";
		}else{			
			$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>";
		}
		$tab.="<tr class=rowcontent>
					<td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
					<td style=min-width:200px><b>".getKary($param['karyawanid'],'namakaryawan')."</b></td>
					
					
					<td>".$_SESSION['lang']['nik2']."</td><td>:</td>
					<td style=min-width:200px>".getKary($param['karyawanid'],'nik')."</td>
				</tr>";
		$tab.="<tr class=rowcontent>
					<td>".$_SESSION['lang']['lokasitugas']."</td><td>:</td>
					<td>".getNamaOrg(getHistKary($param['karyawanid'],$param['tahun'],'lokasitugas'))."</td>
					
					
					<td>".$_SESSION['lang']['jabatan']."</td><td>:</td>
					<td>".getNamaJabatan($jabatan)."</td>
				</tr>";
		$tab.="<tr class=rowcontent>
					<td>".$_SESSION['lang']['departemen']."</td><td>:</td>
					<td>".getNamaDept($dept)."</td>
					
					
					<td>Man Management</td><td>:</td>
					<td>".$namaman[$man]."</td>
				</tr>";
		$tab.="</table>";
		// $tab.="</fieldset>";
		$tab.="<div style=clear:both></div><br>";
		
		if($penilaian=='Q1'){
			$prev="";
		}else{
			$nilaike = intval(substr($param['penilaian'],-1))-1;
			if($listp['Q'.$nilaike]!=''){
				$prev="<button class=mybutton onclick=\"loaddatadetail('".$param['karyawanid']."','".$param['tahun']."','Q".$nilaike."');\">< Prev Q".$nilaike."</button>";
			}else{
				$prev="";				
			}
		}
		if($penilaian=='Q4'){
			$next="";
		}else{
			$nilaike = intval(substr($param['penilaian'],-1))+1;
			if($listp['Q'.$nilaike]!=''){				
				$next="<button class=mybutton onclick=\"loaddatadetail('".$param['karyawanid']."','".$param['tahun']."','Q".$nilaike."');\">Q".$nilaike." Next ></button>";
			}else{
				$next="";				
			}
		}
		
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=1 cellspacing=0 cellpadding=2 class=sortable style='font-size:11px;font-family:sans-serif;'>";
		}else{			
			$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>";
		}
		$tab.="<thead>
					<tr class=rowheader>
						<th align=center rowspan=2 colspan=3>EVALUASI KOMPETENSI</th>
						<th align=center colspan=6>
								".$penilaian." ( ".strtoupper(numToMonth($periodedr,"I","short"))." - ".strtoupper(numToMonth($periodesd,"I","short"))." ".$param['tahun'].")
						</th>
						<th width=60px align=center rowspan=2>NILAI<br>KUMULATIF<br>SCORE</th>
						<th align=center colspan=2>UMPAN BALIK</th>
					</tr>
					<tr class=rowheader>
						<th width=55px align=center>BOBOT<br>(%)</th>
						<th width=55px align=center>NILAI-1</th>
						<th width=55px align=center>SCORE-1</th>
						<th width=55px align=center>NILAI-2</th>
						<th width=55px align=center>SCORE-2</th>
						<th width=55px align=center>TOTAL<BR>SCORE</th>
						<th align=center>KELEBIHAN KARYAWAN YANG DAPAT DIDAYAGUNAKAN</th>
						<th align=center>USULAN PENDAYAGUNAAN (COACHING/MENTORING/dst)</th>
					</tr>
				</thead>
				";
			
			$str = "select a.manmanagement, a.tahun, a.karyawanid, b.bobot, b.porsisendiri, 
			b.porsiatasan, c.nilaiatasan, c.proporsiatasan, 
			c.nilaisendiri, c.proporsisendiri, c.totalproporsi 
			from ".$dbname.".sdm_kpi a
			left join ".$dbname.".sdm_kpidt1 b on a.id=b.idht
			left join ".$dbname.".sdm_kpidt2 c on b.idkpi=c.iddt1
			where a.id='".$idht."'";
			$res = fetchdata($str);
			$count = 0;
			if(count($res)>0){						
				$count ++;
			}
			foreach($res as $val){
				$nilaiatasan    += $val['nilaiatasan'];
				$proporsiatasan += $val['proporsiatasan'];
				$nilaisendiri   += $val['nilaisendiri'];
				$proporsisendiri+= $val['proporsisendiri'];
				$totalproporsi  += $val['totalproporsi'];
				$man      		 = $val['manmanagement'];
			}
			if($man=='Y'){
				$adaanakbuah="1";
			}else{
				$adaanakbuah="2";
			}
			$str = "select * from ".$dbname.".sdm_5pms where tipe='".$adaanakbuah."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['kriteria']=='KPI'){
					$bobotkpi=$bar['persen'];
				}
				if($bar['kriteria']=='Core Values'){
					$bobotcore=$bar['persen'];
				}
				if($bar['kriteria']=='Man Management'){
					$bobotman=$bar['persen'];
				}
			}
			
			$nilaike = substr($param['penilaian'],-1);
			if($nilaike>1){
				$range = range(1,($nilaike-1));
				foreach($range as $penilai){
					$str = "select a.manmanagement, a.tahun, a.karyawanid, b.bobot, b.porsisendiri, 
					b.porsiatasan, c.nilaiatasan, c.proporsiatasan, 
					c.nilaisendiri, c.proporsisendiri, c.totalproporsi 
					from ".$dbname.".sdm_kpi a
					left join ".$dbname.".sdm_kpidt1 b on a.id=b.idht
					left join ".$dbname.".sdm_kpidt2 c on b.idkpi=c.iddt1
					where a.karyawanid='".$param['karyawanid']."' and 
					a.tahun='".$param['tahun']."' and a.penilaian='Q".$penilai."'";
					$res = fetchdata($str);
					if(count($res)>0){						
						$count ++;
					}
					foreach($res as $bar){
						$proporsiatasanold[$penilai]  += $bar['proporsiatasan'];
						$proporsisendiriold[$penilai] += $bar['proporsisendiri'];
						$man      		  			   = $bar['manmanagement'];
						
						#$nilaiatasanold[$penilai]    += $bar['nilaiatasan'];
						#$nilaisendiriold[$penilai]   += $bar['nilaisendiri'];
						#$totalproporsiold[$penilai]  += $bar['totalproporsi'];
					}
					
					if($man=='Y'){
						$adaanakbuah="1";
					}else{
						$adaanakbuah="2";
					}
					$str = "select * from ".$dbname.".sdm_5pms where tipe='".$adaanakbuah."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						if($bar['kriteria']=='KPI'){
							$bobotkpiold[$penilai]=$bar['persen'];
						}
						if($bar['kriteria']=='Core Values'){
							$bobotcoreold[$penilai]=$bar['persen'];
						}
						if($bar['kriteria']=='Man Management'){
							$bobotmanold[$penilai]=$bar['persen'];
						}
					}
					$scoreatasanold+=$proporsiatasanold[$penilai]*($bobotkpiold[$penilai]/100);
					$scoresendiriold+=$proporsisendiriold[$penilai]*($bobotkpiold[$penilai]/100);
				}
			}
			
			
			$scoreatasan=$proporsiatasan*($bobotkpi/100);
			$scoresendiri=$proporsisendiri*($bobotkpi/100);
			
			$str = "select * from ".$dbname.".sdm_pas where id='".$idht."'";
			$res = fetchdata($str);
			foreach($res as $val){
				$kelebihan      = $val['kelebihan'];
				$usulankelebihan= $val['usulankelebihan'];
				$pica           = $val['pica'];
				$kelemahan      = $val['kelemahan'];
				$usulankelemahan= $val['usulankelemahan'];
				$catatankary    = $val['catatankary'];
				$kehadiran      = $val['kehadiran'];
				$atasanpenilai  = $val['atasanpenilai'];
				$karypenilai    = $val['penilai'];
				
				$nilaifinal     = $val['nilaifinal'];
				$kategorifinal  = $val['kategorifinal'];
			}
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center width=30px>1</td>
				<td colspan=2>PENILAIAN HASIL KERJA (KPI)</td>
				<td align=center>".hidezerodecimal($bobotkpi,2)."</td>
				<td align=center>".hidezerodecimal($proporsiatasan,2)."</td>
				<td align=center>".hidezerodecimal($scoreatasan,2)."</td>
				<td align=center>".hidezerodecimal($proporsisendiri,2)."</td>
				<td align=center>".hidezerodecimal($scoresendiri,2)."</td>
				<td align=center>".hidezerodecimal($scoresendiri+$scoreatasan,2)."</td>
				<td align=center>".hidezerodecimal(($scoreatasan+$scoreatasanold+$scoresendiri+$scoresendiriold)/$count,2)."</td>
				<td rowspan=6>".nl2br($kelebihan)."</td>
				<td rowspan=6>".nl2br($usulankelebihan)."</td>
				</tr>";
			$kumulkpi=($scoreatasan+$scoreatasanold+$scoresendiri+$scoresendiriold)/$count;	
			
			
			$maxpenilai="and penilai in (select max(b.penilai) as penilai
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='corevalue')";
			
			$str = "select sum(b.nilai) as jumlah, avg(b.nilai) as rata
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='corevalue' ".$maxpenilai."";
			$res = fetchdata($str);
			foreach($res as $val){
				$jlhcorevalue=$val['jumlah'];
				$avgcorevalue=$val['rata'];
			}
			
			if($nilaike>1){
				$range = range(1,($nilaike-1));
				foreach($range as $penilai){
					$str = "select sum(b.nilai) as jumlah, avg(b.nilai) as rata
					from ".$dbname.".sdm_corevalueandmanmanagement a
					left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
					where a.karyawanid='".$param['karyawanid']."' and 
					a.tahun='".$param['tahun']."' and a.penilaian='Q".$penilai."'
					and a.jenis='corevalue'";
					$res = fetchdata($str);
					foreach($res as $val){
						$jlhcorevalueold[$penilai]=$val['jumlah'];
						$avgcorevalueold[$penilai]=$val['rata'];
					}
					$corevalatasanold+=$avgcorevalueold[$penilai]*($bobotcoreold[$penilai]/100);
				}
			}
			$corevalatasan=$avgcorevalue*($bobotcore/100);
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>2</td>
				<td colspan=2>PENILAIAN KSP AGRO<br>CORE VALUES (FASTER)</td>
				<td align=center>".hidezerodecimal($bobotcore,2)."</td>
				<td align=center>".hidezerodecimal($avgcorevalue,2)."</td>
				<td align=center>".hidezerodecimal($corevalatasan,2)."</td>
				<td align=center>0</td>
				<td align=center>0</td>
				<td align=center>".hidezerodecimal($corevalatasan,2)."</td>
				<td align=center>".hidezerodecimal(($corevalatasan+$corevalatasanold)/$count,2)."</td>
				</tr>";
			$kumulcore=($corevalatasan+$corevalatasanold)/$count;
			
			$maxpenilai="and penilai in (select max(b.penilai) as penilai
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='manmanagement')";
			
			$str = "select sum(b.nilai) as jumlah, avg(b.nilai) as rata
			from ".$dbname.".sdm_corevalueandmanmanagement a
			left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
			where a.karyawanid='".$param['karyawanid']."' and 
			a.tahun='".$param['tahun']."' and a.penilaian='".$param['penilaian']."'
			and a.jenis='manmanagement' ".$maxpenilai."";
			$res = fetchdata($str);
			foreach($res as $val){
				$jlhman=$val['jumlah'];
				$avgman=$val['rata'];
			}
			if($nilaike>1){
				$range = range(1,($nilaike-1));
				foreach($range as $penilai){
					$str = "select sum(b.nilai) as jumlah, avg(b.nilai) as rata
					from ".$dbname.".sdm_corevalueandmanmanagement a
					left join ".$dbname.".sdm_corevalueandmanmanagement_dt b on a.id=b.id
					where a.karyawanid='".$param['karyawanid']."' and 
					a.tahun='".$param['tahun']."' and a.penilaian='Q".$penilai."'
					and a.jenis='manmanagement'";
					$res = fetchdata($str);
					foreach($res as $val){
						$jlhmanold[$penilai]=$val['jumlah'];
						$avgmanold[$penilai]=$val['rata'];
					}
					$manatasanold+=$avgmanold[$penilai]*($bobotmanold[$penilai]/100);
				}
			}
			$manatasan=$avgman*($bobotman/100);
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>3</td>
				<td colspan=2>PENILAIAN PENGELOLAAN ORANG</td>
				<td align=center>".hidezerodecimal($bobotman,2)."</td>
				<td align=center>".hidezerodecimal($avgman,2)."</td>
				<td align=center>".hidezerodecimal($manatasan,2)."</td>
				<td align=center>0</td>
				<td align=center>0</td>
				<td align=center>".hidezerodecimal($manatasan,2)."</td>
				<td align=center>".hidezerodecimal(($manatasan+$manatasanold)/$count,2)."</td>
				</tr>";
				$kumulman=($manatasan+$manatasanold)/$count;
				$ttlscore=$manatasan+$corevalatasan+$scoresendiri+$scoreatasan;
				
				$ttlkumul=$kumulkpi+$kumulcore+$kumulman;
				
			$tab.="<tr class=rowcontent style=vertical-align:top;font-weight:bold;>
				<td align=center></td>
				<td colspan=2>PERFORMANCE VALUE</td>
				<td align=center>".hidezerodecimal($bobotman+$bobotcore+$bobotkpi,2)."</td>
				<td align=center>".hidezerodecimal($avgman+$avgcorevalue+$proporsiatasan,2)."</td>
				<td align=center>".hidezerodecimal($manatasan+$corevalatasan+$scoreatasan,2)."</td>
				<td align=center>".hidezerodecimal($proporsisendiri,2)."</td>
				<td align=center>".hidezerodecimal($scoresendiri,2)."</td>
				<td align=center>".hidezerodecimal($ttlscore,2)."</td>
				<td align=center>".hidezerodecimal($ttlkumul,2)."</td>
				</tr>";	
			
			if($ttlscore<61){
				$n="KURANG";
			}elseif($ttlscore>=61 and $ttlscore<81){
				$n="CUKUP";
			}elseif($ttlscore>=81 and $ttlscore<91){
				$n="BAIK";
			}elseif($ttlscore>=91 and $ttlscore<110){
				$n="SANGAT BAIK";
			}elseif($ttlscore>110){
				$n="LUAR BIASA";
			}
			
			if($ttlkumul<61){
				$e="KURANG";
			}elseif($ttlkumul>=61 and $ttlkumul<81){
				$e="CUKUP";
			}elseif($ttlkumul>=81 and $ttlkumul<91){
				$e="BAIK";
			}elseif($ttlkumul>=91 and $ttlkumul<110){
				$e="SANGAT BAIK";
			}elseif($ttlkumul>110){
				$e="LUAR BIASA";
			}
			
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center></td>
				<td colspan=2><b>KATEGORI PENILAIAN</b></td>
				<td align=center colspan=6><b>".$n."</b></td>
				<td align=center><b>".$e."</b></td>
				</tr>";	

			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center></td>
				<td colspan=2><b>PERFORMANCE VALUE FINAL</b></td>
				<td align=left colspan=4><i>".getKary($atasanpenilai)."</></td>
				<td align=center colspan=2><b>".$kategorifinal."</b></td>
				<td align=center><b>".$nilaifinal."</b></td>
				</tr>";	
			
			
			
			$tab.="<thead><tr class=rowheader style=vertical-align:top;>
				<th align=center colspan=10>CATATAN PENILAI (PICA KINERJA)</th>
				<th align=center>KELEMAHAN KARYAWAN YANG DAPAT DIKEMBANGKAN</th>
				<th align=center>USULAN PENGEMBANGAN (COACHING/MENTORING/dst)</th>
				</tr></thead>";
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=left colspan=10>".nl2br($pica)."</td>
				<td align=left>".nl2br($kelemahan)."</td>
				<td align=left>".nl2br($usulankelemahan)."</td>
				</tr>";
			
			$tab.="<thead><tr class=rowheader style=vertical-align:top;>
				<th align=center colspan=10>CATATAN KARYAWAN YANG DINILAI</th>
				<th align=center>CATATAN KEHADIRAN SELAMA PERIODE PENILAIAN</th>
				<th align=center>FREKUENSI/JUMLAH HARI</th>
				</tr></thead>";
			
			if($kehadiran!=''){
				$default=$kehadiran;
			}else{				
				$default="Sakit : \nIjin : \nAlpa : \nTerlambat : \nIjin Pulang Awal : ";
			}
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=left colspan=10>".nl2br($usulankelemahan)."</td>
				<td align=left colspan=2>".nl2br($default)."</td>
				</tr>";
				
			$tab.="<thead><tr class=rowheader style=vertical-align:top;>
				<th align=center colspan=3>KRITERIA PENILAIAN</th>
				<th align=center colspan=7>ATASAN PENILAI</th>
				<th align=center>PENILAI</th>
				<th align=center>KARYAWAN YANG DINILAI</th>
				</tr></thead>";

			$default="Kurang : 0 - 60<br>Cukup :61 - 80<br>Baik : 81 - 90<br>Sangat Baik : 91 - 110<br>Luar Biasa : 110 - 120";	
			$tab.="<tr class=rowcontent>
				<td align=center>1</td>
				<td align=left>Kurang</td>
				<td align=center width=60px>0 - 60</td>
				<td align=center colspan=7 rowspan=4></td>
				<td align=center rowspan=4></td>
				<td rowspan=4></td>
				</tr>";
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>2</td>
				<td align=left>Cukup</td>
				<td align=center>61 - 80</td>
				</tr>";
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>3</td>
				<td align=left>Baik</td>
				<td align=center>81 - 90</td>
				</tr>";	
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>4</td>
				<td align=left>Sangat Baik</td>
				<td align=center>91 - 110</td>
				</tr>";
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>
				<td align=center>5</td>
				<td align=left>Luar Biasa</td>
				<td align=center>110 - 120</td>
				<td align=center colspan=7><b>".getKary($atasanpenilai,'namakaryawan')."</b></td>
				<td align=center><b>".getKary($karypenilai,'namakaryawan')."</b></td>
				<td align=center><b>".getKary($param['karyawanid'],'namakaryawan')."</b></td>
				</tr>";	
				
			$tab.="</table>";	
		
		if($param['tipeprint']=='pdf'){
			// $nop = "detail_bkm.xls";
			// $xls = new HtmlExcel();
			// $xls->setCss($css);
			// $xls->addSheet("detail_bkm", $tab);
			// $xls->headers($nop);
			// echo $xls->buildFile();
			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$customPaper = array(0,0,1050,650);
			$dompdf->set_paper($customPaper);
			//$dompdf->setPaper('legal', 'landscape');
			$dompdf->render();
			$dompdf->stream("pas", array("Attachment" => false));
		}else{			
			echo $tab;
		}
	break;
}
