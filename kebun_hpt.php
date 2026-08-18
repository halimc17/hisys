<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';

?>

<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/kebun_hpt.js"></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
$tglHrini=date("Ymd");



### GET JENIS HAMA ###
$str = "select * from ".$dbname.".kebun_5jenishama";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optJenisHama = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar = $res->fetch()){
	$optJenisHama .= "<option value='".$bar['kodehama']."'>".$bar['namahama']."</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['hpt']).'</span>');

$ctl = array();

# Control
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"showAdd()\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"defaultList()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";

# Search
$ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>".
    makeElement('cariNoTransaksi','text','').
    makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"loadData()")).
    "</fieldset>";
$frm[0] .= "<div>
	<table>
		<tr>";
		foreach($ctl as $el) {
			$frm[0] .= "<td v-align='middle' style='min-width:100px'>".$el."</td>";
		}
$frm[0] .= "</tr>
	</table>
</div>";
$frm[0] .= "<div style=clear:both;>&nbsp;</div>";
$frm[0] .= "<div id='frmDetail1' style='display:none'><fieldset style='float:left'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
	<table>
		<tr>
			<td colspan=6><b>".$_SESSION['lang']['header']."</b></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nosensus']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input id=sus_ht_nosensus class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" disabled>
			</td>
			
			<td>".$_SESSION['lang']['blok']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input style=width:80px id=sus_ht_blok class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" disabled=true>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input id=sus_ht_notransaksi class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" onclick=\"getPopUpNoTransaksi('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."','<fieldset><div id=formPencarianTransaksi></div></fieldset>',event)\" readonly>
				<img id='sus_ht_srcnotransaksi' src='images/onebit_02.png' class=zImgBtn style='position:relative;top:3px;left:3px;' onclick=\"getPopUpNoTransaksi('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."','<fieldset><div id=formPencarianTransaksi></div></fieldset>',event)\">
			</td>
			
			<td>".$_SESSION['lang']['luas']." (Ha)</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input  style=width:80px type=text id=sus_ht_luas class=myinputtextnumber onKeyPress=\"return angka_doang(event);\" onblur=\"display_number(this.id,event);\" value=0 size=12 disabled=true />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input style=width:80px type=text class=myinputtext id=sus_ht_tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" value='".date('d-m-Y')."'>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=sus_ht_method value=sus_ht_insert />
				<button id=sus_ht_save class=mybutton onclick=sus_ht_simpan()>".$_SESSION['lang']['save']."</button>
			</td>
		</tr>
	</table>
	<div style=clear:both;>&nbsp;</div>
	<div id='sus_dt_frm' style='display:none'>
	<hr>
	<table>
		<tr>
			<td colspan=3><b>".$_SESSION['lang']['detail']."</b></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jenishama']."</td>
			<td>:</td>
			<td>
				<select  style=min-width:80px id='sus_dt_jenishama' onchange=\"sus_change_satuan();\" >".$optJenisHama."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jumlah']."</td>
			<td>:</td>
			<td><input  style=width:85px type=text id=sus_dt_jumlah class=myinputtextnumber onKeyPress=\"return angka_doang(event);\" onblur=\"display_number(this.id,event);\" value=0 size=10 />&nbsp;<span id='sus_satuan'></span></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=sus_dt_method value=sus_dt_insert />
				<button class=mybutton onclick=sus_dt_simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=sus_dt_batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
	</div>
</fieldset>";

$frm[0] .= "<fieldset style='width:400px;min-height:135px'>
	<legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['jenishama']."</legend>
	<div id='sus_dt_list'></div>
</fieldset></div>";

// $frm[0] .= "<div style=clear:both;>&nbsp;</div>";
// $frm[0] .= "<fieldset>
	// <legend>".$_SESSION['lang']['note']."</legend>
	// <table cellspacing=1 border=0>
		// <tr>
			// <td></td>
		// </tr>
	// </table>
// </fieldset></div>";


$frm[0] .= "<div id='frm1'><fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
    <table cellpadding=3 cellspacing=1 border=0 class=sortable>
		<thead>
		<tr class=rowheader>
            <td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['nosensus']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['notransaksi']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['blok']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['luas']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['tanggal']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['penanggulangan']."</td>
            <td style='text-align:center' colspan=2>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id=containData>
		</tbody>
		<tfoot id=footData>
		</tfoot>
	</table>
</fieldset></div>";

###################################################################################################
$ctl2 = array();

# Control
$ctl2[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"showAdd2()\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl2[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"defaultList2()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";

# Search
$ctl2[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>".
    makeElement('cariNoTransaksi2','text','').
    makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"loadData2()")).
    "</fieldset>";
