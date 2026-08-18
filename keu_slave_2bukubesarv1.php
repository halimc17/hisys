<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt = checkPostGet('pt', '');
$gudang = checkPostGet('gudang', '');
$tanggal1=checkPostGet('tanggal1','');
$tanggal2=checkPostGet('tanggal2','');
$akundari = checkPostGet('akundari', '');
$akunsampai = checkPostGet('akunsampai', '');
$regional = checkPostGet('regional', '');
$tipelaporan=checkPostGet('tipelaporan','');

$arrautojurnal=array("0"=>"Manual","1"=>"Otomatis");

if($tanggal1==''){
    echo "WARNING: silakan mengisi tanggal."; exit;
}
if($tanggal2==''){
    echo "WARNING: silakan mengisi tanggal."; exit;
}
if($akundari==''){
    echo "WARNING: silakan memilih akun."; exit;
}
if($akunsampai==''){
    echo "WARNING: silakan memilih akun."; exit;
}

//$periode buat filter keu_saldobulanan, $bulan buat nentuin field-nya
$qwe=explode("-",$tanggal1);
$periode=$qwe[2].$qwe[1];
$bulan=$qwe[1];

//balik tanggal
$qwe=explode("-",$tanggal1);
$tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggal2);
$tanggal2=$qwe[2]."-".$qwe[1]."-".$qwe[0];

// Init Grand Total
$grandtotaldebet=$grandtotalkredit=0;




###tambahan indra
//bentuk tanggal 1 untuk veriv
$qwer=explode("-",$tanggal1);
$tglverivsatu=$qwer[2];

//bentuk tangal 1 diawal bulan untuk sum db-kr bentuk sawal
$tglsatu=$qwer[2]."-".$qwer[1]."-01";

//hitung tanggal kemarin
$tglx =  str_replace("-","",$tanggal1);
$tglkemarin = strtotime('-1 day',strtotime($tglx));
$tglkemarin = date('Y-m-d', $tglkemarin);
##tutup tambah indra


if($regional=='' && $gudang=='') {
   $wheregudang =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
} else if($regional!='' && $gudang=='') {
    $wheregudang=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
} else {
    $wheregudang =" and kodeorg ='".$gudang."'";
}

// exclude laba rugi tahun berjalan
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal
    where kodeaplikasi = 'CLM'
    ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
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
periode = '".$periode."' and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." order by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	setIt($saldoawal[$bar->noakun],0);
    $qwe="awal".$bulan;
    $saldoawal[$bar->noakun]+=$bar->$qwe;
    $aqun[$bar->noakun]=$bar->noakun;
}

// kamus nama akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun
    where level = '5' and noakun!='".$clm."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namaakun[$bar->noakun]=$bar->namaakun;
}

