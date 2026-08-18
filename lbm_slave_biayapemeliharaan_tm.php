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

##untuk atas

// aresta real
$aresta="SELECT sum(luasareaproduktif) as luasareal FROM ".$dbname.".setup_blok
    WHERE kodeorg like '".$unit."%' and statusblok ='TM'";
if($afdId!='')
{
    $aresta="SELECT sum(luasareaproduktif) as luasareal FROM ".$dbname.".setup_blok
    WHERE kodeorg like '".$afdId."%' and statusblok ='TM'";
}

$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){
    $luasreal=$res['luasareal'];
}   

// aresta budget
$aresta="SELECT sum(hathnini) as luasareal FROM ".$dbname.".bgt_blok
    WHERE kodeblok like '".$unit."%' and statusblok ='TM' and tahunbudget = '".$tahun."'";
if($afdId!='')
{
    $aresta="SELECT sum(hathnini) as luasareal FROM ".$dbname.".bgt_blok
    WHERE kodeblok like '".$afdId."%' and statusblok ='TM' and tahunbudget = '".$tahun."'";
}

$query=$owlPDO->query($aresta) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch())	
{
    $luasbudg=$res['luasareal'];
}   



$stylehidden = "style='display:none'";	
	
if($proses=='excel')
{
	$stylehidden = "";	
$bg=" bgcolor=#DEDEDE";
$brdr=1;
$tab.="<table border=0>
     <tr>
        <td colspan=8 align=left><font size=3>18. ".strtoupper($_SESSION['lang']['biaya']." ".$_SESSION['lang']['pemeltanaman'])."(TM)</font></td>
        <td colspan=6 align=right>".$_SESSION['lang']['bulan']." : ".$optBulan[$bulan]." ".$tahun."</td>
     </tr> 
     <tr><td colspan=14 align=left>".$_SESSION['lang']['unit']." : ".$optNm[$unit]." (".$unit.")</td></tr>";
if($afdId!='')
{
    $tab.="<tr><td colspan=14 align=left>".$_SESSION['lang']['afdeling']." : ".$optNm[$afdId]." (".$afdId.")</td></tr>";
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
    <td align=right colspan=3 ".$bg.">".$_SESSION['lang']['luasareal']." TM:</td>
    <td align=right colspan=3 ".$bg.">".@number_format($luasbudg,2)."</td>
    <td align=left colspan=6 ".$bg.">Ha</td>
    <td align=right colspan=3 ".$bg.">".@number_format($luasreal,2)."</td>
    <td align=left colspan=5 ".$bg.">Ha</td>";
	if($proses!='excel')  {
		$tab.="<td align=center rowspan=4>View</td>";
	}
	if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan) && $proses!='excel'  && $periode>=$peraktif)  {
		$tab.="<td align=center rowspan=4>#</td>";
		//$tab.="<td align=center rowspan=4  colspan=2 style='width=370px'>".$_SESSION['lang']['penjelasan']." & Upload File Pendukung</td>";
	}
	$tab.="
    </tr>
    <tr>
    <td align=center rowspan=3 ".$bg.">No.</td>
    <td align=center rowspan=3 ".$bg.">".$_SESSION['lang']['pekerjaan']."</td>
    <td align=center rowspan=3 ".$bg.">".$_SESSION['lang']['satuan']."</td>
    <td align=center colspan=9 ".$bg.">".$_SESSION['lang']['anggaran']."</td>
    <td align=center colspan=6 ".$bg.">".$_SESSION['lang']['realisasi']."</td>
    <td align=center rowspan=2 colspan=2 ".$bg.">% ".$_SESSION['lang']['pencapaian']."</td>
    </tr>
    <tr>
    <td align=center colspan=3 ".$bg.">".$_SESSION['lang']['setahun']."</td>
    <td align=center colspan=3 ".$bg.">".$_SESSION['lang']['bulanini']."</td>
    <td align=center colspan=3 ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>
    <td align=center colspan=3 ".$bg.">".$_SESSION['lang']['bulanini']."</td>
    <td align=center colspan=3 ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>
    </tr>
    <tr>
    <td align=center ".$bg.">Volume</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./Sat</td>
    <td align=center ".$bg.">Volume</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./Sat</td>
    <td align=center ".$bg.">Volume</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./Sat</td>
    <td align=center ".$bg.">Volume</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./Sat</td>
    <td align=center ".$bg.">Volume</td>
    <td align=center ".$bg.">Rp. (000)</td>
    <td align=center ".$bg.">Rp./Sat</td>
    <td align=center ".$bg.">".$_SESSION['lang']['setahun']."</td>
    <td align=center ".$bg.">".$_SESSION['lang']['sdbulanini']."</td>
    </tr>
    </thead>
    <tbody>
