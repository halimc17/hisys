<?php
require_once('master_validation.php');
//require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$pt         = checkPostGet('pt', '');
$regional   = checkPostGet('regional', '');
$gudang     = checkPostGet('gudang', '');
$periode    = checkPostGet('periode', '');
$periode1   = checkPostGet('periode1', '');
$revisi     = checkPostGet('revisi', '');
$kdKel      = checkPostGet('kdKel', '');
$nojurnal   = checkPostGet('nojurnal', '');
$nik        = checkPostGet('nik', '');
$ref        = checkPostGet('ref', '');
$ket        = checkPostGet('ket', '');
$tipelaporan= checkPostGet('tipelaporan', '');
$noakun = checkPostGet('noakun', '');
$nodok = checkPostGet('nodok', '');
$start      = checkPostGet('start', '');
$length     = checkPostGet('length', '');
$draw       = checkPostGet('draw', '');


// $tipeorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,tipe');

$stream='';

if($periode=='' or $periode1==''){
	exit("Warning:Tanggal tidak boleh kosong");
}

if($periode!=''){
	$periode = tanggalsystemn($periode);
}
if($periode1!=''){
	$periode1 = tanggalsystemn($periode1);
}

if($tipelaporan=='excel'){
	#= batasi rentang tanggal untuk export penuh (tanpa nyentuh database dulu, jadi instan).
	#= untuk rentang lebar (berbulan-bulan), datanya bisa jutaan baris: query-nya sendiri sudah kepotong
	#= timeout Apache (default 60 detik) sebelum sempat menghasilkan apa-apa, DAN Excel sendiri cuma
	#= sanggup ~1 juta baris per sheet - jadi walau server dikuatkan pun hasilnya tetap tidak akan utuh.
	$rentangHari = (strtotime($periode1) - strtotime($periode)) / 86400;
	if($rentangHari > 45){
		exit("Warning: Rentang tanggal terlalu lebar (".($rentangHari+1)." hari) untuk export Excel. Data sebanyak itu bisa jutaan baris - selain proses generate-nya berisiko timeout, Excel sendiri cuma sanggup menampilkan sekitar 1 juta baris per file. Silakan persempit rentang tanggal (disarankan maksimal 45 hari / sekitar 1.5 bulan), atau export bertahap per bulan.");
	}
}


$where="";
if($kdKel!=''){
   $where.=" and a.kodejurnal='".$kdKel."'  "; 
}

if($regional=='' && $gudang==''){
   $where.=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}else if($regional!='' && $gudang==''){
    $where.=" and a.kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
}else{
    $where.=" and a.kodeorg='".$gudang."'";
}

if($ref!=''){
    $where.=" and a.noreferensi like '%".$ref."%'";
}

if($ket!=''){
    $where.=" and a.keterangan like '%".$ket."%' ";
}

if($nojurnal!=''){
    @$where.=" and a.nojurnal like '%".$nojurnal."%' ";
}


if($nojurnal!=''){
    @$where.=" and a.nojurnal like '%".$nojurnal."%' ";
}

if($nik!=''){
    @$where.=" and a.nik='".$nik."' ";
}

if($noakun!=''){
    @$where.=" and a.noakun='".$noakun."' ";
}

if($nodok!=''){
    @$where.=" and a.nodok='".$nodok."' ";
}


#= namakegiatan
$str="select * from ".$dbname.".setup_kegiatan";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$namakegiatan[$bar->kodekegiatan]=$bar->namakegiatan;
}

#= namakegiatan
$aresta=$owlPDO->query("SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok");
$aresta->setFetchMode(PDO::FETCH_ASSOC);
while($res=$aresta->fetch()){
    $tahuntanam[$res['kodeorg']]=$res['tahuntanam'];
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

// $nmnik	= makeOption($dbname,'datakaryawan','karyawanid,nik');

$res=$owlPDO->query("SELECT karyawanid, nik, namakaryawan FROM ".$dbname.".datakaryawan");
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $nmnik[$bar['karyawanid']]=$bar['nik'];
    $nkary[$bar['karyawanid']]=$bar['namakaryawan'];
}

