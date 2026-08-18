<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zFunction.php');
#include_once('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/bgt_ptax.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
$_SESSION['rute']=array();
OPEN_BOX('','<span class=judul>'.getMenu('bgt_ptax').'</span><br>');
echo"<table>
     <tr valign=middle>";
echo"<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>";
echo"<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td  style=vertical-align:top;>
		<fieldset id=formpencarianheader><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td><input type=text class=myinputtext id=notransaksilist nkeypress=\"return_tanpa_kutip(event);\" style=\"width:150px;\" onkeypress='enterkey(event,loaddata)' />
			</td>
		</tr>
		";

echo"<tr>
		<td colspan=2></td>
		<td><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
			<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>";

echo"</fieldset></td>
	<td style=vertical-align:top;>
		<fieldset><legend>" . $_SESSION['lang']['info'] . "</legend>
			<font style=color:blue;font-weight:bold>
				<ul>Update, untuk semua PTA yang statusnya ditolak tidak dapat diajukan kembali tetapi harus dibuat ulang.</ul>
				<!--<ul>PTA sudah dibuat tapi tidak diajukan persetujuan lebih dari 30 hari akan dihapus otomatis.</ul>-->
			</font>
		</fieldset>
	</td>
</table><div style=clear:both></div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();
echo"<div class='table-scroll' style=height:65vh>    
		<table cellpadding=5 cellspacing=1 border=0 class=sortable>
		<thead>
			<tr class=rowheader>
				<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['tahun']."<br>PTA</th>
				<th align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['unit']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['jumlah']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['status']."</th>
				<th align=center rowspan=2 colspan=7>Action</th>
			</tr>
		</thead>
			<tbody id=contain> 
				<script>loaddata(0)</script>
			</tbody>
			<tfoot id=footData>
			</tfoot>
		 </table>
		 </div>
		 
</div>"; 
CLOSE_BOX();
echo"</div>";
echo"<div id=detail style=display:none>";
OPEN_BOX();

$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$res=fetchdata($str);
foreach($res as $bar){
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
	$data=array('ESTATE'=>'ESTATE','TRK'=>'TRAKSI');
}elseif($_SESSION['empl']['tipelokasitugas']=='PABRIK'){
	$data=array('MILL'=>'MILL','TRK'=>'TRAKSI');
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$data=array('KANWIL'=>'KANWIL');
}elseif($_SESSION['empl']['tipelokasitugas']=='TC'){
	$data=array('TC'=>'TC');
}elseif($_SESSION['empl']['tipelokasitugas']=='RND'){
	$data=array('RND'=>'RND');
}elseif($_SESSION['empl']['tipelokasitugas']=='BULKING'){
	$data=array('BULKING'=>'BULKING');
}elseif($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$data=array('HOLDING'=>'HOLDING');
}

$opttipebgt="<option value=''></option>";
foreach($data as $bar => $val){
	$opttipebgt.="<option value='".$bar."'>".$val."</option>";
}

echo"<fieldset><legend><b>Form</b></legend>
<table border=0 style='display: inline-block;vertical-align:top'>
	<input hidden id=stsawal value=''>
	<input hidden id=methodheader value='insertheader'>
    <tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td colspan=3><input disabled id=notransaksi class=myinputtext style='width:165px;'></td>
        <td></td>

		<td>".$_SESSION['lang']['tahun']."</td>
        <td>:</td>
        <td colspan=3><input maxlength='4' onchange=getnotrans(); class=myinputtextnumber style='width:165px' id=tahun onkeypress='return angka_doang(event)'></td>

        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td colspan=3><select id=unit onchange=getnotrans(); style='width:170px;'>".$optunit."</select></td>
		
    </tr>
    <tr>		
        <td>".$_SESSION['lang']['tipe']." Budget</td>
        <td>:</td>
        <td colspan=3><select id=tipebudget onchange=gettipepta(); style='width:170px;'>".$opttipebgt."</select></td>
        <td></td>

		<td>".$_SESSION['lang']['tipe']." PTA</td>
        <td>:</td>
        <td colspan=3><select id=tipepta onchange=getnotrans(); style='width:170px;'>".$opttipepta."</select></td>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td colspan=3><input type='text' readonly=readonly class='myinputtext' id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:165px;' value='".date('d-m-Y')."' /></td>
		
    </tr> 
	<tr style=vertical-align:top>
        <td>".$_SESSION['lang']['keterangan']."</td>
        <td>:</td>
        <td colspan=12><textarea rows='3' id='ket' type='text' onkeypress='return tanpa_kutip(event)' style='width:610px;'></textarea></td>
	</tr>
	
	
	";
echo"<tr>
	<td colspan=2></td>";
	echo"<td colspan=3>";
	echo"<button onclick=simpanheader() class=mybutton name=btnsimpan id=btnsimpanheader>".$_SESSION['lang']['save']."</button>";
	echo"<button onclick=batalheader() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>";
echo"</tr>
</table>";
echo"</fieldset>";
CLOSE_BOX();
echo"<div id='contdetail' style=display:none></div>";
echo"</div>";
echo close_body();
?>