";
       

#bentuk akun 5
$str="SELECT noakun, namaakun,namaakun1 FROM ".$dbname.".keu_5akun
    WHERE length(noakun)=5 and noakun like '621%'
    ORDER BY noakun";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
	$akunlima[$bar['noakun']]=$bar['noakun'];
	$nmakun[$bar['noakun']]=$bar['namaakun'];
}

#bentuk akun 7
$str="SELECT noakun, namaakun,namaakun1 FROM ".$dbname.".keu_5akun
    WHERE length(noakun)=7 and noakun like '621%'
    ORDER BY noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akun[$bar['noakun']]=$bar['noakun'];	
	$nmakun[$bar['noakun']]=$bar['namaakun'];
}




if($afdId=='')
{$sortunit=$unit;}
 else 
{$sortunit=$afdId;}



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






$str="select * from ".$dbname.".setup_kegiatan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$kdkegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$listkdkegiatan[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	@$totkeg[substr($bar['noakun'],0,5)][$bar['noakun']]+=1;
	@$totakun[substr($bar['noakun'],0,5)]+=1;
	$nmkeg[$bar['kodekegiatan']]=$bar['namakegiatan'];
	$satkeg[$bar['kodekegiatan']]=$bar['satuan'];
}

// echo "<pre>";
// print_r($akunlima);
// print_r($totakun);
// echo "</pre>";


#rwt
$str=" select kegiatan,noakun,".$addstr." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$addstrvol." as sdbivol,fis".$bulan." as bivol,volume
		from ".$dbname.".bgt_budget_detail where kodeorg like '".$sortunit."%' and tahunbudget = '".$tahun."' and noakun like '621%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$bgtthnvollima[substr($bar['noakun'],0,5)]+=$bar['volume']; 
	@$bgtthnvoltjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['volume']; 
	@$bgtthnvolkeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kegiatan']]+=$bar['volume']; 
	
	@$bgtthnrplima[substr($bar['noakun'],0,5)]+=$bar['rupiah']; 
	@$bgtthnrptjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['rupiah']; 
	@$bgtthnrpkeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kegiatan']]+=$bar['rupiah']; 
	
	//bi
	@$bgtbivollima[substr($bar['noakun'],0,5)]+=$bar['bivol']; 
	@$bgtbivoltjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['bivol']; 
	@$bgtbivolkeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kegiatan']]+=$bar['bivol']; 
	
	@$bgtbirplima[substr($bar['noakun'],0,5)]+=$bar['bi']; 
	@$bgtbirptjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['bi']; 
	@$bgtbirpkeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kegiatan']]+=$bar['bi']; 

	//sdbi
	@$bgtsdbivollima[substr($bar['noakun'],0,5)]+=$bar['sdbivol']; 
	@$bgtsdbivoltjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['sdbivol']; 
	@$bgtsdbivolkeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kegiatan']]+=$bar['sdbivol']; 
	
	@$bgtsdbirplima[substr($bar['noakun'],0,5)]+=$bar['sdbi']; 
	@$bgtsdbirptjh[substr($bar['noakun'],0,5)][$bar['noakun']]+=$bar['sdbi']; 
	@$bgtsdbirpkeg[substr($bar['noakun'],0,5)][$bar['noakun']][$bar['kegiatan']]+=$bar['sdbi']; 
	
}



$str=" select * from ".$dbname.".kebun_perawatan_dan_spk_vw where unit='".$unit."' 
	and kodeorg like '".$sortunit."%' and tanggal like '".$periode."%' and kodekegiatan like '621%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$realbivollima[substr($bar['kodekegiatan'],0,5)]+=$bar['hasilkerja']; 
	@$realbivoltjh[substr($bar['kodekegiatan'],0,5)][substr($bar['kodekegiatan'],0,7)]+=$bar['hasilkerja']; 	
	@$realbivolkeg[substr($bar['kodekegiatan'],0,5)][substr($bar['kodekegiatan'],0,7)][$bar['kodekegiatan']]+=$bar['hasilkerja']; 	
	
	@$realbirplima[substr($bar['kodekegiatan'],0,5)]+=$bar['upah']; 
	@$realbirptjh[substr($bar['kodekegiatan'],0,5)][substr($bar['kodekegiatan'],0,7)]+=$bar['upah']; 	
	@$realbirpkeg[substr($bar['kodekegiatan'],0,5)][substr($bar['kodekegiatan'],0,7)][$bar['kodekegiatan']]+=$bar['upah']; 	
}


