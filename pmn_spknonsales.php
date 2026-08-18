<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/pmn_spknonsales.js?v=<?php echo time(); ?>'></script>

<!--deklarasi untuk option-->
<?php



// $nokontrak=$_GET['nokontrak'];
// $nokontraktampung=$_GET['nokontrak'];

$optbuyer=$optbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".pmn_4customer  order by namacustomer asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbuyer.="<option value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
}

$str = "select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('pmn_spknonsales').'<br></span>');
CLOSE_BOX();
OPEN_BOX();
// echo"<fieldset><legend><b>Form</b></legend>";
		echo"<table class='sortable' cellspacing='1' cellpadding='5' border='0'>";
		echo"<thead><tr class=rowheader>";
		echo"<th align=center>".$_SESSION['lang']['nomor']."</th>";
		echo"<th align=center>".$_SESSION['lang']['kode']."</th>";
		echo"<th align=center>".$_SESSION['lang']['nama']."</th>";
		echo"<th align=center>".$_SESSION['lang']['keterangan']."</th>";
		echo"</tr></thead>";
		$str="select * from ".$dbname.".pmn_5jenisspk where nonpenjualan=1";
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()) {
			$kodejenis[$bar['kode']]=$bar['kode'];
			$namajenis[$bar['kode']]=$bar['nama'];
			$ketjenis[$bar['kode']]=$bar['keterangan'];
			$filejenis[$bar['kode']]=$bar['filenonpenjualan'];
		}
		foreach($kodejenis as $kdjenis){
			$attribut = "style='cursor:pointer;text-decoration: underline' title='Click to Detail' onclick=\"loadtransaksi('".$filejenis[$kdjenis]."','".$kdjenis."')\";";
			$no++;
			echo"<tr class=rowcontent>";
				echo"<td align=center>".$no."</td>";
				echo"<td ".$attribut.">".$kdjenis."</td>";
				echo"<td>".$namajenis[$kdjenis]."</td>";
				echo"<td>".$ketjenis[$kdjenis]."</td>";
			echo"</tr>";
		}
		echo"</table>";
	echo"</fieldset>";
CLOSE_BOX();

echo close_body();		////<input type=hidden id=method value='insert'>	
?>