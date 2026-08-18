<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if (isset($_POST['proses'])) {
	$proses = $_POST['proses'];
} else {
	$proses = $_GET['proses'];
}
$optNmOrg=makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi');
$optTipeOrg=makeOption($dbname, 'organisasi','kodeorganisasi,tipe');

$_POST['kdOrg']==''?$kdOrg=$_GET['kdOrg']:$kdOrg=$_POST['kdOrg'];
$_POST['thnId']==''?$thnId=$_GET['thnId']:$thnId=$_POST['thnId'];
$_POST['kdProj']==''?$kdProj=$_GET['kdProj']:$kdProj=$_POST['kdProj'];

$unitId=$_SESSION['lang']['all'];
$dktlmpk=$_SESSION['lang']['all'];
if($proses=='preview'){
    if(tanggalsystem($_POST['tanggal2'])<tanggalsystem($_POST['tanggal1'])){
        exit("error: Tolong gunakan urutan tanggal yang benar");
    }
    $tglPP=explode("-",$_POST['tanggal1']);
    $date1 = $tglPP[0];
    $month1 = $tglPP[1];
    $year1 = $tglPP[2];
    //$tgl1 = $bar->tanggal;
    $tgl2 = $_POST['tanggal2']; 
    $pecah2 = explode("-", $tgl2);
    $date2 = $pecah2[0];
    $month2 = $pecah2[1];
    $year2 =  $pecah2[2];
    $jd1 = GregorianToJD($month1, $date1, $year1);
    $jd2 = GregorianToJD($month2, $date2, $year2);
    $jmlHari=$jd2-$jd1;
    if(($_POST['tanggal1']=='')||($_POST['tanggal2']=='')){
        exit("error: ".$_SESSION['lang']['tanggal']."1 dan ".$_SESSION['lang']['tanggal']." 2 tidak boleh kosong");
    }
}

$_POST['tanggal1']==''?$tanggal1=$_GET['tanggal1']:$tanggal1=$_POST['tanggal1'];
$_POST['tanggal2']==''?$tanggal2=$_GET['tanggal2']:$tanggal2=$_POST['tanggal2'];

function putertanggal($tanggal)
{
    $qwe=explode('-',$tanggal);
    return $qwe[2].'-'.$qwe[1].'-'.$qwe[0];
} 

$tangsys1=putertanggal($tanggal1);
$tangsys2=putertanggal($tanggal2);

