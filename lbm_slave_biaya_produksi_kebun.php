<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
include_once('lib/zFunction.php');

$path=$_SERVER['REQUEST_URI'];
$path=explode('/',$path);
$rowfile=count($path);
$file=$path[($rowfile-1)];
$file=explode('?',$file);
$file=$file[0];

$postJabatan = getPostingJabatan('lbm');	

$proses = checkPostGet('proses','');
$unit = checkPostGet('unit','');
$periode = checkPostGet('periode','');
$judul = checkPostGet('judul','');
$afdId = checkPostGet('afdId','');


$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and tutupbuku=1 order by periode desc limit 1 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	$peraktif=$bar['periode'];

$qwe=explode('-',$periode); $tahun=$qwe[0]; $bulan=$qwe[1];

$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

if($unit==''||$periode=='')
{
    exit("Error:Field required");
}

$optBulan['01']=$_SESSION['lang']['jan'];
$optBulan['02']=$_SESSION['lang']['peb'];
$optBulan['03']=$_SESSION['lang']['mar'];
$optBulan['04']=$_SESSION['lang']['apr'];
$optBulan['05']=$_SESSION['lang']['mei'];
$optBulan['06']=$_SESSION['lang']['jun'];
$optBulan['07']=$_SESSION['lang']['jul'];
$optBulan['08']=$_SESSION['lang']['agt'];
$optBulan['09']=$_SESSION['lang']['sep'];
$optBulan['10']=$_SESSION['lang']['okt'];
$optBulan['11']=$_SESSION['lang']['nov'];
$optBulan['12']=$_SESSION['lang']['dec'];

// building array: dzArr (main data) =========================================================================
// as seen on sdm_slave_2prasarana.php
$dzArr=array();

// kg budget setahun
$aresta="SELECT sum(kgsetahun) as setahun FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeunit like '".$unit."%' and tahunbudget ='".$tahun."'";
if($afdId!='')
{
   $aresta="SELECT sum(kgsetahun) as setahun FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeblok like '".$afdId."%' and tahunbudget ='".$tahun."'";  
}

$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){	
    $kgbgth=$res['setahun'];
}   

// kg budget bulan ini
$aresta="SELECT sum(kg".$bulan.") as bi FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeunit like '".$unit."%' and tahunbudget = '".$tahun."'";
if($afdId!='')
{
    $aresta="SELECT sum(kg".$bulan.") as bi FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeblok like '".$afdId."%' and tahunbudget = '".$tahun."'";
}

$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){	
    $kgbgbi=$res['bi'];
}   

$addstr="(";
for($W=1;$W<=intval($bulan);$W++)
{
    if($W<10)$jack="kg0".$W;
    else $jack="kg".$W;
    if($W<intval($bulan))$addstr.=$jack."+";
    else $addstr.=$jack;
}
$addstr.=")";

// kg budget sampai dengan bulan ini
$aresta="SELECT sum(".$addstr.") as sdbi FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeunit like '".$unit."%' and tahunbudget = '".$tahun."'";
if($afdId!='')
{
    $aresta="SELECT sum(".$addstr.") as sdbi FROM ".$dbname.".bgt_produksi_kbn_kg_vw
    WHERE kodeblok like '".$afdId."%' and tahunbudget = '".$tahun."'";
}

$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){	
    $kgbgsd=$res['sdbi'];
}   

// kg real bulan ini
$aresta="SELECT sum(beratbersih) as bi FROM ".$dbname.".pabrik_timbangan
    WHERE kodeorg like '".$unit."%' and tanggal like '".$periode."%'";
if($afdId!='')
{
    $aresta="SELECT sum(beratbersih) as bi FROM ".$dbname.".pabrik_timbangan
    WHERE nospb like '%".$afdId."%' and tanggal like '".$periode."%'";
}

$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){	
    $kgrebi=$res['bi'];
}   

// kg real sampain dengan bulan ini
$aresta="SELECT sum(beratbersih) as sdbi FROM ".$dbname.".pabrik_timbangan
    WHERE kodeorg like '".$unit."%' and (substr(tanggal,1,10) between '".$tahun."-01-01 00:00:00' and LAST_DAY('".$periode."-15'))";
if($afdId!='')
{
 $aresta="SELECT sum(beratbersih) as sdbi FROM ".$dbname.".pabrik_timbangan
    WHERE nospb like '%".$afdId."%' and (substr(tanggal,1,10) between '".$tahun."-01-01 00:00:00' and LAST_DAY('".$periode."-15'))";   
}

