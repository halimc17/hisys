<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

switch ($method) {
    case 'getkodeorg':
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
		} else {
			$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
		}

		$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str = "select * from ".$dbname.".organisasi where induk='".$param['pt']."' and kodeorganisasi in (".getOrgDetail(2).") ".$where." order by tipe";
		$res = fetchdata($str);
		foreach($res as $bar){
			$s="";
			$optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}
		echo $optorg;
    break;
    case 'preview':
    	if($param['tipe']=='excel'){
			$tab = "<table class=sortable cellpadding=5 cellspacing=1 border='1'>";
		}else{			
			$tab = "<table class=sortable cellpadding=5 cellspacing=1>";
		}
		$tab.="
			<thead>
				<tr class=rowheader>
					<th rowspan=2>".$_SESSION['lang']['nourut']."</th>
					<th rowspan=2>".$_SESSION['lang']['nik2']."</th>
					<th rowspan=2>".$_SESSION['lang']['nama']."</th>
					<th rowspan=2>".$_SESSION['lang']['jabatan']."</th>
					<th rowspan=2>".$_SESSION['lang']['kodegolongan']."</th>
					<th rowspan=2>".$_SESSION['lang']['departemen']."</th>
					<th rowspan=2>".$_SESSION['lang']['lokasitugas']."</th>
					<th rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>
					<th rowspan=2>".$_SESSION['lang']['tanggalmasuk']."</th>
					<th rowspan=2>".$_SESSION['lang']['masakerja']."</th>
					<th rowspan=2>Quartal</th>
					<th rowspan=2>".$_SESSION['lang']['tahun']."</th>
					<th rowspan=2>Status MM</th>
					<th colspan=5>".$_SESSION['lang']['kpi']."</th>
					<th colspan=5>Core Value</th>
					<th colspan=5>Man Management</th>
					<th colspan=5>PAS</th>
				</tr>
				<tr class=rowheader>
					<th>".$_SESSION['lang']['status']."</th>
					<th>".$_SESSION['lang']['nilai']."</th>
					<th>".$_SESSION['lang']['nilai']." Final</th>
					<th>".$_SESSION['lang']['atasan']."</th>
					<th>".$_SESSION['lang']['persetujuan']."</th>
					<th>".$_SESSION['lang']['status']."</th>
					<th>".$_SESSION['lang']['nilai']."</th>
					<th>".$_SESSION['lang']['nilai']." Final</th>
					<th>".$_SESSION['lang']['atasan']."</th>
					<th>".$_SESSION['lang']['persetujuan']."</th>
					<th>".$_SESSION['lang']['status']."</th>
					<th>".$_SESSION['lang']['nilai']."</th>
					<th>".$_SESSION['lang']['nilai']." Final</th>
					<th>".$_SESSION['lang']['atasan']."</th>
					<th>".$_SESSION['lang']['persetujuan']."</th>
					<th>".$_SESSION['lang']['status']."</th>
					<th>".$_SESSION['lang']['nilai']." Final</th>
					<th>".$_SESSION['lang']['kategori']." ".$_SESSION['lang']['nilai']."</th>
					<th>".$_SESSION['lang']['atasan']."</th>
					<th>".$_SESSION['lang']['persetujuan']."</th>
				</tr>
			</thead>
		 <tbody>";
		
		$where="";
		$where.=" and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > '".date("Y-m-d")."') and a.tanggalmasuk<='".date("Y-m-d")."'";
		if($param['kodeorg']!=''){
			$where.=" and lokasitugas = '".$param['kodeorg']."'";			
		}
		if($param['pt']!=''){
			$where.=" and kodeorganisasi = '".$param['pt']."'";			
		}else{
			//exit("error: Kode PT harus diisi.");
		}
		if($param['departemen']!=''){
			$where.=" and bagian = '".$param['departemen']."'";			
		}
		if($param['kodegolongan']!=''){
			$where.=" and kodegolongan = '".$param['kodegolongan']."'";			
		}
		if($param['jabatan']!=''){
			$where.=" and kodejabatan = '".$param['jabatan']."'";			
		}
		if($param['karyawanid']!=''){
			$where.=" and (namakaryawan like '%".$param['karyawanid']."%' or nik like '%".$param['karyawanid']."%')";
		}
		if($param['penilaian']!=''){
			$where.=" and (b.penilaian = '".$param['penilaian']."' or b.penilaian is null)";
		}
		if($param['tahun']!=''){
			$where.=" and (b.tahun = '".$param['tahun']."' or b.tahun is null)";
		}
		if($param['statusmm']!=''){
			$where.=" and (b.manmanagement = '".$param['statusmm']."' or b.manmanagement is null)";
		}
		if($param['tipekaryawan']!=''){
			$where.=" and tipekaryawan = '".$param['tipekaryawan']."'";			
		}
		
		if ($param['kpi'] != ''){
			if ($param['kpi'] == 'notinput'){
				$where .= " AND a.karyawanid not in (select karyawanid from ".$dbname.".sdm_kpi)";
			}
			if ($param['kpi'] == 'input'){
				$where .= " AND a.karyawanid in (select karyawanid from ".$dbname.".sdm_kpi)";
			}
			if ($param['kpi'] == 'notpost'){
				$where .= " AND b.posting='0'";
			}
			if ($param['kpi'] == 'post'){
				$where .= " AND b.posting='1'";
			}
		}
		if ($param['cv'] != ''){
			if ($param['cv'] == 'notinput'){
				$where .= " AND c.id is null";
			}
			if ($param['cv'] == 'input'){
				$where .= " AND c.id !=''";
			}
			if ($param['cv'] == 'notpost'){
				$where .= " AND c.posting='0'";
			}
			if ($param['cv'] == 'post'){
				$where .= " AND c.posting='1'";
			}
		}
		if ($param['mm'] != ''){
			if ($param['mm'] == 'notinput'){
				$where .= " AND d.id is null";
			}
			if ($param['mm'] == 'input'){
				$where .= " AND d.id !=''";
			}
			if ($param['mm'] == 'notpost'){
				$where .= " AND d.posting='0'";
			}
			if ($param['mm'] == 'post'){
				$where .= " AND d.posting='1'";
			}
		}
		if ($param['pas'] != ''){
			if ($param['pas'] == 'notinput'){
				$where .= " AND e.id is null";
			}
			if ($param['pas'] == 'input'){
				$where .= " AND e.id !=''";
			}
			if ($param['pas'] == 'notpost'){
				$where .= " AND e.posting='0'";
			}
			if ($param['pas'] == 'post'){
				$where .= " AND e.posting='1'";
			}
		}
		
		$namagol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
		$where.= " and a.tipekaryawan in ('1','0')";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where .= "";
		}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where .= " AND lokasitugas not like '%HO'";
		}else{
			$where .= " AND (lokasitugas not like '%HO' and lokasitugas not like '%RO')";
			$where .= " and a.lokasitugas in (".getOrgDetail(2).")";
			$where .= " and a.lokasitugas = '".$_SESSION['empl']['lokasitugas']."'";
			$where .= " and (f.namagolongan < '".$namagol[$_SESSION['empl']['kodegolongan']]."' or a.karyawanid='".$_SESSION['standard']['userid']."')";
		}
		
		$str = "select * from ".$dbname.".sdm_5pms";
		$req = fetchdata($str);
		foreach($req as $val){
			if($val['tipe']=='1'){
				$tipe='Y';
			}else{
				$tipe='N';
			}
			if($val['kriteria']=='KPI'){				
				$bobotkpi[$tipe]=$val['persen'];
			}
			if($val['kriteria']=='Core Values'){				
				$bobotcv[$tipe]=$val['persen'];
			}
			if($val['kriteria']=='Man Management'){				
				$bobotmm[$tipe]=$val['persen'];
			}
		}
		$arrapprov=['1'=>'Disetujui','2'=>'Ditolak','0'=>'Belum diajukan'];
		$colapprov=['1'=>'#93c47d','2'=>'#ea9999','0'=>'#fff2cc'];
		
		$status  = array('Y'=>"YA",'N'=>'TIDAK');
		
		$colom = "b.approval,b.namaatasan,a.karyawanid,nik,namakaryawan,kodejabatan,bagian,a.kodegolongan,lokasitugas,a.tipekaryawan,tanggalmasuk, b.penilaian,
				b.tahun,b.manmanagement,b.id,c.id as idcv, c.posting as postingcv,d.id as idmm, d.posting as postingmm, e.id as idpas, e.posting as postingpas, 
				e.nilaifinal, c.namaatasan as namaatasancv, d.namaatasan as namaatasanmm, e.atasanpenilai as atasanpas, e.approval as approvalpas, c.approval as approvalcv,
				d.approval as approvalmm, e.kategorifinal
				";
		
		$str = "select ".$colom." from ".$dbname.".datakaryawan a 
		left join ".$dbname.".sdm_kpi b on a.karyawanid=b.karyawanid 
		left join ".$dbname.".sdm_corevalueandmanmanagement c on a.karyawanid=c.karyawanid and b.tahun=c.tahun and b.penilaian=c.penilaian and c.jenis='corevalue'
		left join ".$dbname.".sdm_corevalueandmanmanagement d on a.karyawanid=d.karyawanid and b.tahun=d.tahun and b.penilaian=d.penilaian and d.jenis='manmanagement'
		left join ".$dbname.".sdm_pas e on e.id=b.id
		left join ".$dbname.".sdm_5golongan f on f.kodegolongan=a.kodegolongan
		where 1=1 ".$where." order by namakaryawan";
		if($_SESSION['standard']['userid']=='0000000007'){
			#echo $str;
		}
		
		$res = fetchdata($str);
		foreach($res as $bar){
			$totalproporsi=0;
			if(!is_null($bar['id'])){
				$query = "select idkpi,idht from ".$dbname.".sdm_kpidt1 where idht = '".$bar['id']."'";
				$req = fetchdata($query);
				foreach($req as $val){
					$sql = "select totalproporsi from ".$dbname.".sdm_kpidt2 where iddt1 ='".$val['idkpi']."' order by iddt1";
					$req = fetchdata($sql);
					foreach($req as $b){
						$totalproporsi+=$b['totalproporsi'];
					}
				}
			}
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td>".$no."</td>";
			$tab.="<td>".$bar['nik']."</td>";
			$tab.="<td nowrap>".$bar['namakaryawan']."</td>";
			$tab.="<td nowrap>".getNamaJabatan($bar['kodejabatan'])."</td>";
			$tab.="<td align=center>".$namagol[$bar['kodegolongan']]."</td>";
			$tab.="<td nowrap>".getNamaDept($bar['bagian'])."</td>";
			#$tab.="<td nowrap>".$bar['lokasitugas']." - ".getNamaOrg($bar['lokasitugas'])."</td>";
			$tab.="<td nowrap>".$bar['lokasitugas']."</td>";
			$tab.="<td nowrap>".getNamaTipeKary($bar['tipekaryawan'])."</td>";
			$tab.="<td nowrap>".tanggalnormal($bar['tanggalmasuk'])."</td>";
			
			$diff      = (strtotime(date("Y-m-d"))-strtotime($bar['tanggalmasuk']));
			$years     = floor($diff / (365*60*60*24));
			$months    = floor(($diff - $years * 365*60*60*24)/(30*60*60*24));
			$days      = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
			$jam       = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
			$menit     = floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
			$tab.="<td nowrap>".$years." tahun, ".$months." bulan, ".$days." hari</td>";
			
			
			$tab.="<td align=center>".$bar['penilaian']."</td>";
			$tab.="<td align=center>".$bar['tahun']."</td>";
			$tab.="<td>".$status[$bar['manmanagement']]."</td>";
			if(is_null($bar['id'])){
				$tab.="<td nowrap style=background-color:#ea9999;>Belum diinput</td>";
				$kpi="";
			}else{
				if($bar['posting']=='0'){
					$tab.="<td nowrap style=background-color:#fff2cc;>Belum diposting</td>";
				}else{
					$tab.="<td style=background-color:#93c47d;>Posted</td>";
				}
				$kpi="onclick=\"detailkpi('".$bar['id']."');\" style=font-style:italic;color:blue;cursor:pointer;";
			}
			$tab.="<td align=right ".$kpi.">".hidezerodecimal($totalproporsi,2)."</td>";
			$tab.="<td align=right ".$kpi.">".hidezerodecimal($totalproporsi*($bobotkpi[$bar['manmanagement']]/100),2)."</td>";
			$tab.="<td align=left nowrap>".getKary($bar['namaatasan'])."</td>";
			$tab.="<td align=left nowrap style=background-color:".$colapprov[$bar['approval']].";>".$arrapprov[$bar['approval']]."</td>";
			
			if(is_null($bar['idcv'])){
				$tab.="<td nowrap style=background-color:#ea9999;>Belum diinput</td>"; $cv="";
			}else{
				if($bar['postingcv']=='0'){
					$tab.="<td nowrap style=background-color:#fff2cc;>Belum diposting</td>";
				}else{
					$tab.="<td style=background-color:#93c47d;>Posted</td>";
				}
				$cv="onclick=\"detailcvmm('".$bar['idcv']."');\" style=font-style:italic;color:blue;cursor:pointer;";
			}
			
			$query = "SELECT AVG(nilai) as nilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$bar['idcv']."' and penilai in (SELECT max(penilai) as penilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$bar['idcv']."') GROUP BY id";
			$rata2 = fetchdata($query);
			$tab.="<td align=right ".$cv.">".hidezerodecimal($rata2[0]['nilai'],2)."</td>";
			$tab.="<td align=right ".$cv.">".hidezerodecimal($rata2[0]['nilai']*($bobotcv[$bar['manmanagement']]/100),2)."</td>";
			$tab.="<td align=left nowrap>".getKary($bar['namaatasancv'])."</td>";
			$tab.="<td align=left nowrap style=background-color:".$colapprov[$bar['approvalcv']].";>".$arrapprov[$bar['approvalcv']]."</td>";
			
			if($bar['manmanagement']=='Y'){
				if(is_null($bar['idmm'])){
					$tab.="<td nowrap style=background-color:#ea9999;>Belum diinput</td>"; $mm="";
				}else{
					if($bar['postingmm']=='0'){
						$tab.="<td nowrap style=background-color:#fff2cc;>Belum diposting</td>";
					}else{
						$tab.="<td style=background-color:#93c47d;>Posted</td>";
					}
					$mm="onclick=\"detailcvmm('".$bar['idmm']."');\" style=font-style:italic;color:blue;cursor:pointer;";
				}
				$query = "SELECT AVG(nilai) as nilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$bar['idmm']."' and penilai in (SELECT max(penilai) as penilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$bar['idmm']."') GROUP BY id";
				$rata2 = fetchdata($query);
				$tab.="<td align=right ".$mm.">".hidezerodecimal($rata2[0]['nilai'],2)."</td>";
				$tab.="<td align=right ".$mm.">".hidezerodecimal($rata2[0]['nilai']*($bobotmm[$bar['manmanagement']]/100),2)."</td>";
				
				$tab.="<td align=left nowrap>".getKary($bar['namaatasanmm'])."</td>";
				$tab.="<td align=left nowrap style=background-color:".$colapprov[$bar['approvalmm']].";>".$arrapprov[$bar['approvalmm']]."</td>";
					
			}else{				
				$tab.="<td style=background-color:#bcbcbc></td>";
				$tab.="<td style=background-color:#bcbcbc></td>";
				$tab.="<td style=background-color:#bcbcbc></td>";
				$tab.="<td style=background-color:#bcbcbc></td>";
				$tab.="<td style=background-color:#bcbcbc></td>";
			}
			
			if(is_null($bar['idpas'])){
				$tab.="<td nowrap style=background-color:#ea9999;>Belum diinput</td>"; $pas="";
			}else{
				if($bar['posting']=='0'){
					$tab.="<td nowrap style=background-color:#fff2cc;>Belum diposting</td>";
				}else{
					$tab.="<td style=background-color:#93c47d;>Posted</td>";
				}
			}
			$pas="onclick=\"detail('".$bar['karyawanid']."','".$bar['tahun']."','".$bar['penilaian']."');\" style=font-style:italic;color:blue;cursor:pointer;";
			$tab.="<td align=right ".$pas.">".hidezerodecimal($bar['nilaifinal'],2)."</td>";
			
			$tab.="<td align=left nowrap>".$bar['kategorifinal']."</td>";
			$tab.="<td align=left nowrap>".getKary($bar['atasanpas'])."</td>";
			$tab.="<td align=left nowrap style=background-color:".$colapprov[$bar['approvalpas']].";>".$arrapprov[$bar['approvalpas']]."</td>";
				
		}
		
		 
		$tab.="</tbody></table>";

		if($param['tipe']=='excel'){
			$print = $tab;
			$print.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
			
			$nop = "KPI.xls";
            $xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("kpi", $print);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $tab;
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

function getNamaGol($kode,$kolom='namagolongan'){
	global $dbname;
    global $owlPDO;
    
	$hasil='';
    $str="select ".$kolom." from ".$dbname.".sdm_5golongan where kodegolongan='".$kode."'";
	$res=fetchdata($str);
	$hasil=$res[0][$kolom];
	
	return $hasil;    
}

?>