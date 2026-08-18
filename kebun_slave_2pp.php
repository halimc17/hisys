<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
$proses = checkPostGet('proses', '');

$unit = checkPostGet('unit', '');
$tgl2 = tanggalsystemn(checkPostGet('tgl2', ''));


$tahun=substr($tgl2,0,4);
$per1=$tahun.'-01';
$tgl1=$tahun.'-01-01';
$tahundpn=$tahun+1;

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
	  <tr class=rowheader>
		<td align=center rowspan='4'>Divisi</td>
		<td align=center rowspan='4'>TT</td>
		<td align=center rowspan='4'>Blok</td>
		<td align=center rowspan='4'>Jenis Bibit</td>
		<td align=center rowspan='4'>HA Netto</td>
		<td align=center rowspan='4'>SPH</td>
		<td align=center colspan='8'>Tahun Ini</td>
		<td align=center colspan='3'>Tahun Depan</td>
	  </tr>
	  <tr>
		<td align=center colspan='3'>Aktual</td>
		<td align=center colspan='3'>Sensus</td>
		
		
		<td align=center colspan='2'>Distribusi Produksi (%)</td>
		<td align=center colspan='3'>Perhitungan Budget</td>
		
	  </tr>
	  <tr>
		<td align=center rowspan='2'>Jjg/Pkk</td>
		<td align=center rowspan='2'>Bjr</td>
		<td align=center rowspan='2'>Ton/Ha</td>
		<td align=center rowspan='2'>Jjg/Pkk</td>
		<td align=center rowspan='2'>Bjr</td>
		<td align=center rowspan='2'>Ton/Ha</td>

		<td align=center colspan='2'>Aktual + Sensus</td>
		
		<td align=center rowspan='2'>Jjg/Pkk</td>
		<td align=center rowspan='2'>Bjr</td>
		<td align=center rowspan='2'>Ton/Ha</td>
		
	  </tr>
	  <tr>
		<td align=center >SM 1</td>
		<td align=center >SM 2</td>
		
	  </tr>
		 
    </thead>
 <tbody>";

 
 
 
######################################
############# prepare data ###########
######################################

