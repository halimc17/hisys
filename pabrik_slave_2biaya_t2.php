<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$kdorg=checkPostGet('kdorgt','');
$per=checkPostGet('pert','');
$proses=checkPostGet('proses','');
$expblnbgt=  explode('-', $per);
$blnbgt=$expblnbgt[1];


if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}


$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where  (induk='".$kdorg."' or kodeorganisasi='".$kdorg."') and namaorganisasi not like '%GUDANG%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$station[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	$nmstation[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}
@$nmstation[''].=$_SESSION['lang']['lain'];


##ambil produksi
$str=" select * from ".$dbname.".pabrik_produksi where tanggal like '".substr($per,0,4)."%' and kodeorg='".$kdorg."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bi
	if(substr($bar['tanggal'],0,7)==$per){
		$tbsbi+=$bar['tbsdiolah'];
		$cpobi+=$bar['oer'];
		$kerbi+=$bar['oerpk'];
		$palmbi+=$bar['oer']+$bar['oerpk'];
	}
	//sdbi
	$tbssdbi+=$bar['tbsdiolah'];
	$cposdbi+=$bar['oer'];
	$kersdbi+=$bar['oerpk'];
	$palmsdbi+=$bar['oer']+$bar['oerpk'];
}


$addstrtbs="(";
$addstrcpo="(";
$addstrker="(";
$addstrbgt="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10){
        $isitbs="olah0".$i;
		$isicpo="kgcpo0".$i;
		$isiker="kgker0".$i;
		$isibgt="rp0".$i;
		
    }
    else{
        $isitbs="olah".$i;
		$isicpo="kgcpo".$i;
		$isiker="kgker".$i;
		$isibgt="rp".$i;
    }
    if($i<intval($blnbgt)){
        $addstrtbs.=$isitbs."+";
		$addstrcpo.=$isicpo."+";
		$addstrker.=$isiker."+";
		$addstrbgt.=$isibgt."+";
    }
    else{
        $addstrtbs.=$isitbs;
		$addstrcpo.=$isicpo;
		$addstrker.=$isiker;
		$addstrbgt.=$isibgt;
    }
}
$addstrtbs.=")";
$addstrcpo.=")";
$addstrker.=")";
$addstrbgt.=")";

##bgt produksi
$str=" select olah".$blnbgt." as tbsbi,".$addstrtbs." as tbssdbi,kgolah as tbsthn,
			kgcpo".$blnbgt." as cpobi,".$addstrcpo." as cposdbi,kgcpo as cpothn,
			kgker".$blnbgt." as kerbi,".$addstrker." as kersdbi,kgkernel as kerthn
		from ".$dbname.".bgt_produksi_pks_vw where tahunbudget = '".substr($per,0,4)."' and millcode='".$kdorg."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$bgttbsbi+=$bar['tbsbi'];
	@$bgttbssdbi+=$bar['tbssdbi'];
	@$bgttbsthn+=$bar['tbsthn'];
		@$bgtcpobi+=$bar['cpobi'];
		@$bgtcposdbi+=$bar['cposdbi'];
		@$bgtcpothn+=$bar['cpothn'];
	@$bgtkerbi+=$bar['kerbi'];
	@$bgtkersdbi+=$bar['kersdbi'];
	@$bgtkerthn+=$bar['kerthn'];
		@$bgtpalmbi+=$bar['kerbi']+$bar['cpobi'];
		@$bgtpalmsdbi+=$bar['kersdbi']+$bar['cposdbi'];
		@$bgtpalmthn+=$bar['kerthn']+$bar['cpothn'];	
}

$stream.="<thead>
    <tr class=rowheader>
       <td align=center rowspan=2></td>
       <td align=center rowspan=2 colspan=3></td>
       <td align=center colspan=6>".$_SESSION['lang']['bi']."</td>
	   <td align=center colspan=6>".$_SESSION['lang']['sbi']."</td>
	   <td align=center colspan=3 rowspan=2>".$_SESSION['lang']['budget']." ".$_SESSION['lang']['tahun']."</td>
	 </tr>
	 <tr>
		<td align=center colspan=3>".$_SESSION['lang']['realisasi']."</td>
		<td align=center colspan=3>".$_SESSION['lang']['budget']."</td>
		<td align=center colspan=3>".$_SESSION['lang']['realisasi']."</td>
		<td align=center colspan=3>".$_SESSION['lang']['budget']."</td>
	 </tr>";
$stream.="</thead>";

$stream.="
	<tr class=rowcontent>
		<td align=center rowspan=6></td>
		<td align=left rowspan=4 valign=top>".$_SESSION['lang']['produksi']."</td>
		<td align=left>".$_SESSION['lang']['tbs']."</td>
		<td align=left>(".$_SESSION['lang']['kg'].")</td>  
		<td align=right colspan=3>".@number_format($tbsbi)."</td> 
		<td align=right colspan=3>".@number_format($bgttbsbi)."</td> 
		<td align=right colspan=3>".@number_format($tbssdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgttbssdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgttbsthn)."</td> 
    </tr>
	 <tr class=rowcontent>
		<td align=left>".$_SESSION['lang']['cpo']."</td>
		<td align=left>(".$_SESSION['lang']['kg'].")</td>  
		<td align=right colspan=3>".@number_format($cpobi)."</td> 
		<td align=right colspan=3>".@number_format($bgtcpobi)."</td> 
		<td align=right colspan=3>".@number_format($cposdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtcposdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtcpothn)."</td> 
    </tr>
	 <tr class=rowcontent>
		<td align=left>".$_SESSION['lang']['kernel']."</td>
		<td align=left>(".$_SESSION['lang']['kg'].")</td> 
		<td align=right colspan=3>".@number_format($kerbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtkerbi)."</td> 
		<td align=right colspan=3>".@number_format($kersdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtkersdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtkerthn)."</td> 
    </tr>
	
	<tr class=rowcontent>
		<td align=left>".$_SESSION['lang']['palm']."</td>
		<td align=left>(".$_SESSION['lang']['kg'].")</td> 
		<td align=right colspan=3>".@number_format($palmbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtpalmbi)."</td> 
		<td align=right colspan=3>".@number_format($palmsdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtpalmsdbi)."</td> 
		<td align=right colspan=3>".@number_format($bgtpalmthn)."</td> 				
    </tr>
	<tr class=rowcontent>
		<td align=center rowspan=2 colspan=3></td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
		<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
		<td align=center colspan=2>".$_SESSION['lang']['rpperkg']."</td>
    </tr>
	<tr class=rowcontent>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
		<td align=center>".$_SESSION['lang']['palm']."</td>
		<td align=center>".$_SESSION['lang']['tbs']."</td>
    </tr>
	";

	
