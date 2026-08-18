<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_bar.php');
require_once ('lib/zLib.php');


$nmJenis = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');

if (isset($_GET['unit'])) {
    $param = $_GET;
    $border = 1;
} else {
    $param = $_POST;
    $border = 0;
}

if($param['jenisbbm']==''){
	exit("warning : Jenis BBM wajib diisi.");
}

$waktu = $param['tahun'];
$kodeorg = $param['unit'];
#ambil jenis-jenis VHC
$str = "select jenisvhc,namajenisvhc from " . $dbname . ".vhc_5jenisvhc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $nama[$bar->jenisvhc] = $bar->namajenisvhc;
}


    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  
echo"<link rel=stylesheet tyle=text href='style/".$gen."'>
          <script language=javascript src='js/generic.js'></script>";

#ambil Biaya real      
// $str = "select sum(jlhbbm) as jlh,kodevhc,left(tanggal,7) as periode from " . $dbname . ".vhc_runht where
       // tanggal like '" . $waktu . "%'
      // group by kodevhc,left(tanggal,7)";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while ($bar = $res->fetch()) {
    // #$real[$bar->periode][$bar->kodevhc] = $bar->jlh;
// }

// $str = "select sum(jumlah) as jlh,kodevhc,left(tanggal,7) as periode from " . $dbname . ".log_zbahan_kendaraan_vw where  kodebarang like '351%' and 
       // tanggal like '" . $waktu . "%'
      // group by kodevhc,left(tanggal,7)";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while ($bar = $res->fetch()) {
    // #$real[$bar->periode][$bar->kodevhc] = $bar->jlh;
// }

$str = "select sum(jumlah) as jlh,kodemesin,left(tanggal,7) as periode from " . $dbname . ".log_transaksi_vw where  kodebarang = '".$param['jenisbbm']."' and tanggal like '" . $waktu . "%' and kodemesin!='' group by kodemesin,left(tanggal,7)";
$res = fetchdata($str);
foreach($res as $bar){
	$real[$bar['periode']][$bar['kodemesin']] = $bar['jlh'];
}	  

#ambil hm/km real
$str = "select sum(a.jumlah) as km, b.kodevhc,left(b.tanggal,7) as periode from " . $dbname . ".vhc_rundt a
            left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
            and tanggal like '" . $waktu . "%' group by kodevhc,left(b.tanggal,7)";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $realHM[$bar->periode][$bar->kodevhc] = $bar->km;
}

