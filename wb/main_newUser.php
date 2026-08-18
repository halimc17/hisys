<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/usersetting.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['newuser']).'</span>');
echo OPEN_THEME('Pengguna baru:');
echo"<fieldset>
	<legend><img src='images/user.png' height=40px style='vertical-align:middle;'><b>Tambah pengguna:</b></legend> 
	<table cellspacing=1 cellpadding=3 border=0'>
		<tbody>
		<tr>
			<td>Nama Pengguna</td>
			<td>:</td>
			<td>
				<input  class=myinputtext type=text size=20 maxlength=40 id=uname onkeypress=\"return tanpa_kutip_dan_sepasi(event);\"><img src='images/obligatory.gif' style='height:15px;vertical-align:middle;padding-left:5px' title='Required Element'>
			</td>
		</tr>
		<tr>
			<td>Sandi</td>
			<td>:</td>
			<td>
				<input  class=myinputtext type=password id=pwd1 size=20 maxlength=20 onkeypress=\"return tanpa_kutip(event);\"><img src='images/obligatory.gif' style='height:15px;vertical-align:middle;padding-left:5px' title='Required Element'>
			</td>
		</tr>
		<tr>
			<td>Re-Type Sandi</td>
			<td>:</td>
			<td>
				<input  class=myinputtext type=password id=pwd2 size=20 maxlength=20 onkeypress=\"return tanpa_kutip(event);\"><img src='images/obligatory.gif' style='height:15px;vertical-align:middle;padding-left:5px' title='Required Element'>
			</td>
		</tr>
		<tr>
			<td>Status</td>
			<td>:</td>
			<td>
				<input type=radio name=radio id=radio value=1 class=myradio checked>Active <input type=radio name=radio id=radio1 value=0 class=myradio>Not Active<br>
			</td>
		</tr>
		<tr>
			<td colspan=2 align=right>
			<td>
				<input type=button class=mybutton value='Simpan' onclick=savef()> 
				<input type=button class=mybutton value='Batal' onclick=resetf()>
			</td>
		</tr>		 
		</tbody>
	</table>  
	</fieldset><br><hr>
<div id=temp></div>"; 
echo CLOSE_THEME();
CLOSE_BOX();
echo close_body();
?>
