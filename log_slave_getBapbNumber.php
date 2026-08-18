<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

// $notransaksi = checkPostGet('notransaksi','');
// $method = checkPostGet('method','');
// $id = checkPostGet('id','');
// $namafile = checkPostGet('namafile','');
if(isTransactionPeriod())//check if transaction period is normal
{
 //default penerimaan barang 
 //tipe 1=masuk normal, 2 retur pengeluaran, 5=pengeluaran normal,6=retur pemsaukan(ke supplier)
 //============================================   
  $gudang	=$_POST['gudang'];
  $simpan	=$_POST['simpan'];
  $num=1;//default value 
  $str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht where tipetransaksi<5 and tanggal>=".$_SESSION['gudang'][$gudang]['start']." and tanggal<=".$_SESSION['gudang'][$gudang]['end']."
        and kodegudang='".$gudang."' order by notransaksi";	
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  if(owlBaris($res)>0)
  {
  	while($bar=$res->fetch())
	{
		$num=$bar->notransaksi;
		if($num!='')
		{
			$num=intval(substr($num,6,5))+1;
		}
		else
		{
		  $num=1;	
		}	
	}
  }
	if($num<10)
	   $num='0000'.$num;
	else if($num<100)
	   $num='000'.$num;
	else if($num<1000)
	   $num='00'.$num;	   
	else if($num<10000)
           $num='0'.$num;
        else
	   $num=$num;
        
   $num=$_SESSION['gudang'][$gudang]['tahun'].$_SESSION['gudang'][$gudang]['bulan'].$num."-GR-".$gudang;

	$tab = "";
	$lbl = "";
	$subgdg = substr($gudang,0,4);
	$countApp = getCountApproval('GR',$subgdg);
	
	$tab.="<fieldset style=float:left>
		<legend>".$_SESSION['lang']['persetujuan']."</legend>
		<table>";
	// $lbl.="<table class=sortable cellspacing=0 border=0>
		// <thead>
		// <tr class=rowheader>";
	
	for($i=1;$i<=$countApp;$i++)
	{
		$arrListApp = listApprove($i,'GR',$subgdg);
		foreach($arrListApp as $key=>$val)
		{
			$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
		}
		$tab.="<tr>
			<td>".$_SESSION['lang']['persetujuan']." ".$i."</td>
			<td>:</td>
			<td>
				<select id='persetujuan".$i."' style='width:200px'>".$optKry."</select>
				<img id='persetujuan".$i."' onclick=z.elSearch('persetujuan1',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>";
		
		$lbl .= "<td align=center>Ke-".$i."</td>";
		$optKry='';
	}
	
	// $lbl.="</tr>
	// </thead>
	// </table>";
	
	$tab.="</table>
	</fieldset>";
	
//   echo $num."####".$tab."####".$lbl."####".$countApp;
if($simpan == 'simpan'){
	echo $num;
}else{
	echo $num."####".$tab."####".$lbl."####".$countApp."####".$optsj;
}
}
else
{
	echo " Error: Transaction Period missing";
}


?>