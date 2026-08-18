<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');

$tipeApp = "CB";
$jenisApp = "CB";

$method = checkPostGet('method','');
$pages = checkPostGet('page','');

$namafile = checkPostGet('namafile','');

$aset =checkPostGet('aset','');
$unit =checkPostGet('unit','');
$sub=checkPostGet('sub','');
$jenis =checkPostGet('jenis','');
$jenisbiaya =checkPostGet('jenisbiaya','');
$nama =checkPostGet('nama','');
$tanggalmulai=tanggalsystem(checkPostGet('tanggalmulai',''));
$tanggalselesai=tanggalsystem(checkPostGet('tanggalselesai',''));

$pekerjaan=checkPostGet('pekerjaan','');
$statusbg=checkPostGet('statusbg','');
$tipebg=checkPostGet('tipebg','');

$kode = checkPostGet('kode','');
$tipebatal = checkPostGet('tipebatal','');
$htdt = checkPostGet('htdt','');
$deskripsi = checkPostGet('deskripsi','');

//DETAIL
$nmKeg = checkPostGet('nmKeg','');
$tglMul = checkPostGet('tglMul','');
$tglSmp = checkPostGet('tglSmp','');
$index = checkPostGet('index','');
$satKeg = checkPostGet('satKeg','');
$volKeg = checkPostGet('volKeg','');
$hargaKeg = checkPostGet('hargaKeg','');
$hkKeg = checkPostGet('hkKeg','');
$rupiahhkKeg = checkPostGet('rupiahhkKeg','');
$bobotKeg = checkPostGet('bobotKeg','');
$deskripsiKeg = checkPostGet('deskripsiKeg','');

//MATERIAL
$namaBarangCari = checkPostGet('namaBarangCari','');
$kegiatan = checkPostGet('kegiatan','');
$kodeproject=checkPostGet('kodeproject','');
$kodekegiatan=checkPostGet('kodekegiatan','');
$kodeBarangForm=checkPostGet('kodeBarangForm','');//buat insert
$jumlahBarangForm=checkPostGet('jumlahBarangForm','');
$hargaBarangForm=checkPostGet('hargaBarangForm','');

$jumlahmat=checkPostGet('jumlahmat','');
$hargamat=checkPostGet('hargamat','');
$kodebarang=checkPostGet('kodebarang','');











$namacr =checkPostGet('namacr','');
$unitcr =checkPostGet('unitcr','');
$kodecr = checkPostGet('kodecr','');

$kelompok =checkPostGet('kelompok','');
$nilai =checkPostGet('nilai','');

$kodebarang=checkPostGet('kodebarang','');//buat delete

$satKeg=checkPostGet('satKeg','');
$volKeg=checkPostGet('volKeg','');
$bobotKeg=checkPostGet('bobotKeg','');

$kodetender=checkPostGet('kodetender','');
$kontraktor=checkPostGet('kontraktor','');
$suppid=checkPostGet('suppid','');

$leveltender=3;

