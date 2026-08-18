<?
require_once('config/connection.php');
@require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_pie.php');
require_once ('jpgraph/jpgraph_pie3d.php');

// $tglskrg = '2015-07-01';
// $blnskrg = '2015-07';
// $bi = '07';
// $ti = '2015';
// $tglbsk = '2015-08-01';


$tglskrg = date('Y-m-d');
$tglskrg = tglkemarin($tglskrg);
$blnskrg = date('Y-m');
$bi = date('m');
$ti = date('Y');
if($bi == 12){
	$tglbsk = ($ti+1)."-01-01";
}else{
	$tglbsk = $ti."-".($bi+1)."-01";
}

$tglsatubi= $ti."-".($bi)."-01";

//Penerimaan Buah Internal/Ekseternal
$PnBuahInt = 0;
$PnBuahEks = 0;
$arrProporsi = array();
$str = "select sum(beratbersih) as beratbersih, intex, kodeorg from ".$dbname.".pabrik_timbangan_vw where substr(tanggal,1,7) = '".$blnskrg."' group by intex, kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	if($bar['intex'] == '1' || $bar['intex'] == '2'){
		$PnBuahInt += $bar['beratbersih']; 
	}else{
		$PnBuahEks += $bar['beratbersih']; 
	}
	if($bar['kodeorg'] != ''){
		@$arrProporsi[$bar['kodeorg']] += $bar['beratbersih'];
	}
}

$vArrProporsi = array();
$vArrOrgProporsi = array();
foreach($arrProporsi as $key=>$val){
	array_push($vArrProporsi, $val);
	array_push($vArrOrgProporsi, $key);
}

//BEGIN Produksi PKS
$arrTbsOlah = array();
$arrRendemenOer = array();
$arrCpo = array();
$str = "select kodeorg, sum(oer) as oer, sum(tbsdiolah) as tbsdiolah from ".$dbname.".pabrik_produksi where substr(tanggal,1,7) = '".$blnskrg."' and tbsdiolah>0 group by kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	@$arrTbsOlah[$bar['kodeorg']] += $bar['tbsdiolah'];
	@$arrRendemenOer[$bar['kodeorg']] += (($bar['oer']/$bar['tbsdiolah']) * 100);
	@$arrCpo[$bar['kodeorg']] += $bar['oer'];
}

$vArrTbsOlah = array();
$vArrOrgTbsOlah = array();
foreach($arrTbsOlah as $key=>$val){
	array_push($vArrTbsOlah, $val);
	array_push($vArrOrgTbsOlah, $key);
}
$vArrRendemen = array();
$vArrOrgRendemen = array();
foreach($arrRendemenOer as $key=>$val){
	if($val==0){
		$val=1;
	}
	array_push($vArrRendemen, $val);
	array_push($vArrOrgRendemen, $key);
}
$vArrCpo = array();
$vArrOrgCpo = array();
foreach($arrCpo as $key=>$val){
	if($val==0){
		$val=1;
	}
	array_push($vArrCpo, $val);
	array_push($vArrOrgCpo, $key);
}

//Panen
//BULAN LALU
$str=" select substr(divisi,1,4) as estate, sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn_vw where tanggal < '".$tglsatubi."'  and posting='1' group by substr(divisi,1,4)";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $jjgpanenkemarin[$bar['estate']] = $bar['jjgpanen'];
    $jjgafkirkemarin[$bar['estate']] = $bar['jjgafkir'];
}

$str=" select substr(divisi,1,4) as estate, sum(jjg) as jjg from ".$dbname.".kebun_spb_vw where tanggal < '".$tglsatubi."'  and posting='1' group by substr(divisi,1,4)";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $jjgspbkemarin[$bar['estate']] = $bar['jjg'];
}

//BULAN SEKARANG
$str=" select substr(divisi,1,4) as estate, sum(jjgpanen) as jjgpanen, sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn_vw where tanggal >= '".$tglsatubi."' and tanggal <= '".$tglskrg."' and posting='1' group by substr(divisi,1,4)";


$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$jjgpnn[$bar['estate']] = $bar['jjgpanen'];
    $jjgafkir[$bar['estate']] = $bar['jjgafkir'];
}

$str=" select substr(divisi,1,4) as estate, sum(jjg) as jjg, sum(kgwbnetto)as kgwbnetto from ".$dbname.".kebun_spb_vw where tanggal >= '".$tglsatubi."' and tanggal <= '".$tglskrg."'  and posting='1' group by substr(divisi,1,4)";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $jjgkirim[$bar['estate']] = $bar['jjg'];
    $kgkirim[$bar['estate']] = $bar['kgwbnetto'];
}

