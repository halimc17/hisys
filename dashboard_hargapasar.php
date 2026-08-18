<?
require_once('config/connection.php');
@require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

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
$tab .= "<script language=JavaScript1.2 src=js/".$men."></script>";

$tab .="<table style='font-size:12'>
		<tr class=rowcontent>
			<td width=1% nowrap style='font-weight:bold'>".$_SESSION['lang']['hargapasar']."</td>
			<td align=right width=1% nowrap style='text-align:right'>".date('d-m-Y H:i:s')."</td>
		</tr>
	</table>";
	
$str = "select * from ".$dbname.".pmn_hargapasar where tanggal = '".date('Y-m-d')."' order by pasar asc, kodeproduk asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$jlhrow=owlBaris($res);
$tab .="<marquee height=130px onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up>
<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
		<thead>
		<tr class=rowheader>
			<td style='font-weight:bold;text-align:center'>".$_SESSION['lang']['pasar']."</td>
			<td style='font-weight:bold;text-align:center'>".$_SESSION['lang']['produk']."</td>
			<td style='font-weight:bold;text-align:center'>".$_SESSION['lang']['harga']."</td>
		</tr>
		</thead>
		<tbody>";
if($jlhrow == 0){
	$tab .="<tr class=rowcontent>
		<td colspan=3 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td>
	</tr>";
}else{
	while($bar = $res->fetch()){
		$optProduk = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang = '".$bar['kodeproduk']."'");
		$tab .="<tr class=rowcontent>
			<td>".$bar['pasar']."</td>
			<td>".$optProduk[$bar['kodeproduk']]."</td>
			<td style='text-align:right'>".number_format($bar['harga'])."</td>
		</tr>";
	}
}

/*
#escape dulu biar cepat

## Harga direct External
$sites['monyc']='http://www.moneycontrol.com/commodity/cpo-price.html';
$sites['mundi']='http://www.indexmundi.com/commodities/?commodity=palm-oil';
$sites['reauk']='http://www.rea.co.uk/rea/en/markets/cpoprices';


$sites['name']['monyc']='moneycontrol.com';
$sites['name']['mundi']='indexmundi.com';
$sites['name']['reauk']='rea.co.uk';
$sites['commodity']['monyc']='CPO';
$sites['commodity']['mundi']='CPO';
$sites['commodity']['reauk']='CPO';
$sites['curr']['monyc']='US$';
$sites['curr']['mundi']='US$';
$sites['curr']['reauk']='US$';



if($monyc = @file_get_contents($sites['monyc'])){	
	$monyc =str_replace(" ", "", $monyc);
	$pos=strpos($monyc, 'FLbrdr');
	$harga['monyc']=substr($monyc, ($pos+36),9);
}

if($mundi = @file_get_contents($sites['mundi'])){
	$mundi =str_replace(" ", "", $mundi);
	$pos=strpos($mundi, '<spanclass="dailyPrice">');
	$harga['mundi']=substr($mundi, ($pos+24),9);
}

if($reauk = @file_get_contents($sites['reauk'])){
	$reauk =str_replace(" ", "", $reauk);
	$pos=strpos($reauk, '<pclass="bordered">Closingpriceon');
	$harga['reauk']=substr($reauk, ($pos+55),9);
	$harga['reauk']=str_replace('US$', '', $harga['reauk']);
}

$tab.= "<tr><td colspan=3>Other Source:</td></tr>";
if(count($harga)>0){
	foreach($harga as $k =>$val){
		$tab.="<tr class=rowcontent><td>".$sites['name'][$k]."</td><td>".$sites['commodity'][$k]."</td><td align=right>".$sites['curr'][$k]." ".$harga[$k]."</td></tr>";
	}
}else{
	$tab.="<tr><td colspan=3>Server can't connect to internet</td></tr>";
}
*/
$tab .="</tbody></table></marquee>";
echo $tab;
?>