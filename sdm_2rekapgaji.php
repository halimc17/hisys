<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src=js/sdm_2rekapgaji.js?ver=1.6></script>
<?

$sOrg="select distinct kodeorganisasi as kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=fetchData($sOrg);
foreach($res as $row=>$rOrg){
    @$optOrg.="<option value='".$rOrg['kodeorganisasi']."'>".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
    
}

$optperiode='';

$sOrg="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode asc";
$res=fetchData($sOrg);
foreach($res as $row=>$rOrg){
    @$optperiode.="<option value='".$rOrg['periode']."'>".$rOrg['periode']."</option>";
    
}



OPEN_BOX('','<span class=judul>'.getMenu('sdm_2rekapgaji').'</span>');
echo"<fieldset style='width:500px'><legend>".$_SESSION['lang']['form']."</legend>
	   <table>
	      <tr>
		      <td>".$_SESSION['lang']['pt']."</td>
			  <td>:</td>
			  <td><select style=width:200px id=pt>".$optOrg."</select></td>
		  </tr>
		
		  <tr>
		      <td>".$_SESSION['lang']['periode']."</td>
			  <td>:</td>
			  <td>
				<select id=periode>".$optperiode."</select>
			  </td>
		  </tr>
		  <tr>
		      <td colspan=2></td>
			  <td>
				<button class=mybutton onclick=\"loadLaporan()\">".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=\"pdf('event')\">".$_SESSION['lang']['pdf']."</button>
			  </td>
		  </tr>	  
	   </table>
	 </fieldset>  
    ";
CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><div id=containerlist1 style='height:400px;overflow:auto'>
      </div></fieldset>"; 
CLOSE_BOX();
echo close_body();
?>