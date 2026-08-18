<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$tanggal1=$_GET['tanggal1'];
$tanggal2=$_GET['tanggal2'];
$akundari=$_GET['akundari'];
$akunsampai=$_GET['akunsampai'];
$regional=$_GET['regional']; 


$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

//$periode buat filter keu_saldobulanan, $bulan buat nentuin field-nya
$qwe=explode("-",$tanggal1);
$periode=$qwe[2].$qwe[1];
$bulan=$qwe[1];

//balik tanggal
$qwe=explode("-",$tanggal1);
$tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggal2);
$tanggal2=$qwe[2]."-".$qwe[1]."-".$qwe[0];


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

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='';
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
	$namapt=strtoupper($bar->namaorganisasi);
}
//ambil namagudang
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."'";
$namagudang='';
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
	$namagudang=strtoupper($bar->namaorganisasi);
}

// exclude laba rugi tahun berjalan
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal
    where kodeaplikasi = 'CLM'";
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
    $clm=$bar->noakundebet;
}

if($regional=='' && $gudang=='')
{
   $wheregudang =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
}
else if($regional!='' && $gudang=='')
{
    //$where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."') "; 
    $wheregudang=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
}
else
{
    $wheregudang=" and kodeorg='".$gudang."'";
}


//hitung total transaksi yang sudah ada
$iTran="select sum(debet)-sum(kredit) as transaksi,noakun from ".$dbname.".keu_jurnaldt_vw where "
        . " noakun != '".$clm."' and tanggal between '".$tglsatu."' and '".$tglkemarin."' "
        . " and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." "
        . " group by noakun";
try{$iTran=$owlPDO->query($iTran);$iTran->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($dTran=$iTran->fetch())
{
    $totaltran[$dTran->noakun]+=$dTran->transaksi;
}
$str="select * from ".$dbname.".keu_saldobulanan where noakun != '".$clm."' and periode = '".$periode."' and noakun >= '".$akundari."' and noakun <= '".$akunsampai."' ".$wheregudang." order by noakun";
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
    $qwe="awal".$bulan;
    $saldoawal[$bar->noakun]+=$bar->$qwe;
    $aqun[$bar->noakun]=$bar->noakun;
}

#= data noakun yang mau disum
$str="select noakun from ".$dbname.".keu_5sumakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrsumakun[]=$bar['noakun'];
}


#= array GL
$str="select * from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='GL' and jurnalid!='M'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrgl[$bar['jurnalid']]=$bar['jurnalid'];
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



// ambil data
$isidata=array();
// $str="select *, IF(t1.kodesupplier = '', '', (SELECT t2.namasupplier FROM ".$dbname.".log_5supplier t2 WHERE t2.supplierid = t1.kodesupplier)) as namasupplier, (SELECT t3.nocek FROM ".$dbname.".keu_kasbankht t3 WHERE t3.notransaksi = t1.noreferensi) as nocekgiro from ".$dbname.".keu_jurnaldt_vw t1 where t1.noakun != '".$clm."' and t1.tanggal >= '".$tanggal1."' and t1.tanggal <= '".$tanggal2."' and t1.noakun >= '".$akundari."' and t1.noakun <= '".$akunsampai."' ".$wheregudang." order by t1.noakun, t1.tanggal";
$str=" SELECT 
if(sum(jumlah)>0,sum(jumlah),'0') as debet,
if(sum(jumlah)<0,(sum(jumlah)*-1),'0') as kredit,
sum(jumlah) as jumlah,noakun,noreferensi,nojurnal,keterangan,kodeorg,tanggal,kodeblok,kodeasset,nodok,kodesupplier,nik
FROM ".$dbname.".`keu_jurnaldt_vw`
WHERE tanggal between '".$tanggal1."' and '".$tanggal2."' and noakun >= '".$akundari."' and noakun <=  '".$akunsampai."'   
".$wheregudang." and kodejurnal in ('".implode("','",$arrgl)."')
 group by noakun,noreferensi,keterangan order by noakun, tanggal"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	if(in_array($bar->noakun,$arrsumakun)){
		$optNmCust = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar->kodecustomer."'");
		$qwe=$bar->noakun;
		// $isidata[$qwe]['nojur']='';
		$isidata[$qwe]['nojur']=$bar->nojurnal;
		$isidata[$qwe]['tangg']=$bar->tanggal;
		$isidata[$qwe]['noaku']=$bar->noakun;
		// $isidata[$qwe]['keter']='';
		$isidata[$qwe]['keter']=$bar->keterangan;
		$isidata[$qwe]['namacustomer']='';
		$isidata[$qwe]['namasupplier']='';
		$isidata[$qwe]['nodok']='';
		$isidata[$qwe]['noreferensi']='';
		$isidata[$qwe]['nocekgiro']='';
		$isidata[$qwe]['debet']+=$bar->debet;
		$isidata[$qwe]['kredi']+=$bar->kredit;
		$isidata[$qwe]['kodeb']='';
		$isidata[$qwe]['nik']='';
		if($bar->kodeblok=='')$org=$bar->kodeorg; else $org=substr($bar->kodeblok,0,6);
		$isidata[$qwe]['organ']='';
		$aqun[$bar->noakun]=$bar->noakun;
	}else{
		$optNmCust = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar->kodecustomer."'");
		$optnmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->nik."'");
		$qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
		$isidata[$qwe]['nojur']=$bar->nojurnal;
		$isidata[$qwe]['tangg']=$bar->tanggal;
		$isidata[$qwe]['noaku']=$bar->noakun;
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




