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
			$jabatansearch=$_POST['jabatansearch'];
			$tipesearch=$_POST['tipesearch'];
			$statussearch=$_POST['statussearch'];
		}else{
			$txtsearch='';
			$orgsearch='';
			$jabatansearch='';
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
		if($jabatansearch!='')
		   $where .=" and a.kodejabatan='".$jabatansearch."'";
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
			  order by a.namakaryawan asc
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
					$valueTglKeluar = "<span style='color:red;'>".tanggalnormal($bar->tanggalkeluar)."</span>";
				}
				if($bar->photo != NULL && $bar->photo != ''){
					$urlimage = $pathlocation.$bar->photo;
				}else{
					$urlimage = "images/noimages.png";
				}

				echo "<tr class=rowcontent>
					 <td align=center>".$no."</td>
					 <td align=center><img src='".$urlimage."' style='width:40px;height:40px;object-fit:cover;'></td>
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
					<td align=center>".$bar->tipe."</td>
					<td align=center>".($bar->statuskaryawan=='Keluar' ? "<span style='color:red;'>".$bar->statuskaryawan."</span>" : $bar->statuskaryawan)."</td>
					 <td align=center>".$valueTglKeluar."</td>";

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
		$colspan=12;
		$tab = "";
        $no = $maxdisplay;
		
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
		} else {
			$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
		}

		$unitfilter = checkPostGet('unitfilter','');
		$periodefilter = checkPostGet('periodefilter','');
		$wherefilter = "";
		if($unitfilter!=''){
			$wherefilter .= " and kodeorg='".$unitfilter."'";
		}
		if($periodefilter!=''){
			$wherefilter .= " and periode='".$periodefilter."'";
		}

		$sql = "select count(distinct periode, kodeorg, sudahproses) as notr from ".$dbname.".sdm_5periodegaji a where kodeorg in (".getOrgDetail(2).")".$wherefilter;
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];

		$str = "select distinct periode, kodeorg, sudahproses from ".$dbname.".sdm_5periodegaji a where kodeorg in (".getOrgDetail(2).")".$wherefilter." order by periode desc limit " . $offset . "," . $limit . "";
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
		$sql_1 = "select karyawanid,mulaiberlaku,darikodeorg,kekodeorg,posting from ".$dbname.".sdm_riwayatjabatan a where (darikodeorg in ('".implode("','",$optNewOrg)."') or kekodeorg in ('".implode("','",$optNewOrg)."')) and posting='2' and darikodeorg!=kekodeorg";
		$req_1 = fetchdata($sql_1);
		foreach($req_1 as $bar){
			$p = periodelalu(substr($bar['mulaiberlaku'],0,7));
			@$data_riwayatjabatan_daorg[$bar['darikodeorg']][$p] += 1;
			@$data_riwayatjabatan_keorg[$bar['kekodeorg']][$p] += 1;
		}
		
		// echo"<pre>";
		// print_r($data_riwayatjabatan_daorg);
		// echo"</pre>";

		$tglhriniposting=date('Y-m-d');

		$res = fetchdata($str);
		foreach($res as $val){
			$kodeorg = $val['kodeorg'];
			$periode = $val['periode'];
			$isposted = ($data[$kodeorg][$periode] != '');

			#jumlah data karyawan_hist yang tersimpan (hasil posting) untuk periode & unit ini
			$sqlhist = "select count(*) c from ".$dbname.".datakaryawan_hist where lokasitugas='".$kodeorg."' and periodegaji='".$periode."' and approval_status='8' and version_type='B' and namakaryawan not like '%ADMINISTRATOR%'";
			$reshist = fetchdata($sqlhist);
			$jmlhist = $reshist[0]['c'];

			#jumlah karyawan aktif di unit ini saat ini (real time, bukan potret periode)
			$sqlaktif = "select count(*) c from ".$dbname.".datakaryawan where lokasitugas='".$kodeorg."' and (tanggalkeluar>='".$tglhriniposting."' or tanggalkeluar='0000-00-00') and namakaryawan not like '%ADMINISTRATOR%'";
			$resaktif = fetchdata($sqlaktif);
			$jmlaktif = $resaktif[0]['c'];

			#tanggal mulai & sampai periode ini, untuk hitung yang keluar di periode ini
			$sqlperiode = "select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".$kodeorg."' and periode='".$periode."' limit 1";
			$resperiode = fetchdata($sqlperiode);
			$jmlkeluar = 0;
			if(count($resperiode)>0){
				$sqlkeluar = "select count(*) c from ".$dbname.".datakaryawan where lokasitugas='".$kodeorg."' and tanggalkeluar between '".$resperiode[0]['tanggalmulai']."' and '".$resperiode[0]['tanggalsampai']."' and namakaryawan not like '%ADMINISTRATOR%'";
				$reskeluar = fetchdata($sqlkeluar);
				$jmlkeluar = $reskeluar[0]['c'];
			}

			$jmlriwayat = (int)@$data_riwayatjabatan_daorg[$kodeorg][$periode] + (int)@$data_riwayatjabatan_keorg[$kodeorg][$periode];
			$adariwayat = ($jmlriwayat > 0);

			#cek periode akuntansi (tutupbuku), terpisah dari periode gaji (sudahproses)
			$sqlakuntansi = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$kodeorg."' and periode='".$periode."' limit 1";
			$resakuntansi = fetchdata($sqlakuntansi);
			$tutupbukuakuntansi = (count($resakuntansi)>0 && $resakuntansi[0]['tutupbuku']=='1');
			$adatutupbuku = ($val['sudahproses']!='0' || $tutupbukuakuntansi);

			#jumlah karyawan asli (bukan ADMINISTRATOR) yang pernah tercatat di unit ini
			$sqlkaryawanunit = "select count(*) c from ".$dbname.".datakaryawan where lokasitugas='".$kodeorg."' and namakaryawan not like '%ADMINISTRATOR%'";
			$reskaryawanunit = fetchdata($sqlkaryawanunit);
			$jmlkaryawanunit = $reskaryawanunit[0]['c'];

			#siapa & kapan periode ini diposting (kalau memang sudah diposting)
			$postingoleh = "-";
			$tglposting = "-";
			if($isposted){
				$sqlpostinfo = "select updateby,updatetime from ".$dbname.".datakaryawan_hist where lokasitugas='".$kodeorg."' and periodegaji='".$periode."' and approval_status='8' and version_type='B' order by updatetime desc limit 1";
				$respostinfo = fetchdata($sqlpostinfo);
				if(count($respostinfo)>0){
					$postingoleh = getNamaKaryawan($respostinfo[0]['updateby']);
					$tglposting = tanggalnormal(substr($respostinfo[0]['updatetime'],0,10))." ".substr($respostinfo[0]['updatetime'],11,5);
				}
			}

			if($jmlkaryawanunit==0){
				$statuslabel = "Tidak Ada Data Karyawan";
				$alasan = "Data Karyawan Tidak Ada";
				$tombol = "-";
			}elseif($isposted && !$adatutupbuku && !$adariwayat){
				$statuslabel = "Posted";
				$alasan = "-";
				$tombol = "<button class=mybutton title='Click untuk unposting' style=color:green;border-color:green; onclick=unclosedatakary('".$kodeorg."','".$periode."')>Posted</button>";
			}elseif($isposted){
				$statuslabel = "Closed";
				$alasanparts = array();
				$alasanplainparts = array();
				if($adatutupbuku){
					$alasanparts[] = "Tutup Buku";
					$alasanplainparts[] = "Tutup Buku";
				}
				if($adariwayat){
					$linkpindah = "<a href='javascript:void(0)' onclick=\"lihatKaryawanPindah('".$kodeorg."','".$periode."')\">".$jmlriwayat." Karyawan Pindah</a>";
					if($adatutupbuku){
						$alasanparts[] = $linkpindah;
						$alasanplainparts[] = $jmlriwayat." Karyawan Pindah";
					}else{
						$alasanparts[] = $linkpindah." - Perlu Pengecekan Tim IT untuk Unposting";
						$alasanplainparts[] = $jmlriwayat." Karyawan Pindah - Perlu Pengecekan Tim IT untuk Unposting";
					}
				}
				$alasan = implode(", ", $alasanparts);
				$alasanplain = implode(", ", $alasanplainparts);
				$tombol = "<button class=mybutton disabled style='color:red;border-color:red;' title='".$alasanplain."'>Closed</button>";
			}elseif($val['sudahproses']=='0'){
				$statuslabel = "Not Posted";
				$alasan = "-";
				$tombol = "<button class=mybutton onclick=closedatakary('".$kodeorg."','".$periode."')>Posting</button>";
			}else{
				$statuslabel = "Tutup Buku - Belum Posting SDM";
				$alasan = "Tutup Buku";
				$tombol = "<button class=mybutton onclick=closedatakary('".$kodeorg."','".$periode."')>Posting</button>";
			}

			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=center>" . $periode . "</td>";
			$tab.="<td align=center>" . $kodeorg . "</td>";
			$tab.="<td align=left>" . getNamaOrg($kodeorg). "</td>";
			$tab.="<td align=right>" . $jmlhist . "</td>";
			$tab.="<td align=right>" . $jmlaktif . "</td>";
			$tab.="<td align=right>" . $jmlkeluar . "</td>";
			$tab.="<td align=center>" . $statuslabel . "</td>";
			$tab.="<td align=center>" . $alasan . "</td>";
			$tab.="<td align=left>" . $postingoleh . "</td>";
			$tab.="<td align=center>" . $tglposting . "</td>";
			$tab.="<td align=center>" . $tombol . "</td>";
			$tab.="</tr>";
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
		$query = "select karyawanid from " . $dbname . ".datakaryawan a where lokasitugas='" . $param['kodeorg'] . "' and namakaryawan not like '%ADMINISTRATOR%' group by a.karyawanid";
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
					'updateby'           =>$_SESSION['standard']['userid'],
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
					'updatetime'         =>date('Y-m-d H:i:s'),
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
	case'listkaryawanpindah':
		$kodeorg = checkPostGet('kodeorg','');
		$periode = checkPostGet('periode','');
		$periodenext = date('Y-m', strtotime($periode.'-01 +1 month'));

		$sql = "select a.karyawanid,b.namakaryawan,a.mulaiberlaku,a.darikodeorg,a.darisubbagian,a.kekodeorg,a.kesubbagian
				from ".$dbname.".sdm_riwayatjabatan a
				left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				where (a.darikodeorg='".$kodeorg."' or a.kekodeorg='".$kodeorg."') and a.posting='2' and a.darikodeorg!=a.kekodeorg and substr(a.mulaiberlaku,1,7)='".$periodenext."'
				order by b.namakaryawan asc";
		$res = fetchdata($sql);

		$tab = "<table class=sortable border=1 cellspacing=1 cellpadding=5 width=100%>
			<thead><tr class=rowheader>
				<td align=center>No.</td>
				<td align=center>NIK</td>
				<td align=center>Nama Karyawan</td>
				<td align=center>Dari Unit</td>
				<td align=center>Dari Divisi</td>
				<td align=center>Ke Unit</td>
				<td align=center>Ke Divisi</td>
				<td align=center>Tanggal Mulai Berlaku</td>
			</tr></thead><tbody>";
		if(count($res)<1){
			$tab .= "<tr class=rowcontent><td colspan=8 align=center>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			$no=0;
			foreach($res as $bar){
				$no++;
				$darisubbagian = ($bar['darisubbagian']!='') ? $bar['darisubbagian']." - ".getNamaOrg($bar['darisubbagian']) : "-";
				$kesubbagian = ($bar['kesubbagian']!='') ? $bar['kesubbagian']." - ".getNamaOrg($bar['kesubbagian']) : "-";
				$tab .= "<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center>".$bar['karyawanid']."</td>
					<td align=left>".$bar['namakaryawan']."</td>
					<td align=center>".$bar['darikodeorg']." - ".getNamaOrg($bar['darikodeorg'])."</td>
					<td align=center>".$darisubbagian."</td>
					<td align=center>".$bar['kekodeorg']." - ".getNamaOrg($bar['kekodeorg'])."</td>
					<td align=center>".$kesubbagian."</td>
					<td align=center>".tanggalnormal($bar['mulaiberlaku'])."</td>
				</tr>";
			}
		}
		$tab .= "</tbody></table>";
		echo $tab;
	break;
}
?>
