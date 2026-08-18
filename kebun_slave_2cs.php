<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$pt = checkPostGet('pt', '');
$tt = checkPostGet('tt', '');
$ip = checkPostGet('ip', '');

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$rangetanggal = rangeTanggal($tgl1, $tgl2);

$unit = checkPostGet('unit', '');
$divisi = checkPostGet('divisi', '');

$where='';
if($pt!=''){
	$where.=" and b.alokasi='".$pt."'";
}
$whtt='';
if($tt!=''){
	$whtt.=" and c.tahuntanam='".$tt."'";
}
$whip='';
if($ip!=''){
	$whip.=" and c.intiplasma='".$ip."'";
}

######################################
############# prepare data ###########
######################################


#kebun_spb_vw
#kebun_rekappnn_vw

#data pusingan
$str="select a.* from ".$dbname.".kebun_pusingan_vw a 
	  left join ".$dbname.".organisasi b on a.blok=b.kodeorganisasi  where 1=1 ".$where." and a.unit like '".$kdorg."%' and a.tanggal='".$tgl2."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $angka[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['angka'];
    $ket[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['keterangan'];
}

#rekap panen
#bentuk data blok dari rekap panen
$str="select distinct(a.blok) as blok,a.divisi,a.tahuntanam, c.intiplasma from ".$dbname.".kebun_rekappnn_vw a 
	  left join ".$dbname.".organisasi b on a.blok=b.kodeorganisasi 
	  left join ".$dbname.".setup_blok c on a.blok=c.kodeorg
	  where 1=1 ".$where." ".$whtt." ".$whip." and a.divisi like '".$kdorg."%'  and a.divisi like '".$divisi."%' and a.jjgpanen>0 order by blok asc ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $kdblok[$bar['blok']]=$bar['blok'];
    $kddivisi[$bar['divisi']]=$bar['divisi'];
    $tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
    
    $listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
    $listip[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['intiplasma'];
}

#data dari spb
$str="select distinct(a.blok) as blok,a.divisi,a.tahuntanam, c.intiplasma from ".$dbname.".kebun_spb_vw a
	  left join ".$dbname.".organisasi b on a.blok=b.kodeorganisasi 
	  left join ".$dbname.".setup_blok c on a.blok=c.kodeorg
	  where 1=1 ".$where." ".$whtt." ".$whip." and a.kodeorg like '".$kdorg."%' and a.divisi like '".$divisi."%' and a.jjg>0 order by blok asc";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $kdblok[$bar['blok']]=$bar['blok'];
    $tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
    $kddivisi[$bar['divisi']]=$bar['divisi'];
    
    $listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
    $listip[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['intiplasma'];
}

##ambil data setup_blok
$str="select a.luasareaproduktif,a.jenisbibit,a.jumlahpokok,substr(a.kodeorg,1,4) as unit,substr(a.kodeorg,1,6) as divisi, a.tahuntanam,a.kodeorg as blok from ".$dbname.".setup_blok_tahunan a 
		left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi  where 1=1 ".$where."
		and a.kodeorg like '".$kdorg."%' and a.tahun >= '".substr($tgl1,0,6)."' and a.tahun <= '".substr($tgl2,0,6)."' and a.kodeorg like '".$divisi."%'";
//exit('Error : '.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlbaris($res);
if($numrows==0)
{
    $str="select a.luasareaproduktif,a.jenisbibit,a.jumlahpokok,substr(a.kodeorg,1,4) as unit,substr(a.kodeorg,1,6) as divisi, a.tahuntanam,a.kodeorg as blok from ".$dbname.".setup_blok a 
        left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi  where 1=1 ".$where."
        and a.kodeorg like '".$kdorg."%' and a.kodeorg like '".$divisi."%'";

    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
}

