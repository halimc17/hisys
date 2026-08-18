<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');


$proses=checkPostGet('proses','');
$kdpabrik=checkPostGet('kdpabrik','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));


$optnmor=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjnvhc=makeOption($dbname, 'vhc_5jenisvhc','jenisvhc,namajenisvhc');
$optnmbar=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$optnamacostumer=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier');


if(($proses=='preview')or($proses=='excel')or($proses=='pdf'))
{

    if(($tgl1=='')or($tgl2==''))
	{
		echo"Error: Tanggal tidak boleh kosong"; 
		exit;
    }

    else if($tgl1>$tgl2)
	{
        echo"Error: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
		exit;
    }
	
}



$pt=  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');


$kdpt=$pt[$kdpabrik];

#bentuk afdeling internal
$iDiv="select * from ".$dbname.".organisasi where tipe='AFDELING' and induk in "
        . " (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kdpt."') order by kodeorganisasi asc ";
$nDiv=$owlPDO->query($iDiv) or die(print " Gagal: ".PDOException::getMessage());
$nDiv->setFetchMode(PDO::FETCH_ASSOC);
while($dDiv=$nDiv->fetch())
{
    $divisi[$dDiv['kodeorganisasi']]=$dDiv['kodeorganisasi'];
    $nmdivisi[$dDiv['kodeorganisasi']]=$dDiv['namaorganisasi'];
}


#bentuk afdeling afiliasi
$iDivAfi="select * from ".$dbname.".organisasi where tipe='AFDELING' and induk in "
        . " (select kodeorganisasi from ".$dbname.".organisasi where induk!='".$kdpt."') order by kodeorganisasi asc ";
$nDivAfi=$owlPDO->query($iDivAfi) or die(print " Gagal: ".PDOException::getMessage());
$nDivAfi->setFetchMode(PDO::FETCH_ASSOC);
while($dDivAfi=$nDivAfi->fetch()){
    $divisiAfi[$dDivAfi['kodeorganisasi']]=$dDivAfi['kodeorganisasi'];
    $nmdivisiAfi[$dDivAfi['kodeorganisasi']]=$dDivAfi['namaorganisasi'];
}



##ambil tbs diterima 
$iTbsInt="select sum(jjg) as jjg,sum(beratbersih) as netto,sum(beratbersih)/sum(jjg) as bjr,"
        . "divisi,sum(jjgsortasi) as jjgsortasi,sum(kgpotsortasi) as kgpotsortasi,"
        . "kodeorg from ".$dbname.".pabrik_timbangan_vw"
        . " where kodecustomer='' and kodebarang='40000003'  and millcode='".$kdpabrik."' "
        . " and tanggal between '".$tgl1."' and '".$tgl2."'  group by divisi";
$nTbsInt=$owlPDO->query($iTbsInt) or die(print " Gagal: ".PDOException::getMessage());
$nTbsInt->setFetchMode(PDO::FETCH_ASSOC);
while($dTbsInt=$nTbsInt->fetch())
{
    $jjg[$dTbsInt['divisi']]=$dTbsInt['jjg'];
    $netto[$dTbsInt['divisi']]=$dTbsInt['netto'];
    $bjr[$dTbsInt['divisi']]=$dTbsInt['bjr'];
    $jjgsortasi[$dTbsInt['divisi']]=$dTbsInt['jjgsortasi'];
    $kgpotsortasi[$dTbsInt['divisi']]=$dTbsInt['kgpotsortasi'];
    $persentase[$dTbsInt['divisi']]=$dTbsInt['persentase'];
}

#ambil jjg sortasi
#formula count * 100
$iJjgSort="SELECT count(distinct(notiket))*100 as jmljjg,divisi from ".$dbname.".pabrik_sortasi_timbangan_vw"
        . " where kodecustomer='' and tanggal between '".$tgl1."' and '".$tgl2."' and millcode='".$kdpabrik."' group by divisi";
$nJjgSort=$owlPDO->query($iJjgSort) or die(print " Gagal: ".PDOException::getMessage());
$nJjgSort->setFetchMode(PDO::FETCH_ASSOC);
while($dJjgSort=$nJjgSort->fetch())
{
    $jmljjgsor[$dJjgSort['divisi']]=$dJjgSort['jmljjg'];
}



$iSor="select sum(jumlah) as jumlah,kodefraksi,divisi from ".$dbname.".pabrik_sortasi_timbangan_vw "
        . " where kodecustomer='' and tanggal between '".$tgl1."' and '".$tgl2."' and kodebarang='40000003' and millcode='".$kdpabrik."'"
        . " group by kodefraksi,divisi";
$sor = array();
$nSor=$owlPDO->query($iSor) or die(print " Gagal: ".PDOException::getMessage());
$nSor->setFetchMode(PDO::FETCH_ASSOC);
while($dSor=$nSor->fetch())
{
    $sor[$dSor['divisi']][$dSor['kodefraksi']]=$dSor['jumlah'];
}




######prepare data external
#############################
//bentuk list customer 

$iCust="select distinct(kodecustomer) as kodecustomer from ".$dbname.".pabrik_timbangan_vw"
        . " where kodecustomer!='' and intex=0 and kodebarang='40000003'  and millcode='".$kdpabrik."' "
        . " and tanggal between '".$tgl1."' and '".$tgl2."'";
$nCust=$owlPDO->query($iCust) or die(print " Gagal: ".PDOException::getMessage());
$nCust->setFetchMode(PDO::FETCH_ASSOC);
while($dCust=$nCust->fetch())
{
    $cust[$dCust['kodecustomer']]=$dCust['kodecustomer'];
}



##ambil tbs diterima 
$iTbsExt="select sum(jjg) as jjg,sum(beratbersih) as netto,sum(beratbersih)/sum(jjg) as bjr,"
        . "kodecustomer,sum(jjgsortasi) as jjgsortasi,sum(kgpotsortasi) as kgpotsortasi,"
        . "kodeorg from ".$dbname.".pabrik_timbangan_vw"
        . " where intex=0 and kodebarang='40000003'  and millcode='".$kdpabrik."' "
        . " and tanggal between '".$tgl1."' and '".$tgl2."'  group by kodecustomer";
$nTbsExt=$owlPDO->query($iTbsExt) or die(print " Gagal: ".PDOException::getMessage());
$nTbsExt->setFetchMode(PDO::FETCH_ASSOC);
while($dTbsExt=$nTbsExt->fetch()){
    $jjg[$dTbsExt['kodecustomer']]=$dTbsExt['jjg'];
    $netto[$dTbsExt['kodecustomer']]=$dTbsExt['netto'];
    $bjr[$dTbsExt['kodecustomer']]=$dTbsExt['bjr'];
    $jjgsortasi[$dTbsExt['kodecustomer']]=$dTbsExt['jjgsortasi'];
    $kgpotsortasi[$dTbsExt['kodecustomer']]=$dTbsExt['kgpotsortasi'];
    $persentase[$dTbsExt['kodecustomer']]=isset($dTbsExt['persentase'])?$dTbsExt['persentase']:'';
}

$iJjgSortExt="SELECT count(distinct(notiket))*100 as jmljjg,kodecustomer from ".$dbname.".pabrik_sortasi_timbangan_vw"
        . " where intex=0 and tanggal between '".$tgl1."' and '".$tgl2."' and millcode='".$kdpabrik."' group by kodecustomer";
$nJjgSortExt=$owlPDO->query($iJjgSortExt) or die(print " Gagal: ".PDOException::getMessage());
$nJjgSortExt->setFetchMode(PDO::FETCH_ASSOC);
while($dJjgSortExt=$nTbsExt->fetch())
{
    $jmljjgsor[$dJjgSortExt['kodecustomer']]=$dJjgSortExt['jmljjg'];
}


$iSorExt="select sum(jumlah) as jumlah,kodefraksi,kodecustomer from ".$dbname.".pabrik_sortasi_timbangan_vw "
        . " where intex=0 and tanggal between '".$tgl1."' and '".$tgl2."' and kodebarang='40000003' and millcode='".$kdpabrik."'"
        . " group by kodefraksi,kodecustomer";
$sor = array();
$nSorExt=$owlPDO->query($iSorExt) or die(print " Gagal: ".PDOException::getMessage());
$nSorExt->setFetchMode(PDO::FETCH_ASSOC);
while($dSorExt=$nSorExt->fetch())
{
    $sor[$dSorExt['kodecustomer']][$dSorExt['kodefraksi']]=$dSorExt['jumlah'];
}



/*echo"<pre>";
print_r($sor);
echo"</pre>";*/


                if($proses=='excel')
                {
                    $stream="<table cellspacing='1' border='1' class='sortable'>";
                }
                else 
                {
                    $stream.="<table cellspacing='1' border='0' class='sortable'>";
                }
                $stream.="<thead class=rowheader>
                        <tr>
                          <td rowspan=3 align=center>NO</td>
                          <td rowspan=3 align=center>".$_SESSION['lang']['keterangan']."</td>
                          <td colspan=5 align=center>".$_SESSION['lang']['diterima']."</td>
                          <td colspan=9 align=center>".$_SESSION['lang']['sortasi']."</td>
                          <td colspan=3 align=center>PENALTY</td>
                          <td rowspan=3 align=center>Description</td>
                        </tr>
                        <tr>
                          <td align=center>".$_SESSION['lang']['tbs']."<br />".$_SESSION['lang']['diterima']."</td>
                          <td align=center>".$_SESSION['lang']['tbs']."<br />".$_SESSION['lang']['diterima']."</td>
                          <td align=center>".$_SESSION['lang']['bjr']."</td>
                          <td align=center>".$_SESSION['lang']['tbs']."<br />Sample</td>
                          <td align=center>%</td>
                          <td align=center>Unripe</td>
                          <td align=center>Over<br />Ripe</td>
                          <td align=center>Empty<br />Bunch</td>
                          <td align=center>Abnormal</td>
                          <td align=center>Rotten<br />Bunch</td>
                          <td align=center>Ripe</td>
                          <td align=center>Total</td>
                          <td align=center>Long<br />Stalk</td>
                          <td align=center>Loose<br />Fruit</td>
                          <td align=center>%<br />Penalty</td>
                          <td align=center>".$_SESSION['lang']['potongan']."<br />Grading</td>
                          <td align=center>Netto(Normal)</td>
                        </tr>
                        <tr>
                          <td align=center>(Kg)</td>
                          <td align=center>(".$_SESSION['lang']['jjg'].")</td>
                          <td align=center>".$_SESSION['lang']['jjg']."/Kg</td>
                          <td align=center>(".$_SESSION['lang']['jjg'].")</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(%)</td>
                          <td align=center>(Kg)</td>
                          <td align=center>(Kg)</td>
                        </tr>
                    </thead>
                <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr
##bentuk array
$stream.="<tr class=rowcontent>
    <td align=center bgcolor=#00CCFF><b>1</b></td>        
    <td colspan=19 bgcolor=#00CCFF><b>INTERNAL</b></td>
</tr>";//00FFFF

if(is_array($divisi)){
        $tnettoint=$tjjgint=$tjmljjgsorint=$tkgpotsortasiint=$tnormalint=0;
	foreach($divisi as $kddiv)
	{
            
            
            $netto[$kddiv]=isset($netto[$kddiv])?$netto[$kddiv]:'0';
            $jjg[$kddiv]=isset($jjg[$kddiv])?$jjg[$kddiv]:'0';
            $bjr[$kddiv]=isset($bjr[$kddiv])?$bjr[$kddiv]:'0';
            $jmljjgsor[$kddiv]=isset($jmljjgsor[$kddiv])?$jmljjgsor[$kddiv]:'0';
            
		$persen=$jjg[$kddiv]>0?$jmljjgsor[$kddiv]/$jjg[$kddiv]*100:0;
		$stream.="<tr class=rowcontent>
					<td></td>
					<td>".$kddiv." - ". ucwords(strtolower($nmdivisi[$kddiv]))."</td>
					<td align=right>".number_format($netto[$kddiv])."</td> 
					<td align=right>".number_format($jjg[$kddiv])."</td>
					<td align=right>".number_format($bjr[$kddiv],2)."</td>    
					<td align=right>".number_format($jmljjgsor[$kddiv])."</td> 
					<td align=right>".number_format($persen,2)."</td>
					<td align=right>".($jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['B']/$jmljjgsor[$kddiv]*100,2):0)."</td>    
					<td align=right>".($jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['A']/$jmljjgsor[$kddiv]*100,2):0)."</td>    
					<td align=right>".($jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['C']/$jmljjgsor[$kddiv]*100,2):0)."</td>    
					<td align=right>".($jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['D']/$jmljjgsor[$kddiv]*100,2):0)."</td>  
					<td align=right>".($jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['E']/$jmljjgsor[$kddiv]*100,2):0)."</td>";
		
		$b=$jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['B']/$jmljjgsor[$kddiv]*100,2):0;
		$a=$jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['A']/$jmljjgsor[$kddiv]*100,2):0;
		$c=$jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['C']/$jmljjgsor[$kddiv]*100,2):0;
		$d=$jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['D']/$jmljjgsor[$kddiv]*100,2):0;
		$e=$jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['E']/$jmljjgsor[$kddiv]*100,2):0;
		
		$tsor5=$b+$a+$c+$d+$e;
		$ripe=100-$tsor5;
		$totalsor1=$ripe+$tsor5;
		//$tsor=
                
                $kgpotsortasi[$kddiv]=isset($kgpotsortasi[$kddiv])?$kgpotsortasi[$kddiv]:'0';
                
		$stream.="
					<td align=right>".number_format($ripe,2)."</td>
					<td align=right>".number_format($totalsor1,2)."</td>    
					<td align=right>".($jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['F']/$jmljjgsor[$kddiv]*100,2):0)."</td>  
					<td align=right>".($jmljjgsor[$kddiv]>0?number_format($sor[$kddiv]['G']/$jmljjgsor[$kddiv]*100,2):0)."</td>";
		$stream.="
					<td align=right>".($netto[$kddiv]>0?number_format($kgpotsortasi[$kddiv]/$netto[$kddiv]*100,2):0)."</td>
					<td align=right>".number_format($kgpotsortasi[$kddiv])."</td>
					<td align=right>".number_format($netto[$kddiv]-$kgpotsortasi[$kddiv])."</td>
					<td></td>
					";
		$stream.="                
				</tr>";
		
		$tnettoint+=$netto[$kddiv];
		$tjjgint+=$jjg[$kddiv];
		$tjmljjgsorint+=$jmljjgsor[$kddiv];
		$tkgpotsortasiint+=$kgpotsortasi[$kddiv];
		$tnormalint+=$netto[$kddiv]-$kgpotsortasi[$kddiv];    
	}
}else{
}

