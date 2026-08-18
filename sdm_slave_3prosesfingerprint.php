<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
include_once('lib/zFunction.php');
	error_reporting(0);
$param     = $_POST;if(count($param)==0){$param= $_GET;}
$method    = checkPostGet('method','');
$tipeprint = checkPostGet('tipeprint','');
$unit      = checkPostGet('unit','');
$subunit   = checkPostGet('subunit','');
$periode   = checkPostGet('periode','');
$tanggal   = checkPostGet('tanggal','');
$nik       = checkPostGet('nik','');
$karyawanid= checkPostGet('karyawanid','');
$jab       = getPostingJabatan('prosesfp');
$user_id   = $_SESSION['standard']['userid'];
$tglskrg   = date('Y-m-d H:i:s');


$str= "select * from ".$dbname.".sdm_5mastershift where status='1' order by id";
$res= fetchdata($str);
foreach($res as $val){
	$arrnamashift[$val['shift']]=$val['namashift'];
}
$str= "select * from ".$dbname.".sdm_5tipekaryawan";
$res= fetchdata($str);
foreach($res as $val){
	$arrnamatipe[$val['id']]=$val['tipe'];
}


switch($method){
	case'html':
		$tab="<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['tipekaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['shift']."</th>
				<th rowspan='3'>".$_SESSION['lang']['sumber']."</th>
				<th colspan='5'>Tanggal</th>
				<th rowspan='3'>".$_SESSION['lang']['penjelasan']."</th>
				<th rowspan='3'>SDM Absensi</th>
			</tr>
			<tr class=rowheader style='text-align:center;font-weight:bold'>";
			$tab.="<th colspan=5 >".$param['tanggal']."</th>";
			$tab.="</tr>";
			$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			$tab.="<th>In</th>";
			$tab.="<th>Out</th>";
			$tab.="<th>In</th>";
			$tab.="<th>Out</th>";
			$tab.="<th>Abs</th>";
			$tab.="</tr>";
			$tab.="</thead>
			<tbody>";
			
			$nmsumber=array('manual'=>'BA Absensi','upload'=>'Fingerprint');
			
			$where=" and kodeorg like '".$param['kodeorg']."%'";
			$where.=" and tanggal='".$param['tanggal']."'";
			
			$str = "select * from ".$dbname.".sdm_absensidt where 1=1 ".$where."";
			$res = fetchdata($str);
			foreach($res as $bar){
				$sdmabsensi[$bar['idfp']]="&#10003;";
			}			
			
			$where=" and kodeorg='".$param['kodeorg']."'";
			$where.=" and subbagian='".$param['subbagian']."'";
			$where.=" and tanggalabsen='".$param['tanggal']."'";
			//$where.=" and sumber='upload'";
			$str = "select * from ".$dbname.".upload_absensi where 1=1 ".$where."";
			$res = fetchdata($str);
			foreach($res as $bar){
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".getKary($bar['karyawanid'],'nik')."</td>";
				$tab.="<td align=left>".getKary($bar['karyawanid'])."</td>";
				$tab.="<td>".getNamaJabatan(getKary($bar['karyawanid'],'kodejabatan'))."</td>";
				$tab.="<td align=left>".$arrnamatipe[getKary($bar['karyawanid'],'tipekaryawan')]."</td>";
				$tab.="<td align=left>".$bar['shift']." - ".$arrnamashift[$bar['namashift']]."</td>";
				$tab.="<td align=center>".$nmsumber[$bar['sumber']]."</td>";
				$tab.="<td align=center width=75px>".waktunormal($bar['jam'])."</td>";
				if($bar['jam2']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam2'])."</td>";
				}
				if($bar['jam3']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam3'])."</td>";
				}
				if($bar['jam4']=='0000-00-00 00:00:00'){
					$tab.="<td align=center width=75px></td>";
				}else{					
					$tab.="<td align=center width=75px>".waktunormal($bar['jam4'])."</td>";
				}
				
				$tab.="<td align=center>".$bar['absensi']."</td>";
				if($bar['penjelasan']!=''){					
					$tab.="<td align=left>".$bar['penjelasan']."<br><font style=font-size:9px;font-style:italic>".getKary($bar['updatedby'])." ".waktunormal($bar['updatedtime'])."</font></td>";
				}else{
					$tab.="<td align=center></td>";					
				}
				if($sdmabsensi[$bar['id']]!=''){					
					$tab.="<td align=center style=background-color:green;>".$sdmabsensi[$bar['id']]."</td>";
				}else{
					$tab.="<td align=center>x</td>";
				}
			}
			
			
		echo $tab;	
	break;
	case'unposting':
		try {
		$owlPDO->beginTransaction();

			$where=" and kodeorg='".$param['kodeorg']."'";
			$where.=" and subbagian='".$param['subbagian']."'";
			$where.=" and tanggalabsen='".$param['tanggal']."'";
			//$where.=" and sumber='upload'";
			
			$str = "select * from " . $dbname . ".sdm_5fptoabsensi where kodeorg='".$param['kodeorg']."' and subbagian='".$param['subbagian']."'";
			$res = fetchData($str);
			if(count($res)==0){
				exit("Error : SDM - Setup - FP to Absensi belum di setup.");
			}
			$insert = $res[0]['absensi'];
			
			if($insert==0){
			}else{
				$str = "select * from ".$dbname.".upload_absensi where 1=1 ".$where."";
				$res = fetchdata($str);
				foreach($res as $bar){			
					$str = "delete from ".$dbname.".sdm_absensidt where 1=1 and idfp='".$bar['id']."' and karyawanid='".$bar['karyawanid']."' and tanggal='".$bar['tanggalabsen']."'";
					$owlPDO->exec($str);
				}
			}

			$str = "update ".$dbname.".upload_absensi set posting='0', postingby='".$user_id."', postingtime='".$tglskrg."' where 1=1 ".$where."";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;
	case'posting':
		try {
		$owlPDO->beginTransaction();

		$where=" and kodeorg='".$param['kodeorg']."'";
		$where.=" and subbagian='".$param['subbagian']."'";
		$where.=" and tanggalabsen='".$param['tanggal']."'";
		// $where.=" and sumber='upload'";
		
		$tipesubbagian= getNamaOrg($param['subbagian'],'tipe');
		$tipeorg      = getNamaOrg($param['kodeorg'],'tipe');
		
		$insert="";
		$str = "select * from " . $dbname . ".sdm_5fptoabsensi where kodeorg='".$param['kodeorg']."' and subbagian='".$param['subbagian']."'";
		$res = fetchData($str);
		if(count($res)==0){
			exit("Error : SDM - Setup - FP to Absensi belum di setup.");
		}

		$insert = $res[0]['absensi'];
		$akun = $res[0]['noakun'];
		
		if($insert==0){
			#tidak di insert ke sdm absensi
		}else if($insert==1){
			#di insert ke sdm absensi
			if($param['subbagian']==''){
				$divisikary=$param['kodeorg'];
			}else{
				$divisikary=$param['subbagian'];
			}

			$str = "select * from " . $dbname . ".sdm_absensiht where tanggal='".$param['tanggal']."' and kodeorg='".$divisikary."'";
			$res = count(fetchData($str));
			# jika belum ada di ht maka insert dulu
			if($res==0){
				$data = array(
					'tanggal' => $param['tanggal'],
					'kodeorg' => $divisikary,
					'periode' => substr($param['tanggal'],0,7),
					'updateby'=> $_SESSION['standard']['userid']
				);
				
				# Insert sdm_absensiht
				$query = insertQuery($dbname,'sdm_absensiht',$data,array_keys($data));
				$owlPDO->exec($query);
			}

			
			$param['divisi']=$param['subbagian'];
			$opttipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
			$opttipekaryawan = makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan');
			
			$arrthn = explode("-",$param['tanggal']);
			$periodegaji = $arrthn[0]."-".$arrthn[1]; 

			$str = "select * from ".$dbname.".upload_absensi where 1=1 ".$where."";
			$res = fetchdata($str);
			foreach($res as $bar){
				$tipekary=false;

				if($opttipekaryawan[$bar['karyawanid']] == 0){
					$noakun = '7110101';// -> AKUN GAJI STAFF
				}else{
					$tipekary=true;
					$noakun = $akun;
				}

				## Jabatan  ke biaya keamanan
				$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'HR' and kodeparameter = 'JABSECUR'";
				$res=fetchdata($str);
				$jabatanSecur = $res[0]['nilai'];

				$newArrayJab = array();
				$str="select *  from ".$dbname.".sdm_5jabatan where kodejabatan in (".$jabatanSecur.")";
				$res=fetchdata($str);
				foreach($res as $val){
					$newArrayJab[$val['kodejabatan']] = $val['kodejabatan'];
				}

				if(in_array(getKary($bar['karyawanid'],'kodejabatan'),$newArrayJab)){
					$noakun = '7120400'; // -> Akun biaya kaeamanan
				}

				## Jabatan ke biayan ke prasarana umum
				$str1="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'HR' and kodeparameter = 'JABPRASARA'";
				$res1=fetchdata($str1);
				$jabatanPrasarana = $res1[0]['nilai'];

				$newArrayJabPra = array();
				$str="select *  from ".$dbname.".sdm_5jabatan where kodejabatan in (".$jabatanPrasarana.")";
				$res=fetchdata($str);
				foreach($res as $val){
					$newArrayJabPra[$val['kodejabatan']] = $val['kodejabatan'];
				}

				if(in_array(getKary($bar['karyawanid'],'kodejabatan'),$newArrayJabPra)){
					$noakun = '7140912'; // -> Akun biaya Prasarana
				}
						
				# ambil gaji pokok
				$jlhumr=0;
				$sql = "select jumlah from ".$dbname.".sdm_5gajipokok where karyawanid='".$bar['karyawanid']."' and tahun='".$periodegaji."' and idkomponen in ('1')";					
				$req = fetchdata($sql);
				if(count($req)==0 and $tipekary==true){
					throw new PDOException("Gaji pokok karyawan an. ".getKary($bar['karyawanid'])." belum ada.");
				}

				$jlhumr = $req[0]['jumlah']/25;
				
				$day = date('D', strtotime($bar['tanggalabsen']));
				$sql = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$bar['tanggalabsen']."' and (kebun='GLOBAL' or kebun='".$bar['kodeorg']."')";
				$row = fetchdata($sql);
				$absensi="H";

				if(@$row[0]['keterangan']=='libur'){
					if(getKary($bar['karyawanid'],'tipekaryawan') != '4'){
						$absensi="LN";
					}else{
						$absensi="H";						
					}
				}

				if ($day=='Sun'){
					if(getKary($bar['karyawanid'],'tipekaryawan') != '4'){
						$absensi="MG";
					}else{
						$absensi="H";						
					}
				}
	
				# insert
				$data = array();
				$data = array(
					'kodeorg'           => $divisikary,
					'tanggal'           => $param['tanggal'],
					'karyawanid'        => $bar['karyawanid'],
					'noakun'            => $noakun,
					'absensi'           => $absensi,
					'jam'               => substr($bar['jam'],-8),
					'jamistirahatdari'  => substr($bar['jam2'],-8),
					'jamistirahatsampai'=> substr($bar['jam3'],-8),
					'jamPlg'            => substr($bar['jam4'],-8),
					'premi'             => 0,
					'hk'                => 1,
					'umr'               => $jlhumr,
					'penjelasan'        => "Auto Form Fingerprint",
					'norefrensi'        => "Auto Form Fingerprint",
					'idfp'              => $bar['id']
				);
				
				$jlhabsen=0;

				#cek sudah di absen atau belum
				$sql = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$bar['karyawanid']."' and tanggal='".$param['tanggal']."'"; 
				$req = fetchdata($sql);
				if(count($req)>0){					
					$jlhabsen+=1;
				}				
				$sql = "select * from ".$dbname.".kebun_aktifitas where (nikmandor='".$bar['karyawanid']."' or nikmandor1 ='".$bar['karyawanid']."' or nikasisten ='".$bar['karyawanid']."' or keranimuat ='".$bar['karyawanid']."') and tanggal='".$param['tanggal']."'"; #exit("error".$sql);
				$req = fetchdata($sql);
				if(count($req)>0){					
					$jlhabsen+=1;
				}
				
				$sql = "select * from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where (a.nik ='".$bar['karyawanid']."' or a.nikpemel ='".$bar['karyawanid']."') and b.tanggal='".$param['tanggal']."'"; #exit("error".$sql);
				$req = fetchdata($sql);
				if(count($req)>0){					
					$jlhabsen+=1;
				}
				
				$sql = "select * from ".$dbname.".vhc_runhk where idkaryawan ='".$bar['karyawanid']."' and tanggal='".$param['tanggal']."'"; #exit("error".$sql);
				$req = fetchdata($sql);
				if(count($req)>0){					
					$jlhabsen+=1;
				}
				
				# Insert sdm_absensidt
				$query = insertQuery($dbname,'sdm_absensidt',$data,array_keys($data));
				if($jlhabsen==0){
					$owlPDO->exec($query);
				}
			}
		}

		$str = "update ".$dbname.".upload_absensi set posting='1', postingby='".$user_id."', postingtime='".$tglskrg."' where 1=1 ".$where."";
		$owlPDO->exec($str);
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	case'delete':
		$where=" and kodeorg='".$param['kodeorg']."'";
		$where.=" and subbagian='".$param['subbagian']."'";
		$where.=" and tanggalabsen='".$param['tanggal']."'";
		$where.=" and sumber='upload'";
	
		$str = "delete from ".$dbname.".upload_absensi where 1=1 ".$where."";
        $owlPDO->exec($str);
		
	break;
	case'loaddata':
		$where="";
		if($param['kodeorg']!=''){			
			$where=" and kodeorg like '%".$param['kodeorg']."%'";
		}else{
			$where=" and kodeorg in (".getOrgDetail(2).")";			
		}
		
		if($param['tanggal']!=''){			
			$where.=" and tanggalabsen like '%".tanggalsystemn($param['tanggal'])."%'";
		}
		if($param['posting']=='1'){			
			$where.=" and posting > '0'";
		}
		if($param['posting']=='0'){			
			$where.=" and posting = '0'";
		}
		
		if($param['divisi']=='kantor'){
			$where.=" and subbagian = ''";
		}elseif($param['divisi']!=''){
			$where.=" and subbagian like '%".$param['divisi']."%'";
		}
		
		if($param['karyawanid']!=''){			
			$where.=" and karyawanid in (select karyawanid from " . $dbname . ".datakaryawan where namakaryawan like '%".$param['karyawanid']."%' or nik like '%".$param['karyawanid']."%')";
		}
		
        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
		$tab = "";
        $no = $maxdisplay;
		$colspan = 22;
		
		$tab="<table class='sortable' cellspacing='1' cellpadding='7' border='0' width='100%'>
			<thead>
				<tr class=rowheader>
					<th align=center width=40px>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center >" . $_SESSION['lang']['organisasi'] . "</th>
					<th align=center >" . $_SESSION['lang']['divisi'] . "</th>
					<th align=center width=100px>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center >" . $_SESSION['lang']['posting'] . "</th>
					<th align=center >" . $_SESSION['lang']['updateby'] . "</th>
					<th align=center >Tanggal<br>Update</th>
					<th align=center >" . $_SESSION['lang']['posted'] . "</th>
					<th align=center >Tanggal<br>Posting</th>
					<th align=center colspan='7'>" . $_SESSION['lang']['action'] . "</th>
			</thead>
			<tbody>";
		
        $sql = "select count(distinct id) as notr from " . $dbname . ".upload_absensi a where 1=1 ".$where." group by kodeorg, subbagian, tanggalabsen";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=".$colspan." align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
		
		$str = "select a.*, sum(posting) as posted from " . $dbname . ".upload_absensi a where 1=1 ".$where." group by kodeorg, subbagian, tanggalabsen order by tanggalabsen desc, subbagian asc limit " . $offset . "," . $limit . ""; 
        $res = fetchdata($str);
		foreach ($res as $bar) {
			$sql = "select * from " . $dbname . ".upload_absensi a where 1=1 and kodeorg='".$bar['kodeorg']."' and subbagian='".$bar['subbagian']."' and tanggalabsen='".$bar['tanggalabsen']."' group by sumber"; 
			$req = fetchdata($sql);
			if(count($req)>0){				
				$sumberdata='manual';
			}
			foreach ($req as $val) {
				if($val['sumber']=='upload'){
					$sumberdata='upload';
				}
			}
			
			$sql = "select sum(posting) as posted from " . $dbname . ".upload_absensi a where 1=1 and kodeorg='".$bar['kodeorg']."' and subbagian='".$bar['subbagian']."' and tanggalabsen='".$bar['tanggalabsen']."'"; #and sumber='upload'"; 
			$ren = fetchdata($sql);
			$bar['posted'] = $ren[0]['posted'];
			
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodeorg']." - ".getNamaOrg($bar['kodeorg'])."</td>";
			if(getNamaOrg($bar['subbagian'])!=''){
				$divisi=$bar['subbagian']." - ".getNamaOrg($bar['subbagian']);
			}else{				
				$divisi=$bar['kodeorg']." - UMUM";
			}
			$tab.="<td align=left>".$divisi."</td>";
            $tab.="<td align=center>".$bar['tanggalabsen']."</td>";
            $tab.="<td align=center>".$bar['posted']."</td>";
            $tab.="<td align=center>".getKary($bar['updatedby'])."</td>";
            $tab.="<td align=center>".waktunormal($bar['updatedtime'])."</td>";
            $tab.="<td align=center>".getKary($bar['postingby'])."</td>";
            $tab.="<td align=center>".waktunormal($bar['postingtime'])."</td>";
            // $tab.="<td align=center>".$bar['posted']."</td>";
			
			if($bar['posted'] == 0) {
                $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".$bar['kodeorg']."','".$bar['subbagian']."','".tanggalnormal($bar['tanggalabsen'])."','".$bar['sumber']."','".$divisi."');\" ></td>";
                
				$tab.="<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['kodeorg']."','".$bar['subbagian']."','".$bar['tanggalabsen']."','".$bar['sumber']."');\" ></td>";
				
				$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Posting ???' onclick=\"postingx('".$bar['kodeorg']."','".$bar['subbagian']."','".$bar['tanggalabsen']."','".$bar['sumber']."');\" ></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('".$bar['kodeorg']."','".$bar['subbagian']."','".$bar['tanggalabsen']."','".$bar['sumber']."');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				if($sumberdata=='manual'){
					$tab.="<td align=center colspan=3>BA Absensi</td>";
				}else{					
					$tab.="<td align=center width=25px></td><td align=center width=25px></td>";
					$tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
				}
            }
            $tab.="<td align=center width=25px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' 
                    onclick=\"html('".$bar['kodeorg']."','".$bar['subbagian']."','".$bar['tanggalabsen']."','".$bar['sumber']."');\" ></td>";
			
			$tab.="</tr>";
		}
		
		$tab.="</tbody>";
		$tab.="<tfoot>";
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		$tab.="</tfoot>";
		$tab.="</table>";
		echo $tab;
	break;
	case'insert':
		try {
			$owlPDO->beginTransaction();

			$statusposting=0;
			if($param['subbagian']=='all'){
				$wh=" and subbagian like '%'";
			}else{
				$wh=" and subbagian='".$param['subbagian']."'";
			}

			foreach($param['tanggal'] as $key => $tgl){
				$sql = "select * from ".$dbname.".upload_absensi where kodeorg='".$param['kodeorg']."' ".$wh." and  tanggalabsen='".$tgl."' and sumber!='manual' and posting='1'";
				$res = fetchdata($sql);
				if(count($res)>0){
					$statusposting++;
				}
			}
			
			if($statusposting>0){				
				throw new PDOException("Data sudah ada yang diposting.");
			}
			
			foreach($param['tanggal'] as $key => $tgl){
				if($param['absen'][$key]=='H'){
					$sql = "select * from ".$dbname.".upload_absensi where karyawanid='".$param['karyawanid']."' and  tanggalabsen='".$tgl."'";
				$res = fetchdata($sql);
				$insert=true;
				foreach($res as $bar){
					if($bar['posting']=='1' and $bar['sumber']!='manual'){
						throw new PDOException("Data sudah diposting ".getNamaKaryawan($param['karyawanid']).".");
					}	
					if($bar['sumber']=='upload'){
						$str = "delete from " . $dbname . ".upload_absensi where id='".$bar['id']."'";
						$owlPDO->exec($str);
					}
					if($bar['sumber']=='manual'){
						$insert=false;
					}
				}
				if($param['penjelasan'][$key]!=''){
					$adjust='1';
				}else{
					$adjust='0';
				}
				
				$sql = "select * from ".$dbname.".sdm_5shift where id='".$param['idshift'][$key]."'";
				$res = fetchdata($sql);
				foreach($res as $val){
					$shift    = $val['shift'];
					$namashift= $val['namashift'];
				}
				
				if($shift == ''){
					exit("Warning : Ada karyawan yang belum memiliki shift kerja <b>" . getNamaKaryawan($param['karyawanid']) . "</b> ( SDM -> SETUP -> FINGER -> SHIFT KARYAWAN )");
				}
				

				$data = array();
				$data = array(
					'kodeorg'     => $param['kodeorg'],
					'subbagian'   => $param['subbagian'],
					'tanggalabsen'=> $tgl,
					'karyawanid'  => $param['karyawanid'],
					'absensi'     => $param['absen'][$key],
					'idshift'     => $param['idshift'][$key],
					'shift'       => $shift,
					'namashift'   => $namashift,
					'jam'         => $param['masuk'][$key],
					'jam2'        => $param['istout'][$key],
					'jam3'        => $param['istin'][$key],
					'jam4'        => $param['pulang'][$key],
					'sumber'      => 'upload',
					'adjust'      => $adjust,
					'penjelasan'  => $param['penjelasan'][$key],
					'flag'        => '0',
					'fingerprint' => '0',
					'posting'     => '0',
					'postingby'   => "",
					'postingtime' => "",
					'createdby'   => $_SESSION['standard']['userid'],
					'createdtime' => date('Y-m-d H:i:s'),
					'updatedby'   => $_SESSION['standard']['userid'],
					'updatedtime' => date('Y-m-d H:i:s')
				);
				
					if($param['absen'][$key]!='' and $insert==true){
						$query = insertQuery($dbname,'upload_absensi',$data,array_keys($data));
						$owlPDO->exec($query);
					}
				}
			}
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
	break;
	case'getsubunit':
		$optSubUnit="<option value='all'>Pilih Data</option>";
		$optSubUnit.="<option value=''>".$unit." - UMUM</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$s="";
			if($param['subbagian']==$val['kodeorganisasi']){
				$s="selected";
			}
			$optSubUnit.="<option value='".$val['kodeorganisasi']."' ".$s.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;
	
	case'preview':
		$tab="";
		
		$awal =tanggalsystemn($param['tglawal']);
		$akhir=tanggalsystemn($param['tglakhir']);
		
		$tglawal =substr(tanggalsystemn($param['tglawal']),-2);
		$tglakhir=substr(tanggalsystemn($param['tglakhir']),-2);
		$bulan   =tanggalbulan($tglakhir);

		$periode_abs = substr($awal,0,7);
		
		$rangetgl = rangeTanggalarr($awal,$akhir);
		
		if(substr($awal,0,7)!=substr($akhir,0,7)){
			exit("errorcode : Tanggal awal dan tanggal akhir harus didalam bulan yang sama.");
		}

		if($subunit == 'all'){
			exit("Warning : Sub Unit Wajib Diisi");
		}	

		$where="";
		if($subunit=='all'){
			$where.="";
		}else if($subunit==''){
			$where.=" and subbagian=''";
		}else{
			$where.=" and subbagian='".$subunit."'";
		}
		
		
		$wherekary=" and ((tanggalkeluar = '0000-00-00' or tanggalkeluar > '".$awal."') OR (statuskaryawan = 'Percobaan' AND tanggalkeluar != '0000-00-00'))";
		$wherekary.=" and tanggalmasuk<='".$akhir."'";
		if($param['tipekary']!=''){
			$tipekar=explode(",",$param['tipekary']);
			foreach($tipekar as $tipe){
				$datatipe[$tipe]=$tipe;
			}
			
			$wherekary.=" and tipekaryawan in ('".implode("','",$datatipe)."')";
		}
		
		#test
		// $karyidtest="0000000058";
		// $pintestkary="0000000058";
		// $wherekary.=" and karyawanid='".$karyidtest."'";
		// $wheretest=" and karyawan='".$karyidtest."'";
		// $pintest=" and pin='".$pintestkary."' and sn in (select sn from att_pegawai where 1=1 ".$wheretest." and pin='".$pintestkary."')";
		#test

		$dakarbulanan=0;
		$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode_abs."' "; 
		$res = fetchdata($str);
		if(count($res)>0){ 
			$dakarbulanan=1;
		}

		if($dakarbulanan==1){
			$str = "select * from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode_abs."' and (periodeakhirgaji>='" . $periode_abs . "' or periodeakhirgaji='') ".$where." ".$wherekary." order by namakaryawan asc  "; 
			$res = fetchdata($str);
			$arrkary = $res;

			$arrkar = array();
			$str="select sn,pin,karyawan from ".$dbname.".att_pegawai where karyawan!='0000000000' and karyawan in (select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and periodegaji='".$periode_abs."' and (periodeakhirgaji>='" . $periode_abs . "' or periodeakhirgaji='') and lokasitugas='".$unit."' ".$where." ".$wherekary.") and sn in (select distinct sn from ".$dbname.".att_log where substr(scan_date,1,10) between '".$awal."' and '".tglbesok($akhir)."' ".$pintest.") ";
			$res=fetchdata($str);
			foreach($res as $val){
				$arrkar[$val['sn']][$val['karyawan']]=$val['karyawan'];
			}
		}else{
			$str = "select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." ".$wherekary." order by namakaryawan asc";
			$res = fetchdata($str);
			$arrkary = $res;

			$arrkar = array();
			$str="select sn,pin,karyawan from ".$dbname.".att_pegawai where karyawan!='0000000000' and karyawan in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." ".$wherekary.") and sn in (select distinct sn from ".$dbname.".att_log where substr(scan_date,1,10) between '".$awal."' and '".tglbesok($akhir)."' ".$pintest.") ";
			$res=fetchdata($str);
			foreach($res as $val){
				$arrkar[$val['sn']][$val['karyawan']]=$val['karyawan'];
			}
		}

		$str = "select * from ".$dbname.".sdm_5shift where kodeorg = '".$unit."'";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("errorcode : Master shift untuk kode organisasi ".$unit." belum ada.");
		}

		foreach($res as $val){
			$jamshiftmasuk[$val['id']]    = $val['masuk'];
			$jamshiftoutist[$val['id']]   = $val['keluar_ist'];
			$jamshiftinist[$val['id']]    = $val['masuk_ist'];
			$jamshiftpulang[$val['id']]   = $val['keluar'];
			$jamshifttoleransi[$val['id']]= $val['toleransi'];
			$jamshiftbatasawal[$val['id']]= $val['batas_awal'];
			$jamshifttipe_shift[$val['id']]= $val['tipe_shift'];
		}
		
		$jamshift = array();
		$str = "select * from ".$dbname.".sdm_5shiftanggota where kodeorg = '".$unit."' ".$where." and tanggal between '".$awal."' and '".tglbesok($akhir)."' order by tanggal";
		$res = fetchdata($str);
		foreach($res as $val){
			$jamshift[$val['karyawanid']][$val['tanggal']]['namashift']= $val['namashift'];
			$jamshift[$val['karyawanid']][$val['tanggal']]['ke']       = $val['shift'];
			$jamshift[$val['karyawanid']][$val['tanggal']]['idshift']  = $val['idshift'];
			$jamshift[$val['karyawanid']][$val['tanggal']]['masuk']    = $jamshiftmasuk[$val['idshift']];
			$jamshift[$val['karyawanid']][$val['tanggal']]['outist']   = $jamshiftoutist[$val['idshift']];
			$jamshift[$val['karyawanid']][$val['tanggal']]['inist']    = $jamshiftinist[$val['idshift']];
			$jamshift[$val['karyawanid']][$val['tanggal']]['pulang']   = $jamshiftpulang[$val['idshift']];
			$jamshift[$val['karyawanid']][$val['tanggal']]['toleransi']= $jamshifttoleransi[$val['idshift']];
			$jamshift[$val['karyawanid']][$val['tanggal']]['batasawal']= $jamshiftbatasawal[$val['idshift']];
			$jamshift[$val['karyawanid']][$val['tanggal']]['tipe_shift']     = $jamshifttipe_shift[$val['idshift']];
		}
		
		
		$arrfp=array();
		$countsn=array();
		$str = "select * from ".$dbname.".att_log where substr(scan_date,1,10) between '".$awal."' and '".tglbesok($akhir)."' ".$pintest." order by scan_date asc";
		$res = fetchdata($str);
		foreach($res as $val){
			// $arrfp[substr($val['scan_date'],0,10)][$arrkar[$val['sn']][$val['pin']]][$val['scan_date']] = $val['scan_date'];		
			$arrfp[substr($val['scan_date'],0,10)][$val['pin']][$val['scan_date']] = $val['scan_date'];		


			if($arrkar[$val['sn']][$val['pin']]!=''){				
				$adafp[$arrkar[$val['sn']][$val['pin']]][substr($val['scan_date'],0,10)]+=1;
			}

		}

		$jammasukfp = array();
		$jampulangfp = array();
		$validasimasuk = array();
		$validasipulang = array();
		foreach($arrfp as $tanggalfp => $ar1){
			foreach($ar1 as $karid => $ar2){
				foreach($ar2 as $jamfp => $val){
						if(!isset($validasimasuk[$karid][$tanggalfp])){
							$validasimasuk[$karid][$tanggalfp]=0;
						}
						if(!isset($validasipulang[$karid][$tanggalfp])){
							$validasipulang[$karid][$tanggalfp]=0;
						}

						$shiftmasuk = date_create($tanggalfp.' '.$jamshift[$karid][$tanggalfp]['masuk']);
						date_add($shiftmasuk, date_interval_create_from_date_string("+".$jamshift[$karid][$tanggalfp]['toleransi']." minutes"));
						$shiftmasuk = date_format($shiftmasuk, 'Y-m-d H:i');

						$shiftpulang = date_create($tanggalfp.' '.$jamshift[$karid][$tanggalfp]['pulang']);
						$shiftpulang = date_format($shiftpulang, 'Y-m-d H:i');

						$shiftbatasawal = date_create($tanggalfp.' '.$jamshift[$karid][$tanggalfp]['batasawal']);
						$shiftbatasawal = date_format($shiftbatasawal, 'Y-m-d H:i');

						$shiftbatasawalesoknya = date_create(tglbesok($tanggalfp).' '.$jamshift[$karid][tglbesok($tanggalfp)]['batasawal']);
						$shiftbatasawalesoknya = date_format($shiftbatasawalesoknya, 'Y-m-d H:i');
						
						if($shiftmasuk > $shiftpulang){
							$shiftpulang = date_create(tglbesok($tanggalfp).' '.$jamshift[$karid][$tanggalfp]['pulang']);
							$shiftpulang = date_format($shiftpulang, 'Y-m-d H:i');
						}

						$jamfp = date_create($jamfp);
						$jamfp = date_format($jamfp, 'Y-m-d H:i');
						
						## Jam Masuk
						if($jamfp >= $shiftbatasawal and $jamfp <= $shiftmasuk and isset($jammasukfp[$karid][$tanggalfp])==0){
							$jammasukfp[$karid][$tanggalfp] = $jamfp;
							$validasimasuk[$karid][$tanggalfp] = 1;
							//break;
						}

						## Jam Keluar
						if($jamfp >= $shiftpulang and $jamfp <= $shiftbatasawalesoknya){
							$validasipulang[$karid][$tanggalfp] = 1;
							$jampulangfp[$karid][$tanggalfp] = $jamfp;
							// break;
						}

						foreach ($arrfp[tglbesok($tanggalfp)][$karid] as $jamfpx2 => $valx2){
							## Jam Keluar
							if($jamfpx2 >= $shiftpulang and $jamfpx2 <= $shiftbatasawalesoknya){
								$jampulangfp[$karid][$tanggalfp] = $jamfpx2;
								$validasipulang[$karid][$tanggalfp] = 1;
								// break;
							}
						}
					}
				}
			}		
		## Ambil dari ba-absensi
		$dtmanual=array();
		$str = "select * from ".$dbname.".sdm_ba_absensi where kodeorg='".$unit."' and tanggalabsen between '".$awal."' and '".tglbesok($akhir)."' and posting =1 and statuspersetujuan = 1 ".$where."";
		$res = fetchdata($str);
		foreach($res as $val){
			if(!isset($validasimasuk[$karid][$tanggalfp])){
				$validasimasuk[$karid][$tanggalfp]=0;
			}
			if(!isset($validasipulang[$karid][$tanggalfp])){
				$validasipulang[$karid][$tanggalfp]=0;
			}
			$jamBaMasuk = date_create($val['jam']);
			$jamBaMasuk = date_format($jamBaMasuk, 'Y-m-d H:i');

			$jamBaPulang = date_create($val['jam4']);
			$jamBaPulang = date_format($jamBaPulang, 'Y-m-d H:i');


			## Jam Masuk dan Keluar
			if($val['tipeba'] == '1'){
				$jammasukfp_ba[$val['karyawanid']][$val['tanggaljammasuk']] = $jamBaMasuk;
				$jampulangfp_ba[$val['karyawanid']][$val['tanggaljamkeluar']] = $jamBaPulang;
				$jammasukfp[$val['karyawanid']][$val['tanggaljammasuk']] = $jamBaMasuk;
				$jampulangfp[$val['karyawanid']][$val['tanggaljamkeluar']] = $jamBaPulang;
				$validasimasuk[$val['karyawanid']][$val['tanggaljammasuk']] = 1;
				$validasipulang[$val['karyawanid']][$val['tanggaljamkeluar']] = 1;


			## Jam Masuk
			}elseif($val['tipeba'] == '2'){
				$jammasukfp[$val['karyawanid']][$val['tanggaljammasuk']] = $jamBaMasuk;
				$jammasukfp_ba[$val['karyawanid']][$val['tanggaljammasuk']] = $jamBaMasuk;
				$validasimasuk[$val['karyawanid']][$val['tanggaljammasuk']] = 1;

			
			## Jam Keluar
			}elseif($val['tipeba'] == '3'){
				$jampulangfp[$val['karyawanid']][$val['tanggaljamkeluar']] = $jamBaPulang;
				$jampulangfp_ba[$val['karyawanid']][$val['tanggaljamkeluar']] = $jamBaPulang;
				$validasipulang[$val['karyawanid']][$val['tanggaljamkeluar']] = 1;

			}
		}

		
		## Cek absensi H atau ada Izin
		$absensidt=array();
		$str = "select karyawanid,tanggal,absensi from ".$dbname.".sdm_absensidt where  tanggal between '".$awal."' and '".tglbesok($akhir)."' and absensi != 'H' and absensi != 'MG' and absensi != 'L'  and absensi != 'LN'";
		$res = fetchdata($str);
		foreach($res as $val){
			$absensidt[$val['karyawanid']][$val['tanggal']] = $val['absensi'];
		}

		

		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}
		
		$tab="<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='3'>".$_SESSION['lang']['nik']."</th>
				<th rowspan='3'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan='3'>".$_SESSION['lang']['jabatan']."</th>
				<th colspan='".(count($rangetgl)*5)."'>Tanggal</th>
			</tr>
			<tr class=rowheader style='text-align:center;font-weight:bold'>";
			$col=0;
			foreach($rangetgl as $tgl){
				$col++;
				$tab.="<th colspan=5 id=tanggal_".$col.">".$tgl."</th>";
			}
			$tab.="</tr>";
			$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			foreach($rangetgl as $tgl){
				$tab.="<th>Masuk</th>";
				$tab.="<th>Ist Mas</th>";
				$tab.="<th>Ist Kel</th>";
				$tab.="<th>Pulang</th>";
				$tab.="<th>Abs</th>";
			}
			$tab.="</tr>";
			$tab.="</thead>
			<tbody>";
			$no=0;

			foreach($arrkary as $val){
				$no++;
				$tab.="<tr class='rowcontent' id=row".$no.">
					<td align='center'>".$no."</td>";
				if($tipeprint=='html'){					
					$tab.="<td hidden id=karyawanid_".$no.">".$val['karyawanid']."</td>";
				}	
				$tab.="<td>".$val['nik']."</td>
					<td>".$val['namakaryawan']."</td>
					<td>".getNamaJabatan($val['kodejabatan'])."</td>";
				$col=0;	

				foreach($rangetgl as $tgl){
					$col++;	

					if($jamshift[$val['karyawanid']][$tgl]['tipe_shift'] == '1'){
						if($validasimasuk[$val['karyawanid']][$tgl]=='1' and $validasipulang[$val['karyawanid']][$tgl] =='1'){
							$absen='H';
							$style=($st!=''?$st:"cursor:pointer;color:blue");

							## Jam Out Istirahat
							$jamoutis[$val['karyawanid']][$tgl] = date_create($tgl.' '.$jamshift[$val['karyawanid']][$tgl]['outist']);
							$jamoutis[$val['karyawanid']][$tgl] = date_format($jamoutis[$val['karyawanid']][$tgl], 'Y-m-d H:i');

							## Jam In Istirahat
							$jaminist[$val['karyawanid']][$tgl] = date_create($tgl.' '.$jamshift[$val['karyawanid']][$tgl]['inist']);
							$jaminist[$val['karyawanid']][$tgl] = date_format($jaminist[$val['karyawanid']][$tgl], 'Y-m-d H:i');

							if($absensidt[$val['karyawanid']][$tgl] !=''){
								$style="cursor:pointer;color:red";
								$absen=$absensidt[$val['karyawanid']][$tgl];
							}

						}else{
							$style="cursor:pointer;color:red";
							$absen='';

							if($absensidt[$val['karyawanid']][$tgl] !=''){
								$style="cursor:pointer;color:red";
								$absen=$absensidt[$val['karyawanid']][$tgl];
							}
						}
						
					}else{
						if($validasimasuk[$val['karyawanid']][$tgl]=='1' or $validasipulang[$val['karyawanid']][$tgl]=='1'){
							$absen='H';
							$style=($st!=''?$st:"cursor:pointer;color:blue");

							## Jam Out Istirahat
							$jamoutis[$val['karyawanid']][$tgl] = date_create($tgl.' '.$jamshift[$val['karyawanid']][$tgl]['outist']);
							$jamoutis[$val['karyawanid']][$tgl] = date_format($jamoutis[$val['karyawanid']][$tgl], 'Y-m-d H:i');

							## Jam In Istirahat
							$jaminist[$val['karyawanid']][$tgl] = date_create($tgl.' '.$jamshift[$val['karyawanid']][$tgl]['inist']);
							$jaminist[$val['karyawanid']][$tgl] = date_format($jaminist[$val['karyawanid']][$tgl], 'Y-m-d H:i');

							if($absensidt[$val['karyawanid']][$tgl] !=''){
								$style="cursor:pointer;color:red";
								$absen=$absensidt[$val['karyawanid']][$tgl];
							}

						}else{
							$style="cursor:pointer;color:red";
							$absen='';

							if($absensidt[$val['karyawanid']][$tgl] !=''){
								$style="cursor:pointer;color:red";
								$absen=$absensidt[$val['karyawanid']][$tgl];
							}
						}
					}

					// if($jammasukfp_ba[$val['karyawanid']][$tgl] != '' or $jampulangfp_ba[$val['karyawanid']][$tgl]!='' ){
					// 	$absen='H';
					// 	$style=($st!=''?$st:"cursor:pointer;color:green");

					// 	## Jam Out Istirahat
					// 	$jamoutis[$val['karyawanid']][$tgl] = date_create($tgl.' '.$jamshift[$val['karyawanid']][$tgl]['outist']);
					// 	$jamoutis[$val['karyawanid']][$tgl] = date_format($jamoutis[$val['karyawanid']][$tgl], 'Y-m-d H:i');

					// 	## Jam In Istirahat
					// 	$jaminist[$val['karyawanid']][$tgl] = date_create($tgl.' '.$jamshift[$val['karyawanid']][$tgl]['inist']);
					// 	$jaminist[$val['karyawanid']][$tgl] = date_format($jaminist[$val['karyawanid']][$tgl], 'Y-m-d H:i');

					// 	$jammasukfp[$val['karyawanid']][$tgl] = $jammasukfp_ba[$val['karyawanid']][$tgl];
					// 	$jampulangfp[$val['karyawanid']][$tgl] = $jampulangfp_ba[$val['karyawanid']][$tgl];
					// }

					#kalau excel tidak ditampilkan
					if($tipeprint=='html'){						
						$tab.="<td id=idshift_".$no."_".$col." style='display:none'>".$jamshift[$val['karyawanid']][$tgl]['idshift']."</td>";
						$tab.="<td align='center' id=masukx_".$no."_".$col." style='display:none'>".$jammasukfp[$val['karyawanid']][$tgl]."</td>";
						$tab.="<td align='center' id=istoutx_".$no."_".$col." style='display:none'>".$jamoutis[$val['karyawanid']][$tgl]."</td>";
						$tab.="<td align='center' id=istinx_".$no."_".$col." style='display:none'>".$jaminist[$val['karyawanid']][$tgl]."</td>";
						$tab.="<td align='center' id=pulangx_".$no."_".$col." style='display:none'>".$jampulangfp[$val['karyawanid']][$tgl]."</td>";
					}
					
					if($absen=='' and $adafp[$val['karyawanid']][$tgl]){
						$style.=";background-color:#f4f5d7; title='Ada Finger namun jam tidak sesuai dengan shift.'";
					}

					$tab.="<td align='center' id=masuk_".$no."_".$col." style='".$style."' ".$onclick.">".substr($jammasukfp[$val['karyawanid']][$tgl],11,5)."</td>";
					
					$tab.="<td align='center' id=istout_".$no."_".$col." style='".$style."' ".$onclick.">".substr($jamoutis[$val['karyawanid']][$tgl],11,5)."</td>";
					
					$tab.="<td align='center' id=istin_".$no."_".$col." style='".$style."' ".$onclick.">".substr($jaminist[$val['karyawanid']][$tgl],11,5)."</td>";
					
					$tab.="<td align='center' id=pulang_".$no."_".$col." style='".$style."' ".$onclick.">".substr($jampulangfp[$val['karyawanid']][$tgl],11,5)."</td>";
					$tab.="<td align='center' id=absen_".$no."_".$col." style='".$style."' ".$onclick.">".$absen."</td>";

					if($tipeprint=='html'){
						$tab.="<td hidden id=penjelasan_".$no."_".$col.">".$dtexist[$val['karyawanid']][$tgl]['penjelasan']."</td>";
					}
				}
			}

			$tab.="
				<tr>
					<td colspan=".((count($rangetgl)*5)+4).">
						<button onclick=\"simpanshift('".$no."','".count($rangetgl)."')\" class='mybutton'>".$_SESSION['lang']['save']."</button>
					</td>
				</tr>
			</tbody>
		</table><br>";
		
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_FingerPrint_".$unit."_".$periode;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$tab)){
					echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				}else{
					echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				}
				fclose($handle);
			}
		}
	break;
}
?>