$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){	
    $kgresd=$res['sdbi'];
}   


$stylehidden = "style='display:none'";	
	
if($proses=='excel')
{
	$stylehidden = "";	

$bg=" bgcolor=#DEDEDE";
$brdr=1;
$tab.="<table border=0>
     <tr>
        <td colspan=8 align=left><font size=3>20. ".strtoupper($_SESSION['lang']['biaya'])." ".strtoupper($_SESSION['lang']['produksi'])."</font></td>
        <td colspan=6 align=right>".$_SESSION['lang']['bulan']." : ".$optBulan[$bulan]." ".$tahun."</td>
     </tr> 
     <tr><td colspan=14 align=left>".$_SESSION['lang']['unit']." : ".$optNm[$unit]." (".$unit.")</td></tr>  ";
if($afdId!='')
{
    $tab.="<tr><td colspan=14 align=left>".$_SESSION['lang']['afdeling']." : ".$optNm[$afdId]." (".$afdId.")</td></tr>  ";
}
$tab.="</table>";
}
else
{ 
    $bg="";
    $brdr=0;
}


if($proses!='excel')$tab.=$judul;
    $tab.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>
    <td align=right colspan=2 ".$bg.">".strtoupper($_SESSION['lang']['produksi'])." (kg):</td>
    <td align=right colspan=2 ".$bg.">".@number_format($kgbgth,2)."</td>
    <td align=right colspan=2 ".$bg.">".@number_format($kgbgbi,2)."</td>
    <td align=right colspan=2 ".$bg.">".@number_format($kgbgsd,2)."</td>
    <td align=right colspan=2 ".$bg.">".@number_format($kgrebi,2)."</td>
    <td align=right colspan=2 ".$bg.">".@number_format($kgresd,2)."</td>
    <td align=left colspan=2".$bg."></td>
	";
	if($proses!='excel')  
	{
		$tab.="<td align=center rowspan=4>View</td>";
	}
	if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan) && $proses!='excel'  && $periode>=$peraktif)  
	{
		$tab.="<td align=center rowspan=4>#</td>";
		#$tab.="<td align=center rowspan=4  colspan=2 style='width=250px'>".$_SESSION['lang']['penjelasan']." & Upload File Pendukung</td>";
	}
	$tab.="
    </tr>
    <tr>
    <td align=center rowspan=3 ".$bg.">No.</td>
    <td align=center rowspan=3 ".$bg.">".$_SESSION['lang']['pekerjaan']."</td>
    <td align=center colspan=6 ".$bg.">".$_SESSION['lang']['anggaran']."</td>
    <td align=center colspan=4 ".$bg.">".$_SESSION['lang']['realisasi']."</td>
    <td align=center rowspan=2 colspan=2 ".$bg.">% ".$_SESSION['lang']['pencapaian']."</td>
    </tr>
    <tr>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['setahun']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['bulanini']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['bulanini']."</td>
    <td align=center colspan=2 ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>
    </tr>
    <tr>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./kg</td>
    <td align=center ".$bg.">".$_SESSION['lang']['setahun']."</td>
    <td align=center ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>
    </tr>
    </thead>
    <tbody>
";
        


#bentuk akun 5
$str="SELECT noakun, namaakun,namaakun1 FROM ".$dbname.".keu_5akun
    WHERE (length(noakun)=5 and noakun like '611%') ORDER BY noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$akunlima[$bar['noakun']]=$bar['noakun'];
	$nmakun[$bar['noakun']]=$bar['namaakun'];
}

#bentuk akun 7
$str="SELECT noakun, namaakun,namaakun1 FROM ".$dbname.".keu_5akun
    WHERE (length(noakun)=7 and noakun like '611%') ORDER BY noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$akun[$bar['noakun']]=$bar['noakun'];	
	$nmakun[$bar['noakun']]=$bar['namaakun'];
}


$str="select * from ".$dbname.".setup_kegiatan WHERE (noakun like '611%') ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$kdkegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$listkdkegiatan[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	@$totkeg[substr($bar['noakun'],0,5)][$bar['noakun']]+=1;
	@$totakun[substr($bar['noakun'],0,5)]+=1;
	$nmkeg[$bar['kodekegiatan']]=$bar['namakegiatan'];
	$satkeg[$bar['kodekegiatan']]=$bar['satuan'];
}


if($afdId=='')
{
	$sortunit=$unit;
}
else 
{
	$sortunit=$afdId;
}



