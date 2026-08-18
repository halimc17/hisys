<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$param = $_POST;
$proses = checkPostGet('proses', '');
$nopo = checkPostGet('nopo', '');
$noinvoice = checkPostGet('noinvoice', '');
$tanggal = tanggalsystemn(checkPostGet('tanggal', ''));
$tanggalinvoice = tanggalsystemn(checkPostGet('tanggalinvoice', ''));
$kodeorg = checkPostGet('kodeorg', '');
$unit = checkPostGet('unit', '');
$tipeinvoice = checkPostGet('tipeinvoice', '');
$supplier = checkPostGet('supplier', '');
// $nilaiinvoice=checkPostGet('nilaiinvoice','');
// $nilaidpp=checkPostGet('nilaidpp','');
$keterangan = checkPostGet('keterangan', '');
$noakun = checkPostGet('noakun', '');
$matauang = checkPostGet('matauang', '');
$kurs = checkPostGet('kurs', '');
$nilai = checkPostGet('nilai', '');
$kodevhc = checkPostGet('kodevhc', '');
$kodeasset = checkPostGet('kodeasset', '');
$noaruskas = checkPostGet('noaruskas', '');
$hisnoakun = checkPostGet('hisnoakun', '');
$hisnoaruskas = checkPostGet('hisnoaruskas', '');
$noinvoiceum = checkPostGet('noinvoiceum', '');
$nourut = checkPostGet('nourut', '');
$pajak = checkPostGet('pajak', '');
$kodeblok = checkPostGet('kodeblok', '');
$nopo = checkPostGet('nopo', '');
$kegiatan = checkPostGet('kegiatan', '');
$tipearuskas = checkPostGet('tipearuskasdt', '');

$namafile = checkPostGet('namafile', '');
$kriteriaefil = checkPostGet('kriteriaefil', '');
$emodul = "TGH";
@$arrmodul = getmodulefil($emodul);

$klBarangSoAngkut = getParameterApp("KLBRGSOANG", "array");

$optnoakunlain = $optkriteria = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

foreach ($arrmodul as $key => $val) {
	@$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
}
// $path   = "filegis/";
$path   = __DIR__ . "/fileupload/keu_tagihan/";

$str = "select * from " . $dbname . ".setup_filesize where transaksi='keu_tagihan'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$filesize = $bar['filesize'];
}


$notransaksi = checkPostGet('notransaksi', '');
$cekdata = checkPostGet('cekdata', '');


$str = "select * from " . $dbname . ".log_5supplier";
$res = fetchdata($str);
foreach ($res as $bar) {
	// $optsupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
	$nmsupplier[$bar['supplierid']] = $bar['namasupplier'];
}


$str = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400'";
$res = fetchdata($str);
foreach ($res as $bar) {
	// $optsupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
	$nmbarangpabrik[$bar['kodebarang']] = $bar['namabarang'];
}

$str = "select * from " . $dbname . ".keu_5akun where detail='1' and noakun like '7%'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optnoakunlain .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}


#= data kodept
$str = "select kodeorganisasi,induk from " . $dbname . ".organisasi where length(kodeorganisasi)=4";
$res = fetchdata($str);
foreach ($res as $bar) {
	$arrkodept[$bar['kodeorganisasi']] = $bar['induk'];
}

$str = "select * from " . $dbname . ".keu_5jenistagihan";
$res = fetchdata($str);
foreach ($res as $bar) {
	$statusjurnal[$bar['kode']] = $bar['jurnal'];
}
switch ($proses) {
	case 'reconfirmpostingData':
		$str = "update " . $dbname . ".keu_tagihanht set posting=3 where noinvoice '" . $noinvoice . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;

	case 'getinfo':
		$noakun = array();
		$noaruskas = array();

		echo "<fieldset  style='float:left;' >
					<legend>" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['transaksi'] . "</legend>
						<table cellpadding=1 cellspacing=1 border=0 class=sortable>
						<thead>
						<tr class=rowheader>
								<td>No</td>
								<td>" . $_SESSION['lang']['noinvoice'] . "</td>
								<td>" . $_SESSION['lang']['tanggal'] . "</td>
								<td>" . $_SESSION['lang']['nilaiinvoice'] . "</td> 
								<td>" . $_SESSION['lang']['aruskas'] . "</td> 
								<td>" . $_SESSION['lang']['noakun'] . "</td> 
								<td>" . $_SESSION['lang']['nilai'] . "</td> 
								<td>" . $_SESSION['lang']['total'] . "</td> 
						</tr></thead>";

		#= header
		$str = "select * from " . $dbname . ".keu_tagihanht where kodeorg='" . $kodeorg . "' and kodesupplier='" . $supplier . "'
							and tanggal like '" . substr($tanggalinvoice, 0, 4) . "%' ";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$arrnoinvoice[$bar['noinvoice']] = $bar['noinvoice'];
			$tglinv[$bar['noinvoice']] = $bar['tanggal'];
			// $nilaiht[$bar['noinvoice']]=$bar['nilaiinvoice'];

		}

		#= detail
		$str = "select * from " . $dbname . ".keu_tagihandt where noinvoice in ('" . implode("','", $arrnoinvoice) . "') ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$rowspan[$bar['noinvoice']] += 1;
			$nomorurut[$bar['nourut']] = $bar['nourut'];
			$noaruskas[$bar['noinvoice']][$bar['nourut']] = $bar['noaruskas'];
			$nilaidt[$bar['noinvoice']][$bar['nourut']] = $bar['nilai'];
			$listinv[$bar['noinvoice']][$bar['nourut']] = $bar['nourut'];
			$noakun[$bar['noinvoice']][$bar['nourut']] = $bar['noakun'];
			@$tnilaidt[$bar['noinvoice']] += $bar['nilai'];
		}



		// echo"<pre>";
		// print_r($arrnoinvoice);
		// echo"</pre>";
		// @$no+=1;
		// echo"
		// <tr class=rowcontent>
		// <td>".$no."</td>
		// <td>".$bar['noinvoice']."</td>
		// <td>".tanggalnormal($bar['tanggal'])."</td>
		// <td>".$bar['nilaiinvoice']."</td>
		// </tr>";
		$no = 0;

		// echo $noinvoice;


		foreach ($arrnoinvoice as $noinv) {
			$bgcolor = '';
			if ($noinvoice == $noinv) {
				$bgcolor = "bgcolor='pink'";
			}
			@$no++;
			$nodt = 0;
			$totalnilai = array();
			foreach ($nomorurut as $nourut) {
				if ($listinv[$noinv][$nourut] != '') {
					$nodt++;
					if ($nodt == 1) {
						$optnmaruskas = makeOption($dbname, "keu_5aruskas", 'noaruskas,nama_aruskas', "noaruskas='" . $noaruskas[$noinv][$nourut] . "'");
						$optnmakun = makeOption($dbname, "keu_5akun", 'noakun,namaakun', "noakun='" . $noakun[$noinv][$nourut] . "'");
						echo "<tr class=rowcontent>";
						echo "<td " . $bgcolor . " valign=top align=center>" . $no . "</td>";
						echo "<td " . $bgcolor . " valign=top >" . $noinv . "</td>";
						echo "<td " . $bgcolor . " valign=top >" . tanggalnormal($tglinv[$noinv]) . "</td>";
						echo "<td " . $bgcolor . " valign=top >" . @number_format($nilaiht[$noinv], 2) . "</td>";
						echo "<td " . $bgcolor . " valign=top>" . $optnmaruskas[$noaruskas[$noinv][$nourut]] . "</td>";
						echo "<td " . $bgcolor . " valign=top>" . $optnmakun[$noakun[$noinv][$nourut]] . "</td>";
						echo "<td  align=right " . $bgcolor . " valign=top>" . @number_format($nilaidt[$noinv][$nourut], 2) . "</td>";
						$totalnilai[$noinv] = $nilaiht[$noinv] + $tnilaidt[$noinv];
						echo "<td  align=right " . $bgcolor . " valign=top >" . @number_format($totalnilai[$noinv], 2) . "</td>";
						echo "</tr>";
					} else {
						echo "<tr class=rowcontent>";
						echo "<td " . $bgcolor . " colspan=4></td>";
						echo "<td " . $bgcolor . ">" . $optnmaruskas[$noaruskas[$noinv][$nourut]] . "</td>";
						echo "<td " . $bgcolor . ">" . $optnmakun[$noakun[$noinv][$nourut]] . "</td>";
						echo "<td  align=right " . $bgcolor . ">" . @number_format($nilaidt[$noinv][$nourut], 2) . "</td>";
						echo "<td " . $bgcolor . " colspan=2></td>";
						echo "</tr>";
					}
				}
			}
			@$gtnilaiht += $nilaiht[$noinv];
			@$gtnilaidt += $tnilaidt[$noinv];
			@$gttotalnilai += $totalnilai[$noinv];
		}
		echo "<tr class=rowcontent>";
		echo "<td colspan=3 align=center><b>" . $_SESSION['lang']['total'] . "</td>";
		echo "<td align=right><b>" . @number_format($gtnilaiht, 2) . "</td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td><b>" . @number_format($gtnilaidt, 2) . "</td>";
		echo "<td align=right><b>" . @number_format($gttotalnilai, 2) . "</td>";
		echo "</tr>";




		echo "</table></fieldset>";
		break;






	case 'getunit':
		$arrnpwp = $arrUnit = $arrnpwppph = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$lstUnit = getOrgDetail(1);
		$dtMul = 0;
		$listOrg = '';
		foreach ($lstUnit as $row => $isiDt) {
			if (substr($row, 0, 5) == 'Pilih') {
				continue;
			}
			if ($dtMul == 0) {
				$listOrg = "'" . $row . "'";
				$dtMul = 1;
			} else {
				$listOrg .= ",'" . $row . "'";
			}
		}

		# Options
		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $param['kdpt'] . "' and kodeorganisasi in (" . $listOrg . ")";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($param['kodeunit'] == $bar['kodeorganisasi']) {
				$arrUnit .= "<option value='" . $bar['kodeorganisasi'] . "' selected>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			} else {
				$arrUnit .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			}
		}

		# Options
		/*
        $str="select npwp,defaults from ".$dbname.".setup_org_npwp where kodeorg='".$param['kdpt']."' and status=1";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()){
            if($param['npwp']==$bar['npwp']){ 
                $arrnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
            }else{
                if ($bar['defaults']=='1') {
                    $arrnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
                }else{
                    $arrnpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
                }
            }
        }
		*/


		#= opt ppn
		$str = "select * from " . $dbname . ".setup_org_npwp where kodeorg='" . $param['kdpt'] . "' and status=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($param['npwp'] == $bar['npwp']) {
				$arrnpwp .= "<option value='" . $bar['npwp'] . "' selected>" . $bar['npwp'] . "</option>";
			} else {
				if ($bar['defaultppn'] == '1') {
					$arrnpwp .= "<option value='" . $bar['npwp'] . "' selected>" . $bar['npwp'] . "</option>";
				} else {
					$arrnpwp .= "<option value='" . $bar['npwp'] . "'>" . $bar['npwp'] . "</option>";
				}
			}
		}

		# Options
		$str = "select * from " . $dbname . ".setup_org_npwp where kodeorg='" . $param['kdpt'] . "' and status=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($param['npwppph'] == $bar['npwppph']) {
				$arrnpwppph .= "<option value='" . $bar['npwp'] . "' selected>" . $bar['npwp'] . "</option>";
			} else {
				if ($bar['defaultpph'] == '1') {
					$arrnpwppph .= "<option value='" . $bar['npwp'] . "' selected>" . $bar['npwp'] . "</option>";
				} else {
					$arrnpwppph .= "<option value='" . $bar['npwp'] . "'>" . $bar['npwp'] . "</option>";
				}
			}
		}


		echo $arrUnit . "####" . $arrnpwp . "####" . $arrnpwppph;
		break;

	case 'disnopo':

		$str = "select jurnal,tipesupplier from " . $dbname . ".keu_5jenistagihan where kode='" . $tipeinvoice . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$arrjurnal = $bar['jurnal'];
		$tipesupplier = $bar['tipesupplier'];

		if ($tipeinvoice != '') {
			if ($tipesupplier == '') {
				exit('warning : Tipe Supplier untuk jenis tagihan ini belum ada. silahkan input pada keuangan>setup>jenis tagihan.');
			}
		}

		$optsup = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select a.namasupplier,a.supplierid from " . $dbname . ".log_5supplier a left join " . $dbname . ".log_5supkelompok b on a.supplierid=b.supplierid where b.tipe='" . $tipesupplier . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optsup .= "<option value='" . $bar['supplierid'] . "'>" . $bar['namasupplier'] . " (" . $bar['supplierid'] . ")</option>";
		}


		$arrjurnal = 1;

		echo $arrjurnal . "####" . $optsup;
		break;

	case 'getrek':
		$arrrek = $optjenis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		# Options
		$str = "select * from " . $dbname . ".log_5rekbank where supplierid='" . $param['supplier'] . "' and isactive=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optBank = makeOption($dbname, "keu_5daftarbank", 'kodebank,namabank', "kodebank='" . $bar['idbank'] . "'");
			if ($param['reksupplier'] == $bar['rekening']) {
				$arrrek .= "<option value='" . $bar['rekening'] . "' selected>" . $bar['rekening'] . " - " . $bar['an'] . "(" . $optBank[$bar['idbank']] . ")</option>";
			} else {
				if ($bar['def'] == '1') {
					$arrrek .= "<option value='" . $bar['rekening'] . "' selected>" . $bar['rekening'] . " - " . $bar['an'] . "(" . $optBank[$bar['idbank']] . ")</option>";
				} else {
					$arrrek .= "<option value='" . $bar['rekening'] . "'>" . $bar['rekening'] . " - " . $bar['an'] . "(" . $optBank[$bar['idbank']] . ")</option>";
				}
			}
		}

		// $strkel="select a.tipe,b.kode from ".$dbname.".log_5supkelompok a 
		// left join ".$dbname.".log_5klsupplier b on a.tipe=b.tipe where a.supplierid = '".$param['supplier']."' and a.status='1'";

		$strkel = "select * from " . $dbname . ".log_5supkelompok where supplierid = '" . $param['supplier'] . "' and status='1'";
		$reskel = fetchData($strkel);
		foreach ($reskel as $key => $barkel) {
			if ($param['jenissupplier'] == $barkel['tipe']) {
				$optjenis .= "<option value='" . $barkel['tipe'] . "' selected>" . $barkel['tipe'] . "</option>";
			} else {
				$optjenis .= "<option value='" . $barkel['tipe'] . "'>" . $barkel['tipe'] . "</option>";
			}
		}

		/*$str="select jurnal,tipesupplier from ".$dbname.".keu_5jenistagihan where kode='".$tipeinvoice."'";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $arrjurnal=$bar['jurnal'];
        $tipesupplier=$bar['tipesupplier'];

        if ($tipeinvoice=='um') {
            #cek akun untuk uang muka
            $sCekPo="select * from ".$dbname.".log_spkht where notransaksi='".$param['nopo']."'";
            $rCekPo=fetchData($sCekPo);
            if(count($rCekPo)!=0){
                $optSupp2 = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$param['supplier']."' and tipe='KONTRAKTOR'");
                $noakunsup=$optSupp2[$param['supplier']];
            }else{
                $optSupp2 = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$param['supplier']."' and tipe in (select tipe from ".$dbname.".log_5klsupplier where kelompok='SUPPLIER')");
                $noakunsup=$optSupp2[$param['supplier']];
            }
        }else{
            $optak = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$param['supplier']."'");
            $str="select noakun from ".$dbname.".log_5supkelompok where supplierid='".$param['supplier']."' and tipe='".$tipesupplier."'";
            $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            $noakunsup=$bar['noakun'];
        }

        if ($noakunsup=='') {
            exit('warning : Supplier '.$optak[$param['supplier']].' dengan tipe '.$tipesupplier.' belum terdaftar. Silahkan daftarkan pada menu pengadaan > setup > data supplier.');
        }*/

		/*if(count($reskel)==0){
            exit('warning : Supplier '.$optak[$param['supplier']].' belum memiliki jenis usaha / jenis supplier. Silahkan daftarkan pada menu pengadaan > setup > data supplier.');
        }*/

		echo $arrrek . "####" . $optjenis;
		break;

	case 'getnoakunsup':

		$str = "select noakun from " . $dbname . ".log_5supkelompok where supplierid='" . $param['supplier'] . "' 
			and tipe='" . $param['jenissupplier'] . "' and status='1'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		@$noakun = $bar['noakun'];

		if ($param['tipeinvoice'] == 'um') {
			// $noakun='1180301';
			$noakun = '1180105';
		}

		echo $noakun;
		break;

	case 'getdate30':
		// exit("Error:A".$param['nopo']);
		$tanggal = tanggalsystemn($param['tanggal']);
		#= jika bersumber dari PO
		if (@$param['nopo'] != '' and (@$param['tipeinvoice'] == 'p' || @$param['tipeinvoice'] == 'pon')) {

			// GRIR 2021, vienny: jatuh tempo sejak IR, bukan GR
			// #= ambil tanggal GR
			// $str="select tanggal from ".$dbname.".log_transaksiht where nopo='".$param['nopo']."' and tipetransaksi=1";
			// $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar=$res->fetch();
			// 	$tanggal=$bar['tanggal'];

			// #= validasi jika tanggal gudang masih kosong, maka ambil tanggal param kirim
			// if($tanggal==''){
			// 	$tanggal=tanggalsystemn($param['tanggal']);
			// }
			// GRIR 2021


			// exit("Error".$param['nopo']);
			#= ambil data dari po
			$str = "select * from " . $dbname . ".log_poht where nopo='" . $param['nopo'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			if (@$bar['syaratbayar'] == '') {
				@$bar['syaratbayar'] = 30;
			}
			$syaratbayar = "+" . $bar['syaratbayar'] . " days";
			// exit("Error:$syaratbayar");
			// $angkalebih='+60 days';
			$tgljatuhtempo = date('d-m-Y', strtotime($syaratbayar, strtotime($tanggal)));
			// exit("Error:".$tgljatuhtempo._.$syaratbayar);
		} else {
			// $tgljatuhtempo=date('d-m-Y', strtotime('+30 days', strtotime($tanggal)));
			$tgljatuhtempo = tanggalnormal(tglbulandepan($tanggal));
		}

		// exit("error:".$tgljatuhtempo);
		echo $tgljatuhtempo;


		/*
			#= cek tanggal hari ini
		$tglkirim=tanggalsystemn($param['tanggal']);
		$tglhi=date('Y-m-d');
		if($tglkirim<$tglhi){
			echo "1";
		} else {
			$tgl30=date('d-m-Y', strtotime('+30 days', strtotime($param['tanggal'])));
			echo $tgl30;
		}
		*/

		break;

	case 'getkurs':

		if ($param['tanggal'] == '') {
			$param['tanggal'] = date('d-m-Y');
		}

		$str = "select kurs from " . $dbname . ".setup_matauangrate where daritanggal<='" . tanggalsystem($param['tanggal']) . "' and kode='" . $param['matauang'] . "' order by daritanggal desc, jam desc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$kurs = $bar['kurs'];

		echo $kurs;
		break;

	case 'getpajak':

		$str = "select tarif from " . $dbname . ".log_5pphsup where supplierid='" . $param['supplier'] . "' and noakun='" . $param['noakun'] . "' and status='1'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$pajak = $bar['tarif'];

		$nilaidpp = str_replace(',', '', $param['nilaidpp']);

		#== Abdul
		#== Origin
		// $nilai=$nilaidpp*$pajak/100;

		#== Buat Floor samakan dengan case 'bas'
		$nilai = floor($nilaidpp * $pajak / 100);
		#== End Abdul

		echo $pajak . "####" . @number_format($nilai, 2);
		// exit("Error:A");
		break;

	case 'getnoaruskas':

		$arrrek = "";
		if ($kodevhc != '') {
			$arrrek = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$str = "select distinct noaruskas from keu_5aruskas_detail where noaruskas in ('11106','11107')";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optnmakun = makeOption($dbname, "keu_5aruskas", 'noaruskas,nama_aruskas', "noaruskas='" . $bar['noaruskas'] . "'");
				if ($noaruskas == $bar['noaruskas']) {
					$arrrek .= "<option value='" . $bar['noaruskas'] . "' selected>" . $bar['noaruskas'] . " - " . $optnmakun[$bar['noaruskas']] . "</option>";
				} else {
					$arrrek .= "<option value='" . $bar['noaruskas'] . "'>" . $bar['noaruskas'] . " - " . $optnmakun[$bar['noaruskas']] . "</option>";
				}
			}
		} else {
			$arrrek = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		}

		echo $arrrek;
		break;

	case 'getnoakun':

		if ($kodevhc != "") {
			$whrkd = " and left(noakun,1)='4'";
		}

		//and left(noakun,1)!='2'
		$optket = $arrrek = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		// $str="select noakun from keu_5aruskas_detail where noaruskas='".$noaruskas."' ".$whrkd." and left(noakun,3)!='115' ";
		$str = "select distinct noakun from keu_5aruskas_detail where noaruskas='" . $noaruskas . "' " . $whrkd . " ";
		// exit("Error:$str");
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optnmakun = makeOption($dbname, "keu_5akun", 'noakun,namaakun', "noakun='" . $bar['noakun'] . "'");
			if ($noakun == $bar['noakun']) {
				$arrrek .= "<option value='" . $bar['noakun'] . "' selected>" . $bar['noakun'] . " - " . $optnmakun[$bar['noakun']] . "</option>";
			} else {
				$arrrek .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $optnmakun[$bar['noakun']] . "</option>";
			}
		}

		$str1 = "select id_ket,keterangan from keu_5keterangan where noaruskas='" . $noaruskas . "'";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res1->fetch()) {
			if ($keterangan == $bar['id_ket']) {
				$optket .= "<option value='" . $bar['id_ket'] . "' selected>" . $bar['keterangan'] . "</option>";
			} else {
				$optket .= "<option value='" . $bar['id_ket'] . "'>" . $bar['keterangan'] . "</option>";
			}
		}
		$optket = '';
		echo $arrrek . "####" . $optket;
		break;

	case 'getkegiatan':
		$optkeg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "SELECT DISTINCT kodekegiatan, namakegiatan FROM " . $dbname . ".setup_kegiatan WHERE noakun = '" . $noakun . "'";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optkeg .= "<option value='" . $val['kodekegiatan'] . "'>" . $val['kodekegiatan'] . " - " . $val['namakegiatan'] . "</option>";
		}

		echo $optkeg;
		break;

	case 'getblok':
		$optblok = "<option vallue=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$sql = "SELECT kelompok FROM " . $dbname . ".setup_kegiatan WHERE kodekegiatan = '" . $kegiatan . "'";
		$req = fetchdata($sql);
		$statusblok = $req[0]['kelompok'];

		if ($statusblok == 'PNN') {
			$statusblok = 'TM';
		} else {
			$statusblok = $statusblok;
		}

		$str = "SELECT DISTINCT kodeorg FROM " . $dbname . ".setup_blok  WHERE statusblok = '" . $statusblok . "' and kodeorg like '" . $unit . "%' ORDER BY kodeorg ASC";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optblok .= "<option value='" . $val['kodeorg'] . "'>" . $val['kodeorg'] . "</option>";
		}

		echo $optblok;
		break;

	case 'getblokbesar':
		$optblok = "<option vallue=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$sql = "SELECT kelompok FROM " . $dbname . ".setup_kegiatan WHERE kodekegiatan = '" . $kegiatan . "'";
		$req = fetchdata($sql);
		$statusblok = $req[0]['kelompok'];

		if ($statusblok == 'PNN') {
			$statusblok = 'TM';
		} else {
			$statusblok = $statusblok;
		}

		$str = "SELECT indukblok, SUM(luasareaproduktif) as luas FROM " . $dbname . ".setup_blok WHERE statusblok = '" . $statusblok . "' and kodeorg like '" . $unit . "%' GROUP BY indukblok ORDER BY indukblok ASC";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optblok .= "<option value='" . $val['indukblok'] . "'>" . $val['indukblok'] . " - " . round($val['luas'], 2) . " - " . $statusblok . "</option>";
		}

		echo $optblok;
		break;

	/*
    case'cekData':
        $sData="select * from ".$dbname.".keu_5jenistagihan where kode='".$param['jnsInvoice']."' and source!=''";
        $rData=fetchData($sData);
        echo count($rData);
    break;
	*/


	/*
    case 'add':
		
        $data=$_POST;
		
        $noinvoice=$data['noinvoice']=date('Ymdhis');

        if ($data['npwp']=='') {
            exit('Warning : NPWP PPn is obligatory');
        }
		if ($data['npwppph']=='') {
            exit('Warning : NPWP PPh is obligatory');
        }

        // if ($data['tanggalinvoice']=='') {
        //     exit('Warning : Invoice Date is obligatory');
        // }

        if ($data['matauang']=='') {
            exit("Warning : ".$_SESSION['lang']['matauang']." ".$_SESSION['lang']['kosong']);
        }

        if ($data['kurs']=='' || $data['kurs']=='0') {
            exit("Warning : ".$_SESSION['lang']['kurs']." ".$_SESSION['lang']['kosong']);
        }

        if ($data['keterangan2']=='') {
            exit('Warning : Description is obligatory');
        }

        if ($data['unit'] == '') {
            exit("Warning : ".$_SESSION['lang']['unit']." ".$_SESSION['lang']['kosong']);
        }


		if ($data['nilaidpp'] == '' || $data['nilaidpp'] == '0') {
            exit("Warning : Nilai dpp invoice masih kosong");
        }

		if ($data['nilaiinvoice'] == '' || $data['nilaiinvoice'] == '0') {
             exit("Warning : Nilai Invoice masih kosong");
        }

		

        if ($noinvoice == '') {
            exit("Warning : ".$_SESSION['lang']['noinvoice']." ".$_SESSION['lang']['kosong']);
        }

        if ($data['tanggal'] == '') {
            exit("Warning : ".$_SESSION['lang']['tanggalterima']." ".$_SESSION['lang']['kosong']);
        }

        if ($data['jenissupplier'] == '') {
            exit("Warning : ".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['supplier']." ".$_SESSION['lang']['kosong']);
        }

        if ($data['noakun'] == '') {
            exit("Warning : ".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['kosong']);
        }

        if ($data['kodesupplier'] == '') {
            exit('Warning : '.$_SESSION['lang']['notifkodesupplier'].' : '.$data['nopo']);
        }

        if ($data['bagian'] == '') {
            exit("Warning : ".$_SESSION['lang']['departemen']."  ".$_SESSION['lang']['kosong']);
        }

        if ($data['kodesupplier']!='') {
            $sData="select tipesupplier from ".$dbname.".keu_5jenistagihan where kode='".$data['tipeinvoice']."'";
            $rData=fetchData($sData);
            $tipesupplier=$rData[0]['tipesupplier'];
            if ($tipesupplier=='') {
                exit('warning : Tipe Supplier untuk jenis tagihan ini belum ada. silahkan input pada keuangan>setup>jenis tagihan.');
            }
        }

        $data['tipeinvoice']=$data['tipeinvoice'];
        $data['tanggal']=tanggalsystem($data['tanggal']);
        $data['jatuhtempo']=tanggalsystem($data['jatuhtempo']);
        $data['tanggalinvoice']=tanggalsystem($data['tanggalinvoice']);
        $data['tanggalnofp']=tanggalsystem($data['tanggalnofp']);
        $data['nilaiinvoice']=str_replace(',', '', $data['nilaiinvoice']);
        $data['nilaidpp']=str_replace(',', '', $data['nilaidpp']);
        $data['uangmuka']=str_replace(',', '', $data['uangmuka']);
        $data['updateby']=$_SESSION['standard']['userid'];
        $data['createby']=$_SESSION['standard']['userid'];
        $data['createtime']=date('Y-m-d');
        $data['nilaippn']=0; 
        $data['bagian']=$data['bagian'];
		
		

        # mengambil total rupiah dari sumber PO
		
        // if ($data['fileupload'] != '') {
            // if ($_FILES['file']['error'] == 0) {
                // $filetype=strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
                // $filename=$_FILES['file']['name']."_".$noinvoice."".$filetype;
                // $file_tmpname=$_FILES['file']['tmp_name'];
                // if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf')) {
                    // if ($_FILES['file']['size'] <= 512000){
                        // move_uploaded_file($file_tmpname, "filegis/$filename");
                    // } else {
                        // exit("warning : Ukuran file upload maksimal 250kb");
                    // }
                // } else {
                    // exit("Warning : Format file upload harus .jpg atau .jpeg");
                // }
            // }
        // }
		

        unset($data['proses']);
        unset($data['file']);
        unset($data['fileupload']);
        if ($data['notransaksi_gr']=='undefined') {
            unset($data['notransaksi_gr']);
        }
        if ($data['termin']=='undefined') {
            unset($data['termin']);
        }

        if (($data['tipeinvoice'] == 'tck') || ($data['tipeinvoice'] == 'tpk') || ($data['tipeinvoice'] == 'tbs')) {
            $sJumlah="select noakun,sum(jumlah) as jumlah,noreferensi as nodo,kodesupplier from ".$dbname.".keu_jurnaldt where noakun like '21%'  and kodeorg='".$param['unit']."' and noreferensi='".$data['nopo']."' group by noreferensi,noakun";
            $rJumlah=fetchData($sJumlah);
            foreach($rJumlah as $key => $val) {
                if ($val['noakun'] != '2120300') {
                    $rCek2['jmlhpo']=$val['jumlah'] * (-1);
                } else {
                    $rCek2['jmlpph']=$val['jumlah'] * (-1);
                }
            }

            $strk="select (qty*harga) as jumlah,pphditanggung,subsidi from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$data['nopo']."'";
            $rtrk=fetchData($strk);
            $pph=$rCek2['jmlpph'];
            foreach($rtrk as $key => $val) {
                if ($val['pphditanggung'] == '1') {
                    $nilaigross=($rCek2['jmlhpo'] * (100 / (100 - $val['subsidi'])));
                    $pph=($nilaigross * $val['subsidi']) / 100;
                } else {
                    $nilaigross=$rCek2['jmlhpo'];
                    $pph=($nilaigross * $val['subsidi']) / 100;
                }
            }

            $rCek2['jmlhpo']=round($nilaigross, 2);
            $rCek2['ppn']=$pph;

            # data tagihan
            $sJmlhDt="select sum(nilaiinvoice) as jumlah,nopo as nodo,kodesupplier from ".$dbname.".keu_tagihanht where unit='".$param['unit']."' and tipeinvoice='".$data['tipeinvoice']."' and nopo='".$data['nopo']."' group by nopo,kodesupplier";
            $rJmlhDt=fetchData($sJmlhDt);
            foreach($rJmlhDt as $key => $val) {
                $jmlInv=$val['jumlah'];
            }

        }  else  if ($data['tipeinvoice'] == 'p' || $data['tipeinvoice'] == 'pon' || $data['tipeinvoice'] == 'pocbd') {
            $optPO=makeOption($dbname, 'log_poht', 'nopo,kodesupplier', "stat_release=1 and nopo='".$data['nopo']."'");
            //jmlh po di dari po
            $sCek2=$owlPDO->query("select nilaipo as jmlhpo 
			from ".$dbname.".log_poht where nopo like '".$data['nopo']."%' and kodesupplier='".$data['kodesupplier']."' 
			and kodeorg='".$data['kodeorg']."' ");
			
			
			
			// $sCek2=$owlPDO->query("select (subtotal + pbbkb + addcost - nilaidiskon + lc) as jmlhpo 
			// from ".$dbname.".log_poht where nopo like '".$data['nopo']."%' and kodesupplier='".$data['kodesupplier']."' 
			// and kodeorg='".$data['kodeorg']."' ");
		
			
            $sCek2->setFetchMode(PDO::FETCH_ASSOC);
            $rCek2=$sCek2->fetch();
			// exit("Error:".$rCek2['jmlhpo']);

            // $data['notransaksi_gr']='';
            // $snogr="select notransaksi from ".$dbname.".log_transaksi_vw where nopo='".$data['nopo']."'";
            // $tgr=$owlPDO->query($snogr)or die(print " Gagal: ".PDOException::getMessage());
            // $tgr->setFetchMode(PDO::FETCH_ASSOC);
            // $rgr=$tgr->fetch();
            // $data['notransaksi_gr']=$rgr['notransaksi'];

        } else if ($data['tipeinvoice'] == 'k') {
            $sCek2=$owlPDO->query("select sum(jumlahrealisasi) as  jmlhpo from ".$dbname.".log_baspk where statusjurnal=1 and notransaksi='".$data['nopo']."' ");
            $sCek2->setFetchMode(PDO::FETCH_ASSOC);
            $rCek2=$sCek2->fetch();

            $optPO=makeOption($dbname, 'log_spkht', 'notransaksi,koderekanan');

        } 

        $optJenis=makeOption($dbname, 'keu_5jenistagihan', 'kode,jurnal');
	
        ##jika jenis statusnya tidak jurnal sebelumya wajib terisi nopo
        if($optJenis[$data['tipeinvoice']] == 0) {
           
            ##jumlah po di invoice
            $sCek=$owlPDO->query("select distinct sum(nilaiinvoice) as jmlhinvoice from ".$dbname.".keu_tagihanht
                  where nopo='".$data['nopo']."' and tipeinvoice='".$data['tipeinvoice']."' and noinvoice<>'".$data['noinvoice']."'");
            $sCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=$sCek->fetch();
            $jmlInv=$rCek['jmlhinvoice'];
			// exit("error:".$optJenis[$data['tipeinvoice']]);
            if($optJenis[$data['tipeinvoice']] == 0 && $data['tipeinvoice']!='ffb') {
				
                # $rCek2['jmlhpo']=total nilai PO
                # $jmlInv = sum nilai invoice berdasarkan nopo
                # $data['nilaiinvoice']= data kiriman sesuai dengan inputan nilai invoice headernya
                $selisih=$rCek2['jmlhpo']-($jmlInv + $data['nilaiinvoice']);
				// exit("Error:".$selisih._.$rCek2['jmlhpo']._.$jmlInv._.$data['nilaiinvoice']);
                if($data['tipeinvoice'] != 'um'){
                    if($selisih<0){
                        exit("Warning: ".$_SESSION['lang']['notifnilainvoice'].". Total Nilai Invoice :".@number_format(($jmlInv + $data['nilaiinvoice']), 2).", Nilai PO/Kontrak/Document:".@number_format($rCek2['jmlhpo'], 2));
                    }
                }
            }
			
			// exit("Error:A");
        }else{

            if (substr($data['tipeinvoice'],0,2) == 'as' || substr($data['tipeinvoice'],0,2) == 'sw') {
                if ($data['nopo'] == '') {
                    exit('warning'.$_SESSION['lang']['notifpopilih']);
                }

                # cek apakah nilah invoice melebihi nilaipo
                if (@number_format($data['nilaiinvoice'], 2) > @number_format($rCek2['jmlhpo'], 2)) {
                    exit("Warning: ".$_SESSION['lang']['notifnilainvoice'].". Total Nila Invoice :".@number_format($data['nilaiinvoice'], 2).", Nilai PO/Kontrak/Document:".@number_format($rCek2['jmlhpo'], 2));
                }

            }else{
				
				$str="select kode from ".$dbname.".pmn_5jenisspk"; 
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$arrspk[]=$bar['kode'];
				}		
				
				$cektipeinvoice=strtoupper($data['tipeinvoice']);
				if(!in_array($cektipeinvoice,$arrspk)){
					if ($data['noinvoicesupplier'] == '') {
						exit('warning : Invoice supplier number is obligatory');
					}
				}

                ##cek apakah noinvoicesupplier tersebut sudah ada di tagihan
                $sCek2="select nilaiinvoice as jmlhpo from ".$dbname.".keu_tagihanht where noinvoicesupplier='".$data['noinvoicesupplier']."'";
                $rCek2=fetchData($sCek2);
                $jmlhdata=count($rCek2);

                ##jmlh po di invoice
                $sCek=$owlPDO->query("select distinct sum(a.nilai) as jmlhppn from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b 
                      on a.noinvoice=b.noinvoice where b.noinvoicesupplier='".$data['noinvoicesupplier']."' and tipeinvoice='".$data['tipeinvoice']."'");
                $sCek->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=$sCek->fetch();
                $jmlInv=$rCek['jmlhppn'];

                if ($jmlhdata>0 and !in_array($cektipeinvoice,$arrspk)) {
                     exit("Warning: No. Invoice Supplier : ".$data['noinvoicesupplier']." ini sudah pernah ditagihkan.");
                }
            }

        }  

        ##Insert Header
        $cols=array();
        foreach($data as $key => $row) {
            $cols[]=$key;
        }
        $query=insertQuery($dbname, 'keu_tagihanht', $data, $cols);
		// exit("Error".$query._.$data['jenissupplier']._.$data['noakun']._.$data['nilaidpp']);
        try {
            $owlPDO->exec($query);
        } catch (PDOException $e) {
            print " Gagal, DB Error  1!: ".$e->getMessage()."<br/>";
            die();
        }
	
		
        ##insert detail PO
        // if ($data['tipeinvoice'] == 'p' || $data['tipeinvoice'] == 'pj' || $data['tipeinvoice'] == 'poa' || ($data['tipeinvoice'] == 'um' && $jenisum == 'p')){
        if ($data['tipeinvoice'] == 'p' || $data['tipeinvoice'] == 'pon' || $data['tipeinvoice'] == 'pocbd'){

			#= yang diambil harga satuan * jumlah
			#= bukan hartot = harga rata * jumlah
			
            #=ambil harga per kodebarang
            // $str1="select nopo,left(kodebarang,3) as kodekel,noakun,sum(hartot) as terima from ".$dbname.".log_transaksi_vw a 
			// left join ".$dbname.".log_5klbarang b on left(a.kodebarang,3)=b.kode where tipetransaksi=1 and nopo='".$data['nopo']."' group by nopo,left(a.kodebarang,3)";
			

				
				if ($data['tipeinvoice'] == 'p'){
					
					// $str1="select nopo,left(kodebarang,3) as kodekel,noakun,sum(hargasatuan*jumlah) as terima from ".$dbname.".log_transaksi_vw a 
					// left join ".$dbname.".log_5klbarang b on left(a.kodebarang,3)=b.kode where tipetransaksi=1 and nopo='".$data['nopo']."' and 
					// notransaksi='".$data['notransaksi_gr']."' group by nopo,left(a.kodebarang,3)";
					
					$str1="select nopo,left(kodebarang,3) as kodekel,noakun,sum(hargasatuan*jumlah) as terima from ".$dbname.".log_transaksi_vw a 
					left join ".$dbname.".log_5klbarang b on left(a.kodebarang,3)=b.kode where tipetransaksi=1 and nopo='".$data['nopo']."' 
					group by nopo,left(a.kodebarang,3)";
					// exit("Error:$str1");
					$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar1=$res1->fetch()) {
						@$noakundt[$bar1['noakun']]=$bar1['noakun'];
						@$nilaidt[$bar1['noakun']]+=$bar1['terima'];
					}
				}
				
				if ($data['tipeinvoice'] == 'pon'){
					$str1="select nopo,left(kodebarang,3) as kodekel,noakun,sum(hartot) as terima,tipe,subunitdt 
						from ".$dbname.".log_noninventorydt_vw 
						where posting=1 and nopo='".$data['nopo']."' group by nopo,left(a.kodebarang,3)";
					// exit("Error:$str1");
					$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar1=$res1->fetch()) {
						@$noakundt[$bar1['noakun']]=$bar1['noakun'];
						@$nilaidt[$bar1['noakun']]+=$bar1['terima'];
						$kodeadk='';
						if($bar1['tipe']=='CO'){
							$kodeadk=$bar1['subunitdt'];
						}
					}
				}
				
				
				if ($data['tipeinvoice'] == 'pocbd'){
					
					$str1="select nopo,left(kodebarang,3) as kodekel,noakun,sum(hargasatuan*jumlahpesan) as terima from ".$dbname.".log_po_vw a 
					left join ".$dbname.".log_5klbarang b on left(a.kodebarang,3)=b.kode where  nopo='".$data['nopo']."' 
					group by left(a.kodebarang,3)";	
					$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar1=$res1->fetch()) {
						@$noakundt[$bar1['noakun']]=$bar1['noakun'];
						@$nilaidt[$bar1['noakun']]+=$bar1['terima'];
					}
				}
				
				
				
                #=ambil noaruskas dan keterangan
                foreach ($noakundt as $key => $valakun) {
                    $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$valakun."'";
                    $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                    $res1->setFetchMode(PDO::FETCH_ASSOC);
                    $bar1=$res1->fetch();
                    $noaruskasdt[$valakun]=$bar1['noaruskas'];

                    $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt[$valakun]."'";
                    $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                    $res1->setFetchMode(PDO::FETCH_ASSOC);
                    $bar1=$res1->fetch();
                    $keterangandt[$valakun]=$bar1['id_ket'];
                }

                #=query insert detail
				$noaruskaskosong=0;
				$notifnoaruskaskosong='';
                foreach ($noakundt as $key => $valakun) {

                    #jumlah per no.GR
                    $sCekdt=$owlPDO->query("select sum(nilai) as nilaidt ,a.noakun from ".$dbname.".keu_tagihandt a 
						left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where b.nopo='".$data['nopo']."' 
						and a.noakun='".$noakundt[$valakun]."' and b.notransaksi_gr='".$data['notransaksi_gr']."' group by a.noakun ");
                    // exit("warning : select sum(nilai) as nilaidt ,b.noakun from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where b.nopo='".$data['nopo']."' and a.noakun='".$noakundt[$valakun]."' and b.notransaksi_gr='".$data['notransaksi_gr']."' group by a.noakun ");
                    $sCekdt->setFetchMode(PDO::FETCH_ASSOC);
                    $rCekdt= $sCekdt->fetch();
                    $sisaterima=0;
                    $sisaterima=$nilaidt[$valakun]-floatval($rCekdt['nilaidt']);


                    if ($sisaterima==0) {
                        continue;
                    }

                    $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset, noaruskas, keterangan) values
                    ('".$noinvoice."','".$noakundt[$valakun]."','".$sisaterima."','','".$kodeadk."','".$noaruskasdt[$valakun]."','')";
                    try {
                        $owlPDO->exec($ins);
                    } catch (PDOException $e) {
                        print " Gagal  !: ".$e->getMessage()."<br/>";
                        die();
                    }
					
					if($noaruskasdt[$valakun]==''){
						$noaruskaskosong++;
						$notifnoaruskaskosong.=" ".$valakun."\n";
					}
					
                }
          
			
		
            $str1="select ppn,subtotal,pph,nilaidiskon from ".$dbname.".log_poht where nopo='".$data['nopo']."'";
            $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $bar1=$res1->fetch();
            $totppn=$bar1['ppn'];
			$totpph=$bar1['pph'];
            // $subtotal=$bar1['subtotal'];
			 $subtotal=$bar1['subtotal']-$bar1['nilaidiskon'];
            $persenppn=$totppn/$subtotal;
            $persenpph=$totpph/$subtotal;
			
			// if($data['tipeinvoice'] == 'poa' || $data['tipeinvoice'] == 'pj'){
				// $data['nilaiinvoice']=$data['nilaiinvoice']/(1+$persenppn-$persenpph);
			// }
			
			// exit("Error:".$data['nilaiinvoice']);
			
            $ppn=$persenppn*$data['nilaidpp'];
			$notifakunppnkosong='';
            $str1="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKPP'";
            $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $bar1=$res1->fetch();
            $akun=$bar1['nilai'];
            $arrakun=explode(',', $akun);
			
			if($akun==''){
				$notifakunppnkosong++;
			}

            #=noaruskas dan ket
            @$datadt=getArusKasket($arrakun[0],'','');
            @$datadt=explode('##', $datadt);
            $noaruskasppn=$datadt[0];
            $ketppn=$datadt[1];

            if ($totppn>0) {
				
                $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset, noaruskas, keterangan) values
                ('".$noinvoice."','".$arrakun[0]."','".$ppn."','','','".$noaruskasppn."','')";
               
				try {
                    $owlPDO->exec($ins);
                } catch (PDOException $e) {
                    print " Gagal  !: ".$e->getMessage()."<br/>";
                    die();
                }
				// exit("Error".$arrakun[0]);
				if($noaruskasppn==''){
					$noaruskaskosong++;
					$notifnoaruskaskosong.=" ".$arrakun[0]."\n";
				}
			
            }

            if ($data['tipeinvoice'] == 'p' || $data['tipeinvoice'] == 'pon' || $data['tipeinvoice'] == 'pj' || $data['tipeinvoice'] == 'poa'){
                
                if ($data['tipeinvoice'] == 'pj') {
                    $noakunpph=$arrakun[2];
                }else{
                    $noakunpph=$arrakun[1];
                }

                #=cek jika ada pph sup
                $tarifpphpersen=0;
                $tarifpph=0;
                $pph=0;
                $str1="select tarif from ".$dbname.".log_5pphsup where supplierid='".$data['kodesupplier']."' and noakun='".$noakunpph."' and status='1'";
                $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $bar1=$res1->fetch();
                $tarifpphpersen=$bar1['tarif'];
                $tarifpph=$tarifpphpersen/100;
                $pph=$tarifpph*$data['nilaidpp'];
                // $pph=$tarifpph*$data['	'];
				
				
				if($noakunpph==''){
					$notifakunpphkosong++;
				}


                #=noaruskas dan ket
                @$datadt=getArusKasket($noakunpph,'','','');
                @$datadt=explode('##', $datadt);
                $noaruskaspph=$datadt[0];
                $ketpph=$datadt[1];

                if ($tarifpphpersen!='') {
                    $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset, noaruskas, keterangan) values
                    ('".$noinvoice."','".$noakunpph."','".-($pph)."','','','".$noaruskaspph."','')";
                    try {
                        $owlPDO->exec($ins);
                    } catch (PDOException $e) {
                        print " Gagal  !: ".$e->getMessage()."<br/>";
                        die();
                    }
                }
				if($noaruskaspph==''){
					$noaruskaskosong++;
					$notifnoaruskaskosong.=" ".$noakunpph."\n";
				}
            }
        }

        ##insert detail kontraktor
        if ($data['tipeinvoice'] == 'k'){

            $sCekk="select divisi from ".$dbname.".log_spkht where notransaksi='".$data['nopo']."'";
            $rCekk=fetchData($sCekk);

            $str1="select notransaksi as nopo,left(kodekegiatan,7) as noakun,sum(jumlahrealisasi) as terima 
                    from ".$dbname.".log_baspk where notransaksi='".$data['nopo']."' and termin='".$data['termin']."' 
                    group by notransaksi,termin,left(kodekegiatan,7)";

            if ($rCekk[0]['divisi']=='PROJECT') {
                $str1="select notransaksi as nopo,akunak as noakun,sum(jumlahrealisasi) as terima,kodeblok from ".$dbname.".log_baspk a 
                left join ".$dbname.".sdm_5tipeasset b on substr(kodeblok,4,2)=kodetipe 
                where notransaksi='".$data['nopo']."' and termin='".$data['termin']."' group by notransaksi,termin,akunak";
            }

            $noakundt=array();
            $nilaidt=array();
            $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar1=$res1->fetch()) {
                if ($rCekk[0]['divisi']=='PROJECT') {
                    $kdasset=$bar1['kodeblok'];
                }
                $noakundt[$bar1['noakun']]=$bar1['noakun'];
                $nilaidt[$bar1['noakun']]=$bar1['terima'];
            }
            

			foreach ($noakundt as $key => $valakun) {
				$str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$valakun."'";
				$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_ASSOC);
				$bar1=$res1->fetch();
				$noaruskasdt[$valakun]=$bar1['noaruskas'];

				$str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt[$valakun]."'";
				$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_ASSOC);
				$bar1=$res1->fetch();
				$keterangandt[$valakun]=$bar1['id_ket'];
			}

			foreach ($noakundt as $key => $valakun) {

				#jumlah per no.GR
				$sCekdt=$owlPDO->query("select sum(nilai) as nilaidt ,a.noakun from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where b.nopo='".$data['nopo']."' and a.noakun='".$noakundt[$valakun]."' and b.termin='".$data['termin']."' group by a.noakun ");
				$sCekdt->setFetchMode(PDO::FETCH_ASSOC);
				$rCekdt= $sCekdt->fetch();
				$sisaterima=0;
				$sisaterima=$nilaidt[$valakun]-floatval($rCekdt['nilaidt']);


				if ($sisaterima==0) {
					continue;
				}

				$ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset, noaruskas, keterangan) values
				('".$noinvoice."','".$noakundt[$valakun]."','".$nilaidt[$valakun]."','','".$kdasset."','".$noaruskasdt[$valakun]."','')";
				try {
					$owlPDO->exec($ins);
				} catch (PDOException $e) {
					print " Gagal  !: ".$e->getMessage()."<br/>";
					die();
				}
			}
          

            $noakunpajak=array();
            $pajak=array();
            //cek jika ada ppn dan pph
            $str1="select noakun from ".$dbname.".log_spk_tax where notransaksi='".$data['nopo']."'";
            $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar1=$res1->fetch()) {
                $noakunpajak[$bar1['noakun']]=$bar1['noakun'];
            }

            $str1="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKPP'";
            $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $bar1=$res1->fetch();
            $akun=$bar1['nilai'];
            $arrakun=explode(',', $akun);


            foreach ($noakunpajak as $key => $valakun) {
                if ($valakun==$arrakun[0]) {
                    $pajak[$valakun]=0.1*$data['nilaiinvoice'];
                }else{

                    if ($data['tipeinvoice'] == 'k'){
                        //cek tarif pph sup
                        $tarifpph=0;
                        $str1="select tarif from ".$dbname.".log_5pphsup where supplierid='".$data['kodesupplier']."' and noakun='".$valakun."' and status='1'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $tarifpphpersen=$bar1['tarif'];
                        $tarifpph=$tarifpphpersen/100;
                        $pajak[$valakun]=$tarifpph*$data['nilaiinvoice'];
                    }
                    
                }
            }

            foreach ($noakunpajak as $key => $valakun) {

                //noaruskas dan ket
                @$datadt=getArusKasket($valakun);
                @$datadt=explode('##', $datadt);
                $noaruskaspajak=$datadt[0];
                $ketpajak=$datadt[1];

                $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset, noaruskas, keterangan) values
                ('".$noinvoice."','".$valakun."','".-($pajak[$valakun])."','','','".$noaruskaspajak."','".$ketpajak."')";
                try {
                    $owlPDO->exec($ins);
                } catch (PDOException $e) {
                    print " Gagal  !: ".$e->getMessage()."<br/>";
                    die();
                }
            }
        }
		
		
		
		if ($data['tipeinvoice']=='ffb'){
			
			$strH = "select sum(jumrpadjust) as jumrpadjust
					from ".$dbname.".pmn_tbs where notransaksi='".$data['nopo']."' ";
		
			$resH=$owlPDO->query($strH);
			$resH->setFetchMode(PDO::FETCH_ASSOC);
			$barH = $resH->fetch();
				$nilai=$barH['jumrpadjust'];
				
		
			// exit("Error:".$cekdata._.$nilai._.$notransaksi);
			$str="insert into ".$dbname.".keu_tagihandt(noinvoice,notransaksi, nourut, nilai,noakun,noaruskas,keterangan) values
			('".$noinvoice."','".$data['nopo']."','','".$nilai."','".$data['noakun']."','115001','166')";
			// exit("Error:$str");
			
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: ".$e->getMessage()."<br/>";
				die();
			}
			
			
			#= update pajak
			#cek apakah terdaftar dilist pajak
			$str="select * from ".$dbname.".log_5pphsup where supplierid='".$data['kodesupplier']."' and status='1'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				
				#=cek jika ada ppn pajak
				$pajak=0;
				$pajak=$bar['tarif']/100*$nilai;
			   
				#=noaruskas dan ket
				@$datadt=getArusKasket($bar['noakun'],'',''); 
				@$datadt=explode('##', $datadt);
				$noaruskasppn=$datadt[0];
				$ketppn=$datadt[1];
		 
		 
				if(substr($bar['noakun'],0,3)=='213'){
					$pajak=$pajak*-1;
				}
				
					#= delete 1st
					$strdel="delete from ".$dbname.".keu_tagihandt where noinvoice='".$noinvoice."' and noakun='".$bar['noakun']."'";
					try {
						$owlPDO->exec($strdel);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."<br/>";
						die();
					}
					
			  
					$strins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset, noaruskas, keterangan,pajak) values
					('".$noinvoice."','".$bar['noakun']."','".($pajak)."','','','".$noaruskasppn."','".$ketppn."','".$bar['tarif']."')";
					try {
						$owlPDO->exec($strins);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."<br/>";
						die();
					}
			}
		}
		
		#= jika noaruskas kosong maka delete semua
		if($notifakunppnkosong>0 || $notifakunpphkosong>0){
			$str="delete from ".$dbname.".keu_tagihanht where noinvoice='".$data['noinvoice']."'";
			try {
				$owlPDO->exec($str);
				echo "Nomor akun PPn atau PPh belum didaftarkan diparameter aplikasi ";
				// echo $notifakunppnkosong;
				// echo $notifakunpphkosong;
				exit("Warning:");
			} catch (PDOException $e) {
				print " Gagal  !: ".$e->getMessage()."<br/>";
				die();
			}
		}
		
		
		if($noaruskaskosong>0){
			$str="delete from ".$dbname.".keu_tagihanht where noinvoice='".$data['noinvoice']."'";
			try {
				$owlPDO->exec($str);
				echo " Data tidak dapat disimpan, karena ada akun yang belum didaftarkan aruskas-nya\n";
				echo $notifnoaruskaskosong;
				exit("Warning:");
			} catch (PDOException $e) {
				print " Gagal  !: ".$e->getMessage()."<br/>";
				die();
			}
		}

        echo $data['noinvoice'];
    break;
	*/


	case 'edit':
		$data = $_POST;

		if ($data['tanggalinvoice'] == '') {
			exit('warning : Invoice Date is obligatory');
		}

		if ($data['keterangan2'] == '') {
			exit('warning : Detail Pembelian Harus diisi');
		}

		if ($data['unit'] == '' || $data['unit'] == 'false') {
			exit("Warning : Unit tidak boleh kosong. Silahkan reload frame terlebih dahulu.");
		}

		if ($data['jenissupplier'] == '') {
			exit("Warning : " . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['supplier'] . " " . $_SESSION['lang']['kosong']);
		}

		$where = "noinvoice='" . $data['noinvoice'] . "'";
		$optImg = makeOption($dbname, 'keu_tagihanht', 'noinvoice,uploadinvoice', $where);
		$namafile = $optImg[$data['noinvoice']];
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $data['noinvoice'] . "'";
		try {
			$owlPDO->exec($str);

			foreach ($_SESSION['efiltgh'] as $key => $val) {
				$str = "insert into " . $dbname . ".listfileupload values ('','" . $data['noinvoice'] . "','" . $val['namafile'] . "','" . $val['formaticon'] . "','" . $val['kriteriaefil'] . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
				try {
					$owlPDO->exec($str);
					// copy($val['location'], "filegis/".$val['namafile']);
				} catch (PDOException $e) {
					print "DB Error  1!: " . $e->getMessage() . "<br/>";
					die();
				}
			}
		} catch (PDOException $e) {
			print "DB Error  1!: " . $e->getMessage() . "<br/>";
			die();
		}
		// $updateImage=false;
		// if ($data['fileupload'] != '') {
		// if ($_FILES['file']['error'] == 0) {
		// $filetype=strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
		// $nama=explode(".",$_FILES['file']['name']);
		// $filename=$nama[0]."_".$noinvoice."".$filetype;
		// //$filename=$data['noinvoice']."".$filetype;
		// $file_tmpname=$_FILES['file']['tmp_name'];
		// if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf')) {
		// if ($_FILES['file']['size'] <= 512000) {
		// $updateImage=true;
		// $pathx = "filegis/".$namafile;
		// unlink($pathx);
		// move_uploaded_file($file_tmpname, "filegis/$filename");
		// } else {
		// exit("warning : Ukuran file upload maksimal 512kb");
		// }
		// } else {
		// exit("Warning : Format file upload harus .jpg | .jpeg | .png | .pdf");
		// }
		// }
		// }

		$optJenis = makeOption($dbname, 'keu_5jenistagihan', 'kode,jurnal');
		if ($optJenis[$data['tipeinvoice']] == 0) { //jika jenis memiliki jurnal wajib terisi nopo
			if ($data['nopo'] == '' and $data['tipeinvoice'] != 'ffb') {
				exit('warning' . $_SESSION['lang']['notifpopilih']);
			}
			# ambil nilai rp
			if ($data['tipeinvoice'] == 'h') {
				$sCek2 = $owlPDO->query("select bebanperusahaan as jmlhpo from " . $dbname . ".sdm_pengobatanht where notransaksi='" . $data['nopo'] . "'");
				$sCek2->setFetchMode(PDO::FETCH_ASSOC);
				$rCek2 = $sCek2->fetch();
			}
		} else {
			if ($data['status_bayar'] == '1') {
				exit('warning: ' . $_SESSION['lang']['pembayaran'] . ' via Financing tidak di ijinkan untuk jenis tagihan ini');
			}
			if ($data['noinvoicesupplier'] == '') {
				$warning .= "Invoice supplier number is obligatory\n";
			}
			if ($warning != '') {
				echo "Warning :\n" . $warning;
				exit;
			}
			$sCek2 = $owlPDO->query("select nilaiinvoice as jmlhpo from " . $dbname . ".keu_tagihanht where noinvoicesupplier='" . $data['noinvoicesupplier'] . "' ");
			$sCek2->setFetchMode(PDO::FETCH_ASSOC);
			$rCek2 = $sCek2->fetch();
			if (($rCek2['jmlhpo'] == 0) || ($rCek2['jmlhpo'] == '')) {
				$data['nilaiinvoice'] = str_replace(",", "", $data['nilaiinvoice']);
				$rCek2['jmlhpo'] = $data['nilaiinvoice'];
			}

			##cek apakah noinvoicesupplier tersebut sudah ada di tagihan
			$sCek2 = "select nilaiinvoice as jmlhpo from " . $dbname . ".keu_tagihanht where noinvoicesupplier='" . $data['noinvoicesupplier'] . "' and noinvoice!='" . $data['noinvoice'] . "'";
			$rCek2 = fetchData($sCek2);
			$jmlhdata = count($rCek2);

			if ($jmlhdata > 0) {
				exit("Warning: No. Invoice Supplier : " . $data['noinvoicesupplier'] . " ini sudah pernah ditagihkan.");
			}
		}


		// $optAkun=makeOption($dbname, 'log_5klsupplier', 'tipe,noakun');
		// $optTipe=makeOption($dbname, 'log_5supkelompok', 'supplierid,tipe', "supplierid='".$data['kodesupplier']."'");
		// $data['noakun']=$optAkun[$optTipe[$data['kodesupplier']]];

		// Error Trap
		$warning = "";
		if ($data['noinvoice'] == '') {
			$warning .= "Invoice number is obligatory\n";
		}
		if ($data['tanggal'] == '') {
			$warning .= "Date is obligatory\n";
		}
		if ($warning != '') {
			echo "Warning :\n" . $warning;
			exit;
		}

		$invoice = $data['noinvoice'];

		unset($data['proses']);
		unset($data['noinvoice']);
		unset($data['file']);
		unset($data['fileupload']);
		unset($data['notransaksi_gr']);
		unset($data['termin']);
		if ($data['tipeinvoice'] == 'p') {
			$optPO = makeOption($dbname, 'log_poht', 'nopo,kodesupplier', "stat_release=1 and nopo='" . $data['nopo'] . "'");
			//jmlh po di dari po
			$sCek2 = $owlPDO->query("select distinct  nilaipo as jmlhpo,ppn from " . $dbname . ".log_poht where nopo='" . $data['nopo'] . "' ");
			$sCek2->setFetchMode(PDO::FETCH_ASSOC);
			$rCek2 = $sCek2->fetch();
		} else if ($data['tipeinvoice'] == 's') {
			$optPO = makeOption($dbname, 'log_suratjalanht', 'nosj,expeditor');
			$rCek2['jmlhpo'] = 0;
		} else {
			$sCek2 = $owlPDO->query("select distinct nilaikontrak as jmlhpo from " . $dbname . ".log_spkht where notransaksi='" . $data['nopo'] . "' ");
			$sCek2->setFetchMode(PDO::FETCH_ASSOC);
			$rCek2 = $sCek2->fetch();
			$optPO = makeOption($dbname, 'log_spkht', 'notransaksi,koderekanan');
			$rCek2['ppn'] = 0;
		}
		if ($updateImage == true) {
			$data['uploadinvoice'] = isset($filename) ? $filename : "";
		} else {
			unset($data['uploadinvoice']);
		}
		// $data['nilaippn']=$rCek2['ppn'];
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		$data['jatuhtempo'] = tanggalsystem($data['jatuhtempo']);
		$data['tanggalnofp'] = tanggalsystem($data['tanggalnofp']);
		$data['tanggalinvoice'] = tanggalsystem($data['tanggalinvoice']);
		$data['tipeinvoice'] = $data['tipeinvoice'];
		$data['nilaiinvoice'] = str_replace(',', '', $data['nilaiinvoice']);
		$data['nilaidpp'] = str_replace(',', '', $data['nilaidpp']);
		$data['uangmuka'] = str_replace(',', '', $data['uangmuka']);
		$data['updateby'] = $_SESSION['standard']['userid'];
		$data['bagian'] = $data['bagian'];
		$query = updateQuery($dbname, 'keu_tagihanht', $data, $where);
		// exit('warning : '.$query);
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {
			print " Gagal, DB Error  2!: " . $e->getMessage() . "<br/>";
			die();
		}

		echo $invoice;
		break;

	case 'delete':
		$where = "noinvoice='" . $param['noinvoice'] . "'";
		$query = "delete from `" . $dbname . "`.`keu_tagihanht` where " . $where;
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {
			print " Gagal, DB Error  3!: " . $e->getMessage() . "<br/>";
			die();
		}

		$query = "delete from `" . $dbname . "`.`keu_tagihandt` where " . $where;
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {
			print " Gagal, DB Error  3!: " . $e->getMessage() . "<br/>";
			die();
		}
		break;

	case 'showDetail':
		$_SESSION['efiltgh'] = array();
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "'";
		$res = fetchData($str);
		foreach ($res as $key => $val) {
			$newdata = array(
				'idfiledt' => '',
				'tipe' => '1',
				'location' => $path . $val['namafile'],
				'namafile' => $val['namafile'],
				'formaticon' => $val['formaticon'],
				'kriteriaefil' => $val['kriteriaefil'],
				'size' => ''
			);
			array_push($_SESSION['efiltgh'], $newdata);
		}

		$optKet = $optaruskas = $optakun = $optasset = $optvhc = $optblok = $optkeg = $opttipearuskas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select kodevhc,detailvhc from " . $dbname . ".vhc_5master where left(kodetraksi,4)='" . $unit . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optvhc .= "<option value='" . $bar['kodevhc'] . "'>" . $bar['kodevhc'] . " - " . $bar['detailvhc'] . "</option>";
		}

		$str = "select kode,nama from " . $dbname . ".project where kodeorg='" . $unit . "' order by kode";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optasset .= "<option value='" . $bar['kode'] . "'>" . $bar['kode'] . " (" . $bar['nama'] . ")</option>";
		}

		// 20210608 remove input PPH dari tagihan
		$str = "select noaruskas,nama_aruskas,level from " . $dbname . ".keu_5aruskas where tipetransaksi='K' and noaruskas != '120002'
					and status=1 order by noaruskas";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$disabledaruskas = "";
			$textaruskas = "";

			if ($bar['level'] != 3) {
				$textaruskas = "HEADER";
				$disabledaruskas = "disabled";
			}

			$optaruskas .= "<option $disabledaruskas value='" . $bar['noaruskas'] . "'>" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . " " . $textaruskas . "</option>";
		}

		$strx = "select * from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
		$resx = fetchData($strx);
		@$kdunit = $resx[0]['unit'];
		@$tipearuskasht = $resx[0]['tipearuskasht'];

		$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '" . $kdunit . "%' and namaorganisasi not like '%NONAKTIF%' and length(kodeorganisasi)>4";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		// $optblok="<option value=''></option>";
		// while($bar=$res->fetch()){
		//     $optblok.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		// }

		$arrtipearuskas = array('budget' => 'BUDGET', 'nonbudget' => 'NON BUDGET');
		foreach ($arrtipearuskas as $key => $val) {
			$selectedtipearuskasht = "";

			if ($tipearuskasht != '') {
				if ($key == $tipearuskasht) {
					$selectedtipearuskasht = "selected";
				}
			}

			$opttipearuskas .= "<option $selectedtipearuskasht value='" . $key . "'>" . $val . "</option>";
		}

		OPEN_BOX();
		$frm[0] = '';
		$frm[1] = '';
		$frm[2] = '';
		// $frm[0].="<fieldset style=width:845px;>";
		$frm[0] .= "<fieldset>";
		$frm[0] .= "<legend>" . $_SESSION['lang']['detail'] . "</legend>";
		$frm[0] .= "<table style='vertical-align:top' width=100%>
			<td valign=top>
				<table border=0 cellpadding=1 cellspacing=1>";


		$frm[0] .= "<tr hidden>
				<td>Uang Muka</td>
				<td>:</td>
				<td><input type=text id=invum class=myinputtext style=width:197px; placeholder='add..' readonly title='" . $_SESSION['lang']['find'] . "' onclick=\"addum('" . $_SESSION['lang']['find'] . "','<div id=formPencarianum></div>',event)\"></td>
			</tr>";

		$frm[0] .= "<tr>
			
					<td>" . $_SESSION['lang']['noaruskas'] . "</td> 
                    <td>:</td>
                    <td >
                        <select id=noaruskas style=width:150px; onchange='getnoakun()'>" . $optaruskas . "</select>
                        <img id='noaruskas' onclick=z.elSearch('noaruskas',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
                    </td>
					
					<td valign=top>" . $_SESSION['lang']['pajak'] . " <b>(%)</b></td> 
                    <td valign=top>:</td>
                    <td valign=top><input type=text onkeypress=\"return_tanpa_kutip_dan_sepasi(event);\" onkeyup='getnilai()' class=myinputtextnumber id=pajak style=width:145px; ></td>
			
                    <td>" . $_SESSION['lang']['kodevhc'] . "</td> 
                    <td>:</td>
                    <td><select id=kodevhc style=width:150px;>" . $optvhc . "</select>
                        <img id='kodevhc' onclick=z.elSearch('kodevhc',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>&nbsp;&nbsp;
                    </td>
                
                    <td>" . $_SESSION['lang']['aktivadalam'] . "</td> 
                    <td>:</td>
                    <td>
                        <select id=kodeasset style=width:150px;>" . $optasset . "</select>
                        <img id='kodeasset' onclick=z.elSearch('kodeasset',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
                    </td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['noakun'] . "</td> 
                    <td>:</td>
                    <td><select id=noakundt style=width:150px; onchange='getpajak()'>" . $optakun . "</select>
                        <img id='noakundt' onclick=z.elSearch('noakundt',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
                    </td>
					
					<td valign=top>" . $_SESSION['lang']['nilai'] . " <b>(Rp)</b></td> 
                    <td valign=top>:</td>
                    <td valign=top><input type=text id=nilai onkeyup=\"z.numberFormat('nilai',2);\" onkeypress=\"return tanpa_kutip_dan_sepasi(event);\" class=myinputtextnumber style=width:145px; ></td>
					
				 	<td>" . $_SESSION['lang']['kegiatan'] . "</td>
                    <td>:</td>
                    <td>
                    	<select id=kegiatandt style=width:150px; onchange='getblokbesar()'>" . $optkeg . "</select>
						<img id='kegiatandt' onclick=z.elSearch('kegiatandt',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
					 <td>" . $_SESSION['lang']['blok'] . "</td>
                    <td>:</td>
                    <td><select id=kodeblokdt style=width:150px;>" . $optblok . "</select>
						<img id='kodeblokdt' onclick=z.elSearch('kodeblokdt',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
						<td hidden><select hidden id=kodeblokdtold style=width:150px;>" . $optblok . "</select>
						</td>
                </tr>
				
				<tr>
					<td label=tipearuskasdt>Tipe Aruskas (Budget/Non)</td>
					<td>:</td>
					<td><select id=tipearuskasdt name=tipearuskasdt style=width:150px;>" . $opttipearuskas . "</select></td>
				</tr>
                   
                </tr>
				<tr hidden>
					<td>" . $_SESSION['lang']['keterangan'] . "</td>
                    <td>:</td>
                    <td><select id=keterangandt style=width:150px;>" . $optKet . "</select></td>
                
				
                    <td>" . $_SESSION['lang']['nourut'] . "</td>
                    <td>:</td>
                    <td><input type=text id=nourut onkeypress=\"return tanpa_kutip_dan_sepasi(event);\" class=myinputtextnumber style=width:145px; ></td>
                </tr>
                <tr><td colspan=2><input type=hidden id=noinv_ref /></td>
                    <td colspan=3>
                        <button class=mybutton onclick=saveDetail()>Simpan</button>
                        <button class=mybutton onclick=cleardetail()>Hapus</button>
                        <input type=hidden id=hisnoakun>
                        <input type=hidden id=hisnoaruskas>
                        <input type=hidden id=prosesdt value='insertdt'>
                    </td>
                </tr>
            </table>
			</td>
            </table>
			</fieldset>
            ";

		// $frm[0].="<fieldset style=width:845px;>
		$frm[0] .= "<fieldset>
            <legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
            <table class=sortable cellspacing=1 cellpadding=5 border=0>
            <thead>
            <tr class=rowheader>    
                <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
                <th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
                <th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
                <th align=center>" . $_SESSION['lang']['namaasset'] . "</th>
                <th align=center>" . $_SESSION['lang']['noaruskas'] . "</th>
                <th align=center>" . $_SESSION['lang']['noakun'] . "</th>
                <th align=center>" . $_SESSION['lang']['kegiatan'] . "</th>
                <th align=center>" . $_SESSION['lang']['nilai'] . "</th>
                <th align=center>" . $_SESSION['lang']['blok'] . "</th>
                <th align=center colspan=2>" . $_SESSION['lang']['action'] . "</th>
            </tr>
            </thead><tbody>";

		$str1 = "SELECT noinvoiceum from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
		$res1 = fetchData($str1);
		@$noinvoiceumuka = $res1[0]['noinvoiceum'];

		# GET Detail
		$str = "SELECT tgh.noinvoice, tgh.nilai, tgh.noakun, tgh.kodevhc, tgh.kodeasset, tgh.noaruskas, tgh.keterangan, tgh.noinvoice_referensi, tgh.nourut, tgh.notransaksi, tgh.kodeblok, tgh.pajak, tgh.kelompokbarang, tgh.nopo, tgh.termin, tgh.kodekegiatan, indukblok.indukblok as indukblok from " . $dbname . ".keu_tagihandt tgh LEFT JOIN setup_blok indukblok ON tgh.kodeblok=indukblok.kodeorg WHERE noinvoice='" . $noinvoice . "' ORDER BY nourut ASC";
		$res = fetchData($str, "OBJECT");

		foreach ($res as $val) {
			$arrdatasd[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->indukblok;
			$arrdatasdx[$val->noinvoice][$val->noaruskas][$val->noakun][$val->kodeblok] = $val->indukblok;

			$notransaksisd[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->notransaksi;
			$noposd[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->nopo;

			# Header
			$kegiatansdHead[$val->noinvoice][$val->noaruskas][$val->noakun][$val->indukblok] = $val->kodekegiatan;
			$totalRupiah[$val->noinvoice][$val->noaruskas][$val->noakun] += $val->nilai;
			$nourutHead[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->nourut;
			$kodeassetHead[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->kodeasset;
			$kodevhcHead[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->kodevhc;
			$noinvoice_referensiHead[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->noinvoice_referensi;
			$pajakHead[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->pajak;
			$keteranganHead[$val->noinvoice][$val->noaruskas][$val->noakun] = $val->keterangan;

			# Jika ada induk blok
			if ($val->indukblok != '') {
				$arrindukblok[$val->indukblok] = $val->indukblok;
				$pajakHead[$val->noinvoice][$val->noaruskas][$val->noakun] += $val->pajak;
			}

			# Detail Per Blok Kecil
			$kegiatansd[$val->noinvoice][$val->noaruskas][$val->noakun][$val->kodeblok] = $val->kodekegiatan;
			$totalRupiahdt[$val->noinvoice][$val->noaruskas][$val->noakun][$val->kodeblok] = $val->nilai;
		}

		// echo "<pre>";
		// print_r($arrdatasd);

		$noht = 0;
		foreach ($arrdatasd as $noinvoicesd => $val) {
			foreach ($val as $noaruskassd => $vals) {
				foreach ($vals as $noakunsd => $indukblok) {

					#jika kodevhc kosong cek ke noninventorydt
					/*
						if($bar['kodevhc'] == ""){
							$sql_vhc = "SELECT distinct subunitdt as nt FROM ".$dbname.".log_noninventorydt WHERE notransaksi = '".$bar[notransaksi]."'";
							$kdvc = fetchData($sql_vhc);
							//print_r($kdvc);
							$kdvc = $kdvc[0]['nt'];
							$bar['kodevhc'] = $kdvc;
						}
						*/

					$whrKar2 = "kodevhc='" . $bar['kodevhc'] . "'";
					$optjenis = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc', $whrKar2);
					$whrsup = "noakun='" . $noakunsd . "'";
					$optSup = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', $whrsup);
					$whrnama = "kode='" . $bar['kodeasset'] . "'";
					$optnama = makeOption($dbname, 'project', 'kode,nama', $whrnama);
					$whrarus = "noaruskas='" . $noaruskassd . "'";
					$optarus = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', $whrarus);
					$whrkegHead = "kodekegiatan='" . $kegiatansdHead[$noinvoicesd][$noaruskassd][$noakunsd][$indukblok] . "'";
					$optkegHead = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', $whrkeg);

					$noht++;

					if ($indukblok == '') {
						$detailBlokKecil = "";
						$styleCursor = "";
					} else {
						$detailBlokKecil = "onclick=detailBlokKecilDt('" . $noht . "');";
						$styleCursor = "style=cursor:pointer";
					}

					$frm[0] .= "<tr class=rowcontent " . $detailBlokKecil . " " . $styleCursor . ">
							<td align=center>" . $noht . "</td>
							<td>" . $notransaksisd[$noinvoicesd][$noaruskassd][$noakunsd] . "</td>
							<td>" . $kodevhcHead[$noinvoicesd][$noaruskassd][$noakunsd] . " - " . @$optjenis[$kodevhcHead[$noinvoicesd][$noaruskassd][$noakunsd]] . "</td>
							<td>" . $kodeassetHead[$noinvoicesd][$noaruskassd][$noakunsd] . " - " . @$optnama[$kodeassetHead[$noinvoicesd][$noaruskassd][$noakunsd]] . "</td>
							<td>" . $noaruskassd . " - " . @$optarus[$noaruskassd] . "</td>
							<td>" . $noakunsd . " - " . @$optSup[$noakunsd] . "</td>
							<td>" . $kegiatansdHead[$noinvoicesd][$noaruskassd][$noakunsd][$indukblok] . " - " . @$optkegHead[$kegiatansdHead[$noinvoicesd][$noaruskassd][$noakunsd][$indukblok]] . "</td>
							<td align=right>" . @number_format($totalRupiah[$noinvoicesd][$noaruskassd][$noakunsd], 2) . "</td>
							<td>" . $indukblok . "</td>";

					if ($noinvoiceumuka != '' && substr($noakunsd, 0, 5) == '11802') {
						$frm[0] .= "<td>&nbsp;</td>";
					} else {
						$frm[0] .= "<td align=center width=25px><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"updatedt('" . $kodevhcHead[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $kodeassetHead[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $noakunsd . "','" . @number_format($totalRupiah[$noinvoicesd][$noaruskassd][$noakunsd], 2) . "','" . $noaruskassd . "','" . $keteranganHead[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $noinvoice_referensiHead[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $nourutHead[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $pajakHead[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $indukblok . "','" . $kegiatansdHead[$noinvoicesd][$noaruskassd][$noakunsd][$indukblok] . "')\"></td>";
					}

					$frm[0] .= "<td align=center width=25px><img src=images/skyblue/delete.png class=zImgBtn  title='Delete' onclick=\"deletedt('" . $noinvoicesd . "','" . $nourutHead[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $notransaksisd[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $noposd[$noinvoicesd][$noaruskassd][$noakunsd] . "','" . $noakunsd . "','" . $indukblok . "');\" ></td>";
					$frm[0] .= "</tr>";

					$nodt = 0;
					foreach ($arrdatasdx[$noinvoicesd][$noaruskassd][$noakunsd] as $bloksd => $indukblok) {
						if (isset($indukblok)) {

							$whrkeg = "kodekegiatan='" . $kegiatansd[$noinvoicesd][$noaruskassd][$noakunsd][$bloksd] . "'";
							$optkegdt = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', $whrkeg);

							$nodt++;
							$frm[0] .= "<tr class=rowcontent hidden id=detailBlok" . $noht . ">
									<td style=background:#A7C7D9; align=center>" . $noht . "." . $nodt . "</td>
									<td style=background:#A7C7D9;>" . $notransaksisd[$noinvoicesd][$noaruskassd][$noakunsd] . "</td>
									<td style=background:#A7C7D9;>" . $kodevhcHead[$noinvoicesd][$noaruskassd][$noakunsd] . " - " . @$optjenis[$kodevhcHead[$noinvoicesd][$noaruskassd][$noakunsd]] . "</td>
									<td style=background:#A7C7D9;>" . $kodeassetHead[$noinvoicesd][$noaruskassd][$noakunsd] . " - " . @$optnama[$kodeassetHead[$noinvoicesd][$noaruskassd][$noakunsd]] . "</td>
									<td style=background:#A7C7D9;>" . $noaruskassd . " - " . @$optarus[$noaruskassd] . "</td>
									<td style=background:#A7C7D9;>" . $noakunsd . " - " . @$optSup[$noakunsd] . "</td>
									<td style=background:#A7C7D9;>" . $kegiatansd[$noinvoicesd][$noaruskassd][$noakunsd][$bloksd] . " - " . @$optkegdt[$kegiatansd[$noinvoicesd][$noaruskassd][$noakunsd][$bloksd]] . "</td>
									<td style=background:#A7C7D9; align=right>" . @number_format($totalRupiahdt[$noinvoicesd][$noaruskassd][$noakunsd][$bloksd], 2) . "</td>
									<td style=background:#A7C7D9;>" . getNamaOrg($bloksd) . " - " . $bloksd . "</td>";

							$frm[0] .= "<td colspan=2 style=background:#A7C7D9; align=center width=25px></td>";
							$frm[0] .= "</tr>";
						}
					}
				}
			}
		}


		// $no=0;
		// $colspan=2;
		// $str="SELECT tgh.noinvoice, sum(tgh.nilai) as nilai, tgh.noakun, tgh.kodevhc, tgh.kodeasset, tgh.noaruskas, tgh.keterangan, tgh.noinvoice_referensi, tgh.nourut, tgh.notransaksi, tgh.kodeblok, tgh.pajak, tgh.kelompokbarang, tgh.nopo, tgh.termin, tgh.kodekegiatan, indukblok.indukblok as indukblok from ".$dbname.".keu_tagihandt tgh LEFT JOIN setup_blok indukblok ON tgh.kodeblok=indukblok.kodeorg WHERE noinvoice='".$noinvoice."' GROUP BY indukblok, noaruskas, noakun ORDER BY nourut ASC";
		// // exit("Error:".$str);
		// $res=fetchData($str);
		// foreach($res as $row=>$bar){

		// 	#jika kodevhc kosong cek ke noninventorydt
		// 	/*
		// 	if($bar['kodevhc'] == ""){
		// 		$sql_vhc = "SELECT distinct subunitdt as nt FROM ".$dbname.".log_noninventorydt WHERE notransaksi = '".$bar[notransaksi]."'";
		// 		$kdvc = fetchData($sql_vhc);
		// 		//print_r($kdvc);
		// 		$kdvc = $kdvc[0]['nt'];
		// 		$bar['kodevhc'] = $kdvc;
		// 	}
		// 	*/

		//     $whrKar2="kodevhc='".$bar['kodevhc']."'";
		//     $optjenis=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',$whrKar2);
		//     $whrsup="noakun='".$bar['noakun']."'";
		//     $optSup=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrsup);
		//     $whrnama="kode='".$bar['kodeasset']."'";
		//     $optnama=makeOption($dbname,'project','kode,nama',$whrnama);
		//     $whrarus="noaruskas='".$bar['noaruskas']."'";
		//     $optarus=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',$whrarus);
		//     $whrkeg="kodekegiatan='".$bar['kodekegiatan']."'";
		//     $optkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',$whrkeg);
		//     // $whrket="id_ket='".$bar['keterangan']."'";
		//     // $optket=makeOption($dbname,'keu_5keterangan','id_ket,keterangan',$whrket);
		// 	//<td>".@$optket[$bar['keterangan']]."</td>


		// 	$no+=1;

		// 	if($bar['indukblok'] == '') {
		// 		$detailBlokKecil = "";
		// 	} else {
		// 		$detailBlokKecil = "onclick=detailBlokKecilDt(".$no.");";
		// 		$nodtx[$bar['kodeblok']]=$no;
		// 	}

		// 	$frm[0].="<tr class=rowcontent ".$detailBlokKecil.">
		// 	<td align=center>".$no."</td>
		// 	<td>".$bar['notransaksi']."</td>
		// 	<td>".$bar['kodevhc']." - ".@$optjenis[$bar['kodevhc']]."</td>
		// 	<td>".$bar['kodeasset']." - ".@$optnama[$bar['kodeasset']]."</td>
		// 	<td>".$bar['noaruskas']." - ".@$optarus[$bar['noaruskas']]."</td>
		// 	<td>".$bar['noakun']." - ".@$optSup[$bar['noakun']]."</td>
		// 	<td>".$bar['kodekegiatan']." - ".@$optkeg[$bar['kodekegiatan']]."</td>
		// 	<td align=right>".@number_format($bar['nilai'])."</td>
		// 	<td>".getNamaOrg($bar['indukblok'])."</td>";
		// 	if ($noinvoiceumuka!='' && substr($bar['noakun'],0,5)=='11802') {
		// 		$frm[0].="<td>&nbsp;</td>";
		// 	}else{
		// 		// if($bar['kodeblok'] == '') { # Jika Blok Kosong berarti bukan Blok Besar
		// 			$frm[0].="<td align=center width=25px><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"updatedt('".$bar['kodevhc']."','".$bar['kodeasset']."','".$bar['noakun']."','".@number_format($bar['nilai'])."','".$bar['noaruskas']."','".$bar['keterangan']."','".$bar['noinvoice_referensi']."','".$bar['nourut']."','".$bar['pajak']."','".$bar['kodeblok']."','".$bar['kodekegiatan']."')\"></td>";
		// 		// }
		// 	}   
		// 	$frm[0].="<td align=center width=25px><img src=images/skyblue/delete.png class=zImgBtn  title='Delete' onclick=\"deletedt('" . $bar['noinvoice']. "','" . $bar['nourut']. "','" . $bar['notransaksi']. "','" . $bar['nopo']. "','" . $bar['noakun']. "');\" ></td>";  
		// 	$frm[0].="</tr>";
		// }

		// # Blok Besar
		// $nodt=0;
		// $str="SELECT * from ".$dbname.".keu_tagihandt where noinvoice='".$noinvoice."' and kodeblok <> '' order by nourut asc";
		// $res=fetchData($str);
		// foreach($res as $row=>$bar){
		// 	$nodt++;

		// 	$whrKar2="kodevhc='".$bar['kodevhc']."'";
		//     $optjenis=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',$whrKar2);
		//     $whrsup="noakun='".$bar['noakun']."'";
		//     $optSup=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrsup);
		//     $whrnama="kode='".$bar['kodeasset']."'";
		//     $optnama=makeOption($dbname,'project','kode,nama',$whrnama);
		//     $whrarus="noaruskas='".$bar['noaruskas']."'";
		//     $optarus=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',$whrarus);
		//     $whrkeg="kodekegiatan='".$bar['kodekegiatan']."'";
		//     $optkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',$whrkeg);

		// 	$frm[0].="<tr class=rowcontent hidden id=detailBlok".$nodtx[$bar['kodeblok']].">
		// 			<td align=center>".$no.".".$nodt."</td>
		// 			<td>".$bar['notransaksi']."</td>
		// 			<td>".$bar['kodevhc']." - ".@$optjenis[$bar['kodevhc']]."</td>
		// 			<td>".$bar['kodeasset']." - ".@$optnama[$bar['kodeasset']]."</td>
		// 			<td>".$bar['noaruskas']." - ".@$optarus[$bar['noaruskas']]."</td>
		// 			<td>".$bar['noakun']." - ".@$optSup[$bar['noakun']]."</td>
		// 			<td>".$bar['kodekegiatan']." - ".@$optkeg[$bar['kodekegiatan']]."</td>
		// 			<td align=right>".@number_format($bar['nilai'])."</td>
		// 			<td>".getNamaOrg($bar['kodeblok'])." - ".$bar['kodeblok']."</td>";
		// 		if ($noinvoiceumuka!='' && substr($bar['noakun'],0,5)=='11802') {
		// 			$frm[0].="<td>&nbsp;</td>";
		// 		}else{
		// 			$frm[0].="<td align=center width=25px><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"updatedt('".$bar['kodevhc']."','".$bar['kodeasset']."','".$bar['noakun']."','".@number_format($bar['nilai'])."','".$bar['noaruskas']."','".$bar['keterangan']."','".$bar['noinvoice_referensi']."','".$bar['nourut']."','".$bar['pajak']."','".$bar['kodeblok']."','".$bar['kodekegiatan']."')\"></td>";
		// 		}   
		// 		$frm[0].="<td align=center width=25px><img src=images/skyblue/delete.png class=zImgBtn  title='Delete' onclick=\"deletedt('" . $bar['noinvoice']. "','" . $bar['nourut']. "','" . $bar['notransaksi']. "','" . $bar['nopo']. "','" . $bar['noakun']. "');\" ></td>";  
		// 		$frm[0].="</tr>";
		// }

		$frm[0] .= "</tbody></table></fieldset>";





		// $frm[1].="<fieldset>
		// <legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['upload'] . "</legend>";
		$frm[1] .= "<table cellspacing='1' border='0'>
			<tr>
				<td>" . $_SESSION['lang']['kriteria'] . "</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>" . $optkriteria . "</select>
				</td>
			</tr>
			<tr>	
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload'>
				</td>
			</tr>
			<tr>
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='statusbar'></p>
					<p id='loaded_n_total'></p>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick='submitfile()'>Submit</button>
					<button class=mybutton onclick='loadfiles()'>Selesai</button>
				</td>
				
			</tr>
		</table><br>";

		// $frm[1].="<fieldset>
		// <legend>".$_SESSION['lang']['list']."</legend>";
		$frm[1] .= "<table class='sortable' cellspacing='1' border='0' cellpadding=5>
			<thead>
			<tr class=rowheader>
				<th align='center'>" . $_SESSION['lang']['nourut'] . "</th>
				<th align='center'>File Type</th>
				<th align='center'>Kriteria</th>
				<th align='center'>Filename</th>
				<th align='center' colspan=2>Action</th>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		";

		/*
		
		$frm[2].="<fieldset>
		<legend>" . $_SESSION['lang']['form'] . "</legend>";
		$frm[2].="<table cellspacing='1' border='0'>";
		
		$checked='';
		
		#= data lama kalau sudah tersimpan
		$str="select * from ".$dbname.". keu_tagihandt_checklistdokumen where noinvoice='".$param['noinvoice']."'";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$flag[$bar['kodedokumen']]=$bar['flag'];
		}
		$nochekc=0;
		$str="select * from ".$dbname.".keu_5checklistdokumen where status='1'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$nochekc++;
			$checked='';
			if($flag[$bar['kodedokumen']]=='1'){
				$checked='checked';
			}
			
			$frm[2].="<tr>
				<td hidden id='kodedokumen".$nochekc."'>".$bar['kodedokumen']."</td>
				<td>".$bar['namadokumen']."</td>
				<td>:</td>
					<td style=cursor:pointer><input type='checkbox' ".$checked."  id='flagdokumen".$nochekc."'></td>
			</tr>";
		}
			$frm[2].="<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=savechecklistdokumen('".$noinvoice."','".$nochekc."')>Submit</button>
				</td>
				
			</tr>
		</table></fieldset><br>";
		*/

		$hfrm[0] = strtoupper($_SESSION['lang']['transaksi']);
		$hfrm[1] = strtoupper($_SESSION['lang']['file']);
		// $hfrm[2]=strtoupper($_SESSION['lang']['cek']);
		drawTab('FRM', $hfrm, $frm, 100, 'auto');
		CLOSE_BOX();

		break;


	case 'savechecklistdokumen':

		#= delete 1st
		$str = "delete from " . $dbname . ".keu_tagihandt_checklistdokumen where noinvoice='" . $param['noinvoice'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}

		for ($i = 1; $i <= $param['maxrow']; $i++) {
			$str = "insert into " . $dbname . ".keu_tagihandt_checklistdokumen(noinvoice, kodedokumen, flag) values
			('" . $param['noinvoice'] . "','" . $param['kodedokumen'][$i] . "','" . $param['flagdokumen'][$i] . "')";
			// exit("Error:$str");
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
		}

		break;

	case 'getformum':

		$form = "";
		$form = "<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
		$form .= "<table>";
		$form .= "<tr>";
		$form .= "<td>" . $_SESSION['lang']['notransaksi'] . "</td>";
		$form .= "<td>:</td>";
		$form .= "<td><input type=text class=myinputtext id=transum></td>";
		$form .= "<td><button class=mybutton onclick=findum()>Find</button></td>";
		$form .= "</tr>";
		$form .= "</table>";
		$form .= "</fieldset>
                 <div id=containerum></div>";
		echo $form;
		break;

	case 'getdataum':

		$data = "";
		$dt  = "";

		if ($_POST['transum'] != '') {
			$where .= " and b.noinvoice like '%" . $_POST['transum'] . "%'";
		}

		$data .= "<fieldset><legend>" . $_SESSION['lang']['result'] . "</legend>";
		$data .= "<div style=overflow:auto;width:auto;height:200px;>";
		$data .= "<table cellpading=0 cellspacing=1 width=100% class=sortable >";
		$data .= "<thead>";
		$data .= "<tr>";
		$data .= "<td colspan=4 ><button class=mybutton onclick=adddetail()>" . $_SESSION['lang']['addtodetail'] . "</button></td>";
		$data .= "</tr>";
		$data .= "<tr align=center>";
		$data .= "<td>" . $_SESSION['lang']['noinvoice'] . "</td>";
		$data .= "<td>" . $_SESSION['lang']['akun'] . "</td>";
		$data .= "<td>" . $_SESSION['lang']['nilai'] . "</td>";
		$data .= "<td>" . $_SESSION['lang']['keterangan'] . "</td>";
		$data .= "</tr></thead>";

		#data
		$no = 0;
		$str = "select b.noinvoice, b.nilai, b.noakun, a.keterangan2 from keu_tagihanht a left join keu_tagihandt b on a.noinvoice=b.noinvoice where tipeinvoice='um' and kodesupplier='" . $supplier . "' and nopo='' " . $where . " ";
		//echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {

			$strum = "select noinvoiceum from " . $dbname . ".keu_tagihanht where noinvoiceum='" . $bar->noinvoice . "'";
			$resum = $owlPDO->query($strum) or die(print " Gagal: " . PDOException::getMessage());
			$resum->setFetchMode(PDO::FETCH_OBJ);
			$barum = $resum->fetch();
			if ($bar->noinvoice == $barum->noinvoiceum) {
				continue;
			}

			$str1 = "select noaruskas from " . $dbname . ".keu_5aruskas_detail where noakun='" . $bar->noakun . "'";
			$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1 = $res1->fetch();
			$noaruskasdt = $bar1['noaruskas'];

			$str1 = "select id_ket,keterangan from " . $dbname . ".keu_5keterangan where noaruskas='" . $noaruskasdt . "'";
			$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1 = $res1->fetch();
			$keterangandt = $bar1['id_ket'];

			$nilaium = 0;
			$nilaium = (-1) * $bar->nilai;

			$optnmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $bar->noakun . "'");
			$data .= "<tr class=rowcontent style='cursor:pointer' onclick=getdatadt('" . $bar->noinvoice . "','" . $bar->noakun . "','" . $nilaium . "','" . $noaruskasdt . "','" . $keterangandt . "')>";
			$data .= "<td>" . $bar->noinvoice . "</td>";
			$data .= "<td>" . $optnmakun[$bar->noakun] . "</td>";
			$data .= "<td align=right>" . @number_format($bar->nilai, 2) . "</td>";
			$data .= "<td>" . $bar->keterangan2 . "</td>";
			$data .= "</tr>";
			$no += 1;
		}
		$data .= "</table></div></fieldset>";

		echo $data;
		break;

	case 'saveum':

		$str1 = "select count(noakun) as jumlah from " . $dbname . ".keu_tagihandt where left(noakun,5)='11802' and noinvoice='" . $noinvoice . "'";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		$bar1 = $res1->fetch();
		$jumlah = $bar1['jumlah'];

		if ($jumlah > 0) {
			exit('warning : transaksi ini sudah memiliki uang muka.');
		} else {
			$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,nilai,kodevhc,kodeasset,noaruskas,keterangan)
                values ('" . $noinvoice . "','" . $noakun . "','" . $nilai . "','" . $kodevhc . "','" . $kodeasset . "','" . $noaruskas . "','" . $keterangan . "')";
			try {
				$owlPDO->exec($str);

				$strht = "update " . $dbname . ".keu_tagihanht set noinvoiceum='" . $noinvoiceum . "' where noinvoice='" . $noinvoice . "'";
				try {
					$owlPDO->exec($strht);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		}

		break;

	case 'insertdt':

		// if ($noaruskas=='' || $noakun=='' || $nilai=='' || $nilai==0 || $keterangan=='') {
		if ($noaruskas == '' || $noakun == '' || $nilai == '' || $nilai == 0) {
			exit('Warning : Field was Empty');
		}

		$nilai = str_replace(',', '', $nilai);
		if (substr($noakun, 0, 1) == '4') {
			if ($kodevhc == '') {
				exit('warning: ' . $_SESSION['lang']['kodevhc'] . ' ' . $_SESSION['lang']['kosong']);
			}
		} else {
			$kodevhc = '';
		}

		if ($tipeinvoice == 'poa') {
			if ($kodeasset == '') {
				exit('warning: ' . $_SESSION['lang']['kodeasset'] . ' ' . $_SESSION['lang']['kosong']);
			}
		}

		if ($tipeinvoice != 'um') {
			// if(substr($noakun,0,5)=='11803' && $nilai>0){
			if (substr($noakun, 0, 5) == '11801' && $nilai > 0) {
				exit('Warning: Jika uang muka, jenis invoice pada header harus uang muka pembelian.');
			}

			// if(substr($noakun,0,5)=='11803' && $nilai<0){
			if (substr($noakun, 0, 5) == '11801' && $nilai < 0) {
				exit('Warning: Jika transaksi ini ada uang muka sebelumnya, silahkan buat invoice uang muka terlebih dahulu.');
			}
		}

		if ($noakun == '') {
			exit('warning' . $_SESSION['lang']['noakun'] . ' ' . $_SESSION['lang']['notifemptyzero']);
		}

		if ($nilai == 0) {
			exit('warning' . $_SESSION['lang']['nilai'] . ' ' . $_SESSION['lang']['notifemptyzero']);
		}

		if ($param['tipearuskasdt'] == '') {
			exit("<label hidden>Warning</label> Tipe Aruskas Wajib dipilih");
		}

		if (substr($noakun, 0, 3) == '213') {
			if ($nilai > 0) {
				$nilai = (-1) * ($nilai);
			}
		}

		#= cek tipearuskasht
		$sql = "select tipearuskasht from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
		$res = fetchData($sql)[0];
		$tipearuskasht = $res['tipearuskasht'];
		#= end

		# Cek HT dan DT apakah beda
		if ($param['tipearuskasdt'] != $tipearuskasht) {
			exit("<label hidden>Warning</label> Tipe Arus Kas Detail, tidak sama dengan Tipe Arus Kas Header yang sudah dipilih");
		}

		$kdAplikasi = "HPPOLAH";
		#ambil noakun biaya transit
		$sAkun = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='" . $kdAplikasi . "'";
		$rAkun = fetchData($sAkun);
		$arrNoakun = explode(",", $rAkun[0]['nilai']);
		foreach ($arrNoakun as $row => $dtNoakun) {
			if ($dtNoakun == $noakun) {
				$sTipe = "select a.unit,b.tipe from " . $dbname . ".keu_tagihanht a left join " . $dbname . ".organisasi b
                        on a.unit=b.kodeorganisasi where a.noinvoice='" . $noinvoice . "'";
				$rTipe = fetchData($sTipe);
				if ($rTipe[0]['tipe'] != 'PABRIK') {
					exit('warning: Tipe Organisasi Harus Pabrik');
				}
			}
		}

		if ($param['tipeinvoice'] == 'um') {
			$sql = selectQuery($dbname, "log_transaksi_vw", "*", "nopo='" . $param['nopo'] . "'");
			$res = fetchData($sql, "OBJECT")[0];

			$notransaksigudang = $res->notransaksi;
		}

		#cek unit apakah satu PT
		$rCek = array();
		$sCek = "select * from " . $dbname . ".keu_5caco where jenis='intra' and akunpiutang='" . $noakun . "'";
		$rCek = fetchData($sCek);

		$sUnit = "select * from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
		$rUnit = fetchData($sUnit);

		$optCekPt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $rCek[0]['kodeorg'] . "'");
		if (!empty($rCek)) {
			if ($optCekPt[$rCek[0]['kodeorg']] != $_SESSION['org']['kodeorganisasi']) {
				exit('warning: Noakun Yang Dipilih Beda PT');
			}
			if ($rUnit[0]['unit'] == $rCek[0]['kodeorg']) {
				exit('warning: Noakun Yang Dipilih Harus Beda Unit');
			}
		}


		#= cek nourut
		$str = "select count(*) as jumlah from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$nourut = ($bar['jumlah'] + 1);


		#validasi untuk akun kebun harus ada blok dan harus di kebun gak boleh di lain tempat
		$akunkebun = (substr($noakun, 0, 3) == '128' or substr($noakun, 0, 3) == '126' or substr($noakun, 0, 3) == '621' or substr($noakun, 0, 3) == '611');
		if ($akunkebun and $kodeblok == '') {
			echo "Akun Tanaman Harus di Lengkapi dengan Blok\n";
			echo "Gagal";
			exit;
		}
		$nmtipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		if ($akunkebun and $nmtipeorg[$rUnit[0]['unit']] != 'KEBUN') {
			echo "Kode Unit Akun Tanaman harus di KEBUN\n";
			echo "Gagal";
			exit;
		}

		if ($kodeblok != '') {

			# Induk Blok
			$indukblok = $kodeblok;

			# Get Blok Besar & Kecil
			$sql = "SELECT kelompok FROM " . $dbname . ".setup_kegiatan WHERE kodekegiatan = '" . $kegiatan . "'";
			$req = fetchdata($sql);
			$statusblok = $req[0]['kelompok'];

			if ($statusblok == 'PNN') {
				$statusblok = 'TM';
			} else {
				$statusblok = $statusblok;
			}

			$str = "SELECT  kodeorg as kodeblok,
							indukblok,
							tahuntanam,
							jumlahpokok,
							status,
							luasareaproduktif,
							SUM(luasareaproduktif) OVER(PARTITION BY indukblok) as totalareaproduktif 
						FROM " . $dbname . ".setup_blok 
						WHERE statusblok = '" . $statusblok . "' and indukblok = '" . $kodeblok . "' ORDER BY kodeorg ASC";
			$res = fetchdata($str, "OBJECT");
			foreach ($res as $val) {
				$rupiahproporsi = ($val->luasareaproduktif / $val->totalareaproduktif) * $nilai;

				$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,nilai,kodevhc,kodeasset,noaruskas,keterangan,nourut,pajak,kodeblok,nopo,kodekegiatan,indukblok,tipearuskas)
				values ('" . $noinvoice . "','" . $noakun . "','" . $rupiahproporsi . "','" . $kodevhc . "','" . $kodeasset . "','" . $noaruskas . "','" . $keterangan . "','" . $nourut . "','" . $pajak . "','" . $val->kodeblok . "','" . $nopo . "','" . $kegiatan . "','" . $indukblok . "','" . $tipearuskas . "')";
				try {
					$owlPDO->exec($str);
					$nourut++;
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
		} else { // Jika tidak ada inputan BLOK

			// if($param['tipeinvoice'] == 'um') {
			// 	$str="insert into ".$dbname.".keu_tagihandt (noinvoice,noakun,nilai,kodevhc,kodeasset,noaruskas,keterangan,nourut,pajak,kodeblok,nopo,kodekegiatan,indukblok,tipearuskas,notransaksi)
			// 	values ('".$noinvoice."','".$noakun."','".$nilai."','".$kodevhc."','".$kodeasset."','".$noaruskas."','".$keterangan."','".$nourut."','".$pajak."','".$kodeblok."','".$nopo."','".$kegiatan."','".$indukblok."','".$tipearuskas."','".$notransaksigudang."')";
			// } else {
			// $str="insert into ".$dbname.".keu_tagihandt (noinvoice,noakun,nilai,kodevhc,kodeasset,noaruskas,keterangan,nourut,pajak,kodeblok,nopo,kodekegiatan,indukblok,tipearuskas)
			// values ('".$noinvoice."','".$noakun."','".$nilai."','".$kodevhc."','".$kodeasset."','".$noaruskas."','".$keterangan."','".$nourut."','".$pajak."','".$kodeblok."','".$nopo."','".$kegiatan."','".$indukblok."','".$tipearuskas."')";
			// }

			$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,nilai,kodevhc,kodeasset,noaruskas,keterangan,nourut,pajak,kodeblok,nopo,kodekegiatan,indukblok,tipearuskas)
			values ('" . $noinvoice . "','" . $noakun . "','" . $nilai . "','" . $kodevhc . "','" . $kodeasset . "','" . $noaruskas . "','" . $keterangan . "','" . $nourut . "','" . $pajak . "','" . $kodeblok . "','" . $nopo . "','" . $kegiatan . "','" . $indukblok . "','" . $tipearuskas . "')";

			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		}


		$str = "select kode from " . $dbname . ".pmn_5jenisspk";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$arrspk[] = $bar['kode'];
		}

		$tipeinvoice = strtoupper($tipeinvoice);
		echo '0';
		if (in_array($tipeinvoice, $arrspk)) {
			$str = "select sum(nilai) as nilai from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nilai = $bar['nilai'];

			$str = "update " . $dbname . ".keu_tagihanht set nilaiinvoice='" . $nilai . "' where noinvoice='" . $noinvoice . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}

			echo $nilai;
		}


		break;

	case 'updatedt':
		// exit("Error".$tipeinvoice);

		if ($noaruskas == '' || $noakun == '' || $nilai == '' || $nilai == '') {
			exit('Warning : Field was Empty');
		}

		$nilai = str_replace(',', '', $nilai);

		if ($tipeinvoice == 'ot') {
			if (substr($noakun, 0, 1) == '4') {
				if ($kodevhc == '') {
					exit('warning: ' . $_SESSION['lang']['kodevhc'] . ' ' . $_SESSION['lang']['kosong']);
				}
			} else {
				$kodevhc = '';
			}
		}

		if (substr($noakun, 0, 3) == '213') {
			if ($nilai > 0) {
				$nilai = (-1) * ($nilai);
			}
		}

		#cek unit apakah satu PT
		$rCek = array();
		$sCek = "select * from " . $dbname . ".keu_5caco where jenis='intra' and akunpiutang='" . $noakun . "'";
		$rCek = fetchData($sCek);

		$sUnit = "select * from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
		$rUnit = fetchData($sUnit);

		$optCekPt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $rCek[0]['kodeorg'] . "'");
		if (!empty($rCek)) {
			if ($optCekPt[$rCek[0]['kodeorg']] != $_SESSION['org']['kodeorganisasi']) {
				exit('warning: Noakun Yang Dipilih Beda PT');
			}
			if ($rUnit[0]['unit'] == $rCek[0]['kodeorg']) {
				exit('warning: Noakun Yang Dipilih Harus Beda Unit');
			}
		}

		/*
        $kdAplikasi="HPPOLAH";
        #ambil noakun biaya transit
        $sAkun="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='".$kdAplikasi."'";
        $rAkun=fetchData($sAkun);
        $arrNoakun=explode(",",$rAkun[0]['nilai']);
        foreach($arrNoakun as $row=>$dtNoakun){
            if($dtNoakun==$noakun){
                $sTipe="select a.unit,b.tipe from ".$dbname.".keu_tagihanht a left join ".$dbname.".organisasi b
                        on a.unit=b.kodeorganisasi where a.noinvoice='".$noinvoice."'";
                $rTipe=fetchData($sTipe);
                if($rTipe[0]['tipe']!='PABRIK'){
                    exit('warning: Tipe Organisasi Harus Pabrik');
                }
            }
        }
		*/

		#validasi untuk akun kebun harus ada blok dan harus di kebun gak boleh di lain tempat
		#= hanya others saja yang ada validasi dikarenakan terjadinya jurnal
		if ($tipeinvoice == 'ot') {
			$akunkebun = (substr($noakun, 0, 3) == '128' or substr($noakun, 0, 3) == '126' or substr($noakun, 0, 3) == '621' or substr($noakun, 0, 3) == '611');
			if ($akunkebun and $kodeblok == '') {
				echo "Akun Tanaman Harus di Lengkapi dengan Blok\n";
				echo "Gagal";
				exit;
			}
			$nmtipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
			if ($akunkebun and $nmtipeorg[$rUnit[0]['unit']] != 'KEBUN') {
				echo "Kode Unit Akun Tanaman harus di KEBUN\n";
				echo "Gagal";
				exit;
			}
		}

		// $str = "update ".$dbname.".keu_tagihandt set 
		// 		kodeasset='".$kodeasset."',kodevhc='".$kodevhc."',nilai='".$nilai."',noaruskas='".$noaruskas."',
		// 		keterangan='".$keterangan."',noakun='".$noakun."',pajak='".$pajak."', kodeblok='".$kodeblok."', kodekegiatan='".$kegiatan."'
		// 		where  noinvoice='".$noinvoice."' and noakun='".$hisnoakun."' and noaruskas='".$hisnoaruskas."' and nourut='".$nourut."'"; 

		if ($kodeblok != '') {
			try {
				$owlPDO->beginTransaction();

				# Induk Blok
				$indukblok = $kodeblok;
				$indukblokold = $param['kodeblokold'];

				#=====================================================================================================#
				# DELETE ALL BLOK KECIL SESUAI INDUKBLOK DAN KEGIATAN
				#=====================================================================================================#
				$strdt = "delete  from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "' and notransaksi='" . $notransaksi . "' and nopo='" . $nopo . "' and  noakun='" . $noakun . "' and  kodekegiatan='" . $kegiatan . "' and indukblok='" . $indukblokold . "'";
				$owlPDO->exec($strdt);

				#=====================================================================================================#
				# INSERT DETAIL PER BLOK KECIL
				#=====================================================================================================#

				# Cek Nourut
				$str = "select count(*) as jumlah from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$nourut = ($bar['jumlah'] + 1);

				# Get Blok Besar & Kecil
				$sql = "SELECT kelompok FROM " . $dbname . ".setup_kegiatan WHERE kodekegiatan = '" . $kegiatan . "'";
				$req = fetchdata($sql);
				$statusblok = $req[0]['kelompok'];

				if ($statusblok == 'PNN') {
					$statusblok = 'TM';
				} else {
					$statusblok = $statusblok;
				}

				$str = "SELECT  kodeorg as kodeblok,
								indukblok,
								tahuntanam,
								jumlahpokok,
								status,
								luasareaproduktif,
								SUM(luasareaproduktif) OVER(PARTITION BY indukblok) as totalareaproduktif 
							FROM " . $dbname . ".setup_blok 
							WHERE statusblok = '" . $statusblok . "' and indukblok = '" . $kodeblok . "' ORDER BY kodeorg ASC";
				$res = fetchdata($str, "OBJECT");
				foreach ($res as $val) {
					$rupiahproporsi = ($val->luasareaproduktif / $val->totalareaproduktif) * $nilai;

					$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,nilai,kodevhc,kodeasset,noaruskas,keterangan,nourut,pajak,kodeblok,nopo,kodekegiatan,indukblok)
					values ('" . $noinvoice . "','" . $noakun . "','" . $rupiahproporsi . "','" . $kodevhc . "','" . $kodeasset . "','" . $noaruskas . "','" . $keterangan . "','" . $nourut . "','" . $pajak . "','" . $val->kodeblok . "','" . $nopo . "','" . $kegiatan . "','" . $indukblok . "')";

					$owlPDO->exec($str);
					$nourut++;
				}

				$owlPDO->commit();
			} catch (PDOException $e) {
				$owlPDO->rollback();
				print " Gagal Update (D): " . $e->getMessage() . "\n";
				die();
			}
		} else {
			#= Jangan Update keterangan, karena bug untuk BA Service
			$str = "update " . $dbname . ".keu_tagihandt set 
					kodeasset='" . $kodeasset . "',kodevhc='" . $kodevhc . "',nilai='" . $nilai . "',noaruskas='" . $noaruskas . "',
					noakun='" . $noakun . "',pajak='" . $pajak . "', kodeblok='" . $kodeblok . "', kodekegiatan='" . $kegiatan . "'
					where  noinvoice='" . $noinvoice . "' and noakun='" . $hisnoakun . "' and noaruskas='" . $hisnoaruskas . "' and nourut='" . $nourut . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}


		// echo headerupdate($noinvoice,$noakun,$nilai,$proses);

		break;

	case 'deletedt':

		// $whr=" noinvoice='".$noinvoice."' and noakun='".$noakun."' and kodevhc='".$kodevhc."' and noaruskas='".$noaruskas."' and noinvoice_referensi='".$_POST['noinvoice_referensi']."' and nourut='".$_POST['nourut']."'   ";
		// $strdt = "delete from ".$dbname.".keu_tagihandt where ".$whr;
		$strn = "select * from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "' and nourut='" . $nourut . "' and notransaksi='" . $notransaksi . "' and nopo='" . $nopo . "' and  noakun='" . $noakun . "' and indukblok='" . $param['indukblok'] . "'";
		$resn = fetchdata($strn);
		$nilai = $resn[0]['nilai'];

		if ($param['indukblok'] != '') {
			$strdt = "delete from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "' and notransaksi='" . $notransaksi . "' and nopo='" . $nopo . "' and  noakun='" . $noakun . "' and indukblok='" . $param['indukblok'] . "'";
		} else {
			$strdt = "delete from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "' and nourut='" . $nourut . "' and notransaksi='" . $notransaksi . "' and nopo='" . $nopo . "' and  noakun='" . $noakun . "' and (indukblok='" . $param['indukblok'] . "' OR indukblok IS NULL)";
		}
		// exit('warning'.$strdt);
		try {

			$owlPDO->exec($strdt);

			$str1 = "SELECT noinvoiceum from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
			$res1 = fetchData($str1);
			$noinvoiceumuka = $res1[0]['noinvoiceum'];

			if ($noinvoiceumuka != '' && substr($noakun, 0, 5) == '11802') {
				$strht = "update " . $dbname . ".keu_tagihanht set noinvoiceum='' where noinvoice='" . $noinvoice . "'";
				try {
					$owlPDO->exec($strht);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
		} catch (PDOException $e) {
			print " Gagal: " . $e->getMessage() . "\n";
			die();
		}

		// echo headerupdate($noinvoice,$noakun,$nilai,$proses);
		break;

	case 'showformfp':
		$tab .= "<table>
            <tr>
            <td>" . $_SESSION['lang']['historynofp'] . "</td>
            <td>:</td>
            <td  colspan=2><input type=text id=historynofp onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:150px;\" value='' /></td>
            </tr>
            <tr>
            <td>" . $_SESSION['lang']['tanggal'] . "</td>
            <td>:</td>
            <td><input type=text class=myinputtext readonly  id=historytanggalfp onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\" value=''    /></td>
			</tr>
            <tr>
            <td></td><td></td><td><button class=mybutton onclick=savefp('" . $noinvoice . "')>Simpan</button></td>
            </tr>
            </table>";
		echo $tab;
		break;

	case 'savefp':
		$strht = "update " . $dbname . ".keu_tagihanht set historynofp='" . $param['historynofp'] . "',historytanggalfp='" . tanggalsystem($param['historytanggalfp']) . "',jenisfp='01' where noinvoice='" . $noinvoice . "'";
		try {
			$owlPDO->exec($strht);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'loadData':
		$where = '1=1';


		$where = " 1=1 ";
		$where .= " and unit in (" . getOrgDetail(2) . ")";
		// if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' || $_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
		// 	$where .= "";
		// } else {
		// $where.=" and (unit='".$_SESSION['empl']['lokasitugas']."'";
		// $where.= " or unit in (".getOrgDetail(2)."))";
		// $where .= " and lokasitugasuser='" . $_SESSION['empl']['lokasitugas'] . "'";
		// }

		// if($param['unit']==''){
		// $where.= " and (unit in (".getOrgDetail(2).") or createby='".$_SESSION['standard']['userid']."')";
		// }else{
		// $where.=" and unit = '".$param['unit']."'";
		// }

		if ($param['unit'] != '') {
			$where .= " and unit = '" . $param['unit'] . "'";
		}

		if ($param['tanggalmulai'] != '' and $param['tanggalselesai'] != '') {
			$where .= " and tanggal between '" . tanggalsystemn($param['tanggalmulai']) . "' and '" . tanggalsystemn($param['tanggalselesai']) . "'";
		}

		if ($param['noinvoice'] != '') {
			$where .= " and noinvoice like '%" . $param['noinvoice'] . "%'";
		}

		if ($param['noinvoicesupplier'] != '') {
			$where .= " and noinvoicesupplier like '%" . $param['noinvoicesupplier'] . "%'";
		}

		if ($param['kodesupplier'] != '') {
			$where .= " and kodesupplier like '%" . $param['kodesupplier'] . "%'";
		}

		if ($param['nopo'] != '') {
			$where .= " and nopo like '%" . $param['nopo'] . "%'";
		}
		if ($param['posting'] != '') {
			$where .= " and posting='" . $param['posting'] . "'";
		}


		if ($param['tipeinvoice'] != '') {
			$where .= " and tipeinvoice='" . $param['tipeinvoice'] . "'";
		}

		// echo $where;

		// exit("Error:".$where);exit();

		// print_r($param);



		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = intval($_POST['page']);
			if ($page < 0)
				$page = 0;
		}
		@$offset = $page * $limit;
		@$maxdisplay = ($page * $limit);


		$str = "select * from " . $dbname . ".keu_tagihanht where " . $where;
		$res = fetchData($str);
		$jlhbrs = count($res);
		if ($jlhbrs == 0) {
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td colspan=23 align=center>" . $_SESSION['lang']['dataempty'] . "</td>";
			$tab .= "</tr>";
		} else {
			$no = $maxdisplay;
			$str = "SELECT * from " . $dbname . ".keu_tagihanht where " . $where . " order by posting asc, tanggal desc limit " . $offset . "," . $limit . "";
			$tab = "";
			$res = fetchData($str);
			foreach ($res as $row => $bar) {
				$nilaidt = 0;
				#pembuat
				$whrKar2 = "karyawanid='" . $bar['updateby'] . "'";
				$optpembuat = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whrKar2);
				$whrsup = "supplierid='" . $bar['kodesupplier'] . "'";
				$optSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', $whrsup);
				$whrKar3 = "karyawanid='" . $bar['postingby'] . "'";
				$optPosting = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whrKar3);

				##Cek Effil
				$strx = "select count(noinvoice) as count from " . $dbname . ".keu_efillinv where noinvoice='" . $bar['noinvoice'] . "'";
				$resx = fetchdata($strx);
				$countefil = $resx[0]['count'];

				##buat selisih
				$strd = "select * from " . $dbname . ".keu_tagihandt where noinvoice='" . $bar['noinvoice'] . "'";
				$resd = fetchdata($strd);
				foreach ($resd as $bard) {

					// if($statusjurnal[$bar['tipeinvoice']]=='0' and substr($bard['noakun'],0,5)=='11803' and $bar['tipeinvoice']!='um'){
					if ($statusjurnal[$bar['tipeinvoice']] == '0' and substr($bard['noakun'], 0, 5) == '11801' and $bar['tipeinvoice'] != 'um') {
						$bard['nilai'] = 0;
					}
					$nilaidt += $bard['nilai'];
				}




				$colspan = 'colspan=4';
				$tab .= "<tr class=rowcontent>
                    <td>" . $bar['noinvoice'] . "</td>
                    <td>" . $bar['noinvoicesupplier'] . "</td>
                    <td>" . $bar['tipeinvoice'] . "</td>
					<td>" . $bar['noakun'] . "</td>
                    <td>" . $bar['kodeorg'] . "</td>
                    <td>" . $bar['unit'] . "</td>
                    <td>" . tanggalnormal($bar['tanggalinvoice']) . "</td>
                    <td>" . tanggalnormal($bar['tanggal']) . "</td>
                    <td>" . $bar['nopo'] . "</td>
                    <td>" . $optSup[$bar['kodesupplier']] . "</td>
                    
                    <td>" . $bar['keterangan'] . "</td>";
				// $tab.="<td align=right>".@number_format($bar['nilaidpp'],2)."</td>";
				$tab .= "<td align=right>" . @number_format($bar['nilaiinvoice'], 2) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['nilaiinvoice'] - $nilaidt, 2) . "</td>";
				$tab .= "<td>" . $optpembuat[$bar['createby']] . "<br>" . $bar['createtime'] . "</td>";
				$tab .= "<td>" . $optpembuat[$bar['updateby']] . "<br>" . $bar['updatetime'] . "</td>
                    <td>" . @$optPosting[$bar['postingby']] . "<br>" . $bar['postingdate'] . "</td>";
				if ($bar['posting'] == 0 || $bar['posting'] == 3) {
					$tab .= "<td><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"editdt('" . @$bar['noinvoice'] . "','" . @$bar['noinvoicesupplier'] . "','" . tanggalnormal(@$bar['tanggal']) . "','" . tanggalnormal(@$bar['jatuhtempo']) . "','" . @$bar['kodeorg'] . "','" . @$bar['unit'] . "','" . @$bar['tipeinvoice'] . "','" . @$bar['nopo'] . "','" . @$bar['kodesupplier'] . "','" . @$bar['nofp'] . "','" . @$bar['jenistransaksi'] . "','" . tanggalnormal(@$bar['tanggalinvoice']) . "','" . tanggalnormal(@$bar['tanggalnofp']) . "','" . number_format(@$bar['nilaidpp'], 2) . "','" . number_format(@$bar['nilaiinvoice'], 2) . "','" . @$bar['keterangan'] . "','" . @$bar['noakun'] . "','" . @$bar['matauang'] . "','" . @$bar['kurs'] . "','" . @$bar['npwp'] . "','" . @$bar['npwppph'] . "','" . @$bar['reksupplier'] . "','" . @$bar['jenissupplier'] . "','" . @$bar['bagian'] . "','" . $bar['tipearuskasht'] . "')\"></td>
                           <td><img src=images/skyblue/delete.png class=zImgBtn  title='Delete' onclick=\"deleteht('" . @$bar['noinvoice'] . "');\" ></td>
                           <td align=center><img src=images/skyblue/posting.png class=zImgBtn  title='Posting Data' onclick=\"postingData('" . @$bar['noinvoice'] . "');\" ></td>";
				} else {
					$tab .= "<td>&nbsp;</td>";
					$tab .= "<td>&nbsp;</td>";
					$tab .= "<td align=center><img src=images/skyblue/posted.png class=zImgBtn  title='Posted' ></td>";
				}
				$tab .= "<td align=center><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailPDF('" . $bar['noinvoice'] . "',event);\" ></td>";
				$tab .= "<td align=center><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View Detail' onclick=\"viewDetailData2('" . $bar['noinvoice'] . "',event);\" ></td>";
				if ($bar['historynofp'] == '' || $bar['nofp'] == '-') {
					$tab .= "<td align=center><img src=images/application/exchange.png class=zImgBtn class=zImgBtn height='30'  title='Rubah Faktur Pajak' onclick=\"fakturpajak('" . $bar['noinvoice'] . "',event);\" ></td>";
				} else {
					$tab .= "<td>&nbsp;</td>";
				}
				if ($countefil > 0) {
					$tab .= "<td style='text-align:center'><img src='images/efill.png' class='zImgBtn' onclick=\"viewefill('" . $bar['noinvoice'] . "',event)\" title='E-Filling System'></td>";
				} else {
					$tab .= "<td>&nbsp;</td>";
				}
				$tab .= "</tr>";
			}
			$totrows = ceil($jlhbrs / $limit);

			if ($totrows == 0) {
				$totrows = 1;
			}
			$isiRow = '';
			for ($er = 1; $er <= $totrows; $er++) {
				$sel = ($page == $er - 1) ? 'selected' : '';
				$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
			}
			$footd = "
                <tr><td colspan=23  valign=top align=center>
                <img src=\"images/skyblue/first.png\"  onclick=loadData(0);>
                <img src=\"images/skyblue/prev.png\"  onclick=loadData(" . (@$page - 1) . ");>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
                <img src=\"images/skyblue/next.png\"  onclick=loadData(" . (@$page + 1) . ");>
                <img src=\"images/skyblue/last.png\"  onclick=loadData(" . ($totrows - 1) . ");>
                </td>
                </tr>";
		}
		echo $tab . "####" . @$footd;
		break;
	case 'saveHutang':
		$noinvoice = $data['noinvoice'] = date('Ymdhis');

		if ($param['suppIdHtg'] == '') {
			exit('warning:' . $_SESSION['lang']['namasupplier'] . " " . $_SESSION['lang']['kosong']);
		}

		#perseriapan detail data
		$sInsertDet = "";
		$varNoakun = $param['noakundetail'];
		$optArusKas = makeOption($dbname, "keu_5aruskas_detail", "noakun,noaruskas", "noakun='" . $varNoakun . "'");
		$optKet = makeOption($dbname, "keu_5keterangan", "noaruskas,id_ket", "noaruskas='" . $optArusKas[$varNoakun] . "'");
		$sInsertDet = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,noaruskas,keterangan,nilai,noinvoice_referensi) values ";
		foreach ($param['noInv'] as $row => $lst) {
			$totNilRp += $param['nilaiRp'][$row];
			if ($row == 0) {
				$sInsertDet .= "('" . $noinvoice . "','" . $varNoakun . "','" . $optArusKas[$varNoakun] . "','" . $optKet[$optArusKas[$varNoakun]] . "','" . $param['nilaiRp'][$row] . "','" . $lst . "')";
			} else {
				$sInsertDet .= ",('" . $noinvoice . "','" . $varNoakun . "','" . $optArusKas[$varNoakun] . "','" . $optKet[$optArusKas[$varNoakun]] . "','" . $param['nilaiRp'][$row] . "','" . $lst . "')";
			}
		}
		$supplierid = $param['suppIdHtg'];
		$sNoakun = "select * from " . $dbname . ".log_5klsupplier where tipe='SUPPLIER'";
		$rNoakun = fetchData($sNoakun);
		$cols = "noinvoice,tanggal,kodeorg,unit,npwp,tipeinvoice,jatuhtempo,noinvoicesupplier,kodesupplier,noakun,nilaiinvoice,matauang,tanggalinvoice,tanggalnofp,nopo) values ";
		$data['noinvoice'] = $noinvoice;
		$data['tanggal'] = tanggalsystemn($param['tanggal']);
		$data['kodeorg'] = $param['kodeorg'];
		$data['unit'] = $param['unit'];
		$data['npwp'] = $param['npwp'];
		$data['tipeinvoice'] = $param['tipeinvoice'];
		$data['jatuhtempo'] = tanggalsystemn($param['jatuhtempo']);
		$data['noinvoicesupplier'] = $param['noinvoicesupplier'];
		$data['kodesupplier'] = $supplierid;
		$data['noakun'] = $rNoakun[0]['noakun'];
		$data['nilaiinvoice'] = $param['totRpAll'];
		$data['matauang'] = "IDR";
		$data['tanggalinvoice'] = tanggalsystemn($param['tanggal']);
		$data['tanggalnofp'] = tanggalsystemn($param['tanggal']);


		// $query="insert into ".$dbname.".keu_tagihanht (".$cols.") 
		//         values ('".$data['noinvoice']."','".$data['tanggal']."','".$data['kodeorg']."','".$data['unit']."','".$data['npwp']."','".$data['tipeinvoice']."','".$data['jatuhtempo']."','".$data['noinvoicesupplier']."','".$data['kodesupplier']."','".$data['noakun']."','".$data['nilaiinvoice']."')";
		$cols = array();
		foreach ($data as $key => $row) {
			$cols[] = $key;
		}

		$query = insertQuery($dbname, 'keu_tagihanht', $data, $cols);
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {
			print " Gagal, DB Error  1!: " . $e->getMessage() . "<br/>" . $query;
			die();
		}

		try {
			$owlPDO->exec($sInsertDet);
		} catch (PDOException $e) {
			#rollback
			$delHt = "delete from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
			try {
				$owlPDO->exec($delHt);
			} catch (PDOException $e) {
				print " Gagal, DB Error  1!: " . $e->getMessage() . "<br/>";
				die();
			}
			#del det
			$deldt = "delete from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "'";
			try {
				$owlPDO->exec($deldt);
			} catch (PDOException $e) {
				print " Gagal, DB Error  del Dt!: " . $e->getMessage() . "<br/>" . $deldt;
				die();
			}

			print " Gagal, DB Error  2!: " . $e->getMessage() . "\n" . $sInsertDet;
			die();
		}
		echo $supplierid . "####" . @number_format($totNilRp, 2) . "####" . $rNoakun[0]['noakun'] . "####IDR####1####" . $noinvoice . "####" . $tempSupp;
		break;
	/*
	case 'showupload':
		$tab="";
		
		$arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}
		
		$tab.="<table cellspacing='1' border='0' id='uploadpopup'>
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
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		<p />";
		
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
		
		echo $tab;
	break;
	
	case 'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		
		
		// exit("Error".$_FILES['file']['size']);
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar'))
				{
					if($_FILES['file']['size'] <= 5120000)
					{
						$newdata = array(
							'idfiledt'=>$idfiledt,
							'tipe'=>'1',
							'location'=>'filegis/'.$filename,
							'namafile'=>$filename,
							'formaticon'=>$filetype,
							'kriteriaefil'=>$kriteriaefil,
							'size'=>$_FILES['file']['size']
						);
						
						if($_SESSION['efiltgh'] != array())
						{
							foreach($_SESSION['efiltgh'] as $key=>$row)
							{
								if($row['namafile'] == $filename)
								{
									exit("Warning : Item ini sudah pernah diinput sebelumnya.");
								}
							}
							array_push($_SESSION['efiltgh'],$newdata);
						}else{
							array_push($_SESSION['efiltgh'],$newdata);
						}
						move_uploaded_file($file_tmpname,"filegis/$filename");
					}
					else
					{
						exit("warning : Ukuran file upload maksimal 5 Mb");
					}
				}else{
					exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
				}
			}
		}
	break;
	*/



	case 'submitfile':

		// $filesize=1;

		#= jadikan try commi
		try {

			$owlPDO->beginTransaction();

			$tgl = date("YmdHis");
			$his = date("His");
			$nmTemp = $noinvoice;

			if ($_FILES['file']['size'] > $filesize) {
				throw new PDOException("Ukuran File melebihi " . number_format($filesize / 1024, 2) . " Kb; ukuran file ini " . number_format($_FILES['file']['size'] / 1024, 2) . " Kb");
			}

			if ($param['fileupload'] != '') {
				if ($_FILES['file']['error'] == 0) {
					$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
					$filename = $param['kriteriaefil'] . "_" . $nmTemp . "_" . $his . "" . $filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
					if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.rar') || ($filetype == '.gz') || ($filetype == '.zip') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
						$str = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $filename . "','" . $filetype . "','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path . $filename, $file_tmpname);
					} else {
						throw new PDOException("Format file upload tidak boleh " . $filetype);
					}
				}
			} else {
				throw new PDOException("Upload file gagal.");
			}

			if (!file_exists($path . $filename)) {
				throw new PDOException("File gagal diupload");
			}

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan upload data \n" . addslashes($e->getMessage());
		}

		break;





	case 'submitfileLAMA':
		$tgl = date("YmdHis");
		$his = date("His");
		$nmTemp = str_replace('-', '', str_replace('/', '', $param['notransaksi']));
		/*echo"<pre>";
        print_r($_FILES['file']);
        echo"</pre>";
        exit('error');*/
		if ($param['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$filename = $kriteriaefil . "_" . $nmTemp . "_" . $his . "" . $filetype;
				// exit("Error:".$filename);
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				// listfile_keu_kasbank
				// listfileupload
				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.rar') || ($filetype == '.gz') || ($filetype == '.zip') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
					$str = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $filename . "','" . $filetype . "','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
					try {
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path . $filename, $file_tmpname);
					} catch (PDOException $e) {
						echo " Gagal," . addslashes($e->getMessage());
					}
				} else {
					exit("Warning : Format file upload tidak boleh " . $filetype);
				}
			}
		}
		break;

	/*
	case 'loadfiles':
		$no = 0;
		$tab = "";
		
		$coundata = count($_SESSION['efiltgh']);
		
		if($coundata <= 0)
		{
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		else
		{
			foreach($_SESSION['efiltgh'] as $key=>$val)
			{
				$no++;
				$tab.="<tr id='ppDetailTable' class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
				{
					$tab.="<td style='text-align:center'>
						<a href='filegis/".$val['namafile']."' download><img src=images/uploader/jpg.png class=zImgBtn title='JPG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.png')
				{
					$tab.="<td style='text-align:center'>
						<a href='filegis/".$val['namafile']."' download><img src=images/uploader/png.png class=zImgBtn  title='PNG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.pdf')
				{
					$tab.="<td style='text-align:center'>
						<a href='filegis/".$val['namafile']."' download><img src=images/uploader/pdf.png class=zImgBtn  title='PDF'></a>
					</td>";
				}
				elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
				{
					$tab.="<td style='text-align:center'>
						<a href='filegis/".$val['namafile']."' download><img src=images/uploader/excel.png class=zImgBtn  title='xls'></a>
					</td>";
				}
				elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
				{
					$tab.="<td style='text-align:center'>
						<a href='filegis/".$val['namafile']."' download><img src=images/uploader/word.png class=zImgBtn  title='doc'></a>
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>
						<a href='filegis/".$val['namafile']."' download><img src=images/uploader/jpg.png class=zImgBtn  title='jpg'></a>
					</td>";
				}
				
				$tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
					<td style='text-align:left'>".$val['namafile']."</td>
					<td align=center>
						<a href='filegis/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>&nbsp";
				if($close==0){
					$tab.="<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('".$nopp."','".$val['namafile']."');\" >";
				}
				$tab."	</td>
				</tr>";
			}	
		}
		echo $tab;
	break;
	
	case'deletefile':
		foreach($_SESSION['efiltgh'] as $key=>$val)
		{
			if($val['namafile'] == $namafile)
			{
				$path = "filegis/".$namafile;
				unlink($path);
				unset($_SESSION['efiltgh'][$key]);
			}
		}
	break;
	*/


	case 'deletefile':
		// $namafile=$param['namafile'];
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['noinvoice'] . "' and namafile='" . $param['namafile'] . "'";
		// exit('error'.$str);
		try {
			$owlPDO->exec($str);
			// $pathx = $path.str_replace('/','',$param['namafile']);
			// unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'loadfiles':
		$form = '';
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' ";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form .= "<tr class=rowcontent >";
			$form .= "<td style='text-align:center'>" . $no . "</td>";
			$form .= "<td align='center'><img src=" . $icon . " class=zImgBtn></a></td>";
			$form .= "<td>" . getcriterianame($bar['kriteriaefil']) . "</td>";
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download>" . $bar['namafile'] . "</td>";
			$form .= "<td align=center width=25px><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a></td><td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('" . $bar['notransaksi'] . "','" . $bar['namafile'] . "');\" ></td>";
			$form .= "</tr>";
		}
		echo $form;
		break;



	case 'clearData':
		$_SESSION['efiltgh'] = array();
		break;


	case 'getnodok':
		// print_r($param);
		$optjenis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$optjenis .= "<option value='etc'>Lain-Lain</option>";
		$optjenis .= "<option value='ipk'>Instruksi Pemuatan Kargo</option>";
		$optjenis .= "<option value='ipkd'>Instruksi Pemuatan Kargo Darat</option>";
		$optjenis .= "<option value='sda'>Sampling dan Analisa</option>";
		$optjenis .= "<option value='sp'>Surat Pemberitahuan Pengiriman Antar Pulau</option>";
		$optjenis .= "<option value='spp'>Surat Permintaan Ponton</option>";
		$optjenis .= "<option value='sub'>Surveyor Bongkar</option>";
		$optjenis .= "<option value='sum'>Surveyor Pemuatan</option>";
		$optjenis .= "<option value='tkbm'>Tenaga Kerja Bongkar Muat</option>";
		// $optjenis.="<option value='sip'>Surat Instruksi Pengiriman</option></select>";


		// $tab.="<fieldset><legend>".$_SESSION['lang']['form']."</legend>";
		$tab .= "<table>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['nodok'] . "</td>
					<td>:</td>
					<td><input type=text id=nodokcr value='" . date('Y') . "' size=50 class=myinputtext style=\"width:150px;\"></td>";

		$hidden = 'hidden';
		if ($param['tipeinvoice'] == 'spks') {
			$hidden = '';
		}
		// $tab.="<tr ".$hidden.">
		$tab .= "<td " . $hidden . ">" . $_SESSION['lang']['jenis'] . "</td>
				<td " . $hidden . ">:</td>
				<td " . $hidden . "><select id=jeniscr style=width:150px;>" . $optjenis . "</select></td>";

		// $tab.="<tr>
		// <td></td>
		// <td></td>
		// <td><button class=mybutton onclick=findnodok()>".$_SESSION['lang']['find']."</button></td>";
		// $tab.="</tr>";
		$tab .= "<td><button class=mybutton onclick=findnodok()>" . $_SESSION['lang']['find'] . "</button></td>";
		$tab .= "</tr>";

		$tab .= "</table>";
		// $tab.="</fieldset>";
		$tab .= "<div style=clear:both></div>";
		// $tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
		$tab .= "<div id=formpencariannodoktampil></div>";
		// $tab.="</fieldset>";

		$tab .= "<div style=clear:both></div>";
		// $tab.="<fieldset><legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['detail']."</legend>";
		// $tab.="<fieldset><legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['detail']."</legend>";
		$tab .= "<div id=formpencariannodoktampildetail></div><br>";

		// $tab.="</fieldset>";

		echo $tab;
		break;



	#=====================================================================================================================================================
	#=====================================================================================================================================================





	case 'findnodok':


		$stream = '';
		$stream .= "<div style=overflow:auto;max-height:40vh;>";
		$stream .= "<table cellpadding=3 cellspacing=1 border=0 width=100% class='sortable'>";

		// echo"<pre>";
		// print_r($param);

		switch ($param['tipeinvoice']) {

			case 'rtg':
				$stream .= "<thead>";
				$stream .= "<tr>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nourut'] . "</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nopo'] . "</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['namasupplier'] . "</th>";
				$stream .= "<th>" . $_SESSION['lang']['gudang'] . "</th>";
				$stream .= "<th>" . $_SESSION['lang']['retur'] . "</th>";
				$stream .= "</tr>";
				$stream .= "</thead>";


				if ($param['nodok'] != '') {
					$where = " and nopo like '%" . $param['nodok'] . "%'";
				}

				$str = "select * from " . $dbname . ".log_poht where stat_release=1 and kodeorg='" . $param['kodeorg'] . "' and kodeunit='" . $param['unit'] . "' " . $where . " ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnopo[$bar['nopo']] = $bar['nopo'];
					$dtkodesupplier[$bar['nopo']] = $bar['kodesupplier'];
					$dtsubtotal[$bar['nopo']] = $bar['subtotal'] - $bar['nilaidiskon'];
					$dtnilaipo[$bar['nopo']] = $bar['nilaipo'];
					$dtmatauang[$bar['nopo']] = $bar['matauang'];
				}

				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnopo) . "') and tipeinvoice!='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}

				#= case po inventory table penerimaan = log_transaksi_vw
				#= case po inventory table penerimaan = log_noninventorydt_vw

				$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo from " . $dbname . ".log_transaksi_vw where nopo in ('" . implode("','", $arrnopo) . "') and post=1  and tipetransaksi='1' group by nopo";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$bar['nopo'] = strtoupper($bar['nopo']);
					$dthartot[$bar['nopo']] = $bar['hartot'];
				}


				$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo from " . $dbname . ".log_transaksi_vw
				where nopo in ('" . implode("','", $arrnopo) . "') and post=1  and tipetransaksi='6' group by nopo";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dthartotretur[$bar['nopo']] = $bar['hartot'];
				}

				foreach ($arrnopo as $dtnopo) {
					@$no += 1;
					// echo"<pre>";
					// print_r($dthartot[$dtnopo]);
					// echo"</pre>";
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnopo . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnopo . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtkodesupplier[$dtnopo]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dthartot[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dthartotretur[$dtnopo], 2) . "</td>";
					$stream .= "</tr>";
				}


				break;




			case 'rtn':
				$stream .= "<thead>";
				$stream .= "<tr>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nourut'] . "</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nopo'] . "</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['namasupplier'] . "</th>";
				$stream .= "<th>" . $_SESSION['lang']['gudang'] . "</th>";
				$stream .= "<th>" . $_SESSION['lang']['retur'] . "</th>";
				$stream .= "</tr>";
				$stream .= "</thead>";


				if ($param['nodok'] != '') {
					$where = " and nopo like '%" . $param['nodok'] . "%'";
				}

				$str = "select * from " . $dbname . ".log_poht where stat_release=1 and kodeorg='" . $param['kodeorg'] . "' and kodeunit='" . $param['unit'] . "' " . $where . " ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnopo[$bar['nopo']] = $bar['nopo'];
					$dtkodesupplier[$bar['nopo']] = $bar['kodesupplier'];
					$dtsubtotal[$bar['nopo']] = $bar['subtotal'] - $bar['nilaidiskon'];
					$dtnilaipo[$bar['nopo']] = $bar['nilaipo'];
					$dtmatauang[$bar['nopo']] = $bar['matauang'];
				}

				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnopo) . "') and tipeinvoice!='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}



				$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo from " . $dbname . ".log_noninventorydt_vw where nopo in ('" . implode("','", $arrnopo) . "') and posting=1 group by nopo";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$bar['nopo'] = strtoupper($bar['nopo']);
					$dthartot[$bar['nopo']] = $bar['hartot'];
				}

				$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo from " . $dbname . ".log_retursuppliernoninventorydt_vw
				where nopo in ('" . implode("','", $arrnopo) . "') and posting=1 group by nopo";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dthartotretur[$bar['nopo']] = $bar['hartot'];
				}

				foreach ($arrnopo as $dtnopo) {
					@$no += 1;
					// echo"<pre>";
					// print_r($dthartot[$dtnopo]);
					// echo"</pre>";
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnopo . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnopo . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtkodesupplier[$dtnopo]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dthartot[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dthartotretur[$dtnopo], 2) . "</td>";
					$stream .= "</tr>";
				}


				break;

			case 'p':
			case 'pon':
			case 'pocbd':
				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<th rowspan=2>No.</th>";
				$stream .= "<th align=center rowspan=2>" . $_SESSION['lang']['nopo'] . "</th>";
				$stream .= "<th align=center rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</th>";
				$stream .= "<th align=center rowspan=2>" . $_SESSION['lang']['matauang'] . "</th>";
				$stream .= "<th align=center rowspan=2>" . $_SESSION['lang']['gudang'] . "</th>";
				$stream .= "<th align=center rowspan=2>" . $_SESSION['lang']['retur'] . "</th>";
				$stream .= "<th align=center colspan=7>" . $_SESSION['lang']['nilai'] . " PO</th>";
				$stream .= "<th align=center colspan=2>" . $_SESSION['lang']['uangmuka'] . " PO</th>";
				$stream .= "<th align=center colspan=6>" . $_SESSION['lang']['nilaiinvoice'] . "</th>";
				$stream .= "<th align=center colspan=6>" . $_SESSION['lang']['sisa'] . "<br>(PO-Invoice)</th>";
				$stream .= "</tr>";
				$stream .= "<tr>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " DPP</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " PPN</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> Pengurang (PPH 22)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> Penambah (PPH 22)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> (PPH 23)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> (PBBKB)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " DPP</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " DPP</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " PPN</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> Pengurang (PPH 22)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> Penambah (PPH 22)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> (PPH 23)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " DPP</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " PPN</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> Pengurang (PPH 22)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> Penambah (PPH 22)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['nilai'] . " <br/> (PPH 23)</th>";
				$stream .= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
				$stream .= "</tr>";
				$stream .= "</thead>";


				if ($param['nodok'] != '') {
					$where = " and nopo like '%" . $param['nodok'] . "%'";
				}

				# Id Franco
				$franco = makeOption($dbname, "setup_franco", "kodeunit,id_franco");

				# Syarat Bayar
				$syaratbayarnew = makeOption($dbname, "log_5syaratbayar", "keterangan,kode");

				if ($param['tipeinvoice'] == 'p') {
					// $str="select * from ".$dbname.".log_poht where stat_release=1 and tipepo='PO' and syaratbayar!='CBD' and kodeorg='".$param['kodeorg']."' and kodeunit='".$param['unit']."' ".$where." ";
					$str = "select * from " . $dbname . ".log_poht where stat_release=1 and tipepo='PO' and syaratbayar!='{$syaratbayarnew['CBD']}' and kodeorg='" . $param['kodeorg'] . "' and idFrancoinvc='" . $franco[$param['unit']] . "' " . $where . " ";
				}
				if ($param['tipeinvoice'] == 'pocbd') {
					// $str="select * from ".$dbname.".log_poht where stat_release=1 and  syaratbayar='CBD' and kodeorg='".$param['kodeorg']."' and kodeunit='".$param['unit']."' ".$where." ";

					$str = "select * from " . $dbname . ".log_poht where stat_release=1 and syaratbayar='" . $syaratbayarnew['CBD'] . "' and kodeorg='" . $param['kodeorg'] . "' and idFrancoinvc='" . $franco[$param['unit']] . "' " . $where . " ";

					// if ($_SESSION['standard']['username'] == 'tim.owl3') {
					// 	echo $str;
					// }
				}
				if ($param['tipeinvoice'] == 'pon') {
					// $str="select * from ".$dbname.".log_poht where stat_release=1 and tipepo!='PO' and kodeorg='".$param['kodeorg']."' and kodeunit='".$param['unit']."' ".$where." ";
					$str = "select * from " . $dbname . ".log_poht where stat_release=1 and tipepo!='PO' and kodeorg='" . $param['kodeorg'] . "' and idFrancoinvc='" . $franco[$param['unit']] . "' " . $where . " ";
				}
				// echo $str;exit();
				// $str="select * from ".$dbname.".log_poht where kodeorg='".$param['kodeorg']."' and kodeunit='".$param['unit']."' ".$where." ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					# Cek SO Material
					// $rpmaterial = fetchData(selectQuery($dbname, "log_somaterial", "SUM(harga*jumlah) as rpmaterial", "nopo='{$bar['nopo']}'"))[0]['rpmaterial'];

					$arrnopo[$bar['nopo']] = $bar['nopo'];
					$pbbkb[$bar['nopo']] = $bar['pbbkb'];
					$dtkodesupplier[$bar['nopo']] = $bar['kodesupplier'];
					$dtsubtotal[$bar['nopo']] = $bar['subtotal'] - $bar['nilaidiskon'] + $rpmaterial;
					$dtnilaipo[$bar['nopo']] = $bar['nilaipo'];
					$dtmatauang[$bar['nopo']] = $bar['matauang'];
					$nilaidiskonfn[$bar['nopo']] = ($bar['subtotal'] * $bar['diskonpersen']) / 100;
					$nilaippnfn[$bar['nopo']] = (($bar['subtotal'] + $rpmaterial) - ($bar['subtotal'] * $bar['diskonpersen']) / 100) * $bar['persenppn'] / 100;

					# Array Penambah PPh 22
					$arrpph22penambah[$bar['nopo']] = $bar['penambahpph22'];

					if ($bar['pph22'] > 0) {
						$nilaipphfn[$bar['nopo']] = ($bar['subtotal'] - ($bar['subtotal'] * $bar['diskonpersen']) / 100) * $bar['persenpph'] / 100;
					}

					if ($bar['pph'] > 0) {
						# Cek SO Material
						$rpmaterial = fetchData(selectQuery($dbname, "log_somaterial", "SUM(harga*jumlah) as rpmaterial", "nopo='{$bar['nopo']}'"))[0]['rpmaterial'];
						$bar['subtotal'] -= $rpmaterial; // Subtotal dikurangi SO Material
						$nilaipphfn23[$bar['nopo']] = ($bar['subtotal'] - ($bar['subtotal'] * $bar['diskonpersen']) / 100) * $bar['persenpph'] / 100;
					}
				}

				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnopo) . "') and tipeinvoice!='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}

				$str = "select * from " . $dbname . ".keu_tagihandt where nopo in ('" . implode("','", $arrnopo) . "')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					if ($bar['noakun'] == '1160101') { # PPn Masukan
						@$dtnilaippnx[$bar['nopo']] += $bar['nilai'];
					}

					if ($bar['noakun'] == '1160103') { # PPh 22
						@$dtnilaipphx[$bar['nopo']] += $bar['nilai'];
					}

					if ($bar['noakun'] == '1160104') { # PPh 23
						@$dtnilaipphx23[$bar['nopo']] += $bar['nilai'];
					}
				}

				// echo $param['tipeinvoice'];
				// echo $param['tipeinvoice'];
				#= case po inventory table penerimaan = log_transaksi_vw
				#= case po inventory table penerimaan = log_noninventorydt_vw
				if ($param['tipeinvoice'] == 'p') {
					$str = "select sum(hargasatuan*jumlah) as hartot, tanggal,nopo from " . $dbname . ".log_transaksi_vw
					where nopo in ('" . implode("','", $arrnopo) . "') and post=1  and tipetransaksi='1' group by nopo";
				}
				if ($param['tipeinvoice'] == 'pon') {
					$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo from " . $dbname . ".log_noninventorydt_vw
					where nopo in ('" . implode("','", $arrnopo) . "') and posting=1 group by nopo";
				}
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					# Cek SO Material
					$rpmaterial = fetchData(selectQuery($dbname, "log_somaterial", "SUM(harga*jumlah) as rpmaterial", "nopo='{$bar['nopo']}'"))[0]['rpmaterial'];

					$bar['nopo'] = strtoupper($bar['nopo']);
					$dthartot[$bar['nopo']] = $bar['hartot'] + $rpmaterial;
				}

				# = GET = #
				$sql = selectQuery($dbname, "log_sorefrensi", "sum(hargasatuan*jumlah) as hartot,left(kodebarang,3) as kelompokbarang,nopo", "nopo='" . $param['nodok'] . "'");
				$res = fetchData($sql);
				foreach ($res as $val) {
					if (in_array($val['kelompokbarang'], $klBarangSoAngkut)) {
						$dthartot[$val['nopo']] = $val['hartot'];
					}
				}

				// echo"<pre>";
				// print_r($dtnilaippnx);
				// echo"</pre>";
				#= bentuk data retur
				if ($param['tipeinvoice'] == 'p') {
					$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo from " . $dbname . ".log_transaksi_vw
					where nopo in ('" . implode("','", $arrnopo) . "') and post=1  and tipetransaksi='6' group by nopo";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$dthartotretur[$bar['nopo']] = $bar['hartot'];
					}
				}

				if ($param['tipeinvoice'] == 'pon') {
					$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo from " . $dbname . ".log_retursuppliernoninventorydt_vw
					where nopo in ('" . implode("','", $arrnopo) . "') and posting=1 group by nopo";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$dthartotretur[$bar['nopo']] = $bar['hartot'];
					}
				}


				#= tarik data uang muka
				/*
				$str="select * from ".$dbname.".keu_kasbankdtht_vw
				where nodok in ('".implode("','",$arrnopo)."') and pembayaran=1 and noakun like '11803%'";
				// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtuangmuka[$bar['nodok']]=$bar['jumlah'];
				}*/

				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnopo) . "') and tipeinvoice='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtuangmukadpp[$bar['nopo']] += $bar['nilaidpp'];
					@$dtuangmuka[$bar['nopo']] += $bar['nilaiinvoice'];
				}

				foreach ($arrnopo as $dtnopo) {
					@$no += 1;
					// echo"<pre>";
					// print_r($dthartot[$dtnopo]);
					// echo"</pre>";
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnopo . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnopo . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtkodesupplier[$dtnopo]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @$dtmatauang[$dtnopo] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dthartot[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dthartotretur[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsubtotal[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($nilaippnfn[$dtnopo], 2) . "</td>";

					if ($arrpph22penambah[$dtnopo] == '1') {
						$stream .= "<td style=cursor:pointer align=right>" . @number_format(0, 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right>" . @number_format($nilaipphfn[$dtnopo], 2) . "</td>";
					} else {
						$stream .= "<td style=cursor:pointer align=right>" . @number_format($nilaipphfn[$dtnopo], 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right>" . @number_format(0, 2) . "</td>";
					}

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($nilaipphfn23[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($pbbkb[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaipo[$dtnopo], 2) . "</td>";

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtuangmukadpp[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtuangmuka[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaidppinvoice[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaippnx[$dtnopo], 2) . "</td>";

					if ($arrpph22penambah[$dtnopo] == '1') {
						$stream .= "<td style=cursor:pointer align=right>" . @number_format(0, 2) . "</td>"; # PPh Pengurang sama aja
						$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaipphx[$dtnopo], 2) . "</td>"; # PPh Pengurang sama aja
					} else {
						$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaipphx[$dtnopo], 2) . "</td>"; # PPh Pengurang sama aja
						$stream .= "<td style=cursor:pointer align=right>" . @number_format(0, 2) . "</td>"; # PPh Pengurang sama aja
					}

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaipphx23[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaiinvoice[$dtnopo], 2) . "</td>";

					// @$dtsisadpp[$dtnopo] = ($dtsubtotal[$dtnopo] + $pbbkb[$dtnopo]) - $dtnilaidppinvoice[$dtnopo] - $dtuangmukadpp[$dtnopo];
					@$dtsisadpp[$dtnopo] = ($dtsubtotal[$dtnopo] + $pbbkb[$dtnopo]) - $dtnilaidppinvoice[$dtnopo] - $dtuangmukadpp[$dtnopo];
					@$dtsisa[$dtnopo] = $dtnilaipo[$dtnopo] - $dtnilaiinvoice[$dtnopo] - $dtuangmuka[$dtnopo];
					# SISA PPN & PPH
					@$dtsisappn[$dtnopo] = $nilaippnfn[$dtnopo] - $dtnilaippnx[$dtnopo];
					@$dtsisapph[$dtnopo] = $nilaipphfn[$dtnopo] - $dtnilaipphx[$dtnopo];
					@$dtsisapph23[$dtnopo] = $nilaipphfn23[$dtnopo] - $dtnilaipphx23[$dtnopo];

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisadpp[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisappn[$dtnopo], 2) . "</td>";

					if ($arrpph22penambah[$dtnopo] == '1') {
						$stream .= "<td style=cursor:pointer align=right>" . @number_format(0, 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisapph[$dtnopo], 2) . "</td>";
					} else {
						$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisapph[$dtnopo], 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right>" . @number_format(0, 2) . "</td>";
					}

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisapph23[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisa[$dtnopo], 2) . "</td>";
					$stream .= "</tr>";
				}


				break;

			case 'k':

				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nospk'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['kontraktor'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nilai'] . " SPK</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['pajak'] . "(%)</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nilai'] . " BA</td>";
				$stream .= "<td align=center colspan=3>" . $_SESSION['lang']['nilaiinvoice'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['sisa'] . "<br>(BA-Invoice)</td>";
				$stream .= "</tr>";
				$stream .= "<tr>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " <br/> (Pemakaian Barang Gudang)</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "</tr>";
				$stream .= "</thead>";


				if ($param['nodok'] != '') {
					$where = " and notransaksi like '%" . $param['nodok'] . "%'";
				}


				$str = "select * from " . $dbname . ".log_spkht where kodeorg='" . $param['unit'] . "' " . $where . " ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnospk[$bar['notransaksi']] = $bar['notransaksi'];
					$dtkoderekanan[$bar['notransaksi']] = $bar['koderekanan'];
					$dtnilaikontrak[$bar['notransaksi']] = $bar['nilaikontrak'];
				}

				#= Pemakaian Material
				$sql = selectQuery($dbname, "log_transaksi_vw_detail", "sum(hargarata*jumlah) as hargaratadetail, kodeblok", "post='1' AND kodeblok IN ('" . implode("','", $arrnospk) . "') group by kodeblok");
				$res = fetchData($sql);
				foreach ($res as $bar) {
					$hargadetail[$bar['kodeblok']] = $bar['hargaratadetail'];
				}


				#= BASPK
				$str = "select * from " . $dbname . ".log_baspk where notransaksi in ('" . implode("','", $arrnospk) . "') and statusjurnal=1 ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaibaspk[$bar['notransaksi']] += $bar['jumlahrealisasi'];

					# Hitung ada berapa termin
					$datatermin[$bar['notransaksi']] += count($bar['keterangan']);
				}

				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnospk) . "') and tipeinvoice!='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}

				#= tax
				$str = "select * from " . $dbname . ".log_spk_tax where notransaksi in ('" . implode("','", $arrnospk) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtpersenpph[$bar['notransaksi']] += $bar['nilai'];
				}


				foreach ($arrnospk as $dtnospk) {

					#========================# 
					# Nilai SPK / BAPP dikurangkan dengan Pemakaian Barang

					// $dtnilaikontrak[$dtnospk] 	= $dtnilaikontrak[$dtnospk]-$hargadetail[$dtnospk];
					$dtnilaibaspk[$dtnospk] 		= $dtnilaibaspk[$dtnospk] - $hargadetail[$dtnospk];
					$dtnilaidppinvoice[$dtnospk] 	= ($dtnilaidppinvoice[$dtnospk] > 0 ? $dtnilaidppinvoice[$dtnospk] - $hargadetail[$dtnospk] : $dtnilaidppinvoice[$dtnospk]);
					$dtnilaiinvoice[$dtnospk] 		= ($dtnilaiinvoice[$dtnospk] > 0 ? $dtnilaiinvoice[$dtnospk] - $hargadetail[$dtnospk] : $dtnilaiinvoice[$dtnospk]);

					# END
					#========================#

					@$no += 1;
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnospk . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnospk . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtkoderekanan[$dtnospk]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaikontrak[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenpph[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaibaspk[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaidppinvoice[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($hargadetail[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaiinvoice[$dtnospk], 2) . "</td>";

					$dtsisadpp[$dtnospk] = $dtnilaibaspk[$dtnospk] - $dtnilaidppinvoice[$dtnospk];
					#= algotitmanya sisa dari dpp dikurangi dpp*persen pajak (karna pph)
					$dtsisa[$dtnospk] = $dtsisadpp[$dtnospk] - ($dtsisadpp[$dtnospk] * $dtpersenpph[$dtnospk] / 100);

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisadpp[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisa[$dtnospk], 2) . "</td>";

					$stream .= "</tr>";
				}
				break;


			case 'bas':

				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nospk'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['kontraktor'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nilai'] . " SPK</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['pajak'] . "<br>" . $_SESSION['lang']['ppn'] . "(%)</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['pajak'] . "<br>" . $_SESSION['lang']['pph'] . "(%)</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilai'] . " BA</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilaiinvoice'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['sisa'] . "<br>(BA-Invoice)</td>";
				$stream .= "</tr>";
				$stream .= "<tr>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "</tr>";
				$stream .= "</thead>";


				if ($param['nodok'] != '') {
					$where = " and notransaksi like '%" . $param['nodok'] . "%'";
				}


				$str = "select * from " . $dbname . ".log_kontrakjasa where unit='" . $param['unit'] . "' " . $where . " ";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnospk[$bar['notransaksi']] = $bar['notransaksi'];
					$dtkoderekanan[$bar['notransaksi']] = $bar['supplierid'];
					$dtnilaikontrak[$bar['notransaksi']] = $bar['nilaikontrak'];
				}

				#= tax
				$str = "select * from " . $dbname . ".log_spk_tax where notransaksi in ('" . implode("','", $arrnospk) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					if (substr($bar['noakun'], 0, 3) == '117') {
						@$dtpersenppn[$bar['notransaksi']] += $bar['nilai'];
					}
					if (substr($bar['noakun'], 0, 3) == '213') {
						@$dtpersenpph[$bar['notransaksi']] += $bar['nilai'];
					}
				}


				#= BASPK
				$str = "select * from " . $dbname . ".log_bakontrakjasa where nokontrak in ('" . implode("','", $arrnospk) . "') and status=1";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppbaspk[$bar['nokontrak']] += $bar['jumlah'];
					@$dtnilaibaspk[$bar['nokontrak']] += $bar['jumlah'];
					if ($bar['noakun'] == 'material') {
						@$dtnilaidppbaspkmaterial[$bar['nokontrak']] = $bar['jumlah'];
						$dtnilaibaspk[$bar['nokontrak']] += ($dtpersenppn[$bar['nokontrak']] / 100 * $dtnilaidppbaspkmaterial[$bar['nokontrak']]);
					}
					if ($bar['noakun'] == 'jasa') {
						@$dtnilaidppbaspkjasa[$bar['nokontrak']] = $bar['jumlah'];
						$dtnilaibaspk[$bar['nokontrak']] += ($dtpersenppn[$bar['nokontrak']] / 100 * $dtnilaidppbaspkjasa[$bar['nokontrak']]);
						// $dtnilaibaspk[$bar['nokontrak']]-=($dtpersenpph[$bar['nokontrak']]/100*$dtnilaidppbaspkjasa[$bar['nokontrak']]);
					}
				}
				// echo "<pre>";
				// print_r($dtnilaidppbaspkjasa);
				// brg ppn saja
				// jasa ppn + pph

				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnospk) . "') ";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}




				foreach ($arrnospk as $dtnospk) {
					@$no += 1;
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnospk . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnospk . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtkoderekanan[$dtnospk]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaikontrak[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenppn[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenpph[$dtnospk], 2) . "</td>";

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaidppbaspk[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaibaspk[$dtnospk], 2) . "</td>";


					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaidppinvoice[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaiinvoice[$dtnospk], 2) . "</td>";
					$dtsisadpp[$dtnospk] = $dtnilaidppbaspk[$dtnospk] - $dtnilaidppinvoice[$dtnospk];
					$dtsisa[$dtnospk] = $dtnilaibaspk[$dtnospk] - $dtnilaiinvoice[$dtnospk];
					#= algotitmanya sisa dari dpp dikurangi dpp*persen pajak (karna pph)
					// $dtsisa[$dtnospk]=$dtsisadpp[$dtnospk]-($dtsisadpp[$dtnospk]*$dtpersenpph[$dtnospk]/100)+($dtsisadpp[$dtnospk]*$dtpersenppn[$dtnospk]/100);
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisadpp[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisa[$dtnospk], 2) . "</td>";

					$stream .= "</tr>";
				}
				break;


			case 'ffb':
				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tipe'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['supplier'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['pajak'] . " (%)</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilai'] . " " . $_SESSION['lang']['transaksi'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilaiinvoice'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['sisa'] . "</td>";
				$stream .= "</tr>";
				$stream .= "<tr>";
				$stream .= "<td align=center>" . $_SESSION['lang']['ppn'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['pph'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "</tr>";
				$stream .= "</thead>";

				if ($param['nodok'] != '') {
					$where = " and notransaksi like '%" . $param['nodok'] . "%'";
				}
				if ($param['supplier'] != '') {
					$where .= " and supplier like '" . $param['supplier'] . "'";
				}

				$str = "SELECT SUM(jumrpadjust) AS jumrpadjust, persenppn, persenpph, notransaksi, tanggal, tipetbs, supplier 
						FROM " . $dbname . ".pmn_tbs 
						WHERE 
							(CASE 
								WHEN unithutang IS NOT NULL AND unithutang != '' THEN unithutang = '" . $param['unit'] . "' 
								ELSE unit = '" . $param['unit'] . "' 
							END)
						" . $where . " 
						AND posting='1' 
						GROUP BY notransaksi";

				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					$dttipetbs[$bar['notransaksi']] = $bar['tipetbs'];
					$dtsupplier[$bar['notransaksi']] = $bar['supplier'];
					$dtjumrpadjust[$bar['notransaksi']] = $bar['jumrpadjust'];
					$dtpersenppn[$bar['notransaksi']] = $bar['persenppn'];
					$dtpersenpph[$bar['notransaksi']] = $bar['persenpph'];
				}

				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnotransaksi) . "') and tipeinvoice!='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}

				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnotransaksi) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}


				foreach ($arrnotransaksi as $dtnotransaksi) {

					#= buat rumus nilai ppn dan pph
					// $dtnilaippn[$dtnotransaksi]=floor($dtpersenppn[$dtnotransaksi]/100*$dtjumrpadjust[$dtnotransaksi]);
					// $dtnilaipph[$dtnotransaksi]=floor($dtpersenpph[$dtnotransaksi]/100*$dtjumrpadjust[$dtnotransaksi]*-1);
					// $dtnilaitotal[$dtnotransaksi]=$dtjumrpadjust[$dtnotransaksi]+$dtnilaippn[$dtnotransaksi]+$dtnilaipph[$dtnotransaksi];

					$dtnilaippn[$dtnotransaksi] = floor($dtpersenppn[$dtnotransaksi] / 100 * $dtjumrpadjust[$dtnotransaksi]);
					$dtnilaipph[$dtnotransaksi] = floor($dtpersenpph[$dtnotransaksi] / 100 * $dtjumrpadjust[$dtnotransaksi] * -1);
					$dtnilaitotal[$dtnotransaksi] = $dtjumrpadjust[$dtnotransaksi] + $dtnilaippn[$dtnotransaksi] + $dtnilaipph[$dtnotransaksi];


					@$no += 1;
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnotransaksi . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer>" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dttipetbs[$dtnotransaksi] . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtsupplier[$dtnotransaksi]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenppn[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenpph[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtjumrpadjust[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaitotal[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaidppinvoice[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaiinvoice[$dtnotransaksi], 2) . "</td>";

					$dtsisadpp[$dtnotransaksi] = $dtjumrpadjust[$dtnotransaksi] - $dtnilaidppinvoice[$dtnotransaksi];
					#= algotitmanya sisa dari dpp dikurangi dpp*persen pajak (karna pph)
					$dtsisa[$dtnotransaksi] = $dtnilaitotal[$dtnotransaksi] - $dtnilaiinvoice[$dtnotransaksi];

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisadpp[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisa[$dtnotransaksi], 2) . "</td>";

					$stream .= "</tr>";
				}

				break;

			case 'ffba':
			case 'ffbe':
				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tipe'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['supplier'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['pajak'] . " (%)</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilai'] . " " . $_SESSION['lang']['transaksi'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilaiinvoice'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['sisa'] . "</td>";
				$stream .= "</tr>";
				$stream .= "<tr>";
				$stream .= "<td align=center>" . $_SESSION['lang']['ppn'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['pph'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "</tr>";
				$stream .= "</thead>";

				if ($param['nodok'] != '') {
					$where = " and notransaksi like '%" . $param['nodok'] . "%'";
				}

				// if ($param['tipeinvoice'] == 'ffb') {
				// 	$str = "select sum(totalrp) as totalrp,notransaksi,pemilik,supplier,unit,tanggal from " . $dbname . ".kebun_tbskud where (unit='" . $param['unit'] . "' or pemilik='" . $param['unit'] . "') and posting=1 " . $where . " group by notransaksi";
				// 	$res = fetchdata($str);
				// 	foreach ($res as $bar) {
				// 		#= trapnya karna unit adalah pabrik, kasus kud kbp buang tbs ke pabrik kspm
				// 		if ($param['kodeorg'] == $arrkodept[$bar['pemilik']]) {
				// 			$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
				// 			$dttipetbs[$bar['notransaksi']] = 'KUD';
				// 			$dtsupplier[$bar['notransaksi']] = $bar['supplier'];
				// 			// $dtjumrpadjust[$bar['notransaksi']]=$bar['totalrp'];
				// 			$dtjumrpadjust[$bar['notransaksi']] = floor($bar['totalrp']);
				// 			// $dtpersenppn[$bar['notransaksi']]=10;
				// 			// $dtpersenpph[$bar['notransaksi']]=0;
				// 			$dtpemilik[$bar['notransaksi']] = $bar['pemilik'];
				// 			$dtunit[$bar['notransaksi']] = $bar['unit'];
				// 			$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
				// 		}
				// 	}
				// }

				if ($param['tipeinvoice'] == 'ffba') {
					$str = "select  sum(totalrp) as totalrp,notransaksi,pemilik,supplier,unit,tanggal from " . $dbname . ".kebun_tbsafiliasi where rounit='" . $param['unit'] . "' and posting=1 " . $where . " group by notransaksi";
					// echo $str;
					// $str="select  sum(totalrp) as totalrp,notransaksi,pemilik,supplier,unit,tanggal  from ".$dbname.".kebun_tbsafiliasi where unit='".$param['unit']."' and posting=1 ".$where." group by notransaksi";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
						$dttipetbs[$bar['notransaksi']] = 'AFI';
						$dtsupplier[$bar['notransaksi']] = $bar['supplier'];
						$dtjumrpadjust[$bar['notransaksi']] = floor($bar['totalrp']);
						// $dtpersenppn[$bar['notransaksi']]=10;
						// $dtpersenpph[$bar['notransaksi']]=0;
						$dtpemilik[$bar['notransaksi']] = $bar['pemilik'];
						$dtunit[$bar['notransaksi']] = $bar['rounit'];
						$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					}
				}

				if ($param['tipeinvoice'] == 'ffbe') {
					// $str="select  sum(totalrp) as totalrp,notransaksi,pemilik,supplier,unit,tanggal  from ".$dbname.".kebun_tbsexternal where unit='".$param['unit']."' and posting=1 ".$where." group by notransaksi";
					$str = "select  sum(totalrp) as totalrp,notransaksi,pemilik,supplier,unit,tanggal  from " . $dbname . ".kebun_tbsexternal where kodeorginv='" . $param['unit'] . "' and posting=1 " . $where . " group by notransaksi";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
						$dttipetbs[$bar['notransaksi']] = 'EXT';
						$dtsupplier[$bar['notransaksi']] = $bar['supplier'];
						$dtjumrpadjust[$bar['notransaksi']] = floor($bar['totalrp']);
						// $dtpersenppn[$bar['notransaksi']]=10;
						// $dtpersenpph[$bar['notransaksi']]=0;
						$dtpemilik[$bar['notransaksi']] = $bar['pemilik'];
						$dtunit[$bar['notransaksi']] = $bar['unit'];
						$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					}
				}


				#=
				$str = "select *  from " . $dbname . ".log_5pphsup  where supplierid in ('" . implode("','", $dtsupplier) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtpersenppn[$bar['supplierid']] = $bar['tarif'];
					// $dtpersenpph[$bar['supplierid']]=$bar['tarif'];
				}


				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnotransaksi) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}


				foreach ($arrnotransaksi as $dtnotransaksi) {

					#= buat rumus nilai ppn dan pph
					// $dtnilaippn[$dtnotransaksi]=floor($dtpersenppn[$dtnotransaksi]/100*$dtjumrpadjust[$dtnotransaksi]);
					// $dtnilaipph[$dtnotransaksi]=floor($dtpersenpph[$dtnotransaksi]/100*$dtjumrpadjust[$dtnotransaksi]*-1);
					// $dtnilaitotal[$dtnotransaksi]=$dtjumrpadjust[$dtnotransaksi]+$dtnilaippn[$dtnotransaksi]+$dtnilaipph[$dtnotransaksi];

					$dtnilaippn[$dtnotransaksi] = floor($dtpersenppn[$dtsupplier[$dtnotransaksi]] / 100 * $dtjumrpadjust[$dtnotransaksi]);
					$dtnilaipph[$dtnotransaksi] = floor($dtpersenpph[$dtsupplier[$dtnotransaksi]] / 100 * $dtjumrpadjust[$dtnotransaksi] * -1);
					$dtnilaitotal[$dtnotransaksi] = $dtjumrpadjust[$dtnotransaksi] + $dtnilaippn[$dtnotransaksi] + $dtnilaipph[$dtnotransaksi];


					@$no += 1;
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnotransaksi . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer>" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dttipetbs[$dtnotransaksi] . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtsupplier[$dtnotransaksi]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenppn[$dtsupplier[$dtnotransaksi]], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenpph[$dtsupplier[$dtnotransaksi]], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtjumrpadjust[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaitotal[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaidppinvoice[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaiinvoice[$dtnotransaksi], 2) . "</td>";

					$dtsisadpp[$dtnotransaksi] = $dtjumrpadjust[$dtnotransaksi] - $dtnilaidppinvoice[$dtnotransaksi];
					#= algoritmanya sisa dari dpp dikurangi dpp*persen pajak (karna pph)
					$dtsisa[$dtnotransaksi] = $dtnilaitotal[$dtnotransaksi] - $dtnilaiinvoice[$dtnotransaksi];

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisadpp[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisa[$dtnotransaksi], 2) . "</td>";

					$stream .= "</tr>";
				}
				break;


			case 'ffbfee':
				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['supplier'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['pajak'] . " (%)</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilai'] . " " . $_SESSION['lang']['transaksi'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilaiinvoice'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['sisa'] . "</td>";
				$stream .= "</tr>";
				$stream .= "<tr>";
				$stream .= "<td align=center>" . $_SESSION['lang']['ppn'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['pph'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "</tr>";
				$stream .= "</thead>";

				if ($param['nodok'] != '') {
					$where = " and notransaksi like '%" . $param['nodok'] . "%'";
				}


				$str = "select sum(totalrp) as totalrp,sum(rpppn) as rpppn,persenppn,notransaksi,kodesupplier,unit,tanggal from " . $dbname . ".pmn_feetbs where unitalokasi='" . $param['unit'] . "' and posting=1 " . $where . " group by notransaksi";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
					$dtsupplier[$bar['notransaksi']] = $bar['kodesupplier'];
					$dttotalrp[$bar['notransaksi']] = floor($bar['totalrp']);
					$dtnilaitotal[$bar['notransaksi']] = floor($bar['totalrp'] + $bar['rpppn']);
					$dtunit[$bar['notransaksi']] = $bar['unit'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					$dtnilaippn[$bar['notransaksi']] = $bar['rpppn'];
					$dtpersenppn[$bar['notransaksi']] = $bar['persenppn'];
				}



				// $str="select *  from ".$dbname.".log_5pphsup  where supplierid in ('".implode("','",$dtsupplier)."') ";
				// $res=fetchdata($str);
				// foreach($res as $bar){
				// // $dtpersenppn[$bar['supplierid']]=$bar['tarif'];
				// $dtpersenppn[$bar['supplierid']]=0;
				// // $dtpersenpph[$bar['supplierid']]=$bar['tarif'];
				// }


				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnotransaksi) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}


				foreach ($arrnotransaksi as $dtnotransaksi) {

					// $dtnilaippn[$dtnotransaksi]=floor($dtpersenppn[$dtsupplier[$dtnotransaksi]]/100*$dttotalrp[$dtnotransaksi]);
					// $dtnilaipph[$dtnotransaksi]=floor($dtpersenpph[$dtsupplier[$dtnotransaksi]]/100*$dttotalrp[$dtnotransaksi]*-1);
					// $dtnilaitotal[$dtnotransaksi]=$dttotalrp[$dtnotransaksi]+$dtnilaippn[$dtnotransaksi]+$dtnilaipph[$dtnotransaksi];


					@$no += 1;
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnotransaksi . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer>" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtsupplier[$dtnotransaksi]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenppn[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtpersenpph[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dttotalrp[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaitotal[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaidppinvoice[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtnilaiinvoice[$dtnotransaksi], 2) . "</td>";

					$dtsisadpp[$dtnotransaksi] = $dttotalrp[$dtnotransaksi] - $dtnilaidppinvoice[$dtnotransaksi];
					#= algotitmanya sisa dari dpp dikurangi dpp*persen pajak (karna pph)
					$dtsisa[$dtnotransaksi] = $dtnilaitotal[$dtnotransaksi] - $dtnilaiinvoice[$dtnotransaksi];

					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisadpp[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . @number_format($dtsisa[$dtnotransaksi], 2) . "</td>";

					$stream .= "</tr>";
				}
				break;



			case 'sip':

				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nospk'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['tipe'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['supplier'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['customer'] . "</td>";
				$stream .= "<td align=center>DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilaiinvoice'] . "</td>";
				$stream .= "</tr>";

				$stream .= "</thead>";


				if ($param['nodok'] != '') {
					$where = " and nodo like '%" . $param['nodok'] . "%'";
				}


				$str = "select * from " . $dbname . ".pmn_suratperintahpengiriman where pt='" . $param['kodeorg'] . "' " . $where . " ";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnospk[$bar['nodo']] = $bar['nodo'];
					$dttanggal[$bar['nodo']] = $bar['tanggaldo'];


					// $dtsupplier[$bar['nospk']]=$bar['surveyor'];


					$dtsupplier[$bar['nodo']] = $bar['transportir']; //transportir
					$dttipesupplier[$bar['nodo']] = 'TRANSPORTIR'; //transportir

					$dtjenis[$bar['nodo']] = $bar['jenis'];
					$dtnokontrak[$bar['nodo']] = $bar['nokontrak'];

					$arrsupplier[$bar['surveyor']] = $bar['surveyor'];
				}

				$str = "select koderekanan,nokontrak from " . $dbname . ".pmn_kontrakjual where  nokontrak in ('" . implode("','", $dtnokontrak) . "')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtkodecustomer[$bar['nokontrak']] = $bar['koderekanan'];
				}


				#= invoice
				$str = "select * from " . $dbname . ".pmn_4customer";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$namacustomer[$bar['kodecustomer']] = $bar['namacustomer'];
				}



				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnospk) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}

				#= data rekening supplier
				#= rekening dan jenis supplier
				$str = "select * from " . $dbname . ".log_5rekbank where supplierid in ('" . implode("','", $arrsupplier) . "') and isactive=1";
				// echo $str;	
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtrekening[$bar['supplierid']] = $bar['rekening'];
				}
				#= indraspks
				$matauang = 'IDR';
				$kurs = '1';
				$tipesupplier = 'JASA';


				foreach ($arrnospk as $dtnospk) {

					@$no += 1;
					$stream .= "<tr class=rowcontent 
							onclick=\"setdatanodok('" . $dtnospk . "','" . $dtsupplier[$dtnospk] . "','0','0','" . $matauang . "','" . $kurs . "','" . $dtrekening[$dtsupplier[$dtnospk]] . "','" . $dttipesupplier[$dtnospk] . "','Pembayaran SIP  a/n " . $nmsupplier[$dtsupplier[$dtnospk]] . ", No : " . $dtnospk . "')\">";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnospk . "</td>";
					$stream .= "<td style=cursor:pointer>SIP</td>";
					$stream .= "<td style=cursor:pointer>" . tanggalnormal($dttanggal[$dtnospk]) . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtsupplier[$dtnospk]] . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnokontrak[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer>" . $namacustomer[$dtkodecustomer[$dtnokontrak[$dtnospk]]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . $dtnilaidppinvoice[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaiinvoice[$dtnospk], 2) . "</td>";
					$stream .= "</tr>";
				}
				break;



			case 'spks':

				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nospk'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['tipe'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['supplier'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['customer'] . "</td>";
				$stream .= "<td align=center>DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilaiinvoice'] . "</td>";
				$stream .= "</tr>";

				$stream .= "</thead>";

				if ($param['jenis'] == '') {
					exit("Warning:Jenis tidak boleh kosong");
				}

				if ($param['nodok'] != '') {
					$where = " and nospk like '%" . $param['nodok'] . "%'";
				}

				$tablespks = "pmn_spk_" . $param['jenis'];


				$str = "select * from " . $dbname . "." . $tablespks . " where kodept='" . $param['kodeorg'] . "' " . $where . " ";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnospk[$bar['nospk']] = $bar['nospk'];
					$dttanggal[$bar['nospk']] = $bar['tanggal'];


					// $dtsupplier[$bar['nospk']]=$bar['surveyor'];

					if ($bar['jenis'] == 'ETC') {
						$dtsupplier[$bar['nospk']] = $bar['transportirdarat']; //transportir
						$dttipesupplier[$bar['nospk']] = 'TRANSPORTIR'; //transportir
					} else if ($bar['jenis'] == 'IPK') {
						$dtsupplier[$bar['nospk']] = $bar['transportir']; //transportir
						$dttipesupplier[$bar['nospk']] = 'TRANSPORTIR'; //transportir
					} else if ($bar['jenis'] == 'IPKD') {
						$dtsupplier[$bar['nospk']] = $bar['transportirdarat']; //transportir
						$dttipesupplier[$bar['nospk']] = 'TRANSPORTIR'; //transportir
					} else if ($bar['jenis'] == 'TKBM') {
						$dtsupplier[$bar['nospk']] = $bar['bongkarmuat'];  //jasa
						$dttipesupplier[$bar['nospk']] = 'JASABONGKARMUAT'; //transportir
					} else if ($bar['jenis'] == 'SUB' || $bar['jenis'] == 'SUM' || $bar['jenis'] == 'SDA' || $bar['jenis'] == 'SP') {
						$dtsupplier[$bar['nospk']] = $bar['surveyor']; //surveyor
						$dttipesupplier[$bar['nospk']] = 'JASAANALISA';
					} else if ($bar['jenis'] == 'SPP') {
						$dtsupplier[$bar['nospk']] = $bar['transportir']; //transportir
						$dttipesupplier[$bar['nospk']] = 'TRANSPORTIR'; //transportir
					}

					$dtjenis[$bar['nospk']] = $bar['jenis'];
					$dtnokontrak[$bar['nospk']] = $bar['nokontrak'];
					$dtkodecustomer[$bar['nospk']] = $bar['kodecustomer'];
					$arrsupplier[$bar['surveyor']] = $bar['surveyor'];
				}

				$str = "select * from " . $dbname . ".pmn_4customer";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$namacustomer[$bar['kodecustomer']] = $bar['namacustomer'];
				}


				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnospk) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}

				#= data rekening supplier
				#= rekening dan jenis supplier
				$str = "select * from " . $dbname . ".log_5rekbank where supplierid in ('" . implode("','", $arrsupplier) . "') and isactive=1";
				// echo $str;	
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtrekening[$bar['supplierid']] = $bar['rekening'];
				}

				$matauang = 'IDR';
				$kurs = '1';
				$tipesupplier = 'JASA';


				foreach ($arrnospk as $dtnospk) {

					@$no += 1;
					$stream .= "<tr class=rowcontent 
							onclick=\"setdatanodok('" . $dtnospk . "','" . $dtsupplier[$dtnospk] . "','0','0','" . $matauang . "','" . $kurs . "','" . $dtrekening[$dtsupplier[$dtnospk]] . "','" . $dttipesupplier[$dtnospk] . "','Pembayaran SPK  a/n " . $nmsupplier[$dtsupplier[$dtnospk]] . ", No : " . $dtnospk . "')\">";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnospk . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtjenis[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer>" . tanggalnormal($dttanggal[$dtnospk]) . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtsupplier[$dtnospk]] . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnokontrak[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer>" . $namacustomer[$dtkodecustomer[$dtnospk]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . $dtnilaidppinvoice[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaiinvoice[$dtnospk], 2) . "</td>";
					$stream .= "</tr>";
				}
				break;

			case 'um':

				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>";
				// $stream.="<td align=center rowspan=2>".$_SESSION['lang']['gudang']."</td>";
				// $stream.="<td align=center rowspan=2>".$_SESSION['lang']['retur']."</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['nilai'] . " PO</td>";
				// $stream.="<td align=center colspan=2>".$_SESSION['lang']['nilaiinvoice']."</td>";
				// $stream.="<td align=center colspan=2>".$_SESSION['lang']['sisa']."<br>(PO-Invoice)</td>";
				$stream .= "</tr>";
				$stream .= "<tr>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilai'] . " DPP</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				// $stream.="<td align=center>".$_SESSION['lang']['nilai']." DPP</td>";
				// $stream.="<td align=center>".$_SESSION['lang']['total']."</td>";	
				// $stream.="<td align=center>".$_SESSION['lang']['nilai']." DPP</td>";
				// $stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
				$stream .= "</tr>";
				$stream .= "</thead>";

				if ($param['nodok'] != '') {
					$where = " and nopo like '%" . $param['nodok'] . "%'";
				}

				# Id Franco
				$franco = makeOption($dbname, "setup_franco", "kodeunit,id_franco");

				$str = "select * from " . $dbname . ".log_poht where stat_release=1 and kodeorg='" . $param['kodeorg'] . "' and idFrancoinvc='" . $franco[$param['unit']] . "' " . $where . " ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnopo[$bar['nopo']] = $bar['nopo'];
					$arrsupplier[$bar['kodesupplier']] = $bar['kodesupplier'];
					$dtkodesupplier[$bar['nopo']] = $bar['kodesupplier'];
					$dtsubtotal[$bar['nopo']] = $bar['subtotal'] - $bar['nilaidiskon'];
					$dtnilaipo[$bar['nopo']] = $bar['nilaipo'];
					$dtmatauang[$bar['nopo']] = $bar['matauang'];
					$dtkurs[$bar['nopo']] = $bar['kurs'];
					$dtrekening[$bar['nopo']] = $bar['rekening'];
				}


				$akunhutangjasa = '2110301';
				$akunhutanginventory = '2110101';
				// tipepo
				if ($tipepo == 'SO') {
					$str = "select * from " . $dbname . ".log_5supkelompok where supplierid in ('" . implode("','", $arrsupplier) . "') and noakun='" . $akunhutangjasa . "'";
				} else {
					$str = "select * from " . $dbname . ".log_5supkelompok where supplierid in ('" . implode("','", $arrsupplier) . "') and noakun='" . $akunhutanginventory . "'";
				}
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dttipesupplier[$bar['supplierid']] = $bar['tipe'];
				}



				foreach ($arrnopo as $dtnopo) {

					@$no += 1;
					$stream .= "<tr class=rowcontent 
						onclick=\"setdatanodok('" . $dtnopo . "','" . $dtkodesupplier[$dtnopo] . "','0','0','" . $dtmatauang[$dtnopo] . "','" . $dtkurs[$dtnopo] . "','" . $dtrekening[$dtnopo] . "','" . $dttipesupplier[$dtkodesupplier[$dtnopo]] . "','Pembayaran Uang Muka a/n " . $nmsupplier[$dtkodesupplier[$dtnopo]] . ", No : " . $dtnopo . "')\">";

					// @$no+=1;
					// $stream.="<tr class=rowcontent onclick=findnodokdetail('".$dtnopo."','".$param['tipeinvoice']."')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer>" . $dtnopo . "</td>";
					$stream .= "<td style=cursor:pointer>" . $nmsupplier[$dtkodesupplier[$dtnopo]] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . $dtmatauang[$dtnopo] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtsubtotal[$dtnopo], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaipo[$dtnopo], 2) . "</td>";
					$stream .= "</tr>";
				}

				break;

			case 'batr':


				$stream .= "Data unit harus memakai unit RO<br><br>";
				$stream .= "<thead>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td rowspan=2>No.</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nospk'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tipe'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['supplier'] . "</td>";
				$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['NoKontrak'] . "</td>";
				$stream .= "<td align=center colspan=4>" . $_SESSION['lang']['transaksi'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['invoice'] . "</td>";
				$stream .= "<td align=center colspan=2>" . $_SESSION['lang']['sisa'] . "</td>";

				$stream .= "</tr>";
				$stream .= "<tr class='rowheader'>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilaitransaksi'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['klaim'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilaidpp'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilaidpp'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['nilaidpp'] . "</td>";
				$stream .= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
				$stream .= "</tr>";

				$stream .= "</thead>";

				if ($param['nodok'] != '') {
					$where = " and nospk like '%" . $param['nodok'] . "%'";
				}

				$str = "select sum(kgkirim) as kgkirim,sum(kgterima) as kgterima,sum(kgselisih) as kgselisih,sum(rpjumlah) as rpjumlah,sum(kgclaim) as kgclaim,sum(rpclaim) as rpclaim,createby,updateby,notransaksi,tanggal,unit,keterangan,tipe,posting,nokontrak,nospk,transportir from " . $dbname . ".pmn_batransport where 1=1 and rounit='" . $param['unit'] . "' " . $where . " group by nospk order by tanggal desc";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnospk[$bar['nospk']] = $bar['nospk'];
					$dttanggal[$bar['nospk']] = $bar['tanggal'];
					$dtsupplier[$bar['nospk']] = $bar['transportir']; //transportir
					$dttipesupplier[$bar['nospk']] = 'TRANSPORTIR'; //transportir

					$dtnilaitransaksi[$bar['nospk']] = $bar['rpjumlah'];
					$dtnilaiclaim[$bar['nospk']] = $bar['rpclaim'];
					$dtnilaidpp[$bar['nospk']] = $bar['rpjumlah'] + $bar['rpclaim'];
					$dtnilaippn[$bar['nospk']] = (0.1 * ($bar['rpjumlah'] + $bar['rpclaim']));

					$dttipe[$bar['nospk']] = $bar['tipe'];
					$dtnokontrak[$bar['nospk']] = $bar['nokontrak'];
					$arrsupplier[$bar['transportir']] = $bar['transportir'];
				}

				#= invoice
				$str = "select * from " . $dbname . ".keu_tagihanht where nopo in ('" . implode("','", $arrnospk) . "') ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					@$dtnilaidppinvoice[$bar['nopo']] += $bar['nilaidpp'];
					@$dtnilaiinvoice[$bar['nopo']] += $bar['nilaiinvoice'];
				}

				#= data rekening supplier
				#= rekening dan jenis supplier
				$str = "select * from " . $dbname . ".log_5rekbank where supplierid in ('" . implode("','", $arrsupplier) . "') and isactive=1";
				// echo $str;	
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtrekening[$bar['supplierid']] = $bar['rekening'];
				}

				foreach ($arrnospk as $dtnospk) {
					@$no += 1;
					$stream .= "<tr class=rowcontent onclick=findnodokdetail('" . $dtnospk . "','" . $param['tipeinvoice'] . "')>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer align=center>" . $dtnospk . "</td>";
					$stream .= "<td style=cursor:pointer align=center>" . $dttipe[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer align=center>" . $dttanggal[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer align=center>" . $dtsupplier[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer align=center>" . $dtnokontrak[$dtnospk] . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaitransaksi[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaiclaim[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaidpp[$dtnospk], 2) . "</td>";
					// $stream.="<td style=cursor:pointer align=right>".$dtnilaippn[$dtnospk]."</td>";
					$dtnilaitotal[$dtnospk] = $dtnilaidpp[$dtnospk] + $dtnilaippn[$dtnospk];
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaitotal[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaidppinvoice[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaiinvoice[$dtnospk], 2) . "</td>";
					$dtnilaidppsisa[$dtnospk] = $dtnilaidpp[$dtnospk] - $dtnilaidppinvoice[$dtnospk];
					$dtnilaitotalsisa[$dtnospk] = $dtnilaitotal[$dtnospk] - $dtnilaiinvoice[$dtnospk];
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaidppsisa[$dtnospk], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right>" . number_format($dtnilaitotalsisa[$dtnospk], 2) . "</td>";

					$stream .= "</tr>";
				}

				break;


				/*
				case'sales':
					$stream.="<thead>";
					$stream.="<tr class='rowheader'>";
						$stream.="<td rowspan=2>No.</td>";
						$stream.="<td align=center colspan=6>".$_SESSION['lang']['kontrak']."</td>";
						$stream.="<td align=center colspan=2>".$_SESSION['lang']['bast']."</td>";
					$stream.="</tr>"; 
					$stream.="<tr>"; 	
						$stream.="<td align=center>".$_SESSION['lang']['NoKontrak']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['customer']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['barang']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</td>";
						$stream.="<td align=center>".$_SESSION['lang']['hargasatuan']."<br>(Rp)</td>";
						$stream.="<td align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</td>";
						$stream.="<td align=center>".$_SESSION['lang']['total']."<br>(RP)</td>";
					$stream.="</tr>"; 
					$stream.="</thead>";  
					
					if($param['nodok']!=''){
						$where=" and nokontrak like '%".$param['nodok']."%'";
					}
					
					
					$str="select * from ".$dbname.".pmn_kontrakjual where kodept='".$param['kodeorg']."' and tipejualbeli='BELI' ".$where." ";
					$res=fetchdata($str);
					foreach($res as $bar){
						$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
						$dtkoderekanan[$bar['nokontrak']]=$bar['koderekanan'];
						$dtkodebarang[$bar['nokontrak']]=$bar['kodebarang'];
						$dtsatuan[$bar['nokontrak']]=$bar['satuan'];
						$dthargasatuan[$bar['nokontrak']]=$bar['hargasatuan'];
						$dtkuantitaskontrak[$bar['nokontrak']]=$bar['kuantitaskontrak'];
						$dtnilaikontrak[$bar['nokontrak']]=$bar['nilaikontrak'];
					}
					
					#= data bast
					$str="select * from ".$dbname.".pmn_bastdt_vw where nokontrak in ('".implode("','",$arrnokontrak)."') and sales=1";
					// echo $str;
					$res=fetchdata($str);
					foreach($res as $bar){
						$dtnotransaksi[$bar['nokontrak']]=$bar['nokontrak'];
						$dtkuantitas[$bar['nokontrak']]+=$bar['kuantitas'];
					}
						
						
					foreach($arrnokontrak as $dtnokontrak){
						@$no+=1;
						$stream.="<tr class=rowcontent onclick=findnodokdetail('".$dtnokontrak."','".$param['tipeinvoice']."')>";
							$stream.="<td style=cursor:pointer align=center>".$no."</td>";
							$stream.="<td style=cursor:pointer>".$dtnokontrak."</td>";
							$stream.="<td style=cursor:pointer>".$dtkoderekanan[$dtnokontrak]."</td>";
							$stream.="<td style=cursor:pointer>".$nmbarangpabrik[$dtkodebarang[$dtnokontrak]]."</td>";
							$stream.="<td style=cursor:pointer>".$dtsatuan[$dtnokontrak]."</td>";
							$stream.="<td style=cursor:pointer>".@number_format($dtkuantitaskontrak[$dtnokontrak])."</td>";
							$stream.="<td style=cursor:pointer>".@number_format($dthargasatuan[$dtnokontrak])."</td>";
							$stream.="<td style=cursor:pointer align=right>".@number_format($dtkuantitas[$dtnokontrak])."</td>";
							
							#= nilai bast dari kg * qty
							$dtnilaibast[$dtnokontrak]=$dtkuantitas[$dtnokontrak]*$dthargasatuan[$dtnokontrak];
							
							$stream.="<td style=cursor:pointer align=right>".@number_format($dtnilaibast[$dtnokontrak])."</td>";
						$stream.="</tr>";
					}
						
				break;
				*/

				/*
				case'trsales':
					$stream.="<thead>";
					$stream.="<tr class='rowheader'>";
						$stream.="<td rowspan=2>No.</td>";
						$stream.="<td align=center colspan=7>DO</td>";
						$stream.="<td align=center colspan=5>".$_SESSION['lang']['bast']."</td>";
					$stream.="</tr>"; 
					$stream.="<tr>"; 	
						$stream.="<td align=center>".$_SESSION['lang']['nodo']."</td>";
						
						$stream.="<td align=center>".$_SESSION['lang']['transportir']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['barang']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['NoKontrak']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['kuantitas']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['toleransi']."<br>(%)</td>";
						$stream.="<td align=center>".$_SESSION['lang']['hargasatuan']."<br>(Rp)</td>";
						
						$stream.="<td align=center>".$_SESSION['lang']['kuantitas']."<br>".$_SESSION['lang']['muat']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['kuantitas']."<br>".$_SESSION['lang']['bongkar']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['toleransi']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['kuantitas']."<br>".$_SESSION['lang']['dibayar']."</td>";
						$stream.="<td align=center>".$_SESSION['lang']['rupiah']."<br>".$_SESSION['lang']['dibayar']."</td>";
					$stream.="</tr>"; 
					$stream.="</thead>";  
					
					if($param['nodok']!=''){
						$where=" and nodo like '%".$param['nodok']."%'";
					}
					
						
					$str="select * from ".$dbname.".pmn_suratperintahpengiriman where 
						pt='".$param['kodeorg']."' and kodeunit='".$param['unit']."' ".$where." ";
						// echo $str;
					$res=fetchdata($str);
					foreach($res as $bar){
						$arrnodo[$bar['nodo']]=$bar['nodo'];
						$dttransportir[$bar['nodo']]=$bar['transportir'];
						$dtnokontrak[$bar['nodo']]=$bar['nokontrak'];
						$dtkodebarang[$bar['nodo']]=$bar['kodebarang'];
						$dtharga[$bar['nodo']]=$bar['harga'];
						$dtqty[$bar['nodo']]=$bar['qty'];
						$dttoleransi[$bar['nodo']]=$bar['toleransi'];
					}
					
					$str="select * from ".$dbname.".pmn_bastdt_vw where nodo in ('".implode("','",$arrnodo)."')";
					$res=fetchdata($str);
					foreach($res as $bar){
						if($bar['tipetransaksi']=='1' || $bar['tipetransaksi']=='3'){
							@$dtkuantitasmuat[$bar['nodo']]+=$bar['kuantitas'];
						}
						if($bar['tipetransaksi']=='2' || $bar['tipetransaksi']=='4'){
							@$dtkuantitasbongkar[$bar['nodo']]+=$bar['kuantitas'];
						}
						
					}
					
					
					foreach($arrnodo as $dtnodo){
						@$no+=1;
						$stream.="<tr class=rowcontent onclick=findnodokdetail('".$dtnodo."','".$param['tipeinvoice']."')>";
							$stream.="<td style=cursor:pointer align=center>".$no."</td>";
							$stream.="<td style=cursor:pointer>".$dtnodo."</td>";
							
							$stream.="<td style=cursor:pointer>".$dttransportir[$dtnodo]."</td>";
							$stream.="<td style=cursor:pointer>".$nmbarangpabrik[$dtkodebarang[$dtnodo]]."</td>";
							$stream.="<td style=cursor:pointer>".$dtnokontrak[$dtnodo]."</td>";
							$stream.="<td style=cursor:pointer align=right>".@number_format($dtqty[$dtnodo])."</td>";
							$stream.="<td style=cursor:pointer align=right>".@number_format($dttoleransi[$dtnodo],2)."</td>";
							$stream.="<td style=cursor:pointer align=right>".@number_format($dtharga[$dtnodo],2)."</td>";
							
							$stream.="<td style=cursor:pointer align=right>".@number_format($dtkuantitasmuat[$dtnodo])."</td>";
							$stream.="<td style=cursor:pointer align=right>".@number_format($dtkuantitasbongkar[$dtnodo])."</td>";
							
							$dttoleransibast[$dtnodo]=$dttoleransi[$dtnodo]/100*$dtkuantitasmuat[$dtnodo];
							$dttoleransibastbatas[$dtnodo]=$dtkuantitasmuat[$dtnodo]-$dttoleransibast[$dtnodo];
							#= buat perhitungan yang dipakai
							#= jika kg bongkar < muat-toleransi(kg batas toleransi), maka kg yang dibayar adalah kg bongkar
							#= jika kg bongkar > muat-toleransi(kg batas toleransi) , maka kg yang dibayar adalah kg muat
							if($dtkuantitasbongkar[$dtnodo]<$dttoleransibastbatas[$dtnodo]){
								$dtkuantitasdibayar[$dtnodo]=$dtkuantitasbongkar[$dtnodo];
							}
							if($dtkuantitasbongkar[$dtnodo]>=$dttoleransibastbatas[$dtnodo]){
								$dtkuantitasdibayar[$dtnodo]=$dtkuantitasmuat[$dtnodo];
							}
							
							$dtrupiahdibayar[$dtnodo]=$dtkuantitasdibayar[$dtnodo]*$dtharga[$dtnodo];
							
							$stream.="<td style=cursor:pointer align=right>".@number_format($dttoleransibast[$dtnodo])."</td>";
							$stream.="<td style=cursor:pointer align=right>".@number_format($dtkuantitasdibayar[$dtnodo])."</td>";
							$stream.="<td style=cursor:pointer align=right>".@number_format($dtrupiahdibayar[$dtnodo],2)."</td>";
						$stream.="</tr>";
					}
					
				break;
				*/



				$stream .= "</table></div>";

			default:
				// echo "Tipe invoice ".$param['tipeinvoice']." belum dicoding findnodok ";
				echo "Tipe invoice " . $param['tipeinvoice'] . " tidak ada, silahkan input manual nomor dokumen";
				break;
		}

		echo $stream;

		break;



	case 'showformposting':

		// get today date

		$tab .= "<fieldset style='width:95%'>";
		$tab .= "<div style=max-height:200px;overflow:auto;>";
		$tab .= "Yakin akan memposting?";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
		$tab .= "<tbody>
			<tr class=rowcontent><td>" . $_SESSION['lang']['noinvoice'] . "</td><td>" . $noinvoice . "</td></tr>
			<tr class=rowcontent><td>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['jurnal'] . "</td><td><input type=text class=myinputtext id=tanggaljurnal name=tanggaljurnal onkeypress=return false;  maxlength=10 style=width:57px;/ value='" . date("d-m-Y") . "'></td></tr>
			<tr class=rowcontent><td><button id=tomblo title='Posting Data' onclick=\"postingData_('" . $noinvoice . "');\" >Proses</button></td><td></td></tr>
			</tbody>";
		// pake setCalendar kalo pengen tanggal jurnal bisa diganti:
		// <tr class=rowcontent><td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['jurnal']."</td><td><input type=text class=myinputtext id=tanggaljurnal name=tanggaljurnal onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:57px;/ value='".date("d-m-Y")."'></td></tr>
		$tab .= "</table>";
		$tab .= "</div>";
		$tab .= "</fieldset>";

		echo $tab;
		break;

	case 'findnodokdetail':


		#= delete 1st
		#= karna saat tombol proses, insert table ini dahulu, baru ke keu_tagihanht, 
		#= jika sudah proses ini, tapi invoice batal dibuat, maka jika tarik nomor po lagi, delete dlu data yang tidak jadi dibuat (noinvoice kosong)
		$str = "delete from " . $dbname . ".keu_tagihandt where noinvoice='' and nopo='" . $param['nodok'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}

		$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='AD' and  kodeparameter='TGHADJUST'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$dtnoakunlain = $bar['nilai'];
		}


		$stream = "";
		// $stream.="<fieldset>";
		// $stream.="<legend>No. Document : ".$param['nodok']."</legend>";
		$stream .= "<br>No. Document : " . $param['nodok'] . "";
		$stream .= "<div style=overflow:auto;>";
		//$stream.=" No. Document : ".$param['nodok']."  ";
		//$stream.="<br>";
		// $stream.="<br>";


		$str = "select * from " . $dbname . ".keu_tagihanht where nopo='" . $param['nodok'] . "' and tipeinvoice='um' ";
		$res = fetchdata($str);
		$adaum = count($res);
		foreach ($res as $bar) {
			@$dtuangmukadpp += $bar['nilaidpp'];
			@$dtuangmuka += $bar['nilaiinvoice'];
			@$dtuangmukappn += $bar['nilaiinvoice'] - $bar['nilaidpp'];
			@$dtnoakunuangmuka = $bar['noakun'];
			// @$dtinvoicedpp+=$bar['nilaidpp'];
			// @$dtinvoiceppn+=$bar['nilaiinvoice']-$bar['nilaidpp'];
			// @$dtinvoice+=$bar['nilaiinvoice'];
			// @$dtinvoice+=$bar['nilaiinvoice'];
		}

		/*
		$str="select * from ".$dbname.".keu_tagihanht where nopo='".$param['nodok']."' and tipeinvoice!='um' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			@$dtinvoicedpp+=$bar['nilaidpp'];
			@$dtinvoiceppn+=$bar['nilaiinvoice']-$bar['nilaidpp'];
			@$dtinvoice+=$bar['nilaiinvoice'];
			// @$dtinvoice+=$bar['nilaiinvoice'];
		}	
		*/

		$str = "select * from " . $dbname . ".keu_tagihandt where noinvoice in (select noinvoice from " . $dbname . ".keu_tagihanht where nopo='" . $param['nodok'] . "' and tipeinvoice!='um') and noakun='" . @$dtnoakunuangmuka . "' ";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$dtinvoicedpp += ($bar['nilai'] * -1);
			@$dtinvoiceppn += $bar['nilaiinvoice'] - $bar['nilaidpp'];
			@$dtinvoice += ($bar['nilai'] * -1);
		}
		// echo $dtinvoicedpp;
		// $stream.="<fieldset>";
		//$stream.="<legend>".$_SESSION['lang']['uangmuka']."</legend>";
		if ($adaum == 0) {
			// $hideum=" style=display:none;";
			$hideum = "";
		} else {
			$hideum = "";
		}

		$stream .= "<div style=clear:both></div>";
		$stream .= "<span " . $hideum . " ><b>" . $_SESSION['lang']['uangmuka'] . "</b></span>";
		$stream .= "<table " . $hideum . " cellpadding=5 cellspacing=1 border=0 class='sortable'>";
		$stream .= "<thead>";
		$stream .= "<tr class='rowheader'>";
		$stream .= "<th align=center colspan=3>" . $_SESSION['lang']['uangmuka'] . "</th>";
		$stream .= "<th align=center colspan=3>" . $_SESSION['lang']['invoice'] . "</th>";
		$stream .= "<th align=center colspan=3>" . $_SESSION['lang']['sisa'] . "</th>";
		$stream .= "</tr>";
		$stream .= "<tr class='rowheader'>";
		for ($i = 1; $i <= 3; $i++) {
			$stream .= "<th align=center>DPP</th>";
			$stream .= "<th align=center>PPN</th>";
			$stream .= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
		}


		@$dtsisadppum = $dtuangmukadpp - $dtinvoicedpp;
		@$dtsisaum = $dtuangmuka - $dtinvoice;
		@$dtsisappnum = $dtsisaum - $dtsisadppum;

		#= uang muka
		$stream .= "</tr>";
		$stream .= "</thead>";
		$stream .= "<tr class='rowcontent'>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtuangmukadpp, 2) . "</td>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtuangmukappn, 2) . "</td>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtuangmuka, 2) . "</td>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtinvoicedpp, 2) . "</td>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtinvoiceppn, 2) . "</td>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtinvoice, 2) . "</td>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtsisadppum, 2) . "</td>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtsisappnum, 2) . "</td>";
		$stream .= "<td align=center>" . @hidezerodecimal($dtsisaum, 2) . "</td>";
		$stream .= "</tr>";
		$stream .= "</table>";
		// $stream.="</fieldset>";
		//$stream.="<hr>";


		#= data
		// $stream.="<fieldset>";
		//$stream.="<legend>".$_SESSION['lang']['transaksi']."</legend>";
		$stream .= "<span><b>" . $_SESSION['lang']['transaksi'] . "</b></span>";
		$stream .= "<table cellpadding=2 cellspacing=1 border=0 class='sortable'>";
		$stream .= "<thead>";
		$stream .= "<tr class='rowheader'>";
		$stream .= "<th rowspan=2>No.</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>";
		$stream .= "<th align=center width=70px>" . $_SESSION['lang']['tanggal'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['termin'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['rupiah'] . "<br>" . $_SESSION['lang']['gudang'] . "<br>/" . $_SESSION['lang']['transaksi'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['rupiah'] . "<br>" . str_replace("/", "/<br>", $_SESSION['lang']['retur']) . "<br>" . $_SESSION['lang']['klaim'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['rupiah'] . "<br>" . $_SESSION['lang']['uangmuka'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['rupiah'] . "<br>" . $_SESSION['lang']['total'] . "</th>";

		if ($param['tipeinvoice'] == 'k'):
			$stream .= "<th align=center>" . $_SESSION['lang']['rupiah'] . "<br> (Pemakaian Barang Gudang)</th>";
		endif;

		$stream .= "<th align=center>" . $_SESSION['lang']['rupiah'] . "<br>" . $_SESSION['lang']['lain'] . "</th>";
		$stream .= "<th align=center width=40px>" . $_SESSION['lang']['noaruskas'] . "</th>";
		$stream .= "<th align=center width=50px>" . $_SESSION['lang']['noakun'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['noakun'] . "<br>" . $_SESSION['lang']['uangmuka'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['noakun'] . "<br>" . $_SESSION['lang']['lain'] . "</th>";

		$stream .= "<th align=center width=50px>" . $_SESSION['lang']['kelompokbarang'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['adkcip'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['rekening'] . "</th>";

		$stream .= "<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>";
		$stream .= "<th align=center>" . $_SESSION['lang']['action'] . "</th>";
		$stream .= "</tr>";
		$stream .= "</thead>";

		switch ($param['tipeinvoice']) {

			case 'ffbfee':
				$str = "select sum(totalrp) as totalrp,sum(rpppn) as rpppn,notransaksi,kodesupplier,unit,tanggal,rekening,noakunkredit,noakundebet,tanggaltbs1,tanggaltbs2 from " . $dbname . ".pmn_feetbs where notransaksi='" . $param['nodok'] . "' group by rekening";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksi = $bar['notransaksi'];
					$dttanggal = $bar['tanggal'];
					$dttanggaltbs1 = $bar['tanggaltbs1'];
					$dttanggaltbs2 = $bar['tanggaltbs2'];
					$dttotalrp[$bar['rekening']] = floor($bar['totalrp']);
					$dtsupplier = $bar['kodesupplier'];
					$arratasnama[$bar['rekening']] = $bar['rekening'];

					$dtnilai[$bar['rekening']][$bar['noakundebet']] = floor($bar['totalrp']);
					// $dtrpppn[$bar['rekening']][$bar['noakundebet']]=floor($bar['rpppn']);
					$arrnoakun[$bar['noakundebet']] = $bar['noakundebet'];

					#= cari noaruskas untuk akun hutang
					$str1 = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $arrnoakun) . "')";
					$res1 = fetchdata($str1);
					foreach ($res1 as $bar1) {
						$dtnoaruskas[$bar1['noakun']] = $bar1['noaruskas'];
					}

					// print_r($arrnoakun);

					#= ambil noakun ppn dan aruskas
					$str1 = "select * from " . $dbname . ".keu_5jenistagihan_akunpajak where kode='" . $param['tipeinvoice'] . "' ";
					$res1 = fetchdata($str1);
					foreach ($res1 as $bar1) {
						$arrnoakun[$bar1['noakun']] = $bar1['noakun'];
						$dtnilai[$bar['rekening']][$bar1['noakun']] = $bar['rpppn'];
						$dtnoaruskas[$bar1['noakun']] = $bar1['noaruskas'];
					}

					#= cek data sudah ada / belum
					$str1 = "select * from " . $dbname . ".keu_tagihandt where notransaksi='" . $param['nodok'] . "' and notransaksi!='' ";
					$res1 = fetchdata($str1);
					foreach ($res1 as $bar1) {
						// $dtnotransaksiada[$bar1['noakun']][$bar1['keterangan']]=$bar1['noakun'];
						$dtnotransaksiada[$bar1['noakun']][$bar1['keterangan']] = $bar1['keterangan'];
						$dtnoinvoice[$bar1['noakun']] = $bar1['noinvoice'];
					}
				}


				// echo"<pre>";
				// print_r($dtnotransaksiada);

				foreach ($arratasnama as $dtatasnama) {
					foreach ($arrnoakun as $dtnoakun) {
						@$no += 1;
						$stream .= "<tr class=rowcontent>";
						$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
						$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
						$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal) . "</td>";
						$stream .= "<td style=cursor:pointer id=termindt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer></td>";
						$stream .= "<td style=cursor:pointer></td>";

						$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " disabled onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format(0) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";

						$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($dtnilai[$dtatasnama][$dtnoakun], 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";


						$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas[$dtnoakun] . "</td>";

						$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun . "</td>";
						$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . @$dtnoakunuangmuka . "</td>";
						// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
						$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
						$stream .= "<td style=cursor:pointer id=kelompokbarangdt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer id=kodekegiatandt" . $no . ">" . @$dtkodekegiatan . "</td>";
						$stream .= "<td style=cursor:pointer id=kodeassetdt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . ">" . $dtatasnama . "</td>";

						$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">Biaya administrasi KUD periode " . tanggalnormal($dttanggaltbs1) . " s/d " . tanggalnormal($dttanggaltbs2) . " " . @$nmsupplier[$dtsupplier] . " rekening : " . $dtatasnama . "</td>";
						// if(@$dtnotransaksiada[$dtnoakun]["Insentif TBS ".@$nmsupplier[$dtsupplier]." rekening : ".$dtatasnama.""]==@$dtnoakun){
						$kiterkiter = "Biaya administrasi KUD periode " . tanggalnormal($dttanggaltbs1) . " s/d " . tanggalnormal($dttanggaltbs2) . " " . @$nmsupplier[$dtsupplier] . " rekening : " . $dtatasnama . "";
						if (@$dtnotransaksiada[$dtnoakun][$kiterkiter] == @$kiterkiter) {
							$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnoakun] . "
											<input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . ">
										</td>";
						} else {
							$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
						}
						$stream .= "</tr>";
					}
				}

				break;


			case 'batr':

				$str = "select sum(kgkirim) as kgkirim,sum(kgterima) as kgterima,sum(kgselisih) as kgselisih,sum(rpjumlah) as rpjumlah,sum(kgclaim) as kgclaim,sum(rpclaim) as rpclaim,createby,updateby,notransaksi,tanggal,unit,keterangan,tipe,posting,nokontrak,nospk,transportir,noakundebet from " . $dbname . ".pmn_batransport where nospk='" . $param['nodok'] . "' and posting=1 group by notransaksi order by tanggal desc";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					$dtsupplier[$bar['notransaksi']] = $bar['transportir']; //transportir
					$dttipesupplier[$bar['notransaksi']] = 'TRANSPORTIR'; //transportir
					$dtnilaitransaksi[$bar['notransaksi']] = $bar['rpjumlah'];
					$dtnilaiclaim[$bar['notransaksi']] = $bar['rpclaim'];
					// $dtnilaidpp[$bar['notransaksi']]=$bar['rpjumlah']+$bar['rpclaim'];
					$dtnilaidpp[$bar['notransaksi']] = $bar['rpjumlah'];
					$dtnilaippn[$bar['notransaksi']] = (0.1 * ($bar['rpjumlah'] + $bar['rpclaim']));
					$dttipe[$bar['notransaksi']] = $bar['tipe'];
					$dtnokontrak[$bar['notransaksi']] = $bar['nokontrak'];
					$arrsupplier[$bar['notransaksi']] = $bar['transportir'];
					$dtnoakun = $bar['noakundebet'];
				}



				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun='" . $dtnoakun . "' and noaruskas like '1%' and noaruskas in (select noaruskas from keu_5aruskas where nama_aruskas like '%transport%')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas = $bar['noaruskas'];
				}

				#= cek data sudah ada / belum
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!=''";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']] = $bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
				}

				foreach ($arrnotransaksi as $dtnotransaksi) {

					@$no += 1;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
					$stream .= "<td style=cursor:pointer id=termindt" . $no . "></td>";

					$stream .= "<td style=cursor:pointer align=right id=nilaitransaksidt" . $no . ">" . @number_format($dtnilaitransaksi[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=nilaireturdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text disabled class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format($dtnilaiuangmuka[$dtnotransaksi], 2) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";


					$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($dtnilaidpp[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";
					$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun . "</td>";

					$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . "></td>";
					// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
					$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
					$stream .= "<td style=cursor:pointer align=right id=kelompokbarangdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer align=right id=kodekegiatandt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer align=right id=kodeassetdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">DPP</td>";

					if (@$dtnotransaksiada[$dtnotransaksi] == @$dtnotransaksi) {
						$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . " <input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					} else {
						$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					}

					$stream .= "</tr>";
					if (abs($dtnilaiclaim[$dtnotransaksi]) > 0) {
						@$no += 1;
						$stream .= "<tr class=rowcontent>";
						$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
						$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
						$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
						$stream .= "<td style=cursor:pointer id=termindt" . $no . "></td>";

						$stream .= "<td style=cursor:pointer align=right id=nilaitransaksidt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer align=right id=nilaireturdt" . $no . ">" . @number_format($dtnilaiclaim[$dtnotransaksi], 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right><input type=text disabled class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format($dtnilaiuangmuka[$dtnotransaksi], 2) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";


						$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($dtnilaiclaim[$dtnotransaksi], 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";
						$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
						$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun . "</td>";

						$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . "></td>";
						// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
						$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
						$stream .= "<td style=cursor:pointer align=right id=kelompokbarangdt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer align=right id=kodekegiatandt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer align=right id=kodeassetdt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">CLAIM</td>";

						if (@$dtnotransaksiada[$dtnotransaksi] == @$dtnotransaksi) {
							$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . " <input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
						} else {
							$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
						}

						$stream .= "</tr>";
					}
					//

				}
				break;



			case 'rtg':

				$str = "select notransaksi,sum(hargasatuan*jumlah) as hartot,tanggal,left(kodebarang,3) as kelompokbarang,kodebarang from " . $dbname . ".log_transaksi_vw 
				where nopo='" . $param['nodok'] . "' and post=1 and tipetransaksi='6' group by notransaksi order by tanggal asc";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					$dthartot[$bar['notransaksi']] = 0;
					$dthartotretur[$bar['notransaksi']] = $bar['hartot'];
					$dtkelompokbarang[$bar['notransaksi']] = $bar['kelompokbarang'];
					$dtkodebarang[$bar['notransaksi']] = $bar['kodebarang'];
				}

				#= ambil namabarang
				$str = "select * from " . $dbname . ".log_5masterbarang 
					where kodebarang in ('" . implode("','", $dtkodebarang) . "')";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$namabarang[$bar['kodebarang']] = $bar['namabarang'];
				}


				#= ambil data aruskas dan coa
				$str = "select * from " . $dbname . ".log_5klbarang 
					where kode in ('" . implode("','", $dtkelompokbarang) . "')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoakun = $bar['noakun'];
				}

				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun='" . $dtnoakun . "' and noaruskas like '1%'";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas = $bar['noaruskas'];
				}




				#= cek data sudah ada / belum
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!='' and noinvoice in (select noinvoice from " . $dbname . ".keu_tagihanht where tipeinvoice='rtg')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']] = $bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
				}

				foreach ($arrnotransaksi as $dtnotransaksi) {
					@$no += 1;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
					$stream .= "<td style=cursor:pointer id=termindt" . $no . "></td>";

					$stream .= "<td style=cursor:pointer align=right id=nilaitransaksidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=nilaireturdt" . $no . ">" . @number_format($dthartotretur[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format($dtnilaiuangmuka[$dtnotransaksi], 2) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";
					$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format(0 - $dthartotretur[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";

					$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . $dtnoakunuangmuka . "</td>";
					// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
					$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
					$stream .= "<td style=cursor:pointer align=right id=kelompokbarangdt" . $no . ">" . $dtkelompokbarang[$dtnotransaksi] . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=kodekegiatandt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer align=right id=kodeassetdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">" . $namabarang[$dtkodebarang[$dtnotransaksi]] . "</td>";

					// if(@$dtnotransaksiada[$dtnotransaksi]==@$dtnotransaksi or (@$dthartot[$dtnotransaksi]-@$dthartotretur[$dtnotransaksi]=='0')){
					if (@$dtnotransaksiada[$dtnotransaksi] == @$dtnotransaksi) {
						$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . " <input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					} else {
						$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					}
					$stream .= "</tr>";
				}
				break;

			case 'rtn':

				$str = "select notransaksi,(sum(hargasatuan*jumlah)*-1) as hartot,tanggal,left(kodebarang,3) as kelompokbarang,kodebarang,noakun,termin from " . $dbname . ".log_retursuppliernoninventorydt_vw 
				where nopo='" . $param['nodok'] . "' and posting=1  group by notransaksi order by tanggal asc";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					$dthartot[$bar['notransaksi']] = $bar['hartot'];
					$dtkelompokbarang[$bar['notransaksi']] = $bar['kelompokbarang'];
					$dtkodebarang[$bar['notransaksi']] = $bar['kodebarang'];
					$dtnoakun[$bar['notransaksi']] = $bar['noakun'];
					$dttermin[$bar['notransaksi']] = $bar['termin'];
				}

				#= ambil namabarang
				$str = "select * from " . $dbname . ".log_5masterbarang 
					where kodebarang in ('" . implode("','", @$dtkodebarang) . "')";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$namabarang[$bar['kodebarang']] = $bar['namabarang'];
				}

				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $dtnoakun) . "') and noaruskas like '1%'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas = $bar['noaruskas'];
				}


				#= cek data sudah ada / belum
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!=''";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']] = $bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
					// if($bar['noakun']=='1180301'){
					if (substr($bar['noakun'], 0, 5) == '11801') {
						$dtnilaiuangmuka[$bar['notransaksi']] = $bar['nilai'];
					}
				}



				$str = "select * from " . $dbname . ".keu_tagihanht where nopo='" . $param['nodok'] . "' and tipeinvoice='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoakunuangmuka = $bar['noakun'];
				}



				foreach ($arrnotransaksi as $dtnotransaksi) {
					@$no += 1;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
					$stream .= "<td style=cursor:pointer align=center id=termindt" . $no . ">" . $dttermin[$dtnotransaksi] . "</td>";

					$stream .= "<td style=cursor:pointer align=right id=nilaitransaksidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=nilaireturdt" . $no . ">" . @number_format($dthartotretur[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber disabled id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format(@$dtnilaiuangmuka[$dtnotransaksi], 2) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";

					$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi] - $dthartotretur[$dtnotransaksi], 2) . "</td>";

					#= lainnya
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";

					$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun[$dtnotransaksi] . "</td>";
					$stream .= "<td style=cursor:pointer align=right  id=noakunuangmukadt" . $no . ">" . @$dtnoakunuangmuka . "</td>";
					// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
					$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
					$stream .= "<td style=cursor:pointer align=right id=kelompokbarangdt" . $no . ">" . $dtkelompokbarang[$dtnotransaksi] . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=kodekegiatandt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer align=right id=kodeassetdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">" . $namabarang[$dtkodebarang[$dtnotransaksi]] . "</td>";
					if (@$dtnotransaksiada[$dtnotransaksi] == @$dtnotransaksi or (@$dthartot[$dtnotransaksi] - @$dthartotretur[$dtnotransaksi] == '0')) {
						$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . " <input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					} else {
						$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					}
					$stream .= "</tr>";
				}

				break;

			case 'p':
				#== Abdul ==#
				#== Comment By Abdul ==#
				#== Dikarenakan sudah lakukan sum hartot di line 4855 ($dthartot) 
				#== Sudah menggambarkan nilai per kodebarang yang di diskon

				# cari nilai diskon
				// $tempdiskon = 0; $diskonperbarang = [];
				// $str="select * from ".$dbname.".log_podt a left join ".$dbname.".log_poht b on a.nopo=b.nopo where b.nopo='".$param['nodok']."'";
				// $res=fetchdata($str);
				// $jumlahbaris=count($res);
				// foreach($res as $bar){
				// 	$nomor++;
				// 	if($nomor<$jumlahbaris){						
				// 		$diskonperbarang[$bar['kodebarang']]+=round((($bar['jumlahpesan']*$bar['hargasatuan'])/$bar['subtotal'])*$bar['nilaidiskon'],0);
				// 		$tempdiskon+=round((($bar['jumlahpesan']*$bar['hargasatuan'])/$bar['subtotal'])*$bar['nilaidiskon'],0);
				// 	}else{
				// 		#== Abdul
				// 		#== Origin
				// 		// $diskonperbarang[$bar['kodebarang']]+=$bar['nilaidiskon']-$tempdiskon;
				// 		#== Add IF Validasi jika barang lebih dari 1 maka 
				// 		#== Gunakan $diskonperbarangini
				// 		if ($jumlahbaris > 1) {
				// 			$diskonperbarang[$bar['kodebarang']] += $bar['nilaidiskon'] - $tempdiskon;
				// 		}
				// 		#== End Abdul
				// 	}
				// }
				// echo "<pre>";
				// print_r($diskonperbarang);
				#== End Abdul ==#

				# Get Referensi
				$str = "select notransaksi,sum(hargasatuan*jumlah) as hartot, sum(ongkir*jumlah) as ongkir, tanggal,left(kodebarang,3) as kelompokbarang,kodebarang, nopo from " . $dbname . ".log_transaksi_vw 
				where nopo='" . $param['nodok'] . "' and post=1 and tipetransaksi='1' group by notransaksi,kodebarang order by tanggal asc";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					# Untuk get noakun
					$notransaksiarr[$bar['nopo']] = $bar['notransaksi'];
				}

				// = GET = #
				$sql = selectQuery($dbname, "log_sorefrensi", "sum(hargasatuan*jumlah) as hartot,left(kodebarang,3) as kelompokbarang,nopo,noso", "nopo='" . $param['nodok'] . "'");
				$res = fetchData($sql);
				//echo $sql;
				$datasoref = array();
				foreach ($res as $val) {
					$supplierPo = makeOption($dbname, "log_poht", "nopo,kodesupplier", "nopo='{$val['nopo']}'")[$val['nopo']];
					$supplierSo = makeOption($dbname, "log_poht", "nopo,kodesupplier", "nopo='{$val['noso']}'")[$val['noso']];
					$pbbkbPo = makeOption($dbname, "log_poht", "nopo,pbbkb", "nopo='{$val['nopo']}'")[$val['nopo']] ?? 0;

					//if ($val['kelompokbarang'] == '351') {
					if ($supplierPo == $supplierSo) {
						$dthartot[$notransaksiarr[$val['nopo']]][$val['kelompokbarang']] = $val['hartot'] + $pbbkbPo;
						$datasoref[$notransaksiarr[$val['nopo']]][$val['kelompokbarang']] = 1;
					}

					//}
				}

				$str = "select notransaksi,sum(hargasatuan*jumlah) as hartot, sum(ongkir*jumlah) as ongkir, tanggal,left(kodebarang,3) as kelompokbarang,kodebarang, nopo from " . $dbname . ".log_transaksi_vw 
				where nopo='" . $param['nodok'] . "' and post=1 and tipetransaksi='1' group by notransaksi,kodebarang order by tanggal asc";
				//echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					if (!isset($datasoref[$bar['notransaksi']][$bar['kelompokbarang']])) {
						$datasoref[$bar['notransaksi']][$bar['kelompokbarang']] = 0;
					}
					$arrnotransaksi[$bar['notransaksi']][$bar['kelompokbarang']] = $bar['notransaksi'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];

					$nilaiongkir[$bar['notransaksi']][$bar['kelompokbarang']] = $bar['ongkir'];
					#== Update by Abdul ==#
					#== Comment by Abdul ==#
					if ($datasoref[$bar['notransaksi']][$bar['kelompokbarang']] == 0) {
						$dthartot[$bar['notransaksi']][$bar['kelompokbarang']] += $bar['hartot'] - $nilaiongkir[$bar['notransaksi']][$bar['kelompokbarang']];
					}
					//  else {
					// 	$dthartot[$bar['notransaksi']][$bar['kelompokbarang']] -= $nilaiongkir[$bar['notransaksi']][$bar['kelompokbarang']];
					// }

					// $dthartot[$bar['notransaksi']]+=$bar['hartot'];
					#== End Comment by Abdul ==#

					$dtkelompokbarang[$bar['notransaksi']][$bar['kelompokbarang']] = $bar['kelompokbarang'];
					$dtkodebarang[$bar['notransaksi']] = $bar['kodebarang'];

					$nilaidiskon[$bar['notransaksi']] += $diskonperbarang[$bar['kodebarang']];
				}


				// echo "<pre>";
				// print_r($nilaiongkir);
				# = END = #

				# = GET PENGURANG = #
				if ($param['tipeinvoice'] == 'p') {
					$str = "select * from " . $dbname . ".log_poht where stat_release=1 and tipepo='PO' and nopo='" . $param['nodok'] . "'";
				}

				$res = fetchdata($str);
				foreach ($res as $bar) {
					$pbbkbfndt[$notransaksiarr[$bar['nopo']]] = $bar['pbbkb'];

					$arrnopo[$bar['nopo']] = $bar['nopo'];
					$nilaippnfn[$bar['nopo']] = ($bar['subtotal'] - ($bar['subtotal'] * $bar['diskonpersen']) / 100) * $bar['persenppn'] / 100;

					# Array Penambah PPh 22
					$arrpph22penambah[$bar['nopo']] = $bar['penambahpph22'];

					if ($bar['pph22'] > 0) {
						$nilaipphfn[$notransaksiarr[$bar['nopo']]] = ($bar['subtotal'] - ($bar['subtotal'] * $bar['diskonpersen']) / 100) * $bar['persenpph'] / 100;
					}

					if ($bar['pph'] > 0) {
						$nilaipphfn23[$notransaksiarr[$bar['nopo']]] = ($bar['subtotal'] - ($bar['subtotal'] * $bar['diskonpersen']) / 100) * $bar['persenpph'] / 100;
					}
				}
				# = END GET = #

				#= ambil namabarang
				$str = "select * from " . $dbname . ".log_5masterbarang 
					where kodebarang in ('" . implode("','", $dtkodebarang) . "')";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$namabarang[$bar['kodebarang']] = $bar['namabarang'];
				}


				#= ambil data aruskas dan coa
				$str = "select * from " . $dbname . ".log_5klbarang 
					where kode in ('" . implode("','", $dtkelompokbarang[$notransaksiarr[$param['nodok']]]) . "')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoakun = $bar['noakun'];
				}

				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun='" . $dtnoakun . "' and noaruskas like '1%'";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas = $bar['noaruskas'];
				}

				#= cek data sudah ada / belum
				// $str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!=''";
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' AND noinvoice IN (SELECT noinvoice FROM keu_tagihanht WHERE tipeinvoice='um' AND nopo='" . $param['nodok'] . "' and notransaksi!='')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']] = $bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
					// if($bar['noakun']=='1180301'){
					if (substr($bar['noakun'], 0, 5) == '11801') {
						$dtnilaiuangmuka[$bar['notransaksi']] += $bar['nilai'];
					}
				}



				#= data retur
				#= bentuk data retur
				$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo,notransaksireferensi from " . $dbname . ".log_transaksi_vw
				where notransaksireferensi  in ('" . implode("','", $arrnotransaksi) . "') and post=1  and tipetransaksi='6' group by notransaksireferensi";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dthartotretur[$bar['notransaksireferensi']] = $bar['hartot'];
				}


				$str = "select * from " . $dbname . ".keu_tagihanht where nopo='" . $param['nodok'] . "' and tipeinvoice='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					// @$dtuangmukadpp+=$bar['nilaidpp'];
					// @$dtuangmuka+=$bar['nilaiinvoice'];
					$dtnoakunuangmuka = $bar['noakun'];
				}


				foreach ($arrnotransaksi as $dtnotransaksi => $valn) {
					foreach ($valn as $klmpkbarang => $valb):
						@$no += 1;
						$stream .= "<tr class=rowcontent>";
						$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
						$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
						$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
						$stream .= "<td style=cursor:pointer id=termindt" . $no . "></td>";

						$stream .= "<td style=cursor:pointer align=right id=nilaitransaksidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi][$klmpkbarang], 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right id=nilaireturdt" . $no . ">" . @number_format($dthartotretur[$dtnotransaksi], 2) . "</td>";
						$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format($dtnilaiuangmuka[$dtnotransaksi], 2) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";

						// $stream .= "<td style=cursor:pointer title='Rp gudang - Rp Retur - Rp UM - Rp Diskon = " . $nilaidiskon[$dtnotransaksi] . "' align=right id=nilaidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi][$klmpkbarang] - $dthartotretur[$dtnotransaksi] + $pbbkbfndt[$dtnotransaksi], 2) . "</td>";
						$stream .= "<td style=cursor:pointer title='Rp gudang - Rp Retur - Rp UM - Rp Diskon = " . $nilaidiskon[$dtnotransaksi] . "' align=right id=nilaidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi][$klmpkbarang] - $dthartotretur[$dtnotransaksi], 2) . "</td>";

						$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";

						$stream .= "<td style=cursor:pointer title='" . getNamaArusKas($dtnoaruskas) . "' align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
						$stream .= "<td style=cursor:pointer title='" . getNamaAkun($dtnoakun) . "' align=right id=noakundt" . $no . ">" . $dtnoakun . "</td>";
						$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . $dtnoakunuangmuka . "</td>";
						// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
						$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
						$stream .= "<td style=cursor:pointer align=right id=kelompokbarangdt" . $no . ">" . $dtkelompokbarang[$dtnotransaksi][$klmpkbarang] . "</td>";
						$stream .= "<td style=cursor:pointer align=right id=kodekegiatandt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer align=right id=kodeassetdt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
						$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">" . $namabarang[$dtkodebarang[$dtnotransaksi]] . "</td>";

						if (@$dtnotransaksiada[$dtnotransaksi] == @$dtnotransaksi or (@$dthartot[$dtnotransaksi][$klmpkbarang] - @$dthartotretur[$dtnotransaksi] == '0')) {
							$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . " <input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
						} else {
							$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
						}
						$stream .= "</tr>";
					endforeach;
				}
				break;

			case 'pon':

				$str = "select notransaksi,sum(hargasatuan*jumlah) as hartot,tanggal,left(kodebarang,3) as kelompokbarang,kodebarang,noakun,termin from " . $dbname . ".log_noninventorydt_vw 
				where nopo='" . $param['nodok'] . "' and posting=1 group by notransaksi order by tanggal asc";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$strso = "select SUM(harga*jumlah) as total from " . $dbname . ".log_somaterial where nopo='" . $param['nodok'] . "' order by nopo asc";
					$resso = fetchdata($strso);
					foreach ($resso as $val) {
						$rupso = $val['total'];
					}

					$arrnotransaksi[$bar['notransaksi']][$bar['kelompokbarang']] = $bar['notransaksi'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					$dthartot[$bar['notransaksi']][$bar['kelompokbarang']] = $bar['hartot'] + $rupso;
					$dtkelompokbarang[$bar['notransaksi']] = $bar['kelompokbarang'];
					$dtkodebarang[$bar['notransaksi']] = $bar['kodebarang'];
					$dtnoakun[$bar['notransaksi']] = $bar['noakun'];
					$dttermin[$bar['notransaksi']] = $bar['termin'];
				}

				#= ambil namabarang
				$str = "select * from " . $dbname . ".log_5masterbarang 
					where kodebarang in ('" . implode("','", @$dtkodebarang) . "')";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$namabarang[$bar['kodebarang']] = $bar['namabarang'];
				}

				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $dtnoakun) . "') and noaruskas like '1%'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas = $bar['noaruskas'];
				}

				# Cek apakah pakai termin
				$sql = "select a.notransaksi,sum(a.hargasatuan*a.jumlah) as hartot,a.tanggal,left(a.kodebarang,3) as kelompokbarang,a.kodebarang,a.noakun,b.termin,b.rupiah as rupiahtermin,b.bayar from " . $dbname . ".log_noninventorydt_vw a left join " . $dbname . ".log_potermin b on a.nopo=b.nopo 
				where a.nopo='" . $param['nodok'] . "' and a.posting=1 group by notransaksi,b.termin";

				$res = fetchdata($str);
				$res = fetchData($sql);
				foreach ($res as $val):
					$isTermin = $val['termin'];
					$arrnotransaksitermin[$val['notransaksi']][$val['kelompokbarang']][$val['termin']] = $val['notransaksi'];
					$dttanggaltermin[$val['notransaksi']][$val['termin']] = $val['tanggal'];
					$dthartottermin[$val['notransaksi']][$val['kelompokbarang']][$val['termin']] = $val['rupiahtermin'];
					$dtkelompokbarangtermin[$val['notransaksi']][$val['termin']] = $val['kelompokbarang'];
					$dtkodebarangtermin[$val['notransaksi']][$val['termin']] = $val['kodebarang'];
					$dtnoakuntermin[$val['notransaksi']][$val['termin']] = $val['noakun'];
					$dttermintermin[$val['notransaksi']][$val['termin']] = $val['termin'];
					$ketterm[$val['notransaksi']][$val['termin']] = $val['bayar'];
				endforeach;

				#= cek data sudah ada / belum
				// $str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!=''";
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' AND noinvoice IN (SELECT noinvoice FROM keu_tagihanht WHERE tipeinvoice='um' AND nopo='" . $param['nodok'] . "' and notransaksi!='')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']] = $bar['notransaksi'];
					$dtnotransaksiadatermin[$bar['notransaksi']][$bar['termin']] = $bar['termin'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
					// if($bar['noakun']=='1180301'){
					if (substr($bar['noakun'], 0, 5) == '11801') {
						$dtnilaiuangmuka[$bar['notransaksi']] += $bar['nilai'];
					}
				}


				$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo,notransaksireferensi from " . $dbname . ".log_retursuppliernoninventorydt_vw
				where notransaksireferensi  in ('" . implode("','", $arrnotransaksi) . "') and posting=1  group by notransaksireferensi";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dthartotretur[$bar['notransaksireferensi']] = $bar['hartot'];
				}


				$str = "select * from " . $dbname . ".keu_tagihanht where nopo='" . $param['nodok'] . "' and tipeinvoice='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoakunuangmuka = $bar['noakun'];
				}

				if ($isTermin != 0) { # Cek apakah penginputan ada termin
					foreach ($arrnotransaksitermin as $dtnotransaksi => $valn) {
						foreach ($valn as $klmpkbarang => $valk):
							foreach ($valk as $termin => $valt):

								if ($ketterm[$dtnotransaksi][$termin] == '1') {
									continue;
								}

								@$no += 1;
								$stream .= "<tr class=rowcontent>";
								$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
								$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
								$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggaltermin[$dtnotransaksi][$termin]) . "</td>";
								$stream .= "<td style=cursor:pointer align=center id=termindt" . $no . ">" . $dttermintermin[$dtnotransaksi][$termin] . "</td>";

								$stream .= "<td style=cursor:pointer align=right id=nilaitransaksidt" . $no . ">" . @number_format($dthartottermin[$dtnotransaksi][$klmpkbarang][$termin], 2) . "</td>";
								$stream .= "<td style=cursor:pointer align=right id=nilaireturdt" . $no . ">" . @number_format($dthartotreturtermin[$dtnotransaksi][$termin], 2) . "</td>";
								$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format(@$dtnilaiuangmukatermin[$dtnotransaksi][$termin], 2) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";

								$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($dthartottermin[$dtnotransaksi][$klmpkbarang][$termin] - $dthartotreturtermin[$dtnotransaksi][$termin], 2) . "</td>";

								#= lainnya
								$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";

								$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
								$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakuntermin[$dtnotransaksi][$termin] . "</td>";
								$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . @$dtnoakunuangmuka . "</td>";
								// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
								$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
								$stream .= "<td style=cursor:pointer align=right id=kelompokbarangdt" . $no . ">" . $dtkelompokbarang[$dtnotransaksi] . "</td>";
								$stream .= "<td style=cursor:pointer align=right id=kodekegiatandt" . $no . "></td>";
								$stream .= "<td style=cursor:pointer align=right id=kodeassetdt" . $no . "></td>";
								$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
								$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">" . $namabarang[$dtkodebarang[$dtnotransaksi]] . ", Termin ke " . $termin . "</td>";

								if (@$dtnotransaksiadatermin[$dtnotransaksi][$termin] == @$termin or (@$dthartottermin[$dtnotransaksi][$klmpkbarang][$termin] - @$dthartotreturtermin[$dtnotransaksi][$termin] == '0')) {
									$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . " <input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
								} else {
									$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
								}
								$stream .= "</tr>";
							endforeach;
						endforeach;
					}
				} else {
					foreach ($arrnotransaksi as $dtnotransaksi => $valn) {
						foreach ($valn as $klmpkbarang => $valk):
							@$no += 1;
							$stream .= "<tr class=rowcontent>";
							$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
							$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
							$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
							$stream .= "<td style=cursor:pointer align=center id=termindt" . $no . ">" . $dttermin[$dtnotransaksi] . "</td>";

							$stream .= "<td style=cursor:pointer align=right id=nilaitransaksidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi][$klmpkbarang], 2) . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=nilaireturdt" . $no . ">" . @number_format($dthartotretur[$dtnotransaksi], 2) . "</td>";
							$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format(@$dtnilaiuangmuka[$param['nodok']], 2) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";

							$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi][$klmpkbarang] - $dthartotretur[$dtnotransaksi], 2) . "</td>";

							#= lainnya
							$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";

							$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun[$dtnotransaksi] . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . @$dtnoakunuangmuka . "</td>";
							// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
							$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
							$stream .= "<td style=cursor:pointer align=right id=kelompokbarangdt" . $no . ">" . $dtkelompokbarang[$dtnotransaksi] . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=kodekegiatandt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer align=right id=kodeassetdt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">" . $namabarang[$dtkodebarang[$dtnotransaksi]] . "</td>";
							if (@$dtnotransaksiada[$dtnotransaksi] == @$dtnotransaksi or (@$dthartot[$dtnotransaksi][$klmpkbarang] - @$dthartotretur[$dtnotransaksi] == '0')) {
								$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . " <input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
							} else {
								$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
							}
							$stream .= "</tr>";
						endforeach;
					}
				}

				break;

			case 'pocbd':
				if ($param['tipeinvoice'] == 'pocbd') {
					$str = "select nopo as notransaksi,(subtotal-nilaidiskon) as hartot,tanggal,left(kodebarang,3) as kelompokbarang,kodebarang from " . $dbname . ".log_po_vw where nopo='" . $param['nodok'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						// $strso = "select SUM(harga*jumlah) as total from " . $dbname . ".log_somaterial where nopo='" . $param['nodok'] . "' order by nopo asc";
						// $resso = fetchdata($strso);
						// foreach ($resso as $val) {
						// 	$rupso = $val['total'];
						// }

						$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
						$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
						$dthartot[$bar['notransaksi']] = $bar['hartot'] + $rupso;
						$dtkelompokbarang[$bar['notransaksi']] = $bar['kelompokbarang'];
						$dtkodebarang[$bar['notransaksi']] = $bar['kodebarang'];
						// $dtnoakun[$bar['notransaksi']]=$bar['noakun'];
						$dttermin[$bar['notransaksi']] = $bar['termin'];
					}
				}

				#= ambil namabarang
				$str = "select * from " . $dbname . ".log_5masterbarang 
					where kodebarang in ('" . implode("','", $dtkodebarang) . "')";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$namabarang[$bar['kodebarang']] = $bar['namabarang'];
				}


				#= ambil data aruskas dan coa
				$str = "select * from " . $dbname . ".log_5klbarang 
					where kode in ('" . implode("','", $dtkelompokbarang) . "')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoakun = $bar['noakun'];
				}

				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun='" . $dtnoakun . "' and noaruskas like '1%'";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas = $bar['noaruskas'];
				}

				#= cek data sudah ada / belum
				// $str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!=''";
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' AND noinvoice IN (SELECT noinvoice FROM keu_tagihanht WHERE tipeinvoice='um' AND nopo='" . $param['nodok'] . "' and notransaksi!='')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']] = $bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
					// if($bar['noakun']=='1180101'){
					if (substr($bar['noakun'], 0, 3) == '118') {
						$dtnilaiuangmuka[$bar['notransaksi']] += $bar['nilai'];
					}
				}

				#= data retur
				#= bentuk data retur
				$str = "select sum(hargasatuan*jumlah) as hartot,tanggal,nopo,notransaksireferensi from " . $dbname . ".log_transaksi_vw
				where notransaksireferensi  in ('" . implode("','", $arrnotransaksi) . "') and post=1  and tipetransaksi='6' group by notransaksireferensi";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dthartotretur[$bar['notransaksireferensi']] = $bar['hartot'];
				}


				$str = "select * from " . $dbname . ".keu_tagihanht where nopo='" . $param['nodok'] . "' and tipeinvoice='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					// @$dtuangmukadpp+=$bar['nilaidpp'];
					// @$dtuangmuka+=$bar['nilaiinvoice'];
					$dtnoakunuangmuka = $bar['noakun'];
				}


				foreach ($arrnotransaksi as $dtnotransaksi) {
					@$no += 1;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
					$stream .= "<td style=cursor:pointer align=center id=termindt" . $no . ">" . $dttermin[$dtnotransaksi] . "</td>";

					$stream .= "<td style=cursor:pointer align=right id=nilaitransaksidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=nilaireturdt" . $no . ">" . @number_format($dthartotretur[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format($dtnilaiuangmuka[$dtnotransaksi], 2) . "   onkeypress=return angka_doang(event); style=width:100px; /></td>";
					$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($dthartot[$dtnotransaksi] - $dthartotretur[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:100px; /></td>";

					$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . $dtnoakunuangmuka . "</td>";
					// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
					$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
					$stream .= "<td style=cursor:pointer align=right id=kelompokbarangdt" . $no . ">" . $dtkelompokbarang[$dtnotransaksi] . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=kodekegiatandt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer align=right id=kodeassetdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">" . $namabarang[$dtkodebarang[$dtnotransaksi]] . "</td>";

					if (@$dtnotransaksiada[$dtnotransaksi] == @$dtnotransaksi or (@$dthartot[$dtnotransaksi] - @$dthartotretur[$dtnotransaksi] == '0')) {
						$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . " <input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					} else {
						$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					}
					$stream .= "</tr>";
				}
				break;



			case 'bas':

				#= kolom noakun adalah tipe ba

				$str = "select sum(jumlah) as jumlah,tanggal,noakun,notransaksi,kodekegiatan from " . $dbname . ".log_bakontrakjasa 
				where nokontrak='" . $param['nodok'] . "' and status=1 group by notransaksi,noakun order by tanggal asc";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnobaspk[$bar['notransaksi']] = $bar['notransaksi'];
					$arrtipe[$bar['noakun']] = $bar['noakun'];
					$arrnoakun[substr($bar['kodekegiatan'], 0, 7)] = substr($bar['kodekegiatan'], 0, 7);
					$dtnoakun[$bar['notransaksi']][$bar['noakun']] = substr($bar['kodekegiatan'], 0, 7);
					$dtkodekegiatan[$bar['notransaksi']][$bar['noakun']] = $bar['kodekegiatan'];
					$lstipe[$bar['notransaksi']][$bar['noakun']] = $bar['noakun'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					// $dttermin[$bar['notransaksi']][$bar['noakun']]=$bar['termin'];
					$dtjumlahrealisasi[$bar['notransaksi']][$bar['noakun']] += $bar['jumlah'];
					// $dtkelompokbarang[$bar['notransaksi']]=$bar['kelompokbarang'];	
				}


				// print_r($lskodekegiatan);
				#= cari noaruskas
				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $arrnoakun) . "') and noaruskas like '1%'";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas[$bar['noakun']] = $bar['noaruskas'];
				}

				#= cek data sudah ada / belum
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!=''";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']][$bar['noakun']] = $bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
					if ($dtnoakunuangmuka == $bar['noakun']) {
						$bar['nilai'] = $bar['nilai'] * -1;
					}
					@$dtnilaiinvoice[$bar['notransaksi']][$bar['keterangan']] += $bar['nilai'];
					// $dtnilaiuangmuka[$bar['notransaksi']][$bar['noakun']]=$bar['nilai'];
				}


				#== Abdul ==#
				#== Untuk benerin data BA Service yang masih bisa di tarik ==#
				if ($_SESSION['standard']['username'] == 'tim.owl3') {
					echo "<pre>";
					// print_r($dtnilaiuangmuka);
					print_r($dtnilaiinvoice);
				}

				foreach ($arrnobaspk as $dtnobaspk) {
					foreach ($arrtipe as $dttipe) {
						if ($lstipe[$dtnobaspk][$dttipe] != '') {
							@$no += 1;
							// $dtnilainotransaksiada=0;
							$stream .= "<tr class=rowcontent>";
							$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
							$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnobaspk . "</td>";
							$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnobaspk]) . "</td>";
							$stream .= "<td style=cursor:pointer align=center id=termindt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=nilaitransaksidt" . $no . ">" . number_format($dtjumlahrealisasi[$dtnobaspk][$dttipe], 2) . "</td>";
							$stream .= "<td style=cursor:pointer id=nilaireturdt" . $no . " align=right>0</td>";
							$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\"  onblur=getnilaitotaldt(" . $no . ") onkeypress=return angka_doang(event); style=width:70px; /></td>";
							$nilaisisa[$dtnobaspk][$dttipe] = $dtjumlahrealisasi[$dtnobaspk][$dttipe] - $dtnilaiinvoice[$dtnobaspk][$dttipe];
							// echo "<pre>";
							// print_r($dtnilaiinvoice);
							$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($nilaisisa[$dtnobaspk][$dttipe], 2) . "</td>";
							$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";
							$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas[$dtnoakun[$dtnobaspk][$dttipe]] . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun[$dtnobaspk][$dttipe] . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . $dtnoakunuangmuka . "</td>";
							// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
							$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
							$stream .= "<td style=cursor:pointer id=kelompokbarangdt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=kodekegiatandt" . $no . ">" . $dtkodekegiatan[$dtnobaspk][$dttipe] . "</td>";
							$stream .= "<td style=cursor:pointer id=kodeassetdt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">" . $dttipe . "</td>";

							// if($dtnotransaksiada[$dtnobaspk][$dtnoakun[$dttipe]]==$dtnobaspk and $nilaisisa[$dtnobaspk][$dttipe]==0){
							if ($dtnotransaksiada[$dtnobaspk][$dtnoakun[$dtnobaspk][$dttipe]] == $dtnobaspk and $nilaisisa[$dtnobaspk][$dttipe] == 0) {
								$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnobaspk] . "
												<input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . ">
											</td>";
							} else {
								$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
							}
							$stream .= "</tr>";
						}
					}
				}
				break;




			case 'k':
				#ambil tipe spk
				$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $param['nodok'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$jenisspk = $bar['jenis'];
					$tipeproj = substr($bar['divisi'], 3, 2);
					$arrnospk[$bar['notransaksi']] = $bar['notransaksi'];
				}

				$optCapex = makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,akunak', "kodetipe='" . $tipeproj . "'");
				$akunak = $optCapex[$tipeproj];

				// $str="select sum(jumlahrealisasi) as jumlahrealisasi,keterangan,kodekegiatan,tanggal,termin,notransaksi from ".$dbname.".log_baspk 
				// where notransaksi='".$param['nodok']."' and statusjurnal=1 group by keterangan,kodekegiatan order by tanggal asc";

				# Jangan Per Kegiatan
				# Diskusi dengan Mas Ari
				$str = "select sum(jumlahrealisasi) as jumlahrealisasi,keterangan,kodekegiatan,tanggal,termin,notransaksi from " . $dbname . ".log_baspk 
				where notransaksi='" . $param['nodok'] . "' and statusjurnal=1 group by keterangan order by tanggal asc";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					if ($jenisspk == 'PROJECT') {
						$bar['kodekegiatan'] = $akunak . "01";
					}

					$arrnobaspk[$bar['keterangan']] = $bar['keterangan'];
					$arrkodekegiatan[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					// $arrnoakun[substr($bar['kodekegiatan'], 0, 7)] = substr($bar['kodekegiatan'], 0, 7);
					# Baca noakun dari setup_kegiatan
					// $arrnoakun[substr($bar['kodekegiatan'], 0, 7)] = makeOption($dbname, "setup_kegiatan", "kodekegiatan,noakun", "kodekegiatan='{$bar['kodekegiatan']}'")[$bar['kodekegiatan']];

					// $dtnoakun[$bar['kodekegiatan']] = makeOption($dbname, "setup_kegiatan", "kodekegiatan,noakun", "kodekegiatan='{$bar['kodekegiatan']}'")[$bar['kodekegiatan']];

					# Ganti pake substr saja
					# Karena bisa jadi pengambilan dari vhc_kegiatan contoh SPK :166/SPK/PPP/PPPE/X/2025
					# Jadi tidak muncul noakunnya, dan buat looping lemot jika makeoption
					$arrnoakun[substr($bar['kodekegiatan'], 0, 7)] = substr($bar['kodekegiatan'], 0, 7);
					$dtnoakun[$bar['kodekegiatan']] = substr($bar['kodekegiatan'], 0, 7);
					# End

					$lskodekegiatan[$bar['keterangan']][$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$dttanggal[$bar['keterangan']] = $bar['tanggal'];
					$dtperiode[$bar['notransaksi']][$bar['termin']] = substr($bar['tanggal'], 0, 7);
					$dttermin[$bar['keterangan']][$bar['kodekegiatan']] = $bar['termin'];
					$dtjumlahrealisasi[$bar['keterangan']][$bar['kodekegiatan']] += $bar['jumlahrealisasi'];
					// $dtkelompokbarang[$bar['notransaksi']]=$bar['kelompokbarang'];

					# Tanggal Awal Termin 1
					# Untuk BAPP
					$dttanggaltermin[$bar['notransaksi']][$bar['termin']] = $bar['tanggal'];
					# Jika pake BAPP nanti tidak dapat per termin
					// $dttanggaltermin[$bar['keterangan']][$bar['termin']] = $bar['tanggal'];
				}

				#= Pemakaian Material
				foreach ($dttanggaltermin as $nospktermin => $valtermin) {
					foreach ($valtermin as $termintt => $value) {
						$terminminus = $termintt - 1;
						// $terminplus = $termintt+1; # Jika Pakai Case 1

						#==================================================================================#
						# CASE
						#==================================================================================#
						# Case 1
						# Jika per range tanggal
						// $where = " AND (tanggal >= '".$dttanggaltermin[$nospktermin][$termintt]."' AND tanggal < '".$dttanggaltermin[$nospktermin][$terminplus]."')";
						// if($dttanggaltermin[$nospktermin][$terminplus] == '') { # Jika tidak ada termin selanjutnya, maka tanggal dari dan sampai adalah tanggal yang sama
						// 	$where = " AND (tanggal >= '".$dttanggaltermin[$nospktermin][$termintt]."' AND tanggal <= '".$dttanggaltermin[$nospktermin][$termintt]."')";
						// }

						# Case 2
						# Jika per range tanggal per bapp
						// $where = " AND tanggal <= '".$dttanggaltermin[$nospktermin][$termintt]."' AND tanggal LIKE '%".$dtperiode[$nospktermin][$termintt]."%'";
						// if($dttanggaltermin[$nospktermin][$terminplus] != '') { # Jika tidak ada termin selanjutnya, maka tanggal dari dan sampai adalah tanggal yang sama
						$where = " AND (tanggal > '" . $dttanggaltermin[$nospktermin][$terminminus] . "' AND tanggal <= '" . $dttanggaltermin[$nospktermin][$termintt] . "') AND tanggal LIKE '%" . $dtperiode[$nospktermin][$termintt] . "%'";
						// }
						#==================================================================================#
						# END CASE
						#==================================================================================#

						$sql = selectQuery($dbname, "log_transaksi_vw_detail", "sum(hargarata*jumlah) as hargaratadetail,kodeblok", "post='1' AND kodeblok IN ('" . implode("','", $arrnospk) . "') " . $where . "");
						// echo $sql.";<br/>";
						// echo $dttanggaltermin[$nospktermin][$terminplus].";<br/>";
						$res = fetchData($sql);
						foreach ($res as $bar) {
							$hargadetail[$dttanggaltermin[$nospktermin][$termintt]] = ($bar['hargaratadetail'] == '' ? 0 : $bar['hargaratadetail']);
						}

						# Jika pakai case 2
						$terminplus = $termintt + 1;
					}
				}
				# End	

				// echo "<pre>";
				// print_r($dttanggaltermin);
				// echo "<pre>";
				// print_r($hargadetail);

				#= cari noaruskas
				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $arrnoakun) . "') and noaruskas like '1%'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas[$bar['noakun']] = $bar['noaruskas'];
				}

				#= cek data sudah ada / belum
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!=''";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']][$bar['noakun']][$bar['termin']] = $bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
					$dtnilainotransaksiada[$bar['notransaksi']][$bar['noakun']][$bar['termin']] += $bar['nilai'];
				}
				// echo"<pre>";
				// print_r($dtjumlahrealisasi);
				// echo"<pre>";
				// print_r($dtnilainotransaksiada);
				// echo"<pre>";
				// print_r($hargadetail);
				foreach ($arrnobaspk as $dtnobaspk) {
					foreach ($arrkodekegiatan as $dtkodekegiatan) {
						if ($lskodekegiatan[$dtnobaspk][$dtkodekegiatan] != '') {
							@$no += 1;
							// $dtnilainotransaksiada=0;
							$stream .= "<tr class=rowcontent>";
							$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
							$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnobaspk . "</td>";
							$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnobaspk]) . "</td>";
							$stream .= "<td style=cursor:pointer align=center id=termindt" . $no . ">" . $dttermin[$dtnobaspk][$dtkodekegiatan] . "</td>";

							$stream .= "<td style=cursor:pointer id=nilaitransaksidt" . $no . ">" . number_format($dtjumlahrealisasi[$dtnobaspk][$dtkodekegiatan], 2) . "</td>";
							$stream .= "<td style=cursor:pointer id=nilaireturdt" . $no . ">0</td>";
							$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format($dtnilaiuangmuka[$dtnobaspk][$dtkodekegiatan], 2) . " onblur=getnilaitotaldt(" . $no . ") onkeypress=return angka_doang(event); style=width:70px; /></td>";

							#= untuk data awal yang sudah pernah di-insert, karna detail sebelumnya tidak insert nobaspk
							// $nilaisisa[$dtnobaspk][$dtkodekegiatan]=$dtjumlahrealisasi[$dtnobaspk][$dtkodekegiatan]-$dtnilainotransaksiada[$dtnobaspk][substr($dtkodekegiatan,0,7)][$dttermin[$dtnobaspk][$dtkodekegiatan]];
							$nilaisisa[$dtnobaspk][$dtkodekegiatan] = round($dtjumlahrealisasi[$dtnobaspk][$dtkodekegiatan], 2) - round($dtnilainotransaksiada[$dtnobaspk][substr($dtkodekegiatan, 0, 7)][$dttermin[$dtnobaspk][$dtkodekegiatan]], 2) - round($hargadetail[$dttanggal[$dtnobaspk]], 2);
							if ($_SESSION['standard']['username'] == 'tim.owl') {
								// exit("Warning: " . $dtjumlahrealisasi[$dtnobaspk][$dtkodekegiatan] . " - " . $dtnilainotransaksiada[$dtnobaspk][substr($dtkodekegiatan, 0, 7)][$dttermin[$dtnobaspk][$dtkodekegiatan]] . " - " . $hargadetail[$dttanggal[$dtnobaspk]]);
							}


							// echo $dtjumlahrealisasi[$dtnobaspk][$dtkodekegiatan]."<br/>";
							// echo $dtnilainotransaksiada[$dtnobaspk][substr($dtkodekegiatan,0,7)][$dttermin[$dtnobaspk][$dtkodekegiatan]]."(-)<br/>";
							// echo $hargadetail[$dttanggal[$dtnobaspk]]."(-)<br/>";

							// echo "hasil : ".($dtjumlahrealisasi[$dtnobaspk][$dtkodekegiatan]-($dtnilainotransaksiada[$dtnobaspk][substr($dtkodekegiatan,0,7)][$dttermin[$dtnobaspk][$dtkodekegiatan]]+$hargadetail[$dttanggal[$dtnobaspk]]))."<br/><br/>";

							// echo "hasil pengurang : ".$nilaisisa[$dtnobaspk][$dtkodekegiatan]."<br/><br/>";

							$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($nilaisisa[$dtnobaspk][$dtkodekegiatan], 2) . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($hargadetail[$dttanggal[$dtnobaspk]], 2) . "</td>";
							$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";
							$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas[$dtnoakun[$dtkodekegiatan]] . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun[$dtkodekegiatan] . "</td>";
							$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . $dtnoakunuangmuka . "</td>";
							// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
							$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
							$stream .= "<td style=cursor:pointer id=kelompokbarangdt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=kodekegiatandt" . $no . ">" . $dtkodekegiatan . "</td>";
							$stream .= "<td style=cursor:pointer id=kodeassetdt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
							$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . "></td>";

							if ($dtnotransaksiada[$dtnobaspk][substr($dtkodekegiatan, 0, 7)][$dttermin[$dtnobaspk][$dtkodekegiatan]] == $dtnobaspk and number_format($nilaisisa[$dtnobaspk][$dtkodekegiatan], 2) == 0) {
								$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnobaspk] . "
												<input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . ">
											</td>";
							} else {
								$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
							}
							$stream .= "</tr>";
						}
					}
				}
				break;


			case 'ffb':

				$str = "select * from " . $dbname . ".pmn_tbs where notransaksi='" . $param['nodok'] . "' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
					$dttanggal[$bar['notransaksi']] = $bar['tanggal'];
					$dtjumrpadjust[$bar['notransaksi']] += $bar['jumrpadjust'];
					$dtnoakun = $bar['noakunhutang'];
					$dttipetbs = $bar['tipetbs'];
				}

				#= cari noaruskas
				// $str="select * from ".$dbname.".keu_5aruskas_detail where noakun='".$dtnoakun."' and noaruskas in (select noaruskas  from ".$dbname.".keu_5aruskas where nama_aruskas like '%tbs%')";
				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun='" . $dtnoakun . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas = $bar['noaruskas'];
				}

				#= cek data sudah ada / belum
				$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodok'] . "' and notransaksi!=''";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['notransaksi']] = $bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']] = $bar['noinvoice'];
				}

				// norek
				$str1 = "select supplierid, idbank, rekening, an from " . $dbname . ".log_5rekbank order by def"; // diorder kek gini biar yang def kepilih, dibikin gini untuk antisipasi ga ada def-nya
				$res1 = fetchData($str1);
				foreach ($res1 as $bar1) {
					$norekening[$bar1['supplierid']] = $bar1['rekening'];
					$atasnama[$bar1['supplierid']] = $bar1['an'];
				}

				$str = "select * from " . $dbname . ".keu_tagihanht where nopo='" . $param['nodok'] . "' and tipeinvoice='um' ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoakunuangmuka = $bar['noakun'];
				}


				foreach ($arrnotransaksi as $dtnotransaksi) {
					@$no += 1;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal[$dtnotransaksi]) . "</td>";
					$stream .= "<td style=cursor:pointer id=termindt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=nilaitransaksidt" . $no . ">" . number_format($dtjumrpadjust[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer id=nilaireturdt" . $no . " align=right>0</td>";



					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . "  onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format(0) . "   onkeypress=return angka_doang(event); style=width:100px; /></td>";

					$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . number_format($dtjumrpadjust[$dtnotransaksi], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:100px; /></td>";
					$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun . "</td>";
					// $stream.="<td style=cursor:pointer align=left>".$nmakun[$dtnoakun]."</td>";

					$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . @$dtnoakunuangmuka . "</td>";
					$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";

					$stream .= "<td style=cursor:pointer id=kelompokbarangdt" . $no . ">400</td>";
					$stream .= "<td style=cursor:pointer id=kodekegiatandt" . $no . ">" . $dtkodekegiatan . "</td>";
					// $stream.="<td style=cursor:pointer>".$nmkegiatan[$dtkodekegiatan]."</td>";
					$stream .= "<td style=cursor:pointer id=kodeassetdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">Pembayaran TBS Petani via KUD a/n " . $nmsupplier[$dtsupplier] . " NoRek: " . $norekening[$dtsupplier] . "</td>";
					if ($dtnotransaksiada[$dtnotransaksi] == $dtnotransaksi) {
						$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnotransaksi] . "
										<input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . ">
									</td>";
					} else {
						$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					}
					$stream .= "</tr>";
				}

				break;

			case 'ffba':
			case 'ffbe':

				// $arrtipesupplier=array("KUD"=>"TBSKUD","40000002"=>"TBS","4000000001"=>"SALESCPO","4000000002"=>"SALESPK");
				// echo"<pre>";
				// print_r($param);
				// if ($param['tipeinvoice'] == 'ffb') {
				// 	$str = "select sum(totalrp) as totalrp,notransaksi,tanggal,supplier from " . $dbname . ".kebun_tbskud where notransaksi='" . $param['nodok'] . "' ";
				// }
				if ($param['tipeinvoice'] == 'ffba') {
					$str = "select sum(totalrp) as totalrp,notransaksi,tanggal,supplier  from " . $dbname . ".kebun_tbsafiliasi where notransaksi='" . $param['nodok'] . "' ";
				}
				if ($param['tipeinvoice'] == 'ffbe') {
					$str = "select sum(totalrp) as totalrp,notransaksi,tanggal,supplier  from " . $dbname . ".kebun_tbsexternal where notransaksi='" . $param['nodok'] . "' ";
				}
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksi = $bar['notransaksi'];
					$dttanggal = $bar['tanggal'];
					$dtjumrpadjust = floor($bar['totalrp']);
					$dtsupplier = $bar['supplier'];
					$explnotran = explode('/', $bar['notransaksi']);
					$tipesupplier = 'SUPPLIER' . $explnotran[1];
				}

				#= ambil noakun hutang
				$str = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $dtsupplier . "' and tipe='" . $tipesupplier . "' ";
				$res = fetchdata($str);

				if (count($res) <= 0) {
					exit("<label hidden>Warning :</label> Nama Supplier <b>{$nmsupplier[$dtsupplier]}</b>, belum ada Tipe <b>{$tipesupplier}</b>, lakukan SETUP di Menu <b style=color:red>Setup > Kelompok Supplier / Kontraktor</b>");
				}

				foreach ($res as $bar) {
					$arrnoakun[$bar['noakun']] = $bar['noakun'];
					$dtnilai[$bar['noakun']] = $dtjumrpadjust;
				}

				#= cari noaruskas untuk akun hutang
				$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $arrnoakun) . "') and noaruskas like '11201%'";
				if ($param['tipeinvoice'] == 'ffba') { // vienny: arus kas harusnya hub subsidiary yg berkaitan dgn TBS afiliasi arus kas nya adalah hub subsiadiary
					$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $arrnoakun) . "') and noaruskas like '141%'";
				}
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnoaruskas[$bar['noakun']] = $bar['noaruskas'];
				}

				#= ambil noakun ppn dan aruskas
				$str = "select * from " . $dbname . ".keu_5jenistagihan_akunpajak where kode='" . $param['tipeinvoice'] . "' ";
				$res = fetchdata($str);

				if (count($res) <= 0) {
					exit("<label hidden>Warning :</label> Tipe Invoice <b>{$param['tipeinvoice']}</b> belum ada, lakukan SETUP di Menu <b style=color:red>Keuangan > Setup > Jenis Tagihan (Preview > Input Pph dan PPn)</b>");
				}

				foreach ($res as $bar) {

					#= cek apakah terdaftar di log_5pphsup
					$strpajak = "select *  from " . $dbname . ".log_5pphsup  where supplierid='" . $dtsupplier . "' and noakun='" . $bar['noakun'] . "' ";
					$respajak = fetchdata($strpajak);
					foreach ($respajak as $barpajak) {
						$arrnoakun[$bar['noakun']] = $bar['noakun'];
						$dtnoaruskas[$bar['noakun']] = $bar['noaruskas'];
						if (tanggalsystemn($param['tanggalinvoice']) < '2022-04-01') {
							$dtnilai[$bar['noakun']] = floor($dtjumrpadjust * 10 / 100);
						} else {
							$dtnilai[$bar['noakun']] = floor($dtjumrpadjust * $barpajak['tarif'] / 100);
						}
					}
					// $arrnoakun[$bar['noakun']]=$bar['noakun'];
					// $dtnilai[$bar['noakun']]=floor($dtjumrpadjust*0.1);
					// $dtnoaruskas[$bar['noakun']]=$bar['noaruskas'];
				}

				#= cek data sudah ada / belum
				$str = "select * from " . $dbname . ".keu_tagihandt where notransaksi='" . $param['nodok'] . "' and notransaksi!=''";
				// echo $str;
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$dtnotransaksiada[$bar['noakun']] = $bar['noakun'];
					$dtnoinvoice[$bar['noakun']] = $bar['noinvoice'];
				}

				// norek
				$str1 = "select supplierid, idbank, rekening, an from " . $dbname . ".log_5rekbank order by def"; // diorder kek gini biar yang def kepilih, dibikin gini untuk antisipasi ga ada def-nya
				$res1 = fetchData($str1);
				foreach ($res1 as $bar1) {
					$norekening[$bar1['supplierid']] = $bar1['rekening'];
					$atasnama[$bar1['supplierid']] = $bar1['an'];
				}

				// echo "<pre>";
				// print_r($arrnoakun);

				foreach ($arrnoakun as $dtnoakun) {
					@$no += 1;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td style=cursor:pointer align=center>" . $no . "</td>";
					$stream .= "<td style=cursor:pointer id=notransaksidt" . $no . ">" . $dtnotransaksi . "</td>";
					$stream .= "<td style=cursor:pointer id=tanggaldt" . $no . ">" . tanggalnormal($dttanggal) . "</td>";
					$stream .= "<td style=cursor:pointer id=termindt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer></td>";
					$stream .= "<td style=cursor:pointer></td>";

					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilaiuangmukadt" . $no . " disabled onkeyup=\"z.numberFormat('nilaiuangmukadt" . $no . "',2);getnilaitotaldt(" . $no . ")\" value=" . number_format(0) . "   onkeypress=return angka_doang(event); style=width:70px; /></td>";
					$stream .= "<td style=cursor:pointer align=right id=nilaidt" . $no . ">" . @number_format($dtnilai[$dtnoakun], 2) . "</td>";
					$stream .= "<td style=cursor:pointer align=right><input type=text class=myinputtextnumber id=nilailaindt" . $no . " onkeyup=\"z.numberFormat('nilailaindt" . $no . "',2);\" value=0  onkeypress=return angka_doang(event); style=width:70px; /></td>";
					$stream .= "<td style=cursor:pointer align=right id=noaruskasdt" . $no . ">" . $dtnoaruskas[$dtnoakun] . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakundt" . $no . ">" . $dtnoakun . "</td>";
					$stream .= "<td style=cursor:pointer align=right id=noakunuangmukadt" . $no . ">" . $dtnoakunuangmuka . "</td>";
					// $stream.="<td style=cursor:pointer align=right id=noakunlaindt".$no.">".@$dtnoakunlain."</td>";
					$stream .= "<td><select id=noakunlaindt" . $no . "  style=\"width:154px;\">'" . $optnoakunlain . "'</select>";
					$stream .= "<td style=cursor:pointer id=kelompokbarangdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=kodekegiatandt" . $no . ">" . $dtkodekegiatan . "</td>";
					$stream .= "<td style=cursor:pointer id=kodeassetdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=reksupplierdt" . $no . "></td>";
					$stream .= "<td style=cursor:pointer id=keterangandatadt" . $no . ">Pembayaran TBS Petani via KUD a/n " . $nmsupplier[$dtsupplier] . " NoRek: " . $norekening[$dtsupplier] . "</td>";
					if ($dtnotransaksiada[$dtnoakun] == $dtnoakun) {
						$stream .= "<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>" . $dtnoinvoice[$dtnoakun] . "
										<input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . ">
									</td>";
					} else {
						$stream .= "<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
					}
					$stream .= "</tr>";
				}

				break;



				/*
			case'sales':
			
				$arrtipesupplier=array("40000001"=>"SALESCPO","40000002"=>"SALESPK","4000000001"=>"SALESCPO","4000000002"=>"SALESPK");
			
				$str="select * from ".$dbname.".pmn_bastdt_vw where nokontrak='".$param['nodok']."' and sales=1";
				// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrnotransaksi[$bar['notransaksi']]=$bar['notransaksi'];
					$dtnokontrak[$bar['notransaksi']]=$bar['nokontrak'];
					$dttanggal[$bar['notransaksi']]=$bar['tanggal'];
					$dtkuantitas[$bar['notransaksi']]=$bar['kuantitas'];
					$dtkodebarang=$bar['kodebarang'];
				}
			
				#= data nilai kontrak
				$str="select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['nodok']."' ";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dthargasatuan=$bar['hargasatuan'];
					$dtkoderekanan=$bar['koderekanan'];
				}
				
				#= ambil data kodesupplier
				$str="select * from ".$dbname.".log_5supplier where kodecustomer='".$dtkoderekanan."' ";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtsupplierid=$bar['supplierid'];
				}
				
				#= ambil coa biaya dan aruskasnya
				$str="select * from ".$dbname.".log_5supkelompok where supplierid='".$dtsupplierid."' and tipe='".$arrtipesupplier[$dtkodebarang]."' ";
				// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtnoakunbiaya=$bar['noakunbiaya'];
				}
				
				#= cari noaruskas
				$str="select * from ".$dbname.".keu_5aruskas_detail where noakun='".$dtnoakunbiaya."' and noaruskas like '1%'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtnoaruskas=$bar['noaruskas'];
				}
				
				
				#= cek data sudah ada / belum
				$str="select * from ".$dbname.".keu_tagihandt where nopo='".$param['nodok']."' and notransaksi!=''";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtnotransaksiada[$bar['notransaksi']]=$bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']]=$bar['noinvoice'];
				}
					
					
				// echo"<pre>";	
				// print_r($dtnoakunbiaya);	
				
				foreach($arrnotransaksi as $dtnotransaksi){
					@$no+=1;
					
					$dtnilai[$dtnotransaksi]=$dtkuantitas[$dtnotransaksi]*$dthargasatuan;			
					
					$stream.="<tr class=rowcontent>";	
						$stream.="<td style=cursor:pointer align=center>".$no."</td>";
						$stream.="<td style=cursor:pointer id=notransaksidt".$no.">".$dtnotransaksi."</td>";
						$stream.="<td style=cursor:pointer>".tanggalnormal($dttanggal[$dtnotransaksi])."</td>";
						$stream.="<td style=cursor:pointer></td>";
						$stream.="<td style=cursor:pointer></td>";
						$stream.="<td style=cursor:pointer align=right id=nilaidt".$no.">".@number_format($dtnilai[$dtnotransaksi],2)."</td>";
						$stream.="<td style=cursor:pointer align=right id=noaruskasdt".$no.">".$dtnoaruskas."</td>";
						$stream.="<td style=cursor:pointer align=right id=noakundt".$no.">".$dtnoakunbiaya."</td>";
						$stream.="<td style=cursor:pointer id=kelompokbarangdt".$no."></td>";
						$stream.="<td style=cursor:pointer id=kodekegiatandt".$no.">".$dtkodekegiatan."</td>";
						$stream.="<td style=cursor:pointer id=kodeassetdt".$no."></td>";
						$stream.="<td style=cursor:pointer id=keterangandatadt".$no."></td>";
						if($dtnotransaksiada[$dtnotransaksi]==$dtnotransaksi){
							$stream.="<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>".$dtnoinvoice[$dtnotransaksi]."
										<input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt".$no.">
									</td>";
						}else{
							$stream.="<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt".$no."></td>";
						}
					$stream.="</tr>";
				}
			
			
			break;
			*/

				/*
			case'trsales':
				$str="select * from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$param['nodok']."' ";
					// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$dttransportir=$bar['transportir'];
					$dtnokontrak=$bar['nokontrak'];
					$dtkodebarang=$bar['kodebarang'];
					$dtharga=$bar['harga'];
					$dtqty=$bar['qty'];
					$dttoleransi=$bar['toleransi'];
				}
				
				$str="select * from ".$dbname.".pmn_bastdt_vw where nodo='".$param['nodok']."' ";
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrnotransaksi[$bar['notransaksi']]=$bar['notransaksi'];
					$dttanggalmulai[$bar['notransaksi']]=$bar['tanggalmulai'];
					if($bar['tipetransaksi']=='1' || $bar['tipetransaksi']=='3'){
						@$dtkuantitasmuat[$bar['notransaksi']]+=$bar['kuantitas'];
					}
					if($bar['tipetransaksi']=='2' || $bar['tipetransaksi']=='4'){
						@$dtkuantitasbongkar[$bar['notransaksi']]+=$bar['kuantitas'];
					}
				}
				
				$arrtipesupplier=array("40000001"=>"TRANSPORTIRCPO","40000002"=>"TRANSPORTIRPK","40000003"=>"TRANSPORTIRTBS","4000000001"=>"TRANSPORTIRCPO","4000000002"=>"TRANSPORTIRPK","4000000003"=>"TRANSPORTIRTBS");
				
				
				#= ambil coa biaya dan aruskasnya
				$str="select * from ".$dbname.".log_5supkelompok where supplierid='".$dttransportir."' and tipe='".$arrtipesupplier[$dtkodebarang]."' ";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtnoakunbiaya=$bar['noakunbiaya'];
				}
				
				#= cari noaruskas
				$str="select * from ".$dbname.".keu_5aruskas_detail where noakun='".$dtnoakunbiaya."' and noaruskas like '1%'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtnoaruskas=$bar['noaruskas'];
				}
				
				
				#= cek data sudah ada / belum
				$str="select * from ".$dbname.".keu_tagihandt where nopo='".$param['nodok']."' and notransaksi!=''";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtnotransaksiada[$bar['notransaksi']]=$bar['notransaksi'];
					$dtnoinvoice[$bar['notransaksi']]=$bar['noinvoice'];
				}
					
					
				// echo"<pre>";	
				// print_r($dtnoakunbiaya);	
				
				foreach($arrnotransaksi as $dtnotransaksi){
					@$no+=1;
					
					$dttoleransibast[$dtnotransaksi]=$dttoleransi/100*$dtkuantitasmuat[$dtnotransaksi];
					$dttoleransibastbatas[$dtnotransaksi]=$dtkuantitasmuat[$dtnotransaksi]-$dttoleransibast[$dtnotransaksi];
					#= buat perhitungan yang dipakai
					#= jika kg bongkar < muat-toleransi(kg batas toleransi), maka kg yang dibayar adalah kg bongkar
					#= jika kg bongkar > muat-toleransi(kg batas toleransi) , maka kg yang dibayar adalah kg muat
					if($dtkuantitasbongkar[$dtnotransaksi]<$dttoleransibastbatas[$dtnotransaksi]){
						$dtkuantitasdibayar[$dtnotransaksi]=$dtkuantitasbongkar[$dtnotransaksi];
					}
					if($dtkuantitasbongkar[$dtnotransaksi]>=$dttoleransibastbatas[$dtnotransaksi]){
						$dtkuantitasdibayar[$dtnotransaksi]=$dtkuantitasmuat[$dtnotransaksi];
					}
					
					$dtrupiahdibayar[$dtnotransaksi]=$dtkuantitasdibayar[$dtnotransaksi]*$dtharga;	
					
					$stream.="<tr class=rowcontent>";	
						$stream.="<td style=cursor:pointer align=center>".$no."</td>";
						$stream.="<td style=cursor:pointer id=notransaksidt".$no.">".$dtnotransaksi."</td>";
						$stream.="<td style=cursor:pointer>".tanggalnormal($dttanggalmulai[$dtnotransaksi])."</td>";
						$stream.="<td style=cursor:pointer></td>";
						$stream.="<td style=cursor:pointer></td>";
						$stream.="<td style=cursor:pointer align=right id=nilaidt".$no.">".@number_format($dtrupiahdibayar[$dtnotransaksi],2)."</td>";
						$stream.="<td style=cursor:pointer align=right id=noaruskasdt".$no.">".$dtnoaruskas."</td>";
						$stream.="<td style=cursor:pointer align=right id=noakundt".$no.">".$dtnoakunbiaya."</td>";
						$stream.="<td style=cursor:pointer id=kelompokbarangdt".$no."></td>";
						$stream.="<td style=cursor:pointer id=kodekegiatandt".$no."></td>";
						$stream.="<td style=cursor:pointer id=kodeassetdt".$no."></td>";
						$stream.="<td style=cursor:pointer id=keterangandatadt".$no."></td>";
						if($dtnotransaksiada[$dtnotransaksi]==$dtnotransaksi){
							$stream.="<td style=cursor:pointer align=right tittle='Sudah dibuat invoice'>".$dtnoinvoice[$dtnotransaksi]."
										<input hidden disabled title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt".$no.">
									</td>";
						}else{
							$stream.="<td style=cursor:pointer align=right><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt".$no."></td>";
						}
					$stream.="</tr>";
				}
				*/


				break;



			default:
				echo "Tipe invoice " . $param['tipeinvoice'] . " belum dicoding findnodokdetail";
				break;
		}


		$stream .= "<tr class=rowcontent>";
		$stream .= "<td style=cursor:pointer align=right colspan=20><button class=mybutton onclick=prosesnodok('" . $param['tipeinvoice'] . "','" . $param['nodok'] . "','" . $no . "','" . $dtsisadppum . "','" . $dtsisappnum . "')>" . $_SESSION['lang']['proses'] . "</button></td>";
		$stream .= "</tr>";

		// $stream.="</fieldset>";  	
		$stream .= "<table></div>";

		echo $stream;

		break;




	case 'prosesnodok':


		#= daftar akun pajak
		$str = "select noakun from " . $dbname . ".keu_5jenistagihan_akunpajak";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnoakunpajak[$bar['noakun']] = $bar['noakun'];
		}


		// echo"<pre>";
		// print_r($param);
		// exit("Error:A");
		$matauang = 'IDR';
		$kurs = 1;

		try {
			$owlPDO->beginTransaction();

			#= buat default nilai
			$nilaiinvoice = $nilaipph = $nilaidpp = $nilaippn = 0;

			#= case disini langsung insert ke table keu_tagihandt
			#= pengecualian untuk retur yang sudah lunas

			if ($param['tipeinvoice'] == 'rtg' || $param['tipeinvoice'] == 'rtn') {
				for ($i = 1; $i <= $param['maxrow']; $i++) {
					if (@$param['notransaksidt'][$i] != '') {
						$keterangandatadt = $param['keterangandatadt'][$i];
						$tanggaldt = $param['tanggaldt'][$i];
						if ($param['noakundt'][$i] == '' || $param['noaruskasdt'][$i] == '') {
							throw new PDOException("No. Akun atau No. Aruskas masih kosong, Silahkan hubungi Administrator untuk melakukan setup aruskas dan nomor akun");
						}
						$no += 1;
						$param['nilaidt'][$i] = str_replace(',', '', $param['nilaidt'][$i]);
						$param['nilaiuangmukadt'][$i] = str_replace(',', '', $param['nilaiuangmukadt'][$i]);
						$param['nilailaindt'][$i] = str_replace(',', '', $param['nilailaindt'][$i]);
						// nilai biaya ditambahkan nilau uang muka  karna dirumus tampilan dikurangi
						#= case insert keu_tagihandt
						$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,kodeasset,nilai,noaruskas,nourut,notransaksi,kelompokbarang,nopo,pajak,kodeblok,termin,keterangan,kodekegiatan)
						values('','" . $param['noakundt'][$i] . "','" . $param['kodeassetdt'][$i] . "','" . ($param['nilaidt'][$i]) . "','" . $param['noaruskasdt'][$i] . "','" . $no . "','" . $param['notransaksidt'][$i] . "','" . $param['kelompokbarangdt'][$i] . "','" . $param['nodokdt'] . "','','" . @$param['kodeblokdt'] . "','" . $param['termindt'][$i] . "','" . $param['keterangandatadt'][$i] . "','" . $param['kodekegiatandt'][$i] . "')";
						$owlPDO->exec($str);

						if ($param['nilailaindt'][$i] != 0) {
							$no++;
							$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,kodeasset,nilai,noaruskas,nourut, notransaksi,kelompokbarang,nopo,pajak,kodeblok,termin,keterangan,kodekegiatan)
							values('','" . $param['noakunlaindt'][$i] . "','" . $param['kodeassetdt'][$i] . "','" . ($param['nilailaindt'][$i]) . "','" . $param['noaruskasdt'][$i] . "','" . $no . "','" . $param['notransaksidt'][$i] . "','" . $param['kelompokbarangdt'][$i] . "','" . $param['nodokdt'] . "','','','" . $param['termindt'][$i] . "','" . $param['keterangandatadt'][$i] . "','" . $param['kodekegiatandt'][$i] . "')";
							$owlPDO->exec($str);
						}

						#= untuk ambil total dpp 
						#= keluarkan akun pajak sebagai dpp karna tbs di detailkan list datanya pada saat nodokdetail
						#= efek dari tbs yang muncul di view detail jadi otomatis terinsert langsung, tanpa prosesnodok dibawah
						if (!in_array($param['noakundt'][$i], $arrnoakunpajak)) {
							@$nilaidpp += ($param['nilaidt'][$i] + $param['nilailaindt'][$i]);
						}
					}
					$reksupplier = $param['reksupplierdt'][$i];
				}
			} else {
				// exit("error:A");
				for ($i = 1; $i <= $param['maxrow']; $i++) {
					if (@$param['notransaksidt'][$i] != '') {
						$tanggaldt = $param['tanggaldt'][$i];
						$keterangandatadt = $param['keterangandatadt'][$i];
						if ($param['noakundt'][$i] == '' || $param['noaruskasdt'][$i] == '') {
							throw new PDOException("No. Akun atau No. Aruskas masih kosong, Silahkan hubungi Administrator untuk melakukan setup aruskas dan nomor akun");
						}

						// echo "<pre>";
						// print_r($param['keterangandatadt']);
						// echo $i;
						// exit('warning');

						$no += 1;
						$param['nilaidt'][$i] = str_replace(',', '', $param['nilaidt'][$i]);
						$param['nilaiuangmukadt'][$i] = str_replace(',', '', $param['nilaiuangmukadt'][$i]);
						$param['nilailaindt'][$i] = str_replace(',', '', $param['nilailaindt'][$i]);
						// nilai biaya ditambahkan nilau uang muka  karna dirumus tampilan dikurangi
						#= case insert keu_tagihandt
						// if($param['nilaiuangmukadt'][$i]>0){
						// $param['nilaidt'][$i]=$param['nilaidt'][$i]+$param['nilaiuangmukadt'][$i];
						// }
						$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,kodeasset,nilai,noaruskas,nourut, notransaksi,kelompokbarang,nopo,pajak,kodeblok,termin,keterangan,kodekegiatan)
							   values('','" . $param['noakundt'][$i] . "','" . $param['kodeassetdt'][$i] . "','" . ($param['nilaidt'][$i]) . "','" . $param['noaruskasdt'][$i] . "','" . $no . "','" . $param['notransaksidt'][$i] . "','" . $param['kelompokbarangdt'][$i] . "','" . $param['nodokdt'] . "','','" . @$param['kodeblokdt'] . "','" . $param['termindt'][$i] . "','" . $param['keterangandatadt'][$i] . "','" . $param['kodekegiatandt'][$i] . "')";
						// exit('warning'.$str);
						$owlPDO->exec($str);

						if ($param['nilaiuangmukadt'][$i] > 0) {
							$no++;
							$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,kodeasset,nilai,noaruskas,nourut,notransaksi,kelompokbarang,nopo,pajak,kodeblok,termin,keterangan,kodekegiatan)
							   values('','" . $param['noakunuangmukadt'][$i] . "','" . $param['kodeassetdt'][$i] . "','" . ($param['nilaiuangmukadt'][$i] * -1) . "','" . $param['noaruskasdt'][$i] . "','" . $no . "','" . $param['notransaksidt'][$i] . "','" . $param['kelompokbarangdt'][$i] . "','" . $param['nodokdt'] . "','','','" . $param['termindt'][$i] . "','" . $param['keterangandatadt'][$i] . "','" . $param['kodekegiatandt'][$i] . "')";
							$owlPDO->exec($str);
						}


						if ($param['nilailaindt'][$i] != 0) {
							$no++;

							$str = "insert into " . $dbname . ".keu_tagihandt (noinvoice,noakun,kodeasset,nilai,noaruskas,nourut, notransaksi,kelompokbarang,nopo,pajak,kodeblok,termin,keterangan,kodekegiatan)
							   values('','" . $param['noakunlaindt'][$i] . "','" . $param['kodeassetdt'][$i] . "','" . ($param['nilailaindt'][$i]) . "','" . $param['noaruskasdt'][$i] . "','" . $no . "','" . $param['notransaksidt'][$i] . "','" . $param['kelompokbarangdt'][$i] . "','" . $param['nodokdt'] . "','','','" . $param['termindt'][$i] . "','" . $param['keterangandatadt'][$i] . "','" . $param['kodekegiatandt'][$i] . "')";
							$owlPDO->exec($str);
						}

						#= untuk ambil total dpp 
						#= keluarkan akun pajak sebagai dpp karna tbs di detailkan list datanya pada saat nodokdetail
						#= efek dari tbs yang muncul di view detail jadi otomatis terinsert langsung, tanpa prosesnodok dibawah


						if (!in_array($param['noakundt'][$i], $arrnoakunpajak) and !in_array($param['noakunuangmukadt'][$i], $arrnoakunpajak)) {
							if ($param['nilaidt'][$i] > 0) {
								#= ambil data PO untuk setdatanodok dan data pajak
								$str = "select * from " . $dbname . ".log_poht  where nopo='" . $param['nodokdt'] . "'";
								$res = fetchdata($str);
								$nopo = $res[0]['nopo'];
								$subtotal = $res[0]['subtotal'] - $res[0]['nilaidiskon'];
								$pph23 = $res[0]['persenpph'];
								$tipepo = $res[0]['tipepo'];

								@$nilaidpp += $param['nilaidt'][$i] + $param['nilailaindt'][$i];
								# Jika SO + Material dan ada terminnya, ambil nilai DPP di log_poht baru kali persen termin
								$cekSOMaterial = getCountRows($dbname, "log_somaterial", "nopo='{$nopo}'");
								$resTermin = fetchData(selectQuery($dbname, "log_potermin", "*", "nopo='{$nopo}' AND termin='{$param['termindt'][$i]}'"));
								if ($cekSOMaterial > 0 && !empty($resTermin) && $tipepo != 'PO') {
									$rpmaterial = fetchData(selectQuery($dbname, "log_somaterial", "SUM(harga*jumlah) as rpmaterial", "nopo='{$nopo}'"))[0]['rpmaterial'];

									// $nilaidppuntukpajakpphsomaterialtermin += ($resTermin[0]['rupiah'] * ($resTermin[0]['persen'] / 100)) * ($pph23 / 100);
									$nilaidppuntukpajakpphsomaterialtermin += ($resTermin[0]['rupiah'] - ($rpmaterial * ($resTermin[0]['persen'] / 100))) * ($pph23 / 100);
									$xxx .= "({$resTermin[0]['rupiah']} - ({$rpmaterial} * ({$resTermin[0]['persen']} / 100))) * ({$pph23} / 100) = {$nilaidppuntukpajakpphsomaterialtermin}\n";
								}
							} else {
								@$nilaiclaim += $param['nilaidt'][$i] + $param['nilailaindt'][$i];
							}
							@$nilaiuangmuka += $param['nilaiuangmukadt'][$i];
						} else {
							if ($param['nilaidt'][$i] > 0) { #jika tbs, ppn pasti >0, dan pph pasti -
								$nilaippn += $param['nilaidt'][$i] + $param['nilailaindt'][$i];
							} else {
								$nilaipph += $param['nilaidt'][$i] + $param['nilailaindt'][$i];
							}
						}
					}
					$reksupplier = $param['reksupplierdt'][$i];
				}
			}
			// exit("error: ".$nilaidpp);
			switch ($param['tipeinvoice']) {

				#= default data
				case 'batr':

					#= ambil data untuk setdatanodok dan data pajak
					$str = "select transportir from " . $dbname . ".pmn_batransport where nospk='" . $param['nodokdt'] . "' and posting=1";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$kodesupplier = $bar['transportir'];
						$tipesupplier = 'KONTRAKTOR';
					}

					#= ambil noakun ppn dan aruskas
					$str = " select * from " . $dbname . ".keu_5jenistagihan_akunpajak where tipepajak='ppn' and kode='" . $param['tipeinvoice'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						#= cek apakah terdaftar di log_5pphsup
						$strpajak = "select *  from " . $dbname . ".log_5pphsup  where supplierid='" . $kodesupplier . "' and noakun='" . $bar['noakun'] . "' ";
						$respajak = fetchdata($strpajak);
						foreach ($respajak as $barpajak) {
							$no++;
							@$nilaippn = floor($nilaidpp * $barpajak['tarif'] / 100);

							$str = "insert into " . $dbname . ".keu_tagihandt 
								(noinvoice,noakun,kodeasset,nilai,noaruskas,nourut, notransaksi,kelompokbarang,nopo,pajak)
								values('','" . $barpajak['noakun'] . "','','" . $nilaippn . "','" . $bar['noaruskas'] . "','" . $no . "','','','" . $param['nodokdt'] . "','" . $barpajak['tarif'] . "')";
							$owlPDO->exec($str);
						}
					}
					break;

				case 'rtn':
				case 'rtg':


					#= tipe supplier buat dinamis

					#= ambil data PO untuk setdatanodok dan data pajak
					$str = "select * from " . $dbname . ".log_poht  where nopo='" . $param['nodokdt'] . "'";
					$res = fetchdata($str);
					$nopo = $res[0]['nopo'];
					$kodesupplier = $res[0]['kodesupplier'];
					$matauang = $res[0]['matauang'];
					$kurs = $res[0]['kurs'];
					$subtotal = $res[0]['subtotal'] - $res[0]['nilaidiskon'];
					$ppn = $res[0]['ppn'];

					$tipepo = $res[0]['tipepo'];

					$nilaippn = 0;

					#= nilai dpp == uang muka

					@$persenppn = $ppn / $subtotal * 100;
					$nilaippn = floor(($nilaidpp) * $persenppn / 100);

					$akunhutangjasa = '2110301';
					$akunhutangjasa2 = '2110201';
					$akunhutanginventory = '2110101';
					// tipepo
					if ($tipepo == 'SO') {
						$str = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $kodesupplier . "' and (noakun='" . $akunhutangjasa . "' or noakun='" . $akunhutangjasa2 . "')";
					} else {
						$str = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $kodesupplier . "' and noakun='" . $akunhutanginventory . "'";
					}
					// echo $str;
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$tipesupplier = $bar['tipe'];
					}

					#= insert pajak di detail
					#= ppn
					$noakunpajak = $noaruskaspajak = '';


					$no++;
					$str = " select * from " . $dbname . ".keu_5jenistagihan_akunpajak where tipepajak='ppn' and kode='" . $param['tipeinvoice'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$noakunpajak = $bar['noakun'];
						$noaruskaspajak = $bar['noaruskas'];
					}

					if ($noakunpajak == '' || $noaruskaspajak == '') {
						throw new PDOException("Akun pph/aruskas pph untuk " . $param['tipeinvoice'] . " belum ada, silahkan buat di setup noakun pajak tagihan ");
					}

					$str = "insert into " . $dbname . ".keu_tagihandt 
								(noinvoice,noakun,kodeasset,nilai,noaruskas, 
								nourut, notransaksi,kelompokbarang,nopo,pajak)
							   values('','" . $noakunpajak . "','','" . $nilaippn . "','" . $noaruskaspajak . "',
							   '" . $no . "','','','" . $param['nodokdt'] . "','')";
					$owlPDO->exec($str);

					break;

				case 'p':
				case 'pon':
				case 'pocbd':



					#= tipe supplier buat dinamis

					#= ambil data PO untuk setdatanodok dan data pajak
					$str = "select * from " . $dbname . ".log_poht  where nopo='" . $param['nodokdt'] . "'";
					$res = fetchdata($str);
					$nopo = $res[0]['nopo'];
					$nilaidiskon = $res[0]['nilaidiskon'];
					$kodesupplier = $res[0]['kodesupplier'];
					$matauang = $res[0]['matauang'];
					$kurs = $res[0]['kurs'];
					$subtotal = $res[0]['subtotal'] - $res[0]['nilaidiskon'];
					$ongkosangkutan = $res[0]['ongkosangkutan'];
					$tesppn = $res[0]['ppn'];
					if ($res[0]['ppn'] == 0) {
						$ppn = $res[0]['ppn'];
						$ppn = $res[0]['ppn'];
					} else {
						$ppn = $res[0]['persenppn'];
						$ppn = $res[0]['persenppn'];
					}
					$pbbkb = $res[0]['pbbkb'];
					// $pph=$res[0]['pph']; // pph23 cash basis tidak di tagihan (sk ln 20210629) (KSP)
					$flagpph = $res[0]['penambahpph22']; // Palma
					$pph = $res[0]['pph22']; // Palma
					$pph23 = $res[0]['pph']; // Palma
					$tipepo = $res[0]['tipepo'];

					# Jika ada PBBKB nilai dpp dikurangin dulu agar dapet nilai real ppn dan pph
					if ($pbbkb > 0) {
						$nilaidpp -= $pbbkb;
					}
					//exit('warning'.$str);
					#cari proporsi nilai ppn dan pph

					// batalin floor. kalo pake floor: 898999=((8990000+0)/12380000) x1238000 (selisih 1)
					// kalo pake round: 899000=((8990000+0)/12380000) x1238000 (pas)
					// @$nilaippn=round((($nilaidpp+$nilaiuangmuka)/$subtotal)*$ppn); // ini ga bisa karena PO pake floor

					#= disini baru bentuk ppn, sehinggi di buat 0 dulu, karna masuk rumus baru
					$nilaippn = 0;
					// exit("error: ".$nilaidpp);

					// jika dpp melebihi uang muka baru ada jurnal pajaknya, kasus ini jika uang muka = nilai invoice hutang, jurnal terbentuk hanya untuk membalikkan uang muka saja
					// exit("Error:".$ppn._.$subtotal);
					// $nilaidpp=$nilaidpp-$ongkosangkutan-$pbbkb;

					#= jika uang muka tidak ada ppn 
					// if($param['dtsisappnum']=='0'){
					// $nilaidpp=$nilaidpp+$nilaiuangmuka;
					// } #= jika uang muka ada ppn
					#### Codingan Commit by Yoga disuruh Pak Jamhari
					// $nilaidppuntukpajak=$nilaidpp-$ongkosangkutan-$pbbkb;
					//  #= jika uang muka ada ppn

					// if($param['dtsisappnum']=='0'){
					// $nilaidpp=$nilaidpp-$nilaiuangmuka;
					// }
					####

					$sql = selectQuery($dbname, "log_sorefrensi", "*", "nopo='" . $nopo . "'");
					$res = fetchData($sql);
					if (empty($res)) {
						$ongkosangkutan = 0;
					}

					if ($param['dtsisappnum'] == '0') {
						// $nilaidppuntukpajak = $nilaidpp - $ongkosangkutan - $pbbkb + $nilaiuangmuka;
						$nilaidppuntukpajak = $nilaidpp - $ongkosangkutan + $nilaiuangmuka;
					} else {
						// $nilaidppuntukpajak = $nilaidpp - $ongkosangkutan - $pbbkb;
						$nilaidppuntukpajak = $nilaidpp - $ongkosangkutan;
					}
					// exit("Error:".$nilaidppuntukpajak._.$nilaidpp);
					#= nilai dpp == uang muka

					// exit('warning antisipasi'.$nilaiuangmuka.' x '.$nilaidppuntukpajak);
					if ($nilaidppuntukpajak == $nilaiuangmuka) {
						if ($tesppn == 0) {
							@$persenppn = $ppn / $subtotal * 100;
						} else {
							@$persenppn = $ppn;
						}
						#= jika nilai dpp == 0, contoh kasus, uang muka diawal full, sehingga ini hanya sebagai pembalik
						if ($nilaidppuntukpajak == 0) {
							// exit('warning 1');
							$nilaippn = floor(($nilaidppuntukpajak + $nilaiuangmuka) * $persenppn / 100);
						} else { # jika nilai dpp > 0, tapi ada uang muka, contoh kasus, dp 50%
							// exit('warning 2');
							$nilaippn = floor(($nilaidppuntukpajak) * $persenppn / 100);
						}
					} else {
						// exit("Error:".$nilaidppuntukpajak);
						if ($tesppn == 0) {
							@$persenppn = $ppn / $subtotal * 100;
						} else {
							@$persenppn = $ppn;
						}
						// exit('warning3');
						$nilaippn = floor(($nilaidppuntukpajak) * $persenppn / 100);
						// exit("Warning: " . $nilaippn);
						#= Tambah kodebarang 91114 untuk Sepeda Motor
						#= Cek apakah nilainya sudah di bayarkan
						if ($nilaidppuntukpajak > 0) {
							#= Perlakuan Khusus Jikalau PPN PO tersebut adalah kodebarang Motor
							$qCekPpnMotor = "select a.nopo,a.ppn as nilaippn,b.* from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where b.nopo='" . $param['nodokdt'] . "' and (b.kodebarang LIKE '38901%' or b.kodebarang LIKE '91114%')";
							$resCekPpnMotor = fetchData($qCekPpnMotor);
							$cekDataPpnMotor = count($resCekPpnMotor);

							#= Cek apakah PO tersebut, merupakan PO motor atau bukan
							if ($cekDataPpnMotor > 0) {
								if ($nilaidppuntukpajak != $nilaiuangmuka) {
									$nilaippn = $resCekPpnMotor[0]['nilaippn'];
								}
							}
						}
					}
					// echo"<pre>";
					// print_r($param);
					// exit("Error:".$nilaidpp._.$nilaippn._.$persenppn._.$nilaiuangmuka);

					# Perhitungan PPh 22
					if ($pph > 0) {
						if ($flagpph == '1') { # Flag PPh22
							@$nilaipph = round((($nilaidppuntukpajak + $nilaiuangmuka) / $subtotal) * $pph * 1, 2);
						} else {
							@$nilaipph = round((($nilaidppuntukpajak + $nilaiuangmuka) / $subtotal) * $pph * -1, 2);
						}
					}

					// pph23 cash basis tidak di tagihan (sk ln 20210629)
					# Perhitungan PPh 23
					if ($pph23 > 0) { # PPh 23
						# Cek SO Material
						// $rpmaterial = fetchData(selectQuery($dbname, "log_somaterial", "SUM(harga*jumlah) as rpmaterial", "nopo='{$nopo}'"))[0]['rpmaterial'];

						# Jika SO + Material dan ada terminnya, ambil nilai DPP di log_poht baru kali persen termin
						$cekSOMaterial = getCountRows($dbname, "log_somaterial", "nopo='{$nopo}'");
						$resTermin = fetchData(selectQuery($dbname, "log_potermin", "*", "nopo='{$nopo}'"));
						if ($cekSOMaterial > 0 && !empty($resTermin)) {
							$nilaipph = $nilaidppuntukpajakpphsomaterialtermin * -1;
						} else {
							@$nilaipph = round(((($nilaidppuntukpajak - $rpmaterial) + $nilaiuangmuka) / $subtotal) * $pph23 * -1, 2);
						}
						// exit("Warning: (" . $nilaidppuntukpajak . " - " . $rpmaterial . ") + " . $nilaiuangmuka . ") / " . $subtotal . ") * " . $pph23);
					}

					# Info Mas Ari posisi nya sama" di debet
					// @$nilaipph=round((($nilaidpp+$nilaiuangmuka)/$subtotal)*$pph*1);

					# balikin nilai pbbkb lagi
					if ($pbbkb > 0) {
						$nilaidpp += $pbbkb;
					}
					// $xxx .= "dpp3: {$nilaidpp}<br/>";
					// if ($_SESSION['standard']['username'] == 'tim.owl') exit("Warning : ".$xxx);

					@$nilaiinvoice = $nilaidpp + $nilaippn - $nilaipph;
					// if ($_SESSION['standard']['username'] == 'tim.owl') {
					// 	exit("Warningxxx: " . $nilaidpp . " + " . $nilaippn . " - " . $nilaipph . " = " . $nilaiinvoice);
					// }

					// echo "error!".$nilaippn."=((".$nilaidpp."+".$nilaiuangmuka.")/".$subtotal.") x".$ppn;; exit();

					// $akunhutangjasa='2110301'; # Hutang Usaha Jasa
					// $akunhutangjasa2='2110201'; # Hutang Usaha Kontraktor
					// $akunhutanginventory='2110101'; # Hutang Usaha Pemasok

					$akunhutangjasa = '2111301'; # Hutang Usaha Jasa
					$akunhutangjasa2 = '2111201'; # Hutang Usaha Kontraktor
					$akunhutanginventory = '2111101'; # Hutang Usaha Pemasok
					// tipepo
					if ($tipepo == 'SO') {
						$str = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $kodesupplier . "' and (noakun='" . $akunhutangjasa . "' or noakun='" . $akunhutangjasa2 . "')";
					} else {
						$str = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $kodesupplier . "' and noakun='" . $akunhutanginventory . "'";
					}
					// echo $str;
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$tipesupplier = $bar['tipe'];
					}

					#= insert pajak di detail
					#= ppn
					$noakunpajak = $noaruskaspajak = '';
					if ($nilaippn > 0) {
						$no++;
						$str = " select * from " . $dbname . ".keu_5jenistagihan_akunpajak where tipepajak='ppn' and kode='" . $param['tipeinvoice'] . "'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$noakunpajak = $bar['noakun'];
							$noaruskaspajak = $bar['noaruskas'];
						}

						if ($noakunpajak == '' || $noaruskaspajak == '') {
							throw new PDOException("Akun pph/aruskas pph untuk " . $param['tipeinvoice'] . " belum ada, silahkan buat di setup noakun pajak tagihan ");
						}

						$str = "insert into " . $dbname . ".keu_tagihandt 
								(noinvoice,noakun,kodeasset,nilai,noaruskas, 
								nourut, notransaksi,kelompokbarang,nopo,pajak)
								values('','" . $noakunpajak . "','','" . $nilaippn . "','" . $noaruskaspajak . "',
								'" . $no . "','','','" . $param['nodokdt'] . "','')";
						$owlPDO->exec($str);
					}

					#= pph tidak ada lagi di tagihan: pph23 cash basis tidak di tagihan (sk ln 20210629)
					$noakunpajak = $noaruskaspajak = '';

					if ($pph != 0): #pph 22
						if ($nilaipph != 0) {
							$no++;
							// $str =" select * from ".$dbname.".keu_5jenistagihan_akunpajak where tipepajak='pph' and kode='".$param['tipeinvoice']."'";
							// $res=fetchdata($str);
							// foreach($res as $bar) {
							// 	$noakunpajak=$bar['noakun'];
							// 	$noaruskaspajak=$bar['noaruskas'];
							// }

							// if($noakunpajak=='' || $noaruskaspajak==''){
							// 	exit("Error:Akun pph/aruskas pph untuk ".$param['tipeinvoice']." belum ada, silahkan buat di setup noakun pajak tagihan ");
							// }

							if ($flagpph == '1') { # Plus
								$noakunpajak = '1160103'; #akun pph masukan 22
								$noaruskaspajak = '10803'; #arus kas pph 22
							} else {
								$noakunpajak = '2120801'; #akun hutang pph 22
								$noaruskaspajak = '10602'; #arus kas hutang pph 22
							}

							$str = "insert into " . $dbname . ".keu_tagihandt 
									(noinvoice,noakun,kodeasset,nilai,noaruskas, 
									nourut, notransaksi,kelompokbarang,nopo,pajak)
								values('','" . $noakunpajak . "','','" . ($nilaipph) . "','" . $noaruskaspajak . "',
								'" . $no . "','','','" . $param['nodokdt'] . "','')";
							$owlPDO->exec($str);
						}
					endif;

					if ($pph23 != 0) { #pph 23
						if ($nilaipph != 0) {
							$no++;
							$str = " select * from " . $dbname . ".keu_5jenistagihan_akunpajak where tipepajak='pph' and kode='" . $param['tipeinvoice'] . "'";
							$res = fetchdata($str);
							foreach ($res as $bar) {
								$noakunpajak = $bar['noakun'];
								$noaruskaspajak = $bar['noaruskas'];
							}

							if ($noakunpajak == '' || $noaruskaspajak == '') {
								exit("Error:Akun pph/aruskas pph untuk " . $param['tipeinvoice'] . " belum ada, silahkan buat di setup noakun pajak tagihan ");
							}

							$str = "insert into " . $dbname . ".keu_tagihandt 
									(noinvoice,noakun,kodeasset,nilai,noaruskas, 
									nourut, notransaksi,kelompokbarang,nopo,pajak)
								values('','" . $noakunpajak . "','','" . ($nilaipph) . "','" . $noaruskaspajak . "',
								'" . $no . "','','','" . $param['nodokdt'] . "','')";
							$owlPDO->exec($str);
						}
					}

					break;

				case 'k':


					#= data spk
					$str = "select * from " . $dbname . ".log_spkht  where notransaksi='" . $param['nodokdt'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$nopengajuan = $bar['nopengajuan'];
						$kodesupplier = $bar['koderekanan'];
						$matauang = $bar['matauang'];
						$kurs = $bar['kurs'];
						if ($kurs == '') {
							$kurs = 1;
						}
					}

					$str = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $kodesupplier . "'";
					// exit("Error:$str");
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$tipesupplier = $bar['tipe'];
					}

					$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi ='" . $nopengajuan . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$tipesupplier = $bar['jenissupplier'];
					}

					#= tax
					$str = "select * from " . $dbname . ".log_spk_tax where notransaksi='" . $param['nodokdt'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$arrnoakun[$bar['noakun']] = $bar['noakun'];
						$dtpersen[$bar['noakun']] = $bar['nilai'];
					}

					#= ambil data aruskas dan coa
					$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $arrnoakun) . "') and noaruskas like '1%'";
					// echo $str;
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$dtnoaruskas[$bar['noakun']] = $bar['noaruskas'];
					}


					// if($_SESSION['standard']['username']=='tim.owl3') {
					// 	echo floor(($dtpersen['2120101']/100*$nilaidpp))."<br/>";
					// 	echo floor(($dtpersen['2120101']/100*$nilaidpp*-1));
					// 	exit('warning');
					// }

					foreach ($arrnoakun as $dtnoakun) {
						if ($dtnoakun == '1160101') { # Jika akun ppn
							$no++;
							$str = "insert into " . $dbname . ".keu_tagihandt 
									(noinvoice,noakun,kodeasset,nilai,noaruskas, 
									nourut, notransaksi,kelompokbarang,nopo,pajak)
									values('','" . $dtnoakun . "','','" . floor(($dtpersen[$dtnoakun] / 100 * $nilaidpp)) . "','" . $dtnoaruskas[$dtnoakun] . "',
									'" . $no . "','','','" . $param['nodokdt'] . "','" . $dtpersen[$dtnoakun] . "')";
							$owlPDO->exec($str);
							$nilaipph += floor(($dtpersen[$dtnoakun] / 100 * $nilaidpp));
						}

						if ($dtnoakun != '2130301' && $dtnoakun != '1160101') { // pph23 cash basis tidak di tagihan (sk ln 20210629)
							$no++;
							$str = "insert into " . $dbname . ".keu_tagihandt 
									(noinvoice,noakun,kodeasset,nilai,noaruskas, 
									nourut, notransaksi,kelompokbarang,nopo,pajak)
									values('','" . $dtnoakun . "','','" . (floor(($dtpersen[$dtnoakun] / 100 * $nilaidpp)) * -1) . "','" . $dtnoaruskas[$dtnoakun] . "',
									'" . $no . "','','','" . $param['nodokdt'] . "','" . $dtpersen[$dtnoakun] . "')";
							$owlPDO->exec($str);
							$nilaipph += floor(($dtpersen[$dtnoakun] / 100 * $nilaidpp)) * -1;
						}
					}

					if ($tipesupplier == '') {
						throw new PDOException("Tipe Assignment masih kosong, daftarkan tipe assignment KONTRAKTOR dimaster kelompok assignment");
					}


					break;



				case 'bas':

					#= data spk
					$str = "select * from " . $dbname . ".log_kontrakjasa  where notransaksi='" . $param['nodokdt'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$kodesupplier = $bar['supplierid'];
						$matauang = $bar['matauang'];
						if ($matauang == '') {
							$matauang = 'IDR';
						}
						$kurs = $bar['kurs'];
						if ($kurs == '') {
							$kurs = 1;
						}
					}

					#= tax
					$str = "select * from " . $dbname . ".log_spk_tax where notransaksi='" . $param['nodokdt'] . "' ";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$arrnoakun[$bar['noakun']] = $bar['noakun'];
						if (substr($bar['noakun'], 0, 3) == '117') {
							@$dtpersenppn += $bar['nilai'];
							$dtnoakunppn = $bar['noakun'];
						}
						if (substr($bar['noakun'], 0, 3) == '213') {
							// pph23 cash basis tidak di tagihan (sk ln 20210629)
							if ($bar['noakun'] != '2130301') {
								@$dtpersenpph += $bar['nilai'];
								$dtnoakunpph = $bar['noakun'];
							}
						}
						$dtpersen[$bar['noakun']] = $bar['nilai'];
					}


					// #= tax
					// $str = "select * from ".$dbname.".log_spk_tax  where notransaksi='".$param['nodokdt']."'";
					// $res=fetchdata($str);
					// foreach($res as $bar) {
					// $dtpersen[$bar['noakun']]=$bar['nilai'];
					// $dtrupiahpajak[$bar['noakun']]=$bar['nilai'];
					// }

					$nilaippn = $nilaipph = 0;

					#= BASPK
					#= nilai dpp tidak tarik dari atas karna ada perbedaan
					$str = "select * from " . $dbname . ".keu_tagihandt where nopo='" . $param['nodokdt'] . "' and noinvoice='' and notransaksi!=''";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						if (substr($bar['noakun'], 0, 3) == '118') {
							continue; // karna akun uang muka tidak ikut sebagai dpp untuk pajak
						} else {
							@$nilaidpptagihan = $bar['nilai'];
						}

						if ($bar['keterangan'] == 'material') {
							@$nilaippn += floor($dtpersenppn / 100 * $nilaidpptagihan);
						}
						if ($bar['keterangan'] == 'jasa') {
							@$nilaippn += floor($dtpersenppn / 100 * $nilaidpptagihan);
							// @$nilaipph-=floor($dtpersenpph/100*$nilaidpptagihan);
						}
					}
					// exit("Error:".$nilaippn._.$dtpersenppn._.$nilaidpptagihan._.$nilaidpptagihan2);
					$str = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $kodesupplier . "' and tipe like '%KONTRAKTOR%'";
					// exit("Error:$str");
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$tipesupplier = $bar['tipe'];
					}

					if ($tipesupplier == '') {
						$str = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $kodesupplier . "' and tipe like '%JASA%'";
						// exit("Error:$str");
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$tipesupplier = $bar['tipe'];
						}
					}

					// exit('warning'.$nilaippn);

					if ($tipesupplier == '') {
						throw new PDOException("Tipe Assignment masih kosong, daftarkan tipe assignment KONTRAKTOR /  JASA dimaster kelompok assignment");
					}

					#= ambil data aruskas dan coa
					$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun in ('" . implode("','", $arrnoakun) . "') and noaruskas like '1%'";
					// echo $str;
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$dtnoaruskas[$bar['noakun']] = $bar['noaruskas'];
					}


					if ($nilaippn != 0) {
						$no++;
						$str = "insert into " . $dbname . ".keu_tagihandt 
						(noinvoice,noakun,kodeasset,nilai,noaruskas,nourut, notransaksi,kelompokbarang,nopo,pajak)
					   values('','" . $dtnoakunppn . "','','" . $nilaippn . "','" . $dtnoaruskas[$dtnoakunppn] . "','" . $no . "','','','" . $param['nodokdt'] . "','" . $dtpersen[$dtnoakunppn] . "')";
						$owlPDO->exec($str);
					}

					if ($nilaipph != 0) {
						$no++;
						$str = "insert into " . $dbname . ".keu_tagihandt 
						(noinvoice,noakun,kodeasset,nilai,noaruskas,nourut, notransaksi,kelompokbarang,nopo,pajak)
					   values('','" . $dtnoakunpph . "','','" . $nilaipph . "','" . $dtnoaruskas[$dtnoakunpph] . "','" . $no . "','','','" . $param['nodokdt'] . "','" . $dtpersen[$dtnoakunpph] . "')";
						$owlPDO->exec($str);
					}







					break;

				case 'ffbfee':
					$str = "select * from " . $dbname . ".pmn_feetbs where notransaksi='" . $param['nodokdt'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$dtnotransaksi = $bar['notransaksi'];
						$dtsupplier = $bar['kodesupplier'];
						$kodesupplier = $bar['kodesupplier'];
						$explnotran = explode('/', $bar['notransaksi']);
						$tipesupplier = 'FEESUPPLIERTBS';
						// $rekening=$bar['rekening'];
					}
					break;

				case 'ffb':

					#========================================================
					#========================================================
					#========================================================

					$str = "select * from " . $dbname . ".pmn_tbs where notransaksi='" . $param['nodokdt'] . "' ";
					// echo $str;exit("Error:a");
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$dtnotransaksi = $bar['notransaksi'];
						$dtsupplier = $bar['supplier'];
						$kodesupplier = $bar['supplier'];
						$dtpersenppn = $bar['persenppn'];
						$dtpersenpph = $bar['persenpph'];
						$tipesupplier = $bar['tipesupplier'];
						$kodesupplier = $bar['supplier'];
					}

					if ($dtpersenppn > 0) {
						$dtpersenppn = 11;
						@$nilaippn = round($dtpersenppn / 100 * $nilaidpp, 2);
					}
					if ($dtpersenpph > 0) {

						@$nilaipph = round($dtpersenpph / 100 * $nilaidpp * -1, 2);
					}

					#= ppn
					$noakunpajak = $noaruskaspajak = '';
					if ($nilaippn > 0) {
						$no++;
						$str = " select * from " . $dbname . ".keu_5jenistagihan_akunpajak where tipepajak='ppn' and kode='" . $param['tipeinvoice'] . "'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$noakunpajak = $bar['noakun'];
							$noaruskaspajak = $bar['noaruskas'];
						}

						if ($noakunpajak == '' || $noaruskaspajak == '') {
							exit("Error:Akun ppn/aruskas ppn untuk " . $param['tipeinvoice'] . " belum ada, silahkan buat di setup noakun pajak tagihan ");
						}

						$str = "insert into " . $dbname . ".keu_tagihandt 
								(noinvoice,noakun,kodeasset,nilai,noaruskas, 
								nourut, notransaksi,kelompokbarang,nopo,pajak)
							   values('','" . $noakunpajak . "','','" . $nilaippn . "','" . $noaruskaspajak . "',
							   '" . $no . "','','','" . $param['nodokdt'] . "','" . $dtpersenppn . "')";
						$owlPDO->exec($str);
					}

					#= pph
					$noakunpajak = $noaruskaspajak = '';
					if ($nilaipph != 0) {
						$no++;
						$str = " select * from " . $dbname . ".keu_5jenistagihan_akunpajak  where tipepajak='pph' and kode='" . $param['tipeinvoice'] . "'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$noakunpajak = $bar['noakun'];
							$noaruskaspajak = $bar['noaruskas'];
						}

						if ($noakunpajak == '' || $noaruskaspajak == '') {
							exit("Error:Akun pph/aruskas pph untuk " . $param['tipeinvoice'] . " belum ada, silahkan buat di setup noakun pajak tagihan ");
						}

						$str = "insert into " . $dbname . ".keu_tagihandt 
								(noinvoice,noakun,kodeasset,nilai,noaruskas, 
								nourut, notransaksi,kelompokbarang,nopo,pajak)
							   values('','" . $noakunpajak . "','','" . ($nilaipph) . "','" . $noaruskaspajak . "',
							   '" . $no . "','','','" . $param['nodokdt'] . "','" . $dtpersenpph . "')";
						$owlPDO->exec($str);
					}

					break;


				case 'ffba':
				case 'ffbe':

					// tidak ada proses ppn/pphnya, karna sudah di list data detail (dipecah antara dpp dan ppn + pph)

					#= hanya ambil kodesupplier dan tipesupplier
					// if ($param['tipeinvoice'] == 'ffb') {
					// 	$str = "select * from " . $dbname . ".kebun_tbskud where notransaksi='" . $param['nodokdt'] . "' ";
					// }
					if ($param['tipeinvoice'] == 'ffba') {
						$str = "select * from " . $dbname . ".kebun_tbsafiliasi where notransaksi='" . $param['nodokdt'] . "' ";
					}
					if ($param['tipeinvoice'] == 'ffbe') {
						$str = "select * from " . $dbname . ".kebun_tbsexternal where notransaksi='" . $param['nodokdt'] . "' ";
					}
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$dtnotransaksi = $bar['notransaksi'];
						$dtsupplier = $bar['supplier'];
						$kodesupplier = $bar['supplier'];
						$explnotran = explode('/', $bar['notransaksi']);
						$tipesupplier = 'SUPPLIER' . $explnotran[1];
					}

					break;

				/*
					
					case'sales':
					
						#= data nilai kontrak
					$str="select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['nodokdt']."' ";
					$res=fetchdata($str);
					foreach($res as $bar){
						$dtkoderekanan=$bar['koderekanan'];
						$dtkodebarang=$bar['kodebarang'];
					}
					$arrtipesupplier=array("40000001"=>"SALESCPO","40000002"=>"SALESPK","4000000001"=>"SALESCPO","4000000002"=>"SALESPK");
					$tipesupplier=$arrtipesupplier[$dtkodebarang];
				
					#= ambil data kodesupplier
					$str="select * from ".$dbname.".log_5supplier where kodecustomer='".$dtkoderekanan."' ";
					$res=fetchdata($str);
					foreach($res as $bar){
						$dtsupplierid=$bar['supplierid'];
						$kodesupplier=$bar['supplierid'];
					}
				
					$nilaippn=0.1*$nilaidpp;
					
					if($nilaippn>0){
						$no++;
						$str =" select * from ".$dbname.".keu_5jenistagihan_akunpajak where tipepajak='ppn' and kode='".$param['tipeinvoice']."'";
						$res=fetchdata($str);
						foreach($res as $bar) {
							$noakunpajak=$bar['noakun'];
							$noaruskaspajak=$bar['noaruskas'];
						}
						
						if($noakunpajak=='' || $noaruskaspajak==''){
							exit("Error:Akun pph/aruskas pph untuk ".$param['tipeinvoice']." belum ada, silahkan buat di setup noakun pajak tagihan ");
						}
						
						$str = "insert into " . $dbname . ".keu_tagihandt 
								(noinvoice,noakun,kodeasset,nilai,noaruskas, 
								nourut, notransaksi,kelompokbarang,nopo,pajak)
							   values('','".$noakunpajak."','','".$nilaippn."','".$noaruskaspajak."',
							   '".$no."','','','".$param['nodokdt']."','10')";	
						$owlPDO->exec($str);	
					}			
					
					
					
					break;
					
					
					
					case'trsales':
					
						
						#= ambil data untuk setdatanodok dan data pajak
						$str="select * from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$param['nodokdt']."' ";
						$res=fetchdata($str);
						foreach($res as $bar){
							$kodesupplier=$bar['transportir'];
							$persenppn=$bar['persenppn'];
							$persenpph=$bar['persenpph'];
							$kodebarang=$bar['kodebarang'];
						}
						$arrtipesupplier=array("40000001"=>"TRANSPORTIRCPO","40000002"=>"TRANSPORTIRPK","40000003"=>"TRANSPORTIRTBS","4000000001"=>"TRANSPORTIRCPO","4000000002"=>"TRANSPORTIRPK","4000000003"=>"TRANSPORTIRTBS");
						$tipesupplier=$arrtipesupplier[$kodebarang];
						
						#= nilai ppn dan pph
						@$nilaippn=$persenppn/100*$nilaidpp;
						@$nilaipph=$persenpph/100*$nilaidpp*-1;
						
						
						#= insert pajak di detail
						
						#= ppn
						$noakunpajak=$noaruskaspajak='';
						if($nilaippn>0){
							$no++;
							$str =" select * from ".$dbname.".keu_5jenistagihan_akunpajak where tipepajak='ppn' and kode='".$param['tipeinvoice']."'";
							$res=fetchdata($str);
							foreach($res as $bar) {
								$noakunpajak=$bar['noakun'];
								$noaruskaspajak=$bar['noaruskas'];
							}
							
							if($noakunpajak=='' || $noaruskaspajak==''){
								exit("Error:Akun pph/aruskas pph untuk ".$param['tipeinvoice']." belum ada, silahkan buat di setup noakun pajak tagihan ");
							}
							
							$str = "insert into " . $dbname . ".keu_tagihandt 
									(noinvoice,noakun,kodeasset,nilai,noaruskas, 
									nourut, notransaksi,kelompokbarang,nopo,pajak)
								values('','".$noakunpajak."','','".$nilaippn."','".$noaruskaspajak."',
								'".$no."','','','".$param['nodokdt']."','')";	
							$owlPDO->exec($str);	
						}				
						
						#= pph
						$noakunpajak=$noaruskaspajak='';
						if($nilaipph!=0){
							$no++;
							$str =" select * from ".$dbname.".keu_5jenistagihan_akunpajak  where tipepajak='pph' and kode='".$param['tipeinvoice']."'";
							$res=fetchdata($str);
							foreach($res as $bar) {
								$noakunpajak=$bar['noakun'];
								$noaruskaspajak=$bar['noaruskas'];
							}
							
							if($noakunpajak=='' || $noaruskaspajak==''){
								exit("Error:Akun pph/aruskas pph untuk ".$param['tipeinvoice']." belum ada, silahkan buat di setup noakun pajak tagihan ");
							}
							
							$str = "insert into " . $dbname . ".keu_tagihandt 
									(noinvoice,noakun,kodeasset,nilai,noaruskas, 
									nourut, notransaksi,kelompokbarang,nopo,pajak)
								values('','".$noakunpajak."','','".($nilaipph)."','".$noaruskaspajak."',
								'".$no."','','','".$param['nodokdt']."','')";	
							$owlPDO->exec($str);	
						}				
					break;
					*/



				default:
					throw new PDOException("Tipe invoice " . $param['tipeinvoice'] . " belum dicoding");
					break;
			}
			#= tutup switch case sesuai tipe

			#= insert data ke detail
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning: Gagal melakukan simpan dokumen \n" . addslashes($e->getMessage());
		}

		#= nilai penjumlahan untuk nilaidpp dan nilai invoice (nilai dpp +- pajak)
		#= nilai claim menjadi penambah nilai invoice, dibedakan dengan dpp karna yang masuk ppn/pph diluar claim, concoh case batr
		@$nilaiinvoice = $nilaidpp + $nilaippn + $nilaipph + $nilaiclaim;
		// exit("Error:".$nilaiinvoice._.$nilaidpp._.$nilaippn._.$nilaipph);

		#= data rekening supplier
		#= rekening dan jenis supplier
		#= dirubah jadi param kirim saja
		if (@$reksupplier == '') {
			$str = "select * from " . $dbname . ".log_5rekbank where supplierid='" . $kodesupplier . "' and isactive=1";
			$res = fetchdata($str);
			$rekening = $res[0]['rekening'];
		} else {
			$rekening = $reksupplier;
		}

		if ($param['tipeinvoice'] != 'rtg' and $param['tipeinvoice'] != 'rtn') {
			if ($nilaidpp < 0) {
				$nilaidpp = 0;
			}
			if ($nilaiinvoice < 0) {
				$nilaiinvoice = 0;
			}
		}

		// Cek jika nopo memiliki solar dan suratjalan
		$qSuratJalan = selectQuery($dbname, 'log_transaksiht', '*', "nopo='" . $nopo . "' AND kodept='" . $_SESSION['empl']['kodeorganisasi'] . "' ");
		$resSuratJalan = fetchData($qSuratJalan);
		// $suratJalan = $resSuratJalan[0]['nosj'] ? $resSuratJalan[0]['nosj'] : "";
		$suratJalan = '';

		echo  $param['nodokdt'] . "###" . $kodesupplier . "###" . @number_format($nilaidpp, 2) . "###" . @number_format($nilaiinvoice, 2) . "###" . $matauang . "###" . $kurs . "###" . $rekening . "###" . $tipesupplier . "###" . $keterangandatadt . "###" . $tanggaldt . "###" . $suratJalan;
		// echo  $param['nodokdt']."###".$kodesupplier."###".@number_format($nilaidpp,2)."###".@number_format($nilaiinvoice,2)."###".$matauang."###".$kurs."###".$rekening."###".$tipesupplier."###".$keterangandatadt."###".$tanggaldt;

		// exit("Error:MASUK");
		// if($_SESSION['standard']['username']=='tim.owl3'){
		// exit("Error:MASUK");
		// }

		break;





	case 'saveht':

		$param['nilaidpp'] = str_replace(',', '', $param['nilaidpp']);
		$param['nilaiinvoice'] = str_replace(',', '', $param['nilaiinvoice']);


		#= validasi
		if ($param['nopo'] == '') {
			exit("Warning:Nomor Dokumen masih kosong");
		}
		if ($param['tipeinvoice'] == 'ot') {
			if ($param['nilaidpp'] == '' || $param['nilaidpp'] == '0') {
				exit("Warning:Nilai DPP masih kosong");
			}
			if ($param['nilaiinvoice'] == '' || $param['nilaiinvoice'] == '0') {
				exit("Warning:Nilai Invoice masih kosong");
			}
		} else {
			if ($param['nilaidpp'] == '') {
				exit("Warning:Nilai DPP masih kosong");
			}
			if ($param['nilaiinvoice'] == '') {
				exit("Warning:Nilai Invoice masih kosong");
			}
		}
		if ($param['noakun'] == '') {
			exit("Warning:Nomor akun masih kosong");
		}
		if ($param['unit'] == '') {
			exit("Warning:Unit masih kosong");
		}

		if ($param['kodeorg'] == '') {
			exit("Warning:PT masih kosong");
		}

		if ($param['tipearuskasht'] == '') {
			exit("Warning: Tipe Arus Kas masih kosong");
		}

		// if ($param['tipeinvoice'] == 'ffb') { // update keterangan rekening khusus tipe ffb karena ada kemungkinan noreknya beda
		// 	$qwe = explode(' NoRek: ', $param['keterangan']);
		// 	$asd = $qwe[0];
		// 	if ($qwe[1] != '') $asd .= ' NoRek: ' . $param['reksupplier'];
		// 	$param['keterangan'] = $asd;
		// }
		if ($param['tipeinvoice'] == 'ffbfee') { // update keterangan rekening khusus tipe ffb karena ada kemungkinan noreknya beda
			$qwe = explode(' rekening : ', $param['keterangan']);
			$asd = $qwe[0];
			if ($qwe[1] != '') $asd .= ' rekening : ' . $param['reksupplier'];
			$param['keterangan'] = $asd;
		}

		if ($param['tanggalinvoice'] == '') {
			exit("Warning:Tanggal Invoice masih kosong");
		}

		if ($param['tanggal'] == '') {
			exit("Warning:Tanggal Dokumen masih kosong");
		}

		if ($param['noinvoicesupplier'] == '') {
			exit("Warning:No. Invoice Supplier masih kosong");
		}

		if ($param['npwp'] == '' || $param['npwp'] == 'false') {
			exit("Warning:NPWP/NPWP PPH masih kosong");
		}
		if ($param['npwppph'] == '' || $param['npwppph'] == 'false') {
			exit("Warning:NPWP/NPWP PPH masih kosong");
		}

		try {

			$owlPDO->beginTransaction();
			if ($param['noinvoice'] == '') {

				$param['noinvoice'] = date('Ymdhis');

				#= saveht
				$str = "insert into " . $dbname . ".keu_tagihanht
				(noinvoice,tipeinvoice,tanggal,nopo,kodesupplier,
				 nilaidpp,nilaiinvoice,jatuhtempo,tanggalinvoice,nofp,
				 keterangan,keterangan2,noakun,matauang,kurs,
				 posting,kodeorg,unit,updateby,postingby,
				 postingdate,noinvoicesupplier,tanggalnofp,npwp,npwppph,
				 jenistransaksi,reksupplier,jenissupplier,bagian,createby,
				 createtime,lokasitugasuser,nosj,tipearuskasht)  values 
				('" . $param['noinvoice'] . "','" . $param['tipeinvoice'] . "','" . tanggalsystemn($param['tanggal']) . "','" . $param['nopo'] . "','" . $param['supplier'] . "',
				'" . $param['nilaidpp'] . "','" . $param['nilaiinvoice'] . "','" . tanggalsystemn($param['jatuhtempo']) . "','" . tanggalsystemn($param['tanggalinvoice']) . "','" . $param['nofp'] . "','" . $param['keterangan'] . "','" . $param['keterangan'] . "','" . $param['noakun'] . "','" . $param['matauang'] . "','" . $param['kurs'] . "','0','" . $param['kodeorg'] . "','" . $param['unit'] . "','" . $_SESSION['standard']['userid'] . "','',
				'','" . $param['noinvoicesupplier'] . "','" . tanggalsystemn($param['tanggalnofp']) . "','" . $param['npwp'] . "','" . $param['npwppph'] . "',
				'" . $param['tipeinvoice'] . "','" . $param['reksupplier'] . "','" . $param['jenissupplier'] . "','" . $param['bagian'] . "','" . $_SESSION['standard']['userid'] . "',
				'" . date('Y-m-d H:i:s') . "','" . $_SESSION['empl']['lokasitugas'] . "','" . $param['nosj'] . "','" . $param['tipearuskasht'] . "')";
				$checkkarid = $_SESSION['standard']['userid'];
				// if ($checkkarid == '0000000001') {
				// 	echo $str;
				// 	exit('error');
				// }
				$owlPDO->exec($str);
				#= update detail jika ada detailnya
				#= paramnya pakai where noinvocice ='' and nopo=param po
				$str = "update " . $dbname . ".keu_tagihandt set noinvoice='" . $param['noinvoice'] . "' where noinvoice='' and nopo='" . $param['nopo'] . "'";
				$owlPDO->exec($str);
			} else {

				#= updateht
				$str = "update " . $dbname . ".keu_tagihanht set 
				tipeinvoice='" . $param['tipeinvoice'] . "',tanggal='" . tanggalsystemn($param['tanggal']) . "',jenissupplier='" . $param['jenissupplier'] . "',tipearuskasht='" . $param['tipearuskasht'] . "',
				noakun='" . $param['noakun'] . "',nilaidpp='" . $param['nilaidpp'] . "',nilaiinvoice='" . $param['nilaiinvoice'] . "',
				jatuhtempo='" . tanggalsystemn($param['jatuhtempo']) . "',keterangan='" . $param['keterangan'] . "',keterangan2='" . $param['keterangan2'] . "',reksupplier='" . $param['reksupplier'] . "',noinvoicesupplier='" . $param['noinvoicesupplier'] . "',nopo='" . $param['nopo'] . "',kodesupplier='" . $param['supplier'] . "',tanggalinvoice='" . tanggalsystemn($param['tanggalinvoice']) . "',nofp='" . $param['nofp'] . "',tanggalnofp='" . tanggalsystemn($param['tanggalnofp']) . "',npwp='" . $param['npwp'] . "',npwppph='" . $param['npwppph'] . "'
				
				where noinvoice='" . $param['noinvoice'] . "'";
				$owlPDO->exec($str);

				# Cek jika tipearuskasht di update
				if ($param['tipearuskasht'] != $param['tipearuskashtold']) {
					$str = "update " . $dbname . ".keu_tagihandt set tipearuskas='" . $param['tipearuskasht'] . "' where noinvoice='" . $param['noinvoice'] . "' and nopo='" . $param['nopo'] . "'";
					$owlPDO->exec($str);
				}

				$str = "update " . $dbname . ".keu_tagihandt set noinvoice='" . $param['noinvoice'] . "' where noinvoice='' and nopo='" . $param['nopo'] . "'";
				$owlPDO->exec($str);
			}
			## AUTO INSERT DOCUMENT
			if (($param['tipeinvoice'] == 'ffb') or ($param['tipeinvoice'] == 'ffbfee')) {
				$str = "update " . $dbname . ".keu_tagihandt set keterangan='" . $param['keterangan'] . "' where noinvoice='" . $param['noinvoice'] . "'";
				$owlPDO->exec($str);
			}

			//insertefillinv($param['noinvoice'], $param['tipeinvoice']);

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warning: Gagal melakukan simpan data invoice \n" . addslashes($e->getMessage());
		}

		if ($param['noinvoice'] == '') {
			exit("Warning: GAGAL MENYIMPAN HT");
		}
		//exit('error'.$param['tipeinvoice']);



		echo $param['noinvoice'];

		break;
}

function insertefillinv($noinvoice, $tipeinvoice)
{
	global $dbname;
	global $owlPDO;
	global $path;

	$str = "select nopo from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
	$res = fetchdata($str);
	$nopo = $res[0]['nopo'];

	$arrdt = array();
	$str = "select distinct(notransaksi) as notransaksi from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "'";
	$arrdt = fetchdata($str);

	if ($tipeinvoice == 'p') {
		## BEGIN INSERT PO
		$kriteriaefil = "EPO";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);
		$efilename = $kriteriaefil . "_" . $noinvoice . "_" . $nopo . ".pdf";
		$str = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
		$owlPDO->exec($str);

		$_GET['table'] = "log_poht";
		$_GET['column'] = $nopo;
		$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

		include("log_slave_print_detail_po.php");

		unset($_GET['table']);
		unset($_GET['column']);
		unset($_GET['urlefil']);
		## END INSERT PO

		## BEGIN INSERT GRN
		$kriteriaefil = "EGRN";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);
		foreach ($arrdt as $val) {
			if ($val['notransaksi'] != '') {
				$efilename = $kriteriaefil . "_" . $noinvoice . "_" . $val['notransaksi'] . ".pdf";
				$str = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
				$owlPDO->exec($str);

				$_GET['table'] = "log_transaksiht";
				$_GET['column'] = $nopo;
				$_GET['notransaksi'] = $val['notransaksi'];
				$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

				include("log_slave_print_bapb_pdf.php");

				unset($_GET['table']);
				unset($_GET['column']);
				unset($_GET['notransaksi']);
				unset($_GET['urlefil']);
			}
		}
		## END INSERT GRN
	}

	if ($tipeinvoice == 'um') {
		## BEGIN INSERT PO
		$kriteriaefil = "EPO";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);
		$efilename = $kriteriaefil . "_" . $noinvoice . "_" . $nopo . ".pdf";
		$str = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
		$owlPDO->exec($str);

		$_GET['table'] = "log_poht";
		$_GET['column'] = $nopo;
		$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

		include("log_slave_print_detail_po.php");

		unset($_GET['table']);
		unset($_GET['column']);
		unset($_GET['urlefil']);
		## END INSERT PO
	}



	if ($tipeinvoice == 'bas') {
		## BEGIN 
		$kriteriaefil = "EBAC";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);
		foreach ($arrdt as $val) {
			if ($val['notransaksi'] != '') {
				$efilename = $kriteriaefil . "_" . $noinvoice . "_" . $val['notransaksi'] . ".pdf";
				$str = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
				$owlPDO->exec($str);

				$_GET['method'] = "previewbapdf";
				$_GET['notransaksi'] = $val['notransaksi'];
				$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

				include("log_slave_grnpdfefil.php");

				unset($_GET['method']);
				unset($_GET['notransaksi']);
				unset($_GET['urlefil']);
			}
		}
	}


	#= buat PON
	if ($tipeinvoice == 'pon') {
		## BEGIN INSERT PO
		$kriteriaefil = "EPO";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);
		$efilename = $kriteriaefil . "_" . $noinvoice . "_" . $nopo . ".pdf";
		$str = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
		$owlPDO->exec($str);

		$_GET['table'] = "log_poht";
		$_GET['column'] = $nopo;
		$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

		include("log_slave_print_detail_po.php");

		unset($_GET['table']);
		unset($_GET['column']);
		unset($_GET['urlefil']);
		## END INSERT PO


		#= cek hanya SO yang boleh insert BA
		$str = "select tipepo from " . $dbname . ".log_poht where nopo='" . $nopo . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tipepo = $bar['tipepo'];
		}

		## BEGIN INSERT GRN		
		$kriteriaefil = "EGRN";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);

		$kriteriaefil = "EBAC";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);

		foreach ($arrdt as $val) {
			if ($val['notransaksi'] != '') {
				$kriteriaefil = "EGRN";
				#= insert GRN
				$efilename = $kriteriaefil . "_" . $noinvoice . "_GR_" . $val['notransaksi'] . ".pdf";
				$strx = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
				$owlPDO->exec($strx);

				$_GET['method'] = "previewpdfgr";
				$_GET['notransaksi'] = $val['notransaksi'];
				$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

				include("log_slave_grnpdfefil.php");
				// include("log_slave_noninventory.php");

				unset($_GET['method']);
				unset($_GET['notransaksi']);
				unset($_GET['urlefil']);

				#= jika tipe SO 
				#= insert BA
				if ($tipepo == 'SO') {
					$kriteriaefil = "EBAC";
					$efilename = $kriteriaefil . "_" . $noinvoice . "_BA_" . $val['notransaksi'] . ".pdf";
					$strx = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
					$owlPDO->exec($strx);

					$_GET['method'] = "previewpdfgrba";
					$_GET['notransaksi'] = $val['notransaksi'];
					$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

					include("log_slave_grnpdfefil.php");
					// include("log_slave_noninventory.php");

					unset($_GET['method']);
					unset($_GET['notransaksi']);
					unset($_GET['urlefil']);
				}
			}
		}
		## END INSERT GRN
	}



	#= rtg
	if ($tipeinvoice == 'rtg') {
		#= ambil dokumen gudang aja
		foreach ($arrdt as $val) {
			if ($val['notransaksi'] != '') {
				// exit("Error:".$val['notransaksi']);
				$kriteriaefil = "EGRN";
				#= insert GRN
				$efilename = $kriteriaefil . "_" . $noinvoice . "_GI_" . $val['notransaksi'] . ".pdf";
				// exit("Error:".$efilename);
				$strx = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
				$owlPDO->exec($strx);

				// $_GET['method'] = "previewpdfgr";
				$_GET['notransaksi'] = $val['notransaksi'];
				$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

				include("log_slave_print_retur_supplier_pdfharga.php");
				// include("log_slave_noninventory.php");

				unset($_GET['method']);
				unset($_GET['notransaksi']);
				unset($_GET['urlefil']);
			}
		}
	}


	if ($tipeinvoice == 'ffbfee') {
		## BEGIN INSERT PO
		$kriteriaefil = "EPO";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);
		$efilename = $kriteriaefil . "_" . $noinvoice . "_" . $nopo . ".pdf";


		$strx = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
		$owlPDO->exec($strx);


		$_GET['method'] = "pdf";
		$_GET['notrans'] = $nopo;
		$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

		include("pmn_slave_feetbs.php");

		unset($_GET['method']);
		unset($_GET['notrans']);
		unset($_GET['urlefil']);
	}

	if ($tipeinvoice == 'ffb') {
		## BEGIN INSERT PO
		$kriteriaefil = "EPO";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);
		$efilename = $kriteriaefil . "_" . $noinvoice . "_" . $nopo . ".pdf";


		$strx = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
		$owlPDO->exec($strx);

		$_GET['tipetbs'] = "";
		$_GET['table'] = "kebun_tbskud";
		$_GET['method'] = "pdf3";
		$_GET['notransaksi'] = $nopo;
		$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

		include("kebun_tbskud_slave.php");

		unset($_GET['table']);
		unset($_GET['method']);
		unset($_GET['urlefil']);
		unset($_GET['notransaksi']);
	}



	if ($tipeinvoice == 'ffbe') {
		## BEGIN INSERT PO
		$kriteriaefil = "EPO";
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' and kriteriaefil='" . $kriteriaefil . "'";
		$owlPDO->exec($str);
		$efilename = $kriteriaefil . "_" . $noinvoice . "_" . $nopo . ".pdf";


		$strx = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $efilename . "','.pdf','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
		$owlPDO->exec($strx);

		$_GET['tipetbs'] = "";
		$_GET['table'] = "kebun_tbsexternal";
		$_GET['method'] = "pdf";
		$_GET['notransaksi'] = $nopo;
		$_GET['urlefil'] = $path . str_replace('/', '', $efilename);

		include("kebun_tbsexternal_slave.php");

		unset($_GET['table']);
		unset($_GET['method']);
		unset($_GET['urlefil']);
		unset($_GET['notransaksi']);
	}



	//20211013064456 20211000003-GRNI-BPJM.pdf

}
// function headerupdate($noinvoice,$noakun,$nilai,$proses){
// 	global $dbname;
// 	global $owlPDO;

// 	$kolomupdate='';
// 		$nilaidpp=0;
// 		$nilaiinvoice=0;
// 		if ($noakun=='1170111') {
// 			$str="select * from keu_tagihanht where noinvoice='".$noinvoice."' ";
// 				$res=fetchdata($str);
// 				if ($proses!='deletedt'){
// 					$nilaiinvoice=$nilai+$res[0]['nilaidpp'];
// 				}else {
// 					$nilaiinvoice=$res[0]['nilaiinvoice']-$nilai;
// 				}
// 				$kolomupdate="nilaiinvoice='".$nilaiinvoice."'";


// 		}else{

// 			if ($nilai>0) { 
// 				$str="select * from keu_tagihandt where noinvoice='".$noinvoice."' and noakun='1170111' ";
// 				$res=fetchdata($str);
// 				$str2="select sum(nilai) as nilai from keu_tagihandt where noinvoice='".$noinvoice."' and noakun<>'1170111' and nilai >0 ";
// 				$res2=fetchdata($str2);
// 				if ($proses!='deletedt'){
// 					$nilaiinvoice=$res[0]['nilai']+$nilai;
// 				}else{
// 					$str="select * from keu_tagihanht where noinvoice='".$noinvoice."' ";
// 					$res=fetchdata($str);
// 					$nilaiinvoice=$res[0]['nilaiinvoice']-$nilai;
// 					if(count($res2[0]['nilai'])==0) {
// 						$res2[0]['nilai']=0;
// 					}
// 				}
// 				$nilaidpp=$res2[0]['nilai'];	
// 				$kolomupdate="nilaidpp='".$nilaidpp."',nilaiinvoice='".$nilaiinvoice."'";
// 			}
// 		}
// 		if($kolomupdate!='') {
// 			$str = "update ".$dbname.".keu_tagihanht set ".$kolomupdate."  where  noinvoice='".$noinvoice."' ";  
// 			try{
// 				$owlPDO->exec($str);
// 			}
// 			catch (PDOException $e)
// 			{
// 				print " Gagal  !: " . $e->getMessage() . "\n";
// 				die();
// 			}
// 			if ($nilaidpp>0) {

// 				$nilaidpp=number_format($nilaidpp);
// 			}
// 			if ($nilaiinvoice>0) {

// 				$nilaiinvoice=number_format($nilaiinvoice);
// 			}
// 		}
// 		return $nilaidpp."####".$nilaiinvoice;
// }
