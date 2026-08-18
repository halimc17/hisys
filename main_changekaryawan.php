<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/main_changekaryawan.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('main_changekaryawan').'</span>');
echo OPEN_THEME('Change User Employee');
echo"<br><fieldset>
     <legend><img src='images/useraccounts.png' height=40px style='vertical-align:middle;'></legend> 
	  Find User : <input type=text id=uname class=myinputtext onkeypress=\"return validat(event);\" size=20 maxlength=30 title='Enter part of username then click Find'>
	 <input type=button class=mybutton value=Find title='Click to process' onclick=getUserForActivation()>
	 <br>
	 </fieldset><br><hr>
	 <fieldset>
	 <legend>Result</legend>
	 <div id=result></div>
	 </fieldset>
	 <div id=temp></div>
	 "; 
echo CLOSE_THEME();
CLOSE_BOX();
echo close_body();
?>
