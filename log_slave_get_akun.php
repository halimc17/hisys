<?php
require_once('master_validation.php');
require_once('config/connection.php');

        $txtfind=$_POST['txtfind'];
        $str=" select * from ".$dbname.".keu_5akun where (namaakun like '%".$txtfind."%' or namaakun1 like '%".$txtfind."%' or noakun like '%".$txtfind."%' or tipeakun like '%".$txtfind."%') and level = '5'";

        echo"
        <fieldset>
        <legend>".$_SESSION['lang']['result']."</legend>
        <div style=\"width:480px; height:325px; overflow:auto;\">
        <table class=data cellspacing=1 cellpadding=2  border=0>
             <thead>
                 <tr class=rowheader>
                 <td align=center>No.</td>
                 <td align=center>".$_SESSION['lang']['noakun']."</td>
                 <td align=center>".$_SESSION['lang']['namaakun']."</td>
                 <td align=center>".$_SESSION['lang']['tipe']."</td>
                 <td align=center>".$_SESSION['lang']['matauang']."</td>
                 <td align=center>".$_SESSION['lang']['kodeorg']."</td>
                 </tr>
                 </thead>
                 <tbody>";
        $no=0;	 
        
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
        {
                $no+=1;
                if($_SESSION['language']=='EN'){
                    $z=$bar->namaakun1;
                }else{
                    $z=$bar->namaakun;
                }
                echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setNoakun('".$bar->noakun."','".$z."','".$bar->tipeakun."','".$bar->matauang."','".$bar->kodeorg."')\" title='Click' >
                      <td align=center>".$no."</td>
                      <td align=center>".$bar->noakun."</td>
					  <td>".$z."</td>
					  <td>".$bar->tipeakun."</td>
					  <td align=center>".$bar->matauang."</td>
					  <td align=center>".$bar->kodeorg."</td>
					 </tr>";
        }	 
        echo "</tbody>
              <tfoot>
                  </tfoot>
                  </table></div></fieldset>";	   	

?>