<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

$proses = checkPostGet('proses','');
$pres_afdeling = checkPostGet('pres_afdeling','');
$pres_kodekegiatan = checkPostGet('pres_kodekegiatan','');
$pres_alokasibiaya = checkPostGet('pres_alokasibiaya','');
$pres_kodesegment = checkPostGet('pres_kodesegment','');
$pres_hasilkerja = checkPostGet('pres_hasilkerja','');
$pres_jumlahhk = checkPostGet('pres_jumlahhk','');
$pres_upahpremi = checkPostGet('pres_upahpremi','');
$pres_method = checkPostGet('pres_method','');

$abs_kodekegiatan = checkPostGet('abs_kodekegiatan','');
$abs_nik = checkPostGet('abs_nik','');
$abs_absensi = checkPostGet('abs_absensi','');
$abs_jhk = checkPostGet('abs_jhk','');
$abs_umr = checkPostGet('abs_umr','');
$abs_insentif = checkPostGet('abs_insentif','');
$abs_temp_jhk = checkPostGet('abs_temp_jhk','');
$abs_temp_nik = checkPostGet('abs_temp_nik','');
$abs_method = checkPostGet('abs_method','');

$mat_kodekegiatan = checkPostGet('mat_kodekegiatan','');
$mat_kodegudang = checkPostGet('mat_kodegudang','');
$mat_kodebarang = checkPostGet('mat_kodebarang','');
$mat_kwantitas = checkPostGet('mat_kwantitas','');
$kwantitasha = checkPostGet('kwantitasha','');
$mat_method = checkPostGet('mat_method','');

$totalPresHk = checkPostGet('totalPresHk','');
$totalAbsHk = checkPostGet('totalAbsHk','');

$nourut = checkPostGet('nourut','');
$where = checkPostGet('where','');



$notransaksi = checkPostGet('notransaksi','');
$kodeorg = checkPostGet('kodeorg','');
$tanggal = checkPostGet('tanggal','');
$nikmandor = checkPostGet('nikmandor','');
$nikmandor1 = checkPostGet('nikmandor1','');
$nikasisten = checkPostGet('nikasisten','');
$keranimuat = checkPostGet('keranimuat','');
$notransaksi = checkPostGet('notransaksi','');
$numRow = checkPostGet('numRow','');
$shows = checkPostGet('shows','');
$page = checkPostGet('page','');

if($pres_kodesegment == ''){
	$pres_kodesegment = '0000000001';
}