$wheretang=" b.tanggal like '%%' ";
if($tanggal1!=''){
    $wheretang=" b.tanggal = '".$tangsys1."' ";
    if($tanggal2!=''){
        $wheretang=" b.tanggal between '".$tangsys1."' and '".$tangsys2."' ";
    }
}
if($tanggal2!=''){
    $wheretang=" b.tanggal = '".$tangsys2."' ";
    if($tanggal1!=''){
        $wheretang=" b.tanggal between '".$tangsys1."' and '".$tangsys2."' ";
    }
}
$arr="##kdOrg##tanggal1##tanggal2";
if($proses=='preview'||$proses=='excel')
{

$arrnamalembur=array('0'=>'Hari Kerja','1'=>'Hari minggu','2'=>'Hari libur','4'=>'Hari libur di hari pendek');

$brdr=0;
$bgcoloraja='';
if($proses=='excel'){
    $brdr=1;
    $bgcoloraja='green';
}
        $sData="select * from ".$dbname.".sdm_lemburdt where left(kodeorg,4)='".$kdOrg."' and tanggal between '".$tangsys1."' and '".$tangsys2."' order by tanggal,kodeorg asc";
        $qData=fetchData($sData);
        $rowdt=count($qData);
/*         if($_SESSION['empl']['bagian']=='IT' || ($_SESSION['empl']['bagian']=='FIN' && $_SESSION['empl']['tipelokasitugas']=='KANWIL')){
            $tab.="<button class=mybutton onclick=postingDat(".$rowdt.")  id=revTmbl>Update Data</button>&nbsp;<button class=mybutton onclick=zExcel(event,'sdm_slave_3updatelembur.php','".$arr."')>Excel</button>";
        }else{
            $tab.="<button class=mybutton onclick=zExcel(event,'sdm_slave_3updatelembur.php','".$arr."')>Excel</button>";
        }
 */        
	
	
		
		$regData=  makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional');
		$tab.="<table cellspacing=1 cellpadding=5 border=".$brdr." class=sortable>
		<thead class=rowheader>
		<tr>
			<th ".$bgcoloraja." align=center rowspan=2>No.</th>
			<th ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['karyawanid']."</th>
			<th ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['nik']."</th>
			<th ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>
			<th ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>
			<th ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['jabatan']."</th>
			<th ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
			<th ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['tipelembur']."</th>
			<th ".$bgcoloraja." align=center width=60px rowspan=2>".$_SESSION['lang']['jamaktual']."</th>
			<th ".$bgcoloraja." align=center width=60px rowspan=2>Jam Lembur</th>
			<th hidden ".$bgcoloraja." align=center width=60px rowspan=2>UMR</th>
			<th ".$bgcoloraja." align=center width=60px rowspan=2>Rp Per Jam</th>
			
			<th align=center>Sebelum</th>
			<th align=center>Sesudah</th></tr>
			<tr><th align=center ".$bgcoloraja.">".$_SESSION['lang']['uangkelebihanjam']."</th>
			<th align=center ".$bgcoloraja.">".$_SESSION['lang']['uangkelebihanjam']."</th>
			</tr>";
			$tab.="</tr></thead><tbody>";
			$optTipeKar=makeOption($dbname, 'sdm_5tipekaryawan','id,tipe');
			$nor='0';
			foreach($qData as $row=>$rData){
				$whr="karyawanid='".$rData['karyawanid']."'";
				$whrlm="kodeorg='".substr($rData['kodeorg'],0,4)."' and tipelembur='".$rData['tipelembur']."' and jamaktual='".$rData['jamaktual']."'";
				$optKdJab=makeOption($dbname, 'datakaryawan','karyawanid,kodejabatan',$whr);
				$optJab=makeOption($dbname, 'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$optKdJab[$rData['karyawanid']]."'");
				
				$optNmKar=makeOption($dbname, 'datakaryawan','karyawanid,namakaryawan',$whr);
				$optNikKar=makeOption($dbname, 'datakaryawan','karyawanid,nik',$whr);
				$optTipekary=makeOption($dbname, 'datakaryawan','karyawanid,tipekaryawan',$whr);
				$optJamLembur=makeOption($dbname, 'sdm_5lembur','jamaktual,jamlembur',$whrlm);
				
				$optLokasiTugas=makeOption($dbname, 'datakaryawan','karyawanid,lokasitugas',$whr);
				
				#ambil parameter untuk upah lembur khusus danru
				$sparameter = "select nilai from ".$dbname.".setup_parameterappl  where kodeparameter='LBGPK' and kodeorg='".$kdOrg."'";
				$rparameter = fetchData($sparameter);
				$gajipokokdanru=$rparameter[0]['nilai'];
				
				# ambil karyawan (danru) dg nik yg didaftarkan di parameter
				$strDanru = "select * from ".$dbname.".setup_parameterappl where kodeparameter='LBDAN' and kodeorg='".$kdOrg."'";
				$resDanru = fetchdata($strDanru);
				$listDanru = explode(',',$resDanru[0]['nilai']);
				$chkDanru = 0;
				foreach($listDanru as $key){
					$optKarId = makeOption($dbname,'datakaryawan','nik,karyawanid',"nik='".$key."'");
					if($optKarId[$key]==$rData['karyawanid']){
						$chkDanru = 1;
					}
				}
				
				$getPT = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $kdOrg . "'");
				$kdpt = $getPT[$kdOrg];	 

		        $sPlmbr = "select komponengaji from " . $dbname . ".sdm_5komponenlemburpengali where kodeorg='" . $kdpt . "' ";
				$res=fetchdata($sPlmbr); 
				$allKomponen = [];
				foreach ($res as $rPlbr) { 
					$komponen = explode(',', $rPlbr['komponengaji']);
					$allKomponen = array_merge($allKomponen, $komponen);
				}
		 
				// Hapus duplikat array
				$allKomponen = array_unique($allKomponen);

				$whrid = '';
				if (!empty($allKomponen)) {
					$inKomponen = implode(",", array_map('intval', $allKomponen));
					$whrid = " and idkomponen in ($inKomponen) and karyawanid ='" . $rData['karyawanid'] . "' ";
					$sGt = "select sum(jumlah) as gapTun from " . $dbname . ".sdm_5gajipokok where 1=1 ".$whrid." and tahun='".substr($rData['tanggal'],0,7)."' and  kodeorg='" . $kodeOrg . "' ";
					$qGt=$owlPDO->query($sGt) or die(print " Gagal: ".PDOException::getMessage());
					$qGt->setFetchMode(PDO::FETCH_ASSOC);
					$rGt = $qGt->fetch();
					
					if($rGt['gapTun']==0){
						exit("Warning : Komponen Belum Ada Periode ".substr($rData['tanggal'],0,7)." !");
					}

				} else {
					$whrid = " and idkomponen ='87' ";
					$sGt = "select sum(jumlah) as gapTun from " . $dbname . ".sdm_5gajipokok where 1=1 ".$whrid." and tahun='".substr($rData['tanggal'],0,4)."' and  kodeorg='" . $kodeOrg . "' ";
					$qGt=$owlPDO->query($sGt) or die(print " Gagal: ".PDOException::getMessage());
					$qGt->setFetchMode(PDO::FETCH_ASSOC);
					$rGt = $qGt->fetch();
					
					if($rGt['gapTun']==0){
						exit("Warning : UMP Belum Ada Tahun ".$_POST['tahun']." !");
					}
				}

				
				$rGt=$qGt[0]['gapTun'];
				$uangLembur=0;
				
				#jam lembur
				$jamlbr='';
				$gpsebulan=$rGt;
				/* if($optTipeOrg[$optLokasiTugas[$rData['karyawanid']]]=='KEBUN'){
					if($chkDanru==1){
						$gpsebulan  = $gajipokokdanru;
					}

					if($optTipekary[$rData['karyawanid']]==4){
						$gajiperjam = ((($gpsebulan / 30) * 6 )/ 40);
						$jamlbr 	= $rData['jamaktual'];
					}else{
						$gajiperjam = ($gpsebulan / 173);
						$jamlbr 	= $rData['jamaktual'];
					}
				}else{
					if($optTipekary[$rData['karyawanid']]==4){
						$gajiperjam = ((($gpsebulan / 30) * 6 )/ 40);
						$jamlbr 	= $rData['jamaktual'];
					}else{
						$gajiperjam = ($gpsebulan / 173);
						$jamlbr 	= $optJamLembur[$rData['jamaktual']];
					}
				}
				 */
				$gajiperjam = ($gpsebulan / 173);
				$jamlbr 	= $optJamLembur[$rData['jamaktual']];
						
				/* 
				if($optTipekary[$rData['karyawanid']]==4){
					#jika PHL, pengali pakai jam aktual
					#jam lembur = Upah sehari rp. 82.000 x 6 hari kerja kemudian dibagi 40 jam
					if($chkDanru==1){
						$gpsebulan=$gajipokokdanru;
					}else{
						$gpsebulan=$rGt;
					}
					$gajiperjam = ((($gpsebulan / 30) * 6 )/ 40);
					$jamlbr = $rData['jamaktual'];
					
				}else{
					if($chkDanru==1){
						$gpsebulan=$gajipokokdanru;
					}else{
						$gpsebulan=$rGt;
					}
					
					$gajiperjam = ((($gpsebulan / 25) * 6 )/ 40);
					if($optTipeOrg[$optLokasiTugas[$rData['karyawanid']]]=='PABRIK'){
						$jamlbr = $optJamLembur[$rData['jamaktual']];
					}else{
						$jamlbr = $rData['jamaktual'];
					}
				}
				*/			
				$uangLembur = ($gajiperjam * $jamlbr);
				
				if(intval($uangLembur)==intval($rData['uangkelebihanjam'])){
				  continue;
				}

				$nor+=1;

				$tab.="<tr class=rowcontent id=rowDt_".$nor."><td align=center>".$nor."</td>";
				if($proses=='preview'){
					$tab.="<td><input type=hidden  id=karyawanid_".$nor." value='".$rData['karyawanid']."'>".$rData['karyawanid']."</td>";
					$tab.="<td>".$optNikKar[$rData['karyawanid']]."</td>";
				}else{
					$tab.="<td><input type=hidden  id=karyawanid_".$nor." value='".$rData['karyawanid']."'>'".$rData['karyawanid']."</td>";
					$tab.="<td>'".$optNikKar[$rData['karyawanid']]."</td>";
				}
				$tab.="<td>".$optNmKar[$rData['karyawanid']]."</td>";
				$tab.="<td>".$optTipeKar[$optTipekary[$rData['karyawanid']]]."</td>";
				$tab.="<td>".$optJab[$optKdJab[$rData['karyawanid']]]."</td>";
				$tab.="<td id=tanggal_".$nor.">".$rData['tanggal']."</td>";
				$tab.="<td hidden id=tipelembur_".$nor.">".$rData['tipelembur']."</td>";
				$tab.="<td>".$arrnamalembur[$rData['tipelembur']]."</td>";
				$tab.="<td align=right id=jamaktual_".$nor.">".$rData['jamaktual']."</td>";
				$tab.="<td align=right>".$jamlbr."</td>";
				$tab.="<td hidden align=right>".number_format($gpsebulan)."</td>";
				$tab.="<td align=right>".number_format($gajiperjam,2)."</td>";
				$tab.="<td align=right id=uanglembur_".$nor.">".$rData['uangkelebihanjam']."</td>";
				$tab.="<td align=right id=kelebihanjam_".$nor.">".$uangLembur."</td></tr>";  
			}
		 $tab.="</tbody></table>";
	if($nor>0){
		$tab.="<button class=mybutton onclick=postingDat(".$rowdt.")  id=revTmbl>Update Data</button>";	
	}else{
		$tab="<span style=font-size:18px>Seluruh data lembur sudah benar, tidak ada yang perlu diperbaiki.</span>";
	}

        
}     
switch($proses){ 
	case'preview':
	echo $tab;
	break;
        case'getPeriode': 
            $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $sPeriodeAkut="select distinct periode from ".$dbname.".setup_periodeakuntansi 
                         where kodeorg='".$_POST['kdOrg']."' and tutupbuku=0";
            $qPeriodeCari=mysql_query($sPeriodeAkut) or die(mysql_error());
            while($rPeriodeCari=mysql_fetch_assoc($qPeriodeCari))
            {
               $optPeriode.="<option value='".$rPeriodeCari['periode']."'>".$rPeriodeCari['periode']."</option>";
            }
            echo $optPeriode;
        break;
        case'updateData':
            $scek="select * from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdOrg."' and tanggalmulai>='".$_POST['tanggal']."' and tanggalsampai>='".$_POST['tanggal']."'";
            $rcek=  fetchData($scek);
            if($rcek[0]['sudahproses']==0){
                $supdate="update ".$dbname.".sdm_lemburdt set uangkelebihanjam='".$_POST['klbhanjam']."'"
                        . "where karyawanid='".$_POST['karyId']."' and jamaktual='".$_POST['jmaktual']."' and tanggal='".$_POST['tanggal']."'  and tipelembur='".$_POST['tplembur']."'";
                //exit("error:".$supdate);
                try {
                    $owlPDO->exec($supdate);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n".$supdate;
                    die();
                }
            }else{
                exit("error: tanggal di luar periode gaji yang masih aktif");
            }
        break;
        case'excel':
        
        $thisDate=date("YmdHms");
                   //$nop_="Laporan_Pembelian";
                   $nop_="update_lembur_".$thisDate;
                   $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                    gzwrite($gztralala, $tab);
                    gzclose($gztralala);
                    echo "<script language=javascript1.2>
                       window.location='tempExcel/".$nop_.".xls.gz';
                       </script>";
        break;
	default:
	break;
}

?>