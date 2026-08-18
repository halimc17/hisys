<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$tgl1  = tanggalsystem(checkPostGet('tgl1', ''));
$tgl2  = tanggalsystem(checkPostGet('tgl2', ''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$divisi = checkPostGet('divisi', '');

$namaOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$rangetanggal = rangeTanggal($tgl1, $tgl2);
$tglTerakhir = end($rangetanggal);
reset($rangetanggal);

if ($kdorg == '') {
    echo "Warning: Unit tidak boleh kosong";
    exit;
}

if (($tgl1 == '') or ($tgl2 == '')) {
    echo "Warning: Tanggal tidak boleh kosong";
    exit;
} elseif ($tgl1 > $tgl2) {
    echo "Warning: Tanggal pertama tidak boleh lebih besar dari tanggal kedua";
    exit;
}

#bentuk data blok dari rekap panen
$wherediv = "";
if ($divisi == '') {
    $wherediv = " and substr(divisi,1,4)='" . $kdorg . "' ";
} else {
    $wherediv = " and divisi = '" . $divisi . "' ";
}

$str = "select distinct(blok) as blok , divisi from " . $dbname . ".kebun_rekappnn_vw where 1=1 " . $wherediv . " and jjgpanen > 0 order by blok asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kdblok[$bar['blok']] = $bar['blok'];
    $kddivisi[$bar['divisi']] = $bar['divisi'];

    $listblok[$bar['divisi']][$bar['blok']] = $bar['blok'];
}


#bentuk data blok dari spb
$wherediv = "";
if ($divisi == '') {
    $wherediv = " and substr(divisi,1,4)='" . $kdorg . "' ";
} else {
    $wherediv = " and divisi = '" . $divisi . "'";
}
$str = "select distinct(indukblok) as indukblok,divisi from " . $dbname . ".kebun_spb_vw4 where 1=1 " . $wherediv . " and jjg > 0 order by indukblok asc";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kdblok[$bar['indukblok']] = $bar['indukblok'];
    $kddivisi[$bar['divisi']] = $bar['divisi'];

    $listblok[$bar['divisi']][$bar['indukblok']] = $bar['indukblok'];
}

#get tahun tanam blok
$wherediv = "";
if ($divisi == '') {
    $wherediv = " and substr(kodeorg,1,4)='" . $kdorg . "'";
} else {
    $wherediv = " and kodeorg like '" . $divisi . "%'";
}

// $str="select kodeorg,substr(kodeorg,1,6) as divisi,tahuntanam,luasareaproduktif,jenisbibit from ".$dbname.".setup_blok_tahunan where kodeorg like '".($divisi==''?$kdorg:$divisi)."%' and tahun>='".substr($tgl1, 0,6)."' and tahun<='".substr($tgl2, 0,6)."' ";
$str = "select indukblok as kodeorg,substr(kodeorg,1,6) as divisi,SUM(luasareaproduktif) AS luasareaproduktif,jenisbibit from " . $dbname . ".setup_blok_tahunan where 1=1 " . $wherediv . " and tahun>='" . substr($tgl1, 0, 6) . "' and tahun<='" . substr($tgl2, 0, 6) . "'
group by indukblok";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows = owlbaris($res);
if ($numrows == 0) {
    $str = "select indukblok as kodeorg,substr(kodeorg,1,6) as divisi,SUM(luasareaproduktif) AS luasareaproduktif,jenisbibit from " . $dbname . ".setup_blok where 1=1  " . $wherediv . " group by indukblok";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
}

while ($bar = $res->fetch()) {
    $luasaresta[$bar['divisi']][$bar['kodeorg']] = $bar['luasareaproduktif'];
    $jenisbibit[$bar['divisi']][$bar['kodeorg']] = $bar['jenisbibit'];
}


@$jumdiv = count($kddivisi);
if ($jumdiv > 0) {
    array_multisort($kddivisi, SORT_ASC);
    array_multisort($kdblok, SORT_ASC);
} else {
    exit("error:Data kosong");
}

#Restan Kemarin
#SUM jjg pnn [rekap pnn] - SUM jjg[kebunspbvw] - SUM afkir [rekap pnn]


