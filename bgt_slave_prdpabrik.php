<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

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
			
			$str="delete from ".$dbname.".bgt_produksi_pks  where kunci='".$param['kunci']."'";
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
			
			$str = "update " . $dbname . ".bgt_produksi_pks set tutup='1' where tahunbudget = '".$param['tahun']."' and millcode='".$param['kodeorg']."'"; #exit("error".$str);
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
			
			$str = "update " . $dbname . ".bgt_produksi_pks set tutup='0' where tahunbudget = '".$param['tahun']."' and millcode='".$param['kodeorg']."'"; #exit("error".$str);
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
			if($param['kodeorg']==''){
				throw new PDOException("Kode Organisasi wajib diisi.");
			}
			
			$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
			
			if($param['oerpersen']==''){$param['oerpersen']=0;}
			if($param['kerpersen']==''){$param['kerpersen']=0;}
			
			if($tipeorg[substr($param['kodeorg'],0,4)]=='BULKING'){
				if($param['ttlkg']<='0'){
					throw new PDOException("CPO (Kg) tidak boleh kosong.");				
				}
			}else{				
				if($param['oerpersen']=='0'){
					throw new PDOException("OER (%) tidak boleh kosong.");				
				}
				if($param['kerpersen']=='0'){
					throw new PDOException("KER (%) tidak boleh kosong.");				
				}
				if($param['ttlkg']<='0'){
					throw new PDOException("TBS (Kg) tidak boleh kosong.");				
				}				
			}
	
			for($i=1;$i<=12;$i++){
				if($param['kg'][$i]==''){$param['kg'][$i]=0;}
				$ttlkgsebar+=$param['kg'][$i];
			}
			
			if(round($param['ttlkg'])!=round($ttlkgsebar)){
				throw new PDOException("Jumlah Kg sebaran tidak sama dengan Kg total, selisih : ".(round($param['ttlkg'])-round($ttlkgsebar))." Kg");
			}
			
			$str = "select * from ".$dbname.".bgt_produksi_pks where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeorg']."' and tutup='1'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Budget sudah ditutup.");				
			}
			$str = "select * from ".$dbname.".bgt_produksi_pks where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeorg']."' and kodeunit='".$param['kodeunit']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				$str="delete from ".$dbname.".bgt_produksi_pks where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeorg']."' and kodeunit='".$param['kodeunit']."'";
				$owlPDO->exec($str);
			}
			
			if($param['jenis']!='0'){
				if($tipeorg[substr($param['kodeorg'],0,4)]=='BULKING'){
					$str = "select * from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeorg']."' and kodeunit='".$param['kodeunit']."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						$totalpks+=$bar['kgolah'];
					}
					
					$str = "select sum(kgolah) as totalkg from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeunit']."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						$totalkg+=$bar['totalkg'];
					}
					
					if((round($totalpks+$param['ttlkg']))>round($totalkg)){
						throw new PDOException("Jumlah Kg sudah melebihi Kg budget produksi kebun,<br>Budget Produksi ".$param['kodeunit']." : ".round($totalkg)." Kg<br>Budget Produksi PKS : ".(round($totalpks+$param['ttlkg']))." Kg");
					}
				}else{					
					$str = "select * from ".$dbname.".bgt_produksi_pks where tahunbudget='".$param['tahun']."' and kodeunit='".$param['kodeunit']."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						$totalpks+=$bar['kgolah'];
					}
					
					$str = "select sum(totalkg) as totalkg from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$param['tahun']."' and kodeunit='".$param['kodeunit']."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						$totalkg+=$bar['totalkg'];
					}

					if((round($totalpks+$param['ttlkg']))>round($totalkg)){
						throw new PDOException("Jumlah Kg sudah melebihi Kg budget produksi kebun,<br>Budget Produksi ".$param['kodeunit']." : ".round($totalkg)." Kg<br>Budget Produksi PKS : ".(round($totalpks+$param['ttlkg']))." Kg");
						// if(getNamaOrg($param['kodeunit'],'induk')=='AAL' and $param['tahun']<='2022'){
						// }else{						
						// }
					}
				}
			}
			
			if($param['kodeunit']==''){
				$param['kodeunit']='tbsexternal';
			}
			$kodeunitx=$param['kodeunit'];
			if($param['jenis']=='0'){
				$kodeunitx='tbsexternal';
			}
			
			$str="delete from ".$dbname.".bgt_produksi_pks where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeorg']."' and kodeunit='".$kodeunitx."' and kodesupplier='".$param['kodeunit']."'";
			$owlPDO->exec($str);
			
			$str="insert into ".$dbname.".bgt_produksi_pks (`tahunbudget`, `millcode`, `kodeunit`,`kodesupplier`, `kgolah`, `oerbunch`, `oerkernel`,`updateby`, `lastupdate`";
			for($i=1;$i<=12;$i++){
				$str.=",`olah".addZero($i,2)."`";
			}
			$str.=") values('".$param['tahun']."','".$param['kodeorg']."','".$kodeunitx."','".$param['kodeunit']."','".$param['ttlkg']."','".$param['oerpersen']."','".$param['kerpersen']."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."'";
			for($i=1;$i<=12;$i++){
				$str.=",'".$param['kg'][$i]."'";
			}
			$str.=");";
			
			//exit("error".$str);
			$owlPDO->exec($str);
			
				
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
		
	break;
	case'getunit':
		$induk = makeOption($dbname,'organisasi','kodeorganisasi,induk');
		$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($param['kodeorg'],0,4)."'");
		$where="";
		if($tipeorg[substr($param['kodeorg'],0,4)]=='BULKING'){
			$where.=" and tipe ='PABRIK'";
		}else{
			$where.=" and tipe ='KEBUN'";
		}
		
		if($param['jenis']=='1'){
			$where.=" and induk='".$induk[$param['kodeorg']]."'";
		}
		
		if($param['jenis']=='2'){
			$where.=" and induk!='".$induk[$param['kodeorg']]."'";
		}
		
		$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".organisasi where 1=1 ".$where." order by induk asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			$d=$induk[$bar['kodeorganisasi']];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			
			$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
			$n=$d;
			if($d!=$n){			
				$optunit.="</optgroup>";
			}
		}
		
		if($param['jenis']=='0'){
			$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str="select * from ".$dbname.".log_5supplier order by namasupplier asc";
			$res = fetchdata($str);
			$optunit.="<optgroup label='EXTERNAL'>";
			foreach($res as $bar){
				$optunit.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
			}
			$optunit.="</optgroup>";

		}

		echo $optunit;
	break;
	
	case'gettbskebun':
		if($param['jenis']!=0){			
			$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($param['kodeorg'],0,4)."'");
			
			if($tipeorg[substr($param['kodeorg'],0,4)]=='BULKING'){
				$where.=" and tahunbudget ='".$param['tahun']."'";
				$where.=" and millcode ='".$param['kodeunit']."'";
				
				$bulan=range(1,12);
				foreach($bulan as $bln){				
					$olah.= "sum(olah".addZero($bln,2).") as olah".addZero($bln,2).", ";
				}
				$olah.= "sum(kgolah) as kgolah, millcode, tutup, sum(kgcpo) as kgcpo, sum(kgkernel) as kgkernel";
				
				$str = "select ".$olah." from ".$dbname.".bgt_produksi_pks_vw where 1=1 ".$where."";
				$res = fetchdata($str);
				foreach($res as $bar){
					if($bar['kgolah']==0){
						exit("Warning : Budget Produksi Unit ".$nmorg[$param['kodeunit']]." belum dibuat.");
					}
					if($bar['tutup']=='0'){
						exit("Warning : Budget Produksi Unit ".$nmorg[$param['kodeunit']]." belum ditutup.");
					}
					echo round($bar['kgolah'],2)."##";
					foreach($bulan as $bln){				
						echo round($bar['olah'.addZero($bln,2)],2)."##";
					}
					echo round($bar['kgcpo']/$bar['kgolah']*100,2)."##";
					echo round($bar['kgkernel']/$bar['kgolah']*100,2)."##";
				}
			}else{				
				$where.=" and tahunbudget ='".$param['tahun']."'";
				$where.=" and kodeunit ='".$param['kodeunit']."'";
				
				$bulan=range(1,12);
				foreach($bulan as $bln){				
					$olah.= "sum(kg".addZero($bln,2).") as kg".addZero($bln,2).", ";
				}
				$olah.= "sum(totalkg) as totalkg, kodeunit, tutup";
				
				
				$str = "select ".$olah." from ".$dbname.".bgt_produksi_kebun where 1=1 ".$where."";
				$res = fetchdata($str);
				foreach($res as $bar){
					if($bar['totalkg']==0){
						exit("Warning : Budget Produksi Unit ".$nmorg[$param['kodeunit']]." belum dibuat.");
					}
					if($bar['tutup']=='0'){
						exit("Warning : Budget Produksi Unit ".$nmorg[$param['kodeunit']]." belum ditutup.");
					}
					echo round($bar['totalkg'],2)."##";
					foreach($bulan as $bln){				
						echo round($bar['kg'.addZero($bln,2)],2)."##";
					}
				}
			}
		}
	break;
	
	case'adddata':
		if($param['tahun']=='' or strlen($param['tahun'])<4){
			exit("Warning : Periksa Tahun Budget.");
		}
		if($param['kodeorg']==''){
			exit("Warning : Kode organisasi wajib diisi.");
		}
	break;
	case'showposting':
		$jab = getPostingJabatan('budget');
		$where = "";
		if($param['tahun']!=''){
			$where.=" and tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and millcode = '".$param['kodeorg']."'";
		}
		$induk=makeOption($dbname,'organisasi','kodeorganisasi,induk');
		
        $tab = "";
		$bulan=range(1,12);
		$dtunit=array();
        $str = "select * from ".$dbname.".bgt_produksi_pks_vw where 1=1 ".$where." and substr(millcode,1,4) in (".getOrgDetail(2).") order by tahunbudget desc,millcode asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['kodeunit']=='tbsexternal'){
				$jns="External"; $jnsedit=0;
			}elseif($induk[$bar['millcode']]==$induk[$bar['kodeunit']]){
				$jns="Internal"; $jnsedit=1;
			}elseif($induk[$bar['millcode']]!=$induk[$bar['kodeunit']]){
				$jns="Afiliasi"; $jnsedit=2;
			}
				
			
			$dtunit[$bar['tahunbudget']][$bar['millcode']][$jns]=$jns;
			$data[$bar['tahunbudget']][$bar['millcode']][$jns]['ttltbs']+=$bar['kgolah'];
			$data[$bar['tahunbudget']][$bar['millcode']][$jns]['ttlcpo']+=$bar['kgcpo'];
			$data[$bar['tahunbudget']][$bar['millcode']][$jns]['ttlker']+=$bar['kgkernel'];
			$data[$bar['tahunbudget']][$bar['millcode']][$jns]['post']+=$bar['tutup'];
			
			foreach($bulan as $bln){				
				$data[$bar['tahunbudget']][$bar['millcode']][$jns][$bln]['tbs']+=$bar['olah'.addZero($bln,2)];
				$data[$bar['tahunbudget']][$bar['millcode']][$jns][$bln]['cpo']+=$bar['kgcpo'.addZero($bln,2)];
				$data[$bar['tahunbudget']][$bar['millcode']][$jns][$bln]['ker']+=$bar['kgker'.addZero($bln,2)];
			}
		}
		
		
		
		if(count($dtunit) > 0){
			$rowspan="rowspan=3";
			$no=0;
			foreach($dtunit as $tahun => $vunit){
				foreach($vunit as $unit => $vintex){
					foreach($vintex as $intex ){						
						$no++;
						$tab.="<tr class='rowcontent'>";
						$tab.="<td ".$rowspan." style='text-align:center'>".$no."</td>";
						$tab.="<td ".$rowspan." align=center>".$tahun."</td>";
						$tab.="<td ".$rowspan." align=left>".$unit." - ".$nmorg[$unit]."</td>";
						$tab.="<td ".$rowspan." align=left>".$intex."</td>";
						$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit][$intex]['ttltbs'])."</td>";
						$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit][$intex]['ttlcpo']/$data[$tahun][$unit][$intex]['ttltbs']*100,2)."</td>";
						$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit][$intex]['ttlker']/$data[$tahun][$unit][$intex]['ttltbs']*100,2)."</td>";
						
						$tab.="<td style='text-align:center'>TBS</td>";
						
						$vartbs = $varkg = $ttltbssebar = $ttlkgsebar = 0;
						foreach($bulan as $bln){
							$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$intex][$bln]['tbs'])."</td>";
							$ttltbssebar+=$data[$tahun][$unit][$intex][$bln]['tbs'];
						}
						
						$ttltb[$tahun][$unit]+=$data[$tahun][$unit][$intex]['ttltbs'];
						
						$vartbs = $ttltb[$tahun][$unit]-$ttltbssebar;
						$s="";
						if(abs(round($vartbs)) > 0){
							$s="x";
						}
						
						if($data[$tahun][$unit][$intex]['post']==0){
							$tab.="<td ".$rowspan." align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$tahun."','".$unit."','".$s."','".$vartbs."');\" title='Posting'></td>";
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
							$tab.="<td ".$rowspan." align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
						}
						

						$tab.="</tr>";
						$tab.="<tr class='rowcontent'>";
						$tab.="<td style='text-align:center'>CPO</td>";
						foreach($bulan as $bln){				
							$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$intex][$bln]['cpo'])."</td>";
						}
						
						$tab.="</tr>";
						
						$tab.="<tr class='rowcontent'>";
						$tab.="<td style='text-align:center'>KER</td>";
						foreach($bulan as $bln){				
							$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$intex][$bln]['ker'])."</td>";
						}
						
						$tab.="</tr>";
					}
				}
			}
		}else{
			$colspan=21;
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
			$where.=" and millcode = '".$param['kodeorg']."'";
		}
		if($param['sebaran']!=''){
			if($param['sebaran']==0){				
				$where.=" and (olah01+olah02+olah03+olah04+olah05+olah06+olah07+olah08+olah09+olah10+olah11+olah12)=0";
			}else{
				$where.=" and (olah01+olah02+olah03+olah04+olah05+olah06+olah07+olah08+olah09+olah10+olah11+olah12)>0";
			}
		}
		
		$bulan=range(1,12);
		
        $tab = "";
		if($param['jenis']=='excel'){
			$tab.="<table class='sortable' cellspacing=1 cellpadding=5 border=1>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
					<th align=center>".$_SESSION['lang']['kodeorg']."</th>
					<th align=center>".$_SESSION['lang']['jenis']."</th>
					<th align=center>".$_SESSION['lang']['sumber']."</th>
					<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['tbs']."<br>(Kg)</th>
					<th align=center>".$_SESSION['lang']['oer']."<br>(%)</th>
					<th align=center>KER<br>(%)</th>
					<th align=center>".$_SESSION['lang']['kg']."</th>";
					foreach($bulan as $bln){				
						echo"<th align=center>".numToMonth($bln,'E','short')."</th>";
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
		
        $sql = "select count(*) as jmlhrow from ".$dbname.".bgt_produksi_pks_vw where substr(millcode,1,4) in (".getOrgDetail(2).") ".$where."";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['jmlhrow'];
		
		
		$rowspan="rowspan=3";
		if($param['jenis']!='excel'){$lmt="limit " . $offset . "," . $limit . "";}
		$str="select * from ".$dbname.".bgt_produksi_pks_vw where substr(millcode,1,4) in (".getOrgDetail(2).") ".$where." order by tahunbudget desc,millcode asc,kodeunit asc ".$lmt."";
		$res=fetchdata($str);
		if(count($res)>10000){
			exit("Warning : Data terlalu banyak, silahkan filter terlebih dahulu.");
		}
		
		if(count($res) > 0){
			$induk=makeOption($dbname,'organisasi','kodeorganisasi,induk');
			$sql = "select * from ".$dbname.".log_5supplier order by namasupplier  asc";
			$req = fetchdata($sql);
			foreach($req as $bar){
				$nmsupp[$bar['supplierid']]=$bar['namasupplier'];
			}
			foreach($res as $key=>$val){
				$no++;
				
				$tttlkg+=$val['kgolah'];
				$tttlcpo+=$val['kgcpo'];
				$tttlker+=$val['kgkernel'];
				
				if($val['kodeunit']=='tbsexternal'){
					$jns="External"; $jnsedit=0;
				}elseif($induk[$val['millcode']]==$induk[$val['kodeunit']]){
					$jns="Internal"; $jnsedit=1;
				}elseif($induk[$val['millcode']]!=$induk[$val['kodeunit']]){
					$jns="Afiliasi"; $jnsedit=2;
				}
				if($nmorg[$val['kodeunit']]!=''){
					$nmunit=$nmorg[$val['kodeunit']];
				}else{
					$nmunit=$nmsupp[$val['kodesupplier']];
				}
				
				$tab.="<tr class='rowcontent'>";
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:top;'>".$no."</td>";
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:top;'>".$val['tahunbudget']."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$nmorg[$val['millcode']]."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$jns."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$nmunit."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($val['kgolah'],0)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($val['oerbunch'],2)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:top;'>".hidezerodecimal($val['oerkernel'],2)."</td>";
				
				$tab.="<td style='text-align:center'>TBS</td>";
				foreach($bulan as $bln){
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['olah'.addZero($bln,2)],0)."</td>";
					$ttbs[$bln]+=$val['olah'.addZero($bln,2)];
					$tcpo[$bln]+=$val['kgcpo'.addZero($bln,2)];
					$tker[$bln]+=$val['kgker'.addZero($bln,2)];
					
					$gtcpo+=$val['kgcpo'.addZero($bln,2)];
					$gtker+=$val['kgker'.addZero($bln,2)];
				}
				if($param['jenis']=='excel' or $val['tutup']>0){					
					$tab.="<td ".$rowspan." colspan=2></td>";
				}else{
					$tab.="<td ".$rowspan." align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('".$val['tahunbudget']."','".$val['millcode']."','".$jnsedit."','".$val['kodesupplier']."','".$val['kgolah']."','".$val['oerbunch']."','".$val['oerkernel']."','".$val['olah01']."','".$val['olah02']."','".$val['olah03']."','".$val['olah04']."','".$val['olah05']."','".$val['olah06']."','".$val['olah07']."','".$val['olah08']."','".$val['olah09']."','".$val['olah10']."','".$val['olah11']."','".$val['olah12']."');\" ></td>";
					
					$tab.="<td ".$rowspan." align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$val['kunci']."');\" title='Delete'></td>";
				}

				$tab.="</tr>";
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>CPO</td>";
				foreach($bulan as $bln){				
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['kgcpo'.addZero($bln,2)],0)."</td>";
				}
				
				$tab.="</tr>";
				
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>KER</td>";
				foreach($bulan as $bln){				
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['kgker'.addZero($bln,2)],0)."</td>";
				}
				
				$tab.="</tr>";
			}
			
			#== TOTAL ==
			$c="vertical-align:middle;background-color:#AED6F1;";
			$tab.="<tr class='rowcontent'>";
			$tab.="<td ".$rowspan." style='text-align:center;".$c."' colspan=5>SUB TOTAL (this page only)</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal($tttlkg)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal(($gtcpo/$tttlkg)*100,2)."</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal(($gtker/$tttlkg)*100,2)."</td>";
			$tab.="<td style='text-align:center;".$c."'>TBS</td>";
			foreach($bulan as $bln){				
				$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($ttbs[$bln],0)."</td>";
			}
			$tab.="<td rowspan=3 style='text-align:center;".$c."' colspan=2></td>";
			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center;".$c."'>CPO</td>";
			foreach($bulan as $bln){				
				$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($tcpo[$bln],0)."</td>";
			}
			
			$tab.="</tr>";
			
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center;".$c."'>KER</td>";
			foreach($bulan as $bln){				
				$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($tker[$bln],0)."</td>";
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
		
		ALTER TABLE `bgt_produksi_kebun`
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

		ALTER TABLE `bgt_produksi_kebun`
		ADD `tahuntanam` char(10) COLLATE 'latin1_swedish_ci' NOT NULL AFTER `kodeblok`,
		ADD `intiplasma` char(10) COLLATE 'latin1_swedish_ci' NOT NULL AFTER `tahuntanam`,
		CHANGE `lastupdate` `lastupdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `updateby`;

		update bgt_produksi_kebun a set kg01=(select kg01 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg02=(select kg02 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg03=(select kg03 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg04=(select kg04 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg05=(select kg05 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg06=(select kg06 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg07=(select kg07 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg08=(select kg08 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg09=(select kg09 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg10=(select kg10 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg11=(select kg11 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set kg12=(select kg12 from bgt_produksi_kbn_kg_vw b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun set totaljjg=(jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12);
		update bgt_produksi_kebun set totalkg=(kg01+kg02+kg03+kg04+kg05+kg06+kg07+kg08+kg09+kg10+kg11+kg12);
		update bgt_produksi_kebun a set tahuntanam=(select thntnm from bgt_blok b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);
		update bgt_produksi_kebun a set intiplasma =(select intiplasma from bgt_blok b where b.tahunbudget=a.tahunbudget and b.kodeblok=a.kodeblok);


		DROP VIEW IF EXISTS `bgt_produksi_kbn_kg_vw`;
		CREATE VIEW `bgt_produksi_kbn_kg_vw` AS select `a`.`tahunbudget` AS `tahunbudget`,`a`.`kodeunit` AS `kodeunit`,substr(`a`.`kodeblok`,1,6) AS `divisi`,`a`.`kodeblok` AS `kodeblok`,`c`.`thntnm` AS `thntnm`,`a`.`totalkg` / `a`.`totaljjg` AS `bjr`,`c`.`pokokproduksi` AS `pokokproduksi`,`c`.`hathnini` AS `luas`,`a`.`kg01` + `a`.`kg02` + `a`.`kg03` + `a`.`kg04` + `a`.`kg05` + `a`.`kg06` + `a`.`kg07` + `a`.`kg08` + `a`.`kg09` + `a`.`kg10` + `a`.`kg11` + `a`.`kg12` AS `kgsetahun`,`a`.`kg01` AS `kg01`,`a`.`kg02` AS `kg02`,`a`.`kg03` AS `kg03`,`a`.`kg04` AS `kg04`,`a`.`kg05` AS `kg05`,`a`.`kg06` AS `kg06`,`a`.`kg07` AS `kg07`,`a`.`kg08` AS `kg08`,`a`.`kg09` AS `kg09`,`a`.`kg10` AS `kg10`,`a`.`kg11` AS `kg11`,`a`.`kg12` AS `kg12`,`a`.`kg01` + `a`.`kg02` + `a`.`kg03` + `a`.`kg04` + `a`.`kg05` + `a`.`kg06` + `a`.`kg07` + `a`.`kg08` + `a`.`kg09` + `a`.`kg10` + `a`.`kg11` + `a`.`kg12` AS `totalkg`,`a`.`totaljjg` AS `totaljjg`,`c`.`intiplasma` AS `intiplasma` from (`bgt_produksi_kebun` `a` left join `bgt_blok` `c` on(`a`.`kodeblok` = `c`.`kodeblok` and `a`.`tahunbudget` = `c`.`tahunbudget`));

		DROP VIEW IF EXISTS `bgt_produksi_afdeling`;
		CREATE VIEW `bgt_produksi_afdeling` AS select `a`.`tahunbudget` AS `tahunbudget`,`a`.`kodeunit` AS `kodeunit`,left(`a`.`kodeblok`,6) AS `afdeling`,`a`.`thntnm` AS `thntnm`,sum(`a`.`pokokproduksi`) AS `pokokproduksi`,sum(`a`.`luas`) AS `luas`,sum(`a`.`totaljjg`) AS `jlhjjg`,sum(`a`.`kgsetahun`) AS `jlhkg`,sum(`a`.`pokokproduksi`) AS `jlhpkk` from `bgt_produksi_kbn_kg_vw` `a` group by `a`.`tahunbudget`,`a`.`kodeunit`,`a`.`thntnm`,left(`a`.`kodeblok`,6);

		*/

	break;
}

?>