##ambil sumber akun
$str="select * from ".$dbname.".keu_5mesinlaporandt where "
        . " namalaporan='ANALISA BIAYA PABRIK'  and tipe='detail' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	@$jumlahjenis+=1;
    $mlnurut[$bar['nourut']]=$bar['nourut'];
    $mlnama[$bar['nourut']]=$bar['keterangandisplay'];
    $mlnoakundari[$bar['nourut']]=$bar['noakundari'];
    $mlnoakunsampai[$bar['nourut']]=$bar['noakunsampai'];
    $mlnoakundisplay[$bar['nourut']]=$bar['noakundisplay'];
}
	
	
@$lastday = date('t', strtotime($periode));	

$tglawal=substr($per,0,4).'-01-01';
$tglakhir=$per.'-'.$lastday;

$where=" and kodeorg='".$kdorg."' and tanggal between '".$tglawal."' '".$tglakhir."'   ";


	
foreach($mlnurut as $nourutlaporan){
	
	$noakuntidak[$nourutlaporan]=explode(",",$mlnoakundisplay[$nourutlaporan]);
    
    
    if($mlnoakundisplay[$nourutlaporan]!='' || $mlnoakundisplay[$nourutlaporan]!='0'){
        $jum[$nourutlaporan]=  count($noakuntidak[$nourutlaporan])-1;
        $where2=" and noakun not in (";
        $penutupwhere2=")";
    }
	
	for($i=0;$i<=$jum[$nourutlaporan];$i++){
        if($jum[$nourutlaporan]==0){
            $where2.=" '".$noakuntidak[$nourutlaporan][$i]."' ";
        }else{
            if($i==$jum[$nourutlaporan]){
                $where2.=" '".$noakuntidak[$nourutlaporan][$i]."' ";
            }
            else{
                $where2.=" '".$noakuntidak[$nourutlaporan][$i]."', ";
            }
        }
    }
    
    $isiwhere2[$nourutlaporan]=$where2.$penutupwhere2;
	

	##realisasi
	$str="select jumlah,substr(kodeblok,1,6) as kodeblok,tanggal from ".$dbname.".keu_jurnaldt_vw where kodeorg='".$kdorg."' and 
			tanggal between '".$tglawal."' and '".$tglakhir."' and nojurnal not like '%PRSDN%' and
			noakun between '".$mlnoakundari[$nourutlaporan]."' and '".$mlnoakunsampai[$nourutlaporan]."'
			".$isiwhere2[$nourutlaporan]."  ";
			
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		if(substr($bar['tanggal'],0,7)==$per){
			@$isidataurutbi[$nourutlaporan]+=$bar['jumlah'];
			@$isidatastbi[$bar['kodeblok']]+=$bar['jumlah'];
			@$isistakunbi[$bar['kodeblok']][$nourutlaporan]+=$bar['jumlah'];
		}
		
		@$isidataurutsdbi[$nourutlaporan]+=$bar['jumlah'];
		@$isidatastsdbi[$bar['kodeblok']]+=$bar['jumlah'];
		@$isistakunsdbi[$bar['kodeblok']][$nourutlaporan]+=$bar['jumlah'];
	}
	
	
	##bgt where tahunbudget = '".substr($per,0,4)."' and millcode='".$kdorg."' ";
	$str="select substr(kodeorg,1,6) as kodeblok,rp".$blnbgt." as bi,".$addstrbgt." as sdbi,rupiah as thn
			from ".$dbname.".bgt_budget_detail where tahunbudget = '".substr($per,0,4)."' and kodeorg like '".$kdorg."%' 
			and noakun between '".$mlnoakundari[$nourutlaporan]."' and '".$mlnoakunsampai[$nourutlaporan]."' 
			".$isiwhere2[$nourutlaporan]."  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$bgturutbi[$nourutlaporan]+=$bar['bi'];
		$bgturutsdbi[$nourutlaporan]+=$bar['sdbi'];
		$bgturutthn[$nourutlaporan]+=$bar['thn'];
		
		$bgtstbi[$bar['kodeblok']]+=$bar['bi'];
		$bgtstsdbi[$bar['kodeblok']]+=$bar['sdbi'];
		$bgtstthn[$bar['kodeblok']]+=$bar['thn'];
		
		$bgtstakunbi[$bar['kodeblok']][$nourutlaporan]+=$bar['bi'];
		$bgtstakunsdbi[$bar['kodeblok']][$nourutlaporan]+=$bar['sdbi'];
		$bgtstakunthn[$bar['kodeblok']][$nourutlaporan]+=$bar['thn'];

		
	}

	
}


// echo"<pre>";
// print_r($bgturutbi);
// echo"</pre>";

	

