<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$param = $_POST;
$method = checkPostGet('method', '');
$tanggal = tanggalSystem(checkPostGet('tanggal', ''));
$gudang = checkPostGet('gudang', '');
$txtnamabarang = checkPostGet('txtnamabarang', '');
$nodok = checkPostGet('nodok', '');
$pt = checkPostGet('pt','');
$kodeorg = checkPostGet('kodeorg','');
$penerima = checkPostGet('penerima','');
$catatan = checkPostGet('catatan','');
$kodebarang = checkPostGet('kodebarang','');
$qty = checkPostGet('qty','');
$subunit = checkPostGet('subunit','');
$blok = checkPostGet('blok','');
$mesin = checkPostGet('mesin','');
$kegiatan = checkPostGet('kegiatan','');
$satuan = checkPostGet('satuan','');
$nodoksrc = checkPostGet('nodoksrc','');
$tanggalsrc = checkPostGet('tanggalsrc','');
$kepada = checkPostGet('kepada','');
$numrow = checkPostGet('numrow','');
$level = checkPostGet('level','');
$karyawanid = checkPostGet('karyawanid','');
$dept = checkPostGet('dept','');
$qtypic = checkPostGet('qtypic','');
$qtypic=str_replace(',','',$qtypic);

@$optVTipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
@$optStatus = makeOption($dbname,'setup_blok','kodeorg,statusblok');
@$optkegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
@$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun');
@$optbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
@$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
@$optdept = makeOption($dbname,'sdm_5departemen','kode,nama');

$arrStatus = array('0'=>'perlu persetujuan','1'=>'diajukan','2'=>'disetujui','3'=>'ditolak');

