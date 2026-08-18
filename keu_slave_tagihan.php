<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
$proses=($_GET['proses'] == '' ? $_POST['proses'] : $_GET['proses']);
$param=$_POST;
$optNmsupp=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optket=makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');
//cari nama orang
$str=$owlPDO->query("select karyawanid, namakaryawan from ".$dbname.".datakaryawan");
$str->setFetchMode(PDO::FETCH_OBJ);
while ($bar=$str->fetch()) {
	$nama[$bar->karyawanid]=$bar->namakaryawan;
}
switch ($proses) {
case 'getunit':
	 # Options
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['kdpt']."' and kodeorganisasi in (".getOrgDetail(2).")";
	$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$arrUnit="<option value=''></option>";
	while ($bar=$res->fetch()) {
		$arrUnit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
	}
	echo $arrUnit;
	break;
case 'getnpwp':
	 # Options
	$str="select npwp from ".$dbname.".setup_org_npwp where kodeorg='".$param['kodeorg']."'";
	$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$arrnpwp="<option value=''></option>";
	while ($bar=$res->fetch()) {
		$arrnpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
	}
	echo $arrnpwp;
	break;
case 'showformfp':
	$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>
		<tr  class=rowcontent>
		<td>".$_SESSION['lang']['historynofp']."</td>
		<td>:</td>
		<td  colspan=2><input type=text id=historynofp onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:150px;\" value='' /></td>
		</tr>
		<tr  class=rowcontent>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext readonly  id=historytanggalfp onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\" value=''    />
		<button class=mybutton onclick=savefp('".$param['noinvoice']."','".$param['row']."')>Simpan</button></td>
		</tr>
		</table>";
	echo $tab;
	break;
case 'savefp':
	$strht="update ".$dbname.".keu_tagihanht set historynofp='".$param['historynofp']."',historytanggalfp='".tanggalsystem($param['historytanggalfp'])."' where noinvoice='".$param['noinvoice']."'";
	try {
		$owlPDO->exec($strht);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
	 # Daftar Header
case 'showHeadList':
	$where=" 1=1 ";
	if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
		$where.="";
	} else {
		$where.=" and a.unit='".$_SESSION['empl']['lokasitugas']."'";
	}
	$where.="and a.unit in (".getOrgDetail(2).")";
	// $where.= " and a.kodeorg='".$_SESSION['org']['kodeorganisasi']."' and updateby='".$_SESSION['standard']['userid']."'";
	//if($_SESSION['empl']['kodejabatan']==5)$where="a.kodeorg like '%' and updateby like '%'";
	if (isset($param['where'])) {
		$tmpW=str_replace('\\', '', $param['where']);
		$arrWhere=json_decode($tmpW, true);
		if (!empty($arrWhere)) {
			foreach($arrWhere as $key => $r1) {
				if ($r1[0] == 'namasupplier') {
					$where.=" and b.".$r1[0]." like '%".$r1[1]."%'";
				} else {
					$where.=" and a.".$r1[0]." like '%".$r1[1]."%'";
				}
			}
		}
	}
	 # Header
	$header=array(
			$_SESSION['lang']['noinvoice'], $_SESSION['lang']['noinvoice']." Supplier", $_SESSION['lang']['pt'], $_SESSION['lang']['tanggalterima'], 'Last Update',
			$_SESSION['lang']['nopo'], $_SESSION['lang']['supplier'], $_SESSION['lang']['keterangan'],
			$_SESSION['lang']['subtotal'], 'postingby');
	$arrJenisTag=makeOption($dbname, 'keu_5jenistagihan', 'kode,jurnal');
	 # Content
	$cols="a.noinvoice,a.noinvoicesupplier,a.kodeorg,a.tanggal,a.updateby,a.nopo,
		b.namasupplier,a.kodesupplier,a.keterangan,a.nilaiinvoice,a.postingby,a.posting";
	$order="a.tanggal desc";
	$queryRow="select count(*) as rows";
	$query=" from ".$dbname.".keu_tagihanht a
		left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid
		where ".$where." order by ".$order;
	$queryRow.=$query;
	if (!is_null($param['shows'])) {
		if (!is_null($param['page'])) {
			$startFrom=($param['page'] - 1) * $param['shows'];
		} else {
			$startFrom=0;
		}
		$query.=" limit ".$startFrom.",".$param['shows'];
	}
	$query="select ".$cols.$query;
	$tmpTotal=fetchData($queryRow);
	$data=fetchData($query);
	$totalRow=$tmpTotal[0]['rows'];
	// Get Akun Ppn
	$qAkun=selectQuery($dbname, 'setup_parameterappl', 'nilai',
			"kodeaplikasi='TX' and kodeparameter='PPNINV'");
	$resAkun=fetchData($qAkun);
	// List of Invoice
	$listInv='';
	foreach($data as $key => $row) {
		$wrhtipe="noinvoice='".$row['noinvoice']."'";
		$arrCekTag=makeOption($dbname, 'keu_tagihanht', 'noinvoice,tipeinvoice', $wrhtipe);
		if ($arrJenisTag[$arrCekTag[$row['noinvoice']]] == 0) {
			if (!empty($listInv)) {
				$listInv.=",";
			}
			$listInv.="'".$row['noinvoice']."'";
		}
	}
	$optDet=array();
	// Sum Akun Ppn (Detail Tagihan)
	if (empty($resAkun)or empty($listInv)) {
		$optDet=array();
	} else {
		if ($listInv != '') {
			$optDet=makeOption($dbname, 'keu_tagihandt', "noinvoice,nilai", "noinvoice in (".$listInv.") and noakun='".$resAkun[0]['nilai']."'");
		}
	}
	foreach($data as $key => $row) {
		// Add Ppn
		if (isset($optDet[$row['noinvoice']]))
			$row['nilaiinvoice'] += $optDet[$row['noinvoice']];
		// if($row['posting']==1) {
		// $data[$key]['switched']=true;
		// }
		if ($row['posting'] == 1) {
			$data[$key]['switched']=true;
			$data[$key]['noSwitchList'][]="showEdit";
			$data[$key]['noSwitchList'][]="deleteData";
		} else {
			if ($_SESSION['standard']['userid'] == $row['updateby']) {
				// unset($data[$key]['noAction']);
			} else {
				$data[$key]['noSwitchList'][]="showEdit";
				$data[$key]['noSwitchList'][]="deleteData";
			}
		}
		if ($row['historynofp'] != '') {
			$data[$key]['switched']=true;
			$data[$key]['noSwitchList'][]="showEdit";
		}
		if ($data[$key]['namasupplier'] == '') {
			$optSp=makeOption($dbname, 'log_5klsupplier', 'tipe,noakun', "tipe='".$data[$key]['kodesupplier']."'");
			$data[$key]['namasupplier']=$optSp[$data[$key]['kodesupplier']];
		}
		unset($data[$key]['kodesupplier']);
		unset($data[$key]['posting']);
		$data[$key]['tanggal']=tanggalnormal($row['tanggal']);
		$data[$key]['nilaiinvoice']=number_format($row['nilaiinvoice'], 2);
		$data[$key]['updateby']=$nama[$row['updateby']];
		$data[$key]['postingby']=isset($nama[$row['postingby']]) ? $nama[$row['postingby']] : '-';
	}
	//    foreach($data as $c=>$key) {
	//        $sort_noaku[]=$key['tanggal'];
	//        $sort_tangg[]=$key['noinvoice'];
	//    }
	//    array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $data);
	//    array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $isidata);
	 # Make Table
	$tHeader=new rTable('headTable', 'headTableBody', $header, $data);
	$tHeader->addAction('showEdit', 'Edit', 'images/'.$_SESSION['theme']."/edit.png");
	if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL' or $_SESSION['empl']['kodejabatan'] == 117 or $_SESSION['empl']['kodejabatan'] == 119 or $_SESSION['empl']['kodejabatan'] == 98) {
		$tHeader->addAction('deleteData', 'Delete', 'images/'.$_SESSION['theme']."/delete.png");
	} else { //hanya HO dan region yang boleh menghapus
		$tHeader->addAction('', 'Delete', 'images/'.$_SESSION['theme']."/delete.png");
	}
	$tHeader->addAction('postingData', 'Posting', 'images/'.$_SESSION['theme']."/posting.png");
	$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
	$tHeader->addAction('detailPDF', 'Print Data Detail', 'images/'.$_SESSION['theme']."/pdf.jpg");
	$tHeader->_actions[3]->addAttr('event');
	$tHeader->pageSetting($param['page'], $totalRow, $param['shows']);
	$tHeader->addAction('viewDetailData2', 'Print Data Detail', 'images/'.$_SESSION['theme']."/zoom.png");
	$tHeader->_actions[4]->addAttr('event');
	$tHeader->addAction('fakturpajak', 'Faktur Pajak', 'images/'.$_SESSION['theme']."/plus.png");
	$tHeader->_switchException=array('detailPDF', 'viewDetailData2');
	if (isset($param['where'])) {
		$tHeader->setWhere($arrWhere);
	}
	 # View
	$tHeader->renderTable();
	break;
	 # Form Add Header
