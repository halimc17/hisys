<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$path  = "fileupload/sortasi/";
$param = $_POST;
$method= checkPostGet('method', '');
switch ($method) {
	case 'showupload':
		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>" . $_SESSION['lang']['ticket'] . "</td>
				<td>:</td>
				<td id='notranupload'>". $param['notransaksi']."</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td></td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."','".$param['sumber']."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>";

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;
	
	case 'submitfile':
		$data= $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				#cek duplikasi nama file
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."'";
				$res=fetchData($str);
				if(count($res)>0){
					exit("Warning : Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('".$param['notransaksi']."','".$filename."','".$filetype."','".$param['sumber']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
			}
		}
	break;
	case 'loadfiles':
		
		$no = 0;
		$tab= "";
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1' and kriteriaefil='".$param['sumber']."'";
		$res= fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
				$tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				
				$tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."','".$val['sumber']."');\" ></td>";
				$tab.="</tr>";
			}
		}
		echo $tab;
	break;
	case 'deletefile':
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."' and kriteriaefil='".$param['sumber']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;	
	case'viewfile':
		$tab="";
		$str= "select * from ".$dbname.".listfileupload where id = '".$param['idfile']."'";
		$res= fetchData($str);
		if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}
		if($res[0]['formaticon']=='.pdf'){
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:950px;height:500px;' type='application/pdf'>";
		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."'>";
		}
		
		echo $tab;
	break;	
}	
?>		