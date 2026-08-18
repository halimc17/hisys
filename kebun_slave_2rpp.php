<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_bar.php');

$method = checkPostGet('method', '');
$kdorg = checkPostGet('kdorg', '');
$tgl2 = tanggalsystemn(checkPostGet('tgl2', ''));
$blok = checkPostGet('blok', '');

$tahun=substr($tgl2,0,4);
$perawal=$tahun.'-01';
$perakhir=$tahun.'-12';
$tglawal=$tahun.'-01-01';

$tipe = checkPostGet('tipe', '');
$print = checkPostGet('print', '');

$expblnbgt=  explode('-', $tgl2);
$blnbgt=$expblnbgt[1];



$tgl1=$expblnbgt[0].'-'.$expblnbgt[1].'-01';


switch ($method) {
	
	
	case'prev3':
		$tab="<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
			<thead>
			<tr class=rowheader>
				<th rowspan=2 align=center width='50' >".$_SESSION['lang']['bulan']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['produksi']." (".$_SESSION['lang']['jjg'].")</th>
				<th align=center colspan=2>".$_SESSION['lang']['bjr']."</th>
				<th align=center colspan=4>".$_SESSION['lang']['produksi']." (Ton)</th>
			</tr>
			<tr>
				<th align=center width='60' >".$_SESSION['lang']['kebun']."</th>
				<th align=center width='60' >".$_SESSION['lang']['pabrik']."</th>
				<th align=center width='40' >".$_SESSION['lang']['kebun']."</th>
				<th align=center width='40' >".$_SESSION['lang']['pabrik']."</th>
				<th align=center width='60' >".$_SESSION['lang']['kebun']."</th>
				<th align=center width='60' >".$_SESSION['lang']['pabrik']."</th>
				<th align=center width='60' >".$_SESSION['lang']['sensus']."</th>
				<th align=center width='62' >".$_SESSION['lang']['budget']."</th>
			</tr>
			</thead>";
			

			
		$str=" select sum(jjg) as jjg,sum(kgwb) as kgwb,substr(tanggal,1,7) as bulan,substr(tanggal,6,2) as bln from ".$dbname.".kebun_spb_vw 
				where tanggal between '".$tglawal."' and  '".$tgl2."'   and kodeorg = '".$kdorg."' group by bulan order by bulan asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$realp[$bar['bulan']]=$bar['kgwb'];	
			$jjgp[$bar['bulan']]=$bar['jjg'];	
		}	
				
		
		$str=" select sum(kg01) as kg01,sum(kg02) as kg02,sum(kg03) as kg03,sum(kg04) as kg04,sum(kg05) as kg05,sum(kg06) as kg06,
					  sum(kg07) as kg07,sum(kg08) as kg08,sum(kg09) as kg09,sum(kg10) as kg10,sum(kg11) as kg11,sum(kg12) as kg12,
					  tahunbudget,kodeunit
			 from ".$dbname.".bgt_produksi_kbn_kg_vw 
			 where kodeunit = '".$kdorg."' and tahunbudget='".$tahun."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			for($i=0;$i<=12;$i++)
			{
				if($i<10)
				{
					@$bgt[$bar['tahunbudget'].'-0'.$i]=$bar['kg0'.$i];
				}
				else
				{
					@$bgt[$bar['tahunbudget'].'-'.$i]=$bar['kg'.$i];
				}
			}
			
		}
		
		
		$str=" select * from ".$dbname.".kebun_rencanapanen_vw where tahun='".$tahun."' and kodeorg like '".$kdorg."%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['bulan']<10)
			{
				@$sensus[$bar['tahun'].'-0'.$bar['bulan']]+=$bar['kgsensus'];
			}
			else
			{
				@$sensus[$bar['tahun'].'-'.$bar['bulan']]+=$bar['kgsensus'];
			}
		}
		
		// echo"<pre>";
		// print_r($sensus);
		// echo"</pre>";
		
		
		#
		$str=" select sum(jjgpanen) as jjg,substr(tanggal,1,7) as bulan,sum(kgkebun) as kgkebun
		from  ".$dbname.".kebun_rekappnn_vw where divisi like '".$kdorg."%' and tanggal between '".$tglawal."' and  '".$tgl2."' 
				group by bulan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jjgk[$bar['bulan']]=$bar['jjg'];
			$realk[$bar['bulan']]=$bar['kgkebun'];
			
		}
				
		
		
		$arrbln=month_inbetween($perawal, $perakhir);
		
		foreach($arrbln as $bln)
		{
			$expbln=explode("-",$bln);
			$tab.="
				<tr class=rowcontent>
					<td align=center>".numToMonth($expbln[1],'I','short')."</td>
					<td align=right>".@number_format($jjgk[$bln])."</td>
					<td align=right>".@number_format($jjgp[$bln])."</td>
					<td align=right>".@number_format($realk[$bln]/@$jjgk[$bln],2)."</td>
					<td align=right>".@number_format($realp[$bln]/@$jjgp[$bln],2)."</td>
					<td align=right>".@number_format($realk[$bln]/1000)."</td>
					<td align=right>".@number_format($realp[$bln]/1000)."</td>
					<td align=right>".@number_format($sensus[$bln]/1000)."</td>
					<td align=right>".@number_format($bgt[$bln]/1000)."</td>
				</tr>
				";	
				@$tjjgk+=$jjgk[$bln];
				@$tjjgp+=$jjgp[$bln];
				@$trealk+=$realk[$bln];
				@$trealp+=$realp[$bln];
				@$tbgt+=$bgt[$bln];
				@$tsensus+=$sensus[$bln];
				
		}
		$tab.="
				<tr class=rowcontent>
					
					<td align=center>TOTAL</td>
					<td align=right>".@number_format($tjjgk)."</td>
					<td align=right>".@number_format($tjjgp)."</td>
					<td align=right>".@number_format($trealk/@$tjjgk,2)."</td>
					<td align=right>".@number_format($trealp/@$tjjgp,2)."</td>
					<td align=right>".@number_format($trealk/1000)."</td>
					<td align=right>".@number_format($trealp/1000)."</td>
					<td align=right>".@number_format($tsensus/1000)."</td>
					<td align=right>".@number_format($tbgt/1000)."</td>
				</tr>
				";	
		
		// echo"<pre>";
		// print_r($jjgk);
		// echo"</pre>";
		
	
	echo $tab;
	
	break;
	
	
	case'prev2':
	
	
	
		$str=" select sum(kgwb) as kgwb,substr(tanggal,1,7) as bulan,substr(tanggal,6,2) as bln from ".$dbname.".kebun_spb_vw 
				where tanggal between '".$tglawal."' and  '".$tgl2."'   and kodeorg = '".$kdorg."' group by bulan order by bulan asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			//$nor++;
		
			if(substr($bar['bln'],0,1)==0){		
				$real[substr($bar['bln'],1,1)-1]=$bar['kgwb']/1000;	
			}
			else{
				$real[$bar['bln']-1]=$bar['kgwb']/1000;	
			}
			
		}
		if($row==0){
			$real[0]=0;
		}
		
		
		$str=" select sum(kg01) as kg01,sum(kg02) as kg02,sum(kg03) as kg03,sum(kg04) as kg04,sum(kg05) as kg05,sum(kg06) as kg06,
					  sum(kg07) as kg07,sum(kg08) as kg08,sum(kg09) as kg09,sum(kg10) as kg10,sum(kg11) as kg11,sum(kg12) as kg12,
					  tahunbudget,kodeunit
			 from ".$dbname.".bgt_produksi_kbn_kg_vw 
			 where kodeunit = '".$kdorg."' and tahunbudget='".$tahun."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			for($i=1;$i<=12;$i++)
			{
				if($i<10)
				{
					$bgt[$i-1]=$bar['kg0'.$i]/1000;
				}
				else
				{
					$bgt[$i-1]=$bar['kg'.$i]/1000;
				}
			}
		}
		if($row==0){
			$bgt[0]=0;
		}
		
	

		$sensus=array();
		$str=" select * from ".$dbname.".kebun_rencanapanen_vw where tahun='".$tahun."' and kodeorg like '".$kdorg."%' ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$sensus[$bar['bulan']-1]+=$bar['kgsensus']/1000;
		}
		if($row==0){
			$sensus[0]=0;
		}
		

	
		$graph = new Graph(550,255,'auto');    
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,30,40,40);
		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
		 
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']." ".$tahun);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		if($_SESSION['language']=='EN'){
			$graph->title->Set('PRODUCTION ACHIEVEMENT GRAPH');
		}else{ 
			$graph->title->Set('GRAFIK PENCAPAIAN PRODUKSI');
		}
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		 
		$bplot1 = new BarPlot($bgt);
		$bplot3 = new BarPlot($real);
		$bplot2 = new BarPlot($sensus);
	
		 
		$bplot1->SetFillColor("blue");
		$bplot3->SetFillColor("red");
		$bplot2->SetFillColor("green");
		
		
		
		 
		// Set the legends for the plots
		$bplot1->SetLegend($_SESSION['lang']['budget']);
		$bplot3->SetLegend($_SESSION['lang']['realisasi']);
		$bplot2->SetLegend($_SESSION['lang']['sensus']);
		
		 
		// Adjust the legend position
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->legend->Pos(0.5,0.92,"center","bottom");
		
		
		 
		// $bplot1->SetShadow();
		// $bplot2->SetShadow();
	
		 
		// $bplot1->SetShadow();
		// $bplot2->SetShadow();
	
		 
		$gbarplot = new GroupBarPlot(array($bplot1,$bplot2,$bplot3));
		$gbarplot->SetWidth(0.5);
		$graph->Add($gbarplot);
		 
		$graph->StrokeCSIM();
	
	
  
  echo $tab;
	
	break;
	
	case'prev1':
	
	$stream = "<table class=sortable cellspacing=1 width=100%>";
	$stream.="
    <thead>
        <tr class=rowheader>
			<th align='center' width='52' rowspan=2>".$_SESSION['lang']['kebun']."</th>
			<th align='center' colspan=3>".$_SESSION['lang']['hslTimbangan']." (Ton)</th>
			<th align='center' colspan=3>".$_SESSION['lang']['sensus']." (Ton)</th>
			<th align='center' colspan=3>".$_SESSION['lang']['budget']." (Ton)</th>
			<th align='center' colspan=4>".$_SESSION['lang']['pencapaian']." (%)</th>
		</tr>
		<tr>
			<th align='center' width='60' > ".$_SESSION['lang']['hi']."</th>
			<th align='center' width='75' > ".$_SESSION['lang']['bi']."</th>
			<th align='center' width='85' > ".$_SESSION['lang']['sbi']."</th>
			
			<th align='center' width='75' > ".$_SESSION['lang']['bi']."</th>
			<th align='center' width='85' > ".$_SESSION['lang']['sbi']."</th>
			<th align='center' width='95' > ".$_SESSION['lang']['setahun']."</th>
			
			<th align='center' width='75' > ".$_SESSION['lang']['bi']."</th>
			<th align='center' width='85' > ".$_SESSION['lang']['sbi']."</th>
			<th align='center' width='95' > ".$_SESSION['lang']['setahun']."</th>
			
			<th align='center' width='70' > Real ".$_SESSION['lang']['bi']." vs ".$_SESSION['lang']['sensus']." ".$_SESSION['lang']['bi']."</th>
			<th align='center' width='70' > Real ".$_SESSION['lang']['bi']." vs ".$_SESSION['lang']['busget']." ".$_SESSION['lang']['bi']."</th>
			<th align='center' width='70' > Real ".$_SESSION['lang']['sbi']." vs ".$_SESSION['lang']['sensus']."</th>
			<th align='center' width='70' > Real ".$_SESSION['lang']['sbi']." vs ".$_SESSION['lang']['budget']." ".$_SESSION['lang']['setahun']."</th>
		</tr>
		</thead>";
		
	
	$str=" select * from ".$dbname.".kebun_spb_vw where tanggal = '".$tgl2."' and kodeorg = '".$kdorg."'  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$timhi+=$bar['kgwb'];
	}
	
	$str=" select * from ".$dbname.".kebun_spb_vw where tanggal between '".$tgl1."' and  '".$tgl2."' and kodeorg = '".$kdorg."'  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$timbi+=$bar['kgwb'];
	}
	
	$str=" select * from ".$dbname.".kebun_spb_vw where tanggal between '".$tglawal."' and  '".$tgl2."'   and kodeorg = '".$kdorg."'  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$timsbi+=$bar['kgwb'];
	}
	
		
	#sdbi
	$addstr="(";
	for($i=1;$i<=intval($blnbgt);$i++)
	{
		if($i<10)
		{
			$isi="kg0".$i;
		}
		else 
		{
			$isi="kg".$i;
		}
		if($i<intval($blnbgt))
		{
			$addstr.=$isi."+";
		}
		else
		{
			$addstr.=$isi;
		}
	}
	$addstr.=")";

	$addstrthn="(";
	for($i=1;$i<=12;$i++)
	{
		if($i<10)
		{
			$isi="kg0".$i;
		}
		else 
		{
			$isi="kg".$i;
		}
		if($i<12)
		{
			$addstrthn.=$isi."+";
		}
		else
		{
			$addstrthn.=$isi;
		}
	}
	$addstrthn.=")";

	$str=" select kg".$blnbgt." as bi,tahunbudget,kodeunit,divisi,kodeblok,thntnm,".$addstr." as sbi,".$addstrthn." as thn "
			. " from ".$dbname.".bgt_produksi_kbn_kg_vw "
			. " where kodeunit = '".$kdorg."' and tahunbudget='".$tahun."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$bgtbi+=$bar['bi'];
		@$bgtsbi+=$bar['sbi'];
		@$bgtthn+=$bar['thn'];
	}
	
	
	##sensus
	#thn
	$str=" select * from ".$dbname.".kebun_rencanapanen_vw where tahun='".$tahun."' and kodeorg like '".$kdorg."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$sensusthn+=$bar['kgsensus'];
	}
	
	#bi 
	$str=" select * from ".$dbname.".kebun_rencanapanen_vw where tahun='".$tahun."' and kodeorg like '".$kdorg."%' and bulan=".intval($blnbgt)." ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$sensusbi+=$bar['kgsensus'];
	} 

	#sdbi 
	$str=" select * from ".$dbname.".kebun_rencanapanen_vw where tahun='".$tahun."' and kodeorg like '".$kdorg."%' and bulan between 1 and ".intval($blnbgt)." ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$sensussdbi+=$bar['kgsensus'];
	} 
		
		
	$stream.="
		<tr class=rowcontent>
			<td align='center'>".$kdorg."</td>
			<td align=right style=cursor:pointer; title='click detail' onclick=detailpengirmanblok('".$kdorg."','".tanggalnormal($tgl2)."','hari','html','event')>".@number_format($timhi/1000,2)."</td>
			<td align=right style=cursor:pointer; title='click detail' onclick=detailpengirmanblok('".$kdorg."','".tanggalnormal($tgl2)."','bulan','html','event')>".@number_format($timbi/1000,2)."</td>
			<td align=right>".@number_format($timsbi/1000,2)."</td>
			
			<td align=right>".@number_format($sensusbi/1000,2)."</td>
			<td align=right>".@number_format($sensussdbi/1000,2)."</td>
			<td align=right>".@number_format($sensusthn/1000,2)."</td>
			
			<td align=right>".@number_format($bgtbi/1000,2)."</td>
			<td align=right>".@number_format($bgtsbi/1000,2)."</td>
			<td align=right>".@number_format($bgtthn/1000,2)."</td>
			
			<td align=right>".@number_format($timbi/$sensusbi*100,2)."</td>
			<td align=right>".@number_format($timhi/$bgtbi*100,2)."</td>
			<td align=right>".@number_format($timsbi/$sensussdbi*100,2)."</td>
			<td align=right>".@number_format($timsbi/$bgtthn*100,2)."</td>			
		</tr>

			";
		
	echo $stream;
	
	break;
	
	
	case'detailpengirmanblok':
	
	if($tipe=='hari')
	{
		$sort=" and tanggal='".$tgl2."'";
	}
	else
	{
		$sort=" and tanggal between '".$tgl1."' and '".$tgl2."' ";
	}
	
	$str="select * from ".$dbname.".kebun_spb_vw where kodeorg='".$kdorg."' ".$sort." ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$kddivisi[$bar['divisi']]=$bar['divisi'];
		$kdblok[$bar['blok']]=$bar['blok'];
		$listkdblok[$bar['divisi']][$bar['blok']]=$bar['blok'];
		$tt[$bar['divisi']][$bar['blok']]=$bar['tahuntanam'];
		$jjg[$bar['divisi']][$bar['blok']]+=$bar['jjg'];
		$kg[$bar['divisi']][$bar['blok']]+=$bar['kgwb'];
		
		$nospb[$bar['nospb']]=$bar['nospb'];
		$listnospb[$bar['divisi']][$bar['nospb']]=$bar['nospb'];
		$jjgspb[$bar['divisi']][$bar['nospb']]+=$bar['jjg'];
		$kgspb[$bar['divisi']][$bar['nospb']]+=$bar['kgwb'];
		$tgl[$bar['divisi']][$bar['nospb']]=$bar['tanggal'];
	}
	
	array_multisort($kddivisi,SORT_ASC);
	array_multisort($kdblok,SORT_ASC);
	array_multisort($tgl,SORT_ASC);
	array_multisort($nospb,SORT_ASC);
	
	$stream="";
	
	$stream.="<script language=javascript src=js/zTools.js></script>";
	$stream.="<script language=javascript src='js/zReport.js'></script>";
	$stream.="<script language=javascript src=js/zMaster.js></script>";


