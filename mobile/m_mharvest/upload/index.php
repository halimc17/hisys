<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Index extends OWL_Controller{
    
	public function __construct(){
		parent::__construct();
		
    }
    function index($userlogin=array()){
        switch($this->request('method')){
            default:
                echo "No Method";
            break;
            case 'getprofile2':
                echo json_encode($this->getprofile2($userlogin['karyawanid']));
            break;
            case 'synchronize';
                switch($this->request('tipeData')){
                case 'master':
                   echo json_encode($this->synchronizeMaster($userlogin['karyawanid']));
                break;
                }
            break;
            
        }
       
    }

    function getuserMaster($karyawanid){
        $str = "SELECT a.*, b.kodeorg AS lokasitugas1, c.*
        FROM ".$this->dbname.".datakaryawan AS a 
        LEFT JOIN ".$this->dbname.".user AS b ON b.karyawanid = a.karyawanid 
        LEFT JOIN ".$this->dbname.".api_key c on c.username = b.namauser
        WHERE a.karyawanid=".$karyawanid." AND c.uuid = '".addslashes($this->request('uuid'))."' ORDER BY ID DESC LIMIT 1";	

        $qOrg=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while($rOrg=$qOrg->fetch()) {
            $user=array(
                'username' 		=> $this->request('username'),
                'karyawanid' 	=> $rOrg['karyawanid'],
                'namakaryawan' 	=> $rOrg['namakaryawan'],
                'namakaryawan2' => ''/* $rOrg['namakaryawan2'] */,
                'nik' 			=> $rOrg['nik'],
                'tanggallahir' 	=> $rOrg['tanggallahir'],
                'sistemgaji' 	=> $rOrg['sistemgaji'],
                'tanggalmasuk' 	=> $rOrg['tanggalmasuk'],
                'tipekaryawan' 	=> $rOrg['tipekaryawan'],
                'pt' 			=> $rOrg['kodeorganisasi'],
                'bagian' 		=> $rOrg['bagian'],
                'lokasitugas' 	=> $rOrg['lokasitugas'],
                'subbagian' 	=> $rOrg['subbagian'],
                'kodegolongan' 	=> $rOrg['kodegolongan'],
                'kodejabatan' 	=> $rOrg['kodejabatan'],
                'userid' 		=> $rOrg['id'],
                'key_api' 		=> $rOrg['key_api'],
                'datelogin' 	=> $rOrg['datelogin'],
                'explogin' 		=> $rOrg['explogin'],
            );	
        }

        $user['regional'] 	= "";
        $user['logged'] 	= '1';
        if(count($user) > 0){
            $strx 	= "select regional from  ".$this->dbname.".bgt_regional_assignment where 
            kodeunit='".$user['lokasitugas']."'";
            $qOrg 	= $this->owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
            $qOrg->setFetchMode(PDO::FETCH_ASSOC);
            while($rOrg = $qOrg->fetch())
            {
                $user['regional'] = $rOrg['regional'];
            }  
        }

        $user['tipeUser'] 		= 'KEBUN';
        if(substr($user['lokasitugas'],-2)=='HO'){
            $user['tipeUser']  	= 'HO';
        }else   if(substr($user['lokasitugas'],-2)=='RO'){
            $user['tipeUser']  	= 'RO';
        }
        return $user;
    }
    function getprofile2($karyawanid){
        $user=$this->getuserMaster($karyawanid);
        $tipeUser = $user['tipeUser'];
        $arrMenu = array();
			$bahasa = "";
			if(isset($param['bahasa'])){
				$bahasa = $param['bahasa'];
			}
			$str="select 
			t2.id,
			t2.type,
			t2.caption,
            t2.caption2,
			t2.caption3,
			t2.formjs,
			t2.action,
			t2.parent,
			t2.urut,
			t2.hide
			from ".$this->dbname.".authmobile t1 
			left join ".$this->dbname.".menumobile t2 on t1.menuid=t2.id
			left join ".$this->dbname.".bahasa t3 on t2.caption=t3.legend
			where t1.namauser='".$user['username']."' and t1.status='1' and t2.hide='0'";				
			$res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$data['menu'] = array();
			while($bar=$res->fetch()){
                $d = array();
				$d['id'] 		= $bar['id'];
				//$d['icon'] 		= $bar['icon'];
				$d['type'] 		= $bar['type'];
				$d['caption'] 	= $bar['caption'];
				$d['caption2'] 	= $bar['caption2'];
				$d['caption3'] 	= $bar['caption3'];
				$d['action'] 	= $bar['action'];
				$d['formjs'] 	= $bar['formjs'];
				$d['parent'] 	= $bar['parent'];
				$d['urut'] 		= $bar['urut'];
				$d['hide'] 		= $bar['hide'];
				$data['menu'][] = $d;
			}
			$data['user'] = $user;

			//insertLogMobile('Profile 2');
            return $data;
    }
    function synchronizeMaster($karyawanid){
        
        $user=$this->getuserMaster($karyawanid);
        $tipeUser = $user['tipeUser'];

        $data 				= array();
        $version 			= $this->request('version');
        $appversion 		= $this->request('appversion');
        $appversionname  	= "";
        if($this->request('appversionname')!=null){
            $appversionname	= $this->request('appversionname');
        }

        $str 			= "select * from data_version order by updatetime DESC limit 1";
        $dataversion  	= $this->fetchData($str);
        $verData 		= $dataversion[0];
        $data['statusappupdate'] = "UPDATED";
        if($appversionname != ""){
            $vername = explode(".",$appversionname);
            $vernameDB = explode(".",$verData['appversion_name']);
            if(count($vername) == 3 and count($vernameDB) == 3){
                $major = $vername[0];
                $minor = $vername[1];
                $build = $vername[2];
                
                $majorDb = $vernameDB[0];
                $minorDb = $vernameDB[1];
                $buildDb = $vernameDB[2];
                
                if($build < $buildDb){
                    $data['statusappupdate'] 	= "MUST-UPDATE";
                }
                if($minor < $minorDb){
                    $data['statusappupdate'] 	= "URGENT-APP";
                }
                if($major < $majorDb){
                    $data['statusappupdate'] 	= "URGENT-DB";
                }
            }else{
                exit("Gagal : Versi Aplikasi Tidak sesuai !! ");
            }
        }else{
            $data['statusappupdate'] 	= "";
        }
            
        //  if($version == $verData['version']){
        // $data['dataversion'] = "same"; 
        // if($appversion < $verData['appversion']){
        // 	$data['appversion'] = "changing"; 
        // }else{
        // 	$data['appversion'] = "same"; 
        // }
        // $data['appversionnum'] 	= $verData['appversion']; 
        // $data['appversionname'] 	= $verData['appversion_name']; 
        // echo json_encode($data);
        // exit();
        //  }else if($appversion < $verData['appversion']){
        // $data['appversion'] 	= "changing"; 
        // $data['appversionnum'] 	= $verData['appversion']; 
        // $data['appversionname']	= $verData['appversion_name']; 
        // $data['dataversion'] 	= "changing";
        // if($data['statusappupdate'] == "URGENT-DB" or $data['statusappupdate'] == "URGENT-APP"){
        // 	echo json_encode($data);
        // 	exit();
        // }
        //  }else{
        //   $data['appversion'] 		= "same"; 
        //   $data['appversionnum']	= $verData['appversion']; 
        //   $data['appversionname'] 	= $verData['appversion_name']; 
        //   $data['dataversion'] 		= "changing"; 
        // }
            
        $data['appversion'] 		= "same"; 
        $data['appversionnum']		= $verData['appversion']; 
        $data['appversionname'] 	= $verData['appversion_name']; 
        $data['dataversion'] 		= "changing"; 
        
        #1. Synchronize master data
        $data['masterdata']=array();
        $karyawanid1 = "";
        $nik ="";
        $subbagian ="";
        $namakaryawan ="";
        $tipekaryawan ="";
        $EstateOrMill = "";
            //if($tipeUser == "KEBUN"){
            //	$EstateOrMill = " and a.lokasitugas like '%E'";
            //}
        if($tipeUser == "HO" or $tipeUser == "RO"){
            // $ho_or_ro = "and a.lokasitugas REGEXP 'HO|RO'";
            $ho_or_ro = "and a.lokasitugas LIKE '%HO%' OR a.lokasitugas LIKE '%RO%'";
        }else{
            $ho_or_ro= "and kodeorganisasi = '".$user['pt']."'";
        }
        $str="select a.karyawanid,a.nik,a.lokasitugas,a.subbagian,a.namakaryawan
        ,a.kodejabatan,b.namajabatan,a.tipekaryawan ,a.tanggalkeluar from
        ".$this->dbname.".datakaryawan a 
        left join ".$this->dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
        where (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".date('Y-m-d')."') ".$ho_or_ro;
        //echo $str;
        $qKaryawan=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $qKaryawan->setFetchMode(PDO::FETCH_OBJ);
        $count=0;
        $datakaryawan = array();
        $d = array();
        $listKarPemanen = array(1,2,3,4,6);
        $listKarPerawatan = array(1,2,3,4,6);
        while($bar=$qKaryawan->fetch())	{			   
                    //array to json
            $d['karyawanid'] 	= $bar->karyawanid;
            $d['nik'] 			= $bar->nik==''?'NaN':$bar->nik;;
            $d['lokasitugas'] 	= ($bar->lokasitugas==''?' ':$bar->lokasitugas);
            $d['subbagian'] 	= ($bar->subbagian==''?' ':$bar->subbagian);
            $d['namakaryawan'] 	= ($bar->namakaryawan==''?'NaN':$bar->namakaryawan);
            $d['namakaryawan2']	= ''/* ($bar->namakaryawan2==''?'':$bar->namakaryawan2) */;
            $d['tipekaryawan'] 	= ($bar->tipekaryawan==''?'0':$bar->tipekaryawan);
            $d['namajabatan'] 	= ($bar->namajabatan==''?'NaN':$bar->namajabatan);
            $d['kodejabatan'] 	= ($bar->kodejabatan==''?'NaN':$bar->kodejabatan);
            $d['tanggalkeluar'] = ($bar->tanggalkeluar==''?'NaN':$bar->tanggalkeluar);
            $d['gajipokok']		= '';
                    //bisa jadi pemanen
            if(in_array($bar->tipekaryawan,$listKarPemanen)){
                $d['pemanen'] = "1";
            }else{
                $d['pemanen'] = "0";
            }
            if(in_array($bar->tipekaryawan,$listKarPerawatan)){
                $d['perawatan'] = "1";
            }else{
                $d['perawatan'] = "0";
            }
            $d['kemandoran'] = "";
            $datakaryawan[] = $d;
            $count++;
        }

    $data['masterdata']['karyawan'] = $datakaryawan;


        #2
    $dataorganisasi = array();
    $d = array();
    $str2="select kodeorganisasi,namaorganisasi,tipe,induk from ".$this->dbname.".organisasi
    where (tipe like '%GUDANG%')
    or (length(kodeorganisasi)=4 or tipe='PABRIK') or 
    (tipe in ('AFDELING','BIBITAN'))";

    $qOrg=$this->owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
    $qOrg->setFetchMode(PDO::FETCH_OBJ);
    $count=0;
    while($bar2=$qOrg->fetch())	{

        $count++;	
        $d['kodeorganisasi'] 	= $bar2->kodeorganisasi;
        $d['induk']	 			= $bar2->induk;
        $d['namaorganisasi'] 	= ($bar2->namaorganisasi==''?'NaN':$bar2->namaorganisasi);
        $d['tipe'] 				= ($bar2->tipe==''?' ':$bar2->tipe);
        $d['sertifikat'] 	    = "";
        $d['inisialisasiorganisasi']="";
        $dataorganisasi[] = $d;
    }

    $data['masterdata']['organisasi'] = $dataorganisasi;


    #3 blok
    $datablok = array();
    $d = array();
    $str3="select kodeorg,tahuntanam,statusblok,luasareaproduktif,kelaspohon,jumlahpokok,topografi from ".$this->dbname.".setup_blok
    where (kodeorg like '".substr($user['lokasitugas'],0,4)."%' and statusblok <> 'TB') or (statusblok = 'TB') ";//and status = 'A' 
    $qBlok=$this->owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
    $qBlok->setFetchMode(PDO::FETCH_OBJ);
    $count=0;
    while($bar3=$qBlok->fetch()){
        $d['kodeorg'] =$bar3->kodeorg;
        $d['tahuntanam'] =($bar3->tahuntanam==''?0:$bar3->tahuntanam);
        $d['statusblok'] =($bar3->statusblok==''?'TM':$bar3->statusblok);
        $d['kegiatangroup'] = ($bar3->statusblok==''?'NaN':($bar3->statusblok=="TM"?"'PNN','TM'":($bar3->statusblok=="TBM"?"'TBM','LC'":strval($bar3->statusblok))));
        $d['luasareaproduktif'] =($bar3->luasareaproduktif==''?0:$bar3->luasareaproduktif);
        $d['kelaspohon'] =($bar3->kelaspohon==''?0:$bar3->kelaspohon);
        $d['jumlahpokok'] =($bar3->jumlahpokok==''?0:$bar3->jumlahpokok);
        $d['topografi'] =($bar3->topografi==''?0:$bar3->topografi);
        $d['kemandoran'] ="";
        $d['latitude'] ="";
        $d['longitude'] ="";
        $datablok[] = $d;
        $count++;			 
    }

    $data['masterdata']['blok'] = $datablok;

    #4 master barang
        $kodebarang ="";
        $namabarang ="";
        $satuan ="";
        $masterbarang = array();

        $d = array();
        $str4="select kodebarang,namabarang,satuan from ".$this->dbname.".log_5masterbarang
        where kodebarang like '311%' or kodebarang like '312%'";   
        $qBarang=$this->owlPDO->query($str4) or die(print " Gagal: ".PDOException::getMessage());
        $qBarang->setFetchMode(PDO::FETCH_OBJ);
        $count=0;
        while($bar4=$qBarang->fetch())	{

        $d['kodebarang'] = $bar4->kodebarang;
        $d['namabarang'] = htmlspecialchars(($bar4->namabarang==''?'NaN':$bar4->namabarang));
        $d['satuan'] = ($bar4->satuan==''?"NaN":$bar4->satuan);
        $masterbarang[] = $d;
        $count++;			 
        }
        $data['masterdata']['barang'] = $masterbarang;


    #5 master kendaraan
        $kodevhc ="";
        $detailvhc ="";
        $datakendaraan = array();
        $d = array();
        $str5="select kodevhc,kodeorg,nopol from ".$this->dbname.".vhc_5master
        where kelompokvhc in('AB','KD') order by kodevhc"; 
        $qVhc=$this->owlPDO->query($str5) or die(print " Gagal: ".PDOException::getMessage());
        $qVhc->setFetchMode(PDO::FETCH_OBJ);
        $count=0;
        while($bar5=$qVhc->fetch())	{

        $d['kodevhc'] =$bar5->kodevhc;
        $d['nopol'] =$bar5->nopol;
        $d['detailvhc'] =($bar5->kodeorg==''?'':$bar5->kodeorg);
        $datakendaraan[] = $d;
        $count++;			 
        }

        $data['masterdata']['kendaraan'] = $datakendaraan;

    #6 master kegiatan
        $kodekegiatan ="";
        $namakegiatan ="";
        $satuan ="";
        $kelompok ="";
        $noakun ="";
        $datakegiatan = array();

        $d = array();
        $str6="select kodekegiatan,namakegiatan,satuan,kelompok,noakun,premi from ".$this->dbname.".setup_kegiatan
        where status=1 and kelompok in('BBT','TB','TBM','TM','SPL')";  
        $qKegiatan=$this->owlPDO->query($str6) or die(print " Gagal: ".PDOException::getMessage());
        $qKegiatan->setFetchMode(PDO::FETCH_OBJ);
        $count=0;
        while($bar6=$qKegiatan->fetch())	{

        $d['kodekegiatan'] 		= $bar6->kodekegiatan;
        $d['namakegiatan'] 		= $bar6->namakegiatan;
        $d['satuan'] 			= ($bar6->satuan==''?'NaN':$bar6->satuan);
        $d['kelompok'] 			= ($bar6->kelompok==''?'NaN':$bar6->kelompok);
        $d['noakun'] 			= $bar6->noakun;
        $d['premi'] 			= $bar6->premi;
        //$d['kodeklasifikasi']	= $bar6->kodeklasifikasi;
        $datakegiatan[] = $d;
        $count++;			 
        }

        $data['masterdata']['kegiatan'] = $datakegiatan;


    #7 master custommer

        $kodecustomer ="";
        $namacustomer ="";
        $datacustomer = array();
    $d = array();
    $str7="select kodecustomer,namacustomer from ".$this->dbname.".pmn_4customer"; 
    $qCust=$this->owlPDO->query($str7) or die(print " Gagal: ".PDOException::getMessage());
    $qCust->setFetchMode(PDO::FETCH_OBJ);
    $count=0;
    
    while($bar7=$qCust->fetch())	{
        $d['kodecustomer'] = $bar7->kodecustomer;
        $d['namacustomer'] = $bar7->namacustomer;
        $datacustomer[] = $d;
            $count++;			 
    }
    //di Hardcode nama pabrik
    
    $pabrik = array(
            'EXM1'=>'MILL 1',
            'EXM2'=>'MILL 2'
        );
    foreach($pabrik as $k => $v){	
        $d['kodecustomer'] = $k;
        $d['namacustomer'] = $v;
        $datacustomer[] = $d;
    }
    
    $data['masterdata']['customer'] = $datacustomer;

    #8 master basi dan premi panen:
    $databasispanen = array();
    /*
    $d = array();
    $str8=" select afdeling,'' as jenispremi,kelaspohon,basis,'' as premilebihbasis,'' as premilibur,
            '' as premiliburcapaibasis,'' as topografi,'' as premitopografi,'' as premibrondolan 
            from ".$this->dbname.".kebun_5basispanen2 where afdeling='".$user['pt']."'";	
    $qBasis=$this->owlPDO->query($str8) or die(print " Gagal: ".PDOException::getMessage());
    $qBasis->setFetchMode(PDO::FETCH_OBJ);
    $count=0;
        while($bar8=$qBasis->fetch())	{
            $d['afdeling'] 				=$bar8->afdeling;
            $d['jenispremi'] 			=$bar8->jenispremi;
            $d['kelaspohon'] 			=($bar8->kelaspohon==''?' ':$bar8->kelaspohon); 
            $d['basis'] 				=$bar8->basis;
            $d['premilebihbasis'] 		=$bar8->premilebihbasis;
            $d['premilibur'] 			=$bar8->premilibur;
            $d['premiliburcapaibasis'] 	=$bar8->premiliburcapaibasis;
            $d['topografi'] 			=$bar8->topografi;
            $d['premitopografi'] 		=$bar8->premitopografi;
            $d['premibrondolan'] 		=$bar8->premibrondolan;
            $databasispanen[]	= $d;
            $count++;			 
        }
    */
        $data['masterdata']['basispanen'] = $databasispanen;
        

    #9 BJR===========
    $kodeorgBjr ="";
    $kelaspohonBjr ="";
    $bjrBjr ="";
    $tahunproduksiBjr ="";
    $databjr = array();
        
    $d = array();
    $str9="select kodeorg,kelaspohon,bjr,tahunproduksi,periode from ".$this->dbname.".kebun_5bjr where kodeorg like '".$user['lokasitugas']."%'";
    $qBjr=$this->owlPDO->query($str9) or die(print " Gagal: ".PDOException::getMessage());
    $qBjr->setFetchMode(PDO::FETCH_OBJ);
    $count=0;
    while($bar9=$qBjr->fetch())	{
        
        $d['kodeorg'] = $bar9->kodeorg;
        $d['kelaspohon'] = ($bar9->kelaspohon==''?'0':$bar9->kelaspohon);
        $d['bjr'] = ($bar9->bjr==''?'0':$bar9->bjr);
        $d['tahunproduksi'] = $bar9->tahunproduksi;
        $d['periode'] = $bar9->periode;
        $databjr[] = $d;
            $count++;			 
    }
    
    $data['masterdata']['bjr'] = $databjr;
    
    #10 Kode Denda Panen===========
    $kodedenda ="";
    $deskripsi ="";
    $datakodedendapanen = array();
    $d = array();
    $str10="select id,kodedenda,deskripsi,satuan,lockjjg,nourut from ".$this->dbname.".kebun_5kodedendapanen where status = '1' order by nourut asc"; 
    $qKodeDenda=$this->owlPDO->query($str10) or die(print " Gagal: ".PDOException::getMessage());
    $qKodeDenda->setFetchMode(PDO::FETCH_OBJ);
    $count=0;
    while($bar10=$qKodeDenda->fetch())	{

        $d['iddenda'] 	= $bar10->id;
        $d['kodedenda'] = $bar10->kodedenda;
        $d['deskripsi'] = $bar10->deskripsi;
        $d['satuan'] = $bar10->satuan;
        $d['lockjjg'] = $bar10->lockjjg;
        $d['nourut'] = $bar10->nourut;
        $datakodedendapanen[] = $d;
        $count++;			 
    }
    $data['masterdata']['kodedendapanen'] = $datakodedendapanen;

    #11 Denda Panen===========
    $kodeorg ="";
    $kodedenda ="";
    $jenisdenda ="";
    $denda ="";
    $datadendapanen = array();
    
    $d = array();
    $str11="select kodeorg,kodedenda,jenisdenda,denda from ".$this->dbname.".kebun_5dendapanen where kodeorg='".$user['lokasitugas']."'"; 
    $qDenda=$this->owlPDO->query($str11) or die(print " Gagal: ".PDOException::getMessage());
    $qDenda->setFetchMode(PDO::FETCH_OBJ);
    $count=0;
    while($bar11=$qDenda->fetch())	{
        $d['kodeorg'] = $bar11->kodeorg;
        $d['kodedenda'] = ($bar11->kodedenda==''?0:$bar11->kodedenda);
        $d['jenisdenda'] = ($bar11->jenisdenda==''?0:$bar11->jenisdenda);
        $d['denda'] = ($bar11->denda==''?0:$bar11->denda);
        $datadendapanen[] = $d;
            $count++;			 
    }
    
    $data['masterdata']['dendapanen'] = $datadendapanen;
    
    #12 kelas Pohon===========
    $kelas ="";
    $basisbulan ="";
    $basishari ="";
    $nama ="";
    $datakelaspohon = array();
    /*
    $d = array();
    $str12="select kelas,basisbulan,basishari,nama from ".$this->dbname.".kebun_5kelaspohon"; 
    $qPhn=$this->owlPDO->query($str12) or die(print " Gagal: ".PDOException::getMessage());
    $qPhn->setFetchMode(PDO::FETCH_OBJ);
    $count=0;
    while($bar12=$qPhn->fetch())	{
        
        $d['kelas'] = $bar12->kelas;
        $d['basisbulan'] = ($bar12->basisbulan==''?0:$bar12->basisbulan);
        $d['basishari'] = ($bar12->basishari==''?0:$bar12->basishari);
        $d['nama'] = ($bar12->nama==''?0:$bar12->nama);
        $datakelaspohon[] = $d;
            $count++;			 
    }
    */
    $data['masterdata']['kelaspohon'] = $datakelaspohon;
    
    #13 get GPS Interval===========
    $gpsinterval = array();
    $d = array();
    $str12="select * from ".$this->dbname.".gps_interval"; 
    $qInt=$this->owlPDO->query($str12) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    $alo=0;
    while($bar13=$qInt->fetch())	{
        $interval=$bar13->interval;
        $alo=$bar13->enableupload;
        $d['interval'] = $bar13->interval;
        $d['enableupload'] = $bar13->enableupload;
        $gpsinterval[] = $d;
    }	 
    $interval=$interval==''?0:$interval;

    
    $data['masterdata']['gps'] = $gpsinterval;
    
    
    #14 get setup aproval
    
    $kdunitapv ="";
    $applikasiapv ="";
    $karidapv ="";
    $namakaryawanapv ="";
    $nikapv ="";	
    $setupaproval = array();
    
    $d = array();
    $str="select a.*,b.namakaryawan,b.nik from ".$this->dbname.".setup_approval a left join 
    ".$this->dbname.".datakaryawan b on a.karyawanid=b.karyawanid where namakaryawan is not null";
    $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $countapv=0;
    /* kodeunit	char(10)	 
    jenispersetujuan	varchar(100)	 
    level	int(2)	 
    karyawanid	int(10) unsigned zerofill	 
    departemen	char(10)	 
    tipekaryawan	char(10)	 
    jabatan	int(10) unsigned	 
    tipe */
    while($bar=$res->fetch()){
        $d['kodeunit']  	= $bar['kodeunit'];
        $d['jenispersetujuan']	= $bar['jenispersetujuan'];
        $d['level'] 		= $bar['level'];
        $d['applikasi'] 	= ''/* $bar['applikasi'] */;
        $d['karyawanid'] 	= $bar['karyawanid'];
        $d['namakaryawan'] 	= $bar['namakaryawan'];
        $d['nik'] 			= $bar['nik'];
        $setupaproval[] = $d;
        $countapv++;	
    }	
    
    $data['masterdata']['setupaproval'] = $setupaproval;
    
    ##15 log_prapoht
    $nopp ="";
    $tgl ="";
    $dibuat ="";
    $namadibuat ="";
    $close ="";
    $persetujuan1 ="";
    $persetujuan2 ="";
    $persetujuan3 ="";
    $persetujuan4 ="";
    $persetujuan5 ="";
    $namapersetujuan1 ="";
    $namapersetujuan2 ="";
    $namapersetujuan3 ="";
    $namapersetujuan4 ="";
    $namapersetujuan5 ="";				
    $hasilpersetujuan1 ="";
    $hasilpersetujuan2 ="";
    $hasilpersetujuan3 ="";
    $hasilpersetujuan4 ="";
    $hasilpersetujuan5 ="";
    $komentar1 ="";
    $komentar2 ="";
    $komentar3 ="";
    $komentar4 ="";
    $komentar5 ="";
    $tglp1 ="";
    $tglp2 ="";
    $tglp3 ="";
    $tglp4 ="";
    $tglp5 ="";
    
    $log_prapoht = array();
    $d = array();			
    /* 
    pt	char(10)	kode PT
    unit	char(10)	Kode Unit
    tipepp	enum('PR','SR')	 
    nopp	varchar(20)	 
    tanggal	date	 
    keterangan	varchar(50) NULL	 
    dibuat	int(10) unsigned zerofill	userid
    requester	int(10) unsigned zerofill	requester
    close	tinyint(4) [0]	0; belum seleai;1 sudah selesai di sisi user; 2:pp sudah bisa di PO
    ket_balik	text NULL	  */

    /* $nmkar=makeOption($this->dbname,'datakaryawan','karyawanid,namakaryawan');
    $str="SELECT * FROM ".$this->dbname.".log_prapoht where close=1  and 
        (requester='".$karyawanid."'  or dibuat='".$karyawanid."')
        ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,hasilpersetujuan5 ASC ";
    
    $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $countppht=0;
    while($bar=$res->fetch()){
        for($i=1;$i<=5;$i++){
            if($bar['tglp'.$i]==''){
                $bar['tglp'.$i]='0000-00-00';
            }
            if($bar['persetujuan'.$i]==''){
                $bar['persetujuan'.$i]='0000000000';
            }
            if($bar['komentar'.$i]==''){
                $bar['komentar'.$i]='-';
            }
        }
        
            $namapersetujuan1v=@$nmkar[$bar['persetujuan1']];
                if($namapersetujuan1v==''){
                    $namapersetujuan1v='-';
                }
            $namapersetujuan2v=@$nmkar[$bar['persetujuan2']];
                if($namapersetujuan2v==''){
                    $namapersetujuan2v='-';
                }
            $namapersetujuan3v=@$nmkar[$bar['persetujuan3']];
                if($namapersetujuan3v==''){
                    $namapersetujuan3v='-';
                }
            $namapersetujuan4v=@$nmkar[$bar['persetujuan4']];
                if($namapersetujuan4v==''){
                    $namapersetujuan4v='-';
                }
            $namapersetujuan5v=@$nmkar[$bar['persetujuan5']];
            if($namapersetujuan5v==''){
                $namapersetujuan5v='-';
            }
            
            $namadibuatv=@$nmkar[$bar['dibuat']];
            if($namadibuatv==''){
                $namadibuatv='-';
            }
        
        $d['nopp'] = $bar['nopp'];
        $d['tanggal'] = $bar['tanggal'];
        $d['dibuat'] = $bar['dibuat'];
        $d['namadibuat'] = $namadibuatv;
        $d['close'] = $bar['close'];
        $d['persetujuan1'] = $bar['persetujuan1'];
        $d['persetujuan2'] = $bar['persetujuan2'];
        $d['persetujuan3'] = $bar['persetujuan3'];
        $d['persetujuan4'] = $bar['persetujuan4'];
        $d['persetujuan5'] = $bar['persetujuan5'];
        $d['namapersetujuan1'] = $namapersetujuan1v;
        $d['namapersetujuan2'] = $namapersetujuan2v;
        $d['namapersetujuan3'] = $namapersetujuan3v;
        $d['namapersetujuan4'] = $namapersetujuan4v;
        $d['namapersetujuan5'] = $namapersetujuan5v;
        $d['hasilpersetujuan1'] = $bar['hasilpersetujuan1'];
        $d['hasilpersetujuan2'] = $bar['hasilpersetujuan2'];
        $d['hasilpersetujuan3'] = $bar['hasilpersetujuan3'];
        $d['hasilpersetujuan4'] = $bar['hasilpersetujuan4'];
        $d['hasilpersetujuan5'] = $bar['hasilpersetujuan5'];
        $d['komentar1'] = $bar['komentar1'];
        $d['komentar2'] = $bar['komentar2'];
        $d['komentar3'] = $bar['komentar3'];
        $d['komentar4'] = $bar['komentar4'];
        $d['komentar5'] = $bar['komentar5'];
        $d['tglp1'] = $bar['tglp1'];
        $d['tglp2'] = $bar['tglp2'];
        $d['tglp3'] = $bar['tglp3'];
        $d['tglp4'] = $bar['tglp4'];
        $d['tglp5'] = $bar['tglp5'];	
        $log_prapoht[] = $d;
        
        $countppht++;
    } */
    
    $data['masterdata']['prht'] = $log_prapoht;
    
    ##16 log_prapodt
    $log_prapodt = array();
    $d = array();
    
    /* $nmbrg=makeOption($this->dbname,'log_5masterbarang','kodebarang,namabarang');
    $str="SELECT nopp,kodebarang,jumlah,satuanpp,status,keterangan,alasanstatus,ditolakoleh from ".$this->dbname.".log_prapodt where nopp in 
        (select nopp FROM ".$this->dbname.".log_prapoht where close=1  and 
        ((persetujuan1='".$karyawanid."' or
            persetujuan2='".$karyawanid."' or
            persetujuan3='".$karyawanid."' or
            persetujuan4='".$karyawanid."' or
            persetujuan5='".$karyawanid."') or dibuat='".$karyawanid."')) ";
    
    $noppppdt ="";
    $kdbrgppdt ="";
    $nmbrgppdt ="";
    $jumlahppdt ="";
    $satuanppppdt ="";
    $statusppdt ="";
    $keteranganppdt ="";
    $alasanstatusppdt ="";
    $ditolakolehppdt ="";
    
    $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $countppdt=0;
    while($bar=$res->fetch()){
        
        if($bar['alasanstatus']==''){
            $bar['alasanstatus']='-';
        }
        if( $bar['ditolakoleh']==''){
            $bar['ditolakoleh']='0000000000';
        }
        if($bar['keterangan']==''){
            $bar['keterangan']='-';
        }
        
        $d['nopp']			= $bar['nopp'];
        $d['kodebarang']	= $bar['kodebarang'];
        $d['namabarang']	= $nmbrg[$bar['kodebarang']];
        $d['jumlah'] 		= $bar['jumlah'];
        $d['satuanpp'] 		= $bar['satuanpp'];
        $d['status'] 		= $bar['status'];
        $d['keterangan'] 	= $bar['keterangan'];
        $d['alasanstatus'] 	= $bar['alasanstatus'];
        $d['ditolakoleh'] 	= $bar['ditolakoleh'];
        $log_prapodt[] = $d;
        $countppdt++;
    } */
    
    $data['masterdata']['prdt'] = $log_prapodt;
    
    
##17 sync purchaser
#diambil dr log_prapodt left join ke datakaryawan untuk ambil nama
    $syncpur ="";
    $syncnmpur ="";
    $syncbagpur ="";
    $syncnikpur ="";
    $synctpkarpur ="";
    // $str="SELECT distinct(purchaser) as purchaser,namakaryawan
    // FROM ".$this->dbname.".log_prapodt a left join ".$this->dbname.".datakaryawan b on a.purchaser=b.karyawanid
    // where purchaser!='0000000000'";

    #purch		
    $purch 	= array();
    $d		= array(); 
    
    $str="SELECT distinct(purchaser) as purchaser,namakaryawan,bagian,nik,tipekaryawan
    FROM ".$this->dbname.".log_prapodt a left join ".$this->dbname.".datakaryawan b on a.purchaser=b.karyawanid
    where purchaser!='0000000000'";
    $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        
        $d['karyawanid'] = $bar['purchaser'];
        $d['namakaryawan'] = $bar['namakaryawan'];
        $d['bagian'] = $bar['bagian'];
        $d['nik'] = $bar['nik'];
        $d['tipekaryawan'] = $bar['tipekaryawan'];
        
        $purch[] = $d;
    }
    
    #hrd
    
    $d		= array(); 
    $str="select karyawanid, namakaryawan,bagian,nik,tipekaryawan from ".$this->dbname.".datakaryawan where 
    (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') 
    and bagian in ('HRD','HHRD','HRA') and karyawanid!='".$karyawanid."'";		

    $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $d['karyawanid'] = $bar['karyawanid'];
        $d['namakaryawan'] = $bar['namakaryawan'];
        $d['bagian'] = $bar['bagian'];
        $d['nik'] = $bar['nik'];
        $d['tipekaryawan'] = $bar['tipekaryawan'];
        $purch[] = $d;
    }
    
    #atasan
    $d		= array(); 
    $str="select karyawanid,namakaryawan,bagian,nik,tipekaryawan from ".$this->dbname.".datakaryawan where 
    (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') 
    and karyawanid!='".$karyawanid."'";				
    $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $d['karyawanid'] = $bar['karyawanid'];
        $d['namakaryawan'] = $bar['namakaryawan'];
        $d['bagian'] = $bar['bagian'];
        $d['nik'] = $bar['nik'];
        $d['tipekaryawan'] = $bar['tipekaryawan'];
        $purch[] = $d;
    }
    
    $data['masterdata']['purchaser'] = $purch;
    
##18 sync sdm_ijin untuk persetujuan
#diambil dr sdm_ijin left join ke datakaryawan untuk ambil nama

    $karidsdmizin ="";
    $nmkarsdmizin ="";
    $tglsdmizin ="";
    $kepsdmizin ="";
    $ketsdmizin ="";
    $setujusdmizin ="";
    $nmsetujusdmizin ="";
    $stsetujusdmizin ="";
    $komensetujusdmizin ="";
    $waktusdmizin ="";
    $jenissdmizin ="";
    $hrdsdmizin ="";
    $nmhrdsdmizin ="";
    $sthrdsdmizin ="";
    $thnsdmizin ="";
    $darisdmizin ="";
    $sampaisdmizin ="";
    $jumhrsdmizin ="";
    $komenhrdsdmizin ="";
    $sdm_ijin = array();
    $d = array();

    $str="select * from ".$this->dbname.".sdm_ijin where 1=1 and (((persetujuan1='".$karyawanid."' or hrd='".$karyawanid."') and (stpersetujuan1=0 or stpersetujuanhrd=0))
    or karyawanid='".$karyawanid."')";
    $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $countsdmizin=0;
    while($bar=$res->fetch()){

        if($bar['keperluan']==''){
            $bar['keperluan']='-';
        }
        if($bar['keterangan']==''){
            $bar['keterangan']='-';
        }
        if($bar['komenst1']==''){
            $bar['komenst1']='-';
        }
        if($bar['komenst2']==''){
            $bar['komenst2']='-';
        }

        $d['karyawanid'] = $bar['karyawanid'];
        //$d['namakaryawan'] = $nmkar[$bar['karyawanid']];
        $d['tanggal'] = $bar['tanggal'];
        $d['keperluan'] = $bar['keperluan'];
        $d['keterangan'] = $bar['keterangan'];
        $d['persetujuan1'] = $bar['persetujuan1'];
        //$d['namapersetujuan1'] = $nmkar[$bar['persetujuan1']];
        $d['stpersetujuan1'] = $bar['stpersetujuan1'];
        $d['komenst1'] = $bar['komenst1'];
        $d['waktupengajuan'] = $bar['waktupengajuan'];

        $d['jenisijin'] = $bar['jenisijin'];
        $d['hrd'] = $bar['hrd'];
        //$d['namahrd'] = $nmkar[$bar['hrd']];
        $d['stpersetujuanhrd'] = $bar['stpersetujuanhrd'];
        $d['periodecuti'] = $bar['periodecuti'];

        $d['darijam'] = $bar['darijam'];
        $d['sampaijam'] = $bar['sampaijam'];
        $d['jumlahhari'] = $bar['jumlahhari'];
        $d['komenst2'] = $bar['komenst2'];
        $sdm_ijin[] = $d;

        $countsdmizin++;
    }

    $data['masterdata']['ijin'] = $sdm_ijin;


    #19 sync sdm_pjdinasht untuk persetujuan perdin
    #diambil dr sdm_pjdinasht left join ke datakaryawan untuk ambil nama
        $notranperdin ="";
        $karidsdmperdin ="";
        $nmkarsdmperdin ="";
        $tglbuatperdin ="";
        $tgl1perdin ="";
        $kodeorgsdmperdin ="";
        $tujuan1sdmperdin ="";
        $tugas1sdmperdin ="";
        $tujuan2sdmperdin ="";
        $tugas2sdmperdin ="";
        $tujuan3sdmperdin ="";
        $tugas3sdmperdin ="";
        $tujuanlainsdmperdin ="";
        $tugaslainsdmperdin ="";
        $pesawatsdmperdin ="";
        $daratsdmperdin ="";
        $lautsdmperdin ="";
        $messsdmperdin ="";
        $hotelsdmperdin ="";
        $tgl2sdmperdin ="";
        $hrdsdmperdin ="";
        $nmhrdsdmperdin ="";
        $statussdmperdin ="";
        $tglhrdsdmperdin ="";
        $uangsdmperdin ="";
        $uangtempsdmperdin ="";
        
        //$sdm_pjdinasht = array();
    // $d = array();

    // $str="select * from ".$this->dbname.".sdm_pjdinasht where 1=1 and ((namahrd='".$karyawanid."' and statushrd=0)
    // 	or karyawanid='".$karyawanid."')";
    // $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    // $res->setFetchMode(PDO::FETCH_ASSOC);
    // $countsdmperdin=0;
    // while($bar=$res->fetch()){

    // 	if($bar['tujuan1']==''){
    // 		$bar['tujuan1']='-';
    // 	}
    // 	if($bar['tugas1']==''){
    // 		$bar['tugas1']='-';
    // 	}
    // 	if($bar['tujuan2']==''){
    // 		$bar['tujuan2']='-';
    // 	}
    // 	if($bar['tugas2']==''){
    // 		$bar['tugas2']='-';
    // 	}
    // 	if($bar['tujuan3']==''){
    // 		$bar['tujuan3']='-';
    // 	}
    // 	if($bar['tugas3']==''){
    // 		$bar['tugas3']='-';
    // 	}
    // 	if($bar['tujuanlain']==''){
    // 		$bar['tujuanlain']='-';
    // 	}
    // 	if($bar['tugaslain']==''){
    // 		$bar['tugaslain']='-';
    // 	}
        
    // 	$d['notransaksi'] = $bar['notransaksi'];
    // 	$d['karyawanid'] = $bar['karyawanid'];
    // 	$d['namakaryawan'] = $nmkar[$bar['karyawanid']];
    // 	$d['tanggalbuat'] = $bar['tanggalbuat'];
    // 	$d['tanggalperjalanan'] = $bar['tanggalperjalanan'];
    // 	$d['kodeorg'] = $bar['kodeorg'];
    // 		$d['tujuan1'] = $bar['tujuan1'];
    // 		$d['tugas1'] = $bar['tugas1'];
    // 	$d['tujuan2'] = $bar['tujuan2'];
    // 	$d['tugas2'] = $bar['tugas2'];
    // 		$d['tujuan3'] = $bar['tujuan3'];
    // 		$d['tugas3'] = $bar['tugas3'];
    // 	$d['tujuanlain'] = $bar['tujuanlain'];
    // 	$d['tugaslain'] = $bar['tugaslain'];
    // 	$d['pesawat'] = $bar['pesawat'];
    // 	$d['darat'] = $bar['darat'];
    // 	$d['laut'] = $bar['laut'];
    // 	$d['mess'] = $bar['mess'];
    // 	$d['hotel'] = $bar['hotel'];
    // 	$d['tanggalkembali'] = $bar['tanggalkembali'];
    // 	$d['hrd'] = $bar['hrd'];
    // 	$d['namahrd'] = $nmkar[$bar['hrd']];
    // 	$d['statushrd'] = $bar['statushrd'];
    // 	$d['tanggalhrd'] = $bar['tanggalhrd'];
    // 	$d['uangmuka'] = $bar['uangmuka'];
    // 	$d['uang'] = '0';
    // 	$sdm_pjdinasht[] = $d;
    // 	$countsdmperdin++;
    // }

        //$data['masterdata']['pjdinas'] = $sdm_pjdinasht;	

    ##20 kebun_5gudangtransaksi ===========
        $kodedenda = "";
        $deskripsi = "";
        $datagudang = array();
        $d = array();

        $str20="select afdeling,kodegudang,status from ".$this->dbname.".kebun_5gudangtransaksi "; 
        $qdatagudang=$this->owlPDO->query($str20) or die(print " Gagal: ".PDOException::getMessage());
        $qdatagudang->setFetchMode(PDO::FETCH_OBJ);
        $count=0;
        while($bar20=$qdatagudang->fetch())	{
            if($bar20->status == 1){
                $d['afdeling'] 		= $bar20->afdeling;
                $d['kodegudang'] 	= $bar20->kodegudang;
                $d['status'] 		= $bar20->status;
                $datagudang[] 		= $d;
            }
        }
        $data['masterdata']['gudangtransaksi'] = $datagudang;
    ##21 kegiatannorma ===========
        $getsetup_kegiatannorma = "SELECT kodeorg, kodekegiatan, kelompok, kodebarang FROM ".$this->dbname.".setup_kegiatannorma";
        $quList=$this->owlPDO->query($getsetup_kegiatannorma) or die(print " Gagal: ".PDOException::getMessage());
        $quList->setFetchMode(PDO::FETCH_OBJ);
        $datasetup_kegiatannorma  = [];
        while($rList=$quList->fetch())
        {
            $datasetup_kegiatannorma [] = $rList;
        }
        $data['masterdata']['setup_kegiatannorma'] = $datasetup_kegiatannorma;

    ##22 Klasifikasi ===========
        $klasifikasi = array();
    /*
    $d = array();
    $str21="select distinct kodeklasifikasi,namaklasifikasi,tipeklasifikasi from ".$this->dbname.".sdm_klasifikasi";
    $qInt=$this->owlPDO->query($str21) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    $alo=0;
    while($bar22=$qInt->fetch())	{
            $d['kodeklasifikasi'] = $bar22->kodeklasifikasi;
            $d['namaklasifikasi'] = $bar22->namaklasifikasi;
            $d['tipeklasifikasi'] = $bar22->tipeklasifikasi;
            $klasifikasi[] = $d;
        }	 */
        $data['masterdata']['klasifikasi'] = $klasifikasi;	

    ##23 userPin ===========
        $userPin = array();
        $d = array();
    /*
    $str23="select * from ".$this->dbname.".security_token where status='1' and permission = 'signature'";
    $qInt=$this->owlPDO->query($str23) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    $alo=0;
    while($bar23=$qInt->fetch())	{
            $d['karyawanid'] 	= $bar23->param_id;
            $d['token'] 		= $bar23->token;
            $userPin[] 		= $d;
        }	 */
        $data['masterdata']['userpin'] = $userPin;	

    ##24 kebun_5mandor ===========
        $kebun_5mandor = array();

        $d = array();
        // $str="select mandorid,karyawanid from ".$this->dbname.".kebun_5mandor where statusaktif = '1' ";
        $str=  "select a.mandorid, a.karyawanid
        from kebun_5mandor a
        left join datakaryawan b on a.karyawanid=b.karyawanid
        where statusaktif = '1' and b.lokasitugas = '".$user['lokasitugas']."'";
        $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $qInt->setFetchMode(PDO::FETCH_OBJ);
        $alo=0;
        while($bar=$qInt->fetch())	{
            $d['mandorid'] 	= $bar->mandorid;
            $d['karyawanid']	= $bar->karyawanid;
            $kebun_5mandor[]	= $d;
        }	 
        $data['masterdata']['kebun_5mandor'] = $kebun_5mandor;	

    ##25 kebun_5mandor_blok ===========
        $kebun_5mandor_blok = array();
    /*
    $d = array();
    $str="select mandorid,kodeorg from ".$this->dbname.".kebun_5mandor_blok where statusaktif = '1' ";
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    $alo=0;
    while($bar=$qInt->fetch())	{
            $d['mandorid'] 		= $bar->mandorid;
            $d['kodeorg']			= $bar->kodeorg;
            $kebun_5mandor_blok[]	= $d;
    }	 
    */
    $data['masterdata']['kebun_5mandor_blok'] = $kebun_5mandor_blok;	

    ##26 SPK =========== 
    $KodeKegiatan = array();
    $SPK = array();
    /*
    $str="select kodekegiatan from ".$this->dbname.".setup_kegiatan where namakegiatan like '%Pruning%'"; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $KodeKegiatan[] = $bar->kodekegiatan;
    }
    $lokasitugas = $user['lokasitugas'];
    $divisi = $user['subbagian'];
    //exit('error:'.count($KodeKegiatan));
    $d = array();
    if(count($KodeKegiatan) > 0){
        $AllKodeKegiatan = "'".implode("','",$KodeKegiatan)."'";
        $str="select a.*,b.koderekanan,c.namasupplier,b.divisi,b.kodeorg,b.dari,b.sampai from ".$this->dbname.".log_spkdt a  
        left join ".$this->dbname.".log_spkht b on a.notransaksi = b.notransaksi
        left join ".$this->dbname.".log_5supplier c on b.koderekanan = c.supplierid
        where b.kodeorg = '".$lokasitugas."' and b.posting = '0' and b.dari<='".date('Y-m-d')."' and b.sampai>='".date('Y-m-d')."' and kodekegiatan in (".$AllKodeKegiatan.")";
        $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $qInt->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$qInt->fetch()){
            $d['notransaksi']		= $bar->notransaksi;
            $d['supplierid']		= $bar->koderekanan;
            $d['namasupplier']		= $bar->namasupplier;
            $d['kodeorg'] 			= $bar->kodeorg;
            $d['kodekegiatan'] 		= $bar->kodekegiatan;
            $d['divisi']			= $bar->divisi;
            $d['kodeblok']			= $bar->kodeblok;
            $d['satuan']			= $bar->satuan;
            $d['dari']				= $bar->dari;
            $d['sampai']			= $bar->sampai;
            $SPK[]					= $d;
        }	 
    }
    */
    $data['masterdata']['log_spk'] = $SPK;	

    ##27 setup_mutu =========== 
    $setup_mutu = array();
    $str="select * from ".$this->dbname.".kebun_5jenismutu "; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['idjenis']	= $bar->idjenis;
        if(@$bar->kode){
            $d['kodemutu']	= $bar->kode;
        }else{
            $d['kodemutu'] = '';
        }
        $d['jenis']		= $bar->jenis;
        $d['namamutu']	= $bar->kriteria;
        $d['satuan']	= $bar->satuan;
        $d['satuan2']	= $bar->satuan2;
        $setup_mutu[] 	= $d;
    }
    $data['masterdata']['setup_mutu'] = $setup_mutu;

    ##28 setup_hama =========== 
    $setup_hama = array();
    $str="select * from ".$this->dbname.".kebun_5jenishama "; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        // $d['idhama']	= $bar->id;
        $d['kodehama']	= $bar->kodehama;
        $d['namahama']	= $bar->namahama;
        $d['satuan']	= $bar->satuan;
        $setup_hama[] 	= $d;
    }
    $data['masterdata']['setup_hama'] = $setup_hama;

    ##29 kebun_5tph =========== 
    $setup_tph = array();
    $str="select * from ".$this->dbname.".kebun_5tph"; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['kode']	= $bar->kode;
        $d['keterangan']	= $bar->keterangan;
        $d['kodeorg']		= $bar->kodeorg;
        $d['latitude']		= $bar->latitude;
        $d['longitude']		= $bar->logitude;
        $d['luas']			= $bar->luas;
        $setup_tph[] 		= $d;
    }
    $data['masterdata']['setup_tph'] = $setup_tph;

    ##30 setup_grading =========== 
    $setup_grading = array();
    /*$str="select * from ".$this->dbname.".kebun_5grading where status = '1'"; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['kodegrading']	= $bar->kode;
        $d['namagrading']	= $bar->namakriteria;
        $setup_grading[] 		= $d;
    }*/
    $data['masterdata']['setup_grading'] = $setup_grading;

    ##31 data_version =========== 
    $data_version	= array();
    $data_detail 	= array();
    $str="select * from ".$this->dbname.".data_version order by updatetime DESC limit 1"; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['version']	= $bar->version;
        $data_version[]	= $d;
        $data_detail[] 	= $bar;
    }
    $data['masterdata']['data_version']	= $data_version;
    $data['masterdata']['data_app'] 	= $data_detail;

    ##32 kebun_5kemandoran =========== // setup kode mandor 
    $data_kebun_5kemandoran = array();
    /*$str="select * from ".$this->dbname.".kebun_5kemandoran where status = '1' "; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['kodemandor']	= $bar->kodemandor;
        $d['namamandor']	= $bar->namamandor;
        $data_kebun_5kemandoran[] 		= $d;
    }*/
    $data['masterdata']['kebun_5kemandoran'] = $data_kebun_5kemandoran;

    ##33 Parameter Applikasi =========== 
    $data_parameterappl = array();
    $str="select * from ".$this->dbname.".setup_parameterappl"; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $expnilai = explode(',',$bar->nilai);
        foreach($expnilai as $key=>$val){
            $d = array();
            $d['kodeaplikasi']	= $bar->kodeaplikasi;
            $d['kodeparameter']	= $bar->kodeparameter;
            $d['kodeorg']	= $bar->kodeorg;
            $d['keterangan']	= $bar->keterangan;
            $d['nilai']	= $val;
            $data_parameterappl[] 		= $d;
        }
    }
    $data['masterdata']['setup_parameterappl'] = $data_parameterappl;

    ##34 data karyawan baru =========== 
    $data_karyawanbaru = array();
    /*$str="select * from ".$this->dbname.".temp_datakaryawan where nik = '' "; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['karyawanid']		= $bar->karyawanid_temp;
        $d['no_identitas']		= $bar->no_identitas;
        $d['unicode']			= $bar->unicode;
        $d['div_code']			= $bar->div_code;;
        $d['nama']				= $bar->nama;
        $d['no_pemanen']		= $bar->no_pemanen;
        $data_karyawanbaru[]	= $d;
    }*/
    $data['masterdata']['datakaryawan_baru'] = $data_karyawanbaru;

    ##35 data karyawan baru =========== 
    $where_uuid = "";
    // if(isset($this->request('uuid'))){
    // 	$where_uuid = " and deviceid = '".$this->request('uuid')."' ";
    // }
    $data_lasnospb = array();
    $year = date("Y");
    $str="select MAX(nospb) as lastnospb from ".$this->dbname.".kebun_spbht where DATE_FORMAT(tanggal,'%Y') = '".$year."' ".$where_uuid; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['lastnospb']		= $bar->lastnospb;
        $data_lasnospb[]	= $d;
    }
    $data['masterdata']['data_lastnospb'] = $data_lasnospb;


    ##37 komoditi =========== 
        

    $data_komoditi = array();
    $str="select * from ".$this->dbname.".pmn_4komoditi";
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['kodecustomer']	= $bar->kodecustomer;
        $d['kodebarang']	= $bar->kodebarang;
        $d['kodekomoditi']	= $bar->kodekomoditi;
        $data_komoditi[] = $d;
    }
    $data['masterdata']['pmn_4komoditi'] = $data_komoditi;


    ##36 data_version =========== 
    $data_setting_dev = array();
    $str="select a.id,a.name,IFNULL(b.value,a.default_value) as value from ".$this->dbname.".setting_developer_mobile a
    left join ".$this->dbname.".setting_dev_user b on b.setting_devid = a.id and namauser = '".$this->request('username')."'"; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['id']	= $bar->id;
        $d['name']	= $bar->name;
        $d['value']	= $bar->value;
        $d['updateby']	= $this->request('username');
        $data_setting_dev[] 		= $d;
    }
    $data['masterdata']['setting_developer'] = $data_setting_dev;

    ##37 Setup Status Sensus Pokok =========== 
    $setup_statusSensus = array();
    $str="select * from ".$this->dbname.".kebun_5statussensus WHERE status = 1"; 
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['code']		= $bar->code;
        $d['tipe']		= $bar->tipe;
        $d['nama']		= $bar->nama;
        $d['deskripsi']	= $bar->dekripsi;
        $d['warna']		= $bar->warna;
        $setup_statusSensus[] 	= $d;
    }

    $data['masterdata']['statusSensus'] = $setup_statusSensus;

    ##38 sdm_5absensi =========== 
    $sdm_5absensi = array();
    $str="select * from ".$this->dbname.".sdm_5absensi where status = '1' ";
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['kodeabsen']		= $bar->kodeabsen;
        $d['keterangan']	= $bar->keterangan;
        $d['kelompok']		= $bar->kelompok;
        // $d['kelompokcatu']	= $bar->kelompokcatu;
        $d['nilaihk']		= $bar->nilaihk;
        $d['pengali']		= $bar->pengali;
        $sdm_5absensi[] 	= $d;
    }
    $data['masterdata']['sdm_5absensi'] = $sdm_5absensi;

    ##39 template device fingerprint =========== 
    $fingerprint_template_server = array();
    $str="select * from ".$this->dbname.".fingerprint_template where template !='gagal' and kebun='".$user['lokasitugas']."'";
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['sn']			= $bar->sn;
        $d['sensor']		= $bar->sensor;
        $d['id_jari']		= $bar->id_jari;
        $d['kebun']			= $bar->kebun;
        $d['template']		= $bar->template ;
        $d['updateby']		= $bar->updateby;
        $d['karyawanid']	= $bar->karyawanid;
        $fingerprint_template_server[] 	= $d;
    }
    $data['masterdata']['fingerprint_template_server'] = $fingerprint_template_server;

    ##39 Docket Tiket Print =========== 
    $tiket_docket = array();
    $str="select * from ".$this->dbname.".tiket_docket_flag where flag='0'";
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['notransaksi']	= $bar->notransaksi;
        // $d['karyawanid']	= $bar->karyawanid;
        // $d['tipekary']		= $bar->tipekary;
        $d['tph']			= $bar->tph;
        $d['kebun']			= $bar->tph;
        $d['nik']			= $bar->nik;
        $d['sesi']			= $bar->sesi;
        $d['updateby']		= $bar->updateby;
        $tiket_docket[] 	= $d;
    }
    $data['masterdata']['tiket_docket'] = $tiket_docket;


    ##40 PJD DINAS HT =========== 
    $sdm_pjdinasht = array();
    $str="SELECT * FROM ".$this->dbname.".sdm_pjdinasht where 1=1  and karyawanid='".$karyawanid."' and kodeorg='".$user['lokasitugas']."' and statuspengajuan=1 and statusrealisasi=0 and tgldinassampaireal >= CURRENT_DATE() order by createtime desc";
    // echo $str;
    // exit('hello');
    $qInt=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $qInt->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$qInt->fetch()){
        $d = array();
        $d['notransaksi']		= $bar->notransaksi;
        $d['synMobile']			= $bar->synMobile;
        $d['karyawanid']		= $bar->karyawanid;
        $d['tipekary']			= $bar->tipekary;
        $d['level']				= $bar->level;
        $d['kodeorg']			= $bar->kodeorg	;
        $d['pttujuan']			= $bar->pttujuan;
        $d['unittujuan']		= $bar->unittujuan;
        $d['regiontujuan']		= $bar->regiontujuan;
        $d['statuspengajuan']	= $bar->statuspengajuan;
        $d['statusrealisasi']	= $bar->statusrealisasi;
        $d['namahrd']			= $bar->namahrd;
        $d['createdby']			= $bar->createdby;
        $d['keterangan']		= $bar->keterangan;
        $d['tgldinasdari']		= $bar->tgldinasdari;
        $d['tgldinassampai']	= $bar->tgldinassampai;
        $d['tgldinasdarireal']	= $bar->tgldinasdarireal;
        $d['tgldinassampaireal']= $bar->tgldinassampaireal;
        $d['tiket']= $bar->tiket;
        $sdm_pjdinasht[] 	= $d;
    }
    $data['masterdata']['sdm_pjdinasht'] = $sdm_pjdinasht;
        
        
        //insertLogMobile('Synchronize Data');
        return $data;
    }

}
?>