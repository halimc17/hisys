<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');

$proses = checkPostGet('proses','');
$param = ($_POST=='' ? $_GET : $_POST);

switch($proses) {
    case 'gantikegiatan':
        $kodekegiatan = $param['kodekegiatan'];

        // samain dari case 'showDetail':
                $whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan in('2','3','4','6')";
                $whereKary .= " and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
                $whereKaryBukanPemanen .= " and kodejabatan != '45'"; // selain pemanen

                // kegiatan tunas
                $kegiatantunas=false;
                //$str="SELECT kodekegiatan,namakegiatan,satuan FROM ".$dbname.".setup_kegiatan 
                //    where namakegiatan like '%tunas%' or namakegiatan like '%kastrasi%'"; // ambil kegiatan tunas dan kastrasi (126100101, 126100201, 126100301, 621050101, 621090901)
                $str="SELECT kodekegiatan,namakegiatan,satuan FROM ".$dbname.".setup_kegiatan 
                        where namakegiatan like '%tunas pokok%' and status = '1'"; // ambil kegiatan tunas pokok saja (126100101, 621050101)
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch())
                {
                        if($kodekegiatan==$bar->kodekegiatan)$kegiatantunas=true;
                }

                // cek regional, KALO KALTIM BOLEH SEMUA
                $str="select * from ".$dbname.".bgt_regional_assignment 
                        where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch())
                {
                        $regional=$bar->regional;
                }
                if($regional=='KALTIM')$kegiatantunas=true;

                if(!$kegiatantunas) {
                        // kalo tunas, pemanen boleh ada
                        // selain tunas, pemanen ga boleh
                        $whereKary.=$whereKaryBukanPemanen;    
                }
                $query = selectQuery($dbname,'datakaryawan','karyawanid,namakaryawan,subbagian,nik',$whereKary,'namakaryawan');
                $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch())
                {
                    // $optKbn.="<option value=".$bar->karyawanid.">".$bar->namakaryawan." - ".$bar->subbagian."</option>";
					$optKbn .= "<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
                } 
                echo $optKbn;    
                break;

    case 'cegatKegiatan': // by dz March 11, 2014
        // jika ada perubahan, ganti juga di log_slave_realisasispk_detail
        $kegiatan = $param['kodekegiatan'];
        $kodeorg = $param['kodeorg'];
        $hasilkerja = $param['hasilkerja'];
        $notransaksi = $param['notransaksi'];

        // cek hasil kerja ga boleh 0
        if($hasilkerja==0){
            echo "error: ".$_SESSION['lang']['hasilkerjad']." = 0.";
            exit();
        }
		
		if($kodeorg==''){
           exit("Error:Warning Blok masih kosong");
        }

        // ambil kode parameter kegiatan
        $where = "nilai = '".$kegiatan."'";
        $cols = "kodeparameter";
        $query = selectQuery($dbname,'setup_parameterappl',$cols,$where);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
		$kodeparameter="";
        while($bar=$res->fetch())
        {
            $kodeparameter=$bar->kodeparameter;
        }
        $luasareanonproduktif=0;
        $jumlahpokok=0;
        $luasareaproduktif=0;

        // kalo kegiatan tanam, cek. kalo luas blok = luas kerangka tidak bisa.
        $where = "kodeorg = '".$kodeorg."'";
        $cols = "luasareanonproduktif,jumlahpokok,luasareaproduktif";
        $query = selectQuery($dbname,'setup_blok',$cols,$where);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $luasareanonproduktif=$bar->luasareanonproduktif;
            $jumlahpokok=$bar->jumlahpokok;
            $luasareaproduktif=$bar->luasareaproduktif;
        }
        @$sph=($jumlahpokok+$hasilkerja)/$luasareaproduktif;
        $maxtanam=$luasareanonproduktif*150;

        // ambil periode
        $where = "notransaksi = '".$notransaksi."'";
        $cols = "tanggal";
        $query = selectQuery($dbname,'kebun_aktifitas',$cols,$where);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $tanggal=$bar->tanggal;
        }        

        // kalo kegiatan sisip, cek. kalo sisa rencanasisip-udahsisip<=0 tidak bisa.
        // ambil rencana sisip s/d pada tahun berjalan
        $where = "blok = '".$kodeorg."' and periode <= '".substr($tanggal,0,7)."' and substr(periode,1,4) = '".substr($tanggal,0,4)."' and posting ='1'";
        $cols = "sum(rencanasisip) as rencanasisip";
        $query = selectQuery($dbname,'kebun_rencanasisip',$cols,$where);
        $rencanasisip=0;
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $rencanasisip+=$bar->rencanasisip;
        }

        $where = "notransaksi = '".$notransaksi."'";
        $cols = "tanggal";
        $query = selectQuery($dbname,'kebun_aktifitas',$cols,$where);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $tanggal=$bar->tanggal;
        }

        // ambil jumlah sisip
                $sudahsisip=0;

        // BKM
        $query="select kodeorg,sum(hasilkerja)as telahsisip from ".$dbname.".kebun_perawatan_vw 
            where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeorg = '".$kodeorg."' and tanggal >= '".$tanggal."' and tanggal like '".substr($tanggal,0,4)."%'";        
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $sudahsisip+=$bar->telahsisip;
        }
        // PERAWATAN
        $query="select kodeblok,sum(hasilkerjarealisasi)as telahsisip from ".$dbname.".log_baspk 
            where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeblok = '".$kodeorg."' and tanggal >= '".$tanggal."' and tanggal like '".substr($tanggal,0,4)."%'";        
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $sudahsisip+=$bar->telahsisip;
        }

        $sisasisip=$rencanasisip-($sudahsisip+$hasilkerja);       

        if(substr($kodeparameter,0,5)=='TANAM'){
            if($hasilkerja>$maxtanam) {
                echo "error: Tidak bisa tanam baru, luas yang belum ditanam: ".number_format($luasareanonproduktif,2)." Ha, pokok bisa ditanam: ".number_format($maxtanam).". Jumlah ditanam: ".number_format($hasilkerja).".";
                exit();
            }
        }
        if(substr($kodeparameter,0,5)=='COMPL'){
            if($sph>150) {
                echo "error: SPH setelah transaksi lebih dari 150: ".number_format($sph,2).".";
                exit();
            }
        } 
        if(substr($kodeparameter,0,5)=='SISIP'){
            if($sisasisip < 0) {
                echo "error: Harap diinput data pokok mati dan rencana sisipan, \nrencana sisip: ".$rencanasisip.", \nsudah sisip: ".$sudahsisip." + ".$hasilkerja.", \nsisa rencana sisip: ".$sisasisip.".";
                exit();
            } elseif($sisasisip > 0) {
                                echo "Message: Rencana sisip tersisa: ".$sisasisip;
                        } elseif($rencanasisip >0 and $sisasisip == 0) {
                                echo "Message: Rencana Sisip sudah selesai dilakukan. Silahkan buat BA Penyisipan";
                        }
        }
                break;

    case 'cekSisip': // by dz March 13, 2012
        $kegiatan = $param['kodekegiatan'];
        $where = "nilai = '".$kegiatan."' and kodeparameter like 'SISIP%' and kodeaplikasi = 'TN'"; // kalo kodeparameter SISIP
        $cols = "kodeaplikasi";
        $query = selectQuery($dbname,'setup_parameterappl',$cols,$where);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $kodeaplikasi=$bar->kodeaplikasi;
        }
        echo $kodeaplikasi;        
                break;

    case 'saveSisip': // by dz March 15, 2012
                $notrans = $param['notrans'];
        $kodeorg = $param['kodeorg'];
        $jumlah = $param['jumlah'];
        $penyebab = $param['penyebab'];
        $where = "notransaksi = '".$notrans."'";
        $cols = "tanggal";
        $query = selectQuery($dbname,'kebun_aktifitas',$cols,$where);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $tanggal=$bar->tanggal;
        }
        $qwe="INSERT INTO `".$dbname."`.`kebun_sisip` (`notransaksi` ,`tanggal` ,`kodeorg` ,`jumlah` ,`penyebab`)
        VALUES ('".$notrans."', '".$tanggal."', '".$kodeorg."', '".$jumlah."', '".$penyebab." ')";
        try{$owlPDO->exec($qwe); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";}
                break;

    case 'showDetail':
		#== Prep Tab
		$headFrame = array(
				$_SESSION['lang']['prestasi'],
				$_SESSION['lang']['absensi'],
				$_SESSION['lang']['material']
		);
$contentFrame = array();

$blokStatus = $_SESSION['tmp']['actStat'];

# Options
//$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan<>0";
$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan in('1','2','3','4','6')";
$whereKary .= " and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
$whereKaryBukanPemanen = " and kodejabatan != '45'"; // selain pemanen

		$whereKeg = "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and status='1' and ";
		switch($blokStatus) {
			case 'lc':
			$whereKeg = "kelompok in ('TB','TBM') and status='1'";
			$whereBlok = " and statusblok='TB' ";
			break;
			case 'bibit':
			$whereKeg = "(kelompok='BBT' or kelompok='PN' or kelompok='MN') and status='1'";
			$whereBlok = " and statusblok='BBT' ";
			break;
			case 'tbm':
			$whereKeg = "(kelompok='TBM') and status='1'";
			$whereBlok = " and statusblok='TBM' ";
			break;
			case 'tm':
			$whereKeg = "kelompok='TM' and status='1'";
			$whereBlok = "and statusblok='TM'";
			break;
			default:
			break;
		}

        if($blokStatus=='bibit'){
           $whereOrg = " tipe='BIBITAN' and length(kodeorganisasi)>6 and left(kodeorganisasi,4)='".$param['afdeling']."'";
		   $whereDiv = " tipe='BIBITAN'";
		}
        else{    
                $whereOrg = " kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['afdeling']."' and luasareaproduktif>0 ".$whereBlok.")
                          and tipe='BLOK' and left(kodeorganisasi,4)='".$param['afdeling']."'";
				$whereDiv = " tipe='AFDELING'";
            //$whereOrg = "(tipe='BLOK') and left(kodeorganisasi,4)='".$param['afdeling']."'";

        }

		// cek kegiatan, samain dengan case 'gantikegiatan':
		$str="select kodekegiatan from ".$dbname.".kebun_prestasi
				where notransaksi LIKE '".$param['notransaksi']."' ";
		$kodekegiatan='';
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
				$kodekegiatan=$bar->kodekegiatan;
		}        

		$kegiatantunas=true;        
		// kegiatan tunas
		$str="SELECT kodekegiatan,namakegiatan,satuan FROM ".$dbname.".setup_kegiatan 
				where kodekegiatan like '621%' and namakegiatan like '%tunas pokok%' and status = '1'"; // ambil kegiatan tunas 621 aja
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
				if($kodekegiatan==$bar->kodekegiatan)$kegiatantunas=true;
		}

		// cek regional, KALO KALTIM BOLEH SEMUA
		$str="select * from ".$dbname.".bgt_regional_assignment 
				where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
				$regional=$bar->regional;
		}
		$kegiatantunas=true; //Munculkan semua jenis kegiatan termasuk Tunas Pokok edit 09032016

		if(!$kegiatantunas) { 
		// kalo tunas, pemanen boleh ada    
		// selain tunas, pemanen ga boleh
				$whereKary.=$whereKaryBukanPemanen;    
		}
        //exit('warning'.$whereKeg."___".$blokStatus);
		//bisst
        if($param['notransaksi']!=''){
            $sDr="select distinct lokasitugas from ".$dbname.".kebun_kehadiran a 
              left join ".$dbname.".datakaryawan b on a.nik=b.karyawanid
              where notransaksi='".$param['notransaksi']."' and lokasitugas!='".$_SESSION['empl']['lokasitugas']."'";
            $rDr=fetchdata($sDr);
            if(count($rDr)!=0){
                $dtIn.="(";
                foreach($rDr as $row=>$lstData){
                    if($row==0){
                        $dtIn.="'".$lstData['lokasitugas']."'";    
                    }else{
                        $dtIn.=",'".$lstData['lokasitugas']."'";    
                    }
                    
                }
                $dtIn.=")";
                $whereKary.=" or lokasitugas in ".$dtIn."";
            }    
        }
		$optKary = makeOption($dbname,'datakaryawan','karyawanid,nik,subbagian,namakaryawan',$whereKary,'6');
		if($_SESSION['language']=='EN'){
				$qKeg = selectQuery($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1,satuan',$whereKeg,"namakegiatan1");
		} else {
				$qKeg = selectQuery($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan',$whereKeg,"namakegiatan");
		}
		$resKeg = fetchData($qKeg);
		$optKeg = array();$satuan = array();
		foreach($resKeg as $row) {
				if($_SESSION['language']=='EN'){
						$optKeg[$row['kodekegiatan']] = $row['namakegiatan1']." (".$row['satuan'].") - ".$row['kodekegiatan'];
				} else {
						$optKeg[$row['kodekegiatan']] = $row['namakegiatan']." (".$row['satuan'].") - ".$row['kodekegiatan'];
				}
				$satuan[$row['kodekegiatan']] = $row['satuan'];
		}
		$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);
		$optAbs = makeOption($dbname,'sdm_5absensi','kodeabsen,keterangan','kodeabsen="H"');
		#$optOrg = getOrgBelow($dbname,$_SESSION['empl']['lokasitugas'],false,'kebun');
		$optBin = array('1'=>$_SESSION['lang']['yes'],'0'=>$_SESSION['lang']['no']);

		$lokasi=$_SESSION['empl']['lokasitugas'];
		$optDivisi = "<option value=''>".$_SESSION['lang']['all']."</option>";
		$sDiv="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$whereDiv." and induk='".$lokasi."'"; 
		$res=$owlPDO->query($sDiv) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bDiv=$res->fetch())
		{
				$optDivisi .= "<option value=".$bDiv->kodeorganisasi.">".$bDiv->namaorganisasi."</option>";
		}

		#================ Prestasi Tab =============================
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."'";
		$cols = "kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi";
		$query = selectQuery($dbname,'kebun_prestasi',$cols,$where);
		$data = fetchData($query);
		$dataShow = $data;

		// Masking Segment
		// $arrSegment = array();
		// foreach($data as $row) {
			// $arrSegment[$row['kodesegment']] = "'".$row['kodesegment']."'";
		// }
		// if(!empty($arrSegment)) {
			// $whereSegment = "kodesegment in (".implode(',',$arrSegment).")";
			// $optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',$whereSegment);
		// } else {
			// $optSegment = array();
		// }
		// $optSegment[''] = '';

		foreach($dataShow as $key=>$row) {
			#$dataShow[$key]['nik'] = $optKary[$row['nik']];
			$dataShow[$key]['kodekegiatan'] = $optKeg[$row['kodekegiatan']];
			$dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
			// $dataShow[$key]['kodesegment'] = $optSegment[$row['kodesegment']];
			$dataShow[$key]['satuan'] = $satuan[$row['kodekegiatan']];
			#$dataShow[$key]['pekerjaanpremi'] = $optBin[$row['pekerjaanpremi']];
		}
        
			# Form
			$theForm2 = new uForm('prestasiForm',$_SESSION['lang']['form'].' '.$_SESSION['lang']['prestasi'],2);
			if(!empty($data)){
					$theForm2->addEls('kodekegiatan',$_SESSION['lang']['kodekegiatan'],'','selectsearch','L',30,$optKeg);
					$theForm2->_elements[0]->_attr['onchange'] = 'gantikegiatan()';
					$theForm2->_elements[0]->_attr['disabled'] = 'disabled';
				
					$theForm2->addEls('kodeorg',$_SESSION['lang']['blok'],'','select','L',30,$optOrg);
					$theForm2->_elements[1]->_attr['onchange'] = 'changeOrg()';
					$theForm2->_elements[1]->_attr['title'] = 'Please choose block';
					$theForm2->_elements[1]->_attr['disabled'] = 'disabled';
			}else{
					$theForm2->addEls('kodekegiatan',$_SESSION['lang']['kodekegiatan'],'','selectsearch','L',30,$optKeg);
					$theForm2->_elements[0]->_attr['onchange'] = 'gantikegiatan()';
			
					$theForm2->addEls('kodeorg',$_SESSION['lang']['blok'],'','select','L',30,$optOrg);
					$theForm2->_elements[1]->_attr['onchange'] = 'changeOrg()';
					$theForm2->_elements[1]->_attr['title'] = 'Please choose block';
			}
			// $theForm2->addEls('kodesegment',$_SESSION['lang']['kodesegment'],'','searchSegment2','L',30,$optOrg);
			$theForm2->addEls('hasilkerja',$_SESSION['lang']['hasilkerjajumlah'],'0','textnumwsatuan','R',10);
			$theForm2->addEls('jumlahhk',$_SESSION['lang']['jumlahhk'],'0','textnum','R',10);
			$theForm2->_elements[3]->_attr['onfocus'] =
				"document.getElementById('tmpValHk').value = this.value";
			$theForm2->_elements[3]->_attr['onkeyup'] = "totalVal();cekVal(this,'Pres','Hk')";
			$theForm2->addEls('upahkerja',$_SESSION['lang']['upahkerja'],'0','textnum','R',10);
			$theForm2->_elements[4]->_attr['disabled'] = 'disabled';
			// $theForm2->addEls('umr',$_SESSION['lang']['umr'],'0','textnum','R',10);
			// $theForm2->_elements[5]->_attr['disabled'] = 'disabled';
			// $theForm2->_elements[5]->_attr['onfocus'] =
				// "document.getElementById('tmpValUmr').value = this.value";
			// $theForm2->_elements[5]->_attr['onkeyup'] = "totalVal();cekVal(this,'Pres','Umr')";
			$theForm2->addEls('upahpremi',$_SESSION['lang']['upahpremi'],'0','textnum','R',10);
			$theForm2->_elements[5]->_attr['disabled'] = 'disabled';
			$theForm2->_elements[5]->_attr['onfocus'] =
				"document.getElementById('tmpValIns').value = this.value";
			$theForm2->_elements[5]->_attr['onkeyup'] = "totalVal();cekVal(this,'Pres','Ins')";


			# Table
			$theTable2 = new uTable('prestasiTable',$_SESSION['lang']['tabel'].' '.$_SESSION['lang']['prestasi'],$cols,$data,$dataShow);

			# FormTable
			$formTab2 = new uFormTable('ftPrestasi',$theForm2,$theTable2,null,array('notransaksi'));
			$formTab2->_target = "kebun_slave_operasional_prestasi";
			$contentFrame[0] = "";
			if(!empty($data)) {
				$formTab2->_noEnable = '##upahkerja##umr##upahpremi##kodesegment##kodeorg##kodekegiatan';
				$formTab2->_noaction = false;
				// $formTab2->_afterEditMode = 'gantikegiatan';
				$theBlok = $data[0]['kodeorg'];
			} else {
				$theBlok = "";
				$contentFrame[0] ="<fieldset><div id='divDivisi' style='display:block'>Divisi : <select id=divisi onchange=getDivisi('ftPrestasi_kodeorg',this)>".$optDivisi."</select></div></fieldset>";
			}

			$contentFrame[0] .= $formTab2->prep();

		#================ Absensi Tab =============================
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."'";
		$cols = "nourut,nik,absensi,jhk,umr,insentif";
		$query = selectQuery($dbname,'kebun_kehadiran',$cols,$where);
		$data = fetchData($query);
		$dataShow = $data;

		foreach($dataShow as $key=>$row) {
			$dataShow[$key]['nik'] = $optKary[$row['nik']];
			$dataShow[$key]['absensi'] = $optAbs[$row['absensi']];
			$dataShow[$key]['umr'] = number_format($row['umr'],0);
		}

		#=============================== Get UMR ==============================
		$firstKary = getFirstKey($optKary);
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',"karyawanid='".$firstKary."' and tahun='".date('Y')."' and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
		#=============================== Get UMR ==============================

		# Form
		$theForm1 = new uForm('absensiForm',$_SESSION['lang']['form'].' '.$_SESSION['lang']['absensi'],2);
		$theForm1->addEls('nourut',$_SESSION['lang']['nourut'],'0','textnum','R',3);
		$theForm1->_elements[0]->_attr['disabled'] = 'disabled';
		$theForm1->addEls('nik',$_SESSION['lang']['nama'],'','selectsearch','L',30,$optKary);
		$theForm1->_elements[1]->_attr['onchange'] = 'updateUMR(this)';
		$theForm1->addEls('absensi',$_SESSION['lang']['absensi'],'H','select','L',10,$optAbs);
		$theForm1->addEls('jhk',$_SESSION['lang']['jhk'],'0','textnum','R',10);
		$theForm1->_elements[3]->_attr['onkeyup'] = "totalVal();cekVal(this,'Abs','Hk');updateUMR2()";
		$theForm1->addEls('umr',$_SESSION['lang']['umrhari'],0,'textnum','R',10);
		#$theForm1->_elements[4]->_attr['onkeyup'] = "totalVal();cekVal(this,'Abs','Umr')";
		$theForm1->_elements[4]->_attr['onkeyup'] = "totalVal();";
		$theForm1->addEls('insentif',$_SESSION['lang']['insentif'],'0','textnum','R',10);
		#$theForm1->_elements[5]->_attr['onkeyup'] = "totalVal();cekVal(this,'Abs','Ins')";
		$theForm1->_elements[5]->_attr['onkeyup'] = "totalVal();";

		# Table
		$theTable1 = new uTable('absensiTable',$_SESSION['lang']['tabel'].' '.$_SESSION['lang']['absensi'],$cols,$data,$dataShow);

		# FormTable
		$formTab1 = new uFormTable('ftAbsensi',$theForm1,$theTable1,null,array('notransaksi'));
		$formTab1->_target = "kebun_slave_operasional_absensi";
		$formTab1->_noEnable = '##nourut';
		//$formTab1->_defValue = '##umr='.($Umr[0]['nilai']/25).'##jhk=1';

		$contentFrame[1] ="<fieldset><table>
			<tr>
				<td><input type=checkbox id=filternikpt onclick=filterKaryawanPt('nik',this) title='Tampilkan semua karyawan di dalam satu PT'>Filter Kary Per PT</checkbox></td>
                <td><input type=checkbox id=filternik onclick=filterKaryawan('nik',this) title='Tampilkan karyawan di dalam satu divisi'>Filter Kary Per Divisi</checkbox></td>
				<td><input type=checkbox id=filtermandor onclick=filterMandor('nik',this) title='Tampilkan karyawan per kemandoran'>Filter Kary Per Mandor</checkbox></td>
			</tr>
		</table></fieldset>";

		$contentFrame[1] .= "<div id=myFrm1 style=''>
			".$formTab1->prep()."
		</div>
		<div id=myFrm2 style='display:none'>
		</div>";

		#================ Material Tab =============================
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."'";
		$cols = "kodeorg,kwantitasha,kodegudang,kodebarang,kwantitas";
		$query = selectQuery($dbname,'kebun_pakaimaterial',$cols,$where);
		$data = fetchData($query);

		if(!empty($data)) {
				$whereBarang = "";
				$i=0;
				foreach($data as $row) {
				if($i==0) {
						$whereBarang .= "kodebarang='".$row['kodebarang']."'";
				} else {
						$whereBarang .= " or kodebarang='".$row['kodebarang']."'";
				}
				$i++;
				}
				$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whereBarang);
		} else {
				$optBarang = array();
		}
								  $optGudang=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi'," kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe='GUDANGTEMP'");

		$dataShow = $data;
		foreach($dataShow as $key=>$row) {
				$dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
				$dataShow[$key]['kwantitasha'] = number_format($row['kwantitasha'],2);
				$dataShow[$key]['kodegudang'] = $optGudang[$row['kodegudang']];
				$dataShow[$key]['kodebarang'] = $optBarang[$row['kodebarang']];
				$dataShow[$key]['kwantitas'] = number_format($row['kwantitas'],2);
		}

		# Form
		$theForm3 = new uForm('materialForm',$_SESSION['lang']['form'].' '.$_SESSION['lang']['pakaimaterial'],2);
		$theForm3->addEls('kodeorg',$_SESSION['lang']['blok'],$theBlok,'select','L',30,$optOrg);
		$theForm3->_elements[0]->_attr['disabled'] = 'disabled';
		$theForm3->addEls('kwantitasha',$_SESSION['lang']['kwantitasha'],'0','textnum','R',10);        
		$theForm3->addEls('kodegudang',$_SESSION['lang']['pilihgudang'],'','select','L',30,$optGudang);
		$theForm3->_elements[2]->_attr['onchange'] = 'changeGudang()';
		$theForm3->_elements[2]->_attr['disabled'] = 'disabled';
		$theForm3->addEls('kodebarang',$_SESSION['lang']['kodebarang'],'','searchBarangGudang','L',20,null,null,null,null,'kodegudang','saldoMaterial');
		$theForm3->addEls('kwantitas',$_SESSION['lang']['kwantitas'],'0','textnum','R',10);
		//$theForm3->_elements[4]->_attr['onkeyup'] = 'cekSaldo()';


		# Table
		$theTable3 = new uTable('materialTable',$_SESSION['lang']['tabel'].' '.$_SESSION['lang']['pakaimaterial'],$cols,$data,$dataShow);

		# FormTable
		$formTab3 = new uFormTable('ftMaterial',$theForm3,$theTable3,null,array('notransaksi'));
		$formTab3->_target = "kebun_slave_operasional_material";
		$formTab3->_noClearField = '##kodebarang';
		$formTab3->_noEnable = '##kodebarang##kodeorg##kodegudang##kwantitas';
		// $formTab3->_numberFormat = '##kwantitas';

		$contentFrame[2] = $formTab3->prep();

		#== Display View
		# Draw Tab
		echo "<fieldset><legend><b>Detail</b></legend>";
		drawTab('FRM',$headFrame,$contentFrame,150,'100%');
		echo "<input type='hidden' id='saldoMaterial' value=0>";
		echo "<input type='hidden' id='satuan' value='".json_encode($satuan)."'>";
		echo "<input type='hidden' id='firstSatuan' value='".reset($satuan)."'>";
		echo "</fieldset>";
		break;

    case 'updateUMR':
                $firstKary = $param['nik'];
                $jhk = $param['jhk'];
                $tanggal = $param['tanggal'];

                // Ambil Gaji Pokok
                $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
                        "karyawanid=".$firstKary." and tahun=".$param['tahun']." and idkomponen in (1,31)");
                $Umr = fetchData($qUMR);

                // Standard UMR
                $stdUmr = $Umr[0]['nilai']/25;

                // Upah yang didapat
                @$zUmr=$jhk*$Umr[0]['nilai']/25;

                // Cek UMR di Panen
                $qPanen = selectQuery($dbname,'kebun_prestasi_vw','sum(upahkerja) as upah',
                                                          "karyawanid = '".$firstKary."' and tanggal = '".tanggalsystem($param['tanggal'])."'");
                $resPanen = fetchData($qPanen);
                $upahPanen = $resPanen[0]['upah'];

                // Sisa Upah setelah panen
                $sisaUpah = $stdUmr - $upahPanen;

                // Jika UMR
                if($zUmr > $sisaUpah) {
                        exit("Warning: Karyawan tersebut sudah bekerja di panen.\n".
                                "Sisa HK yang dapat digunakan adalah ".number_format($sisaUpah / $stdUmr,2));
                } else {
                        echo $zUmr;
                }
                break;

    case 'gatKarywanAFD':
        if($param['tipe']=='perpt')
        {
            $subbagian=substr($param['kodeorg'],0,6);
            $str="select karyawanid,namakaryawan,nik,subbagian from ".$dbname.".datakaryawan where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN') and statuskaryawan != 'Keluar'  and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")
                and tipekaryawan in('2','3','4','6')  order by namakaryawan";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				$optKary .= "<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
			}
			echo $optKary;
        }
        else if($param['tipe']=='afdeling')
        {
            $subbagian=substr($param['kodeorg'],0,6);
            $str="select karyawanid,namakaryawan,nik,subbagian from ".$dbname.".datakaryawan where subbagian='".$subbagian."' and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")
                and tipekaryawan in('2','3','4','6')  order by namakaryawan";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				$optKary .= "<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
			}
			echo $optKary;	
        }else if($param['tipe']=='mandor'){
			$nikmandor = $param['nikmandor'];
			
			$tab = "<fieldset>
				<legend style='font-weight:bold'>".$_SESSION['lang']['form'].' '.$_SESSION['lang']['absensi']."</legend>
				<table class=sortable cellspacing=1 cellpadding=3 border=0>
					<thead>
					<tr class=rowheader>
						<td align=center>".$_SESSION['lang']['nourut']."</td>
						<td align=center>".$_SESSION['lang']['nik']."</td>
						<td align=center>".$_SESSION['lang']['absensi']."</td>
						<td align=center>".$_SESSION['lang']['jhk']."</td>
						<td align=center>".$_SESSION['lang']['umrhari']."</td>
						<td align=center>".$_SESSION['lang']['insentif']."</td>
						<td align=center>".$_SESSION['lang']['action']."</td>
					</tr>
					</thead>
					<tbody>";
			$arrKarDetail = array();
			$str = "select t1.nourut, t2.nik, t1.absensi, t1.jhk, t1.umr, t1.insentif, t2.namakaryawan, t2.subbagian, t2.karyawanid from ".$dbname.".kebun_kehadiran t1 
			left join ".$dbname.".datakaryawan t2 on t1.nik = t2.karyawanid
			where t1.notransaksi='".$param['notransaksi']."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$arrKarDetail[$bar['nik']]['nik'] = $bar['nik'];
				$arrKarDetail[$bar['nik']]['karyawanid'] = $bar['karyawanid'];
				$arrKarDetail[$bar['nik']]['status'] = "0";
				$arrKarDetail[$bar['nik']]['namakaryawan'] = $bar['namakaryawan'];
				$arrKarDetail[$bar['nik']]['subbagian'] = $bar['subbagian'];
				$arrKarDetail[$bar['nik']]['absensi'] = $bar['absensi'];
				$arrKarDetail[$bar['nik']]['jhk'] = $bar['jhk'];
				$arrKarDetail[$bar['nik']]['umr'] = $bar['umr'];
				$arrKarDetail[$bar['nik']]['insentif'] = $bar['insentif'];
			}
			
			$str = "select t1.karyawanid,t2.namakaryawan,t2.nik,t2.subbagian from ".$dbname.".kebun_5mandor t1
			left join ".$dbname.".datakaryawan t2 on t1.karyawanid = t2.karyawanid 
			where t1.mandorid='".$nikmandor."' order by t1.nourut";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				if($arrKarDetail[$bar['nik']]['nik'] == $bar['nik']){
					$arrKarDetail[$bar['nik']]['status'] = "1";
				}else{
					$arrKarDetail[$bar['nik']]['nik'] = $bar['nik'];
					$arrKarDetail[$bar['nik']]['karyawanid'] = $bar['karyawanid'];
					$arrKarDetail[$bar['nik']]['status'] = "1";
					$arrKarDetail[$bar['nik']]['namakaryawan'] = $bar['namakaryawan'];
					$arrKarDetail[$bar['nik']]['subbagian'] = $bar['subbagian'];
					$arrKarDetail[$bar['nik']]['absensi'] = 'H';
					$arrKarDetail[$bar['nik']]['jhk'] = 0;
					$arrKarDetail[$bar['nik']]['umr'] = 0;
					$arrKarDetail[$bar['nik']]['insentif'] = 0;
				}
			}
			function kar_detail($a,$b) {
				return $b['status'] - $a['status'];
			}
			usort($arrKarDetail, 'kar_detail');
			$nourut = 0;
			$nourutSave = 0;
			foreach($arrKarDetail as $key=>$val){
				$nourut++;
				if($val['status'] == 0){
					$tab .= "<tr class=rowcontent>
						<td style='text-align:right'>".$nourut."</td>
						<td>".$val['namakaryawan']." - ".$val['nik']." (".$val['subbagian'].")</td>
						<td>".($val['absensi'] == "H" ? "Hadir" : "")."</td>
						<td style='text-align:right'>".($val['jhk'] == 0 ? 0 : number_format($val['jhk'],2))."</td>
						<td style='text-align:right'>".($val['umr'] == 0 ? 0 : number_format($val['umr'],2))."</td>
						<td style='text-align:right'>".($val['insentif'] == 0 ? 0 : number_format($val['insentif'],2))."</td>
						<td style='text-align:center'></td>
					</tr>";
				}else{
					$nourutSave++;
					$tab .= "<tr id=row_".$nourutSave." class=rowcontent>
						<td style='text-align:right'>".$nourut."</td>
						<td>".$val['namakaryawan']." - ".$val['nik']." (".$val['subbagian'].")</td>
						<td>
							<select id=absensi_".$nourut.">
								<option value='H'>Hadir</option>
							</select>
						</td>
						<td style='text-align:right'><input style=width:65px type=text id=kehadiranhk_".$nourut." class=myinputtextnumber onkeyup=\"getTotalKehadiran('".$nourut."','".$val['karyawanid']."');\" onKeyPress=\"return angka_doang(event);\" value='".($val['jhk'] == 0 ? 0 : number_format($val['jhk'],2))."' onclick=\"opendisabled('".$nourut."')\" /></td>
						<td style='text-align:right'><input style=width:75px type=text id=kehadiranumr_".$nourut." class=myinputtextnumber onKeyPress=\"return angka_doang(event);\" value='".($val['umr'] == 0 ? 0 : number_format($val['umr'],2))."' onclick=\"opendisabled('".$nourut."')\" /></td>
						<td style='text-align:right'><input style=width:75px type=text id=kehadiranpremi_".$nourut." class=myinputtextnumber onKeyPress=\"return angka_doang(event);\" value='".($val['insentif'] == 0 ? 0 : number_format($val['insentif'],2))."' onclick=\"opendisabled('".$nourut."')\" /></td>
						<td style='text-align:center'>
							<input type=hidden id=kehadirannik_".$nourut." value='".$val['karyawanid']."'>
							<button class=mybutton id=simpankehadiran_".$nourut." onclick=simpankehadiran('".$nourut."','".$val['karyawanid']."')>".$_SESSION['lang']['save']."</button>
						</td>
					</tr>";
				}
			}
			$tab .= "<tr>
				<td colspan=7 style='text-align:center'><button class=mybutton onclick=simpankehadiranAll('".$nourutSave."')>".$_SESSION['lang']['saveall']."</button></td>
			</tr>";
			$tab .= "</tbody>
				</table>
			</fieldset>";
			
			echo $tab;
		} else {    
            $subbagian=substr($param['kodeorg'],0,4);
            $str="select karyawanid,namakaryawan,nik,subbagian from ".$dbname.".datakaryawan where lokasitugas='".$subbagian."' and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")
                and tipekaryawan in('2','3','4','6') order by namakaryawan";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				$optKary .= "<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." (".$bar->subbagian.")</option>";
			}
			echo $optKary;	
        }   
			
       break;  

        case 'getDivisi':
                $blokStatus = $_SESSION['tmp']['actStat'];
                if($blokStatus=='bibit'){
           $whereOrg = " tipe='BIBITAN' and length(kodeorganisasi)>6 and left(kodeorganisasi,4)='".$param['chKdOrg']."'";
                }
        else{    
                        $whereOrg = " kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$param['chKdOrg']."' and luasareaproduktif>0)
                                          and tipe='BLOK' and left(kodeorganisasi,4)='".$param['chKdOrg']."'";

        }

                $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$whereOrg." and induk like '".$param['divisi']."%' order by namaorganisasi";

                echo"<select style=width:195px id='kodeorg' onchange='changeOrg()'>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            echo"<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
        }
                echo"</select>";
                break;

        case 'updateGudang':
        $blokStatus = $_SESSION['tmp']['actStat'];
        // if($blokStatus=='bibit'){
        //     $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".substr($param['material'],0,5)."%' AND tipe like '%GUDANG%'";
        //     // $kdGudang = substr($param['material'],0,5);
        // }
        // else{
        //     $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".substr($param['material'],0,4)."2".substr($param['material'],5,1)."%' AND tipe like '%GUDANG%'";
        //     // $kdGudang = substr($param['material'],0,1);

        // }
            $str="select kodegudang from ".$dbname.".kebun_5gudangtransaksi where afdeling='".substr($param['material'],0,6)."'";
            // echo"<select id='kodegudang' onchange='changeGudang()'>";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            echo $bar['kodegudang'];
        // echo"</select>";
                break; 

        case 'savePrestasi':
                if($param['jumlahhk'] < $param['totalAbsHk']){
                        echo number_format($param['totalAbsHk'],2);
                        exit('Warning : Jumlah HK Prestasi Harus lebih besar atau sama dengan HK Kehadiran = '.$param['totalAbsHk']);
                }else{
                        $sUpd = "update ".$dbname.".kebun_prestasi set hasilkerja = '".$param['hasilkerja']."', jumlahhk = '".$param['jumlahhk']."' where notransaksi = '".$param['notransaksi']."'";
                        $owlPDO->exec($sUpd);
                        echo number_format($param['jumlahhk'],2);
                }
        break;
		
	case 'simpankehadiran':
		$kehadiranhk = $param['kehadiranhk'];
		$kehadiranumr = $param['kehadiranumr'];
		$kehadiranpremi = $param['kehadiranpremi'];
		$nik = $param['nik'];
		$notransaksi = $param['notransaksi'];
		
		if(($kehadiranhk == 0 || $kehadiranhk == "") && ($kehadiranumr > 0)){
			exit("warning : Jumlah HK harus lebih besar dari 0");
		}
		
		// Cek Upah harus ada jika HK lebih dari 0
		if($kehadiranhk > 0 and ($kehadiranpremi==0 || $kehadiranpremi == "")) {
			exit("Warning: Untuk pekerjaan dengan HK, maka upah tidak boleh 0");
		}
		
		//Get Jumlah HK Prestasi
		$str = "select jumlahhk from ".$dbname.".kebun_prestasi where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$totakHkPres = $bar['jumlahhk'];
		
		//Get Jumlah HK Absensi
		$str = "select sum(jhk) as jumlahhk from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$totakHkAbs = $bar['jumlahhk'];
		
		//
		$str = "select jhk from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."' and nik = '".$nik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$nowJlhHk = (isset($bar['jhk']) ? $bar['jhk'] : 0);
		
		// exit("error :__".$totakHkPres."___".$totakHkAbs."___".$kehadiranhk);
		if((($totakHkAbs-$nowJlhHk)+$kehadiranhk) > $totakHkPres){
			exit("warning : Nilai Prestasi harus lebih besar dari nilai Absensi");
		}
		
		// exit("error : ".$str);
		if($nowJlhHk > 0){
			if(($kehadiranhk == 0 || $kehadiranhk == "") && ($kehadiranpremi == 0 || $kehadiranpremi == "")){
				$str = "delete from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."' and nik = '".$nik."'";				
			}else{
				$str = "update ".$dbname.".kebun_kehadiran set jhk = '".$kehadiranhk."', umr = '".$kehadiranumr."', insentif = '".$kehadiranpremi."' where notransaksi = '".$notransaksi."' and nik = '".$nik."'";
			}
		}else{
			if(($kehadiranhk == 0 || $kehadiranhk == "") && ($kehadiranpremi == 0 || $kehadiranpremi == "")){
				$str = "delete from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."' and nik = '".$nik."'";
			}else{
				$str = "select max(nourut) as nourut from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$noUrut = $bar['nourut']+1;
				
				$str = "insert into ".$dbname.".kebun_kehadiran (notransaksi,nourut,nik,absensi,jhk,umr,insentif) values ('".$notransaksi."','".$noUrut."','".$nik."','H','".$kehadiranhk."','".$kehadiranumr."','".$kehadiranpremi."')";
			}
		}
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo $e->getMessage();
			die();
		}
	break;

	case 'updateUMR2':
		$firstKary = $param['nik'];
		$jhk = $param['jhk'];
		$tanggal = $param['tanggal'];
		
		//Get Jumlah HK Prestasi
		$str = "select jumlahhk from ".$dbname.".kebun_prestasi where notransaksi = '".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$totakHkPres = $bar['jumlahhk'];
		
		//Get Jumlah HK Absensi
		$str = "select sum(jhk) as jumlahhk from ".$dbname.".kebun_kehadiran where notransaksi = '".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$totakHkAbs = $bar['jumlahhk'];
		
		//
		$str = "select jhk from ".$dbname.".kebun_kehadiran where notransaksi = '".$param['notransaksi']."' and nik = '".$firstKary."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if((isset($bar['jhk']) ? $bar['jhk'] : 0) > 0){
			
		}else{
			if($totakHkPres < ($totakHkAbs+($jhk=='' ? 0 : $jhk))){
				exit("warning : Nilai Prestasi harus lebih besar dari nilai Absensi");
			}
		}
		
		// Ambil Gaji Pokok
		$qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai',
			"karyawanid=".$firstKary." and tahun=".$param['tahun']." and idkomponen in (1,31)");
		$Umr = fetchData($qUMR);
		
		// Standard UMR
		$stdUmr = $Umr[0]['nilai']/25;
		
		// Upah yang didapat
		@$zUmr=$jhk*$Umr[0]['nilai']/25;
		
		// Cek UMR di Panen
		$qPanen = selectQuery($dbname,'kebun_prestasi_vw','sum(upahkerja) as upah',
							  "karyawanid = '".$firstKary."' and tanggal = '".tanggalsystem($param['tanggal'])."'");
		$resPanen = fetchData($qPanen);
		$upahPanen = $resPanen[0]['upah'];
		
		// Sisa Upah setelah panen
		$sisaUpah = $stdUmr - $upahPanen;
		
		// Jika UMR
		if($zUmr > $sisaUpah) {
			exit("Warning: Karyawan tersebut sudah bekerja di panen.\n".
				"Sisa HK yang dapat digunakan adalah ".number_format($sisaUpah / $stdUmr,2));
		} else {
			echo $zUmr;
		}
		break;
		
	case 'simpankehadiranAll':
		$kehadiranhk = checkPostGet('kehadiranhk','');
		$kehadiranumr = checkPostGet('kehadiranumr','');
		$kehadiranpremi = checkPostGet('kehadiranpremi','');
		$kehadirannik = checkPostGet('kehadirannik','');
		$nik = checkPostGet('kehadirannik','');
		$notransaksi = checkPostGet('notransaksi','');
		
		if(($kehadiranhk == 0 || $kehadiranhk == "") && ($kehadiranumr > 0)){
			exit("warning : Jumlah HK harus lebih besar dari 0");
		}
		
		//Get Jumlah HK Prestasi
		$str = "select jumlahhk from ".$dbname.".kebun_prestasi where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$totakHkPres = $bar['jumlahhk'];
		
		//Get Jumlah HK Absensi
		$str = "select sum(jhk) as jumlahhk from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$totakHkAbs = $bar['jumlahhk'];
		
		//
		$str = "select jhk from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."' and nik = '".$nik."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$nowJlhHk = (isset($bar['jhk']) ? $bar['jhk'] : 0);
		
		// exit("error :__".$totakHkPres."___".$totakHkAbs."___".$kehadiranhk);
		if((($totakHkAbs-$nowJlhHk)+$kehadiranhk) > $totakHkPres){
			exit("warning : Nilai Prestasi harus lebih besar dari nilai Absensi");
		}
		
		// exit("error : ".$str);
		if($nowJlhHk > 0){
			if(($kehadiranhk == 0 || $kehadiranhk == "") && ($kehadiranpremi == 0 || $kehadiranpremi == "")){
				$str = "delete from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."' and nik = '".$nik."'";				
			}else{
				$str = "update ".$dbname.".kebun_kehadiran set jhk = '".$kehadiranhk."', umr = '".$kehadiranumr."', insentif = '".$kehadiranpremi."' where notransaksi = '".$notransaksi."' and nik = '".$nik."'";
			}
		}else{
			if(($kehadiranhk == 0 || $kehadiranhk == "") && ($kehadiranpremi == 0 || $kehadiranpremi == "")){
				$str = "delete from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."' and nik = '".$nik."'";
			}else{
				$str = "select max(nourut) as nourut from ".$dbname.".kebun_kehadiran where notransaksi = '".$notransaksi."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$noUrut = $bar['nourut']+1;
				
				$str = "insert into ".$dbname.".kebun_kehadiran (notransaksi,nourut,nik,absensi,jhk,umr,insentif) values ('".$notransaksi."','".$noUrut."','".$nik."','H','".$kehadiranhk."','".$kehadiranumr."','".$kehadiranpremi."')";
			}
		}
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo $e->getMessage();
			die();
		}
		break;

    default:
		break;
}
?>