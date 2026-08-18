<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zFunction.php');
#include_once('lib/rTable.php');
require_once('lib/zSelect2Lite.php');
?>
<script language=javascript src='js/sdm_pjdx.js?v=<?php echo time(); ?>'></script>
<!-- <script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script> -->
<?
$_SESSION['rute']=array();

OPEN_BOX('','<span class=judul>'.getMenu($_SESSION['pjd']['menu']).'</span><br>');
echo"<table>
     <tr valign=middle>";
	 if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){		 
		 echo"<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
		   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>";
	 }
	 
echo"<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td><input type=text class=myinputtext id=notransaksilist nkeypress=\"return_tanpa_kutip(event);\" style=\"width:150px;\" onkeypress='enterkey(event,loaddata)' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
			<td><input type=text class=myinputtext id=namakarylist nkeypress=\"return_tanpa_kutip(event);\" style=\"width:150px;\" onkeypress='enterkey(event,loaddata)' /> </td>
			
		</tr>";

echo"<tr>
		<td colspan=2></td>
		<td><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
			<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>";

echo"</fieldset></table><div style=clear:both></div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();
if(getindukPT($_SESSION['empl']['kodeorganisasi']) == 'PPP'){
	$col = '6';
}else{
	$col = '7';
}
echo"
	<div>    
		<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['jenis']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['unit']."</th>
				<th align=center colspan=3>Renc ".$_SESSION['lang']['tanggaldinas']."</th>
				<th align=center colspan=3>Real ".$_SESSION['lang']['tanggaldinas']."</th>
				<th align=center rowspan=2>PT ".$_SESSION['lang']['tujuan']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['tujuan']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['regional']." ".$_SESSION['lang']['tujuan']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['keterangan']."</th>
				<th align=center rowspan=2 width=50px>".$_SESSION['lang']['ticket']."<br>(Pesawat Udara)</th>
				<th align=center rowspan=2>Status<br>Pengajuan</th>
				<!--<th align=center rowspan=2>Pemby<br>Uang Muka</th>-->
				<th align=center rowspan=2>Status<br>Pertanggungjawaban</th>
				<th align=center rowspan=2>".$_SESSION['lang']['dibuat']."</th>
				<!--<th align=center rowspan=2>Pemby<br>PTJ</th>-->
				<th align=center rowspan=2 colspan=".$col.">Action</th>
			</tr>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['dari']."</th>
				<th align=center>".$_SESSION['lang']['sampai']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center>".$_SESSION['lang']['dari']."</th>
				<th align=center>".$_SESSION['lang']['sampai']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
			
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

$wh="";
if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx'){
	$wh.=" and karyawanid='".$_SESSION['standard']['userid']."'";
	$wh.=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')";
}elseif($_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){	
	if ($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	}elseif ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$wh.=" and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."'";
	} else {
		$wh.=" and lokasitugas = '".$_SESSION['empl']['lokasitugas']."'";
	}
	$wh.=" and tipekaryawan in ('1','2','3','4','5','6')";
	$wh.=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')";
}

$optkary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['pjd']['menu']!='sdm_pengajuanpjdstaffx'){	
}
$str="select * from ".$dbname.".datakaryawan where 1=1 ".$wh." order by namakaryawan asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optkary.="<option value=".$bar['karyawanid'].">".$bar['nik']." - ".$bar['namakaryawan']."</option>";
}

$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$optpt.="<option value='OTH'>".strtoupper($_SESSION['lang']['others'])."</option>";

