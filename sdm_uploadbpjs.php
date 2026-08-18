<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
?>
<script language=javascript1.2 src='js/sdm_uploadbpjs.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php
$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(1) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgsch.="</optgroup>";
	}
}


$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
$res = fetchdata($str);
foreach($res as $bar){
    $optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
    $optperiodex.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif='1' order by no asc";
$res = fetchdata($str);
foreach($res as $bar){
    $opttipe.="<option value='".$bar['id']."'>".$bar['tipe']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_uploadbpjs').'</span>');
echo"<div id=action_list>";
echo"<table border=0>
     <tr valign=middle>	 
		<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td>
			<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
				<table>
					<tr>
						<td>" . $_SESSION['lang']['kodeorg'] . "</td> 
						<td>:</td>
						<td><select class='select2' id=kodeorgsch onchange='loaddata()' style=\"width:150px;border-color:blue;\">".$optorgsch."</select></td>
						
						<td>" . $_SESSION['lang']['periode'] . "</td> 
						<td>:</td>
						<td><select class='select2' id=periodesch onchange='loaddata()' style=\"width:150px;border-color:blue;\">".$optperiodex."</select></td>
						
					</tr>
				</table>
			</fieldset>
		</td>
		</tr></table>";

echo "</div>";
CLOSE_BOX();

echo"<div id=inputdata style=display:none>";
OPEN_BOX();
echo"
	<fieldset style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>
		<table border=0>
			<tr>
				<td>" . $_SESSION['lang']['periode'] . "</td>
				<td>:</td>
				<td><select class=select2 id=periode style=\"width:275px;\">" . $optperiode . "</select></td>
			</tr>
			<tr>		
				<td>" . $_SESSION['lang']['kodeorg']."</td>
				<td>:</td>
				<td><select class=select2 id=kodeorg style=\"width:275px;\">" . $optorg . "</select></td>
			</tr>
			<tr>		
				<td>" . $_SESSION['lang']['tipekaryawan'] . "</td>
				<td>:</td>
				<td><select class=select2 id=tipekary style=\"width:275px;\">" . $opttipe . "</select></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<button class=mybutton id=formuploaddt onclick=formupload() style=width:150px;color:red;>Download Template</button>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['upload'] . "</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<button class=mybutton onclick=fileSelected('') style=width:150px;color:blue;>Upload Data</button>
				</td>
			</tr>
		</table>
		
	</fieldset>
	";
CLOSE_BOX();
echo "</div>";


OPEN_BOX();
$bulan=range(1,12);

#untuk inputan baru
echo"<div id=contdetail style=display:none;>
		<div id=continputdata></div>";
echo"</div>";

#list data
echo"<div id=listData style=display:block>";
echo"<div id=contain>
			<script>loaddata(0)</script>";
echo "</div>";
echo "</div>";

CLOSE_BOX();
echo close_body();
?>