<?php
//ini_set('display_errors',0);
//error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
$pt = checkPostGet('pt', '');
$unit= checkPostGet('unit', '');
$notransaksi= checkPostGet('notransaksi', '');
$lokasipengadilan= checkPostGet('lokasipengadilan', '');
$jenispengadilan= checkPostGet('jenispengadilan', '');
$penggugat= checkPostGet('penggugat', '');
$tergugat= checkPostGet('tergugat', '');	
$jenisperkara= checkPostGet('jenisperkara', '');
$advokat= checkPostGet('advokat', '');
$tanggalterdaftar= tanggalsystem(checkPostGet('tanggalterdaftar', ''));
$ringkasan= trim(checkPostGet('ringkasan', ''));
$tanggalputusan= tanggalsystem(checkPostGet('tanggalputusan', ''));
$amarputusan= trim(checkPostGet('amarputusan', ''));

$namafile = checkPostGet('namafile', '');
$tipe = checkPostGet('tipe', '');
$divsch = checkPostGet('divsch', '');
$jenissch= checkPostGet('jenissch', '');
$nohaksch= checkPostGet('nohaksch', '');
$unitsch= checkPostGet('unitsch', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$amarputusan=nl2br($amarputusan);
$xxx=explode('<br />',$amarputusan);
$no='';
foreach($xxx as $yyy => $iii){
	$no+=1;
	if($no < count($xxx)){
		@$asdf.=trim($iii)."####";
	}else{
		@$asdf.=trim($iii);
	}
}

$ringkasan=nl2br($ringkasan);
$eee=explode('<br />',$ringkasan);
$no='';
foreach($eee as $ggg => $hhh){
	$no+=1;
	if($no < count($eee)){
		@$ghjkl.=trim($hhh)."####";
	}else{
		@$ghjkl.=trim($hhh);
	}
}
$ringkasan = $ghjkl;
$amarputusan =  $asdf;

$path	= "fileupload/lgl_litigasi/";
$today=date('Y-m-d');
$todayhis=date('Y-m-d h:i:s');

switch ($method) {
	case'getunit':
		$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4 order by namaorganisasi asc "; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
	echo $optun;
	break;
    case'html':
		$tab= "<img src=images/excel.jpg class=resicon  title='Excel' onclick=\"viewexcel('".$pt."','".$unit."','".$notransaksi."','excel');\">";
		
		$no = 0;
        $str = "select * from " . $dbname . ".lgl_litigasi where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if($tipe=='html'){
			$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
		} else{
			$tab.= "<table cellpadding=1 cellspacing=1 border=1>";
		}
		$tab.="<tr class=rowcontent>
				<td>" . $_SESSION['lang']['pt'] . "</td> 
				<td>:</td>
				<td colspan=4>".$pt." ".$nmorg[$pt]."</td>
				
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td colspan=4>".$unit." ".$nmorg[$unit]."</td>
			</tr>
			<tr class=rowcontent>
				<td>Nomor Perkara</td> 
				<td>:</td>
				<td colspan=4>".$bar['notransaksi']."</td>
				
				<td>Jenis Perkara</td> 
				<td>:</td>
				<td colspan=4>".$bar['jenisperkara']."</td>
				
			</tr>
			<tr class=rowcontent>
				<td>Nama Penggugat</td> 
				<td>:</td>
				<td colspan=4>".$bar['penggugat']."</td>
				
				<td>Nama Tergugat</td> 
				<td>:</td>
				<td colspan=4>".$bar['tergugat']."</td>
			</tr>
			<tr class=rowcontent>
				<td>Jenis Pengadilan</td> 
				<td>:</td>
				<td colspan=4>".$bar['jenispengadilan']."</td>
				
				<td>Lokasi Pengadilan</td> 
				<td>:</td>
				<td colspan=4>".$bar['lokasipengadilan']."</td>
			</tr>
			<tr class=rowcontent>
				<td>Advokat</td> 
				<td>:</td>
				<td colspan=4>".$bar['advokat']."</td>
				
				<td>Tanggal Daftar Perkara</td> 
				<td>:</td>
				<td style='width:75px;'>".$bar['tanggalterdaftar']."</td>

				<td>Tanggal Putusan</td> 
				<td>:</td>
				<td style='width:100px;'>".$bar['tanggalputusan']."</td>
			</tr>
			<tr class=rowcontent>
				<td valign=top>Ringkasan Kasus</td> 
				<td valign=top>:</td>
				<td colspan=10>".str_replace('####','<br />',$bar['ringkasan'])."</td>
			</tr>
			<tr class=rowcontent>
				<td valign=top>Amar Putusan</td> 
				<td valign=top>:</td>
				<td colspan=10>".str_replace('####','<br />',$bar['amarputusan'])."</td>
			</tr>
		</table>";
		if($tipe=='html'){
			echo $tab;
			echo @$isi.="<hr><table class='sortable' cellspacing='1' border='0' style=min-width:100%>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>";
			
		} else {
			$stream = $tab;
			$nop_ = "litigasi";
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
								parent.window.alert('Cant convert to excel format');
								</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
				}
				closedir($handle);
			}
		}
	break;

	case'viewlistfile':
		$tab.="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' border='0' style=min-width:350px>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>
			</fieldset> ";
		echo $tab;
	break;
		
    case'insert':
		#cek data
        $sql = "select * from " . $dbname . ".lgl_litigasi where kodept='" . $pt . "' and unit='" . $unit . "' and notransaksi='" . $notransaksi . "'";
		$res=fetchData($sql);
		if(count($res)>0){
			exit('Warning : Data sudah ada !');
		}

		# Jika data sudah ada maka langsung Insert
        $str = "insert into " . $dbname . ".lgl_litigasi (`notransaksi`,`kodept`,`unit`,`lokasipengadilan`,`jenispengadilan`,`penggugat`,`tergugat`,`jenisperkara`,`advokat`,`tanggalterdaftar`,`ringkasan`,`tanggalputusan`,`amarputusan`,`createby`,`createtime`,`updateby`)
        values ('".$notransaksi."','".$pt."','".$unit."','".$lokasipengadilan."','".$jenispengadilan."','".$penggugat."','".$tergugat."','".$jenisperkara."','".$advokat."','".$tanggalterdaftar."','".$ringkasan."','".$tanggalputusan."','".$amarputusan."','".$_SESSION['standard']['userid'] . "','".$todayhis."','".$_SESSION['standard']['userid'] . "')";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
	case'update':
        $str = "update " . $dbname . ".lgl_litigasi set lokasipengadilan='".$lokasipengadilan."',jenispengadilan='".$jenispengadilan."',penggugat='".$penggugat."',tergugat='".$tergugat."',jenisperkara='".$jenisperkara."',advokat='".$advokat."',tanggalterdaftar='".$tanggalterdaftar."',ringkasan='".$ringkasan."',tanggalputusan='".$tanggalputusan."',amarputusan='".$amarputusan."',updateby='" . $_SESSION['standard']['userid'] . "',updatetime='".$todayhis."'
		where kodept='".$pt."' and unit='".$unit."' and notransaksi='".$notransaksi."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
	case'delete':
		$str = "delete from " . $dbname . ".lgl_litigasi where kodept='".$pt."' and notransaksi='".$notransaksi."' and unit='".$unit."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		# delete file
		$sql = "select * from " . $dbname . ".listfile_lgl_litigasi where notransaksi='".$notransaksi."'"; 
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$str="delete from ".$dbname.".listfile_lgl_litigasi where notransaksi='".$notransaksi."'and namafile='".$bar['namafile']."'";
			try{$owlPDO->exec($str);
				$pathx = $path.$bar['namafile'];
				unlink($pathx);
			}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
	break;

    case'loaddata':
        $where = "";
        if ($divsch != '') {
            $where.=" and kodept='" . $divsch . "' ";
        }
		if ($jenissch != '') {
            $where.=" and jenispengadilan ='" . $jenissch . "' ";
        }
		if ($unitsch != '') {
            $where.=" and unit='" . $unitsch . "' ";
        }
		if ($nohaksch != '') {
            $where.=" and notransaksi like '%" . $nohaksch . "%' ";
        }
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

		$sql = "SELECT * FROM " . $dbname . ".lgl_litigasi where 1=1 " . $where . " order by kodept asc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;
		
        $str = "SELECT * FROM " . $dbname . ".lgl_litigasi  where 1=1 " . $where . " order by updatetime desc limit " . $offset . "," . $limit . "";
		$tab = "";
        $no = $maxdisplay;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
        $res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=15 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$isi = '';
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==1){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td>" . $bar['kodept'] . " - " . $nmorg[$bar['kodept']] . "</td>";
				$tab.="<td>" . $bar['unit'] . " - " . $nmorg[$bar['unit']] . "</td>";
				$tab.="<td>" . $bar['notransaksi'] . "</td>";
				$tab.="<td>" . $bar['lokasipengadilan'] . "</td>";
				$tab.="<td>" . $bar['jenispengadilan'] . "</td>";
				$tab.="<td>" . $bar['penggugat'] . "</td>";
				$tab.="<td>" . $bar['tergugat'] . "</td>";
				$tab.="<td>" . $bar['jenisperkara'] . "</td>";
				$tab.="<td>" . $bar['advokat'] . "</td>";
				$tab.="<td>" . $bar['tanggalterdaftar'] . "</td>";
				$tab.="<td align=left>" . $nmkar[$bar['updateby']] . "</td>";

				$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
					onclick=\"edit('".$bar['notransaksi']."','".$bar['kodept']."','".$bar['unit']."','".$bar['lokasipengadilan']."','".$bar['jenispengadilan']."','".$bar['penggugat']."','".$bar['tergugat']."','".$bar['jenisperkara']."','".$bar['advokat']."','".tanggalnormal($bar['tanggalterdaftar'])."','".str_replace('####','\n',$bar['ringkasan'])."','".tanggalnormal($bar['tanggalputusan'])."','".str_replace('####','\n',$bar['amarputusan'])."');\" ></td>";
				
				$isi.="<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"del('".$bar['kodept']."','".$bar['unit']."','".$bar['notransaksi']."');\" title='Delete'></td>";
				$isi.="<td align=center><img src=images/zoom.png class=resicon  title='View' onclick=\"html('".$bar['kodept']."','".$bar['unit']."','".$bar['notransaksi']."','html');\"></td>";
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
                     <tr><td colspan=15 align=center>";

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
	
	case 'submitfile':
		if($pt=='' or $unit=='' or $notransaksi==''){
			exit("Warning : Silahkan isikan detail transaksi terlebih dahulu !");
		}
		
		#cek data
        $sql = "select * from " . $dbname . ".lgl_litigasi where kodept='" . $pt . "' and unit='" . $unit . "' and notransaksi='" . $notransaksi . "'";
		$res=fetchData($sql);
		if(count($res)==0){
			exit('Warning : Silahkan isikan dan save detail transaksi terlebih dahulu !');
		}
		
		$str="select * from ".$dbname.".listfile_lgl_litigasi where notransaksi = '".$notransaksi."'";
		//exit('error'.$str);
		$res=fetchData($str);
		if(count($res)>=10){
			exit("Warning : Limit upload hanya 10 file.");
		}
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){	
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $pt."_".$tgl."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 250000){
						$str = "insert into ".$dbname.".listfile_lgl_litigasi values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
		$str="select * from ".$dbname.".listfile_lgl_litigasi where notransaksi = '".$notransaksi."' and status='1'";
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
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}elseif($val['formaticon']=='.png'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}elseif($val['formaticon']=='.pdf'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				}elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}else{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}
				
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
					<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				
				$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
				
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
		$str="delete from ".$dbname.".listfile_lgl_litigasi where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
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