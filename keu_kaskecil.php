<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/keu_kaskecil.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php

##deklarasi untuk option##

$optper=$optunit=$optnoakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";


$arrCashAdvance=array();
$sql = "SELECT notransaksi,novoucher FROM " . $dbname . ".keu_kaskecildt where jenis='1' ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $arrCashAdvance[$bar['notransaksi']]=$bar['novoucher'];
}

// $optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
// $sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4";
// $qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
// $qry->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $qry->fetch()) {
//     $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . ": " . $bar['namaorganisasi'] . "</option>";
// }


$optaruskas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT * FROM ".$dbname.".keu_5aruskas where (akses_rekening='KK' or akses_rekening='') and tipetransaksi='K'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']."-".$bar['nama_aruskas']."</option>";
}

$optpenerima = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT karyawanid,namakaryawan FROM ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."' and statuskaryawan != 'Keluar' and tanggalkeluar='0000-00-00'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optpenerima.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";
}

# ambil unit
//$optunitcash="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$lstUnit=getOrgDetail(1);
$dtMul=0;
$listOrg='';
foreach($lstUnit as $row=>$isiDt){
	// if(substr($row,0,5)=='pilih'){
	// 	continue;
	// }
    @$optunitcash.="<option value='".$row."'>".$row." - ".$isiDt."</option>";
}

$optjenis="<option value='3'>Pemakaian</option>";
$optjenis.="<option value='1'>Cash Advance</option>";
$optjenis.="<option value='2'>Pertanggung Jawaban</option>";
$arrTipe=array("0"=>"Top Up","1"=>"Close Periode");
foreach ($arrTipe as $key => $val) {
	$optTipe.="<option value='".$key."'>".$val."</option>";
}

##HEADER UNTUK BUAT BARU SAMA LIST-->
OPEN_BOX('','<span class=judul>'.getMenu('keu_kaskecil').'</span>');
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
			<tr >
				<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext id=notransaksisch style=width:150px maxlength=20 onkeypress='return_tampa_kutip(event)' ></td>
			</tr>
				<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext  id=tanggalsch onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:95px;\" /></td>
            </tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
		<td>
		<fieldset hidden><legend>" . $_SESSION['lang']['print'] . "</legend> 
        <table>
			<tr>
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=unitexp  style=\"width:100px;\">" . $optunitcash . "</select></td>
				</tr>
				<tr>
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=perexp  style=\"width:100px;\">" . $optper . "</select></td>
			</tr>
			";