@$station[''].='';
array_multisort($station,SORT_DESC);	
foreach($station as $subunit){
	$no+=1;
	
	
	$stream.="
		<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=left colspan=3>".$nmstation[$subunit]."</td>
			<td align=right>".@number_format($isidatastbi[$subunit])."</td>
			<td align=right>".@number_format($isidatastbi[$subunit]/$palmbi,2)."</td>
			<td align=right>".@number_format($isidatastbi[$subunit]/$tbsbi,2)."</td>
				<td align=right>".@number_format($bgtstbi[$subunit])."</td>
				<td align=right>".@number_format($bgtstbi[$subunit]/$bgtpalmbi,2)."</td>
				<td align=right>".@number_format($bgtstbi[$subunit]/$bgttbsbi,2)."</td>
			<td align=right>".@number_format($isidatastsdbi[$subunit])."</td>
			<td align=right>".@number_format($isidatastsdbi[$subunit]/$palmsdbi,2)."</td>
			<td align=right>".@number_format($isidatastsdbi[$subunit]/$tbssdbi,2)."</td>
				<td align=right>".@number_format($bgtstsdbi[$subunit])."</td>
				<td align=right>".@number_format($bgtstsdbi[$subunit]/$bgtpalmsdbi,2)."</td>
				<td align=right>".@number_format($bgtstsdbi[$subunit]/$bgttbssdbi,2)."</td>
			<td align=right>".@number_format($bgtstthn[$subunit])."</td>
			<td align=right>".@number_format($bgtstthn[$subunit]/$bgtpalmthn,2)."</td>
			<td align=right>".@number_format($bgtstthn[$subunit]/$bgttbsthn,2)."</td>
		</tr>";		
	@$totisidatastbi+=$isidatastbi[$subunit];
	@$totisidatastsdbi+=$isidatastsdbi[$subunit];
	
	@$totbgtstbi+=$bgtstbi[$subunit];
	@$totbgtstsdbi+=$bgtstsdbi[$subunit];
	@$totbgtstthn+=$bgtstthn[$subunit];
}	
$stream.="
	<tr class=rowcontent>
		<td></td>
		<td bgcolor=#00CCFF colspan=3>".$_SESSION['lang']['total']." ".$_SESSION['lang']['biayalangsung']."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totisidatastbi)."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totisidatastbi/$palmbi,2)."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totisidatastbi/$tbsbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgtstbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgtstbi/$bgtpalmbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgtstbi/$bgttbsbi,2)."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totisidatastsdbi)."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totisidatastsdbi/$palmsdbi,2)."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totisidatastsdbi/$tbssdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgtstsdbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgtstsdbi/$bgtpalmsdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgtstsdbi/$bgttbssdbi,2)."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totbgtstthn)."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totbgtstthn/$bgtpalmthn,2)."</td>
		<td bgcolor=#00CCFF align=right>".@number_format($totbgtstthn/$bgttbsthn,2)."</td>
	</tr>