$tbjrint=$tjjgint>0?$tnettoint/$tjjgint:0;
$tpersen=$tjjgint>0?$tjmljjgsorint/$tjjgint*100:0;
$tpersenpenint=$tnettoint>0?$tkgpotsortasiint/$tnettoint*100:0;



$stream.="<tr class=rowcontent>
    <td align=center colspan=2><b>Total</b></td>        
    <td align=right><b>".number_format($tnettoint)."</b></td>
    <td align=right><b>".number_format($tjjgint)."</b></td>    
    <td align=right><b>".number_format($tbjrint,2)."</b></td>    
    <td align=right><b>".number_format($tjmljjgsorint)."</b></td>    
    <td align=right><b>".number_format($tpersen,2)."</b></td>";
    
    for($i=1;$i<=9;$i++)
    {
        $stream.="<td align=right><b></b></td>";  
    }
$stream.="
    <td align=right><b>".number_format($tpersenpenint,2)."</b></td>    
    <td align=right><b>".number_format($tkgpotsortasiint)."</b></td>  
    <td align=right><b>".number_format($tnormalint)."</b></td>
    <td></td>
        ";
$stream.="</tr>";
                
$stream.="<tr class=rowcontent>
    <td align=center bgcolor=#00CCFF><b>1</b></td>        
    <td colspan=19 bgcolor=#00CCFF><b>AFILIASI</b></td>
