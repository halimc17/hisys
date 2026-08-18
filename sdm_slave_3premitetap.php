<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$per=checkPostGet('perpremitetap','');
$unit=checkPostGet('unitpremitetap','');
$tipe=checkPostGet('tipepremitetap','');

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



/*$sGetKary="select * from ".$dbname.".datakaryawan  where  "
        . " tipekaryawan='".$tipe."'  and  lokasitugas='".$unit."' group by karyawanid order by namakaryawan asc";    
*/

$sGetKary="select * from ".$dbname.".datakaryawan  where  "
        . " tipekaryawan='".$tipe."'  and  lokasitugas='".$unit."' "
        . " and (tanggalkeluar>='".$tgl1."' or tanggalkeluar='0000-00-00') "
        . " and (tanggalmasuk<='".$tgl2."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)"
        . " order by namakaryawan asc";  



 //$sGetKar="";
// echo $sGetKary; exit;
$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{ 
    $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
    $nikkar[$kar['karyawanid']]=$kar['nik'];
    @$nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
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
 
	
	
###########	
	
	
	if((@$tgl_1!='')&&(@$tgl_2!=''))
	{
		$tgl1=$tgl_1;
		$tgl2=$tgl_2;
	}
	
	$test = rangeTanggal($tgl1, $tgl2);
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
            <td align=center>Tj</td>
	";
	foreach($test as $ar => $isi)
	{
            $qwe=date('D', strtotime($isi));
            echo"<td width=5px align=center>";
            if($qwe=='Sun')
                    echo"<font color=red>".substr($isi,8,2)."</font>"; 
            else echo(substr($isi,8,2)); 
            echo"</td>";
	}
	
	
	echo"
	<td align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['absensi']."</td>
	<td align=center>Premi Tetap</td>";//<td>Jumlah Hari Hadir</td>
	echo"
	</tr></thead>
	<tbody>";//<td>Jumlah</td>
	
	$resData[]=array();
	$hasilAbsn[]=array();
	$umrList[]=array();

        
        ##ambil besaran tj perhari dari sdm_5gapok
        $iTj="select * from ".$dbname.".sdm_5gajipokok where tahun='".substr($per,0,4)."' and idkomponen='40' "
                . " and karyawanid in (select karyawanid from ".$dbname.".datakaryawan "
                . " where lokasitugas='".$unit."' and tipekaryawan='".$tipe."')";
        $nTj=$owlPDO->query($iTj) or die(print " Gagal: ".PDOException::getMessage());
		$nTj->setFetchMode(PDO::FETCH_ASSOC);
		while($dTj=$nTj->fetch()) {
            $uangtj[$dTj['karyawanid']]=$dTj['jumlah'];
            $uangtjhr[$dTj['karyawanid']]=$dTj['jumlah']/25;
        }
        
        /*echo"<pre>";
        print_r($uangtjhr);
        echo"</pre>";
        */
        
 
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
    @$notran[$dzbar->idkaryawan][$dzbar->tanggal].='TRAKSI:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}


	
    $brt=array();

    @$lmit=count($klmpkAbsn);
    $a=0;
    
   
    
    
    foreach($resData as $hslBrs => $hslAkhir)
    {	

        if(@$hslAkhir[0]!='' and @$namakar[$hslAkhir[0]]!='')
        { 

            if(@$uangtj[$hslAkhir[0]]=='' || @$uangtj[$hslAkhir[0]]==0)
            {
                $uangtj[$hslAkhir[0]]=0;
            }
            
            $no+=1;
            echo"<tr class=rowcontent id=row".$no."><td>".$no."</td>";
            echo"
            <td>".$namakar[$hslAkhir[0]]."</td>
            <td>".$nikkar[$hslAkhir[0]]."</td>
            <td>".$sbgnb[$hslAkhir[0]]."</td>
            <td id=karyawanid".$no." hidden>".$hslAkhir[0]."</td>
            <td id=periode".$no.">".$per."</td>
            <td>".$uangtj[$hslAkhir[0]]."</td>
            ";/*<td>".$jumlahUmr[$hslAkhir[0]]."</td>
            <td>".$umpHari."</td>*/
            foreach($test as $barisTgl =>$isiTgl)
            {
                if(@$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']=='H'){
                    $cekList=1;//$cekList='V';
                    @$totCekList[$hslAkhir[0]]+=1;
                }else {
                    $cekList=0;
                }

                 echo"<td>".$arrXV[$cekList]."</td> ";
                //}                    

            }
            echo"<td width=5px  align=right>".$totCekList[$hslAkhir[0]]."</td>";	
            //    $premi=$totCekList[$hslAkhir[0]]*$rupiah;
            
            
            
            if(@$totCekList[$hslAkhir[0]]*@$uangtjhr[$hslAkhir[0]]>=@$uangtj[$hslAkhir[0]])
            {
                $premidapat=$uangtj[$hslAkhir[0]];
            }
            else
            {
                $premidapat=$totCekList[$hslAkhir[0]]*$uangtjhr[$hslAkhir[0]];
            }
            
            echo"<td width=5px  align=right id=premi".$no.">".$premidapat."</td>";	
            echo"</tr>";
        }	
    }
    //echo"<button class=mybutton onclick=saveAlltjabsen(".$no.");>".$_SESSION['lang']['proses']."</button>";
    echo"<button class=mybutton onclick=deletpremitetap(".$no.");>".$_SESSION['lang']['proses']."</button>";
    echo"</tbody></table>";
    break;


    case'delete':
        $str="delete from ".$dbname.".sdm_premi where kodeorg='".$unit."' and periode='".$per."' "
        . " and jenis='PREMITETAP' and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where"
        . " tipekaryawan='".$tipe."') ";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
    break;
    
    
    case'savedata':
        
        if($premi=='0' or $premi==''){
        }else { 
            $str="insert into ".$dbname.".sdm_premi (`kodeorg`,`periode`,`karyawanid`,`jenis`,`premi`,`updateby`)
            values ('".$unit."','".$periode."','".$karyawanid."','PREMITETAP','".$premi."','".$_SESSION['standard']['userid']."')";
            try{$owlPDO->exec($str);
                }
                catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        }
        
    break;
	
  
    default;	
	
	
}

?>