<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<b>".getMenu('pmn_scr')."</b>");
?>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<script type='text/javascript' language='javascript' src='js/zMaster.js'></script>
<script type='text/javascript' language='javascript' src='js/zTools.js'></script>
<script type='text/javascript' language='javascript' src='js/pmn_scr.js'></script>

<?php
$jenisApp = "SCR";

//Inisialisasi variable
$optramp = $optorganisasi = $optBuyer = $optKomoditi = $optByrke = $optsupplier = $optpabrik = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPt = $optUnitKerja = $optSubBagian = $optunit = "<option value=''>".$_SESSION['lang']['all']."</option>";

### Get PT
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$sCek="select distinct * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$bar->kodeorganisasi."'";
	$rCek=fetchData($sCek);
	if(count($rCek)!=0){
		$optorganisasi.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
		$optPt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";		
	}
	
}

### Get Buyer
$str="select kodecustomer,namacustomer  from ".$dbname.".pmn_4customer order by namacustomer";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optBuyer.="<option value=".$bar['kodecustomer'].">".$bar['namacustomer']."</option>";	
}

### Get Komoditi
$str="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang=400";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optKomoditi.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";	
}

### Get PPn
$arrStatPPn=array("0"=>"Exclude","1"=>"Include");
foreach($arrStatPPn as $row=>$lstNm){
	$optSat.="<option value='".$row."'>".$lstNm."</option>";
}
$optNamabank=makeOption($dbname,"keu_5daftarbank","kodebank,namabank");
### Get Bayar Ke
$str="select * from ".$dbname.".keu_5akunbank order by namabank";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optByrke.="<option value='".$bar['noakun']."'>".$bar['pemilik'].":".$optNamabank[$bar['namabank']]." ".$bar['rekening']."</option>";
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
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
		</td>
		<td>
			<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
		
		echo"<table><tr>";
		
		echo"<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td>
			<select id=caript style=width:175px;>".$optPt."</select>
		</td>";
		
		echo"</tr><tr>";
		
		echo"<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=caritanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />
		</td>";
		
		echo"</tr>
		
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=carinotransaksi  size=25 maxlength=50 />
		</td>";
		
		echo"</tr>
		
		
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button></td>
		</tr>";
		echo "</table>";
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
echo"<tr align=center><td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['unit']."</td>";
echo"<td>Buyer</td>";
echo"<td>Sales Contract No.</td>";
echo"<td>".$_SESSION['lang']['tanggal']."</td>";
echo"<td>Commodity</td>";
echo"<td>".$_SESSION['lang']['updateby']."</td>";
$countApp = getCountApproval($jenisApp,'');
for($i=1;$i<=$countApp;$i++){
	echo"<td align=center>".$_SESSION['lang']['persetujuan']. "".$i."</td>";
}

echo"<td>".$_SESSION['lang']['status']."</td>";
echo"<td colspan=4>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
 
echo"</tfoot></table></div></fieldset>";
echo"</div><input type=hidden id=proses value=insert />";

//===========================================================================
echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
	<table style=width:100%;>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtext' id='notransaksi' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' disabled />
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['komoditi']."</td>
			<td>:</td>
			<td>
				<select id=komoditi style='width:196px;' onchange=\"getkomoditi('0','0','0','0')\">".$optKomoditi."</select>
				<img id='komoditi_find' onclick=\"z.elSearch('komoditi',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
			</td>
			
			<td style='padding-left:20px;vertical-align:top' rowspan=7>
				<table>
					<tbody id='trkualitas'></tbody>
					<tbody id='trapproval'></tbody>
				</table>
			</td>			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select id=pt style='width:200px;' onchange=\"getapproval()\">".$optorganisasi."</select>
				<img id='pt_find' onclick=\"z.elSearch('pt',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['kuantitas']."</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='kuantitas' style='width:80px;' onkeypress='return angka_doang(event)' value='0' /> Kg
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tanggal style='text-align:center' onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('d-m-Y')."' readonly />
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']."</td>
			<td>:</td>
			<td>
				<input  type='text' class='myinputtextnumber' id='harga' style='width:80px;' onkeypress='return angka_doang(event)' value='0' />
			</td>
		</tr>
		<tr>
			<td>Buyer</td>
			<td>:</td>
			<td>
				<select id=buyer style='width:200px;'>".$optBuyer."</select>
				<img id='buyer_find' onclick=\"z.elSearch('buyer',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
			</td>
			
			<td style='padding-left:20px;'>PPN</td>
			<td>:</td>
			<td>
				<select id=ppn>".$optSat."</select>
			</td>
		</tr>
		<tr>
			<td style='display:none'>Sales Contract No.</td>
			<td style='display:none'>:</td>
			<td style='display:none'>
				<input  type='text' class='myinputtext' id='scn' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" onkeydown='upperCaseF(this)' />
			</td style='display:none'>
			
			<td>Payment Date</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tanggalbayar style='text-align:center' onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('d-m-Y')."' readonly />
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['bayarke']."</td>
			<td>:</td>
			<td>
				<select id=bayarke style='width:196px;'>".$optByrke."</select>
				<img id='bayarke_find' onclick=\"z.elSearch('bayarke',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
			</td>
		</tr>
		<tr>
			<td>Berikat</td>
			<td>:</td>
			<td>
				<input type=checkbox id=berikat>".$_SESSION['lang']['yes']."/".$_SESSION['lang']['no']."
			</td>
		</tr>
		<tr>
			<td colspan=9 style='text-align:center;padding-top:5px;'>
				<input type='hidden' id='proses' value='insert'>
				<button class=mybutton onclick=saveData()>".$_SESSION['lang']['save']."</button>&nbsp;
				<button class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button>
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
				<select id=tmplpt onchange='getramp2()'>".$optorganisasi."</select>
			</td>
			
			<td style='vertical-align:top;padding-left:20px;'><u>".$_SESSION['lang']['kdsupplierramo']."</u></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['pabrik']."</td>
			<td>:</td>
			<td>
				<select id=tmplkodepabrik>".$optpabrik."</select>
			</td>
			<td rowspan=2 style='vertical-align:top;padding-left:20px;'>
				<div id='listsupplier'></div>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['koderamp']."</td>
			<td>:</td>
			<td>
				<select id=tmplkoderamp onchange='getsupplier2()'>".$optramp."</select>
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
				<button class=mybutton onclick=download()>Download</button>&nbsp;
				<button class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button>
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