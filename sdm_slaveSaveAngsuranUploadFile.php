<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$userid = checkPostGet('userid','');
$trans_no = checkPostGet('trans_no','');
$idx = checkPostGet('idx','');
$namafile = checkPostGet('namafile','');

$method = checkPostGet('method','');

$namaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid=".$trans_no."");
switch($method){
	case 'showupload':
		$tab="";
		$tab.="<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>".$_SESSION['lang']['namakaryawan']."</td>
				<td>:</td>
				<td>
					<label style=display:none id='notransaksiupload' style='font-weight:bold'>".$trans_no."</label>
					<label style=display:none id='idxupload' style='font-weight:bold'>".$idx."</label>
					<label id='namakaryawan' style='font-weight:bold'>".$namaKary[$trans_no]."</label>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		<p />";
		
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Filename</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
		
		echo $tab;
	break;
	case 'submitfile':
		
		// $str="select * from ".$dbname.".sdm_angsuran where karyawanid = '".$trans_no."' and jenis='".$idx."'";
		// $resv=fetchData($str);
		// if(count($resv)==0){
			// exit('Error : Isikan detail transaksi terlebih dahulu.');
		// }
		
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 250000){
						$str = "insert into ".$dbname.".sdm_angsuran_upload values ('','".$data['trans_no']."','".$data['idx']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						try{
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname,"fileupload/sdm_angsuran/$filename");
						}catch(PDOException $e){
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
		$str="select * from ".$dbname.".sdm_angsuran where karyawanid = '".$trans_no."' and jenis='".$idx."'";
		$resv=fetchData($str);
		foreach($resv as $bar => $barv){
			$posting = $barv['active'];	
		}
		
		$str="select * from ".$dbname.".sdm_angsuran_upload where karyawanid = '".$trans_no."' and jenis='".$idx."' and status='1'";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
					$tab.="<td style='text-align:center'>
						<a href='fileupload/sdm_angsuran/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}elseif($val['formaticon']=='.png'){
					$tab.="<td style='text-align:center'>
						<a href='fileupload/sdm_angsuran/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.pdf'){
					$tab.="<td style='text-align:center'>
						<a href='fileupload/sdm_angsuran/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				}elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
					$tab.="<td style='text-align:center'>
						<a href='fileupload/sdm_angsuran/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
					$tab.="<td style='text-align:center'>
						<a href='fileupload/sdm_angsuran/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}else{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/sdm_angsuran/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}
				
				$tab.="<td style='text-align:left'>".$val['namafile']."</td>
					<td align=center>
						<a href='fileupload/sdm_angsuran/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if($posting==0){
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$trans_no."','".$idx."','".$val['namafile']."');\" >";
				}
				$tab."	</td>
				</tr>";
			}	
		}
		echo $tab;
	break;
	case 'deletefile':
		$str="delete from ".$dbname.".sdm_angsuran_upload where karyawanid='".$trans_no."' and jenis='".$idx."' and namafile='".$namafile."'";
		try
		{
			$owlPDO->exec($str);
			$path = "fileupload/sdm_angsuran/".$namafile;
			unlink($path);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'deletefileall':
		$str="select * from ".$dbname.".sdm_angsuran_upload where karyawanid='".$trans_no."' and jenis='".$idx."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$path = "fileupload/sdm_angsuran/".$bar['namafile'];
			unlink($path);
		}
		
		$str="delete from ".$dbname.".sdm_angsuran_upload where karyawanid='".$trans_no."' and jenis='".$idx."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	default;
}
?>
