<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$proses=checkPostGet('proses','');
$gudang=checkPostGet('gudang','');
$klbarang=checkPostGet('klbarang','');
$kdbarang=checkPostGet('kdbarang','');
$klsubbarang=checkPostGet('klsubbarang','');
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));

if ($proses=='preview' || $proses=='excel') {

	## Filter data
	$whr="";
	if ($unit=='') {
		$whr=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
	}else{
		$whr=" and kodeorg='".$unit."'";
	}

												###############################
												############ Begin ############
												###############################

	## Inisialisasi array
	$arrlist=array();

	## Data transaksi penerimaan
	$str="select * from ".$dbname.".log_transaksi_vw where kodegudang='".$gudang."' and kodebarang='".$kdbarang."' and post='1' 
	and tanggal<='".$tanggal."' and tipetransaksi<4 order by tanggal";
	// exit('warning:'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){			
		$arrlist[$bar['notransaksi']][$bar['nopp']]['kodebarang']=$bar['kodebarang'];		
		$arrlist[$bar['notransaksi']][$bar['nopp']]['hargarata']=$bar['hargarata'];		
		$arrlist[$bar['notransaksi']][$bar['nopp']]['tanggal']=$bar['tanggal'];		
		$arrlist[$bar['notransaksi']][$bar['nopp']]['satuan']=$bar['satuan'];		
		$arrlist[$bar['notransaksi']][$bar['nopp']]['nopo']=$bar['nopo'];		
		$arrlist[$bar['notransaksi']][$bar['nopp']]['penerimaan']=$bar['jumlah'];
		$totpenerimaan+=$bar['jumlah'];

		$dt1 = strtotime($tanggal);
        $dt2 = strtotime($bar['tanggal']);
        $diff = abs($dt2-$dt1);
        $jmlhhari = $diff/86400; 

        if ($jmlhhari>0 && $jmlhhari<=90) {
			$arrlist[$bar['notransaksi']][$bar['nopp']]['0']=$jmlhhari;	
        }else if ($jmlhhari>90 && $jmlhhari<=180) {
			$arrlist[$bar['notransaksi']][$bar['nopp']]['90']=$jmlhhari;	
        }else if ($jmlhhari>180 && $jmlhhari<=360) {
			$arrlist[$bar['notransaksi']][$bar['nopp']]['180']=$jmlhhari;	
        }else if ($jmlhhari>360) {
			$arrlist[$bar['notransaksi']][$bar['nopp']]['360']=$jmlhhari;	
        }

	}

	/*query ambil transaksi masuk, untuk ditampilkan pada list penerimaan
	select * from log_transaksi_vw where kodept=pt and kodegudang like unit% and tanggal<=tanggal and tipetransaksi<4
	query ambil transaksi keluar
	select kodebarang,sum(jumlah) as jumlah from log_transaksi_vw where kodept=pt and kodegudang like unit% and tanggal<=tanggal and tipetransaksi>4 group by kodobarang*/

	## Data transaksi pengeluaran
	$str="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where kodegudang='".$gudang."' and kodebarang='".$kdbarang."' and post='1' 
	and tanggal<='".$tanggal."' and tipetransaksi>4 order by tanggal";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){			
		$totpengeluaran=$bar['jumlah'];
	}

	/*if (count($arrlist)==0) {
		exit('Warning : data empty.');
	}*/

												################################
												############# End ##############
												################################

	// echo "<pre>";
	// print_r($arrlist);
	// echo "</pre>";
	// exit('warning : ');

	$border=0;
	if ($proses=='excel') {
		$border=1;
	
		$optnoakun = makeOption($dbname,'log_5klbarang','kode,noakun',"kode='".substr($kdbarang,0,3)."'");
		$optkel = makeOption($dbname,'log_5klbarang','kode,kelompok',"kode='".substr($kdbarang,0,3)."'");
		$optkelsub = makeOption($dbname,'log_5subklbarang','kode,namasubkelompok',"kode='".substr($kdbarang,0,5)."'");
		$optkdbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbarang."'");
		$display.="<table class=sortable>
						<tr>
							<td >".$_SESSION['lang']['noakun']."</td>
							<td align='left'>".$optnoakun[substr($kdbarang,0,3)]."</td>
						</tr>
						<tr>
							<td >".$_SESSION['lang']['kelompokbarang']."</td>
							<td align='left'>".$optkel[substr($kdbarang,0,3)]."</td>
						</tr>
						<tr>
							<td >".$_SESSION['lang']['subkelompokbarang']."</td>
							<td align='left'>".$optkelsub[substr($kdbarang,0,5)]."</td>
						</tr>
						<tr>
							<td >".$_SESSION['lang']['kodebarang']."</td>
							<td align='left'>".$kdbarang."</td>
						</tr>
						<tr>
							<td >".$_SESSION['lang']['namabarang']."</td>
							<td align='left'>".$optkdbrg[$kdbarang]."</td>
						</tr>
					</table>";

	}

		## Display Data
		$display.="<table class=sortable cellspacing=1 border=".$border." width=100%>
					<thead>
						<tr>
							<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>
							<td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>
							<td align=center rowspan=2>".$_SESSION['lang']['nopo']."</td>
							<td align=center rowspan=2>".$_SESSION['lang']['nopp']."</td>
							<td align=center colspan=4>Inventory Aging</td>
							<td align=center rowspan=2>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['penerimaan']."</td>
						</tr>
						<tr>
							<td align=center>0 to 90</td>
							<td align=center>91  to 180</td>
							<td align=center>181 to 360</td>
							<td align=center>360</td>
						</tr>
					</thead>";

		$no=0;
		foreach ($arrlist as $notransaksi => $listpr) {
			foreach ($listpr as $pr => $data) {
				
				$no+=1;

				if ($pr=='NULL') {
					$pr='';
				}

				$sakhir=$totpenerimaan-$totpengeluaran;
				$display.="<tr class=rowcontent>
					<td align='center'>".$no."</td>
					<td>".$data['tanggal']."</td>
					<td>".$data['nopo']."</td>
					<td>".$pr."</td>
					<td align='center'>".$data['0']."</td>
					<td align='center'>".$data['90']."</td>
					<td align='center'>".$data['180']."</td>
					<td align='center'>".$data['360']."</td>
					<td align='right'>".number_format($data['penerimaan'])."</td>";
				$display.="</tr>";
			}
		}

		$display.="<tr class=rowcontent>
				<td align='right' colspan='8'><b>".$_SESSION['lang']['total']." ".$_SESSION['lang']['penerimaan']."</b></td>
				<td align='right'><b>".number_format($totpenerimaan)."</b></td>
				</tr>";

		$display.="<tr class=rowcontent>
				<td align='right' colspan='8'><b>".$_SESSION['lang']['total']." ".$_SESSION['lang']['pengeluaran']."</b></td>
				<td align='right'><b>".number_format($totpengeluaran)."</b></td>
				</tr>";

		$display.="<tr class=rowcontent>
				<td align='right' colspan='8'><b>".$_SESSION['lang']['saldoakhir']."</b></td>
				<td align='right'><b>".number_format($sakhir)."</b></td>
				</tr>";

	$display.="</table>";
}

