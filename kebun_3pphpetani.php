<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript1.2 src='js/kebun_3pphpetani.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<?php

$where=$wh=$whd="";
$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
if(count($adm)==0){
	$wh= " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
	$whd= " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}
# Organisasi
$optkud=$optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' ".$wh."";
// $res = fetchData($str);
// foreach($res as $key => $val){
	// /* if($_SESSION['empl']['lokasitugas']==$val['kodeorganisasi']){
		// $optorg.="<option value=".$val['kodeorganisasi']." selected >".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	// }else{		
		// $optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	// } */
	// $optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
// }

$optdiv = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' ".$whd."";
$res = fetchData($str);
foreach($res as $key => $val){
	/* if($_SESSION['empl']['lokasitugas']==$val['kodeorganisasi']){
		$optorg.="<option value=".$val['kodeorganisasi']." selected >".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}else{		
		$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	} */
	$optdiv.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

$optprd = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optprdscr = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sql = "SELECT distinct(substr(tanggal,1,7)) as periode FROM " . $dbname . ".kebun_spbht order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optprd.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
    $optprdscr.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

#============================================================================================#

$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN'  ";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
}

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=@$n){			
		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

$sql = "SELECT distinct (supplier) as supplier FROM " . $dbname . ".kebun_tbskud order by supplier  ";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier',"supplierid='".$bar['supplier']."'");
    $optkud.="<option value=" . $bar['supplier'] . ">" . $nmsup[$bar['supplier']] . "</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('kebun_3pphpetani').'</span>');
echo"<div id=action_list>"; //buka div
echo"<table>
     <tr valign=middle>	 
		<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
		<td>
            <fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
    <table>
	<tr>
		<td>" . $_SESSION['lang']['unit'] . "</td> 
		<td>:</td>
		<td><select id=divsch class=select2 style=\"width:100px;\">" . $optorg . "</select></td>
		
		<td>" . $_SESSION['lang']['bulan'] . "</td> 
		<td>:</td>
		<td><select id=tglsch  class=select2 style=\"width:100px;\">" . $optprdscr . "</select></td>
		
		
		
		
	</tr>";
echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
	</tr></table> ";
CLOSE_BOX();
echo "</div>";
echo"<div id=listData style=display:block>";#style=display:block
OPEN_BOX();
echo "
	
		<div class='table-scroll' style=height:60vh>";
        echo"<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['notransaksi'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['unit'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['periode'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['supplier'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nilai'] . " Pph 22</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kasbank'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['status'] . "</th>
            <th align=center rowspan='2' colspan=5>" . $_SESSION['lang']['action'] . "</th>
        </tr>";
		
		echo"</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
		</table>
		</div>
	";
CLOSE_BOX();
echo "</div>";
echo "<div id=header style=display:none>"; #style=display:none
OPEN_BOX();
$res=array('0'=>'Sebulan (Tanggal : 1 s/d 30)','1'=>'Pertama (Tanggal : 1 s/d 15)','2'=>'Kedua (Tanggal : 16 s/d 30)');


foreach($res as $key => $val){
	@$optbyr.="<option value=".$key.">".$val."</option>";
}



echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>

	<tr>
		<td style=\"width:100px;\">" . $_SESSION['lang']['notransaksi'] . "</td> 
		<td>:</td>
		<td><input class=myinputtext disabled id=notransaksi style=\"width:145px;\"></td>
		
    </tr> 
    <tr>
		<td style=\"width:100px;\">" . $_SESSION['lang']['kodeorg'] . "</td> 
		<td>:</td>
		<td><select style=\"width:150px;\"  class=select2 onchange=getDivisiX(this.value,'divisi',''); id=kodeorg>" . $optorg . "</select></td>
    </tr> 
    <tr>
		<td>" . $_SESSION['lang']['bulan'] . "</td> 
		<td>:</td>
		<td><select style=\"width:150px;\"  class=select2 id=periode>" . $optprd . "</select></td>
    </tr>
	<tr hidden>
		<td>" . $_SESSION['lang']['periode'] . "</td> 
		<td>:</td>
		<td><select style=\"width:150px;\"  class=select2 id=periodebyr>" . $optbyr . "</select></td>
    </tr>
	<tr>
		<td>KUD</td> 
		<td>:</td>
		<td><select style=\"width:150px;\"  class=select2 id=kud>" . $optkud . "</select></td>
    </tr>

	<tr>
		<td colspan=2></td>
		<td>
			<button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['preview'] . "</button>
			<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
		<input type=hidden id=method value='insert'>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";
echo"<div id=detail style='display:none';>";
OPEN_BOX();
CLOSE_BOX();
echo"</div>";
echo close_body();
?>