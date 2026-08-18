<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$str = "select *  from " . $dbname . ".organisasi";
$res = fetchdata($str);
foreach($res as $bar){
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
	$tporg[$bar['kodeorganisasi']]=$bar['tipe'];
	$nmpt[$bar['kodeorganisasi']]=$bar['induk'];
}

$str = "select *  from " . $dbname . ".sdm_ho_component";
$res = fetchdata($str);
foreach($res as $bar){
	$idcomp[$bar['id']]=$bar['name'];
	$plus[$bar['id']]=$bar['plus'];
}

$str = "select *  from " . $dbname . ".bgt_regional_assignment";
$res = fetchdata($str);
foreach($res as $bar){
	$nmreg[$bar['kodeunit']]=$bar['regional'];
}

$str = "select *  from " . $dbname . ".sdm_5jabatan";
$res = fetchdata($str);
foreach($res as $bar){
	$nmjab[$bar['kodejabatan']]=$bar['namajabatan'];
}
$str = "select *  from " . $dbname . ".sdm_5tipekaryawan";
$res = fetchdata($str);
foreach($res as $bar){
	$tipekar[$bar['id']]=$bar['tipe'];
}
$str = "select *  from " . $dbname . ".sdm_5departemen";
$res = fetchdata($str);
foreach($res as $bar){
	$nmdept[$bar['kode']]=$bar['nama'];
}
$str = "select *  from " . $dbname . ".sdm_5golongan";
$res = fetchdata($str);
foreach($res as $bar){
	$nmgol[$bar['kodegolongan']]=$bar['namagolongan'];
}


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


$datasort= array();

