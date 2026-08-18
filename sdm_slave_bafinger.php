<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

require_once('dompdf/autoload.inc.php');
	require_once 'dompdf/PHPExcel.php';
	require_once 'dompdf/PHPExcel/IOFactory.php';
use Dompdf\Dompdf;

$method   =checkPostGet('method','');
$pages    =checkPostGet('page','');
$scnoba   =checkPostGet('scnoba','');
$sctanggal=checkPostGet('sctanggal','');
$scnama   =checkPostGet('scnama','');
$scstatusper   =checkPostGet('scstatusper','');
$noba     =checkPostGet('noba','');
$tanggal  =checkPostGet('tanggal','');
$unit     =checkPostGet('unit','');
$karyawan =checkPostGet('karyawan','');
$absen    =checkPostGet('absen','');
$jam      =checkPostGet('jam','');
$mnt      =checkPostGet('mnt','');
$jam2     =checkPostGet('jam2','');
$mnt2     =checkPostGet('mnt2','');
$jam3     =checkPostGet('jam3','');
$mnt3     =checkPostGet('mnt3','');
$jam4     =checkPostGet('jam4','');
$mnt4     =checkPostGet('mnt4','');
$keterangan     	  =checkPostGet('keterangan','');
$tanggaljamkeluar     =checkPostGet('tanggaljamkeluar','');
$tanggaljammasuk      =checkPostGet('tanggaljammasuk','');
$jlh 				  =checkPostGet('jlh', '');
$tipeba 			  =checkPostGet('tipeba', '');

$jenispersetujuan="BAA";
$user_id=$_SESSION['standard']['userid'];
$tglskrg=date('Y-m-d H:i:s');

$path     ="fileupload/baabsensi/";
$namafile =checkPostGet('namafile','');

