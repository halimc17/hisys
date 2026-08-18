<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}


$prdlist    =checkPostGet('prdlist','');
$unitlist   =checkPostGet('unitlist','');
$afdlist    =checkPostGet('afdlist','');
$divisi     =checkPostGet('divisi','');
$tipe       =checkPostGet('tipe','');
$proses     =checkPostGet('proses','');
$notransaksi=checkPostGet('notransaksi','');
$prd        =checkPostGet('prd','');
$unit       =checkPostGet('unit','');
$afd        =checkPostGet('afd','');
$kary       =checkPostGet('rowkary','');
$mdr        =checkPostGet('rowmdr','');
$krn        =checkPostGet('rowkrn','');
$tt         =checkPostGet('rowtt','');
$hk         =checkPostGet('rowhk','');
$jjg        =checkPostGet('rowjjg','');
$kg         =checkPostGet('rowkg','');
$kgbss      =checkPostGet('rowkgbss','');
$kglb1      =checkPostGet('rowkglb1','');
$rplb1      =checkPostGet('rowrplb1','');
$kglb2      =checkPostGet('rowkglb2','');
$rplb2      =checkPostGet('rowrplb2','');
$kgbrd      =checkPostGet('rowkgbrd','');
$rpbrd      =checkPostGet('rowrpbrd','');
$denda      =checkPostGet('rowdenda','');
$banjir     =checkPostGet('banjir','');
$tglpnn     =checkPostGet('tglpnn','');
$blok       =checkPostGet('topografi','');
$kdblok     =checkPostGet('blok','');
$rptopo     =checkPostGet('rowtopo','');
$tahap      =checkPostGet('tahap','');
$thntnm     =checkPostGet('tt','');
$id         =checkPostGet('id','');
$jenis      =checkPostGet('jenis','');
$kgbrondol  =checkPostGet('kgbrondol','');
$jnstgl     =checkPostGet('jnstgl','');
$potbrd     =checkPostGet('potbrd','');
$tgl        =checkPostGet('tgl','');
$tambahan   =checkPostGet('tambahan','');
$tgl1       =tanggalsystemn(checkPostGet('tgl1',''));
$tgl2       =tanggalsystemn(checkPostGet('tgl2',''));

/* if($tahap=='1'){
	$tgl1 = $prd."-01";
	$tgl2 = $prd."-15";
}else{
	$tgl1 = $prd."-16";
	$tgl2 = tglakhir($tgl1);
} */

$hk           =str_replace(',','',$hk);
$jjg          =str_replace(',','',$jjg);
$kg           =str_replace(',','',$kg);
$kgbss        =str_replace(',','',$kgbss);
$kglb1        =str_replace(',','',$kglb1);
$rplb1        =str_replace(',','',$rplb1);
$kglb2        =str_replace(',','',$kglb2);
$rplb2        =str_replace(',','',$rplb2);
$kgbrd        =str_replace(',','',$kgbrd);
$rpbrd        =str_replace(',','',$rpbrd);
$denda        =str_replace(',','',$denda);
$rptopo       =str_replace(',','',$rptopo);
$potbrd       =str_replace(',','',$potbrd);

