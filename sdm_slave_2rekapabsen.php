<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdeOrg=checkPostGet('kdeOrg','');
$kdOrg=checkPostGet('kdOrg','');
$afdId=checkPostGet('afdId','');
$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$tgl_1=tanggalsystem(checkPostGet('tgl_1',''));
$tgl_2=tanggalsystem(checkPostGet('tgl_2',''));
$periodeGaji=checkPostGet('periode','');
$periode=explode('-',checkPostGet('periode',''));
$kdUnit=checkPostGet('kdUnit','');
$idKry=checkPostGet('idKry','');
$tipeKary=checkPostGet('tipeKary','');
$sistemGaji=checkPostGet('sistemGaji','');



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

$where = $wherez = $dmna = "";

//ambil query untuk data karyawan
        if($kdOrg!='')
        {
                $kodeOrg=$kdOrg;
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                {
                        $where="  lokasitugas in ('".$kodeOrg."')";
                        if($afdId!='')
                        {			
                            $where="  subbagian='".$afdId."'";		
                        }

                }
                else
                {
                        if(strlen($kodeOrg)>4)
                        {			
                                $where="  subbagian='".$kodeOrg."'";		
                        }
                        else
                        {
                                $where="  lokasitugas='".$kodeOrg."'";
                        }
                }
        }
        else
        {
                $kodeOrg=$_SESSION['empl']['lokasitugas'];
                $where="  lokasitugas='".$kodeOrg."'";
        }
        if($tipeKary!='')
        {
            $where.=" and tipekaryawan='".$tipeKary."'";
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        

		
//cek datakaryawan history
$dakarbulanan=0;
$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$kdOrg."' and periodegaji='".$periodeGaji."' ";
$res = fetchdata($str);
if(count($res)>0)
{ 
    $dakarbulanan=1;
}

$whrtgl=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tgl1."')";
if($dakarbulanan == 0){
    $sGetKary="select a.karyawanid,a.nik,b.namajabatan,a.namakaryawan,subbagian from ".$dbname.".datakaryawan a 
    left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where
    ".$where." ".$wherez." ".$whrtgl." order by namakaryawan asc";  
}else{
    $sGetKary="select a.karyawanid,a.nik,b.namajabatan,a.namakaryawan,subbagian from ".$dbname.".datakaryawan_hist a 
    left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where
    ".$where." ".$wherez." ".$whrtgl." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."'  order by namakaryawan asc";  
}
$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{
   // $resData[$kar['karyawanid']][]=$kar['karyawanid'];
    $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
    $nikkar[$kar['karyawanid']]=$kar['nik'];
    $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
    $sbgnb[$kar['karyawanid']]=$kar['subbagian'];
}  
//exit("Error:$afdId");
$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,regional');
$regorg=$regional[substr($kodeOrg,0,4)];

$unit=substr($kodeOrg,0,4);	
$kept= makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$kodept=$kept[$unit];

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    //$dimanaPnjng=" substring(kodeorg,1,4)='".$kodeOrg."'";
		$dimanaPnjng=" substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
    // $dimanaPnjng2=" substring(kodeorg,1,4)='".$kodeOrg."'";
		$dimanaPnjng2=" substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
	//$dimanaPnjng3=" substr(b.kodeorg,1,4)='".$kodeOrg."'";
		$dimanaPnjng3=" substring(b.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
    if($afdId!='')
    {
        //$dimanaPnjng=" kodeorg like '".substr($afdId,0,4)."%'";
			$dimanaPnjng=" substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
        //$dimanaPnjng2=" substring(kodeorg,1,4)='".substr($afdId,0,4)."'";
			$dimanaPnjng2=" substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
		//$dimanaPnjng3=" substr(b.kodeorg,1,4)='".substr($afdId,0,4)."'";
			$dimanaPnjng3=" substring(b.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
    }
}
else
{
    if(strlen($kodeOrg)>4)
    {
        //$dimanaPnjng=" kodeorg='".$kodeOrg."'";
			$dimanaPnjng=" substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
		//$dimanaPnjng2=" substring(kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
			$dimanaPnjng2=" substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
        //$dimanaPnjng3=" substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
			$dimanaPnjng3=" substring(b.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
    }
    else
    {
        //$dimanaPnjng=" substring(kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
			$dimanaPnjng=" substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
        //$dimanaPnjng2=" substring(kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
			$dimanaPnjng2=" substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
        //$dimanaPnjng3=" substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
			$dimanaPnjng3=" substring(b.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')";
    }
}

