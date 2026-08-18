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



$sql="select a.*,b.namaakun,c.novoucher,c.cgttu from ".$dbname.".keu_jurnaldt_vw a
left join ".$dbname.".keu_5akun b
on a.noakun=b.noakun
left join ".$dbname.".keu_kasbankht c on a.noreferensi=c.notransaksi 
where a.tanggal between '".$periode."' and '".$periode1."'
".$kdOrgSch."
and a.nojurnal NOT LIKE '%CLSM%' ".$where."
and a.revisi<='".$revisi."' 
order by a.nojurnal, a.nourut"; 
// if($_SESSION['standard']['userid']=='0000000003'){
// echo $sql;
// }	


$str=$owlPDO->query($sql);
$str->setFetchMode(PDO::FETCH_OBJ);
$no=0;
if(!$str){
    $stream."<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}else{
	$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$gudang."'");
	$border=0;
	if($tipelaporan=='excel'){
		$stream.="Laporan Jurnal<br>";
		$stream.="".$gudang." - ".$nmorg[$gudang]."<br>";
		$stream.="".tanggalnormal($periode)." s/d ".tanggalnormal($periode1)."<br><br>";
		$border=1;
	}
	$stream.="<table id=pvtTable cellpadding=1 cellspacing=1 border=".$border." class='sortable nowrap' width='100%' data-scroll-x='true' scroll-collapse='false'>
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

						$stream.="<tr class=rowcontent>
							
							<td>".$bar->nojurnal."</td>
							<td>".$bar->kodejurnal."</td>
							<td>".$namajurnal[$bar->kodejurnal]."</td>";
							
							$stream.="<td>".$nmauto[$bar->autojurnal]."</td>";
							
							
							$stream.="   <td>".$bar->novoucher."</td>
							<td >".tanggalnormal($bar->tanggal)."</td>
							<td align=center >".$bar->kodeorg."</td>
							<td>".$bar->noakun."</td>
							<td>".$bar->namaakun."</td>
							<td>".$bar->cgttu."</td>
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
							<td align=center>".getNopol($bar->kodevhc)."</td>
							<td align=center >".$bar->kodebarang."</td>
							<td align=center >".getNamaBrg($bar->kodebarang)."</td>
							<td align=center >".$bar->revisi."</td>
							</tr>"; 	
						$tdebet+=$debet;
						$tkredit+=$kredit;
					}	
				}elseif($tipelaporan=='json'){
					$res = fetchdata($sql);
					$adadata = count($res);
					foreach($res as $bar){
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
							clearSpecialChar(getNamaOrg($bar['kodeblok'])),
							clearSpecialChar($tahuntanam[$bar['kodeblok']]),
							clearSpecialChar($bar['kodekegiatan']),
							clearSpecialChar($namakegiatan[$bar['kodekegiatan']]),
							$karya,
							clearSpecialChar($bar['kodevhc']),
							clearSpecialChar(getNopol($bar['kodevhc'])),
							clearSpecialChar($bar['kodebarang']),
							clearSpecialChar(getNamaBrg($bar['kodebarang'])),
							clearSpecialChar($bar['revisi'])
						);
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
				
		$stream.="</table>";		
} 	





if($tipelaporan=='html'){
	echo $stream;
}else if ($tipelaporan=='json'){
	if($adadata>0){		
		echo $stream."####".json_encode($data);
	}else{
		exit("Warning: Data Kosong.");
	}
}else{
	// exit("Error:A");
	$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
	$qwe=date("YmdHms");
	$nop_="NeracaSaldo_".$gudang.$periode."rev".$revisi."___".$qwe;
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

function clearSpecialChar($tulisan){
	$hasil='';
	$hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $tulisan); //remove non-ascii chars
	return $hasil;
}
?>