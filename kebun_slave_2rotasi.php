<?php
//@uhr
require_once('master_validation.php');
require_once('lib/zLib.php');

$prd = checkPostGet('prd1','');
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg1', '');

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
			<td colspan=7 align=center>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['rotasi']."</td>
			<td colspan=6 align=center>".$_SESSION['lang']['ha']." ".$_SESSION['lang']['panen']."</td>
		</tr>
		<tr>
			<td colspan=3 align=center>".$_SESSION['lang']['budget']."</td>
			<td colspan=2 align=center>".$_SESSION['lang']['realisasi']."</td>
			<td colspan=2 align=center>".$_SESSION['lang']['varian']." %</td>
			<td colspan=2 align=center>".$_SESSION['lang']['budget']."</td>
			<td colspan=2 align=center>".$_SESSION['lang']['realisasi']."</td>
			<td colspan=2 align=center>".$_SESSION['lang']['varian']." %</td>
		</tr>";
		$stream.="
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['setahun']."</td>
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['bi']."</td>
			<td align=center width=60px>".$_SESSION['lang']['sbi']."</td>";
				
$stream.="</thead>
		<tbody>";
		#budget
		$str="select distinct substr(a.kodeorg,1,6) as divisi, a.kodebudget, a.kegiatan, avg(a.rotasi) as rot, a.JUMLAH, a.satuanj, b.tahuntanam from 
			 ".$dbname.".bgt_budget a left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg where a.kodebudget in('SDM-PHL', 'SDM-KHT') 
			 and a.kodeorg like '".$kdorg."%' and a.kegiatan = '611010101' and a.tahunbudget = '".$tahun."' group by divisi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$rot[$bar['divisi']][$bar['tahuntanam']]=$bar['rot'];
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
		$str="select sum(luaspanen) as luaspanen, sum(jjgpanen) as jjgpanen, tahuntanam, divisi, substr(tanggal,1,7) as prd from 
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
		}	
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
			<td align=right>".@number_format(($rot[$divisi][$thntnm]/12),2)."</td>
			<td align=right>".@number_format(($rot[$divisi][$thntnm]/12)*$bln,2)."</td>
			<td align=right>".@number_format($rot[$divisi][$thntnm],2)."</td>
			<td align=right>".@number_format(($luaspnn[$divisi][$thntnm][$prd]/$luasblok[$divisi][$thntnm]),2)."</td>
			<td align=right>".@number_format(($luaspnnsbi[$divisi][$thntnm]/$luasblok[$divisi][$thntnm]),2)."</td>
			<td align=right>".@number_format(((($luaspnn[$divisi][$thntnm][$prd]/$luasblok[$divisi][$thntnm])-($rot[$divisi][$thntnm]/12)))/($rot[$divisi][$thntnm]/12)*100,2)."</td>
			<td align=right>".@number_format(((($luaspnnsbi[$divisi][$thntnm]/$luasblok[$divisi][$thntnm])-(($rot[$divisi][$thntnm]/12)*$bln)))/(($rot[$divisi][$thntnm]/12)*$bln)*100,2)."</td>
			<td align=right>".@number_format(($rot[$divisi][$thntnm]/12)*$luasblok[$divisi][$thntnm],2)."</td>
			<td align=right>".@number_format((($rot[$divisi][$thntnm]/12)*$bln)*$luasblok[$divisi][$thntnm],2)."</td>
			<td align=right>".@number_format($luaspnn[$divisi][$thntnm][$prd],2)."</td>
			<td align=right>".@number_format($luaspnnsbi[$divisi][$thntnm],2)."</td>
			<td align=right>".@number_format(($luaspnn[$divisi][$thntnm][$prd]-($rot[$divisi][$thntnm]/12)*$luasblok[$divisi][$thntnm])/(($rot[$divisi][$thntnm]/12)*$luasblok[$divisi][$thntnm])*100,2)."</td>
			<td align=right>".@number_format(($luaspnnsbi[$divisi][$thntnm]-(($rot[$divisi][$thntnm]/12)*$bln)*$luasblok[$divisi][$thntnm])/((($rot[$divisi][$thntnm]/12)*$bln)*$luasblok[$divisi][$thntnm])*100,2)."</td>
		";

		@$ttlluas[$divisi]+=$luasblok[$divisi][$thntnm];
		@$ttlpkk[$divisi]+=$pokokblok[$divisi][$thntnm];
		@$ttlhaanggbi[$divisi]+=($rot[$divisi][$thntnm]/12)*$luasblok[$divisi][$thntnm];
		@$ttlhaanggsbi[$divisi]+=(($rot[$divisi][$thntnm]/12)*$bln)*$luasblok[$divisi][$thntnm];
		@$ttlharealbi[$divisi]+=$luaspnn[$divisi][$thntnm][$prd];
		@$ttlharealsbi[$divisi]+=$luaspnnsbi[$divisi][$thntnm];
		@$ttlanghathn[$divisi]+=$rot[$divisi][$thntnm]*$luasblok[$divisi][$thntnm];
		}
	}


