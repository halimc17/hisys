<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$method      = checkPostGet('method', '');
$akun        = checkPostGet('akun', '');
$akun        = substr($akun,0,7);
$kegiatan    = checkPostGet('akun', '');
$kodeorg     = checkPostGet('kodeorg', '');
$periode     = checkPostGet('periode', '');
$tags        = checkPostGet('tags', '');
$bi          = checkPostGet('bi', '');
$id          = checkPostGet('id', '');
$real        = checkPostGet('real', '');
$arrbi       = explode('-',$periode); 
$tahun       = $arrbi[0];
$bulan       = $arrbi[1];
$namafile    = checkPostGet('namafile','');
$action      = checkPostGet('action','');
$path        = "fileupload/comment/";

if(!empty($_POST)){$param=$_POST;}else{$param=$_GET;}
$valid_ext   = array("pdf","doc","docx","jpg","png","jpeg","xls","xlsx","zip","rar");

$str = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
$res = fetchdata($str);
if(count($res)>0){
	$admin=true;
}else{
	$admin=false;
}


$str = "select * from ".$dbname.".bgt_regional_assignment";
$res = fetchdata($str);
foreach($res as $bar){
	$region[$bar['kodeunit']]=$bar['subregional'];
	$listregion[$bar['subregional']]=$bar['subregional'];	
}

$str = "select * from ".$dbname.".organisasi where tipe='PT'";
$res = fetchdata($str);
foreach($res as $bar){
	$listpt[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];	
}

$where="";
if($listregion[$kodeorg]!=''){	
	$regional = $kodeorg;
}else if($listpt[$kodeorg]!=''){	
	$pt = $kodeorg;
}else{	
	if(strlen($kodeorg)>=4){$unit = substr($kodeorg,0,4);}else{$unit = "";}
	if(strlen($kodeorg)>=6){$divisi = substr($kodeorg,0,6);}else{$divisi = "";}
	if(strlen($kodeorg)>6){$blok = $kodeorg;}else{$blok = "";}
	
	$regional = $region[$unit];
	$pt = getNamaOrg($unit,'induk');
	
	$where.=" and unit like '".$kodeorg."%'";
}

$x = strpos($param['menuid'],"')");
$param['menuid'] = substr($param['menuid'],14,($x-14));

// echo"<pre>";
// print_r($param);
// echo"</pre>";