</tr>";   








#########################
##bentuk afiliasi
#########################
if(is_array($divisiAfi))
{
    $tnettoafi=$tjjgafi=$tjmljjgsorafi=$tkgpotsortasiafi=$tnormalafi=0;
    foreach($divisiAfi as $kddivafi)
    {
        $nmdivisiAfi[$kddivafi]=isset($nmdivisiAfi[$kddivafi])?$nmdivisiAfi[$kddivafi]:'0';
        $netto[$kddivafi]=isset($netto[$kddivafi])?$netto[$kddivafi]:'0';
        $jjg[$kddivafi]=isset($jjg[$kddivafi])?$jjg[$kddivafi]:'0';
        $bjr[$kddivafi]=isset($bjr[$kddivafi])?$bjr[$kddivafi]:'0';
        $jmljjgsor[$kddivafi]=isset($jmljjgsor[$kddivafi])?$jmljjgsor[$kddivafi]:'0';
        $jmljjg[$kddivafi]=isset($jmljjg[$kddivafi])?$jmljjg[$kddivafi]:'0';
        $kgpotsortasi[$kddivafi]=isset($kgpotsortasi[$kddivafi])?$kgpotsortasi[$kddivafi]:'0';

        $persen=$jjg[$kddivafi]>0?$jmljjgsor[$kddivafi]/$jjg[$kddivafi]*100:0;
        $stream.="<tr class=rowcontent>
                    <td></td>
                    <td>".$kddivafi." - ".ucwords(strtolower($nmdivisiAfi[$kddivafi]))."</td>
                    <td align=right>".number_format($netto[$kddivafi])."</td> 
                    <td align=right>".number_format($jjg[$kddivafi])."</td>
                    <td align=right>".number_format($bjr[$kddivafi],2)."</td>    
                    <td align=right>".number_format($jmljjgsor[$kddivafi])."</td> 
                    <td align=right>".number_format($persen,2)."</td>
                    <td align=right>".($jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['B']/$jmljjgsor[$kddivafi]*100,2):0)."</td>    
                    <td align=right>".($jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['A']/$jmljjgsor[$kddivafi]*100,2):0)."</td>    
                    <td align=right>".($jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['C']/$jmljjgsor[$kddivafi]*100,2):0)."</td>    
                    <td align=right>".($jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['D']/$jmljjgsor[$kddivafi]*100,2):0)."</td>  
                    <td align=right>".($jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['E']/$jmljjgsor[$kddivafi]*100,2):0)."</td>";

        $b=$jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['B']/$jmljjgsor[$kddivafi]*100,2):0;
        $a=$jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['A']/$jmljjgsor[$kddivafi]*100,2):0;
        $c=$jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['C']/$jmljjgsor[$kddivafi]*100,2):0;
        $d=$jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['D']/$jmljjgsor[$kddivafi]*100,2):0;
        $e=$jmljjgsor[$kddivafi]>0?number_format($sor[$kddivafi]['E']/$jmljjgsor[$kddivafi]*100,2):0;

        $tsor5=$b+$a+$c+$d+$e;
        $ripe=100-$tsor5;
        $totalsor1=$ripe+$tsor5;
        //$tsor=
        $stream.="
                                <td align=right>".number_format($ripe,2)."</td>
                                <td align=right>".number_format($totalsor1,2)."</td>    
                                <td align=right>".($jmljjg[$kddivafi]>0?number_format($sor[$kddivafi]['F']/$jmljjg[$kddivafi]*100,2):0)."</td>  
                                <td align=right>".($jmljjg[$kddivafi]>0?number_format($sor[$kddivafi]['G']/$jmljjg[$kddivafi]*100,2):0)."</td>";
        $stream.="
                                <td align=right>".($netto[$kddivafi]>0?number_format($kgpotsortasi[$kddivafi]/$netto[$kddivafi]*100,2):0)."</td>
                                <td align=right>".number_format($kgpotsortasi[$kddivafi])."</td>
                                <td align=right>".number_format($netto[$kddivafi]-$kgpotsortasi[$kddivafi])."</td>
                                <td></td>
                                ";
        $stream.="                
                        </tr>";

        $tnettoafi+=$netto[$kddivafi];
        $tjjgafi+=$jjg[$kddivafi];
        $tjmljjgsorafi+=$jmljjgsor[$kddivafi];
        $tkgpotsortasiafi+=$kgpotsortasi[$kddivafi];
        //$tnormalafi+=$nettoafi[$kddivafi]-$kgpotsortasiafi[$kddivafi];     
        $tnormalafi+=$netto[$kddivafi]-$kgpotsortasi[$kddivafi];    
    }
}
else
{
}
 