while($bar=$res->fetch())
{
    $luas[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['luasareaproduktif'];
    $bbt[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jenisbibit'];
    $pkk[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jumlahpokok']; 
}

##########
## prepare data hari ini
##########

#hari ini
$str=" select * from ".$dbname.".kebun_rekappnn_vw where tanggal = '".$tgl2."'";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $hkpnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['tenagakerja'];
    $luaspnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['luaspanen'];
    
    $jjg[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jjgpanen'];   
    $jjgafkir[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jjgafkir'];
    $kgkebun[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['kgkebun'];
}

# sd hari ini
$str=" select * from ".$dbname.".kebun_rekappnn_vw where tanggal between '".$tgl1."' and '".$tgl2."'";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$hksdpnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['tenagakerja'];
    @$luassdpnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['luaspanen'];
    
    @$jjgsd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjgpanen'];  
    @$jjgafkirsd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjgafkir'];
    @$kgkebunsd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgkebun'];
}

#################
##data ambil dari spb_vw
################

#hi
$str=" select * from ".$dbname.".kebun_spb_vw where tanggal = '".$tgl2."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$jggpks[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjg'];
    @$kgpks[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
    @$brd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['brondolan'];
}

#sdhi
$str=" select * from ".$dbname.".kebun_spb_vw where tanggal between '".$tgl1."' and '".$tgl2."'";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$jggpkssd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjg'];
    @$kgpkssd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
    @$brdsd[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['brondolan'];
}


#cari max pusingan

$str="select max(angka) as angka,divisi,tahuntanam from ".$dbname.".kebun_pusingan_vw where tanggal='".$tgl2."'group by divisi,tahuntanam";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $angkatt[$bar['divisi']][$bar['tahuntanam']]=$bar['angka'];
}

$str="select max(angka) as angka,divisi,tahuntanam,tanggal from ".$dbname.".kebun_pusingan_vw where tanggal='".$tgl2."' group by divisi";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $angkadiv[$bar['divisi']]=$bar['angka'];
}

$str="select max(angka) as angka from ".$dbname.".kebun_pusingan_vw where tanggal='".$tgl2."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $gtangka=$bar['angka'];
}



#restan sumber 2 table..
#spb dan rekappnn

//indra
$str=" select sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir,divisi,tahuntanam,blok from ".$dbname.".kebun_rekappnn_vw where tanggal <= '".$tgl2."'  group by divisi,tahuntanam,blok  ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $rjjgpnn[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jjgpanen'];
    $rjjgafkir[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jjgafkir'];
}

$str=" select sum(jjg) as jjg,divisi,blok,tahuntanam from ".$dbname.".kebun_spb_vw where tanggal <= '".$tgl2."'  group by divisi,tahuntanam,blok ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $rjjgpks[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jjg'];
}



if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}

$span=count($rangetanggal);