##sdbi
$addstr="(";
for($i=1;$i<=intval($bulan);$i++)
{
    if($i<10)
    {
        $isi="rp0".$i;
    }
    else 
    {
        $isi="rp".$i;
    }
    if($i<intval($bulan))
    {
        $addstr.=$isi."+";
    }
    else
    {
        $addstr.=$isi;
    }
}
$addstr.=")";


$addstrvol="(";
for($i=1;$i<=intval($bulan);$i++)
{
    if($i<10)
    {
        $isi="fis0".$i;
    }
    else 
    {
        $isi="fis".$i;
    }
    if($i<intval($bulan))
    {
        $addstrvol.=$isi."+";
    }
    else
    {
        $addstrvol.=$isi;
    }
}
$addstrvol.=")";

#rwt
$str=" select kegiatan,noakun,".$addstr." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg
		from ".$dbname.".bgt_budget_detail where kodeorg like '".$sortunit."%' and tahunbudget = '".$tahun."' 
		and (noakun like '611%')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$bgtthnlima[substr($bar['noakun'],0,5)]+=$bar['rupiah']; 
	@$bgtbilima[substr($bar['noakun'],0,5)]+=$bar['bi']; 
	@$bgtsdbilima[substr($bar['noakun'],0,5)]+=$bar['sdbi']; 
	
	@$bgtthntjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['rupiah']; 
	@$bgtbitjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['bi']; 
	@$bgtsdbitjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['sdbi']; 
	
	@$bgtthnkeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kodekegiatan']]+=$bar['rupiah']; 
	@$bgtbikeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kodekegiatan']]+=$bar['bi']; 
	@$bgtsdbikeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kodekegiatan']]+=$bar['sdbi'];	
	
}


#real bi
$str="SELECT * FROM ".$dbname.".keu_jurnaldt  WHERE tanggal like '".$periode."%' and kodeorg='".$unit."' 
	and (noakun like '611%') and kodeblok like '".$sortunit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$realbilima[substr($bar['noakun'],0,5)]+=$bar['jumlah']; 
	@$realbitjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['jumlah']; 
	@$realbikeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kodekegiatan']]+=$bar['jumlah']; 
}



#real sdbi
$str="SELECT * FROM ".$dbname.".keu_jurnaldt  WHERE (substr(tanggal,1,10) between '".$tahun."-01-01' and LAST_DAY('".$periode."-15'))
	and kodeorg='".$unit."' and (noakun like '611%') and kodeblok like '".$sortunit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$realsdbilima[substr($bar['noakun'],0,5)]+=$bar['jumlah']; 
	@$realsdbitjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['jumlah']; 
	@$realsdbikeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kodekegiatan']]+=$bar['jumlah']; 
}




##ini untuk value awal komen nanti
$str=" select * from ".$dbname.".lbm_comment where file='".$file."' and unit='".$unit."' and periode='".$periode."'  and divisi='".$afdId."'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$textinput[substr($bar['parameter'],0,5)][$bar['parameter']]=$bar['text'];
}