switch($method)
{
	case'getsubtipeasset':
		//List SubAsset
		$optSub="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$aset."' order by namasub";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
			$optSub.="<option value='".$bar->kodesub."'>".$bar->namasub."</option>";
		}
		echo $optSub;
	break;
	
	case'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
				{
					if($_FILES['file']['size'] <= 250000)
					{
						$newdata = array(
							'namafile'=>$filename
						);
						
						if($_SESSION['bgimage'] != array())
						{
							foreach($_SESSION['bgimage'] as $key=>$row)
							{
								if($row['namafile'] == $filename)
								{
									exit("Warning : Item ini sudah pernah diinput sebelumnya.");
								}
							}
							array_push($_SESSION['bgimage'],$newdata);
						}else{
							array_push($_SESSION['bgimage'],$newdata);
						}
						move_uploaded_file($file_tmpname,"fileupload/capexbg/$filename");
					}
					else
					{
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
	break;
	
	case'loadfiles':
		$tab="";
		$no=0;
		foreach($_SESSION['bgimage'] as $key=>$row)
		{
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:right'>".$no."</td>";
			$tab.="<td><a href='fileupload/capexbg/".$row['namafile']."' download>".substr($row['namafile'],0,30)."...</a></td>";
			if($htdt=='ht')
			{
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletefile('".$row['namafile']."')\" src='images/delete_32.png'/
				</td>";
			}
			else
			{
				$tab.="<td></td>";
			}
			$tab.="</tr>";
		}
		
		echo $tab;
	break;
	
	case 'deletefile':
		foreach($_SESSION['bgimage'] as $key=>$row)
		{
			if($row['namafile'] == $namafile)
			{
				$path = "fileupload/capexbg/".$namafile;
				unlink($path);
				unset($_SESSION['bgimage'][$key]);

				if ($kode!='') {
					$str="delete from ".$dbname.".listfileupload where namafile='".$namafile."' and notransaksi='".$kode."'";
					try{$owlPDO->exec($str); }
					catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				

			}
		}
	break;
	
	case 'batal':
		foreach($_SESSION['bgimage'] as $key=>$row)
		{
			// if($tipebatal=='insert')
			// {
				// $path = "fileupload/capexbg/".$row['namafile'];
				// unlink($path);
			// }
			unset($_SESSION['bgimage'][$key]);
		}
	break;
	
	case'getjbiaya':
		// $optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $arjb = getEnum($dbname, 'project', 'jenis_biaya');
        foreach ($arjb as $kei => $fal) 
		{
			if ((substr($unit,2,2)=='HO')&&($fal!=3))
			{
				continue;   
            }
			
			if ((substr($unit,2,2)!='HO')&&($fal==3))
			{
				continue;
            }

            if ($fal==1)
			{
				$capt="Biaya Langsung";
            }
			
            if ($fal==2)
			{
				$capt="Biaya Tidak Langsung";
            }
			
            if ($fal==3)
			{
				$capt="Operasi";
            }

			if(substr($unit,2,2)!='HO')
			{
				if($pekerjaan=='Internal')
				{
					if($fal==1)
					{
						$optjb.="<option value='" . $kei . "' selected=selected>" . $capt . "</option>";
					}
				}
				else
				{
					$optjb.="<option value='" . $kei . "' selected=selected>" . $capt . "</option>";
				}
			}
			else
			{
				if($fal==3)
				{
					$optjb.="<option value='" . $kei . "' selected=selected>" . $capt . "</option>";
				}
			}
            // if($jenisbiaya==$fal)
            // {
                // $optjb.="<option value='" . $kei . "' selected=selected>" . $capt . "</option>";
            // }
            // else
			// {
				// $optjb.="<option value='" . $kei . "'>" . $capt . "</option>";
            // }
        }
		
		echo $optjb;
    break;
	
	case'loadpersetujuan':
		$tab="";
		$countApproval = getCountApproval($tipeApp,$unit);
		
		for($i=1;$i<=$countApproval;$i++)
		{
			$optPersetujuan = "";
			$listApprove = listApprove($i,$tipeApp,$unit);
			$optSetuju = makeOption($dbname,'approval','notransaksi,karyawanid',"notransaksi='".$kode."' and level='".$i."'");
			foreach($listApprove as $key=>$val)
			{
				if($optSetuju[$kode]==$val['karyawanid'])
				{
					$optPersetujuan.="<option value='".$val['karyawanid']."' selected>".$val['nama']."</option>";
				}
				else
				{
					$optPersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
				}
			}
			
			if($htdt=='ht')
			{
				$disabaled = '';
			}
			else
			{
				$disabaled = 'disabled';
			}
			
			$tab.="<tr>
				<td>".$_SESSION['lang']['persetujuan']." ".$i."<td>
				<td>:<td>
				<td>
					<select id='persetujuan".$i."' ".$disabaled.">".$optPersetujuan."</select>
				<td>
			</tr>";
		}
		
		echo $tab;
	break;
	
	case 'insert':
		if($unit=='' || $sub=='' || $jenisbiaya=='' || $nama=='')
		{
			exit("Warning : Form belum dilengkapi. Silahkan lengkapi pengisian form");
		}
		
		$countImage = count($_SESSION['bgimage']);
		if($tipebg=='Incidental')
		{
			if($countImage<=0)
			{
				exit("Warning : File pendukung belum diupload.");
			}
		}
		
		$listpersetujuan=$_POST['persetujuan'];
		if(count($listpersetujuan) <= 0)
		{
			exit("Warning : Approval masih belum ada. Silahkan hubungi Administrator");
		}
		
		// String Kode
        $kode = 'CAPEX'.$jenis.'-'.$aset.$sub;
		
        // cari nomor terakhir
        $str="select kode from ".$dbname.".spl_capexbangunan where kode like '".$kode."%' order by substring(kode, -5) desc  limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $belakangnya=intval(substr($bar->kode,-5));
        }
        $belakangnya+=1;
        
        $belakangnya=addZero($belakangnya,10-strlen($aset.$sub));
        $kode='CAPEX'.$jenis."-".$aset.$sub.$belakangnya;
		
        $str="insert into ".$dbname.".spl_capexbangunan (kode, nama, tipe, subtipe, kodeorg,tanggalmulai,tanggalselesai,statusbg,tipebg,pekerjaan,updateby,jenis_biaya) 
			values('".$kode."','".$nama."','".$jenis."','".$sub."','".$unit."','".$tanggalmulai."','".$tanggalselesai."','".$statusbg."','".$tipebg."','".$pekerjaan."',".$_SESSION['standard']['userid'].",'".$jenisbiaya."')";
        try
		{
			$owlPDO->exec($str);
			
			## Begin Copy from template ##
			$optRab = makeOption($dbname,'vhc_5rab','pekerjaan,kode',"pekerjaan='".$aset.$sub."'");
			$koderab = $optRab[$aset.$sub];
			if($koderab!='')
			{
				$str = "select * from ".$dbname.".vhc_5rabdet where koderab='".$koderab."'";
				$res=fetchData($str);
				foreach($res as $key=>$val)
				{
					$str2 = "select * from ".$dbname.".vhc_5rabkeg where kodedet='".$val['kode']."'";
					$res2=fetchData($str2);
					if(count($res2) > 0)
					{
						foreach($res2 as $key2=>$val2)
						{
							$str3="SELECT AUTO_INCREMENT as id FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".$dbname."' AND TABLE_NAME = 'spl_capexbangunandt'";
							$res3=fetchData($str3);
							$mykodekeg = $res3[0]['id'];
							$valkodekeg = addZero(($mykodekeg + 1),8);
							
							$str3="insert into spl_capexbangunandt (kegiatan,kodeproject,deskripsikegiatan,namakegiatan,tanggalmulai,tanggalselesai,satuan,volume,hargasatuan) values ('".$valkodekeg."','".$kode."','".$val['dekripsi']."','".$val2['kegiatan']."','','','".$val2['satuan']."','".$val2['volume']."','0')";
							try
							{
								$owlPDO->exec($str3);
							}
							catch (PDOException $e) 
							{
								print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
							}
							
							$str4="select * from ".$dbname.".vhc_5rabmat where kodekeg='".$val2['kode']."'";
							$res4=fetchData($str4);
							if(count($res4) > 0)
							{
								foreach($res4 as $key4=>$val4)
								{
									$str5="insert into spl_capexbangunanmaterial (kodeproject,kodekegiatan,kodebarang,jumlah,hargasatuan,updateby) values ('".$kode."','".$valkodekeg."','".$val4['material']."','','','')";
									try
									{
										$owlPDO->exec($str5);
									}
									catch (PDOException $e) 
									{
										print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
									}
								}
							}
						}
					}
				}
			}
			## End Copy from template ##
			
			foreach($listpersetujuan as $key=>$val)
			{
				$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$key."' and kodeunit='".$unit."'";
				$res=fetchData($str);
				$tipeapp = $res[0]['tipe'];
				$departemenapp = $res[0]['departemen'];
				$tipekaryawanapp = $res[0]['tipekaryawan'];
				$jabatanapp = $res[0]['jabatan'];
				
				$str="delete from ".$dbname.".approval where notransaksi='".$kode."' and jenispersetujuan='".$jenisApp."' and level='".$key."'";
				$owlPDO->exec($str);
				
				if($tipeapp=='1'){
					if($departemenapp!=''){
						$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
							$owlPDO->exec($str);
						}
					}
					if($tipekaryawanapp!=''){
						$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
							$owlPDO->exec($str);
						}
					}
					if($jabatanapp!='0'){
						$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
							$owlPDO->exec($str);
						}
					}
				}else{
					$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$tipeApp."','".$key."','".$listpersetujuan[$key]."','9')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
					}
				}
				
				// $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$tipeApp."','".$key."','".$listpersetujuan[$key]."','9')";
				// try
				// {
					// $owlPDO->exec($str);
				// }
				// catch (PDOException $e) 
				// {
					// print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
				// }
			}
			
			if($countImage>0)
			{
				foreach($_SESSION['bgimage'] as $key=>$row)
				{
					$str="insert into ".$dbname.".listfileupload (notransaksi,namafile,status,createdby,createdtime) values ('".$kode."','".$row['namafile']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
					}
				}
			}
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}	
    break;
	
	case 'update':	

		$countImage = count($_SESSION['bgimage']);
		if($tipebg=='Incidental')
		{
			if($countImage<=0)
			{
				exit("Warning : File pendukung belum diupload.");
			}
		}

		$str="update ".$dbname.".spl_capexbangunan set nama='".$nama."',tanggalmulai='".$tanggalmulai."',tanggalselesai='".$tanggalselesai."',updateby='".$_SESSION['standard']['userid']."',subtipe='".$sub."',jenis_biaya='".$jenisbiaya."',pekerjaan='".$pekerjaan."',statusbg='".$statusbg."',tipebg='".$tipebg."' 
          where kode='".$kode."'";
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		
		$listpersetujuan=$_POST['persetujuan'];
		if(count($listpersetujuan) <= 0)
		{
			exit("Warning : Approval masih belum ada. Silahkan hubungi Administrator");
		}
		
		// $str="delete from ".$dbname.".approval where notransaksi='".$kode."'";
		// try{$owlPDO->exec($str); }
		// catch (PDOException $e) {
			// print " Gagal  !: " . $e->getMessage() . "\n"; 
			// die(); 
		// }
		
		foreach($listpersetujuan as $key=>$val)
		{
			$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$key."' and kodeunit='".$unit."'";
			$res=fetchData($str);
			$tipeapp = $res[0]['tipe'];
			$departemenapp = $res[0]['departemen'];
			$tipekaryawanapp = $res[0]['tipekaryawan'];
			$jabatanapp = $res[0]['jabatan'];
			
			$str="delete from ".$dbname.".approval where notransaksi='".$kode."' and jenispersetujuan='".$jenisApp."' and level='".$key."'";
			$owlPDO->exec($str);
			
			if($tipeapp=='1'){
				if($departemenapp!=''){
					$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
					$res=fetchdata($str);
					foreach($res as $keyx=>$valx){
						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
						$owlPDO->exec($str);
					}
				}
				if($tipekaryawanapp!=''){
					$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
					$res=fetchdata($str);
					foreach($res as $keyx=>$valx){
						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
						$owlPDO->exec($str);
					}
				}
				if($jabatanapp!='0'){
					$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
					$res=fetchdata($str);
					foreach($res as $keyx=>$valx){
						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
						$owlPDO->exec($str);
					}
				}
			}else{
				$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$tipeApp."','".$key."','".$listpersetujuan[$key]."','9')";
				try
				{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) 
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
				}
			}
			
			// $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kode."','".$tipeApp."','".$key."','".$listpersetujuan[$key]."','9')";
			// try
			// {
				// $owlPDO->exec($str);
			// }
			// catch (PDOException $e) 
			// {
				// print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			// }
		}
		
		// exit("error : ".$str);
		
		
		if($countImage>0)
		{
			foreach($_SESSION['bgimage'] as $key=>$row)
			{

				$str="delete from ".$dbname.".listfileupload where notransaksi='".$kode."' and namafile='".$row['namafile']."'";
				try{
					$owlPDO->exec($str); 
				
					$str="insert into ".$dbname.".listfileupload (notransaksi,namafile,status,createdby,createdtime) values ('".$kode."','".$row['namafile']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
					}

				}catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}				
			}
		}
    break;
	
	case'loadData':
        $where = "";
        if ($kodecr != '') 
		{
            $where.=" and kode like '%" . $kodecr . "%' ";
        }
        if ($namacr != '') 
		{
            $where.=" and nama like '%" . $namacr . "%' ";
        }
        if ($unitcr != '') 
		{
            $where.=" and kodeorg like '%" . $unitcr . "%' ";
        }
        
		$limit=20;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$countApp = getCountApproval($jenisApp,'');
		
		$str="select a.*,b.namakaryawan from ".$dbname.".spl_capexbangunan a left join ".$dbname.".datakaryawan b on a.updateby=b.karyawanid where 1=1 ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0)
		{
			$tab.="<tr class=rowcontent>
				<td colspan=9>".$_SESSION['lang']['dataempty']."</td>
			</tr>";
        }
        else
		{
            $tab="";
			$no=(($page*$limit));
			$str="select * from ".$dbname.".spl_capexbangunan where 1=1 ".$where." order by substring(kode, -7) desc limit ".$offset.",".$limit."";
            // exit("error : ".$str);
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch())
			{
				$kdAst=substr($bar['kode'],8,2);
				$iSubAst="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$kdAst."' ";
				$resx=$owlPDO->query($iSubAst) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$dSubAst=$resx->fetch();
				$qwe=substr($bar['kode'],8,2);
				$asd=substr($qwe,-1);
				
				if($asd=='0')
				{
					$aset=substr($qwe,0,2);
				}
				else
				{
					$aset=$qwe;
				}
				
				$optNmKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
				$optKontraktor = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['kontraktor']."'");
				$optNmSubAsset = makeOption($dbname,'sdm_5subtipeasset','kodesub,namasub',"kodesub='".$bar['subtipe']."' and kodetipe='BG'");
				
				$no+=1;
				$tab.="<tr class=rowcontent>
					<td style='vertical-align:top'>".$bar['kode']."</td>
					<td style='text-align:center;vertical-align:top'>".$bar['kodeorg']."</td>
					<td style='vertical-align:top'>".$optNmSubAsset[$bar['subtipe']]."</td>
					<td style='text-align:center;vertical-align:top'>".$bar['tipe']."</td>
					<td style='vertical-align:top'>".$bar['nama']."</td>
					<td align=center style='vertical-align:top'>".tanggalnormal($bar['tanggalmulai'])."</td>
					<td align=center style='vertical-align:top'>".tanggalnormal($bar['tanggalselesai'])."</td>
					<td style='vertical-align:top'>
						<table>";
						$str2="select * from ".$dbname.".listfileupload where notransaksi='".$bar['kode']."'";
						$res2=fetchData($str2);
						$no2=0;
						foreach($res2 as $key2=>$val2)
						{
							$no2++;
							$tab.="<tr>
								<td>".$no2.".</td>
								<td>
									<a href='fileupload/capexbg/".$val2['namafile']."' download>".substr($val2['namafile'],0,30)."...</a>
								</td>
							</tr>";
						}
					$tab.="</table>
					</td>";
					if($bar['kontraktor']!='')
					{
						$tab.="<td style='vertical-align:top;cursor:pointer' title='Click to Preview' onclick=\"appshowcapex('".$bar['kode']."','".$bar['kontraktor']."',event);\">".$optKontraktor[$bar['kontraktor']]."</td>";
					}
					else{
						$tab.="<td style='vertical-align:top'></td>";
					}
					$tab.="<td style='vertical-align:top'>".$optNmKary[$bar['updateby']]."</td>";
					
					##BEGIN APPROVAL##
					for($i=1;$i<=$countApp;$i++){
						$strx="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$i."' and kodeunit='".$bar['kodeorg']."'";
						$resx=fetchData($strx);
						$tipeapp = $resx[0]['tipe'];
						$departemenapp = $resx[0]['departemen'];
						$tipekaryawanapp = $resx[0]['tipekaryawan'];
						$jabatanapp = $resx[0]['jabatan'];
						
						$arrDetail = detailApprove($i,$bar['kode'],$jenisApp);
						if($tipeapp=='1' && $arrDetail['status']!=''){
							if($arrDetail['status']!='1'){
								if($departemenapp!=''){
									$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
									$arrDetail['nama'] = $opttipe[$departemenapp];
								}
								
								if($tipekaryawanapp!=''){
									$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
									$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
								}
								
								if($jabatanapp!='0'){
									$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
									$arrDetail['nama'] = $opttipe[$jabatanapp];
								}
							}
						}
						$tab.="<td align=center style='vertical-align:top'>".$arrDetail['nama']." ".(($arrDetail['status']=='9'||$arrDetail['status']=='')?"":"(".$arrDetail['namastatus'].")")."</td>";
					}
					##END APPROVAL##
					
					
					$tab.="<td align=right style='vertical-align:top;text-align:center'>";
					
					if($bar['posting']==0 and $bar['updateby']==$_SESSION['standard']['userid'])
					{
						$tab.="<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$aset."','".$bar['kodeorg']."','".$bar['subtipe']."','".$bar['tipe']."','".$bar['jenis_biaya']."','".$bar['nama']."','".tanggalnormal($bar['tanggalmulai'])."','".tanggalnormal($bar['tanggalselesai'])."','".$bar['pekerjaan']."','".$bar['statusbg']."','".$bar['tipebg']."','".$bar['kode']."','update','ht');\">
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapus('".$bar['kode']."');\">
						<img src=images/nxbtn.png class=resicon  title='Detail' onclick=\"detailForm('".$aset."','".$bar['kodeorg']."','".$bar['subtipe']."','".$bar['tipe']."','".$bar['jenis_biaya']."','".$bar['nama']."','".tanggalnormal($bar['tanggalmulai'])."','".tanggalnormal($bar['tanggalselesai'])."','".$bar['pekerjaan']."','".$bar['statusbg']."','".$bar['tipebg']."','".$bar['kode']."','detail','dt');\">
						<img src=images/skyblue/posting.png class=resicon  title='Ajukan Data' onclick=\"postIni('".$bar['kode']."');\">&nbsp;";
					}
					else
					{
						if($bar['posting']==1)
						{
							$tab.="<img src=images/skyblue/posted.png class=resicon>";
						}
						else 
						{
							$tab."<img src=images/skyblue/posting.png class=resicon>";
                        }
					}
					$tab.="</td>
                    <td align=center style='vertical-align:top'>
						<img onclick=\"printpdf('".$bar['kode']."',event);\" title=\"Print\" class=\"resicon\" src=\"images/pdf.jpg\">
						<img onclick=excelMaterial(event,'".$bar1->kode."') src=images/excel.jpg class=resicon title='MS.Excel Material'>
                        <img onclick=timeFrame(event,'".$bar1->kode."') src=images/excel.jpg class=resicon title='MS.Excel Time Frame Project'>
					</td>
				</tr>";
            }
			
            $totrows=ceil($jlhbrs/$limit);
			if($totrows==0)
			{
				$totrows=1;
			}
			
			$isiRow='';
			for($er=1;$er<=$totrows;$er++)
			{
				$sel = ($page==$er-1)? 'selected': '';
				$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
			}
			
			$frompage = (($page*$limit)+1);
			if((($page+1)*$limit) > $jlhbrs)
			{
				$topage = $jlhbrs;
			}
			else
			{
				$topage = (($page+1)*$limit);
			}
			$tab.="</tr>
			<tr>
				<td colspan=11 align=center>
					".$frompage." to ".$topage." Of ".  $jlhbrs."
				</td>
			</tr>
			<tr>
				<td colspan=11 align=center>";
			
			if($page=='0')
			{
				$tab.="";
			}
			else
			{
				$tab.="<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
			}
			
			$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
			
			if(($page+1) == $totrows)
			{
				$tab.="";
			}
			else
			{
				$tab.="<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
			}
			$tab.="</td></tr>";
        }
        echo $tab;
	break;
	
	case 'delete':
        $str="delete from ".$dbname.".spl_capexbangunan where kode='".$kode."'";
        try
		{
			$owlPDO->exec($str);
			$str="delete from ".$dbname.".approval where notransaksi='".$kode."'";
			try
			{
				$owlPDO->exec($str);

				$str="select * from listfileupload where notransaksi='".$kode."'";
				$res=fetchData($str);
				foreach($res as $key=>$val)
				{
					$path = "fileupload/capexbg/".$val['namafile'];
					unlink($path);
				}
				
				$str="delete from ".$dbname.".listfileupload where notransaksi='".$kode."'";
				try
				{
					$owlPDO->exec($str);
					
					$str="delete from ".$dbname.".spl_capexbangunandt where kodeproject='".$kode."'";
					try
					{
						$owlPDO->exec($str);
						
						$str="delete from ".$dbname.".spl_capexbangunanmaterial where kodeproject='".$kode."'";
						try
						{
							$owlPDO->exec($str);
							
							$str="delete from ".$dbname.".approval where notransaksi='".$kode."' and jenispersetujuan='CB'";
							try
							{
								$owlPDO->exec($str);
							}
							catch (PDOException $e)
							{
								print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
							}
						}
						catch (PDOException $e)
						{
							print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
						}
					}
					catch (PDOException $e)
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
					}
				}
				catch (PDOException $e)
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
				}
			}
			catch (PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
		catch (PDOException $e)
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
    break;
	
	case 'postingData':
		$str="update ".$dbname.".spl_capexbangunan set updateby='".$_SESSION['standard']['userid']."',posting='1' where kode='".$kode."'";
		try
		{
			$owlPDO->exec($str); 
			
			$str="update ".$dbname.".approval set status='0' where notransaksi='".$kode."'";
			try
			{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e) 
			{
				print " Gagal  Ajukan!: " . $e->getMessage() . "\n";  
			}
		}
		catch (PDOException $e) 
		{
			print " Gagal  Ajukan!: " . $e->getMessage() . "\n";  
		}
	break;
	
	case'fillField':
		$_SESSION['bgimage'] = array();
		$str="select * from listfileupload where notransaksi='".$kode."'";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$newdata = array('namafile'=>$val['namafile']);
			array_push($_SESSION['bgimage'],$newdata);
		}
	break;
	
	case'detail':   
		$sDet="select distinct * from ".$dbname.".spl_capexbangunandt  where kodeproject='".$kode."' order by deskripsikegiatan asc";
		$resx=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC); 
		$numrows=owlBaris($resx);
		$frmdt="";
		if($numrows==0)
		{
			$frmdt.="<tr class=rowcontent><td colspan=12 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td></tr>";
		}
		else
		{
			while($rDet=  $resx->fetch())
			{
			$frmdt.="<tr class=rowcontent><td>".$rDet['kodeproject']."</td>";
			$frmdt.="<td>".$rDet['deskripsikegiatan']."</td>";
			$frmdt.="<td>".$rDet['namakegiatan']."</td>";
			$frmdt.="<td>".$rDet['satuan']."</td>";
			$frmdt.="<td align=right>".$rDet['volume']."</td>";
			$frmdt.="<td align=right>".$rDet['hargasatuan']."</td>";
			$frmdt.="<td align=right>".$rDet['hk']."</td>";
			$frmdt.="<td align=right>".$rDet['rphk']."</td>";
			$frmdt.="<td align=right>".$rDet['bobot']."</td>";
			$frmdt.="<td>".tanggalnormal($rDet['tanggalmulai'])."</td>";
			$frmdt.="<td>".tanggalnormal($rDet['tanggalselesai'])."</td>";
			$frmdt.="<td>
					<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$rDet['kegiatan']."','".$rDet['kodeproject']."','".$_SESSION['lang']['find']."',event)>
					<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editDet('".tanggalnormal($rDet['tanggalmulai'])."','".tanggalnormal($rDet['tanggalselesai'])."','updatedet','".$rDet['deskripsikegiatan']."','".$rDet['kodeproject']."','".$rDet['kegiatan']."','".$rDet['namakegiatan']."','".$rDet['satuan']."','".$rDet['volume']."','".$rDet['hargasatuan']."','".$rDet['hk']."','".$rDet['rphk']."','".$rDet['bobot']."');\">
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapusData('".$rDet['kegiatan']."');\">
					</td></tr>";
			}
		}
    echo $frmdt;
    break;
	
	case'cekdeskripsi':
		$tab="";
		$optRab = makeOption($dbname,'vhc_5rab','pekerjaan,kode',"pekerjaan='".$sub."' and status='1'");
		
		$str="select * from ".$dbname.".vhc_5rabdet where status='1' and koderab='".$optRab[$sub]."' order by nourut asc";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$tab.="<option id='".$val['dekripsi']."' value='".$val['dekripsi']."'>".$val['dekripsi']."</option>";
		}
		echo $tab;
	break;
	
	case'insertdt':
        if($deskripsiKeg=='')
		{
			exit("Warning : Deskripsi kegiatan harus diisi.");
		}
		
		if($nmKeg=='')
		{
			exit("Warning : Nama kegiatan harus diisi.");
		}

        $str="SELECT datediff('".$tglSmp."', '".$tglMul."') as selisih";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_NUM);      
        $data = $res->fetch();
		if($data['selisih']<0)
        {
			exit("Warning : Tanggal Selesai Lebih Besar dari Tanggal Mulai");
        }
		
		$str="insert into ".$dbname.".spl_capexbangunandt (kodeproject, deskripsikegiatan, namakegiatan, tanggalmulai, tanggalselesai,satuan,volume,hargasatuan,hk,rphk,bobot) 
             values ('".$kode."', '".$deskripsiKeg."','".$nmKeg."','".tanggalsystem($tglMul)."','".tanggalsystem($tglSmp)."','".$satKeg."','".$volKeg."','".$hargaKeg."','".$hkKeg."','".$rupiahhkKeg."','".$bobotKeg."')";
		 try
		 {
			 $owlPDO->exec($str); 
		 }
		 catch (PDOException $e) 
		 {
			 print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		 }
    break;
	
	case'updatedet':
		if($deskripsiKeg=='')
		{
			exit("Warning : Deskripsi kegiatan harus diisi.");
		}
		
		if($nmKeg=='')
		{
			exit("Warning : Nama kegiatan harus diisi.");
		}

        $str="SELECT datediff('".$tglSmp."', '".$tglMul."') as selisih";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_NUM);      
        $data = $res->fetch();
		if($data['selisih']<0)
        {
			exit("Warning : Tanggal Selesai Lebih Besar dari Tanggal Mulai");
        }
		
		$str="update ".$dbname.".spl_capexbangunandt set deskripsikegiatan='".$deskripsiKeg."',namakegiatan='".$nmKeg."',
              tanggalmulai='".tanggalsystem($tglMul)."', tanggalselesai='".tanggalsystem($tglSmp)."',
              satuan='".$satKeg."',volume='".$volKeg."',hargasatuan='".$hargaKeg."',hk='".$hkKeg."',rphk='".$rupiahhkKeg."',bobot='".$bobotKeg."' where kegiatan='".$index."'";
		try
		{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
    break;
	
	case'hpsDetail':
		$str="delete from ".$dbname.".spl_capexbangunandt where kegiatan='".$index."'";
		try
		{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
    break;
	
	case'getListBarang':
		echo"<fieldset>
			<legend>".$_SESSION['lang']['form']."</legend>
            <fieldset  style='float:left;'>
			<legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
			<table cellspacing=1 border=0 class=data>
				<tr>
					<td colspan=2>".$_SESSION['lang']['namabarang']."</td>
					<td colspan=5>:<input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'><button class=mybutton onclick=cariListBarang('".$kegiatan."','".$kodeproject."')>".$_SESSION['lang']['find']."</button>
					<td>
				</tr>
			</table>
			
			<table id=listCariBarang >
				<thead>
                <tr class=rowheader>
					<td>No</td>
                    <td>".$_SESSION['lang']['kodebarang']."</td>
                    <td>".$_SESSION['lang']['namabarang']."</td>
                    <td>".$_SESSION['lang']['satuan']."</td>
                    <td>".$_SESSION['lang']['hargasatuan']."</td>
				</tr>
			</thead>";
			
			if($namaBarangCari==''){}
			else
			{
				$i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
                $resw=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
                $resw->setFetchMode(PDO::FETCH_ASSOC);
                while ($d=$resw->fetch())
				{
					$no+=1;
					$nmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',"kodebarang='".$d['kodebarang']."'");
					$satBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan',"kodebarang='".$d['kodebarang']."'");
					$hargaBrg=makeOption($dbname, 'log_5saldobulanan', 'kodebarang,hargarata',"kodebarang='".$d['kodebarang']."' and kodegudang like '%".$unit."%'");
					echo"<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."','".$hargaBrg[$d['kodebarang']]."');\">
						<td>".$no."</td>
                        <td>".$d['kodebarang']."</td>
                        <td>".$nmBrg[$d['kodebarang']]."</td>
                        <td>".$satBrg[$d['kodebarang']]."</td>
                        <td style='text-align:right'>".hidezerodecimal($hargaBrg[$d['kodebarang']],2)."</td>
					</tr>";
				}
			}
			echo"</table>
			</fieldset>
			<fieldset>
            <legend>".$_SESSION['lang']['form']."</legend>
			<table cellspacing=1 border=0>
				<tr>
					<td>".$_SESSION['lang']['project']."</td>
                    <td>:</td>
                    <td><input type=text id=kodeproject disabled value='".$kodeproject."' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;'></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodekegiatan']."</td>
                    <td>:</td>
                    <td><input type=text id=kodekegiatan disabled value='".$kegiatan."' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodebarang']."</td>
                    <td>:</td>
                    <td>
						<input type=text id=kodeBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namabarang']."</td>
                    <td>:</td>
                    <td><input type=text id=namaBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['satuan']."</td>
                    <td>:</td>
                    <td><input type=text id=satuanBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jumlah']."</td>
                    <td>:</td>
                    <td><input type=text id=jumlahBarangForm class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['hargasatuan']."</td>
                    <td>:</td>
                    <td><input type=text id=hargaBarangForm class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
				</tr>
				<tr>
					<td style='text-align:center'>
						<input type='hidden' id='methodmat' value='saveFormBarang'>
						<button class=mybutton onclick=saveFormBarang('".$kegiatan."','".$kodeproject."','".$_SESSION['lang']['find']."',event)>".$_SESSION['lang']['save']."</button>
                        <button class=mybutton onclick=cancelFormBarang('".$kegiatan."','".$kodeproject."','".$_SESSION['lang']['find']."',event)>".$_SESSION['lang']['cancel']."</button>
                        <button class=mybutton onclick=closeDialog()>".$_SESSION['lang']['selesai']."</button>
					</td>
				</tr>
			</table>
			</fieldset>	
            </fieldset>
			<fieldset>
			<legend>".$_SESSION['lang']['datatersimpan']."</legend>
            <table cellspacing=1 border=0 class=data>
				<thead>
                <tr class=rowheader>
					<td>No</td>
                    <td>".$_SESSION['lang']['project']."</td>
                    <td>".$_SESSION['lang']['namakegiatan']."</td>
                    <td>".$_SESSION['lang']['kodebarang']."</td>
                    <td>".$_SESSION['lang']['namabarang']."</td>
                    <td>".$_SESSION['lang']['jumlah']."</td>
                    <td>".$_SESSION['lang']['satuan']."</td>
                    <td>".$_SESSION['lang']['hargasatuan']."</td>
                    <td>".$_SESSION['lang']['dibuat']."</td>
                    <td>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				</tbody>";
			
			$i="select * from ".$dbname.".spl_capexbangunanmaterial where kodekegiatan='".$kegiatan."'";
            $res1=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $noData=0;
            while ($d=$res1->fetch())
            {
				$optNmKegBrg=makeOption($dbname, 'spl_capexbangunandt', 'kegiatan,namakegiatan',"kegiatan='".$d['kodekegiatan']."'");
				$nmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',"kodebarang='".$d['kodebarang']."'");
				$satBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan',"kodebarang='".$d['kodebarang']."'");
				$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$d['updateby']."'");
				$noData+=1;
				echo"<tr class=rowcontent>
					<td>".$noData."</td>
                    <td>".$d['kodeproject']."</td>
                    <td>".$optNmKegBrg[$d['kodekegiatan']]."</td>
                    <td>".$d['kodebarang']."</td>
                    <td>".$nmBrg[$d['kodebarang']]."</td>
                    <td align=right>".$d['jumlah']."</td>
                    <td>".$satBrg[$d['kodebarang']]."</td>
                    <td align=right>".$d['hargasatuan']."</td>
                    <td>".$nmKar[$d['updateby']]."</td>
					<td>
						<img src=images/application/application_edit.png class=resicon  caption='edit' onclick=\"editMaterial('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."','".$d['jumlah']."','".$d['hargasatuan']."');\">
						<img src=images/application/application_delete.png class=resicon  caption='Delete'  onclick=\"delMaterial('".$d['kodeproject']."','".$d['kodekegiatan']."','".$d['kodebarang']."');\">
					</td>
				</tr>";
            }
            echo "</table>
		</fieldset>";
	break;
	
	case'saveFormBarang':
		$i="INSERT INTO ".$dbname.".`spl_capexbangunanmaterial` (`kodeproject`, `kodekegiatan`, `kodebarang`, `jumlah`, `hargasatuan`, `updateby`) values('".$kodeproject."','".$kodekegiatan."','".$kodeBarangForm."','".$jumlahBarangForm."','".$hargaBarangForm."','".$_SESSION['standard']['userid']."')";
		try{$owlPDO->exec($i); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    break;	
	
	case'editFormBarang':
		$str="update ".$dbname.".spl_capexbangunanmaterial set jumlah='".$jumlahBarangForm."',hargasatuan='".$hargaBarangForm."',updateby='".$_SESSION['standard']['userid']."' where kodeproject='".$kodeproject."' and kodekegiatan='".$kodekegiatan."' and kodebarang='".$kodeBarangForm."'";
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	case 'deleteMaterial':
		$i="DELETE FROM ".$dbname.".`spl_capexbangunanmaterial` WHERE `kodeproject` = '".$kodeproject."' AND `kodekegiatan` = '".$kegiatan."' AND `kodebarang`= '".$kodebarang."'";
        try{$owlPDO->exec($i); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    break;	
	
	case 'printpdf':
		$str="select * from ".$dbname.".spl_capexbangunan where kode='".$kode."'";
		$res=fetchData($str);
		$kdorg = $res[0]['kodeorg'];
		$updateby = $res[0]['updateby'];
		$kode = $res[0]['kode'];
		$nama = $res[0]['nama'];
		$tanggalmulai = $res[0]['tanggalmulai'];
		$tanggalselesai = $res[0]['tanggalselesai'];
		class PDF extends FPDF
		{
			function Header()
			{
				global $conn;
				global $dbname;
				global $owlPDO;
				global $res;
				global $kdorg;
				global $updateby;
				global $kode;
				global $nama;
				global $tanggalmulai;
				global $tanggalselesai;

				$arrHead = setheadreport($kdorg);
				$path=$arrHead['logo'];
				$this->Image($path,5,5,0,22);	
				$this->SetFont('Arial','B',10);
				$this->SetFillColor(255,255,255);	
				$this->SetX(27);   
				$this->Cell(60,5,$arrHead['nama'],0,1,'L');	 
				$this->SetX(27); 		
				$this->Cell(60,5,$arrHead['alamat'],0,1,'L');	
				$this->SetX(27); 			
				$this->Cell(60,5,"Tel: ".$arrHead['telepon'],0,1,'L');	
				$this->Ln();
				
				$optNmorg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kdorg."'");
				$optNmKary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$updateby."'");
				
				$this->Line(10,28,200,28);	
				
				$this->SetFont('Arial','',9); 
				$this->Cell(30,4,$_SESSION['lang']['unitkerja'],0,0,'L');
				$this->Cell(40,4,": ".$kdorg." [".$optNmorg[$kdorg]."]",0,1,'L');
				$this->Cell(30,4,$_SESSION['lang']['kode'],0,0,'L');
				$this->Cell(40,4,": ".$kode,0,1,'L');
				$this->Cell(30,4,$_SESSION['lang']['nama'],0,0,'L'); 
				$this->Cell(40,4,": ".$nama,0,1,'L');
				$this->Cell(30,4,$_SESSION['lang']['tanggalmulai'],0,0,'L');
				$this->Cell(40,4,": ".tanggalnormal($tanggalmulai),0,1,'L');
				$this->Cell(30,4,$_SESSION['lang']['tanggalsampai'],0,0,'L');
				$this->Cell(40,4,": ".tanggalnormal($tanggalselesai),0,1,'L');

				$this->Cell(30,4,$_SESSION['lang']['updateby'],0,0,'L');
				$this->Cell(40,4,": ".$optNmKary[$updateby],0,1,'L');
			}
		}
		
		$pdf=new PDF('P','mm','A4');
		$pdf->AddPage();
		
		$pdf->SetFont('Arial','B',7);	
		$pdf->SetFillColor(220,220,220);
		$pdf->Cell(8,5,'No',1,0,'L',1);
		$pdf->Cell(20,5,"Kode ".$_SESSION['lang']['kegiatan'],1,0,'C',1);
		$pdf->Cell(60,5,$_SESSION['lang']['deskripsi'],1,0,'C',1);
		$pdf->Cell(60,5,$_SESSION['lang']['namakegiatan'],1,0,'C',1);
		$pdf->Cell(20,5,$_SESSION['lang']['dari'],1,0,'C',1);
		$pdf->Cell(20,5,$_SESSION['lang']['sampai'],1,1,'C',1);

		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);

		$str="select * from ".$dbname.".spl_capexbangunandt where kodeproject='".$res[0]['kode']."'";
		$resw=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$resw->setFetchMode(PDO::FETCH_ASSOC);                 
		$no=0;
		while($res=$resw->fetch())
		{
			$awalY=$pdf->GetY();
			$pdf->SetX(1000);
			$pdf->MultiCell(60, 5, $res['deskripsikegiatan'], '0', 'L');
			$akhirYakun=$pdf->GetY();
			$pdf->SetXY(1000,$awalY);
			$pdf->MultiCell(60, 5, $res['namakegiatan'], '0', 'L');
			$akhirYketerangan=$pdf->GetY();
			$akhirY = max($akhirYketerangan,$akhirYakun);
			$height2=$akhirY-$awalY;
			$pdf->SetY($awalY);
			
			$no+=1;
			
			$pdf->Cell(8,$height2,'',1,0,'L',1);
			$pdf->Cell(20,$height2,'',1,0,'L',1);
			$pdf->Cell(60,$height2,'',1,0,'L',1);
			$pdf->Cell(60,$height2,'',1,0,'L',1);
			$pdf->Cell(20,$height2,'',1,0,'C',1);
			$pdf->Cell(20,$height2,'',1,1,'C',1);

			$pdf->SetY($awalY);
			$pdf->Cell(8,$height2,$no,1,0,'L',1);
			$pdf->Cell(20,$height2,$res['kegiatan'],1,0,'L',1);
			$pdf->MultiCell(60,5,$res['deskripsikegiatan'],0,'L');
			$pdf->SetXY($pdf->GetX()+88, $awalY);
			$pdf->MultiCell(60,5,$res['namakegiatan'],0,'L');
			$pdf->SetXY($pdf->GetX()+148, $awalY);
			$pdf->Cell(20,$height2,tanggalnormal($res['tanggalmulai']),1,0,'C',1);
			$pdf->Cell(20,$height2,tanggalnormal($res['tanggalselesai']),1,1,'C',1);
			$pdf->__currentY=$pdf->GetY();
			
			if($pdf->__currentY>250){
				$pdf->AddPage();
				$pdf->SetY(55);
			}
		}
		$pdf->Output();
	break;
	
	case'appeditcapex':
		$tab="";
		
		$str="select * from ".$dbname.".spl_capexbangunan where kode='".$kode."'";
		$res=fetchData($str);
		
		$optSubAsset = makeOption($dbname,"sdm_5subtipeasset",'kodesub,namasub',"kodesub='".$res[0]['subtipe']."' and kodetipe='BG'");
		
		$tab="<fieldset>
		<table cellpadding=1 cellspacing=0>
			<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td>".$res[0]['kode']."</td>
				
				<td style='padding-left:15px'>".$_SESSION['lang']['nama']."</td>
				<td>:</td>
				<td>".$res[0]['nama']."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>".$res[0]['kodeorg']."</td>
				
				<td style='padding-left:15px'>".$_SESSION['lang']['tanggalmulai']."</td>
				<td>:</td>
				<td>".tanggalnormal($res[0]['tanggalmulai'])."</td>
			</tr>
			<tr>
				<td>Sub Asset</td>
				<td>:</td>
				<td>".$optSubAsset[$res[0]['subtipe']]."</td>
				
				<td style='padding-left:15px'>".$_SESSION['lang']['tanggalsampai']."</td>
				<td>:</td>
				<td>".tanggalnormal($res[0]['tanggalselesai'])."</td>
			</tr>
		</table>";
		
		$tab.="<table cellpadding=1 cellspacing=0>
			<tr style='font-weight:bold;text-align:center'>
				<td style='border:1px solid grey'>NO</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>URAIAN PEKERJAAN</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>VOLUME</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>SATUAN</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>HARGA SATUAN (Rp)</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>HK</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>Rupiah HK</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>Material</td>
			</tr>";
			
		$str="select distinct(deskripsikegiatan) as deskripsikegiatan from ".$dbname.".spl_capexbangunandt where kodeproject='".$kode."'";
		$res=fetchData($str);
		$no=0;
		foreach($res as $key=>$val)
		{
			$no++;
			
			$tab.="<tr style='font-weight:bold'>
				<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center'>".romawi($no)."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'>".$val['deskripsikegiatan']."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
			</tr>";
			
			$str2="select * from ".$dbname.".spl_capexbangunandt where kodeproject='".$kode."' and deskripsikegiatan='".$val['deskripsikegiatan']."'";
			$res2=fetchData($str2);
			$nodet=0;
			foreach($res2 as $key2=>$val2)
			{
				$nodet++;
				
				$tab.="<tr id='trdet_".$val2['kegiatan']."'>
					<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center;vertical-align:top'>".$nodet."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>".$val2['namakegiatan']."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:right;vertical-align:top'>".number_format($val2['volume'])."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>".$val2['satuan']."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>
						<input type=text id='hargasatuan_".$val2['kegiatan']."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style='width:100px;' maxlength=100 value='".$val2['hargasatuan']."'>
					</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>
						<input type=text id='hk_".$val2['kegiatan']."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style='width:100px;' maxlength=100 value='".$val2['hk']."'>
					</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>
						<input type=text id='rupiahhk_".$val2['kegiatan']."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style='width:100px;' maxlength=100 value='".$val2['rphk']."'>
					</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;width:30px;'>
						<img src=images/save.png class=resicon  title='Save' onclick=\"simpanappcapex('".$val2['kegiatan']."');\">
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapusappcapex('".$val2['kegiatan']."','".$val2['namakegiatan']."');\">
					</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>";
					
					$str3="select * from ".$dbname.".spl_capexbangunanmaterial where kodeproject='".$kode."' and kodekegiatan='".$val2['kegiatan']."'";
					$res3=fetchData($str3);
					if(count($res3)>0)
					{
						$tab.="<table cellpadding=1 cellspacing=0>
							<tr style='font-weight:bold;text-align:center'>
								<td style='border:1px solid grey'>NO</td>
								<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>".$_SESSION['lang']['nama']."</td>
								<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>".$_SESSION['lang']['jumlah']."</td>
								<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>".$_SESSION['lang']['hargasatuan']."</td>
								<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;width:30px;'></td>
							</tr>";
						$no3=0;
						foreach($res3 as $key3=>$val3)
						{		
							$optNmBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val3['kodebarang']."'");
							$no3++;
							$tab.="<tr id='trmat_".$val3['kodekegiatan']."_".$val3['kodebarang']."'>
								<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center;vertical-align:top'>".$no3."</td>
								<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>".$optNmBarang[$val3['kodebarang']]."</td>
								<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>
									<input type=text id='jumlahmat_".$val3['kodekegiatan']."_".$val3['kodebarang']."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style='width:50px;' maxlength=100 value='".$val3['jumlah']."'>
								</td>
								<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>
									<input type=text id='hargamat_".$val3['kodekegiatan']."_".$val3['kodebarang']."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style='width:100px;' maxlength=100 value='".$val3['hargasatuan']."'>
								</td>
								<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;width:30px'>
									<img src=images/save.png class=resicon  title='Save' onclick=\"saveappmat('".$val3['kodekegiatan']."','".$val3['kodebarang']."');\">
									<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapusappmat('".$val3['kodekegiatan']."','".$val3['kodebarang']."','".$optNmBarang[$val3['kodebarang']]."');\">
								</td>
							</tr>";
						}
						$tab.="</table>";
					}
					$tab.="</td>
				</tr>";
			}
			
			$tab.="<tr>
				<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'>&nbsp;</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
			</tr>";
		}
			
		$tab.="</fieldset>";
		
		echo $tab;
	break;
	
	case'addtendercapex':
		$tab="";
		
		$str="select status from ".$dbname.".approval where jenispersetujuan='".$tipeApp."' and notransaksi='".$kode."' and level='".($leveltender-1)."'";
		$res=fetchdata($str);
		$statussblm=$res[0]['status'];
		if ($statussblm==0) {
			$tab.="<div id=test style=display:block>
			<fieldset>
				<legend><input align=center class=myinputtext disabled type=text readonly=readonly value=".$kode." style=\"min-width:175px;\"  /></legend>
				<table cellspacing=1 border=0>
					<tr>
						<td>Note : Harap menunggu persetujuan sebelumnya.</td>
					</tr>
				</table> 
			</fieldset></div>";
		}else{
			$str="select a.supplierid, b.namasupplier from ".$dbname.".log_5supkelompok a 
				left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.tipe='KONTRAKTOR'";
			$res=fetchData($str);
			foreach($res as $key=>$val)
			{
				$optKontraktor.="<option value='".$val['supplierid']."'>".$val['namasupplier']."</option>";
			}
			
			$tab="<fieldset>
			<table cellpadding=1 cellspacing=0>
				<tr>
					<td>".$_SESSION['lang']['kontraktor']."</td>
					<td>:</td>
					<td>
						<select id=kontraktor style='width:200px;'>".$optKontraktor."</select>
						<input type=hidden id='kodetender' value='".$kode."'>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=simpantender()>".$_SESSION['lang']['tambah']."</button>
					</td>
				</tr>
			</table><p>";
			
			$tab.="<table class=sortable border=0 cellspacing=1 width=100%>
				<thead> 
				<tr>
					<td style='text-align:center'>No</td>
					<td style='text-align:center'>".$_SESSION['lang']['kontraktor']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['uploadfile']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['status']."</td>
					<td style='text-align:center;display:none'>".$_SESSION['lang']['pemenang']."</td>
				</tr>
				</thead>";
				
			$tab.="<tbody id=containertender>				
				</tbody>
				<tr style='display:none'>
					<td colspan=5 style='text-align:center'>
					<textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=15 rows=2 placeholder='tulis komentar'></textarea><br>
					<button class=mybutton onclick=checktender()>".$_SESSION['lang']['menyetujui']."</button>
					<button class=mybutton onclick=closeDialog()>".$_SESSION['lang']['cancel']."</button></td>
				</tr>
			</table>
			</fieldset>";
		}
		
		echo $tab;
	break;
	
	case'addtendercapex2':
		$tab="";
		
		$str="select status from ".$dbname.".approval where jenispersetujuan='".$tipeApp."' and notransaksi='".$kode."' and level='".($leveltender-1)."'";
		$res=fetchdata($str);
		$statussblm=$res[0]['status'];
		if ($statussblm==0) {
			$tab.="<div id=test style=display:block>
			<fieldset>
				<legend><input align=center class=myinputtext disabled type=text readonly=readonly value=".$kode." style=\"min-width:175px;\"  /></legend>
				<table cellspacing=1 border=0>
					<tr>
						<td>Note : Harap menunggu persetujuan sebelumnya.</td>
					</tr>
				</table> 
			</fieldset></div>";
		}else{
			$str="select a.supplierid, b.namasupplier from ".$dbname.".log_5supkelompok a 
				left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.tipe='KONTRAKTOR'";
			$res=fetchData($str);
			foreach($res as $key=>$val)
			{
				$optKontraktor.="<option value='".$val['supplierid']."'>".$val['namasupplier']."</option>";
			}
			
			$tab="<fieldset>
			<table cellpadding=1 cellspacing=0 style='display:none'>
				<tr>
					<td>".$_SESSION['lang']['kontraktor']."</td>
					<td>:</td>
					<td>
						<select id=kontraktor style='width:200px;'>".$optKontraktor."</select>
						<input type=hidden id='kodetender' value='".$kode."'>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=\"simpantender()\">".$_SESSION['lang']['tambah']."</button>
					</td>
				</tr>
			</table><p>";
			
			$tab.="<table class=sortable border=0 cellspacing=1 width=100%>
				<thead> 
				<tr>
					<td style='text-align:center'>No</td>
					<td style='text-align:center'>".$_SESSION['lang']['kontraktor']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['uploadfile']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['status']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['pemenang']."</td>
				</tr>
				</thead>";
				
			$tab.="<tbody id=containertender>				
				</tbody>
				<tr>
					<td colspan=5 style='text-align:center'>
					<textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=15 rows=2 placeholder='tulis komentar'></textarea><br>
					<button class=mybutton onclick=\"checktender('".($leveltender+1)."')\">".$_SESSION['lang']['menyetujui']."</button>
					<button class=mybutton onclick=closeDialog()>".$_SESSION['lang']['cancel']."</button></td>
				</tr>
			</table>
			</fieldset>";
		}
		
		echo $tab;
	break;
	
	case'loadtender':
		$tab="";
		$str="select a.kode,a.supplierid,b.namasupplier,a.status from ".$dbname.".spl_tendercapex a 
		left join ".$dbname.".log_5supplier b on a.supplierid = b.supplierid
		where a.kode='".$kodetender."'";
		$res=fetchData($str);
		$no=0;
		foreach($res as $key=>$val)
		{
			$status = "";
			$no++;
			$status = ($val['status']=='1'?"Submitted":($val['status']=='2'?"Progress":"Waiting Approval"));
			$disabled = ($val['status']!='3'?"disabled=disabled":"");
			$tab.="<tr class='rowcontent'>
				<td style='text-align:center'>".$no."</td>
				<td style='text-align:left;cursor:pointer' onclick=\"appshowcapex('".$val['kode']."','".$val['supplierid']."',event)\"><u>".$val['namasupplier']."</u></td>
				<td style='text-align:left'>".$_SESSION['lang']['uploadfile']."</td>
				<td style='text-align:center'>".$status."</td>
				<td style='text-align:center;display:none'>";
					if($val['status']=='1'){
						$tab.="<input type='radio' value='".$val['supplierid']."' id='chk_".$no."' name='chk' ".$disabled.">";						
					}
				$tab.="</td>
			</tr>";
		}
		
		echo $tab;
	break;
	
	case'loadtender2':
		$tab="";
		$str="select a.kode,a.supplierid,b.namasupplier,a.status from ".$dbname.".spl_tendercapex a 
		left join ".$dbname.".log_5supplier b on a.supplierid = b.supplierid
		where a.kode='".$kodetender."'";
		$res=fetchData($str);
		$no=0;
		if(count($res) > 0){
			foreach($res as $key=>$val)
			{
				$status = "";
				$no++;
				$status = ($val['status']=='1'?"Submitted":($val['status']=='2'?"Progress":"Waiting Approval"));
				$disabled = ($val['status']!='3'?"disabled=disabled":"");
				$tab.="<tr class='rowcontent'>
					<td style='text-align:center'>".$no."</td>
					<td style='text-align:left;cursor:pointer' onclick=\"appshowcapex('".$val['kode']."','".$val['supplierid']."',event)\"><u>".$val['namasupplier']."</u></td>
					<td style='text-align:left'>".$_SESSION['lang']['uploadfile']."</td>
					<td style='text-align:center'>".$status."</td>
					<td style='text-align:center'>
						<input type='radio' value='".$val['supplierid']."' id='chk_".$no."' name='chk' ".$disabled.">
					</td>
				</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'>
				<td colspan=5 style='text-align:center;color:red'>Pemilihan Kontraktor belum dilakukan.</td>
			</tr>";
		}
		
		echo $tab;
	break;
	
	case'simpantender':
		$str = "select * from ".$dbname.".spl_tendercapex where kode='".$kodetender."' and supplierid='".$kontraktor."'";
		$res=fetchData($str);
		$counttender = count($res);
		if($counttender > 0)
		{
			exit("warning : Kontraktor sudah ada di list tender.");
		}
		
		$str="insert into ".$dbname.".spl_tendercapex (kode,supplierid,status) values ('".$kodetender."','".$kontraktor."','1')";
		try
		{
			$owlPDO->exec($str); 
			
			$strx="select a.namasupplier, b.email_konfirmasi from ".$dbname.".log_5supplier a 
					left join ".$dbname.".log_5supalamat b on a.supplierid=b.supplierid 
					where a.supplierid='".$kontraktor."'";
			$resx=fetchData($strx);
			$namasupplier = $resx[0]['namasupplier'];
			$email = $resx[0]['email_konfirmasi'];
			
			$newpas = rand_passwd(4);
			$exp 	= urldecode(base64_encode(date("Ymd")));
			$url 	=  site_url()."/".segment(1);
			$qstr = "select * from " . $dbname . ".log_5supuser where id_supplier = '".$kontraktor."' limit 1";
			$r = fetchData($qstr);
			if(count($r) >0){
				$log_5supuser = "UPDATE " . $dbname . ".log_5supuser set password = PASSWORD('" . $newpas . "'), sessionid = 'tendercapex', email='".$email."' where id_supplier = '".$kontraktor."' limit 1";
			}
			else{
				$log_5supuser = "insert into ".$dbname.".log_5supuser (id_supplier,full_name,email,password,sessionid,isactive) values ('".$kontraktor."','".$namasupplier."','".$email."',PASSWORD('".$newpas."'),'tendercapex','1')";
			}
			
			try{
				$owlPDO->exec($log_5supuser); 
			}catch(PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
			$subject	=	"Aplikasi Tender Bangunan";
			$from		=	"STH Grup";
			$to			=	trim($email);
			$link 		=   $url."/supplier/?log=".$exp;
			$content	= 	"<table>";
			$content	.= "<tr><td>Register Penawaran Harga</td></tr>";
			$content	.= "<tr><td>Url </td><td>: $link</td></tr>";
			$content	.= "<tr><td>Email  </td><td>: $to</td></tr>";
			$content	.= "<tr><td>Password  </td><td>: $newpas</td></tr>";
			$content	.= "<tr><td>Email ini dikirim untuk membuka halaman Penawaran Harga.</td></tr>";
			$content	.= "</table>";
			kirimEmailkeSupplier($to,$cc = "",$subject,$content,$mailType='text/html');
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;
	
	case'appshowcapex':
		$tab="";
		
		$str="select * from ".$dbname.".spl_capexbangunan where kode='".$kode."'";
		$res=fetchData($str);
		
		$optSubAsset = makeOption($dbname,"sdm_5subtipeasset",'kodesub,namasub',"kodesub='".$res[0]['subtipe']."' and kodetipe='BG'");
		
		$tab="<fieldset>
		<table cellpadding=1 cellspacing=0>";
			if($suppid!='')
			{
				$optKontraktor = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$suppid."'");
				$tab.="<tr>
					<td>".$_SESSION['lang']['kontraktor']."</td>
					<td>:</td>
					<td>".$optKontraktor[$suppid]."</td>
				</tr>";
			}
			$tab.="<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td>".$res[0]['kode']."</td>
				
				<td style='padding-left:15px'>".$_SESSION['lang']['nama']."</td>
				<td>:</td>
				<td>".$res[0]['nama']."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>".$res[0]['kodeorg']."</td>
				
				<td style='padding-left:15px'>".$_SESSION['lang']['tanggalmulai']."</td>
				<td>:</td>
				<td>".tanggalnormal($res[0]['tanggalmulai'])."</td>
			</tr>
			<tr>
				<td>Sub Asset</td>
				<td>:</td>
				<td>".$optSubAsset[$res[0]['subtipe']]."</td>
				
				<td style='padding-left:15px'>".$_SESSION['lang']['tanggalsampai']."</td>
				<td>:</td>
				<td>".tanggalnormal($res[0]['tanggalselesai'])."</td>
			</tr>
		</table>";
		
		$tab.="<table cellpadding=1 cellspacing=0>
			<tr style='font-weight:bold;text-align:center'>
				<td style='border:1px solid grey'>NO</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>URAIAN PEKERJAAN</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>VOLUME</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>SATUAN</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>HARGA SATUAN</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>SUBTOTAL</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>HK</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>Rupiah HK</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>SUBTOTAL</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>Material</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>TOTAL</td>
			</tr>";
			
		$str="select distinct(deskripsikegiatan) as deskripsikegiatan from ".$dbname.".spl_capexbangunandt where kodeproject='".$kode."'";
		$res=fetchData($str);
		$no=0;
		$total = $total1 = $total2 = $total3 = $grandtotal = 0;
		foreach($res as $key=>$val)
		{
			$no++;
			
			$tab.="<tr style='font-weight:bold'>
				<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center'>".romawi($no)."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'>".$val['deskripsikegiatan']."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
			</tr>";
			
			$str2="select * from ".$dbname.".spl_capexbangunandt where kodeproject='".$kode."' and deskripsikegiatan='".$val['deskripsikegiatan']."'";
			$res2=fetchData($str2);
			$nodet=0;
			$subtotal1 = $subtotal2 = 0;
			foreach($res2 as $key2=>$val2)
			{
				if($suppid!='')
				{
					$strTender = "select b.* from ".$dbname.".spl_tendercapex a 
					left join ".$dbname.".spl_tendercapexdt b on a.id=b.tendercapexid 
					where a.kode='".$kode."' and a.supplierid='".$suppid."' and b.tipedata='kegiatan' and b.kodekegiatan='".$val2['kegiatan']."'";
					// echo $strTender."<br>";
					$resTender = fetchData($strTender);
					$val2['hargasatuan'] = $resTender[0]['hargasatuan'];
					$val2['rphk'] = $resTender[0]['rphk'];
					$val2['hk'] = $resTender[0]['hk'];
				}
				
				$nodet++;
				$subtotal1 = $val2['volume'] * $val2['hargasatuan'];
				$subtotal2 = $val2['hk'] * $val2['rphk'];
				
				$tab.="<tr id='trdet_".$val2['kegiatan']."'>
					<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center;vertical-align:top'>".$nodet."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>".$val2['namakegiatan']."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:right;vertical-align:top'>".number_format($val2['volume'])."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>".$val2['satuan']."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;text-align:right'>".number_format($val2['hargasatuan'],2)."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;text-align:right;background-color:grey'>".number_format($subtotal1,2)."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;text-align:right'>".$val2['hk']."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top';text-align:right>".number_format($val2['rphk'],2)."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;text-align:right;background-color:grey'>".number_format($subtotal2,2)."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>";
					
					$str3="select * from ".$dbname.".spl_capexbangunanmaterial where kodeproject='".$kode."' and kodekegiatan='".$val2['kegiatan']."'";
					$res3=fetchData($str3);
					$subtotal3 = 0;
					$tempsubtotal3 = 0;
					if(count($res3)>0)
					{
						$tab.="<table cellpadding=1 cellspacing=0>
							<tr style='font-weight:bold;text-align:center'>
								<td style='border:1px solid grey'>NO</td>
								<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>".$_SESSION['lang']['nama']."</td>
								<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>".$_SESSION['lang']['jumlah']."</td>
								<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>".$_SESSION['lang']['hargasatuan']."</td>
								<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey;width:30px;'>Subtotal</td>
							</tr>";
						$no3=0;
						foreach($res3 as $key3=>$val3)
						{
							if($suppid!='')
							{
								$strMaterial = "select b.* from ".$dbname.".spl_tendercapex a 
								left join ".$dbname.".spl_tendercapexdt b on a.id=b.tendercapexid 
								where a.kode='".$kode."' and a.supplierid='".$suppid."' and b.tipedata='material' and b.kodekegiatan='".$val2['kegiatan']."' and b.kodematerial='".$val3['kodebarang']."'";
								$resMaterial = fetchData($strMaterial);
								$val3['jumlah'] = $resMaterial[0]['jumlah'];
								$val3['hargasatuan'] = $resMaterial[0]['hargasatuan'];
							}
							$optNmBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val3['kodebarang']."'");
							$no3++;
							$tempsubtotal3 = $val3['jumlah'] * $val3['hargasatuan'];
							$tab.="<tr id='trmat_".$val3['kodekegiatan']."_".$val3['kodebarang']."'>
								<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center;vertical-align:top'>".$no3."</td>
								<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top'>".$optNmBarang[$val3['kodebarang']]."</td>
								<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;text-align:right'>".$val3['jumlah']."</td>
								<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;text-align:right'>".number_format($val3['hargasatuan'],2)."</td>
								<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;text-align:right;background-color:gray'>".number_format($tempsubtotal3,2)."</td>
							</tr>";
							$subtotal3 = $subtotal3 + $tempsubtotal3;
						}
						$tab.="</table>";
					}
					$total = $subtotal1 + $subtotal2 + $subtotal3;
					$tab.="</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;vertical-align:top;text-align:right;background-color:grey'>".number_format($total,2)."</td>
				</tr>";
				$total1 = $total1 + $subtotal1;
				$total2 = $total2 + $subtotal2;
				$total3 = $total3 + $subtotal3;	
			}
			
			$tab.="<tr>
				<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'>&nbsp;</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
			</tr>";
		}
		$grandtotal = ($total1 + $total2 + $total3);
		$tab.="<tr style='font-weight:bold;background-color:#6960EC'>
				<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:right;' colspan=5>TOTAL</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:right'>".number_format($total1,2)."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey' colspan=2></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:right'>".number_format($total2,2)."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:right'>".number_format($total3,2)."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:right'>".number_format($grandtotal,2)."</td>
			</tr>
		</table>
		</fieldset>";
		
		echo $tab;
	break;
	
	case'simpanappcapex':
		$hargasatuan = checkPostGet('hargasatuan','');
		$hk = checkPostGet('hk','');
		$rupiahhk = checkPostGet('rupiahhk','');
		
		$str="update ".$dbname.".spl_capexbangunandt set hargasatuan='".$hargasatuan."',hk='".$hk."',rphk='".$rupiahhk."' where kegiatan='".$kode."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;
	
	case'hapusappcapex':
		$str="delete from ".$dbname.".spl_capexbangunandt where kegiatan='".$kode."'";
		try
		{
			$owlPDO->exec($str);
			
			$str="delete from ".$dbname.".spl_capexbangunanmaterial where kodekegiatan='".$kode."'";
			try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) 
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;
	
	case'saveappmat':
		$str="update ".$dbname.".spl_capexbangunanmaterial set jumlah='".$jumlahmat."',hargasatuan='".$hargamat."' where kodekegiatan='".$kode."' and kodebarang='".$kodebarang."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;
	
	case'hapusappmat':
		$str="delete from ".$dbname.".spl_capexbangunanmaterial where kodekegiatan='".$kode."' and kodebarang='".$kodebarang."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
    
    
    
	
    
    
    
    

    
 
	
    
    
    
	
	
	
	
    
    
 case'timeFrame':	
        $iHead="select * from ".$dbname.".project where kode='".$kode."'";
        $res=$owlPDO->query($iHead) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $dHead=$res->fetch();

        $tgl1=
        $stream="PROJECT TIMEFRAME<table border=0>
                    <tr>
                            <td colspan=2>".$_SESSION['lang']['unit']."</td>
                            <td><u>".$optNmOrg[$dHead['kodeorg']]."</u></td>
                    </tr>
                    <tr>
                            <td colspan=2>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['project']."</td>
                            <td><u>".$dHead['nama']."</u></td>
                    </tr>
                    <tr>
                            <td colspan=2>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['mulai']."</td>
                            <td><u>".tanggalnormal($dHead['tanggalmulai'])."</u></td>
                            <td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['selesai']."</td>
                            <td><u>".tanggalnormal($dHead['tanggalselesai'])."</u></td>
                    </tr>
                </table>";//NO	Kodebarang	Namabarang	Satuan	JLH RAB	DIPAKAI	SELISIH	
	$arrTgl=rangeTanggal($dHead['tanggalmulai'],$dHead['tanggalselesai']);
//	print_r($arrTgl);
	$stream.="<br /><table class=sortable border=1 cellspacing=1>
                         <thead>
                            <tr>
                                <td align=center bgcolor=#CCCCCC>Tahapan</td>";
                                if(!empty($arrTgl))foreach($arrTgl as $lstTgl=>$tgl)
                                {
                                    $stream.="<td align=center bgcolor=#CCCCCC>".tanggalnormal($tgl)."</td>";
                                }
	$stream.="</tr>";
	$iTahap="select * from ".$dbname.".project_dt where kodeproject='".$kode."' ";
                    $res=$owlPDO->query($iTahap) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
	while($dTahap=$res->fetch())
	{
		//$i+=1;
		//$listKdProject[$dTahap['kodeproject']]=$dTahap['kodeproject'];
		$tahapan[$dTahap['namakegiatan']]=$dTahap['namakegiatan'];
		$tglMulai[$dTahap['namakegiatan']]=$dTahap['tanggalmulai'];
		$tglSelesai[$dTahap['namakegiatan']]=$dTahap['tanggalselesai'];
	}
	//echo $i;
	//$tglMulai[$dTahap['namakegiatan'].$dTahap['tanggalmulai']]
	//$arrTgl=rangeTanggal($dHead['tanggalmulai'],$dHead['tanggalselesai']);

	if(!empty($tahapan))foreach($tahapan as $listTahapan)
	{
            $arrTglData=rangeTanggal($tglMulai[$listTahapan],$tglSelesai[$listTahapan]);
            $listTersimpan=false;
            $dert=false;
            $stream.="<tr>
                        <td>".$tahapan[$listTahapan]."</td>";
            $isi="";
            if(!empty($arrTgl))foreach($arrTgl as $listTgl)
            {
                if($dert==false)
                {
                    if($tglSelesai[$listTahapan]==$listTgl)
                    {	
                        $isi="bgcolor=blue";//$isi="bgcolor=red";
                        $listTersimpan=false;
                        //$tglSelesai[$listTahapan]="";
                        $dert=true;
                    }
                    else
                    {
                        if($listTersimpan==false)
                        {
                            if($tglMulai[$listTahapan]==$listTgl)
                            {
                                    $isi="bgcolor=blue";
                                    $listTersimpan=true;
                            }

                        }

                    }
                }
                else
                {
                        $isi="";
                        $dert=false;
                }
                //$isi="";//exit("Error:HAHA");
                $stream.="<td ".$isi."></td>";	//".$tglSelesai[$listTahapan]."			
            }	
	}
	//$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="Laporan_Progres_Project".$dHead['kode'];
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }  
	
    break;
	
	
	 

            case'excelMaterial':

            $iHead="select * from ".$dbname.".project where kode='".$kode."'";
            $res=$owlPDO->query($iHead) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $dHead=$res->fetch();
            $stream="MATERIAL USAGE<table border=0>
                                    <tr>
                                            <td></td>
                                            <td>".$_SESSION['lang']['unitkerja']."</td>
                                            <td><u>".$optNmOrg[$dHead['kodeorg']]."</u></td>
                                    </tr>
                                    <tr>
                                            <td ></td >
                                            <td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['project']."</td>
                                            <td><u>".$dHead['nama']."</u></td>
                                    </tr>
                                    <tr>
                                            <td></td>
                                            <td>".$_SESSION['lang']['namakelompok']." ".$_SESSION['lang']['project']."</td>
                                            <td><u>".$dHead['tipe']."</u></td>
                                    </tr>
                                    <tr>
                                            <td></td>
                                            <td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['mulai']."</td>
                                            <td><u>".tanggalnormal($dHead['tanggalmulai'])."</u></td>
                                            <td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['selesai']."</td>
                                            <td><u>".tanggalnormal($dHead['tanggalselesai'])."</u></td>
                                    </tr>
                            </table>";//NO	Kodebarang	Namabarang	Satuan	JLH RAB	DIPAKAI	SELISIH

            $stream.="<br /><table class=sortable border=1 cellspacing=1>
                                     <thead>
                                            <tr>
                                                    <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['nourut']."</td> 
                                                    <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['kodebarang']."</td> 
                                                    <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['namabarang']."</td> 
                                                    <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['satuan']."</td> 
                                                    <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['penggunaan']." ".$_SESSION['lang']['project']."</td> 
                                                    <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['jumlahkeluargudang']."</td>
                                                    <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['selisih']."</td> 
                                            </tr>";

            $iPro="select * from ".$dbname.".project_material where kodeproject='".$kode."' ";
            $res=$owlPDO->query($iPro) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);            
            while($dPro=$res->fetch()){
                    $listKdBrg[$dPro['kodebarang']]=$dPro['kodebarang'];
                    @$listJumlahRab[$dPro['kodebarang']]+=$dPro['jumlah'];
            }
            $iGud="select * from ".$dbname.".log_transaksi_vw where kodeblok='".$kode."' and post='1' ";
            $res=$owlPDO->query($iGud) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);    
            while($dGud=$res->fetch()){
                    $listKdBrg[$dGud['kodebarang']]=$dGud['kodebarang'];
                    @$listJumlahPakai[$dGud['kodebarang']]+=$dGud['jumlah'];
            }	
            if(!empty($listKdBrg))foreach($listKdBrg as $kdBarang)
            {
                    $no+=1;
                    setIt($listJumlahRab[$kdBarang],0);
                    setIt($listJumlahPakai[$kdBarang],0);
                    $selisih[$kdBarang]=$listJumlahRab[$kdBarang]-$listJumlahPakai[$kdBarang];
                    $stream.="<tr>
                                            <td>".$no."</td>
                                            <td>".$kdBarang."</td>
                                            <td>".$nmBrg[$kdBarang]."</td>
                                            <td>".$satBrg[$kdBarang]."</td>
                                            <td>".$listJumlahRab[$kdBarang]."</td>
                                            <td>".$listJumlahPakai[$kdBarang]."</td>
                                            <td>".$selisih[$kdBarang]."</td>
                                    </tr>";	
            }
            $nop_="Laporan_Material_".$dHead['kode'];
            if(strlen($stream)>0)
            {
                    if ($handle = opendir('tempExcel')) {
                            while (false !== ($file = readdir($handle))) {
                            if ($file != "." && $file != ".." && $file != "index.html") {
                                    @unlink('tempExcel/'.$file);
                            }
                            }	
                            closedir($handle);
                    }
                    $handle=fopen("tempExcel/".$nop_.".xls",'w');
                    if(!fwrite($handle,$stream))
                    {
                            echo "<script language=javascript1.2>
                            parent.window.alert('Can't convert to excel format');
                            </script>";
                            exit;
                    }
                    else
                    {
                            echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls';
                            </script>";
                    }
                    fclose($handle);
            }
    break;


    

            
default:
break;					
}

