<? //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/zFunction.php');


$proses = checkPostGet('proses', '');
// $proses = $_GET['proses'];
$param = $_POST;


$app = 'pabrik';
$postJabatan = getPostingJabatan($app);

switch ($proses) {

	case 'html':
		$theme = $_SESSION['theme'];
		if ($theme == 'skyblue' || $theme == '') {
			$men = 'menu.css';
			$gen = 'generic.css';
		} else if ($theme == 'red') {
			$men = 'menuRed.css';
			$gen = 'genericRed.css';
		} else {
			$men = 'menuGray.css';
			$gen = 'genericGray.css';
		}

		$str = "select * from " . $dbname . ".pabrik_pengolahan where nopengolahan='" . $param['nopengolahan'] . "' ";/*   <td align=center>Loses</td> <td align=center>Loses</td>*/
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		$tab = "<link rel=stylesheet type=text/css href=style/" . $gen . ">";
		//$tab.="<fieldset><legend>Preview</legend>";
		$tab .= "<table cellpadding=5 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<tr><td>" . $_SESSION['lang']['tanggal'] . "</td><td> :</td><td> " . tanggalnormal($bar['tanggal']) . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['shift'] . "</td><td> :</td><td> " . $bar['shift'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['jammulai'] . "</td><td> :</td><td> " . $bar['jammulai'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['jamselesai'] . "</td><td> :</td><td> " . $bar['jamselesai'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['jamdinasbruto'] . "</td><td> :</td><td> " . $bar['jamdinasbruto'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['jamstagnasi'] . "</td><td> :</td><td> " . $bar['jamstagnasi'] . "</td></tr>";
		$tab .= "<tr><td>Jumlah Set Olah</td><td> :</td><td> " . $bar['jumlahlori'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['jamshift'] . "</td><td> :</td><td> " . $bar['jamshift'] . "</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['restan'] . " Ramp</td><td> :</td><td> " . $bar['lorirestan'] . "</td></tr>";
		// $tab.="<tr><td>".$_SESSION['lang']['restan']." Sebelum</td><td> :</td><td> ".$bar['restansebelum']."</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['restan'] . " didalam</td><td> :</td><td> " . $bar['restandidalam'] . "</td></tr>";
		// $tab.="<tr><td>".$_SESSION['lang']['restan']." sesudah</td><td> :</td><td> ".$bar['restansesudah']."</td></tr>";
		$tab .= "<tr><td>" . $_SESSION['lang']['keterangan'] . "</td><td> :</td><td> " . $bar['keterangan'] . "</td></tr>";
		$tab .= "</table></fieldset><br>";

		if (in_array($_SESSION['empl']['kodejabatan'], $postJabatan) and $bar['posting'] == 1) {
			$tab .= "<center><button id=edit class=mybutton onclick=unposting('" . $param['nopengolahan'] . "','" . $bar['tanggal'] . "') >Unposting Transaksi</button></center>";
		}
		echo $tab;
		break;
	case 'unposting':
		$str = "select * from " . $dbname . ".pabrik_pengolahan where nopengolahan='" . $param['nopengolahan'] . "' ";/*   <td align=center>Loses</td> <td align=center>Loses</td>*/
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$tgl = $bar['tanggal'];
		$unit = $bar['kodeorg'];

		#cek apakah ada produksi diatas tanggal yang di unposting
		$str = "delete from " . $dbname . ".pabrik_produksi where tanggal >= '" . $tgl . "' and kodeorg='" . $unit . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}

		#unpost nopengolahan 
		$str = "update  " . $dbname . ".pabrik_pengolahan set posting=0 where nopengolahan='" . $param['nopengolahan'] . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	#posting
	case 'posting':
		$data = $_POST;
		$where = "nopengolahan='" . $data['nopengolahan'] . "'";
		$query = updateQuery($dbname, 'pabrik_pengolahan', array('posting' => '1'), $where);
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	# Daftar Header
	case 'showHeadList':
		$where = "kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' ";
		if (isset($param['where'])) {
			$tmpW = str_replace('\\', '', $param['where']);
			$arrWhere = json_decode($tmpW, true);
			if (!empty($arrWhere)) {
				foreach ($arrWhere as $key => $r1) {
					if ($key == 0) {
						//$where .= $r1[0]." like '%".$r1[1]."%'";
						//} else {
						$where .= " and " . $r1[0] . " like '%" . tanggalsystemn($r1[1]) . "%'";
					}
				}
			} else {
				$where .= null;
			}
		} else {
			$where .= null;
		}
		$where .= "  order by tanggal desc, shift desc";


		# Header
		$header = array(
			$_SESSION['lang']['nopengolahan'],
			$_SESSION['lang']['pabrik'],
			$_SESSION['lang']['tanggal'],
			$_SESSION['lang']['shift'],
			$_SESSION['lang']['status']
		);

		# Content
		$cols = "nopengolahan,kodeorg,tanggal,shift,posting";
		$query = selectQuery($dbname, 'pabrik_pengolahan', $cols, $where, "", false, $param['shows'], $param['page']);
		$data = fetchData($query);
		$totalRow = getTotalRow($dbname, 'pabrik_pengolahan', $where);
		foreach ($data as $key => $row) {
			$data[$key]['kodeorg'] = getNamaOrg($row['kodeorg']);
			$data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
			$data[$key]['status'] = $row['posting'] == 1 ? "POSTED" : "NOT POSTED";
			if ($row['posting'] == 1) {
				$data[$key]['switched'] = true;
				// $hilangkangambar = 
				// $tHeader->addAction('showEdit', 'Edit', '');
				// $tHeader->addAction('deleteData', 'Delete', '');
			// }else{
			// 	$tHeader->addAction('showEdit', 'Edit', 'images/' . $_SESSION['theme'] . "/edit.png");
			// 	$tHeader->addAction('deleteData', 'Delete', 'images/' . $_SESSION['theme'] . "/delete.png");
			}
			unset($data[$key]['posting']);
		}
		// echo "<pre>";
		// print_r($data);
		// exit("Warning");

		# Make Table
		$tHeader = new rTable('headTable', 'headTableBody', $header, $data);
		$tHeader->addAction('showEdit', 'Edit', 'images/' . $_SESSION['theme'] . "/edit.png");
		$tHeader->addAction('deleteData', 'Delete', 'images/' . $_SESSION['theme'] . "/delete.png");

		$tHeader->addAction('postingData', 'Posting', 'images/' . $_SESSION['theme'] . "/posting.png");
		$tHeader->_actions[2]->setAltImg('images/' . $_SESSION['theme'] . "/posted.png");

		// if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
		// $tHeader->_actions[2]->_name='';
		// }

		// $tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
		// $tHeader->_actions[3]->addAttr('event');
		// $tHeader->_switchException = array('detailPDF');

		$tHeader->addAction('tampilDetail', 'Print Data Detail', 'images/' . $_SESSION['theme'] . "/zoom.png");
		$tHeader->_actions[3]->addAttr('event');
		$tHeader->_switchException = array('tampilDetail');

		$tHeader->pageSetting($param['page'], $totalRow, $param['shows']);
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
		$query = selectQuery($dbname, 'pabrik_pengolahan', "*", "nopengolahan='" . $param['nopengolahan'] . "'");
		$tmpData = fetchData($query);
		$data = $tmpData[0];
		$data['tanggal'] = tanggalnormal($data['tanggal']);
		echo formHeader('edit', $data);
		echo "<div id='detailField' style='clear:both'></div>";
		break;
	# Proses Add Header
	case 'add':
		$data = $_POST;

		$jammulai = substr($data['jammulai'], 0, 2);
		$jammulai =  floatval($jammulai);

		$menitmulai = substr($data['jammulai'], 3, 2);
		$menitmulai =  floatval($menitmulai);

		$jamselesai = substr($data['jamselesai'], 0, 2);
		$jamselesai =  floatval($jamselesai);

		$menitselesai = substr($data['jamselesai'], 3, 2);
		$menitselesai =  floatval($menitselesai);


		if ($jammulai > $jamselesai) {
			if ($menitmulai > $menitselesai) {
				$jamisi = ($jamselesai + 23) - $jammulai;
				$menitisi =  number_format((($menitselesai + 60) - $menitmulai) / 60, 2);
			} else {
				$jamisi = ($jamselesai + 24) - $jammulai;
				$menitisi =  number_format(($menitselesai - $menitmulai) / 60, 2);
			}
		} else {
			if ($menitmulai > $menitselesai) {
				$jamisi = $jamselesai - $jammulai - 1;
				$menitisi =  number_format((($menitselesai + 60) - $menitmulai) / 60, 2);
			} else {
				$jamisi = $jamselesai - $jammulai;
				$menitisi =  number_format(($menitselesai - $menitmulai) / 60, 2);
			}
		}

		$selisih = $jamisi + $menitisi;
		if ((number_format($data['jamdinasbruto'], 2) + number_format($data['jamstagnasi'], 2)) != number_format($selisih, 2)) {
			// exit("Warning: Jam proses masih salah,  masih terdapat selisih");
		}

		// Error Trap
		$warning = "";
		#if($data['nopengolahan']=='') {$warning .= "No Pengolahan harus diisi\n";}
		if ($data['tanggal'] == '') {
			$warning .= "Tanggal harus diisi\n";
		}
		if ($warning != '') {
			echo "Warning :\n" . $warning;
			exit;
		}
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		$data['createby'] = $_SESSION['standard']['userid'];
		$data['createtime'] = date('Y-m-d H:i:s');

		$cols = array(
			'kodeorg',
			'nopengolahan',
			'tanggal',
			'shift',
			'jamshift',
			'jammulai',
			'jamselesai',
			'asisten',
			'mandor',
			'jamdinasbruto',
			'jamstagnasi',
			'jumlahlori',
			'tbsdiolah',
			'lorirestan',
			'restansebelum',
			'restandidalam',
			'restansesudah',
			'keterangan',
			'createby',
			'createtime'
		);


		// $cols = array('kodeorg','nopengolahan','tanggal','shift',
		// 'jammulai','jamselesai','mandor','asisten','jamdinasbruto',
		// 'jamstagnasi','jumlahlori','tbsdiolah','jamshift','lorirestan');
		$query = insertQuery($dbname, 'pabrik_pengolahan', $data, $cols);
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {

			print " Gagal  !: " . $e->getMessage() . "<br/>";
			//die(); 
			$str = "kodeorg='" . $data['kodeorg'] .
				"' and tanggal='" . $data['tanggal'] .
				"' and shift=" . $data['shift'] .
				" and jammulai='" . $data['jammulai'] .
				"' and jamselesai='" . $data['jamselesai'] .
				"' and mandor='" . $data['mandor'] .
				"' and asisten='" . $data['asisten'] .
				"' and jamdinasbruto=" . $data['jamdinasbruto'] .
				" and jamstagnasi=" . $data['jamstagnasi'] .
				" and jumlahlori=" . $data['jumlahlori'] .
				" and tbsdiolah=" . $data['tbsdiolah'] .
				" and jamshift=" . $data['jamshift'] .
				" and keterangan=" . $data['keterangan'] .
				" and lorirestan=" . $data['lorirestan'] .
				" and restansebelum='" . $data['restansebelum'] . "' and restandidalam='" . $data['restandidalam'] . "' and restansesudah='" . $data['restansesudah'] . "'";
			$q = selectQuery($dbname, 'pabrik_pengolahan', 'nopengolahan', $str);
			$res = fetchData($q);
			//echo " Gagal".$res[0]['nopengolahan'];
		}
		echo $data['nopengolahan'];
		break;
	# Proses Edit Header
	case 'edit':
		$data = $_POST;

		$jammulai = substr($data['jammulai'], 0, 2);
		$jammulai =  floatval($jammulai);

		$menitmulai = substr($data['jammulai'], 3, 2);
		$menitmulai =  floatval($menitmulai);

		$jamselesai = substr($data['jamselesai'], 0, 2);
		$jamselesai =  floatval($jamselesai);

		$menitselesai = substr($data['jamselesai'], 3, 2);
		$menitselesai =  floatval($menitselesai);


		if ($jammulai > $jamselesai) {
			if ($menitmulai > $menitselesai) {
				$jamisi = ($jamselesai + 23) - $jammulai;
				$menitisi =  number_format((($menitselesai + 60) - $menitmulai) / 60, 2);
			} else {
				$jamisi = ($jamselesai + 24) - $jammulai;
				$menitisi =  number_format(($menitselesai - $menitmulai) / 60, 2);
			}
		} else {
			if ($menitmulai > $menitselesai) {
				$jamisi = $jamselesai - $jammulai - 1;
				$menitisi =  number_format((($menitselesai + 60) - $menitmulai) / 60, 2);
			} else {
				$jamisi = $jamselesai - $jammulai;
				$menitisi =  number_format(($menitselesai - $menitmulai) / 60, 2);
			}
		}

		$selisih = $jamisi + $menitisi;

		if ((number_format($data['jamdinasbruto'], 2) + number_format($data['jamstagnasi'], 2)) != number_format($selisih, 2)) {
			// exit("Warning: Jam proses masih salah,  masih terdapat selisih");
		}

		$where = "nopengolahan='" . $data['nopengolahan'] . "'";
		unset($data['nopengolahan']);
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		$data['updateby'] = $_SESSION['standard']['userid'];
		$query = updateQuery($dbname, 'pabrik_pengolahan', $data, $where);
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	case 'delete':
		$where = "nopengolahan=" . $param['nopengolahan'];
		$query = "delete from `" . $dbname . "`.`pabrik_pengolahan` where " . $where;
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	case 'updMandorAst':
		$mode = $param['mode'];
		$shift = $param['shift'];
		if ($mode == 'tanggal') {
			$optShift = makeOption(
				$dbname,
				'pabrik_5shift',
				'shift,shift',
				"kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and berlakusdtgl>='" .
					tanggalsystem($param['tanggal']) . "'"
			);
			if (empty($optShift)) {
				echo 'Warning : Tidak ada shift yang berlaku pada tanggal tersebut';
				exit;
			}
			$where = "kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and shift in (";
			$i = 0;
			foreach ($optShift as $row) {
				if ($i == 0) {
					$where .= $row;
				} else {
					$where .= "," . $row;
				}
				$i++;
			}
			$where .= ")";
			$cols = 'shift,mandor,asisten';
		} else {
			$cols = 'mandor,asisten';
			$where = "kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and shift=" . $param['shift'];
		}
		$query = selectQuery($dbname, 'pabrik_5shift', $cols, $where);
		$res = fetchData($query);

		$whereKary = "karyawanid in (" . $res[0]['mandor'] . "," . $res[0]['asisten'] . ")";
		$optKary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whereKary);
		// Hasil Options
		$resShift = array();
		$resMandor = array($res[0]['mandor'] => $optKary[$res[0]['mandor']]);
		$resAst = array($res[0]['asisten'] => $optKary[$res[0]['asisten']]);

		if ($mode == 'tanggal') {
			foreach ($res as $row) {
				$resShift[$row['shift']] = $row['shift'];
			}
		} else {
			$resShift = 'empty';
		}

		$result = array(
			'shift' => $resShift,
			'mandor' => $resMandor,
			'asisten' => $resAst
		);
		echo json_encode($result);
		break;
	default:
		break;
}

function formHeader($mode, $data)
{
	global $dbname;
	global $owlPDO;

	# Default Value
	if (empty($data)) {
		$new = true;
		$noOlah = selectQuery($dbname, 'pabrik_pengolahan', 'max(nopengolahan) as noMax');
		$tmpOlah = fetchData($noOlah);
		$noOlah = $tmpOlah[0]['noMax'] + 1;
		$data['kodeorg'] = '';
		// $data['nopengolahan'] = $noOlah;
		$data['nopengolahan'] = 0;
		$data['tanggal'] = '';
		$data['shift'] = '';
		$data['jammulai'] = '00:00:00';
		$data['jamselesai'] = '00:00:00';
		$data['mandor'] = '';
		$data['asisten'] = '';
		$data['jamdinasbruto'] = '0';
		$data['jamstagnasi'] = '0';
		$data['jumlahlori'] = '0';
		$data['lorirestan'] = '0';
		$data['restansebelum'] = '0';
		$data['restandidalam'] = '0';
		$data['restansesudah'] = '0';
		/*$data['kodebarang'] = '';
		$data['kapasitaslori'] = '0';
		$data['mutuolah'] = '';
		$data['randemen'] = '0';*/
		$data['tbsdiolah'] = '0';
		$data['jamshift'] = '0';
		$data['keterangan'] = '';
	} else {
		$new = false;
	}

	# Disabled Primary
	if ($mode == 'edit') {
		$disabled = 'disabled';
	} else {
		$disabled = '';
	}

	# Options
	$optOrg = makeOption(
		$dbname,
		'organisasi',
		'kodeorganisasi,namaorganisasi',
		"tipe='PABRIK' and kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'"
	);
	#$whereBarang = "kelompokbarang='400'";
	#$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whereBarang);
	$qShift = selectQuery($dbname, 'pabrik_5shift', 'shift,mandor,asisten', "kodeorg='" .
		$_SESSION['empl']['lokasitugas'] . "'");
	$tmpShift = fetchData($qShift);
	$optShift = array("0" => $_SESSION['lang']['pilihdata']);
	$whereKary = "";
	$whereKaryNew = "";

	# OptShift
	foreach ($tmpShift as $key => $row) {
		$optShift[$row['shift']] = $row['shift'];
		if ($key == 0) {
			$whereKaryNew .= "karyawanid='" . $row['mandor'] . "' or karyawanid='" . $row['asisten'] . "'";
			$whereKary .= "karyawanid='" . $row['mandor'] . "' or karyawanid='" . $row['asisten'] . "'";
		} else {
			$whereKaryNew .= " or karyawanid='" . $row['mandor'] . "' or karyawanid='" . $row['asisten'] . "'";
		}
	}

	# OptKary
	//if($new==false) {
	//	$whereKary = "";
	//	foreach($tmpShift as $key=>$row) {
	//	    $optShift[$row['shift']] = $row['shift'];
	//	    if($key==0) {
	//		$whereKary .= "karyawanid='".$row['mandor']."' or karyawanid='".$row['asisten']."'";
	//	    } else {
	//		$whereKary .= " or karyawanid='".$row['mandor']."' or karyawanid='".$row['asisten']."'";
	//	    }
	//	}
	//	$optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKaryNew);
	//	$data['mandor']=$tmpShift[0]['mandor'];
	//	$data['asisten']=$tmpShift[0]['asisten'];
	//    } else {
	//	$optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKary);
	//    }


	/*$whereKary=" tipekaryawan in (0,1,2)";
    $optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKary);
    */
	//Maintenance Group Leader

	#buat if jika vertical = cycling	
	$str = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$tipepabrik = $bar['tipepabrik'];

	if ($tipepabrik != 'VERTICAL') {
		$langproses = 'Jumlah Set Olah';/* $langproses=$_SESSION['lang']['jumlahcycle']; */
		$langrestan = $_SESSION['lang']['cyclerestan'];
	} else {
		$langproses = $_SESSION['lang']['jumlahlori'];
		$langrestan = $_SESSION['lang']['lorirestan'];
	}




	$whereKaryMandor = " lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "' and statuskaryawan != 'Keluar'  and tanggalkeluar='0000-00-00' and "
		. " kodejabatan in (select kodejabatan from " . $dbname . ".sdm_5jabatan "
		. " where namajabatan like '%mandor%') ";
	$optKaryMandor = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whereKaryMandor, '', true);


	$whereKaryAst = " lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "' and statuskaryawan != 'Keluar'  and tanggalkeluar='0000-00-00' and "
		. " kodejabatan in (select kodejabatan from " . $dbname . ".sdm_5jabatan "
		. " where  namajabatan like '%asis%') ";
	$optKaryAst = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whereKaryAst, '', true);




	$els = array();
	$els[] = array(
		makeElement('nopengolahan', 'label', $_SESSION['lang']['nopengolahan']),
		makeElement(
			'nopengolahan',
			'text',
			$data['nopengolahan'],
			array('style' => 'width:70px', 'maxlength' => '15', 'disabled' => 'disabled')
		)
	);
	$els[] = array(
		makeElement('tanggal', 'label', $_SESSION['lang']['tanggal']),
		makeElement('tanggal', 'text', $data['tanggal'], array(
			'style' => 'width:70px;text-align:center',
			'readonly' => 'readonly',
			'onmousemove' => 'setCalendar(this.id)'
		))
	);
	$els[] = array(
		makeElement('shift', 'label', $_SESSION['lang']['shift']),
		makeElement(
			'shift',
			'select',
			$data['shift'],
			// array('style'=>'width:150px','onchange'=>"updMandorAst('shift')"),$optShift)
			array('style' => 'width:75px', 'onchange' => 'getJam()'),
			$optShift
		)
	);
	$els[] = array(
		makeElement('jammulai', 'label', $_SESSION['lang']['jammulai']),
		makeElement('jammulai', 'jammenit', $data['jammulai'], array('onchange' => 'getselisih()'))
	);
	$els[] = array(
		makeElement('jamselesai', 'label', $_SESSION['lang']['jamselesai']),
		makeElement('jamselesai', 'jammenit', $data['jamselesai'], array('onchange' => 'getselisih()'))
	);

	$els[] = array(
		makeElement('jamdinasbruto', 'label', $_SESSION['lang']['jamdinasbruto']),
		makeElement('jamdinasbruto', 'textnum', $data['jamdinasbruto'], array('style' => 'width:70px', 'onclick' => 'this.select()'))
	);

	$els[] = array(
		makeElement('jamstagnasi', 'label', $_SESSION['lang']['jamstagnasi']),
		makeElement('jamstagnasi', 'textnum', $data['jamstagnasi'], array('style' => 'width:70px', 'onclick' => 'this.select()', 'onkeyup' => 'getselisihdowntime()'))
	);

	$els[] = array(
		makeElement('tbsdiolah', 'label', ''),
		makeElement('tbsdiolah', 'textnum', $data['tbsdiolah'], array('style' => 'display:none', 'disabled' => 'disabled'))
	);


	$els[] = array(
		makeElement('restansebelum', 'label', 'Restan Sebelum Sterilizer', array('style' => 'display:none')),
		makeElement('restansebelum', 'textnum', $data['restansebelum'], array('style' => 'width:150px;display:none', 'onclick' => 'this.select()'))
	);

	$els[] = array(
		makeElement('restansesudah', 'label', 'Restan Sesudah Sterilizer', array('style' => 'display:none')),
		makeElement('restansesudah', 'textnum', $data['restansesudah'], array('style' => 'width:150px;display:none', 'onclick' => 'this.select()'))
	);

	$els[] = array(
		makeElement('kodeorg', 'label', $_SESSION['lang']['pabrik']),
		makeElement(
			'kodeorg',
			'select',
			$data['kodeorg'],
			array('style' => 'width:150px'),
			$optOrg
		)
	);

	$els[] = array(
		makeElement('jumlahlori', 'label', $langproses),
		makeElement('jumlahlori', 'textnum', $data['jumlahlori'], array('style' => 'width:145px', 'onclick' => 'this.select()'))
	);

	$els[] = array(
		makeElement('lorirestan', 'label', 'Restan Ramp'),
		makeElement('lorirestan', 'textnum', $data['lorirestan'], array('style' => 'width:145px', 'onclick' => 'this.select()'))
	);

	$els[] = array(
		makeElement('restandidalam', 'label', 'Restan Di Dalam Sterilizer'),
		makeElement('restandidalam', 'textnum', $data['restandidalam'], array('style' => 'width:145px', 'onclick' => 'this.select()'))
	);

	$els[] = array(
		makeElement('jamshift', 'label', $_SESSION['lang']['jamshift']),
		makeElement('jamshift', 'textnum', $data['jamshift'], array('style' => 'width:145px', 'onclick' => 'this.select()'))
	);

	$els[] = array(
		makeElement('keterangan', 'label', $_SESSION['lang']['keterangan']),
		makeElement('keterangan', 'text', $data['keterangan'], array('style' => 'width:145px', 'onclick' => 'this.select()'))
	);


	/*$els[] = array(
	makeElement('kodebarang','label',$_SESSION['lang']['kodebarang']),
	makeElement('kodebarang','select',$data['kodebarang'],array('style'=>'width:150px'),$optBarang)
    );
    $els[] = array(
	makeElement('kapasitaslori','label',$_SESSION['lang']['kapasitaslori']),
	makeElement('kapasitaslori','textnum',$data['kapasitaslori'],array('style'=>'width:150px'))." kg"
    );
    $els[] = array(
	makeElement('mutuolah','label',$_SESSION['lang']['mutuolah']),
	makeElement('mutuolah','textnum',$data['mutuolah'],array('style'=>'width:150px'))
    );
    $els[] = array(
	makeElement('randemen','label',$_SESSION['lang']['randemen']),
	makeElement('randemen','textnum',$data['randemen'],array('style'=>'width:150px'))." kg"
    );*/

	$els[] = array(
		makeElement('mandor', 'label', ''),
		makeElement(
			'mandor',
			'select',
			$data['mandor'],
			//  array('style'=>'width:150px','disabled'=>'disabled'),$optKary)
			array('style' => 'display:none'),
			$optKaryMandor
		)
	);
	$els[] = array(
		makeElement('asisten', 'label', ''),
		makeElement(
			'asisten',
			'select',
			$data['asisten'],
			array('style' => 'display:none'),
			$optKaryAst
		)
	);


	if ($mode == 'add') {
		$els['btn'] = array(
			makeElement(
				'addHead',
				'btn',
				$_SESSION['lang']['save'],
				array('onclick' => "addDataTable()")
			)
		);
	} elseif ($mode == 'edit') {
		$els['btn'] = array(
			makeElement(
				'editHead',
				'btn',
				$_SESSION['lang']['save'],
				array('onclick' => "editDataTable()")
			)
		);
	}

	if ($mode == 'add') {
		return genElementMultiDim($_SESSION['lang']['addheader'], $els, 2);
	} elseif ($mode == 'edit') {
		return genElementMultiDim($_SESSION['lang']['editheader'], $els, 2);
	}
}
