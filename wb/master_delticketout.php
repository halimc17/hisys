<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('master_delticketout').'</span><br><br>');
echo"<fieldset style='width:400px'>
<legend>Note</legend>
<label style='font-weight:bold'>No. Tiket</label> Yang Sudah Dihapus Tidak Dapat Dikembalikan Lagi Datanya,
Harap Berhati2 dalam Melakukan Penghapusan No.Tiket.
</fieldset>";
echo"<table border=0 cellpadding=3 style=width:100%>
	<tr>
		<td>
			<label class=label style='font-size:12px;font-weight:bold'>No Tiket<br>
			<input tabindex=1 class=myinputtext style='width:250px;height:30px;font-size:12px' type=text name=notiket id=notiket onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='16' value=''>
		</td>
	</tr>
	<tr>
		<td>
			<label class=label style='font-size:12px;font-weight:bold'>Catatan<br>
			<input tabindex=2 class=myinputtext style='width:400px;height:30px;font-size:12px' type=text name=catatan id=catatan onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' maxlength='250' value=''>
		</td>
	</tr>
	<tr>
		<td>
			<button tabindex=3 class=mybutton id=tabhapus style=height:40px;>Hapus</button>
			<button tabindex=4 class=mybutton id=tabbatal style=height:40px;>Batal</button>
		</td>
	</tr>
</table>";
CLOSE_BOX();
?>
<script language=javascript src='js/master_delticketout.js?v=<?php echo time(); ?>'></script>
<?
echo close_body();
?>