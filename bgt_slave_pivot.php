<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method = checkPostGet('method', '');
$param  = $_POST;if(count($param)==0){$param= $_GET;}
$nmorg  = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmarus= makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');


$path   = $_SERVER['HTTP_REFERER'];
$path   = explode('/',$path);
$rowfile= count($path)-1;
$file   = $path[$rowfile];
$file   = str_replace(".php","",$file);
$idmenu = makeOption($dbname,'menu','action,id');

$str = "select * from ".$dbname.".pivot_favorit where karyawanid='".$_SESSION['standard']['userid']."' and idmenu='".$idmenu[$file]."' and jenis ='".$param['method']."' order by id asc";
$res = fetchData($str);
$optlap="<option value=''>&nbsp;</option>";
foreach($res as $key => $bar){
	//$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
	$judullap.=$bar['id']."$$$$";
	$datalap.=$bar['data']."$$$$";
	$listopt[$bar['id']]=$bar['label'];
}

$str = "select a.*, b.label, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' and b.jenis ='".$param['method']."' order by a.id asc";
$res = fetchdata($str);
foreach ($res as $bar){
	//$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
	
	$listopt[$bar['id']]=$bar['label'];
	$judullap.=$bar['id']."$$$$";
	$datalap.=$bar['data']."$$$$";
}

$str = "select distinct a.id, a.label from ".$dbname.".pivot_favorit a left join ".$dbname.".pivot_favoritdt b on a.id=b.id where 1=1 and ( a.karyawanid='".$_SESSION['standard']['userid']."' or b.karyawanid='".$_SESSION['standard']['userid']."') and a.idmenu='".$idmenu[$file]."' and a.jenis ='".$param['method']."' order by a.id asc";
$res = fetchdata($str);
foreach ($res as $bar){
	$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
}

$str = "select * from " . $dbname . ".bgt_kode"; 
$res = fetchdata($str);
foreach($res as $bar){
	$kodebudget[$bar['kodebudget']] = $bar['noakuntrk'];
}

