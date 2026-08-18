<?php
require_once('master_validation.php');
require_once('config/connection.php');

$txtfind_klp=$_POST['txtfind_klp'];
$stp="select * from ".$dbname.".pmn_4klcustomer where kelompok like '%".$txtfind_klp."%' limit 12";
$rep=$owlPDO->query($stp) or die(print " Gagal: ".PDOException::getMessage());
$rep->setFetchMode(PDO::FETCH_OBJ);


if($txtfind_klp!='')
{
        echo"<table class=data cellspacing=1 cellpadding=2  border=0>
                 <thead>
                 <tr class=rowheader>
                 <td class=firsttd>
                 No.
                 </td>
                 <td>Group Code</td>
                 <td>Group Namek</td>
                 <td>Account No.</td>
                 <td>Account Name</td>
                 </tr>
                 </thead>
                 <tbody>";
        $no=0;	 
        while($bas=$rep->fetch())
        {
                         if($_SESSION['language']=='EN'){
                             $kol='namaakun1  as namaakun';
                         }else{
                             $kol='namaakun';
                         }         
                $op="select noakun,".$kol." from ".$dbname.".keu_5akun where `noakun`='".$bas->noakun."'";
                $po=$owlPDO->query($op) or die(print " Gagal: ".PDOException::getMessage());
                $po->setFetchMode(PDO::FETCH_OBJ);

                $pos=$po->fetch();
                $no+=1;
                echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setGroup('".$bas->kode."','".$bas->kelompok."')\" title='Click' >
                          <td class=firsttd>".$no."</td>
                          <td>".$bas->kode."</td>
                          <td>".$bas->kelompok."</td>
                          <td>".$pos->noakun."</td>
                          <td>".$pos->namaakun."</td>
                         </tr>";
        }	 
        echo "</tbody>
                  <tfoot>
                  </tfoot>
                  </table>";	   		
}
else
{
        $txtfind =$_POST['txtfind'];
$str=" select * from ".$dbname.".keu_5akun where namaakun like '%".$txtfind."%' or  namaakun1 like '%".$txtfind."%' or noakun like '%".$txtfind."%' or tipeakun like '%".$txtfind."%' limit 12";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
        echo"<table class=data cellspacing=1 cellpadding=2  border=0>
             <thead>
                 <tr class=rowheader>
                 <td class=firsttd>
                 No.
                 </td>
                 <td>".$_SESSION['lang']['noakun']."</td>
                 <td>".$_SESSION['lang']['namaakun']."</td>
                 <td>".$_SESSION['lang']['tipe']."</td>
                 <td>".$_SESSION['lang']['matauang']."</td>
                 <td>".$_SESSION['lang']['kodeorg']."</td>
                 </tr>
                 </thead>
                 <tbody>";
        $no=0;	 
        while($bar=$res->fetch())
        {
                $no+=1;
                if($_SESSION['language']=='EN'){
                    $z=$bar->namaakun1;
                }else{
                    $z=$bar->namaakun;
                }                
                echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setNoakun('".$bar->noakun."','".$bar->namaakun."','".$bar->tipeakun."','".$bar->matauang."','".$bar->kodeorg."')\" title='Click' >
                      <td class=firsttd>".$no."</td>
                      <td>".$bar->noakun."</td>
                          <td>".$z."</td><td>".$bar->tipeakun."</td>
                          <td>".$bar->matauang."</td><td>".$bar->kodeorg."</td>
                         </tr>";
        }	 
        echo "</tbody>
              <tfoot>
                  </tfoot>
                  </table>";	   	
}
?>