// kamus tahun tanam
$aresta="SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok";
$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch())
{
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
$str="select * from ".$dbname.".keu_kasbankht where 
tanggal between '".$tanggal1."' and '".$tanggal2."' ".$wheregudang."
and posting='1' and pembayaran=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nocek[$bar['notransaksi']]=$bar['nocek'];
	$novoucher[$bar['notransaksi']]=$bar['novoucher'];
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
$str="select noakun from ".$dbname.".keu_5akun
    where left(noakun,3) = '111' and detail=1";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
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



// print_r($arrnoakunkb);

$isidata=array();


#= nojurnal untuk bank
// $str=" SELECT 
// if(sum(jumlah)>0,sum(jumlah),'0') as debet,
// if(sum(jumlah)<0,(sum(jumlah)*-1),'0') as kredit,
// sum(jumlah) as jumlah,noakun,noreferensi,nojurnal,keterangan,kodeorg,tanggal,kodeblok,kodeasset,nodok,kodesupplier,nik,kodejurnal,autojurnal
// FROM ".$dbname.".`keu_jurnaldt_vw`
// WHERE tanggal between '".$tanggal1."' and '".$tanggal2."' and noakun >= '".$akundari."' and noakun <=  '".$akunsampai."'  
// ".$wheregudang."
 // group by noakun,noreferensi,keterangan order by noakun, tanggal"; 
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while($bar=$res->fetch()){
	
	
// }

/*
$str=" SELECT 
if(sum(jumlah)>0,sum(jumlah),'0') as debet,
if(sum(jumlah)<0,(sum(jumlah)*-1),'0') as kredit,
sum(jumlah) as jumlah,noakun,noreferensi,nojurnal,keterangan,kodeorg,tanggal,kodeblok,kodeasset,nodok,kodesupplier,nik,kodejurnal,autojurnal
FROM ".$dbname.".`keu_jurnaldt_vw`
WHERE tanggal between '".$tanggal1."' and '".$tanggal2."' and noakun >= '".$akundari."' and noakun <=  '".$akunsampai."'  
".$wheregudang."
 group by noakun,noreferensi,keterangan order by noakun, tanggal"; 
 // echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(in_array($bar->noakun,$arrnoakunkb)){
		$optNmCust = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar->kodecustomer."'");
			$optnmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->nik."'");
			$qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
			$isidata[$qwe]['autojurnal']=$bar->autojurnal;
			$isidata[$qwe]['nojur']=$bar->nojurnal;
			$isidata[$qwe]['tangg']=$bar->tanggal;
			$isidata[$qwe]['noaku']=$bar->noakun;
			$isidata[$qwe]['kodejurnal']=$bar->kodejurnal;
			$isidata[$qwe]['keter']=$bar->keterangan;
			$isidata[$qwe]['namacustomer']=@$optNmCust[$bar->kodecustomer];
			$isidata[$qwe]['namasupplier']=$namasupplier[$bar->kodesupplier];
			$isidata[$qwe]['nodok']=$bar->nodok;
			$isidata[$qwe]['project']=$bar->kodeasset;
			if ($bar->noakun=='1130101' || $bar->noakun=='2120101' || $bar->noakun=='2130601' || $bar->noakun=='1170101' ) {
				$nokontrak=explode('##', $bar->noreferensi);
			}
			if (@$nokontrak[1]!='') {
				$isidata[$qwe]['noreferensi']=$nokontrak[1];
			}else{
				$isidata[$qwe]['noreferensi']=$bar->noreferensi;
			}
			$isidata[$qwe]['nocekgiro']=$nocek[$bar->noreferensi];
			$isidata[$qwe]['novoucher']=$novoucher[$bar->noreferensi];
			// $isidata[$qwe]['debet']=$bar->debet;
			
			@$isidata[$qwe]['debet']+=$bar->debet;
			@$isidata[$qwe]['kredi']+=$bar->kredit;
			
			// $isidata[$qwe]['kredi']=$bar->kredit;
			$isidata[$qwe]['kodeb']=$bar->kodeblok;
			$isidata[$qwe]['nik']=$optnmkar[$bar->nik];
			// $isidata[$qwe]['nik']=$bar->nik;
			if($bar->kodeblok=='')$org=$bar->kodeorg; else $org=substr($bar->kodeblok,0,6);
			$isidata[$qwe]['organ']=$org;
			$aqun[$bar->noakun]=$bar->noakun;
	}
}

*/

$str=" SELECT *
FROM ".$dbname.".`keu_jurnaldt_vw`
WHERE tanggal between '".$tanggal1."' and '".$tanggal2."' and noakun >= '".$akundari."' and noakun <=  '".$akunsampai."'   and noakun!='".$clm."' and jumlah!=0
".$wheregudang." order by noakun,tanggal"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()) {
	// if(!in_array($bar->noakun,$arrnoakunkb)){
			$optNmCust = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar->kodecustomer."'");
			$optnmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->nik."'");
			$qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
			$isidata[$qwe]['nojur']=$bar->nojurnal;
			$isidata[$qwe]['autojurnal']=$bar->autojurnal;
			$isidata[$qwe]['tangg']=$bar->tanggal;
			$isidata[$qwe]['noaku']=$bar->noakun;
			$isidata[$qwe]['kodejurnal']=$bar->kodejurnal;
			$isidata[$qwe]['keter']=$bar->keterangan;
			$isidata[$qwe]['namacustomer']=@$optNmCust[$bar->kodecustomer];
			$isidata[$qwe]['namasupplier']=$namasupplier[$bar->kodesupplier];
			$isidata[$qwe]['nodok']=$bar->nodok;
			$isidata[$qwe]['project']=$bar->kodeasset;
			if ($bar->noakun=='1130101' || $bar->noakun=='2120101' || $bar->noakun=='2130601' || $bar->noakun=='1170101' ) {
				$nokontrak=explode('##', $bar->noreferensi);
			}
			if (@$nokontrak[1]!='') {
				$isidata[$qwe]['noreferensi']=$nokontrak[1];
			}else{
				$isidata[$qwe]['noreferensi']=$bar->noreferensi;
			}
			$isidata[$qwe]['nocekgiro']=$nocek[$bar->noreferensi];
			$isidata[$qwe]['novoucher']=$novoucher[$bar->noreferensi];
			@$isidata[$qwe]['debet']=$bar->debet;
			@$isidata[$qwe]['kredi']=$bar->kredit;
			$isidata[$qwe]['kodeb']=$bar->kodeblok;
			$isidata[$qwe]['nik']=$optnmkar[$bar->nik];
			if($bar->kodeblok==''){
				$org=$bar->kodeorg; 
			} else {
				$org=substr($bar->kodeblok,0,6);
			}
			$isidata[$qwe]['organ']=$org;
			$aqun[$bar->noakun]=$bar->noakun;
	
	// }
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

