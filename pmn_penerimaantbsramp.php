<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<b>".strtoupper($_SESSION['lang']['penerimaantbsramp'])."</b>"); //1 O
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/pmn_penerimaantbsramp.js" /></script>
<script>
	pilData="<?php echo $_SESSION['lang']['pilihdata']?>";
</script>`

<script>function submitFile()
{
    if(confirm('Are you sure..?'))
	{
		document.getElementById('frm').submit();
    }
}
</script>

<?php
//Inisialisasi variable
$optramp = $optorganisasi = $optsupplier = $optpabrik = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPt = $optUnitKerja = $optSubBagian = $optunit = "<option value=''>".$_SESSION['lang']['all']."</option>";

### Get PT
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optorganisasi.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
	$optPt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
}

### Get Unit
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optunit.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
}

### Get Ramp
$str="select kode,kelompok from ".$dbname.".log_5klsupplier where tipe='RAMP' order by kelompok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optUnitKerja.="<option value='".$bar['kode']."'>".$bar['kode']."-".$bar['kelompok']."</option>";	
}

### Get Supplier
$str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP' and status=1)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optSubBagian.="<option value='".$bar['supplierid']."'>".$bar['supplierid']."-".$bar['namasupplier']."</option>";	
}

echo"<table>
	<tr valign=middle>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayFormUpload()>
			<img class=delliconBig src=images/skyblue/upload.png title='".$_SESSION['lang']['uploaddata']."'><br>".$_SESSION['lang']['uploaddata']."</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
		<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
		<td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
		
		echo"<table><tr>";
		
		echo"<td>".$_SESSION['lang']['perusahaan']."</td>
		<td>:</td>
		<td>
			<select id=caript style=width:175px; onchange='getcariramp()'>".$optPt."</select>
		</td>";
		
		echo"<td>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['pabrik']."</td>
		<td>:</td>
		<td>
			<select id=cariunit style=width:175px;>".$optunit."</select>
		</td>";
			
		echo"<td>".$_SESSION['lang']['koderamp']."</td>
		<td>:</td>
		<td>
			<select id=carikoderamp style=width:175px; onchange='getcarisupplier()'>".$optUnitKerja."</select>
		</td>";
		
		echo"</tr><tr>";
		
		echo"<td>".$_SESSION['lang']['kdsupplierramo']."</td>
		<td>:</td>
		<td>
			<select id=carisupplier style=width:175px;>".$optSubBagian."</select>
		</td>";
		
		echo"<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=caritanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />
		</td>";
		
		echo"</tr>";
		echo "</table>";
		echo"<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
	 </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
echo"<div style='overflow:auto'>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>No. Tiket</td>";
echo"<td>".$_SESSION['lang']['perusahaan']."</td>";
echo"<td>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['pabrik']."</td>";
echo"<td>".$_SESSION['lang']['nodo']."</td>";
echo"<td>".$_SESSION['lang']['koderamp']."</td>";
echo"<td>".$_SESSION['lang']['kdsupplierramo']."</td>";
echo"<td>".$_SESSION['lang']['tanggal']."</td>";
echo"<td>Netto</td>";
echo"<td>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']."</td>";
echo"<td>".$_SESSION['lang']['updateby']."</td>";
echo"<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['posting']."</td>";
echo"<td>".$_SESSION['lang']['posted']."</td>";
echo"<td colspan=3>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
 
echo"</tfoot></table></div></fieldset>";
echo"</div><input type=hidden id=proses value=insert />";

//===========================================================================
$arr="##instiket##tiket##nospb##nokendaraan##pt##tanggalmasuk##jammasuk##menitmasuk##beratmasuk##kodepabrik##tanggalkeluar##jamkeluar##menitkeluar##beratkeluar##koderamp##jjg##kg##netto##supplier##harga##totalrupiah##bbnPajak##totalrupiahpph##prsnAll##totalpembayaran##proses##potongan";

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

echo"<div id=formInput style=display:none;>";
$instiket = 'X';
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
	<table style=width:100%;>
		<tr>
			<td>No. Tiket</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtext' id='instiket' style='width:20px;text-align:center' onkeypress=\"return tanpa_kutip(event);\" tabindex=1 disabled value='".$instiket."' />
				<input  type='text' class='myinputtext' id='tiket' style='width:60px;' onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' tabindex=2 maxlength=7 disabled />
			</td>
			
			<td style='padding-left:20px;display:none'>No. SPB</td>
			<td style='display:none'>:</td>
			<td style='display:none'>
				<input  type='text' class='myinputtext' id='nospb' style='width:150px;' onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' tabindex=7 />
			</td>
			
			<td style='padding-left:20px;'>Waktu Masuk</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tanggalmasuk style='text-align:center' onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('d-m-Y')."' tabindex=9 readonly />
				<select id=jammasuk tabindex=11>".$jm."</select> : 
				<select id=menitmasuk tabindex=12>".$mnt."</select>
			</td>
			
			<td style='padding-left:20px;'>Berat Masuk</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='beratmasuk' style='width:80px;' onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('beratmasuk',2);getnetto();\" value='0' tabindex=12 /> Kg
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select id=pt onchange='getramp()' tabindex=3>".$optorganisasi."</select>
			</td>
			
			<td style='padding-left:20px;'>Waktu Keluar</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tanggalkeluar style='text-align:center' onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('d-m-Y')."' tabindex=13 readonly />
				<select id=jamkeluar tabindex=14>".$jm."</select> : 
				<select id=menitkeluar tabindex=15>".$mnt."</select>
			</td>
			
			<td style='padding-left:20px;'>Berat Keluar</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='beratkeluar' style='width:80px;' onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('beratkeluar',2);getnetto();\" value='0' tabindex=16 /> Kg
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['pabrik']."</td>
			<td>:</td>
			<td>
				<select id=kodepabrik tabindex=4>".$optpabrik."</select>
			</td>
			
			<td style='padding-left:20px;'>Potongan</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='potongan' style='width:80px;' onkeypress='return angka_doang(event)' value='0' tabindex=16 onkeyup=\"z.numberFormat('potongan');getnetto();\" /> Kg
			</td>
			
			<td style='padding-left:20px;'>NETTO</td>
			<td>:</td>
			<td>
				<input  type='hidden' class='myinputtextnumber' id='kg' style='width:80px;' onkeypress='return angka_doang(event)' value='0' disabled />
				<input  type='text' class='myinputtextnumber' id='netto' style='width:80px;' onkeypress='return angka_doang(event)' value='0' disabled /> Kg
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['koderamp']."</td>
			<td>:</td>
			<td>
				<select id=koderamp onchange='getsupplier()' tabindex=5>".$optramp."</select>
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']."</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='harga' style='width:80px;' onkeypress='return angka_doang(event)' value='0' tabindex=18 onkeyup=\"z.numberFormat('harga',2);getnetto();\" />
			</td>
			
			<td style='padding-left:20px;'>Total Rupiah</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='totalrupiah' style='width:80px;' onkeypress='return angka_doang(event)' value='0' disabled/>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kdsupplierramo']."</td>
			<td>:</td>
			<td>
				<select id=supplier tabindex=6>".$optsupplier."</select>
			</td>
			
			<td style='padding-left:20px;display:none'>".$_SESSION['lang']['jjg']."</td>
			<td style='display:none'>:</td>
			<td style='display:none'>
				<input  type='text' class='myinputtextnumber' id='jjg' style='width:80px;' onkeypress='return angka_doang(event)' value='0' tabindex=17 />
			</td>
			
			<td style='padding-left:20px;'>Beban Pajak PPh 22 </td>
			<td>:</td>
			<td>
				<select id=bbnPajak style=\"width:150px;\"  onchange=getnetto() tabindex=19> 
					<option value='1'>Di Tanggung</option>
					<option value='0'>".$_SESSION['lang']['tidak']."</option></selected>
			</td>
			<td style='padding-left:20px;'>Total Rupiah Pph</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='totalrupiahpph' style='width:80px;' onkeypress='return angka_doang(event)' value='0' disabled/>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nokendaraan']."</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtext' id='nokendaraan' style='width:80px;' onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' tabindex=8 maxlength=9 />
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['persen']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:50px;\" value=0.5 id=prsnAll  onkeyup=\"z.numberFormat('prsnAll',2);getnetto();\" tabindex=20>
			</td>
			<td style='padding-left:20px;'>Total Pembayaran</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='totalpembayaran' style='width:80px;' onkeypress='return angka_doang(event)' value='0' disabled/>
			</td>
		</tr>
		<tr>
			<td colspan=9 style='text-align:center;padding-top:5px;'>
				<button tabindex=21 class=mybutton onclick=saveData('pmn_slave_penerimaantbsramp','".$arr."')>".$_SESSION['lang']['save']."</button>&nbsp;
				<button tabindex=22 class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>"; 
echo"</div>";

echo"<div id=formUpload style=display:none;>";
echo"<fieldset style=float:left;><legend>Download Template</legend>
	<table style=width:100%;>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select id=tmplpt onchange='getramp2()' tabindex=1>".$optorganisasi."</select>
			</td>
			
			<td style='vertical-align:top;padding-left:20px;'><u>".$_SESSION['lang']['kdsupplierramo']."</u></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['pabrik']."</td>
			<td>:</td>
			<td>
				<select id=tmplkodepabrik tabindex=2>".$optpabrik."</select>
			</td>
			<td rowspan=2 style='vertical-align:top;padding-left:20px;'>
				<div id='listsupplier'></div>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['koderamp']."</td>
			<td>:</td>
			<td>
				<select id=tmplkoderamp onchange='getsupplier2()' tabindex=2>".$optramp."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tmpltanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly value='".date('d-m-Y')."'/>
			</td>
		</tr>
		<tr>
			<td colspan=6 style='text-align:center;padding-top:5px;'>
				<button tabindex=8 class=mybutton onclick=download()>Download</button>&nbsp;
				<button tabindex=9 class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>"; 

echo "<div style='clear:both;'></div>";

echo"<fieldset style=float:left;><legend>Upload Data</legend>
	(File type support only CSV).<p>
	
	<form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>
	
		<input type=hidden name=jenisdata id=jenisdata value='PENERIMAANTBSRAMP'>
		<input type=hidden name=intltiket id=intltiket value='".$instiket."'>
        <input type=hidden name=MAX_FILE_SIZE value=1024000>
        File : <input name=filex type=file id=filex size=25 class=mybutton>
        
		<select name=pemisah style='display:none'>
			<option value=','>, (comma)</option>
			<option value=';'>; (semicolon)</option>
			<option value=':'>: (two dots)</option>
			<option value='/'>/ (devider)</option>
        </select>
        
		<input type=button class=mybutton  value=".$_SESSION['lang']['uploaddata']." title='Submit this File' onclick=submitFile()>
		<br>
		<iframe frameborder=0 width=800px height=200px name=frame></iframe>
	</form>
</fieldset>"; 
echo"</div>";
CLOSE_BOX();
echo close_body(); 
?>