switch ($proses) {
	case 'getunit':
		$str="select distinct left(kodegudang,4) as kodeorganisasi,namaorganisasi from ".$dbname.".log_transaksi_vw a left join ".$dbname.".organisasi b on left(a.kodegudang,4)=b.kodeorganisasi 
          where induk='".$pt."' order by namaorganisasi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	    $res->setFetchMode(PDO::FETCH_OBJ);        
	    $optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	    while($bar= $res->fetch())
	    {
	      $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	    }

	    echo $optgudang;
	break;

	case'getgudang':
		$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sUnit="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unit."%' and tipe like 'GUDANG%' order by kodeorganisasi asc";
		$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
		$qUnit->setFetchMode(PDO::FETCH_ASSOC);
		while($rUnit=$qUnit->fetch())
		{
			$optUnit.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['kodeorganisasi']." - ".$rUnit['namaorganisasi']."</option>";
		}

	     echo $optUnit;
    break;
		
	case 'getklbarang':
		$optkodesub="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct left(kodebarang,3) as kelbarang, kelompok from ".$dbname.".log_transaksi_vw a left join 
		".$dbname.".log_5klbarang b on left(a.kodebarang,3)=b.kode where kodegudang='".$gudang."' order by kode";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkodesub.="<option value='".$bar['kelbarang']."'>".$bar['kelbarang']." - ".$bar['kelompok']."</option>";
		}

		echo $optkodesub;
	break;
		
	case 'getKodeSub':
		$optkodesub="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct left(kodebarang,5) as kelbarang, namasubkelompok from ".$dbname.".log_transaksi_vw a left join 
		".$dbname.".log_5subklbarang b on left(a.kodebarang,5)=b.kode where kodegudang='".$gudang."' and left(kodebarang,3)='".$klbarang."' order by kode";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkodesub.="<option value='".$bar['kelbarang']."'>".$bar['kelbarang']." - ".$bar['namasubkelompok']."</option>";
		}

		echo $optkodesub;
	break;
		
	case 'getkodebarang':
		$optkodebarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct a.kodebarang,namabarang from ".$dbname.".log_transaksi_vw a left join 
		".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where kodegudang='".$gudang."' and left(a.kodebarang,5)='".$klsubbarang."' order by a.kodebarang";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkodebarang.="<option value='".$bar['kodebarang']."'>".$bar['kodebarang']." - ".$bar['namabarang']."</option>";
		}

		echo $optkodebarang;
	break;

    case 'preview':
        echo $display;
    break;

    case 'excel':
        $tglSkrg = date("Ymd");

        $kodeorg=$unit;
        if ($unit=='') {
        	$kodeorg=$pt;
        }
        $nop_ = "inventory_aging_".$gudang;
        if (strlen($display) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $display)) {
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

?>