$wherediv = "";
if ($divisi == '') {
    $wherediv = " and substr(divisi,1,4)='" . $kdorg . "' ";
} else {
    $wherediv = " and divisi = '" . $divisi . "' ";
}

$str = " select sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir,blok,divisi from " . $dbname . ".kebun_rekappnn_vw where tanggal < '" . $tgl1 . "' " . $wherediv . " group by blok ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $jjgpanenkemarin[$bar['divisi']][$bar['blok']]['kemarin'] = $bar['jjgpanen'];
    $jjgafkirkemarin[$bar['divisi']][$bar['blok']]['kemarin'] = $bar['jjgafkir'];
}

$wherediv = "";
if ($divisi == '') {
    $wherediv = " and substr(divisi,1,4)='" . $kdorg . "' ";
} else {
    $wherediv = " and divisi = '" . $divisi . "'";
}

$str = " select sum(jjg) as jjg,indukblok,divisi from " . $dbname . ".kebun_spb_vw4 where  1=1 " . $wherediv . " and tanggal < '" . $tgl1 . "'  group by indukblok ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $jjgspbkemarin[$bar['divisi']][$bar['indukblok']]['kemarin'] = $bar['jjg'];
}

####tutup ambil restan kemarin


##prepare data untuk pertanggal
##repakpnn & spb

$wherediv = "";
if ($divisi == '') {
    $wherediv = " and substr(divisi,1,4) = '" . $kdorg . "' ";
} else {
    $wherediv = " and divisi = '" . $divisi . "'";
}

$str = " select sum(luaspanen) as luaspanen,sum(tenagakerja) as tenagakerja,sum(jjgpanen) as jjgpanen,"
    . "sum(jjgafkir) as jjgafkir,blok,tanggal,divisi from " . $dbname . ".kebun_rekappnn_vw where"
    . " tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' " . $wherediv . " group by blok,tanggal"
    . " order by tanggal asc,blok asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $luaspnn[$bar['divisi']][$bar['blok']][$bar['tanggal']] = $bar['luaspanen'];
    $tk[$bar['divisi']][$bar['blok']][$bar['tanggal']] = $bar['tenagakerja'];
    $jjgpnn[$bar['divisi']][$bar['blok']][$bar['tanggal']] = $bar['jjgpanen'];
    $jjgafkir[$bar['divisi']][$bar['blok']][$bar['tanggal']] = $bar['jjgafkir'];
}


$str = " select sum(jjg) as jjg,sum(kgwbnetto) as kgwb,indukblok,tanggal,divisi from " . $dbname . ".kebun_spb_vw4 where 1=1  " . $wherediv . " and tanggal  between '" . $tgl1 . "' and '" . $tgl2 . "' group by indukblok,tanggal ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $jjgkirim[$bar['divisi']][$bar['indukblok']][$bar['tanggal']] = $bar['jjg'];
    $kgwb[$bar['divisi']][$bar['indukblok']][$bar['tanggal']] = $bar['kgwb'];
}

if ($proses == 'excel') {
    $stream = $_SESSION['lang']['panen'] . " " . $kdorg . " " . $tgl1 . " - " . $tgl2 . "
               <table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellpadding=5 cellspacing=1>";
} //style=width:63%

$span = count($rangetanggal);
$spantanggal = 7 * $span;

$stream .= "
    
    <thead>
        <tr class=rowheader>
            <th align=center  rowspan='3'>" . $_SESSION['lang']['divisi'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center  rowspan='3'>" . $_SESSION['lang']['luas'] . "</th>
            <th align=center  rowspan='3'>" . $_SESSION['lang']['jenisbibit'] . "</th>
            <th align=center  rowspan='3'>Restan</th>    
            <th align=center colspan=" . $spantanggal . ">" . $_SESSION['lang']['tanggal'] . "</th> 
			<th align=center rowspan='2' colspan='9'>" . $_SESSION['lang']['total'] . "</th>    
        </tr>";
$stream .= "<tr>";
foreach ($rangetanggal as $listtanggal => $tgl) {
    $stream .= "<th align=center colspan=7>" . $tgl . "</th>";
}

