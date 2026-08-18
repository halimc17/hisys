<?
include('../config/connection.php');
include('../lib/nangkoelib.php');
//include('master_validation.php');
include('lib/zLib.php');
//header('Content-Type: image/svg+xml');

//get warna
$str="select * from ".$dbname.".bi_5warna";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$fill[$bar['tipe']]=$bar['fill'];			
	$line[$bar['tipe']]=$bar['line'];	
	$width[$bar['tipe']]=$bar['width'];	
}

$att = array(
	'transform' => 'matrix(1.5,0,0,1.5,423.8499755859375,0)',
	'class' => 'svg-pan-zoom_viewport'
);
$result = array(
	'element' => 'g', 
	'attribute' => $att,
	'child' => array()
);


$firstTipe = "";
$att1 = array();
$d1 = array();
$str = "select a.*,b.tipefeature from ".$dbname.".bi_map_basic a 
left join ".$dbname.".bi_5tipepeta b on a.tipepeta = b.id_tipepeta
order by a.tipepeta";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
   if($firstTipe != $bar['tipepeta']){
	   if($firstTipe != ""){
		 $result['child'][] = $d1; 
	   }
	   
	   $att1 = array();
	   $d1 = array();
	   
	   $firstTipe = $bar['tipepeta'];
	   $d1['element'] = 'g';
	   $att1['id'] = 'SVGLOCATION_'.$firstTipe;
	   $d1['attribute'] = $att1;
   }
   $tipefeature = $bar['tipefeature'];
   $expTitle = explode('##', $bar['keterangan']);
   $d_dt = array();
   $d_dtC = array();
	if($tipefeature == 'path'){
		$x = count($bar['path']);
		$d_dt['element'] = 'path';
		$d_dt['attribute']['id'] = $bar['idsvg'];
		$d_dt['attribute']['class'] = 'pathhover';
		$d_dt['attribute']['d'] = $x;
		$d_dt['attribute']['alt'] = $expTitle[0];
		$d_dt['attribute']['title'] = $expTitle[0];
		$d_dt['attribute']['fill'] = @$fill[$firstTipe];
		$d_dt['attribute']['style'] = 'stroke:'.@$line[$firstTipe].';cursor:default;';
		
		$d_dtC['element'] = 'title';
		$d_dtC['text'] = @$expTitle[0];
		$d_dt['child'][] = $d_dtC;
	}else{
		$pieces = explode(',', $bar['path']);
		$d_dt['element'] = 'circle';
		$d_dt['attribute']['id'] = $bar['tipepeta'];
		$d_dt['attribute']['class'] = 'non-scaling';
		$d_dt['attribute']['transform'] = 'translate('.$pieces[0].','.$pieces[1].')';
		$d_dt['attribute']['title'] = $expTitle[0];
		$d_dt['attribute']['fill'] = @$fill[$firstTipe];;
		$d_dt['attribute']['r'] = @$width[$firstTipe];
		$d_dt['attribute']['style'] = 'cursor:default';
		
		$d_dtC['element'] = 'title';
		$d_dtC['text'] = $expTitle[0];
		$d_dt['child'][] = $d_dtC;
		
	}
	$d1['child'][] = $d_dt;
}	
$result['child'][] = $d1; 				
				
