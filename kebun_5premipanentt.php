<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/kebun_5premipanentt.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
	
	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>
<?php
$optorg=$org = "<option value=''>&nbsp;</option>";
$org.="<option value=" .$_SESSION['empl']['lokasitugas']. ">" .getNamaOrg($_SESSION['empl']['lokasitugas']). "</option>";

$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN'";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$s="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . "-" . $bar['namaorganisasi'] . "</option>";
}
$optprd = "<option value=''></option>";
$sql = "SELECT distinct periode FROM " . $dbname . ".kebun_5basispanen3 order by periode desc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    @$optprd.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$opttahuntnm = "<option value='' hidden>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "select distinct tahuntanam from ".$dbname.".setup_blok where left(kodeorg, 4) = '" .$_SESSION['empl']['lokasitugas']. "' order by tahuntanam desc ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $opttahuntnm.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
}

$optper= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
for($x=-6;$x<6;$x++){
	$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
	$optper.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
	@$optper2.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}

$optjenis2 = "<option value=''></option>";
$optjenis3 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arrjenis = getEnum($dbname,'kebun_5basispanen3','jenispremi');
foreach ($arrjenis as $key){
	@$optjenis.="<option value=".$key.">".$key."</option>";
	@$optjenis3.="<option value=".$key.">".$key."</option>";
	@$optjenis2.="<option value=".$key.">".$key."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5premipanentt').'</span>');
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
				<td>" . $_SESSION['lang']['kodeorg'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=kodeorgsch  style=\"width:100px;\">" . $optorg . "</select></td>

				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=periodesch  style=\"width:100px;\">" . $optprd . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['jenis'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=jenissch  style=\"width:100px;\">" . $optjenis2 . "</select></td>	
            </tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset>";

echo"<td valign=top><fieldset><legend>Copy dari :</legend>";
echo"<table>";
echo"<tr>
		<td>".$_SESSION['lang']['kodeorg']."</td>
		<td>:</td>
		<td><select class=select2 id=kodeorgdari  style=\"width:100px;\">" . $optorg . "</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['periode'] . "</td> 
		<td>:</td>
		<td><select class=select2 id=periodedari  style=\"width:100px;\">" . $optprd . "</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['jenis'] . "</td> 
		<td>:</td>
		<td><select class=select2 id=jenisdari  style=\"width:100px;\">" . $optjenis3 . "</select></td>
	</tr>
	</table>";
	
echo"<td valign=top><fieldset><legend>Copy ke :</legend>";
echo"<table>";
echo"<tr>
		<td>" . $_SESSION['lang']['periode'] . "</td> 
		<td>:</td>
		<td><select class=select2 id=periodeke  style=\"width:100px;\">" . $optper2 . "</select></td>
	</tr>
	<tr>
		<td>&nbsp;</td> 
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><button class=mybutton onclick=copy()>Copy</button></td>
	</tr>
	</table>";	

echo"</fieldset></td>
     </tr>
	 </table> ";
CLOSE_BOX();
echo "</div>";
echo"<div id=listData style=display:block>";
OPEN_BOX();
	echo "
	
            <div>    
            <table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['periode'] . "<br>(Berlaku)</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['jenispremi'] . "</th>
					<th align=center rowspan=2 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center colspan=2 width=50px>" . $_SESSION['lang']['basic'] . " (Jjg)</th>
					<th align=center rowspan=2 width=50px>Tidak Basis</th>
					<th align=center colspan=2>Premi Siap Basis (Rp)</th>
					<th align=center colspan=2>" . $_SESSION['lang']['lebihbasis'] . " (Rp)</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['brondol'] . "<br>Rp/Kg</th>
					<th align=center rowspan=2>Rp / HK</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['updateby'] . "</th>
					<th align=center rowspan=2 colspan=2>" . $_SESSION['lang']['action'] . "</th>
				</tr>
				 <tr class=rowheader>
                    <th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>
					<th align=center width=60px>I</th>
                    <th align=center width=60px>II</th>
					<th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>  
				</tr>
            </thead>
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
echo "<div id=header style=display:none>";
OPEN_BOX();
echo "
	<table cellpadding=2 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader style=height:25px>
                    <th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['periode'] . "<br>(Berlaku)</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['jenispremi'] . "</th>
					<th align=center rowspan=2 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center colspan=2 width=50px>" . $_SESSION['lang']['basic'] . " (Jjg)</th>
					<th align=center rowspan=2 width=50px>Tidak Basis</th>
					<th align=center colspan=2>Premi Siap Basis (Rp)</th>
					<th align=center colspan=2>" . $_SESSION['lang']['lebihbasis'] . " (Rp)</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['brondol'] . "<br>Rp/Kg</th>
					<th align=center rowspan=2>Rp / HK</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['action'] . "</th>
				</tr>
				 <tr class=rowheader style=height:25px>
                    <th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>
					<th align=center width=60px>I</th>
                    <th align=center width=60px>II</th>
					<th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>  
				</tr>
            </thead>
			<tbody>
				<tr class=rowcontent>
					<td align=center>#</td>
					<td><select class=select2 style=\"width:150px;\" onmousemove=hapuswarna(this.id) onchange=ambiltt() id=kodeorg>" . $org . "</select></td>
					<td><select class=select2 style=\"width:100px;\" onmousemove=hapuswarna(this.id); id=periode>" . $optper . "</select></td>
					<td><select class=select2 style=\"width:100px;\" onchange=getlibur(this.value); onmousemove=hapuswarna(this.id); id=jenis>" . $optjenis . "</select></td>

					<td><select class=select2 style=\"width:70px;\" onmousemove=hapuswarna(this.id); id=tahuntanam>" .$opttahuntnm. "</select></td>
			
					<td><input style=\"width:50px;\" onmousemove=hapuswarna(this.id); type=text id=basis1 nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td><input style=\"width:50px;\" onmousemove=hapuswarna(this.id); type=text id=basis2 nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td><input style=\"width:50px;\" onmousemove=hapuswarna(this.id); type=text id=tidakbasis nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td><input style=\"width:65px;\" onmousemove=hapuswarna(this.id); type=text id=siapbasis nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td><input style=\"width:65px;\" onmousemove=hapuswarna(this.id); type=text id=siapbasis2 nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td><input style=\"width:50px;\" onmousemove=hapuswarna(this.id); type=text id=lebihbasis1 nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td><input style=\"width:50px;\" onmousemove=hapuswarna(this.id); type=text id=lebihbasis2 nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td><input style=\"width:60px;\" onmousemove=hapuswarna(this.id); type=text id=brondol nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					<td><input style=\"width:60px;\" onmousemove=hapuswarna(this.id); type=text id=rphk nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
					
					<input type=hidden id=method value='insert'>
					<td>
						<button id=tomboldetail class=mybutton onclick=savedetail()>" . $_SESSION['lang']['save'] . "</button>
						<button id=batal class=mybutton onclick=cleardetail()>" . $_SESSION['lang']['cancel'] . "</button>
					</td>
			
				</tr>
			</tbody>
</table>
<span>Rp / HK isikan jika Rupiah per HK tidak sesuai dengan UMK, isikan nol jika nilai sesuai dengan UMK</span>
<hr>
";

echo"<div id=detail style=display:none></div>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>