$stream .= "
        </tr>
        <tr>";
for ($i = 1; $i <= $span + 1; $i++) {
    $stream .= "
            <th align=center>" . $_SESSION['lang']['luas'] . "</th> 
            <th align=center>" . $_SESSION['lang']['jhk'] . "</th> 
            <th align=center>" . $_SESSION['lang']['jjg'] . " " . $_SESSION['lang']['panen'] . "</th> 
            <th align=center>" . $_SESSION['lang']['jjg'] . " " . $_SESSION['lang']['kirim'] . "</th> 
            <th align=center>" . $_SESSION['lang']['Ton'] . " " . $_SESSION['lang']['pabrik'] . "</th>    
            <th align=center>" . $_SESSION['lang']['afkir'] . "</th> 
            <th align=center>Restan</th>";
}
$stream .= "<th align=center>BJR</th>    
			<th align=center>" . $_SESSION['lang']['rotasi'] . "</th>    ";
$stream .= "
        </tr>
    </thead>
 <tbody>";


//   echo"<pre>";
//print_r($luaspnn);
//echo"</pre>";

$romawi = array("01" => "I", "02" => "II", "03" => "III", "04" => "IV", "05" => "V", "06" => "VI", "07" => "VII", "08" => "VIII", "09" => "IX", "10" => "X", "11" => "XI", "12" => "XII", "13" => "XIII", "14" => "XIV", "15" => "XV", "16" => "XVI", "17" => "XVII", "18" => "XVIII", "19" => "XIX", "20" => "XX", "A1" => "Plasma I", "A2" => "Plasma II", "A3" => "Plasma III");