";	
	
	
foreach($mlnurut as $nourutlaporan){
	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td colspan=3>".$mlnama[$nourutlaporan]."</td>
			<td align=right>".@number_format($isidataurutbi[$nourutlaporan])."</td>
			<td align=right>".@number_format($isidataurutbi[$nourutlaporan]/$palmbi)."</td>
			<td align=right>".@number_format($isidataurutbi[$nourutlaporan]/$tbsbi)."</td>
				<td  align=right>".@number_format($bgturutbi[$nourutlaporan])."</td>
				<td  align=right>".@number_format($bgturutbi[$nourutlaporan]/$bgtpalmbi,2)."</td>
				<td  align=right>".@number_format($bgturutbi[$nourutlaporan]/$bgttbsbi,2)."</td>			
			<td align=right>".@number_format($isidataurutsdbi[$nourutlaporan])."</td>
			<td align=right>".@number_format($isidataurutsdbi[$nourutlaporan]/$palmsdbi)."</td>
			<td align=right>".@number_format($isidataurutsdbi[$nourutlaporan]/$tbssdbibi)."</td>
				<td  align=right>".@number_format($bgturutsdbi[$nourutlaporan])."</td>
				<td  align=right>".@number_format($bgturutsdbi[$nourutlaporan]/$bgtpalmsdbi,2)."</td>
				<td  align=right>".@number_format($bgturutsdbi[$nourutlaporan]/$bgttbssdbi,2)."</td>			
			<td  align=right>".@number_format($bgturutthn[$nourutlaporan])."</td>
			<td  align=right>".@number_format($bgturutthn[$nourutlaporan]/$bgtpalmthn,2)."</td>
			<td  align=right>".@number_format($bgturutthn[$nourutlaporan]/$bgttbsthn,2)."</td>	
		</tr>";	
	@$totisidataurutbi+=$isidataurutbi[$nourutlaporan];
	@$totisidataurutsdbi+=$isidataurutsdbi[$nourutlaporan];
		@$totbgturutbi+=$bgturutbi[$nourutlaporan];
		@$totbgturutsdbi+=$bgturutsdbi[$nourutlaporan];
		@$totbgturutthn+=$bgturutthn[$nourutlaporan];
}	
	


	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td bgcolor=#00CCFF colspan=3>".$_SESSION['lang']['total']." ".$_SESSION['lang']['biayalangsung']."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi/$palmbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi/$tbsbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi/$bgtpalmbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi/$bgttbsbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi/$palmsdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi/$tbssdbibi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi/$bgttbssdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn/$bgtpalmthn,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn/$bgttbsthn,2)."</td>
		</tr>";	
	/*
	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td bgcolor=#00FFFF colspan=3>".$_SESSION['lang']['total']." ".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['tidaklangsung']."</td>
			<td bgcolor=#00FFFF align=right></td>
			<td bgcolor=#00FFFF align=right></td>
			<td bgcolor=#00FFFF align=right></td>
			<td bgcolor=#00FFFF></td>
			<td bgcolor=#00FFFF></td>
			<td bgcolor=#00FFFF></td>
			
			<td bgcolor=#00FFFF align=right></td>
			<td bgcolor=#00FFFF align=right></td>
			<td bgcolor=#00FFFF align=right></td>
			<td bgcolor=#00FFFF></td>
			<td bgcolor=#00FFFF></td>
			<td bgcolor=#00FFFF></td>
			<td bgcolor=#00FFFF></td>
			<td bgcolor=#00FFFF></td>
			<td bgcolor=#00FFFF></td>
		</tr>";	
	
	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td bgcolor=#0099FF colspan=3>".$_SESSION['lang']['total']." ".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['proses']."</td>
			<td bgcolor=#0099FF align=right></td>
			<td bgcolor=#0099FF align=right></td>
			<td bgcolor=#0099FF align=right></td>
			<td bgcolor=#0099FF></td>
			<td bgcolor=#0099FF></td>
			<td bgcolor=#0099FF></td>
			
			<td bgcolor=#0099FF align=right></td>
			<td bgcolor=#0099FF align=right></td>
			<td bgcolor=#0099FF align=right></td>
			<td bgcolor=#0099FF></td>
			<td bgcolor=#0099FF></td>
			<td bgcolor=#0099FF></td>
			<td bgcolor=#0099FF></td>
			<td bgcolor=#0099FF></td>
			<td bgcolor=#0099FF></td>
		</tr>";	
*/	
	
	
foreach($station as $subunit){
	@$nodet+=1;
		$stream.="
			<tr class=rowcontent>
				<td rowspan=7 valign=top align=center>".$nodet."</td>
				<td colspan=3><b><u>".$nmstation[$subunit]."</u></b></td>
				<td colspan=15></td>
			</tr>
		";
		/*
		$bgtstakunbi[$bar['kodeblok']][$nourutlaporan]+=$bar['bi'];
		$bgtstakunsdbi[$bar['kodeblok']][$nourutlaporan]+=$bar['sdbi'];
		$bgtstakunthn[$bar['kodeblok']][$nourutlaporan]+=$bar['thn'];
		*/
		
	foreach($mlnurut as $nourutlaporan){
		$stream.="
			<tr class=rowcontent>
				<td colspan=3>".$mlnama[$nourutlaporan]."</td>
				<td align=right>".@number_format($isistakunbi[$subunit][$nourutlaporan])."</td>
				<td align=right>".@number_format($isistakunbi[$subunit][$nourutlaporan]/$palmbi,2)."</td>
				<td align=right>".@number_format($isistakunbi[$subunit][$nourutlaporan]/$tbsbi,2)."</td>
					<td align=right>".@number_format($bgtstakunbi[$subunit][$nourutlaporan])."</td>
					<td align=right>".@number_format($bgtstakunbi[$subunit][$nourutlaporan]/$bgtpalmbi,2)."</td>
					<td align=right>".@number_format($bgtstakunbi[$subunit][$nourutlaporan]/$bgttbsbi,2)."</td>
				<td align=right>".@number_format($isistakunsdbi[$subunit][$nourutlaporan])."</td>
				<td align=right>".@number_format($isistakunsdbi[$subunit][$nourutlaporan]/$palmsdbi,2)."</td>
				<td align=right>".@number_format($isistakunsdbi[$subunit][$nourutlaporan]/$tbssdbi,2)."</td>
					<td align=right>".@number_format($bgtstakunsdbi[$subunit][$nourutlaporan])."</td>
					<td align=right>".@number_format($bgtstakunsdbi[$subunit][$nourutlaporan]/$bgtpalmsdbi,2)."</td>
					<td align=right>".@number_format($bgtstakunsdbi[$subunit][$nourutlaporan]/$bgttbssdbi,2)."</td>
				<td align=right>".@number_format($bgtstakunthn[$subunit][$nourutlaporan])."</td>
				<td align=right>".@number_format($bgtstakunthn[$subunit][$nourutlaporan]/$bgtpalmthn,2)."</td>
				<td align=right>".@number_format($bgtstakunthn[$subunit][$nourutlaporan]/$bgttbsthn,2)."</td>
			</tr>";
		@$subtotisistakunbi[$subunit]+=$isistakunbi[$subunit][$nourutlaporan];
		@$subtotisistakunsdbi[$subunit]+=$isistakunsdbi[$subunit][$nourutlaporan];
			@$subtotbgtstakunbi[$subunit]+=$bgtstakunbi[$subunit][$nourutlaporan];
			@$subtotbgtstakunsdbi[$subunit]+=$bgtstakunsdbi[$subunit][$nourutlaporan];
			@$subtotbgtstakunthn[$subunit]+=$bgtstakunthn[$subunit][$nourutlaporan];
		
	}
	$stream.="<tr class=rowcontent>
			<td bgcolor=#CCCCCC colspan=3 align=center>".$_SESSION['lang']['subtotal']."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotisistakunbi[$subunit])."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotisistakunbi[$subunit]/$palmbi,2)."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotisistakunbi[$subunit]/$tbsbi,2)."</td>
				<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunbi[$subunit])."</td>
				<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunbi[$subunit]/$bgtpalmbi,2)."</td>
				<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunbi[$subunit]/$bgttbsbi,2)."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotisistakunsdbi[$subunit])."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotisistakunsdbi[$subunit]/$palmsdbi,2)."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotisistakunsdbi[$subunit]/$tbssdbi,2)."</td>
				<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunsdbi[$subunit])."</td>
				<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunsdbi[$subunit]/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunsdbi[$subunit]/$bgttbssdbi,2)."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunthn[$subunit])."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunthn[$subunit]/$bgtpalmthn,2)."</td>
			<td bgcolor=#CCCCCC align=right>".@number_format($subtotbgtstakunthn[$subunit]/$bgttbsthn,2)."</td>
		</tr>";		
		@$gtisistakunbi+=$subtotisistakunbi[$subunit];
		@$gtisistakunsdbi+=$subtotisistakunsdbi[$subunit];
			@$gtbgtstakunbi+=$subtotbgtstakunbi[$subunit];
			@$gtbgtstakunsdbi+=$subtotbgtstakunsdbi[$subunit];
			@$gtbgtstakunthn+=$subtotbgtstakunthn[$subunit];
}	


	
	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td bgcolor=#00CCFF colspan=3>".$_SESSION['lang']['total']." ".$_SESSION['lang']['biayalangsung']."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($gtisistakunbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($gtisistakunbi/$palmbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($gtisistakunbi/$tbsbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunbi)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunbi/$bgtpalmbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunbi/$bgttbsbi,2)."</td>
			
			<td bgcolor=#00CCFF align=right>".@number_format($gtisistakunsdbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($gtisistakunsdbi/$palmsdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($gtisistakunsdbi/$tbssdbibi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunsdbi)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunsdbi/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunsdbi/$bgttbssdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunthn)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunthn/$bgtpalmthn,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($gtbgtstakunthn/$bgttbsthn,2)."</td>
		</tr>";	

	
