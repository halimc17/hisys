<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
?>
<script language=javascript src='js/pmn_kontrakbeli.js?v=<?= time(); ?>'></script>
<?php
$optunit=$optakunht=$opttipetransaksi=$optmatauang=$optunitpenerima=$optnorekpenerima="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opthutangunit=$optalokasi=$optaruskas=$optbarang=$optsupplier ="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
#= untuk unit ht
$nmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
// $arrunit=array();
// $arrunit=getOrgDetail(9);
// foreach($arrunit as $val=>$nama){
// 	if($val != $_SESSION['empl']['lokasitugas']){
//     	$optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
// 	} else {
//     	$optunit.="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']." - ".$nmOrg[$_SESSION['empl']['lokasitugas']]."</option>";
// 	}
// } 

// Ambil unit dari organisasi dengan tipe 'PABRIK'
$str = "SELECT kodeorganisasi, namaorganisasi FROM ".$dbname.".organisasi WHERE tipe='PABRIK' ORDER BY kodeorganisasi";
$res = fetchdata($str);
foreach($res as $row) {
    if($row['kodeorganisasi'] != $_SESSION['empl']['lokasitugas']){
        $optunit .= "<option value='".$row['kodeorganisasi']."'>".$row['kodeorganisasi']." - ".$row['namaorganisasi']."</option>";
    } else {
        $optunit .= "<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']." - ".$nmOrg[$_SESSION['empl']['lokasitugas']]."</option>";
    }
}

$arrjenis=array('prd'=>'Periode','vol'=>'Volume');
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrjenis as $key => $val){
	$optjenis.="<option value='".$key."'>".$val."</option>";
}

$str="select * from ".$dbname.".log_5supplier where status=1 and supplierid in  (select supplierid from ".$dbname.".log_5supkelompok where tipe like '%SALES%' or tipe like '%TBS%') order by namasupplier asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optsupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
}

$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang=400";
$res=fetchdata($str);
foreach($res as $bar){
	$optbarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}