$tbjrafi=$tjjgafi>0?$tnettoafi/$tjjgafi:0;
$tpersenafi=$tjjgafi>0?$tjmljjgsorafi/$tjjgafi*100:0;
$tpersenpenafi=$tnettoafi>0?$tkgpotsortasiafi/$tnettoafi*100:0;




$stream.="<tr class=rowcontent>
    <td align=center colspan=2><b>Total</b></td>        
    <td align=right><b>".number_format($tnettoafi)."</b></td>
    <td align=right><b>".number_format($tjjgafi)."</b></td>    
    <td align=right><b>".number_format($tbjrafi,2)."</b></td>    
    <td align=right><b>".number_format($tjmljjgsorafi)."</b></td>    
    <td align=right><b>".number_format($tpersenafi,2)."</b></td>";
    
    for($i=1;$i<=9;$i++)
    {
        $stream.="<td align=right><b></b></td>";  
    }
$stream.="
    <td align=right><b>".number_format($tpersenpenafi,2)."</b></td>    
    <td align=right><b>".number_format($tkgpotsortasiafi)."</b></td>  
    <td align=right><b>".number_format($tnormalafi)."</b></td>
    <td></td>
        ";
$stream.="</tr>";



#########################################################################
##EXTERNAL#####
########################################################################


                
$stream.="<tr class=rowcontent>
    <td align=center bgcolor=#00CCFF><b>3</b></td>        
    <td colspan=19 bgcolor=#00CCFF><b>EXTERNAL</b></td>
