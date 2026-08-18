<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}

$method        = checkPostGet('method','');
$tipe          = checkPostGet('tipe','');
$unit          = checkPostGet('unit','');
$divisi        = checkPostGet('divisi','');
$fp            = checkPostGet('fp','');
$fppin         = checkPostGet('fppin','');
$nik           = checkPostGet('nik','');
$karyawanid    = checkPostGet('karyawanid','');
$nourut        = checkPostGet('nourut','');
$dttipe        = checkPostGet('dttipe','');
$dtmesin       = checkPostGet('dtmesin','');
$dtpin         = checkPostGet('dtpin','');
$dtnamakaryawan= checkPostGet('dtnamakaryawan','');

switch($method){
	case'simpanchangesn':
	try {
		$owlPDO->beginTransaction();
		
		$data="";
		$str = "select * from ".$dbname.".att_pegawai where sn='".$param['snlama']."'";
		$res = fetchdata($str);
		foreach($res as $val){
			$sql = "select * from ".$dbname.".att_pegawai where sn='".$param['snbaru']."' and pin='".$val['pin']."'";
			$req = fetchdata($sql);
			foreach($req as $vaq){
				$str="delete from ".$dbname.".att_pegawai where sn='".$param['snbaru']."' and pin='".$val['pin']."'";
				$owlPDO->exec($str);
				
				$no++;
				$data.=$no.". ".$vaq['pin']." - ".$vaq['namafp']."<br>";
			}
		}
		
		if($data!=''){
			#throw new PDOException("Data sudah ada di SN yang baru :<br>".$data);
		}

		$str = "update ".$dbname.".att_pegawai set sn='".$param['snbaru']."' where sn='".$param['snlama']."'";
		$owlPDO->exec($str);
				
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}

	break;
	case'changesnfp':
		$str="select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res=fetchdata($str);
		if(count($res)==0){
			exit("Error : Diperlukan akses administrator untuk melakukan ini.");
		}
	
		$optdtmesin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct sn, device_name from ".$dbname.".att_device order by device_name asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optdtmesin.="<option value='".$val['sn']."'>".$val['device_name']." - ".$val['sn']."</option>";			
		}
		
		$tab="<table cellpadding=1>
				<tr>
					<td>SN Lama</td>
					<td>:</td>
					<td><select class=select2 id='snlama' style=width:300px;>".$optdtmesin."</select></td>
				</tr>
				<tr>
					<td>SN Baru</td>
					<td>:</td>
					<td><select class=select2 id='snbaru' style=width:300px;>".$optdtmesin."</select></td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=\"simpanchangesn(event,'html')\">".$_SESSION['lang']['save']."</button>
					</td>
				</tr>
			</table>";
		echo $tab;	
	break;
	case'getdivisi':
		$optfppin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct(subbagian) as subbagian from ".$dbname.".datakaryawan where lokasitugas='".$unit."' order by subbagian asc";
		// $str="select namaorganisasi,kodeorganisasi,tipe from ".$dbname.".organisasi where tipe NOT IN ('KEBUN','BLOK') and kodeorganisasi LIKE '%".$unit."%' order by namaorganisasi asc";
		$res=fetchdata($str);
		$optfppin="<option value=''>UMUM - DIVISI UMUM</option>";
		foreach($res as $val){
			if($val['subbagian']==''){
				$optfppin.="<option value='".$val['subbagian']."'>UMUM - DIVISI UMUM</option>";
			}else{				
				$optfppin.="<option value='".$val['subbagian']."'>".$val['subbagian']." - ".getNamaOrg($val['subbagian'])."</option>";
			}
			// $optfppin.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optfppin;
	break;
	case'preview':
		if($unit==''){
			exit("Warning : Lokasi tugas harus diisi");
		}
		
		$arrkary=array();
		
		$where=" and subbagian='".$divisi."'";
		
		## GET DATA KARYAWAN
		$str="select karyawanid,nik,namakaryawan, subbagian from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." order by namakaryawan asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrkary[$val['karyawanid']]['karyawanid']=$val['karyawanid'];
			$arrkary[$val['karyawanid']]['nik']=$val['nik'];
			$arrkary[$val['karyawanid']]['namakaryawan']=$val['namakaryawan'];
			$arrkary[$val['karyawanid']]['divisi']=$val['subbagian'];
		}
		
		## LIST DEVICE FP
		$arrdevfp=array();
		$optfp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optfppin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct(sn) as sn from ".$dbname.".att_pegawai where nik=''";
		$res=fetchdata($str);
		foreach($res as $val){
			$optnmfp = makeOption($dbname,'att_device','sn,device_name',"sn='".$val['sn']."'");
			$arrfp[$val['sn']]=$optnmfp[$val['sn']];
		}
		asort($arrfp);
		foreach($arrfp as $key=>$val){
			$optfp.="<option value='".$key."'>".$val."</option>";
		}
		
		if($tipe=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}
		
		$tab="<table cellpadding=3 cellspacing=1 ".$border." class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold;height:25px'>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['nik2']."</th>
				<th>".$_SESSION['lang']['namakaryawan']."</th>
				<th>".$_SESSION['lang']['divisi']."</th>
				<th>Fingerprint</th>
			</tr>
			</thead>
			<tbody>";
		
		$no=0;
		foreach($arrkary as $val){
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td align='center'>".$no."</td>";
			$tab.="<td align='left' id='nik_".$no."'>".$val['nik']."</td>";
			$tab.="<td align='left'>".$val['namakaryawan']."</td>";
			$tab.="<td align='left'>".getNamaOrg($val['divisi'])."</td>";
			$tab.="<td align='left'>
				<input type='hidden' id='karyawanid_".$no."' value='".$val['karyawanid']."'>
				<table>
					<tr>
						<td><div id='listfp_".$no."'>";
						$str="select * from ".$dbname.".att_pegawai where karyawan='".$val['karyawanid']."'";
						$res=fetchdata($str);
						if(count($res) > 0){
							foreach($res as $valx){
								$tab.="<div class='choosed noselect' onclick=\"deletefp('".$no."','".$valx['sn']."','".$valx['pin']."')\">".$valx['pin']." - ".$valx['namafp']." (".$valx['sn'].")</div>";
							}
						}
						$tab.="</div></td>
					</tr>";
					if($tipe!='excel'){						
						$tab.="<tr>
							<td>
								<table>
									<tr>
										<td>
											<select class=select id='fp_".$no."' onchange=\"getpin('".$no."')\">".$optfp."</select>
											<img id='imgfp_".$no."' onclick=z.elSearch('fp_".$no."',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
										</td>
										<td style='padding-left:15px'>
											<div id='divfp_".$no."' style='display:none''>
												<select class=select id='fppin_".$no."' onchange=\"insattpeg('".$no."')\">".$optfppin."</select>
												<img id='imgfppin_".$no."' onclick=z.elSearch('fppin_".$no."',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>";
					}
				$tab.="</table>
			</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody>
			<tr hidden>
				<td><input type='hidden' id='totalkar' value='".$no."'></td>
			</tr>
		</table>";
		
		if($tipe=='pdf'){			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("to", array("Attachment" => false));
		}elseif($tipe=='excel'){
			$nop_="Mapping_FP_".$unit;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$tab)){
					echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				}else{
					echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				}
				fclose($handle);
			}
		}else{
			echo $tab;
		}		
	break;
	
	case'getpin':
		$optfppin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".att_pegawai where nik='' and sn='".$fp."'";
		$res=fetchdata($str);
		if(count($res) > 0 && $fp!=''){
			foreach($res as $val){
				$optfppin.="<option value='".$val['pin']."'>".$val['pin']." - ".$val['namafp']."</option>";
			}
		}else{
			$optfppin="";
		}
		
		echo $optfppin;
	break;
	
	case'insattpeg':
		$str="update ".$dbname.".att_pegawai set nik='".$nik."',karyawan='".$karyawanid."' where sn='".$fp."' and pin='".$fppin."'";
		$owlPDO->exec($str);
	break;
	
	case'deletefp':
		$str="update ".$dbname.".att_pegawai set nik='',karyawan='' where sn='".$fp."' and pin='".$fppin."'";
		$owlPDO->exec($str);
	break;
	
	case'loadattpeg':
		$tab="";
		$str="select * from ".$dbname.".att_pegawai where karyawan='".$karyawanid."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			foreach($res as $valx){
				$tab.="<div class='choosed noselect' onclick=\"deletefp('".$nourut."','".$valx['sn']."','".$valx['pin']."')\">".$valx['pin']." - ".$valx['namafp']." (".$valx['sn'].")</div>";
			}
		}
		
		echo $tab;
	break;
	
	case'adddtfp':
		$tab="";
		
		$optunit.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)='4' order by induk, namaorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$key = $val['kodeorganisasi'];
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				$optunit.="<optgroup label='".getNamaOrg($d)."'>";
			}
			$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
			$n=$d;
			if($d!=$n){
				$optunit.="</optgroup>";
			}
		}

		## GET MESIN FINGERPRINT
		$optdtmesin.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".att_device order by device_name asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optdtmesin.="<option value='".$val['sn']."'>".$val['device_name']." - ".$val['sn']."</option>";			
		}
		
		$tab.="<table>
			<tr>
				<td style='min-width:80px'>Kode Organisasi</td>
				<td>:</td>
				<td>
					<select class=select2 id='dtorganisasi' style=width:200px;  onchange=\"loaddtdata()\">".$optunit."</select>
				</td>
				
				<td style='min-width:80px'>Mesin finger</td>
				<td>:</td>
				<td>
					<select class=select2 id='dtmesin' style=width:200px; onchange=\"loaddtdata()\">".$optdtmesin."</select>
				</td>
			</tr>
			<tr>
				<td>PIN</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext  id='dtpin' style=width:197px; onkeypress='return tanpa_kutip(event)'/>
				</td>
			
				<td>Nama Karyawan</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext  id='dtnamakaryawan' style=width:197px; onkeypress='return tanpa_kutip(event)'/>
				</td>
			</tr>
			<tr>
				<td colspan=2>
					<input type='hidden' id=dtmethod value='insertdt'>
				</td>
				<td>
					<button class=mybutton onclick=simpandt()>".$_SESSION['lang']['save']."</button>&nbsp;
					<button class=mybutton onclick=bataldt()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>
		<div id='dtcotainer' style=max-height:400px></div>";
		
		echo $tab;
	break;
	case'addserverfp':
		$tab="";
		
		## GET MESIN FINGERPRINT
		$optdtmesin.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".att_device order by device_name asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optdtmesin.="<option value='".$val['sn']."'>".$val['device_name']." - ".$val['sn']."</option>";			
		}
		
		$optserver="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$arrserver=array('10.1.1.63'=>'HO Jakarta','10.7.1.4'=>'SDKM (4)','10.7.1.3'=>'SDKM (3)','10.7.28.99'=>'BPJM','10.7.12.99'=>'KSPM');
		foreach($arrserver as $key => $val){			
			$optserver.="<option value=".$key.">".$val."</option>";
		}
		
		$tab.="<font style=color:red>Sebelum melakukan proses ini pastikan anda sudah mengerti terlebih dahulu.</font>
			<br><table>
			<tr>
				<td style='min-width:80px'>Server FP</td>
				<td>:</td>
				<td>
					<select class=select2 id='serverfpdt' style=width:200px;>".$optserver."</select>
				</td>
				
				<td style='min-width:80px'>Mesin finger</td>
				<td>:</td>
				<td>
					<select class=select2 id='dtmesin' style=width:200px;>".$optdtmesin."</select>
				</td>
			</tr>
			<tr>
				<td></td><td></td>
				<td>
					<button class=mybutton onclick=ambilkary()>".$_SESSION['lang']['preview']."</button>
				</td>
			</tr>
		</table>
		<div id='dtcotainerx' style=max-height:400px></div>";
		
		echo $tab;
	break;
	case'ambilkary':
		$dbserverfp=$param['dtserver'];
		$dbportfp  ='3306';
		$dbnamefp  ='fin_pro';
		$unamefp   ='uploader';
		$passwdfp  ='!0987654321';
		
		
		try{
			$owlPDOFP = new PDO('mysql:host='.$dbserverfp.';dbname='.$dbnamefp, $unamefp, $passwdfp, array(PDO::ATTR_PERSISTENT => false));
			$owlPDOFP->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}catch (PDOException $e){
			echo "masuk kandang";
			print " Gagal, ".$dbserverfp." could not connect\n";	
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		$optnmmesin=makeOption($dbname,'att_device','sn,device_name',"sn='".$dtmesin."'");
		$tab.="<hr>
			<table class='sortable' cellspacing='1' cellpadding=3 border='0' style='min-width:700px;'>
				<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>Mesin Finger</td>
					<td align='center'>SN</td>
					<td align='center'>PIN</td>
					<td align='center'>Nama Karyawan</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody>";
				
		$str = "select * from ".$dbnamefp.".pegawai";
		$res = $owlPDOFP->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=right>".$no."</td>";
			$tab.="<td>".$optnmmesin[$dtmesin]."</td>";
			$tab.="<td id=mesin".$no.">".$dtmesin."</td>";
			$tab.="<td id=pin".$no.">".$bar['pegawai_pin']."</td>";
			$tab.="<td id=nama".$no.">".$bar['pegawai_nama']."</td>";
			$tab.="<td align=center width=30px><input type=checkbox checked></td>";
		}
		echo $tab;
	break;
	case'loaddtdata':
		$tab="";
		if($dtmesin!=''){
			$tab.="<hr>
			<table class='sortable' cellspacing='1' cellpadding=3 border='0' style='min-width:700px;'>
				<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>Mesin</td>
					<td align='center'>PIN</td>
					<td align='center'>Nama Karyawan</td>
					<td align='center' colspan=2>Mapping</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>
				<tbody>";
			
			$optkary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			if($param['dtorganisasi']!=''){				
				$str="select * from ".$dbname.".datakaryawan where karyawanid not in (select karyawan from ".$dbname.".att_pegawai) and lokasitugas='".$param['dtorganisasi']."' order by namakaryawan asc";
				$res=fetchdata($str);
				foreach($res as $val){
					$d=$val['lokasitugas'];
					if($d!=$n){			
						$optkary.="<optgroup label='".getNamaOrg($d)."'>";
					}
					$optkary.="<option value='".$val['karyawanid']."'>".$val['nik']." - ".$val['namakaryawan']." - ".getJabatanKaryawan($val['karyawanid'])."</option>";	
					$n=$d;
					if($d!=$n){
						$optkary.="</optgroup>";
					}
				}
			}
			
			$optnmmesin=makeOption($dbname,'att_device','sn,device_name',"sn='".$dtmesin."'");
			$no=0;
			$str="select * from ".$dbname.".att_pegawai where sn='".$dtmesin."' order by namafp asc";
			$res=fetchdata($str);
			if(count($res) > 0){
				foreach($res as $val){
					$no++;
					$mapping=($val['nik']!=''?'v':'');
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=right>".$no."</td>";
					$tab.="<td>".$optnmmesin[$dtmesin]." - ".$dtmesin."</td>";
					$tab.="<td hidden id=mesin".$no.">".$dtmesin."</td>";
					$tab.="<td id=pin".$no.">".$val['pin']."</td>";
					$tab.="<td>".$val['namafp']."</td>";

					if($val['karyawan']!='0000000000'){						
						$tab.="<td align=left colspan=2>".getKary($val['karyawan'])." - ".getJabatanKaryawan($val['karyawan'])."</td>";
					}else{
						$tab.="<td align=left id=contkaryawan".$no."><select class=select2 name=karyawan[] id=karyawan".$no." style=width:250px;>".$optkary."</select></td>";
						$tab.="<td align=left id=contsimpan".$no."><img id=tombolsave".$no." title='Simpan' class='zImgBtn' onclick=savedt('".$no."'); src='images/save.png'></td>";
					}

					if($mapping==''){
						$tab.="<td align=center id=edit".$no."><img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"dtfillfield('".$dtmesin."','".$val['pin']."','".$val['namafp']."');\"></td>";
						$tab.="<td align=center id=delete".$no."><img src='images/application/application_delete.png' class='resicon' title='Delete' onclick=\"dtdelete('".$dtmesin."','".$val['pin']."');\"></td>";
					}else{
						$tab.="<td colspan=2></td>";						
					}
					$tab.="</tr>";
				}
			}else{
				$tab.="<tr class=rowcontent><td colspan=7 align='center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}
		}
		$tab.="</tbody>
		</table><br><br>";
		
		echo $tab;
	break;
	
	case'dtdelete':
		$str="delete from ".$dbname.".att_pegawai where sn='".$dtmesin."' and pin='".$dtpin."'";
		$owlPDO->exec($str);
	break;
	case'savedt':
		$str="update ".$dbname.".att_pegawai set karyawan='".$param['karyawan']."', nik='".getKary($param['karyawan'],'nik')."' where sn='".$param['mesin']."' and pin='".$param['pin']."'";
		$owlPDO->exec($str);
		
		$optkary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($param['dtorganisasi']!=''){				
			$str="select * from ".$dbname.".datakaryawan where karyawanid not in (select karyawan from ".$dbname.".att_pegawai) and lokasitugas='".$param['dtorganisasi']."' order by namakaryawan asc";
			$res=fetchdata($str);
			foreach($res as $val){
				$optkary.="<option value='".$val['karyawanid']."'>".$val['nik']." - ".$val['namakaryawan']." - ".getJabatanKaryawan($val['karyawanid'])."</option>";	
			}
		}
		
		echo getKary($param['karyawan'],'namakaryawan')."####".$optkary;
	break;
	case'simpandt':
		if($dtmesin==''){
			exit("Gagal, Mesin finger harus dipilih.");
		}
		
		if($dtpin==''){
			exit("Gagal, PIN harus diisi.");
		}
		
		if($dtnamakaryawan==''){
			exit("Gagal, Nama Karyawan harus diisi.");
		}
		
		$str="select * from ".$dbname.".att_pegawai where sn='".$dtmesin."' and pin='".$dtpin."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			exit("Gagal, PIN sudah terdaftar an. ".$res[0]['namafp']."");
		}
		
		
		if($dttipe=='insertdt'){
			$str="insert into ".$dbname.".att_pegawai (sn,pin,namafp) values ('".$dtmesin."','".$dtpin."','".$dtnamakaryawan."')";
			$owlPDO->exec($str);
		}else{
			$str="update ".$dbname.".att_pegawai set namafp='".$dtnamakaryawan."' where sn='".$dtmesin."' and pin='".$dtpin."'";
			$owlPDO->exec($str);
		}
	break;
}
?>
