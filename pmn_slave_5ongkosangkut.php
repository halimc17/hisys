<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method         = checkPostGet('method', '');

$kodeunit       = checkPostGet('kodeunit', '');
$lokasi       = checkPostGet('lokasi', '');
$lokasi2       = checkPostGet('lokasi2', '');
$trpcode       = checkPostGet('trpcode', '');
$komoditi       = checkPostGet('komoditi', '');
$iddet       = checkPostGet('iddet', '');
$nodetail       = checkPostGet('nodetail', '');
$tgl1       = checkPostGet('tgl1', '');
$tgl2       = checkPostGet('tgl2', '');
$harga       = checkPostGet('harga', '');
$hargapotongan       = checkPostGet('hargapotongan', '');
$idsch       = checkPostGet('idsch', '');
$idht       = checkPostGet('idht', '');
$idks       = checkPostGet('idks', '');
$posting       = checkPostGet('posting', '');
$nourutid       = checkPostGet('nourutid', '');
$kdtrans       = checkPostGet('kdtrans', '');

$kodeunitcari   = checkPostGet('kodeunitcari', '');
$komoditicari   = checkPostGet('komoditicari', '');
$notransaksicari   = checkPostGet('notransaksicari', '');
$kodeunitdetailcari   = checkPostGet('kodeunitdetailcari', '');


// $supplier       = checkPostGet('supplier','');
// $aktif          = checkPostGet('aktif','');
// $tahuntanam     = checkPostGet('tahuntanam','');
// $harga          = checkPostGet('harga','');
// $budgetharga    = checkPostGet('budgetharga','');
// $disbunharga    = checkPostGet('disbunharga','');
// $awalrealisasi  = checkPostGet('awalrealisasi','');
// $awaldisbun     = checkPostGet('awaldisbun','');
// // $suppliercari= checkPostGet('suppliercari','');
// $notransaksi    = checkPostGet('notransaksi','');

// $tanggal        =tanggalsystemn(checkPostGet('tanggal',''));
// $tanggal2       =tanggalsystemn(checkPostGet('tanggal2',''));
// $jam        =checkPostGet('jam','');
// $jam2       =checkPostGet('jam2','');
// $menit        =checkPostGet('menit','');
// $menit2       =checkPostGet('menit2','');

// $tanggaljam       =checkPostGet('tanggaljam','');
// $tanggaljam2       =checkPostGet('tanggaljam2','');


// $tanggalcopy    =tanggalsystemn(checkPostGet('tanggalcopy',''));
// $tanggal2copy   =tanggalsystemn(checkPostGet('tanggal2copy',''));

// $jamcopy        =checkPostGet('jamcopy','');
// $jam2copy       =checkPostGet('jam2copy','');
// $menitcopy        =checkPostGet('menitcopy','');
// $menit2copy       =checkPostGet('menit2copy','');
// $tipe    = checkPostGet('tipe','');


// $kode           = checkPostGet('kode','');
// $batasbawah     = checkPostGet('batasbawah','');
// $batasatas      = checkPostGet('batasatas','');

// $tahuntanamcari = checkPostGet('tahuntanamcari','');
// $tanggalcari    =tanggalsystemn(checkPostGet('tanggalcari',''));


$optposting = array('' => $_SESSION['lang']['pilihdata'], '0' => 'Belum Disetujui', '1' => 'Disetujui', '3' => 'Ditolak', '9' => 'Proses Persetujuan');
$maxaproval = checkPostGet('maxaproval', '');

$kodept = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmsupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$nmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$nmbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

$arraktif = array("0" => "Tidak", "1" => "Ya");



