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
			
			$str="update ".$dbname.".bgt_produksi_bulk set updateby='".$_SESSION['standard']['userid']."', lastupdate='".date("Y-m-d H:i:s")."' ";
			for($i=1;$i<=12;$i++){
				if($param['komoditi']=='CPO'){					
					$str.=",`kgcpo".addZero($i,2)."`='0'";
				}
				if($param['komoditi']=='KER'){					
					$str.=",`kgker".addZero($i,2)."`='0'";
				}
			}
			$str.=" where kunci='".$param['kunci']."'";
			$owlPDO->exec($str);
			
			$str="select sum(kgcpo01+kgcpo02+kgcpo03+kgcpo04+kgcpo05+kgcpo06+kgcpo07+kgcpo08+kgcpo09+kgcpo10+kgcpo11+kgcpo12+
			kgker01+kgker02+kgker03+kgker04+kgker05+kgker06+kgker07+kgker08+kgker09+kgker10+kgker11+kgker12) as jlh from ".$dbname.".bgt_produksi_bulk where kunci='".$param['kunci']."'";
			$res = fetchdata($str);
			if($res[0]['jlh']==0){				
				$str="delete from ".$dbname.".bgt_produksi_bulk  where kunci='".$param['kunci']."'";
				$owlPDO->exec($str);
			}
			
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
			
			$str = "update " . $dbname . ".bgt_produksi_bulk set tutup='1' where tahunbudget = '".$param['tahun']."' and millcode='".$param['kodeorg']."'"; #exit("error".$str);
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
			
			$str = "update " . $dbname . ".bgt_produksi_bulk set tutup='0' where tahunbudget = '".$param['tahun']."' and millcode='".$param['kodeorg']."'"; #exit("error".$str);
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
			if($tipeorg[substr($param['kodeorg'],0,4)]!='BULKING'){
				throw new PDOException("Hanya untuk Bulking.");				
			}
			for($i=1;$i<=12;$i++){
				if($param['kg'][$i]==''){$param['kg'][$i]=0;}
				$ttlkgsebar+=$param['kg'][$i];
			}
			
			
			$str = "select * from ".$dbname.".bgt_produksi_bulk where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeorg']."' and tutup='1'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Budget sudah ditutup.");				
			}
			
			$set='insert';
			$str = "select * from ".$dbname.".bgt_produksi_bulk where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeorg']."' and kodeunit='".$param['kodeunit']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				$set='update';
			}
			
			if($param['kodeunit']==''){
				$param['kodeunit']='tbsexternal';
			}
			$kodeunitx=$param['kodeunit'];
			if($param['jenis']=='0'){
				$kodeunitx='tbsexternal';
			}
			
			if($set=='insert'){				
				$str="insert into ".$dbname.".bgt_produksi_bulk (`tahunbudget`, `millcode`, `kodeunit`,`kodesupplier`,`updateby`, `lastupdate`";
				for($i=1;$i<=12;$i++){
					if($param['komoditi']=='CPO'){					
						$str.=",`kgcpo".addZero($i,2)."`";
						$kolom=",`kgcpo`";
					}
					if($param['komoditi']=='KER'){					
						$str.=",`kgker".addZero($i,2)."`";
						$kolom=",`kgkernel`";
					}
				}
				$str.=" ".$kolom.") values('".$param['tahun']."','".$param['kodeorg']."','".$kodeunitx."','".$param['kodeunit']."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."'";
				for($i=1;$i<=12;$i++){
					$str.=",'".$param['kg'][$i]."'";
					$total+=$param['kg'][$i];
				}
				$str.=",".$total.");";
				
				$owlPDO->exec($str);
			}else{
				$str="update ".$dbname.".bgt_produksi_bulk set updateby='".$_SESSION['standard']['userid']."', lastupdate='".date("Y-m-d H:i:s")."' ";
				for($i=1;$i<=12;$i++){
					if($param['komoditi']=='CPO'){					
						$str.=",`kgcpo".addZero($i,2)."`='".$param['kg'][$i]."'";
						$kolom=",kgcpo=";
					}
					if($param['komoditi']=='KER'){					
						$str.=",`kgker".addZero($i,2)."`='".$param['kg'][$i]."'";
						$kolom=",kgkernel=";
					}
					$total+=$param['kg'][$i];
				}
				$str.=" ".$kolom."'".$total."'  where tahunbudget='".$param['tahun']."' and millcode='".$param['kodeorg']."' and kodeunit='".$param['kodeunit']."'";
				$owlPDO->exec($str);
			}
			
			//exit("error".$str);
				
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
			$str="select * from ".$dbname.".log_5supplier order by namasupplier  asc";
			$res = fetchdata($str);
			$optunit.="<optgroup label='EXTERNAL'>";
			foreach($res as $bar){
				$optunit.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
			}
			$optunit.="</optgroup>";

		}

		echo $optunit;
	break;
	
	// case'gettbskebun':
		// if($param['jenis']!=0){			
			// $tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($param['kodeorg'],0,4)."'");
			
			// if($tipeorg[substr($param['kodeorg'],0,4)]=='BULKING'){
				// $where.=" and tahunbudget ='".$param['tahun']."'";
				// $where.=" and millcode ='".$param['kodeunit']."'";
				
				// $bulan=range(1,12);
				// foreach($bulan as $bln){				
					// $olah.= "sum(olah".addZero($bln,2).") as olah".addZero($bln,2).", ";
				// }
				// $olah.= "sum(kgolah) as kgolah, millcode, tutup, sum(kgcpo) as kgcpo, sum(kgkernel) as kgkernel";
				
				// $str = "select ".$olah." from ".$dbname.".bgt_produksi_bulk where 1=1 ".$where."";
				// $res = fetchdata($str);
				// foreach($res as $bar){
					// if($bar['kgolah']==0){
						// exit("Warning : Budget Produksi Unit ".$nmorg[$param['kodeunit']]." belum dibuat.");
					// }
					// if($bar['tutup']=='0'){
						// exit("Warning : Budget Produksi Unit ".$nmorg[$param['kodeunit']]." belum ditutup.");
					// }
					// echo round($bar['kgolah'],2)."##";
					// foreach($bulan as $bln){				
						// echo round($bar['olah'.addZero($bln,2)],2)."##";
					// }
					// echo round($bar['kgcpo']/$bar['kgolah']*100,2)."##";
					// echo round($bar['kgkernel']/$bar['kgolah']*100,2)."##";
				// }
			// }else{				
				// $where.=" and tahunbudget ='".$param['tahun']."'";
				// $where.=" and kodeunit ='".$param['kodeunit']."'";
				
				// $bulan=range(1,12);
				// foreach($bulan as $bln){				
					// $olah.= "sum(kg".addZero($bln,2).") as kg".addZero($bln,2).", ";
				// }
				// $olah.= "sum(totalkg) as totalkg, kodeunit, tutup";
				
				
				// $str = "select ".$olah." from ".$dbname.".bgt_produksi_kebun where 1=1 ".$where."";
				// $res = fetchdata($str);
				// foreach($res as $bar){
					// if($bar['totalkg']==0){
						// exit("Warning : Budget Produksi Unit ".$nmorg[$param['kodeunit']]." belum dibuat.");
					// }
					// if($bar['tutup']=='0'){
						// exit("Warning : Budget Produksi Unit ".$nmorg[$param['kodeunit']]." belum ditutup.");
					// }
					// echo round($bar['totalkg'],2)."##";
					// foreach($bulan as $bln){				
						// echo round($bar['kg'.addZero($bln,2)],2)."##";
					// }
				// }
			// }
		// }
	// break;
	
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
        $str = "select * from ".$dbname.".bgt_produksi_bulk where 1=1 ".$where." and substr(millcode,1,4) in (".getOrgDetail(2).") order by tahunbudget desc,millcode asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['kodeunit']=='tbsexternal'){
				$jns="External"; $jnsedit=0;
			}elseif($induk[$bar['millcode']]==$induk[$bar['kodeunit']]){
				$jns="Internal"; $jnsedit=1;
			}elseif($induk[$bar['millcode']]!=$induk[$bar['kodeunit']]){
				$jns="Afiliasi"; $jnsedit=2;
			}
				
			
			$dtunit[$bar['tahunbudget']][$bar['millcode']]=$bar['millcode'];
			$data[$bar['tahunbudget']][$bar['millcode']]['post']+=$bar['tutup'];
			
			foreach($bulan as $bln){				
				$data[$bar['tahunbudget']][$bar['millcode']][$bln]['tbs']+=$bar['olah'.addZero($bln,2)];
				$data[$bar['tahunbudget']][$bar['millcode']][$bln]['cpo']+=$bar['kgcpo'.addZero($bln,2)];
				$data[$bar['tahunbudget']][$bar['millcode']][$bln]['ker']+=$bar['kgker'.addZero($bln,2)];
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
					
					$tab.="<td style='text-align:center'>CPO</td>";
					
					$vartbs = $varkg = $ttltbssebar = $ttlkgsebar = 0;
					foreach($bulan as $bln){
						$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$bln]['cpo'])."</td>";
					}
					
					$s="";
					if($data[$tahun][$unit]['post']==0){
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
					$tab.="<td style='text-align:center'>PK</td>";
					foreach($bulan as $bln){				
						$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$bln]['ker'])."</td>";
					}
					
					$tab.="</tr>";
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
				$where.=" and (kgcpo01+kgcpo02+kgcpo03+kgcpo04+kgcpo05+kgcpo06+kgcpo07+kgcpo08+kgcpo09+kgcpo10+kgcpo11+kgcpo12+
				kgker01+kgker02+kgker03+kgker04+kgker05+kgker06+kgker07+kgker08+kgker09+kgker10+kgker11+kgker12)=0";
			}else{
				$where.=" and (kgcpo01+kgcpo02+kgcpo03+kgcpo04+kgcpo05+kgcpo06+kgcpo07+kgcpo08+kgcpo09+kgcpo10+kgcpo11+kgcpo12+
				kgker01+kgker02+kgker03+kgker04+kgker05+kgker06+kgker07+kgker08+kgker09+kgker10+kgker11+kgker12)>0";
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
		
        $sql = "select count(*) as jmlhrow from ".$dbname.".bgt_produksi_bulk where substr(millcode,1,4) in (".getOrgDetail(2).") ".$where."";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['jmlhrow'];
		
		
		$rowspan="rowspan=2";
		if($param['jenis']!='excel'){$lmt="limit " . $offset . "," . $limit . "";}
		$str="select * from ".$dbname.".bgt_produksi_bulk where substr(millcode,1,4) in (".getOrgDetail(2).") ".$where." order by tahunbudget desc,millcode asc,kodeunit asc ".$lmt."";
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
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$nmorg[$val['kodeunit']]."</td>";
				
				$tab.="<td style='text-align:center'>CPO</td>";
				foreach($bulan as $bln){
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['kgcpo'.addZero($bln,2)],0)."</td>";
					$tcpo[$bln]+=$val['kgcpo'.addZero($bln,2)];
					$tker[$bln]+=$val['kgker'.addZero($bln,2)];
					
					$gtcpo+=$val['kgcpo'.addZero($bln,2)];
					$gtker+=$val['kgker'.addZero($bln,2)];
				}
				if($param['jenis']=='excel' or $val['tutup']>0){					
					$tab.="<td colspan=2></td>";
				}else{
					$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('".$val['tahunbudget']."','".$val['millcode']."','".$jnsedit."','".$val['kodeunit']."','".$val['kgkgcpo']."','".$val['oerbunch']."','".$val['oerkernel']."','".$val['kgcpo01']."','".$val['kgcpo02']."','".$val['kgcpo03']."','".$val['kgcpo04']."','".$val['kgcpo05']."','".$val['kgcpo06']."','".$val['kgcpo07']."','".$val['kgcpo08']."','".$val['kgcpo09']."','".$val['kgcpo10']."','".$val['kgcpo11']."','".$val['kgcpo12']."','CPO');\" ></td>";
					
					$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$val['kunci']."','CPO');\" title='Delete'></td>";
				}

				$tab.="</tr>";
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>KER</td>";
				foreach($bulan as $bln){				
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['kgker'.addZero($bln,2)],0)."</td>";
				}
				
				if($param['jenis']=='excel' or $val['tutup']>0){					
					$tab.="<td colspan=2></td>";
				}else{
					$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('".$val['tahunbudget']."','".$val['millcode']."','".$jnsedit."','".$val['kodeunit']."','".$val['kgkgker']."','".$val['oerbunch']."','".$val['oerkernel']."','".$val['kgker01']."','".$val['kgker02']."','".$val['kgker03']."','".$val['kgker04']."','".$val['kgker05']."','".$val['kgker06']."','".$val['kgker07']."','".$val['kgker08']."','".$val['kgker09']."','".$val['kgker10']."','".$val['kgker11']."','".$val['kgker12']."','KER');\" ></td>";
					
					$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$val['kunci']."','KER');\" title='Delete'></td>";
				}
				
				$tab.="</tr>";
			}
			
			#== TOTAL ==
			$c="vertical-align:middle;background-color:#AED6F1;";
			$tab.="<tr class='rowcontent'>";
			$tab.="<td ".$rowspan." style='text-align:center;".$c."' colspan=5>SUB TOTAL (this page only)</td>";
			// $tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal($tttlkg)."</td>";
			// $tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal(($gtcpo/$tttlkg)*100,2)."</td>";
			// $tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal(($gtker/$tttlkg)*100,2)."</td>";
			$tab.="<td style='text-align:center;".$c."'>CPO</td>";
			foreach($bulan as $bln){				
				$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($tcpo[$bln],0)."</td>";
			}
			$tab.="<td rowspan=3 style='text-align:center;".$c."' colspan=2></td>";
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