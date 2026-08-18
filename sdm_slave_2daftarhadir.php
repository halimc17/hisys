<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
//$arrThn="##kdeOrg2##periodThn##periodThnSmp##sistemGaji3";

$proses=checkPostGet("proses","");
$kdOrg=checkPostGet("kdeOrg2","");
$tgl1=checkPostGet("periodThn","");
$tgl2=checkPostGet("periodThnSmp","");
$tipeKary=checkPostGet("tipeKary2","");
$sistemGaji=checkPostGet("sistemGaji3","");
$nilaiMax=checkPostGet("nilaiMax","");
$optTmk=makeOption($dbname, 'datakaryawan', 'karyawanid,tanggalmasuk');

if($kdOrg==''){
    exit("error: Working unit required");
}
if(($tgl1=='')||($tgl2=='')){
    exit("error: Both period required");
}
if($sistemGaji==''){
    exit("error: Payment system required");
}
if($nilaiMax==''){
    exit("error: Minimum presence required, type 0 for all");
}
$optDept=makeOption($dbname, 'sdm_5departemen', 'kode,nama');
$thn=explode("-",$tgl1);
$thn2=explode("-",$tgl2);

$blndt1=intval($thn[1]);
$blndt12=intval($thn2[1]);
$bulan=array();
if($tgl2<$tgl1){
    exit("error: First period must lower");
}
if($thn[0]!=$thn2[0]){
    for($mule=$blndt1;$mule<13;$mule++){

            $bulan[]=$mule;
    }
    for($mule=1;$mule<=$blndt12;$mule++){
            $bulan[]=$mule;
    }
}
$cerk=count($bulan);
$where="";
$whrd="";
$whrc="";
if($cerk>12){
    exit("error: Query maximum 12 months, your query is".$cerk." moths");
}
        //ambil query untuk data karyawan
        $where="  lokasitugas='".$kdOrg."'";

        if($tipeKary!='')
        {
            $where.=" and tipekaryawan='".$tipeKary."'";
            $whrd="and b.tipekaryawan='".$tipeKary."'";
            $whrc="and c.tipekaryawan='".$tipeKary."'";
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        
//cek datakaryawan history
$dakarbulanan=0;
$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$kdOrg."' and periodegaji >= '".$tgl1."' and periodegaji <= '".$tgl2."' ";
$res = fetchdata($str);
if(count($res)>0)
{ 
    $dakarbulanan=1;
}
if($dakarbulanan == 0){
    $sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan,c.nama,d.tipe from ".$dbname.".datakaryawan a 
            left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
            left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode
            left join ".$dbname.".sdm_5tipekaryawan d on a.tipekaryawan=d.id
            where tanggalkeluar='0000-00-00'
            order by namakaryawan asc";  
}else{
    $sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan,c.nama,d.tipe from ".$dbname.".datakaryawan_hist a 
            left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
            left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode
            left join ".$dbname.".sdm_5tipekaryawan d on a.tipekaryawan=d.id
            where periodegaji >= '".$tgl1."' and periodegaji <= '".$tgl2."' and tanggalkeluar='0000-00-00'
            order by namakaryawan asc";  
}
// echo $sGetKary; exit;
$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{
   // $resData[$kar['karyawanid']][]=$kar['karyawanid'];
   $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
    $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
    $nmBagian[$kar['karyawanid']]=$kar['nama'];
    $nmTipe[$kar['karyawanid']]=$kar['tipe'];
}  
$bln1=explode("-",$tgl1);
$bln2=explode("-",$tgl2);

        $resData[]=array();
        $hasilAbsn[]=array();
        //get karyawan


    $dimanaPnjng=" kodeorg like '".$kdOrg."%'";

                        if($dakarbulanan == 0){
                            $sAbsn="select count(absensi) as total,absensi,a.karyawanid,left(tanggal,7) as periode from ".$dbname.".sdm_absensidt a
                            left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid  where substr(tanggal,1,7) between  '".$tgl1."' and '".$tgl2."' 
                            and ".$dimanaPnjng."  and a.karyawanid!='' ".$whrd." and tanggalkeluar='0000-00-00'
                            group by absensi,karyawanid,left(tanggal,7)";
                        }else{
                            $sAbsn="select count(absensi) as total,absensi,a.karyawanid,left(tanggal,7) as periode from ".$dbname.".sdm_absensidt a
                            left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid  where substr(tanggal,1,7) between  '".$tgl1."' and '".$tgl2."' 
                            and ".$dimanaPnjng."  and a.karyawanid!='' ".$whrd." and tanggalkeluar='0000-00-00'
                            group by absensi,karyawanid,left(tanggal,7)";
                        }
                        //exit("Error".$sAbsn);
                        $rAbsn=fetchData($sAbsn);
                        foreach ($rAbsn as $absnBrs =>$resAbsn)
                        {
                                if(!is_null($resAbsn['absensi']))
                                {
                                    $hasilAbsn[$resAbsn['karyawanid']][$resAbsn['periode']][$resAbsn['absensi']]=$resAbsn['total'];
                                    $resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
                                    $dtPeriode[$resAbsn['periode']]=$resAbsn['periode'];
                                    $klmpkAbsn[$resAbsn['absensi']]=$resAbsn['absensi'];
                                }
                        }
                        if($dakarbulanan == 0){
                            $sKehadiran="select count(absensi) as total,absensi,a.karyawanid,left(tanggal,7) as periode from ".$dbname.".kebun_kehadiran_vw a
                            left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                            where substr(tanggal,1,7) between  '".$tgl1."' and '".$tgl2."' and substring(unit,1,4)='".$kdOrg."'  ".$whrd." and tanggalkeluar='0000-00-00'
                            group by absensi,karyawanid,left(tanggal,7)";
                        }else{
                            $sKehadiran="select count(absensi) as total,absensi,a.karyawanid,left(tanggal,7) as periode from ".$dbname.".kebun_kehadiran_vw a
                            left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid
                            where substr(tanggal,1,7) between  '".$tgl1."' and '".$tgl2."' and substring(unit,1,4)='".$kdOrg."'  ".$whrd." and tanggalkeluar='0000-00-00'
                            group by absensi,karyawanid,left(tanggal,7)";
                        }
                        //exit("Error".$sKehadiran);
                        $rkehadiran=fetchData($sKehadiran);
                        foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
                        {	
                                if($resKhdrn['absensi']!='')
                                {
                                    @$hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['periode']][$resKhdrn['absensi']]+=$resKhdrn['total'];
                                    $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
                                    $dtPeriode[$resKhdrn['periode']]=$resKhdrn['periode'];
                                    $klmpkAbsn[$resAbsn['absensi']]=$resAbsn['absensi'];
                                }

                        }
                        if($dakarbulanan == 0){
                            $sPrestasi="select left(c.tanggal,7) as periode,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a 
                            left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi 
                            left join ".$dbname.".datakaryawan b on a.nik=b.karyawanid
                            where c.notransaksi like '%PNN%' and a.nik!=''   ".$whrd."
                            and substr(c.kodeorg,1,4)='".$kdOrg."' and substr(c.tanggal,1,7) between '".$tgl1."' and '".$tgl2."' and tanggalkeluar='0000-00-00'";
                        }else{
                            $sPrestasi="select left(c.tanggal,7) as periode,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a 
                            left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi 
                            left join ".$dbname.".datakaryawan_hist b on a.nik=b.karyawanid
                            where c.notransaksi like '%PNN%' and a.nik!=''   ".$whrd."
                            and substr(c.kodeorg,1,4)='".$kdOrg."' and substr(c.tanggal,1,7) between '".$tgl1."' and '".$tgl2."' and tanggalkeluar='0000-00-00'";
                        }
                       // exit("Error".$sPrestasi);
                        $rPrestasi=fetchData($sPrestasi);
                        foreach ($rPrestasi as $presBrs =>$resPres)
                        {
                            $resPres['absensi']='H';
                            @$hasilAbsn[$resPres['nik']][$resPres['periode']]['H']+=1;
                            $resData[$resPres['nik']][]=$resPres['nik'];
                            $dtPeriode[$resPres['periode']]=$resPres['periode'];
                            //$klmpkAbsn[$resPres['absensi']]=$resPres['absensi'];
                        }

