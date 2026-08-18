<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$where = "";
if(isset($_POST['kodept'])){
	$where .= " and a.kodept = '".$_POST['kodept']."'";
}
if(!empty($_POST['po'])){
	$where .= " and a.nopo like '%".$_POST['po']."%'";
}
if(isset($_POST['kodeorg'])){
	$where .= " and a.kodegudang = '".$_POST['kodeorg']."'";
}
function cariyangtidaksama($data,$kodebarang,$jml){
	$value = 0;
	if(isset($data[$kodebarang])){
		$value = $data[$kodebarang];
	}
	return $value;
}
$data = array();
$strs="select kodebarang,saldoqty from ".$dbname.".log_5masterbarangdt a where kodeorg='".$_POST['kodept']."'
	and kodegudang='".$_POST['kodeorg']."'"; 
$ress=$owlPDO->query($strs) or die(print " Gagal: ".PDOException::getMessage());
$ress->setFetchMode(PDO::FETCH_OBJ);
while($bars=$ress->fetch())
{
	$data[$bars->kodebarang] = $bars->saldoqty;
}

/*
$where = "a.kodeorg like '%".$_POST['kodept']."%' and a.statuspo>1";
// $where .= " and (b.nosj IS NULL or (a.jumlahpesan>b.jumlah and b.nosj<>'".$_POST['nosj']."'))";
if(!empty($_POST['po'])) {
	$where .= " and a.nopo like '%".$_POST['po']."%'";
}

$query = "select distinct 
		a.nopo,a.kodebarang,a.namabarang,a.nopp,
		a.jumlahpesan,a.satuan
	from ".$dbname.".log_po_vw a
	where ".$where;
	*/

$query = "select  a.notransaksi,
		a.nopo,a.kodebarang,a.nopp,a.nopo,b.namabarang ,a.jumlah,a.satuan,a.kodept,a.kodegudang 
	from ".$dbname.".log_transaksi_vw a
	left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
	where a.tipetransaksi= '1' and a.post=1 ".$where;	
$result = fetchData($query);

/*$q2 = "SELECT nopo,kodebarang,nopp,sum(jumlah) as jumlah FROM ".$dbname.".`log_rinciankono` where nopo like '%".$_POST['po']."%'";
$data2 = fetchData($q2);
$optData = array();
foreach($data2 as $row) {
	$optData[$row['nopo']][$row['nopp']][$row['kodebarang']] = $row['jumlah'];
}*/
?>
<fieldset><legend><i>Result</i></legend>
<button class="mybutton" onclick="add2detail('po')" >Add to Detail</button>
<div style="max-height:340px;overflow:auto">
<table cellpadding=1 cellspacing=1 border=0 class='sortable'>
	<thead><tr class=rowheader>
		<td align='center'></td>
		<td align='center'><?php echo $_SESSION['lang']['notransaksi']?></td>
		<td align='center'><?php echo $_SESSION['lang']['nopo']?></td>
		<td align='center'><?php echo $_SESSION['lang']['kodebarang']?></td>
		<td align='center'><?php echo $_SESSION['lang']['namabarang']?></td>
		<td align='center'><?php echo $_SESSION['lang']['jumlah']?></td>
		<td align='center'><?php echo $_SESSION['lang']['nopp']?></td>
		<td align='center'><?php echo $_SESSION['lang']['jumlah'].' terkirim'?></td>
		<td align='center'><?php echo $_SESSION['lang']['satuan']?></td>
	</tr></thead>
	<tbody id=bodySearch>
		<?php 
		$i=0;
		foreach($result as $key=>$row){
			## Cek Surat Jalan
			$str="select sum(jumlah) as jumlah from ".$dbname.".log_suratjalandt where nopp='".$row['nopp']."' and nopo='".$row['nopo']."' and kodebarang='".$row['kodebarang']."' and nogr='".$row['notransaksi']."'";
			$res=fetchdata($str);
			$jumlahsj = $res[0]['jumlah'];
			if($jumlahsj==''){
				$jumlahsj = 0;
			}
			
			## Cek Packing List
			$str="select sum(jumlah) as jumlah from ".$dbname.".log_packingdt where nopp='".$row['nopp']."' and nopo='".$row['nopo']."' and kodebarang='".$row['kodebarang']."' and nobpb='".$row['notransaksi']."'";
			$res=fetchdata($str);
			$jumlahpl = $res[0]['jumlah'];
			if($jumlahpl==''){
				$jumlahpl = 0;
			}
			
			$jumlahpo = $row['jumlah'];
			$jumlahkirim = $jumlahsj+$jumlahpl;
			$saldobarang = cariyangtidaksama($data,$row['kodebarang'],$row['jumlah']);
			
			// echo cariyangtidaksama($data,$row['kodebarang'],$row['jumlah'])."__".($row['jumlah']+$row['jumlahsj']+$row['jumlahpl'])."<br>";
			
			if($saldobarang > 0 && $jumlahpo > $jumlahkirim){
		?>
		<tr class="rowcontent">
				<td align='center'>
				<?php 
					echo makeElement('po_'.$i,'checkbox',0);
				?>
				</td>
			<td id="notransaksi_<?php echo $i?>"><?php echo $row['notransaksi']?></td>
			<td id="nopo_<?php echo $i?>"><?php echo $row['nopo']?></td>
			<td id="kodebarang_<?php echo $i?>"><?php echo $row['kodebarang']?></td>
			<td id="namabarang_<?php echo $i?>"><?php echo $row['namabarang']?></td>
			<td id="jumlah_<?php echo $i?>" align=right><?php echo $jumlahpo; ?></td>
			<td id="nopp_<?php echo $i?>" align=right><?php echo $row['nopp']?></td>
			<td id="jumlahkirim_<?php echo $i?>" align="right">
				<?php echo $jumlahkirim; ?>
			</td>
			<td id="satuan_<?php echo $i?>"><?php echo $row['satuan']?></td>
		</tr>
		<?php
		$i++;
			}
			
		};?>
	</tbody>
</table>
</div></fieldset>