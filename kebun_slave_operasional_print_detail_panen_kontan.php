<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$tipe=$_GET['tipe'];
$param = $_GET;

$notran = checkPostGet('notransaksi', '');
$divisi = checkPostGet('divisi', '');
$tanggal = checkPostGet('tanggal', '');
$method = checkPostGet('method', '');
$jenis = checkPostGet('jenis', '');



switch($method) {
	case'bmtbs':
	$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
	$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
	$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');
	$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

	
	$strx="select * from ".$dbname.".kebun_3premibmtbs where	tanggal='".$tanggal."' and notransaksi='".$jenis."' and divisi='".$divisi."'  and kontanan ='KONTAN' group by notransaksi";
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_ASSOC);
	while($barx=$resx->fetch()){
		$keg=$barx['kegiatan'];
		$notransaksi=$barx['notransaksi'];
		$prd=$barx['periode'];
		$unit=$barx['divisi'];
		
		# Ambil data
		$str="select * from ".$dbname.".kebun_3premibmtbs where	kegiatan = '".$keg."' and notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$karyawanid[$bar['karyawanid']][$bar['tanggal']]=$bar['karyawanid'];
			@$rplb[$bar['karyawanid']][$bar['tanggal']]+=$bar['rplb'];
			@$trplbkary[$bar['karyawanid']]+=$bar['rplb'];
			@$kgwb[$bar['karyawanid']]+=$bar['kgwb'];
			@$hk[$bar['karyawanid']]+=$bar['hk'];
			@$rphk[$bar['karyawanid']]+=$bar['rphk'];
			@$basiskg[$bar['karyawanid']]+=$bar['basiskg'];
			@$kglb[$bar['karyawanid']]+=$bar['kglb'];
			$nojurnal=$bar['jurnal'];
			
		}
		
		# ambil basis dan harga
		$str="select * from ".$dbname.".kebun_5premibkm where unit ='".$unit."' and kodekegiatan='".$keg."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$basiskghk=$bar['basis'];
			$rplbhk=$bar['premilebihbasis'];
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
				$stream.="<table class=sortable cellspacing=1>";
			}
			
			$stream.="<thead>";
			$stream.="<tr class=rowheader>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nik2']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kgwb']."</td>";
				$stream.="<td align=center rowspan=2 width=30px>".$_SESSION['lang']['jumlahhk']."</td>";
				$stream.="<td align=center rowspan=2 width=75px>Basis / HK</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['basic']." Kg</td>";
				$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['lebihbasis']." Kg</td>";
				$stream.="<td align=center rowspan=2>Rp / Kg</td>";
				$stream.="<td align=center colspan=".$col." width=75px>".$_SESSION['lang']['premlebihbasis']." Rp</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']." Rp</td>";
			$stream.="</tr>";
			
			$stream.="<tr class=rowheader>";
				foreach($jlhtgl as $jtgl => $isitgl){
				$mg = date('D', strtotime($isitgl));
					$fcolor="";
					if((@$hl[$isitgl]==$isitgl) or $mg=="Sun"){
						$fcolor=" color='red'";
					}
						$stream.="<td align=center><font ".$fcolor.">".substr($isitgl,-2)."</font></td>";
				}
			$stream.="</tr>";
			
			$stream.="</thead>";
			
			if($karyawanid==''){
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center colspan=11>".$_SESSION['lang']['errdatanotexist']."</td>";
				$stream.="</tr>";
			}

			$nokar=$no=0;
			foreach($karyawanid as $kary => $isikary){
				$nokar++;
				$stream.="<tr class=rowcontent id=row".$no.">";
				$stream.="<td align=center>".$nokar."</td>";
				$stream.="<td>".$nikkar[$kary]."</td>";
				$stream.="<td>".$nmkar[$kary]."</td>";
				$stream.="<td align=right>".@number_format($kgwb[$kary],2)."</td>";
				$stream.="<td align=right>".@number_format($hk[$kary],2)."</td>";
				$stream.="<td align=right>".@number_format($basiskghk)."</td>";
				$stream.="<td align=right>".@number_format($basiskg[$kary],2)."</td>";
				$stream.="<td align=right>".@number_format($kglb[$kary],2)."</td>";
				$stream.="<td align=right>".@number_format($rplbhk,2)."</td>";
				
				@$ttlkgwb+=$kgwb[$kary];
				@$ttlhk+=$hk[$kary];
				@$ttlrphk+=$rphk[$kary];
				@$ttlbss+=$basiskg[$kary];
				@$ttlkglb+=$kglb[$kary];
				
				foreach($jlhtgl as $jtgl => $isitgl){
					$stream.="<td align=right>".@number_format((($rplb[$kary][$isitgl])==0?'':($rplb[$kary][$isitgl])))."</td>";
					@$ttlrplbkry+=($rplb[$kary][$isitgl]);
					@$ttlrplbkrytgl[$isitgl]+=($rplb[$kary][$isitgl]);
				}
				$stream.="<td align=right>".@number_format($trplbkary[$kary])."</td>";
			}
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center colspan=3 bgcolor=cyan><b>Grand Total</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlkgwb,2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlhk,2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($basiskghk)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlbss,2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlkglb,2)."</b></td>";
				$stream.="<td align=right bgcolor=cyan><b></b></td>";
				
				foreach($jlhtgl as $jtgl => $isitgl){
					$stream.="<td align=right bgcolor=cyan><b>".@number_format(($ttlrplbkrytgl[$isitgl]==0?'':$ttlrplbkrytgl[$isitgl]))."</b></td>";
				}
				
				$stream.="<td align=right bgcolor=cyan><b>".@number_format($ttlrplbkry)."</b></td>";
				$stream.="</tr>";
				
			$stream.="</table></br>";
			
	} #tutup while
	
		echo $stream;

	break;
	case'pengawas':
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>No</td>";
        $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['jabatan']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['divisi']."</td>";
        $tab.="<td  align=center>Jenis Premi</td>";
        $tab.="<td  align=center>Premi Kotor</td>";
        $tab.="<td  align=center>Denda</td>";
        $tab.="<td  align=center>Premi Bersih</td>";
        $tab.="</tr></thead><tbody>";
        
		$wh='';
		if($jenis!=''){
			$wh=" and jabatan='".$jenis."' ";
		}
		
        $str="select * from ".$dbname.".kebun_premikemandoran where tanggalkontanan='".$tanggal."' and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where subbagian='".$divisi."') ".$wh." and kontanan='KONTAN' order by karyawanid asc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);        
        $no='';
		while($bar=$res->fetch()){
			$no++;
			$RnamaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
			$nmdiv=makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$bar['karyawanid']."'");
			$jabatan=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$bar['karyawanid']."'");
			$nmjabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatan[$bar['karyawanid']]."'");
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".tanggalnormal($bar['tanggalkontanan'])."</td>";
			$tab.="<td>".$RnamaKary[$bar['karyawanid']]."</td>";
			$tab.="<td>".$nmjabatan[$jabatan[$bar['karyawanid']]]."</td>";
			$tab.="<td>".$nmdiv[$bar['karyawanid']]."</td>";
			$tab.="<td align=left>".$bar['jabatan']."</td>";
			$tab.="<td align=right>".@number_format($bar['premikomputer'])."</td>";
			$tab.="<td align=right>".number_format($bar['denda'])."</td>";
			$tab.="<td align=right>".number_format($bar['premiinput'])."</td>";
			$tab.="</tr>";
			
			@$totJanjang+=$bar['premikomputer'];
			@$totJanjangkg+=$bar['denda'];
			@$totLuas+=$bar['premiinput'];
			
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=6 align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($totJanjang,0)."</td>";
        $tab.="<td align=right>".number_format($totJanjangkg,0)."</td>";
        $tab.="<td align=right>".number_format($totLuas)."</td>";
        $tab.="</tr></tbody></table>";
        

        echo $tab;
	break;
	case'pemanen':
		$theme=$_SESSION['theme'];
		if($theme=='skyblue' || $theme==''){
		  $men='menu.css';
		  $gen='generic.css';
		}else if($theme=='red'){
		  $men='menuRed.css';
		  $gen='genericRed.css';  
		}else{
		  $men='menuGray.css';
		  $gen='genericGray.css';  
		}               
           
        $tab="<link rel=stylesheet type=text/css href=style/".$gen.">";
        
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>No</td>";
        $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['hasilkerja']."</td>";
        $tab.="<td  align=center>Hasil Kerja<br>(Kg)</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['luas']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['upahkerja']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['upahpenalty']."</td>";        
        $tab.="<td align=center>".$_SESSION['lang']['premibasis']." (Rp)</td>";
        $tab.="<td align=center>".$_SESSION['lang']['premlebihbasis']." (Rp)</td>";
        $tab.="<td align=center>Total ".$_SESSION['lang']['upahpremi']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['rupiahpenalty']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="</tr></thead><tbody>";
        
		$str="select * from ".$dbname.".kebun_prestasi_vs_hk where kodeorg like '".$divisi."%' and tanggal='".$tanggal."' and keterangan='KONTAN' and jurnal='1'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$notr[$bar['notransaksi']]=$bar['notransaksi'];
		}
        
        $str="select * from ".$dbname.".kebun_prestasi_vw where notransaksi in ('".implode("','",$notr)."') order by karyawanid asc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);        
        $no='';
		while($bar=$res->fetch()){
				$no++;
				$bgcolor=$title=$color='';
				$strx = "select count(nik) as jmlkary, nik from " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."' and nik='".$bar['karyawanid']."' group by nik";
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$barx = $resx->fetch();
				if(($bar['karyawanid']==$barx['nik']) and ($barx['jmlkary']>1)){
					$bgcolor="style=background-color:orange;";
					$title=" title = 'Karyawan Panen lebih dari 1 blok !'";
				}
					
					$RnamaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
					
					$tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td>".$RnamaKary[$bar['karyawanid']]."</td>";
					$tab.="<td>".$bar['kodeorg']."</td>";
					$tab.="<td align=right>".$bar['hasilkerja']."</td>";
					$tab.="<td align=right>".@number_format($bar['hasilkerjakg'])."</td>";
					$tab.="<td align=right>".number_format($bar['luaspanen'],2)."</td>";
					$tab.="<td align=right>".number_format($bar['upahkerja'],0)."</td>";
					$tab.="<td align=right>".number_format($bar['upahpenalty'],0)."</td>";                
					$tab.="<td align=right>".number_format($bar['upahpremi'],0)."</td>";
					$tab.="<td align=right>".number_format($bar['upahpremilebihbasis'],0)."</td>";
					$totPremi = $bar['upahpremi'] + $bar['upahpremilebihbasis'];
					$tab.="<td align=right>".number_format($totPremi,0)."</td>";
					$tab.="<td align=right>".number_format($bar['rupiahpenalty'],0)."</td>";
					$sisa=($bar['upahkerja']-$bar['upahpenalty'])+($totPremi-$bar['rupiahpenalty']);
					$tab.="<td align=right>".number_format($sisa,0)."</td>";
                $tab.="</tr>";
                @$totJanjang+=$bar['hasilkerja'];
                @$totJanjangkg+=$bar['hasilkerjakg'];
                @$totLuas+=$bar['luaspanen'];
                @$totUpahKerja+=$bar['upahkerja'];
                @$totUpahKerjapenalty+=$bar['upahpenalty'];
                @$totUpahPremi+=$bar['upahpremi'];
                @$totUpahPremiLebihBasis+=$bar['upahpremilebihbasis'];
                @$totPremiAll+=$totPremi;
                @$totUpahDenda+=$bar['rupiahpenalty'];
                @$totSisa+=$sisa;
                
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=4 align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($totJanjang,0)."</td>";
        $tab.="<td align=right>".number_format($totJanjangkg,0)."</td>";
        $tab.="<td align=right>".number_format($totLuas,2)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerja,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerjapenalty,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahPremi,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahPremiLebihBasis,0)."</td>";
        $tab.="<td align=right>".number_format($totPremiAll,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahDenda,0)."</td>";
        $tab.="<td align=right>".number_format($totSisa,0)."</td>";
        $tab.="</tr></tbody></table>";
        

        echo $tab;
	break;
}
?>