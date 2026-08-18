<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method               = checkPostGet('method','');
$param                =$_POST;
$kodeunit             = checkPostGet('kodeunit','');
$jenispersetujuan     = checkPostGet('jenispersetujuan','');
$level                = checkPostGet('level','');
$nilaidari      	  = str_replace(',','',checkPostGet('nilaidari','0'));
$nilaisampai      	  = str_replace(',','',checkPostGet('nilaisampai','0'));
$karyawanid           = checkPostGet('karyawanid','');
$departemen           = checkPostGet('departemen','');
$tipekaryawan         = checkPostGet('tipekaryawan','');
$golongan             = checkPostGet('golongan','');
$tipe                 = checkPostGet('tipe','');
$jabatan              = checkPostGet('jabatan','');

$kodeunitold          = checkPostGet('kodeorgold','');
$jenispersetujuanold  = checkPostGet('jenispersetujuanold','');
$levelold             = checkPostGet('levelold','');
$karyawanidold        = checkPostGet('karyawanidold','');
$departemenold        = checkPostGet('departemenold','');
$tipekaryawanold      = checkPostGet('tipekaryawanold','');
$golonganold          = checkPostGet('golonganold','');
$tipeold              = checkPostGet('tipeold','');
$jabatanold           = checkPostGet('jabatanold','');

$karyawanidrep1       = checkPostGet('karyawanidrep1','');
$karyawanidrep2       = checkPostGet('karyawanidrep2','');
$kodeorgcopy1         = checkPostGet('kodeorgcopy1','');
$kodeorgcopy2         = checkPostGet('kodeorgcopy2','');
$jenispersetujuancopy = checkPostGet('jenispersetujuancopy','');
$jenispersetujuancopy2= checkPostGet('jenispersetujuancopy2','');
$departemencopy       = checkPostGet('departemencopy','');
$departemencopy2      = checkPostGet('departemencopy2','');
$golongancopy         = checkPostGet('golongancopy','');
$golongancopy2        = checkPostGet('golongancopy2','');

$karyawaniduser       = checkPostGet('karyawaniduser','');
$karyawaniduserold    = checkPostGet('karyawaniduserold','');
$timenow              = date('Y-m-d H:i:s');


$wherex               = checkPostGet('wherex','');
$tipeprint            = checkPostGet('tipeprint','');

