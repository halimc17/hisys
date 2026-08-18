<?php
ini_set('display_errors',0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
include_once('lib/HtmlExcel.php');

$proses=checkPostGet('proses','');
$per=checkPostGet('per','');
$unit=checkPostGet('unit','');
$pt=checkPostGet('pt','');
$karyawanid=checkPostGet('karyawanid','');
$tpKary=checkPostGet('tpKary','');
$tipe=checkPostGet('tipe','');
$pphx21=checkPostGet('pph','');

$tipeprint= checkPostGet('tipeprint','');

$regional=  makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional');
$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$ev=checkPostGet('ev','');
$rNmTipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

$nminduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");


switch($proses){
  
  
  
    case 'preview':
		$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$nminduk[$unit]."'";
		$res=fetchdata($str);
		$namaorg=$res[0]['namaorganisasi'];

	#= komponen yang tidak termasuk di slip gaji
	$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='KOMGJEXSLP'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch(); 
	$exslip=array();
	$arrx=explode(',', $bar['nilai']);
	for ($i=0; $i < count($arrx); $i++) { 
	  $exslip[$arrx[$i]]=$arrx[$i];
	}
	$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$gjthnlu=$bar['nilai'];

	$whcatukdbag=$dtTipecatu ='';
	if($unit=='')
	{
	  //exit('Warning : Unit harus dipilih !');
	}

	if($per!=''){
		if($pt!=''){
			$where="a.periodegaji='".$per."' and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and induk='".$pt."')";
		}else{
			$where="a.periodegaji='".$per."' and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4)";
		}
		if($unit!=''){
			$where="a.periodegaji='".$per."' and a.kodeorg='".$unit."'";
		}
		
		$wherelalu="a.periodegaji='".periodelalu($per)."' ";
	}else{
		exit('Warning : Periode gaji harus dipilih !');
	}

	$dtTipe=$dtTupecatu="";

	if($tpKary!=''){
		if($tpKary=='4'){
			$dtTipe=" and b.sistemgaji='Harian' and b.tipekaryawan='".$tpKary."' ";
			$wherelalu='1=1';
		}else{
			$dtTipe=" and b.sistemgaji='Bulanan' and b.tipekaryawan='".$tpKary."' and a.idkomponen not in (".$gjthnlu.")";
			$dtTipe2=" and b.sistemgaji='Bulanan' and b.tipekaryawan='".$tpKary."' and a.idkomponen in (".$gjthnlu.")";
		}
	}else{
		exit('Warning : Tipe karyawan harus dipilih !');
	}

	$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji 
		where kodeorg='".$unit."' and periode='".$per."' and jenisgaji='H'";
	$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
	$qTgl->setFetchMode(PDO::FETCH_ASSOC);
	$rTgl=$qTgl->fetch();
	//$test = dates_inbetween($rTgl['tanggalmulai'], $rTgl['tanggalsampai']);



	$nmbank= makeOption($dbname, 'datakaryawan', 'karyawanid,namabank');
	$norek= makeOption($dbname, 'datakaryawan', 'karyawanid,norekeningbank');
	
	$wh1="";
	if($pt!=''){
		$wh1.="and kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and induk='".$pt."')";
	}else{
		$wh1.="and kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4)";
	}
	if($unit!=''){
		$wh1.=" and kodeorganisasi='".$unit."'";
	}
	
	$sOrg="select namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$wh1."";
	$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
    $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        $rOrg=$qOrg->fetch();

        //periode gaji
        $bln=explode('-',$per);
        $idBln=intval(@$bln[1]);  
        $dakarbulanan=0;
		
		$wh1="";
		if($pt!=''){
			$wh1.="and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and induk='".$pt."')";
		}else{
			$wh1.="and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4)";
		}
		if($unit!=''){
			$wh1.=" and lokasitugas='".$unit."'";
		}
        $str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B'  ".$wh1." and periodegaji='".$per."' "; 
        $res = fetchdata($str);
        if(count($res)>0){ 
			$dakarbulanan=1;
        }
        //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        if($dakarbulanan==0){
            $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,b.kodeorganisasi,b.lokasitugas,b.noktp,
            b.tanggalkeluar,b.namabank,f.namagolongan,c.namajabatan,d.nama,e.plus from 
                   ".$dbname.".sdm_gaji a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
                   left join ".$dbname.".sdm_ho_component e on a.idkomponen=e.id 
                   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
                   left join ".$dbname.".sdm_5golongan f on f.kodegolongan=b.kodegolongan 
                   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode where ".$where." ".$dtTipe."  and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)";
        }else{
            $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,b.kodeorganisasi,b.lokasitugas,
            b.noktp,b.tanggalkeluar,b.namabank,f.namagolongan,c.namajabatan,d.nama,e.plus from 
                   ".$dbname.".sdm_gaji a  left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid 
                   left join ".$dbname.".sdm_ho_component e on a.idkomponen=e.id 
                   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
                   left join ".$dbname.".sdm_5golongan f on f.kodegolongan=b.kodegolongan 
                   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode where ".$where." ".$dtTipe."  and approval_status='8' and version_type='B' and b.periodegaji='".$per."' and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)";
        }
        $qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
		$qSlip->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=owlBaris($qSlip);
		$karyawanidxxx='';
		if($rCek>0){
			while($rSlip=$qSlip->fetch()){
				if($rSlip['karyawanid']!=''){
				  if($karyawanidxxx==''){
					$karyawanidxxx="'".$rSlip['karyawanid']."'";
				  }else{
					$karyawanidxxx.=",'".$rSlip['karyawanid']."'";
				  }
				  $arrKary[$rSlip['karyawanid']]=$rSlip['karyawanid'];
				  $arrTipekary[$rSlip['karyawanid']]=$rSlip['tipekaryawan'];
				  $arrStatPjk[$rSlip['karyawanid']]=$rSlip['statuspajak'];
				  $arrRek[$rSlip['karyawanid']]=$rSlip['norekeningbank'];
				  $arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
				  $arrTglMsk[$rSlip['karyawanid']]=$rSlip['tanggalmasuk'];
				  $arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
				  $arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
				  $arrBag[$rSlip['karyawanid']]=$rSlip['bagian'];
				  $arrJbtn[$rSlip['karyawanid']]=$rSlip['namajabatan'];
				  $arrDept[$rSlip['karyawanid']]=$rSlip['nama'];
				  $arrnamabank[$rSlip['karyawanid']]=$rSlip['namabank'];
				  $arrkodeorg[$rSlip['karyawanid']]=$rSlip['kodeorganisasi'];
				  $arrlokasitugas[$rSlip['karyawanid']]=$rSlip['lokasitugas'];
				  $arrnoktp[$rSlip['karyawanid']]=$rSlip['noktp'];
				  $arrtanggalkeluar[$rSlip['karyawanid']]=$rSlip['tanggalkeluar'];
				  $arrgolongan[$rSlip['karyawanid']]=$rSlip['namagolongan'];
				  $arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];

				  if($rSlip['plus']=='1')
				  {
				  $arrValPlus[$rSlip['karyawanid']][$rSlip['idkomponen']]=$rSlip['jumlah'];
				  }
				  else
				  {
				  $arrValMinus[$rSlip['karyawanid']][$rSlip['idkomponen']]=$rSlip['jumlah'];
				  }

				  $arrafd[$rSlip['karyawanid']]=$rSlip['subbagian'];
				  $arrtipekar[$rSlip['karyawanid']]=$rSlip['tipekaryawan'];
				}
			}
			if($dakarbulanan==0){
				$sSliplalu="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,b.kodeorganisasi,b.lokasitugas,b.noktp,
				b.tanggalkeluar,b.namabank,f.namagolongan,c.namajabatan,d.nama,e.plus from 
				 ".$dbname.".sdm_gaji a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
			   left join ".$dbname.".sdm_ho_component e on a.idkomponen=e.id 
				 left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
			   left join ".$dbname.".sdm_5golongan f on f.kodegolongan=b.kodegolongan 
				 left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode where ".$wherelalu." ".$dtTipe2." and b.karyawanid in (".$karyawanidxxx.") ";
			}else{
				$sSliplalu="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,b.kodeorganisasi,b.lokasitugas,b.noktp,
				b.tanggalkeluar,b.namabank,f.namagolongan,c.namajabatan,d.nama,e.plus from 
				 ".$dbname.".sdm_gaji a  left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid 
			   left join ".$dbname.".sdm_ho_component e on a.idkomponen=e.id 
				 left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
			   left join ".$dbname.".sdm_5golongan f on f.kodegolongan=b.kodegolongan 
				 left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode where ".$wherelalu." ".$dtTipe2."  and approval_status='8' and version_type='B' and b.periodegaji='".$per."' and b.karyawanid in (".$karyawanidxxx.")  ";
			}
			//echo $karyawanidxxx;
			$qSliplalu=$owlPDO->query($sSliplalu) or die(print " Gagal: ".PDOException::getMessage());
			$qSliplalu->setFetchMode(PDO::FETCH_ASSOC);
			$rCeklalu=owlBaris($qSliplalu);
			//echo $sSliplalu;
			if($rCeklalu>0 and $tpKary!='4'){
			  while($rSliplalu=$qSliplalu->fetch()){
				  if($rSliplalu['karyawanid']!=''){
					$arrKary[$rSliplalu['karyawanid']]=$rSliplalu['karyawanid'];
					$arrTipekary[$rSliplalu['karyawanid']]=$rSliplalu['tipekaryawan'];
					$arrStatPjk[$rSliplalu['karyawanid']]=$rSliplalu['statuspajak'];
					$arrRek[$rSliplalu['karyawanid']]=$rSliplalu['norekeningbank'];
					$arrKomp[$rSliplalu['karyawanid']]=$rSliplalu['idkomponen'];
					$arrTglMsk[$rSliplalu['karyawanid']]=$rSliplalu['tanggalmasuk'];
					$arrNik[$rSliplalu['karyawanid']]=$rSliplalu['nik'];
					$arrNmKary[$rSliplalu['karyawanid']]=$rSliplalu['namakaryawan'];
					$arrBag[$rSliplalu['karyawanid']]=$rSliplalu['bagian'];
					$arrJbtn[$rSliplalu['karyawanid']]=$rSliplalu['namajabatan'];
					$arrDept[$rSliplalu['karyawanid']]=$rSliplalu['nama'];
				  $arrnamabank[$rSlip['karyawanid']]=$rSlip['namabank'];
				  $arrkodeorg[$rSlip['karyawanid']]=$rSlip['kodeorganisasi'];
				  $arrlokasitugas[$rSlip['karyawanid']]=$rSlip['lokasitugas'];
				  $arrnoktp[$rSlip['karyawanid']]=$rSlip['noktp'];
				  $arrtanggalkeluar[$rSlip['karyawanid']]=$rSlip['tanggalkeluar'];
				  $arrgolongan[$rSlip['karyawanid']]=$rSlip['namagolongan'];
					$arrJmlh[$rSliplalu['karyawanid'].$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];


					if($rSliplalu['plus']=='1')
					{
					$arrValPlus[$rSliplalu['karyawanid']][$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];
					}
					else
					{
					$arrValMinus[$rSliplalu['karyawanid']][$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];
					}

					$arrafd[$rSliplalu['karyawanid']]=$rSliplalu['subbagian'];
					$arrtipekar[$rSliplalu['karyawanid']]=$rSliplalu['tipekaryawan'];
				  }
				
			  }
			}


                //array data komponen penambah dan pengurang

				$arrIdKompPls = array();
				$arrIdKompMin = array();
				$arrPlusId = array();
				$arrMinusId = array();

				$wh1="";
				if($pt!=''){
					$wh1.="and b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and induk='".$pt."')";
				}else{
					$wh1.="and b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4)";
				}
				if($unit!=''){
					$wh1.=" and b.kodeorg='".$unit."'";
				}
                $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=0 and b.jumlah!=0 and b.periodegaji='".$per."' 
                   ".$wh1." and a.id not in (".$gjthnlu.")  order by a.id";
                $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
                $qKomp->setFetchMode(PDO::FETCH_ASSOC);
                while($rKomp=$qKomp->fetch())
                {
                      $arrIdKompMin[]=$rKomp['id'];
                      $arrNmKomMin[$rKomp['id']]=$rKomp['name'];

                      $arrMinusId[]=$rKomp['id'];
                      $arrMinusName[]=$rKomp['name'];
                }


                $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=0 and b.jumlah!=0 and b.periodegaji='".periodelalu($per)."' 
                   and b.karyawanid in (".$karyawanidxxx.") and a.id in (".$gjthnlu.")  order by a.id";
                // exit($sKomp);
                $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
                $qKomp->setFetchMode(PDO::FETCH_ASSOC);
                while($rKomp=$qKomp->fetch())
                {
                      $arrIdKompMin[]=$rKomp['id'];
                      $arrNmKomMin[$rKomp['id']]=$rKomp['name'];


                      $arrMinusId[]=$rKomp['id'];
                      $arrMinusName[]=$rKomp['name'];
                }


                 $arrPlusId=$arrMinusId;
                $arrPlusName=$arrMinusName;
                for($r=0;$r<count($arrMinusId);$r++)
                {
                     $arrPlusId[$r]='';
                     $arrPlusName[$r]='';
                }
                $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=1 and b.jumlah!=0 and b.periodegaji='".$per."' 
                   ".$wh1."
                   and a.id not in (".$gjthnlu.") order by a.id";

                // exit($sKomp);
                $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
                $qKomp->setFetchMode(PDO::FETCH_ASSOC);
                while($rKomp=$qKomp->fetch())
                {
                      $arrIdKompPls[]=$rKomp['id'];
                      $arrNmKomPls[$rKomp['id']]=$rKomp['name'];

                      $arrPlusId[]=$rKomp['id'];
                      $arrPlusName[]=$rKomp['name'];
                }
                //array data komponen penambah dan pengurang periode lalu
				#and a.id not in ('26','28')
                $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
                   left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
                   where a.plus=1 and b.jumlah!=0 and b.periodegaji='".periodelalu($per)."' 
                    
                   and b.karyawanid in (".$karyawanidxxx.") and a.id in (".$gjthnlu.") order by a.id";
                $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
                $qKomp->setFetchMode(PDO::FETCH_ASSOC);
                while($rKomp=$qKomp->fetch())
                {
                      $arrIdKompPls[]=$rKomp['id'];
                      $arrNmKomPls[$rKomp['id']]=$rKomp['name'];

                      $arrPlusId[]=$rKomp['id'];
                      $arrPlusName[]=$rKomp['name'];
                }

               


                
  }
  
    $bln=explode('-',$per);
        @$idBln=intval($bln[1]);  
    

                        $sPeriod="select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where jenisgaji='B' and periode='".$per."' and kodeorg='".$unit."'";
            $qPeriod=$owlPDO->query($sPeriod) or die(print " Gagal: ".PDOException::getMessage());
            $qPeriod->setFetchMode(PDO::FETCH_ASSOC);
                        $rPeriod=$qPeriod->fetch();
                        $mulai=tanggalnormal($rPeriod['tanggalmulai']);
                        $selesi=tanggalnormal($rPeriod['tanggalsampai']);

                        $stream.="
                        <table class=sortable cellspacing=1>
                        <thead>
                        <tr class=rowcontent>
                                <th align=center rowspan='2'>No.</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['nik2']."</td>
                <th align=center rowspan='2'>".$_SESSION['lang']['namakaryawan']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['kodeorg']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['lokasitugas']."</td>
                <th align=center rowspan='2'>".$_SESSION['lang']['divisi']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['regional']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['jabatan']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['kode']." ".$_SESSION['lang']['kodegolongan']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['tipekaryawan']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['statuspajak']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['noktp']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['bank']."</td>
                                <th align=center rowspan='2'>Bank Custom</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['norek']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['masuk']."</td>
                                <th align=center rowspan='2'>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['keluar']."</td>
                                
                                ";
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
            
                        // $stream.="<td align=center  colspan='".($rowabs+1)."'>".$_SESSION['lang']['hkdibayar']."</td>";
                        // $stream.="<td align=center colspan='".($rowabs2+1)."'>".$_SESSION['lang']['hktdkdibayar']."</td>";
                        $plsCol=count($arrIdKompPls);
                        $minCol=count($arrIdKompMin);
                        
                        
                        $stream.="<th align=center colspan='".($plsCol+1)."'>".$_SESSION['lang']['penambah']."</td>";
                        $stream.="<th align=center colspan='".($minCol+1)."'>".$_SESSION['lang']['pengurang']."</td>";
                        $stream.="<th align=center rowspan='2'>Total Terima</td></tr><tr>";
                        // while($rdbyr=$qhkdbyr->fetch()){
                           // $stream.="<td align=center>".$rdbyr['kodeabsen']."</td>";
                            // $dtAbsByr[]=$rdbyr['kodeabsen'];
                        // }
                        // $stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
                        // while($rdbyr=$qhkdbyr2->fetch()){
                            // $stream.="<td align=center>".$rdbyr['kodeabsen']."</td>";
                            // $dtAbsTdkByr[]=$rdbyr['kodeabsen'];
                        // }
                        //   $stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
                        foreach($arrIdKompPls as $lstKompPls)
                                {
                                    //$brsPlus++;
                                    $stream.="<th align=center>".$arrNmKomPls[$lstKompPls]."</td>";
                                    /*if($brsPlus==1)
                                    {
                                        $stream.="<td align=center>".$arrNmKomMin[37]."</td>";
                                        $stream.="<td align=center>".$arrNmKomMin[36]."</td>";
                                    }*/

                                }
                        $stream.="<th align=center >".$_SESSION['lang']['totalPendapatan']."</td>";
                                
                                //indra
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
                                         $stream.="<th align=center>".$arrNmKomMin[$lstKompMin]."</td>";
                                    //}
                                }     

                      $stream.="<th align=center >".$_SESSION['lang']['totalPotongan']."</td></tr>";
            
            
    
      
        
    $stream.="</tr>"; 
    $stream.="</thead>";

                         //prepare array data gaji karyawan,nama,jabatan,tmk dan bagian
        
        if($rCek>0)
        {
                
                    $totalx=Array();
                    foreach($arrKary as $dtKary)
                    {     
                                $no+=1;
                                setIt($jumTot[$dtKary],0);
                                $stream.="<tr class=rowcontent>
                                <td align=center>".$no."</td>
                <td align=left style='mso-number-format:\@;'>".$arrNik[$dtKary]."</td>
                <td align=left>".$arrNmKary[$dtKary]."</td>
                                <td align=left>".$nmorg[$arrkodeorg[$dtKary]]."</td>
                                <td align=left>".$nmorg[$arrlokasitugas[$dtKary]]."</td>
                                <td align=left>".$arrDept[$dtKary]."</td>
                                <td align=left>".$regional[$arrlokasitugas[$dtKary]]."</td>
                                <td align=left>".$arrJbtn[$dtKary]."</td>
                                <td align=left>".$arrgolongan[$dtKary]."</td>
                                <td align=left>".$rNmTipe[$arrTipekary[$dtKary]]."</td> 
                                <td align=left>".$arrStatPjk[$dtKary]."</td>
                                <td align=left>".$arrnoktp[$dtKary]."</td>
                                <td align=left>".$arrnamabank[$dtKary]."</td>
                                <td align=center>-</td>
                <td align=left style='mso-number-format:\@;'>".$arrRek[$dtKary]."</td>
                                <td align=center>".tanggalnormal($arrTglMsk[$dtKary])."</td>
                                <td align=center>".tanggalnormal($arrtanggalkeluar[$dtKary])."</td>";
                
                

                                $arrPlus=Array();
                                $s=0;
                                //$brsPlus2=0;
                                foreach($arrIdKompPls as $lstKompPls)
                                {
                                    if($arrJmlh[$dtKary.$lstKompPls]==''){
                                    setIt($arrJmlh[$dtKary.$lstKompPls],0);
                                    }
                                    $stream.="<td align=right>".@number_format($arrJmlh[$dtKary.$lstKompPls],2)."</td>";
                                    $totalx[$lstKompPls]+=$arrJmlh[$dtKary.$lstKompPls];
                                    if(!in_array($lstKompPls, $exslip))
                                    {
                                    $arrPlus[$s]=$arrJmlh[$dtKary.$lstKompPls]; 
                                    }
                                    $s++;
                                    //$brsPlus2++;
                                    /*if($brsPlus2==1)
                                    {
                                        setIt($arrJmlh[$dtKary.$peng1],0);
                                        setIt($arrJmlh[$dtKary.$peng2],0);
                                        $stream.="<td>-".@number_format($arrJmlh[$dtKary.$peng1],2)."</td>";
                                        $stream.="<td>-".@number_format($arrJmlh[$dtKary.$peng2],2)."</td>";
                                    }*/

                                }
                                $totDpt=array_sum($arrPlus);
                                //$totDpt=array_sum($arrPlus)-($arrJmlh[$dtKary.$peng1]+$arrJmlh[$dtKary.$peng2]);
                                $stream.="<td align=right>".@number_format($totDpt,2)."</td>";


                                $arrMin=Array();
                                $q=0;
                                foreach($arrIdKompMin as $lstKompMin)
                                {
                                    //if(($lstKompMin!=37)&&($lstKompMin!=36))
                                    //{
                                         if(!isset($arrJmlh[$dtKary.$lstKompMin])){
                                         setIt($arrJmlh[$dtKary.$lstKompMin],0);
                                         }
                                         $stream.="<td align=right>".@number_format($arrJmlh[$dtKary.$lstKompMin])."</td>";
                                       $totalx[$lstKompMin]+=$arrJmlh[$dtKary.$lstKompMin];
                                         $arrMin[$q]=$arrJmlh[$dtKary.$lstKompMin];
                                         $q++;
                                   // }
                                }
                                $gajiBersih=$totDpt-array_sum($arrMin);       

                                //$stream.="<td align=right>".@number_format(array_sum($arrPlus),2)."</td>";
                                $stream.="<td align=right>".@number_format(array_sum($arrMin),2)."</td>";
                                $stream.="<td align=right>".@number_format($gajiBersih,0)."</td></tr>"; 

                     }
                                $stream.="<tfoot><tr class=rowcontent><td colspan=17 align=center>".$_SESSION['lang']['total']."</td>";


                                $s=0;
                                $arrPlus=array();
                                foreach($arrIdKompPls as $lstKompPls)
                                {
                                  if(!isset($totalx[$lstKompPls])){
                                      setIt($totalx[$lstKompPls],0);
                                    }
                                    $stream.="<td align=right>".@number_format($totalx[$lstKompPls],2)."</td>";
                                    if(!in_array($lstKompPls, $exslip))
                                    {
                                    $arrPlus[$s]=$totalx[$lstKompPls];
                                    }
                                    $s++;
                                    //$brsPlus2++;
                                    /*if($brsPlus2==1)
                                    {
                                        setIt($arrTotal[$peng1],0);
                                        setIt($arrTotal[$peng2],0);
                                        $stream.="<td>-".@number_format($arrTotal[$peng1],2)."</td>";
                                        $stream.="<td>-".@number_format($arrTotal[$peng2],2)."</td>";
                                    }*/
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
                                  if(!isset($totalx[$lstKompMin])){
                                        setIt($totalx[$lstKompMin],0);
                                    }
                                         $stream.="<td align=right>".@number_format($totalx[$lstKompMin])."</td>";
                                         $arrMin[$q]=$totalx[$lstKompMin];
                                         $q++;
                                   // }
                                }
                                $gajiBersih=$totDpt-array_sum($arrMin);       

                                //$stream.="<td align=right>".@number_format(array_sum($arrPlus),2)."</td>";
                                $stream.="<td align=right>".@number_format(array_sum($arrMin),2)."</td>";
                                $stream.="<td align=right>".@number_format($gajiBersih,0)."</td>";  
                                $stream.="</tr></tfoot>";
                }

    if($tipeprint=='html'){
      echo $stream;     
    }else if($tipeprint=='excel'){
      $stream.="</tbody></table></div>";
      
      $nop = "Laporan_Rekap_Gaji_Perkaryawan_SalaryList.xls";
      $xls = new HtmlExcel();
      $xls->setCss($css);
      $xls->addSheet("LPF", $stream);
      $xls->headers($nop);
      echo $xls->buildFile();
    }
    
    // if($tipe=='excel'){
    //  $tglSkrg=date("Ymd");
    //  $nop_="laporan_rekap_gaji_perkaryawan";
    //  if(strlen($stream)>0)
    //  {
    //      if ($handle = opendir('tempExcel')) {
    //          while (false !== ($file = readdir($handle))) {
    //          if ($file != "." && $file != ".." && $file != "index.html") {
    //              @unlink('tempExcel/'.$file);
    //          }
    //          } 
    //          closedir($handle);
    //      }
    //      $handle=fopen("tempExcel/".$nop_.".xls",'w');
    //      if(!fwrite($handle,$stream))
    //      {
    //          echo "<script language=javascript1.2>
    //          parent.window.alert('Can't convert to excel format');
    //          </script>";
    //          exit;
    //      }
    //      else
    //      {
    //          echo "<script language=javascript1.2>
    //          window.location='tempExcel/".$nop_.".xls';
    //          </script>";
    //      }
    //      fclose($handle);
    //  }    
    // }
    
    
    break;

    case 'getunit':
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk = '".$pt."' ";
		$res=fetchdata($str);
		$optunit="";
		if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}else{	
			$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
		}
		foreach ($res as $val) {
			$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
		}
		echo $optunit;
    break;
  
  

######EXCEL 
    // case 'excel':
    //     //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name']; 
         
    //     break; 
}
?>