<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
error_reporting(0);

$proses = checkPostGet('proses','');
$kdUnit = checkPostGet('kdUnit','');
$periode = checkPostGet('periode','');
$periode1 = checkPostGet('periode1','');
$status = checkPostGet('status','');
$kodevhc = checkPostGet('kodevhc','');
$kodebarang = checkPostGet('kodebarang','');
$tipe = checkPostGet('tipe','');
$tab="";
$optNmSat=makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmJenis=  makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');

if($periode>$periode1)
{
	exit("Error: Please Check Periode".$periode);
}
if($kdUnit=='')
{
    exit("Error: Organizer core required");
}
if($kdUnit!='')
{
    $where="  kodetraksi='".$kdUnit."'";
}
if($periode!='')
{
    $where.=" and substr(tanggal,1,7) between '".$periode."' and '".$periode1."' ";
}
// list kendaraan
$sData="select distinct kodevhc from ".$dbname.".log_zbahan_kendaraan_vw where ".$where." order by kodevhc asc";
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while($rList=$qData->fetch()){
    $dataVhc[$rList['kodevhc']]=$rList['kodevhc'];
}

// $sData="select distinct kodevhc from ".$dbname.".vhc_runht where jenisbbm='351010004' and  substr(tanggal,1,7) between '".$periode."' and '".$periode1."' order by kodevhc asc";
// $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
// $qData->setFetchMode(PDO::FETCH_ASSOC);
// while($rList=$qData->fetch()){
    // $dataVhc[$rList['kodevhc']]=$rList['kodevhc'];
// }



//print_r($dataVhc);
// jumlah pakai
$sJmlh="select a.kodevhc,jenisbbm,sum(a.jlhbbm) as jlbbm from  ".$dbname.". vhc_runht a 
        left join ".$dbname.".vhc_5master b on a.kodevhc=b.kodevhc
        where ".$where." group by a.kodevhc,jenisbbm";
//echo $sJmlh;
$qJmlh=$owlPDO->query($sJmlh) or die(print " Gagal: ".PDOException::getMessage());
$qJmlh->setFetchMode(PDO::FETCH_ASSOC);
while($rJmlh=$qJmlh->fetch())
{
    setIt($dtJlhPakai[$rJmlh['kodevhc']][$rJmlh['jenisbbm']],0);
    $dtJlhPakai[$rJmlh['kodevhc']][$rJmlh['jenisbbm']]+= $rJmlh['jlbbm'];
    $dtBarang[$rJmlh['kodevhc']]=$rJmlh['jenisbbm'];
}

// cari induk (dz mar 26, 2012)
$sinduk="select induk from  ".$dbname.".organisasi 
        where kodeorganisasi = '".$kdUnit."' order by kodeorganisasi desc limit 1";
$qinduk=$owlPDO->query($sinduk) or die(print " Gagal: ".PDOException::getMessage());
$qinduk->setFetchMode(PDO::FETCH_ASSOC);
while($rinduk=$qinduk->fetch())
{
    $induk= $rinduk['induk'];
}
if(strlen($induk)==4){
    $sinduk2="select induk from  ".$dbname.".organisasi 
            where kodeorganisasi = '".$induk."'";
    $qinduk2=$owlPDO->query($sinduk2) or die(print " Gagal: ".PDOException::getMessage());
    $qinduk2->setFetchMode(PDO::FETCH_ASSOC);
    while($rinduk2=$qinduk2->fetch())
    {
      $induk= $rinduk2['induk'];
    }
}
//echo $induk;

// cari harga rata
$sinduk="select sum(hargarata) as hargarata,kodebarang from  ".$dbname.".log_5saldobulanan 
        where kodeorg = '".$induk."' and periode between '".$periode."' and '".$periode1."' group by kodebarang";
//echo $sinduk;
$qinduk=$owlPDO->query($sinduk) or die(print " Gagal: ".PDOException::getMessage());
$qinduk->setFetchMode(PDO::FETCH_ASSOC);
while($rinduk=$qinduk->fetch())
{
    $hargarata[$rinduk['kodebarang']]= $rinduk['hargarata'];
}
//echo $induk;