case 'showAdd':
	// View
	echo formHeader('add', array());
	echo "<div id='detailField' style='clear:both'></div>";
	break;
	 # Form Edit Header
case 'showEdit':
	$query=selectQuery($dbname, 'keu_tagihanht', "*", "noinvoice='".$param['noinvoice']."'");
	$tmpData=fetchData($query);
	$data=$tmpData[0];
	$data['tanggal']=tanggalnormal($data['tanggal']);
	$data['jatuhtempo']=tanggalnormal($data['jatuhtempo']);
	echo formHeader('edit', $data);
	echo "<div id='detailField' style='clear:both'></div>";
	break;
	 # Proses Add Header
case 'add':
	$data=$_POST;
	// echo"<pre>";
	// print_r($data);
	// echo"</pre>";
	// exit('warning');
	 # mengambil total rupiah dari sumber PO
	if ($data['fileupload'] != '') {
		if ($_FILES['file']['error'] == 0) {
			$filetype=strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
			$filename=$data['noinvoice']."".$filetype;
			$file_tmpname=$_FILES['file']['tmp_name'];
			if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf')) {
				if ($_FILES['file']['size'] <= 250000) {
					move_uploaded_file($file_tmpname, "filegis/$filename");
				} else {
					exit("warning : Ukuran file upload maksimal 250kb");
				}
			} else {
				exit("Warning : Format file upload harus .jpg atau .jpeg");
			}
		}
	}
	unset($data['file']);
	unset($data['fileupload']);
	if (($data['tipeinvoice'] == 'tck') || ($data['tipeinvoice'] == 'tpk')) {
		// exit('warning : '.$data['nopo']);
		$sJumlah="select noakun,sum(jumlah) as jumlah,noreferensi as nodo,kodesupplier from ".$dbname.".keu_jurnaldt
			where noakun like '21%'  and kodeorg='".$param['unit']."' and noreferensi='".$data['nopo']."' group by noreferensi,noakun";
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
		//$nilaigross=$rCek2['jmlhpo'];
		foreach($rtrk as $key => $val) {
			if ($val['pphditanggung'] == '1') {
				// $nilaigross=($val['jumlah'] * (100 / (100-$val['subsidi'])));
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
		$sJmlhDt="select sum(nilaiinvoice) as jumlah,nopo as nodo,kodesupplier from ".$dbname.".keu_tagihanht
			where unit='".$param['unit']."' and tipeinvoice='".$data['tipeinvoice']."' and nopo='".$data['nopo']."' group by nopo,kodesupplier";
		$rJmlhDt=fetchData($sJmlhDt);
		foreach($rJmlhDt as $key => $val) {
			$jmlInv=$val['jumlah'];
		}
	} else if ($data['tipeinvoice'] == 'ram') {
		$optPO=makeOption($dbname, 'keu_persediaantbs_vw', 'notransaksi,kodesupplier', "notransaksi='".$data['nopo']."'");
		$optHo=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='".$param['unit']."'");
		//jmlh po di dari po
		//  $a="select totalrupiah,rupiahpajakditanggung from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$data['nopo']."' and kodesupplier='".$data['kodesupplier']."'";
		$strH="select concat(left(datein,10),'/',kodesupplier) as notransaksi, left(datein,10) as tanggal, kodesupplier as koderamp, beratmasuk, beratkeluar, potongan, harga, beban_pajak, persenpajak from ".$dbname.".pmn_penerimaantbsramp
			where concat(left(datein,10),'/',kodesupplier)='".$data['nopo']."' and kodesupplier='".$data['kodesupplier']."' ";
		//echo $strH;
		$resH=fetchData($strH);
		$data['nilaiinvoice']=0;
		foreach($resH as $key => $barH) {
			$potongan=round($barH['potongan']);
			// $potongan=($barH['beratmasuk'] - $barH['beratkeluar'] - $barH['potongan']);
			// $dataAp=explode(".",$potongan);
			// if((intval($dataAp[1])>=1)&&(intval($dataAp[1])<=5)){
			// $dtNetto=floor($potongan);
			// }else{
			// $dtNetto=round($potongan,0);
			// }
			$jumlah=($barH['beratmasuk'] - $barH['beratkeluar'] - $potongan) * $barH['harga'];
			// $jumlah= $dtNetto * $barH['harga'];
			$rupiahpajakditanggung=0;
			$rpgross=$jumlah;
			if ($barH['beban_pajak'] == '1') {
				$rpgross=($jumlah * (100 / (100 - $barH['persenpajak'])));
				$rupiahpajakditanggung=($rpgross * $barH['persenpajak']) / 100;
			}
			$rCek2['jmlhpo'] += $rpgross;
			$rCek2['ppn'] += $rupiahpajakditanggung;
			$data['nilaiinvoice'] += ($rpgross);
		}
		//exit("Error".$rCek2['totalrupiah']._.$a);
	} else if ($data['tipeinvoice'] == 'ffb') {
		$optPO=makeOption($dbname, 'keu_persediaantbs_vw', 'notransaksi,kodesupplier', "notransaksi='".$data['nopo']."'");
		//jmlh po di dari po
		//  $a="select totalrupiah,rupiahpajakditanggung from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$data['nopo']."' and kodesupplier='".$data['kodesupplier']."'";
		$strH="select total_terima, harga_perkg, beban_pajak, persenpajak, totalrupiah, rupiahpajakditanggung, notransaksi, tanggal, kodeho, kodesupplier from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$data['nopo']."' and kodesupplier='".$data['kodesupplier']."' ";
		// $sCek2=$owlPDO->query("select sum(totalrupiah) as jmlhpo,sum(rupiahpajakditanggung) as ppn from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$data['nopo']."' and kodesupplier='".$data['kodesupplier']."' ");
		$resH=$owlPDO->query($strH);
		$resH->setFetchMode(PDO::FETCH_ASSOC);
		$data['nilaiinvoice']=0;
		while ($barH=$resH->fetch()) {
			$rupiahpajakditanggung=0;
			$rpgross=$barH['totalrupiah'];
			if ($barH['beban_pajak'] == '1') {
				$rpgross=($barH['totalrupiah'] * (100 / (100 - $barH['persenpajak'])));
				$rupiahpajakditanggung=($rpgross * $barH['persenpajak']) / 100;
			}
			$rCek2['jmlhpo'] += $rpgross;
			$rCek2['ppn'] += $rupiahpajakditanggung;
			$data['nilaiinvoice'] += ($rpgross);
		}
		//exit("Error".$rCek2['totalrupiah']._.$a);
	} else if ($data['tipeinvoice'] == 'p') {
		$optPO=makeOption($dbname, 'log_poht', 'nopo,kodesupplier', "stat_release=1 and nopo='".$data['nopo']."'");
		//jmlh po di dari po
		$sCek2=$owlPDO->query("select distinct  nilaipo as jmlhpo,ppn from ".$dbname.".log_poht where nopo='".$data['nopo']."' ");
		$sCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek2=$sCek2->fetch();
	} else if ($data['tipeinvoice'] == 's') {
		$optPO=makeOption($dbname, 'log_suratjalanht', 'nosj,expeditor');
		$rCek2['jmlhpo']=0;
		$rCek2['ppn']=0;
	} else if ($data['tipeinvoice'] == 'b') {
		$optPO=makeOption($dbname, 'log_biayakirim', 'nodok,kodetrp');
		$rCek2['jmlhpo']=0;
		$rCek2['ppn']=0;
		$sCek2=$owlPDO->query("select distinct jumlah as jmlhpo from ".$dbname.".log_biayakirim where nodok='".$data['nopo']."' ");
		$sCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek2=$sCek2->fetch();
	} else if ($data['tipeinvoice'] == 'k') {
		// $sCek2=$owlPDO->query("select distinct nilaikontrak as jmlhpo from ".$dbname.".log_spkht where notransaksi='".$data['nopo']."' ");
		$sCek2=$owlPDO->query("select sum(jumlahrealisasi) as  jmlhpo from ".$dbname.".log_baspk where statusjurnal=1 and notransaksi='".$data['nopo']."' ");
		$sCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek2=$sCek2->fetch();
		 # ppn
		$iPn=$owlPDO->query("select sum(nilai) as jumppn from ".$dbname.".log_spk_tax where noakun='1160100' and notransaksi='".$data['nopo']."' ");
		$iPn->setFetchMode(PDO::FETCH_ASSOC);
		$dPn=$iPn->fetch();
		 # pph
		$iPh=$owlPDO->query("select sum(nilai) as jumpph from ".$dbname.".log_spk_tax where noakun!='1160100' and notransaksi='".$data['nopo']."' ");
		$iPn->setFetchMode(PDO::FETCH_ASSOC);
		$dPh=$iPn->fetch();
		$rCek2['jmlhpo']=$rCek2['jmlhpo'] + $dPn['jumppn'] - $dPh['jumpph'];
		$optPO=makeOption($dbname, 'log_spkht', 'notransaksi,koderekanan');
	} else if ($data['tipeinvoice'] == 'um') {
		$optPO=makeOption($dbname, 'log_poht', 'nopo,kodesupplier', "stat_release=1 and nopo='".$data['nopo']."'");
	} else {
		$optPO=makeOption($dbname, 'log_5supplier', 'supplierid,supplierid');
	}
	$optJenis=makeOption($dbname, 'keu_5jenistagihan', 'kode,jurnal');
	if ($optJenis[$data['tipeinvoice']] == 0) { //jika jenis memiliki jurnal sebelumya wajib terisi nopo
		if ($data['nopo'] == '') {
			exit('warning'.$_SESSION['lang']['notifpopilih']);
		}
		if ($data['noinvoicesupplier'] == '') {
			 @ $warning.="Invoice supplier number is obligatory\n";
		}
		 # ambil nilai rp
		if ($data['tipeinvoice'] == 'h') {
			$sCek2=$owlPDO->query("select bebanperusahaan as jmlhpo from ".$dbname.".sdm_pengobatanht where notransaksi='".$data['nopo']."'");
			$sCek2->setFetchMode(PDO::FETCH_ASSOC);
			$rCek2=$sCek2->fetch();
		}
	} else {
		if ($data['status_bayar'] == '1') {
			exit('warning: '.$_SESSION['lang']['pembayaran'].' via Financing tidak di ijinkan untuk jenis tagihan ini');
		}
		if ($data['noinvoicesupplier'] == '') {
			$warning.="Invoice supplier number is obligatory\n";
		}
		if ($data['unit'] == '') {
			$warning.="Unit  is obligatory\n";
		}
		if ($warning != '') {
			echo "Warning :\n".$warning;
			exit;
		}
		$sCek2=$owlPDO->query("select nilaiinvoice as jmlhpo from ".$dbname.".keu_tagihanht where noinvoicesupplier='".$data['noinvoicesupplier']."'");
		$sCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek2=$sCek2->fetch();
		if (($rCek2['jmlhpo'] == 0) || ($rCek2['jmlhpo'] == '')) {
			$data['nilaiinvoice']=str_replace(",", "", $data['nilaiinvoice']);
			$rCek2['jmlhpo']=$data['nilaiinvoice'];
		}
		$optAkun=makeOption($dbname, 'log_5klsupplier', 'tipe,noakun');
		$data['noakun']=$optAkun[substr($data['kodesupplier'], 0, 4)];
	}
	// Error Trap
	$warning="";
	if ($data['noinvoice'] == '') {
		$warning.="Invoice number is obligatory\n";
	}
	if ($data['tanggal'] == '') {
		$warning.="Date is obligatory\n";
	}
	if ($warning != '') {
		echo "Warning :\n".$warning;
		exit;
	}
	// $data['tipeinvoice']=substr($data['tipeinvoice'],0,1);
	$data['tipeinvoice']=$data['tipeinvoice'];
	$data['tanggal']=tanggalsystem($data['tanggal']);
	$data['tanggalinvoice']=tanggalsystem($data['tanggalinvoice']);
	$data['tanggalnofp']=tanggalsystem($data['tanggalnofp']);
	$data['nilaiinvoice']=str_replace(',', '', $data['nilaiinvoice']);
	$data['uangmuka']=str_replace(',', '', $data['uangmuka']);
	//$data['nilaippn']=str_replace(',','',$data['nilaippn']);
	//$data['nilaippn']=$rCek2['ppn'];
	$data['nilaippn']=0;
	if ($data['jatuhtempo'] != '') {
		$data['jatuhtempo']=tanggalsystem($data['jatuhtempo']);
	} else {
		$data['jatuhtempo']='0000-00-00';
	}
	//if(empty($optPO)) {
	if ($data['kodesupplier'] == '') {
		exit('Warning : '.$_SESSION['lang']['notifkodesupplier'].': '.$data['nopo']);
	}
	//$data['kodesupplier']=isset($optPO[$data['nopo']])? $optPO[$data['nopo']]: '';
	$data['updateby']=$_SESSION['standard']['userid'];
	if ($optJenis[$data['tipeinvoice']] == 0) {
		//jmlh po di invoice
		$sCek=$owlPDO->query("select distinct sum(nilaiinvoice) as jmlhinvoice from ".$dbname.".keu_tagihanht
				where nopo='".$data['nopo']."' "
				." and tipeinvoice='".$data['tipeinvoice']."'");
		$sCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=$sCek->fetch();
		//jmlh ppn di invoice
		$sPpn=$owlPDO->query("select distinct sum(a.nilai) as jmlhppn from ".$dbname.".keu_tagihandt a
				left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
				where b.nopo='".$data['nopo']."' and tipeinvoice='".$data['tipeinvoice']."'");
		$sPpn->setFetchMode(PDO::FETCH_ASSOC);
		$rPpn=$sPpn->fetch();
		$jmlInv=$rCek['jmlhinvoice'] + $rPpn['jmlhppn'];
	} else {
		//jmlh po di invoice
		$sCek=$owlPDO->query("select distinct sum(a.nilai) as jmlhppn from ".$dbname.".keu_tagihandt a
				left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
				where b.noinvoicesupplier='".$data['noinvoicesupplier']."' and tipeinvoice='".$data['tipeinvoice']."'");
		$sCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=$sCek->fetch();
		$jmlInv=$rCek['jmlhppn'];
	}
	$a=$jmlInv;
	$b=$data['nilaiinvoice'];
	$c=$rCek2['jmlhpo'];
	//exit("Error:$a._.$b._.$c".$rCek2['jmlhpo']);
	//exit("Error".$selisih._.$jmlInv._.$data['nilaiinvoice']._.$rCek2['jmlhpo']);
	if (($data['tipeinvoice'] != 'tck') || ($data['tipeinvoice'] != 'tpk')) {
		 # nanti tolong hapus setelah dah perbaikan script
		$selisih=($jmlInv + $data['nilaiinvoice']);
		// if((number_format($jmlInv+$data['nilaiinvoice']))>number_format($rCek2['jmlhpo'])){
		if ($data['tipeinvoice'] != 'um') {
			if (number_format($jmlInv + $data['nilaiinvoice'], 2) > number_format($rCek2['jmlhpo'], 2)) {
				exit("Warning: ".$_SESSION['lang']['notifnilainvoice'].". Total Nila Invoice :".number_format($selisih, 2).", Nilai PO/Kontrak/Document:".number_format($rCek2['jmlhpo'], 2));
			}
		}
		$data['noakun']='2111201';
	}
	if ($data['tipeinvoice'] == 'as' || $data['tipeinvoice'] == 'sw') {
		$strutin="select noakun_kredit, noakun_debet from ".$dbname.".keu_transaksi_rutin where notransaksi='".$data['nopo']."'";
		$trutin=$owlPDO->query($strutin)or die(print " Gagal: ".PDOException::getMessage());
		$trutin->setFetchMode(PDO::FETCH_ASSOC);
		$rrutin=$trutin->fetch();
		$data['noakun']=$rrutin['noakun_kredit'];
		$noakun=$rrutin['noakun_debet'];
	}
	// Insert Header
	$cols=array();
	foreach($data as $key => $row) {
		$cols[]=$key;
	}
	$query=insertQuery($dbname, 'keu_tagihanht', $data, $cols);
	try {
		$owlPDO->exec($query);
	} catch (PDOException $e) {
		print " Gagal, DB Error  1!: ".$e->getMessage()."<br/>";
		die();
	}
	 # khusus PO dan Kontraktor
	 # insert uang muka di dt
	if (substr($data['tipeinvoice'], 0, 1) == 'p' || $data['tipeinvoice'] == 'k') {
		//uang muka
		$umuka=0;
		$sC="select sum(b.nilai) as jmlhum from ".$dbname.".keu_tagihanht a left join ".$dbname.".keu_tagihandt b on a.noinvoice=b.noinvoice where a.nopo='".$data['nopo']."' and a.tipeinvoice='um'";
		$tC=$owlPDO->query($sC)or die(print " Gagal: ".PDOException::getMessage());
		$tC->setFetchMode(PDO::FETCH_ASSOC);
		$rC=$tC->fetch();
		$umuka=$rC['jmlhum'];
		if ($umuka != 0) {
			//noakun
			$sC="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='UMPOKO'";
			$tC=$owlPDO->query($sC)or die(print " Gagal: ".PDOException::getMessage());
			$tC->setFetchMode(PDO::FETCH_ASSOC);
			$rC=$tC->fetch();
			$nilai=$rC['nilai'];
			$noakun=explode(',', $nilai);
			if (substr($data['tipeinvoice'], 0, 1) == 'p') {
				$noakun=$noakun[0];
			}
			if ($data['tipeinvoice'] == 'k') {
				$noakun=$noakun[1];
			}
			$ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset) values
				('".$data['noinvoice']."','".$noakun."','". - ($umuka)."','','')";
			try {
				$owlPDO->exec($ins);
			} catch (PDOException $e) {
				print " Gagal  !: ".$e->getMessage()."<br/>";
				die();
			}
		}
	}
	 # khusus Asuransi dan Sewa
	if ($data['tipeinvoice'] == 'as' || $data['tipeinvoice'] == 'sw') {
		$ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset) values
			('".$data['noinvoice']."','".$noakun."','".$data['nilaiinvoice']."','','')";
		try {
			$owlPDO->exec($ins);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."<br/>";
			die();
		}
	}
	 # khusus FFB
	 # get pphnya
	if ($data['tipeinvoice'] == 'ffb' || $data['tipeinvoice'] == 'ram') {
		// $str=" select noakun,jumlah from ".$dbname.".keu_jurnaldt_vw where substr(noakun,1,4)='2120' and noreferensi='".$data['nopo']."' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		if ($rCek2['ppn'] > 0) {
			$insert=" insert into ".$dbname.".keu_tagihandt (`noinvoice`, `noakun`, `nilai`)
				values ('".$data['noinvoice']."','2120200','".($rCek2['ppn'] * -1)."') ";
			try {
				$owlPDO->exec($insert);
			} catch (PDOException $e) {
				print " Gagal  !: ".$e->getMessage()."<br/>";
				die();
			}
		}
		// }
	}
	if (($data['tipeinvoice'] == 'tck') || ($data['tipeinvoice'] == 'tpk')) {
		if ($rCek2['ppn'] > 0) {
			$insert=" insert into ".$dbname.".keu_tagihandt (`noinvoice`, `noakun`, `nilai`) values ('".$data['noinvoice']."','2120300','".($rCek2['ppn'] * -1)."') ";
			try {
				$owlPDO->exec($insert);
			} catch (PDOException $e) {
				print " Gagal  !: ".$e->getMessage()."<br/>";
				die();
			}
		}
	}
	break;
	 # Proses Edit Header
case 'edit':
	$data=$_POST;
	$where="noinvoice='".$data['noinvoice']."'";
	$updateImage=false;
	if ($data['fileupload'] != '') {
		if ($_FILES['file']['error'] == 0) {
			$filetype=strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
			$filename=$data['noinvoice']."".$filetype;
			$file_tmpname=$_FILES['file']['tmp_name'];
			if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf')) {
				if ($_FILES['file']['size'] <= 512000) {
					$updateImage=true;
					move_uploaded_file($file_tmpname, "filegis/$filename");
				} else {
					exit("warning : Ukuran file upload maksimal 512kb");
				}
			} else {
				exit("Warning : Format file upload harus .jpg | .jpeg | .png | .pdf");
			}
		}
	}
	$optJenis=makeOption($dbname, 'keu_5jenistagihan', 'kode,jurnal');
	if ($optJenis[$data['tipeinvoice']] == 0) { //jika jenis memiliki jurnal wajib terisi nopo
		if ($data['nopo'] == '') {
			exit('warning'.$_SESSION['lang']['notifpopilih']);
		}
		 # ambil nilai rp
		if ($data['tipeinvoice'] == 'h') {
			$sCek2=$owlPDO->query("select bebanperusahaan as jmlhpo from ".$dbname.".sdm_pengobatanht where notransaksi='".$data['nopo']."'");
			$sCek2->setFetchMode(PDO::FETCH_ASSOC);
			$rCek2=$sCek2->fetch();
		}
	} else {
		if ($data['status_bayar'] == '1') {
			exit('warning: '.$_SESSION['lang']['pembayaran'].' via Financing tidak di ijinkan untuk jenis tagihan ini');
		}
		if ($data['noinvoicesupplier'] == '') {
			$warning.="Invoice supplier number is obligatory\n";
		}
		if ($warning != '') {
			echo "Warning :\n".$warning;
			exit;
		}
		$sCek2=$owlPDO->query("select nilaiinvoice as jmlhpo from ".$dbname.".keu_tagihanht where noinvoicesupplier='".$data['noinvoicesupplier']."'");
		$sCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek2=$sCek2->fetch();
		if (($rCek2['jmlhpo'] == 0) || ($rCek2['jmlhpo'] == '')) {
			$data['nilaiinvoice']=str_replace(",", "", $data['nilaiinvoice']);
			$rCek2['jmlhpo']=$data['nilaiinvoice'];
		}
	}
	$optAkun=makeOption($dbname, 'log_5klsupplier', 'tipe,noakun');
	$optTipe=makeOption($dbname, 'log_5supkelompok', 'supplierid,tipe', "supplierid='".$data['kodesupplier']."'");
	$data['noakun']=$optAkun[$optTipe[$data['kodesupplier']]];
	// $data['noakun']=$optAkun[substr($data['kodesupplier'],0,4)];
	// Error Trap
	$warning="";
	if ($data['noinvoice'] == '') {
		$warning.="Invoice number is obligatory\n";
	}
	if ($data['tanggal'] == '') {
		$warning.="Date is obligatory\n";
	}
	if ($warning != '') {
		echo "Warning :\n".$warning;
		exit;
	}
	//if(empty($optPO)) {
	//$data['kodesupplier']=isset($optPO[$data['nopo']])? $optPO[$data['nopo']]: '';
	unset($data['noinvoice']);
	unset($data['file']);
	unset($data['fileupload']);
	if ($data['tipeinvoice'] == 'p') {
		$optPO=makeOption($dbname, 'log_poht', 'nopo,kodesupplier', "stat_release=1 and nopo='".$data['nopo']."'");
		//jmlh po di dari po
		$sCek2=$owlPDO->query("select distinct  nilaipo as jmlhpo,ppn from ".$dbname.".log_poht where nopo='".$data['nopo']."' ");
		$sCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek2=$sCek2->fetch();
	} else if ($data['tipeinvoice'] == 's') {
		$optPO=makeOption($dbname, 'log_suratjalanht', 'nosj,expeditor');
		$rCek2['jmlhpo']=0;
	} else {
		$sCek2=$owlPDO->query("select distinct nilaikontrak as jmlhpo from ".$dbname.".log_spkht where notransaksi='".$data['nopo']."' ");
		$sCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek2=$sCek2->fetch();
		$optPO=makeOption($dbname, 'log_spkht', 'notransaksi,koderekanan');
		$rCek2['ppn']=0;
	}
	if ($updateImage == true) {
		$data['uploadinvoice']=isset($filename) ? $filename : "";
	} else {
		unset($data['uploadinvoice']);
	}
	$data['nilaippn']=$rCek2['ppn'];
	$data['tanggal']=tanggalsystem($data['tanggal']);
	$data['jatuhtempo']=tanggalsystem($data['jatuhtempo']);
	$data['tipeinvoice']=$data['tipeinvoice'];
	$data['nilaiinvoice']=str_replace(',', '', $data['nilaiinvoice']);
	$data['uangmuka']=str_replace(',', '', $data['uangmuka']);
	$data['updateby']=$_SESSION['standard']['userid'];
	$query=updateQuery($dbname, 'keu_tagihanht', $data, $where);
	// exit('warning : '.$query);
	try {
		$owlPDO->exec($query);
	} catch (PDOException $e) {
		print " Gagal, DB Error  2!: ".$e->getMessage()."<br/>";
		die();
	}
	break;
