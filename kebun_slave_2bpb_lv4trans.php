<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method = checkPostGet('method', '');
$blok = checkPostGet('blok', '');
$per2 = checkPostGet('per2', '');
$tipe = checkPostGet('tipe', '');
$tipeakun = checkPostGet('tipeakun', '');

$tahun=substr($per2,0,4);
$per1=$tahun.'-01';
$tgl1=$tahun.'-01-01';

$satkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');



##ambil tanggal akhir
$str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$per2."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
    $tgl2=$bar['tanggalsampai'];

#bentuk untuk bgt..
$expblnbgt=  explode('-', $per2);
$blnbgt=$expblnbgt[1];

$str="select * from ".$dbname.".setup_blok_tahunan where kodeorg = '".$blok."' and tahun='".str_replace('-', '', $per2)."' ";

//exit('Error :'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($res);
if($numrows==0){

$str="select * from ".$dbname.".setup_blok where kodeorg = '".$blok."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

}
$bar=$res->fetch();
    $luas=$bar['luasareaproduktif'];
    $pkk=$bar['jumlahpokok'];
    $tt=$bar['tahuntanam'];


$stream="";


if ($method=='excel4') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}
$stream.="
        <tr>
            <td bgcolor='black'><font color='#FF0000'><b>Blok</b></font></td>
            <td align=left  bgcolor='black'><font color='#FF0000'><b>".getNamaOrg($blok)."</b></font></td>
        </tr>
		<tr class=rowcontent>
			<td >Luas</td>
            <td align=right>".$luas."</td>
		</tr>
        <tr class=rowcontent>
            <td>TT</td>
            <td align=right>".$tt."</td>
        </tr>
		<tr class=rowcontent>
			<td>Pokok</td>
            <td align=right>".number_format($pkk)."</td>
		</tr>
		</table>";
$stream.="<br>";	


if ($method=='excel4') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}
	$stream.="<thead>
	<tr class='rowheader'>
		<th align='center' rowspan='2'>NO AKUN</th>
		<th align='center' rowspan='2'>KEGIATAN</th>
		<th align='center' rowspan='2'>NAMA KEGIATAN</th>
		<th align='center' rowspan='2'>NO JURNAL</th>
		<th align='center' colspan='2'>TRANSPORT</th>
	</tr>
	<tr>
		<th align='center'>KODE VHC</th>
		<th align='center'>BIAYA</th>
	</tr>	
    </thead>
    <tbody>";

	

$str=" select  nojurnal,noakun,jumlah,kodekegiatan,kodevhc,kodeblok 
		from ".$dbname.".keu_jurnaldt_vw 
		where noakun like '".$tipeakun."%' and 
		kodeblok='".$blok."' and periode='".$per2."' and kodevhc!='' and nojurnal like '%VHC%' ";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	
	$akun[$bar['noakun']]=$bar['noakun'];
	$kodekegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$vhc[$bar['kodevhc']]=$bar['kodevhc'];
	
	$listkeg[$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$listvhc[$bar['noakun']][$bar['kodekegiatan']][$bar['kodevhc']]=$bar['kodevhc'];
	@$biaya[$bar['noakun']][$bar['kodekegiatan']][$bar['kodevhc']]+=$bar['jumlah'];
	$jurnal[$bar['noakun']][$bar['kodekegiatan']][$bar['kodevhc']]=$bar['nojurnal'];
}	
	
$str="	select a.jenispekerjaan,c.namakegiatan,c.satuan,c.noakun,b.tanggal,b.kodevhc,a.notransaksi,a.alokasibiaya,a.jumlah 
		from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi 
		left join ".$dbname.".vhc_kegiatan c on a.jenispekerjaan=c.kodekegiatan 
		where b.tanggal like '".$per2."%' and a.alokasibiaya='".$blok."' and c.noakun='".$tipeakun."' ";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akun[$bar['noakun']]=$bar['noakun'];
	$jenispekerjaan[$bar['jenispekerjaan']]=$bar['jenispekerjaan'];
	$namakegiatan[$bar['jenispekerjaan']]=$bar['namakegiatan'];
	$satuan[$bar['jenispekerjaan']]=$bar['satuan'];
	$notransaksi[$bar['notransaksi']]=$bar['notransaksi'];
	$kodevhc[$bar['noakun']][$bar['jenispekerjaan']][$bar['notransaksi']]=$bar['kodevhc'];
	$kmhm[$bar['noakun']][$bar['jenispekerjaan']][$bar['notransaksi']]=$bar['jumlah'];
	$tanggal[$bar['noakun']][$bar['jenispekerjaan']][$bar['notransaksi']]=$bar['tanggal'];
}