$str=" select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $org[$bar['kodeorganisasi']] = $bar['kodeorganisasi'];
	$optplasma=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"namaorganisasi like '%PLASMA%' and kodeorganisasi='".$bar['kodeorganisasi']."'");
	if($optplasma[$bar['kodeorganisasi']]!=''){
		$plasma[$bar['kodeorganisasi']] = "PLASMA";
	}else{
		$plasma[$bar['kodeorganisasi']] = "INTI";
	}
	
	
}

//Produksi
//Anggaran
$str=" select kodeunit as estate, sum(kg".$bi.") as jumlah from ".$dbname.".bgt_produksi_kbn_kg_vw where tahunbudget = '".$ti."' group by kodeunit";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $bgtproduksi[$bar['estate']] = $bar['jumlah'];
}

//Sensus
$str=" select substr(kodeorg,1,4) as estate, sum(kgsensus) as jumlah from ".$dbname.".kebun_rencanapanen_vw where substr(tanggal,1,7) = '".$blnskrg."' group by substr(kodeorg,1,4)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $sensusproduksi[$bar['estate']] = $bar['jumlah'];
}

//Realisasi
$arrPrdPT = array();
$str=" SELECT substr(divisi,1,4) as estate, sum(kgwb) as jumlah from  ".$dbname.".kebun_spb_vw where substr(tanggal,1,7) = '".$blnskrg."' and posting ='1' group by estate";
// $str=" select unit as estate, sum(hasilkerjakg) as jumlah from ".$dbname.".kebun_prestasi_vw where substr(tanggal,1,7) = '".$blnskrg."' group by unit";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi = '".$bar['estate']."'");
    $realproduksi[$bar['estate']] = $bar['jumlah'];
	@$arrPrdPT[$optPt[$bar['estate']]] += $bar['jumlah'];
}
$vArrPrdPT = array();
$vArrOrgPrdPT = array();
foreach($arrPrdPT as $key=>$val){
	array_push($vArrPrdPT, $val);
	array_push($vArrOrgPrdPT, $key);
}

//TANAM
//HA
$str=" select kodeorg, (hasilkerja) as jumlah from ".$dbname.".kebun_perawatan_vw where substr(tanggal,1,7) = '".$blnskrg."' and kodekegiatan = '126060103'";
$vArrBlok = array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$tanamha[substr($bar['kodeorg'],0,4)] += $bar['jumlah'];
    @$tanamhablok[$bar['kodeorg']] += $bar['jumlah'];
    array_push($vArrBlok, $bar['kodeorg']);
}

//Pokok
foreach($vArrBlok as $val){
	$optBlok = makeOption($dbname,'setup_blok','kodeorg,(jumlahpokok/luasareaproduktif)',"kodeorg = '".$val."'");
	@$tanampokok[substr($val,0,4)] += ($tanamhablok[$val] * $optBlok[$val]);
}
if($theme=='skyblue' || $theme==''){
  $men='menu.css';
  $gen='generic.css';
 }else if($theme=='red'){
  $men='menuRed.css';
  $gen='genericRed.css';
  }else{
  $men='menuGray.css';
  $gen='genericGray.css';
}


