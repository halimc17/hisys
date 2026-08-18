<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$supplierid1 = checkPostGet('supplierid1','');
$kodetimbangan = checkPostGet('kodetimbangan','');
$status1 = checkPostGet('status1','');
$method = checkPostGet('method','');
// bikin baru lagi pake array untuk load data yg checkbox
$strnama = array ("0"=>"tidak aktif","1"=>"aktif");

// exit('warning : '.$method);

switch ($method) {

    case 'insert':
    //exit ('error:a');
        $input = "insert into " . $dbname . ".log_5suptimbangan (supplierid,kodetimbangan,updateby,status)
            values ('" . $supplierid1 . "','" . $kodetimbangan . "','" . $_SESSION['standard']['userid'] . "','" . $status1 . "')";
    try{
      $owlPDO->exec($input); 
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
        break;

    case 'update':
        $input = "update " . $dbname . ".log_5suptimbangan set updateby='" . $_SESSION['standard']['userid'] . "',status='" . $status1 . "'
             where supplierid='" . $supplierid1 . "' and kodetimbangan='" . $kodetimbangan . "'";
        try{
      $owlPDO->exec($input); 
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
            
        break;

    //perhatikan load data
    case'loadData3':
    // exit('warning masukk')
        echo"
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
         <td align=center>" . $_SESSION['lang']['kodetimbangan'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . "</td>
         <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
       </tr>
    </thead>
    <tbody>";

 //paging untuk membatyasi data perhalaman
        // $limit = 10;
        // $page = 0;
        // if (isset($_POST['page'])) {
        //     $page = $_POST['page'];
        //     if ($page < 0)
        //         $page = 0;
        // }
        // $offset = $page * $limit;
        // $maxdisplay = ($page * $limit);

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5suptimbangan where supplierid = '".$supplierid1."'" ; // echo $ql2;notran

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        $tab='';
  $nor=0;
    
        $input = "select * from " . $dbname . ".log_5suptimbangan where supplierid = '".$supplierid1."'" ;
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        // $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
            //$no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            // echo "<td align=left>" . $d['supplierid'] . "</td>";
            echo "<td align=left>" . $d['kodetimbangan'] . "</td>";
            
            echo "<td align=left>" . $strnama[$d['status']]."</td>";


            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            //echo "<td align=left>".$d['updatetime']."</td>";
            //echo "<td align=left>" . $d['alamatnpwp'] . "</td>";
            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editSupTim('" . $d['supplierid'] . "','" . $d['kodetimbangan'] . "',". "'" . $d['status'] . "' );\">
                            </td>";

            echo "</tr>"; 
            //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['supplierid']."');\"> <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['kode']."');\">
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
    // echo"<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
    // echo"<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
    // echo"<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
    // echo"</td></tr>";
        
    echo"</tbody></table>";
        break;

    default:
}
?>
