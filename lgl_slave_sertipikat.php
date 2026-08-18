<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
$method                  = checkPostGet('method', '');
$pt                      = checkPostGet('pt', '');
$unit                    = checkPostGet('unit', '');
$unitsch                 = checkPostGet('unitsch', '');
$jenis                   = checkPostGet('jenis', '');
$jenissch                = checkPostGet('jenissch', '');
$nohak                   = checkPostGet('nohak', '');
$nonop                   = checkPostGet('nonop', '');

$nohaksch                = checkPostGet('nohaksch', '');
$luassch	             = checkPostGet('luassch', '');
$pemiliksertisch		 = checkPostGet('pemiliksertisch', '');
$thnsertisch		     = checkPostGet('thnsertisch', '');
$lokasi                  = checkPostGet('lokasi', '');
$luas                    = checkPostGet('luas', '');
$nodetail                = checkPostGet('nodetail', '');
$jenisakta               = checkPostGet('jenisakta', '');
$namadetailakta          = checkPostGet('namadetailakta', '');
$namapembeli             = checkPostGet('namapembeli', '');
$nodetailakta            = checkPostGet('nodetailakta', '');
$tgldetailakta           = tanggalsystem(checkPostGet('tgldetailakta', ''));
$nilaidetailakta         = checkPostGet('nilaidetailakta', '');
$ketdetailakta           = checkPostGet('ketdetailakta', '');
$pembuat                 = checkPostGet('pembuat', '');
$kary                    = checkPostGet('usr_id', '');
$id                      = checkPostGet('id', '');
$idfile                  = checkPostGet('idfile', '');
$nib                     = checkPostGet('nib', '');
$nosuratukur             = checkPostGet('nosuratukur', '');
$tglsrtukur              = tanggalsystem(checkPostGet('tglsrtukur', ''));
$pemiliksert             = checkPostGet('pemiliksert', '');
$ketstatushak            = checkPostGet('ketstatushak', '');
$masaberlaku             = tanggalsystem(checkPostGet('masaberlaku', ''));
$jatuhtempo              = tanggalsystem(checkPostGet('jatuhtempo', ''));
$statusbayar             = checkPostGet('statusbayar', '');
$kurangbayar             = checkPostGet('kurangbayar', '');
$denda                   = checkPostGet('denda', '');
$pbb                     = checkPostGet('pbb', '');
$idpajak                 = checkPostGet('idpajak', '');
$thnpajak                = checkPostGet('thnpajak', '');
$nospptpbb               = checkPostGet('nospptpbb', '');
$namawp                  = checkPostGet('namawp', '');
$nilaitanah              = checkPostGet('nilaitanah', '');
$nilainjoptanah          = checkPostGet('nilainjoptanah', '');
$nilaibangunan           = checkPostGet('nilaibangunan', '');
$nilainjopbangunan       = checkPostGet('nilainjopbangunan', '');
$letakobjekpajak         = checkPostGet('letakobjekpajak', '');
$keterangan              = checkPostGet('keterangan', '');
$nopeta                  = checkPostGet('nopeta', '');
$tglterbit               = tanggalsystem(checkPostGet('tglterbit', ''));
$nocek                   = checkPostGet('nocek', '');
$tglcek                  = tanggalsystem(checkPostGet('tglcek', ''));
$stts                    = checkPostGet('stts', '');
$numrow                  = checkPostGet('numrow', '');
$notransaksi             = checkPostGet('notransaksi', '');
$kepada           		 = checkPostGet('kepada', '');

$kelompok           	 = checkPostGet('kelompok', '');
$tipe           	 	 = checkPostGet('tipe', '');
$emodul 				 = 'SPBB';
$tipex                  = checkPostGet('tipex', '');



$luas                    = str_replace(',','',$luas);
$nilaidetailakta         = str_replace(',','',$nilaidetailakta);
$nilaitanah              = str_replace(',','',$nilaitanah);
$nilainjoptanah          = str_replace(',','',$nilainjoptanah);
$nilaibangunan           = str_replace(',','',$nilaibangunan);
$nilainjopbangunan       = str_replace(',','',$nilainjopbangunan);
$denda                   = str_replace(',','',$denda);
$pbb                     = str_replace(',','',$pbb);
$namafile                = checkPostGet('namafile', '');
$tipe                    = checkPostGet('tipe', '');
$divsch                  = checkPostGet('divsch', '');
$nmorg                   = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar                   = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmijin                  = makeOption($dbname, 'legal_5nama', 'kodeijin,namaijin');
$jnsserti                = makeOption($dbname, 'lgl_5jenissertipikat', 'kode,nama');
$path	                 = "fileupload/lgl_sertipikat/";
$today                   = date('Y-m-d');
$todayhis                = date('Y-m-d h:i:s');
$sts                     = array('1'=>'Aktif','0'=>'Non Aktif');

$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);

