<?php
	session_start();
	require_once('master_validation.php');
	require_once('config/connection.php');
	include_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include_once('lib/zFunction.php');

	$proses            =checkPostGet('proses','');
	$lokasi            =$_SESSION['empl']['lokasitugas'];
	$codeOrg           =checkPostGet('codeOrg','');
	$trans_no          =checkPostGet('trans_no','');
	$nmpemohon         =checkPostGet('nmpemohon','');
	$vhc_code          =checkPostGet('vhc_code','');
	$kdTraksi          =checkPostGet('kdTraksi','');
	$tgl_pengajuan     =tanggalsystemn(checkPostGet('tgl_pengajuan',''));
	$tgl_ganti         =tanggalsystemn(checkPostGet('tgl_ganti',''));
	$tgl_keluar        =tanggalsystemn(checkPostGet('tgl_keluar',''));
	$dwnTime           =checkPostGet('dwnTime','');
	$dwnTimeMnt        =checkPostGet('dwnTimeMnt','');
	$kmmasuk           =checkPostGet('kmmasuk','');
	$kmkeluar          =checkPostGet('kmkeluar','');
	$descDmg           =(checkPostGet('descDmg',''));
	$terlambat         =(checkPostGet('terlambat',''));


	$kdTraksiDt        =makeOption($dbname,'vhc_5master','kodevhc,kodetraksi');
	$usr_id            =$_SESSION['standard']['userid'];
	$nodok             =checkPostGet('nodok','');
	$external          =checkPostGet('external','');
	$namafile          =checkPostGet('namafile','');
	$kdTraksiDt        =makeOption($dbname,'vhc_5master','kodevhc,kodetraksi');
	$nmvhc        	   =makeOption($dbname,'vhc_5master','kodevhc,detailvhc');

	$kodeBarang        =checkPostGet('kodeBarang','');
	$tipeperbaikan     =checkPostGet('tipeperbaikan','');
	$jumlahBarang      =checkPostGet('jumlahBarang','');
	$keteranganBarang  =checkPostGet('keteranganBarang','');
	$namaBarang        =checkPostGet('namaBarang','');
	$satuanBarang      =checkPostGet('satuanBarang','');
	$karyawan          =checkPostGet('karyawan','');
	$kodeBarangback    =checkPostGet('kodeBarangback','');

	$perpt             = checkPostGet('perpt','');
	$namaBarangCari    =checkPostGet('namaBarangCari','');
	$namaBarangCariback=checkPostGet('namaBarangCariback','');
	$path              = "fileupload/vhc_service/";

	$jenisVhc          =makeOption($dbname,'vhc_5master','kodevhc,jenisvhc');
	$nmjenis           =makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');
	//$nikKar          =makeOption($dbname,'datakaryawan','karyawanid,nik',$whKar);
	//$nmKar           =makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
	//$nmBrg           =makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whBrg);
	//$satBrg          =makeOption($dbname,'log_5masterbarang','kodebarang,satuan',$whBrg);

	$schTran           =checkPostGet('schTran','');
	$schTgl            =tanggalsystemn(checkPostGet('schTgl',''));
	$schTglPengajuan   =tanggalsystemn(checkPostGet('schTglPengajuan',''));

	$jab               = getPostingJabatan('traksi'); 

	// Get Nik dan Nama
	$nikKar = $nmKar = array();
	$qKary = selectQuery($dbname,'datakaryawan','karyawanid,namakaryawan,nik');
	$resKary = fetchData($qKary);
	foreach($resKary as $row) {
		$nikKar[$row['karyawanid']] = $row['nik'];
		$nmKar[$row['karyawanid']] = $row['namakaryawan'];
	}

	// Get Nama dan Satuan Barang
	$nmBrg = $satBrg = array();
	$qBrg = selectQuery($dbname,'log_5masterbarang','kodebarang,namabarang,satuan');
	$resBrg = fetchData($qBrg);
	foreach($resBrg as $row) {
		$nmBrg[$row['kodebarang']] = $row['namabarang'];
		$satBrg[$row['kodebarang']] = $row['satuan'];
	}


	if($schTgl=='--')
	{
		$schTgl='';
	}
	if($schTglPengajuan=='--')
	{
		$schTglPengajuan='';
	}

	switch($proses)
	{
		case'getdivisi':
			if($perpt == "perpt"){
				$qTrans = selectQuery($dbname,'organisasi',"kodeorganisasi,namaorganisasi","induk='".$_SESSION['empl']['lokasitugas']."' and tipe <> 'GUDANG' and tipe <> 'GUDANGTEMP' order by namaorganisasi");
				$resTrans = fetchData($qTrans);
				$selection = "<select class='select2' id='list_divisi' style='width:210px' onchange=\"selectkaryawan(this.value,'perpt');\">";
				$selection .= '<option value="">'.$_SESSION['lang']['all'].'</option>'; 
				foreach($resTrans as $v){
					$selection .= '<option value="'.$v['kodeorganisasi'].'">'.$v['namaorganisasi'].'</option>'; 
				}
				$selection .= '</select>';
			}elseif($perpt == "permandor"){
				$qTrans = " select a.karyawanid,a.namakaryawan,a.nik,a.subbagian from ".$dbname.".datakaryawan a
					left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
					where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and statuskaryawan != 'Keluar' and a.tipekaryawan !='0'
					and b.namajabatan like '%mandor%' and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' order by namakaryawan,a.subbagian ";
				$resTrans = fetchData($qTrans);
				$selection = "<select class='select2' id='list_mandor' style='width:210px' onchange=\"selectkaryawan(this.value,'permandor');\">";
				$selection .= '<option value="">'.$_SESSION['lang']['all'].'</option>'; 
				foreach($resTrans as $v){
					$selection .= '<option value="'.$v['karyawanid'].'">'.$v['namakaryawan'].' ['.$v['nik'].'] ['.$v['subbagian'].']</option>'; 
				}
				$selection .= '</select>';
			}
			
			echo $selection;
		break;
		case'getkaryawan':

			## Gini dulu urgent di kebun/ kalau sempat nanti diganti parameter aplikasi
			if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' ";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
			}

			if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
			}

			if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' ";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}
				$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
			}

			$optKaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$iKar="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a
					left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
					where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and statuskaryawan != 'Keluar' ".$whereKary;
			//exit("error:".$iKar);
			$res=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($dKar=$res->fetch())
			{
				$optKaryawan.="<option value=".$dKar['karyawanid'].">".$dKar['namakaryawan']." [".$dKar['nik']."]</option>";
			}
			echo $optKaryawan;
		break;
		case'getKm':
			$iKm="select * from ".$dbname.".vhc_kmhm_track where kodevhc='".$vhc_code."' order by kmhmakhir desc";
			$res=$owlPDO->query($iKm) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$dKm=  $res->fetch();
			
			if($dKm['kmhmakhir']=='' || $dKm['kmhmakhir']=='0')
			{
				$dsb="0";
			}
			else
			{
				$dsb="1";
			}
				echo $dKm['kmhmakhir']."###".$dsb;            
		break;
		case'loaddetail':	
            ## Gini dulu urgent di kebun/ kalau sempat nanti diganti parameter aplikasi
			if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' ";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
			}

			if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA'";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}

				$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
			}

			if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
				$dataunitx='';
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' ";
				$res=fetchdata($str);
				foreach($res as $val){
					if($dataunitx==""){
						$dataunitx.="'".$val['kodeorganisasi']."'";				
					}else{
						$dataunitx.=",'".$val['kodeorganisasi']."'";				
					}
				}
				$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
			}

			$optKaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$iKar="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a
					left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
					where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and statuskaryawan != 'Keluar' ".$whereKary;
			//exit("error:".$iKar);
			$res=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($dKar=$res->fetch())
			{
				$optKaryawan.="<option value=".$dKar['karyawanid'].">".$dKar['namakaryawan']." [".$dKar['nik']."]</option>";
			}

			//Keluarkan Detailnya
			$frm[0] ="<fieldset>";
			$frm[0] .= "<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>";
			$frm[0] .= "<table class=sortable border=0 cellpadding=3 cellspacing=1>";
				$frm[0] .= "<thead>
								<tr class=rowheader>		
									<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
									<td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
									<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
									<td align=center>" . $_SESSION['lang']['satuan'] . "</td>
									<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
									<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
									<td align=center>" . $_SESSION['lang']['save'] . "</td>
								</tr>
							</thead>";
				$frm[0] .= "<tr class=rowcontent>
								<td></td>
								<td>
									<input type=text  id=kodeBarang disabled onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
									<img src=images/zoom.png title='" . $_SESSION['lang']['find'] . "' id=tmblCariNoGudang class=resicon onclick=tambahBarang('" . $_SESSION['lang']['find'] . "',event)>
								</td>
								<td>
									<input type=text  id=namaBarang disabled onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:105px;\">
								</td>
								<td>
									<input type=text  id=satuanBarang disabled onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:50px;\">
								</td>
								<td>
									<input type=text  id=jumlahBarang onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\">
								</td>
								<td>
									<input type=text  id=keteranganBarang onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:225px;\">
								</td>
								<td align=center>
									<img src=images/save.png class=resicon  title='Save Material' onclick=saveBarang()>
								</td>
							</tr>
							<tbody id='containListBarang'></tbody>";
			$frm[0] .= "</table></fieldset>";

			$frm[1] = "<fieldset>";
			$frm[1] .= "<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>";
			$frm[1] .= "<table border=0 cellpadding=1 cellspacing=1>";
				$frm[1] .= "<tr hidden>
								<td colspan='3'><input id='perpt' type='checkbox' value='perpt' onchange='openlistperpt(this);'>&nbsp;&nbsp;&nbsp;<span>Per PT</span>&nbsp;&nbsp;&nbsp;
								<input id='permandor' type='checkbox' value='permandor' onchange='openlistpermandor(this);'>&nbsp;&nbsp;&nbsp;<span>Per Mandor</span>
								</td>
							</tr>
							<tr id='listpt' style='display:none;'>
								<td id='filtername'></td>
								<td>:</td>		
								<td id='listdivisi'></td>
							</tr>
							<tr>
								<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
								<td>:</td>		
								<td>
									<select class='select2' style='width:210px' id='karyawan'>" . $optKaryawan . "</select>
									<img id=karyawan onclick=\"z.elSearch('karyawan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
								</td>
							</tr>
							<tr>
								<td><td><td><button id=save class=mybutton onclick=saveKaryawan()>Simpan</button></td>
							</tr>";
			$frm[1] .= "</table></fieldset>";
			$frm[1] .= "<div style='clear:both'></div>";
			$frm[1] .= "<fieldset  style='display:block;'>";
			$frm[1] .= "<legend><b>" . $_SESSION['lang']['list'] . "</b></legend>"; // 
			$frm[1] .= "<div id=containListKaryawan></div></fieldset>";

			$frm[2] = "<fieldset>";
			$frm[2] .= "<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>";
			$frm[2] .= "<table border=0 cellpadding=3 cellspacing=1 class=sortable>";
				$frm[2] .= "<thead>
								<tr class=rowheader>
									<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
									<td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
									<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
									<td align=center>" . $_SESSION['lang']['satuan'] . "</td>
									<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
									<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
									<td align=center  width=30px>" . $_SESSION['lang']['save'] . "</td>
								</tr>
							</thead>";
				$frm[2] .= "<tr class=rowcontent>
								<td></td>
								<td><input type=text  id=kodeBarangback disabled onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
								<img src=images/zoom.png title='" . $_SESSION['lang']['find'] . "' id=tmblCariNoGudangback class=resicon onclick=tambahBarangback('" . $_SESSION['lang']['find'] . "',event)>
								</td>
								<td>
									<input type=text disabled id=backnamaBarang onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:105px;\">
								</td>
								<td>
									<input type=text disabled id=backsatuanBarang onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:50px;\">
								</td>
								<td>
									<input type=text  id=backjumlahBarang onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\">
								</td>
								<td>
									<input type=text  id=backketeranganBarang onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:225px;\">
								</td>
								<td align=center>
									<img src=images/save.png class=resicon  title='Save Material' onclick=backsaveBarang()>
								</td>
							</tr>";
			$frm[2] .= "<tbody id='backcontainListBarang'></tbody>
					</table></fieldset>";

			$frm[2] .= "<fieldset style='display:none;'>";
			$frm[2] .= "<legend><b>List File</b></legend>";
			$frm[2] .= "<button class=mybutton onclick='showupload(event)'>Upload Files</button><div id=loadfilesht></div></fieldset>";

			$hfrm[0] = $_SESSION['lang']['daftarbarang'];
			$hfrm[1] = $_SESSION['lang']['karyawan'];
			$hfrm[2] = $_SESSION['lang']['bulkreturn'];

			// //draw tab, jangan ganti parameter pertama, krn dipakai di javascript
			drawTab('FRM', $hfrm, $frm, '', '100%');
		break;
		case'getVhc':
			$svhc="select * from ".$dbname.".vhc_5master where kodetraksi='".$_POST['kdTraksi']."' and status=1 order by kodevhc asc";
			$optVhc.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$res=$owlPDO->query($svhc) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($rvhc=$res->fetch()){
				if($rvhc['nopol'] != ''){
					$nopol = "[ ".$rvhc['nopol']." ]";
				}else{
					$nopol = '';
				}
				if($rvhc['tahunperolehan'] != '0'){
					$tahunperolehan = "[ ".$rvhc['tahunperolehan']." ]";
				}else{
					$tahunperolehan = '';
				}
				if($_POST['kdVhc']==$rvhc['kodevhc']){
						$optVhc.="<option value='".$rvhc['kodevhc']."' selected>".$rvhc['kodevhc']." [ ".$rvhc['detailvhc']." ] ".$nopol." ".$tahunperolehan."</option>";
				}else{
						$optVhc.="<option value='".$rvhc['kodevhc']."'>".$rvhc['kodevhc']." [ ".$rvhc['detailvhc']." ] ".$nopol." ".$tahunperolehan."</option>";
				}
			}
			echo $optVhc;
		break;
		case'saveHeader':
			$str="select * from ".$dbname.".vhc_pengajuanservice where tanggalpengajuan ='".$tgl_pengajuan."' and kodevhc='".$vhc_code."'";
			$res=fetchdata($str);
			if(count($res) > 0 && $trans_no == ''){
				exit("Warningsystem : <br>".$_SESSION['lang']['tanggalpengajuan']." ".tanggalnormal($tgl_pengajuan)." ".$_SESSION['lang']['dan']."<br>  ".$_SESSION['lang']['kode']." Vehicle ".$vhc_code." - ".getVhc($vhc_code,'detailvhc')."<br> sudah ada. ");
			}else{
				try{
					$owlPDO->beginTransaction();
					
					if($trans_no==''){
						$tgl=  date('Ymd');
						$bln = substr($tgl,4,2);
						$thn = substr($tgl,0,4);

						$notransaksi=$codeOrg."/".date('Y').date('m');
						$str="select nopengajuan from ".$dbname.".vhc_pengajuanservice where nopengajuan like '%".$notransaksi."%' order by nopengajuan desc limit 1";
						$res=fetchdata($str);
						$notransaksi=$res[0]['nopengajuan'];
						if($notransaksi==''){
							$counter=addZero(1,4);
						}else{
							$expnotran=explode('/',$res[0]['nopengajuan']);
							$counter=addZero(($expnotran[3]+1),4);
						}
						$notransaksi=$codeOrg."/".$thn.$bln."/WOSVC/".$counter;

						//pakai format saat ini
						// $str="select * from ".$dbname.".setup_insnotrans where unit ='".substr($codeOrg,0,4)."' and inisial='spksvc'";
						// $res=fetchdata($str);
						// if(count($res) < 1){
						// 	exit("Warning : Counter Notransaksi untuk unit <br>".substr($codeOrg,0,4)." - ".getNamaOrg(substr($codeOrg,0,4))." dengan inisial spksvc <br>belum ditambahkan di menu SETUP > SETUP NO. TRANSAKSI. Silahkan tambahkan unit ".substr($codeOrg,0,4)." dengan inisial spksvc.  ");
						// }
						$trans_no=$notransaksi;
						
						$data = array(
							'nopengajuan' 		=> $trans_no,
							'tanggalpengajuan' 	=> $tgl_pengajuan,
							'karyawanidpemohon'	=> $nmpemohon,
							'kodeorg'			=> $codeOrg,
							'kodevhc' 	 		=> $vhc_code,
							'tglmasuk'			=> $tgl_ganti,
							'tglkeluar'			=> $tgl_keluar,
							'kmmasuk' 	 		=> $kmmasuk,
							'kmkeluar' 	 		=> $kmkeluar,
							'downtimejam' 	  	=> $dwnTime,
							'tipeperbaikan' 	=> $tipeperbaikan,
							'kerusakan' 		=> strtoupper($descDmg),
							'alasan' 			=> strtoupper($terlambat),
							'createdby'	 		=> $usr_id,
							'createdtime'	 	=> date('Y-m-d H:i:s')
						);
						
						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}

						$str = insertQuery($dbname,'vhc_pengajuanservice',$data,$cols);
						$owlPDO->exec($str);
					}else{
						$str="update ".$dbname.".vhc_pengajuanservice set 
								tglmasuk='".$tgl_ganti."',
								tglkeluar='".$tgl_keluar."',
								downtimejam='".$dwnTime."',
								kmmasuk='".$kmmasuk."',
								kmkeluar='".$kmkeluar."',
								kerusakan='".strtoupper($descDmg)."',
								alasan='".strtoupper($terlambat)."',
								tipeperbaikan='".($tipeperbaikan)."' 
								where nopengajuan='".$trans_no."' and kodeorg='".$codeOrg."' and kodevhc='".$vhc_code."' ";
						$owlPDO->exec($str);
					}

					echo $trans_no;
					
					$owlPDO->commit();
				}catch(PDOException $e){
					$owlPDO->rollback();
					echo "Warning, " . addslashes($e->getMessage());
					die();
				}
			}
			
		break;
		case'insert':
			validasiInput(substr($codeOrg,0,4),'','WS',$tgl_keluar,$exit='1');
			
			$iSave="insert into ".$dbname.".vhc_pengajuanservice (
				`kodeorg`,`kodevhc`,`tanggal`,`updateby`,`nopengajuan`,`downtime`, `kerusakan`,`noreferensi`,`tanggalkeluar`,`kmmasuk`,`kmkeluar`,`alasan`,`external`,`tipeperbaikan`,`createdtime`) values 
			('".$codeOrg."','".$vhc_code."','".$tgl_ganti."','".$usr_id."','".$trans_no."','".$dwnTime."','".strtoupper($descDmg)."',
				'".$nodok."','".$tgl_keluar."','".$kmmasuk."','".$kmkeluar."','".strtoupper($terlambat)."','".$external."','".$tipeperbaikan."','".date('Y-m-d H:i:s')."')";
			try{$owlPDO->exec($iSave); }catch (PDOException $e) {print " Gagal  insert!: " . $e->getMessage() . "\n"; die(); }
		break;
		case'update':
			$iUpdate="update ".$dbname.".vhc_pengajuanservice set  noreferensi='".$nodok."',tanggal='".$tgl_ganti."',
					tanggalkeluar='".$tgl_keluar."',downtime='".$dwnTime."',kmmasuk='".$kmmasuk."',kmkeluar='".$kmkeluar."',
					kerusakan='".strtoupper($descDmg)."',alasan='".strtoupper($terlambat)."',tipeperbaikan='".($tipeperbaikan)."'  
					where  nopengajuan='".$trans_no."' and kodeorg='".$codeOrg."' and kodevhc='".$vhc_code."' ";  
			try{$owlPDO->exec($iUpdate); }catch (PDOException $e) {print " Gagal  update!: " . $e->getMessage() . "\n"; die(); }
		break;
		case'delete':
			try{
				$owlPDO->beginTransaction();
				
				$str="delete from ".$dbname.".vhc_pengajuanservice where  nopengajuan='".$trans_no."'";
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".listfilepenggantian where  notransaksi='".$trans_no."'";
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".vhc_pengajuanservicedt where  nopengajuan='".$trans_no."'";
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".vhc_pengajuanservicedt_karyawan where  nopengajuan='".$trans_no."'";
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".vhc_pengajuanservicedt_pengembalian where  nopengajuan='".$trans_no."'";
				$owlPDO->exec($str);
				
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Warning, " . addslashes($e->getMessage());
				die();
			}
		break;
		case'getListBarang':
			echo"<fieldset  style='float:left;' >
					<table cellspacing=1 border=0 class=data >
						<tr>
							<td colspan=2>".$_SESSION['lang']['namabarang']."</td>

							<td colspan=5>: 
									<input type=text id=namaBarangCari onkeyup=enterkey(event,cariListBarang) class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:120px;'>
									<button class=mybutton onclick=cariListBarang()>cari</button>
							<td>
						<tr>
					</table>
	
					<table id=listCariBarang class=sortable>
						<thead>
						<tr class=rowheader>
								<td align=center>No</td>
								<td align=center>".$_SESSION['lang']['kodebarang']."</td>
								<td align=center>".$_SESSION['lang']['namabarang']."</td>
								<td align=center>".$_SESSION['lang']['satuan']."</td>
						</tr></thead>";
							
						if($namaBarangCari=='')
						{
							echo "	<tr class=rowcontent>
										<td colspan=4 align=center>".$_SESSION['lang']['errdatanotexist']."<br>Silahkan cari Nama Barang.</td>
									</tr>";
						}
						else
						{
							
							$i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
							$res=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							while ($d=$res->fetch())
							{
								$whBrg="kodebarang='".$d['kodebarang']."'";
								$no+=1;
								echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."');\">
										<td align=center>".$no."</td>
										<td>".$d['kodebarang']."</td>
										<td>".$nmBrg[$d['kodebarang']]."</td>
										<td>".$satBrg[$d['kodebarang']]."</td>
										
								</tr>";
							}
						}
					echo"</table>
				</fieldset>";
		break;
		case'getListBarangback':
			echo"<fieldset  style='float:left;' >
					
						<table cellspacing=1 border=0 class=data >
							<tr>
								<td colspan=2>".$_SESSION['lang']['namabarang']."</td>

								<td colspan=5>: 
										<input type=text id=namaBarangCariback onkeyup=enterkey(event,cariListBarangback) class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:120px;'>
										<button class=mybutton onclick=cariListBarangback()>cari</button>
								<td>
							<tr>
						</table>
	
						<table id=listCariBarangback class=sortable>
						<thead>
						<tr class=rowheader>
								<td align=center>No</td>
								<td align=center>".$_SESSION['lang']['kodebarang']."</td>
								<td align=center>".$_SESSION['lang']['namabarang']."</td>
								<td align=center>".$_SESSION['lang']['satuan']."</td>
						</tr></thead>";
							
						if($namaBarangCariback=='')
						{
						
						}
						else
						{
							
							$i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCariback."%'";
							$res=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							while ($d=$res->fetch())
							{
								$whBrg="kodebarang='".$d['kodebarang']."'";
								$no+=1;
								echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarangback('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."');\">
										<td align=center>".$no."</td>
										<td>".$d['kodebarang']."</td>
										<td>".$nmBrg[$d['kodebarang']]."</td>
										<td>".$satBrg[$d['kodebarang']]."</td>
										
								</tr>";
							}
						}
						echo"</table>
			</fieldset>";
		break;
		case 'saveBarang':
			try{
				$owlPDO->beginTransaction();
				
				$str="select count(nopengajuan) as jlhitem from ".$dbname.".vhc_pengajuanservicedt where nopengajuan='".$trans_no."' and kodebarang='".$kodeBarang."'";
				$res=fetchdata($str);
				$jlhitem=$res[0]['jlhitem'];
				
				if($jlhitem > 0){
					throw new PDOException("Kode barang sudah pernah diinput.");
				}
				
				$str="insert into ".$dbname.".vhc_pengajuanservicedt (`nopengajuan`,`kodebarang`,`jumlah`,`keterangan`)
				values ('".$trans_no."','".$kodeBarang."','".$jumlahBarang."','".strtoupper($keteranganBarang)."')";
				$owlPDO->exec($str);
				
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Warning, " . addslashes($e->getMessage());
				die();
			}
		break;
		case 'loadDetailBarang':
			$no=0;
			$iListBarang="select * from ".$dbname.".vhc_pengajuanservicedt where nopengajuan='".$trans_no."' ";
			$res=$owlPDO->query($iListBarang) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($dListBarang=$res->fetch())
			{
				$whBrg="kodebarang='".$dListBarang['kodebarang']."'";
					$no+=1;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td align=center>".$dListBarang['kodebarang']."</td>";
					$tab.="<td align=left>".$nmBrg[$dListBarang['kodebarang']]."</td>";
					$tab.="<td align=center>".getSatBrg($dListBarang['kodebarang'])."</td>";
					$tab.="<td align=right>".$dListBarang['jumlah']."</td>";
					$tab.="<td align=left>".$dListBarang['keterangan']."</td>";
					$tab.="<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
							onclick=\"deleteBarang('".$dListBarang['nopengajuan']."','".$dListBarang['kodebarang']."');\" ></td>";
					$tab.="</tr>";
			}
			$tab.="</table>";
			echo $tab;
		break;
		case 'deleteBarang':
			$iDelBarang="delete from ".$dbname.".vhc_pengajuanservicedt where nopengajuan='".$trans_no."' and kodebarang='".$kodeBarang."' ";
			try{$owlPDO->exec($iDelBarang); }catch (PDOException $e) {print " Gagal delete barang !: " . $e->getMessage() . "\n"; die(); }		
		break;
		case'unposting':
			$tglunpost=checkPostGet('tgl', '');
			#cek tutup buku
			$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".substr($trans_no,0,4)."' and periode ='".substr($tglunpost,0,7)."'";
			
			$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$ttp->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$ttp->fetch();
				$tutup=$bar['tutupbuku'];
			if($tutup==1){
				exit("Error : Unposting tidak bisa dilakukan karena periode akuntansi ".substr($tglunpost,0,7)." unit ".substr($trans_no,0,4)." sudah di tutup.");
			}
			
			$str="select * from ".$dbname.".approval where jenispersetujuan='SVC' and notransaksi ='".$trans_no."' and level='1'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$tutup=$bar['status'];
			if($tutup!=0){
				exit("Error : Unposting tidak bisa dilakukan karena sudah dalam proses persetujuan");
			}

			$str = "update " . $dbname . ".vhc_pengajuanservice set posting='0',"
					. "postingby='" . $_SESSION['standard']['userid'] . "' where nopengajuan = '".$trans_no."' and tanggal='" . $tglunpost . "' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
			
			$str = "delete from " . $dbname . ".vhc_stokbarangbekas where notransaksi = '".$trans_no."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			
			$str = "delete from " . $dbname . ".approval where notransaksi = '".$trans_no."' and jenispersetujuan='SVC'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		break;
		case'loadData':
			$arrapprv = array(
						"0"=>$_SESSION['lang']['belumdiajukan'],
						"1"=>$_SESSION['lang']['disetujui'],
						"2"=>$_SESSION['lang']['ditolak'],
						"3"=>$_SESSION['lang']['reconfirm'],
						"9"=>$_SESSION['lang']['prosespersetujuan']);
			$arrpost = array(
						"0"=>$_SESSION['lang']['belumposting'],
						"1"=>$_SESSION['lang']['post'],
						"9"=>$_SESSION['lang']['prosespersetujuan']);

			//Ambil Menu SERVICE
			$smnu="select id,caption,caption2,caption3 from ".$dbname.".menu where id='506'";//Silahkan diganti kalau urutan id di tabel menunya berubah
			$res=$owlPDO->query($smnu) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);      
			$menunya=$res->fetch();

			if($_SESSION['language'] == 'ID'){
				$nmmenu = $menunya['caption'];
			}elseif($_SESSION['language'] == 'EN'){
				$nmmenu = $menunya['caption2'];
			}elseif($_SESSION['language'] == 'MY'){
				$nmmenu = $menunya['caption3'];
			}

			//Ambil Menu PENGAJUAN SERVICE
			$smnu="select id,caption,caption2,caption3 from ".$dbname.".menu where id='2282'";//Silahkan diganti kalau urutan id di tabel menunya berubah
			$res=$owlPDO->query($smnu) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);      
			$menunya=$res->fetch();

			if($_SESSION['language'] == 'ID'){
				$nmmenuu = $menunya['caption'];
			}elseif($_SESSION['language'] == 'EN'){
				$nmmenuu = $menunya['caption2'];
			}elseif($_SESSION['language'] == 'MY'){
				$nmmenuu = $menunya['caption3'];
			}
			echo"
			<table cellspacing=1 border=0 class=sortable cellpadding=5 width=100%>
			<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['nopengajuan']."</th>
				<th align=center style=width:5%>".$_SESSION['lang']['tanggal']."<br>".$_SESSION['lang']['pengajuan']."</th>
				<th align=center style=width:5%>".$_SESSION['lang']['tanggalmasuk']."</th>
				<th align=center style=width:5%>".$_SESSION['lang']['tanggalkeluar']."</th>    
				<th align=center>".$_SESSION['lang']['jenisvch']."</th>    
				<th align=center>".$_SESSION['lang']['kodevhc']."</th>
				<th align=center style=width:60px>".$_SESSION['lang']['downtime']."</th>
				<th align=center>" . $_SESSION['lang']['createby'] . "</th>
				<th align=center style=width:8.9%>" . $nmmenuu."<br>".$_SESSION['lang']['status']."</th>
				<th align=center colspan=5>".$_SESSION['lang']['action']."</th>    
			</tr>
			</thead>
			<tbody>
			";/* 
				<th align=center>" . $nmmenu."<br>".$_SESSION['lang']['status']."</th> */
			
			$sch = "";
			if($schTran!='')
			{
				$sch.=" and nopengajuan like '%".$schTran."%' ";
			}
			if($schTgl!='')
			{
				$sch.=" and tglmasuk='".$schTgl."'";
			}
			if($schTglPengajuan!='')
			{
				$sch.=" and tanggalpengajuan = '".$schTglPengajuan."' ";
			}

			// $arrunit=getOrgDetail(2);

			#= Fix Bug
			#= Jika detail akses tidak terdaftar
			#= Buang langsung ke BRSC
			// if(count($arrunit)<=0) {
			// 	$arrunit = "'BRSC'";
			// }

			$limit=20;
			$page=0;
			if(isset($_POST['page'])){
				$page=$_POST['page'];
				if($page<0)
					$page=0;
			}
			$offset=$page*$limit;
			$maxdisplay=($page*$limit);
			$colspan=16;
			
			$str="select count(*) as jmlhrow from ".$dbname.".vhc_pengajuanservice where 1=1 ".$sch. " and left(kodeorg,4) in (".getOrgDetail(2).")  order by `nopengajuan` desc";
			$res=fetchdata($str);
			$jlhbrs= $res[0]['jmlhrow'];
			
			if($jlhbrs <= 0){
				$tab.="<tr class=rowcontent><td align=center colspan='".$colspan."'>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}else{
				$str="select * from ".$dbname.".vhc_pengajuanservice where 1=1 ".$sch."  and left(kodeorg,4) in (".getOrgDetail(2).")  order by `tanggalpengajuan` desc limit ".$offset.",".$limit."";
				$res=fetchdata($str);
				foreach($res as $val){
					$no+=1;
					
					$tab.="<tr class=rowcontent id=tr_$no>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$val['nopengajuan']."</td>";
					$tab.="<td align=center>".tanggalnormal($val['tanggalpengajuan'])."</td>";
					$tab.="<td align=center>".tanggalnormal($val['tglmasuk'])."</td>";
					$tab.="<td align=center>".tanggalnormal($val['tglkeluar'])."</td>";
					$tab.="<td align=left>".$jenisVhc[$val['kodevhc']]." - ".$nmjenis[$jenisVhc[$val['kodevhc']]]."</td>";
					$tab.="<td align=left>".$val['kodevhc']." - ".getVhc($val['kodevhc'],'detailvhc')."</td>";
					$tab.="<td align=center>".$val['downtimejam']." ".$_SESSION['lang']['jam']."</td>";
					$tab.="<td align=center>".getNamaKaryawan($val['createdby'])."</td>";

					$sHD = selectQuery($dbname,"vhc_penggantianht","notransaksi,posting,statuspersetujuan", "nopengajuan = '".$val['nopengajuan']."'");
					$qHD = fetchData($sHD)[0];
					// $tab.="<td align=center>";
					// 	if(count(fetchData($sHD))>0){
					// 		$tab.="<a href=\"javascript:do_load('vhc_service')\" >".$qHD['notransaksi']."</a><br><br>
					// 			<u>".strtoupper($_SESSION['lang']['approval_status'])."</u> :<br>
					// 			<b>".$arrapprv[$qHD['statuspersetujuan']]."</b><br>
					// 			<u>".strtoupper($_SESSION['lang']['status'])." ".strtoupper($_SESSION['lang']['posting'])."</u> :<br>
					// 			<b>".$arrpost[$qHD['posting']]."</b>";
					// 	}else if($val['statuspersetujuan'] == 1 && count(fetchData($sHD))<1){
					// 		$tab.="<b>".$_SESSION['lang']['belumrealisasi']."</b>";
					// 	}
					// $tab.="</td>";

					if($val<1){
						$stt="<label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('".$val['nopp']."',event)\">".$_SESSION['lang']['disetujui']."</label>";
					}else{
						if($val['close']=='3'){
							$stt="<label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('".$val['nopp']."',event)\">".$_SESSION['lang']['ditolak']."</label>";
						}else{
							$stt="<label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('".$val['nopp']."',event)\">".$_SESSION['lang']['disetujui']."</label>";
						}
					}
					if($val['statuspersetujuan']==0 || $val['statuspersetujuan'] == 3){
						$tab.="<td align=center>
								<label>".$_SESSION['lang']['belumposting']."</label>
								</td>";
						// $tab.="<td align=center>
						// 			<img src=images/skyblue/submit.jpg class=zImgBtn class=zImgBtn height='30'  title='Ajukan ?' 
						// onclick=\"form_ajukan('".$val['nopengajuan']."');\" ></td>";
						
                        $tab.="<td align=center width=25px>
								<img src=images/skyblue/posting.png class=zImgBtn height='30'  title='Posting' onclick=\"posting_data('" . $val['nopengajuan'] . "','".$val['kodevhc']."','".$val['tanggal']."');\">
								</td>";
						$tab.="<td align=center>
							<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('".$val['kodeorg']."','".$val['nopengajuan']."','".$kdTraksiDt[$val['kodevhc']]."','".$val['kodevhc']."','".tanggalnormal($val['tglmasuk'])."','".tanggalnormal($val['tglkeluar'])."','".$val['downtimejam']."','".$val['kmmasuk']."','".$val['kmkeluar']."','".$val['tipeperbaikan']."','".str_replace('####','\n',$val['kerusakan'])."','".str_replace('####','\n',$val['alasan'])."','".tanggalnormal($val['tanggalpengajuan'])."','".$val['karyawanidpemohon']."');\">
						</td>";
						$tab.="<td align=center>
							<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deleteHead('".$val['nopengajuan']."');\" >
						</td>";
						$tab.="<td align=center>
							<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('".$val['nopengajuan']."');\">
						</td>";
						$tab.="<td align=center>
							<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"printpdf('".$val['nopengajuan']."','".$val['kodevhc']."');\">
						</td>";
					}elseif($val['statuspersetujuan'] == 1){
						$tab.="<td align=center><b>".$_SESSION['lang']['post']."</b></td>";
						// if($qHD['posting'] == '' && $val['statuspersetujuan'] != 99){
							// $tab.="<td align=center  colspan=3>
							// 			<img src=\"images/icons/book_previous.png\" class=\"zImgBtn\" height=\"30\" title=\"Close Pengajuan ???\" onclick=\"tutuppengajuan('".$val['nopengajuan']."');\">
							// 		</td>";
						// }else{
							// $tab.="<td></td>";
							// $tab.="<td></td>";
							// $tab.="<td></td>";
						// }
						$tab.="<td align=right colspan=4>
							<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('".$val['nopengajuan']."');\">
						</td>";
						$tab.="<td align=center>
							<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"printpdf('".$val['nopengajuan']."','".$val['kodevhc']."');\">
						</td>";
					}elseif($val['statuspersetujuan'] == 2){
						$tab.="<td align=center>".$_SESSION['lang']['ditolak']."</td>";
						// $tab.="<td></td>";
						// $tab.="<td></td>";
						// $tab.="<td></td>";
						$tab.="<td align=right colspan=4>
							<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('".$val['nopengajuan']."');\">
						</td>";
						$tab.="<td align=center>
							<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"printpdf('".$val['nopengajuan']."','".$val['kodevhc']."');\">
						</td>";
					}else if($qHD['posting'] == '' && $val['statuspersetujuan'] == 99){
						$tab.="<td align=center><b>Data sudah di close</b></td>";
						// $tab.="<td></td>";
						// $tab.="<td></td>";
						// $tab.="<td></td>";
						$tab.="<td align=right colspan=4>
							<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('".$val['nopengajuan']."');\">
						</td>";
						$tab.="<td align=center>
							<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"printpdf('".$val['nopengajuan']."','".$val['kodevhc']."');\">
						</td>";
					}else{
						$tab.="<td align=center>Proses Persetujuan</td>";
						// $tab.="<td></td>";
						// $tab.="<td></td>";
						// $tab.="<td></td>";
						$tab.="<td align=right colspan=4>
							<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('".$val['nopengajuan']."');\">
						</td>";
						$tab.="<td align=center>
							<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"printpdf('".$val['nopengajuan']."','".$val['kodevhc']."');\">
						</td>";
					}
					
					$tab.="</tr>";
				}
				$tab.="<tfoot>".createpaging($jlhbrs,$limit,$page,$colspan,'loadData','getPage')."</tfoot>";
			}
			$tab.="</table>";
			
			echo $tab;
		break;
		#karyawan
		case 'saveKaryawan':
			try{
				$owlPDO->beginTransaction();
				
				$str="select count(nopengajuan) as jlhitem from ".$dbname.".vhc_pengajuanservicedt_karyawan where nopengajuan='".$trans_no."' and karyawanid='".$karyawan."'";
				$res=fetchdata($str);
				$jlhitem=$res[0]['jlhitem'];
				
				if($jlhitem > 0){
					throw new PDOException("Nama karyawan sudah pernah diinput.");
				}
				
				$str="insert into ".$dbname.".vhc_pengajuanservicedt_karyawan (`nopengajuan`,`karyawanid`,`updatedby`,`updatedtime`) values ('".$trans_no."','".$karyawan."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
				$owlPDO->exec($str);
				
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Warning, " . addslashes($e->getMessage());
				die();
			}
		break;
		case 'deleteKaryawan':
			$iDelPekerjaan="delete from ".$dbname.".vhc_pengajuanservicedt_karyawan where nopengajuan='".$trans_no."' 
				and karyawanid='".$karyawan."' ";
			try{$owlPDO->exec($iDelPekerjaan); }catch (PDOException $e) {print " Gagal  delete  karyawan!: " . $e->getMessage() . "\n"; die(); }		
		break;
		case 'loadDetailKaryawan':
			$tab="<table cellpadding=3 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader>
			<td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>".$_SESSION['lang']['nik']."</td>
			<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
			<td align=center>".$_SESSION['lang']['action']."</td></tr></thead><tbody>";
			$no=0;
			$iListKaryawan="select * from ".$dbname.".vhc_pengajuanservicedt_karyawan where nopengajuan='".$trans_no."' ";
			if(count(fetchData($iListKaryawan))>0){
				$res=$owlPDO->query($iListKaryawan) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($dListKaryawan=$res->fetch())
				{
					$whKar="karyawanid='".$dListKaryawan['karyawanid']."'";
					$no+=1;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td align=center>".getNik($dListKaryawan['karyawanid'])."</td>";
					$tab.="<td align=left>".getNamaKaryawan($dListKaryawan['karyawanid'])."</td>";
					$tab.="<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deleteKaryawan('".$dListKaryawan['nopengajuan']."','".$dListKaryawan['karyawanid']."');\" ></td>";
				}
			}else{
				$tab.="<tr class=rowcontent><td colspan=4 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
			}
				
			$tab.="</tbody></table>";
			echo $tab;
		break;
		case 'backloadDetailBarang':
			$no=0;
			$str="select * from ".$dbname.".vhc_pengajuanservicedt_pengembalian where nopengajuan='".$trans_no."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=center>".$bar['kodebarang']."</td>";
				$tab.="<td align=left>".getNamaBrg($bar['kodebarang'])."</td>";
				$tab.="<td align=center>".getSatBrg($bar['kodebarang'])."</td>";
				$tab.="<td align=right>".$bar['jumlah']."</td>";
				$tab.="<td align=left>".$bar['keterangan']."</td>";
				$tab.="<td align=center>
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"backdeleteBarang('".$bar['nopengajuan']."','".$bar['kodebarang']."');\" >
				</td>";
			}
			$tab.="</table>";
			
			$tabfile="<table class='sortable' cellspacing='1' cellpadding=3 border='0'>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' colspan=2>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>";
			
			echo $tab."####".$tabfile;
		break;
		case 'backsaveBarang':
			if($kodeBarangback==''|| $jumlahBarang==''){
				echo "warning: Nama Barang dan jumlah wajib diisi !";
				exit ();
			}
			
			$str="insert into ".$dbname.".vhc_pengajuanservicedt_pengembalian (`nopengajuan`,`jumlah`,`keterangan`,`kodebarang`,`updatedby`,`updatedtime`) values ('".$trans_no."','".$jumlahBarang."','".strtoupper($keteranganBarang)."','".$kodeBarangback."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
			try
			{
				$owlPDO->exec($str); 
			}
			catch(PDOException $e)
			{
				print " Gagal insert material!: " . $e->getMessage() . "\n"; die(); 
			}
		break;
		case 'backdeleteBarang':
			echo$str="delete from ".$dbname.".vhc_pengajuanservicedt_pengembalian where nopengajuan='".$trans_no."' and kodebarang='".$kodeBarangback."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal delete barang !: " . $e->getMessage() . "\n"; die(); }		
		break;
		case'getws':
			$optOrgTr=$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' 
						and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi asc";
			
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$optOrg.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
			
			$svhc23="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI'  
							order by namaorganisasi asc"; //echo $svhc;
			$res=$owlPDO->query($svhc23) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($rvhc23=$res->fetch())
			{
				$optOrgTr.="<option value='".$rvhc23['kodeorganisasi']."'>".$rvhc23['kodeorganisasi']." - ".$rvhc23['namaorganisasi']."</option>";
			}
			
			if($external=='external'){
				$optOrg=$optOrg;
			}else{
				$optOrg=$optOrgTr;
			}
			echo $optOrg;
		break;
		case'html':
			$str="select * from ".$dbname.".vhc_pengajuanservice where nopengajuan='".$trans_no."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$stpos=$bar['posting'];
			$barkd=$bar['kodevhc'];

			$nmvhcnya ='';
			if($nmvhc[$bar['kodevhc']] != ''){
				$nmvhcnya = " - ".$nmvhc[$bar['kodevhc']];
				
			}
			$tab="<table class='sortable' cellspacing=1 cellpadding=3 border=0 cellpadding=5>
					<tr class=rowcontent>
						<td>".$_SESSION['lang']['nopengajuan']."</td>
						<td>:</td>
						<td colspan=4><b>".$trans_no."</b></td>
						
						<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['pengajuan']."</td>
						<td>:</td>
						<td colspan=4>".tanggalnormal($bar['tanggalpengajuan'])."</td>
					</tr>
					<tr class=rowcontent>
						<td>".$_SESSION['lang']['workshop']."</td>
						<td>:</td>
						<td colspan=4>".$bar['kodeorg']." - ".getNamaOrg($bar['kodeorg'])."</td>
					
						<td> ".$_SESSION['lang']['kodetraksi']."</td>
						<td>:</td>
						<td colspan=4 >".$kdTraksiDt[$bar['kodevhc']]." - ".getNamaOrg($kdTraksiDt[$bar['kodevhc']])."</td>
					</tr>
				
					<tr class=rowcontent>     
						
						<td>".$_SESSION['lang']['kodevhc']."</td>
						<td>:</td>
						<td colspan=4 >".$bar['kodevhc']."".$nmvhcnya."</td>

						<td>".$_SESSION['lang']['pemohon']."</td>
						<td>:</td>
						<td colspan=4>".getNamaKaryawan($bar['karyawanidpemohon'])."</td>
					</tr>
					
					<tr class=rowcontent>
						<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['masuk']."</td>
						<td>:</td>
						<td>".tanggalnormal($bar['tglmasuk'])."</td>
					
						<td>Est. ".$_SESSION['lang']['keluar']."</td>
						<td>:</td>
						<td>".tanggalnormal($bar['tglkeluar'])."</td>
						<td>".$_SESSION['lang']['downtime']."</td>
						<td>:</td>
						<td colspan=4>".$bar['downtimejam']." ".$_SESSION['lang']['jam']."</td>
					</tr>
					
					<tr class=rowcontent>
						<td>KM / HM ".$_SESSION['lang']['masuk']."</td>
						<td>:</td>
						<td >".$bar['kmmasuk']."</td>
					
						<td>Est. ".$_SESSION['lang']['keluar']."</td>
						<td>:</td>
						<td >".$bar['kmkeluar']."</td>
					
						<td valign=top>".$_SESSION['lang']['tipeperbaikan']."</td>
						<td valign=top>:</td>
						<td colspan=4>".$bar['tipeperbaikan']."</td>
					</tr>

					<tr class=rowcontent>
						<td valign=top> ".$_SESSION['lang']['descDamage']."</td>
						<td valign=top>:</td>
						<td colspan=10>".str_replace('####', '<br>',$bar['kerusakan'])."</td>
					</tr>        

					<tr class=rowcontent>
						<td valign=top>".$_SESSION['lang']['alasan']."</td>
						<td valign=top>:</td>
						<td colspan=10>".str_replace('####', '<br>', $bar['alasan'])."</td>
					</tr>

					</table>
					<br>";
			$tab.= "<table class='sortable' cellspacing='1' cellpadding=3 border='0'  cellpadding=5>
					<thead>
						<tr class=rowheader>
							<td colspan=5 align=center>".$_SESSION['lang']['daftarbarang']." </td>
						</tr>
						<tr class=rowheader>
							<td align=center width=30px>".$_SESSION['lang']['nourut']." </td>
							<td align=center>".$_SESSION['lang']['namabarang']." </td>
							<td align=center width=50px>".$_SESSION['lang']['satuan']." </td>
							<td align=center width=50px>".$_SESSION['lang']['jumlah']." </td>
							<td align=center>".$_SESSION['lang']['keterangan']." </td>
							
						</tr>
					</thead>
					<tbody>";
					$str="select * from ".$dbname.".vhc_pengajuanservicedt where nopengajuan='".$trans_no."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$row=$res->rowCount();
					$res->setFetchMode(PDO::FETCH_ASSOC);
					if($row==0){
						$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
					}else{
						$no=0;
						while($bar=$res->fetch()){
							$no+=1;
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=center>".$no."</td>
								<td align=left>".$nmBrg[$bar['kodebarang']]."</td>
								<td align=left>".getSatBrg($bar['kodebarang'])."</td>
								<td align=right>".number_format($bar['jumlah'])."</td>
								<td align=left>".$bar['keterangan']."</td>
								</tr>";
						}
					}
				$tab.="</tbody>
					</table>
						<br>
						<table class='sortable' cellspacing='1' cellpadding=3 border='0'  cellpadding=5>
							<thead>
							<tr class=rowheader>
								<td colspan=3 align=center>".$_SESSION['lang']['karyawan']." </td>
							</tr>
							<tr class=rowheader>
								<td align=center width=30px>".$_SESSION['lang']['nourut']." </td>
								<td align=center width=100px>".$_SESSION['lang']['nik']." </td>
								<td align=center>".$_SESSION['lang']['namakaryawan']." </td>
							</tr>
							</thead>
							<tbody>";
							$str="select * from ".$dbname.".vhc_pengajuanservicedt_karyawan where nopengajuan='".$trans_no."'";
							$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$row=$res->rowCount();
							$res->setFetchMode(PDO::FETCH_ASSOC);
							if($row==0){
								$tab.="<tr class=rowcontent><td colspan=3 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
							}else{
								$no=0;
								while($bar=$res->fetch()){
									$no+=1;
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=center>".$no."</td>
										<td align=left>".getNik($bar['karyawanid'])."</td>
										<td align=left>".getNamaKaryawan($bar['karyawanid'])."</td>
										";
									$tab.="</tr>";
								}
							}
					$tab.="</tbody>
						</table>
						<br>
						<table class='sortable' cellspacing='1' cellpadding=3 border='0'  cellpadding=5>
							<thead>
							<tr class=rowheader>
								<td colspan=6 align=center>".$_SESSION['lang']['bulkreturn']." </td>
							</tr>
							<tr class=rowheader>
								<td align=center width=30px>".$_SESSION['lang']['nourut']." </td>
								<td align=center>".$_SESSION['lang']['kodebarang']." </td>
								<td align=center>".$_SESSION['lang']['namabarang']." </td>
								<td align=center width=50px>".$_SESSION['lang']['satuan']." </td>
								<td align=center width=50px>".$_SESSION['lang']['jumlah']." </td>
								<td align=center>".$_SESSION['lang']['keterangan']." </td>
								
							</tr>
							</thead>
							<tbody>";
							$str="select * from ".$dbname.".vhc_pengajuanservicedt_pengembalian where nopengajuan='".$trans_no."'";
							$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$row=$res->rowCount();
							$res->setFetchMode(PDO::FETCH_ASSOC);
							if($row==0){
								$tab.="<tr class=rowcontent><td colspan=6 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
							}else{
								$no=0;
								while($bar=$res->fetch()){
									$no+=1;
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=center>".$no."</td>
										<td align=left>".$bar['kodebarang']."</td>
										<td align=left>".$nmBrg[$bar['kodebarang']]."</td>
										<td align=left>".getSatBrg($bar['kodebarang'])."</td>
										<td align=right>".number_format($bar['jumlah'])."</td>
										<td align=left>".$bar['keterangan']."</td>";
								$tab.="</tr>";
								}
							}
					$tab.="</tbody>
						</table>";
		
				$tab.="</tr>
						</tbody>";
				
				$tab.="</table><br>";
				// $tab.="<table class='sortable' cellspacing='1' cellpadding=3 border='0' cellpadding=5>
				// 			<thead>
				// 			<tr class=rowheader>
				// 				<td align='center' width=30px>No.</td>
				// 				<td align='center' width=50px>File Type</td>
				// 				<td align='center'>Filename</td>
				// 				<td align='center' colspan=2>Action</td>
				// 			</tr>
				// 			</thead>
				// 			<tbody id='loadfilesview'>
				// 			</tbody>
				// 		</table><br><br>";
			echo $tab;
		break;			
		case 'showupload':
			$tab="";
			
			$tab.="<table cellspacing='1' border='0' id='uploadpopup'>
				<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td>
						<label id='notransaksiupload' style='font-weight:bold'>".$trans_no."</label>
					</td>
				</tr>
				<tr>
					<td>Filename</td>
					<td>:</td>
					<td>
						<input type='file' name='upload' id='upload' class=mybutton>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=\"submitfile()\">Submit</button>
					</td>
				</tr>
			</table>
			<p />";
			
			$tab.="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' cellpadding=3 border='0' >
					<thead>
					<tr class=rowheader>
						<td align='center'>No.</td>
						<td align='center'>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' colspan=2>Action</td>
					</tr>
					</thead>
					<tbody id='listfiles'>
					</tbody>
				</table>
			</fieldset> ";
			
			echo $tab;
		break;
		case 'submitfile':
			$str="select * from ".$dbname.".vhc_pengajuanservice where nopengajuan = '".$trans_no."'";
			$resv=fetchData($str);
			if(count($resv)==0){
				exit('Error : Isikan detail transaksi terlebih dahulu.');
			}
			
			$tgl = date("YmdHis");
			$data = $_POST;
			
			if($data['fileupload']!=''){
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
					$filename = $newfilename."_".$tgl."".$filetype;
					#$file_tmpname = $_FILES['file']['tmp_name'];
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
					
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
						if($_FILES['file']['size'] <= 250000){
							$str = "insert into ".$dbname.".listfilepenggantian values ('','".$data['trans_no']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
							try{
								$owlPDO->exec($str);
								if (!file_exists($path)) {
								mkdir($path, 0777, true);
							}
								file_put_contents($path.$filename,$file_tmpname);
								# move_uploaded_file($file_tmpname,"fileupload/vhc_service/$filename");
							}catch(PDOException $e){
								echo " Gagal," . addslashes($e->getMessage());
							}
						}else{
							exit("warning : Ukuran file upload maksimal 250kb");
						}
					}else{
						exit("Warning : Format file upload harus .jpg atau .jpeg");
					}
				}
			}
		break;
		case 'loadfiles':
			$no = 0;
			$tab = "";
			$str="select * from ".$dbname.".vhc_pengajuanservice where nopengajuan = '".$trans_no."'";
			$resv=fetchData($str);
			foreach($resv as $bar => $barv){
				$posting = $barv['posting'];	
			}
			
			$str="select * from ".$dbname.".listfilepenggantian where notransaksi = '".$trans_no."' and status='1'";
			$res=fetchData($str);
			if(empty($res)){
				$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
			}else{
				foreach($res as $key=>$val){
					$no++;
					$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
						
					if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=zImgBtn title='JPG'></a>
						</td>";
					}elseif($val['formaticon']=='.png'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=zImgBtn  title='PNG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.pdf'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=zImgBtn  title='PDF'></a>
						</td>";
					}elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=zImgBtn  title='xls'></a>
						</td>";
					}elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=zImgBtn  title='doc'></a>
						</td>";
					}else{
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=zImgBtn  title='jpg'></a>
						</td>";
					}
					
					$tab.="<td style='text-align:left'>".$val['namafile']."</td>
						<td align=center>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>
						</td>";
					if($posting==0){
						$tab.="<td>
							<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('".$trans_no."','".$val['namafile']."');\" >
						</td>";
					}else{
						$tab.="<td></td>";
					}
					$tab.="</tr>";
				}	
			}
			echo $tab;
		break;
		case 'deletefile':
			$str="delete from ".$dbname.".listfilepenggantian where notransaksi='".$trans_no."' and namafile='".$namafile."'";
			try
			{
				$owlPDO->exec($str);
				$path = $path.$namafile;
				unlink($path);
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
		break;
		case 'postingData':	
			$scek="select * from ".$dbname.".vhc_pengajuanservice where nopengajuan='".$trans_no."'";
			$res=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rcek=$res->fetch();
			// if($rcek['external']=='external'){
			// 	$str="select * from ".$dbname.".setup_approval where kodeunit = '".substr($rcek['kodeorg'],0,4)."' and jenispersetujuan='SVC' and level='1'";
			// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// 	$row=$res->rowCount();
			// 	$res->setFetchMode(PDO::FETCH_ASSOC);
			// 	$aju=$res->fetch();
			// 	if($row<1){
			// 		exit('Error : Silahkan tambahkan nama penyetuju melalui menu : Setup - Persetujuan');
			// 	}
			// }
			
			$scek="select * from ".$dbname.".vhc_pengajuanservice where tanggalpengajuan < '".$rcek['tanggal']."' and kodeorg='".$rcek['kodeorg']."' and statuspersetujuan='0'";
			$res=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$row=$res->rowCount();
			if($row!=0){
				exit('Error : Silahkan posting dari tanggal terkecil.');
			}
			
			// $sCekv="select kodejabatan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
			// $res=$owlPDO->query($sCekv) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $rCekv=$res->fetch();
			// if(!in_array($_SESSION['empl']['kodejabatan'],$jab)){
			// 		echo"warning : Anda tidak memiliki autorisasi atau No Transaksi ini sudah di posting!!";
			// 		exit();
			// }
			
			$sudPost="update ".$dbname.".vhc_pengajuanservice set statuspersetujuan='1',updatedby='".$usr_id."',updatedtime='".date('Y-m-d H:i:s')."' where nopengajuan='".$trans_no."' and kodevhc='".$vhc_code."'";
			try{$owlPDO->exec($sudPost); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			
			$str="select * from ".$dbname.".vhc_pengajuanservicedt_pengembalian where nopengajuan='".$trans_no."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$row=$res->rowCount();
			$res->setFetchMode(PDO::FETCH_ASSOC);
			if($row>0){
				while($bar=$res->fetch()){
					$jam=date("H:i:s");
					$tgljam=$rcek['tanggal']." ".$jam;
					$ins="insert into ".$dbname.".vhc_stokbarangbekas (`kodeorg`,`notransaksi`,`tanggal`,`tanggaljam`,`kodebarang`,`masuk`,`updateby`,`keterangan`) values 
					('".$rcek['kodeorg']."','".$rcek['notransaksi']."','".$rcek['tanggal']."','".$tgljam."','".$bar['kodebarang']."','".$bar['jumlah']."','".$_SESSION['standard']['userid']."','".$bar['keterangan']."')";
					try{$owlPDO->exec($ins);}catch (PDOException $e){print " Gagal !: ".$e->getMessage()."\n"; die(); }
				}
			}
		break;
		case 'form_ajukan':
			$jenispersetujuanx 	= 'WOSVC';
			$pecahnotrans		= explode('/', $trans_no);
			$kodeorg 			= $pecahnotrans[1];
			$nmapprval        	=makeOption($dbname,'setup_jenisapproval','jenis,nama');

			$tab.="<table cellspacing=1 border=0 class=sortable cellpadding=5 align=center>";
			$tab.="<h3 style='padding-left:40px'><b>Persetujuan ".$nmapprval[$jenispersetujuanx]."<br></b></h3>";
			$tab.="<h4 style='padding-left:40px'><b> Unit : ".$kodeorg." - ".getNamaOrg($kodeorg)."</b></h4>";
			$tab.="<tr class=rowcontent >";
				$tab.="<td>No Pengajuan</td>";
				$tab.="<td width=5px>:</td>";
				$tab.="<td id=notran_aju>".$trans_no."</td>";
			$tab.="</tr>";

			##CEK PER DEPARTEMEN
			$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg."' and jenispersetujuan='".$jenispersetujuanx."' and departemen='".$departemen."'";
			$res=fetchdata($str);
			$perdepartemen=$res[0]['kodeunit'];
			$where="";
			if($perdepartemen>0){
				$where.=" and departemen='".$departemen."'";
			}else{
				$where.=" and departemen=''";
			}

			## APPROVAL DINAMIS SESUAI SETUP##
			$optKryx	=array();
			$optKrylevel=array();

			$optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuanx."' and kodeunit='".$kodeorg."' and karyawaniduser='' ".$where." ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$whr		=" karyawanid='".$bar['karyawanid']."'";
				$optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
				
				$optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
				$optKrylevel[$bar['level']]=$bar['level'];

			}

			
			$jumlahlevel=count($optKrylevel);
			if($jumlahlevel>0)
			{
				for ($i=1; $i <= $jumlahlevel; $i++) { 
					$optKry='';
					foreach ($optKryx[$i] as $key2 => $val) {
						$optKry.=$val;
					}
					$tab .= "<tr class=rowcontent>
						<td>Approval ke-".$i."</td>
						<td width=5px>:</td>
						<td><select id=kepada".$i." style='width:200px;'>".$optKry."</select></td>
					</tr>";					
				}
			}
			else
			{			
				$jumlahlevel=1;
				$tab .= "<tr class=rowcontent>
					<td>Approval ke-1</td>
					<td width=5px>:</td>
					<td><select id=kepada1 style='width:200px;'></select></td>
				</tr>";
			}
				$tab .= "<tr class=rowcontent>
					<td hidden><input id=jenispersetujuanx style=display:none value=".$jenispersetujuanx."></td><td><input id=numrow style=display:none value=".$jumlahlevel."></td>
					<td align=left></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>
			</table>";

			echo $tab;
		break;
		case 'ajukan':
			$kepada 			= checkPostGet('kepada','');
			$jenispersetujuanx 	= checkPostGet('jenispersetujuanx','');

			if($kepada==''){
				throw new PDOException('Isikan nama penyetuju.');
			}
			try{
				$owlPDO->beginTransaction();
				//update flag menjadi 9
				$str2 = "update " . $dbname . ".vhc_pengajuanservice set statuspersetujuan='9' where nopengajuan = '" . $trans_no . "'";
				$owlPDO->exec($str2);
				//cek apakah sudah terdapat approval sebelum jika ada delete semua approval yang ada
				$sql="select * from ".$dbname.".approval where notransaksi='".$trans_no."' ";
				$hsl=fetchData($sql);
				if(count($hsl)>0){
					$string = "delete from " . $dbname . ".approval where notransaksi='" . $trans_no . "'";
					try{$owlPDO->exec($string);}catch (PDOException $e){echo "DB Header Error: " . addslashes($e->getMessage());}
				}
				//insert ke table approval
				$str='';
				$arrkepada=explode('###', $kepada);
				for ($i=0; $i < count($arrkepada); $i++) { 
					$str .= "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					values ('','".$trans_no."','".$jenispersetujuanx."','".($i+1)."','" . $arrkepada[$i]."','0','','','');";
				}
				$owlPDO->exec($str);
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
			}
		break;
		case 'tutuppengajuan':
			$sudPost="update ".$dbname.".vhc_pengajuanservice set statuspersetujuan='99',updatedby='".$usr_id."',updatedtime='".date('Y-m-d H:i:s')."' where nopengajuan='".$trans_no."'";
			try{$owlPDO->exec($sudPost); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		break;
		case 'deletefileall':
			$str="select * from ".$dbname.".listfilepenggantian where notransaksi='".$trans_no."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$pathx = $path.$bar['namafile'];
				unlink($pathx);
			}
			
			$str="delete from ".$dbname.".listfilepenggantian where notransaksi='".$trans_no."'";
			try
			{
				$owlPDO->exec($str);
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
		
		default;
	}


	function updateKmHm($kodevhc) {
		global $dbname;
		global $owlPDO;
		// Get KM/HM Akhir
		$qKm = selectQuery($dbname,'vhc_kmhmakhir_vw','*',"kodevhc='".$kodevhc."'");
		$resKm = fetchData($qKm);
		$kmhmAkhir = (empty($resKm))? 0: $resKm[0]['kmhmakhir'];
		
		$dataIns = array($kodevhc,$kmhmAkhir);
		$qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);
		try{
			$owlPDO->exec($qIns); 
		}catch (PDOException $e) {
			$dataUpd = array('kmhmakhir'=>$kmhmAkhir);
			$qUpd = updateQuery($dbname,'vhc_kmhm_track',$dataUpd,"kodevhc='".$kodevhc."'");                        
			try{$owlPDO->exec($qUpd); }catch (PDOException $e) {
				print " Gagal update hmkm !: " . $e->getMessage() . "\n"; die(); 
			}
		}
	}