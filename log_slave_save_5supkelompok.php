<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$supplier_id = checkPostGet('supplier_id','');
$kode = checkPostGet('kode','');
$noakun = checkPostGet('noakun','');
$nodo = checkPostGet('nodo','');
$sync = checkPostGet('sync','');
$statkel = checkPostGet('statkel','');
$method = checkPostGet('method','');
// bikin baru lagi pake array untuk load data yg checkbox
$strnama = array ("0"=>"tidak aktif","1"=>"aktif");

// exit('warning : '.$method);

switch ($method) {

    case 'insert':
    $sCek="select count(tipe) as tip from ".$dbname.".log_5supkelompok where supplierid='".$supplier_id."'";
    $rCek=fetchData($sCek);
    if($rCek[0]['tip']>4){
        exit('warning :'.$_SESSION['lang']['max'].'>4');
    }
    // exit ('error:a');
        $input = "insert into " . $dbname . ".log_5supkelompok (supplierid,noakun,tipe,nodo,sync,updateby,status)
            values ('" . $supplier_id . "','" . $noakun . "','" . $kode . "','" . $nodo . "','" . $sync . "','" . $_SESSION['standard']['userid'] . "','" . $statkel . "')";
    try{
      $owlPDO->exec($input); 
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
        break;

    case 'update':
        $input = "update ".$dbname.".log_5supkelompok set noakun='".$noakun."', nodo='".$nodo."',sync='".$sync."', updateby='".$_SESSION['standard']['userid']."' ,status='".$statkel."' where supplierid='".$supplier_id."' and tipe='".$kode."'";
        try
		{
			$owlPDO->exec($input); 
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
            
        break;

    //perhatikan load data
    case'loadData2':
    // exit('warning masukk')
        echo"
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
         <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
         <td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
         <td align=center>" . $_SESSION['lang']['tipe'] . "</td>
         <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
       </tr>
    </thead>
    <tbody>";

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5supkelompok where supplierid = '".$supplier_id."'" ; // echo $ql2;notran

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        $tab='';
  $nor=0;
    
        $input = "select * from " . $dbname . ".log_5supkelompok where supplierid = '".$supplier_id."'";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        // $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
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
            echo "<td align=left>" . $d['noakun'] . "</td>";
            echo "<td align=left>" . (isset($nmAkun[$d['noakun']]) ? $nmAkun[$d['noakun']] : '') . "</td>";
            echo "<td align=left>" . $d['tipe'] . "</td>";
                        // echo "<td align=left>" . $d['supplierid'] . "</td>";
           
            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
             echo "<td align=left>" . $strnama[$d['status']]."</td>";
            //echo "<td align=left>".$d['updatetime']."</td>";
            //echo "<td align=left>" . $d['alamatnpwp'] . "</td>";
            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editSupKel('" . $d['supplierid'] . "','" . $d['noakun'] . "','" . $d['tipe'] . "',". "'" . $d['status'] . "' );\">
                            </td>";

            echo "</tr>"; 
            //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['supplierid']."');\"> <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['kode']."');\">
        }

        
    echo"</tbody></table>";
        break;

    default:
}
?>