$stream.="<tr bgcolor=#00BFFF  style='color:#000000'>
		<td align=left colspan=3 ><b>TOTAL ".$divisi."</b></td>
		<td align=right ><b>".@number_format($ttlluas[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlpkk[$divisi])."</b></td>
		<td align=right ><b>".@number_format($ttlhaanggbi[$divisi]/$ttlluas[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlhaanggsbi[$divisi]/$ttlluas[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlanghathn[$divisi]/$ttlluas[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlharealbi[$divisi]/$ttlluas[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlharealsbi[$divisi]/$ttlluas[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format(($ttlharealbi[$divisi]/$ttlluas[$divisi]-($ttlhaanggbi[$divisi]/$ttlluas[$divisi]))/($ttlhaanggbi[$divisi]/$ttlluas[$divisi])*100,2)."</b></td>
		<td align=right ><b>".@number_format(($ttlharealsbi[$divisi]/$ttlluas[$divisi]-($ttlhaanggsbi[$divisi]/$ttlluas[$divisi]))/($ttlhaanggsbi[$divisi]/$ttlluas[$divisi])*100,2)."</b></td>
		<td align=right ><b>".@number_format($ttlhaanggbi[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlhaanggsbi[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlharealbi[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format($ttlharealsbi[$divisi],2)."</b></td>
		<td align=right ><b>".@number_format((($ttlharealbi[$divisi]-$ttlhaanggbi[$divisi])/$ttlhaanggbi[$divisi])*100,2)."</b></td>
		<td align=right ><b>".@number_format((($ttlharealsbi[$divisi]-$ttlhaanggsbi[$divisi])/$ttlhaanggsbi[$divisi])*100,2)."</b></td>";
		
	@$gtluas+=$ttlluas[$divisi];
	@$gtpkk+=$ttlpkk[$divisi];
	@$gthaangbi+=$ttlhaanggbi[$divisi];
	@$gthaangsbi+=$ttlhaanggsbi[$divisi];
	@$gtharealbi+=$ttlharealbi[$divisi];
	@$gtharealsbi+=$ttlharealsbi[$divisi];
	@$gthaangthn+=$ttlanghathn[$divisi];
	

}
$stream.="<tr bgcolor=#1E90FF   style='color:#000000'>
		<td align=left colspan=3 ><b>GRAND TOTAL</b></td>
		<td align=right ><b>".@number_format($gtluas,2)."</b></td>
		<td align=right ><b>".@number_format($gtpkk,0)."</b></td>
		<td align=right ><b>".@number_format($gthaangbi/$gtluas,2)."</b></td>
		<td align=right ><b>".@number_format($gthaangsbi/$gtluas,2)."</b></td>
		<td align=right ><b>".@number_format($gthaangthn/$gtluas,2)."</b></td>
		<td align=right ><b>".@number_format($gtharealbi/$gtluas,2)."</b></td>
		<td align=right ><b>".@number_format($gtharealsbi/$gtluas,2)."</b></td>
		<td align=right ><b>".@number_format(((($gtharealbi/$gtluas)-($gthaangbi/$gtluas))/($gthaangbi/$gtluas))*100,2)."</b></td>
		<td align=right ><b>".@number_format(((($gtharealsbi/$gtluas)-($gthaangsbi/$gtluas))/($gthaangsbi/$gtluas))*100,2)."</b></td>
		<td align=right ><b>".@number_format($gthaangbi,2)."</b></td>
		<td align=right ><b>".@number_format($gthaangsbi,2)."</b></td>
		<td align=right ><b>".@number_format($gtharealbi,2)."</b></td>
		<td align=right ><b>".@number_format($gtharealsbi,2)."</b></td>
		<td align=right ><b>".@number_format((($gtharealbi-$gthaangbi)/$gthaangbi)*100,2)."</b></td>
		<td align=right ><b>".@number_format((($gtharealsbi-$gthaangsbi)/$gthaangsbi)*100,2)."</b></td>";

		
$stream.="
 </tbody>";

		
switch ($proses) {
    case 'preview':
        echo $stream;
        break;

    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "Rekap Persen AKP unit ". $kdorg;
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