$brd=0;
$bgBelakang='';
if($proses=='excel')
{
    $brd=1;
    $bgBelakang="bgcolor=#00FF40 align=center";
    $tab="<table>
            <tr><td colspan=10 align=center>".strtoupper($_SESSION['lang']['laporanByBahan'])."</td></tr>
            <tr><td  align=left>".$_SESSION['lang']['unitkerja']." : ".$kdUnit." [".$optNm[$kdUnit]."]</td></tr>
            <tr><td  align=left>".$_SESSION['lang']['periode']." : ".$periode."</td></tr>
            <tr><td ></td><td></td></tr>
            </table>";
}

if($proses!='getDetail'){
// header
$tab.="<table cellpadding=5 cellspacing=1 border=".$brd." class=sortable>
<thead>
<tr class=rowheader>            
<th align=center ".$bgBelakang.">".$_SESSION['lang']['kodevhc']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['nopol']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['jenisvch']." - ".$_SESSION['lang']['namajenisvhc']."</th>
<th align=center width=75px ".$bgBelakang.">".$_SESSION['lang']['tahunperolehan']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['namabarang']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['satuan']."</th>

<th align=center width=70px  ".$bgBelakang.">".$_SESSION['lang']['jumlahkeluargudang']."</th>
<th align=center width=70px  ".$bgBelakang.">".$_SESSION['lang']['jmlhPakai']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['satuan']."</th>
<th align=center ".$bgBelakang.">HM / KM</th>

<th align=center ".$bgBelakang.">".$_SESSION['lang']['hargagudang']."</th>   
</tr>
</thead><tbody id=containDataStock>";
$grandTotal=0;
$cekDt=count($dataVhc);
if($cekDt!=0)
{
    // hm/km
    $sJmlh="select distinct sum(a.jumlah) as totjmhm,a.satuan,b.kodevhc,c.jenisvhc,"
            . "c.tahunperolehan from ".$dbname.".vhc_rundt a
            left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
	    left join ".$dbname.".vhc_5master c on b.kodevhc=c.kodevhc
            where substr(tanggal,1,7) between '".$periode."' and '".$periode1."' and kodetraksi like '%".substr($kdUnit,0,4)."%' group by b.kodevhc";
			//echo $sJmlh;
    $qJmlh=$owlPDO->query($sJmlh) or die(print " Gagal: ".PDOException::getMessage());
    $qJmlh->setFetchMode(PDO::FETCH_ASSOC);
    while($rJmlh=$qJmlh->fetch()){
        $dtJmlh[$rJmlh['kodevhc']]=$rJmlh['totjmhm'];
        $dtSat[$rJmlh['kodevhc']]=$rJmlh['satuan'];
        $jenisVhc[$rJmlh['kodevhc']]=$rJmlh['jenisvhc'];
        $tahunperolehan[$rJmlh['kodevhc']]=$rJmlh['tahunperolehan'];
    }

foreach($dataVhc as $listDataVhc){
    $hrgSemua=0;
    // jumlah keluar gudang + harga gudang
    $sData="select sum(jumlah) as jumlah,sum(hargatotal) as hrgTotal,kodevhc,namabarang,kodebarang,detailvhc,tahunperolehan from ".$dbname.".log_zbahan_kendaraan_vw where kodevhc='".$listDataVhc."' and substr(tanggal,1,7) between '".$periode."' and '".$periode1."' group by kodevhc,kodebarang ";
    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
    $qData->setFetchMode(PDO::FETCH_ASSOC);
    while($rData=$qData->fetch()){
        $jenisVhc[$listDataVhc]=isset($jenisVhc[$listDataVhc])?$jenisVhc[$listDataVhc]:'';
        $nmJenis[$jenisVhc[$listDataVhc]]=isset($nmJenis[$jenisVhc[$listDataVhc]])?$nmJenis[$jenisVhc[$listDataVhc]]:'';
        $tahunperolehan[$listDataVhc]=isset($tahunperolehan[$listDataVhc])?$tahunperolehan[$listDataVhc]:'';
        $no+=1;
        
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=left>".$rData['kodevhc']."</td>";
        $tab.="<td align=left>".getNopol($rData['kodevhc'])."</td>";
        $tab.="<td align=left>".$jenisVhc[$listDataVhc]." - ".$rData['detailvhc']."</td>";
        $tab.="<td align=center>".$rData['tahunperolehan']."</td>";
        $tab.="<td>".$rData['namabarang']."</td>";
        $arrd="##kodevhc_".$no."##periode_".$no."##status_".$no."";
        $arrb="##kodevhc_".$no."##periode_".$no."##statusb_".$no."";
		setIt($dtJlhPakai[$rData['kodevhc']][$rData['kodebarang']],0);
		setIt($hargarata[$rData['kodebarang']],0);
		setIt($dtJmlh[$rData['kodevhc']],0);
		setIt($dtSat[$rData['kodevhc']],'');
		$tab.="<td>".$optNmSat[$rData['kodebarang']]."</td>";
        if($proses!='excel'){
            $tab.="<td align=right onclick=\"previewDetail('".$rData['kodevhc']."','".$periode."','".$periode1."','0','".$rData['kodebarang']."','".$kdUnit."','event','html')\" style='cursor:pointer;' title='get detail jlh keluar dari gudang'>".$rData['jumlah']."</td>";
            $tab.="<td align=right onclick=\"previewDetail('".$rData['kodevhc']."','".$periode."','".$periode1."','1','".$rData['kodebarang']."','".$kdUnit."','event','html')\" style='cursor:pointer;' title='get detail pemakaian'>".$dtJlhPakai[$rData['kodevhc']][$rData['kodebarang']]."</td>";
        }else{
            $tab.="<td align=right>".number_format($rData['jumlah'],0)."</td>";
            $tab.="<td>".$dtJlhPakai[$rData['kodevhc']][$rData['kodebarang']]."</td>";
        }
        
		$tab.="<td>".$dtSat[$rData['kodevhc']]."</td>";
        $tab.="<td align=right>".number_format($dtJmlh[$rData['kodevhc']])."</td>";

        $hrgTotal=$rData['hrgTotal'];
        $tab.="<td align=right>".number_format($hrgTotal,0)."</td>";
        $tab.="</tr>";
        $hrgSemua+=$hrgTotal;
    }
	
	$sData="select sum(jlhbbm) as jumlah,kodevhc,jenisbbm as kodebarang from ".$dbname.".vhc_runht where jenisbbm not in (select distinct kodebarang from ".$dbname.".log_zbahan_kendaraan_vw where kodevhc='".$listDataVhc."' and substr(tanggal,1,7) between '".$periode."' and '".$periode1."') and jlhbbm!='' and kodevhc='".$listDataVhc."' and substr(tanggal,1,7) between '".$periode."' and '".$periode1."' group by kodevhc,kodebarang";
    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
    $qData->setFetchMode(PDO::FETCH_ASSOC);
    while($rData=$qData->fetch()){
        $jenisVhc[$listDataVhc]=isset($jenisVhc[$listDataVhc])?$jenisVhc[$listDataVhc]:'';
        $nmJenis[$jenisVhc[$listDataVhc]]=isset($nmJenis[$jenisVhc[$listDataVhc]])?$nmJenis[$jenisVhc[$listDataVhc]]:'';
        $tahunperolehan[$listDataVhc]=isset($tahunperolehan[$listDataVhc])?$tahunperolehan[$listDataVhc]:'';
        $no+=1;
        
		$optdetailvhc=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',"kodevhc='".$listDataVhc."'");
		$optbbm=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$rData['kodebarang']."'");
		
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=left>".$rData['kodevhc']."</td>";
        $tab.="<td align=left>".getNopol($rData['kodevhc'])."</td>";
        $tab.="<td align=left>".$jenisVhc[$listDataVhc]." - ".$optdetailvhc[$listDataVhc]."</td>";
        $tab.="<td align=center>".$rData['tahunperolehan']."</td>";
        $tab.="<td>".$optbbm[$rData['kodebarang']]."</td>";
        $arrd="##kodevhc_".$no."##periode_".$no."##status_".$no."";
        $arrb="##kodevhc_".$no."##periode_".$no."##statusb_".$no."";
		setIt($dtJlhPakai[$rData['kodevhc']][$rData['kodebarang']],0);
		setIt($hargarata[$rData['kodebarang']],0);
		setIt($dtJmlh[$rData['kodevhc']],0);
		setIt($dtSat[$rData['kodevhc']],'');
		$tab.="<td>".$optNmSat[$rData['kodebarang']]."</td>";
        if($proses!='excel'){
            $tab.="<td align=right>0</td>";
            $tab.="<td align=right onclick=\"previewDetail('".$rData['kodevhc']."','".$periode."','".$periode1."','1','".$rData['kodebarang']."','".$kdUnit."','event','html')\" style='cursor:pointer;' title='get detail pemakaian'>".$dtJlhPakai[$rData['kodevhc']][$rData['kodebarang']]."</td>";
        }else{
            $tab.="<td align=right>".number_format($rData['jumlah'],0)."</td>";
            $tab.="<td>".$dtJlhPakai[$rData['kodevhc']][$rData['kodebarang']]."</td>";
        }
        
		$tab.="<td>".$dtSat[$rData['kodevhc']]."</td>";
        $tab.="<td align=right>".number_format($dtJmlh[$rData['kodevhc']])."</td>";

        $hrgTotal=$rData['hrgTotal'];
        $tab.="<td align=right>".number_format($hrgTotal,0)."</td>";
        $tab.="</tr>";
        $hrgSemua+=$hrgTotal;
    }
	
	
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td colspan=9 align=left><b>".$_SESSION['lang']['subtotal']." ".$listDataVhc."</b></td>";
    $tab.="<td align=right colspan=2><b>".number_format($hrgSemua,0)."</b></td>";
    $tab.="</tr>";
    $grandTotal+=$hrgSemua;
}
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=9 align=left><b>".$_SESSION['lang']['grnd_total']."</b></td>";
$tab.="<td colspan=2 align=right><b>".number_format($grandTotal,0)."</b></td>";
$tab.="</tr>";
}
else
{
    $tab.="<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
}
$tab.="</tbody></table>";
}
switch($proses){
	case'preview':
        echo $tab;
		
	break;
        case'getDetail':
			//$tab.="<div style=\"overflow: auto; height:400px;\">";
			if ($tipe == 'excel') {
				$tab.="<table class=sortable cellspacing=1 border=1>";
			} else {
				$tab.= "<table class=sortable cellpadding=5 cellspacing=1>";
			}
			
                $sData="select distinct * from ".$dbname.".log_zbahan_kendaraan_vw where kodevhc='".$kodevhc."' and substr(tanggal,1,7) between '".$periode."' and '".$periode1."' 
                    and kodebarang='".$kodebarang."'";
                
				$tab.="<img onclick=\"getdetailexcel('".$kodevhc."','".$periode."','".$periode1."','0','".$kodebarang."','".$kdUnit."','event','excel')\" src=images/excel.jpg class=resicon title='MS.Excel'>";

                
                $tab.="</br>";

                $tab.= "<thead>

                        <tr class=rowheader> 
                        <th align=center colspan=4><b>Sumber Transaksi Gudang</b></th>    
                        <th align=center colspan=5><b>Sumber Transaksi Traksi</b></th>
                        </tr>
                        <tr class=rowheader> 

					   <th align=center>".$_SESSION['lang']['notransaksi']."</th>
					   <th align=center>".$_SESSION['lang']['sloc']."</th>";
                $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>
					   <th align=center>".$_SESSION['lang']['jumlah']."<br>BBM</th>
                        <th>".$_SESSION['lang']['notransaksi']."</th>
                        <th>".$_SESSION['lang']['tanggal']."</th>";
                $tab.="	<th>".$_SESSION['lang']['jumlah']."</th>
                        <th>".$_SESSION['lang']['satuan']."</th>
                        <th>".$_SESSION['lang']['jumlah']."<br>BBM</th>
					   </tr></thead><tbody>";
                
               $sData="select a.notransaksi, a.tanggal , a.kodegudang, a.jumlah, b.notransaksi as notrans, b.tanggal as tglGd, b.jlhbbm from ".$dbname.".log_zbahan_kendaraan_vw a LEFT JOIN ".$dbname. ".vhc_runht b ON b.kodevhc=a.kodevhc AND b.tanggal=a.tanggal AND b.jenisbbm = a.kodebarang where a.kodevhc='".$kodevhc."' and substr(a.tanggal,1,7) between '".$periode."' and '".$periode1."'
                    and kodebarang='".$kodebarang."'";

                $qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
                $qData->setFetchMode(PDO::FETCH_ASSOC);
                while ($rData = $qData->fetch()) {
                    $newdata[$rData['tanggal']]['kiri'][] = $rData;
                }

                $ssl = "SELECT notransaksi,tanggal,jlhbbm FROM $dbname.vhc_runht WHERE tanggal NOT IN ('" . implode("','", $exist) . "') AND left(tanggal,7) between '" . $periode . "' and '" . $periode1 . "' and jenisbbm='" . $kodebarang . "' and kodevhc = '$kodevhc'";
                $outstand = fetchData($ssl);


                foreach ($outstand as $key => $value) {
                    $newdata[$value['tanggal']]['kanan'][] = $value;
                }

                ksort($newdata);
                
               
                foreach($newdata as $tgl => $paham){
                    $largest = count($paham['kiri']) > count($paham['kanan']) ? count($paham['kiri']) : count($paham['kanan']); 
                    

                    for($i=0;$i<$largest; $i++){



                    $sGet = "select distinct sum(jumlah) as jumlah,satuan from " . $dbname . ".vhc_rundt where notransaksi='" . $paham['kanan'][$i]['notransaksi'] . "' group by notransaksi";
                    $qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
                    $qGet->setFetchMode(PDO::FETCH_ASSOC);
                    $rGet = $qGet->fetch();

                    $tab.="<tr class=rowcontent>";
                    $tab.="<td>".$paham['kiri'][$i]['notransaksi']."</td>";
                    $tab.="<td>".$paham['kiri'][$i]['kodegudang']." - ".$optNm[$paham['kiri'][$i]['kodegudang']]."</td>";
                    $tab.="<td>".tanggalnormal($tgl)."</td>";
                    $tab.="<td  align=right>".$paham['kiri'][$i]['jumlah']."</td>";
                    $tab.="<td  align=right>". $paham['kanan'][$i]['notransaksi']."</td>";
                    $tab.="<td  align=right>".$paham['kanan'][$i]['tanggal']."</td>";
                    $tab.="<td align=right>".$rGet['jumlah']."</td>";
                    $tab.="<td align=right>".$rGet['satuan']."</td>";
                    $tab.="<td  align=right>". $paham['kanan'][$i]['jlhbbm']."</td>";
                    
                    $tab.="</tr>";
					
					@$tbbmg+= $paham['kiri'][$i]['jumlah'];
                    
                    @$tjlh += $rGet['jumlah'];
                    @$tbbm += $paham['kanan'][$i]['jlhbbm'];
                    }
                }

					$tab.="<tr class=rowcontent>";
                    $tab.="<td align=center colspan=3><b>TOTAL</b></td>";
                    $tab.="<td align=right><b>".$tbbmg."</b></td>";
                    $tab.= "<td ></td>";
                    $tab.= "<td ></td>";
                    $tab.="<td align=right><b>".$tjlh."</b></td>";
                    $tab.= "<td ></td>";
                    $tab.="<td align=right><b>".$tbbm."</b></td>";
                    $tab.="</tr>";
					
                $tab.="</tbody></table></div>";
                
                switch($tipe){
					case 'html':
						echo $tab;
					break;
					case 'excel':
						$nop_ = "Detail";
						if (strlen($tab) > 0) {
							$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
							if ($handle = opendir('tempExcel')) {
								while (false !== ($file = readdir($handle))) {
									if ($file != "." && $file != ".." && $file != "index.html") {
										@unlink('tempExcel/' . $file);
									}
								}
								closedir($handle);
							}
							$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
							if (!fwrite($handle, $tab)) {
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
				#==========
            // // }elseif($status==1){
			// 	$tab.="<img onclick=\"getdetailexcel('".$kodevhc."','".$periode."','".$periode1."','1','".$kodebarang."','".$kdUnit."','event','excel')\" src=images/excel.jpg class=resicon title='MS.Excel'>";

            //     $tab.="<thead>
			// 			<tr class=rowheader>
			// 				<td>".$_SESSION['lang']['notransaksi']."</td>
			// 				<td>".$_SESSION['lang']['tanggal']."</td>";
            //     $tab.="		<td>".$_SESSION['lang']['jumlah']."</td>
			// 				<td>".$_SESSION['lang']['satuan']."</td>
			// 				<td>".$_SESSION['lang']['jumlah']." BBM</td>
			// 			</tr></thead><tbody>";
            //     echo $sData="select distinct * from ".$dbname.".vhc_runht  where kodevhc='".$kodevhc."' and substr(tanggal,1,7) between '".$periode."' and '".$periode1."' 
            //         and jenisbbm='".$kodebarang."'";
            //    // echo $sData;
            //     $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
            //     $qData->setFetchMode(PDO::FETCH_ASSOC);
            //     while($rData=$qData->fetch()){
            //         $sGet="select distinct sum(jumlah) as jumlah,satuan from ".$dbname.".vhc_rundt where notransaksi='".$rData['notransaksi']."' 	
            //                group by notransaksi";
            //         $qGet=$owlPDO->query($sGet) or die(print " Gagal: ".PDOException::getMessage());
            //         $qGet->setFetchMode(PDO::FETCH_ASSOC);                           
            //         $rGet=$qGet->fetch();
            //         $tab.="<tr class=rowcontent>";
            //         $tab.="<td>".$rData['notransaksi']."</td>";
            //         $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
            //         $tab.="<td align=right>".$rGet['jumlah']."</td>";
            //         $tab.="<td>".$rGet['satuan']."</td>";
            //         $tab.="<td align=right>".$rData['jlhbbm']."</td>";
            //         $tab.="</tr>";
			// 		@$tjlh+=$rGet['jumlah'];
			// 		@$tbbm+=$rData['jlhbbm'];
            //     }
			// 		$tab.="<tr class=rowcontent>";
            //         $tab.="<td colspan=2><b>TOTAL</b></td>";
            //         $tab.="<td align=right><b>".$tjlh."</b></td>";
            //         $tab.="<td align=right><b></b></td>";
            //         $tab.="<td align=right><b>".$tbbm."</b></td>";
            //         $tab.="</tr>";
            //     $tab.="</tbody></table></div>";
                
			// 	switch($tipe){
			// 		case 'html':
			// 			echo $tab;
			// 		break;
			// 		case 'excel':
			// 			$nop_ = "Detail";
			// 			if (strlen($tab) > 0) {
			// 				$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
			// 				if ($handle = opendir('tempExcel')) {
			// 					while (false !== ($file = readdir($handle))) {
			// 						if ($file != "." && $file != ".." && $file != "index.html") {
			// 							@unlink('tempExcel/' . $file);
			// 						}
			// 					}
			// 					closedir($handle);
			// 				}
			// 				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
			// 				if (!fwrite($handle, $tab)) {
			// 					echo "<script language=javascript1.2>
			// 							parent.window.alert('Can't convert to excel format');
			// 							</script>";
			// 					exit;
			// 				} else {
			// 					echo "<script language=javascript1.2>
			// 							window.location='tempExcel/" . $nop_ . ".xls';
			// 							</script>";
			// 				}
			// 				fclose($handle);
			// 			}
			// 		break;
			// 	}
                
            // }
        break;
	case'pdf':
	
	 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $kdUnit;
                global $periode;
                global $rData;
                global $optNm;
                global $dataVhc;
				
			    # Alamat & No Telp
                $arrHead = setheadreport(substr($kdUnit,0,4));
				
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(110);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(110); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(110); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                
                $this->SetFont('Arial','B',12);
                    //	$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanKendAb'],'',0,'L');
                    //	$this->Ln();
                            $this->SetFont('Arial','',8);
                            $this->SetFont('Arial','U',12);
                            $this->Cell($width,$height, strtoupper($_SESSION['lang']['laporanByBahan']),0,1,'C');	
                            $this->Ln();	
                             $this->SetFont('Arial','B',6);
                            $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['unitkerja'],'',0,'L');
                            $this->Cell(5,$height,':','',0,'L');
                            $this->Cell(25/100*$width,$height,$optNm[$kdUnit],'',0,'L');
                            $this->Ln();

                            $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
                            $this->Cell(5,$height,':','',0,'L');
                            $this->Cell(25/100*$width,$height,$periode,'',0,'L');
                            $this->Ln();					
                $this->SetFont('Arial','B',7);	
                $this->SetFillColor(220,220,220);

                $this->Cell(14/100*$width,$height,$_SESSION['lang']['kodevhc'],1,0,'C',1);	
                $this->Cell(15/100*$width,$height,$_SESSION['lang']['jenisvch'].' - '.$_SESSION['lang']['namajenisvhc'],1,0,'C',1);
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['tahunperolehan'],1,0,'C',1);
                $this->Cell(22/100*$width,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);		
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['jumlahkeluargudang'],1,0,'C',1);
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['jmlhPakai'],1,0,'C',1);
                $this->Cell(11/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);		
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['hargagudang'],1,1,'C',1);
                				
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
            $pdf->AddPage();
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',7);
				if(isset($dataVhc))
                foreach($dataVhc as $listDataVhc)
                { 
                       $hrgSemua=0;
                        $sData="select sum(jumlah) as jumlah,sum(hargatotal) as hrgTotal,kodevhc,namabarang,kodebarang from ".$dbname.".log_zbahan_kendaraan_vw where kodevhc='".$listDataVhc."' and substr(tanggal,1,7) = '".$periode."' group by kodevhc,kodebarang ";
                        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                        $qData->setFetchMode(PDO::FETCH_ASSOC);
                        while($rData=$qData->fetch())
                        {                           
                            $no+=1;
							setIt($dtJlhPakai[$rData['kodevhc']][$rData['kodebarang']],0);
							setIt($hargarata[$rData['kodebarang']],0);
							setIt($optNmSat[$rData['kodebarang']],'');
							$pdf->Cell(14/100*$width,$height,$rData['kodevhc'],1,0,'C',1);	
                            $pdf->Cell(15/100*$width,$height,$jenisVhc[$listDataVhc].' - '.$nmJenis[$jenisVhc[$listDataVhc]],1,0,'C',1);	
                            $pdf->Cell(10/100*$width,$height,$tahunperolehan[$listDataVhc],1,0,'C',1);		
                                                        
                            $pdf->Cell(22/100*$width,$height,$rData['namabarang'],1,0,'L',1);		
                            $pdf->Cell(10/100*$width,$height,number_format($rData['jumlah'],0),1,0,'R',1);
                            $pdf->Cell(10/100*$width,$height,number_format($dtJlhPakai[$rData['kodevhc']][$rData['kodebarang']],0),1,0,'R',1);
                            $pdf->Cell(11/100*$width,$height,$optNmSat[$rData['kodebarang']],1,0,'C',1);
                            // $hrgTotal=$rData['jumlah']*$hargarata[$rData['kodebarang']];
                            $hrgTotal=$rData['hrgTotal'];
                            $pdf->Cell(8/100*$width,$height,number_format($hrgTotal,0),1,1,'R',1);	
                            $hrgSemua+=$hrgTotal;
                        }
                        $pdf->Cell(92/100*$width,$height,$_SESSION['lang']['subtotal']."  ".$listDataVhc,1,0,'R',1);
                        $pdf->Cell(8/100*$width,$height,number_format($hrgSemua,0),1,1,'R',1);
                        $grandTotal+=$hrgSemua;
                }
                $pdf->Cell(92/100*$width,$height,$_SESSION['lang']['grnd_total'],1,0,'R',1);
                $pdf->Cell(8/100*$width,$height,number_format($grandTotal,0),1,1,'R',1);
        $pdf->Output();
	break;
	case'excel':
	  

                    //echo "warning:".$strx;
                    //=================================================
            $tab.="Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

                    $nop_="laporan_penggunaan_bahan_".$kdUnit;
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
	
	default:
	break;
}

?>