foreach($akun as $noakun)
{
	foreach($kodekegiatan as $kdkeg)
	{
		foreach($vhc as $kdvhc)
		{	
		
			$stream.="<tr class=rowcontent>
						<td>".$noakun."</td>
						<td>".$kdkeg."</td>
						<td>".$nmkeg[$kdkeg]."</td>
						<td  style=cursor:pointer; title='click detail' onclick=detailjurnal('".$jurnal[$noakun][$kdkeg][$kdvhc]."','".$blok."','".$kdkeg."','event')>".$jurnal[$noakun][$kdkeg][$kdvhc]."</td>
						<td>".$kdvhc."</td>
						<td align=right>".number_format($biaya[$noakun][$kdkeg][$kdvhc])."</td>
					  </tr>
			";
			@$st+=$biaya[$noakun][$kdkeg][$kdvhc];
		}
	}
	$stream.="
		<tr  bgcolor=#80FFFE>
			<td colspan='5'>".$_SESSION['lang']['total']."</td>
			<td align=right>".number_format($st)."</td>
		</tr>
		";
	@$gt+=$st;
}
$stream.="
		<tr  bgcolor=#48D1CC>
			<td colspan='5'>".$_SESSION['lang']['grnd_total']."</td>
			<td align=right>".number_format($gt)."</td>
		</tr>
		";
	
#selain pnn

	 
	 
if ($method=='excel4') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}
	$stream.="<thead>
	<tr class='rowheader'>
		<th align='center'>NO AKUN</th>
		<th align='center'>KEGIATAN TRAKSI</th>
		<th align='center'>NAMA KEGIATAN TRAKSI</th>
		<th align='center'>SATUAN</th>
		<th align='center'>NO TRANSAKSI</th>
		<th align='center'>TANGGAL</th>
		<th align='center'>KODE VHC</th>
		<th align='center'>JUMLAH HK/KM</th>
	</tr>
    </thead>
    <tbody>";

$stream.="<br><br>";	
	
foreach($akun as $noakun)
{
	foreach($jenispekerjaan as $kdkeg)
	{
		foreach($notransaksi as $notran)
		{
			$stream.="<tr class=rowcontent>
						<td>".$noakun."</td>
						<td>".$kdkeg."</td>
						<td>".$namakegiatan[$kdkeg]."</td>
						<td>".$satuan[$kdkeg]."</td>
						<td  style=cursor:pointer; title='click detail'  onclick=\"masterPDF('vhc_runht','".$notran.",".$kodevhc[$noakun][$kdkeg][$notran]."','','vhc_slave_pekerjaanPrint',event);\">".$notran."</td>
						<td>".tanggalnormal($tanggal[$noakun][$kdkeg][$notran])."</td>
						<td>".$kodevhc[$noakun][$kdkeg][$notran]."</td>
						<td align=right>".number_format($kmhm[$noakun][$kdkeg][$notran],2)."</td>
					  </tr>
			";
			@$sthmkm+=$kmhm[$noakun][$kdkeg][$notran];
		}
	}
	$stream.="
		<tr  bgcolor=#80FFFE>
			<td colspan='7'>".$_SESSION['lang']['total']."</td>
			<td align=right>".number_format($sthmkm,2)."</td>
		</tr>
		";
	@$gthmkm+=$sthmkm;
}	
$stream.="
		<tr  bgcolor=#48D1CC>
			<td colspan='7'>".$_SESSION['lang']['grnd_total']."</td>
			<td align=right>".number_format($gthmkm,2)."</td>
		</tr>
		";	
	
	
$stream.="
 </tbody>
     </table>";
	 
switch ($method) {
######PREVIEW
    case 'html4':
		//echo $blok;
		//echo "<br>";
		
		echo"
			<button id=tomboldetail class=mybutton onclick=kehtml1()>Level 1</button> 
			<button id=tomboldetail class=mybutton onclick=kehtml2()>Level 2</button>
			<button id=tomboldetail class=mybutton onclick=kehtml3()>Level 3</button>
			<button id=tomboldetail class=mybutton disabled>Level 4</button>
		";
		
		echo"<br>";
		
		echo "
			<button id=tomboldetail class=mybutton disabled>" . $_SESSION['lang']['excel'] . " 1</button>   
			<button id=tomboldetail class=mybutton disabled>" . $_SESSION['lang']['excel'] . " 2</button>   
			<button id=tomboldetail class=mybutton disabled>" . $_SESSION['lang']['excel'] . " 3</button> 
			<button id=tomboldetail class=mybutton onclick=excel4(event,'".$blok."','".$per2."','".$tipeakun."','".$tipe."')>" . $_SESSION['lang']['excel'] . " 4</button>  			
		";
		
        echo "<br>";
		echo "<br>";
        echo $stream;
        break;

######EXCEL	
    case 'excel4':
      
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "BIAYA_DAN_PRODUKSI_PERBLOK_LV2_" . $blok;
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