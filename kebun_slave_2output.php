<?php
// @uhr
require_once('master_validation.php');
require_once('lib/zLib.php');

$prd = checkPostGet('prd2','');
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg2', '');

if($kdorg=='')
{
    echo"Warning: Unit tidak boleh kosong"; 
    exit;
}
else if ($prd=='')
{
	echo "Warning : Periode tidak boleh kosong";
	exit;
}

$expbln=  explode('-', $prd);
$tahun=$expbln[0];
$bln=$expbln[1];

$blawal=$tahun."-01";

$rangebulan = month_inbetween($blawal, $prd);


$stream="";
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>
			<td rowspan=3 align=center width=20px >".$_SESSION['lang']['nourut']."</td>
			<td rowspan=3 align=center width=50px >".$_SESSION['lang']['divisi']."</td>
			<td rowspan=3 align=center width=50px >".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['tanam']."</td>
			<td rowspan=3 align=center width=50px >".$_SESSION['lang']['luas']." ".$_SESSION['lang']['ha']."</td>
			<td rowspan=3 align=center width=50px >".$_SESSION['lang']['pokok']."</td>
			<td colspan=6 align=center>".$_SESSION['lang']['panen']."</td>
			<td colspan=3 align=center>".$_SESSION['lang']['budget']."</td>
			<td colspan=3 align=center>".$_SESSION['lang']['jjg']." / ".$_SESSION['lang']['jhk']."</td>
			<td colspan=3 align=center>".$_SESSION['lang']['kg']." / ".$_SESSION['lang']['jhk']."</td>
		</tr>
		<tr>
			<td colspan=2 align=center>".$_SESSION['lang']['jhk']."</td>
			<td colspan=2 align=center>".$_SESSION['lang']['jjg']."</td>
			<td colspan=2 align=center>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['kebun']."</td>
			<td rowspan=2 align=center>".$_SESSION['lang']['jhk']."</td>
			<td rowspan=2 align=center>".$_SESSION['lang']['jjg']."</td>
			<td rowspan=2 align=center>".$_SESSION['lang']['kg']."</td>
			<td rowspan=2 align=center>".$_SESSION['lang']['budget']."</td>
			<td colspan=2 align=center>".$_SESSION['lang']['realisasi']."</td>
			<td rowspan=2 align=center>".$_SESSION['lang']['budget']."</td>
			<td colspan=2 align=center>".$_SESSION['lang']['realisasi']."</td>
			
		</tr>";
		$stream.="
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=50px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=50px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=50px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=50px>".$_SESSION['lang']['sbi']."</td>";
				
