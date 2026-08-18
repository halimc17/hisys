<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$tahunbudget=$_POST['tahunbudget'];
$regional=$_POST['regional'];
$sumberharga=$_POST['sumberharga'];
$kelompokbarang=$_POST['kelompokbarang'];

//check, one-two
if($tahunbudget==''){
    echo "WARNING: silakan mengisi tahun budget."; exit;
}
if(strlen($tahunbudget)!=4){
    echo "WARNING: silakan mengisi tahun budget dengan benar."; exit;
}
if($regional==''){
    echo "WARNING: silakan mengisi region."; exit;
}
if($sumberharga==''){
    echo "WARNING: silakan memilih sumberharga."; exit;
}
if($kelompokbarang==''){
    echo "WARNING: silakan memilih kelompokbarang."; exit;
}

$sInd="select distinct induk from ".$dbname.".organisasi where kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$sumberharga."')";
$qInd=$owlPDO->query($sInd) or die(print " Gagal: ".PDOException::getMessage());
$qInd->setFetchMode(PDO::FETCH_ASSOC);
while($rInd=$qInd->fetch()){
    $nor+=1;
    if($nor==1){
        $dind="'".$rInd['induk']."'";
    }else{
        $dind.=",'".$rInd['induk']."'";
    }
}

if($dind==''){
	exit("Error : Unit anda belum terdaftar di regional assinment.");
}
$thn=$tahunbudget-1;
$str="SELECT distinct a.*,(select avg(hargasatuan) from ".$dbname.".log_po_vw b where b.kodebarang=a.kodebarang and b.hargasatuan>0 and right(nopo,3) in (".$dind.") and substr(tanggal,1,4)='".$thn."') as hargarata
    FROM ".$dbname.".log_5masterbarang a where a.kodebarang like '".$kelompokbarang."%' order by a.kodebarang";
$kobar='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar= $res->fetch()){
   if($bar->hargarata==''){
       $sGud="select distinct hargarata from ".$dbname.".log_5saldobulanan where kodebarang='".$bar->kodebarang."'
              order by lastupdate desc limit 1";
        $qGud=$owlPDO->query($sGud) or die(print " Gagal: ".PDOException::getMessage());
        $qGud->setFetchMode(PDO::FETCH_ASSOC);
       $rGud=$qGud->fetch();
       $bar->hargarata=$rGud['hargarata'];
   }
    $sCek="select distinct matauang from ".$dbname.".log_po_vw where kodebarang='".$bar->kodebarang."'
          and  hargasatuan>0 and right(nopo,3) in (".$dind.") and substr(tanggal,1,4)='".$thn."'";
    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
    $qCek->setFetchMode(PDO::FETCH_ASSOC);
    $rCek=$qCek->fetch();
   if($rCek['matauang']!='IDR'){
       $sKurs="select distinct kurs from ".$dbname.".setup_matauangrate 
               where kode='".$rCek['matauang']."' and kurs!=0 order by daritanggal desc limit 1";
       $qKurs=$owlPDO->query($sKurs) or die(print " Gagal: ".PDOException::getMessage());
       $qKurs->setFetchMode(PDO::FETCH_OBJ);
       $rKurs=$qKurs->fetch();
       $bar->hargarata=$rKurs['kurs']*$bar->hargarata;
   }
   
   $strkonv="select * from ".$dbname.".log_5stkonversi where kodebarang = '".$bar->kodebarang."'";
   $reskonv=fetchdata($strkonv);
   if(count($reskonv)>0){
	   $satkonversi=$reskonv[0]['jumlah'];
	   $bar->hargarata=$bar->hargarata*$satkonversi;
   }
   
   $isidata[$bar->kodebarang]['kodebarang']=$bar->kodebarang;
   $isidata[$bar->kodebarang]['kodeorg']=$sumberharga;
   $isidata[$bar->kodebarang]['hargarata']=$bar->hargarata;
   $kobar.="'".$bar->kodebarang."',";
}
$kobar=substr($kobar,0,-1);

//cari nama barang, yang dalam array kobar
$str="select kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang where kodebarang in (".$kobar.")";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
   $isidata[$bar->kodebarang]['namabarang']=$bar->namabarang;
   $isidata[$bar->kodebarang]['satuan']=$bar->satuan;
}

$thnlalu=$tahunbudget-1;
$str="select distinct kodebarang,hargarata from ".$dbname.".log_5saldobulanan where hargarata>0 and periode like '".$thnlalu."%' order by hargarata desc";
$resq=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$resq->setFetchMode(PDO::FETCH_OBJ);
while($barq=$resq->fetch()){
    if(!isset($harga[$barq->kodebarang])){
       $harga[$barq->kodebarang]=$barq->hargarata;
    }
}

echo"<table width=100%><tr id=baris_0 class=rowheader>";
    echo"<td align=left>Set ".$_SESSION['lang']['varian']." : ";
    echo"<input type=text id=varianall style=width:50px  class=myinputtextnumber nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doangsamaminus(event);\"/>
            <button class=mybutton id=proses onclick=updateHargaall()>".$_SESSION['lang']['proses']."</button></td>";
    echo"<td align=right><button class=mybutton id=simpan onclick=simpanHarga(1)>".$_SESSION['lang']['save']."</button></td>";
echo"</tr></table>";
echo"<table id=container9 class=sortable cellspacing=1 border=0 width=100%>
     <thead>
        <tr style=height:25px>
            <td align=center>".$_SESSION['lang']['nomor']."</td>
            <td align=center>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center>".$_SESSION['lang']['regional']."</td>
            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center width=100px>".$_SESSION['lang']['sumberHarga']."</td>
            <td align=center width=100px>".$_SESSION['lang']['hargatahunlalu']."</td>
            <td align=center>".$_SESSION['lang']['varian']."</td>
            <td align=center width=100px>".$_SESSION['lang']['hargabudget']."</td>
       </tr>  
     </thead>
     <tbody>";

//tampilkan data dalam array
if(empty($isidata)){
    
}else
foreach($isidata as $baris)
{
   if($baris['hargarata']==0){
        $baris['hargarata']=$harga[$baris['kodebarang']];
    }
    
    $no+=1;
    echo"<tr id=baris_".$no." class=rowcontent>";
        echo"<td align=center>".$no."</td>";
        echo"<td align=center>".$tahunbudget."</td>";
        echo"<td align=center>".$regional."</td>";
        echo"<td align=center><label id=kode_".$no.">".$baris['kodebarang']."</label></td>";
        echo"<td>".$baris['namabarang']."</td>";
        echo"<td align=center>".$baris['satuan']."</td>";
        echo"<td align=center>".$sumberharga."</td>";
        echo"<td align=right><label id=rata_".$no.">".number_format($baris['hargarata'],2)."</label></td>";
        echo"<td align=center width=50px><input type=text id=varian_".$no." size=5 value='0.00' maxlength=5 class=myinputtextnumber onkeyup=\"hitungharga(".$baris['hargarata'].",this.value,".$no.")\" onkeypress=\"return angka_doangsamaminus(event);\"></td>";
        $hargarata=$baris['hargarata']+0; 
		$hargarata=round($hargarata*100)/100;
        echo"<td align=center><input class=myinputtextnumber  id=harga_".$no." size=15 value='".$hargarata."' maxlength=15  onkeyup=\"hitungpersen(".$baris['hargarata'].",this.value,".$no.")\" onkeypress=\"return angka_doang(event);\"></td>";
    echo"</tr>";
}    

echo "     </tbody>
     <tfoot>
     </tfoot>		 
     </table>";
?>