switch($method){
	case 'loaddata':
		$tab="";
		
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 style='width:100%' border=0>
			<thead>
			<tr class=rowheader style='font-weight:bold'>
				<th rowspan=2 style='text-align:center;width:70px'>".$_SESSION['lang']['kodeorg']."</th>
				<th rowspan=2 style='text-align:center;display:none'>".$_SESSION['lang']['namaorganisasi']."</th>
				<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['jenispersetujuan']."</th>
				<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['level']."</th>
				<th colspan=2 style='text-align:center'>".$_SESSION['lang']['range']."</th>
				<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['departemen']."</th>
				<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan=2 style='text-align:center;width:70px'>".$_SESSION['lang']['tipekaryawan']."</th>
				<th rowspan=2 style='text-align:center;width:50px'>".$_SESSION['lang']['kodegolongan']."<br>".$_SESSION['lang']['user']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['namakaryawan']."<br>".$_SESSION['lang']['user']."</th>
				<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['tipe']."</th>
				<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['createby']."</th>
				<th rowspan=2 style='text-align:center;width:70px'>".$_SESSION['lang']['createtime']."</th>
				<th rowspan=2 style='text-align:center'>".$_SESSION['lang']['updatedby']."</th>
				<th rowspan=2 style='text-align:center;width:70px'>".$_SESSION['lang']['updatedtime']."</th>";
			if($tipeprint==''){
				$tab.="<th rowspan=2 style='text-align:center' colspan=3>".$_SESSION['lang']['action']."</th>";
			}
			$tab.="</tr>
			<tr>
				<th style='text-align:center'>".$_SESSION['lang']['dari']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['sampai']."</th>
			</tr>";
			$tab.="</thead>
			 <tbody>";
		
		$footd="";
		$where = "1=1 ";
		if($kodeunit!=""){
			$where .= " and kodeunit='".$kodeunit."'";
		}
		if($jenispersetujuan!=""){
			$where .= " and jenispersetujuan='".$jenispersetujuan."'";
		}
		if($level!=""){
			$where .= " and level='".$level."'";
		}
		if($karyawanid!=""){
			$where .= " and karyawanid='".$karyawanid."'";
		}
		if($departemen!=""){
			$where .= " and departemen='".$departemen."'";
		}
		if($tipekaryawan!=""){
			$where .= " and tipekaryawan='".$tipekaryawan."'";
		}
		if($golongan!=""){
			$where .= " and golongan='".$golongan."'";
		}
		if($jabatan!=""){
			$where .= " and jabatan='".$jabatan."'";
		}
		if($karyawaniduser!=""){
			$where .= " and karyawaniduser='".$karyawaniduser."'";
		}
		
		
		$optNmJns = makeOption($dbname,'setup_jenisapproval','jenis,nama');
		
		$limit= 20;
		$page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 19;
		$lmt	   = " limit ";
		$comma	   = " , ";
		
        $sql="select count(*) as jmlhrow from ".$dbname.".setup_approval where ".$where." order by kodeunit asc, jenispersetujuan asc, level asc";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['jmlhrow'];
		
		if($tipeprint!=''){
			$lmt="";
			$comma="";
			$offset="";
			$limit="";
		}
		
		$str="select * from ".$dbname.".setup_approval where ".$where." order by kodeunit asc, jenispersetujuan asc, level asc ".$lmt." ".$offset." ".$comma." ".$limit."";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr></tbody>";
		}else{
			foreach($res as $key=>$val){
				$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeunit']."'");
				$optNmKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
				@$optNmUsr = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawaniduser']."'");
				$optbagian = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$val['departemen']."'");
				$optjabatan = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$val['jabatan']."'");
				$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$val['tipekaryawan']."'");
				$optcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
				@$createby = $_SESSION['standard']['userid'];

				if ($val['createby'] == '0000000000') {
					$buatoleh = '';
				} else {
					$buatoleh = getNamaKaryawan($val['createby']);
				}
				if ($val['updateby'] == '0000000000') {
					$updtoleh = '';
				} else {
					$updtoleh = getNamaKaryawan($val['updateby']);
				}
				if($val['createtime']=='0000-00-00 00:00:00'){
					$val['createtime']="";
				}
				if($val['updatedtime']=='0000-00-00 00:00:00'){
					$val['updatedtime']="";
				}
				
				
				// echo "<pre>";
				// print_r($optcreate);
				// echo "</pre>";

				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$val['kodeunit']."</td>
					<td style='text-align:left;display:none'>".@$optNmOrg[$val['kodeunit']]."</td>
					<td style='text-align:left'>".@$val['jenispersetujuan']." - ".@$optNmJns[$val['jenispersetujuan']]."</td>
					<td style='text-align:center'>Level ".$val['level']."</td>
					<td style='text-align:right'>".hidezerodecimal($val['nilaidari'],2)."</td>
					<td style='text-align:right'>".hidezerodecimal($val['nilaisampai'],2)."</td>
					<td style='text-align:left'>".@$optNmKry[$val['karyawanid']]."</td>
					<td style='text-align:left'>".@$optbagian[$val['departemen']]."</td>
					<td style='text-align:left'>".@$optjabatan[$val['jabatan']]."</td>
					<td style='text-align:left'>".@$opttipe[$val['tipekaryawan']]."</td>
					<td style='text-align:center'>".@$val['golongan']."</td>
					<td style='text-align:center'>".@$optNmUsr[$val['karyawaniduser']]."</td>
					<td style='text-align:center'>".($val['tipe']=='1'?'&#10003;':'')."</td>

					<!--<td style='text-align:center'>".@$optcreate[@$createby]."</td>-->
					<!--<td style='text-align:center'>".@$timenow."</td>-->
					<!--<td style='text-align:center'>".@$optcreate[@$createby]."</td>-->
					<!--<td style='text-align:center'>".@$timenow."</td>-->

					<td style='text-align:center'>".$buatoleh."</td>
					<td style='text-align:center'>".$val['createtime']."</td>
					<td style='text-align:center'>".$updtoleh."</td>
					<td style='text-align:center'>".$val['updatedtime']."</td>";

				if($tipeprint==''){
					$tab.="<td style='text-align:center;width:25px;' >
						<img src=images/application/application_edit.png class=zImgBtn title=Edit onclick=\"fillField('".$val['kodeunit']."','".$val['jenispersetujuan']."','".$val['level']."','".$val['karyawanid']."','".$val['departemen']."','".($val['jabatan']=='0'?'':$val['jabatan'])."','".$val['tipekaryawan']."','".@$val['golongan']."','".$val['tipe']."','".$val['karyawaniduser']."','".$optNmUsr[$val['karyawaniduser']]."','".hidezerodecimal($val['nilaidari'],2)."','".hidezerodecimal($val['nilaisampai'],2)."')\";>
					</td>
					<td style='text-align:center;width:25px;'>
						<img src=images/skyblue/delete.png class=zImgBtn title='Delete' onclick=\"deletefield('".$val['kodeunit']."','".$val['jenispersetujuan']."','".$val['level']."','".$val['karyawanid']."','".$val['departemen']."','".$val['tipekaryawan']."','".@$val['golongan']."','".$val['tipe']."','".($val['jabatan']=='0'?'':$val['jabatan'])."');\"> 
					</td>
					
					<td style='text-align:center;width:25px;'>
						<img src=images/plus.png class=zImgBtn title='Notifikasi' onclick=\"notiffield('".$val['kodeunit']."','".$val['jenispersetujuan']."','".$val['level']."','".$val['karyawanid']."','".$val['departemen']."','".$val['tipekaryawan']."','".@$val['golongan']."','".$val['tipe']."','".($val['jabatan']=='0'?'':$val['jabatan'])."','".$val['karyawaniduser']."');\"> 
					</td>";
				}
					
				$tab.="</tr>";
			}
			$tab.="</tbody>";
			
			if($tipeprint==''){
				$tab.="<tfoot>".createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage')."</tfoot>";
			}
		}
		
		$tab.="</table>";
		
		if($tipeprint==''){
			echo $tab;			
		}else{
			$nop = "LIST APPROVAL.xls";
			$xls = new HtmlExcel();
			$xls->addSheet("LIST APPROVAL", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
	break;
	case 'simpannotif':
		$optNmKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
		$str = "select * from ".$dbname.".setup_approval_notif where kodeunit='".$kodeunit."' and jenispersetujuan='".$jenispersetujuan."' and level='".$level."' and karyawanid='".$karyawanid."' and departemen='".$departemen."' and tipekaryawan='".$tipekaryawan."' and golongan='".$golongan."' and karyawaniduser='".$karyawaniduser."'";
		$res=fetchData($str);
		if(!empty($res)){
			exit("Warning : Data notifikasi untuk karyawan ".$optNmKry[$karyawanid]." sudah pernah terdaftar disistem");
		}else{
			$str = "insert into ".$dbname.".setup_approval_notif (kodeunit, jenispersetujuan, level, karyawanid, departemen, tipekaryawan, golongan, tipe, jabatan, karyawaniduser, createby, createtime, notifonly) 
			values (
				'".$kodeunit."',
				'".$jenispersetujuan."',
				'".$level."',
				'".$karyawanid."',
				'".$departemen."',
				'".$tipekaryawan."',
				'".$golongan."',
				'".$tipe."',
				'".$jabatan."',
				'".$karyawaniduser."',
				'".$_SESSION['standard']['userid']."',
				'".$timenow."',
				'1'
			)";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}	
	break;
	case 'notiffield':
		$str = "select * from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan not in ('4','5') order by lokasitugas, namakaryawan";
		$res = fetchdata($str);
		$optkarnotif="";
		$optkarnotif="<option value=''></option>";
		$optkarnotif.="<option value='".$param['karyawanid']."'>".getNamaKaryawan($param['karyawanid'])."</option>";
		foreach($res as $bar){
			$d=$bar['lokasitugas'];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optkarnotif.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optkarnotif.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']."</option>";
			$n=$d;
			if($d!=$n){			
				$optkarnotif.="</optgroup>";
			}
		}
		
		
		echo"<table style='display: inline-block;vertical-align:top'>	
			<tr>
				<td>".$_SESSION['lang']['namakaryawan']."</td>
				<td>:</td>
				<td>
					<select id=karynotif >".$optkarnotif."</select>
				</td>
				<td>&nbsp;</td>
				<td>
					<button class=mybutton onclick=simpannotif('event')>".$_SESSION['lang']['save']."</button>
				</td>
			</tr>
		</table><hr>
		<input hidden id=kodeunitnotif value=".$kodeunit.">
		<input hidden id=jenispersetujuannotif value=".$jenispersetujuan.">
		<input hidden id=levelnotif value=".$level.">
		<input hidden id=departemennotif value=".$departemen.">
		<input hidden id=tipekaryawannotif value=".$tipekaryawan.">
		<input hidden id=golongannotif value=".$golongan.">
		<input hidden id=karyawanidusernotif value=".$karyawaniduser.">
		
		<table class=sortable cellspacing=1 cellpadding=5 border=0 style='width:100%;'>
		<thead>
		<tr class=rowheader style='font-weight:bold'>
			<td style='text-align:center;width:30px'>".$_SESSION['lang']['nomor']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
		 </thead>
		 <tbody id=bodynotif>
		";
		echo"</tbody></table>";
	break;
	case 'loaddatanotif':
		$str = "select * from ".$dbname.".setup_approval_notif where kodeunit='".$kodeunit."' and jenispersetujuan='".$jenispersetujuan."' and level='".$level."' and departemen='".$departemen."' and tipekaryawan='".$tipekaryawan."' and golongan='".$golongan."' and karyawaniduser='".$karyawaniduser."'";
		$res = fetchData($str);
		foreach($res as $bar){
			$no++;
			echo"<tr class=rowcontent>";
			echo"<td align=center>".$no."</td>";
			echo"<td>".getNamaKaryawan($bar['karyawanid'])."</td>";
			echo"<td style='text-align:center;width:25px;'>
						<img src=images/skyblue/delete.png class=zImgBtn caption='Delete' onclick=\"deletenotif('".$bar['id']."');\"> 
					</td>";
			echo"</tr>";
		}
	break;
	case 'deletenotif':
		$str="delete from ".$dbname.".setup_approval_notif where id=".$param['id']." ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case 'tampilkanformdelete':
		$optCrOrg = $optCrkar = $optCrjnspstj = $optCrapp ="<option value=''>".$_SESSION['lang']['all']."</option>";
		$optdepcr = $optjabcr = $opttipecr = $optjabcr = $optgolcr ="<option value=''>".$_SESSION['lang']['all']."</option>";
		$optjab=$opttipe=$optdep=$optkar=$optgol=$optOrg=$optjnspstj=$optCrkarlama=$optCrkarbaru="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

		##KODE ORGANISASI
		$str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by kodeorganisasi");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
			$optCrOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
		}

		##DATA KARYAWAN
		$str=$owlPDO->query("select karyawanid,namakaryawan, lokasitugas from ".$dbname.".datakaryawan 
			  where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan not in ('4','5') order by namakaryawan");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->lokasitugas."</option>";
			$optCrkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->lokasitugas."</option>";
			$optCrkarbaru.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->lokasitugas."</option>";
		}

		##DATA KARYAWAN replace
		$str=$owlPDO->query("select karyawanid,namakaryawan, lokasitugas from ".$dbname.".datakaryawan 
			  where tipekaryawan in ('0','1','6','7','8','9','10','11','12','13') order by namakaryawan");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			//$optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."- ".$bar->lokasitugas."</option>";
			$optCrkarlama.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->lokasitugas."</option>";
		}

		##JENIS PERSETUJUAN
		$str=$owlPDO->query("select distinct jenis from ".$dbname.".setup_jenisapproval where status='1' order by jenis asc");
		$optNmJns = makeOption($dbname,'setup_jenisapproval','jenis,nama');
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$optjnspstj.="<option value='".$bar->jenis."'>".$bar->jenis." - ".$optNmJns[$bar->jenis]."</option>";
			$optCrjnspstj.="<option value='".$bar->jenis."'>".$bar->jenis." - ".$optNmJns[$bar->jenis]."</option>";
		}

		##LEVEL APPROVAL
		$optapp="";
		for($i=1;$i<=10;$i++){
			$optapp.="<option value='".$i."'>Level ".$i."</option>";
			$optCrapp.="<option value='".$i."'>Level ".$i."</option>";
		}

		##DEPARTEMEN
		$str=$owlPDO->query("select kode,nama from ".$dbname.".sdm_5departemen order by nama asc");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$optdep.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->nama."</option>";
			$optdepcr.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->nama."</option>";
		}

		##TIPE KARYAWAN
		$str=$owlPDO->query("select id,tipe from ".$dbname.".sdm_5tipekaryawan order by id asc");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$opttipe.="<option value='".$bar->id."'>".$bar->tipe."</option>";
			$opttipecr.="<option value='".$bar->id."'>".$bar->tipe."</option>";
		}

		##GOLONGAN KARYAWAN
		$arrgol = array('3','4','5','6','7');
		$where ='';
		foreach ($arrgol as $key => $value) {
			if($key == '0'){
				$where.=" namagolongan LIKE '".$value."%'";
			}else{
				$where.=" OR namagolongan LIKE '%".$value."%'";
			}
		}
		$str=$owlPDO->query("select distinct left(namagolongan,1) as namagolongan from ".$dbname.".sdm_5golongan where ".$where."  and aktif='1' and kodegolongan != '32' order by kodegolongan desc");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
				$optgol.="<option value='".$bar->namagolongan."'>".$bar->namagolongan." </option>";
				$optgolcr.="<option value='".$bar->namagolongan."'>".$bar->namagolongan."</option>";
		}

		##JABATAN
		$str=$owlPDO->query("select kodejabatan,namajabatan from ".$dbname.".sdm_5jabatan order by kodejabatan asc");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$optjab.="<option value='".$bar->kodejabatan."'>".$bar->kodejabatan." - ".$bar->namajabatan."</option>";
			$optjabcr.="<option value='".$bar->kodejabatan."'>".$bar->kodejabatan." - ".$bar->namajabatan."</option>";
		}

		echo"<table style='display: inline-block;vertical-align:top'>	
			<tr>
				<td style='padding-left:10px;'>".$_SESSION['lang']['kodeorg']."</td>
				<td>:</td>
				<td>
					<select style=width:150px id=cr2kodeorg>".$optCrOrg."</select>
					<img id='cr2kodeorg' onclick=z.elSearch('cr2kodeorg',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>
				
				<td style='padding-left:10px;'>".$_SESSION['lang']['jenispersetujuan']."</td> 
				<td>:</td>
				<td>
					<select style=width:150px id=cr2jenispersetujuan >".$optCrjnspstj."</select>
					<img id='cr2jenispersetujuan' onclick=z.elSearch('cr2jenispersetujuan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>
				
			</tr>
			<tr>
				<td style='padding-left:10px;'>".$_SESSION['lang']['level']."</td>
				<td>:</td>
				<td>
					<select id=cr2level style=width:150px >".$optCrapp."</select>
					<img id='cr2level' onclick=z.elSearch('cr2level',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>
				
				<td style='padding-left:10px;'>".$_SESSION['lang']['namakaryawan']."</td>
				<td>:</td>
				<td>
					<select style=width:150px id=cr2karyawanid >".$optCrkar."</select>
					<img id='cr2karyawanid' onclick=z.elSearch('cr2karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td style='padding-left:10px;'>".$_SESSION['lang']['departemen']."</td>
				<td>:</td>
				<td>
					<select id=cr2departemen style=width:150px >".$optdepcr."</select>
					<img id='cr2departemen' onclick=z.elSearch('cr2departemen',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>
				
				<td style='padding-left:10px;'>".$_SESSION['lang']['tipekaryawan']."</td>
				<td>:</td>
				<td>
					<select id=cr2tipekaryawan style=width:150px >".$opttipecr."</select>
					<img id='cr2tipekaryawan' onclick=z.elSearch('cr2tipekaryawan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td style='padding-left:10px;'>".$_SESSION['lang']['jabatan']."</td>
				<td>:</td>
				<td>
					<select id=cr2jabatan style=width:150px >".$optjabcr."</select>
					<img id='cr2jabatan' onclick=z.elSearch('cr2jabatan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>
				
				<td style='padding-left:10px;'>".$_SESSION['lang']['kodegolongan']."</td>
				<td>:</td>
				<td>
					<select id=cr2golongan style=width:150px >".$optgolcr."</select>
					<img id='cr2golongan' onclick=z.elSearch('cr2golongan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=formdelete('event')>".$_SESSION['lang']['preview']."</button>
				</td>
			</tr>
		</table><hr>
		
		<table class=sortable cellspacing=1 cellpadding=3 border=0 style='margin-left:5px;min-width:635px;'>
		<thead>
		<tr class=rowheader style='font-weight:bold'>
			<td style='text-align:center;width:70px'>".$_SESSION['lang']['kodeorg']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['namaorganisasi']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['jenispersetujuan']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['level']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['departemen']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['jabatan']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['tipekaryawan']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['kodegolongan']."  ".$_SESSION['lang']['user']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['namakaryawan']." ".$_SESSION['lang']['user']."</td>
			<td style='text-align:center'>".$_SESSION['lang']['tipe']." Global /<br>Non-Global</td>
		 </thead>
		 <tbody id=contdelall></tbody>
		";
				
	break;
	case 'formdelete':
		$tab="";
		$where = "1=1 ";
		if($kodeunit!=""){
			$where .= " and kodeunit='".$kodeunit."'";
		}
		if($jenispersetujuan!=""){
			$where .= " and jenispersetujuan='".$jenispersetujuan."'";
		}
		if($level!=""){
			$where .= " and level='".$level."'";
		}
		if($karyawanid!=""){
			$where .= " and karyawanid='".$karyawanid."'";
		}
		if($departemen!=""){
			$where .= " and departemen='".$departemen."'";
		}
		if($tipekaryawan!=""){
			$where .= " and tipekaryawan='".$tipekaryawan."'";
		}
		if($golongan!=""){
			$where .= " and golongan='".$golongan."'";
		}
		if($jabatan!=""){
			$where .= " and jabatan='".$jabatan."'";
		}
		$optNmJns = makeOption($dbname,'setup_jenisapproval','jenis,nama');
		
		
		$colspan   = 13;
		
		$str="select * from ".$dbname.".setup_approval where ".$where." order by kodeunit asc, jenispersetujuan asc, level asc ";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeunit']."'");
				$optNmKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
				$optNmUsr = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawaniduser']."'");
				$optbagian = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$val['departemen']."'");
				$optjabatan = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$val['jabatan']."'");
				$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$val['tipekaryawan']."'");
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$val['kodeunit']."</td>
					<td style='text-align:left'>".@$optNmOrg[$val['kodeunit']]."</td>
					<td style='text-align:left'>".$val['jenispersetujuan']." - ".$optNmJns[$val['jenispersetujuan']]."</td>
					<td style='text-align:center'>Level ".$val['level']."</td>
					<td style='text-align:right'>".hidezerodecimal($val['nilaidari'],2)."</td>
					<td style='text-align:right'>".hidezerodecimal($val['nilaisampai'],2)."</td>
					<td style='text-align:left'>".@$optNmKry[$val['karyawanid']]."</td>
					<td style='text-align:left'>".@$optbagian[$val['departemen']]."</td>
					<td style='text-align:left'>".@$optjabatan[$val['jabatan']]."</td>
					<td style='text-align:left'>".@$opttipe[$val['tipekaryawan']]."</td>
					<td style='text-align:center'>".$val['golongan']."</td>
					<td style='text-align:center'>".@$optNmUsr[$val['karyawaniduser']]."</td>
					<td style='text-align:center'>".($val['tipe']=='1'?'&#10003;':'')."</td>
				</tr>";
			}
			$tab.="<tr>
						<td colspan=11 align=center>
							<button class=mybutton onclick=\"deleteall('".str_replace("'", "##", $where)."');\">".$_SESSION['lang']['delete']."</button>
						</td>
					</tr></table>";
		}
		
		
		echo $tab ;
	break;
	case 'deleteall':
		$wherex=str_replace('##', "'", $wherex);
		$str = "delete from ".$dbname.".setup_approval where ".$wherex." ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
		
		$str = "delete from ".$dbname.".setup_approval_notif where ".$wherex." ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'getkary':
		$where='';
		if($kodeunit!=''){
			$where.=" and kodeunit='".$kodeunit."'";
		}
		if($jenispersetujuan!=''){
			$where.=" and jenispersetujuan='".$jenispersetujuan."'";
		}
		if($departemen!=''){
			$where.=" and bagian='".$departemen."'";
		}
		
	
		$whr='';
		if($departemen!='' and $jenispersetujuan!='PR'){
			$whr.=" and bagian='".$departemen."'";
		}
		if($jabatan!=''){
			$whr.=" and kodejabatan='".$jabatan."'";
		}

		if($tipekaryawan!=''){
			if($tipekaryawan==0){
				$whr.=" and tipekaryawan=9";
			} else if($tipekaryawan==9){
				$whr.=" and tipekaryawan=10";
			} else if($tipekaryawan==10){
				$whr.=" and tipekaryawan in (7,8)";
			}else{
				$whr.=" and tipekaryawan not in ('4','5')";
			}
		}else{
			$whr.=" and tipekaryawan not in ('4','5')";
		}

		$str = "select karyawanid,namakaryawan, lokasitugas from  " . $dbname . ".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and karyawanid not in (select karyawanid from  " . $dbname . ".setup_approval where 1=1 ".$where.") ".$whr." order by namakaryawan asc ";
		#exit('warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optkar.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			$optkar.="<option value='" . $bar['karyawanid'] . "'>" . $bar['namakaryawan'] . " - ".$bar['lokasitugas']."</option>";
		}
		
	echo $optkar;
	break;
	
	case 'delete':
		$str = "delete from ".$dbname.".setup_approval where kodeunit='".$kodeunit."' and jenispersetujuan='".$jenispersetujuan."' and level='".$level."' and karyawanid='".$karyawanid."' and departemen='".$departemen."' and tipekaryawan='".$tipekaryawan."' and golongan='".$golongan."'
		and tipe='".$tipe."'
		and jabatan='".$jabatan."'
		";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
		
		$str = "delete from ".$dbname.".setup_approval_notif where kodeunit='".$kodeunit."' and jenispersetujuan='".$jenispersetujuan."' and level='".$level."' and karyawanid='".$karyawanid."' and departemen='".$departemen."' and tipekaryawan='".$tipekaryawan."' and golongan='".$golongan."'
		and tipe='".$tipe."'
		and jabatan='".$jabatan."'
		";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'update':
		try {
		$owlPDO->beginTransaction();
		
		$sDet="update ".$dbname.".setup_approval_notif set kodeunit='".$kodeunit."',jenispersetujuan='".$jenispersetujuan."',level='".$level."',karyawanid='".$karyawanid."',departemen='".$departemen."',tipekaryawan='".$tipekaryawan."',golongan='".$golongan."',tipe='".$tipe."',jabatan='".$jabatan."',karyawaniduser='".$karyawaniduser."', updateby='".$_SESSION['standard']['userid']."', updatedtime='".$timenow."'  where kodeunit='".$kodeunitold."' and jenispersetujuan='".$jenispersetujuanold."' and level='".$levelold."' and karyawanid='".$karyawanidold."' and departemen='".$departemenold."' and golongan='".$golonganold."'
		and jabatan='".$jabatanold."'
		and tipekaryawan='".$tipekaryawanold."'
		and karyawaniduser='".$karyawaniduserold."'
		"; 
		$owlPDO->exec($sDet);
		
		
		$sDet="update ".$dbname.".setup_approval set kodeunit='".$kodeunit."',jenispersetujuan='".$jenispersetujuan."',level='".$level."',nilaidari='".$nilaidari."',nilaisampai='".$nilaisampai."',karyawanid='".$karyawanid."',departemen='".$departemen."',tipekaryawan='".$tipekaryawan."',golongan='".$golongan."',tipe='".$tipe."',jabatan='".$jabatan."',karyawaniduser='".$karyawaniduser."', updateby='".$_SESSION['standard']['userid']."', updatedtime='".$timenow."'  where kodeunit='".$kodeunitold."' and jenispersetujuan='".$jenispersetujuanold."' and level='".$levelold."' and karyawanid='".$karyawanidold."' and departemen='".$departemenold."' and golongan='".$golonganold."'
		
		and jabatan='".$jabatanold."'
		and tipekaryawan='".$tipekaryawanold."'
		and karyawaniduser='".$karyawaniduserold."'
		and tipe='".$tipeold."'
		  
		"; 
		// exit("error".$sDet);

		$owlPDO->exec($sDet);
	$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning\n" . addslashes($e->getMessage());
		}
	break;
	case 'simpan':
	try {
		$owlPDO->beginTransaction();
			
		$optNmKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
		$str="select * from ".$dbname.".setup_approval where kodeunit='".$kodeunit."' and jenispersetujuan='".$jenispersetujuan."' and level='".$level."' and karyawanid='".$karyawanid."' and departemen='".$departemen."' and tipekaryawan='".$tipekaryawan."' and golongan='".$golongan."' and karyawaniduser='".$karyawaniduser."'";
		$res=fetchData($str);
		if(!empty($res)){
			throw new PDOException("Data persetujuan untuk karyawan ".$optNmKry[$karyawanid]." sudah pernah terdaftar disistem");
		}else{
			
			$str="insert into ".$dbname.".setup_approval (kodeunit,jenispersetujuan,level,nilaidari,nilaisampai,karyawanid,departemen,tipekaryawan,golongan,tipe,jabatan,karyawaniduser,createby,createtime) 
			values (
			'".$kodeunit."',
			'".$jenispersetujuan."',
			'".$level."',
			'".$nilaidari."',
			'".$nilaisampai."',
			'".$karyawanid."',
			'".$departemen."',
			'".$tipekaryawan."',
			'".$golongan."',
			'".$tipe."',
			'".$jabatan."',
			'".$karyawaniduser."',
			'".$_SESSION['standard']['userid']."',
			'".$timenow."'		
			)";
			// '".$level."',
			$owlPDO->exec($str);
						
			$optNmKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
			$str = "select * from ".$dbname.".setup_approval_notif where kodeunit='".$kodeunit."' and jenispersetujuan='".$jenispersetujuan."' and level='".$level."' and karyawanid='".$karyawanid."' and departemen='".$departemen."' and tipekaryawan='".$tipekaryawan."' and golongan='".$golongan."' and karyawaniduser='".$karyawaniduser."'";
			$res=fetchData($str);
			if(!empty($res)){
				throw new PDOException("Data notifikasi untuk karyawan ".$optNmKry[$karyawanid]." sudah pernah terdaftar disistem");
			}else{
				$str = "insert into ".$dbname.".setup_approval_notif (kodeunit, jenispersetujuan, level, karyawanid, departemen, tipekaryawan, golongan, tipe, jabatan, karyawaniduser, createby, createtime) 
				values (
					'".$kodeunit."',
					'".$jenispersetujuan."',
					'".$level."',
					'".$karyawanid."',
					'".$departemen."',
					'".$tipekaryawan."',
					'".$golongan."',
					'".$tipe."',
					'".$jabatan."',
					'".$karyawaniduser."',
					'".$_SESSION['standard']['userid']."',
					'".$timenow."'		
				)";
				$owlPDO->exec($str);
				
			}	
		}
	
		$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning\n" . addslashes($e->getMessage());
		}
		
	break;

	case 'simpanreplace':
		try {
			$owlPDO->beginTransaction();
			
			$arrsapp=array();
			$str="select * from ".$dbname.".setup_approval where karyawanid='".$karyawanidrep1."'";
			$res=fetchdata($str);
			$no=0;
			foreach($res as $val){
				$no++;
				$arrsapp[$no]['kodeunit']=$val['kodeunit'];
				$arrsapp[$no]['jenispersetujuan']=$val['jenispersetujuan'];
				$arrsapp[$no]['level']=$val['level'];
				$arrsapp[$no]['nilaidari']=$val['nilaidari'];
				$arrsapp[$no]['nilaisampai']=$val['nilaisampai'];
				$arrsapp[$no]['departemen']=$val['departemen'];
				$arrsapp[$no]['tipekaryawan']=$val['tipekaryawan'];
				$arrsapp[$no]['jabatan']=$val['jabatan'];
				$arrsapp[$no]['tipe']=$val['tipe'];
				$arrsapp[$no]['golongan']=$val['golongan'];
				$arrsapp[$no]['karyawaniduser']=$val['karyawaniduser'];
				
				$str="delete from ".$dbname.".setup_approval where karyawanid='".$karyawanidrep2."' and kodeunit='".$val['kodeunit']."' and jenispersetujuan='".$val['jenispersetujuan']."' and level='".$val['level']."' and departemen='".$val['departemen']."' and tipekaryawan='".$val['tipekaryawan']."' and jabatan='".$val['jabatan']."' and tipe='".$val['tipe']."' and golongan='".$val['golongan']."' and karyawaniduser='".$val['karyawaniduser']."'";
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".setup_approval_notif where karyawanid='".$karyawanidrep2."' and kodeunit='".$val['kodeunit']."' and jenispersetujuan='".$val['jenispersetujuan']."' and level='".$val['level']."' and departemen='".$val['departemen']."' and tipekaryawan='".$val['tipekaryawan']."' and jabatan='".$val['jabatan']."' and tipe='".$val['tipe']."' and golongan='".$val['golongan']."' and karyawaniduser='".$val['karyawaniduser']."'";
				$owlPDO->exec($str);
			}
			
			## DELETE AWAL
			$str="delete from ".$dbname.".setup_approval where karyawanid='".$karyawanidrep1."'";
			$owlPDO->exec($str);
			
			$str="delete from ".$dbname.".setup_approval_notif where karyawanid='".$karyawanidrep1."'";
			$owlPDO->exec($str);
			
			## INSERT NEW
			foreach($arrsapp as $key=>$val){
				$str="insert into ".$dbname.".setup_approval (kodeunit,jenispersetujuan,level,nilaidari,nilaisampai,karyawanid,departemen,tipekaryawan,jabatan,tipe,golongan,karyawaniduser) values ('".$val['kodeunit']."','".$val['jenispersetujuan']."','".$val['level']."','".$val['nilaidari']."','".$val['nilaisampai']."','".$karyawanidrep2."','".$val['departemen']."','".$val['tipekaryawan']."','".$val['jabatan']."','".$val['tipe']."','".$val['golongan']."','".$val['karyawaniduser']."')";
				$owlPDO->exec($str);
				
				$str="insert into ".$dbname.".setup_approval_notif (kodeunit,jenispersetujuan,level,karyawanid,departemen,tipekaryawan,jabatan,tipe,golongan,karyawaniduser) values ('".$val['kodeunit']."','".$val['jenispersetujuan']."','".$val['level']."','".$karyawanidrep2."','".$val['departemen']."','".$val['tipekaryawan']."','".$val['jabatan']."','".$val['tipe']."','".$val['golongan']."','".$val['karyawaniduser']."')";
				$owlPDO->exec($str);
			}
			
			
			$arrapp=array();
			$str="select * from ".$dbname.".approval where karyawanid='".$karyawanidrep1."' and (status='0' or status='9')";
			$res=fetchdata($str);
			$no=0;
			foreach($res as $val){
				$no++;
				$arrapp[$no]['notransaksi']=$val['notransaksi'];
				$arrapp[$no]['jenispersetujuan']=$val['jenispersetujuan'];
				$arrapp[$no]['level']=$val['level'];
				$arrapp[$no]['status']=$val['status'];
				
				$str="delete from ".$dbname.".approval where karyawanid='".$karyawanidrep2."' and notransaksi='".$val['notransaksi']."' and jenispersetujuan='".$val['jenispersetujuan']."' and level='".$val['level']."' and status='".$val['status']."'";
				$owlPDO->exec($str);
			}
			
			## DELETE AWAL
			$str="delete from ".$dbname.".approval where karyawanid='".$karyawanidrep1."' and (status='0' or status='9')";
			$owlPDO->exec($str);
			
			## INSERT NEW
			foreach($arrapp as $key=>$val){
				$str="insert into ".$dbname.".approval (nourut,notransaksi,jenispersetujuan,level,karyawanid,status) values ('','".$val['notransaksi']."','".$val['jenispersetujuan']."','".$val['level']."','".$karyawanidrep2."','".$val['status']."')";
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning\n" . addslashes($e->getMessage());
		}

	break;

	case 'simpancopy':
		if($kodeorgcopy1=='' or $jenispersetujuancopy=='' or $kodeorgcopy2=='' or $jenispersetujuancopy2==''){
			exit("Warning : Kode organisasi, Jenis persetujuan dari dan tujuan wajib diisi.");
		}
	try {
		$owlPDO->beginTransaction();
		# HAPUS DULU DATA YG ADA DI TUJUAN
		$dl="";
		if($departemencopy2!=''){
			$dl.=" and departemen='".$departemencopy2."'";
		}
		if($golongancopy2!=''){
			$dl.=" and golongan='".$golongancopy2."'";
		}
		
		$str="delete from ".$dbname.".setup_approval where kodeunit='".$kodeorgcopy2."' and jenispersetujuan='".$jenispersetujuancopy2."' ".$dl."";
		$owlPDO->exec($str);
		
		$str="delete from ".$dbname.".setup_approval_notif where kodeunit='".$kodeorgcopy2."' and jenispersetujuan='".$jenispersetujuancopy2."' ".$dl."";
		$owlPDO->exec($str);
		
		$wh="";
		if($departemencopy!=''){
			$wh.=" and departemen='".$departemencopy."'";
		}
		if($golongancopy!=''){
			$wh.=" and golongan='".$golongancopy."'";
		}
		$str = "select * from  " . $dbname . ".setup_approval where kodeunit='".$kodeorgcopy1."' and jenispersetujuan='".$jenispersetujuancopy."' ".$wh." order by level asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if($departemencopy2!='' and $golongancopy2!=''){
				$str="insert into ".$dbname.".setup_approval (kodeunit, jenispersetujuan, level, nilaidari, nilaisampai, karyawanid, departemen, tipekaryawan, tipe, jabatan, golongan, karyawaniduser) values ('".$kodeorgcopy2."','".$jenispersetujuancopy2."','".$bar['level']."','".$bar['nilaidari']."','".$bar['nilaisampai']."','".$bar['karyawanid']."'
				,'".$departemencopy2."','".$bar['tipekaryawan']."','".$bar['tipe']."','".$bar['jabatan']."','".$golongancopy2."','".$bar['karyawaniduser']."')";	
				
				$sql="insert into ".$dbname.".setup_approval_notif (kodeunit, jenispersetujuan, level, karyawanid, departemen, tipekaryawan, tipe, jabatan, golongan, karyawaniduser) values ('".$kodeorgcopy2."','".$jenispersetujuancopy2."','".$bar['level']."','".$bar['karyawanid']."'
				,'".$departemencopy2."','".$bar['tipekaryawan']."','".$bar['tipe']."','".$bar['jabatan']."','".$golongancopy2."','".$bar['karyawaniduser']."')";	
			}elseif($departemencopy2!='' and $golongancopy2==''){
				$str="insert into ".$dbname.".setup_approval (kodeunit, jenispersetujuan, level, nilaidari, nilaisampai, karyawanid, departemen, tipekaryawan, tipe, jabatan, golongan, karyawaniduser) values 
				('".$kodeorgcopy2."','".$jenispersetujuancopy2."','".$bar['level']."','".$bar['nilaidari']."','".$bar['nilaisampai']."','".$bar['karyawanid']."','".$departemencopy2."','".$bar['tipekaryawan']."','".$bar['tipe']."','".$bar['jabatan']."','".$bar['golongan']."','".$bar['karyawaniduser']."')";
				
				$sql="insert into ".$dbname.".setup_approval_notif (kodeunit, jenispersetujuan, level, karyawanid, departemen, tipekaryawan, tipe, jabatan, golongan, karyawaniduser) values 
				('".$kodeorgcopy2."','".$jenispersetujuancopy2."','".$bar['level']."','".$bar['karyawanid']."','".$departemencopy2."','".$bar['tipekaryawan']."','".$bar['tipe']."','".$bar['jabatan']."','".$bar['golongan']."','".$bar['karyawaniduser']."')";
				
			}elseif($departemencopy2=='' and $golongancopy2!=''){
				$str="insert into ".$dbname.".setup_approval (kodeunit, jenispersetujuan, level, nilaidari, nilaisampai, karyawanid, departemen, tipekaryawan, tipe, jabatan, golongan, karyawaniduser) values ('".$kodeorgcopy2."','".$jenispersetujuancopy2."','".$bar['level']."','".$bar['nilaidari']."','".$bar['nilaisampai']."','".$bar['karyawanid']."'
				,'".$bar['departemen']."','".$bar['tipekaryawan']."','".$bar['tipe']."','".$bar['jabatan']."','".$golongancopy2."','".$bar['karyawaniduser']."')";	
				
				$sql="insert into ".$dbname.".setup_approval_notif (kodeunit, jenispersetujuan, level, karyawanid, departemen, tipekaryawan, tipe, jabatan, golongan, karyawaniduser) values ('".$kodeorgcopy2."','".$jenispersetujuancopy2."','".$bar['level']."','".$bar['karyawanid']."'
				,'".$bar['departemen']."','".$bar['tipekaryawan']."','".$bar['tipe']."','".$bar['jabatan']."','".$golongancopy2."','".$bar['karyawaniduser']."')";	
			}else{
				$str="insert into ".$dbname.".setup_approval 
				(kodeunit,jenispersetujuan,level,nilaidari,nilaisampai,karyawanid,departemen,tipekaryawan,tipe,jabatan,golongan,karyawaniduser) values 
				('".$kodeorgcopy2."','".$jenispersetujuancopy2."','".$bar['level']."','".$bar['nilaidari']."','".$bar['nilaisampai']."','".$bar['karyawanid']."'
				,'".$bar['departemen']."','".$bar['tipekaryawan']."','".$bar['tipe']."','".$bar['jabatan']."','".$bar['golongan']."','".$bar['karyawaniduser']."')";
				
				$sql="insert into ".$dbname.".setup_approval_notif 
				(kodeunit,jenispersetujuan,level,karyawanid,departemen,tipekaryawan,tipe,jabatan,golongan,karyawaniduser) values 
				('".$kodeorgcopy2."','".$jenispersetujuancopy2."','".$bar['level']."','".$bar['karyawanid']."'
				,'".$bar['departemen']."','".$bar['tipekaryawan']."','".$bar['tipe']."','".$bar['jabatan']."','".$bar['golongan']."','".$bar['karyawaniduser']."')";	
			}

			$owlPDO->exec($str);
			$owlPDO->exec($sql);
		}
	
		$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning\n" . addslashes($e->getMessage());
		}
	break;
	
	case'getkarygol':
		$where='';
		if($golongan==''){
			$arrgol = array('3','4','5','6','7');
			foreach ($arrgol as $key => $value) {
				if($key == '0'){
					$where.=" and b.namagolongan LIKE '".$value."%'";
				}else{
					$where.=" OR b.namagolongan LIKE '%".$value."%'";
				}
			}
		}else{
			$where.=" and b.namagolongan like '%".$golongan."%'";
		}
		if($kodeunit!=''){
			$where.=" and lokasitugas='".$kodeunit."'";
		}


		$str="select * from ".$dbname.".setup_approval where kodeunit='".$kodeunit."' and jenispersetujuan='".$jenispersetujuan."' and level='".$level."' and karyawanid='".$karyawanid."' and departemen='".$departemen."' and tipekaryawan='".$tipekaryawan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$barx=$res->fetch();
		$str = "select a.karyawanid,a.namakaryawan,b.namagolongan,b.kodegolongan from  " . $dbname . ".datakaryawan a
				left join  " . $dbname . ".sdm_5golongan b on a.kodegolongan=b.kodegolongan
				where 1=1 ".$where." and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  order by namakaryawan asc ";
		// exit('error : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optkar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			if($barx['karyawaniduser'] == $bar['karyawanid']){
				$optkar.="<option value='" . $bar['karyawanid'] . "' selected>" . $bar['namakaryawan'] . " - [".$bar['namagolongan']."]</option>";
			}else{
				$optkar.="<option value='" . $bar['karyawanid'] . "'>" . $bar['namakaryawan'] . " - [".$bar['namagolongan']."]</option>";
			}
		}
		
	echo $optkar;
	break;
	default:
	break;					
}

?>