switch($proses) {
	case 'showHeadList':    
		// $where = "a.kodeorg='".$_SESSION['org']['kodeorganisasi']."' and updateby='".$_SESSION['standard']['userid']."'";
        // if($_SESSION['empl']['kodejabatan']==5)$where = "a.kodeorg like '%' and updateby like '%'";
        // if(isset($param['where'])) {
                  // $tmpW = str_replace('\\','',$param['where']);
            // $arrWhere = json_decode($tmpW,true);
            // if(!empty($arrWhere)) {
				// foreach($arrWhere as $key=>$r1) {
					// if($r1[0]=='namasupplier') {
						// $where .= " and b.".$r1[0]." like '%".$r1[1]."%'";
					// } else {
						// $where .= " and a.".$r1[0]." like '%".$r1[1]."%'";
					// }
				// }
            // } 
        // }
        
        # Header
		$header = array(
		   $_SESSION['lang']['nomor'],$_SESSION['lang']['organisasi'],
		   $_SESSION['lang']['tanggal'],$_SESSION['lang']['mandor'],$_SESSION['lang']['nikmandor1'],
		   $_SESSION['lang']['asisten'],$_SESSION['lang']['keraniafdeling'],
		   $_SESSION['lang']['updateby']
		);
		
		//cari nama orang
		$str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar= $res->fetch()){
		   $nama[$bar->karyawanid]=$bar->namakaryawan;
		}    
        
        # Content
        $cols = "a.notransaksi,a.kodeorg,a.tanggal,a.mandor,a.mandor1,a.assisten,
			a.krani,a.updateby,a.posting,a.postingby";
		$order="a.tanggal desc";
		if($_SESSION['empl']['subbagian']!=''){
			$wheres = "a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and updateby='".$_SESSION['standard']['userid']."'";
		}else{
			$wheres = "a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		}
		$where = " and a.notransaksi like '%".$where."%'";
		if($_SESSION['empl']['kodejabatan']==21)$wheres = "a.kodeorg like '%' and updateby like '%'";
		
		$queryRow = "select count(*) as total";
		$query = " from ".$dbname.".vhc_splht a 
			where ".$wheres." ".$where." order by ".$order;
		$queryRow .= $query;
		if(!is_null($shows)) {
			if(!is_null($page)) {
			   $startFrom = ($page-1) * $shows;
			} else {
			   $startFrom = 0;
			}
			$query .= " limit ".$startFrom.",".$shows;
		}
		$query = "select ".$cols.$query;
		$tmpTotal = fetchData($queryRow);
        $data = fetchData($query);
        $totalRow = $tmpTotal[0]['total'];		
		
		foreach($data as $key=>$row) {
			if($row['posting']==1) {
				$data[$key]['switched']=true;
			}
			unset($data[$key]['posting']);            
			unset($data[$key]['postingby']);            
			$data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
			$data[$key]['mandor'] = isset($nama[$row['mandor']])? $nama[$row['mandor']]: '';
			$data[$key]['mandor1'] = isset($nama[$row['mandor1']])? $nama[$row['mandor1']]: '';
			$data[$key]['assisten'] = isset($nama[$row['assisten']])? $nama[$row['assisten']]: '';
			$data[$key]['krani'] = isset($nama[$row['krani']])? $nama[$row['krani']]: '';
			$data[$key]['updateby'] = $nama[$row['updateby']];
		}
        # Posting --> Jabatan
		$postJabatan = getPostingJabatan('sipil');
        
		# Make Table
        $tHeader = new rTable('headTable','headTableBody',$header,$data);
		$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
		$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
        $tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
		$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
		if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
			$tHeader->_actions[2]->_name='';
		}
		$tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
		$tHeader->_actions[3]->addAttr('event');
		if(!is_null($page)) {
		$tHeader->pageSetting(intval($page),$totalRow,10);
		}
		else
		{
		$tHeader->pageSetting(1,$totalRow,10);	
		}
		$tHeader->_switchException = array('detailPDF');
        
        # View
        $tHeader->renderTable();
        break;
	
	case 'showAdd':
        echo formHeader('add',array());
        break;
		
	case 'addHead':
		if($kodeorg == ''){
			exit("warning : ".$_SESSION['lang']['kodeorg']." required.");
		}
		if($tanggal == ''){
			exit("warning : ".$_SESSION['lang']['tanggal']." required.");
		}
		
		if($notransaksi == ''){
			$NoTransaksi = getNoTransaksi($tanggal);
		
			//Insert Header
			$str = "insert into ".$dbname.".vhc_splht (notransaksi,kodeorg,tanggal,mandor,mandor1,assisten,krani,updateby,posting) VALUES ('".$NoTransaksi."','".$kodeorg."','".tanggalsystem($tanggal)."','".$nikmandor."','".$nikmandor1."','".$nikasisten."','".$keranimuat."','".$_SESSION['standard']['userid']."','0')";
			try{
				$owlPDO->exec($str); 
				echo $NoTransaksi;
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}else{
			$str = "update ".$dbname.".vhc_splht set mandor = '".$nikmandor."', mandor1 = '".$nikmandor1."', assisten = '".$nikasisten."', krani = '".$keranimuat."', updateby = '".$_SESSION['standard']['userid']."' where notransaksi = '".$notransaksi."'";
			try{
				$owlPDO->exec($str); 
				echo $NoTransaksi;
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}
		
		break;
	
	case 'pres_oc_getSatuan':
		$str="select satuan from ".$dbname.".vhc_kegiatan where kodekegiatan = '".$pres_kodekegiatan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$satuan = $bar['satuan'];
		
		echo $satuan;
		break;
	
	case 'getAbsMatKegiatan':
		$optKegiatan = "";
		
		if($notransaksi != ''){
			$str="select kodekegiatan,alokasi, nourut from ".$dbname.".vhc_spl_prestasi where notransaksi = '".$notransaksi."' order by nourut";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				if(substr($bar['alokasi'],0,5) == "AK-BG"){
					$optNamaKegiatan = makeOption($dbname,'project_dt','kegiatan,namakegiatan',"kegiatan='".$bar['kodekegiatan']."'");
					$optKegiatan .= "<option value='".$bar['nourut']."'>".$optNamaKegiatan[$bar['kodekegiatan']]." (".$bar['kodekegiatan'].")</option>";
				}else{	
					$optNamaKegiatan = makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kodekegiatan']."'");
					$optKegiatan .= "<option value='".$bar['nourut']."'>".$optNamaKegiatan[$bar['kodekegiatan']]." (".$bar['kodekegiatan'].")</option>";
				}
			}			
		}
		
		echo $optKegiatan;
		break;
		
	case 'pres_oc_getalokasi':
		$lokasi=$_SESSION['empl']['lokasitugas'];
		$optAlokasiBiaya = "";
		
		$optTipeKdOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kodeorg."'");
		if($pres_afdeling == ''){
			$optTipeKegiatan = makeOption($dbname,'vhc_kegiatan','kodekegiatan,tipe',"kodekegiatan='".$pres_kodekegiatan."'");
			if(@$optTipeKdOrg[$kodeorg] == 'KANWIL'){
				if($optTipeKegiatan[$pres_kodekegiatan] == 'sipil'){
					$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".substr($kodeorg,0,4)."'");
					$str="select norumah,keterangan from ".$dbname.".sdm_perumahanht where kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[substr($kodeorg,0,4)]."')"; 
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						$optAlokasiBiaya.="<option value='".$bar['norumah']."'>".$bar['keterangan']." (".$bar['norumah'].")</option>";
					}
				}else{
					$str = "select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe = 'BLOK' and induk like '".$pres_afdeling."%'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar = $res->fetch()){
						$optAlokasiBiaya .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']." (".$bar['kodeorganisasi'].")</option>";
					} 
				}
			}else{
				if($optTipeKegiatan[$pres_kodekegiatan] == 'sipil'){
					$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".substr($kodeorg,0,4)."'");
					$str="select norumah,keterangan from ".$dbname.".sdm_perumahanht where kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[substr($kodeorg,0,4)]."')";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						$optAlokasiBiaya.="<option value='".$bar['norumah']."'>".$bar['keterangan']." (".$bar['norumah'].")</option>";
					}
				}else{
					$str = "select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe = 'BLOK' and induk like '".$kodeorg."%'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar = $res->fetch()){
						$optAlokasiBiaya .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']." (".$bar['kodeorganisasi'].")</option>";
					} 
					//project
					$str="select kode,nama from ".$dbname.".project where kode = '".$pres_afdeling."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch())
					{
						$optAlokasiBiaya .= "<option value='".$bar['kode']."'>".$bar['nama']." (".$bar['kode'].")</option>";
					}
				}
			}
		}else if($pres_afdeling == 'SPL'){
			if(@$optTipeKdOrg[$kodeorg] == 'KANWIL'){
				$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".substr($kodeorg,0,4)."'");
				$str="select norumah,keterangan from ".$dbname.".sdm_perumahanht where kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[substr($kodeorg,0,4)]."')";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()){
					$optAlokasiBiaya.="<option value='".$bar->norumah."'>".$bar->keterangan." (".$bar->norumah.")</option>";
				}
			}else{
				$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".substr($kodeorg,0,4)."'");
				$str="select norumah,keterangan from ".$dbname.".sdm_perumahanht where kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[substr($kodeorg,0,4)]."')";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()){
					$optAlokasiBiaya.="<option value='".$bar->norumah."'>".$bar->keterangan." (".$bar->norumah.")</option>";
				}
			}
		}else{
			$str = "select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe = 'BLOK' and induk like '".$pres_afdeling."%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optAlokasiBiaya .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']." (".$bar['kodeorganisasi'].")</option>";
			}
			//project
			$str="select kode,nama from ".$dbname.".project where kode = '".$pres_afdeling."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$optAlokasiBiaya .= "<option value='".$bar['kode']."'>".$bar['nama']." (".$bar['kode'].")</option>";
			}
		}
		echo $optAlokasiBiaya;
		break;
	
	case 'pres_oc_kegiatan':
		$lokasi=$_SESSION['empl']['lokasitugas'];
		$optAlokasiBiaya = "";
		$optTipeKdOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kodeorg."'");
		$optJnsPekerjaan = "";
		if($pres_afdeling == ''){
			
			//Get Jenis Kegiatan Prestasi
			$str = "select kodekegiatan, namakegiatan from ".$dbname.".vhc_kegiatan where (tipe = 'sipil' or tipe = 'blok')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optJnsPekerjaan .= "<option value='".$bar['kodekegiatan']."'>".$bar['namakegiatan']." (".$bar['kodekegiatan'].")</option>";
			}
			//Get Jenis Kegiatan Internal
			$str = "select kegiatan, namakegiatan from ".$dbname.".project_dt order by namakegiatan";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optJnsPekerjaan .= "<option value='".$bar['kegiatan']."'>".$bar['namakegiatan']."</option>";
			}
		}else if($pres_afdeling == 'SPL'){
			//Get Jenis Kegiatan Prestasi
			$optJnsPekerjaan = "";
			$str = "select kodekegiatan, namakegiatan from ".$dbname.".vhc_kegiatan where (tipe = 'sipil')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optJnsPekerjaan .= "<option value='".$bar['kodekegiatan']."'>".$bar['namakegiatan']." (".$bar['kodekegiatan'].")</option>";
			}
		}else if(substr($pres_afdeling,0,5) == 'AK-BG'){
			//Get Jenis Kegiatan Internal
			$str = "select kegiatan, namakegiatan from ".$dbname.".project_dt where kodeproject = '".$pres_afdeling."' order by namakegiatan";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optJnsPekerjaan .= "<option value='".$bar['kegiatan']."'>".$bar['namakegiatan']."</option>";
			}
			
		}else{
			//Get Jenis Kegiatan Prestasi
			$optJnsPekerjaan = "";
			$str = "select kodekegiatan, namakegiatan from ".$dbname.".vhc_kegiatan where (tipe = 'blok')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optJnsPekerjaan .= "<option value='".$bar['kodekegiatan']."'>".$bar['namakegiatan']." (".$bar['kodekegiatan'].")</option>";
			}
		}
		
		echo $optJnsPekerjaan;
		break;	
	case 'pres_oc_afdeling':
		$lokasi=$_SESSION['empl']['lokasitugas'];
		$optAlokasiBiaya = "";
		$optTipeKdOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kodeorg."'");
		if($pres_afdeling == ''){
			
			//Get Jenis Kegiatan Prestasi
			$optJnsPekerjaan = "";
			$str = "select kodekegiatan, namakegiatan from ".$dbname.".vhc_kegiatan where (tipe = 'sipil' or tipe = 'blok')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optJnsPekerjaan .= "<option value='".$bar['kodekegiatan']."'>".$bar['namakegiatan']." (".$bar['kodekegiatan'].")</option>";
			}
		}else if($pres_afdeling == 'SPL'){
			//Get Jenis Kegiatan Prestasi
			$optJnsPekerjaan = "";
			$str = "select kodekegiatan, namakegiatan from ".$dbname.".vhc_kegiatan where (tipe = 'sipil')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optJnsPekerjaan .= "<option value='".$bar['kodekegiatan']."'>".$bar['namakegiatan']." (".$bar['kodekegiatan'].")</option>";
			}
		}else{
			//Get Jenis Kegiatan Prestasi
			$optJnsPekerjaan = "";
			$str = "select kodekegiatan, namakegiatan from ".$dbname.".vhc_kegiatan where (tipe = 'blok')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optJnsPekerjaan .= "<option value='".$bar['kodekegiatan']."'>".$bar['namakegiatan']." (".$bar['kodekegiatan'].")</option>";
			}
		}
		
		echo $optJnsPekerjaan;
		break;
		
	case 'detailFieldShow':
		$tab = "";
		$frm[0]='';
		$frm[1]='';
		$frm[2]='';
		
		### BEGIN PRESTASI ###
		$lokasi=$_SESSION['empl']['lokasitugas'];
				
		//Get Divisi Prestasi
		$optDivisi = "<option value=''>".$_SESSION['lang']['all']."</option>";
		$optDivisi .= "<option value='SPL'>".$_SESSION['lang']['manajemenperumahan']."</option>";
		//$optDivisi .= "<option value='Internal'>".$_SESSION['lang']['internal']."</option>";
		$sDiv="select kode,nama from ".$dbname.".project where pekerjaan = 'Internal' and posting = '0' and kodeorg='".$lokasi."' and SUBSTRING(kode,1,5) ='AK-BG'";
		$rDiv=$owlPDO->query($sDiv) or die(print " Gagal: ".PDOException::getMessage());
		$rDiv->setFetchMode(PDO::FETCH_OBJ);
		while($bDiv=$rDiv->fetch())
		{
			$optDivisi .= "<option value=".$bDiv->kode.">".$bDiv->nama." - Project</option>";
		}
		$sDiv="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' and induk='".$lokasi."'";
		$rDiv=$owlPDO->query($sDiv) or die(print " Gagal: ".PDOException::getMessage());
		$rDiv->setFetchMode(PDO::FETCH_OBJ);
		while($bDiv=$rDiv->fetch())
		{
			$optDivisi .= "<option value=".$bDiv->kodeorganisasi.">".$bDiv->namaorganisasi."</option>";
		}
		
		//Get Jenis Kegiatan Prestasi
		$optJnsPekerjaan = "";
		$str = "select kodekegiatan, namakegiatan from ".$dbname.".vhc_kegiatan where (tipe = 'sipil' or tipe = 'blok')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optJnsPekerjaan .= "<option value='".$bar['kodekegiatan']."'>".$bar['namakegiatan']." (".$bar['kodekegiatan'].")</option>";
		}
		
		//Get Alokasi Biaya Prestasi
		$optAlokasiBiaya = "";
		
		//Generate Form Prestasi, Kehadiran, Material
		$frm[0] .= "<fieldset><table style='padding-bottom:5px;'>
			<tr>
				<td width=82px>".$_SESSION['lang']['jenis']."</td>
				<td>:</td>
				<td style='padding-right:20px;'>
					<select id=pres_afdeling style='width:232px' onchange=\"pres_oc_kegiatan()\">".$optDivisi."</select>
				</td>
			</tr>
		</table></fieldset>";
		$frm[0] .= "<fieldset>
			<legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['prestasi']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<select id=pres_kodekegiatan onchange=\"pres_oc_getalokasi();\" style='width:232px'>".$optJnsPekerjaan."</select>
						<img id=pres_kodekegiatan_find onclick=\"z.elSearch('pres_kodekegiatan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
					
					<td>".$_SESSION['lang']['jumlahhk']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<input id=pres_jumlahhk class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:65px' onfocus=\"document.getElementById('tmpValHk').value = this.value\" onkeyup=\"totalVal();\">
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['alokasibiaya']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<select id=pres_alokasibiaya style='width:232px'>".$optAlokasiBiaya."</select>
						<img id=pres_alokasibiaya onclick=\"z.elSearch('pres_alokasibiaya',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
					
					<td>".$_SESSION['lang']['umr']." (Rp)</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<input id=pres_umr class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' value='0' style='width:65px' disabled='disabled'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['hasilkerjajumlah']."</td>
					<td>:</td>
					<td>
						<input id=pres_hasilkerja class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:70px' onkeyup=\"totalVal();\">
						<input id=pres_hasilkerja_satuan class='myinputtext' type='text' disabled='disabled' value='' style='width:50px'>
					</td>
					
					<td style='display:none'>".$_SESSION['lang']['kodesegment']."</td>
					<td style='display:none'>:</td>
					<td style='display:none'>
						<input id=pres_kodesegment class=myinputtext type=text style='width:70px' value='' disabled=''>
						<input id=pres_kodesegment_name class=myinputtext value='' type=text style='width:150px' disabled=''>
						<img id=pres_kodesegment_name_find onclick=\"getSearch(event,'pres_kodesegment','segment')\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
					
					<td>".$_SESSION['lang']['premi']." (Rp)</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<input id=pres_upahpremi class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:65px' disabled='disabled' onfocus=\"document.getElementById('tmpValIns').value = this.value\" onkeyup=\"totalVal();\">
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<input id=pres_nourut class='myinputtext' type='hidden' value='' style='width:100px'>
					<input id=pres_method class='myinputtext' type='hidden' value='insert' style='width:100px'>
					<td>
						<button id='addPres' name='addPres' class='mybutton' onclick=\"addPres()\">".$_SESSION['lang']['save']."</button>
						<button id='cancelPres' class='mybutton' onclick=\"cancelPres()\">".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset>
			<legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['prestasi']."</legend>
			<table class=sortable cellspacing=1   border=0>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center' colspan=2>".$_SESSION['lang']['action']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['alokasibiaya']."</td>
					<td style='text-align:center;display:none'>".$_SESSION['lang']['kodesegment']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['hasilkerjajumlah']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['jumlahhk']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['umr']." (Rp)</td>
					<td style='text-align:center'>".$_SESSION['lang']['premi']." (Rp)</td>
				</tr>
				</thead>
				<tbody id=listPrestasi>
				</tbody>
			</table>
		</fieldset><script>loadAllListTab();</script>";
		### END PRESTASI ###
		
		
		### BEGIN ABSENSI ###
		//Get Karyawan Sipil
		$optAbsKaryawan = "";
		// $str = "select karyawanid,nik,subbagian,namakaryawan from ".$dbname.".datakaryawan where subbagian in (select kodeorganisasi from ".$dbname.".organisasi where tipe = 'SIPIL' and alokasi='".$_SESSION['empl']['kodeorganisasi']."')";
		$str = "select karyawanid,nik,subbagian,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in (1,2,3,4,6)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);	
		while($bar = $res->fetch()){
			$optAbsKaryawan .= "<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['nik']."(".$bar['subbagian'].")</option>";
		}
		
		//Get Absensi Jenis Pekerjaan
		$optAbsJnsPekerjaan = "";
		
		$frm[1] .= "<fieldset>
			<legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['absensi']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<select id=abs_kodekegiatan style='width:162.5px'>".$optAbsJnsPekerjaan."</select>
						<input id=abs_nourut class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=hidden value='0' style='width:19.5px' disabled='disabled'>
						<img id=abs_kodekegiatan onclick=\"z.elSearch('abs_kodekegiatan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
					
					<td>".$_SESSION['lang']['jumlahhk']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<input id='abs_jhk' class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type='text' value='0' style='width:65px' onkeyup=\"totalVal();updateUMR();\">
						<input id=abs_temp_jhk class='myinputtext' type='hidden' value='' style='width:100px'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['nik']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<select id='abs_nik' style='width:162.5px' onchange=\"updateUMR(this)\">".$optAbsKaryawan."</select>
						<input id=abs_temp_nik class='myinputtext' type='hidden' value='' style='width:100px'>
						<img id=abs_nik_find onclick=\"z.elSearch('abs_nik',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
					
					<td>".$_SESSION['lang']['umr']." / ".$_SESSION['lang']['hari']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<input id='abs_umr' disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:65px' onkeyup=\"totalVal();\">
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['absensi']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<select id='abs_absensi' style='width:162.5px'><option value='H' selected>Hadir</option></select>
					</td>
					
					<td>".$_SESSION['lang']['premi']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<input id='abs_insentif' class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' value='0' style='width:65px' onkeyup=\"totalVal();\">
					</td>
				</tr>
				<tr>
					<td colspan=2>
						<input id=abs_nourut class='myinputtext' type='hidden' value='' style='width:100px'>
						<input id=abs_method class='myinputtext' type='hidden' value='insert' style='width:100px'>
					</td>
					<td>
						<button id='addAbs' name='addAbs' class='mybutton' onclick=\"addAbs();\">Simpan</button>
						<button id='cancelAbs' class='mybutton' onclick=\"cancelAbs()\">".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset>
			<legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['absensi']."</legend>
			<table class=sortable cellspacing=1  border=0>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center' colspan=2>".$_SESSION['lang']['action']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['nik']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['absensi']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['jumlahhk']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['umr']." / ".$_SESSION['lang']['hari']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['premi']."</td>
				</tr>
				</thead>
				<tbody id=listAbsensi>
				</tbody>
			</table>
		</fieldset>";
		### END ABSENSI ###
		
		
		### BEGIN MATERIAL ###
		//Get Kode Gudang
		$optKodeGudang = "";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe like '%GUDANG%' and induk like '%".$kodeorg."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);		
		while($bar = $res->fetch()){
			$optKodeGudang.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
		}
		
		//Get Material Jenis Pekerjaan
		$optMatJnsPekerjaan = "";
		
		$frm[2] .= "<fieldset>
			<legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['material']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<select id=mat_kodekegiatan style='width:200.5px'>".$optMatJnsPekerjaan."</select>
						<img id=mat_kodekegiatan onclick=\"z.elSearch('mat_kodekegiatan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
					
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>:</td>
					<td>
						<input id=mat_kodebarang class='myinputtext' type=text style='width:50px' value='' disabled=''>
					</td>
					<td style='padding-right:20px;'>
						<input id=mat_kodebarang_name class=myinputtext value='' type=text style='width:150px' disabled=''>
						<img id=mat_kodebarang_find onclick=\"getSearch(event,'mat_kodebarang','barang')\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['pilihgudang']."</td>
					<td>:</td>
					<td style='padding-right:20px;'>
						<select id=mat_kodegudang style='width:200.5px'>".$optKodeGudang."</select>
						<img id=mat_kodegudang_find onclick=\"z.elSearch('mat_kodegudang',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
						<input id='kwantitasha' class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type=hidden value='0' style='width:65px'>
					</td>
					
					<td>".$_SESSION['lang']['kuantitas']."</td>
					<td>:</td>
					<td colspan=2 style='padding-right:20px;'>
						<input id='mat_kwantitas' class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:50px' onkeyup=\"totalVal();\">
					</td>
				</tr>
				<tr>
					<td colspan=2>
						<input id=mat_nourut class='myinputtext' type='hidden' value='' style='width:100px'>
						<input id=mat_method class='myinputtext' type='hidden' value='insert' style='width:100px'>
					</td>
					<td>
						<button id='addMat' name='addMat' class='mybutton' onclick=\"addMat();\">Simpan</button>
						<button id='cancelMat' class='mybutton' onclick=\"cancelMat()\">".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset>
			<legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['material']."</legend>
			<table class=sortable cellspacing=1  border=0>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center' colspan=2>".$_SESSION['lang']['action']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['pilihgudang']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['kodebarang']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['kuantitas']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['satuan']."</td>
				</tr>
				</thead>
				<tbody id=listMaterial>
				</tbody>
			</table>
		</fieldset>";
		### END MATERIAL ###
		
		
		$hfrm[0]=$_SESSION['lang']['prestasi'];
		$hfrm[1]=$_SESSION['lang']['absensi'];
		$hfrm[2]=$_SESSION['lang']['material'];
	
		echo OPEN_BOX();
		echo drawTab('FRM',$hfrm,$frm,150,'100%');
		echo CLOSE_BOX();
		break;
		
	case 'showEdit':
        $query = selectQuery($dbname,'vhc_splht',"*","notransaksi='".$notransaksi."'");
        $tmpData = fetchData($query);
        $data = $tmpData[0];
        $data['tanggal'] = tanggalnormal($data['tanggal']);
        echo formHeader('edit',$data);
        echo "<div id='detailField' style='clear:both'></div>";
        break;	
		
	case 'delete':
		$where = "notransaksi='".$notransaksi."'";
		$query = "delete from `".$dbname."`.`vhc_splht` where ".$where;
		try{
			$owlPDO->exec($query);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
			exit;
		}
		break;	
		
	case 'addPres':
		if($pres_hasilkerja == 0 || $pres_hasilkerja == ''){
			exit("warning : Hasil kerja = 0");
		}
			
		if($pres_method == 'insert'){
			$str = "select count(notransaksi) as countItem from ".$dbname.".vhc_spl_prestasi where notransaksi = '".$notransaksi."' and alokasi = '".$pres_alokasibiaya."' and kodekegiatan = '".$pres_kodekegiatan."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			if($bar['countItem'] >= 1){
				exit("warning : Jenis kegiatan dan Alokasi biaya sudah pernah diinput sebelumnya.");
			}
			
			$str = "select max(nourut) as nourut from ".$dbname.".vhc_spl_prestasi where notransaksi = '".$notransaksi."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$vNourut = $bar['nourut'] + 1;
			
			$str = "insert into ".$dbname.".vhc_spl_prestasi (notransaksi,nourut,alokasi,kodekegiatan,total_hasilkerja,total_hk,total_premi,kodesegment) value ('".$notransaksi."','".$vNourut."','".$pres_alokasibiaya."','".$pres_kodekegiatan."','".$pres_hasilkerja."','".$pres_jumlahhk."','".$pres_upahpremi."','".$pres_kodesegment."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}else{
			$str = "select count(jhk) as jhk from ".$dbname.".vhc_spl_absen where notransaksi = '".$notransaksi."' and nourut = '".$nourut."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			
			if($pres_jumlahhk < $bar['jhk']){
				exit("warning : Jumlah HK prestasi harus lebih besar atau sama dengan Jumlah HK absensi");
			}
			
			$str = "update ".$dbname.".vhc_spl_prestasi set alokasi = '".$pres_alokasibiaya."', kodekegiatan = '".$pres_kodekegiatan."', total_hasilkerja = '".$pres_hasilkerja."', total_hk = '".$pres_jumlahhk."', total_premi = '".$pres_upahpremi."', kodesegment = '".$pres_kodesegment."' where notransaksi = '".$notransaksi."' and nourut = '".$nourut."'";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}
		break;
	
	case 'addAbs':
	
	// Check data :
	// table kebun_kehadiran
	// table vhc_runHk
	// table sdm_absensiht
	
		$tanggal = date('Y-m-d',$tanggal);
		 #==============periksa apakah sudah ada kehadiran pada hari yang sama
        $str="select sum(jhk) as jumlah from ".$dbname.".hk_vw where tanggal=".$tanggal."
              and karyawanid='".$abs_nik."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $datr=$res->fetch();
		
        if(($datr['jumlah']+$abs_jhk)>1)
        {
            exit("Error: Karyawan tersebut sudah memiliki HK dengan Jumlah:".$datr['jumlah']);
        }
       
		#jika belum ada maka aman
		
		//if($totalPresHk == 0){
			//exit("warning : Nilai HK prestasi harus lebih besar atau sama dengan nilai HK absensi");
		//}
		
		if(($abs_jhk == 0 || $abs_jhk == '') && $abs_insentif == 0) {
			exit("warning : Jumlah HK atau premi harus lebih besar dari 0");
		}
		
		$countHk = 0;
		$countHk2 = 0;
		$countHk3 = 0;
		$countHk4 = 0;
		$str = "select nik, jhk, nourut from ".$dbname.".vhc_spl_absen where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			if($abs_nik == $bar['nik']){
				$countHk += $bar['jhk'];
				if($abs_kodekegiatan == $bar['nourut']){
					$countHk2 = 1;
				}
			}
			if($abs_kodekegiatan == $bar['nourut']){
				$countHk3 += $bar['jhk'];
			}
		}
		
		$str = "select total_hk from ".$dbname.".vhc_spl_prestasi where notransaksi = '".$notransaksi."' and nourut = '".$abs_kodekegiatan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$countHk4 = $bar['total_hk'];
		
		if($abs_method == 'insert'){
			if($abs_jhk > 1 and $abs_umr < 1){
				exit("warning : UMR Nik ".$abs_nik.", belum terdaftar di setup gaji pokok");
			}
			if(($countHk + $abs_jhk) > 1){
				exit("warning : Maksimal HK untuk nik ".$abs_nik." adalah 1");
			}
			
			if($countHk2 >= 1){
				exit("warning : Karyawan dengan nik ".$abs_nik." sudah pernah di-input sebelumnya.");
			}
			
			if(($countHk3+$abs_jhk) > $countHk4){
				exit("warning : Nilai HK prestasi harus lebih besar atau sama dengan nilai HK absensi");
			}
			
			$str = "insert into ".$dbname.".vhc_spl_absen (notransaksi,nourut,nik,jhk,umr,premi) values ('".$notransaksi."','".$abs_kodekegiatan."','".$abs_nik."','".$abs_jhk."','".$abs_umr."','".$abs_insentif."')";
		}else{
			if($countHk4 < (($countHk3+$abs_jhk)-$abs_temp_jhk)){
				exit("warning : Nilai HK prestasi harus lebih besar atau sama dengan nilai HK absensi");
			}
			
			$str = "update ".$dbname.".vhc_spl_absen set jhk = '".$abs_jhk."', umr = '".$abs_umr."', premi = '".$abs_insentif."' where notransaksi = '".$notransaksi."' and nourut = '".$abs_kodekegiatan."' and nik = '".$abs_temp_nik."'";
		}
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	case 'addMat':
		if($mat_kodebarang == ''){
			exit("warning : Kode barang harus diisi.");
		}
		
		if($mat_kwantitas == '' || $mat_kwantitas == 0){
			exit("warning : Kuantitas barang harus diisi.");
		}
		
		if($mat_method == 'insert'){
			$str = "select * from ".$dbname.".vhc_spl_material where notransaksi = '".$notransaksi."' and nourut = '".$mat_kodekegiatan."' and kodegudang = '".$mat_kodegudang."' and kodebarang = '".$mat_kodebarang."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrow=owlBaris($res);
			
			if($numrow >= 1){
				exit("warning : Item ini sudah pernah diinput sebelumnya.");
			}
			
			$str = "insert into ".$dbname.".vhc_spl_material (notransaksi,nourut,kodegudang,kodebarang,jumlah) values ('".$notransaksi."','".$mat_kodekegiatan."','".$mat_kodegudang."','".$mat_kodebarang."','".$mat_kwantitas."')";
		}else{
			$str = "update ".$dbname.".vhc_spl_material set jumlah = '".$mat_kwantitas."' where notransaksi = '".$notransaksi."' and nourut = '".$mat_kodekegiatan."' and kodegudang = '".$mat_kodegudang."' and kodebarang = '".$mat_kodebarang."'";
		}
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	case 'loadListPrestasi':
		$tab = "";
		
		$str = "select a.*,IFNULL(b.namakegiatan, c.namakegiatan) as namakegproject, b.tipe as tipekegiatan,c.namakegiatan as namakegproject,
		IFNULL(d.nama, IFNULL(e.keterangan,f.namaorganisasi)) as namaalokasi
		from ".$dbname.".vhc_spl_prestasi a
		left join vhc_kegiatan 		b on a.kodekegiatan 	= b.kodekegiatan
		left join project_dt 		c on a.kodekegiatan 	= c.kegiatan
		left join project 			d on d.kode 			= a.alokasi
		left join sdm_perumahanht 	e on e.norumah 			= a.alokasi
		left join organisasi 		f on f.kodeorganisasi 	= a.alokasi
		where a.notransaksi = '".$notransaksi."' order by a.nourut ASC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',"kodesegment='".$bar['kodesegment']."'");
			//$optKegiatan = makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kodekegiatan']."'");
			//$optTipeKegiatan = makeOption($dbname,'vhc_kegiatan','kodekegiatan,tipe',"kodekegiatan='".$bar['kodekegiatan']."'");
			//if($bar['tipekegiatan'] == 'sipil'){
			//	$optAlokasi = makeOption($dbname,'sdm_perumahanht','norumah,keterangan',"norumah='".$bar['alokasi']."'");
			//}else{
			//	$optAlokasi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['alokasi']."'");
			//}
			
			$str2 = "select sum(umr) as umr, sum(premi) as premi from ".$dbname.".vhc_spl_absen where notransaksi = '".$notransaksi."' and nourut = '".$bar['nourut']."' group by nourut";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			
			$tab .= "<tr class=rowcontent>
				<td style='text-align:center'><img class=zImgBtn src=images/skyblue/edit.png onclick=\"editListPresitasi('".$bar['kodekegiatan']."','".$bar['alokasi']."','".$bar['total_hasilkerja']."','".$bar['total_hk']."','".$bar['total_premi']."','".$bar['nourut']."','".$bar['kodesegment']."','".$optSegment[$bar['kodesegment']]."','".number_format($bar2['umr'])."','".number_format($bar2['premi'])."')\"></td>
				<td style='text-align:center'><img class=zImgBtn src=images/skyblue/delete.png onclick=\"deleteListPresitasi('".$bar['nourut']."')\"></td>
				<td>".$bar['namakegproject']." (".$bar['kodekegiatan'].")</td>
				<td>".$bar['namaalokasi']." (".$bar['alokasi'].")</td>
				<td style='display:none'>".$bar['kodesegment']." (".$optSegment[$bar['kodesegment']].")</td>
				<td style='text-align:right'>".$bar['total_hasilkerja']."</td>
				<td style='text-align:right'>".$bar['total_hk']."</td>
				<td style='text-align:right'>".number_format($bar2['umr'])."</td>
				<td style='text-align:right'>".number_format($bar2['premi'])."</td>
			</tr>";
		}
		
		echo $tab;
		break;
	
	case 'loadListAbsensi':
		$tab = "";
		
		$str = "select * from ".$dbname.".vhc_spl_absen where notransaksi = '".$notransaksi."' order by nourut ASC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while($bar = $res->fetch()){
			$str2 = "select karyawanid,nik,subbagian,namakaryawan from ".$dbname.".datakaryawan where karyawanid = '".$bar['nik']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$vKaryawan = $bar2['namakaryawan']." - ".$bar2['nik']." (".$bar2['subbagian'].")";
			
			$str2 = "select t1.kodekegiatan, t2.namakegiatan from ".$dbname.".vhc_spl_prestasi t1 
					left join ".$dbname.".vhc_kegiatan t2 on t1.kodekegiatan = t2.kodekegiatan 
					where t1.notransaksi = '".$bar['notransaksi']."' and t1.nourut = '".$bar['nourut']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$vKegiatan = $bar2['namakegiatan']." (".$bar2['kodekegiatan'].")";
			$no++;
			$tab .= "<tr class=rowcontent>
				<td style='text-align:center'><img class=zImgBtn src=images/skyblue/edit.png onclick=\"editListAbsensi('".$bar['nik']."','".$bar['jhk']."','".$bar['umr']."','".$bar['premi']."','".$bar['nourut']."')\"></td>
				<td style='text-align:center'><img class=zImgBtn src=images/skyblue/delete.png onclick=\"deleteListAbsensi('".$bar['nourut']."','".$bar['nik']."')\"></td>
				<td style='text-align:center;'>".$no."</td>
				<td>".$vKegiatan."</td>
				<td>".$vKaryawan."</td>
				<td>Hadir</td>
				<td style='text-align:right;'>".$bar['jhk']."</td>
				<td style='text-align:right'>".$bar['umr']."</td>
				<td style='text-align:right'>".$bar['premi']."</td>
			</tr>";
		}
		
		echo $tab;
		break;
		
	case 'loadListMaterial':
		$tab = "";
		
		$str = "select * from ".$dbname.".vhc_spl_material where notransaksi = '".$notransaksi."' order by nourut ASC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optGudang = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodegudang']."'");
			
			$str2 = "select t1.kodekegiatan, t2.namakegiatan from ".$dbname.".vhc_spl_prestasi t1 
					left join ".$dbname.".vhc_kegiatan t2 on t1.kodekegiatan = t2.kodekegiatan 
					where t1.notransaksi = '".$bar['notransaksi']."' and t1.nourut = '".$bar['nourut']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$vKegiatan = $bar2['namakegiatan']." (".$bar2['kodekegiatan'].")";
			
			$str2 = "select kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang where kodebarang = '".$bar['kodebarang']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$vNamaBarang = $bar2['namabarang']." (".$bar2['kodebarang'].")";
			$vSatuan = $bar2['satuan'];
			
			$tab .= "<tr class=rowcontent>
				<td style='text-align:center'><img class=zImgBtn src=images/skyblue/edit.png onclick=\"editListMaterial('".$bar['nourut']."','".$bar['kodebarang']."','".$bar['kodegudang']."','".$bar2['namabarang']."','".$bar['jumlah']."')\"></td>
				<td style='text-align:center'><img class=zImgBtn src=images/skyblue/delete.png onclick=\"deleteListMaterial('".$bar['nourut']."','".$bar['kodebarang']."','".$bar['kodegudang']."')\"></td>
				<td>".$vKegiatan."</td>
				<td>".$optGudang[$bar['kodegudang']]."</td>
				<td>".$vNamaBarang."</td>
				<td style='text-align:right'>".$bar['jumlah']."</td>
				<td style='text-align:left'>".$vSatuan."</td>
			</tr>";
		}
		
		echo $tab;
		break;
		
	case 'deleteListPresitasi':
		$str = "delete from ".$dbname.".vhc_spl_prestasi where notransaksi = '".$notransaksi."' and nourut='".$nourut."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	case 'deleteListAbsensi':
		$str = "delete from ".$dbname.".vhc_spl_absen where notransaksi = '".$notransaksi."' and nourut='".$nourut."' and nik = '".$abs_nik."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	case 'deleteListMaterial':
		$str = "delete from ".$dbname.".vhc_spl_material where notransaksi = '".$notransaksi."' and nourut='".$nourut."' and kodebarang = '".$mat_kodebarang."' and kodegudang = '".$mat_kodegudang."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	case 'updateUMR':
		$tanggal = tanggalsystem($tanggal);
		// Ambil Gaji Pokok
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$abs_nik." and tahun=".substr($tanggal,0,4)." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
		
		// Standard UMR
		$stdUmr = $Umr[0]['nilai']/25;
		
		// Upah yang didapat
		@$zUmr=$abs_jhk*$Umr[0]['nilai']/25;
		
		echo $zUmr;
		break;
		
	case 'getTotal':
		//Get Total HK Prestasi
		$str = "select sum(total_hasilkerja) as total_hasilkerja, sum(total_hk) as total_hk, sum(total_premi) as total_premi from ".$dbname.".vhc_spl_prestasi where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$vPresHasilKerja = $bar['total_hasilkerja'];
		$vPresHk = (isset($bar['total_hk']) ? $bar['total_hk'] : 0);
		$vPresUmr = 0;
		$vPresPremi = (isset($bar['total_premi']) ? $bar['total_premi'] : 0);
		
		//Get Total HK Absensi
		$str = "select sum(jhk) as jhk, sum(umr) as umr, sum(premi) as premi from ".$dbname.".vhc_spl_absen where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$vAbsHk = (isset($bar['jhk']) ? $bar['jhk'] : 0);
		$vAbsUmr = (isset($bar['umr']) ? $bar['umr'] : 0);
		$vAbsPremi = (isset($bar['premi']) ? $bar['premi'] : 0);
		
		echo $vPresHk."###".number_format($vAbsUmr)."###".number_format($vAbsPremi)."###".$vAbsHk."###".number_format($vAbsUmr)."###".number_format($vAbsPremi);
		break;
		
	case 'postingdata':
		$str = "select sum(total_hasilkerja) as hk, sum(total_hk) as jhk from ".$dbname.".vhc_spl_prestasi where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$vPresHk = $bar['hk'];
		$vPresJhk = $bar['jhk'];
		
		$str = "select sum(jhk) as jhk from ".$dbname.".vhc_spl_absen where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$vAbsJhk = $bar['jhk'];
		
		if($vPresHk <= 0){
			exit("warning : Belum ada prestasi untuk no transaksi ".$notransaksi."!");
		}
		if($vPresJhk != $vAbsJhk){
			exit("warning : Jumlah HK di Prestasi dan Absen masih selisih.");
		}
		
		$str = "update ".$dbname.".vhc_splht set posting = '1', postingby = '".$_SESSION['standard']['userid']."' where notransaksi = '".$notransaksi."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		
		break;
    # Daftar Header
    
    # Form Add Header
    case 'showAdd':
        // View
        echo formHeader('add',array());
        echo "<div id='detailField' style='clear:both'></div>";
        break;
    # Form Edit Header
    
    # Proses Add Header
    
    # Proses Edit Header
    case 'edit':
		$data = $_POST;
		$where = "noinvoice='".$data['noinvoice']."'";
		unset($data['noinvoice']);
                if($data['tipeinvoice']=='po') {
                    $optPO = makeOption($dbname,'log_poht','nopo,kodesupplier',"stat_release=1 and nopo='".$data['nopo']."'");
                    //jmlh po di dari po
                    $sCek2="select distinct  nilaipo as jmlhpo,ppn from ".$dbname.".log_poht where nopo='".$data['nopo']."' ";
					$rCek2=$owlPDO->query($qCek2) or die(print " Gagal: ".PDOException::getMessage());
					$rCek2->setFetchMode(PDO::FETCH_ASSOC);
                } else if($data['tipeinvoice']=='sj') {
                    $optPO = makeOption($dbname,'log_suratjalanht','nosj,expeditor');
                    $rCek2['jmlhpo']=0;
                } else {
                    $sCek2="select distinct nilaikontrak as jmlhpo from ".$dbname.".log_spkht where notransaksi='".$data['nopo']."' ";
					$qCek2=$owlPDO->query($sCek2) or die(print " Gagal: ".PDOException::getMessage());
					$qCek2->setFetchMode(PDO::FETCH_ASSOC);
                    $rCek2=$qCek2->fetch();
                    $optPO = makeOption($dbname,'log_spkht','notransaksi,koderekanan');
                    $rCek2['ppn']=0;
                }
                $data['nilaippn'] = $rCek2['ppn'];
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		$data['jatuhtempo'] = tanggalsystem($data['jatuhtempo']);
		$data['tipeinvoice'] = substr($data['tipeinvoice'],0,1);
		$data['nilaiinvoice'] = str_replace(',','',$data['nilaiinvoice']);
		$data['uangmuka'] = str_replace(',','',$data['uangmuka']);
		$data['updateby'] = $_SESSION['standard']['userid'];
		$query = updateQuery($dbname,'keu_tagihanht',$data,$where);
		try{
			$owlPDO->exec($query);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
    
    case 'updpo':
		$pokontrak = $_POST['pokontrak'];
		if($pokontrak=='po') {
			$resPO = makeOption($dbname,'log_poht','nopo,nopo',"stat_release=1",'0',true);
		} if($pokontrak=='sj') {
			$resPO = makeOption($dbname,'log_pengiriman_ht','nosj,nosj','0',true);
		} else {
			$resPO = makeOption($dbname,'log_spkht','notransaksi,notransaksi',
			"kodeorg='".$_SESSION['empl']['lokasitugas']."'",'0',true);
		}
		
		echo json_encode($resPO);
		break;
    case 'updInvoice':
		# Check existing PO
		$query = selectQuery($dbname,'keu_tagihanht','nilaiinvoice',"nopo='".$_POST['nopo']."'");
		$res = fetchData($query);
		if(!empty($res)) {
			echo $res[0]['nilaiinvoice'];
		}
		break;
    case'getPo':
        $jenisInvoice = $_POST['jnsInvoice'];
        
		// Get Akun Ppn
		$qPpn = selectQuery($dbname,'setup_parameterappl','nilai',
			"kodeaplikasi='TX' and kodeparameter='PPNINV'");
		$resPpn = fetchData($qPpn);
		$akunPpn = '';
		if(!empty($resPpn)) $akunPpn = $resPpn[0]['nilai'];
		
        $optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
        $dat="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $dat.="<div style=overflow:auto;width:100%;height:310px;>";
        $dat.="<table cellpadding=1 cellspacing=1 border=0 class='sortable'><thead>";
        $dat.="<tr class='rowheader'><td>No.</td>";
        $rPo['ppn']=0;
		
		$where = '';
		switch($jenisInvoice) {
                    
                    
                        case'bykrm':
                            
                            //if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
                            //{
                                $sortHold=" and right(nodok,3)='".$_SESSION['empl']['kodeorganisasi']."'";
                            //}
                            
                            if($param['txtfind']!='')
                            {
                                    $where=" and nodok like '%".$param['txtfind']."%'";
                            } 
                            
                            $sPo="select * from ".$dbname.".log_biayakirim where 1=1 ".$where.""
                                    . " and posting=1 ".$sortHold."  order by updatetime desc ";
                            $dat.="<td>".$_SESSION['lang']['nopo']."</td>";
                            $dat.="<td>".$_SESSION['lang']['kodebarang']."</td>";
                            $dat.="<td>".$_SESSION['lang']['namabarang']."</td>";
                            $dat.="<td>Transportir</td>";
                            $dat.="<td>".$_SESSION['lang']['jumlah']."</td></tr></thead><tbody>";
                            
                        break;
                    
			case 'po':
				if($param['txtfind']!='')
				{
					$where=" and nopo like '%".$param['txtfind']."%'";
				}
				//$where.=" and closed=0";
				$addlokal=" and lokalpusat=0 ";
				$addkdorg=" and kodeorg='".$_SESSION['org']['kodeorganisasi']."'";
				if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
				{
					$addlokal=" and lokalpusat=1 ";
					$addkdorg="";
				}
				$sPo="select distinct nopo,(subtotal + ppn) as nilaipo,ppn,kodesupplier,stat_release,matauang,nilaidiskon from ".$dbname.".log_poht where 
                                      kodeorg='".$_SESSION['org']['kodeorganisasi']."' ".$where.$addlokal."  order by tanggal desc";//and nopo not in (select distinct nopo from ".$dbname.".keu_tagihanht where tipeinvoice='p') ".$where." and kodeorg='".$_SESSION['org']['kodeorganisasi']."'order by tanggal desc";
				
				// Table Header
				$dat.="<td>".$_SESSION['lang']['nopo']."</td>";
				$dat.="<td>".$_SESSION['lang']['namasupplier']."</td>";
				$dat.="<td>".$_SESSION['lang']['matauang']."</td></tr></thead><tbody>"; 
				break;
                               
                                
			case 'sj':
				if($param['txtfind']!='')
				{
					$where="where nosj like '%".$param['txtfind']."%'";
				}
				$sPo="select distinct nosj as nopo,expeditor as kodesupplier from ".$dbname.". log_suratjalanht 
					   ".$where."  order by nosj desc";
				
				// Table Header
				$dat.="<td>".$_SESSION['lang']['nosj']."</td>";
				$dat.="<td>".$_SESSION['lang']['expeditor']."</td></tr></thead><tbody>";
				break;
			case 'ns':
				if($param['txtfind']!='')
				{
					$where="where nokonosemen like '%".$param['txtfind']."%'";
				}
				$sPo="select distinct nokonosemen as nopo,shipper as kodesupplier from ".$dbname.". log_konosemenht 
					   ".$where."  order by nokonosemen desc";
				
				// Table Header
				$dat.="<td>".$_SESSION['lang']['nokonosemen']."</td>";
				$dat.="<td>".$_SESSION['lang']['shipper']."</td></tr></thead><tbody>";
				break;
			default:
				if($param['txtfind']!='')
				{
					$where=" and notransaksi like '%".$param['txtfind']."%'";
				}
			   if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
			   {
				   $sPo="select distinct notransaksi as nopo,kodeorg,nilaikontrak as nilaipo,koderekanan as kodesupplier from ".$dbname.".log_spkht where kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."')  ".$where."  order by tanggal desc";
			   }
			   else
			   {
				   $sPo="select distinct notransaksi as nopo,kodeorg,nilaikontrak as nilaipo,koderekanan as kodesupplier from ".$dbname.".log_spkht where  kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') ".$where."   order by tanggal desc";
			   }
                            $dat.="<td>".$_SESSION['lang']['kontrak']."</td>";
                            $dat.="<td>".$_SESSION['lang']['kontraktor']."</td></tr></thead><tbody>";
			   break;
        }
        $qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
		$qPo->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
        while($rPo=$qPo->fetch()){
            
                if($jenisInvoice=='bykrm') {
                   
                    
                    #cek sudah pernah ada inv apa belum
                    $sCek="select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby "
                    . "from ".$dbname.".keu_tagihanht where nopo='".$rPo['nodok']."' and tipeinvoice='b' order by noinvoice";
					$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
					$qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $rCek = $qCek->fetch();
                    if($rCek['jmlhinvoice']!='')
                    {
                        $rPo['jumlah']=$rPo['jumlah']-$rCek['jmlhinvoice'];
                    }
                    $nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrg);
                        
                    if($rPo['jumlah']>0)
                    {
                        $whbrg="kodebarang='".$rPo['kodebarang']."'";
                        $no+=1;
                        $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nodok']."','";
                                $dat.=isset($rPo['jumlah'])? $rPo['jumlah']: 0;
                                $dat.="','".$param['jnsInvoice']."','";
                                $dat.="','".$optNmsupp[$rPo['kodetrp']]."')\" style='pointer:cursor;'><td>".$no."</td>";
                        $dat.="<td>".$rPo['nodok']."</td>";  
                        $dat.="<td>".$rPo['kodebarang']."</td>";
                        $dat.="<td>".$nmBrg[$rPo['kodebarang']]."</td>";
                        $dat.="<td>".$optNmsupp[$rPo['kodetrp']]."</td>";
                        $dat.="<td>".number_format($rPo['jumlah'])."</td></tr>";
                    }
                }
            
                
                
	        elseif($jenisInvoice=='po') {
                    if($rPo['nilaidiskon']==''){
                        $rPo['nilaidiskon']=0;
                    }
                    $nilPo=($rPo['nilaipo']-$rPo['nilaidiskon']);
                    $rPo['nilaipo']=$nilPo;
                $sCek="select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby "
                    . "from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='p' order by noinvoice";
				$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
				$qCek->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=$qCek->fetch();
                if($rCek['jmlhinvoice']!=''){
                    $rPo['nilaipo']=$rPo['nilaipo']-$rCek['jmlhinvoice'];
                    $rPo['ppn']=$rPo['ppn']-$rCek['jmlppn'];
                }
				
				// Get Kurs from Setup Mata Uang
				$qKurs = selectQuery($dbname,'setup_matauangrate','*',
									 "daritanggal<='".tanggalsystem($param['tanggal'])."' and
									 kode='".$rPo['matauang']."'","daritanggal desc, jam desc",false,1,1);
				$resKurs = fetchData($qKurs);
				$kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];
				
                if($rPo['nilaipo']>0) {
                    if($rPo['stat_release']==1) {
						$no+=1;
						$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
						$dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
						$dat.="','".$param['jnsInvoice']."','";
						$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
						$dat.="','".$optNmsupp[$rPo['kodesupplier']]."','".
							$rPo['matauang']."','".$kurs."')\" style='pointer:cursor;'>";
						$dat.="<td>".$no."</td>";
						$dat.="<td>".$rPo['nopo']."</td>";
						$dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td>";
						$dat.="<td>".$rPo['matauang']."</td></tr>";
					}
                }
                
            } elseif($jenisInvoice=='sj') {
                $no+=1;
                $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
                            $dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
                            $dat.="','".$param['jnsInvoice']."','";
                            $dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
                            $dat.="','".$optNmsupp[$rPo['kodesupplier']]."')\" style='pointer:cursor;'><td>".$no."</td>";
                $dat.="<td>".$rPo['nopo']."</td>";
                $dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td></tr>";
            } else {
				$notransaksi = $rPo['nopo'];
				
				// Get Tax
				$optTax = makeOption($dbname,'log_spk_tax','noakun,nilai',
					"notransaksi='".$notransaksi."' and kodeorg='".$rPo['kodeorg']."'");
				
				// Nilai Invoice ditambahkan dengan Ppn dan dikurangi PPh
				foreach($optTax as $noakun=>$nilai) {
					if($akunPpn==$noakun) {
						$rPo['nilaipo'] += $nilai;
					} else {
						$rPo['nilaipo'] -= $nilai;
					}
				}
				
				$no+=1;
                $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
                            $dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
                            $dat.="','".$param['jnsInvoice']."','";
                            $dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
                            $dat.="','".$optNmsupp[$rPo['kodesupplier']]."')\" style='pointer:cursor;'><td>".$no."</td>";
                $dat.="<td>".$rPo['nopo']."</td>";
                $dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td></tr>";
			}
        }
        $dat.="</tbody></table></div></fieldset>";
        echo $dat;
        break;
    default:
	break;
}

