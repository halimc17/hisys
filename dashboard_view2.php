<?
require_once('config/connection.php');
@require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

// $tglskrg = '2015-07-01';
// $blnskrg = '2015-07';
// $bi = '07';
// $ti = '2015';
// $tglbsk = '2015-08-01';
$ton=array();
$ttlcus='';

$tglskrg = date('Y-m-d');
// $tglskrg = tglkemarin($tglskrg);
$blnskrg = date('Y-m');
$bi = date('m');
$ti = date('Y');
if($bi == 12){
	$tglbsk = ($ti+1)."-01-01";
}else{
	$tglbsk = $ti."-".($bi+1)."-01";
}

$tglsatubi= $ti."-".($bi)."-01";
//$nmcus=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier');
//$nmcus2=makeOption($dbname,'log_5klsupplier','kode,kelompok');
$sData="select a.kodetimbangan as kodetimbangan,b.namasupplier as namasupplier from ".$dbname.".log_5suptimbangan a left join ".$dbname.".log_5supplier b
        on a.supplierid=b.supplierid where a.status=1";
$rData=fetchData($sData);
foreach($rData as $row=>$lstData){
	//$nmcus[$lstData['kodetimbangan']]=$lstData['namasupplier'];
	$lstData['kodetimbangan']=$lstData['namasupplier'];
}

//ambil organisasi
$str=" select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $org[$bar['kodeorganisasi']] = $bar['kodeorganisasi'];
}

//ambil ton tbs internal
$str=" select kodeorg, sum(beratbersih) as beratbersih from ".$dbname.".pabrik_timbangan where substr(tanggal,1,10) >= '".$tglsatubi."' and substr(tanggal,1,10) <= '".$tglskrg."'  and kodebarang='40000003' and kodeorg !='' group by kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $ton[$bar['kodeorg']] = $bar['beratbersih'];
}

//ambil ton tbs external
$str=" select kodecustomer, sum(beratbersih) as beratbersih from ".$dbname.".pabrik_timbangan where substr(tanggal,1,10) >= '".$tglsatubi."' and substr(tanggal,1,10) <= '".$tglskrg."'  and kodebarang='40000003' and kodeorg ='' and kodecustomer!='' group by kodecustomer";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $cus[$bar['kodecustomer']] = $bar['kodecustomer'];
    $toncus[$bar['kodecustomer']] = $bar['beratbersih'];
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
			<div>
				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr class=rowcontent>
						<td align=left width=1% nowrap style='font-weight:bold'>".$_SESSION['lang']['penerimaan']." ".$_SESSION['lang']['tbs']." di PMKS<br>
						".strtolower($_SESSION['lang']['sd'])." ".$_SESSION['lang']['tanggal']." : ".tanggalNormal($tglskrg)."<br><i><b><font size=1>Source : Timbangan PMKS</font></b></i>
						</td>
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
								<td style='text-align:center'>".$_SESSION['lang']['Ton']."</td>
							</tr>
							</thead>
							<tbody>";
							
							
							
							foreach($org as $val){
								$tab .= "<tr class=rowcontent>";
								$tab .= "<td align=center>".$val."</td>";
								$tab .= "<td align=right>".@number_format($ton[$val]/1000,2)."</td>";
				
								$tab .= "</tr>";
								@$ttl+=$ton[$val];
							}
								$tab .= "<tr class=rowcontent>
										 <td style='text-align:center'><b>".$_SESSION['lang']['total']."</b></td>
										 <td style='text-align:right'><b>".number_format($ttl/1000,2)."</b></td>
										</tr>";

							$tab .= "</tbody>
						</table>
					</td>
					</tr>
				</table>


				<table class=sortable cellspacing=1 border=0 width=100%>
					<tr class=rowcontent>
					<td align=left width=1% nowrap>
						<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
							<thead>
							<tr class=rowheader>
								<td style='text-align:center'>".$_SESSION['lang']['customer']."</td>
								<td style='text-align:center'>".$_SESSION['lang']['Ton']."</td>
							</tr>
							</thead>
							<tbody>";
							
							
							if(isset($cus))
							foreach($cus as $val){
								$tab .= "<tr class=rowcontent>";
								$tab .= "<td align=center><font size=1>".(@$nmcus[$val]!='' ? substr(@$nmcus[$val],0,12) : substr(@$nmcus2[$val],0,12))."</font></td>";
								$tab .= "<td align=right>".number_format($toncus[$val]/1000,2)."</td>";
				
								$tab .= "</tr>";
								@$ttlcus+=$toncus[$val];
							}
								$tab .= "<tr class=rowcontent>
										 <td style='text-align:center'><b>".$_SESSION['lang']['total']."</b></td>
										 <td style='text-align:right'><b>".@number_format($ttlcus/1000,2)."</b></td>
										</tr>";

							$tab .= "</tbody>
						</table>
						
						<hr>
						
						<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
						<tr class=rowcontent>
							<td align=center><b>Grand Total</b></td>
							<td style='text-align:right'><b>".@number_format(($ttl+$ttlcus)/1000,2)."</b></td>
						</tr>
						</table>
					
					</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>";
echo $tab;

?>