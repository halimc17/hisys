<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');



$proses='';
$proses2='';
$per='';
$unit='';
$tipe='';
$rupiah='';
$periode='';
$karyawanid='';
$premi='';
$totCekList='';


$proses=checkPostGet('proses','');
$per=checkPostGet('per','');
$unit=checkPostGet('unit','');
$tipe=checkPostGet('tipe','');
$rupiah=checkPostGet('rupiah','');

$periode=checkPostGet('periode','');
$karyawanid=checkPostGet('karyawanid','');
$premi=checkPostGet('premi','');



$arrXV=array('0'=>'','1'=>'√');
$tahunGaji=substr($per,0,4);


$atgl="select * from ".$dbname.".sdm_5periodegaji where periode='".$per."' and kodeorg='".$unit."'";

$btgl=$owlPDO->query($atgl) or die(print " Gagal: ".PDOException::getMessage());
$btgl->setFetchMode(PDO::FETCH_ASSOC);
$ctgl=$btgl->fetch();
	$tgl1=$ctgl['tanggalmulai'];
	$tgl2=$ctgl['tanggalsampai'];

        
$golkar=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan');
$namagol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
$namatipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

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


if($_SESSION['empl']['kodeorganisasi'])
{
    $pnntdkdpt="and kodejabatan !='14' ";
}

$sGetKary="select * from ".$dbname.".datakaryawan  where  "
        . " tipekaryawan='".$tipe."'  and  lokasitugas='".$unit."' "
        . " and (tanggalkeluar>='".$tgl1."' or tanggalkeluar='0000-00-00') "
        . " and (tanggalmasuk<='".$tgl2."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)"
        . "  ".$pnntdkdpt." order by namakaryawan asc";  

$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{ 
    $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
    $nikkar[$kar['karyawanid']]=$kar['nik'];
    $nmJabatan[$kar['karyawanid']]=$kar['kodejabatan'];
    $sbgnb[$kar['karyawanid']]=$kar['subbagian'];
    $tipekaryawan[$kar['karyawanid']]=$kar['tipekaryawan'];
}  


