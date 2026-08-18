<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/sdm_splembur.js?v=<?php echo time(); ?>"></script>

<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_splembur').'</span>');

$optper1=$optper2=$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optOrg2 = getOrgDetail(1);
##kodeorganisasi
//$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
foreach ($optOrg2 as $key => $nmorg) {
	$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (kodeorganisasi='".$key."' or induk='".$key."') ORDER BY `kodeorganisasi` ASC";
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
	while($res=$query->fetch()){
		$optOrg.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>"; 
	}
}


##persetujuan1
foreach ($optOrg2 as $key => $nmorg){
	$str="select a.namakaryawan,a.nik,b.karyawanid from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit='".$key."' and b.jenispersetujuan='SPL' and b.level='1' order by a.namakaryawan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optper1.="<option value='".$bar['karyawanid']."'>".$key."-".$bar['namakaryawan']." - ".$bar['nik']."</option>";
	}
}

foreach ($optOrg2 as $key => $nmorg){
##persetujuan2
$str="select a.namakaryawan,a.nik,b.karyawanid from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit='".$key."' and b.jenispersetujuan='SPL' and b.level='2' order by a.namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optper2.="<option value='".$bar['karyawanid']."'>".$key."-".$bar['namakaryawan']." - ".$bar['nik']."</option>";
}
}
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
		echo $_SESSION['lang']['kodeorganisasi']." : <select style=width:150px  id=kdOrgCr><option value=''></option>".$optOrg."</select>&nbsp;";
		echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
		echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
	 </tr>
	 </table>"; 
CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable >";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['kodeorg']."</td>";
echo"<td>".$_SESSION['lang']['namaorganisasi']."</td>";
echo"<td>".$_SESSION['lang']['tanggal']."</td>";
echo"<td>".$_SESSION['lang']['status']."</td>";
echo"<td colspan=4>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div>";
?>

<div id="headher" style="display:none">
<fieldset style='float:left'>
<legend><?php echo $_SESSION['lang']['header']?></legend>
<table cellspacing="1" border="0">
	<tr>
		<td><?php echo $_SESSION['lang']['unitkerja']?></td>
		<td>:</td>	
		<td><select id="kdOrg" name="kdOrg" style="width:150px;" onchange='getPersetujuan(0)' ><?php echo $optOrg;?></select></td>
		<td><?php echo $_SESSION['lang']['tanggal']?></td>
		<td>:</td>
		<td><input type="text" class="myinputtext" id="tglAbsen" name="tglAbsen" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:145px;" /></td>
	</tr>
	<tr>
		<td><?php echo $_SESSION['lang']['persetujuan']." 1"?></td>
		<td>:</td>	
		<td><select id="persetujuan1" name="persetujuan1" style="width:150px;" ><?php echo $optper1;?></select></td>
		<td><?php echo $_SESSION['lang']['persetujuan']." 2"?></td>
		<td>:</td>
		<td><select id="persetujuan2" name="persetujuan2" style="width:150px;" ><?php echo $optper2;?></select></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><button class=mybutton onclick="detailAbsn()" id="butsave" ><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick="add_new_data()"><?php echo $_SESSION['lang']['cancel']?></button>
		</td>
	</tr>
	<input type="hidden" id="proses" name="proses" value="insert"  />
	<input type="hidden" id="notransaksi" name="notransaksi" value=""  />
</table>
</fieldset>
</div>
<?php
CLOSE_BOX();

echo"<div id='detailEntry' style='display:none'>";
OPEN_BOX();
?>
<div id="detailIsi"></div>
<div id="loaddetail"></div>
<div id="contentDetail"></div>
<?php
CLOSE_BOX();
echo "</div>";

echo close_body();
?>


