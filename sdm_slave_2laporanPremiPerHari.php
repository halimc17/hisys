<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

##premi##upah##hk##premitetap##dendahk##dendarp

$proses = checkPostGet('proses','');
$periode = checkPostGet('periode','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdOrg = checkPostGet('kdOrg','');
$afdId = checkPostGet('afdId','');
$thnd=explode("-",$periode);


if($proses!='getSubUnit'){
	$cpremi = checkPostGet('premi','');
	$cpremibasis = checkPostGet('premibasis','');
	$cpremitetap = checkPostGet('premitetap','');
	$cupah = checkPostGet('upah','');
	$cupahpenalty = checkPostGet('upahpenalty','');
	$chk = checkPostGet('hk','');
	$cpenaltykehadiran = checkPostGet('dendahk','');
	$crupiahpenalty = checkPostGet('dendarp','');
    
    $kolspan=0;
    if($cpremi=="1")$kolspan+=1;
    if($cpremibasis=="1")$kolspan+=1;
    if($cpremitetap=="1")$kolspan+=1;
    if($cupah=="1")$kolspan+=1;
    if($cupahpenalty=="1")$kolspan+=1;
    if($chk=="1")$kolspan+=1;    
    if($cpenaltykehadiran=="1")$kolspan+=1;
    if($crupiahpenalty=="1")$kolspan+=1;
    
    if($kolspan==0){
        exit("Please check at least one.");
    }  
}else{
    $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
    $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
       where induk='".$kdOrg."' order by namaorganisasi asc ";	
    $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
	$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
	$qOrg->setFetchMode(PDO::FETCH_ASSOC);
    while($rOrg=$qOrg->fetch())
    {
        $optAfd.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
    }
    echo $optAfd;
    exit;
}

// get post =========================================================================


//echo $cpremi." ".$cupah." ".$chk." ".$cpremitetap." ".$cpenaltykehadiran." ".$crupiahpenalty." ";
//exit;



//if($kdOrg==''){
//    echo "error: Please fill field.";
//    exit;
//}

// get namaorganisasi =========================================================================
        $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kdOrg."' ";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while($rOrg=$qOrg->fetch())
        {
                $nmOrg=$rOrg['namaorganisasi'];
        }
        if(!$nmOrg)$nmOrg=$kdOrg;

// determine begin end =========================================================================
        $lok=substr($kdOrg,0,4); //$_SESSION['empl']['lokasitugas'];
        $sDatez = "select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where periode = '".$periode."' and kodeorg= '".$lok."'";
		$qDatez=$owlPDO->query($sDatez) or die(print " Gagal: ".PDOException::getMessage());
		$qDatez->setFetchMode(PDO::FETCH_ASSOC);
        while($rDatez=$qDatez->fetch())
        {
                $tanggalMulai=$rDatez['tanggalmulai'];
                $tanggalSampai=$rDatez['tanggalsampai'];
        }

function dates_inbetween($date1, $date2)
{
    $day = 60*60*24;
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between
    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);
    for($x = 1; $x < $days_diff; $x++)
        {
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }
    $dates_array[] = date('Y-m-d',$date2);
    return $dates_array;
}
$tgltgl = dates_inbetween($tanggalMulai, $tanggalSampai);
#ambil data premi
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    if($afdId!='')
    {
        $whr="and b.subbagian='".$afdId."' ";
        $whrw=" subbagian='".$afdId."' ";
//        $wprem=" a.kodeorg='".$afdId."'";
        $wprem=" b.subbagian='".$afdId."'";       
    }
    else
    {
        $whr="and b.lokasitugas='".$kdOrg."' ";
        $whrw=" lokasitugas='".$kdOrg."' ";
 //       $wprem=" a.kodeorg like '".$kdOrg."%'";
        $wprem=" b.lokasitugas like '".$kdOrg."%'";        
    }
    $str="select b.nik,a.karyawanid,a.tanggal,sum(a.upahpremi) as upahpremi,sum(a.rupiahpenalty) as dendabkm,sum(a.upahkerja) as gaji,b.kodejabatan,c.namajabatan,b.subbagian from ".$dbname.".kebun_prestasi_vw a 
     left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
     left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
     where substr(a.tanggal,1,7)='".$periode."' ".$whr." and (b.tanggalkeluar>='".$tanggalMulai."' or b.tanggalkeluar='0000-00-00')
     group by a.karyawanid,tanggal
     order by a.karyawanid";   

