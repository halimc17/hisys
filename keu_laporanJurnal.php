<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$pt = checkPostGet('pt', '');
$regional = checkPostGet('regional', '');
$gudang = checkPostGet('gudang', '');
$periode = checkPostGet('periode', '');
$periode1 = checkPostGet('periode1', '');
$revisi = checkPostGet('revisi', '');
$kdKel = checkPostGet('kdKel', '');
$nojurnal = checkPostGet('nojurnal', '');
$nik = checkPostGet('nik', '');
$ref = checkPostGet('ref', '');
$ket = checkPostGet('ket', '');
$tipelaporan = checkPostGet('tipelaporan', '');


$tipeorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,tipe');

// if(intval(str_replace('-','',substr($periode1,0,7)))-intval(str_replace('-','',substr($periode,0,7)))>4){
    // exit('error: periode terlalu panjang');
// }
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
if($kdKel!='')
{
   $where.=" and a.nojurnal like '%/".$kdKel."/%'  "; 
}

if($regional=='' && $gudang=='')
{
   $where.=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
else if($regional!='' && $gudang=='')
{
    //$kdOrgSch=" and a.kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."')";

    
    $where.=" and a.kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
    
}
else
{
    $where.=" and a.kodeorg='".$gudang."'";
}

if($ref!='')
{
    $where.=" and a.noreferensi like '%".$ref."%'";
}

if($ket!='')
{
    $where.=" and a.keterangan like '%".$ket."%' ";
}

if($nojurnal!='')
{
    @$where.=" and a.nojurnal like '%".$nojurnal."%' ";
}


if($nojurnal!='')
{
    @$where.=" and a.nojurnal like '%".$nojurnal."%' ";
}

if($nik!='')
{
    @$where.=" and a.nik='".$nik."' ";
}


/*
if($regional=='JAKARTA'){
	
}else{
	if($tipeorganisasi[$gudang]=='HOLDING' || $tipeorganisasi[$gudang]=='KANWIL'){
		
	}else{
		if($nik=='' and $nojurnal=='' and $ref=='' and $kdKel==''){
			exit("Warning:Nik / Nojurnal / Noreferensi / Kelompok jurnal tidak bileh kosong / seluruhnya secara bersamaan");
		}
	}
}
*/


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



/*
$str="select a.*,b.namaakun,c.novoucher from ".$dbname.".keu_jurnaldt_vw a
left join ".$dbname.".keu_5akun b
on a.noakun=b.noakun
left join ".$dbname.".keu_kasbankht c on a.noreferensi=c.notransaksi 
where a.tanggal between '".$periode."' and '".$periode1."'
".$kdOrgSch."
and a.nojurnal NOT LIKE '%CLSM%' ".$kdKelSch." ".@$nojurnalsch."
and a.revisi<='".$revisi."' ".@$refKet."  
order by a.nojurnal";   
*/

$str="select a.*,b.namaakun,c.novoucher,c.cgttu from ".$dbname.".keu_jurnaldt_vw a
left join ".$dbname.".keu_5akun b
on a.noakun=b.noakun
left join ".$dbname.".keu_kasbankht c on a.noreferensi=c.notransaksi 
where a.tanggal between '".$periode."' and '".$periode1."'
".$kdOrgSch."
and a.nojurnal NOT LIKE '%CLSM%' ".$where."
and a.revisi<='".$revisi."' 
order by a.nojurnal, a.nourut";   
// echo $str;





// $optref = makeOption($dbname,'keu_jurnaldt_vw','nojurnal,noreferensi');
// $opttipe = makeOption($dbname,'keu_kasbankht','notransaksi,cgttu');

//=================================================
$str=$owlPDO->query($str);
$str->setFetchMode(PDO::FETCH_OBJ);
$no=0;
if($tipelaporan=='excel'){
	$stream.="<table class=sortable cellspacing=1 border=1 width=100%>
			
				
					<tr>
						<th align=center >".$_SESSION['lang']['nourut']."</th>
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
						<th align=center >".$_SESSION['lang']['kodeblok']."</th>
						<th align=center >".$_SESSION['lang']['kodeasset']."</th>
						<th align=center >".$_SESSION['lang']['tahuntanam']."</th>
						<th align=center >".$_SESSION['lang']['kodekegiatan']."</th>
						<th align=center >".$_SESSION['lang']['namakegiatan']."</th>
						<th align=center >".$_SESSION['lang']['revisi']."</th>
		   
				</tr> 
			";
			
}

if(!$str){
    $stream."<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}else{
	
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
		$gen='generic.css';
	}else if($theme=='red'){
		$gen='genericRed.css';  
	}else{
		$gen='genericGray.css';  
	} 
	echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>
	";  
	$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$gudang."'");
	$stream.="Laporan Jurnal<br>";
	//$stream.="".$gudang." - ".$nmorg[$gudang]."<br>";
	$stream.="".tanggalnormal($periode)." s/d ".tanggalnormal($periode1)."<br>";
	$stream.="<table class='sortable' cellpadding=5 cellspacing='1' border='0'>
	 			<thead>
					<tr>
						<th align=center >".$_SESSION['lang']['nourut']."</th>
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
						<th align=center >".$_SESSION['lang']['kodeblok']."</th>
						<th align=center >".$_SESSION['lang']['kodeasset']."</th>
						<th align=center >".$_SESSION['lang']['tahuntanam']."</th>
						<th align=center >".$_SESSION['lang']['kodekegiatan']."</th>
						<th align=center >".$_SESSION['lang']['namakegiatan']."</th>
						<th align=center >".$_SESSION['lang']['revisi']."</th>
					</tr>  
				</thead>
				<tbody>";
				$tdebet = $tkredit = 0;
				while($bar=$str->fetch())
				{
					$no+=1;
					$debet=0;
					$kredit=0;
					if($bar->jumlah>0)
						$debet=$bar->jumlah;
					else
						$kredit=$bar->jumlah*-1;

					$stream.="<tr class=rowcontent>
						<td align=center>".$no."</td>
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
						<td align=center>".getNamaOrg($bar->kodeblok)."</td>
						<td align=center>".$bar->kodeasset."</td>
						<td align=center>".(isset($tahuntanam[$bar->kodeblok])? $tahuntanam[$bar->kodeblok]: '')."</td>
						<td align=center>".$bar->kodekegiatan."</td>
						<td align=center>".@$namakegiatan[$bar->kodekegiatan]."</td>
						<td align=center >".$bar->revisi."</td>
						</tr>"; 	
					$tdebet+=$debet;
					$tkredit+=$kredit;
				}	
				$stream.="<tr class=rowcontent>
					<td align=center colspan=12>Total</td>
					<td align=right >".number_format($tdebet,2)."</td>
					<td align=right >".number_format($tkredit,2)."</td>
					<td align=center colspan=7></td>
					</tr>"; 
	$stream.="</tbody>
			<tfoot>
			</tfoot>		 
		</table>";		
} 	





if($tipelaporan=='html'){
	echo $stream;
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



?>