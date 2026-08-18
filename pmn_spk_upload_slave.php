<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$jenis=checkPostGet('jenis','');


$notransaksi=checkPostGet('notransaksi','');
$namafile        = trim(checkPostGet('namafile', ''));
$path               = "fileupload/".$jenis."/";


$transportir=checkPostGet('transportir','');
$namakapal=checkPostGet('namakapal','');
$namaponton=checkPostGet('namaponton','');




switch($method){
	
	
	
	
	
	case'getkapalponton':
	
		$optkapal=$optponton="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$str="select * from ".$dbname.".pmn_5kapalponton where transportir='".$transportir."'";
		// exit("Error:$str");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$select='';
			if($bar['jenis']=='KPL'){
				if($bar['kode']==$namakapal){
					$select="selected";
				}
				$optkapal.="<option value=".$bar['kode']." ".$select.">".$bar['nama']."</option>";
			}
			
			if($bar['jenis']=='PNT'){
				if($bar['kode']==$namaponton){
					$select="selected";
				}
				$optponton.="<option value=".$bar['kode']." ".$select.">".$bar['nama']."</option>";
			}
		}
		
		echo $optkapal."####".$optponton;
		// exit("Error:A");
		
	break;
	
	
	
	
	case 'showupload':
		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>Nomor</td>
				<td>:</td>
				<td id='notransaksiupload'>".trim($notransaksi)."
				</td>
			</tr>
			<tr hidden>
				<td>Jenis</td>
				<td>:</td>
				<td id='jenisupload'>".trim($jenis)."
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p />";
		
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
		
		echo $tab;
		
		

	break;
	case 'submitfile':
	#cek data
	$tgl = date("YmdHis");
	$his = date("His");
	$data = $_POST;
	if($data['fileupload']!=''){
		if($_FILES['file']['error']==0){
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$filename = $_FILES['file']['name'];
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
				/*if($_FILES['file']['size'] <= 250000){*/
					$str = "insert into ".$dbname.".listfileupload values ('','".trim($notransaksi)."','".$filename."','".$filetype."','','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";

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
				/*}else{
					exit("warning : Ukuran file upload maksimal 250kb");
				}*/
			}else{
				exit("Warning : Format file upload harus .jpg atau .jpeg");
			}
		}
	}
	break;
	
	case 'loadfiles':
	
	$no = 0;
	$tab = "";
	$str="select * from ".$dbname.".listfileupload where notransaksi = '".trim($notransaksi)."' and status='1'";
	// exit("Error:".$str);
	$res=fetchData($str);
	if(empty($res)){
		$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		foreach($res as $key=>$val){
			$no++;
			$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
			$icon=seticonfile($val['formaticon']);
			$tab.="<td style='text-align:center'>
					<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
				</td>";
			$nfile='';
			$nfile = $val['namafile'];
			$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>
				<td align=center>
					<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a>&nbsp";
			$tab.="<img src=images/application/application_delete.png class=resicon	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
			$tab."	</td>
				</tr>";
		}
	}
	echo $tab;
	break;
	
	case'viewfile':
		$tab="";
		$tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
		echo $tab;
	break;
	
	case 'deletefile':
	$str="delete from ".$dbname.".listfileupload where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
	try{
		$owlPDO->exec($str);
		// $pathx = $path.$namafile;
		// unlink($pathx);
	}
	catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
	break;
	

	
}
?>	