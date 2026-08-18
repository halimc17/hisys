<?php

require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
	$gen='generic.css';
}else if($theme=='red'){
	$gen='genericRed.css';  
}else{
	$gen='genericGray.css';  
}  
echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 
?>
<script language=javascript1.2 src='js/master_barang.js'></script>
<script language=javascript1.2 src='js/generic.js'></script>
<?php

$kodebarang = $_GET['kodebarang'];
$str = "select a.*,b.*,a.kodebarang as kodebarang from " . $dbname . ".log_5masterbarang a
        left join " . $dbname . ".log_5photobarang b 
		on a.kodebarang=b.kodebarang
       where a.kodebarang='" . $kodebarang . "'";
$depan = '';
$samping = '';
$atas = '';
$spesifikasi = '';

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $namabarang = $bar->namabarang;
    $satuan = $bar->satuan;
    $depan = $bar->depan;
    $samping = $bar->samping;
    $atas = $bar->atas;
    $spesifikasi = $bar->spesifikasi;
}
OPEN_BOX();
echo"<fieldset>
	<legend>History</legend>";
echo"<table cellspacing=1 border=0 cellpadding=5 class=sortable width=100%>
      <thead>
	  <tr class=rowheader>
	  <th align=center>No</th>
	  <th align=center colspan=2>" .$_SESSION['lang']['materialgroupcode']. "</th>
	  <th align=center colspan=2>" .$_SESSION['lang']['kodesubkelompokbarang'] . "</th>
	  <th align=center>" . $_SESSION['lang']['materialcode'] . "</th>
	  <th align=center>" . $_SESSION['lang']['materialname'] . "</th>
	  <th align=center>" . $_SESSION['lang']['satuan'] . "</th>
	  <th align=center>" . $_SESSION['lang']['jenis'] . "</th>
	  <th align=center>" . $_SESSION['lang']['konversi'] . "</th>
	  <th align=center>" . $_SESSION['lang']['inisial'] . "</th>
	  <th align=center>" . $_SESSION['lang']['status'] . "</th>
	  <th align=center>" . $_SESSION['lang']['createby'] . "</th>
	  <th align=center>" . $_SESSION['lang']['updateby'] . "</th>
	  <th align=center>Kategori Barang</th>	  
	  <th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
	  </tr>
	  </thead>";
	$arrjeni=array('slow'=>'Slow Moving','fast'=>'Fast Moving','non'=>'Non Moving');
	$strx = "select * from ".$dbname.".log_5klbarang";
	$resx = fetchdata($strx);
	foreach($resx as $bar){
		$klbarang[$bar['kode']]=$bar['kelompok'];
	}

	$strx = "select * from ".$dbname.".log_5subklbarang";
	$resx = fetchdata($strx);
	foreach($resx as $bar){
		$subklbarang[$bar['kode']]=$bar['namasubkelompok'];
	}
	$skonversi = array ("0"=>"No","1"=>"Yes");
	$optkodevh=['0'=>'Tidak','1'=>'Wajib Terisi'];  
	$convertKategori = makeOption($dbname,"log_5kategoribarang","id,jenis"); 
	
	$strx="SHOW COLUMNS FROM ".$dbname.".log_5masterbarang";
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
	$arrdtc=array();
	while($bar=$resx->fetch()){
		$arrdtc[$bar->Field]='';
	}
	
	echo"<tr class=rowcontent>
		<td colspan=16><b>Sesudah:</b></td>";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);	
	while($bar=$res->fetch()){
		$no++;
		echo"<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td align=center bgcolor=".$arrdtc['kelompokbarang'].">".$bar->kelompokbarang."</td>
		<td align=left bgcolor=".$arrdtc['kelompokbarang'].">".$klbarang[$bar->kelompokbarang]."</td>
		<td align=center >".substr($bar->kodebarang,0,5)."</td>
		<td align=left>".$subklbarang[substr($bar->kodebarang,0,5)]."</td>
		<td align=center bgcolor=".$arrdtc['kodebarang'].">".$bar->kodebarang."</td>
		<td bgcolor=".$arrdtc['kodebarang'].">".$bar->namabarang."</td>
		<td align=center bgcolor=".$arrdtc['satuan'].">".$bar->satuan."</td>
		<td align=center bgcolor=".$arrdtc['jenis'].">".$arrjeni[$bar->jenis]."</td>
		<td align=center bgcolor=".$arrdtc['konversi'].">".$skonversi[$bar->konversi]."</td>
		<td align=center bgcolor=".$arrdtc['inisial'].">".$bar->inisial."</td>
		<td align=center bgcolor=".$arrdtc['inactive'].">".($bar->inactive=='1' ? 'Non-Aktif' : 'Aktif')."</td>
		<td align=center bgcolor=".$arrdtc['createby'].">".getKary($bar->createby)."<br>".($bar->updatetime!='0000-00-00 00:00:00'?$bar->updatetime:"")."</td>
		<td align=center bgcolor=".$arrdtc['updateby'].">".getKary($bar->updateby)."<br>".($bar->updatetime!='0000-00-00 00:00:00'?$bar->updatetime:"")."</td>
		";
		echo"<td align=center nowrap bgcolor=".$arrdtc['idkategorinya'].">" . $convertKategori[$bar->idkategorinya] . "</td>
		<td align=center nowrap bgcolor=".$arrdtc['kodevhc'].">" . $optkodevh[$bar->kodevhc] . "</td>";
		echo "</tr>";
	}
	
	echo"<tr class=rowcontent>
		<td colspan=16><b>Sebelumnya:</b></td>";
		
	$qData = selectQuery($dbname,'log_5masterbarang_hist','*',"kodebarang='".$kodebarang."' order by id desc");
	$res=$owlPDO->query($qData) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$res->fetch()){
		$arr1x=explode('###', $bar->datachange);
		foreach ($arr1x as $key => $val) {
			if($val!=''){
				$arrdtc[$val]='red';
			}
		}
		$no++;
		echo"<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td align=center bgcolor=".$arrdtc['kelompokbarang'].">".$bar->kelompokbarang."</td>
		<td align=left bgcolor=".$arrdtc['kelompokbarang'].">".$klbarang[$bar->kelompokbarang]."</td>
		<td align=center >".substr($bar->kodebarang,0,5)."</td>
		<td align=left>".$subklbarang[substr($bar->kodebarang,0,5)]."</td>
		<td align=center bgcolor=".$arrdtc['kodebarang'].">".$bar->kodebarang."</td>
		<td bgcolor=".$arrdtc['kodebarang'].">".$bar->namabarang."</td>
		<td align=center bgcolor=".$arrdtc['satuan'].">".$bar->satuan."</td>
		<td align=center bgcolor=".$arrdtc['jenis'].">".$arrjeni[$bar->jenis]."</td>
		<td align=center bgcolor=".$arrdtc['konversi'].">".$skonversi[$bar->konversi]."</td>
		<td align=center bgcolor=".$arrdtc['inisial'].">".$bar->inisial."</td>
		<td align=center bgcolor=".$arrdtc['inactive'].">".($bar->inactive=='1' ? 'Non-Aktif' : 'Aktif')."</td>
		<td align=center bgcolor=".$arrdtc['createby'].">".getKary($bar->createby)."<br>".($bar->updatetime!='0000-00-00 00:00:00'?$bar->updatetime:"")."</td>
		<td align=center bgcolor=".$arrdtc['updateby'].">".getKary($bar->updateby)."<br>".($bar->updatetime!='0000-00-00 00:00:00'?$bar->updatetime:"")."</td>
		";
		echo"<td align=center nowrap bgcolor=".$arrdtc['idkategorinya'].">" . $convertKategori[$bar->idkategorinya] . "</td>
		<td align=center nowrap bgcolor=".$arrdtc['kodevhc'].">" . $optkodevh[$bar->kodevhc] . "</td>";
	}
	
	$qData = selectQuery($dbname,'log_5masterbarang_hist','*',"kodebarang='".$kodebarang."'");
	$resData = fetchData($qData);
	
	
	
