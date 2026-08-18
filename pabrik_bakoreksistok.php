<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
?>

<script language=javascript src='js/pabrik_bakoreksistok.js?v=<?php echo time(); ?>'></script
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<!--deklarasi untuk option-->

<?php

$optbuyer=$optbarang=$opttipe=$opttangki=$optunit=$optunitsch=$optpt=$optunitreferensi=$opttransportasi=$optpelayaran=$optkapal=$opttipepengiriman=$optponton=$optfranco="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".pmn_4customer  order by namacustomer asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbuyer.="<option value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
}
$unit=$_SESSION['empl']['lokasitugas'];
$str = "select * from ".$dbname.".organisasi where kodeorganisasi='".$unit."'";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


$str = "select * from ".$dbname.".organisasi where tipe in ('BULKING','PABRIK')";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$optunitsch.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$str = "select * from ".$dbname.".organisasi where kodeorganisasi!='".$unit."' and (tipe='PABRIK' or tipe='BULKING')";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	
	$optunitreferensi.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";

}



$str = "select * from ".$dbname.".organisasi where tipe='PT'";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   @$optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


$str="select * from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."'";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$select='';
	if($bar['kodetangki']=='ST01'){
		// $select="selected";
	}
	$opttangki.="<option value='".$bar['kodetangki']."' ".$select.">[".$bar['komoditi']."]&nbsp;&nbsp;".$bar['keterangan']."</option>";
}

$str = "select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}

$opttipe.="<option value='IN'>+</option>";
$opttipe.="<option value='OUT'>-</option>";

$opttransportasi.="<option value='DARAT'>".$_SESSION['lang']['darat']."</option>";
$opttransportasi.="<option value='AIR'>Air</option>";

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

?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_bakoreksistok').'</span>');
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
					<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:153px;\">
				</td>
				
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>		
				<td>
					<select id=unitsch  style=\"width:150px;\">'".$optunitsch."'</select>
				</td>
		
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>:</td>		
				<td>
				<select id=tipesch  style=\"width:150px;\">'".$opttipe."'</select>
				</td>
		
			<td>".$_SESSION['lang']['kodetangki']."</td>
			<td>:</td>		
			<td>
				<select id=kodetangkisch  style=\"width:150px;\">'".$opttangki."'</select>
			</td>
			</tr>	
			<tr>	
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>			
				</td>
				
				<td>".$_SESSION['lang']['kodept']."</td>
				<td>:</td>		
				<td>
					<select id=kodeptsch  style=\"width:150px;\">'".$optpt."'</select>
				</td>
				<td>".$_SESSION['lang']['komoditi']."</td>
				<td>:</td>		
				<td>
				<select id=kodebarangsch  style=\"width:150px;\">'".$optbarang."'</select>
				</td>
	
			</tr>
			<tr>
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
    <fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['notransaksi']."</td>
                    <td  align=center>".$_SESSION['lang']['tanggal']."</td>
                    <td  align=center>".$_SESSION['lang']['tipe']."</td>
                    <td  align=center>".$_SESSION['lang']['unit']."</td>
                    <td  align=center>".$_SESSION['lang']['pt']."</td>
                    <td  align=center>".$_SESSION['lang']['kodetangki']."</td>
                    <td  align=center>".$_SESSION['lang']['kodebarang']."</td>
                    <td  align=center>".$_SESSION['lang']['jumlah']."</td>
                    <td  align=center>".$_SESSION['lang']['keterangan']."</td>
                    <td  align=center>".$_SESSION['lang']['updateby']."</td>
                    <td  align=center colspan=4>".$_SESSION['lang']['action']."</td>    
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['input'].'</span>');
// echo "<fieldset style=float:left>
echo "<fieldset>

<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\"></td>	
	
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit  style=\"width:150px;\" >'".$optunit."'</select>
		</td>
		<td>".$_SESSION['lang']['kodept']."</td>
		<td>:</td>		
		<td>
			<select id=kodept  style=\"width:150px;\">'".$optpt."'</select>
		</td>
	</tr>
	
	
	<tr>
		<td>".$_SESSION['lang']['tipe']."</td>
		<td>:</td>		
		<td>
		<select id=tipe  style=\"width:150px;\">'".$opttipe."'</select>
		</td>
		<td>".$_SESSION['lang']['komoditi']."</td>
		<td>:</td>		
		<td>
		<select id=kodebarang  style=\"width:150px;\">'".$optbarang."'</select>
		</td>
		<td>".$_SESSION['lang']['kodetangki']."</td>
		<td>:</td>		
		<td>
			<select id=kodetangki  style=\"width:150px;\">'".$opttangki."'</select>
		</td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>	
		<td><input type=text class=myinputtext placeholder='Tanggal ' id=tanggal name=tanggal readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			<select id=jm>".$jm."</select>:<select id=mn>".$mnt."</select></td>
		
		
		<td>".$_SESSION['lang']['total']."</td>
			<td>:</td>
			<td><input type=text id=jumlah  size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:150px;\"> Kg&nbsp;&nbsp;</td>							
			</td>
		
	</tr>
	
	<tr>	
		<td valign=top rowspan=3>".$_SESSION['lang']['keterangan']."</td> 
			<td valign=top rowspan=3>:</td>
			<td rowspan=3 colspan=4><textarea rows='3'  id=keteranganht type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:400px;\"></textarea></td>
	</tr>
	

	<tr>	
		<td>&nbsp;</td>
	</tr>
	<tr>	
		<td>&nbsp;</td>
	</tr>
	
	
	<tr>
		<td align=center colspan=12><button  id=saveht class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button>
		<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button></td>
	</tr>
	</table>
</fieldset><input type=hidden id=method value='insert'>	";

CLOSE_BOX();
echo"</div>";


echo close_body();		
?>