//ambil data di perawatan
$sql="select b.nik,a.karyawanid,a.tanggal,sum(a.insentif) as upahpremi,sum(a.umr) as gaji,b.kodejabatan,c.namajabatan,b.subbagian from ".$dbname.".kebun_kehadiran_vw a 
     left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
     left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
     where substr(a.tanggal,1,7)='".$periode."' and insentif!=0  ".$whr." and (b.tanggalkeluar>='".$tanggalMulai."' or b.tanggalkeluar='0000-00-00')

     group by a.karyawanid,tanggal
     order by a.karyawanid";    

//ambil data kemandoran
$sql2="select b.nik,a.karyawanid,a.tanggal,sum(a.premiinput) as upahpremi,b.kodejabatan,c.namajabatan,b.subbagian from ".$dbname.".kebun_premikemandoran a 
     left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
     left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
     where a.periode='".$periode."' and premiinput!=0  ".$whr."  and (b.tanggalkeluar>='".$tanggalMulai."' or b.tanggalkeluar='0000-00-00')
     and a.posting=1
     group by a.karyawanid,tanggal 
     order by a. karyawanid";
//ambil data traksi
//premi traksi
$sql3="select b.nik,a.idkaryawan as karyawanid,a.tanggal,sum(a.premi) as upahpremi,sum(a.penalty) as dendabkm,sum(a.upah) as gaji, b.kodejabatan,c.namajabatan,b.subbagian from ".$dbname.".vhc_runhk a 
     left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
     left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
     where substr(a.tanggal,1,7)='".$periode."'    ".$whr."  and (b.tanggalkeluar>='".$tanggalMulai."' or b.tanggalkeluar='0000-00-00')
     group by a.idkaryawan,tanggal 
     order by a. idkaryawan";

$spremi="select sum(premi+insentif) as premi,a.karyawanid,tanggal,sum(penaltykehadiran) as pinalti, b.kodejabatan,c.namajabatan,b.subbagian
         from ".$dbname.". sdm_absensidt a
         left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
         left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
         where ".$wprem."  and left(tanggal,7)='".$periode."'
         group by  a.karyawanid,tanggal order by a.karyawanid";
