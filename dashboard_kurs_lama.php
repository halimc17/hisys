<?
require_once('config/connection.php');
@require_once('master_validation.php');
require_once('lib/nangkoelib.php');

error_reporting(0);
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

$tab .= kurs('BCA', 'http://www.bi.go.id/id/moneter/informasi-kurs/transaksi-bi/Default.aspx', 4, 0, 2);


function kurs($bank, $url, $tdr, $tdr1, $tdr2) {
	global $dbname;
	
	$url = $url;
	$chp = curl_init();
	$agent = "Googlebot/2.1 (http://www.googlebot.com/bot.html)";
	curl_setopt($chp, CURLOPT_USERAGENT, $agent);
	curl_setopt($chp, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($chp, CURLOPT_URL, $url);
	$content = curl_exec($chp);
	$dom = new DOMDocument;
	$dom->loadHTML( $content );
	$rows = array();
	foreach( $dom->getElementsByTagName( 'tr' ) as $tr ) {
		$cells = array();
		foreach( $tr->getElementsByTagName( 'td' ) as $td ) {
			$cells[] = $td->nodeValue;
		}
		$rows[] = $cells;
	}
	
	$arrMatauang = array();
	$str = "select * from ".$dbname.".setup_matauang";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar = $res->fetch()){
		$arrMatauang[] = $bar['kode'];
	}
	
	echo "<table style='font-size:12'>
		<tr class=rowcontent>
			<td width=1% nowrap style='font-weight:bold'>Kurs</td>
			<td align=right width=1% nowrap style='text-align:right'>".date('d-m-Y H:i:s')."</td>
		</tr>
	</table>";
	
	echo "<marquee height=108px onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up>
	<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>
		<thead>
		<tr class=rowheader>
			<td style='font-weight:bold;text-align:center'>Mata Uang</td>
			<td style='font-weight:bold;text-align:center'>Kurs Jual</td>
			<td style='font-weight:bold;text-align:center'>Kurs Beli</td>
		</tr>
		</thead>
		<tbody>";
	foreach($rows as $val){
		if(isset($val[1]) && isset($val[2]) && isset($val[3]) && isset($val[4])){
			foreach($arrMatauang as $row){
				if(trim($row) == trim($val[0])){
					echo "<tr class=rowcontent>
						<td>".trim($val[0])."</td>
						<td style='text-align:right'>".$val[2]."</td>
						<td style='text-align:right'>".$val[3]."</td>
					</tr>";
				}
			}
		}
	}
	echo "</tbody><table></marquee>";
}

echo $tab;
?>