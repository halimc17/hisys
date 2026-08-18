<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method       = checkPostGet('method','');
$unit         = checkPostGet('unit','');
$blok         = checkPostGet('blok','');
$divisi       = checkPostGet('divisi','');
$kegiatan     = checkPostGet('kegiatan','');
$find_divisi  = checkPostGet('find_divisi','');
$find_blok    = checkPostGet('find_blok','');
$find_tt      = checkPostGet('find_tt','');
$tahuntanam   = checkPostGet('tahuntanam','');
$muat_tphpks  = checkPostGet('muat_tphpks','');
$muat_rampks  = checkPostGet('muat_rampks','');
$angkut_tphpks= checkPostGet('angkut_tphpks','');
$angkut_rampks= checkPostGet('angkut_rampks','');
$muat_tphpks  =str_replace(",","",$muat_tphpks);
$muat_rampks  =str_replace(",","",$muat_rampks);
$angkut_tphpks=str_replace(",","",$angkut_tphpks);
$angkut_rampks=str_replace(",","",$angkut_rampks);
$pkstujuan    = checkPostGet('pkstujuan','');
$jenisvhc     = checkPostGet('jenisvhc','');
$pkstujuanht  = checkPostGet('pkstujuanht','');
$jnskendht    = checkPostGet('jnskendht','');
$namafee      = checkPostGet('namafee','');
$jenisfeex    = checkPostGet('jenisfeex','');
$jenisfee     = checkPostGet('jenisfee','');
$rpfee        = checkPostGet('rpfee','');
$keyfee       = checkPostGet('key','');
$nofee        = checkPostGet('no','');
$rpfee        =str_replace(",","",$rpfee);

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$sql = "SELECT * FROM " . $dbname . ".keu_5akun";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optakun[$bar['noakun']]=$bar['namaakun'];
}

$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optakun[$bar['kodekegiatan']]=$bar['namakegiatan'];
}

