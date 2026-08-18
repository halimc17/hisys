<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method', '');

#= ht
$notransaksi = checkPostGet('notransaksi', '');
$pt = checkPostGet('pt', '');
$jenis = checkPostGet('jenis', '');
$noakun = checkPostGet('noakun', '');
$kodebank = checkPostGet('kodebank', '');
$jumlahfasilitas = checkPostGet('jumlahfasilitas', '');
$jangkawaktu=tanggalsystemn(checkPostGet('jangkawaktu',''));
if($jangkawaktu=='--'){
	$jangkawaktu='';
}
$jatuhtempo = checkPostGet('jatuhtempo', '');
$periode = checkPostGet('periode', '');

$notransaksisch = checkPostGet('notransaksisch', '');
$ptsch = checkPostGet('ptsch', '');
$jenissch = checkPostGet('jenissch', '');
$noakunsch = checkPostGet('noakunsch', '');
$periodesch = checkPostGet('periodesch', '');

$nmakun=  makeOption($dbname, 'keu_5akunbank', 'noakun,rekening');

//$namaorganisasi=  makeOption($dbname, 'organisasi', 'induk,namaorganisasi');

#= insert
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
if($tanggal=='--'){
	$tanggal='';
}
$saldoakhir = checkPostGet('saldoakhir', '');
$bunga = checkPostGet('bunga', '');
$totalbunga = checkPostGet('totalbunga', '');
$optpt=$optjatuhtempo=$optjenis=$optnoakun=$optnotransaksi=$optper="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
for($x=0;$x<=12;$x++){
	$dte=mktime(0,0,0,(date('m')+2)-$x,15,date('Y'));
	$optper.="<option value=".date("Y-m",$dte).">".date("m-Y",$dte)."</option>";
}
function getbunga($arr,$kodebank,$periode,$tipePinjaman){
	$result = "0";
	for($i=0; $i<count($arr); $i++){
		if($tipePinjaman=='KISI'){
			if($arr[$i]['kodebank'] == $kodebank and substr($arr[$i]['periode'],0,7) == $periode){
				$result = $arr[$i]['nilai'];
			}	
		}else{
			if($arr[$i]['kodebank'] == $kodebank and $arr[$i]['periode'] == $periode){
				$result = $arr[$i]['nilai'];
			}
		}
		
	}
	return $result;
}
switch ($method){
	case 'getDataPinjaman':
		$databunga = array();
		
		$str="SELECT a.*,b.namabank as kodebank,b.rekening as noaccount from ".$dbname.".keu_pmpeminjamanht a 
		left join keu_5akunbank	b on a.noakun = b.noakun where a.notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$pinjaman=$res->fetch();
		$tipePencairanPokok=$pinjaman['tp_pokok'];
		$noAccount=$pinjaman['noaccount'];
		$kodenamabank=$pinjaman['kodebank'];

		$strak="SELECT jumlah_hari FROM `keu_5daftarbank` WHERE `kodebank`='".$kodenamabank."'";
		$resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
		$resak->setFetchMode(PDO::FETCH_ASSOC);
		$barak=$resak->fetch();
		$jumlahharibank=$barak['jumlah_hari'];

		if ($jumlahharibank=='') {
			exit('warning : Jumlah hari bank kosong. Silahkan setting di menu keuangan > setup > daftar nama bank.');
		}

		$str="SELECT kodebank,periode,nilai from ".$dbname.".keu_pmsukubunga order by periode";
		$rbungas=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$rbungas->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$rbungas->fetch()){ 
			$databunga[] = $bar;
		}
		
		$str="SELECT sum(jumlah) as tercairkan from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$pencairan=$res->fetch();
		
		
		$title = "";
		if($pinjaman['jenis'] == "KRK"){
			$title = "Kredit Rekening Koran (KRK)";
		}else if($pinjaman['jenis'] == "KISI"){
			$title = "Kredit Investasi Konstruksi (KISI)";
		}
		$sisa = $pinjaman['jumlahfasilitas'];
		if(is_numeric($pencairan['tercairkan'])){
			$sisa = $pinjaman['jumlahfasilitas'] - $pencairan['tercairkan'];
		}
		$jangkawaktu = "";
		if(isset($pinjaman['jangkawaktu'])){
			$jangkawaktu = tglnmbln($pinjaman['jangkawaktu'],'','');
		}
		#create periode berdasarkan jumlah bulan
		//$tgljatuhtempo=date('Y-m-d', strtotime('+1 month', $dt1));
		$listPeriode=array();
		
		if($pinjaman['jenis'] == "KISI"){
			$optPeriode="<select id=periode style=width:150px onchange=\"getdata('".$notransaksi."',this.value)\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";

			$sAwalCair="select * from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."' order by tanggal asc limit 1";
			//echo $sAwalCair;
			$rAwalCair=fetchData($sAwalCair);
			$awalcair=strtotime($rAwalCair[0]['tanggal']);
			for($awal=1;$awal<=$pinjaman['jumlahbulan'];$awal++){
				if($awal==1){
					$isiTgl=explode("-", $tglBuatDpnnya);
					if($isiTgl[1]=="01"){
						$tglBuatDpnnya=$isiTgl[0]."-".$isiTgl[1]."-01";
					}
					$tglBuatDpnnya=date('Y-m-d',strtotime('+1 month', $awalcair));
					$tglBerikut=date('Y-m', strtotime($tglBuatDpnnya));
					$listPeriode[$tglBerikut]=$tglBerikut;
				}else{
					$isiTgl=explode("-", $tglBuatDpnnya);
					if($isiTgl[1]=="01"){
						$tglBuatDpnnya=$isiTgl[0]."-".$isiTgl[1]."-01";
					}
					$tglMaju=strtotime($tglBuatDpnnya);
					$tglBuatDpnnya=date('Y-m-d', strtotime('+1 month', $tglMaju));
					$tglBerikut=date('Y-m', strtotime($tglBuatDpnnya));
					$listPeriode[$tglBerikut]=$tglBerikut;
					
				}
			}
			foreach ($listPeriode as $key) {
				if($periode==$key){
					$optPeriode.="<option value='".$key."' selected>".$key."</option>";
				}else{
					$optPeriode.="<option value='".$key."'>".$key."</option>";	
				}
			}
			$optPeriode.="</select>&nbsp;&nbsp;<img id=periode onclick=z.elSearch('periode',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'> ";
		}else{
			$optPeriode="<input type=text class=myinputtext id=periode onmousemove=setCalendar(this.id,'%Y-%m') value='".$periode."' onblur=\"getdata('".$notransaksi."',this.value)\" style=width:100px onkeypress=return false;  size=10 maxlength=10 />";
		}
		//
		echo "
			<table>
				<tbody>
					<tr>
						<td colspan='2'>".$title."</td>	
					</tr>
					<tr>
						<td>".$_SESSION['lang']['notransaksi']." </td><td>: ".$notransaksi."</td>
					</tr>
					<tr>
						<td>Jumlah Fasilitas </td><td>: Rp ".number_format($pinjaman['jumlahfasilitas'])."</td>
					</tr>
					<tr>
						<td>Sisa Fasilitas </td><td>: Rp ".number_format($sisa)."</td>
					</tr>
					<tr>
						<td>Jangka Waktu </td><td>: ".$jangkawaktu."</td>
					</tr>
					<tr>
						<td>Jatuh Tempo Kredit </td><td>: Tgl ".$pinjaman['jatuhtempo']." Setiap Bulan<input type=hidden id=tglAkhir value='".$rAwalCair[0]['tanggal']."' /></td>
					</tr>
					<tr>
						<td>Periode</td>
						<td>:
							".$optPeriode."
						</td>
					</tr>
			</tbody>
			</table>";
			if($periode != ""){
				$daftarjumlahhari=  makeOption($dbname, 'keu_5daftarbank', 'kodebank,jumlah_hari');
				$str="SELECT * from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and periode='".$periode."'";
				//echo $str;
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$totalbunga = 0;
				while($bar=$res->fetch()){ 
					$totalbunga += $bar['bunga'];
				}
				if($pinjaman['jenis'] == "KRK"){
				echo'
					<table cellpading=1 cellspacing=1 border=0 class=sortable>
						<thead>
							<tr>
								<th>No.</th>
								<th>Tanggal</th>
								<th>Saldo Akhir [Minus]</th>
								<th>Bunga</th>
								<th>Total Bunga</th>
								<th colspan=2>#</th>
							</tr>
						</thead>';
						
					echo '<tbody>
							<tr class=>
								<td>#</td>
								<td ><input type=text class=myinputtext id=tanggal onchange="getbunga(this.value,\''.$pinjaman['kodebank'].'\')" onmousemove=setCalendar(this.id) style=width:100px  onkeypress=return false;  size=10 maxlength=10 readonly/></td>
								<td><input type=text class="myinputtextnumber" id=saldoakhir onkeyup="gettotalbunga(this.value,\''.$jumlahharibank.'\');"/></td>
								<td align=center><span id=strbunga></span><input type=hidden class=myinputtext id=bunga readonly/></td>
								<td align=right><span id=strtotalbunga></span><input type=hidden class=myinputtext id=totalbunga readonly/></td>jumlahharibank
								<td>
									<img id="detail_add" title="Simpan" class="zImgBtn" onclick="toSubmit(this)" src="images/save.png">
								</td>
								<td>
									<img src="images/clear.png" class="zImgBtn" title="Clear" onclick="cleardt();">
								</td>
							</tr>';
							
					$str="SELECT * from ".$dbname.".keu_pmkalkulasi where notransaksi='".$notransaksi."' and periode='".$periode."' order by tanggal";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$number = 1;
					$total = 0;
					while($bar=$res->fetch()){ 
						$total += $bar['totalbunga'];
						echo "<tr class=rowcontent>
								<td align=center>".$number."</td>
								<td align=center>".date("d-m-Y",strtotime($bar['tanggal']))."</td>
								<td align=right>".number_format($bar['saldoakhir'])."</td>
								<td align=center>".$bar['bunga']." %</td>
								<td align=right>".number_format($bar['totalbunga'])."</td>";
						echo 	'<td></td>
								<td><img title="delete" class="zImgBtn" onclick="deletedata(\''.$notransaksi.'\',\''.$periode.'\',\''.$bar['tanggal'].'\');" src="images/skyblue/delete.png"></td>
							</tr>';
						$number++;
					}
						echo "<tr class=rowcontent>
							  <td align=center colspan='4'>TOTAL</td>
							  <td align=right colspan='1'>".number_format($total)."</td>
							  <td></td>
							  <td></td>
							  </tr>";						
						echo "</tbody><tfoot>";
						
						$tab.="<tr class=>
								<td align='right' colspan='4'>Bunga (Rekening Koran)&nbsp;&nbsp;</td>
								<td align=right>".number_format($totalbunga)."</td>
								<td></td>
								  <td></td>
							</tr>";
						$Variance = $totalbunga-$total;
						$tab.="<tr class=>
								<td align='right' colspan='4'>Variance&nbsp;&nbsp;</td>
								<td align=right>".number_format($Variance)."</td>
								<td></td>
								<td></td>
							</tr></tfoot></table>";
						echo $tab;
					}else{
						$tanggaljatuhtempo = $pinjaman['jatuhtempo'];
						// if periode 2012-11
						$periodedate = $periode."-".$tanggaljatuhtempo;#bulan berjalan
						$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
						$periodedate2 = date("Y-m-d",strtotime("+2 Month",strtotime($periodedate1)));
						
						$totaldayHari = datediff($periodedate1,$periodedate);#plafon jumlah hari
						$jmlhari = 0;
						if(isset($daftarjumlahhari[$pinjaman['kodebank']])){
							$jmlhari = $daftarjumlahhari[$pinjaman['kodebank']];
						}
						switch ($tipePencairanPokok) {
							case '0':
								$tab.="
									<table cellpading=1 cellspacing=1 border=0 class=sortable>
									<thead>
										<tr>
										<td colspan=12 align=center>".$_SESSION['lang']['angsuran']."</td>
										</tr>
										<tr class=rowheader>
											<td align=center>Ang. Ke</td>
											<td align=center>".$_SESSION['lang']['nopeminjaman']."</td>
											<td align=center>".$_SESSION['lang']['tanggalpencairan']."</td>
											<td align=center>".$_SESSION['lang']['outstandingloan']."</td>
											<td align=center>".$_SESSION['lang']['pokokhutang']."</td>
											<td align=center>".$_SESSION['lang']['sukubunga']."</td>
											<td align=center colspan=2>".$_SESSION['lang']['periode']."</td>
											<td align=center>".$_SESSION['lang']['harihutang']."</td>
											<td align=center>Bunga</td>
											<td align=center>".$_SESSION['lang']['totalbunga']."</td>
											<td align=center>".$_SESSION['lang']['pokokhutang']."+".$_SESSION['lang']['totalbunga']."</td>
										</tr>	
									</thead>";
									#ambill kodebank
									$sBank="select * from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$notransaksi."'";
									$rBank=fetchData($sBank);


									#ambil nilai pokok per noloan per periode
									#periode sebenernya dimundurin sebulan,karena pemotongan nilai pokok dibulan berikutnya
									//$sPokok="select noloan,sum(pokok) as angPokok from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and periode<='".substr($periodedate1,0,7)."' group by noloan";
									$sPokok="select noloan,sum(rupiahangsuran) as angPokok from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and periode<='".substr($periodedate1,0,7)."' group by noloan";
									$rPokok=fetchData($sPokok);
									foreach ($rPokok as $key => $val) {
										$rupPokok[$val['noloan']]=$val['angPokok'];#angka untuk pengurang hutang
									}

									//$sPokok="select noloan,pokok as angPokok,tenor,sukubunga from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and periode='".$periode."' ";
									#ambil nilai pokok per pencairan atau per noloan
									$sPokok="select noloan,rupiahangsuran as angPokok,bulanke as tenor from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and periode='".$periode."' ";
									//echo $sPokok;
									$rPokok=fetchData($sPokok);
									foreach ($rPokok as $key => $val) {
										$rupPokokDisplay[$val['noloan']]=$val['angPokok'];#angka untuk kepentingan display
										$ruptenDisplay[$val['noloan']]=$val['tenor'];#angka untuk kepentingan display
										$bungaNya[$val['noloan']]=$val['sukubunga'];
									}
									// $arrBunga=array();
									// $sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where  periode between '".$periodedate1."' and '".$periodedate."' and notransaksipm='".$notransaksi."' order by periode asc";
									// //echo $sBungaKisi;
									// $rBungaKisi=fetchData($sBungaKisi);
									// foreach($rBungaKisi as $row=>$isi){
									// 	$arrBunga['tanggal'][]=$isi['periode'];
									// 	$arrBunga['bunga'][]=$isi['nilai'];
									// }
									// echo"<pre>";
									// print_r($arrBunga);
									// echo"</pre>";
									#realisasi bunga
									$sRealisasi="select sum(bunga) as bungarealisasi from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and periode='".$periode."'";
									$rRealisasi=fetchData($sRealisasi);
									$totBungaRealisasi=$rRealisasi[0];
									#query ambiil data pencairan
									$str=" select * from ".$dbname.".keu_pmpeminjamandt_pencairan 
									where  notransaksi='".$notransaksi."' and tanggal between '".$_POST['tglAkhir']."' and '".$periodedate."' order by tanggal asc";
									//echo $str;
									$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
									$res->setFetchMode(PDO::FETCH_ASSOC);
									$jumlah = 0;
									$Alltotalbunga = 0;
									
									$awlItung=0;
									//$sukub = getbunga($databunga,$pinjaman['kodebank'],$periode,$pinjaman['jenis']);

									while($bar=$res->fetch()){
										// $sukub=$bungaNya[$bar['noloan']];
										if($bar['tgl_jatuhtempo']!='00'){
											$periodedate = $periode."-".$bar['tgl_jatuhtempo'];#bulan berjalan
											$periodedate1=date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
										}else{
											$periodedate = $periode."-".$tanggaljatuhtempo;#bulan berjalan
											$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
										}
										
										#cari tanggal bunga dibawah range
										$rBungaKisi=$arrBunga=array();
										$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <='".$periodedate1."' and notransaksipm='".$notransaksi."' order by periode asc";
										// echo $sBungaKisi;
										$rBungaKisi=fetchData($sBungaKisi);
										if(count($rBungaKisi)!=0){
											foreach($rBungaKisi as $row=>$isi){
												if($periodedate1>$isi['periode']){
													$arrBunga['tanggal']=array(); #reset selalu hanya satu paling terakhir
													$arrBunga['bunga']=array();#reset selalu hanya satu paling terakhir
													$isi['periode']=$periodedate1;
												}
												$arrBunga['tanggal'][]=$isi['periode'];
												$arrBunga['bunga'][]=$isi['nilai'];	
											}
										}
										$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <='".$periodedate."' and notransaksipm='".$notransaksi."' order by periode asc";
										// echo $sBungaKisi;
										$rBungaKisi=fetchData($sBungaKisi);
										if(count($rBungaKisi)!=0){
											foreach($rBungaKisi as $row=>$isi){
												if(count($arrBunga['tanggal'])!=0){
													$crTgl=array_search($isi['periode'], $arrBunga['tanggal']);
													if($arrBunga['tanggal'][$crTgl]==$isi['periode']){
														continue;
													}	
												}
												if($periodedate1>$isi['periode']){
													continue;
												}

												$arrBunga['tanggal'][]=$isi['periode'];
												$arrBunga['bunga'][]=$isi['nilai'];
											}
										}
										// $str=" select nilai from ".$dbname.".keu_pmsukubunga where  left(periode,7)>='".substr($tglAwalPencairan,0,7)."' and left(periode,7)<='".substr($periodedate,0,7)."' and notransaksipm='".$notransaksi."' order by periode desc limit 1";
										$str=" select nilai from ".$dbname.".keu_pmsukubunga where periode>='".$tglAwalPencairan."' and periode<='".$periodedate."' and notransaksipm='".$notransaksi."' order by periode desc limit 1";
										// echo $str;
										$rSukuB=fetchData($str);
										$sukub=$rSukuB[0]['nilai'];
										$hasilbunga = (($bar['jumlah']-$rupPokok[$bar['noloan']])*$sukub/100);
										$jumlah += $bar['jumlah'];
										$rowspanDt=1;
										$pokBunga[$bar['noloan']]=$rupPokokDisplay[$bar['noloan']];
										if(count($arrBunga['tanggal'])<2){
											
											@$nopencairan+=1;
											$totalday_ = array();
											if($bar['tanggal']<$periodedate&&$bar['tanggal']>$periodedate1){
												// jika tanggal lebih kecil atau lebih besar dari tangal jatuh tempo tiap bulannya
												//maka jumlah hari bukan sebulan alias harus proporsi
												$periodedate1_ = $bar['tanggal'];
												$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
												$totalday_ = datediff($periodedate1_,$periodedate2_);
											}else{
												$periodedate1_ = $periodedate1;
												$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
												$totalday_ = datediff($periodedate1_,$periodedate2_);
											}
											$totalbunga = ($hasilbunga*$totalday_['days_total'])/(int)$jmlhari;
											$Alltotalbunga += $totalbunga;
											$pokBunga[$bar['noloan']]+=$totalbunga;
											$totBunga[$bar['noloan']]=$totalbunga;
											$tab.="<tr class=rowcontent>
												<td rowspan=".$rowspanDt.">".$ruptenDisplay[$bar['noloan']]."</td>
												<td rowspan=".$rowspanDt.">".$bar['noloan']."</td>
												<td rowspan=".$rowspanDt.">".tglnmbln($bar['tanggal'],'','')."</td>
												<td align=right rowspan=".$rowspanDt.">".number_format(($bar['jumlah']-$rupPokok[$bar['noloan']]))."</td>
												<td align=right rowspan=".$rowspanDt.">".number_format($rupPokokDisplay[$bar['noloan']])."</td>";
												$tab.="<td align=right>".number_format($sukub,2)."%</td>
												<td align=right>".tglnmbln($periodedate1_,'','')."</td>
												<td align=right>".tglnmbln($periodedate2_,'','')."</td>
												<td align=right>".$totalday_['days_total']."</td>
												<td align=right>".number_format($totalbunga)."</td>
												<td align=right>".number_format($totalbunga)."</td>";
										$tab.="<td align=right rowspan=".$rowspanDt.">".number_format($pokBunga[$bar['noloan']])."</td></tr>";
										}else{#jika tanggal bunga lebih dari satu
											$rowspanDt=count($arrBunga['tanggal']);
											$totalday_ = array();
											$totHari=count($arrBunga['tanggal'])-1;
											#foreach buat cari total bunga
											foreach ($arrBunga['tanggal'] as $key => $val){
														if($totHari==$key){#jika tothari sama dengan index array,mencari index terakhir
															$indSblm=$totHari-1;
															if($tglBerikutNya==$val){
																$periodedate1_ = $tglBerikutNya;	
															}else{
																$periodedate1_ = $arrBunga['tanggal'][$indSblm];	
															}
															
															if($periodedate!=$val){#jika tanggal terakhir tidak sama dengan nilai array terakhir maka diisi dengan tanggal akhir
																$val=$periodedate;
															}
														}else{
															if($key!=0){
																$periodedate1_ = $tglBerikutNya;	
															}else{
																$periodedate1_ = $periodedate1;	
																if($periodedate1>$val){
																	$val=$arrBunga['tanggal'][($key+1)];
																	$tglBerikutNya=$arrBunga['tanggal'][($key+1)];
																}
																else if($periodedate1==$val){#jika tanggal bunga sama dengan tanggal mulai continue row dikurangi 1
																	$val=$arrBunga['tanggal'][($key+1)];
																	$tglBerikutNya=$arrBunga['tanggal'][($key+1)];
																	// $tglBerikutNya=$val;
																	// $rowspanDt=$rowspanDt-1;
																	// continue;
																}else{
																	$tglBerikutNya=$val;
																}
															}						
															//$tglBerikutNya=$val;
														}
														$periodedate2_ = date("Y-m-d",strtotime($val));
														//$periodedate2_=date("Y-m-d",strtotime($val));
														$totalday_ = datediff($periodedate1_,$periodedate2_);
													
													$hasilbunga = (($bar['jumlah']-$rupPokok[$bar['noloan']])*$arrBunga['bunga'][$key]/100);
													$totalbunga = ($hasilbunga*$totalday_['days_total'])/(int)$jmlhari;
													$totBunga[$bar['noloan']]+=$totalbunga;
											}
											#foreach buat display bunga
											foreach ($arrBunga['tanggal'] as $key => $val) {
														if($totHari==$key){#jika tothari sama dengan index array,mencari index terakhir
															$indSblm=$totHari-1;
															if($tglBerikutNya==$val){
																$periodedate1_ = $tglBerikutNya;	
															}else{
																$periodedate1_ = $arrBunga['tanggal'][$indSblm];	
															}
															
															if($periodedate!=$val){#jika tanggal terakhir tidak sama dengan nilai array terakhir maka diisi dengan tanggal akhir
																$val=$periodedate;
															}
														}else{
															if($key!=0){
																$periodedate1_ = $tglBerikutNya;	
															}else{
																$periodedate1_ = $periodedate1;	
																if($periodedate1>$val){
																	$val=$arrBunga['tanggal'][($key+1)];
																	$tglBerikutNya=$arrBunga['tanggal'][($key+1)];
																}
																else if($periodedate1==$val){#jika tanggal bunga sama dengan tanggal mulai continue row dikurangi 1
																	$val=$arrBunga['tanggal'][($key+1)];
																	$tglBerikutNya=$arrBunga['tanggal'][($key+1)];
																	// $tglBerikutNya=$val;
																	// $rowspanDt=$rowspanDt-1;
																	// continue;
																}else{
																	$tglBerikutNya=$val;
																}
															}						
															//$tglBerikutNya=$val;
														}
														$periodedate2_ = date("Y-m-d",strtotime($val));
														//$periodedate2_=date("Y-m-d",strtotime($val));
														$totalday_ = datediff($periodedate1_,$periodedate2_);
													
													$hasilbunga = (($bar['jumlah']-$rupPokok[$bar['noloan']])*$arrBunga['bunga'][$key]/100);
													$totalbunga = ($hasilbunga*$totalday_['days_total'])/(int)$jmlhari;
													
													$Alltotalbunga += $totalbunga;
													$pokBunga[$bar['noloan']]=$totBunga[$bar['noloan']]+$rupPokokDisplay[$bar['noloan']];
													$tab.="<tr class=rowcontent>";
													if($tempLoan!=$bar['noloan']){
														$tempLoan=$bar['noloan'];
													     $tab.="<td rowspan=".$rowspanDt.">".$ruptenDisplay[$bar['noloan']]."</td>
												          <td rowspan=".$rowspanDt.">".$bar['noloan']."</td>
												          <td rowspan=".$rowspanDt.">".tglnmbln($bar['tanggal'],'','')."</td>
												          <td align=right rowspan=".$rowspanDt.">".number_format(($bar['jumlah']-$rupPokok[$bar['noloan']]))."</td>
												          <td align=right rowspan=".$rowspanDt.">".number_format($rupPokokDisplay[$bar['noloan']])."</td>";
													
														$tab.="<td align=right>".number_format($arrBunga['bunga'][$key],2)."%</td>
																<td>".tglnmbln($periodedate1_,'','')."</td>
																<td>".tglnmbln($periodedate2_,'','')."</td>
																<td align=right>".$totalday_['days_total']."</td>
																<td align=right>".number_format($totalbunga)."</td>
																<td align=right rowspan=".$rowspanDt.">".number_format($totBunga[$bar['noloan']])."</td>
																<td align=right rowspan=".$rowspanDt.">".number_format($pokBunga[$bar['noloan']])."</td>";											
													}else{
														$tab.="<td align=right>".number_format($arrBunga['bunga'][$key],2)."%</td>
																<td>".tglnmbln($periodedate1_,'','')."</td>
																<td>".tglnmbln($periodedate2_,'','')."</td>
																<td align=right>".$totalday_['days_total']."</td>
																<td align=right>".number_format($totalbunga)."</td>";
													}
													$tab.="</tr>";	
											}
										}
										$totCaira+=($bar['jumlah']-$rupPokok[$bar['noloan']]);
										$totPokok+=$rupPokokDisplay[$bar['noloan']];
										$totPokBunga+=$pokBunga[$bar['noloan']];
										$totBungaAll+=$totBunga[$bar['noloan']];
										
									}
									$tab.="<tr class=rowcontent>
										<td colspan='3' align=right>TOTAL</td>
										<td align=right>".number_format($totCaira)."</td>
										<td align=right>".number_format($totPokok)."</td>
										<td colspan='5' align=right>&nbsp;</td>
										<td align=right>".number_format($totBungaAll)."</td>
										<td align=right>".number_format($totPokBunga)."</td>
									</tr>";
									$tab.="<tr>
										<td colspan='9' align=right>Bunga (Rekening Koran)</td>
										<td align=right>".number_format($totBungaRealisasi['bungarealisasi'])."</td>
									</tr>";
									$tab.="<tr>
										<td colspan='9' align=right>Variance</td>
										<td align=right>".number_format($totBungaRealisasi['bungarealisasi']-$Alltotalbunga)."</td>
									</tr>";
								$tab.="</table>";	
							break;
							case'1':
							#BRI Satu Pokok
							$tab="";
							$tanggaljatuhtempo = $pinjaman['jatuhtempo'];
							// if periode 2012-11
							$periodedate = $periode."-".$tanggaljatuhtempo;#bulan berjalan
							$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
							$periodedate2 = date("Y-m-d",strtotime("+2 Month",strtotime($periodedate1)));

							$sPokok="select sum(rupiahangsuran) as angPokok from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and periode<='".substr($periodedate1,0,7)."'";
							$rPokok=fetchData($sPokok);
							foreach ($rPokok as $key => $val) {
								$rupPokok=$val['angPokok'];#angka untuk pengurang hutang
							}

							#ambil nilai pokok per pencairan atau per noloan
							$sPokok="select noloan,rupiahangsuran as angPokok,bulanke as tenor,notransaksi from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and periode='".$periode."' ";
							//echo $sPokok;
							$rPokok=fetchData($sPokok);
							foreach ($rPokok as $key => $val) {
								$rupPokokDisplay[$val['notransaksi']]=$val['angPokok'];#angka untuk kepentingan display
								$ruptenDisplay[$val['notransaksi']]=$val['tenor'];#angka tenor untuk kepentingan display
							}
							
							$tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable>
									<thead>
										<tr>
										<td colspan=14 align=center>".$_SESSION['lang']['angsuran']."</td>
										</tr>
										<tr class=rowheader>
											<td align=center>No.</td>
											<td align=center>No. Account</td>
											<td align=center>Outstanding</td>
											<td align=center>Pencairan</td>
											<td align=center>Sisa Hutang</td>
											<td align=center>Pembayaran Pokok</td>
											<td align=center>Tanggal Pencairan</td>
											<td align=center>".$_SESSION['lang']['sukubunga']."</td>
											<td align=center colspan=2>".$_SESSION['lang']['periode']."</td>
											<td align=center>".$_SESSION['lang']['harihutang']."</td>
											<td align=center>Bunga</td>
											<td align=center>Pokok+Bunga</td>
											<td align=center>Jatuh Tempo Fasilitas</td>
										</tr>	
									</thead>";
									$sData="select * from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."' and tanggal<='".$periodedate."'";
									//echo $sData;
									$rData=fetchData($sData);
									$totRowData=$rwSpn=count($rData);
									$cairOutstanding=0;
									$lstRpPencairan=$outStand=array();
									$totOustanding=0;
									$nilSblmnya=0;
									$nourt=0;
									$tempLoan="";
									if(!empty($rData)){
										$statAkhirBkn=0;#untuk cek apakah ada pencairan di akhir loan
										foreach ($rData as $key => $val){
											if($totRowData!=0){
												$totRowData-=1;
											}
											$displaycairOutstand+=$val['jumlah'];#untuk display
											$lstRpPencairan[$val['noloan']]=$val['jumlah'];
											if(($val['tanggal']<=$periodedate)&&($val['tanggal']>=$periodedate1)){
												$periodedate1_ = $val['tanggal'];
												$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));	
												$totalday_ = datediff($periodedate1_,$periodedate2_);
												$outStand[$val['noloan']]=$val['jumlah'];
												$bnykoutstand+=1;
												if($totRowData==0){
													$statAkhirBkn=1;
													$lastLoan=$key;
												}
											}else{
												$periodedate1_ = $periodedate1;
												$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
												$totalday_ = datediff($periodedate1_,$periodedate2_);
												$totOustanding+=$val['jumlah'];
												$displayTotOustand+=$val['jumlah'];
												$testasaja+=1;
											}
										}
										$totRowData=count($rData);
										#cari nilai untuk bunga display
										foreach ($rData as $key => $val){
											if($totRowData!=0){
												$totRowData-=1;
											}
											#cari bunga yang aktif start
											if($val['tgl_jatuhtempo']!='00'){
												    #jika jatuh temponya memiliki tanggal berbeda dengan header
													$periodedate = $periode."-".$val['tgl_jatuhtempo'];#bulan berjalan
													$periodedate1=date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
													$tgljatuhtempoBri[$val['noloan']]=$periode."-".$val['tgl_jatuhtempo'];#bulan berjalan
											}else{
												$periodedate = $periode."-".$tanggaljatuhtempo;#bulan berjalan
												$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
												$tgljatuhtempoBri[$val['noloan']]=$periode."-".$tanggaljatuhtempo;#bulan berjalan
											}
											#cari tanggal bunga dibawah range
											$rBungaKisi=$arrBunga=array();
											$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <'".$periodedate1."' and notransaksipm='".$notransaksi."' order by periode asc";
											//echo $sBungaKisi;
											$rBungaKisi=fetchData($sBungaKisi);
											if(count($rBungaKisi)!=0){
												foreach($rBungaKisi as $row=>$isi){
													if($periodedate1>$isi['periode']){
														$arrBunga['tanggal']=array(); #reset selalu hanya satu paling terakhir
														$arrBunga['bunga']=array();#reset selalu hanya satu paling terakhir
														$isi['periode']=$periodedate1;
													}
													if($val['tanggal']>$isi['periode']){
														continue;
													}
													$arrBunga['tanggal'][]=$isi['periode'];
													$arrBunga['bunga'][]=$isi['nilai'];	
												}
											}
											$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <'".$periodedate."' and notransaksipm='".$notransaksi."' order by periode asc";
											//echo $sBungaKisi;
											$rBungaKisi=fetchData($sBungaKisi);
											if(count($rBungaKisi)!=0){
												foreach($rBungaKisi as $row=>$isi){
													if(count($arrBunga['tanggal'])!=0){
														$crTgl=array_search($isi['periode'], $arrBunga['tanggal']);
														if($arrBunga['tanggal'][$crTgl]==$isi['periode']){
															continue;
														}	
													}
													if($periodedate1>$isi['periode']){
														continue;
													}
													if($val['tanggal']>$isi['periode']){
														continue;
													}
													 
													$arrBunga['tanggal'][]=$isi['periode'];
													$arrBunga['bunga'][]=$isi['nilai'];
												}
											}
											#filter tanggal klo1 ada yang sama
											foreach ($arrBunga['tanggal'] as $keydt => $valTanggal) {
												if($valTanggal==$arrBunga['tanggal'][$keydt+1]){
													unset($arrBunga['tanggal'][$keydt+1]);
												}
											}
											$totalday_ = array();
											$totalbunga=0;
											
											if(count($arrBunga['tanggal'])<2){	
												if(($val['tanggal']<=$periodedate)&&($val['tanggal']>=$periodedate1)){
													// jika tanggal lebih kecil atau lebih besar dari tangal jatuh tempo tiap bulannya
													//maka jumlah hari bukan sebulan alias harus proporsi
													$periodedate1_ = $val['tanggal'];
													$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));	
													$totalday_ = datediff($periodedate1_,$periodedate2_);
													//$outStand[$val['noloan']]=$val['jumlah'];
												}else{
													$periodedate1_ = $periodedate1;
													$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
													$totalday_ = datediff($periodedate1_,$periodedate2_);
													//$totOustanding+=$val['jumlah'];
												}
												$str=" select nilai from ".$dbname.".keu_pmsukubunga where periode<'".$periodedate."' and notransaksipm='".$notransaksi."' order by periode desc limit 1";
												//echo $str;
												$rSukuB=fetchData($str);
												$sukub=$rSukuB[0]['nilai'];
												if(isset($outStand[$val['noloan']])){
													$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($sukub/100))/(int)$jmlhari;	
													$tampilan.="(((".$outStand[$val['noloan']].")*".$totalday_['days_total']."*(".$sukub."/100)))/".(int)$jmlhari."<br />";
												}else{
													if($totRowData==0){
														$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($sukub/100)))/(int)$jmlhari;	
														$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$sukub."/100)))/".(int)$jmlhari." gak ada statAkhirBkn \n\n\n";
													}
												}
												if($statAkhirBkn==1){
													$itung=$rwSpn-$testasaja;
													if($key==$itung){
														$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($sukub/100)))/(int)$jmlhari;	
														$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$sukub."/100)))/".(int)$jmlhari." adda statAkhirBkn \n\n\n";
													}
												}
												$grandTotBungaDisplay+=$totalbunga;
												
											}else{
												$rowspanDt=count($arrBunga['tanggal']);
												$totalday_ = array();
												$totHari=count($arrBunga['tanggal'])-1;
												foreach ($arrBunga['tanggal'] as $key2 => $val2){
													if($totHari==$key2){#jika tothari sama dengan index array,mencari index terakhir
														$indSblm=$totHari-1;
														if($tglBerikutNya==$val2){
															$periodedate1_ = $tglBerikutNya;
															$val2 = $periodedate;	
														}else{
															//exit('warning:'.$tglBerikutNya."__".$val2);
															if($tglBerikutNya<$val2){
																$periodedate1_ = $tglBerikutNya;	
																$val2 = $periodedate;
															}else{
																$periodedate1_ = $arrBunga['tanggal'][$indSblm];		
																$val2 = $periodedate;
															}
															
														}
														
														if($periodedate!=$val2){#jika tanggal terakhir tidak sama dengan nilai array terakhir maka diisi dengan tanggal akhir
															$val2=$periodedate;
														}
													}else{
														if($key2!=0){
															$periodedate1_ = $tglBerikutNya;	
														}else{
															$periodedate1_ = $periodedate1;	
															if($periodedate1>$val2){
																$val2=$arrBunga['tanggal'][($key2+1)];
																$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
															}
															else if($periodedate1==$val2){#jika tanggal bunga sama dengan tanggal mulai continue row dikurangi 1
																$val2=$arrBunga['tanggal'][($key2+1)];
																$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
																$val2=$arrBunga['tanggal'][($key2+1)];
																$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
																if($periodedate==$val2){
																	$val2 = date("Y-m-d",strtotime("-1 Days",strtotime($val2)));
																	$tglBerikutNya=$val2;
																}
															}else{
																$tglBerikutNya=$val2;
															}
														}						
														//$tglBerikutNya=$val;
													}
													$periodedate2_ = date("Y-m-d",strtotime($val2));
													//$periodedate2_=date("Y-m-d",strtotime($val));
													$totalday_ = datediff($periodedate1_,$periodedate2_);
													$nourt+=1;
													if($val['jumlah']!=$nilSblmnya){
														$nilSblmnya=$val['jumlah'];
														$cairOutstanding+=$val['jumlah'];		
														//$totOustanding=$cairOutstanding;	
													}
													 
													if(isset($outStand[$val['noloan']])){
														$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100))/(int)$jmlhari;	
														$tampilan.="(((".$outStand[$val['noloan']]."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
													}else{
														if($totRowData==0){
															$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;
															$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
														}
													}
													if($statAkhirBkn==1){
														$itung=(($rwSpn-1)-$testasaja);
														if($key==$itung){
															$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;	
															$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." adda statAkhirBkn \n\n\n";
														}
													}
													 
													$grandTotBungaDisplay+=$totalbunga;
												}
											}
										}
									}
									#display kalkulasinya
									if(!empty($rData)){
										$totRowData=count($rData);
										$nourt=0;
										$cairOutstanding=0;
										foreach ($rData as $key => $val) {
											if($totRowData!=0){
												$totRowData-=1;
											}
											#cari bunga yang aktif start
											if($val['tgl_jatuhtempo']!='00'){#jika jatuh temponya memiliki tanggal berbeda dengan header
													$periodedate = $periode."-".$val['tgl_jatuhtempo'];#bulan berjalan
													$periodedate1=date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
													$tgljatuhtempoBri[$val['noloan']]=$periode."-".$val['tgl_jatuhtempo'];#bulan berjalan
											}else{
												$periodedate = $periode."-".$tanggaljatuhtempo;#bulan berjalan
												$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));#bulan sebelumnya
												$tgljatuhtempoBri[$val['noloan']]=$periode."-".$tanggaljatuhtempo;#bulan berjalan
											}
											#cari tanggal bunga dibawah range
											$rBungaKisi=$arrBunga=array();
											$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <'".$periodedate1."' and notransaksipm='".$notransaksi."' order by periode asc";
											//echo $sBungaKisi;
											$rBungaKisi=fetchData($sBungaKisi);
											if(count($rBungaKisi)!=0){
												foreach($rBungaKisi as $row=>$isi){
													if($periodedate1>$isi['periode']){
														$arrBunga['tanggal']=array(); #reset selalu hanya satu paling terakhir
														$arrBunga['bunga']=array();#reset selalu hanya satu paling terakhir
														$isi['periode']=$periodedate1;
													}
													if($val['tanggal']>$isi['periode']){
														continue;
													}
													$arrBunga['tanggal'][]=$isi['periode'];
													$arrBunga['bunga'][]=$isi['nilai'];	
												}
											}
											$sBungaKisi="select * from ".$dbname.".keu_pmsukubunga where periode <'".$periodedate."' and notransaksipm='".$notransaksi."' order by periode asc";
											//echo $sBungaKisi;
											$rBungaKisi=fetchData($sBungaKisi);
											if(count($rBungaKisi)!=0){
												foreach($rBungaKisi as $row=>$isi){
													if(count($arrBunga['tanggal'])!=0){
														$crTgl=array_search($isi['periode'], $arrBunga['tanggal']);
														if($arrBunga['tanggal'][$crTgl]==$isi['periode']){
															continue;
														}	
													}
													
													if($periodedate1>$isi['periode']){
														continue;
													}
													if($val['tanggal']>$isi['periode']){
														continue;
													}
													 
													$arrBunga['tanggal'][]=$isi['periode'];
													$arrBunga['bunga'][]=$isi['nilai'];
												}
											}
											#filter tanggal klo1 ada yang sama
											foreach ($arrBunga['tanggal'] as $keydt => $valTanggal) {
												if($valTanggal==$arrBunga['tanggal'][$keydt+1]){
													unset($arrBunga['tanggal'][$keydt+1]);
												}
											}
											#proses loannya start
											if(count($arrBunga['tanggal'])<2){	
												$totalday_ = array();
												$totalbunga=0;
												if(($val['tanggal']<=$periodedate)&&($val['tanggal']>=$periodedate1)){
													// jika tanggal lebih kecil atau lebih besar dari tangal jatuh tempo tiap bulannya
													//maka jumlah hari bukan sebulan alias harus proporsi
													$periodedate1_ = $val['tanggal'];
													$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));	
													$totalday_ = datediff($periodedate1_,$periodedate2_);
													$outStand[$val['noloan']]=$val['jumlah'];
												}else{
													$periodedate1_ = $periodedate1;
													$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
													$totalday_ = datediff($periodedate1_,$periodedate2_);
													//$totOustanding+=$val['jumlah'];
												}
												$str=" select nilai from ".$dbname.".keu_pmsukubunga where  periode<'".$periodedate."' and notransaksipm='".$notransaksi."' order by periode desc limit 1";
													//echo $str;
												$rSukuB=fetchData($str);
												$sukub=$rSukuB[0]['nilai'];
												 
												$nourt+=1;
												$tab.="<tr class=rowcontent>";
												$tab.="<td>".$nourt."</td>";
												if($key==0){
													$tab.="<td rowspan='".$rwSpn."'>".$noAccount."</td>";	
													
												}
												
												$cairOutstandingDisply+=$val['jumlah'];	
												
												$tab.="<td align=right>".number_format($cairOutstandingDisply)."</td>";
												$tab.="<td align=right>".number_format($val['jumlah'])."</td>";
												if($key==0){
													$tab.="<td rowspan='".$rwSpn."'>".number_format(($displaycairOutstand-$rupPokok))."</td>";	
													$tab.="<td align=right rowspan='".$rwSpn."'>".number_format($rupPokokDisplay[$notransaksi])."</td>";	
												}
												$tab.="<td>".tglnmbln($val['tanggal'],'','')."</td>";
												$tab.="<td align=right>".number_format($sukub,2)."%</td>";
												$tab.="<td>".tglnmbln($periodedate1_,'','')."</td>
													   <td>".tglnmbln($periodedate2_,'','')."</td>
													   <td align=right>".$totalday_['days_total']."</td>";

												if(isset($outStand[$val['noloan']])){
													$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($sukub/100))/(int)$jmlhari;	
												}else{
													if($totRowData==0){
														$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($sukub/100)))/(int)$jmlhari;	
													}
												}
												
												if($statAkhirBkn==1){
													$itung=$rwSpn-($bnykoutstand+1);
													if($key==$itung){
														$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($sukub/100)))/(int)$jmlhari;	
														//__(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$sukub."/100)))/".(int)$jmlhari." klo mau test itungan
													}
												}
												$grandTotBunga+=$totalbunga;
												$tab.="<td align=right>".number_format($totalbunga)."</td>";
												if($key==0){
													$tab.="<td align=right rowspan='".$rwSpn."'>".number_format($grandTotBungaDisplay+$rupPokokDisplay[$notransaksi])."</td>";
												}
												$tab.="<td align=right>".tglnmbln($tgljatuhtempoBri[$val['noloan']],'','')."</td>";
												$tab.="</tr>";
											}else{
												$rowspanDt=count($arrBunga['tanggal']);
												$totalday_ = array();
												$totHari=count($arrBunga['tanggal'])-1;
												// echo"<pre>";
												// print_r($arrBunga['tanggal']);
												// echo"</pre>";
												foreach ($arrBunga['tanggal'] as $key2 => $val2){
													if($totHari==$key2){#jika tothari sama dengan index array,mencari index terakhir
														$indSblm=$totHari-1;
														if($tglBerikutNya==$val2){
															$periodedate1_ = $tglBerikutNya;
															$val2 = $periodedate;	
														}else{
															//exit('warning:'.$tglBerikutNya."__".$val2);
															if($tglBerikutNya<$val2){
																$periodedate1_ = $tglBerikutNya;	
																$val2 = $periodedate;
															}else{
																$periodedate1_ = $arrBunga['tanggal'][$indSblm];		
																$val2 = $periodedate;
															}
															
														}
														
														if($periodedate!=$val2){#jika tanggal terakhir tidak sama dengan nilai array terakhir maka diisi dengan tanggal akhir
															$val2=$periodedate;
														}
													}else{
														if($key2!=0){
															$periodedate1_ = $tglBerikutNya;	
														}else{
															 
															$periodedate1_ = $periodedate1;	
															if($periodedate1>$val2){
																$val2=$arrBunga['tanggal'][($key2+1)];
																$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
															}
															else if($periodedate1==$val2){#jika tanggal bunga sama dengan tanggal mulai continue row dikurangi 1
																$val2=$arrBunga['tanggal'][($key2+1)];
																//exit('warning:masuk'.$periodedate1);
																$tglBerikutNya=$arrBunga['tanggal'][($key2+1)];
																if($periodedate==$val2){
																	$val2 = date("Y-m-d",strtotime("-1 Days",strtotime($val2)));
																	$tglBerikutNya=$val2;
																}
															}else{
																$tglBerikutNya=$val2;
															}
														}						
														//$tglBerikutNya=$val;
													}
													$periodedate2_ = date("Y-m-d",strtotime($val2));
													//$periodedate2_=date("Y-m-d",strtotime($val));
													$totalday_ = datediff($periodedate1_,$periodedate2_);
													$nourt+=1;
													if($val['noloan']!=$tempNoloan){
														$cairOutstanding+=$lstRpPencairan[$val['noloan']];		
														$tempNoloan=$val['noloan'];
														//$totOustanding=$cairOutstanding;	
													}
													$totalbunga=0;
													$tab.="<tr class=rowcontent>";
													if($tempLoan!=$notransaksi){
														$tempLoan=$notransaksi;
														$tab.="<td>".$nourt."</td>";
														if($key==0){
															$tab.="<td rowspan='".(($testasaja*$rowspanDt)+$bnykoutstand)."'>".$noAccount."</td>";	
														}
														$tab.="<td align=right>".number_format($cairOutstanding)."</td>";
														$tab.="<td align=right>".number_format($lstRpPencairan[$val['noloan']])."</td>";
														if($key==0){
															$tab.="<td rowspan='".(($testasaja*$rowspanDt)+$bnykoutstand)."'>".number_format(($displaycairOutstand-$rupPokok))."</td>";	
															$tab.="<td align=right rowspan='".(($testasaja*$rowspanDt)+$bnykoutstand)."'>".number_format($rupPokokDisplay[$notransaksi])."</td>";	
														}
														$tab.="<td>".tglnmbln($val['tanggal'],'','')."</td>";
														$tab.="<td align=right>".number_format($arrBunga['bunga'][$key],2)."%</td>";
														$tab.="<td>".tglnmbln($periodedate1_,'','')."</td>
															   <td>".tglnmbln($periodedate2_,'','')."</td>
															   <td align=right>".$totalday_['days_total']."</td>";
														//$totalbunga = ($totOustanding*$totalday_['days_total'])/(int)$jmlhari;	
														if(isset($outStand[$val['noloan']])){
															$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100))/(int)$jmlhari;	
															$tampilan.="(((".$outStand[$val['noloan']]."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
														}else{
															if($totRowData==0){
																$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;
																$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
															}
														}
														if($statAkhirBkn==1){
															$totalbunga=0;
															$itung=$rwSpn-($bnykoutstand+1);
															if($key==$itung){
																$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;	
																$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." adda statAkhirBkn \n\n\n";
															}
														}
														$grandTotBunga+=$totalbunga;
														$tab.="<td align=right>".number_format($totalbunga)."</td>";
														if($key==0){
															$tab.="<td align=right rowspan='".(($testasaja*$rowspanDt)+$bnykoutstand)."'>".number_format(($grandTotBungaDisplay+$rupPokokDisplay[$notransaksi]))."</td>";
														}
														$tab.="<td align=right>".tglnmbln($tgljatuhtempoBri[$val['noloan']],'','')."</td>";
													}else{
														$tab.="<td>".$nourt."</td>";
														$tab.="<td align=right>".number_format($cairOutstanding)."</td>";
														$tab.="<td align=right>".number_format($val['jumlah'])."</td>";
														$tab.="<td>".tglnmbln($val['tanggal'],'','')."</td>";
														$tab.="<td align=right>".number_format($arrBunga['bunga'][$key2],2)."%</td>";
														$tab.="<td>".tglnmbln($periodedate1_,'','')."</td>
															   <td>".tglnmbln($periodedate2_,'','')."</td>
															   <td align=right>".$totalday_['days_total']."</td>";
														if(isset($outStand[$val['noloan']])){
															$totalbunga = ($outStand[$val['noloan']]*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100))/(int)$jmlhari;	
															$tampilan.="(((".$outStand[$val['noloan']]."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
														}else{
															if($totRowData==0){
																$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;
																$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." <br>";
															}
														}
														if($statAkhirBkn==1){
															$totalbunga=0;
															$itung=$rwSpn-($bnykoutstand+1);
															if($key==$itung){
																$totalbunga = ((($totOustanding-$rupPokok)*$totalday_['days_total']*($arrBunga['bunga'][$key2]/100)))/(int)$jmlhari;	
																$tampilan.="(((".$totOustanding."-".$rupPokok.")*".$totalday_['days_total']."*(".$arrBunga['bunga'][$key2]."/100)))/".(int)$jmlhari." adda statAkhirBkn \n\n\n";
															}
														}
														$grandTotBunga+=$totalbunga;
														$tab.="<td align=right>".number_format($totalbunga)."</td>";
														$tab.="<td align=right>".tglnmbln($tgljatuhtempoBri[$val['noloan']],'','')." </td>";
													}
													$tab.="</tr>";
												}
											}
										}
										$tab.="<tr class=rowcontent>";
										$tab.="<td colspan=2>".$_SESSION['lang']['total']."</td>";
										$tab.="<td align=right>".number_format($displaycairOutstand)."</td>";
										$tab.="<td>&nbsp;</td>";
										$tab.="<td align=right>".number_format($displaycairOutstand-$rupPokok)."</td>";
										$tab.="<td align=right>".number_format($rupPokokDisplay[$notransaksi])."</td>";
										$tab.="<td  colspan=5>&nbsp;</td>";
										$tab.="<td align=right>".number_format($grandTotBunga)."</td>";
										$tab.="<td align=right>".number_format($grandTotBunga+$rupPokokDisplay[$notransaksi])."</td>";
										$tab.="<td>&nbsp;</td>";
										$tab.="</tr>";
									}
							break;
						}
						
				echo $tab;
				//echo $grandTotBungaDisplay."\n";
				//echo $tampilan;
				}
			}
	break;
	case'getsukubunga':
		//$tanggal =date("Y-m",strtotime($tanggal));// periode<='".$tanggalpembayaranangsuran."' or periode>='".$tanggalpembayaranangsuran."' and kodebank='".$kodebank."' order by periode desc limit 1
		$str="SELECT nilai from ".$dbname.".keu_pmsukubunga where kodebank='".$kodebank."' and periode<='".$tanggal."' or periode>='".$tanggal."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$persent=$res->fetch();
		$prsn = 0;
		if(count($persent) > 0 ){
			$prsn = $persent['nilai'];
		}
		echo $prsn;
	break;
	case'savedatecalculate':
	if($notransaksi == "" or $periode == "" or $tanggal == "" or $saldoakhir == "" or $bunga == "" or $totalbunga == ""){
		$result['err'] = "true";
		$result['mssg'] = "Lengkapi field ygn tersedia"; 
	}else{
		$result = array();
		$str="INSERT INTO ".$dbname.".`keu_pmkalkulasi` (`notransaksi`,`periode`,`tanggal`, `saldoakhir`, `bunga`,`totalbunga`,createdby,createtime)
		VALUES ('".$notransaksi."','".$periode."', '".$tanggal."',
		'".$saldoakhir."','".$bunga."', '".$totalbunga."',
		'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
			$result['err'] = "false";
			$result['mssg'] = "";
		}
		catch (PDOException $e) {
		   $result['err'] = "true";
		   $result['mssg'] = " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	}
		echo json_encode($result);
	break;
	case'deletedatacalculate':
		$tanggal = date("Y-m-d",strtotime($_POST['tanggal']));
		$str="delete from ".$dbname.".`keu_pmkalkulasi` where notransaksi='".$notransaksi."' and periode = '".$periode."' and tanggal = '".$tanggal."'";
	
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	case'datapopupdetail':
		$tab="";
		$tab.="
			<table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%;'>
			<thead>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>".$_SESSION['lang']['saldoakhir']."</td>
					<td align=center>".$_SESSION['lang']['jumlahbunga']."</td>
					<td align=center>".$_SESSION['lang']['totalbunga']."</td>
				</tr>	
			</thead><tbody>";

			$tgljatuhtempo=$periode."-".$jatuhtempo;
            $dt1 = strtotime($tgljatuhtempo);
            $tanggalawal=date('Y-m-d', strtotime('-1 month', $dt1));
			$str="SELECT distinct * from ".$dbname.".keu_pmkalkulasi_vw where notransaksi='".$notransaksi."' and tanggal>'".$tanggalawal."' and tanggal<='".$tgljatuhtempo."' order by tanggal";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$total = 0;
			while($bar=$res->fetch()){ 
			@$no+=1;
			$total += $bar['totalbunga'];
				$tab.="<tr class=rowcontent>
					<td>".$no."</td>
					<td>".tglnmbln($bar['tanggal'],'','')."</td>
					<td align=right>".number_format($bar['saldoakhir'],2)."</td>
					<td align=right>".number_format($bar['bunga'],2)."</td>
					<td align=right>".number_format($bar['totalbunga'],2)."</td>
				</tr>";	
			}
			$tab.="</tbody><tfoot><tr class=rowcontent>
					<td align='right' colspan='4'>TOTAL&nbsp;&nbsp;</td>
					<td align=right>".number_format($total,2)."</td>
				</tr>";
			$str="SELECT * from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and periode='".$periode."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$totalbunga = 0;
			while($bar=$res->fetch()){ 
			$totalbunga += $bar['bunga'];
			}
			$tab.="<tr class=>
					<td align='right' colspan='4'>Bunga (Rekening Koran)&nbsp;&nbsp;</td>
					<td align=right>".number_format($totalbunga,2)."</td>
				</tr>";
			$Variance = $totalbunga-$total;
			$tab.="<tr class=>
					<td align='right' colspan='4'>Variance&nbsp;&nbsp;</td>
					<td align=right>".number_format($Variance,2)."</td>
				</tr></tfoot></table>";
			echo $tab;
	
	break;
	
	case'savedata':
	
		#delete 1st
		$str="delete from ".$dbname.".`keu_pmkalkulasi` where notransaksi='".$notransaksi."' and periode='".$periode."' and tanggal='".$tanggal."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	
		$str="INSERT INTO ".$dbname.".`keu_pmkalkulasi` (`notransaksi`,`periode`,`tanggal`, `saldoakhir`, `bunga`,`totalbunga`,createdby,createtime)
		VALUES ('".$notransaksi."','".$periode."', '".$tanggal."',
		'".$saldoakhir."','".$bunga."', '".$totalbunga."',
		'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	
	case'detaildata':
	
		$tgl1=periodelalu($periode).'-'.($jatuhtempo+1);
		$tgl2=$periode.'-'.($jatuhtempo);
		
		$arrtgl=rangeTanggalarr($tgl1,$tgl2);
		
		$carrtgl=count($arrtgl);

		$tab.="
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>".$_SESSION['lang']['saldoakhir']."</td>
					<td align=center>".$_SESSION['lang']['jumlahbunga']."</td>
					<td align=center>".$_SESSION['lang']['totalbunga']."</td>
				</tr>	
			</thead>";
			$tab.="<button class=mybutton onclick=saveall(".$carrtgl.");>".$_SESSION['lang']['proses']."</button>";
			foreach($arrtgl as $tgl){
				
				
				#= cari saldo akhir
					#= saldo awal periode tsb
					$str="SELECT awal".substr($tgl,5,2)." as sawal from ".$dbname.".keu_saldobulanan where noakun='".$noakun."' and periode='".str_replace('-','',substr($tgl,0,7))."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch(); 
						$sawal=$bar['sawal'];
						
					#= transaksi
					$str="SELECT jumlah,tipetransaksi from ".$dbname.".keu_kasbankht where noakun='".$noakun."' and tanggal between '".substr($tgl,0,7)."-01' and '".$tgl."'";				
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						if($bar['tipetransaksi']=='M')
							$transaksi+=$bar['jumlah'];
						else
							$transaksi-=$bar['jumlah'];
					}					
					
					$salak=$sawal+$transaksi;
				
				#= cari bunga
				$str="SELECT * from ".$dbname.".keu_pmsukubunga where noakun='".$noakun."' and periode='".substr($tgl,0,7)."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$bunga=$bar['nilai'];
				
				@$no+=1;
				$totalbunga=$bunga/100*$salak;
				$tab.="<tr class=rowcontent id=row".$no.">
					<td>".$no."</td>
					<td>".tglnmbln($tgl,'','')."</td>
					<td id=tanggal".$no." hidden>".tanggalnormal($tgl)."</td>
					<td align=right  id=saldoakhir".$no.">".number_format($salak,2)."</td>
					<td align=right  id=bunga".$no.">".number_format($bunga,2)."</td>
					<td align=right  id=totalbunga".$no.">".number_format($totalbunga,2)."</td>
				</tr>";
			}
					
			$tab.="</table>";	
		echo $tab;	
	break;
	
	
	case'getdata':
		$str="SELECT * from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		echo $bar['pt']."####".$bar['jenis']."####".$bar['noakun']."####".$bar['jumlahfasilitas']."####".tanggalnormal($bar['jangkawaktu'])."####".$bar['jatuhtempo'];
	break;
	
	
	
	case 'loaddata':
	
		$where='';
		$footd = '';
		if($notransaksisch!=''){
			$where.=" and notransaksi like '%".$notransaksisch."%'";
		}
		if($periodesch!=''){
			$where.=" and periode='".$periodesch."'";
		}
		if($ptsch!=''){
			$where.=" and kodeunit = '".$ptsch."'";
		}
		if($jenissch!=''){
			$where.=" and jenis = '".$jenissch."'";
		}
		if($noakunsch!=''){
			$where.=" and noakun = '".$noakunsch."'";
		}
	
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".keu_pmkalkulasi_vw where 1=1 ".$where." group by notransaksi,periode desc ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_pmkalkulasi_vw where 1=1 ".$where." group by notransaksi,periode desc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $whr="kodeorganisasi='".$bar['kodeunit']."'";
                $optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whr);
                @$no+=1;
                $tab.="<tr class=rowcontent>";
					$tab.="<td style='text-align:center;'>".$no."</td>";
					$tab.="<td>".$bar['notransaksi']."</td>";
					$tab.="<td>".$bar['periode']."</td>";
					$tab.="<td>".$optorg[$bar['kodeunit']]."</td>";
					$tab.="<td>".$bar['jenis']."</td>";
					$tab.="<td>".$nmakun[$bar['noakun']]."</td>";
					$tab.="<td align=right>".number_format($bar['jumlahfasilitas'])."</td>";
					$tab.="<td align=right>".tanggalnormal($bar['jangkawaktu'])."</td>";
					$tab.="<td align=center>";
						// $tab.="
                    	// <img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editht(
						// '".$bar['notransaksi']."','".$bar['pt']."','".$bar['jenis']."','".$bar['noakun']."',
						// '".$bar['jumlahfasilitas']."','".tanggalnormal($bar['jangkawaktu'])."','".$bar['jatuhtempo']."')\">
						// <img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteht('".$bar['notransaksi']."')\">"; 
						$tab.="<img src='images/skyblue/zoom.png' class='zImgBtn' onclick=\"popupdetail('".$bar['notransaksi']."','".$bar['periode']."','".$bar['jatuhtempo']."');\" title='Preview'>";
					$tab.="</td>";
				$tab.="</tr>";
            }
            $totrows=ceil($jlhbrs/$limit);
            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=10 align=center>
                <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;
	
	
	case'deleteht':
		$str="delete from ".$dbname.".`keu_pmkalkulasi` where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
    
    default;
	
}
?>