$sbasis="select * from ".$dbname.".kebun_prestasi_vw where kodeorg like '".$afdId."%' and tanggal like '".$periode."%'";
}
else
{
    if(strlen($kdOrg)>4)
    {
        $whr="and b.subbagian='".$kdOrg."' ";
        $whrw=" subbagian='".$kdOrg."' ";
//        $wprem=" a.kodeorg='".$afdId."'";
        $wprem=" b.subbagian='".$afdId."'";  
    }
    else
    {
        $whr="and b.lokasitugas='".$kdOrg."' ";
        $whrw=" lokasitugas='".$kdOrg."' ";
 //       $wprem=" a.kodeorg like '".$kdOrg."%'";
        $wprem=" b.lokasitugas like '".$kdOrg."%'";
    }
$str="select b.nik,a.karyawanid,a.tanggal,sum(a.upahpremi) as upahpremi,sum(a.rupiahpenalty) as dendabkm,sum(a.upahkerja) as gaji,b.kodejabatan,c.namajabatan,b.subbagian from ".$dbname.".kebun_prestasi_vw a 
     left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
     left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
     where substr(a.tanggal,1,7)='".$periode."' ".$whr." and (b.tanggalkeluar>='".$tanggalMulai."' or b.tanggalkeluar='0000-00-00')
     group by a.karyawanid,tanggal
     order by a.karyawanid";   

//ambil data di perawatan
$sql="select b.nik,a.karyawanid,a.tanggal,sum(a.insentif) as upahpremi,sum(a.umr) as gaji,b.kodejabatan,c.namajabatan,b.subbagian from ".$dbname.".kebun_kehadiran_vw a 
     left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
     left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
     where substr(a.tanggal,1,7)='".$periode."' and insentif!=0  ".$whr." and (b.tanggalkeluar>='".$tanggalMulai."' or b.tanggalkeluar='0000-00-00')

     group by a.karyawanid,tanggal
     order by a.karyawanid";    

//ambil data kemandoran
$sql2="select b.nik,a.karyawanid,a.tanggal,sum(a.premiinput) as upahpremi,b.kodejabatan,c.namajabatan,b.subbagian from ".$dbname.".kebun_premikemandoran a 
     left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
     left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
     where a.preiode='".$periode."' and premiinput!=0  ".$whr."  and (b.tanggalkeluar>='".$tanggalMulai."' or b.tanggalkeluar='0000-00-00')
     and a.posting=1
     group by a.karyawanid,tanggal 
     order by a. karyawanid";
//ambil data traksi
//premi traksi
$sql3="select b.nik,a.idkaryawan as karyawanid,a.tanggal,sum(a.premi) as upahpremi,sum(a.penalty) as dendabkm,sum(a.upah) as gaji, b.kodejabatan,c.namajabatan,b.subbagian from ".$dbname.".vhc_runhk a 
     left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
     left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
     where substr(a.tanggal,1,7)='".$periode."'    ".$whr."  and (b.tanggalkeluar>='".$tanggalMulai."' or b.tanggalkeluar='0000-00-00')
     group by a.idkaryawan,tanggal 
     order by a. idkaryawan";
$spremi="select sum(premi+insentif) as premi,a.karyawanid,tanggal,sum(penaltykehadiran) as pinalti, b.kodejabatan,c.namajabatan,b.subbagian
         from ".$dbname.". sdm_absensidt a
         left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
         left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
         where ".$wprem." and left(tanggal,7)='".$periode."'
          group by  a.karyawanid,tanggal order by a.karyawanid";

$sbasis="select * from ".$dbname.".kebun_prestasi_vw 
         where kodeorg like '".substr($kdOrg,0,4)."%' and tanggal like '".$periode."%'";
}
//exit("error: ".$sbasis);

