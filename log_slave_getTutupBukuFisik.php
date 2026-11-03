<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include('lib/zLib.php');
$gudang=preg_replace('/[^A-Za-z0-9]/','',$_POST['gudang']);

#ambil daftar gudang:
$kondisiAkses="a.kodeorganisasi like '".$gudang."%'"; // batasan gudang yang sama untuk daftar yang belum dan yang sudah ditutup
if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $kondisiAkses="a.kodeorganisasi ='".$gudang."'";
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

    if(!empty($unitDetailAkses)){
        $kondisiAkses="1+1 ".$whereClause_gudangx;
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


$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

// kelompokkan gudang per periode: tiap periode ditutup dengan tombol sendiri supaya periode baru tidak ikut tertutup
$grup=array();
while($bar=$res->fetch())
{
    $grup[$bar->periode][]=$bar;
}
ksort($grup);
$periodeTerlama=(count($grup)>0 ? key($grup) : '');

// gudang yang sudah ditutup pada periode yang sama (untuk informasi sudah/belum)
$sudahTutup=array();
foreach(array_keys($grup) as $p){
    $sudahTutup[$p]=fetchData("select a.kodeorganisasi,a.namaorganisasi from ".$dbname.".organisasi a join ".$dbname.".setup_periodeakuntansi b on a.kodeorganisasi=b.kodeorg
        where ".$kondisiAkses." and a.tipe like 'GUDANG%' and b.tutupbuku=1 and b.periode='".$p."' order by a.kodeorganisasi");
}

$stream="<style>
        .ptb-info{margin:6px 0;padding:8px 10px;background:#eef3fb;border:1px solid #9bb3d6;font-size:12px;}
        .ptb-grup{margin:12px 0;border:1px solid #9bb3d6;background:#f3f7fd;}
        .ptb-judul{padding:7px 10px;background:#3a5470;color:white;font-weight:bold;font-size:13px;}
        .ptb-status{padding:7px 10px;font-size:12px;border-bottom:1px solid #9bb3d6;}
        .ptb-grup table{width:100%;}
        .ptb-aksi{padding:8px 10px;border-top:1px solid #9bb3d6;background:#e6eefb;}
      </style>
      <div class=ptb-info>
        <b>Perhatian:</b> tutup buku fisik hanya bisa dilakukan kalau <b>3. Pra Tutup Buku Fisik</b> sudah clear (semua pengecekan hijau).
        Kalau belum, proses akan ditolak dengan pesan kendalanya. Cek dulu di menu <a href=\"javascript:do_load('log_3cekgudang')\">3. Pra Tutup Buku Fisik</a>.";
if(count($grup)>1){
    $stream.="<br/>Ada ".count($grup)." periode yang masih terbuka (".implode(', ',array_keys($grup))."). Tutup buku dilakukan per periode dengan tombol masing-masing; tutup periode terlama lebih dulu.";
}
$stream.="</div>";

$no=0;
if(count($grup)==0){
    $stream.="<div class=ptb-info>Tidak ada gudang dengan periode yang masih terbuka.</div>";
}
foreach($grup as $periode=>$daftar){
    $awalNo=$no+1;
    $akhirNo=$no+count($daftar);
    $jmlSudah=count($sudahTutup[$periode]);
    $blokStatus="<div class=ptb-status><b>Sudah ditutup: ".$jmlSudah." gudang</b> &nbsp;|&nbsp; <b>Belum ditutup: ".count($daftar)." gudang</b>";
    if($jmlSudah>0){
        $kodeSudah=array();
        foreach($sudahTutup[$periode] as $gs){
            $kodeSudah[]=$gs['kodeorganisasi'];
        }
        $blokStatus.="<br/>Sudah ditutup: ".implode(', ',$kodeSudah);
    }
    $blokStatus.="</div>";
    $stream.="<div class=ptb-grup>
        <div class=ptb-judul>Periode ".$periode." <span style='font-weight:normal;font-size:12px;'>(".tanggalnormal($daftar[0]->tanggalmulai)." s/d ".tanggalnormal($daftar[0]->tanggalsampai).")</span></div>
        ".$blokStatus."
        <table class=sortable cellspacing=1 border=0>
        <thead>
         <tr class=rowheader>
          <th style='width:30px'>No.</th>
          <th>".$_SESSION['lang']['gudang']."</th>
          <th>".$_SESSION['lang']['namaorganisasi']."</th>
          <th>".$_SESSION['lang']['periode']."</th>
          <th>".$_SESSION['lang']['tanggalmulai']."</th>
          <th>".$_SESSION['lang']['tanggalsampai']."</th>
          <th style='width:110px'>Status</th>
          <th style='width:70px'>".$_SESSION['lang']['pilih']."<br/><input type=checkbox checked onclick=pilihSemuaGudang(".$awalNo.",".$akhirNo.",this.checked) title='Pilih semua'></th>
         </tr>
        </thead>
        <tbody>";
    $nomorGrup=0;
    foreach($daftar as $bar){
        $no+=1;
        $nomorGrup+=1;
        $stream.="<tr class=rowcontent id=row".$no.">
          <td align=center>".$nomorGrup."</td>
          <td id=kodeorg".$no.">".$bar->kodeorganisasi."</td>
          <td>".$bar->namaorganisasi."</td>
          <td id=periode".$no." align=center>".$bar->periode."</td>
          <td id=tanggalmulai".$no." align=center>".$bar->tanggalmulai."</td>
          <td id=tanggalsampai".$no." align=center>".$bar->tanggalsampai."</td>
          <td align=center id=status".$no.">Belum ditutup</td>
          <td align=center><input type=checkbox id=pilihan".$no." checked></td>
          </tr>";
    }
    $stream.="</tbody></table>
        <div class=ptb-aksi><button class=mybutton onclick=saveSaldoFisik(".$awalNo.",".$akhirNo.",this,'".$periodeTerlama."')>Proses Tutup Buku Periode ".$periode."</button></div>
        </div>";
}
echo $stream;

?>