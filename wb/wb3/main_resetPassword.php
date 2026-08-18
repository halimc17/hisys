<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/usersetting.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.strtoupper('Ubah Sandi Pengguna').'</span>');
echo OPEN_THEME('Ubah Sandi Pengguna:');
echo"<fieldset>
     <legend><img src='images/vista_icons_03.png' height=40px style='vertical-align:middle;'><b>Ubah Sandi Pengguna:</b></legend>Cari Pengguna:<input type=text id=uname class=myinputtext onkeypress=\"return validat1(event);\" size=20 maxlength=30 title='Enter part of username then click Find'>
	 <input type=button class=mybutton value='Cari' title='Click to process' onclick=getUserForResetP()>
	 <br>
	 </fieldset><br><hr>
	 <fieldset>
	 <legend>Result</legend>
	 <div id=result></div>
	 </fieldset>
	 <div id=temp></div>
	 "; 
echo CLOSE_THEME();

//menu order editor
echo"<div id=resetter style='display:none;position:absolute;'>";
echo OPEN_THEME('Ubah Sandi Pengguna:');
  echo"<input type=hidden value='' id=uid>
       <center></center>
       <div id=resetwin>
	   <table>
	   <tr><td><b>Account</b></td><td>:<b><a id=un></a></b></td></tr>
	   <tr>
	    <td>Sandi Baru</td><td>:<input class=myinputtext type=password id=newpwd1 size=15 onkeypress=\"return tanpa_kutip(event);\"><img src='images/obligatory.gif' style='height:15px;vertical-align:middle;' title='Required Element'></td></tr>
        <tr><td>Re-Type Sandi Baru</td><td>:<input class=myinputtext type=password id=newpwd2 size=15 onkeypress=\"return tanpa_kutip(event);\"><img src='images/obligatory.gif' style='height:15px;vertical-align:middle;' title='Required Element'></td></tr>
	    <tr><td colspan=2 align=right></td></tr>
		<tr><td colspan=2 align=right>
		<input type=button class=mybutton value='Simpan' onclick=saveNewPwd()>
		<input type=button class=mybutton value='Tutup' onclick=hideSetter()>
		</td></tr>
	   </table>
	   </div>";  
echo CLOSE_THEME();
echo"</div>";
CLOSE_BOX();
echo close_body();
?>