switch ($method) {
	case'delpic':
		$str = "delete from " . $dbname . ".log_permintaanpicdt where notransaksi='".$nodok."' and kodebarang='".$kodebarang."' and karyawanid='".$karyawanid."' and dept='".$dept."'";
		try { $owlPDO->exec($str); } catch (PDOException $e) { print " Gagal  !: " . $e->getMessage() . "\n"; die();}
	break;
	case'datapic':
		$tab=$no="";
		$str="select * from ".$dbname.".log_permintaanpicdt where notransaksi ='".$nodok."' and kodebarang='".$kodebarang."'";
		//exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".@$optnama[($bar['karyawanid']=='0000000000'?'':$bar['karyawanid'])]."</td>";
			$tab.="<td align=left>".@$optdept[$bar['dept']]."</td>";
			$tab.="<td align=right>".$bar['qty']."</td>";
			$tab.="<td align=center>
				<img title='Delete' class=zImgBtn onclick=\"delpic('".$nodok."','".$kodebarang."','".$bar['karyawanid']."','".$bar['dept']."')\" src='images/application/application_delete.png'/></td>";
			$tab.="</tr>";
		}
		echo $tab;
	break;
	case'insertpic':
		#ambil jumlah total
		$str="select sum(jumlah) as jumlah from ".$dbname.".log_permintaandt where notransaksi ='".$nodok."' and kodebarang='".$kodebarang."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		#ambil jumlah total di pic dt
		$strv="select sum(qty) as jumlah from ".$dbname.".log_permintaanpicdt where notransaksi ='".$nodok."' and kodebarang='".$kodebarang."'";
		$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
		$resv->setFetchMode(PDO::FETCH_ASSOC);
		$barv=$resv->fetch();
		if($bar['jumlah']=='' or $bar['jumlah']==0){
			exit("Warning : Silahkan simpan permintaan terlebih dahulu !");
		}
		
		if(($barv['jumlah'] + $qtypic)> $bar['jumlah']){
			exit("Warning : Jumlah alokasi melebihi jumlah diminta.");
		}
		
		$str = "insert into " . $dbname . ".log_permintaanpicdt (`notransaksi`, `kodebarang`, `karyawanid`, `dept`, `qty`)
		values ('".$nodok."','".$kodebarang."','".$karyawanid."','".$dept."','".$qtypic."')";
			try { $owlPDO->exec($str); } catch (PDOException $e) { print " Gagal  !: " . $e->getMessage() . "\n"; die();}
	break;
	case'getpic':
		$optkary=$optdept="<option value=''></option>";
		@$whereKary.= " and statuskaryawan != 'Keluar' and tipekaryawan in (0,2,3,4,6) and (tanggalkeluar = '0000-00-00')";
		$str = "select * from ".$dbname.".datakaryawan where 1=1 and lokasitugas='".$_SESSION['empl']['lokasitugas']."' ".$whereKary." order by namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkary.="<option value='".$bar['karyawanid']."'>".$bar['nik']." - ".$bar['namakaryawan']."</option>";
		}
		
		$str = "select * from ".$dbname.".sdm_5departemen order by nama asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdept.="<option value='".$bar['kode']."'>".$bar['nama']."</option>";
		}

		$tab='';
		$tab.="<fieldset><legend>".$nodok."</legend>";
		$tab.="<table border=0 cellspacing=1 class=sortable>";
		$tab.="<thead>
				<tr>
					<td align=center style=\"width:20px;\">No</td>
					<td align=center style=\"width:150px;\">".$_SESSION['lang']['namakaryawan']."</td>
					<td align=center style=\"width:150px;\">".$_SESSION['lang']['departemen']."</td>
					<td align=center width=50px>".$_SESSION['lang']['jumlah']."</td>
					<td align=center width=50px>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>";
		$tab.="<tr class=rowcontent>
					<td align=center>#</td>
					<td><select id=karyawanid style=\"width:130px\" onchange=disdept('dept')>".$optkary."</select>
						<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'></td>
					<td><select id=dept style=\"width:130px\" onchange=disdept('kary')>".$optdept."</select>
						<img id='dept' onclick=z.elSearch('dept',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'></td>
					<td><input id=qtypic style=\"width:50px\"  onkeyup=\"z.numberFormat('qtypic',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)';>
					</td>
					<td align=center>
						<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savepic()\" src='images/save.png'/>
						<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"clearpic()\" src='images/clear.png'/>
						<img title='Refresh' class=resicon onclick=\"datapic()\" src='images/refresh2.png'/>
					</td>
				</tr>";
		$tab.="<tbody id=datapic>
			   </tbody>";
		$tab.="</table>";
		$tab.="</fieldset>";
	echo $tab;
	break;
	case'ajukan':
	
		if($kepada!='' and $nodok!=''){
			# update flag menjadi 1
			$str = "update " . $dbname . ".log_permintaanht set statuspersetujuan='1',posteddate='" . date('Y-m-d') . "',"
					. "postedby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $nodok . "'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','".$nodok."','CU','".$level."','" . $kepada."','0','','','')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		
	break;
		
	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='CU' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch())
		{
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$nodok."</td>
				</tr>";
	$countApprove = getCountApproval('CU',$kodeorg);
		$i='';
		for($i=1;$i<=$countApprove;$i++){
		if($i!=1){
			$disabled=" disabled ";
		}
			$arrApp = listApprove($i,'CU',$kodeorg);
			$optKry="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			foreach($arrApp as $key => $val){
				$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
			}
			$tab.="<tr class=rowcontent>
						<td width=100px>".$_SESSION['lang']['persetujuan']." ".$i."</td>
						<td width=5px>:</td>
						<td><select style=width:150px ".$disabled." onchange=enable(".$i.") id=kepada".$i.">".$optKry."</select></td>
					</tr>";
		}

		$tab.="<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=center><button id=tomboldetail class=mybutton onclick=ajukanall(".($i-1).")>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
	
	case'view':
	$stream='';
	$countApprove = getCountApproval('CU',$kodeorg);
	$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak']);
	
	$str=" select * from ".$dbname.".log_permintaanht where  notransaksi='".$nodok."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	
	$stream.= "<fieldset><legend><b>".$_SESSION['lang']['notransaksi']." : ".$nodok."</b></legend>
			<table border=0 cellspacing=1 class=sortable width=100%>
			<thead>
			<tr style='font-weight:bold'>
				<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$stream.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
				
	$stream.= "</tr></thead><tbody>";
	$stream.= "<tr class=rowcontent>
				<td>".$optnama[$bar['createby']]."<br>
					".$bar['createdate']."</td>";
			for($i=1;$i<=$countApprove;$i++){
				$arrApp = detailApprove($i,$nodok,'CU');
				
				if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
					$tngl='';
				}else{
					$tngl=tanggalnormal($arrApp['tanggal']);
				}
				
				if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
					$stream.= "<td>".$arrApp['nama']."
						<br />".$arrHsl[$arrApp['status']]."
						<br>".$tngl."
					</td>";
				}else{
					$stream.= "<td>&nbsp;</td>";
				}
			}
				
			
	$stream.= "</tbody>
			</table><hr>";
			
	$stream.="<table class=sortable cellspacing=1 border=0 width=100%>
			   <thead>
			   <tr class=rowheader>
			   <td align=center>No</td>
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
				<td align=center>".$_SESSION['lang']['jumlah']."</td>
				<td align=center>".$_SESSION['lang']['namapt']."</td>
				<td align=center>".$_SESSION['lang']['untukunit']."</td>
				<td align=center>".$_SESSION['lang']['subunit']."</td>
				<td align=center>".$_SESSION['lang']['blok']."</td>
				<td align=center>Kend / AB / Mesin</td>
				<td align=center>".$_SESSION['lang']['kegiatan']."</td>
			   </tr>
			   </thead>";
		   
	$str = "select * from ".$dbname.".log_permintaanht a left join ".$dbname.".log_permintaandt b on a.notransaksi=b.notransaksi where "."a.notransaksi='".$nodok."' order by b.createtime desc";
	$res = fetchData($str);
	if(count($res)<=0){
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=13 align=center>".$_SESSION['lang']['dataempty']."</td>";
		$stream.="</tr>";		
		$stream.="</table>";
	}
	$no='';
	foreach($res as $key => $val){
		$no++;
		$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td align=center>".$val['kodebarang']."</td>";
			$stream.="<td align=left>".$optbrg[$val['kodebarang']]."</td>";
			$stream.="<td align=left>".$val['satuan']."</td>";
			$stream.="<td align=right>".$val['jumlah']."</td>";
			$stream.="<td align=left>".$val['kodept']."</td>";
			$stream.="<td align=left>".$val['untukunit']."</td>";
			$stream.="<td align=left>".$val['subunit']."</td>";
			$stream.="<td align=left>".$val['kodeblok']."</td>";
			$stream.="<td align=left>".$val['kodemesin']."</td>";
			$stream.="<td align=left>".$val['kodekegiatan']."-".($optkegiatan[$val['kodekegiatan']]==''?$optakun[$val['kodekegiatan']]:$optkegiatan[$val['kodekegiatan']])."</td>";
		$stream.="</tr>";
		
	}
	$stream.="</table></fieldset>";
	
	echo $stream;
	break;
	
	case'loaddata':
	$wh='';
	if($nodoksrc!=''){
		$wh.=" and notransaksi like '%".$nodoksrc."%'";
	}
	if($tanggalsrc!=''){
		$wh.=" and tanggal like '%".$tanggalsrc."%'";
	}
	
	$stream='';
	$str = "select * from ".$dbname.".log_permintaanht where "."kodegudang='".$gudang."' ".$wh." order by notransaksi desc";
	$res = fetchData($str);
	if(count($res)<=0){
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=13 align=center>".$_SESSION['lang']['dataempty']."</td>";
		$stream.="</tr>";		
		$stream.="</table>";
	}
	$no='';
	foreach($res as $key => $val){
		$no++;
		$stream.="<tr class=rowcontent id=row_".$no.">";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td align=center>".$val['kodegudang']."</td>";
			$stream.="<td align=center>".$val['notransaksi']."</td>";
			$stream.="<td align=center>".$val['tanggal']."</td>";
			$stream.="<td align=center>".$val['untukunit']."</td>";
			$stream.="<td align=left>".@$optnama[$val['namapenerima']]."</td>";
			$stream.="<td align=left>".$val['keterangan']."</td>";
			$stream.="<td align=left>".$optnama[$val['createby']]."</td>";
			$stream.="<td align=left>".$arrStatus[$val['statuspersetujuan']]."</td>";
			
			if($val['statuspersetujuan']==0){
				$stream.="<td align=center width=25px><img src=images/application/application_edit.png class=resicon  title='Edit' 
				onclick=\"edit('".@$val['notransaksi']."','".@$val['kodept']."','".@tanggalnormal($val['tanggal'])."','".@$val['untukunit']."','".@$val['penerima']."','".@$val['catatan']."','".@$val['kodegudang']."','".$no."');\"></td>";
					
				$stream.="<td align=center width=25px><img src=images/application/application_delete.png class=resicon  title='Delete' 
				onclick=\"del('".$val['notransaksi']."','".$no."');\"></td>";
				
				$stream.="<td align=center width=25px><img src=images/skyblue/submit.jpg class=resicon  title='Ajukan' 
				onclick=\"form_ajukan('".$val['notransaksi']."','".$val['untukunit']."','".$no."');\"></td>";
				
				$stream.="<td align=center width=25px><img src=images/skyblue/zoom.png class=resicon  title='View' 
				onclick=\"view('".$val['notransaksi']."','".$no."');\"></td>";
			} else{
			$stream.="<td width=25px></td><td width=25px></td><td width=25px></td>";
			$stream.="<td align=center width=25px><img src=images/skyblue/zoom.png class=resicon  title='View' 
				onclick=\"view('".$val['notransaksi']."');\"></td>";
			}
		$stream.="</td>";
		$stream.="</tr>";
		
	}
	$stream.="</table>";
	
	echo $stream;
	break;
	
	case'del':
	    $str = "delete from " . $dbname . ".log_permintaanht where notransaksi ='".$nodok."'";
        try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
	case'deletedetail':
	    $str = "delete from " . $dbname . ".log_permintaandt where notransaksi ='".$nodok."' and kodebarang='".$kodebarang."' and subunit='".$subunit."' and kodeblok='".$blok."' and kodemesin='".$mesin."' and kodekegiatan='" . $kegiatan."'";
        try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
	case'loaddatadetail':
	$stream='';
	$str = "select * from ".$dbname.".log_permintaanht a left join ".$dbname.".log_permintaandt b on a.notransaksi=b.notransaksi where "."a.notransaksi='".$nodok."' order by b.createtime desc";
	$res = fetchData($str);
	if(count($res)<=0){
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=12 align=center>".$_SESSION['lang']['dataempty']."</td>";
		$stream.="</tr>";		
		$stream.="</table>";
	}
	$no='';
	foreach($res as $key => $val){
		$no++;
		$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td align=center>".$val['kodebarang']."</td>";
			$stream.="<td align=left>".$optbrg[$val['kodebarang']]."</td>";
			$stream.="<td align=left>".$val['satuan']."</td>";
			$stream.="<td align=right>".$val['jumlah']."</td>";
			$stream.="<td align=left>".$val['kodept']."</td>";
			$stream.="<td align=left>".$val['untukunit']."</td>";
			$stream.="<td align=left>".$val['subunit']."</td>";
			$stream.="<td align=left>".$val['kodeblok']."</td>";
			$stream.="<td align=left>".$val['kodemesin']."</td>";
			$stream.="<td align=left>".$val['kodekegiatan']."-".($optkegiatan[$val['kodekegiatan']]==''?$optakun[$val['kodekegiatan']]:$optkegiatan[$val['kodekegiatan']])."</td>";
			$stream.="<td align=center>
					<img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"deletedetail('".$nodok."','".$val['kodebarang']."','".$val['subunit']."','".$val['kodeblok']."','".$val['kodemesin']."','".$val['kodekegiatan']."','".$no."');\" >
					</td>";
		$stream.="</tr>";
		
	}
	$stream.="</table>";
	
	echo $stream;
	break;
	case 'saveItemBast':
		if($nodok=='' or $gudang=='' or $kodebarang=='' or $qty=='' or $subunit=='' or $kegiatan==''){
			exit('Error : Detail transaksi diperlukan !');
		}
		#=== insert header ===
		# Jika ht belum ada maka simpan dulu
		$sql = "select * from " . $dbname . ".log_permintaanht where "."notransaksi='".$nodok."'";
        $res = fetchData($sql);
		if(count($res)==''){
			# Simpan HT
			$str = "insert into " . $dbname . ".log_permintaanht (`notransaksi`, `tanggal`, `kodept`, `untukpt`, `keterangan`, `kodegudang`, `namapenerima`, `untukunit`,`statuspersetujuan`, `createby`,`createdate`)
			values ('".$nodok."','".$tanggal."','".$pt."','','".$catatan."','".$gudang."','".$penerima."','".$kodeorg."','0','" . $_SESSION['standard']['userid'] . "','".$tanggal."')";
			try { $owlPDO->exec($str); } catch (PDOException $e) { print " Gagal  !: " . $e->getMessage() . "\n"; die();}
			
			# Simpan DT
			$str = "insert into " . $dbname . ".log_permintaandt (`notransaksi`, `kodebarang`, `satuan`, `jumlah`, `subunit`, `kodeblok`, `kodemesin`,`kodekegiatan`)
			values ('".$nodok."','".$kodebarang."','".$satuan."','".$qty."','".$subunit."','".$blok."','".$mesin."','".$kegiatan."')";
			try { $owlPDO->exec($str); } catch (PDOException $e) { print " Gagal  !: " . $e->getMessage() . "\n"; die();}
			
		}else{
			# Update HT
			$str = "update " . $dbname . ".log_permintaanht set `namapenerima`='".$penerima."', `keterangan`='".$catatan."' where `notransaksi`='".$nodok."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

			# Simpan DT
			$str = "insert into " . $dbname . ".log_permintaandt (`notransaksi`, `kodebarang`, `satuan`, `jumlah`, `subunit`, `kodeblok`, `kodemesin`,`kodekegiatan`)
			values ('".$nodok."','".$kodebarang."','".$satuan."','".$qty."','".$subunit."','".$blok."','".$mesin."','".$kegiatan."')";
			try { $owlPDO->exec($str); } catch (PDOException $e) { print " Gagal  !: " . $e->getMessage() . "\n"; die();}
			
		}
	
	break;
	
	case'getKegiatan':
		$blok=$param['blok'];
		$jenis=$param['jenis']; #TRAKSI or BLOK
		$subunit=$param['subunit'];
		$kodeorg = checkPostGet('kodeorg',''); //untuk
		$optkeg='';
		# Jika sub unit = KEBUN / AFD blok = AFD
		$tipesubunit=(($optVTipe[$subunit]=='KEBUN' or $optVTipe[$subunit]=='AFDELING') and $optVTipe[$blok]=='AFDELING');
		if($jenis =='BLOK' AND $tipesubunit){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('KNT')  and noakun not like '8%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		
		# Jika tipe kodeorg = KEBUN or AFDELING, BIBITAN, STATION, STENGINE, MAINTENANCE
		$tipesubunit=(($optVTipe[$subunit]=='KEBUN' or $optVTipe[$subunit]=='AFDELING') and ($optVTipe[$blok]=='BLOK' and $optStatus[$blok]=='TBM'));
		if($jenis =='BLOK' AND $tipesubunit){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('TBM')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		
		# Jika tipe kodeorg = KEBUN or AFDELING, BIBITAN, STATION, STENGINE, MAINTENANCE
		$tipesubunit=(($optVTipe[$subunit]=='KEBUN' or $optVTipe[$subunit]=='AFDELING') and ($optVTipe[$blok]=='BLOK' and $optStatus[$blok]=='TB'));
		if($jenis =='BLOK' AND $tipesubunit){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('TB')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		
		# Jika tipe kodeorg = KEBUN or AFDELING, BIBITAN, STATION, STENGINE, MAINTENANCE
		$tipesubunit=(($optVTipe[$subunit]=='KEBUN' or $optVTipe[$subunit]=='AFDELING') and ($optVTipe[$blok]=='BLOK' and $optStatus[$blok]=='TM'));
		if($jenis =='BLOK' AND $tipesubunit){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('TM','PNN')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		
		# Jika sub unit = KEBUN / AFD blok = BBT
		$tipesubunit=(($optVTipe[$subunit]=='KEBUN' or $optVTipe[$subunit]=='BIBITAN') and $optVTipe[$blok]=='BIBITAN' and strlen($blok)==6);
		if($jenis =='BLOK' AND $tipesubunit){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('KNT')  and noakun not like '8%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}

		# Jika tipe kodeorg = KEBUN or BIBITAN
		$tipesubunit=(($optVTipe[$subunit]=='KEBUN' or $optVTipe[$subunit]=='BIBITAN') and $optVTipe[$blok]=='BIBITAN'  and strlen($blok)>6);
		if($jenis =='BLOK' AND $tipesubunit){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('BBT')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}

		# Jika tipe kodeorg = KEBUN
		$tipesubunit=($optVTipe[$subunit]=='KEBUN' or $optVTipe[$subunit]=='AFDELING');
		if($jenis =='undefined' AND $tipesubunit){ 
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('KNT') and noakun not like '8%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		# Jika jenis = TRAKSI sub = TRAKSI
		if($jenis =='TRAKSI' and $optVTipe[$subunit]=='TRAKSI'){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('TRK') and noakun not like '8%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		
		# Jika jenis = TRAKSI sub = WORKSHOP
		if(($jenis =='TRAKSI' and $optVTipe[$subunit]=='WORKSHOP') or ($optVTipe[$subunit]=='WORKSHOP' and $jenis =='undefined')){ 
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('WSH') and noakun not like '8%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		
		# Jika jenis = PABRIK
		if($optVTipe[$kodeorg]=='PABRIK' AND ($optVTipe[$subunit]=='STATION' OR $optVTipe[$subunit]=='PABRIK')){ 
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('MIL') and noakun not like '8%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		
		# Jika Project
		if(substr($blok,0,2)=='AK'){
			$tipeasset=substr($blok,3,2);
			$str="select akunak,namatipe from ".$dbname.".sdm_5tipeasset where kodetipe='".$tipeasset."'";    
			$resf=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_ASSOC);
			if(owlBaris($resf)>0){
				while($barf=$resf->fetch()){
					$optkeg.="<option value='".$barf['akunak']."'>[Project] - ".$barf['akunak']." ".$barf['namatipe']."</option>";
				} 
			}else{
				exit("Error: Akun aktiva dalam kontruksi belum ditentukan untuk kode ".$tipeasset);
			}   	
		}
		
		# Jika jenis = HO
		if($optVTipe[$kodeorg]=='HOLDING' and $optVTipe[$subunit]=='HOLDING'){ 
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('KNT') and noakun like '8%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		# Jika jenis = RO
		if($optVTipe[$kodeorg]=='KANWIL' and $optVTipe[$subunit]=='KANWIL'){ 
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('KNT') and noakun like '7%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		# Jika KONTRAKTOR
		$kont = makeOption($dbname,'log_5supplier','supplierid,supplierid',"supplierid='".$blok."'");
		if(count($kont)>0){
			$str="select * from ".$dbname.".keu_5akun where noakun like '21112%' and length(noakun) ='7'";  
			$resf=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_ASSOC);			
			while($barf=$resf->fetch()){
				$optkeg.="<option value='".$barf['noakun']."'>[Kontraktor] - ".$barf['noakun']." ".$barf['namaakun']."</option>";
			} 
		} 
		$tipesubunit=(($optVTipe[$subunit]=='KEBUN') and (substr($optVTipe[$blok],0,6)=='GUDANG' or $optVTipe[$blok]=='SIPIL'));
		if($jenis =='BLOK' AND $tipesubunit){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok in ('KNT')  and noakun not like '8%'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value='".$bar['kodekegiatan']."'>[".$bar['kelompok']."] ".$bar['kodekegiatan']."  ".$bar['namakegiatan']."</option>";
			}
		}
		
		// echo"<pre>";
		// // print_r($optVTipe[$subunit]); //kebun
		// // print_r($optVTipe[$blok]); //adf
		// print_r(substr($optVTipe[$blok],0,6));
		// echo"</pre>";
		// exit('error');
		
		ECHO $optkeg;
	break;
	
	case'loadBlock':
		$param=$_POST;
		$induk=$_POST['induk']; //sub unit
		$kodeorg = checkPostGet('kodeorg',''); //untuk
		
		$optVRegional = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".$kodeorg."'");
		
		$optblok=$optmsn="<option value=''></option>";
		
		# Jika tipe kodeorg = SIPIL
		if($optVTipe[$kodeorg] == 'SIPIL'){
			$str="select norumah,keterangan from ".$dbname.".sdm_perumahanht where kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optVRegional[$kodeorg]."') order by norumah ASC"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optblok.="<option value='".$bar['norumah']."'>".$bar['norumah']." ".$bar['keterangan']."</option>";
			}
		}
		
		# Jika tipe kodeorg = KEBUN or PABRIK or KANWIL or HOLDING and tipe = TRAKSI, WORKSHOP 
		$tipekebun	=($optVTipe[$induk] == 'TRAKSI' or $optVTipe[$induk] == 'WORKSHOP');
		$tipekodeorg=($optVTipe[$kodeorg]=='KEBUN' or $optVTipe[$kodeorg]=='KANWIL' or 
					  $optVTipe[$kodeorg]=='PABRIK' or $optVTipe[$kodeorg]=='HOLDING');
		if($tipekodeorg and $tipekebun){
			$str="select * from ".$dbname.".vhc_5master where kodeorg='".$kodeorg."' and status='1' order by kodetraksi,kodevhc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optmsn.="<option value='".$bar['kodevhc']."'>".$bar['kodetraksi']." : ".$bar['kodevhc']."  ".$bar['nopol']."</option>";
			}
		}
		# Jika tipe kodeorg = KEBUN or PABRIK and tipe = AFDELING, BIBITAN, STATION, STENGINE, MAINTENANCE
		$tipekebun=($optVTipe[$induk] == 'KEBUN' or $optVTipe[$induk] == 'AFDELING' or $optVTipe[$induk] == 'BIBITAN' or 			$optVTipe[$induk] == 'PABRIK' or $optVTipe[$induk] == 'STATION' or $optVTipe[$induk] == 'STENGINE' or
					$optVTipe[$induk] == 'MAINTENANCE');
		$tipekodeorg=($optVTipe[$kodeorg]=='KEBUN' or $optVTipe[$kodeorg]=='PABRIK');
		if($tipekodeorg and $tipekebun){
			$str="select * from ".$dbname.".organisasi where 1=1 and induk like '".$induk."%' order by length(kodeorganisasi) asc, kodeorganisasi ASC"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optblok.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}
		
		# Jika induk = External / KONTRAKTOR
		$str = "select * from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where 1=1  and a.tipe='KONTRAKTOR' "; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if(($bar['supplierid']) == $induk){
				$optblok.="<option value='".$bar['supplierid']."'  selected >[".$bar['supplierid']."] ".$bar['namasupplier']."</option>";
			}
		}

		# Jika induk = Project
		if(substr($induk,0,2)=='AK' or substr($induk,0,2)=='PB'){
		$optblok='';
			$str="select kode,nama from ".$dbname.".project where kode='".$induk."'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				if(($bar['kode']) == $induk){
					$optblok.="<option value='".$bar['kode']."' selected>".$bar['kode']."-".$bar['nama']."</option>";
				}else{
					$optblok.="<option value='".$bar['kode']."'>".$bar['kode']."-".$bar['kode']."</option>";
				}
			}
		}

		
	echo $optblok."####".$optmsn;
	break;
	case'goCariBarang':
		$str="select a.kodebarang,a.namabarang,a.satuan from ".$dbname.".log_5masterbarang a where (a.namabarang like '%".$txtnamabarang."%' or kodebarang like '%".$txtnamabarang."%') and kodebarang not like '8%' order by kodebarang asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		
		echo"<hr><table class=sortable cellspacing=1 border=0 style=width:100%>
		     <thead>
			      <tr class=rowheader>
				      <td align=center>No</td>
					  <td align=center>".$_SESSION['lang']['kodebarang']."</td>
					  <td align=center>".$_SESSION['lang']['namabarang']."</td>
					  <td align=center>".$_SESSION['lang']['satuan']."</td>
				  </tr>
		     </thead>
			 <tbody>";
		if(owlBaris($res)<1){
			echo"<tr class=rowcontent>
				     <td align=center colspan=5>".$_SESSION['lang']['tidakditemukan']."</td>
				 </tr>";
		}
			$no=0;	 
			while($bar=$res->fetch()){
				
				$no+=1;
				echo"<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"loadField('".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."');\">
				  <td align=center>".$no."</td>
				  <td align=center>".$bar->kodebarang."</td>
				  <td>".$bar->namabarang."</td>
				  <td>".$bar->satuan."</td>
			      </tr>";			   	
			   
			}
		echo"	 </tbody>
				 <tfoot></tfoot>
				 </table>";	
	break;
	case 'getNotransaksi':
		
		if($tanggal<$_SESSION['org']['period']['start']){
			exit('Error : Tanggal transaksi tidak boleh lebih kecil dari tanggal awal periode aktif.');
		} 		
		
		# bentuk nomor transaksi
		$unit=$_SESSION['empl']['lokasitugas'];
		$tanggal=tanggalSystemn($_POST['tanggal']);
		$tmpTgl = explode('-',$tanggal);
		$notran=$tmpTgl[0].$tmpTgl[1];
        $str="select max(substr(notransaksi,7,5)) as nomorurut from ".$dbname.".log_permintaanht where substr(notransaksi,1,6) = '".$notran."' and untukunit='".$unit."' order by substr(notransaksi,1,6) desc";
		
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        
        if(intval($bar['nomorurut'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nomorurut'])+1;
        }
        $notran=$notran.addZero($noawal,5)."-CU-".$unit;
		echo trim($notran);
	
	break;
	
    case'getPT':
		$optDivisi='';
		$str="select * from ".$dbname.".organisasi where kodeorganisasi = '".substr($param['gudang'],0,4)."'";
		$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$resstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $resstr->fetch()) {
			$optDivisi.=$res['induk'];
		}	

		if($_SESSION['empl']['subbagian']==''){
			$untuk = $_SESSION['empl']['lokasitugas'];
			$wh =" and kodeorganisasi like '".$untuk."%'";
		} else {
			$untuk = $_SESSION['empl']['subbagian'];
			$wh =" and kodeorganisasi like '".$untuk."%'";
		}
		
		# ==== option subunit ====
		# unit estate / pabrik
		$optsubunit="<option value=''></option>";
		$str = "select * from ".$dbname.".organisasi where 1=1 ".$wh."  and length(kodeorganisasi)<=6 and tipe in('AFDELING','PABRIK','PT','HOLDING','TRAKSI','WORKSHOP','STATION','BIBITAN') order by kodeorganisasi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsubunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		# project
		$str = "select * from ".$dbname.".project where 1=1  and kodeorg='".$_SESSION['empl']['lokasitugas']."' and posting='0' order by kode asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsubunit.="<option value='".$bar['kode']."'>Project : [".$bar['kode']."] ".$bar['nama']."</option>";
		}

		# kontraktor
		$str = "select * from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where 1=1  and a.tipe='KONTRAKTOR' "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsubunit.="<option value='".$bar['supplierid']."'>Kontraktor : [".$bar['supplierid']."] ".$bar['namasupplier']."</option>";
		}


		echo $optDivisi."####".$optsubunit;
	break;
}
?>	