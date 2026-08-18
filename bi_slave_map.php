<?php
include('../config/connection.php');
include('../lib/nangkoelib.php');
include('master_validation.php');
include('lib/zLib.php');

$method = checkPostGet('method','');
$vChecked = checkPostGet('vChecked','');
$kodept = checkPostGet('kodept','');
$kebun = checkPostGet('kebun','');
$idsvg = checkPostGet('idsvg','');
$tipesvg = checkPostGet('tipesvg','');

$periodeawal = checkPostGet('periodeawal','');
$periodeakhir = checkPostGet('periodeakhir','');
$detailtipedokumen = checkPostGet('detailtipedokumen','');
$detailkegiatan = checkPostGet('detailkegiatan','');
$noakun = checkPostGet('noakun','');

$detaillaporan2 = checkPostGet('detaillaporan2','');

$showstatusblok = checkPostGet('showstatusblok','');
$divNewDetail = checkPostGet('divNewDetail','');
$detInfo = checkPostGet('detInfo','');
$detailreport = checkPostGet('detailreport','');

$karyawanid = checkPostGet('karyawanid','');
$tanggalhistory = checkPostGet('tanggalhistory','');
$tipetracking = checkPostGet('tipetracking','');

$namafile = checkPostGet('namafile','');
$arrnmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');

$fill=array();
$pointx1=array();
$pointx=array();
$pointy=array();