echo json_encode($result);
/*
echo'<g transform="matrix(1.5,0,0,1.5,423.8499755859375,0)" class="svg-pan-zoom_viewport">';
		
		while($resTipe=$qryTipe->fetch()){
			if($resTipe['id_tipepeta'] == $firstTipe){
				$str = "select * from ".$dbname.".bi_map_basic where tipepeta = '".$firstTipe."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				echo "<g id='SVGLOCATION_".$firstTipe."'>";
				echo'<desc>Layer '.$firstTipe.'</desc>';
				
				while($bar = $res->fetch()){
					$tipefeature = $resTipe['tipefeature'];
					$expTitle = explode('##', $bar['keterangan']);
					
					if($tipefeature == 'path'){
						if($bar['tipepeta'] == $firstTipe){
							$style = "style='fill:".$fill[$resTipe['id_tipepeta']].";stroke:".$line[$resTipe['id_tipepeta']].";cursor:default'";
						}else{
							$style = "style='fill:".$fill[$resTipe['id_tipepeta']].";stroke:".$line[$resTipe['id_tipepeta']]."stroke-width:".$width['id_tipepeta'].";stroke-linejoin:round;cursor:default;' vector-effect='non-scaling-stroke'";
						}				
						// echo "<path id='".$bar['idsvg']."' d='".$bar['path']."' title='".$expTitle[0]."' ".$style." onclick=\"showinfosvg('".$bar['idsvg']."',0,'event')\" />";
						// echo "<path id='".$bar['idsvg']."' d='".$bar['path']."' title='".$expTitle[0]."' ".$style." onmousedown=\"isClicked=false;\" onmousemove=\"isClicked = true;\" onmouseup=\"showinfosvg('".$bar['idsvg']."',0,'event')\" />";
						echo "<path class='pathhover' id='".$bar['idsvg']."' d='".$bar['path']."' ".$style." alt='".$expTitle[0]."' title='".$expTitle[0]."'><title>".$expTitle[0]."</title></path>";
					}else{
						$pieces = explode(',', $bar['path']);
						// echo "<circle class='non-scaling' transform='translate(".$pieces[0].",".$pieces[1].")' title='".$expTitle[0]."' id='".$bar['tipepeta']."' fill='".$fill['id_tipepeta']."' r='".$width['id_tipepeta']."' onmousedown=\"isClicked=false;\" onmousemove=\"isClicked = true;\" onmouseup=\"showinfosvg('".$bar['idsvg']."',0,'event')\" />";
						echo "<circle class='non-scaling' transform='translate(".$pieces[0].",".$pieces[1].")' title='".$expTitle[0]."' id='".$bar['tipepeta']."' fill='".$fill['id_tipepeta']."' r='".$width['id_tipepeta']."' style='cursor:default'><title>".$expTitle[0]."</title></circle>";
					}
				}
				echo"</g>";
			}else{
				echo "<g id='SVGLOCATION_".$resTipe['id_tipepeta']."'></g>";
			}
		}
		
		echo "<g id=svgPt></g>";
		echo "<g id=svgDetail></g>";
		echo "<g id=svgTracking></g>";
		echo"</g>";



 
//Get Master Warna
		$str="select * from ".$dbname.".bi_5warna";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$fill[$bar['tipe']]=$bar['fill'];			
			$line[$bar['tipe']]=$bar['line'];	
			$width[$bar['tipe']]=$bar['width'];	
		}
		
		$str = "select * from ".$dbname.".bi_map_pt where kodeorg = 'LMD' and unit = 'KJNE' order by tipepeta asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = "";
		$pointx = "";
		$pointy = "";
		$result = "";
		while($bar = $res->fetch()){
			if($no != "" && $no != $bar['tipepeta']){
				$result .= "</g>";
			}
			
			if($no == "" || $no != $bar['tipepeta']){
				$pointx1 = $bar['viewbox'];
				$pointx1 = explode(' ', $pointx1);
				$pointx = ($pointx1[0] + ($pointx1[2] / 2));
				$pointy = ($pointx1[1] + ($pointx1[3] / 2));
				if($bar['tipepeta'] == $firstPT || $bar['tipepeta'] == $textBlok){
					$vDisplay = '';
				}else{
					$vDisplay = 'none';
				}
				
				$result .= "<g id='".$bar['tipepeta']."' style='display:".$vDisplay."'>";
				$result .= '<desc>Layer '.$bar['tipepeta'].'</desc>';
				$no = $bar['tipepeta'];
			}
			
			if($fill[$bar['tipepeta']]==''){
				$fill[$bar['tipepeta']]='none';
			}else{
				$fill[$bar['tipepeta']]=$fill[$bar['tipepeta']];
			}
			
			if($line[$bar['tipepeta']]==''){
				$line[$bar['tipepeta']]=='none';
			}else{
				$line[$bar['tipepeta']]==$line[$bar['tipepeta']];
			}
			
			if($width[$bar['tipepeta']]==''){
				$width[$bar['tipepeta']]=0.05;
			}else{
				$width[$bar['tipepeta']]=$width[$bar['tipepeta']];
			}
			
			$str2 = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$bar['tipepeta']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$tipefeature = $bar2['tipefeature'];
			$expTitle = explode('##', $bar['keterangan']);
			
			if($tipefeature == 'path'){
				$style = "style='fill:".$fill[$bar['tipepeta']].";stroke:".$line[$bar['tipepeta']].";stroke-width:".$width[$bar['tipepeta']].";stroke-linejoin:round;cursor:help;' vector-effect='non-scaling-stroke'";
				$result .= "<path id='".$bar['idsvg']."' d='".$bar['path']."' title='".$expTitle[0]."' ".$style." onclick=\"showinfosvg('".$bar['idsvg']."',1,event)\" fill-opacity='0.4'><title>".$expTitle[0]."</title></path>";
			}else{
				$pieces = explode(',', $bar['path']);
				if($bar['tipepeta']==$textBlok){
					$result .= "<g font-family='verdana' font-size='1' kerning='0' font-weight='100' fill='#000000' xml:space='preserve'>
						<text transform='matrix(0.001 0 0 0.001 ".($pieces[0]-0.001)." ".($pieces[1]+0.0001).")'>".substr($expTitle[0],-4)."</text>
					</g>";
				}else{
					$result .= "<circle cx='".$pieces[0]."' cy='".$pieces[1]."' title='".$expTitle[0]."' id='".$bar['tipepeta']."' fill=".$fill[$bar['tipepeta']]." r='".$width[$bar['tipepeta']]."' onclick=\"showinfosvg('".$bar['idsvg']."',1,event)\" style='cursor:help'><title>".$expTitle[0]."</title></circle>";
				}
			}
			
		}
		$result .= "</g>";	
		

echo'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" >';
//echo'<g transform="matrix(22.875250579738022,0,0,22.875250579738022,-2418.4019651260796,271.3121198613853)" class="svg-pan-zoom_viewport">';
echo $result;
//echo "</g>";
echo "</svg>";

*/
?>
