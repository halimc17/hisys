<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kdorg = checkPostGet('kdorg','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));
$kddiv = checkPostGet('kddiv','');
if(($proses=='excel')or($proses=='pdf')){
	
	$tgl1=tanggalsystemn($_GET['tgl1']);
	$tgl2=tanggalsystemn($_GET['tgl2']);
	
}




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


$optNmKeg=  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optNmKegST=  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
$optMt=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optSt=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');

$kdorgsch = "";
$kdorgsch2 = "";
$kdorgsch3 = "";

if($kdorg!='')
{
	if($kddiv=='')
	{
    $kdorgsch="and divisi like '".$kdorg."%' ";
    $kdorgsch2="and a.kodeorg like '".$kdorg."' ";
    $kdorgsch3="and kodeorg like '".$kdorg."%' ";
	}
	else
	{
	$kdorgsch="and divisi like '".$kddiv."' ";
    $kdorgsch2="and a.kodeorg like '".$kdorg."' and b.kodeorg like '".$kddiv."%'";
    $kdorgsch3="and kodeorg like '".$kddiv."%' ";
	}
    
}
else
{
	exit('Warning : Unit harus dipilih');
}

	


    
    if($proses=='excel'){###prepare data
$stream="";
if($_SESSION['language']=='EN'){
	$stream="Daily Work Program Versus Planting";
}else{
	$stream="Rencana Kerja Harian";
}$stream.="<table cellspacing='1' cellpadding='5' border='1' class='sortable'>";}
      else $stream.="<table cellspacing='1' cellpadding='5' border='0' class='sortable' max-width=100%>";
      $stream.="<thead class=rowheader>
       <tr>
                <th align=center rowspan=2  width:70px;>".$_SESSION['lang']['tanggal']."</th>
                <th align=center rowspan=2 >".$_SESSION['lang']['divisi']."</th>
                <th align=center rowspan=2 >".$_SESSION['lang']['kegiatan']."</th>
                <th align=center rowspan=2 >".$_SESSION['lang']['satuan']."</th>

                <th align=center colspan=3>Blok</th>
                <th align=center colspan=5>RKH</th>
                <th align=center colspan=7>Realisasi</th>          

        </tr>
        <tr>
        		<th align=center>".$_SESSION['lang']['nomor']."</th>
                <th align=center>".$_SESSION['lang']['luas']."</th>
                <th align=center>".$_SESSION['lang']['tahuntanam']."</th>

                <th align=center>".$_SESSION['lang']['prestasi']."</th>
                <th align=center>".$_SESSION['lang']['hk']."</th>
                <th align=center width:200px;>".$_SESSION['lang']['material']."</th>
                <th align=center>".$_SESSION['lang']['satuan']."</th>
                <th align=center>".$_SESSION['lang']['jumlah']."</th>

                <th align=center>".$_SESSION['lang']['prestasi']."</th>
                <th align=center>".$_SESSION['lang']['hk']."</th>
                <th align=center>".$_SESSION['lang']['premi']."</th>
                <th align=center width:200px;>".$_SESSION['lang']['material']."</th>
                <th align=center>".$_SESSION['lang']['satuan']."</th>
                <th align=center>".$_SESSION['lang']['jumlah']."</th>
                <th align=center width:200px;>".$_SESSION['lang']['keterangan']."</th>

        </tr>
                </thead>
      <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr
$str1="SELECT *, (hk_pb+hk_khl+hk_bor) as jlhhk FROM ".$dbname.".kebun_rkh_vw  WHERE tanggal between '".$tgl1."' and '".$tgl2."' ".$kdorgsch." order by tanggal, divisi, kodekegiatan, kodeblok ";
//exit($str1);
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_ASSOC);

@$arrData = array();
@$arrDatax = array();
@$arrDataz = array();

