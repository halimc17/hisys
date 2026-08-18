<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt         = checkPostGet('pt', '');
$gudang     = checkPostGet('gudang', '');
$periode   = checkPostGet('periode','');
$periode2   = checkPostGet('periode2','');
$akundari   = checkPostGet('akundari', '');
$akunsampai = checkPostGet('akunsampai', '');
$regional   = checkPostGet('regional', '');
$tipelaporan= checkPostGet('tipelaporan','');
$arrautojurnal=array("0"=>"Manual","1"=>"Otomatis");

if($akundari==''){
    echo "WARNING: silakan memilih akun."; exit;
}
if($akunsampai==''){
    echo "WARNING: silakan memilih akun."; exit;
}

if(($periode=='')or($periode2=='')){
    echo "WARNING: silakan memilih periode."; exit;
}


//$periode buat filter keu_saldobulanan, $bulan buat nentuin field-nya
$qwe=explode("-",$periode);
$bulan=$qwe[1];

// Init Grand Total
$grandtotaldebet=$grandtotalkredit=0;

###tambahan indra
//bentuk tanggal 1 untuk veriv
$qwer=explode("-",$tanggal1);
$tglverivsatu=$qwer[2];

//bentuk tangal 1 diawal bulan untuk sum db-kr bentuk sawal
$tglsatu=$qwer[2]."-".$qwer[1]."-01";

#= bentuk tanggal awal dan akhir periode
$tglawalpersatu=$periode1."-01";
$tglakhirperdua=$periode2."-31";

//hitung tanggal kemarin
$tglx =  str_replace("-","",$tanggal1);
$tglkemarin = strtotime('-1 day',strtotime($tglx));
$tglkemarin = date('Y-m-d', $tglkemarin);


if($periode>$periode2){
	echo "WARNING: Periode 2 tidak bisa lebih kecil."; exit;
}


if($regional=='' && $gudang=='') {
   $wheregudang =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
} else if($regional!='' && $gudang=='') {
    $wheregudang=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
	. " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
} else {
    $wheregudang =" and kodeorg ='".$gudang."'";
}

// exclude laba rugi tahun berjalan
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi = 'CLM'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $clm=$bar->noakundebet;
}


//hitung total transaksi yang sudah ada
$str="select sum(debet)-sum(kredit) as transaksi,noakun from ".$dbname.".keu_jurnaldt_vw where "
        . " noakun != '".$clm."' and tanggal between '".$tglsatu."' and '".$tglkemarin."' "
        . " and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." "
        . " group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    @$totaltran[$dTran->noakun]+=$dTran->transaksi;
}



// ambil saldo awal
$str="select * from ".$dbname.".keu_saldobulanan where noakun != '".$clm."' and 
periode = '".str_replace("-","",$periode)."' and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." order by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	setIt($saldoawal[$bar->noakun],0);
    $qwe="awal".$bulan;
    $saldoawal[$bar->noakun]+=$bar->$qwe;
    $aqun[$bar->noakun]=$bar->noakun;
}

// kamus nama akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun where level = '5' and noakun!='".$clm."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $namaakun[$bar->noakun]=$bar->namaakun;
}

// kamus tahun tanam
$aresta="SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok";
$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){
    $tahuntanam[$res['kodeorg']]=$res['tahuntanam'];
}   


#= data noakun yang mau disum
$str="select noakun from ".$dbname.".keu_5sumakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrsumakun[]=$bar['noakun'];
}

#= ambil novoucher dan nocek
$str="select * from ".$dbname.".keu_kasbankht where tanggal between '".$tglawalpersatu."' and '".$tglakhirperdua."' ".$wheregudang." and posting='1' and pembayaran=1";
// echo $str;exit();
$res=fetchdata($str);
foreach($res as $bar){
	$nocek[$bar['notransaksi']]=$bar['nocek'];
	$novoucher[$bar['notransaksi']]=$bar['novoucher'];
	$rekening[$bar['notransaksi']]=$bar['rekening'];
}

#= ambil novoucher dan nocek
$str="select * from ".$dbname.".keu_5akunbank_vw";
$res=fetchdata($str);
foreach($res as $bar){
	$namabank[$bar['noakun']]=$bar['namabank'];
}

$str="select * from ".$dbname.".log_5supplier";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$namasupplier[$bar['supplierid']]=$bar['namasupplier'];
}


#= array GL
$str="select * from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='GL' and jurnalid!='M'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrgl[$bar['jurnalid']]=$bar['jurnalid'];
}

#= akun kas/bank
$str="select noakun from ".$dbname.".keu_5akun where left(noakun,3) = '111' and detail=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $arrnoakunkb[$bar->noakun]=$bar->noakun;
}

#= nama jurnal
#= default autojurnal
$str="select * from ".$dbname.".keu_5parameterjurnal";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$namajurnal[$bar->jurnalid]=$bar->keterangan;
	$auto[$bar->jurnalid]=$bar->auto;
}