case 'delete':
	$where="noinvoice='".$param['noinvoice']."'";
	$query="delete from `".$dbname."`.`keu_tagihanht` where ".$where;
	try {
		$owlPDO->exec($query);
	} catch (PDOException $e) {
		print " Gagal, DB Error  3!: ".$e->getMessage()."<br/>";
		die();
	}
	break;
case 'updpo':
	$pokontrak=$_POST['pokontrak'];
	if ($pokontrak == 'po') {
		$resPO=makeOption($dbname, 'log_poht', 'nopo,nopo', "stat_release=1", '0', true);
	}
	if ($pokontrak == 'sj') {
		$resPO=makeOption($dbname, 'log_pengiriman_ht', 'nosj,nosj', '0', true);
	} else {
		$resPO=makeOption($dbname, 'log_spkht', 'notransaksi,notransaksi',
				"kodeorg='".$_SESSION['empl']['lokasitugas']."'", '0', true);
	}
	echo json_encode($resPO);
	break;
case 'updInvoice':
	 # Check existing PO
	$query=selectQuery($dbname, 'keu_tagihanht', 'nilaiinvoice', "nopo='".$_POST['nopo']."'");
	$res=fetchData($query);
	if (!empty($res)) {
		echo $res[0]['nilaiinvoice'];
	}
	break;