$tab = "<link rel=stylesheet type=text/css href='style/".$gen."'>";
$tab .= "<table width=100%>
	<tr>
		<td style='width:50%;vertical-align:top' nowrap>
			<div id='penerimaanbuah'>
				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr class=rowcontent>
						<td align=left width=1% nowrap style='font-weight:bold'>".$_SESSION['lang']['tbsmasuk']." / ".$_SESSION['lang']['bulan']." ".strtolower($_SESSION['lang']['sd'])." ".$_SESSION['lang']['bulan']." : ".$bi."-".$ti."</td>
					</tr>
				</table>
				<marquee height=225px onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up>
				<table class=sortable cellspacing=1 border=0 width=100%>";
					if(($PnBuahInt == 0 && $PnBuahEks == 0) && count($vArrProporsi) <= 0){
						$tab .= "<tr>
						<td style='min-height:200px;display:block;text-align:center;vertical-align:middle' border=0>".$_SESSION['lang']['dataempty']."</td>
					</tr>";
					}else{
						if($PnBuahInt == 0 && $PnBuahEks == 0){
							$tab .= "";
						}else{
							@unlink('tempExcel/penerimaanbuah.png');
							$filpenerimaanbuah = "tempExcel/penerimaanbuah.png";
							$penerimaanbuah = penerimaanbuah();
							$tab .= "<tr>
							<td style='min-height:200px;display:block'><img style=width:100% src='".$filpenerimaanbuah."'></td>
							</tr>";
						}
						if(count($vArrProporsi) <= 0){
							$tab .= "";
						}else{
							@unlink('tempExcel/proporsi.png');
							$filproporsi = "tempExcel/proporsi.png";
							$proporsibuah = proposibuah();
							$tab .= "<tr>
							<td style='min-height:200px;display:block'><img style=width:100% src='".$filproporsi."'></td>
							</tr>";
						}
					}
				$tab .= "</table></marquee>
			</div>
		</td>
		<td style='width:50%;vertical-align:top' nowrap>
			<div id='panen'>
				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr class=rowcontent>
						<td align=left width=1% nowrap style='font-weight:bold'>".$_SESSION['lang']['panen']." ( Jjg )
						".strtolower($_SESSION['lang']['sd'])." ".$_SESSION['lang']['tanggal']." : ".tanggalNormal($tglskrg)."</td>
					</tr>
				</table>
				<marquee height=225px onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up>
				
				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr class=rowcontent>
						<td align=left width=1% nowrap>
						<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
							<thead>
							<tr class=rowheader>
								<td style='text-align:center'>".$_SESSION['lang']['kebun']."</td>
								<td style='text-align:center'>".$_SESSION['lang']['status']."</td>
								<td style='text-align:center'>Restant ".$_SESSION['lang']['bl']."</td>
								<td style='text-align:center'>".$_SESSION['lang']['panen']." ".$_SESSION['lang']['bi']."</td>
								<td style='text-align:center'>".$_SESSION['lang']['kirim']." ".$_SESSION['lang']['bi']."</td>
								<td style='text-align:center'>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['bi']."</td>
								<td style='text-align:center'>Afkir ".$_SESSION['lang']['bi']."</td>
								<td style='text-align:center'>Restant ".$_SESSION['lang']['bi']."</td>
							</tr>
							</thead>
							<tbody>";
							
							
							
							foreach($org as $val){
								@$restankemarin = $jjgpanenkemarin[$val] - $jjgafkirkemarin[$val] - $jjgspbkemarin[$val];
								@$restan = $restankemarin + $jjgpnn[$val] - $jjgafkir[$val] - $jjgkirim[$val];
								$tab .= "<tr class=rowcontent>";
								$tab .= "<td align=center>".$val."</td>";
								$tab .= "<td align=center>".$plasma[$val]."</td>";
								if($plasma[$val]=='INTI'){
									$tab .= "<td style='text-align:right'>".number_format($restankemarin)."</td>";
									$tab .= "<td style='text-align:right'>".number_format(@$jjgpnn[$val])."</td>";
									$tab .= "<td style='text-align:right'>".number_format(@$jjgkirim[$val])."</td>";
									$tab .= "<td style='text-align:right'>".number_format(@$kgkirim[$val])."</td>";
									$tab .= "<td style='text-align:right'>".number_format(@$jjgafkir[$val])."</td>";
									$tab .= "<td style='text-align:right'>".number_format($restan)."</td>";
									@$trk+=$restankemarin;
									@$tjpnn+=$jjgpnn[$val];
									@$tjkrm+=$jjgkirim[$val];
									@$tjaf+=$jjgafkir[$val];
									@$tres+=$restan;
									@$tkgkirim+=$kgkirim[$val];
								}else{
									$tab .= "<td style='text-align:right;background-color:#D7D3D2'></td>";
									$tab .= "<td style='text-align:right;background-color:#D7D3D2'></td>";
									$tab .= "<td style='text-align:right'>".number_format(@$jjgkirim[$val])."</td>";
									$tab .= "<td style='text-align:right'>".number_format(@$kgkirim[$val])."</td>";
									$tab .= "<td style='text-align:right;background-color:#D7D3D2'></td>";
									$tab .= "<td style='text-align:right;background-color:#D7D3D2'></td>";
									@$tjkrm+=$jjgkirim[$val];
									@$tkgkirim+=$kgkirim[$val];
								}
								$tab .= "</tr>";
							}
								$tab .= "<tr class=rowcontent>
										 <td style='text-align:center'><b>".$_SESSION['lang']['total']."</b></td>
										 <td style='text-align:right'></td>
										 <td style='text-align:right'><b>".@number_format($trk)."</b></td>
										 <td style='text-align:right'><b>".@number_format($tjpnn)."</b></td>
										 <td style='text-align:right'><b>".@number_format($tjkrm)."</b></td>
										 <td style='text-align:right'><b>".@number_format($tkgkirim)."</b></td>
										 <td style='text-align:right'><b>".@number_format($tjaf)."</b></td>
										 <td style='text-align:right'><b>".@number_format($tres)."</b></td>
										</tr>";


							$tab.="<tr class=rowcontent>
									<td colspan=8>
									Note :<br>
										1. Sumber data Jjg Panen : Kebun - Trans - Rekap Panen Perblok<br>
										2. Sumber data Jjg Kirim dan Kg : Kebun - Trans - SPB<br>
										3. Untuk kebun PLASMA Jjg Panen tidak dicatat ke dalam system<br>
										
									</td>
							</tr>";
							$tab .= "</tbody>
						</table>
						</td>
					</tr>
					
				</table>
				</marquee>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan=2><hr></td>
	</tr>
	<tr>
		<td style='vertical-align:top'>
			<div id='produksipks'>
				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr class=rowcontent>
						<td align=left width=1% nowrap style='font-weight:bold'>".$_SESSION['lang']['produksipks']." / ".$_SESSION['lang']['bulan']." ".strtolower($_SESSION['lang']['sd'])." ".$_SESSION['lang']['bulan']." : ".$bi."-".$ti."</td>
					</tr>
				</table>
				<marquee height=225px onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up>
				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr>";
					if(count($vArrTbsOlah) <= 0 && count($vArrRendemen) <= 0 && count($vArrRendemen) <= 0){
						$tab .= "<td style='min-height:200px;display:block;text-align:center;vertical-align:middle' border=0>".$_SESSION['lang']['dataempty']."</td>";
					}else{
						if(count($vArrTbsOlah) <= 0){
							$tab .= "";
						}else{
							@unlink('tempExcel/tbsolah.png');
							$filtbsolah = "tempExcel/tbsolah.png";
							$tbsolah = tbsolah();
							$tab .= "<td style='min-height:200px;display:block'><img style=width:100% src='".$filtbsolah."'></td>";
						}
						
						$tab .= "</tr>
						<tr>";
						if(count($vArrRendemen) <= 0){
							$tab .= "";
						}else{
							@unlink('tempExcel/rendemen.png');
							$filrendemen = "tempExcel/rendemen.png";
							$rendemen = rendemen();
							$tab .= "<td style='min-height:200px;display:block'><img style=width:100% src='".$filrendemen."'></td>";
						}
						$tab .= "</tr>
						<tr>";
						if(count($vArrRendemen) <= 0){
							$tab .= "";
						}else{
							@unlink('tempExcel/cpo.png');
							$filcpo = "tempExcel/cpo.png";
							$cpo = cpo();
							$tab .= "<td style='min-height:200px;display:block'><img style=width:100% src='".$filcpo."'></td>";
						}
					}
					$tab .= "</tr>
				</table>
				</marquee>
			</div>
		</td>
		<td style='vertical-align:top'>
			<div id='produksikg'>
				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr class=rowcontent>
						<td align=left width=1% nowrap style='font-weight:bold'>".$_SESSION['lang']['produksi']." ( Kg ) & ".$_SESSION['lang']['tanam']." ( Pkk ) ".strtolower($_SESSION['lang']['sd'])." ".$_SESSION['lang']['tanggal']." : ".tanggalNormal($tglskrg)."</td>
					</tr>
				</table>
				<marquee height=225px onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up>
				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr>";
					if(count($vArrPrdPT) <= 0){
						$tab .= "";
					}else{
						@unlink('tempExcel/prdpt.png');
						$filprdpt = "tempExcel/prdpt.png";
						$prdpt = prdpt();
						$tab .= "<td style='min-height:200px;display:block'><img style=width:100% src='".$filprdpt."'></td>";
					}
					$tab .= "</tr>
					<tr class=rowcontent>
						<td align=left width=1% nowrap>
						<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
							<thead>
							<tr class=rowheader>
								<td style='text-align:center' width=10%>".$_SESSION['lang']['kebun']."</td>
								<td style='text-align:center' width=18%>".$_SESSION['lang']['budget']."</td>
								<td style='text-align:center' width=18%>".$_SESSION['lang']['sensus']."</td>
								<td style='text-align:center' width=18%>".$_SESSION['lang']['realisasi']."</td>
								<td style='text-align:center' width=18%>Varian ( ".$_SESSION['lang']['real']." - ".$_SESSION['lang']['bgt']." )</td>
								<td style='text-align:center' width=18%>Varian ( ".$_SESSION['lang']['real']." - ".$_SESSION['lang']['sns']." )</td>
							</tr>
							</thead>
							<tbody>";
							foreach($org as $val){
								@$varrealbgt = @$realproduksi[$val] - @$bgtproduksi[$val];
								@$varrealsensus = @$realproduksi[$val] - @$sensusproduksi[$val];
								$tab .= "<tr class=rowcontent>";
								$tab .= "<td>".$val."</td>";
								$tab .= "<td style='text-align:right'>".number_format(@$bgtproduksi[$val])."</td>";
								$tab .= "<td style='text-align:right'>".number_format(@$sensusproduksi[$val])."</td>";
								$tab .= "<td style='text-align:right'>".number_format(@$realproduksi[$val])."</td>";
								$tab .= "<td style='text-align:right'>".number_format(@$varrealbgt)."</td>";
								$tab .= "<td style='text-align:right'>".number_format(@$varrealsensus)."</td>";
								$tab .= "</tr>";
								
								@$tb+=$bgtproduksi[$val];
								@$ts+=$sensusproduksi[$val];
								@$tr+=$realproduksi[$val];
								@$tv+=$varrealbgt;
								@$tvs+=$varrealsensus;
								
							}
							$tab .= "<tr class=rowcontent>
									 <td style='text-align:left'><b>".$_SESSION['lang']['total']."</b></td>
									 <td style='text-align:right'><b>".@number_format($tb)."</b></td>
									 <td style='text-align:right'><b>".@number_format($ts)."</b></td>
									 <td style='text-align:right'><b>".@number_format($tr)."</b></td>
									 <td style='text-align:right'><b>".@number_format($tv)."</b></td>
									 <td style='text-align:right'><b>".@number_format($tvs)."</b></td>
									</tr>";
							$tab .= "</tbody>
						</table>
						</td>
					</tr>
					<tr>
						<td><hr></td>
					</tr>
					<tr class=rowcontent>
						<td align=left width=1% nowrap style='font-weight:bold'>".$_SESSION['lang']['tanam']."</td>
					</tr>
					<tr class=rowcontent>
						<td align=left width=1% nowrap>
						<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
							<thead>
							<tr class=rowheader>
								<td style='text-align:center'>".$_SESSION['lang']['kebun']."</td>
								<td style='text-align:center'>".$_SESSION['lang']['ha']."</td>
								<td style='text-align:center'>".$_SESSION['lang']['pokok']."</td>
							</tr>
							</thead>
							<tbody>";
							foreach($org as $val){
								$tab .= "<tr class=rowcontent>";
								$tab .= "<td>".$val."</td>";
								$tab .= "<td style='text-align:right'>".number_format(@$tanamha[$val],2)."</td>";
								$tab .= "<td style='text-align:right'>".number_format(@$tanampokok[$val])."</td>";
								$tab .= "</tr>";
								
								@$tha+=$tanamha[$val];
								@$tpk+=$tanampokok[$val];
								
							}
							$tab .= "<tr class=rowcontent>
									 <td style='text-align:left'><b>".$_SESSION['lang']['total']."</b></td>
									 <td style='text-align:right'><b>".@number_format($tha,2)."</b></td>
									 <td style='text-align:right'><b>".@number_format($tpk)."</b></td>
									</tr>";
							$tab .= "</tbody>
						</table>
						</td>
					</tr>
				</table>
				</marquee>
			</div>
		</td>
	</tr>
