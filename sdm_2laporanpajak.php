<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src=js/sdm_2laporanpajak.js?ver=1.7></script>
<?

$sOrg="select distinct kodeorganisasi as kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=fetchData($sOrg);
foreach($res as $row=>$rOrg){
    @$optOrg.="<option value='".$rOrg['kodeorganisasi']."'>".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
    
}

$optperiode='';

$sOrg="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode asc";
$res=fetchData($sOrg);
foreach($res as $row=>$rOrg){
    @$optperiode.="<option value='".$rOrg['periode']."'>".$rOrg['periode']."</option>";
    
}

$sKar="select * from ".$dbname.".sdm_5tipekaryawan where id in ('1','2','3','4')";
$res=fetchData($sKar);
foreach($res as $row=>$rKar){
    @$optkar.="<option value='".$rKar['id']."'>".$rKar['tipe']."</option>";
    
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_2laporanpajak').'</span>');
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
		      <td>Tipe ".$_SESSION['lang']['pph']."</td>
			  <td>:</td>
			  <td>
				<select id=pph0>
				<option value=1>Ada PPH</option>
				<option value=2>PPH0</option>
				</select>
			  </td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['tipekaryawan']."</td>
			  <td>:</td>
			  <td>
			  <select id=tipekar>".$optkar."</select>
			
			  </td>
		  </tr>
		

		  <tr>
		      <td colspan=2></td>
			  <td>
				<button class=mybutton onclick=\"loadLaporan()\">".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=\"excel('event')\">".$_SESSION['lang']['excel']."</button>
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