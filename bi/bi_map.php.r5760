<?
	include('../config/connection.php');
	include('../lib/nangkoelib.php');

	OPEN_BODY_NEWBI();
?>

<!-- <script src="js/jquery-latest.min.js"></script>
<script src="js/jspdf.min.js"></script>
<script src="js/SVGPanUnscale.js"></script> -->
<script src="js/svg-pan-zoom.js"></script>
<script src="js/bi_map.js?ver=2.6"></script>

<?
	$frm[1] = '';
	$frm[0] = '';
	$tab1 = "";
	$tab2 = "";
	$tab3 = "";

	//get warna
	$str = "select * from ".$dbname.".bi_5warna";
	$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$fill[$bar['tipe']] = $bar['fill'];
		$line[$bar['tipe']] = $bar['line'];
		$width[$bar['tipe']] = $bar['width'];
	}

	//Get Tipe peta
	$strTipe = "select * from ".$dbname.".bi_5tipepeta where tipekelompok='0' order by id_tipepeta ASC";
	$qryTipe = $owlPDO->query($strTipe) or die(print " Gagal: ".PDOException::getMessage());
	$qryTipe->setFetchMode(PDO::FETCH_ASSOC);

	$frm[1] .= "
		<input type='hidden' id='showstatusblok' style='display:none' value='0'>
			<span id='divNewDetail' style='display:none'></span>
			<table>
				<tbody>
					<tr>
						<td>
							<ul id=sortable>
								<table>
									<tr>
										<td align=center></td>
										<td style='text-align:center'><b>Fill</b></td>
										<td style='width:20px'>&nbsp;</td>
										<td style='text-align:center'><b>Line</b></td>
									</tr>
	";

	while ($res = $qryTipe->fetch()) {
		$frm[1] .= "";
		if ($res['id_tipepeta'] == $firstTipe) {
			$frm[1] .= "
				<tr>
					<td style='float:left;width:100%;padding-bottom:4px;list-style-type:none;padding-right:15px;'>
						<input type='checkbox' id='tipepeta' name='tipepeta[]' value='".$res['id_tipepeta']."' checked onclick=checkMarkList(this) />
						".$res['keterangan']."
						<input type='hidden' id='MARK_".$res['id_tipepeta']."' value='1'>
					</td>
					<td style='width:25px;border-top:1px solid #FFF;border-left:1px solid #FFF;border-bottom:1px solid #FFF;border-right:1px solid #FFF;' bgcolor=".@$fill[$res['id_tipepeta']]."></td>
					<td></td>
					<td style='width:25px;border-top:1px solid #FFF;border-left:1px solid #FFF;border-bottom:1px solid #FFF;border-right:1px solid #FFF;' bgcolor=".@$line[$res['id_tipepeta']]."></td>
				</tr>
			";
		} else {
			$frm[1] .= "
				<tr>
					<td style='float:left;width:100%;padding-bottom:4px;list-style-type:none;cursor:pointer;padding-right:15px;'>
						<input type='checkbox' id='tipepeta' name='tipepeta[]' value='".$res['id_tipepeta']."' onclick=checkMarkList(this) />
						".$res['keterangan']."
						<input type='hidden' id='MARK_".$res['id_tipepeta']."' value='0'>
					</td>
					<td style='width:25px;border-top:1px solid #FFF;border-left:1px solid #FFF;border-bottom:1px solid #FFF;border-right:1px solid #FFF;' bgcolor=".@$fill[$res['id_tipepeta']]."></td>
					<td></td>
					<td style='width:25px;border-top:1px solid #FFF;border-left:1px solid #FFF;border-bottom:1px solid #FFF;border-right:1px solid #FFF;' bgcolor=".@$line[$res['id_tipepeta']]."></td>
				</tr>
			";
		}
		$frm[1] .= "";
	}

	$frm[1] .= "
							</table>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	";
	
	$hfrm[1] = $_SESSION['lang']['tipepeta'];

	//Get All PT
	$optPT = $optKebun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "SELECT * FROM ".$dbname.".organisasi WHERE tipe = 'PT'";
	try {
		$res = $owlPDO->query($str);
	} catch (PDOException $e) {
		die(print "Gagal: ". $e->getMessage());
	}
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$optPT .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
	}

	$frm[0] .= "
		<table style='padding-bottom:10px;'>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
			</tr>
			<tr>
				<td>
					<select id='kodept' onchange='getkebun()'>".$optPT."</select>
				</td>
			</tr>
			<tr id='trkebun' style='display:none'>
				<td>
					<table>
						<tr>
							<td>".$_SESSION['lang']['kebun']."</td>
						</tr>
						<tr>
							<td>
								<select id='kebun' onchange='getdetailkebun()'>".$optKebun."</select>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr id='trdetail' style='display:none'>
				<td style='padding-top:10px;' id='detailpt'></td>
			</tr>
		</table>
	";

	$hfrm[0] = $_SESSION['lang']['pt'];
	$sfrm[0] = 'open';
	$sfrm[1] = 'open';

	echo "
		<div id='menumap' style='display:none'>
			<div id=header style='padding-top:15px;padding-bottom:15px;padding-left:10px;'>
				<b>OWL Plantation Map</b>
				<span style='float:right;margin-right:5px;cursor:pointer' title='Hide Menu' onclick='hiddenmenu()'>
					<img src='images/36.png'>
				</span>
			</div>
			<hr>
	";

	drawaccordion($hfrm, $frm, $sfrm);
	
	echo "</div>";

	$tab .= "<div id='detailmap'></div>";

	echo "<div id='addons' style='display:none'>";
	echo showpopup('Detail Map', $tab, 'addons', 'pane');
	echo "</div>";

	$tab2 .= "<div id='detailreport' style='text-align:center;width:100%'></div>";
	echo "<div id='addons2' style='display:none'>";
	echo showpopup('Detail Report', $tab2, 'addons2', 'pane2', 1);
	echo "</div>";

	$tab3 .= "<div id='informasi' style='background-color:#E0ECFF'></div>";
	echo "<div id='addons3' style='display:none'>";
	echo showpopup($_SESSION['lang']['informasi'], $tab3, 'addons3', 'pane3', 1);
	echo "</div>";
	echo "<div id='editor'></div>";
	echo "
		<div id='menu_map' title='View Menu' onclick='loadmenu()'>
			<img src='./images/menuBtn.png'>
		</div>
	";

	$optProvinsi = "<option value=''>".$_SESSION['lang']['all']."</option>";
	$str = "select * from ".$dbname.".provinsi";
	$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$optProvinsi .= "<option value='".$bar['id']."'>".$bar['provinsi']."</option>";
	}

	echo "
		<div id='mapall' style='width:100%;height:98%'>
			<input type='hidden' id='tempId' value=''>
			<input type='hidden' id='tempWidth' value=''>
			<input type='hidden' id='tempColor' value=''>
			<fieldset id='home_map' style='height:100%;border:#99CCFF solid 1px;background-color:#99CCFF;cursor:-moz-grab;cursor: -webkit-grab;'>
				<div id='showscale' style='position:fixed;bottom:20px;width:90%;text-align:right;font-weight:bold;color:#556'>
					Zoom : 0 x
				</div>
	";

	$qryTipe = $owlPDO->query($strTipe) or die(print " Gagal: ".PDOException::getMessage());
	$qryTipe->setFetchMode(PDO::FETCH_ASSOC);

	echo '
		<svg id="demo-blok" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="overflow: hidden;width: 100%; height: 100%;" version="1.1">
		<g transform="matrix(1.5,0,0,1.5,423.8499755859375,0)" class="svg-pan-zoom_viewport">
	';

	while ($resTipe = $qryTipe->fetch()) {
		if ($resTipe['id_tipepeta'] == $firstTipe) {
			$str = "select * from ".$dbname.".bi_map_basic where tipepeta = '".$firstTipe."'";
			$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);

			echo "
				<g id='SVGLOCATION_".$firstTipe."'>
				<desc>Layer '.$firstTipe.'</desc>
			";

			while ($bar = $res->fetch()) {
				$tipefeature = $resTipe['tipefeature'];
				$expTitle = explode('##', $bar['keterangan']);

				if ($tipefeature == 'path') {
					if ($bar['tipepeta'] == $firstTipe) {
						$style = "style='fill:".$fill[$resTipe['id_tipepeta']].";stroke:".$line[$resTipe['id_tipepeta']].";cursor:default'";
					} else {
						$style = "style='fill:".$fill[$resTipe['id_tipepeta']].";stroke:".$line[$resTipe['id_tipepeta']]."stroke-width:".$width['id_tipepeta'].";stroke-linejoin:round;cursor:default;' vector-effect='non-scaling-stroke'";
					}

					// echo "<path id='".$bar['idsvg']."' d='".$bar['path']."' title='".$expTitle[0]."' ".$style." onclick=\"showinfosvg('".$bar['idsvg']."',0,'event')\" />";
					// echo "<path id='".$bar['idsvg']."' d='".$bar['path']."' title='".$expTitle[0]."' ".$style." onmousedown=\"isClicked=false;\" onmousemove=\"isClicked = true;\" onmouseup=\"showinfosvg('".$bar['idsvg']."',0,'event')\" />";
					
					echo "
						<path class='pathhover' id='".$bar['idsvg']."' d='".$bar['path']."' ".$style." alt='".$expTitle[0]."' title='".$expTitle[0]."'>
							<title>".$expTitle[0]."</title>
						</path>
					";
				} else {
					$pieces = explode(',', $bar['path']);
					
					// echo "<circle class='non-scaling' transform='translate(".$pieces[0].",".$pieces[1].")' title='".$expTitle[0]."' id='".$bar['tipepeta']."' fill='".$fill['id_tipepeta']."' r='".$width['id_tipepeta']."' onmousedown=\"isClicked=false;\" onmousemove=\"isClicked = true;\" onmouseup=\"showinfosvg('".$bar['idsvg']."',0,'event')\" />";
					
					echo "
						<circle class='non-scaling' transform='translate(".$pieces[0].",".$pieces[1].")' title='".$expTitle[0]."' id='".$bar['tipepeta']."' fill='".$fill['id_tipepeta']."' r='".$width['id_tipepeta']."' style='cursor:default'>
							<title>".$expTitle[0]."</title>
						</circle>
					";
				}
			}
			
			echo "</g>";
		} else {
			echo "<g id='SVGLOCATION_".$resTipe['id_tipepeta']."'></g>";
		}
	}

	echo "
						<g id=svgPt></g>
						<g id=svgDetail></g>
						<g id=svgTracking></g>
					</g>
				</svg>
				
				<script>		
					var isClicked = false;
					var clickCounter = 100;
					window.onload = function() {
						var panZoom = window.panZoom = svgPanZoom('#demo-blok', {
							zoomEnabled: true,
							controlIconsEnabled: true,
							fit: 1,
							center: 1
						});
					};
					
					window.onresize = function(){
						window.panZoom.resize();
						window.panZoom.fit();
						window.panZoom.center();
					};
				</script>
			</fieldset>
		</div>
	";

	// CLOSE_BOX();

	CLOSE_BODY_NEWBI();
