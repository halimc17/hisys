<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
function dates_inbetween($date1, $date2){

    $day = 60*60*24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);

    for($x = 1; $x < $days_diff; $x++){
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }

    $dates_array[] = date('Y-m-d',$date2);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[] = date('Y-m-d',$date1);        
    }
    return $dates_array;
}

$proses = checkPostGet('proses','');
$periode = checkPostGet('periode','');
$period = checkPostGet('period','');
$perod = checkPostGet('perod','');
$idKry = checkPostGet('idKry','');
$tPkary = checkPostGet('tPkary2','');
$idAfd = checkPostGet('idAfd','');
$kdBag2 = checkPostGet('kdBag2','');
$rNmTipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$dtTipecatu='';
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$whcatukdbag='';
if($kdBag2!=''){
	$whcatukdbag=" and bagian='".$kdBag2."'";
}


#= komponen yang tidak termasuk di slip gaji
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='KOMGJEXSLP'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
$exslip=$bar['nilai'];

$arrBln=array(1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"Mei",6=>"Jun",7=>"Jul",8=>"Agu",9=>"Sep",10=>"Okt",11=>"Nov",12=>"Des");
$rNmTipe=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$lksiTgs=substr($idAfd,0,4);
 if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    if($idAfd!='')
    {
     $add="b.lokasitugas='".$idAfd."' ";
    }
    else
    {
        exit("Error: Work unit required");
    }

    if($kdBag2!='')
    {
     $add.=" and b.bagian='".$kdBag2."'";
    }
}
else
{
	if(strlen($idAfd)<6)
	{
	    // $add="b.lokasitugas='".$idAfd."' and (b.subbagian is null or b.subbagian='') ";
	    $add="b.lokasitugas='".$idAfd."' ";
	}
	else
	{
	    $add="b.subbagian='".$idAfd."'";
	}

    if($kdBag2!='')
    {
        $add.=" and b.bagian='".$kdBag2."'";
    }
}
				$dtTipe="";
                if($tPkary!='')
                {
                    $dtTipe=" and b.tipekaryawan='".$tPkary."'";
					$dtTipecatu=" and tipekaryawan='".$tPkary."'";
                }
				
