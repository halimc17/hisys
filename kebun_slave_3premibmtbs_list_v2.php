<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');

$proses=checkPostGet('proses','');
$prdlist=checkPostGet('prdlist','');
$unitlist=checkPostGet('unitlist','');
$afdlist=checkPostGet('afdlist','');
$notransaksi=checkPostGet('notransaksi','');
$prd=checkPostGet('prd','');
$unit=checkPostGet('unit','');
$keg=checkPostGet('keg','');
$tipe=checkPostGet('tipe','');
$afd=checkPostGet('afd','');
$stskontan=checkPostGet('kontlist','');

$tglEntry=date('Ymh');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$jab = getPostingJabatan('premibmtbs');

switch($proses){    
	case'unposting':
	#========================= Validasi Data ===========================
	#1. Cek Prd Akuntansi
	$str="select * from ".$dbname.".setup_periodeakuntansi where periode = '".$prd."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['tutupbuku']=='1'){
		exit('Error : Periode Akuntansi Sudah di Tutup.');
	}

	#2. Cek Prd Gaji
	$str="select * from ".$dbname.".sdm_5periodegaji where periode = '".$prd."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['sudahproses']=='1'){
		exit('Error : Periode Gaji Sudah di Tutup.');
	}

	#========================= End Validasi Data ===========================

	#============================= Update ==================================
	$errorDB='';
	# Ambil no jurnal
	$queryParam = selectQuery($dbname,'kebun_3premibmtbs','distinct (jurnal) as jurnal',"notransaksi='".$notransaksi."' and kegiatan='".$keg."'");
	$resParam = fetchData($queryParam);

	# Hapus Jurnal
	$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$resParam[0]['jurnal']."' and noreferensi='".$notransaksi."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Delete Jurnal !: " . $e->getMessage() . "\n"; die();}

	#Hapus RK
	$str="select b.noreferensi,b.nojurnal from ".$dbname.".kebun_3premibmtbs a left join ".$dbname.".keu_jurnalht b on a.notransaksi=b.noreferensi where a.notransaksi = '".$notransaksi."' and a.kegiatan='".$keg."' and b.kodejurnal = 'M' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$bar['nojurnal']."' and noreferensi='".$notransaksi."'";
		try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Delete Jurnal !: " . $e->getMessage() . "\n"; die();}
	}

	# Update flag transaksi
	$str="update ".$dbname.".kebun_3premibmtbs set posting='0', jurnal = '', postingby ='".$_SESSION['standard']['userid']."', postingdate='".$tglEntry."' where notransaksi='".$notransaksi."' and kegiatan='".$keg."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Update Flag !: " . $e->getMessage() . "\n"; die();}
	
	# Jika gagal
	if ($errorDB!=''){
		exit('Error : Unposting gagal di lakukan, '.$errorDB);
	}
	#=========================== End Update ===============================
	break;

	case'view':
	# Ambil data
	$str="select * from ".$dbname.".kebun_3premibmtbs where	kegiatan = '".$keg."' and divisi = '".$afd."' and notransaksi='".$notransaksi."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$karyawanid[$bar['karyawanid']][$bar['tanggal']]=$bar['karyawanid'];
		@$rppremi[$bar['karyawanid']][$bar['tanggal']]+=$bar['rppremi'];
		@$trppremi[$bar['karyawanid']]+=$bar['rppremi'];
		@$jjgkry[$bar['karyawanid']][$bar['tanggal']]+=$bar['jjgkry'];
		@$tjjgkry[$bar['karyawanid']]+=$bar['jjgkry'];
		@$kgwb[$bar['karyawanid']][$bar['tanggal']]+=$bar['kgwb'];
		@$tkgwb[$bar['karyawanid']]+=$bar['kgwb'];
		@$hk[$bar['karyawanid']][$bar['tanggal']]+=$bar['hk'];
		@$thk[$bar['karyawanid']]+=$bar['hk'];
		@$rphk[$bar['karyawanid']][$bar['tanggal']]+=$bar['rphk'];
		@$kontanan[$bar['karyawanid']][$bar['tanggal']]=$bar['kontanan'];
		@$trphk[$bar['karyawanid']]+=$bar['rphk'];
		$nojurnal=$bar['jurnal'];
		
	}
	
	
	$str="select * from ".$dbname.".kebun_5premibmtbs where kodeorg ='".$unit."' and divisi ='".$afd."' and tanggalberlaku<='".$prd."-01'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$rpkg[$bar['kegiatan']]=$bar['harga'];
	}

	# ambil hari libur
	$str="select * from ".$dbname.".sdm_5harilibur where (kebun ='".$unit."' or kebun='GLOBAL') and tanggal like '".$prd."%' and keterangan='libur'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$hl[$bar['tanggal']]=$bar['tanggal'];
	}
	$tglTempAwal=$prd."-01";
	$tglTempAkhir=tglakhir($prd);
	$jlhtgl=rangeTanggal($tglTempAwal,$tglTempAkhir);
	$col=count($jlhtgl);
		$stream.="<table><tr><td><b>".$_SESSION['lang']['notransaksi']."</b></td><td><b>:</b></td>
						 <td><b>".$notransaksi."</b></td></tr>
						 
						 <tr><td><b>".$_SESSION['lang']['jenis']."</b></td><td><b>:</b></td>
						 <td><b>".$nmkeg[$keg]."</b></td></tr>
						 
						  <tr><td><b>".$_SESSION['lang']['nojurnal']."</b></td><td><b>:</b></td>
						 <td><b>".$nojurnal."</b></td></tr>
				  </table>";
				  
		if ($tipe == 'excel') {
			$stream.="<table class=sortable cellspacing=1 border=1>";
		} else 	{
			$stream.="<table class=sortable cellspacing=1 cellpadding=5>";
		}
		
		$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.="<td align=center rowspan=3>".$_SESSION['lang']['nourut']."</td>";
			$stream.="<td align=center rowspan=3>".$_SESSION['lang']['nik2']."</td>";
			$stream.="<td align=center rowspan=3>".$_SESSION['lang']['namakaryawan']."</td>";
			$stream.="<td align=center rowspan=3>".$_SESSION['lang']['jjg']."</td>";
			$stream.="<td align=center rowspan=3>".$_SESSION['lang']['kgwb']."</td>";
			$stream.="<td align=center rowspan=3 width=30px>".$_SESSION['lang']['jumlahhk']."</td>";
			$stream.="<td align=center rowspan=3>Rp / Kg</td>";
			$stream.="<td align=center rowspan=3>".$_SESSION['lang']['upah']." Rp</td>";
			$stream.="<td align=center rowspan=3>".$_SESSION['lang']['premi']." Rp</td>";
			$stream.="<td align=center colspan=".($col*6)." width=75px>".$_SESSION['lang']['tanggal']."</td>";
		$stream.="</tr>";
		
		$stream.="<tr class=rowheader>";
			foreach($jlhtgl as $jtgl => $isitgl){
			$mg = date('D', strtotime($isitgl));
				$fcolor="";
				if((@$hl[$isitgl]==$isitgl) or $mg=="Sun"){
					$fcolor=" color='red'";
				}
					$stream.="<td align=center colspan='6'><font ".$fcolor.">".substr($isitgl,-2)."</font></td>";
			}
		$stream.="</tr>";

		$stream.="<tr class=rowheader>";
			foreach($jlhtgl as $jtgl => $isitgl){
					$stream.="<td align=center>jjg</td>";
					$stream.="<td align=center>kgwb</td>";
					$stream.="<td align=center>HK</td>";
					$stream.="<td align=center>upah</td>";
					$stream.="<td align=center>premi</td>";
					$stream.="<td align=center>kontanan</td>";
			}
		$stream.="</tr>";
		
		$stream.="</thead>";
		
		if($karyawanid==''){
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center colspan=".(($col*5)+9).">".$_SESSION['lang']['errdatanotexist']."</td>";
			$stream.="</tr>";
		}

		$nokar=$no=0;
		foreach($karyawanid as $kary => $isikary){
			$nokar++;
			$stream.="<tr class=rowcontent id=row".$no.">";
			$stream.="<td align=center>".$nokar."</td>";
			$stream.="<td>".$nikkar[$kary]."</td>";
			$stream.="<td>".strtoupper($nmkar[$kary])."</td>";
			$stream.="<td align=right>".@number_format($tjjgkry[$kary],2)."</td>";
			$stream.="<td align=right>".@number_format($tkgwb[$kary],2)."</td>";
			$stream.="<td align=right>".@number_format($thk[$kary],2)."</td>";
			$stream.="<td align=right>".@number_format($rpkg[$keg],2)."</td>";
			$stream.="<td align=right>".@number_format($trphk[$kary],2)."</td>";
			$stream.="<td align=right>".@number_format($trppremi[$kary],2)."</td>";
			
			
			
			foreach($jlhtgl as $jtgl => $isitgl){
				$stream.="<td align=right>".@number_format((($jjgkry[$kary][$isitgl])==0?'':($jjgkry[$kary][$isitgl])),2)."</td>";
				$stream.="<td align=right>".@number_format((($kgwb[$kary][$isitgl])==0?'':($kgwb[$kary][$isitgl])),2)."</td>";
				$stream.="<td align=right>".@number_format((($hk[$kary][$isitgl])==0?'':($hk[$kary][$isitgl])),2)."</td>";
				$stream.="<td align=right>".@number_format((($rphk[$kary][$isitgl])==0?'':($rphk[$kary][$isitgl])),2)."</td>";
				$stream.="<td align=right>".@number_format((($rppremi[$kary][$isitgl])==0?'':($rppremi[$kary][$isitgl])),2)."</td>";
				$stream.="<td align=center>".$kontanan[$kary][$isitgl]."</td>";

				$tjjg_tgl[$isitgl] 		+=$jjgkry[$kary][$isitgl]; 
				$tkgwb_tgl[$isitgl] 	+=$kgwb[$kary][$isitgl]; 
				$thk_tgl[$isitgl] 		+=$hk[$kary][$isitgl]; 
				$trphk_tgl[$isitgl] 	+=$rphk[$kary][$isitgl]; 
				$trpremi_tgl[$isitgl] 	+=$rppremi[$kary][$isitgl]; 
			}
		
			$ttljjg  	+= $tjjgkry[$kary];
			$ttlkgwb 	+= $tkgwb[$kary];
			$ttlhk 		+= $thk[$kary];
			$ttlupah 	+= $trphk[$kary];
			$ttlpremi 	+= $trppremi[$kary];

		}
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center colspan=3 bgcolor=cyan><b>Grand Total</b></td>";
			$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttljjg,2)."</b></td>";
			$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlkgwb,2)."</b></td>";
			$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlhk,2)."</b></td>";
			$stream.="<td align=right bgcolor=cyan></td>";
			$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlupah,2)."</b></td>";
			$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlpremi,2)."</b></td>";

			foreach($jlhtgl as $jtgl => $isitgl){
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($tjjg_tgl[$isitgl],2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($tkgwb_tgl[$isitgl],2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($thk_tgl[$isitgl],2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($trphk_tgl[$isitgl],2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($trpremi_tgl[$isitgl],2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan></td>";
			}
			
			$stream.="</tr>";
			
		$stream.="</table></br>";
		
		if($tipe!='excel'){
			echo $stream;
		}else{
			$nop_ = "Laporan_BMTBS_" . date('Ymd_His');
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

	case 'getAfd2':
		$whr = " and kodeorganisasi IN (".getOrgDetail(26).")";
		$optafd2="<option value=''>".$_SESSION['lang']['all']."</option>";
		if ($unitlist != "") {
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unitlist."' and tipe='AFDELING' ".$whr."";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optafd2.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}
		echo $optafd2;
	break;
	
	case'loaddata':

        $where =$wh= "";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = "";
		} 
		else{
			$where = " and kodeorg IN (".getOrgDetail(2).")";
		}
		
        if ($prdlist != '') {
            $where.=" and periode='" . $prdlist . "' ";
        }
        if ($unitlist != '') {
            $where.=" and kodeorg='" . $unitlist . "' ";
            $wh.=" and unit='" . $unitlist . "' ";
        }
		if ($afdlist != '') {
			$where.=" and divisi='" . $afdlist . "' ";
		}
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$tab = "";
        $no = $maxdisplay;
		if($stskontan!='KONTAN'){
			$strx = "SELECT kodeorg, notransaksi, divisi, periode,kegiatan, sum(jjgkry) as jjgkry, sum(kgwb) as kgwb, sum(hk) as hk,sum(rphk) as rphk,sum(rppremi) as rppremi, jurnal, posting, updateby,kontanan FROM " . $dbname . ".kebun_3premibmtbs
			where 1=1 ".$where." and kontanan!='KONTAN' group by periode, kodeorg, divisi,kegiatan,kontanan order by periode desc, kodeorg asc, divisi asc,kontanan asc";
			
			$str = "SELECT kodeorg, notransaksi, divisi, periode,kegiatan, sum(jjgkry) as jjgkry, sum(kgwb) as kgwb, sum(hk) as hk,sum(rphk) as rphk,sum(rppremi) as rppremi, jurnal, posting, updateby,kontanan FROM " . $dbname . ".kebun_3premibmtbs
			where 1=1 ".$where." and kontanan!='KONTAN' group by periode, kodeorg, divisi,kegiatan,kontanan order by periode desc, kodeorg asc, divisi asc,kontanan asc limit " . $offset . "," . $limit . "";			
		}else{
			$strx = "SELECT tanggal,kodeorg, notransaksi, divisi, periode,kegiatan, sum(jjgkry) as jjgkry, sum(kgwb) as kgwb, sum(hk) as hk,sum(rphk) as rphk,sum(rppremi) as rppremi, jurnal, posting, updateby,kontanan FROM " . $dbname . ".kebun_3premibmtbs
			where 1=1 ".$where." and kontanan='KONTAN' group by notransaksi, kodeorg, divisi, periode, kontanan, tanggal order by tanggal desc,periode desc, kodeorg asc, divisi asc,kontanan asc";
			
			$str = "SELECT tanggal,kodeorg, notransaksi, divisi, periode,kegiatan, sum(jjgkry) as jjgkry, sum(kgwb) as kgwb, sum(hk) as hk,sum(rphk) as rphk,sum(rppremi) as rppremi, jurnal, posting, updateby,kontanan FROM " . $dbname . ".kebun_3premibmtbs
			where 1=1 ".$where." and kontanan='KONTAN' group by notransaksi, kodeorg, divisi, periode, kontanan, tanggal order by tanggal desc,periode desc, kodeorg asc, divisi asc,kontanan asc limit " . $offset . "," . $limit . "";			
		}
		
		
		$resttl=fetchdata($strx);
		$jlhbrs=count($resttl);
		$resx=fetchdata($str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		if($jlhbrs==''){
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=19>".$_SESSION['lang']['errdatanotexist']."</td>";
		$tab.="</tr>";
		}
	
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $no = 0;
        while ($bar = $res->fetch()) {
            $isi = $color='';
            $no+=1;
			#cek jurnal
			$str="select sum(debet) as rpj from ".$dbname.".keu_jurnaldt_vw where nojurnal = '".$bar['jurnal']."' and noreferensi='".$bar['notransaksi']."'";
			//echo $str;
			$cekj=fetchData($str);
			$rpjurnal=$cekj[0]['rpj'];
			#vs jurnal
			$valjurnal=(($bar['rphk']+$bar['rppremi']) - $rpjurnal);
			if(($valjurnal > 2 or $valjurnal < (-2)) and $bar['posting']==1){
				$notofj="Nilai di Jurnal tidak sama,<br>silahkan unposting kemudian posting ulang<br>Varian : ".number_format($valjurnal)."";
				$color=" style=background-color:red;";
			}else if($bar['posting']==1){
				$notofj="Posted";
			}else{
				$notofj="Not Posted";
			}
			
            $tab.="<tr class=rowcontent ".$color." id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=center>".$bar['periode']."</td>";
            $tab.="<td>" . $bar['notransaksi'] . "</td>";            
            $tab.="<td>[" . $bar['kodeorg'] . "] - ".getNamaOrg($bar['kodeorg'])."</td>";            
            $tab.="<td>[" . $bar['divisi'] . "] - ".getNamaOrg($bar['divisi'])."</td>";            
            $tab.="<td>" . $nmkeg[$bar['kegiatan']] . "</td>";            
            $tab.="<td>" . $bar['kontanan'] . "</td>";            
            $tab.="<td align=right>".@hidezerodecimal($bar['jjgkry'],2)."</td>";
            $tab.="<td align=right>".@hidezerodecimal($bar['kgwb'],2)."</td>";
            $tab.="<td align=right>".@hidezerodecimal($bar['hk'],2)."</td>";
            $tab.="<td align=right>".@hidezerodecimal($bar['rphk'],2)."</td>";
            $tab.="<td align=right>".@hidezerodecimal($bar['rppremi'],2) . "</td>";
            $tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";
            $tab.="<td>" . $notofj . "</td>";

            
			if ($bar['posting'] == 0) {
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['kegiatan']."','".$bar['kontanan']."');\" ></td>";
                $post='';
				if(in_array($_SESSION['empl']['jabatan'],$jab,true)){
					$post=" onclick=\"posting('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['kegiatan']."','".$bar['kontanan']."','".$no."');\" ";
				}
				$isi.="<td align=center><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30' ".$post." title='Posting' 
                     ></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab,true)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['kegiatan']."','".$no."');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$isi.="<td></td>";
                $isi.="<td align=center><img src=".$icon." class=resicon class=zImgBtn height='30' title='".$title."' ".$unpost." ></td>";
            }
            $isi.="<td align=right><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View' 
                    onclick=\"view('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['kegiatan']."','','".$bar['divisi']."');\" ></td>";
			$isi.="<td align=right><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='Excel' 
                    onclick=\"previewexcel('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['kegiatan']."','excel','".$bar['divisi']."');\" ></td>";
            $tab.=$isi;

            $tab.="</tr>";
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
                     <tr><td colspan=19 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td>
            </tr>";

	
	echo $tab . "####" . $footd;
	break;
	
    
    ######EXCEL	
	case 'excel':
		//$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="daftar_premi_mandor";
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempExcel/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
		break;
                
	default:
}
?>