</tr>";     

$cust=isset($cust)?$cust:'';
if(is_array($cust)){
    $tnettoext=$tjjgext=$tjmljjgsorext=$tkgpotsortasiext=$tnormalext=0;
	foreach($cust as $kdcust)
	{
            $optnamacostumer[$kdcust]=isset($optnamacostumer[$kdcust])?$optnamacostumer[$kdcust]:'0';
            $netto[$kdcust]=isset($netto[$kdcust])?$netto[$kdcust]:'0';
            $jjg[$kdcust]=isset($jjg[$kdcust])?$jjg[$kdcust]:'0';
            $bjr[$kdcust]=isset($bjr[$kdcust])?$bjr[$kdcust]:'0';
            $jmljjgsor[$kdcust]=isset($jmljjgsor[$kdcust])?$jmljjgsor[$kdcust]:'0';
            $jmljjg[$kdcust]=isset($jmljjg[$kdcust])?$jmljjg[$kdcust]:'0';
            $kgpotsortasi[$kdcust]=isset($kgpotsortasi[$kdcust])?$kgpotsortasi[$kdcust]:'0';
            
            
		$persen=$jjg[$kdcust]>0?$jmljjgsor[$kdcust]/$jjg[$kdcust]*100:0;
		$stream.="<tr class=rowcontent>
					<td></td>
					<td>".$optnamacostumer[$kdcust]."</td>
					<td align=right>".number_format($netto[$kdcust])."</td> 
					<td align=right>".number_format($jjg[$kdcust])."</td>
					<td align=right>".number_format($bjr[$kdcust],2)."</td>    
					<td align=right>".number_format($jmljjgsor[$kdcust])."</td> 
					<td align=right>".number_format($persen,2)."</td>
					<td align=right>".($jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['B']/$jmljjgsor[$kdcust]*100,2):0)."</td>    
					<td align=right>".($jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['A']/$jmljjgsor[$kdcust]*100,2):0)."</td>    
					<td align=right>".($jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['C']/$jmljjgsor[$kdcust]*100,2):0)."</td>    
					<td align=right>".($jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['D']/$jmljjgsor[$kdcust]*100,2):0)."</td>  
					<td align=right>".($jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['E']/$jmljjgsor[$kdcust]*100,2):0)."</td>";
		
		$b=$jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['B']/$jmljjgsor[$kdcust]*100,2):0;
		$a=$jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['A']/$jmljjgsor[$kdcust]*100,2):0;
		$c=$jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['C']/$jmljjgsor[$kdcust]*100,2):0;
		$d=$jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['D']/$jmljjgsor[$kdcust]*100,2):0;
		$e=$jmljjgsor[$kdcust]>0?number_format($sor[$kdcust]['E']/$jmljjgsor[$kdcust]*100,2):0;
		
		$tsor5=$b+$a+$c+$d+$e;
		$ripe=100-$tsor5;
		$totalsor1=$ripe+$tsor5;
		//$tsor=
		$stream.="
					<td align=right>".number_format($ripe,2)."</td>
					<td align=right>".number_format($totalsor1,2)."</td>    
					<td align=right>".($jmljjg[$kdcust]>0?number_format($sor[$kdcust]['F']/$jmljjg[$kdcust]*100,2):0)."</td>  
					<td align=right>".($jmljjg[$kdcust]>0?number_format($sor[$kdcust]['G']/$jmljjg[$kdcust]*100,2):0)."</td>";
		$stream.="
					<td align=right>".($netto[$kdcust]>0?number_format($kgpotsortasi[$kdcust]/$netto[$kdcust]*100,2):0)."</td>
					<td align=right>".number_format($kgpotsortasi[$kdcust])."</td>
					<td align=right>".number_format($netto[$kdcust]-$kgpotsortasi[$kdcust])."</td>
					<td></td>
					";
		$stream.="                
				</tr>";
		
		$tnettoext+=$netto[$kdcust];
		$tjjgext+=$jjg[$kdcust];
		$tjmljjgsorext+=$jmljjgsor[$kdcust];
		$tkgpotsortasiext+=$kgpotsortasi[$kdcust];
		//$tnormalext+=$nettoext[$kdcust]-$kgpotsortasiext[$kdcust];     
		$tnormalext+=$netto[$kdcust]-$kgpotsortasi[$kdcust];    
	}
}
else{
  
}

