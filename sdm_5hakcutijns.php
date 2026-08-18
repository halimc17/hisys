<?//@Copy nangkoelframework

require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/sdm_5hakcutijns.js'></script>

<?php
$optjnsijn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql="select idjenis,jenisijin from ".$dbname.".sdm_5jenisijin";
// echo $sql;
// exit('error');
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch())
{
    $optjnsijn.="<option value=".$res['idjenis'].">".$res['jenisijin']."</option>"; 
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5hakcutijns').'</span>');
echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['jenisijin']."</td> 
                    <td>:</td>
                    <td><select id=jenisijin style='width:148px;' >".$optjnsijn."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['hakcuti']."</td> 
                    <td>:</td>
                    <td><input type=text  id=hakcuti onkeypress=\"return angka_doang(event)\"  class=myinputtextnumber style=\"width:150px;\"></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=cancel()>Hapus</button>
                    </td>
                </tr>
            </table></fieldset>
                        <input type=hidden id=method value='insert'>";
CLOSE_BOX();
?>

<?php
OPEN_BOX();
//ISI UNTUK DAFTAR 
echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpadding=7 width=100% cellspacing=1 border=0 class=sortable >";
echo"<thead>";
echo"<td align=center>".$_SESSION['lang']['nourut']."</td>
     <td align=center>".$_SESSION['lang']['jenisijin']."</td>
     <td align=center>".$_SESSION['lang']['hakcuti']."</td>
     <td align=center>".$_SESSION['lang']['createby']."</td>
     <td align=center>".$_SESSION['lang']['createtime']."</td>
     <td align=center>".$_SESSION['lang']['updateby']."</td>
     <td align=center>".$_SESSION['lang']['updatetime']."</td>
     <td align=center>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div>";


CLOSE_BOX();
echo close_body();                  
?>