switch ($method){
	case'loaddata':


		$tab="";
		
		$limit=20;
        $page=0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		$no=(($page*$limit));
		$colspan=18;
		
		$arrorgdet = getOrgDetail(2);
		$where = "";
		if($scnoba!=''){
			$where.=" and a.noba like '%".$scnoba."%'";
		}

		if($sctanggal!=''){
			$where.=" and a.tanggalabsen='".tanggalsystem($sctanggal)."'";
		}

		if($scnama!=''){
			$where.=" and b.namakaryawan like '%".$scnama."%'";
		}

		if($scstatusper!=''){
			$where.=" and a.statuspersetujuan ='".$scstatusper."'";
		}

		$where.=" and sumber='manual'";
		
		## Jabatan bisa batal ba
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'HR' and kodeparameter='BABATAL'";
		$res=fetchdata($str);
		$jabatanBatal = $res[0]['nilai'];

		$array_jabatan = array();

		$flag=0;
		$str="select *  from ".$dbname.".sdm_5jabatan where kodejabatan in (".$jabatanBatal.")";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['kodejabatan'] == $_SESSION['empl']['jabatan']){
				$flag = 1;
			}  

			$array_jabatan[$val['kodejabatan']] = $val['kodejabatan'];
		}

		if(!in_array($_SESSION['empl']['jabatan'],$array_jabatan)){
			$where.=" and a.createdby ='".$_SESSION['standard']['userid']."'";
		}

		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'HR' and kodeparameter='BABATALX'";
		// exit;
		$res=fetchdata($str);
		$jabatanBatal = $res[0]['nilai'];


		if(!empty($jabatanBatal)) {
			$str = "SELECT * FROM ".$dbname.".sdm_5jabatan 
					WHERE kodejabatan IN (".$jabatanBatal.")";
		} else {
			// kalau kosong biar gak error
			$str = "SELECT * FROM ".$dbname.".sdm_5jabatan WHERE 1=0";
		}

		$res=fetchdata($str);
		foreach($res as $val){
			if($val['kodejabatan'] == $_SESSION['empl']['jabatan']){
				$flag = 1;
			}  

		}

		## GET JUMLAH BARIS
		$str="select a.noba from ".$dbname.".sdm_ba_absensi a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.kodeorg in (".$arrorgdet.") ".$where."";
		$res=fetchdata($str);
		$jlhbrs = count($res);
		$tipeba=array('1'=>'Jam Masuk dan Jam Keluar','2'=>'Jam Masuk','3'=>'Jam Keluar');
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{	
			$str="select a.*, b.namakaryawan, b.nik from ".$dbname.".sdm_ba_absensi a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.kodeorg in (".$arrorgdet.") ".$where." order by a.tanggalabsen desc, a.kodeorg asc, b.namakaryawan asc, a.noba asc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				$optnmunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeorg']."'");
				$optabsen=makeOption($dbname,'sdm_5absensi','kodeabsen,keterangan',"kodeabsen='".$val['absensi']."'");
				
				$status="";
				if($val['statuspersetujuan']=='0'){
					$status="Belum Diajukan";
					$warna = "style='vertical-align:top;text-align:center;font-weight:bold;'";
				}elseif($val['statuspersetujuan']=='9'){
					$status="Proses Approval";
					$warna = "style='vertical-align:top;text-align:center;background-color:yellow;font-weight:bold;'";
				}elseif($val['statuspersetujuan']=='2'){
					$status="Ditolak";
					$warna = "style='vertical-align:top;text-align:center;color:red;font-weight:bold;'";
				}else{
					$status="Disetujui";
					$warna = "style='vertical-align:top;text-align:center;color:green;font-weight:bold;'";
				}

				$tab.="<tr class=rowcontent>";
				$tab.="<td style='text-align:right;vertical-align:top'>".$no."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".$val['noba']."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".$optnmunit[$val['kodeorg']]."</td>";
				$tab.="<td style='text-align:center;vertical-align:top;min-width:70px;'>".tanggalnormal($val['tanggalabsen'])."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".$val['namakaryawan']." [".$val['nik']."]</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".getNamaOrg($val['subbagian'])."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>(".$val['tipeba'].")".$tipeba[$val['tipeba']]."</td>";
				$tab.="<td style='text-align:center;vertical-align:top'>".$optabsen[$val['absensi']]."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".$val['penjelasan']."</td>";
				$tab.="<td style='text-align:center;vertical-align:top' width=65px>".$val['jam']."</td>";
				$tab.="<td hidden style='text-align:center;vertical-align:top' width=65px>".$val['jam2']."</td>";
				$tab.="<td hidden style='text-align:center;vertical-align:top' width=65px>".$val['jam3']."</td>";
				$tab.="<td style='text-align:center;vertical-align:top' width=65px>".$val['jam4']."</td>";
				$tab.="<td style='text-align:center;vertical-align:top'>".getNamaKaryawan($val['createdby'])."</td>";
				$tab.="<td ".$warna.">".$status."</td>";
				$tab.="<td style='vertical-align:top;text-align:center'>".$val['createdtime']."</td>";

				if($val['statuspersetujuan']=='0'){
					if($val['createdby']==$user_id){
						$tab.="<td align=center valign=top><img src=images/skyblue/submit.jpg class=zImgBtn  title='Ajukan' onclick=\"formajukan('".$val['noba']."',event);\"></td>";
						$tab.="<td align=center valign=top><img src=images/application/application_edit.png class=zImgBtn  title='Edit BA' onclick=\"editba('".$val['noba']."');\"></td>";
						$tab.="<td align=center valign=top><img src=images/application/application_delete.png class=zImgBtn  title='Delete BA' onclick=\"deleteba('".$val['noba']."');\"></td>";
					}else{
						$tab.="<td></td><td></td><td></td>";
					}
				}elseif($val['statuspersetujuan']=='9'){
					$tab.="<td></td><td></td><td></td>";				
				}else{
					if($flag == 1){
						$tab.="<td></td>
						<td></td>
						<td align=center style=width:20px><img src=images/stop1.png class=zImgBtn  title='Batal' onclick=\"form_batal('".$val['noba']."');\" ></td>";	
					}else{
						$tab.="<td></td><td></td><td></td>";				
					}
				}
				
				$tab.="<td align=center valign=top>
					<img src=images/zoom.png class=zImgBtn title='Preview' onclick=\"gethistoriapproval('".$val['noba']."');\">
				</td>";	
				$tab.="<!--<td align=center valign=top>
					<img src=images/pdf.jpg class=zImgBtn title='Print PDF' onclick=\"previewtr(event,'".$val['noba']."','pdf');\">
				</td>-->";
				$tab.="</tr>";
			}
		}
		
		## PAGING
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		$tab.="</table>";
		
		echo $tab;
	break;

	case'form_batal';
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>".$_SESSION['lang']['notransaksi']."</td>
					<td width=5px>:</td>
					<td id=notran_batal>".$noba."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px valign=top>".$_SESSION['lang']['keterangan']."</td>
					<td width=5px valign=top>:</td>
					<td><textarea rows=3 maxlength='1024' id=ketbatal type='text' onkeypress='return tanpa_kutip(event)' style='width:205px;'></textarea></td>
				</tr>
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td align=left><button class=mybutton onclick=batalkan()>" . $_SESSION['lang']['save'] . "</button></td>
				</tr>				
				</table>";
        echo $tab;
	break;

	case'batalkan':
		try {
		$owlPDO->beginTransaction();
		
			if($noba==''){
				throw new PDOException("Noba wajib diisi.");
			}
			if($keterangan==''){
				throw new PDOException("Keterangan wajib diisi.");
			}
			
			$data = array();
			$data = array(
				'statuspersetujuan'=> 0,
				'posting' 		 => 0,
				'updatedby'       => $_SESSION['standard']['userid'],
				'keteranganbatal'=> $keterangan,
				'statusbatal'=> 1,
			);
			
			$where = "noba='".$noba."'";
			$str = updateQuery($dbname,'sdm_ba_absensi',$data,$where);
			$owlPDO->exec($str);

			$str="select *  from ".$dbname.".approval where notransaksi='".$noba."'";
			$res=fetchdata($str);
			foreach($res as $val){
				# jika ada pindahkan ke table ini
				$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('".$val['notransaksi']."','".$val['jenispersetujuan']."','".$val['level']."','".$val['karyawanid']."','".$val['status']."','".$val['komentar']."','".$val['keterangan']."','".$val['tanggal']."')";
				$owlPDO->exec($str);
			}

			#kemudian setelah di pindah, hapus persetujuan lama
			$str="delete from ".$dbname.".approval where jenispersetujuan='BAA' and notransaksi='".$noba."'";
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;

	
	
	case'getkaryawan':
		$optkaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select karyawanid,namakaryawan,nik, subbagian, lokasitugas from  ".$dbname.".datakaryawan where lokasitugas='".$unit."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') order by subbagian, namakaryawan asc ";
		$res=fetchdata($str);
		$d ="";
		foreach($res as $val){
			if($val['subbagian']==''){
				$val['subbagian']=$val['lokasitugas'];
			}
			$d=$val['subbagian'];
			if($d!=$n){			
				$optkaryawan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['nik']."]</option>";
			if($d!=$n){			
				$n=$d;
				$optkaryawan.="</optgroup>";
			}
		}
		
		echo $optkaryawan;
	break;

	case 'getShift':
		
		$str = "select a.* from " . $dbname . ".sdm_5shift a join " . $dbname . ".sdm_5shiftanggota b on a.id = b.idshift and a.kodeorg=b.kodeorg where b.kodeorg = '".$unit."' and b.karyawanid = '".$karyawan."' and b.tanggal = '".tanggalsystemn($tanggal)."' order by b.idshift desc";
		$res = fetchdata($str);
		if(count($res) <= 0){
			exit("ERROR : Setup Shift belum ada (".tanggalsystemn($tanggal)." - ".$unit.") ");
		} 
			
		echo $res[0]['masuk']."###".$res[0]['keluar'];
		
		
	break;
	
	case'insert':
		
		$noba=str_replace('-','',tanggalsystemn($tanggal))."/BA-ABSENSI/".$karyawan;
		
		if($noba==''){
			exit("Gagal, No. BA harus diisi");
		}
		if($karyawan==''){
			exit("Gagal, Karyawan harus dipilih");
		}
		if($keterangan==''){
			exit("Gagal, Keterangan harus dipilih");
		}
		
		## CEK NO BA
		$str="select noba from ".$dbname.".sdm_ba_absensi where noba='".$noba."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			exit("Gagal, No. BA ".$noba." sudah pernah terdaftar sebelumnya. Ganti No. BA dengan yang lain");
		}

		$str="select noba from ".$dbname.".sdm_ba_absensi where karyawanid='".$karyawan."' and tanggalabsen='".tanggalsystemn($tanggal)."' and statuspersetujuan != 2 ";
		$res=fetchdata($str);
		if(count($res) > 0){
			exit("Gagal, Karaywan sudah pernah terdaftar sebelumnya.");
		}
		
		$jam=addZero($jam,2).":".addZero($mnt,2).":00";
		$jam2=addZero($jam2,2).":".addZero($mnt2,2).":00";
		$jam3=addZero($jam3,2).":".addZero($mnt3,2).":00";
		$jam4=addZero($jam4,2).":".addZero($mnt4,2).":00";
		
		if($jam=='00:00:00' or $jam4=='00:00:00'){
			exit("Gagal : Jam masuk dan jam pulang harus diisi.");
		}
		
		$str="insert into ".$dbname.".sdm_ba_absensi (noba,kodeorg,subbagian,tanggalabsen,karyawanid,absensi,tanggaljammasuk,jam,jam2,jam3,jam4,tanggaljamkeluar,sumber,flag,tipeba,updatedby,updatedtime,createdby,createdtime,penjelasan) values ('".$noba."','".$unit."','".getKary($karyawan,'subbagian')."','".tanggalsystemn($tanggal)."','".$karyawan."','".$absen."','".tanggalsystemn($tanggaljammasuk)."','".tanggalsystemn($tanggaljammasuk)." ".$jam."','".tanggalsystemn($tanggal)." ".$jam2."','".tanggalsystemn($tanggal)." ".$jam3."','".tanggalsystemn($tanggaljamkeluar)." ".$jam4."','".tanggalsystemn($tanggaljamkeluar)."','Manual','0','".$tipeba."','".$user_id."','".$tglskrg."','".$user_id."','".$tglskrg."','".$keterangan."')";
		
		 try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		echo $noba;
	break;
	case'getkaryawanid':
		$stream="<table class=sortable border=1 cellpadding=5 cellspacing=1>
					<thead>
					<tr class=rowheader>
						<td align=center>".$_SESSION['lang']['nik']."</td>
						<td align=center>".$_SESSION['lang']['nama']."</td>
						<td align=center>".$_SESSION['lang']['lokasitugas']."</td>
						<td align=center>".$_SESSION['lang']['divisi']."</td>
					</tr>
					</thead><tbody>";


		$str = "select * from ".$dbname.".datakaryawan 
				where (tanggalkeluar='0000-00-00' or tanggalkeluar>'".date('Y-m-d')."') and lokasitugas = '".$unit."' order by namakaryawan";
				$res = fetchData($str);
				foreach($res as $bar){

					if($bar['subbagian'] == ''){
						$text='KANTOR';
					}else{
						$text=$bar['subbagian'];
					}

					$stream.="<tr class=rowcontent>
								<td>".$bar['nik']."</td>
								<td>".$bar['namakaryawan']."</td>
								<td>".$bar['lokasitugas']."</td>
								<td>".$text."</td>
							</tr>";						
				}

				$stream.="<tbody></table>";
		
			$nop_="Daftar_Karyawan_".$unit;
			if(strlen($stream)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
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

	break;

	case'insertfile':
		if($_FILES['file']['error']==0){

			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$file = $_FILES['file']['tmp_name'];       
			if($filetype=='.xlsx'){
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null,true,true,true);
				$firsturut1=1;
				$i=1;

				try{
					$owlPDO->beginTransaction();

					
					foreach ($sheets as $sheet){
						if($i > $firsturut1){
							if($sheet['A'] !=''){
								$karyawan_id=makeOption($dbname,'datakaryawan','nik,karyawanid');

								$noba=str_replace('-','',$sheet['B'])."/BA-ABSENSI/".$karyawan_id[$sheet['A']];

								$str="select * from ".$dbname.".sdm_ba_absensi where karyawanid='".$karyawan_id[$sheet['A']]."' and tanggalabsen='".$sheet['B']."' and statuspersetujuan != 2 ";
								$res=fetchdata($str);
								if(count($res) == 0){

									$dateString_Masuk = $sheet['B'] . ' ' . $sheet['C'];
									$dateTime_Masuk = new DateTime($dateString_Masuk);

									$dateString_Keluar = $sheet['E'] . ' ' . $sheet['D'];
									$dateTime_Keluar = new DateTime($dateString_Keluar);


									$data = array(
										'noba'      		=> $noba,
										'kodeorg' 			=> $unit,
										'subbagian' 		=> getKary($karyawan_id[$sheet['A']],'subbagian'),
										'tanggalabsen'		=> $sheet['B'],
										'karyawanid'		=> $karyawan_id[$sheet['A']],
										'absensi'     		=> 'H',
										'tanggaljammasuk'   => $sheet['B'],
										'jam'       		=> $dateString_Masuk,
										'jam2'       		=> '00:00:00',
										'jam3'        		=> '00:00:00',
										'jam4'        		=> $dateString_Keluar,
										'tanggaljamkeluar'  => $sheet['E'],
										'sumber'        	=> 'Manual',
										'flag'        		=> '0',
										'tipeba'        	=> $sheet['F'],
										'updatedby'        	=> $user_id,
										'updatedtime'       => $tglskrg,
										'createdby'        	=> $user_id,
										'createdtime'       => $tglskrg,
										'penjelasan'        => $sheet['G']
									);

									$cols = array();
									foreach($data as $key=>$row) {
										$cols[] = $key;
									}
									
						
									$query = insertQuery($dbname,'sdm_ba_absensi',$data,$cols);
									$owlPDO->exec($query);

								}else{
									continue;
								}
							}else{
								break;
							}
						}
						$i++;
					}

					$owlPDO->commit();
				}catch(PDOException $e){
					$owlPDO->rollback();
					echo "Error, ".addslashes($e->getMessage());
					die();
				}

				
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
	break;
	
	case'update':
		$jam=addZero($jam,2).":".addZero($mnt,2).":00";
		$jam2=addZero($jam2,2).":".addZero($mnt2,2).":00";
		$jam3=addZero($jam3,2).":".addZero($mnt3,2).":00";
		$jam4=addZero($jam4,2).":".addZero($mnt4,2).":00";
		
		if($jam=='00:00:00' or $jam4=='00:00:00'){
			exit("Gagal : Jam masuk dan jam pulang harus diisi.");
		}
		if($noba==''){
			exit("Gagal : No BA tidak ditemukan, proses dibatalkan.");
		}
		
		$str="update ".$dbname.".sdm_ba_absensi set jam='".tanggalsystemn($tanggal)." ".$jam."',jam2='".tanggalsystemn($tanggal)." ".$jam2."',jam3='".tanggalsystemn($tanggal)." ".$jam3."',jam4='".tanggalsystemn($tanggal)." ".$jam4."',updatedby='".$user_id."',updatedtime='".$tglskrg."', penjelasan='".$keterangan."' , tipeba='".$tipeba."' where noba='".$noba."'";
		try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	
	case'editba':
		$str="select * from ".$dbname.".sdm_ba_absensi where noba='".$noba."'";
		$res=fetchdata($str);
		$noba=$res[0]['noba'];
		$tanggal=$res[0]['tanggalabsen'];
		$unit=$res[0]['kodeorg'];
		$karyawan=$res[0]['karyawanid'];
		$absen=$res[0]['absensi'];
		$ket=$res[0]['penjelasan'];
		$jam=substr($res[0]['jam'],11,2);
		$jam2=substr($res[0]['jam2'],11,2);
		$jam3=substr($res[0]['jam3'],11,2);
		$jam4=substr($res[0]['jam4'],11,2);
		$tanggaljamkeluar=$res[0]['tanggaljamkeluar'];
		$tanggaljammasuk=$res[0]['tanggaljammasuk'];

		$minutes = substr($res[0]['jam'],14, 2);
		$minutes2 = substr($res[0]['jam2'],14, 2);
		$minutes3 = substr($res[0]['jam3'],14, 2);
		$minutes4 = substr($res[0]['jam4'],14, 2);

		$tipeba=$res[0]['tipeba'];
		echo $noba."####".tanggalnormal($tanggal)."####".$unit."####".$karyawan."####".$absen."####".$jam."####".$jam2."####".$jam3."####".$jam4."####".$ket."####".tanggalnormal($tanggaljamkeluar)."####".$minutes."####".$minutes2."####".$minutes3."####".$minutes4."####".tanggalnormal($tanggaljammasuk)."####".$tipeba;
	break;

	case'deleteba':
		$str="delete from ".$dbname.".sdm_ba_absensi where noba='".$noba."'";
		 try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	
	case'formajukan':

		$sDat = selectQuery($dbname,"datakaryawan","bagian,kodegolongan,lokasitugas", "karyawanid = '".$_SESSION['standard']['userid']."'");
		$qDat = fetchData($sDat);
		
		$karDepar = $qDat[0]['bagian'];
		$karGol = $qDat[0]['kodegolongan'];
		$karTugas = $qDat[0]['lokasitugas'];

		/* Commment aja kalau ga butuh lokasi tugas dari pengajuan */
		$lokTugasPengaju = " AND kodeunit = '".$karTugas."'";
	
		/* Approval Dinamis */
		$where = '';

		/* Cek Perdepartemen */
		$sStr = selectQuery($dbname,"setup_approval","COUNT(departemen) AS perdepartemen", "jenispersetujuan = '".$jenispersetujuan."' AND departemen = '".$karDepar."' ".$lokTugasPengaju."");
		$qStr = fetchData($sStr);
		$perdepartemen = $qStr[0]['perdepartemen'];
		$where .= " AND departemen = '".($perdepartemen > 0 ? $karDepar : '')."'";

		/* Cek Pergolongan */
		$sStr = selectQuery($dbname,"setup_approval","COUNT(golongan) AS pergolongan", "jenispersetujuan = '".$jenispersetujuan."' AND golongan = '".$karGol."' ".$lokTugasPengaju."");
		$qStr = fetchData($sStr);
		$pergolongan = $qStr[0]['pergolongan'];
		$where .= " AND golongan = '".($pergolongan > 0 ? $karGol : '')."'";

		// Setup Approval
		$sApp = selectQuery($dbname,"setup_approval","*", "jenispersetujuan = '".$jenispersetujuan."' AND kodeunit = '".$_SESSION['empl']['lokasitugas']."' AND level=1 ".$where."", "level");
		$qApp = fetchData($sApp);

		/* Kasih warning apabila tidak ada yang cocok di setup */
		if (count($qApp) <= 0) {
			exit("warning : Silahkan tambahkan nama penyetuju melalui menu : Setup - Persetujuan");
		}

		// Input Data Approval
		$optApp = array();
		foreach ($qApp as $apv) {
			$optApp[$apv['level']][] = $apv['karyawanid']; 
		}

		// Membuat Select Option
		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$tab = '';
		$jlh = 0;
		foreach ($optApp as $level => $user) {
			$opt = '';
			foreach ($user as $username) {
				$opt .= "<option value='".$username."'>".$nmkar[$username]."</option>";
			}
			if ($opt != '') {
				$jlh++;
				$tab .="
				<tr class='rowcontent'>
					<td>Approval ke-".$level."</td>
					<td width='5px'>:</td>
					<td><select id='kepada".$level."' style='width: 99%';>".$opt."</select></td>
				</tr>";
			}
		}

				/* Ambil jumlah total approval */
		$tab .= "<input hidden id=jlh value='".$jlh."'>";
				/* Ambil no transaksi */
		$tab .= "<input hidden  id=notransaksi_ajukan value='".$noba."'>";

		$tab .= "<tr>
					<td></td>
					<td></td>
					<td><button id='tomboldetail' class='mybutton' onclick=\"ajukan()\">".$_SESSION['lang']['diajukan']."</button></td>
				</tr>";
		echo $tab;
	break;

	case 'ajukan':
		$notransaksi = $noba;

		/* cek apabila user membuka 2 tab */
		$sAppr = selectQuery($dbname,"sdm_ba_absensi","statuspersetujuan", "noba = '".$notransaksi."'");
		$qAppr = fetchData($sAppr);
		$stts = [1, 2, 9];
		if (in_array($qAppr[0]['statuspersetujuan'], $stts)) {
			exit("warning : Transaksi sudah proses persetujuan/sudah disetujui!");
		}


		$sDat = selectQuery($dbname,"datakaryawan","lokasitugas", "karyawanid = '".$_SESSION['standard']['userid']."'");
		$qDat = fetchData($sDat);
		$karTugas = $qDat[0]['lokasitugas'];

		/* Commment aja kalau ga butuh lokasi tugas dari pengajuan */
		$lokTugasPengaju = " AND kodeunit = '".$karTugas."'";
		
		/* Error jika Penyetuju tidak diinput */
		if ($jlh == 0) {
				exit("Warning : Isikan nama penyetuju");
		}
		
		$appr = array();
		for ($lev = 1; $lev <= $jlh; $lev++) { 
				$appr[$lev] = checkPostGet("kepada".$lev."", '');/* Ambil per masing-masing user approval */
				$sApp = selectQuery($dbname,"setup_approval","*", "jenispersetujuan='".$jenispersetujuan."' AND level='".$lev."' ".$lokTugasPengaju."");
				$qApp = fetchData($sApp);

				if (count($qApp) > 0) {
						$tipeApp = $qApp[0]['tipe'];
						$departemenApp = $qApp[0]['departemen'];
						$tipekaryawanApp = $qApp[0]['tipekaryawan'];
						$jabatanApp = $qApp[0]['jabatan'];
						$data = array(
								'notransaksi'=> $notransaksi,
								'jenispersetujuan'=> $jenispersetujuan,
								'level'=> $lev,
								'status'=> '0',
						);
						
						$data['karyawanid'] = $appr[$lev];
						$sIns = insertQuery($dbname,'approval',$data, array_keys($data));
						try { 
								$owlPDO->exec($sIns); 
						} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
						}
						
				}
		}

		/* Update Status persetujuan di transaksi */
		$data = array(
				'statuspersetujuan'=> 9,
		);
		$sUpt = updateQuery($dbname,'sdm_ba_absensi',$data, "noba = '".$notransaksi."'");
		try {
				$owlPDO->exec($sUpt); 
		} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;
	
	// case'postingba':
	// 	try {
	// 	$owlPDO->beginTransaction();
		
	// 	$str = "select * from " . $dbname . ".sdm_ba_absensi where noba='".$noba."'";
	// 	$res = fetchdata($str);
	// 	foreach($res as $bar){
	// 		$karyawanid = $bar['karyawanid'];
	// 		$param['tanggal'] = $bar['tanggalabsen'];
	// 		$param['subbagian'] = $bar['subbagian'];
	// 		$param['kodeorg'] = $bar['kodeorg'];
	// 	}
		
	// 	$tipesubbagian= getNamaOrg($param['subbagian'],'tipe');
	// 	$tipeorg      = getNamaOrg($param['kodeorg'],'tipe');
		
	// 	$insert="";
	// 	$str = "select * from " . $dbname . ".sdm_5fptoabsensi where kodeorg='".$param['kodeorg']."' and subbagian='".$param['subbagian']."'";
	// 	$res = fetchData($str);
	// 	if(count($res)==0){
	// 		exit("Error : SDM - Setup - FP to Absensi belum di setup.");
	// 	}
	// 	$insert = $res[0]['absensi'];
	// 	$akun = $res[0]['noakun'];
		
	// 	if($insert==0){
	// 		#tidak di insert ke sdm absensi
	// 		//exit("error xxxx");
	// 	}else if($insert==1){
	// 		//exit("error");
	// 		#di insert ke sdm absensi
	// 		if($param['subbagian']==''){
	// 			$divisikary=$param['kodeorg'];
	// 		}else{
	// 			$divisikary=$param['subbagian'];
	// 		}
	// 		$str = "select * from " . $dbname . ".sdm_absensiht where tanggal='".$param['tanggal']."' and kodeorg='".$divisikary."'";
	// 		$res = count(fetchData($str));
	// 		# jika belum ada di ht maka insert dulu
	// 		if($res==0){
	// 			$data = array(
	// 				'tanggal' => $param['tanggal'],
	// 				'kodeorg' => $divisikary,
	// 				'periode' => substr($param['tanggal'],0,7),
	// 				'updateby'=> $_SESSION['standard']['userid']
	// 			);
				
	// 			# Insert sdm_absensiht
	// 			$query = insertQuery($dbname,'sdm_absensiht',$data,array_keys($data));
	// 			$owlPDO->exec($query);
	// 		}
			
	// 		$param['divisi']=$param['subbagian'];
	// 		$opttipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
			
	// 		$arrthn = explode("-",$param['tanggal']);
	// 		$tahun = $arrthn[0]; 
	// 		$str = "select * from ".$dbname.".sdm_ba_absensi where noba='".$noba."'";
	// 		$res = fetchdata($str);
	// 		foreach($res as $bar){
	// 			$tipekary=false;
	// 			#bhl
	// 			if(getKary($bar['karyawanid'],'tipekaryawan')=='4'){
	// 				$tipekary=true;					
	// 			}
	// 			#nonstaff
	// 			if(getKary($bar['karyawanid'],'tipekaryawan')=='1'){
	// 				$tipekary=true;
	// 			}
				
	// 			# ambil gaji pokok
	// 			$jlhumr=0;
	// 			$sql = "select jumlah from ".$dbname.".sdm_5gajipokok where karyawanid='".$bar['karyawanid']."' and tahun=".$tahun." and idkomponen in ('1')";					
	// 			$req = fetchdata($sql);
	// 			if(count($req)==0 and $tipekary==true){
	// 				throw new PDOException("Gaji pokok karyawan an. ".getKary($bar['karyawanid'])." belum ada.");
	// 			}
	// 			$jlhumr = $req[0]['jumlah']/25;
				
	// 			# insert
	// 			$data = array();
	// 			$data = array(
	// 				'kodeorg'           => $divisikary,
	// 				'tanggal'           => $param['tanggal'],
	// 				'karyawanid'        => $bar['karyawanid'],
	// 				'noakun'            => $akun,
	// 				'absensi'           => 'H',
	// 				'jam'               => substr($bar['jam'],-8),
	// 				'jamistirahatdari'  => substr($bar['jam2'],-8),
	// 				'jamistirahatsampai'=> substr($bar['jam3'],-8),
	// 				'jamPlg'            => substr($bar['jam4'],-8),
	// 				'premi'             => 0,
	// 				'hk'                => 1,
	// 				'umr'               => $jlhumr,
	// 				'penjelasan'        => "Auto form BA Absensi ".$noba,
	// 				'idfp'              => $bar['id']
	// 			);
				
	// 			$jlhabsen=0;
	// 			#cek sudah di absen atau belum
	// 			$sql = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$bar['karyawanid']."' and tanggal='".$param['tanggal']."'"; 
	// 			#exit("error".$sql);
	// 			$req = fetchdata($sql);
	// 			if(count($req)>0){					
	// 				$jlhabsen+=1;
	// 			}
				
	// 			$sql = "select * from ".$dbname.".kebun_aktifitas where (nikmandor='".$bar['karyawanid']."' or nikmandor1 ='".$bar['karyawanid']."' or nikasisten ='".$bar['karyawanid']."' or keranimuat ='".$bar['karyawanid']."') and tanggal='".$param['tanggal']."'"; #exit("error".$sql);
	// 			$req = fetchdata($sql);
	// 			if(count($req)>0){					
	// 				$jlhabsen+=1;
	// 			}
				
	// 			$sql = "select * from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where (a.nik ='".$bar['karyawanid']."' or a.nikpemel ='".$bar['karyawanid']."') and b.tanggal='".$param['tanggal']."'"; #exit("error".$sql);
	// 			$req = fetchdata($sql);
	// 			if(count($req)>0){					
	// 				$jlhabsen+=1;
	// 			}
				
	// 			$sql = "select * from ".$dbname.".vhc_runhk where idkaryawan ='".$bar['karyawanid']."' and tanggal='".$param['tanggal']."'"; #exit("error".$sql);
	// 			$req = fetchdata($sql);
	// 			if(count($req)>0){					
	// 				$jlhabsen+=1;
	// 			}
				
				
	// 			# Insert sdm_absensidt
	// 			$query = insertQuery($dbname,'sdm_absensidt',$data,array_keys($data));
	// 			if($tipekary==true and $jlhabsen==0){
	// 				$owlPDO->exec($query);
	// 			}
	// 		}
	// 	}
	
	
	// 	$str="update ".$dbname.".sdm_ba_absensi set posting='1', postingby='".$user_id."', postingtime='".$tglskrg."' where noba='".$noba."'";
	// 	$owlPDO->exec($str);
		
	// 	#execute
	// 	$owlPDO->commit();
	// 	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	// break;
}
?>