#######################################################
			$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji 
                   where kodeorg='".substr($idAfd,0,4)."' and periode='".$perod."' 
                   and jenisgaji='H'";
			$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
			$qTgl->setFetchMode(PDO::FETCH_ASSOC);
            $rTgl=$qTgl->fetch();

            $test = dates_inbetween($rTgl['tanggalmulai'], $rTgl['tanggalsampai']);
            ##tambahan absen permintaan dari pak ujang#
            foreach($test as $rw){
            	$sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik,a.notransaksi from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                        where b.notransaksi like '%PNN%' and b.kodeorg like '".substr($idAfd,0,4)."%' and b.tanggal='".$rw."'";
                //exit('warning'.$sPrestasi);
	            $rPrestasi=fetchData($sPrestasi);
	            foreach ($rPrestasi as $presBrs =>$resPres){
	                if($resPres['upahkerja']>0){
	                $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
	                'absensi'=>'H');
	                }
	                else{
	                    $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
	                'absensi'=>'HK0');
	                }
	                $resData[$resPres['nik']][]=$resPres['nik'];
	            }
	            $sKehadiran="select jhk,absensi,tanggal,karyawanid,notransaksi from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal='".$rw."' and kodeorg like '".substr($idAfd,0,4)."%'";
                          //exit("Error".$sKehadiran);
                $rkehadiran=fetchData($sKehadiran);
                foreach ($rkehadiran as $khdrnBrs =>$resKhdrn){	
                        if($resKhdrn['absensi']!=''){
                                if($resKhdrn['jhk']>0)
                                {
                                    $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                                    'absensi'=>$resKhdrn['absensi']);
                                }
                                else
                                {
                                    $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                                    'absensi'=>'HK0');
                                }
                                $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                        }
                }
                // ambil pengawas                        
				$dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
				    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
				    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
				    where a.tanggal='".$rw."' and b.kodeorg like '".substr($idAfd,0,4)."%' and c.namakaryawan is not NULL
				    union select tanggal,nikmandor1,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
				    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
				    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
				    where a.tanggal='".$rw."' and b.kodeorg like '".substr($idAfd,0,4)."%' and c.namakaryawan is not NULL";
				//echo $dzstr;
				//exit("Error".$dzstr);
				$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
				$dzres->setFetchMode(PDO::FETCH_OBJ);
				while($dzbar=$dzres->fetch()){
				    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
				    'absensi'=>'H');
				    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
				}
				// ambil administrasi                       
				$dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
				    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
				    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
				    where a.tanggal='".$rw."' and b.kodeorg like '".substr($idAfd,0,4)."%' and c.namakaryawan is not NULL
				    union select tanggal,keranimuat,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
				    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
				    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
				    where a.tanggal='".$rw."' and b.kodeorg like '".substr($idAfd,0,4)."%' and c.namakaryawan is not NULL";
				$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
				$dzres->setFetchMode(PDO::FETCH_OBJ);
				while($dzbar=$dzres->fetch()){
				    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
				    'absensi'=>'H');
				    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
				}
				// ambil administrasi                       
				$dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
				    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
				    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
				    where a.tanggal='".$rw."' and b.kodeorg like '".substr($idAfd,0,4)."%' and c.namakaryawan is not NULL
				    union select tanggal,nikasisten,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
				    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
				    left join ".$dbname.".datakaryawan c on a.nikasisten=c.karyawanid
				    where a.tanggal='".$rw."' and b.kodeorg like '".substr($idAfd,0,4)."%' and c.namakaryawan is not NULL";
				$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
				$dzres->setFetchMode(PDO::FETCH_OBJ);
				while($dzbar=$dzres->fetch())
				{
				    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
				    'absensi'=>'H');
				    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
				}
				#ambil dari sdm_absensi
				$sAbsn="select absensi,tanggal,karyawanid,kodeorg,catu from ".$dbname.".sdm_absensidt 
							where tanggal='".$rw."' and kodeorg like '".substr($idAfd,0,4)."%'";
						  //exit("Error".$sAbsn);
				$rAbsn=fetchData($sAbsn);
				foreach ($rAbsn as $absnBrs =>$resAbsn){
					if(!is_null($resAbsn['absensi'])){
						$hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
							'absensi'=>$resAbsn['absensi']);						
						$resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
					}
				}
            }
            $whrdt="subbagian='".$idAfd."'";
            if(strlen($idAfd)==4){
            	$whrdt="lokasitugas='".$idAfd."' and (subbagian is null or subbagian='')";
            }
            // ambil traksi                       
			$dzstr="SELECT a.tanggal,idkaryawan, a.notransaksi FROM ".$dbname.".vhc_runhk a
			        left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
			        where a.tanggal='".$rw."' and notransaksi like '%".substr($idAfd,0,4)."%'
			        and ".$whrdt."";
			$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
			$dzres->setFetchMode(PDO::FETCH_OBJ);
			while($dzbar=$dzres->fetch()){
			    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
			    'absensi'=>'H');    
			    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
			}
            
				if(is_array($resData)){
					foreach($resData as $hslBrs => $hslAkhir){	
						if($hslAkhir[0]!=''){
							foreach($test as $barisTgl =>$isiTgl){
								setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
								setIt($brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']],0);
								$brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
							}
						}	
					}
				}
					

#######################################################

$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$gjthnlu=$bar['nilai'];

$nmbank= makeOption($dbname, 'datakaryawan', 'karyawanid,namabank');
$norek= makeOption($dbname, 'datakaryawan', 'karyawanid,norekeningbank');

switch($proses)
{
        case'preview':
		$path='images/logo.jpg';
		$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$idAfd."'";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
		$rOrg=$qOrg->fetch();
		$path='images/logo.jpg';

	
        //periode gaji
        $bln=explode('-',$perod);
        $idBln=intval($bln[1]);	

        //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama from 
               ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
               where b.sistemgaji='Harian' and a.periodegaji='".$perod."' and ".$add." ".$dtTipe." and a.idkomponen not in (".$gjthnlu.")";
		// echo $sSlip;
		// echo $add;
		$qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
		$qSlip->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=owlBaris($qSlip);
        if($rCek>0)
        {
                while($rSlip=$qSlip->fetch())
                {
                    if($rSlip['karyawanid']!='')
                    {
                    $arrKary[$rSlip['karyawanid']]=$rSlip['karyawanid'];
                    $arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
                    $arrTglMsk[$rSlip['karyawanid']]=$rSlip['tanggalmasuk'];
                    $arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
                    $arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
                    $arrBag[$rSlip['karyawanid']]=$rSlip['bagian'];
                    $arrJbtn[$rSlip['karyawanid']]=$rSlip['namajabatan'];
                    $arrDept[$rSlip['karyawanid']]=$rSlip['nama'];
                    $arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];
                    }
                }

                $sSliplalu="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama from 
               ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
               where b.sistemgaji='Harian' and a.periodegaji='".periodelalu($perod)."' and ".$add." ".$dtTipe." and a.idkomponen  in (".$gjthnlu.")";
				// echo $sSlip;
				// echo $add;
				$qSliplalu=$owlPDO->query($sSliplalu) or die(print " Gagal: ".PDOException::getMessage());
				$qSliplalu->setFetchMode(PDO::FETCH_ASSOC);
				$rCeklalu=owlBaris($qSliplalu);
		        if($rCeklalu>0)
		        {
		        	while($rSliplalu=$qSliplalu->fetch())
	                {
	                    if($rSliplalu['karyawanid']!='')
	                    {
	                    $arrKary[$rSliplalu['karyawanid']]=$rSliplalu['karyawanid'];
	                    $arrKomp[$rSliplalu['karyawanid']]=$rSliplalu['idkomponen'];
	                    $arrTglMsk[$rSliplalu['karyawanid']]=$rSliplalu['tanggalmasuk'];
	                    $arrNik[$rSliplalu['karyawanid']]=$rSliplalu['nik'];
	                    $arrNmKary[$rSliplalu['karyawanid']]=$rSliplalu['namakaryawan'];
	                    $arrBag[$rSliplalu['karyawanid']]=$rSliplalu['bagian'];
	                    $arrJbtn[$rSliplalu['karyawanid']]=$rSliplalu['namajabatan'];
	                    $arrDept[$rSliplalu['karyawanid']]=$rSliplalu['nama'];
	                    $arrJmlh[$rSliplalu['karyawanid'].$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];
	                    }
	                }

		        }
          //array data komponen penambah dan pengurang
		        
			$arrIdKompPls = array();
			$arrIdKompMin = array();
          $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=1 and b.jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and a.id not in (".$exslip.") and b.sistemgaji='Harian' order by a.id";
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
		  $n=-1;
          while($rKomp=$qKomp->fetch())
          {
			  $n++;
              $arrIdKompPls[$n]=$rKomp['id'];
              $arrNmKomPls[$rKomp['id']]=$rKomp['name'];
          }
          $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=0 and b.jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and b.sistemgaji='Harian' order by a.id";
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
              $arrIdKompMin[]=$rKomp['id'];
              $arrNmKomMin[$rKomp['id']]=$rKomp['name'];
          }


          #lalu
          //array data komponen penambah dan pengurang
          $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=1 and b.jumlah!=0 and b.periodegaji='".periodelalu($perod)."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and a.id not in (".$exslip.",".$gjthnlu.") and b.sistemgaji='Harian' order by a.id";
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
		  //$n=-1;
          while($rKomp=$qKomp->fetch())
          {
			  $n++;
              $arrIdKompPls[$n]=$rKomp['id'];
              $arrNmKomPls[$rKomp['id']]=$rKomp['name'];
          }
          $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=0 and b.jumlah!=0 and b.periodegaji='".periodelalu($perod)."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and b.sistemgaji='Harian'and a.id  in (".$gjthnlu.") order by a.id";
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
              $arrIdKompMin[]=$rKomp['id'];
              $arrNmKomMin[$rKomp['id']]=$rKomp['name'];
          }
		  
		  
		  
			$str="SELECT a.*,b.namakaryawan,b.nik,b.statuspajak,b.norekeningbank,b.tipekaryawan,c.namajabatan
			FROM ".$dbname.".sdm_catu a
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
			where periodegaji='".$perod."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar=$res->fetch()){
				$arrIdKompPls[$n+1]='60';
				$arrNmKomPls['60']='Natura';
				$arrJmlh[$bar['karyawanid'].'60']=$bar['jumlahrupiah'];
				$totalcatu[$bar['karyawanid']]=$bar['totalcatu'];
			}
		
		//absen di bayar
		$shkdbyr="select distinct kodeabsen from ".$dbname.".sdm_5absensi where kelompok=1 order by kodeabsen";
		$qhkdbyr=$owlPDO->query($shkdbyr) or die(print " Gagal: ".PDOException::getMessage());
		$qhkdbyr->setFetchMode(PDO::FETCH_ASSOC);
		while($rdbyr=$qhkdbyr->fetch()){
			$dtAbsByr[]=$rdbyr['kodeabsen'];
		}
		
		//absen tidak di bayar
		$shkdbyr2="select distinct kodeabsen from ".$dbname.".sdm_5absensi where kelompok=0 order by kodeabsen";
		$qhkdbyr2=$owlPDO->query($shkdbyr2) or die(print " Gagal: ".PDOException::getMessage());
		$qhkdbyr2->setFetchMode(PDO::FETCH_ASSOC);
		while($rdbyr=$qhkdbyr2->fetch()){
			$dtAbsTdkByr[]=$rdbyr['kodeabsen'];
		}
		  
		  
          // $jmlhKary=count($arrKary);
        //  exit("Error".$sSlip);
				foreach($arrKary as $dtKary){
					$totalHkBayar = 0 ;
					$totalHkTidakBayar = 0 ;
					
					//Total HK di Bayar
					foreach($dtAbsByr as $dtJmlhAbsDbyr){
						@$totalHkBayar += @number_format($brt[$dtKary][$dtJmlhAbsDbyr]);
					}
					
					//Total HK tidak di Bayar
					foreach($dtAbsTdkByr as $dtTidakDbyr){
						@$totalHkTidakBayar += @number_format($brt[$dtKary][$dtTidakDbyr]);
					}
					
					//<img src=".$path." width=60 height=35>&nbsp;
					echo"<table cellspacing=1 border=0 width=500>
					<tr>
						<td><h2>".$_SESSION['org']['namaorganisasi']."</h2></td>
					</tr>
                        <tr style='border-bottom:#000 solid 2px; border-top:#000 solid 2px;'>
							<td valign=top>
								<table border=0 width=110%>
									<tr>
										<td width=49% valign=top>
											<table border=0>
												<tr>
													<td><b>".$_SESSION['lang']['slipGaji']."</b></td>
													<td>:</td>
													<td>".$arrBln[$idBln]."-".$bln[0]."</td>
												</tr>
												<tr>
													<td style='vertical-align:top'>NIK / ".$_SESSION['lang']['tmk']."</td>
													<td style='vertical-align:top'>:</td>
													<td style='vertical-align:top'>".$arrNik[$dtKary]." / ".tanggalnormal($arrTglMsk[$dtKary])."</td>
												</tr>
												<tr>
													<td style='vertical-align:top'>".$_SESSION['lang']['nama']."</td>
													<td style='vertical-align:top'>:</td>
													<td style='vertical-align:top'>".$arrNmKary[$dtKary]."</td>
												</tr>
											</table>
										</td>
										<td width=51% valign=top>
											<table border=0>
												<tr>
													<td style='vertical-align:top'>".$_SESSION['lang']['tanggal']."</td>
													<td style='vertical-align:top'>:</td>
													<td style='vertical-align:top'>".substr(tanggalnormal($rTgl['tanggalmulai']),0,2)." s/d ".tanggalnormal($rTgl['tanggalsampai'])."</td>
												</tr>
												<tr>
													<td style='vertical-align:top'>".$_SESSION['lang']['unit']." / ".$_SESSION['lang']['bagian']."</td>
													<td style='vertical-align:top'>:</td>
													<td style='vertical-align:top'>".$rOrg['namaorganisasi']." / ".$arrBag[$dtKary]."</td>
												</tr>
												<tr>
													<td style='vertical-align:top'>".$_SESSION['lang']['jabatan']."</td>
													<td style='vertical-align:top'>:</td>
													<td style='vertical-align:top'>".$arrJbtn[$dtKary]."</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
                        <tr>
							<td>
								<table width=100%>
									<thead>
									<tr>
										<td align=center width=50%>".$_SESSION['lang']['penambah']."</td>
										<td align=center width=50%>".$_SESSION['lang']['pengurang']."</td>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td valign=top>
											<table width=100%>";
												// if($totalHkBayar != 0){
													echo "<tr>
															<td>Absensi dibayar</td>
															<td>: </td>
															<td>".$totalHkBayar."</td>
															<td></td>
														</tr>";
												// }
												$arrPlus=Array();
												$s=0;
												foreach($arrIdKompPls as $idKompPls){
													if(intval(@$arrJmlh[$dtKary.$idKompPls]!=0)){
														echo"<tr>
															<td>".$arrNmKomPls[$idKompPls]."</td>
															<td>: </td>
															<td>Rp.</td>
															<td align=right> ".@number_format($arrJmlh[$dtKary.$idKompPls],2)."</td>
															</tr>";
														}
														@$arrPlus[$s]=$arrJmlh[$dtKary.$idKompPls];
														$s++;
													}
											echo"</table>
										</td>
										<td valign=top>
											<table width=100%>";
												if($totalHkTidakBayar != 0){
													echo "<tr>
															<td>Absensi tdk dibayar</td>
															<td>: </td>
															<td>".$totalHkTidakBayar."</td>
															<td></td>
														</tr>";
												}
												$arrMin=Array();
												$q=0;
												if(is_array($arrIdKompMin) && count($arrIdKompMin)>0){
													foreach($arrIdKompMin as $idKompMin){
														if(intval(@$arrJmlh[$dtKary.$idKompMin]!=0)){
															echo"<tr>
																<td>".$arrNmKomMin[$idKompMin]."</td>
																<td>:</td>
																<td>Rp.</td>
																<td align=right> ".@number_format($arrJmlh[$dtKary.$idKompMin],2)."</td>
																</tr>";
														}
														@$arrMin[$q]=$arrJmlh[$dtKary.$idKompMin];
														$q++;
													}
												}
												$gajiBersih=array_sum($arrPlus)-array_sum($arrMin);
											echo"</table>
										</td>
									</tr>
									<tr>
										<td valign=top>
										<hr>
											<table width=100% border=0>
												<tr>
													<td>Total Penambahan</td>
													<td>: </td>
													<td>Rp.</td>
													<td align=right> ".@number_format(array_sum($arrPlus),2)."</td>
												</tr>
												<tr>
													<td>Gaji Bersih</td>
													<td>: </td>
													<td>Rp.</td>
													<td align=right> ".@number_format((array_sum($arrPlus)-array_sum($arrMin)),2)."</td>
												</tr>
											</table>
										</td>
										<td valign=top>
										<hr>
											<table width=100%>
												<tr>
													<td>Total Pengurangan</td>
													<td>:</td>
													<td>Rp.</td>
													<td align=right> ".@number_format(array_sum($arrMin),2)."</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td colspan=2>
											<table width=100% border=0>
												<tr>
													<td style='vertical-align:top'>Terbilang</td>
													<td style='vertical-align:top'>: </td>
													<td>".terbilang($gajiBersih,2)." rupiah</td>
												</tr>
											</table>
										</td>
									</tr>
									</tbody>
								</table>
							</td>
                        </tr>
                         <tr>
						<td>
						<table cellspacing=0>
						<tr>
						<hr>
						<td>
						Transfer Melalui :<br>".$nmbank[$dtKary]."<br>".$norek[$dtKary]."<br>".$arrNmKary[$dtKary]."</td>
						</td>
						</tr>
						</table>
					</tr>
						<tr>
							<td>&nbsp;</td>
                        </tr>
					</table>";
				}

        }
        else
        {
                echo" Not Found";
        }

        break;
        case'pdf':
        //$perod=$_GET['perod'];
        //$idAfd=$_GET['idAfd'];
        //$idKry=$_GET['idKry'];
        //$kdBag2=$_GET['kdBag2'];

        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