#= namaorg (dulu dipanggil per-baris lewat getNamaOrg())
$namaorg=array();
$res=$owlPDO->query("SELECT kodeorganisasi, namaorganisasi FROM ".$dbname.".organisasi");
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $namaorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

#= namabarang (dulu dipanggil per-baris lewat getNamaBrg())
$namabrg=array();
$res=$owlPDO->query("SELECT kodebarang, namabarang FROM ".$dbname.".log_5masterbarang");
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $namabrg[$bar['kodebarang']]=$bar['namabarang'];
}

#= nopol kendaraan (dulu dipanggil per-baris lewat getNopol())
$nopolvhc=array();
$res=$owlPDO->query("SELECT kodevhc, detailvhc FROM ".$dbname.".vhc_5master");
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $nopolvhc[$bar['kodevhc']]=$bar['detailvhc'];
}



$usingLeanQuery = false;
$sql="select a.*,b.namaakun,c.novoucher,c.cgttu from ".$dbname.".keu_jurnaldt_vw a
left join ".$dbname.".keu_5akun b
on a.noakun=b.noakun
left join ".$dbname.".keu_kasbankht c on a.noreferensi=c.notransaksi
where a.tanggal between '".$periode."' and '".$periode1."'
".$kdOrgSch."
and a.nojurnal NOT LIKE '%CLSM%' ".$where."
and a.revisi<='".$revisi."'";
// if($_SESSION['standard']['userid']=='0000000003'){
// echo $sql;
// }

if($tipelaporan=='json' && $start!=='' && $length!==''){
	#= server-side pagination untuk DataTables: hindari tarik+urutkan seluruh data sekaligus.
	#= tanpa ORDER BY, MySQL bisa berhenti begitu dapat $length baris lewat index tanggal (jauh lebih cepat dari filesort atas seluruh hasil).
	if($kdKel!=''){
		$sqlCount="select count(*) as cnt from ".$dbname.".keu_jurnaldt a
		left join ".$dbname.".keu_jurnalht b on a.nojurnal=b.nojurnal
		where a.tanggal between '".$periode."' and '".$periode1."'
		".$kdOrgSch."
		and a.nojurnal NOT LIKE '%CLSM%' ".$where."
		and a.revisi<='".$revisi."'";
	}else{
		$sqlCount="select count(*) as cnt from ".$dbname.".keu_jurnaldt a
		where a.tanggal between '".$periode."' and '".$periode1."'
		".$kdOrgSch."
		and a.nojurnal NOT LIKE '%CLSM%' ".$where."
		and a.revisi<='".$revisi."'";
	}

	$countKey='jurnalxxcnt_'.md5($sqlCount);
	if(isset($_SESSION[$countKey]) && $_SESSION[$countKey]['exp']>time()){
		$totalCount=$_SESSION[$countKey]['val'];
	}else{
		$rcnt=$owlPDO->query($sqlCount);
		$rcnt->setFetchMode(PDO::FETCH_ASSOC);
		$totalCount=intval($rcnt->fetch()['cnt']);
		$_SESSION[$countKey]=array('val'=>$totalCount,'exp'=>time()+300);
	}

	#= urut berdasarkan tanggal dulu (bukan nojurnal) - kolom ini sama dgn yg dipakai index filter di atas,
	#= jadi MySQL bisa kasih hasil terurut TANPA filesort (tetap cepat) sekaligus data tampil urut tanggal.
	$sqlPage=$sql." order by a.tanggal, a.nojurnal, a.nourut limit ".intval($start).",".intval($length);
	$strp=$owlPDO->query($sqlPage);
	$strp->setFetchMode(PDO::FETCH_ASSOC);
	$data=array();
	while($bar=$strp->fetch()){
		if($nmnik[$bar['nik']]!=''){
			$karya = $nmnik[$bar['nik']]." - ".$nkary[$bar['nik']];
		}else{
			$karya = "";
		}
		$debet=0;
		$kredit=0;
		if($bar['jumlah']>0){
			$debet=$bar['jumlah'];
		}else{
			$kredit=$bar['jumlah']*-1;
		}
		$data[]=array(
			clearSpecialChar($bar['nojurnal']),
			clearSpecialChar($bar['kodejurnal']),
			clearSpecialChar($namajurnal[$bar['kodejurnal']]),
			clearSpecialChar($nmauto[$bar['autojurnal']]),
			clearSpecialChar($bar['novoucher']),
			clearSpecialChar($bar['tanggal']),
			clearSpecialChar($bar['kodeorg']),
			clearSpecialChar($bar['noakun']),
			clearSpecialChar($bar['namaakun']),
			clearSpecialChar($bar['cgttu']),
			clearSpecialChar(htmlentities($bar['keterangan'])),
			$debet,
			$kredit,
			clearSpecialChar($bar['noreferensi']),
			clearSpecialChar($bar['nodok']),
			clearSpecialChar((isset($namaorg[$bar['kodeblok']]) && $namaorg[$bar['kodeblok']]!='')? $namaorg[$bar['kodeblok']]: $bar['kodeblok']),
			clearSpecialChar($tahuntanam[$bar['kodeblok']]),
			clearSpecialChar($bar['kodekegiatan']),
			clearSpecialChar($namakegiatan[$bar['kodekegiatan']]),
			$karya,
			clearSpecialChar($bar['kodevhc']),
			clearSpecialChar(isset($nopolvhc[$bar['kodevhc']])? $nopolvhc[$bar['kodevhc']]: ''),
			clearSpecialChar($bar['kodebarang']),
			clearSpecialChar(isset($namabrg[$bar['kodebarang']])? $namabrg[$bar['kodebarang']]: ''),
			clearSpecialChar($bar['revisi'])
		);
	}

	header('Content-Type: application/json');
	echo json_encode(array(
		"draw"=>intval($draw),
		"recordsTotal"=>$totalCount,
		"recordsFiltered"=>$totalCount,
		"data"=>$data
	));
	exit;
}

