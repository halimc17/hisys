<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/setup_mtuang.js'></script>

<?php
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['matauang'].' '.$_SESSION['lang']['dan'].' '.$_SESSION['lang']['kurs']).'</span><br>');

echo"<fieldset style='width:400px;'>
<legend><font size=2.5><b>".$_SESSION['lang']['form']."</b></legend></font>		
        <table class=sortable cellspacing=1 border=0>
                <tr class=rowheader>		
                        <td align=center>".$_SESSION['lang']['kode']."</td>
                        <td align=center>".$_SESSION['lang']['matauang']."</td>
                        <td align=center>".$_SESSION['lang']['simbol']."</td>
                        <td align=center>".$_SESSION['lang']['kode']." ISO</td>
                        <td align=center>".$_SESSION['lang']['action']."</td>
                </tr>";



$ha=$owlPDO->query("select * from ".$dbname.".setup_matauang");
$ha->setFetchMode(PDO::FETCH_ASSOC);
while($hu=$ha->fetch())
{
        $no+=1;
        echo"<tr class=rowcontent>
                        <td><input type=text maxlength=3 id=kode".$hu['kode']." value='".$hu['kode']."' onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=matauang".$hu['kode']." value='".$hu['matauang']."' onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=simbol".$hu['kode']." value='".$hu['simbol']."' onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=kodeiso".$hu['kode']." value='".$hu['kodeiso']."' onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td>
                                <img src=images/save.png class=resicon  title='Update' onclick=\"edithead('".$hu['kode']."');\" >
                                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delhead('".$hu['kode']."','".$hu['matauang']."','".$hu['simbol']."','".$hu['kodeiso']."');\" >
                                <img src=images/application/application_view_list.png class=resicon  title='View' onclick=loadData('".$hu['kode']."')>

                        </td>
        </tr>";
}
                        echo"<tr class=rowcontent>
                        <td><input type=text maxlength=3 id=kodetambah onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=matauangtambah onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=simboltambah onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=kodeisotambah onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><img src=images/application/application_add.png class=resicon  title='Save'  onclick=simpanbaru()></td>
                        </tr>";
                        echo"</table></fieldset>
                                        <input type=hidden id=method value='insert'>";//application_add

CLOSE_BOX();
OPEN_BOX();
echo "<fieldset style='width:650px;'>
		
                <legend><font size=2.5><b>".$_SESSION['lang']['kurs']."</b></legend></font>
                <input type=hidden id=kodedetail value=''>
                <div id=container style='overflow:auto;height:500px;max-width:650px'; > 

                </div>
        </fieldset>";//<script>loadData()</script>

CLOSE_BOX();
echo close_body();					
?>