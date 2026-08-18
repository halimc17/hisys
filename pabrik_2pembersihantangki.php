<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript1.2 src='js/pabrik_pembersihantangki.js?ver=1.1'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	


$optTangki="";   
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe in ('PABRIK','BULKING')";   
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch())
{
    $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}	

$optBrg="";
$opttipe=$optBrgSch="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optBrgSch.="<option value='40000001'>CRUDE PALM OIL (CPO)</option>";
$optBrgSch.="<option value='40000002'>PALM KERNEL (PK)</option>";

$opttipe.="<option value='Cuci'>Cuci</option>";
$opttipe.="<option value='Return'>Return</option>";


// $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// //persetujuan1
// $str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSCUCITANGKI' and level=1 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
	// $whr=" karyawanid='".$bar['karyawanid']."'";
	// $optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	// $optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
// }

// //persetujuan2
// $str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSCUCITANGKI' and level=2 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
	// $whr=" karyawanid='".$bar['karyawanid']."'";
	// $optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	// $optper2.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
// }

// //persetujuan3
// $str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSCUCITANGKI' and level=3 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
	// $whr=" karyawanid='".$bar['karyawanid']."'";
	// $optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	// $optper3.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
// }


#buat jam dan menit
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}
	
?>


<?php
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_2pembersihantangki').'<br><br></span>');
$arr="##pabrikRep##brgRep##tgl1Rep##tgl2Rep";
echo"<fieldset style='float:left;'><legend><b>Laporan BA Pembersihan Tangki</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=pabrikRep style=\"width:163px;\" >".$optOrg."</select></td>
        </tr>
	<tr>
		<td>".$_SESSION['lang']['namabarang']."</td>
		<td>:</td>
		<td><select id=brgRep style='width:163px;'>".$optBrgSch."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']." BA</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
		s/d
		<input type='text' class='myinputtext' id='tgl2Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>
	</tr>	

	
	<tr>
		<td><td><td>
		<button onclick=zPreview('pabrik_slave_2pembersihantangki','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2pembersihantangki.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>		
		<button onclick=batalRep() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
echo"
<div id='printContainer'>
</div>";



CLOSE_BOX();
echo close_body();




?>

<?php
/*
OPEN_BOX();
//ISI UNTUK DAFTAR 
echo "<fieldset>";
echo "<legend><b>".$_SESSION['lang']['datatersimpan']."</b></legend>";
//echo "<div id=container>";
echo" <div id=container style='width:500px;height:400px;overflow:scroll'>";	
	echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
			 <td align=center>No</td>
                         <td align=center>".$_SESSION['lang']['pabrik']."</td>
			 <td align=center>".$_SESSION['lang']['namasupplier']."</td>
			 <td align=center>".$_SESSION['lang']['tanggal']."</td>
			 <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
			 <td align=center>".$_SESSION['lang']['harga']."</td>
			 <td align=center>*</td></tr>
		 </thead>
		 <tbody id='containerData'><script>loadData()</script>";
        
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
echo "</fieldset>";
CLOSE_BOX();
echo close_body();*/					
?>