$totisidataurutbi=$totisidataurutsdbi=$totbgturutbi=$totbgturutsdbi=$totbgturutthn=0;	
foreach($mlnurut as $nourutlaporan){

	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td colspan=3>".$mlnama[$nourutlaporan]."</td>
			<td align=right>".@number_format($isidataurutbi[$nourutlaporan])."</td>
			<td align=right>".@number_format($isidataurutbi[$nourutlaporan]/$palmbi)."</td>
			<td align=right>".@number_format($isidataurutbi[$nourutlaporan]/$tbsbi)."</td>
				<td  align=right>".@number_format($bgturutbi[$nourutlaporan])."</td>
				<td  align=right>".@number_format($bgturutbi[$nourutlaporan]/$bgtpalmbi,2)."</td>
				<td  align=right>".@number_format($bgturutbi[$nourutlaporan]/$bgttbsbi,2)."</td>			
			<td align=right>".@number_format($isidataurutsdbi[$nourutlaporan])."</td>
			<td align=right>".@number_format($isidataurutsdbi[$nourutlaporan]/$palmsdbi)."</td>
			<td align=right>".@number_format($isidataurutsdbi[$nourutlaporan]/$tbssdbibi)."</td>
				<td  align=right>".@number_format($bgturutsdbi[$nourutlaporan])."</td>
				<td  align=right>".@number_format($bgturutsdbi[$nourutlaporan]/$bgtpalmsdbi,2)."</td>
				<td  align=right>".@number_format($bgturutsdbi[$nourutlaporan]/$bgttbssdbi,2)."</td>			
			<td  align=right>".@number_format($bgturutthn[$nourutlaporan])."</td>
			<td  align=right>".@number_format($bgturutthn[$nourutlaporan]/$bgtpalmthn,2)."</td>
			<td  align=right>".@number_format($bgturutthn[$nourutlaporan]/$bgttbsthn,2)."</td>	
		</tr>";		
		
			@$totisidataurutbi+=$isidataurutbi[$nourutlaporan];
			@$totisidataurutsdbi+=$isidataurutsdbi[$nourutlaporan];
				@$totbgturutbi+=$bgturutbi[$nourutlaporan];
				@$totbgturutsdbi+=$bgturutsdbi[$nourutlaporan];
				@$totbgturutthn+=$bgturutthn[$nourutlaporan];
}	
	


	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td bgcolor=#00CCFF colspan=3>".$_SESSION['lang']['total']." ".$_SESSION['lang']['biayalangsung']."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi/$palmbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi/$tbsbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi/$bgtpalmbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi/$bgttbsbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi/$palmsdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi/$tbssdbibi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi/$bgttbssdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn/$bgtpalmthn,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn/$bgttbsthn,2)."</td>
		</tr>";	
	



	

$noakunlima=array();
$noakun=array();
$str="select substr(noakun,1,5) as noakunlima,noakun,jumlah,tanggal from ".$dbname.".keu_jurnaldt_vw where kodeorg='".$kdorg."' and 
			tanggal between '".$tglawal."' and '".$tglakhir."' and nojurnal not like '%PRSDN%'  
			and (noakun like '7%' and (noakun!='7199999' and left(noakun,5)!='71502')) order by noakun asc ";
	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$noakunlima[$bar['noakunlima']]=$bar['noakunlima'];
	$noakun[$bar['noakun']]=$bar['noakun'];
	if(substr($bar['tanggal'],0,7)==$per){
		@$btllimabi[$bar['noakunlima']]+=$bar['jumlah'];
		@$btltujuhbi[$bar['noakun']]+=$bar['jumlah'];
		@$transfoutbi+=$bar['jumlah'];
	}
	@$btllimasdbi[$bar['noakunlima']]+=$bar['jumlah'];
	@$btltujuhsdbi[$bar['noakun']]+=$bar['jumlah'];
	@$transfoutsdbi+=$bar['jumlah'];
}	

##bgt
$str="select substr(kodeorg,1,6) as kodeblok,rp".$blnbgt." as bi,".$addstrbgt." as sdbi,rupiah as thn,
		substr(noakun,1,5) as noakunlima,noakun
		from ".$dbname.".bgt_budget_detail where tahunbudget = '".substr($per,0,4)."' and kodeorg like '".$kdorg."%' 
		and (noakun like '7%' and (noakun!='7199999' and left(noakun,5)!='71502')) order by noakun asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$noakunlima[$bar['noakunlima']]=$bar['noakunlima'];
	$noakun[$bar['noakun']]=$bar['noakun'];
	
	//5
	$bgtbtllimabi[$bar['noakunlima']]+=$bar['bi'];
	$bgtbtllimasdbi[$bar['noakunlima']]+=$bar['sdbi'];
	$bgtbtllimathn[$bar['noakunlima']]+=$bar['thn'];
	
	//7
	$bgtbtltujuhbi[$bar['noakun']]+=$bar['bi'];
	$bgtbtltujuhsdbi[$bar['noakun']]+=$bar['sdbi'];
	$bgtbtltujuhthn[$bar['noakun']]+=$bar['thn'];
	
	$bgttransfoutbi+=$bar['bi'];
	$bgttransfoutsdbi+=$bar['sdbi'];
	$bgttransfoutthn+=$bar['thn'];
}



