<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/kebun_rekaptbsplasma.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
$where=$wh="";
$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
if(count($adm)==0){
	$wh= " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
# Organisasi
$optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' ".$wh." and namaorganisasi like '%PLASMA%'";
$res = fetchData($str);
foreach($res as $key => $val){
	if($_SESSION['empl']['lokasitugas']==$val['kodeorganisasi']){
		$optorg.="<option value=".$val['kodeorganisasi']." selected >".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}else{		
		$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}
}

$optprd = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(substr(tanggal,1,7)) as periode FROM " . $dbname . ".kebun_spbht order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optprd.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$optsupp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(a.supplierid) as supplierid, a.namasupplier FROM " . $dbname . ".log_5supplier a 
left join " . $dbname . ".log_5supkelompok b on a.supplierid=b.supplierid 
where b.tipe='KONTRAKTOR' order by a.namasupplier asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optsupp.="<option value=" . $bar['supplierid'] . ">" . $bar['namasupplier'] . "</option>";
}

$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN'  ";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_rekaptbsplasma').'</span>');
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
		<td>" . $_SESSION['lang']['divisi'] . "</td> 
		<td>:</td>
		<td><select id=divsch  style=\"width:100px;\">" . $optorg . "</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['tanggal'] . "</td> 
		<td>:</td>
		<td><input type=text class=myinputtext  id=tglsch onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:95px;\" /></td>
	</tr>";
echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
	<td>
	<fieldset><legend>" . $_SESSION['lang']['print'] . "</legend> 
	 <table>
		<tr>
			<td>" . $_SESSION['lang']['unit'] . "</td> 
			<td>:</td>
			<td><select id=unitexp  style=\"width:100px;\">" . $optunit . "</select></td>
			</tr>
			<tr>
			<td>" . $_SESSION['lang']['periode'] . "</td> 
			<td>:</td>
			<td><select id=perexp  style=\"width:100px;\">" . @$optper . "</select></td>
		</tr>
		";
echo"<tr><td><td><td><button class=mybutton onclick=excel(event,'kebun_slave_rekappnn.php')>" . $_SESSION['lang']['excel'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
     </tr>
	 </table> ";
CLOSE_BOX();
echo "</div>";
echo"<div id=listData style=display:block>";#style=display:block
OPEN_BOX();
echo "
	<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead>
                <tr class=rowheader>
                    <td align=center  rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
                    <td align=center rowspan='2'>" . $_SESSION['lang']['afdeling'] . "</td>
					<td align=center rowspan='2'>" . $_SESSION['lang']['namaorganisasi'] . "</td>
					<td align=center  rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align=center  rowspan='2'>Ha " . $_SESSION['lang']['panen'] . "</td>
					<td align=center  rowspan='2'>".$_SESSION['lang']['hk2']."</td>
					<td align=center  colspan='2'>" . $_SESSION['lang']['jjg'] . "</td>
					<td align=center  rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</td>
                    <td align=center  rowspan='2'>" . $_SESSION['lang']['dibuat'] . "</td>
					<td align=center rowspan='2' colspan='4'>" . $_SESSION['lang']['action'] . "</td>
                </tr> 
                <tr>
                     <td align=center>" . $_SESSION['lang']['panen'] . "</td>
					 <td align=center>Afkir</td>
                </tr>
            </thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
		</table>
		</div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";
echo "<div id=header style=display:none>"; #style=display:none
OPEN_BOX();
echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
    <tr>
		<td style=\"width:100px;\">" . $_SESSION['lang']['kodeorg'] . "</td> 
		<td>:</td>
		<td><select style=\"width:150px;\" onchange=getdivisi(this.value); id=kodeorg>" . $optorg . "</select></td>
    </tr>
	<tr>
		<td style=\"width:100px;\">Kode KUD</td> 
		<td>:</td>
		<td><select style=\"width:150px;\" id=divisi>" . $optorg . "</select></td>
    </tr>
	<tr>
		<td style=\"width:100px;\">Nama KUD</td> 
		<td>:</td>
		<td><select style=\"width:150px;\" id=supplierid>" . $optorg . "</select></td>
    </tr> 	 	
    <tr>
		<td>" . $_SESSION['lang']['periode'] . "</td> 
		<td>:</td>
		<td><select style=\"width:150px;\" id=periode>" . $optprd . "</select></td>
    </tr>
	<tr>
		<td>" . $_SESSION['lang']['tanggal'] . "</td> 
		<td>:</td>
		<td><input type=text class=myinputtext placeholder='Seluruhnya' id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:145px;\" /></td>
		
    </tr>
	<tr>
		<td>" . $_SESSION['lang']['kontraktor'] . "</td> 
		<td>:</td>
		<td><select style=\"width:150px;\" id=supplier>" . $optsupp . "</select>
			<img id='supplier' onclick=z.elSearch('supplier',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
			</td>
    </tr>
	<tr>
		<td>" . $_SESSION['lang']['pekerjaan'] . "</td> 
		<td>:</td>
		<td><select style=\"width:150px;\" id=pekerjaan><option value='611010402'>CONTRACT TRANSPORT</option></select></td>
    </tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['save'] . "</button>
			<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
		<input type=hidden id=method value='insert'>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";
echo"<div id=detail style=display:none>";
OPEN_BOX();
CLOSE_BOX();
echo"</div>";
echo close_body();
?>