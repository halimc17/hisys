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
			$str = "select * from ".$dbname.".bgt_kapital where kunci='".$param['kunci']."'";
			$res = fetchData($str)[0];
			$param['tahunbudget']=$res['tahunbudget'];
			$param['kodeorg']=$res['kodeunit'];
			
			
			$str = "select * from ".$dbname.".bgt_kapital where tahunbudget='".$param['tahunbudget']."' and tutup='1' and kodeunit='".$param['kodeorg']."' and pta='BGT'";
			$res = fetchData($str);
			if(count($res)>0){
				throw new PDOException("Budget sudah ditutup.");
			}
			
			$str="delete from ".$dbname.".bgt_kapital  where kunci='".$param['kunci']."'";
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
			
			$str = "update " . $dbname . ".bgt_kapital set tutup='1' where tahunbudget = '".$param['tahun']."' and kodeunit='".$param['kodeorg']."' and pta='BGT'"; #exit("error".$str);
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
			
			$str = "update " . $dbname . ".bgt_kapital set tutup='0' where tahunbudget = '".$param['tahun']."' and kodeunit='".$param['kodeorg']."'  and pta='BGT'"; #exit("error".$str);
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
			
			if($param['tahunbudget']==''){
				throw new PDOException("Tahun budget wajib diisi.");
			}
			if(strlen($param['tahunbudget'])<'4'){
				throw new PDOException("Tahun budget salah.");
			}
			if($param['kodeorg']==''){
				throw new PDOException("Kode Organisasi wajib diisi.");
			}
			
			if($param['id']!=''){
				$str = "delete from ".$dbname.".bgt_kapital where kunci='".$param['id']."'";
				$owlPDO->exec($str);
			}
			
			$str = "select * from ".$dbname.".bgt_kapital where tahunbudget='".$param['tahunbudget']."' and tutup='1' and kodeunit='".$param['kodeorg']."' and pta='BGT'";
			$res = fetchData($str);
			if(count($res)>0){
				throw new PDOException("Budget sudah ditutup.");
			}
			
			
			if($param['aruskas']==''){
				throw new PDOException("Arus kas tidak boleh kosong.");
			}
			
			$data = array();
			$data = array(
				'tahunbudget' =>$param['tahunbudget'],
				'kodeunit'    =>$param['kodeorg'],
				'jeniskapital'=>$param['jeniskapital'],
				'keterangan'  =>$param['keterangan'],
				'jumlah'      =>$param['jumlah'],
				'hargasatuan' =>$param['harga'],
				'hargatotal'  =>$param['total'],
				'kodebarang'  =>$param['kodebarang'],
				'tutup'       =>'0',
				'updateby'    =>$_SESSION['standard']['userid'],
				'lokasi'      =>$param['lokasi'],
				'aruskas'     =>$param['aruskas']
			);
			$str = insertQuery($dbname,'bgt_kapital',$data,array_keys($data)); #exit("error".$str);
			$owlPDO->exec($str);
			
			
				
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
		
		$str = "SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".$dbname."' AND TABLE_NAME   = 'bgt_kapital'";
		$res = fetchdata($str);
		foreach($res as $val){
			$lastid=$val['AUTO_INCREMENT']-1;
		}
		
		echo $lastid;
		//exit("error");
	break;
	case'simpansebaran':
		try{
			$owlPDO->beginTransaction();
			
			
			$str="update ".$dbname.".bgt_kapital set `updateby`='".$_SESSION['standard']['userid']."'";
			for($i=1;$i<=12;$i++){
				$str.=", `k".addZero($i,2)."`='".$param['k'][$i]."'";
			}
			
			$str.=" where kunci='".$param['kunci']."';";
			
			$owlPDO->exec($str);
			
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'getlokasi':
		$optlokasi="<option value=''></option>";
		$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)>=4 and kodeorganisasi like '".$param['kodeorg']."%' order by length(kodeorganisasi), induk";
		// exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $val){
			$d=$val['induk'];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optlokasi.="<optgroup label='".$nmorg[$d]."'>";
			}
			$optlokasi.="<option value=".$val['kodeorganisasi'].">".$val['namaorganisasi']."</option>";
			$n=$d;
			if($d!=$n){			
				$optlokasi.="</optgroup>";
			}
		}
		echo $optlokasi;
	break;
	case'getaruskas':
		$optakun=makeOption($dbname,'sdm_5tipeasset','kodetipe,akunak');
		$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$optakun[$param['kodebgt']]."' order by a.noaruskas asc"; #exit("error".$str);
		$res=fetchdata($str);
		if(count($res)=='0'){
			exit("Warning : Nomor aruskas untuk akun ".$optakun[$param['kodebgt']]." belum ada.");
		}
		
		$optaruskas="<option value=''></option>";
		foreach($res as $bar){
			$a="";
			if($param['aruskas']==$bar['noaruskas']){
				$a="selected";
			}
			$optaruskas.="<option value=".$bar['noaruskas']." ".$a.">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
		}
		
		$str = "select * from ".$dbname.".bgt_5capex where kodecapex = '".$param['kodebgt']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$klbarang=$bar['kelbarang'];
		}
		
		$optbarang="<option value=''></option>";
		if($klbarang!=''){
			$whr=" and substr(kodebarang,1,5) in (select kelbarang from ".$dbname.".bgt_5capex where kodecapex = '".$param['kodebgt']."') ";
		/*	$str = "select * from ".$dbname.".log_5masterbarang where kodebarang like '".$klbarang."%' and inactive='0' order by kodebarang";*/
			$str = "select * from ".$dbname.".log_5masterbarang where 1=1 ".$whr." and inactive='0' order by kodebarang";
			$res = fetchdata($str);
			if(count($res)>0){
				$optbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$ada='1';
			}else{
				$ada="";
			}
			foreach($res as $val){
				$d=substr($val['kodebarang'],0,5);
				if($d!=$n){			
					$nmkel = makeOption($dbname, 'log_5subklbarang', 'kode,namasubkelompok',"kode='".$d."'");
					$optbarang.="<optgroup label='".$nmkel[$d]."'>";
				}
				$b="";
				if($param['kodebarang']==$val['kodebarang']){
					$b="selected";
				}
				$optbarang.="<option value=".$val['kodebarang']." ".$b.">".$val['kodebarang']." - ".$val['namabarang']."</option>";
				$n=$d;
				if($d!=$n){			
					$optbarang.="</optgroup>";
				}
			}
		}
		
		echo $optaruskas."####".$optbarang."####".$ada;
		
	break;
	
	case'gethargabarang':
		$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		$whr="";
		$whr.=" and kodebarang = '".$param['kodebarang']."'";
		
		
		$harga=0;
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahunbudget']."' and closed=1 ".$whr." ";
		$res=fetchData($str);
		foreach($res as $bar){
			$harga=$bar['hargasatuan'];
		}

		echo $harga;
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
			$where.=" and kodeunit = '".$param['kodeorg']."'";
		}
		$induk=makeOption($dbname,'organisasi','kodeorganisasi,induk');
		
		
        $tab = "";
		$bulan=range(1,12);
		$dtunit=array();
        $str = "select * from ".$dbname.".bgt_kapital where 1=1 ".$where." and substr(kodeunit,1,4) in (".getOrgDetail(2).") and pta='BGT' order by tahunbudget desc,kodeunit asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$jns=$bar['jeniskapital'];
			
			$dtunit[$bar['tahunbudget']][$bar['kodeunit']][$jns]=$jns;
			$data[$bar['tahunbudget']][$bar['kodeunit']][$jns]['rp']+=$bar['hargatotal'];
			$data[$bar['tahunbudget']][$bar['kodeunit']][$jns]['post']+=$bar['tutup'];
			$data[$bar['tahunbudget']][$bar['kodeunit']]['postx']+=$bar['tutup'];
			
			foreach($bulan as $bln){				
				$data[$bar['tahunbudget']][$bar['kodeunit']][$jns][$bln]['rp']+=$bar['k'.addZero($bln,2)];
			}
		}
		
		$nkapital=makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
		if(count($dtunit) > 0){
			$rowspan="";
			$no=0;
			$ttltbssebar=array();
			foreach($dtunit as $tahun => $vunit){
				foreach($vunit as $unit => $vintex){
					foreach($vintex as $intex ){						
						$no++;
						$tab.="<tr class='rowcontent'>";
						$tab.="<td ".$rowspan." style='text-align:center'>".$no."</td>";
						$tab.="<td ".$rowspan." align=center>".$tahun."</td>";
						$tab.="<td ".$rowspan." align=left>".$unit." - ".$nmorg[$unit]."</td>";
						$tab.="<td ".$rowspan." align=left>".$nkapital[$intex]."</td>";
						$tab.="<td ".$rowspan." align=right>".hidezerodecimal($data[$tahun][$unit][$intex]['rp'])."</td>";
						
						$sttl[$tahun][$unit]+=$data[$tahun][$unit][$intex]['rp'];
						
						$vartbs = $vartbsx  = $ttlkgsebar = 0;
						foreach($bulan as $bln){
							$tab.="<td style='text-align:right'>".hidezerodecimal($data[$tahun][$unit][$intex][$bln]['rp'])."</td>";
							$ttltbssebar[$tahun][$unit]+=$data[$tahun][$unit][$intex][$bln]['rp'];
							$sttlbln[$tahun][$unit][$bln]+=$data[$tahun][$unit][$intex][$bln]['rp'];
						}
						
						$vartbsx = $sttl[$tahun][$unit]-$ttltbssebar[$tahun][$unit];
						$vartbs = $data[$tahun][$unit][$intex]['rp']-$ttltbssebar[$tahun][$unit];
						$s="";
						if(abs(round($vartbs)) >= 1){
							$s="x";
						}
						$x="";
						if(abs(round($vartbsx)) >= 1){
							$x="x";
						}
						
						$tab.="<td ".$rowspan." align=center></td>";
						/* if($data[$tahun][$unit][$intex]['post']==0){
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
						} */
						
						$tab.="</tr>";
					}
					$tab.="<tr class='rowcontent'>";
					$tab.="<td colspan=4 style='text-align:center;background-color:#e6e6e6;'>SUB TOTAL</td>";
					$tab.="<td style='text-align:right;background-color:#e6e6e6;'>".hidezerodecimal($sttl[$tahun][$unit])."</td>";
					foreach($bulan as $bln){
						$tab.="<td style='text-align:right;background-color:#e6e6e6;'>".hidezerodecimal($sttlbln[$tahun][$unit][$bln])."</td>";
					}
					
					if($data[$tahun][$unit]['postx']==0){
						$tab.="<td ".$rowspan." style='text-align:center;background-color:#e6e6e6;' width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$tahun."','".$unit."','".$x."','".abs(round($vartbsx))."');\" title='Posting'></td>";
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
						$tab.="<td ".$rowspan." style='text-align:center;background-color:#e6e6e6;' width=25px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
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
			$where.=" and kodeunit = '".$param['kodeorg']."'";
		}
		if($param['sebaran']!=''){
			$where.=" and jeniskapital = '".$param['sebaran']."'";
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
					<th align=center>".$_SESSION['lang']['lokasi']."</th>
					<th align=center>".$_SESSION['lang']['jnsKapital']."</th>
					<th align=center>".$_SESSION['lang']['kodebarang']."</th>
					<th align=center>".$_SESSION['lang']['namabarang']."</th>
					<th align=center>".$_SESSION['lang']['keterangan']."</th>
					<th align=center>".$_SESSION['lang']['aruskas']."</th>
					<th align=center>".$_SESSION['lang']['namaaruskas']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['rpsat']."</th>
					<th align=center>".$_SESSION['lang']['total']."</th>";
				$tab.="<th style=background-color:#EEFFEF; align=center colspan=3>Action</th>
				</tr>
			</thead><tbody>";
		}
		
		
		$limit= 10;
		$page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 16;
		
		$where.=" and pta = 'BGT'";
        $sql = "select count(*) as jmlhrow from ".$dbname.".bgt_kapital where substr(kodeunit,1,4) in (".getOrgDetail(2).") ".$where."  and pta='BGT'";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['jmlhrow'];
		
		
		if($param['jenis']!='excel'){$lmt="limit " . $offset . "," . $limit . "";}
		$str = "select * from ".$dbname.".bgt_kapital where substr(kodeunit,1,4) in (".getOrgDetail(2).") ".$where."  and pta='BGT' order by tahunbudget desc,kodeunit asc, kunci desc ".$lmt."";
		$res=fetchdata($str);
		if(count($res)>10000){
			exit("Warning : Data terlalu banyak, silahkan filter terlebih dahulu.");
		}
		$rowspan="";
		if(count($res) > 0){
			$nkapital=makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
			$nmarus=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');
			$bulan=range(1,12);
			foreach($res as $key=>$val){
				$tutup[$val['tahunbudget']][$val['kodeunit']]+=$val['tutup'];
			}
			
			foreach($res as $key=>$val){
				$ttlsebar=0;
				foreach($bulan as $bln){				
					$ttlsebar+=$val["k".addZero($bln,2)];
				}
				$style="";
				$selisih=0;
				$selisih=round($ttlsebar)-round($val['hargatotal']);
				if(abs(round($selisih))>0){
					$style="background-color:#f7d5d5; title=\"Belum dilakukan kalenderisasi / disebarkan.\"";
				}
				
				$no++;
				$tab.="<tr class='rowcontent' style=".$style.">";
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:middle;".$style."'>".$no."</td>";
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:middle;".$style."'>".$val['tahunbudget']."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:middle;".$style."'>".$nmorg[$val['kodeunit']]."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:middle;".$style."'>".$nmorg[$val['lokasi']]."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:middle;".$style."'>".$nkapital[$val['jeniskapital']]."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:middle;".$style."'>".$val['kodebarang']."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:middle;".$style."'>".getNamaBrg($val['kodebarang'])."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:middle;".$style."'>".nl2br($val['keterangan'])."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:middle;".$style."'>".$val['aruskas']."</td>";
				$tab.="<td ".$rowspan." style='text-align:left;vertical-align:middle;".$style."'>".$nmarus[$val['aruskas']]."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:middle;".$style."'>".hidezerodecimal($val['jumlah'],2)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:middle;".$style."'>".hidezerodecimal($val['hargasatuan'],0)."</td>";
				$tab.="<td ".$rowspan." style='text-align:right;vertical-align:middle;".$style."'>".hidezerodecimal($val['hargatotal'],0)."</td>";
				$tttlkg+=$val['hargatotal'];
				if($param['jenis']=='excel' or $tutup[$val['tahunbudget']][$val['kodeunit']]>0){					
					$tab.="<td ".$rowspan." style='text-align:center;vertical-align:middle;".$style."' colspan=2></td>";
				}else{
					$tab.="<td ".$rowspan." style='text-align:center;vertical-align:middle;".$style."' width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('".$val['kunci']."');\" ></td>";
					
					$tab.="<td ".$rowspan." style='text-align:center;vertical-align:middle;".$style."' width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$val['kunci']."');\" title='Delete'></td>";
					
				}
				$tab.="<td ".$rowspan." style='text-align:center;vertical-align:middle;".$style."' width=25px><img class='zImgBtn' src='images/skyblue/zoom.png'  onclick=\"kalenderisasi('".$val['kunci']."','".$tutup[$val['tahunbudget']][$val['kodeunit']]."');\" title='Sebarkan'></td>";

				$tab.="</tr>";
			}
			
			#== TOTAL ==
			$c="vertical-align:middle;background-color:#AED6F1;";
			$tab.="<tr class='rowcontent'>";
			$tab.="<td ".$rowspan." style='text-align:center;".$c."' colspan=12>SUB TOTAL (this page only)</td>";
			$tab.="<td ".$rowspan." style='text-align:right;".$c."'>".@hidezerodecimal($tttlkg)."</td>";
			
			$tab.="<td style='text-align:center;".$c."' colspan=3></td>";
			$tab.="</tr>";
			
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['datanotfound']."</tr>";
		}
		
		## PAGING
		$foot=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
		if($param['jenis']=='excel'){
			$tab.="</tbody></table>";
			$nop = "bgt_kapital_".$param['tahun'].".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("bgt_prd_".$param['tahun'], $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab . "####" . $foot;
		}
	break;
	case 'kalenderisasi':
		$bulan=range(1,12);
		$str="select * from ".$dbname.".bgt_kapital where kunci=".$param['kunci'];
		$res=fetchdata($str);
		foreach($res as $bar){
			$ket  =nl2br($bar['keterangan']);
			$kunci=$bar['kunci'];
			$total=$bar['hargatotal'];
			foreach($bulan as $bln){				
				$data[$bln]+=$bar['k'.addZero($bln,2)];
			}
			
			
			$sql = "select * from ".$dbname.".bgt_kapital where tahunbudget='".$bar['tahunbudget']."' and kodeunit='".$bar['kodeunit']."' and tutup='1' and pta='BGT'";
			$req = fetchdata($sql);
			$tutup = count($req);
		}
		echo"<table class=sortable cellspacing=1 border=0 cellpadding=5>
				<thead>
					<tr class=rowheader>
						<th>".$_SESSION['lang']['keterangan']."</th>
						<th>".$_SESSION['lang']['total']."</th>";
						foreach($bulan as $bln){				
							echo"<th align=center width=40px >".numToMonth($bln,'E','short')."</th>";
						}
				echo"</tr>";
				echo"<tr class=rowheader style=height:25px>";
				echo"<th align=center>Isikan Persen / Qty</th>";
				echo"<th align=center >
						<button class=mybutton onclick=hapuspersen()>" . $_SESSION['lang']['delete'] . "</button>
					</th>";
					foreach($bulan as $bln){				
						echo"<th align=center><input type=text class=myinputtextnumberdt id=persen_".$bln."  onkeypress=\"return angka_doang(event);\" style=width:45px;border:blue; onkeyup=ubahNilai(); value=''></th>";
					}
				echo"</tr>";
			echo"</thead>
				<tbody>";
			echo"<tr class=rowcontent>";
			echo"<td>".$ket."</td>";
			echo"<td align=right id=totalrpx>".hidezerodecimal($total)."</td>";
			foreach($bulan as $bln){				
				echo"<td align=right id=k".$bln.">".hidezerodecimal($data[$bln])."</td>";
			}
			
			if($tutup=='0'){
				echo"</tr>";				
				echo"<tr class=rowcontent>";
				echo"<td colspan=14 align=center><button class=\"mybutton\"  onclick=simpansebaran('".trim($param['kunci'])."')>".$_SESSION['lang']['save']."</button></td>";
			}
		echo"</tr>
			</tbody>
			<tfoot>
			</tfoot>
		   </table>";
	break; 

	case'editdetail':
		$str = "select * from ".$dbname.".bgt_kapital where kunci='".$param['id']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			echo $bar['tahunbudget']."##";
			echo $bar['kodeunit']."##";
			echo $bar['jeniskapital']."##";
			echo $bar['aruskas']."##";
			echo $bar['kodebarang']."##";
			echo $bar['keterangan']."##";
			echo $bar['jumlah']."##";
			echo $bar['hargasatuan']."##";
			echo $bar['hargatotal']."##";
			echo $bar['lokasi']."##";
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
	
}

?>	