#budget
$str = "SELECT a.kodevhc,
   sum(fis01) as fis01,
   sum(fis02) as fis02,
   sum(fis03) as fis03,
   sum(fis04) as fis04,
   sum(fis05) as fis05,
   sum(fis06) as fis06,
   sum(fis07) as fis07,
   sum(fis08) as fis08,
   sum(fis09) as fis09,
   sum(fis10) as fis10,
   sum(fis11) as fis11,
   sum(fis12) as fis12
    FROM " . $dbname . ".bgt_budget_detail a
   where a.kodevhc is not null and tipebudget='TRK' and tahunbudget='" . $waktu . "'
   and kodebarang = '".$param['jenisbbm']."'
   group by kodevhc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $bgtfis[$waktu . "-01"][$bar->kodevhc] = $bar->fis01;
    $bgtfis[$waktu . "-02"][$bar->kodevhc] = $bar->fis02;
    $bgtfis[$waktu . "-03"][$bar->kodevhc] = $bar->fis03;
    $bgtfis[$waktu . "-04"][$bar->kodevhc] = $bar->fis04;
    $bgtfis[$waktu . "-05"][$bar->kodevhc] = $bar->fis05;
    $bgtfis[$waktu . "-06"][$bar->kodevhc] = $bar->fis06;
    $bgtfis[$waktu . "-07"][$bar->kodevhc] = $bar->fis07;
    $bgtfis[$waktu . "-08"][$bar->kodevhc] = $bar->fis08;
    $bgtfis[$waktu . "-09"][$bar->kodevhc] = $bar->fis09;
    $bgtfis[$waktu . "-10"][$bar->kodevhc] = $bar->fis10;
    $bgtfis[$waktu . "-11"][$bar->kodevhc] = $bar->fis11;
    $bgtfis[$waktu . "-12"][$bar->kodevhc] = $bar->fis12;
}
#ambil  budget fisik kendaraan
$str = "SELECT a.kodevhc,
   sum(jam01) as jam01,
   sum(jam02) as jam02,
   sum(jam03) as jam03,
   sum(jam04) as jam04,
   sum(jam05) as jam05,
   sum(jam06) as jam06,
   sum(jam07) as jam07,
   sum(jam08) as jam08,
   sum(jam09) as jam09,
   sum(jam10) as jam10,
   sum(jam11) as jam11,
   sum(jam12) as jam12
    FROM " . $dbname . ".bgt_vhc_jam a  where tahunbudget='" . $waktu . "'
   group by a.kodevhc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $bgtjam[$waktu . "-01"][$bar->kodevhc] = $bar->jam01;
    $bgtjam[$waktu . "-02"][$bar->kodevhc] = $bar->jam02;
    $bgtjam[$waktu . "-03"][$bar->kodevhc] = $bar->jam03;
    $bgtjam[$waktu . "-04"][$bar->kodevhc] = $bar->jam04;
    $bgtjam[$waktu . "-05"][$bar->kodevhc] = $bar->jam05;
    $bgtjam[$waktu . "-06"][$bar->kodevhc] = $bar->jam06;
    $bgtjam[$waktu . "-07"][$bar->kodevhc] = $bar->jam07;
    $bgtjam[$waktu . "-08"][$bar->kodevhc] = $bar->jam08;
    $bgtjam[$waktu . "-09"][$bar->kodevhc] = $bar->jam09;
    $bgtjam[$waktu . "-10"][$bar->kodevhc] = $bar->jam10;
    $bgtjam[$waktu . "-11"][$bar->kodevhc] = $bar->jam11;
    $bgtjam[$waktu . "-12"][$bar->kodevhc] = $bar->jam12;
}

$str = "select kodevhc,jenisvhc,tahunperolehan,nopol,detailvhc from " . $dbname . ".vhc_5master where kodetraksi like '" . $kodeorg . "%' order by jenisvhc asc, kodevhc asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $kodevhc[] = $bar->kodevhc;
    $jenisvhc[$bar->kodevhc] = $bar->jenisvhc;
    $tahunperolehan[$bar->kodevhc] = $bar->tahunperolehan;
	$nopol[$bar->kodevhc] = $bar->nopol;
	$detailvhc[$bar->kodevhc] = $bar->detailvhc;
}

if($_SESSION['language']=='EN'){
  $cap="Fuel Usage Ratio, Unit : " . $kodeorg . " " . $_SESSION['lang']['tahun'] . " : " . substr($waktu, 0, 4);
}else{
  $cap="Rasio Pemakaian BBM, Unit : " . $kodeorg . " " . $_SESSION['lang']['tahun'] . " : " . substr($waktu, 0, 4);
}


$spanbulan=8;
$spanaktual=4;