$str="select substr(kodeorg,1,6) as divisi,kodeorg as blok,tahuntanam as tahuntanam,jenisbibit,"
	. " statusblok as statusblok,luasareaproduktif as luasareaproduktif,jumlahpokok"
	. " from ".$dbname.".setup_blok where kodeorg like '".$unit."%'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $kdblok[$bar['blok']]=$bar['blok'];
    $kddivisi[$bar['divisi']]=$bar['divisi'];
    $tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
    
    $listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
    $listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
    
    $luas[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['luasareaproduktif'];
    $status[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['statusblok'];
	$jenisbibit[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jenisbibit'];
	
	$jumlahpokok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jumlahpokok'];

}


#sm1
$str=" select * from ".$dbname.".kebun_spb_vw where tanggal  between '".$tgl1."' and '".$tahun."-06-30' and divisi like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    @$jjgaktualsm1[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjg'];
	@$kgaktualsm1[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
}

$str=" select * from ".$dbname.".kebun_spb_vw where tanggal  between '".$tahun."-07-01' and '".$tahun."-12-31' and divisi like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    @$jjgaktualsm2[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjg'];
	@$kgaktualsm2[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
}


$str=" select * from ".$dbname.".kebun_rencanapanen_vw where tahun='".$tahun."' and kodeorg like '".$unit."%' and semester=1 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
	@$jjgsensussm1[$bar['kodeorg']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
	@$kgsensussm1[$bar['kodeorg']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['kgsensus'];
	
}

$str=" select * from ".$dbname.".kebun_rencanapanen_vw where tahun='".$tahun."' and kodeorg like '".$unit."%' and semester=2 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
	@$jjgsensussm2[$bar['kodeorg']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jumlah'];
	@$kgsensussm2[$bar['kodeorg']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['kgsensus'];
}



$addstrthn="(";
$addstrthnjjg="(";
for($i=1;$i<=12;$i++)
{
    if($i<10)
    {
		$isijjg="jjg0".$i;
        $isi="kg0".$i;
    }
    else 
    {
        $isi="kg".$i;
		$isijjg="jjg".$i;
    }
    if($i<12)
    {
        $addstrthn.=$isi."+";
		$addstrthnjjg.=$isijjg."+";
    }
    else
    {
        $addstrthn.=$isi;
		$addstrthnjjg.=$isijjg;
    }
}
$addstrthn.=")";
$addstrthnjjg.=")";


$str=" select tahunbudget,kodeunit,divisi,kodeblok,thntnm,".$addstrthn." as thn
		from ".$dbname.".bgt_produksi_kbn_kg_vw where divisi like '".$unit."%' and tahunbudget='".$tahundpn."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
	@$kgbgt[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]+=$bar['thn'];
} 

$str=" select tahunbudget,kodeunit,kodeblok,thntnm,".$addstrthnjjg." as thn
		from ".$dbname.".bgt_produksi_kbn_vw where kodeunit = '".$unit."' and tahunbudget='".$tahundpn."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
	@$jjgbgt[substr($bar['kodeblok'],0,6)][$bar['thntnm']][$bar['kodeblok']]+=$bar['thn'];
} 




foreach($kddivisi as $divisi)
{
	foreach($tahuntanam as $thntnm)
	{
		if(@$listtahuntanam[$divisi][$thntnm]!='')
		{
			foreach($kdblok as $blok)
			{
				if(@$listblok[$divisi][$thntnm][$blok]!='')
				{
					$stream.="<tr class=rowcontent>
								<td align=center>".romawi(substr($divisi,5,1))."</td>
								<td>".$thntnm."</td>
								<td>".$blok."</td>
								<td>".$jenisbibit[$divisi][$thntnm][$blok]."</td>
								<td align=right>".$luas[$divisi][$thntnm][$blok]."</td>
								<td align=right>".@number_format($jumlahpokok[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok],2)."</td>
								<td align=right>".@number_format(($jjgaktualsm1[$divisi][$thntnm][$blok]+$jjgaktualsm2[$divisi][$thntnm][$blok])/$jumlahpokok[$divisi][$thntnm][$blok],2)."</td>
								<td align=right>".@number_format(($kgaktualsm1[$divisi][$thntnm][$blok]+$kgaktualsm2[$divisi][$thntnm][$blok])/($jjgaktualsm1[$divisi][$thntnm][$blok]+$jjgaktualsm2[$divisi][$thntnm][$blok]),2)."</td>
								<td align=right>".@number_format((($kgaktualsm1[$divisi][$thntnm][$blok]+$kgaktualsm2[$divisi][$thntnm][$blok])/$luas[$divisi][$thntnm][$blok])/1000,2)."</td>
								
								<td align=right>".@number_format(($jjgsensussm1[$divisi][$thntnm][$blok]+$jjgsensussm2[$divisi][$thntnm][$blok])/$jumlahpokok[$divisi][$thntnm][$blok],2)."</td>
								<td align=right>".@number_format(($kgsensussm1[$divisi][$thntnm][$blok]+$kgsensussm2[$divisi][$thntnm][$blok])/($jjgsensussm1[$divisi][$thntnm][$blok]+$jjgsensussm2[$divisi][$thntnm][$blok]),2)."</td>
								<td align=right>".@number_format((($kgsensussm1[$divisi][$thntnm][$blok]+$kgsensussm2[$divisi][$thntnm][$blok])/1000)/($luas[$divisi][$thntnm][$blok]),2)."</td>
								
								<td align=right>".@number_format($kgaktualsm1[$divisi][$thntnm][$blok]+$kgsensussm1[$divisi][$thntnm][$blok],2)."</td>
								<td align=right>".@number_format($kgaktualsm2[$divisi][$thntnm][$blok]+$kgsensussm2[$divisi][$thntnm][$blok],2)."</td>
								
								<td align=right>".@number_format($jjgbgt[$divisi][$thntnm][$blok]/$jumlahpokok[$divisi][$thntnm][$blok],2)."</td>
								<td align=right>".@number_format($kgbgt[$divisi][$thntnm][$blok]/$jjgbgt[$divisi][$thntnm][$blok],2)."</td>
								<td align=right>".@number_format($kgbgt[$divisi][$thntnm][$blok]/$luas[$divisi][$thntnm][$blok]/1000,2)."</td>
								
							  </tr>";
							  
							  
							@$luastt[$divisi][$thntnm]+=$luas[$divisi][$thntnm][$blok];  
							@$jumlahpokoktt[$divisi][$thntnm]+=$jumlahpokok[$divisi][$thntnm][$blok];  
							@$jjgaktualsm1tt[$divisi][$thntnm]+=$jjgaktualsm1[$divisi][$thntnm][$blok];
							@$jjgaktualsm2tt[$divisi][$thntnm]+=$jjgaktualsm2[$divisi][$thntnm][$blok];
							
							@$kgaktualsm1tt[$divisi][$thntnm]+=$kgaktualsm1[$divisi][$thntnm][$blok];
							@$kgaktualsm2tt[$divisi][$thntnm]+=$kgaktualsm2[$divisi][$thntnm][$blok];
							
							@$jjgsensussm1tt[$divisi][$thntnm]+=$jjgsensussm1[$divisi][$thntnm][$blok];
							@$jjgsensussm2tt[$divisi][$thntnm]+=$jjgsensussm2[$divisi][$thntnm][$blok];
							@$kgsensussm1tt[$divisi][$thntnm]+=$kgsensussm1[$divisi][$thntnm][$blok];
							@$kgsensussm2tt[$divisi][$thntnm]+=$kgsensussm2[$divisi][$thntnm][$blok];
							
							@$jjgbgttt[$divisi][$thntnm]+=$jjgbgt[$divisi][$thntnm][$blok];
							@$kgbgttt[$divisi][$thntnm]+=$kgbgt[$divisi][$thntnm][$blok];
							
				}
			}
			$stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=4>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['tahuntanam']."  ".$thntnm."</td>
                    <td align=right>".@number_format($luastt[$divisi][$thntnm],2)."</td>
					<td align=right>".@number_format($jumlahpokoktt[$divisi][$thntnm]/$luastt[$divisi][$thntnm],2)."</td>
					<td align=right>".@number_format(($jjgaktualsm1tt[$divisi][$thntnm]+$jjgaktualsm2tt[$divisi][$thntnm])/$jumlahpokoktt[$divisi][$thntnm],2)."</td>
					<td align=right>".@number_format(($kgaktualsm1tt[$divisi][$thntnm]+$kgaktualsm2tt[$divisi][$thntnm])/($jjgaktualsm1tt[$divisi][$thntnm]+$jjgaktualsm2tt[$divisi][$thntnm]),2)."</td>
					<td align=right>".@number_format((($kgaktualsm1tt[$divisi][$thntnm]+$kgaktualsm2tt[$divisi][$thntnm])/$luastt[$divisi][$thntnm])/1000,2)."</td>
					
					<td align=right>".@number_format(($jjgsensussm1tt[$divisi][$thntnm]+$jjgsensussm2tt[$divisi][$thntnm])/$jumlahpokoktt[$divisi][$thntnm],2)."</td>
					<td align=right>".@number_format(($kgsensussm1tt[$divisi][$thntnm]+$kgsensussm2tt[$divisi][$thntnm])/($jjgsensussm1tt[$divisi][$thntnm]+$jjgsensussm2tt[$divisi][$thntnm]),2)."</td>
					<td align=right>".@number_format((($kgsensussm1tt[$divisi][$thntnm]+$kgsensussm2tt[$divisi][$thntnm])/1000)/($luastt[$divisi][$thntnm]),2)."</td>
					
					<td align=right>".@number_format(($kgaktualsm1tt[$divisi][$thntnm]+$kgsensussm1tt[$divisi][$thntnm]),2)."</td>
					<td align=right>".@number_format(($kgaktualsm2tt[$divisi][$thntnm]+$kgsensussm2tt[$divisi][$thntnm]),2)."</td>	

					<td align=right>".@number_format($jjgbgttt[$divisi][$thntnm]/$jumlahpokoktt[$divisi][$thntnm],2)."</td>
					<td align=right>".@number_format($kgbgttt[$divisi][$thntnm]/$jjgbgttt[$divisi][$thntnm],2)."</td>
					<td align=right>".@number_format($kgbgttt[$divisi][$thntnm]/$luastt[$divisi][$thntnm]/1000,2)."</td>	
					";
					
				@$luasdiv[$divisi]+=$luastt[$divisi][$thntnm];
				@$jumlahpokokdiv[$divisi]+=$jumlahpokoktt[$divisi][$thntnm];
				@$jjgaktualsm1div[$divisi]+=$jjgaktualsm1tt[$divisi][$thntnm];
				@$jjgaktualsm2div[$divisi]+=$jjgaktualsm2tt[$divisi][$thntnm];
				
				@$kgaktualsm1div[$divisi]+=$kgaktualsm1tt[$divisi][$thntnm];
				@$kgaktualsm2div[$divisi]+=$kgaktualsm2tt[$divisi][$thntnm];
				
				@$jjgsensussm1div[$divisi]+=$jjgsensussm1tt[$divisi][$thntnm];
				@$jjgsensussm2div[$divisi]+=$jjgsensussm2tt[$divisi][$thntnm];
				@$kgsensussm1div[$divisi]+=$kgsensussm1tt[$divisi][$thntnm];
				@$kgsensussm2div[$divisi]+=$kgsensussm2tt[$divisi][$thntnm];
				
				@$jjgbgtdiv[$divisi]+=$jjgbgttt[$divisi][$thntnm];
				@$kgbgtdiv[$divisi]+=$kgbgttt[$divisi][$thntnm];
					
					
		}
	}
	$stream.="
        <tr bgcolor=#48D1CC>
            <td colspan=4>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['divisi']."  ".romawi(substr($divisi,5,1))."</td>
                    <td align=right>".@number_format($luasdiv[$divisi],2)."</td>
					<td align=right>".@number_format($jumlahpokokdiv[$divisi]/$luasdiv[$divisi],2)."</td>
					<td align=right>".@number_format(($jjgaktualsm1div[$divisi]+$jjgaktualsm2div[$divisi])/$jumlahpokokdiv[$divisi],2)."</td>
					<td align=right>".@number_format(($kgaktualsm1div[$divisi]+$kgaktualsm2div[$divisi])/($jjgaktualsm1div[$divisi]+$jjgaktualsm2div[$divisi]),2)."</td>
					<td align=right>".@number_format((($kgaktualsm1div[$divisi]+$kgaktualsm2div[$divisi])/$luasdiv[$divisi])/1000,2)."</td>
					
					<td align=right>".@number_format(($jjgsensussm1div[$divisi]+$jjgsensussm2div[$divisi])/$jumlahpokokdiv[$divisi],2)."</td>
					<td align=right>".@number_format(($kgsensussm1div[$divisi]+$kgsensussm2div[$divisi])/($jjgsensussm1div[$divisi]+$jjgsensussm2div[$divisi]),2)."</td>
					<td align=right>".@number_format((($kgsensussm1div[$divisi]+$kgsensussm2div[$divisi])/1000)/($luasdiv[$divisi]),2)."</td>
					
					<td align=right>".@number_format(($kgaktualsm1div[$divisi]+$kgsensussm1div[$divisi]),2)."</td>
					<td align=right>".@number_format(($kgaktualsm2div[$divisi]+$kgsensussm2div[$divisi]),2)."</td>	

					<td align=right>".@number_format($jjgbgtdiv[$divisi]/$jumlahpokokdiv[$divisi],2)."</td>
					<td align=right>".@number_format($kgbgtdiv[$divisi]/$jjgbgtdiv[$divisi],2)."</td>
					<td align=right>".@number_format($kgbgtdiv[$divisi]/$luasdiv[$divisi]/1000,2)."</td>	
					";
					
				@$gtluas+=$luasdiv[$divisi];
				@$gtjumlahpokok+=$jumlahpokokdiv[$divisi];
				@$gtjjgaktualsm1+=$jjgaktualsm1div[$divisi];
				@$gtjjgaktualsm2+=$jjgaktualsm2div[$divisi];
				
				@$gtkgaktualsm1+=$kgaktualsm1div[$divisi];
				@$gtkgaktualsm2+=$kgaktualsm2div[$divisi];
				
				@$gtjjgsensussm1+=$jjgsensussm1div[$divisi];
				@$gtjjgsensussm2+=$jjgsensussm2div[$divisi];
				@$gtkgsensussm1+=$kgsensussm1div[$divisi];
				@$gtkgsensussm2+=$kgsensussm2div[$divisi];
				
				@$gtjjgbgt+=$jjgbgtdiv[$divisi];
				@$gtkgbgt+=$kgbgtdiv[$divisi];			
					
}

$stream.="
        <tr bgcolor=#009999>
             <td align=left colspan=4>".$_SESSION['lang']['grnd_total']." ".$unit."</td>
                    <td align=right>".@number_format($gtluas,2)."</td>
					<td align=right>".@number_format($gtjumlahpokok/$gtluas,2)."</td>
					<td align=right>".@number_format(($gtjjgaktualsm1+$gtjjgaktualsm2)/$gtjumlahpokok,2)."</td>
					<td align=right>".@number_format(($gtkgaktualsm1+$gtkgaktualsm2)/($gtjjgaktualsm1+$gtjjgaktualsm2),2)."</td>
					<td align=right>".@number_format((($gtkgaktualsm1+$gtkgaktualsm2)/$gtluas)/1000,2)."</td>
					
					<td align=right>".@number_format(($gtjjgsensussm1+$gtjjgsensussm2)/$gtjumlahpokok,2)."</td>
					<td align=right>".@number_format(($gtkgsensussm1+$gtkgsensussm2)/($gtjjgsensussm1+$gtjjgsensussm2),2)."</td>
					<td align=right>".@number_format((($gtkgsensussm1+$gtkgsensussm2)/1000)/($gtluas),2)."</td>
					
					<td align=right>".@number_format(($gtkgaktualsm1+$gtkgsensussm1),2)."</td>
					<td align=right>".@number_format(($gtkgaktualsm2+$gtkgsensussm2),2)."</td>	

					<td align=right>".@number_format($gtjjgbgt/$gtjumlahpokok,2)."</td>
					<td align=right>".@number_format($gtkgbgt/$gtjjgbgt,2)."</td>
					<td align=right>".@number_format($gtkgbgt/$gtluas/1000,2)."</td>
					";
 
 
			
			
	
         
                  
$stream.="
 </tbody>
     </table>";

switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "Penaksiran_Produksi_Budget_" . $unit;
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