//echo $sql.'<br>';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$jab=Array();
$prem=Array();
while($bar=$res->fetch())
{
    $nik[$bar->karyawanid]=$bar->nik;
    $jab[$bar->karyawanid]=$bar->namajabatan;
    $prem[$bar->karyawanid][$bar->tanggal]=$bar->upahpremi;    
    $dtgaji[$bar->karyawanid][$bar->tanggal]=$bar->gaji;
    $sb[$bar->karyawanid]=$bar->subbagian;
    $dendBkm[$bar->karyawanid][$bar->tanggal]=$bar->dendabkm;
}
$qData=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_OBJ);
while($rData=$qData->fetch())
{
    $nik[$rData->karyawanid]=$rData->nik;
    $jab[$rData->karyawanid]=$rData->namajabatan;
    $prem[$rData->karyawanid][$rData->tanggal]=$rData->upahpremi;   
    $dtgaji[$rData->karyawanid][$rData->tanggal]=$rData->gaji;
    $sb[$rData->karyawanid]=$rData->subbagian;
}
$qData2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
$qData2->setFetchMode(PDO::FETCH_OBJ);
while($rData2=$qData2->fetch())
{
    $nik[$rData2->karyawanid]=$rData2->nik;
    $jab[$rData2->karyawanid]=$rData2->namajabatan;
    $prem[$rData2->karyawanid][$rData2->tanggal]=$rData2->upahpremi;   
    
    $sb[$rData2->karyawanid]=$rData2->subbagian;
}
$qData3=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
$qData3->setFetchMode(PDO::FETCH_OBJ);
while($rData3=$qData3->fetch())
{
    if($rData3->upahpremi!=0)
    {
    $nik[$rData3->karyawanid]=$rData3->nik;
    $jab[$rData3->karyawanid]=$rData3->namajabatan;
    $prem[$rData3->karyawanid][$rData3->tanggal]=$rData3->upahpremi; 
    $dtgaji[$rData3->karyawanid][$rData3->tanggal]=$rData3->gaji;
    $sb[$rData3->karyawanid]=$rData3->subbagian;
    $dendBkm[$rData3->karyawanid][$rData3->tanggal]=$rData3->dendabkm;
    }
}
$qDataPrem=$owlPDO->query($spremi) or die(print " Gagal: ".PDOException::getMessage());
$qDataPrem->setFetchMode(PDO::FETCH_ASSOC);
while($rPremiTtp=$qDataPrem->fetch()){
    $premiTtp[$rPremiTtp['karyawanid']][$rPremiTtp['tanggal']]=$rPremiTtp['premi'];
    $dendaKehadiran[$rPremiTtp['karyawanid']][$rPremiTtp['tanggal']]=$rPremiTtp['pinalti'];
    $nik[$rPremiTtp['karyawanid']]=$rPremiTtp['karyawanid'];
    $sb[$rPremiTtp['karyawanid']]=$rPremiTtp['subbagian'];
    $jab[$rPremiTtp['karyawanid']]=$rPremiTtp['namajabatan'];
}
$qbasis=$owlPDO->query($sbasis) or die(print " Gagal: ".PDOException::getMessage());
$qbasis->setFetchMode(PDO::FETCH_OBJ);
while($rbasis= $qbasis->fetch()){
     $basis[$rbasis->karyawanid][$rbasis->tanggal]=$rbasis->premibasis;
     $penalty[$rbasis->karyawanid][$rbasis->tanggal]=$rbasis->upahpenalty;
}

