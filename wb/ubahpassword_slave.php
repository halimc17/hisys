<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

switch($method){
	case 'loaddata':
		$tab="";
		
		$tab.="<fieldset>
		<legend>Form</legend>
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Username</td>
					<td>:</td>
					<td style='font-size:16px'><label id='username'>".$_SESSION['standard']['username']."</label></td>
				</tr>
				<tr>
					<td>Password Lama</td>
					<td>:</td>
					<td>
						<input type='password' class=myinputtext style='width:200px;height:30px;font-size:14px;text-align' id='passwordlama'>
					</td>
				</tr>
				<tr>
					<td>Password Baru</td>
					<td>:</td>
					<td>
						<input type='password' class=myinputtext style='width:200px;height:30px;font-size:14px;text-align' id='passwordbaru'>
					</td>
				</tr>
				<tr>
					<td>Ulangi Password Baru</td>
					<td>:</td>
					<td>
						<input type='password' class=myinputtext style='width:200px;height:30px;font-size:14px;text-align' id='passwordbaruverifikasi'>
					</td>
				</tr>
                <tr>
                    <td colspan=2></td>
                    <td>
						<button id='btnsimpan' style='width:120px;height:30px' class=mybutton onclick=\"simpan();\">Rubah</button>
                    </td>
                </tr>
            </table>
		</fieldset>";
		
		echo $tab;
	break;
	
	case 'simpan':
		try{
			$owlPDO->beginTransaction();
			
			$str="select * from ".$dbname.".user where uname='".$param['username']."' and password=PASSWORD('".$param['passwordlama']."')";
			$res=fetchdata($str);
			if(count($res) <= 0){
				throw new PDOException('Password lama tidak sesuai dengan yang ada di sistem');
			}
			
			$data = array(
				'password'		=> "PASSWORD('".$param['passwordbaru']."')",
				'lastuser'		=> $_SESSION['standard']['username']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = updateQuery($dbname,'user',$data,"uname='".$param['username']."'");
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Gagal, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
