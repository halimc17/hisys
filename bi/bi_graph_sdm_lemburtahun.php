<?php // content="text/plain; charset=utf-8"
include('master_validation.php');
include('../config/connection.php');
include('../lib/nangkoelib.php');
require_once ('../jpgraph/jpgraph.php');
require_once ('../jpgraph/jpgraph_pie.php');
require_once ('../jpgraph/jpgraph_pie3d.php');
include('../lib/zLib.php');

$pt = checkPostGet('pt','');
$thn = checkPostGet('thn','');
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$jenis = checkPostGet('jenis','');
$detailPt = checkPostGet('detailPt','');


$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

switch($method){
	
	case'detailgraph':
		// $stylehidden = "style='display:none'";	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";

		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['unit']."</td>
						<td align=center>".$_SESSION['lang']['tahun']."</td>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>
					</tr>
				</thead>
				";
		
		
		$str="select kodeorganisasi,induk from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumunit[$bar['induk']]+=1;
		}

		$str="select kodeorganisasi,induk from ".$dbname.".organisasi where length(kodeorganisasi)='6' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumdivisi[$bar['induk']]+=1;
		}	
		
		
		
		$str="select uangkelebihanjam,left(kodeorg,4) as kodeorg,left(kodeorg,6) as divisi,induk from ".$dbname.".sdm_lemburdt a left join 
				".$dbname.".organisasi b on left(a.kodeorg,4)=b.kodeorganisasi where left(tanggal,4) = '".$thn."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			$listkddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']]=$bar['divisi'];
			@$prodpt[$bar['induk']][$bar['tahuntanam']]+=$bar['uangkelebihanjam'];
			@$produnit[$bar['induk']][$bar['kodeorg']][$bar['tahuntanam']]+=$bar['uangkelebihanjam'];
			@$proddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']][$bar['tahuntanam']]+=$bar['uangkelebihanjam'];
			@$prodtot[$bar['tahuntanam']]+=$bar['uangkelebihanjam'];
		}


		if(empty($kodept)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
				
		foreach($kodept as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt." - ".$nmorg[$pt]."</td>
					<td>".$thn."</td>		
					<td align=right>".@number_format($prodpt[$pt][$tt])."</td></tr>";
			
			$urutunitlist=0;
				foreach($kodeunit as $unit){
					if(@$listkodeunit[$pt][$unit]==$unit){
						@$urutunit+=1;
						$urutunitlist++;
						$form.="
						<tr  class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunitlist." onclick=\"detailunit('".$urutunit."','".$jumdivisi[$unit]."')\">
							<td>".$no.".".$urutunitlist."</td>
							<td>".$unit." - ".$nmorg[$unit]."</td>
							<td>".$thn."</td>
							<td align=right>".@number_format($produnit[$pt][$unit][$tt])."</td>
						</tr>";	
					$urutdivisilist=0;
					foreach($kddivisi as $divisi){
						if(@$listkddivisi[$pt][$unit][$divisi]==$divisi){
							$urutdivisilist++;
							$form.="
							<tr class=rowcontentdetail style='display:none'  id=divisilist".$urutunit."".$urutdivisilist.">
								<td>".$no.".".$urutunitlist.".".$urutdivisilist."</td>
								<td>".$divisi." - ".$nmorg[$divisi]."</td>
								<td>".$thn."</td>
								<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi][$tt])."</td>
							</tr>";	
							
						}
					}
				}
			}
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=3 align=center><b>Total</td>
					<td align=right><b>".@number_format($prodtot[$tt])."</td>
				</tr></table>
			";		
				
		
		echo $form;
		
	break;
	
	
	
	
	
	case'global':
		
		if($pt!=''){
			$sortp="and kodeorg in (select kodeorganisasi  from ".$dbname.".organisasi where induk='".$pt."') ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}else{
			$sortp="";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
		 
		$text='';
		$sData="select sum(lembur) as lembur,induk from ".$dbname.".sdm_lembur_vw a left join 
				".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where periode like '".$thn."%'
				".$sortp."
				group by b.induk order by b.induk asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while ($rData = $qData->fetch()){
			$lbl[] =$rData['induk']." \n%.1f%%";
            $data[]=$rData['lembur'];
            $text.=$rData['induk']." : Rp. ".number_format($rData['lembur'],0)." \n";
            $targ[]=$_SERVER['PHP_SELF']."?thn=".$thn."&pt=".$pt."&detailPt=".$rData['induk']."&method=detailperpt";
            $alts[]='Click to drill';  
		}
		
		if(empty($lbl)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		$graph = new PieGraph(580,240);
		$graph->title->SetMargin(2); // Add a little bit more margin from the top
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 

		$p1 = new PiePlot3d($data);
		$p1->SetSize(0.5);
        $p1->SetCenter(0.5);
        $p1->SetAngle(40);
        $p1->value->SetColor('black');

        $p1->SetLabelType(PIE_VALUE_PER);
        $p1->SetLabels($lbl);
        $p1->SetLabelPos(1); 
        // Add drop shadow to slices
        $p1->SetShadow();
		$p1->ExplodeAll(20);
		// Setup the CSIM targets
		$p1->SetCSIMTargets($targ,$alts);
		       // Setup a small help text in the image
		// $txt = new Text($text);
		// $txt->SetFont(FF_FONT2,FS_BOLD,2);
		// $txt->SetPos(0.33,0.65,'left','bottom');
		// $txt->SetBox('lightyellow');
		//$txt->SetPos(0.4,0.6,'left','left');
		//$txt->SetBox('white','black');
		// $txt->SetShadow();
		// $graph->AddText($txt);
		$graph->Add($p1);

        if(count($data)!=0){
        	$graph->StrokeCSIM();       	
        }else{
        	echo $_SESSION['lang']['dataempty'];
        }
        //echo "<a href='".$_SERVER['PHP_SELF']."?thn=".$thn."&pt=''&jenis=global'>[ Back ]</a>";   
	break;
	
	case'detailperpt':
		$text='';
		$nmpt=$_SESSION['lang']['all']."";
		if($pt!=''){
			$sortp="and kodeorg in (select kodeorganisasi  from ".$dbname.".organisasi where induk='".$pt."') ";
			$nmpt="PT ". $pt;
		}
	
		$sData="select sum(lembur) as lembur,a.kodeorg as kodeorg from ".$dbname.".sdm_lembur_vw a left join 
				".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where periode like '".$thn."%'
				and b.induk='".$detailPt."' group by a.kodeorg order by b.induk asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while ($rData = $qData->fetch()){
			$lbl[] =$rData['kodeorg']." \n%.1f%%";
            $data[]=$rData['lembur'];
            $text.=$rData['kodeorg']." : Rp. ".number_format($rData['lembur'],0)." \n";
            $targ[]=$_SERVER['PHP_SELF']."?thn=".$thn."&unit=".$rData['kodeorg']."&method=detailunit&pt=".$pt."&detailPt=".$detailPt;
            $alts[]='Click to drill';  
		}
		
		$graph = new PieGraph(580,220);
		$graph->title->SetMargin(2); // Add a little bit more margin from the top
		
		$graph->title->Set('PT. '.$detailPt);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		
		$p1 = new PiePlot3d($data);
		$p1->SetSize(0.5);
        $p1->SetCenter(0.5);
        $p1->SetAngle(20);
        $p1->value->SetColor('black');

        $p1->SetLabelType(PIE_VALUE_PER);
        $p1->SetLabels($lbl);
        $p1->SetLabelPos(1); 
        // Add drop shadow to slices
        $p1->SetShadow();
		$p1->ExplodeAll(20);
		// Setup the CSIM targets
		$p1->SetCSIMTargets($targ,$alts);
		//        // Setup a small help text in the image
		// $txt = new Text($text);
		// $txt->SetFont(FF_FONT2,FS_BOLD,2);
		// $txt->SetPos(0.33,0.65,'left','bottom');
		// $txt->SetBox('lightyellow');
		// //$txt->SetPos(0.4,0.6,'left','left');
		// //$txt->SetBox('white','black');
		// $txt->SetShadow();
		// $graph->AddText($txt);
			$graph->Add($p1);

        // .. and send the image on it's marry way to the browser
		$graph->StrokeCSIM();  
		echo "<a href='".$_SERVER['PHP_SELF']."?thn=".$thn."&pt=".$pt."&method=global'>[ Back ]</a>";           
	break;
	
	
	case'detailunit':
		$text='';
		$sData="select sum(uangkelebihanjam) as lembur,kodeorg from ".$dbname.".sdm_lemburdt 
				where left(tanggal,4)='".$thn."' and kodeorg like '".$unit."%'
				group by kodeorg order by kodeorg asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while ($rData = $qData->fetch()){
			$lbl[] =$rData['kodeorg']." \n%.1f%%";
            $data[]=$rData['lembur'];
            $text.=$rData['kodeorg']." : Rp. ".number_format($rData['lembur'],0)." \n";
            $targ[]="";
            $alts[]="";  
		}
		
		$graph = new PieGraph(580,220);
		$graph->title->SetMargin(2); // Add a little bit more margin from the top
		
		//$graph->title->SetFont(FF_FONT1,FS_BOLD);
		
		$graph->title->Set($_SESSION['lang']['unit'].' '.$unit);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		

		$p1 = new PiePlot3d($data);
		$p1->SetSize(0.5);
        $p1->SetCenter(0.5);
        $p1->SetAngle(20);
        $p1->value->SetColor('black');

        $p1->SetLabelType(PIE_VALUE_PER);
        $p1->SetLabels($lbl);
        $p1->SetLabelPos(1); 
        // Add drop shadow to slices
        $p1->SetShadow();
		$p1->ExplodeAll(20);
		// Setup the CSIM targets
		$p1->SetCSIMTargets($targ,$alts);
		//        // Setup a small help text in the image
		// $txt = new Text($text);
		// $txt->SetFont(FF_FONT2,FS_BOLD,2);
		// $txt->SetPos(0.33,0.65,'left','bottom');
		// $txt->SetBox('lightyellow');
		// //$txt->SetPos(0.4,0.6,'left','left');
		// //$txt->SetBox('white','black');
		// $txt->SetShadow();
		// $graph->AddText($txt);
			$graph->Add($p1);

        // .. and send the image on it's marry way to the browser
		$graph->StrokeCSIM();  
		echo "<a href='".$_SERVER['PHP_SELF']."?thn=".$thn."&detailPt=".$detailPt."&pt=".$pt."&method=detailperpt'>[ Back ]</a>";           
	break;
}



?>