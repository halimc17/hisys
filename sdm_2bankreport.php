<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/sdm_2bankreport.js?ver=1.7'></script>
<style>
	.datarekening{
		cursor:pointer;
	}
	.todelete{
		background:red;
		color:white;
	}
	.todelete:hover{
		background:#ffa0a0 !important;
		color:black;
	}
</style>
<?

$sOrg="select distinct kodeorganisasi as kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$optOrg=$optkar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
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

$sKar="select * from ".$dbname.".sdm_5tipekaryawan where id in ('1','3','4')";
$res=fetchData($sKar);
foreach($res as $row=>$rKar){
    @$optkar.="<option value='".$rKar['id']."'>".$rKar['tipe']."</option>";
    
}

/*for($x=-1;$x<3;$x++)
{
	$dt=date('Y')-$x;
	$dt1=date('M');
	$optperiode.="<option value='".$dt.'-'.$dt1."'>".$dt.'-'.$dt1."</option>";
}*/
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2bankreport').'</span>');
echo"<fieldset style='width:500px'><legend>".$_SESSION['lang']['form']."</legend>
	   <table>
	      <tr>
		      <td>".$_SESSION['lang']['pt']."</td>
			  <td>:</td>
			  <td><select style=width:200px id=pt>".$optOrg."</select></td>
		  </tr>
		   <tr hidden>
		      <td>".$_SESSION['lang']['unit']."</td>
			  <td>:</td>
			  <td><select style=width:200px id=unit></select></td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['periode']."</td>
			  <td>:</td>
			  <td>
				<select id=periode>".$optperiode."</select>
			  </td>
		  </tr>
		   <tr>
		      <td>".$_SESSION['lang']['tipekaryawan']."</td>
			  <td>:</td>
			  <td>
				<select id=tipekar onchange=\"getkar();\">".$optkar."</select>
			  </td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['namakaryawan']."</td>
			  <td>:</td>
			  <td><select  style=width:200px id=karyawan>
			  </select>
			  <img id='karyawan' onclick=z.elSearch('karyawan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
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
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<div id=containerlist1 style='height:400px;overflow:auto'>
      </div></fieldset>"; 
CLOSE_BOX();
echo close_body();
?>