if($print=='excel')
{
	$border="border=1";
}
else
{
        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }   	
	$stream.="<link rel=stylesheet type=text/css href=style/zTable.css>";
	$stream.="<link rel=stylesheet type=text/css href=style/".$gen.">";
	$border="border=0";
}
	
	
	$stream.="<img 
			style=cursor:pointer; title='click detail' 
			onclick=detailpengirmanblokexcel('".$kdorg."','".tanggalnormal($tgl2)."','".$tipe."','excel','event') src=images/excel.jpg  
			title='MS.Excel' > ";
	
	$stream.="<br>";
	$stream.="<fieldset style='float:left'>";
	$stream.="<legend>".strtoupper("REKAP PENGIRIMAN PERBLOK PER-".$tipe)."</legend>";
	$stream.="<table cellpadding=1 ".$border." cellspacing=1 class=sortable width=410px>";
	$stream.="<thead>";
	$stream.="<tr>";
	$stream.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['divisi']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['blok']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['tahuntanam']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['jjg']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['kg']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['bjr']."</th>";
	$stream.="</tr>";
	$stream.="</thead>";
	
	foreach($kddivisi as $divisi)
	{
		foreach($kdblok as $blok)
		{
			if($listkdblok[$divisi][$blok]!='')
			{
				@$no+=1;//(blok,tgl2,tipe,ev)
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$divisi."</td>";
				$stream.="<td style=cursor:pointer; title='click detail' onclick=detailspb('".$blok."','".tanggalnormal($tgl2)."','".$tipe."','event')>".$blok."</td>";
				$stream.="<td>".$tt[$divisi][$blok]."</td>";
				$stream.="<td align=right>".number_format($jjg[$divisi][$blok])."</td>";
				$stream.="<td align=right>".number_format($kg[$divisi][$blok],2)."</td>";
				$stream.="<td align=right>".number_format($kg[$divisi][$blok]/$jjg[$divisi][$blok],2)."</td>";
				$stream.="</tr>";
				
				$stjjg[$divisi]+=$jjg[$divisi][$blok];
				$stkg[$divisi]+=$kg[$divisi][$blok];
				
				
			}
		}
		$stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=4>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['divisi']."  ".$divisi."</td>
                    <td align=right>".@number_format($stjjg[$divisi])."</td>
					<td align=right>".@number_format($stkg[$divisi],2)."</td>
					<td align=right>".@number_format($stkg[$divisi]/$stjjg[$divisi],2)."</td>
				</tr>";
				$gtjjg+=$stjjg[$divisi];
				$gtkg+=$stkg[$divisi];
	}
	$stream.="
        <tr bgcolor=#48D1CC>
            <td align=left colspan=4>".$_SESSION['lang']['grnd_total']." ".$kdorg."</td>
            <td align=right>".@number_format($gtjjg)."</td>
			<td align=right>".@number_format($gtkg,2)."</td>
			<td align=right>".@number_format($gtkg/$gtjjg,2)."</td>
		</tr>";
	$stream.="</table></fieldset>";
	
	
	##fieldset kanan
	$stream.="<fieldset style='float:left'>";
	$stream.="<legend>".strtoupper("REKAP PENGIRIMAN SPB PER-".$tipe)."</legend>";
	$stream.="<table ".$border." cellpadding=1 cellspacing=1 class=sortable  width=430px>";
	$stream.="<thead>";
	$stream.="<tr>";
	$stream.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['divisi']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['nospb']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['jjg']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['kg']."</th>";
	$stream.="<th align=center>".$_SESSION['lang']['bjr']."</th>";
	$stream.="</tr>";
	$stream.="</thead>";
	
	foreach($kddivisi as $divisi)
	{
		foreach($nospb as $spb)
		{
			if($listnospb[$divisi][$spb]!='')
			{
				@$nos+=1;
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$nos."</td>";
				$stream.="<td>".$divisi."</td>";
				$stream.="<td style=cursor:pointer; title='click untuk melihat PDF SPB' onclick=masterPDF2('kebun_spbht','".$spb."','','kebun_spbPdf',event)>".$spb."</td>";
				$stream.="<td>".tanggalnormal($tgl[$divisi][$spb])."</td>";
				$stream.="<td align=right>".number_format($jjgspb[$divisi][$spb])."</td>";
				$stream.="<td align=right>".number_format($kgspb[$divisi][$spb],2)."</td>";
				$stream.="<td align=right>".number_format($kgspb[$divisi][$spb]/$jjgspb[$divisi][$spb],2)."</td>";
				$stream.="</tr>";
				
				$stjjgspb[$divisi]+=$jjgspb[$divisi][$spb];
				$stkgspb[$divisi]+=$kgspb[$divisi][$spb];
				
				
			}
		}
		$stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=4>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['divisi']."  ".$divisi."</td>
                    <td align=right>".@number_format($stjjgspb[$divisi])."</td>
					<td align=right>".@number_format($stkgspb[$divisi],2)."</td>
					<td align=right>".@number_format($stkgspb[$divisi]/$stjjgspb[$divisi],2)."</td>
				</tr>";
				$gtjjgspb+=$stjjgspb[$divisi];
				$gtkgspb+=$stkgspb[$divisi];
	}
	$stream.="
        <tr bgcolor=#48D1CC>
            <td align=left colspan=4>".$_SESSION['lang']['grnd_total']." ".$kdorg."</td>
            <td align=right>".@number_format($gtjjgspb)."</td>
			<td align=right>".@number_format($gtkgspb,2)."</td>
			<td align=right>".@number_format($gtkgspb/$gtjjgspb,2)."</td>
		</tr>";
	$stream.="</table></fieldset>";
	
	// $stream.="";
	// $stream.="";
	// $stream.="";
	
	