echo "</tr>";
echo "</table>";
echo"</fieldset>";
echo"<fieldset>
	<legend>[" . $kodebarang . "]" . $namabarang . "(" . $satuan . ")</legend>
	<table>
		<tr>
			<td>Spec</td>
			<td>".nl2br($spesifikasi)."</td>
		</tr>
	</table>
	<br>
	<table class=sortable cellspacing=1>
		<thead>
		<tr>
			<td style='text-align:center'>".$_SESSION['lang']['keterangan']."</td>
			<td style='text-align:center'>Image</td>
			<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody>
		<tr class=rowcontent>
			<td style='text-align:center'>Pic1 (Depan)</td>
			<td style='text-align:center'>
				<img id='imgdepan' src='".($depan==''?'images/noimages.png':$depan)."' height=150px>
			</td>
			<td id='tddepan' style='text-align:center'>
				".($depan==''?'':"<img src='images/application/application_delete.png' class='resicon' title='Delete' onclick=\"parent.deleteimage('depan','".$kodebarang."')\">")."
			</td>
		</tr>
		<tr class=rowcontent>
			<td style='text-align:center'>Pic2 (Samping)</td>
			<td style='text-align:center'><img id='imgsamping' src='".($samping==''?'images/noimages.png':$samping)."' height=150px></td>
			<td id='tdsamping' style='text-align:center'>
				".($samping==''?'':"<img src='images/application/application_delete.png' class='resicon' title='Delete' onclick=\"parent.deleteimage('samping','".$kodebarang."')\">")."
			</td>
		</tr>
		<tr class=rowcontent>
			<td style='text-align:center'>Pic3 (Atas)</td>
			<td style='text-align:center'><img id='imgatas' src='".($atas==''?'images/noimages.png':$atas)."' height=150px></td>
			<td id='tdatas' style='text-align:center'>
				".($atas==''?'':"<img src='images/application/application_delete.png' class='resicon' title='Delete' onclick=\"parent.deleteimage('atas','".$kodebarang."')\">")."
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>";
CLOSE_BOX();
?>