$sql.=" order by a.tanggal, a.nojurnal, a.nourut";

if($tipelaporan!='json'){
	$str=$owlPDO->query($sql);
	$str->setFetchMode(PDO::FETCH_OBJ);
}
$no=0;
{
	$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$gudang."'");
	$border=0;

	#= untuk mode excel: tulis langsung ke file CSV+gzip per baris (streaming), jangan ditumpuk di variabel
	#= string dulu - dataset besar (ratusan ribu baris) bisa menghabiskan memory_limit PHP dan bikin request gagal (500).
	#= CSV dipakai (bukan tabel HTML dibungkus .xls) karena Excel jauh lebih cepat buka CSV murni -
	#= tabel HTML ratusan ribu baris berat banget di-parse Excel walau file-nya sudah di tangan.
	$gzExcel=null;
	if($tipelaporan=='excel'){
		$qwe=date("YmdHms");
		#= uniqid ditambahkan supaya nama file tidak bentrok kalau ada 2 request nyaris bersamaan (mis. double click) -
		#= sejak file ditulis streaming (bukan sekali tulis di akhir), file yg sama akan corrupt kalau 2 proses menulis bareng.
		$nop_="NeracaSaldo_".$gudang.$periode."rev".$revisi."___".$qwe."_".uniqid();
		$gzExcel = gzopen("tempExcel/".$nop_.".csv.gz", "w9");
		gzwrite($gzExcel, "Laporan Jurnal\r\n");
		gzwrite($gzExcel, "".$gudang." - ".$nmorg[$gudang]."\r\n");
		gzwrite($gzExcel, "".tanggalnormal($periode)." s/d ".tanggalnormal($periode1)."\r\n\r\n");
		gzwrite($gzExcel, csvLine(array(
			$_SESSION['lang']['nojurnal'],$_SESSION['lang']['kodejurnal'],$_SESSION['lang']['namajurnal'],
			$_SESSION['lang']['tipe'],$_SESSION['lang']['novoucher'],$_SESSION['lang']['tanggal'],
			$_SESSION['lang']['unit'],$_SESSION['lang']['noakun'],$_SESSION['lang']['namaakun'],
			'Tipe Pembayaran',$_SESSION['lang']['keterangan'],$_SESSION['lang']['debet'],$_SESSION['lang']['kredit'],
			$_SESSION['lang']['noreferensi'],$_SESSION['lang']['nodok'],$_SESSION['lang']['kodeblok'],
			$_SESSION['lang']['tahuntanam'],$_SESSION['lang']['kodekegiatan'],$_SESSION['lang']['namakegiatan'],
			$_SESSION['lang']['nik'],$_SESSION['lang']['kodevhc'],$_SESSION['lang']['nopol'],
			$_SESSION['lang']['kodebarang'],$_SESSION['lang']['namabarang'],$_SESSION['lang']['revisi']
		)));
	}

	$header="<table id=pvtTable cellpadding=1 cellspacing=1 border=".$border." class='sortable nowrap' width='100%' data-scroll-x='true' scroll-collapse='false'>
	 			<thead>
					<tr>

						<th align=center >".$_SESSION['lang']['nojurnal']."</th>
						<th align=center >".$_SESSION['lang']['kodejurnal']."</th>
						<th align=center >".$_SESSION['lang']['namajurnal']."</th>
						<th align=center >".$_SESSION['lang']['tipe']."</th>
						<th align=center >".$_SESSION['lang']['novoucher']."</th>
						<th align=center >".$_SESSION['lang']['tanggal']."</th>
						<th align=center >".$_SESSION['lang']['unit']."</th>
						<th align=center >".$_SESSION['lang']['noakun']."</th>
						<th align=center >".$_SESSION['lang']['namaakun']."</th>
						<th align=center >Tipe Pembayaran</th>
						<th align=center >".$_SESSION['lang']['keterangan']."</th>
						<th align=center >".$_SESSION['lang']['debet']."</th>
						<th align=center >".$_SESSION['lang']['kredit']."</th>
						<th align=center >".$_SESSION['lang']['noreferensi']."</th>
						<th align=center >".$_SESSION['lang']['nodok']."</th>
						<th align=center >".$_SESSION['lang']['kodeblok']."</th>
						<th align=center >".$_SESSION['lang']['tahuntanam']."</th>
						<th align=center >".$_SESSION['lang']['kodekegiatan']."</th>
						<th align=center >".$_SESSION['lang']['namakegiatan']."</th>
						<th align=center >".$_SESSION['lang']['nik']."</th>
						<th align=center >".$_SESSION['lang']['kodevhc']."</th>
						<th align=center >".$_SESSION['lang']['nopol']."</th>
						<th align=center >".$_SESSION['lang']['kodebarang']."</th>
						<th align=center >".$_SESSION['lang']['namabarang']."</th>
						<th align=center >".$_SESSION['lang']['revisi']."</th>
					</tr>

				</thead>
				<tbody>";
	if(!$gzExcel){
		$stream.=$header;
	}
				if($tipelaporan!='json'){
					$tdebet = $tkredit = 0;
					while($bar=$str->fetch()){
						$no+=1;
						$debet=0;
						$kredit=0;
						if($bar->jumlah>0)
							$debet=$bar->jumlah;
						else
							$kredit=$bar->jumlah*-1;

						#= samakan sumber data terlepas dari query mana yg dipakai (view lengkap, atau lean+preload)
						if($usingLeanQuery){
							$rowKodejurnal = isset($jurnalhtMap[$bar->nojurnal])? $jurnalhtMap[$bar->nojurnal][0]: '';
							$rowAutojurnal = isset($jurnalhtMap[$bar->nojurnal])? $jurnalhtMap[$bar->nojurnal][1]: '';
							$rowNovoucher  = isset($kasbankMap[$bar->noreferensi])? $kasbankMap[$bar->noreferensi][0]: '';
							$rowCgttu      = isset($kasbankMap[$bar->noreferensi])? $kasbankMap[$bar->noreferensi][1]: '';
							$rowNamaakun   = isset($namaakunMap[$bar->noakun])? $namaakunMap[$bar->noakun]: '';
						}else{
							$rowKodejurnal = $bar->kodejurnal;
							$rowAutojurnal = $bar->autojurnal;
							$rowNovoucher  = $bar->novoucher;
							$rowCgttu      = $bar->cgttu;
							$rowNamaakun   = $bar->namaakun;
						}

						if($gzExcel){
							gzwrite($gzExcel, csvLine(array(
								$bar->nojurnal,$rowKodejurnal,$namajurnal[$rowKodejurnal],$nmauto[$rowAutojurnal],
								$rowNovoucher,tanggalnormal($bar->tanggal),$bar->kodeorg,$bar->noakun,$rowNamaakun,
								$rowCgttu,$bar->keterangan,number_format($debet,2,'.',''),number_format($kredit,2,'.',''),$bar->noreferensi,$bar->nodok,$bar->kodeblok,
								(isset($tahuntanam[$bar->kodeblok])? $tahuntanam[$bar->kodeblok]: ''),$bar->kodekegiatan,
								@$namakegiatan[$bar->kodekegiatan],$nmnik[$bar->nik],$bar->kodevhc,
								(isset($nopolvhc[$bar->kodevhc])? $nopolvhc[$bar->kodevhc]: ''),$bar->kodebarang,
								(isset($namabrg[$bar->kodebarang])? $namabrg[$bar->kodebarang]: ''),$bar->revisi
							)));
						}else{
							$stream.="<tr class=rowcontent>

							<td>".$bar->nojurnal."</td>
							<td>".$rowKodejurnal."</td>
							<td>".$namajurnal[$rowKodejurnal]."</td>
							<td>".$nmauto[$rowAutojurnal]."</td>
							<td>".$rowNovoucher."</td>
							<td >".tanggalnormal($bar->tanggal)."</td>
							<td align=center >".$bar->kodeorg."</td>
							<td>".$bar->noakun."</td>
							<td>".$rowNamaakun."</td>
							<td>".$rowCgttu."</td>
							<td>".$bar->keterangan."</td>
							<td align=right  >".number_format($debet,2)."</td>
							<td align=right  >".number_format($kredit,2)."</td>
							<td align=center>".$bar->noreferensi."</td>
							<td align=center>".$bar->nodok."</td>
							<td align=center>".$bar->kodeblok."</td>
							<td align=center>".(isset($tahuntanam[$bar->kodeblok])? $tahuntanam[$bar->kodeblok]: '')."</td>
							<td align=center>".$bar->kodekegiatan."</td>
							<td align=center>".@$namakegiatan[$bar->kodekegiatan]."</td>
							<td align=center >".$nmnik[$bar->nik]."</td>
							<td align=center>".$bar->kodevhc."</td>
							<td align=center>".(isset($nopolvhc[$bar->kodevhc])? $nopolvhc[$bar->kodevhc]: '')."</td>
							<td align=center >".$bar->kodebarang."</td>
							<td align=center >".(isset($namabrg[$bar->kodebarang])? $namabrg[$bar->kodebarang]: '')."</td>
							<td align=center >".$bar->revisi."</td>
							</tr>";
						}
						$tdebet+=$debet;
						$tkredit+=$kredit;
					}
				}
				if($tipelaporan=='html' or $tipelaporan=='json'){
					$stream.="</tbody>";
					/* $stream.="
							<tfoot>
								<tr>
									
									<th align=center >".$_SESSION['lang']['nojurnal']."</th>
									<th align=center >".$_SESSION['lang']['kodejurnal']."</th>
									<th align=center >".$_SESSION['lang']['namajurnal']."</th>
									<th align=center >".$_SESSION['lang']['tipe']."</th>
									<th align=center >".$_SESSION['lang']['novoucher']."</th>
									<th align=center >".$_SESSION['lang']['tanggal']."</th>
									<th align=center >".$_SESSION['lang']['unit']."</th>
									<th align=center >".$_SESSION['lang']['noakun']."</th>
									<th align=center >".$_SESSION['lang']['namaakun']."</th>
									<th align=center >Tipe Pembayaran</th>
									<th align=center >".$_SESSION['lang']['keterangan']."</th>
									<th align=right>".$_SESSION['lang']['debet']."</th>
									<th align=right >".$_SESSION['lang']['kredit']."</th>
									<th align=center >".$_SESSION['lang']['noreferensi']."</th>    
									<th align=center >".$_SESSION['lang']['kodeblok']."</th>
									<th align=center >".$_SESSION['lang']['tahuntanam']."</th>
									<th align=center >".$_SESSION['lang']['kodekegiatan']."</th>
									<th align=center >".$_SESSION['lang']['namakegiatan']."</th>
									<th align=center >".$_SESSION['lang']['nik']."</th>
									<th align=center >".$_SESSION['lang']['revisi']."</th>
								</tr>  
							</tfoot>"; */
				}

		if(!$gzExcel){
			$stream.="</table>";
		}
}





