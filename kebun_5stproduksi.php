<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/kebun_5stproduksi.js?v=<?php echo time(); ?>'></script>
<?
$arr="##bibit##tanah##tahuntanam##produksi##method##oldjb##oldkt##oldum##unit";
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['standardprodkebun'].' / yield').'</span>');

$optbibit="<option value=''></option>";
$str="select * from ".$dbname.".setup_jenisbibit order by jenisbibit";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optbibit.="<option value='".$bar->jenisbibit."'>".$bar->jenisbibit."</option>";
}
$opttanah="<option value=''></option>";
// $x=readCountry('config/kelastanah.lst');
$x = fetchdata("SELECT kode, nama FROM $dbname.setup_kelaslahan WHERE Aktif='1'");
foreach($x as $val)
{                    
	$opttanah.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
}

$optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$d=getNamaOrg($key,'induk');
	if($d!=$n){			
		$optkodeorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optkodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optkodeorg.="</optgroup>";
	}
}
		
                

echo"<fieldset  style='width:450px;'>
     <legend>".$_SESSION['lang']['form']."</legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['unit']."</td><td>:</td>
	   <td><select id=unit style='width:150px;'>".$optkodeorg."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['jenisbibit']."<input type='hidden' id=oldjb name=oldjb /></td><td>:</td>
	   <td><select id=bibit style='width:150px;'>".$optbibit."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['kelastanah']."<input type='hidden' id=oldkt name=oldkt /></td><td>:</td>
	   <td><select id=tanah style='width:150px;'>".$opttanah."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['tahuntanam']."<input type='hidden' id=oldum name=oldum /></td><td>:</td>
	   <td><input type=text class=myinputtext id=tahuntanam name=tahuntanam onkeypress=\"return angkadowang(event);\" style=\"width:145px;\" maxlength=5/></td>
	 </tr>	
	 <tr>
	   <td>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['produksi']." / Ha</td><td>:</td>
	   <td><input type=text class=myinputtext id=produksi name=produksi onkeypress=\"return angka_doang(event);\" style=\"width:145px;\" maxlength=10></td>
	 </tr>	 
	 <tr><td><td><td>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=saveFranco('kebun_slave_5stproduksi','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </td></td></td></tr></table></fieldset><input type='hidden' id=hiddenz name=hiddenz />";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
	 <div style='height:350px;overflow:auto;'>
	 <table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td align=center>No</td>
	   <td align=center>".$_SESSION['lang']['unit']."</td>
	   <td align=center>".$_SESSION['lang']['jenisbibit']."</td>
	   <td align=center>".$_SESSION['lang']['kelastanah']."</td>
	   <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
	   <td align=center>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['produksi']." / Ha</td>
	   <td align=center>".$_SESSION['lang']['action']."</td>
	  </tr>
     </thead>
     <tbody id=container>";
echo"<script>loadData()</script>";
echo"</tbody>
     <tfoot>
     </tfoot>
     </table><div></fieldset>";
CLOSE_BOX();
echo close_body();
?>