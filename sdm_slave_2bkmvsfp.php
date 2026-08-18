<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$unit = checkPostGet('unit','');
$divisi = checkPostGet('divisi','');
$tgl1 = tanggalsystemn(checkPostGet('tgl1',''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2',''));

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$stBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$arrPost=array("0"=>"Not Posted","1"=>"Posting");

if($tgl1=='--')
{
    $tgl1='';
}
if($tgl2=='--')
{
    $tgl2='';
}


$golkar=makeOption($dbname,'datakaryawan','karyawanid','kodegolongan');
$namagol=makeOption($dbname,'sdm_5golongan','kodegolongan','namagolongan');
$namatipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

$sGetKary="select sum(c.jumlah) as jumlah,a.kodegolongan,a.karyawanid,a.nik,b.namajabatan,a.namakaryawan,a.tipekaryawan,
           subbagian from ".$dbname.".datakaryawan a 
           left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan 
           left join ".$dbname.".sdm_5gajipokok c on a.karyawanid=c.karyawanid
           where (tanggalkeluar>='".$tgl1."' or tanggalkeluar='0000-00-00') and lokasitugas='".$unit."' and subbagian like '".$divisi."%' and 
           a.tipekaryawan in ('1','2','3','4')  group by a.karyawanid order by namakaryawan asc";  
$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{
        $resData[$kar['karyawanid']][]=$kar['karyawanid'];
   	
	//$karyawanid[$kar['karyawanid']]=$kar['karyawanid'];
	$jumlahUmr[$kar['karyawanid']]=$kar['jumlah'];
        $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
        $nikkar[$kar['karyawanid']]=$kar['nik'];
        $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
        $sbgnb[$kar['karyawanid']]=$kar['subbagian'];
	$tipekaryawan[$kar['karyawanid']]=$kar['tipekaryawan'];
	$golongankar[$kar['karyawanid']]=$kar['kodegolongan'];
}  


		
		
       
        $test = rangeTanggal($tgl1, $tgl2);

	$jmlHari=count($test);
        $colspanTgl=$jmlHari*3;
	//cek max hari inputan
	if($jmlHari>32)
	{
		echo"warning:Range tanggal tidak valid";
		exit();
	}
        

        
	$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
	$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
	$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
	$jmAbsen=owlBaris($qAbsen);
	$colSpan=intval($jmAbsen)+3;
        
       if($proses=='excel')
       {
           $border="border=1";
       }
       else
       {
           $border="border=0";
       }
        
       
      $ind="<table cellspacing='1' $border class='sortable'>";
      $ind.="<thead><tr class=rowheader>
              <td rowspan=3>No</td>
                <td rowspan=3  align=center>".$_SESSION['lang']['nik']."</td>
				<td rowspan=3 align=center>".$_SESSION['lang']['namakaryawan']."</td>
                
                <td colspan=".$colspanTgl."  align=center>".$_SESSION['lang']['tanggal']."</td>
              </tr>";
             $ind.="<tr>";
             foreach($test as $ar => $isi)
            {
                $qwe=date('D', strtotime($isi));
                $ind.="<td colspan=3 width=75px align=center>";
                if($qwe=='Sun')
                {
                    $ind.="<font color=red width=75px align=center>".$isi."</font>"; 
                }
                else 
                {
                    $ind.= $isi;
                }
                $ind.="</td>";
            }
            $ind.="</tr> <tr>"; 
            for($z=1;$z<=$jmlHari;$z++)
            {  
                $ind.="
                <td  align=center>BKM</td>
                <td  align=center>FP</td>
                <td  align=center>BA</td>
                ";
            }  
    $ind.="</tr></thead><tbody>";
       

	
	$resData[]=array();
	$hasilAbsn[]=array();
	$umrList[]=array();

           
        $sKehadiran="select jhk,absensi,tanggal,karyawanid,notransaksi,umr from ".$dbname.".kebun_kehadiran_vw 
            where tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg like '%".$unit."%'";
        //echo $sKehadiran;
        $rkehadiran=fetchData($sKehadiran);
        foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
        {	
                if($resKhdrn['absensi']!='')
                {
                        $umrList[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('umr'=>$resKhdrn['umr']);
                        $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('absensi'=>$resKhdrn['absensi']);
                     //   $notran[$resKhdrn['karyawanid']][$resKhdrn['tanggal']].='BKM:'.$resKhdrn['notransaksi'].'__';
                        $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                }

        }

       

        $sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik,a.notransaksi from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
            where b.notransaksi like '%PNN%' and b.kodeorg like '%".$unit."%' and b.tanggal between '".$tgl1."' and '".$tgl2."'";
         //exit("Error".$sPrestasi);
        $rPrestasi=fetchData($sPrestasi);
        foreach ($rPrestasi as $presBrs =>$resPres)
        {
                        //$umrList[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array('umr'=>$resKhdrn['upahkerja']);
                        $umrList[$resPres['nik']][$resPres['tanggal']][]=array('umr'=>$resPres['upahkerja']);
                        $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array('absensi'=>'H');
                     //   $notran[$resPres['nik']][$resPres['tanggal']].='BKM:'.$resPres['notransaksi'].'__';
                        $resData[$resPres['nik']][]=$resPres['nik'];

        } 
       

        // ambil pengawas                        
        $dzstr="SELECT tanggal,nikmandor,a.notransaksi,b.upahpremi FROM ".$dbname.".kebun_aktifitas a
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL
            union select tanggal,nikmandor1,a.notransaksi,b.upahpremi FROM ".$dbname.".kebun_aktifitas a 
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL";
		$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
		$dzres->setFetchMode(PDO::FETCH_OBJ);
        while($dzbar=$dzres->fetch())
        {
            $umrList[$dzbar->nikmandor][$dzbar->tanggal][]=array('umr'=>'ind');
            $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array('absensi'=>'H');
           // $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
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
           // $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
            $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
        }
        
        
        
        
         // ambil administrasi                       
        $dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL
            union select tanggal,nikasisten,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
            left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
            left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '%".$unit."%' and c.namakaryawan is not NULL";
		$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
		$dzres->setFetchMode(PDO::FETCH_OBJ);
        while($dzbar=$dzres->fetch())
        {
            $umrList[$dzbar->nikmandor][$dzbar->tanggal][]=array('umr'=>'ind');
            $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array('absensi'=>'H');
           // $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
            $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
        }
        
        
        
        $sAbsn="select a.karyawanid,tanggal,a.absensi,kodeorg,catu,insentif
                  from ".$dbname.".sdm_absensidt a left join 
                 ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                  where  (b.tanggalkeluar>='".$tgl1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                  and a.tanggal>='".$tgl1."' and a.tanggal<='".$tgl2."' and 
                  b.lokasitugas='".$unit."' and a.fingerprint='1' order by tanggal"; 
                $rAbsn=fetchData($sAbsn);
        foreach ($rAbsn as $absnBrs =>$resAbsn)
        {
                #jika yang sks dkk dibayar 
                
                   //$umrList[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('umr'=>'ind');//kalo jam tidak berpengaruh
                    $umrList[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('umr'=>$resAbsn['insentif']);
                    // $hasilAbsnFp[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array('absensi'=>$resAbsn['absensi']);
                   //$notran[$resAbsn['karyawanid']][$resAbsn['tanggal']].='ABSENSI:'.$resAbsn['kodeorg'].'__';
                    $resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
               

        }
		
		$sFp="select a.karyawanid, a.tanggalabsen, a.absensi, a.sumber from ".$dbname.".upload_absensi a 
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
		where (b.tanggalkeluar>='".$tgl1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0 and a.tanggalabsen>='".$tgl1."' and a.tanggalabsen<='".$tgl2."' and b.lokasitugas='".$unit."' order by a.tanggalabsen"; 
		$rFp=fetchData($sFp);
		foreach ($rFp as $key =>$val)
		{
			#jika yang sks dkk dibayar 
			if($val['sumber']=='manual')
			{
				$hasilAbsnBa[$val['karyawanid']][$val['tanggalabsen']][]=array('absensi'=>$val['absensi']);				
			}
			else
			{
				$hasilAbsnFp[$val['karyawanid']][$val['tanggalabsen']][]=array('absensi'=>$val['absensi']);
			}
			
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

	
	// $lmit=count($klmpkAbsn);
        
	foreach($resData as $hslBrs => $hslAkhir)
	{	
			
            if(@$hslAkhir[0]!='' and @$namakar[$hslAkhir[0]]!='')
            {
                $no+=1;
                $ind.="<tr class=rowcontent id=row".$no."><td align=center>".$no."</td>";
                $ind.="
                <td width=50px>".$nikkar[$hslAkhir[0]]."</td>
				<td>".$namakar[$hslAkhir[0]]."</td>
                
                ";
                foreach($test as $barisTgl =>$isiTgl)
                {
                    $ind.="<td  align=center>".@$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
                    $ind.="<td  align=center>".@$hasilAbsnFp[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
                    $ind.="<td  align=center>".@$hasilAbsnBa[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
						
					@$tabs[$isiTgl][0]['absensi']+=count($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']);
					@$tfp[$isiTgl][0]['absensi']+=count($hasilAbsnFp[$hslAkhir[0]][$isiTgl][0]['absensi']);
					@$tba[$isiTgl][0]['absensi']+=count($hasilAbsnBa[$hslAkhir[0]][$isiTgl][0]['absensi']);
                }
				$ind.="</tr>";
            }
				
	}			
				
				
				$ind.="<tr class=rowcontent>
					   <td colspan=3 align=center>TOTAL</td>";
				foreach($test as $barisTgl =>$isiTgl){
				$fontcolor='';
				if($tabs[$isiTgl][0]['absensi']!=($tfp[$isiTgl][0]['absensi']+$tba[$isiTgl][0]['absensi'])){
					$fontcolor.=" color=red";
				}
					$ind.="<td  align=center><font ".$fontcolor.">".$tabs[$isiTgl][0]['absensi']."</font></td>";
					$ind.="<td  align=center>".$tfp[$isiTgl][0]['absensi']."</td>";
                    $ind.="<td  align=center>".$tba[$isiTgl][0]['absensi']."</td>";
				}
				
				$ind.="</tr>";
	$ind.="</tbody></table>";
	
        
        
switch($proses)
{

	######PREVIEW
	case 'preview':
             
            
		echo $ind;
    break;
        
        ######EXCEL	
	case 'excel':
            
            
		//$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_BKM_vs_FP".$unit."_".$tgl1."_".$tgl2;
		if(strlen($ind)>0)
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
			if(!fwrite($handle,$ind))
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
}

?>