// $str="select noakun,jumlah from ".$dbname.".keu_jurnaldt_vw where kodeorg='".$kdorg."' and 
			// tanggal between '".$tglawal."' and '".$tglakhir."' and nojurnal not like '%PRSDN%'  
			// and (noakun like '7%' and (noakun!='7199999' or left(noakun,5)!='71502')) ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
	// if(substr($bar['tanggal'],0,7)==$per){	
		// @$transfoutbi+=$bar['jumlah'];
	// }
	// @$transfoutsdbi+=$bar['jumlah'];
// }



// echo"<pre>";
// print_r($btllimabi);
// echo"</pre>";
	
	
foreach($noakunlima as $akunlima){
	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td colspan=3>".$akunlima." - ".$nmakun[$akunlima]."</td>
			<td align=right>".@number_format($btllimabi[$akunlima])."</td>
			<td align=right>".@number_format($btllimabi[$akunlima]/$palmbi,2)."</td>
			<td align=right>".@number_format($btllimabi[$akunlima]/$tbsbi,2)."</td>
				<td align=right>".@number_format($bgtbtllimabi[$akunlima])."</td>
				<td align=right>".@number_format($bgtbtllimabi[$akunlima]/$bgtpalmbi,2)."</td>
				<td align=right>".@number_format($bgtbtllimabi[$akunlima]/$bgttbsbi,2)."</td>
			<td align=right>".@number_format($btllimasdbi[$akunlima])."</td>
			<td align=right>".@number_format($btllimasdbi[$akunlima]/$palmsdbi,2)."</td>
			<td align=right>".@number_format($btllimasdbi[$akunlima]/$tbssdbibi,2)."</td>
				<td align=right>".@number_format($bgtbtllimasdbi[$akunlima])."</td>
				<td align=right>".@number_format($bgtbtllimasdbi[$akunlima]/$bgtpalmsdbi,2)."</td>
				<td align=right>".@number_format($bgtbtllimasdbi[$akunlima]/$bgttbssdbi,2)."</td>
			<td align=right>".@number_format($bgtbtllimathn[$akunlima])."</td>
			<td align=right>".@number_format($bgtbtllimathn[$akunlima]/$bgtpalmthn,2)."</td>
			<td align=right>".@number_format($bgtbtllimathn[$akunlima]/$bgttbsthn,2)."</td>
		</tr>";
	@$totbtllimabi+=$btllimabi[$akunlima];
	@$totbtllimasdbi+=$btllimasdbi[$akunlima];
		@$totbgtbtllimabi+=$bgtbtllimabi[$akunlima];
		@$totbgtbtllimasdbi+=$bgtbtllimasdbi[$akunlima];
		@$totbgtbtllimathn+=$bgtbtllimathn[$akunlima];
	
}	



	$stream.="
		<tr class=rowcontent>
			<td bgcolor=#00FFFF colspan=4>".$_SESSION['lang']['total']."  ".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['tidaklangsung']." (A)xxx</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtllimabi)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtllimabi/$palmbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtllimabi/$tbsbi,2)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimabi)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimabi/$bgtpalmbi,2)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimabi/$bgttbsbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtllimasdbi)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtllimasdbi/$palmsdbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtllimasdbi/$tbssdbibi,2)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimasdbi)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimasdbi/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimasdbi/$bgttbssdbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimathn)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimathn/$bgtpalmthn,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimathn/$bgttbsthn,2)."</td>
		</tr>";	
	
	
$stream.="
		<tr class=rowcontent>
			<td colspan=4>Transfer Out GC</td>
			<td align=right>".@number_format($transfoutbi)."</td>
			<td align=right>".@number_format($transfoutbi/$palmbi,2)."</td>
			<td align=right>".@number_format($transfoutbi/$tbsbi,2)."</td>
				<td align=right>".@number_format($bgttransfoutbi)."</td>
				<td align=right>".@number_format($bgttransfoutbi/$bgtpalmbi,2)."</td>
				<td align=right>".@number_format($bgttransfoutbi/$bgttbsbi,2)."</td>
			<td align=right>".@number_format($transfoutsdbi)."</td>
			<td align=right>".@number_format($transfoutsdbi/$palmsdbi,2)."</td>
			<td align=right>".@number_format($transfoutsdbi/$tbssdbibi,2)."</td>
				<td align=right>".@number_format($bgttransfoutsdbi)."</td>
				<td align=right>".@number_format($bgttransfoutsdbi/$bgtpalmsdbi,2)."</td>
				<td align=right>".@number_format($bgttransfoutsdbi/$bgttbssdbi,2)."</td>
			<td align=right>".@number_format($bgttransfoutthn)."</td>
			<td align=right>".@number_format($bgttransfoutthn/$bgtpalmthn,2)."</td>
			<td align=right>".@number_format($bgttransfoutthn/$bgttbsthn,2)."</td>
		</tr>";

$stream.="
		<tr class=rowcontent>
			<td colspan=4 bgcolor=#CCCCCC>Total Transfer Out GC (B)</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutbi)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutbi/$palmbi,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutbi/$tbsbi,2)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutbi)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutbi/$bgtpalmbi,2)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutbi/$bgttbsbi,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutsdbi)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutsdbi/$palmsdbi,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutsdbi/$tbssdbibi,2)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutsdbi)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutsdbi/$bgtpalmsdbi,2)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutsdbi/$bgttbssdbi,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutthn)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutthn/$bgtpalmthn,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutthn/$bgttbsthn,2)."</td>
		</tr>";

	
		
		
