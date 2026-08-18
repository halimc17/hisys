<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses= checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
if($kdorg == '') {
    exit("Warning: Unit tidak boleh kosong");
}
$kdorgArr = explode(',', $kdorg);
$unitList = "'".implode("','", $kdorgArr)."'";

$periode= checkPostGet('periode', '');
$intiplasma = checkPostGet('intiplasma','');

$inplasjoin="";
$inplasfilter="";
if($intiplasma!=''){
    $inplasjoin=" AND b.intiplasma='".$intiplasma."'";
    $inplasfilter=" AND intiplasma='".$intiplasma."'";
}

// exit("warning : ".$inplas);

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$prd = $periode;

$periode = str_replace('-', '', $periode);

if($periode==''){
    echo"Warning: Periode tidak boleh kosong"; 
    exit;
}

// Data blok utama akan dibentuk sekalian dari master blok
$str_master="select kodeorg,substr(kodeorg,1,6) as divisi,SUM(luasareaproduktif) AS luasareaproduktif, SUM(jumlahpokok) as jumlahpokok, statusblok 
          from ".$dbname.".setup_blok 
          where substr(kodeorg,1,4) IN (".$unitList.") ".$inplasfilter." 
          group by kodeorg";
$res_master=$owlPDO->query($str_master) or die(print " Gagal: ".PDOException::getMessage());
$res_master->setFetchMode(PDO::FETCH_ASSOC);

while($bar=$res_master->fetch()){
    $divisi = $bar['divisi'];
    $blok = $bar['kodeorg'];
    
    // Bentuk data blok utama di sini
    $kdblok[$blok] = $blok;
    $kddivisi[$divisi] = $divisi;
    $unitCode = substr($divisi,0,4);
    $unitDivs[$unitCode][$divisi] = $divisi;
    $listblok[$divisi][$blok] = $blok;

    $luasaresta[$divisi][$blok][$bar['statusblok']] = $bar['luasareaproduktif'];
    $pokokaresta[$divisi][$blok] = $bar['jumlahpokok'];
}

// Timpa luas dan pokok dari setup_blok_tahunan jika ada
$str_thn="select indukblok as kodeorg,substr(kodeorg,1,6) as divisi,SUM(luasareaproduktif) AS luasareaproduktif, SUM(jumlahpokok) as jumlahpokok, statusblok 
      from ".$dbname.".setup_blok_tahunan 
      where tahun='".substr($periode,0,4)."' AND substr(kodeorg,1,4) IN (".$unitList.") ".$inplasfilter."
      group by kodeorg";
$res_thn=$owlPDO->query($str_thn) or die(print " Gagal: ".PDOException::getMessage());
$res_thn->setFetchMode(PDO::FETCH_ASSOC);

while($bar=$res_thn->fetch()){
    $divisi = $bar['divisi'];
    $blok = $bar['kodeorg'];
    
    if(isset($kdblok[$blok])){
        unset($luasaresta[$divisi][$blok]); // bersihkan status lama
        $luasaresta[$divisi][$blok][$bar['statusblok']] = $bar['luasareaproduktif'];
        $pokokaresta[$divisi][$blok] = $bar['jumlahpokok'];
    }
}

@$jumunit=count($unitDivs);
if($jumunit>0){
    ksort($unitDivs);
    array_multisort($kdblok,SORT_ASC);
}else{
    exit("error:Data kosong");
}

$mapping = [
    '621080301' => ['pupuk'=>'kieserit'],
    '621080305' => ['pupuk'=>'mop'],
    '621080306' => ['pupuk'=>'rp'],
    '621080307' => ['pupuk'=>'urea'],
    '621080308' => ['pupuk'=>'borate'],
    '621080309' => ['pupuk'=>'dolomite'],
    '621080310' => ['pupuk'=>'npk'],
    '621080311' => ['pupuk'=>'npk'],
    '621080313' => ['pupuk'=>'demplot'],
    '621080314' => ['pupuk'=>'sisip'],
    '621080316' => ['pupuk'=>'npk'],
    '621080346' => ['pupuk'=>'fe'],
    '621080351' => ['pupuk'=>'npk'],
    '621080401' => ['pupuk'=>'organik'],

    '126080201' => ['pupuk'=>'kieserit'],
    '126080206' => ['pupuk'=>'mop'],
    '126080207' => ['pupuk'=>'rp'],
    '126080208' => ['pupuk'=>'urea'],
    '126080209' => ['pupuk'=>'borate'],
    '126080210' => ['pupuk'=>'dolomite'],
    '126080211' => ['pupuk'=>'npk'],
    '126080212' => ['pupuk'=>'npk'],
    '126080213' => ['pupuk'=>'npk'],
    '126080248' => ['pupuk'=>'urea'],
    '126080251' => ['pupuk'=>'fe'],
    '126080301' => ['pupuk'=>'organik'],
];

