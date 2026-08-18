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
    case "TB":
    $title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
    break;
    default:
    echo "Error : Atribut not Defined";
    exit;
    break;
}
$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);

// Init Total
$totJanjang=$totUpahKerja=$totUpahKerjapenalty=$totUpahPremi=$totLuasHa=0;
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
        $tab.="<br />".$titleDetail[0]."<br />";

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
		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
            }else{
                $url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal."mobile/index.php/api/access_token/api_key";
        }
		$getApi->init($url,$options);

		if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
			if (strlen($expri[1]) <= 7){
                $url = $_SERVER['HTTP_ORIGIN'].'/'.$expri[1].'/mobile/index.php/api/module/mharvest/getDetail/send';
            }else{
                $url = $_SERVER['HTTP_ORIGIN'].'/mobile/index.php/api/module/mharvest/getDetail/send';
            }
        }else{
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal.'mobile/index.php/api/module/mharvest/getDetail/send';
        }
		$dataParam = array(
			'notransaksi' => $notran
		);
		
		$data = $getApi->post($url,$dataParam);

        // echo "<pre>";
		// print_r($data->response);
		// echo "</pre>";
        foreach ($data->response['result']['kebun_prestasi_mobile'] as $key => $bar){
            $notrans = $bar['notransaksi'];
        }
        // echo $notrans;
        $tab.="<button onclick=\"postingMobileERP('".$notrans."')\" class=mybutton>Download Seluruhnya</button>";
        $tab.="<br/><br/>";

        $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<th align=center>No</th>";
        $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['nik']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['nama']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['tph']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['tahuntanam']."</th>";
        $tab.="<th align=center>Sesi</th>";
        $tab.="<th align=center>".$_SESSION['lang']['photo']." </th>";
        $tab.="<th align=center>".$_SESSION['lang']['photo']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['hasilkerja2']." (HA)</th>";
        $tab.="<th align=center>".$_SESSION['lang']['hasilkerja']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['brondolan']."</th>";
        $tab.="</tr></thead><tbody>";
        
        $no=0;
        foreach ($data->response['result']['kebun_prestasi_mobile'] as $key => $bar) {
            $no++;
            $tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td>".$bar['notransaksi']."</td>";
                $tab.="<td>".$bar['nik']."</td>";
                $tab.="<td>".$RnamaKary[$bar['nik']]."</td>";
                $tab.="<td>".$bar['kodeorg']."</td>";
                $tab.="<td align=center>".$bar['tph']."</td>";
                $tab.="<td align=center>".$bar['tahuntanam']."</td>";
                $tab.="<td align=center>".$bar['sesi']."</td>";
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
                $tab.="<td align=right>".$bar['luaspanen']."</td>";
                $tab.="<td align=right>".$bar['hasilkerja']."</td>";
                $tab.="<td align=right>".$bar['brondolan']."</td>";
            $tab.="</tr>";

            @$totLuasHa+=$bar['luaspanen'];
            @$totJanjang+=$bar['hasilkerja'];
            @$totbrondolan+=$bar['brondolan'];
        }

        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=10 align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".$totLuasHa."</td>";
        $tab.="<td align=right>".$totJanjang."</td>";
        $tab.="<td align=right>".$totbrondolan."</td>";
        $tab.="</tr></tbody></table>";

        echo $tab;
    break;
}

?>