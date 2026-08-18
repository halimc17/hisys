<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_5cuti.js?v=<?php echo time(); ?>'></script>
<?


$optlokasitugas="";
$optlokasitugas.="<option value=''>".$_SESSION['lang']['all']."</option>";
if(trim($_SESSION['org']['tipeinduk'])=='HOLDING')//user holding dapat menempatkan dimana saja
{
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and length(kodeorganisasi)=4 order by namaorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
	}
}
else if(trim($_SESSION['org']['induk']!=''))//user unit hanya dapat menempatkan pada unitnya dan anak unitnya
{
     $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
			$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
	}
}
$optperiode='';
for($x=-1;$x<3;$x++)
{
	$dt=date('Y')-$x;
	$optperiode.="<option value='".$dt."'>".$dt."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('sdm_laporanCuti').'</span>');
echo"<fieldset style='width:500px'><legend>".$_SESSION['lang']['form']."</legend>
	   <table>
	      <tr>
		      <td>".$_SESSION['lang']['lokasitugas']."</td>
			  <td>:</td>
			  <td><select style=width:200px id=lokasitugas onchange=\"loadkaryawan();\">".$optlokasitugas."</select></td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['periode']."</td>
			  <td>:</td>
			  <td>
				<select id=periode onchange=\"loadkaryawan();\">".$optperiode."</select>
			  </td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['namakaryawan']."</td>
			  <td>:</td>
			  <td><select  style=width:200px id=karyawan>";
			  ?> <script>loadkaryawan();</script> <? echo "</select>
			  <img id='karyawan' onclick=z.elSearch('karyawan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		  </tr>
		  <tr>
		      <td colspan=2></td>
			  <td>
				<button class=mybutton onclick=\"loadLaporan()\">".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=\"cutiToExcel(document.getElementById('lokasitugas').options[document.getElementById('lokasitugas').selectedIndex].value,document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value,document.getElementById('karyawan').options[document.getElementById('karyawan').selectedIndex].value,event)\">".$_SESSION['lang']['excel']."</button>
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