// khusus urea, moop, rp, dan npk ambil sdbi
$listpupuk = ['urea','mop','rp','npk'];

function getRotasi($jenisPupuk, $bulan){
    if($jenisPupuk == 'npk'){
        if($bulan <= 4) return '1';
        if($bulan <= 8) return '2';
        return '3';
    }
    return ($bulan <= 6) ? '1' : '2';
}

$str="
SELECT 
    kodeblok as kodeorg,
    SUBSTR(kodeblok,1,6) AS divisi,
    kodekegiatan,
    notransaksi,
    tanggal,
    SUM(jumlah) AS jumlah
FROM ".$dbname.".log_transaksi_vw
WHERE tipetransaksi=5 
  AND SUBSTR(kodeblok,1,4) IN (".$unitList.")
  AND tanggal LIKE '".$prd."%'
  AND kodekegiatan IN ('".implode("','", array_keys($mapping))."')
GROUP BY kodeblok, kodekegiatan, notransaksi, tanggal
ORDER BY kodeblok
";

// exit("warning: ".$str);

$pupukreal = [];

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

$bulanNow = (int)substr($periode,4,2);
while($bar=$res->fetch()){
    $kode = $bar['kodekegiatan'];

    if(!isset($mapping[$kode])) continue;

    $jenisPupuk = $mapping[$kode]['pupuk'];

    $divisi = $bar['divisi'];
    $blok   = $bar['kodeorg'];
    $jumlah = $bar['jumlah'];

    // ambil bulan
    $bulan = (int)substr($bar['tanggal'], 5, 2);

    if(in_array($jenisPupuk, $listpupuk)){

        $rotasi = getRotasi($jenisPupuk, $bulan);
        $finalKey = ($rotasi == '1') ? $jenisPupuk : $jenisPupuk.$rotasi;

        $pupukreal[$divisi][$blok][$finalKey] =
            ($pupukreal[$divisi][$blok][$finalKey] ?? 0)
            + $jumlah;

    } 
    else{
        if($bulan != $bulanNow) continue;

        $finalKey = $jenisPupuk;

        $pupukreal[$divisi][$blok][$finalKey] =
            ($pupukreal[$divisi][$blok][$finalKey] ?? 0)
            + $jumlah;
    }
}

$map_barang = [
    '31101001' => 'kieserit',
    '31101002' => 'mop',
    '31101003' => 'rp',
    '31101004' => 'urea',
    '31101005' => 'borate',
    '31101006' => 'dolomite',
    '31101017' => 'fe',
    '31101019' => 'fe',
    '31101020' => 'fe',
    '31101021' => 'dolomite',
    '31101023' => 'rp',
    '31102001' => 'npk',
    '31102002' => 'npk',
    '31102003' => 'cu',
    '31102005' => 'cu',
    '31102007' => 'npk',
    '31102011' => 'npk',
    '31102015' => 'npk',
    '31102018' => 'npk',
    '31102019' => 'npk',
    '31102020' => 'npk',
    '31102021' => 'npk',
    '31102022' => 'npk',
    '31102023' => 'fe',
    '31102024' => 'npk',
    '31102025' => 'npk',
    '31102026' => 'npk',
    '31102027' => 'npk',
    '31102028' => 'npk',
    '31103002' => 'organik',
    '31103004' => 'organik',
    '31103005' => 'ostindo',
    '31103006' => 'organik',
    '31103007' => 'organik',
    '31103012' => 'organik'
];

$str="
SELECT 
    blok as kodeorg,
    SUBSTR(blok,1,6) AS divisi,
    kodebarang,
    periodepemupukan,
    SUM(dosis) AS dosis
FROM ".$dbname.".kebun_rekomendasipupuk
WHERE SUBSTR(blok,1,4) IN (".$unitList.")
  AND periodepemupukan LIKE '".substr($periode,0,4)."%'
GROUP BY blok, kodebarang, periodepemupukan
ORDER BY blok
";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$dosisrek = [];

