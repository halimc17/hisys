<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$tahunbudget = checkPostGet('tahunbudget','');
$regional = checkPostGet('regional','');
$sumberharga = checkPostGet('sumberharga','');
$kelompokbarang = checkPostGet('kelompokbarang','');
$what = checkPostGet('what','');
$object = "OBJECT";

if($regional==''){
	exit("Warning : Regional harus diisi.");
}


if($what=='adadata'){
    $str="select * from ".$dbname.".bgt_masterbarang 
    where tahunbudget='".$tahunbudget."' and regional = '".$regional."' 
        and kodebarang like '".$kelompokbarang."%'  limit 0,1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $adadata="1";	
    }
    if($adadata=="1"){
        echo "Sudah ada data, bila lanjut akan ditimpa.\nLanjut?/nThis kind of data already exist.\n Replace ?"; exit;
    }
}

if($what=='closing'){
    $str="select * from ".$dbname.".bgt_masterbarang 
    where tahunbudget='".$tahunbudget."' and regional = '".$regional."'  and closed = 1 limit 0,1";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        @$sudahtutup="1";	
    }
    if(@$sudahtutup=="1"){
        echo "Data has been closed"; exit;
    }
    
//    echo $str;
}
if($what=='delete'){
    $str="DELETE FROM ".$dbname.".bgt_masterbarang WHERE tahunbudget = ".$tahunbudget." and regional = '".$regional."'";
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
}

if($what=='editing'){
echo"<table width=100%><tr id=baris_0 class=rowheader>";
    echo"<td align=left>Set ".$_SESSION['lang']['varian']." : ";
    echo"
	<input type=text id=varianall style=width:50px  class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doangsamaminus(event);\"/>
	
            <button class=mybutton id=proses onclick=updateHargaall()>".$_SESSION['lang']['proses']."</button></td>";
    echo"<td align=right><button class=mybutton id=simpan onclick=updateHarga(1)>".$_SESSION['lang']['save']."</button></td>";
echo"</tr></table>";
echo"<table id=container9 class=sortable cellspacing=1 border=0 width=100%>
     <thead>
        <tr>
            <td align=center>".$_SESSION['lang']['nomor']."</td>
            <td align=center>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center>".$_SESSION['lang']['regional']."</td>
            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center width=100px>".$_SESSION['lang']['sumberHarga']."</td>
            <td align=center width=100px>".$_SESSION['lang']['hargatahunlalu']." Pembelian</td>
            <td align=center>".$_SESSION['lang']['varian']."</td>
            <td align=center width=100px>".$_SESSION['lang']['hargabudget']."</td>
       </tr>  
     </thead>
     <tbody>";

//Ambil harga dari gudang jika harga budget tahun lalu belum ada
$thnlalu=$tahunbudget-1;
$str="select distinct kodebarang,hargarata from ".$dbname.".log_5saldobulanan where hargarata>0 and periode like '".$thnlalu."%' order by hargarata";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($barq=$res->fetch()){
    if(!isset($harga[$bar->kodebarang])){
       $harga[$barq->kodebarang]=$barq->hargarata;
    }
}

$str="select regional, tahunbudget, kodebarang, hargasatuan, sumberharga, variant, hargalalu from ".$dbname.".bgt_masterbarang
      where tahunbudget = '".$tahunbudget."' and kodebarang like '".$kelompokbarang."%' and regional = '".$regional."' order by regional";
	  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$kobar='';
while($bar= $res->fetch())
{
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

// if($kobar!=''){
	// $wh=" and kodebarang in (".$kobar.")";
// }


// $str="select kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang where 1=1 ".$wh."";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while(@$bar= $res->fetch())
// {
   // $isidata[$bar->kodebarang][namabarang]=$bar->namabarang;
   // $isidata[$bar->kodebarang][satuan]=$bar->satuan;
// }


# Harga Tahun Lalu ambil dari Log_5hargaterakhir (UAT)
$sql = "SELECT t1.kodebarang, t1.tanggal, t1.hargasatuan as hargalalu FROM $dbname.log_5hargaterakhir t1 
JOIN (
    SELECT kodebarang, MAX(tanggal) AS tanggal_terakhir
    FROM $dbname.log_5hargaterakhir 
    WHERE kodebarang LIKE '".$kelompokbarang."%' AND left(tanggal,4)='".$tahunbudget."'
    GROUP BY kodebarang
) t2 ON t1.kodebarang = t2.kodebarang AND t1.tanggal = t2.tanggal_terakhir 
WHERE t1.kodebarang LIKE '".$kelompokbarang."%' AND left(t1.tanggal,4)='".$tahunbudget."'";
$res = fetchData($sql, $object);

foreach($res as $val) {
    $isidata[$val->kodebarang]['hargalalupembelian']=$val->hargalalu;
}


//tampilkan data dalam array
if(empty(@$isidata)){
    echo"<tr><td colspan=9>Empty, please click
        <button id= buttonbaru class=mybutton onclick=buatbaru(".$tahunbudget.",'".$regional."',".$kelompokbarang.")>".$_SESSION['lang']['new']."</button>.</td>
        </tr>";
}else
foreach(@$isidata as $baris)
{
    if($baris['hargalalu']==0){
        $baris['hargalalu']=$harga[$baris['kodebarang']];
     }
     
    $no+=1;
    echo"<tr id=baris_".$no." class=rowcontent>";
        echo"<td align=center>".$no."</td>";
        echo"<td align=center>".$tahunbudget."</td>";
        echo"<td align=center>".$regional."</td>";
        echo"<td align=center><label id=kode_".$no.">".$baris['kodebarang']."</label></td>";
        echo"<td>".getNamaBrg($baris['kodebarang'])."</td>";
        echo"<td align=center>".getSatBrg($baris['kodebarang'])."</td>";
        echo"<td align=center><label id=sumber_".$no.">".$baris['sumberharga']."</td>";
        echo"<td align=right><label id=rata_".$no.">".number_format($baris['hargalalu'],2)."</label></td>"; # Ini Harga Lalu Budget Tahun kemarin
        // echo"<td align=right><label id=rata_".$no.">".number_format($baris['hargalalupembelian'],2)."</label></td>";
        echo"<td align=center width=50px><input type=text id=varian_".$no." size=5 value='".$baris['variant']."' maxlength=5 class=myinputtextnumber onkeyup=\"hitungharga(".$baris['hargalalu'].",this.value,".$no.")\" onkeypress=\"return angka_doangsamaminus(event);\"></td>";
        // echo"<td align=center><input type=text id=harga_".$no." size=15 value='".$baris['hargasatuan']."' maxlength=15 class=myinputtextnumber onkeyup=\"hitungpersen(".$baris['hargalalu'].",this.value,".$no.")\" onkeypress=\"return angka_doang(event);\"></td>";
        echo"<td align=center><input type=text id=harga_".$no." size=15 value='".$baris['hargasatuan']."' maxlength=15 class=myinputtextnumber onkeyup=\"hitungpersen(".$baris['hargalalu'].",this.value,".$no.")\" onkeypress=\"return angka_doang(event);\"></td>";
    echo"</tr>";
}    

echo "     </tbody>
     <tfoot>
     </tfoot>		 
     </table>";
//    echo $str;
}