$frm[1] .= "<div>
	<table>
		<tr>";
		foreach($ctl2 as $el) {
			$frm[1] .= "<td v-align='middle' style='min-width:100px'>".$el."</td>";
		}
$frm[1] .= "</tr>
	</table>
</div>";
$frm[1] .= "<div style=clear:both;>&nbsp;</div>";


$frm[1] .= "<div id='frmDetail2' style='display:none'><fieldset style='float:left'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
	<table>
		<tr>
			<td colspan=6><b>".$_SESSION['lang']['header']."</b></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nopenanggulangan']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input id=png_ht_nopenanggulangan class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nosensus']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input id=png_ht_nosensus class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" onclick=\"getPopUpNoSensus('".$_SESSION['lang']['find']." ".$_SESSION['lang']['sensus']."','<fieldset><div id=formPencarianTransaksi></div></fieldset>',event)\" readonly>
				<img id='png_ht_srcNoSensus' src='images/onebit_02.png' class=zImgBtn style='position:relative;top:3px;left:3px;' onclick=\"getPopUpNoSensus('".$_SESSION['lang']['find']." ".$_SESSION['lang']['sensus']."','<fieldset><div id=formPencarianTransaksi></div></fieldset>',event)\">
			</td>
			
			<td>".$_SESSION['lang']['blok']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input style=width:80px id=png_ht_blok class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" disabled=true>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input id=png_ht_notransaksi class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" onclick=\"getPopUpNoTransaksi2('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."','<fieldset><div id=formPencarianTransaksi></div></fieldset>',event)\" readonly>
				<img id='png_ht_srcNoTransaksi2' src='images/onebit_02.png' class=zImgBtn style='position:relative;top:3px;left:3px;' onclick=\"getPopUpNoTransaksi2('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."','<fieldset><div id=formPencarianTransaksi></div></fieldset>',event)\">
			</td>
			
			<td>".$_SESSION['lang']['luas']."&nbsp;Ha</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input style=width:80px  type=text id=png_ht_luas class=myinputtextnumber onKeyPress=\"return angka_doang(event);\" onblur=\"display_number(this.id,event);\" value=0 size=12 disabled=true />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input style=width:80px type=text class=myinputtext id=png_ht_tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" value='".date('d-m-Y')."'>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=png_ht_method value=png_ht_insert />
				<button id=png_ht_save class=mybutton onclick=png_ht_simpan()>".$_SESSION['lang']['save']."</button>
			</td>
		</tr>
	</table>
	<div style=clear:both;>&nbsp;</div>
	<div id='png_dt_frm' style='display:none'>
	<hr>
	<table>
		<tr>
			<td colspan=3><b>".$_SESSION['lang']['detail']."</b></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jenishama']."</td>
			<td>:</td>
			<td>
				<select id='png_dt_jenishama' onchange=\"png_change_satuan();\" >".$optJenisHama."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jumlah']."</td>
			<td>:</td>
			<td><input type=text id=png_dt_jumlah class=myinputtextnumber onKeyPress=\"return angka_doang(event);\" onblur=\"display_number(this.id,event);\" value=0 size=10 />&nbsp;<span id='png_satuan'></span></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=png_dt_method value=png_dt_insert />
				<button class=mybutton onclick=png_dt_simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=png_dt_batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
	</div>
</fieldset>";

$frm[1] .= "<fieldset style='width:400px;min-height:155px'>
	<legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['jenishama']."</legend>
	<div id='png_dt_list'></div>
</fieldset></div>";

// $frm[1] .= "<fieldset style='width:300px;'>
	// <legend>".$_SESSION['lang']['note']."</legend>
	// <table cellspacing=1 border=0>
		// <tr>
			// <td></td>
		// </tr>
	// </table>
// </fieldset>";

$frm[1] .= "<div style=clear:both;>&nbsp;</div>";

$frm[1] .= "<div id='frm2'><fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
    <table cellpadding=3 cellspacing=1 border=0 class=sortable>
		<thead>
		<tr class=rowheader>
            <td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['nopenanggulangan']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['nosensus']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['notransaksi']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['blok']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['luas']."</td>
            <td style='text-align:center'>".$_SESSION['lang']['tanggal']."</td>
            <td style='text-align:center' colspan=2>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id=containData2>
			
		</tbody>
		<tfoot id=footData2>
		</tfoot>
	</table>
</fieldset></div>
<script>loadAllTabData()</script> ";

###################################################################################################



//=======================================
$hfrm[0]=$_SESSION['lang']['sensus'];
$hfrm[1]=$_SESSION['lang']['penanggulangan'];

drawTab('FRM',$hfrm,$frm,150,'100%');
//=======================================	

CLOSE_BOX();
echo"</div>";
echo close_body();
?>