if($print=='excel')
{
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="detail_transaksi".$kodeorg._.$noakun._.$per;
    if(strlen($stream)>0)
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
    
	}
else
{
   echo $stream;
} 

		
break;



case'detailspb':
//".$border."
$stream="<fieldset style=height:93%><table border=0 class=sortable cellspacing=1 style=width:100%>
		 <thead>
			<tr class=rowheader>
				  <th align=center>".$_SESSION['lang']['nospb']."</th>    
				  <th align=center>".$_SESSION['lang']['tanggal']."</th>    
				  <th align=center>".$_SESSION['lang']['nokendaraan']."</th>
				  <th align=center>".$_SESSION['lang']['blok']."</th> 
				  <th align=center>".$_SESSION['lang']['jjg']."</th> 
				  <th align=center>".$_SESSION['lang']['kg']."</th>  
				  <th align=center>".$_SESSION['lang']['pabrik']." ".$_SESSION['lang']['tujuan']."</th> 
				</tr>  
		 </thead>";
if($tipe=='hari')
{
	$sort=" and tanggal='".$tgl2."'";
}
else
{
	$sort=" and tanggal between '".$tgl1."' and '".$tgl2."' ";
}
$str="select * from ".$dbname.".kebun_spb_vw where blok='".$blok."' ".$sort." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$no+=1;
	$stream.="<tr class=rowcontent>
		<td align=left>".$bar['nospb']."</td>   
		<td align=left>".tanggalnormal($bar['tanggal'])."</td>   
		<td align=left>".$bar['nokendaraan']."</td>   
		<td align=left>".$bar['blok']."</td>   
		<td align=left>".number_format($bar['jjg'])."</td>   
		<td align=right>".number_format($bar['kgwb'],2)."</td>   
		<td align=left>".$bar['penerimatbs']."</td>   	
	 </tr></fieldset>"; 
}			 
				 
				 
				 echo $stream;

break;

}
?>