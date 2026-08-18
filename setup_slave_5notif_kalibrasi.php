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
		try {
			$owlPDO->beginTransaction();
			$where = " id='" . $param['id'] . "'";
			$str = "update " . $dbname . ".setup_notifikasikalibrasi set posting='1' where " . $where . "";
			$owlPDO->exec($str);
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}

		break;
	case 'delete':
		try {
			$owlPDO->beginTransaction();
			$where = " id='" . $param['id'] . "'";
			$str = "delete from " . $dbname . ".setup_notifikasikalibrasi where " . $where . "";
			$owlPDO->exec($str);

			$nopp = "setup_notifikasikalibrasi_" . $param['id'] . "";

			// delete
			$strd = "delete from " . $dbname . ".listfileupload where notransaksi = '".$nopp."' ";
			$owlPDO->exec($strd);

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

			// Membuat objek DateTime dari tanggal yang diberikan
			$date = new DateTime($param['tgl_kalibrasi_selanjutnya']);

			// Mengurangi satu bulan
			$date->sub(new DateInterval('P1M'));

			// Mendapatkan tanggal hasil pengurangan
			$tanggal_baru = $date->format('Y-m-d');

			$data = array(
				'kodeorg'         => $param['kodeorg'],
				'tanggal'         => tanggalsystemn($param['tgl_kalibrasi']),
				'tanggalBerikutnya'       => tanggalsystemn($param['tgl_kalibrasi_selanjutnya']),
				'tanggalNotif'     => $tanggal_baru,
				'kriteria_file'     => $param['kriteriaefil'],
				'file'     => $param['upload'],
				'updateby'      => date("Y-m-d H:i:s"),
				'updatetime'       => $_SESSION['standard']['userid']
			);
			$where = "id='" . $param['id'] . "'";
			$query = updateQuery($dbname, 'setup_notifikasikalibrasi', $data, $where); #exit("warningcode".$query);
			try {
				$owlPDO->exec($query);

				if ($param['upload'] != '') {

					// ambil id yang baru ke insert
					$str0 = "select id from " . $dbname . ".setup_notifikasikalibrasi where kodeorg='" . $param['kodeorg'] . "' and tanggal = '" . tanggalsystemn($param['tgl_kalibrasi']) . "' and tanggalBerikutnya = '" . tanggalsystemn($param['tgl_kalibrasi_selanjutnya']) . "' ";
					$res0 = fetchdata($str0);
					$id_new = $res0[0]['id'];
					$nopp = "setup_notifikasikalibrasi_" . $id_new . "";

					// delete
					$strd = "delete from " . $dbname . ".listfileupload where notransaksi = '".$nopp."' ";
					$owlPDO->exec($strd);

					if ($_FILES['file']['error'] == 0) {
						$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
						$newfilename = str_replace($filetype, '', $_FILES['file']['name']);
						$filename = $newfilename . "_" . $tgl . "" . $filetype;
						$file_tmpname = $_FILES['file']['tmp_name'];
						if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx') || ($filetype == '.rar')) {
							if ($_FILES['file']['size'] <= 250000) {
								$str = "insert into " . $dbname . ".listfileupload values ('','" . $nopp . "','" . $filename . "','" . $filetype . "','" . $param['kriteriaefil'] . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
								try {
									$owlPDO->exec($str);
									move_uploaded_file($file_tmpname, "fileupload/notifkalibrasi/$filename");
								} catch (PDOException $e) {
									echo " Gagal," . addslashes($e->getMessage());
								}
							} else {
								exit("warning : Ukuran file upload maksimal 250kb");
							}
						} else {
							exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
						}
					}
				}

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

			$strcek = "select * from " . $dbname . ".setup_notifikasikalibrasi where kodeorg='" . $param['kodeorg'] . "' and tanggal = '" . tanggalsystemn($param['tgl_kalibrasi']) . "' and tanggalBerikutnya = '" . tanggalsystemn($param['tgl_kalibrasi_selanjutnya']) . "' ";
			$rescek = fetchdata($strcek);
			$countdata =  count($rescek);
			if ($countdata > 0) {
				exit("warning : Data Sudah Ada...");
			}


			// Membuat objek DateTime dari tanggal yang diberikan
			$date = new DateTime($param['tgl_kalibrasi_selanjutnya']);

			// Mengurangi satu bulan
			$date->sub(new DateInterval('P1M'));

			// Mendapatkan tanggal hasil pengurangan
			$tanggal_baru = $date->format('Y-m-d');

			if($param['tgl_kalibrasi'] > $param['tgl_kalibrasi_selanjutnya'] || $param['tgl_kalibrasi'] == $param['tgl_kalibrasi_selanjutnya']){
				exit("warning : Tanggal Kalibrasi nya harus lebih besar dari tanggal kalibrasi... ");
			}

			$data = array(
				'kodeorg'         => $param['kodeorg'],
				'tanggal'         => tanggalsystemn($param['tgl_kalibrasi']),
				'tanggalBerikutnya'       => tanggalsystemn($param['tgl_kalibrasi_selanjutnya']),
				'tanggalNotif'       => $tanggal_baru,
				'kriteria_file'     => $param['kriteriaefil'],
				'file'     => $param['upload'],
				'posting'     => 0,
				'createby'        => $_SESSION['standard']['userid'],
				'createtime'      => date("Y-m-d H:i:s"),
				'updateby'        => $_SESSION['standard']['userid'],
				'updatetime'      => date("Y-m-d H:i:s")
			);

			$cols = array();
			foreach ($data as $key => $row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname, 'setup_notifikasikalibrasi', $data, $cols); #exit("error".$query);
			try {
				$owlPDO->exec($query);


				if ($param['upload'] != '') {

					// ambil id yang baru ke insert
					$str0 = "select id from " . $dbname . ".setup_notifikasikalibrasi where kodeorg='" . $param['kodeorg'] . "' and tanggal = '" . tanggalsystemn($param['tgl_kalibrasi']) . "' and tanggalBerikutnya = '" . tanggalsystemn($param['tgl_kalibrasi_selanjutnya']) . "' ";
					$res0 = fetchdata($str0);
					$id_new = $res0[0]['id'];
					$nopp = "setup_notifikasikalibrasi_" . $id_new . "";
					if ($_FILES['file']['error'] == 0) {
						$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
						$newfilename = str_replace($filetype, '', $_FILES['file']['name']);
						$filename = $newfilename . "_" . $tgl . "" . $filetype;
						$file_tmpname = $_FILES['file']['tmp_name'];
						if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx') || ($filetype == '.rar')) {
							if ($_FILES['file']['size'] <= 250000) {
								$str = "insert into " . $dbname . ".listfileupload values ('','" . $nopp . "','" . $filename . "','" . $filetype . "','" . $param['kriteriaefil'] . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
								try {
									$owlPDO->exec($str);
									move_uploaded_file($file_tmpname, "fileupload/notifkalibrasi/$filename");
								} catch (PDOException $e) {
									echo " Gagal," . addslashes($e->getMessage());
								}
							} else {
								exit("warning : Ukuran file upload maksimal 250kb");
							}
						} else {
							exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
						}
					}
				}
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

		$arrmodul = getmodulefil('others');
		foreach ($arrmodul as $key => $val) {
			$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
		}

		$tab .= "
			 <table border=0 cellpadding=2 cellspacing=1>
			 	<tr>
					<td class=bintang style=min-width:150px>" . str_replace(".", " ", $_SESSION['lang']['unit']) . "</td>
					<td><select class='select2' style='width:400px;' id=kodeorg >" . $optkodeorg . "</select></td>
				</tr>
				<tr>
					<td class=bintang style=min-width:150px>Tanggal Kalibrasi</td>
					<td>
						<input type='text' class='myinputtext' id='tgl_kalibrasi' name='tgl_kalibrasi' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:396px;' readonly/>
					</td>
				</tr>
				<tr>
					<td class=bintang>Tanggal Kalibrasi Selanjutnya</td>
					<td>
					<input type='text' class='myinputtext' id='tgl_kalibrasi_selanjutnya' name='tgl_kalibrasi_selanjutnya' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:396px;' readonly/>
					</td>
				</tr>
				<tr>
					<td  class=bintang>Kriteria Upload File</td>
					<td>
						<select style='width:400px;' id='kriteriaefil'>" . $optkriteria . "</select>
					</td>
				</tr>
				<tr>
					<td class=bintang>Upload File</td>
					<td>
						<input style='width:396px;' type='file' name='upload' id='upload' class=mybutton>
					</td>
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
				<th style='text-align:center;' rowspan=2>Tanggal Kalibrasi</th>
				<th style='text-align:center;' rowspan=2>Tanggal Kalibrasi Berikutnya</th>
				<th style='text-align:center;' rowspan=2>Tanggal Notifikasi Kalibrasi</th>
				<th style='text-align:center;' rowspan=2>File</th>
				<th style='text-align:center;' rowspan=2>Posting</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['createby'] . "</th>
				<th style='text-align:center;' rowspan=2>" . $_SESSION['lang']['updatetime'] . "</th>
				<th style='text-align:center;' colspan=3>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr class=rowheader>
				<th style='text-align:center;'>" . $_SESSION['lang']['posting'] . "</th>
				<th style='text-align:center;'>" . $_SESSION['lang']['edit'] . "</th>
				<th style='text-align:center;'>" . $_SESSION['lang']['delete'] . "</th>
			</tr>
		</thead>
		<tbody >";
		$optnamakaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$optnamaorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

		$str = "select * from " . $dbname . ".setup_notifikasikalibrasi where 1=1 order by id";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no++;
			if($bar['posting'] == '1'){
				$postedd = 'Sudah Posting';
			}else{
				$postedd = 'Belum Posting';
			}
			$file_d="";
			$stra="select * from ".$dbname.".listfileupload where notransaksi = 'setup_notifikasikalibrasi_".$bar['id']."'  ";
			$resa=fetchData($stra);
			if(empty($resa))
			{
				$file_d.="-";
			}
			else
			{
				foreach($resa as $key=>$val)
				{
					if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
					{
						$file_d.="<td style='text-align:center'>
							<a href='fileupload/notifkalibrasi/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.png')
					{
						$file_d.="<td style='text-align:center'>
							<a href='fileupload/notifkalibrasi/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.pdf')
					{
						$file_d.="<td style='text-align:center'>
							<a href='fileupload/notifkalibrasi/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
						</td>";
					}
					elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
					{
						$file_d.="<td style='text-align:center'>
							<a href='fileupload/notifkalibrasi/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
						</td>";
					}
					elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
					{
						$file_d.="<td style='text-align:center'>
							<a href='fileupload/notifkalibrasi/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
						</td>";
					}
					else
					{
						$file_d.="<td style='text-align:center'>
							<a href='fileupload/notifkalibrasi/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
						</td>";
					}
				}
			}



			$tab .= "<tr class=rowcontent>";
			$tab .= "<td style='text-align:center;'>" . $no . "</td>";
			$tab.="<td style='text-align:left;'>".$optnamaorganisasi[$bar['kodeorg']]."</td>";
			$tab.="<td style='text-align:left;'>".$bar['tanggal']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['tanggalBerikutnya']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['tanggalNotif']."</td>";
			$tab.=$file_d;
			$tab.="<td style='text-align:left;'>".$postedd."</td>";
			$tab.="<td style='text-align:left;'>".$optnamakaryawan[$bar['createby']]."</td>";
			$tab.="<td style='text-align:left;'>".$bar['updatetime']."</td>";

			if($bar['posting'] == '1'){
				$tab .= "<td style='text-align:center;width:25px'><img src='images/icons/04/16/02.png' class='resicon' title='Posting'></td>";
				$tab .= "<td style='text-align:center;width:25px'></td>";
				$tab .= "<td style='text-align:center;width:25px'></td>";
			}else{
				$tab .= "<td style='text-align:center;width:25px'><img src='images/skyblue/posting.png' class='resicon' title='Posting' onclick=posting('" . $bar['id'] . "');></td>";
				$tab .= "<td style='text-align:center;width:25px'><img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','" . $bar['id'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . tanggalnormal($bar['tanggalBerikutnya']) . "')\";></td>";
				$tab .= "<td style='text-align:center;width:25px'><img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('" . $bar['id'] . "');></td>";
			}
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