switch($proses)
{
        case'preview':
        case'excel':
        if(($tgl_1!='')&&($tgl_2!=''))
        {
                $tgl1=$tgl_1;
                $tgl2=$tgl_2;
        }

            $test = dates_inbetween($tgl1, $tgl2);
        if(($tgl2=="")&&($tgl1==""))
        {
                echo"warning: Both period required";
                exit();
        }

        $jmlHari=count($test);
        //cek max hari inputan
        if($jmlHari>40)
        {
                echo"warning: Invalid period range";
                exit();
        }
        $sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
		$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
		$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
		$jmAbsen=owlBaris($qAbsen);
        $colSpan=intval($jmAbsen)+2;
		
		
		if($proses=='preview'){
			$border="border='0'";
		}else{
			$border="border='1'";
		}
        $tab.="<table cellspacing='1' ".$border." class='sortable' width='100%'>
        <thead class=rowheader>
        <tr>
        <th align=center>No</th>
        <th align=center>".$_SESSION['lang']['subunit']."</th>
		<th align=center>".$_SESSION['lang']['nik']."</th>
		<th align=center>".$_SESSION['lang']['nama']."</th>
        
        <th align=center>".$_SESSION['lang']['jabatan']."</th>";
        
        $klmpkAbsn=array();
        foreach($test as $ar => $isi)
        {
                $qwe=date('D', strtotime($isi));
                $tab.="<th width=5px align=center>";
                if($qwe=='Sun')$tab.="<font color=red>".substr($isi,8,2)."</font>"; else $tab.=(substr($isi,8,2)); 
                $tab.="</th>";
        }
        while($rKet=$qAbsen->fetch())
        {
                $klmpkAbsn[]=$rKet;
                $tab.="<th width=10px>".$rKet['kodeabsen']."</th>";
        }
        $tab.="
        <th  align=center>".$_SESSION['lang']['total']."</th></tr></thead>
        <tbody>";

        $resData[]=array();
        $hasilAbsn[]=array();
        //get karyawan

		

                        
                        $sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik,a.notransaksi from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                            where b.notransaksi like '%PNN%' and ".$dimanaPnjng3." and b.tanggal between '".$tgl1."' and '".$tgl2."'";
                         //exit("Error".$sPrestasi);
                        $rPrestasi=fetchData($sPrestasi);
                        foreach ($rPrestasi as $presBrs =>$resPres)
                        {
                                        setIt($notran[$resPres['nik']][$resPres['tanggal']],'');
                                        
                                        if($resPres['upahkerja']>0)
                                        {
                                        $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        'absensi'=>'H');
                                        }
                                        else
                                        {
                                            $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        'absensi'=>'HK0');
                                        }
                                        $notran[$resPres['nik']][$resPres['tanggal']].='BKM:'.$resPres['notransaksi'].'__';
                                        $resData[$resPres['nik']][]=$resPres['nik'];

                        }
                        
                        $sKehadiran="select jhk,absensi,tanggal,karyawanid,notransaksi from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng2." and notransaksi not like '%BOR%'";
                          //exit("Error".$sKehadiran);
                        $rkehadiran=fetchData($sKehadiran);
                        foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
                        {	
                                if($resKhdrn['absensi']!='')
                                {
                                        setIt($notran[$resKhdrn['karyawanid']][$resKhdrn['tanggal']],'');
                                        
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
                                        $notran[$resKhdrn['karyawanid']][$resKhdrn['tanggal']].='BKM:'.$resKhdrn['notransaksi'].'__';
                                        $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                                }

                        }
                        
                        
$whrtgl=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tgl1."')";
// ambil pengawas                        
if($dakarbulanan == 0){
    $dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL ".$whrtgl."
    union select tanggal,nikmandor1,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL ".$whrtgl."";
}else{
	$dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
	where a.tanggal between '".$tgl1."' and '".$tgl2."' and nikmandor in 
	(select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' 
	and version_type='B' and namakaryawan is not NULL and (tanggalkeluar = '0000-00-00' 
	or tanggalkeluar>= '".$tgl1."') and lokasitugas='".$kdOrg."' and periodegaji ='".$periodeGaji."')";
}
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch()){
	setIt($notran[$dzbar->nikmandor][$dzbar->tanggal],'');
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $notran[$dzbar->nikmandor][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil administrasi                       
if($dakarbulanan == 0){
    $dzstr="SELECT tanggal,nikmandor1,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL ".$whrtgl."
    union select tanggal,keranimuat,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL ".$whrtgl."";
}else{
    $dzstr="SELECT tanggal,nikmandor1,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
	where a.tanggal between '".$tgl1."' and '".$tgl2."' and nikmandor1 in 
	(select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' 
	and version_type='B' and namakaryawan is not NULL and (tanggalkeluar = '0000-00-00' 
	or tanggalkeluar>= '".$tgl1."') and lokasitugas='".$kdOrg."' and periodegaji ='".$periodeGaji."')";
}



$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch()){
    $hasilAbsn[$dzbar->nikmandor1][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$dzbar->nikmandor1][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->nikmandor1][]=$dzbar->nikmandor1;
}

// ambil administrasi                       
if($dakarbulanan == 0){
    $dzstr="SELECT tanggal,keranimuat,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL ".$whrtgl."
    union select tanggal,nikasisten,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikasisten=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL ".$whrtgl."";
}else{
    $dzstr="SELECT tanggal,keranimuat,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
	where a.tanggal between '".$tgl1."' and '".$tgl2."' and keranimuat in 
	(select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' 
	and version_type='B' and namakaryawan is not NULL and (tanggalkeluar = '0000-00-00' 
	or tanggalkeluar>= '".$tgl1."') and lokasitugas='".$kdOrg."' and periodegaji ='".$periodeGaji."')";
}


$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch()){
    $hasilAbsn[$dzbar->keranimuat][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$dzbar->keranimuat][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->keranimuat][]=$dzbar->keranimuat;
}
// ambil administrasi                       
if($dakarbulanan == 0){
    $dzstr="SELECT tanggal,nikasisten,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikasisten=c.karyawanid
    where a.tipetransaksi='PNN' and a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL ".$whrtgl."
    union select tanggal,nikasisten,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikasisten=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL ".$whrtgl."";
}else{
    $dzstr="SELECT tanggal,nikasisten,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
	where a.tanggal between '".$tgl1."' and '".$tgl2."' and tipetransaksi='PNN' and nikasisten in 
	(select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' 
	and version_type='B' and namakaryawan is not NULL and (tanggalkeluar = '0000-00-00' 
	or tanggalkeluar>= '".$tgl1."') and lokasitugas='".$kdOrg."' and periodegaji ='".$periodeGaji."')";
}


$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch()){
    $hasilAbsn[$dzbar->nikasisten][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$dzbar->nikasisten][$dzbar->tanggal].='BKM:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->nikasisten][]=$dzbar->nikasisten;
}




###ambil absensi  sipil => vhc_splht

//mandor
if($dakarbulanan == 0){
    $str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan c on a.mandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL ".$whrtgl."";
}else{
    $str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and mandor in 
		(select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' 
		and version_type='B' and namakaryawan is not NULL and (tanggalkeluar = '0000-00-00' 
		or tanggalkeluar>= '".$tgl1."') and lokasitugas='".$kdOrg."' and periodegaji ='".$periodeGaji."')";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor][]=$bar->mandor;
}

//mandor1
if($dakarbulanan == 0){
    $str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan c on a.mandor1=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL ".$whrtgl."";
}else{
    $str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and mandor1 in 
		(select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' 
		and version_type='B' and namakaryawan is not NULL and (tanggalkeluar = '0000-00-00' 
		or tanggalkeluar>= '".$tgl1."') and lokasitugas='".$kdOrg."' and periodegaji ='".$periodeGaji."')";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor1][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor1][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor1][]=$bar->mandor1;
}

