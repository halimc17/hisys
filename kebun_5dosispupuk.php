<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/kebun_5dosispupuk.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
$pupuk=$org=$divisi=$blok=$tt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$tahundet=$pupuksdet=$orgsdet=$divisisdet=$bloksdet=$ttsdet="<option value=''>".$_SESSION['lang']['all']."</option>";
$where="";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	#tidak ada apa apa disini, alias munculkan semua
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	#hanya ro ke bawah
	$where=" and kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where tipe!='HOLDING')";
} else {
	$where=" and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}			

$sql = "SELECT * FROM " . $dbname . ".organisasi where 1=1 ".$where."";
$res = fetchdata($sql);
foreach($res as $bar){
	if($bar['tipe']=='KEBUN'){		
		$org.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		$orgsdet.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	}else if($bar['tipe']=='AFDELING'){
		$divisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		$divisisdet.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	}else if($bar['tipe']=='BLOK'){
		$blok.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
		$bloksdet.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
	}
}

$sql = "SELECT * FROM " . $dbname . ".log_5masterbarang where kelompokbarang='311' and inactive='0'";
$res = fetchdata($sql);
foreach($res as $bar){
    $pupuk.="<option value=" . $bar['kodebarang'] . " ".$s.">" . $bar['kodebarang'] . " - " . $bar['namabarang'] . "</option>";
    $pupuksdet.="<option value=" . $bar['kodebarang'] . " ".$s.">" . $bar['kodebarang'] . " - " . $bar['namabarang'] . "</option>";
}

$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
$res = fetchdata($sql);
foreach($res as $bar){
    $tt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
}

$optprd = "<option value=''></option>";
/* $sql = "SELECT distinct periode FROM " . $dbname . ".kebun_5basispanen2 order by periode desc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    @$optprd.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}
 */
$tahun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$tahun.="<option value=".(date("Y")+2).">".(date('Y')+2)."</option>";
$tahun.="<option value=".(date("Y")+1).">".(date('Y')+1)."</option>";
$tahun.="<option selected value=".date("Y").">".date('Y')."</option>";
$tahun.="<option value=".(date("Y")-1).">".(date('Y')-1)."</option>";


$sql = "SELECT distinct substr(periodepemupukan,1,4) as tahun FROM " . $dbname . ".kebun_rekomendasipupuk order by tahun desc";
$res = fetchdata($sql);
foreach($res as $bar){
    $tahundet.="<option value=" . $bar['tahun'] . ">" . $bar['tahun'] . "</option>";
}


$bulan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrapl=array('01'=>'Januari','02'=>'Febuari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'Nopember','12'=>'Desember');
foreach ($arrapl as $key => $val){
	$bulan.="<option value=".$key.">".$val."</option>";
}

$apl = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrapl=array('1'=>'Satu','2'=>'Dua','3'=>'Tiga','4'=>'Empat','5'=>'Lima','6'=>'Enam','7'=>'Tujuh','8'=>'Delapan','9'=>'Sembilan','10'=>'Sepuluh','11'=>'Sebelas','12'=>'Dua Belas','1e'=>'Extra Satu','2e'=>'Extra Dua','3e'=>'Extra Tiga');
foreach ($arrapl as $key => $val){
	$apl.="<option value=".$key.">".$val."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5dosispupuk').'</span>');
echo"<div id=action_list>"; //buka div
echo"<table border=0>
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
				<td><select style=\"width:150px;\" onchange=loaddata(); id=kodeorgs>" . $orgsdet. "</select></td>
			
				<td>" . $_SESSION['lang']['divisi'] . "</td>
				<td>:</td>
				<td><select style=\"width:150px;\"  onchange=loaddata(); id=divisis>" . $divisisdet . "</select></td>
			</tr>
			<tr>

				<td>" . $_SESSION['lang']['tahun'] . "</td>
				<td>:</td>
				<td><select style=\"width:150px;\"  onchange=loaddata(); id=tts>" . $tahundet . "</select></td>
				
				<td>" . $_SESSION['lang']['pupuk'] . "</td>
				<td>:</td>
				<td><select style=\"width:150px;\"  onchange=loaddata(); id=pupuks>" . $pupuksdet . "</select>
				<img id='pupuks' onclick=z.elSearch('pupuks',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
            </tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset>";
echo"</tr></table> ";
	
CLOSE_BOX();
echo "</div>";
echo"<div id=listData style=display:block>";
OPEN_BOX();
	echo "
		<div class='table-scroll'>
		<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>
		<thead>
			<tr>
				<th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['tahun'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['divisi'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['pupuk'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['jumlah'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['updateby'] . "</th>
				<th align=center rowspan=2 colspan='5'>" . $_SESSION['lang']['action'] . "</th>
			</tr>
		</thead>
		 <tbody id=contain> 
			<script>loaddata(0)</script>
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
		 </div>";
CLOSE_BOX();
echo "</div>";
echo "<div id=header style=display:none>";
OPEN_BOX();
echo "<fieldset>
	<legend>Input</legend>
	<table>
		<tr>
			<td>" . $_SESSION['lang']['kodeorg'] . "</td>
			<td>:</td>
			<td><select style=\"width:150px;\" onchange=getdata('kodeorg'); onmousemove=hapuswarna(this.id); id=kodeorg>" . $org . "</select></td>
		
			<td>" . $_SESSION['lang']['divisi'] . "</td>
			<td>:</td>
			<td><select style=\"width:150px;\" onchange=getdata('divisi'); onmousemove=hapuswarna(this.id); id=divisi>" . $divisi . "</select></td>

			<td>" . $_SESSION['lang']['tahuntanam'] . "</td>
			<td>:</td>
			<td><select style=\"width:150px;\" onchange=getdata('tt'); onmousemove=hapuswarna(this.id); id=tt>" . $tt . "</select></td>
		</tr>
		<tr>	
			<td>" . $_SESSION['lang']['blok'] . "</td>
			<td>:</td>
			<td><select style=\"width:150px;\"  onchange=getluas(); onmousemove=hapuswarna(this.id); id=blok>" . $blok . "</select>
			<img id='akundari' onclick=z.elSearch('akundari',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
		
			<td>" . $_SESSION['lang']['luas'] . "</td>
			<td>:</td>
			<td><input style=\"width:145px;\" disabled onmousemove=hapuswarna(this.id); type=text id=luas nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
			
			<td>" . $_SESSION['lang']['pokok'] . "</td>
			<td>:</td>
			<td><input style=\"width:145px;\" disabled onmousemove=hapuswarna(this.id); type=text id=pokok nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
			
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['pupuk'] . "</td>
			<td>:</td>
			<td><select style=\"width:150px;\" onmousemove=hapuswarna(this.id); id=pupuk>" . $pupuk . "</select>
			<img id='pupuk' onclick=z.elSearch('pupuk',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
						
			<td>Aplikasi</td>
			<td>:</td>
			<td><select style=\"width:150px;\" onmousemove=hapuswarna(this.id); id=apl>" . $apl . "</select></td>

			<td>Jenis Tanah</td>
			<td>:</td>
			<td><input style=\"width:145px;\" disabled onmousemove=hapuswarna(this.id); type=text id=jenistanah nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
			
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['dosis'] . " <i>(Kg/Pkk)</i></td>
			<td>:</td>
			<td><input style=\"width:145px;\" onkeyup=hitungjlh(this.id); onmousemove=hapuswarna(this.id); type=text id=dosis nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
			
			<td>" . $_SESSION['lang']['jumlah'] . " Kg</td>
			<td>:</td>
			<td><input style=\"width:145px;\" onkeyup=hitungjlh(this.id); onmousemove=hapuswarna(this.id); type=text id=jumlah nkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"/></td>
			
			<td>" . $_SESSION['lang']['bulan'] . "</td>
			<td>:</td>
			<td><select style=\"width:75px;\" onmousemove=hapuswarna(this.id); id=bulan>" . $bulan . "</select>
				<select style=\"width:70px;\" onmousemove=hapuswarna(this.id); id=tahun>" . $tahun . "</select></td>
			
		</tr>
		<tr>
			<td></td><td></td>
			<td>
				<input type=hidden id=pupukold><input type=hidden id=aplold><input type=hidden id=bulanold><input type=hidden id=tahunold>
				<input type=hidden id=method value='insert'>
				<button id=tomboldetail class=mybutton onclick=savedetail()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batal class=mybutton onclick=cleardetail()>" . $_SESSION['lang']['cancel'] . "</button>
				<button id=batal class=mybutton onclick=showupload()>" . $_SESSION['lang']['upload'] . "</button>
			</td>
		</tr>
</table>
</fieldset>
";
CLOSE_BOX();
OPEN_BOX();
echo"
	<fieldset>
	<legend>Find</legend>
		<table>
			<tr>
				<td>" . $_SESSION['lang']['kodeorg'] . "</td>
				<td>:</td>
				<td><select style=\"width:150px;\" onchange=loaddatadetail(); id=kodeorgsdet>" . $orgsdet. "</select></td>
			
				<td>" . $_SESSION['lang']['divisi'] . "</td>
				<td>:</td>
				<td><select style=\"width:150px;\" onchange=loaddatadetail(); id=divisisdet>" . $divisisdet . "</select></td>

				<td>" . $_SESSION['lang']['tahun'] . "</td>
				<td>:</td>
				<td><select style=\"width:70px;\" onchange=loaddatadetail(); id=ttsdet>" . $tahundet . "</select></td>
				
				<td>" . $_SESSION['lang']['pupuk'] . "</td>
				<td>:</td>
				<td><select style=\"width:150px;\" onchange=loaddatadetail(); id=pupuksdet>" . $pupuksdet . "</select>
				<img id='pupuksdet' onclick=z.elSearch('pupuksdet',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
			</tr>
			<tr>
				
				<td></td>
				<td></td>
				<td>
				<button id=tomboldetail class=mybutton onclick=loaddatadetail()>" . $_SESSION['lang']['preview'] . "</button>
			</td>
			
			</tr>
		</table>
		</fieldset><div style=clear:both></div>";
		
echo"<fieldset><legend>List Data</legend>
	<div id=detail style=display:none class='table-scroll'>
	 <table cellpading=1 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['divisi'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['tahuntanam'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['blok'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['pupuk'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['luas'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['pokok'] . "</th>
                    <th align=center rowspan=2>Aplikasi</th>
                    <th align=center rowspan=2>Jenis Tanah</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['dosis'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['jumlah'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['bulan'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['updateby'] . "</th>
                    <th align=center rowspan=2 colspan=2>" . $_SESSION['lang']['action'] . "</th>
				</tr>
            </thead>
             <tbody id=containdet> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footDatadet>
             </tfoot>
             </table>
			 
	</fieldset>";
CLOSE_BOX();
echo"</div>";
echo "<div id=upload style=display:none>";
OPEN_BOX();
echo"<fieldset><legend>Form</legend><div id=viewupload style='overflow:auto;width:100%';></div></fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>