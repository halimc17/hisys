<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses','');
$id = checkPostGet('id','');
$file = checkPostGet('file','');
$fileupload = checkPostGet('fileupload','');
$tipepeta = checkPostGet('tipepeta','');
$namapeta = checkPostGet('namapeta','');
$namapeta = trim($namapeta);
$kodept = checkPostGet('kodept','');
$kebun = checkPostGet('kebun','');
$revisi = checkPostGet('revisi','');
$status = checkPostGet('status','');
$nourut = checkPostGet('nourut','');
$method = checkPostGet('method','');
$namafile = checkPostGet('namafile','');
$page = checkPostGet('pages','0');
$ptscr = checkPostGet('ptscr','');
$unitscr = checkPostGet('unitscr','');
$tipepetascr = checkPostGet('tipepetascr','');
$statusscr = checkPostGet('statusscr','');
$namapetascr = checkPostGet('namapetascr','');
$namafilescr = checkPostGet('namafilescr','');
$revisiscr = checkPostGet('revisiscr','');
$tglscr = checkPostGet('tglscr','');
	
$path = "fileupload/uploadpeta/";
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmpeta=makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan');
	
switch($proses){
	case 'simpan':
		if($namapeta == '' or $tipepeta=='' or $namapeta=='' or $kodept=='' or $kebun==''){
			exit("warning : PT, Unit, Tipe Peta, Nama Peta harus diisi !!!");
		}					
		$str="select * from ".$dbname.".listfile_umm_uploadpeta where pt='".$kodept."' and unit='".$kebun."' and tipepeta='".$tipepeta."' and namapeta='".$namapeta."' and revisi='".$revisi."'";
		$res=fetchData($str);
		if(count($res)>0){
			exit("Warning : Peta ".$namapeta." Revisi ".$revisi." sudah ada !!!");
		}
		
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;
		
		if($status==''){
			$status='0';
		}
		
		if($_FILES['file']==''){
			exit("warning : File belum di pilih !!!");
		}
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				$filename = str_replace("'","",$filename);
				$filename = str_replace("&quot;","",$filename);
				$filename = str_replace("%","",$filename);
				$filename = str_replace("&","",$filename);
				
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfile_umm_uploadpeta values ('','".$kodept."','".$kebun."','".$tipepeta."','".$namapeta."','".$revisi."','".$filename."','".$filetype."','".$_FILES['file']['size']."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					try{$owlPDO->exec($str);
						$folder=$kodept."/".$kebun."/";
						if (!file_exists($path.$folder)) {
							mkdir($path.$folder, 0777, true);
						}
						file_put_contents($path.$folder.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
	break;
	
	case 'deldata':
		$str = "delete from ".$dbname.".bi_map_basic where idsvg = '".$idsvg."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case'loaddata':
		$where = "";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where.="";
		}else{
			$where.=" and pt = '".$_SESSION['empl']['kodeorganisasi']."'";
		}
		if ($ptscr != '') {$where.=" and pt='" . $ptscr . "' ";}
		if ($unitscr != '') {$where.=" and unit='" . $unitscr . "' ";}
		if ($tipepetascr != '') {$where.=" and tipepeta='" . $tipepetascr . "' ";}
		if ($statusscr != '') {$where.=" and status='" . $statusscr . "' ";}
		if ($revisiscr != '') {$where.=" and revisi='" . $revisiscr . "' ";}
		if ($namapetascr != '') {$where.=" and namapeta like '%" . $namapetascr . "%' ";}
		if ($namafilescr != '') {$where.=" and namafile like '%" . $namafilescr . "%' ";}
		if ($tglscr != '') {$where.=" and createdtime like '%" . $tglscr . "%' ";}

		$limit = 15;
		$page = 0;
		$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
			$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		
		$arrsts=array(''=>'','0'=>'Aktif','1'=>'Non Aktif');
		
		$sql = "SELECT * FROM " . $dbname . ".listfile_umm_uploadpeta where 1=1 " . $where . "";
		$res = fetchData($sql);
		$jlhbrs = count($res);
		$no = 0;
		$str = "SELECT * FROM " . $dbname . ".listfile_umm_uploadpeta where 1=1 " . $where . " order by createdtime desc limit " . $offset . "," . $limit . ""; //exit("error $str");
		$tab = "";
		$no = $maxdisplay;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$isi = '';
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==1){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['createdby']."'");
				$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td>" .$bar['pt']."</td>";
				$tab.="<td>" . $bar['unit'] . " - " . @$nmorg[$bar['unit']] . "</td>";
				$tab.="<td>" . $nmpeta[$bar['tipepeta']] . "</td>";
				$tab.="<td>" . $bar['namapeta'] . "</td>";
				$tab.="<td align=center>" . $bar['revisi'] . "</td>";
				$tab.="<td>" . $bar['namafile'] . "</td>";
				$tab.="<td align=right>" . number_format($bar['filesize'] / 1024000 , 2) . "</td>";
				$tab.="<td>" . $arrsts[$bar['status']] . "</td>";
				$tab.="<td>" . $nmkary[$bar['createdby']] . "</td>";
				$tab.="<td>" . $bar['createdtime'] . "</td>";
				if($bar['status']==0){
					$isi.="<td align=center><img class=resicon src=images/Delete.png onclick=\"aktif('".$bar['id']."','".$bar['status']."');\" title='Non Aktifkan !'></td>";
				}else{
					$isi.="<td align=center><img class=resicon src=images/onebit_34.png onclick=\"aktif('".$bar['id']."','".$bar['status']."');\" title='Aktifkan !'></td>";
				}
				
				$isi.="<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"del('".$bar['id']."');\" title='Delete'></td>";
				$folder=$bar['pt']."/".$bar['unit']."/";
				
				$isi.="<td align=center><a href=\"".$path.$folder.$bar['namafile']."\" download><img src=images/uploader/dwnld8.png class=resicon title=download></a></td>";
				$tab.=$isi;
				$tab.="</tr>";
			}
		}
		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}
		$footd = "";
		$footd.="</tr>
				<tr><td colspan=14 align=center>";
		if ($page == '0') {
			$footd.="<button class=mybutton disabled=true>Prev</button>";
		} else {
			$footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
		}
		$footd.="<select id=\"pages\" name=\"pages\" onchange=\"getPage()\">" . $isiRow . "</select>";
		if (($page + 1) == $totrows) {
			$footd.="<button class=mybutton disabled=true>Next</button>";
		} else {
			$footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
		}
		$footd.="</td>
				</tr>";
		echo $tab . "####" . $footd;
	
	break;
	case'aktif':
		if($status==1){
			$status=0;
		}else{
			$status=1;
		}
		$str="update ".$dbname.".listfile_umm_uploadpeta set status='".$status."' where id='".$id."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case 'del':
	$sql="select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
	$resadmin =fetchData($sql);
	
	$str="select * from ".$dbname.".listfile_umm_uploadpeta where id='".$id."'";
	$res=fetchData($str);
	if($res[0]['createdby']!=$_SESSION['standard']['userid'] and count($resadmin)=='0'){
		exit("Warning : Anda tidak diizinkan menghapus file ini, silahkan hubungi pembuat atau admin anda !!!");
	}
	
	$str="delete from ".$dbname.".listfile_umm_uploadpeta where id='".$id."'";
	try{
		$owlPDO->exec($str);
		$folder=$res[0]['pt']."/".$res[0]['unit']."/";
		$pathx = $path.$folder.$res[0]['namafile'];
		unlink($pathx);
	}
	catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
	break;
	
	case 'getkebun3':
	if($status=='cari'){
		$optKebun = "<option value=''>".$_SESSION['lang']['all']."</option>";
	}else{
		$optKebun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	}
	$where = "";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where.="";
	}else{
		$where.=" and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
	}
	$str = "select * from ".$dbname.".organisasi where induk = '".$kodept."' and length(kodeorganisasi) = '4' ".$where."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar = $res->fetch()){
		$optKebun .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
	}
	
	echo $optKebun;
	break;
}
?>