<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$supp_id = checkPostGet('supp_id','');
$pph = checkPostGet('pph','');
$tarif = checkPostGet('tarif','');
$nodo = checkPostGet('nodo','');
$sync = checkPostGet('sync','');
$status = checkPostGet('status','');
$statkel = checkPostGet('statkel','');
$method = checkPostGet('method','');
$strnama = array ("0"=>"Tidak aktif","1"=>"Aktif");
$strnamaper = array ("0"=>"Proses persetujuan","1"=>"Disetujui","2"=>"Ditolak");
$jnsapp = "DS";
// bikin baru lagi pake array untuk load data yg checkbox
// $strnama = array ("0"=>"tidak aktif","1"=>"aktif");

// exit('warning : '.$method);

switch ($method) {

    case 'insert':
    // exit ('error:a');
        $input = "insert into " . $dbname . ".log_5pphsup (supplierid,noakun,tarif,createdby,status,statusyangdiinginkan,statuspersetujuan)
            values ('" . $supp_id . "','" . $pph . "','" . $tarif . "','" . $_SESSION['standard']['userid'] . "','0','".$status."','0')";
			// exit("error:$input");
    try{
      $owlPDO->exec($input); 
      $strx="delete from ".$dbname.".approval where notransaksi='".$supp_id."' and jenispersetujuan='".$jnsapp."'";
            try
            {
              $owlPDO->exec($strx); 
              $listpersetujuan=$_POST['persetujuan'];
              foreach($listpersetujuan as $key=>$val)
              {
                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$supp_id."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
                try
                {
                  $owlPDO->exec($str);
                }
                catch (PDOException $e) 
                {
                  print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                }
              }
            }catch(PDOException $e){
              echo " Gagal," . addslashes($e->getMessage());
            }
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }

    // echo $input;
        break;

    case 'update':
          $strx = selectQuery($dbname,"log_5pphsup","*","noakun='" . $pph . "' and supplierid='".$supp_id."'");
          $resx = fetchData($strx);
          $oldx['supplierid'] = $resx[0]['supplierid'];
          $oldx['noakun'] = $resx[0]['noakun'];
          $oldx['tarif'] = $resx[0]['tarif'];
          $oldx['status'] = $resx[0]['status'];
          $perubahanx = $resx[0]['perubahan'];

          $textubah=$oldx['supplierid']. "##" .$oldx['noakun'] . "##" . $oldx['tarif'] . "##" . $oldx['status'];


        $input = "update " . $dbname . ".log_5pphsup set tarif='" . $tarif . "',". " updateby='" . $_SESSION['standard']['userid'] . "',status='0',statusyangdiinginkan='".$status."',statuspersetujuan='0',perubahan='".$textubah."' where supplierid='" . $supp_id . "' and noakun ='" .$pph."'";

            if($perubahanx!='')
            {
              $arrperub=explode('##', $perubahanx);
              if($arrperub[0]!='')
              {
                   $input = "update " . $dbname . ".log_5pphsup set tarif='" . $tarif . "',". " updateby='" . $_SESSION['standard']['userid'] . "',status='0',statusyangdiinginkan='".$status."',statuspersetujuan='0' where supplierid='" . $supp_id . "' and noakun ='" .$pph."'";
              }
            }

        try{
      $owlPDO->exec($input); 
      $strx="delete from ".$dbname.".approval where notransaksi='".$supp_id."' and jenispersetujuan='".$jnsapp."'";
            try
            {
              $owlPDO->exec($strx); 
              $listpersetujuan=$_POST['persetujuan'];
              foreach($listpersetujuan as $key=>$val)
              {
                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$supp_id."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
                try
                {
                  $owlPDO->exec($str);
                }
                catch (PDOException $e) 
                {
                  print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                }
              }
            }catch(PDOException $e){
              echo " Gagal," . addslashes($e->getMessage());
            }
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
            
        break;

    //perhatikan load data
    case'loadDatapph':
    // exit('warning masukk')
        echo"
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
         <td align=center>" . $_SESSION['lang']['pph'] . "</td>
         <td align=center>" . $_SESSION['lang']['tarif'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan'] . "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
       </tr>
    </thead>
    <tbody>";

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5pphsup where supplierid = '".$supp_id."'" ; // echo $ql2;notran

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        $tab='';
  $nor=0;
    
        $input = "select * from " . $dbname . ".log_5pphsup where supplierid = '".$supp_id."'";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        // $no = $maxdisplay;
        while ($d = $n->fetch()) {

            // $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
            // $nmKode = makeOption($dbname, 'log_5klsupplier', 'noakun,tipe');
            $nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
            // $optTipeSup = '';
            // $nmKode = makeOption($dbname, 'log_5klsupplier', 'tipe,noakun');
            //$no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            echo "<td align=left>" . (isset($nmAkun[$d['noakun']]) ? $nmAkun[$d['noakun']] : '') . "</td>";
            echo "<td align=left>" . $d['tarif'] . "</td>";
                        // echo "<td align=left>" . $d['supplierid'] . "</td>";
           
            // echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            echo "<td align=left>" . $strnama[$d['status']]."</td>";
            echo "<td align=left>" . $strnamaper[$d['statuspersetujuan']]."</td>";
            //echo "<td align=left>".$d['updatetime']."</td>";
            //echo "<td align=left>" . $d['alamatnpwp'] . "</td>";
            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editpph('" . $d['supplierid'] . "','" . $d['noakun'] . "','" . $d['tarif'] . "' );\">
                            </td>";

            echo "</tr>"; 
            //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['supplierid']."');\"> <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['kode']."');\">
        }

        
    echo"</tbody></table>";
        break;

    default:
}
?>
