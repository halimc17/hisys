<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>


<?
    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  
$tipe = checkPostGet('tipe', '');
$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$karyawanid = checkPostGet('karyawanid', '');
$tgl1 = checkPostGet('tgl1', '');
$tgl2 = checkPostGet('tgl2', '');
$tanggal = tanggalnormal(checkPostGet('tgl1', ''));
$tanggal = tanggalsystemn($tanggal);

$periode = substr($tanggal,0,7);

echo"<link rel=stylesheet type=text/css href=style/".$gen.">";

if ($tipe == 'excel') {
    $border = "border=1";
} else {
    $border = "border=0";
}

switch($proses)
{
	case'kodedenda':
	echo" Print Excel : <img style=cursor:pointer; "
	 . " onclick=\"parent.lihatdetail('excel',event)\" src=images/excel.jpg  
		title='MS.Excel'>
	   ";	
	
	$stream = "<table cellpadding=1 cellspacing=1 ".$border." class=sortable style=width:100%>
            <thead><tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
            <td align=center>" . $_SESSION['lang']['kode'] . " " . $_SESSION['lang']['denda'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center>" . $_SESSION['lang']['denda'] . " Rp</td>
            </tr></thead>";
        
		if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
			$where=" and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		}
		$no = 0;
        $str = "select * from " . $dbname . ".kebun_5dendapanen where 1=1 ".$where."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $stream.="<tr class=rowcontent>";
            $stream.="<td align=center>" . $no . "</td>";
            $stream.="<td align=center width=50px>" . $bar['kodeorg'] . "</td>";
            $stream.="<td align=center width=50px>" . $bar['kodedenda'] . "</td>";
            $stream.="<td align=left>" . $bar['deskripsi'] . "</td>";
            $stream.="<td align=left>" . $bar['jenisdenda'] . "</td>";
            $stream.="<td align=right>" . @number_format($bar['denda']) . "</td>";
        }
        
        $stream.="</table>";

	break;
	
	
	case'DetailTanggal':
	echo "sssssssssssssssss";
	break;
	
	case'DetailBlok':
	$nikkary=makeOption($dbname,'datakaryawan','karyawanid,nik',$karyawanid);
	$namakary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$karyawanid);
	$str = "select * from " . $dbname . ".kebun_5dendapanen where kodeorg='".substr($unit,0,4)."'";
	$resdenda=fetchdata($str);
	$span = count($resdenda);

	echo" Print Excel : <img style=cursor:pointer; "
	 . " onclick=\"parent.level1('".$karyawanid."','".$tgl1."','".$tgl2."','".$unit."','excel','DetailBlok',event)\" src=images/excel.jpg title='MS.Excel'>";
	$stream = "<table class=data cellpadding=1 cellspacing=1>
				<tr class=rowcontent>
					<td colspan=2>" . $_SESSION['lang']['namakaryawan'] . "</td>
					<td>:</td>
					<td colspan=5><b>".$nikkary[$karyawanid]." - ".$namakary[$karyawanid]."</b></td>
				</tr>
					<td colspan=2>" . $_SESSION['lang']['tanggal'] . "</td>
					<td>:</td>
					<td colspan=5><b>".tanggalnormal($tgl1)." s/d ".tanggalnormal($tgl2)."</b></td>
				<tr>
				</tr>";
	$stream.="</table>";
	$stream.="<table cellpadding=1 cellspacing=1 ".$border." class=sortable style=width:100%>";
	$stream.="
		<thead>
			<tr class=rowheader>
				<td align=center  rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center  rowspan='2'>" . $_SESSION['lang']['blok'] . "</td>
				<td align=center  colspan='11'>" . $_SESSION['lang']['panen'] . "</td> 
				<td align=center  colspan=".(($span)+1).">" . $_SESSION['lang']['denda'] . "</td> 
			</tr>";
	$stream.="<tr>
				<td align=center >" . $_SESSION['lang']['luas'] . "</td> 
				<td align=center >" . $_SESSION['lang']['jjg'] . "</td> 
				<td align=center >" . $_SESSION['lang']['kgwb'] . "</td> 
				<td align=center >" . $_SESSION['lang']['hk2'] . "</td> 
				<td align=center >" . $_SESSION['lang']['upah'] . "</td> 
				<td align=center width=70px>" . $_SESSION['lang']['premibasis'] . "</td> 
				<td align=center width=70px>" . $_SESSION['lang']['premlebihbasis'] . "</td> 
				<td align=center width=70px>" . $_SESSION['lang']['jumlahpremi'] . "</td> 
				<td align=center>" . $_SESSION['lang']['denda'] . "<br>".$_SESSION['lang']['karyawan']."<br>Rp</td> 
				<td align=center >" . $_SESSION['lang']['premi'] . " " . $_SESSION['lang']['dibayar'] . " Rp</td>
				<td align=center >" . $_SESSION['lang']['upah'] . " + " . $_SESSION['lang']['premi'] . " Rp</td>
				";
	foreach ($resdenda as $listdenda => $bardenda) {
			$stream.="<td align=center width=25px><b>".$bardenda['kodedenda']."</td>";
	}
			$stream.="<td align=center>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['denda']."<br>Rp</td>";
	$stream.="</tr>";
	$stream.="</thead>
	 <tbody>";
	
	######################################
	############# prepare data ###########
	######################################

	#kebun_prestasi_vs_hk
	$str = "select * from " . $dbname . ".kebun_prestasi_vs_hk where "
			. "karyawanid='" . $karyawanid . "' and tanggal between '".$tgl1."' and '".$tgl2."' order by kodeorg asc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$kdkary[$bar['karyawanid']] = $bar['karyawanid'];
		$kodeblok[$bar['kodeorg']] = $bar['kodeorg'];
		$listkary[$bar['kodeorg']] = $bar['karyawanid'];
		@$janjang[$bar['kodeorg']]+= $bar['hasilkerja'];
		@$kg[$bar['kodeorg']]+= $bar['hasilkerjakg'];
		@$totalkg+= $bar['hasilkerjakg'];
		@$upah[$bar['kodeorg']]+= $bar['tupah'];
		@$luaspanen[$bar['kodeorg']]+= $bar['luaspanen'];
		@$hk[$bar['kodeorg']]+= $bar['hkpanenperhari'];
		@$premisb[$bar['kodeorg']]+= $bar['upahpremi'];
		@$premilb[$bar['kodeorg']]+= $bar['upahpremilebihbasis'];
		@$tpremi[$bar['kodeorg']]+= $bar['tpremi'];
		@$rpenalty[$bar['kodeorg']]+= $bar['rupiahpenalty'];
	}
	
	#kebun_3premipemanen
	$str = "select * from " . $dbname . ".kebun_3premipemanen where "
			. "karyawanid='" . $karyawanid . "' and periode like '".$periode."%'"; //exit('error'.$str);
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		@$kgwb+= $bar['kgwb'];
	}


	#kebun_prestasi
	$str = "select a.* from " . $dbname . ".kebun_prestasi a
			left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
			where a.nik='" . $karyawanid . "' and b.tanggal between '".$tgl1."' and '".$tgl2."'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$penalti[$bar['kodeorg']]['A']+= $bar['penalti1'];
			@$penalti[$bar['kodeorg']]['S']+= $bar['penalti2'];
			@$penalti[$bar['kodeorg']]['M1']+= $bar['penalti3'];
			@$penalti[$bar['kodeorg']]['M2']+= $bar['penalti4'];
			@$penalti[$bar['kodeorg']]['M3']+= $bar['penalti5'];
			@$penalti[$bar['kodeorg']]['GL']+= $bar['penalti6'];
			@$penalti[$bar['kodeorg']]['PB']+= $bar['penalti7'];
			@$penalti[$bar['kodeorg']]['TP']+= $bar['penalti8'];
			@$penalti[$bar['kodeorg']]['BT']+= $bar['penalti9'];
			@$penalti[$bar['kodeorg']]['PS']+= $bar['penalti10'];
			@$penalti[$bar['kodeorg']]['X1']+= $bar['penalti11'];
			@$penalti[$bar['kodeorg']]['X2']+= $bar['penalti12'];
			@$penalti[$bar['kodeorg']]['X3']+= $bar['penalti13'];
		}

	@$where="lokasitugas='".$kdorg."'";
	$nikkary=makeOption($dbname,'datakaryawan','karyawanid,nik',$where);
	$kodetipe=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',$where);
	$namatipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

	@$jumdiv = count($kodeblok);
	if ($jumdiv > 0) {
		array_multisort($kodeblok, SORT_ASC);
	} else {
		exit("error : Data kosong");
	}

	foreach ($kodeblok as $blok) {
		$listkary[$blok]= isset($listkary[$blok]) ? $listkary[$blok] : '';
		if ($listkary[$blok] != '') {
			$no+=1;$color="";
			$kgwbkary[$blok]=($kg[$blok]/$totalkg)*$kgwb;
			$stream.="<tr class=rowcontent style=cursor:pointer; title='clickdetail' onclick=level2('".$karyawanid."','".$tgl1."','".$tgl2."','".$blok."','html','DetailTanggal',event)>
				<td align=center>".$no."</td>
				<td align=left>".$blok."</td>
				<td align=right>".@number_format($luaspanen[$blok],2)."</td>
				<td align=right>".@number_format($janjang[$blok])."</td>
				<td align=right>".@number_format($kgwbkary[$blok])."</td>
				<td align=right>".@number_format($hk[$blok],2)."</td>
				<td align=right>".@number_format($upah[$blok])."</td>
				<td align=right>".@number_format($premisb[$blok])."</td>
				<td align=right>".@number_format($premilb[$blok]+$rpenalty[$blok])."</td>
				<td align=right>".@number_format($tpremi[$blok]+$rpenalty[$blok])."</td>
				<td align=right>".@number_format($rpenalty[$blok])."</td>
				<td align=right>".@number_format($tpremi[$blok])."</td>
				<td align=right>".@number_format(($upah[$blok]+$tpremi[$blok]))."</td>
				";
				@$tluaspanen+=$luaspanen[$blok];
				@$tjanjang+=$janjang[$blok];
				@$tkg+=$kgwbkary[$blok];
				@$thk+=$hk[$blok];
				@$tupah+=$upah[$blok];
				@$tpremisb+=$premisb[$blok];
				@$tpremilb+=$premilb[$blok]+$rpenalty[$blok];
				@$ttpremi+=$tpremi[$blok]+$rpenalty[$blok];
				@$trpenalty+=$rpenalty[$blok];
				
			foreach ($resdenda as $listdenda => $bardenda) {
			$stream.="
				  <td align=right>".$penalti[$blok][$bardenda['kodedenda']]."</td>";
				  @$totaldendarp[$blok]+=$penalti[$blok][$bardenda['kodedenda']]*$bardenda['denda'];
				  @$totaldendafis[$bardenda['kodedenda']]+=$penalti[$blok][$bardenda['kodedenda']];
			}
			$stream.="<td align=right>".@number_format($totaldendarp[$blok])."</td>";
			@$gtdendarp+=$totaldendarp[$blok];
		}
	}
	$stream.="
			<tr bgcolor=#F5F5DC>
				<td align=center colspan=2>" . $_SESSION['lang']['grnd_total'] . "</td>
				<td align=right>".@number_format($tluaspanen,2)."</td>
				<td align=right>".@number_format($tjanjang)."</td>
				<td align=right>".@number_format($tkg)."</td>   
				<td align=right>".@number_format($thk,2)."</td>   
				<td align=right>".@number_format($tupah)."</td>   
				<td align=right>".@number_format($tpremisb)."</td>   
				<td align=right>".@number_format($tpremilb)."</td>   
				<td align=right>".@number_format($ttpremi)."</td>   
				<td align=right>".@number_format($trpenalty)."</td>   
				<td align=right>".@number_format($ttpremi-$trpenalty)."</td>   
				<td align=right>".@number_format(($tupah+$ttpremi)-$trpenalty)."</td>   
			";
		foreach ($resdenda as $listdenda => $bardenda) {
			$stream.="
			  <td align=right>".$totaldendafis[$bardenda['kodedenda']]."</td>
			  ";
			  
		}
	$stream.="
			  <td align=right>".@number_format($gtdendarp)."</td>
			  ";
			  
	$stream.="</tr>";
	
	$stream.="
	</tbody>
     </table>";

	break;
	
	
}

if ($tipe == 'excel') {
    $nop_ = "detail";
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
                parent.window.alert('Can't convert to excel format');
                </script>";
            exit;
        } else {
            echo "<script language=javascript1.2>
                window.location='tempExcel/" . $nop_ . ".xls';
                </script>";
        }
        fclose($handle);
    }
} else {
    echo $stream;
}
?>