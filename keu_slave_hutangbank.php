<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');

$notrans = checkPostGet('notrans','');
$kodeorg = checkPostGet('kodeorg','');
$noakun = checkPostGet('noakun','');
$jenis = checkPostGet('jenis','');
$namahutang = checkPostGet('namahutang','');
$nilaipokok = checkPostGet('nilaipokok','');
$nilaibunga = checkPostGet('nilaibunga','');
$jumlahbulan = checkPostGet('jumlahbulan','');
$tglmulai=tanggalsystemn(checkPostGet('tglmulai',''));
$tglselesai=tanggalsystemn(checkPostGet('tglselesai',''));
$method = checkPostGet('method','');

$strx = "";
$data = array();
$data['error'] = 'false';

switch ($method) {
        case 'insert':
    // exit ('error:a');
        //CEK no transaksi apakah sudah ada apa belum
        // $listCek="select * from ".$dbname.".keu_hutangbank where notransaksi like '%".$notrans."%'";
        // $qcek=$owlPDO->query($listCek) or die(print " Gagal: ".PDOException::getMessage());
        // $rcek=owlBaris($qcek);
        // if($rcek != 0){
        //   exit('warning :'.$_SESSION['lang']['datasudahada']);
        // }
        #S201707001
        $tahunbulan = "LH".date("Ym");

        $query="select right(notransaksi,3) as nomorurut from ".$dbname.".keu_hutangbank where left(notransaksi,8) = '".$tahunbulan."' and kodeorg = '".$kodeorg."' order by right(notransaksi,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
      
        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }

        $notrans=$tahunbulan.addZero($awal,3);
        //exit('warning: '.$idsupplier);

        $input = "insert into " . $dbname . ".keu_hutangbank (notransaksi,kodeorg,noakun,jenis,namahutang,nilaipokok,nilaibunga,jumlahbulan,tanggalmulai,tanggalselesai)
            values ('" . $notrans . "','" . $kodeorg . "','" . $noakun . "','" . $jenis . "','" . $namahutang . "','" . $nilaipokok . "','" . $nilaibunga . "','" . $jumlahbulan . "','" . $tglmulai . "','" . $tglselesai . "')";

        try{
          $owlPDO->exec($input); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
        }
            break;

        case 'update':
        // $listCek="select * from ".$dbname.".keu_hutangbank where notransaksi like '%".$notransaksi."%'";
        // $qcek=$owlPDO->query($listCek) or die(print " Gagal: ".PDOException::getMessage());
        // $rcek=owlBaris($qcek);
        // if($rcek != 0){
        //   exit('warning :'.$_SESSION['lang']['datasudahada']);
        // }
            $input = "update " . $dbname . ".keu_hutangbank set kodeorg='" . $kodeorg . "',noakun='" . $noakun . "',jenis='" . $jenis . "',namahutang='" . $namahutang . "',nilaipokok='" . $nilaipokok . "',nilaibunga='" . $nilaibunga . "',jumlahbulan='" . $jumlahbulan . "',tanggalmulai='" . $tglmulai . "',tanggalselesai='" . $tglselesai . "'
                 where notransaksi='" . $notrans . "' and kodeorg ='".$kodeorg."'";
            try{
          $owlPDO->exec($input); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
      }
            
        break;


case'loadData':
    // exit('warning masukk')
        // echo"
      echo"
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
         <td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
         <td align=center>" . $_SESSION['lang']['jenis'] . "</td>
         <td align=center>" .$_SESSION['lang']['nama']." ".$_SESSION['lang']['hutang']. "</td>
         <td align=center>" .$_SESSION['lang']['nilai']." ".$_SESSION['lang']['pokok']. "</td>
         <td align=center>" .$_SESSION['lang']['nilai']." ".$_SESSION['lang']['bunga']. "</td>
         <td align=center>" . $_SESSION['lang']['jumlahbulan'] . "</td>
         <td align=center>" . $_SESSION['lang']['tanggalmulai'] . "</td>
         <td align=center>" . $_SESSION['lang']['tanggalselesai'] . "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
       </tr>
    </thead>
    <tbody>";


        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_hutangbank"; 

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        // exit ('error : '.$jlhbrs);
        $tab='';
  $nor=0;
    
        $input = "select * from " . $dbname . ".keu_hutangbank";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        // $no = $maxdisplay;
        while ($d = $n->fetch()) {

            // $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            $nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
             echo"<tr class=rowcontent>";
            // echo"<td align=left>" . $d['kodekelompok'] . "</td>";
            echo "<td align=center>" . $no . "</td>";
            // echo"<td align=left>" . $d['namaorganisasi'] . "</td>";
            echo"<td align=left>" . $d['kodeorg'] . "</td>";
            echo "<td align=left>" . (isset($nmakun[$d['noakun']]) ? $nmakun[$d['noakun']] : '') . "</td>";
            // echo"<td align=left>" . $d['noakun'] . "</td>";
            echo"<td align=left>" . $d['jenis'] . "</td>";
            echo"<td align=left>" . $d['namahutang'] . "</td>";
            echo"<td align=left>" . $d['nilaipokok'] . "</td>";
            echo"<td align=left>" . $d['nilaibunga'] . "</td>";
            echo"<td align=left>" . $d['jumlahbulan'] . "</td>";
            echo"<td align=left>" . $d['tanggalmulai'] . "</td>";
            echo"<td align=left>" . $d['tanggalselesai'] . "</td>";
            echo"<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['kodeorg'] . "','" . $d['noakun'] . "','" . $d['jenis'] . "','" . $d['namahutang'] . "','" . $d['nilaipokok'] . "','" . $d['nilaibunga'] . "','" . $d['jumlahbulan'] . "','" . $d['tanggalmulai'] . "','" . $d['tanggalselesai'] . "','" . $d['notransaksi'] . "' );\">
                            </td>";
                            

            echo"</tr>"; 
        }

        //#bikin tombol untuk pagingnya
    //     $totrows=ceil($jlhbrs/$limit);
    // if($totrows==0)
    // {
    //   $totrows=1;
    // }
    
    // $isiRow='';
    // for($er=1;$er<=$totrows;$er++)
    // {
    //   $sel = ($page==$er-1)? 'selected': '';
    //   $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
    // }

    // echo"<tr><td colspan=20 align=center>";
    // echo"<button class=mybutton onclick=loadData1(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
    // echo"<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
    // echo"<button class=mybutton onclick=loadData1(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
    // echo"</td></tr>";
        
    echo"</tbody></table>";
        break;

    default:
        // break;
}

?>