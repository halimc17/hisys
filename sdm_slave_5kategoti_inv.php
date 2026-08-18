<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$idjenis = checkPostGet('idjenis', '');
$jenis = checkPostGet('jenis', '');
$jumlahhk = checkPostGet('jumlahhk', '0');
$method = checkPostGet('method','');


switch ($method) {
    case 'insert':
    // exit ('error:a');
      $listCek="select * from ".$dbname.".sdm_5kategori_inv where jeniskategori like '%".$jenis."%' and idjenis like '%".$idjenis."%'";
        $qcek=$owlPDO->query($listCek) or die(print " Gagal: ".PDOException::getMessage());
        $rcek=owlBaris($qcek);
        if($rcek != 0){
          exit('warning : Jenis Kategori sudah pernah terdaftar');
        }

        #S201707001
        $jeniscuti = "KAT";

        $query="select right(idjenis,2) as nomorurut from ".$dbname.".sdm_5kategori_inv where left(idjenis,3) = '".$jeniscuti."' order by right(idjenis,2) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
      
        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }
       

        $idjenis=$jeniscuti.addZero($awal,2);
        // exit('error: '.$idjenis);

        if ($jumlahhk > '1.0') {
            echo "Error: Nilai HK tidak lebih dari 1.0";
        } else {
        $input = "insert into " . $dbname . ".sdm_5kategori_inv (idjenis,jeniskategori,createdby)
            values ('" . $idjenis . "','" . $jenis . "','" . $_SESSION['standard']['userid'] . "')";
        // $input2 = "insert into " . $dbname . ".log_5supuser (supplierid,namasupplier,email,status)
        // values ('" . $idsupplier . "','" . $namasupplier . "','" . $email . "','" . $statusup . "')";

            // exit('error:'.$input);
        try{
          $owlPDO->exec($input); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
        }
}
        
        
        break;
		
    case 'update':

    // $listCek="select * from ".$dbname.".sdm_5kategori_inv where jeniskategori like '%".$jenis."%'";
    //     $qcek=$owlPDO->query($listCek) or die(print " Gagal: ".PDOException::getMessage());
    //     $rcek=owlBaris($qcek);
    //     if($rcek != 0){
    //       exit('warning : Jenis Cuti sudah pernah terdaftar');
    //     }

         if ($jenis > 0) {
            echo "Error: Nilai HK tidak lebih dari 1.0";
        } else {

            $input = "update " . $dbname . ".sdm_5kategori_inv set jeniskategori='" . $jenis . "',". " updateby='" . $_SESSION['standard']['userid'] . "'
                 where idjenis='" . $idjenis . "'";
            try{
          $owlPDO->exec($input); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
      }
  }
            
        break;

    case'loadData':
    // exit('warning masukk')
        // echo"


        // $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_5kategori_inv where idjenis = '".$idjenis."'" ; 
        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_5kategori_inv where idjenis<>'' ".$where."";
        // exit('error:'.$ql2); 

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        // exit ('error : '.$jlhbrs);
        $tab='';
  $nor=0;
    
  // $input = "select * from " . $dbname . ".sdm_5kategori_inv  where idjenis = '".$idjenis."'";
        $input = "select * from " . $dbname . ".sdm_5kategori_inv where idjenis<>'' ".$where."";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            // $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
            //$no+=1;
             echo"<tr class=rowcontent>";
            echo"<td align=center>" . $no . "</td>";
        echo"<td align=left>" . $d['idjenis'] . "</td>";
        echo"<td align=left>" . $d['jeniskategori'] . "</td>";        
        echo"<td align=center>
          <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['idjenis'] . "','" . $d['jeniskategori'] . "','" . $d['nilaihk'] . "');\">
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
        break;
}

?>