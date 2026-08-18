<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method = checkPostGet('method', '');
$param = $_POST;
if(count($param)==0){$param = $_GET;}

switch($method){
	case 'getKeterangan':
		$str = "SELECT keterangan FROM ".$dbname.".sdm_5corevalues WHERE id='".$param['id']."'";
		$res = fetchdata($str);

		echo $res[0]['keterangan'];
	break;

	case 'loaddata':
		$tab.="<table id=mytable class='sortable' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['kriteria']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['penilaian']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['updateby']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['updatetime']."</th>
				<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody >";

		$str= "select * from ".$dbname.".sdm_5corevalues where jenis='core values'";
		$res= fetchdata($str);
		foreach($res as $bar){
			@$no+=1;
			$tab .= "<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td>".$bar['kriteria']."</td>
						<td>".$bar['namanilai']."</td>
						<td>".nl2br($bar['keterangan'])."</td>
						<td style='text-align:left;'>".getKary($bar['updateby'])."</td>
						<td style='text-align:left;'>".$bar['updatetime']."</td>
						<td align=center>
							<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['id']."','".$bar['kode']."','".$bar['penilaian']."')\";>
						</td>
						<td align=center>
							<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['id']."');>
						</td>
					</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;

	case 'addnew':
		$tab.="<table border=0 cellpadding=1 cellspacing=1 style=width:100%>
					<tr>
						<td style=min-width:100px valign=top>".$_SESSION['lang']['kriteria']."</td>
						<td>
							<select class='select2' style='width:570px;' id=kriteria>
								<option value=''>".$_SESSION['lang']['pilihdata']."</option>
								<option value='F'>Focus (Fokus)</option>
								<option value='A'>Ambition (Ambisi)</option>
								<option value='S'>Synergy (Sinergi)</option>
								<option value='T'>Trust (Terpercaya)</option>
								<option value='E'>Excellence (Ekstra Unggul)</option>
								<option value='R'>Responsiveness (Respon Sigap)</option>
							</select>
						</td>
					</tr>
					<tr>
						<td style=min-width:100px valign=top>".$_SESSION['lang']['penilaian']."</td>
						<td>
							<select class='select2' style='width:570px;' id=penilaian>
								<option value=''>".$_SESSION['lang']['pilihdata']."</option>
								<option value='100'>Luar Biasa (100)</option>
								<option value='80'>Sangat Baik (80)</option>
								<option value='60'>Baik (60)</option>
								<option value='40'>Dapat Ditingkatkan (40)</option>
								<option value='20'>Kurang Memuaskan (20)</option>
							</select>
						</td>
					</tr>
					<tr>
						<td style=min-width:100px valign=top>".$_SESSION['lang']['keterangan']."</td>
						<td>
							<textarea id=keterangan style='width:550px;height:175px' type=text onkeypress=\"return tanpa_kutip(event)\"></textarea> 
						</td>
					</tr>
				
	                <tr>
	                    <td><input type=hidden id=mode value=insert><input type=hidden id=id value=''></td>
	                    <td>
							<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
	                    </td>
	                </tr>
            </table>
		";
		echo $tab;
	break;

	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$data = array(
				'jenis' => 'core values',
				'kode' => $param['kode'],
				'kriteria' => $param['kriteria'],
				'penilaian' => $param['penilaian'],
				'namanilai' => $param['namanilai'],
				'keterangan' => $param['keterangan'],
				'createby' => $_SESSION['standard']['userid'],
				'createtime' => date("Y-m-d H:i:s")
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'sdm_5corevalues',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'kode' => $param['kode'],
					'kriteria' => $param['kriteria'],
					'penilaian' => $param['penilaian'],
					'namanilai' => $param['namanilai'],
					'keterangan' => $param['keterangan'],
					'updateby' => $_SESSION['standard']['userid'],
					'updatetime' => date("Y-m-d H:i:s")
				);
				$where = "id='".$param['id']."'";
				$query = updateQuery($dbname,'sdm_5corevalues',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case 'delete':
		try {
			$owlPDO->beginTransaction();

			$str = "delete from " . $dbname . ".sdm_5corevalues where id='".$param['id']."'";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
	break;
}
?>