while($bar=$res->fetch()){
    $kodeb = $bar['kodebarang'];

    if(!isset($map_barang[$kodeb])) continue;

    $jenisPupuk = $map_barang[$kodeb];

    $divisi = $bar['divisi'];
    $blok   = $bar['kodeorg'];
    $nilai  = $bar['dosis'];

    $bulan = (int)substr($bar['periodepemupukan'], 5, 2);

    if($nilai == 0) continue;

    if(in_array($jenisPupuk, $listpupuk)){
        if($bulan > $bulanNow) continue;
        $rotasi = getRotasi($jenisPupuk, $bulan);
        $finalKey = ($rotasi == '1') ? $jenisPupuk : $jenisPupuk.$rotasi;

        $dosisrek[$divisi][$blok][$finalKey] = ($dosisrek[$divisi][$blok][$finalKey] ?? 0) + $nilai;
    } else {
        if($bulan != $bulanNow) continue;
        $finalKey = $jenisPupuk;
        $dosisrek[$divisi][$blok][$finalKey] = ($dosisrek[$divisi][$blok][$finalKey] ?? 0) + $nilai;
    }
}

$addstrsdbi="(";
for($i=1;$i<=intval(substr($periode,4,2));$i++)
{
    if($i<10)
    {
        $isi="fis0".$i;
    }
    else 
    {
        $isi="fis".$i;
    }
    if($i<intval(substr($periode,4,2)))
    {
        $addstrsdbi.=$isi."+";
    }
    else
    {
        $addstrsdbi.=$isi;
    }
}
$addstrsdbi.=")";

$str="
SELECT 
    kodeorg AS kodeorg,
    SUBSTR(kodeorg,1,6) AS divisi,
    kodebarang,
    fis01,fis02,fis03,fis04,fis05,fis06,
    fis07,fis08,fis09,fis10,fis11,fis12
FROM ".$dbname.".bgt_budget_kebun_perblok_vw
WHERE SUBSTR(kodeorg,1,4) IN (".$unitList.")
  AND tahunbudget = '".substr($periode,0,4)."'
  AND kodebarang IN ('".implode("','", array_keys($map_barang))."')
ORDER BY kodeorg
";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$pupukbgt = [];

$bulanNow = (int)substr($periode,4,2);

while($bar=$res->fetch()){
    $kodeb = $bar['kodebarang'];

    if(!isset($map_barang[$kodeb])) continue;

    $jenisPupuk = $map_barang[$kodeb];

    $divisi = $bar['divisi'];
    $blok   = $bar['kodeorg'];

    // loop per bulan (fis01 - fis12)
    for($i=1; $i<=12; $i++){

        $field = ($i<10) ? 'fis0'.$i : 'fis'.$i;
        $nilai = $bar[$field] ?? 0;

        if($nilai == 0) continue;

        if(in_array($jenisPupuk, $listpupuk)){

            // hanya sampai bulan periode
            if($i > $bulanNow) continue;

            $rotasi = getRotasi($jenisPupuk, $i);
            $finalKey = ($rotasi == '1') ? $jenisPupuk : $jenisPupuk.$rotasi;

            $pupukbgt[$divisi][$blok][$finalKey] =
                ($pupukbgt[$divisi][$blok][$finalKey] ?? 0)
                + $nilai;

        }
        else{

            if($i != $bulanNow) continue;

            $finalKey = $jenisPupuk;

            $pupukbgt[$divisi][$blok][$finalKey] =
                ($pupukbgt[$divisi][$blok][$finalKey] ?? 0)
                + $nilai;
        }
    }
}

if ($proses == 'excel') {
    $stream = $_SESSION['lang']['panen']." ".$kdorg." ".$tgl1." - ".$tgl2."
               <table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellpadding=5 cellspacing=1>";
}//style=width:63%

$stream.="

    <table border=1 cellspacing=0 cellpadding=4>
  <thead>
    <tr>
      <th rowspan=3>DIVISI</th>
      <th rowspan=3>BLOK</th>
      <th colspan=3>LUAS (Ha)</th>

      <th colspan=4>Urea Rotasi 1</th>
      <th colspan=4>Urea Rotasi 2</th>

      <th colspan=4>MOP ROTASI 1</th>
      <th colspan=4>MOP ROTASI 2</th>

      <th colspan=4>KISERIT</th>

      <th colspan=4>RP</th>
      <th colspan=4>RP 2</th>

      <th colspan=4>Dolomite</th>

      <th colspan=4>Borate</th>

      <th colspan=4>Fe</th>

      <th colspan=4>Cu</th>

      <th colspan=4>NPK TAHAP I</th>
      <th colspan=4>NPK TAHAP II</th>
      <th colspan=4>NPK TAHAP III</th>

      <th colspan=4>OSTINDO</th>
    </tr>

    <tr>
      <th rowspan=2>TM</th>
      <th rowspan=2>TBM</th>
      <th rowspan=2>Total</th>
    ";
    for($i=1; $i<=15; $i++) {
      $stream.="
      <th colspan=2>Program</th>
      <th colspan=2>Realisasi</th>
    ";
    }
    $stream.="
    </tr>

    <tr>
    ";
    for($i=1; $i<=30; $i++) {
      $stream.="
      <th>Dosis Rata2 (kg/ha)</th>
      <th>Jumlah (Kg)</th>
    ";
    }
    $stream.="
    </tr>
  </thead>
 <tbody>";

