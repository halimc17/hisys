<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src="js/zTools.js"></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<script language=javascript1.2>
function hapusGaji() {
    periode = getValue('periode');
    karyawanid = getValue('karyawanid');
    komponen = getValue('komponen');
    tipekaryawan = getValue('tipekaryawan');
    param = 'periode=' + periode + '&karyawanid=' + karyawanid + '&komponen=' + komponen + '&tipekaryawan=' + tipekaryawan;
    tujuan = 'sdm_slave_hapusSlyip.php';
    if (confirm("Delete ?"))
        post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    alert(con.responseText);

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
</script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_3hapusSlipGaji').'</span><br>');
$str="select distinct periode from ".$dbname.".sdm_5periodegaji 
     where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by periode desc";
$optper='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optper.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}
$str="select namakaryawan,karyawanid,subbagian, nik from ".$dbname.".datakaryawan 
    where lokasitugas='".$_SESSION['empl']['lokasitugas']."' order by namakaryawan";
$optkar='<option value=all>'.$_SESSION['lang']['all'].'</option>';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." [".$bar->nik."]</option>";
}
#ambil komponen
$str="select id,name from ".$dbname.".sdm_ho_component order by name";
$optkom='<option value=all>'.$_SESSION['lang']['all'].'</option>';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optkom.="<option value='".$bar->id."'>".$bar->name."</option>";
}
echo"<fieldset style='float:left'><legend>Form</legend>
    <table>
     <tr><td>".$_SESSION['lang']['namakaryawan']."</td><td><select id=karyawanid class=select2 style='width:150px;'>".$optkar."</select></td></tr>
     <tr><td>".$_SESSION['lang']['periode']."</td><td><select id=periode  class=select2 style='width:150px;'>".$optper."</select></td></tr>
     <tr><td>".$_SESSION['lang']['namakomponen']."</td><td><select id=komponen  class=select2 style='width:150px;'>".$optkom."</select></td></tr>  
     <tr><td>".$_SESSION['lang']['sistemgaji']."</td><td><select id=tipekaryawan  class=select2 style='width:150px;'><option value='all'>".$_SESSION['lang']['all']."</option><option value='Bulanan'>".$_SESSION['lang']['bulanan']."</option><option  class=select2 value='Harian'>".$_SESSION['lang']['harian']."</option></select></td></tr>    
	 <tr>
		<td></td><td>			
			 <button class=mybutton onclick=hapusGaji()>".$_SESSION['lang']['delete']."</button>
		</td>
	 </tr>
	 
     </table>
	
	 </fieldset>";
CLOSE_BOX();
echo close_body();
?>