// ambil pengawas   
if($dakarbulanan == 0){
    $dzstr="SELECT left(a.tanggal,7) as periode,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and b.kodeorg like '".$kdOrg."%' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
    union select left(a.tanggal,7) as periode,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') 
    and c.karyawanid!='' and b.kodeorg like '".$kdOrg."%' ".$whrc." and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}else{
    $dzstr="SELECT left(a.tanggal,7) as periode,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan_hist c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and b.kodeorg like '".$kdOrg."%' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
    union select left(a.tanggal,7) as periode,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan_hist c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') 
    and c.karyawanid!='' and b.kodeorg like '".$kdOrg."%' ".$whrc." and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}                     
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $dzbar->absensi='H';
    $hasilAbsn[$dzbar->nikmandor][$dzbar->periode]['H']+=1;
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
    $dtPeriode[$dzbar->periode]=$dzbar->periode;
    //$klmpkAbsn[$dzbar->absensi]=$dzbar->absensi;

}

// ambil administrasi                       
if($dakarbulanan == 0){
    $dzstr="SELECT left(a.tanggal,7) as periode,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and b.kodeorg like '".$kdOrg."%' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
    union select left(a.tanggal,7) as periode,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and c.karyawanid!='' and b.kodeorg like '".$kdOrg."%'  ".$whrc." 
    and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}else{
    $dzstr="SELECT left(a.tanggal,7) as periode,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan_hist c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and b.kodeorg like '".$kdOrg."%' and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'
    union select left(a.tanggal,7) as periode,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan_hist c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and c.karyawanid!='' and b.kodeorg like '".$kdOrg."%'  ".$whrc." 
    and c.namakaryawan is not NULL and tanggalkeluar='0000-00-00'";
}
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $dzbar->absensi='H';
    $hasilAbsn[$dzbar->nikmandor][$dzbar->periode]['H']+=1;
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
    $dtPeriode[$dzbar->periode]=$dzbar->periode;
    //$klmpkAbsn[$dzbar->absensi]=$dzbar->absensi;

}       
array_multisort($dtPeriode); 
$bgc="";
$brd="0";
if($proses=='excel'){
    $bgc=" bgcolor=#DEDEDE align=center";
    $brd="1";
}
 $tab.="<table cellspacing='1' border='".$brd."' class='sortable'>
        <thead class=rowheader>
        <tr ".$bgc.">
        <td rowspan=2>No</td>
        <td rowspan=2>".$_SESSION['lang']['nama']."</td>
        <td rowspan=2>".$_SESSION['lang']['tipekaryawan']."</td>
        <td rowspan=2>".$_SESSION['lang']['bagian']."</td>
        <td rowspan=2>".$_SESSION['lang']['jabatan']."</td>
        <td rowspan=2>".$_SESSION['lang']['tmk']."</td>";

        foreach($dtPeriode as $dtprd){
            $tab.="<td align=center colspan='".(count($klmpkAbsn))."'>".$dtprd."</td>";
        }
        $tab.="</tr><tr  ".$bgc.">";
        foreach($dtPeriode as $dtprd){
            foreach($klmpkAbsn as $brsKet =>$hslKet)
            {
                $tab.="<td width=10px align=center>".$hslKet."</td>";
            }
        }
        $tab.="
        </tr></thead>
        <tbody>";
		$not=0;
       foreach($resData as $hslBrs => $hslAkhir){
           if((isset($hslAkhir[0]) ? $hslAkhir[0] : '')!=''){
                $not++;
                $tab.="<tr class=rowcontent><td>".$not."</td>
                <td>".$namakar[$hslAkhir[0]]."</td>
                <td>".$nmTipe[$hslAkhir[0]]."</td>
                <td>".$nmBagian[$hslAkhir[0]]."</td>
                <td>".$nmJabatan[$hslAkhir[0]]."</td>
                <td>".$optTmk[$hslAkhir[0]]."</td>";
                foreach($dtPeriode as $dtprd){
                    foreach($klmpkAbsn as $brsKet =>$hslKet)
                    {
                        $bgrd="";
                        if($hslKet=='H'){
                            if(@$hasilAbsn[$hslAkhir[0]][$dtprd][$hslKet]<$nilaiMax){
                                $bgrd="bgcolor=red";
                            }
                        }
                        $tab.="<td width=10px align=center ".$bgrd.">".@$hasilAbsn[$hslAkhir[0]][$dtprd][$hslKet]."</td>";
                    }
                }
           }
        }
        $tab.="</tbody></table>";

switch($proses)
{
        case'preview':
        echo $tab;
        break;
        case'excel':


                        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	

                        $nop_="RekapAbsen_PerBulan__".$kdOrg;
                        if(strlen($tab)>0)
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
                        if(!fwrite($handle,$tab))
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

}
?>