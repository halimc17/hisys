<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method = checkPostGet('method', '');
$txtSearch = checkPostGet('txtSearch', '');
$tglCari = isset($_POST['tglCari']) ? tanggalsystem($_POST['tglCari']) : '';
$kdGudang = checkPostGet('kdGudang', '');
$nmBrg = checkPostGet('nmBrg', '');
$optNma = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

switch ($method) {
    case 'list_new_data':
        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay=($page*$limit);
		$where = "";
        if ($nmBrg != '') {
            $where.=" and b.kodebarang in (select  kodebarang from " . $dbname . ".log_5masterbarang where namabarang like '%" . $nmBrg . "%')";
        }
        if ($kdGudang != '') {
            $where.=" and a.kodegudang='" . $kdGudang . "'";
        }
        if ($tglCari != '') {
            $where.=" and a.tanggal='" . $tglCari . "'";
        }

        if ($_POST['nopp'] != '') {
            if (strlen($_POST['nopp']) < 3) {
                exit("Error: masukan nopp min 3 karakter");
            } else {
                $where.=" and a.nopo in (select distinct nopo from " . $dbname . ".log_podt where nopp like '" . $_POST['nopp'] . "%')";
            }
        } else {
            if ($txtSearch != '') {
                $where.=" and a.nopo like '%" . $txtSearch . "%'";
            }
        }
		
        $sql2 = "select distinct count(*) as jmlhrow from " . $dbname . ".log_transaksiht a left join " . $dbname . ".log_transaksidt b on a.notransaksi=b.notransaksi
            where tipetransaksi=1 " . $where . "   ORDER BY a.notransaksi DESC";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$no=$maxdisplay;
        // stat_release='1'
        $str = "SELECT distinct a.notransaksi,tanggal,a.nopo,kodegudang FROM " . $dbname . ".log_transaksiht a left join " . $dbname . ".log_transaksidt b on a.notransaksi=b.notransaksi
                    where tipetransaksi=1  " . $where . "  ORDER BY a.notransaksi DESC limit " . $offset . "," . $limit . "";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($res);
		if ($row != 0) {
			$jlhbrs = $row;
			// $no = 0;
			while ($bar = $res->fetch()) {
				setIt($bar['kodeorg'], '');
				$kodeorg = $bar['kodeorg'];
				$no+=1;
				echo"<tr class=rowcontent >
			  <td align=center>" . $no . "</td>
			  <td id=td_" . $no . ">" . $bar['notransaksi'] . "</td>
			  <td>" . tanggalnormal($bar['tanggal']) . "</td>
			  <td>" . $bar['nopo'] . "</td>
							  <td>" . $optNma[$bar['kodegudang']] . "</td>";
				?>
				<td>			
					<button class=mybutton onclick="previewBapb('<?php echo $bar['notransaksi'] ?>', event);" ><? echo $_SESSION['lang']['print'] ?>
					</button>
				</td>

				<?php
				echo"</tr>";
			} echo"
				<tr><td colspan=8 align=center>
				" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
				<button class=mybutton onclick=cariPage(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
				<button class=mybutton onclick=cariPage(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
				</td>
				</tr><input type=hidden id=nopp_" . $no . " name=nopp_" . $no . " value='" . $bar['nopp'] . "' />";
		} else {
			echo"<tr class=rowcontent><td colspan=6>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
		}

        break;

	case 'loadData':
		$where="";
		/* if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
           	 $where.= " and substr(kodegudang,1,4) in ('".implode("','",getdetailakses())."')";
        }else{
             $where.= " and kodegudang like '".$_SESSION['empl']['lokasitugas']."%'";
        } */
           	 $where.= " and substr(kodegudang,1,4) in (".getOrgDetail(2).")";
		
        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		$maxdisplay=($page*$limit);
		
		
		
        $sql2 = "select distinct count(*) as jmlhrow from " . $dbname . ".log_transaksiht where tipetransaksi=1 ".$where." ORDER BY notransaksi DESC";
		// exit('error'.$sql2);
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$no=$maxdisplay;
        // stat_release='1'
        $str = "SELECT distinct * FROM " . $dbname . ".log_transaksiht where tipetransaksi=1 ".$where."  ORDER BY tanggal desc, notransaksi DESC limit " . $offset . "," . $limit . "";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		// $no = 0;
		while ($bar = $res->fetch()) {
			setIt($bar['kodeorg'], '');
			$kodeorg = $bar['kodeorg'];
			$no+=1;
			echo"<tr class=rowcontent >
				<td align=center>" . $no . "</td>
				<td id=td_" . $no . ">" . $bar['notransaksi'] . "</td>
				<td>" . tanggalnormal($bar['tanggal']) . "</td>
				<td>" . $bar['nopo'] . "</td>
				<td>" . $optNma[$bar['kodegudang']] . "</td>";
			?>
			<td>			
				<button class=mybutton onclick="previewBapb('<?php echo $bar['notransaksi'] ?>', event);" ><? echo $_SESSION['lang']['print'] ?>
				</button>
			</td>

			<?php
			echo"</tr>";
		} 
		
		echo"<tr><td colspan=8 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr><input type=hidden id=nopp_" . $no . " name=nopp_" . $no . " value='" . $bar['nopp'] . "' />";
        
        break;

    default:
        break;
}
?>