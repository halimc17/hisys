<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses=($_GET['proses']==''?$_POST['proses']:$_GET['proses']);
$param=$_POST;

$sPeriode="select distinct * from ".$dbname.".setup_periodeakuntansi where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['unitId']."' and tipe like 'GUDANG%') and periode='".$param['periodeId']."' and tutupbuku=0 order by periode asc limit 1";
$rPeriode=fetchData($sPeriode);
$awal=$rPeriode[0]['tanggalmulai'];
$akhir=$rPeriode[0]['tanggalsampai'];

// $unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
$gudang_detailAkses=" (".$unitDetailAkses.") ";

$OptOrganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$OptBarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');


// create array 
// Hapus tanda kutip tunggal
$stringData = str_replace("'", "", $unitDetailAkses);

// Ubah menjadi array
$arrayData = explode(',', $stringData);

// GUDANGX
// Array untuk menampung klausa-klausa LIKE
$conditions_gudangx = [];

// Loop melalui setiap nilai dan buat klausa LIKE
foreach ($arrayData as $value) {
    $conditions_gudangx[] = "gudangx LIKE '{$value}%'";
}

// Gabungkan semua klausa LIKE dengan 'OR'
$whereClause_gudangx =  "AND (\n    " . implode(" OR\n    ", $conditions_gudangx) . "\n)";
// AKHIR GUDANGX

// GUDANGX
// Array untuk menampung klausa-klausa LIKE
$conditions_kodegudang = [];

// Loop melalui setiap nilai dan buat klausa LIKE
foreach ($arrayData as $value) {
    $conditions_kodegudang[] = "kodegudang LIKE '{$value}%'";
}

// Gabungkan semua klausa LIKE dengan 'OR'
$whereClause_kodegudang =  "AND (\n    " . implode(" OR\n    ", $conditions_kodegudang) . "\n)";
// AKHIR GUDANGX


