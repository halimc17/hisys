<?php
//ini_set('display_errors',0);
//error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
$method             = checkPostGet('method', '');
$notransaksi        = checkPostGet('notransaksi', '');
$tanggal        = (checkPostGet('tanggal', ''));
$termin        = checkPostGet('termin', '');
$pengajuanspk        = checkPostGet('pengajuanspk', '');
$namafile        = checkPostGet('namafile', '');
$path               = "fileupload/lgl_pengajuanspk/";
$kriteriaefil = checkPostGet('kriteriaefil','');
$emodul = 'BAP';
switch ($method) {
case 'UploadFile':
		$arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}

		$nopengajuan = makeOption($dbname,'log_spkht','notransaksi,nopengajuan',"notransaksi='".$notransaksi."'");
		$tab="";
		$tab.="<fieldset style=width:96%><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>No SPK</td>
				<td>:</td>
				<td id=notransaksi>".$notransaksi."</td>
			</tr>
			<tr>
				<td>No Pengajuan SPK</td>
				<td>:</td>
				<td id=pengajuanspk>".$nopengajuan[$notransaksi]."</td>
			</tr>
			<tr style=display:none>
				<td>Tanggal BAPP</td>
				<td>:</td>
				<td id=tanggal>".$tanggal."</td>
			</tr>
			<tr>
				<td>Termin</td>
				<td>:</td>
				<td id=terminup>".$termin."</td>
			</tr>
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
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
		</fieldset>";
		
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center' width=30px>Termin</td>
					<td align='center'>Kriteria</td>
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
	if($notransaksi=='' || $pengajuanspk==''){
		exit("Warning : Nomor transaksi dan nomor pengajuan SPK di perlukan !");
	}
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
					$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$pengajuanspk."' and status='1' and namafile='".$filename."'";
					$res=fetchData($str);
					if(count($res)>0){exit("Warning : Nama file sudah ada !!!");}
					$str = "insert into ".$dbname.".listfile_lgl_pengajuanspk values ('','".$pengajuanspk."','".$filename."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$termin."')"; #exit("error".$str);
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
				exit("Warning : Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
			}
		}
	}
	break;
case 'loadfiles':
	$no = 0;
	$tab = "";
	$where='';
	if($termin!='undefined'){
		$where= " and (termin='".$termin."' or termin='')";
	}
	$nopengajuan = makeOption($dbname,'log_spkht','notransaksi,nopengajuan',"notransaksi='".$notransaksi."'");
	$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$nopengajuan[$notransaksi]."' and status='1' ".$where.""; #exit("error".$str);
	$res=fetchData($str);
	if(empty($res)){
		$tab.="<tr class=rowcontent><td colspan=6 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
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
			if(strlen($val['namafile'])>10){
				$nfile = potongtext($val['namafile'],10).$val['formaticon'];
			}else{
				$nfile = $val['namafile'];
			}
			$tab.="<td style='text-align:center'>".($val['termin'])."</td>
					<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
			<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>
				<td align=center>
					<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a>&nbsp";
			$tgl = date("Y-m-d");
			
			if($val['createdby']==$_SESSION['standard']['userid'] and substr($val['createdtime'],0,10) == $tgl){
				$tab.="<img src=images/application/application_delete.png class=resicon	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
				
			}
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
	$str="delete from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
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