$nmauto=array("0"=>"Manual","1"=>"Auto");
$isidata=array();
function clearsym($tulisan){
	// $hasil='';
	// $hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $tulisan); // dz: remove non-ascii chars
	// return $hasil;
	
	$string = $tulisan;
	$string = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string); // dz: remove non-ascii chars
	$string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.
    // $string = preg_replace('/-+/',' ',$string);
    // $string = preg_replace('/\s+/', '-', trim($string)); 
    return $string;
}

$str=" SELECT * FROM ".$dbname.".keu_jurnaldt_vw WHERE periode >= '".$periode."' and periode <= '".$periode2."' and noakun >= '".$akundari."' and noakun <=  '".$akunsampai."' and noakun!='".$clm."' and jumlah!=0 ".
$wheregudang." order by noakun,tanggal";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()) {
	$optNmCust = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar->kodecustomer."'");
	$optnmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->nik."'");
	$qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
	$isidata[$qwe]['nojur']=($bar->nojurnal);
	$isidata[$qwe]['autojurnal']=clearsym($bar->autojurnal);
	$isidata[$qwe]['tangg']=clearsym($bar->tanggal);
	$isidata[$qwe]['noaku']=clearsym($bar->noakun);
	$isidata[$qwe]['kodejurnal']=clearsym($bar->kodejurnal);
	$isidata[$qwe]['keter']=clearsym($bar->keterangan);
	$isidata[$qwe]['namacustomer']=clearsym(@$optNmCust[$bar->kodecustomer]);
	$isidata[$qwe]['namasupplier']=clearsym($namasupplier[$bar->kodesupplier]);
	$isidata[$qwe]['nodok']=($bar->nodok);
	$isidata[$qwe]['project']=clearsym($bar->kodeasset);
	if ($bar->noakun=='1130101' || $bar->noakun=='2120101' || $bar->noakun=='2130601' || $bar->noakun=='1170101' ) {
		$nokontrak=explode('##', $bar->noreferensi);
	}
	if (@$nokontrak[1]!='') {
		$isidata[$qwe]['noreferensi']=($nokontrak[1]);
	}else{
		$isidata[$qwe]['noreferensi']=($bar->noreferensi);
	}
	$isidata[$qwe]['namabank']=clearsym($namabank[$rekening[$bar->noreferensi]]);
	$isidata[$qwe]['rekening']=clearsym($rekening[$bar->noreferensi]);
	$isidata[$qwe]['nocekgiro']=clearsym($nocek[$bar->noreferensi]);
	$isidata[$qwe]['novoucher']=($novoucher[$bar->noreferensi]);
	@$isidata[$qwe]['debet']=clearsym($bar->debet);
	@$isidata[$qwe]['kredi']=clearsym($bar->kredit);
	$isidata[$qwe]['kodeb']=clearsym($bar->kodeblok);
	
	$isidata[$qwe]['kodebarang']=clearsym($bar->kodebarang);
	$isidata[$qwe]['kodebarang']=clearsym($bar->kodebarang);
	
	$isidata[$qwe]['nik']=clearsym($optnmkar[$bar->nik]);
	if($bar->kodeblok==''){
		$org=$bar->kodeorg; 
	} else {
		$org=substr($bar->kodeblok,0,6);
	}
	$isidata[$qwe]['organ']=clearsym($org);
	$isidata[$qwe]['kodevhc']=clearsym($bar->kodevhc);
	$aqun[$bar->noakun]=$bar->noakun;
	$arrkodebarang[$bar->kodebarang]=$bar->kodebarang;
}

if(@count($arrkodebarang)>'0'){
	$str="select * from ".$dbname.".log_5masterbarang where  kodebarang in ('".implode("','",$arrkodebarang)."') ";
	// echo $str;exit("Error");
	$res=fetchdata($str);
	foreach($res as $bar){
		$namabarang[$bar['kodebarang']]=$bar['namabarang'];
	}
}

if(!empty($isidata)) foreach($isidata as $c=>$key) {
    $sort_noaku[] = $key['noaku'];
    $sort_tangg[] = $key['tangg'];
    $sort_debet[] = $key['debet'];
    $sort_nojur[] = $key['nojur'];
}

$border="border=0";
if($tipelaporan=='excel'){
	$border="border=1";
}

$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$gudang."'");

