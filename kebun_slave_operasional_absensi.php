<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = $_GET['proses'];
$param = $_POST;
switch($proses) {
    case 'add':
                cekHK();

                # Kegiatan harus ada
                $qKeg = selectQuery($dbname,'kebun_prestasi','*',"notransaksi='".$param['notransaksi']."'");
                $resKeg = fetchData($qKeg);
                if(empty($resKeg)) {
                        echo 'Warning : Kegiatan harus diisi lebih dahulu';
                        exit;
                }

                # Search No urut
                $selQuery = selectQuery($dbname,'kebun_kehadiran','nourut',"notransaksi='".$param['notransaksi']."'");
                $nourut = fetchData($selQuery);
                $maxNoUrut = 1;
                if(!empty($nourut)) {
                        foreach($nourut as $row) {
                        $row['nourut']>=$maxNoUrut ? $maxNoUrut=$row['nourut'] : false;
                        }
                        $maxNoUrut++;
                }
        #==============periksa apakah sudah ada kehadiran pada hari yang sama
        $tanggal=substr($param['notransaksi'],0,8);
        $str="select sum(jhk) as jum from ".$dbname.".kebun_kehadiran_vw where tanggal=".$tanggal."
              and karyawanid='".$param['nik']."' group by karyawanid";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $datr=$res->fetch();
        #=        
        $str1="select * from ".$dbname.".sdm_absensidt a
                        LEFT JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
                        where a.tanggal=".$tanggal." and b.tipekaryawan = 4
            and a.karyawanid=".$param['nik'];
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);

        if(($datr['jum']+$param['jhk'])>1)
        {
            $not='';
            $str="select * from ".$dbname.".kebun_kehadiran_vw where tanggal=".$tanggal."
                  and karyawanid='".$param['nik']."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            echo "Karyawan tersebut sudah memiliki absen lebih dari satu HK :\n";
            while($bar=$res->fetch())
            {
                // $not.="\n".$bar->notransaksi;
                // $jhk.="\n".$bar->jhk;
				$no+=1;
				echo $no.". No => ".$bar->notransaksi." HK => ".$bar->jhk."\n"; 
            }
            exit("Error");
        }
        $numrows=owlBaris($res1);
        if($numrows>0)#cek dari sdm_absensi
        {
            exit("Error: Karyawan tersebut sudah memiliki absen pada daftar absen untuk hari yang sama");
        }
        else
        {#jika belum ada maka aman
	
		
            $cols = array('nourut','nik','absensi','hasilkerja','jhk','umr','insentif','notransaksi');
            $data = $param;
            $data['nourut'] = $maxNoUrut;
            unset($data['numRow']);
            $query = insertQuery($dbname,'kebun_kehadiran',$data,$cols);
			//exit("Error:$query");
            try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

            unset($data['notransaksi']);
            $res = "";
            foreach($data as $cont) {
                $res .= "##".$cont;
            }

            $result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
            echo $result;
        }
	break;
	
	
    case 'edit':
                cekHK();

                $data = $param;
                unset($data['notransaksi']);
                unset($data['nourut']);
                foreach($data as $key=>$cont) {
                        if(substr($key,0,5)=='cond_') {
                        unset($data[$key]);
                        }
                }
                $where = "notransaksi='".$param['notransaksi']."' and nourut='".$param['cond_nourut']."'";
                $query = updateQuery($dbname,'kebun_kehadiran',$data,$where);
                try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                echo json_encode($param);
                break;
    case 'delete':
                $where = "notransaksi='".$param['notransaksi']."' and nourut='".$param['nourut']."'";
                $query = "delete from `".$dbname."`.`kebun_kehadiran` where ".$where;
                try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                break;
    default:
                break;
}