//krani
if($dakarbulanan == 0){
    $str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan c on a.krani=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL ".$whrtgl."";
}else{
    $str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and krani in 
		(select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' 
		and version_type='B' and namakaryawan is not NULL and (tanggalkeluar = '0000-00-00' 
		or tanggalkeluar>= '".$tgl1."') and lokasitugas='".$kdOrg."' and periodegaji ='".$periodeGaji."')";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->krani][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->krani][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->krani][]=$bar->krani;
}




#ambil dari vhc_spl_kehadiran_vw
$sSpl="select notransaksi,tanggal,nik,kodeorg from ".$dbname.".vhc_spl_kehadiran_vw where 
       tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".substr($kodeOrg,0,4)."'";
$qSpl=$owlPDO->query($sSpl) or die(print " Gagal: ".PDOException::getMessage());
$qSpl->setFetchMode(PDO::FETCH_ASSOC);
while($rSpl=$qSpl->fetch()){
    $hasilAbsn[$rSpl['nik']][$rSpl['tanggal']][]=array(
    'absensi'=>'H');
    @$notran[$rSpl['nik']][$rSpl['tanggal']].='SIPIL:'.$rSpl['notransaksi'].'__';
    $resData[$rSpl['nik']][]=$rSpl['nik'];
}


#ambil dari sdm_absensi
$sAbsn="select absensi,tanggal,karyawanid,kodeorg,catu from ".$dbname.".sdm_absensidt 
			where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng."";
		  //exit("Error".$sAbsn);
		$rAbsn=fetchData($sAbsn);
		foreach ($rAbsn as $absnBrs =>$resAbsn)
		{
			if(!is_null($resAbsn['absensi']))
			{
				setIt($notran[$resAbsn['karyawanid']][$resAbsn['tanggal']],'');
				$hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
					'absensi'=>$resAbsn['absensi']);
				$notran[$resAbsn['karyawanid']][$resAbsn['tanggal']].='ABSENSI:'.$resAbsn['kodeorg'].'__';
				$resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
				$catuBerasStat[$resAbsn['karyawanid']][$resAbsn['tanggal']]=$resAbsn['catu'];
			}
		}

$arrTotalKode=array();   
// ambil traksi                       
if($dakarbulanan == 0){
    $dzstr="SELECT a.tanggal,idkaryawan, a.notransaksi FROM ".$dbname.".vhc_runhk a
            left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".substr($kodeOrg,0,4)."%'
            and ".$where." ".$whrtgl."
        ";
}else{
    $dzstr="SELECT a.tanggal,idkaryawan, a.notransaksi FROM ".$dbname.".vhc_runhk a
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and idkaryawan in 
		(select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' 
		and version_type='B' and namakaryawan is not NULL and (tanggalkeluar = '0000-00-00' 
		or tanggalkeluar>= '".$tgl1."') and lokasitugas='".$kdOrg."' and periodegaji ='".$periodeGaji."')";
}
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
	setIt($notran[$dzbar->idkaryawan][$dzbar->tanggal],'');
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    'absensi'=>'H');    
    $notran[$dzbar->idkaryawan][$dzbar->tanggal].='TRAKSI:'.$dzbar->notransaksi.'__';
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}


foreach($hasilAbsn as $karid => $v1){
	foreach($v1 as $tgl =>$v2){
		foreach($v2 as $val){
			$hasilAbsn2[$karid][$tgl][0]['absensi']=$val['absensi'];
			// echo $val['absensi'];
		}
	}
}

// $hasilAbsn = $data;

// echo"<pre>";
// print_r($hasilAbsn);


