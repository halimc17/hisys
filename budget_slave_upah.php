<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$tahunbudget=  checkPostGet('tahunbudget','');
$kodeorg    =  checkPostGet('kodeorg','');
$what       =  checkPostGet('what','');

//check, one-two
if($tahunbudget==''){
    echo "WARNING : silakan mengisi tahun budget."; exit;
}
if(strlen($tahunbudget)!=4){
    echo "WARNING : silakan mengisi tahun budget dengan benar."; exit;
}
if($kodeorg==''){
    echo "WARNING : silakan mengisi kode organisasi."; exit;
}

//bila sudah ada data, masukkan ke dalam array
$str2="select golongan, jumlah from ".$dbname.".bgt_upah
    where tahunbudget = '".$tahunbudget."' and kodeorg = '".$kodeorg."' order by golongan";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_OBJ);
while($bar2= $res2->fetch()){
   $isidata[$bar2->golongan]['kodegolongan']=$bar2->golongan;
   $isidata[$bar2->golongan]['upah']=$bar2->jumlah;
}

//ambil data dari golongan, masukkan ke dalam array
if($_SESSION['empl']['tipelokasitugas']=='PABRIK'){
	$where = " and kodebudget like 'EXPL%'";
}else{
	$where = " and kodebudget like 'SDM%'";
}

$str="select * from ".$dbname.".bgt_kode where 1=1 ".$where." order by nama";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar= $res->fetch()){
   $isidata[$bar->kodebudget]['kodegolongan']=$bar->kodebudget;
   $isidata[$bar->kodebudget]['namagolongan']=$bar->nama;
   if(@$isidata[$bar->kodebudget]['upah']!=0){}else $isidata[$bar->kodebudget]['upah']=0;
}

$str = "select sum(a.jumlah) as jumlah, count(a.karyawanid) as total, tipekaryawan from ".$dbname.".sdm_5gajipokok a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.tahun='".($tahunbudget-1)."' and b.lokasitugas='".$kodeorg."' and a.idkomponen='1' group by tipekaryawan"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	#1 = PB, 3 = KHT, 4 = KHL
	if(substr($kodeorg,-1)!='M'){
		if($bar['tipekaryawan']=='1'){
			if(@$isidata['SDM-KBL']['upah']==0){
				$isidata['SDM-KBL']['upah']=$bar['jumlah']/$bar['total']/25;
			}
		}
		if($bar['tipekaryawan']=='3'){
			if(@$isidata['SDM-KHT']['upah']==0){
				$isidata['SDM-KHT']['upah']=$bar['jumlah']/$bar['total']/25;
			}
		}
		if($bar['tipekaryawan']=='4'){
			if(@$isidata['SDM-PHL']['upah']==0){
				$isidata['SDM-PHL']['upah']=$bar['jumlah']/$bar['total']/25;
			}
		}
	}
}



if(($what=='closed')or(substr($_SESSION['empl']['lokasitugas'],0,4)!=$kodeorg)){
    
}else

//echo "<button class=mybutton id=simpan onclick=simpanHarga(1)>".$_SESSION['lang']['save']."</button>";
echo'';
echo"<table cellspacing=1 border=0 class=sortable>
    <thead>
    <tr class=\"rowheader\">
    <td align=center>".substr($_SESSION['lang']['nomor'],0,2)."</td>
    <td align=center>".$_SESSION['lang']['kodeorg']."</td>
    <td align=center>".$_SESSION['lang']['kodegolongan']."</td>
    <td align=center>".$_SESSION['lang']['levelname']."</td>
    <td align=center>".$_SESSION['lang']['upahkerja']." / ".$_SESSION['lang']['hari']."</td>
    <td align=center>".$_SESSION['lang']['save']."</td>
    </tr></thead><tbody>";

//tampilkan data dalam array
if(isset($isidata)){
	foreach($isidata as $baris)
	{
		$no+=1;
		echo"<tr id=baris_".$no." class=rowcontent>";
			echo"<td align=center>".$no."</td>";
			echo"<td align=center><label id=kodeorg_".$no.">".$kodeorg."</td>";
			echo"<td><label id=kodegolongan_".$no.">".$baris['kodegolongan']."</td>";
			echo"<td>".$baris['namagolongan']."</td>";
			if(($what=='closed')or(substr($_SESSION['empl']['lokasitugas'],0,4)!=$kodeorg)){
				echo"<td align=right>".number_format($baris['upah'])."</td>";
				echo"<td></td>";
			}else{
				echo"<td><input type=text id=upah_".$no." size=10 style='text-align:right' value='".$baris['upah']."' maxlength=10 class=myinputtext onkeypress=\"return angka_doang(event);\"></td>";
				echo"<td><button class=mybutton onclick=simpanHargasatusatu(".$no.")>".$_SESSION['lang']['save']."</button></td>";

				
			}
		echo"</tr>";
	}
	if(($what=='closed')or(substr($_SESSION['empl']['lokasitugas'],0,4)!=$kodeorg)){
	}else{
		echo "<tr><td colspan=6 align=right><button class=mybutton id=simpan onclick=simpanHarga(1)>".$_SESSION['lang']['saveall']."</button></td></tr>";
	}
}
echo "</tbody></table>";
?>