default:
	break;
}
function formHeader($mode, $data) {
	global $dbname;
	global $owlPDO;
	 # Default Value
	if (empty($data)) {
		$data['noinvoice']=date('Ymdhis');
		$data['noinvoicesupplier']='';
		$data['nilaiinvoice']='0';
		$data['noakun']='';
		$data['tanggal']='';
		$data['tipeinvoice']='po';
		$data['nopo']='';
		$data['jatuhtempo']='';
		$data['nofp']='';
		$data['keterangan']='';
		$data['uangmuka']='0';
		$data['nilaippn']='0';
		$data['kodeorg']='';
		$data['kurs']='1';
		$data['supplier']='';
		$data['matauang']='IDR';
	} else {
		$data['nilaiinvoice']=number_format($data['nilaiinvoice'], 0);
		$data['uangmuka']=number_format($data['uangmuka'], 0);
		$data['nilaippn']=number_format($data['nilaippn'], 0);
		$data['supplier']='';
		$data['matauang']='IDR';
		// Perbaiki Kurs Non IDR, jika kurs 1
		if ($data['matauang'] != 'IDR' and $data['kurs'] == 1) {
			// Get from Setup Mata Uang
			$qKurs=selectQuery($dbname, 'setup_matauangrate', '*',
					"daritanggal<='".tanggalsystem($data['tanggal'])."' and
					kode='".$data['matauang']."'", "daritanggal desc, jam desc", false, 1, 1);
			$resKurs=fetchData($qKurs);
			// Update hanya jika kurs ada
			if (!empty($resKurs)) {
				$dataUpd=array('kurs' => $resKurs[0]['kurs']);
				$qUpd=updateQuery($dbname, 'keu_tagihanht', $dataUpd,
						"noinvoice='".$data['noinvoice']."'");
				$test=false;
				try {
					$test=$owlPDO->exec($qUpd);
				} catch (PDOException $e) {
					print " Gagal  !: ".$e->getMessage()."<br/>";
					die();
				}
				if ($test) {
					$data['kurs']=$resKurs[0]['kurs'];
				}
			}
		}
	}
	 # Disabled Primary
	if ($mode == 'edit') {
		$disabled='disabled';
	} else {
		$disabled='';
	}
	 # Options
	// $str="select * from ".$dbname.".log_5klsupplier where tipe='RAMP'";
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $res->setFetchMode(PDO::FETCH_ASSOC);
	// while($bar=$res->fetch())
	// {
	//  $optNmsupp[$bar['kode']]=$bar['kelompok']." (".$bar['kode'].")";
	// }
	$str="select * from ".$dbname.".log_5supplier";
	$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar=$res->fetch()) {
		$optNmsupp[$bar['supplierid']]=$bar['namasupplier']." (".$bar['supplierid'].")";
	}
	$str="select * from ".$dbname.".keu_5keterangan";
	$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar=$res->fetch()) {
		$optket[$bar['id_ket']]=$bar['keterangan']."";
	}
	// $optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier',2,true);
	// $optOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
	$optOrg=getOrgDetail(3);
	if ($mode == 'edit') {
		$optUnit=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$data['unit']."'", '', true);
	} else {
		$optUnit=array("0" => $_SESSION['lang']['pilihdata']);
		$optUnit=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "induk='".$_SESSION['org']['kodeorganisasi']."' and char_length(kodeorganisasi)=4", '', true);
	}
	
	// echo"<pre>";
	// print_r($optUnit);
	// echo"</pre>";
	
	$strNpwp="select kodeorg,npwp from ".$dbname.".setup_org_npwp where kodeorg='".$_SESSION['org']['kodeorganisasi']."'";
	$resNpwp=fetchData($strNpwp);
	$optNpwp = array();
	$tempNpwp = array();
	foreach($resNpwp as $key=>$val)
	{
		$tempNpwp =  array($val['npwp']=>$val['npwp']);
		$optNpwp =  $optNpwp + $tempNpwp;
	}
	
	// echo"<pre>";
	// print_r($optUnit);
	// print_r($optNpwp);
	// print_r($strNpwp);
	// echo"</pre>";
	
	$str="select * from ".$dbname.".setup_matauang";
	$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$optKurs['IDR']="IDR";
	while ($bar=$res->fetch()) {
		if ($bar['kode'] != 'IDR') {
			 @ $optKurs[$bar['kode']].=$bar['kode'];
		}
	}
	$optAkun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "kasbank=1 and detail=1");
	$optJnsInv=makeOption($dbname, 'keu_5jenistagihan', 'kode,namajenis');
	if ($data['tipeinvoice'] == 'p') {
		$optPO=makeOption($dbname, 'log_poht', 'nopo,nopo', "stat_release=1", '0', true);
	}
	elseif($data['tipeinvoice'] == 's') {
		$optPO=makeOption($dbname, 'log_suratjalanht', 'nosj,nosj', null, '0', true);
	}
	elseif($data['tipeinvoice'] == 'ns') {
		$optPO=makeOption($dbname, 'log_konosemenht', 'nokonosemen,nokonosemen', null, '0', true);
	}
	else {
		$optPO=makeOption($dbname, 'log_spkht', 'notransaksi,notransaksi', null, '0', true);
	}
	$optCgt=getEnum($dbname, 'keu_kasbankht', 'cgttu');
	$optYn=array(0 => $_SESSION['lang']['belumposting'], 1 => $_SESSION['lang']['posting']);
	$els=array();
	$els[]=array(
			makeElement('noinvoice', 'label', $_SESSION['lang']['noinvoice']),
			makeElement('noinvoice', 'text', $data['noinvoice'],
				array('style' => 'width:150px', 'maxlength' => '20', 'disabled' => 'disabled')));
	$els[]=array(
			makeElement('noinvoicesupplier', 'label', $_SESSION['lang']['noinvoice']." Supplier"),
			makeElement('noinvoicesupplier', 'text', $data['noinvoicesupplier'],
				array('style' => 'width:150px', 'maxlength' => '50')));
	$els[]=array(
			makeElement('kodeorg', 'label', $_SESSION['lang']['pt']),
			makeElement('kodeorg', 'select', $data['kodeorg'],
				array('style' => 'width:155px', 'onchange' => 'getunit(this)', $disabled => $disabled), $optOrg));
	$els[]=array(
			makeElement('unit', 'label', $_SESSION['lang']['unit']),
			makeElement('unit', 'select',  @ $data['unit'],
				array('style' => 'width:155px', $disabled => $disabled), $optUnit));
	$els[]=array(
			makeElement('tanggal', 'label', $_SESSION['lang']['tanggalterima']),
			makeElement('tanggal', 'text', $data['tanggal'], array('style' => 'width:150px',
					'readonly' => 'readonly', 'onmousemove' => 'setCalendar(this.id)')));
	$els[]=array(
			makeElement('keterangan', 'label', $_SESSION['lang']['keterangan']),
			makeElement('keterangan', 'select', $data['keterangan'], array('style' => 'width:150px'), $optket));
	// $els[]=array(
	//     makeElement('matauang','label',$_SESSION['lang']['matauang']),
	//     makeElement('matauang','select',$data['matauang'],array('style'=>'width:150px'),$optKurs)
	// );
	$els[]=array(
			makeElement('upinvoice', 'label', 'Upload File External'),
			"<input type='file' name='upload' id='upload'>");
	$els[]=array(
			makeElement('tipeinvoice', 'label', $_SESSION['lang']['jenis']),
			makeElement('tipeinvoice', 'select', $data['tipeinvoice'],
				array('style' => 'width:155px', $disabled => $disabled, 'onchange' => 'updPO()'), $optJnsInv));
	// $els[]=array(
	// makeElement('tipeinvoice','label',$_SESSION['lang']['jenis']),
	// makeElement('tipeinvoice','select',$data['tipeinvoice'],
	// array('style'=>'width:155px',$disabled=>$disabled,'onchange'=>'updPO()'),
	// array('po'=>'PO',
	// 'kontrak'=>$_SESSION['lang']['kontrak'],
	// 'sj'=>$_SESSION['lang']['suratjalan'],
	// 'bykrm'=>'Biaya Kirim',
	// // 'ns'=>$_SESSION['lang']['konosemen']
	// ))
	// );
	$els[]=array(
			makeElement('nopo', 'label', $_SESSION['lang']['nopo']),
			makeElement('nopo', 'text', $data['nopo'], array('style' => 'width:150px;cursor:pointer',
					'readonly' => 'readonly',
					$disabled => $disabled,
					'placeholder' => 'Click to choose',
					'onclick' => "searchNopo('".$_SESSION['lang']['find']." ',event,'".$_SESSION['lang']['find']."')")));
	/** [START] Data dari PO */
	//,'onchange'=>'getnpwp(this)'
	$els[]=array(
			makeElement('supplier', 'label', $_SESSION['lang']['supplier']),
			makeElement('supplier', 'selectsearch',  @ $data['kodesupplier'], array('style' => 'width:150px', $disabled => $disabled), $optNmsupp));
	$els[]=array(
			makeElement('noakunh', 'label', $_SESSION['lang']['noakun']),
			makeElement('noakunh', 'text', $data['noakun'],
				array('style' => 'width:150px', 'disabled' => 'disabled')));
	$els[]=array(
			makeElement('matauang', 'label', $_SESSION['lang']['matauang']),
			makeElement('matauang', 'select', $data['matauang'], array('style' => 'width:150px'), $optKurs));
	$els[]=array(
			makeElement('kurs', 'label', $_SESSION['lang']['kurs']),
			makeElement('kurs', 'text', $data['kurs'], array('style' => 'width:150px')));
	if ( @ $data['uploadinvoice'] != '') {
		$els[]=array(
				"<div id=divFile1>File</div>",
				"<div id=divFile2>".$data['uploadinvoice']."&nbsp;
				<img src='images/skyblue/pdf.jpg' class='zImgBtn' title='Priview File' onclick='detailFile(".$data['noinvoice'].",event);'>
				<img src='images/skyblue/delete.png' class='zImgBtn' title='Hapus File' onclick='deleteFile(".$data['noinvoice'].");'></div>", );
	} else {
		$els[]=array();
	}
	/** [END] Data dari PO */
	$els[]=array(
			makeElement('jatuhtempo', 'label', $_SESSION['lang']['jatuhtempo']),
			makeElement('jatuhtempo', 'text', $data['jatuhtempo'],
				array('style' => 'width:150px', 'readonly' => 'readonly', 'onmousemove' => 'setCalendar(this.id)')));
	$els[]=array(
			makeElement('nofp', 'label', $_SESSION['lang']['nofp']),
			makeElement('nofp', 'text', $data['nofp'],
				array('style' => 'width:150px', 'maxlength' => '20')));
	$els[]=array(
			makeElement('tanggalnofp', 'label', $_SESSION['lang']['tanggalnofp']),
			makeElement('tanggalnofp', 'text', $data['tanggalnofp'], array('style' => 'width:150px',
					'readonly' => 'readonly', 'onmousemove' => 'setCalendar(this.id)')));
	$els[]=array(
			makeElement('nilaiinvoice', 'label', $_SESSION['lang']['nilaiinvoice']),
			makeElement('nilaiinvoice', 'textnum', $data['nilaiinvoice'],
				array('style' => 'width:150px', 'onchange' => 'this.value=remove_comma(this);this.value=_formatted(this)')));
	$els[]=array(
			makeElement('tanggalinvoice', 'label', $_SESSION['lang']['tanggalinvoice']),
			makeElement('tanggalinvoice', 'text', $data['tanggalinvoice'], array('style' => 'width:150px',
					'readonly' => 'readonly', 'onmousemove' => 'setCalendar(this.id)')));
	$els[]=array(
			makeElement('keterangan2', 'label', $_SESSION['lang']['deskripsipembelian']),
			makeElement('keterangan2', 'text', $data['keterangan2'],
				array('style' => 'width:150px', 'maxlength' => '255')));
	 # uang muka
	$els[]=array(
			makeElement('uangmuka', 'label', ''),
			makeElement('uangmuka', 'textnum', $data['uangmuka'],
				array('style' => 'display:none', 'onchange' => 'this.value=remove_comma(this);this.value=_formatted(this)')));
	/*
	$els[]=array(
	makeElement('uangmuka','label',$_SESSION['lang']['uangmuka']),
	makeElement('uangmuka','textnum',$data['uangmuka'],
	array('style'=>'width:150px','onchange'=>'this.value=remove_comma(this);this.value=_formatted(this)'))
	);
	 */
	$els[]=array(
			makeElement('status_bayar', 'label', $_SESSION['lang']['pembayaran']),
			makeElement('status_bayar', 'select',  @ $data['status_bayar'],
				array('style' => 'width:155px'),
				array('0' => 'Kas Bank')));
	$els[]=array(
			makeElement('npwp', 'label', 'NPWP'),
			makeElement('npwp', 'select', $data['npwp'], array('style' => 'width:155px', $disabled => $disabled),$optNpwp));
	$checkbox=makeElement('statusdoc', 'checkbox',  @ $data['statusdoc'])." ".makeElement('statusdoc', 'label', $_SESSION['lang']['lengkap']." / ".$_SESSION['lang']['belumlengkap']);
	$els[]=array(
			makeElement('statusdoc', 'label', $_SESSION['lang']['status']." ".$_SESSION['lang']['dokumen']),
			$checkbox);
	if ($mode == 'add') {
		$els['btn']=array(
				makeElement('addHead', 'btn', $_SESSION['lang']['save'],
					array('onclick' => "addDataTable()")));
	}
	elseif($mode == 'edit') {
		$els['btn']=array(
				makeElement('editHead', 'btn', $_SESSION['lang']['save'],
					array('onclick' => "editDataTable()")));
	}
	if ($mode == 'add') {
		return genElementMultiDim($_SESSION['lang']['addheader'], $els, 3);
	}
	elseif($mode == 'edit') {
		return genElementMultiDim($_SESSION['lang']['editheader'], $els, 3);
	}
}
?>