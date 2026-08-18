<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/menusetting.js?v=<?php echo time(); ?>'></script>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});

});
</script>
<?
include('master_mainMenu.php');
/*ambil pengguna */
$str=$owlPDO->query("select a.namauser,b.namakaryawan,b.lokasitugas,c.namajabatan,d.nama,b.kodejabatan from ".$dbname.".user a
      left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
      left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
      left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode");
$str->setFetchMode(PDO::FETCH_OBJ);
$optPengguna="";
$optPengguna.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPengguna.="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$str->fetch()){
    $optPengguna.="<option value='".$bar->namauser."'>".$bar->namauser." - ".$bar->lokasitugas." - ".$bar->namajabatan."</option>";

}
OPEN_BOX('','<span class=judul>'.getMenu('main_copy_privileges').'</span>');
echo OPEN_THEME($_SESSION['lang']['privconf'].':');
echo"<fieldset>
     <legend><img src='images/vista_icons_03.png' height=60px style='vertical-align:middle;'>".$_SESSION['lang']['newuser']."</legend> 
	 <table>
	 <tr>
		<td rowspan=2>Tipe</td>
		<td rowspan=2>:</td>
		<td><input id=tipe name=tipe type=radio checked onclick='checktpyecopy()'>Per User</td>
		<td><input id=tipe2 name=tipe type=radio onclick='checktpyecopy()'>Per Jabatan</td>
	 </tr>
	 <tr>
		<td><input id=tipe3 name=tipe type=radio onclick='checktpyecopy()'>All Menu</td>
		<td><input id=tipe4 name=tipe type=radio onclick='checktpyecopy()'>All Title & Devider</td>
	 </tr>
	 <tr id='trnewuser'>
		<td>".$_SESSION['lang']['newuser']."</td>
		<td>:</td><td colspan=3><select class=select2 style='width:200px' id=pengguna>".$optPengguna."</select>
		<img id='pengguna' onclick=z.elSearch('pengguna',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
	 </tr>
	 <tr id='copyfrom'>
		<td>".$_SESSION['lang']['copyfrom']."</td>
		<td>:</td><td colspan=3><select class=select2 onchange=getjabatan() style='width:200px' id=dari>".$optPengguna."</select>
		<img id='dari' onclick=z.elSearch('dari',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
	</tr>
	<tr id='trjabatan' style='display:none'>
		<td>".$_SESSION['lang']['jabatan']."</td>
		<td>:</td>
		<td colspan=3>&nbsp;<input type='hidden' id='jabatan'><span id=namajabatan>".$_SESSION['lang']['pilihdata']."</span></td>
	</tr>
	 
	 
	 <tr><td colspan=4 align=center><button class=mybutton onclick=copyPrivileges()>".$_SESSION['lang']['proses']."</button></td></tr>
	 </tr>
	 </table>
	 </fieldset>
	 ";  
echo CLOSE_THEME();
echo"<div id=container></div>";
CLOSE_BOX();
echo close_body();
?>