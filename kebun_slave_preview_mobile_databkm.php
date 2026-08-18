<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include("lib/mharvest/getContentAPI.php");
$getApi = new getContentAPI;

$param = $_GET;
if(!empty($_GET)){$param=$_GET;}else{$param=$_POST;}
$proses = $param['proses'];
$tipe=$param['tipe'];

$notran=$param['notransaksi'];

/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$col1 = 'tanggal,nik,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpenalty,upahpremi,premibasis,rupiahpenalty,luaspanen';
$cols[] = explode(',',$col1);
//$query = selectQuery($dbname,'kebun_prestasi',$col1,
//    "notransaksi='".$param['notransaksi']."'");
$query="select ".$col1." from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";
//exit("Error".$query);
$data[] = fetchData($query);
$align[] = explode(",","L,L,L,R,R,R,R,R");
$length[] = explode(",","10,10,15,10,10,15,15,15");



//getNamakaryawan
$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan)
{
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}



$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi order by namaorganisasi asc";
//exit("Error".$sOrg);
$rDataOrg=fetchData($sOrg);
foreach($rDataOrg as $brOrg =>$rNamaOrg){
    $rNmOrg[$rNamaOrg['kodeorganisasi']]=$rNamaOrg['namaorganisasi'];
}
switch($tipe) {
    case "LC":
    $title = strtoupper("Land Clearing");
    break;
    case "BBT":
    $title = strtoupper($_SESSION['lang']['pembibitan']);
    break;
    case "TBM":
    $title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
    break;
    case "TM":
    $title = strtoupper("UPKEEP-".$_SESSION['lang']['tm']);
    break;
    case "PNN":
    $title = strtoupper($_SESSION['lang']['panen']);
    break;
    case "BKM":
    $title = strtoupper("bkm");
    break;
    case "TB":
    $title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
    break;
    default:
    echo "Error : Atribut not Defined";
    exit;
    break;
}
$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material'],$_SESSION['lang']['absensi']." ".$_SESSION['lang']['umum'], $_SESSION['lang']['header']);
$notrans = "";
// Init Total
$totJanjang=$totUpahKerja=$totUpahKerjapenalty=$totUpahPremi=0;
$totUpahPremibasis=$totUpahDenda=$totLuas=$totSisa=0;