$stream.="
	<tr class=rowcontent>
		<td bgcolor=#00FFFF colspan=4>Total Transfer Keseluruhan (A)+(B)</td>
		<td bgcolor=#00FFFF align=right>".@number_format($totbtllimabi+$transfoutbi)."</td>
		<td bgcolor=#00FFFF align=right>".@number_format(($totbtllimabi+$transfoutbi)/$palmbi,2)."</td>
		<td bgcolor=#00FFFF align=right>".@number_format(($totbtllimabi+$transfoutbi)/$tbsbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimabi+$bgttransfoutbi)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format(($totbgtbtllimabi+$bgttransfoutbi)/$bgtpalmbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format(($totbgtbtllimabi+$bgttransfoutbi)/$bgttbsbi,2)."</td>
		<td bgcolor=#00FFFF align=right>".@number_format($totbtllimasdbi+$transfoutsdbi)."</td>
		<td bgcolor=#00FFFF align=right>".@number_format(($totbtllimasdbi+$transfoutsdbi)/$palmsdbi,2)."</td>
		<td bgcolor=#00FFFF align=right>".@number_format(($totbtllimasdbi+$transfoutsdbi)/$tbssdbibi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimasdbi+$bgttransfoutsdbi)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format(($totbgtbtllimasdbi+$bgttransfoutsdbi)/$bgtpalmsdbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format(($totbgtbtllimasdbi+$bgttransfoutsdbi)/$bgttbssdbi,2)."</td>
		<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtllimathn+$bgttransfoutthn)."</td>
		<td bgcolor=#00FFFF align=right>".@number_format(($totbgtbtllimathn+$bgttransfoutthn)/$bgtpalmthn,2)."</td>
		<td bgcolor=#00FFFF align=right>".@number_format(($totbgtbtllimathn+$bgttransfoutthn)/$bgttbsthn,2)."</td>
	</tr>";

		
foreach($noakun as $akun){
	$stream.="
		<tr class=rowcontent>
			<td></td>
			<td colspan=3>".$akun." - ".$nmakun[$akun]."</td>
			<td align=right>".@number_format($btltujuhbi[$akun])."</td>
			<td align=right>".@number_format($btltujuhbi[$akun]/$palmbi,2)."</td>
			<td align=right>".@number_format($btltujuhbi[$akun]/$tbsbi,2)."</td>
				<td align=right>".@number_format($bgtbtltujuhbi[$akun])."</td>
				<td align=right>".@number_format($bgtbtltujuhbi[$akun]/$bgtpalmbi,2)."</td>
				<td align=right>".@number_format($bgtbtltujuhbi[$akun]/$bgttbsbi,2)."</td>
			<td align=right>".@number_format($btltujuhsdbi[$akun])."</td>
			<td align=right>".@number_format($btltujuhsdbi[$akun]/$palmsdbi,2)."</td>
			<td align=right>".@number_format($btltujuhsdbi[$akun]/$tbssdbibi,2)."</td>
				<td align=right>".@number_format($bgtbtltujuhsdbi[$akun])."</td>
				<td align=right>".@number_format($bgtbtltujuhsdbi[$akun]/$bgtpalmsdbi,2)."</td>
				<td align=right>".@number_format($bgtbtltujuhsdbi[$akun]/$bgttbssdbi,2)."</td>
			<td align=right>".@number_format($bgtbtltujuhthn[$akun])."</td>
			<td align=right>".@number_format($bgtbtltujuhthn[$akun]/$bgtpalmthn,2)."</td>
			<td align=right>".@number_format($bgtbtltujuhthn[$akun]/$bgttbsthn,2)."</td>
		</tr>";
	@$totbtltujuhbi+=$btltujuhbi[$akun];
	@$totbtltujuhsdbi+=$btltujuhsdbi[$akun];
		@$totbgtbtltujuhbi+=$bgtbtltujuhbi[$akun];
		@$totbgtbtltujuhsdbi+=$bgtbtltujuhsdbi[$akun];
		@$totbgtbtltujuhthn+=$bgtbtltujuhthn[$akun];
		
}	
		
		
$stream.="
		<tr class=rowcontent>
			<td bgcolor=#00FFFF colspan=4>".$_SESSION['lang']['total']."  ".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['tidaklangsung']." (A)</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtltujuhbi)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtltujuhbi/$palmbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtltujuhbi/$tbsbi,2)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhbi)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhbi/$bgtpalmbi,2)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhbi/$bgttbsbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtltujuhsdbi)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtltujuhsdbi/$palmsdbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbtltujuhsdbi/$tbssdbibi,2)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhsdbi)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhsdbi/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhsdbi/$bgttbssdbi,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhthn)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhthn/$bgtpalmthn,2)."</td>
			<td bgcolor=#00FFFF align=right>".@number_format($totbgtbtltujuhthn/$bgttbsthn,2)."</td>
		</tr>";
		


