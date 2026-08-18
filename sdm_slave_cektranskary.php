<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses   = checkPostGet('proses', '');
$kdorg    = checkPostGet('kdorg', '');
$pt       = checkPostGet('pt', '');
$tt       = checkPostGet('tt', '');
$ip       = checkPostGet('ip', '');
$divisi   = checkPostGet('divisi', '');
$prd      = checkPostGet('prd', '');
$tipe      = checkPostGet('tipe', '');
$karyawanid      = checkPostGet('karyawanid', '');

if($proses=='preview' or $proses=='excel'){
	if($pt==''){exit("warning : Kode PT harus di pilih !!!");}

	$where="";
	if($kdorg!=''){
		$where.=" and lokasitugas='".$kdorg."'";
	}
	if($tipe!=''){
		$where.=" and tipekaryawan='".$tipe."'";
	}else{
		$where.=" and tipekaryawan in ('1','2','3','4')";
	}

	$tab = "<table class=sortable cellspacing=1>";
	$tab.="
		<thead>
			<tr class=rowheader>
				<th align=center rowspan='2'>".$_SESSION['lang']['nourut']."</th>
				<th align=center rowspan='2'>".$_SESSION['lang']['nik2']."</th>
				<th align=center rowspan='2'>".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center rowspan='2'>Tgl Lahir</th>
				<th align=center rowspan='2'>No KTP</th>
				<th align=center rowspan='1' colspan='9'>".$_SESSION['lang']['transaksi']."</th>
				<th align=center rowspan='2'>Action</th>
			</tr>
			<tr class=rowheader>
				<th align=center width=50px>BKM<br>Panen</th>
				<th align=center width=50px>BKM<br>Rawat</th>
				<th align=center width=50px>Pengawas</th>
				<th align=center width=50px>Traksi</th>
				<th align=center width=50px>Lembur</th>
				<th align=center width=50px>SDM</th>
				<th align=center width=50px>Premi BM TBS</th>
				<th align=center width=50px>Potongan SDM</th>
				<th align=center width=50px>Angsuran SDM</th>
			</tr>
		</thead>
	 <tbody>";

	$no="";
	$str = "select * from " . $dbname . ".datakaryawan  where 1=1 ".$where." and kodeorganisasi='".$pt."' and tanggalkeluar='0000-00-00' order by namakaryawan asc"; 
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		#cek dibkm
		$pnn = "select * from " . $dbname . ".kebun_prestasi  where nik='".$bar['karyawanid']."'"; 
		$rpnn = fetchdata($pnn);
		
		$bkm = "select * from " . $dbname . ".kebun_prestasi  where nikpemel='".$bar['karyawanid']."'"; 
		$rbkm = fetchdata($bkm);
		
		$a = "select * from " . $dbname . ".kebun_aktifitas  where nikmandor='".$bar['karyawanid']."'"; 
		$ra = fetchdata($a);
		
		$b = "select * from " . $dbname . ".kebun_aktifitas  where nikmandor1='".$bar['karyawanid']."'"; 
		$rb = fetchdata($b);
		
		$c = "select * from " . $dbname . ".kebun_aktifitas  where nikasisten='".$bar['karyawanid']."'"; 
		$rc = fetchdata($c);
		
		$d = "select * from " . $dbname . ".kebun_aktifitas  where keranimuat='".$bar['karyawanid']."'"; 
		$rd = fetchdata($d);
		
		$e = "select * from " . $dbname . ".vhc_runhk  where idkaryawan='".$bar['karyawanid']."'"; 
		$re = fetchdata($e);
		
		$f = "select * from " . $dbname . ".sdm_lemburdt  where karyawanid='".$bar['karyawanid']."'"; 
		$rf = fetchdata($f);
		
		$g = "select * from " . $dbname . ".sdm_absensidt  where karyawanid='".$bar['karyawanid']."'"; 
		$rg = fetchdata($g);
		
		$h = "select * from " . $dbname . ".kebun_spbbm  where karyawanid='".$bar['karyawanid']."'"; 
		$rh = fetchdata($h);
		
		$i = "select * from " . $dbname . ".sdm_potongandt  where nik='".$bar['karyawanid']."'"; 
		$ri = fetchdata($i);
		
		$j = "select * from " . $dbname . ".sdm_angsurandt  where karyawanid='".$bar['karyawanid']."'"; 
		$rj = fetchdata($j);
		
		$awas = count($ra)+count($rb)+count($rc)+count($rd);
		$ttl=count($rpnn)+count($rbkm)+$awas+count($re)+count($rf)+count($rg)+count($rh)+count($ri)+count($rj);
		$warna="";
		if($ttl>0){
			$warna="style=background-color:red title=\"Sudah ada transaksi\"";
		}
		$no++;
		$tab.="<tr class=rowcontent ".$warna." id=tr_".$no.">";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td>".$bar['nik']."</td>";
		$tab.="<td>".$bar['namakaryawan']."</td>";
		$tab.="<td>".$bar['tanggallahir']."</td>";
		$tab.="<td>".$bar['noktp']."</td>";
		$tab.="<td align=center>".count($rpnn)."</td>";
		$tab.="<td align=center>".count($rbkm)."</td>";
		$tab.="<td align=center>".$awas."</td>";
		$tab.="<td align=center>".count($re)."</td>";
		$tab.="<td align=center>".count($rf)."</td>";
		$tab.="<td align=center>".count($rg)."</td>";
		$tab.="<td align=center>".count($rh)."</td>";
		$tab.="<td align=center>".count($ri)."</td>";
		$tab.="<td align=center>".count($rj)."</td>";

		if($ttl==0){
			$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['karyawanid']."','".$no."');\" >&nbsp;
			
			<img src='images/zoom.png' class='resicon' title='Lihat' onclick=\"previewKaryawan('".$bar['karyawanid']."','".$bar['namakaryawan']."',event)\";>
			</td>";
		}else{
			$tab.="<td align=center><img src='images/zoom.png' class='resicon' title='Lihat' onclick=\"previewKaryawan('".$bar['karyawanid']."','".$bar['namakaryawan']."',event)\";></td>";		
		}
	}

					
	 
	$tab.="</tbody></table>";
}
switch ($proses) {
	case 'delete':
		$str = "delete from " . $dbname . ".datakaryawan  where karyawanid='" . $karyawanid . "'"; #exit("error".$str);
	
		#$str = "update " . $dbname . ".datakaryawan set tanggalkeluar = '2019-01-01',updateby='".$_SESSION['standard']['userid']."' where karyawanid='" . $karyawanid . "'"; #exit("error".$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
######PREVIEW
    case 'preview':
        echo $tab;
        break;

######EXCEL	
    case 'excel':
        $nop_ = $tipe;
        if (strlen($tab) > 0) {
			$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
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
	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}



?>