$nikkar       =makeOption($dbname,'datakaryawan','karyawanid,nik');
$nmorg        =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$jab          =getPostingJabatan('premipanen');
$tglEntry     =date('Ymd'); 
switch($proses){
	case'addpotonganbrd':
		foreach($_SESSION['temppnn'] as $key => $val){
			if($val['mandor']==$mdr and $val['kerani']==$krn and $val['periode']==$prd and $val['notransaksi']==$notransaksi and $val['tanggal']==$tglpnn and $val['blok']==$kdblok and $val['karyawanid']==$kary){
				unset($_SESSION['temppnn'][$key]);
			}
		}
		
		$newdata = array();
		$newdata = array(
			'mandor'     =>$mdr,
			'kerani'     =>$krn,
			'periode'    =>$prd,
			'notransaksi'=>$notransaksi,
			'tanggal'    =>$tglpnn,
			'blok'       =>$kdblok,
			'karyawanid' =>$kary,
			'potbrd'     =>$potbrd
		);
		
		// print_r($newdata);
		// exit("error");
		array_push($_SESSION['temppnn'],$newdata);
	break;
	case'prevdata':
	switch($jenis){
		case'jjgspb':
			$stream="<table class=sortable cellspacing=1 width=100%>";
			$stream.="<thead>";
			$stream.="<tr class=rowheader>";
				$stream.="<td align=center rowspan=2>No</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nospb']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." Panen</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." SPB</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['blok']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tahuntanam']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['janjang']."</td>";
				$stream.="<td align=center width=50px rowspan=2>Kg PKS Sebelum Sortasi</td>";
				$stream.="<td align=center width=50px rowspan=2>Kg Sortasi</td>";
				$stream.="<td align=center width=50px rowspan=2>Kg PKS Setelah Pot Sortasi</td>";
				$stream.="<td align=center width=50px rowspan=2>Brondol</td>";
				$stream.="<td align=center width=50px rowspan=2>Kg PKS Setelah Pot Brondol</td>";
			$stream.="</tr>";
			$stream.="</thead><tbody>";
			
			$no=$stlhbrd=$potbrd=$sblmbrd=$jjkrm="";
			if($kgbrondol=='1'){
				$sblmbrd="style=font-weight:bold;background-color:#B3F7B3;";
			}elseif($kgbrondol=='2'){
				$stlhbrd="style=font-weight:bold;background-color:#B3F7B3;";
			}
			
			$jjkrm="style=font-weight:bold;background-color:#B3F7B3;";
			
			$tjjg=$tkgwb=$tkgwbnetto=$tbrondolan="";
			$str="select * from ".$dbname.".kebun_spb_vw where tanggalpanen = '".$tgl1."' and divisi like '".$afd."%' and divisi like '".$unit."%' order by nospb";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$no++;
				$tit="";
				#$tit="style=cursor:pointer;color:blue; onclick=\"previewdata('".$bar['nospb']."','".tanggalnormal($tgl1)."','event')\" title=\"Click untuk melihat detail SPB : ".$bar['nospb']."\"";
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td align=left ".$tit.">".$bar['nospb']."</td>";
				$stream.="<td align=center style=font-weight:bold;background-color:#B3F7B3;>".tanggalnormal($bar['tanggalpanen'])."</td>";
				$stream.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
				$stream.="<td align=center>".$bar['blok']."</td>";
				$stream.="<td align=center>".$bar['tahuntanam']."</td>";
				$stream.="<td align=right ".$jjkrm.">".@number_format($bar['jjg'],0)."</td>";
				$stream.="<td align=right>".@number_format($bar['kgwb'],0)."</td>";
				$stream.="<td align=right>".@number_format($bar['kgwb']-$bar['kgwbnetto'],0)."</td>";
				$stream.="<td align=right ".$sblmbrd.">".@number_format($bar['kgwbnetto'],0)."</td>";
				$stream.="<td align=right ".$potbrd.">".@number_format($bar['brondolan'],0)."</td>";
				$stream.="<td align=right ".$stlhbrd.">".@number_format($bar['kgwbnetto']-$bar['brondolan'],0)."</td>";
				$stream.="</tr>";
				
				@$tjjg+=$bar['jjg'];
				@$tkgwb+=$bar['kgwb'];
				@$tkgwbnetto+=$bar['kgwbnetto'];
				@$tbrondolan+=$bar['brondolan'];
			}
			
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center colspan=6>TOTAL</td>";
			$stream.="<td align=right ".$jjkrm.">".@number_format($tjjg,0)."</td>";
			$stream.="<td align=right>".@number_format($tkgwb,0)."</td>";
			$stream.="<td align=right>".@number_format($tkgwb-$tkgwbnetto,0)."</td>";
			$stream.="<td align=right ".$sblmbrd.">".@number_format($tkgwbnetto,0)."</td>";
			$stream.="<td align=right ".$potbrd.">".@number_format($tbrondolan,0)."</td>";
			$stream.="<td align=right ".$stlhbrd.">".@number_format($tkgwbnetto-$tbrondolan,0)."</td>";

			$stream.="</tr>";
			$stream.="</tbody><table>";
			
			echo $stream;
		
		break;
		case'kegpnn':
			$stream="<table class=sortable cellspacing=1 width=100%>";
			$stream.="<thead>";
			$stream.="<tr class=rowheader>";
				$stream.="<td align=center rowspan=2>No</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['mandor']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['blok']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['janjang']."</td>";
			$stream.="</tr>";
			$stream.="</thead><tbody>";
			
			$no="";
			$tjjg=$tkgwb=$tkgwbnetto=$tbrondolan="";
			$str="select a.*,b.nikmandor,b.jurnal,b.tanggal from ".$dbname.".kebun_prestasi a  
			left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi  
			where a.kodeorg like '".$afd."%' and b.tanggal = '".$tgl1."' and a.kodeorg like '".$unit."%' 
			and keterangan!='KONTAN' and b.noreferensi='' and b.tipetransaksi='PNN' order by a.notransaksi asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optnamakaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['nikmandor']."' or karyawanid='".$bar['nik']."'");
				$no++;
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td align=center>".$bar['notransaksi']."</td>";
				$stream.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
				$stream.="<td align=left>".$optnamakaryawan[$bar['nikmandor']]."</td>";
				$stream.="<td align=left>".$optnamakaryawan[$bar['nik']]."</td>";
				$stream.="<td align=center>".$bar['kodeorg']."</td>";
				$stream.="<td align=right>".@number_format($bar['hasilkerja'],0)."</td>";
				$stream.="</tr>";
				
				@$tjjg+=$bar['hasilkerja'];
			}
			
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center colspan=6>TOTAL</td>";
			$stream.="<td align=right>".@number_format($tjjg,0)."</td>";
			$stream.="</tr>";
			$stream.="</tbody><table>";
			
			echo $stream;
		
		break;
		case'brd':
			$stream="<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
			$stream.="<thead>";
			$stream.="<tr class=rowheader>";
				$stream.="<td align=center rowspan=2>No</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['mandor']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['blok']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['janjang']."</td>";
			$stream.="</tr>";
			$stream.="</thead><tbody>";
			
			$no="";
			$tjjg=$tkgwb=$tkgwbnetto=$tbrondolan="";
			$str="select a.*,b.nikmandor,b.jurnal,b.tanggal from ".$dbname.".kebun_prestasi a  
			left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi  
			where a.kodeorg like '".$kdblok."%' and b.tanggal = '".$tgl1."' and a.kodekegiatan='611010206'
			and keterangan!='KONTAN' and b.noreferensi='' and b.tipetransaksi!='PNN' order by a.notransaksi asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$optnamakaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['nikmandor']."' or karyawanid='".$bar['nikpemel']."'");
				$no++;
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td align=center>".$bar['notransaksi']."</td>";
				$stream.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
				$stream.="<td align=left>".$optnamakaryawan[$bar['nikmandor']]."</td>";
				$stream.="<td align=left>".$optnamakaryawan[$bar['nikpemel']]."</td>";
				$stream.="<td align=center>".$bar['kodeorg']."</td>";
				$stream.="<td align=right>".@number_format($bar['hasilkerja'],0)."</td>";
				$stream.="</tr>";
				
				@$tjjg+=$bar['hasilkerja'];
			}
			
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center colspan=6>TOTAL</td>";
			$stream.="<td align=right>".@number_format($tjjg,0)."</td>";
			$stream.="</tr>";
			$stream.="</tbody><table>";
			
			echo $stream;
		
		break;
	}
	break;
	case'getdetailkg':
	$stream="<table class=sortable cellspacing=1 width=100%>";
	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
		$stream.="<td align=center rowspan=2>No</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nospb']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." Panen</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." SPB</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." PKS</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['blok']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tahuntanam']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['janjang']."</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg PKS Sebelum Sortasi</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg Sortasi</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg PKS Setelah Pot Sortasi</td>";
		$stream.="<td align=center width=50px rowspan=2>Brondol</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg PKS Setelah Pot Brondol</td>";
	$stream.="</tr>";
	$stream.="</thead><tbody>";
	
	$no=$stlhbrd=$potbrd=$sblmbrd=$kgwbxx=$kgsortx="";
	if($id=='sblmbrd' || $id=='kgnet'){
		$sblmbrd="style=font-weight:bold;background-color:#B3F7B3;";
	}elseif($id=='potbrd'){
		$potbrd="style=font-weight:bold;background-color:#B3F7B3;";
	}elseif($id=='stlhbrd'){
		$stlhbrd="style=font-weight:bold;background-color:#B3F7B3;";
	}elseif($id=='kgwb'){
		$kgwbxx="style=font-weight:bold;background-color:#B3F7B3;";
	}elseif($id=='kgsort'){
		$kgsortx="style=font-weight:bold;background-color:#B3F7B3;";
	}
	
	$whtt=$tgspb=$tgpnn=$tgpks="";
	if($thntnm!=''){
		$whtt="and tahuntanam='".$thntnm."'";
	}
	if($jnstgl=='tglspb'){
		$whtgl=" and tanggal between '".$tgl1."' and '".$tgl2."'";
		$tgspb="style=font-weight:bold;background-color:#B3F7B3;";
	}else{
		$tgpnn="style=font-weight:bold;background-color:#B3F7B3;";
		$whtgl=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
	}
	
	$tjjg=$tkgwb=$tkgwbnetto=$tbrondolan="";
	$str="select * from ".$dbname.".kebun_spb_vw where 1=1 ".$whtgl." ".$whtt." and divisi like '".$afd."%' and divisi like '".$unit."%' order by nospb";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$tglpks=makeOption($dbname,'pabrik_timbangan','nospb,tanggal',"nospb='".$bar['nospb']."'");
		
		$no++;
		$tit="";
		#$tit="style=cursor:pointer;color:blue; onclick=\"previewdata('".$bar['nospb']."','".tanggalnormal($bar['tanggalpanen'])."','event')\" title=\"Click untuk melihat detail SPB : ".$bar['nospb']."\"";
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left ".$tit.">".$bar['nospb']."</td>";
		$stream.="<td align=center ".$tgpnn.">".tanggalnormal($bar['tanggalpanen'])."</td>";
		$stream.="<td align=center ".$tgspb.">".tanggalnormal($bar['tanggal'])."</td>";
		$stream.="<td align=center>".tanggalnormal(substr($tglpks[$bar['nospb']],0,10))."</td>";
		$stream.="<td align=center>".$bar['blok']."</td>";
		$stream.="<td align=center>".$bar['tahuntanam']."</td>";
		$stream.="<td align=right>".@number_format($bar['jjg'],0)."</td>";
		$stream.="<td align=right ".$kgwbxx.">".@number_format($bar['kgwb'],0)."</td>";
		$stream.="<td align=right ".$kgsortx.">".@number_format($bar['kgwb']-$bar['kgwbnetto'],0)."</td>";
		$stream.="<td align=right ".$sblmbrd.">".@number_format($bar['kgwbnetto'],0)."</td>";
		$stream.="<td align=right ".$potbrd.">".@number_format($bar['brondolan'],0)."</td>";
		$stream.="<td align=right ".$stlhbrd.">".@number_format($bar['kgwbnetto']-$bar['brondolan'],0)."</td>";
		$stream.="</tr>";
		
		@$tjjg+=$bar['jjg'];
		@$tkgwb+=$bar['kgwb'];
		@$tkgwbnetto+=$bar['kgwbnetto'];
		@$tbrondolan+=$bar['brondolan'];
	}
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center colspan=7>TOTAL</td>";
	$stream.="<td align=right>".@number_format($tjjg,0)."</td>";
	$stream.="<td align=right ".$kgwbxx.">".@number_format($tkgwb,0)."</td>";
	$stream.="<td align=right ".$kgsortx.">".@number_format($tkgwb-$tkgwbnetto,0)."</td>";
	$stream.="<td align=right ".$sblmbrd.">".@number_format($tkgwbnetto,0)."</td>";
	$stream.="<td align=right ".$potbrd.">".@number_format($tbrondolan,0)."</td>";
	$stream.="<td align=right ".$stlhbrd.">".@number_format($tkgwbnetto-$tbrondolan,0)."</td>";

	$stream.="</tr>";
	$stream.="</tbody><table>";
	
	echo $stream;
	break;
	case'previewdata':
	$stream="<table class=sortable cellspacing=1 width=100%>";
	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
		$stream.="<td align=center rowspan=2>No</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nospb']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." Panen</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." SPB</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." PKS</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['blok']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tahuntanam']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['janjang']."</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg PKS Sebelum Sortasi</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg Sortasi</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg PKS Setelah Pot Sortasi</td>";
		$stream.="<td align=center width=50px rowspan=2>Brondol</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg PKS Setelah Pot Brondol</td>";
	$stream.="</tr>";
	$stream.="</thead><tbody>";
	
	$no=$stlhbrd=$potbrd=$sblmbrd=$kgwbxx=$kgsortx="";
	$kgsortx="style=font-weight:bold;background-color:#B3F7B3;";
	
	$whtt=$tgspb=$tgpnn=$tgpks="";
	
	$tgpnn="style=font-weight:bold;background-color:#B3F7B3;";
	$whtgl=" and tanggalpanen = '".$tgl1."'";
	
	$tjjg=$tkgwb=$tkgwbnetto=$tbrondolan="";
	$str="select * from ".$dbname.".kebun_spb_vw where 1=1 ".$whtgl." and blok='".$kdblok."' order by nospb";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$tglpks=makeOption($dbname,'pabrik_timbangan','nospb,tanggal',"nospb='".$bar['nospb']."'");
		
		$no++;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left >".$bar['nospb']."</td>";
		$stream.="<td align=center ".$tgpnn.">".tanggalnormal($bar['tanggalpanen'])."</td>";
		$stream.="<td align=center ".$tgspb.">".tanggalnormal($bar['tanggal'])."</td>";
		$stream.="<td align=center>".tanggalnormal(substr($tglpks[$bar['nospb']],0,10))."</td>";
		$stream.="<td align=center>".$bar['blok']."</td>";
		$stream.="<td align=center>".$bar['tahuntanam']."</td>";
		$stream.="<td align=right>".@number_format($bar['jjg'],0)."</td>";
		$stream.="<td align=right >".@number_format($bar['kgwb'],0)."</td>";
		$stream.="<td align=right >".@number_format($bar['kgwb']-$bar['kgwbnetto'],0)."</td>";
		$stream.="<td align=right ".$kgsortx.">".@number_format($bar['kgwbnetto'],0)."</td>";
		$stream.="<td align=right >".@number_format($bar['brondolan'],0)."</td>";
		$stream.="<td align=right >".@number_format($bar['kgwbnetto']-$bar['brondolan'],0)."</td>";
		$stream.="</tr>";
		
		@$tjjg+=$bar['jjg'];
		@$tkgwb+=$bar['kgwb'];
		@$tkgwbnetto+=$bar['kgwbnetto'];
		@$tbrondolan+=$bar['brondolan'];
	}
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center colspan=7>TOTAL</td>";
	$stream.="<td align=right>".@number_format($tjjg,0)."</td>";
	$stream.="<td align=right >".@number_format($tkgwb,0)."</td>";
	$stream.="<td align=right >".@number_format($tkgwb-$tkgwbnetto,0)."</td>";
	$stream.="<td align=right ".$kgsortx.">".@number_format($tkgwbnetto,0)."</td>";
	$stream.="<td align=right >".@number_format($tbrondolan,0)."</td>";
	$stream.="<td align=right >".@number_format($tkgwbnetto-$tbrondolan,0)."</td>";

	$stream.="</tr>";
	$stream.="</tbody><table>";
	
	echo $stream;
	break;
	
	case'getdetailkgpks':
	$stream="<table class=sortable cellspacing=1 width=100%>";
	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
		$stream.="<td align=center rowspan=2>No</td>";
		$stream.="<td align=center rowspan=2>No Tiket</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nospb']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']." PKS</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nopol']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['sopir']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['janjang']."</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg PKS Sebelum Sortasi</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg Sortasi</td>";
		$stream.="<td align=center width=50px rowspan=2>Kg PKS Setelah Pot Sortasi</td>";
	$stream.="</tr>";
	$stream.="</thead><tbody>";
	
	$no=$stlhbrd=$potbrd=$sblmbrd=$kgwbxx=$kgsortx="";
	if($id=='kgnet'){
		$stlhbrd="style=font-weight:bold;background-color:#B3F7B3;";
	}elseif($id=='kgwb'){
		$kgwbxx="style=font-weight:bold;background-color:#B3F7B3;";
	}elseif($id=='kgsort'){
		$kgsortx="style=font-weight:bold;background-color:#B3F7B3;";
	}
	
	
	$tjjg=$tkgwb=$tkgwbnetto=$tbrondolan="";
	$n="";
	if($afd!=''){		
		$n=" and divcode like '".$afd."%'";
	}
	
	$w=" and substr(tanggal,1,10) = '".$tgl1."'";
	$str="select * from ".$dbname.".pabrik_timbangan where	1=1 ".$w." ".$n." and kodeorg like '".$unit."%' and kodebarang='40000003' and nospb!=''";
	$res=fetchdata($str);
	foreach($res as $bar){
		$no++;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left >".$bar['notransaksi']."</td>";
		$stream.="<td align=left >".$bar['nospb']."</td>";
		$stream.="<td align=center>".tanggalnormal(substr($bar['tanggal'],0,10))."</td>";
		$stream.="<td align=left >".$bar['nokendaraan']."</td>";
		$stream.="<td align=left>".$bar['supir']."</td>";
		$stream.="<td align=right>".@number_format($bar['jumlahtandan1'],0)."</td>";
		$stream.="<td align=right ".$kgwbxx.">".@number_format($bar['beratbersih'],0)."</td>";
		$stream.="<td align=right ".$kgsortx.">".@number_format($bar['kgpotsortasi'],0)."</td>";
		$stream.="<td align=right ".$stlhbrd.">".@number_format($bar['beratbersih']-$bar['kgpotsortasi'],0)."</td>";
		$stream.="</tr>";
		
		@$tjjg+=$bar['jumlahtandan1'];
		@$tkgwb+=$bar['beratbersih'];
		@$tpot+=$bar['kgpotsortasi'];
	}
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center colspan=6>TOTAL</td>";
	$stream.="<td align=right>".@number_format($tjjg,0)."</td>";
	$stream.="<td align=right ".$kgwbxx.">".@number_format($tkgwb,0)."</td>";
	$stream.="<td align=right ".$kgsortx.">".@number_format($tpot,0)."</td>";
	$stream.="<td align=right  ".$stlhbrd.">".@number_format($tkgwb-$tpot,0)."</td>";
	

	$stream.="</tr>";
	$stream.="</tbody><table>";
	
	echo $stream;
	break;
	
	case'deleteTrans':
		#Validasi :
		#1. Cek Prd Akuntansi
		$str="select * from ".$dbname.".setup_periodeakuntansi
		where periode = '".$prd."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['tutupbuku']=='1'){
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		
		#2. Cek Prd Gaji
		$str="select * from ".$dbname.".sdm_5periodegaji
		where periode = '".$prd."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['sudahproses']=='1'){
			exit('Error : Periode Gaji Sudah di Tutup.');
		}
		
		#3. Cek Transaksi sudah di posting belum
		$str="select * from ".$dbname.".kebun_3premipemanen
		where periode = '".$prd."' and kodeorg='".$unit."' and notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['posting']=='1'){
			exit('Error : Transaksi notransaksi : '.$notransaksi.' unit : '.$unit.' periode : '.$prd.' sudah di Posting.');
		}
		
		if(substr($notransaksi,0,6)!=str_replace("-","",$prd)){
			exit("Error : Nomor Transaksi dan Periode tidak sesuai, silahkan reload.");
		}
		
		#Hapus Transaksi
		$str="delete from ".$dbname.".kebun_3premipemanen where `notransaksi` ='".$notransaksi."' and tanggalpanen between '".$tgl1."' and '".$tgl2."' and periode='".$prd."'"; #exit("error".$str);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		
	break;
    case'savedata':
		if($param['currRow']=='1'){
			#Validasi :
			#1. Cek Prd Akuntansi
			$str="select * from ".$dbname.".setup_periodeakuntansi
			where periode = '".$prd."' and kodeorg='".$unit."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if($bar['tutupbuku']=='1'){
				exit('Error : Periode Akuntansi Sudah di Tutup.');
			}
			
			#2. Cek Prd Gaji
			$str="select * from ".$dbname.".sdm_5periodegaji
			where periode = '".$prd."' and kodeorg='".$unit."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if($bar['sudahproses']=='1'){
				exit('Error : Periode Gaji Sudah di Tutup.');
			}
		}
			
		#3. Cek Transaksi sudah di posting belum
		$str="select * from ".$dbname.".kebun_3premipemanen
		where periode = '".$prd."' and kodeorg='".$unit."' and notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['posting']=='1'){
			exit('Error : Transaksi notransaksi : '.$notransaksi.' unit : '.$unit.' periode : '.$prd.' sudah di Posting.');
		}
		
		if(substr($notransaksi,0,6)!=str_replace("-","",$prd)){
			exit("Error : Nomor Transaksi dan Periode tidak sesuai, silahkan reload.");
		}
	
		$str="select * from ".$dbname.".kebun_spb_vw where tanggalpanen between '".$tgl1."' and '".$tgl2."' and kodeorg='".$unit."' and divisi like '".$afd."%' and posting='0'";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			exit('Error : Ada SPB yang belum di Posting');
		}
		
		$str="select * from ".$dbname.".kebun_prestasi_vs_hk
		where tanggal between '".$tgl1."' and '".$tgl2."' and unit='".$unit."' and kodeorg like '".$afd."%' and jurnal='0'";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			exit('Error : Ada transaksi Kegiatan Panen yang belum di Posting');
		}
		$optkodetopo  =makeOption($dbname,'setup_blok','kodeorg,topografi');
		$topografi = $optkodetopo[$blok];
		
		#CEK APAKAH TANGGAL SUDAH ADA DI PERIODE LAIN
		$str="select * from ".$dbname.".kebun_3premipemanen where 
		kerani='".$krn."' and periode = '".$prd."' and mandor = '".$mdr."' and karyawanid = '".$kary."' 
		and divisi='".$afd."' and tahuntanam = '".$tt."' and tanggalpanen = '".$tglpnn."' and blok = '".$blok."' 
		";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			exit("Error : Transaksi tanggal ".tanggalnormal($tglpnn)." sudah ada,<br>silahkan hapus terlebih dahulu melalui List Data.");
		}
		
		if(substr($notransaksi,0,6)!=str_replace("-","",$prd)){
			exit("Error : Nomor Transaksi dan Periode tidak sesuai, silahkan reload.");
		}
		
		if(substr($tglpnn,0,7)!=$prd){
			exit("Error : Periode dan tanggal panen tidak sesuai, proses dibatalkan.");
		}
		
		$str="insert into ".$dbname.".kebun_3premipemanen (`notransaksi`,`kodeorg`,`divisi`,`periode`,`mandor`,
			 `kerani`,`tahuntanam`,`karyawanid`,`hk`,`jjgpanen`,`kgwb`,`basiskg`,`kglb1`,`rplb1`,`kglb2`,`rplb2`,
			 `kgbrd`,`rpbrd`,`kehadiran`,`denda`,`updateby`,`status`,`tanggalpanen`,`tahap`,`blok`,`potbrdkg`,`tambahan`)
			  values ('".$notransaksi."','".$unit."','".$afd."','".$prd."','".$mdr."','".$krn."','".$tt."','".$kary."',
			  '".$hk."','".$jjg."','".$kg."','".$kgbss."','".$kglb1."','".$rplb1."','".$kglb2."','".$rplb2."',
			  '".$kgbrd."','".$rpbrd."','".$rptopo."','".$denda."','".$_SESSION['standard']['userid']."','".$topografi."','".$tglpnn."','".$tahap."','".$blok."','".$potbrd."','".$tambahan."')";
		
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}			

    break;
	
	case'unposting':
	#========================= Validasi Data ===========================
	#1. Cek Prd Akuntansi
	$str="select * from ".$dbname.".setup_periodeakuntansi where periode = '".$prd."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['tutupbuku']=='1'){
		exit('Error : Periode Akuntansi Sudah di Tutup.');
	}
	#2. Cek Prd Gaji
	$str="select * from ".$dbname.".sdm_5periodegaji where periode = '".$prd."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['sudahproses']=='1'){
		exit('Error : Periode Gaji Sudah di Tutup.');
	}
	#========================= End Validasi Data ===========================
	#============================= Update ==================================
	$errorDB='';
	# Ambil no jurnal
	$queryParam = selectQuery($dbname,'kebun_3premipemanen','distinct (jurnal) as jurnal',"notransaksi='".$notransaksi."'");
	$resParam = fetchData($queryParam);
	# Hapus Jurnal
	$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$resParam[0]['jurnal']."' and noreferensi='".$notransaksi."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Delete Jurnal !: " . $e->getMessage() . "\n"; die();}
	# Update flag transaksi
	$str="update ".$dbname.".kebun_3premipemanen set posting='0', jurnal = '', postingby ='".$_SESSION['standard']['userid']."', postingdate='".$tglEntry."' where notransaksi='".$notransaksi."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Update Flag !: " . $e->getMessage() . "\n"; die();}
	# Hapus Kebun_Aktifitas
	$str="delete from ".$dbname.".kebun_aktifitas where noreferensi ='".$notransaksi."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Delete kebun_aktifitas !: " . $e->getMessage() . "\n"; die();}
	# Jika gagal
	if ($errorDB!=''){
		exit('Error : Unposting gagal di lakukan, '.$errorDB);
	}
	#=========================== End Update ===============================
	break;
	case'view':
	
	$listkary=array();
	$basisk=$rplb=$rpbr=$rptop=$arrtop=$jlhtop=array();
	$listkary=$hk=$jjgpanen=$kgwb=$basiskg=$kglb1=$rplb1=$kglb2=$rplb2=$kgbrd=$rpbrd=$denda=$kehadiran=array();
	$thk=$tjjgpanen=$tkgwb=$tbasiskg=$tkglb1=$trplb1=$tkglb2=$trplb2=$tkgbrd=$trpbrd=$tdenda=$tkehadiran=$ttotal=array();


	# ambil data
	$str="select * from ".$dbname.".kebun_3premipemanen where notransaksi='".$notransaksi."' and periode='".$prd."' and kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$blok=$bar['divisi'];
	}
	
	#ambil basis wb
	$str="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$divisi."' and tahun='".$prd."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$basiskg[$bar['tahuntanam']][$bar['topografi']]=$bar['basis'];
		$rplb1[$bar['tahuntanam']][$bar['topografi']]=$bar['premilebihbasis'];
		$rpbrd[$bar['tahuntanam']][$bar['topografi']]=$bar['premibrondolan'];
		$rptopo[$bar['tahuntanam']][$bar['topografi']]=$bar['premitopografi'];
		$arrtopo[$bar['tahuntanam']][$bar['topografi']]=$bar['topografi'];
		$jlhtopo[$bar['topografi']]=$bar['topografi'];
	}

	$stream='';
	$stream.="<img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='excel' onclick=\"previewexcel('".$notransaksi."','".$prd."','".$unit."','".$divisi."','excel');\" >";
	$stream.="<table><td colspan=2><b>".$_SESSION['lang']['notransaksi']." &nbsp;:</b></td>
					 <td colspan=2><b>".$notransaksi."</b></td>
			  </table>";
			  
	
	if ($tipe == 'excel') {
		$stream.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$stream.="<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
	}
	


	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
		$stream.="<td align=center rowspan=2>No</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['mandor']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kerani']."</td>";
		$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahuntanam']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nik2']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
		$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['topografi']."</td>";
		$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['hk']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>";
		$stream.="<td align=center rowspan=2>Total Kg</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['basic']." Kg</td>";
		$stream.="<td align=center colspan=2>".$_SESSION['lang']['premi']."</td>";
		$stream.="<td align=center colspan=2>".$_SESSION['lang']['brondol']."</td>";
		$stream.="<td align=center rowspan=2>Kehadiran</td>";
		$stream.="<td align=center rowspan=2>Tambahan</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['denda']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>";
	$stream.="</tr>";
	$stream.="<tr>";
		$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
	$stream.="</tr>";
	$stream.="</thead><tbody>";
	# ambil data
	$str="select a.*, b.namakaryawan from ".$dbname.".kebun_3premipemanen a left join datakaryawan b 
	on a.karyawanid=b.karyawanid where a.notransaksi ='".$notransaksi."' order by a.mandor asc, a.tahuntanam asc, b.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$listkary[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]=$bar['status'];
		@$hk[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['hk'];
		@$jjgpanen[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['jjgpanen'];
		@$kgwb[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kgwb'];
		@$basiskg[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['basiskg'];
		@$kglb1[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kglb1'];
		@$rplb1[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rplb1'];
		@$kglb2[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kglb2'];
		@$rplb2[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rplb2'];
		@$kgbrd[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kgbrd'];
		@$rpbrd[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['rpbrd'];
		@$denda[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['denda'];
		@$kehadiran[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['kehadiran'];
		@$pretambahan[$bar['mandor']][$bar['kerani']][$bar['tahuntanam']][$bar['karyawanid']][$bar['status']]+=$bar['tambahan'];
	}
	$no='';
	$optTopografi =makeOption($dbname,'setup_topografi','topografi,keterangan');
	foreach($listkary as $mdr => $val){
		foreach($val as $krn => $key){
			foreach($key as $tt => $key1){
				foreach($key1 as $kary => $key2){
					foreach($key2 as $status){
						$no++;
						$stream.="<tr class=rowcontent>";
						$stream.="<td align=right>".$no."</td>";
						$stream.="<td align=left>".@getNamaKaryawan($mdr)."</td>";
						$stream.="<td align=left>".@getNamaKaryawan($krn)."</td>";
						$stream.="<td align=center>".$tt."</td>";
						$stream.="<td align=center>".@$nikkar[$kary]."</td>";
						$stream.="<td align=left>".@getNamaKaryawan($kary)."</td>";
						$stream.="<td align=left>".$optTopografi[$status]."</td>";
						$stream.="<td align=right>".@hidezerodecimal($hk[$mdr][$krn][$tt][$kary][$status],1)."</td>";
						$stream.="<td align=right>".@hidezerodecimal($jjgpanen[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($kgwb[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($basiskg[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($kglb1[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($rplb1[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($kgbrd[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($rpbrd[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($kehadiran[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($pretambahan[$mdr][$krn][$tt][$kary][$status])."</td>";
						$stream.="<td align=right>".@hidezerodecimal($denda[$mdr][$krn][$tt][$kary][$status])."</td>";
						$total=(($rplb1[$mdr][$krn][$tt][$kary][$status]+$rplb2[$mdr][$krn][$tt][$kary][$status]+$rpbrd[$mdr][$krn][$tt][$kary][$status] +$kehadiran[$mdr][$krn][$tt][$kary][$status]+$pretambahan[$mdr][$krn][$tt][$kary][$status])-$denda[$mdr][$krn][$tt][$kary][$status]);
						$stream.="<td align=right>".@hidezerodecimal($total)."</td>";
						$stream.="</tr>";
						@$thk[$mdr]+=$hk[$mdr][$krn][$tt][$kary][$status];
						@$tjjgpanen[$mdr]+=$jjgpanen[$mdr][$krn][$tt][$kary][$status];
						@$tkgwb[$mdr]+=$kgwb[$mdr][$krn][$tt][$kary][$status];
						@$tbasiskg[$mdr]+=$basiskg[$mdr][$krn][$tt][$kary][$status];
						@$tkglb1[$mdr]+=$kglb1[$mdr][$krn][$tt][$kary][$status];
						@$trplb1[$mdr]+=$rplb1[$mdr][$krn][$tt][$kary][$status];
						@$tkglb2[$mdr]+=$kglb2[$mdr][$krn][$tt][$kary][$status];
						@$trplb2[$mdr]+=$rplb2[$mdr][$krn][$tt][$kary][$status];
						@$tkgbrd[$mdr]+=$kgbrd[$mdr][$krn][$tt][$kary][$status];
						@$trpbrd[$mdr]+=$rpbrd[$mdr][$krn][$tt][$kary][$status];
						@$tdenda[$mdr]+=$denda[$mdr][$krn][$tt][$kary][$status];
						@$tkehadiran[$mdr]+=$kehadiran[$mdr][$krn][$tt][$kary][$status];
						@$ttambahan[$mdr]+=$pretambahan[$mdr][$krn][$tt][$kary][$status];
						@$ttotal[$mdr]+=$total;
					}
				}
			}
		}		
			$stream.="<tr class=rowcontent>";
			$stream.="<td colspan=7 bgcolor='cyan'>Sub Total Kemandoran ".getNamaKaryawan($mdr)."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($thk[$mdr],1)."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tjjgpanen[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgwb[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tbasiskg[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkglb1[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trplb1[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgbrd[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trpbrd[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkehadiran[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($ttambahan[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tdenda[$mdr])."</td>";
			$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($ttotal[$mdr])."</td>";
			$stream.="</tr>";
			@$gthk+=$thk[$mdr];
			@$gtjjgpanen+=$tjjgpanen[$mdr];
			@$gtkgwb+=$tkgwb[$mdr];
			@$gtbasiskg+=$tbasiskg[$mdr];
			@$gtkglb1+=$tkglb1[$mdr];
			@$gtrplb1+=$trplb1[$mdr];
			@$gtkglb2+=$tkglb2[$mdr];
			@$gtrplb2+=$trplb2[$mdr];
			@$gtkgbrd+=$tkgbrd[$mdr];
			@$gtrpbrd+=$trpbrd[$mdr];
			@$gtdenda+=$tdenda[$mdr];
			@$gtkehadiran+=$tkehadiran[$mdr];
			@$gttambahan+=$ttambahan[$mdr];
			@$gttotal+=$ttotal[$mdr];
	}	
		$stream.="<tr class=rowcontent>";
		$stream.="<td bgcolor='cyan' colspan=7>Grand Total</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gthk)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtjjgpanen)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkgwb)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtbasiskg)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkglb1)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtrplb1)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkgbrd)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtrpbrd)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtkehadiran)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gttambahan)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gtdenda)."</td>";
		$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($gttotal)."</td>";
		$stream.="</tr>";
		$stream.="</tbody></table>";
		
		
		if($tipe!='excel'){
			echo $stream;
		}else{
			$nop_="daftar_premi";
			if(strlen($stream)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				fclose($handle);
			}       
		}
	break;
	case'viewdetail':
	$stream='';
	$stream.="<img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='excel' onclick=\"previewexceldetail('".$notransaksi."','".$prd."','".$unit."','".$divisi."','excel');\" >";
	$stream.="<table><td colspan=2><b>".$_SESSION['lang']['notransaksi']." &nbsp;:</b></td>
					 <td colspan=2><b>".$notransaksi."</b></td>
			  </table>";

	if ($tipe == 'excel') {
		$stream.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$stream.="<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
	}

	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>";
		$stream.="<td align=center rowspan=2>Tanggal Panen</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['mandor']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kerani']."</td>";
		$stream.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahuntanam']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nik2']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['topografi']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['blok']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>";
		$stream.="<td align=center rowspan=2>Total Kg</td>";
		$stream.="<td align=center rowspan=2>Potongan<br>Brondolan<br>(Kg)</td>";
		$stream.="<td align=center rowspan=2>Total Kg<br>Netto</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['basic']." Kg</td>";
		$stream.="<td align=center colspan=3>Lebih Basis</td>";
		$stream.="<td align=center colspan=3>".$_SESSION['lang']['brondol']."</td>";
		$stream.="<td align=center rowspan=2>Kehadiran</td>";
		$stream.="<td align=center rowspan=2>Tambahan</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['denda']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>";
	$stream.="</tr>";
	$stream.="<tr>";
		$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['harga']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['harga']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['rp']."</td>";
	$stream.="</tr>";
	$stream.="</thead><tbody>";
	
	# ambil data
	$no='';
	$tjjgpanen=$tpotbrdkg=$tkgwb=$tbasiskg=$tkglb1=$trplb1=$tkgbrd=$trpbrd=$tkehadiran=$tdenda=$ttotal=0;
	$optTopografi =makeOption($dbname,'setup_topografi','topografi,keterangan');
	$str="select a.*, b.namakaryawan from ".$dbname.".kebun_3premipemanen a left join datakaryawan b 
	on a.karyawanid=b.karyawanid where a.notransaksi ='".$notransaksi."' order by a.mandor asc,a.tanggalpanen asc, a.tahuntanam asc, b.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$no++;
		$total=0;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left>".@tanggalnormal($bar['tanggalpanen'])."</td>";
		$stream.="<td align=left>".@getNamaKaryawan($bar['mandor'])."</td>";
		$stream.="<td align=left>".@getNamaKaryawan($bar['kerani'])."</td>";
		$stream.="<td align=center>".$bar['tahuntanam']."</td>";
		$stream.="<td align=left>".@$nikkar[$bar['karyawanid']]."</td>";
		$stream.="<td align=left>".@getNamaKaryawan($bar['karyawanid'])."</td>";
		$stream.="<td align=left>".$optTopografi[$bar['status']]."</td>";
		$stream.="<td align=center>".$bar['blok']."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['jjgpanen'],1)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['kgwb'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['potbrdkg'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['kgwb']-$bar['potbrdkg'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['basiskg'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['kglb1'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['rplb1']/$bar['kglb1'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['rplb1'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['kgbrd'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['rpbrd']/$bar['kgbrd'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['rpbrd'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['kehadiran'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['tambahan'],2)."</td>";
		$stream.="<td align=right>".@hidezerodecimal($bar['denda'],2)."</td>";
		$total=$bar['rplb1']+$bar['rpbrd']+$bar['kehadiran']+$bar['tambahan']-$bar['denda'];
		$stream.="<td align=right>".@hidezerodecimal($total,2)."</td>";
		
		@$tjjgpanen+=$bar['jjgpanen'];
		@$tpotbrdkg+=$bar['potbrdkg'];
		@$tkgwb+=$bar['kgwb'];
		@$tbasiskg+=$bar['basiskg'];
		@$tkglb1+=$bar['kglb1'];
		@$trplb1+=$bar['rplb1'];
		@$tkgbrd+=$bar['kgbrd'];
		@$trpbrd+=$bar['rpbrd'];
		@$tkehadiran+=$bar['kehadiran'];
		@$ttambahan+=$bar['tambahan'];
		@$tdenda+=$bar['denda'];
		@$ttotal+=$total;

	}
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td bgcolor='cyan' colspan=9 align=center>Grand Total</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tjjgpanen)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgwb+$tpotbrdkg)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tpotbrdkg)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgwb)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tbasiskg)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkglb1)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trplb1/$tkglb1)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trplb1)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkgbrd)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trpbrd/$tkgbrd)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($trpbrd)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tkehadiran)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($ttambahan)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($tdenda)."</td>";
	$stream.="<td align=right bgcolor='cyan'>".@hidezerodecimal($ttotal)."</td>";
	
	$stream.="</tr>";
	$stream.="</tbody></table>";
		
		
		if($tipe!='excel'){
			echo $stream;
		}else{
			$nop_="daftar_premi";
			if(strlen($stream)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				fclose($handle);
			}       
		}
	break;
	
	case'viewdetail2':
	## GET PREMI PEMANEN Detail
	$str="select * from ".$dbname.".kebun_3premipemanen where notransaksi='".$notransaksi."'";
	$res=fetchdata($str);
	$tahap = $res[0]['tahap'];
	if($tahap=='1'){
		$tglawal = '01';
		$tglakhir = '15';
	}else{
		$vtglakhir = tglakhir($prd);
		$extglakhir = explode('-',$vtglakhir);
		$tglawal = '16';
		$tglakhir = $extglakhir[2];
	}
	$jlhtgl = $tglakhir-$tglawal+1;
	$arrdata=array();
	$arrhsl=array();
	$arrtotupah=array();
	$arrtotkg=array();
	$arrtotjjg=array();
	foreach($res as $val){
		$opttopografi = makeOption($dbname,'setup_topografi','topografi,keterangan',"topografi='".$val['status']."'");
		$arrtopografi[$val['status']] = $opttopografi[$val['status']];
		
		$optnamakaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
		$optsubbag = makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$val['karyawanid']."'");
		
		$arrdata[$val['karyawanid']]['karyawanid'] = $val['karyawanid'];
		$arrdata[$val['karyawanid']]['namakaryawan'] = $optnamakaryawan[$val['karyawanid']];
		$arrdata[$val['karyawanid']]['subbagian'] = $optsubbag[$val['karyawanid']];
		
		$exptgl = explode('-',$val['tanggalpanen']);
		$arrhsl[$val['karyawanid']][addZero($exptgl[2],2)]['rp'] += $val['rplb1'];
		$arrhsl[$val['karyawanid']][addZero($exptgl[2],2)]['kg'] += $val['kglb1'];
		$arrhsl[$val['karyawanid']][addZero($exptgl[2],2)]['jjg'] += $val['jjgpanen'];
		$arrtotupah[$val['karyawanid']]+=$val['rplb1'];
		$arrtotkg[$val['karyawanid']]+=$val['kglb1'];
		$arrkgbrd[$val['karyawanid']] += $val['kgbrd'];
		$arrtotjjg[$val['karyawanid']]+=$val['jjgpanen'];
		$arrdata[$val['karyawanid']]['insentif']+=$val['kehadiran'];
		$arrdata[$val['karyawanid']]['tambahan']+=$val['tambahan'];
		$arrdata[$val['karyawanid']]['denda']+=$val['denda'];
		$arrdata[$val['karyawanid']]['upah']+=$val['rplb1'];
		$arrdata[$val['karyawanid']]['jjg']+=$val['jjgpanen'];
		$arrdata[$val['karyawanid']]['kgbrd']+=$val['kgbrd'];
		$arrdata[$val['karyawanid']]['rpbrd']+=$val['rpbrd'];
		
		$harga=0;
		$s="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$val['kodeorg']."' and tahun='".$val['periode']."' and tahuntanam ='".$val['tahuntanam']."' and topografi='".$val['status']."'";
		$jlhbss = count(fetchdata($s));
		if($jlhbss==0){
			$s="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$val['kodeorg']."' and tahun<='".$val['periode']."' and tahuntanam ='".$val['tahuntanam']."' and topografi='".$val['status']."' order by tahun desc limit 1";	
		}
		$r=fetchdata($s);
		$harga=$r[0]['premitopografi'];
		$arrjlhtpg[$val['karyawanid']][$val['status']]+=$val['kehadiran']/$harga;
	}
	
	// echo "<pre>";
	// print_r($arrhsl);
	// //exit();
	
	
	
	$stream='';
	$stream.="<img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='excel' onclick=\"previewexceldetail2('".$notransaksi."','".$prd."','".$unit."','".$divisi."','excel');\" >";
	$stream.="<table><td colspan=2><b>".$_SESSION['lang']['notransaksi']." &nbsp;:</b></td>
					 <td colspan=2><b>".$notransaksi."</b></td>
			  </table>";

	if ($tipe == 'excel') {
		$stream.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$stream.="<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
	}

	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nama']."</td>";
		$stream.="<td align=center rowspan=1 colspan='".($jlhtgl)."'>".$_SESSION['lang']['tanggal']."</td>";
		$stream.="<td align=center width=50px rowspan=2>Brd (Kg)</td>";
		$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>";
		$stream.="<td align=center width=50px rowspan=2>Upah Panen (Rp/Kg)</td>";
		$stream.="<td align=center width=50px colspan='".count($arrtopografi)."'>Insentif Kehadiran</td>";
		$stream.="<td align=center colspan='6'>".$_SESSION['lang']['total']." Upah</td>";
		$stream.="<td align=center rowspan='2'>TTD</td>";
	$stream.="</tr>";
	$stream.="<tr>";
		for($i=$tglawal;$i<=$tglakhir;$i++){
			$stream.="<td align=center>".addZero($i,2)."</td>";
		}
		foreach($arrtopografi as $key=>$val){
			$stream.="<td align=center>".$val."</td>";
		}
		$stream.="<td align=center>Panen</td>";
		$stream.="<td align=center>Brondolan</td>";
		$stream.="<td align=center>Insentif</td>";
		$stream.="<td align=center>Tambahan</td>";
		$stream.="<td align=center>Denda</td>";
		$stream.="<td align=center>Total</td>";
	$stream.="</tr>";
	// $stream.="<tr>";
		// $stream.="<td align=center colspan='".(count($arrtopografi)+4)."'></td>";
	// $stream.="</tr>";
	$stream.="</thead><tbody>";
	
	array_multisort(array_column($arrdata, 'namakaryawan'), SORT_ASC, $arrdata);
	
	$no=0;
	foreach($arrdata as $key=>$val){
		$no++;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left>".$val['namakaryawan']."</td>";
		for($i=$tglawal;$i<=$tglakhir;$i++){
			$stream.="<td align=right>".hidezerodecimal($arrhsl[$key][addZero($i,2)]['kg'],2)."</td>";
			$ttlkg[addZero($i,2)]+=$arrhsl[$key][addZero($i,2)]['kg'];
		}
		$stream.="<td align=center>".hidezerodecimal($val['kgbrd'])."</td>"; 
		$stream.="<td align=right>".hidezerodecimal(($arrtotkg[$key]+$val['kgbrd']),2)."</td>";
		$stream.="<td align=right>".hidezerodecimal(($arrtotupah[$key]/$arrtotkg[$key]),0)."</td>";
		foreach($arrtopografi as $key2=>$val2){
			$stream.="<td align=center>".$arrjlhtpg[$key][$key2]."</td>";
			$ttltopo[$key2]+=$arrjlhtpg[$key][$key2];
		}
		$stream.="<td align=right>".hidezerodecimal($val['upah'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal($val['rpbrd'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal($val['insentif'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal($val['tambahan'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal($val['denda'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal(($val['upah']+$val['rpbrd']+$val['insentif']+$val['tambahan']-$val['denda']),2)."</td>";
		$stream.="<td align=left rowspan=2></td>";
		$stream.="</tr>";
		
		$ttl['brd']+=$val['kgbrd'];
		$ttl['ttl']+=($arrtotkg[$key]+$val['kgbrd']);
		$ttl['upah']+=$val['upah'];
		$ttl['rpbrd']+=$val['rpbrd'];
		$ttl['insentif']+=$val['insentif'];
		$ttl['tambahan']+=$val['tambahan'];
		$ttl['denda']+=$val['denda'];
		$ttl['gtl']+=($val['upah']+$val['rpbrd']+$val['insentif']+$val['tambahan']-$val['denda']);
		
		###################################
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=2 align=center>".$val['subbagian']."</td>";
		for($i=$tglawal;$i<=$tglakhir;$i++){
			$stream.="<td align=right>".hidezerodecimal($arrhsl[$key][addZero($i,2)]['jjg'],2)."</td>";
			$ttljjg[addZero($i,2)]+=$arrhsl[$key][addZero($i,2)]['jjg'];
		}
		$stream.="<td align=left></td>";
		$stream.="<td align=right>".hidezerodecimal($val['jjg'],2)."</td>";
		$stream.="<td align=left></td>";
		foreach($arrtopografi as $key2=>$val2){
			$stream.="<td align=left></td>";
		}
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="</tr>";
		
		$ttl['jjg']+=$val['jjg'];
		## SPACE
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan='".(13+$jlhtgl+count($arrtopografi))."'></td>";
		$stream.="</tr>";
	}
	
	#KG
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=left rowspan=2>TOTAL</td>";
	$stream.="<td align=right>Kg</td>";
	for($i=$tglawal;$i<=$tglakhir;$i++){
		$stream.="<td align=right>".hidezerodecimal($ttlkg[addZero($i,2)],2)."</td>";
	}
	$stream.="<td align=right>".hidezerodecimal($ttl['brd'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['ttl'],2)."</td>";
	$stream.="<td align=right></td>";
	foreach($arrtopografi as $key2=>$val2){
		$stream.="<td align=center>".$ttltopo[$key2]."</td>";
	}
	$stream.="<td align=right>".hidezerodecimal($ttl['upah'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['rpbrd'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['insentif'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['tambahan'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['denda'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['gtl'],2)."</td>";
	$stream.="<td></td>";
	$stream.="</tr>";
	
	#JJG
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=right>Jjg</td>";
	for($i=$tglawal;$i<=$tglakhir;$i++){
		$stream.="<td align=right>".hidezerodecimal($ttljjg[addZero($i,2)],2)."</td>";
	}
	$stream.="<td></td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['jjg'],2)."</td>";
	$stream.="<td></td>";
	foreach($arrtopografi as $key2=>$val2){
		$stream.="<td align=left></td>";
	}
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="</tr>";
	
	$stream.="</tr>";
	$stream.="</tbody></table>";
		
		
		if($tipe!='excel'){
			echo $stream;
		}else{
			$nop_="daftar_premi";
			if(strlen($stream)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				fclose($handle);
			}       
		}
	break;
	case'loaddata':
        $where = "";
		$wh = "";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = "";
			$whj = "";
		} else if($_SESSION['empl']['subbagian']!=''){
			$where = " and divisi = '".$_SESSION['empl']['subbagian']."'";
			$whj = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
			$wh = " and b.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}else{
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
			$whj = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
			$wh = " and b.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
        if ($prdlist != '') {
            $where.=" and periode='" . $prdlist . "' ";
            $whj.=" and periode='" . $prdlist . "' ";
            $wh.=" and b.tanggal like '" . $prdlist . "%' ";
        }
        if ($unitlist != '') {
            $where.=" and kodeorg='" . $unitlist . "' ";
			$wh.=" and b.kodeorg = '" . $unitlist . "' ";
        }
		if ($afdlist != '') {
			$where.=" and divisi='" . $afdlist . "' ";
		}
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $strx="select * from ".$dbname.".kebun_3premipemanen where 1=1 ".$where." group by notransaksi order by notransaksi asc, periode desc, kodeorg asc, divisi asc";
        $resxx=fetchdata($strx);
		$jlhbrs=count($resxx);

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$tab = "";
        $no = $maxdisplay;
		
		#cek jurnal
		// $str = "select nojurnal, noreferensi, sum(debet) as rpj from ".$dbname.".keu_jurnaldt_vw where noreferensi in (SELECT distinct notransaksi FROM " . $dbname . ".kebun_3premipemanen where 1=1 ".$where." group by notransaksi) ".$whj." group by nojurnal, noreferensi";
		// $res    =fetchData($str);
		// foreach($res as $val){			
			// $rpjurnal[$val['nojurnal']][$val['noreferensi']]+=$val['rpj'];
		// }
		
		// $str = "select b.noreferensi, sum(upahpremilebihbasis) as prelb from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 ".$wh." group by noreferensi"; 
		// $res    =fetchData($str);
		// foreach($res as $val){			
			// $rppres[$val['noreferensi']]+=$val['prelb'];
		// }
		
		// $strmin="select notransaksi, min(tanggalpanen) as tglawal,max(tanggalpanen) as tglakhir from ".$dbname.".kebun_3premipemanen where 1=1 ".$whj." group by notransaksi"; 
		// $barmin=fetchData($strmin);
		// foreach($barmin as $valmin){			
			// $tglmin[$valmin['notransaksi']]=$valmin['tglawal'];
			// $tglmax[$valmin['notransaksi']]=$valmin['tglakhir'];
		// }

        $str = "SELECT tanggalpanen, tahap, kodeorg, notransaksi, divisi, periode,sum(tambahan) as tambahan , sum(hk) as hk, sum(jjgpanen) as jjgpanen, sum(kgwb) as kgwb, sum(basiskg) as basiskg, sum(kglb1) as kglb1, sum(rplb1) as rplb1, sum(kglb2) as kglb2, sum(rplb2) as rplb2, sum(kgbrd) as kgbrd, sum(rpbrd) as rpbrd, sum(denda) as denda,sum(kehadiran) as kehadiran, jurnal, posting, updateby FROM " . $dbname . ".kebun_3premipemanen
		where 1=1 ".$where." group by notransaksi order by notransaksi desc, periode desc, kodeorg asc, divisi asc limit " . $offset . "," . $limit . "";
		$resx=fetchdata($str);
		// $jlhbrs=count($resx);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $no = 0;
        while ($bar = $res->fetch()) {
			$notofj=$color='';
			if(strlen($bar['notransaksi'])=='23'){				
				if($bar['tahap']=='1'){
					$tglmin=$bar['periode']."-01";
					$tglmax=$bar['periode']."-15";
				}else{
					$tglmin=$bar['periode']."-06";
					$tglmax=tglakhir($bar['periode']."-01");
				}
				$tanggal3=tanggalnormal($tglmin)." - ".tanggalnormal($tglmax);
			}else{
				$tglmin=$bar['tanggalpanen'];
				$tglmax=$bar['tanggalpanen'];
				$tanggal3=tanggalnormal($tglmin);
			}
			
			$totalpre=(($bar['rplb1']+$bar['rplb2']+$bar['rpbrd']+$bar['kehadiran']+$bar['tambahan'])-$bar['denda']);
			
			#vs jurnal
			$valjurnal=($totalpre - $rpjurnal[$bar['jurnal']][$bar['notransaksi']]);
			if(($valjurnal > 2 or $valjurnal < (-2)) and $bar['posting']==1){
				$notofj="Nilai di Jurnal tidak sama,<br>silahkan unposting kemudian posting ulang<br>Varian : ".number_format($valjurnal)."";
				$color=" style=background-color:red;";
			}else if($bar['posting']==1){
				$notofj="Posted";
			}else{
				$notofj="Not Posted";
			}
			
			#vs pres
			$valpres=($totalpre - $rppres[$bar['notransaksi']]);
			if(($valpres > 2 or $valpres < (-2)) and $bar['posting']==1){
				$notofp="Nilai di Kegiatan Panen tidak sama,<br>silahkan unposting kemudian posting ulang<br>Varian : ".number_format($valpres)."";
				$color=" style=background-color:red;";
			}else if($bar['posting']==1){
				$notofp="Posted";
			}else{
				$notofp="Not Posted";
			}
			
            $isi = '';
            $no+=1;
            $tab.="<tr class=rowcontent ".$color." id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=center>".$bar['periode']."</td>";
            $tab.="<td align=center>".$tanggal3."</td>";
            $tab.="<td>" . $bar['notransaksi'] . "</td>";            
            $tab.="<td>" . $bar['kodeorg'] . "</td>";            
            $tab.="<td>" . $bar['divisi'] . "</td>";            
            //$tab.="<td align=right>".@number_format($bar['hk'])."</td>";
            $tab.="<td align=right>".@number_format($bar['jjgpanen']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['kgwb']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['basiskg']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['kglb1']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['rplb1']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['kgbrd']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['rpbrd']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['kehadiran']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['tambahan']) . "</td>";
            $tab.="<td align=right>".@number_format($bar['denda']) . "</td>";
            $tab.="<td align=right>".@number_format(($bar['rplb1']+$bar['rpbrd']+$bar['kehadiran']+$bar['tambahan'])-$bar['denda']) . "</td>";
			
            $tab.="<td>" . getNamaKaryawan($bar['updateby']) . "</td>";
            // $tab.="<td ".$color.">".$notofj."</td>";
            // $tab.="<td ".$color.">".$notofp."</td>";
			if ($bar['posting'] == 0) {
                $isi.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                    onclick=\"del('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".tanggalnormal($tglmin)."','".tanggalnormal($tglmax)."');\" ></td>";
				$post='';
				if(in_array($_SESSION['empl']['jabatan'],$jab,true)){
					$post=" onclick=\"posting('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$no."');\" ";
				}
			   $isi.="<td align=center width=20px><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30' ".$post." title='Posting'></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab,true)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$no."');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$isi.="<td></td>";
                $isi.="<td align=center width=20px><img src=".$icon." class=zImgBtn class=zImgBtn height='30' title='".$title."' ".$unpost." ></td>";
            }
            $isi.="<td align=center width=20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View' 
                    onclick=\"view('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['divisi']."','html');\" ></td>
					
					<td align=center width=20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View Detail' 
                    onclick=\"viewdetail('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['divisi']."','html');\" >
					
					<td align=center width=20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View Detail' 
                    onclick=\"viewdetail2('".$bar['notransaksi']."','".$bar['periode']."','".$bar['kodeorg']."','".$bar['divisi']."','html');\" >
					
					</td>";
            $tab.=$isi;
            $tab.="</tr>";
        }
        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr>
                     <tr><td colspan=23 align=center>";
        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }
        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td>
            </tr>";
	echo $tab . "####" . $footd;
	break;
    ######EXCEL	
	case 'excel':
		$tglSkrg=date("Ymd");
		$nop_="daftar_premi_mandor";
		if(strlen($stream)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
	break;
	case'pivot':
		
		
		
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = "";
		} else {
			$where.= " and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		}
		
		if($param['divisi']!=''){
			$where.=" and divisi='".$param['divisi']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeorg='".$param['kodeorg']."'";
		}

		$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		
		$str = "select *  from " . $dbname . ".kebun_3premipemanen where 1=1 and periode='".$param['periode']."' ".$where.""; #exit("error$str");
		$res = fetchdata($str);
		
		$data[]=array('NOTRANSAKSI','POSTING','JURNAL','KODEORG','DIVISI','PERIODE','TAHAP','TANGGAL','BLOK','TT','TOPOGRAFI','MANDOR','KERANI','NIK','NAMA','JENIS','DATA','JUMLAH');
		$datasort= array();
		$row     = array("KODEORG","DIVISI","NIK","NAMA");
		$col     = array("TAHAP","JENIS","DATA");
		$val     = array("JUMLAH");
		foreach($res as $bar){
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'DENDA',
				$bar['denda']*(-1)
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'TAMBAHAN',
				$bar['tambahan']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'KEHADIRAN',
				$bar['kehadiran']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'RP BRD',
				$bar['rpbrd']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'RP LB',
				$bar['rplb1']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'KG',
				'KG LB',
				$bar['kglb1']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'KG',
				'POT BRD (Kg)',
				$bar['potbrdkg']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'KG',
				'KG',
				$bar['kgwb']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['posting'],
				$bar['jurnal'],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tahap'],
				$bar['tanggalpanen'],
				$nmorg[$bar['blok']],
				$bar['tahuntanam'],
				$bar['status'],
				$nmkary[$bar['mandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'JJG',
				'JJG',
				$bar['jjgpanen']
			);
			
		}
		
		echo json_encode($data)."####".json_encode($row)."####".json_encode($col)."####".json_encode($val)."####".json_encode($datasort);
		
	break;
    default:
}

?>