switch($method){
	case'loadmenu':
	
		break;
		
	case 'checkedmap':
		//Get From master warna
		$str="select * from ".$dbname.".bi_5warna";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$fill[$bar['tipe']]=$bar['fill'];			
			$line[$bar['tipe']]=$bar['line'];	
			$width[$bar['tipe']]=$bar['width'];	
		}
		
		//Get Tipe feature map
		$str = "select * from ".$dbname.".bi_map_basic where tipepeta = '".$vChecked."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$str2 = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$bar['tipepeta']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$tipefeature = $bar2['tipefeature'];
			
			if($fill[$vChecked]==''){
				$fill[$vChecked]='none';
			}
			else{
				$fill[$vChecked]=$fill[$vChecked];
			}
			
			if($line[$vChecked]==''){
				$line[$vChecked]=='none';
			}
			else{
				$line[$vChecked]==$line[$vChecked];
			}
			
			if($width[$vChecked]==''){
				$width[$vChecked]=0.05;
			}
			else{
				$width[$vChecked]=$width[$vChecked];
			}
			
			$expTitle = explode('##', $bar['keterangan']);
			if($tipefeature == 'path'){
				$style = "style='fill:".$fill[$vChecked].";stroke-linejoin:round;stroke:".$line[$vChecked].";stroke-width:".$width[$vChecked].";cursor:pointer;' vector-effect='non-scaling-stroke'";
				$result .= "<path id='".$bar['idsvg']."' d='".$bar['path']."' title='".$expTitle[0]."' ".$style." onclick=\"showinfosvg('".$bar['idsvg']."',0,event)\"><title>".$expTitle[0]."</title></path>";
			}else{
				$pieces = explode(',', $bar['path']);
				$result .= "<g class='non-scaling'>";
				$result .= "<circle class='non-scaling' transform='translate(".$pieces[0]." ".$pieces[1].")' title='".$expTitle[0]."' id='".$bar['tipepeta']."' fill='".$fill[$vChecked]."' r=".$width[$vChecked]." onclick=\"showinfosvg('".$bar['idsvg']."',0,event)\"><title>".$expTitle[0]."</title></circle>";
				$result .= "</g>";
			}
		}
		
		echo $result;
		break;
		
	case 'preview':
		if(str_replace('-','',$periodeawal) > str_replace('-','',$periodeakhir)){
			exit("error : Periode awal harus lebih kecil dari periode akhir.");
		}
		
		if($detailtipedokumen == ''){
			exit("error : Tipe Dokumen harus dipilih.");
		}
		
		if($detailkegiatan == ''){
			exit("error : Kegiatan harus dipilih.");
		}
		
		//Get From master warna
		$str="select * from ".$dbname.".bi_5warna";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$fill[$bar['tipe']]=$bar['fill'];			
			$line[$bar['tipe']]=$bar['line'];	
			$width[$bar['tipe']]=$bar['width'];	
		}
	
		$str = "select * from ".$dbname.".bi_map_transaksi where (periode between '".$periodeawal."' and '".$periodeakhir."') and kodeorg = '".$kebun."' and tipedok = '".$detailtipedokumen."' and kodekegiatan = '".$detailkegiatan."'";
		//exit("error :".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$str2 = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '".$bar['tipepeta']."' and keterangan = '".$bar['tipedok']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$tipefeature = $bar2['tipefeature'];
			$tipepeta = $bar2['id_tipepeta'];
			$expTitle = explode('##', $bar['keterangan']);
			if($tipefeature == 'path'){
				//$style = "style='fill:#BDBDBD;stroke:#424242;stroke-width:0.5;stroke-linejoin:round;cursor:help;' vector-effect='non-scaling-stroke'";
				$style = "style='fill:".($fill[$tipepeta]!='' ? $fill[$tipepeta] : 'none').";stroke-linejoin:round;stroke:".($line[$tipepeta]!='' ? $line[$tipepeta] : 'none').";stroke-width:1;cursor:pointer;' vector-effect='non-scaling-stroke'";
				$result .= "<path id='".$bar['idsvg']."' d='".$bar['path']."' title='".$expTitle[0]."' ".$style." onclick=\"showinfosvg('".$bar['idsvg']."',2,event)\"><title>".$expTitle[0]."</title></path>";
			//exit("error :".$style);
			}else{
				$pieces = explode(',', $bar['path']);
				$result .= "<circle class='non-scaling' transform='translate(".$pieces[0].",".$pieces[1].")' r=0.001 title='".$expTitle[0]."' id='".$bar['tipepeta']."' fill='".$bar['warna']."' onclick=\"showinfosvg('".$bar['idsvg']."',2,event)\"><title>".$expTitle[0]."</title></cicle>";
			}
		}
		
		echo $result;
		break;
		
	case 'getkebun':
		$str = "select distinct(unit) as unit from ".$dbname.".bi_map_pt where kodeorg = '".$kodept."' order by unit";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optUnit = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while($bar = $res->fetch()){
			$optNamaUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['unit']."'");
			$optUnit .= "<option value='".$bar['unit']."'>".$optNamaUnit[$bar['unit']]."</option>";
		}		
		echo $optUnit;
		break;
		
	case 'getdetailkebun':
	
		//Get Master Warna
		$str="select * from ".$dbname.".bi_5warna";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$fill[$bar['tipe']]=$bar['fill'];			
			$line[$bar['tipe']]=$bar['line'];	
			$width[$bar['tipe']]=$bar['width'];	
		}
	
		//Get MAP Blok
		$str = "select * from ".$dbname.".bi_map_pt where kodeorg = '".$kodept."' and unit = '".$kebun."' order by tipepeta asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = "";
		$pointx = "";
		$pointy = "";
		while($bar = $res->fetch()){
			if($no != "" && $no != $bar['tipepeta']){
				$result .= "</g>";
			}
			
			if($no == "" || $no != $bar['tipepeta']){
				$pointx1 = $bar['viewbox'];
				$pointx1 = explode(' ', $pointx1);
				$pointx = ($pointx1[0] + ($pointx1[2] / 2));
				$pointy = ($pointx1[1] + ($pointx1[3] / 2));
				// if($tipefeature == 'path'){
					// $pointx1 = explode('l', $bar['path']);
					// $pointx1 = $pointx1[0];
					// $pointx1 = explode('M', $pointx1);
					// $pointx1 = $pointx1[1];
					// $pointx1 = explode(',', $pointx1);
					// $pointx = $pointx1[0];
					// $pointy = $pointx1[1];
				// }else{
					// $pointx1 = explode(',', $bar['path']);
					// $pointx = $pointx1[0];
					// $pointy = $pointx1[1];
				// }
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
		
		// $str = "select * from ".$dbname.".bi_map_pt where kodeorg = '".$kodept."' and unit = '".$kebun."' order by tipepeta asc";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar = $res->fetch()){
			// if($bar['tipepeta'] == $firstPT){
				// $str2 = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$bar['tipepeta']."'";
				// $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				// $res2->setFetchMode(PDO::FETCH_ASSOC);
				// $bar2 = $res2->fetch();
				// $tipefeature = $bar2['tipefeature'];
				// $expTitle = explode('##', $bar['keterangan']);
				
				// $coorSvg = str_replace(' ',',',$bar['path']);
				// $coorSvg = str_replace('L',',',$coorSvg);
				// $coorSvg = str_replace('l',',',$coorSvg);
				// $coorSvg = str_replace('M','',$coorSvg);
				// $coorSvg = str_replace('m','',$coorSvg);
				// $coorSvg = explode(',',$coorSvg);
				
				// $result .= "<g font-family='verdana' font-size='1' kerning='0' font-weight='100' fill='#000000' xml:space='preserve'>
					// <text transform='matrix(0.001 0 0 0.001 ".$coorSvg[0]." ".$coorSvg[1].")'>".substr($expTitle[0],-4)."</text>
				// </g>";
			// }
		// }
		
		//Get List Map PT
		$result2 = "";
		$result2 = "<table>
			<tr>
				<td align=center></td>
				<td style='text-align:center'><b>Fill</b></td>
				<td style='width:20px'>&nbsp;</td>
				<td style='text-align:center'><b>Line</b></td>
			</tr>";
		$str = "select distinct(tipepeta) as tipepeta from ".$dbname.".bi_map_pt where kodeorg = '".$kodept."' and unit = '".$kebun."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optKetTipe = makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan',"id_tipepeta='".$bar['tipepeta']."'");
			$result2 .= "<tr>
				<td style='float:left;width:100%;padding-bottom:4px;list-style-type:none;padding-right:15px;'>";
					if($bar['tipepeta'] == $firstPT || $bar['tipepeta'] == $textBlok){
						$result2 .= "<input type='checkbox' id='tipepetapt' name='tipepetapt[]' value='".$bar['tipepeta']."' checked onclick=checkMarkListPt(this) />".$optKetTipe[$bar['tipepeta']]."
						<input type='hidden' id='MARK_".$bar['tipepeta']."' value='1'>";
					}else{
						$result2 .= "<input type='checkbox' id='tipepetapt' name='tipepetapt[]' value='".$bar['tipepeta']."' onclick=checkMarkListPt(this) />".$optKetTipe[$bar['tipepeta']]."
						<input type='hidden' id='MARK_".$bar['tipepeta']."' value='0'>";
					}
				$result2 .= "</td>
				<td style='width:25px;border-top:1px solid #FFF;border-left:1px solid #FFF;border-bottom:1px solid #FFF;border-right:1px solid #FFF;' bgcolor=".(@$fill[$bar['tipepeta']]=='none' ? '' : @$fill[$bar['tipepeta']])."></td>
				<td></td>
				<td style='width:25px;border-top:1px solid #FFF;border-left:1px solid #FFF;border-bottom:1px solid #FFF;border-right:1px solid #FFF;' bgcolor=".(@$line[$bar['tipepeta']]=='none' ? '' : @$line[$bar['tipepeta']])."></td>
			</tr>";
		}
		
		$str = "select distinct(periode) from ".$dbname.".setup_periodeakuntansi order by periode desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optPeriode .= "<option value='".$bar['periode']."'>".$bar['periode']."</option>";
		}
		
			$optKegiatan = $optTipeDok = $optTipeLap = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select distinct(t1.tipedok), t2.nama_tipe as nama_tipe from ".$dbname.".bi_map_transaksi t1 
				left join ".$dbname.".bi_5tipedok t2 on t1.tipedok = t2.id_tipedok 
				where t1.kodeorg = '".$kebun."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optTipeDok .= "<option value='".$bar['tipedok']."'>".$bar['nama_tipe']."</option>";
		}
		
		
		
		$str = "select distinct(idlap), namalaporan from ".$dbname.".bi_5laporan where tipe = 'performance' order by namalaporan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optTipeLap .= "<option value='".$bar['idlap']."'>".$bar['namalaporan']."</option>";
		}
		
		$optNoAkun='';
		$optNoAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select distinct(noakun) as noakun from ".$dbname.".bi_5siklusht order by noakun asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optNamaAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$bar['noakun']."'");
			$optNoAkun .= "<option value='".$bar['noakun']."'>".$bar['noakun']." ".$optNamaAkun[$bar['noakun']]."</option>";
		}
		
		$optsik='';		
		
		//Get data karyawan tracking
		$str = "select distinct(a.username) as username, b.karyawanid, c.namakaryawan, c.nik from ".$dbname.".gps_location a
		left join ".$dbname.".user b on a.username = b.namauser
		left join ".$dbname.".datakaryawan c on b.karyawanid=c.karyawanid 
		where c.lokasitugas = '".$kebun."' order by c.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($numrows >= 1){
			while($bar = $res->fetch()){
				$optKaryawan .= "<option value='".$bar['username']."'>".$bar['nik']." - ".$bar['namakaryawan']."</option>";
			}
		}else{
			$optKaryawan = "<option value='0'>".$_SESSION['lang']['pilihdata']."</option>";
		}
		
		//Get Filter Informasi blok
		$optfilterblok = "";
		$optfilterblok = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optfilterblok .= "<option value='1'>Tahun Tanam</option>";
		$optfilterblok .= "<option value='2'>Status Blok</option>";
		$optfilterblok .= "<option value='3'>Topografi</option>";
		$optfilterblok .= "<option value='4'>Jenis Bibit</option>";
		$optfilterblok .= "<option value='5'>Inti Plasma</option>";
		
		$result3 = "";
		$result3 .= "<hr><table width=100%>
			<tr>
				<td style='text-align:center'>
					<select id='chkdetail' onchange=\"getChkDetail();\">
						<option value=''>".$_SESSION['lang']['pilihdata']."</option>
						<option value='activitymonitoring'>Activity Monitoring</option>
						<option value='performance'>Performance</option>
						<option value='siklus'>Siklus</option>
						<option value='tracking'>Tracking</option>
						<option value='informasiblok'>Informasi Blok</option>
					</select>
				</td>
			</tr>
			<!--<tr>
				<td>
					<input type=radio name=chk id=chkKegiatan onclick=\"checkChkTipe();\" value='0' checked>&nbsp;Kegiatan
				</td>
				<td style='padding-left:10px;'>
					<input type=radio name=chk id=chkLaporan onclick=\"checkChkTipe();\" value='1'>&nbsp;Laporan
				</td>
			</tr>-->
		</table>
		<hr>
		<div id='divChkKegiatan' style='display:none;'>
		<table>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id='periodeawal' onchange=\"getdetailkegiatan();\">".$optPeriode."</select></td>
				<td>s/d</td>
				<td><select id='periodeakhir' onchange=\"getdetailkegiatan();\">".$optPeriode."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tipedokumen']."</td>
				<td>:</td>
				<td colspan=3><select id='detailtipedokumen' onchange=\"getdetailkegiatan();\" style='max-width:120px;'>".$optTipeDok."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>:</td>
				<td colspan=3><select id='detailkegiatan' onchange='clearAMSvgDetail();' style='max-width:120px'>".$optKegiatan."</select></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td colspan=3><button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button></td>
			</tr>
		</table>
		</div>
		<div id='divChkLaporan' style='display:none;'>
		<table>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id='periodeawal2' onchange=\"clearPFLaporan();\" >".$optPeriode."</select></td>
				<td>s/d</td>
				<td><select id='periodeakhir2' onchange=\"clearPFLaporan();\" >".$optPeriode."</select></td>
			</tr>
			<tr>
				<td>Laporan</td>
				<td>:</td>
				<td colspan=3>
					<select id='detaillaporan2' onchange=\"getnamafile();\" style='max-width:185px;'>".$optTipeLap."</select>
					<input type=hidden id=namafile2 value=''>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td colspan=3><button class=mybutton onclick=preview2()>".$_SESSION['lang']['preview']."</button></td>
			</tr>
		</table>
		</div>
		<div id='divChkSiklus' style='display:none;'>
		<table>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id='periodeawal3' onchange=clearPFLaporan()>".$optPeriode."</select></td>
				<td>s/d</td>
				<td><select id='periodeakhir3' onchange=clearPFLaporan()>".$optPeriode."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['noakun']."</td>
				<td>:</td>
				<td colspan=3>
					<select id='noakun3' style='max-width:185px;' onchange=getkegiatan()>".$optNoAkun."</select>
				</td>
			</tr>
			<tr>
				<td>Kegiatan</td>
				<td>:</td>
				<td colspan=3>
					<select id='detailkegiatan3' style='max-width:185px;' onchange=getidsiklus()>".$optsik."</select>
					<input type=hidden id=namafile3 value='bi_map_siklus.php'>
					<input type=hidden id=detaillaporan3 value=''>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td colspan=3><button class=mybutton onclick=preview3()>".$_SESSION['lang']['preview']."</button></td>
			</tr>
		</table>
		</div>
		<div id='divChkTracking' style='display:none;'>
		<table>
			<tr>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>:</td>
				<td>
					<input type='radio' name='tipetracking' id='realtime' value='realtime' onclick='changeTipeTracking()' checked />Real Time
					<input type='radio' name='tipetracking' id='history' value='history' onclick='changeTipeTracking()' />History
				</td>
			</tr>
			<tr id='tanggaltracking' style='display:none'>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input id='tanggalhistory' class='myinputtext' onkeypress='return tanpa_kutip(event)' style='width:60px' readonly='readonly' onmousemove='setCalendar(this.id)' type='text' value='".date('d-m-Y')."'>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['namakaryawan']."</td>
				<td>:</td>
				<td>
					<select id='karyawanid4' style='max-width:180px;' onchange='clearTracking()'>".$optKaryawan."</select>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=showDataTracking()>Tracking</button>
				</td>
			</tr>
		</table>
		</div>
		<div id='divChkInformasiBlok' style='display:none;'>
		<table>
			<tr>
				<td>".$_SESSION['lang']['searchdata']."</td>
				<td>:</td>
				<td>
					<select id='filterblok' onchange='clearChkDetail()'>".$optfilterblok."</select>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td colspan=3><button class=mybutton onclick=preview4()>".$_SESSION['lang']['preview']."</button></td>
			</tr>
		</table>
		</div>
		<div id='divLegend' style='display:none;padding-top:5px;'>
		</div>";
		
		echo $result."####".$result2."####".$result3."####".$pointx."####".$pointy;
		break;
		
	case 'getdetailkegiatan':
		$optKegiatan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select distinct(kodekegiatan) from ".$dbname.".bi_map_transaksi where kodeorg = '".$kebun."' and (periode BETWEEN '".$periodeawal."' and '".$periodeakhir."') and tipedok = '".$detailtipedokumen."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optNamaKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kodekegiatan']."'");
			$optKegiatan .= "<option value='".$bar['kodekegiatan']."'>".$optNamaKegiatan[$bar['kodekegiatan']]."</option>";
		}
		echo $optKegiatan;
		break;
		
	case 'showinfosvg':
		$result = "";
		$result .= "<div><table id='tblInformasi'><tr><td>";
		if($tipesvg==0){
			$str = "select * from ".$dbname.".bi_map_pt where idsvg = '".$idsvg."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$expKtr = explode('##',$bar['keterangan']);
			
			$str = "select * from ".$dbname.".setup_blok where kodeorg = '".$expKtr[0]."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$sttBlok = $bar['statusblok'];
			$thnTanam = $bar['tahuntanam'];
			$intiplasma = ($bar['intiplasma']=='P' ? 'Plasma' : 'Inti');
			$jnsBibit = $bar['jenisbibit'];
			$topgra = $bar['topografi'];
			$luasPlant = $bar['luasareaproduktif'];
			$luasUnplant = $bar['luasareanonproduktif'];
			$jlhPokok = $bar['jumlahpokok'];
			$sPH = @($jlhPokok/$luasPlant);
			
			$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
			$result .= "<table>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['perusahaan']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$optNmOrg[$kodept]."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['blok']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$expKtr[0]."</td>
				</tr>
			</table>";
			$result .= "<hr style='margin-bottom:5px;border-top: 1px solid red;'>";
			
			$frm[0] .= "<table>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['kebun']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$optNmOrg[$kebun]."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['divisi']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".substr($expKtr[0],0,6)."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['blok']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$expKtr[0]."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['statusblok']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$sttBlok."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['tahuntanam']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$thnTanam."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>Inti / Plasma</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$intiplasma."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['jenisbibit']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$jnsBibit."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['topografi']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$topgra."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['luasareaproduktif']." (Ha)</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".number_format($luasareaproduktif)."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['luasareanonproduktif']." (Ha)</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".number_format($luasareanonproduktif)."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['jmlhpokok']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".number_format($jlhPokok)."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['sph']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".number_format($sPH,2)."</td>
				</tr>
			</table>";
			$frm[1] .= 'form 2';
			
			$hfrm[0] = $_SESSION['lang']['detail'];
			$hfrm[1] = $_SESSION['lang']['produksi'];
			
			$result .= drawTabBI('FRM', $hfrm, $frm, 120, '');
		}else if($tipesvg==1){
			$str = "select * from ".$dbname.".bi_map_pt where idsvg = '".$idsvg."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$expKtr = explode('##',$bar['keterangan']);
			
			$str = "select * from ".$dbname.".setup_blok where kodeorg = '".$expKtr[0]."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$sttBlok = $bar['statusblok'];
			$thnTanam = $bar['tahuntanam'];
			$intiplasma = ($bar['intiplasma']=='P' ? 'Plasma' : 'Inti');
			$jnsBibit = $bar['jenisbibit'];
			$topgra = $bar['topografi'];
			$luasPlant = $bar['luasareaproduktif'];
			$luasUnplant = $bar['luasareanonproduktif'];
			$jlhPokok = $bar['jumlahpokok'];
			$sPH = @($jlhPokok/$luasPlant);
			
			$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
			$result .= "<table>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['perusahaan']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$optNmOrg[$kodept]."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['blok']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$expKtr[0]."</td>
				</tr>
			</table>";
			$result .= "<hr style='margin-bottom:5px;border-top: 1px solid red;'>";
			
			$frm[0] .= "<table>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['kebun']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$optNmOrg[$kebun]."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['divisi']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".substr($expKtr[0],0,6)."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['blok']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$expKtr[0]."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['statusblok']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$sttBlok."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['tahuntanam']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$thnTanam."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>Inti / Plasma</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$intiplasma."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['jenisbibit']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$jnsBibit."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['topografi']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".$topgra."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['luasareaproduktif']." (Ha)</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".number_format($luasPlant)."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['luasareanonproduktif']." (Ha)</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".number_format($luasUnplant)."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['jmlhpokok']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".number_format($jlhPokok)."</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['sph']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>".number_format($sPH,2)."</td>
				</tr>
			</table>";
			
			$thnSkrg = date("Y");
			$thnLalu = date("Y")-1;
			
			$arrBulan = array();
			for($i=0;$i<12;$i++){
				$val = date("M Y", strtotime("-".$i." month"));
				$key = date("Y-m", strtotime("-".$i." month"));
				$arrBulan[$key] = $val;
			}
			
			$arrReal = array();
			$str = "select sum(kgwb) as kg, left(tanggal,7) as tanggal from ".$dbname.".kebun_spb_vw where blok = '".$expKtr[0]."' and left(tanggal,4) in ('".$thnSkrg."','".$thnLalu."') group by left(tanggal,7)";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$arrReal[$bar['tanggal']] = @($bar['kg']/1000);
			}
			
			$arrAngg = array();
			$str = "select sum(kg01) as kg01, sum(kg02) as kg02, sum(kg03) as kg03, sum(kg04) as kg04, sum(kg05) as kg05, sum(kg06) as kg06, sum(kg07) as kg07, sum(kg08) as kg08, sum(kg09) as kg09, sum(kg10) as kg10, sum(kg11) as kg11, sum(kg12) as kg12, tahunbudget as tahun from ".$dbname.".bgt_produksi_kbn_kg_vw where kodeblok = '".$expKtr[0]."' and tahunbudget in ('".$thnSkrg."','".$thnLalu."') group by tahunbudget";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$arrAngg[$bar['tahun']."-01"] = @($bar['kg01']/1000);
				$arrAngg[$bar['tahun']."-02"] = @($bar['kg02']/1000);
				$arrAngg[$bar['tahun']."-03"] = @($bar['kg03']/1000);
				$arrAngg[$bar['tahun']."-04"] = @($bar['kg04']/1000);
				$arrAngg[$bar['tahun']."-05"] = @($bar['kg05']/1000);
				$arrAngg[$bar['tahun']."-06"] = @($bar['kg06']/1000);
				$arrAngg[$bar['tahun']."-07"] = @($bar['kg07']/1000);
				$arrAngg[$bar['tahun']."-08"] = @($bar['kg08']/1000);
				$arrAngg[$bar['tahun']."-09"] = @($bar['kg09']/1000);
				$arrAngg[$bar['tahun']."-10"] = @($bar['kg10']/1000);
				$arrAngg[$bar['tahun']."-11"] = @($bar['kg11']/1000);
				$arrAngg[$bar['tahun']."-12"] = @($bar['kg12']/1000);
			}
			
			//GRAPH
			$maxReal = 0;
			$maxAngg = 0;
			$maxAll = 0;
			if(!empty($arrReal) || !empty($arrAngg)){
				$maxReal = @round(max($arrReal));
				$maxAngg = @round(max($arrAngg));
				$maxAll = @max($maxReal,$maxAngg);
			}
			
			$frm[1] .= "<span style='font-size:85%'><i>History produksi 12 bulan (ton)</i></span>
			<table width=100% cellpadding=0 cellspacing=0>
				<tr>
					<td colspan=2></td>
					<td style='border-left:1px solid rgb(30, 88, 150);padding-left:5px;'></td>
					<td style='text-align:center'><i>Real</i></td>
					<td style='text-align:center'><i>Angg</i></td>
				</tr>";
			foreach($arrBulan as $key=>$val){
				$widthReal = @((100/$maxAll) * round($arrReal[$key]));
				$widthAngg = @((100/$maxAll) * round($arrAngg[$key]));
				$frm[1] .= "<tr>
					<td rowspan=2 style='width:80px;'>".$val."</td>
					<td style='width:100px;font-size:50%;padding-right:1%'>
						<table cellpadding=0 cellspacing=0 style='width:".$widthReal."%'><tr><td style='background-color:blue'>&nbsp;</td></tr></table>
					</td>
					<td rowspan=2 style='border-left:1px solid rgb(30, 88, 150);padding-left:5px;width:80px;'>".$val."</td>
					<td rowspan=2 style='text-align:right;color:blue'>".number_format($arrReal[$key])."</td>
					<td rowspan=2 style='text-align:right;color:orange'>".number_format($arrAngg[$key])."</td>
				</tr>
				<tr>
					<td style='width:100px;font-size:50%;padding-right:1%;padding-bottom:2%;'>
						<table cellpadding=0 cellspacing=0 style='width:".$widthAngg."%;'><tr><td style='background-color:orange;'>&nbsp;</td></tr></table>
					<td>
				</tr>"; 
			}
			
			$frm[1] .= "</table>";
			
			if($showstatusblok !=0){
				$frm[2] .= $detailreport;
				$hfrm[2] = $_SESSION['lang']['preview'];
			}
			
			$hfrm[0] = $_SESSION['lang']['detail'];
			$hfrm[1] = $_SESSION['lang']['produksi'];
			
			$result .= drawTabBI('FRM', $hfrm, $frm, 120, '');
		}else{
			$str = "select * from ".$dbname.".bi_map_transaksi where idsvg = '".$idsvg."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			
			$bar = $res->fetch();
			$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi = '".$bar['kodeorg']."'");
			$optTipeDok = makeOption($dbname,'bi_5tipedok','id_tipedok,nama_tipe',"id_tipedok = '".$bar['tipedok']."'");
			$optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan = '".$bar['kodekegiatan']."'");
			
			$str2="select * from ".$dbname.".bi_5tipedok where id_tipedok='".$bar['tipedok']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$tTabel = $bar2['tabel'];
			$tKolom1 = $bar2['nodok'];
			$tKolom2 = $bar2['jnskgtn'];
			$tKolom3 = $bar2['kodeorg'];
			$tKolom4 = $bar2['periode'];
			
			$vPeriode = $bar['periode'];
			$vKegiatan = $optKegiatan[$bar['kodekegiatan']];
			
			$result .= "<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead style='background:black'>
				<tr align=center style='background:black'>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>".$_SESSION['lang']['kodeblok']."</td>
					<td>".$_SESSION['lang']['kegiatan']."</td>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>Hasil Kerja</td>
					<td>HK Realisasi</td>
					<td>Jumlah Realisasi</td>
					<td>Photo</td>
				</tr>
				</thead>
				<tbody>";
			
			//Get List Document
			$str = "select distinct(nodok) as nodok from ".$dbname.".bi_map_transaksi_dok where idsvg = '".$idsvg."' order by nodok asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$no = 0;
			while($bar = $res->fetch()){
				$str3 = "select ".$tKolom1.",".$tKolom2.",".$tKolom3.",".$tKolom4." from ".$dbname.".".$tTabel." where ".$tKolom1." = '".$bar['nodok']."'";
				$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
				$res3->setFetchMode(PDO::FETCH_ASSOC);
				$bar3 = $res3->fetch();
				
				$str2 = "select * from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$bar['nodok']."' order by nourut asc";
				// $numrows = owlBaris($str2);
				$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				$numrows=$res2->rowCount();
				$no = $no + 1;
				$result .= "<tr class=rowcontent align=center>
					<td style='vertical-align:top'>".$bar['nodok']."</td>
					<td style='vertical-align:top'>".$bar3[$tKolom3]."</td>
					<td style='vertical-align:top'>".$vKegiatan."</td>
					<td style='vertical-align:top'>".$vPeriode."</td>
					<td style='vertical-align:top'>".tanggalnormal(substr($bar3[$tKolom4],0,10))."</td>
					<td style='vertical-align:top'>-</td>
					<td style='vertical-align:top'>-</td>
					<td style='vertical-align:top'>-</td>
					<td style='vertical-align:top'>";
						if($numrows <= 0){
							$result .= "-";
						}else{
							$result .= "<table cellpading=1 cellspacing=1 border=0 class=sortable>
								<tr class=rowcontent>";
							while($bar2 = $res2->fetch()){
								$result .= "<td style='cursor:pointer;' onclick=\"isifile('".$bar2['namafile']."','event');\"><img src='../fileupload/photodok/".$bar2['namafile']."' style='width:50px;height:50px'></td>";
							}
							$result .= "<tr></table>";
						}
					$result .= "</td>
				</tr>";
			}
			$result .= "</tbody>
		</table>";
				
		}
		$result .= "</td></tr></table></div>";
			
		echo $result;
		
		
		
		
		// $result .= "<div>";
		
		// if($tipesvg == '0'){
			// $str = "select * from ".$dbname.".bi_map_basic where idsvg = '".$idsvg."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar = $res->fetch();
			
			// $expKtr = explode('##',$bar['keterangan']);
			// $optTpPeta = makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan',"id_tipepeta = '".$bar['tipepeta']."'");
			// $vTipePeta = $optTpPeta[$bar['tipepeta']];
			// $vNamaPeta = $bar['namapeta']." / ".$expKtr[0];
			// if($bar['tipepeta'] == $firstTipe){
				// $optNmProvinsi = makeOption($dbname,'provinsi','id,provinsi',"id = '".$bar['namapeta']."'");
				// $vNamaPeta = $optNmProvinsi[$bar['namapeta']]." / ".$expKtr[0];
			// }
			
			// $result .= "<table cellpading=1 cellspacing=1 border=0 class=sortable style='background:none'>
				// <tr>
					// <td>ID SVG</td>
					// <td>:</td>
					// <td>".$idsvg."</td>
				// </tr>
				// <tr>
					// <td>Kelompok Peta</td>
					// <td>:</td>
					// <td>Peta Dasar</td>
				// </tr>
				// <tr>
					// <td>Tipe Peta</td>
					// <td>:</td>
					// <td>".$vTipePeta." (".$bar['tipepeta'].")</td>
				// </tr>
				// <tr>
					// <td>Nama Peta</td>
					// <td>:</td>
					// <td>".$vNamaPeta."</td>
				// </tr>
			// </table>";
		// }else if($tipesvg == '1'){
			// $str = "select * from ".$dbname.".bi_map_pt where idsvg = '".$idsvg."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar = $res->fetch();
			
			// $expKtr = explode('##',$bar['keterangan']);
			// $optTpPeta = makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan',"id_tipepeta = '".$bar['tipepeta']."'");
			// $vTipePeta = $optTpPeta[$bar['tipepeta']];
			// $vNamaPeta = $bar['namapeta']." / ".$expKtr[0];
			// if($bar['tipepeta'] == $firstTipe){
				// $optNmProvinsi = makeOption($dbname,'provinsi','id,provinsi',"id = '".$bar['namapeta']."'");
				// $vNamaPeta = $optNmProvinsi[$bar['namapeta']]." / ".$expKtr[0];
			// }
			
			// $result .= "<table cellpading=1 cellspacing=1 border=0 class=sortable style='background:none'>
				// <tr>
					// <td>ID SVG</td>
					// <td>:</td>
					// <td>".$idsvg."</td>
				// </tr>
				// <tr>
					// <td>Kelompok Peta</td>
					// <td>:</td>
					// <td>Peta PT</td>
				// </tr>
				// <tr>
					// <td>Tipe Peta</td>
					// <td>:</td>
					// <td>".$vTipePeta." (".$bar['tipepeta'].")</td>
				// </tr>
				// <tr>
					// <td>Nama Peta</td>
					// <td>:</td>
					// <td>".$vNamaPeta."</td>
				// </tr>
			// </table>";
			
			// $str="select * from ".$dbname.".setup_blok where kodeorg='".$expKtr[0]."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar = $res->fetch();			
			// $optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan',"topografi='".$bar['topografi']."'");
			
			// $detailreport2 = "<table cellpading=1 cellspacing=1 border=0 class=sortable>
				// <thead style='background:black'>
				// <tr align=center style='background:black'>
					// <td>".$_SESSION['lang']['statusblok']."</td>
					// <td>".$_SESSION['lang']['tahuntanam']."</td>
					// <td>".$_SESSION['lang']['intiplasma']."</td>
					// <td>".$_SESSION['lang']['jenisbibit']."</td>
					// <td>".$_SESSION['lang']['topografi']."</td>
					// <td>HA Planted</td>
					// <td>HA Unplanted</td>
					// <td>".$_SESSION['lang']['jumlahpokok']."</td>
					// <td>SPH</td>
				// </tr>
				// </thead>
				// <tbody>
				// <tr class=rowcontent align=center>
					// <td>".$bar['statusblok']."</td>
					// <td>".$bar['tahuntanam']."</td>
					// <td>".($bar['intiplasma']=='I' ? 'Inti' : ($bar['intiplasma']=='P') ? 'Plasma' : '')."</td>
					// <td>".$bar['jenisbibit']."</td>
					// <td>".$optTopografi[$bar['topografi']]."</td>
					// <td>".number_format($bar['luasareaproduktif'])."</td>
					// <td>".number_format($bar['luasareanonproduktif'])."</td>
					// <td>".number_format($bar['jumlahpokok'])."</td>
					// <td>".number_format(@($bar['jumlahpokok']/$bar['luasareaproduktif']),2)."</td>
				// </tr>
				// </tbody>
			// </table>";
			
			// $result .= "<div style='padding-top:5px;overflow:auto'><b>Detail : </b>".$detailreport2."".$detailreport."</div>";
		// }else if($tipesvg == '2'){
			// $str = "select * from ".$dbname.".bi_map_transaksi where idsvg = '".$idsvg."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			
			// $bar = $res->fetch();
			// $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi = '".$bar['kodeorg']."'");
			// $optTipeDok = makeOption($dbname,'bi_5tipedok','id_tipedok,nama_tipe',"id_tipedok = '".$bar['tipedok']."'");
			// $optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan = '".$bar['kodekegiatan']."'");
			
			// $str2="select * from ".$dbname.".bi_5tipedok where id_tipedok='".$bar['tipedok']."'";
			// $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			// $res2->setFetchMode(PDO::FETCH_ASSOC);
			// $bar2 = $res2->fetch();
			// $tTabel = $bar2['tabel'];
			// $tKolom1 = $bar2['nodok'];
			// $tKolom2 = $bar2['jnskgtn'];
			// $tKolom3 = $bar2['kodeorg'];
			// $tKolom4 = $bar2['periode'];
			
			// $vPeriode = $bar['periode'];
			// $vKegiatan = $optKegiatan[$bar['kodekegiatan']];
			
			// $result .= "<table cellpading=1 cellspacing=1 border=0 class=sortable style='background:none'>
				// <tr>
					// <td>ID SVG</td>
					// <td>:</td>
					// <td>".$idsvg."</td>
				// </tr>
				// <tr>
					// <td>Kelompok Peta</td>
					// <td>:</td>
					// <td>Peta Transaksi</td>
				// </tr>
				// <tr>
					// <td>".$_SESSION['lang']['unit']."</td>
					// <td>:</td>
					// <td>".$bar['kodeorg']."-".$optOrg[$bar['kodeorg']]."</td>
				// </tr>
				// <tr>
					// <td>Layer</td>
					// <td>:</td>
					// <td>Activity Monitoring / ".$optTipeDok[$bar['tipedok']]."</td>
				// </tr>
			// </table>";
			
			// $detailreport = "<table cellpading=1 cellspacing=1 border=0 class=sortable>
				// <thead style='background:black'>
				// <tr align=center style='background:black'>
					// <td>".$_SESSION['lang']['notransaksi']."</td>
					// <td>".$_SESSION['lang']['kodeblok']."</td>
					// <td>".$_SESSION['lang']['kegiatan']."</td>
					// <td>".$_SESSION['lang']['periode']."</td>
					// <td>".$_SESSION['lang']['tanggal']."</td>
					// <td>Hasil Kerja</td>
					// <td>HK Realisasi</td>
					// <td>Jumlah Realisasi</td>
					// <td>Photo</td>
				// </tr>
				// </thead>
				// <tbody>";

				// //Get List Document
				// $str = "select distinct(nodok) as nodok from ".$dbname.".bi_map_transaksi_dok where idsvg = '".$idsvg."' order by nodok asc";
				// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				// $res->setFetchMode(PDO::FETCH_ASSOC);
				// $no = 0;
				// while($bar = $res->fetch()){
					// $str3 = "select ".$tKolom1.",".$tKolom2.",".$tKolom3.",".$tKolom4." from ".$dbname.".".$tTabel." where ".$tKolom1." = '".$bar['nodok']."'";
					// $res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
					// $res3->setFetchMode(PDO::FETCH_ASSOC);
					// $bar3 = $res3->fetch();
					
					// $str2 = "select * from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$bar['nodok']."' order by nourut asc";
					// // $numrows = owlBaris($str2);
					// $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
					// $res2->setFetchMode(PDO::FETCH_ASSOC);
					// $numrows=$res2->rowCount();
					// $no = $no + 1;
					// $detailreport .= "<tr class=rowcontent align=center>
						// <td style='vertical-align:top'>".$bar['nodok']."</td>
						// <td style='vertical-align:top'>".$bar3[$tKolom3]."</td>
						// <td style='vertical-align:top'>".$vKegiatan."</td>
						// <td style='vertical-align:top'>".$vPeriode."</td>
						// <td style='vertical-align:top'>".tanggalnormal(substr($bar3[$tKolom4],0,10))."</td>
						// <td style='vertical-align:top'>-</td>
						// <td style='vertical-align:top'>-</td>
						// <td style='vertical-align:top'>-</td>
						// <td style='vertical-align:top'>";
							// if($numrows <= 0){
								// $detailreport .= "-";
							// }else{
								// $detailreport .= "<table cellpading=1 cellspacing=1 border=0 class=sortable>";
								// while($bar2 = $res2->fetch()){
									// $detailreport .= "<tr>
										// <td style='cursor:pointer;' onclick=\"parent.isifile('".$bar2['namafile']."','event');\"><u><font color=blue>".$bar2['namafile']."</td>
									// </tr>";
								// }
								// $detailreport .= "</table>";
							// }
						// $detailreport .= "</td>
					// </tr>";
				// }
				// $detailreport .= "</tbody>
			// </table><br>";
			
			// $result .= "<b>Detail : </b>".$detailreport;
			
			
		// }
		// $result .= "</div>";
		// echo $result;
		break;
		
	case 'isifile':
		$expNamafile = explode('.',$namafile);
		if($expNamafile[1]=='pdf'){
			echo "<embed src='../fileupload/photodok/".$namafile."' width=780px height=370px>";
		}else{
			echo"<img src='../fileupload/photodok/".$namafile."'>";
		}
		break;
		
	case 'getnamafile':
		$str = "select * from ".$dbname.".bi_5laporan where idlap = '".$detaillaporan2."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		echo $bar['namafile'];
		break;
		
	case 'getkegiatan':
		$optsik='';
		
		$str = "select distinct(kegiatan) as kegiatan from ".$dbname.".bi_5siklusht where noakun = '".$noakun."' order by kegiatan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optNamaKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kegiatan']."' and status='1'");
			if($bar['kegiatan']==''){
				$optsik="<option value=''>".$_SESSION['lang']['all']."</option>";
			}else{
				$optsik .= "<option value='".$bar['kegiatan']."'>".$bar['kegiatan']." ".$optNamaKegiatan[$bar['kegiatan']]."</option>";
			}
		}
		
		echo $optsik;
		break;
		
	case 'getidsiklus':
		$str = "select idsiklus from ".$dbname.".bi_5siklusht where noakun = '".$noakun."' and kegiatan='".$detailkegiatan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		echo $bar['idsiklus'];
		
		break;
	
	case 'showDataTracking':
		$result='';
		$str = "select * from ".$dbname.".gps_location where username = '".$karyawanid."' and tanggal = '".tanggalsystem($tanggalhistory)."' order by updatetime asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$count = 0;
		
		if($numrows <= 0){
			exit("warning : ".$_SESSION['lang']['datanotfound']);
		}
		
		$result .= "<filter id='location_image' x='-50%' y='-150%' width='200%' height='200%'>
			<feImage xlink:href='images/location.png' />
		</filter>";
		while($bar = $res->fetch()){
			if($count==0){
				$firstCoorX = $bar['logitude'];
				$firstCoorY = ($bar['latitude']*(-1));
			}
			$result .= "<circle filter='url(#location_image)' cx='".($bar['logitude'])."' cy='".($bar['latitude']*(-1))."' r='0.00003' fill='red' onclick='showinfogps()' />";
			$coordinat[] = $bar['logitude'].",".$bar['latitude']*(-1);
			$count++;
		}
		
		$joinArrCoor = implode(" ",$coordinat);
		
		$result .= "<polyline points='".$joinArrCoor."' stroke='".randomColor()."' stroke-width='0.00001' stroke-linecap='butt' fill='none' stroke-linejoin='miter' title='testtetstst' />";
		
		echo $result."####".$firstCoorX."####".$firstCoorY;
		
		break;
		
	case 'showDataTrackingRealtime':
		$waktuMin = tambahmenit(1);
		$waktuMax = kurangmenit(1);
		
		$str = "select * from ".$dbname.".gps_location where username = '".$karyawanid."' and updatetime between '".$waktuMin."' and '".$waktuMax."' order by updatetime desc LIMIT 2";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$count = 0;
		
		while($bar = $res->fetch()){
			if($count==0){
				$xAwal = $bar['logitude'];
				$yAwal = ($bar['latitude']*(-1));
			}else{
				$xAkhir = $bar['logitude'];
				$yAkhir = ($bar['latitude']*(-1));
			}
			$count++;
		}
		
		echo $xAwal."####".$yAwal."####".$xAkhir."####".$yAkhir;
		break;
		
	case 'clearColorSvgBlok':
		$str="select fill from ".$dbname.".bi_5warna where tipe='".$firstPT."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$warnaBlok = $bar['fill'];
	
		//Get MAP Blok
		$str = "select idsvg from ".$dbname.".bi_map_pt where kodeorg = '".$kodept."' and unit = '".$kebun."' and tipepeta='".$firstPT."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$arrBlok[]['idsvg'] = $bar['idsvg'];
		}
		
		echo json_encode($arrBlok)."####".$warnaBlok;
		break;
}

function randomColor() {
	$colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
	return $colorArray[array_rand($colorArray)];
}
?>