$stream.="
    <thead>
        <tr class=rowheader>
            <th align=center  rowspan='3'>".$_SESSION['lang']['nourut']."</th>
            <th align=center  rowspan='3'>".$_SESSION['lang']['divisi']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['blok']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['tahuntanam']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['intiplasma']."</th>
            <th align=center  rowspan='3'>".$_SESSION['lang']['luas']."</th>
            <th align=center  rowspan='3'>".$_SESSION['lang']['jenisbibit']."</th>    
            <th align=center  rowspan='3'>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['pokok']."</th>        
            <th align=center  rowspan='3'>SPH</th>  
            <th align=center  colspan='2'>".$_SESSION['lang']['jhk']."</th>    
            <th align=center  colspan='2'>".$_SESSION['lang']['panen']." (Ha)</th>    
            <th align=center  colspan='2'>Ha / ".$_SESSION['lang']['jhk']."</th>    
            <th align=center  rowspan='3'>".$_SESSION['lang']['pusingan']."</th>
            <th align=center  rowspan='3'>".$_SESSION['lang']['rotasi']."</th> 
            <th align=center  colspan='6'>".$_SESSION['lang']['jjg']."</th>     
            <th align=center  colspan='6'>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['tbs']."</th>    
            <th align=center  rowspan='3'>Restan</th>   
            <th align=center  colspan='2'>".$_SESSION['lang']['persen']." ".$_SESSION['lang']['brondol']."</th>  
            <th align=center  colspan='2'>".$_SESSION['lang']['jjg']." / ".$_SESSION['lang']['jhk']."</th>  
            <th align=center  colspan='2'>Kg / ".$_SESSION['lang']['jhk']."</th>
            <th align=center  colspan='2'>".$_SESSION['lang']['bjr']."</th> 
            <th align=center  colspan='2'> Ton / Ha</th> 
        </tr>
        <tr>
            <th align=center rowspan='2'>".$_SESSION['lang']['hi']."</th>  
            <th align=center rowspan='2'>".$_SESSION['lang']['sdhi']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hi']."</th>  
            <th align=center rowspan='2'>".$_SESSION['lang']['sdhi']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hi']."</th>  
            <th align=center rowspan='2'>".$_SESSION['lang']['sdhi']."</th>
            <th align=center colspan='2'>".$_SESSION['lang']['kebun']."</th>
            <th align=center colspan='2'>Afkir</th>
            <th align=center colspan='2'>".$_SESSION['lang']['pabrik']."</th>
            <th align=center colspan='2'>".$_SESSION['lang']['kebun']."</th>
            <th align=center colspan='2'>".$_SESSION['lang']['brondol']."</th>
			
            <th align=center colspan='2'>".$_SESSION['lang']['pabrik']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hi']."</th>  
            <th align=center rowspan='2'>".$_SESSION['lang']['sdhi']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hi']."</th>  
            <th align=center rowspan='2'>".$_SESSION['lang']['sdhi']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hi']."</th>  
            <th align=center rowspan='2'>".$_SESSION['lang']['sdhi']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hi']."</th>  
            <th align=center rowspan='2'>".$_SESSION['lang']['sdhi']."</th>
			<th align=center rowspan='2'>".$_SESSION['lang']['hi']."</th>  
            <th align=center rowspan='2'>".$_SESSION['lang']['sdhi']."</th>
        </tr>
        <tr>
            <th align=center>".$_SESSION['lang']['hi']."</th>  
            <th align=center>".$_SESSION['lang']['sdhi']."</th>
            <th align=center>".$_SESSION['lang']['hi']."</th>  
            <th align=center>".$_SESSION['lang']['sdhi']."</th>
            <th align=center>".$_SESSION['lang']['hi']."</th>  
            <th align=center>".$_SESSION['lang']['sdhi']."</th>
            <th align=center>".$_SESSION['lang']['hi']."</th>  
            <th align=center>".$_SESSION['lang']['sdhi']."</th>
            <th align=center>".$_SESSION['lang']['hi']."</th>  
            <th align=center>".$_SESSION['lang']['sdhi']."</th>
            <th align=center>".$_SESSION['lang']['hi']."</th>
            <th align=center>".$_SESSION['lang']['sdhi']."</th>
        </tr>

    </thead>
 <tbody>";



$romawi = array("01"=>"I","02"=>"II","03"=>"III","04"=>"IV","05"=>"V","06"=>"VI","07"=>"VII","08"=>"VIII","09"=>"IX","10"=>"X","11"=>"XI","12"=>"XII","13"=>"XIII","14"=>"XIV","15"=>"XV","16"=>"XVI","17"=>"XVII","18"=>"XVIII","19"=>"XIX","20"=>"XX","A1"=>"Plasma I","A2"=>"Plasma II","A3"=>"Plasma III");


@$jumdiv=count($kdblok);
if($jumdiv>0)
{
    array_multisort($kddivisi,SORT_ASC);
    array_multisort($tahuntanam,SORT_ASC);
    array_multisort($kdblok,SORT_ASC);
}
else
{
    exit("error:Data kosong");
}


