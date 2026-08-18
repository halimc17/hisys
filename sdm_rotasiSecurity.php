<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/datakaryawan.js></script>
<script language=javascript src='js/sdm_rotasiSecurity.js'></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['mutasiantarkebun']).'</span>');
echo "<fieldset style='width:1000px'>Note : Apply only at the beginning of month</fieldset>
	  <fieldset style='width:1000px'><legend>Form</legend>
      ".$_SESSION['lang']['caripadanama']." : <input type=text id=txtnama class=myinputtext onclick=\"return tanpa_kutip(event);\" size=25>
	  <button class=mybutton onclick=cariNama()>".$_SESSION['lang']['find']."</button>
	  <br>
	  
	  <div id=container style='width:1000px; height:400px; overflow:auto'>
	  </div>
	  </fieldset>";
CLOSE_BOX();
echo close_body();
?>