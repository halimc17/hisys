<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$per=checkPostGet('per','');
$unit=checkPostGet('unit','');

$kar=checkPostGet('kar','');
$cek=checkPostGet('cek','');
$subbagian=checkPostGet('subbagian','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));

$status=makeOption($dbname,'setup_blok','kodeorg,statusblok');

switch($proses){
    case'preview':
	
	if($unit=='' || $per==''){
		exit("Warning:Lengkapi Pengisian");
	}
	

	#cek penguncian tutup buku 
	$str="SELECT tutupbuku FROM ".$dbname.".setup_periodeakuntansi where 
			periode='".$per."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['tutupbuku']==1){
		exit("Warning:Periode akuntansi sudah ditutup");
	}
	
	#cek semua bkm harus sudah posting
	$str="select count(*) as jumlah from ".$dbname.".kebun_aktifitas where 
			kodeorg='".$unit."' and tanggal like '".$per."%' 
			and (nospk='' or nospk is null) and jurnal=0";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['jumlah']>1){
		exit("Warning:Ada BKM yang belum diposting");
	}
	
	#cek semua baspk harus sudah posting
	$str="select count(*) as jumlah from ".$dbname.".log_baspk where 
			kodeblok like '".$unit."%' and tanggal like '".$per."%' 
			and statusjurnal=0";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['jumlah']>1){
		exit("Warning:Ada BASPK yang belum diposting");
	}
	
	
	#####################################################################################################################
	#####################################################################################################################
	
	
	#ambil mandor yg ada absennya di umum
	$str=" select * from ".$dbname.".sdm_absensidt_vw where 
			lokasitugas='".$unit."' and tanggal like '".$per."%' and absensi='H'
			and kodejabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan where namajabatan like '%mandor%')
			and subbagian in (select kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING') ";			
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$arrkar[$bar['karyawanid']]=$bar['karyawanid'];
		$tglsdm[$bar['karyawanid']]=$bar['tanggal'];
		$subbagiansdm[$bar['karyawanid']]=$bar['subbagian'];
	}
		
	#ambil mandor yg ada absennya dibkm
	$str=" select * from ".$dbname.".kebun_aktifitas where 
			kodeorg='".$unit."' and tanggal like '".$per."%' 
			and (nospk='' or nospk is null) and jurnal=1 and nikmandor!=''";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$karbkm[$bar['nikmandor']]=$bar['nikmandor'];
	}
	
	#nama mandor berdasarkan absen umum
	$str=" select * from ".$dbname.".datakaryawan where karyawanid in ('".implode("','",$arrkar)."')";			
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$nmkar[$bar['karyawanid']]=$bar['namakaryawan'];
	}
	
	#cek apakah di afdeling tersebut ada baspk
	$str=" select substr(kodeblok,1,6) as afd from ".$dbname.".log_baspk where kodeblok like '".$unit."%' and tanggal like '".$per."%'  ";	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$afd[$bar['afd']]=$bar['afd'];
	}	
	
	
	$stream.= "<table class=sortable cellspacing=1>";
	$stream.="<thead><tr class=rowheader>";
	$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>";  
	$stream.="       
        <td align=center>".$_SESSION['lang']['karyawanid']."</td>    
		<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
		<td align=center>".$_SESSION['lang']['subbagian']."</td>
		<td align=center>".$_SESSION['lang']['tanggal']."</td>
		<td align=center width=50px >".$_SESSION['lang']['action']." 
			<br><input type=checkbox id=cekall onclick=cekall()>
		</td>
    </tr>";
	$stream.="</thead><tbody id=content>";
	foreach(@$arrkar as $kar){
		if(@$karbkm[$kar]==''){
			if(@$subbagiansdm[$kar]==@$afd[$subbagiansdm[$kar]]){
				@$no+=1;
				$stream.="<tr class=rowcontent id=row".$no.">";
				$stream.="<td align=center>".@$no."</td>";
				$stream.="<td align=center id=kar".$no.">".$kar."</td>";
				$stream.="<td>".$nmkar[$kar]."</td>";
				$stream.="<td id=subbagian".$no.">".$subbagiansdm[$kar]."</td>";
				$stream.="<td id=tgl".$no.">".tanggalnormal($tglsdm[$kar])."</td>";
				$stream.="<td align=center><input type=checkbox id=cek".$no." ".$cek."></td>";
				$stream.="</tr>";
			}
		}
	}
	$stream.="<button class=mybutton onclick=saveall(".$no.");>".$_SESSION['lang']['proses']."</button>";
	$stream.="</table>";
		echo $stream;
    break;

    
    case'save':
		
		#ambil daftar blok yg ada baspk
		$str="SELECT * FROM ".$dbname.".log_baspk where 
				kodeblok like '".$subbagian."%' and tanggal like '".$per."%' and statusjurnal=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$kdkeg[$bar['kodeblok']]=$bar['kodekegiatan'];
			$nospk[$bar['kodeblok']]=$bar['notransaksi'];
		}
		
		// exit("Error:$cek");
		if($cek==1){
			#delete data sudah ada
			$str="delete from ".$dbname.".kebun_aktifitas 
				where nospk!='' and nikmandor='".$kar."'
				and nobkm='DATA DUMMY ALOKASI MANDOR'";
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
			foreach($kdblok as $blok){
				#=== Generate No Transaksi
				# Get Existing Data
				$tgl=str_replace("-","",$tgl);
				$fWhere = "tanggal='".$tgl."' and kodeorg='".$unit.
					"' and tipetransaksi='".$status[$blok]."'";
				$fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
				$tmpNo = fetchData($fQuery);
				
				# Generate No Transaksi
				if(count($tmpNo)==0) {
					$notran = $tgl."/".$unit."/".
					$status[$blok]."/001";
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
					$notran = $tgl."/".$unit."/".
					$status[$blok]."/".$currNo;
				}	
				
				#insert kebun_aktifitas
				$str="INSERT INTO `kebun_aktifitas` 
						(`notransaksi`, `tipetransaksi`, `tanggal`, `kodeorg`,
						`nikmandor`, `jurnal`, `nospk`, `updateby`,`nobkm`)
				VALUES ('".$notran."', '".$status[$blok]."', '".$tgl."', '".$unit."',
						'".$kar."','1','".$nospk[$blok]."','".$_SESSION['standard']['userid']."'
						,'DATA DUMMY ALOKASI MANDOR')";
						//	exit("Error:$str");
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
				
				#insert kebun_prestasi
				$str="INSERT INTO `kebun_prestasi` 
						(`notransaksi`, `nik`, `kodekegiatan`, `kodeorg`,
						`tahuntanam`)
				VALUES ('".$notran."','-','".$kdkeg[$blok]."','".$blok."',
						'0')";
							//exit("Error:$str");
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			} 
		}
    break;
	
	
	case'loaddata':
        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
		$where="";
        $str="select * from ".$dbname.".kebun_aktifitas 
		where nospk!=''	and nobkm='DATA DUMMY ALOKASI MANDOR' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrkar[$bar['nikmandor']]=$bar['nikmandor'];
			@$jlhbrs++;
		}
		
		#bentuk array nama karyawan
		if(@$jlhbrs>0){
			$str=" select * from ".$dbname.".datakaryawan where karyawanid in ('".implode("','",$arrkar)."')";			
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$nmkar[$bar['karyawanid']]=$bar['namakaryawan'];
			}
		}
        $no=0;
		$no=$maxdisplay;
		$str="select * from ".$dbname.".kebun_aktifitas 
		where nospk!=''	and nobkm='DATA DUMMY ALOKASI MANDOR' and kodeorg='".$_SESSION['empl']['lokasitugas']."'
		order by tanggal desc limit ".$offset.",".$limit." ";
		$tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            @$no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=left>".$nmkar[$bar['nikmandor']]."</td>";
			$tab.="<td align=left>".$bar['notransaksi']."</td>";
            $tab.="</tr>";
        }
        @$totrows=ceil($jlhbrs/$limit);
        if($totrows==0){
			$totrows=1;
        }
        $isiRow='';
        for($er=1;$er<=$totrows;$er++){
			$sel = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd="
            <tr><td colspan=4 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
    default;		
}
?>