</table>";

echo $tab;

function penerimaanbuah(){
	global $filpenerimaanbuah;
	global $PnBuahInt;
	global $PnBuahEks;
	
	$data = array($PnBuahInt,$PnBuahEks);

	// Create the Pie Graph. 
	$graph = new PieGraph(390,250);

	$theme_class= new PastelTheme;
	$graph->SetTheme($theme_class);

	// Set A title for the plot
	$graph->title->Set("Internal / External Kebun");

	// Create
	$p1 = new PiePlot3D($data);
	
	$p1->value->SetFont(FF_FONT1,FS_BOLD);
	$p1->value->SetColor("darkred");
	
	$lbl = array("Internal\n%.1f%%","Eksternal\n%.1f%%");
	$p1->SetLabels($lbl);
	
	$p1->SetLabelPos(0.2);
	
	$graph->Add($p1);

	$p1->ShowBorder();
	$p1->SetColor('black');
	// $p1->ExplodeSlice(1);
	$graph->Stroke($filpenerimaanbuah);
}

function proposibuah(){
	global $filproporsi;
	global $vArrProporsi;
	global $vArrOrgProporsi;
	
	$data = $vArrProporsi;

	// Create the Pie Graph. 
	$graph = new PieGraph(390,350);

	// $theme_class= new VividTheme;
	// $graph->SetTheme($theme_class);

	// Set A title for the plot
	$graph->title->Set("Proporsi Buah");

	// Create
	$p1 = new PiePlot3D($data);
	$p1->SetSize(0.45);
	$p1->SetLegends($vArrOrgProporsi);
	$graph->legend->SetPos(0.18, 0.99, 'left', 'bottom');
	
	$graph->Add($p1);
	// $graph->SetMargin(0,10,1,100);

	$p1->ShowBorder();
	$p1->SetColor('black');
	// $p1->ExplodeSlice(1);
	$graph->Stroke($filproporsi);
}

