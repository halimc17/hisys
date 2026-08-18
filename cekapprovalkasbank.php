<?
include('lib/nangkoelib.php');
include('lib/zLib.php');

$str = "select distinct notransaksi, kodeorg from ".$dbname.".keu_kasbankht where posting = '9' and notransaksi like '2021%' limit 50";
$res=fetchdata($str);
foreach($res as $bar){
	$data[$bar['kodeorg']][$bar['notransaksi']]=$bar['notransaksi'];
	
	$str1 = "select max(level) as level, kodeunit   from ".$dbname.".setup_approval where jenispersetujuan = 'KASBANK' and kodeunit='".$bar['kodeorg']."'";
	$res1=fetchdata($str1);
	foreach($res1 as $bar1){
		$str2 = "select *  from ".$dbname.".approval where jenispersetujuan = 'KASBANK' and level='".$bar1['level']."' and notransaksi='".$bar['notransaksi']."' and status='1'";
		$res2=fetchdata($str2);
		foreach($res2 as $bar2){
			echo $bar2['notransaksi']."<br>";
		}
	}
}

echo "xxxxxxxxx";

?>