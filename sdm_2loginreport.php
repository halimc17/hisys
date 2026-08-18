<?//@Copy nangkoelframework 
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src="js/sdm_2loginreport.js"></script>
<?
include('master_mainMenu.php'); 
OPEN_BOX('','<span class=judul>'.strtoupper('LOGIN REPORT').'</span><br>');

//=================ambil user active;  
$str="select a.namauser, a.karyawanid, a.status, b.namakaryawan, b.lokasitugas from ".$dbname.".user a
    left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
    where a.status='1' and b.tanggalkeluar = '0000-00-00' 
    order by a.namauser";

$optuser="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optuser.="<option value='".$bar->namauser."'>".$bar->namauser." [".$bar->namakaryawan."] ".$bar->lokasitugas."</option>";
}

?>
<fieldset style="float: left;"> 
<legend><b><?php echo "Form" ?></b></legend>
<table cellspacing="1" border="0" >
<tr>
    <td><label><?php echo $_SESSION['lang']['user']?></label></td><td>:</td>
    <td><select id=namauser style='width:200px;' onchange=document.getElementById('container').innerHTML=''><?php echo $optuser; ?></select></td>
</tr>
<tr height="20"><td><td><td><button class=mybutton onclick=getUser()><?php echo $_SESSION['lang']['preview'] ?></button></td></tr>
</table>
</fieldset>
<?

CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset><legend>Result</legend><div id=container style='width:100%;height:400px;overflow:auto;'>
</div></fieldset>";
CLOSE_BOX();
close_body();

?>