$tab ="                  
                <table class=sortable cellpadding=5 cellspacing=1 border=" . $border . " >
               <thead><tr class=rowheader>
               <th rowspan=3 align=center>" . $_SESSION['lang']['nourut'] . "</th>
               <th rowspan=3 align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
               <th rowspan=3 align=center>" . $_SESSION['lang']['nopol'] . "</th>
               <th rowspan=3 align=center>" . $_SESSION['lang']['keterangan'] . "</th>
               <th rowspan=3 align=center>" . $_SESSION['lang']['jenis'] . "</th>
               <th rowspan=3 align=center>" . $_SESSION['lang']['namajenisvhc'] . "</th>
               <th rowspan=3 align=center width=55px>" . $_SESSION['lang']['tahunperolehan'] . "</th>
                        
               <th colspan=".$spanbulan." align=center>Jan " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Feb " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Mar " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Apr " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Mei " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Jun " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Jul " . substr($waktu, 0, 4) . "</th>    
               <th colspan=".$spanbulan." align=center>Aug " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Sep " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Okt " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Nop " . substr($waktu, 0, 4) . "</th>
               <th colspan=".$spanbulan." align=center>Des " . substr($waktu, 0, 4) . "</th>    
               <th colspan=".$spanbulan." align=center>Total " . substr($waktu, 0, 4) . "</th>
               </tr>";
			   
			   
			  
			  
               
			   
			   $tab.="<tr class=rowheader>";
			    for($i=1;$i<=13;$i++){
					$tab.="  <th colspan=".$spanaktual." align=center>".$_SESSION['lang']['aktual']."</th>
					<th colspan=".$spanaktual." align=center>Budget</th>";
				}
					$tab.="</tr>";
               $tab.=" <tr class=rowheader>";
			    for($i=1;$i<=13;$i++){
                  $tab.="
					<th align=center>Ltr</th>
					<th align=center>HM or KM</th>
					<th align=center>Sat/Ltr</th>
					<th align=center>Ltr/Sat</th>
					<th align=center>Ltr</th>
					<th align=center>HM or KM</th>
					<th align=center>Sat/Ltr</th>
					<th align=center>Ltr/Sat</th>
					";

				}
			    $tab.="</tr>";
				
			  
               $tab.="</thead>
               <tbody>";
