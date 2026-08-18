<?
error_reporting(0);
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method = checkPostGet('method', '');
$param = $_POST;
if (count($param) == 0) $param = $_GET;

#= makeOption - Convert
// $nopr = makeOption($dbname,"log_2povw","nopo,nopp");
// $supplier = makeOption($dbname,"log_2povw","nopo,kodesupplier");
// $hargasatuan = makeOption($dbname,"log_2povw","nopo,hargasatuan");
// $ppn = makeOption($dbname,"log_2povw","nopo,ppn");
// $nilaipo = makeOption($dbname,"log_2povw","nopo,nilaipo");
// $jumlahpesan = makeOption($dbname,"log_2povw","nopo,jumlahpesan");
// $tanggalPoDt = makeOption($dbname,"log_2povw","nopo,tanggal");
// $tanggalPrDt = makeOption($dbname,"log_prapoht","nopp,tanggal");

$str = "select * from " . $dbname . ".log_prapoht where tanggal LIKE '" . $param['tahun'] . "%'";
$res = fetchData($str);
foreach($res as $bar){
	// $nopr[$bar['nopo']]=$bar['nopp'];
	// $supplier[$bar['nopo']]=$bar['kodesupplier'];
	// $hargasatuan[$bar['nopo']]=$bar['hargasatuan'];
	// $ppn[$bar['nopo']]=$bar['ppn'];
	// $nilaipo[$bar['nopo']]=$bar['nilaipo'];
	// $jumlahpesan[$bar['nopo']]=$bar['jumlahpesan'];
	// $tanggalPoDt[$bar['nopo']]=$bar['tanggal'];
	$tanggalPrDt[$bar['nopp']]=$bar['tanggal'];
}

