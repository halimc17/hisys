<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param = $_POST;
$method=checkPostGet('method','');
$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$tipe=checkPostGet('tipe','');
$notransaksi=checkPostGet('notransaksi','');

$where = " 1=1 ";

if ($unit!='') {
    $where.=" and a.unit1='".$unit."'  ";
}

if ($tipe!='') {
    $where.=" and tipetransaksi='" . $tipe . "'  ";
}
if ($notransaksi!='') {
    $where.=" and a.notransaksi='" . $notransaksi . "'  ";
}



switch($method){
case'getUnit':
    $optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"induk='".$param['pt']."'");

    echo "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    foreach($optUnit as $code=>$val) {
      echo "<option value='".$code."'>".$code." - ".$val."</option>";
    }
  break;

    case 'preview': 
        $tab="  <table class=sortable cellspacing=1 cellpadding=3 border=0>
              <thead>
                <tr class=rowheader>
              
                    <th colspan=3>DATA PERUSAHAAN</th>
                    <th colspan=3>DATA BANK </th>
                    <th colspan=4>DATA DEVIDEN </th>
              <tr class=rowcontent>
                    
                        <td align=center>Nama Perusahaan</td>
                        <td align=center>Lokasi</td>
                        <td align=center>Status Transaski</td>
                        <td align=center>Nama Bank</td>
                        <td align=center>Mata Uang</td>
                        <td align=center>No Rekening</td>
                        <td align=center>Nama Perusahaan</td>
                        <td align=center>Transaksi</td>
                        <td align=center>Tgl Transaksi</td>
                        <td align=center>Jumlah Dividen</td>
                    </tr>
              </td>
              </tr> 
                 </thead>";
      
       $str1 = "select notransaksi,namaorganisasi,alamat,a.status,unit2,transaksi,d.namabank,c.matauang,norekening,a.tanggal,nilai from keu_dividen a left join organisasi b on a.unit1=b.kodeorganisasi
                left join keu_5akunbank c on a.norekening=c.noakun left join keu_5daftarbank d on c.namabank=d.kodebank where ".$where."";
                
       $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
       $res1->setFetchMode(PDO::FETCH_ASSOC);
       while ($bar=$res1->fetch())
                {

               $strorg = "select unit2,namaorganisasi from keu_dividen a left join organisasi b on a.unit2=b.kodeorganisasi where unit1='".$unit."'";
               //exit('error'.$strorg);
               $resorg = $owlPDO->query($strorg) or die(print " Gagal: " . PDOException::getMessage());
               $resorg->setFetchMode(PDO::FETCH_ASSOC);
               $barorg=$resorg->fetch();
               

                  $tab.=
                  "<tr class=rowcontent onclick=\"detaildividen('" . $bar['notransaksi']. "');\">
                   <td align=left>".$bar['namaorganisasi']."</td>
                   <td align=left>".$bar['alamat']."</td>
                   <td align=left>".$bar['status']."</td>
                   <td align=left>".$bar['namabank']."</td>
                   <td align=left>".$bar['matauang']."</td>
                   <td align=right>".$bar['norekening']."</td>
                   <td align=left>".$barorg['namaorganisasi']."</td>
                   <td align=left>".$bar['transaksi']."</td>
                   <td align=center>".$bar['tanggal']."</td>
                   <td align=right>".number_format($bar['nilai'])."</td>
                  </tr>";  
                    
                    
                          
                }

    
    break;

    case'loaddetail':
