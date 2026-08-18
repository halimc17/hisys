<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
$zel         = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
$method      = checkPostGet('method', '');
$tpKary      = checkPostGet('tpKary', '');
$optThn      = checkPostGet('optThn', '');
$pilInp      = checkPostGet('pilInp', '');
$karyawanId  = checkPostGet('karyawanId', '');
$idKomponen  = checkPostGet('idKomponen', '');
$jmlhDt      = checkPostGet('jmlhDt', '');
$thn         = checkPostGet('thn', '');
$golongan    = checkPostGet('golongan', '');
$kdUnitCr    = checkPostGet('kdUnitCr', '');
$kdUnit      = checkPostGet('kdUnit', '');
$namaKary    = checkPostGet('namaKary', '');
$tpKaryCr    = checkPostGet('tpKaryCr', '');
$idKomponenCr= checkPostGet('idKomponenCr', '');
$idjabatan   = checkPostGet('idjabatan', '');
$vpage       = checkPostGet('vpage', '');
$page        = checkPostGet('page', '');
$showhide    = checkPostGet('showhide', '');

$jmlhDt      =str_replace(',','',$jmlhDt);
$optGol1     = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
$optGol      = makeOption($dbname, 'datakaryawan', 'karyawanid,kodegolongan');
$optUnit     = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
$optTip      = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$optNikKar   = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');#sudah diakomodasi dengan left join pada display
$optNmKar    = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optTipe     = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan');
$optJbtn     =makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$optKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');
switch ($method) {
	case 'getKar':
	$karyPdf = "karyawanid in (";
	$optTipe2 = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	if ($kdUnit != '') {
		$whr.="and lokasitugas='".$kdUnit."'";
	}
	if ($tpKary != '') {
		$whr.="and tipekaryawan='".$tpKary."'";
	}
	if ($golongan != '') {
		$whr.=" and kodegolongan='".$golongan."'";
	}
	if ($_POST['jabatan'] != '') {
		$whr.=" and kodejabatan='".$_POST['jabatan']."'";
	}
	$i = "select * from ".$dbname.".datakaryawan where lokasitugas!='' ".$whr."";
	$n = $owlPDO->query($i)or die(print " Gagal: ".PDOException::getMessage());
	$n->setFetchMode(PDO::FETCH_ASSOC);
	while ($d = $n->fetch()) {
		$ader += 1;
		$optTipe2.="<option value='".$d['karyawanid']."'>".$d['namakaryawan']." - ".$d['subbagian']." - ".$d['nik']." - ".$optJbtn[$d['kodejabatan']]."</option>";
		if ($ader == 1) {
			$karyPdf.=$d['karyawanid'];
		} else {
			$karyPdf.=",".$d['karyawanid'];
		}
	}
	$karyPdf.=") and tahun=".date('Y')."";
	echo $optTipe2;
	break;
	case 'insert':
		$tempTgl = date('Y-m')."-01";
		if ($kdUnit != '') {
			$where = " and lokasitugas='".$kdUnit."'";
		}
		if ($tpKary == '') {
			echo "Warning : silakan pilih tipe karyawan";
			exit;
		}
		if ($idKomponen == '') {
			echo "Warning : Component is obligatory";
			exit;
		}
		if (intval($jmlhDt) == '0') {
			echo "Warning : Please fill amount(jumlah)".$jmlhDt;
			exit;
		}
		if ($karyawanId == '' && $pilInp == '0') {
			exit("Warning : Bila pilihan perorang, maka namakaryawan harus diisi \n if you choose the option per person, the employee's name can not be blank ");
		}
		if ($pilInp == 0) {
			 # jika per orangan
			$i = "delete from ".$dbname.".sdm_5gajipokokho where tahun='".$thn."' and karyawanid='".$karyawanId."' and idkomponen='".$idKomponen."'";
			try {
				$owlPDO->exec($i);
				$n = "insert into ".$dbname.".sdm_5gajipokokho values ('".$thn."','".$karyawanId."','".$idKomponen."','".$jmlhDt."')";
				try {
					$owlPDO->exec($n);
				} catch (PDOException $e) {
					echo "Gagal".$e->getMessage();
				}
			} catch (PDOException $e) {
				echo " Gagal,".addslashes($e->getMessage());
			}
		}
		if ($pilInp == 1) {
			$whrDt = "";
			if ($_POST['jabatan'] != '') {
				$whrDt.=" and kodejabatan='".$_POST['jabatan']."'";
			}
			if ($golongan != '') {
				$whrDt.=" and kodegolongan='".$golongan."'";
			}
			$x = "select distinct karyawanid from ".$dbname.".datakaryawan where 1=1 ".$where."
				and tipekaryawan='".$tpKary."' and (tanggalkeluar='0000-00-00' or tanggalkeluar>".$tempTgl.")
				".$whrDt."";
			$y = fetchData($x);
			foreach($y as $row => $z) {
				$i = "delete from ".$dbname.".sdm_5gajipokokho where tahun='".$thn."' and karyawanid='".$z['karyawanid']."' and idkomponen='".$idKomponen."'";
				try {
					$owlPDO->exec($i);
					$n = "insert into ".$dbname.".sdm_5gajipokokho values ('".$thn."','".$z['karyawanid']."','".$idKomponen."','".$jmlhDt."')";
					try {
						$owlPDO->exec($n);
					} catch (PDOException $e) {
						echo " Gagal,".addslashes($e->getMessage());
					}
				} catch (PDOException $e) {
					echo " Gagal,".addslashes($e->getMessage());
				}
			}
		}
		break;
	case 'loadData':
		$whrd = '';
		if ($optThn != '') {
			$whrd.=" and a.tahun='".$optThn."'";
		}
		if ($namaKary != '') {
			$whrd.=" and b.namakaryawan like '%".$namaKary."%'";
		}
		if ($tpKaryCr != '') {
			$whrd.=" and b.tipekaryawan = '".$tpKaryCr."'";
		}
		if ($idKomponenCr != '') {
			$whrd.=" and a.idkomponen='".$idKomponenCr."'";
		}
		if ($idjabatan != '') {
			$whrd.=" and b.kodejabatan='".$idjabatan."'";
		}
		$limit = 30;
		//$page = 0;
		if (isset($page)) {
			//$page = $vpage;
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$ql2 = "select count(*) as jmlhrow,b.namakaryawan,b.tipekaryawan from ".$dbname.".sdm_5gajipokokho a "
			." left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 ".$whrd." ";
		$query2 = $owlPDO->query($ql2)or die(print " Gagal: ".PDOException::getMessage()."___".$ql2);
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}
		echo "<img onclick=\"dataKeExcel(event,'".$kdUnitCr."','".$optThn."','".$namaKary."','".$tpKaryCr."','".$idKomponenCr."','".$idjabatan."')\" src=\"images/excel.jpg\" class=\"resicon\" title=\"MS.Excel\">
		<table class=sortable cellspacing=1 border=0 style='min-width:955px'>
		<thead>
		<tr class=rowheader>
		<td align=center>No</td>
		<td align=center>".$_SESSION['lang']['tahun']."</td>
		<td align=center>".$_SESSION['lang']['unitkerja']."</td>
		<td align=center>".$_SESSION['lang']['nik']."</td>
		<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
		<td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
		<td align=center>".$_SESSION['lang']['kodegolongan']."</td>
		<td align=center>".$_SESSION['lang']['kodejabatan']."</td>
		<td align=center>".$_SESSION['lang']['idkomponen']."</td>
		<td align=center>".$_SESSION['lang']['jumlah']."</td>
		<td align=center>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody>";
		$str = "select a.*,b.namakaryawan,b.tipekaryawan,b.kodegolongan,b.nik,b.lokasitugas,b.kodejabatan from ".$dbname.".sdm_5gajipokokho a "
			." left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 ".$whrd." order by b.lokasitugas, b.namakaryawan asc "
			." limit ".$offset.",".$limit." ";
		$no = $maxdisplay;
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$oow = owlBaris($res);
		while ($bar = $res->fetch()) {
			$no += 1;
			$optJab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='".$bar['kodejabatan']."'");
			echo "<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center>".$bar['tahun']."</td>
			<td align=center>".$bar['lokasitugas']."</td>
			<td>".$bar['nik']."</td>
			<td>".$bar['namakaryawan']."</td>
			<td>".$optTip[$bar['tipekaryawan']]."</td>
			<td>".$bar['kodegolongan']." - ".$optGol1[$bar['kodegolongan']]."</td>
			<td>".$optJab[$bar['kodejabatan']]."</td>
			<td>".$optKomponen[$bar['idkomponen']]."</td>";
			if ($showhide == 1) {
				echo "<td align=right>*********</td>  ";
			}
			else {
				echo "<td align=right>".$bar['jumlah']."</td>  ";
			}
			echo "<td align=center>
			<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['tahun']."','".$bar['karyawanid']."','".$optTipe[$bar['karyawanid']]."','".$bar['idkomponen']."','".$bar['jumlah']."','".$zel[$bar['karyawanid']]."','".$optNmKar[$bar['karyawanid']]."','".$optGol[$bar['karyawanid']]."');\">
			<img src=images/application/application_delete.png class=resicon  title='Delete Data' onclick=\"delData('".$bar['tahun']."','".$bar['karyawanid']."','".$bar['idkomponen']."');\">
			</td>
			</tr>";
		}
		echo "<tr class=rowheader><td colspan=11 align=center>
		".(($page * $limit) + 1)." to ".(($page + 1) * $limit)." Of ".$jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page - 1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page + 1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo "</tbody>
		<tfoot>
		</tfoot>
		</table>";
		break;
	case 'updateData':
		if ($pilInp == 0) {
			$sdel = "delete from ".$dbname.".sdm_5gajipokokho where karyawanid='".$karyawanId."'
				and idkomponen='".$idKomponen."' and tahun='".$thn."'";
			try {
				$owlPDO->exec($sdel);
				$sIns = "insert into ".$dbname.".sdm_5gajipokokho
					values ('".$thn."','".$karyawanId."','".$idKomponen."','".$jmlhDt."')";
				try {
					$owlPDO->exec($sIns);
				} catch (PDOException $e) {
					echo "Gagal".$e->getMessage();
				}
			} catch (PDOException $e) {}
		} else {
			$sdata = "select distinct karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."'
				and tipekaryawan='".$tpKary."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
			$qData = $owlPDO->query($sdata)or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while ($rdata = $qData->fetch()) {
				$sdel = "delete from ".$dbname.".sdm_5gajipokokho where karyawanid='".$rdata['karyawanid']."'
					and idkomponen='".$idKomponen."' and tahun='".$thn."'";
				try {
					$owlPDO->exec($sdel);
					$sIns = "insert into ".$dbname.".sdm_5gajipokokho
						values ('".$thn."','".$rdata['karyawanid']."','".$idKomponen."','".$jmlhDt."')";
					try {
						$owlPDO->exec($sIns);
					} catch (PDOException $e) {
						echo "Gagal".$sIns."____".$e->getMessage();
					}
				} catch (PDOException $e) {
					echo "Gagal".$sdel."____".$e->getMessage();
				}
			}
		}
		break;
	case 'delData':
		$sdel = "delete from ".$dbname.".sdm_5gajipokokho where karyawanid='".$_POST['karyawanId']."'
			and idkomponen='".$_POST['idKomponen']."' and tahun='".$_POST['optThn']."'";
		try {
			$owlPDO->exec($sdel);
		} catch (PDOException $e) {
			echo "Gagal".$sdel."____".$e->getMessage();
		}
		break;
}
?>