$no = 0;
foreach ($kddivisi as $divisi) {
    $strestankemarindiv = 0;
    $strestankemarintt = 0;
    foreach ($kdblok as $blok) {
        $listblok[$divisi][$blok] = isset($listblok[$divisi][$blok]) ? $listblok[$divisi][$blok] : '';
        if ($listblok[$divisi][$blok] != '') {
            @$restankemarin = $jjgpanenkemarin[$divisi][$blok]['kemarin'] - $jjgafkirkemarin[$divisi][$blok]['kemarin'] - $jjgspbkemarin[$divisi][$blok]['kemarin'];
            @$urestankemarin = $restankemarin; //untuk buat total nanti

            if ($restankemarin < 0) {
                $bgcol = "bgcolor=red";
                $title = "title='restan tidak boleh dibawah 0'";
            } else {
                $bgcol = "";
                $title = "";
            }

            $no++;
            $stream .= "<tr class=rowcontent id=row_" . $no . " onclick=getmark(this.id);>
                        <td align=center>" . substr($divisi, 0, 6) . "</td>
                        <td align=center>" . $listblok[$divisi][$blok] . "</td>    
                        <td align=right>" . @hidezerodecimal($luasaresta[$divisi][$blok], 2) . "</td>
                        <td>" . $jenisbibit[$divisi][$blok] . "</td>
                        <td align=right " . $bgcol . " " . $title . ">" . @hidezerodecimal($restankemarin) . "</td>
                    ";
            foreach ($rangetanggal as $listtanggal => $tgl) {
                @$restan = $restankemarin + $jjgpnn[$divisi][$blok][$tgl] - $jjgafkir[$divisi][$blok][$tgl] - $jjgkirim[$divisi][$blok][$tgl];
                if ($restan < 0) {
                    $bgcol = "bgcolor=red";
                    $title = "title='restan tidak boleh dibawah 0'";
                } else {
                    $bgcol = "";
                    $title = "";
                }

                $stream .= "
                                <td align=right>" . @hidezerodecimal($luaspnn[$divisi][$blok][$tgl], 2) . "</td>
                                <td align=right>" . @hidezerodecimal($tk[$divisi][$blok][$tgl], 2) . "</td>
                                <td align=right>" . @hidezerodecimal($jjgpnn[$divisi][$blok][$tgl]) . "</td>
                                <td align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail('" . $listblok[$divisi][$blok] . "','" . $tgl . "','html',event)>" . @hidezerodecimal($jjgkirim[$divisi][$blok][$tgl]) . "</td>    
                                <td align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail('" . $listblok[$divisi][$blok] . "','" . $tgl . "','html',event)>" . @hidezerodecimal($kgwb[$divisi][$blok][$tgl], 2) . "</td> 
                                <td align=right>" . @hidezerodecimal($jjgafkir[$divisi][$blok][$tgl]) . "</td>  
                                <td align=right " . $bgcol . " " . $title . ">" . @hidezerodecimal($restan) . "</td>    
                        ";

                $urestantgl = $restan;
                @$strestantt[$divisi][$tgl] += $restan;

                $restankemarin = $restan;

                @$stluaspnntt[$divisi][$tgl] += $luaspnn[$divisi][$blok][$tgl];
                @$sttktt[$divisi][$tgl] += $tk[$divisi][$blok][$tgl];
                @$stjjgpnntt[$divisi][$tgl] += $jjgpnn[$divisi][$blok][$tgl];
                @$stjjgkirimtt[$divisi][$tgl] += $jjgkirim[$divisi][$blok][$tgl];
                @$stkgwbtt[$divisi][$tgl] += $kgwb[$divisi][$blok][$tgl];
                @$stjjgafkirtt[$divisi][$tgl] += $jjgafkir[$divisi][$blok][$tgl];

                ##rekap
                @$rekapluaspnn[$divisi][$blok] += $luaspnn[$divisi][$blok][$tgl];
                @$rekaptk[$divisi][$blok] += $tk[$divisi][$blok][$tgl];
                @$rekapjjgpnn[$divisi][$blok] += $jjgpnn[$divisi][$blok][$tgl];
                @$rekapjjgkirim[$divisi][$blok] += $jjgkirim[$divisi][$blok][$tgl];
                @$rekapkgwb[$divisi][$blok] += $kgwb[$divisi][$blok][$tgl];
                @$rekapjjgafkir[$divisi][$blok] += $jjgafkir[$divisi][$blok][$tgl];
            }

            @$rekapbjr = $rekapkgwb[$divisi][$blok] / $rekapjjgkirim[$divisi][$blok];
            @$rekaprot = $rekapluaspnn[$divisi][$blok] / $luasaresta[$divisi][$blok];

            if ($restan < 0) {
                $bgcol = "bgcolor=red";
                $title = "title='restan tidak boleh dibawah 0'";
            } else {
                $bgcol = "";
                $title = "";
            }

            #####untuk rekap
            $stream .= "
                                <td align=right>" . @hidezerodecimal($rekapluaspnn[$divisi][$blok], 2) . "</td>
                                <td align=right>" . @hidezerodecimal($rekaptk[$divisi][$blok], 2) . "</td>
                                <td align=right>" . @hidezerodecimal($rekapjjgpnn[$divisi][$blok]) . "</td>
                                <td align=right>" . @hidezerodecimal($rekapjjgkirim[$divisi][$blok]) . "</td>
                                <td align=right>" . @hidezerodecimal($rekapkgwb[$divisi][$blok], 2) . "</td>
                                <td align=right>" . @hidezerodecimal($rekapjjgafkir[$divisi][$blok]) . "</td>
                                <td align=right " . $bgcol . " " . $title . ">" . @hidezerodecimal($restan) . "</td>    
                                <td align=right>" . @hidezerodecimal($rekapbjr, 2) . "</td>   
                                <td align=right>" . @hidezerodecimal($rekaprot, 2) . "</td>       
                    ";

            @$strekapluaspnntt[$divisi] += $rekapluaspnn[$divisi][$blok];
            @$strekaptktt[$divisi] += $rekaptk[$divisi][$blok];
            @$strekapjjgpnntt[$divisi] += $rekapjjgpnn[$divisi][$blok];
            @$strekapjjgkirimtt[$divisi] += $rekapjjgkirim[$divisi][$blok];
            @$strekapkgwbtt[$divisi] += $rekapkgwb[$divisi][$blok];
            @$strekapjjgafkirtt[$divisi] += $rekapjjgafkir[$divisi][$blok];

            ###tutup rekap

            $stream .= "</tr>";
            @$strestankemarintt += $urestankemarin;
        }
        @$stluastt[$divisi] += $luasaresta[$divisi][$blok];
    }
    @$stluasdiv[$divisi] += $stluastt[$divisi];
    @$strestankemarindiv += $strestankemarintt;

    if ($strestankemarintt < 0) {
        $bgcol = "bgcolor=red";
        $title = "title='restan tidak boleh dibawah 0'";
    } else {
        $bgcol = "";
        $title = "";
    }




    @$gtluas += $stluasdiv[$divisi];
    @$gtrestankemarin += $strestankemarindiv;

    if ($strestankemarindiv < 0) {
        $bgcol = "bgcolor=red";
        $title = "title='restan tidak boleh dibawah 0'";
    } else {
        $bgcol = "";
        $title = "";
    }
    $stream .= "  <tr  bgcolor=pink>
                    <td colspan=2>" . $_SESSION['lang']['subtotal'] . " " . $_SESSION['lang']['divisi'] . " " . $divisi . "</td>
                    <td align=right>" . @hidezerodecimal($stluasdiv[$divisi], 2) . "</td>
                    <td></td>
					<td align=right " . $bgcol . " " . $title . ">" . @hidezerodecimal($strestankemarindiv) . "</td>    
                ";
    foreach ($rangetanggal as $listtanggal => $tgl) {
        if ($strestantt[$divisi][$tgl] < 0) {
            $bgcol = "bgcolor=red";
            $title = "title='restan tidak boleh dibawah 0'";
        } else {
            $bgcol = "";
            $title = "";
        }

        $stream .=  "
					<td align=right>" . @hidezerodecimal($stluaspnntt[$divisi][$tgl], 2) . "</td>
					<td align=right>" . @hidezerodecimal($sttktt[$divisi][$tgl], 2) . "</td>
					<td align=right>" . @hidezerodecimal($stjjgpnntt[$divisi][$tgl]) . "</td>
					<td align=right>" . @hidezerodecimal($stjjgkirimtt[$divisi][$tgl]) . "</td>
					<td align=right>" . @hidezerodecimal($stkgwbtt[$divisi][$tgl], 2) . "</td>
					<td align=right>" . @hidezerodecimal($stjjgafkirtt[$divisi][$tgl]) . "</td>
					<td align=right   " . $bgcol . " " . $title . ">" . @hidezerodecimal($strestantt[$divisi][$tgl]) . "</td>
					";
        @$gtluaspnn[$tgl] += $stluaspnntt[$divisi][$tgl];
        @$gttk[$tgl] += $sttktt[$divisi][$tgl];
        @$gtjjgpnn[$tgl] += $stjjgpnntt[$divisi][$tgl];
        @$gtjjgkirim[$tgl] += $stjjgkirimtt[$divisi][$tgl];
        @$gtkgwbdiv[$tgl] += $stkgwbtt[$divisi][$tgl];
        @$gtjjgafkir[$tgl] += $stjjgafkirtt[$divisi][$tgl];
        @$gtrestan[$tgl] += $strestantt[$divisi][$tgl];
    }


    #rekap

    @$strekapbjrdiv = $strekapkgwbtt[$divisi] / $strekapjjgkirimtt[$divisi];
    @$strekaprotdiv = $strekapluaspnntt[$divisi] / $stluasdiv[$divisi];
    if ($strestantt[$divisi][$tglTerakhir] < 0) {
        $bgcol = "bgcolor=red";
        $title = "title='restan tidak boleh dibawah 0'";
    } else {
        $bgcol = "";
        $title = "";
    }

    $stream .= "
		<td align=right>" . @hidezerodecimal($strekapluaspnntt[$divisi], 2) . "</td>
		<td align=right>" . @hidezerodecimal($strekaptktt[$divisi], 2) . "</td>
		<td align=right>" . @hidezerodecimal($strekapjjgpnntt[$divisi]) . "</td>
		<td align=right>" . @hidezerodecimal($strekapjjgkirimtt[$divisi]) . "</td>
		<td align=right>" . @hidezerodecimal($strekapkgwbtt[$divisi], 2) . "</td>
		<td align=right>" . @hidezerodecimal($strekapjjgafkirtt[$divisi]) . "</td>
		<td align=right   " . $bgcol . " " . $title . ">" . @hidezerodecimal($strestantt[$divisi][$tglTerakhir]) . "</td>
		<td align=right>" . @hidezerodecimal($strekapbjrdiv, 2) . "</td>
		<td align=right>" . @hidezerodecimal($strekaprotdiv, 2) . "</td>   
		";

    @$gtrekapluaspnn += $strekapluaspnntt[$divisi];
    @$gtrekaptk += $strekaptktt[$divisi];
    @$gtrekapjjgpnn += $strekapjjgpnntt[$divisi];
    @$gtrekapjjgkirim += $strekapjjgkirimtt[$divisi];
    @$gtrekapkgwb += $strekapkgwbtt[$divisi];
    @$gtrekapjjgafkir += $strekapjjgafkirtt[$divisi];

    $stream .= "
		</tr>";
}
if ($gtrestankemarin < 0) {
    $bgcol = "bgcolor=red";
    $title = "title='restan tidak boleh dibawah 0'";
} else {
    $bgcol = "";
    $title = "";
}