while($bar1=$res1->fetch()){
   	$arrData[$bar1['tanggal']][$bar1['divisi']][$bar1['kodekegiatan']][$bar1['kodeblok']]['prestasi']=$bar1['target'];
   	$arrData[$bar1['tanggal']][$bar1['divisi']][$bar1['kodekegiatan']][$bar1['kodeblok']]['hk']=$bar1['jlhhk'];

    $arrDataz[$bar1['tanggal']][$bar1['divisi']][$bar1['kodekegiatan']][$bar1['kodeblok']][0]+=1;
}

$whin=" and notransaksi in (SELECT notransaksi FROM ".$dbname.".kebun_rkh_vw  WHERE tanggal between '".$tgl1."' and '".$tgl2."' ".$kdorgsch.")";

$strx="SELECT * FROM ".$dbname.".kebun_rkh_dtmaterial  WHERE 1=1 ".$whin."  order by kodebarang ";
//echo $strx;
$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_ASSOC);
while($barx=$resx->fetch()){
	$arrData[$bar1['tanggal']][$bar1['divisi']][$bar1['kodekegiatan']][$bar1['kodeblok']]['material'][$barx['kodebarang']]=$barx['kodebarang'];
	$arrData[$bar1['tanggal']][$bar1['divisi']][$bar1['kodekegiatan']][$bar1['kodeblok']]['jumlah'][$barx['kodebarang']]=$barx['jumlah'];
	$arrDataz[$bar1['tanggal']][$bar1['divisi']][$bar1['kodekegiatan']][$bar1['kodeblok']][$barx['kodebarang']]+=1;
//exit();
}

$str1="SELECT a.*, b.*, (b.upahpremi+b.upahpremilebihbasis +b.premibasis) as jlhpremi FROM ".$dbname.".kebun_aktifitas a 
left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi 
WHERE tanggal between '".$tgl1."' and '".$tgl2."' ".$kdorgsch2." order by a.tanggal, kodekegiatan, b.kodeorg ";

$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_ASSOC);
$bgcolorsx='';
while($bar1=$res1->fetch())
{
	$arrDatax[$bar1['tanggal']][substr($bar1['kodeorg'],0,6)][$bar1['kodekegiatan']][$bar1['kodeorg']]['prestasi']=$bar1['hasilkerja'];
	$arrDatax[$bar1['tanggal']][substr($bar1['kodeorg'],0,6)][$bar1['kodekegiatan']][$bar1['kodeorg']]['hk']=$bar1['jumlahhk'];
	$arrDatax[$bar1['tanggal']][substr($bar1['kodeorg'],0,6)][$bar1['kodekegiatan']][$bar1['kodeorg']]['premi']+=$bar1['jlhpremi'];
   	$arrDatax[$bar1['tanggal']][substr($bar1['kodeorg'],0,6)][$bar1['kodekegiatan']][$bar1['kodeorg']]['keterangan']=$bar1['keterangan'];
   	
   	$strx="SELECT * FROM ".$dbname.".kebun_pakaimaterial  WHERE notransaksi='".$bar1['notransaksi']."'  order by kodebarang ";
   	//echo $strx;
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_ASSOC);
	while($barx=$resx->fetch()){
		$arrDatax[$bar1['tanggal']][substr($bar1['kodeorg'],0,6)][$bar1['kodekegiatan']][$bar1['kodeorg']]['material'][$barx['kodebarang']]=$barx['kodebarang'];
		$arrDatax[$bar1['tanggal']][substr($bar1['kodeorg'],0,6)][$bar1['kodekegiatan']][$bar1['kodeorg']]['jumlah'][$barx['kodebarang']]=$barx['kwantitas'];
		$arrDataz[$bar1['tanggal']][substr($bar1['kodeorg'],0,6)][$bar1['kodekegiatan']][$bar1['kodeorg']][$barx['kodebarang']]+=1;
	}

    $arrDataz[$bar1['tanggal']][substr($bar1['kodeorg'],0,6)][$bar1['kodekegiatan']][$bar1['kodeorg']][0]+=1;
}

