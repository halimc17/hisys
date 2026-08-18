<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include('lib/zLib.php');
$gudang=$_POST['gudang'];

#ambil daftar gudang:
if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $str="select a.kodeorganisasi,a.namaorganisasi,b.periode,b.tanggalmulai,b.tanggalsampai
    from ".$dbname.".organisasi a left join ".$dbname.".setup_periodeakuntansi b on a.kodeorganisasi=b.kodeorg
    where a.kodeorganisasi ='".$gudang."' and a.tipe like 'GUDANG%' and b.tutupbuku=0";   
}
else
{

    $unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
    $gudang_detailAkses=" (".$unitDetailAkses.") ";
    
    // create array 
    // Hapus tanda kutip tunggal
    $stringData = str_replace("'", "", $unitDetailAkses);
    
    // Ubah menjadi array
    $arrayData = explode(',', $stringData);
    // GUDANGX
    // Array untuk menampung klausa-klausa LIKE
    $conditions_kodeorganisasi = [];
    
    // Loop melalui setiap nilai dan buat klausa LIKE
    foreach ($arrayData as $value) {
        $conditions_kodeorganisasi[] = "a.kodeorganisasi LIKE '{$value}%'";
    }
    
    // Gabungkan semua klausa LIKE dengan 'OR'
    $whereClause_gudangx =  "AND (\n    " . implode(" OR\n    ", $conditions_kodeorganisasi) . "\n)";
    // AKHIR GUDANGX

    if(count($unitDetailAkses) > 0){
        $str="select a.kodeorganisasi,a.namaorganisasi,b.periode,b.tanggalmulai,b.tanggalsampai
        from ".$dbname.".organisasi a left join ".$dbname.".setup_periodeakuntansi b on a.kodeorganisasi=b.kodeorg
        where 1+1 ".$whereClause_gudangx." and a.tipe like 'GUDANG%' and b.tutupbuku=0 order by a.kodeorganisasi";    
    }else{
        $str="select a.kodeorganisasi,a.namaorganisasi,b.periode,b.tanggalmulai,b.tanggalsampai
        from ".$dbname.".organisasi a left join ".$dbname.".setup_periodeakuntansi b on a.kodeorganisasi=b.kodeorg
        where a.kodeorganisasi like '".$gudang."%' and a.tipe like 'GUDANG%' and b.tutupbuku=0";    
    }
}
if(($_SESSION['empl']['lokasitugas']==('MRKE'))or($_SESSION['empl']['lokasitugas']==('SKSE'))){
    $str="select a.kodeorganisasi,a.namaorganisasi,b.periode,b.tanggalmulai,b.tanggalsampai
    from ".$dbname.".organisasi a left join ".$dbname.".setup_periodeakuntansi b on a.kodeorganisasi=b.kodeorg
    where a.kodeorganisasi like '".$gudang."%' and a.tipe like 'GUDANGTEMP%' and b.tutupbuku=0";    
}


$stream="Please choose storage location(warehouse):
              <table class=sortable cellspacing=1 border=0>
              <thead>
               <tr class=rowheader>
               <td>".$_SESSION['lang']['gudang']."</td>
               <td>".$_SESSION['lang']['namaorganisasi']."</td>
               <td>".$_SESSION['lang']['periode']."</td>
               <td>".$_SESSION['lang']['tanggalmulai']."</td>
               <td>".$_SESSION['lang']['tanggalsampai']."</td>
               <td>".$_SESSION['lang']['pilih']."</td>     
               </tr>    
               </thead>";
$stream2="Please recalculate(material):
              <table class=sortable cellspacing=1 border=0>
              <thead>
               <tr class=rowheader>
               <td>".$_SESSION['lang']['gudang']."</td>
               <td>".$_SESSION['lang']['kodebarang']."</td>
               <td>".$_SESSION['lang']['periode']."</td>
               <td>".$_SESSION['lang']['saldoawal']."</td>
               <td>".$_SESSION['lang']['masuk']."</td>
               <td>".$_SESSION['lang']['keluar']."</td>
               <td>".$_SESSION['lang']['saldoakhir']."</td>
               <td>".$_SESSION['lang']['action']."</td>     
               </tr>    
               </thead>";
$no=0;
$no2=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$maxRow=owlBaris($res);
$adaerror=0;
while($bar=$res->fetch())
{
        $str2="SELECT *, (saldoawalqty+qtymasuk-qtykeluar) as pembanding
        FROM ".$dbname.".log_5saldobulanan
        WHERE kodegudang = '".$bar->kodeorganisasi."' and periode = '".$bar->periode."'
        AND ( saldoawalqty + qtymasuk - qtykeluar - saldoakhirqty) != 0";
		$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
		$res2->setFetchMode(PDO::FETCH_OBJ);
        while($bar2=$res2->fetch())
        {
            if((number_format($bar2->pembanding,2))!=(number_format($bar2->saldoakhirqty,2))){
            $adaerror=1;
            $no2+=1;  
          $stream2.="<tr class=rowcontent  id=guaikutaja_".$no2.">
               <td id=kodegud".$no2.">".$bar2->kodegudang."</td>
               <td id=kodebar".$no2.">".$bar2->kodebarang."</td>
               <td id=kodeper".$no2.">".$bar2->periode."</td>
               <td id=sawal_".$no2.">".$bar2->saldoawalqty."</td>
               <td id=qtymsk_".$no2.">".$bar2->qtymasuk."</td>
               <td id=qtyklr_".$no2.">".$bar2->qtykeluar."</td>
               <td id=salak_".$no2.">".$bar2->saldoakhirqty."</td>
               <td><button class=mybutton onclick=reklasDt('".$bar2->kodebarang."','".$bar2->kodegudang."','".$bar2->periode."','".$no2."') >".$_SESSION['lang']['rekalkulasi']."</button></td>    
               </tr>";  
            }
        }    
  $no+=1;  
  $stream.="<tr class=rowcontent  id=row".$no.">
               <td id=kodeorg".$no.">".$bar->kodeorganisasi."</td>
               <td>".$bar->namaorganisasi."</td>
               <td id=periode".$no.">".$bar->periode."</td>
               <td id=tanggalmulai".$no.">".$bar->tanggalmulai."</td>
               <td id=tanggalsampai".$no.">".$bar->tanggalsampai."</td>
               <td><input type=checkbox  id=pilihan".$no." checked></td>    
               </tr>";  
}
$stream.="</tbody><tfoot></tfoot></table>
<button class=mybutton onclick=saveSaldoFisik(".$maxRow.",this)>Proses</button>";
$stream2.="</tbody><tfoot></tfoot></table>
Please refresh after all material has been recalculated correctly (green).<br/><br/>
<button onclick=setSloc('simpan') class=mybutton id=btnsloc>Refresh</button>
";
if($adaerror==1){
    echo $stream;    
}else{
    echo $stream;    
}

?>