<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
	require_once 'dompdf/PHPExcel.php';
	require_once 'dompdf/PHPExcel/IOFactory.php';
use Dompdf\Dompdf;

$method   =checkPostGet('method','');
$pages    =checkPostGet('page','');
$unitsc   =checkPostGet('unitsc','');
$tanggalsc=checkPostGet('tanggalsc','');
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
$jlh      = checkPostGet('jlh','');
$idx      = checkPostGet('idx','');

$notranya =checkPostGet('notransaksi','');
$path     ="fileupload/baabsensi/";
$namafile =checkPostGet('namafile','');

// $jnsapp="TR";
$user_id=$_SESSION['standard']['userid'];
$tglskrg=date('Y-m-d H:i:s');

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
		$colspan=17;
		
		$arrorgdet = getOrgDetail(2);
		$where = "";
		if($unitsc!=''){
			$where.=" and kodeorg like '%".$unitsc."%'";
		}
		if($tanggalsc!=''){
			$where.=" and tanggalupload like '%".tanggalsystemn($tanggalsc)."%'";
		}

		
		## GET JUMLAH BARIS
		$str="select id from ".$dbname.".uploadfingerht where kodeorg in (".$arrorgdet.") ".$where."";
		$res=fetchdata($str);
		$jlhbrs = count($res);
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{	
			$str="select * from ".$dbname.".uploadfingerht where kodeorg in (".$arrorgdet.") ".$where." order by tanggalupload desc, kodeorg asc, id asc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				$optnmunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeorg']."'");
				
				$status="";
				if($val['posting']=='0'){
					$status="Belum Diposting";
				// }elseif($val['posting']=='9'){
				// 	$status="Menunggu Approval";
				// }elseif($val['posting']=='2'){
				// 	$status="Ditolak";
				}else{
					$status="Diposting";
				}
				
				$tab.="<tr class=rowcontent>";
				$tab.="<td style='text-align:right;vertical-align:top'>".$no."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".$optnmunit[$val['kodeorg']]."</td>";
				$tab.="<td style='text-align:center;vertical-align:top'>".tanggalnormal($val['tanggalupload'])."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".getNamaKaryawan($val['createdby'])."</td>";
				$tab.="<td style='text-align:center;vertical-align:top'>".$val['createdtime']."</td>";
				$tab.="<td style='vertical-align:top;text-align:center'>".$status."</td>";
				
				if($val['posting']=='0'){
					if($val['createdby']==$user_id){
						$tab.="<td align=center valign=top><img src=images/skyblue/posting.png class=zImgBtn  title='Ajukan ".$val['id']."'  onclick=\"postingdata('".$val['id']."');\"></td>";
						$tab.="<td align=center valign=top><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$val['id']."');\" ></td>";
						$tab.="<td align=center valign=top><img src=images/application/application_edit.png class=zImgBtn  title='Upload Ulang' onclick=\"edit('".$val['id']."');\"></td>";
						$tab.="<td align=center valign=top><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$val['id']."');></td>";
						$tab.="<td align=center valign=top><img src=images/application/application_delete.png class=zImgBtn  title='Delete Data' onclick=\"deletedata('".$val['id']."');\"></td>";
						
					}else{
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td align=center valign=top><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$val['id']."');></td>";
						$tab.="<td></td>";
					}
				}else{
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td align=center valign=top><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$val['id']."');></td>";
					$tab.="<td></td>";					
				}
				$tab.="</tr>";
			}
		}
		
		## PAGING
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		$tab.="</table>";
		
		echo $tab;
	break;
	
	case 'previewDetail':
		$str="select * from ".$dbname.".uploadfinger where id='".$notranya."'";
		$res=fetchData($str);
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<th align='center'>No.</th>
					<th hidden align='center'>Karyawan ID (System)</th>
					<th align='center'>Nama Karyawan</th>
					<th align='center'>date time</th>
					<th align='center'>in/out</th>
					<th align='center'>nama mesin</th>
				</tr>
				</thead>
				<tbody>";

				$optnama_fp=makeOption($dbname,'att_device','sn,device_name');

				foreach ($res as $bar) {
					$no++;
					$tab.="<tr class=rowcontent>";
						$tab.="<td>".$no."</td>";
						$tab.="<td hidden align=left>".$bar['nik']."</td>";
						$tab.="<td align=left>".getNamaKaryawan($bar['nik'])."</td>";
						$tab.="<td align=center>".$bar['datescan']."</td>";
						$tab.="<td align=center>".$bar['inandout']."</td>";
						$tab.="<td align=center>".$optnama_fp[$bar['namamesin']]."</td>";
					$tab.="</tr>";
				}
			$tab.="</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;

	case 'showupload':
		$tab="";
		$tab.="<table border=0 >
					<tr>
						<td>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td>:</td>
						<td id='notranupload'>". $notranya."</td>
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
							<button id=btnsubmit class=mybutton onclick=\"submitfile('".$notranya."')\">Submit</button>
						</td>
					</tr>
				</table>";

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
					values ('".$notranya."','".$filename."','".$filetype."','others','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
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
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$notranya."' and status='1'";
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
				// if($jurnal==0){					
					$tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
					
					$tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" ></td>";
				// }else{
				// 	$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				// }
				$tab.="</tr>";
			}
		}
		echo $tab;
	break;
    case 'deletefile':
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$notranya."' and namafile='".$namafile."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$namafile;
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
    case'viewfile':
		$tab="";
		$str= "select * from ".$dbname.".listfileupload where id = '".$idfile."'";
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
	
	case'getkaryawan':
		$optkaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select karyawanid,namakaryawan,nik, subbagian, lokasitugas from  ".$dbname.".datakaryawan where lokasitugas='".$unit."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') order by subbagian, namakaryawan asc ";
		$res=fetchdata($str);
		$n = "";
		foreach($res as $val){
			if($val['subbagian']==''){
				$val['subbagian']=$val['lokasitugas'];
			}
			$d=$val['subbagian'];
			if($d!=$n){			
				$optkaryawan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['nik']."]</option>";
			$n=$d;
			if($d!=$n){			
				$optkaryawan.="</optgroup>";
			}
		}
		
		echo $optkaryawan;
	break;
	case'getba':
		$nomor=1;
		$noba = "";
		$posting = "0";
		if($unit !="" and $tanggal !="" and $karyawan !="" ){
			$str = "select noba,posting from ".$dbname.".uploadabsensiht where kodeorg='".$unit."' and tanggalabsen = '".tanggalsystemn($tanggal)."' limit 1";
			//echo $str;
			$res = fetchdata($str);
			foreach($res as $bar){
				$noba = trim($bar['noba']);
				$posting = trim($bar['posting']);
			}
			
			if($noba != ""){
				if($posting!='0' or $posting!='2'){
					exit("Gagal, Unit ini sudah dibuat BA untuk tanggal ".tanggalsystemn($tanggal)." dan terposting");
				}else{
					exit("Gagal, Unit ini sudah dibuat BA untuk tanggal ".tanggalsystemn($tanggal).", silahkan lakukan edit pada list data");
				}
			}
			$str = "select max(substr(noba,1,4)) as lastnoba from ".$dbname.".upload_absensi where kodeorg='".$unit."' and tanggalabsen like '".substr(tanggalsystemn($tanggal),0,6)."%' ";
			$res = fetchdata($str);
			foreach($res as $bar){
				$nomor = intval($bar['lastnoba'])+1;
			}
			$noba=addZero($nomor,4)."/".$unit."/BA-ABSENSI/".substr(tanggalsystemn($tanggal),6,2)."/".substr(tanggalsystemn($tanggal),0,4);
		}
		echo $noba;
	break;
	case'insert':
        $data = $_POST;

		// print_r($data);
		if($_FILES['file']['error']==0){
			 $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $file = $_FILES['file']['tmp_name'];       
                if($filetype=='.xlsx'){
					$load = PHPExcel_IOFactory::load($file);
                    $sheets = $load->getActiveSheet()->toArray(null,true,true,true);
					$firsturut1=1;
					$i=1;

					// echo "<pre>";
					// 	print_r($sheets);exit("Error:A");
					// echo "</pre>";


					$str="insert into ".$dbname.".uploadfingerht (kodeorg,tanggalupload,posting,createdby,createdtime) 
									values ('".$data['unit']."','".tanggalsystemn($data['tanggal'])."','0','".$user_id."','".date('Y-m-d')."')";

					try{
						$owlPDO->exec($str);
						$idx = $owlPDO->lastInsertId();


						foreach ($sheets as $sheet){
							if($i > $firsturut1){
								if($sheet['A'] !='' && $sheet['B'] !='' ){
									

									$strc="select karyawan from ".$dbname.".att_pegawai where pin='".$sheet['A']."' and karyawan != '0000000000'";
									$resc=fetchdata($strc);
									$jlhbrs = count($resc);
										if($jlhbrs>0){

											// Create a DateTime object from the string
											$dateTime = new DateTime($sheet['B']);

											// Format the DateTime object to a different format
											// Example: convert to 'd/m/Y H:i:s' format
											$formattedDate = $dateTime->format('Y-m-d H:i:s');	

											$str2="insert into ".$dbname.".uploadfinger (`id`,`nik`,`datescan`,`inandout`,`namamesin`) 
														values ('".$idx."','".$resc[0]['karyawan']."','".$formattedDate."','1','101')";
														try{
															$owlPDO->exec($str2);
														}
														catch(PDOException $e){
															echo " Gagal," . addslashes($e->getMessage());
														}
										}
								}else{
									break;
								}
							}

							$i++;
						}

					}catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
                }else{
                    exit("Warning : Format file upload harus .xlsx");
                }
            }

		
		
	break;
	
	case'update':
		$data = $_POST;

		// print_r($data);
		if($_FILES['file']['error']==0){
			 $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $file = $_FILES['file']['tmp_name'];        
                if($filetype=='.xlsx'){
                    $str="delete from ".$dbname.".uploadfinger where id='".$idx."'";
					$owlPDO->exec($str);

					$load = PHPExcel_IOFactory::load($file);
                    $sheets = $load->getActiveSheet()->toArray(null,true,true,true);
					$firsturut1=1;
					$i=1;
			
					try{
						$owlPDO->exec($str);

						foreach ($sheets as $sheet){
							if($i > $firsturut1){
								if($sheet['A'] !='' && $sheet['B'] !='' ){
									
									$strc="select karyawan from ".$dbname.".att_pegawai where pin='".$sheet['A']."' and karyawan != '0000000000'";
									$resc=fetchdata($strc);
									$jlhbrs = count($resc);
										if($jlhbrs>0){

											// Create a DateTime object from the string
											$dateTime = new DateTime($sheet['B']);

											// Format the DateTime object to a different format
											// Example: convert to 'd/m/Y H:i:s' format
											$formattedDate = $dateTime->format('Y-m-d H:i:s');	

											$str2="insert into ".$dbname.".uploadfinger (`id`,`nik`,`datescan`,`inandout`,`namamesin`) 
														values ('".$idx."','".$resc[0]['karyawan']."','".$formattedDate."','1','101')";
														try{
															$owlPDO->exec($str2);
														}
														catch(PDOException $e){
															echo " Gagal," . addslashes($e->getMessage());
														}
										}
								}else{
									break;
								}
							}

							$i++;
						}

					}catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
		            

                }else{
                    exit("Warning : Format file upload harus .xlsx");
                }
            }
	
		
	break;
	case'ajukan':
        
        $str="select * from ".$dbname.".uploadabsensiht where noba='".$noba."'";
        $res=fetchData($str);
        $kodeorg = $res[0]['kodeorg'];

        for ($i=1; $i <= $jlh ; $i++) { 
            $per['persetujuan'.$i]=checkPostGet("kepada".$i, '');
            if($per['persetujuan'.$i] == '' or $noba==''){
                exit('Warning : Isikan nama penyetuju.');
            }
        }

        $str = "UPDATE " . $dbname . ".uploadabsensiht SET posting='9' WHERE noba= '" . $noba . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }


        
        $jenispersetujuan='BAS';
        for($i=1; $i<=$jlh; $i++){
            $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$kodeorg."'";
            // exit("error : $str");
            $res=fetchData($str);
            $tipeapp = $res[0]['tipe'];
            $departemenapp = $res[0]['departemen'];
            $tipekaryawanapp = $res[0]['tipekaryawan'];
            $jabatanapp = $res[0]['jabatan'];
            
            if(count($res) > 0){
                if($tipeapp=='1'){
                    if($departemenapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$noba."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($tipekaryawanapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$noba."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($jabatanapp!='0'){
                        $str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            if($per['persetujuan'.$i]!=''){
                                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$noba."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                                $owlPDO->exec($str);
                            }
                        }
                    }
                }else{
                    if($per['persetujuan'.$i]!=''){
                        $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$noba."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
                        try
                        {
                            $owlPDO->exec($str);
                        }
                        catch (PDOException $e) 
                        {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                }
            }
        }
    break;
	case'edit':
		$str="select * from ".$dbname.".uploadfingerht where id='".$idx."'";
		$res=fetchdata($str);
		$tanggal=$res[0]['tanggalupload'];
		$unit=$res[0]['kodeorg'];
		
		echo $idx."####".tanggalnormal($tanggal)."####".$unit;
	break;

	case'deletedata':
		$str="delete from ".$dbname.".uploadfingerht where id='".$idx."'";
		$owlPDO->exec($str);
	break;
	
	case'postingdata':
		try {
			$owlPDO->beginTransaction();

			$str="update ".$dbname.".uploadfingerht set posting='1' where id='".$idx."'";
			$owlPDO->exec($str);
			$str="update ".$dbname.".uploadfinger set posting='1' where id='".$idx."'";
			$owlPDO->exec($str);

			$str_insert = '';
			$str_posting="select * from ".$dbname.".uploadfinger where id='".$idx."'";
			$res=fetchdata($str_posting);
			foreach($res as $bar){
				
				$str_cekdata="select * from ".$dbname.".att_log where sn = '".$bar['namamesin']."' and scan_date = '".$bar['datescan']."' and pin = '".$bar['nik']."' and inoutmode= '1' and latitude ='0' and longitude ='0'";
				$res = fetchdata($str_cekdata);
				if(count($res)>0){	
					continue;
				}else{
					$str_insert.="insert into ".$dbname.".att_log (sn,scan_date,pin,inoutmode,latitude,longitude) values ('".$bar['namamesin']."','".$bar['datescan']."','".$bar['nik']."','1','0','0');";
				}							
			}

			if($str_insert != ""){
				$owlPDO->exec($str_insert);
			}

		$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}

	break;
	
	case'simpanapproval':
		try {
			$owlPDO->beginTransaction();

			$str="update ".$dbname.".pmn_tr set posting='9', postedtime='".$tglskrg."' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$str="insert into ".$dbname.".approval values ('','".$notransaksi."','".$jnsapp."','".$level."','".$approval."','0','','','')";
			$owlPDO->query($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'formajukantr':
		$tab="";
		
		##GET DETAIL TR
		$str="select * from ".$dbname.".pmn_tr where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$unit=$res[0]['unit'];
		
		##APPROVAL
		$str="select a.karyawanid,a.nilaidari,a.nilaisampai,a.level,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='".$user_id."' and a.jenispersetujuan='".$jnsapp."' and a.kodeunit='".$unit."'  order by level asc, b.namakaryawan asc";
		$res=fetchdata($str);
		$templevel=0;
		$finalevel=0;
		foreach($res as $val){
			$templevel=$val['level'];
			if($finalevel==0){
				if($val['nilaidari']=='0' && $val['nilaisampai']=='0'){
					$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";
					$finalevel=$val['level'];
				}else{
					if($val['nilaidari'] < $nilai && $nilai <= $val['nilaisampai']){
						$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";						
						$finalevel=$val['level'];
					}
				}
			}else{
				if($templevel==$finalevel){
					$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";						
				}
			}
		}
		$tab.="<input type='hidden' id='level' value='".$finalevel."'>";
		$tab.="<input type='hidden' id='appnotransaksi' value='".$notransaksi."'>";
		
		$tab.="<table>
			<tr>
				<td>No. BA</td>
				<td>:</td>
				<td>".$notransaksi."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kepada']."</td>
				<td>:</td>
				<td>
					<select id='approval'>".$optkaryawan."</select>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"simpanapproval()\">".$_SESSION['lang']['diajukan']."</button>
					<button class=mybutton onclick=closeDialog()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>";
		
		echo $tab;
	break;
	case'previewtr':
		$str = "select * from ".$dbname.".pmn_tr where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res[0]['pt']."'");
		$optkode=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kodebarang='".$res[0]['komoditi']."'");
		$optkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$res[0]['komoditi']."'");
		$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['createdby']."'");
		$nmjabkary=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$res[0]['createdby']."'");
		$nmjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		
		
		
		$tab="";
		$tab.="<table width=100%>
				<td width=25% style=\"font-family:sans-serif;font-weight:bold;font-size:11px;\">".$nmorg[$res[0]['pt']]."</td>
				<td width=50% align=center style=\"font-family:sans-serif;font-weight:bold;font-size:13px;\">MEMO PERMINTAAN ANGKUTAN KOMODITI</td>
				<td width=25%></td>
				</table><br>";
		
		if($tipe=='html'){
			$border=0;
			$width="width=100% cellspacing=1 cellpadding=3";
			$warna="skyblue";
		}elseif($tipe=='pdf'){		
			$border=1; $warna="black";
			$width="width=100% cellspacing=0 cellpadding=1 style=\"font-family: Tahoma, Geneva, sans-serif;font-size:11px;\"";
		}	
		$tab.="<table class=sortable  border='".$border."' ".$width." cellspacing=1>
				<tr class=rowcontent>
					<td width=50% height=80px style=vertical-align:top>Kepada Yth,<br>Bagian Logistic Commercial</td>
					<td width=50% style=vertical-align:top>
						<table>
							<tr><td>No. MP</td><td>:</td><td>".$res[0]['notransaksi']."</td></tr>
							<tr><td>Tanggal</td><td>:</td><td>".tanggalbulan($res[0]['tanggalinput'])."</td></tr>
						</table>
					</td>
				</tr>
				<tr  class=rowcontent>
					<td colspan=2 height=50px>Dengan Hormat,<br>Mohon dipersiapkan jasa angkutan untuk kirim komoditi dibawah ini</td>
				</tr>
				<tr  class=rowcontent>
					<td colspan=2>
						<table width=100% class='sortable' cellspacing=0>
							<thead>
							<tr class=rowheader>
								<td style='border-right:0.5px solid ".$warna.";font-weight:bold;text-align:center'>No</td>
								<td style='border-right:0.5px solid ".$warna.";font-weight:bold;text-align:center'>Kode</td>
								<td style='border-right:0.5px solid ".$warna.";font-weight:bold;text-align:center'>Nama Barang</td>
								<td style='border-right:0.5px solid ".$warna.";font-weight:bold;text-align:center'>Jumlah</td>
								<td style='border-right:0.5px solid ".$warna.";font-weight:bold;text-align:center'>Satuan</td>
								<td style='text-align:left;font-weight:bold;'>Keterangan</td>
							</tr>
							</thead>
							<tbody>
							<tr class='rowcontent'>
								<td style='border-right:0.5px solid ".$warna.";text-align:center;vertical-align:top'>1</td>
								<td style='border-right:0.5px solid ".$warna.";text-align:center;vertical-align:top'>".$optkode[$res[0]['komoditi']]."</td>
								<td style='border-right:0.5px solid ".$warna.";text-align:center;vertical-align:top'>".$optkomoditi[$res[0]['komoditi']]."</td>
								<td style='border-right:0.5px solid ".$warna.";text-align:center;vertical-align:top'>".number_format($res[0]['kuantitas'])."</td>
								<td style='border-right:0.5px solid ".$warna.";text-align:center;vertical-align:top'>".$res[0]['satuan']."</td>
								<td style='text-align:left;vertical-align:top'>".nl2br($res[0]['keterangan'])."</td>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr class=rowcontent>
					<td colspan=2 height=30px>Tanggal dibutuhkan : ".tanggalbulan(tanggalsystemn(tanggalnormal($res[0]['tanggaldibutuhkan'])))."</td>
				</tr>
				<tr  class=rowcontent>
					<td colspan=2>";
					$countApprove = getCountApproval('TR',$res[0]['unit']);
					$tab.="<table width=100%>";
					$tab.="<tr>";
					$tab.= "<td height=70px style='text-align:center;vertical-align:top'>".$_SESSION['lang']['dibuat'].",</td>";
					for($i=1;$i<=$countApprove;$i++){
						$tab.= "<td style='border-left:0.5px solid ".$warna.";text-align:center;vertical-align:top'>".$_SESSION['lang']['persetujuan']." ".$i.",</td>";
					}
					$tab.="</tr>";
					
					$tab.="<tr>";
					$tab.= "<td style='text-align:center;vertical-align:top'>".$nmkary[$res[0]['createdby']]."<br>".$nmjab[$nmjabkary[$res[0]['createdby']]]."</td>";
					for($i=1;$i<=$countApprove;$i++){
						$arrApp = detailApprove($i,$notransaksi,'TR');
						
						$tab.= "<td style='border-left:0.5px solid ".$warna.";text-align:center;vertical-align:top'>".$arrApp['nama']."<br>".$arrApp['namajabatan']."</td>";
					}
					$tab.="</tr>";
					
				$tab.="</table>";
				$tab.="</td>
				</tr>
				";
		if($tipe=='pdf'){			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$dompdf->stream("tr", array("Attachment" => false));
		}else{
			echo $tab;
		}		
	break;
}
?>