// Tampilan hasil Pra Tutup Buku Fisik: ringkasan pengecekan di atas, rincian temuan di bawahnya
function renderPraTutupBuku($temuan, $akunBelumBalance, $param)
{
    $h = function ($s) {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    };
    $namaUnit = getNamaOrg($param['unitId']);

    // [kunci, judul pengecekan, jumlah temuan (null = belum dicek), tindakan]
    $cek = array();
    $cek[] = array('tutup', 'Gudang sudah tutup buku pada periode ini', ($temuan['tutup'] != '' ? 1 : 0), 'Hubungi IT');
    if ($temuan['integrity']) {
        $cek[] = array('integrity', 'Integrity Check BKM', 1, 'Jalankan Pengadaan - Proses - Integrity Check BKM, lalu cek ulang');
    }
    $cek[] = array('bkm', 'BKM pakai material belum diposting', count($temuan['bkm']), 'Posting BKM yang memakai material', 'baris');
    $cek[] = array('belumposting', 'Transaksi gudang belum diposting', count(array_unique(array_column($temuan['belumposting'], 'notransaksi'))), 'Posting di menu Pengadaan - Proses - Posting, urut dari tanggal paling awal', 'transaksi');
    $cek[] = array('mutasidalam', 'Mutasi dalam unit belum diterima', count($temuan['mutasidalam']), 'Selesaikan penerimaan mutasi pada periode yang sama', 'transaksi');
    $cek[] = array('mutasiantar', 'Mutasi antar unit belum diterima', count($temuan['mutasiantar']), 'Selesaikan penerimaan mutasi pada periode yang sama', 'transaksi');
    $cek[] = array('mutasibelumterima', 'Mutasi belum dibuatkan penerimaan / pengiriman belum diposting', count($temuan['mutasibelumterima']), 'Buat penerimaan mutasi atau posting pengirimannya', 'baris');
    $cek[] = array('saldodivisi', 'Saldo barang di gudang divisi belum nol', count($temuan['saldodivisi']), 'Kembalikan stok ke gudang central sampai saldo divisi nol', 'baris');
    $cek[] = array('akun', 'Nilai material vs jurnal (akun belum balance, ambang Rp 300)', ($temuan['integrity'] ? null : count($akunBelumBalance)), 'Klik ikon kunci pada baris akun untuk melihat rinciannya', 'akun');

    $jumlahMasalah = 0;
    foreach ($cek as $c) {
        if ($c[2] !== null && $c[2] > 0) {
            $jumlahMasalah++;
        }
    }

    $out = "<div style='font-size:13px;'>";
    // ringkasan
    if ($jumlahMasalah == 0) {
        $out .= "<div style='padding:10px 14px;background:green;color:white;font-weight:bold;font-size:15px;'>&#10003; SEMUA PENGECEKAN LOLOS - Silakan lanjutkan Tutup Buku.</div>";
    } else {
        $out .= "<div style='padding:10px 14px;background:#d9362a;color:white;font-weight:bold;font-size:15px;'>&#10007; BELUM BISA TUTUP BUKU - " . $jumlahMasalah . " pengecekan belum lolos</div>";
    }
    $out .= "<div style='margin:6px 0;font-size:12px;'><b>Unit</b> : " . $h($namaUnit) . " (" . $h($param['unitId']) . ") &nbsp;|&nbsp; <b>Periode</b> : " . $h($param['periodeId']) . "</div>";
    $out .= "<table cellpadding=5 cellspacing=1 class=sortable style='width:100%;margin-bottom:12px;'>
        <thead><tr class=rowheader><th style='width:30px'>No.</th><th>Pengecekan</th><th style='width:130px'>Hasil</th><th>Tindakan</th></tr></thead><tbody>";
    $no = 0;
    foreach ($cek as $c) {
        $no++;
        if ($c[2] === null) {
            $hasil = "<td align='center' style='background:#999;color:white;font-weight:bold;'>Belum dicek</td>";
            $tindak = "Selesaikan pengecekan di atasnya dulu";
        } elseif ($c[2] > 0) {
            $hasil = "<td align='center' style='background:#d9362a;color:white;font-weight:bold;'>&#10007; " . $c[2] . " " . (isset($c[4]) ? $c[4] : 'temuan') . "</td>";
            $tindak = $h($c[3]);
        } else {
            $hasil = "<td align='center' style='background:green;color:white;font-weight:bold;'>&#10003; Lolos</td>";
            $tindak = "-";
        }
        $out .= "<tr class=rowcontent><td align='center'>" . $no . "</td><td>" . $h($c[1]) . "</td>" . $hasil . "<td>" . $tindak . "</td></tr>";
    }
    $out .= "</tbody></table>";

    // rincian temuan
    $judul = function ($teks, $jumlah) use ($h) {
        return "<div style='margin:14px 0 4px 0;padding:5px 10px;background:#3a5470;color:white;font-weight:bold;'>" . $h($teks) . " (" . $jumlah . ")</div>";
    };
    $tabel = function ($kolom, $baris) use ($h) {
        $t = "<table cellpadding=4 cellspacing=1 class=sortable style='width:100%'><thead><tr class=rowheader><th style='width:30px'>No.</th>";
        foreach ($kolom as $k) {
            $t .= "<th>" . $h($k) . "</th>";
        }
        $t .= "</tr></thead><tbody>";
        $n = 0;
        foreach ($baris as $b) {
            $n++;
            $t .= "<tr class=rowcontent><td align='center'>" . $n . "</td>";
            foreach ($b as $sel) {
                $t .= $sel;
            }
            $t .= "</tr>";
        }
        return $t . "</tbody></table>";
    };
    $td = function ($teks, $rata = 'left') use ($h) {
        return "<td align='" . $rata . "'>" . $h($teks) . "</td>";
    };

    if ($temuan['tutup'] != '') {
        $out .= $judul('Gudang sudah tutup buku', 1) . "<div style='padding:8px 10px;background:#fdecea;border:1px solid #d9362a;'>" . $h($temuan['tutup']) . "</div>";
    }
    if ($temuan['integrity']) {
        $out .= $judul('Integrity Check BKM belum dijalankan', 1) . "<div style='padding:8px 10px;background:#fff4e0;border:1px solid orange;'>Ada pemakaian material BKM yang belum tercatat di gudang. Silakan jalankan Proses pada menu Pengadaan &gt; Proses &gt; Integrity Check BKM, lalu cek ulang.</div>";
    }
    if (count($temuan['bkm']) > 0) {
        $baris = array();
        foreach ($temuan['bkm'] as $r) {
            $baris[] = array($td($r['notransaksi']), $td($r['kodegudang'], 'center'), $td($r['kodebarang'] . ' - ' . $r['namabarang']), $td($r['jumlah'], 'right'));
        }
        $out .= $judul('BKM pakai material belum diposting', count($baris)) . $tabel(array('No. Transaksi', 'Gudang', 'Barang', 'Jumlah'), $baris);
    }
    if (count($temuan['belumposting']) > 0) {
        $baris = array();
        foreach ($temuan['belumposting'] as $r) {
            $baris[] = array($td($r['notransaksi']), $td(tanggalnormal($r['tanggal']), 'center'), $td($r['kodegudang'], 'center'), $td($r['kodebarang'] . ' - ' . $r['namabarang']), $td($r['jumlah'], 'right'));
        }
        $out .= $judul('Transaksi gudang belum diposting - ' . count(array_unique(array_column($temuan['belumposting'], 'notransaksi'))) . ' transaksi, rincian per barang', count($baris)) .$tabel(array('No. Transaksi', 'Tanggal', 'Gudang', 'Barang', 'Jumlah'), $baris);
    }
    foreach (array('mutasidalam' => 'Mutasi dalam unit belum diterima', 'mutasiantar' => 'Mutasi antar unit belum diterima') as $kunci => $teks) {
        if (count($temuan[$kunci]) > 0) {
            $baris = array();
            foreach ($temuan[$kunci] as $r) {
                $baris[] = array($td($r));
            }
            $out .= $judul($teks, count($baris)) . $tabel(array('No. Transaksi'), $baris);
        }
    }
    if (count($temuan['mutasibelumterima']) > 0) {
        $baris = array();
        foreach ($temuan['mutasibelumterima'] as $r) {
            $baris[] = array($td($r['notransaksi']), $td($r['tanggal'], 'center'), $td($r['kodebarang'] . ' - ' . $r['namabarang']), $td($r['keterangan']));
        }
        $out .= $judul('Mutasi belum dibuatkan penerimaan / pengiriman belum diposting', count($baris)) . $tabel(array('No. Transaksi', 'Tanggal', 'Barang', 'Keterangan'), $baris);
    }
    if (count($temuan['saldodivisi']) > 0) {
        $baris = array();
        foreach ($temuan['saldodivisi'] as $r) {
            $baris[] = array($td($r['kodegudang'] . ' - ' . $r['namagudang']), $td($r['kodebarang'] . ' - ' . $r['namabarang']), $td($r['jumlah'], 'right'));
        }
        $out .= $judul('Saldo barang di gudang divisi belum nol', count($baris)) . $tabel(array('Gudang', 'Barang', 'Saldo (qty)'), $baris);
    }
    if (count($akunBelumBalance) > 0) {
        $baris = array();
        foreach ($akunBelumBalance as $r) {
            $aksi = "<td align='center'><img src=images/tool.png class=resicon height=26 title='Rincian " . $h($r['nama']) . "' onclick=updateData('" . $h($r['noakun']) . "','" . $h($param['periodeId']) . "','" . $h($param['unitId']) . "',event)></td>";
            $baris[] = array($td($r['noakun'], 'center'), $td($r['nama']), $td(number_format($r['material']), 'right'), $td(number_format($r['jurnal']), 'right'),
                "<td align='right' style='font-weight:bold;color:#d9362a;'>" . number_format($r['selisih']) . "</td>", $aksi);
        }
        $out .= $judul('Akun persediaan belum balance (material vs jurnal)', count($baris)) . $tabel(array('No. Akun', 'Nama Akun', 'Nilai Material', 'Nilai Jurnal', 'Selisih', 'Rincian'), $baris);
    }
    $out .= "</div>";
    echo $out;
}
switch ($proses) {
  case 'cekAwal':
  $textwarn="";
  $temuan=array('tutup'=>'','integrity'=>false,'bkm'=>array(),'belumposting'=>array(),'mutasidalam'=>array(),'mutasiantar'=>array(),'mutasibelumterima'=>array(),'saldodivisi'=>array());
  $x = str_replace("-", "", $param['periodeId']);
  $x = str_replace("/", "", $x);
  $x = mktime(0, 0, 0, intval(substr($x, 4, 2)) + 1, 15, substr($x, 0, 4));
  $prefper = date('Y-m', $x);;
  #periksa apakah sudah pernah tutup buku pada periode tersebut:

  if(!empty($unitDetailAkses)){
        $str = "select distinct(periode)  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $prefper. "' and left(kodegudang,4) IN ".$gudang_detailAkses." ";
    }else{
        $str = "select distinct(periode)  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $prefper. "' and left(kodegudang,4)='" .$param['unitId'] . "'";
  }

  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $numrows=owlBaris($res);
  if ($numrows>0){
      $temuan['tutup']="Ada gudang sudah tutup buku pada periode tersebut (" . $param['periodeId'] . "), mohon hubungi IT";
  }
  $nobkm = 0;
  ##cek disini coi untuk transaksi BKM
  if(!empty($unitDetailAkses)){
      $iBkm = "select * from " . $dbname . ".kebun_pakai_material_vw where jurnal=0 and tanggal like '" . $param['periodeId']. "%'  and left(kodegudang,4) IN ".$gudang_detailAkses."  ";
}else{
      $iBkm = "select * from " . $dbname . ".kebun_pakai_material_vw where jurnal=0 and tanggal like '" . $param['periodeId']. "%' and"
              . " left(kodegudang,4)='" . $param['unitId']. "' ";
  }
  $nBkm=$owlPDO->query($iBkm) or die(print " Gagal: ".PDOException::getMessage());
  $nBkm->setFetchMode(PDO::FETCH_ASSOC);
//   $adabkm="\n";
  while ($dBkm = $nBkm->fetch()) {
      $nobkm++;
      $temuan['bkm'][]=array('notransaksi'=>$dBkm['notransaksi'],'kodegudang'=>$dBkm['kodegudang'],'kodebarang'=>$dBkm['kodebarang'],'namabarang'=>$OptBarang[$dBkm['kodebarang']],'jumlah'=>hidezerodecimal($dBkm['kwantitas'],10));
  }
  // transaksi belum diposting/disetujui dicek sekali saja di bawah (query-nya sama)

  #cek transaksi blm terposting
  if(!empty($unitDetailAkses)){
      $sTrns="select * from ".$dbname.".log_transaksi_vw where left(kodegudang,4) IN ".$gudang_detailAkses." and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "'
              and (post=0 or statussaldo=0) ";
  }else{
      $sTrns="select * from ".$dbname.".log_transaksi_vw where left(kodegudang,4)='".$param['unitId']."' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "'
              and (post=0 or statussaldo=0) ";
  }
  $rTrans=fetchData($sTrns);

  foreach ($rTrans as $val) {
      $temuan['belumposting'][]=array('notransaksi'=>$val['notransaksi'],'tanggal'=>$val['tanggal'],'kodegudang'=>$val['kodegudang'],'kodebarang'=>$val['kodebarang'],'namabarang'=>$OptBarang[$val['kodebarang']],'jumlah'=>$val['jumlah']);
  }
    #mutasi antar unit
    if(!empty($unitDetailAkses)){
        $sCekMutasi="select notransaksi,sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where left(gudangx,4) IN ".$gudang_detailAkses." and tanggal like '".$param['periodeId']."%' and tipetransaksi=7 and left(kodegudang,4)=left(gudangx,4) and statussaldo=1 group by notransaksi";
    }else{
        $sCekMutasi="select notransaksi,sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where left(gudangx,4)='".$param['unitId']."' and tanggal like '".$param['periodeId']."%' and tipetransaksi=7 and left(kodegudang,4)=left(gudangx,4) and statussaldo=1 group by notransaksi";
    }
   
    $rCekMutasi=fetchData($sCekMutasi);

  #terima mutasi
  if(!empty($unitDetailAkses)){
      $sCekTrmMutasi="select notransaksireferensi,sum(jumlah) as jumlah  from ".$dbname.".log_transaksi_vw where left(kodegudang,4) IN ".$gudang_detailAkses." and tanggal like '".$param['periodeId']."%' and tipetransaksi=3 and left(kodegudang,4)=left(gudangx,4) and statussaldo=1 group by notransaksireferensi";
    }else{
      $sCekTrmMutasi="select notransaksireferensi,sum(jumlah) as jumlah  from ".$dbname.".log_transaksi_vw where left(kodegudang,4)='".$param['unitId']."' and tanggal like '".$param['periodeId']."%' and tipetransaksi=3 and left(kodegudang,4)=left(gudangx,4) and statussaldo=1 group by notransaksireferensi";
  }
  
  $rCekTrmMutasi=fetchData($sCekTrmMutasi);
  if(count($rCekMutasi)!=0||count($rCekTrmMutasi)!=0){#bandingkan antara mutasi dengan terima dalam satu unit, harus selesai dalam periode yang sama
    foreach ($rCekMutasi as $key => $val) {
        $lstGudangKrm[$val['notransaksi']]=$val['jumlah'];
    }
    $selisih=0;
    $arrListTrans=array();
    foreach($rCekTrmMutasi as $key => $val) {
       $lstGudangTrm[$val['notransaksireferensi']]=$val['jumlah'];
    }
    foreach($rCekMutasi as $key => $val) {
       // $lstGudangTrima[$val['notransaksireferensi']]=$val['jumlah'];
       $selisih=$val['jumlah']-$lstGudangTrm[$val['notransaksi']];
       if($selisih!=0){
           $arrListTrans[$val['notransaksi']]=$val['notransaksi'];
       }
    }
     
    if(count($arrListTrans)!=0){
        foreach ($arrListTrans as $key) {
            $temuan['mutasidalam'][]=$key;
        }
    }
  }
  
  
  $lstGudangTrm=$lstGudangKrm=array();
  #mutasi antar unit
  if(!empty($unitDetailAkses)){
      $sCekMutasi="select notransaksi,sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where left(gudangx,4) IN ".$gudang_detailAkses." and tanggal like '".$param['periodeId']."%' and tipetransaksi=7 and left(kodegudang,4)<>left(gudangx,4) and statussaldo=1 group by notransaksi";
    }else{
      $sCekMutasi="select notransaksi,sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where left(gudangx,4)='".$param['unitId']."' and tanggal like '".$param['periodeId']."%' and tipetransaksi=7 and left(kodegudang,4)<>left(gudangx,4) and statussaldo=1 group by notransaksi";
  }
 $rCekMutasi=fetchData($sCekMutasi);
  
  #terima mutasi
  if(!empty($unitDetailAkses)){
      $sCekTrmMutasi="select notransaksireferensi,sum(jumlah) as jumlah  from ".$dbname.".log_transaksi_vw where left(kodegudang,4) IN ".$gudang_detailAkses." and tanggal like '".$param['periodeId']."%' and tipetransaksi=3 and left(kodegudang,4)<>left(gudangx,4) and statussaldo=1 group by notransaksireferensi";
    }else{
      $sCekTrmMutasi="select notransaksireferensi,sum(jumlah) as jumlah  from ".$dbname.".log_transaksi_vw where left(kodegudang,4)='".$param['unitId']."' and tanggal like '".$param['periodeId']."%' and tipetransaksi=3 and left(kodegudang,4)<>left(gudangx,4) and statussaldo=1 group by notransaksireferensi";
  }
  $rCekTrmMutasi=fetchData($sCekTrmMutasi);
  if(count($rCekMutasi)!=0||count($rCekTrmMutasi)!=0){#bandingkan antara mutasi dengan terima dalam satu unit, harus selesai dalam periode yang sama
    foreach ($rCekMutasi as $key => $val) {
        $lstGudangKrm[$val['notransaksi']]=$val['jumlah'];
    }
    $selisih=0;
    $arrListTrans=array();
    foreach($rCekTrmMutasi as $key => $val) {
       $lstGudangTrm[$val['notransaksireferensi']]=$val['jumlah'];
    }
    foreach($rCekMutasi as $key => $val) {
       // $lstGudangTrima[$val['notransaksireferensi']]=$val['jumlah'];
       $selisih=$val['jumlah']-$lstGudangTrm[$val['notransaksi']];
       if($selisih!=0){
           $arrListTrans[$val['notransaksi']]=$val['notransaksi'];
       }
    }
     
    if(count($arrListTrans)!=0){
        foreach ($arrListTrans as $key) {
            $temuan['mutasiantar'][]=$key;
        }
    }
  }
  
  
	#==============================================================================================================================================
	#= ubah dulu kalau ada saldo <0 langsung ubah menjad 0 termasuk nilai saldoakhirqty
    if(!empty($unitDetailAkses)){
        $str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $param['periodeId']. "' and left(kodegudang,4) IN ".$gudang_detailAkses." and saldoakhirqty<0";
    }else{
        $str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $param['periodeId']. "' and left(kodegudang,4)='" .$param['unitId'] . "' and saldoakhirqty<0";
    }
	$res=fetchdata($str);
	foreach($res as $bar){
		#= update jika ada
		$strup = "update  ".$dbname.".`log_5saldobulanan` set  saldoakhirqty=0,nilaisaldoakhir=0 where kodebarang='".$bar['kodebarang']."' and periode='" . $param['periodeId']. "' and kodegudang='" . $bar['kodegudang']. "'";			
		try {
			$owlPDO->exec($strup);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	}
	
	#=cek gudang divisi harus 0
	$noerrortransaksi=$nod=0;
	$texterrorsaldodivisi='';
    if(!empty($unitDetailAkses)){
        $str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $param['periodeId']. "' and   saldoakhirqty>0 and kodegudang in (select kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANGTEMP' and induk IN ".$gudang_detailAkses.")";
    }else{
        $str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $param['periodeId']. "' and   saldoakhirqty>0 and kodegudang in (select kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANGTEMP' and induk='" .$param['unitId'] . "')";
    }
	$res=fetchdata($str);
	foreach($res as $bar){
		$noerrortransaksi++;
		$temuan['saldodivisi'][]=array('kodegudang'=>$bar['kodegudang'],'namagudang'=>$OptOrganisasi[$bar['kodegudang']],'kodebarang'=>$bar['kodebarang'],'namabarang'=>$OptBarang[$bar['kodebarang']],'jumlah'=>hidezerodecimal($bar['saldoakhirqty'],10));
	}
	#==============================================================================================================================================
	
	
	#==============================================================================================================================================
	#= cek belum ada penerimaan mutasi yang belum dibuat
	$noerrortransaksi=$nod=0;
    if(!empty($unitDetailAkses)){
        // likeee
        $str="select * from ".$dbname.".log_transaksi_vw where tipetransaksi=7 ".$whereClause_gudangx." and tanggal <='".tglakhir($param['periodeId'])."' and (notransaksireferensi='' or notransaksireferensi is null)";
    }else{
        $str="select * from ".$dbname.".log_transaksi_vw where tipetransaksi=7 and gudangx like '".$param['unitId']."%' and tanggal <='".tglakhir($param['periodeId'])."' and (notransaksireferensi='' or notransaksireferensi is null)";
    }
	$res=fetchdata($str);
	foreach($res as $bar){
		$noerrortransaksi++;
		$temuan['mutasibelumterima'][]=array('notransaksi'=>$bar['notransaksi'],'tanggal'=>tanggalnormal($bar['tanggal']),'kodebarang'=>$bar['kodebarang'],'namabarang'=>$OptBarang[$bar['kodebarang']],
			'keterangan'=>($bar['statussaldo']==1 ? "Belum dibuatkan penerimaan mutasi" : "Pengiriman dari ".$OptOrganisasi[substr($bar['kodegudang'],0,4)]." belum diposting"));
	}
  
  #==============================================================================================================================================
  
  
  
  
    $tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
    if($tipeorg[$param['unitId']] == 'KEBUN') {
        $bkmMat=$log = Array();
        if(!empty($unitDetailAkses)){
            $str = "select a.*,b.jurnal,b.tanggal,c.kodekegiatan from " . $dbname . ".kebun_pakaimaterial a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
                     left join " . $dbname . ".kebun_prestasi c on a.notransaksi=c.notransaksi
                      where b.tanggal>='" . $awal . "' and b.tanggal<='" . $akhir . "' and b.jurnal=1 and b.kodeorg IN ".$gudang_detailAkses." ";
        }else{
            $str = "select a.*,b.jurnal,b.tanggal,c.kodekegiatan from " . $dbname . ".kebun_pakaimaterial a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
                     left join " . $dbname . ".kebun_prestasi c on a.notransaksi=c.notransaksi
                      where b.tanggal>='" . $awal . "' and b.tanggal<='" . $akhir . "' and b.jurnal=1 and b.kodeorg='" . $param['unitId'] . "'";
        }
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $bkmMat[] = $bar;
            $bkmLast[] = $bar;
        }
        $namabrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$bar['kodebarang']);
        
         #ambil transaksi gudang
         if(!empty($unitDetailAkses)){
            // likeee
             $str = "select notransaksireferensi,kodebarang from " . $dbname . ".log_transaksi_vw where 1+1 ".$whereClause_kodegudang." and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "'
                       and tipetransaksi=5 and notransaksireferensi is not null and notransaksireferensi!='' order by kodegudang";
         }else{
             $str = "select notransaksireferensi,kodebarang from " . $dbname . ".log_transaksi_vw where kodegudang like '" . $param['unitId'] . "%' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "'
                       and tipetransaksi=5 and notransaksireferensi is not null and notransaksireferensi!='' order by kodegudang";
         }
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $log[] = $bar;
        }
        
        if ((isset($log) ? count($log) : 0) > 0) {
            foreach ($log as $key => $val) {
                foreach ($bkmMat as $key1 => $val1) {
                    if (($val1['notransaksi'] == $val['notransaksireferensi']) and ($val1['kodebarang']==$val['kodebarang'])) {
                        unset($bkmLast[$key1]);
                    }
                }
            }
        }
        if ((isset($bkmLast) ? count($bkmLast) : 0) == 0) {
             
        } else {
            $temuan['integrity']=true;
            renderPraTutupBuku($temuan, array(), $param);
            exit();
        }
    }

     /* * ************************************************************
     * [START] Cek Nilai Material VS Jurnal ***********************
     * ************************************************************ */
	 
	 
	  if($tipeorg[$param['unitId']] == 'KANWIL') {
		  
		  			$optKel = makeOption($dbname, 'log_5klbarang', "kode,noakun", "noakun!='' and noakun like '11504%'");
			$listKel = $listAkun = array();
			foreach ($optKel as $kode => $akun) {
				$listKel[] = $kode;
				$listAkun[$akun] = $akun;
			}

			// Get Nilai Material, log_5saldobulanan
			$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
			  FROM " . $dbname . ".log_5saldobulanan 
			  WHERE left(kodebarang,3) in ('" . implode("','", $listKel) . "') and kodegudang like '" . $param['unitId'] . "%' and periode='" . $param['periodeId'] . "' GROUP BY left(kodebarang,3)";
			//echo $qSaldoMat."<p>";
			$resSaldoMat = fetchData($qSaldoMat);
			$optSaldoMat = array();
			foreach ($resSaldoMat as $row) {
				if (!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
					$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
				} else {
					$optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
				}
			}
			$dtAdd=explode("-",$param['periodeId']);
			$periodeKuangan = $dtAdd[0] . $dtAdd[1];
			// Get Nilai Jurnal, keu_saldobulanan
			$qSaldoJ = "SELECT awal" . $dtAdd[1] . " as saldoawal,noakun
			  FROM " . $dbname . ".keu_saldobulanan
			  WHERE kodeorg='" . $param['unitId'] . "' and periode='" . $periodeKuangan. "'
				and noakun in ('" . implode("','", $listAkun) . "')";
			//echo $qSaldoJ."<p>";
			$resSaldoJ = fetchData($qSaldoJ);
			$optSaldoJ = array();
			foreach ($resSaldoJ as $row) {
				$optSaldoJ[$row['noakun']] = $row['saldoawal'];
			}
			$lstAkun2=array();
			// Get Transaksi Jurnal
			$qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
			  FROM " . $dbname . ".keu_jurnaldt_vw
			  WHERE kodeorg='" . $param['unitId'] . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
				and noakun in ('" . implode("','", $listAkun) . "')
			  GROUP BY noakun";
			//echo $qTrans;
			//echo $qTrans."<p>";
			$resTrans = fetchData($qTrans);
			foreach ($resTrans as $row) {
				if (!isset($optSaldoJ[$row['noakun']]))
					$optSaldoJ[$row['noakun']] = 0;
				$optSaldoJ[$row['noakun']] += $row['saldotrans'];
			}

		  
	  }else{
		  // Get Kelompok Barang yang ada Akun
			$optKel = makeOption($dbname, 'log_5klbarang', "kode,noakun", "noakun!='' and noakun like '11501%'");
			$listKel = $listAkun = array();
			foreach ($optKel as $kode => $akun) {
				$listKel[] = $kode;
				$listAkun[$akun] = $akun;
			}

			// Get Nilai Material, log_5saldobulanan
            if(!empty($unitDetailAkses)){
                // likeee
                $qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
                  FROM " . $dbname . ".log_5saldobulanan 
                  WHERE left(kodebarang,3) in ('" . implode("','", $listKel) . "') ".$whereClause_kodegudang." and periode='" . $param['periodeId'] . "' GROUP BY left(kodebarang,3)";
            }else{
                $qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
                  FROM " . $dbname . ".log_5saldobulanan 
                  WHERE left(kodebarang,3) in ('" . implode("','", $listKel) . "') and kodegudang like '" . $param['unitId'] . "%' and periode='" . $param['periodeId'] . "' GROUP BY left(kodebarang,3)";
            }
            if ($_SESSION['standard']['userid']=='0000000001') {
                # code...
                echo $qSaldoMat."<p>";
            }
			$resSaldoMat = fetchData($qSaldoMat);
			$optSaldoMat = array();
			foreach ($resSaldoMat as $row) {
				if (!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
					$optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
				} else {
					$optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
				}
			}
			$dtAdd=explode("-",$param['periodeId']);
			$periodeKuangan = $dtAdd[0] . $dtAdd[1];
			// Get Nilai Jurnal, keu_saldobulanan
            if(!empty($unitDetailAkses)){
                $qSaldoJ = "SELECT awal" . $dtAdd[1] . " as saldoawal,noakun
                  FROM " . $dbname . ".keu_saldobulanan
                  WHERE kodeorg IN ".$gudang_detailAkses." and periode='" . $periodeKuangan. "'
                    and noakun in ('" . implode("','", $listAkun) . "')";
                }else{
                $qSaldoJ = "SELECT awal" . $dtAdd[1] . " as saldoawal,noakun
                  FROM " . $dbname . ".keu_saldobulanan
                  WHERE kodeorg='" . $param['unitId'] . "' and periode='" . $periodeKuangan. "'
                    and noakun in ('" . implode("','", $listAkun) . "')";
            }
            if ($_SESSION['standard']['userid']=='0000000001') {
                # code...
                // echo $qSaldoMat."<p>";
                echo $qSaldoJ."<p>";
            }
			$resSaldoJ = fetchData($qSaldoJ);
			$optSaldoJ = array();
			foreach ($resSaldoJ as $row) {
				$optSaldoJ[$row['noakun']] = $row['saldoawal'];
			}
			$lstAkun2=array();
			// Get Transaksi Jurnal
            if(!empty($unitDetailAkses)){
                $qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
                  FROM " . $dbname . ".keu_jurnaldt_vw
                  WHERE kodeorg IN ".$gudang_detailAkses." and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
                    and noakun in ('" . implode("','", $listAkun) . "')
                  GROUP BY noakun";
            }else{
                $qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
                  FROM " . $dbname . ".keu_jurnaldt_vw
                  WHERE kodeorg='" . $param['unitId'] . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
                    and noakun in ('" . implode("','", $listAkun) . "')
                  GROUP BY noakun";
            }
            if ($_SESSION['standard']['userid']=='0000000001') {
                # code...
                // echo $qSaldoMat."<p>";
                echo $qTrans."<p>";
            }
			//echo $qTrans;
			$resTrans = fetchData($qTrans);
			foreach ($resTrans as $row) {
				if (!isset($optSaldoJ[$row['noakun']]))
					$optSaldoJ[$row['noakun']] = 0;
				$optSaldoJ[$row['noakun']] += $row['saldotrans'];
			}
	  }
	 
	 
    

    // Cek All Akun
    $notBal = "";
    foreach ($listAkun as $akun) {
        if (!isset($optSaldoMat[$akun]))
            $optSaldoMat[$akun] = 0;
        if (!isset($optSaldoJ[$akun]))
            $optSaldoJ[$akun] = 0;

        $selisih = abs(abs($optSaldoMat[$akun]) - abs($optSaldoJ[$akun]));
        if ($selisih > 300) {
            $lstAkun2[$akun]=$akun;
            $lstNilai[$akun]="Angka Selisih : ".number_format($selisih) . ", Angka Material : ". number_format(abs($optSaldoMat[$akun])) . ", Angka Jurnal : " . number_format(abs($optSaldoJ[$akun]));
            $notBal .= $akun . " = " . number_format($selisih) . "___" . abs($optSaldoMat[$akun]) . "____" . abs($optSaldoJ[$akun]) . "\n";
        }
    }
    $akunBelumBalance=array();
    foreach ($lstAkun2 as $key) {
        $optNmAkun=makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$key."'");
        $akunBelumBalance[]=array('noakun'=>$key,'nama'=>$optNmAkun[$key],'material'=>abs($optSaldoMat[$key]),'jurnal'=>abs($optSaldoJ[$key]),'selisih'=>abs(abs($optSaldoMat[$key])-abs($optSaldoJ[$key])));
    }
    renderPraTutupBuku($temuan, $akunBelumBalance, $param);
    /* * ************************************************************
     * [END] Cek Nilai Material VS Jurnal *************************
     * ************************************************************ */
  break;
  case'cekAwal2':
    $arrTrans=array();
    $arrJurnal=array();
    $key=$param['noakun'];
        #ambil data transaksi
        //pemakaian diluar bkm
        if(!empty($unitDetailAkses)){
            $scek="select notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4) IN ".$gudang_detailAkses."
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=5 and notransaksireferensi is null group by notransaksi";
        }else{
            $scek="select notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4)='".$param['unitId']."' 
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=5 and notransaksireferensi is null group by notransaksi";
        }
        // echo $scek;
        $rcek=fetchData($scek);
        if(count($rcek)!=0){
            foreach($rcek as $rw=>$val){
                $arrTrans[$val['notransaksi']]=$val['itung'];
                $lstTrans[$val['notransaksi']]=$val['notransaksi'];
            }
        }
        //pemakaian bkm
        if(!empty($unitDetailAkses)){
            $scek="select  notransaksireferensi as notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4) IN ".$gudang_detailAkses."
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=5 and notransaksireferensi is not null group by notransaksireferensi";
        }else{
            $scek="select  notransaksireferensi as notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4)='".$param['unitId']."' 
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=5 and notransaksireferensi is not null group by notransaksireferensi";
        }
        $rcek=fetchData($scek);
        if(count($rcek)!=0){
            foreach($rcek as $rw=>$val){
                $arrTrans[$val['notransaksi']]=$val['itung'];
                $lstTrans[$val['notransaksi']]=$val['notransaksi'];
            }
        }
        //terima mutasi beda unit
        if(!empty($unitDetailAkses)){
            $scek="select notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4) IN ".$gudang_detailAkses."
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=3 and left(kodegudang,4)<>left(gudangx,4) group by notransaksi";
        }else{
            $scek="select notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4)='".$param['unitId']."' 
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=3 and left(kodegudang,4)<>left(gudangx,4) group by notransaksi";
        }
        $rcek=fetchData($scek);
        if(count($rcek)!=0){
            foreach($rcek as $rw=>$val){
                $arrTrans[$val['notransaksi']]=$val['itung'];
                $lstTrans[$val['notransaksi']]=$val['notransaksi'];
            }
        }
        //mutasi beda unit
        if(!empty($unitDetailAkses)){
            $scek="select notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4) IN ".$gudang_detailAkses."
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=7 and left(kodegudang,4)<>left(gudangx,4) group by notransaksi";
        }else{
            $scek="select notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4)='".$param['unitId']."' 
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=7 and left(kodegudang,4)<>left(gudangx,4) group by notransaksi";
        }
        $rcek=fetchData($scek);
        if(count($rcek)!=0){
            foreach($rcek as $rw=>$val){
                $arrTrans[$val['notransaksi']]=$val['itung'];
                $lstTrans[$val['notransaksi']]=$val['notransaksi'];
            }
        }
        //penerimaan supplier
        if(!empty($unitDetailAkses)){
            $scek="select notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4) IN ".$gudang_detailAkses."
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=1  group by notransaksi";
        }else{
            $scek="select notransaksi,count(notransaksi) as itung from ".$dbname.".log_transaksi_vw where left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."') and left(kodegudang,4)='".$param['unitId']."' 
                    and tanggal between  '".$awal."' and '".$akhir."' and tipetransaksi=1  group by notransaksi";
        }
        // echo $scek;
        $rcek=fetchData($scek);
        if(count($rcek)!=0){
            foreach($rcek as $rw=>$val){
                $arrTrans[$val['notransaksi']]=$val['itung'];
                $lstTrans[$val['notransaksi']]=$val['notransaksi'];
            }
        }
        #jurnal data
        if(!empty($unitDetailAkses)){
            $sGrJr="select noreferensi,count(noreferensi) as itung from ".$dbname.".keu_jurnaldt_vw where noakun='".$key."' and tanggal between  '".$awal."' and '".$akhir."'  and kodejurnal like 'INVM%' and kodeorg IN ".$gudang_detailAkses." group by noreferensi";
        }else{
            $sGrJr="select noreferensi,count(noreferensi) as itung from ".$dbname.".keu_jurnaldt_vw where noakun='".$key."' and tanggal between  '".$awal."' and '".$akhir."'  and kodejurnal like 'INVM%' and kodeorg='".$param['unitId']."' group by noreferensi";
        }
        // echo $sGrJr;
        $rGrJr=fetchData($sGrJr);
        if(count($rGrJr)!=0){
            foreach($rGrJr as $rw=>$val){
                $arrJurnal[$val['noreferensi']]=$val['itung'];
                $lstTrans[$val['notransaksi']]=$val['notransaksi'];
            }
        }
        if(!empty($unitDetailAkses)){
            $sGrJr="select noreferensi,count(noreferensi) as itung from ".$dbname.".keu_jurnaldt_vw where noakun='".$key."' and tanggal between  '".$awal."' and '".$akhir."'  and kodejurnal like 'INVK%' and kodeorg IN ".$gudang_detailAkses." group by noreferensi";
        }else{
            $sGrJr="select noreferensi,count(noreferensi) as itung from ".$dbname.".keu_jurnaldt_vw where noakun='".$key."' and tanggal between  '".$awal."' and '".$akhir."'  and kodejurnal like 'INVK%' and kodeorg='".$param['unitId']."' group by noreferensi";
        }
        $rGrJr=fetchData($sGrJr);
        if(count($rGrJr)!=0){
            foreach($rGrJr as $rw=>$val){
                $arrJurnal[$val['noreferensi']]=$val['itung'];
                $lstTrans[$val['notransaksi']]=$val['notransaksi'];
            }
        }
        $arrNonGdng=array();
        if(!empty($unitDetailAkses)){
            $sGrJr="select nojurnal,count(nojurnal) as itung from ".$dbname.".keu_jurnaldt_vw where noakun='".$key."' and tanggal between  '".$awal."' and '".$akhir."'  and (kodejurnal not like 'INVK%' and kodejurnal not like 'INVM%') and kodeorg IN ".$gudang_detailAkses." group by nojurnal";
        }else{
            $sGrJr="select nojurnal,count(nojurnal) as itung from ".$dbname.".keu_jurnaldt_vw where noakun='".$key."' and tanggal between  '".$awal."' and '".$akhir."'  and (kodejurnal not like 'INVK%' and kodejurnal not like 'INVM%') and kodeorg='".$param['unitId']."' group by nojurnal";
        }
        $rGrJr=fetchData($sGrJr);
        if(count($rGrJr)!=0){
            $arrNonGdng[]=$rGrJr;
        }

        $arrBlmJurnal=array();
        $arrDouble=array();
        $arrTransBkm=array();
        if(count($lstTrans)!=0){
            foreach($lstTrans as $transaksiLst){
                if($arrTrans[$transaksiLst]!=$arrJurnal[$transaksiLst]){
                    #jika jurnal dgn transaksi tidak sama, maka dicek apakah double atau blm terjurnal
                    if($arrTrans[$transaksiLst]<$arrJurnal[$transaksiLst]){
                        $sData="select distinct nojurnal,noreferensi,tanggal,noakun from ".$dbname.".keu_jurnaldt_vw where noreferensi='".$transaksiLst."' and noakun='".$key."'";
                        $rData=fetchData($sData);
                        if(count($rData)!=0){
                            $arrDouble[]=$rData;//jurnal doubl ketika jurnal lebih besar dibandingkan transaksi
                        }
                    }else{
                        //belum terjurnal cek atas transksi mana yg blm terjurnal
                        #cek bkm atau bukan
                        $sData="select * from ".$dbname.".log_transaksi_vw where notransaksireferensi='".$transaksiLst."' and tipetransaksi=5 and left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."')";
                        $rData=fetchData($sData);
                        $adabkm=1;
                        if(count($rData)==0){
                            $sData="select * from ".$dbname.".log_transaksi_vw where notransaksi='".$transaksiLst."' and left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$key."')";
                            $rData=fetchData($sData);#diluar bkm
                            $adabkm=0;
                        }
                        if(count($rData)!=0){
                            foreach($rData as $rt=>$val2){
                                if($val2['tipetransaksi']==1){//klo penerimaan maka dicek nopp dan nopo
                                    $whd.=" and kodeblok='".$val2['nopp']."' and nodok='".$val2['nopo']."'";
                                }else{
                                    $whd.=" and kodeblok='".$val2['kodeblok']."'";
                                }
                                $sJur="select * from ".$dbname.".keu_jurnaldt_vw 
                                       where noreferensi='".$transaksiLst."' ".$whd." and noakun='".$key."' and kodebarang='".$val2['kodebarang']."'
                                       and kodekegiatan='".$val2['kodekegiatan']."' and kodevhc='".$val2['kodemesin']."'  ";
                                $rJur=fetchData($sJur);
                                if(count($rJur)==0){
                                    if($adabkm==0){
                                        $arrBlmJurnal[]=$rData[$rt];
                                    }else{
                                        $arrTransBkm[$rData[$rt]['notransaksireferensi']]=$rData[$rt]['notransaksireferensi'];
                                    }
                                }
                            }
                        }
                    }
                }else{
                    #jika jurnal dgn transaksi sama unset
                    unset($arrTrans[$transaksiLst]);
                    unset($arrJurnal[$transaksiLst]);
                    unset($lstTrans[$transaksiLst]);
                }
            }
        }
    
    $tab="<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>";
    $tab.="<thead><tr class=rowheader>";
    $tab.="<th>".$_SESSION['lang']['nojurnal']."</th>";
    $tab.="<th>".$_SESSION['lang']['tanggal']."</th>";
    $tab.="<th>".$_SESSION['lang']['noakun']."</th>";
    $tab.="<th>".$_SESSION['lang']['noreferensi']."</th>";
    $tab.="</tr></thead><tbody>";
