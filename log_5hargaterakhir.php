<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_5hargaterakhir.js?v=1'></script>

<?php
$optOrg=$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
$optcrOrg=$optcrunit="<option value=''>".$_SESSION['lang']['all']."</option>";

$sOrg="select distinct kodeorganisasi as kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$res=fetchData($sOrg);
foreach($res as $row=>$rOrg){
    @$optOrg.="<option value='".$rOrg['kodeorganisasi']."'>".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
    @$optcrOrg.="<option value='".$rOrg['kodeorganisasi']."'>".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('log_5hargaterakhir').'</span>');
//print_r($_SESSION['empl']['regional']);
echo"<fieldset>
	<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td class=bintang style='vertical-align:top'>Kode Barang</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<input type=text id=kodebrg onkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\" disabled />
				<img src='images/onebit_02.png' id='imgkodebrg' style='position:relative;top:3px;left:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg onkeypress=enterkey(event,findBrg)><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container1></div><input type=hidden id=nomor name=nomor value=".$key.">',event)\";>
				&nbsp;<label id='namabrg' style='color:blue;padding-top:1px'></label>
			</td>
		</tr>
		<tr id='trpt' style='display:none'>
			<td>".$_SESSION['lang']['pt']."</td> 
			<td>:</td>
			<td>
				<select id=\"pt\" name=\"pt\" onchange=getunit()>".$optOrg."</select>
				<img id='pt' onclick=z.elSearch('pt',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr id='trunit' style='display:none'>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select id=\"unit\" name=\"unit\" >".$optunit."</select>
				<img id='unit' onclick=z.elSearch('unit',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td class=bintang>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext  id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:75px;\" readonly/>
			</td>
		</tr>
		<tr>
			<td class=bintang>".$_SESSION['lang']['harga']." Satuan</td>
			<td>:</td>
			<td>
				<input type=text id=harga onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\" placeholder=0 onkeyup=\"z.numberFormat('harga',2)\">
				&nbsp;<label style='color:blue;padding-top:10px;cursor:pointer' onselectstart='return false;' onclick=\"addfrompo()\">Add from PO</label>
			</td>
		</tr>
		<tr id='trnopo' style='display:none'>
			<td>No PO</td>
			<td>:</td>
			<td>
				<label style='color:blue;padding-top:10px' id='nopo'></label>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['harga']." Estimasi</td>
			<td>:</td>
			<td>
				<input type=text id=hargaestimasi onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\" placeholder=0 onkeyup=\"z.numberFormat('hargaestimasi',2)\">
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=myid value=''>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top;padding-bottom:10px;'>
			<tr>
				<td>
					<fieldset>
					<legend>Cari</legend>
					<table border=0 cellpadding=1 cellspacing=1>
						<tr>
							<td>".$_SESSION['lang']['pt']."</td> 
							<td>:</td>
							<td>
								<select id=\"ptscr\" name=\"ptsrc\" onchange=getunitsrc()>".$optcrOrg."</select>
								<img id='ptscr' onclick=z.elSearch('ptscr',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
							</td>

							<td style='padding-left:20px'>Kode Barang</td>
							<td>:</td>
							<td><input type=text  id=kodebrgsrc nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\"  onkeyup=\"loadData(0)\" >
						</tr>
						<tr>
							<td>".$_SESSION['lang']['unit']."</td>
							<td>:</td>
							<td>
								<select id=\"unitsrc\" name=\"unitsrc\" onchange=loadData(0)>".$optcrunit."</select>
								<img id='unitsrc' onclick=z.elSearch('unitsrc',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
							</td>                    
					
							<td style='padding-left:20px'>".$_SESSION['lang']['tanggal']."</td>
							<td>:</td>
						   <td><input type=text class=myinputtext  id=tglsrc onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:75px;\" readonly onchange=loadData(0) />
						   </td>
						</tr>
						<tr>
							<td colspan=6 style='text-align:center'>
								<button class=mybutton onclick='batalcari();'>".$_SESSION['lang']['resetvariableoutput']."</button>
							</td>
						</tr>
					</table>
				</fieldset>
				</td>
			</tr>
		</table>
		<div id=container> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>