$stream.="
		<tr class=rowcontent>
			<td>7199999</td>
			<td colspan=3>Total Transfer Out GC</td>
			<td align=right>".@number_format($transfoutbi)."</td>
			<td align=right>".@number_format($transfoutbi/$palmbi,2)."</td>
			<td align=right>".@number_format($transfoutbi/$tbsbi,2)."</td>
				<td align=right>".@number_format($bgttransfoutbi)."</td>
				<td align=right>".@number_format($bgttransfoutbi/$bgtpalmbi,2)."</td>
				<td align=right>".@number_format($bgttransfoutbi/$bgttbsbi,2)."</td>
			<td align=right>".@number_format($transfoutsdbi)."</td>
			<td align=right>".@number_format($transfoutsdbi/$palmsdbi,2)."</td>
			<td align=right>".@number_format($transfoutsdbi/$tbssdbibi,2)."</td>
				<td align=right>".@number_format($bgttransfoutsdbi)."</td>
				<td align=right>".@number_format($bgttransfoutsdbi/$bgtpalmsdbi,2)."</td>
				<td align=right>".@number_format($bgttransfoutsdbi/$bgttbssdbi,2)."</td>
			<td align=right>".@number_format($bgttransfoutthn)."</td>
			<td align=right>".@number_format($bgttransfoutthn/$bgtpalmthn,2)."</td>
			<td align=right>".@number_format($bgttransfoutthn/$bgttbsthn,2)."</td>
		</tr>";
		

		
		

		
$stream.="
		<tr class=rowcontent>
			<td colspan=4 bgcolor=#CCCCCC>Total Transfer Out GC (B)</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutbi)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutbi/$palmbi,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutbi/$tbsbi,2)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutbi)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutbi/$bgtpalmbi,2)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutbi/$bgttbsbi,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutsdbi)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutsdbi/$palmsdbi,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($transfoutsdbi/$tbssdbibi,2)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutsdbi)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutsdbi/$bgtpalmsdbi,2)."</td>
				<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutsdbi/$bgttbssdbi,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutthn)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutthn/$bgtpalmthn,2)."</td>
			<td align=right bgcolor=#CCCCCC >".@number_format($bgttransfoutthn/$bgttbsthn,2)."</td>
		</tr>";		

	
		
$stream.="
		<tr class=rowcontent>
			<td bgcolor=#0099FF colspan=4>Total Transfer Keseluruhan (A)+(B)</td>
			<td bgcolor=#0099FF align=right>".@number_format($totbtltujuhbi+$transfoutbi)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbtltujuhbi+$transfoutbi)/$palmbi,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbtltujuhbi+$transfoutbi)/$tbsbi,2)."</td>
				<td bgcolor=#0099FF align=right>".@number_format($totbgtbtltujuhbi+$bgttransfoutbi)."</td>
				<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhbi+$bgttransfoutbi)/$bgtpalmbi,2)."</td>
				<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhbi+$bgttransfoutbi)/$bgttbsbi,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format($totbtltujuhsdbi+$transfoutsdbi)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbtltujuhsdbi+$transfoutsdbi)/$palmsdbi,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbtltujuhsdbi+$transfoutsdbi)/$tbssdbibi,2)."</td>
				<td bgcolor=#0099FF align=right>".@number_format($totbgtbtltujuhsdbi+$bgttransfoutsdbi)."</td>
				<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhsdbi+$bgttransfoutsdbi)/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhsdbi+$bgttransfoutsdbi)/$bgttbssdbi,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format($totbgtbtltujuhthn+$bgttransfoutthn)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhthn+$bgttransfoutthn)/$bgtpalmthn,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhthn+$bgttransfoutthn)/$bgttbsthn,2)."</td>
		</tr>";	


		/*
		
			$stream.="
		<tr class=rowcontent>
			<td></td>
			<td bgcolor=#00CCFF colspan=3>".$_SESSION['lang']['total']." ".$_SESSION['lang']['biayalangsung']."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi/$palmbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutbi/$tbsbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi/$bgtpalmbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutbi/$bgttbsbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi/$palmsdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totisidataurutsdbi/$tbssdbibi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#00CCFF align=right>".@number_format($totbgturutsdbi/$bgttbssdbi,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn/$bgtpalmthn,2)."</td>
			<td bgcolor=#00CCFF align=right>".@number_format($totbgturutthn/$bgttbsthn,2)."</td>
		</tr>";	
		*/

###gran total
$stream.="
		<tr class=rowcontent><thead>
			<td bgcolor=#0099FF colspan=4>Total Transfer Keseluruhan (A)+(B)</td>
			<td bgcolor=#0099FF align=right>".@number_format($totbtltujuhbi+$transfoutbi+$totisidataurutbi)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbtltujuhbi+$transfoutbi+$totisidataurutbi)/$palmbi,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbtltujuhbi+$transfoutbi+$totisidataurutbi)/$tbsbi,2)."</td>
				<td bgcolor=#0099FF align=right>".@number_format($totbgtbtltujuhbi+$bgttransfoutbi+$totbgturutbi)."</td>
				<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhbi+$bgttransfoutbi+$totbgturutbi)/$bgtpalmbi,2)."</td>
				<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhbi+$bgttransfoutbi+$totbgturutbi)/$bgttbsbi,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format($totbtltujuhsdbi+$transfoutbi+$totisidataurutsdbi)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbtltujuhsdbi+$transfoutbi+$totisidataurutsdbi)/$palmsdbi,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbtltujuhsdbi+$transfoutbi+$totisidataurutsdbi)/$tbssdbibi,2)."</td>
				<td bgcolor=#0099FF align=right>".@number_format($totbgtbtltujuhsdbi+$bgttransfoutsdbi+$totbgturutsdbi)."</td>
				<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhsdbi+$bgttransfoutsdbi+$totbgturutsdbi)/$bgtpalmsdbi,2)."</td>
				<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhsdbi+$bgttransfoutsdbi+$totbgturutsdbi)/$bgttbssdbi,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format($totbgtbtltujuhthn+$bgttransfoutthn+$totbgturutthn)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhthn+$bgttransfoutthn+$totbgturutthn)/$bgtpalmthn,2)."</td>
			<td bgcolor=#0099FF align=right>".@number_format(($totbgtbtltujuhthn+$bgttransfoutthn+$totbgturutthn)/$bgttbsthn,2)."</td>
		</tr>";	

			
$stream.="</table>";


switch($proses)
{
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="laporan_biaya_pabrik ".$kdorg."_".$per1."_sd_".$per2;
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }     
        break;	
}
?>