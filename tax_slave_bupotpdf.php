<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$no_buktipotong = checkPostGet('no_buktipotong', '');
$periode = checkPostGet('periode', '');
$kodeorg = checkPostGet('kodeorg', '');
$method = checkPostGet('method', '');

$tmpPrd=explode('-',$periode);
$prdN=$tmpPrd[1]."/".$tmpPrd[0];

$induk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$kodeorg."'");
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmnpwppt=makeOption($dbname,'setup_org_npwp','kodeorg,npwp');
switch($method){
	case'rekap':
		$tab='';
		$tab.="<table width=100% border=0>
				<tr><td>
					<table cellspacing=0 border=1 width=100%>
						<tr>
							<td align=center rowspan=2 width=100px><img style=width:75px;height:75px; src=images/logo-direktorat-jenderal-pajak.jpg></td>
							<td align=center style=width:150px;>DEPARTEMEN<br>KEUANGAN R.I.</td>
							<td align=center rowspan=2><b>DAFTAR BUKTI PEMOTONGAN<br>PPh PASAL 23 DAN/ATAU PASAL 26</b></td>
							<td align=center style=width:150px;>Masa Pajak</td>
						</tr>
						<tr>
							<td align=center>DIREKTORAT<br>JENDERAL PAJAK</td>
							<td align=center>
								<table border=1>
									<tr>";
									for($i=0;$i<strlen($prdN);$i++){
										$tab.="<td style=width:14px; align=center>".substr($prdN,$i,1)."</td>";
									}
								$tab.="</tr>
								</table>
							</td>
						</tr>
					</table>
				</td></tr>
				<tr><td>
					<table cellspacing=0 border=1 width=100%>
						<tr>
							<td rowspan=2 align=center style=width:30px;>No.</td>
							<td rowspan=2 align=center>NPWP</td>
							<td rowspan=2 align=center>Nama</td>
							<td colspan=2 align=center>Bukti Pemotongan</td>
							<td rowspan=2 align=center>Nilai Obyek<br>Pajak (Rp)</td>
							<td rowspan=2 align=center>PPh yang<br>Dipotong (Rp)</td>
						</tr>
						<tr>
							<td align=center>Nomor</td>
							<td align=center>Tanggal</td>
						</tr>
						<tr>
							<td style='background-color:#cccccc' align=center>(1)</td>
							<td style='background-color:#cccccc' align=center>(2)</td>
							<td style='background-color:#cccccc' align=center>(3)</td>
							<td style='background-color:#cccccc' align=center>(4)</td>
							<td style='background-color:#cccccc' align=center>(5)</td>
							<td style='background-color:#cccccc' align=center>(6)</td>
							<td style='background-color:#cccccc' align=center>(7)</td>
						</tr>";

					$induk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$kodeorg."'");
					$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
					$nmnpwppt=makeOption($dbname,'setup_org_npwp','kodeorg,npwp');
					$str = "SELECT * FROM " . $dbname . ".tax_buktipotongpajak where periode='".$periode."' and kodeorg in (select kodeorganisasi from organisasi where induk='".$induk[$kodeorg]."')";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$no='';
					while($bar = $res->fetch()){
						$kdorg=$bar['kodeorg'];
						$kb=$bar['notrans_kasbank'];

						$noakun[$bar['noakun']]=$bar['noakun'];
						$kodesupplier[$bar['noakun']]=$bar['kodesupplier'];
						$no_buktiptg[$bar['noakun']]=$bar['no_buktipotong'];
						$supplier[$bar['noakun']][$bar['kodesupplier']][$bar['no_buktipotong']]=$bar['kodesupplier'];
						$no_buktiptng[$bar['noakun']][$bar['kodesupplier']][$bar['no_buktipotong']]=$bar['no_buktipotong'];
						$kasbank[$bar['noakun']][$bar['kodesupplier']][$bar['no_buktipotong']]=$bar['notrans_kasbank'];
						$nop[$bar['noakun']][$bar['kodesupplier']][$bar['no_buktipotong']]=$bar['tarif_pajak'];
						$nilaipajak[$bar['noakun']][$bar['kodesupplier']][$bar['no_buktipotong']]=$bar['nilai'];
					}
					$no='';
					foreach($noakun as $kdakun){
						$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$kdakun."'");
						$tab.="<tr><td colspan=7>".$nmakun[$kdakun]."</td></tr>";
						foreach($kodesupplier as $kdsupp){
							foreach($no_buktiptg as $bupot){
								$tglkas=makeOption($dbname,'keu_kasbankht','notransaksi,tanggal',"notransaksi='".$kasbank[$kdakun][$kdsupp][$bupot]."'");
								$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier[$kdakun][$kdsupp][$bupot]."'");
								$nmnpwp=makeOption($dbname,'log_5supnpwp','supplierid,npwp',"supplierid='".$supplier[$kdakun][$kdsupp][$bupot]."'");
								$no++;
								$tab.="<tr><td align=center>".$no."</td>";
								$tab.="<td>".@$nmnpwp[$supplier[$kdakun][$kdsupp][$bupot]]."</td>";
								$tab.="<td>".@$nmsup[$supplier[$kdakun][$kdsupp][$bupot]]."</td>";
								$tab.="<td>".$no_buktiptng[$kdakun][$kdsupp][$bupot]."</td>";
								$tab.="<td align=center>".$tglkas[$kasbank[$kdakun][$kdsupp][$bupot]]."</td>";
								$tab.="<td align=right>".@number_format($nop[$kdakun][$kdsupp][$bupot])."</td>";
								$tab.="<td align=right>".@number_format($nilaipajak[$kdakun][$kdsupp][$bupot])."</td>";
								$tab.="</tr>";
								@$tnop+=$nop[$kdakun][$kdsupp][$bupot];
								@$tnilaipajak+=$nilaipajak[$kdakun][$kdsupp][$bupot];
							}
						}
						$tab.="<tr><td colspan=5 align=center><b>JUMLAH</b></td>";
						$tab.="<td align=right><b>".@number_format($tnop)."</b></td>";
						$tab.="<td align=right><b>".@number_format($tnilaipajak)."</b></td></tr>";
					}
				$tab.="</table>
				</td></tr>
				<tr><td>
					<table cellspacing=0 border=1 width=100%><tr><td width=66% rowspan=2>
						<table border=0  width=100%>
							<tr><td style=width:38%;height:35px style=vertical-align:center>
									<img class=resicon src=images/cekpajak.jpg style=width:20px;height:20px>
									PEMOTONG PAJAK/PIMPINAN
								</td>
								<td width=33%>
									<img class=resicon src=images/uncekpajak.jpg style=width:20px;height:20px>
									KUASA WAJIB PAJAK</td>
							</tr>";
						$tab.="<tr><td colspan=2>";
							$tab.="<table><tr>";
							$tab.="<td width=50px>Nama</td>";
							$tab.="<td>";
									$tab.="<table cellspacing=0 border=1><tr>";
										$len='';
										$len=25;
										for ($i=0;$i<$len;$i++){
											$tab.="<td style=width:15px align=center>&nbsp;</td>";
										}
									$tab.="</tr></table>";
							$tab.="</td>";
							$tab.="</tr></table>";

							$tab.="</td>
								</tr>";
							$tab.="<tr><td colspan=2>";
							$tab.="<table><tr>";
							$tab.="<td width=50px>NPWP</td>";
							$tab.="<td>";
									$tab.="<table cellspacing=0 border=1><tr>";
										$len=$ptptg='';
										@$ptptgnpwp = $nmnpwppt[$induk[$kdorg]];
										$len=strlen($ptptgnpwp);
										for ($i=0;$i<$len;$i++){
											$tab.="<td style=width:15px align=center>".substr($ptptgnpwp,$i,1)."</td>";
										}
									$tab.="</tr></table>";
							$tab.="</td>";
							$tab.="</tr></table>";
							$tab.="</td>
								</tr>";
							$tab.="<tr><td style=height:55px></td><td></td></tr>
						</table>";

					$tab.="</td><td style=vertical-align:center;>
								<table>
									<tr><td>
											<table cellspacing=0 border=1>
												<tr>";
												$tglx=makeOption($dbname,'keu_kasbankht','notransaksi,tanggal',"notransaksi='".$kb."'");
												$tglptg = tanggalnormal($tglx[$kb]);
												$tglptg = str_replace('-','',$tglptg);
												for ($i=0;$i<8;$i++){
													$tab.="<td style=width:15px align=center>".substr($tglptg,$i,1)."</td>";
												}
										$tab.="</tr>
												<tr>
													<td colspan=2 align=center>tanggal</td>
													<td colspan=2 align=center>bulan</td>
													<td colspan=4 align=center>tahun</td>
												</tr>
											</table>
										</td></tr>
								</table>
							</td></tr>

							<td style=vertical-align:top>
								<table>
									<tr><td>Tanda Tangan & Cap</td></tr>
								</table>
							</td></tr>

							</table>";

					$tab.="</td></tr>";
					$tab.="<tr><td><b>D.1.1.32.05</b></td></tr>
				</table>";
			#echo $tab;
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$dompdf->stream("rekap",array("Attachment"=>0));
	break;
	case'pph':
		$nmpph=array('2130101'=>'PPh PASAL 21',
					'2130201'=>'PPh PASAL 22',
					'2130301'=>'PPh PASAL 23',
					'2130401'=>'PPh PASAL 4(2)',
					'2130501'=>'PPh PASAL 29');
		$str = "SELECT * FROM " . $dbname . ".tax_buktipotongpajak where no_buktipotong='".$no_buktipotong."' and periode='".$periode."' and kodeorg ='".$kodeorg."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		$strx = "SELECT * FROM " . $dbname . ".log_5supnpwp where supplierid='".$bar['kodesupplier']."'";
		$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$barx = $resx->fetch();
		$jalan="Jln.".$barx['jalan'];
		$blok=",Blok.".$barx['blok'];
		$nomor=",No.".$barx['nomor'];
		$rt=",RT".$barx['rt'];
		$rw=",RW".$barx['rw'];
		$kec=",Kec.".$barx['kecamatan'];
		$kel=",Kel.".$barx['keluarahan'];
		$kab=",Kab.".$barx['kabupaten'];
		$prop=",Prop.".$barx['propinsi'];
		$kopos=",Kode Pos".$barx['kodepos'];
		$telp_no=$barx['telp_no'];

		$alamatsupp = $jalan.$blok.$nomor.$rt.$rw.$kec.$kel.$kel.$kab.$prop.$kopos;


		$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['kodesupplier']."'");
		@$nmnpwp=makeOption($dbname,'log_5supnpwp','supplierid,npwp',"supplierid='".$bar['kodesupplier']."'");

		$namasupp=$nmsup[$bar['kodesupplier']];
		$namasupp=str_replace(" ",".",$namasupp);
		@$npwpsupp=$nmnpwp[$bar['kodesupplier']];
		$npwpsupp=str_replace(" ","-",$npwpsupp);
		if($npwpsupp==''){
			$npwpsupp='npwp not found';
		}

		$tab='';
		if($bar['noakun']=='2130301'){
		$tab.="<table border=0   width='753px'>
				<tr><td colspan=2>
					<table cellspacing=0 border=0 style='margin-left:400px'>
						<tr><td></td><td style=width:220px;font-size:10px><b>Lembar ke-1 untuk : Wajib Pajak</b></td></tr>
						<tr><td></td><td style=width:220px;font-size:10px><b>Lembar ke-2 untuk : Kantor Pelayanan Pajak</b></td></tr>
						<tr><td></td><td style=width:220px;font-size:10px><b>Lembar ke-3 untuk : Pemotong Pajak</b></td></tr>
					</table>
				</td></tr>";
		$tab.="<tr><td colspan=2>
					<table cellspacing=0 border=0 >
						<tr><td style=width:50px><img style=width:50px;height:50px; src=images/logo-direktorat-jenderal-pajak.jpg>
							</td><td style=width:380px;font-size:13px align=center><b>DEPARTEMEN KEUANGAN REPUBLIK INDONESIA<br>DIREKTORAT JENDERAL PAJAK<br>KANTOR PELAYANAN PAJAK</b><br>...........................................................................<i>(1)</i></td>
							<td></td></tr>
					</table>
				</td></tr>";
		$tab.="<tr><td colspan=2>
					<table cellspacing=0 border=0 >";
						$tab.="<tr><td width=120px >&nbsp;</td><td align=center style='border:1px ridge  black;background-color:#b3b3b3;font-size:13px'><b>BUKTI PEMOTONGAN ".$nmpph[$bar['noakun']]."</b></td><td width=220px>&nbsp;</td></tr>";
						$tab.="<tr><td width=120px>&nbsp;</td><td align=center style='border:1px ridge black;background-color:#b3b3b3'><b><font size=2>Nomor : ".$bar['no_buktipotong']." </font></b><i>(2)</i></td><td width=220px>&nbsp;</td></tr>";
					$tab.="</table>
				</td></tr>";
		$tab.="<tr><td colspan=2>
					<table border=0>";
					$tab.="<tr><td width=20px >&nbsp;</td><td  style=width:80px;font-size:13px><b>NPWP</b></td><td>:</td><td>";
						$tab.="<table cellspacing=0 border=0><tr>";
						for ($i=0;$i<strlen($npwpsupp);$i++){
							if(is_numeric(substr($npwpsupp,$i,1))){
							$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>".substr($npwpsupp,$i,1)."</td>";
							}
							else
							{
							$tab.="<td style=width:15px align=center>&nbsp;</td>";
							}
						}
						$tab.="</tr></table>";
					$tab.="</td></tr>";
					$tab.="<tr><td width=20px >&nbsp;</td><td style=width:50px;font-size:13px><b>Nama</td><td>:</td><td>";
						$tab.="<table cellspacing=0 border=1><tr>";
						$len=29;
						for ($i=0;$i<$len;$i++){
							if($i>=strlen($namasupp)){
								$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>&nbsp;</td>";
							}else{
								$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>".substr($namasupp,$i,1)."</td>";
							}
						}
						$tab.="</tr></table>";
					$tab.="</td></tr>";
					$tab.="<tr><td width=20px >&nbsp;</td><td style=width:50px;font-size:13px><b>Alamat</td><td>:</td><td>";
						$tab.="<table cellspacing=0 border=1><tr>";
						$len=29;
						for ($i=0;$i<$len;$i++){
							if($i>=strlen($alamatsupp)){
								$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>&nbsp;</td>";
							}else{
								$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>".substr($alamatsupp,$i,1)."</td>";
							}
						}
						$tab.="</tr></table>";
					$tab.="</td></tr>";
				$tab.="</table>
				</td></tr>";
		$tab.="<tr><td colspan=2></td></tr>";
		$tab.="<tr><td  colspan=2>";

		$stri = "SELECT * FROM " . $dbname . ".pmn_5jenispenghasilan where kodepajak='".$bar['noakun']."' order by idpenghasilan asc";
		$resi = $owlPDO->query($stri) or die(print " Gagal: " . PDOException::getMessage());
		$resi->setFetchMode(PDO::FETCH_ASSOC);
		while($bari = $resi->fetch()){
			$jenispenghasil[$bari['kodepajak']][$bari['idpenghasilan']]=$bari['idpenghasilan'];
			$namahasil[$bari['kodepajak']][$bari['idpenghasilan']]=$bari['namapenghasilan'];
			$noparent[$bari['kodepajak']][$bari['idpenghasilan']]=$bari['nourutparent'];
			$mnldet[$bari['kodepajak']][$bari['idpenghasilan']]=$bari['manual'];
		}

		$str1 = "SELECT * FROM " . $dbname . ".tax_buktipotongpajak where no_buktipotong='".$no_buktipotong."' and periode='".$periode."' and kodeorg ='".$kodeorg."'";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		while($bar1 = $res1->fetch()){
			$jnsdetl[$bar1['jenisdetail']]=$bar1['jenisdetail'];
			$jnsdet[$bar1['noakun']][$bar1['jenis_penghasilan']][$bar1['jenisdetail']]=$bar1['jenisdetail'];
			@$trfpjk[$bar1['noakun']][$bar1['jenis_penghasilan']]+=$bar1['tarif_pajak'];
			@$trfpjkdet[$bar1['noakun']][$bar1['jenis_penghasilan']][$bar1['jenisdetail']]+=$bar1['tarif_pajak'];
			@$nilaipph[$bar1['noakun']][$bar1['jenis_penghasilan']]+=$bar1['nilai'];
			@$nilaipphdet[$bar1['noakun']][$bar1['jenis_penghasilan']][$bar1['jenisdetail']]+=$bar1['nilai'];
		}
		// echo"<pre>";
		// print_r($kdpajak);
		// echo"</pre>";
		$tab.="<table cellspacing=0 border='1' width='723px' style='margin-left:15px;'>
					<tr>
						<td align=center style=background-color:#b3b3b3;font-size:13px; width=30px><b>No.</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px; colspan=3  width=220px><b>Jenis Penghasilan<b/></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px width=120px><b>Jumlah Penghasilan<br>Bruto (Rp)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px width=80px><b>Tarif Lebih<br>Tinggi 100%<br>(Tdk ber-NPWP)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px width=30px><b>Tarif<br>%</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px width=160px><b>PPh yang Dipotong<br>(Rp)</b></td>
					</tr>
					<tr>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(1)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px; colspan=3  width=220px><b>(2)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(3)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(4)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(5)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(6)</b></td>
					</tr>";

				foreach($jenispenghasil as $kdpjk => $valpjk){
					foreach($valpjk as $jnshasil){
						$nd='';
						if($noparent[$kdpjk][$jnshasil]==0){
							$noparent[$kdpjk][$jnshasil]='';
						}
						if($mnldet[$kdpjk][$jnshasil]==1){
							foreach($jnsdetl as $jdet){
								if($jnsdet[$kdpjk][$jnshasil][$jdet]!=''){
									$nd+=1;
									$tab.="<tr>";
									$tab.="<td></td>";
									$tab.="<td colspan=3>&nbsp;&nbsp;<b>".$nd.") </b>".$jnsdet[$kdpjk][$jnshasil][$jdet]."</td>";
									$tab.="<td align=right>".@number_format($trfpjkdet[$kdpjk][$jnshasil][$jdet])."</td>";
									$tab.="<td align=center><img class=resicon src=images/uncekpajak.jpg style=width:20px;height:20px></td>";
									$tab.="<td align=right>".@number_format($trfpjkdet[$kdpjk][$jnshasil][$jdet]/$nilaipphdet[$kdpjk][$jnshasil][$jdet])."</td>";
									$tab.="<td align=right>".@number_format($nilaipphdet[$kdpjk][$jnshasil][$jdet])."</td>";
									$tab.="</tr>";

									@$jumlahdpp+=$trfpjkdet[$kdpjk][$jnshasil][$jdet];
									@$jumlahpph+=$nilaipphdet[$kdpjk][$jnshasil][$jdet];
								}
							}
						}else{
							$tab.="<tr>";
							$tab.="<td align=center>".$noparent[$kdpjk][$jnshasil]."</td>";
							$tab.="<td colspan=3>".$namahasil[$kdpjk][$jnshasil]."</td>";
							$tab.="<td align=right>".@number_format($trfpjk[$kdpjk][$jnshasil])."</td>";
							$tab.="<td align=center><img class=resicon src=images/uncekpajak.jpg style=width:20px;height:20px></td>";
							$tab.="<td align=right>".@number_format($trfpjk[$kdpjk][$jnshasil]/$nilaipph[$kdpjk][$jnshasil])."</td>";
							$tab.="<td align=right>".@number_format($nilaipph[$kdpjk][$jnshasil])."</td>";

							@$jumlahdpp+=$trfpjk[$kdpjk][$jnshasil];
							@$jumlahpph+=$nilaipph[$kdpjk][$jnshasil];
						}
						$tab.="</tr>";
					}
				}
			$tab.="<tr>
						<td colspan=4 align=center>Jumlah</td>
						<td align=right>".@number_format($jumlahdpp)."</td>
						<td align=right></td>
						<td align=right></td>
						<td align=right>".@number_format($jumlahpph)."</td>
					</tr>";
			$tab.="<tr>
						<td colspan=8 align=left>Terbilang : ".terbilang(@number_format($jumlahpph),1)." RUPIAH</td>
					</tr>";
			$tab.="</table>";
		$tab.="</td></tr>";
		$tab.="<tr>
				<td width=267px>
					<table  cellspacing=0 border=1>
						<tr><td style=vertical-align:top; width=185px><font size=2>
							Perhatian :<br>
							1.&nbsp;Jumlah Pajak Penghasilan Pasal 23
							yang dipotong di atas<br>&nbsp;&nbsp;&nbsp;&nbsp;merupakan
							angsuran atas Pajak Penghasilan yang
							terutang<br>&nbsp;&nbsp;&nbsp;&nbsp;untuk tahun pajak yang
							bersangkutan. Simpanlah bukti<br>&nbsp;&nbsp;&nbsp;&nbsp;pemotongan ini baik-baik untuk
							diperhitungkan sebagai kredit<br>&nbsp;&nbsp;&nbsp;&nbsp;pajak.<br>

							2.&nbsp;Bukti Pemotongan ini dianggap sah
							apabila diisi dengan<br>&nbsp;&nbsp;&nbsp;&nbsp;lengkap dan
							benar.<br>
							</font>
							</td></tr>
					</table>
				</td>
				<td style=vertical-align:top; rowspan=2 width=476px>
					<table>
						<tr><td colspan=2 align=center>Jakarta, ________________ </td></tr>
						<tr><td colspan=2 align=center><b>Pemotong Pajak</b></td></tr>
						<tr><td width=50px>NPWP:</td><td>";
						$tab.="<table cellspacing=0 border=0><tr>";
							$len=$ptptg='';
							@$ptptgnpwp = $nmnpwppt[$induk[$kodeorg]];
							$len=strlen($ptptgnpwp);
							for ($i=0;$i<$len;$i++){
								if(is_numeric(substr($ptptgnpwp,$i,1))){
								$tab.="<td style='border:1.5px solid black;width:10px;height:5px;text-align:center;font-size:12px'>".substr($ptptgnpwp,$i,1)."</td>";
								}
								else
								{
								$tab.="<td style=width:15px align=center>&nbsp;</td>";
								}
								//$tab.="<td align=center style=width:10px>".substr($ptptgnpwp,$i,1)."</td>";
							}
						$tab.="</tr></table>";
						$tab.="</td></tr>
						<tr><td width=50px>Nama:</td><td>";
						$tab.="<table cellspacing=0 border=1><tr>";
							$len='';
							@$nmpt = $nmorg[$induk[$kodeorg]];
							$len=20;
							for ($i=0;$i<$len;$i++){
								if($i>=strlen($nmpt)){
								$tab.="<td style='border:1.5px solid black;width:10px;height:5px;text-align:center;font-size:12px'>&nbsp;</td>";
								}else{
									$tab.="<td style='border:1.5px solid black;width:10px;height:5px;text-align:center;font-size:12px'>".substr($nmpt,$i,1)."</td>";
								}
								//$tab.="<td align=center style=width:10px>".substr($nmpt,$i,1)."</td>";
							}
						$tab.="</tr></table>";

						$tab.="</td></tr>";
						$tab.="<tr><td colspan=2 align=center><b>Tanda Tangan, Nama dan Cap</b></td></tr>";
						$tab.="<tr><td colspan=2>&nbsp;</td></tr>";
						$tab.="<tr><td colspan=2>&nbsp;</td></tr>";
						$tab.="<tr><td colspan=2 align=center>......................................</td></tr>";
				$tab.="</table>
				</td>
				</tr>";
		$tab.="<tr><td style=vertical-align:top><font size=1><i>
							*)  Tidak termasuk dividen kepada WP Orang Pribadi dalam negeri.<br>
							**)  Tidak termasuk bunga simpanan yang dibayarkan oleh koperasi kepada anggota WP Orang Pribadi.<br>
							***)  Kecuali sewa tanah dan bangunan.<br>
							****)  Apabila kurang harap diisi sendiri.
							</i></font></td></tr>";
		$tab.="<tr><td colspan=2><b>F.1.1.33.06</b></td></tr>";

		$tab.="</table>";
		}
		elseif($bar['noakun']=='2130201')
		{
			$tab.="<table border=0   >
				<tr><td colspan=2>
					<table cellspacing=0 border=0 style='margin-left:400px'>
						<tr><td></td><td style=width:320px;font-size:10px><b>Lembar ke-1 untuk : Wajib Pajak</b></td></tr>
						<tr><td></td><td style=width:320px;font-size:10px><b>Lembar ke-2 untuk : Kantor Pelayanan Pajak</b></td></tr>
						<tr><td></td><td style=width:320px;font-size:10px><b>Lembar ke-3 untuk : Pemotong Pajak</b></td></tr>
					</table>
				</td></tr>";
		$tab.="<tr><td colspan=2>
					<table cellspacing=0 border=0 >
						<tr><td style=width:50px><img style=width:50px;height:50px; src=images/logo-direktorat-jenderal-pajak.jpg>
							</td><td style=width:380px;font-size:13px align=center><b>DEPARTEMEN KEUANGAN REPUBLIK INDONESIA<br>DIREKTORAT JENDERAL PAJAK<br>KANTOR PELAYANAN PAJAK</b><br>...........................................................................<i>(1)</i></td>
							<td></td></tr>
					</table>
				</td></tr>";
		$tab.="<tr><td colspan=2>
					<table cellspacing=0 border=0 >";
						$tab.="<tr><td width=120px >&nbsp;</td><td align=center style='border:1px ridge  black;background-color:#b3b3b3;font-size:13px'><b>BUKTI PEMUNGUTAN ".$nmpph[$bar['noakun']]."</b><br><b> (OLEH BADAN USAHA INDUSTRI/EKSPORTIR TERTENTU)</b> </td><td width=220px>&nbsp;</td></tr>";
						$tab.="<tr><td width=120px>&nbsp;</td><td align=center style='border:1px ridge black;background-color:#b3b3b3'><b><font size=2>Nomor : ".$bar['no_buktipotong']." </font></b><i>(2)</i></td><td width=220px>&nbsp;</td></tr>";
					$tab.="</table>
				</td></tr>";
		$tab.="<tr><td colspan=2>
					<table border=0>";
					$tab.="<tr><td width=20px >&nbsp;</td><td  style=width:80px;font-size:13px><b>NPWP</b></td><td>:</td><td>";
						$tab.="<table cellspacing=0 border=0><tr>";
						for ($i=0;$i<strlen($npwpsupp);$i++){
							if(is_numeric(substr($npwpsupp,$i,1))){
							$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>".substr($npwpsupp,$i,1)."</td>";
							}
							else
							{
							$tab.="<td style=width:15px align=center>&nbsp;</td>";
							}
						}
						$tab.="</tr></table>";
					$tab.="</td></tr>";
					$tab.="<tr><td width=20px >&nbsp;</td><td style=width:50px;font-size:13px><b>Nama</td><td>:</td><td>";
						$tab.="<table cellspacing=0 border=1><tr>";
						$len=29;
						for ($i=0;$i<$len;$i++){
							if($i>=strlen($namasupp)){
								$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>&nbsp;</td>";
							}else{
								$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>".substr($namasupp,$i,1)."</td>";
							}
						}
						$tab.="</tr></table>";
					$tab.="</td></tr>";
					$tab.="<tr><td width=20px >&nbsp;</td><td style=width:50px;font-size:13px><b>Alamat</td><td>:</td><td>";
						$tab.="<table cellspacing=0 border=1><tr>";
						$len=29;
						for ($i=0;$i<$len;$i++){
							if($i>=strlen($alamatsupp)){
								$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>&nbsp;</td>";
							}else{
								$tab.="<td style='border:1.5px solid black;width:15px;height:5px;text-align:center;font-size:12px'>".substr($alamatsupp,$i,1)."</td>";
							}
						}
						$tab.="</tr></table>";
					$tab.="</td></tr>";
				$tab.="</table>
				</td></tr>";
		$tab.="<tr><td colspan=2></td></tr>";
		$tab.="<tr><td colspan=2>";

		$stri = "SELECT * FROM " . $dbname . ".pmn_5jenispenghasilan where kodepajak='".$bar['noakun']."' order by idpenghasilan asc";
		$resi = $owlPDO->query($stri) or die(print " Gagal: " . PDOException::getMessage());
		$resi->setFetchMode(PDO::FETCH_ASSOC);
		while($bari = $resi->fetch()){
			$jenispenghasil[$bari['kodepajak']][$bari['idpenghasilan']]=$bari['idpenghasilan'];
			$namahasil[$bari['kodepajak']][$bari['idpenghasilan']]=$bari['namapenghasilan'];
			$noparent[$bari['kodepajak']][$bari['idpenghasilan']]=$bari['nourutparent'];
			$mnldet[$bari['kodepajak']][$bari['idpenghasilan']]=$bari['manual'];
		}

		$str1 = "SELECT * FROM " . $dbname . ".tax_buktipotongpajak where no_buktipotong='".$no_buktipotong."' and periode='".$periode."' and kodeorg ='".$kodeorg."'";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		while($bar1 = $res1->fetch()){
			$jnsdetl[$bar1['jenisdetail']]=$bar1['jenisdetail'];
			$jnsdet[$bar1['noakun']][$bar1['jenis_penghasilan']][$bar1['jenisdetail']]=$bar1['jenisdetail'];
			@$trfpjk[$bar1['noakun']][$bar1['jenis_penghasilan']]+=$bar1['tarif_pajak'];
			@$trfpjkdet[$bar1['noakun']][$bar1['jenis_penghasilan']][$bar1['jenisdetail']]+=$bar1['tarif_pajak'];
			@$nilaipph[$bar1['noakun']][$bar1['jenis_penghasilan']]+=$bar1['nilai'];
			@$nilaipphdet[$bar1['noakun']][$bar1['jenis_penghasilan']][$bar1['jenisdetail']]+=$bar1['nilai'];
		}
		// echo"<pre>";
		// print_r($kdpajak);
		// echo"</pre>";

		$tab.="<table cellspacing=0 border='1' style='margin-left:15px;'>
					<tr>
						<td align=center style=background-color:#b3b3b3;font-size:13px; width=30px><b>No.</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px; colspan=3><b>Uraian<b/></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px; width=120px><b>Harga(Rp)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px; width=80px><b>Tarif Lebih<br>Tinggi 100%<br>(Tdk ber-NPWP)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px; width=30px><b>Tarif<br>%</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px; width=160px><b>PPh yang dipungut<br>(Rp)</b></td>
					</tr>
					<tr>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(1)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px; colspan=3><b>(2)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(3)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(4)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(5)</b></td>
						<td align=center style=background-color:#b3b3b3;font-size:13px;><b>(6)</b></td>
					</tr>";
				foreach($jenispenghasil as $kdpjk => $valpjk){
						$nox=0;
						$zes=0;
					foreach($valpjk as $jnshasil){
						$nd='';
						if($noparent[$kdpjk][$jnshasil]==0){
							$noparent[$kdpjk][$jnshasil]='';
						}
						if($mnldet[$kdpjk][$jnshasil]==1){
							foreach($jnsdetl as $jdet){
								if($jnsdet[$kdpjk][$jnshasil][$jdet]!=''){
									$nd+=1;
									$tab.="<tr>";
									$tab.="<td></td>";
									$tab.="<td colspan=3>&nbsp;&nbsp;<b>".$nd.") </b>".$jnsdet[$kdpjk][$jnshasil][$jdet]."</td>";
									$tab.="<td align=right>".@number_format($trfpjkdet[$kdpjk][$jnshasil][$jdet])."</td>";
									$tab.="<td align=center><img class=resicon src=images/uncekpajak.jpg style=width:20px;height:20px></td>";
									$tab.="<td align=right>".@number_format(floatval($trfpjkdet[$kdpjk][$jnshasil][$jdet]/$nilaipphdet[$kdpjk][$jnshasil][$jdet]))."</td>";
									$tab.="<td align=right>".@number_format($nilaipphdet[$kdpjk][$jnshasil][$jdet])."</td>";
									$tab.="</tr>";

									@$jumlahdpp+=$trfpjkdet[$kdpjk][$jnshasil][$jdet];
									@$jumlahpph+=$nilaipphdet[$kdpjk][$jnshasil][$jdet];
								}
							}
						}else{
							$tab.="<tr>";
							if($noparent[$kdpjk][$jnshasil]==''){
							$nox+=1;
							$tab.="<td style=font-size:13px; align=center>".$nox."</td>";
							$tab.="<td style=font-size:13px;  colspan=3>".$namahasil[$kdpjk][$jnshasil]."</td>";
							$tab.="<td style=font-size:13px;  align=right>".@number_format($trfpjk[$kdpjk][$jnshasil])."</td>";
							$tab.="<td align=center><img class=resicon src=images/uncekpajak.jpg style=width:20px;height:20px></td>";
							$tab.="<td style=font-size:13px;  style=font-size:13px;  align=right>".@number_format(floatval($trfpjk[$kdpjk][$jnshasil]/$nilaipph[$kdpjk][$jnshasil]))."</td>";
							$tab.="<td align=right>".@number_format($nilaipph[$kdpjk][$jnshasil])."</td>";
							}
							else
							{
							$zes+=1;
							$tab.="<td align=center></td>";
							$tab.="<td style=font-size:13px;  colspan=3><b>".$namahasil[$kdpjk][$jnshasil]."</b></td>";
							if($zes==1){
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;><b>Penjualan Bruto:<b></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							}
							elseif ($zes==2) {
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;><b>Harga Jual:<b></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							}
							elseif ($zes==3) {
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;><b>Pembelian Bruto:<b></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							}
							elseif ($zes==4) {
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							$tab.="<td align=center style=background-color:#b3b3b3;font-size:13px;></td>";
							}

							}

							@$jumlahdpp+=$trfpjk[$kdpjk][$jnshasil];
							@$jumlahpph+=$nilaipph[$kdpjk][$jnshasil];
						}
						$tab.="</tr>";
					}
				}
			$tab.="<tr>
						<td style=font-size:13px;  colspan=4 align=center>Jumlah</td>
						<td style=font-size:13px;  align=right>".@number_format($jumlahdpp)."</td>
						<td align=right></td>
						<td align=right></td>
						<td style=font-size:13px;  align=right>".@number_format($jumlahpph)."</td>
					</tr>";
			$tab.="<tr>
						<td style=font-size:13px;  colspan=8 align=left>Terbilang : ".terbilang(@number_format($jumlahpph),1)." RUPIAH</td>
					</tr>";
			$tab.="</table>";
		$tab.="</td></tr>";
		$tab.="<tr>
				<td width=220px height=100px>
					<table cellspacing=0 border=0>
						<tr><td style=vertical-align:top;><font size=2><br><br>
							</font>
							</td></tr>
					</table>
				</td>
				<td style=vertical-align:top;>
					<table>
						<tr><td colspan=2 align=center>Jakarta, ________________ </td></tr>
						<tr><td colspan=2 align=center><b>Pemungut Pajak</b></td></tr>
						<tr><td width=5px>NPWP:</td><td>";
						$tab.="<table cellspacing=0 border=0><tr>";
							$len=$ptptg='';
							@$ptptgnpwp = $nmnpwppt[$induk[$kodeorg]];
							$len=strlen($ptptgnpwp);
							for ($i=0;$i<$len;$i++){
								if(is_numeric(substr($ptptgnpwp,$i,1))){
								$tab.="<td style='border:1.5px solid black;width:10px;height:5px;text-align:center;font-size:12px'>".substr($ptptgnpwp,$i,1)."</td>";
								}
								else
								{
								$tab.="<td style=width:15px align=center>&nbsp;</td>";
								}
								//$tab.="<td align=center style=width:10px>".substr($ptptgnpwp,$i,1)."</td>";
							}
						$tab.="</tr></table>";
						$tab.="</td></tr>
						<tr><td width=5px>Nama:</td><td>";
						$tab.="<table cellspacing=0 border=1><tr>";
							$len='';
							@$nmpt = $nmorg[$induk[$kodeorg]];
							$len=20;
							for ($i=0;$i<$len;$i++){
								if($i>=strlen($nmpt)){
								$tab.="<td style='border:1.5px solid black;width:10px;height:5px;text-align:center;font-size:12px'>&nbsp;</td>";
								}else{
									$tab.="<td style='border:1.5px solid black;width:10px;height:5px;text-align:center;font-size:12px'>".substr($nmpt,$i,1)."</td>";
								}
								//$tab.="<td align=center style=width:10px>".substr($nmpt,$i,1)."</td>";
							}
						$tab.="</tr></table>";

						$tab.="</td></tr></table></td><tr><td width=220px>
					<table cellspacing=0 border=1>
						<tr><td style=vertical-align:top; width=220px><font size=2>
							Perhatian :<br>
							1.&nbsp;Jumlah PPh Pasal 22 yang dipungut di<br>&nbsp;&nbsp;&nbsp;&nbsp;atas merupakan pembayaran di muka<br>&nbsp;&nbsp;&nbsp;&nbsp;atas PPh yang terutang untuk tahun<br>&nbsp;&nbsp;&nbsp;&nbsp;pajak yang bersangkutan. Simpanlah<br>&nbsp;&nbsp;&nbsp;&nbsp;Bukti Pemungutan ini baik-baik untuk<br>&nbsp;&nbsp;&nbsp;&nbsp;diperhitungkan sebagai kredit pajak<br>&nbsp;&nbsp;&nbsp;&nbsp;dalam Surat Pemberitahuan (SPT)<br>&nbsp;&nbsp;&nbsp;&nbsp;Tahunan PPh.<br>

							2.&nbsp;Bukti Pemungutan ini dianggap sah
							<br>&nbsp;&nbsp;&nbsp;&nbsp;apabila diisi dengan lengkap dan
							benar.<br>
							</font>
							</td></tr>
					</table>
				</td><td><table>";
						$tab.="<tr><td colspan=2 align=center><b>Tanda Tangan, Nama dan Cap</b></td></tr>";
						$tab.="<tr><td colspan=2>&nbsp;</td></tr>";
						$tab.="<tr><td colspan=2>&nbsp;</td></tr>";
						$tab.="<tr><td colspan=2 align=center>......................................</td></tr>";
				$tab.="</table>
				</td>
				</tr>";
		
		$tab.="<tr><td style=font-size:13px; colspan=2><b>F.1.1.33.04</b></td></tr>";

		$tab.="</table>";
		}
		elseif($bar['noakun']=='2130101')
		{
			$tab.="<table border=0 cellspacing'=0>";
			$tab.="<tr>";
			$tab.="<td style='border-right:1px solid black;' width=230px; align=center;><img style=width:60px;height:60px; src=images/logo-direktorat-jenderal-pajak.jpg></td>";
			$tab.="<td style='font-size:13px;border-right:1px solid black;border-bottom:1px solid black;' align=center;><b>BUKTI PEMOTONGAN PAJAK <br> PENGHASILAN PASAL 21 (TIDAK FINAL) <br> ATAU PASAL 26</b><br><br><br><br></td>";
			$tab.="<td style='font-size:13px;border-bottom:1px solid black;'><b>FORMULIR 1721 - VI</b><br>";
			$tab.="<a style=font-size:10px><b>Lembar ke-1 : untuk Penerima Penghasilan</b></a><br><a style=font-size:10px><b>Lembar ke-2 : untuk Pemotong</b></a></td></tr>";
			$tab.="</tr>";
			$tab.="<tr>";
			$tab.="<td style='font-size:13px;border-bottom:1px solid black;' align=center;><b>KEMENTERRIAN KEUANGAN RI <br> DIREKTORAT JENDERAL PAJAK</b></td>";
			$tab.="<td style='font-size:13px;border-bottom:1px solid black;border-left:1px solid black;' colspan=2><b>NOMOR <sub style=font-size:9px;><font color='blue'>H.01</font></sub> 1 &nbsp;&nbsp;.&nbsp;&nbsp; 3 &nbsp; -&nbsp;<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>.&nbsp;<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>-&nbsp;<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>  </b></td>";
			$tab.="</tr>";
			$tab.="</table>";


			$tab.="<table border=0 cellspacing'=0>";
			$tab.="<tr>";
			$tab.="<td style='font-size:12px;'><br><b>A. IDENTITAS PENERIMA PENGHASILAN YANG DIPOTONG</b><br><br></td>";
			$tab.="</tr>";
			$tab.="</table>";

			$arnpwp=explode('-', $npwpsupp);
			$arnpwp2=explode('.', $arnpwp[1]);

			$tab.="<table border=0 cellspacing'=0>";

			$tab.="<tr>";
			$tab.="<td colspan=8 style='border:1px solid black;border-bottom:0px;'><br></td>";
			$tab.="</tr>";

			$tab.="<tr>";
			$tab.="<td style='font-size:12px;border-left:1px solid black;'><b>&nbsp;&nbsp;&nbsp;&nbsp;1.NPWP</b></td>";
			$tab.="<td style='font-size:12px;'><b>:</b></td>";
			$tab.="<td style='font-size:12px;'><b><sub style=font-size:9px;><font color='blue'>A.01</font></sub></b></td>";
			$tab.="<td style='font-size:12px;'><b><u>".$arnpwp[0]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>&nbsp;-&nbsp;<u>".$arnpwp2[0]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>&nbsp;.&nbsp;<u>".$arnpwp2[1]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			$tab.="<td style='font-size:12px;'><b>2.NIK/NO.PASPOR</b></td>";
			$tab.="<td style='font-size:12px;'><b>:</b></td>";
			$tab.="<td style='font-size:12px;'><b><sub style=font-size:9px;><font color='blue'>A.02</font></sub></b></td>";
			$tab.="<td style='font-size:12px;border-right:1px solid black;'><b><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>&nbsp;&nbsp;&nbsp;&nbsp;</b></td>";
			$tab.="</tr>";

			$tab.="<tr>";
			$tab.="<td colspan=8 style='border:1px solid black;border-bottom:0px;border-top:0px;'><br></td>";
			$tab.="</tr>";

			$tab.="<tr>";
			$tab.="<td style='font-size:12px;border-left:1px solid black;'><b>&nbsp;&nbsp;&nbsp;&nbsp;3.NAMA</b></td>";
			$tab.="<td style='font-size:12px;'><b>:</b></td>";
			$tab.="<td style='font-size:12px;'><b><sub style=font-size:9px;><font color='blue'>A.03</font></sub></b></td>";
			$tab.="<td colspan=5 style='font-size:12px;border-bottom:0.5px solid black;border-right:1px solid black;'><b>".$namasupp."</b></td>";
			$tab.="</tr>";


			$tab.="<tr>";
			$tab.="<td colspan=8 style='border:1px solid black;border-bottom:0px;border-top:0px;'><br></td>";
			$tab.="</tr>";

			
			if(60>=strlen($alamatsupp)){
			$tab.="<tr>";
			$tab.="<td style='font-size:12px;border-left:1px solid black;'><b>&nbsp;&nbsp;&nbsp;&nbsp;4.ALAMAT</b></td>";
			$tab.="<td style='font-size:12px;'><b>:</b></td>";
			$tab.="<td style='font-size:12px;'><b><sub style=font-size:9px;><font color='blue'>A.04</font></sub></b></td>";
			$tab.="<td colspan=5 style='font-size:12px;border-bottom:0.5px solid black;border-right:1px solid black;'><b>".$alamatsupp."</b></td>";
			$tab.="</tr>";
			}
			else
			{
			$tab.="<tr>";
			$tab.="<td style='font-size:12px;border-left:1px solid black;'><b>&nbsp;&nbsp;&nbsp;&nbsp;4.ALAMAT</b></td>";
			$tab.="<td style='font-size:12px;'><b>:</b></td>";
			$tab.="<td style='font-size:12px;'><b><sub style=font-size:9px;><font color='blue'>A.04</font></sub></b></td>";
			$tab.="<td colspan=5 style='font-size:12px;border-bottom:0.5px solid black;border-right:1px solid black;'><b>".substr($alamatsupp,0,60)."</b></td>";
			$tab.="</tr>";
			$tab.="<tr>";
			$tab.="<td colspan=3 style='font-size:12px;border-left:1px solid black;'><b></b></td>";
			$tab.="<td colspan=5 style='font-size:12px;border-bottom:0.5px solid black;border-right:1px solid black;'><b>".substr($alamatsupp, 61)."</b></td>";
			$tab.="</tr>";
			}

			$tab.="<tr>";
			$tab.="<td colspan=4 style='font-size:12px;border-left:1px solid black;'><b>&nbsp;&nbsp;&nbsp;&nbsp;5.WAJIB PAJAK LUAR NEGERI : <sub style=font-size:9px;><font color='blue'>A.05</font></sub>&nbsp;&nbsp;&nbsp;<input type='checkbox' style='vertical-align: bottom;'> YA</b></td>";
			$tab.="<td colspan=4 style='font-size:12px;border-right:1px solid black;'><b>6.KODE NEGARA DOMISILI:<sub style=font-size:9px;><font color='blue'>A.06</font></sub><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></b></td>";
			$tab.="</tr>";

			$tab.="<tr>";
			$tab.="<td colspan=8 style='border:1px solid black;border-top:0px;'><br></td>";
			$tab.="</tr>";

			$tab.="</table>";

			$tab.="<table border=0 cellspacing'=0>";
			$tab.="<tr>";
			$tab.="<td style='font-size:12px;'><br><b>B. PPh PASAL 21 DAN/ATAU PASAL 26 YANG DIPOTONG</b><br><br></td>";
			$tab.="</tr>";
			$tab.="</table>";

			$tab.="<table border=1 cellspacing'=0 style='margin-right:10px'>";
			$tab.="<tr>";
			$tab.="<td style='font-size:11px;' width=130px align=center><b>KODE OBJEK PAJAK</b></td>";
			$tab.="<td style='font-size:11px;' width=110px align=center><b>JUMLAH <br>PENGHASILAN BRUTO <br>(RP)</b></td>";
			$tab.="<td style='font-size:12px;' width=110px align=center><b>DASAR PENGENAAN PAJAK<br>(RP)</b></td>";
			$tab.="<td style='font-size:10px;' width=50px align=center><b>TARIF LEBIH TINGGI 20% <br> (TIDAK BER-<br>NPWP)</b></td>";
			$tab.="<td style='font-size:11px;' width=20px align=center><b>TARIF<br>(%)</b></td>";
			$tab.="<td style='font-size:11px;' width=100px align=center><b>PPh DIPOTONG <br>(RP)</b></td>";
			$tab.="</tr>";

			$tab.="<tr>";
			$tab.="<td style='font-size:11px;background-color:#b3b3b3;' width=130px align=center><b>(1)</b></td>";
			$tab.="<td style='font-size:11px;background-color:#b3b3b3;' width=110px align=center><b>(2)</b></td>";
			$tab.="<td style='font-size:12px;background-color:#b3b3b3;' width=110px align=center><b>(3)</b></td>";
			$tab.="<td style='font-size:10px;background-color:#b3b3b3;' width=50px align=center><b>(4)</b></td>";
			$tab.="<td style='font-size:11px;background-color:#b3b3b3;' width=20px align=center><b>(5)</b></td>";
			$tab.="<td style='font-size:11px;background-color:#b3b3b3;' width=100px align=center><b>(6)</b></td>";
			$tab.="</tr>";


			$tab.="<tr>";
			$tab.="<td style='font-size:11px;' width=130px align=center><b></b></td>";
			$tab.="<td style='font-size:11px;' width=110px align=center><b></b></td>";
			$tab.="<td style='font-size:12px;' width=110px align=center><b></b></td>";
			$tab.="<td style='font-size:10px;' width=50px align=center><div style='width:30px;height:30px;border:1px solid black;margin-left:15px;'></div></td>";
			$tab.="<td style='font-size:11px;' width=20px align=center><b></b></td>";
			$tab.="<td style='font-size:11px;' width=100px align=center><b></b></td>";
			$tab.="</tr>";

			$tab.="</table>";

			$tab.="<table border=0 cellspacing'=0>";
			$tab.="<tr>";
			$tab.="<td style='font-size:12px;'><br><b>C. IDENTITAS PEMOTONG</b><br><br></td>";
			$tab.="</tr>";
			$tab.="</table>";


			@$ptptgnpwp = $nmnpwppt[$induk[$kodeorg]];
			$arnpwp=explode('-', $ptptgnpwp);
			$arnpwp2=explode('.', $arnpwp[1]);

			$tab.="<table border=0 cellspacing'=0>";

			$tab.="<tr>";
			$tab.="<td style='font-size:12px;border-left:1px solid black;border-top:1px solid black;'><b>&nbsp;&nbsp;&nbsp;&nbsp;1.NPWP</b></td>";
			$tab.="<td style='font-size:12px;border-top:1px solid black;'><b>:</b></td>";
			$tab.="<td style='font-size:12px;border-top:1px solid black;'><b><sub style=font-size:9px;><font color='blue'>C.01</font></sub></b></td>";
			$tab.="<td style='font-size:12px;border-top:1px solid black;'><b><u>".$arnpwp[0]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>&nbsp;-&nbsp;<u>".$arnpwp2[0]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>&nbsp;.&nbsp;<u>".$arnpwp2[1]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			$tab.="<td colspan=3 style='font-size:12px;border-top:1px solid black;'><b>3.TANGGAL & TANDA TANGAN</b></td>";
			$tab.="<td rowspan=2 style='font-size:12px;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;' width=145px><div style='width:125px;height:70px;border:1px solid black;margin-left:15px;'></div></td>";
			$tab.="</tr>";


			@$nmpt = $nmorg[$induk[$kodeorg]];

			$tab.="<tr>";
			$tab.="<td style='font-size:12px;border-left:1px solid black;border-bottom:1px solid black;'><b>&nbsp;&nbsp;&nbsp;&nbsp;2.NAMA</b></td>";
			$tab.="<td style='font-size:12px;border-bottom:1px solid black;'><b>:</b></td>";
			$tab.="<td style='font-size:12px;border-bottom:1px solid black;'><b><sub style=font-size:9px;><font color='blue'>C.02</font></sub></b></td>";
			$tab.="<td style='font-size:12px;border-bottom:1px solid black;'><b><u>".$nmpt."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td>";
			$tab.="<td colspan=3  style='font-size:12px;border-bottom:1px solid black;'><b><sub style=font-size:9px;><font color='blue'>C.03</font></sub><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>&nbsp;-&nbsp;<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>&nbsp;-&nbsp;<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></b></td>";
			$tab.="</tr>";

			$tab.="</table>";

			$tab.="<table border=0 cellspacing'=0>";
			$tab.="<tr>";
			$tab.="<td style='font-size:12px;'><br><br><br></td>";
			$tab.="</tr>";
			$tab.="</table>";

			$tab.="<table border=1 cellspacing'=0>";
			$tab.="<tr>";
			$tab.="<td style='font-size:12px;background-color:#b3b3b3;' width=695px align=center><b>KODE OBJEK PAJAK PENGHASILAN 21 (TIDAK FINAL) ATAU PASAL 26</b></td>";
			$tab.="</tr>";
			$tab.="<tr>";
			$tab.="<td style='font-size:10px;border-bottom:0px;' width=695px align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>PPh PASAL 21 TIDAK FINAL</b>";
			$tab.="<br>";
			$tab.="1.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-03 &nbsp;&nbsp; Upah Pegawai Tidak Tetap atau Tenaga Kerja Lepas";
			$tab.="<br>";
			$tab.="2.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-04 &nbsp;&nbsp; Imbalan Kepada Distributor Multi Level Marketing (MLM)";
			$tab.="<br>";
			$tab.="3.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-05 &nbsp;&nbsp; Imbalan Kepada Petugas Dinas Luar Asuransi";
			$tab.="<br>";
			$tab.="4.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-06 &nbsp;&nbsp; Imbalan Kepada Penjaja Barang Dagangan";
			$tab.="<br>";
			$tab.="5.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-07 &nbsp;&nbsp; Imbalan Kepada Tenaga Ahli";
			$tab.="<br>";
			$tab.="6.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-08 &nbsp;&nbsp; Imbalan Kepada Bukan Pegawai yang Menerima Penghasilan yang Bersifat Berkesinambungan";
			$tab.="<br>";
			$tab.="7.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-09 &nbsp;&nbsp; Imbalan Kepada Bukan Pegawai yang Menerima Penghasilan yang Tidak Bersifat Berkesinambungan";
			$tab.="<br>";
			$tab.="8.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-10 &nbsp;&nbsp; Honorium atau Imbalan Kepada Anggota Dewan Komisaris atau Dewan Pengawas yang tidak Merangkap sebagai Pegawai Tetap";
			$tab.="<br>";
			$tab.="9.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21-100-11 &nbsp;&nbsp; Jasa Produksi, Tantiem, Bonus atau Imbalan Kepada Mantan Pegawai";
			$tab.="<br>";
			$tab.="10.&nbsp;&nbsp;&nbsp;&nbsp;21-100-12 &nbsp;&nbsp; Penarikan Dana Pensiun oleh Pegawai";
			$tab.="<br>";
			$tab.="11.&nbsp;&nbsp;&nbsp;&nbsp;21-100-13 &nbsp;&nbsp; Imbalan Kepada Peserta Kegiatan";
			$tab.="<br>";
			$tab.="12.&nbsp;&nbsp;&nbsp;&nbsp;21-100-14 &nbsp;&nbsp; Objek PPh Pasal 21 Tidak Final Lainnya";
			$tab.="<br><br>";
			$tab.="</td></tr>";
			$tab.="<tr>";
			$tab.="<td style='font-size:10px;border-top:0px;' width=695px align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>PPh PASAL 26</b>";
			$tab.="<br>";
			$tab.="1.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;27-100-99 &nbsp;&nbsp; Imbalan sehubungan dengan jasa, pekerjaan dan kegiatan, hadiah dan penghargaan, pensiun dan pembayaran berkala lainnya yang<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dipotong PPh Pasal 26";
			$tab.="<br>";
			$tab.="</td></tr>";

			$tab.="</table>";
		}
		//echo $tab;
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream("bupot pph",array("Attachment"=>0));
	break;
}

?>