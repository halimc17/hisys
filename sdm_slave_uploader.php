<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method','');
$pages = checkPostGet('page','');
$notransaksi = checkPostGet('notransaksi','');
$namafile = checkPostGet('namafile','');
$path               = "fileupload/sdm_jatahbbm/";

switch($method){
case 'submitfile':
	$tgl = date("YmdHis");
	$his = date("His");
	$data = $_POST;
	$str="select * from ".$dbname.".listfile_sdm_jatahbbm where notransaksi = '".$notransaksi."' and namafile='".$_FILES['file']['name']."'";
	$res=fetchData($str);
	if($res[0]['namafile']!=''){
		exit("error : File sudah ada !!!");
	}
	
	if($notransaksi==''){
		exit('Error : Silahkan input detail terlebih dahulu dan pastikan No Transaksi sudah terisi !');
	}
	if($data['fileupload']!=''){
		if($_FILES['file']['error']==0){
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$filename = $_FILES['file']['name'];
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
				if($_FILES['file']['size'] <= 250000){
					$str = "insert into ".$dbname.".listfile_sdm_jatahbbm values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
					exit("warning : Ukuran file upload maksimal 250kb");
				}
			}else{
				exit("Warning : Format file upload harus .jpg atau .jpeg");
			}
		}
	}
	break;
	
	case 'loadfiles': 
	$no = 0;
	$tab = "";
	$str="select * from ".$dbname.".listfile_sdm_jatahbbm where notransaksi = '".$notransaksi."' and status='1'";
	$res=fetchData($str);
	$tab.="<table>";
	foreach($res as $key=>$val){
		$no++;
		$tab.="<tr class=rowcontent>
				<td style='text-align:center'>".$no."</td>";
		$nfile='';
		if(strlen($val['namafile'])>30){
			$nfile = potongtext($val['namafile'],30).$val['formaticon'];
		}else{
			$nfile = $val['namafile'];
		}
		$tab.="<td style='text-align:left;cursor:pointer'>
				<a href='".$path.$val['namafile']."' download>".$nfile."</a></td>
			<td align=center>";
		$tab.="<img src=images/application/application_delete.png class=resicon	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
		$tab."	</td>
			</tr>";
	}
	$tab.="</table>";
	echo $tab;
	break;
	case 'deletefile':
	$str="delete from ".$dbname.".listfile_sdm_jatahbbm where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
	try{
		$owlPDO->exec($str);
		$pathx = $path.$namafile;
		unlink($pathx);
	}
	catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
	break;
}
	

?>