$str=" select * from ".$dbname.".kebun_perawatan_dan_spk_vw where unit='".$unit."' 
	and kodeorg like '".$sortunit."%' and tanggal between '".$tahun."-01-01' and LAST_DAY('".$periode."-15') and kodekegiatan like '621%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$realsdbivollima[substr($bar['kodekegiatan'],0,5)]+=$bar['hasilkerja']; 
	@$realsdbivoltjh[substr($bar['kodekegiatan'],0,5)][substr($bar['kodekegiatan'],0,7)]+=$bar['hasilkerja']; 	
	@$realsdbivolkeg[substr($bar['kodekegiatan'],0,5)][substr($bar['kodekegiatan'],0,7)][$bar['kodekegiatan']]+=$bar['hasilkerja']; 	
	
	@$realsdbirplima[substr($bar['kodekegiatan'],0,5)]+=$bar['upah']; 
	@$realsdbirptjh[substr($bar['kodekegiatan'],0,5)][substr($bar['kodekegiatan'],0,7)]+=$bar['upah']; 	
	@$realsdbirpkeg[substr($bar['kodekegiatan'],0,5)][substr($bar['kodekegiatan'],0,7)][$bar['kodekegiatan']]+=$bar['upah']; 	
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
		$tab.="<td></td>";
		$tab.="<td align=right>".@number_format($bgtthnvollima[$noakunlima])."</td>";
		$tab.="<td align=right>".@number_format($bgtthnrplima[$noakunlima]/1000)."</td>";
		$tab.="<td align=right>".@number_format($bgtthnrplima[$noakunlima]/$bgtthnvollima[$noakunlima])."</td>";
		
		$tab.="<td align=right>".@number_format($bgtbivollima[$noakunlima])."</td>";
		$tab.="<td align=right>".@number_format($bgtbirplima[$noakunlima]/1000)."</td>";
		$tab.="<td align=right>".@number_format($bgtbirplima[$noakunlima]/$bgtbivollima[$noakunlima])."</td>";
		
		$tab.="<td align=right>".@number_format($bgtsdbivollima[$noakunlima])."</td>";
		$tab.="<td align=right>".@number_format($bgtsdbirplima[$noakunlima]/1000)."</td>";
		$tab.="<td align=right>".@number_format($bgtsdbirplima[$noakunlima]/$bgtsdbivollima[$noakunlima])."</td>";
		
		
		$tab.="<td align=right>".@number_format($realbivollima[$noakunlima])."</td>";
		$tab.="<td align=right>".@number_format($realbirplima[$noakunlima]/1000)."</td>";
		$tab.="<td align=right>".@number_format($realbirplima[$noakunlima]/$realbivollima[$noakunlima])."</td>";
		
		$tab.="<td align=right>".@number_format($realsdbivollima[$noakunlima])."</td>";
		$tab.="<td align=right>".@number_format($realsdbirplima[$noakunlima]/1000)."</td>";
		$tab.="<td align=right>".@number_format($realsdbirplima[$noakunlima]/$realsdbivollima[$noakunlima])."</td>";
		
		$tab.="<td align=right>".@number_format($realsdbirplima[$noakunlima]/$bgtthnrplima[$noakunlima]*100,2)."</td>";
		$tab.="<td align=right>".@number_format($realsdbirplima[$noakunlima]/$bgtsdbirplima[$noakunlima]*100,2)."</td>";
		
		if($proses!='excel')  
		{
			$tab.="<td colspan=3></td>";
		}
		
	$tab.= "</tr>";	
	
	foreach($akun as $noakun){
		if($noakunlima==substr($noakun,0,5)){
			$no+=1;
			$tab.="<tr class=rowcontent ".$stylehidden." id=listakunlina".$nox."".$no.">";
			$tab.="<td align=center style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\">".$no."</td>";
			$tab.= "<td  style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\">".$noakun." - ".$nmakun[$noakun]."</td>";
			$tab.="<td></td>";
			$tab.="<td style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" align=right>".@number_format($bgtthnvoltjh[$noakunlima][$noakun])."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($bgtthnrptjh[$noakunlima][$noakun]/1000)."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($bgtthnrptjh[$noakunlima][$noakun]/$bgtthnvoltjh[$noakunlima][$noakun])."</td>";
			
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($bgtbivoltjh[$noakunlima][$noakun])."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($bgtbirptjh[$noakunlima][$noakun]/1000)."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($bgtbirptjh[$noakunlima][$noakun]/$bgtbivoltjh[$noakunlima][$noakun])."</td>";
			
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($bgtsdbivoltjh[$noakunlima][$noakun])."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($bgtsdbirptjh[$noakunlima][$noakun]/1000)."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($bgtsdbirptjh[$noakunlima][$noakun]/$bgtsdbivoltjh[$noakunlima][$noakun])."</td>";
			
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($realbivoltjh[$noakunlima][$noakun])."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($realbirptjh[$noakunlima][$noakun]/1000)."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($realbirptjh[$noakunlima][$noakun]/$realbivoltjh[$noakunlima][$noakun])."</td>";
			
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($realsdbivoltjh[$noakunlima][$noakun])."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($realsdbirptjh[$noakunlima][$noakun]/1000)."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($realsdbirptjh[$noakunlima][$noakun]/$realsdbivoltjh[$noakunlima][$noakun])."</td>";
			
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($realsdbirptjh[$noakunlima][$noakun]/$bgtthnrptjh[$noakunlima][$noakun]*100,2)."</td>";
			$tab.="<td align=right style='cursor:pointer' title='click to show details' onclick=\"detailrow('".$no."','".@$totkeg[$noakunlima][$noakun]."')\" >".@number_format($realsdbirptjh[$noakunlima][$noakun]/$bgtsdbirptjh[$noakunlima][$noakun]*100,2)."</td>";
			if($proses!='excel')  {
				$tab.= "<td align=center valign=center style=cursor:pointer;><img onclick=\"detailcomment('".$file."','".$unit."','".$periode."','".$afdId."','".$noakun."','html','event')\"class=resicon src=images/skyblue/zoom.png style=position:relative;top:5px></td>";
			}
			if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan) && $proses!='excel'  && $periode>=$peraktif)  {
				$tab.= "<td valign=center><img onclick=\"showpopup('".$file."','".$unit."','".$periode."','".$afdId."','".$noakun."','".$no."','".@$textinput[$noakunlima][$noakun]."')\"  class=resicon src=images/upload-2-xxl.png style=position:relative;top:5px></td>";
				
				
				// $tab.= "<td style=width:220px >
					// <input type=text style=width:200px class=myinputtext name=komen value='".@$textinput[$noakunlima][$noakun]."' id=text".$no." onkeypress=\"return tanpa_kutip(event);\" >
					// <img onclick=\"savecomment('".$file."','".$unit."','".$periode."','".$afdId."','".$noakun."','".$no."')\"  class=resicon src=images/save.png style=position:relative;top:5px>
					// </td>";			
				
				// $tab.="<td style='width:210px;'><input name=fileupload type=file id=fileupload".$no." size=1 class=mybutton style=width:160px>
				// <img onclick=\"savefile('".$file."','".$unit."','".$periode."','".$afdId."','".$noakun."','".$no."')\"  class=resicon src=images/save.png style=position:relative;top:5px>
				// </td>";	
			}
			
			
			$tab.= "</tr>";	
			
			$urutkeg=0;
			foreach($kdkegiatan as $kegiatan){
				if(@$listkdkegiatan[$noakunlima][$noakun][$kegiatan]==$kegiatan){
					@$urutkeg++;
					$tab.="<tr class=rowcontent ".$stylehidden." id=listkegiatan".$no."".$urutkeg.">";
						$tab.="<td></td>";
						$tab.="<td>".$no.".".$urutkeg."&nbsp;&nbsp;&nbsp;&nbsp;".$kegiatan." - ".$nmkeg[$kegiatan]."</td>";
						$tab.="<td>".$satkeg[$kegiatan]."</td>";
						
						$tab.="<td align=right>".@number_format($bgtthnvolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						$tab.="<td align=right>".@number_format($bgtthnrpkeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
						$tab.="<td align=right>".@number_format($bgtthnrpkeg[$noakunlima][$noakun][$kegiatan]/$bgtthnvolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						
						$tab.="<td align=right>".@number_format($bgtbivolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						$tab.="<td align=right>".@number_format($bgtbirpkeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
						$tab.="<td align=right>".@number_format($bgtbirpkeg[$noakunlima][$noakun][$kegiatan]/$bgtbivolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						
						$tab.="<td align=right>".@number_format($bgtsdbivolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						$tab.="<td align=right>".@number_format($bgtsdbirpkeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
						$tab.="<td align=right>".@number_format($bgtsdbirpkeg[$noakunlima][$noakun][$kegiatan]/$bgtsdbivolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						
						$tab.="<td align=right>".@number_format($realbivolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						$tab.="<td align=right>".@number_format($realbirpkeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
						$tab.="<td align=right>".@number_format($realbirpkeg[$noakunlima][$noakun][$kegiatan]/$realbivolkeg[$noakunlima][$noakun][$kegiatan])."</td>";						
						
						$tab.="<td align=right>".@number_format($realsdbivolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						$tab.="<td align=right>".@number_format($realsdbirpkeg[$noakunlima][$noakun][$kegiatan]/1000)."</td>";
						$tab.="<td align=right>".@number_format($realsdbirpkeg[$noakunlima][$noakun][$kegiatan]/$realsdbivolkeg[$noakunlima][$noakun][$kegiatan])."</td>";
						
						$tab.="<td align=right>".@number_format($realsdbirpkeg[$noakunlima][$noakun][$kegiatan]/$bgtthnrpkeg[$noakunlima][$noakun][$kegiatan]*100,2)."</td>";
						$tab.="<td align=right>".@number_format($realsdbirpkeg[$noakunlima][$noakun][$kegiatan]/$bgtsdbirpkeg[$noakunlima][$noakun][$kegiatan]*100,2)."</td>";
					
						if($proses!='excel')  
						{
							$tab.="<td colspan=3></td>";
						}
				}
			}
			
		}
	}
	@$tbgtthnrplima+=$bgtthnrplima[$noakunlima];
	@$tbgtbirplima+=$bgtbirplima[$noakunlima];
	@$tbgtsdbirplima+=$bgtsdbirplima[$noakunlima];
	@$trealbirplima+=$realbirplima[$noakunlima];
	@$trealsdbirplima+=$realsdbirplima[$noakunlima];
}
$tab.= "<tr class=rowcontent>";

    $tab.= "<td align=center colspan=3>Total</td>";
    $tab.= "<td align=right></td>";
	$tab.= "<td align=right>".@number_format($tbgtthnrplima/1000)."</td>";
	$tab.= "<td align=right>".@number_format($tbgtthnrplima/$luasbudg)."</td>";
	
	$tab.= "<td align=right></td>";
	$tab.= "<td align=right>".@number_format($tbgtbirplima/1000)."</td>";
	$tab.= "<td align=right>".@number_format($tbgtbirplima/$luasbudg)."</td>";
	
	$tab.= "<td align=right></td>";
	$tab.= "<td align=right>".@number_format($tbgtsdbirplima/1000)."</td>";
	$tab.= "<td align=right>".@number_format($tbgtsdbirplima/$luasbudg)."</td>";
	
	$tab.= "<td align=right></td>";
	$tab.= "<td align=right>".@number_format($trealbirplima/1000)."</td>";
	$tab.= "<td align=right>".@number_format($trealbirplima/$luasreal)."</td>";
	
	$tab.= "<td align=right></td>";
	$tab.= "<td align=right>".@number_format($trealsdbirplima/1000)."</td>";
	$tab.= "<td align=right>".@number_format($trealsdbirplima/$luasreal)."</td>";
	
	$tab.= "<td align=right>".@number_format($trealsdbirplima/$tbgtthnrplima*100)."</td>";
	$tab.= "<td align=right>".@number_format($trealsdbirplima/$tbgtsdbirplima*100)."</td>";
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
    $nop_="lbm_biayapemeliharan_tm_".$unit.$periode;
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
            $wkiri=15;
            $wlain=4.5;

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
        global $wkiri, $wlain;
        global $luasbudg, $luasreal;
            $width = $this->w - $this->lMargin - $this->rMargin;
  
        $height = 20;
        $this->SetFillColor(220,220,220);
        $this->SetFont('Arial','B',12);

        $this->Cell($width/2,$height,"18. ".strtoupper($_SESSION['lang']['biaya']." ".$_SESSION['lang']['pemeltanaman'])."(TM)",NULL,0,'L',1);
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
        $this->Cell((3/100*$width)+(($wlain+$wkiri)/100*$width),$height,$_SESSION['lang']['luasareal'].' TM:',0,0,'R',1);	
        $this->Cell($wlain*9/100*$width,$height,@number_format($luasbudg,2).' Ha',0,0,'C',1);	
        $this->Cell($wlain*6/100*$width,$height,@number_format($luasreal,2).' Ha',0,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,'',0,0,'L',1);	
        $this->Ln();
        $this->Cell(3/100*$width,$height,'',TRL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,'',TRL,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'',TRL,0,'C',1);	
        $this->Cell($wlain*9/100*$width,$height,$_SESSION['lang']['anggaran'],1,0,'C',1);	
        $this->Cell($wlain*6/100*$width,$height,$_SESSION['lang']['realisasi'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,'',TRL,0,'C',1);	
        $this->Ln(); 
        $this->Cell(3/100*$width,$height,'No.',RL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,$_SESSION['lang']['pekerjaan'],RL,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,$_SESSION['lang']['satuan'],RL,0,'C',1);	
        $this->Cell($wlain*3/100*$width,$height,$_SESSION['lang']['setahun'],1,0,'C',1);	
        $this->Cell($wlain*3/100*$width,$height,$_SESSION['lang']['bulanini'],1,0,'C',1);	
        $this->Cell($wlain*3/100*$width,$height,$_SESSION['lang']['sdbulanini'],1,0,'C',1);	
        $this->Cell($wlain*3/100*$width,$height,$_SESSION['lang']['bulanini'],1,0,'C',1);	
        $this->Cell($wlain*3/100*$width,$height,$_SESSION['lang']['sdbulanini'],1,0,'C',1);	
        $this->Cell($wlain*2/100*$width,$height,$_SESSION['lang']['pencapaian'],BRL,0,'C',1);	
        $this->Ln();
        $this->Cell(3/100*$width,$height,'',BRL,0,'C',1);	
        $this->Cell($wkiri/100*$width,$height,'',BRL,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'',BRL,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Volume',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./Sat',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Volume',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./Sat',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Volume',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./Sat',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Volume',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./Sat',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Volume',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp. (000)',1,0,'C',1);	
        $this->Cell($wlain/100*$width,$height,'Rp./Sat',1,0,'C',1);	
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
    if(!empty($dzArr))foreach($dzArr as $keg){
        if(strlen($keg['noakun'])==5){
            $pdf->Cell((3+$wkiri+$wlain)/100*$width,$height,$keg['noakun'].' - '.$keg['namaakun'],1,0,'L',1);	
            $pdf->Cell(($wlain*17)/100*$width,$height,'',1,0,'R',1);	            
        }else{
            $pdf->Cell(3/100*$width,$height,$no,1,0,'R',1);	
            $pdf->Cell($wkiri/100*$width,$height,$keg['namaakun'],1,0,'L',1);	
            $pdf->Cell($wlain/100*$width,$height,$kamussatuan[$keg['noakun']],1,0,'L',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[110],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[111]/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[112],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[120],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[121]/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[122],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[130],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[131]/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[132],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[210],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[211]/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[212],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[220],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[221]/1000,0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[222],0),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[311],2),1,0,'R',1);	
            $pdf->Cell($wlain/100*$width,$height,@number_format($keg[312],2),1,0,'R',1);	
            $no+=1;            
        }        
        $pdf->Ln();
    }else echo 'Data Empty.';
    $pdf->Cell((3/100*$width)+($wkiri/100*$width)+($wlain/100*$width),$height,'Total',1,0,'C',1);	
    $pdf->Cell($wlain/100*$width,$height,'',1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[111]/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[112],0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,'',1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[121]/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[122],0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,'',1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[131]/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[132],0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,'',1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[211]/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[212],0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,'',1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[221]/1000,0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[222],0),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[311],2),1,0,'R',1);	
    $pdf->Cell($wlain/100*$width,$height,@number_format($total[312],2),1,0,'R',1);	
    
    $pdf->Output();	 
    break;

    default:
    break;
}
	
?>
