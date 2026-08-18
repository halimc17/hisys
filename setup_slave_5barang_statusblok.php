<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('lib/nangkoelib.php');

$method   = checkPostGet('method', '');
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

switch ($method) {
	case 'posting':
		exit("warning : !posting... ");
		break;
	case 'delete':
		try {
			$owlPDO->beginTransaction();
			$where = " id='" . $param['id'] . "'";
			$str = "delete from " . $dbname . ".setup_barangstatusblok where " . $where . "";
			$owlPDO->exec($str);

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}

		break;
	case 'update':
		try {
			$owlPDO->beginTransaction();

			$data = array(
				'kodeorg'         => $param['kodeorg'],
				'kodebarang'         => $param['kodebarang'],
				'status'         => $param['status'],
				'updateby'      => date("Y-m-d H:i:s"),
				'updatetime'       => $_SESSION['standard']['userid']
			);
			$where = "id='" . $param['id'] . "'";
			$query = updateQuery($dbname, 'setup_barangstatusblok', $data, $where); #exit("warningcode".$query);
			try {
				$owlPDO->exec($query);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();

			$strcek = "select * from " . $dbname . ".setup_barangstatusblok where kodeorg='" . $param['kodeorg'] . "' and kodebarang = '".$param['kodebarang']."' ";
			$rescek = fetchdata($strcek);
			$countdata =  count($rescek);
			if ($countdata > 0) {
				exit("warning : Data Sudah Ada...");
			}

			$data = array(
				'kodeorg'         => $param['kodeorg'],
				'kodebarang'         => $param['kodebarang'],
				'status'         => $param['status'],
				'createby'        => $_SESSION['standard']['userid'],
				'createtime'      => date("Y-m-d H:i:s"),
				'updateby'        => $_SESSION['standard']['userid'],
				'updatetime'      => date("Y-m-d H:i:s")
			);

			$cols = array();
			foreach ($data as $key => $row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname, 'setup_barangstatusblok', $data, $cols); #exit("error".$query);
			try {
				$owlPDO->exec($query);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}

		break;

	case 'addnew':

		$optkodeorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$str = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)='4' order by namaorganisasi";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optkodeorg .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['namaorganisasi'] . "</option>";
		}
		$optkodebarang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$str = "select distinct kodebarang,namabarang from " . $dbname . ".log_5masterbarang where inactive='0' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optkodebarang .= "<option value='" . $bar['kodebarang'] . "'>" . $bar['namabarang'] . " - [".$bar['kodebarang']."]</option>";
		}
		
		$optstatus = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$optstatus .= "<option value='1'>Aktif</option>";
		$optstatus .= "<option value='0'>Tidak Aktif</option>";


		$tab .= "
			 <table border=0 cellpadding=2 cellspacing=1>
			 	<tr>
					<td class=bintang style=min-width:150px>" . str_replace(".", " ", $_SESSION['lang']['unit']) . "</td>
					<td><select class='select2' style='width:400px;' id=kodeorg >" . $optkodeorg . "</select></td>
				</tr>
				<tr>
					<td class=bintang style=min-width:150px>Barang</td>
					<td><select class='select2' style='width:400px;' id=kodebarang >" . $optkodebarang . "</select></td>
				</tr>
				<tr>
					<td class=bintang style=min-width:150px>Status</td>
					<td><select class='select2' style='width:400px;' id=status >" . $optstatus . "</select></td>
				</tr>
                <tr>
                    <td colspan=4 align=center>
						<input type=hidden id=id >
						<input type=hidden id=method value=insert>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
		break;
	case 'loaddata':
		$tab .= "<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
				<th style='text-align:center;' rowspan=2>" . str_replace(".", " ", $_SESSION['lang']['unit']) . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['barang'] . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['status'] . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['createby'] . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['updatetime'] . "</th>
				<th style='text-align:center;' colspan=1>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr class=rowheader>
				<!--<th style='text-align:center;'>" . $_SESSION['lang']['posting'] . "</th>-->
				<th style='text-align:center;'>" . $_SESSION['lang']['edit'] . "</th>
				<!--<th style='text-align:center;'>" . $_SESSION['lang']['delete'] . "</th>-->
			</tr>
		</thead>
		<tbody >";
		$optnamakaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$optnamaorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		$optnamabarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');

		$str = "select * from " . $dbname . ".setup_barangstatusblok where 1=1 order by id";
		$res = fetchdata($str);
		foreach ($res as $bar) {

			if($bar['status'] == '1'){
				$status_k = 'Aktif';
			}else{
				$status_k = 'Tidak Aktif';
			}

			$no++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td style='text-align:center;'>" . $no . "</td>";
			$tab.="<td style='text-align:center;'>".$optnamaorganisasi[$bar['kodeorg']]."</td>";
			$tab.="<td style='text-align:center;'>".$optnamabarang[$bar['kodebarang']]."</td>";
			$tab.="<td style='text-align:center;'>".$status_k."</td>";
			$tab.="<td style='text-align:center;'>".$optnamakaryawan[$bar['createby']]."</td>";
			$tab.="<td style='text-align:center;'>".$bar['updatetime']."</td>";
			$tab .= "<td style='text-align:center;width:25px'><img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','" . $bar['id'] . "','" . $bar['kodeorg'] . "','" . $bar['kodebarang'] . "','" . $bar['status'] . "')\";></td>";
			$tab .= "</tr>";

			$n = $d;
			$o = $e;
		}

		$tab .= "</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
		break;
}
