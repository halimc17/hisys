<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$pathlocation = "./photokaryawan/";

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}

switch($method){
	case 'loaddata':
		$getrows=20;
		$page = checkPostGet('page',1);
		$maxdisplay=($page*$getrows-20);

		if(isset($_POST['txtsearch'])){
			$txtsearch=$_POST['txtsearch'];
			$orgsearch=$_POST['orgsearch'];	
			$noktp=$_POST['noktp'];	
			$tipesearch=$_POST['tipesearch'];	
			$statussearch=$_POST['statussearch'];
		}else{
			$txtsearch='';
			$orgsearch='';	
			$tipesearch='';
			$statussearch='';
			$noktp='';
		}


		$tglhrini=date('Y-m-d');

		$where='';
		if($txtsearch!='')
		   $where= " and a.namakaryawan like '%".$txtsearch."%'";
		if($noktp!='')
		   $where.= " and a.noktp like '%".$noktp."%'";

		if($orgsearch!='')
		   $where .=" and (a.lokasitugas='".$orgsearch."' or a.subbagian='".$orgsearch."') ";  
		if($tipesearch!='')
		   $where .=" and a.tipekaryawan='".$tipesearch."'";  
		if($statussearch=='*')
		   $where .="  and (tanggalkeluar!='0000-00-00' and tanggalkeluar<'".$tglhrini."')";
		else if($statussearch=='0000-00-00')
		   $where .=" and (tanggalkeluar>= '".$tglhrini."' or tanggalkeluar='0000-00-00')";
		else
		{}   

		// $where .=" and a.tipekaryawan in (select id from ".$dbname.".sdm_5tipekaryawan_detail where unittipe='".$_SESSION['empl']['tipelokasitugas']."')";
		  
		//make sure user can only access allowed data   
		$listOrg=ambilLokasiTugasDanTurunannya('list',$_SESSION['empl']['lokasitugas']);
		$list=str_replace("|","','",$listOrg);
		$list="'".$list."'";



		// if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
		// $str="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
		// 	  ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
		// 	  a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
		// 	  and d.id=a.tipekaryawan and namakaryawan not like '%ADMINISTRATOR%'
		// 	  ".$where."
		// 	  limit ".$maxdisplay.",".$getrows
		// 	  ;    
		// }else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
		// $str="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
		// 	  ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
		// 	  a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and namakaryawan not like '%ADMINISTRATOR%'
		// 	  and d.id=a.tipekaryawan and lokasitugas in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional != 'JAKARTA')
		// 	  ".$where."
		// 	  limit ".$maxdisplay.",".$getrows
		// 	  ;   

		// }else{
		//a.tipekaryawan!=0 orang yang tidak di pusat tidak dapat melihat data orang permanent
		$str="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
			  ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
			  lokasitugas in(".getOrgDetail(2).") and namakaryawan not like '%ADMINISTRATOR%'
			  and a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
			  and d.id=a.tipekaryawan 
			  ".$where." 
			  limit ".$maxdisplay.",".$getrows
			  ;   	
		// }

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res);
		if($numrows<1){
			echo "<tr class=rowcontent>
					<td colspan=18 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
					</tr>";
		}else{
			$no=$maxdisplay;
			while($bar=$res->fetch()){
				//get pendidikan terakhir
				$str1="select a.kelompok from ".$dbname.".sdm_5pendidikan a where a.levelpendidikan=".$bar->levelpendidikan; 
				$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				$pendidikan="";
				while($barpendidikan=$res1->fetch()){
					$pendidikan=$barpendidikan->kelompok;
				}	   
				$no+=1;
				if($bar->tanggalkeluar == '0000-00-00'){
					$valueTglKeluar = '-';
				}else{
					$valueTglKeluar = tanggalnormal($bar->tanggalkeluar);
				}
				$csss='';
				// if($bar->photo != NULL || $bar->photo != ''){
				// 	$csss = "class=zoom";
				// 	$urlimage = $pathlocation.$bar->photo;
				// }else{
				// 	$urlimage = "images/noimages.png ";
				// }

				echo "<tr class=rowcontent>
					 <td align=center>".$no."</td>
					 <td>".$bar->nik."</td>
					 <td>".$bar->namakaryawan."</td>
					 <td>".$bar->namajabatan."</td>
					 <td>".$bar->namagolongan."</td>
					 <td align=center>".$bar->lokasitugas."</td>
					 <td align=center>".$bar->subbagian."</td>
					 <td align=center>".$bar->kodeorganisasi."</td>
					 <td>".$bar->noktp."</td>
					 <td>".$pendidikan."</td>
					 <td>".$bar->statuspajak."</td>
					 <td>".$bar->statusperkawinan."</td>
					 <td align=right >".$bar->jumlahanak."</td>
					 <td align=center>".tanggalnormal($bar->tanggalmasuk)."</td>
					 <td align=center>".$valueTglKeluar."</td>
					<td align=center>".$bar->tipe."</td>
					<td align=center>".$bar->statuskaryawan."</td>";

					if($bar->statusapproval==0){
				echo "<td style=width:25px align=center></td>";
					}else{
				echo "<td style=width:25px align=center>
						<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editKaryawan('".$bar->karyawanid."','".$bar->namakaryawan."');\"> 
					  </td>";
					}
				echo "<td style=width:25px align=center>	
					    <img src=images/zoom.png class=zImgBtn  title='".$_SESSION['lang']['view']."' onclick=\"previewKaryawan('".$bar->karyawanid."','".$bar->namakaryawan."',event);\">
					  </td>
					<td style=width:25px align=center>		
						<img src=images/pdf.jpg class=zImgBtn  title='".$_SESSION['lang']['pdf']."' onclick=\"previewKaryawanPDF('".$bar->karyawanid."','".$bar->namakaryawan."',event);\">		 
					</td>
				</tr>";
			}
		}
	break;
	case'listpostingdata':
		$limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
		$colspan=6;
		$tab = "";
        $no = $maxdisplay;
		
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
		} else {
			$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
		}
		
		$sql = "select count(distinct periode, kodeorg, sudahproses) as notr from ".$dbname.".sdm_5periodegaji a where kodeorg in (".getOrgDetail(2).")";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];
		
		$str = "select distinct periode, kodeorg, sudahproses from ".$dbname.".sdm_5periodegaji a where kodeorg in (".getOrgDetail(2).") order by periode desc limit " . $offset . "," . $limit . ""; 
		$res = fetchdata($str);
		$optNewOrg = $optprd = [];
		foreach($res as $val){
			$optNewOrg[$val['kodeorg']]=$val['kodeorg'];
			$optprd[$val['periode']]=$val['periode'];
		}
		
		$sql = "select distinct lokasitugas, periodegaji from ".$dbname.".datakaryawan_hist a where lokasitugas in ('".implode("','",$optNewOrg)."') and periodegaji in ('".implode("','",$optprd)."') and approval_status='8' and version_type='B' group by lokasitugas, periodegaji";
		$req = fetchdata($sql);
		foreach($req as $bar){
			$data[$bar['lokasitugas']][$bar['periodegaji']]=$bar['periodegaji'];
			$unit[$bar['lokasitugas']][$bar['periodegaji']]=$bar['lokasitugas'];
		}

		## Cek ada datakaryawan di riwayatjabatan gak
		$sql_1 = "select distinct mulaiberlaku,darikodeorg,kekodeorg,posting from ".$dbname.".sdm_riwayatjabatan a where darikodeorg in ('".implode("','",$optNewOrg)."') or kekodeorg in ('".implode("','",$optNewOrg)."') and posting ='2'";
		$req_1 = fetchdata($sql_1);
		foreach($req_1 as $bar){
			$data_riwayatjabatan_daorg[$bar['darikodeorg']][periodelalu(substr($bar['mulaiberlaku'],0,7))]= periodelalu(substr($bar['mulaiberlaku'],0,7));
			$data_riwayatjabatan_keorg[$bar['kekodeorg']][periodelalu(substr($bar['mulaiberlaku'],0,7))]  = periodelalu(substr($bar['mulaiberlaku'],0,7));
		}
		
		// echo"<pre>";
		// print_r($data_riwayatjabatan_daorg);
		// echo"</pre>";

		$res = fetchdata($str);
		foreach($res as $val){
			if($val['sudahproses']=='0' and $data[$val['kodeorg']][$val['periode']]==''){
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=center>" . $val['periode'] . "</td>";
				$tab.="<td align=center>" . $val['kodeorg'] . "</td>";
				$tab.="<td align=left>" . getNamaOrg($val['kodeorg']). "</td>";
				$tab.="<td align=center>Not Posted</td>";
				$tab.="<td align=center><button class=mybutton onclick=closedatakary('".$val['kodeorg']."','".$val['periode']."')>Posting</button></td>";
				$tab.="</tr>";
			}elseif($data[$val['kodeorg']][$val['periode']]!=''){				
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=center>" . $data[$val['kodeorg']][$val['periode']] . "</td>";
				$tab.="<td align=center>" . $unit[$val['kodeorg']][$val['periode']] . "</td>";
				$tab.="<td align=left>" . getNamaOrg($unit[$val['kodeorg']][$val['periode']]) . "</td>";
				$tab.="<td align=center>Posted</td>";
				// if($val['sudahproses']=='0' and ($data_riwayatjabatan_daorg[$val['kodeorg']][$val['periode']]=='' and $data_riwayatjabatan_keorg[$val['kodeorg']][$val['periode']]=='') ){
					$tab.="<td align=center><button class=mybutton title='Click untuk unposting' style=color:green;border-color:green; onclick=unclosedatakary('".$val['kodeorg']."','".$val['periode']."')>Posted</button></td>";					
				// }else{					
				// 	$tab.="<td align=center><button class=mybutton style=color:red;border-color:red;>Closed</button></td>";
				// }
				$tab.="</tr>";
			}
		}
		
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'listpostingdata','getPage');
		
		echo $tab;
	break;
	case'closedatakary':
		try {
			$owlPDO->beginTransaction();
	
		$str = "select karyawanid,nik,namakaryawan,nourut from ".$dbname.".datakaryawan_hist where approval_status='9'  and lokasitugas = '".$param['kodeorg']."' and periodegaji ='".$param['periode']."'"; 
		$res = fetchdata($str);
		if(count($res)>0){ 
			$datatmpl="";
			$nodasa=0;
			foreach($res as $brs=>$val){
				$sAkhir="select * from ".$dbname.".approval where notransaksi='".$val['nourut']."'  and status='0'  order by level desc limit 1";
				$rAkhir=fetchData($sAkhir);
				$optnm=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$rAkhir[0]['karyawanid']."'");
				$nmkary=$optnm[$rAkhir[0]['karyawanid']];
				$nodasa+=1;
				//$datatmpl.=$nodasa.". NIK :".$val['nik']."-Nama : ".$val['namakaryawan']."-Penyetuju Terakhir : ".$nmkary."<br>";
				$datatmpl.=$nodasa.". ".$val['nik']." - ".$val['namakaryawan']." - ".$nmkary."<br>";
			} 
			//echo $datatmpl;
			
			throw new PDOException("Masih terdapat perubahan/buat baru datakaryawan pada periode ini yang belum di approved<br>No . NIK - Nama - Penyetuju Terakhir :<br>".$datatmpl."");
		}

		$str = "select karyawanid,nik,namakaryawan,nourut from ".$dbname.".datakaryawan_hist where approval_status='7'  and lokasitugas = '".$param['kodeorg']."' and periodegaji ='".$param['periode']."'"; 
		$res = fetchdata($str);
		if(count($res)>0){ 
			throw new PDOException("Masih terdapat datakaryawan pada periode ini yang belum di posting");
		}
		
		# Get Period Range
		$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai', "periode='" . $param['periode'] . "' and kodeorg='" .
				$param['kodeorg'] . "'");
		$resPeriod = fetchData($qPeriod);
		@$tanggal1 = $resPeriod[0]['tanggalmulai'];

		
		#ambil datakaryawan
		# and (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') 
		$query = "select karyawanid from " . $dbname . ".datakaryawan a where lokasitugas='" . $param['kodeorg'] . "' group by a.karyawanid";
		$res = fetchdata($query);
		foreach($res as $val){
			$datakaryawan[$val['karyawanid']]=$val['karyawanid'];
		}

		foreach($datakaryawan as $nik){
			$dt = "select * from " . $dbname . ".datakaryawan where karyawanid='".$nik."'";
			$rd = fetchdata($dt);
			foreach($rd as $bar){
				$data = array(
					'karyawanid'         =>$bar['karyawanid'],
					'nik'                =>$bar['nik'],
					'namakaryawan'       =>$bar['namakaryawan'],
					'namakaryawan2'      =>$bar['namakaryawan2'],
					'tempatlahir'        =>$bar['tempatlahir'],
					'tanggallahir'       =>$bar['tanggallahir'],
					'warganegara'        =>$bar['warganegara'],
					'jeniskelamin'       =>$bar['jeniskelamin'],
					'statusperkawinan'   =>$bar['statusperkawinan'],
					'tanggalmenikah'     =>$bar['tanggalmenikah'],
					'agama'              =>$bar['agama'],
					'golongandarah'      =>$bar['golongandarah'],
					'levelpendidikan'    =>$bar['levelpendidikan'],
					'alamataktif'        =>$bar['alamataktif'],
					'provinsi'           =>$bar['provinsi'],
					'kota'               =>$bar['kota'],
					'kodepos'            =>$bar['kodepos'],
					'noteleponrumah'     =>$bar['noteleponrumah'],
					'nohp'               =>$bar['nohp'],
					'nohp2'              =>$bar['nohp2'],
					'norekeningbank'     =>$bar['norekeningbank'],
					'namabank'           =>$bar['namabank'],
					'pemilikrekening'    =>$bar['pemilikrekening'],
					'sistemgaji'         =>$bar['sistemgaji'],
					'nopaspor'           =>$bar['nopaspor'],
					'no_keluarga'        =>$bar['no_keluarga'],
					'noktp'              =>$bar['noktp'],
					'notelepondarurat'   =>$bar['notelepondarurat'],
					'tanggalmasuk'       =>$bar['tanggalmasuk'],
					'tanggalpengangkatan'=>$bar['tanggalpengangkatan'],
					'tanggalpengangkatannonstaff'=>$bar['tanggalpengangkatannonstaff'],
					'tanggalkeluar'      =>$bar['tanggalkeluar'],
					'tipekaryawan'       =>$bar['tipekaryawan'],
					'jumlahanak'         =>$bar['jumlahanak'],
					'jumlahtanggungan'   =>$bar['jumlahtanggungan'],
					'statuspajak'        =>$bar['statuspajak'],
					'npwp'               =>$bar['npwp'],
					'bpjs'               =>$bar['bpjs'],
					'lokasipenerimaan'   =>$bar['lokasipenerimaan'],
					'kodeorganisasi'     =>$bar['kodeorganisasi'],
					'bagian'             =>$bar['bagian'],
					'kodejabatan'        =>$bar['kodejabatan'],
					'kodegolongan'       =>$bar['kodegolongan'],
					'lokasitugas'        =>$bar['lokasitugas'],
					'photo'              =>$bar['photo'],
					'email'              =>$bar['email'],
					'emailkantor'        =>$bar['emailkantor'],
					'alokasi'            =>$bar['alokasi'],
					'subbagian'          =>$bar['subbagian'],
					'subdept'            =>$bar['subdept'],
					'jms'                =>$bar['jms'],
					'kodecatu'           =>$bar['kodecatu'],
					'statpremi'          =>$bar['statpremi'],
					'statusakad'         =>$bar['statusakad'],
					'suku'               =>$bar['suku'],
					'sim'                =>$bar['sim'],
					'statuskaryawan'     =>$bar['statuskaryawan'],
					'updateby'           =>$bar['updateby'],
					'pensiun'            =>$bar['pensiun'],
					'insstatuspajak'     =>$bar['insstatuspajak'],
					'supbpjs'            =>$bar['supbpjs'],
					'kppnpwp'            =>$bar['kppnpwp'],
					'nosk'               =>$bar['nosk'],
					'tanggalsk'          =>$bar['tanggalsk'],
					'noerf'              =>$bar['noerf'],
					'periodeakhirgaji'   =>$bar['periodeakhirgaji'],
					'tmkjamsostek'       =>$bar['tmkjamsostek'],
					'kabupaten'          =>$bar['kabupaten'],
					'kecamatan'          =>$bar['kecamatan'],
					'desa'               =>$bar['desa'],
					'bulandaftarbpjs'    =>$bar['bulandaftarbpjs'],
					'updatetime'         =>date('Y-m-d'),
					'approval_status'    =>'8',
					'periodegaji'        =>$param['periode'],
					'version_type'       =>'B',
					'datachange'         =>'',
					'version'            =>'1'
				);
				$query = insertQuery($dbname,'datakaryawan_hist',$data,array_keys($data));
				$owlPDO->exec($query);
			}
		}
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
	case'unclosedatakary':
		try {
			$owlPDO->beginTransaction();
	
		# Get Period Range
		$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'sudahproses', "periode='" . $param['periode'] . "' and kodeorg='" .
				$param['kodeorg'] . "' and sudahproses='1'");
		$resPeriod = fetchData($qPeriod);
		if(count($resPeriod)>0){
			throw new PDOException("Periode gaji sudah ditutup.");			
		}
		
		$str = "delete from " . $dbname . ".datakaryawan_hist where  lokasitugas='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and approval_status='8' and version_type='B'";
		$owlPDO->exec($str);
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
}
?>