function kirimnama($nama) // buat ngirim nama lewat javascript. spasi diganti __
{
    $qwe=explode(' ',$nama);
	$balikin="";
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
	$balikin="";
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
	if(!empty($hslAkhir[0]) and !empty($namakar[$hslAkhir[0]]))
	{
		$no+=1;
		$tab.="<tr class=rowcontent><td  align=center>".$no."</td>";
		$tab.="
		<td>".$sbgnb[$hslAkhir[0]]."</td>
		<td>".$nikkar[$hslAkhir[0]]."</td>
		<td>".$namakar[$hslAkhir[0]]."</td>
		
		<td>".$nmJabatan[$hslAkhir[0]]."</td>
		
		";
		foreach($test as $barisTgl =>$isiTgl)
		{
			setIt($hasilAbsn2[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
			setIt($notran[$hslAkhir[0]][$isiTgl],'');
			if($hasilAbsn2[$hslAkhir[0]][$isiTgl][0]['absensi']!='H')
			{
				// bgcolor=pink
                            if($hasilAbsn2[$hslAkhir[0]][$isiTgl][0]['absensi']=='HK0')
                            {
                                $tab.="<td title='Click for detail.' style=\"cursor: pointer\" onclick=showpopup('".$hslAkhir[0]."','".kirimnama($namakar[$hslAkhir[0]])."','".tanggalnormal($isiTgl)."','".removeduplicate($notran[$hslAkhir[0]][$isiTgl])."',event)>".$hasilAbsn2[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";	
                            }
                            else
                            {
                                $tab.="<td title='Click for detail.' style=\"cursor: pointer\" onclick=showpopup('".$hslAkhir[0]."','".kirimnama($namakar[$hslAkhir[0]])."','".tanggalnormal($isiTgl)."','".removeduplicate($notran[$hslAkhir[0]][$isiTgl])."',event)><font color=red>".$hasilAbsn2[$hslAkhir[0]][$isiTgl][0]['absensi']."</font></td>";
                            }
                            
                        }
			else
			{
				$bgdt="";
				setIt($catuBerasStat[$hslAkhir[0]][$isiTgl],0);
				setIt($totTgl[$isiTgl],0);
				if(@count($catuBerasStat[$hslAkhir[0]][$isiTgl])!=0){
			        if($catuBerasStat[$hslAkhir[0]][$isiTgl]==0){
				        //$bgdt="bgcolor=yellow";
						$bgdt="";
					}
			    }
				$tab.="<td ".$bgdt." title='Click for detail' style=\"cursor: pointer\" onclick=showpopup('".$hslAkhir[0]."','".kirimnama($namakar[$hslAkhir[0]])."','".tanggalnormal($isiTgl)."','".removeduplicate($notran[$hslAkhir[0]][$isiTgl])."',event)>".$hasilAbsn2[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
				$totTgl[$isiTgl]+=1;
			}
			setIt($brt[$hslAkhir[0]][$hasilAbsn2[$hslAkhir[0]][$isiTgl][0]['absensi']],0);
			$brt[$hslAkhir[0]][$hasilAbsn2[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
        }
		 
		foreach($klmpkAbsn as $brsKet =>$hslKet)
		{
			setIt($brt[$hslAkhir[0]][$hslKet['kodeabsen']],'');
			if($hslKet['kodeabsen']!='H')
			{
				$tab.="<td width=5px align=right><font color=red>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</font></td>";	
			}
			else
			{
				$tab.="<td width=5px  align=right>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</td>";	
			}
			setIt($subtot[$hslAkhir[0]]['total'],0);
			@$subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$hslKet['kodeabsen']];
            $arrTotalKode[$hslKet['kodeabsen']]+=$brt[$hslAkhir[0]][$hslKet['kodeabsen']];
		}	
		$tab.="<td width=5px align=right>".$subtot[$hslAkhir[0]]['total']."</td>";
		$subtot['total']=0;
		$tab.="</tr>";
    }	
}
        $coldt=count($klmpkAbsn);
        $itungRow=0;
        // exit("warning".print_r($klmpkAbsn));
        $tab.="<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['total']." ".$_SESSION['lang']['absensi']."</td>";
        foreach($test as $barisTgl =>$isiTgl)
        { 
			setIt($totTgl[$isiTgl],0);
            $itungRow+=1;
            $tab.= "<td>".$totTgl[$isiTgl]."</td>";
        }

        // $tab.="<td colspan=".($itungRow).">&nbsp;</td>";
     
        foreach($klmpkAbsn as $brsKet =>$hslKet)
        {
           $tab.="<td width=5px  align=right>".$arrTotalKode[$hslKet['kodeabsen']]."</td>";    
           $totalnyaaa+=$arrTotalKode[$hslKet['kodeabsen']];
        }
        $tab.="<td align=right>".$totalnyaaa."</td>";
        $tab.="</tr>";

        $tab.="</tbody></table>";

if($proses=='preview'){
	echo $tab;
}else{
	
	$nop_ = "RekapAbsen".$art."__".$kodeOrg;
	if (strlen($tab) > 0) {
		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					 @ unlink('tempExcel/'.$file);
				}
			}
			closedir($handle);
		}
		$handle = fopen("tempExcel/".$nop_.".xls", 'w');
		if (!fwrite($handle, $tab)) {
			echo "<script language=javascript1.2>
			parent.window.alert('Can't convert to excel format');
			</script>";
			exit;
		} else {
			echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls';
			</script>";
		}
		fclose($handle);
	}
}
						
break;
        case'pdf':


        $test = dates_inbetween($tgl1, $tgl2);

        if(($tgl2=="")&&($tgl1==""))
        {
                echo"warning: Both period required";
                exit();
        }

        $jmlHari=count($test);
        //cek max hari inputan
        if($jmlHari>40)
        {
                echo"warning:Invalid period range ".$jmlHari;
                exit();
        }
        //ambil query untuk tanggal kehadiran

        $sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
        $qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
		$jmAbsen=owlBaris($qAbsen);
		$colSpan=intval($jmAbsen)+2;

        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
//create Header

class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $period;
                                global $periode;
                                global $kdOrg;
                                global $kdeOrg;
                                global $tgl1;
                                global $tgl2;
                                global $where;
                                global $jmlHari;
                                global $test;
                                global $klmpkAbsn;
                                global $tipeKary;
                                global $sistemGaji;
                                global $dimanaPnjng;
                                global $afdId;
                                global $dimanaPnjng2;
                                global $dimanaPnjng3;
                                global $owlPDO;


                                $jmlHari=$jmlHari*1.5;
                                $cols=247.5;
                            # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 20;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();

                $this->SetFont('Arial','B',10);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['rkpAbsen']." ".$sistemGaji,'',0,'L');
                                $this->Ln();
                                $this->Ln();

                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['rkpAbsen']),'',0,'C');
                                $this->Ln();
                                $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2),'',0,'C');
                                $this->Ln();
                                $this->Ln();
                $this->SetFont('Arial','B',7);
                $this->SetFillColor(220,220,220);
                                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['nama'],1,0,'C',1);		
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['jabatan'],1,0,'C',1);	

                                //$this->Cell($jmlHari/100*$width,$height-10,$_SESSION['lang']['tanggal'],1,0,'C',1);
                                //$this->GetX();
                                //$this->SetY($this->GetY());
                                //
                                //$this->SetX($this->GetX()+$cols);

                                foreach($test as $ar => $isi)
                                {
                                    $this->Cell(1.5/100*$width,$height,substr($isi,8,2),1,0,'C',1);	
                                    $akhirX=$this->GetX();
                                }	
                                $this->SetY($this->GetY());
                                $this->SetX($akhirX);
                                $sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
								$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
								$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
                                while($rAbsen=$qAbsen->fetch())
                                {
                                        $klmpkAbsn[]=$rAbsen;
                                        $this->Cell(2/100*$width,$height,$rAbsen['kodeabsen'],1,0,'C',1);
                                }
                                $this->Cell(5/100*$width,$height,$_SESSION['lang']['total'],1,1,'C',1);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','Legal');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',7);
                $subtot=array();
                //ambil query untuk data karyawan
        if($kdOrg!='')
        {
                $kodeOrg=$kdOrg;
                if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                {
                        $where="  lokasitugas in ('".$kodeOrg."')";
                        if($afdId!='')
                        {			
                            $where="  subbagian='".$afdId."'";		
                        }

                }
                else
                {
                        if(strlen($kdOrg)>4)
                        {			
                                $where="  subbagian='".$kdOrg."'";		
                        }
                        else
                        {
                                $where="  lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
                        }
                }
        }
        else
        {
                $kodeOrg=$_SESSION['empl']['lokasitugas'];
                $where="  lokasitugas='".$kodeOrg."'";
        }
        if($tipeKary!='')
        {
            $where.=" and tipekaryawan='".$tipeKary."'";
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        

        if($dakarbulanan == 0){
            $sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a 
                       left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where
                       ".$where." ".$wherez." and tanggalkeluar='0000-00-00' order by namakaryawan asc";
        }else{
            $sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan_hist a 
                       left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where
                       ".$where." ".$wherez." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and tanggalkeluar='0000-00-00' order by namakaryawan asc";
        }
        //exit("Error:".$sGetKary);
        $rGetkary=fetchData($sGetKary);
        $namakar=Array();
        $nmJabatan=Array();
        foreach($rGetkary as $row => $kar)
        {
           // $resData[$kar['karyawanid']][]=$kar['karyawanid'];
            $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
            $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
        }
        
		$sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
			where b.notransaksi like '%PNN%' and ".$dimanaPnjng3." and b.tanggal between '".$tgl1."' and '".$tgl2."'";
		//exit("Error".$sPrestasi);
		$rPrestasi=fetchData($sPrestasi);
		foreach ($rPrestasi as $presBrs =>$resPres)
		{
			if($resPres['upahkerja']>0)
			{
						$hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
						'absensi'=>'H');
			}
			else
			{
				$hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
						'absensi'=>'HK0');
			}
						$resData[$resPres['nik']][]=$resPres['nik'];

		} 
		
		$sKehadiran="select jhk,absensi,tanggal,karyawanid from ".$dbname.".kebun_kehadiran_vw 
			where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng2."";
		//exit("Error".$sKehadiran);
		$rkehadiran=fetchData($sKehadiran);
		foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
		{	
				if($resKhdrn['absensi']!='')
				{
					
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
if($dakarbulanan == 0){
    $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
        union select tanggal,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}else{
    $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan_hist c on a.nikmandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
        union select tanggal,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan_hist c on a.nikmandor1=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}
//exit("Error".$dzstr);
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil administrasi                       
if($dakarbulanan == 0){
    $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
        union select tanggal,keranimuat FROM ".$dbname.".kebun_aktifitas a 
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}else{
    $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan_hist c on a.nikmandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
        union select tanggal,keranimuat FROM ".$dbname.".kebun_aktifitas a 
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan_hist c on a.keranimuat=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}
 //exit("Error".$dzstr);
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}


//nikasisten
if($dakarbulanan == 0){
    $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
        union select tanggal,nikasisten FROM ".$dbname.".kebun_aktifitas a 
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan c on a.nikasisten=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}else{
    $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan_hist c on a.nikmandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
        union select tanggal,nikasisten FROM ".$dbname.".kebun_aktifitas a 
        left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
        left join ".$dbname.".datakaryawan_hist c on a.nikasisten=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}
	
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}


