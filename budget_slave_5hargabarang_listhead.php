<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$str="select regional, tahunbudget, sumberharga,closed from ".$dbname.".bgt_masterbarang group by regional, tahunbudget order by tahunbudget desc"; 

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=1;
while($bar= $res->fetch()){
    echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center><label id=tahun2_".$no.">".$bar->tahunbudget."</label></td>
			<td align=center><label id=reg2_".$no.">".$bar->regional."</label></td>";
		if($bar->closed=='0'){
			echo"<td align=center><button id=edit_".$no." class=mybutton onclick=tampolkanHarga(".$bar->tahunbudget.",'".$bar->regional."','".$bar->sumberharga."')>".$_SESSION['lang']['edit']."</button></td>";			
			echo"<td align=center><button id=close_".$no." class=mybutton onclick=TutupHarga(".$no.",".$no.")>".$_SESSION['lang']['close']."</button></td>";
			echo"<td align=center><button class=mybutton onclick=deleteHarga(".$bar->tahunbudget.",'".$bar->regional."')>".$_SESSION['lang']['delete']."</button></td>";			
		}else{
			echo"<td align=center></td>";			
			echo"<td align=center><button id=close_".$no." class=mybutton onclick=BukaHarga(".$no.",".$no.")>".$_SESSION['lang']['buka']."</button></td>";
			echo"<td align=center></td>";			
		}
		if($bar->closed=='0'){
		}else{
		}
			
		echo"<td align=center><button class=mybutton onclick=listHarga('".$bar->tahunbudget."','".$bar->regional."','".$no."')>".$_SESSION['lang']['preview']."</button></td>";
		echo"<td align=center><button class=mybutton onclick=hargaKeExcel(event,".$no.")>Excel</button></td>";
		
		echo"<td align=center><button class=mybutton onclick=addbarang('".$bar->tahunbudget."','".$bar->regional."')>Rev/Add Harga</button></td>";	
    echo"</tr>";
    $no+=1;
}