switch($method){
	case'simpancomment':
		try {
			$owlPDO->beginTransaction();
			if($param['penjelasan']==''){
				throw new PDOException("Penjelasan tidak boleh kosong.");
			}
			
			if($action!='edit'){
				$str = "select max(id) as id from ".$dbname.".kebun_2commentreport";
				$res = fetchdata($str);
				if($res[0]['id']>0){
					$id=$res[0]['id']+1;
				}else{
					$id=1;
				}
				
				$data = array(
					'id'              => $id,
					'regional'        => $param['regional'],
					'pt'              => $param['kodept'],
					'unit'            => $param['unit'],
					'akun'            => $param['akun'],
					'kegiatan'        => $param['kegiatan'],
					'periode'         => $param['periode'],
					'bi'              => $param['bi'],
					'act'             => $param['real'],
					'comment'         => $param['penjelasan'],
					'sumber'          => $param['menuid'],
					'tags'            => $tags,
					'username'        => $_SESSION['standard']['username'],
					'createdby'       => $_SESSION['standard']['userid'],
					'updateby'        => $_SESSION['standard']['userid'],
					'createtime'      => date('Y-m-d H:i:s')
				);
				$str = insertQuery($dbname,'kebun_2commentreport',$data,array_keys($data));
				$owlPDO->exec($str);
				
			}else{
				$data = array(
					'regional'        => $param['regional'],
					'pt'              => $param['kodept'],
					'unit'            => $param['unit'],
					'akun'            => $param['akun'],
					'kegiatan'        => $param['kegiatan'],
					'periode'         => $param['periode'],
					'bi'              => $param['bi'],
					'tags'            => $tags,
					'act'             => $param['real'],
					'comment'         => $param['penjelasan'],
					'sumber'          => $param['menuid'],
					'updateby'        => $_SESSION['standard']['userid']
				);
				
				$where = "id='".$id."'";
				$str = updateQuery($dbname,'kebun_2commentreport',$data,$where);
				$owlPDO->exec($str);
			}
			
			$countfiles = @count($_FILES['file']['name']);
			if($countfiles>10){
				throw new PDOException("Jumlah maksimal hanya 10 file.");
			}
			if($countfiles>0){
				for($i=0;$i < $countfiles;$i++){
					$filesize+=$_FILES['file']['size'][$i];
				}
				if($filesize>250000000){
					throw new PDOException("File size terlalu besar (".formatBytes($filesize).").");
				}
				
				if (!file_exists($path)) {
					mkdir($path, 0777, true);
				}	
				$tempfile="";
				for($i=0;$i < $countfiles;$i++){
					$file_tmpname= file_get_contents($_FILES['file']['tmp_name'][$i]);
					$filename    = $_FILES['file']['name'][$i];
					
					$file_extension = pathinfo($path.$filename, PATHINFO_EXTENSION);
					$file_extension = strtolower($file_extension);
					
					if($_FILES['file']['error'][$i]==0){
						$filename = $id."_".$filename;
						$tempfile.=$filename."|";
						
						if(file_exists($path.$filename)) {
							unlink($path.$filename);
						}
						if(in_array($file_extension,$valid_ext)){
							file_put_contents($path.$filename,$file_tmpname);
						}else{
							throw new PDOException("File tidak diizinkan.");
						}
					}
				}
				
				$data = array(
					'file'    => $tempfile
				);
				$where = "id='".$id."'";
				$str = updateQuery($dbname,'kebun_2commentreport',$data,$where);
				$owlPDO->exec($str);
			}
			
			if($tags!='' and $tags!='null'){
				
				if(getNamaKeg($param['kegiatan'])!=''){				
					$info = getNamaKeg($param['kegiatan']);
				}else{
					$info = getNamaAkun($param['akun']);
				}
			
				$textx="<html>
					<head>
						<body>
							Dengan Hormat,
							<br>
							<br>
							".getKary($_SESSION['standard']['userid'])." telah menyebut nama anda sebagai berikut :
							<br>
							<br>
							<b>Path Menu :</b> ".getNamaMenu2($param['menuid'])."<br>
							<b>Unit/Div/Blok :</b> ".getNamaOrg($param['unit'])."<br>
							<b>Akun/Kegiatan :</b> ".$info."<br>
							<b>Isi comment :</b> ".$param['penjelasan']."<br>
							Demikian disampaikan, terima kasih.<br>
							Salam,
							
							<br><br><br>
							<i>Pesan ini dikirim otomatis, oleh https://owl.ksp-agro.com</i>
						</body>
					</head>
			   </html>
				";
				
				// $arrtags = explode(",",$tags);
				// foreach($arrtags as $karid){
					// $to = getUserEmail($karid);
					// $subjectx="[Notifikasi]Comment Report ".getNamaMenu2($param['menuid'])."";
					// if(isset($to)){
						// $kirim = kirimEmail($to, '', $subjectx, $textx);
					// }
				// }
			}
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
	
	break;
	case'showcomment':
		$admin=false;
		$query = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($query);
		if(!empty($res)){			
			$admin=true;
		}
		
		if($regional!=''){
			$whreg="and subregional='".$regional."'";
		}
		
		$str = "select * from ".$dbname.".bgt_regional_assignment where 1=1 ".$whreg."";
		$res = fetchdata($str);
		foreach($res as $bar){
			$listkebun[$bar['kodeunit']]=$bar['kodeunit'];	
		}
		if($pt!=''){	
			$listkebun=[];
			$str = "select * from ".$dbname.".organisasi where induk='".$pt."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$listkebun[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];	
			}
		}

		$where.=" and substr(unit,1,4) in ('".implode("','",$listkebun)."')";
		
		if($unit!=''){			
			$where.=" and substr(unit,1,4) ='".$unit."'";
		}
		
		
		$tab="";
		$listticket=false;
		if($bi=='bi'){
			$str = "select * from ".$dbname.".kebun_2commentreport where 1=1 ".$where." and akun like '".$akun."%' and kegiatan like '".$kegiatan."%' and periode = '".$periode."' and periode like '".$tahun."%' and act='".$real."' and bi='".$bi."' order by id desc";
		}else{			
			$str = "select * from ".$dbname.".kebun_2commentreport where 1=1 ".$where." and akun like '".$akun."%' and kegiatan like '".$kegiatan."%' and periode <= '".$periode."' and periode like '".$tahun."%' and act='".$real."' order by id desc";
		}
		// echo $str;
		$res = fetchdata($str);
		if(count($res)>0){			
			foreach($res as $bar){
				$no++;
				$i=$no%2;
				$tomboldel="";
				if($bar['username']==$_SESSION['standard']['username'] and $bar['createtime']==date('Y-m-d')){
					$tomboldel="<td align=right nowrap width=20px><img src='images/application/application_delete.png' style=\"height:10px;width:10px;vertical-align:center;cursor:pointer;\" title='Delete' onclick=delcomment('".$bar['id']."','".$no."')></td>";
				}
				if($admin==true){
					$tomboldel="<td align=right nowrap width=20px><img src='images/application/application_delete.png' style=\"height:10px;width:10px;vertical-align:center;cursor:pointer;\" title='Delete' onclick=delcomment('".$bar['id']."','".$no."')></td>";
				}
				
				
				if($i==0){
					$tab.="<fieldset style=background-color:#C7E5F7; id=field".$no.">";
					$tab.="<legend style=background-color:#AFDDFA;height:10px><table border=0><td><i>Penjelasan Oleh: ".getKary($bar['createdby'])." (".getNamaJabatan(getKary($bar['createdby'],'kodejabatan')).") at: ".$bar['createtime']."</i></td>".$tomboldel."</table></legend>";
					// $tab.="<table style=background-color:#AFDDFA;border-radius:3px; cellpadding=5px>";
				}else{
					$tab.="<fieldset style=background-color:#BFDBED; id=field".$no.">";
					$tab.="<legend style=background-color:#87CEFA;height:10px><table border=0><td><i>Penjelasan Oleh: ".getKary($bar['createdby'])." (".getNamaJabatan(getKary($bar['createdby'],'kodejabatan')).") at: ".$bar['createtime']."</i></td>".$tomboldel."</table></legend>";
				}
				
				$tab.="<table style=border-radius:3px; cellpadding=5px>";
				$tab.="<tr><td width=100px>Nama Akun</td><td width=1px>:</td><td>".$bar['akun']." - ".getNamaAkun($bar['akun'])."</td></tr>";
				if(getNamaKeg($bar['kegiatan'])!=''){
					$tab.="<tr><td>Kegiatan</td><td>:</td><td>".$bar['kegiatan']." - ".getNamaKeg($bar['kegiatan'])."</td></tr>";
				}
				$tab.="<tr><td>Unit/Div/Blok</td><td>:</td><td><b><i>".$bar['unit']." - ".getNamaOrg($bar['unit'])."</i></b></td></tr>";
				
				
				if($bar['bi']=='bi' and $bar['act']=='real'){
					$p="(Actual Bulan Ini)";
				}if($bar['bi']=='sdbi' and $bar['act']=='real'){
					$p="(Actual Sampai Dengan Bulan Ini.)";
				}
				if($bar['bi']=='bi' and $bar['act']=='bgt'){
					$p="(Budget Bulan Ini.)";
				}
				if($bar['bi']=='sdbi' and $bar['act']=='bgt'){
					$p="(Budget Sampai Dengan Bulan Ini.)";
				}
				if($bar['bi']=='thn' and $bar['act']=='bgt'){
					$p="(Budget Setahun.)";
				}
				
				$tab.="<tr><td>Periode</td><td>:</td><td>".$bar['periode']." ".$p."</td></tr>";
				$tab.="<tr><td>Path Menu</td><td>:</td><td><span onclick=jump(".$bar['sumber'].",event) style=cursor:pointer;color:blue; title='Click untuk membukan menu ini.'>".getNamaMenu2($bar['sumber'])."</spab></td></tr>";
				
				$tab.="<tr><td></td><td></td><td>";
				if($bar['tags']!='' and $bar['tags']!='null'){
					$exptag = explode(",",$bar['tags']);
					$title="";
					foreach($exptag as $tag){
						if(getKary($tag)!=''){							
							$title="title='Jabatan: ".getNamaJabatan(getKary($tag,'kodejabatan'))."";
							$title.="\nLokasi tugas: ".getNamaOrg(getKary($tag,'lokasitugas'))."'";
							$tab.="<label ".$title." title style=padding-right:15px><i>@".getKary($tag)."</i></label>";
						}
					}
				}
				
				$tab.="</td></tr>";
				$tab.="<tr style=font-weight:bold><td>Penjelasan</td><td>:</td><td></td></tr>";
				// $tab.="<br><br><label><b><i>Penjelasan :</i></b></label>";
				$tab.="<tr><td style=padding-left:15px colspan=3>".nl2br($bar['comment'])."</td></tr>";
				
				// $tab.="<br><br>".nl2br($bar['comment']);
				$tab.="</table>";
				
				if($bar['file']!= ''){
					$fileT = explode("|",$bar['file']);
					$ada=false;
					$urut=$bar['id'].'0';
					foreach($fileT as $filename){
						if($filename!=''){						
							$filetype = strtolower(substr($filename,strripos($filename,'.')+1));
							if(($filetype=='jpeg')||($filetype=='jpg')||($filetype=='png')||($filetype=='pdf')){
								$urut++;
								$urut=$bar['id'].$urut;
								if(($filetype=='pdf')){	
									$tab.="<div>
											<div style='margin: 5px 20px 20px;'>
												<div class='smallfont' style='margin-bottom: 2px;'>
													<i><span style='font-weight: bold;'>".$filename."</span></i>
														<input class=mybutton value='Show' onclick=showhidespoiler(this); type='button'/> 
												</div> 
												<div class='alt2' style='border: 1px inset ; margin: 0px; padding: 6px;'> 
													<div style='display: none;'>
														<object data=".$path.$filename." type='application/pdf' width='100%' height='500px'>
															<p>Unable to display PDF file.<a href='".$path.$filename."' download target=blank>Download</a> instead.</p>
														</object>
													</div>
												</div>
											</div>
										</div>";
								}else{
									$tab.="<div>
											<div style='margin: 5px 20px 20px;'>
												<div class='smallfont' style='margin-bottom: 2px;'>
													<i><span style='font-weight: bold;'>".$filename."</span></i>
														<input class=mybutton value='Show' onclick=showhidespoiler(this); type='button'/> 
												</div> 
												<div class='alt2' style='border: 1px inset ; margin: 0px; padding: 6px;'> 
													<div style='display: none;'>
														<img src='".$path.$filename."' style=width:100%>
														<p><a href='".$path.$filename."' download target=blank>Download</a></p>
													</div>
												</div>
											</div>
										</div>";
								}
							}else{
								$ada=true;
							}
						}
					}
					if($ada==true){						
						$tab.="<br><br>Attachment :<br>";
					}
					$tab.="<ol>";
					foreach($fileT as $filename){
						if($filename!=''){						
							$filetype = strtolower(substr($filename,strripos($filename,'.')+1));
							if(($filetype=='jpeg')||($filetype=='jpg')||($filetype=='png')||($filetype=='pdf')){
							}else{
								$tab.="<li><a href='".$path.$filename."' download target=blank><span style='color:blue;cursor:pointer;' title=\"Klik untuk download\">".$filename."</span></a></li>";
							}
						}
					}
					$tab.="</ol>";				
				}
				
				$tab.="<br><br></fieldset>";
			}
		}else{
			$tab.=$_SESSION['lang']['datanotfound'];
		}
	
		echo $tab;
		
	break;
	case'delcomment':
		$str = "select * from ".$dbname.".kebun_2commentreport where id='".$id."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$tempfile=$bar['file'];
		}
		
		$fileT = explode("|",$tempfile);
		foreach($fileT as $filename){
			if($filename!=''){				
				$file = $path.$filename;
				if(file_exists($file)){
					unlink($file);
				}
			}
		}
		
		$str="delete from ".$dbname.".kebun_2commentreport where id='".$id."'";
		try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
	break;
	case'addcomment':
		$optuser="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".user where 1=1 and status='1' order by namauser asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($userinput==$bar['namauser']){
				$optuser.="<option value='".$bar['namauser']."' selected>".getKary($bar['karyawanid'])." (".$bar['namauser'].")</option>";
			}else{							
				$optuser.="<option value='".$bar['namauser']."'>".getKary($bar['karyawanid'])." (".$bar['namauser'].")</option>";
			}
		}
		
		echo"<div style=display:none>
				<input id=region value=".$regional.">
				<input id=kodept value=".$pt.">
				<input id=unit value=".$kodeorg.">
				<input id=periode value=".$periode.">
				<input id=bi value=".$bi.">
				<input id=real value=".$real.">
				<input id=id value=".$id.">
				<input id=akun value=".$akun.">
				<input id=kegiatan value=".$kegiatan.">
				<input id=action>
			</div>
			<table border=0 style=width:100%>";
				echo"<tr><td align=center>";
				echo"<table border=0 cellpadding=1 cellspacing=1 style=width:90%>";
				echo"<tr><td colspan=3>";
					echo"<table border=0 cellpadding=5 cellspacing=1>";
						echo"<tr>";
							// echo"<td>".$_SESSION['lang']['regional']."</td>
								// <td>:</td>
								// <td>".$regional."</td>
								if(getNamaOrg($unit)==''){									
									echo"<td>".$_SESSION['lang']['pt']."</td>
										<td>:</td>
										<td>".getNamaOrg($pt)."</td>";
								}else{									
									echo"
										<td>".$_SESSION['lang']['kodeorg']."</td>
										<td>:</td>
										<td>".$unit." - ".getNamaOrg($unit)."</td>";
								}
								
								
								if($divisi!=''){
									echo"<td width=50px></td>
									<td>".$_SESSION['lang']['divisi']."</td>
									<td>:</td>
									<td>".getNamaOrg($divisi)."</td>";
								}
								if($blok!=''){
									echo"<td width=50px></td>
									<td>".$_SESSION['lang']['blok']."</td>
									<td>:</td>
									<td>".getNamaOrg($blok)."</td>";
								}
						echo"</tr>";
						echo"<tr>
								<td>".$_SESSION['lang']['noakun']."</td>
								<td>:</td>
								<td>".$akun." - ".getNamaAkun($akun)."</td>";
							if(getNamaKeg($kegiatan)!=''){
								echo"<td></td>
								<td>".$_SESSION['lang']['kegiatan']."</td>
								<td>:</td>
								<td colspan=5>".getNamaKeg($kegiatan)."</td>
								";
							}	
						echo"</tr>";
						echo"</table>";			
				
				echo"</td></tr>";
				echo"<tr><td colspan=3></td></tr>";
				
				$optper="<option value=''>&nbsp;</option>";
				$whereKary= " and tipekaryawan in (0) and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date('Y-m-d')."')";
				$str = "select karyawanid,nik, namakaryawan, kodejabatan from ".$dbname.".datakaryawan where 1=1 ".$whereKary." order by kodejabatan, namakaryawan asc";
				$res = fetchdata($str);
				foreach($res as $bar){
					$d=$bar['kodejabatan'];
					if($d!=$n){			
						$optper.="<optgroup label='".getNamaJabatan($d)."'>";
					}
					$optper.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']."</option>";
					$n=$d;
					if($d!=$n){			
						$optper.="</optgroup>";
					}
				}
				
				echo"<tr>
						<td nowrap style=padding-left:8px; colspan=6><b>Penjelasan :</b></td>
					</tr>
					<tr>
						<td colspan=6><textarea rows=23 placeholder=required id=penjelasancomment name=penjelasancomment[] type='text' onkeypress='return tanpa_kutip(event)' style='width:98%;'>".$penjelasan."</textarea></td>
					</tr>
					<tr>
						<td colspan=3 >Attachment : <i>(max 10 files)</i></td>
					</tr>
					<tr style=vertical-align:top>
						<td nowrap>File : <input id=filescomment name=filescomment[] type=file multiple>
						</td>
						<td style=width:350px hidden>
							<i>@ <select style=width:300px id=mentionuser multiple class='select2 help'>".$optper."</select> <img src=images/application/application_delete.png style=\"height:10px;width:10px;vertical-align:center;cursor:pointer;\" title='Hapus Data' onclick=\"deletemention()\"></i>
						
						</td>
					</tr>
					<tr>
						<td align=center colspan=3>
							<button onclick=simpancomment('".$param['action']."'); style=width:200px;height:30px class=mybutton name=preview id=preview>Simpan</button>
						</td>
					</tr>
				</table>
			</td></tr></table>
		";
	
	break;
	
}
function getNamaMenu2($idmenu,$tipe=''){
	include('lib/zLib.php');
	global $dbname;
	global $owlPDO;
	
	$menu=[];
	$str="SELECT f.* FROM (SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)FROM (SELECT @id := ".$idmenu." ) tmp1 JOIN ".$dbname.".menu ON @id <> 0) tmp2 JOIN ".$dbname.".menu f ON tmp2._id = f.Id order by action,parent";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['parent']==0){
			$modul  =$bar['caption'];
			$modulid=$bar['id'];
		}
		$menu[$bar['caption']]=strtoupper(strtolower($bar['caption']));
	}
	if($tipe=='modul'){
		return $modul;
	}else{		
		return implode(" - ",$menu);
	}
}
?>