#ambil karyawan
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
  $str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".substr($kdOrg,0,4)."'
    and (tanggalkeluar>='".$tanggalMulai."' or tanggalkeluar='0000-00-00')";   
}
 else {
$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where ".$whrw."
    and (tanggalkeluar>='".$tanggalMulai."' or tanggalkeluar='0000-00-00')";    
}

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$karid=Array();
while($bar=$res->fetch())
{
    if(isset($jab[$bar->karyawanid]))#jika terdaftar pada premi maka sertakan
        $karid[$bar->karyawanid]=$bar->namakaryawan;
}
$brd=0;
$bgclr="align='center'";
if($proses=='excel')
{
    $brd=1;
    $bgclr="bgcolor='#DEDEDE' align='center'";
}
$stream="Laporan_premi_per_hari_".$kdOrg."_".$periode; 
#preview: nampilin header ================================================================================
        $stream.="<table cellspacing='1' border='".$brd."' class='sortable' width='20000px'>
        <thead class=rowheader>
        <tr>
        <td ".$bgclr."  rowspan=2>No</td>
        <td ".$bgclr." rowspan=2>".$_SESSION['lang']['nama']."</td>
        <td ".$bgclr." rowspan=2>".$_SESSION['lang']['nik']."</td>
        <td ".$bgclr." rowspan=2>".$_SESSION['lang']['jabatan']."</td>
        <td ".$bgclr." rowspan=2>".$_SESSION['lang']['subunit']."</td>";
        foreach($tgltgl as $ar => $isi)
        {
                $qwe=date('D', strtotime($isi));
                $stream.="<td   ".$bgclr."  colspan=".$kolspan.">";
                if($qwe=='Sun')
                    $stream.="<font color=red>".substr($isi,8,2)."</font>"; 
                else 
                    $stream.=(substr($isi,8,2)); 
                $stream.="</td>";
        }
        $stream.="<td ".$bgclr."  colspan=".$kolspan.">".$_SESSION['lang']['jumlah']."</td></tr><tr>";
        foreach($tgltgl as $ar => $isi)
        {
                $qwe=date('D', strtotime($isi));
                ;
                if($qwe=='Sun'){ 
                   if($cpremi=="1")$stream.="<td  ".$bgclr."><font color=red>".$_SESSION['lang']['premi']."</font></td>";
                   if($cpremibasis=="1")$stream.="<td   ".$bgclr."><font color=red>".$_SESSION['lang']['premibasis']."</font></td>";
                   if($cpremitetap=="1")$stream.="<td  ".$bgclr."><font color=red>".$_SESSION['lang']['premitetap']."</font></td>";
                   if($cupah=="1")$stream.="<td   ".$bgclr."><font color=red>".$_SESSION['lang']['upah']."</font></td>"; 
                   if($cupahpenalty=="1")$stream.="<td  ".$bgclr."><font color=red>".$_SESSION['lang']['upahpenalty2']."</font></td>"; 
                   if($chk=="1")$stream.="<td  ".$bgclr."><font color=red>".$_SESSION['lang']['hk']."</font></td>"; 
                   if($cpenaltykehadiran=="1")$stream.="<td   ".$bgclr."><font color=red>".$_SESSION['lang']['penaltykehadiran']."</font></td>"; 
                   if($crupiahpenalty=="1")$stream.="<td   ".$bgclr."><font color=red>".$_SESSION['lang']['rupiahpenalty']."</font></td>";
                   
                }
                else{ 
                    if($cpremi=="1")$stream.="<td  ".$bgclr.">".$_SESSION['lang']['premi']."</td>"; 
                    if($cpremibasis=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['premibasis']."</td>";
                    if($cpremitetap=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['premitetap']."</td>"; 
                    if($cupah=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['upah']."</td>";
                    if($cupahpenalty=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['upahpenalty2']."</td>";
                    if($chk=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['hk']."</td>"; 
                    if($cpenaltykehadiran=="1")$stream.="<td  ".$bgclr.">".$_SESSION['lang']['penaltykehadiran']."</td>"; 
                    if($crupiahpenalty=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['rupiahpenalty']."</td>"; 
                    
                }
        }
        if($cpremi=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['premi']."</td>";
        if($cpremibasis=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['premibasis']."</td>";
        if($cpremitetap=="1")$stream.="<td ".$bgclr.">".$_SESSION['lang']['premitetap']."</td>";
        if($cupah=="1")$stream.="<td ".$bgclr.">".$_SESSION['lang']['upah']."</td>";
        if($cupahpenalty=="1")$stream.="<td  ".$bgclr.">".$_SESSION['lang']['upahpenalty2']."</td>";
        if($chk=="1")$stream.="<td  ".$bgclr.">".$_SESSION['lang']['hk']."</td>";
        if($cpenaltykehadiran=="1")$stream.="<td  ".$bgclr.">".$_SESSION['lang']['penaltykehadiran']."</td>";
        if($crupiahpenalty=="1")$stream.="<td   ".$bgclr.">".$_SESSION['lang']['rupiahpenalty']."</td></tr>"; 
        
        $stream.="</thead>
        <tbody>";
           # preview: nampilin data ================================================================================
		   $tottgl= Array();
		   $totGjpertgl= Array();
		   $totPremittppertgl= Array();
		   $totDendaKhdrnpertgl= Array();
		   $totDendaBkmpertgl= Array();
		   $totHkPertgl= Array();
		   $totbasispertgl= Array();
		   $totpenaltypertgl= Array();
		   $totperkar= Array();
		   $totPremittp= Array();
		   $totDendaKhdrn= Array();
		   $totDendaBkm= Array();
		   $totbasiskar= Array();
		   $totpenaltykar= Array();
        foreach($karid as $id=>$val)
        {
            $no+=1;
            $stream.="<tr class=rowcontent><td>".$no."</td>
            <td>".$val."</td>
            <td>".$nik[$id]."</td>
            <td>".$jab[$id]."</td>
            <td>".$sb[$id]."</td>";
            foreach($tgltgl as $key=>$tangval)
            {	             
                    $whrd="karyawanid='".$id."' and tahun='".$thnd[0]."' and idkomponen=1";
                    $optGapok=makeOption($dbname, 'sdm_5gajipokok', 'karyawanid,jumlah',$whrd);
                    @$hk[$id][$tangval]=(($dtgaji[$id][$tangval]/($optGapok[$id]/25))*100)/100; 
                    if($cpremi=="1")$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$id."','".$tangval."',event)>".@number_format($prem[$id][$tangval],2)."</td>";
                    if($cpremibasis=="1")$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$id."','".$tangval."',event)>".@number_format($basis[$id][$tangval],2)."</td>";
                    if($cpremitetap=="1")$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$id."','".$tangval."',event)>".@number_format($premiTtp[$id][$tangval],2)."</td>";
                    if($cupah=="1")$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$id."','".$tangval."',event)>".@number_format($dtgaji[$id][$tangval],2)."</td>";
                    if($cupahpenalty=="1")$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$id."','".$tangval."',event)>".@number_format($penalty[$id][$tangval],2)."</td>";
                    if($chk=="1")$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$id."','".$tangval."',event)>".@number_format($hk[$id][$tangval],2)."</td>";
                    if($cpenaltykehadiran=="1")$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$id."','".$tangval."',event)>".@number_format($dendaKehadiran[$id][$tangval],2)."</td>";
                    if($crupiahpenalty=="1")$stream.="<td title='Click untuk melihat notransaksi.' align=right style=\"cursor: pointer\" onclick=showpopup('".$id."','".$tangval."',event)>".@number_format($dendBkm[$id][$tangval],2)."</td>";
                    
                    @$tottgl[$tangval]+=$prem[$id][$tangval];
                    @$totGjpertgl[$tangval]+=$dtgaji[$id][$tangval];
                    @$totPremittppertgl[$tangval]+=$premiTtp[$id][$tangval];
                    @$totDendaKhdrnpertgl[$tangval]+=$dendaKehadiran[$id][$tangval];
                    @$totDendaBkmpertgl[$tangval]+=$dendBkm[$id][$tangval];
                    @$totHkPertgl[$tangval]+=$hk[$id][$tangval];
                    @$totbasispertgl[$tangval]+=$basis[$id][$tangval];
                    @$totpenaltypertgl[$tangval]+=$penalty[$id][$tangval];
                    
                    @$totperkar[$id]+=$prem[$id][$tangval];
                    @$totGjperkar[$id]+=$dtgaji[$id][$tangval];
                    @$totPremittp[$id]+=$premiTtp[$id][$tangval];
                    @$totDendaKhdrn[$id]+=$dendaKehadiran[$id][$tangval];
                    @$totDendaBkm[$id]+=$dendBkm[$id][$tangval];
                    @$totHkDt[$id]=$hk[$id][$tangval];
                    @$totbasiskar[$id]+=$basis[$id][$tangval];
                    @$totpenaltykar[$id]+=$penalty[$id][$tangval];
            }
            if($cpremi=="1")$stream.="<td align=right>".number_format($totperkar[$id],2)."</td>";
            if($cpremibasis=="1")$stream.="<td align=right>".number_format($totbasiskar[$id],2)."</td>";
            if($cpremitetap=="1")$stream.="<td align=right>".number_format($totPremittp[$id],2)."</td>";
            if($cupah=="1")$stream.="<td align=right>".number_format($totGjperkar[$id],2)."</td>";
            if($cupahpenalty=="1")$stream.="<td align=right>".number_format($totpenaltykar[$id],2)."</td>";
            if($chk=="1")$stream.="<td align=right>".number_format($totHkDt[$id],2)."</td>";
            if($cpenaltykehadiran=="1")$stream.="<td align=right>".number_format($totDendaKhdrn[$id],2)."</td>";
            if($crupiahpenalty=="1")$stream.="<td align=right>".number_format($totDendaBkm[$id],2)."</td>";
            
            $stream.="</tr>";
        }  
           # preview: nampilin total ================================================================================
        $stream.="<thead class=rowheader>
        <tr>
        <td colspan=5>Total</td>";
		$total=0;;
		$totGj=0;
		$totPrmTtt=0;
		$totDenda=0;
		$grTotHk=0;
		$totDendaBkmder=0;
		$totbasis2=0;
		$totpenalty2=0;		
        foreach($tgltgl as $ar => $isi)
        {
                if($cpremi=="1")$stream.="<td align=right>".number_format($tottgl[$isi],2)."</td>";
                if($cpremibasis=="1")$stream.="<td align=right>".number_format($totbasispertgl[$isi],2)."</td>";
                if($cpremitetap=="1")$stream.="<td align=right>".number_format($totPremittppertgl[$isi],2)."</td>";
                if($cupah=="1")$stream.="<td align=right>".number_format($totGjpertgl[$isi],2)."</td>";
                if($cupahpenalty=="1")$stream.="<td align=right>".number_format($totpenaltypertgl[$isi],2)."</td>";
                if($chk=="1")$stream.="<td align=right>".number_format($totHkPertgl[$isi],2)."</td>";
                if($cpenaltykehadiran=="1")$stream.="<td align=right>".number_format($totDendaKhdrnpertgl[$isi],2)."</td>";
                if($crupiahpenalty=="1")$stream.="<td align=right>".number_format($totDendaBkmpertgl[$isi],2)."</td>";
                
                $total+=$tottgl[$isi];
                $totGj+=$totGjpertgl[$isi];
                $totPrmTtt+=$totPremittppertgl[$isi];
                $totDenda+=$totDendaKhdrnpertgl[$isi];
                $grTotHk+=$totHkPertgl[$isi];
                $totDendaBkmder+=$totDendaBkmpertgl[$isi];
                $totbasis2+=$totbasispertgl[$isi];
                $totpenalty2+=$totpenaltypertgl[$isi];
        }
        if($cpremi=="1")$stream.="<td align=right>".number_format($total,2)."</td>";
        if($cpremibasis=="1")$stream.="<td align=right>".number_format($totbasis2,2)."</td>";
        if($cpremitetap=="1")$stream.="<td align=right>".number_format($totPrmTtt,2)."</td>";
        if($cupah=="1")$stream.="<td align=right>".number_format($totGj,2)."</td>";
        if($cupahpenalty=="1")$stream.="<td align=right>".number_format($totpenalty2,2)."</td>";
        if($chk=="1")$stream.="<td align=right>".number_format($grTotHk,2)."</td>";
        if($cpenaltykehadiran=="1")$stream.="<td align=right>".number_format($totDenda,2)."</td>";
        if($crupiahpenalty=="1")$stream.="<td align=right>".number_format($totDendaBkmder,2)."</td>";
        
        $stream.="</tr></tbody></table>";

switch($proses)
{
        case'preview':
          echo $stream;
        break;
        case 'excel':
            $nop_="Laporan_premi_per_hari_".$kdOrg."_".$periode."__".date("His");
            if(strlen($stream)>0)
            {
                $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $stream);
                gzclose($gztralala);
                 echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
//                $handle=fopen("tempExcel/".$nop_.".xls",'w');
//                if(!fwrite($handle,$stream))
//                {
//                    echo "<script language=javascript1.2>
//                    parent.window.alert('Can't convert to excel format');
//                    </script>";
//                    exit;
//                }
//                else
//                {
//                    echo "<script language=javascript1.2>
//                    window.location='tempExcel/".$nop_.".xls';
//                    </script>";
//                }
//                fclose($handle);
            }           
            break;
//        case'pdf':
//
//        class PDF extends FPDF
//        {
//                function Header() {
//                global $conn;
//                global $dbname;
//                global $align;
//                global $length;
//                global $colArr;
//                global $title;
//                global $periode;
//                global $nmOrg;
//                global $tgltgl;       
//
//
//                                $jmlHari=count($tgltgl);
//                                $jmlHari=$jmlHari*10;
//                                $cols=247.5;
//                            # Alamat & No Telp
//                $query = selectQuery($dbname,'organisasi','alamat,telepon',
//                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
//                $orgData = fetchData($query);
//
//                $width = $this->w - $this->lMargin - $this->rMargin;
//                $height = 20;
//                $path='images/logo.jpg';
//                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
//                $this->SetFont('Arial','B',9);
//                $this->SetFillColor(255,255,255);	
//                $this->SetX(100);   
//                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
//                $this->SetX(100); 		
//                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
//                $this->SetX(100); 			
//                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
//                $this->Line($this->lMargin,$this->tMargin+($height*4),
//                $this->lMargin+$width,$this->tMargin+($height*4));
//                $this->Ln();
//
//                $this->SetFont('Arial','B',10);
//                                $this->Cell((20/100*$width)-5,$height,strtoupper($_SESSION['lang']['laporanPremi']),'',0,'L');
//                                $this->Ln();
//                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['unit'])." :". $nmOrg,'',0,'L');
//                                $this->Ln();
//                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :". $periode,'',0,'L');
//                                $this->Ln();
//                                $this->Ln();
//                $this->SetFont('Arial','B',6);
//                $this->SetFillColor(220,220,220);
//                                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
//                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['nama'],1,0,'C',1);		
//                                $this->Cell(6/100*$width,$height,$_SESSION['lang']['jabatan'],1,0,'C',1);	
//
//                                $smpng=$this->GetX();
//                                $atas=$this->GetY();
//                                $this->SetY($atas);
//                                $this->SetX($smpng);
//                                $this->SetFont('Arial','B',4);
//                                foreach($tgltgl as $ar => $isi)
//                                {
//                                        $this->Cell(2.5/100*$width,$height,substr($isi,8,2),1,0,'C',1);	
//                                        $akhirX=$this->GetX();
//                                }	
//                                $this->SetY($this->GetY());
//                                $this->SetX($akhirX);
//                                $this->Cell(5/100*$width,$height,$_SESSION['lang']['jumlah'],1,1,'C',1);
//            }
//
//            function Footer()
//            {
//                $this->SetY(-15);
//                $this->SetFont('Arial','I',8);
//                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
//            }
//        }
//        $pdf=new PDF('L','pt','Legal');
//        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
//        $height = 12;
//
//                $pdf->AddPage();
//                $pdf->SetFillColor(255,255,255);
//
//                foreach($karid as $id=>$val)
//                {
//                    $nor+=1;
//                    $pdf->SetFont('Arial','',6);
//                    
//                    $pdf->Cell(3/100*$width,$height,$nor,1,0,'C',1);
//                    $pdf->Cell(8/100*$width,$height,$val,1,0,'L',1);		
//                    $pdf->Cell(6/100*$width,$height,$jab[$id],1,0,'L',1);	
//                    $pdf->SetFont('Arial','',4);
//                    foreach($tgltgl as $key=>$tangval)
//                    {
////                            $pdf->Cell(2.5/100*$width,$height,number_format($tottgl[$isi],2),1,0,'R',1);
//                            $pdf->Cell(2.5/100*$width,$height,number_format($prem[$id][$tangval],2),1,0,'R',1);
////                            $tottgl[$tangval]+=$prem[$id][$tangval];
////                            $totperkar+=$tottgl[$tangval];
//                            $totperkar+=$prem[$id][$tangval];
//                    }	
//                    $pdf->Cell(5/100*$width,$height,number_format($totperkar,2),1,1,'R',1);
//                }
//                 $pdf->Cell(3/100*$width,$height,'TOTAL',1,0,'C',0);
//                 $pdf->Cell(8/100*$width,$height,'',1,0,'C',0);
//                 $pdf->Cell(6/100*$width,$height,'',1,0,'C',1);
//                     # preview: nampilin total ================================================================================
//                    foreach($tgltgl as $ar => $isi)
//                    {
//                            $pdf->Cell(2.5/100*$width,$height,number_format($tottgl[$isi],2),1,0,'R',1);
////                            $total+=$tottgl[$isi];
//                    }
//                    $pdf->Cell(5/100*$width,$height,number_format($total,2),1,0,'R',1);
//              
//              $pdf->Output();
//           
//            break;
//            case'getSubUnit':
//
//            break;
}    
?>