$str1=" SELECT kodeorg, (luasareaproduktif + luasareanonproduktif) as luas, tahuntanam FROM ".$dbname.".setup_blok_tahunan 
 WHERE tahun>='".substr(str_replace('-', '', $tgl1),0,6)."' ".$kdorgsch3." order by kodeorg ";
 //exit('Error : '.$str1);
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlbaris($res1);
if(count(fetchData($str1))==0){
	$str1=" SELECT kodeorg, (luasareaproduktif + luasareanonproduktif) as luas, tahuntanam FROM ".$dbname.".setup_blok 
 WHERE 1=1 ".$kdorgsch3." order by kodeorg ";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_ASSOC);
}

$arrDatay=array();
while($bar1=$res1->fetch())
{
	$arrDatay[$bar1['kodeorg']]['kodeorg']=$bar1['kodeorg'];
	$arrDatay[$bar1['kodeorg']]['luas']=$bar1['luas'];
	$arrDatay[$bar1['kodeorg']]['tahuntanam']=$bar1['tahuntanam'];
}
$date=$tgl1;
while (strtotime($date) <= strtotime($tgl2)) {
	$stream.="<tr class=rowcontent>";

	if(!empty($arrDataz[$date]))
	{
		$xy=0;
		$divisi='';
		$divisix='';
		$kegiatan='';
		$kegiatanx='';
		$blok='';
		$blokx='';

		foreach ($arrDataz[$date] as $key1 => $val1) {
			foreach ($val1 as $key2 => $val2) {
				foreach ($val2 as $key3 => $val3) {
					foreach ($val3 as $key4 => $val4) {
						
						if($key4==0){
							if($arrData[$date][$key1][$key2][$key3]['prestasi']=='')
								{
									//echo $arrData[$date][$key1][$key2][$key3]['prestasi'];
									$bgcolorsx='#ec1c24';
								}
								elseif($arrDatax[$date][$key1][$key2][$key3]['prestasi']=='')
								{
									//echo $arrDatax[$date][$key1][$key2][$key3]['prestasi'];
									$bgcolorsx='#ec1c24';
								}
								elseif ($arrData[$date][$key1][$key2][$key3]['material'][$key4]!=$arrDatax[$date][$key1][$key2][$key3]['material'][$key4]) {
									$bgcolorsx='#ec1c24';
								}
								else
								{
									$bgcolorsx='';
								}
								$bgcolorsx='';
							if($xy>0)
							{
								if($key1==$divisix)
								{
									$divisi='';
									if($key2==$kegiatanx)
									{
										$kegiatan='';
										if($key3==$blokx)
										{
											$blok='';
										}
										else
										{
											$blok=$key3;
											$blokx=$key3;
										}
									}
									else
									{
										$kegiatan=$key2;
										$kegiatanx=$key2;
										$blok=$key3;
										$blokx=$key3;
									}
								}
								else
								{
									$divisi=$key1;
									$divisix=$key1;
									$kegiatan=$key2;
									$kegiatanx=$key2;
									$blok=$key3;
									$blokx=$key3;
								}

								$stream.="<tr class=rowcontent>";
								$stream.="<td></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$divisi."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optNmKeg[$kegiatan]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optNmKegST[$kegiatan]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".getNamaOrg($blok)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatay[$blok]['luas'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".$arrDatay[$blok]['tahuntanam']."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['prestasi'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['hk'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['prestasi'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['hk'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['premi'])."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$arrDatax[$date][$key1][$key2][$key3]['keterangan']."</td> ";
								$stream.="</tr>";
							}
							else
							{
									$divisi=$key1;
									$divisix=$key1;
									$kegiatan=$key2;
									$kegiatanx=$key2;
									$blok=$key3;
									$blokx=$key3;
								$stream.="<td width=70px>".tanggalnormal($date)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$divisi."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";width:200px;'>".$optNmKeg[$kegiatan]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optNmKegST[$kegiatan]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".getNamaOrg($blok)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatay[$blok]['luas'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".$arrDatay[$blok]['tahuntanam']."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['prestasi'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['hk'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'width:200px;></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['prestasi'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['hk'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['premi'])."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'></td> ";
								$stream.="<td style='color:".$bgcolorsx.";width:200px;'>".$arrDatax[$date][$key1][$key2][$key3]['keterangan']."</td> ";
							}
						}
						else
						{
							if($arrData[$date][$key1][$key2][$key3]['prestasi']=='')
							{
								//echo $arrData[$date][$key1][$key2][$key3]['prestasi'];
								$bgcolorsx='#ec1c24';
							}
							elseif($arrDatax[$date][$key1][$key2][$key3]['prestasi']=='')
							{
								//echo $arrDatax[$date][$key1][$key2][$key3]['prestasi'];
								$bgcolorsx='#ec1c24';
							}
							elseif ($arrData[$date][$key1][$key2][$key3]['material'][$key4]!=$arrDatax[$date][$key1][$key2][$key3]['material'][$key4]) {
								$bgcolorsx='#ec1c24';
							}
							else
							{
								$bgcolorsx='';
							}
							$bgcolorsx='';

							if($xy>0)
							{
								if($key1==$divisix)
								{
									$divisi='';
									if($key2==$kegiatanx)
									{
										$kegiatan='';
										if($key3==$blokx)
										{
											$blok='';
										}
										else
										{
											$blok=$key3;
											$blokx=$key3;
										}
									}
									else
									{
										$kegiatan=$key2;
										$kegiatanx=$key2;
										$blok=$key3;
										$blokx=$key3;
									}
								}
								else
								{
									$divisi=$key1;
									$divisix=$key1;
									$kegiatan=$key2;
									$kegiatanx=$key2;
									$blok=$key3;
									$blokx=$key3;
								}
								//print_r($arrDatax[$date][$key1][$key2][$key3]['material']);
								$stream.="<tr class=rowcontent>";
								$stream.="<td></td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$divisi."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";width:200px;'>".$optNmKeg[$kegiatan]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optNmKegST[$kegiatan]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".getNamaOrg($blok)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatay[$blok]['luas'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".$arrDatay[$blok]['tahuntanam']."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['prestasi'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['hk'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'width:200px;>".$optMt[$arrData[$date][$key1][$key2][$key3]['material'][$key4]]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optSt[$arrData[$date][$key1][$key2][$key3]['material'][$key4]]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['jumlah'][$key4],3)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['prestasi'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['hk'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['premi'])."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'width:200px;>".$optMt[$arrDatax[$date][$key1][$key2][$key3]['material'][$key4]]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optSt[$arrDatax[$date][$key1][$key2][$key3]['material'][$key4]]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['jumlah'][$key4],3)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";width:200px;'>".$arrDatax[$date][$key1][$key2][$key3]['keterangan']."</td> ";
								$stream.="</tr>";
							}
							else
							{
									$divisi=$key1;
									$divisix=$key1;
									$kegiatan=$key2;
									$kegiatanx=$key2;
									$blok=$key3;
									$blokx=$key3;
								$stream.="<td width=70px>".tanggalnormal($date)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$divisi."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optNmKeg[$kegiatan]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optNmKegST[$kegiatan]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$blok."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatay[$blok]['luas'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".$arrDatay[$blok]['tahuntanam']."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['prestasi'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['hk'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optMt[$arrData[$date][$key1][$key2][$key3]['material'][$key4]]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optSt[$arrData[$date][$key1][$key2][$key3]['material'][$key4]]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrData[$date][$key1][$key2][$key3]['jumlah'][$key4],3)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['prestasi'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['hk'],2)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['premi'])."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optMt[$arrDatax[$date][$key1][$key2][$key3]['material'][$key4]]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$optSt[$arrDatax[$date][$key1][$key2][$key3]['material'][$key4]]."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";text-align:right;'>".hidezerodecimal($arrDatax[$date][$key1][$key2][$key3]['jumlah'][$key4],3)."</td> ";
								$stream.="<td style='color:".$bgcolorsx.";'>".$arrDatax[$date][$key1][$key2][$key3]['keterangan']."</td> ";
							}
						}
						$xy+=1;
					}
						
						
						
				}
			}
		}
	}
	else
	{
						$stream.="<td>".tanggalnormal($date)."</td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
						$stream.="<td></td> ";
	}


	$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
	$stream.="</tr>";

}
    $stream.="</tbody></table></br>";
    
/*echo"<pre>";
print_r($arrDatax);
echo"</pre>";
exit();*/
/*//$stream="";
if($_SESSION['language']=='EN'){
	$stream.="Planting";
}else{
	$stream.="BKM";
}
    
    if($proses=='excel')$stream.="<table cellspacing='1' border='1' class='sortable'>";
      else $stream.="<table cellspacing='1' border='0' class='sortable'>";
      $stream.="<thead class=rowheader>
       <tr>
                <td align=center rowspan=2 >".$_SESSION['lang']['tanggal']."</td>


                <td align=center colspan=5>BKM</td>
                

        </tr>
        <tr>
        		<td align=center>".$_SESSION['lang']['unit']."</td>
                <td align=center>".$_SESSION['lang']['divisi']."</td>
                <td align=center>".$_SESSION['lang']['blok']."</td>
                <td align=center>".$_SESSION['lang']['kegiatan']."</td>
                <td align=center>".$_SESSION['lang']['keterangan']."</td>
        </tr>
                </thead>
      <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr


while (strtotime($tgl1) <= strtotime($tgl2)) {
	$stream.="<tr class=rowcontent>";

	if(!empty($arrDatax[$tgl1]))
	{
		$xy=0;
		foreach ($arrDatax[$tgl1] as $key1 => $val1) {
			foreach ($val1 as $key2 => $val2) {
				foreach ($val2 as $key3 => $val3) {
					foreach ($val3 as $key4 => $val4) {
						if(empty($arrData[$tgl1][$key1][$key2][$key3]))
							{
								$bgcolorsx='#ec1c24';
							}
							else
							{
								$bgcolorsx='';
							}
						if($xy>0)
						{
							
							$stream.="<tr class=rowcontent>";
							$stream.="<td style='color:".$bgcolorsx.";'></td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".@substr($val1,0,4)."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".$key1."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".$key2."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".$optNmKeg[$key3]."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".$val4."</td> ";
							$stream.="</tr>";
						}
						else
						{
							$stream.="<td style='color:".$bgcolorsx.";'>".tanggalnormal($tgl1)."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".@substr($key1,0,4)."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".$key1."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".$key2."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".$optNmKeg[$key3]."</td> ";
							$stream.="<td style='color:".$bgcolorsx.";'>".$val4."</td> ";
						}
						$xy+=1;
					}
				}
			}
		}
	}
	else
	{
							if(!empty($arrData[$tgl1]))
							{
								$bgcolorsx='#ec1c24';
							}
							else
							{
								$bgcolorsx='';
							}
						$stream.="<td style='color:".$bgcolorsx.";'>".tanggalnormal($tgl1)."</td> ";
						$stream.="<td style='color:".$bgcolorsx.";'></td> ";
						$stream.="<td style='color:".$bgcolorsx.";'></td> ";
						$stream.="<td style='color:".$bgcolorsx.";'></td> ";
						$stream.="<td style='color:".$bgcolorsx.";'></td> ";
						$stream.="<td style='color:".$bgcolorsx.";'></td> ";
	}


	$tgl1 = date ("Y-m-d", strtotime("+1 day", strtotime($tgl1)));
	$stream.="</tr>";

}
    $stream.="</tbody></table>";*/

/*$str2=" 	SELECT * FROM ".$dbname.".kebun_perawatan_vw 
        WHERE tanggal between '".$tgl1."' and '".$tgl2."' ".$kdorgsch." "
        . " and intex=2 and kodetransaksi='PNB' order by tanggal ";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_ASSOC);

//$str1eam.="<tr bgcolor=#FFFFFF>";
    $stream.="<tr class=rowcontent>";
    $stream.="
    <td>".tanggalnormal($bar1['tanggal'])."</td>    
    <td>".substr($bar1['divisi'],0,4)."</td>
    <td>".$bar1['divisi']."</td>
    <td>".$bar1['kodeblok']."</td>
    <td align=right>".$optNmKeg($bar1['kodekegiatan'])."</td> 
    </tr>";	*/

#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
######HTML
	case 'preview':
		//print_r($arrDatax);
		echo $stream;
    break;

######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_RKH_vs_BKM_".$tglSkrg;
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
			//closedir($handle);
		}           
		break;
	
		case 'getdiv':

		$optdiv="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sql = "select distinct kodeorganisasi,namaorganisasi 
		        from ".$dbname.".organisasi 
		        where induk='".$kdorg."' and (tipe='AFDELING' or tipe='BIBITAN') order by kodeorganisasi asc";
		$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($data=$qry->fetch())
		{
			$optdiv.="<option value=".$data['kodeorganisasi'].">".$data['kodeorganisasi']." - ".$data['namaorganisasi']."</option>";
		}          

		echo $optdiv;
		
		break;
	