switch($method){
	case'sumber':
		$tab="<fieldset><legend>Info</legend><div>";
		switch($param['jenis']){
			case'payroll':
				$tab.="Sumber data :<li>Data gaji dengan beberapa komponen diambil dari bulan lalu.</li>";
				$tab.="<table class='sortable' cellspacing='1' cellpadding='1' border='0'>";
				$tab.="<tr class=rowcontent><td>";
				$tab.="Data diluar komponen payroll karyawan (tidak ditampilkan) :";

				$str   = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='KOMGJEXSLP'";
				$res   = fetchdata($str);
				$arrx  = explode(',', $res[0]['nilai']);
				for ($i=0; $i < count($arrx); $i++) {
					$tab.="<li>".$idcomp[$arrx[$i]]."</li>";
				}
				$tab.="</td><td>";
				$tab.="Data diambil dari bulan lalu :";
				$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
				$res = fetchdata($str);
				$arrx  = explode(',', $res[0]['nilai']);
				for ($i=0; $i < count($arrx); $i++) {
					$tab.="<li>".$idcomp[str_replace("'","",$arrx[$i])]."</li>";
				}
				$tab.="</td></tr>";
				$tab.="</table>";

			break;
			case'aktual':
				$tab.="Sumber data :<li>Data gaji aktual bulan ini.</li>
						<li>Jika ada data yg tidak muncul agar dilakukan Proses Penggajian ulang.</li>
						<li>Untuk menghitung jumlah TK pilih <b>Count Unique Values : KaryID</b></li>
						";
			break;
			case'detpayroll':
				$tab.="Sumber data :<li>Data yang di tampilkan bersumber dari SDM - Transaksi - Absensi.</li>
						<li>Data yang di tampilkan hanya untuk tipe karyawan <b>KHL/KHT</b>.</li>
						";
			break;
			case'source':
				$tab.="Sumber data :
						<li>Kebun - Transaksi - Buku Kegiatan Mandor.</li>
						<li>Kebun - Transaksi - Buku Kegiatan Mandor Panen Kg.</li>
						<li>Kebun - Transaksi - Buku Kegiatan Mandor Panen Jjg.</li>
						<li>Traksi - Transaksi - Pekerjaan.</li>
						<li>SDM - Transaksi - Absensi.</li>
						<li>SDM - Transaksi - Lembur.</li>
						";
			break;
			case'hkdankehadiran':
				$tab.="Sumber data :
						<li>Kebun - Transaksi - Buku Kegiatan Mandor.</li>
						<li>Kebun - Transaksi - Buku Kegiatan Mandor Panen.</li>
						<li>Traksi - Transaksi - Pekerjaan.</li>
						<li>SDM - Transaksi - Absensi.</li>
						";
			break;
			case'alokasi':
				$tab.="Sumber data :<li>Data yang di tampilkan bersumber dari Jurnal.</li>";
			break;
		}
		$tab.="<li style=color:blue;font-weight:bold;>Jika pilihan Jenis berbeda dengan pilihan sebelumnya, pastikan anda Reload Frame terlebih dahulu.</li>";
		$tab.="</div></fieldset>";

		// echo $tab;
	break;
	case'getkary':
		$wh="";
		if($param['kodeorg']!=''){
			$wh.=" and lokasitugas='".$param['kodeorg']."'";
		}else{
			$wh.=" and lokasitugas in (".getOrgDetail(2).")";
		}
		if($param['tipekaryawan']!=''){
			$wh.=" and tipekaryawan='".$param['tipekaryawan']."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$wh.=" and tipekaryawan='4'";
			}
		}
		$wh.=" and (tanggalkeluar='0000-00-00' or tanggalkeluar>'".date('Y-m-d')."')";
		$tab="<option value=''>&nbsp;</option>";
		$str = "select * from ".$dbname.".datakaryawan where 1=1 ".$wh." order by lokasitugas, namakaryawan";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$d=$val['lokasitugas'];
			if($d!=$n){
				$tab.="<optgroup label='".getNamaOrg($d)."'>";
			}
			$tab.="<option value=".$val['karyawanid'].">".$val['nik']." - ".$val['namakaryawan']."</option>";
			$n=$d;
			if($d!=$n){
				$tab.="</optgroup>";
			}
		}

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
	// echo $tab;
	break;
	case'getfromfav':
		$str = "select a.*, b.label, b.karyawanid, b.filter, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where b.id='".$param['fromfavorit']."' order by a.id asc";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$tab=$bar['filter'];
		}

		// echo"<pre>";
		// // print_r(json_decode($filter));
		// foreach(json_decode($filter) as $key => $val){
			// echo $key.$val."<br>";

		// }

		// exit("error");
		//echo $filter;
		//echo $tab;
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
		//echo $tab;
	break;

	case'favorit':
		try {
			$owlPDO->beginTransaction();
				if($param['sumber']=='keu_pivot'){
					$filter['pt']      = $param['pt'];
					$filter['regional']= $param['regional'];
					$filter['gudang']  = $param['kodeorg'];
					$filter['periode'] = $param['periode'];
					$filter['noakun']  = $param['noakun'];
					$filter['noakun2'] = $param['noakun2'];
					$filter['periode2']= $param['periode2'];
					$filter['tipe']    = $param['tipe'];
				}elseif($param['sumber']=='sdm_pivot'){
					$filter['kodeorg']     = $param['kodeorg'];
					$filter['periode']     = $param['periode'];
					$filter['periode2']    = $param['periode2'];
					$filter['tipe']        = $param['tipe'];
					$filter['tipekaryawan']= $param['tipekaryawan'];
				}else{
					$filter['kodeorg']= $param['kodeorg'];
					$filter['periode']= $param['periode'];
					$filter['tipe']   = $param['tipe'];
				}

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
	case'source':
		$whr=$wh=$whg=$whpres="";
		if($param['kodeorg']!=''){
			$whr.=" and kodeorg='".$param['kodeorg']."'";
			$wh.=" and lokasitugas='".$param['kodeorg']."'";
			$whkary.=" and lokasitugas='".$param['kodeorg']."'";
			$whlist.=" and lokasitugas='".$param['kodeorg']."'";
			$whg.=" and substr(kodeorg,1,4)='".$param['kodeorg']."'";

			$whpres.=" and substr(b.kodeorg,1,4)='".$param['kodeorg']."'";
		}else{
			$whr.=" and kodeorg in (".getOrgDetail(2).")";
			$wh.=" and lokasitugas in (".getOrgDetail(2).")";
			$whkary.=" and lokasitugas in (".getOrgDetail(2).")";
			$whlist.=" and lokasitugas in (".getOrgDetail(2).")";
			$whg.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";

			$whpres.=" and substr(b.kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		$rangebulan = month_inbetween($param['periode'],$param['periode2']);
		foreach($rangebulan as $bulan){
			$dtbulan[$bulan]=$bulan;
			$thn = substr($bulan,0,4);
			$dttahun[$thn]=$thn;
		}

		if($param['karyawanid']!=''){
			$wh.=" and karyawanid='".$param['karyawanid']."'";
			$whkary.=" and karyawanid='".$param['karyawanid']."'";
			$whlist.=" and karyawanid='".$param['karyawanid']."'";
			$whgj=" and karyawanid='".$param['karyawanid']."'";
		}
		foreach($dttahun as $tahun){
			$str = "select * from " . $dbname . ".sdm_5gajipokok where 1=1 and tahun='".$tahun."' ".$whgj." and idkomponen='1'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$gaji[$val['karyawanid']][$tahun]=$val['jumlah']/25;
			}
		}

		$whr.=" and periode in ('".implode("','",$dtbulan)."')";
		$wh.=" and periodegaji in ('".implode("','",$dtbulan)."')";
		#$whlist.=" and periodegaji in ('".implode("','",$dtbulan)."')";
		$whg.=" and periodegaji in ('".implode("','",$dtbulan)."')";
		$whpres.=" and substr(b.tanggal,1,7) in ('".implode("','",$dtbulan)."')";

		if($param['tipekaryawan']!=''){
			$whr.=" and nik in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 ".$wh." and version_type='B' and tipekaryawan='".$param['tipekaryawan']."')";

			$wh.=" and tipekaryawan='".$param['tipekaryawan']."'";
			$whkary.=" and tipekaryawan='".$param['tipekaryawan']."'";
			$whg.=" and tipekaryawan='".$param['tipekaryawan']."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$whr.=" and nik in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 ".$wh." and version_type='B' and tipekaryawan='4')";
				$wh.=" and tipekaryawan='4'";
				$whg.=" and tipekaryawan='4'";
				$whkary.=" and tipekaryawan='4'";
			}
		}

		$str = "select * from ".$dbname.".datakaryawan_hist where 1=1 and version_type='B' ".$wh."";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$listkary[$val['karyawanid']]['kodeorg']=$val['lokasitugas'];
		}

		$whkary.=" and (tanggalkeluar='0000-00-00' or tanggalkeluar>'".date('Y-m-d')."')";
		foreach($rangebulan as $bulan){
			$str = "select * from ".$dbname.".datakaryawan_hist where 1=1 and version_type='B' ".$whlist." and periodegaji='".$bulan."'";
			$res = fetchdata($str);
			if(empty($res)){
				$str = "select * from ".$dbname.".datakaryawan where 1=1 ".$whkary."";
				$res = fetchdata($str);
			}
			foreach ($res as $val) {
				$val['periodegaji']=$bulan;
				$kodeorganisasi[$val['karyawanid']][$val['periodegaji']]=$val['kodeorganisasi'];
				$lokasitugas[$val['karyawanid']][$val['periodegaji']]=$val['lokasitugas'];
				$subbagian[$val['karyawanid']][$val['periodegaji']]=$val['subbagian'];
				$kodejabatan[$val['karyawanid']][$val['periodegaji']]=$val['kodejabatan'];
				$tipekaryawan[$val['karyawanid']][$val['periodegaji']]=$val['tipekaryawan'];
				if($val['subbagian']==''){
					$subbagian[$val['karyawanid']][$val['periodegaji']]="UMUM / KANTOR ".$val['lokasitugas'];
				}
			}
		}

		$datae[]=array('PT Kary','Unit Kary','Divisi Kary','NIK','Nama Karyawan','Jabatan','Tipe Kary','No Transaksi','Periode','Tanggal','Noakun','Nama Akun','Keterangan','Blok','Kode Kegiatan','Nama Kegiatan','Satuan','Komponen','Jumlah');

		$numb=array(22);

		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row     = array("Unit Kary","Divisi Kary");
		$col     = array("Komponen");
		$value   = array('Jumlah');
		$datasort= array("Prestasi","Hadir","HK","Upah","Premi","Jam Aktual","Lembur");

		if($param['karyawanid']!=''){
			$whnik=" and karyawanid='".$param['karyawanid']."'";
		}
		$sql =  "SELECT * FROM ".$dbname.".sdm_lemburdt b WHERE 1=1 ".$whpres." ".$whnik."";
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			$tip = getNamaOrg(substr($val['kodeorg'],0,4),'tipe');
			$param['tipeorganisasi'] = getNamaOrg($subbagian[$val['karyawanid']][$periode],'tipe');

			if($tip=='PABRIK'){
				if($param['tipeorganisasi']=='TRAKSI'){
					$grouppremi = "VHCG1";
				} else if($param['tipeorganisasi']=='WORKSHOP'){
					$grouppremi = "WSG1";
				} else if($param['tipeorganisasi']=='STATION'){
					$grouppremi = "PKS02";
				} else if($param['tipeorganisasi']=='SIPIL'){
					$grouppremi = "SIPL1";
				} else {
					$grouppremi  = "KBNB1";
				}
			} else if ($tip=='KEBUN'){
				if($param['tipeorganisasi']=='TRAKSI'){
					$grouppremi = "VHCG1";
				} else if($param['tipeorganisasi']=='WORKSHOP'){
					$grouppremi = "WSG1";
				} else if($param['tipeorganisasi']=='SIPIL'){
					$grouppremi = "SIPL1";
				} else {
					$grouppremi  = "KBNB1";
				}
			} else if ($tip=='BULKING'){
				if($param['tipeorganisasi']=='TRAKSI'){
					$grouppremi = "VHCG1";
				} else if($param['tipeorganisasi']=='WORKSHOP'){
					$grouppremi = "WSG1";
				} else if($param['tipeorganisasi']=='SIPIL'){
					$grouppremi = "SIPL1";
				} else {
					$grouppremi = "BLK02";
				}
			} else if ($tip=='RND'){
				$grouppremi = "RNDB1";
			}  else if ($tip=='KANWIL'){
				$grouppremi = "GJHO2";
			} else if ($tip=='HOLDING'){
				$grouppremi = "GJHO2";
			} else if ($tip=='TC'){
				$grouppremi = "RNDB1";
			}
			$query = "select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$grouppremi."' limit 1";
			$resque = fetchdata($query);
			foreach ($resque as $valque) {
				$noakunpremi=$valque['noakundebet'];
			}

			$val['nik'] = $val['karyawanid'];
			if($listkary[$val['nik']]['kodeorg']!=''){
				if($val['noakun']!=''){
					$noakunpremi=$val['noakun'];
				}
				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					'',
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					$noakunpremi,
					getNamaAkun($noakunpremi),
					'SDM Lembur',
					'',
					'',
					$val['ket']." (".$val['jammulai']." - ".$val['jamselesai'].")",
					'Jam',
					'Jam Aktual',
					$val['jamaktual']
				);
				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					'',
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					$noakunpremi,
					getNamaAkun($noakunpremi),
					'SDM Lembur',
					'',
					'',
					$val['ket']." (".$val['jammulai']." - ".$val['jamselesai'].")",
					'Jam',
					'Lembur',
					$val['uangkelebihanjam']
				);
			}

			$kehadiran[$val['nik']][$val['tanggal']]=1;
		}
		// echo"<pre>";
		// print_r($data);
		// print_r($jumlahvhc);
		// echo"</pre>";
		// exit("error".$noakunpremi);
		// continue;

		$sql =  "SELECT * FROM ".$dbname.".sdm_absensidt b WHERE 1=1 ".$whpres." ".$whnik." ";
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			$tip = getNamaOrg(substr($val['kodeorg'],0,4),'tipe');
			$param['tipeorganisasi'] = getNamaOrg($subbagian[$val['karyawanid']][$periode],'tipe');

			if($tip=='PABRIK'){
				if($param['tipeorganisasi']=='TRAKSI'){
					$groupupah  = "VHCG0";
					$grouppremi = "VHCG1";
				} else if($param['tipeorganisasi']=='WORKSHOP'){
					$groupupah  = "WSG0";
					$grouppremi = "WSG1";
				} else if($param['tipeorganisasi']=='STATION'){
					$groupupah  = "PKS01";
					$grouppremi = "PKS02";
				} else if($param['tipeorganisasi']=='SIPIL'){
					$groupupah  = "SIPL1";
					$grouppremi = "SIPL1";
				} else {
					$groupupah  = "KBNB0";
					$grouppremi  = "KBNB1";
				}
			} else if ($tip=='KEBUN'){
				if($param['tipeorganisasi']=='TRAKSI'){
					$groupupah  = "VHCG0";
					$grouppremi = "VHCG1";
				} else if($param['tipeorganisasi']=='WORKSHOP'){
					$groupupah  = "WSG0";
					$grouppremi = "WSG1";
				} else if($param['tipeorganisasi']=='SIPIL'){
					$groupupah  = "SIPL1";
					$grouppremi = "SIPL1";
				} else {
					$groupupah  = "KBNB0";
					$grouppremi  = "KBNB1";
				}
			} else if ($tip=='BULKING'){
				if($param['tipeorganisasi']=='TRAKSI'){
					$groupupah  = "VHCG0";
					$grouppremi = "VHCG1";
				} else if($param['tipeorganisasi']=='WORKSHOP'){
					$groupupah  = "WSG0";
					$grouppremi = "WSG1";
				} else if($param['tipeorganisasi']=='SIPIL'){
					$groupupah  = "SIPL1";
					$grouppremi = "SIPL1";
				} else {
					$groupupah  = "BLK01";
					$grouppremi = "BLK02";
				}
			} else if ($tip=='RND'){
				$groupupah  = "RNDB0";
				$grouppremi = "RNDB1";
			}  else if ($tip=='KANWIL'){
				$groupupah  = "GJHO1";
				$grouppremi = "GJHO2";
			} else if ($tip=='HOLDING'){
				$groupupah  = "GJHO1";
				$grouppremi = "GJHO2";
			} else if ($tip=='TC'){
				$groupupah  = "RNDB0";
				$grouppremi = "RNDB1";
			}
			$query = "select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$groupupah."' limit 1";
			$resque = fetchdata($query);
			foreach ($resque as $valque) {
				$noakunupah=$valque['noakundebet'];
			}
			$query = "select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$grouppremi."' limit 1";
			$resque = fetchdata($query);
			foreach ($resque as $valque) {
				$noakunpremi=$valque['noakundebet'];
			}

			$val['nik'] = $val['karyawanid'];
			if($listkary[$val['nik']]['kodeorg']!=''){
				if(($val['umr']-$val['penaltykehadiran'])>0){
					if($val['noakun']!=''){
						$noakunupah=$val['noakun'];
					}
					$data[]=array(
						$kodeorganisasi[$val['nik']][$periode],
						$lokasitugas[$val['nik']][$periode],
						getNamaOrg($subbagian[$val['nik']][$periode]),
						getKary($val['nik'],'nik'),
						getKary($val['nik'],'namakaryawan'),
						getNamaJabatan($kodejabatan[$val['nik']][$periode]),
						getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
						'',
						substr($val['tanggal'],0,7),
						$val['tanggal'],
						$noakunupah,
						getNamaAkun($noakunupah),
						'SDM Absensi',
						getNamaOrg($val['alokasi']),
						'',
						$val['penjelasan'],
						$val['absensi'],
						'Upah',
						($val['umr']-$val['penaltykehadiran'])
					);
				}
				if(($val['premi']+$val['insentif']+$val['insentiflibur'])>0){
					if($val['noakun']!=''){
						$noakunpremi=$val['noakun'];
					}
					$data[]=array(
						$kodeorganisasi[$val['nik']][$periode],
						$lokasitugas[$val['nik']][$periode],
						getNamaOrg($subbagian[$val['nik']][$periode]),
						getKary($val['nik'],'nik'),
						getKary($val['nik'],'namakaryawan'),
						getNamaJabatan($kodejabatan[$val['nik']][$periode]),
						getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
						'',
						substr($val['tanggal'],0,7),
						$val['tanggal'],
						$noakunupah,
						getNamaAkun($noakunupah),
						'SDM Absensi',
						getNamaOrg($val['alokasi']),
						'',
						$val['penjelasan'],
						$val['absensi'],
						'Premi',
						($val['premi']+$val['insentif']+$val['insentiflibur'])
					);
				}
				if($val['absensi']=='H' or $val['absensi']=='H/2'){				
					$data[]=array(
						$kodeorganisasi[$val['nik']][$periode],
						$lokasitugas[$val['nik']][$periode],
						getNamaOrg($subbagian[$val['nik']][$periode]),
						getKary($val['nik'],'nik'),
						getKary($val['nik'],'namakaryawan'),
						getNamaJabatan($kodejabatan[$val['nik']][$periode]),
						getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
						'',
						substr($val['tanggal'],0,7),
						$val['tanggal'],
						$noakunupah,
						getNamaAkun($noakunupah),
						'SDM Absensi',
						getNamaOrg($val['alokasi']),
						'',
						$val['penjelasan'],
						$val['absensi'],
						'HK',
						$val['hk']
					);
				}
			}
			if($val['absensi']=='H' or $val['absensi']=='H/2'){				
				$kehadiran[$val['nik']][$val['tanggal']]=1;
			}
			$hkabsendt[$val['nik']][$val['tanggal']]=1;
		}
		// echo"<pre>";
		// print_r($data);
		// print_r($jumlahvhc);
		// echo"</pre>";

		$listmandor=[];

		if($param['karyawanid']!=''){
			$whnik=" and c.nik='".$param['karyawanid']."'";
		}
		#pemel
		$sql =  "SELECT a.notransaksi, a.kodekegiatan, a.kodeorg as blok, a.hasilkerja, b.tipetransaksi, b.tanggal, b.kodeorg, b.divisi, c.*, b.nikmandor, b.nikmandor1, b.nikasisten, b.keranimuat FROM ".$dbname.".kebun_prestasi a
		left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
		left join ".$dbname.".kebun_kehadiran c on a.notransaksi=c.notransaksi and a.nourut=c.nourut and a.nikpemel=c.nik
		WHERE 1=1 and b.tipetransaksi!='PNN' ".$whpres." ".$whnik." and jurnal='1'";
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			if($listkary[$val['nik']]['kodeorg']!=''){
				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Pemeliharaan '.$val['tipetransaksi'],
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					getNamaKeg($val['kodekegiatan'],'satuan'),
					'Prestasi',
					$val['hasilkerja']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Pemeliharaan '.$val['tipetransaksi'],
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					getNamaKeg($val['kodekegiatan'],'satuan'),
					'HK',
					$val['jhk']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Pemeliharaan '.$val['tipetransaksi'],
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					getNamaKeg($val['kodekegiatan'],'satuan'),
					'Upah',
					$val['umr']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Pemeliharaan '.$val['tipetransaksi'],
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					getNamaKeg($val['kodekegiatan'],'satuan'),
					'Premi',
					$val['insentif']
				);
			}

			if($val['nikmandor']!=''){
				$listmandor[$val['tanggal']][$val['nikmandor']]=$val['notransaksi'];
				$kehadiran[$val['nikmandor']][$val['tanggal']]=1;
			}
			if($val['nikmandor1']!=''){
				$listmandor[$val['tanggal']][$val['nikmandor1']]=$val['notransaksi'];
				$kehadiran[$val['nikmandor1']][$val['tanggal']]=1;
			}
			if($val['nikasisten']!=''){
				$listmandor[$val['tanggal']][$val['nikasisten']]=$val['notransaksi'];
				$kehadiran[$val['nikasisten']][$val['tanggal']]=1;
			}
			if($val['keranimuat']!=''){
				$listmandor[$val['tanggal']][$val['keranimuat']]=$val['notransaksi'];
				$kehadiran[$val['keranimuat']][$val['tanggal']]=1;
			}
			$datakebun[$val['notransaksi']]['tipetransaksi']=$val['tipetransaksi'];
			$datakebun[$val['notransaksi']]['tanggal']=$val['tanggal'];
			$datakebun[$val['notransaksi']]['kodeorg']=$val['kodeorg'];
			$datakebun[$val['notransaksi']]['divisi']=$val['divisi'];

			$kehadiran[$val['nik']][$val['tanggal']]=1;
		}

		if($param['karyawanid']!=''){
			$whnik=" and nik='".$param['karyawanid']."'";
		}
		#panen jjg
		$sql =  "SELECT a.notransaksi, a.kodekegiatan, a.kodeorg as blok, a.nik, a.hasilkerja, b.tipetransaksi, b.tanggal, b.kodeorg, b.divisi, a.jumlahhk as jhk, a.upahkerja as umr, (a.upahpremi+a.upahpremilebihbasis+a.upahpremilebihbasis2+a.premibasis+a.premibasis2+a.premibrondol) as insentif, b.nikmandor, b.nikmandor1, b.nikasisten, b.keranimuat FROM ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
		WHERE 1=1 and b.tipetransaksi='PNN' and b.tipe='JJG' ".$whpres." ".$whnik." and jurnal='1'";
		// exit("error".$sql);
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			$val['kodekegiatan']='611010201';
			if($listkary[$val['nik']]['kodeorg']!=''){
				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Panen TBS JJG',
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					'Jjg',
					'Prestasi',
					$val['hasilkerja']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Panen TBS JJG',
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					'Jjg',
					'HK',
					$val['jhk']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Panen TBS JJG',
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					'Jjg',
					'Upah',
					$val['umr']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Panen TBS JJG',
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					'Jjg',
					'Premi',
					$val['insentif']
				);
			}
			if($val['nikmandor']!=''){
				$listmandor[$val['tanggal']][$val['nikmandor']]=$val['notransaksi'];
				$kehadiran[$val['nikmandor']][$val['tanggal']]=1;
			}
			if($val['nikmandor1']!=''){
				$listmandor[$val['tanggal']][$val['nikmandor1']]=$val['notransaksi'];
				$kehadiran[$val['nikmandor1']][$val['tanggal']]=1;
			}
			if($val['nikasisten']!=''){
				$listmandor[$val['tanggal']][$val['nikasisten']]=$val['notransaksi'];
				$kehadiran[$val['nikasisten']][$val['tanggal']]=1;
			}
			if($val['keranimuat']!=''){
				$listmandor[$val['tanggal']][$val['keranimuat']]=$val['notransaksi'];
				$kehadiran[$val['keranimuat']][$val['tanggal']]=1;
			}
			$datakebun[$val['notransaksi']]['tipetransaksi']=$val['tipetransaksi'];
			$datakebun[$val['notransaksi']]['tanggal']=$val['tanggal'];
			$datakebun[$val['notransaksi']]['kodeorg']=$val['kodeorg'];
			$datakebun[$val['notransaksi']]['divisi']=$val['divisi'];

			$kehadiran[$val['nik']][$val['tanggal']]=1;
		}

		#panen kg
		$sql =  "SELECT a.notransaksi, a.kodekegiatan, a.kodeorg as blok, a.nik, a.hasilkerja, b.tipetransaksi, b.tanggal, b.kodeorg, b.divisi, a.jumlahhk as jhk, a.upahkerja as umr, (a.upahpremi+a.upahpremilebihbasis+a.upahpremilebihbasis2+a.premibasis+a.premibasis2+a.premibrondol) as insentif, b.nikmandor, b.nikmandor1, b.nikasisten, b.keranimuat FROM ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
		WHERE 1=1 and b.tipetransaksi='PNN' and b.tipe='KG' ".$whpres." ".$whnik." and jurnal='1'";
		// exit("error".$sql);
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			$val['kodekegiatan']='611010201';
			if($listkary[$val['nik']]['kodeorg']!=''){
				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Panen TBS KG',
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					'Jjg',
					'Prestasi',
					$val['hasilkerja']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Panen TBS KG',
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					'Jjg',
					'HK',
					$val['jhk']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Panen TBS KG',
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					'Jjg',
					'Upah',
					$val['umr']
				);

				$data[]=array(
					$kodeorganisasi[$val['nik']][$periode],
					$lokasitugas[$val['nik']][$periode],
					getNamaOrg($subbagian[$val['nik']][$periode]),
					getKary($val['nik'],'nik'),
					getKary($val['nik'],'namakaryawan'),
					getNamaJabatan($kodejabatan[$val['nik']][$periode]),
					getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
					$val['notransaksi'],
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					getNamaKeg($val['kodekegiatan'],'noakun'),
					getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
					'Panen TBS KG',
					getNamaOrg($val['blok']),
					$val['kodekegiatan'],
					getNamaKeg($val['kodekegiatan']),
					'Jjg',
					'Premi',
					$val['insentif']
				);
			}
			if($val['nikmandor']!=''){
				$listmandor[$val['tanggal']][$val['nikmandor']]=$val['notransaksi'];
				$kehadiran[$val['nikmandor']][$val['tanggal']]=1;
			}
			if($val['nikmandor1']!=''){
				$listmandor[$val['tanggal']][$val['nikmandor1']]=$val['notransaksi'];
				$kehadiran[$val['nikmandor1']][$val['tanggal']]=1;
			}
			if($val['nikasisten']!=''){
				$listmandor[$val['tanggal']][$val['nikasisten']]=$val['notransaksi'];
				$kehadiran[$val['nikasisten']][$val['tanggal']]=1;
			}
			if($val['keranimuat']!=''){
				$listmandor[$val['tanggal']][$val['keranimuat']]=$val['notransaksi'];
				$kehadiran[$val['keranimuat']][$val['tanggal']]=1;
			}
			$datakebun[$val['notransaksi']]['tipetransaksi']=$val['tipetransaksi'];
			$datakebun[$val['notransaksi']]['tanggal']=$val['tanggal'];
			$datakebun[$val['notransaksi']]['kodeorg']=$val['kodeorg'];
			$datakebun[$val['notransaksi']]['divisi']=$val['divisi'];

			$kehadiran[$val['nik']][$val['tanggal']]=1;
		}


		if($param['karyawanid']!=''){
			$whnik=" and (nikmandor='".$param['karyawanid']."' or nikmandor1='".$param['karyawanid']."' or nikasisten='".$param['karyawanid']."' or keranimuat='".$param['karyawanid']."')";
		}
		$sql =  "SELECT * FROM ".$dbname.".kebun_aktifitas b WHERE 1=1 ".$whpres." ".$whnik." and b.tipetransaksi='PNN' and b.tipe='KG' and jurnal='1'";
		$res = fetchdata($sql);
		foreach ($res as $val) {
			if($val['nikmandor']!=''){
				$listmandor[$val['tanggal']][$val['nikmandor']]=$val['notransaksi'];
				$kehadiran[$val['nikmandor']][$val['tanggal']]=1;
			}
			if($val['nikmandor1']!=''){
				$listmandor[$val['tanggal']][$val['nikmandor1']]=$val['notransaksi'];
				$kehadiran[$val['nikmandor1']][$val['tanggal']]=1;
			}
			if($val['nikasisten']!=''){
				$listmandor[$val['tanggal']][$val['nikasisten']]=$val['notransaksi'];
				$kehadiran[$val['nikasisten']][$val['tanggal']]=1;
			}
			if($val['keranimuat']!=''){
				$listmandor[$val['tanggal']][$val['keranimuat']]=$val['notransaksi'];
				$kehadiran[$val['keranimuat']][$val['tanggal']]=1;
			}
			$datakebun[$val['notransaksi']]['tipetransaksi']=$val['tipetransaksi'];
			$datakebun[$val['notransaksi']]['tanggal']=$val['tanggal'];
			$datakebun[$val['notransaksi']]['kodeorg']=$val['kodeorg'];
			$datakebun[$val['notransaksi']]['divisi']=$val['divisi'];
		}
		
		if($param['karyawanid']!=''){
			$whnik=" and idkaryawan='".$param['karyawanid']."'";
		}
		$datavhc = $listnotrvhc = [];
		$sql =  "SELECT a.* FROM ".$dbname.".vhc_runhk a
				left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
				WHERE 1=1 ".$whpres." ".$whnik." and posting='1' and (premi >0 or upah>0)"; //exit("error".$sql);
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			if($listkary[$val['idkaryawan']]['kodeorg']!=''){
				if($val['upah']>0){
					$datavhc[$val['notransaksi']][$val['idkaryawan']]['Upah']+=$val['upah'];
					$listnotrvhc[$val['notransaksi']]=$val['notransaksi'];
				}
				if($val['premi']>0){
					$datavhc[$val['notransaksi']][$val['idkaryawan']]['Premi']+=$val['premi'];
					$listnotrvhc[$val['notransaksi']]=$val['notransaksi'];
				}
				$kehadiran[$val['idkaryawan']][$val['tanggal']]=1;
			}
		}

		$sql =  "SELECT * FROM ".$dbname.".vhc_kegiatan"; //exit("error".$sql);
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$kegvhc[$val['kodekegiatan']]=$val['namakegiatan'];
			$akunvhc[$val['kodekegiatan']]=$val['noakun'];
		}

		$sql =  "SELECT * FROM ".$dbname.".vhc_rundt a
				left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
				WHERE 1=1 ".$whpres."  and posting='1'"; //exit("error".$sql);
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$totalvhc[$val['notransaksi']]+=$val['jumlah'];
		}
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			$tahun = substr($val['tanggal'],0,4);
			if($listnotrvhc[$val['notransaksi']]!=''){
				foreach(@$datavhc[$val['notransaksi']] as $karyawan => $val1){
					foreach($val1 as $jenis => $value){
						$jumlahvhc=$val['jumlah']/$totalvhc[$val['notransaksi']]*$value;
						$val['nik'] = $karyawan;
						$val['kodekegiatan'] = $val['jenispekerjaan'];

						$kehadiran[$val['nik']][$val['tanggal']]=1;
						$data[]=array(
							$kodeorganisasi[$val['nik']][$periode],
							$lokasitugas[$val['nik']][$periode],
							getNamaOrg($subbagian[$val['nik']][$periode]),
							getKary($val['nik'],'nik'),
							getKary($val['nik'],'namakaryawan'),
							getNamaJabatan($kodejabatan[$val['nik']][$periode]),
							getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
							$val['notransaksi'],
							substr($val['tanggal'],0,7),
							$val['tanggal'],
							$akunvhc[$val['kodekegiatan']],
							getNamaAkun($akunvhc[$val['kodekegiatan']]),
							"Kegiatan Traksi",
							getNamaOrg($val['alokasibiaya']),
							$val['kodekegiatan'],
							$kegvhc[$val['kodekegiatan']],
							$val['satuan'],
							$jenis,
							$jumlahvhc
						);
						$data[]=array(
							$kodeorganisasi[$val['nik']][$periode],
							$lokasitugas[$val['nik']][$periode],
							getNamaOrg($subbagian[$val['nik']][$periode]),
							getKary($val['nik'],'nik'),
							getKary($val['nik'],'namakaryawan'),
							getNamaJabatan($kodejabatan[$val['nik']][$periode]),
							getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
							$val['notransaksi'],
							substr($val['tanggal'],0,7),
							$val['tanggal'],
							$akunvhc[$val['kodekegiatan']],
							getNamaAkun($akunvhc[$val['kodekegiatan']]),
							"Kegiatan Traksi",
							getNamaOrg($val['alokasibiaya']),
							$val['kodekegiatan'],
							$kegvhc[$val['kodekegiatan']],
							$val['satuan'],
							'Prestasi',
							$val['jumlah']
						);

						if($jenis=='Upah' and $jumlahvhc>0){
							$data[]=array(
								$kodeorganisasi[$val['nik']][$periode],
								$lokasitugas[$val['nik']][$periode],
								getNamaOrg($subbagian[$val['nik']][$periode]),
								getKary($val['nik'],'nik'),
								getKary($val['nik'],'namakaryawan'),
								getNamaJabatan($kodejabatan[$val['nik']][$periode]),
								getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
								$val['notransaksi'],
								substr($val['tanggal'],0,7),
								$val['tanggal'],
								$akunvhc[$val['kodekegiatan']],
								getNamaAkun($akunvhc[$val['kodekegiatan']]),
								"Kegiatan Traksi",
								getNamaOrg($val['alokasibiaya']),
								$val['kodekegiatan'],
								$kegvhc[$val['kodekegiatan']],
								$val['satuan'],
								'HK',
								fixnan($jumlahvhc/$gaji[$val['nik']][$tahun])
							);
							$hkabsendt[$val['nik']][$val['tanggal']]=1;
						}
					}
				}
			}
		}



		foreach($listmandor as $tanggal => $val2){
			foreach($val2 as $nik => $notran){
				if($datakebun[$notran]['tipetransaksi']=='PNN'){
					$val['kodekegiatan']='611010101';
				}elseif($datakebun[$notran]['tipetransaksi']=='TBM'){
					$val['kodekegiatan']='126012001';
				}elseif($datakebun[$notran]['tipetransaksi']=='BBT'){
					$val['kodekegiatan']='128021001';
				}elseif($datakebun[$notran]['tipetransaksi']=='BBT'){
					$val['kodekegiatan']='128021001';
				}else{
					$val['kodekegiatan']='621011501';
				}
				$periode = substr($val['tanggal'],0,7);
				$tahun = substr($val['tanggal'],0,4);
				$val['nik'] = $nik;
				$val['tanggal'] = $datakebun[$notran]['tanggal'];
				$val['divisi'] = $datakebun[$notran]['divisi'];


				if($listkary[$val['nik']]['kodeorg']!='' and empty($hkabsendt[$val['nik']][$val['tanggal']])){
					$data[]=array(
						$kodeorganisasi[$val['nik']][$periode],
						$lokasitugas[$val['nik']][$periode],
						getNamaOrg($subbagian[$val['nik']][$periode]),
						getKary($val['nik'],'nik'),
						getKary($val['nik'],'namakaryawan'),
						getNamaJabatan($kodejabatan[$val['nik']][$periode]),
						getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
						$notran,
						substr($val['tanggal'],0,7),
						$val['tanggal'],
						getNamaKeg($val['kodekegiatan'],'noakun'),
						getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
						"Supervisi ".$datakebun[$notran]['tipetransaksi'],
						getNamaOrg($val['divisi']),
						$val['kodekegiatan'],
						getNamaKeg($val['kodekegiatan']),
						'HK',
						'HK',
						'1'
					);

					$data[]=array(
						$kodeorganisasi[$val['nik']][$periode],
						$lokasitugas[$val['nik']][$periode],
						getNamaOrg($subbagian[$val['nik']][$periode]),
						getKary($val['nik'],'nik'),
						getKary($val['nik'],'namakaryawan'),
						getNamaJabatan($kodejabatan[$val['nik']][$periode]),
						getNamaTipeKary($tipekaryawan[$val['nik']][$periode]),
						$notran,
						substr($val['tanggal'],0,7),
						$val['tanggal'],
						getNamaKeg($val['kodekegiatan'],'noakun'),
						getNamaAkun(getNamaKeg($val['kodekegiatan'],'noakun')),
						"Supervisi ".$datakebun[$notran]['tipetransaksi'],
						getNamaOrg($val['divisi']),
						$val['kodekegiatan'],
						getNamaKeg($val['kodekegiatan']),
						'HK',
						'Upah',
						$gaji[$val['nik']][$tahun]
					);
				}
			}
		}

		
		foreach($kehadiran as $nik => $val1){
			foreach($val1 as $tanggal => $hadir){
				$periode = substr($tanggal,0,7);
				$tahun   = substr($tanggal,0,4);
				if($listkary[$nik]['kodeorg']!=''){
					$data[]=array(
						$kodeorganisasi[$nik][$periode],
						$lokasitugas[$nik][$periode],
						getNamaOrg($subbagian[$nik][$periode]),
						getKary($nik,'nik'),
						getKary($nik,'namakaryawan'),
						getNamaJabatan($kodejabatan[$nik][$periode]),
						getNamaTipeKary($tipekaryawan[$nik][$periode]),
						'',
						$periode,
						$tanggal,
						"",
						"",
						"Kehadiran",
						"",
						"",
						"",
						"",
						'Hadir',
						$hadir
					);
				}
			}
		}

	break;
	case'hkdankehadiran':
		$whr=$wh=$whg=$whpres="";
		if($param['kodeorg']!=''){
			$whr.=" and kodeorg='".$param['kodeorg']."'";
			$wh.=" and lokasitugas='".$param['kodeorg']."'";
			$whg.=" and substr(kodeorg,1,4)='".$param['kodeorg']."'";

			$whpres.=" and substr(b.kodeorg,1,4)='".$param['kodeorg']."'";
		}else{
			$whr.=" and kodeorg in (".getOrgDetail(2).")";
			$wh.=" and lokasitugas in (".getOrgDetail(2).")";
			$whg.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";

			$whpres.=" and substr(b.kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		$rangebulan = month_inbetween($param['periode'],$param['periode2']);
		foreach($rangebulan as $bulan){
			$dtbulan[$bulan]=$bulan;
			$thn = substr($bulan,0,4);
			$dttahun[$thn]=$thn;
		}

		foreach($dttahun as $tahun){
			$str = "select * from " . $dbname . ".sdm_5gajipokok where 1=1 and tahun='".$tahun."' and idkomponen='1'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$gaji[$val['karyawanid']][$tahun]=$val['jumlah']/25;
			}
		}

		$whr.=" and periode in ('".implode("','",$dtbulan)."')";
		$wh.=" and periodegaji in ('".implode("','",$dtbulan)."')";
		$whg.=" and periodegaji in ('".implode("','",$dtbulan)."')";
		$whpres.=" and substr(b.tanggal,1,7) in ('".implode("','",$dtbulan)."')";

		if($param['tipekaryawan']!=''){
			$whr.=" and nik in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 ".$wh." and version_type='B' and tipekaryawan='".$param['tipekaryawan']."')";

			$wh.=" and tipekaryawan='".$param['tipekaryawan']."'";
			$whg.=" and tipekaryawan='".$param['tipekaryawan']."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$whr.=" and nik in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 ".$wh." and version_type='B' and tipekaryawan='4')";
				$wh.=" and tipekaryawan='4'";
				$whg.=" and tipekaryawan='4'";
			}
		}

		$str = "select * from ".$dbname.".datakaryawan_hist where 1=1 and version_type='B' ".$wh."";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$listkary[$val['karyawanid']]['kodeorg']=$val['lokasitugas'];
		}

		$str = "select * from ".$dbname.".datakaryawan_hist where 1=1 and version_type='B' and periodegaji in ('".implode("','",$dtbulan)."')";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$kodeorganisasi[$val['karyawanid']][$val['periodegaji']]=$val['kodeorganisasi'];
			$lokasitugas[$val['karyawanid']][$val['periodegaji']]=$val['lokasitugas'];
			$subbagian[$val['karyawanid']][$val['periodegaji']]=$val['subbagian'];
			$kodejabatan[$val['karyawanid']][$val['periodegaji']]=$val['kodejabatan'];
			$tipekaryawan[$val['karyawanid']][$val['periodegaji']]=$val['tipekaryawan'];
			if($val['subbagian']==''){
				$subbagian[$val['karyawanid']][$val['periodegaji']]="UMUM / KANTOR ".$val['lokasitugas'];
			}
		}

		$str = "select * from ".$dbname.".datakaryawan";
		$res = fetchdata($str);
		foreach ($res as $val) {
			foreach($rangebulan as $bulan){
				if(empty($lokasitugas[$val['karyawanid']][$bulan])){
					$kodeorganisasi[$val['karyawanid']][$bulan]=$val['kodeorganisasi'];
					$lokasitugas[$val['karyawanid']][$bulan]=$val['lokasitugas'];
					$subbagian[$val['karyawanid']][$bulan]=$val['subbagian'];
					$kodejabatan[$val['karyawanid']][$bulan]=$val['kodejabatan'];
					$tipekaryawan[$val['karyawanid']][$bulan]=$val['tipekaryawan'];
					if($val['subbagian']==''){
						$subbagian[$val['karyawanid']][$bulan]="UMUM / KANTOR ".$val['lokasitugas'];
					}
				}
			}
		}

		$datae[]=array('PT Kary','Unit Kary','Divisi Kary','NIK','Nama Karyawan','Jabatan','Sumber Inputan','Tipe Kary','Periode','Tanggal','Komponen','Jumlah');

		$numb=array(22);

		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row     = array("Unit Kary","Divisi Kary");
		$col     = array("Komponen");
		$value   = array('Jumlah');
		$datasort= array("HK","Hadir");

		$sql =  "SELECT * FROM ".$dbname.".sdm_5absensi";
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$absensi[$val['kodeabsen']]=$val['nilaihk'];
		}

		$sql =  "SELECT * FROM ".$dbname.".sdm_absensidt b WHERE 1=1 ".$whpres." and absensi in ('H','H/2')";
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			$tip = getNamaOrg(substr($val['kodeorg'],0,4),'tipe');
			$param['tipeorganisasi'] = getNamaOrg($subbagian[$val['karyawanid']][$periode],'tipe');

			$val['nik'] = $val['karyawanid'];
			if($listkary[$val['nik']]['kodeorg']!=''){
				$data[]=array(
					cleanSpecialChar($kodeorganisasi[$val['nik']][$periode]),
					cleanSpecialChar($lokasitugas[$val['nik']][$periode]),
					cleanSpecialChar(getNamaOrg($subbagian[$val['nik']][$periode])),
					cleanSpecialChar(getKary($val['nik'],'nik')),
					cleanSpecialChar(getKary($val['nik'],'namakaryawan')),
					cleanSpecialChar(getNamaJabatan($kodejabatan[$val['nik']][$periode])),
					"Umum (Absensi)",
					cleanSpecialChar(getNamaTipeKary($tipekaryawan[$val['nik']][$periode])),
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					'HK',
					$val['hk']
				);
				$kehadiran[$val['nik']][$val['tanggal']]=1;
				$hkabsendt[$val['nik']][$val['tanggal']]=1;
			}
		}
		

		#pemel
		$sql =  "SELECT a.notransaksi, a.kodekegiatan, a.kodeorg as blok, a.hasilkerja, b.tipetransaksi, b.tanggal, b.kodeorg, b.divisi, c.*, b.nikmandor, b.nikmandor1, b.nikasisten, b.keranimuat FROM ".$dbname.".kebun_prestasi a
				left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
				left join ".$dbname.".kebun_kehadiran c on a.notransaksi=c.notransaksi and a.nourut=c.nourut and a.nikpemel=c.nik
				WHERE 1=1 and b.tipetransaksi!='PNN' ".$whpres."  and jurnal='1'";
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			if($listkary[$val['nik']]['kodeorg']!=''){
				$data[]=array(
					cleanSpecialChar($kodeorganisasi[$val['nik']][$periode]),
					cleanSpecialChar($lokasitugas[$val['nik']][$periode]),
					cleanSpecialChar(getNamaOrg($subbagian[$val['nik']][$periode])),
					cleanSpecialChar(getKary($val['nik'],'nik')),
					cleanSpecialChar(getKary($val['nik'],'namakaryawan')),
					cleanSpecialChar(getNamaJabatan($kodejabatan[$val['nik']][$periode])),
					"Rawat Kebun",
					cleanSpecialChar(getNamaTipeKary($tipekaryawan[$val['nik']][$periode])),
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					'HK',
					$val['jhk']
				);

				$kehadiran[$val['nik']][$val['tanggal']]=1;
			}

			if($val['nikmandor']!=''){
				$kehadiran[$val['nikmandor']][$val['tanggal']]=1;
				$mandor[$val['nikmandor']][$val['tanggal']]=1;
			}
			if($val['nikmandor1']!=''){
				$kehadiran[$val['nikmandor1']][$val['tanggal']]=1;
				$mandor[$val['nikmandor1']][$val['tanggal']]=1;
			}
			if($val['nikasisten']!=''){
				$kehadiran[$val['nikasisten']][$val['tanggal']]=1;
				$mandor[$val['nikasisten']][$val['tanggal']]=1;
			}
			if($val['keranimuat']!=''){
				$kehadiran[$val['keranimuat']][$val['tanggal']]=1;
				$mandor[$val['keranimuat']][$val['tanggal']]=1;
			}
			$datakebun[$val['notransaksi']]['tipetransaksi']=$val['tipetransaksi'];
			$datakebun[$val['notransaksi']]['tanggal']=$val['tanggal'];
			$datakebun[$val['notransaksi']]['kodeorg']=$val['kodeorg'];
			$datakebun[$val['notransaksi']]['divisi']=$val['divisi'];
		}

		#panen jjg
		$sql =  "SELECT a.notransaksi, a.kodekegiatan, a.kodeorg as blok, a.nik, a.hasilkerja, b.tipetransaksi, b.tanggal, b.kodeorg, b.divisi, a.jumlahhk as jhk, a.upahkerja as umr, (a.upahpremi+a.upahpremilebihbasis+a.upahpremilebihbasis2+a.premibasis+a.premibasis2+a.premibrondol) as insentif, b.nikmandor, b.nikmandor1, b.nikasisten, b.keranimuat FROM ".$dbname.".kebun_prestasi a
		left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
		WHERE 1=1 and b.tipetransaksi='PNN' ".$whpres."  and jurnal='1'";
		// exit("error".$sql);
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			if($listkary[$val['nik']]['kodeorg']!=''){
				$data[]=array(
					cleanSpecialChar($kodeorganisasi[$val['nik']][$periode]),
					cleanSpecialChar($lokasitugas[$val['nik']][$periode]),
					cleanSpecialChar(getNamaOrg($subbagian[$val['nik']][$periode])),
					cleanSpecialChar(getKary($val['nik'],'nik')),
					cleanSpecialChar(getKary($val['nik'],'namakaryawan')),
					cleanSpecialChar(getNamaJabatan($kodejabatan[$val['nik']][$periode])),
					"Pemanen",
					cleanSpecialChar(getNamaTipeKary($tipekaryawan[$val['nik']][$periode])),
					substr($val['tanggal'],0,7),
					$val['tanggal'],
					'HK',
					$val['jhk']
				);
				$kehadiran[$val['nik']][$val['tanggal']]=1;
			}
			if($val['nikmandor']!=''){
				$kehadiran[$val['nikmandor']][$val['tanggal']]=1;
				$mandor[$val['nikmandor']][$val['tanggal']]=1;
			}
			if($val['nikmandor1']!=''){
				$kehadiran[$val['nikmandor1']][$val['tanggal']]=1;
				$mandor[$val['nikmandor1']][$val['tanggal']]=1;
			}
			if($val['nikasisten']!=''){
				$kehadiran[$val['nikasisten']][$val['tanggal']]=1;
				$mandor[$val['nikasisten']][$val['tanggal']]=1;
			}
			if($val['keranimuat']!=''){
				$kehadiran[$val['keranimuat']][$val['tanggal']]=1;
				$mandor[$val['keranimuat']][$val['tanggal']]=1;
			}
			$datakebun[$val['notransaksi']]['tipetransaksi']=$val['tipetransaksi'];
			$datakebun[$val['notransaksi']]['tanggal']=$val['tanggal'];
			$datakebun[$val['notransaksi']]['kodeorg']=$val['kodeorg'];
			$datakebun[$val['notransaksi']]['divisi']=$val['divisi'];
		}

		$datavhc = $listnotrvhc = [];
		$sql =  "SELECT a.*, sum(upah) as upah, sum(premi) as premi FROM ".$dbname.".vhc_runhk a
				left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
				WHERE 1=1 ".$whpres."  and posting='1' and (premi >0 or upah>0)
				group by idkaryawan, tanggal"; //exit("error".$sql);
		$res = fetchdata($sql);
		foreach ($res as $val) {
			$periode = substr($val['tanggal'],0,7);
			$tahun = substr($val['tanggal'],0,4);
			if($listkary[$val['idkaryawan']]['kodeorg']!=''){
				if($val['upah']>0){
					if($val['upah']/$gaji[$val['idkaryawan']][$tahun]>1){
						$hk=1;
					}else{
						$hk=fixnan($val['upah']/$gaji[$val['idkaryawan']][$tahun]);
					}
					
					$data[]=array(
						cleanSpecialChar($kodeorganisasi[$val['idkaryawan']][$periode]),
						cleanSpecialChar($lokasitugas[$val['idkaryawan']][$periode]),
						cleanSpecialChar(getNamaOrg($subbagian[$val['idkaryawan']][$periode])),
						cleanSpecialChar(getKary($val['idkaryawan'],'nik')),
						cleanSpecialChar(getKary($val['idkaryawan'],'namakaryawan')),
						cleanSpecialChar(getNamaJabatan($kodejabatan[$val['idkaryawan']][$periode])),
						"Operator (Traksi)",
						cleanSpecialChar(getNamaTipeKary($tipekaryawan[$val['idkaryawan']][$periode])),
						substr($val['tanggal'],0,7),
						$val['tanggal'],
						'HK',
						$hk
					);
					$hkabsendt[$val['idkaryawan']][$val['tanggal']]=1;
				}
				if($val['premi']>0){
					$datavhc[$val['idkaryawan']]['Premi']+=$val['premi'];
				}
				$kehadiran[$val['idkaryawan']][$val['tanggal']]=1;
			}
		}
		
		// echo"<pre>";
		// print_r($data);
		// echo"</pre>";
		// exit("error");
		foreach($kehadiran as $nik => $val2){
			foreach($val2 as $tanggal => $nilai){
				$val['nik'] = $nik;
				$val['tanggal'] = $tanggal;

				$periode = substr($val['tanggal'],0,7);
				$tahun = substr($val['tanggal'],0,4);
				if($listkary[$val['nik']]['kodeorg']!=''){
					$data[]=array(
						cleanSpecialChar($kodeorganisasi[$val['nik']][$periode]),
						cleanSpecialChar($lokasitugas[$val['nik']][$periode]),
						cleanSpecialChar(getNamaOrg($subbagian[$val['nik']][$periode])),
						cleanSpecialChar(getKary($val['nik'],'nik')),
						cleanSpecialChar(getKary($val['nik'],'namakaryawan')),
						cleanSpecialChar(getNamaJabatan($kodejabatan[$val['nik']][$periode])),
						"",
						cleanSpecialChar(getNamaTipeKary($tipekaryawan[$val['nik']][$periode])),
						$periode,
						$tanggal,
						'Hadir',
						$nilai
					);
				}
			}
		}
		foreach($mandor as $nik => $val2){
			foreach($val2 as $tanggal => $nilai){
				$val['nik'] = $nik;
				$val['tanggal'] = $tanggal;
				
				$periode = substr($val['tanggal'],0,7);
				$tahun = substr($val['tanggal'],0,4);
				
				if($listkary[$val['nik']]['kodeorg']!='' and empty($hkabsendt[$val['nik']][$val['tanggal']])){
					$data[]=array(
						cleanSpecialChar($kodeorganisasi[$val['nik']][$periode]),
						cleanSpecialChar($lokasitugas[$val['nik']][$periode]),
						cleanSpecialChar(getNamaOrg($subbagian[$val['nik']][$periode])),
						cleanSpecialChar(getKary($val['nik'],'nik')),
						cleanSpecialChar(getKary($val['nik'],'namakaryawan')),
						cleanSpecialChar(getNamaJabatan($kodejabatan[$val['nik']][$periode])),
						"Mandor",
						cleanSpecialChar(getNamaTipeKary($tipekaryawan[$val['nik']][$periode])),
						$periode,
						$tanggal,
						'HK',
						$nilai
					);
				}
			}
		}


	break;
	case'alokasi':
		$whr=$wh=$whg="";
		if($param['kodeorg']!=''){
			$whr.=" and kodeorg='".$param['kodeorg']."'";
			$wh.=" and lokasitugas='".$param['kodeorg']."'";
			$whg.=" and substr(kodeorg,1,4)='".$param['kodeorg']."'";
		}else{
			$whr.=" and kodeorg in (".getOrgDetail(2).")";
			$wh.=" and lokasitugas in (".getOrgDetail(2).")";
			$whg.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		$rangebulan = month_inbetween($param['periode'],$param['periode2']);
		foreach($rangebulan as $bulan){
			$dtbulan[$bulan]=$bulan;
		}
		$whr.=" and periode in ('".implode("','",$dtbulan)."')";
		$wh.=" and periodegaji in ('".implode("','",$dtbulan)."')";
		$whg.=" and periodegaji in ('".implode("','",$dtbulan)."')";

		if($param['tipekaryawan']!=''){
			$whr.=" and nik in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 ".$wh." and version_type='B' and tipekaryawan='".$param['tipekaryawan']."')";

			$wh.=" and tipekaryawan='".$param['tipekaryawan']."'";
			$whg.=" and tipekaryawan='".$param['tipekaryawan']."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$whr.=" and nik in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 ".$wh." and version_type='B' and tipekaryawan='4')";
				$wh.=" and tipekaryawan='4'";
				$whg.=" and tipekaryawan='4'";
			}
		}


		$datae[]=array('PT Kary','Unit Kary','Divisi Kary','NIK','Nama Karyawan','Jabatan','Tipe Kary','No Jurnal','Periode','Tanggal','Noakun','Nama Akun','Keterangan','Unit Alokasi','Kode Kegiatan','Nama Kegiatan','Noreferensi','Kode Kend','Kode Blok','Kode Jurnal','Data','Jumlah');

		$numb=array(22);

		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row     = array("Unit Kary","Divisi Kary");
		$col     = array("Data");
		$value   = array('Jumlah');
		$datasort= array("Rupiah");

		// $str = "select distinct karyawanid from ".$dbname.".sdm_gaji_vw where 1=1 ".$whg."";
		// $res = fetchdata($str);
		// foreach($res as $key => $bar){
		// }

		$where=" and nojurnal in (SELECT distinct nojurnal FROM ".$dbname.".keu_jurnaldt_vw WHERE noakun like '216%' and nik!='' ".$whr." AND jumlah < '0' AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%')";
		$query = "SELECT * FROM ".$dbname.".keu_jurnaldt_vw
				WHERE noakun not like '216%' ".$where."
				AND jumlah > '0'
				AND noreferensi NOT LIKE 'ALK_POT%' and nojurnal not like '%/M/%'";
		$strquery = fetchdata($query);
		foreach ($strquery as $key => $val) {
			$data[]=array(
				getKary($val['nik'],'kodeorganisasi'),
				getKary($val['nik'],'lokasitugas'),
				getKary($val['nik'],'subbagian'),
				getKary($val['nik'],'nik'),
				getKary($val['nik'],'namakaryawan'),
				getNamaJabatan(getKary($val['nik'],'kodejabatan')),
				getNamaTipeKary(getKary($val['nik'],'tipekaryawan')),
				$val['nojurnal'],
				$val['periode'],
				$val['tanggal'],
				$val['noakun'],
				getNamaAkun($val['noakun']),
				cleanSpecialChar($val['keterangan']),
				$val['kodeorg'],
				$val['kodekegiatan'],
				getNamaKeg($val['kodekegiatan']),
				$val['noreferensi'],
				$val['kodevhc'],
				getNamaOrg($val['kodeblok']),
				$val['kodejurnal'],
				'Rupiah',
				$val['jumlah']
			);
		}
	break;
	case'detpayroll':
		$wh="";$whr="";
		if($param['tipekaryawan']!=''){
			$whr.=" and tipekaryawan='".$param['tipekaryawan']."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$whr=" and tipekaryawan in ('4')";
			}
		}
		if($param['kodeorg']!=''){
			$whr.=" and lokasitugas='".$param['kodeorg']."'";
		}else{
			$whr.=" and lokasitugas in (".getOrgDetail(2).")";
		}

		$tgl1    = $param['periode']."-01";
		$tgl2    = tglakhir($param['periode2']."-01");
		$rangetgl= rangeTanggal($tgl1,$tgl2);

		$rangebulan = month_inbetween($param['periode'],$param['periode2']);
		foreach($rangebulan as $bulan){
			$thn = substr($bulan,0,4);
			$dttahun[$thn]=$thn;
		}
		foreach($dttahun as $tahun){
			$str = "select * from " . $dbname . ".sdm_5gajipokok where 1=1 and tahun='".$tahun."' and idkomponen='1'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$gaji[$val['karyawanid']][$tahun]=$val['jumlah']/25;
			}
		}

		$datae[]=array('Periode','Tahap','Tanggal','PT','Unit','Tipe Org','Divisi','KaryID','NIK','Nama Karyawan','Jabatan','Tipe Kary','Status Pajak','NPWP','Bank','Rekening','Pemilik','Sistem Gaji','Tanggal Masuk','Tanggal Keluar','Agama','J/K','Dept','Golongan','Status Kary','Noakun','Nama Akun','Keterangan','Noreferensi','Absensi','Komponen','Jumlah');

		$numb=array(27);

		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row     = array("Unit","Divisi");
		$col     = array("Komponen");
		$value   = array('Jumlah');
		$datasort= array("HK","Upah","Premi");




		$nourut = 0;
		foreach($rangebulan as $bulan){
			$sql = "select * from ".$dbname.".datakaryawan where 1=1  ".$whr."";
			$req = fetchdata($sql);
			foreach($req as $bar){
				if($bar['subbagian']==''){
					$bar['subbagian']="UMUM";
				}else{
					$bar['subbagian']=getNamaOrg($bar['subbagian']);
				}

				$datakary[$bar['karyawanid']]=$bar['karyawanid'];
				$dt[$bar['karyawanid']][$bulan]['nama']=$bar['namakaryawan'];
				$dt[$bar['karyawanid']][$bulan]['nik']=$bar['nik'];
				$dt[$bar['karyawanid']][$bulan]['jk']=$bar['jeniskelamin'];
				$dt[$bar['karyawanid']][$bulan]['agama']=$bar['agama'];
				$dt[$bar['karyawanid']][$bulan]['norek']=$bar['norekeningbank'];
				$dt[$bar['karyawanid']][$bulan]['bank']=$bar['namabank'];
				$dt[$bar['karyawanid']][$bulan]['pemilik']=$bar['pemilikrekening'];
				$dt[$bar['karyawanid']][$bulan]['sistemgaji']=$bar['sistemgaji'];
				$dt[$bar['karyawanid']][$bulan]['tanggalmasuk']=$bar['tanggalmasuk'];
				$dt[$bar['karyawanid']][$bulan]['tanggalkeluar']=$bar['tanggalkeluar'];
				$dt[$bar['karyawanid']][$bulan]['tipekaryawan']=$bar['tipekaryawan'];
				$dt[$bar['karyawanid']][$bulan]['statuspajak']=$bar['statuspajak'];
				$dt[$bar['karyawanid']][$bulan]['npwp']=$bar['npwp'];
				$dt[$bar['karyawanid']][$bulan]['pt']=$bar['kodeorganisasi'];
				$dt[$bar['karyawanid']][$bulan]['dept']=$bar['bagian'];
				$dt[$bar['karyawanid']][$bulan]['kodejabatan']=$bar['kodejabatan'];
				$dt[$bar['karyawanid']][$bulan]['kodegolongan']=$bar['kodegolongan'];
				$dt[$bar['karyawanid']][$bulan]['lokasitugas']=$bar['lokasitugas'];
				$dt[$bar['karyawanid']][$bulan]['alokasi']=$bar['alokasi'];
				$dt[$bar['karyawanid']][$bulan]['subbagian']=$bar['subbagian'];
				$dt[$bar['karyawanid']][$bulan]['statuskaryawan']=$bar['statuskaryawan'];
				$dt[$bar['karyawanid']][$bulan]['kppnpwp']=$bar['kppnpwp'];
				$dt[$bar['karyawanid']][$bulan]['bpjstk']=$bar['jms'];
				$dt[$bar['karyawanid']][$bulan]['bpjskes']=$bar['bpjs'];
				$dt[$bar['karyawanid']][$bulan]['pensiun']=$bar['pensiun'];
			}


			$sql = "select * from ".$dbname.".datakaryawan_hist where 1=1  and periodegaji = '".$bulan."' and version_type='B' ".$whr."";
			$req = fetchdata($sql);
			foreach($req as $bar){
				if($bar['subbagian']==''){
					$bar['subbagian']="UMUM";
				}else{
					$bar['subbagian']=getNamaOrg($bar['subbagian']);
				}

				$datakary[$bar['karyawanid']]=$bar['karyawanid'];
				$dt[$bar['karyawanid']][$bulan]['nama']=$bar['namakaryawan'];
				$dt[$bar['karyawanid']][$bulan]['nik']=$bar['nik'];
				$dt[$bar['karyawanid']][$bulan]['jk']=$bar['jeniskelamin'];
				$dt[$bar['karyawanid']][$bulan]['agama']=$bar['agama'];
				$dt[$bar['karyawanid']][$bulan]['norek']=$bar['norekeningbank'];
				$dt[$bar['karyawanid']][$bulan]['bank']=$bar['namabank'];
				$dt[$bar['karyawanid']][$bulan]['pemilik']=$bar['pemilikrekening'];
				$dt[$bar['karyawanid']][$bulan]['sistemgaji']=$bar['sistemgaji'];
				$dt[$bar['karyawanid']][$bulan]['tanggalmasuk']=$bar['tanggalmasuk'];
				$dt[$bar['karyawanid']][$bulan]['tanggalkeluar']=$bar['tanggalkeluar'];
				$dt[$bar['karyawanid']][$bulan]['tipekaryawan']=$bar['tipekaryawan'];
				$dt[$bar['karyawanid']][$bulan]['statuspajak']=$bar['statuspajak'];
				$dt[$bar['karyawanid']][$bulan]['npwp']=$bar['npwp'];
				$dt[$bar['karyawanid']][$bulan]['pt']=$bar['kodeorganisasi'];
				$dt[$bar['karyawanid']][$bulan]['dept']=$bar['bagian'];
				$dt[$bar['karyawanid']][$bulan]['kodejabatan']=$bar['kodejabatan'];
				$dt[$bar['karyawanid']][$bulan]['kodegolongan']=$bar['kodegolongan'];
				$dt[$bar['karyawanid']][$bulan]['lokasitugas']=$bar['lokasitugas'];
				$dt[$bar['karyawanid']][$bulan]['alokasi']=$bar['alokasi'];
				$dt[$bar['karyawanid']][$bulan]['subbagian']=$bar['subbagian'];
				$dt[$bar['karyawanid']][$bulan]['statuskaryawan']=$bar['statuskaryawan'];
				$dt[$bar['karyawanid']][$bulan]['kppnpwp']=$bar['kppnpwp'];
				$dt[$bar['karyawanid']][$bulan]['bpjstk']=$bar['jms'];
				$dt[$bar['karyawanid']][$bulan]['bpjskes']=$bar['bpjs'];
				$dt[$bar['karyawanid']][$bulan]['pensiun']=$bar['pensiun'];
			}
		}

		$opttipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');

		$where="";
		$where.=" and kodeorg like '".$param['kodeorg']."%'";
		$where.=" and tanggal between '".$tgl1."' and '".$tgl2."'";
		$str = "select * from " . $dbname . ".sdm_absensidt where 1=1 ".$where."";
		$res = fetchData($str);
		foreach ($res as $val){
			$val['kodeorg']=substr($val['kodeorg'],0,4);
			$val['divisi']=$val['kodeorg'];

			$whk="";$wh="";$whr="";
			if($opttipe[$val['kodeorg']]=='KEBUN'){
				if(strlen($val['divisi'])==6 and $opttipe[$val['divisi']]=='BIBITAN'){
					$kdjurnal="KBNL0";
				}elseif(strlen($val['divisi'])==6 and $opttipe[$val['divisi']]=='TRAKSI'){
					$kdjurnal="VHCG0";
				}elseif(strlen($val['divisi'])==6 and $opttipe[$val['divisi']]=='WORKSHOP'){
					$kdjurnal="WSG0";
				}else{
					$kdjurnal="KBNB0";
				}
			}elseif($opttipe[$val['kodeorg']]=='PABRIK'){
				if(strlen($val['divisi'])==6 and $opttipe[$val['divisi']]=='TRAKSI'){
					$kdjurnal="VHCG0";
				}elseif(strlen($val['divisi'])==6 and $opttipe[$val['divisi']]=='WORKSHOP'){
					$kdjurnal="WSG0";
				}else{
					$kdjurnal="PKS01";
				}
			}elseif($opttipe[$val['kodeorg']]=='BULKING'){
				$kdjurnal="BLK01";
			}elseif($opttipe[$val['kodeorg']]=='RND' or $opttipe[$val['kodeorg']]=='TC'){
				$kdjurnal="RNDB0";
			}elseif($opttipe[$val['kodeorg']]=='HOLDING'){
				$kdjurnal="GJHO0";

			}

			$optakun=makeOption($dbname,'keu_5parameterjurnal','jurnalid,noakundebet',"jurnalid='".$kdjurnal."'");
			$akun=$optakun[$kdjurnal];

			if($val['noakun']=='' or is_null($val['noakun'])){
				$val['noakun']=$akun;
			}else{
				$val['noakun']=$val['noakun'];
			}

			if(substr($val['tanggal'],-2)<=15){
				$tahap='1';
			}else{
				$tahap='2';
			}

			$val['periodegaji'] = substr($val['tanggal'],0,7);
			if($datakary[$val['karyawanid']]!=''){
				$data[]=array(
					$val['periodegaji'],
					$tahap,
					$val['tanggal'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$dt[$val['karyawanid']][$val['periodegaji']]['subbagian'],
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nama'],
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$nmdept[$dt[$val['karyawanid']][$val['periodegaji']]['dept']],
					$nmgol[$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					substr($val['noakun'],0,7),
					getNamaAkun(substr($val['noakun'],0,7)),
					$val['penjelasan'],
					$val['norefrensi'],
					$val['absensi'],
					'Upah',
					$val['umr']-$val['penaltykehadiran']
				);

				$data[]=array(
					$val['periodegaji'],
					$tahap,
					$val['tanggal'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$dt[$val['karyawanid']][$val['periodegaji']]['subbagian'],
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nama'],
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$nmdept[$dt[$val['karyawanid']][$val['periodegaji']]['dept']],
					$nmgol[$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					substr($val['noakun'],0,7),
					getNamaAkun(substr($val['noakun'],0,7)),
					$val['penjelasan'],
					$val['norefrensi'],
					$val['absensi'],
					'Premi',
					$val['premi']+$val['insentif']+$val['insentiflibur']
				);

				$data[]=array(
					$val['periodegaji'],
					$tahap,
					$val['tanggal'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$dt[$val['karyawanid']][$val['periodegaji']]['subbagian'],
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nama'],
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$nmdept[$dt[$val['karyawanid']][$val['periodegaji']]['dept']],
					$nmgol[$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					substr($val['noakun'],0,7),
					getNamaAkun(substr($val['noakun'],0,7)),
					$val['penjelasan'],
					$val['norefrensi'],
					$val['absensi'],
					'HK',
					$val['hk']
				);
			}
		}
	break;
	case'aktual':
		$wh="";$whr="";
		if($param['tipekaryawan']!=''){
			$whr.=" and tipekaryawan='".$param['tipekaryawan']."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$whr=" and tipekaryawan in ('4')";
			}
		}
		if($param['kodeorg']!=''){
			$whr.=" and lokasitugas='".$param['kodeorg']."'";
			$where.=" and kodeorg='".$param['kodeorg']."'";
		}else{
			$whr.=" and lokasitugas in (".getOrgDetail(2).")";
			$where.=" and kodeorg in (".getOrgDetail(2).")";
		}
		$datae[]=array('Periode','PT','Unit','Tipe Org','Divisi','KaryID','NIK','Nama Karyawan','Jabatan','Tipe Kary','Status Pajak','NPWP','Bank','Rekening','Pemilik','Sistem Gaji','Tanggal Masuk','Tanggal Keluar','Agama','J/K','Dept','Golongan','Status Kary','KPP NPWP','BPJS TK','BPJS Kes','JP','Panen','Jenis Komponen','Komponen','Jumlah');

		$numb=array(26);

		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row     = array("Unit","Divisi");
		$col     = array("Jenis Komponen","Komponen");
		$value   = array('Jumlah');
		$datasort= array("Upah","Premi","Lembur","Insentif Tetap","Uang Transport","Extra Fooding");

		$rppanen=[];
		$str = "select karyawanid, periode, sum((rplb1+rplb2+rpbrd+kehadiran+tambahan)-denda) as rppanen from " . $dbname . ".kebun_3premipemanen where 1=1 ".$where." and periode between '".$param['periode']."' and '".$param['periode2']."' and posting='1' group by karyawanid, periode"; //exit("error$str");
		$res = fetchdata($str);
		foreach($res as $val){
			$rppanen[$val['karyawanid']][$val['periode']]+=$val['rppanen'];
		}

		$rangebulan = month_inbetween($param['periode'],$param['periode2']);
		$nourut = 0;
		foreach($rangebulan as $bulan){
			$str = "select * from " . $dbname . ".sdm_gaji where 1=1 ".$wh." and periodegaji = '".$bulan."'";
			$res = fetchdata($str);
			foreach($res as $val){
				$sql = "select * from ".$dbname.".datakaryawan_hist where 1=1 and karyawanid='".$val['karyawanid']."' and periodegaji = '".$bulan."' and version_type='B' ".$whr."";
				$req = fetchdata($sql);
				if(count($req)==0){
					$sql = "select * from ".$dbname.".datakaryawan where 1=1 and karyawanid='".$val['karyawanid']."' ".$whr."";
					$req = fetchdata($sql);
				}
				foreach($req as $bar){
					$datakary[$bar['karyawanid']][$bulan]=$bar['namakaryawan'];
					$dt[$bar['karyawanid']][$bulan]['nama']=$bar['namakaryawan'];
					$dt[$bar['karyawanid']][$bulan]['nik']=$bar['nik'];
					$dt[$bar['karyawanid']][$bulan]['jk']=$bar['jeniskelamin'];
					$dt[$bar['karyawanid']][$bulan]['agama']=$bar['agama'];
					$dt[$bar['karyawanid']][$bulan]['norek']=$bar['norekeningbank'];
					$dt[$bar['karyawanid']][$bulan]['bank']=$bar['namabank'];
					$dt[$bar['karyawanid']][$bulan]['pemilik']=$bar['pemilikrekening'];
					$dt[$bar['karyawanid']][$bulan]['sistemgaji']=$bar['sistemgaji'];
					$dt[$bar['karyawanid']][$bulan]['tanggalmasuk']=$bar['tanggalmasuk'];
					$dt[$bar['karyawanid']][$bulan]['tanggalkeluar']=$bar['tanggalkeluar'];
					$dt[$bar['karyawanid']][$bulan]['tipekaryawan']=$bar['tipekaryawan'];
					$dt[$bar['karyawanid']][$bulan]['statuspajak']=$bar['statuspajak'];
					$dt[$bar['karyawanid']][$bulan]['npwp']=$bar['npwp'];
					$dt[$bar['karyawanid']][$bulan]['pt']=$bar['kodeorganisasi'];
					$dt[$bar['karyawanid']][$bulan]['dept']=$bar['bagian'];
					$dt[$bar['karyawanid']][$bulan]['kodejabatan']=$bar['kodejabatan'];
					$dt[$bar['karyawanid']][$bulan]['kodegolongan']=$bar['kodegolongan'];
					$dt[$bar['karyawanid']][$bulan]['lokasitugas']=$bar['lokasitugas'];
					$dt[$bar['karyawanid']][$bulan]['alokasi']=$bar['alokasi'];
					$dt[$bar['karyawanid']][$bulan]['subbagian']=$bar['subbagian'];
					$dt[$bar['karyawanid']][$bulan]['statuskaryawan']=$bar['statuskaryawan'];
					$dt[$bar['karyawanid']][$bulan]['kppnpwp']=$bar['kppnpwp'];
					$dt[$bar['karyawanid']][$bulan]['bpjstk']=$bar['jms'];
					$dt[$bar['karyawanid']][$bulan]['bpjskes']=$bar['bpjs'];
					$dt[$bar['karyawanid']][$bulan]['pensiun']=$bar['pensiun'];
				}

				if($plus[$val['idkomponen']]=='0'){
					$val['jumlah']=$val['jumlah']*(-1);
					$nmcom="Pengurang";
				}else{
					$nmcom="Penambah";
					$idkomp[$val['idkomponen']]=$val['idkomponen'];
				}
				if($dt[$val['karyawanid']][$val['periodegaji']]['subbagian']==''){
					$divisi="UMUM / KANTOR";
				}else{
					$divisi=$nmorg[$dt[$val['karyawanid']][$val['periodegaji']]['subbagian']];
				}
				if($datakary[$val['karyawanid']][$val['periodegaji']]!=''){
					if($val['idkomponen']=='32' and $rppanen[$val['karyawanid']][$val['periodegaji']]>0){
						$data[]=array(
							$val['periodegaji'],
							$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
							$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
							$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
							$divisi,
							$val['karyawanid'],
							$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
							$dt[$val['karyawanid']][$val['periodegaji']]['nama'],
							$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
							$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
							$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
							$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
							$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
							$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
							$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
							$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
							$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
							$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
							$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
							$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
							$nmdept[$dt[$val['karyawanid']][$val['periodegaji']]['dept']],
							$nmgol[$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan']],
							$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
							$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
							$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
							$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
							$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
							'Panen',
							'Penambah',
							'Premi Panen',
							$rppanen[$val['karyawanid']][$val['periodegaji']]
						);
						$val['jumlah']=$val['jumlah']-$rppanen[$val['karyawanid']][$val['periodegaji']];
					}

					$data[]=array(
						$val['periodegaji'],
						$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
						$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
						$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
						$divisi,
						$val['karyawanid'],
						$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
						$dt[$val['karyawanid']][$val['periodegaji']]['nama'],
						$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
						$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
						$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
						$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
						$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
						$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
						$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
						$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
						$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
						$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
						$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
						$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
						$nmdept[$dt[$val['karyawanid']][$val['periodegaji']]['dept']],
						$nmgol[$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan']],
						$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
						$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
						$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
						$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
						$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
						'Non Panen',
						$nmcom,
						$idcomp[$val['idkomponen']],
						$val['jumlah']
					);
				}
			}
		}

		array_multisort($idkomp,SORT_ASC);
		foreach($idkomp as $komponen){
			$datasort[]=$idcomp[$komponen];
		}

	break;
	case'payroll':
		$wh="";$whr="";$whtp="";

		if($param['tipekaryawan']!=''){
			$whr.=" and tipekaryawan='".$param['tipekaryawan']."'";
			$whtp.=" and tipekaryawan='".$param['tipekaryawan']."'";
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$whr.=" and tipekaryawan in ('4')";
				$whtp.=" and tipekaryawan in ('4')";
			}
		}

		if($param['kodeorg']!=''){
			$whr.=" and lokasitugas='".$param['kodeorg']."'";
		}else{
			$whr.=" and lokasitugas in (".getOrgDetail(2).")";
		}

		$datae[]=array('Periode','PT','Unit','Tipe Org','Divisi','KaryID','NIK','Nama Karyawan','Jabatan','Tipe Kary','Status Pajak','NPWP','Bank','Rekening','Pemilik','Sistem Gaji','Tanggal Masuk','Tanggal Keluar','Agama','J/K','Dept','Golongan','Status Kary','KPP NPWP','BPJS TK','BPJS Kes','JP','Jenis Komponen','Komponen','Jumlah');

		$numb=array(26);


		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("Unit","Divisi");
		$col = array("Jenis Komponen","Komponen");
		$value = array('Jumlah');


		#= komponen yang tidak termasuk di slip gaji
		$str   = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='KOMGJEXSLP'";
		$res   = fetchdata($str);
		$exslp = $res[0]['nilai'];
		$exslip= array();
		$arrx  = explode(',', $res[0]['nilai']);
		for ($i=0; $i < count($arrx); $i++) {
			$exslip[$arrx[$i]]=$arrx[$i];
		}


		$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
		$res = fetchdata($str);
		$gjthnlu=$res[0]['nilai'];

		$rangebulan = month_inbetween($param['periode'],$param['periode2']);
		$nourut = 0;
		foreach($rangebulan as $bulan){
			$str = "select * from ".$dbname.".datakaryawan_hist where 1=1 and periodegaji = '".$bulan."' ".$whr." and version_type='B'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$karyblnini[$bar['karyawanid']]=$bar['karyawanid'];
				if($nourut==0){
					$nikkary.= "'".$bar['karyawanid']."'";
				}else{
					$nikkary.= ",'".$bar['karyawanid']."'";
				}
				$nourut++;
				$dt[$bar['karyawanid']][$bar['periodegaji']]['nama']=$bar['namakaryawan'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['nik']=$bar['nik'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['jk']=$bar['jeniskelamin'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['agama']=$bar['agama'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['norek']=$bar['norekeningbank'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['bank']=$bar['namabank'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['pemilik']=$bar['pemilikrekening'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['sistemgaji']=$bar['sistemgaji'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalmasuk']=$bar['tanggalmasuk'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalkeluar']=$bar['tanggalkeluar'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['tipekaryawan']=$bar['tipekaryawan'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['statuspajak']=$bar['statuspajak'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['npwp']=$bar['npwp'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['pt']=$bar['kodeorganisasi'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['dept']=$nmdept[$bar['bagian']];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['kodejabatan']=$bar['kodejabatan'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['kodegolongan']=$nmgol[$bar['kodegolongan']];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['lokasitugas']=$bar['lokasitugas'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['alokasi']=$bar['alokasi'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['subbagian']=$bar['subbagian'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['statuskaryawan']=$bar['statuskaryawan'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['kppnpwp']=$bar['kppnpwp'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['bpjstk']=$bar['jms'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['bpjskes']=$bar['bpjs'];
				$dt[$bar['karyawanid']][$bar['periodegaji']]['pensiun']=$bar['pensiun'];
			}

			$str = "select * from " . $dbname . ".sdm_gaji_vw where 1=1 and periodegaji = '".$bulan."' and tipekaryawan='4' ".$whr."";
			$res = fetchdata($str);
			foreach($res as $val){
				if($dt[$val['karyawanid']][$val['periodegaji']]['subbagian']==''){
					$divisi="UMUM";
				}else{
					$divisi=$nmorg[$dt[$val['karyawanid']][$val['periodegaji']]['subbagian']];
				}
				if($plus[$val['idkomponen']]=='0'){
					$val['jumlah']=$val['jumlah']*(-1);
					$nmcom="Pengurang";
				}else{
					$nmcom="Penambah";
					$idkomp[$val['idkomponen']]=$val['idkomponen'];
				}
				$data[]=array(
					$val['periodegaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$divisi,
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					($dt[$val['karyawanid']][$val['periodegaji']]['nama']==""?$val['karyawanid']:$dt[$val['karyawanid']][$val['periodegaji']]['nama']),
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['dept'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
					$nmcom,
					$idcomp[$val['idkomponen']],
					$val['jumlah']
				);
			}

			$whkar=" and karyawanid in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 and periodegaji = '".$bulan."' ".$whr." and version_type='B')";
			$str = "select * from " . $dbname . ".sdm_gaji_vw where 1=1 and tipekaryawan!='4' ".$whtp." and periodegaji = '".$bulan."' and idkomponen not in (".$gjthnlu.") and idkomponen not in(".$exslp.") ".$whkar."";
			$res = fetchdata($str);
			foreach($res as $val){
				if($dt[$val['karyawanid']][$val['periodegaji']]['subbagian']==''){
					$divisi="UMUM";
				}else{
					$divisi=$nmorg[$dt[$val['karyawanid']][$val['periodegaji']]['subbagian']];
				}
				if($plus[$val['idkomponen']]=='0'){
					$val['jumlah']=$val['jumlah']*(-1);
					$nmcom="Pengurang";
				}else{
					$nmcom="Penambah";
					$idkomp[$val['idkomponen']]=$val['idkomponen'];
				}
				$val['periodegaji']=$bulan;
				$data[]=array(
					$val['periodegaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$divisi,
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					($dt[$val['karyawanid']][$val['periodegaji']]['nama']==""?$val['karyawanid']:$dt[$val['karyawanid']][$val['periodegaji']]['nama']),
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['dept'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
					$nmcom,
					$idcomp[$val['idkomponen']],
					$val['jumlah']
				);
			}
			#komponen bulan lalu
			#$str = "select * from " . $dbname . ".sdm_gaji_vw where periodegaji = '".periodelalu($bulan)."' and idkomponen in (".$gjthnlu.") and idkomponen not in(".$exslp.") and karyawanid in (select karyawanid from " . $dbname . ".sdm_gaji_vw where 1=1 and tipekaryawan!='4' ".$whtp." and periodegaji = '".$bulan."' and idkomponen not in (".$gjthnlu.") and idkomponen not in(".$exslp.")) ".$whkar."";

			$str = "select * from " . $dbname . ".sdm_gaji_vw where periodegaji = '".periodelalu($bulan)."' and idkomponen in (".$gjthnlu.") and idkomponen not in(".$exslp.") and tipekaryawan!='4' ".$whr."";
			$res = fetchdata($str);
			foreach($res as $val){
				if($karyblnini[$val['karyawanid']]==''){
					$sql = "select * from ".$dbname.".datakaryawan_hist where 1=1 and periodegaji = '".periodelalu($bulan)."' and karyawanid='".$val['karyawanid']."' and version_type='B'";
					$req = fetchdata($sql);
					foreach($req as $bar){
						$dt[$bar['karyawanid']][$bulan]['nama']=$bar['namakaryawan'];
						$dt[$bar['karyawanid']][$bulan]['nik']=$bar['nik'];
						$dt[$bar['karyawanid']][$bulan]['jk']=$bar['jeniskelamin'];
						$dt[$bar['karyawanid']][$bulan]['agama']=$bar['agama'];
						$dt[$bar['karyawanid']][$bulan]['norek']=$bar['norekeningbank'];
						$dt[$bar['karyawanid']][$bulan]['bank']=$bar['namabank'];
						$dt[$bar['karyawanid']][$bulan]['pemilik']=$bar['pemilikrekening'];
						$dt[$bar['karyawanid']][$bulan]['sistemgaji']=$bar['sistemgaji'];
						$dt[$bar['karyawanid']][$bulan]['tanggalmasuk']=$bar['tanggalmasuk'];
						$dt[$bar['karyawanid']][$bulan]['tanggalkeluar']=$bar['tanggalkeluar'];
						$dt[$bar['karyawanid']][$bulan]['tipekaryawan']=$bar['tipekaryawan'];
						$dt[$bar['karyawanid']][$bulan]['statuspajak']=$bar['statuspajak'];
						$dt[$bar['karyawanid']][$bulan]['npwp']=$bar['npwp'];
						$dt[$bar['karyawanid']][$bulan]['pt']=$bar['kodeorganisasi'];
						$dt[$bar['karyawanid']][$bulan]['dept']=$nmdept[$bar['bagian']];
						$dt[$bar['karyawanid']][$bulan]['kodejabatan']=$bar['kodejabatan'];
						$dt[$bar['karyawanid']][$bulan]['kodegolongan']=$nmgol[$bar['kodegolongan']];
						$dt[$bar['karyawanid']][$bulan]['lokasitugas']=$bar['lokasitugas'];
						$dt[$bar['karyawanid']][$bulan]['alokasi']=$bar['alokasi'];
						$dt[$bar['karyawanid']][$bulan]['subbagian']=$bar['subbagian'];
						$dt[$bar['karyawanid']][$bulan]['statuskaryawan']=$bar['statuskaryawan'];
						$dt[$bar['karyawanid']][$bulan]['kppnpwp']=$bar['kppnpwp'];
						$dt[$bar['karyawanid']][$bulan]['bpjstk']=$bar['jms'];
						$dt[$bar['karyawanid']][$bulan]['bpjskes']=$bar['bpjs'];
						$dt[$bar['karyawanid']][$bulan]['pensiun']=$bar['pensiun'];
					}
				}


				if($dt[$val['karyawanid']][$val['periodegaji']]['subbagian']==''){
					$divisi="UMUM / KANTOR";
				}else{
					$divisi=$nmorg[$dt[$val['karyawanid']][$val['periodegaji']]['subbagian']];
				}
				if($plus[$val['idkomponen']]=='0'){
					$val['jumlah']=$val['jumlah']*(-1);
					$nmcom="Pengurang";
				}else{
					$nmcom="Penambah";
					$idkomp[$val['idkomponen']]=$val['idkomponen'];
				}
				$val['periodegaji']=$bulan;
				$data[]=array(
					$val['periodegaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pt'],
					$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas'],
					$tporg[$dt[$val['karyawanid']][$val['periodegaji']]['lokasitugas']],
					$divisi,
					$val['karyawanid'],
					$dt[$val['karyawanid']][$val['periodegaji']]['nik'],
					($dt[$val['karyawanid']][$val['periodegaji']]['nama']==""?$val['karyawanid']:$dt[$val['karyawanid']][$val['periodegaji']]['nama']),
					$nmjab[$dt[$val['karyawanid']][$val['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$val['karyawanid']][$val['periodegaji']]['tipekaryawan']],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuspajak'],
					$dt[$val['karyawanid']][$val['periodegaji']]['npwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bank'],
					$dt[$val['karyawanid']][$val['periodegaji']]['norek'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pemilik'],
					$dt[$val['karyawanid']][$val['periodegaji']]['sistemgaji'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalmasuk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['tanggalkeluar'],
					$dt[$val['karyawanid']][$val['periodegaji']]['agama'],
					$dt[$val['karyawanid']][$val['periodegaji']]['jk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['dept'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kodegolongan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['statuskaryawan'],
					$dt[$val['karyawanid']][$val['periodegaji']]['kppnpwp'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjstk'],
					$dt[$val['karyawanid']][$val['periodegaji']]['bpjskes'],
					$dt[$val['karyawanid']][$val['periodegaji']]['pensiun'],
					$nmcom,
					$idcomp[$val['idkomponen']],
					$val['jumlah']
				);
			}
		}

		array_multisort($idkomp,SORT_ASC);
		foreach($idkomp as $komponen){
			$datasort[]=$idcomp[$komponen];
		}

	break;
}

// echo"<pre>";
// print_r($rangebulan);
// echo"</pre>";
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
// }else
if($method=='payroll' or $method=='aktual' or $method=='detpayroll' or $method=='alokasi' or $method=='source' or $method=='hkdankehadiran'){
	//echo json_encode($data)."####".json_encode($row)."####".json_encode($col)."####".json_encode($value)."####".json_encode($datasort);
	echo json_encode($data)."####".json_encode($row)."####".json_encode($col)."####".json_encode($value)."####".json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;

}else{
	echo $tab;
}

function cleanSpecialChar($string) {
    // $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.
    // $string = preg_replace('/-+/',' ',$string);
    // $string = preg_replace('/\s+/', ' ', trim($string));
    // return $string;

	$hasil='';
	$hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string); //remove non-ascii chars
	return trim($hasil);
}

function clearsym($tulisan){
	$hasil='';
	$hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $tulisan); //remove non-ascii chars
	return $hasil;
}
?>