//    echo"<pre>";
//    print_r($arrBlmJurnal);
//    echo"</pre>";
    if($arrBlmJurnal!=0){
        foreach($arrBlmJurnal as $rwd=>$arrdtisi){
            createJurnal($arrBlmJurnal[$rwd]);
        }
        foreach($arrBlmJurnal as $rwd=>$val2){
            $whd=" noreferensi='".$val2['notransaksi']."'";
            $sJur="select * from ".$dbname.".keu_jurnaldt_vw 
            where ".$whd." and noakun='".$key."' and kodebarang='".$val2['kodebarang']."'
            and kodekegiatan='".$val2['kodekegiatan']."' and kodevhc='".$val2['kodemesin']."' ";
            // echo $sJur;
            $rJur=fetchData($sJur);
            $rJur=fetchData($sJur);
            if(count($rJur)!=0){
                $dtis="bgcolor='#00cc99' title='Jurnal Sudah Dibentuk'";
                foreach($rJur as $wd=>$dtisi){
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td ".$dtis.">".$dtisi['nojurnal']."</td>";
                    $tab.="<td ".$dtis.">".$dtisi['tanggal']."</td>";
                    $tab.="<td ".$dtis.">".$dtisi['noakun']."</td>";
                    $tab.="<td ".$dtis.">".$dtisi['noreferensi']."</td>";
                    $tab.="</tr>";
                }
            }
        }
    }
    if(count($arrTransBkm)!=0){
        foreach($arrTransBkm as $notransbkm){
            createJrnBKm($notransbkm);
        }
        foreach($arrTransBkm as $notransbkm){
            $whd="";
            $sJur="select distinct nojurnal,tanggal,noakun,noreferensi from ".$dbname.".keu_jurnaldt_vw where  noreferensi='".$notransbkm."' and noakun='".$key."' and keterangan like '%Material BKM%'";
            // echo $sJur;
            $rJur=fetchData($sJur);
            if(count($rJur)!=0){
                $dtis="bgcolor='#00cc99' title='Jurnal Sudah Dibentuk'";
                foreach($rJur as $wd=>$dtisi){
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td ".$dtis.">".$dtisi['nojurnal']."</td>";
                    $tab.="<td ".$dtis.">".$dtisi['tanggal']."</td>";
                    $tab.="<td ".$dtis.">".$dtisi['noakun']."</td>";
                    $tab.="<td ".$dtis.">".$dtisi['noreferensi']."</td>";
                    $tab.="</tr>";
                }
            }
        }
    }
     
    if(count($arrDouble)!=0){
        $tempdt="";
         
        foreach($arrDouble as $rw=>$arrisi){
            foreach($arrisi as $rw2=>$val){
                    if($tempdt!=$lstTrans[$val['noreferensi']]){
                        $tempdt=$lstTrans[$val['noreferensi']];
                        $stt=0;
                        $dtis="";
                    }else{
                        $sdel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$val['nojurnal']."'";
                        $owlPDO->exec($sdel);
                        $stt=1;
                    }
                    if($stt==1){
                        $dtis=" bgcolor=#00cc99 title='Jurnal Double Sudah Dihapus'";
                    }
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td ".$dtis.">".$val['nojurnal']."</td>";
                    $tab.="<td ".$dtis.">".$val['tanggal']."</td>";
                    $tab.="<td ".$dtis.">".$val['noakun']."</td>"; 
                    $tab.="<td ".$dtis.">".$val['noreferensi']."</td>";
                    $tab.="</tr>";
            }
        }
        
    }
    if(count($arrNonGdng)!=0){
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=4>Jurnal Diluar Transaksi Gudang</td>";
        $tab.="</tr>";
        foreach($arrNonGdng as $rw=>$notransbkm){
            $sJur="select distinct nojurnal,tanggal,noakun,noreferensi from ".$dbname.".keu_jurnaldt_vw where  noreferensi='".$notransbkm."' and noakun='".$key."' and keterangan like '%Material BKM%'";
            // echo $sJur;
            $rJur=fetchData($sJur);
            if(count($rJur)!=0){
                foreach($rJur as $rw2=>$val){
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td>".$val['nojurnal']."</td>";
                    $tab.="<td>".$val['tanggal']."</td>";
                    $tab.="<td>".$val['noakun']."</td>";
                    $tab.="<td>".$val['noreferensi']."</td>";
                    $tab.="</tr>";
                }
            }
        }
    }
    $tab.="</tbody></table>";
    echo $tab;
  break;
}