$datasort= array();
$inclusions = array();		
switch($method){
	case'sumber':
		$tab="<fieldset><legend>Info</legend><div>";
		switch($param['jenis']){
			case'kebun':
				$tab.="<li>Anggaran - Transaksi - Budget Kebun - Budget Kebun</li>";
			break;
			case'mill':
				$tab.="<li>Anggaran - Transaksi - Budget Pabrik - Budget Pabrik</li>";
			break;
			case'byy':
				$tab.="Sumber data :<li>Data Jurnal.</li>";
			break;
			case'bkm':
				$tab.="Sumber data :<li>Kebun - Transaksi - Buku Kegiatan Mandor.</li>";
			break;
			case'pnn':
				$tab.="Sumber data :<li>Kebun - Proses - Premi Pemanen.</li>";
			break;
			case'atbs':
				$tab.="Sumber data :<li>Kebun - Transaksi - Rekap Angkutan TBS.</li>";
			break;
			case'fee':
				$tab.="Sumber data :<li>Kebun - Proses - Fee Panen.</li>";
			break;
			case'tmb':
				$tab.="Sumber data :<li>Pabrik - Laporan - Timbangan.</li><li>Data juga bisa diakses melalui Telegram <a href=http://t.me/owlksp_robot target='_blank'>click here</a></li>";
			break;
		}
		$tab.="<li style=color:blue;font-weight:bold;>Jika pilihan Jenis berbeda dengan pilihan sebelumnya, pastikan anda Reload Frame terlebih dahulu.</li>";
		$tab.="</div></fieldset>";
		echo $tab; 
	break;
	case'formfav':
		$tab="<table>";
		$tab.="<tr>
					<td>Nama Favorit<td>
					<td>:<td>
					<td><input class=myinputtext id=namafav style=width:200px><td>
					<td><button onclick=favorit2(); class=mybutton>Save</button><td>
				</tr>";
		$tab.="</table>";
		
		$tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable style=min-width:370px>
			<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>Nama Favorit</td>
				<td align=center>Dibuat Oleh</td>
				<td align=center colspan=2>Action</td>";
		$tab.="</tr>
			</thead>
			<tbody id=loadformfav>";
		
		$tab.="
		</tbody>
	</table>";
	echo $tab; 
	break;
	case'getfromfav':
		$str = "select a.*, b.label, b.karyawanid, b.filter, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where b.id='".$param['fromfavorit']."' order by a.id asc";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$filter=$bar['filter'];
		}
		echo $filter;
	break;
	case'loadformfav':
		
		$str = "select * from ".$dbname.".pivot_favorit where karyawanid='".$_SESSION['standard']['userid']."' and idmenu='".$idmenu[$file]."' and jenis ='".$param['tipe']."' order by id asc";
		
		$res = fetchdata($str);
		$no = 0;
		foreach ($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['label']."</td>";
			$tab.="<td align=left>".getNamaKaryawan($bar['karyawanid'])."</td>";
			$tab.="<td align=center width=25px><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete' onclick=deletefield('".$bar['id']."','ht')></td>";
			$tab.="<td align=center width=25px><img src='images/orgicon.png' class='zImgBtn' title='Assign' onclick=setformuser('".$bar['id']."','".$bar['jenis']."')></td>";
			$tab.="</tr>";
		}
		
		$str = "select a.*, b.label, b.karyawanid, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' and b.jenis ='".$param['tipe']."' order by a.id asc";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['label']."</td>";
			$tab.="<td align=left>".getNamaKaryawan($bar['karyawanid'])."</td>";
			$tab.="<td align=center width=25px colspan=2><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete' onclick=deletefield('".$bar['id']."','dt','".$_SESSION['standard']['userid']."')></td>";
			//$tab.="<td align=center width=25px><img src='images/orgicon.png' class='zImgBtn' title='Assign' onclick='setformuser('".$bar['id']."','dt')'></td>";
			$tab.="</tr>";
		}
		
		$tab.="</tr>";
		echo $tab; 
	break;
	
	case'favorit':
		try {
			$owlPDO->beginTransaction();
				$filter['kodeorg']= $param['kodeorg'];
				$filter['tahun']  = $param['periode'];
				$filter['tipe']   = $param['tipe'];
				
				$data = array(
					'idmenu'    => $idmenu[$file],
					'jenis'     => $param['tipe'],
					'karyawanid'=> $_SESSION['standard']['userid'],
					'label'     => $param['namafav'],
					'filter'    => json_encode($filter),
					'data'      => $param['data']
				);
				$cols = array();
				foreach($data as $key=>$row) {
					$cols[] = $key;
				}
				$query = insertQuery($dbname,'pivot_favorit',$data,$cols);#exit("error".$query);
				$owlPDO->exec($query);
				
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	case'prdpks':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and millcode like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(millcode,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('PABRIK','UNIT','SUPPLIER','BULAN','DATA','JUMLAH');
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("PABRIK","UNIT","SUPPLIER");
		$col = array("DATA");
		$val = array("JUMLAH");
		
		$datasort = array("TBS","CPO","KER");
		
		#ambil prd bgt
		$bulan = '12';			
		$tahun = substr($param['tahun'],0,4);
		
		
		$str = " select * from ".$dbname.".organisasi";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
		}
		$str = " select * from ".$dbname.".log_5supplier";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmorg[$bar['supplierid']]=$bar['namasupplier'];
		}
		
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		
		$str = " select * from ".$dbname.".bgt_produksi_pks_vw a where 1=1 ".$whb."";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(getNamaOrg($bar['kodeunit'])!=''){
				$unit=getNamaOrg($bar['kodeunit']);
			}else{
				$unit="EXTERNAL / SWADAYA";
			}
			if($nmorg[$bar['kodesupplier']]!=''){
				$supp=$nmorg[$bar['kodesupplier']];
			}else{
				$supp="EXTERNAL / SWADAYA";
			}
			for($i=1;$i<=intval($bulan);$i++){
				$r="olah".addZero($i,2);
				$c="kgcpo".addZero($i,2);
				$k="kgker".addZero($i,2);
				
				$data[]=array(
					getNamaOrg($bar['millcode']),
					$unit,
					$supp,
					$tahun."-".addZero($i,2),
					"TBS",
					$bar[$r]
				);
				$data[]=array(
					getNamaOrg($bar['millcode']),
					$unit,
					$supp,
					$tahun."-".addZero($i,2),
					"CPO",
					$bar[$c]
				);
				$data[]=array(
					getNamaOrg($bar['millcode']),
					$unit,
					$supp,
					$tahun."-".addZero($i,2),
					"KER",
					$bar[$k]
				);
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'prdkbn':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and kodeunit like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(kodeunit,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('REGION','UNIT','DIVISI','TT','BLOK','BULAN','DATA','JUMLAH');
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("REGION","UNIT","DIVISI");
		$col = array("DATA");
		$val = array("JUMLAH");
		
		$datasort = array("JJG","KG");
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		#ambil prd bgt
		$bulan = '12';			
		$tahun = substr($param['tahun'],0,4);
		
		
		$nmreg = makeOption($dbname,'bgt_regional_assignment','kodeunit,subregional');
		
		$str = " select * from ".$dbname.".bgt_produksi_kebun a where 1=1 ".$whb."";
		$res = fetchdata($str);
		foreach($res as $bar){
			for($i=1;$i<=intval($bulan);$i++){
				$r="kg".addZero($i,2);
				$j="jjg".addZero($i,2);
				
				$data[]=array(
					$nmreg[$bar['kodeunit']],
					getNamaOrg($bar['kodeunit']),
					getNamaOrg(substr($bar['kodeblok'],0,6)),
					$bar['tahuntanam'],
					getNamaOrg($bar['kodeblok']),
					$tahun."-".addZero($i,2),
					"JJG",
					$bar[$j]
				);
				$data[]=array(
					$nmreg[$bar['kodeunit']],
					getNamaOrg($bar['kodeunit']),
					getNamaOrg(substr($bar['kodeblok'],0,6)),
					$bar['tahuntanam'],
					getNamaOrg($bar['kodeblok']),
					$tahun."-".addZero($i,2),
					"KG",
					$bar[$r]
				);
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'ws':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and kodeorg like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('BGT/PTA','UNIT','KODE VHC','NOPOL','WORKSHOP','NOAKUN','NAMAAKUN','TIPE','JENIS','KODE BGT','KODE BARANG','NAMA BARANG','DATA','JUMLAH');
		$numb=array(19);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","WORKSHOP");
		$col = array("DATA");
		$val = array("JUMLAH");
		
		$arrdata = array('VOL'=>'volume','JLH'=>'jumlah','RP'=>'rupiah');
		$datasort = array("VOL","JLH","RP");
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmakun= makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$satkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
		$nmtipe= makeOption($dbname,'bgt_kode','kodebudget,nama');
		$nmtt  = makeOption($dbname,'bgt_blok','kodeblok,thntnm',"tahunbudget='".$param['tahun']."'");
		
		$whb.=" and tipebudget = 'WS'";	
		$whb.=" and (pta='BGT' or (pta='PTA' and statuspta='1'))";
		$str=" select * from ".$dbname.".bgt_budget where 1=1 ".$whb."";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){
				$kodebgt="Tenaga Kerja";
			}elseif(substr($bar['kodebudget'],0,2)=='M-'){
				$kodebgt="Material";
			}else{
				$kodebgt=$nmtipe[$bar['kodebudget']];
			}
			if($bar['noakun']==''){
				$bar['noakun'] = $kodebudget[$bar['kodebudget']]; 
			}
			foreach($arrdata as $key => $value){				
				$data[]=array(
					$bar['pta'],
					getNamaOrg(substr($bar['kodeorg'],0,4)),
					$bar['kodevhc'],
					getNopol($bar['kodevhc']),
					getNamaOrg(substr($bar['kodeorg'],0,6)),
					$bar['noakun'],
					getNamaAkun($bar['noakun']),
					strtoupper(strtolower($bar['tipebudget'])),
					strtoupper(strtolower($kodebgt)),
					strtoupper(strtolower($nmtipe[$bar['kodebudget']])),
					$bar['kodebarang'],
					cleanSpecialChar($nmbrg[$bar['kodebarang']]),
					$key,
					$bar[$value]
				);
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'trk':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and kodeorg like '".$param['kodeorg']."%'";	
			$whr.=" and kodetraksi like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
			$whr.=" and substr(kodetraksi,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
			$whr.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('BGT/PTA','UNIT','KODE VHC','NOPOL','TRAKSI','NOAKUN','NAMAAKUN','TIPE','JENIS','KODE BGT','KODE BARANG','NAMA BARANG','DATA','JUMLAH');
		$numb=array(19);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","TRAKSI");
		$col = array("DATA");
		$val = array("JUMLAH");
		
		$arrdata = array('VOL'=>'volume','JLH'=>'jumlah','RP'=>'rupiah');
		$datasort = array("HM/KM","VOL","JLH","RP");
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmtipe= makeOption($dbname,'bgt_kode','kodebudget,nama');
		$nmtt  = makeOption($dbname,'bgt_blok','kodeblok,thntnm',"tahunbudget='".$param['tahun']."'");
		
		$str=" select * from ".$dbname.".bgt_vhc_jam where 1=1 ".$whr."";
		$res = fetchdata($str);
		foreach($res as $bar){
			foreach($arrdata as $key => $value){				
				$data[]=array(
					"BGT",
					getNamaOrg(substr($bar['kodetraksi'],0,4)),
					$bar['kodevhc'],
					getNopol($bar['kodevhc']),
					getNamaOrg($bar['kodetraksi']),
					"",
					"",
					"HM/KM",
					"HM/KM",
					"HM/KM",
					"",
					"",
					"HM/KM",
					$bar['jumlahjam']
				);
			}
		}
		
		$whb.=" and (pta='BGT' or (pta='PTA' and statuspta='1'))";
		$whb.=" and tipebudget = 'TRK'";	
		$str=" select * from ".$dbname.".bgt_budget where 1=1 ".$whb."";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){
				$kodebgt="Tenaga Kerja";
			}elseif(substr($bar['kodebudget'],0,2)=='M-'){
				$kodebgt="Material";
			}else{
				$kodebgt=$nmtipe[$bar['kodebudget']];
			}
			if($bar['noakun']==''){
				$bar['noakun'] = $kodebudget[$bar['kodebudget']]; 
			}
			foreach($arrdata as $key => $value){				
				$data[]=array(
					$bar['pta'],
					getNamaOrg(substr($bar['kodeorg'],0,4)),
					$bar['kodevhc'],
					getNopol($bar['kodevhc']),
					getNamaOrg(substr($bar['kodeorg'],0,6)),
					$bar['noakun'],
					getNamaAkun($bar['noakun']),
					strtoupper(strtolower($bar['tipebudget'])),
					strtoupper(strtolower($kodebgt)),
					strtoupper(strtolower($nmtipe[$bar['kodebudget']])),
					$bar['kodebarang'],
					cleanSpecialChar($nmbrg[$bar['kodebarang']]),
					$key,
					$bar[$value]
				);
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'kebun':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and kodeorg like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('BGT/PTA','UNIT','DEPT','KODE VHC','DIVISI','TT','BLOK','TIPE','JENIS','KODE BGT','KODE KLP','NAMA KLP','ARUSKAS','NM ARUSKAS','NO AKUN','NAMA AKUN','KODE KEG','NAMA KEG','KODE BARANG','NAMA BARANG','KETERANGAN','DATA','JUMLAH');
		$numb=array(20);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","DIVISI");
		$col = array("NAMA KLP","DATA");
		$val = array("JUMLAH");
		
		$arrdata=array('VOL'=>'volume','JLH'=>'jumlah','RP'=>'rupiah');
		$datasort = array("VOL","JLH","RP");
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		$nmbrg= makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmakun= makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$satkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
		$nmtipe= makeOption($dbname,'bgt_kode','kodebudget,nama');
		$nmtt  = makeOption($dbname,'bgt_blok','kodeblok,thntnm',"tahunbudget='".$param['tahun']."'");
		
		$whb.=" and tipebudget = 'ESTATE' and kodebudget!='UMUM'";	
		$whb.=" and (pta='BGT' or (pta='PTA' and statuspta='1'))";
		$str=" select * from ".$dbname.".bgt_budget where 1=1 ".$whb."";
		// exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){
				$kodebgt="Tenaga Kerja";
			}elseif(substr($bar['kodebudget'],0,2)=='M-'){
				$kodebgt="Material";
			}else{
				$kodebgt=$nmtipe[$bar['kodebudget']];
			}
			
			foreach($arrdata as $key => $value){				
				$data[]=array(
					$bar['pta'],
					substr($bar['kodeorg'],0,4),
					$bar['dept'],
					$bar['kodevhc'],
					substr($bar['kodeorg'],0,6),
					$nmtt[$bar['kodeorg']],
					$nmorg[$bar['kodeorg']],
					strtoupper(strtolower($bar['tipebudget'])),
					strtoupper(strtolower($kodebgt)),
					strtoupper(strtolower($nmtipe[$bar['kodebudget']])),
					substr($bar['noakun'],0,3),
					$nmakun[substr($bar['noakun'],0,3)],
					$bar['aruskas'],
					$nmarus[$bar['aruskas']],
					$bar['noakun'],
					$nmakun[$bar['noakun']],
					$bar['kegiatan'],
					$nmkeg[$bar['kegiatan']],
					$bar['kodebarang'],
					cleanSpecialChar($nmbrg[$bar['kodebarang']]),
					cleanSpecialChar($bar['keterangan']),
					$key,
					$bar[$value]
				);
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'mill':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and kodeorg like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		

		$datae[]=array('BGT/PTA','UNIT','DEPT','KODE VHC','STATION','MESIN','TIPE','JENIS','KODE BGT','KODE KLP ACC','NAMA KLP ACC','SUB KLP ACC','NAMA SUB KLP ACC','ARUSKAS','NM ARUSKAS','NO AKUN','NAMA AKUN','KODE KEG','NAMA KEG','KODE BARANG','NAMA BARANG','KETERANGAN','DATA','JUMLAH');
		$numb=array(22);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","STATION");
		$col = array("NAMA KLP","DATA");
		$val = array("JUMLAH");
		
		//$arrdata = array('VOL'=>'volume','JLH'=>'jumlah','RP'=>'rupiah','Jlh_Jan'=>'fis01','Rp_Jan'=>'rp01','Jlh_Feb'=>'fis02','Rp_Feb'=>'rp02','Jlh_Mar'=>'fis03','Rp_Mar'=>'rp03','Jlh_Apr'=>'fis04','Rp_Apr'=>'rp04','Jlh_Mei'=>'fis05','Rp_Mei'=>'rp05','Jlh_Jun'=>'fis06','Rp_Jun'=>'rp06','Jlh_Jul'=>'fis07','Rp_Jul'=>'rp07','Jlh_Ags'=>'fis08','Rp_Ags'=>'rp08','Jlh_Sep'=>'fis09','Rp_Sep'=>'rp09','Jlh_Okt'=>'fis10','Rp_Okt'=>'rp10','Jlh_Nov'=>'fis11','Rp_Nov'=>'rp11','Jlh_Des'=>'fis12','Rp_Des'=>'rp12');
		$arrdata = array('VOL'=>'volume','JLH'=>'jumlah','RP'=>'rupiah','Rp_Jan'=>'rp01','Rp_Feb'=>'rp02','Rp_Mar'=>'rp03','Rp_Apr'=>'rp04','Rp_Mei'=>'rp05','Rp_Jun'=>'rp06','Rp_Jul'=>'rp07','Rp_Ags'=>'rp08','Rp_Sep'=>'rp09','Rp_Okt'=>'rp10','Rp_Nov'=>'rp11','Rp_Des'=>'rp12');
		
		$datasort = array('VOL','JLH','RP','Jlh_Jan','Rp_Jan','Jlh_Feb','Rp_Feb','Jlh_Mar','Rp_Mar','Jlh_Apr','Rp_Apr','Jlh_Mei','Rp_Mei','Jlh_Jun','Rp_Jun','Jlh_Jul','Rp_Jul','Jlh_Ags','Rp_Ags','Jlh_Sep','Rp_Sep','Jlh_Okt','Rp_Okt','Jlh_Nov','Rp_Nov','Jlh_Des','Rp_Des');
		
		$inclusions = array(
			'BGT/PTA'=>array('BGT'),
			'DATA'=>array('Rp_Jan','Rp_Feb','Rp_Mar','Rp_Apr','Rp_Mei','Rp_Jun','Rp_Jul','Rp_Ags','Rp_Sep','Rp_Okt','Rp_Nov','Rp_Des')
		);
		
		$nmbrg= makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmakun= makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$satkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
		$nmtipe= makeOption($dbname,'bgt_kode','kodebudget,nama');
		
		$tempno=0;
		foreach($arrdata as $key => $value){
			$tempno++;
			$isi.=",sum(".$value.") as ".$value;
			if($tempno>1){			
			}else{
				// $isi.="sum(".$value.") as ".$value;
			}
		}
		
		$whb.=" and tipebudget = 'MILL' and kodebudget!='UMUM'";
		$whb.=" and (pta='BGT' or (pta='PTA' and statuspta='1'))";		
		$str=" select * from ".$dbname.".bgt_budget a left join ".$dbname.".bgt_distribusi b on a.kunci=b.kunci where 1=1 ".$whb."";
		// exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){
				$kodebgt="Tenaga Kerja";
			}elseif(substr($bar['kodebudget'],0,2)=='M-'){
				$kodebgt="Material";
			}else{
				$kodebgt=$nmtipe[$bar['kodebudget']];
			}
			
			foreach($arrdata as $key => $value){				
				$data[]=array(
					$bar['pta'],
					substr($bar['kodeorg'],0,4),
					cleanSpecialChar($bar['dept']),
					cleanSpecialChar($bar['kodevhc']),
					$nmorg[substr($bar['kodeorg'],0,6)],
					cleanSpecialChar($nmorg[$bar['kodeorg']]),
					cleanSpecialChar($bar['tipebudget']),
					cleanSpecialChar(strtoupper($kodebgt)),
					cleanSpecialChar($nmtipe[$bar['kodebudget']]),
					cleanSpecialChar(substr($bar['noakun'],0,3)),
					cleanSpecialChar($nmakun[substr($bar['noakun'],0,3)]),
					cleanSpecialChar(substr($bar['noakun'],0,5)),
					cleanSpecialChar($nmakun[substr($bar['noakun'],0,5)]),
					cleanSpecialChar($bar['aruskas']),
					cleanSpecialChar($nmarus[$bar['aruskas']]),
					cleanSpecialChar($bar['noakun']),
					cleanSpecialChar($nmakun[$bar['noakun']]),
					cleanSpecialChar($bar['kegiatan']),
					cleanSpecialChar($nmkeg[$bar['kegiatan']]),
					cleanSpecialChar($bar['kodebarang']),
					cleanSpecialChar($nmbrg[$bar['kodebarang']]),
					cleanSpecialChar(strtoupper($bar['keterangan'])),
					$key,
					$bar[$value]
				);
			}
			
			/* for($i=1;$i<=intval($bulan);$i++){
				$r="kg".addZero($i,2);
				$kgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($i,2)][$tahun."-".addZero($i,2)."-01"]+=$bar[$r];
			} */
		}
		
		
		
		echo json_encode($data,JSON_UNESCAPED_UNICODE)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'umm':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and kodeorg like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('BGT/PTA','UNIT','DEPT','KODE VHC','TIPE','JENIS','KODE BGT','KODE KLP','NAMA KLP','ARUSKAS','NM ARUSKAS','NO AKUN','NAMA AKUN','KETERANGAN','KODE BARANG','NAMA BARANG','DATA','JUMLAH');
		$numb=array(19);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","NAMA KLP");
		$col = array("DATA");
		$val = array("JUMLAH");
		
		$arrdata=array('RP'=>'rupiah');
		$datasort = array("RP");
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmakun= makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$satkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
		$nmtipe= makeOption($dbname,'bgt_kode','kodebudget,nama');
		$nmdept= makeOption($dbname,'sdm_5departemen','kode,nama');
		$nmtt  = makeOption($dbname,'bgt_blok','kodeblok,thntnm',"tahunbudget='".$param['tahun']."'");
		
		$whb.=" and kodebudget='UMUM'";	
		$whb.=" and pta='BGT' or (pta='PTA' and statuspta='1')";
		$str=" select * from ".$dbname.".bgt_budget where 1=1 ".$whb."";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){
				$kodebgt="Tenaga Kerja";
			}elseif(substr($bar['kodebudget'],0,2)=='M-'){
				$kodebgt="Material";
			}else{
				$kodebgt=$nmtipe[$bar['kodebudget']];
			}
			
			foreach($arrdata as $key => $value){				
				$data[]=array(
					$bar['pta'],
					substr($bar['kodeorg'],0,4),
					$nmdept[$bar['dept']],
					$bar['kodevhc'],
					$bar['tipebudget'],
					$kodebgt,
					$nmtipe[$bar['kodebudget']],
					substr($bar['noakun'],0,3),
					$nmakun[substr($bar['noakun'],0,3)],
					$bar['aruskas'],
					$nmarus[$bar['aruskas']],
					$bar['noakun'],
					$nmakun[$bar['noakun']],
					$bar['keterangan'],
					$bar['kodebarang'],
					cleanSpecialChar($nmbrg[$bar['kodebarang']]),
					$key,
					$bar[$value]
				);
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'ummsbr':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and kodeorg like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('BGT/PTA','UNIT','DEPT','KODE VHC','TIPE','JENIS','KODE BGT','KODE KLP','NAMA KLP','ARUSKAS','NM ARUSKAS','NO AKUN','NAMA AKUN','KETERANGAN','KODE BARANG','NAMA BARANG','KALENDERISASI','JUMLAH');
		$numb=array(19);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","NAMA KLP");
		$col = array("KALENDERISASI");
		$val = array("JUMLAH");
		
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		
		$datasort = array("RP");
		
		$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmakun= makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$satkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
		$nmtipe= makeOption($dbname,'bgt_kode','kodebudget,nama');
		$nmtt  = makeOption($dbname,'bgt_blok','kodeblok,thntnm',"tahunbudget='".$param['tahun']."'");
		
		$whb.=" and kodebudget='UMUM'";	
		$whb.=" and a.pta='BGT' or (a.pta='PTA' and a.statuspta='1')";
		$str=" select * from ".$dbname.".bgt_budget a left join ".$dbname.".bgt_distribusi b on a.kunci=b.kunci where 1=1 ".$whb."";
		//exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){
				$kodebgt="Tenaga Kerja";
			}elseif(substr($bar['kodebudget'],0,2)=='M-'){
				$kodebgt="Material";
			}else{
				$kodebgt=$nmtipe[$bar['kodebudget']];
			}
			
			for($i=1;$i<=$bulan;$i++){
				$data[]=array(
					$bar['pta'],
					substr($bar['kodeorg'],0,4),
					$bar['dept'],
					$bar['kodevhc'],
					$bar['tipebudget'],
					$kodebgt,
					$nmtipe[$bar['kodebudget']],
					substr($bar['noakun'],0,3),
					$nmakun[substr($bar['noakun'],0,3)],
					$bar['aruskas'],
					$nmarus[$bar['aruskas']],
					$bar['noakun'],
					$nmakun[$bar['noakun']],
					$bar['keterangan'],
					$bar['kodebarang'],
					cleanSpecialChar($nmbrg[$bar['kodebarang']]),
					"(".$i.") ".numToMonth($i),
					$bar['rp'.addZero($i,2)]
				);
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'kap':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and kodeunit like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(kodeunit,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('BGT/PTA','UNIT','KODE JENIS KAPITAL','JENIS KAPITAL','LOKASI','KETERANGAN','ARUSKAS','NM ARUSKAS','KODE BARANG','NAMA BARANG','DATA','JUMLAH');
		$numb=array(6);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","JENIS KAPITAL");
		$col = array("DATA");
		$val = array("JUMLAH");
		
		$arrdata=array('RP'=>'rupiah');
		$datasort = array("RP");
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		$nmtpass= makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
		
		$whb.=" and pta='BGT' or (pta='PTA' and statuspta='1')";
		$str=" select * from ".$dbname.".bgt_kapital where 1=1 ".$whb."";
		$res = fetchdata($str);
		foreach($res as $bar){
			$data[]=array(
				cleanSpecialChar($bar['pta']),
				cleanSpecialChar(substr($bar['kodeunit'],0,4)),
				$bar['jeniskapital'],
				$nmtpass[$bar['jeniskapital']],
				cleanSpecialChar($bar['lokasi']),
				cleanSpecialChar($bar['keterangan']),
				cleanSpecialChar($bar['aruskas']),
				cleanSpecialChar($nmarus[$bar['aruskas']]),
				cleanSpecialChar($bar['kodebarang']),
				cleanSpecialChar(getNamaBrg($bar['kodebarang'])),
				"JUMLAH",
				$bar['jumlah']
			);
			$data[]=array(
				cleanSpecialChar($bar['pta']),
				cleanSpecialChar(substr($bar['kodeunit'],0,4)),
				$bar['jeniskapital'],
				$nmtpass[$bar['jeniskapital']],
				cleanSpecialChar($bar['lokasi']),
				cleanSpecialChar($bar['keterangan']),
				cleanSpecialChar($bar['aruskas']),
				cleanSpecialChar($nmarus[$bar['aruskas']]),
				cleanSpecialChar($bar['kodebarang']),
				cleanSpecialChar(getNamaBrg($bar['kodebarang'])),
				"RUPIAH",
				$bar['hargatotal']
			);
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
	case'bgt':
		$datax=array();
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$whb.=" and a.kodeorg like '".$param['kodeorg']."%'";	
		}else{
			$whb.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['tahun']!=''){
			$whb.=" and a.tahunbudget = '".$param['tahun']."'";	
		}		

		$bulan='12';
		$tahun=$param['tahun'];
		
		$datae[]=array('BGT/PTA','PT','UNIT','TIPE ORG','DEPT','KODE VHC','DIV/STAT','TT','TIPE','JENIS','KODE BGT','NAMA KODE BGT','KODE KLP','NAMA KLP','KODE SUBKLP','NAMA SUBKLP','ARUSKAS','NM ARUSKAS','NO AKUN','NAMA AKUN','KODE KEG','NAMA KEG','KODE BARANG','NAMA BARANG','KETERANGAN','DATA','JUMLAH');
		
		$numb=array(21);
		if($param['jenis']!='data'){
			$datax=$datae;
		}
		$row = array("UNIT");
		$col = array("NAMA KLP","DATA");
		$val = array("JUMLAH");
		$datasort = array("RP");
		$inclusions = array(
			'BGT/PTA'=>array('BGT')
		);
		$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmakun= makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$satkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
		$tporg = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
		$pt    = makeOption($dbname,'organisasi','kodeorganisasi,induk');
		$nmtipe= makeOption($dbname,'bgt_kode','kodebudget,nama');
		$nmtt  = makeOption($dbname,'bgt_blok','kodeblok,thntnm',"tahunbudget='".$param['tahun']."'");
		
		$whb.=" and a.tipebudget not in ('TRK','WS')";	
		$whb.=" and a.pta='BGT' or (a.pta='PTA' and a.statuspta='1')";	
		
		
		$str=" select sum(rupiah) as rp, substr(kodeorg,1,6) as divisi, substr(kodeorg,1,4) as unit, b.thntnm, a.* from ".$dbname.".bgt_budget a left join ".$dbname.".bgt_blok b on a.tahunbudget=b.tahunbudget and b.kodeblok=a.kodeorg where 1=1 ".$whb." group by unit, divisi, tipebudget, kodebudget, noakun, kegiatan, kodevhc, kodebarang, pta, thntnm";
		// exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){
				$kodebgt="Tenaga Kerja";
			}elseif(substr($bar['kodebudget'],0,2)=='M-'){
				$kodebgt="Material";
			}else{
				$kodebgt=$nmtipe[$bar['kodebudget']];
			}
		
			$datax[]=array(
				$bar['pta'],
				$pt[$bar['unit']],
				$bar['unit'],
				$tporg[$bar['unit']],
				$bar['dept'],
				$bar['kodevhc'],
				$bar['divisi'],
				$bar['thntnm'],
				strtoupper($bar['tipebudget']),
				strtoupper($kodebgt),
				strtoupper($bar['kodebudget']),
				strtoupper($nmtipe[$bar['kodebudget']]),
				substr($bar['noakun'],0,3),
				strtoupper($nmakun[substr($bar['noakun'],0,3)]),
				substr($bar['noakun'],0,5),
				strtoupper($nmakun[substr($bar['noakun'],0,5)]),
				$bar['aruskas'],
				$nmarus[$bar['aruskas']],
				$bar['noakun'],
				strtoupper($nmakun[$bar['noakun']]),
				$bar['kegiatan'],
				strtoupper($nmkeg[$bar['kegiatan']]),
				$bar['kodebarang'],
				strtoupper(cleanSpecialChar($nmbrg[$bar['kodebarang']])),
				strtoupper(cleanSpecialChar($bar['keterangan'])),
				"RP",
				$bar['rp']
			);
			
		}
		
		echo json_encode($datax)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap."####".json_encode($inclusions);
	break;
}

