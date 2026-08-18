<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


// $prosses = checkPostGet('prosses','');
// $id_supplier = checkPostGet('idsupplier_detail','');

$id_supplier = checkPostGet('id_supplier','');
$rekening = checkPostGet('rekening','');
$bank = checkPostGet('bank','');
$atasnama = checkPostGet('atasnama','');
$method = checkPostGet('method','');
$cabang = checkPostGet('cabang','');
$kota = checkPostGet('kota','');
$negara = checkPostGet('negara','');
$matauang = checkPostGet('matauang','');
$statusbank = checkPostGet('statusbank','');
$def = checkPostGet('def','');
$strnama = array ("0"=>"TIDAK","1"=>"YA");
$strnama1 = array ("0"=>"Tidak Aktif","1"=>"Aktif");
$strnamax = array ("0"=>"Proses persetujuan","1"=>"Disetujui","2"=>"Ditolak");
$jnsapp = "DS";

switch($method){

  case 'insert':
  //echo $_POST['persetujuan'];
  //exit('Error'.$_POST['persetujuan']);

  if ($def==1){
      // $input="select * from ".$dbname.".log_5rekbank where supplierid='".$id_supplier."' and idbank='".$bank."' and matauang='".$matauang."' ";
      // $qry=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
      // $numRows=owlBaris($qry);
      // if($numRows>=1){
        // echo "Error: Data Bank Sudah Ada.";
      // }else{
        $input="select * from ".$dbname.".log_5rekbank where rekening='".$rekening."' and supplierid='".$id_supplier."'";
        $qry=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
        $numRows=owlBaris($qry);
        if($numRows>=1){
          echo "Error: Nomor rekening Sudah Ada untuk supplier ini.";
        }
        else{
            $input="update ".$dbname.".log_5rekbank set def='0' where supplierid='".$id_supplier."' ";
            // exit('error'.$input);
            try{
          $owlPDO->exec($input); 
          }
          catch (PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
          die();
          }
          $input="insert into " . $dbname . ".log_5rekbank (supplierid,idbank,rekening,an,cabang,kota,negara,matauang,updateby,def,isactive,statusyangdiinginkan)
                values ('" . $id_supplier . "','" . $bank . "','" . $rekening . "','" . $atasnama . "','" . $cabang . "','" . $kota . "','" . $negara . "','" . $matauang . "','" . $_SESSION['standard']['userid'] . "','" . $def . "','0','" . $statusbank . "')";
          try{
          $owlPDO->exec($input); 
           $strx="delete from ".$dbname.".approval where notransaksi='".$id_supplier."' and jenispersetujuan='".$jnsapp."'";
            try
            {
              $owlPDO->exec($strx); 
              $listpersetujuan=$_POST['persetujuan'];
              foreach($listpersetujuan as $key=>$val)
              {
                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$id_supplier."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
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
          }
          catch (PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
          die();
          }
        }
      // }
      }

      if ($def==0){
      // $input="select * from ".$dbname.".log_5rekbank where supplierid='".$id_supplier."' and idbank='".$bank."' and matauang='".$matauang."'";
      // $qry=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
      // $numRows=owlBaris($qry);
	  // exit("error : ".$numRows);
      // if($numRows>=1){
        // echo "Error: Data Bank Sudah Ada.";
      // }else{
         $input="select * from ".$dbname.".log_5rekbank where rekening='".$rekening."' and supplierid='".$id_supplier."'";
        $qry=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
        $numRows=owlBaris($qry);
        if($numRows>=1){
          echo "Error: Nomor rekening Sudah Ada di supplier ini.";
        }
        else{
          $input="insert into " . $dbname . ".log_5rekbank (supplierid,idbank,rekening,an,cabang,kota,negara,matauang,updateby,def,isactive,statusyangdiinginkan,statuspersetujuan)
                values ('" . $id_supplier . "','" . $bank . "','" . $rekening . "','" . $atasnama . "','" . $cabang . "','" . $kota . "','" . $negara . "','" . $matauang . "','" . $_SESSION['standard']['userid'] . "','" . $def . "','0','" . $statusbank . "','0')";
          try{
          $owlPDO->exec($input); 
              $listpersetujuan=$_POST['persetujuan'];
              foreach($listpersetujuan as $key=>$val)
              {
                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$id_supplier."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
                try
                {
                  $owlPDO->exec($str);
                }
                catch (PDOException $e) 
                {
                  print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                }
              }
            
          }
          catch (PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
          die();
          }
        }
      // }
      }
        break;

    case 'update':

          $strx = selectQuery($dbname,"log_5rekbank","*","supplierid='".$id_supplier."'  and idbank='".$bank."' and matauang='".$matauang."'");
          $resx = fetchData($strx);
          $oldx['supplierid'] = $resx[0]['supplierid'];
          $oldx['idbank'] = $resx[0]['idbank'];
          $oldx['bank'] = $resx[0]['bank'];
          $oldx['rekening'] = $resx[0]['rekening'];
          $oldx['an'] = $resx[0]['an'];
          $oldx['cabang'] = $resx[0]['cabang'];
          $oldx['kota'] = $resx[0]['kota'];
          $oldx['negara'] = $resx[0]['negara'];
          $oldx['matauang'] = $resx[0]['matauang'];
          $oldx['def'] = $resx[0]['def'];
          $oldx['isactive'] = $resx[0]['isactive'];
          $perubahanx = $resx[0]['perubahan'];

           $textubah=$oldx['supplierid']."##".$oldx['idbank']."##".$oldx['bank']."##".$oldx['rekening']."##".$oldx['an']."##".$oldx['cabang']."##".$oldx['kota']."##".$oldx['negara']."##".$oldx['matauang']."##".$oldx['def']."##".$oldx['isactive'];
        
           #defaultnya
        $sDefault="select def,idbank,matauang from ".$dbname.".log_5rekbank where supplierid='".$id_supplier."' and def=1";
        $rDefault=fetchData($sDefault);

            $input="update ".$dbname.".log_5rekbank set def='".$def."',
               an='".$atasnama."', cabang='".$cabang."', kota='".$kota."',
               negara='".$negara."', matauang='".$matauang."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date('Ymdhis')."',
               isactive='".$statusbank."',statusyangdiinginkan='".$statusbank."',statuspersetujuan='0',perubahan='".$textubah."' 
              where supplierid='".$id_supplier."' and idbank='".$bank."' and matauang='".$matauang."' and rekening='".$rekening."'";

            if($perubahanx!='')
            {
              $arrperub=explode('##', $perubahanx);
              if($arrperub[0]!='')
              {
                $input="update ".$dbname.".log_5rekbank set def='".$def."', 
                 an='".$atasnama."', cabang='".$cabang."', kota='".$kota."',
                 negara='".$negara."', matauang='".$matauang."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date('Ymdhis')."',
                 isactive='".$statusbank."',statusyangdiinginkan='".$statusbank."',statuspersetujuan='0'  
                where supplierid='".$id_supplier."' and idbank='".$bank."' and matauang='".$matauang."' and rekening='".$rekening."' ";
              }
            }
            try{$owlPDO->exec($input); }
            catch (PDOException $e){ echo " Gagal," . addslashes($e->getMessage());die();}
            if($def==0){
            $sCek="select def from ".$dbname.".log_5rekbank where supplierid='".$id_supplier."' and def=1 ";
            $rCek=fetchData($sCek);
              if(count($rCek)==0){
                #roll back defaultnya
                $input="update ".$dbname.".log_5rekbank set def='".$rDefault[0]['def']."'
                      where supplierid='".$id_supplier."' and idbank='".$rDefault[0]['idbank']."' and matauang='".$rDefault[0]['matauang']."' and rekening='".$rekening."' ";
                try{$owlPDO->exec($input); }
                catch (PDOException $e){ echo " Gagal," . addslashes($e->getMessage());die();}
                exit ('Error: Harus ada 1 Rek. Bank yang default.');
              }else{
                $input="update ".$dbname.".log_5rekbank set def='0'
                      where supplierid='".$id_supplier."' and idbank='".$rDefault[0]['idbank']."' and matauang='".$rDefault[0]['matauang']."' and rekening='".$rekening."' ";
                try{$owlPDO->exec($input); }
                catch (PDOException $e){ echo " Gagal," . addslashes($e->getMessage());die();}
              }
              
            }
            
            if($def==1){
              // $input="update ".$dbname.".log_5rekbank set def='0' where supplierid='".$id_supplier."' and idbank='".$rDefault[0]['idbank']."' and rekening='".$rekening."'";
              $input="update ".$dbname.".log_5rekbank set def='".$def."' where supplierid='".$id_supplier."' and idbank='".$rDefault[0]['idbank']."' and rekening='".$rekening."'";
              try{
              $owlPDO->exec($input); 
              }
              catch (PDOException $e){
              echo " Gagal," . addslashes($e->getMessage());
              die();
              }
          }

            $strx="delete from ".$dbname.".approval where notransaksi='".$id_supplier."' and jenispersetujuan='".$jnsapp."'";
            try
            {
              $owlPDO->exec($strx); 
              $listpersetujuan=$_POST['persetujuan'];
              foreach($listpersetujuan as $key=>$val)
              {
                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$id_supplier."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
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
          
    // if ($def==0){
    //   $input="select def from ".$dbname.".log_5rekbank where supplierid='".$id_supplier."' ";
    //   $qry=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    //   $qry->setFetchMode(PDO::FETCH_OBJ);
    //   while($sbr=$qry->fetch()){
    //     if ($bar->def==0){
    //       exit ('Error: Harus ada 1 Rek. Bank yang default.');
    //     }
    //   }
    //   }else{
    //     $input="select * from ".$dbname.".log_5rekbank where rekening='".$rekening."'";
    //     $qry=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    //     $numRows=owlBaris($qry);
    //     if($numRows>=1){
    //       echo "Error: Nomor rekening Sudah Ada.";
    //     }
    //     else{
    //         $input="update ".$dbname.".log_5rekbank set def='".$def."', rekening='".$rekening."',
    //            an='".$atasnama."', cabang='".$cabang."', kota='".$kota."',
    //            negara='".$negara."', matauang='".$matauang."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date('Ymdhis')."',
    //            isactive='".$statusbank."'
    //           where supplierid='".$id_supplier."' and idbank='".$bank."' ";
    //         try{
    //       $owlPDO->exec($input); 
    //       }
    //       catch (PDOException $e){
    //       echo " Gagal," . addslashes($e->getMessage());
    //       die();
    //       }
    //   }
    // }
        
    // if ($def==1){
    //   #penguncian matauang lebih dari satu
    //     $arrMatauang[$matauang]=0;
    //     $input="select * from ".$dbname.".log_5rekbank where supplierid='".$id_supplier."' and matauang='".$matauang."'";
    //     $qry=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    //     $numRows=owlBaris($qry);
    //     if($numRows>=1){
    //       $arrMatauang[$matauang]=$numRows;
    //       //echo "Error: Nomor rekening Sudah Ada.";
    //     }
    //     else{
    //          $input="update ".$dbname.".log_5rekbank set def='0', rekening='".$rekening."',
    //            an='".$atasnama."', cabang='".$cabang."', kota='".$kota."',
    //            negara='".$negara."', matauang='".$matauang."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date('Ymdhis')."',
    //            isactive='".$statusbank."'
    //           where supplierid='".$id_supplier."' and idbank='".$bank."' ";
    //         try{
    //       $owlPDO->exec($input); 
    //       }
    //       catch (PDOException $e){
    //       echo " Gagal," . addslashes($e->getMessage());
    //       die();
    //       }
    //       $input="update ".$dbname.".log_5rekbank set def='".$def."' where supplierid='".$id_supplier."' and idbank='".$bank."' ";
    //       try{
    //       $owlPDO->exec($input); 
    //       }
    //       catch (PDOException $e){
    //       echo " Gagal," . addslashes($e->getMessage());
    //       die();
    //       }
    //     }
    // }
  
        break;

        case'loadData4':
        echo"
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
         <td align=center>" . $_SESSION['lang']['namabank'] . "</td>
         <td align=center>" . $_SESSION['lang']['norek'] . "</td>
         <td align=center>" . $_SESSION['lang']['atasnama'] . "</td>
         <td align=center>" . $_SESSION['lang']['cabang'] . "</td>
         <td align=center>" . $_SESSION['lang']['kota'] . "</td>
         <td align=center>" . $_SESSION['lang']['negara'] . "</td>
         <td align=center>" . $_SESSION['lang']['matauang'] . "</td>
         <td align=center>" . $_SESSION['lang']['default'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan'] . "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
       </tr>
    </thead>
    <tbody>";

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5rekbank where supplierid = '".$id_supplier."'" ; // echo $ql2;notran

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        $tab='';
  $nor=0;
    
        $input = "select * from " . $dbname . ".log_5rekbank where supplierid = '".$id_supplier."'";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        while ($d = $n->fetch()) {
            $no+=1;
            $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
            $nmbank = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            echo "<td align=left>" . $nmbank[$d['idbank']] . "</td>";
            echo "<td align=left>" . $d['rekening'] . "</td>";
            echo "<td align=left>" . $d['an'] . "</td>";
            echo "<td align=left>" . $d['cabang'] . "</td>";
            echo "<td align=left>" . $d['kota'] . "</td>";
            echo "<td align=left>" . $d['negara'] . "</td>";
            echo "<td align=left>" . $d['matauang'] . "</td>";
            echo "<td align=left>" . $strnama[$d['def']]."</td>";
            echo "<td align=left>" . $strnama1[$d['isactive']]."</td>";
            echo "<td align=left>" . $strnamax[$d['statuspersetujuan']]."</td>";
            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editAkun('" . $d['supplierid'] . "',". "'" . $d['idbank'] . "',". "'" . $d['rekening'] . "',". "'" . $d['an'] . "',". "'" . $d['cabang'] . "',". "'" . $d['kota'] . "',". "'" . $d['negara'] . "',". "'" . $d['matauang'] . "','" . $d['def'] . "','" . $d['isactive'] . "');\">
                            </td>";

            echo "</tr>"; 
        }
        
    echo"</tbody></table>";
	break;
  default:
  break;
  
}
?>