<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript src='js/kebun_tbsexternal.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<!--deklarasi untuk option-->

<?php

// if($_SESSION['empl']['kodeorganisasi']!='SDK'){
	// echo"<font size='10px'>Menu ini Sedang dalam proses penambahan unit region sekadau dan bonti</font>";exit();
// }

$whrunit="";
$whrunit.=getOrgDetail(2);

$optbuyer=$optbarang=$opttipe=$opttangki=$optpt=$optpelayaran=$optkapal=$optponton=$optfranco="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

$str = "select * from ".$dbname.".organisasi where tipe='kebun' and inti!='1' and kodeorganisasi in (".$whrunit.")";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   @$optunit.="<option value='".$bar['kodeorganisasi']."'>[".$bar['kodeorganisasi']."] - ".$bar['namaorganisasi']."</option>";
}


#= array kodesupplier
$str = "SELECT a.supplierid,a.namasupplier,a.kodept FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1 order by a.namasupplier asc";
// $str = "SELECT a.supplierid,a.namasupplier,a.kodept FROM " . $dbname . ".log_5supplier a
// left join log_5supkelompok b on a.supplierid=b.supplierid
// where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
	$kodesupplier[$bar['kodept']]=$bar['supplierid'];
}
	

// $str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
// left join log_5supkelompok b on a.supplierid=b.supplierid
// where a.status=1 and b.tipe in ('SUPPLIERTBSEXT') order by a.namasupplier asc";
$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1  order by a.namasupplier asc";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   @$optkud.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
}


?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('kebun_tbsexternal').'</span>');
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	echo"<table>";
	echo"
	
			<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>		
				<td>
					<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:150px;\">
				</td>
				
				
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>			
				</td>
			</tr>
			<tr>
			<td></td>		
			<td></td>		
            <td colspan=3><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button></td>
        </tr>
	";
        echo "</table>";
echo"</fieldset></td>";
echo"
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
// echo"<div id=listdata style=display:none>";//buka list data
echo"<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
    echo "
            <table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['notransaksi']."</td>
                    <td  align=center>".$_SESSION['lang']['tanggal']."</td>
					 <td  align=center>".$_SESSION['lang']['unit']."</td>
                    <td  align=center>".$_SESSION['lang']['supplier']."</td>
                    <td  align=center>".$_SESSION['lang']['unit']."<br>".$_SESSION['lang']['induk']."</td>
                    <td align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</td>
                    <td  align=center>".$_SESSION['lang']['kg']."</td>
                    <td  align=center>".$_SESSION['lang']['rp']."</td>
                    <td  align=center>".$_SESSION['lang']['rp']."<br>Pembulatan</td>
                    <td  align=center>".$_SESSION['lang']['noreferensi']."</td>   
                    <td  align=center colspan=6>".$_SESSION['lang']['action']."</td>    
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
	";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');
// echo "<fieldset style=float:left>
echo "<fieldset style=float:left>

<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\"></td>		
		
		<td valign=top rowspan=4>".$_SESSION['lang']['keterangan']."</td> 
			<td valign=top rowspan=4>:</td>
			<td  rowspan=4 valign=top><textarea rows='4' id=keteranganht type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\"></textarea></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit onchange=getsupplier(); style=\"width:155px;\" >'".$optunit."'</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['supplier']."</td>
		<td>:</td>		
		<td>
			<select id=divisi  style=\"width:155px;\" ></select>
		</td>
	</tr>

	<tr>
		<td>".$_SESSION['lang']['unit']." Tagihan</td>
		<td>:</td>		
		<td>
			<select id=unitinv  style=\"width:155px;\" ></select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dokumen']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal placeholder='Tanggal tbs' name=tanggal name=tanggal  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/></td>
		<td>".$_SESSION['lang']['persen']." ".$_SESSION['lang']['ppn']."</td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber value='11' id=persenppn name=persenppn onkeypress=\"return angka_doang(event)\" onkeypress=\"return isNumberKey(event);\" maxlength=5 style=width:70px; placeholder=0 />
	 		Persen PPh :
			<input type=text class=myinputtextnumber value='0.25' id=persenpph name=persenpph onkeypress=\"return angka_doang(event)\" onkeypress=\"return isNumberKey(event);\" maxlength=5 style=width:70px; placeholder=0 />
		</td>
	</tr>
	<tr>	
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</td>
		<td>:</td>	
		<td>
			<input type=text class=myinputtext  placeholder='Tanggal tbs' id=tanggaltbs1 name=tanggaltbs1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
			s/d <input type=text class=myinputtext placeholder='Tanggal tbs' id=tanggaltbs2 name=tanggaltbs2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
		</td>
	</tr>
	<tr>
		<td hidden>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['afiliasi']."</td>
		<td hidden>:</td>		
		<td hidden><input type=text id=noafiliasi size=20 disabled class=myinputtext style=\"width:150px;\"></td>		
	</tr>
	<tr>
		<td align=center colspan=9><button  id=saveht class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button>
		
	</tr>
	</table>
</fieldset>";//<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button></td>



echo "<fieldset style=float:left;width:500px;>

<legend><b>".$_SESSION['lang']['info']."</b></legend>
	<ol>
	
		<li>Lakukan Proses ambil kg timbangan terlebih dahulu</li>
		<li>Pastikan nama KUD sudah terdaftar dan sudah disetting</li>
		<li>Tanggal Dokumen terisi tanggal transaksi</li>
		<li>Tanggal TBS mengikuti harga yang disetting oleh Finance RO</li>
		<li>Jika muncul informasi <font color=red>Harga Belum diinput/disetujui</font>, artinya belum ada harga untuk data tersebut, maka hubungi pihak finance RO, dengan menyebutkan tanggal, grade, supplier tbs, dan tanggal pabrik</li>
		<li>Jika Asignment tidak muncul, daftarkan terlebih dahulu melalui <b>Pengadaan - Setup - Data Supplier / Kontraktor</b> dengan Jenis Usaha : <b>BAHAN BAKU (TBS) EXTERNAL</b></li>
		</ol>
</fieldset>";

CLOSE_BOX();
echo"</div>";



$border='0';
echo "<div id=detail style=display:none>";
// echo "<div id=detail style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
// echo "<fieldset style=float:left>

	
	echo "
    <fieldset>
            <legend><b>".$_SESSION['lang']['list']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['tanggal']."<br>".$_SESSION['lang']['pabrik']."</td>
                    <td  align=center>".$_SESSION['lang']['noTiket']."</td>
                    <td  align=center>".$_SESSION['lang']['kodeblok']."</td>
                    <td  align=center>".$_SESSION['lang']['tahuntanam']."</td>
                    <td  align=center>".$_SESSION['lang']['nospb']."</td>
                    <td  align=center>".$_SESSION['lang']['kodevhc']."</td>
                    <td  align=center>".$_SESSION['lang']['bjr']."</td>
                    <td  align=center>".$_SESSION['lang']['berat']."</td>    
                    <td  align=center>".$_SESSION['lang']['potongan']."</td>    
                    <td  align=center>".$_SESSION['lang']['beratBersih']."</td>    
                    <td  align=center>".$_SESSION['lang']['rpperkg']."</td>    
                    <td  align=center>".$_SESSION['lang']['total']."</td>      
                    <td  align=center>".$_SESSION['lang']['info']."</td>      
                </tr>  
            </thead>
             <tbody id=listdatadt> 
             </tbody>
             </table>
	</fieldset>";

CLOSE_BOX();
echo"</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>