function  kirimEmailkeSupplier($to,$cc = "",$subject,$body,$mailType='text/html')
{
    global $owlPDO;
    global $dbname;
    #default
    $port=25;
    $ssl='YES';
    $str=$owlPDO->query("select * from ".$dbname.".setup_remotetimbangan where lokasi='MAILSYS'");
    $str->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$str->fetch()){
        $host=trim($bar->ip);
        $username=trim($bar->username);
        $password=trim($bar->password);
        $port=trim($bar->port);
        $ssl=strtoupper(trim($bar->dbname));
    }

    if($ssl=='YES' or $ssl=='TRUE' or strtoupper($ssl)=='SSL')
    {
        $host="ssl://".$host;
    }
    #mailType posible value 'text/html' or 'text/text'
    
     require_once "Mail.php";   
     $from = "Owl-Plantation<noreply@owl-plantation.com>";
     $headers = array ('From' => $from,
       'To' => $to,
       'Cc' => $cc,  
       'Subject' => $subject,
       'Content-Type'=> $mailType);
     $mail = Mail::factory('smtp',
       array ('host' => $host,
         'auth' => true,
         'port' => $port,
         'username' => $username,
         'password' => $password));     
/*
     echo "<pre>";
     print_r($headers);
     echo "<br>";
     print_r($mail);
     echo "<br>";
     echo "</pre>";
*/
     if($mailType=='text/html')
     {
         $body.="";
     }    
	 $toto=explode(",",$to);
	 foreach($toto as $key =>$val){
           $kirim = $mail->send($val, $headers, $body);
       }
     if (PEAR::isError($kirim)) {
       return $kirim->getMessage();
     	//return true;
      } else {
       return true;
      }
     return true;
}

function rand_passwd( $length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ) {
    return substr( str_shuffle( $chars ), 0, $length );
}

function site_url(){
  return sprintf(
    "%s://%s%s",isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',$_SERVER['SERVER_NAME'],''
  );
}
function segment($num){
	$result = "";
	$list = explode("/",$_SERVER['REQUEST_URI']);
	if(isset($list[$num])){
		$result = $list[$num];
	}
  return $result; 
}
?>