$stream.="Laporan Buku Besar<br>";
$stream.="".$gudang." - ".$nmorg[$gudang]."<br>";
$stream.="".tanggalnormal($tanggal1)." s/d ".tanggalnormal($tanggal2)."<br><br>";
$stream.="<table class=sortable cellpadding=5 cellspacing=1 ".$border.">
			<thead>
			<tr>
				<th align=center >".$_SESSION['lang']['nourut']."</th>
				<th align=center >".$_SESSION['lang']['nojurnal']."</th>
				<th align=center >".$_SESSION['lang']['tipe']."</th>
				<th align=center >".$_SESSION['lang']['tanggal']."</th>
				<th align=center >".$_SESSION['lang']['noakun']."</th>
				<th align=center >".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center >".$_SESSION['lang']['nmcust']."</th>
				<th align=center >".$_SESSION['lang']['namasupplier']."</th>
				<th align=center >".$_SESSION['lang']['noreferensi']."</th>
				<th align=center >".$_SESSION['lang']['nodok']."</th>
				<th align=center >".$_SESSION['lang']['novoucher']."</th>
				<th align=center >".$_SESSION['lang']['project']."</th>
				<th align=center >No Cek/Giro</th>
				<th align=center >".$_SESSION['lang']['keterangan']."</th>
				<th align=center >".$_SESSION['lang']['debet']."</th>
				<th align=center >".$_SESSION['lang']['kredit']."</th>
				<th align=center >".$_SESSION['lang']['saldo']."</th>
				<th align=center >".$_SESSION['lang']['kodeorg']."</th>
				<th align=center >".$_SESSION['lang']['kodeblok']."</th>
				<th align=center >".$_SESSION['lang']['tahuntanam']."</th>
			</tr>  
			</thead>
	<tbody>";
        


// sort
if(!empty($isidata))array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $sort_debet, SORT_DESC, $sort_nojur, SORT_ASC, $isidata);
if(!empty($aqun))asort($aqun);

$no=0;
$grandsalwal=0;
// tampilin daftar akun

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
    $stream.="<tr class=rowcontent>";
		$stream.="<td width=3250px align=right colspan=3></td>";
		$stream.="<td width=80px align=center>".$akyun."</td>";
		$stream.="<td width=1600px colspan=12>".$namaakun[$akyun]."</td>";
		$stream.="<td width=150px align=right>".number_format($salwal,2)."</td>";
		$stream.="<td width=240px colspan=3></td>";
    $stream.="</tr>";
	// tampilin jurnal daftar akun
	if(!empty($isidata))foreach($isidata as $baris)
    {
        if($baris['noaku']==$akyun){
            $no+=1;
			setIt($nmKar[$baris['nik']],'');
            $stream.="<tr class=rowcontent>";
				$stream.="<td  align=center>".$no."</td>";
				$stream.="<td>".$baris['nojur']."</td>";
				$stream.="<td>".$nmauto[$auto[$baris['kodejurnal']]]."</td>";
				$stream.="<td align=center>".tanggalnormal($baris['tangg'])."</td>";
				$stream.="<td align=center>".$baris['noaku']."</td>";
				$stream.="<td>".$baris['nik']."</td>";
				$stream.="<td>".$baris['namacustomer']."</td>";
				$stream.="<td>".$baris['namasupplier']."</td>";
				$stream.="<td>".$baris['noreferensi']."</td>";
				$stream.="<td>".$baris['nodok']."</td>";
				$stream.="<td>".$baris['novoucher']."</td>";
				$stream.="<td>".$baris['project']."</td>";
				$stream.="<td>".$baris['nocekgiro']."</td>";
				$stream.="<td>".$baris['keter']."</td>";
				$stream.="<td align=right>".number_format($baris['debet'],2)."</td>";
				$totaldebet+=$baris['debet'];
				$grandtotaldebet+=$baris['debet'];
				$stream.="<td align=right>".number_format($baris['kredi'],2)."</td>";
				$totalkredit+=$baris['kredi'];
				$grandtotalkredit+=$baris['kredi'];
				$salwal=$salwal+($baris['debet'])-($baris['kredi']);
				$stream.="<td align=right>".number_format($salwal,2)."</td>";
				$stream.="<td align=center>".$baris['organ']."</td>";
				$stream.="<td >".$baris['kodeb']."</td>";
				$stream.="<td align=center>".(isset($tahuntanam[$baris['kodeb']])? $tahuntanam[$baris['kodeb']]: '')."</td>";
            $stream.="</tr>";
            $subsalak=$salwal;
        }
    } 
	// subtotal    
    $stream.="<tr class=rowcontent>";
        $stream.="<td align=right colspan=14>SubTotal</td>";
        $stream.="<td align=right>".number_format($totaldebet,2)."</td>";
        $stream.="<td align=right>".number_format($totalkredit,2)."</td>";
        $stream.="<td align=right>".number_format($subsalak,2)."</td>";
        $stream.="<td colspan=3></td>";
     $stream.="</tr>";
}

// total
$grandsalak=$grandsalwal+$grandtotaldebet-$grandtotalkredit;
$stream.="<tr class=rowcontent>";
	$stream.="<td align=right colspan=14>GrandTotal</td>";
	$stream.="<td align=right>".number_format($grandtotaldebet,2)."</td>";
	$stream.="<td align=right>".number_format($grandtotalkredit,2)."</td>";
	$stream.="<td align=right>".number_format($grandsalak,2)."</td>";
	$stream.="<td colspan=3></td>";
$stream.="</tr>";

$stream.="</tbody></table>";

if($tipelaporan=='html'){
	echo $stream;
}else{
	$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
	$qwe=date("YmdHms");
	$nop_="LP_JRNL_Bukubesar_".$gudang.$periode."rev".$revisi."___".$qwe;
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