$optunit="<option value=''></option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res=fetchdata($str);
foreach($res as $bar){
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".sdm_5regionalpjd";
$res=fetchdata($str);
foreach($res as $bar){
	$opttipe.="<option value=".$bar['regional'].">".$bar['nama']."</option>";
}
$opttipe.="<option value='OTH'>LAIN-LAIN</option>";

$opttipekary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan";
$res=fetchdata($str);
foreach($res as $bar){
	$opttipekary.="<option value=".$bar['id'].">".$bar['tipe']."</option>";
}

// $str="select distinct level,namalevel from ".$dbname.".sdm_5levelpjdinas";
// $res=fetchdata($str);
// foreach($res as $bar){
// 	$opttipekary.="<option value=".$bar['level'].">".$bar['namalevel']."</option>";
// }

$optjab="<option value=''></option>";
$str="select * from ".$dbname.".sdm_5jabatan";
$res=fetchdata($str);
foreach($res as $bar){
	$optjab.="<option value=".$bar['kodejabatan'].">".$bar['namajabatan']."</option>";
}

$optlevel="<option value=''></option>";
$str="select * from ".$dbname.".sdm_5levelkaryawan";
$res=fetchdata($str);
foreach($res as $bar){
	$optlevel.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
}

$optgol="<option value=''></option>";
$str="select * from ".$dbname.".sdm_5golongan";
$res=fetchdata($str);
foreach($res as $bar){
	$optgol.="<option value=".$bar['kodegolongan'].">".$bar['namagolongan']."</option>";
}

$optdep="<option value=''></option>";
$str="select * from ".$dbname.".sdm_5departemen";
$res=fetchdata($str);
foreach($res as $bar){
	$optdep.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
}

$optlok="<option value=''></option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res=fetchdata($str);
foreach($res as $bar){
	$optlok.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

echo"<fieldset><legend><b>Form</b></legend>
<table border=0 cellpadding=2  style='display: inline-block;vertical-align:top'>
	<input hidden id=stsawal value='".$_SESSION['pjd']['menu']."'>
	<input hidden id=methodheader value='insertheader'>
    <tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td colspan=3><input disabled id=notransaksi class=myinputtext style='width:165px;'></td>
        <td></td>
		
		<td>".$_SESSION['lang']['lokasitugas']."</td>
        <td>:</td>
        <td colspan=3><select disabled id=lokasitugas style='width:170px;'>".$optlok."</select></td>

    </tr>
    <tr>
        <td>".$_SESSION['lang']['namakaryawan']."&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
        <td>:</td>
        <td colspan=3><select onchange=getdata(); id=karyawanid style='width:170px;'>".$optkary."</select></td>
		<td>
			<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
		</td>
		
        <td>PT ".$_SESSION['lang']['tujuan']."&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
        <td>:</td>
        <td colspan=3><select id=pttujuan onchange=getunit(); style='width:170px;'>".$optpt."</select></td>
    </tr> 
	<tr>
		<td>".$_SESSION['lang']['jabatan']."</td>
        <td>:</td>
        <td colspan=3><select disabled id=jabatan style='width:170px;'>".$optjab."</select></td>
		<td></td>
	
		<td>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['tujuan']."&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
        <td>:</td>
        <td colspan=3><select id=unittujuan1 onchange=getregion(); style='width:170px;'>".$optunit."</select>
			<input style=\"display:none;width:165px\" id=unittujuan2 class=myinputtext>
		</td>
	</tr>
	<tr>
		<td>Level ".$_SESSION['lang']['karyawan']."</td>
		<td>:</td>
		<td colspan=3><select disabled id=levelkaryawan style='width:170px;'>".$optlevel."</select></td>
		<td></td>

		<td>".$_SESSION['lang']['regional']." ".$_SESSION['lang']['tujuan']."&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
        <td>:</td>
        <td colspan=3><select id=regiontujuan style='width:170px;'>".$opttipe."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['kodegolongan']."</td>
        <td>:</td>
        <td colspan=3><select disabled id=golongan style='width:170px;'>".$optgol."</select></td>
		<td></td>

		<td>Tipe Karyawan&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
        <td>:<input id=jumlahlevel hidden></td>
        <td colspan=3><select disabled id=tipekary style='width:170px;'>".$opttipekary."</select></td>
		
	</tr>
	<tr>
		<td>".$_SESSION['lang']['departemen']."</td>
        <td>:</td>
        <td colspan=3><select disabled id=dept style='width:170px;'>".$optdep."</select></td>
		<td></td>

		<td>".$_SESSION['lang']['ticket']." <font style=font-size:10px><i>(".$_SESSION['lang']['pesawatudara'].")<i></font>&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
        <td>:</td>
        <td colspan=3>
			<input type=radio name=tiket id=tiketya value='1'><span>".$_SESSION['lang']['ya']."</span>
			<input type=radio name=tiket id=tiketno value='0'><span>".$_SESSION['lang']['tidak']."</span>		
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggaldinas']."&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
		<td>:</td>
		<td width=50px><input type='text' readonly=readonly class='myinputtext' id='tglawal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:67px;' value='".date('d-m-Y')."' /> 
		</td>
		<td>s/d</td>
		<td width=50px><input type='text' readonly=readonly class='myinputtext' id='tglakhir' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:67px;' /> 
		</td>
		<td></td>
	</tr>";
	$disp="";
	if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){
		$disp="style=display:none;";
	}
	
	if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx' or $_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
		$disab=""; $n="";
	}else{
		$disab="disabled"; $n="style=display:none;";
	}
	echo"<tr ".$disp.">
		<td>Real Tgl Dinas&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
		<td>:</td>
		<td width=50px><input type='text' ".$disab." readonly=readonly class='myinputtext' id='tglawalreal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:67px;'/> 
		</td>
		<td>s/d</td>
		<td width=50px><input type='text' ".$disab." readonly=readonly class='myinputtext' id='tglakhirreal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:67px;' /> 
		</td>
		<td ".$n."><img title=Simpan class=zImgBtn onclick=simpantglreal(); src=images/save.png></td>
	</tr>";
	
	echo"<tr>
		<td valign=top>".$_SESSION['lang']['keterangan']."&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
        <td valign=top>:</td>
        <td colspan=8>
			<input id=ketdinas class=myinputtext type=text style='width:550px;'>
		</td>
	</tr>
	<tr>
		<td valign=top>".$_SESSION['lang']['rute']."&nbsp;<font size=1px style=color:red;><b>*</b></font></td>
        <td valign=top>:</td>
        <td colspan=70>
			<table border=0 cellpadding=3 cellspacing=1 class=sortable>
				<thead>
					<tr class=rowheader>
						<th width=25px>No</th>
						<th>".$_SESSION['lang']['dari']."</th>
						<th>".$_SESSION['lang']['tujuan']."</th>
						<th>".$_SESSION['lang']['tanggal']."</th>
						<th>".$_SESSION['lang']['jam']."</th>
						<th>".$_SESSION['lang']['transport']."</th>
						<th width=30px colspan=2>".$_SESSION['lang']['action']."</th>
					</tr>
				</thead>
					<tr class=rowcontent style=text-align:center id=barisinputrute>
						<td id=keyrute align=center>#</td>
						<td style='width:70px;'><input id=dari class=myinputtext style='width:75px;'></td>
						<td style='width:70px;'><input id=tujuan class=myinputtext style='width:75px;'></td>
						<td style='width:60px;'><input type='text' readonly=readonly class='myinputtext' id='tglrute' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:67px;'></td>
						<td style='width:100px;'><input type='time' id=time class=myinputtext style='width:85px;'></td>
						<td><input id=transport class=myinputtext style='width:120px;'></td>
						<td colspan=2><img src='images/plus.png' class='zImgBtn' title='Tambah Rute' id=tombolrute onclick=addrute();></td>
						<input hidden id=methodrute value='addrute'>
					</tr>
				<tbody id=contrute>
				</tbody>
			</table>
		</td>
	</tr>
	";
	
echo"<tr>
        <td colspan=2></td>";
	if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){		
		echo"<td colspan=3>";
		echo"<button onclick=simpanheader() class=mybutton name=btnsimpan id=btnsimpanheader>".$_SESSION['lang']['save']."</button>";
		echo"<button onclick=batalheader() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>";
		echo"</td><td colspan=200>Isi kolom yang diberi tanda &nbsp;<font size=1px style=color:red;><b>*</b></font></td>";
	}
echo"</tr>
<input hidden id=jenistampilan value='tampilansimple'>
</table>";
echo"</fieldset>";

CLOSE_BOX();
echo"<div id='contdetail' style=display:none></div>";
echo"</div>";
?>