switch ($proses) {
    case'html':
        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $men='menu.css';
          $gen='generic.css';
        }else if($theme=='red'){
          $men='menuRed.css';
          $gen='genericRed.css';  
        }else{
          $men='menuGray.css';
          $gen='genericGray.css';  
        }               
       
        $tab="<link rel=stylesheet type=text/css href=style/".$gen.">";

        // GET URI FOR PRODUCTION
		$expri = explode("/",$_SERVER['REQUEST_URI']);

        $svr=parse_url($_SERVER['HTTP_REFERER']);
        $pat=array();
        $pat=explode('/',$svr['path']);
        $arr = array_filter($pat, function($value) {
            return !is_null($value) && $value !== '';
        });
        $data=[];
        foreach ($arr as $key => $value) {
            if (!strpos($value, ".php")) {
                $data[]=$value;
            }
        }
        $urlocal=$_SERVER['HTTP_ORIGIN'].'/'.implode("/",$data);
        
        $options = array(
			'client_id' => 'USERSYSTEM',
			'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
			'username' => $_SESSION['standard']['username']
		);
		/** GET API KEY */
		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7){
                $url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
            }else{
                $url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal."mobile/index.php/api/access_token/api_key";
        }
		$getApi->init($url,$options);

		// Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Mrawat/getdetailerp/load';
            }else{
                $url = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Mrawat/getdetailerp/load';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal.'mobile/index.php/api/module/Mrawat/getdetailerp/load';
        }
		$dataParam = array(
			'notransaksi' => $notran
		);
		
		$data = $getApi->post($url,$dataParam);

        // echo "<pre>";
        // print_r(count($data->response['result']['Header']));
        // echo "</pre>";

        // $tab.="<br/>";
        // $tab.="<button onclick=\"postingMobileERP('".$data->response['result']['Header']['notransaksi']."')\" class=mybutton>Download Seluruhnya</button>";
        // $tab.="<br/><br/>";

        
        // Jika Transaksi bukan hanya absensi umum di BKM
        if (count($data->response['result']['Header']) > 0) {
            $tab.="<br/>";
            $tab.="<button onclick=\"postingMobileERP('".$data->response['result']['Header']['notransaksi']."','bkm')\" class=mybutton>Download Seluruhnya</button>";
            $tab.="<br/><br/>";

            /* ============================================================ HEADER ============================================================= */

            $tab.=$titleDetail[4]."<br />";
            $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['unit']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['divisi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['nikmandor']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['nikasisten']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['photo']." </th>";
            $tab.="<th align=center>".$_SESSION['lang']['photo']."</th>";
            $tab.="</tr></thead><tbody>";
           
            $tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
                $tab.="<td>".$data->response['result']['Header']['notransaksi']."</td>";
                $tab.="<td>".tanggalnormal($data->response['result']['Header']['tanggal'])."</td>";
                $tab.="<td>".getNamaOrg($data->response['result']['Header']['kodeorg'])."</td>";
                $tab.="<td>".getNamaOrg($data->response['result']['Header']['divisi'])."</td>";
                $tab.="<td>".$RnamaKary[$data->response['result']['Header']['nikmandor']]."</td>";
                $tab.="<td>".$RnamaKary[$data->response['result']['Header']['nikasisten']]."</td>";
                $tab.="<td align=center>
                    <a href='".$data->response['result']['Header']['photo']."' class='popup-img'>
                        <img onclick=\"popupimage()\" class='resiconn' style='height:50px;width:50px;' src='".$data->response['result']['Header']['photo']."'>
                    </a>
                </td>";
                $tab.="<td align=center>
                    <a href='".$data->response['result']['Header']['photo2']."' class='popup-img'>
                        <img onclick=\"popupimage()\" class='resiconn' style='height:50px;width:50px;' src='".$data->response['result']['Header']['photo2']."'>
                    </a>
                </td>";
            $tab.="</tr>";
            $tab.="</tr></tbody></table>";
            
            /* ============================================================ PRESTASI ============================================================= */
            
            $tab.="<br/><br/>".$titleDetail[0]."<br />";
            $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th align=center>No</th>";
            $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['karyawanid']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['nama']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['kodekegiatan']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['namakegiatan']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['photo']." </th>";
            $tab.="<th align=center>".$_SESSION['lang']['photo']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['hasilkerja2']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['satuan']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['jumlahhk']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['upahpremi']."</th>";
            $tab.="</tr></thead><tbody>";
            
            $no=0;
            foreach ($data->response['result']['Prestasi'] as $key => $bar) {
                $no++;
                $tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td>".$data->response['result']['Header']['notransaksi']."</td>";
                    $tab.="<td>".$bar['karyawanid']."</td>";
                    $tab.="<td>".$RnamaKary[$bar['karyawanid']]."</td>";
                    $tab.="<td>".$bar['kodeorg']."</td>";
                    $tab.="<td>".$bar['kodekegiatan']."</td>";
                    $tab.="<td>".$bar['namakegiatan']."</td>";
                    $tab.="<td align=center>
                        <a href='".$bar['photo']."' class='popup-img'>
                            <img onclick=\"popupimage()\" class='resiconn' style='height:50px;width:50px;' src='".$bar['photo']."'>
                        </a>
                    </td>";
                    $tab.="<td align=center>
                        <a href='".$bar['photo2']."' class='popup-img'>
                            <img onclick=\"popupimage()\" class='resiconn' style='height:50px;width:50px;' src='".$bar['photo2']."'>
                        </a>
                    </td>";
                    $tab.="<td align=right>".$bar['hasilkerja']."</td>";
                    $tab.="<td align=right>".$bar['satuan']."</td>";
                    $tab.="<td align=right>".$bar['jumlahhk']."</td>";
                    $tab.="<td align=right>".$bar['hasilkerjapremi']."</td>";
                $tab.="</tr>";
    
                @$totHasilKerja+=$bar['hasilkerja'];
                @$totJumlahHK+=$bar['jumlahhk'];
                @$totPremi+=$bar['hasilkerjapremi'];
            }
    
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=9 align=center>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".$totHasilKerja."</td>";
            $tab.="<td align=right></td>";
            $tab.="<td align=right>".$totJumlahHK."</td>";
            $tab.="<td align=right>".$totPremi."</td>";
            $tab.="</tr></tbody></table>";
    
            /* ============================================================ KEHADIRAN ============================================================= */
            
            $tab.="<br/><br/>".$titleDetail[1]."<br />";
            $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th align=center>No</th>";
            $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['karyawanid']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['nama']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['absensi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['jumlahhk']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['upahpremi']."</th>";
            $tab.="</tr></thead><tbody>";
            
            $no=0;
            foreach ($data->response['result']['Kehadiran'] as $key => $bar) {
                $no++;
                $tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td>".$data->response['result']['Header']['notransaksi']."</td>";
                    $tab.="<td>".$bar['karyawanid']."</td>";
                    $tab.="<td>".$bar['namakaryawan']."</td>";
                    if (!empty($bar['hasilkerja']) || !empty($bar['hasilkerjapremi']) || !empty($bar['jumlahhk'])) {
                        $tab.="<td>H</td>";
                    } else {
                        $tab.="<td></td>";
                    }
                    $tab.="<td align=right>".$bar['jumlahhk']."</td>";
                    $tab.="<td align=right>".$bar['hasilkerjapremi']."</td>";
                $tab.="</tr>";
    
                @$totJumlahHK2+=$bar['jumlahhk'];
                @$totPremi2+=$bar['hasilkerjapremi'];
            }
    
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=5 align=center>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".$totJumlahHK2."</td>";
            $tab.="<td align=right>".$totPremi2."</td>";
            $tab.="</tr></tbody></table>";
    
            /* ============================================================ MATERIAL ============================================================= */
            
            $tab.="<br/><br/>".$titleDetail[2]."<br />";
            $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th align=center>No</th>";
            $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['kodekegiatan']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['namakegiatan']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['sloc']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['kodebarang']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['namabarang']." </th>";
            $tab.="<th align=center>".$_SESSION['lang']['satuan']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['jumlah']."</th>";
            $tab.="</tr></thead><tbody>";
            
            $no=0;
            foreach ($data->response['result']['Material'] as $key => $bar) {
                $no++;
                $tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td>".$data->response['result']['Header']['notransaksi']."</td>";
                    $tab.="<td>".$bar['kodekegiatan']."</td>";
                    $tab.="<td>".$bar['namakegiatan']."</td>";
                    $tab.="<td>".$bar['kodeorg']."</td>";
                    $tab.="<td>".$bar['kodegudang']."</td>";
                    $tab.="<td>".$bar['kodebarang']."</td>";
                    $tab.="<td>".$bar['namabarang']."</td>";
                    $tab.="<td align=right>".$bar['satuan']."</td>";
                    $tab.="<td align=right>".$bar['kwantitas']."</td>";
                $tab.="</tr>";
    
                @$totJmlMatr+=$bar['kwantitas'];
            }
    
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=8 align=center>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right></td>";
            $tab.="<td align=right>".$totJmlMatr."</td>";
            $tab.="</tr></tbody></table>";

            
            /* ============================================================ KEHADIRAN UMUM BKM ============================================================= */
            $tab.="<br/><br/>".$titleDetail[3]."<br />";
            $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th align=center>No</th>";
            $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['karyawanid']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['nama']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['absensi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['jumlahhk']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['upahpremi']."</th>";
            $tab.="</tr></thead><tbody>";

            $no=0;
            foreach ($data->response['result']['kehadiranumum'] as $key => $bar) {
                $no++;
                $tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td>".$data->response['result']['Header']['notransaksi']."</td>";
                    $tab.="<td>".$bar['nik']."</td>";
                    $tab.="<td>".getNamaKaryawan($bar['nik'])."</td>";
                    $tab.="<td>".$bar['absensi']."</td>";
                    $tab.="<td align=right>".$bar['jhk']."</td>";
                    $tab.="<td align=right>".$bar['insentif']."</td>";
                $tab.="</tr>";
                @$totJumlahHK3+=$bar['jhk'];
                @$totPremi3+=$bar['insentif'];
            }
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=5 align=center>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".$totJumlahHK3."</td>";
            $tab.="<td align=right>".$totPremi3."</td>";
            $tab.="</tr></tbody></table>";


            // Jika hanya absensi umum di bkm
        } else {
            // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
            if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
                // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			    if (strlen($expri[1]) <= 7){
                    $urlHead = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Kehadiranumum/getHeader/send';
                }else{
                    $urlHead = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Kehadiranumum/getHeader/send';
                }
            }else{
                // Jika Server local / localhost maka munculkan URL localhost
                $urlHead = $urlocal.'mobile/index.php/api/module/Kehadiranumum/getHeader/send';
            }
            $dataParamHead = array(
                'notransaksi' => $notran
            );
            
            $dataHead = $getApi->post($urlHead,$dataParamHead);

            // foreach ($dataHead->response['result']['data'] as $key => $val) {
            //     $notrans = $val['notransaksi'];
            // }
            
            $notrans = $notran;
            
            $tab.="<br/>";
            $tab.="<button onclick=\"postingMobileERP('".$notrans."','kehadiranumum')\" class=mybutton>Download Seluruhnya</button>";
            $tab.="<br/><br/>";

            // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
            if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
                // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			    if (strlen($expri[1]) <= 7){
                    $urlDt = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/Kehadiranumum/getDetail/send';
                }else{
                    $urlDt = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/Kehadiranumum/getDetail/send';
                }
            }else{
                // Jika Server local / localhost maka munculkan URL localhost
                $urlDt = $urlocal.'mobile/index.php/api/module/Kehadiranumum/getDetail/send';
            }
            $dataParamDt = array(
                'notransaksi' => $notran
            );
            
            $dataDt = $getApi->post($urlDt,$dataParamDt);
            
            // echo "<pre>";
            // print_r($dataDt->response['result']);
            // echo "</pre>";
             /* ============================================================ KEHADIRAN UMUM ============================================================= */
             $tab.="".$titleDetail[3]."<br />";
             $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
             $tab.="<tr class=rowheader>";
             $tab.="<th align=center>No</th>";
             $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
             $tab.="<th align=center>".$_SESSION['lang']['karyawanid']."</th>";
             $tab.="<th align=center>".$_SESSION['lang']['nama']."</th>";
             $tab.="<th align=center>".$_SESSION['lang']['absensi']."</th>";
             $tab.="<th align=center>".$_SESSION['lang']['jumlahhk']."</th>";
             $tab.="<th align=center>".$_SESSION['lang']['upahpremi']."</th>";
             $tab.="</tr></thead><tbody>";
             
             $no=0;
             foreach ($dataDt->response['result']['sdm_absensi'] as $key => $bar) {
                 $no++;
                 $tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
                     $tab.="<td align=center>".$no."</td>";
                     $tab.="<td>".$bar['notransaksi']."</td>";
                     $tab.="<td>".$bar['nik']."</td>";
                     $tab.="<td>".getNamaKaryawan($bar['nik'])."</td>";
                     $tab.="<td>".$bar['absensi']."</td>";
                     $tab.="<td align=right>".$bar['jhk']."</td>";
                     $tab.="<td align=right>".$bar['insentif']."</td>";
                 $tab.="</tr>";
     
                 @$totJumlahHK4+=$bar['jhk'];
                 @$totPremi4+=$bar['insentif'];
             }
     
             $tab.="<tr class=rowcontent>";
             $tab.="<td colspan=5 align=center>".$_SESSION['lang']['total']."</td>";
             $tab.="<td align=right>".$totJumlahHK4."</td>";
             $tab.="<td align=right>".$totPremi4."</td>";
             $tab.="</tr></tbody></table>";
        }

        echo $tab;
    break;
}

?>