$no = 0;
foreach ($kodevhc as $key => $val) {
    $no+=1;
    $treal = 0;
    $trealHM = 0;
    $tbgtfis = 0;
    $tbgtjam = 0;

    $tab.="<tr class=rowcontent>
				<td  align=center>" . $no . "</td>
				<td>" . $val . "</td>
				<td>" . $nopol[$val] . "</td>
				<td>" . $detailvhc[$val] . "</td>
				<td align=center>" . $jenisvhc[$val] . "</td>
				<td>" . $nmJenis[$jenisvhc[$val]] . "</td>
				<td align=center>" . $tahunperolehan[$val] . "</td>";
    for ($kk = 1; $kk <= 12; $kk++) {
        if ($kk < 10)
            $zz = $waktu . "-0" . $kk;
        else
            $zz = $waktu . "-" . $kk;
        $color = 'bgcolor=green';
        if (@($realHM[$zz][$val] / $real[$zz][$val]) < @($bgtjam[$zz][$val] / $bgtfis[$zz][$val]))
            $color = 'bgcolor=red';

        setIt($real[$zz][$val], 0);
        setIt($realHM[$zz][$val], 0);
        setIt($bgtjam[$zz][$val], 0);
        setIt($bgtfis[$zz][$val], 0);
        $tab.="<td align=right>" . numb_format($real[$zz][$val], 2) . "</td>
			   <td align=right>" . numb_format($realHM[$zz][$val], 2) . "</td>
			   <td " . $color . "  align=right>" . @numb_format($realHM[$zz][$val] / $real[$zz][$val], 2) . "</td>
			   <td " . $color . "  align=right>" . @numb_format( $real[$zz][$val]/$realHM[$zz][$val], 2) . "</td>
			   <td align=right>" . numb_format($bgtfis[$zz][$val], 2) . "</td>
			   <td align=right>" . numb_format($bgtjam[$zz][$val], 2) . "</td>
			   <td align=right bgcolor=#dedede>" . @numb_format($bgtjam[$zz][$val] / $bgtfis[$zz][$val], 2) . "</td>
			   <td align=right bgcolor=#dedede>" . @numb_format($bgtfis[$zz][$val]/$bgtjam[$zz][$val] , 2) . "</td>";
        $treal+=$real[$zz][$val];
        $trealHM+=$realHM[$zz][$val];
        $tbgtfis+=$bgtfis[$zz][$val];
        $tbgtjam+=$bgtjam[$zz][$val];
		
		@$trealx[$zz]+=$real[$zz][$val];
        @$trealHMx[$zz]+=$realHM[$zz][$val];
        @$tbgtfisx[$zz]+=$bgtfis[$zz][$val];
        @$tbgtjamx[$zz]+=$bgtjam[$zz][$val];
		
    }
    #total
    $color = 'bgcolor=green';
    if (@($trealHM / $treal) < @($tbgtjam / $tbgtfis)) {
        $color = 'bgcolor=red';
    }
    $tab.="<td align=right>" . numb_format($treal, 2) . "</td>
			<td align=right>" . numb_format($trealHM, 2) . "</td>
			<td " . $color . "  align=right>" . @numb_format($trealHM / $treal, 2) . "</td>
			<td " . $color . "  align=right>" . @numb_format($treal/$trealHM, 2) . "</td>
			<td align=right>" . numb_format($tbgtfis, 2) . "</td>
			<td align=right>" . numb_format($tbgtjam, 2) . "</td>
			<td align=right bgcolor=#dedede>" . @numb_format($tbgtjam / $tbgtfis, 2) . "</td>
			<td align=right bgcolor=#dedede>" . @numb_format($tbgtfis/$tbgtjam, 2) . "</td>
			";
    $tab.="</tr>";
	
	$trealz+=$treal;
	$trealHMz+=$trealHM;
	$tbgtfisz+=$tbgtfis;
	$tbgtjamz+=$tbgtjam;
	
}
	$tab.="<tr class=rowcontent>";
	$tab.="<td colspan=7 align=center>TOTAL</td>";
	for ($kk = 1; $kk <= 12; $kk++) {
        if ($kk < 10)
            $zz = $waktu . "-0" . $kk;
        else
            $zz = $waktu . "-" . $kk;
		
        $tab.="<td align=right>" . numb_format($trealx[$zz], 2) . "</td>
			   <td align=right>" . numb_format($trealHMx[$zz], 2) . "</td>
			   <td align=right>" . @numb_format($trealHMx[$zz] / $trealx[$zz], 2) . "</td>
			   <td align=right>" . @numb_format( $trealx[$zz]/$trealHMx[$zz], 2) . "</td>
			   <td align=right>" . numb_format($tbgtfisx[$zz], 2) . "</td>
			   <td align=right>" . numb_format($tbgtjamx[$zz], 2) . "</td>
			   <td align=right bgcolor=#dedede>" . @numb_format($tbgtjamx[$zz]/ $tbgtfisx[$zz], 2) . "</td>
			   <td align=right bgcolor=#dedede>" . @numb_format($tbgtfisx[$zz]/$tbgtjamx[$zz] , 2) . "</td>";
    }
	$tab.="<td align=right>" . numb_format($trealz, 2) . "</td>
			<td align=right>" . numb_format($trealHMz, 2) . "</td>
			<td " . $color . "  align=right>" . @numb_format($trealHMz / $trealz, 2) . "</td>
			<td " . $color . "  align=right>" . @numb_format($trealz/$trealHMz, 2) . "</td>
			<td align=right>" . numb_format($tbgtfisz, 2) . "</td>
			<td align=right>" . numb_format($tbgtjamz, 2) . "</td>
			<td align=right bgcolor=#dedede>" . @numb_format($tbgtjamz / $tbgtfisz, 2) . "</td>
			<td align=right bgcolor=#dedede>" . @numb_format($tbgtfisz/$tbgtjamz, 2) . "</td>
			";
			
	$tab.="</tr>";

$tab.= "</tbody><tfoot>
                </tfoot></table>";
if (isset($_GET['unit'])) {
    $nop_ = "Ratio_BBM";
    if (strlen($tab) > 0) {
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
} else {
    echo $tab;
}

function numb_format($a,$d=0){
	$n = hidezerodecimal(fixnan($a),$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	
	
	return $n;
}
?>