// select * from organisasi where length(kodeorganisasi)>=6 
// and (induk in (select kodeorganisasi from organisasi where induk in (select kodeorganisasi from organisasi where induk='CKS' ))) or induk in ((select kodeorganisasi from organisasi where induk='CKS'));

function formHeader($mode,$data) {
    global $dbname;
	
	if($mode=='edit') {
		$whereOrg = "kodeorganisasi='".$data['kodeorg']."' and tipe<>'BLOK'";
    } else {
		$whereOrg = "left(kodeorganisasi,4)='".substr($_SESSION['empl']['lokasitugas'],0,4)."' and tipe IN ('KEBUN','KANWIL')";
    }
	
	$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
	$whereKary .= "  and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
	
	$str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$whereOrg."";
	$optOrg1 = "";
	$res = fetchData($str);
	foreach($res as $bar) {
		$optOrg1 .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
    }
	
	//mandor clerk asisten
    $str = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where (b.namajabatan like '%mandor%' or ".
		"b.namajabatan like '%assistant%' or b.namajabatan like '%asisten%' or b.namajabatan like '%clerk%' or b.namajabatan like '%krani%') and ".$whereKary.
		" order by a.namakaryawan asc";
		//exit('error'.$str);
    $optMandor = $optMandor1 = $optAsisten = $optKrani = "<option value=''></option>";
    $res = fetchData($str);
    foreach($res as $bar) {
		if(@$data['mandor'] == $bar['karyawanid']){
			$optMandor .= "<option value='".$bar['karyawanid']."' selected>".$bar['namakaryawan']." [".($bar['karyawanid'])."] ".$bar['namajabatan']."</option>";
		}else{
			$optMandor .= "<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." [".($bar['karyawanid'])."] ".$bar['namajabatan']."</option>";
		}
		if(@$data['mandor1'] == $bar['karyawanid']){
			$optMandor1 .= "<option value='".$bar['karyawanid']."' selected>".$bar['namakaryawan']." [".($bar['karyawanid'])."] ".$bar['namajabatan']."</option>";
		}else{
			$optMandor1 .= "<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." [".($bar['karyawanid'])."] ".$bar['namajabatan']."</option>";
		}
		if(@$data['assisten'] == $bar['karyawanid']){
			$optAsisten .= "<option value='".$bar['karyawanid']."' selected>".$bar['namakaryawan']." [".($bar['karyawanid'])."] ".$bar['namajabatan']."</option>";
		}else{
			$optAsisten .= "<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." [".($bar['karyawanid'])."] ".$bar['namajabatan']."</option>";
		}
		if(@$data['krani'] == $bar['karyawanid']){
			$optKrani .= "<option value='".$bar['karyawanid']."' selected>".$bar['namakaryawan']." [".($bar['karyawanid'])."] ".$bar['namajabatan']."</option>";
		}else{
			$optKrani .= "<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." [".($bar['karyawanid'])."] ".$bar['namajabatan']."</option>";
		}
    }
	
	$tab = "";
	$tab .= "<fieldset style='float:left'>
		<legend id='title_Form'><b>".($mode=='edit' ? $_SESSION['lang']['editheader'] : $_SESSION['lang']['addheader'])."</b></legend>
		<div id='Tambah Header'>
		<table>
			<tr>
				<td style='padding-right:20px;'>".$_SESSION['lang']['notransaksi']."</td>
				<td style='padding-right:20px;'>
					<input id='notransaksi' class='myinputtext' type='text' onkeypress=\"return tanpa_kutip(event)\" value='".@$data['notransaksi']."' style='width:150px' disabled='disabled' />
				</td>
				
				<td style='padding-right:20px;'>".$_SESSION['lang']['nikmandor1']."</td>
				<td style='padding-right:20px;'>
					<select id=nikmandor1 style='width:150px'>".$optMandor1."</select>
					<img id=nikmandor1_find onclick=\"z.elSearch('nikmandor1',event)\" class=resicon src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td style='padding-right:20px;'>".$_SESSION['lang']['kodeorg']."</td>
				<td style='padding-right:20px;'>
					<select id=kodeorg style='width:150px'>".$optOrg1."</select>
				</td>
				
				<td style='padding-right:20px;'>".$_SESSION['lang']['asisten']."</td>
				<td style='padding-right:20px;'>
					<select id=nikasisten style='width:150px'>".$optAsisten."</select>
					<img id=nikasisten_find onclick=\"z.elSearch('nikasisten',event)\" class=resicon src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td style='padding-right:20px;'>".$_SESSION['lang']['tanggal']."</td>
				<td style='padding-right:20px;'>
					<input id=tanggal class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" value='".@$data['tanggal']."' style='width:150px' readonly='readonly' onmousemove=\"setCalendar(this.id)\" />
				</td>
				
				<td style='padding-right:20px;'>".$_SESSION['lang']['keraniafdeling']."</td>
				<td style='padding-right:20px;'>
					<select id=keranimuat style='width:150px'>".$optKrani."</select>
					<img id=keranimuat_find onclick=\"z.elSearch('keranimuat',event)\" class=resicon src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td style='padding-right:20px;'>".$_SESSION['lang']['mandor']."</td>
				<td style='padding-right:20px;'>
					<select id=nikmandor style='width:150px'>".$optMandor."</select>
					<img id=nikmandor_find onclick=\"z.elSearch('nikmandor',event)\" class=resicon src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
				
				<!-- <td style='padding-right:20px;'>".$_SESSION['lang']['notransaksi']."</td>
				<td style='padding-right:20px;'></td> -->
			</tr>
			<tr>
				<td style='padding-right:20px;'></td>
				<td style='padding-right:20px;'>
					<button id='addHead' name='addHead' class='mybutton' onclick=\"addHead()\">Simpan</button>
				</td>
			</tr>
		</table>
		</div>
	</fieldset>";
	
	$tab .= "<fieldset>
		<legend><b>".$_SESSION['lang']['total']."</b></legend>
		<div>
		<table>
			<tr>
				<td colspan=2><b>".$_SESSION['lang']['prestasi']."</b></td>
				<td colspan=2><b>".$_SESSION['lang']['absensi']."</b></td>
			</tr>
			<tr>
				<td style='padding-right:20px;'>".$_SESSION['lang']['jumlahhk']."</td>
				<td style='padding-right:20px;'>
					<input id=totalPresHk class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:70px' disabled=disabled realvalue='0'>
				</td>
				
				<td style='padding-right:20px;'>".$_SESSION['lang']['jumlahhk']."</td>
				<td style='padding-right:20px;'>
					<input id=totalAbsHk class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:70px' disabled=disabled realvalue='0'>
				</td>
			</tr>
			<tr>
				<td style='padding-right:20px;'>".$_SESSION['lang']['umr']."</td>
				<td style='padding-right:20px;'>
					<input id=totalPresUmr class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:70px' disabled=disabled realvalue='0'>
				</td>
				
				<td style='padding-right:20px;'>".$_SESSION['lang']['umr']."</td>
				<td style='padding-right:20px;'>
					<input id=totalAbsUmr class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:70px' disabled=disabled realvalue='0'>
				</td>
			</tr>
			<tr>
				<td style='padding-right:20px;'>".$_SESSION['lang']['premi']."</td>
				<td style='padding-right:20px;'>
					<input id=totalPresIns class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:70px' disabled=disabled realvalue='0'>
				</td>
				
				<td style='padding-right:20px;'>".$_SESSION['lang']['premi']."</td>
				<td style='padding-right:20px;'>
					<input id=totalAbsIns class=myinputtextnumber onkeypress=\"return angka_doang(event)\" type=text value='0' style='width:70px' disabled=disabled realvalue='0'>
				</td>
			</tr>
			<tr>
				<td style='padding-bottom:10px;'>&nbsp;</td>
			</tr>
		</table>
		</div>
	</fieldset>";
	
	return $tab;
}

function getNoTransaksi($tanggal){
	global $dbname;
	
	$data = "";
	
	#=== Generate No Transaksi
	# Get Existing Data
	$fWhere = "notransaksi like '%".tanggalsystem($tanggal)."%' and notransaksi like '%".$_SESSION['empl']['lokasitugas']."%'";
	$fQuery = selectQuery($dbname,'vhc_splht','notransaksi',$fWhere);
	$tmpNo = fetchData($fQuery);
	
	# Generate No Transaksi
	if(count($tmpNo)==0) {
	    $data = tanggalsystem($tanggal)."/".$_SESSION['empl']['lokasitugas']."/SPL/001";
	} else {
	    # Get Max No Urut
	    $maxNo = 1;
	    foreach($tmpNo as $row) {
		$tmpRow = explode('/',$row['notransaksi']);
		$noUrut = (int)$tmpRow[3];
		if($noUrut>$maxNo)
		    $maxNo = $noUrut;
	    }
	    $currNo = addZero($maxNo+1,3);
	    $data = tanggalsystem($tanggal)."/".$_SESSION['empl']['lokasitugas']."/PNG/".$currNo;
	}
	
	return $data;
}
?>