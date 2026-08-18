<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');

$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


if(isTransactionPeriod())//check if transaction period is normal
{
//========================
 
	$notransaksi=$_POST['notransaksi'];
	$gudang=$_POST['gudang'];
	$jlhbaris=0;
	$str="select a.tipetransaksi,a.notransaksi,a.tanggal,a.kodept,a.kodegudang,
		   b.kodebarang,b.satuan,b.jumlah,b.nopp
		   from ".$dbname.".log_transaksiht a 
		   left join ".$dbname.".log_transaksidt b on
		   a.notransaksi=b.notransaksi
		   where a.notransaksi='".$notransaksi."'
		  and a.tipetransaksi =7 order by waktutransaksi asc";
	echo "<table class=sortable cellpadding=5 cellspacing=1 border=0>
		  <thead>
			 <tr>
				<th align=center>No.</th>
				<th align=center>".$_SESSION['lang']['notransaksi']."</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center width=70px>".$_SESSION['lang']['jumlah']."</th>
				<th align=center width=70px>".$_SESSION['lang']['diterimasebelumnya']."</th>
				<th align=center width=50px>".$_SESSION['lang']['jumlahditerima']."</th>
				<th align=center>".$_SESSION['lang']['kodept']."</th>
				<th align=center>".$_SESSION['lang']['kodeorgpengirim']."</th>
				<th align=center>".$_SESSION['lang']['penerima']."</th>
				<th align=center>".$_SESSION['lang']['nopp']."</th>
			 </tr>
		   </thead>
		   <tbody>";
	$no=0;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$jlhbaris=0;

	while($bar=$res->fetch())
	{
		//ambil namabarang
		$stru="select namabarang from ".$dbname.".log_5masterbarang 
			  where kodebarang='".$bar->kodebarang."'";
		$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
		$resu->setFetchMode(PDO::FETCH_OBJ);
		$namabarang='';
		while($baru=$resu->fetch())
		{
			$namabarang=$baru->namabarang;
		}
			  
		$strSebelum="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where kodebarang='".$bar->kodebarang.
					"' and notransaksireferensi='".$bar->notransaksi."' and nopp='".$bar->nopp."' and tipetransaksi=3";
		$resu=$owlPDO->query($strSebelum) or die(print " Gagal: ".PDOException::getMessage());
		$resu->setFetchMode(PDO::FETCH_OBJ);
		$jmlSebelum=0;
		while($baru=$resu->fetch())
		{
			$jmlSebelum=isset($baru->jumlah)? $baru->jumlah: 0;
		}
		$sisa=$bar->jumlah-$jmlSebelum;
		if ($sisa>0){
			$no+=1;
			$jlhbaris+=1;
			$disform="";
			if(substr($bar->kodegudang,0,4)==substr($gudang,0,4)){
				$disform="disabled";
			}
			echo"<tr class=rowcontent id=row".$no.">
			  <td align=center>".$no."</td>
			  <td id=notransaksi".$no.">".$bar->notransaksi."</td>
			  <td align=center>".getDetailTipeMutasi($bar->tipetransaksi)."</td>
			  <td align=center id=kodebarang".$no.">".$bar->kodebarang."</td>	  
			  <td id=namabarang".$no.">".$namabarang."</td>
			  <td id=satuan".$no.">".$bar->satuan."</td>
			  <td align=right id=jumlah".$no.">".@hidezerodecimal($bar->jumlah,5)."</td>
			  <td align=right id=sebelum".$no.">".@hidezerodecimal($jmlSebelum,5)."</td>
			  <td><input disabled type=text id=diterima".$no." size=6 onkeypress=\"return angka_doang(event)\" ".$disform." value=".@hidezerodecimal($sisa,5)." class=myinputtextnumber /></td>
			  <td id=kodept".$no.">".$bar->kodept."</td>			  
			  <td id=asalgudang".$no.">".$bar->kodegudang."</td>
			  <td id=gudang".$no.">".$gudang."</td>
			  <td id=nopp".$no.">".$bar->nopp."</td>
			  </tr>";
		}
	}
		echo"<tr><td colspan=13 align=center>
			<button onclick=mulaiSimpan(1) id='btnmulaisimpan' class=mybutton>".$_SESSION['lang']['save']."</button>
			</td></tr>";
	echo"</tbody><tfoot>
		</tfoot></table>
	";
}
else
{
	echo " Error: Transaction Period missing";
}