switch($proses)
{
    case'preview':
	
        $xi="select distinct * from ".$dbname.".sdm_5periodegaji where periode='".$per."' 
              and kodeorg='".$unit."' and sudahproses='1'";
        $xu=$owlPDO->query($xi) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($xu);
        if($row>0)    
            $aktif2=false;
               else
             $aktif2=true;
          if(!$aktif2)
          {
              exit("Error:Periode gaji untuk ".$unit." sudah ditutup");
          }
  
  
 #periksa apakah sudah tutup buku

       $str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$per."' and 
             kodeorg='".$unit."' and tutupbuku=1";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($res);
        if($row>0)    
           $aktif=false;
       else
           $aktif=true;
  if(!$aktif)
  {
      exit("Error:Periode akuntansi untuk ".$unit." sudah tutup buku");
  } 
  

  
  if($per=='')
  {
	  exit("Error:Periode masih kosong");
  }
	    if($unit=='')
  {
	  exit("Error:Unit masih kosong");
  }
if($tipe=='')
  {
	  exit("Error:Tipe Karyawan masih kosong");
  }
  
  if($rupiah=='')
  {
	  exit("Error:Rupiah masih kosong");
  }
	
// Untuk PHL, tidak dapat
if($tipe==4) {$rupiah = 0;}
	$test = dates_inbetween($tgl1, $tgl2);
	if(($tgl2=="")&&($tgl1==""))
	{
		echo"warning: Periode Penggajian Belum Terinput";
		exit();
	}

	$jmlHari=count($test);
	//cek max hari inputan
	if($jmlHari>40)
	{
		echo"warning:Range tanggal tidak valid";
		exit();
	}

	$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
        $qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
        $jmAbsen=owlBaris($qAbsen);
        
	$colSpan=intval($jmAbsen)+2;
	echo"<table cellspacing='1' border='0' class='sortable'>
	<thead class=rowheader>
	<tr>
	<td align=center>No</td>
	<td align=center>".$_SESSION['lang']['nama']."</td>
	<td align=center>".$_SESSION['lang']['nik']."</td>
	<td align=center>".$_SESSION['lang']['subbagian']."</td>
	<td align=center hidden>".$_SESSION['lang']['karyawanid']."</td>
	<td align=center>".$_SESSION['lang']['periode']."</td>
	";/*<td>UMP Bulan</td>
	<td>UMP Harian</td>*/
	foreach($test as $ar => $isi)
	{
		$qwe=date('D', strtotime($isi));
		echo"<td width=5px align=center>";
		if($qwe=='Sun')
			echo"<font color=red>".substr($isi,8,2)."</font>"; 
		else echo(substr($isi,8,2)); 
		echo"</td>";
	//	echo"<td>Std</td>";
	//	echo"<td>Upah Dapat</td>";
		
	}
	
	
	echo"
	<td align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['absensi']."</td>
	<td align=center>Uang Makan</td>";//<td>Jumlah Hari Hadir</td>
	$klmpkAbsn=array();
	foreach($test as $ar => $isi)
	{
		$qwe=date('D', strtotime($isi));
	}
	$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
        while($rKet=$qAbsen->fetch())
        {
		$klmpkAbsn[]=$rKet;
	}
	echo"
	</tr></thead>
	<tbody>";//<td>Jumlah</td>
	
	$resData[]=array();
	$hasilAbsn[]=array();
	$umrList[]=array();


			$sAbsn="select absensi,tanggal,karyawanid,kodeorg from ".$dbname.".sdm_absensidt 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg like '%".$unit."%'"
                                . " and absensi in ('H','HL','IDT','IPC','TR','PD','AS','D') ";
			  //echo $sAbsn;
			  //as d
			  //exit("Error".$sAbsn);
			$rAbsn=fetchData($sAbsn);
			foreach ($rAbsn as $absnBrs =>$resAbsn)
			{
				if(!is_null($resAbsn['absensi']))
				{
					$umrList[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('umr'=>'ind');
					//$hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('absensi'=>$resAbsn['absensi']);
                                        $hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('absensi'=>'H');

					$resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
				}

			}
                        
			$sKehadiran="select absensi,tanggal,karyawanid,notransaksi,umr from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg like '%".$unit."%'";
			  //exit("Error".$sKehadiran);
			$rkehadiran=fetchData($sKehadiran);
			foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
			{	
                            if($resKhdrn['absensi']!='')
                            {
                                $umrList[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('umr'=>$resKhdrn['umr']);
                                $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('absensi'=>$resKhdrn['absensi']);

                                $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                            }
			
			}
			
			$sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik,a.notransaksi from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                            where b.notransaksi like '%PNN%' and b.kodeorg like '%".$unit."%' and b.tanggal between '".$tgl1."' and '".$tgl2."'";
                         //exit("Error".$sPrestasi);
			$notran='';			 
			$rPrestasi=fetchData($sPrestasi);
			foreach ($rPrestasi as $presBrs =>$resPres)
			{
					//$umrList[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('umr'=>$resKhdrn['upahkerja']);
				$umrList[$resPres['nik']][$resPres['tanggal']][]=array('umr'=>$resPres['upahkerja']);
				$hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array('absensi'=>'H');
				@$notran[$resPres['nik']][$resPres['tanggal']].='BKM:'.$resPres['notransaksi'].'__';
				$resData[$resPres['nik']][]=$resPres['nik'];

			} 
			
			//print_r($umrList);
			

// ambil pengawas                        
$dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL
    union select tanggal,nikmandor1,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL";
//exit("Error".$dzstr);   upahpremi
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $umrList[$dzbar->nikmandor][$dzbar->tanggal][]=array('umr'=>'ind');
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array('absensi'=>'H');
    @$notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}


// ambil administrasi                       
$dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL
    union select tanggal,keranimuat,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL";

$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $umrList[$dzbar->nikmandor][$dzbar->tanggal][]=array('umr'=>'ind');
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array('absensi'=>'H');
    @$notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}


// ambil traksi                       
$dzstr="SELECT a.upah,a.tanggal,idkaryawan, a.notransaksi FROM ".$dbname.".vhc_runhk a
        left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".substr($unit,0,4)."%'";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
	$umrList[$dzbar->idkaryawan][$dzbar->tanggal][]=array('umr'=>$dzbar->upah);
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array('absensi'=>'H');    
    $notran[$dzbar->idkaryawan][$dzbar->tanggal].='TRAKSI:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}

function kirimnama($nama) // buat ngirim nama lewat javascript. spasi diganti __
{
    $qwe=explode(' ',$nama);
    foreach($qwe as $kyu){
        $balikin.=$kyu.'__';
    }    
    return $balikin;
}

function removeduplicate($notransaksi) // buat ngilangin nomor transaksi yang dobel
{
    $notransaksi=substr($notransaksi,0,-2);    
    $qwe=explode('__',$notransaksi);
    foreach($qwe as $kyu){
        $tumpuk[$kyu]=$kyu;
    }    
    foreach($tumpuk as $tumpz){
        $balikin.=$tumpz.'__';
    }    

    return $balikin;
}

	
        $brt=array();
	
	$lmit=count($klmpkAbsn);
	$a=0;
	foreach($resData as $hslBrs => $hslAkhir)
	{	
		
		if(@$hslAkhir[0]!='' and @$namakar[$hslAkhir[0]]!='')
		{ 
			
			$no+=1;
			echo"<tr class=rowcontent id=row".$no."><td>".$no."</td>";
			echo"
			<td>".$namakar[$hslAkhir[0]]."</td>
			<td>".$nikkar[$hslAkhir[0]]."</td>
			
			<td>".$sbgnb[$hslAkhir[0]]."</td>
			<td id=karyawanid".$no." hidden>".$hslAkhir[0]."</td>
			<td id=periode".$no.">".$per."</td>
			";/*<td>".$jumlahUmr[$hslAkhir[0]]."</td>
			<td>".$umpHari."</td>*/
			
			foreach($test as $barisTgl =>$isiTgl)
			{
                if(@$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']=='H')
                {
                    $cekList=1;//$cekList='V';
                    @$totCekList[$hslAkhir[0]]+=1;
                }
                else
                {
                    $cekList=0;
                }

                 echo"<td>".$arrXV[$cekList]."</td> ";
				 
			}
                echo"<td width=5px  align=right>".$totCekList[$hslAkhir[0]]."</td>";	
                //    $premi=$totCekList[$hslAkhir[0]]*$rupiah;
                echo"<td width=5px  align=right id=premi".$no.">".$totCekList[$hslAkhir[0]]*$rupiah."</td>";	
                echo"</tr>";
			}	
	}
	//echo"<button class=mybutton onclick=saveAll(".$no.");>".$_SESSION['lang']['proses']."</button>";
        echo"<button class=mybutton onclick=detelemakan(".$no.");>".$_SESSION['lang']['proses']."</button>";
	echo"</tbody></table>";
	break;

    case'delete':
        $str="delete from ".$dbname.".sdm_premi where kodeorg='".$unit."' and periode='".$per."' "
        . " and jenis='UANGMAKAN' and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where"
        . " tipekaryawan='".$tipe."') ";
        
      
        try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
    break;
    
    case'uang':
       
        
        $optReg=  makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional');
        @$reg=$optReg[$unit];
        $iUang="select * from ".$dbname.".sdm_5uangmakan where regional='".$reg."' ";
        $nUang=$owlPDO->query($iUang) or die(print " Gagal: ".PDOException::getMessage());
        $nUang->setFetchMode(PDO::FETCH_ASSOC);
        $dUang=$nUang->fetch();
            echo $dUang['rupiah'];
    break;
    
    
	case'savedata':
                
                if($premi=='0' or $premi=='')
		{
		}
		else
		{ 
                    $str="insert into ".$dbname.".sdm_premi (`kodeorg`,`periode`,`karyawanid`,`jenis`,`premi`,`updateby`)
                            values ('".$unit."','".$periode."','".$karyawanid."','UANGMAKAN','".$premi."','".$_SESSION['standard']['userid']."')";

                        try{$owlPDO->exec($str); }
                    catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n"; 
                        die(); 
                    }
                }
                
                
	break;
	
	break;
	default;	
	
}
?>