foreach($kddivisi as $divisi)
{
    foreach($tahuntanam as $thntnm)
    {   
        $listtahuntanam[$divisi][$thntnm]=isset($listtahuntanam[$divisi][$thntnm])?$listtahuntanam[$divisi][$thntnm]:'';
        if($listtahuntanam[$divisi][$thntnm]!='')
        {
            foreach($kdblok as $blok)
            {
                $listblok[$divisi][$thntnm][$blok]=isset($listblok[$divisi][$thntnm][$blok])?$listblok[$divisi][$thntnm][$blok]:'';
                if($listblok[$divisi][$thntnm][$blok]!='')
                {
                    
                    @$restan[$divisi][$thntnm][$blok]=$rjjgpnn[$divisi][$thntnm][$blok]-$rjjgafkir[$divisi][$thntnm][$blok]-$rjjgpks[$divisi][$thntnm][$blok];        
                    
                    $no+=1;
                    $stream.="<tr class=rowcontent>
                        <td align=center>".$no."</td>
                        <td align=center>".$romawi[substr($divisi,4,2)]."</td>
                        <td align=center>".$namaOrg[$listblok[$divisi][$thntnm][$blok]]."</td>    
                        <td align=center>".$listtahuntanam[$divisi][$thntnm]."</td>
                        <td align=center>".$listip[$divisi][$thntnm][$blok]."</td>
                        <td align=right>".number_format(@$luas[$divisi][$thntnm][$blok],2)."</td>    
                        <td align=left>".@$bbt[$divisi][$thntnm][$blok]."</td>    
                        <td align=right>".@number_format($pkk[$divisi][$thntnm][$blok])."</td>    
                        <td align=right>".@number_format($pkk[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok])."</td>    
                        
                        <td align=right>".@$hkpnn[$divisi][$thntnm][$blok]."</td> 
                        <td align=right>".@$hksdpnn[$divisi][$thntnm][$blok]."</td> 
                        <td align=right>".@$luaspnn[$divisi][$thntnm][$blok]."</td> 
                        <td align=right>".@$luassdpnn[$divisi][$thntnm][$blok]."</td> 
                        <td align=right>".@number_format($luaspnn[$divisi][$thntnm][$blok]/$hkpnn[$divisi][$thntnm][$blok],2)."</td>
                        <td align=right>".@number_format($luassdpnn[$divisi][$thntnm][$blok]/$hksdpnn[$divisi][$thntnm][$blok],2)."</td>
                        <td align=right>".@$angka[$divisi][$thntnm][$blok]."</td>
                        <td align=right>".@number_format($luassdpnn[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
                        
                        <td align=right>".@number_format($jjg[$divisi][$thntnm][$blok])."</td>    
                        <td align=right>".@number_format($jjgsd[$divisi][$thntnm][$blok])."</td>    
                        <td align=right>".@number_format($jjgafkir[$divisi][$thntnm][$blok])."</td>    
                        <td align=right>".@number_format($jjgafkirsd[$divisi][$thntnm][$blok])."</td>    
                        <td align=right>".@number_format($jggpks[$divisi][$thntnm][$blok])."</td>    
                        <td align=right>".@number_format($jggpkssd[$divisi][$thntnm][$blok])."</td>        

                        <td align=right>".@number_format($kgkebun[$divisi][$thntnm][$blok],2)."</td>    
                        <td align=right>".@number_format($kgkebunsd[$divisi][$thntnm][$blok],2)."</td>    
						<td align=right>".@number_format($brd[$divisi][$thntnm][$blok])."</td>    
                        <td align=right>".@number_format($brdsd[$divisi][$thntnm][$blok])."</td>
						
                        <td align=right>".@number_format($kgpks[$divisi][$thntnm][$blok],2)."</td>    
                        <td align=right>".@number_format($kgpkssd[$divisi][$thntnm][$blok],2)."</td>
                        <td align=right>".@number_format($restan[$divisi][$thntnm][$blok])."</td>  
						
						<td align=right>".@number_format($brd[$divisi][$thntnm][$blok]/$kgpks[$divisi][$thntnm][$blok]*100,2)."</td>    
                        <td align=right>".@number_format($brdsd[$divisi][$thntnm][$blok]/$kgpkssd[$divisi][$thntnm][$blok]*100,2)."</td>                        

						
                        <td align=right>".@number_format($jjg[$divisi][$thntnm][$blok]/$hkpnn[$divisi][$thntnm][$blok],2)."</td>
                        <td align=right>".@number_format($jjgsd[$divisi][$thntnm][$blok]/$hksdpnn[$divisi][$thntnm][$blok],2)."</td>
                        <td align=right>".@number_format($kgpks[$divisi][$thntnm][$blok]/$hkpnn[$divisi][$thntnm][$blok],2)."</td>
                        <td align=right>".@number_format($kgpkssd[$divisi][$thntnm][$blok]/$hksdpnn[$divisi][$thntnm][$blok],2)."</td>
                        
                        <td align=right>".@number_format($kgpks[$divisi][$thntnm][$blok]/$jggpks[$divisi][$thntnm][$blok],2)."</td>
                        <td align=right>".@number_format($kgpkssd[$divisi][$thntnm][$blok]/$jggpkssd[$divisi][$thntnm][$blok],2)."</td>
                        
                        <td align=right>".@number_format($kgpks[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]/1000,2)."</td>
                        <td align=right>".@number_format($kgpkssd[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]/1000,2)."</td>
                        

                    ";
                    
                    @$luastt[$divisi][$thntnm]+=$luas[$divisi][$thntnm][$blok];
                    @$pkktt[$divisi][$thntnm]+=$pkk[$divisi][$thntnm][$blok];
                    
                    @$hkpnntt[$divisi][$thntnm]+=$hkpnn[$divisi][$thntnm][$blok];
                    @$hksdpnntt[$divisi][$thntnm]+=$hksdpnn[$divisi][$thntnm][$blok];
                    @$luaspnntt[$divisi][$thntnm]+=$luaspnn[$divisi][$thntnm][$blok];
                    @$luassdpnntt[$divisi][$thntnm]+=$luassdpnn[$divisi][$thntnm][$blok];
                    
                    @$jjgtt[$divisi][$thntnm]+=$jjg[$divisi][$thntnm][$blok];
                    @$jjgsdtt[$divisi][$thntnm]+=$jjgsd[$divisi][$thntnm][$blok];
                    @$jjgafkirtt[$divisi][$thntnm]+=$jjgafkir[$divisi][$thntnm][$blok];
                    @$jjgafkirsdtt[$divisi][$thntnm]+=$jjgafkirsd[$divisi][$thntnm][$blok];
                    @$jggpkstt[$divisi][$thntnm]+=$jggpks[$divisi][$thntnm][$blok];
                    @$jggpkssdtt[$divisi][$thntnm]+=$jggpkssd[$divisi][$thntnm][$blok];
                    
                    @$kgkebuntt[$divisi][$thntnm]+=$kgkebun[$divisi][$thntnm][$blok];
                    @$kgkebunsdtt[$divisi][$thntnm]+=$kgkebunsd[$divisi][$thntnm][$blok];
                    @$kgpkstt[$divisi][$thntnm]+=$kgpks[$divisi][$thntnm][$blok];
                    @$kgpkssdtt[$divisi][$thntnm]+=$kgpkssd[$divisi][$thntnm][$blok];
                    @$brdtt[$divisi][$thntnm]+=$brd[$divisi][$thntnm][$blok];
                    @$brdsdtt[$divisi][$thntnm]+=$brdsd[$divisi][$thntnm][$blok];
                    
                    @$restantt[$divisi][$thntnm]+=$restan[$divisi][$thntnm][$blok];
                    
                    
                }
            }
            $stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=5>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['tahuntanam']."  ".$thntnm."</td>
                    <td align=right>".@number_format($luastt[$divisi][$thntnm],2)."</td>
                    <td></td>
                    <td align=right>".@number_format($pkktt[$divisi][$thntnm])."</td>
                    <td align=right>".@number_format($pkktt[$divisi][$thntnm]/$luastt[$divisi][$thntnm])."</td>
                        
                    <td align=right>".$hkpnntt[$divisi][$thntnm]."</td>
                    <td align=right>".$hksdpnntt[$divisi][$thntnm]."</td>
                    <td align=right>".$luaspnntt[$divisi][$thntnm]."</td>
                    <td align=right>".$luassdpnntt[$divisi][$thntnm]."</td>
                    <td align=right>".@number_format($luaspnntt[$divisi][$thntnm]/$hkpnntt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@number_format($luassdpnntt[$divisi][$thntnm]/$hksdpnntt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@$angkatt[$divisi][$thntnm]."</td>
                    <td align=right>".@number_format($luassdpnntt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
                        
                    <td align=right>".@number_format($jjgtt[$divisi][$thntnm])."</td>
                    <td align=right>".@number_format($jjgsdtt[$divisi][$thntnm])."</td>
                    <td align=right>".@number_format($jjgafkirtt[$divisi][$thntnm])."</td>
                    <td align=right>".@number_format($jjgafkirsdtt[$divisi][$thntnm])."</td>
                    <td align=right>".@number_format($jggpkstt[$divisi][$thntnm])."</td>
                    <td align=right>".@number_format($jggpkssdtt[$divisi][$thntnm])."</td>
                        
                    <td align=right>".@number_format($kgkebuntt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@number_format($kgkebunsdtt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@number_format($brdtt[$divisi][$thntnm])."</td>
                    <td align=right>".@number_format($brdsdtt[$divisi][$thntnm])."</td>
					
                    <td align=right>".@number_format($kgpkstt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@number_format($kgpkssdtt[$divisi][$thntnm],2)."</td>
                        
                    <td align=right>".@number_format($restantt[$divisi][$thntnm])."</td>
                    <td align=right>".@number_format($brdtt[$divisi][$thntnm]/$kgpkstt[$divisi][$thntnm]*100,2)."</td>
                    <td align=right>".@number_format($brdsdtt[$divisi][$thntnm]/$kgpkssdtt[$divisi][$thntnm]*100,2)."</td>    
                    <td align=right>".@number_format($jjgtt[$divisi][$thntnm]/$hkpnntt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@number_format($jjgsdtt[$divisi][$thntnm]/$hksdpnntt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@number_format($kgpkstt[$divisi][$thntnm]/$hkpnntt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@number_format($kgpkssdtt[$divisi][$thntnm]/$hksdpnntt[$divisi][$thntnm],2)."</td>

                    <td align=right>".@number_format($kgpkstt[$divisi][$thntnm]/$jggpkstt[$divisi][$thntnm],2)."</td>
                    <td align=right>".@number_format($kgpkssdtt[$divisi][$thntnm]/$jggpkssdtt[$divisi][$thntnm],2)."</td>
                        
                    <td align=right>".@number_format($kgpkstt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]/1000,2)."</td>
                    <td align=right>".@number_format($kgpkssdtt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]/1000,2)."</td>
                        
                </tr>
                ";
            
            @$luasdiv[$divisi]+=$luastt[$divisi][$thntnm];
            @$pkkdiv[$divisi]+=$pkktt[$divisi][$thntnm];
            
            @$hkpnndiv[$divisi]+=$hkpnntt[$divisi][$thntnm];
            @$hksdpnndiv[$divisi]+=$hksdpnntt[$divisi][$thntnm];
            @$luaspnndiv[$divisi]+=$luaspnntt[$divisi][$thntnm];
            @$luassdpnndiv[$divisi]+=$luassdpnntt[$divisi][$thntnm];
            
            @$jjgdiv[$divisi]+=$jjgtt[$divisi][$thntnm];
            @$jjgsddiv[$divisi]+=$jjgsdtt[$divisi][$thntnm];
            @$jjgafkirdiv[$divisi]+=$jjgafkirtt[$divisi][$thntnm];
            @$jjgafkirsddiv[$divisi]+=$jjgafkirsdtt[$divisi][$thntnm];
            @$jggpksdiv[$divisi]+=$jggpkstt[$divisi][$thntnm];
            @$jggpkssddiv[$divisi]+=$jggpkssdtt[$divisi][$thntnm];
            
            @$kgkebundiv[$divisi]+=$kgkebuntt[$divisi][$thntnm];
            @$kgkebunsddiv[$divisi]+=$kgkebunsdtt[$divisi][$thntnm];
            @$kgpksdiv[$divisi]+=$kgpkstt[$divisi][$thntnm];
            @$kgpkssddiv[$divisi]+=$kgpkssdtt[$divisi][$thntnm];
            @$brddiv[$divisi]+=$brdtt[$divisi][$thntnm];
            @$brdsddiv[$divisi]+=$brdsdtt[$divisi][$thntnm];
            
            @$restandiv[$divisi]+=$restantt[$divisi][$thntnm];
            
            
        }
        
    }
    $stream.="
        <tr bgcolor=#48D1CC>
            <td align=left colspan=5>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['divisi']." ".$divisi."</td>
            <td align=right>".@number_format($luasdiv[$divisi],2)."</td>
            <td></td>
            <td align=right>".@number_format($pkkdiv[$divisi])."</td>
            <td align=right>".@number_format($pkkdiv[$divisi]/$luasdiv[$divisi])."</td> 

            <td align=right>".$hkpnndiv[$divisi]."</td>
            <td align=right>".$hksdpnndiv[$divisi]."</td>
            <td align=right>".$luaspnndiv[$divisi]."</td>
            <td align=right>".$luassdpnndiv[$divisi]."</td>
            <td align=right>".@number_format($luaspnndiv[$divisi]/$hkpnndiv[$divisi],2)."</td>
            <td align=right>".@number_format($luassdpnndiv[$divisi]/$hksdpnndiv[$divisi],2)."</td>    
            <td align=right>".@$angkadiv[$divisi]."</td>  
            <td align=right>".@number_format($luassdpnndiv[$divisi]/$luasdiv[$divisi],2)."</td>       
                
            <td align=right>".@number_format($jjgdiv[$divisi])."</td>
            <td align=right>".@number_format($jjgsddiv[$divisi])."</td>
            <td align=right>".@number_format($jjgafkirdiv[$divisi])."</td>
            <td align=right>".@number_format($jjgafkirsddiv[$divisi])."</td>
            <td align=right>".@number_format($jggpksdiv[$divisi])."</td>
            <td align=right>".@number_format($jggpkssddiv[$divisi])."</td>

            <td align=right>".@number_format($kgkebundiv[$divisi],2)."</td>
            <td align=right>".@number_format($kgkebunsddiv[$divisi],2)."</td>
            <td align=right>".@number_format($brddiv[$divisi])."</td>
            <td align=right>".@number_format($brdsddiv[$divisi])."</td>
            <td align=right>".@number_format($kgpksdiv[$divisi],2)."</td>
            <td align=right>".@number_format($kgpkssddiv[$divisi],2)."</td> 
                
            <td align=right>".@number_format($restandiv[$divisi])."</td>
            <td align=right>".@number_format($brddiv[$divisi]/$kgpksdiv[$divisi]*100,2)."</td>
            <td align=right>".@number_format($brdsddiv[$divisi]/$kgpkssddiv[$divisi]*100,2)."</td>    
            <td align=right>".@number_format($jjgdiv[$divisi]/$hkpnndiv[$divisi],2)."</td>
            <td align=right>".@number_format($jjgsddiv[$divisi]/$hksdpnndiv[$divisi],2)."</td>
            <td align=right>".@number_format($kgpksdiv[$divisi]/$hkpnndiv[$divisi],2)."</td>
            <td align=right>".@number_format($kgpkssddiv[$divisi]/$hksdpnndiv[$divisi],2)."</td>

            <td align=right>".@number_format($kgpksdiv[$divisi]/$jggpksdiv[$divisi],2)."</td>
            <td align=right>".@number_format($kgpkssddiv[$divisi]/$jggpkssddiv[$divisi],2)."</td>
                
            <td align=right>".@number_format($kgpksdiv[$divisi]/$luasdiv[$divisi]/1000,2)."</td>
            <td align=right>".@number_format($kgpkssddiv[$divisi]/$luasdiv[$divisi]/1000,2)."</td>
              
        </tr>
        ";
    
    @$gtluas+=$luasdiv[$divisi];
    @$gtpkk+=$pkkdiv[$divisi];
    
    @$gthkpnn+=$hkpnndiv[$divisi];
    @$gthksdpnn+=$hksdpnndiv[$divisi];
    @$gtluaspnn+=$luaspnndiv[$divisi];
    @$gtluassdpnn+=$luassdpnndiv[$divisi];
    
    @$gtjjg+=$jjgdiv[$divisi];
    @$gtjjgsd+=$jjgsddiv[$divisi];
    @$gtjjgafkir+=$jjgafkirdiv[$divisi];
    @$gtjjgafkirsd+=$jjgafkirsddiv[$divisi];
    @$gtjggpks+=$jggpksdiv[$divisi];
    @$gtjggpkssd+=$jggpkssddiv[$divisi];

    @$gtkgkebun+=$kgkebundiv[$divisi];
    @$gtkgkebunsd+=$kgkebunsddiv[$divisi];
    @$gtkgpks+=$kgpksdiv[$divisi];
    @$gtkgpkssd+=$kgpkssddiv[$divisi];
    @$gtbrd+=$brddiv[$divisi];
    @$gtbrdsd+=$brdsddiv[$divisi];
    
    @$gtrestan+=$restandiv[$divisi];
    
    
}
$stream.="
        <tr bgcolor=#009999>
            <td align=left colspan=5>".$_SESSION['lang']['grnd_total']." ".$kdorg."</td>
           
            <td align=right>".@number_format($gtluas,2)."</td>
            <td></td>
            <td align=right>".@number_format($gtpkk)."</td>
            <td align=right>".@number_format($gtpkk/$gtluas)."</td>   
            
            <td align=right>".$gthkpnn."</td>
            <td align=right>".$gthksdpnn."</td>
            <td align=right>".$gtluaspnn."</td>
            <td align=right>".$gtluassdpnn."</td>
            <td align=right>".@number_format($gtluaspnn/$gthkpnn,2)."</td>
            <td align=right>".@number_format($gtluassdpnn/$gthksdpnn,2)."</td>  
            <td align=right>".$gtangka."</td>
            <td align=right>".@number_format($gtluassdpnn/$gtluas,2)."</td> 
                
            <td align=right>".@number_format($gtjjg)."</td>
            <td align=right>".@number_format($gtjjgsd)."</td>
            <td align=right>".@number_format($gtjjgafkir)."</td>
            <td align=right>".@number_format($gtjjgafkirsd)."</td>
            <td align=right>".@number_format($gtjggpks)."</td>
            <td align=right>".@number_format($gtjggpkssd)."</td>

            <td align=right>".@number_format($gtkgkebun,2)."</td>
            <td align=right>".@number_format($gtkgkebunsd,2)."</td>
            <td align=right>".@number_format($gtbrd)."</td>
            <td align=right>".@number_format($gtbrdsd)."</td>
            <td align=right>".@number_format($gtkgpks,2)."</td>
            <td align=right>".@number_format($gtkgpkssd,2)."</td>
                
            <td align=right>".@number_format($gtrestan)."</td>
            <td align=right>".@number_format($gtbrd/$gtkgpks*100,2)."</td>
            <td align=right>".@number_format($gtbrdsd/$gtkgpkssd*100,2)."</td>    
            <td align=right>".@number_format($gtjjg/$gthkpnn,2)."</td>
            <td align=right>".@number_format($gtjjgsd/$gthksdpnn,2)."</td>
            <td align=right>".@number_format($gtkgpks/$gthkpnn,2)."</td>
            <td align=right>".@number_format($gtkgpkssd/$gthksdpnn,2)."</td>

            <td align=right>".@number_format($gtkgpks/$gtjggpks,2)."</td>
            <td align=right>".@number_format($gtkgpkssd/$gtjggpkssd,2)."</td>
                
            <td align=right>".@number_format($gtkgpks/$gtluas/1000,2)."</td>
            <td align=right>".@number_format($gtkgpkssd/$gtluas/1000,2)."</td>
            
            
            </tr><thead>
            ";
                  
$stream.="
 </tbody>
     </table>";
	 

	 
switch ($proses) {
	
	case 'changediv':
	$optDiv="";
	$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$unit."' order by namaorganisasi asc ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
	while ($bar = $res->fetch()) {
		$optDiv.="<option value=" . $bar['kodeorganisasi'] . ">".$bar['namaorganisasi']."</option>";
	}
	
	echo $optDiv;
	break;
	
	
if($kdorg=='')
{
    echo"Warning: Unit tidak boleh kosong"; 
    exit;
}

if(($tgl1=='')or($tgl2==''))
{
    echo"Warning: Tanggal tidak boleh kosong"; 
    exit;
}

else if($tgl1>$tgl2)
{
    echo"Warning: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
    exit;
}
	


######PREVIEW
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "Laporan Crop Statement" . $kdorg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
        break;
		
	
		
}



?>