<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$param['ttljjg'] = str_replace(',','',$param['ttljjg']);
$param['ttlkg'] = str_replace(',','',$param['ttlkg']);

$nmorg= makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

switch ($method) {
	case'del':
		try{
		$owlPDO->beginTransaction();
			$wh="";
			if($param['blok']!=''){
				$wh="and kodeblok='".$param['blok']."'";				
			}
			$str="delete from ".$dbname.".bgt_produksi_kebun_parameter  where tahunbudget='".$param['tahun']."' and kodeunit='".$param['kodeorg']."' ".$wh."";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'posting':
		try{
		$owlPDO->beginTransaction();
			$str = "select * from " . $dbname . ".bgt_produksi_kebun_parameter where tahunbudget = '".$param['tahun']."' and kodeunit='".$param['kodeorg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$totalkg[$bar['kodeblok']]+=$bar['totalkg'];
				$totaljjg[$bar['kodeblok']]+=$bar['totaljjg'];
			}
			
			$str = "select * from ".$dbname.".bgt_budget where tahunbudget='".$param['tahun']."' and kodeorg like '".$param['kodeorg']."%' and satuanv in ('JJG','KG') and substr(noakun,1,5) in ('61101','61102','61103')";
			$res = fetchdata($str);
			foreach($res as $bar){
				if(strtolower($bar['satuanv'])=='kg'){					
					$pres=$totalkg[$bar['kodeorg']];
				}else{
					$pres=$totaljjg[$bar['kodeorg']];
				}
				
				$str = "update " . $dbname . ".bgt_budget set volume='".$pres."' where kunci = '".$bar['kunci']."'"; #exit("error".$str);
				$owlPDO->exec($str);
			}
			
			
			$str = "update " . $dbname . ".bgt_produksi_kebun_parameter set tutup='1' where tahunbudget = '".$param['tahun']."' and kodeunit='".$param['kodeorg']."'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'unposting':
		try{
		$owlPDO->beginTransaction();
			
			$str = "update " . $dbname . ".bgt_produksi_kebun_parameter set tutup='0' where tahunbudget = '".$param['tahun']."' and kodeunit='".$param['kodeorg']."'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'simpan':
		try{
			$owlPDO->beginTransaction();
			
			if($param['tahun']==''){
				throw new PDOException("Tahun budget wajib diisi.");
			}
			
			if(strlen($param['tahun'])<'4'){
				throw new PDOException("Tahun budget salah.");
			}
			
			$str = "select * from ".$dbname.".bgt_produksi_kebun_parameter where tahunbudget='".$param['tahun']."' and kodeunit='".$param['kodeorg']."' and tutup='1'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Budget sudah ditutup.");				
			}
			
			if($param['jenisbudget']==''){
				throw new PDOException("Jenis Budget Wajib diisi!");
			}
			
			for($i=1;$i<=12;$i++){
				if($param['jjg'][$i]==''){$param['jjg'][$i]=0;}
				if($param['kg'][$i]==''){$param['kg'][$i]=0;}
				if($param['kgbruto'][$i]==''){$param['kgbruto'][$i]=0;}
				
				$ttljjgsebar+=$param['jjg'][$i];
				$ttlkgsebar+=$param['kg'][$i];
				$ttlkgbrutosebar+=$param['kgbruto'][$i];
			}

			if(round($param['ttljjg'])!=round($ttljjgsebar)){
				throw new PDOException("Jumlah Jjg sebaran tidak sama dengan Jjg total, selisih : ".(round($param['ttljjg'])-round($ttljjgsebar))." Jjg");
			}
			
			if(round($param['ttlkg'])!=round($ttlkgsebar)){
				throw new PDOException("Jumlah Kg sebaran tidak sama dengan Kg total, selisih : ".(round($param['ttlkg'])-round($ttlkgsebar))." Kg");
			}
			
			if(round($param['ttlkgbruto'])!=round($ttlkgbrutosebar)){
				throw new PDOException("Jumlah Kg Bruto Grading sebaran tidak sama dengan Kg Bruto Grading total, selisih : ".(round($param['ttlkgbruto'])-round($ttlkgbrutosebar))." Kg Bruto");
			}
			
			$str="select * from ".$dbname.".bgt_produksi_kebun_parameter where tahunbudget='".$param['tahun']."' and kodeblok='".$param['blok']."'";
			$res=fetchdata($str);
			if(count($res)>0){
				$str="delete from ".$dbname.".bgt_produksi_kebun_parameter  where tahunbudget='".$param['tahun']."' and kodeblok='".$param['blok']."'";
				$owlPDO->exec($str);
			}
			
			$ip = makeOption($dbname,'bgt_blok','kodeblok,intiplasma',"kodeblok='".$param['blok']."' and tahunbudget='".$param['tahun']."'");
			
			if($param['ttljjg']>0 and $param['ttlkg']>0){				
				$str="insert into ".$dbname.".bgt_produksi_kebun_parameter (`tahunbudget`, `kodeunit`, `kodeblok`, `tahuntanam`, `intiplasma`, `jenisbudget`, `jjgperpkk`, `totaljjg`, `totalkg`, `totalkgbruto`, `updateby`";
				for($i=1;$i<=12;$i++){
					$str.=",`jjg".addZero($i,2)."`";
					$str.=",`kg".addZero($i,2)."`";
					$str.=",`kgbruto".addZero($i,2)."`";
				}
				$str.=") values('".$param['tahun']."','".$param['kodeorg']."','".$param['blok']."','".$param['tt']."','".$ip[$param['blok']]."','".$param['jenisbudget']."','".$param['ttlkg']/$param['ttljjg']."','".$param['ttljjg']."','".$param['ttlkg']."','".$param['ttlkgbruto']."','".$_SESSION['standard']['userid']."'";
				for($i=1;$i<=12;$i++){
					$str.=",'".$param['jjg'][$i]."'";
					$str.=",'".$param['kg'][$i]."'";
					$str.=",'".$param['kgbruto'][$i]."'";
				}
				$str.=");";
				
				$owlPDO->exec($str);
			}
				
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
		
	break;
	case'getblok':
		
		$blok="<option value=''>".$_SESSION['lang']['all']."</option>";
		$where="";
		if($param['kodeorg']!=''){
			$where.=" and kodeblok like '".$param['kodeorg']."%'";
		}
		if($param['divisi']!=''){
			$where.=" and kodeblok like '".$param['divisi']."%'";
		}
		
		$str="select distinct kodeblok from ".$dbname.".bgt_blok where thntnm like '".$param['tt']."%' ".$where." and closed='1' and tahunbudget='".$param['tahun']."' and statusblok in ('TM','TBM') order by kodeblok asc"; 
		$res=fetchdata($str);
		foreach($res as $bar){
			$blok.="<option value='".$bar['kodeblok']."'>".$nmorg[$bar['kodeblok']]."</option>";
		}

		echo $blok;
	break;
	case'getttblok':
		if($param['tahun']=='' or strlen($param['tahun'])<4){
			exit("Warning : Tahun budget wajib diisi.");
		}
		$tt=$blok="<option value=''>".$param['bahasa']."</option>";
		$str="select distinct tahuntanam from ".$dbname.".setup_blok where kodeorg like '".$param['kodeorg']."%' order by tahuntanam asc"; 
		$res=fetchdata($str);
		foreach($res as $bar){
			$tt.="<option value='".$bar['tahuntanam']."'>".$bar['tahuntanam']."</option>";
		}
		
		$str="select distinct kodeblok from ".$dbname.".bgt_blok where kodeblok like '".$param['kodeorg']."%' and tahunbudget='".$param['tahun']."' and closed='1'  and statusblok in ('TM','TBM') order by kodeblok asc"; 
		$res=fetchdata($str);
		foreach($res as $bar){
			$blok.="<option value='".$bar['kodeblok']."'>".$nmorg[$bar['kodeblok']]."</option>";
		}

		echo $tt."####".$blok;
	break;
	case'getdivttblok':
		if($param['tahun']=='' or strlen($param['tahun'])<4){
			exit("Warning : Tahun budget wajib diisi.");
		}
		$div=$tt=$blok="<option value=''>".$param['bahasa']."</option>";
		$div.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='AFDELING' and induk ='".$param['kodeorg']."' ",'2','0',true);

		if($param['kodeorg']!=''){
			$whr="and induk='".$param['kodeorg']."'";	
		}

		$str="select distinct tahuntanam from ".$dbname.".setup_blok a left join ".$dbname.".organisasi b on left(a.kodeorg,6)=b.kodeorganisasi where b.tipe='AFDELING' and tahuntanam!=0 ".$whr." order by tahuntanam asc"; 
		$res=fetchdata($str);
		foreach($res as $bar){
			$tt.="<option value='".$bar['tahuntanam']."'>".$bar['tahuntanam']."</option>";
		}
		
		$str="select distinct kodeblok from ".$dbname.".bgt_blok where kodeblok like '".$param['kodeorg']."%' and tahunbudget='".$param['tahun']."'  and closed='1'  and statusblok in ('TM','TBM') order by kodeblok asc"; 
		$res=fetchdata($str);
		foreach($res as $bar){
			$blok.="<option value='".$bar['kodeblok']."'>".$nmorg[$bar['kodeblok']]."</option>";
		}

		echo $div."####".$tt."####".$blok;
	break;
	case'adddata':
		if($param['tahun']=='' or strlen($param['tahun'])<4){
			exit("Warning : Periksa Tahun Budget.");
		}
		if($param['kodeorg']==''){
			exit("Warning : Kode organisasi wajib diisi.");
		}
        $where = $wh = "";
		if($param['tahun']!=''){
			$where.=" and tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeblok like '".$param['kodeorg']."%'";
		}
		if($param['divisi']!=''){
			$where.=" and kodeblok like '".$param['divisi']."%'";
		}
		if($param['blok']!=''){
			$where.=" and kodeblok like '".$param['blok']."%'";
		}
		if($param['tt']!=''){
			$wh.=" and thntnm = '".$param['tt']."'";
		}
		$wh.=" and statusblok in ('TM','TBM') and closed='1'";
		$bulan=range(1,12);
		$colspan=25;
		
		
        $tab = "";
		$data=$dtblok=array();
		$str="select * from ".$dbname.".bgt_blok where 1=1 ".$where." ".$wh." order by substr(kodeblok,1,6) asc, thntnm asc,kodeblok asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$dtblok[$bar['kodeblok']]=$bar['kodeblok'];
			$data[$bar['kodeblok']]['tt']=$bar['thntnm'];
			$data[$bar['kodeblok']]['ha']=$bar['hathnini'];
			$data[$bar['kodeblok']]['pkk']=$bar['pokokthnini'];
		}
		
		$str="select * from ".$dbname.".bgt_produksi_kebun_parameter where 1=1 ".$where." and tutup='1'";
		$res=fetchdata($str);
		if(count($res)>0){
			echo"<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center;color:red;font-size:15px;'>Budget produksi sudah ditutup.</td></tr>";
			exit();
		}
		
		if($param['sumber']=='upload'){
			$dt = json_decode($param['data']);
			$jlhrow = count($dt)-1;
			
			for($i=1;$i<=$jlhrow;$i++){				
				for($n=1;$n<=12;$n++){
					if(trim($dt[$i][0])!=''){						
						$data[$dt[$i][0]][$n]['kg']=$dt[$i][$n+1];
						$data[$dt[$i][0]][$n]['kgbruto']=$dt[$i][$n+1];
						$data[$dt[$i][0]][$n]['jjg']=$dt[$i][$n+13];
						
						$kgbln[$n]+=$dt[$i][$n+1];
						$kgbrutobln[$n]+=$dt[$i][$n+1];
						
						$data[$dt[$i][0]]['ttlkg']+=$dt[$i][$n+1];
						$data[$dt[$i][0]]['ttlkgbruto']+=$dt[$i][$n+1];
						$data[$dt[$i][0]]['ttljjg']+=$dt[$i][$n+13];

						$totalkg+=$dt[$i][$n+1];
						$totalkgbruto+=$dt[$i][$n+1];
					}
				}
			}
		}else{			
			$str="select * from ".$dbname.".bgt_produksi_kebun_parameter where 1=1 ".$where." order by substr(kodeblok,1,6) asc,tahuntanam asc,kodeblok asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				#$dtblok[$bar['kodeblok']]=$bar['kodeblok'];
				$data[$bar['kodeblok']]['ttljjg']=$bar['totaljjg'];
				$data[$bar['kodeblok']]['ttlkg']=$bar['totalkg'];
				$data[$bar['kodeblok']]['ttlkgbruto']=$bar['totalkgbruto'];
				$totalkg=$bar['totalkg'];
				foreach($bulan as $bln){				
					$kgbln[$bln]=$bar['kg'.addZero($bln,2)];
					$data[$bar['kodeblok']][$bln]['kg']=$bar['kg'.addZero($bln,2)];
					$data[$bar['kodeblok']][$bln]['kgbruto']=$bar['kgbruto'.addZero($bln,2)];
					$data[$bar['kodeblok']][$bln]['jjg']=$bar['jjg'.addZero($bln,2)];
				}
			}
		}
		
		if(count($dtblok) > 0){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=12 align=right style='font-weight:bold;color:blue;background-color:#D2FFD2;'>Isikan persentase sebaran bulanan >> </td>";
			$persen=0;
			foreach($bulan as $bln){
				if($totalkg>0){$persen=$kgbln[$bln]/$totalkg*100;}
				$tab.="<td align=center style=background-color:#D2FFD2;><input onkeyup=\"hitungsebaran('','".$bln."')\" type=text title='Isikan persen sebaran' class=myinputtextnumber id=persen_".$bln." onkeypress=\"return angka_doang(event);\" style=width:50px;border-color:blue;background-color:#D2FFD2; value=".hidezerodecimal($persen,2)."></td>";
			}
			$tab.="<td align=center style=background-color:#D2FFD2;><button class=mybutton onclick=hapuspersen() title=\"Hapus persen\">Del</button></td>";
			$tab.="</tr>";
			$rowspan="rowspan=3";
			$no=0;
			foreach($dtblok as $blok){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td ".$rowspan." style='text-align:center'>".$no."</td>";
				$tab.="<td ".$rowspan." align=center>".substr($blok,0,6)."</td>";
				$tab.="<td ".$rowspan." align=center id=tt_".$no.">".$data[$blok]['tt']."</td>";
				$tab.="<td ".$rowspan." align=center hidden id=blok_".$no.">".$blok."</td>";
				$tab.="<td ".$rowspan." align=center >".$nmorg[$blok]."</td>";
				$tab.="<td ".$rowspan." align=center>".hidezerodecimal($data[$blok]['ha'],2)."</td>";
				$tab.="<td ".$rowspan." align=center>".hidezerodecimal($data[$blok]['pkk'])."</td>";
				$tab.="<td ".$rowspan." align=center>".@hidezerodecimal($data[$blok]['pkk']/$data[$blok]['ha'],2)."</td>";
				$tab.="<td ".$rowspan." align=center><input onkeyup=\"hitungsebaran('".$no."','')\" onblur=\"z.numberFormat('jjg_".$no."',2)\" type=text title='Isikan Total Janjang' class=myinputtextnumber id=jjg_".$no." onkeypress=\"return angka_doang(event);\" style=width:50px; value=".hidezerodecimal($data[$blok]['ttljjg'])."></td>";
				$tab.="<td ".$rowspan." align=center><input onkeyup=\"hitungsebaran('".$no."','')\" onblur=\"z.numberFormat('kg_".$no."',2)\" type=text title='Isikan Total Kg' class=myinputtextnumber id=kg_".$no." onkeypress=\"return angka_doang(event);\" style=width:55px; value=".hidezerodecimal($data[$blok]['ttlkg'],5)."></td>";
				$tab.="<td ".$rowspan." align=center><input onkeyup=\"hitungsebaran('".$no."','')\" onblur=\"z.numberFormat('kgbruto_".$no."',2)\" type=text title='Isikan Total Kg' class=myinputtextnumber id=kgbruto_".$no." onkeypress=\"return angka_doang(event);\" style=width:55px; value=".hidezerodecimal($data[$blok]['ttlkgbruto'],5)."></td>";
				$tab.="<td ".$rowspan." align=center id=bjr_".$no.">".@hidezerodecimal($data[$blok]['ttlkg']/$data[$blok]['ttljjg'],2)."</td>";
				
				// $tab.="<td style='text-align:center'>Jjgtes</td>";
				$tab.="<td style='text-align:center'>Jjg</td>";
				
				$varjjg = $varkg = $ttljjgsebar = $ttlkgsebar = 0;
				foreach($bulan as $bln){
					$tab.="<td style='text-align:center'>
					<input onkeyup=\"z.numberFormat('jjg_".$no."_".$bln."',2)\" type=text  class=myinputtextnumber id=jjg_".$no."_".$bln." onkeypress=\"return angka_doang(event);\" style=width:50px; value=".hidezerodecimal($data[$blok][$bln]['jjg']).">
					</td>";
					$ttljjgsebar+=$data[$blok][$bln]['jjg'];
					$ttlkgsebar+=$data[$blok][$bln]['kg'];
					$ttlkgbrutosebar+=$data[$blok][$bln]['kgbruto'];
				}
				
				$varjjg = $data[$blok]['ttljjg']-$ttljjgsebar;
				$varkg = $data[$blok]['ttlkg']-$ttlkgsebar;
				$varkgbruto = $data[$blok]['ttlkgbruto']-$ttlkgbrutosebar;
				$s="";
				if($varjjg !=0 or $varkg!=0 or $varkgbruto!=0){
					$s="style=background-color:red;";
				}
				
				$tab.="<td ".$rowspan." align=center id=btnsave_".$no." ".$s."><button class=mybutton onclick=simpan('".$no."') title=\"Save\">Save</button></td>";
				

				$tab.="</tr>";
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>Kg</td>";
				foreach($bulan as $bln){				
					$tab.="<td style='text-align:center'>
					<input onkeyup=\"z.numberFormat('kg_".$no."_".$bln."',2)\" type=text  class=myinputtextnumber id=kg_".$no."_".$bln." onkeypress=\"return angka_doang(event);\" style=width:50px; value=".hidezerodecimal($data[$blok][$bln]['kg'],5).">
					</td>";
				}
				
				$tab.="</tr>";

				# ================================================= #
				# Kg Grading (Bruto)
				# ================================================= #
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>Kg Bruto <br/> (Grading)</td>";
				foreach($bulan as $bln){				
					$tab.="<td style='text-align:center'>
					<input onkeyup=\"z.numberFormat('kgbruto_".$no."_".$bln."',2)\" type=text  class=myinputtextnumber id=kgbruto_".$no."_".$bln." onkeypress=\"return angka_doang(event);\" style=width:50px; value=".hidezerodecimal($data[$blok][$bln]['kgbruto'],5).">
					</td>";
				}
				
				$tab.="</tr>";
			}
			
			$tab.="<tr class='rowcontent'><td hidden><input hidden id=awalbaris value='1'><input hidden id=ttlbaris value=".$no."></td>
					<td colspan=".($colspan)." align=right style=background-color:#71FFFC;>
						<button class=mybutton onclick=saveall('".$no."') title=\"Save Seluruhnya\">SaveAll</button></td>
					</tr>";
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>Silahkan input dan posting terlebih dahulu data blok budget melalui menu : Anggaran - Transaksi - Budget Kebun - Blok Anggaran</td></tr>";
		}
		
		echo $tab;
	break;
	case'showposting':
		$jab = getPostingJabatan('budget');
		$where = "";
		if($param['tahun']!=''){
			$where.=" and tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeunit = '".$param['kodeorg']."'";
		}
		
        $tab = "";
		$bulan=range(1,12);
		$dtunit=array();
        $str="select * from ".$dbname.".bgt_produksi_kebun_parameter where 1=1 ".$where." and substr(kodeunit,1,4) in (".getOrgDetail(2).") order by tahunbudget desc,kodeunit asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$dtunit[$bar['tahunbudget']][$bar['kodeunit']]=$bar['kodeunit'];
			$data[$bar['tahunbudget']][$bar['kodeunit']]['ttljjg']+=$bar['totaljjg'];
			$data[$bar['tahunbudget']][$bar['kodeunit']]['ttlkg']+=$bar['totalkg'];
			$data[$bar['tahunbudget']][$bar['kodeunit']]['ttlkgbruto']+=$bar['totalkgbruto'];
			$data[$bar['tahunbudget']][$bar['kodeunit']]['post']=$bar['tutup'];
			foreach($bulan as $bln){				
				$data[$bar['tahunbudget']][$bar['kodeunit']][$bln]['kg']+=$bar['kg'.addZero($bln,2)];
				$data[$bar['tahunbudget']][$bar['kodeunit']][$bln]['kgbruto']+=$bar['kgbruto'.addZero($bln,2)];
				$data[$bar['tahunbudget']][$bar['kodeunit']][$bln]['jjg']+=$bar['jjg'.addZero($bln,2)];
			}

			$s="select sum(hathnini) as hathnini, sum(pokokthnini) as pokokthnini from ".$dbname.".bgt_blok where substr(kodeblok,1,4)='".$bar['kodeunit']."' and tahunbudget='".$bar['tahunbudget']."' group by substr(kodeblok,1,4)";
			$r=fetchdata($s);
			foreach($r as $b){
				$data[$bar['tahunbudget']][$bar['kodeunit']]['ha']=$b['hathnini'];
				$data[$bar['tahunbudget']][$bar['kodeunit']]['pkk']=$b['pokokthnini'];
			}
		}
		
		
		if(count($dtunit) > 0){
			$rowspan="rowspan=3";
			$no=0;
			foreach($dtunit as $tahun => $vunit){
				foreach($vunit as $unit){					
					$no++;
					$tab.="<tr class='rowcontent'>";
					$tab.="<td ".$rowspan." style='text-align:center'>".$no."</td>";
					$tab.="<td ".$rowspan." align=center>".$tahun."</td>";
					$tab.="<td ".$rowspan." align=left>".$unit." - ".$nmorg[$unit]."</td>";
					$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit]['ha'],2)."</td>";
					$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit]['pkk'])."</td>";
					$tab.="<td ".$rowspan." align=right>".@hidezerodecimal($data[$tahun][$unit]['pkk']/$data[$tahun][$unit]['ha'],2)."</td>";
					$tab.="<td ".$rowspan." align=right>".@hidezerodecimal(($data[$tahun][$unit]['ttlkg']/$data[$tahun][$unit]['ha'])/1000,2)."</td>";
					$tab.="<td ".$rowspan." align=right>".@hidezerodecimal(($data[$tahun][$unit]['ttljjg']/$data[$tahun][$unit]['pkk']),2)."</td>";
					
					$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit]['ttljjg'])."</td>";
					$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit]['ttlkg'])."</td>";
					$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit]['ttlkgbruto'])."</td>";
					$tab.="<td ".$rowspan." align=right>".@hidezerodecimal($data[$tahun][$unit]['ttlkg']/$data[$tahun][$unit]['ttljjg'],2)."</td>";
					
					$tab.="<td style='text-align:center'>Jjg</td>";
					
					$varjjg = $varkg = $ttljjgsebar = $ttlkgsebar = 0;
					foreach($bulan as $bln){
						$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$bln]['jjg'])."</td>";
						$ttljjgsebar+=$data[$tahun][$unit][$bln]['jjg'];
						$ttlkgsebar+=$data[$tahun][$unit][$bln]['kg'];
						$ttlkgbrutosebar+=$data[$tahun][$unit][$bln]['kgbruto'];
					}
					
					$varjjg = $data[$tahun][$unit]['ttljjg']-$ttljjgsebar;
					$varkg = $data[$tahun][$unit]['ttlkg']-$ttlkgsebar;
					$varkgbruto = $data[$tahun][$unit]['ttlkgbruto']-$ttlkgbrutosebar;
					$s="";
					if(abs(round($varjjg)) >=1 or abs(round($varkg))>=1 or abs(round($varkgbruto))>=1){
						$s="x";
					}
					
					if($data[$tahun][$unit]['post']==0){
						$tab.="<td ".$rowspan." align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$tahun."','".$unit."','');\" title='Delete'></td>";
						
						$tab.="<td ".$rowspan." align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$tahun."','".$unit."','".$s."','".$varjjg."','".$varkg."');\" title='Posting'></td>";
					}else{
						if(in_array($_SESSION['empl']['jabatan'],$jab)){
							$icon="images/icons/04/16/04.png";
							$title="Unclose / Unposting";
							$unpost=" onclick=\"unposting('".$tahun."','".$unit."');\" ";
						}else {
							$icon="images/icons/04/16/02.png";
							$title="Closed / Posted";
							$unpost='';
						}
						$tab.="<td ".$rowspan." align=center width=25px></td>";
						$tab.="<td ".$rowspan." align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
					}
					

					$tab.="</tr>";
					$tab.="<tr class='rowcontent'>";
					$tab.="<td style='text-align:center'>Kg</td>";
					foreach($bulan as $bln){				
						$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$bln]['kg'])."</td>";
					}
					
					$tab.="</tr>";
					
					$tab.="<tr class='rowcontent'>";
					$tab.="<td style='text-align:center'>Kg Bruto <br/> (Grading)</td>";
					foreach($bulan as $bln){				
						$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$bln]['kgbruto'])."</td>";
					}
					
					$tab.="</tr>";
				}
			}
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>Data tidak ditemukan.</td></tr>";
		}
		
        echo $tab;
	break;
	
    case'loaddata':
        $where = "";
		if($param['tahun']!=''){
			$where.=" and tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeunit = '".$param['kodeorg']."'";
		}
		if($param['divisi']!=''){
			$where.=" and kodeblok like '".$param['divisi']."%'";
		}
		if($param['tt']!=''){
			$where.=" and tahuntanam = '".$param['tt']."'";
		}
		if($param['sebaran']!=''){
			if($param['sebaran']==0){				
				$where.=" and (kg01+kg02+kg03+kg04+kg05+kg06+kg07+kg08+kg09+kg10+kg11+kg12)=0";
			}else{
				$where.=" and (kg01+kg02+kg03+kg04+kg05+kg06+kg07+kg08+kg09+kg10+kg11+kg12)>0";
			}
		}
		if($param['ip']!=''){
			$where.=" and intiplasma = '".$param['ip']."'";
		}
		$bulan=range(1,12);
		
        $tab = "";
		if($param['jenis']=='excel'){
			$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=1>
				<thead>
				<tr class=rowheader>
					<th style=background-color:#EEFFEF; align=center width=30px>No.</th>
					<th style=background-color:#EEFFEF; align=center style='width:70px'>".$_SESSION['lang']['budgetyear']."</th>
					<!--<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['kodeorg']."</th>-->
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['divisi']."</th>
					<th style=background-color:#EEFFEF; align=centers style='width:70px'>".$_SESSION['lang']['tahuntanam']."</th>
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['blok']."</th>
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['luas']."</th>
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['pokok']."</th>
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['sph']."</th>
					<th style=background-color:#EEFFEF; align=center>Ton / Ha</th>
					<th style=background-color:#EEFFEF; align=center>Jjg / Pkk</th>
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jjg']."</th>
					<th style=background-color:#EEFFEF; align=center>BJR</th>
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['kg']."</th>
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['kg']." Bruto <br/> Grading</th>
					<th style=background-color:#EEFFEF; align=center>".$_SESSION['lang']['jenis']."</th>";
					foreach($bulan as $bln){				
						$tab.="<th style=background-color:#EEFFEF; align=center>".numToMonth($bln,'E','short')."</th>";
					}
				$tab.="<th style=background-color:#EEFFEF; align=center colspan=2>Action</th>
				</tr>
			</thead><tbody>";
		}
		
		
		$limit= 50;
		$page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 28;
		
        $sql = "select count(*) as jmlhrow from ".$dbname.".bgt_produksi_kebun_parameter where substr(kodeunit,1,4) in (".getOrgDetail(2).") ".$where."";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['jmlhrow'];
		
		
		$rowspan="rowspan=3";
		if($param['jenis']!='excel'){$lmt="limit " . $offset . "," . $limit . "";}
		$str="select * from ".$dbname.".bgt_produksi_kebun_parameter where substr(kodeunit,1,4) in (".getOrgDetail(2).") ".$where." order by tahunbudget desc,kodeunit asc,tahuntanam asc ".$lmt."";
		$res=fetchdata($str);
		if(count($res)>10000){
			exit("Warning : Data terlalu banyak, silahkan filter terlebih dahulu.");
		}
		
		if(count($res) > 0){
			foreach($res as $key=>$val){
				$no++;
				
				$luas = makeOption($dbname,'bgt_blok','kodeblok,hathnini',"kodeblok='".$val['kodeblok']."' and tahunbudget='".$val['tahunbudget']."'");
				$pokok= makeOption($dbname,'bgt_blok','kodeblok,pokokthnini',"kodeblok='".$val['kodeblok']."' and tahunbudget='".$val['tahunbudget']."'");
				
				$tluas+=$luas[$val['kodeblok']];
				$tpokok+=$pokok[$val['kodeblok']];
				$tttljjg+=$val['totaljjg'];
				$tttlkg+=$val['totalkg'];
				$tttlkgbruto+=$val['totalkgbruto'];
				
				$tab.="<tr class='rowcontent'>";
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:top;'>".$no."</td>";
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:top;'>".$val['tahunbudget']."</td>";
				#$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$nmorg[$val['kodeunit']]."</td>";
				#$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$val['kodeunit']."</td>";
				#$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$nmorg[substr($val['kodeblok'],0,6)]."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".substr($val['kodeblok'],0,6)."</td>";
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:top;'>".$val['tahuntanam']."</td>";
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:top;'>".$nmorg[$val['kodeblok']]."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($luas[$val['kodeblok']],2)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($pokok[$val['kodeblok']],0)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($pokok[$val['kodeblok']]/$luas[$val['kodeblok']],2)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal(($val['totalkg']/$luas[$val['kodeblok']])/1000,2)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal(($val['totaljjg']/$pokok[$val['kodeblok']]),2)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($val['totaljjg'],0)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($val['totalkg']/$val['totaljjg'],2)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($val['totalkg'],0)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($val['totalkgbruto'],0)."</td>";

				$tab.="<td style='text-align:center'>Jjg</td>";
				foreach($bulan as $bln){
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['jjg'.addZero($bln,2)],0)."</td>";
					$tjjg[$bln]+=$val['jjg'.addZero($bln,2)];
					$tkg[$bln]+=$val['kg'.addZero($bln,2)];
					$tkgbruto[$bln]+=$val['kgbruto'.addZero($bln,2)];
				}
				if($param['jenis']=='excel' or $val['tutup']>0){					
					$tab.="<td ".$rowspan." colspan=2></td>";
				}else{
					$tab.="<td ".$rowspan." align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('".$val['tahunbudget']."','".$val['kodeunit']."','".$val['kodeblok']."');\" ></td>";
					
					$tab.="<td ".$rowspan." align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$val['tahunbudget']."','".$val['kodeunit']."','".$val['kodeblok']."');\" title='Delete'></td>";
				}

				$tab.="</tr>";
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>Kg</td>";
				foreach($bulan as $bln){				
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['kg'.addZero($bln,2)],0)."</td>";
				}
				
				$tab.="</tr>";
				
				$tab.="</tr>";
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>Kg Bruto <br/> (Grading)</td>";
				foreach($bulan as $bln){				
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['kgbruto'.addZero($bln,2)],0)."</td>";
				}
				
				$tab.="</tr>";
			}
			
			#== TOTAL ==
			$c="vertical-align:top;background-color:#AED6F1;";
			$tab.="<tr class='rowcontent'>";
			$tab.="<td ".$rowspan." style='text-align:center;".$c."' colspan=5>TOTAL</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".hidezerodecimal($tluas,2)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".hidezerodecimal($tpokok,0)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal($tpokok/$tluas,2)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal(($tttlkg/$tluas)/1000,2)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal(($tttljjg/$tpokok),2)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal($tttljjg,0)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal($tttlkg/$tttljjg,2)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal($tttlkg,0)."</td>";
			$tab.="<td style='text-align:center;".$c."'>Jjg</td>";
			foreach($bulan as $bln){				
				$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($tjjg[$bln],0)."</td>";
			}
			$tab.="<td rowspan=2 style='text-align:center;".$c."' colspan=2></td>";
			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center;".$c."'>Kg</td>";
			foreach($bulan as $bln){				
				$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($tkg[$bln],0)."</td>";
			}
			
			$tab.="</tr>";
			
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center;".$c."'>Kg Bruto</td>";
			foreach($bulan as $bln){				
				$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($tkgbruto[$bln],0)."</td>";
			}
			
			$tab.="</tr>";


		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['datanotfound']."</tr>";
		}
		
		## PAGING
		$foot=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
		if($param['jenis']=='excel'){
			$tab.="</tbody></table>";
			$nop = "bgt_prd_".$param['tahun'].".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("bgt_prd_".$param['tahun'], $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab . "####" . $foot;
		}
	break;
	case'formupload':
		if($param['tahun']==''){
			exit("Warning : Tahun budget wajib diisi.");
		}
		if($param['kodeorg']==''){
			exit("Warning : Kode organisasi wajib diisi.");
		}
		header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=formuploadbgtprdkebun.csv");
		
		$where = $wh = "";
		if($param['kodeorg']!=''){
			$where.=" and kodeblok like '".$param['kodeorg']."%'";
		}
		if($param['divisi']!=''){
			$where.=" and kodeblok like '".$param['divisi']."%'";
		}
		if($param['tt']!=''){
			$wh.=" and thntnm = '".$param['tt']."'";
		}
		$where.=" and tahunbudget = '".$param['tahun']."'";
		$wh.=" and statusblok in ('TM','TBM') and closed='1'";
		
		$bulan=range(1,12);
		$tab.="blok,namablok,";
		foreach($bulan as $bln){				
			$tab.="kg".addZero($bln,2).",";
		}
		foreach($bulan as $bln){				
			$tab.="jjg".addZero($bln,2).",";
		}
		$tab.="\n";
		
		$str="select * from ".$dbname.".bgt_blok where 1=1 ".$where." ".$wh." order by substr(kodeblok,1,6) asc, thntnm asc,kodeblok asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$tab.=$bar['kodeblok'].",".$nmorg[$bar['kodeblok']]."\n";
		}
		
		echo $tab;
	break;
	case'sql':
		#ini untuk update sql 
		/*
		
		ALTER TABLE `bgt_produksi_kebun_parameter`
		ADD `kg01` double NOT NULL DEFAULT '0' AFTER `jjg12`,
		ADD `kg02` double NOT NULL DEFAULT '0' AFTER `kg01`,
		ADD `kg03` double NOT NULL DEFAULT '0' AFTER `kg02`,
		ADD `kg04` double NOT NULL DEFAULT '0' AFTER `kg03`,
		ADD `kg05` double NOT NULL DEFAULT '0' AFTER `kg04`,
		ADD `kg06` double NOT NULL DEFAULT '0' AFTER `kg05`,
		ADD `kg07` double NOT NULL DEFAULT '0' AFTER `kg06`,
		ADD `kg08` double NOT NULL DEFAULT '0' AFTER `kg07`,
		ADD `kg09` double NOT NULL DEFAULT '0' AFTER `kg08`,
		ADD `kg10` double NOT NULL DEFAULT '0' AFTER `kg09`,
		ADD `kg11` double NOT NULL DEFAULT '0' AFTER `kg10`,
		ADD `kg12` double NOT NULL DEFAULT '0' AFTER `kg11`,
		CHANGE `updateby` `updateby` int(10) unsigned zerofill NOT NULL DEFAULT '0' AFTER `kg12`,
		CHANGE `lastupdate` `lastupdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `updateby`;

		ALTER TABLE `bgt_produksi_kebun_parameter`
		ADD `tahuntanam` char(10) COLLATE 'latin1_swedish_ci' NOT NULL AFTER `kodeblok`,
		ADD `intiplasma` char(10) COLLATE 'latin1_swedish_ci' NOT NULL AFTER `tahuntanam`,
		CHANGE `lastupdate` `lastupdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `updateby`;

		update bgt_produksi_kebun_parameter a set kg01=(select kg01 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg02=(select kg02 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg03=(select kg03 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg04=(select kg04 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg05=(select kg05 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg06=(select kg06 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg07=(select kg07 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg08=(select kg08 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg09=(select kg09 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg10=(select kg10 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg11=(select kg11 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set kg12=(select kg12 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter set totaljjg=(jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12);
		update bgt_produksi_kebun_parameter set totalkg=(kg01+kg02+kg03+kg04+kg05+kg06+kg07+kg08+kg09+kg10+kg11+kg12);
		update bgt_produksi_kebun_parameter a set tahuntanam=(select thntnm from bgt_blok b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun_parameter a set intiplasma =(select intiplasma from bgt_blok b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);


		DROP VIEW IF EXISTS `bgt_produksi_kbn_kg_vw`;
		CREATE VIEW `bgt_produksi_kbn_kg_vw` AS select `a`.`tahunbudget` AS `tahunbudget`,`a`.`kodeunit` AS `kodeunit`,substr(`a`.`kodeblok`,1,6) AS `divisi`,`a`.`kodeblok` AS `kodeblok`,`c`.`thntnm` AS `thntnm`,`a`.`totalkg` / `a`.`totaljjg` AS `bjr`,`c`.`pokokproduksi` AS `pokokproduksi`,`c`.`hathnini` AS `luas`,`a`.`kg01` + `a`.`kg02` + `a`.`kg03` + `a`.`kg04` + `a`.`kg05` + `a`.`kg06` + `a`.`kg07` + `a`.`kg08` + `a`.`kg09` + `a`.`kg10` + `a`.`kg11` + `a`.`kg12` AS `kgsetahun`,`a`.`kg01` AS `kg01`,`a`.`kg02` AS `kg02`,`a`.`kg03` AS `kg03`,`a`.`kg04` AS `kg04`,`a`.`kg05` AS `kg05`,`a`.`kg06` AS `kg06`,`a`.`kg07` AS `kg07`,`a`.`kg08` AS `kg08`,`a`.`kg09` AS `kg09`,`a`.`kg10` AS `kg10`,`a`.`kg11` AS `kg11`,`a`.`kg12` AS `kg12`,`a`.`kg01` + `a`.`kg02` + `a`.`kg03` + `a`.`kg04` + `a`.`kg05` + `a`.`kg06` + `a`.`kg07` + `a`.`kg08` + `a`.`kg09` + `a`.`kg10` + `a`.`kg11` + `a`.`kg12` AS `totalkg`,`a`.`totaljjg` AS `totaljjg`,`c`.`intiplasma` AS `intiplasma` from (`bgt_produksi_kebun_parameter` `a` left join `bgt_blok` `c` on(`a`.`kodeblok` = `c`.`kodeblok` and `a`.`tahunbudget` = `c`.`tahunbudget`));

		DROP VIEW IF EXISTS `bgt_produksi_afdeling`;
		CREATE VIEW `bgt_produksi_afdeling` AS select `a`.`tahunbudget` AS `tahunbudget`,`a`.`kodeunit` AS `kodeunit`,left(`a`.`kodeblok`,6) AS `afdeling`,`a`.`thntnm` AS `thntnm`,sum(`a`.`pokokproduksi`) AS `pokokproduksi`,sum(`a`.`luas`) AS `luas`,sum(`a`.`totaljjg`) AS `jlhjjg`,sum(`a`.`kgsetahun`) AS `jlhkg`,sum(`a`.`pokokproduksi`) AS `jlhpkk` from `bgt_produksi_kbn_kg_vw` `a` group by `a`.`tahunbudget`,`a`.`kodeunit`,`a`.`thntnm`,left(`a`.`kodeblok`,6);

		*/

	break;

	case'fileSelected':
		$data = $_POST;
		
		$param['kodeorg']= $_SESSION['empl']['lokasitugas'];
		$kodeorg         = $_SESSION['empl']['lokasitugas'];

		$str = "select * from ".$dbname.".vhc_5jenisvhc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kelvhc[$bar['jenisvhc']]=$bar['kelompokvhc'];
		}
		
		
		if($_FILES['file']['error']==0){
			$filetype= strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$file    = $_FILES['file']['tmp_name'];  
			
			if($filetype=='.xlsx'){
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null,true,true,true);

				// Debug: print the first row to see all columns
				// echo "<pre>";
				// print_r($sheets[1]); // Print first row
				// echo "</pre>";
				
				# New 
				function createRange($start, $end) {
					$range = [];
					$current = $start;
					while ($current !== $end) {
						$range[] = $current;
						$current = ++$current; // Increment column
					}
					$range[] = $end; // Add the end column
					return $range;
				}
				
				// Tes fungsi createRange
				$range = createRange('A', 'AR');
				# End Abdul

				# Bug jika ada sampai A sekian
				// $range = range('A', 'AR');
				// echo "<pre>";
				// print_r($range);
				# End

				$header= array('tahunbudget','kodeunit','kodeblok','tahuntanam','jenisbudget','totaljjg','totalkg','totalkgbruto','jjg01','jjg02','jjg03','jjg04','jjg05','jjg06','jjg07','jjg08','jjg09','jjg10','jjg11','jjg12','kg01','kg02','kg03','kg04','kg05','kg06','kg07','kg08','kg09','kg10','kg11','kg12','kgbruto01','kgbruto02','kgbruto03','kgbruto04','kgbruto05','kgbruto06','kgbruto07','kgbruto08','kgbruto09','kgbruto10','kgbruto11','kgbruto12');
				
				foreach($header as $head){
					$cekhead[$head]=$head;
				}
				$arritem = $tahunlist = $kodeorglist = array();
				$validasiht= "";
				$err = "0";
				foreach($sheets as $noitem => $sheet){
					if($noitem>1){						
						$tahun = $sheet['A'];
						$tahunlist[$sheet['A']] = $sheet['A'];
						
						$kodeorg = $sheet['B'];
						$kodeorglist[$sheet['B']] = $sheet['B'];

						// if($sheet['C']!=''){							
						// 	$divisilist[$sheet['B']] = $sheet['B'];
						// }
						// if($sheet['C']!=''){							
						// 	$ttlist[$sheet['C']] = $sheet['C'];
						// }
					}
				}
				
				if(count($tahunlist)!=1){
					$validasiht.="Tahun Budget tidak boleh lebih dari satu.<br>"; $err++;
				}

				if(count($kodeorglist)!=1){
					$validasiht.="Kodeorganisasi tidak boleh lebih dari satu.<br>"; $err++;
				}	

				foreach($sheets as $noitem => $sheet){
					if($noitem==1){
						$tab.="<table class='sortable' cellspacing=1 cellpadding=5 border=0 >
						<thead>
							<tr class=rowheader style=height:25px>";
							$tab.="<th align=center width=30px>No.</th>";
							foreach($range as $idcol => $col){
								$style="";
								if($cekhead[$sheet[$col]]==""){
									$style="style=color:red; title='Kolom header mengalami perubahan.'";
								}
								$tab.="<th align=center ".$style.">".$sheet[$col]."</th>";
							}								
							$tab.="<th align=center>Status</th>";
							$tab.="<th align=center>Selisih <br/> (Jjg)</th>";
							$tab.="<th align=center>Selisih <br/> (Kg)</th>";
							$tab.="<th align=center>Selisih <br/> (Kg Bruto)</th>";
						$tab.="</tr>
						</thead>";
						
						// $str = "select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$tahun."'";
						// $res = fetchData($str);
						// foreach($res as $bar){
						// 	$hargabarang[$bar['kodebarang']]=$bar['hargasatuan'];
						// }
						
						// $str = "select * from ".$dbname.".bgt_blok where tahunbudget='".$tahun."' and kodeblok like '".$param['kodeorg']."%' and closed='1'";
						// $res = fetchData($str);
						// foreach($res as $bar){
						// 	$listtt[$bar['thntnm']]=$bar['thntnm'];
						// 	$listdiv[substr($bar['kodeblok'],0,6)]=substr($bar['kodeblok'],0,6);
						// }
						
						// $str = "select * from ".$dbname.".bgt_kode";
						// $res = fetchData($str);
						// foreach($res as $bar){
						// 	$namakdbgt[$bar['kodebudget']]=$bar['nama'];
						// 	$akunkdbgt[$bar['kodebudget']]=$bar['noakun'];
						// }
						// $namakdbgt['MATERIAL']='MATERIAL';
					}else{
						
						$validasi  						= "";
						
						$uploadtahunbudget   			= $sheet['A'];
						$uploadkodeunit     			= $sheet['B'];
						$uploadkodeblok         		= $sheet['C'];
						$uploadtahuntanam    			= $sheet['D'];
						$uploadjenisbudget			    = $sheet['E'];
						$uploadtotaljjg    				= $sheet['F'];
						$uploadtotalkg					= $sheet['G'];
						$uploadtotalkgbruto				= $sheet['H'];
						$uploadjjg1						= $sheet['I'];
						$uploadjjg2						= $sheet['J'];
						$uploadjjg3						= $sheet['K'];
						$uploadjjg4						= $sheet['L'];
						$uploadjjg5						= $sheet['M'];
						$uploadjjg6						= $sheet['N'];
						$uploadjjg7						= $sheet['O'];
						$uploadjjg8						= $sheet['P'];
						$uploadjjg9						= $sheet['Q'];
						$uploadjjg10					= $sheet['R'];
						$uploadjjg11					= $sheet['S'];
						$uploadjjg12					= $sheet['T'];
						$uploadkg1  					= $sheet['U'];
						$uploadkg2  					= $sheet['V'];
						$uploadkg3  					= $sheet['W'];
						$uploadkg4  					= $sheet['X'];
						$uploadkg5  					= $sheet['Y'];
						$uploadkg6  					= $sheet['Z'];
						$uploadkg7  					= $sheet['AA'];
						$uploadkg8  					= $sheet['AB'];
						$uploadkg9  					= $sheet['AC'];
						$uploadkg10  					= $sheet['AD'];
						$uploadkg11  					= $sheet['AE'];
						$uploadkg12  					= $sheet['AF'];
						$uploadkgbruto1     			= $sheet['AG'];
						$uploadkgbruto2     			= $sheet['AH'];
						$uploadkgbruto3     			= $sheet['AI'];
						$uploadkgbruto4     			= $sheet['AJ'];
						$uploadkgbruto5     			= $sheet['AK'];
						$uploadkgbruto6     			= $sheet['AL'];
						$uploadkgbruto7     			= $sheet['AM'];
						$uploadkgbruto8     			= $sheet['AN'];
						$uploadkgbruto9     			= $sheet['AO'];
						$uploadkgbruto10     			= $sheet['AP'];
						$uploadkgbruto11     			= $sheet['AQ'];
						$uploadkgbruto12     			= $sheet['AR'];

					
						if($uploadtahunbudget==''){$validasi.="Tahun Kosong.<br>";$err++;}
						if(strlen($uploadtahunbudget)!=4){$validasi.="Panjang Tahun Budget tidak sesuai.<br>";$err++;}
						if($uploadkodeunit==''){$validasi.="Kode Org	anisasi tidak boleh Kosong.<br>";$err++;}
						if(strlen($uploadkodeunit)!=4){$validasi.="Panjang Karakter Kode Organisasi tidak sesuai.<br>";$err++;}

						$sql = "select kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$uploadkodeunit."'";
						$res = fetchData($sql);
						$kodeorgval = $res[0]['kodeorganisasi'];
						
						if($uploadkodeunit != $kodeorgval) {
							$validasi .= "Kode Organisasi tersebut tidak terdaftar di Organisasi";
						}

						$method = "simpan";

						$color="";
						if($validasiht!='' or $validasi!=''){
							$color="style=color:red";
						}
						
						$no++;
						$tab.="<tr class=rowcontent ".$color." id=baris_".$no.">";
						$tab.="<td hidden>
									<input id=method_".$no." value=".$method.">
									<input id=kodeorg_".$no." value=".$kodeorg.">
									<input id=kodejurnal_".$no." value=".$kodejurnal.">
									<input id=jenis_".$no." value=".$jenis.">
								</td>";
						$tab.="<td ".$color." align=center>".$no."</td>";
						$tab.="<td ".$color." align=center id=tahunbudget_".$no.">".$uploadtahunbudget."</td>";
						$tab.="<td ".$color." align=center id=kodeunit_".$no.">".$uploadkodeunit."</td>";
						$tab.="<td ".$color." align=center id=kodeblok_".$no.">".$uploadkodeblok."</td>";
						$tab.="<td ".$color." align=center id=tahuntanam_".$no.">".$uploadtahuntanam."</td>";
						$tab.="<td ".$color." align=center id=jenisbudget_".$no.">".$uploadjenisbudget."</td>";
						$tab.="<td ".$color." align=center id=totaljjg_".$no.">".$uploadtotaljjg."</td>";
						$tab.="<td ".$color." align=center id=totalkg_".$no.">".$uploadtotalkg."</td>";
						$tab.="<td ".$color." align=center id=totalkgbruto_".$no.">".$uploadtotalkgbruto."</td>";
						$tab.="<td ".$color." align=center id=jjg01_".$no.">".$uploadjjg1."</td>";
						$tab.="<td ".$color." align=center id=jjg02_".$no.">".$uploadjjg12."</td>";
						$tab.="<td ".$color." align=center id=jjg03_".$no.">".$uploadjjg3."</td>";
						$tab.="<td ".$color." align=center id=jjg04_".$no.">".$uploadjjg4."</td>";
						$tab.="<td ".$color." align=center id=jjg05_".$no.">".$uploadjjg5."</td>";
						$tab.="<td ".$color." align=center id=jjg06_".$no.">".$uploadjjg6."</td>";
						$tab.="<td ".$color." align=center id=jjg07_".$no.">".$uploadjjg7."</td>";
						$tab.="<td ".$color." align=center id=jjg08_".$no.">".$uploadjjg8."</td>";
						$tab.="<td ".$color." align=center id=jjg09_".$no.">".$uploadjjg9."</td>";
						$tab.="<td ".$color." align=center id=jjg10_".$no.">".$uploadjjg10."</td>";
						$tab.="<td ".$color." align=center id=jjg11_".$no.">".$uploadjjg11."</td>";
						$tab.="<td ".$color." align=center id=jjg12_".$no.">".$uploadjjg12."</td>";
						$tab.="<td ".$color." align=center id=kg01_".$no.">".$uploadkg1."</td>";
						$tab.="<td ".$color." align=center id=kg02_".$no.">".$uploadkg2."</td>";
						$tab.="<td ".$color." align=center id=kg03_".$no.">".$uploadkg3."</td>";
						$tab.="<td ".$color." align=center id=kg04_".$no.">".$uploadkg4."</td>";
						$tab.="<td ".$color." align=center id=kg05_".$no.">".$uploadkg5."</td>";
						$tab.="<td ".$color." align=center id=kg06_".$no.">".$uploadkg6."</td>";
						$tab.="<td ".$color." align=center id=kg07_".$no.">".$uploadkg7."</td>";
						$tab.="<td ".$color." align=center id=kg08_".$no.">".$uploadkg8."</td>";
						$tab.="<td ".$color." align=center id=kg09_".$no.">".$uploadkg9."</td>";
						$tab.="<td ".$color." align=center id=kg10_".$no.">".$uploadkg10."</td>";
						$tab.="<td ".$color." align=center id=kg11_".$no.">".$uploadkg11."</td>";
						$tab.="<td ".$color." align=center id=kg12_".$no.">".$uploadkg12."</td>";
						$tab.="<td ".$color." align=center id=kgbruto01_".$no.">".$uploadkgbruto1."</td>";
						$tab.="<td ".$color." align=center id=kgbruto02_".$no.">".$uploadkgbruto2."</td>";
						$tab.="<td ".$color." align=center id=kgbruto03_".$no.">".$uploadkgbruto3."</td>";
						$tab.="<td ".$color." align=center id=kgbruto04_".$no.">".$uploadkgbruto4."</td>";
						$tab.="<td ".$color." align=center id=kgbruto05_".$no.">".$uploadkgbruto5."</td>";
						$tab.="<td ".$color." align=center id=kgbruto06_".$no.">".$uploadkgbruto6."</td>";
						$tab.="<td ".$color." align=center id=kgbruto07_".$no.">".$uploadkgbruto7."</td>";
						$tab.="<td ".$color." align=center id=kgbruto08_".$no.">".$uploadkgbruto8."</td>";
						$tab.="<td ".$color." align=center id=kgbruto09_".$no.">".$uploadkgbruto9."</td>";
						$tab.="<td ".$color." align=center id=kgbruto10_".$no.">".$uploadkgbruto10."</td>";
						$tab.="<td ".$color." align=center id=kgbruto11_".$no.">".$uploadkgbruto11."</td>";
						$tab.="<td ".$color." align=center id=kgbruto12_".$no.">".$uploadkgbruto12."</td>";
						# Jjg
						$ttljjg=round($uploadjjg1+$uploadjjg2+$uploadjjg3+$uploadjjg4+$uploadjjg5+$uploadjjg6+$uploadjjg7+$uploadjjg8+$uploadjjg9+$uploadjjg10+$uploadjjg11+$uploadjjg12);
						# Kg
						$ttlkg=round($uploadkg1+$uploadkg2+$uploadkg3+$uploadkg4+$uploadkg5+$uploadkg6+$uploadkg7+$uploadkg8+$uploadkg9+$uploadkg10+$uploadkg11+$uploadkg12);
						# Kg Bruto
						$ttlkgbruto=round($uploadkgbruto1+$uploadkgbruto2+$uploadkgbruto3+$uploadkgbruto4+$uploadkgbruto5+$uploadkgbruto6+$uploadkgbruto7+$uploadkgbruto8+$uploadkgbruto9+$uploadkgbruto10+$uploadkgbruto11+$uploadkgbruto12);

						$tab.="<td ".$color." align=left id=validasi_".$no.">".trim(nl2br($validasiht)).trim(nl2br($validasi)).$selisih.$varvhc.$varupah."</td>";
						$tab.="<td ".$color." align=center id=selisihjjg_".$no.">".($uploadtotaljjg-$ttljjg)."</td>";
						$tab.="<td ".$color." align=center id=selisihkg_".$no.">".($uploadtotalkg-$ttlkg)."</td>";
						$tab.="<td ".$color." align=center id=selisihkgbruto_".$no.">".($uploadtotalkgbruto-$ttlkgbruto)."</td>";

						$tab.="</tr>";
						
						
						$cekduplicate[$uploadtahunbudget][$uploadkodeunit][$uploadkodeblok][$uploadjenisbudget]+=1;
						$barisduplicate[$uploadtahunbudget][$uploadkodeunit][$uploadkodeblok][$uploadjenisbudget]=$no;
					}
				}
				
				$duplicate="<br>";
				foreach($cekduplicate as $t => $v1){
					foreach($v1 as $d => $v2){
						foreach($v2 as $k => $v3){
							foreach($v3 as $g => $v4){
								foreach($v4 as $b => $v5){
									foreach($v5 as $v => $nilai){
										if($nilai>1){
											//$duplicate.=$barisduplicate[$t][$d][$k][$g][$b][$v].", ";
											$duplicate.=$t.",".$d.",".$k.",".$g.",".$b.",".$v.";<br>";
										}
									}
								}
							}
						}
					}
				}
				
				// echo"<pre>";
				// print_r($barisduplicate);
				
				if($duplicate!=''){					
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=49 style=background-color:#fcdede;color:blue;>Ada data yang double : <b>".$duplicate."</b> (jika ada data duplicate maka data pada baris sebelumnya akan di replace dengan data baris terakhir)</td>";
					$tab.="</tr>";
				}
				
				$tab.="</tbody>";
				$tab.="<tfoot>";
				$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=47 rowspan=3 align=center style=background-color:cyan;color:black;>T O T A L</td>";
					$tab.="<td style=background-color:cyan;color:black;>SELISIH <br/> (Jjg)</td>";
					$tab.="<td align=right style=background-color:cyan;color:black;>".number_format(round($ttlrp))."</td>";
					// $tab.="<td style=background-color:cyan;color:black;>".number_format($ttlselisih)."</td>";
				$tab.="</tr>";

				$tab.="<tr class=rowcontent>";
					$tab.="<td align=center style=background-color:cyan;color:black;>SELISIH <br/> (Kg)</td>";
					$tab.="<td align=right style=background-color:cyan;color:black;>".number_format(round($totaldebet))."</td>";
					$tab.="<td hidden id=totaldebet align=right style=background-color:cyan;color:black;>".$totaldebet."</td>";
					// $tab.="<td style=background-color:cyan;color:black;>".number_format($ttlselisih)."</td>";
				$tab.="</tr>";

				$tab.="<tr class=rowcontent>";
					$tab.="<td align=center style=background-color:cyan;color:black;>SELISIH <br/> (Kg Bruto)</td>";
					$tab.="<td align=right style=background-color:cyan;color:black;>".number_format(round($totalkredit))."</td>";
					$tab.="<td hidden id=totalkredit align=right style=background-color:cyan;color:black;>".$totalkredit."</td>";
					// $tab.="<td style=background-color:cyan;color:black;>".number_format($ttlselisih)."</td>";
				$tab.="</tr>";

				
				
				
				if($err>0){
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=100 align=center style=color:black;font-size:20px;><b>Tombol simpan akan muncul jika tidak ditemukan baris yg berwarna merah.</b></td>";
					$tab.="</tr>";
				}else{
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=100 align=center><button id=btnsubmit class=mybutton onclick=\"simpanupload(".$no.")\">SaveAll</button></td>";
					$tab.="</tr>";
				}
				$tab.="</tfoot>";
				$tab.="</table>";
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
		
		echo $tab;
	break;
}

?>	