$stream.="</thead>
		<tbody>";
		#budget hk
		$str="select distinct a.kodeorg, substr(a.kodeorg,1,6) as divisi, a.kodebudget, a.kegiatan, a.JUMLAH as hk, a.satuanj, b.tahuntanam from 
			 ".$dbname.".bgt_budget a left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg where a.kodebudget in('SDM-PHL', 'SDM-KHT') 
			 and a.kodeorg like '".$kdorg."%' and a.kegiatan = '611010101' and a.tahunbudget = '".$tahun."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$hkbgt[$bar['divisi']][$bar['tahuntanam']]+=$bar['hk'];
		}
		#budget jjg
		$str="select substr(a.kodeblok,1,6)as divisi, b.tahuntanam, sum(jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12) as jjg from 
			 ".$dbname.".bgt_produksi_kebun a left join ".$dbname.".setup_blok b on a.kodeblok = b.kodeorg where  
			 a.kodeblok like '".$kdorg."%' and a.tahunbudget = '".$tahun."' group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgbgt[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjg'];
		}
		#budget kg
		$str="select divisi, thntnm as tahuntanam, sum(kgsetahun) as kg from 
			 ".$dbname.".bgt_produksi_kbn_kg_vw where kodeblok like '".$kdorg."%' and tahunbudget = '".$tahun."' group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kgbgt[$bar['divisi']][$bar['tahuntanam']]+=$bar['kg'];
		}
				
		#setup blok
		$str="select substr(kodeorg,1,6) as divisi, tahuntanam, sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from 
			 ".$dbname.".setup_blok where kodeorg like '".$kdorg."%' group by divisi, tahuntanam";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$luasblok[$bar['divisi']][$bar['tahuntanam']]=$bar['luas'];
			$pokokblok[$bar['divisi']][$bar['tahuntanam']]=$bar['pokok'];
		}	
		#rekappnn
		$str="select sum(luaspanen) as luaspanen, sum(jjgpanen) as jjgpanen, sum(tenagakerja) as hk, sum(kgkebun) as kgkebun, tahuntanam, divisi, substr(tanggal,1,7) as prd from 
			 ".$dbname.".kebun_rekappnn_vw where divisi like '".$kdorg."%' and left(tanggal,7) >= '".$tahun."-01' and left(tanggal,7) <= '".$prd."' group by divisi, tahuntanam, prd";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$luaspnn[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['luaspanen'];
			@$luaspnnsbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['luaspanen'];
			$hkbi[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['hk'];
			@$hksbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['hk'];
			$jjgbi[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['jjgpanen'];
			@$jjgsbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjgpanen'];
			$kgkbnbi[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['kgkebun'];
			@$kgkbnsbi[$bar['divisi']][$bar['tahuntanam']]+=$bar['kgkebun'];
		}
		
		#spb
		$str="select divisi, tahuntanam, sum(jjg) as jjg, sum(kgwb) as kgwb, substr(tanggal,1,7) as prd from 
			 ".$dbname.".kebun_spb_vw where divisi like '".$kdorg."%' and left(tanggal,7) >= '".$tahun."-01' and left(tanggal,7) <= '".$prd."' group by divisi, tahuntanam, prd";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$jjgkrm[$bar['divisi']][$bar['tahuntanam']]=$bar['jjg'];
			$kgwb[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['kgwb'];
		}	

array_multisort($kddivisi,SORT_ASC);
array_multisort($tahuntanam,SORT_ASC);

foreach($kddivisi as $divisi)
{
	foreach($tahuntanam as $thntnm)
	{
		if(@$listtahuntanam[$divisi][$thntnm]!=''){
		$no+=1;	
		$stream.="<tr class=rowcontent>
            <td align=center>".$no."</td>
			<td align=center>".$divisi."</td>
			<td align=center>".$thntnm."</td>	
			<td align=right>".@number_format($luasblok[$divisi][$thntnm],2)."</td>	
			<td align=right>".@number_format($pokokblok[$divisi][$thntnm])."</td>	
			<td align=right>".@number_format($hkbi[$divisi][$thntnm][$prd],2)."</td>
			<td align=right>".@number_format($hksbi[$divisi][$thntnm],2)."</td>
			<td align=right>".@number_format($jjgbi[$divisi][$thntnm][$prd],0)."</td>
			<td align=right>".@number_format($jjgsbi[$divisi][$thntnm],0)."</td>
			<td align=right>".@number_format($kgkbnbi[$divisi][$thntnm][$prd],0)."</td>
			<td align=right>".@number_format($kgkbnsbi[$divisi][$thntnm],0)."</td>
			<td align=right>".@number_format($hkbgt[$divisi][$thntnm],0)."</td>
			<td align=right>".@number_format($jjgbgt[$divisi][$thntnm],0)."</td>
			<td align=right>".@number_format($kgbgt[$divisi][$thntnm],0)."</td>
			<td align=right>".@number_format($jjgbgt[$divisi][$thntnm]/$hkbgt[$divisi][$thntnm],0)."</td>
			<td align=right>".@number_format($jjgbi[$divisi][$thntnm][$prd]/$hkbi[$divisi][$thntnm][$prd],0)."</td>
			<td align=right>".@number_format($jjgsbi[$divisi][$thntnm]/$hksbi[$divisi][$thntnm],0)."</td>
			<td align=right>".@number_format($kgbgt[$divisi][$thntnm]/$hkbgt[$divisi][$thntnm],0)."</td>
			<td align=right>".@number_format($kgkbnbi[$divisi][$thntnm][$prd]/$hkbi[$divisi][$thntnm][$prd],0)."</td>
			<td align=right>".@number_format($kgkbnsbi[$divisi][$thntnm]/$hksbi[$divisi][$thntnm],0)."</td>
		";

		@$ttlluas[$divisi]+=$luasblok[$divisi][$thntnm];
		@$ttlpkk[$divisi]+=$pokokblok[$divisi][$thntnm];
		@$ttlhkbi[$divisi]+=$hkbi[$divisi][$thntnm][$prd];
		@$ttlhksbi[$divisi]+=$hksbi[$divisi][$thntnm];
		@$ttljjgbi[$divisi]+=$jjgbi[$divisi][$thntnm][$prd];
		@$ttljjgsbi[$divisi]+=$jjgsbi[$divisi][$thntnm];
		@$ttlkgkbnbi[$divisi]+=$kgkbnbi[$divisi][$thntnm][$prd];
		@$ttlkgkbnsbi[$divisi]+=$kgkbnsbi[$divisi][$thntnm];
		@$ttlhkbgt[$divisi]+=$hkbgt[$divisi][$thntnm];
		@$ttljjgbgt[$divisi]+=$jjgbgt[$divisi][$thntnm];
		@$ttlkgbgt[$divisi]+=$kgbgt[$divisi][$thntnm];
		
		}
	}


$stream.="<tr bgcolor=#00BFFF  style='color:#000000'>
		<td align=left colspan=3 ><b>TOTAL ".$divisi."</b></td>
		<td align=right ><b>".@number_format($ttlluas[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlpkk[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlhkbi[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlhksbi[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttljjgbi[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttljjgsbi[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlkgkbnbi[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlkgkbnsbi[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlhkbgt[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttljjgbgt[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlkgbgt[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttljjgbgt[$divisi]/$ttlhkbgt[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttljjgbi[$divisi]/$ttlhkbi[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttljjgsbi[$divisi]/$ttlhksbi[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlkgbgt[$divisi]/$ttlhkbgt[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlkgkbnbi[$divisi]/$ttlhkbi[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlkgkbnsbi[$divisi]/$ttlhksbi[$divisi])."</b></td>
		
		
		";
		
	@$gtluas+=$ttlluas[$divisi];
	@$gtpkk+=$ttlpkk[$divisi];
	@$gthkbi+=$ttlhkbi[$divisi];
	@$gthksbi+=$ttlhksbi[$divisi];
	@$gtjjgbi+=$ttljjgbi[$divisi];
	@$gtjjgsbi+=$ttljjgsbi[$divisi];
	@$gtkgbi+=$ttlkgkbnbi[$divisi];
	@$gtkgsbi+=$ttlkgkbnsbi[$divisi];
	@$gthkbgt+=$ttlhkbgt[$divisi];
	@$gtjjgbgt+=$ttljjgbgt[$divisi];
	@$gtkgbgt+=$ttlkgbgt[$divisi];
	

}
$stream.="<tr bgcolor=#1E90FF   style='color:#000000'>
		<td align=left colspan=3 ><b>GRAND TOTAL</b></td>
		<td align=right ><b>".@number_format($gtluas,2)."</b></td>
		<td align=right ><b>".@number_format($gtpkk,0)."</b></td>
		
		<td align=right ><b>".@number_format($gthkbi,2)."</b></td>
		<td align=right ><b>".@number_format($gthksbi,2)."</b></td>
		<td align=right ><b>".@number_format($gtjjgbi,0)."</b></td>
		<td align=right ><b>".@number_format($gtjjgsbi,0)."</b></td>
		<td align=right ><b>".@number_format($gtkgbi,0)."</b></td>
		<td align=right ><b>".@number_format($gtkgsbi,0)."</b></td>
		<td align=right ><b>".@number_format($gthkbgt,0)."</b></td>
		<td align=right ><b>".@number_format($gtjjgbgt,0)."</b></td>
		<td align=right ><b>".@number_format($gtkgbgt,0)."</b></td>
		<td align=right ><b>".@number_format($gtjjgbgt/$gthkbgt,0)."</b></td>
		<td align=right ><b>".@number_format($gtjjgbi/$gthkbi,0)."</b></td>
		<td align=right ><b>".@number_format($gtjjgsbi/$gthksbi,0)."</b></td>
		<td align=right ><b>".@number_format($gtkgbgt/$gthkbgt,0)."</b></td>
		<td align=right ><b>".@number_format($gtkgbi/$gthkbi,0)."</b></td>
		<td align=right ><b>".@number_format($gtkgsbi/$gthksbi,0)."</b></td>		
		";

$stream.="
 </tbody>";

		
switch ($proses) {
    case 'preview':
        echo $stream;
        break;

    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "Output Panen Unit ". $kdorg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
        break;
}

?>