// ambil traksi                       
if($dakarbulanan == 0){
    $dzstr="SELECT a.tanggal,idkaryawan FROM ".$dbname.".vhc_runhk a
            left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".$kodeOrg."%'
            and ".$where." and tanggalkeluar='0000-00-00'
        ";
}else{
    $dzstr="SELECT a.tanggal,idkaryawan FROM ".$dbname.".vhc_runhk a
            left join ".$dbname.".datakaryawan_hist b on a.idkaryawan=b.karyawanid
            where a.tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".$kodeOrg."%'
            and ".$where." and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and tanggalkeluar='0000-00-00'
        ";
}
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}




###ambil absensi  sipil => vhc_splht

//mandor
if($dakarbulanan ==0){
    $str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan c on a.mandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}else{
    $str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan_hist c on a.mandor=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor][]=$bar->mandor;
}

//mandor1
if($dakarbulanan ==0){
    $str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan c on a.mandor1=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}else{
    $str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan_hist c on a.mandor1=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor1][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor1][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor1][]=$bar->mandor1;
}

//krani
if($dakarbulanan ==0){
    $str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan c on a.krani=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}else{
    $str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
        left join ".$dbname.".datakaryawan_hist c on a.krani=c.karyawanid
        where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and approval_status='8' and version_type='B' and periodegaji='".$periodeGaji."' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->krani][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->krani][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->krani][]=$bar->krani;
}

#ambil dari vhc_spl_kehadiran_vw
$sSpl="select notransaksi,tanggal,nik,kodeorg from ".$dbname.".vhc_spl_kehadiran_vw where 
       tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".substr($kodeOrg,0,4)."'";
$qSpl=$owlPDO->query($sSpl) or die(print " Gagal: ".PDOException::getMessage());
$qSpl->setFetchMode(PDO::FETCH_ASSOC);
while($rSpl=$qSpl->fetch()){
    $hasilAbsn[$rSpl['nik']][$rSpl['tanggal']][]=array(
    'absensi'=>'H');
    @$notran[$rSpl['nik']][$rSpl['tanggal']].='SIPIL:'.$rSpl['notransaksi'].'__';
    $resData[$rSpl['nik']][]=$rSpl['nik'];
}