$nox='';
foreach($akunlima as $noakunlima){
	$nox++;
		$tab.= "<tr class=rowcontent style='cursor:pointer' title='click to show details' onclick=\"detailrowlima('".$nox."','".@$totakun[$noakunlima]."')\">";
		$tab.= "<td colspan=2>".$noakunlima." - ".$nmakun[$noakunlima]."</td>";
		
		$tab.="<td align=right>".@number_format($bgtthnlima[$noakunlima]/1000)."</td>";
			$tab.="<td align=right>".@number_format($bgtthnlima[$noakunlima]/$kgbgth,2)."</td>";
		$tab.="<td align=right>".@number_format($bgtbilima[$noakunlima]/1000)."</td>";
			$tab.="<td align=right>".@number_format($bgtbilima[$noakunlima]/$kgbgbi,2)."</td>";
		$tab.="<td align=right>".@number_format($bgtsdbilima[$noakunlima]/1000)."</td>";
			$tab.="<td align=right>".@number_format($bgtsdbilima[$noakunlima]/$kgbgsd,2)."</td>";
			
		$tab.="<td align=right>".@number_format($realbilima[$noakunlima]/1000)."</td>";
			$tab.="<td align=right>".@number_format($realbilima[$noakunlima]/$kgrebi,2)."</td>";
		
		$tab.="<td align=right>".@number_format($realsdbilima[$noakunlima]/1000)."</td>";
			$tab.="<td align=right>".@number_format($realsdbilima[$noakunlima]/$kgresd,2)."</td>";	
		
		$tab.="<td align=right>".@number_format($realsdbilima[$noakunlima]/$bgtthnlima[$noakunlima]*100)."</td>";
			$tab.="<td align=right>".@number_format($realsdbilima[$noakunlima]/$bgtsdbilima[$noakunlima]*100)."</td>";	
		if($proses!='excel')  
		{
			$tab.="<td colspan=3></td>";
		}
		$tab.="</tr>";	
		
	foreach($akun as $noakun){
		if($noakunlima==substr($noakun,0,5)){	
			$no+=1;
			$tab.="<tr class=rowcontent ".$stylehidden." id=listakunlina".$nox."".$no.">";
				$tab.="<td align=center style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".$totkeg[$noakunlima][$noakun]."')\">".$no."</td>";
				$tab.= "<td style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".$totkeg[$noakunlima][$noakun]."')\">".$noakun." - ".$nmakun[$noakun]."</td>";
				$tab.="<td align=right>".@number_format($bgtthntjh[$noakunlima][$noakun]/1000)."</td>";
					$tab.="<td align=right>".@number_format($bgtthntjh[$noakunlima][$noakun]/$kgbgth,2)."</td>";
				$tab.="<td align=right>".@number_format($bgtbitjh[$noakunlima][$noakun]/1000)."</td>";
					$tab.="<td align=right>".@number_format($bgtbitjh[$noakunlima][$noakun]/$kgbgbi,2)."</td>";
				$tab.="<td align=right>".@number_format($bgtsdbitjh[$noakunlima][$noakun]/1000)."</td>";
					$tab.="<td align=right>".@number_format($bgtsdbitjh[$noakunlima][$noakun]/$kgbgsd,2)."</td>";
				
				$tab.="<td align=right>".@number_format($realbitjh[$noakunlima][$noakun]/1000)."</td>";
					$tab.="<td align=right>".@number_format($realbitjh[$noakunlima][$noakun]/$kgrebi,2)."</td>";
					
				$tab.="<td align=right>".@number_format($realsdbitjh[$noakunlima][$noakun]/1000)."</td>";
					$tab.="<td align=right>".@number_format($realsdbitjh[$noakunlima][$noakun]/$kgresd,2)."</td>";	
					
				$tab.="<td align=right>".@number_format($realsdbitjh[$noakunlima][$noakun]/$bgtthntjh[$noakunlima][$noakun]*100)."</td>";
					$tab.="<td align=right>".@number_format($realsdbitjh[$noakunlima][$noakun]/$bgtsdbitjh[$noakunlima][$noakun]*100)."</td>";	

				
				if($proses!='excel')  {
					$tab.= "<td align=center  style=cursor:pointer;><img onclick=\"detailcomment('".$file."','".$unit."','".$periode."','".$afdId."','".$noakun."','html','event')\"class=resicon src=images/skyblue/zoom.png style=position:relative;top:5px></td>";
				}
				if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan) && $proses!='excel'  && $periode>=$peraktif)  {
					$tab.= "<td valign=center><img onclick=\"showpopup('".$file."','".$unit."','".$periode."','".$afdId."','".$noakun."','".$no."','".@$textinput[$noakunlima][$noakun]."')\"  class=resicon src=images/upload-2-xxl.png style=position:relative;top:5px></td>";
					
					// $tab.= "<td style=width:220px >
						// <input type=text style=width:180px class=myinputtext name=komen value='".@$textinput[$noakunlima][$noakun]."' id=text".$no." onkeypress=\"return tanpa_kutip(event);\" >
						// <img onclick=\"savecomment('".$file."','".$unit."','".$periode."','".$afdId."','".$noakun."','".$no."')\"  class=resicon src=images/save.png style=position:relative;top:5px>
						// </td>";			
					
					// $tab.="<td style='width:220px;'><input name=fileupload type=file id=fileupload".$no." size=1 class=mybutton style=width:160px>
					// <img onclick=\"savefile('".$file."','".$unit."','".$periode."','".$afdId."','".$noakun."','".$no."')\"  class=resicon src=images/save.png style=position:relative;top:5px>
					// </td>";	
				}
			
					
				
			$tab.="</tr>";
		
			$urutkeg=0;
			foreach($kdkegiatan as $kegiatan){
				if(@$listkdkegiatan[$noakunlima][$noakun][$kegiatan]==$kegiatan){
					$urutkeg++;
					$tab.="<tr class=rowcontent ".$stylehidden." id=listkegiatan".$no."".$urutkeg.">";
						$tab.="<td></td>";
						$tab.="<td>".$no.".".$urutkeg."&nbsp;&nbsp;&nbsp;&nbsp;".$kegiatan." - ".$nmkeg[$kegiatan]."</td>";
						$tab.="<td align=right>".@number_format($bgtthnkeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
							$tab.="<td align=right>".@number_format($bgtthnkeg[$noakunlima][$noakun][$kegiatan]/$kgbgt,2)."</td>";
						$tab.="<td align=right>".@number_format($bgtbikeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
							$tab.="<td align=right>".@number_format($bgtbikeg[$noakunlima][$noakun][$kegiatan]/$kgbgbi,2)."</td>";
						$tab.="<td align=right>".@number_format($bgtsdbikeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
							$tab.="<td align=right>".@number_format($bgtsdbikeg[$noakunlima][$noakun][$kegiatan]/$kgbgsd,2)."</td>";
						
						$tab.="<td align=right>".@number_format($realbikeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
							$tab.="<td align=right>".@number_format($realbikeg[$noakunlima][$noakun][$kegiatan]/$kgrebi,2)."</td>";		

						$tab.="<td align=right>".@number_format($realsdbikeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
							$tab.="<td align=right>".@number_format($realsdbikeg[$noakunlima][$noakun][$kegiatan]/$kgresd,2)."</td>";		

						$tab.="<td align=right>".@number_format($realsdbikeg[$noakunlima][$noakun][$kegiatan]/$bgtthnkeg[$noakunlima][$noakun][$kegiatan]*100)."</td>";
							$tab.="<td align=right>".@number_format($realsdbikeg[$noakunlima][$noakun][$kegiatan]/$bgtsdbikeg[$noakunlima][$noakun][$kegiatan]*100)."</td>";			
						if($proses!='excel')  
						{
							$tab.="<td colspan=3></td>";
						}	
					$tab.="</tr>";
				}
			}
		}
	}
	@$tbgtthnlima+=$bgtthnlima[$noakunlima];
	@$tbgtbilima+=$bgtbilima[$noakunlima];
	@$tbgtsdbilima+=$bgtsdbilima[$noakunlima];
	@$trealbilima+=$realbilima[$noakunlima];
	@$trealsdbilima+=$realsdbilima[$noakunlima];
}
$tab.= "<tr class=rowcontent>";
	$tab.= "<td align=center colspan=2>Total</td>";
	$tab.="<td align=right>".@number_format($tbgtthnlima/1000)."</td>";
		$tab.="<td align=right>".@number_format($tbgtthnlima/$kgbgth,2)."</td>";
	$tab.="<td align=right>".@number_format($tbgtbilima/1000)."</td>";
		$tab.="<td align=right>".@number_format($tbgtbilima/$kgbgbi,2)."</td>";
	$tab.="<td align=right>".@number_format($tbgtsdbilima/1000)."</td>";
		$tab.="<td align=right>".@number_format($tbgtsdbilima/$kgbgsd,2)."</td>";
		
	$tab.="<td align=right>".@number_format($trealbilima/1000)."</td>";
		$tab.="<td align=right>".@number_format($trealbilima/$kgrebi,2)."</td>";
	
	$tab.="<td align=right>".@number_format($trealsdbilima/1000)."</td>";
		$tab.="<td align=right>".@number_format($trealsdbilima/$kgresd,2)."</td>";	
		
	$tab.="<td align=right>".@number_format($trealsdbilima/$tbgtthnlima*100)."</td>";
		$tab.="<td align=right>".@number_format($trealsdbilima/$tbgtsdbilima*100)."</td>";	
		
	if($proses!='excel')  
	{
		$tab.="<td colspan=3></td>";
	}			
$tab.="</tr>";

$tab.="</tbody></table>";

switch($proses)
{
    case'preview':
    if($unit==''||$periode=='')
    {
        exit("Error:Field required");
    }
    echo $tab;
    break;

    case'excel':
    if($unit==''||$periode=='')
    {
        exit("Error:Field required");
    }

    $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
    $dte=date("YmdHis");
    $nop_="lbm_biayaroduksi_".$unit.$periode;
    if(strlen($tab)>0)
    {
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                    @unlink('tempExcel/'.$file);
                }
            }	
           closedir($handle);
        }
        $handle=fopen("tempExcel/".$nop_.".xls",'w');
        if(!fwrite($handle,$tab))
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

    case'pdf':
    if($unit==''||$periode=='')
    {
        exit("Error:Field required");
    }

            $cols=247.5;
            $wkiri=24;
            $wlain=6;

    class PDF extends FPDF {
    function Header() {
        global $periode;
        global $unit;
        global $optNm;
        global $optBulan;
        global $tahun;
        global $bulan;
        global $dbname;
        global $luas;
        global $wkiri, $wlain,$afdId;
        global $kgbgth, $kgbgbi, $kgbgsd, $kgrebi, $kgresd;
            $width = $this->w - $this->lMargin - $this->rMargin;
  
        $height = 20;
        $this->SetFillColor(220,220,220);
        $this->SetFont('Arial','B',12);

        $this->Cell($width/2,$height,'20. '.strtoupper($_SESSION['lang']['biaya'].' '.$_SESSION['lang']['produksi']).' (RP./KG)',NULL,0,'L',1);
        $this->Cell($width/2,$height,$_SESSION['lang']['bulan']." : ".$optBulan[$bulan]." ".$tahun,NULL,0,'R',1);
        $this->Ln();
        $this->Cell($width,$height,$_SESSION['lang']['unit']." : ".$optNm[$unit]." (".$unit.")",NULL,0,'L',1);
        if($afdId!='')
        {
            $this->Ln();
        $this->Cell($width,$height,$_SESSION['lang']['afdeling']." : ".$optNm[$afdId]." (".$afdId.")",NULL,0,'L',1);
        }
        $this->Ln();
        $this->Ln();

        $height = 15;
        $this->SetFont('Arial','B',7);
        $this->Cell(3/100*$width+$wkiri/100*$width,$height,'Produksi (kg):',0,0,'R',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgbgth,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgbgbi,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgbgsd,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgrebi,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,numberformat($kgresd,2).'',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,'',0,0,'L',1);	
        $this->Ln();
        $this->Cell(3/100*$width,$height,'',TRL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,'',TRL,0,'C',1);	
        $this->Cell($wlain*6/100*$width,$height,$_SESSION['lang']['anggaran'],1,0,'C',1);	
        $this->Cell($wlain*4/100*$width,$height,$_SESSION['lang']['realisasi'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,'',TRL,0,'C',1);	
        $this->Ln(); 
        $this->Cell(3/100*$width,$height,'No.',RL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,$_SESSION['lang']['pekerjaan'],RL,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['setahun'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['bulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['sdbulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['bulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['sdbulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['pencapaian'],BRL,0,'C',1);	
        $this->Ln();
        $this->Cell(3/100*$width,$height,'',BRL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,'',BRL,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./kg',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,$_SESSION['lang']['setahun'],1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,$_SESSION['lang']['sdbulanini'],1,0,'C',1);	
        $this->Ln();
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(10,10,'Page '.$this->PageNo()." / {totalPages}",0,0,'L');
    }
}
    //================================

    $pdf=new PDF('L','pt','A4');
	$pdf->AliasNbPages('{totalPages}');
    $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
    $height = 15;
    $pdf->AddPage();
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Arial','',7);
    
    $no=1;
// pdf array content =========================================================================
//    if(!empty($dzArr))foreach($dzArr as $keg){
//        $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
//        $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[111]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[112],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[121]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[122],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[131]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[132],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[211]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[212],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[221]/1000,0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[222],0),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[311],2),1,0,'R',1);	
//        $pdf->Cell($wlain/100*$width,$height,numberformat($keg[312],2),1,0,'R',1);	
//        $no+=1;
//        $pdf->Ln();
//    }else echo 'Data Empty.';
   
if(empty($dzArr)){
    echo "Data Empty.";
}else{
// PANEN DAN PENGUMPUL =========================================================================
    if(!empty($kegpanen)){
        $pdf->Cell(3/100*$width,$height,'A.',1,0,'R',1);	
        $pdf->Cell($wkiri/100*$width,$height,strtoupper($_SESSION['lang']['panen']),1,0,'L',1);	
        $pdf->Cell(12*$wlain/100*$width,$height,'',1,0,'R',1);	
        $pdf->Ln();
        $totalpanen=Array();
        $no=1;
        foreach($kegpanen as $keg){
            $totalpanen['111']+=$dzArr[$keg['noakun']]['111'];
            $totalpanen['112']+=$dzArr[$keg['noakun']]['112'];
            $totalpanen['121']+=$dzArr[$keg['noakun']]['121'];
            $totalpanen['122']+=$dzArr[$keg['noakun']]['122'];
            $totalpanen['131']+=$dzArr[$keg['noakun']]['131'];
            $totalpanen['132']+=$dzArr[$keg['noakun']]['132'];
            $totalpanen['211']+=$dzArr[$keg['noakun']]['211'];
            $totalpanen['212']+=$dzArr[$keg['noakun']]['212'];
            $totalpanen['221']+=$dzArr[$keg['noakun']]['221'];
            $totalpanen['222']+=$dzArr[$keg['noakun']]['222'];
            $totalpanen['311']+=$dzArr[$keg['noakun']]['311'];
            $totalpanen['312']+=$dzArr[$keg['noakun']]['312'];
            $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
            $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['111']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['112'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['121']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['122'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['131']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['132'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['211']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['212'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['221']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['222'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['311'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['312'],2),1,0,'R',1);	
            $no+=1;
            $pdf->Ln();
        }
        $pdf->Cell(($wkiri/100*$width)+(3/100*$width),$height,strtoupper($_SESSION['lang']['biaya'].' '.$_SESSION['lang']['panen']),1,0,'C',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['111']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['112'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['121']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['122'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['131']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['132'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['211']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['212'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['221']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpanen['222'],2),1,0,'R',1);	
        @$panen311=100*$totalpanen['221']/$totalpanen['111'];
        @$panen312=100*$totalpanen['221']/$totalpanen['131'];
        $pdf->Cell($wlain/100*$width,$height,numberformat($panen311,2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($panen312,2),1,0,'R',1);	
        $pdf->Ln();
    }    
// PEMELIHARAAN TM =========================================================================
    if(!empty($kegpanen)){
        $pdf->Cell(3/100*$width,$height,'B.',1,0,'R',1);	
        $pdf->Cell($wkiri/100*$width,$height,strtoupper($_SESSION['lang']['pemeltanaman'].' '.$_SESSION['lang']['TM']),1,0,'L',1);	
        $pdf->Cell(12*$wlain/100*$width,$height,'',1,0,'R',1);	
        $pdf->Ln();
        $totalpemel=Array();
        $no=1;
        foreach($kegpemel as $keg){
            $totalpemel['111']+=$dzArr[$keg['noakun']]['111'];
            $totalpemel['112']+=$dzArr[$keg['noakun']]['112'];
            $totalpemel['121']+=$dzArr[$keg['noakun']]['121'];
            $totalpemel['122']+=$dzArr[$keg['noakun']]['122'];
            $totalpemel['131']+=$dzArr[$keg['noakun']]['131'];
            $totalpemel['132']+=$dzArr[$keg['noakun']]['132'];
            $totalpemel['211']+=$dzArr[$keg['noakun']]['211'];
            $totalpemel['212']+=$dzArr[$keg['noakun']]['212'];
            $totalpemel['221']+=$dzArr[$keg['noakun']]['221'];
            $totalpemel['222']+=$dzArr[$keg['noakun']]['222'];
            $totalpemel['311']+=$dzArr[$keg['noakun']]['311'];
            $totalpemel['312']+=$dzArr[$keg['noakun']]['312'];
            $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
            $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['111']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['112'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['121']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['122'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['131']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['132'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['211']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['212'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['221']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['222'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['311'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['312'],2),1,0,'R',1);	
            $no+=1;
            $pdf->Ln();
        }
        $pdf->Cell(($wkiri/100*$width)+(3/100*$width),$height,strtoupper($_SESSION['lang']['biaya'].' '.$_SESSION['lang']['pemeltanaman'].' '.$_SESSION['lang']['TM']),1,0,'C',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['111']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['112'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['121']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['122'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['131']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['132'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['211']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['212'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['221']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totalpemel['222'],2),1,0,'R',1);	
        @$pemel311=100*$totalpemel['221']/$totalpemel['111'];
        @$pemel312=100*$totalpemel['221']/$totalpemel['131'];
        $pdf->Cell($wlain/100*$width,$height,numberformat($pemel311,2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($pemel312,2),1,0,'R',1);	
        $pdf->Ln();
    } 
// BIAYA TIDAK LANGSUNG (OVER HEAD) =========================================================================
    if(!empty($kegtidak)){
        $pdf->Cell(3/100*$width,$height,'C.',1,0,'R',1);	
        $pdf->Cell($wkiri/100*$width,$height,'BIAYA TIDAK LANGSUNG (OVER HEAD)',1,0,'L',1);	
        $pdf->Cell(12*$wlain/100*$width,$height,'',1,0,'R',1);	
        $pdf->Ln();
        $totalpemel=Array();
        $no=1;
        foreach($kegtidak as $keg){
            $totaltidak['111']+=$dzArr[$keg['noakun']]['111'];
            $totaltidak['112']+=$dzArr[$keg['noakun']]['112'];
            $totaltidak['121']+=$dzArr[$keg['noakun']]['121'];
            $totaltidak['122']+=$dzArr[$keg['noakun']]['122'];
            $totaltidak['131']+=$dzArr[$keg['noakun']]['131'];
            $totaltidak['132']+=$dzArr[$keg['noakun']]['132'];
            $totaltidak['211']+=$dzArr[$keg['noakun']]['211'];
            $totaltidak['212']+=$dzArr[$keg['noakun']]['212'];
            $totaltidak['221']+=$dzArr[$keg['noakun']]['221'];
            $totaltidak['222']+=$dzArr[$keg['noakun']]['222'];
            $totaltidak['311']+=$dzArr[$keg['noakun']]['311'];
            $totaltidak['312']+=$dzArr[$keg['noakun']]['312'];
            $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
            $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['111']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['112'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['121']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['122'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['131']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['132'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['211']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['212'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['221']/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['222'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['311'],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,numberformat($dzArr[$keg['noakun']]['312'],2),1,0,'R',1);	
            $no+=1;
            $pdf->Ln();
        }
        $pdf->Cell(($wkiri/100*$width)+(3/100*$width),$height,'BIAYA TIDAK LANGSUNG (OVER HEAD)',1,0,'C',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['111']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['112'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['121']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['122'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['131']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['132'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['211']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['212'],2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['221']/1000,0),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($totaltidak['222'],2),1,0,'R',1);	
        @$tidak311=100*$totaltidak['221']/$totaltidak['111'];
        @$tidak312=100*$totaltidak['221']/$totaltidak['131'];
        $pdf->Cell($wlain/100*$width,$height,numberformat($tidak311,2),1,0,'R',1);	
        $pdf->Cell($wlain/100*$width,$height,numberformat($tidak312,2),1,0,'R',1);	
        $pdf->Ln();
    } 
    $totalbiaya['111']=$totalpanen['111']+$totalpemel['111']+$totaltidak['111'];
    $totalbiaya['112']=$totalpanen['112']+$totalpemel['112']+$totaltidak['112'];
    $totalbiaya['121']=$totalpanen['121']+$totalpemel['121']+$totaltidak['121'];
    $totalbiaya['122']=$totalpanen['122']+$totalpemel['122']+$totaltidak['122'];
    $totalbiaya['131']=$totalpanen['131']+$totalpemel['131']+$totaltidak['131'];
    $totalbiaya['132']=$totalpanen['132']+$totalpemel['132']+$totaltidak['132'];
    $totalbiaya['211']=$totalpanen['211']+$totalpemel['211']+$totaltidak['211'];
    $totalbiaya['212']=$totalpanen['212']+$totalpemel['212']+$totaltidak['212'];
    $totalbiaya['221']=$totalpanen['221']+$totalpemel['221']+$totaltidak['221'];
    $totalbiaya['222']=$totalpanen['222']+$totalpemel['222']+$totaltidak['222'];
    $totalbiaya['311']=$totalpanen['311']+$totalpemel['311']+$totaltidak['311'];
    $totalbiaya['312']=$totalpanen['312']+$totalpemel['312']+$totaltidak['312'];
    $pdf->Cell(($wkiri/100*$width)+(3/100*$width),$height,'TOTAL '.strtoupper($_SESSION['lang']['biaya'].' '.$_SESSION['lang']['produksi']),1,0,'C',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['111']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['112'],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['121']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['122'],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['131']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['132'],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['211']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['212'],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['221']/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($totalbiaya['222'],2),1,0,'R',1);	
    @$biaya311=100*$totalbiaya['221']/$totalbiaya['111'];
    @$biaya312=100*$totalbiaya['221']/$totalbiaya['131'];
    $pdf->Cell($wlain/100*$width,$height,numberformat($biaya311,2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,numberformat($biaya312,2),1,0,'R',1);	
    $pdf->Ln();
}// end of else data empty    
    $pdf->Output();	 
    break;

    default:
    break;
}
	
?>