###############	
#panggil PDFnya
###############
	
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
							global $kdorg;
							global $kdAfd;
							global $tgl1;
							global $tgl2;
							global $where;
							global $nmOrg;
							global $lok;
							global $notrans;
							

                            //$cols=247.5;
                            $query = selectQuery($dbname,'organisasi','alamat,telepon',
                                "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                            $orgData = fetchData($query);

                            $width = $this->w - $this->lMargin - $this->rMargin;
                            $height = 20;
                            $path='images/logo.jpg';
                            //$this->Image($path,$this->lMargin,$this->tMargin,50);	
							$this->Image($path,30,15,55);
                            $this->SetFont('Arial','B',9);
                            $this->SetFillColor(255,255,255);	
                            $this->SetX(90); 
							  
                            $this->Cell($width-80,12,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                            $this->SetX(90); 		
			                $this->SetFont('Arial','',9);
							$height = 12;
                            $this->Cell($width-80,$height,$orgData[0]['alamat'],0,1,'L');	
                            $this->SetX(90); 			
                            $this->Cell($width-80,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                            $this->Ln();
                            $this->Line($this->lMargin,$this->tMargin+($height*4),
                            $this->lMargin+$width,$this->tMargin+($height*4));

                            $this->SetFont('Arial','B',12);
                                            $this->Ln();
                            $height = 15;
                                            $this->Cell($width,$height,"Laporan Harga TBS ".$kdorg,'',0,'C');
                                            $this->Ln();
                            $this->SetFont('Arial','',10);
                                            $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." : ". tanggalnormal($tgl1)." S/D ". tanggalnormal($tgl2),'',0,'C');
											//$this->Ln();
                                            $this->Ln(30);
                            $this->SetFont('Arial','B',7);
                            $this->SetFillColor(220,220,220);
                                            $this->Cell(3/100*$width,15,substr($_SESSION['lang']['nomor'],0,2),1,0,'C',1);		
                                            $this->Cell(15/100*$width,15,'Supplier',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Tanggal',1,0,'C',1);
											$this->Cell(10/100*$width,15,'BJR',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Harga Satuan',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Netto',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Sortasi',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Berat Normal',1,0,'C',1);
											$this->Cell(10/100*$width,15,'Total',1,1,'C',1);	
											
											
		
											//$this->Ln();
                       }

                        function Footer()
                        {
                            $this->SetY(-15);
                            $this->SetFont('Arial','I',8);
                            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
                        }
                    }
                    $pdf=new PDF('P','pt','A4');
                    $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                    $height = 15;
                            $pdf->AddPage();
                            $pdf->SetFillColor(255,255,255);
                            $pdf->SetFont('Arial','',7);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);//tinggal tarik $res karna sudah di declarasi di atas
		$no=0;
		$ttl=0;
		while($bar=$res->fetch())
		{	
			
			$bjr=$bar['bjr'];
	//echo $bjr._;
	if($bjr>=3 && $bjr<5)
	{
		$a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='1' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
	}
	else if($bjr>=5 && $bjr<7)
	{
		$a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='2' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
		//echo $a;
	}
	else
	{
		$a="select harga from ".$dbname.".pabrik_5hargatbs where bjr='3' and tanggal='".$bar['tanggal']."' and supplierid='".$bar['kodecustomer']."'";
		//echo $a;
	}
	$b=$owlPDO->query($a) or die(print " Gagal: ".PDOException::getMessage());
	$b->setFetchMode(PDO::FETCH_ASSOC);
	$c=$b->fetch();
	
	$la="select disticnt kodetimbangan from ".$dbname.".log_5supplier";
	$li=$owlPDO->query($la) or die(print " Gagal: ".PDOException::getMessage());
	$li->setFetchMode(PDO::FETCH_ASSOC);
	$lu=$li->fetch();
		$supz=$lu['kodetimbangan'];
	
	if($supz=='')
	{
		$sNm="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$bar['kodecustomer']."'  ";
	}
	else
	{
		$sNm="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$bar['kodecustomer']."'  ";
	}	
	$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
	$qNm->setFetchMode(PDO::FETCH_ASSOC);
	$rNm=$qNm->fetch();
		$nm=$rNm['namasupplier'];
		
				$beratnormal=$bar['netto']-$bar['kgpotsortasi'];//and supplierid='".$bar['kodecustomer']."'
		$total=$c['harga']*$beratnormal;//<td>".$optnamacostumer[$bar['kodecustomer']]."</td>
		
		
		
				//echo $sNm;			
			$no+=1;	
			$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);	
			$pdf->Cell(15/100*$width,$height,$nm,1,0,'L',1);		
			$pdf->Cell(10/100*$width,$height,tanggalnormal($bar['tanggal']),1,0,'L',1);		
			$pdf->Cell(10/100*$width,$height,hidezerodecimal($bar['bjr'],2),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,hidezerodecimal($c['harga']),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,$bar['netto'],1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,$bar['kgpotsortasi'],1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,$beratnormal,1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,hidezerodecimal($total),1,1,'R',1);	
		
		$tonetto+=$bar['netto'];
		$tosortasi+=$bar['kgpotsortasi'];
		$toberatnormal+=$beratnormal;
		$tototal+=$total;
											/*
				<td>".$no."</td>
				<td>".$nm."</td>
				<td>".tanggalnormal($bar['tanggal'])."</td>
				<td align=right>".hidezerodecimal($bar['bjr'],2)."</td>
				<td align=right>".hidezerodecimal($c['harga'])."</td>
				<td align=right>".hidezerodecimal($bar['netto'],2)."</td>
				
				<td align=right>".$bar['kgpotsortasi']."</td>
				<td align=right>".$beratnormal."</td>
				<td align=right>".hidezerodecimal($total)."</td>
											
											
											*/
		
		
		/*
		$tonetto+=$bar['netto'];
		$tosortasi+=$bar['kgpotsortasi'];
		$toberatnormal+=$beratnormal;
		$tototal+=$total;
					
	}
	$stream.="
				<thead><tr>
					<td align=center colspan=5>Total</td>
					<td align=right>".hidezerodecimal($tonetto,2)."</td>
					<td align=right>".hidezerodecimal($tosortasi,2)."</td>
					<td align=right>".hidezerodecimal($toberatnormal,2)."</td>
					<td align=right>".hidezerodecimal($tototal)."</td>
		
		*/
		//$totnet+=$bar['netto'];
		
		}
			$pdf->SetFillColor(220,220,220);
			//$pdf->SetFont('arial','B',10);
			$pdf->Cell(48/100*$width,$height,strtoupper('Total'),1,0,'C',1);
					
			$pdf->Cell(10/100*$width,$height,hidezerodecimal($tonetto,2),1,0,'R',1);	
			$pdf->Cell(10/100*$width,$height,hidezerodecimal($tosortasi,2),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,hidezerodecimal($toberatnormal,2),1,0,'R',1);
			$pdf->Cell(10/100*$width,$height,hidezerodecimal($tototal),1,1,'R',1);
//		
		
		$pdf->Output();
            
	break;

	
	
	default:
	break;
}

?>