function cleanSpecialChar($string) {
	$hasil='';
    $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.
    $string = preg_replace('/-+/',' ',$string);
    $string = preg_replace('/\s+/', '-', trim($string)); 
    return $string;
	
    // $hasil=preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	// return $hasil;
}

function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
   $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

   return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}
function clearsym($tulisan){
	$hasil='';
	$hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $tulisan); //remove non-ascii chars
	return $hasil;
}

// echo count($datax);
// // echo"<pre>";
// // print_r($data);
// // echo"</pre>";
// exit("error");

// if($param['jenis']=='data'){
	// $tab="<table id=pvtTable cellpadding=1 cellspacing=1 border=0 class='sortable' width='100%' data-scroll-x='true' scroll-collapse='false'>
		// <thead>
			// <tr>";
			// foreach($datae as $key => $var){
				// foreach($var as $val){
					// if($key==0){
						// $tab.="<th>".$val."</th>";
						// $jlhcolhead++;
					// }
				// }
			// }
		
		// $tab.="</tr>
			// </thead><tbody>";
		// $tab.="</tbody><tfoot>
			// <tr>";
			// foreach($datae as $key => $var){
				// foreach($var as $val){
					// if($key==0){					
						// $tab.="<th>".$val."</th>";
					// }
				// }
			// }
		
	// $tab.="</tr></tfoot>";	
	// $tab.="</table>";
	// $tab.="<fieldset style=float:left;><legend>Show/Hide</legend><div>";
		// $e=0;
		// foreach($datae as $key => $var){
			// foreach($var as $val){
				// if($key==0){
					// if($jlhcolhead>8){						
						// $tab.="<button class=\"dt-button\" data-column=".$e.">".substr($val,0,4)."...</button>";
					// }else{
						// $tab.="<button class=\"dt-button\" data-column=".$e.">".$val."</button>";
					// }
					// $e++;
				// }
			// }
		// }
	// $tab.="</div></fieldset>";
	
	// echo $tab."####".json_encode($data)."####".json_encode($numb);
// }elseif($method=='sumber'){
	// echo $tab;
// }else{	
	// echo json_encode($data)."####".json_encode($row)."####".json_encode($col)."####".json_encode($val)."####".json_encode($datasort);
// }

?>
