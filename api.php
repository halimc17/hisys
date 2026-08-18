<?php
header('Access-Control-Allow-Origin: *');
error_reporting(0);

// require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method 		= checkPostGet('method','');
$type 			= checkPostGet('type','');
$key 			= checkPostGet('key','');

$tahun 			= checkPostGet('tahun','');
$bulan 			= checkPostGet('bulan','');
$tanggal 		= checkPostGet('tanggal','');
$from 			= checkPostGet('from','');
$until 			= checkPostGet('until','');

if($from !=""){
	$tahun = date("Y",strtotime($from));
	$bulan = date("m",strtotime($from));
	$tanggal = date("d",strtotime($from));
}

$pt 			= checkPostGet('pt','');
$unit 			= checkPostGet('unit','');
$divisi 		= checkPostGet('divisi','');
$blok 			= checkPostGet('blok','');

$bahasa 		= checkPostGet('bahasa','');
$limit 			= checkPostGet('limit', '');
$jenis 			= checkPostGet('jenis', '');
$pages 			= checkPostGet('pages', '');
$name 			= checkPostGet('name', '');
$description	= checkPostGet('description', '');
$properties 	= json_decode(checkPostGet('properties', ''));
$geometry 		= json_decode(checkPostGet('geometry', ''));

$response 		= array();
$where      	= 'AND ';

$str = "select kodeorganisasi from ".$dbname.".organisasi where tipe = 'PT'";

if($pt != '') {
	$str = "select kodeorganisasi from ".$dbname.".organisasi where induk = '".$pt."'";
}


$res 		= fetchdata($str);
$ptArray  	= array();
foreach ($res as $row) {
    $ptArray[] = $row['kodeorganisasi'];
}

if ($unit != '') {
	$ptArray = array();
}

$color 		= array(
	"f0f8ff",
	"faebd7",
	"00ffff",
	"7fffd4",
	"f0ffff",
	"f5f5dc",
	"ffe4c4",
	"000000",
	"ffebcd",
	"0000ff",
	"8a2be2",
	"a52a2a",
	"deb887",
	"5f9ea0",
	"7fff00",
	"d2691e",
	"ff7f50",
	"6495ed",
	"fff8dc",
	"dc143c",
	"00008b",
	"008b8b",
	"b8860b",
	"a9a9a9",
	"006400",
	"a9a9a9",
	"bdb76b",
	"8b008b",
	"556b2f",
	"ff8c00",
	"9932cc",
	"8b0000",
	"e9967a",
	"8fbc8f",
	"483d8b",
	"2f4f4f",
	"2f4f4f",
	"00ced1",
	"9400d3",
	"ff1493",
	"00bfff",
	"696969",
	"696969",
	"1e90ff",
	"b22222",
	"fffaf0",
	"228b22",
	"ff00ff",
	"dcdcdc",
	"f8f8ff",
	"daa520",
	"ffd700",
	"808080",
	"008000",
	"adff2f",
	"808080",
	"f0fff0",
	"ff69b4",
	"cd5c5c",
	"4b0082",
	"fffff0",
	"f0e68c",
	"fff0f5",
	"e6e6fa",
	"7cfc00",
	"fffacd",
	"add8e6",
	"f08080",
	"e0ffff",
	"fafad2",
	"d3d3d3",
	"90ee90",
	"d3d3d3",
	"ffb6c1",
	"ffa07a",
	"20b2aa",
	"87cefa",
	"778899",
	"778899",
	"b0c4de",
	"ffffe0",
	"00ff00",
	"32cd32",
	"faf0e6",
	"ff00ff",
	"800000",
	"66cdaa",
	"0000cd",
	"ba55d3",
	"9370db",
	"3cb371",
	"7b68ee",
	"00fa9a",
	"48d1cc",
	"c71585",
	"191970",
	"f5fffa",
	"ffe4e1",
	"ffe4b5",
	"ffdead",
	"000080",
	"fdf5e6",
	"808000",
	"6b8e23",
	"ffa500",
	"ff4500",
	"da70d6",
	"eee8aa",
	"98fb98",
	"afeeee",
	"db7093",
	"ffefd5",
	"ffdab9",
	"cd853f",
	"ffc0cb",
	"dda0dd",
	"b0e0e6",
	"800080",
	"663399",
	"ff0000",
	"bc8f8f",
	"4169e1",
	"8b4513",
	"fa8072",
	"f4a460",
	"2e8b57",
	"fff5ee",
	"a0522d",
	"c0c0c0",
	"87ceeb",
	"6a5acd",
	"708090",
	"708090",
	"fffafa",
	"00ff7f",
	"4682b4",
	"d2b48c",
	"008080",
	"d8bfd8",
	"ff6347",
	"40e0d0",
	"ee82ee",
	"f5deb3",
	"ffffff",
	"f5f5f5",
	"ffff00",
	"9acd32"
);

// return d == "01" ? '#800026' :
        //     d == "02" ? '#BD0026' :
        //     d == "03" ? '#E31A1C' :
        //     d == "04" ? '#FC4E2A' :
        //     d == "05" ? '#FD8D3C' :
        //     d == "06" ? '#FEB24C' :
        //     d == "07" ? '#FED976' : '#FFEDA0';
        //     
$colorFilter = array(
	'000000',
	'1a0000',
	'330000',
	'4d0000',
	'660000',
	'800026',
	'BD0026',
	'E31A1C',
	'FC4E2A',
	'FD8D3C',
	'FEB24C',
	'FED976',
	// 'FFEDA0',
	'fed667',
	'fecf4d',
	'fec834',
	'fec11b',
	'feba01',
	'fefe01',
);