//create Header

class PDF extends FPDF
{
var $col=0;
var $dbname;

function SetCol($col)
        {
            //Move position to a column
            $this->col=$col;
            $x=10+$col*100;
            $this->SetLeftMargin($x);
            $this->SetX($x);
        }

function AcceptPageBreak()
        { 
                        if($this->col<1)
                    {
                        //Go to next column
                        $this->SetCol($this->col+1);
                        $this->SetY(10);
                        return false;
                    }
                    else
                    {
                        //Go back to first column and issue page break
                                $this->SetCol(0);
                        return true;
                    }
        }

        function Header()
        {    
                //$this->lMargin=5;  
        }

        function Footer()
        {
            //$this->SetY(-15);
            $this->SetFont('Arial','I',5);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }
}
        $pdf=new PDF('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','',5);
        //periode gaji
        $bln=explode('-',$perod);
        $idBln=intval($bln[1]);	

		
		## 
		$optTipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$tipeOrg = $optTipe[$_SESSION['empl']['lokasitugas']];
		
		if ($tipeOrg == 'PABRIK') {
			$bpjsOrg = 'PABRIK';
		} else {
			$bpjsOrg = 'KEBUN';
		}
		
		$str = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='" . $bpjsOrg . "' and jenisbpjs='ketanagakerjaan' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$kary = $bar['bebanperusahaan'];//masuk ke +
		$persh = $bar['bebanperusahaan']+$bar['bebankaryawan'];
	//echo $kary._.$persh;
		
		
        //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        $sSlip="select distinct a.*,b.subbagian,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama from 
               ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
               where b.sistemgaji='Harian' and a.periodegaji='".$perod."' and ".$add."  ".$dtTipe."";
		$qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
		$qSlip->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=owlBaris($qSlip);
        if($rCek>0)
        {
                while($rSlip=$qSlip->fetch())
                {
                    if($rSlip['karyawanid']!='')
                    {
						$arrKary[$rSlip['karyawanid']]=$rSlip['karyawanid'];
						$arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
						$arrTglMsk[$rSlip['karyawanid']]=$rSlip['tanggalmasuk'];
						$arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
						$arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
						$arrBag[$rSlip['karyawanid']]=$rSlip['bagian'];
						$arrJbtn[$rSlip['karyawanid']]=$rSlip['namajabatan'];
						$arrDept[$rSlip['karyawanid']]=$rSlip['nama'];
						$arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];
						
						$arrtipekar[$rSlip['karyawanid']]=$rSlip['tipekaryawan'];
						$arrafd[$rSlip['karyawanid']]=$rSlip['subbagian'];
						if($rSlip['idkomponen']==1){
							$arrhk[$rSlip['karyawanid']]=@number_format($rSlip['hk'],2);
						}
						
                    }
                }

          //array data komponen penambah dan pengurang
          $sKomp="select id,name,plus from ".$dbname.".sdm_ho_component where plus=1 ";
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
              $arrIdKompPls[]=$rKomp['id'];
              $arrNmKomPls[$rKomp['id']][1]=$rKomp['name'];
          }
          $sKomp2="select id,name,plus from ".$dbname.".sdm_ho_component where plus=0 ";
		  $qKomp2=$owlPDO->query($sKomp2) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp2->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp2=$qKomp2->fetch())
          {
              $arrIdKompPls[]=$rKomp2['id'];
              $arrNmKomPls[$rKomp2['id']][0]=$rKomp2['name'];
          }
          //komponen
            $arrMinusId=Array();
            $arrMinusName=Array();
            //$str="select id,name from ".$dbname.".sdm_ho_component where plus='0' order by id";
            $str="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where plus=0 and jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' order by id";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                array_push($arrMinusId,$bar->id);
				// if($bar->id=='3'){
				// 	array_push($arrMinusName,$bar->name." (".$persh." %)");
				// }else{
					array_push($arrMinusName,$bar->name);
				// }
            }
            //samakan
            $arrPlusId=$arrMinusId;
            $arrPlusName=$arrMinusName;
            //Kosongkan
            for($r=0;$r<count($arrMinusId);$r++)
            {
                 $arrPlusId[$r]='';
                 $arrPlusName[$r]='';
            }
            //$str="select  id,name from ".$dbname.".sdm_ho_component where plus='1'  and id not in ('26','28') order by id";
            $str="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where plus=1 and jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and id not in (".$exslip.") order by id";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
            $n=-1;
            while($bar=$res->fetch())
            {
                $n+=1;
                $arrPlusId[$n]=$bar->id;
				if($bar->id=='61'){
					$arrPlusName[$n]=$bar->name." (".$kary." %)";
				}else{
					$arrPlusName[$n]=$bar->name;
				}
            }
           $arrValPlus=Array();
           $arrValMinus=Array();
           for($x=0;$x<count($arrPlusId);$x++)
           {
                $arrValPlus[$x]=0;
                $arrValMinus[$x]=0;
           }
           $str3="select jumlah,idkomponen,a.karyawanid,c.plus from ".$dbname.".sdm_gaji_vw a 
                  left join ".$dbname.".sdm_ho_component c on a.idkomponen=c.id
                 where a.sistemgaji='Harian' and a.periodegaji='".$perod."' group by a.karyawanid,idkomponen";
           $res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
		   $res3->setFetchMode(PDO::FETCH_ASSOC);
           while($bar3=$res3->fetch())
           {
               if($bar3['plus']=='1')
               {
                    if($bar3['jumlah']!='')
                    {
                        $arrValPlus[$bar3['karyawanid']][$bar3['idkomponen']]=$bar3['jumlah'];
                    }
               }
               elseif($bar3['plus']=='0')
               {
                    if($bar3['jumlah']!='')
                    {
                        $arrValMinus[$bar3['karyawanid']][$bar3['idkomponen']]=$bar3['jumlah'];
                    }
               } 
            }

			
		$str="SELECT * FROM ".$dbname.".sdm_catu where periodegaji='".$perod."'";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){
			$arrPlusId[$n+1]='60';
			$arrPlusName[$n+1]='Natura';
			$arrValPlus[$bar['karyawanid']]['60']=$bar['jumlahrupiah'];
			$totalcatu[$bar['karyawanid']]=$bar['totalcatu'];
		}
		
		// echo"<pre>";
			// print_r($arrPlusId);
							// echo"</pre>";
			
			
		//absen di bayar
		$shkdbyr="select distinct kodeabsen from ".$dbname.".sdm_5absensi where kelompok=1 order by kodeabsen";
		$qhkdbyr=$owlPDO->query($shkdbyr) or die(print " Gagal: ".PDOException::getMessage());
		$qhkdbyr->setFetchMode(PDO::FETCH_ASSOC);
		while($rdbyr=$qhkdbyr->fetch()){
			$dtAbsByr[]=$rdbyr['kodeabsen'];
		}
		
		//absen tidak di bayar
		$shkdbyr2="select distinct kodeabsen from ".$dbname.".sdm_5absensi where kelompok=0 order by kodeabsen";
		$qhkdbyr2=$owlPDO->query($shkdbyr2) or die(print " Gagal: ".PDOException::getMessage());
		$qhkdbyr2->setFetchMode(PDO::FETCH_ASSOC);
		while($rdbyr=$qhkdbyr2->fetch()){
			$dtAbsTdkByr[]=$rdbyr['kodeabsen'];
		}

        foreach($arrKary as $dtKary)
        {
			$totalHkBayar = 0 ;
			$totalHkTidakBayar = 0 ;
			
			//Total HK di Bayar
			foreach($dtAbsByr as $dtJmlhAbsDbyr){
				@$totalHkBayar += @number_format($brt[$dtKary][$dtJmlhAbsDbyr]);
			}
			
			//Total HK tidak di Bayar
			foreach($dtAbsTdkByr as $dtTidakDbyr){
				@$totalHkTidakBayar += @number_format($brt[$dtKary][$dtTidakDbyr]);
			}
                        // $pdf->Image('images/logo.jpg',$pdf->GetX(),$pdf->GetY(),10);
                        // $pdf->SetX($pdf->getX()+10);
						
						
						@$st++;
                        $arrHead = setheadreport($_SESSION['empl']['lokasitugas']);
						$yatas=$pdf->getY();
						if($st==1){
							$xkiriatas=$pdf->getX();
							$spasi='';
						}else{
							$xkiriatas=$pdf->getX()+1;
							$spasi=' ';
						}
						if (($yatas>=100 and $pdf->getX()>48 and $pdf->col>0) or ($yatas>=100 and $pdf->getX()<48 and $pdf->col<1)){
							$pdf->setY(150);
						}

						if ($yatas>=50 and $pdf->getX()<=48 and $pdf->col>0){
							$pdf->setY($yatas+20);
						}

						//$xkiriatas=$pdf->getX();
						if (($yatas>=100 and $pdf->getX()>48 and $pdf->col>0) or ($yatas>=100 and $pdf->getX()<48 and $pdf->col<1)){
							$xkananatas=$xkiriatas+96;
							$pdf->Line($xkiriatas,150,$xkananatas,150);
						}else{
							$xkananatas=$xkiriatas+96;
							$pdf->Line($xkiriatas,$yatas,$xkananatas,$yatas);
						}
						
                        $pdf->SetFont('Arial','',8);	
                        $pdf->Cell(75,10,$spasi.''.$_SESSION['org']['namaorganisasi'],0,0,'L');
						$pdf->Ln(9);
						 $pdf->Cell(75,1,$_SESSION['empl']['kodeorganisasi'].' - '.$_SESSION['empl']['lokasitugas'],0,1,'L');
						$pdf->SetFont('Arial','B',8);	
						 $pdf->Cell(100,10,'Slip Gaji',0,1,'C');
                        $pdf->SetFont('Arial','',6);
                        $pdf->Cell(71,4,$_SESSION['lang']['slipGaji'].' : '.$arrBln[$idBln]."-".$bln[0]." ( ".$_SESSION['lang']['tanggal']." : ".substr(tanggalnormal($rTgl['tanggalmulai']),0,2)." s/d ".tanggalnormal($rTgl['tanggalsampai'])." )",'T',0,'L');
                        $pdf->SetFont('Arial','',6);
                        $pdf->Cell(25,4,'Printed on: '.date('d-m-Y: H:i:s'),"T",1,'R');
						
						
						
						
                        $pdf->SetFont('Arial','',6);		
                        $pdf->Cell(17,4,"NIK",0,0,'L');
                        $pdf->Cell(30,4,": ".$arrNik[$dtKary],0,0,'L');
                        
						if(@$nmorg[$arrafd[$dtKary]]==''){
							@$nmorg[$arrafd[$dtKary]]='Kantor';
						}
						$pdf->Cell(10,4,$_SESSION['lang']['divisi'],0,0,'L');	
                        $pdf->Cell(28,4,': '.$nmorg[$arrafd[$dtKary]],0,1,'L');		
                        
						$pdf->Cell(17,4,$_SESSION['lang']['namakaryawan'],0,0,'L');
                        $pdf->Cell(30,4,': '.$arrNmKary[$dtKary],0,0,'L');	
                        
						$pdf->Cell(10,4,$_SESSION['lang']['jabatan'],0,0,'L');
                        $pdf->Cell(28,4,': '.$arrJbtn[$dtKary],0,1,'L');	
						
						
						if($_SESSION['empl']['lokasitugas']!='SRLM'){
							$pdf->Cell(17,4,$_SESSION['lang']['tipekaryawan'],0,0,'L');
                        	$pdf->Cell(30,4,': '.$rNmTipe[$arrtipekar[$dtKary]],0,0,'L');
                        	$lebar1=10;	
                        	$lebar2=28;	
						}else{
                        	$lebar1=17;	
                        	$lebar2=30;	
						}
							
						
						$pdf->Cell($lebar1,4,$_SESSION['lang']['tmk'],0,0,'L');
                        $pdf->Cell($lebar2,4,': '.tanggalnormal($arrTglMsk[$dtKary]),0,1,'L');			
						
                        $pdf->Cell(48,4,$_SESSION['lang']['penambah'],'TB',0,'C');
                        $pdf->Cell(48,4,$_SESSION['lang']['pengurang'],'TB',1,'C');
						
						// $pdf->Cell(25,4,"Absensi dibayar",'',0,'L');
						// 	$pdf->Cell(1.5,4,": ",'',0,'L');
						// 	$pdf->Cell(21.5,4,$arrhk[$dtKary],'R',0,'L');
                        
						// if($totalHkTidakBayar != 0){
						// 	$pdf->Cell(25,4,"Absensi tdk dibayar",0,0,'L');
						// 	$pdf->Cell(1.5,4,": ",0,0,'L');
						// 	$pdf->Cell(21.5,4,$totalHkTidakBayar,0,1,'L');
						// }else{
						// 	$pdf->Cell(25,4,"",'',0,'L');
						// 	$pdf->Cell(1.5,4,"",'',0,'L');
						// 	$pdf->Cell(21.5,4,"",0,1,'L');
						// }


                     $yulang=$pdf->getY();
						for($mn=0;$mn<count($arrPlusId);$mn++)
                        {
                        		if(intval(@$arrValPlus[$dtKary][$arrPlusId[$mn]]!=0)){
                                    $pdf->Cell(25,4,$arrPlusName[$mn],0,0,'L');                                 
                                } 
                                // else if(intval(@$arrValPlus[$dtKary][$arrPlusId[$mn]]==0)&&intval(@$arrValMinus[$dtKary][$arrMinusId[$mn]]!=0)){
                                //     $pdf->Cell(25,4,'',0,0,'L');                                 
                                // }
                               //  else{
                               //     $pdf->Cell(25,4,'',0,0,'L');
                               // }
                                // if($arrPlusName[$mn]=='')
                                // {
                                //   $pdf->Cell(5,4,"",0,0,'L');
                                //   $pdf->Cell(18,4,'','R',0,'R');
                                // }
                                // else
                                // {
                                    // if($arrPlusId[$mn]=='')
                                    // {
                                    //     $pdf->Cell(5,4,"",0,0,'L');
                                    //     $pdf->Cell(18,4,'','R',0,'R');
                                    // }
                                    // else
                                    // {
                                        setIt($arrValPlus[$dtKary][$arrPlusId[$mn]],0);
                                        setIt($arrPlus[$dtKary],0);
                                        if(intval($arrValPlus[$dtKary][$arrPlusId[$mn]]!=0)){
                                            $pdf->Cell(5,4,": Rp.",0,0,'L');
                                            $pdf->Cell(18,4,@number_format($arrValPlus[$dtKary][$arrPlusId[$mn]],2,'.',','),'R',1,'R');
                                        } 
                                  //       else if(intval(@$arrValPlus[$dtKary][$arrPlusId[$mn]]==0)&&intval(@$arrValMinus[$dtKary][$arrMinusId[$mn]]!=0)){
                                  //  			$pdf->Cell(5,4,"",0,0,'L');
                                  //          	$pdf->Cell(18,4,'','R',0,'R');                                
                                		// }
           //                              else{
											// $pdf->Cell(5,4,"",0,0,'L');
           //                                 $pdf->Cell(18,4,'','R',0,'R');                                           
           //                             }
                                        @$arrPlus[$dtKary]+=$arrValPlus[$dtKary][$arrPlusId[$mn]];
                                    // }
                                // }
                      

                      			// if(intval(@$arrValMinus[$dtKary][$arrMinusId[$mn]]!=0)){
                         //            $pdf->Cell(25,4,@$arrMinusName[$mn],0,0,'L');
                         //       }
                         //       // else if(intval(@$arrValPlus[$dtKary][$arrPlusId[$mn]]!=0)&&intval(@$arrValMinus[$dtKary][$arrMinusId[$mn]]==0)){
                         //       // // else{
                         //       //     $pdf->Cell(25,4,'',0,0,'L');
                         //       //  }
                         //        if(@$arrMinusName[$mn]=='')
                         //        {
                         //          $pdf->Cell(5,4,"",0,0,'L');
                         //          $pdf->Cell(18,4,'',0,1,'R');
                         //        }
                         //        else
                         //        {
                         //            if($arrMinusId[$mn]=='')
                         //            {
                         //              $pdf->Cell(5,4,"",0,0,'L');
                         //               $pdf->Cell(18,4,'',0,1,'R');
                         //            }
                         //            else
                         //            {
                         //                setIt($arrMin[$dtKary],0);
                         //                setIt($arrValMinus[$dtKary][$arrMinusId[$mn]],0);
                         //                if(intval($arrValMinus[$dtKary][$arrMinusId[$mn]]!=0)){
                         //                    $pdf->Cell(5,4,": Rp.",0,0,'L');
                         //                    $pdf->Cell(18,4,@number_format(($arrValMinus[$dtKary][$arrMinusId[$mn]]),2,'.',','),0,1,'R');
                         //               }
                         //               // else if(intval(@$arrValPlus[$dtKary][$arrPlusId[$mn]]!=0)&&intval(@$arrValMinus[$dtKary][$arrMinusId[$mn]]==0)){
                         //               // // else{
                         //               //      $pdf->Cell(5,4,"",0,0,'L');
                         //               //     $pdf->Cell(18,4,'',0,1,'R');                                           
                         //               // }
                         //                @$arrMin[$dtKary]+=$arrValMinus[$dtKary][$arrMinusId[$mn]];
                         //            }
                         //        }
                        $yulangplus=$pdf->getY();
						$xulang1=$pdf->getX();      
						$xulang=$xulang1+48;      
                        }


                        $pdf->SetY($yulang);
                        for($mn=0;$mn<count($arrPlusId);$mn++)
                        {
                    		$pdf->SetX($xulang);
                  			if(intval(@$arrValMinus[$dtKary][$arrMinusId[$mn]]!=0)){
                                $pdf->Cell(25,4,@$arrMinusName[$mn],'L',0,'L');
                           	}
                                    setIt($arrMin[$dtKary],0);
                                    setIt($arrValMinus[$dtKary][$arrMinusId[$mn]],0);
                                    if(intval($arrValMinus[$dtKary][$arrMinusId[$mn]]!=0)){
                                        $pdf->Cell(5,4,": Rp.",0,0,'L');
                                        $pdf->Cell(18,4,@number_format(($arrValMinus[$dtKary][$arrMinusId[$mn]]),2,'.',','),0,1,'R');
                                   }
                                    @$arrMin[$dtKary]+=$arrValMinus[$dtKary][$arrMinusId[$mn]];
                                
                        }
                        $yulangminus=$pdf->getY();

                        if ($yulangplus>$yulangminus){
                        	$yulang2=$yulangplus;
                        }else{
                        	$yulang2=$yulangminus;
                        }

                        		$pdf->SetY($yulang2);
                        		$pdf->SetX($xulang-48);                                
                        		$pdf->Cell(25,4,'Total.Pendapatan','TB',0,'L');
                                $pdf->Cell(5,4,": Rp.",'TB',0,'L');
                                        $pdf->Cell(18,4,@number_format($arrPlus[$dtKary],2,'.',','),'TB',0,'R');
                                $pdf->Cell(25,4,'Total.Pengurangan','TB',0,'L');
                                $pdf->Cell(5,4,": Rp.",'TB',0,'L');
                                        $pdf->Cell(18,4,@number_format((@$arrMin[$dtKary]),2,'.',','),'TB',1,'R');

                        
						$pdf->SetFont('Arial','B',6);
                        $pdf->Cell(15,4,'Gaji.Bersih',0,0,'L');
                        $pdf->Cell(2,4,":  ",0,0,'L');
						$pdf->Cell(18,4,"Rp.  ".@number_format((@$arrPlus[$dtKary]-(@$arrMin[$dtKary])),2,'.',','),0,0,'L');
						$pdf->Cell(47,4,"",0,1,'L');
                                $terbilang=(@$arrPlus[$dtKary]-(@$arrMin[$dtKary]));
                                $blng=terbilang($terbilang,2)." rupiah";
                        $pdf->SetFont('Arial','',7);	
                        $pdf->Cell(15,4,'Terbilang ',0,0,'L');
                        $pdf->Cell(1.5,4,":",0,0,'L');                                
						$awalY=$pdf->GetY();
						$pdf->MultiCell(58,4,$blng,0,'L');

						$pdf->Cell(15,4,'',0,1,'L');
                        $pdf->Cell(15,4,'Transfer Melalui :',0,1,'L');
                        $pdf->Cell(15,4,$nmbank[$dtKary],0,1,'L');
                        $pdf->Cell(15,4,$norek[$dtKary],0,1,'L');
                        $pdf->Cell(15,4,$arrNmKary[$dtKary],0,1,'L');

						$akhirY=$pdf->GetY();
						$tinggiY=$akhirY-$awalY;
						
						if($tinggiY<=5)
						{
							$pdf->Ln();
						}
						
						$pdf->Ln(3);	
						if($totalcatu[$dtKary]!=0){
						$pdf->Cell(25,2.5,'Natura',0,0,'L');
						$pdf->Cell(10,2.5,'Jumlah',0,0,'R');
						$pdf->Cell(10,2,'Satuan',0,1,'L');
						$pdf->Cell(50,2,'________________________________',0,1,'L');
						$pdf->Cell(25,5,'Natura - '.$_SESSION['empl']['kodeorganisasi'],0,0,'L');
						$pdf->Cell(10,4,@number_format($totalcatu[$dtKary],2),0,0,'R');
						$pdf->Cell(10,4,'Kg',0,1,'L');	
						}	
						
						$pdf->Ln(10);		 
                        $pdf->Cell(50,5,' ',0,0,'C');
						$pdf->Cell(50,5,'( '.$arrNmKary[$dtKary].' )',0,1,'C');
						$xkiribawah=$pdf->getX();
						$pdf->Cell(50,5,' ',0,0,'C');
						$xkananbawah=$pdf->getX()+46;		
						$pdf->Cell(50,5,'Nama Karyawan',0,1,'C');	
						$pdf->Ln();
						$ybawaah=$pdf->GetY();
						
						//garis bawah
						$pdf->Line($xkiribawah,$ybawaah,$xkananbawah,$ybawaah);
						
						if (($yatas>=100 and $pdf->getX()>48 and $pdf->col>0) or ($yatas>=100 and $pdf->getX()<48 and $pdf->col<1)){
							//garis kiri
							$pdf->Line($xkiriatas,150,$xkiribawah,$ybawaah);
						}else {
							$pdf->Line($xkiriatas,$yatas,$xkiribawah,$ybawaah);
						}

						if (($yatas>=100 and $pdf->getX()>48 and $pdf->col>0) or ($yatas>=100 and $pdf->getX()<48 and $pdf->col<1)){
							//garis kiri
							$pdf->Line($xkananatas,150,$xkananbawah,$ybawaah);
						}else{
							//garis kanan
							$pdf->Line($xkananatas,$yatas,$xkananbawah,$ybawaah);
						}
						
                        $pdf->SetFont('Arial','',6);	
                        $pdf->Ln();	
						
                        if($pdf->GetY()>160 and $pdf->col<1)
                                $pdf->AcceptPageBreak();
                        if ($pdf->GetY()>160 and $pdf->col>0)
                           {
                                //$pdf->lewat=true;
                                // $pdf->AcceptPageBreak();
                                //$pdf->SetY(277-$pdf->GetY());
                                $r=275-$pdf->GetY();
                                $pdf->Cell(80,$r,'',0,1,'L');

                                //$pdf->ln();
                           }
                        //else   
                        //$pdf->lewat=false; 	

                        $pdf->cell(-1,3,'',0,0,'L');	
                }
}
else
{
        // $pdf->Image('images/logo.jpg',$pdf->GetX(),$pdf->GetY(),10);
        // $pdf->SetX($pdf->getX()+8);
        $pdf->SetFont('Arial','B',8);	
        $pdf->Cell(70,5,$_SESSION['org']['namaorganisasi'],0,1,'L');
        $pdf->SetFont('Arial','',5);	
        $pdf->Cell(60,3,'NOT FOUND','T',0,'L');
}
        $pdf->Output();

        break;

		
		
		
        case'excel':
        //periode gaji
        $bln=explode('-',$perod);
        $idBln=intval($bln[1]);	

          //array data komponen penambah dan pengurang
           $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=1 and b.jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and a.id not in (".$exslip.") and b.sistemgaji='Harian' order by a.id";
				  
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
			  @$nourutkomplus+=1;
              @$arrIdKompPls[]=$rKomp['id'];
              @$arrNmKomPls[$rKomp['id']]=$rKomp['name'];
          }
          $totPlus=count($arrIdKompPls);
          $brsPlus=0;
         $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=0 and b.jumlah!=0 and b.periodegaji='".$perod."' 
                   and b.kodeorg='".substr($idAfd,0,4)."' and b.sistemgaji='Harian' order by a.id";
		  $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
		  $qKomp->setFetchMode(PDO::FETCH_ASSOC);
          while($rKomp=$qKomp->fetch())
          {
				@$arrIdKompMin[]=$rKomp['id'];
				@$arrNmKomMin[$rKomp['id']]=$rKomp['name'];
          }
		  
		#where khusus catu  
		$wherecatuexcel='';
		if(strlen($idAfd)=='6'){
			$wherecatuexcel.=" and subbagian='".$idAfd."'";
		} else if(strlen($idAfd)=='4'){
			$wherecatuexcel.=" and subbagian=''";
		}
		
		$dakarbulanan=0;
        $str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$idAfd."' and periodegaji='".$perod."' "; 
        $res = fetchdata($str);
        if(count($res)>0)
        { 
        $dakarbulanan=1;
        }
        if($dakarbulanan==0){
            $str="SELECT * FROM ".$dbname.".sdm_catu where periodegaji='".$perod."' ".$dtTipecatu." and kodeorg='".substr($idAfd,0,4)."'
                    ".$wherecatuexcel." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where sistemgaji='Harian'  ".$whcatukdbag.")";
        }else{
            $str="SELECT * FROM ".$dbname.".sdm_catu where periodegaji='".$perod."' ".$dtTipecatu." and kodeorg='".substr($idAfd,0,4)."'
                    ".$wherecatuexcel." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan_hist where sistemgaji='Harian'  ".$whcatukdbag.") and approval_status='8' and version_type='B'";
        }			
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){
			//@$arrIdKompPls[$nourutkomplus+1]='60';
			@$arrNmKomPls['60']='Catu Beras';
			@$arrJmlh[$bar['karyawanid'].'60']=$bar['jumlahrupiah'];
			@$arrTotal['60']+=$bar['jumlahrupiah'];
		}				

		  

                        $sPeriod="select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where jenisgaji='H' and periode='".$perod."' and kodeorg='".substr($idAfd,0,4)."'";	
						$qPeriod=$owlPDO->query($sPeriod) or die(print " Gagal: ".PDOException::getMessage());
						$qPeriod->setFetchMode(PDO::FETCH_ASSOC);
                        $rPeriod=$qPeriod->fetch();
                        $mulai=tanggalnormal($rPeriod['tanggalmulai']);
                        $selesi=tanggalnormal($rPeriod['tanggalsampai']);

                        $stream.="
                        <table>
                        <tr><td colspan=15 align=center>List Data Gaji Harian, Unit : ".$idAfd."</td></tr>
                        <tr><td colspan=15 align=center>Periode : ".$mulai." s.d. ".$selesi."</td></tr>
                        </table>
                        <table border=1>
                        <tr>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>No.</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['namakaryawan']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['nik']."/".$_SESSION['lang']['tmk']."</td>";
                         // if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                         // {
                            $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['subbagian']."</td>";
                         // }
                         $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>No. Rekening</td>";
                         $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['tipekaryawan']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['totLembur']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['bagian']."</td>
                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['statuspajak']."</td>

                                <td bgcolor=#DEDEDE align=center rowspan='2'>".$_SESSION['lang']['jabatan']."</td>";
                                //absen di bayar
                        $shkdbyr="select distinct kodeabsen from ".$dbname.".sdm_5absensi where kelompok=1 order by kodeabsen";
						$qhkdbyr=$owlPDO->query($shkdbyr) or die(print " Gagal: ".PDOException::getMessage());
						$qhkdbyr->setFetchMode(PDO::FETCH_ASSOC);
						$rowabs=owlBaris($qhkdbyr);
                         //absen tidak di bayar
                        $shkdbyr2="select distinct kodeabsen from ".$dbname.".sdm_5absensi where kelompok=0 order by kodeabsen";
						$qhkdbyr2=$owlPDO->query($shkdbyr2) or die(print " Gagal: ".PDOException::getMessage());
						$qhkdbyr2->setFetchMode(PDO::FETCH_ASSOC);
						$rowabs2=owlBaris($qhkdbyr2);
                        
                        $stream.="<td bgcolor=#DEDEDE align=center  colspan='".($rowabs+1)."'>".$_SESSION['lang']['hkdibayar']."</td>";
                        $stream.="<td bgcolor=#DEDEDE align=center colspan='".($rowabs2+1)."'>".$_SESSION['lang']['hktdkdibayar']."</td>";
                        $plsCol=count($arrIdKompPls);
                        $minCol=count($arrIdKompMin);
                        $stream.="<td bgcolor=#DEDEDE align=center colspan='".($plsCol+1)."'>".$_SESSION['lang']['penambah']."</td>";
                        $stream.="<td bgcolor=#DEDEDE align=center colspan='".($minCol+1)."'>".$_SESSION['lang']['pengurang']."</td>";
                        $stream.="<td bgcolor=#DEDEDE align=center rowspan='2'>GAJI BERSIH</td></tr><tr>";
                        while($rdbyr=$qhkdbyr->fetch()){
                           $stream.="<td bgcolor=#DEDEDE align=center>".$rdbyr['kodeabsen']."</td>";
                            $dtAbsByr[]=$rdbyr['kodeabsen'];
                        }
                        $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."</td>";
                        while($rdbyr=$qhkdbyr2->fetch()){
                            $stream.="<td bgcolor=#DEDEDE align=center>".$rdbyr['kodeabsen']."</td>";
                            $dtAbsTdkByr[]=$rdbyr['kodeabsen'];
                        }
                           $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."</td>";
                        foreach($arrIdKompPls as $lstKompPls)
                                {
                                    $brsPlus++;
                                    $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomPls[$lstKompPls]."</td>";
                                    /*if($brsPlus==1)
                                    {
                                        $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[37]."</td>";
                                        $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[36]."</td>";
                                    }*/

                                }
                        $stream.="<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['totalPendapatan']."</td>";

                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
                                         $stream.="<td bgcolor=#DEDEDE align=center>".$arrNmKomMin[$lstKompMin]."</td>";
                                    //}
                                }			

                      $stream.="<td bgcolor=#DEDEDE align=center >".$_SESSION['lang']['totalPotongan']."</td></tr>";

					  
		
			
        //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        if($dakarbulanan==0){
            $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.subbagian,
                   b.norekeningbank from
                   ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
                   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
                   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
                   where b.sistemgaji='Harian' and a.periodegaji='".$perod."' and ".$add." ".$dtTipe." order by b.subbagian asc,b.namakaryawan asc";
        }else{
            $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.subbagian,
                   b.norekeningbank from
                   ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid 
                   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
                   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
                   where b.sistemgaji='Harian' and a.periodegaji='".$perod."' and ".$add." ".$dtTipe." and approval_status='8' and version_type='B' order by b.subbagian asc,b.namakaryawan asc";
        }
			   // exit("Error:$sSlip");
		$qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
		$qSlip->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=owlBaris($qSlip);
        if($rCek>0)
        {
                while($rSlip=$qSlip->fetch())
                {
                    if($rSlip['karyawanid']!='')
                    {
						setIt($arrTotal[$rSlip['idkomponen']],0);
						$arrKary[$rSlip['karyawanid']]=$rSlip['karyawanid'];
						$arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
						$arrTglMsk[$rSlip['karyawanid']]=$rSlip['tanggalmasuk'];
						$arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
						$arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
						$arrBag[$rSlip['karyawanid']]=$rSlip['bagian'];
						$arrJbtn[$rSlip['karyawanid']]=$rSlip['namajabatan'];
						$arrTipekary[$rSlip['karyawanid']]=$rSlip['tipekaryawan'];
						$arrStatPjk[$rSlip['karyawanid']]=$rSlip['statuspajak'];
						$arrDept[$rSlip['karyawanid']]=$rSlip['nama'];
						$arrSubbagian[$rSlip['karyawanid']]=$rSlip['subbagian'];
						$arrRek[$rSlip['karyawanid']]=$rSlip['norekeningbank'];
						$arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];
						$arrTotal[$rSlip['idkomponen']]+=$rSlip['jumlah'];
                    }
                }
                $sTot="select tipelembur,jamaktual,karyawanid from ".$dbname.".sdm_lemburdt where substr(kodeorg,1,4)='".substr($idAfd,0,4)."' and tanggal between '".$rPeriod['tanggalmulai']."' and '".$rPeriod['tanggalsampai']."'";$qTot=$owlPDO->query($sTot) or die(print " Gagal: ".PDOException::getMessage());
				$qTot->setFetchMode(PDO::FETCH_ASSOC);		
                while($rTot=$qTot->fetch())
                {
                        $sJum="select jamlembur as totalLembur from ".$dbname.".sdm_5lembur where tipelembur='".$rTot['tipelembur']."'
                        and jamaktual='".$rTot['jamaktual']."' and kodeorg='".substr($idAfd,0,4)."'";
						$qJum=$owlPDO->query($sJum) or die(print " Gagal: ".PDOException::getMessage());
						$qJum->setFetchMode(PDO::FETCH_ASSOC);
                        $rJum=$qJum->fetch();
                        @$jumTot[$rTot['karyawanid']]+=$rJum['totalLembur'];
                }
                //$peng1=37;
               // $peng2=36;
                    foreach($arrKary as $dtKary)
                    {		
                        $no+=1;
                                $stream.="<tr class=rowcontent>
                                <td>".$no."</td>
                                <td>".$arrNmKary[$dtKary]."</td>";
                                $stream.="<td>".$arrNik[$dtKary]."</td>";
                                $ocldt=9;
                                // if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                                // {
                                    $ocldt=10;
                                    $stream.="<td>".$arrSubbagian[$dtKary]."</td>";
                                // }
								setIt($jumTot[$dtKary],0);
                                $stream.="
                                <td>".$arrRek[$dtKary]."</td>
                                <td>".$rNmTipe[$arrTipekary[$dtKary]]."</td>
                                <td>".$jumTot[$dtKary]."</td>
                                <td>".$arrDept[$dtKary]."</td> 
                                <td>".$arrStatPjk[$dtKary]."</td>
                                <td>".$arrJbtn[$dtKary]."</td>";

                                foreach($dtAbsByr as $dtJmlhAbsDbyr){
									setIt($brt[$dtKary][$dtJmlhAbsDbyr],0);
									setIt($totAbsen[$dtKary],0);
									setIt($grTotDbyr[$dtJmlhAbsDbyr],0);
                                    $stream.="<td align=right>".@number_format($brt[$dtKary][$dtJmlhAbsDbyr])."</td>";
                                    $totAbsen[$dtKary]+=$brt[$dtKary][$dtJmlhAbsDbyr];
                                    $grTotDbyr[$dtJmlhAbsDbyr]+=$brt[$dtKary][$dtJmlhAbsDbyr];
                                }
                                $stream.="<td align=right>".@number_format($totAbsen[$dtKary])."</td>";
                                foreach($dtAbsTdkByr as $dtTidakDbyr){
									setIt($brt[$dtKary][$dtTidakDbyr],0);
									setIt($totAbsenTdkDbyr[$dtKary],0);
									setIt($grTotTdkDbyr[$dtTidakDbyr],0);
                                    $stream.="<td align=right>".@number_format($brt[$dtKary][$dtTidakDbyr])."</td>";
                                    $totAbsenTdkDbyr[$dtKary]+=$brt[$dtKary][$dtTidakDbyr];
                                     $grTotTdkDbyr[$dtTidakDbyr]+=$brt[$dtKary][$dtTidakDbyr];
                                }
                                $stream.="<td align=right>".@number_format($totAbsenTdkDbyr[$dtKary])."</td>";

                                $arrPlus=Array();
                                $s=0;
                                $brsPlus2=0;
                                foreach($arrIdKompPls as $lstKompPls)
                                {
									if($arrJmlh[$dtKary.$lstKompPls]==''){
                                    setIt($arrJmlh[$dtKary.$lstKompPls],0);
                                	}
                                    $stream.="<td align=right>".@number_format($arrJmlh[$dtKary.$lstKompPls],2)."</td>";
                                    $arrPlus[$s]=$arrJmlh[$dtKary.$lstKompPls];
                                    $s++;
                                    $brsPlus2++;
                                }
                                $totDpt=array_sum($arrPlus);
                                //$totDpt=array_sum($arrPlus)-($arrJmlh[$dtKary.$peng1]+$arrJmlh[$dtKary.$peng2]);
                                $stream.="<td align=right>".@number_format($totDpt,2)."</td>";
                                $arrMin=Array();
                                $q=0;
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                   // {
										 if($arrJmlh[$dtKary.$lstKompPls]==''){
	                                     setIt($arrJmlh[$dtKary.$lstKompPls],0);
	                                	 }
                                         $stream.="<td align=right>".@number_format($arrJmlh[$dtKary.$lstKompMin])."</td>";
                                         $arrMin[$q]=$arrJmlh[$dtKary.$lstKompMin];
                                         $q++;
                                    //}
                                }
                                $gajiBersih=$totDpt-array_sum($arrMin);				

                                //$stream.="<td align=right>".@number_format(array_sum($arrPlus),2)."</td>";
                                $stream.="<td align=right>".@number_format(array_sum($arrMin),2)."</td>";
                                $stream.="<td align=right>".@number_format($gajiBersih,0)."</td></tr>";	
                      }
                                $stream.="<tr><td colspan=".($ocldt+$rowabs+$rowabs2+2)." align=right>".$_SESSION['lang']['total']."</td>";

                                $s=0;
                                $brsPlus2=0;
                                $arrPlus=array();
                                foreach($arrIdKompPls as $lstKompPls)
                                {
									setIt($arrTotal[$lstKompPls],0);
                                    $stream.="<td align=right>".@number_format($arrTotal[$lstKompPls],2)."</td>";
                                    $arrPlus[$s]=$arrTotal[$lstKompPls];
                                    $s++;
                                    $brsPlus2++;
                                }
                                $totDpt=array_sum($arrPlus);
                                //$totDpt=array_sum($arrPlus)-($arrTotal[$peng1]+$arrTotal[$peng2]);
                                $stream.="<td align=right>".@number_format($totDpt,2)."</td>";

                                $arrMin=Array();
                                $q=0;
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
										setIt($arrTotal[$lstKompMin],0);
                                         $stream.="<td align=right>".@number_format($arrTotal[$lstKompMin])."</td>";
                                         $arrMin[$q]=$arrTotal[$lstKompMin];
                                         $q++;
                                   // }
                                }
                                $gajiBersih=$totDpt-array_sum($arrMin);				

                                //$stream.="<td align=right>".@number_format(array_sum($arrPlus),2)."</td>";
                                $stream.="<td align=right>".@number_format(array_sum($arrMin),2)."</td>";
                                $stream.="<td align=right>".@number_format($gajiBersih,0)."</td>";	
                                $stream.="</tr>";
                }
                else
                {
                    $stream.="<tr><td colspan=20>&nbsp;</td></tr>";
                }

				
                // echo $stream;
                // exit('warning');
				//echo "warning:".$strx;
                        //=================================================

               // exit("Error:$stream");
                        $stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
                        //echo $stream;exit();
                        $dte=date("YmdHms");
                        $nop_="GajiHarianAfdeling_".$_SESSION['empl']['lokasitugas'].$dte;
                         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                         gzwrite($gztralala, $stream);
                         gzclose($gztralala);
                         echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls.gz';
                            </script>";

        break;
         case'getPeriode':
            $optPeriode="<option value''>".$_SESSION['lang']['pilihdata']."</option>";
            $sPeriode="select periode from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($idAfd,1,4)."' and jenisgaji='H'";
			$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
			$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
            while($rPeriode=$qPeriode->fetch())
            {
                $optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
            }
            echo $optPeriode;
        break;
        default:
        break;
}
?>