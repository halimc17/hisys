<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method = checkPostGet('method', '');
$param = $_POST;
if(count($param)==0){$param = $_GET;}

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['kodeaplikasi']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['jabatan']."</th>
				<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody >";
		
		$str= "select * from ".$dbname.".setup_posting order by kodeaplikasi";
		$res= fetchdata($str);
		foreach($res as $bar){
			@$no+=1;
			$tab .= "<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td>".$bar['kodeaplikasi']."</td>
						<td style='text-align:left;'>".getNamaJabatan($bar['jabatan'])."</td>
						<td align=center>
							<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['kodeaplikasi']."','".$bar['jabatan']."')\";>
						</td>
						<td align=center>
							<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['kodeaplikasi']."','".$bar['jabatan']."');>
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
		$arroptApp = getEnum($dbname,'setup_posting','kodeaplikasi');
		$optApp = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($arroptApp as $value){
			$optApp.="<option value='".$value."'>".$value."</option>";
		}
		
		$optJab = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".sdm_5jabatan order by namajabatan asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optJab.="<option value='".$bar['kodejabatan']."'>".$bar['namajabatan']."</option>";
		}

		$tab.="<table border=0 cellpadding=2 cellspacing=1>
					<tr>
						<td style=min-width:150px>".$_SESSION['lang']['kodeaplikasi']."</td>
						<td>
							<select class='select2' style='width:400px;' id=kodeaplikasi>".$optApp."</select>
						</td>
					</tr>
					<tr>
						<td style=min-width:150px>".$_SESSION['lang']['jabatan']."</td>
						<td>
							<select class='select2' style='width:400px;' id=kodejabatan>".$optJab."</select>
						</td>
					</tr>
	                <tr>
	                    <td><input type=hidden id=mode value=insert>
							<input type=hidden id=kodeaplikasiold value=''>
							<input type=hidden id=kodejabatanold value=''>
						</td>
	                    <td>
							<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
	                    </td>
	                </tr>
            </table>
		";
		echo $tab;
	break;

	case 'insert':

		
		$str = "select * from ".$dbname.".setup_posting where kodeaplikasi='".$param['kodeaplikasi']."' and jabatan='".$param['kodejabatan']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Warning : Data sudah pernah diinput." );
		}


		try {
			$owlPDO->beginTransaction();
			$data = array(
				'kodeaplikasi' => $param['kodeaplikasi'],
				'jabatan'  => $param['kodejabatan']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'setup_posting',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'kodeaplikasi' => $param['kodeaplikasi'],
					'jabatan'  => $param['kodejabatan']
				);
				$where = "kodeaplikasi='".$param['kodeaplikasiold']."' and jabatan='".$param['kodejabatanold']."'";
				$query = updateQuery($dbname,'setup_posting',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case 'delete':
		try {
			$owlPDO->beginTransaction();

			$str = "delete from " . $dbname . ".setup_posting where kodeaplikasi='".$param['kodeaplikasi']."' and jabatan='".$param['kodejabatan']."'";
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