function cekHK() {
        global $dbname;
        global $param;
        global $proses;
		global $owlPDO;

		$tgl=explode('/',$param['notransaksi']);
		$tgl=$tgl[0];
		$hasilkerja=$param['hasilkerja'];
		
		// Cek HK harus ada jika premi sama dengan 0
		if($param['jhk']==0 and $param['insentif']==0) {
			exit("Warning: Jumlah HK atau premi harus lebih besar dari 0");
		}
		
        // Cek Upah harus ada jika HK lebih dari 0
        if($param['jhk']>0 and $param['umr']==0) {
                exit("Warning: Untuk pekerjaan dengan HK, maka upah tidak boleh 0");
        }
		
		// Cek HK harus ada jika UMR lebih dari 0
		if($param['jhk']==0 and $param['umr']>0) {
			exit("Warning: Jumlah HK harus lebih besar dari 0");
		}
		
		// echo"<pre>";
		// print_r($param);
		// echo"</pre>";exit("Error:A");
		
		
		//cek apakah sudah ada HK di sdm_absensidt
		$str = "select nilaihk from ".$dbname.".sdm_absensidt_vw where karyawanid='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$hkabs=$bar['nilaihk'];
			
		if(($param['jhk']+$hkabs)>1){
			exit("Warning:Jumlah HK dihari ini sudah melebihi 1");
		}			
			
		
		//cek apakah terdaftar sebagai pejabat bkm, jika sudah ada HK sudah dari atas, sehingga jika ada diabsensi detail maka hk harus 0
		$jumtrans=0;
		
		
		#cek mandor
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans+=$bar['jumkar'];
			
		#cek mandor1
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor1='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans+=$bar['jumkar'];
			
		#cek kerani
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where keranimuat='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans+=$bar['jumkar'];
			
		
		#cek nikasisten
		$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikasisten='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans+=$bar['jumkar'];
			
		if($jumtrans>0 and ($param['umr']>0 || $param['hk']>0)){
			exit("Warning:Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani, hanya diperbolehkan menginput premi saja dengan umr harus di-0-kan");
		}		

		#cek vhc kegiatan
		$jlhvhc='';
		$str = "select count(*) as jlhvhc from ".$dbname.".vhc_runhk where idkaryawan='".$param['nik']."' and tanggal='".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jlhvhc+=$bar['jlhvhc'];
			
		if($jlhvhc>0){
			exit("Warning: Karyawan tersebut sudah terdaftar pada transaksi Kegiatan Traksi");
		}		
		
        // Get HK Prestasi
        $qHK = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".$param['notransaksi']."'");
        $resHK = fetchData($qHK);

        // Get HK Absensi
        $optAbs = makeOption($dbname,'kebun_kehadiran',"nourut,jhk",
                                                 "notransaksi='".$param['notransaksi']."'");
        $hkAbs = 0;
        foreach($optAbs as $val) {
                $hkAbs += $val;
        }

        if($proses=='edit') {
                $hkAbs -= $optAbs[$param['nourut']];
        }
        $hkAbs += $param['jhk'];

        if(empty($resHK)) {
                exit("Warning: Prestasi harus diisi terlebih dahulu");
        } else {
                $hkPres = $resHK[0]['jumlahhk'];
				
                if($hkAbs > $hkPres) exit("Warning: HK Absensi tidak boleh lebih besar dari HK Prestasi");
        }
		
		#cari kodekegiatan
		$str = "select kodekegiatan, hasilkerja from ".$dbname.".kebun_prestasi where notransaksi = '".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$kodekegiatan=$bar['kodekegiatan'];
			$hslkerja_pr=$bar['hasilkerja'];
		
		#cari hasilkerja di kehadiran / sudah disave
		$strorg="select * from ".$dbname.".kebun_kehadiran where notransaksi = '".$param['notransaksi']."' and nik != '".$param['nik']."'";
		$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
		$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
		while($roworg=$queorg->fetch()){
			$hslkerja_hdr+=$roworg['hasilkerja'];	
		} 
		
		$hasilkerja=number_format($hasilkerja,2);
		$hasilkerja=str_replace(",","",$hasilkerja);
		
		$hslkerja_hdr=number_format($hslkerja_hdr,2);
		$hslkerja_hdr=str_replace(",","",$hslkerja_hdr);
		
		$hslkerja_pr=number_format($hslkerja_pr,2);
		$hslkerja_pr=str_replace(",","",$hslkerja_pr);
		
		
		#cek apakah hasil kerja di prestasi vs kehadiran
		if(($hasilkerja+$hslkerja_hdr)>$hslkerja_pr){
			exit("Error: Hasil kerja melebihi jumlah Hasil kerja di Tab Prestasi :\nPrestasi => ".$hslkerja_pr." Kehadiran => ".($hasilkerja+$hslkerja_hdr)." Varian => ".($hslkerja_pr - ($hasilkerja+$hslkerja_hdr))."");
		}
		
	
		#cek di panen ada atau tidak
		$str = "select * from ".$dbname.".kebun_prestasi_vs_hk where karyawanid = '".$param['nik']."' and tanggal ='".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$hkpanen+=$bar['hkpanenperhari'];
			
		if($param['jhk']>(1-$hkpanen)){
			exit("Error: Karyawan sudah bekerja di Kegiatan Panen dan sudah mendapatkan HK sebesar ".number_format($hkpanen,2)."");
		}
		
}