$tjjgext=isset($tjjgext)?$tjjgext:'0';
$tnettoext=isset($tnettoext)?$tnettoext:'0';
$tjmljjgsorext=isset($tjmljjgsorext)?$tjmljjgsorext:'0';
$tkgpotsortasiext=isset($tkgpotsortasiext)?$tkgpotsortasiext:'0';
$tnormalext=isset($tnormalext)?$tnormalext:'0';

$tbjrext=$tjjgext>0?$tnettoext/$tjjgext:0;
$tpersenext=$tjjgext>0?$tjmljjgsorext/$tjjgext*100:0;
$tpersenpenext=$tnettoext>0?$tkgpotsortasiext/$tnettoext*100:0;




$stream.="<tr class=rowcontent>
    <td align=center colspan=2><b>Total</b></td>        
    <td align=right><b>".number_format($tnettoext)."</b></td>
    <td align=right><b>".number_format($tjjgext)."</b></td>    
    <td align=right><b>".number_format($tbjrext,2)."</b></td>    
    <td align=right><b>".number_format($tjmljjgsorext)."</b></td>    
    <td align=right><b>".number_format($tpersenext,2)."</b></td>";
    for($i=1;$i<=9;$i++)
    {
        $stream.="<td align=right><b></b></td>";  
    }