function tbsolah(){
	global $filtbsolah;
	global $vArrTbsOlah;
	global $vArrOrgTbsOlah;
	
	$data = $vArrTbsOlah;

	// Create the Pie Graph. 
	$graph = new PieGraph(390,300);
	// $theme_class= new VividTheme;
	// $graph->SetTheme($theme_class);

	// Set A title for the plot
	$graph->title->Set("TBS di Olah");

	// Create
	$p1 = new PiePlot3D($data);
	$p1->SetSize(0.45);
	$p1->SetLegends($vArrOrgTbsOlah);
	$graph->legend->SetPos(0.32, 0.99, 'left', 'bottom');
	
	$graph->Add($p1);
	// $graph->SetMargin(0,10,1,100);

	$p1->ShowBorder();
	$p1->SetColor('black');
	// $p1->ExplodeSlice(1);
	$graph->Stroke($filtbsolah);
}

function rendemen(){
	global $filrendemen;
	global $vArrRendemen;
	global $vArrOrgRendemen;
	
	$data = $vArrRendemen;

	// Create the Pie Graph. 
	$graph = new PieGraph(390,300);

	// $theme_class= new VividTheme;
	// $graph->SetTheme($theme_class);

	// Set A title for the plot
	$graph->title->Set("Rendemen");

	// Create
	$p1 = new PiePlot3D($data);
	// $p1->SetAngle(90);
	$p1->SetSize(0.45);
	$p1->SetLegends($vArrOrgRendemen);
	$graph->legend->SetPos(0.32, 0.99, 'left', 'bottom');
	
	$graph->Add($p1);
	// $graph->SetMargin(0,10,1,100);

	$p1->ShowBorder();
	$p1->SetColor('black');
	// $p1->ExplodeSlice(1);
	$graph->Stroke($filrendemen);
}

