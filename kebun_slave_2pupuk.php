<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$proses= checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$pt    = checkPostGet('pt', '');
$thntnm= checkPostGet('tt', '');
$divisi= checkPostGet('divisi', '');
$pupuk = checkPostGet('pupuk', '');
$status= checkPostGet('status', '');
$tahun = checkPostGet('tahun', '');
$tampil= checkPostGet('tampil', '');
$kdblok= checkPostGet('kdblok', '');
$kdbrg = checkPostGet('kdbrg', '');
$bln   = checkPostGet('bln', '');

if($tampil!='getdetail'){	
	#if($status==''){exit("warning : Status harus di pilih.");}
	if($kdorg==''){exit("warning : Unit harus di pilih.");}
	if($tahun==''){exit("warning : Tahun harus di pilih.");}
}

$where=$wh="";
if($kdorg!=''){
	$where=" and kodeorg ='".$kdorg."'";
	$wh=" and b.kodeorg ='".$kdorg."'";
}
if($divisi!=''){
	$where.=" and blok like '".$divisi."%'";
	$wh.=" and a.kodeorg like '".$divisi."%'";
}
if($thntnm!=''){
	$where.=" and tahuntanam='".$thntnm."'";
}
if($status!=''){
	$where.=" and statusblok='".$status."'";
	$wh.=" and b.tipetransaksi='".$status."'";
}
if($pupuk!=''){
	$where.=" and kodebarang='".$pupuk."'";
	$wh.=" and a.kodebarang='".$pupuk."'";
}else{
	$wh.=" and a.kodebarang like '311%'";
}
if($tahun!=''){
	$where.=" and periodepemupukan like '".$tahun."%'";
	$wh.=" and tanggal like '".$tahun."%'";
}


