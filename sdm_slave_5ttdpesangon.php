<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/utilities.php');

$karyawan = checkPostGet('karyawan', '');
$level = checkPostGet('level', '');
$tipe = checkPostGet('tipe', '');
$unit = checkPostGet('unit', '');
$ket = checkPostGet('ket', '');
$method = checkPostGet('method', '');
$page = checkPostGet('page', '');
$id = checkPostGet('id', '');

$values = array(
	'karyawan' => $karyawan,
	'level' => $level,
	'tipe' => $tipe,
	'unit' => $unit,
	'keterangan' => $ket,
	'keterangan' => $ket,
	'updateby' => $_SESSION['standard']['userid'],
	'updatetime' => date('YmdHis'),
);

$table  = 'sdm_5ttdpesangon';
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

switch ($method) {
	//Modify Header
	case 'loadData':
		$tab = '';
		$tab .= "<div class='table-scroll'>";
			$tab .= "<table cellspacing='1' cellpadding='7' border='0' class='sortable'>";
				$tab .= "<thead>";
					$tab .= "<tr class='rowheader'>";
						$tab .= "<th style='text-align:center'>".$_SESSION['lang']['nourut']."</th>";
						$tab .= "<th style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</th>";
						$tab .= "<th style='text-align:center'>Level</th>";
						$tab .= "<th style='text-align:center'>".$_SESSION['lang']['tipe']."</th>";
						$tab .= "<th style='text-align:center'>".$_SESSION['lang']['unit']."</th>";
						$tab .= "<th style='text-align:center'>".$_SESSION['lang']['keterangan']."</th>";
						$tab .= "<th style='text-align:center'>".$_SESSION['lang']['updateby']."</th>";
						$tab .= "<th style='text-align:center'>".$_SESSION['lang']['updatetime']."</th>";
						$tab .= "<th style='text-align:center'>".$_SESSION['lang']['action']."</th>";
					$tab .= "</tr>";
				$tab .= "</thead>";
				$tab .= "<tbody>";

				// HALAMAN
				$limit = 20;
				$page = 0;

				$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
				if (isset($_POST['page'])) {
						$page = intval($_POST['page']);
						if ($page < 0)
								$page = 0;
				}

				$offset = $page * $limit;
				$maxdisplay = ($page * $limit);

				$sql = selectQuery($dbname, $table, '*', '', "updatetime DESC");
				$res = fetchData($sql);
				$jlhbrs = count($res);
				$no = 0;
				
				$no = $maxdisplay;
				
				$sql .= " LIMIT " . $offset . "," . $limit . "";
				$data = fetchData($sql);

				if (empty($data)) {
					$tab .= "<tr class='rowcontent'>";
						$tab .= "<td colspan='9' align='center'>".$_SESSION['lang']['errdatanotexist']."</td>";						
					$tab .= "</tr>";
				}else{
					foreach ($data as $key => $value) {
						$no += 1;
						$tab .= "<tr class='rowcontent'>";
							$tab .= "<td align='center'>".$no."</td>";
							$tab .= "<td>".$value['karyawan']."</td>";
							$tab .= "<td align='center'>".$value['level']."</td>";
							$tab .= "<td align='center'>".$value['tipe']."</td>";
							$tab .= "<td>".$nmorg[$value['unit']]."</td>";
							$tab .= "<td>".$value['keterangan']."</td>";
							$tab .= "<td>".getNamaKaryawan($value['updateby'])."</td>";
							$tab .= "<td>".date('d-m-Y H:i:s',strtotime($value['updatetime']))."</td>";
							$tab .= "<td align='center'>
													<img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"edit('".$value['id']."','".$value['karyawan']."','".$value['level']."','".$value['tipe']."','".$value['unit']."','".$value['keterangan']."');\">
													<img src='images/skyblue/delete.png' class='zImgBtn' onclick='deleteData(`".$value['id']."`)' title='Delete'>
												</td>";
						$tab .= "</tr>";
					}
				}

				$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }

				$isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }

        $tab.="<tr class='rowcontent'>
									<td colspan=9 align=center>";
        if ($page == '0') {
            $tab.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $tab.="<button class=mybutton onclick=loadData(" . ($page - 1) . ");>Prev</button>";
        }
        $tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $tab.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $tab.="<button class=mybutton onclick=loadData(" . ($page + 1) . ");>Next</button>";
        }
        $tab.="</td>
            </tr>";


			$tab .= "</table>";
		$tab .= "</div>";
		echo $tab;
	break;

	case 'insert':
		$values['createdby'] = $_SESSION['standard']['userid'];
		$values['createdtime'] = date('YmdHis');
		$qIns = insertQuery($dbname, $table, $values, array_keys($values));
		$owlPDO->exec($qIns);
	break;

	case 'update':
		$sUpt = updateQuery($dbname, $table, $values, "id = '".$id."'");
		$owlPDO->exec($sUpt);
	break;

	case 'deleteData':
		$sDel = deleteQuery($dbname,$table, "id = '".$id."'");
		$owlPDO->exec($sDel);
	break;

	default:
		exit('Error:There Is No Method :)');
	break;
}
?>