$sAbsn="select absensi,tanggal,karyawanid from ".$dbname.".sdm_absensidt 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng."";
                        //exit("Error".$sAbsn);
                        $rAbsn=fetchData($sAbsn);
                        foreach ($rAbsn as $absnBrs =>$resAbsn)
                        {
                                if(!is_null($resAbsn['absensi']))
                                {
                                        $hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
                'absensi'=>$resAbsn['absensi']);
                                $resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
                                }

                        }


        $brt=array();
        $lmit=count($klmpkAbsn);
        $a=0;
        foreach($resData as $hslBrs => $hslAkhir)
        {	
                        if(!empty($hslAkhir[0])  and !empty($namakar[$hslAkhir[0]]))
                        {
                                $no+=1;
                                $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                                $pdf->Cell(10/100*$width,$height,strtoupper($namakar[$hslAkhir[0]]),1,0,'L',1);		
                                $pdf->Cell(10/100*$width,$height,strtoupper($nmJabatan[$hslAkhir[0]]),1,0,'L',1);	
                                foreach($test as $barisTgl =>$isiTgl)
                                {
                                    setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
                                    $pdf->Cell(1.5/100*$width,$height,$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],1,0,'C',1);	
                                    $akhirX=$pdf->GetX();
                                    setIt($brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']],0);
                                    $brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
                                }
                                $a=0;
                                for(;$a<$lmit;$a++)
                                {
                                    setIt($brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']],0);
                                    $pdf->Cell(2/100*$width,$height,$brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']],1,0,'C',1);
                                    setIt($subtot[$hslAkhir[0]]['total'],0);
                                    $subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']];
                                }	
                                $pdf->Cell(5/100*$width,$height,$subtot[$hslAkhir[0]]['total'],1,1,'R',1);
                                $subtot[$hslAkhir[0]]['total']=0;
                        }	

        }


        $pdf->Output();

        break;
        // case'excel':

        // $test = dates_inbetween($tgl1, $tgl2);
        // if(($tgl2=="")&&($tgl1==""))
        // {
                // echo"warning: Both period required";
                // exit();
        // }

        // $jmlHari=count($test);
        // //cek max hari inputan
        // if($jmlHari>40)
        // {
                // echo"warning: Invalid period range";
                // exit();
        // }
        // $sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
        // $qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
		// $qAbsen->setFetchMode(PDO::FETCH_ASSOC);
		// $jmAbsen=owlBaris($qAbsen);
		// $colSpan=intval($jmAbsen)+2;
        // $colatas=$jmlHari+$colSpan+3;
        // $stream.="<table border='0'><tr><td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['rkpAbsen'])." ".$sistemGaji."</td></tr>
        // <tr><td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2)."</td></tr><tr><td colspan='".$colatas."'>&nbsp;</td></tr></table>";
        // $stream.="<table cellspacing='1' border='1' class='sortable'>
        // <thead class=rowheader>
        // <tr>
        // <td bgcolor=#DEDEDE align=center>No</td>
        // <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nama']."</td>
        // <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nik']."</td>
        // <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jabatan']."</td>
        // <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['bagian']."</td>
         // <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['subunit']."</td>";
        // $klmpkAbsn=array();
        // foreach($test as $ar => $isi)
        // {
                // //exit("Error".$isi);
                // $qwe=date('D', strtotime($isi));

                // if($qwe=='Sun')
                // {
                    // $stream.="<td bgcolor=red align=center width=5px align=center><font color=white>".substr($isi,8,2)."</font></td>";
                // }
                // else
                // {
                    // $stream.="<td bgcolor=#DEDEDE align=center width=5px align=center>".substr($isi,8,2)."</td>";
                // }

        // }
        // while($rKet=$qAbsen->fetch())
        // {
                // $klmpkAbsn[]=$rKet;
                // $stream.="<td bgcolor=#DEDEDE align=center width=10px>".$rKet['kodeabsen']."</td>";
        // }
        // $stream.="
        // <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['total']."</td></tr></thead>
        // <tbody>";
        // //ambil query untuk data karyawan
        // if($kdOrg!='')
        // {
                // $kodeOrg=$kdOrg;
                // if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
                // {
                        // $where="  lokasitugas in ('".$kodeOrg."')";
                        // if($afdId!='')
                        // {			
                            // $where="  subbagian='".$afdId."'";		
                        // }

                // }
                // else
                // {
                        // if(strlen($kdOrg)>4)
                        // {			
                                // $where="  subbagian='".$kdOrg."'";		
                        // }
                        // else
                        // {
                                // $where="  lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
                        // }
                // }
        // }
        // else
        // {
                // $kodeOrg=$_SESSION['empl']['lokasitugas'];
                // $where="  lokasitugas='".$kodeOrg."'";
        // }
        // if($tipeKary!='')
        // {
            // $where.=" and tipekaryawan='".$tipeKary."'";
        // }
        // if($sistemGaji=='All')$wherez="";        
        // if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        // if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        
        // $resData[]=array();
        // $hasilAbsn[]=array();
        // $sGetKary="select a.karyawanid,a.nik,b.namajabatan,a.namakaryawan,c.nama,subbagian from ".$dbname.".datakaryawan a 
           // left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan 
           // left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode
           // where
           // ".$where." ".$wherez."order by namakaryawan asc";  
         // $namakar=Array();
        // $nmJabatan=Array();
        // $rGetkary=fetchData($sGetKary);
        // foreach($rGetkary as $row => $kar)
    // {

          // $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
          // $nikkar[$kar['karyawanid']]=$kar['nik'];
          // $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
          // $nmBagian[$kar['karyawanid']]=$kar['nama'];
           // $sbgnb[$kar['karyawanid']]=$kar['subbagian'];
    // }  
                

                        
                        // $sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                            // where b.notransaksi like '%PNN%' and ".$dimanaPnjng3." and b.tanggal between '".$tgl1."' and '".$tgl2."'";
                        // //exit("Error".$sPrestasi);
                        // $rPrestasi=fetchData($sPrestasi);
                        // foreach ($rPrestasi as $presBrs =>$resPres)
                        // {
                            // if($resPres['upahkerja']>0)
                            // {
                                // $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        // 'absensi'=>'H');
                            // }
                            // else
                            // {
                                // $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                        // 'absensi'=>'HK0');
                            // }
                                        
                            // $resData[$resPres['nik']][]=$resPres['nik'];

                        // } 
                        
                        // $sKehadiran="select jhk,absensi,tanggal,karyawanid from ".$dbname.".kebun_kehadiran_vw 
                            // where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng2."";
                        // //exit("Error".$sKehadiran);
                        // $rkehadiran=fetchData($sKehadiran);
                        // foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
                        // {	
                            
                                // if($resKhdrn['absensi']!='')
                                // {
                                    // if($resKhdrn['jhk']>0)
                                    // {
                                        // $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                          // 'absensi'=>$resKhdrn['absensi']);
                                    // }
                                    // else
                                    // {
                                        // $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                          // 'absensi'=>'HK0');
                                    // }
                                        
                                        // $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                                // }

                        // }
                        

// // ambil pengawas                        
// $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    // left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    // left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    // union select tanggal,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    // left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    // left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
// $dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
// $dzres->setFetchMode(PDO::FETCH_OBJ);
// while($dzbar=$dzres->fetch())
// {
    // $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    // 'absensi'=>'H');
    // $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
// }

// // ambil administrasi                       
// $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    // left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    // left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    // union select tanggal,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    // left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    // left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
// $dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
// $dzres->setFetchMode(PDO::FETCH_OBJ);
// while($dzbar=$dzres->fetch())
// {
    // $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    // 'absensi'=>'H');
    // $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
// }

// //nikasisten
// $dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    // left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    // left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
    // union select tanggal,nikasisten FROM ".$dbname.".kebun_aktifitas a 
    // left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    // left join ".$dbname.".datakaryawan c on a.nikasisten=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
// $dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
// $dzres->setFetchMode(PDO::FETCH_OBJ);
// while($dzbar=$dzres->fetch())
// {
    // $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    // 'absensi'=>'H');
    // $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
// }

// // ambil traksi                       
// $dzstr="SELECT a.tanggal,idkaryawan FROM ".$dbname.".vhc_runhk a
        // left join ".$dbname.".datakaryawan b on a.idkaryawan=b.karyawanid
        // where a.tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".$kodeOrg."%'
        // and ".$where."
    // ";
// $dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
// $dzres->setFetchMode(PDO::FETCH_OBJ);
// while($dzbar=$dzres->fetch())
// {
    // $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    // 'absensi'=>'H');
    // $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
// }        







// ###ambil absensi  sipil => vhc_splht

// //mandor
// $str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
    // left join ".$dbname.".datakaryawan c on a.mandor=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while($bar=$res->fetch())
// {
    // $hasilAbsn[$bar->mandor][$bar->tanggal][]=array(
    // 'absensi'=>'H');
    // @$notran[$bar->mandor][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    // $resData[$bar->mandor][]=$bar->mandor;
// }

// //mandor1
// $str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
    // left join ".$dbname.".datakaryawan c on a.mandor1=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while($bar=$res->fetch())
// {
    // $hasilAbsn[$bar->mandor1][$bar->tanggal][]=array(
    // 'absensi'=>'H');
    // @$notran[$bar->mandor1][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    // $resData[$bar->mandor1][]=$bar->mandor1;
// }

// //krani
// $str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
    // left join ".$dbname.".datakaryawan c on a.krani=c.karyawanid
    // where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while($bar=$res->fetch())
// {
    // $hasilAbsn[$bar->krani][$bar->tanggal][]=array(
    // 'absensi'=>'H');
    // @$notran[$bar->krani][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    // $resData[$bar->krani][]=$bar->krani;
// }

// #ambil dari vhc_spl_kehadiran_vw
// $sSpl="select notransaksi,tanggal,nik,kodeorg from ".$dbname.".vhc_spl_kehadiran_vw where 
       // tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".substr($kodeOrg,0,4)."'";
// $qSpl=$owlPDO->query($sSpl) or die(print " Gagal: ".PDOException::getMessage());
// $qSpl->setFetchMode(PDO::FETCH_ASSOC);
// while($rSpl=$qSpl->fetch()){
    // $hasilAbsn[$rSpl['nik']][$rSpl['tanggal']][]=array(
    // 'absensi'=>'H');
    // @$notran[$rSpl['nik']][$rSpl['tanggal']].='SIPIL:'.$rSpl['notransaksi'].'__';
    // $resData[$rSpl['nik']][]=$rSpl['nik'];
// }






// $sAbsn="select absensi,tanggal,karyawanid,catu from ".$dbname.".sdm_absensidt 
	// where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng."";
// //exit("Error".$sAbsn);
// $rAbsn=fetchData($sAbsn);
// foreach ($rAbsn as $absnBrs =>$resAbsn)
// {
		// if(!is_null($resAbsn['absensi']))
		// {
				// $hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
// 'absensi'=>$resAbsn['absensi']);
		// $resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
// $catuBerasStat[$resAbsn['karyawanid']][$resAbsn['tanggal']]=$resAbsn['catu'];
		// }

// }


        // $brt=array();
        // $lmit=count($klmpkAbsn);
        // $a=0;
        // foreach($resData as $hslBrs => $hslAkhir)
        // {	
                        // if(!empty($hslAkhir[0]) and !empty($namakar[$hslAkhir[0]]))
                        // {
                                // $no+=1;
                                // $stream.="<tr><td>".$no."</td>";
                                // $stream.="
                                // <td>".$namakar[$hslAkhir[0]]."</td>
                                // <td>'".$nikkar[$hslAkhir[0]]."</td>
                                // <td>".$nmJabatan[$hslAkhir[0]]."</td>
                                // <td>".$nmBagian[$hslAkhir[0]]."</td>
                                // <td>".$sbgnb[$hslAkhir[0]]."</td>
                                // ";
                                // foreach($test as $barisTgl =>$isiTgl)
                                // {
									// setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
                                    // if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']!='H')
                                    // {
                                        // setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],'');
                                        // if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']=='HK0')
                                        // {
                                            // $stream.="<td>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
											// //$stream.="<td bgcolor=pink>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
                                        // }
                                        // else
                                        // {
                                            // $stream.="<td><font color=red>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</font></td>";
                                        // }
                                        
                                        
                                    // }
                                    // else
                                    // {
				  // $bgdt="";
				  // setIt($catuBerasStat[$hslAkhir[0]][$isiTgl],0);
				  // if(count($catuBerasStat[$hslAkhir[0]][$isiTgl])!=0){
			            // if($catuBerasStat[$hslAkhir[0]][$isiTgl]==0){
				          // $bgdt="";
						   // //$bgdt="bgcolor=yellow";
					// }
			          // }
					  // setIt($totTgl[$isiTgl],0);
                                        // $stream.="<td ".$bgdt.">".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
                                        // $totTgl[$isiTgl]+=1;
                                    // }
									// setIt($brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']],0);
                                    // $brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
                                // }

                                // foreach($klmpkAbsn as $brsKet =>$hslKet)
                                // {
									// setIt($brt[$hslAkhir[0]][$hslKet['kodeabsen']],'');
                                    // if($hslKet['kodeabsen']!='H')
                                    // {
                                        // $stream.="<td width=5px  align=right><font color=red>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</font></td>";	
                                    // }
                                    // else
                                    // {
                                        // $stream.="<td width=5px  align=right>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</td>";	
                                    // }
									// setIt($subtot[$hslAkhir[0]]['total'],0);
                                    // $subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$hslKet['kodeabsen']];
                                // }	
                                // $stream.="<td width=5px  align=right>".$subtot[$hslAkhir[0]]['total']."</td>";
                                // $subtot['total']=0;
                                // $stream.="</tr>";
                        // }	
        // }
         // $coldt=count($klmpkAbsn);
        // $stream.="<tr class=rowcontent><td colspan=6>".$_SESSION['lang']['total']."</td>";
        // foreach($test as $barisTgl =>$isiTgl)
        // {
			// setIt($totTgl[$isiTgl],0);
            // $stream.= "<td>".$totTgl[$isiTgl]."</td>";
        // }
        // $stream.="<td colspan=".($coldt+1).">&nbsp;</td></tr>";
        // $stream.="</tbody></table>";




                        // //echo "warning:".$strx;
                        // //=================================================


                        // $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
                        // if(!empty($period))
                        // {
                                // $art=$period;
                                // $art=$art[1].$art[0];
                        // }
                        // if($periode!='')
                        // {
                                // $art=$periode;
                                // $art=$art[1].$art[0];
                        // }
                        // if($kdeOrg!='')
                        // {
                                // $kodeOrg=$kdeOrg;
                        // }
                        // if($kdOrg!='')
                        // {
                                // $kodeOrg=$kdOrg;
                        // }
                        // $nop_="RekapAbsen".$art."__".$kodeOrg;
                        // if(strlen($stream)>0)
                        // {
                        // if ($handle = opendir('tempExcel')) {
                        // while (false !== ($file = readdir($handle))) {
                        // if ($file != "." && $file != ".." && $file != "index.html") {
                        // @unlink('tempExcel/'.$file);
                        // }
                        // }	
                        // closedir($handle);
                        // }
                        // $handle=fopen("tempExcel/".$nop_.".xls",'w');
                        // if(!fwrite($handle,$stream))
                        // {
                        // echo "<script language=javascript1.2>
                        // parent.window.alert('Can't convert to excel format');
                        // </script>";
                        // exit;
                        // }
                        // else
                        // {
                        // echo "<script language=javascript1.2>
                        // window.location='tempExcel/".$nop_.".xls';
                        // </script>";
                        // }
                        // fclose($handle);
                        // }
        // break;
        case'getTgl':
			
        if($periode!='')
        {
                $tgl=$periode;
                $tanggal=$tgl[0]."-".$tgl[1];
                $dmna.=" and periode='".$tanggal."'";
        }
        elseif($period!='')
        {
                $tgl=$period;
                $tanggal=$tgl[0]."-".$tgl[1];
                $dmna.=" and periode='".$tanggal."'";
        }
        if($sistemGaji!='')
        {
                $dmna.=" and jenisgaji='".substr($sistemGaji,0,1)."'";
        }
        if($kdUnit=='')
        {
            $kdUnit=$_SESSION['empl']['lokasitugas'];
        }
        $sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($kdUnit,0,4)."' ".$dmna." ";
        $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
		$qTgl->setFetchMode(PDO::FETCH_ASSOC);
        $rTgl=$qTgl->fetch();
        echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
        break;
        case'getKry':
        $optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if(strlen($kdeOrg)>4)
        {
			
                $where=" lokasitugas='".substr($kdeOrg,0,4)."' and subbagian='".$kdeOrg."' ";
        }
        else
        {
                $where=" lokasitugas='".$kdeOrg."' and subbagian like '".$kdeOrg."%'";
                //and (subbagian='0' or subbagian is null or subbagian='')
        }
        $sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where ".$where." order by namakaryawan asc";
		// exit('warning : '.$sKry);
        $qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
		$qKry->setFetchMode(PDO::FETCH_ASSOC);
        while($rKry=$qKry->fetch())
        {
                $optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']."</option>";
        }
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdeOrg."'";
        $qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
        while($rPeriode=$qPeriode->fetch())
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        //echo $optPeriode;
        echo $optKry."###".$optPeriode;
        break;
        case'getPeriode':
        if($periodeGaji!='')
        {
                $were=" kodeorg='".$kdUnit."' and periode='".$periodeGaji."' and jenisgaji='".$sistemGaji."'";
        }
        else
        {
                $were=" kodeorg='".$kdUnit."'";
        }
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where ".$were." order by periode desc";
        $qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
        while($rPeriode=$qPeriode->fetch())
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sSub="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kdUnit."' and tipe in ('AFDELING','TRAKSI','GUDANG','WORKSHOP','STATION') order by namaorganisasi asc";
		$qSub=$owlPDO->query($sSub) or die(print " Gagal: ".PDOException::getMessage());
		$qSub->setFetchMode(PDO::FETCH_ASSOC);
        while($rSub=$qSub->fetch())
        {
             $optAfd.="<option value='".$rSub['kodeorganisasi']."'>".$rSub['namaorganisasi']."</option>";
        }
        echo $optAfd."####".$optPeriode;
        break;
        case'getPeriodeGaji5':
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optPeriode2=$optPeriode;
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$_POST['kdUnit']."' order by periode desc";
		$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
        while($rPeriode=$qPeriode->fetch())
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        $sPeriode2="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$_POST['kdUnit']."' order by periode desc";
		$qPeriode2=$owlPDO->query($sPeriode2) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode2->setFetchMode(PDO::FETCH_ASSOC);
        while($rPeriode2=$qPeriode2->fetch())
        {
                $optPeriode2.="<option value=".$rPeriode2['periode'].">".substr(tanggalnormal($rPeriode2['periode']),1,7)."</option>";
        }
        echo $optPeriode2."####".$optPeriode;
        break;
        default:
        break;
}
?>