switch ($method) {

	case 'insertmaster':

		# Validasi
		if ($lokasi == "" && $lokasi2 == "") {
			exit("<label hidden>Warning</label> Lokasi Asal atau Tujuan Wajib diisi salah satu!");
		}
		if ($trpcode == "") {
			exit("<label hidden>Warning</label> Transportir Wajib diisi!");
		}

		if ($lokasi != "" && $lokasi2 != "") {
			exit("<label hidden>Warning</label> Lokasi Asal atau Tujuan hanya bisa diisi salah satu!");
		}

		if ($lokasi != '') {
			$textn = ' dan Supplier ' . $nmsupp[$lokasi] . ' dengan komoditi ' . $nmbarang[$komoditi];
			$whr = "and lokasi='" . $lokasi . "'";
		}

		if ($lokasi2 != '') {
			$textn = ' dan Customer ' . $nmcust[$lokasi2] . ' dengan komoditi ' . $nmbarang[$komoditi];
			$whr = "and tujuan='" . $lokasi2 . "'";
		}


		$cek = "SELECT * from " . $dbname . ".pmn_5ongkosangkutht where komoditi='" . $komoditi . "' and kodeunit='" . $kodeunit . "' and trpcode ='" . $trpcode . "' " . $whr . "";
		$res = fetchData($cek);
		$htgc = count($res);

		if ($kdtrans == '') {
			exit("Warning : Notransaksi tidak boleh kosong");
		}

		if ($res[0]['notransaksi'] == $kdtrans) {
			exit('Warning!! Notransaksi sudah ada !!');
		}

		if ($htgc > 0) {
			exit("Warning!! Sudah ada pada notransaksi {$res[0]['notransaksi']} data untuk unit " . $nmorg[$kodeunit] . $textn);
		}

		$str = "insert into  " . $dbname . ".pmn_5ongkosangkutht (`kodeunit`,`notransaksi`,`lokasi`,`trpcode`, `tujuan`, `komoditi`, `createdby`, `createdtime`, `updateby`) 
		values ('" . $kodeunit . "','" . $kdtrans . "','" . $lokasi . "','" . $trpcode . "','" . $lokasi2 . "','" . $komoditi . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'getnotrans':

		$varcode = "OAP";
		$unit = $kodeunit;

		@$tgl = date("Y-m-d");
		$bln = substr($tgl, 5, 2);
		$notrans = $unit . "/" . $varcode . "/" . $bln . "/" . date("Y");
		echo $notrans;

		break;

	case 'insertdetail':
		$arrpstg = makeOption($dbname, 'pmn_5ongkosangkutht', 'id,notransaksi');
		$notransaksi = $arrpstg[$iddet];
		$str = "insert into  " . $dbname . ".pmn_5ongkosangkutdt (`id`, `notransaksi`, `tanggalawal`, `tanggalsampai`,`harga`, `hargapotongan`, `createdby`, `createdtime`) 
		values ('" . $iddet . "', '" . $notransaksi . "','" . tanggalsystemn($tgl1) . "','" . tanggalsystemn($tgl2) . "','" . $harga . "','" . $hargapotongan . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'updatemaster':

		if ($lokasi != '') {
			$textn = ' dan Supplier ' . $nmsupp[$lokasi] . ' dengan komoditi ' . $nmbarang[$komoditi];
			$whr = "and lokasi='" . $lokasi . "'";
		}

		if ($lokasi2 != '') {
			$textn = ' dan Customer ' . $nmcust[$lokasi2] . ' dengan komoditi ' . $nmbarang[$komoditi];
			$whr = "and tujuan='" . $lokasi2 . "'";
		}


		$cek = "SELECT * from " . $dbname . ".pmn_5ongkosangkutht where komoditi='" . $komoditi . "' and kodeunit='" . $kodeunit . "' " . $whr . "";
		$res = fetchData($cek);
		$htgc = count($res);


		if ($htgc > 0) {
			exit('Warning!! Sudah ada data untuk unit ' . $nmorg[$kodeunit] . $textn);
		}


		$str = "update " . $dbname . ".pmn_5ongkosangkutht set kodeunit='" . $kodeunit . "',
		lokasi='" . $lokasi . "',
		tujuan='" . $lokasi2 . "',
		trpcode='" . $trpcode . "',
		komoditi='" . $komoditi . "',
		updateby='" . $_SESSION['standard']['username'] . "',
		updatetime='" . date('Y-m-d H:i') . "' 
		where id = '" . $iddet . "' ";
		// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'updatedetail':
		$str = "update " . $dbname . ".pmn_5ongkosangkutdt set tanggalawal='" . tanggalsystemn($tgl1) . "',tanggalsampai='" . tanggalsystemn($tgl2) . "',harga='" . $harga . "',hargapotongan='" . $hargapotongan . "',updateby='" . $_SESSION['standard']['username'] . "',updatetime='" . date('Y-m-d H:i') . "' where nourut = '" . $nodetail . "' ";
		//  exit('error '.$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;




	case 'loaddatamaster':

		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$where = "";
		if ($kodeunitcari != '') {
			$where .= " and kodeunit   ='" . $kodeunitcari . "'";
		}
		if ($notransaksicari != '') {
			$where .= " and notransaksi like '%" . $notransaksicari . "%'";
		}
		if ($komoditicari != '') {
			$where .= " and komoditi ='" . $komoditicari . "'";
		}


		$listorg = orgDetailuser($_SESSION['standard']['username'], '2');

		// $where .= " and kodeunit IN ({$listorg})";

		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_5ongkosangkutht
				where 0=0 " . $where;

		$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}


		$tab = "<br><table style='width:100%;' class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
				<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
				<th align=center>" . $_SESSION['lang']['unit'] . "</th>
				<th align=center> Transportir</th> 
				<th align=center> Tujuan</th>   
				<th align=center>" . $_SESSION['lang']['komoditi'] . "</th> 
				<th align=center>" . $_SESSION['lang']['updateby'] . "</th> 
				<th align=center colspan=3>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			</thead>
			<tbody>";

		$no = 0;
		$no = $maxdisplay;
		$colspan = 7;
		$str = "select * from " . $dbname . ".pmn_5ongkosangkutht where 0=0 " . $where . " LIMIT " . $offset . "," . $limit . "";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no++;
			$tab .= "<tr class=rowcontent id=tr_$no>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . $bar['notransaksi'] . "</td>";
			$tab .= "<td>" . $nmorg[$bar['kodeunit']] . "</td>";
			$tab .= "<td>" . @$nmsupp[$bar['trpcode']] . "</td>";
			$tab .= "<td>" . @$nmcust[$bar['tujuan']] . "</td>";
			$tab .= "<td>" . $nmbarang[$bar['komoditi']] . "</td>";
			$tab .= "<td>" . getNamaKaryawan($bar['updateby']) . "</td>";

			if ($bar['posting'] == 0 || $bar['posting'] == 3) {
				$tab .= "<td align=center style='width:25px;'><img src=images/skyblue/zoom.png class=resicon  title='Input Detail Transaksi' onclick=\"formdetail('" . $bar['id'] . "','" . $bar['lokasi'] . "','" . $bar['posting'] . "');\"></td>";
				$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  caption='Edit' 
				onclick=\"editmaster('" . $bar['id'] . "','" . $bar['kodeunit'] . "','" . $bar['lokasi'] . "','" . $bar['tujuan'] . "','" . $bar['trpcode'] . "','" . $bar['komoditi'] . "','updatemaster');\"></td>";
				$tab .= "<td align=center style=\"width:15px; height:15px; \"> 
					<img  src=images/icons/04/16/01.png class='zImgBtn' title='Ajukan' onclick='posting(`" . $bar['id'] . "`)'>
				</td>";
				// $tab.="<td align=center style=\"width:15px; height:15px; \">
				// 	<img style='display:none' src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('" . $bar['nokontrak'] . "','" . $no . "');\" >
				// 	<img src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick='form_ajukan(`".$bar['nokontrak']."`)'>
				// </td>";							
			} else if ($bar['posting'] == 9) {
				$tab .= "<td colspan=2 style=\"width:15px; height:15px; \"></td>";
				$tab .= "<td align=center style=\"width:15px; height:15px; \">
					<img src='images/icons/04/16/04.png' class='zImgBtn' height='30' title='On Progress Approval'>
				</td>";
			} else if ($bar['posting'] == 2) {
				$tab .= "<td colspan=2 style=\"width:15px; height:15px; \"></td>";
				$tab .= "<td align=center style=\"width:15px; height:15px; \">
					<img src='images/icons/04/16/01.png' class='zImgBtn' height='30' title='Approval Rejected'>
				</td>";
			} else {
				$tab .= "<td colspan=1 style=\"width:15px; height:15px; \"></td>";
				$tab .= "<td align=center style='width:25px;'><img src=images/skyblue/zoom.png class=resicon  title='Input Detail Transaksi' onclick=\"formdetail('" . $bar['id'] . "','" . $bar['lokasi'] . "','" . $bar['posting'] . "');\"></td>";
				## aktifkan jika diperlukan unposting
				// $tab.="<td align=center style=\"width:15px; height:15px; \">
				// 	<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Unposting' onclick=\"unposting('".@$bar['id']."','" . $no . "');\" >
				// </td>";
				$tab .= "<td align=center style=\"width:15px; height:15px; \">
					<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Unposting' >
				</td>";
			}

			// $tab.="<td align=center style='width:25px;'><img src=images/skyblue/zoom.png class=resicon  title='Input Detail Transaksi' onclick=\"formdetail('".$bar['id']."','".$bar['lokasi']."');\"></td>";
			// $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  caption='Edit' 
			// onclick=\"editmaster('".$bar['id']."','".$bar['kodeunit']."','".$bar['lokasi']."','".$bar['tujuan']."','".$bar['komoditi']."','updatemaster');\"></td>";



			$tab .= "</tr>";
		}
		// $totrows=ceil($jlhbrs/$limit);
		// if($totrows==0)
		// {
		// $totrows=1;
		// }
		// $isiRow='';
		// for($er=1;$er<=$totrows;$er++)
		// {
		// $sel = ($page==$er-1)? 'selected': '';
		// $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		// }

		// $tab.="<tr><td colspan=7 align=center>";
		// $tab.="<button class=mybutton ".($page == '0' ? 'disabled=true' : '')." onclick=loaddatamaster(".($page-1).");>Prev</button>";
		// $tab.="<select id=\"pagesmaster\" name=\"pagesmaster\" onchange=\"getPagemaster(this.value)\">".$isiRow."</select>";
		// $tab.="<button class=mybutton ".(($page + 1) == $totrows ? 'disabled=true' : '')." onclick=loaddatamaster(".($page+1).");>Next</button>";
		// $tab.="</td></tr>";

		$tab .= createpaging($jlhbrs, $limit, $page, $colspan, 'loaddatamaster', 'getPagemaster');

		echo $tab;
		break;

	case 'posting':
		$str = "update " . $dbname . ".pmn_5ongkosangkutht set posting='1', postingby='" . $_SESSION['standard']['userid'] . "', postingtime='" . date('Y-m-d H:i:s') . "' where id='" . $idks . "'";

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		break;

	case 'unposting':
		$str = "update " . $dbname . ".pmn_5ongkosangkutht set posting='0', postingby='" . $_SESSION['standard']['userid'] . "', postingtime='" . date('Y-m-d H:i:s') . "' where id='" . $idks . "'";

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		break;

	case 'deldetail':
		$str = "delete from " . $dbname . ".pmn_5ongkosangkutdt where nourut='" . $nourutid . "'";
		// exit('error '.$str);

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		break;

	case 'postingdt':
		$str = "update " . $dbname . ".pmn_5ongkosangkutdt set posting='1', postingby='" . $_SESSION['standard']['userid'] . "', postingtime='" . date('Y-m-d H:i:s') . "' where nourut='" . $nourutid . "'";
		// exit('error '.$str);

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		break;

	case 'loaddatadetail':

		$limit = 20;
		$page = 0;
		if (isset($_POST['pagedt'])) {
			$page = $_POST['pagedt'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$where = "";
		$where .= " and id='" . $_POST['iddet'] . "'";
		// if($kodeunitcari!=''){ 
		// 	$where.=" and kodeunit   ='".$kodeunitcari."'";
		// }


		$arrpstg = makeOption($dbname, 'pmn_5ongkosangkutht', 'id,posting');
		$checkposting = $arrpstg[$iddet];

		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_5ongkosangkutdt
				where 0=0 " . $where;
		$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}


		$tab = "<br><table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center>" . $_SESSION['lang']['nourut'] . "</th> 
				<th align=center>" . $_SESSION['lang']['tanggal'] . " Awal</th> 
				<th align=center>" . $_SESSION['lang']['tanggal'] . " Sampai</th> 
				<th align=center>" . $_SESSION['lang']['harga'] . "</th>
				<th align=center>" . $_SESSION['lang']['persen'] . " Potongan</th>
				";
		# code...
		$tab .= "<th align=center>" . $_SESSION['lang']['createby'] . "</th>";
		$tab .= "<th align=center>" . $_SESSION['lang']['dipostingoleh'] . "</th>";
		// if ($checkposting!='1' || $checkposting!=1) { 
		$tab .= "<th align=center colspan=3>" . $_SESSION['lang']['action'] . "</th>";
		// }
		$tab .= "</tr>
			</thead>
			<tbody>";
		$nmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');



		$no = 0;
		$no = $maxdisplay;
		$str = "select * from " . $dbname . ".pmn_5ongkosangkutdt where id='" . $iddet . "' " . $where . " LIMIT " . $offset . "," . $limit . "";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no++;
			$tab .= "<tr class=rowcontent id=tr_$no>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . tanggalnormal($bar['tanggalawal']) . "</td>";
			$tab .= "<td>" . tanggalnormal($bar['tanggalsampai']) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($bar['harga'], 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($bar['hargapotongan'], 2) . "</td>";
			$tab .= "<td>" . getNamaKaryawan($bar['createdby']) . "</td>";
			$tab .= "<td>" . getNamaKaryawan($bar['postingby']) . "</td>";
			// if ($checkposting!='1' || $checkposting!=1) { 
			if ($bar['posting'] == '0') {
				$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"editdetail('" . $bar['nourut'] . "','" . tanggalnormal($bar['tanggalawal']) . "','" . tanggalnormal($bar['tanggalsampai']) . "','" . $bar['harga'] . "','" . $bar['hargapotongan'] . "','updatedetail');\"></td>";
				$tab .= "<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  caption='Edit' onclick=\"deletedetail('" . $bar['nourut'] . "','" . $iddet . "');\"></td>";
				$tab .= "<td align=center width=25px>
					<img  src=images/icons/04/16/01.png class='zImgBtn' title='Ajukan' onclick=\"postingdt('" . $bar['nourut'] . "','" . $iddet . "')\">
				</td>";
			} else {
				$tab .= "<td></td>";
				$tab .= "<td></td>";
				$tab .= "<td align=center width=25px>
					<img  src=images/icons/04/16/02.png class='zImgBtn' title='Ajukan' onclick=\"postingdt('" . $bar['nourut'] . "','" . $iddet . "')\">
				</td>";
			}
			// } else{

			// }
			// $tab.="<td align=center><input  id=methodmaster value='".$bar['nourut']."'></td>"; 




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

		$tab .= "<tr><td colspan=7 align=center>";
		if ($page < '0') {
			$tab .= "<button class=mybutton onclick=loaddatadetail(" . ($page - 1) . ");>Prev</button>";
		}
		$tab .= "<select id=\"pagedetail\" name=\"pagedetail\" onchange=\"getpagedetail(this.value)\">" . $isiRow . "</select>";
		if (@($page + 1) == $totrows) {
			$tab .= "";
		} else {
			$tab .= "<button class=mybutton onclick=loaddatadetail(" . ($page + 1) . ");>Next</button>";
		}

		$tab .= "</td></tr>";

		echo $tab;
		break;



	case 'formdetail':
		## GET MILL
		$optmill = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where tipe='PABRIK' order by kodeorganisasi asc";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optmill .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
		}

		$strk = "select * from " . $dbname . ".pmn_bast where notransaksi='" . $param['notransaksi'] . "' ";
		$resk = fetchdata($strk);

		$nmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
		$checkposting = $posting;
		// $arrTipePerbaikan=array("prev"=>"Preventive Maintenance","kalibrasi"=>"Kalibrasi","project"=>"Project",
		// 	"pabrikasi"=>"Pabrikasi","corrective"=>"Corrective Maintenance","service"=>"Service");
		// if ($checkposting=='1' || $checkposting==1) {
		if (false) {
			$displayx = "style=display:none";
		}
		$stream = "<fieldset " . $displayx . ">
        <legend>" . $_SESSION['lang']['form'] . "</legend>";
		$stream .= "<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>" . $_SESSION['lang']['tanggal'] . "</td> 
			<td>:</td>
			<td> 
                <input type=text class=myinputtext id=tgl1  onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"  style='width:190px;'  size=10 maxlength=10 readonly/> 
			</td>
			<td> 
                s/d    <input type=text class=myinputtext id=tgl2  onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"  style='width:190px;'  size=10 maxlength=10 readonly/> 

			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['harga'] . "</td> 
			<td>:</td> 
            <td><input type=text class=myinputtextnumber id=harga name=harga onkeypress=\"return angka_doang(event);\" style=width:150px; maxlength=45 /></td> 
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['persen'] . " Potongan</td> 
			<td>:</td> 
            <td><input type=text class=myinputtextnumber id=hargapotongan name=hargapotongan onkeypress=\"return angka_doang(event);\" style=width:150px; maxlength=45 /></td> 
		</tr>
 
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=simpandetail()>" . $_SESSION['lang']['save'] . "</button>
				<button class=mybutton onclick=bataldetail()>" . $_SESSION['lang']['cancel'] . "</button>
				<input hidden id=methoddetail value='insertdetail'>
				<input hidden id=iddet value='" . $iddet . "'>
				<input hidden id=nodetail value=''>
			</td>
		</tr>
	</table>
    </fieldset>
    ";

		$stream .= "<fieldset>
        <legend>" . $_SESSION['lang']['list'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>  
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=containerdetail> 
            <script>loaddatadetail(0)</script>
        </div>
    </fieldset>";

		echo $stream;
		break;


	default:
}