$stream .= "<tr bgcolor=lightgreen>
                    <td colspan=2>" . $_SESSION['lang']['grnd_total'] . "</td>
                    <td align=right>" . @hidezerodecimal($gtluas, 2) . "</td>  
                    <td></td>
					<td align=right " . $bgcol . " " . $title . ">" . @hidezerodecimal($gtrestankemarin) . "</td>     
               ";

foreach ($rangetanggal as $listtanggal => $tgl) {
    if ($gtrestan[$tgl] < 0) {
        $bgcol = "bgcolor=red";
        $title = "title='restan tidak boleh dibawah 0'";
    } else {
        $bgcol = "";
        $title = "";
    }

    $stream .=  "
                            <td align=right>" . @hidezerodecimal($gtluaspnn[$tgl], 2) . "</td>
                            <td align=right>" . @hidezerodecimal($gttk[$tgl], 2) . "</td>
                            <td align=right>" . @hidezerodecimal($gtjjgpnn[$tgl]) . "</td>
                            <td align=right>" . @hidezerodecimal($gtjjgkirim[$tgl]) . "</td>
                            <td align=right>" . @hidezerodecimal($gtkgwbdiv[$tgl], 2) . "</td>
                            <td align=right>" . @hidezerodecimal($gtjjgafkir[$tgl]) . "</td>
                            <td align=right   " . $bgcol . " " . $title . ">" . @hidezerodecimal($gtrestan[$tgl]) . "</td>
                            ";
}