$nmorg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmvhc=makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
$nmvhc['GLOBAL']='GLOBAL';
switch ($method) {
	case'getblok':
		$opt = "<option value=''></option>";
		$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe ='BLOK' and kodeorganisasi like '".$find_divisi."%'";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opt.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
		}
		
		$opttt = "<option value=''></option>";
		$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where kodeorg like '".$find_divisi."%' order by tahuntanam asc";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opttt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
		}
		
		echo $opt."####".$opttt;
	break;
	case'getfindtt':
		$opttt = "<option value=''></option>";
		$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where kodeorg like '".$find_blok."%' order by tahuntanam asc";
		$qry = fetchdata($sql);
		foreach($qry as $bar){
			$opttt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
		}
		echo $opttt;
	break;
	case'gettahuntanam':
		$opt = "<option value=''>".$_SESSION['lang']['all']."</option>";
		$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe ='AFDELING' and kodeorganisasi like '".$param['unit']."%'";
		$qry = fetchdata($sql);
		foreach($qry as $bar){
			$opt.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
		}
		
		if($param['unit']!=''){
			$wh=" and kodeorg like '".$param['unit']."%'";
		}	
		if($divisi!=''){
			$wh=" and kodeorg like '".$param['divisi']."%'";
		}
		
		
		$opttt = "<option value=''>".$_SESSION['lang']['all']."</option>";
		$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where 1=1 ".$wh." order by tahuntanam asc";
		$qry = fetchdata($sql);
		foreach($qry as $bar){
			$opttt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
		}
		if($param['tahuntanam']!=''){
			$wh.=" and tahuntanam = '".$param['tahuntanam']."'";
		}
		
		$blok = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sql = "SELECT * FROM " . $dbname . ".setup_blok where 1=1 ".$wh." order by kodeorg asc";
		$qry = fetchdata($sql);
		foreach($qry as $bar){
			$blok.="<option value=" . $bar['kodeorg'] . ">" . getNamaOrg($bar['kodeorg']) . "</option>";
		}
		
		$optnamafee = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sql = "SELECT * FROM " . $dbname . ".kebun_5namafee where status=1 and kodeorg='".$param['unit']."' order by nama asc";
		$qry = fetchdata($sql);
		foreach($qry as $bar){
			$optnamafee.="<option value=" . $bar['id'] . " ".$i.">" . $bar['nama'] . "</option>";
		}
		$sql = "SELECT distinct supplierid FROM " . $dbname . ".log_5supkelompok where status=1 AND TIPE='SUPPLIERTBSKUD'";
		$qry = fetchdata($sql);
		foreach($qry as $bar){
			$optnamafee.="<option value=" . $bar['supplierid'] . " ".$i.">" . getNamaSupplier($bar['supplierid']) . "</option>";
		}

		echo $opt."####".$opttt."####".$blok."####".$optnamafee;
	break;
	
	case 'insert':
		try {
		$owlPDO->beginTransaction();
			if($param['unit']==''){				
				throw new PDOException("Unit wajib diisi.");
			}
			
			if($param['blok']==''){				
				throw new PDOException("Blok wajib diisi.");
			}
			
			if($param['jenisvhc']==''){				
				throw new PDOException("Jenis Kend wajib diisi.");
			}
			if($param['namafee']==''){				
				throw new PDOException("Nama wajib diisi.");
			}
			if($param['namafee']==''){				
				throw new PDOException("Nama wajib diisi.");
			}
			if($param['jenis']==''){				
				throw new PDOException("No Akun wajib diisi.");
			}
			if($param['jenisfee']==''){				
				throw new PDOException("Jenis wajib diisi.");
			}
			if($param['rpfee']=='' or $param['rpfee']=='0'){				
				throw new PDOException("Rupiah wajib diisi.");
			}
			
			$str = "delete from ".$dbname.".kebun_5daftarfee where blok = '".$blok."' and id='".$param['namafee']."' and jenis='".$param['jenis']."' and jenisfee='".$param['jenisfee']."' and jenisvhc='".$param['jenisvhc']."'";
			$owlPDO->exec($str);
			
			$datafee=array();
			$datafee = array(
				'blok'    => $param['blok'],
				'id'      => $param['namafee'],
				'jenisfee'=> $param['jenisfee'],
				'jenis'   => $param['jenis'],
				'jenisvhc'=> $param['jenisvhc'],
				'rp'      => $param['rpfee']
			);
			
			$colsfee = array();
			foreach($datafee as $key=>$row) {
					$colsfee[] = $key;
			}
			$str = insertQuery($dbname,'kebun_5daftarfee',$datafee,$colsfee);
			$owlPDO->exec($str);
		
		
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
	case 'delete':
		$str = "delete from ".$dbname.".kebun_5daftarfee where blok = '".$blok."' and id='".$param['nama']."' and jenis='".$param['jenis']."' and jenisfee='".$param['jenisfee']."' and jenisvhc='".$param['jenisvhc']."'";
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
    case'loaddata':
		$tab="<table border=0 cellpadding=5 class=sortable cellspacing=1 style=min-width:820px>
				<thead>
					<tr class=rowheader style=font-weight:bold>
						<th align=center rowspan=2>No</th> 
						<th align=center rowspan=2>".$_SESSION['lang']['blok']."</th> 
						<th align=center rowspan=2>".$_SESSION['lang']['namablok']."</th> 
						<th align=center rowspan=2>".$_SESSION['lang']['nama']."</th> 
						<th align=center rowspan=2>Jenis Kend</th> 
						<th align=center rowspan=2>".$_SESSION['lang']['jenis']."</th> 
						<th align=center rowspan=2>".$_SESSION['lang']['akun']."</th> 
						<th align=center rowspan=2>".$_SESSION['lang']['rupiah']."</th> 
						<th align=center rowspan=2  colspan=2>".$_SESSION['lang']['action']."</th> 
					</tr>
				</thead>
			<tbody>";
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
		$where="";
		if($param['nama']!=''){ 
			$where.=" and (id in (select id from ".$dbname.".kebun_5namafee where nama like '%".$param['nama']."%') or id in (select supplierid from ".$dbname.".log_5supplier where namasupplier like '%".$param['nama']."%'))";
		}
		if($param['blok']!=''){ 
			$where.=" and blok LIKE  '%".$param['blok']."%'";
		}
		if($param['jeniskend']!=''){ 
			$where.=" and jenisvhc LIKE  '%".$param['jeniskend']."%'";
		}
		if($param['jenis']!=''){ 
			$where.=" and jenisfee LIKE  '".$param['jenis']."%'";
		}
		
		$where.= "and substr(blok,1,4) in (".getOrgDetail(2).")";
		
		$ql2 = "select count(distinct(blok)) as jmlhrow from " . $dbname . ".kebun_5daftarfee where 0=0 ".$where.""; 
        $res = fetchdata($ql2);
        $jlhbrs = count($res);
        
		
		$sql = "SELECT * FROM " . $dbname . ".kebun_5namafee where status=1 order by nama asc";
		$qry = fetchdata($sql);
		foreach($qry as $bar){
			$optnamafee[$bar['id']]=$bar['nama'];
		}

		$sql = "SELECT * FROM " . $dbname . ".log_5supplier";
		$qry = fetchdata($sql);
		foreach($qry as $bar){
			$optnamafee[$bar['supplierid']]=$bar['namasupplier'];
		}
		
		$no = 0;
		$str = "select * from ".$dbname.".kebun_5daftarfee where 0=0 ".$where." order by blok asc LIMIT ".$offset.",".$limit."";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			#$optjnsfee = makeOption($dbname,"keu_5akun",'noakun,namaakun',"noakun='".$bar['jenis']."'");
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['blok']."</td>";
			$tab.="<td align=center>".getNamaOrg($bar['blok'])."</td>";
			$tab.="<td align=left>".$optnamafee[$bar['id']]."</td>";
			$tab.="<td align=center>".$bar['jenisvhc']."</td>";
			$tab.="<td align=center><b><i>".$bar['jenisfee']."</i></b></td>";
			$tab.="<td align=left>".$optakun[$bar['jenis']]."</td>";
			$tab.="<td align=right>".$bar['rp']."</td>";
			
			
            $tab.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('" . $bar['blok'] . "','".$bar['id']."','".$bar['jenisfee']."','".$bar['jenis']."','".$bar['rp']."','".$bar['jenisvhc']."');\" ></td>";
			
            $tab.="<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('" . $bar['blok'] . "','".$bar['id']."','".$bar['jenisfee']."','".$bar['jenis']."','".$bar['jenisvhc']."');\" ></td>";
            $tab.="</tr>";
        }
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0){
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++){
		  $sel = ($page==$er-1)? 'selected': '';
		  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$tab.="<tr><td colspan=9 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;

    default:
}
?>