function createJurnal($dtarr){
    global $dbname;
    global $param;
    global $owlPDO;
    
        if (isTransactionPeriod()){
            //check if transaction period is normal
            $tipetransaksi = $dtarr['tipetransaksi'];
            $tanggal = tanggalnormal($dtarr['tanggal']);
            $kodebarang = $dtarr['kodebarang'];
            $satuan = $dtarr['satuan'];
            $jumlah = $dtarr['jumlah'];
            $kodept = $dtarr['kodept'];
            $gudangx = $dtarr['gudangx'];
            $untukpt = $dtarr['untukpt'];
            $gudang = $dtarr['kodegudang'];
            $blok = $dtarr['kodeblok'];
            $kdpabrikasi = $dtarr['kdpabrikasi'];
            $notransaksi = $dtarr['notransaksi'];
            $hargasatuan = $dtarr['hargasatuan'];
            $hargarata = $dtarr['hargarata'];
            $nopo = $dtarr['nopo'];
            $nopp = $dtarr['nopp'];
            $supplier = $dtarr['idsupplier'];
            $kodekegiatan = $dtarr['kodekegiatan'];
            $kodemesin = $dtarr['kodemesin'];
            if($tipetransaksi<4){
                $nilaitotal=$hargasatuan*$jumlah;
            }
           
            $user = $_SESSION['standard']['userid'];
            //$segment = !empty($_POST['kodesegment']) ? $_POST['kodesegment'] : colDefaultValue($dbname, 'keu_5segment', 'kodesegment');
            // $segment = kodesegment($_SESSION['empl']['lokasitugas']);
            $segment=1;
            ## Validasi Kode barang
            if (!preg_match('/^[0-9]{9}$/', $kodebarang)) {
                exit("Warning: Kode Barang tidak standard ".$kodebarang."");
            }
            
         
            
            ## Periksa apakah sudah pernah mempengaruhi saldo
            if(!is_null($dtarr['notransaksireferensi'])&&$dtarr['notransaksireferensi']!=""){
                $bkmjrn=0;
                $scek="select * from ".$dbname.".kebun_pakai_material_vw where notransaksi='".$dtarr['notransaksireferensi']."'";
                $rcek=fetchData($scek);
                if(count($rcek)!=0){
                    $bkmjrn=1;
                    $param['notransaksi']=$dtarr['notransaksireferensi'];
                }

            }
           
                ## statussaldo=1
                ## Periksa apakah sudah tutup buku
                ## Unit sendiri
                $close = 0;
                $periode = $_SESSION['gudang'][$gudang]['tahun']."-".$_SESSION['gudang'][$gudang]['bulan'];
                $str = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".substr($gudang, 0, 4)."'";
                $res=fetchdata($str);
                $close = $res[0]['tutupbuku'];
                
                if ($close == '1'){
                    if($_SESSION['language']=='ID'){
                        exit ("Error : Keuangan sudah tutup buku");
                    }else{
                        exit("Error : Accounting Period has been closed.");				
                    }
                }
                
                ## Unit tujuan
                if($gudangx != '' and (substr($gudang, 0, 4) != substr($gudangx, 0, 4))){
                    
                    ## Jika mutasi dan gudang tujuan ada di unit berbeda
                    $close = 0;
                    $str = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".substr($gudangx, 0, 4)."'";
                    $res=fetchdata($str);
                    $close = $res[0]['tutupbuku'];
                    
                    if($close == '1' and $tipetransaksi != '3'){
                        ## Khusus penerimaan mutasi dikecualikan boleh di jurnal walau pengirim sudah utup bk
                        if($_SESSION['language']=='ID'){
                            exit ("Error : Keuangan sudah tutup buku");
                        }else{
                            exit("Error : Receiver Accounting Period has been closed.");
                        }
                    }
                }
                 
                
                ## Ambil nama barang
                $namabarang = '';
                $str = "select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."'";
                $res=fetchdata($str);
                $namabarang = $res[0]['namabarang'];
                
                if($namabarang == ''){
                    $namabarang = $kodebarang;			
                }
                
                ########################################
                #### Begin Penerimaan dari Supplier ####
                ########################################
                if($tipetransaksi == '1'){
                    try{
                        $owlPDO->beginTransaction();
                        ## Periksa harga satuan
                        if(intval($hargasatuan) == 0 or $nopo == '' or $supplier == ''){
                            throw new PDOException("Price/PO/Supplier not found.\n\nHarga/PO/supplier tidak ditemukan");
                        }
                        $flaggrir=0; 
                        ## Prepare jurnal
                        ## Ambil noakun supplier
                        $akunspl = '';
                        $kodekl = substr($supplier, 0, 4);
                        $str = "select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
                        $res=fetchdata($str);
                        $akunspl = $res[0]['noakun'];
                        
                        ## Ambil noakun barang
                        $akunbarang = '';
                        $klbarang = substr($kodebarang, 0, 3);
                        $str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=fetchdata($str);
                        $akunbarang = $res[0]['noakun'];
                        
                        if (($akunbarang == '' or $akunspl == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
                            throw new PDOException("Account no. for material or supplier not available yet for " . $notransaksi."\n\nNoakun barang atau supplier  belum ada untuk transaksi ".$notransaksi);
                        }
                        
                        if(($klbarang=='400')||(substr($klbarang,0,1)=='8')){
                            throw new PDOException($_SESSION['lang']['kelompokbarang']." : ".$klbarang." Tidak Bisa Diterimakan");
                        }
                        
                        ## Cek Nilai Ppn di PO
                        $str = "select * from ".$dbname.".log_poht where nopo='".$nopo."'";
                        $res = fetchData($str);
                        if(count($res) <= 0){
                            throw new PDOException("PO " . $nopo . " tidak terdaftar");
                        }
                        $nilaiPpn = $res[0]['ppn'] * $res[0]['kurs'] * ($nilaitotal / ($res[0]['kurs'] * ($res[0]['subtotal'] - $res[0]['nilaidiskon'])));
                        
                        
                        ## Proses data
                        $kodeJurnal = 'INVM1';
                        
                        ##======================== Begin Nomor Jurnal =============================
                        ## Get Journal Counter
                        $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."'";
                        $tmpKonter = fetchData($str);
                        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
        
                        ## Transform No Jurnal dari No Transaksi
                        $nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
                        ##======================== End Nomor Jurnal ============================
                        
                        ## Prep Header
                        $dataRes['header'] = array(
                            'nojurnal' => $nojurnal,
                            'kodejurnal' => $kodeJurnal,
                            'tanggal' => tanggalsystem($tanggal),
                            'tanggalentry' => date('Ymd'),
                            'posting' => 1,
                            'totaldebet' => $nilaitotal,
                            'totalkredit' => -1 * $nilaitotal,
                            'amountkoreksi' => '0',
                            'noreferensi' => $notransaksi,
                            'autojurnal' => '1',
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'revisi' => '0'
                        );
        
                        ## Data Detail
                        $noUrut = 1;
        
                        ## Debet
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => tanggalsystem($tanggal),
                            'nourut' => $noUrut,
                            'noakun' => $akunbarang,
                            'keterangan' => 'Pembelian barang ' . $namabarang . ' ' . $jumlah . " " . $satuan,
                            'jumlah' => $nilaitotal,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => substr($gudang, 0, 4),
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => $kodebarang,
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $nopo,
                            'kodeblok' => $nopp,
                            'revisi' => '0',
                            'kodesegment' => $segment
                        );
                        $noUrut++;
        
                        ## Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => tanggalsystem($tanggal),
                            'nourut' => $noUrut,
                            'noakun' => $akunspl,
                            'keterangan' => 'Pembelian barang ' . $namabarang . ' ' . $jumlah . " " . $satuan,
                            'jumlah' => (-1) * $nilaitotal,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => substr($gudang, 0, 4),
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => $kodebarang,
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $nopo,
                            'kodeblok' => $nopp,
                            'revisi' => '0',
                            'kodesegment' => $segment
                        );
                        $noUrut++;
        
                       
                        ## execute
                        if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
                            $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                            $owlPDO->exec($insHead); 
                            
                            foreach ($dataRes['detail'] as $row){
                                $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                $owlPDO->exec($insDet);
                            }
                            
                            ## Header and Detail inserted
                            ## Update Kode Jurnal
                            $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."'");
                            $owlPDO->exec($updJurnal);    
                        }                        
                        $owlPDO->commit();
                    }catch(PDOException $e){
                        $owlPDO->rollback();
                        echo "Error, " . addslashes($e->getMessage());
                    }
                }
                ######################################
                #### End Penerimaan dari Supplier ####
                ######################################
                
                
                #################################
                #### Begin Retur ke Supplier ####
                #################################
                if ($tipetransaksi == '6'){
                    try{
                        $owlPDO->beginTransaction();
                        throw new PDOException("Retur ke supplier tidak bisa di lakukan");
                        
                        ## Periksa harga satuan
                        if (intval($hargasatuan) == 0 or $nopo == '' or $supplier == '') {
                            throw new PDOException("Price/PO/Supplier not found");
                        }
                        
                        ## Generate saldo updater
                        ## Ambil saldo saat ini 
                        $nilaitotal = $jumlah * $hargasatuan;
                        $cursaldo = 0;
                        $nilaisaldo = 0;
                        $qtymasuk = 0;
                        $qtymasukxharga = 0;
                        $saldoakhirqty = 0;
                        $nilaisaldoakhir = 0;
                        $hargarata = 0;
                        
                        $str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                        $res=fetchdata($str);
                        $numrows=count($res);
                        if ($numrows < 1) {
                            ## Jika belum ada penerimaan sebelumnya
                            $newhargarata = $hargasatuan;
                            $newqtykeluar = $jumlah;
                            $newqtykeluarxharga = $nilaitotal;
                            $newsaldoakhirqty = $jumlah;
                            $newnilaisaldoakhir = $nilaitotal;
                        }
                        
                        ## Bentuk harga baru
                        foreach($res as $key=>$val){
                            $cursaldo = $val['saldoakhirqty'];
                            $nilaisaldo = $val['nilaisaldoakhir'];
                            $qtykeluar = $val['qtykeluar'];
                            $qtykeluarxharga = $val['qtykeluarxharga'];
                            $hargarata = $val['hargarata'];
                        }
                        
                        if (($cursaldo - $jumlah) <= 0){
                            $newhargarata = $hargasatuan;
                        }else{
                            @$newhargarata = ($nilaisaldo - $nilaitotal) / ($cursaldo - $jumlah);
                        }
                        
                        $newqtykeluar = $qtykeluar + $jumlah;
                        @$newqtykeluarxharga = $qtykeluarxharga + $nilaitotal;
                        $newsaldoakhirqty = $cursaldo - $jumlah;
                        #$newnilaisaldoakhir = $newhargarata * $newsaldoakhirqty;
                        $newnilaisaldoakhir = $nilaisaldo - $nilaitotal;
                        
                        if ($newsaldoakhirqty < 0){
                            throw new PDOException("Amount not sufficient (retur:" . $jumlah . " volume:" . $cursaldo."\n\nSaldo tidak mencukupi (retur:".$jumlah." saldo:".$cursaldo);
                        }
                        
                        if ($newhargarata == 0){
                            throw new PDOException("Average price can not be formed on " . $notransaksi . " material code :" . $kodebarang."\n\nHargarata tidak dapat dibentuk pada ".$notransaksi." kodebarang :".$kodebarang);
                        }else{
                            $strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."', hargarata='".$newhargarata."', nilaisaldoakhir='".$newnilaisaldoakhir."', lastuser='".$user."', qtykeluar='".$newqtykeluar."', qtykeluarxharga='".$newqtykeluarxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                        }
        
                        ## Prepare rollback pengembalian
                        $strrollback = "update " . $dbname . ".log_5saldobulanan set 
                                saldoakhirqty=" . $cursaldo . ", hargarata=" . $hargarata . ",nilaisaldoakhir=" . $nilaisaldo . ",
                                lastuser=" . $user . ",qtykeluar=" . $qtykeluar . ",qtykeluarxharga=" . $qtykeluarxharga . "
                                where periode='" . $periode . "' and kodegudang='" . $gudang . "' and kodebarang='" . $kodebarang . "' and kodeorg='" . $kodept . "'";
        
                        ## Prepare update masterbarangdt
                        $instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','".$newhargarata."','0','0','0','".$user."','".$gudang."')";
                        
                        $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
        
                        ## Prepare jurnal
                        ## Ambil noakun supplier
                        $kodekl = substr($supplier, 0, 4);
                        $akunspl = '';
                        $str = "select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
                        $res=fetchdata($str);
                        $akunspl = $res[0]['noakun'];
                        
                        ## Ambil noakun barang
                        $klbarang = substr($kodebarang, 0, 3);
                        $akunbarang = '';
                        $str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=fetchdata($str);
                        $akunbarang = $res[0]['noakun'];
                        
                        if (($akunbarang == '' or $akunspl == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
                            throw new PDOException("Account number for material or supplier not available yet on " . $notransaksi."\n\nNoakun  Noakun barang atau supplier  belum ada untuk transaksi ".$notransaksi);
                        }
        
                        ## Cek Nilai Ppn di PO
                        $str = "select * from ".$dbname.".log_poht where nopo='".$nopo."'";
                        $res = fetchData($str);
                        if (count($res) <= 0){
                            throw new PDOException("PO " . $nopo . " tidak terdaftar");
                        }
                        $nilaiPpn = $resPO[0]['ppn'] * $resPO[0]['kurs'] * ($nilaitotal / ($resPO[0]['kurs'] * ($resPO[0]['subtotal'] - $resPO[0]['nilaidiskon'])));
                        
                        ## Proses data
                        $kodeJurnal = 'INVK1';
                        ##======================== Begin Nomor Jurnal =============================
                        ## Get Journal Counter
                        $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."'";
                        $tmpKonter = fetchData($str);
                        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
        
                        ## Transform No Jurnal dari No Transaksi
                        $nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
                        ##======================== Begin Nomor Jurnal ============================
                        
                        ## Prep Header
                        $dataRes['header'] = array(
                            'nojurnal' => $nojurnal,
                            'kodejurnal' => $kodeJurnal,
                            'tanggal' => tanggalsystem($tanggal),
                            'tanggalentry' => date('Ymd'),
                            'posting' => 1,
                            'totaldebet' => $nilaitotal,
                            'totalkredit' => -1 * $nilaitotal,
                            'amountkoreksi' => '0',
                            'noreferensi' => $notransaksi,
                            'autojurnal' => '1',
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'revisi' => '0'
                        );
        
                        ## Data Detail
                        $noUrut = 1;
        
                        ## Debet
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => tanggalsystem($tanggal),
                            'nourut' => $noUrut,
                            'noakun' => $akunspl,
                            'keterangan' => 'ReturSupplier ' . $namabarang . ' ' . $jumlah . " " . $satuan,
                            'jumlah' => $nilaitotal,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => substr($gudang, 0, 4),
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => $kodebarang,
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $nopo,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => $segment
                        );
                        $noUrut++;
        
                        ## Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => tanggalsystem($tanggal),
                            'nourut' => $noUrut,
                            'noakun' => $akunbarang,
                            'keterangan' => 'ReturSupplier ' . $namabarang . ' ' . $jumlah . " " . $satuan,
                            'jumlah' => -1 * $nilaitotal,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => substr($gudang, 0, 4),
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => $kodebarang,
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $nopo,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => $segment
                        );
                        $noUrut++;
        
                        ## Kredit PPn
                        if ($nilaiPpn > 0) {
                            $str = "select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='INV' and jurnalid='PPN'";
                            $res = fetchData($str);
                            if (count($res) <= 0){
                                throw new PDOException("No Akun untuk PPn Penerimaan Barang belum ada");
                            }
        
                            $dataRes['detail'][] = array(
                                'nojurnal' => $nojurnal,
                                'tanggal' => tanggalsystem($tanggal),
                                'nourut' => $noUrut,
                                'noakun' => $res[0]['noakundebet'],
                                'keterangan' => 'Retur PPn Pembelian barang ' . $namabarang . ' ' . $jumlah . " " . $satuan,
                                'jumlah' => $nilaiPpn * (-1),
                                'matauang' => 'IDR',
                                'kurs' => '1',
                                'kodeorg' => substr($gudang, 0, 4),
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => $kodebarang,
                                'nik' => '',
                                'kodecustomer' => '',
                                'kodesupplier' => $supplier,
                                'noreferensi' => $notransaksi,
                                'noaruskas' => '',
                                'kodevhc' => '',
                                'nodok' => $nopo,
                                'kodeblok' => '',
                                'revisi' => '0',
                                'kodesegment' => $segment
                            );
                            $noUrut++;
                        }
                        
                       
                        ## Execute
                        if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
                            $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                            $owlPDO->exec($insHead); 
                            
                            foreach ($dataRes['detail'] as $row){
                                $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                $owlPDO->exec($insDet); 
                            }
                            
                            ## Header and Detail inserted
                            ## Update Kode Jurnal
                            $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."'");
                            $owlPDO->exec($updJurnal); 
                        }                         
                        $owlPDO->commit();
                    }catch(PDOException $e){
                        $owlPDO->rollback();
                        echo "Error, " . addslashes($e->getMessage());
                    }
                }
                ###############################
                #### End Retur ke Supplier ####
                ###############################
                
                ######################################
                #### Begin Retur Barang ke Gudang ####
                ######################################
                if ($tipetransaksi == '2'){
                    try{
                        $owlPDO->beginTransaction();
                        
                       
        
                        ## Periksa apakah dari satu PT
                        $pengguna = substr($dtarr['untukunit'], 0, 4);
        
                        $ptpengguna = '';
                        $str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                        $res=fetchdata($str);
                        $ptpengguna = $res[0]['induk'];
                        
                        $intraco = '';
                        $interco = '';
                        $str = "select akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
                        $res=fetchdata($str);
                        foreach($res as $key=>$val){
                            if ($val['jenis'] == 'intra'){
                                $intraco = $val['akunhutang'];
                            }else{
                                $interco = $val['akunhutang'];
                            }
                        }
        
                        if ($intraco=='' || $interco=='') {
                            //exit(" Error: Account intraco or interco not available for ".$pengguna;
                            throw new PDOException("Account intraco or interco not available for ".$pengguna);
                        }  
                        
                        $ptGudang = '';
                        $str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang, 0, 4)."'";
                        $res=fetchdata($str);
                        $ptGudang = $res[0]['induk'];
                        
                        ## Jika pt tidak sama maka pakai akun interco
                        $akunspl = '';
                        if ($ptGudang != $ptpengguna) {
                            ## Ambil akun interco
                            $akunspl = '';
                            $str = "select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang, 0, 4)."' and jenis='inter'";
                            $res=fetchdata($str);
                            $akunspl = $res[0]['akunpiutang'];
                            
                            $inter = $interco;
                            if ($akunspl == ''){
                                throw new PDOException("Account for intraco or interco not available yet for " . $pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
                            }
                        }else if ($pengguna != substr($gudang, 0, 4)){
                            ## Jika satu pt beda kebun
                            ## Ambil akun intraco
                            $akunspl = '';
                            $str = "select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang, 0, 4)."' and jenis='intra'";
                            $res=fetchdata($str);
                            $akunspl = $res[0]['akunpiutang'];
                            
                            $inter = $intraco;
                            if ($akunspl == ''){
                                throw new PDOException("Account for intraco or interco not available yet for " . $pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
                            }
                        }
        
        
                        ## Ambil akun pekerjaan atau kendaraan atau ab
                        ## Periksa ke table setup blok
                        $statustm = '';
                        $str = "select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok."'";
                        $res=fetchdata($str);
                        $statustm = $res[0]['statusblok'];
                        
                        $akunpekerjaan = '';
                        $str = "select noakun from ".$dbname.".setup_kegiatan where kodekegiatan='".$kodekegiatan."'";
                        $res=fetchdata($str);
                        $akunpekerjaan = $res[0]['noakun'];
                        
                        ## Jika akun kegiatan tidak ada maka exit
                        // if ($akunpekerjaan == ''){
                            // throw new PDOException("Account not available yet for activity " . $kodekegiatan."\n\nAkun pekerjaan belum ada untuk kegiatan ".$kodekegiatan);
                        // }
        
                        ## Ambil noakun barang
                        $akunbarang = '';
                        $klbarang = substr($kodebarang, 0, 3);
                        $str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=fetchdata($str);
                        $akunbarang = $res[0]['noakun'];
                        
                        if ($akunbarang == ''){
                            throw new PDOException("Material account not available yet on " . $notransaksi."\n\nNoakun barang belum ada untuk transaksi ".$notransaksi);
                        }else{
                            ## Penggunaan internal
                            if ($pengguna == substr($gudang, 0, 4)){
                                $kodeJurnal = 'INVM1';
                                ##======================== Begin Nomor Jurnal =============================
                                ## Get Journal Counter
                                $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'";
                                $tmpKonter = fetchData($str);
                                $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
        
                                ## Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
                                ##======================== End Nomor Jurnal ============================
                                
                                ## Prep Header
                                $dataRes['header'] = array(
                                    'nojurnal' => $nojurnal,
                                    'kodejurnal' => $kodeJurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'tanggalentry' => date('Ymd'),
                                    'posting' => 1,
                                    'totaldebet' => ($rpkembali),
                                    'totalkredit' => (-1 * $rpkembali),
                                    'amountkoreksi' => '0',
                                    'noreferensi' => $notransaksi,
                                    'autojurnal' => '1',
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'revisi' => '0'
                                );
        
                                ## Data Detail
                                $noUrut = 1;
                                $keterangan = "ReturGudang barang " . $namabarang . " " . $jumlah . " " . $satuan;
                                ## Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal' => $nojurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'nourut' => $noUrut,
                                    'noakun' => $akunbarang,
                                    'keterangan' => $keterangan,
                                    'jumlah' => ($rpkembali),
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'kodeorg' => substr($gudang, 0, 4),
                                    'kodekegiatan' => '',
                                    'kodeasset' => '',
                                    'kodebarang' => $kodebarang,
                                    'nik' => '',
                                    'kodecustomer' => '',
                                    'kodesupplier' => '',
                                    'noreferensi' => $notransaksi,
                                    'noaruskas' => '',
                                    'kodevhc' => $kodemesin,
                                    'nodok' => '',
                                    'kodeblok' => $blok,
                                    'revisi' => '0',
                                    'kodesegment' => $segment
                                );
                                $noUrut++;
        
                                ## Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal' => $nojurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'nourut' => $noUrut,
                                    'noakun' => $akunpekerjaan,
                                    'keterangan' => $keterangan,
                                    'jumlah' => (-1 * $rpkembali),
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'kodeorg' => substr($gudang, 0, 4),
                                    'kodekegiatan' => $kodekegiatan,
                                    'kodeasset' => '',
                                    'kodebarang' => $kodebarang,
                                    'nik' => '',
                                    'kodecustomer' => '',
                                    'kodesupplier' => '',
                                    'noreferensi' => $notransaksi,
                                    'noaruskas' => '',
                                    'kodevhc' => $kodemesin,
                                    'nodok' => '',
                                    'kodeblok' => $blok,
                                    'revisi' => '0',
                                    'kodesegment' => $segment
                                );
                                $noUrut++;
                                
                                if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != '') {
                                    $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                                    $owlPDO->exec($insHead); 
                                    
                                    foreach ($dataRes['detail'] as $row){
                                        $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                        $owlPDO->exec($insDet); 
                                    }
                                    
                                    ## Header and Detail inserted
                                    ## Update Kode Jurnal
                                    $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'");
                                    $owlPDO->exec($updJurnal); 
                                } 
                            }else{
                                ## Jika inter atau intraco 
                                ## Proses data sisi pemilik
                                $kodeJurnal = 'INVM1';
                                
                                ##======================== Begin Nomor Jurnal =============================
                                ## Get Journal Counter
                                $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."'";
                                $tmpKonter = fetchData($str);
                                $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
        
                                ## Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
                                ##======================== End Nomor Jurnal ============================
                                
                                ## No header pemilik
                                $header1pemilik = $nojurnal;    
                                
                                ## Prep Header
                                $dataRes['header'] = array(
                                    'nojurnal' => $nojurnal,
                                    'kodejurnal' => $kodeJurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'tanggalentry' => date('Ymd'),
                                    'posting' => 1,
                                    'totaldebet' => ($rpkembali),
                                    'totalkredit' => (-1 * $rpkembali),
                                    'amountkoreksi' => '0',
                                    'noreferensi' => $notransaksi,
                                    'autojurnal' => '1',
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'revisi' => '0'
                                );
        
                                ## Data Detail
                                $noUrut = 1;
                                $keterangan = "ReturGudang barang " . $namabarang . " " . $jumlah . " " . $satuan;
                                $keterangan = substr($keterangan, 0, 150);
                                
                                ## Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal' => $nojurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'nourut' => $noUrut,
                                    'noakun' => $akunbarang,
                                    'keterangan' => $keterangan,
                                    'jumlah' => ($rpkembali),
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'kodeorg' => substr($gudang, 0, 4),
                                    'kodekegiatan' => '',
                                    'kodeasset' => '',
                                    'kodebarang' => $kodebarang,
                                    'nik' => '',
                                    'kodecustomer' => '',
                                    'kodesupplier' => '',
                                    'noreferensi' => $notransaksi,
                                    'noaruskas' => '',
                                    'kodevhc' => '',
                                    'nodok' => '',
                                    'kodeblok' => '',
                                    'revisi' => '0',
                                    'kodesegment' => $segment
                                );
                                $noUrut++;
        
                                ## Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal' => $nojurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'nourut' => $noUrut,
                                    'noakun' => $inter,
                                    'keterangan' => $keterangan,
                                    'jumlah' => (-1 * $rpkembali),
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'kodeorg' => substr($gudang, 0, 4),
                                    'kodekegiatan' => '',
                                    'kodeasset' => '',
                                    'kodebarang' => $kodebarang,
                                    'nik' => '',
                                    'kodecustomer' => '',
                                    'kodesupplier' => '',
                                    'noreferensi' => $notransaksi,
                                    'noaruskas' => '',
                                    'kodevhc' => '',
                                    'nodok' => '',
                                    'kodeblok' => '',
                                    'revisi' => '0',
                                    'kodesegment' => $segment
                                );
                                
                                if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
                                    $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                                    $owlPDO->exec($insHead); 
                                    
                                    foreach ($dataRes['detail'] as $row){
                                        $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                        $owlPDO->exec($insDet); 
                                    }
                                    
                                    ## Header and Detail inserted
                                    ## Update Kode Jurnal
                                    $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."'");
                                    $owlPDO->exec($updJurnal);
                                }
                                
                                ## Proses data sisi pengguna
                                $kodeJurnal = 'INVM1';
                                
                                ##======================== Begin Nomor Jurnal =============================
                                ## Ambil tanggal terkecil periode pengguna
                                $tanggalsana = '';
                                $stri = "select tanggalmulai from ".$dbname.".setup_periodeakuntansi where kodeorg='".$pengguna."' and tutupbuku='0'";
                                $res=fetchdata($stri);
                                foreach($res as $key=>$val){
                                    $tanggalsana = $val['tanggalmulai'];
                                
                                }
                                if ($tanggalsana == '' or substr($tanggalsana, 0, 7) == (substr(tanggalsystem($tanggal), 0, 4) . "-" . substr(tanggalsystem($tanggal), 4, 2))){
                                    ## Jika periode sama maka biarkan
                                    $tanggalsana = tanggalsystem($tanggal);
                                }else{
                                    ## Rollback header sisi pemilik
                                    $RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $header1pemilik . "'");
                                    $owlPDO->exec($RBDet); 
                                    throw new PDOException("Receivers accounting period not the same as warehouse.");
                                }
                                
                                ## Get Journal Counter
                                $str = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'");
                                $tmpKonter = fetchData($str);
                                $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
        
        
                                $segmentrk=kodesegment($ptpengguna);
                                ## Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-", "", $tanggalsana) . "/" . $pengguna . "/" . $kodeJurnal . "/" . $konter;
                                ##======================== End Nomor Jurnal ============================
                                
                                ## Prep Header
                                ## Ganti header
                                unset($dataRes['header']);    
                                $dataRes['header'] = array(
                                    'nojurnal' => $nojurnal,
                                    'kodejurnal' => $kodeJurnal,
                                    'tanggal' => $tanggalsana,
                                    'tanggalentry' => date('Ymd'),
                                    'posting' => 1,
                                    'totaldebet' => ($rpkembali),
                                    'totalkredit' => (-1 * $rpkembali),
                                    'amountkoreksi' => '0',
                                    'noreferensi' => $notransaksi,
                                    'autojurnal' => '1',
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'revisi' => '0'
                                );
        
                                ## Data Detail
                                $keterangan = "ReturGudang barang " . $namabarang . " " . $jumlah . " " . $satuan . " " . substr($dtarr['tanggal'], 0, 7);
                                $keterangan = substr($keterangan, 0, 150);
                                $noUrut = 1;
                                unset($dataRes['detail']); //ganti detail 
                                # Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal' => $nojurnal,
                                    'tanggal' => $tanggalsana,
                                    'nourut' => $noUrut,
                                    'noakun' => $akunspl,
                                    'keterangan' => $keterangan,
                                    'jumlah' => ($rpkembali),
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'kodeorg' => $pengguna,
                                    'kodekegiatan' => '',
                                    'kodeasset' => '',
                                    'kodebarang' => $kodebarang,
                                    'nik' => '',
                                    'kodecustomer' => '',
                                    'kodesupplier' => '',
                                    'noreferensi' => $notransaksi,
                                    'noaruskas' => '',
                                    'kodevhc' => $kodemesin,
                                    'nodok' => '',
                                    'kodeblok' => $blok,
                                    'revisi' => '0',
                                    'kodesegment' => $segmentrk
                                );
                                $noUrut++;
        
                                ## Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal' => $nojurnal,
                                    'tanggal' => $tanggalsana,
                                    'nourut' => $noUrut,
                                    'noakun' => $akunpekerjaan,
                                    'keterangan' => $keterangan,
                                    'jumlah' => (-1 * $rpkembali),
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'kodeorg' => $pengguna,
                                    'kodekegiatan' => $kodekegiatan,
                                    'kodeasset' => '',
                                    'kodebarang' => $kodebarang,
                                    'nik' => '',
                                    'kodecustomer' => '',
                                    'kodesupplier' => '',
                                    'noreferensi' => $notransaksi,
                                    'noaruskas' => '',
                                    'kodevhc' => $kodemesin,
                                    'nodok' => '',
                                    'kodeblok' => $blok,
                                    'revisi' => '0',
                                    'kodesegment' => $segmentrk
                                );
                                $noUrut++;
                                
                                ## EXECUTE
                                if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
                                    $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                                    $owlPDO->exec($insHead); 
                                    
                                    foreach ($dataRes['detail'] as $row){
                                        $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                        $owlPDO->exec($insDet); 
                                    }
                                    
                                    ## Header and Detail inserted
                                    ## Update Kode Jurnal
                                    $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'");
                                    $owlPDO->exec($updJurnal); 
                                    
                                    ## Berhasil di jurnal
                                    ## Proses gudang
                                    $owlPDO->exec($strupdate); 
                                    
                                    ## Update masterbarangdt
                                    // $owlPDO->exec($updmaster); 
                                    $affected_rows=$owlPDO->exec($updmaster);
                                    if ($affected_rows == 0) {
                                        @$owlPDO->exec($instmaster); 
                                    }
                                    
                                    $owlPDO->exec($updflagststussaldo);       
                                }else{
                                    ## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
                                    ## Proses gudang
                                    $owlPDO->exec($strupdate); 
                                    
                                    ## Update masterbarangdt
                                    // $owlPDO->exec($updmaster); 
                                    $affected_rows=$owlPDO->exec($updmaster);
                                    if ($affected_rows == 0){
                                        @$owlPDO->exec($instmaster); 
                                    }
                                        
                                    $owlPDO->exec($updflagststussaldo); 
                                }
                            }
                        }
                        
                        $owlPDO->commit();
                    }catch(PDOException $e){
                        $owlPDO->rollback();
                        echo "Error, " . addslashes($e->getMessage());
                    }
                }
                ####################################
                #### End Retur Barang ke Gudang ####
                ####################################
                
                
                ########################################
                #### Begin Penerimaan Mutasi Gudang ####
                ########################################
                if ($tipetransaksi == '3'){
                    try{
                        $owlPDO->beginTransaction();
                        
                        ## Ambil harga satuan dan saldo
                        $hargarata = 0;
                        $saldoakhirqty = 0;
                        $nilaisaldoakhir = 0;
                        $qtymasukxharga = 0;
                        $qtymasuk = 0;
                        $nilaitotal = $jumlah * $hargasatuan;
                        
                        $str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtymasuk,qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                        $res=fetchdata($str);
                        $numrows=count($res);
                        if ($numrows < 1){
                            ## Jika belum ada penerimaan sebelumnya
                            $newhargarata = $hargasatuan;
                            $newqtymasuk = $jumlah;
                            $newqtymasukxharga = $nilaitotal;
                            $newsaldoakhirqty = $jumlah;
                            $newnilaisaldoakhir = $nilaitotal;
                            
                            $strupdate = "insert into ".$dbname.".log_5saldobulanan (kodeorg, kodebarang, saldoakhirqty, hargarata, lastuser, periode, nilaisaldoakhir, kodegudang, qtymasuk, qtykeluar, qtymasukxharga, qtykeluarxharga, saldoawalqty, hargaratasaldoawal, nilaisaldoawal) values ('".$kodept."','".$kodebarang."','".$newqtymasuk."','".$newhargarata."','".$user."','".$periode."','".$newqtymasukxharga."','".$gudang."','".$newsaldoakhirqty."','0','".$newnilaisaldoakhir."','0','0','0','0')";
                        }else{
                            foreach($res as $key=>$val){
                                $hargarata = $val['hargarata'];
                                $saldoakhirqty = $val['saldoakhirqty'];
                                $nilaisaldoakhir = $val['nilaisaldoakhir'];
                                $qtymasukxharga = $val['qtymasukxharga'];
                                $qtymasuk = $val['qtymasuk'];
                            }
                            
                            $newsaldoakhirqty    = $saldoakhirqty + $jumlah;
                            @$newhargarata       = ($nilaitotal + $nilaisaldoakhir) / ($newsaldoakhirqty);
                            #$newnilaisaldoakhir = $newhargarata * $newsaldoakhirqty;
                            $newnilaisaldoakhir  = $nilaitotal + $nilaisaldoakhir;
                            $newqtymasuk         = $qtymasuk + $jumlah;
                            #$newqtymasukxharga  = $newqtymasuk * $hargarata; 
                            $newqtymasukxharga   = $qtymasukxharga + $nilaitotal;
                            
                            ## Menggunakan harga rata-rata pada saat itu, bukan harga pada saat dikeluarkan 
                            
                            if ($newhargarata == 0 or $newhargarata == ''){
                                throw new PDOException("Average price cannot be formed on " . $notransaksi . " material code :" . $kodebarang."\n\nHargarata tidak dapat dibentuk pada ".$notransaksi." kodebarang :".$kodebarang);
                            }else{
                                $strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."', hargarata='".$newhargarata."', nilaisaldoakhir='".$newnilaisaldoakhir."', lastuser='".$user."', qtymasuk='".$newqtymasuk."', qtymasukxharga='".$newqtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                            }
                        }
                        
                        if ($newhargarata == '0') {
                            throw new PDOException("Harga rata-rata tidak dapat dibentuk.");
                        }
                        
                        //prepare rollback penerimaan
                        $strrollback = "update " . $dbname . ".log_5saldobulanan set 
                        saldoakhirqty=" . $saldoakhirqty . ",nilaisaldoakhir=" . $nilaisaldoakhir . ",
                        lastuser=" . $user . ",qtymasuk=" . $qtymasuk . ",qtymasukxharga=" . $qtymasukxharga . "
                        where periode='" . $periode . "' and kodegudang='" . $gudang . "' and kodebarang='" . $kodebarang . "' and kodeorg='" . $kodept . "'";
        
                        ## Prepare update masterbarangdt
                        $instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values ('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','0','".$newhargarata."','0','0','".$user."','".$gudang."')";
                        
                        $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastin='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                        
                        ## Periksa apakah dari satu PT
                        ## Ini sebenarnya pemilik
                        $pengguna = substr($gudang, 0, 4);
        
                        $ptpengguna = '';
                        $str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                        $res=fetchdata($str);
                        $ptpengguna = $res[0]['induk'];
                        
                        ## Ini yang pengguna
                        $ptGudang = '';
                        $str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudangx, 0, 4)."'";
                        $res=fetchdata($str);
                        $ptGudang = $res[0]['induk'];
                        
                        ## Jika pt tidak sama maka pakai akun interco
                        $akunspl = '';
                        if ($ptGudang != $ptpengguna){
                            ## Ambil akun interco
                            $akunspl = '';
                            $str = "select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx, 0, 4)."' and jenis='inter'";
                            $res=fetchdata($str);
                            $akunspl = $res[0]['akunhutang'];
                            
                            if ($akunspl == ''){
                                throw new PDOException("Account intraco or interco not available for " . substr($gudangx, 0, 4)."\n\nAkun intraco  atau interco belum ada untuk unit ".substr($gudangx,0,4));
                            }
                        }else if ($pengguna != substr($gudangx, 0, 4)){
                            ## Jika satu pt beda kebun
                            ## Ambil akun intraco
                            $akunspl = '';
                            $str = "select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx, 0, 4)."' and jenis='intra'";
                            $res=fetchdata($str);
                            $akunspl = $res[0]['akunhutang'];
                            
                            if ($akunspl == ''){
                                throw new PDOException("Account intraco / interco not available for " . substr($gudangx, 0, 4)."\n\nAkun intraco  atau interco belum ada untuk unit ".substr($gudangx,0,4));
                            }
                        }
                        
                        ## Ambil noakun barang
                        $akunbarang = '';
                        $klbarang = substr($kodebarang, 0, 3);
                        $str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=fetchdata($str);
                        $akunbarang = $res[0]['noakun'];
                        
                        if ($akunbarang == ''){
                            throw new PDOException("Account for material not available for " . $notransaksi."\n\nNoakun barang belum ada untuk transaksi".$notransaksi);
                        }else{
                          
                            ## Proses data sisi pengguna
                            $kodeJurnal = 'INVM1';
                            
                            ##======================== Begin Nomor Jurnal =============================
                            ## Get Journal Counter
                            $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'";
                            $tmpKonter = fetchData($str);
                            $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
        
                            ## Transform No Jurnal dari No Transaksi
                            $nojurnal = tanggalsystem($tanggal) . "/" . $pengguna . "/" . $kodeJurnal . "/" . $konter;
                            ##======================== End Nomor Jurnal ============================
                            
                            ## Prep Header
                            ## Ganti header
                            unset($dataRes['header']); 
                            
                            $dataRes['header'] = array(
                                'nojurnal' => $nojurnal,
                                'kodejurnal' => $kodeJurnal,
                                'tanggal' => tanggalsystem($tanggal),
                                'tanggalentry' => date('Ymd'),
                                'posting' => 1,
                                'totaldebet' => $nilaitotal,
                                'totalkredit' => (-1 * $nilaitotal),
                                'amountkoreksi' => '0',
                                'noreferensi' => $notransaksi,
                                'autojurnal' => '1',
                                'matauang' => 'IDR',
                                'kurs' => '1',
                                'revisi' => '0'
                            );
        
                            ## Data Detail
                            $keterangan = "Terima Mutasi barang " . $namabarang . " " . $jumlah . " " . $satuan . " " . substr($dtarr['tanggal'], 0, 7);
                            $keterangan = substr($keterangan, 0, 150);
                            $noUrut = 1;
                            unset($dataRes['detail']); //ganti detail 
                            ## Debet
                            $dataRes['detail'][] = array(
                                'nojurnal' => $nojurnal,
                                'tanggal' => tanggalsystem($tanggal),
                                'nourut' => $noUrut,
                                'noakun' => $akunbarang,
                                'keterangan' => $keterangan,
                                'jumlah' => $nilaitotal,
                                'matauang' => 'IDR',
                                'kurs' => '1',
                                'kodeorg' => $pengguna,
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => $kodebarang,
                                'nik' => '',
                                'kodecustomer' => '',
                                'kodesupplier' => '',
                                'noreferensi' => $notransaksi,
                                'noaruskas' => '',
                                'kodevhc' => '',
                                'nodok' => '',
                                'kodeblok' => '',
                                'revisi' => '0',
                                'kodesegment' => $segment
                            );
                            $noUrut++;
        
                            # Kredit
                            $dataRes['detail'][] = array(
                                'nojurnal' => $nojurnal,
                                'tanggal' => tanggalsystem($tanggal),
                                'nourut' => $noUrut,
                                'noakun' => $akunspl,
                                'keterangan' => $keterangan,
                                'jumlah' => (-1 * $nilaitotal),
                                'matauang' => 'IDR',
                                'kurs' => '1',
                                'kodeorg' => $pengguna,
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => $kodebarang,
                                'nik' => '',
                                'kodecustomer' => '',
                                'kodesupplier' => '',
                                'noreferensi' => $notransaksi,
                                'noaruskas' => '',
                                'kodevhc' => '',
                                'nodok' => '',
                                'kodeblok' => '',
                                'revisi' => '0',
                                'kodesegment' => $segment
                            );
                            $noUrut++;
                            
                            
                            if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != '' and ( substr($pengguna, 0, 4) != substr($gudangx, 0, 4))){
                                ## Hanya barang stok yang dijurnal dan mutasi keluar kebun
                                $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                                $owlPDO->exec($insHead); 
                                
                                foreach ($dataRes['detail'] as $row){
                                    $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                    $owlPDO->exec($insDet); 
                                }
                                
                                ## Header and Detail inserted
                                ## Update Kode Jurnal
                                $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'");
                                $owlPDO->exec($updJurnal);    
                            }
                        }
                        
                        $owlPDO->commit();
                    }catch(PDOException $e){
                        $owlPDO->rollback();
                        echo "Error, " . addslashes($e->getMessage());
                    }
                }
                ######################################
                #### End Penerimaan Mutasi Gudang ####
                ######################################
                
                
                
                #########################################
                #### Begin Pengeluaran Mutasi Gudang ####
                #########################################
                if ($tipetransaksi == '7'){
                    try{
                        $owlPDO->beginTransaction();
                        
                        ## Ambil harga satuan dan saldo
                        $hargarata = 0;
                        $saldoakhirqty = 0;
                        $nilaisaldoakhir = 0;
                        $qtykeluarxharga = 0;
                        $qtykeluar = 0;
                        $str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                        $res=fetchdata($str);
                        foreach($res as $key=>$val){
                            $hargarata = $val['hargarata'];
                            $saldoakhirqty = $val['saldoakhirqty'];
                            $nilaisaldoakhir = $val['nilaisaldoakhir'];
                            $qtykeluarxharga = $val['qtykeluarxharga'];
                            $qtykeluar = $val['qtykeluar'];
                        }
                        
                        if ($hargarata == 0 || $hargarata == ''){
                            throw new PDOException("Average price not available\n\nHarga rata-rata belum ada");
                        }
                        
                        $newsaldoakhirqty    = $saldoakhirqty - $jumlah;
                        $newhargarata        = $hargarata;
                        #$newnilaisaldoakhir = $newhargarata * $newsaldoakhirqty;
                        $newnilaisaldoakhir  = $nilaisaldoakhir - ($jumlah * $hargarata);
                        $newqtykeluar        = $qtykeluar + $jumlah;
                        #$newqtykeluarxharga = $newqtykeluar * $newhargarata;
                        $newqtykeluarxharga  = $qtykeluarxharga + ($jumlah * $hargarata);
                        
                        if ($newsaldoakhirqty < 0){
                            throw new PDOException("Amount not sufficient\n\nSaldo tidak cukup");
                        }
                        
                        $strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."',nilaisaldoakhir='".$newnilaisaldoakhir."',lastuser='".$user."',qtykeluar='".$newqtykeluar."',qtykeluarxharga='".$newqtykeluarxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                        
                        //prepare update masterbarangdt
                        $instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout,  stockbataspesan, stockminimum, lastuser,kodegudang) values ('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','0','".$newhargarata."','0','0','".$user."','".$gudang."')";
                        
                        $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                        
                        
                        ## Periksa apakah dari satu PT
                        ## Gudang tujuan
                        $pengguna = substr($gudangx, 0, 4);
        
                        $ptpengguna = '';
                        $str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                        $res=fetchdata($str);
                        $ptpengguna = $res[0]['induk'];
                        
                        $str = "select akunpiutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
                        $res=fetchdata($str);
                        $intraco = '';
                        $interco = '';
                        /*
                        if($res[0]['jenis'] == 'intra'){
                            $intraco = $res[0]['akunpiutang'];
                        }else{
                            $interco = $res[0]['akunpiutang'];
                        }
                        */
                        $str = "select akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
                        $res=fetchdata($str);
                        foreach($res as $key=>$val){
                            if ($val['jenis'] == 'intra'){
                                $intraco = $val['akunhutang'];
                            }else{
                                $interco = $val['akunhutang'];
                            }
                        }
        
                        if ($intraco=='' || $interco=='') {
                            //exit(" Error: Account intraco or interco not available for ".$pengguna;
                            throw new PDOException("Account intraco or interco not available for ".$pengguna);
                        }  
                        
                        
                        $ptGudang = '';
                        $str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang, 0, 4)."'";
                        $res=fetchdata($str);
                        $ptGudang = $res[0]['induk'];
                        
                        ## Jika pt tidak sama maka pakai akun interco
                        $akunspl = '';
                        if($ptGudang != $ptpengguna){
                            ## Ambil akun interco
                            $str = "select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang, 0, 4)."' and jenis='inter'";
                            $res=fetchdata($str);
                            $akunspl = '';
                            $akunspl = $res[0]['akunhutang'];
                            $inter = $interco;
                            
                            if ($akunspl == ''){
                                throw new PDOException("Account intraco or interco not available for " . $pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
                            }
                        }else if ($pengguna != substr($gudang, 0, 4)){
                            ## Jika satu pt beda kebun
                            ## Ambil akun intraco
                            $str = "select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang, 0, 4)."' and jenis='intra'";
                            $res=fetchdata($str);
                            $akunspl = '';
                            $akunspl = $res[0]['akunhutang'];
                            
                            $inter = $intraco;
                            if ($akunspl == ''){
                                throw new PDOException("Account intraco or interco not available for " . $pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
                            }
                        }
        
                        ## Ambil noakun barang
                        $klbarang = substr($kodebarang, 0, 3);
                        $str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=fetchdata($str);
                        $akunbarang = '';
                        $akunbarang = $res[0]['noakun'];
                        
                        if ($akunbarang == ''){
                            throw new PDOException("Account for material not available for " . $notransaksi);
                        }else{
                           
                            ## Mutasi antar gudang internal tidak menggunakan jurnal
                            if ($pengguna == substr($gudang, 0, 4)){
                               
                            } else {
                                ## Jika inter atau intraco 
                                ## Proses data sisi pemilik
                                
                                $kodeJurnal = 'INVK1';
                                
                                ##======================== Begin Nomor Jurnal =============================
                                ## Get Journal Counter
                                $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."'";
                                $tmpKonter = fetchData($str);
                                $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
        
                                ## Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
                                ##======================== End Nomor Jurnal ============================
                                
                                ## No header pemilik
                                $header1pemilik = $nojurnal;    
                                # Prep Header
                                $dataRes['header'] = array(
                                    'nojurnal' => $nojurnal,
                                    'kodejurnal' => $kodeJurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'tanggalentry' => date('Ymd'),
                                    'posting' => 1,
                                    'totaldebet' => ($jumlah * $hargarata),
                                    'totalkredit' => (-1 * $jumlah * $hargarata),
                                    'amountkoreksi' => '0',
                                    'noreferensi' => $notransaksi,
                                    'autojurnal' => '1',
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'revisi' => '0'
                                );
        
                                ## Data Detail
                                $noUrut = 1;
                                $keterangan = "Mutasi barang " . $namabarang . " " . $jumlah . " " . $satuan;
                                $keterangan = substr($keterangan, 0, 150);
                                ## Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal' => $nojurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'nourut' => $noUrut,
                                    'noakun' => $inter,
                                    'keterangan' => $keterangan,
                                    'jumlah' => ($jumlah * $hargarata),
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'kodeorg' => substr($gudang, 0, 4),
                                    'kodekegiatan' => '',
                                    'kodeasset' => '',
                                    'kodebarang' => $kodebarang,
                                    'nik' => '',
                                    'kodecustomer' => '',
                                    'kodesupplier' => '',
                                    'noreferensi' => $notransaksi,
                                    'noaruskas' => '',
                                    'kodevhc' => '',
                                    'nodok' => '',
                                    'kodeblok' => '',
                                    'revisi' => '0',
                                    'kodesegment' => $segment
                                );
                                $noUrut++;
        
                                ## Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal' => $nojurnal,
                                    'tanggal' => tanggalsystem($tanggal),
                                    'nourut' => $noUrut,
                                    'noakun' => $akunbarang,
                                    'keterangan' => $keterangan,
                                    'jumlah' => (-1 * $jumlah * $hargarata),
                                    'matauang' => 'IDR',
                                    'kurs' => '1',
                                    'kodeorg' => substr($gudang, 0, 4),
                                    'kodekegiatan' => '',
                                    'kodeasset' => '',
                                    'kodebarang' => $kodebarang,
                                    'nik' => '',
                                    'kodecustomer' => '',
                                    'kodesupplier' => '',
                                    'noreferensi' => $notransaksi,
                                    'noaruskas' => '',
                                    'kodevhc' => '',
                                    'nodok' => '',
                                    'kodeblok' => '',
                                    'revisi' => '0',
                                    'kodesegment' => $segment
                                );
        
                                if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
                                    $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                                    $owlPDO->exec($insHead); 
                                    
                                    foreach ($dataRes['detail'] as $row){
                                        $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                        $owlPDO->exec($insDet); 
                                    }
                                    
                                    ## Header and Detail inserted
                                    ## Update Kode Jurnal
                                    $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."'");
                                    $owlPDO->exec($updJurnal);
                                }
                            }
                        }
                        
                        $owlPDO->commit();
                    }catch(PDOException $e){
                        $owlPDO->rollback();
                        echo "Error, " . addslashes($e->getMessage());
                    }
                }
                #######################################
                #### End Pengeluaran Mutasi Gudang ####
                #######################################
                
                
                
                
                
                
                ###################################################
                #### Begin Pengeluaran/Pemakaian Barang Gudang ####
                ###################################################
                if ($tipetransaksi == '5'){
                    if($bkmjrn==1){

                    }else{
                        try{
                            $owlPDO->beginTransaction();
                            $kelkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok');
                            if($kelkeg[$kodekegiatan]=='TRK' and $kodemesin==''){
                                throw new PDOException("Jika kegiatan traksi maka kendaraan tidak boleh kosong");
                            }
                            ## Get Kelompok kegiatan
                            $str = "select kodekegiatan from ".$dbname.".log_transaksidt where notransaksi = '".$notransaksi."' LIMIT 1";
                            $res=fetchdata($str);
                            $vKdKegiatan = $res[0]['kodekegiatan'];
                            
                            $str = "select kelompok from ".$dbname.".setup_kegiatan where kodekegiatan = '".$vKdKegiatan."'";
                            $res=fetchdata($str);
                            $vKlmpKegiatan = $res[0]['kelompok'];			
                            
                            ## Periksa apakah dari satu PT
                            $pengguna = substr($dtarr['untukunit'], 0, 4);
                            
                            $ptpengguna = '';
                            $str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                            $res=fetchdata($str);
                            $ptpengguna = $res[0]['induk'];
                            
            
                            $intraco = '';
                            $interco = '';
                            /*
                            $str = "select akunpiutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
                            $res=fetchdata($str);
                            if ($res[0]['jenis'] == 'intra'){
                                $intraco = $res[0]['akunpiutang'];
                            }else{
                                $interco = $res[0]['akunpiutang'];
                            }
                            */
                            $str = "select akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
                            
                            $res=fetchdata($str);
                           
                            foreach($res as $key=>$val){
                                if ($val['jenis'] == 'intra'){
                                    $intraco = $val['akunhutang'];
                                }else{
                                    $interco = $val['akunhutang'];
                                }
                            }
            
                            if ($intraco=='' || $interco=='') {
                                throw new PDOException("Account intraco or interco not available for " . $pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
                            }  
                            
                            $ptGudang = '';
                            $str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang, 0, 4)."'";
                            // exit('warning:masuuukkkk'.$gudang);
                            $res=fetchdata($str);
                            $ptGudang = $res[0]['induk'];
                            
                            ## Jika pt tidak sama maka pakai akun interco
                            $akunspl = '';
                            if ($ptGudang != $ptpengguna){
                                ## Ambil akun interco
                                $akunspl = '';
                                $str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='".substr($gudang, 0, 4)."' and jenis='inter'";
                                $res=fetchdata($str);
                                $akunspl = $res[0]['akunhutang'];
                                
                                $inter = $interco;
                                if ($akunspl == ''){
                                    throw new PDOException("Account intraco or interco not available for " . $pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
                                }
                            }else if ($pengguna != substr($gudang, 0, 4)) { 
                                ## Jika satu pt beda kebun
                                ## Ambil akun intraco
                                $akunspl = '';
                                $str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='".substr($gudang, 0, 4)."' and jenis='intra'";
                                $res=fetchdata($str);
                                $akunspl = $res[0]['akunhutang'];
                                
                                $inter = $intraco;
                                if ($akunspl == ''){
                                    throw new PDOException("Account intraco or interco not available for ".$pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
                                }
                            }
            
            
                            ## Ambil akun pekerjaan atau kendaraan atau ab
                            ## Periksa ke table setup blok
                            $statustm = '';
                            $str = "select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok."'";
                            $res=fetchdata($str);
                            $statustm = $res[0]['statusblok'];
                            
                            $akunpekerjaan = '';
                            $str = "select noakun from ".$dbname.".setup_kegiatan where kodekegiatan='".$kodekegiatan."'";
                            $res=fetchdata($str);
                            $akunpekerjaan = $res[0]['noakun'];
                            
                            ## Untuk project aktiva dalam konstruksi maka akun diambil dari kolom kodekegiatan
                            $kodeasset = '';
                            if (substr($blok, 0, 2) == 'AK' or substr($blok, 0, 2) == 'PB'){
                                $akunpekerjaan = substr($kodekegiatan, 0, 7);
                                $kodeasset = $blok;
            
                                ## Pemindahan kodeblok ke kode asset
                                $blok = "";
                            }
                            
                            ## Jika akun kegiatan tidak ada maka exit
                            if ($akunpekerjaan == ''){
                                throw new PDOException("Account not available for activity " . $kodekegiatan."\n\nAkun pekerjaan belum ada untuk kegiatan ".$kodekegiatan);
                            }
            
                            ## Ambil noakun barang
                            $akunbarang = '';
                            $klbarang = substr($kodebarang, 0, 3);
                            $str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                            $res=fetchdata($str);
                            $akunbarang = $res[0]['noakun'];
                            
                            if (($akunbarang == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
                                throw new PDOException("Account for material not available for " . $notransaksi."\n\nNoakun barang belum ada untuk transaksi".$notransaksi);
                            }else{
                                 
                                
                                if(substr($blok,0,4)=='K001'){
                                    $kdsup=$blok;
                                    $blok='';
                                }else{
                                    $blok=$blok;
                                    $kdsup='';
                                }
                                
                                ## Penggunaan internal$ptGudang$ptpengguna
                                if ($pengguna == substr($gudang, 0, 4)){
                                    $kodeJurnal = 'INVK1';
                                    
                                    ##======================== Begin Nomor Jurnal =============================
                                    ## Get Journal Counter
                                    $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'";
                                    $tmpKonter = fetchData($str);
                                    $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
            
                                    ## Transform No Jurnal dari No Transaksi
                                    $nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
                                    ##======================== End Nomor Jurnal ============================
                                    
                                    ## Prep Header
                                    $dataRes['header'] = array(
                                        'nojurnal' => $nojurnal,
                                        'kodejurnal' => $kodeJurnal,
                                        'tanggal' => tanggalsystem($tanggal),
                                        'tanggalentry' => date('Ymd'),
                                        'posting' => 1,
                                        'totaldebet' => ($jumlah * $hargarata),
                                        'totalkredit' => (-1 * $jumlah * $hargarata),
                                        'amountkoreksi' => '0',
                                        'noreferensi' => $notransaksi,
                                        'autojurnal' => '1',
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'revisi' => '0'
                                    );
                                
                                    
                                    ## Data Detail
                                    $noUrut = 1;
                                    $keterangan = "Pemakaian barang " . $namabarang . " " . $jumlah . " " . $satuan;
                                    ## Debet
                                    $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => tanggalsystem($tanggal),
                                        'nourut' => $noUrut,
                                        'noakun' => $akunpekerjaan,
                                        'keterangan' => $keterangan,
                                        'jumlah' => ($jumlah * $hargarata),
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => substr($gudang, 0, 4),
                                        'kodekegiatan' => $kodekegiatan,
                                        'kodeasset' => $kodeasset,
                                        'kodebarang' => $kodebarang,
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => $kdsup,
                                        'noreferensi' => $notransaksi,
                                        'noaruskas' => '',
                                        'kodevhc' => $kodemesin,
                                        'nodok'=>($vKlmpKegiatan=='SPL' ? $blok : ''),
                                        'kodeblok'=>($vKlmpKegiatan=='SPL' ? '' : $blok),
                                        'revisi' => '0',
                                        'kodesegment' => $segment
                                    );
                                    $noUrut++;
            
                                    ## Kredit
                                    $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => tanggalsystem($tanggal),
                                        'nourut' => $noUrut,
                                        'noakun' => $akunbarang,
                                        'keterangan' => $keterangan,
                                        'jumlah' => (-1 * $jumlah * $hargarata),
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => substr($gudang, 0, 4),
                                        'kodekegiatan' => $kodekegiatan,
                                        'kodeasset' => $kodeasset,
                                        'kodebarang' => $kodebarang,
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => $kdsup,
                                        'noreferensi' => $notransaksi,
                                        'noaruskas' => '',
                                        'kodevhc' => $kodemesin,
                                        'nodok'=>($vKlmpKegiatan=='SPL' ? $blok : ''),
                                        'kodeblok'=>($vKlmpKegiatan=='SPL' ? '' : $blok),
                                        'revisi' => '0',
                                        'kodesegment' => $segment
                                    );
                                    $noUrut++;
                                    
                                    if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
                                        $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                                        $owlPDO->exec($insHead); 
                                        
                                        foreach ($dataRes['detail'] as $row){
                                            $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                            $owlPDO->exec($insDet); 
                                        }
                                        
                                        ## Header and Detail inserted
                                        ## Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'");
                                        $owlPDO->exec($updJurnal);                                       
                                    }
                                }else{
                                    ## Jika inter atau intraco 
                                    ## Proses data sisi pemilik
                                    $kodeJurnal = 'INVK1';
                                    
                                    ##======================== Begin Nomor Jurnal =============================
                                    ## Get Journal Counter
                                    $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."'";
                                    $tmpKonter = fetchData($str);
                                    $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
            
                                    ## Transform No Jurnal dari No Transaksi
                                    $nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
                                    ##======================== End Nomor Jurnal ============================
                                    
                                    ## No header pemilik
                                    $header1pemilik = $nojurnal;    
                                    
                                    ## Prep Header
                                    $dataRes['header'] = array(
                                        'nojurnal' => $nojurnal,
                                        'kodejurnal' => $kodeJurnal,
                                        'tanggal' => tanggalsystem($tanggal),
                                        'tanggalentry' => date('Ymd'),
                                        'posting' => 1,
                                        'totaldebet' => ($jumlah * $hargarata),
                                        'totalkredit' => (-1 * $jumlah * $hargarata),
                                        'amountkoreksi' => '0',
                                        'noreferensi' => $notransaksi,
                                        'autojurnal' => '1',
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'revisi' => '0'
                                    );
            
                                    ## Data Detail
                                    $noUrut = 1;
                                    $keterangan = "Pemakaian barang " . $namabarang . " " . $jumlah . " " . $satuan;
                                    $keterangan = substr($keterangan, 0, 150);
                                    ## Debet
                                    $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => tanggalsystem($tanggal),
                                        'nourut' => $noUrut,
                                        'noakun' => $inter,
                                        'keterangan' => $keterangan,
                                        'jumlah' => ($jumlah * $hargarata),
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => substr($gudang, 0, 4),
                                        'kodekegiatan' => '',
                                        'kodeasset' => '',
                                        'kodebarang' => $kodebarang,
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => '',
                                        'noreferensi' => $notransaksi,
                                        'noaruskas' => '',
                                        'kodevhc' => '',
                                        'nodok' => '',
                                        'kodeblok' => '',
                                        'revisi' => '0',
                                        'kodesegment' => $segment
                                    );
                                    $noUrut++;
            
                                    ## Kredit
                                    $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => tanggalsystem($tanggal),
                                        'nourut' => $noUrut,
                                        'noakun' => $akunbarang,
                                        'keterangan' => $keterangan,
                                        'jumlah' => (-1 * $jumlah * $hargarata),
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => substr($gudang, 0, 4),
                                        'kodekegiatan' => '',
                                        'kodeasset' => '',
                                        'kodebarang' => $kodebarang,
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => '',
                                        'noreferensi' => $notransaksi,
                                        'noaruskas' => '',
                                        'kodevhc' => '',
                                        'nodok' => '',
                                        'kodeblok' => '',
                                        'revisi' => '0',
                                        'kodesegment' => $segment
                                    );
                                    
                                    if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
                                        $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                                        $owlPDO->exec($insHead); 
                                        
                                        foreach ($dataRes['detail'] as $row){
                                            $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                            $owlPDO->exec($insDet); 
                                        }
                                        
                                        ## Header and Detail inserted
                                        ## Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."'");
                                        $owlPDO->exec($updJurnal); 
                                    }
            
                                    
                                    ## Proses data sisi pengguna
                                    $kodeJurnal = 'INVK1';
                                    
                                    ##======================== Begin Nomor Jurnal =============================
                                    ## Ambil tanggal terkecil periode pengguna
                                    $tanggalsana = '';
                                    $str = "select tanggalmulai from ".$dbname.".setup_periodeakuntansi where kodeorg='".$pengguna."' and tutupbuku='0'";
                                    $res=fetchdata($str);
                                    foreach($res as $key=>$val){
                                        $tanggalsana = $val['tanggalmulai'];
                                    }
                                    
                                    if ($tanggalsana == '' or substr($tanggalsana, 0, 7) == (substr(tanggalsystem($tanggal), 0, 4) . "-" . substr(tanggalsystem($tanggal), 4, 2))){
                                        ## Jika periode sama maka biarkan
                                        $tanggalsana = tanggalsystem($tanggal);
                                    }else{
                                        ## Rollback header sisi pemilik
                                        $RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='".$header1pemilik."'");
                                        $owlPDO->exec($RBDet); 
                                        throw new PDOException("Receivers accounting period not the same as warehouse");
                                    }
                                    
                                    ## Get Journal Counter
                                    $str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'";
                                    $tmpKonter = fetchData($str);
                                    $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
            
            
                                    $segmentrk=kodesegment($ptpengguna);
                                    ## Transform No Jurnal dari No Transaksi
                                    $nojurnal = str_replace("-", "", $tanggalsana) . "/" . $pengguna . "/" . $kodeJurnal . "/" . $konter;
                                    ##======================== End Nomor Jurnal ============================
                                    
                                    ## Prep Header
                                    ## Ganti header    
                                    unset($dataRes['header']);
                                    $dataRes['header'] = array(
                                        'nojurnal' => $nojurnal,
                                        'kodejurnal' => $kodeJurnal,
                                        'tanggal' => $tanggalsana,
                                        'tanggalentry' => date('Ymd'),
                                        'posting' => 1,
                                        'totaldebet' => ($jumlah * $hargarata),
                                        'totalkredit' => (-1 * $jumlah * $hargarata),
                                        'amountkoreksi' => '0',
                                        'noreferensi' => $notransaksi,
                                        'autojurnal' => '1',
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'revisi' => '0'
                                    );
            
                                    ## Data Detail
                                    $keterangan = "Pemakaian barang " . $namabarang . " " . $jumlah . " " . $satuan . " " . substr($dtarr['tanggal'], 0, 7);
                                    $keterangan = substr($keterangan, 0, 150);
                                    $noUrut = 1;
                                    unset($dataRes['detail']); //ganti detail 
                                    
                                    ## Debet
                                    $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => $tanggalsana,
                                        'nourut' => $noUrut,
                                        'noakun' => $akunpekerjaan,
                                        'keterangan' => $keterangan,
                                        'jumlah' => ($jumlah * $hargarata),
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => $pengguna,
                                        'kodekegiatan' => $kodekegiatan,
                                        'kodeasset' => $kodeasset,
                                        'kodebarang' => $kodebarang,
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => $kdsup,
                                        'noreferensi' => $notransaksi,
                                        'noaruskas' => '',
                                        'kodevhc' => $kodemesin,
                                        'nodok'=>($vKlmpKegiatan=='SPL' ? $blok : ''),
                                        'kodeblok'=>($vKlmpKegiatan=='SPL' ? '' : $blok),
                                        'revisi' => '0',
                                        'kodesegment' => $segmentrk
                                    );
                                    $noUrut++;
            
                                    ## Kredit
                                    $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => $tanggalsana,
                                        'nourut' => $noUrut,
                                        'noakun' => $akunspl,
                                        'keterangan' => $keterangan,
                                        'jumlah' => (-1 * $jumlah * $hargarata),
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => $pengguna,
                                        'kodekegiatan' => '',
                                        'kodeasset' => '',
                                        'kodebarang' => $kodebarang,
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => $kdsup,
                                        'noreferensi' => $notransaksi,
                                        'noaruskas' => '',
                                        'kodevhc' => $kodemesin,
                                        'nodok'=>($vKlmpKegiatan=='SPL' ? $blok : ''),
                                        'kodeblok'=>($vKlmpKegiatan=='SPL' ? '' : $blok),
                                        'revisi' => '0',
                                        'kodesegment' => $segmentrk
                                    );
                                    $noUrut++;
                                    
                                    ## EXECUTE                      
                                    if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != '') {
                                        $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                                        $owlPDO->exec($insHead); 
                                        
                                        foreach ($dataRes['detail'] as $row){
                                            $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                                            $owlPDO->exec($insDet); 
                                        }
                                        
                                        ## Header and Detail inserted
                                        ## Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'");
                                        $owlPDO->exec($updJurnal); 
                                        
                                               
                                    } 
                                }
                            }
                            
                            $owlPDO->commit();
                        }catch(PDOException $e){
                            $owlPDO->rollback();
                            echo "Error, " . addslashes($e->getMessage());
                        }
                    }
                   
                }
                #################################################
                #### End Pengeluaran/Pemakaian Barang Gudang ####
                #################################################
            
        }
        ## end of if(isTransactionPeriod()) line: 7 ##
        else{
            echo " Error: Transaction Period missing";
            exit();
        }
        
        
        
        #########################################
        #### BEGIN PENERIMAAN DARI PABRIKASI ####
        #########################################
        if($tipetransaksi == '0'){
            try{
                $owlPDO->beginTransaction();
                
                ## Periksa harga satuan
                if(intval($hargasatuan) == 0 or $kdpabrikasi == ''){
                    throw new PDOException("Price/Kode Fabrication not found.");
                }
                
                ## Generate saldo updater
                ## Ambil saldo saat ini 
                $nilaitotal = $jumlah * $hargasatuan;
                $cursaldo = 0;
                $nilaisaldo = 0;
                $qtymasuk = 0;
                $qtymasukxharga = 0;
                $saldoakhirqty = 0;
                $nilaisaldoakhir = 0;
                $hargarata = 0;
                $str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtymasuk,qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
                $res=fetchdata($str);
                $numrows=count($res);
                if ($numrows < 1){
                    ## Jika belum ada penerimaan sebelumnya
                    $newhargarata = $hargasatuan;
                    $newqtymasuk = $jumlah;
                    $newqtymasukxharga = $nilaitotal;
                    $newsaldoakhirqty = $jumlah;
                    $newnilaisaldoakhir = $nilaitotal;
                    $strupdate = "insert into " .$dbname.".log_5saldobulanan (kodeorg, kodebarang, saldoakhirqty, hargarata, lastuser,periode, nilaisaldoakhir, kodegudang, qtymasuk, qtykeluar, qtymasukxharga,qtykeluarxharga, saldoawalqty, hargaratasaldoawal, nilaisaldoawal) 
                    values('".$kodept."','".$kodebarang."',".$newqtymasuk.",".$newhargarata.",".$user.",'".$periode."',".$newqtymasukxharga.",'".$gudang."',".$newsaldoakhirqty.",0,".$newnilaisaldoakhir.",0,0,0,0)";
                }else{
                    ## Bentuk harga baru
                    foreach ($res as $key=>$val){
                        $cursaldo = $val['saldoakhirqty'];
                        $nilaisaldo = $val['nilaisaldoakhir'];
                        $qtymasuk = $val['qtymasuk'];
                        $qtymasukxharga = $val['qtymasukxharga'];
                        $hargarata = $val['hargarata'];
                    }
                    
                    @$newhargarata = ($nilaitotal + $nilaisaldo) / ($jumlah + $cursaldo);
                    $newqtymasuk = $qtymasuk + $jumlah;
                    @$newqtymasukxharga = $qtymasukxharga + $nilaitotal;
                    $newsaldoakhirqty = $jumlah + $cursaldo;
                    #$newnilaisaldoakhir = $newhargarata * $newsaldoakhirqty;
                    $newnilaisaldoakhir = ($nilaitotal + $nilaisaldo);
                    
                    if ($newhargarata == 0){
                        throw new PDOException("Average price cannot be formed for " . $notransaksi . " material code :" . $kodebarang);
                    }else{
                        $strupdate = "update".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."',hargarata='".$newhargarata."',nilaisaldoakhir='".$newnilaisaldoakhir."',lastuser='".$user."',qtymasuk='".$newqtymasuk."',qtymasukxharga='".$newqtymasukxharga."'  where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."'and kodeorg='".$kodept."'";
                    }
                }
                
                
                ## Prepare update masterbarangdt
                $instmaster = " insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values ('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','".$newhargarata."','0','0','0','".$user."','".$gudang."')";
        
                $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."',hargalastin='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
        
                ## Prepare jurnal
                ## Ambil noakun supplier
                $akunspl = '';
                $kodekl = substr($supplier, 0, 4);
                $str = "select noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='PBR2'";
                $res=fetchdata($str);
                $akunspl = $res[0]['noakunkredit'];
                
                ## Ambil noakun barang
                $akunbarang = '';
                $klbarang = substr($kodebarang, 0, 3);
                $str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                $res=fetchdata($str);
                $akunbarang = $res[0]['noakun'];
                
                if (($akunbarang == '' or $akunspl == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
                    throw new PDOException("Account no. for material or supplier not available yet for " . $notransaksi);
                }
                
                ## Cek Nilai Ppn di PO
                $str = "select * from ".$dbname.".pabrikasi_5masterht where kodepabrikasi='".$kdpabrikasi."'";
                $res = fetchdata($str);
                if(count($res) <= 0){
                    throw new PDOException("PO ".$kdpabrikasi." tidak terdaftar");
                }
                
                ## Proses data
                $kodeJurnal = 'INVM0';
                
                #======================== Begin Nomor Jurnal =============================#
                ## Get Journal Counter
                $str="select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."'";
                $res = fetchData($str);
                $konter = addZero($res[0]['nokounter'] + 1, 3);
                
                ## Transform No Jurnal dari No Transaksi
                $nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
                #======================== End Nomor Jurnal ============================
                        
                ## Prep Header
                $dataRes['header'] = array(
                    'nojurnal' => $nojurnal,
                    'kodejurnal' => $kodeJurnal,
                    'tanggal' => tanggalsystem($tanggal),
                    'tanggalentry' => date('Ymd'),
                    'posting' => 1,
                    'totaldebet' => $nilaitotal,
                    'totalkredit' => -1 * $nilaitotal,
                    'amountkoreksi' => '0',
                    'noreferensi' => $notransaksi,
                    'autojurnal' => '1',
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'revisi' => '0'
                );
                
                ## Data Detail
                $noUrut = 1;
                
                ## Debet
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => tanggalsystem($tanggal),
                    'nourut' => $noUrut,
                    'noakun' => $akunbarang,
                    'keterangan' => 'Peneriaman barang pabrikasi ' . $namabarang . ' ' . $jumlah . " " . $satuan,
                    'jumlah' => $nilaitotal,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => substr($gudang, 0, 4),
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => $notransaksi,
                    'noaruskas' => '',
                    'kodevhc' => '',
                    'nodok' => '',
                    'kodeblok' => $kdpabrikasi,
                    'revisi' => '0',
                    'kodesegment' => $segment
                );
                $noUrut++;
                
                ## Kredit
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => tanggalsystem($tanggal),
                    'nourut' => $noUrut,
                    'noakun' => $akunspl,
                    'keterangan' => 'Peneriaman barang pabrikasi ' . $namabarang . ' ' . $jumlah . " " . $satuan,
                    'jumlah' => (-1 * $nilaitotal),
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => substr($gudang, 0, 4),
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => '',
                    'noreferensi' => $notransaksi,
                    'noaruskas' => '',
                    'kodevhc' => '',
                    'nodok' => '',
                    'kodeblok' => $kdpabrikasi,
                    'revisi' => '0',
                    'kodesegment' => $segment
                );
                $noUrut++;
                
                
                
                if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
                    $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                    $owlPDO->exec($insHead); 
                    
                    foreach($dataRes['detail'] as $row){
                        $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                        $owlPDO->exec($insDet); 
                    }
                    
                    ## Header and Detail inserted
                    ## Update Kode Jurnal
                    $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."'");
                    $owlPDO->exec($updJurnal); 
                }
                $owlPDO->commit();
            }catch(PDOException $e){
                $owlPDO->rollback();
                echo "Error, ".addslashes($e->getMessage());
                die();
            }
        }
} 