@$gtrekapbjr = $gtrekapkgwb / $gtrekapjjgkirim;
@$gtrekaprot = $gtrekapluaspnn / $gtluas;

if ($gtrestan[$tglTerakhir] < 0) {
    $bgcol = "bgcolor=red";
    $title = "title='restan tidak boleh dibawah 0'";
} else {
    $bgcol = "";
    $title = "";
}

$stream .= "
                <td align=right>" . @hidezerodecimal($gtrekapluaspnn, 2) . "</td>
                <td align=right>" . @hidezerodecimal($gtrekaptk, 2) . "</td>
                <td align=right>" . @hidezerodecimal($gtrekapjjgpnn) . "</td>
                <td align=right>" . @hidezerodecimal($gtrekapjjgkirim) . "</td>
                <td align=right>" . @hidezerodecimal($gtrekapkgwb, 2) . "</td>
                <td align=right>" . @hidezerodecimal($gtrekapjjgafkir) . "</td>
                <td align=right   " . $bgcol . " " . $title . ">" . @hidezerodecimal($gtrestan[$tglTerakhir]) . "</td>
                    <td align=right>" . @hidezerodecimal($gtrekapbjr, 2) . "</td>
                    <td align=right>" . @hidezerodecimal($gtrekaprot, 2) . "</td>    
                    
                ";


$stream .= "
                </tr>";

$stream .= "
 </tbody>
     </table>";



switch ($proses) {
    ######PREVIEW
    case 'preview':
        echo $stream;
        break;

    ######EXCEL	
    case 'excel':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "laporan_rekap_panen_per_blok" . $kdorg;
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
