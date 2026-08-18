<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

switch($method){
	case'getmill':
		$optmill="<option value=''>Silahkan pilih</option>";
		
		$str="select * from ".$dbname.".msunit where compcode='".$param['pt']."' and tipe in ('PABRIK','KEBUN') and unitstatus='1'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($param['millcode']==$val['unitcode']){
				$optmill.="<option value='".$val['unitcode']."' selected>".$val['unitname']."</option>";					
			}else{
				$optmill.="<option value='".$val['unitcode']."'>".$val['unitname']."</option>";					
			}
		}
		echo $optmill;
	break;
	
	case 'loaddata':
		$tab="";
		
		## GET DATA
		$countdata=0;
		$stydis="disabled";
		$mthd="insert";
		$str="select * from ".$dbname.".mssystem";
		$res=fetchdata($str);
		$countdata=count($res);
		if($countdata > 0){
			$pt=$res[0]['compcode'];
			$millcode=$res[0]['millcode'];
			$millname=$res[0]['millname'];
			$alamat1=$res[0]['alamat1'];
			$alamat2=$res[0]['alamat2'];
			$managername=$res[0]['managername'];
			$ktuname=$res[0]['ktuname'];
			$labname=$res[0]['labname'];
			$idwb=$res[0]['idwb'];
			$port = $res[0]['port'];
			$baudrate = $res[0]['baudrate'];
			$databit = $res[0]['databit'];
			$parity = $res[0]['parity'];
			$stopbit = $res[0]['stopbit'];
			$emailNotif = $res[0]['email_notif'];
			$stydis="disabled";	
			$mthd="update";
		}
		
		## GET PERUSAHAAN
		$compc="";
		$optperusahaan="<option value=''>Pilih data</option>";	
		$str="select compcode,compname from ".$dbname.".mscompany where compstatus='1' order by compname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			if($pt==$val['compcode']){
				$optperusahaan.="<option value='".$val['compcode']."' selected>".$val['compname']."</option>";
				$compc=$val['compcode'];
			}else{
				$optperusahaan.="<option value='".$val['compcode']."'>".$val['compname']."</option>";					
			}
		}
		
		## GET MILL
		$optunit="<option value=''>Pilih data</option>";	
		$str="select unitcode,unitname,compcode from ".$dbname.".msunit where unitstatus='1' and compcode='".$compc."' order by unitname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			if($millcode==$val['unitcode']){
				$optunit.="<option value='".$val['unitcode']."' selected>".$val['unitname']."</option>";					
			}else{
				$optunit.="<option value='".$val['unitcode']."'>".$val['unitname']."</option>";					
			}
		}
		
		$tab.="<fieldset>
		<legend>Form</legend>
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Perusahaan</td>
					<td>
						<select class='select2' style='width:360px;height:25px;' id='kodeperusahaan' ".$stydis." onchange=\"getmill()\">".$optperusahaan."</select>
					</td>
				</tr>
				<tr>
					<td>Mill Name</td>
					<td>
						<select class='select2' style='width:360px;height:25px;' id='millcode' ".$stydis.">".$optunit."</select>
					</td>
				</tr>
				<tr style='display:none'>
					<td>Deskripsi</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' ".$stydis." onkeydown='upperCaseF(this)' value='".$millname."' id=deskripsi>
					</td>
				</tr>
					<td>Alamat</td>
					<td>
						<input class=myinputtext maxlength=250 style='width:345px;height:30px;font-size:14px;' ".$stydis." value='".$alamat1."' id=alamat1>
					</td>
				</tr>
					<td></td>
					<td>
						<input class=myinputtext maxlength=250 style='width:345px;height:30px;font-size:14px;' ".$stydis." value='".$alamat2."' id=alamat2>
					</td>
				</tr>
				<tr>
					<td>Nama Manager</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' ".$stydis." onkeydown='upperCaseF(this)' value='".$managername."' id=manager>
					</td>
				</tr>
				<tr>
					<td>Nama Kord Sec</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' ".$stydis." onkeydown='upperCaseF(this)' value='".$ktuname."' id=ktu>
					</td>
				</tr>
				<tr>
					<td>Nama Pengawas Laboratorium</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' ".$stydis." onkeydown='upperCaseF(this)' value='".$labname."' id=labname>
					</td>
				</tr>
				<tr>
					<td>Email Notifikasi</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' ".$stydis." value='{$emailNotif}' id=email>
					</td>
				</tr>
				<tr>
					<td>ID Timbangan</td>
					<td>
						<input class=myinputtext maxlength=20 style='width:100px;height:30px;font-size:14px;text-align:center' ".$stydis." onkeydown='upperCaseF(this)' value='".$idwb."' id=idtimbangan>
					</td>
				</tr>
				<tr>
					<td>Port Timbangan</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;text-align:center' ".$stydis." value='{$port}' id=porttimbangan>
					</td>
				</tr>
				<tr>
					<td>Baudrate Timbangan</td>
					<td>
						<input class=myinputtext maxlength=20 style='width:345px;height:30px;font-size:14px;text-align:center' ".$stydis."  value='{$baudrate}' id=baudratetimbangan>
					</td>
				</tr>
				<tr>
					<td>Databit Timbangan</td>
					<td>
						<input class=myinputtext maxlength=20 style='width:345px;height:30px;font-size:14px;text-align:center' ".$stydis." value='{$databit}' id=databittimbangan>
					</td>
				</tr>
				<tr>
					<td>Parity</td>
					<td>
						<input class=myinputtext maxlength=20 style='width:345px;height:30px;font-size:14px;text-align:center' ".$stydis." value='{$parity}' id=paritytimbangan>
					</td>
				</tr>
				<tr>
					<td>Stopbit</td>
					<td>
						<input class=myinputtext maxlength=20 style='width:345px;height:30px;font-size:14px;text-align:center' ".$stydis." value='{$stopbit}' id=stopbittimbangan>
					</td>
				</tr>
                <tr>
                    <td><input type=hidden id=method value='".$mthd."'></td>
                    <td colspan=4>
						<button id='simpan' onclick=simpan(); style='width:120px;height:30px' class=mybutton disabled>Simpan</button>
						<button id='rubah' onclick=rubah(); style='width:120px;height:30px' class=mybutton>Rubah</button>
                    </td>
                </tr>
            </table>
		</fieldset>";
		
		echo $tab;
	break;
	
	case 'insert':
		try{
			$owlPDO->beginTransaction();
			
			$data = array(
				'millcode'		=> $param['millcode'],
				'millname' 		=> $param['deskripsi'],
				'compcode' 		=> $param['kodeperusahaan'],
				'alamat1' 		=> $param['alamat1'],
				'alamat2' 		=> $param['alamat2'],
				'managername' 	=> $param['manager'],
				'ktuname' 		=> $param['ktu'],
				'idwb' 			=> $param['idtimbangan'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'mssystem',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			$str="delete from ".$dbname.".mssystem";
			$owlPDO->exec($str);
			
			$optdescode=makeOption($dbname,'mscompany','compcode,descode1',"compcode='".$param['kodeperusahaan']."'");
			$optname=makeOption($dbname,'mscompany','compcode,compname',"compcode='".$param['kodeperusahaan']."'");
			$optdescode2=makeOption($dbname,'msunit','unitcode,descode1',"unitcode='".$param['millcode']."'");
			$optname2=makeOption($dbname,'msunit','unitcode,unitname',"unitcode='".$param['millcode']."'");
				
			$data = array(
				'millcode'		=> $param['millcode'],
				'descmillcode'	=> $optdescode2[$param['millcode']],
				'millname' 		=> $optname2[$param['millcode']],
				'compcode' 		=> $param['kodeperusahaan'],
				'descompcode' 	=> $optdescode[$param['kodeperusahaan']],
				'compname'	 	=> $optname[$param['kodeperusahaan']],
				'alamat1' 		=> $param['alamat1'],
				'alamat2' 		=> $param['alamat2'],
				'managername' 	=> $param['manager'],
				'ktuname' 		=> $param['ktu'],
				'labname' 		=> $param['labname'],
				'idwb' 			=> $param['idtimbangan'],
				'port'			=> $param['port'],
				'baudrate'		=> $param['baudrate'],
				'databit'		=> $param['databit'],
				'parity'		=> $param['parity'],
				'stopbit'		=> $param['stopbit'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'mssystem',$data,$cols);
			$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