$stream="<table id=pvtTable cellpadding=1 cellspacing=1 ".$border." class='sortable nowrap' width='100%' data-scroll-x='true' scroll-collapse='false'>
			<thead>
			<tr>
				<th align=center >".$_SESSION['lang']['nojurnal']."</th>
				<th align=center >".$_SESSION['lang']['tipe']."</th>
				<th align=center >".$_SESSION['lang']['tanggal']."</th>
				<th align=center >".$_SESSION['lang']['noakun']."</th>
				<th align=center >".$_SESSION['lang']['akun']."</th>
				<th align=center >".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center >".$_SESSION['lang']['nmcust']."</th>
				<th align=center >".$_SESSION['lang']['namasupplier']."</th>
				<th align=center >".$_SESSION['lang']['noreferensi']."</th>
				<th align=center >".$_SESSION['lang']['nodok']."</th>
				<th align=center >".$_SESSION['lang']['project']."</th>
				<th align=center >".$_SESSION['lang']['novoucher']."</th>
				<th align=center >".$_SESSION['lang']['namabank']."</th>
				<th align=center >Rekening</th>
				<th align=center >No Cek/Giro</th>
				<th align=center >".$_SESSION['lang']['keterangan']."</th>
				<th align=center >".$_SESSION['lang']['debet']."</th>
				<th align=center >".$_SESSION['lang']['kredit']."</th>
				<th align=center >".$_SESSION['lang']['saldo']."</th>
				<th align=center >".$_SESSION['lang']['kodeorg']."</th>
				<th align=center >".$_SESSION['lang']['kodeblok']."</th>
				<th align=center >".$_SESSION['lang']['tahuntanam']."</th>
				<th align=center >".$_SESSION['lang']['kodevhc']."</th>
				<th align=center >".$_SESSION['lang']['kodenopol']."</th>
				<th align=center >".$_SESSION['lang']['kodebarang']."</th>
				<th align=center >".$_SESSION['lang']['namabarang']."</th>
			</tr>  
			</thead>
	<tbody>";

if(!empty($isidata))array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $sort_debet, SORT_DESC, $sort_nojur, SORT_ASC, $isidata);
if(!empty($aqun))asort($aqun);

$no=0;
$grandsalwal=0;
if(!empty($aqun))foreach($aqun as $akyun){
    $subsalwal=isset($saldoawal[$akyun])? $saldoawal[$akyun]: 0;
    $totaldebet=0;
    $totalkredit=0;
    $subsalak=$subsalwal;
    
    if($tglverivsatu!='01'){
        $salwal=$subsalwal+$totaltran[$akyun];
    }else{
        $salwal=$subsalwal;
    }
    $grandsalwal+=$subsalwal;
    $data[]=array(
		"SALDO AWAL","","",$akyun,$namaakun[$akyun],
		"","","","","","","","","","","","","",$salwal,"","","","","","",""
	);
	# tampilin jurnal daftar akun
	if(!empty($isidata))foreach($isidata as $baris){
        if($baris['noaku']==$akyun){
            $no+=1;
			setIt($nmKar[$baris['nik']],'');
			
			$totaldebet+=$baris['debet'];
			$grandtotaldebet+=$baris['debet'];
			$totalkredit+=$baris['kredi'];
			$grandtotalkredit+=$baris['kredi'];
			$salwal=$salwal+($baris['debet'])-($baris['kredi']);
			
			$data[]=array(
				$baris['nojur'],
				$nmauto[$auto[$baris['kodejurnal']]],
				tanggalnormal($baris['tangg']),
				$baris['noaku'],
				$namaakun[$baris['noaku']],
				$baris['nik'],
				clearsym($baris['namacustomer']),
				clearsym($baris['namasupplier']),
				$baris['noreferensi'],
				$baris['nodok'],
				$baris['project'],
				$baris['novoucher'],
				$baris['namabank'],
				$baris['rekening'],
				$baris['nocekgiro'],
				$baris['keter'],
				$baris['debet'],
				$baris['kredi'],
				$salwal,
				$baris['organ'],
				$baris['kodeb'],
				(isset($tahuntanam[$baris['kodeb']])? $tahuntanam[$baris['kodeb']]: ''),
				$baris['kodevhc'],
				getNopol($baris['kodevhc']),
				$baris['kodebarang'],
				clearsym($namabarang[$baris['kodebarang']])
			);
            $subsalak=$salwal;
        }
    }
	$data[]=array(
		"SALDO AKHIR","","",$akyun,$namaakun[$akyun],
		"","","","","","","","","","","",$totaldebet,$totalkredit,$subsalak,"","","","","","",""
	);
}
$grandsalak=$grandsalwal+$grandtotaldebet-$grandtotalkredit;
$data[]=array(
	"GRAND TOTAL","","",$akyun,$namaakun[$akyun],
	"","","","","","","","","","","",$grandtotaldebet,$grandtotalkredit,$grandsalak,"","","","","","",""
);
$stream.="</tbody>";
$stream.="
	<tfoot>
		 
	</tfoot>";
$stream.="</table>";

if($tipelaporan=='html'){
	echo $stream."####".json_encode($data);
}else{
	$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
	$qwe=date("YmdHms");
	$nop_="LP_JRNL_Bukubesar_".$gudang.$periode.$periode2."rev".$revisi."___".$qwe;
	if(strlen($stream)>0)
	{
		 $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		 gzwrite($gztralala, $stream);
		 gzclose($gztralala);
		 echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
			</script>";
	}
}

		
?>     