$listpupukfull = ['urea','urea2','mop','mop2','kieserit','rp','rp2','dolomite','borate','fe','cu','npk','npk2','npk3','ostindo'];

$gtluastm = $gtluastbm = $gtluas = $gtpokok = 0;
$gtpupukbgt = $gtpupukreal = [];

$no=0;
foreach($unitDivs as $unit => $divisions){
    // Unit Header
    $stream.="<tr class=rowcontent bgcolor='#FFFF00'>
                <td colspan='65'><b>UNIT: ".$unit." - ".($namaOrg[$unit] ?? $unit)."</b></td>
              </tr>";
    
    // Reset Unit Totals
    $utluastm = $utluastbm = $utluastt = $utpokok = 0;
    $utpupukbgt = $utpupukreal = [];

    foreach($divisions as $divisi){
        // Reset Divisi Totals
        $stluastm[$divisi] = $stluastmbm[$divisi] = $stluastt[$divisi] = $stpokok[$divisi] = 0;
        $stpupukbgt[$divisi] = $stpupukreal[$divisi] = [];

        foreach($kdblok as $blok){
            $listblok[$divisi][$blok]=isset($listblok[$divisi][$blok])?$listblok[$divisi][$blok]:'';
            if($listblok[$divisi][$blok]!=''){
                $no++;
                $stream.="<tr class=rowcontent id=row_".$no." onclick=getmark(this.id);>
                    <td align=center>".substr($divisi,0,6)."</td>
                    <td align=center>".$listblok[$divisi][$blok]."</td>    
                    <td align=right>".@hidezerodecimal($luasaresta[$divisi][$blok]['TM'],2)."</td>
                    <td align=right>".@hidezerodecimal($luasaresta[$divisi][$blok]['TBM'],2)."</td>
                    <td align=right>".@hidezerodecimal($luasaresta[$divisi][$blok]['TM']+$luasaresta[$divisi][$blok]['TBM'],2)."</td>
                ";
                foreach($listpupukfull as $pupuk){
                    $dosisbgt = $dosisrek[$divisi][$blok][$pupuk] ?? 0;
                    $stream.="<td align=right>".fixnan(@hidezerodecimal($dosisbgt,2))."</td>";
                    $stream.="<td align=right>".fixnan(@hidezerodecimal($pupukbgt[$divisi][$blok][$pupuk] ?? 0,2))."</td>";

                    $jmlpokok = $pokokaresta[$divisi][$blok] ?? 0;
                    $dosisreal = ($jmlpokok!=0)?($pupukreal[$divisi][$blok][$pupuk] ?? 0)/$jmlpokok:0;
                    $stream.="<td align=right>".fixnan(@hidezerodecimal($dosisreal,2))."</td>";
                    $stream.="<td align=right>".fixnan(@hidezerodecimal($pupukreal[$divisi][$blok][$pupuk] ?? 0,2))."</td>";

                    @$stpupukbgt[$divisi][$pupuk]+=$pupukbgt[$divisi][$blok][$pupuk];
                    @$stpupukreal[$divisi][$pupuk]+=$pupukreal[$divisi][$blok][$pupuk];
                }
                @$stluastm[$divisi]+=$luasaresta[$divisi][$blok]['TM'];
                @$stluastmbm[$divisi]+=$luasaresta[$divisi][$blok]['TBM'];
                @$stluastt[$divisi]+=($luasaresta[$divisi][$blok]['TM']+$luasaresta[$divisi][$blok]['TBM']);
                @$stpokok[$divisi]+=$pokokaresta[$divisi][$blok];
            }
        }
        
        $stluastmdiv[$divisi]=$stluastm[$divisi];
        $stluastbmdiv[$divisi]=$stluastmbm[$divisi];
        $stluasdiv[$divisi]=$stluastt[$divisi];
        $stpokokdiv[$divisi]=$stpokok[$divisi];
        
        $stream.="  <tr bgcolor='#FFCC99'>
                        <td colspan=2>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['divisi']." ".$divisi."</td>
                        <td align=right>".@hidezerodecimal($stluastmdiv[$divisi],2)."</td>
                        <td align=right>".@hidezerodecimal($stluastbmdiv[$divisi],2)."</td>
                        <td align=right>".@hidezerodecimal($stluasdiv[$divisi],2)."</td> 
                    ";
        foreach($listpupukfull as $pupuk){
            $dosisbgt = ($stpokokdiv[$divisi]!=0)?$stpupukbgt[$divisi][$pupuk]/$stpokokdiv[$divisi]:0;
            $dosisreal = ($stpokokdiv[$divisi]!=0)?$stpupukreal[$divisi][$pupuk]/$stpokokdiv[$divisi]:0;
            $stream.=  "
                        <td align=right>".@hidezerodecimal($dosisbgt,2)."</td>
                        <td align=right>".@hidezerodecimal($stpupukbgt[$divisi][$pupuk],2)."</td>
                        <td align=right>".@hidezerodecimal($dosisreal,2)."</td>
                        <td align=right>".@hidezerodecimal($stpupukreal[$divisi][$pupuk],2)."</td>
                        ";
            @$utpupukbgt[$unit][$pupuk]+=$stpupukbgt[$divisi][$pupuk];
            @$utpupukreal[$unit][$pupuk]+=$stpupukreal[$divisi][$pupuk];
        }
        $stream.="</tr>";
        
        $utluastm += $stluastmdiv[$divisi];
        $utluastbm += $stluastbmdiv[$divisi];
        $utluastt += $stluasdiv[$divisi];
        $utpokok += $stpokokdiv[$divisi];
    }
    
    // Unit Total
    $stream.="  <tr bgcolor='#FF9900'>
                    <td colspan=2><b>TOTAL UNIT ".$unit."</b></td>
                    <td align=right><b>".@hidezerodecimal($utluastm,2)."</b></td>
                    <td align=right><b>".@hidezerodecimal($utluastbm,2)."</b></td>
                    <td align=right><b>".@hidezerodecimal($utluastt,2)."</b></td> 
                ";
    foreach($listpupukfull as $pupuk){
        $dosisbgt = ($utpokok!=0)?$utpupukbgt[$unit][$pupuk]/$utpokok:0;
        $dosisreal = ($utpokok!=0)?$utpupukreal[$unit][$pupuk]/$utpokok:0;
        $stream.=  "
                    <td align=right><b>".@hidezerodecimal($dosisbgt,2)."</b></td>
                    <td align=right><b>".@hidezerodecimal($utpupukbgt[$unit][$pupuk],2)."</b></td>
                    <td align=right><b>".@hidezerodecimal($dosisreal,2)."</b></td>
                    <td align=right><b>".@hidezerodecimal($utpupukreal[$unit][$pupuk],2)."</b></td>
                    ";
        @$gtpupukbgt[$pupuk]+=$utpupukbgt[$unit][$pupuk];
        @$gtpupukreal[$pupuk]+=$utpupukreal[$unit][$pupuk];
    }
    $stream.="</tr>";
    
    $gtluastm += $utluastm;
    $gtluastbm += $utluastbm;
    $gtluas += $utluastt;
    $gtpokok += $utpokok;
}

$stream.="<tr bgcolor=lightgreen>
                    <td colspan=2>".$_SESSION['lang']['grnd_total']."</td>
                    <td align=right>".@hidezerodecimal($gtluastm,2)."</td>  
                    <td align=right>".@hidezerodecimal($gtluastbm,2)."</td>
                    <td align=right>".@hidezerodecimal($gtluas,2)."</td>    
               ";
   
            foreach($listpupukfull as $pupuk){
                $dosisbgt = ($gtpokok!=0)?$gtpupukbgt[$pupuk]/$gtpokok:0;
                $dosisreal = ($gtpokok!=0)?$gtpupukreal[$pupuk]/$gtpokok:0;
                $stream.=  "
                            <td align=right>".@hidezerodecimal($dosisbgt,2)."</td>
                            <td align=right>".@hidezerodecimal($gtpupukbgt[$pupuk],2)."</td>
                            <td align=right>".@hidezerodecimal($dosisreal,2)."</td>
                            <td align=right>".@hidezerodecimal($gtpupukreal[$pupuk],2)."</td>
                            ";
            }            
            $stream.="
                </tr>";

$stream.="
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
        $nop_ = "laporan_rekap_pupuk_per_blok_" . str_replace(',', '_', $kdorg);
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