function createJrnBKm($notransaksi){
    global $dbname;
    global $owlPDO;
    global $param;
    $param['notransaksi']=$notransaksi;
    $sHead="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$param['notransaksi']."'";
    $dataH=fetchData($sHead);
    $sKeg="select distinct kodekegiatan from ".$dbname.".kebun_pakai_material_vw where notransaksi='".$param['notransaksi']."' and left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where noakun='".$param['noakun']."')";
    $rKeg=fetchData($sKeg);
    $dtarr['kodekegiatan']=$rKeg[0]['kodekegiatan'];
    $whereKeg="kodekegiatan='".$dtarr['kodekegiatan']."'";
    $queryKeg = selectQuery($dbname,'setup_kegiatan',"kodekegiatan,namakegiatan,noakun",$whereKeg);
    $tmpRes = fetchData($queryKeg);
    $resKeg = array();
    foreach($tmpRes as $row) {
        $resKeg[$row['kodekegiatan']]['nama'] = $row['namakegiatan'];
        $resKeg[$row['kodekegiatan']]['akun'] = $row['noakun'];
    }
    #======================== Nomor Jurnal material=============================
    $kodeJurnal1 = 'INVK1';
        # Get Journal Counter
    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal1."'");
    $tmpKonter1 = fetchData($queryJ);
    $konter1 = addZero($tmpKonter1[0]['nokounter']+1,3);

    # Transform No Jurnal dari No Transaksi
    $tmpNoJurnal = explode('/',$param['notransaksi']);
    $nojurnal1 = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal1."/".$konter1;
    #======================== Nomor Jurnal =============================
    #=== Transform Data ===
    $dataResMat['header'] = array();
    $dataResMat['detail'] = array();
    #1. Data Header
    $dataResMat['header'] = array(
        'nojurnal'=>$nojurnal1,
        'kodejurnal'=>$kodeJurnal1,
        'tanggal'=>$dataH[0]['tanggal'],
        'tanggalentry'=>date('Ymd'),
        'posting'=>'1',
        'totaldebet'=>'0',
        'totalkredit'=>'0',
        'amountkoreksi'=>'0',
        'noreferensi'=>$dataH[0]['notransaksi'],
        'autojurnal'=>'1',
        'matauang'=>'IDR',
        'kurs'=>'1',
        'revisi'=>'0'
    );    
    $noUrut = 1;
    $totalJumlah = 0;
    $str="select * from ".$dbname.".kebun_pakai_material_vw where notransaksi='".$param['notransaksi']."' and kodegudang!=''";
    $res=fetchData($str);
    # Detail (kredit)
    foreach($res as $rw=>$vald){
        $klbarang=substr($vald['kodebarang'],0,3);
        $str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
        $res=fetchdata($str);
        $akunbarang = $res[0]['noakun'];
        $optnmbrg=makeOption($dbname,"log_5masterbarang","kodebarang,namabarang","kodebarang='".$vald['kodebarang']."'");
        $$vald['namabarang']=$optnmbrg[$vald['kodebarang']];
        $dataResMat['detail'][] = array(
            'nojurnal'=>$nojurnal1,
            'tanggal'=>$dataH[0]['tanggal'],
            'nourut'=>$noUrut,
            'noakun'=>$akunbarang,
            'keterangan'=>'Material BKM '. $dataH[0]['notransaksi']." ".$vald['namabarang'],
            'jumlah'=>($vald['hargasatuan']*$vald['kwantitas'])*(-1),
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>substr($vald['kodeorg'],0,4),
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>$vald['kodebarang'],
            'nik'=>'',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>$dataH[0]['notransaksi'],
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>'',
            'revisi'=>'0',
            'kodesegment' => $segment
        );  
        $noUrut++;
        $totalJumlah+=($vald['hargasatuan']*$vald['kwantitas']);
    }
    $dataResMat['detail'][] = array(
        'nojurnal'=>$nojurnal1,
        'tanggal'=>$dataH[0]['tanggal'],
        'nourut'=>$noUrut,
        'noakun'=>$resKeg[$vald['kodekegiatan']]['akun'],
        'keterangan'=>'Material BKM '.$dataH[0]['notransaksi'],
        'jumlah'=>$totalJumlah,
        'matauang'=>'IDR',
        'kurs'=>'1',
        'kodeorg'=>substr($vald['kodeorg'],0,4),
        'kodekegiatan'=>$vald['kodekegiatan'],
        'kodeasset'=>'',
        'kodebarang'=>'',
        'nik'=>'',
        'kodecustomer'=>'',
        'kodesupplier'=>'',
        'noreferensi'=>$dataH[0]['notransaksi'],
        'noaruskas'=>'',
        'kodevhc'=>'',
        'nodok'=>'',
        'kodeblok'=>$vald['kodeorg'],
        'revisi'=>'0',
        'kodesegment' => $segment
    );
    # Total D/K
    $dataResMat['header']['totaldebet'] = $totalJumlah;
    $dataResMat['header']['totalkredit'] = $totalJumlah;
    
    #=== Insert Data jurnal material ===
    $errorDBX = "";    
    # Header
    $queryH = insertQuery($dbname,'keu_jurnalht',$dataResMat['header']);
    // exit('warning'.$queryH);
    try{$owlPDO->exec($queryH); }
        catch (PDOException $e) {
            $errorDBX .= " Error Header jurnal material:" . $e->getMessage() . "\n".$queryH;
    }
    foreach($dataResMat['detail'] as $key=>$dataDet) {
        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
        try{$owlPDO->exec($queryD); }
        catch (PDOException $e) {
                $errorDBX .= "Error Detail jurnal material ".$key." :" . $e->getMessage() . "\n".$queryD;
                $where = "nojurnal='".$nojurnal1."' and noreferensi='".$dataH[0]['notransaksi']."'";
                $queryRB = "delete from `".$dbname."`.`keu_jurnalht` where ".$where;
                try{$owlPDO->exec($queryRB); }
                    catch (PDOException $e) {
                            $errorDB .= "Rollback jurnal material Error :" . $e->getMessage() . "\n";
                    }   
                echo   $errorDBX;         
                $queryRBKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']),
                            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                try{$owlPDO->exec($queryRBKonter); }
                catch (PDOException $e) {
                        $errorDB .= "Rollback Counter Error :" . $e->getMessage() . "\n".$queryRBKonter;
                }
                echo "DB Error :\n".$errorDB;
                exit();
        }        
        $queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter1[0]['nokounter']+1),
            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal1."'");
        try{$owlPDO->exec($queryKonter); }
        catch (PDOException $e) {
                $errorDB .= "Update Counter jurnal material Error" . $e->getMessage() . "\n";
                echo $errorDB;
        }            
    }
}
?>
