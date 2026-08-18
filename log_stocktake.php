<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/log_stocktake.js?ver=1.3'></script>

<?php

$arr = "##kdgudang##klbrg##periode##unit";

$optunit = "";
$optunit.="<option value=''>Pilih Data</option>";
$strunit = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$resunit=$owlPDO->query($strunit) or die(print " Gagal: ".PDOException::getMessage());
$resunit->setFetchMode(PDO::FETCH_OBJ);
while ($barunit = $resunit->fetch()) {
    $optunit.="<option value='" . $barunit->kodeorganisasi . "'>" . $barunit->kodeorganisasi . " - " . $barunit->namaorganisasi . "</option>";
}

$optKebun = "";
$optKebun.="<option value=''>Pilih Data</option>";
$strKebun = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='gudang'";
$resKebun=$owlPDO->query($strKebun) or die(print " Gagal: ".PDOException::getMessage());
$resKebun->setFetchMode(PDO::FETCH_OBJ);
while ($barKebun = $resKebun->fetch()) {
    $optKebun.="<option value='" . $barKebun->kodeorganisasi . "'>" . $barKebun->kodeorganisasi . " - " . $barKebun->namaorganisasi . "</option>";
}

$optbrg = "";
$optbrg.="<option value=''>Pilih Data</option>";
$strbrg = "select kode,kelompok from " . $dbname . ".log_5klbarang where status='1'";
$resbrg=$owlPDO->query($strbrg) or die(print " Gagal: ".PDOException::getMessage());
$resbrg->setFetchMode(PDO::FETCH_OBJ);
while ($barbrg = $resbrg->fetch()) {
    $optbrg.="<option value='" . $barbrg->kode . "'>" . $barbrg->kode . " [ " . $barbrg->kelompok . " ]</option>";
}

$optper = "";
//$optper.="<option value=''>Pilih Data</option>";
$strper= "select distinct periode as periode from " . $dbname . ".log_5saldobulanan";
$resper=$owlPDO->query($strper) or die(print " Gagal: ".PDOException::getMessage());
$resper->setFetchMode(PDO::FETCH_OBJ);
while ($barper = $resper->fetch()) {
    $optper.="<option value='" . $barper->periode . "'>" . $barper->periode . "</option>";
}


include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('log_stocktake').'</span>');

echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=loaddatadt(0)>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset hidden><legend>".$_SESSION['lang']['find']."</legend>"; 
		/*	echo $_SESSION['lang']['noso'].":<input type=text id=txtsearchkontrak size=25 maxlength=50 class=myinputtext>";			
                        echo "NO. BA:<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>";
			echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
			echo"<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>";*/
echo"</fieldset></td>
     </tr>
	 </table> "; 

CLOSE_BOX();

echo"<div id=formInput style=display:none;>";
OPEN_BOX();
echo"<fieldset>
     <legend>Filter</legend>
	 <table>
	  <tr>
	   <td>Unit</td>
	   <td><select id=\"unit\" name=\"unit\" style=\"width:150px\">" . $optunit . "</select></td>
	 </tr>
	 <tr>
	   <td>Kode Gudang</td>
	   <td><select id=\"kdgudang\" name=\"kdgudang\" style=\"width:150px\">" . $optKebun . "</select></td>
	 </tr>
	 <tr hidden>
	   <td>Kelompok Barang</td>
	   <td><select id=\"klbrg\" name=\"klbrg\" style=\"width:150px\">" . $optbrg . "</select></td>
	 </tr>
	 <tr>
	   <td>Periode</td>
	   <td><select id=\"periode\" name=\"periode\" style=\"width:150px\">" . $optper . "</select></td>
	 </tr>
	 </table>
         <button class=mybutton onclick=simpanht('log_slave_stocktake','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>
         <button class=mybutton onclick=cancelIsi()>" . $_SESSION['lang']['cancel'] . "</button>
     </fieldset>";
CLOSE_BOX();
echo"</div>";

echo"<div id=formInputdetail style=display:none;>";
OPEN_BOX();
/*echo "<fieldset style='width:200px' ><legend>" . $_SESSION['lang']['find'] . "</legend>";
echo "<td>".$_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class='myinputtext' id='tglcr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /></td>";
echo"<td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";
echo"</table>";
echo"</fieldset>";*/
echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td rowspan=2>Store Code</td>
	   <td rowspan=2 align=center>Name</td>
	   <td rowspan=2 align=center>Part Number</td>
	   <td rowspan=2 align=center>Bin<br>Unit</td>
	   <td >Computer</td>
	   <td >Physical</td>
	   <td >Bin Card</td>
	   <td  rowspan=2>Variance</td>
	   <td  rowspan=2>REMARK</td>
	   <tr>
	   <td>Quantity</td>
	   <td>Quantity</td>
	   <td>Quantity</td>
	  
	   </tr>
	  </tr>
	 </thead>
	 <tbody id=container>";
//echo"<script>loadData(0)</script>";
echo"</tbody>
     <tfoot id='footData'>
     </tfoot>
     </table>
     </fieldset>";
CLOSE_BOX();
echo"</div>";

echo"<div id=formloaddata style=display:blok;>";
OPEN_BOX();
// $str="select * from log_stocktakeht where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
// $tab="";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$no+=1;
// 	$tab.="<tr class=rowcontent>";
// 	$tab.="<td>".$no."</td>";  
// 	$tab.="<td>".$bar['kodegudang']."</td>";  
// 	$tab.="<td>".$bar['kodeorg']."</td>";  
// 	$tab.="<td>".$bar['periode']."</td>"; 
// 	$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
// 	onclick=edit('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."')></td>";
// 	$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
// 	onclick=del('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."')></td>";
// 	$tab.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','html');\" ></td>";
// 	$tab.="<td align=center><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','excel');\" ></td>";
// 	/*$tab.="<td align=center><img src=images/skyblue/pdf.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','pdf');\" ></td>";*/   
// 	$tab.="</tr>";

// }


echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td rowspan=2>No</td>
	   <td align=center>Kode Gudang</td>
	   <td align=center>Unit</td>
	   <td align=center>Perode</td>
	   <td colspan=5 align=center>Aksi</td>
	  </tr>
	 </thead>
	 <tbody id=container1>";
	 echo"<script>loaddatadt(0)</script>";
     // echo $tab;
echo"</tbody>
     <tfoot id='footData'>
     </tfoot>
     </table></fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>