function cpo(){
	global $filcpo;
	global $vArrCpo;
	global $vArrOrgCpo;
	
	$data = $vArrCpo;

	// Create the Pie Graph. 
	$graph = new PieGraph(390,300);

	// $theme_class= new VividTheme;
	// $graph->SetTheme($theme_class);

	// Set A title for the plot
	$graph->title->Set("CPO");

	// Create
	$p1 = new PiePlot3D($data);
	$p1->SetSize(0.45);
	$p1->SetLegends($vArrOrgCpo);
	$graph->legend->SetPos(0.32, 0.99, 'left', 'bottom');
	
	$graph->Add($p1);
	// $graph->SetMargin(0,10,1,100);

	$p1->ShowBorder();
	$p1->SetColor('black');
	// $p1->ExplodeSlice(1);
	$graph->Stroke($filcpo);
}

function prdpt(){
	global $filprdpt;
	global $vArrPrdPT;
	global $vArrOrgPrdPT;
	
	$data = $vArrPrdPT;

	// Create the Pie Graph. 
	$graph = new PieGraph(385,300);

	$theme_class= new AquaTheme;
	$graph->SetTheme($theme_class);

	// Set A title for the plot
	$graph->title->Set("Produksi per PT");

	// Create
	$p1 = new PiePlot3D($data);
	$p1->SetSize(0.45);
	$p1->SetLegends($vArrOrgPrdPT);
	$graph->legend->SetPos(0.32, 0.99, 'left', 'bottom');
	
	$graph->Add($p1);
	
	$p1->ShowBorder();
	$p1->SetColor('black');
	// $p1->ExplodeSlice(1);
	$graph->Stroke($filprdpt);
}

?>