OPEN_BOX();
        echo "<fieldset><legend>Pengeluaran Dividen</legend>";
        echo "<div style=overflow:auto;width:100%;>
      <table cellpading=0 cellspacing=1 width=100% class=sortable >
       <thead>
        <tr align=center>
       <td>".$_SESSION['lang']['tanggal']."</td>
      <td>".$_SESSION['lang']['notransaksi']." Kasbank</td>
      <td>".$_SESSION['lang']['keterangan']."</td>
      <td>".$_SESSION['lang']['jumlah']."</td>
        </tr></thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and tipetransaksi='K'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            
            $strket="select keterangan from ".$dbname.".keu_kasbankht where notransaksi='".$bar['notransaksi']."' and tipetransaksi='K' ";
            $resket=$owlPDO->query($strket) or die(print " Gagal: ".PDOException::getMessage());
            $resket->setFetchMode(PDO::FETCH_ASSOC);
            $barket=$resket->fetch();

           echo "<tr class=rowcontent>
                  <td>".$bar['tanggal']."</td>
                  <td>".$bar['notransaksi']."</td>
                  <td>".$barket['keterangan']."</td>
                    <td align=right>".number_format($bar['jumlah'])."</td>
                      </tr>";
                $total+=$bar['jumlah'];

        }
        "<tr class=rowcontent>";
       "<td align=right colspan=3>".$_SESSION['lang']['total']."</td>";
       "<td align=right>".number_format($total)."</td>";
       "</tr>";
         //echo $data;
         echo "</table>";
        echo "</fieldset><br>";  
         
         

       echo "<fieldset><legend>Penerimaan Dividen</legend>";
        echo "<div style=overflow:auto;width:100%;>
      <table cellpading=0 cellspacing=1 width=100% class=sortable >
       <thead>
        <tr align=center>
       <td>".$_SESSION['lang']['tanggal']."</td>
      <td>".$_SESSION['lang']['notransaksi']." Kasbank</td>
      <td>".$_SESSION['lang']['keterangan']."</td>
      <td>".$_SESSION['lang']['jumlah']."</td>
        </tr></thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and tipetransaksi='M'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            
            $strket="select keterangan from ".$dbname.".keu_kasbankht where notransaksi='".$bar['notransaksi']."' and tipetransaksi='M' ";
            $resket=$owlPDO->query($strket) or die(print " Gagal: ".PDOException::getMessage());
            $resket->setFetchMode(PDO::FETCH_ASSOC);
            $barket=$resket->fetch();

           echo "<tr class=rowcontent>
                  <td>".$bar['tanggal']."</td>
                  <td>".$bar['notransaksi']."</td>
                  <td>".$barket['keterangan']."</td>
                    <td align=right>".number_format($bar['jumlah'])."</td>
                      </tr>";
                $total+=$bar['jumlah'];

        }
        "<tr class=rowcontent>";
       "<td align=right colspan=3>".$_SESSION['lang']['total']."</td>";
       "<td align=right>".number_format($total)."</td>";
       "</tr>";
         //echo $data;
         echo "</table>";
        echo "</fieldset><br>";  
       
 CLOSE_BOX();
    break;

case'excel':
 
 $tab="  <table class=sortable cellspacing=1 cellpadding=3 border=1>
              <thead>
                <tr class=rowheader>
              
                    <th colspan=3>DATA PERUSAHAAN</th>
                    <th colspan=3>DATA BANK </th>
                    <th colspan=4>DATA DEVIDEN </th>
              <tr class=rowcontent>
                    
                        <td align=center>Nama Perusahaan</td>
                        <td align=center>Lokasi</td>
                        <td align=center>Status Transaski</td>
                        <td align=center>Nama Bank</td>
                        <td align=center>Mata Uang</td>
                        <td align=center>No Rekening</td>
                        <td align=center>Nama Perusahaan</td>
                        <td align=center>Transaksi</td>
                        <td align=center>Tgl Transaksi</td>
                        <td align=center>Jumlah Dividen</td>
                    </tr>
              </td>
              </tr> 
                 </thead>";
      
       $str1 = "select notransaksi,namaorganisasi,alamat,a.status,unit2,transaksi,d.namabank,c.matauang,norekening,a.tanggal,nilai from keu_dividen a left join organisasi b on a.unit1=b.kodeorganisasi
                left join keu_5akunbank c on a.norekening=c.noakun left join keu_5daftarbank d on c.namabank=d.kodebank where ".$where."";
                
       $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
       $res1->setFetchMode(PDO::FETCH_ASSOC);
       while ($bar=$res1->fetch())
                {

               $strorg = "select unit2,namaorganisasi from keu_dividen a left join organisasi b on a.unit2=b.kodeorganisasi where unit1='".$unit."'";
               //exit('error'.$strorg);
               $resorg = $owlPDO->query($strorg) or die(print " Gagal: " . PDOException::getMessage());
               $resorg->setFetchMode(PDO::FETCH_ASSOC);
               $barorg=$resorg->fetch();
               

                  $tab.=
                  "<tr class=rowcontent onclick=\"detaildividen('" . $bar['notransaksi']. "');\">
                   <td align=left>".$bar['namaorganisasi']."</td>
                   <td align=left>".$bar['alamat']."</td>
                   <td align=left>".$bar['status']."</td>
                   <td align=left>".$bar['namabank']."</td>
                   <td align=left>".$bar['matauang']."</td>
                   <td align=right>".$bar['norekening']."</td>
                   <td align=left>".$barorg['namaorganisasi']."</td>
                   <td align=left>".$bar['transaksi']."</td>
                   <td align=center>".$bar['tanggal']."</td>
                   <td align=right>".number_format($bar['nilai'])."</td>
                  </tr>";  
                    
                          
                }

        $nop_ = "Laporan_Dividen" . date('Ymd_His');
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
                echo "<script language=javascript1.2>
                            parent.window.alert('Cant convert to excel format');
                            </script>";
              exit;
            } else {
                echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
            }
            closedir($handle);
        }        

        break;
    
}
if ($method != 'excel')
{
    echo $tab;
}
?>