echo"<tr><td><td><td><button class=mybutton onclick=excel(event,'vhc_slave_rkh.php')>" . $_SESSION['lang']['excel'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
     </tr>
	 </table> ";
CLOSE_BOX();
echo "</div>"; //tutup div
##UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->


echo"
<div id=listData style=display:block>"; //buka list data
OPEN_BOX(); //<div style=overflow:scroll>
//<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%
	echo "
	<fieldset  style=width:900px>
            <legend>" . $_SESSION['lang']['list'] . "</legend>
            <div>    
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                    <td align=center >" . $_SESSION['lang']['notransaksi'] . "</td>
                    <td align=center >" . $_SESSION['lang']['notransaksi'] . " Kas Bank</td>
					<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align=center>" . $_SESSION['lang']['unit'] . "</td>
					<td align=center>" . $_SESSION['lang']['tipe'] . "</td>
					<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
					<td align=center>" . $_SESSION['lang']['action'] . "</td>
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
echo "</div>"; //tutup list data
##UNTUK BUAT FORM INPUT HEADER-->


echo "<div id=header style=display:none >"; //buka diff
OPEN_BOX();

$frm[0]='';
$frm[1]='';
//<button id=tomboldetail class=mybutton onclick=popupdetail('cashtopup','1')>Cash Top Up</button>
$frm[0].="
<fieldset>
<legend>Form</legend>";
$frm[0].="
	<fieldset><legend>".$_SESSION['lang']['saldo']."</legend>
	<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['saldoawal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber id=sawal disabled style=width:100px maxlength=8 onkeypress='return angka_doang(event)'></td>
		<td>&nbsp;</td>
		<td>".$_SESSION['lang']['opening']."</td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber id=opening disabled style=width:100px maxlength=8 onkeypress='return angka_doang(event)'></td>
		<td>&nbsp;</td>
		<td>".$_SESSION['lang']['advance']."</td>
		<td>:</td>
	<td><input type=text class=myinputtextnumber id=advance disabled style=width:100px maxlength=8 onkeypress='return angka_doang(event)'></td>
		<td>&nbsp;</td></tr>
		<tr><td>".$_SESSION['lang']['expenses']."</td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber id=expense disabled style=width:100px maxlength=8 onkeypress='return angka_doang(event)'></td>
		<td>&nbsp;</td>
		<td>".$_SESSION['lang']['closing']."</td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber id=closing disabled style=width:100px maxlength=8 onkeypress='return angka_doang(event)'></td>
	</tr>
	</table></fieldset><div style=clear:both>&nbsp;</div>";
	$frm[0].="<fieldset><legend>".getMenu('keu_kaskecil')." ".$_SESSION['lang']['form']."</legend>
	          <table cellspacing=1 cellpading=1 border=0>";
	$frm[0].="<tr>";
	$frm[0].="<td width=200px>".$_SESSION['lang']['notransaksi']."</td><td>:</td><td><input type=text class=myinputtext id=notransaksi style=width:145px  onkeypress='return_tanpa_kutip(event)' disabled></td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select style=\"width:150px;\" id=unit onchange=getopening()>".$optunitcash."</select>
			<img id='unit' onclick=z.elSearch('unit',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
	         </td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['tanggal']."</td><td>:</td><td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10  onchange=getopening() /></td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['jenis']."</td><td>:</td><td><select style=\"width:150px;\" id=jenis onchange='disableditem()'>".$optjenis."</select></td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['noaruskas']."</td><td>:</td><td><select style=\"width:150px;\"  id=noaruskas onchange=getnoakun()>".$optaruskas."</select>
			 <img id='noaruskas' onclick=z.elSearch('noaruskas',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['noakun']."</td><td>:</td><td><select style=\"width:150px;\" id=noakun>".$optnoakun."</select></td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td><select style=\"width:150px;\" id=keterangan>".$optnoakun."</select></td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>&nbsp;</td><td>:</td><td><input type=text class=myinputtext id=keterangan2 style=width:145px placeholder='Ketik Keterangan Tambahan' ></td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['noreferensi']."</td><td>:</td><td><input type=hidden class=myinputtext id=noreferensival > <input type=text class=myinputtext onclick=\"searchNoRef('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noreferensi']."','<div id=formPencariandata></div>',event);\" id=noreferensi style=width:145px  readonly></td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['penerima']."</td><td>:</td><td><select style=\"width:150px;\" id=penerima>".$optpenerima."</select><img id='penerima' onclick=z.elSearch('penerima',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td>".$_SESSION['lang']['jumlahdipakai']."</td><td>:</td><td><input type=text class=myinputtextnumber id=jumlah  value=0  style=width:145px maxlength=8  onkeypress='return angka_doang(event)'>
	</td>";
	$frm[0].="</tr>";
	$frm[0].="<tr>";
	$frm[0].="<td colspan=2>
			  <input type=hidden class=myinputtextnumber id=novoucher  style=width:100px maxlength=20 onkeypress='return_tampa_kutip(event)'>
			  <input type=hidden class=myinputtextnumber id=nourut disabled style=width:25px maxlength=8 onkeypress='return angka_doang(event)'>
			  <input type=hidden class=myinputtextnumber onkeyup=gethitung() id=jumlahditerima value=0  style=width:75px maxlength=8 onkeypress='return angka_doang(event)' >
			  <input type=hidden class=myinputtextnumber  id=jumlahdipakai value=0  style=width:75px maxlength=8 onkeypress='return angka_doang(event)' >
			  <input  type=hidden class=myinputtextnumber id=saldoberjalan value=0  style=width:150px maxlength=8 onkeypress='return angka_doang(event)' >
			  <input type=hidden id=method value='insert'>
	           &nbsp;</td><td>
	          <button class=mybutton onclick=simpan() >".$_SESSION['lang']['save']."</button>&nbsp;
			  <button class=mybutton onclick=canceldt() >".$_SESSION['lang']['cancel']."</button>&nbsp;
			  <button class=mybutton onclick=add_new_data() >".$_SESSION['lang']['new']."</button>&nbsp;
	          </td>";
	$frm[0].="</tr>";
	$frm[0].="</table></fieldset></fieldset>";

// $frm[0].="
// <table cellpading=1 cellspacing=1 border=0 class=sortable>
// 	<thead>
// 	<tr>
// 		<td align=center>".$_SESSION['lang']['jenis']."</td>
// 		<td align=center hidden>".$_SESSION['lang']['nourut']."</td>
// 		<td align=center>".$_SESSION['lang']['unit']."</td>
// 		<td align=center>".$_SESSION['lang']['noreferensi']."</td>
// 		<td align=center>".$_SESSION['lang']['tanggal']."</td>
// 		<td align=center hidden>".$_SESSION['lang']['novoucher']."</td>
// 		<td align=center>".$_SESSION['lang']['noaruskas']."</td>
// 		<td align=center>".$_SESSION['lang']['noakun']."</td>
// 		<td align=center colspan=2>".$_SESSION['lang']['keterangan']."</td>
// 		<td align=center style=\"width:120px;\">".$_SESSION['lang']['penerima']."</td>
// 		<td align=center hidden>".$_SESSION['lang']['jumlahditerima']."</td>
// 		<td align=center hidden >".$_SESSION['lang']['jumlah']."</td>
// 		<td align=center >".$_SESSION['lang']['jumlahdipakai']."</td>
		
// 		<td hidden align=center>".$_SESSION['lang']['saldoberjalan']."</td>
// 		<td align=center>".$_SESSION['lang']['action']."</td>
// 	</tr></thead>
			
// 	<tr class=rowcontent>
// 		<td hidden><input type=text class=myinputtextnumber id=nourut disabled style=width:25px maxlength=8 onkeypress='return angka_doang(event)'></td>
// 		<td><select style=\"width:80px;\" id=jenis onchange='disableditem()'>".$optjenis."</select></td>	
// 		<td><select style=\"width:130px;\" id=unit onchange=getopening()>".$optunitcash."</select></td>
// 		<td><input type=hidden class=myinputtext id=noreferensival > <input type=text class=myinputtextnumber onclick=\"searchNoRef('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noreferensi']."','<div id=formPencariandata></div>',event);\" id=noreferensi style=width:150px  disabled readonly></td>	
// 		<td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>
// 		<td hidden><input type=text class=myinputtextnumber id=novoucher  style=width:100px maxlength=20 onkeypress='return_tampa_kutip(event)'></td>
// 		<td><select style=\"width:120px;\" id=noaruskas onchange=getnoakun()>".$optaruskas."</select></td>
// 		<td><select style=\"width:120px;\" id=noakun>".$optnoakun."</select></td>
// 		<td><select style=\"width:150px;\" id=keterangan>".$optnoakun."</select></td>
// 		<td><input type=text class=myinputtext id=keterangan2 style=width:150px ></td>
// 		<td><select style=\"width:100px;\" id=penerima>".$optpenerima."</select>
// 		<img id='penerima' onclick=z.elSearch('penerima',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
// 		</td>
// 		<td hidden><input type=text class=myinputtextnumber onkeyup=gethitung() id=jumlahditerima value=0  style=width:75px maxlength=8 onkeypress='return angka_doang(event)' ></td>
// 		<td hidden><input type=text class=myinputtextnumber  id=jumlahdipakai value=0  style=width:75px maxlength=8 onkeypress='return angka_doang(event)' ></td>
// 		<td><input type=text class=myinputtextnumber id=jumlah  value=0  style=width:75px maxlength=8 onkeypress='return angka_doang(event)' ></td>
		
// 		<td hidden><input  type=text class=myinputtextnumber id=saldoberjalan value=0  style=width:150px maxlength=8 onkeypress='return angka_doang(event)' ></td>
// 		<td>
// 			<img src='images/skyblue/save.png' class='zImgBtn' onclick=simpan() title='simpan'>
// 			<img src='images/clear.png' class='zImgBtn' onclick=canceldt() title='batal'>
// 			<img src='images/skyblue/addbig.png' class='zImgBtn' onclick=add_new_data() title='buat baru'>
// 		</td>
// 		<input type=hidden id=method value='insert'>
// 	</tr>
// </table>
// </fieldset>";


$frm[0].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
  <div id=datadetail></div>
  </fieldset>";
  
  

$frm[1].="
<fieldset>
<legend>Form</legend>
<table cellspacing=1 border=0>
	<tr>
		<td align=left>".$_SESSION['lang']['unit']."</td>
		<td align=center>:</td>
		<td><select style=\"width:175px;\" id=unitcash onchange=getPeriodeKas()>".$optunitcash."</select>
		<img id='unitcash' onclick=z.elSearch('unitcash',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
	</tr>
	<tr>
		<td align=left>".$_SESSION['lang']['periode']."</td>
		<td align=center>:</td>
		<td><select style=\"width:175px;\" id=periodecash  onchange=getPeriodeKas()>".$optper."</select></td>
	</tr>
	<tr>
		<td align=left>".$_SESSION['lang']['tipe']."</td>
		<td align=center>:</td>
		<td><select style=\"width:175px;\" id=tipeDis>".$optTipe."</select></td>
	</tr>
	<tr>
		<td align=left>".$_SESSION['lang']['plafon']."</td>
		<td align=center>:</td>
		<td><input type=text class=myinputtextnumber style=\"width:175px;\" id=plafondTopUpa disabled /></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td><button id=tomboldetail class=mybutton onclick=prosescash()>".$_SESSION['lang']['proses']."</button></td>
	</tr>
	
	
</table>
</fieldset>";

$frm[1].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
  <div id=datadetailcash></div>
  </fieldset>";
  
  
  
  
  
$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['cashtopup'];
drawTab('FRM',$hfrm,$frm,250,950);		
CLOSE_BOX();
echo"</div>";


echo close_body();
?>