if($tipelaporan=='html'){
	echo $stream;
}else if ($tipelaporan=='json'){
	#= shell tabel kosong; baris data ditarik terpisah oleh DataTables lewat server-side draw (blok di atas)
	echo $stream;
}else{
	// exit("Error:A");
	#= file gzip sudah ditulis langsung (streaming) selama loop di atas, tinggal tutup & arahkan browser ke situ.
	#= diarahkan lewat keu_download_excel.php supaya nama file yang di-download user rapi/manusiawi,
	#= bukan nama penyimpanan internal yang sengaja dibikin unik (timestamp+uniqid).
	if($gzExcel){
		gzwrite($gzExcel, "\r\nPrint Time:".date('Y-m-d H:i:s')." By:".$_SESSION['empl']['name']."\r\n");
		gzclose($gzExcel);
		$niceName="Laporan_Jurnal_".$gudang."_".$periode."_sd_".$periode1.".csv";
		#= sembunyikan loading indicator di jendela induk secara langsung di sini (bukan mengandalkan
		#= event 'onload' iframe) - begitu redirect ke URL download (Content-Disposition:attachment),
		#= iframe tidak dianggap browser sebagai "selesai loading" secara normal, jadi onload tidak reliable.
		echo "<script language=javascript1.2>
			try{
				var elLoading = window.parent.document.getElementById('printFileLoading');
				if(elLoading){ elLoading.style.display='none'; }
			}catch(e){}
			window.location='keu_download_excel.php?f=".urlencode($nop_.".csv.gz")."&name=".urlencode($niceName)."';
			</script>";
	}
}

function clearSpecialChar($tulisan){
	$hasil='';
	$hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $tulisan); //remove non-ascii chars
	return $hasil;
}

#= escape 1 baris nilai jadi format CSV yang aman (kutip nilai yg mengandung koma/petik/baris baru)
function csvLine($cols){
	$out=array();
	foreach($cols as $val){
		$val=(string)$val;
		#= kode seperti NIK/nomor akun/kode blok sering diawali angka 0 (mis. "0000010163") - tanpa penanda ini
		#= Excel otomatis membacanya sebagai angka dan membuang nol di depannya, mengubah nilai aslinya.
		#= Diawali kutip satu (') memaksa Excel memperlakukannya sebagai teks apa adanya.
		if(preg_match('/^0\d+$/',$val) || preg_match('/^\d{12,}$/',$val)){
			$val="'".$val;
		}
		if(strpos($val,',')!==false || strpos($val,'"')!==false || strpos($val,"\n")!==false || strpos($val,"\r")!==false){
			$val='"'.str_replace('"','""',$val).'"';
		}
		$out[]=$val;
	}
	return implode(',',$out)."\r\n";
}
?>