$stream.="
    <td align=right><b>".number_format($tpersenpenext,2)."</b></td>    
    <td align=right><b>".number_format($tkgpotsortasiext)."</b></td>  
    <td align=right><b>".number_format($tnormalext)."</b></td>
    <td></td>
        ";
$stream.="</tr>";



#####################################################################
#####################################################################
#####################################################################

$gtotnetto=$tnettoint+$tnettoafi+$tnettoext;
$gtotjjg=$tjjgint+$tjjgafi+$tjjgext;
$gtotbjr=$gtotjjg>0?$gtotnetto/$gtotjjg:0;
$gtotjmljjgsor=$tjmljjgsorint+$tjmljjgsorafi+$tjmljjgsorext;

$gtotpersen=$gtotjjg>0?$gtotjmljjgsor/$gtotjjg*100:0;

$gtotkgpotsor=$tkgpotsortasiint+$tkgpotsortasiafi+$tkgpotsortasiext;
$gtotnormal=$gtotnetto-$gtotkgpotsor;
 
$gtotpersenpen=$gtotnetto>0?$gtotkgpotsor/$gtotnetto*100:0;
$stream.="<tr class=rowcontent>";
	$stream.="
            <thead>
            <tr>
                <td align=center colspan=2>Total</td>
                <td align=right>".number_format($gtotnetto,2)."</td>
                <td align=right>".number_format($gtotjjg,2)."</td>
                <td align=right>".number_format($gtotbjr,2)."</td>
                <td align=right>".number_format($gtotjmljjgsor,2)."</td>
                <td align=right>".number_format($gtotpersen,2)."</td>";    
                for($i=1;$i<=9;$i++)
                {
                    $stream.="<td align=right><b></b></td>";  
                }
        $stream.="
                <td align=right>".number_format($gtotpersenpen,2)."</td>
                <td align=right>".number_format($gtotkgpotsor,2)."</td>   
                <td align=right>".number_format($gtotnormal,2)."</td> 
                <td align=right></td>   
                ";
                
        $stream.="        
            </tr>
	</tbody></table>";


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
######HTML
	case 'preview':
		echo $stream;
    break;

######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_sortasi_".$tglSkrg;
		if(strlen($stream)>0)
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
			if(!fwrite($handle,$stream))
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
	
	
	
###############	
#panggil PDFnya
###############
	
	default:
	break;
}

?>