$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$satkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
switch($tampil){
	case'getdetail':
		$optpkk = makeOption($dbname,'setup_blok','kodeorg,jumlahpokok');
		$optha = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif');
		$tab.="<table cellpadding=5 cellspacing=1 class=sortable>
					<thead>
						<tr class=rowheader>
							<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
							<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
							<th align=center>" . $_SESSION['lang']['blok'] . "</th>
							<th align=center>" . $_SESSION['lang']['luas'] . "</th>
							<th align=center>" . $_SESSION['lang']['pokok'] . "</th>
							<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
							<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
							<th align=center>" . $_SESSION['lang']['kwantitas'] . "</th>
							<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
							<th align=center>Kg/Ha</th>
							<th align=center>Kg/Pkk</th>
							";
						$tab.="</tr>
					</thead>
					 <tbody>";
		
		$str = "select a.*,b.tanggal,substr(tanggal,1,7) as prd from ".$dbname.".kebun_pakaimaterial a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 and a.kodeorg= '".$kdblok."' and a.kodebarang ='".$kdbrg."' and tanggal like '".$bln."%'  and kodekegiatan in (select kodekegiatan from ".$dbname.".setup_kegiatan where namakegiatan like '%MANURING%' or namakegiatan like '%PEMUPUKAN%' or namakegiatan like '%PUPUK%')";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optppk = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			
			$no+=1;
			$tab.="<tr class=rowcontent style=vertical-align:top;>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['notransaksi']."</td>";
			$tab.="<td align=center>".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=right>".numb_format($optha[$bar['kodeorg']],2)."</td>";
			$tab.="<td align=right>".numb_format($optpkk[$bar['kodeorg']])."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=center>".$satkeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=right>".numb_format($bar['kwantitasha'],2)."</td>";
			$tab.="<td align=right>".numb_format($bar['kwantitas'],2)."</td>";
			$tab.="<td align=right>".numb_format($bar['kwantitas']/$bar['kwantitasha'],2)."</td>";
			$tab.="<td align=right>".numb_format($bar['kwantitas']/($bar['kwantitasha']*($optpkk[$bar['kodeorg']]/$optha[$bar['kodeorg']])),2)."</td>";
			
			$ttlha+=$bar['kwantitasha'];
			$ttlkg+=$bar['kwantitas'];
			$ttlpkr+=($bar['kwantitasha']*($optpkk[$bar['kodeorg']]/$optha[$bar['kodeorg']]));
			
		}
		$tab.="</tr>";
		$tab.="<tr class=rowcontent style=vertical-align:top;>";
		$tab.="<td align=center colspan=7>TOTAL</td>";
		$tab.="<td align=right>".numb_format($ttlha,2)."</td>";
		$tab.="<td align=right>".numb_format($ttlkg,2)."</td>";
		$tab.="<td align=right>".numb_format($ttlkg/$ttlha,2)."</td>";
		$tab.="<td align=right>".numb_format($ttlkg/$ttlpkr,2)."</td>";
		$tab.="</tr>";
		
		echo $tab;
	break;
	case'real':
		if($proses=='excel'){
			$border="border=1";
		}else{
			$border="border=0";
		}
		$tab="";
		$rbulan = month_inbetween($tahun."-01",$tahun."-12");
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class='sortable nowrap'>
					<thead>
						<tr class=rowheader>
							<th align=center rowspan=3>" . $_SESSION['lang']['nourut'] . "</th>
							<th align=center rowspan=3>" . $_SESSION['lang']['divisi'] . "</th>
							<th align=center rowspan=3>" . $_SESSION['lang']['blok'] . "</th>
							<th align=center rowspan=3 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
							<th align=center rowspan=3>" . $_SESSION['lang']['luas'] . "</th>
							<th align=center rowspan=3>" . $_SESSION['lang']['pokok'] . "</th>
							<th align=center rowspan=3>SPH</th>
							<th align=center rowspan=3>" . $_SESSION['lang']['pupuk'] . "</th>
							";
						foreach($rbulan as $bln){							
							$tab.="<th align=center colspan=4>".numToMonth(substr($bln,5,2),'E','short')."</th>";
						}
						$tab.="<th align=center colspan=4>" . $_SESSION['lang']['total'] . "</th>";
						$tab.="</tr>";
						$tab.="<tr class=rowheader>";
						foreach($rbulan as $bln){							
							$tab.="<th align=center colspan=2>Renc</th>";
							$tab.="<th align=center colspan=2 style=color:#00FF40;>Real</th>";
						}
						$tab.="<th align=center colspan=2>Renc</th>";
						$tab.="<th align=center colspan=2 style=color:#00FF40;>Real</th>";						
						$tab.="</tr>";
					
						$tab.="<tr class=rowheader>";
						foreach($rbulan as $bln){							
							$tab.="<th align=center>" . $_SESSION['lang']['dosis']."</th>";
							$tab.="<th align=center>" . $_SESSION['lang']['jumlah']."</th>";
							$tab.="<th align=center style=color:#00FF40;>" . $_SESSION['lang']['dosis']."</th>";
							$tab.="<th align=center style=color:#00FF40;>" . $_SESSION['lang']['jumlah']."</th>";
						}
						$tab.="<th align=center>" . $_SESSION['lang']['dosis']."</th>";
						$tab.="<th align=center>" . $_SESSION['lang']['jumlah']."</th>";						
						$tab.="<th align=center style=color:#00FF40;>" . $_SESSION['lang']['dosis']."</th>";
						$tab.="<th align=center style=color:#00FF40;>" . $_SESSION['lang']['jumlah']."</th>";						
						$tab.="</tr>
					</thead>
					 <tbody>";
					#renc
					$str = "select * from " . $dbname . ".kebun_rekomendasipupuk where 1=1 ".$where." order by blok";
					$res = fetchdata($str);
					$data=$jlh=$dss=$pokok=$luas=array();
					foreach($res as $bar){
						$jnsppk[$bar['kodebarang']]=$bar['kodebarang'];
						$data[$bar['blok']]=$bar['blok'];
						$tt[$bar['blok']]=$bar['tahuntanam'];
						$luas[$bar['blok']]=$bar['luas'];
						$pokok[$bar['blok']]=$bar['pokok'];
						$jlh[$bar['blok']][$bar['kodebarang']][$bar['periodepemupukan']]+=$bar['jumlah'];
						$dss[$bar['blok']][$bar['kodebarang']][$bar['periodepemupukan']]+=$bar['dosis'];
					}
					#real
					$str = "select a.*,b.tanggal,substr(tanggal,1,7) as prd from ".$dbname.".kebun_pakaimaterial a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 ".$wh." and kodekegiatan in (select kodekegiatan from ".$dbname.".setup_kegiatan where namakegiatan like '%MANURING%' or namakegiatan like '%PEMUPUKAN%' or namakegiatan like '%PUPUK%')";
					$res = fetchdata($str);
					foreach($res as $bar){
						$jnsppk[$bar['kodebarang']]=$bar['kodebarang'];
						$data[$bar['kodeorg']]=$bar['kodeorg'];
						
						$opttt  = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
						$optpkk = makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$bar['kodeorg']."'");
						$optluas= makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$bar['kodeorg']."'");
						@$sph=$optpkk[$bar['kodeorg']]/$optluas[$bar['kodeorg']];
						
						$tt[$bar['kodeorg']]=$opttt[$bar['kodeorg']];
						$luas[$bar['kodeorg']]=$optluas[$bar['kodeorg']];
						$pokok[$bar['kodeorg']]=$optpkk[$bar['kodeorg']];
						$rjlh[$bar['kodeorg']][$bar['kodebarang']][$bar['prd']]+=$bar['kwantitas'];
						$pkkreal[$bar['kodeorg']][$bar['kodebarang']][$bar['prd']]+=($bar['kwantitasha']*$sph);
					}
					
					if(count($data)==0){
						exit($_SESSION['lang']['errdatanotexist']);
					}
					
					$no=0;
					$row="rowspan=".count($jnsppk)."";
					array_multisort($data,SORT_ASC);
					foreach($data as $kdblok){
						$no+=1;
						$tab.="<tr class=rowcontent style=vertical-align:top;>";
						$tab.="<td align=center ".$row.">".$no."</td>";
						$tab.="<td align=center ".$row.">".$nmorg[substr($kdblok,0,6)]."</td>";
						$tab.="<td align=center ".$row.">".$nmorg[$kdblok]."</td>";
						$tab.="<td align=center ".$row.">".$tt[$kdblok]."</td>";
						$tab.="<td align=center ".$row.">".numb_format($luas[$kdblok],2)."</td>";
						$tab.="<td align=center ".$row.">".numb_format($pokok[$kdblok])."</td>";
						$tab.="<td align=center ".$row.">".@numb_format($pokok[$kdblok]/$luas[$kdblok],2)."</td>";
						$tluas+=$luas[$kdblok];$tpokok+=$pokok[$kdblok];
						$nbrg=0;
						foreach($jnsppk as $kdbrg){
							$nbrg+=1;
							if($nbrg>1){
								$tab.="</tr>";
								$tab.="<tr class=rowcontent>";
							}
							$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
							$tab.="<td>".$optppk[$kdbrg]."</td>";
							$napl=0;
							foreach($rbulan as $bln){
								$tab.="<td align=right>".numb_format($dss[$kdblok][$kdbrg][$bln],2)."</td>";
								$tab.="<td align=right>".numb_format($jlh[$kdblok][$kdbrg][$bln],2)."</td>";
								
								$tab.="<td align=right style=background-color:#DDF5FC>".@numb_format($rjlh[$kdblok][$kdbrg][$bln]/$pkkreal[$kdblok][$kdbrg][$bln],2)."</td>";
								$tab.="<td align=right style=background-color:#DDF5FC;cursor:pointer;color:blue; title='click untuk melihat detail' onclick=getdetail('".$kdblok."','".$kdbrg."','".$bln."')>".numb_format($rjlh[$kdblok][$kdbrg][$bln],2)."</td>";
								
								$tdss[$kdblok][$kdbrg]+=$dss[$kdblok][$kdbrg][$bln];
								$tjlh[$kdblok][$kdbrg]+=$jlh[$kdblok][$kdbrg][$bln];
								
								$rtdss[$kdblok][$kdbrg]+=$pkkreal[$kdblok][$kdbrg][$bln];
								$rtjlh[$kdblok][$kdbrg]+=$rjlh[$kdblok][$kdbrg][$bln];
								
								$gtjlh[$kdbrg][$bln]+=$jlh[$kdblok][$kdbrg][$bln];
								$gtdss[$kdbrg][$bln]+=$dss[$kdblok][$kdbrg][$bln];
								
								$rgtjlh[$kdbrg][$bln]+=$rjlh[$kdblok][$kdbrg][$bln];
								$rgtdss[$kdbrg][$bln]+=$pkkreal[$kdblok][$kdbrg][$bln];
							}
							$tab.="<td align=right>".numb_format($tdss[$kdblok][$kdbrg],2)."</td>";
							$tab.="<td align=right>".numb_format($tjlh[$kdblok][$kdbrg],2)."</td>";
							$tab.="<td align=right style=background-color:#DDF5FC>".@numb_format($rtjlh[$kdblok][$kdbrg]/$rtdss[$kdblok][$kdbrg],2)."</td>";
							$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rtjlh[$kdblok][$kdbrg],2)."</td>";
						}						
					}	
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=4 rowspan=".count($jnsppk).">S U B  T O T A L</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tluas,2)."</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tpokok)."</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tpokok/$tluas,2)."</td>";
					$nbrg=0;
					foreach($jnsppk as $kdbrg){
						$nbrg+=1;
						if($nbrg>1){
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
						}
						$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
						$tab.="<td>".$optppk[$kdbrg]."</td>";
						foreach($rbulan as $bln){							
							$tab.="<td align=right>".numb_format($gtjlh[$kdbrg][$bln]/$tpokok,2)."</td>";
							$tab.="<td align=right>".numb_format($gtjlh[$kdbrg][$bln],2)."</td>";
							
							$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rgtjlh[$kdbrg][$bln]/$tpokok,2)."</td>";
							$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rgtjlh[$kdbrg][$bln],2)."</td>";
							
							$gttjlh[$kdbrg]+=$gtjlh[$kdbrg][$bln];
							$grandttl[$bln]+=$gtjlh[$kdbrg][$bln];
							$rgttjlh[$kdbrg]+=$rgtjlh[$kdbrg][$bln];
							$rgrandttl[$bln]+=$rgtjlh[$kdbrg][$bln];
						}
						$tab.="<td align=right>".numb_format($gttjlh[$kdbrg]/$tpokok,2)."</td>";
						$tab.="<td align=right>".numb_format($gttjlh[$kdbrg],2)."</td>";
						
						$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rgttjlh[$kdbrg]/$tpokok,2)."</td>";
						$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rgttjlh[$kdbrg],2)."</td>";
					}
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=8>G R A N D  T O T A L</td>";
					foreach($rbulan as $bln){							
						$tab.="<td align=right>".numb_format($grandttl[$bln]/$tpokok,2)."</td>";
						$tab.="<td align=right>".numb_format($grandttl[$bln],2)."</td>";
						
						$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rgrandttl[$bln]/$tpokok,2)."</td>";
						$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rgrandttl[$bln],2)."</td>";
						
						$gtt+=$grandttl[$bln];
						$rgtt+=$rgrandttl[$bln];
					}
					$tab.="<td align=right>".numb_format($gtt/$tpokok,2)."</td>";
					$tab.="<td align=right>".numb_format($gtt,2)."</td>";
					
					$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rgtt/$tpokok,2)."</td>";
					$tab.="<td align=right style=background-color:#DDF5FC>".numb_format($rgtt,2)."</td>";
					$tab.="</tr>";
					
					$tab.="</tbody>
				 </table>
		";
		if($proses=='excel'){
			$nop = "pupuk.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("pupuk", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $tab;
		}
	break;
	default;
		if($proses=='excel'){
			$border="border=1";
		}else{
			$border="border=0";
		}
		$tab="";
		$rbulan = month_inbetween($tahun."-01",$tahun."-12");
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class='sortable nowrap'>
					<thead>
						<tr class=rowheader>
							<th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
							<th align=center rowspan=2>" . $_SESSION['lang']['divisi'] . "</th>
							<th align=center rowspan=2>" . $_SESSION['lang']['blok'] . "</th>
							<th align=center rowspan=2 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
							<th align=center rowspan=2>" . $_SESSION['lang']['luas'] . "</th>
							<th align=center rowspan=2>" . $_SESSION['lang']['pokok'] . "</th>
							<th align=center rowspan=2>SPH</th>
							<th align=center rowspan=2>" . $_SESSION['lang']['pupuk'] . "</th>
							<th align=center rowspan=2>Apl</th>
							";
						foreach($rbulan as $bln){							
							$tab.="<th align=center colspan=2>".numToMonth(substr($bln,5,2),'E','short')."</th>";
						}
						$tab.="<th align=center colspan=2>" . $_SESSION['lang']['total'] . "</th>";
						$tab.="</tr>";
						$tab.="<tr class=rowheader>";
						foreach($rbulan as $bln){							
							$tab.="<th align=center>" . $_SESSION['lang']['dosis']."</th>";
							$tab.="<th align=center>" . $_SESSION['lang']['jumlah']."</th>";
						}
						$tab.="<th align=center>" . $_SESSION['lang']['dosis']."</th>";
						$tab.="<th align=center>" . $_SESSION['lang']['jumlah']."</th>";						
						$tab.="</tr>
					</thead>
					 <tbody>";
					#renc
					$str = "select * from " . $dbname . ".kebun_rekomendasipupuk where 1=1 ".$where." order by blok";
					$res = fetchdata($str);
					$data=$jlh=$dss=array();
					foreach($res as $bar){
						$aplikasi[$bar['aplikasi']]=$bar['aplikasi'];
						$jnsppk[$bar['kodebarang']]=$bar['kodebarang'];
						$data[$bar['blok']]=$bar['blok'];
						$tt[$bar['blok']]=$bar['tahuntanam'];
						$luas[$bar['blok']]=$bar['luas'];
						$pokok[$bar['blok']]=$bar['pokok'];
						$jlh[$bar['blok']][$bar['kodebarang']][$bar['aplikasi']][$bar['periodepemupukan']]+=$bar['jumlah'];
						$dss[$bar['blok']][$bar['kodebarang']][$bar['aplikasi']][$bar['periodepemupukan']]+=$bar['dosis'];
						
					}
					if(count($data)==0){
						exit($_SESSION['lang']['errdatanotexist']);
					}
					
					
					
					
					$no=0;
					$row="rowspan=".((count($jnsppk)*count($aplikasi)))."";
					array_multisort($data,SORT_ASC);
					array_multisort($aplikasi,SORT_ASC);
					foreach($data as $kdblok){
						$no+=1;
						$tab.="<tr class=rowcontent style=vertical-align:top;>";
						$tab.="<td align=center ".$row.">".$no."</td>";
						$tab.="<td align=center ".$row.">".$nmorg[substr($kdblok,0,6)]."</td>";
						$tab.="<td align=center ".$row.">".$nmorg[$kdblok]."</td>";
						$tab.="<td align=center ".$row.">".$tt[$kdblok]."</td>";
						$tab.="<td align=center ".$row.">".numb_format($luas[$kdblok],2)."</td>";
						$tab.="<td align=center ".$row.">".numb_format($pokok[$kdblok])."</td>";
						$tab.="<td align=center ".$row.">".numb_format($pokok[$kdblok]/$luas[$kdblok],2)."</td>";
						$tluas+=$luas[$kdblok];$tpokok+=$pokok[$kdblok];
						$nbrg=0;
						foreach($jnsppk as $kdbrg){
							$nbrg+=1;
							if($nbrg>1){
								$tab.="</tr>";
								$tab.="<tr class=rowcontent>";
							}
							$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
							$tab.="<td rowspan=".count($aplikasi).">".$optppk[$kdbrg]."</td>";
							$napl=0;
							foreach($aplikasi as $apl){
								$napl+=1;
								if($napl>1){
									$tab.="</tr>";
									$tab.="<tr class=rowcontent>";								
								}	
								$tab.="<td align=center style=font-style:italic;>".$apl."</td>";
								foreach($rbulan as $bln){
									$tab.="<td align=right>".numb_format($dss[$kdblok][$kdbrg][$apl][$bln],2)."</td>";
									$tab.="<td align=right>".numb_format($jlh[$kdblok][$kdbrg][$apl][$bln],2)."</td>";
									$tdss[$kdblok][$kdbrg][$apl]+=$dss[$kdblok][$kdbrg][$apl][$bln];
									$tjlh[$kdblok][$kdbrg][$apl]+=$jlh[$kdblok][$kdbrg][$apl][$bln];
									
									$gtjlh[$kdbrg][$bln]+=$jlh[$kdblok][$kdbrg][$apl][$bln];
									$gtdss[$kdbrg][$bln]+=$dss[$kdblok][$kdbrg][$apl][$bln];
								}
								$tab.="<td align=right>".numb_format($tdss[$kdblok][$kdbrg][$apl],2)."</td>";
								$tab.="<td align=right>".numb_format($tjlh[$kdblok][$kdbrg][$apl],2)."</td>";
								
							}
						}						
					}	
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=4 rowspan=".count($jnsppk).">S U B  T O T A L</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tluas,2)."</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tpokok)."</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tpokok/$tluas,2)."</td>";
					$nbrg=0;
					foreach($jnsppk as $kdbrg){
						$nbrg+=1;
						if($nbrg>1){
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
						}
						$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
						$tab.="<td>".$optppk[$kdbrg]."</td>";
						$tab.="<td></td>";
						foreach($rbulan as $bln){							
							$tab.="<td align=right>".numb_format($gtjlh[$kdbrg][$bln]/$tpokok,2)."</td>";
							$tab.="<td align=right>".numb_format($gtjlh[$kdbrg][$bln],2)."</td>";
							$gttjlh[$kdbrg]+=$gtjlh[$kdbrg][$bln];
							$grandttl[$bln]+=$gtjlh[$kdbrg][$bln];
						}
						$tab.="<td align=right>".numb_format($gttjlh[$kdbrg]/$tpokok,2)."</td>";
						$tab.="<td align=right>".numb_format($gttjlh[$kdbrg],2)."</td>";
					}
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=9>G R A N D  T O T A L</td>";
					foreach($rbulan as $bln){							
						$tab.="<td align=right>".numb_format($grandttl[$bln]/$tpokok,2)."</td>";
						$tab.="<td align=right>".numb_format($grandttl[$bln],2)."</td>";
						$gtt+=$grandttl[$bln];
					}
					$tab.="<td align=right>".numb_format($gtt/$tpokok,2)."</td>";
					$tab.="<td align=right>".numb_format($gtt,2)."</td>";
					$tab.="</tr>";
					
					$tab.="</tbody>
				 </table>
		";
		if($proses=='excel'){
			$nop = "pupuk.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("pupuk", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $tab;
		}
	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}

function numb_format($a,$d=0){
	if(is_nan($a)){
		$a="0";
	}
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>