#=== selain GL

// ambil data
// $str="select *, IF(t1.kodesupplier = '', '', (SELECT t2.namasupplier FROM ".$dbname.".log_5supplier t2 WHERE t2.supplierid = t1.kodesupplier)) as namasupplier, (SELECT t3.nocek FROM ".$dbname.".keu_kasbankht t3 WHERE t3.notransaksi = t1.noreferensi) as nocekgiro from ".$dbname.".keu_jurnaldt_vw t1 where t1.noakun != '".$clm."' and t1.tanggal >= '".$tanggal1."' and t1.tanggal <= '".$tanggal2."' and t1.noakun >= '".$akundari."' and t1.noakun <= '".$akunsampai."' ".$wheregudang." order by t1.noakun, t1.tanggal";
$str=" SELECT *
FROM ".$dbname.".`keu_jurnaldt_vw`
WHERE tanggal between '".$tanggal1."' and '".$tanggal2."' and noakun >= '".$akundari."' and noakun <=  '".$akunsampai."'   
".$wheregudang." and kodejurnal not in ('".implode("','",$arrgl)."')  order by noakun, tanggal"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	if(in_array($bar->noakun,$arrsumakun)){
		$optNmCust = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar->kodecustomer."'");
		$qwe=$bar->noakun;
		// $isidata[$qwe]['nojur']='';
		$isidata[$qwe]['nojur']=$bar->nojurnal;
		$isidata[$qwe]['tangg']=$bar->tanggal;
		$isidata[$qwe]['noaku']=$bar->noakun;
		// $isidata[$qwe]['keter']='';
		$isidata[$qwe]['keter']=$bar->keterangan;
		$isidata[$qwe]['namacustomer']='';
		$isidata[$qwe]['namasupplier']='';
		$isidata[$qwe]['nodok']='';
		$isidata[$qwe]['noreferensi']='';
		$isidata[$qwe]['nocekgiro']='';
		$isidata[$qwe]['debet']+=$bar->debet;
		$isidata[$qwe]['kredi']+=$bar->kredit;
		$isidata[$qwe]['kodeb']='';
		$isidata[$qwe]['nik']='';
		if($bar->kodeblok=='')$org=$bar->kodeorg; else $org=substr($bar->kodeblok,0,6);
		$isidata[$qwe]['organ']='';
		$aqun[$bar->noakun]=$bar->noakun;
	}else{
		$optNmCust = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar->kodecustomer."'");
		$optnmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->nik."'");
		$qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
		$isidata[$qwe]['nojur']=$bar->nojurnal;
		$isidata[$qwe]['tangg']=$bar->tanggal;
		$isidata[$qwe]['noaku']=$bar->noakun;
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

// kamus nama akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun
    where level = '5' and noakun!='".$clm."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namaakun[$bar->noakun]=$bar->namaakun;
}

// kamus nama supplier
$str="select supplierid, namasupplier from ".$dbname.".log_5supplier";
try{$str=$owlPDO->query($str);$str->setFetchMode(PDO::FETCH_OBJ);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
while($bar=$str->fetch())
{
    $namasupplier[$bar->supplierid]=$bar->namasupplier;
}

// kamus tahun tanam
$aresta="SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok
    ";
$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch())
{
    $tahuntanam[$res['kodeorg']]=$res['tahuntanam'];
}   

if(!empty($isidata)) foreach($isidata as $c=>$key) {
    $sort_noaku[] = $key['noaku'];
    $sort_tangg[] = $key['tangg'];
    $sort_debet[] = $key['debet'];
    $sort_nojur[] = $key['nojur'];
}

// sort
if(!empty($isidata))array_multisort($sort_noaku, SORT_ASC, $sort_tangg, SORT_ASC, $sort_debet, SORT_DESC, $sort_nojur, SORT_ASC, $isidata);
if(!empty($aqun))asort($aqun);