switch ($method) {
	case'cekdeskripsi':
	$str="select * from ".$dbname.".setup_blok where kodeorg like '".$unit."%'";
	$res=fetchData($str);
	foreach($res as $key=>$val){
		$tab.="<option id='".$val['kodeorg']."' value='".$val['kodeorg']."'>".$val['kodeorg']."</option>";
	}
	echo $tab;
	break;
	case'getunit':
	$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4 order by namaorganisasi asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		echo $optun;
		break;
		case'html':
		if($tipe=='html'){
			$tab= "<img src=images/excel.jpg class=resicon  title='Excel' onclick=\"viewexcel('".$pt."','".$unit."','".$jenis."','".$nohak."','".$id."','excel');\">";
		}
		$tab.= "<table>";
		$tab.= "<tr class=rowcontent>";
		$tab.= "<td>" . $_SESSION['lang']['pt'] . "</td>";
		$tab.= "<td>:</td>";
		$tab.= "<td>".$pt." ".$nmorg[$pt]."</td>";
		$tab.= "</tr>";
		$tab.= "<tr class=rowcontent>";
		$tab.= "<td>" . $_SESSION['lang']['unit'] . "</td>";
		$tab.= "<td>:</td>";
		$tab.= "<td>".$unit." ".$nmorg[$unit]."</td>";
		$tab.= "</tr></table>";
		$tab.= "<b>Status Hak</b>";
		if($tipe=='html'){
			$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
		} else{
			$tab.= "<table cellpadding=1 cellspacing=1 border=1>";
		}
		$tab.="<thead><tr class=rowheader>";
		$tab.="<td align=center >".$_SESSION['lang']['nourut']."</td>
		<td align=center>".$_SESSION['lang']['jenis']."</td>
		<td align=center>Nomor Hak</td>
		<td align=center>Nomor Nop</td>

		<td align=center>Lokasi</td>
		<td align=center>Luas</td>
		<td align=center>Masa Berlaku</td>
		<td align=center>Nomor NIB</td>
		<td align=center>Nomor<br>Surat Ukur</td>
		<td align=center>Tanggal</td>
		<td align=center>Pemilik<br>Sertifikat</td>
		<td align=center>Keterangan</td>
		</tr>
		</thead>";
		$no = 0;
		$str = "select * from " . $dbname . ".lgl_sertipikat where id='" . $id . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$lf=$xx='';
		while ($bar = $res->fetch()) {
			//$lf=" onclick=\"viewlistfile('".$bar['kodept']."','".$bar['jenis']."','".$bar['unit']."','".$bar['nohak']."')\" valign=top style=cursor:pointer";
			$lf='';
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==0){
				$xx.=" style=background-color:#F5EEF8 ";
			}
			$tab.="<tr ".$xx." class=rowcontent style=cursor:pointer>";
			$tab.="<td ".$lf." align=center>" . $no . "</td>";
			$tab.="<td ".$lf." align=left>".$bar['jenis']." - ".$jnsserti[$bar['jenis']]."</td>";
			$tab.="<td ".$lf." align=center>".$bar['nohak']."</td>";
			$tab.="<td ".$lf." align=center>".$bar['nonop']."</td>";

			$tab.="<td ".$lf." align=left>".$bar['lokasi']."</td>";
			$tab.="<td ".$lf." align=right>".hidezerodecimal($bar['luas'],2)."</td>";
			$tab.="<td ".$lf." align=center>".$bar['masaberlaku']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['nib']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['nosuratukur']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['tglsrtukur']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['pemiliksert']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['ketstatushak']."</td>";
			$tab.="</tr>";
		}
		$tab.="</table><hr>";
		$tab.= "<b>Pajak</b>";
		if($tipe=='html'){
			$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
		} else{
			$tab.= "<table cellpadding=1 cellspacing=1 border=1>";
		}
		$tab.="<thead><tr class=rowheader>";
		$tab.="
		<td align=center width=30px>No</td> 
		<td align=center>Tahun<br>Pajak</td> 
		<td align=center>Nomor<br>SPPT PBB</td> 
		<td align=center>Nama<br>Wajib Pajak</td> 
		<td align=center>Luas<br>Tanah</td> 
		<td align=center>Nilai<br>NJOP Tanah</td> 
		<td align=center>Luas<br>Bangunan</td> 
		<td align=center>Nilai<br>NJOP Bangunan</td> 
		<td align=center>PBB</td> 
		<td align=center>Denda</td> 
		<td align=center>Jatuh<br>Tempo</td> 
		<td align=center>Kurang<br>Bayar</td> 
		<td align=center>Letak<br>Objek Pajak</td> 
		<td align=center>Keterangan</td> 
		<td align=center>Status<br>Bayar</td> 

		</tr>
		</thead>";
		$no = 0;
		// $idpajak=$pt.$unit.$jenis.$nohak;
		$str = "SELECT * FROM " . $dbname . ".lgl_sertipikat_pajak
		where 1=1 and idpajak='" . $id . "' order by updatetime desc "; //exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=16 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$isi = '';
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==0){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$tab.="<tr class=rowcontent ".$xx." id=trpajak_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=center>" . $bar['thnpajak'] . "</td>";
				$tab.="<td>" . $bar['nospptpbb'] . "</td>";
				$tab.="<td>" . $bar['namawp'] . "</td>";
				$tab.="<td align=right>" . @number_format($bar['nilaitanah'],2) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['nilainjoptanah']) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['nilaibangunan'],2) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['nilainjopbangunan']) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['pbb']) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['denda']) . "</td>";
				$tab.="<td>" . $bar['jatuhtempo'] . "</td>";
				$tab.="<td align=right>" . @number_format($bar['kurangbayar']) . "</td>";
				$tab.="<td>" . $bar['letakobjekpajak'] . "</td>";
				$tab.="<td>" . $bar['keterangan'] . "</td>";
				$tab.="<td>" . $bar['statusbayar'] . "</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</table>";
		
		$tab.="<hr>";
		$tab.= "<b>Pengalihan Hak</b>";
		if($tipe=='html'){
			$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
		} else{
			$tab.= "<table cellpadding=1 cellspacing=1 border=1>";
		}
		$tab.="<thead><tr class=rowheader>";
		$tab.="
		<td align=center width=30px>No</td> 
		<td align=center>Jenis</td> 
		<td align=center>Nama Pembuat Hak</td> 
		<td align=center>Nama</td> 
		<td align=center>Nomor</td> 
		<td align=center>Tanggal</td> 
		<td align=center>Nilai</td> 
		<td align=center>Keterangan</td> 

		</tr>
		</thead>";
		$no = 0;
		// $nodetail=$pt.$unit.$jenis.$nohak;
		$str = "select * from " . $dbname . ".lgl_sertipikatdt where id='" . $id . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$lf=$xx='';
		while ($bar = $res->fetch()) {
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==0){
				$xx.=" style=background-color:#F5EEF8 ";
			}
			$tab.="<tr ".$xx." class=rowcontent style=cursor:pointer>";
			$tab.="<td ".$lf." align=center>" . $no . "</td>";
			$tab.="<td ".$lf." align=left>".$bar['jenis']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['namapembuat']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['nama']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['nomor']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['tanggal']."</td>";
			$tab.="<td ".$lf." align=right>".hidezerodecimal($bar['nilai'])."</td>";
			$tab.="<td ".$lf." align=center>".$bar['keterangan']."</td>";
		}
		$tab.="</tr>";
		$tab.="</table><hr>";
		
		
		if($tipe=='html'){
			// $tab.="<thead><tr class=rowheader>";
			// $tab.="
			// <td align='center' width=30px>No.</td>
			// <td align='center' width=100px>Jenis</td>
			// <td align='center'>Filename</td>
			// <td align='center' width=30px>Action</td>

			// </tr>
			// </thead>";
			// $no = 0;
			// 	// $id=$pt.$unit.$jenis.$nohak;
			// $no = 0;
			// $str="select * from ".$dbname.".listfile_lgl_sertipikat where idtransaksi = '".$id."' and status='1' order by jenis asc, namafile asc";
			// 	//exit('error'.$str);
			// $res=fetchData($str);
			// if(empty($res)){
			// 	$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
			// }else{
			// 	foreach($res as $key=>$val){
			// 		$no++;
			// 		$tab.="<tr class=rowcontent>
			// 		<td style='text-align:center'>".$no."</td>";
			// 		$tab.="<td align=left>".$val['jenis']."</td>";


			// 		$tab.="<td align=left>
			// 		<a href='".$path.$val['namafile']."' download>".(strlen($val['namafile'])>35?substr($val['namafile'],0,35)."...":$val['namafile'])."</a></td>";

			// 		$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['idtransaksi']."','".$val['jenis']."','".$val['id']."','".$val['namafile']."');\" >";
			// 		$tab."	</td>
			// 		</tr>";
			// 	}
			// }
			// $tab.="</tr>";
			// $tab.="</table>";
			echo $tab;
		} 
		else {
			$stream = $tab;
			$nop_ = $pt;
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
					</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/" . $nop_ . ".xls';
					</script>";
				}
				closedir($handle);
			}
		}
		break;
		case'viewlistfile':
		$tab.="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table class='sortable' cellspacing='1' border='0' style=min-width:350px>
		<thead>
		<tr class=rowheader>
		<td align='center' width=50px>No.</td>
		<td align='center' width=50px>File Type</td>
		<td align='center'>Filename</td>
		<td align='center' width=50px>Action</td>
		</tr>
		</thead>
		<tbody id='loadfilesdetail'>
		</tbody>
		</table>
		</fieldset> ";
		echo $tab;
		break;
		case'insert':
		#cek data
		$sql = "select * from " . $dbname . ".lgl_sertipikat where kodept='" . $pt . "' and unit='" . $unit . "' and jenis='" . $jenis . "' and nohak='" . $nohak . "' and lokasi='".$lokasi."'";
		$res=fetchData($sql);
		if(count($res)>0){
			exit('Warning : Data sudah ada !');
		}
		
		##ID Transaksi
		$id=$pt.$unit.$jenis.$nohak;
		$str="select max(id) as cntid from ".$dbname.".lgl_sertipikat where id like '".$id."%'";
		$res=fetchdata($str);
		$cntid=$res[0]['cntid'];
		if($cntid != ''){
			$expcntid = explode('_',$cntid);
			$id = $id."_".($expcntid[1]+1);
		}
		# Jika data sudah ada maka langsung Insert
		$str = "insert into " . $dbname . ".lgl_sertipikat (`id`,`kodept`,`unit`,`jenis`,`nohak`,`nonop`,`lokasi`,`luas`,`masaberlaku`,`createby`,`createtime`,`updateby`,`nib`,`nosuratukur`,`tglsrtukur`,`pemiliksert`,`ketstatushak`,`nopeta`,`noceksertifikat`,`tglterbitsertifikat`,`tglceksertifikat`)
		values ('".$id."','".$pt."','".$unit."','".$jenis."','".$nohak."','".$nonop."' ,'".$lokasi."','".$luas."','".$masaberlaku."','" . $_SESSION['standard']['userid'] . "','".$todayhis."','" . $_SESSION['standard']['userid'] . "','".$nib."','".$nosuratukur."','".$tglsrtukur."','".$pemiliksert."','".$ketstatushak."','".$nopeta."','".$nocek."','".$tglterbit."','".$tglcek."')";

		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		echo $id;
		break;
		case'update':
		$str = "update " . $dbname . ".lgl_sertipikat set lokasi='".$lokasi."',luas='".$luas."',masaberlaku='".$masaberlaku."',updateby='" . $_SESSION['standard']['userid'] . "', nib='".$nib."',nosuratukur='".$nosuratukur."',tglsrtukur='".$tglsrtukur."',pemiliksert='".$pemiliksert."',ketstatushak='".$ketstatushak."',
		nopeta='".$nopeta."',noceksertifikat='".$nocek."',tglterbitsertifikat='".$tglterbit."',tglceksertifikat='".$tglcek."',jenis='".$jenis."',nohak='".$nohak."',nonop='".$nonop."'  
		where kodept='".$pt."' and unit='".$unit."' and nohak='".$_POST['nohakold']."' and id='".$id."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		break;
		case'updatepajak':
		if($jatuhtempo=='00000000'){
			exit("Error : Tanggal jatuh tempo harus diisi.");
		}
		$str = "update " . $dbname . ".lgl_sertipikat_pajak set thnpajak='".$thnpajak."',nospptpbb='".$nospptpbb."',namawp='".$namawp."',updateby='" . $_SESSION['standard']['userid'] . "', letakobjekpajak='".$letakobjekpajak."',nilaitanah='".$nilaitanah."',nilaibangunan='".$nilaibangunan."',nilainjoptanah='".$nilainjoptanah."',nilainjopbangunan='".$nilainjopbangunan."',
		pbb='".$pbb."',denda='".$denda."',jatuhtempo='".$jatuhtempo."',kurangbayar='".$kurangbayar."',statusbayar='".$statusbayar."',keterangan='".$keterangan."',updateby='".$_SESSION['standard']['userid']."'
		where nospptpbb='".$nospptpbb."'";
		//exit('error'.$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		break;
		case'delete':
		$str = "delete from ".$dbname.".lgl_sertipikat where id='".$nodetail."'";
		try {$owlPDO->exec($str);
			#delete dt
			$str = "delete from " . $dbname . ".lgl_sertipikatdt where id='".$nodetail."'";
			try {$owlPDO->exec($str);
				#delete pajak
				$str = "delete from " . $dbname . ".lgl_sertipikat_pajak where idpajak='".$nodetail."'";
				try {$owlPDO->exec($str);	
					# delete file
					$sql = "select * from " . $dbname . ".listfile_lgl_sertipikat where idtransaksi='".$nodetail."'"; //exit('error'.$sql);
					$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()) {
						$str="delete from ".$dbname.".listfile_lgl_sertipikat where idtransaksi='".$nodetail."' and namafile='".$bar['namafile']."'";
						try{$owlPDO->exec($str);
							$pathx = $path.$bar['namafile'];
							unlink($pathx);
						}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
					}
				} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		break;

		case'deldetail':
		$str = "delete from " . $dbname . ".lgl_sertipikatdt where no='".$id."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		break;

		case'insertakta':
		if($nodetail==''){
			exit('Error : ID Akta Kosong');
		}
	
		# Jika data sudah ada maka langsung Insert
		$str = "insert into " . $dbname . ".lgl_sertipikatdt (`no`,`id`,`jenis`,`namapembuat`,`nama`,`namapembeli`,`nomor`,`tanggal`,`nilai`,`keterangan`,`updateby`)
		values ('','".$nodetail."','".$jenisakta."','".$pembuat."','".$namadetailakta."','".$namapembeli."','".$nodetailakta."','".$tgldetailakta."','".$nilaidetailakta."','".$ketdetailakta."','" . $_SESSION['standard']['userid'] . "')";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		break;

		case'insertpajak':
		if($idpajak==''){
			exit('Error : ID Pajak Kosong');
		}
		

		if($jatuhtempo=='00000000'){
			exit("Error : Tanggal jatuh tempo harus diisi.");
		}
		$cols = array(
			'no',
			'idpajak',
			'thnpajak',
			'nospptpbb',
			'namawp',
			'letakobjekpajak',
			'nilaitanah',
			'nilaibangunan',
			'nilainjoptanah',
			'nilainjopbangunan',
			'pbb',
			'denda',
			'jatuhtempo',
			'kurangbayar',
			'statusbayar',
			'createby',
			'createtime',
			'updateby',
			'keterangan'
		);
		$data = array(
			'no'=>'',
			'idpajak'=>$idpajak,
			'thnpajak'=>$thnpajak,
			'nospptpbb'=>$nospptpbb,
			'namawp'=>$namawp,
			'letakobjekpajak'=>$letakobjekpajak,
			'nilaitanah'=>$nilaitanah,
			'nilaibangunan'=>$nilaibangunan,
			'nilainjoptanah'=>$nilainjoptanah,
			'nilainjopbangunan'=>$nilainjopbangunan,
			'pbb'=>$pbb,
			'denda'=>$denda,
			'jatuhtempo'=>$jatuhtempo,
			'kurangbayar'=>$kurangbayar,
			'statusbayar'=>$statusbayar,
			'createby'=>$_SESSION['standard']['userid'],
			'createtime'=>$todayhis,
			'updateby'=>$_SESSION['standard']['userid'],
			'keterangan'=>$keterangan
		);
		$str = insertQuery($dbname,'lgl_sertipikat_pajak',$data,$cols);

		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		break;

		case'deldetailpajak':
		$str = "delete from " . $dbname . ".lgl_sertipikat_pajak where no='".$id."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		break;

		case'excel_list':
		$arrstatus = array('0' => 'Belum Pengajuan' , '1' => 'Disetujui' , '2' => 'Ditolak','3' => 'Proses Pengajuan');
		$where = "";
		if ($divsch != '') {
			$where.=" and kodept='" . $divsch . "' ";
			$optUnit="<option value=''></option>";
			$sData="select * from ".$dbname.".organisasi where induk='".$divsch."'";
			$rData=fetchData($sData);
			foreach ($rData as $key => $val) {
				$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";
			}
		}
		if ($jenissch != '') {
			$where.=" and jenis='" . $jenissch . "' ";
		}
		if ($unitsch != '') {
			$where.=" and unit='" . $unitsch . "' ";
		}
		if ($nohaksch != '') {
			$where.=" and nohak like '%" . $nohaksch . "%' ";
		}
		if ($luassch != '') {
			$where.=" and luas like '%" . $luassch . "%' ";
		}
		if ($pemiliksertisch != '') {
			$where.= " and pemiliksert LIKE '%".$pemiliksertisch."%'";
		}
		if (@$_POST['lokasisch'] != '') {
			$where.=" and lokasi like '%" . $_POST['lokasisch'] . "%' ";
		}
		if ($thnsertisch != '') {
			$where.= " and tglterbitsertifikat LIKE '%" . $thnsertisch . "%' ";
		}

		$sql = "SELECT count(*) jmlhrow FROM " . $dbname . ".lgl_sertipikat
		where 1=1 " . $where . " order by kodept asc";
		$rCount=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$rCount->setFetchMode(PDO::FETCH_OBJ);
		while($bCount=$rCount->fetch()){
			$jlhbrs= $bCount->jmlhrow;
		}

		$no = 0;
		$str = "SELECT * FROM " . $dbname . ".lgl_sertipikat
		where 1=1 " . $where . " order by kodept asc,updatetime desc "; 

		$tab = "";
		$tab.="<table cellpadding=5 cellspacing=1 border=1 class=sortable width=100%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['pt'] . "</td>
				<td align=center>" . $_SESSION['lang']['unit'] . "</td>
				<td align=center>" . $_SESSION['lang']['jenis'] . "</td>
				<td align=center>" . $_SESSION['lang']['nomor'] . " Hak</td>
				<td align=center>" . $_SESSION['lang']['nomor'] . " Nop</td>

				<td align=center>" . $_SESSION['lang']['lokasi'] . "</td>
				<td align=center>Luas (M2)</td>
				<td align=center>Pemilik Sertifikat</td>
				<td align=center>Tanggal Terbit Sertifikat</td>
				<td align=center>Masa Berlaku</td>
				<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
		</thead>";

		$no = $maxdisplay;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=17 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$no+=1;
			while ($bar = $res->fetch()) {
				$tab.= "<tbody> ";
				$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td>" . $bar['kodept'] . "</td>";
				$tab.="<td>" . $bar['unit'] . " - " . @$nmorg[$bar['unit']] . "</td>";
				$tab.="<td>" . $jnsserti[$bar['jenis']] . "</td>";
				$tab.="<td>" . $bar['nohak'] . "</td>";
				$tab.="<td>" . $bar['nonop'] . "</td>";
				$tab.="<td>" . $bar['lokasi'] . "</td>";
				$tab.="<td style='text-align:center'>" . number_format($bar['luas']) . "</td>";
				$tab.="<td>" . $bar['pemiliksert'] . "</td>";
				$tab.="<td style='text-align:center'>" . tanggalnormal($bar['tglterbitsertifikat']) . "</td>";
				$tab.="<td style='text-align:center'>" . tanggalnormal($bar['masaberlaku']) . "</td>";
				$tab.="</tbody><tfoot></tfoot>";
			}
		}

		$tab.="</table>";

			$nop_ = "Excel_report_sertipikattanah";
			if (strlen($tab) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $tab)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
					</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/" . $nop_ . ".xls';
					</script>";
				}
				closedir($handle);
			}
		break;


	case'loaddata':
		$arrstatus = array('0' => 'Belum Pengajuan' , '1' => 'Disetujui' , '2' => 'Ditolak','3' => 'Proses Pengajuan');
		$where = "";
		if ($divsch != '') {
			$where.=" and kodept='" . $divsch . "' ";
			$optUnit="<option value=''></option>";
			$sData="select * from ".$dbname.".organisasi where induk='".$divsch."'";
			$rData=fetchData($sData);
			foreach ($rData as $key => $val) {
				$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";
			}
		}
		if ($jenissch != '') {
			$where.=" and jenis='" . $jenissch . "' ";
		}
		if ($unitsch != '') {
			$where.=" and unit='" . $unitsch . "' ";
		}
		if ($nohaksch != '') {
			$where.=" and nohak like '%" . $nohaksch . "%' ";
		}
		if ($luassch != '') {
			$where.=" and luas like '%" . $luassch . "%' ";
		}
		if ($pemiliksertisch != '') {
			$where.= " and pemiliksert LIKE '%".$pemiliksertisch."%'";
		}
		if (@$_POST['lokasisch'] != '') {
			$where.=" and lokasi like '%" . $_POST['lokasisch'] . "%' ";
		}
		if ($thnsertisch != '') {
			$where.= " and tglterbitsertifikat LIKE '%" . $thnsertisch . "%' ";
		}
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);

		$sql = "SELECT count(*) jmlhrow FROM " . $dbname . ".lgl_sertipikat
		where 1=1 " . $where . " order by kodept asc";
		$rCount=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$rCount->setFetchMode(PDO::FETCH_OBJ);
		while($bCount=$rCount->fetch()){
			$jlhbrs= $bCount->jmlhrow;
		}


		$no = 0;
		$str = "SELECT * FROM " . $dbname . ".lgl_sertipikat
		where 1=1 " . $where . " order by kodept asc,updatetime desc limit " . $offset . "," . $limit . ""; 
		$tab = "";
		$no = $maxdisplay;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=17 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$judul = makeOption($dbname,'approval','notransaksi,komentar',"notransaksi='".$bar['id']."'");
				$isi = '';
				$no+=1;
				$a=$no%2;
				$xx='';
				if($bar['statuspersetujuan']==3){
					$xx.="style=background-color:red; title=\"".$judul[$bar['id']]."\"";
				}
				if($a==1){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td>" . $bar['kodept'] . "</td>";
				$tab.="<td>" . $bar['unit'] . " - " . @$nmorg[$bar['unit']] . "</td>";
				$tab.="<td>" . $jnsserti[$bar['jenis']] . "</td>";
				$tab.="<td>" . $bar['nohak'] . "</td>";
				$tab.="<td>" . $bar['nonop'] . "</td>";

				$tab.="<td>" . $bar['lokasi'] . "</td>";
				$tab.="<td style='text-align:center'>" . number_format($bar['luas']) . "</td>";
				$tab.="<td>" . $bar['pemiliksert'] . "</td>";
				$tab.="<td style='text-align:center'>" . tanggalnormal($bar['tglterbitsertifikat']) . "</td>";
				$tab.="<td style='text-align:center'>" . tanggalnormal($bar['masaberlaku']) . "</td>";
				$tab.="<td align=left>" . $nmkar[$bar['updateby']] . "</td>";
				
				# approval		
				$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
				
				$strX = "select * from ".$dbname.".approval where notransaksi='".$bar['id']."' and jenispersetujuan='SERTIPIKAT' order by level desc limit 1";
				$resX = $owlPDO->query($strX) or die(print " Gagal: " . PDOException::getMessage());
				$resX->setFetchMode(PDO::FETCH_ASSOC);
				$barX = $resX->fetch();
				if($barX['tanggal']==''|| $barX['tanggal']=='0000-00-00 00:00:00'){$tngl='';}else{$tngl=tanggalnormal($barX['tanggal']);}
				
				$optnmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$barX['karyawanid']."'");
				$tab.="<td onclick=getstatuspersetujuan('".$bar['id']."') style=cursor:pointer;color:blue>
				".@$optnmkary[$barX['karyawanid']]."
				<br>".@$arrHsl[$barX['status']]."
				<!--<br>".$tngl."
				<br>".$barX['komentar']."-->
				</td>";
				# end approval
				
				if($bar['posting']==0 or ($bar['posting']==1 and $bar['statuspersetujuan']==3)){
					
					$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit'
					onclick=\"edit('".$bar['kodept']."','".$bar['unit']."','".$bar['jenis']."','".$bar['nohak']."','".$bar['nonop']."','".$bar['lokasi']."','".$bar['luas']."','".tanggalnormal($bar['masaberlaku'])."','".$bar['nib']."','".$bar['nosuratukur']."','".tanggalnormal($bar['tglsrtukur'])."','".$bar['pemiliksert']."','".$bar['ketstatushak']."','".$bar['nopeta']."','".tanggalnormal($bar['tglterbitsertifikat'])."','".$bar['noceksertifikat']."','".tanggalnormal($bar['tglceksertifikat'])."','".$bar['id']."');\" ></td>";

					$isi.="<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"del('".$bar['id']."');\" title='Delete'></td>";

					$isi.="<td align=center><img src=images/upload-2-xxl.png class=resicon  title='Upload' onclick=\"showupload('event','".$bar['id']."','hak');\"></td>";
					$isi.="<td align=center></td>";
					
					
					//$isi.="<td align=center><img class=resicon src=images/skyblue/submit.jpg onclick=\"form_ajukan('".$bar['id']."','".$no."');\" title='Ajukan ???'></td>";
					
				}else{
					$isi.="<td colspan='4'></td>";
				}
				
				$isi.="<td align=center><img src=images/zoom.png class=resicon  title='View' onclick=\"html('".$bar['kodept']."','".$bar['unit']."','".$bar['jenis']."','".$bar['nohak']."','".$bar['id']."','html');\"></td>";
				
				
				$tab.=$isi;
				$tab.="</tr>";
			}
		}
		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}
		$footd = "";
		$footd.="</tr>
		<tr><td colspan=18 align=center>";
		if ($page == '0') {
			$footd.="<button class=mybutton >Prev</button>";
		} else {
			$footd.="<button class=mybutton onclick=loaddata(" .($page-1) . ");>Prev</button>";
		}
		$footd.="<select id=\"pages\" name=\"pages\" onchange=\"getPage()\">" . $isiRow . "</select>";
		if (($page + 1) == $totrows) {
			$footd.="<button class=mybutton>Next</button>";
		} else {
			$footd.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		}
		$footd.="</td>
		</tr>";
		echo $tab . "####" . $footd."####".@$optUnit;
		break;
		case'loaddataakta':
		$tab = "";
		$str = "SELECT * FROM " . $dbname . ".lgl_sertipikatdt
		where 1=1 and id='" . $nodetail . "' order by updatetime desc "; //exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=11 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$isi = '';
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==1){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td>" . $bar['jenis'] . "</td>";
				$tab.="<td>" . $bar['namapembuat'] . "</td>";
				$tab.="<td>" . $bar['nama'] . "</td>";
				$tab.="<td>" . $bar['namapembeli'] . "</td>";
				$tab.="<td>" . $bar['nomor'] . "</td>";
				$tab.="<td>" . $bar['tanggal'] . "</td>";
				$tab.="<td>" . $bar['nilai'] . "</td>";
				$tab.="<td>" . $bar['keterangan'] . "</td>";
				
			/*	$isi.="<td align=center><img src=images/upload-2-xxl.png class=resicon  title='Upload' onclick=\"showupload('event','".$bar['id']."','akta');\"></td>";*/
			$isi.="<td align=center></td>";

				$isi.="<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"deldetail('".$bar['no']."');\" title='Delete'></td>";
				
				$tab.=$isi;
				$tab.="</tr>";
			}
		}
		echo $tab;
		break;

		case'loaddatapajak':
		$tab = "";
		$str = "SELECT * FROM " . $dbname . ".lgl_sertipikat_pajak
		where 1=1 and idpajak='" . $idpajak . "' order by updatetime desc "; //exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=16 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$isi = '';
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==1){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$tab.="<tr class=rowcontent ".$xx." id=trpajak_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=center>" . $bar['thnpajak'] . "</td>";
				$tab.="<td>" . $bar['nospptpbb'] . "</td>";
				$tab.="<td>" . $bar['namawp'] . "</td>";
				$tab.="<td align=right>" . @number_format($bar['nilaitanah'],2) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['nilainjoptanah']) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['nilaibangunan'],2) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['nilainjopbangunan']) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['pbb']) . "</td>";
				$tab.="<td align=right>" . @number_format($bar['denda']) . "</td>";
				$tab.="<td>" . $bar['jatuhtempo'] . "</td>";
				$tab.="<td align=right>" . @number_format($bar['kurangbayar']) . "</td>";
				$tab.="<td>" . $bar['letakobjekpajak'] . "</td>";
				$tab.="<td>" . $bar['keterangan'] . "</td>";
				$tab.="<td>" . $bar['statusbayar'] . "</td>";
				
			/*	$isi.="<td align=center><img src=images/upload-2-xxl.png class=resicon  title='Upload' onclick=\"showupload('event','".$bar['idpajak']."','pajak');\"></td>";*/
			$isi.="<td align=center></td>";
				$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit'
				onclick=\"editpajak('".$bar['idpajak']."','".$bar['thnpajak']."','".$bar['nospptpbb']."','".$bar['namawp']."','".$bar['nilaitanah']."','".$bar['nilainjoptanah']."','".$bar['nilaibangunan']."','".$bar['nilainjopbangunan']."','".$bar['pbb']."','".$bar['denda']."','".tanggalnormal($bar['jatuhtempo'])."','".$bar['kurangbayar']."','".$bar['letakobjekpajak']."','".$bar['keterangan']."','".$bar['statusbayar']."');\" ></td>";
				$isi.="<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"deldetailpajak('".$bar['no']."','".$bar['idpajak']."');\" title='Delete'></td>";
				$tab.=$isi;
				$tab.="</tr>";
			}
		}
		echo $tab;
	break;
	
	case 'showupload':
		$tab="";
		
		$arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}
		
		$tab.="<table cellspacing='1' cellpadding=3 border='0' id='uploadpopup'>
			<tr>
				<td>No. Transaksi</td>
				<td>:</td>
				<td>
					<label id='noppupload' style='font-weight:bold'>".$param['id']."</label>
				</td>
			</tr>
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
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
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='status'></p>
					<p id='loaded_n_total'></p>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		<p />
		<hr>";
		
		$tab.="
			<div style=clear:both></div>
			<table class='sortable' cellspacing='1' border='0' cellpadding=5>
				<thead>
				<tr class=rowheader>
					<th align='center'>No.</th>
					<th align='center'>File Type</th>
					<th align='center'>Kriteria</th>
					<th align='center'>Filename</th>
					<th align='center'>Action</th>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
		
		echo $tab;
	break;
	
	case 'loadfiles':
		$no = 0;
		$tab = "";
		
		$str="select * from ".$dbname.".listfileupload where notransaksi = '".$param['id']."' and status='1'";
		$res=fetchData($str);
		if(empty($res))
		{
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		else
		{
			foreach($res as $key=>$val)
			{
				$no++;
				$tab.="<tr id='ppDetailTable' class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/lgl_sertipikat/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.png')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/lgl_sertipikat/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.pdf')
				{
					$tab.="<td style='text-align:center'>
						<img src=images/uploader/pdf.png class=resicon  title='PDF' onclick=\"getdatapdf('".$val['namafile']."')\">
					</td>";
				}
				elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/lgl_sertipikat/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}
				elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/lgl_sertipikat/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/lgl_sertipikat/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}
				
				$tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
					<td style='text-align:left'>".$val['namafile']."</td>
					<td align=center nowrap>
						<a href='fileupload/lgl_sertipikat/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if($close==0){
					$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$param['id']."','".$val['namafile']."');\" >";
				}
				if($close!=0 and $dibuat==$_SESSION['standard']['userid']){
					$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$param['id']."','".$val['namafile']."');\" >";
				}
				$tab."	</td>
				</tr>";
			}	
		}
		echo $tab;
	break;
	
	case'getdatapdf':
		$efil=$path."".$param['namafile'];
		$tab='<embed src="'.$efil.'" type="application/pdf" frameBorder="0" scrolling="auto" height="100%" width="100%"></embed>';
		
		echo $tab;
	break;
	
	case 'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = "LEGAL_FILE_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar')){
					if($_FILES['file']['size'] <= 1000000){
						$str = "insert into ".$dbname.".listfileupload values ('','".$id."','".$filename."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						try
						{
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname,"fileupload/lgl_sertipikat/$filename");
						}
						catch(PDOException $e)
						{
							echo " Gagal," . addslashes($e->getMessage());
						}
					}else{
						exit("warning : Ukuran file upload maksimal 10 MB");
					}
				}else{
					exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
				}
			}
		}
	break;
	
	case 'deletefile':
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$param['id']."' and namafile='".$param['namafile']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			#sementara tidak boleh ada unlink
			//unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	

		case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
		a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='SERTIPIKAT' and a.level='1' order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}

		$kodept=makeOption($dbname,'lgl_sertipikat','id,kodept',"id='".$id."'");
		$jenisx=makeOption($dbname,'lgl_sertipikat','id,jenis',"id='".$id."'");
		$nohakx=makeOption($dbname,'lgl_sertipikat','id,nohak',"id='".$id."'");
		$tab = "<table cellspacing=1 border=0 width=100%>
		<tr class=rowcontent hidden>
		<td width=100px>No Pengajuan</td>
		<td width=5px>:</td>
		<td id=notran_aju>".$id."</td>
		</tr>

		<tr class=rowcontent>
		<td width=100px>Nama PT</td>
		<td width=5px>:</td>
		<td >".$nmorg[$kodept[$id]]."</td>
		</tr>

		<tr class=rowcontent>
		<td width=100px>Jenis</td>
		<td width=5px>:</td>
		<td>".$jnsserti[$jenisx[$id]]."</td>
		</tr>
		<tr class=rowcontent>
		<td width=100px>Nomor</td>
		<td width=5px>:</td>
		<td>".$nohakx[$id]."</td>
		</tr>

		<tr class=rowcontent>
		<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
		<td width=5px>:</td>
		<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
		</tr>
		<tr class=rowcontent>
		<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
		<td align=LEFT><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
		</tr>				
		</table>";
		
		echo $tab;
		break;
		case'ajukan':
		try {
			$owlPDO->beginTransaction();

			if($kepada=='' or $notransaksi==''){
				throw new PDOException('Isikan nama penyetuju.');
			}

		//cari dulu apakah sudah pernah di ajukan sebelumnya
			$tglhi = date("Ymd");
			$str="select * from ".$dbname.".approval where jenispersetujuan='SERTIPIKAT' and notransaksi='".$notransaksi."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				if($bar['notransaksi']!=''){
				# jika ada pindahkan ke table ini
					$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
					$owlPDO->exec($str);
				}
			}

		#kemudian setelah di pindah, hapus persetujuan lama
			$str="delete from ".$dbname.".approval where jenispersetujuan='SERTIPIKAT' and notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);


		# update flag menjadi 1
        $str = "update " . $dbname . ".lgl_sertipikat set posting='1', statuspersetujuan='0', postingdate='" . date('Y-m-d') . "',"."postingby='" . $_SESSION['standard']['userid'] . "' where id = '" . $notransaksi . "'"; #exit("error".$str);
        $owlPDO->exec($str);

		# insert ke table approval
        $str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
        `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
        values ('','".$notransaksi."','SERTIPIKAT','1','" . $kepada."','0','','','')";
        $owlPDO->exec($str);

        $owlPDO->commit();
    } catch (PDOException $e) {
    	$owlPDO->rollback();
    	echo "Error, " . addslashes($e->getMessage());
    	die();
    }

    break;
    case'getstatuspersetujuan':

    @$countApprove = getCountApproval('SERTIPIKAT');


    $str=" select * from ".$dbname.".lgl_sertipikat where  id='".$notransaksi."' ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $bar=$res->fetch();

    $tab.= "<table border=0 cellspacing=1 class=sortable>
    <thead>
    <tr style='font-weight:bold'>
    <td style='text-align:center'>Di Buat Oleh</td>";
    for($i=1;$i<=$countApprove;$i++){
    	$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
    }

    $tab.= "</tr></thead><tbody>";
    $tab.= "<tr class=rowcontent>
    <td>".$nmkar[$bar['createby']]."<br>
    ".$bar['createtime']."</td>";
    for($i=1;$i<=$countApprove;$i++){
    	@$arrApp = detailApprove($i,$notransaksi);

    	if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
    		$tngl='';
    	}else{
    		$tngl=($arrApp['tanggal']);
    	}
    	if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
    		$tab.= "<td>".$arrApp['nama']."
    		<br />".$arrHsl[$arrApp['status']].", ".$tngl."
    		<br />".$arrApp['komentar']."
    		</td>";
    	}else{
    		$tab.= "<td>&nbsp;</td>";
    	}
    }
    $tab.= "</tbody></table>";

	#status tolak
    $str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
    $res=fetchdata($str);
    $row=count($res);
    if($row>0){
    	$no=0;
    	foreach($res as $key=>$val){
    		$no++;
    		$tab.="<br><table border=0 cellspacing=1 class=sortable>
    		<thead>
    		<tr style='font-weight:bold'>
    		<td colspan='".($val['level'])."'>Return / Tolak - ".$no."</td>
    		</tr>
    		<tr style='font-weight:bold'>";
    		for($i=1;$i<=$val['level'];$i++) {
    			$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
    		}
    		$tab.="</tr>
    		</thead>
    		<tbody>
    		<tr class=rowcontent>";
    		for($i=1;$i<=$val['level'];$i++) {
    			$strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
    			$resx=fetchdata($strx);
    			$color='';
    			if($resx[0]['status']==3){
    				$color=" style=background-color:red ";
    			}
    			$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
    			<br>	
    			".$arrHsl[$resx[0]['status']]."
    			<br>	
    			".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
    			<br>	
    			".$resx[0]['komentar']."
    			</td>";
    		}
    		$tab.="</tr>
    		</tbody>
    		</table>";
    	}
    }
    $tab.="<hr>";

    echo $tab;
    break;	

    case'viewfile':
    $tab="";
    $tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
    echo $tab;
    break;
}
	?>