echo"<div>";
OPEN_BOX('','<span class=judul>'.getMenu('pmn_kontrakbeli').'</span>');
echo"<table border=0>
	<tr>
		<td align=center style='width:100px;cursor:pointer;' onclick=newdata()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
		<td>
		<fieldset>
		<legend>".$_SESSION['lang']['find']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['NoKontrak']."</td>
				<td>:</td>		
				<td>
					<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:150px;\">
				</td>				
			
				<td style='padding-left:10px'>".$_SESSION['lang']['unit']."</td>
				<td>:</td>		
				<td>
					<select class='select2' id='kodeunitsch' style=\"width:154px;\">".$optunit."</select>
				</td>
			
				<td style='padding-left:10px'>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalsch name=tanggalsch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['supplier']."</td>
				<td>:</td>		
				<td>
					<select class='select2' id='kodesuppliersch' style=\"width:154px;\">".$optsupplier."</select>
					<img id=kodesuppliersch onclick=z.elSearch('kodesuppliersch',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
				</td>
				
				<td style='padding-left:10px'>".$_SESSION['lang']['produk']."</td>
				<td>:</td>		
				<td>
					<select class='select2' id='kodebarangsch' style=\"width:154px;\">".$optbarang."</select>
				</td>
			
				<td style='padding-left:10px'>".$_SESSION['lang']['jenis']."</td>
				<td>:</td>		
				<td>
					<select class='select2' id='jenissch' style=\"width:154px;\">".$optjenis."</select>
				</td>
			</tr>
			<tr>
			<td></td><td></td>
            <td colspan=3><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button></td>
        </tr>
		</table>
		</fieldset>
		</td>
	</tr>
</table> "; 
CLOSE_BOX();
echo "</div>";

echo"<div id=listdata style=display:block>";
OPEN_BOX();
echo "<div class='table-scroll'>
		<table cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
					<th rowspan=2 align=center>".$_SESSION['lang']['nourut']."</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['NoKontrak']."</th>
                    <th rowspan=2 align=center>".$_SESSION['lang']['unit']."</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['jenis']."</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['supplier']."</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['npwp']." </th>
					<th rowspan=2 align=center>".$_SESSION['lang']['produk']."</th>
                    <th rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</th>
                    <th colspan=2 align=center>".$_SESSION['lang']['periode']."</th> 
                    <th rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</th> 
                    <th rowspan=2 align=center>".$_SESSION['lang']['dibuatoleh']."</th> 
                    <th rowspan=2 align=center>".$_SESSION['lang']['perubahan']." </th> 
                    <th rowspan=2 align=center>".$_SESSION['lang']['status']." </th> 
                    <th rowspan=2 align=center>".$_SESSION['lang']['posted']." </th>   
                    <th rowspan=2 align=center colspan=3>".$_SESSION['lang']['action']." </th> 
                </tr>  
                <tr class=rowheader>
				    <th  align=center>".$_SESSION['lang']['mulai']." </th> 
				    <th  align=center>".$_SESSION['lang']['sampai']." </th> 
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
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span>');



$arrht="###notransaksi###tanggal###kadaluwarsa###kodeunit###tanggaldari###tanggalsampai";
$arrht.="###reffharga###jenis###volume###keterangan###kodesupplier###batasbawah";
$arrht.="###kodebarang###batasatas###dropship###kball";
$style="";
echo "<fieldset>";
echo "<legend><b>".$_SESSION['lang']['form']."</b></legend>
	<table cellspacing=1 border=0>
	<tr>
		<td ".$style.">".$_SESSION['lang']['NoKontrak']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 class=myinputtext style=\"width:150px;\" placeholder='generate otomatis' disabled></td>
		
		<td ".$style.">".$_SESSION['lang']['supplier']."</td>
		<td>:</td>		
		<td>
			<select id=kodesupplier  style=\"width:155px;\">'".$optsupplier."'</select>
			<img id=kodesupplier onclick=z.elSearch('kodesupplier',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
		</td>	
		
		
		<td valign=top ".$style.">".$_SESSION['lang']['keterangan']."</td>	
		<td valign=top>:</td>
		<td colspan=5 rowspan=4 valign=top><textarea rows='4' id=keterangan placeholder='keterangan' type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:150%;\"></textarea></td>
	</tr>
	<tr>
		<td ".$style.">".$_SESSION['lang']['pabrik']."</td>
		<td>:</td>		
		<td>
			<select id=kodeunit  style=\"width:155px;\">'".$optunit."'</select>
		</td>
		
		<td ".$style.">".$_SESSION['lang']['produk']."</td>
		<td>:</td>		
		<td>
			<select id=kodebarang  style=\"width:154px;\">'".$optbarang."'</select>
		</td>
		
		
	</tr>
	<tr>	
		<td ".$style.">".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kontrak']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal name=tanggal  style=\"width:150px;\" readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10>	
		</td>
		
		<td ".$style.">Kelas Buah All</td>
		<td>:</td>		
		<td><input type=checkbox onchange=yesno('yesno2','kball'); id=kball>&nbsp;<span id=yesno2>".$_SESSION['lang']['tidak']."</span></td>
	</tr>
	<tr>	
		<td ".$style.">".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggaldari name=tanggaldari readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>	s/d
			<input type=text class=myinputtext id=tanggalsampai name=tanggalsampai  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>	
		</td></tr>
	
	<tr hidden>
		<td ".$style.">".$_SESSION['lang']['jenis']."</td>
		<td>:</td>		
		<td>
			<select id=jenis  style=\"width:155px;\">'".$optjenis."'</select>
		</td>
		
	
		<td ".$style.">".$_SESSION['lang']['volume']." (Kg)</td>
		<td>:</td>		
		<td><input type=text id=volume class=myinputtextnumber onkeyup=\"z.numberFormat('volume',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
		
		<td ".$style.">".$_SESSION['lang']['batasatas']." (%)</td>
		<td>:</td>		
		<td><input type=text id=batasatas class=myinputtextnumber onkeyup=\"z.numberFormat('batasatas',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
		
		<td ".$style.">".$_SESSION['lang']['batasbawah']." (%)</td>
		<td>:</td>		
		<td><input type=text id=batasbawah class=myinputtextnumber onkeyup=\"z.numberFormat('batasbawah',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>	

		<td ".$style.">".$_SESSION['lang']['batas']." ".$_SESSION['lang']['kadaluwarsa']." (Bln)</td>
		<td>:</td>		
		<td><input type=text id=kadaluwarsa class=myinputtextnumber onkeyup=\"z.numberFormat('kadaluwarsa',2)\" nkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\" onkeypress=\"return angka_doang(event);\"/></td>		
		
		<td ".$style.">Referensi Harga</td>
		<td>:</td>		
		<td><input type=text id=reffharga class=myinputtext style=\"width:150px;\"></td>
		
		<td ".$style.">Dropship</td>
		<td>:</td>		
		<td><input type=checkbox onchange=yesno('yesno','dropship'); id=dropship>&nbsp;<span id=yesno>".$_SESSION['lang']['tidak']."</span></td>
		
		
	</tr>
	<tr>
		<td align=center colspan=2></td>
		<td>
			<input hidden id=methodht value=saveht>
			<button id='saveht' class=mybutton onclick=saveht('".$arrht."')>".$_SESSION['lang']['save']."</button>&nbsp;
			<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo"</div>";

$border='0';
echo "<div id=detail style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
echo "<div id=isidetail></div>";
CLOSE_BOX();
echo"</div>";
?>

<script>
	getSelect2();
</script>

<?php
echo close_body();
?>