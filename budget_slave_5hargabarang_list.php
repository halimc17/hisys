<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$tahunbudget=$_POST['tahunbudget'];
$regional=$_POST['regional'];
$barang=$_POST['namabarangcari'];

$wh="";
if($barang!=''){
	$wh=" and (kodebarang like '%".$barang."%' or kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where namabarang like '%".$barang."%'))";
}



$str="select regional, tahunbudget, kodebarang, hargasatuan, sumberharga, variant, hargalalu from ".$dbname.".bgt_masterbarang
      where tahunbudget = '".$tahunbudget."' and regional = '".$regional."' ".$wh." order by regional";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$kobar='';
while($bar= $res->fetch()){
   $isidata[$bar->kodebarang]['regional']=$bar->regional;
   $isidata[$bar->kodebarang]['tahunbudget']=$bar->tahunbudget;
   $isidata[$bar->kodebarang]['kodebarang']=$bar->kodebarang;
   $isidata[$bar->kodebarang]['hargasatuan']=$bar->hargasatuan;
   $isidata[$bar->kodebarang]['sumberharga']=$bar->sumberharga;
   $isidata[$bar->kodebarang]['variant']=$bar->variant;
   $isidata[$bar->kodebarang]['hargalalu']=$bar->hargalalu;
   $kobar.="'".$bar->kodebarang."',";
}
    
$kobar=substr($kobar,0,-1);
$str="select kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang where kodebarang in (".$kobar.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar= $res->fetch()){
   $isidata[$bar->kodebarang]['namabarang']=$bar->namabarang;
   $isidata[$bar->kodebarang]['satuan']=$bar->satuan;
}

if(empty($isidata)){
	exit("Warning : Data tidak ditemukan.");
}
$no=0;
foreach($isidata as $baris){
    $no+=1;
    echo"<tr id=barisl_".$no." class=rowcontent>";
        echo"<td align=center>".$no."</td>";
        echo"<td align=center><label id=tahun_".$no.">".$baris['tahunbudget']."</td>";
        echo"<td align=center><label id=reg_".$no.">".$baris['regional']."</td>";
        echo"<td align=center><label id=kode_".$no.">".$baris['kodebarang']."</label></td>";
        echo"<td>".$baris['namabarang']."</td>";
        echo"<td align=center>".$baris['satuan']."</td>";
        echo"<td align=center><label id=sumber_".$no.">".$baris['sumberharga']."</td>";
        echo"<td align=right><label id=lalu_".$no.">".number_format($baris['hargalalu'],2)."</td>";
        echo"<td align=right><label id=var_".$no.">".number_format($baris['variant'],2)."</td>";
        echo"<td align=right><label id=harga_".$no.">".number_format($baris['hargasatuan'],2)."</td>";
        echo"<td align=center><img title='Revisi Harga Material.' onclick=\"revisiharga('".$baris['tahunbudget']."','".$baris['regional']."','".$baris['kodebarang']."','".$baris['namabarang']."','".$baris['satuan']."','".$baris['hargasatuan']."')\"; class=zImgBtn src=images/application/application_view_xp.png></td>";
     echo"</tr>";
}    