#= Penanganan Case
switch ($method) {
    case 'getPt':
        #= Convert Kode Unit ke Nama Organisasi
        $convOrg = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi");

        #= Ambil data dari bgt_regional_assignment
        // $where = "";
        // $where .= "regional='".$param['regional']."'";
        // $qRegionalPt = selectQuery($dbname,"bgt_regional_assignment","*",$where);
        // $resRegionalPt = fetchData($qRegionalPt);
        $where = "";
        $where .= "namaregional='" . $param['regional'] . "' group by ptregional";
        $qRegionalPt = selectQuery($dbname, "log_5regionalprocurement", "*", $where);
        $resRegionalPt = fetchData($qRegionalPt);

        #= Definisikan Array Kosong
        $optRegionalPt = array();

        #= Definisikan Option All
        $optRegionalPt = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

        foreach ($resRegionalPt as $val) :
            #= Salah Tidak Di Pakai (Awal)
            # $optRegionalPt .= "<option value=".$val['kodeunit'].">".$val['kodeunit']." - ".$convOrg[$val['kodeunit']]."</option>";
            #= Salah (End)

            // $qRegionOrg = "select induk,namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$val['kodeunit']."'";
            // $resRegionOrg = fetchData($qRegionOrg);

            // foreach($resRegionOrg as $key => $valnya) {
            //     $optRegionalPt .= "<option value=" . $valnya['kodeorganisasi'] . ">" . $valnya['induk'] . " - " . $valnya['namaorganisasi'] . "</option>";
            // }

            $qRegionOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $val['ptregional'] . "'";
            $resRegionOrg = fetchData($qRegionOrg);

            foreach ($resRegionOrg as $key => $valnya) {
                $optRegionalPt .= "<option value=" . $valnya['kodeorganisasi'] . ">" . $valnya['kodeorganisasi'] . " - " . $valnya['namaorganisasi'] . "</option>";
            }

        endforeach;

        #= Kirim Ke JS
        echo $optRegionalPt;
        break;

    case 'getTipe':
        $whereUnit = "";
        // $whereTipe = "";

        #= Kondisi jika pilih sesuai PT
        // if(!empty($param['pt'])) {
        //     $whereTipe .= "kodeorganisasi='".$param['pt']."'";
        // }

        #= Definisikan Option All
        // $optTipeUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

        // #= Ambil data Dropdown dari tabel organisasi
        // // $qTipeUnit = selectQuery($dbname, "organisasi", "tipe",$whereTipe);
        // $qTipeUnit = selectQuery($dbname, "organisasi", "tipe","","",TRUE);
        // $resTipeUnit = fetchData($qTipeUnit);

        // foreach($resTipeUnit as $valTU) :
        //     #= Isi Optionnya
        //     $optTipeUnit .= "<option value=".$valTU['tipe'].">".$valTU['tipe']."</option>";
        // endforeach;

        // echo $optTipeUnit;

        #= V2
        $optTipe = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $optUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		
		
		$where = "1=1";
        if ($param['regional'] != "") {
            $where .= " and induk in (select ptregional from " . $dbname . ".log_5regionalprocurement where namaregional='" . $param['regional'] . "')";
        }
		if ($param['pt'] != "") {
            $where .= " and induk='" . $param['pt'] . "'";
        }
		$where .= " and length(kodeorganisasi)='4'";

        $qTipe = selectQuery($dbname, "organisasi", "tipe", $where, "tipe asc", TRUE);
        $resTipe = fetchData($qTipe);

        foreach ($resTipe as $valTU) {
            $optTipe .= "<option value=" . $valTU['tipe'] . ">" . $valTU['tipe'] . "</option>";
        }

        // if ($param['pt'] != "") {
        //     $whereUnit .= "induk='" . $param['pt'] . "' and length(kodeorganisasi)='4'";
        // }

        //if ($param['pt'] != "") {
            $whereUnit .= "ptregional='" . $param['pt'] . "'";
        //}

        // $qUnit = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi", $whereUnit, "namaorganisasi asc");
        $qUnit = selectQuery($dbname, "log_5regionalprocurement", "ptregional,unitregional", $whereUnit, "unitregional asc");
        $resUnit = fetchData($qUnit);

        foreach ($resUnit as $valUnit) {
            $optUnit .= "<option value=" . $valUnit['unitregional'] . ">" . $valUnit['unitregional'] . "</option>";
        }


        $data = [
            "tipe" => $optTipe,
            "unit" => $optUnit
        ];
        echo json_encode($data);

        // echo $optTipe."##".$optUnit;
        break;

    case 'getUnit':

        #= V1
        #= Check apakah parameter tipe unit kosong
        #= Jika Iya, maka tampilkan semua 
        #= Jika Tidak, maka tampilkan sesuai yang di pilih
        // $whereUnit = "";
        // if(!empty($param['tipeunit'])) {
        //     $whereUnit .= "tipe='".$param['tipeunit']."' and length(kodeorganisasi)=4";
        // }
        #= Akhir V1

        #= V2
        // $whereUnit = "";
        // if(!empty($param['tipeunit'])) {
        //     $whereUnit .= " and tipe='".$param['tipeunit']."'";
        // }

        // if(!empty($param['regional'])) {
        //     #= Dapatkan regionalnya untuk IN di organisasi
        //     $whereRegional = "regional='".$param['regional']."'";
        //     $qRegional = selectQuery($dbname, "bgt_regional_assignment", "*", $whereRegional);
        //     $resRegional = fetchData($qRegional);

        //     foreach($resRegional as $valRegional):
        //         $dataRegional .= "'".$valRegional['kodeunit']."',";
        //     endforeach;

        //     #= Hapus (,) Koma terakhir
        //     $dataRegional = rtrim($dataRegional, ",");

        //     $whereUnit = " and kodeorganisasi IN (" . $dataRegional . ")";
        // }
        #= Akhir V2

        #= Awal V3
        $unitDetailAkses = getOrgDetail(2);
        $whereUnit = " and kodeorganisasi in (".$unitDetailAkses.") ";
        if (!empty($param['regional'])) {
            #= Dapatkan regionalnya untuk IN di organisasi
            $whereRegional = "namaregional='" . $param['regional'] . "'";
            $qRegional = selectQuery($dbname, "log_5regionalprocurement", "*", $whereRegional);
            $resRegional = fetchData($qRegional);

            foreach ($resRegional as $valRegional) :
                $dataRegional .= "'" . $valRegional['unitregional'] . "',";
            endforeach;

            #= Hapus (,) Koma terakhir
            $dataRegional = rtrim($dataRegional, ",");

            $whereUnit = " and kodeorganisasi IN (" . $dataRegional . ")";
        }

        if (!empty($param['tipeunit'])) {
            $whereUnit .= " and tipe='" . $param['tipeunit'] . "'";
        }

        if (!empty($param['pt'])) {
            $whereUnit .= " and induk='" . $param['pt'] . "'";
        }
        #= Akhir V3


        #= Ambil data Dropdown dari tabel Organisasi
        $qUnit = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi", "1=1" . $whereUnit);
        $resUnit = fetchData($qUnit);

        #= Definisikan array kosong
        $optUnit = array();

        #= All Data
        $optUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

        foreach ($resUnit as $valUnit) :
            $optUnit .= "<option value=" . $valUnit['kodeorganisasi'] . ">" . $valUnit['kodeorganisasi'] . " - " . $valUnit['namaorganisasi'] . "</option>";
        endforeach;

        #= Kirim ke JS
        echo $optUnit;
        break;

    case 'preview2':
        $periodeawalx = substr($param['periodeawal'], 0, 4);
        $periodeakhirx = substr($param['periodeakhir'], 0, 4);

        if ($periodeakhirx != $periodeawalx) :
            exit('Warning : Tahun Periode Awal dan Akhir harus sama!');
        endif;


        #= Ambil Data untuk di jadikan Header Tabel
        $qHeadKategori = selectQuery($dbname, "log_5kategoribarang", "id");
        $resHeadKategori = fetchData($qHeadKategori);

        if ($param['tipereport'] == 'excel') {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1 border='1'>";
        } else {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
        }

        #= Tabel Header
        $tab .= "<thead>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<th rowspan=2>Month</th>";
        foreach ($resHeadKategori as $valHeadKategori) :
            #= Conv Header
            $convHeadKategori = makeOption($dbname, "log_5kategoribarang", "id,jenis");
            $tab .= "<th rowspan=2>" . $convHeadKategori[$valHeadKategori['id']] . "</th>";
        endforeach;
        $tab .= "</tr>";
        $tab .= "</thead>";

        $where = "";

        if (!empty($param['pt'])) {
            if (!empty($param['tipeunit'])) {
                $where .= " and kodeorg IN (select induk from organisasi where induk='" . $param['pt'] . "' and tipe='" . $param['tipeunit'] . "')";
            }
            $where .= " and kodeorg='" . $param['pt'] . "'";
        }

        if (empty($param['pt'])) {
            if (!empty($param['tipeunit'])) {

                $p = "";

                if ($param['tipeunit'] == "KEBUN") {
                    $p .= tipeUnit("KEBUN");
                } elseif ($param['tipeunit'] == "HOLDING") {
                    $p .= tipeUnit("HOLDING");
                } elseif ($param['tipeunit'] == "KANWIL") {
                    $p .= tipeUnit("KANWIL");
                    // $p .= "RO";    
                } elseif ($param['tipeunit'] == "PABRIK") {
                    $p .= tipeUnit("PABRIK");
                    // $p .= "M";
                } elseif ($param['tipeunit'] == "BULKING") {
                    $p .= tipeUnit("BULKING");
                    // $p .= "W";
                } elseif ($param['tipeunit'] == "RND") {
                    $p .= tipeUnit("RND");
                    // $p .= "D";
                } elseif ($param['tipeunit'] == "TC") {
                    $p .= tipeUnit("TC");
                    // $p .= "TC";
                }

                $where .= " and substr(nopp,16,20) IN (" . $p . ")";
            }
        }

        // if (!empty($param['pt'])) {
        //     if (!empty($param['tipeunit'])) {
        //         $sql = "select kodeorganisasi from organisasi where induk='" . $param['pt'] . "' and tipe='" . $param['tipeunit'] . "'";
        //         $res = fetchData($sql);
        //         $optnya = "";
        //         foreach ($res as $val) :
        //             $optnya .= "'" . $val['kodeorganisasi'] . "',";
        //         endforeach;

        //         $optnya = rtrim($optnya, ",");
        //         echo $optnya;
        //     } else {
        //         $sql = "select kodeorganisasi from organisasi where induk='" . $param['pt'] . "'";
        //         $res = fetchData($sql);
        //         $optnya = "";
        //         foreach ($res as $val) :
        //             $optnya .= "'" . $val['kodeorganisasi'] . "',";
        //         endforeach;

        //         $optnya = rtrim($optnya, ",");
        //         echo $optnya;
        //     }
        // } 

        // if (!empty($param['regional'])) {
        //     #= Dapatkan regionalnya untuk IN di organisasi
        //     $whereRegional = "regional='" . $param['regional'] . "'";
        //     $qRegional = selectQuery($dbname, "bgt_regional_assignment", "*", $whereRegional);
        //     $resRegional = fetchData($qRegional);
        //     $dataRegional = "";
        //     foreach ($resRegional as $valRegional) :
        //         $dataRegional .= "'" . $valRegional['kodeunit'] . "',";
        //     endforeach;

        //     #= Hapus (,) Koma terakhir
        //     $dataRegional = rtrim($dataRegional, ",");

        //     $wherenya = " kodeorganisasi IN (" . $dataRegional . ")";
        //     $sql = "select distinct induk from organisasi where ".$wherenya."";
        //     $res = fetchData($sql);

        //     foreach ($res as $valx) :
        //         @$dataIn .= "'" . $valx['induk'] . "',";
        //     endforeach;

        //     #= Hapus (,) Koma terakhir
        //     $dataIn = rtrim($dataIn, ",");

        //     $where .= " and kodeorg IN (".$dataIn.")";
        // } 

        #= Tabel Body (Data)      
        // $qPoVw = "select * from ".$dbname. ".log_po_vwv212 where kodeorg='" . $param['pt'] . "' and substr(tanggal,1,7) between '".$param['periodeawal']."' and '". $param['periodeakhir'] ."'";
        // echo $qPoVw = "select substr(tanggal,1,7) as periode, sum(nilaipo) as rupiah, idkategori, kodeorg from ".$dbname. ".log_po_vwv212 where substr(tanggal,1,7) between '".$param['periodeawal']."' and '". $param['periodeakhir'] ."' and stat_release=1 ".$where." group by idkategori,substr(tanggal,6,2)";
        $qPoVw = "select substr(tanggal,1,7) as periode, sum(nilaipo) as rupiah, idkategori, kodeorg from " . $dbname . ".log_2povw where substr(tanggal,1,7) between '" . $param['periodeawal'] . "' and '" . $param['periodeakhir'] . "' and stat_release=1 " . $where . " group by idkategori,substr(tanggal,6,2)";
        $resPoVw = fetchData($qPoVw);

        foreach ($resPoVw as $data) {
            $rupiah[$data['periode']][$data['idkategori']] = $data['rupiah'];
        }

        $tab .= "<tbody>";
        for ($i = $param['periodeawal']; $i <= $param['periodeakhir']; $i++) {
            $tab .= "<tr class=rowcontent>";

            $tab .= "<td align=left>" . numToMonth(substr($i, 5, 8), "E", "long") . "</td>";
            foreach ($resHeadKategori as $valHeadKategori) {
                $tab .= "<td align=right>" . (@$rupiah[$i][$valHeadKategori['id']] == "" ? 0 : numb_format(@$rupiah[$i][$valHeadKategori['id']])) . "</td>";
            }

            $tab .= "</tr>";
        }
        // echo "<pre>";
        // print_r($resHeadKategori);
        $tab .= "</tbody>";


        echo $tab;
        break;

    case 'preview':

        #= Set Periode Awal & Akhir 
        #= Ketika tipe report Summary 
        if ($param['tipereport'] == "summary") {
            $periodeawalx = "01";
            $periodeakhirx = "12";

            $param['periodeawal'] = $param['tahun'] . "-01";
            $param['periodeakhir'] = $param['tahun'] . "-12";
        }

        #= Debug
        // exit('warning'.$param['periodeawal']);

        #= Cek apakah tipe report Quaterly
        if ($param['tipereport'] == "quaterly") {

            #= Get Tahun dengan substr
            $periodeawalx = substr($param['periodeawal'], 0, 4);
            $periodeakhirx = substr($param['periodeakhir'], 0, 4);

            #= Check tahun pada periode awal dan akhir harus sama 
            #= Tidak bisa lihat beda tahun
            if ($periodeakhirx != $periodeawalx) :
                exit('Warning : Tahun Periode Awal dan Akhir harus sama!');
            endif;
        }


        #= Ambil Data untuk di jadikan Header Tabel
        $qHeadKategori = selectQuery($dbname, "log_5kategoribarang", "id");
        $resHeadKategori = fetchData($qHeadKategori);

        if ($param['aksi'] == 'excel') {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1 border=1>";
        } else {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
        }



        #= Inisialisasi Where
        $where = $whereptjasa=$wherept="";

        if(!empty($param['regional'])) {
            #= Dapatkan regionalnya untuk IN di organisasi
            $whereRegional = "namaregional='".$param['regional']."'";
            $qRegional = selectQuery($dbname, "log_5regionalprocurement", "*", $whereRegional);
            $resRegional = fetchData($qRegional);

            foreach($resRegional as $valRegional):
                $dataRegional .= "'".$valRegional['unitregional']."',";
            endforeach;

            #= Hapus (,) Koma terakhir
            $dataRegional = rtrim($dataRegional, ",");
            $whereUnit =" and unit IN (".$dataRegional.")";
        }

        if (!empty($param['pt'])) {
            $wherept .= " and kodeorg='" . $param['pt'] . "'";
            $whereptjasa .= " and pt='" . $param['pt'] . "'";
        }
        
		if (!empty($param['tipeunit'])) {
			$where .= " and unit in (select kodeorganisasi from organisasi where tipe='".$param['tipeunit']."')";
		}	
        
        if (!empty($param['unit'])) {
            $where .= " and unit = '" . $param['unit'] . "'";
        }
        #= Tipe Report bedakan wherenya
        #= Jika Summary ambil berdasarkan tahun
        #= Jika Quaterly ambil berdasarkan range pada bulan dan tahun yang sama (dipilih)
        $whereR = "";
        if ($param['tipereport'] == "summary") {
            $whereR .= " and tanggal LIKE '" . $param['tahun'] . "%'";
        } else {
            $whereR .= " and substr(tanggal,1,7) between '" . $param['periodeawal'] . "' and '" . $param['periodeakhir'] . "'";
        }

		$nilaipo=$subtotal=[];
		$str = "select * from " . $dbname . ".log_poht where 1=1 and tanggal LIKE '" . $param['tahun'] . "%' and stat_release=1";
        $res = fetchData($str);
        foreach ($res as $bar) {
			$nilaipo[$bar['nopo']]=$bar['nilaipo'];
			$subtotal[$bar['nopo']]=$bar['subtotal'];
		}
		
		$_SESSION['tempdt']=[];
		
		
		$undefined=false;
        $qPoVw = "select jumlahpesan,namasupplier,hargasatuan,nopo,kodebarang, substr(tanggal,1,7) as periode, nilaipodt as rupiahdt, idkategori, idkategori2, unit, nopp from " . $dbname . ".log_2povw where 1=1 " . $whereR . " and stat_release=1 ". $whereUnit."" . $where . " " . $wherept . ""; 
        $resPoVw = fetchData($qPoVw);
        foreach ($resPoVw as $data) {
			$data['rupiah']=$nilaipo[$data['nopo']]*($data['rupiahdt']/$subtotal[$data['nopo']]);
			$masuk="";
			$d['nopo']=$data['nopo'];
			$d['nopp']=$data['nopp'];
			$d['kodebarang']=$data['kodebarang'];
			$d['jumlah']=$data['jumlah'];
			$d['jumlahpesan']=$data['jumlahpesan'];
			$d['namasupplier']=$data['namasupplier'];
			$d['hargasatuan']=$data['hargasatuan'];
			$d['rupiah']=$data['rupiah'];
			
            if ($data['idkategori2'] == "") {
				#= Untuk Cek data hidden
				$rupiah[$data['periode']]['rupiahx'] += $data['rupiah'];
				$rupiah[$data['idkategori']]['rupiahs'] += $data['rupiah'];
				$category = explode(",", $data['idkategori']);
				$countCategory = count($category);

				if ($countCategory > 1) {
					for ($x = 0; $x < count($category); $x++) {
						if ($data['idkategori']=='1,4') {
							if(getNamaOrg($data['unit'],'tipe')=='PABRIK'){
								$categori=1;
							}else{
								$categori=4;
							}
						} else {
							$categori=$category[$x];
						}
					}
					
					$rupiah[$data['periode']][$categori] += $data['rupiah'];
					$rupiah[$categori]['rupiahs'] += $data['rupiah'];
					
					$_SESSION['tempdt']['mat'][$categori][$data['periode']][]=$d;
					
					// $masuk.="2";
				} else {
					if ($data['idkategori'] == 1) {
						if(getNamaOrg($data['unit'],'tipe')!='PABRIK'){
							$data['idkategori']=4;
						}else{
							$data['idkategori']=$data['idkategori'];
						}
						$rupiah[$data['periode']][$data['idkategori']] += $data['rupiah'];
						$_SESSION['tempdt']['mat'][$data['idkategori']][$data['periode']][]=$d;
						// $masuk.="3";
					} else {
						$rupiah[$data['periode']][$data['idkategori']] += $data['rupiah'];
						$_SESSION['tempdt']['mat'][$data['idkategori']][$data['periode']][]=$d;
					}
				}
			} else {
				if ($data['idkategori2'] == 1) {					
					if(getNamaOrg($data['unit'],'tipe')!='PABRIK'){
						$data['idkategori2']=4;
					}
				}
				$rupiah[$data['periode']][$data['idkategori2']] += $data['rupiah'];
				$_SESSION['tempdt']['mat'][$data['idkategori2']][$data['periode']][]=$d;
			}
			
			if ($data['idkategori2'] == "" and $data['idkategori'] == "") {
				$rupiah[$data['periode']]['undefined'] += $data['rupiah'];
				$_SESSION['tempdt']['mat']['undefined'][$data['periode']][]=$d;
				$undefined=true;
			}
		}
		
		
		
        #= Ambil Data Kontrakjasa
		$rupiahKj=[];$nokontrakjasa=[];
        $qKontrakJasa = "select kegiatan,noakun,a.notransaksi, substr(a.tanggal,1,7) as periode, (b.rpsatuan) as rupiah, b.idkategori  
		FROM ".$dbname.".log_kontrakjasa a RIGHT JOIN ".$dbname.".log_kontrakjasadt b ON a.notransaksi = b.notransaksi 
		where 1=1 " . $whereR . " ".$whereUnit." ".$where." ".$whereptjasa." and a.posting=1 
		and a.notransaksi not in (select nokontrak from ".$dbname.".log_bakontrakjasa) ORDER BY a.tanggal";
        $resKJ = fetchData($qKontrakJasa);
        #= Mapping Data untuk Kontrak Jasa
        foreach ($resKJ as $dataKJ) {
			if($dataKJ['idkategori']==''){					
				$rupiah[$dataKJ['periode']]['undefined'] += $dataKJ['rupiah'];
				$undefined=true;
			}else{
				$rupiahKj[$dataKJ['periode']][$dataKJ['idkategori']] += $dataKJ['rupiah'];	
			}
        }
		
		
		//$wh=" and a.nokontrak in ('".implode("','",$nokontrakjasa)."')";
		//$strx="select a.*, b.idkategori from ".$dbname.".log_bakontrakjasa a left join ".$dbname.".log_kontrakjasadt b on b.kegiatan=a.kegiatan and a.nokontrak=b.notransaksi where 1=1 ".$wh."";
		
		
		$strx="select a.*, b.idkategori from ".$dbname.".log_bakontrakjasa a left join ".$dbname.".log_kontrakjasadt b on b.kegiatan=a.kegiatan and a.nokontrak=b.notransaksi where 1=1 " . $whereR . "  ".$whereUnit." ".$where." ".$whereptjasa."";
		$resx=fetchdata($strx);
		foreach($resx as $bar){
			$bar['periode']=substr($bar['tanggal'],0,7);
			
			if($bar['idkategori']==''){					
				$rupiah[$bar['periode']]['undefined'] += $bar['jumlah'];
				$undefined=true;
			}else{
				$rupiahKj[$bar['periode']][$bar['idkategori']] += $bar['jumlah'];
			}
		}
		
		if($undefined==true){
			$push=array('id'=>'undefined');
			array_push($resHeadKategori,$push);			
		}
		// echo "<pre>";
        // print_r($rupiahKj);
        // print_r($rupiah);
        // //print_r($masuk);
        // echo "</pre>";
		// exit("error".$qKontrakJasa);
		//echo $dataKJ['rupiah']."<br>";
		
		//$resHeadKategori = 
		
		$convHeadKategori = makeOption($dbname,"log_5kategoribarang","id,jenis");
		$convHeadKategori['undefined']='Undefined';
		
        #= Tabel Header
        $tab .= "<thead>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<th>Month</th>";
        $formattedLabels = [];
        foreach ($resHeadKategori as $valHeadKategori) :
            $totnya = $rupiah[$valHeadKategori['id']]['rupiahs'];
            $noLabels += 1;
            #= Conv Header
            $tab .= "<th " . $hidden . ">" . $convHeadKategori[$valHeadKategori['id']] . "</th>";
            $formattedLabels[] = $convHeadKategori[$valHeadKategori['id']];
        endforeach;
		$tab .= "<th " . $hidden . ">Total</th>";
        $tab .= "</tr>";
        $tab .= "</thead>";

        $tab .= "<tbody>";
        $formattedData = $ttltahun = [];
        $colData = 0;
        for ($i = $param['periodeawal']; $i <= $param['periodeakhir']; $i++) {
            #= Increment kolom (Kolom)
            $colData += 1;
			
            #= Ambil untuk id dan parameter (Bulan)
            $bulanEx = explode("-", $i);
            $exBulan = $bulanEx[0] . $bulanEx[1];
			
            $totalRupiahHidden = $rupiah[$i]['rupiahx'];
            $tab .= "<tr class=rowcontent " . $hidden . ">";
            $tab .= "<td align=left>" . numToMonth(substr($i, 5, 8), "E", "long") . "</td>";
            foreach ($resHeadKategori as $valHeadKategori) {
                #= Get Baris (Baris)
                $rowData = $valHeadKategori['id'];
                if ($param['aksi'] == "html") :
                    #= Bedakan function onclicknya
                    if ($param['tipereport'] == "summary") {
                        #= Jika Summary
                        $funcPopUpDetail = "popupdetail('" . $i . "','" . $valHeadKategori['id'] . "')";
                    } else {
                        #= Jika Quaterly
                        $funcPopUpDetail = "popupdetail('" . $i . "','" . $valHeadKategori['id'] . "')";
                    }
					if($rowData=='undefined'){
						$stcol=";color:red;";
					}else{
						$stcol="";
					}
                endif;
                #= Data
                $tab .= "<td align=right id=" . $exBulan . "_" . $rowData . "_" . $colData . " onclick=" . $funcPopUpDetail . " style=cursor:pointer".$stcol.$hidden.">" .
                    ((@$rupiah[$i][$valHeadKategori['id']]+ @$rupiahKj[$i][$valHeadKategori['id']]) == "" ? "" : numb_format((@$rupiah[$i][$valHeadKategori['id']] + @$rupiahKj[$i][$valHeadKategori['id']])))
                    . "</td>";
                #= Untuk di kirim ke Chart
                $formattedData[] =
                    [$valHeadKategori['id'] => ((@$rupiah[$i][$valHeadKategori['id']]+ @$rupiahKj[$i][$valHeadKategori['id']]) == "" ? "" : numb_format((@$rupiah[$i][$valHeadKategori['id']] + @$rupiahKj[$i][$valHeadKategori['id']])))];
                $ttltahun[$convHeadKategori[$valHeadKategori['id']]] += (@$rupiah[$i][$valHeadKategori['id']] + @$rupiahKj[$i][$valHeadKategori['id']]);
                $ttlbulan[$i] += (@$rupiah[$i][$valHeadKategori['id']] + @$rupiahKj[$i][$valHeadKategori['id']]);
                $grandttl += (@$rupiah[$i][$valHeadKategori['id']] + @$rupiahKj[$i][$valHeadKategori['id']]);
            }
			
			$tab .= "<td align=right>".numb_format($ttlbulan[$i])."</td>";
            $tab .= "</tr>";
        }
		
		// echo "<pre>";
		// print_r($ttltahun);
		
		
        #= Total
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=center>Total</td>";
        foreach ($resHeadKategori as $valHeadKategori) :
			if($valHeadKategori['id']=='undefined'){
				$stcol=";color:red;";
			}else{
				$stcol="";
			}
            $tab .= "<td style=text-align:right".$stcol.$hidden . ">" . ($ttltahun[$convHeadKategori[$valHeadKategori['id']]] == "" ? "" : numb_format($ttltahun[$convHeadKategori[$valHeadKategori['id']]])) . "</td>";
        endforeach;
		$tab .= "<td align=right>".numb_format($grandttl)."</td>";
        $tab .= "</tr>";

        $tab .= "</tbody>";
        $warna = [];
        $qWarna = selectQuery($dbname, "log_5kategoribarang", "color");
        $rWarna = fetchData($qWarna);

        foreach($rWarna as $key => $val):
            $warna[] = $val['color'];
        endforeach;

        // echo "<pre>";
        // print_r($rupiah);
        // exit("Warning: ".$ttltahun);
        // echo $tab."##".json_encode($header)."##".json_encode($rupiah);

        #= Cek jika Excel maka buat filenya
        #= Dan jika html maka tampilkan update data dan data pie chartnya
        // $tab .= "<img src='".$param['canvasimg']."' alt='ea' width='500' height'=300'>";
        // $tab .= "<div id=xxx></div>";
        if ($param['aksi'] == "excel") {
            $nop = "Laporan_Procurement_" . date('Ymd') . ".xls";
            $xls = new HtmlExcel();
            $xls->setCss($css);
            $xls->addSheet('Laporan Procurement', $tab);
            $xls->headers($nop);
            echo $xls->buildFile();
            // exit('warning');
        } else {
            $data = [
                "table" => $tab,
                "labels" => $formattedLabels,
                "values" => $ttltahun,
                "warna" => $warna
            ];
            echo json_encode($data);
			
			// echo $tab;
        }
        break;

    case 'detailRow':
        #= Inisialisasi Where
        $where = "";
        $whereptjasa = "";
        $wherept = "";

        if(!empty($param['regional'])) {
            #= Dapatkan regionalnya untuk IN di organisasi
            $whereRegional = "namaregional='".$param['regional']."'";
            $qRegional = selectQuery($dbname, "log_5regionalprocurement", "*", $whereRegional);
            $resRegional = fetchData($qRegional);

            foreach($resRegional as $valRegional):
                $dataRegional .= "'".$valRegional['unitregional']."',";
            endforeach;

            #= Hapus (,) Koma terakhir
            $dataRegional = rtrim($dataRegional, ",");
            $whereUnit =" and a.unit IN (".$dataRegional.")";
        }

         if (!empty($param['pt'])) {
            $wherept .= " and kodeorg='" . $param['pt'] . "'";
            $whereptjasa .= " and pt='" . $param['pt'] . "'";
        }
        
		if (!empty($param['tipeunit'])) {
			$where .= " and a.unit in (select kodeorganisasi from organisasi where tipe='".$param['tipeunit']."')";
		}	
        
        if (!empty($param['unit'])) {
            $where .= " and a.unit = '" . $param['unit'] . "'";
        }
        
        $whereR = "";
		if($param['idkategori']=='undefined'){
			$whereR .= " and a.tanggal LIKE '" . $param['periode'] . "%' and idkategori = '' and idkategori2 = '' "; 
		}else{			
			$whereR .= " and a.tanggal LIKE '" . $param['periode'] . "%' and (idkategori LIKE '%".$param['idkategori']."%' or idkategori2 LIKE '%".$param['idkategori']."%')"; 
		}
        
        $tab = "";
        $tab .= "<div  class=freezetblload id=listData style=display:block>";
        $tab .= "<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
        $tab .= "<thead>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<td align=center>" . $_SESSION['lang']['nopo'] . "</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . " PO</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>";
        $tab .= "<td align=center>Nama Assignment</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['hargasatuan'] . "</td>";
        $tab .= "<td align=center>PPN</td>";
        $tab .= "<td align=center>Nilai PO</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['nopp'] . "</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . " PR</td>";
        $tab .= "</tr>";
        $tab .= "</thead>";
		
		$str = "select * from " . $dbname . ".log_poht where 1=1 and tanggal LIKE '" . substr($param['periode'],0,4) . "%' and stat_release=1";
        $res = fetchData($str);
        foreach ($res as $bar) {
			$ppnx[$bar['nopo']]=$bar['ppn'];
			$nilaipo[$bar['nopo']]=$bar['nilaipo'];
			$subtotal[$bar['nopo']]=$bar['subtotal'];
			$tanggalpo[$bar['nopo']]=$bar['tanggal'];
		}
		
		// $qPoVw = "select *, nilaipodt as rupiahdt from " . $dbname . ".log_2povw a where 1=1 " . $whereR . " and stat_release=1 ". $whereUnit."" . $where . "" . $wherept . "";
        // $resPoVw = fetchData($qPoVw);
		// if(count($resPoVw)>0){
			// $tab .= "<tr class=rowcontent style=background-color:#48f0b5>";
			// $tab .= "<td colspan=11 align=center><b>MATERIAL</b></td>";
			// $tab .= "</tr>";			
		// }
        // foreach($resPoVw as $data) {
			// if ($data['idkategori2'] == "") {
				// $category = explode(",", $data['idkategori']);
				// $countCategory = count($category);
				// if ($countCategory > 1) {
					// for ($x = 0; $x < count($category); $x++) {
						// if ($data['idkategori']=='1,4') {
							// if(getNamaOrg($data['unit'],'tipe')=='PABRIK'){
								// $categori=1;
							// }else{
								// $categori=4;
							// }
						// } else {
							// $categori=$category[$x];
						// }
					// }
				// } else {
					// if ($data['idkategori'] == 1) {
						// if(getNamaOrg($data['unit'],'tipe')!='PABRIK'){
							// $categori=4;
						// }else{
							// $categori=$data['idkategori'];
						// }
					// } else {
						// $categori=$data['idkategori'];
					// }
				// }
			// } else {
				// if ($data['idkategori2'] == 1) {					
					// if(getNamaOrg($data['unit'],'tipe')!='PABRIK'){
						// $categori=4;
					// }
				// }else {
					// $categori=$data['idkategori2'];
				// }
			// }
			// if($categori==$param['idkategori']){				
				// $data['rupiah']=$nilaipo[$data['nopo']]*($data['rupiahdt']/$subtotal[$data['nopo']]);
				// $data['ppnx']=$ppnx[$data['nopo']]*($data['rupiahdt']/$subtotal[$data['nopo']]);
				// $nomor++;
				// $tab .= "<tr class=rowcontent>";
				// $tab .= "<td>" . $nomor . "</td>";
				// $tab .= "<td>" . $data['nopo'] . "</td>";
				// $tab .= "<td nowrap align=center>" . $tanggalpo[$data['nopo']] . "</td>";
				// $tab .= "<td>" . $data['kodebarang'] . "</td>";
				// $tab .= "<td>" . getNamaBrg($data['kodebarang']) . "</td>";
				// $tab .= "<td align=right>" . numb_format($data['jumlahpesan'],2) . "</td>";
				// $tab .= "<td>" . $data['namasupplier'] . "</td>";
				// $tab .= "<td align=right>" . numb_format($data['hargasatuan'],2) . "</td>";
				// $tab .= "<td align=right>" . numb_format($data['ppnx'],2) . "</td>";
				// $tab .= "<td align=right>" . numb_format($data['rupiah'],2) . "</td>";
				// $tab .= "<td nowrap align=center>" . $data['nopp'] . "</td>";
				// $tab .= "<td>" . $tanggalPrDt[$data['nopp']] . "</td>";
				// $tab .= "</tr>";
				
				// $totalrp+=$data['rupiah'];
			// }
        // }
		
		if(count($_SESSION['tempdt']['mat'][$param['idkategori']][$param['periode']])>0){
			$tab .= "<tr class=rowcontent style=background-color:#48f0b5>";
			$tab .= "<td colspan=11 align=center><b>MATERIAL</b></td>";
			$tab .= "</tr>";			
		}
		
		foreach($_SESSION['tempdt']['mat'][$param['idkategori']][$param['periode']] as $key => $val){
			$val['ppnx']=$ppnx[$val['nopo']]*($val['rupiah']/$subtotal[$val['nopo']]);
				
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td>" . $val['nopo'] . "</td>";
			$tab .= "<td nowrap align=center>" . $tanggalpo[$val['nopo']] . "</td>";
			$tab .= "<td>" . $val['kodebarang'] . "</td>";
			$tab .= "<td>" . getNamaBrg($val['kodebarang']) . "</td>";
			$tab .= "<td align=right>" . numb_format($val['jumlahpesan'],2) . "</td>";
			$tab .= "<td>" . $val['namasupplier'] . "</td>";
			$tab .= "<td align=right>" . numb_format($val['hargasatuan'],2) . "</td>";
			$tab .= "<td align=right>" . numb_format($val['ppnx'],0) . "</td>";
			$tab .= "<td align=right>" . numb_format($val['rupiah'],0) . "</td>";
			$tab .= "<td nowrap align=center>" . $val['nopp'] . "</td>";
			$tab .= "<td>" . $tanggalPrDt[$val['nopp']] . "</td>";
			$tab .= "</tr>";
			
			$totalrp+=$val['rupiah'];
			
		}
		
		if($totalrp>0){			
			$tab .= "<tr class=rowcontent style=background-color:#77d8f8>";
			$tab .= "<td colspan=8>TOTAL MATERIAL</td>";
			$tab .= "<td align=right>" . numb_format($totalrp) . "</td>";
			$tab .= "<td colspan=2></td>";
			$tab .= "</tr>";
		}
		
        $convSupplier = makeOption($dbname, "log_5supplier", "supplierid,namasupplier");
		$whereR = "";
        if($param['idkategori']=='undefined'){
			$whereR .= " and a.tanggal LIKE '" . $param['periode'] . "%' and idkategori = ''"; 
		}else{
			$kategori=[];
			$cat = explode(",",$param['idkategori']);
			foreach($cat as $cate){
				$kategori[$cate]=$cate;
			}
			//$whereR .= " and a.tanggal LIKE '" . $param['periode'] . "%' and idkategori LIKE '%".$param['idkategori']."%' "; 
			$whereR .= " and a.tanggal LIKE '" . $param['periode'] . "%' and idkategori in ('".implode("','",$kategori)."')"; 
		}
		
		$rupiahKj=[];$nokontrakjasa=[];
		$qKontrakJasa = "select a.*,b.*, a.notransaksi, substr(a.tanggal,1,7) as periode, (b.rpsatuan) as rupiah, b.idkategori  
		FROM ".$dbname.".log_kontrakjasa a RIGHT JOIN ".$dbname.".log_kontrakjasadt b ON a.notransaksi = b.notransaksi 
		where 1=1 " . $whereR . " ".$whereUnit." ".$where." ".$whereptjasa." and a.posting=1 
		and a.notransaksi not in (select nokontrak from ".$dbname.".log_bakontrakjasa) ORDER BY a.tanggal";
        $resKJ = fetchData($qKontrakJasa);
		if(count($resKJ)>0){
			$tab .= "<tr class=rowcontent style=background-color:#48f0b5>";
			$tab .= "<td colspan=11 align=center><b>KONTRAK JASA</b></td>";
			$tab .= "</tr>";			
		}
        #= Mapping Data untuk Kontrak Jasa
        foreach ($resKJ as $dataKJ) {
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td>" . $dataKJ['notransaksi'] . "</td>";
			$tab .= "<td>" . $dataKJ['tanggal'] . "</td>";
			$tab .= "<td>" . $dataKJ['noakun'] . "</td>";
			$tab .= "<td>" . $dataKJ['kegiatan'] . "</td>";
			$tab .= "<td></td>";
			$tab .= "<td>" . $convSupplier[$dataKJ['supplierid']] . "</td>";
			$tab .= "<td></td>";
			$tab .= "<td></td>";
			$tab .= "<td align=right>" . numb_format($dataKJ['rupiah']) . "</td>";
			$tab .= "<td></td>";
			$tab .= "<td></td>";
			$tab .= "</tr>";
			
			$totalrpk+=$dataKJ['rupiah'];
        }
		
		if($totalrpk>0){	
			$tab .= "<tr class=rowcontent style=background-color:#77d8f8>";
			$tab .= "<td colspan=8>TOTAL KONTRAK JASA</td>";
			$tab .= "<td align=right>" . numb_format($totalrpk) . "</td>";
			$tab .= "<td colspan=2></td>";
			$tab .= "</tr>";
		}
		
		$wh=" and a.nokontrak in ('".implode("','",$nokontrakjasa)."')";
		$strx="select a.*, b.idkategori from ".$dbname.".log_bakontrakjasa a left join ".$dbname.".log_kontrakjasadt b on b.kegiatan=a.kegiatan and a.nokontrak=b.notransaksi where 1=1 " . $whereR . "  ".$whereUnit." ".$where." ".$whereptjasa."";
		$resx=fetchdata($strx);
		if(count($resx)>0){
			$tab .= "<tr class=rowcontent style=background-color:#48f0b5>";
			$tab .= "<td colspan=11 align=center><b>BA KONTRAK JASA</b></td>";
			$tab .= "</tr>";			
		}
		foreach($resx as $dataKJ){
			$bar['periode']=substr($bar['tanggal'],0,7);
			
			$supid = makeOption($dbname, "log_kontrakjasa", "notransaksi,supplierid","notransaksi='".$dataKJ['nokontrak']."'");
			$tglkontrak = makeOption($dbname, "log_kontrakjasa", "notransaksi,tanggal","notransaksi='".$dataKJ['nokontrak']."'");
			 
			$tab .= "<tr class=rowcontent>";
            $tab .= "<td>" . $dataKJ['notransaksi'] . "</td>";
            $tab .= "<td>" . $dataKJ['tanggal'] . "</td>";
            $tab .= "<td>" . $dataKJ['noakun'] . "</td>";
		$tab .= "<td>" . $dataKJ['kegiatan'] . " <i>( " . $dataKJ['keterangan'] . " )</i></td>";
            $tab .= "<td></td>";
            $tab .= "<td>" . $convSupplier[$supid[$dataKJ['nokontrak']]] . "</td>";
            $tab .= "<td></td>";
            $tab .= "<td></td>";
            $tab .= "<td align=right>" . numb_format($dataKJ['jumlah']) . "</td>";
            $tab .= "<td>" . $dataKJ['nokontrak'] . "</td>";
            $tab .= "<td>" . $tglkontrak[$dataKJ['nokontrak']] . "</td>";
            $tab .= "</tr>";
			
			$totalrpb+=$dataKJ['jumlah'];
		}
		
		if($totalrpb>0){	
			$tab .= "<tr class=rowcontent style=background-color:#77d8f8>";
			$tab .= "<td colspan=8>TOTAL BA KONTRAK JASA</td>";
			$tab .= "<td align=right>" . numb_format($totalrpb) . "</td>";
			$tab .= "<td colspan=2></td>";
			$tab .= "</tr>";
		}
		
		$tab .= "<tr class=rowcontent style=background-color:#46c36c>";
		$tab .= "<td colspan=8>GRAND TOTAL</td>";
		$tab .= "<td align=right>" . numb_format($totalrp+$totalrpk+$totalrpb) . "</td>";
		$tab .= "<td colspan=2></td>";
		$tab .= "</tr>";
		
       
        $tab .= "</table>";
        $tab .= "</div>";

        echo $tab;
	break;
}

function tipeUnit($tipe)
{
    global $dbname;

    #= Select Data Organisasi
    $sql = selectQuery($dbname, "organisasi", "kodeorganisasi", "tipe='" . $tipe . "'");
    $res = fetchData($sql);

    #= Inisialisasi Variabel
    $result = "";

    #= Lakukan perulangan untuk di buat IN TIPE UNIT apa
    #= Function ini untuk query IN di atas
    foreach ($res as $data) :
        $result .= "'" . $data['kodeorganisasi'] . "',";
    endforeach;

    #= Hilangkan koma terakhir
    $result = rtrim($result, ",");

    #= Cetak Data
    return $result;
}
function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}

?>