switch ($method) {
	case 'getPT':
		$str = "select kodeorganisasi from ".$dbname.".organisasi where tipe = 'PT'";
		$res = fetchdata($str);
		$pt  = array();
		foreach ($res as $row) {
		    $pt[$row['kodeorganisasi']] = 0;
		}

		$response['data'] = $pt;
	break;

	case 'biGraphic':
		$labels     = array();
        $data       = array();
        $language 	= array();
        $pabrik   	= array();
        $kebun    	= array();

        $where1     = '';
        $group      = 'GROUP BY LEFT(tanggal, 4)';
        $group1     = '';
        $select     = ', LEFT(tanggal, 4) AS x';
        $select1    = '';
        $order      = '';
        $order1     = '';

        $jmlCpo = $jmlKernel    = $jmlTbs   = $hasil       = $dasar      = 0;
        $xCpo   = $xKrn         = $xTbs     = $xPabrik     = $xKebun     = array();
        $yCpo   = $yKrn         = $yTbs     = $yPabrik     = $yKebun     = array();

        $languageQuery 	= "SELECT * FROM ".$dbname.".bahasa";
        $res 			= fetchdata($languageQuery);
        foreach ($res as $row) {
            $language[$row['legend']]['ID']  = $row['ID'];
            $language[$row['legend']]['MY']  = $row['MY'];
            $language[$row['legend']]['EN']  = $row['EN'];
        }

        $label = $language['tahun'][$bahasa];

        if ($tahun != '') {
            $where  = 'LEFT(tanggal, 4) = \''.$tahun.'\' ';
            $group  = 'GROUP BY LEFT(tanggal, 7)';
            $select = ', SUBSTR(tanggal, 6, 2) AS x';
            $label  = $language['bulan'][$bahasa];
            $order  = ' ORDER BY LEFT(tanggal, 7) ASC';
        }

        if ($bulan != '') {
            $where  = 'LEFT(tanggal, 7) = \''.$tahun.'-'.$bulan.'\'';
            $group  = 'GROUP BY SUBSTR(tanggal, 9, 2)';
            $select = ', SUBSTR(tanggal, 9, 2) AS x';
            $label  = $language['tanggal'][$bahasa];
            $order  = ' ORDER BY SUBSTR(tanggal, 9, 2) ASC';
        }

        if ($tanggal != '') {
            $where  = 'LEFT(tanggal, 10) = \''.$tahun.'-'.$bulan.'-'.$tanggal.'\'';
			if($until != ""){
				$where = " LEFT(tanggal, 10) BETWEEN '".$tahun."-".$bulan."-".$tanggal."' and '".$until."' ";
			}
		}

        if ($pt != '') {
            if ($where != '') {
                $where1 .= 'AND ';
            }
            $where1  .= 'LEFT(a.kodeorg, 4) IN (SELECT kodeorganisasi FROM '.$dbname.'.organisasi WHERE induk = \''.$pt.'\') ';

            if ($unit != '') {
                if ($where != '') {
                    $where1 .= 'AND ';
                }
                $where1  .= 'LEFT(a.kodeorg, 4) = \''.$unit.'\' ';

                $select1 = ', LEFT(a.kodeorg, 4) AS z';
            } else {
                $select1 = ', LEFT(a.kodeorg, 4) AS z';
                $group1  = ', LEFT(a.kodeorg, 4)'; 
                $order1  = ', LEFT(a.kodeorg, 4) ASC';
            }
        } else {
            $select1 = ', (SELECT induk FROM '.$dbname.'.organisasi WHERE kodeorganisasi = LEFT(a.kodeorg, 4)) AS z';
            $group1  = ', (SELECT induk FROM '.$dbname.'.organisasi WHERE kodeorganisasi = LEFT(a.kodeorg, 4))';
            $order1  = ', (SELECT induk FROM '.$dbname.'.organisasi WHERE kodeorganisasi = LEFT(a.kodeorg, 4)) ASC';
        }

        if ($where == 'AND ') {
            $where = '';
        } else {
            $where = 'AND '.$where;
        }

        $select = $select .= $select1;
        $where  = $where .= $where1;
        $group  = $group .= $group1;
        $order  = $order .= $order1;

        $response['status']    = 200;
        $response['error']     = false;
        $response['message']   = 'Data Success';

        switch ($type) {
        	case 'kebun':
        		$prodKebunQuery 	= "SELECT SUM(a.hasilkerja) AS data".$select." FROM ".$dbname.".kebun_prestasi a LEFT JOIN ".$dbname.".kebun_aktifitas b ON b.notransaksi = a.notransaksi WHERE a.notransaksi LIKE '%PNN%' ".$where.$group.$order."";
        		$res 			 = fetchdata($prodKebunQuery);
	            $kebun['panen']  = 0;
	            // $yKebun['panen'] = array();
	            $xKebun['panen'] = array();
	            $PtReady = array();
	            foreach ($res as $row) {
	                $kebun['panen'] += $row['data'];

	                // if ($yKebun['panen'][$row['x']]['data'] == undefined) {
	                //     $yKebun['panen'][$row['x']]['data'] = 0;
	                // }

	                // $yKebun['panen'][$row['x']][$label]            = $row['x'];
	                // $yKebun['panen'][$row['x']]['data']            += $row['data'];

	                $xKebun['panen'][$row['x']][$label]            			= $row['x'];
	                $xKebun['panen'][$row['x']]['jumlah']          			+= (int) $row['data'];
	                $xKebun['panen'][$row['x']]['listdetail'][$row['z']] 	= (int) $row['data'];
	                $PtReady[$row['x']][] 									= $row['z'];
	            }

	            foreach($PtReady as $thn => $t){
		            foreach($ptArray as $org){
	                	if (!in_array($org, $t)) {
	            			 $xKebun['panen'][$thn]['listdetail'][$org] = 0;
	                	}
	            	}
            	}

	            // sort($yKebun['panen']);
	            sort($xKebun['panen']);

	            $prodKebunQuery = "SELECT SUM(b.jjg) AS data".$select." FROM ".$dbname.".kebun_spbdt b LEFT JOIN ".$dbname.".kebun_spbht a ON b.nospb = a.nospb WHERE 1=1 ".$where.$group.$order."";
	            $res 		   = fetchdata($prodKebunQuery);
	            $kebun['spb']  = 0;
	            // $yKebun['spb'] = array();
	            $xKebun['spb'] = array();
	            foreach ($res as $row) {
	                $kebun['spb'] += $row['data'];

	                // if ($yKebun['spb'][$row['x']]['data'] == undefined) {
	                //     $yKebun['spb'][$row['x']]['data'] = 0;
	                // }

	                // $yKebun['spb'][$row['x']][$label]            = $row['x'];
	                // $yKebun['spb'][$row['x']]['data']            += $row['data'];

	                $xKebun['spb'][$row['x']][$label]            			= $row['x'];
	                $xKebun['spb'][$row['x']]['jumlah']          			+= (int) $row['data'];
	                $xKebun['spb'][$row['x']]['listdetail'][$row['z']] 		= (int) $row['data'];
	                $PtReady[$row['x']][] 									= $row['z'];
	            }

	            foreach($PtReady as $thn => $t){
		            foreach($ptArray as $org){
	                	if (!in_array($org, $t)) {
	            			 $xKebun['spb'][$thn]['listdetail'][$org] = 0;
	                	}
	            	}
            	}

	            // sort($yKebun['spb']);
	            sort($xKebun['spb']);

	            $prodKebunQuery = "SELECT SUM(b.kgwb) AS data".$select." FROM ".$dbname.".kebun_spbdt b LEFT JOIN ".$dbname.".kebun_spbht a ON b.nospb = a.nospb WHERE 1=1 ".$where.$group.$order."";
	            $res 		    = fetchdata($prodKebunQuery);
	            $kebun['realisasi']  = 0;
	            // $yKebun['realisasi'] = array();
	            $xKebun['realisasi'] = array();
	            foreach ($res as $row) {
	                $kebun['realisasi'] += $row['data'];

	                // if ($yKebun['realisasi'][$row['x']]['data'] == undefined) {
	                //     $yKebun['realisasi'][$row['x']]['data'] = 0;
	                // }

	                // $yKebun['realisasi'][$row['x']][$label]            = $row['x'];
	                // $yKebun['realisasi'][$row['x']]['data']            += $row['data'];

	                $xKebun['realisasi'][$row['x']][$label]            			= $row['x'];
	                $xKebun['realisasi'][$row['x']]['jumlah']          			+= (int) $row['data'];
	                $xKebun['realisasi'][$row['x']]['listdetail'][$row['z']] 	= (int) $row['data'];
	                $PtReady[$row['x']][] 										= $row['z'];
	            }

	            foreach($PtReady as $thn => $t){
		            foreach($ptArray as $org){
	                	if (!in_array($org, $t)) {
	            			 $xKebun['realisasi'][$thn]['listdetail'][$org] = 0;
	                	}
	            	}
            	}

	            // sort($yKebun['realisasi']);
	            sort($xKebun['realisasi']);

	            $response['data']['panen']['jumlah']        = $kebun['panen'];
	            $response['data']['panen']['type']          = '';
	            $response['data']['panen']['label']         = $language['panen'][$bahasa];
	            $response['data']['panen']['satuan'] 		= $language['jjg'][$bahasa];
	            $response['data']['panen']['listdata']['label']    = $label;
	            $response['data']['panen']['listdata']['detail']     = $xKebun['panen'];
	            // $response['data']['panen']['y']['label']    = $language['jjg'][$bahasa];
	            // $response['data']['panen']['y']['data']     = $yKebun['panen'];

	            $response['data']['spb']['jumlah']          = $kebun['spb'];
	            $response['data']['spb']['type']            = '';
	            $response['data']['spb']['label']           = $language['tbs'][$bahasa];
	            $response['data']['spb']['satuan'] 			= $language['jjg'][$bahasa];
	            $response['data']['spb']['listdata']['label']      = $label;
	            $response['data']['spb']['listdata']['detail']       = $xKebun['spb'];
	            // $response['data']['spb']['y']['label']      = $language['jjg'][$bahasa];
	            // $response['data']['spb']['y']['data']       = $yKebun['spb'];

	            $response['data']['realisasi']['jumlah']     = $kebun['realisasi'];
	            $response['data']['realisasi']['type']       = '';
	            $response['data']['realisasi']['label']      = $language['tbs'][$bahasa];
	            $response['data']['realisasi']['satuan'] 	 = $language['kg'][$bahasa];
	            $response['data']['realisasi']['listdata']['label'] = $label;
	            $response['data']['realisasi']['listdata']['detail']  = $xKebun['realisasi'];
	            // $response['data']['realisasi']['y']['label'] = $language['jjg'][$bahasa];
	            // $response['data']['realisasi']['y']['data']  = $yKebun['realisasi'];
			break;

			case 'pabrik':
				$gaugeQuery = "SELECT SUM(oer) AS oer, SUM(tbsdiolah) AS tbsolah".$select." FROM ".$dbname.".pabrik_produksi a WHERE 1=1 ".$where.$group.$order."";
	            $res 		= fetchdata($gaugeQuery);
	            foreach ($res as $row) {
	                if ($row['tbsolah'] == 0) {
	                    continue;
	                }
	                $hasil += $row['oer']/$row['tbsolah'];
	            }
	                // exit('error:'.$hasil);


	            $gaugeDasarQuery = "SELECT SUM(oerbunch) AS data FROM ".$dbname.".bgt_produksi_pks_vw a";
	            $res 			 = fetchdata($gaugeDasarQuery);
	            foreach ($res as $row) {
	                $dasar += $row['data'];
	            }

	            // $pabrikQuery = $this->db->query("SELECT SUM(tbsdiolahnetto) AS tbsolah, SUM(oer) AS oer, SUM(oerpk) AS oerpk, SUM(sisatbskemarin) AS stockCpo".$select." FROM pabrik_produksi a WHERE 1=1 ".$where.$group.$order."")->result_array();

	            $pabrikQuery = "SELECT SUM(a.tbsdiolah) AS tbsolah, SUM(a.oer) AS oer, SUM(a.oerpk) AS oerpk, (SELECT SUM(kuantitas) FROM ".$dbname.".pabrik_masukkeluartangki a LEFT JOIN ".$dbname.".pabrik_5tangki b ON b.kodetangki = a.kodetangki WHERE b.komoditi = 'CPO' ".$where.") AS stockCpo, (SELECT SUM(kernelquantity) FROM ".$dbname.".pabrik_masukkeluartangki a LEFT JOIN ".$dbname.".pabrik_5tangki b ON b.kodetangki = a.kodetangki WHERE b.komoditi = 'KER' ".$where.") AS stockKernel".$select." FROM ".$dbname.".pabrik_produksi a WHERE 1=1 ".$where.$group.$order."";
	            $res = fetchdata($pabrikQuery);
	            $pabrik['tbsOlah']   = 0;
	            $pabrik['oilCpo']    = 0;
	            $pabrik['kernel']    = 0;
	            $pabrik['stockCpo']  = 0;
	            $pabrik['stockKrn']  = 0;
	            $xPabrik['tbsOlah']  = array();
	            $xPabrik['oilCpo']   = array();
	            $xPabrik['kernel']   = array();
	            $xPabrik['stockCpo'] = array();
	            $xPabrik['stockKrn'] = array();
	            $xPabrik['tbsOlah']  = array();
	            foreach ($res as $row) {
	                $pabrik['tbsOlah']      += (int) $row['tbsolah'];
	                // if ($yPabrik['tbsOlah'][$row['x']]['data'] == undefined) {
	                //     $yPabrik['tbsOlah'][$row['x']]['data'] = 0;
	                // }

	                // $yPabrik['tbsOlah'][$row['x']][$label]            = $row['x'];
	                // $yPabrik['tbsOlah'][$row['x']]['data']            += $row['tbsolah'];

	                $xPabrik['tbsOlah'][$row['x']][$label]            		= $row['x'];
	                $xPabrik['tbsOlah'][$row['x']]['jumlah']          		+= (int) $row['tbsOlah'];
	                $xPabrik['tbsOlah'][$row['x']]['listdetail'][$row['z']] = (int) $row['tbsolah'];


	                $pabrik['oilCpo']       += (int) $row['oer'];
	                // if ($yPabrik['oilCpo'][$row['x']]['data'] == undefined) {
	                //     $yPabrik['oilCpo'][$row['x']]['data'] = 0;
	                // }

	                // $yPabrik['oilCpo'][$row['x']][$label]            = $row['x'];
	                // $yPabrik['oilCpo'][$row['x']]['data']            += $row['oer'];

	                $xPabrik['oilCpo'][$row['x']][$label]            		= $row['x'];
	                $xPabrik['oilCpo'][$row['x']]['jumlah']          		+= (int) $row['oer'];
	                $xPabrik['oilCpo'][$row['x']]['listdetail'][$row['z']]  = (int) $row['oer'];

	                
	                // $pabrik['kernel']       += $row['oerpk'];
	                // if ($yPabrik['kernel'][$row['x']]['data'] == undefined) {
	                //     $yPabrik['kernel'][$row['x']]['data'] = 0;
	                // }

	                // $yPabrik['kernel'][$row['x']][$label]            = $row['x'];
	                // $yPabrik['kernel'][$row['x']]['data']            += $row['oerpk'];

	                $xPabrik['kernel'][$row['x']][$label]            	   = $row['x'];
	                $xPabrik['kernel'][$row['x']]['jumlah']          	   += (int) $row['oerpk'];
	                $xPabrik['kernel'][$row['x']]['listdetail'][$row['z']] = (int) $row['oerpk'];

	                
	                $pabrik['stockCpo']     += (int) $row['stockCpo'];
	                // if ($yPabrik['stockCpo'][$row['x']]['data'] == undefined) {
	                //     $yPabrik['stockCpo'][$row['x']]['data'] = 0;
	                // }

	                // $yPabrik['stockCpo'][$row['x']][$label]            = $row['x'];
	                // $yPabrik['stockCpo'][$row['x']]['data']            += $row['stockCpo'];

	                $xPabrik['stockCpo'][$row['x']][$label]            		 = $row['x'];
	                $xPabrik['stockCpo'][$row['x']]['jumlah']          		 += (int) $row['stockCpo'];
	                $xPabrik['stockCpo'][$row['x']]['listdetail'][$row['z']] = (int) $row['stockCpo'];

	                $pabrik['stockKernel']     += (int) $row['stockKernel'];
	                // if ($yPabrik['stockKernel'][$row['x']]['data'] == undefined) {
	                //     $yPabrik['stockKernel'][$row['x']]['data'] = 0;
	                // }

	                // $yPabrik['stockKernel'][$row['x']][$label]            = $row['x'];
	                // $yPabrik['stockKernel'][$row['x']]['data']            += $row['stockKernel'];

	                $xPabrik['stockKernel'][$row['x']][$label]            		= $row['x'];
	                $xPabrik['stockKernel'][$row['x']]['jumlah']          		+= (int) $row['stockKernel'];
	                $xPabrik['stockKernel'][$row['x']]['listdetail'][$row['z']] = (int) $row['stockKernel'];                        
	            	// $PtReady[$row['x']][] 										= $row['z'];
	            }

	            // foreach($PtReady as $thn => $t){
		           //  foreach($ptArray as $org){
	            //     	if (!in_array($org, $t)) {
	            // 			 $xPabrik['tbsOlah'][$thn]['listdetail'][$org] = 0;
	            // 			 $xPabrik['oilCpo'][$thn]['listdetail'][$org] = 0;
	            // 			 $xPabrik['stockKernel'][$thn]['listdetail'][$org] = 0;
	            // 			 $xPabrik['stockKernel'][$thn]['listdetail'][$org] = 0;
	            // 			 $xPabrik['stockKernel'][$thn]['listdetail'][$org] = 0;
	            //     	}
	            // 	}
            	// }



	            sort($xPabrik['tbsOlah']);
	            // sort($yPabrik['tbsOlah']);

	            sort($xPabrik['oilCpo']);
	            // sort($yPabrik['oilCpo']);

	            sort($xPabrik['kernel']);
	            // sort($yPabrik['kernel']);

	            sort($xPabrik['stockCpo']);
	            // sort($yPabrik['stockCpo']);

	            sort($xPabrik['stockKernel']);
	            // sort($yPabrik['stockKernel']);

	            $query = "SELECT ffa AS FFACPO, kadarair AS MOISTCPO, kadarkotoran AS DIRTCPO, ffapk AS FFAKERNEL, kadarairpk AS MOISTKERNEL, kadarkotoranpk AS DIRTKERNEL".$select." FROM ".$dbname.".pabrik_produksi a WHERE 1=1 ".$where.$group.$order."";
	            $res   = fetchdata($query);
	            $jml   = 0;
	            $x     = array();
	            foreach ($res as $row) {
	                $jml += $row['data'];

	                $x[$row['x']][$label]            					  = $row['x'];
	                $x[$row['x']]['listdetail'][$row['z']]['ffa'] 		  = $row['FFACPO'];
	                $x[$row['x']]['listdetail'][$row['z']]['moistcpo'] 	  = $row['MOISTCPO'];
	                $x[$row['x']]['listdetail'][$row['z']]['dirtcpo'] 	  = $row['DIRTCPO'];
	                $x[$row['x']]['listdetail'][$row['z']]['ffapk'] 	  = $row['FFAKERNEL'];
	                $x[$row['x']]['listdetail'][$row['z']]['moistkernel'] = $row['MOISTKERNEL'];
	                $x[$row['x']]['listdetail'][$row['z']]['dirtkernel']  = $row['DIRTKERNEL'];
	            }

	            sort($x);

	            $response['data']['kualitas']['jumlah']       		= $jml;
	            $response['data']['kualitas']['type']         		= '';
	            $response['data']['kualitas']['label']        		= $language['kualitas'][$bahasa];
	            $response['data']['kualitas']['listdata']['label']  = $label;
	            $response['data']['kualitas']['listdata']['detail'] = $x;
	            // $response['data']['kualitas']['query'] 				= $query;

	            $response['data']['tbsolah']['jumlah']        		 = $pabrik['tbsOlah'];
	            $response['data']['tbsolah']['type']          		 = '';
	            $response['data']['tbsolah']['label']         		 = $language['tbs'][$bahasa];
	            $response['data']['tbsolah']['satuan'] 		  		 = $language['kg'][$bahasa];
	            $response['data']['tbsolah']['listdata']['label']    = $label;
	            $response['data']['tbsolah']['listdata']['detail']   = $xPabrik['tbsOlah'];
	            // $response['data']['tbsolah']['y']['label']    = $language['kg'][$bahasa];
	            // $response['data']['tbsolah']['y']['data']     = $yPabrik['tbsOlah'];

	            $response['data']['cpo']['jumlah']            	 = $pabrik['oilCpo'];
	            $response['data']['cpo']['type']              	 = '';
	            $response['data']['cpo']['label']             	 = $language['cpo'][$bahasa];
	            $response['data']['cpo']['satuan'] 		  	  	 = $language['kg'][$bahasa];
	            $response['data']['cpo']['listdata']['label']    = $label;
	            $response['data']['cpo']['listdata']['detail']   = $xPabrik['oilCpo'];
	            // $response['data']['cpo']['y']['label']        = $language['kg'][$bahasa];
	            // $response['data']['cpo']['y']['data']         = $yPabrik['oilCpo'];

	            $response['data']['stockCpo']['jumlah']       		 = $pabrik['stockCpo'];
	            $response['data']['stockCpo']['type']         		 = '';
	            $response['data']['stockCpo']['label']        		 = $language['cpo'][$bahasa];
	            $response['data']['stockCpo']['satuan'] 	  		 = $language['kg'][$bahasa];
	            $response['data']['stockCpo']['listdata']['label']   = $label;
	            $response['data']['stockCpo']['listdata']['detail']  = $xPabrik['stockCpo'];
	            // $response['data']['stockCpo']['y']['label']   = $language['kg'][$bahasa];
	            // $response['data']['stockCpo']['y']['data']    = $yPabrik['stockCpo'];

	            $response['data']['kernel']['jumlah']         		= $pabrik['kernel'];
	            $response['data']['kernel']['type']           		= '';
	            $response['data']['kernel']['label']          		= $language['kernel'][$bahasa];
	            $response['data']['kernel']['satuan'] 	  	  		= $language['kg'][$bahasa];
	            $response['data']['kernel']['listdata']['label']    = $label;
	            $response['data']['kernel']['listdata']['detail']   = $xPabrik['kernel'];
	            // $response['data']['kernel']['y']['label']     = $language['kg'][$bahasa];
	            // $response['data']['kernel']['y']['data']      = $yPabrik['kernel'];

	            $response['data']['stockKernel']['jumlah']       		= $pabrik['stockKernel'];
	            $response['data']['stockKernel']['type']         		= '';
	            $response['data']['stockKernel']['label']        		= $language['cpo'][$bahasa];
	            $response['data']['stockKernel']['satuan'] 	  	 		= $language['kg'][$bahasa];
	            $response['data']['stockKernel']['listdata']['label']   = $label;
	            $response['data']['stockKernel']['listdata']['detail']  = $xPabrik['stockKernel'];
	            // $response['data']['stockKernel']['y']['label']   = $language['kg'][$bahasa];
	            // $response['data']['stockKernel']['y']['data']    = $yPabrik['stockKernel'];
			break;

			case 'timbangan':
				
				$cpoQuery = "SELECT SUM(beratbersih) AS data".$select." FROM ".$dbname.".pabrik_timbangan a WHERE kodebarang = '40000001' ".$where.$group.$order."";
	            $res  	  = fetchdata($cpoQuery);
	            foreach ($res as $row) {
	                $jmlCpo += (int) $row['data'];

	                // if ($yCpo[$row['x']]['data'] == undefined) {
	                //     $yCpo[$row['x']]['data'] = 0;
	                // }

	                // $yCpo[$row['x']][$label]            = $row['x'];
	                // $yCpo[$row['x']]['data']            += $row['data'];

	                $xCpo[$row['x']][$label]            		= $row['x'];
	                $xCpo[$row['x']]['jumlah']          		+= (int) $row['data'];
	                $xCpo[$row['x']]['listdetail'][$row['z']] 	= (int) $row['data'];
	            	$PtReady[$row['x']][] 						= $row['z'];
	            }

	            foreach($PtReady as $thn => $t){
		            foreach($ptArray as $org){
	                	if (!in_array($org, $t)) {
	            			 $xCpo[$thn]['listdetail'][$org] = 0;
	                	}
	            	}
            	}

	            // sort($yCpo);
	            sort($xCpo);
				$Varawal =", (SELECT induk FROM ".$dbname.".organisasi WHERE kodeorganisasi = LEFT(a.kodeorg, 4)) AS z";
				$Varreplace = ", ifnull((SELECT induk FROM ".$dbname.".organisasi WHERE kodeorganisasi = LEFT(a.kodeorg, 4)),'EKS') AS z ";
				$select = str_replace($Varawal,$Varreplace,$select);
	            $kernelQuery = "SELECT SUM(beratbersih) AS data".$select." FROM ".$dbname.".pabrik_timbangan a WHERE kodebarang = '40000002' ".$where.$group.$order."";
	            $res  	  	 = fetchdata($kernelQuery);
	            foreach ($res as $row) {
	                $jmlKernel += (int) $row['data'];

	                // if ($yKrn[$row['x']]['data'] == undefined) {
	                //     $yKrn[$row['x']]['data'] = 0;
	                // }

	                // $yKrn[$row['x']][$label]            = $row['x'];
	                // $yKrn[$row['x']]['data']            += $row['data'];

	                $xKrn[$row['x']][$label]            		= $row['x'];
	                $xKrn[$row['x']]['jumlah']          		+= (int) $row['data'];
	                $xKrn[$row['x']]['listdetail'][$row['z']] 	= (int) $row['data'];
	            	$PtReady[$row['x']][] 						= $row['z'];
	            }

	            foreach($PtReady as $thn => $t){
		            foreach($ptArray as $org){
	                	if (!in_array($org, $t)) {
	            			 $xKrn[$thn]['listdetail'][$org] = 0;
	                	}
	            	}
            	}

	            // sort($yKrn);
	            sort($xKrn);
				
	            $tbsQuery = "SELECT millcode,SUM(beratbersih) AS data ".$select." FROM ".$dbname.".pabrik_timbangan a WHERE kodebarang = '40000003' ".$where.$group.$order."";
	            $res  	  = fetchdata($tbsQuery);
	            foreach ($res as $row) {
	                $jmlTbs += (int) $row['data'];

	                // if ($yTbs[$row['x']]['data'] == undefined) {
	                //     $yTbs[$row['x']]['data'] = 0;
	                // }

	                // $yTbs[$row['x']][$label]            = $row['x'];
	                // $yTbs[$row['x']]['data']            += $row['data'];

	                $xTbs[$row['x']][$label]            									= $row['x'];
	                $xTbs[$row['x']]['jumlah']            									+= $row['data'];
	                $xTbs[$row['x']]['detailpt'][$row['millcode']]['jumlah']          		+= (int) $row['data'];
	                $xTbs[$row['x']]['detailpt'][$row['millcode']]['listdetail'][$row['z']] = (int) $row['data'];
	            	$PtReady[$row['x']][] 													= $row['z'];
	            }

	            foreach($PtReady as $thn => $t){
		            foreach($ptArray as $org){
	                	if (!in_array($org, $t)) {
	            			 $xTbs[$thn]['listkosong'][$org] = 0;
	                	}
	            	}
            	}

	            // sort($yTbs);
	            sort($xTbs);

	            $response['data']['cpo']['jumlah']          	= $jmlCpo;
	            $response['data']['cpo']['type']            	= 'line';
	            $response['data']['cpo']['label']           	= $language['cpo'][$bahasa];
	            $response['data']['cpo']['satuan'] 				= $language['kg'][$bahasa];
	            $response['data']['cpo']['listdata']['label']   = $label;
	            $response['data']['cpo']['listdata']['detail']  = $xCpo;
	            // $response['data']['cpo']['y']['label']      = $language['kg'][$bahasa];
	            // $response['data']['cpo']['y']['data']     	= $yCpo;
	            $response['data']['cpo']['query']           	= $tbsQuery;

	            $response['data']['kernel']['jumlah']       		= $jmlKernel;
	            $response['data']['kernel']['type']         		= 'line';
	            $response['data']['kernel']['label']        		= $language['kernel'][$bahasa];
	            $response['data']['kernel']['satuan'] 				= $language['kg'][$bahasa];
	            $response['data']['kernel']['listdata']['label']   	= $label;
	            $response['data']['kernel']['listdata']['detail']  	= $xKrn;
	            // $response['data']['kernel']['y']['label']   = $language['kg'][$bahasa];
	            // $response['data']['kernel']['y']['data']  	= $yKrn;
	            // $response['data']['kernel']['query']        = "SELECT SUM(beratbersih) AS data".$select." FROM pabrik_timbangan a WHERE kodebarang = '40000002' AND ".$where." ".$group."";
	            
	            $response['data']['tbs']['jumlah']          	= $jmlTbs;
	            $response['data']['tbs']['type']            	= 'line';
	            $response['data']['tbs']['label']           	= $language['tbs'][$bahasa];
	            $response['data']['tbs']['satuan'] 				= $language['kg'][$bahasa];
	            $response['data']['tbs']['listdata']['label']   = $label;
	            $response['data']['tbs']['listdata']['detail']  = $xTbs;
	            // $response['data']['tbs']['y']['label']      = $language['kg'][$bahasa];
	            // $response['data']['tbs']['y']['data']     	= $yTbs;
	            // $response['data']['tbs']['query']           = "SELECT SUM(beratbersih) AS data".$select." FROM pabrik_timbangan a WHERE kodebarang = '40000003' AND ".$where." ".$group."";
			break;

			case 'budget':
				$arrayBulan = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
				$query 		= "SELECT SUM(kgsetahun) AS data".$select." FROM ".$dbname.".bgt_produksi_kbn_kg_bi_vw a WHERE 1=1 ".$where.$group.$order."";

				if ($tahun != '') {
					$query 	= "SELECT SUM(kg01) AS kg01, SUM(kg02) AS kg02, SUM(kg03) AS kg03, SUM(kg04) AS kg04, SUM(kg05) AS kg05, SUM(kg06) AS kg06, SUM(kg07) AS kg07, SUM(kg08) AS kg08, SUM(kg09) AS kg09, SUM(kg10) AS kg10, SUM(kg11) AS kg11, SUM(kg12) AS kg12".$select." FROM ".$dbname.".bgt_produksi_kbn_kg_bi_vw a WHERE 1=1 ".$where.$group.$order."";

					if ($bulan != '') {
						$query 	= "SELECT SUM(kg01) AS kg01, SUM(kg02) AS kg02, SUM(kg03) AS kg03, SUM(kg04) AS kg04, SUM(kg05) AS kg05, SUM(kg06) AS kg06, SUM(kg07) AS kg07, SUM(kg08) AS kg08, SUM(kg09) AS kg09, SUM(kg10) AS kg10, SUM(kg11) AS kg11, SUM(kg12) AS kg12".$select." FROM ".$dbname.".bgt_produksi_kbn_kg_bi_vw a WHERE tanggal = '".$tahun."' ".$where1.$group.$order."";
					}
				}

	            $res  	= fetchdata($query);
	            $jml 	= 0;
	            $x 		= array();

	            if ($tahun == '') {
	            	foreach ($res as $row) {
		                $jml += $row['kg'.$bulan];

	                	$x[$row['x']][$label]            		= $row['x'];
	                	$x[$row['x']]['jumlah']          		+= (int) $row['data'];
	                	$x[$row['x']]['listdetail'][$row['z']] 	= (int) $row['data'];
		                $PtReady[$row['x']][] 					= $row['z'];
		            }

		            foreach($PtReady as $thn => $t){
			            foreach($ptArray as $org){
		                	if (!in_array($org, $t)) {
		            			 $x[$thn]['listdetail'][$org] = 0;
		                	}
		            	}
	            	}
	            }
				
	            if ($tahun != '') {
	            	if($bulan == '') {
	            		foreach ($arrayBulan as $bulans){
				            foreach ($res as $row) {
				                $jml += $row['kg'.$bulans];
				                
			                	$x[$bulans][$label]            		 	= $bulans;
				                $x[$bulans]['jumlah']          		 	+= (int) $row['kg'.$bulans];
					            $x[$bulans]['listdetail'][$row['z']] 	= (int) $row['kg'.$bulans];
				                $PtReady[$bulans][] 					= $row['z'];
				            }

				            foreach($PtReady as $thn => $t){
					            foreach($ptArray as $org){
				                	if (!in_array($org, $t)) {
				            			 $x[$thn]['listdetail'][$org] = 0;
				                	}
				            	}
			            	}
			            }
	            	} else {
	            		foreach ($arrayBulan as $bulans){
	            			if ($bulans == $bulan) {
	            				$day  	 = cal_days_in_month(CAL_GREGORIAN, (int) $bulan, $tahun);
	            				for ($i=1; $i <= $day; $i++) {
		            				foreach ($res as $row) {
		            					$perhari = $row['kg'.$bulans] / $day;
						                $jml 	 += (int) $row['kg'.$bulans];
						                
					                	$x[$i][$label]            		= $i;
						                $x[$i]['jumlah']          		+= $perhari;
							            $x[$i]['listdetail'][$row['z']] = (int) $perhari;
						                $PtReady[$i][] 					= $row['z'];
						            }

						            foreach($PtReady as $thn => $t){
							            foreach($ptArray as $org){
						                	if (!in_array($org, $t)) {
						            			 $x[$thn]['listdetail'][$org] = 0;
						                	}
						            	}
					            	}
	            				}
	            			}
			            }
	            	}
	            }

	            

	            sort($x);

	            $response['data']['anggaran']['jumlah']     		= $jml;
	            $response['data']['anggaran']['type']       		= 'line';
	            $response['data']['anggaran']['label']      		= $language['fisik'][$bahasa];
	            $response['data']['anggaran']['listdata']['label'] 	= $label;
	            $response['data']['anggaran']['listdata']['detail'] = $x;
	            // $response['data']['anggaran']['query']				= $query;
			break;

			case 'sales':
				$query = "SELECT supplierid as supplier, SUM(harga) AS data".$select." FROM ".$dbname.".pmn_hargabelitbs a WHERE 1=1 ".$where.$group.", supplierid ".$order."";
	            $res   = fetchdata($query);
	            $jml   = 0;
	            $x 	   = array(); 
	            foreach ($res as $row) {
	                $jml += $row['data'];

	                $x[$row['x']][$label]            						 = $row['x'];
	                $x[$row['x']]['listdetail'][$row['z']]['jumlah'] 		 += $row['data'];
	                $x[$row['x']]['listdetail'][$row['z']][$row['supplier']] = $row['data'];
	            }

	            sort($x);

	            $response['data']['hargabelitbs']['jumlah']       			= $jml;
	            $response['data']['hargabelitbs']['type']         			= 'line';
	            $response['data']['hargabelitbs']['label']        			= $language['harga'][$bahasa];
	            $response['data']['hargabelitbs']['satuan'] 				= $language['rp'][$bahasa];
	            $response['data']['hargabelitbs']['listdata']['label']   	= $label;
	            $response['data']['hargabelitbs']['listdata']['detail']  	= $x;

	            $query = "SELECT SUM(hargasatuan) AS data".$select." FROM ".$dbname.".pmn_kontrakjual_bi_vw a WHERE 1=1 ".$where.$group.$order."";
	            $res   = fetchdata($query);
	            $jml   = 0;
	            $x 	   = array(); 
	            foreach ($res as $row) {
	                $jml += $row['data'];

	                $x[$row['x']][$label]            	   = $row['x'];
	                $x[$row['x']]['jumlah']  			   += $row['data'];
	                $x[$row['x']]['listdetail'][$row['z']] = $row['data'];
	            }

	            sort($x);

	            $response['data']['hargajualcpo']['jumlah']       			= $jml;
	            $response['data']['hargajualcpo']['type']         			= 'line';
	            $response['data']['hargajualcpo']['label']        			= $language['harga'][$bahasa];
	            $response['data']['hargajualcpo']['satuan'] 				= $language['rp'][$bahasa];
	            $response['data']['hargajualcpo']['listdata']['label']   	= $label;
	            $response['data']['hargajualcpo']['listdata']['detail']  	= $x;
			break;
        	
        	default:
        	break;
        }
	break;

	case 'biMap':
		if ($pt != '') {
			$where .= 'pt = "'.$pt.'"';
		}

		if ($jenis == 'point') {
			$where .= 'type = "Point"';
		} else {
			$where .= 'type = "MultiPolygon"';
		}

		switch ($type) {
			case 'getFilter':
				$str 	= "SELECT DISTINCT pt from ".$dbname.".bi_map";
				$res 	= fetchdata($str);
				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$no]['name'] = $row['pt'];

					$str1 	= "SELECT DISTINCT unit from ".$dbname.".bi_map WHERE pt = '".$row['pt']."' ORDER BY unit ASC";
					$res1 	= fetchdata($str1);
					$no1 	= 0;
					foreach ($res1 as $row1) {
						$response['data'][$no]['unit'][$no1]['name'] = $row1['unit'];

						$str2 	= "SELECT DISTINCT divisi from ".$dbname.".bi_map WHERE unit = '".$row1['unit']."' AND (divisi != 'Sungai' AND divisi != 'Jalan') ORDER BY divisi ASC";
						$res2 	= fetchdata($str2);
						$no2 	= 0;
						foreach ($res2 as $row2) {
							$response['data'][$no]['unit'][$no1]['divisi'][$no2]['name'] = $row2['divisi'];
							$no2++;
						}
						$no1++;
					}
					$no++;
				}
			break;

			case 'getData':
				$offset = $pages * $limit;

				$str 	= "select id from ".$dbname.".bi_map where 1=1 ".$where;
				$res 	= fetchdata($str);
				$totalP = ceil(count($res)/$limit);

				$str 	= "select * from ".$dbname.".bi_map where 1=1 ".$where." limit ".$offset.", ".$limit;
				$res 	= fetchdata($str);

				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$no]['coordinates'] 	= $row['coordinates'];
					$response['data'][$no]['properties'] 	= $row['properties'];
					$response['data'][$no]['type'] 			= $row['type'];
					$no++;
				}

				$response['totalPages'] = $totalP;
			break;

			case 'getStylePolygon':
				$offset = $pages * $limit;

				$str 	= "select * from ".$dbname.".bi_map_5warna";
				$res 	= fetchdata($str);
				$totalP = ceil(count($res)/$limit);

				$str 	= "select * from ".$dbname.".bi_map_5warna limit ".$offset.", ".$limit;
				$res 	= fetchdata($str);

				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$row['kodeorg']]['fillColor'] 	= $row['fill'];
					$response['data'][$row['kodeorg']]['weight'] 		= $row['weight'];
					$response['data'][$row['kodeorg']]['opacity'] 		= $row['opacity'];
					$response['data'][$row['kodeorg']]['color'] 		= $row['color'];
					$response['data'][$row['kodeorg']]['dashArray'] 	= $row['dashArray'];
					$response['data'][$row['kodeorg']]['fillOpacity'] 	= $row['fillOpacity'];
					$no++;
				}

				$response['totalPages'] = $totalP;
			break;

			case 'getDataHotspot':
				$offset = $pages * $limit;

				$str 	= "select id from ".$dbname.".bi_map_hotspot";
				$res 	= fetchdata($str);
				$totalP = ceil(count($res)/$limit);

				$str 	= "select * from ".$dbname.".bi_map_hotspot limit ".$offset.", ".$limit;
				$res 	= fetchdata($str);

				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$no]['coordinates'] 	= $row['coordinates'];
					$response['data'][$no]['properties'] 	= htmlspecialchars_decode($row['properties']);
					$response['data'][$no]['type'] 			= $row['type'];
					$no++;
				}

				$response['totalPages'] = $totalP;
			break;

			case 'detailPolygon':
				$str = "select * from ".$dbname.".setup_blok where kodeorg = '".$blok."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();

				$unitt 	 = substr($blok,0,4);
				$divisii = substr($blok,0,6);
				
				$optKdOrg    = makeOption($dbname,'organisasi','kodeorganisasi,induk');
				$optNmOrg    = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

				$response['data']['detail']['pt'] 					= $optNmOrg[$optKdOrg[$unitt]];
				$response['data']['detail']['unit'] 				= $unitt;
				$response['data']['detail']['divisi'] 				= $divisii;
				$response['data']['detail']['blok'] 				= $blok;
				$response['data']['detail']['statusblok'] 			= $bar['statusblok'];
				$response['data']['detail']['tahuntanam'] 			= $bar['tahuntanam'];
				$response['data']['detail']['intiplasma'] 			= ($bar['intiplasma'] == 'P' ? 'Plasma' : 'Inti');
				$response['data']['detail']['jenisbibit'] 			= $bar['jenisbibit'];
				$response['data']['detail']['topografi'] 			= $bar['topografi'];
				$response['data']['detail']['luasareaproduktif'] 	= $bar['luasareaproduktif'];
				$response['data']['detail']['luasareanonproduktif'] = $bar['luasareanonproduktif'];
				$response['data']['detail']['jumlahpokok'] 			= $bar['jumlahpokok'];
				$response['data']['detail']['sph'] 					= $bar['jumlahpokok']/$bar['luasareaproduktif'];
				
				$thnSkrg = date("Y");
				$thnLalu = date("Y")-1;
				
				$arrBulan = array();
				for($i = 0;$i < 12;$i++){
					$val = date("M Y", strtotime("-".$i." month"));
					$key = date("Y-m", strtotime("-".$i." month"));
					$arrBulan[$key] = $val;
				}
				
				$arrAngg = array();
				$str 	 = "select sum(kgwb) as kg, left(tanggal,7) as tanggal from ".$dbname.".kebun_spb_vw where blok = '".$blok."' and left(tanggal,4) in ('".$thnSkrg."','".$thnLalu."') group by left(tanggal,7)";
				$res 	= $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar = $res->fetch()){
					$arrAngg[$bar['tanggal']]['real'] = @($bar['kg']/1000);
				}
				
				$str 	 = "select sum(kg01) as kg01, sum(kg02) as kg02, sum(kg03) as kg03, sum(kg04) as kg04, sum(kg05) as kg05, sum(kg06) as kg06, sum(kg07) as kg07, sum(kg08) as kg08, sum(kg09) as kg09, sum(kg10) as kg10, sum(kg11) as kg11, sum(kg12) as kg12, tahunbudget as tahun from ".$dbname.".bgt_produksi_kbn_kg_vw where kodeblok = '".$blok."' and tahunbudget in ('".$thnSkrg."','".$thnLalu."') group by tahunbudget";
				$res 	 = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar = $res->fetch()){
					$arrAngg[$bar['tahun']."-01"]['anggaran'] = @($bar['kg01']/1000);
					$arrAngg[$bar['tahun']."-02"]['anggaran'] = @($bar['kg02']/1000);
					$arrAngg[$bar['tahun']."-03"]['anggaran'] = @($bar['kg03']/1000);
					$arrAngg[$bar['tahun']."-04"]['anggaran'] = @($bar['kg04']/1000);
					$arrAngg[$bar['tahun']."-05"]['anggaran'] = @($bar['kg05']/1000);
					$arrAngg[$bar['tahun']."-06"]['anggaran'] = @($bar['kg06']/1000);
					$arrAngg[$bar['tahun']."-07"]['anggaran'] = @($bar['kg07']/1000);
					$arrAngg[$bar['tahun']."-08"]['anggaran'] = @($bar['kg08']/1000);
					$arrAngg[$bar['tahun']."-09"]['anggaran'] = @($bar['kg09']/1000);
					$arrAngg[$bar['tahun']."-10"]['anggaran'] = @($bar['kg10']/1000);
					$arrAngg[$bar['tahun']."-11"]['anggaran'] = @($bar['kg11']/1000);
					$arrAngg[$bar['tahun']."-12"]['anggaran'] = @($bar['kg12']/1000);
				}
				

				$response['data']['anggaran'] = $arrAngg;
				
				// $frm[1] .= "<span style='font-size:85%'><i>History produksi 12 bulan (ton)</i></span>
				// <table width=100% cellpadding=0 cellspacing=0>
				// 	<tr>
				// 		<td colspan=2></td>
				// 		<td style='border-left:1px solid rgb(30, 88, 150);padding-left:5px;'></td>
				// 		<td style='text-align:center'><i>Real</i></td>
				// 		<td style='text-align:center'><i>Angg</i></td>
				// 	</tr>";
				// foreach($arrBulan as $key=>$val){
				// 	$widthReal = @((100/$maxAll) * round($arrReal[$key]));
				// 	$widthAngg = @((100/$maxAll) * round($arrAngg[$key]));
				// 	$frm[1] .= "<tr>
				// 		<td rowspan=2 style='width:80px;'>".$val."</td>
				// 		<td style='width:100px;font-size:50%;padding-right:1%'>
				// 			<table cellpadding=0 cellspacing=0 style='width:".$widthReal."%'><tr><td style='background-color:blue'>&nbsp;</td></tr></table>
				// 		</td>
				// 		<td rowspan=2 style='border-left:1px solid rgb(30, 88, 150);padding-left:5px;width:80px;'>".$val."</td>
				// 		<td rowspan=2 style='text-align:right;color:blue'>".number_format($arrReal[$key])."</td>
				// 		<td rowspan=2 style='text-align:right;color:orange'>".number_format($arrAngg[$key])."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='width:100px;font-size:50%;padding-right:1%;padding-bottom:2%;'>
				// 			<table cellpadding=0 cellspacing=0 style='width:".$widthAngg."%;'><tr><td style='background-color:orange;'>&nbsp;</td></tr></table>
				// 		<td>
				// 	</tr>"; 
				// }
				
				// $frm[1] .= "</table>";
				
				// $hfrm[0] = $_SESSION['lang']['detail'];
				// $hfrm[1] = $_SESSION['lang']['produksi'];
				
				// $result .= drawTabBI('FRM', $hfrm, $frm, 120, '');

				// echo $result;
			break;

			case 'insertColorPolygon': 
				$str 	= "select distinct divisi from ".$dbname.".bi_map";
				$res 	= fetchdata($str);

				foreach($res as $row){
					$strInsert = "INSERT INTO ".$dbname.".bi_map_5warna (kodeorg, fill, weight, opacity, color, dashArray, fillOpacity) 
					VALUES ('".$row['divisi']."','".$color['#'.rand(0, 146)]."', 2, 0.5, '".$color['#'.rand(0, 146)]."', 3, 0.7)";
					try{
						$owlPDO->exec($strInsert);
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
			break;

			case 'tahunTanam':
				$str 	= "select distinct tahuntanam from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);
				$d 		= [];
				foreach($res as $row){
					$d[] = $row['tahuntanam'];
				}

				$str 	= "select kodeorg,tahuntanam from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);

				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$row['kodeorg']]['keterangan'] = $row['tahuntanam'];
					$response['data'][$row['kodeorg']]['warna'] 	 = $colorFilter[array_search($row['tahuntanam'], $d)];
					$no++;
				}
			break;

			case 'statusBlok':
				$str 	= "select distinct statusblok from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);
				$d 		= [];
				foreach($res as $row){
					$d[] = $row['statusblok'];
				}

				$str 	= "select kodeorg,statusblok from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);

				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$row['kodeorg']]['keterangan'] = $row['statusblok'];
					$response['data'][$row['kodeorg']]['warna'] 	 = $colorFilter[array_search($row['statusblok'], $d)];
					$no++;
				}
			break;

			case 'topografi':
				$str 	= "select distinct topografi from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);
				$d 		= [];
				foreach($res as $row){
					$d[] = $row['topografi'];
				}

				$str 	= "select kodeorg,topografi from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);

				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$row['kodeorg']]['keterangan'] = $row['topografi'];
					$response['data'][$row['kodeorg']]['warna'] 	 = $colorFilter[array_search($row['topografi'], $d)];
					$no++;
				}
			break;

			case 'jenisBibit':
				$str 	= "select distinct jenisbibit from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);
				$d 		= [];
				foreach($res as $row){
					$d[] = $row['jenisbibit'];
				}

				$str 	= "select kodeorg,jenisbibit from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);

				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$row['kodeorg']]['keterangan'] = $row['jenisbibit'];
					$response['data'][$row['kodeorg']]['warna'] 	 = $colorFilter[array_search($row['jenisbibit'], $d)];
					$no++;
				}
			break;

			case 'intiPlasma':
				$str 	= "select distinct intiplasma from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);
				$d 		= [];
				foreach($res as $row){
					$d[] = $row['intiplasma'];
				}

				$str 	= "select kodeorg,intiplasma from ".$dbname.".setup_blok";
				$res 	= fetchdata($str);

				$no 	= 0;
				foreach ($res as $row) {
					$response['data'][$row['kodeorg']]['keterangan'] = $row['intiplasma'];
					$response['data'][$row['kodeorg']]['warna'] 	 = $colorFilter[array_search($row['intiplasma'], $d)];
					$no++;
				}
			break;
			
			default:
			break;
		}
	break;
	
	default:
	break;
}

$response['size'] = formatBytes(strlen(json_encode($response['data'])));

echo json_encode($response);

function formatBytes($size, $precision = 2) {
    $base 		= log($size, 1024);
    $suffixes 	= array('B', 'KB', 'MB', 'GB', 'TB');   

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}

?>