$stream=strtoupper($_SESSION['lang']['laporanbukubesar'])." : ".$namapt." ".$namagudang."<br>".
        strtoupper($_SESSION['lang']['tanggal'])." : ".tanggalnormal($tanggal1)." s/d ".tanggalnormal($tanggal2)."<br>".
        strtoupper($_SESSION['lang']['noakun'])." : ".$akundari." s/d ".$akunsampai."<br>
    <table border=1>
    <thead>
    <tr bgcolor='#dedede'>
         <td align=center >".$_SESSION['lang']['nourut']."</td>
			  <td align=center >".$_SESSION['lang']['nojurnal']."</td>
			  <td align=center >".$_SESSION['lang']['tanggal']."</td>
			  <td align=center >".$_SESSION['lang']['noakun']."</td>
			  <td align=center >".$_SESSION['lang']['namakaryawan']."</td>
			  <td align=center >".$_SESSION['lang']['nmcust']."</td>
			  <td align=center >".$_SESSION['lang']['namasupplier']."</td>
			  <td align=center >".$_SESSION['lang']['noreferensi']."</td>
				<td align=center >".$_SESSION['lang']['nodok']."</td>
				<td align=center >".$_SESSION['lang']['novoucher']."</td>
			  <td align=center >".$_SESSION['lang']['project']."</td>
			  <td align=center >No Cek/Giro</td>
			  <td align=center >".$_SESSION['lang']['keterangan']."</td>
			  <td align=center >".$_SESSION['lang']['debet']."</td>
			  <td align=center >".$_SESSION['lang']['kredit']."</td>
			  <td align=center >".$_SESSION['lang']['saldo']."</td>
			  <td align=center >".$_SESSION['lang']['kodeorg']."</td>
			  <td align=center >".$_SESSION['lang']['kodeblok']."</td>
			  <td align=center >".$_SESSION['lang']['tahuntanam']."</td>
    </tr>  
    </thead>
    <tbody id=container>";
 //tampil data
$no=0;
// tampilin daftar akun
if(!empty($aqun))foreach($aqun as $akyun){
    $subsalwal=$saldoawal[$akyun];
    $totaldebet=0;
    $totalkredit=0;
    $subsalak=$subsalwal;
    
    
    if($tglverivsatu!='01')
    {
        $salwal=$subsalwal+$totaltran[$akyun];
    }
    else
    {
        $salwal=$subsalwal;
    }
    //$salwal=$subsalwal;
    
    $grandsalwal+=$subsalwal;
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=3></td>";
        $stream.="<td>".$akyun."</td>";
        // $stream.="<td colspan=6>&nbsp;</td>";
        $stream.="<td colspan=11>".$namaakun[$akyun]."</td>";
        $stream.="<td align=right>".number_format($salwal,2)."</td>";
        $stream.="<td colspan=3></td>";
    $stream.="</tr>";
// tampilin jurnal daftar akun    
    if(!empty($isidata))foreach($isidata as $baris)
    {
        if($baris['noaku']==$akyun){
            $no+=1;
			setIt($nmKar[$baris['nik']],'');
            $stream.="<tr class=rowcontent>";
            $stream.="<td  align=center>".$no."</td>";
            //$stream.="<td style='width:80px;min-width:80px;max-width:80px;'>".substr($baris['nojur'],14,8)."</td>";
            $stream.="<td>".$baris['nojur']."</td>";
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
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=13>SubTotal</td>";
//        $stream.="<td align=right>".number_format($subsalwal)."</td>";
        $stream.="<td align=right>".number_format($totaldebet,2)."</td>";
        $stream.="<td align=right>".number_format($totalkredit,2)."</td>";
        $stream.="<td align=right>".number_format($subsalak,2)."</td>";
        $stream.="<td colspan=3></td>";
     $stream.="</tr>";
}

// total
    $grandsalak=$grandsalwal+$grandtotaldebet-$grandtotalkredit;
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=13>GrandTotal</td>";
//        $stream.="<td align=right>".number_format($grandsalwal)."</td>";
        $stream.="<td align=right>".number_format($grandtotaldebet,2)."</td>";
        $stream.="<td align=right>".number_format($grandtotalkredit,2)."</td>";
        $stream.="<td align=right>".number_format($grandsalak,2)."</td>";
        $stream.="<td colspan=3></td>";
     $stream.="</tr>";

$stream.="</tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>";

// exit("Error:$stream");

$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
$qwe=date("YmdHms");
$nop_="Laporan_BukuBesar_".$pt.$gudang." ".$qwe;
if(strlen($stream)>0)
{
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
}    
?>