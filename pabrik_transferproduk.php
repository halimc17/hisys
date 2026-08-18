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

<script language=javascript src='js/pabrik_transferproduk.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<!--deklarasi untuk option-->

<?php

$optbuyer=$optbarang=$opttipe=$opttangki=$optptstok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".pmn_4customer  order by namacustomer asc";
$res=fetchdata($str);
foreach($res as $bar){
    $optbuyer.="<option value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
}
$unit=$_SESSION['empl']['lokasitugas'];
$str = "select * from ".$dbname.".organisasi where kodeorganisasi='".$unit."'";
$res=fetchdata($str);
foreach($res as $bar){
    $optunit="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


$str = "select * from ".$dbname.".organisasi where tipe='PT'";
$res=fetchdata($str);
foreach($res as $bar){
    $optptstok.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


$str="select * from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."'";
$res=fetchdata($str);
foreach($res as $bar){
	$opttangki.="<option value='".$bar['kodetangki']."'>[".$bar['komoditi']."]&nbsp;&nbsp;".$bar['keterangan']."</option>";
}

$str = "select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res=fetchdata($str);
foreach($res as $bar){
    $optbarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}

$opttipe.="<option value='OUT'>".$_SESSION['lang']['pengiriman']."</option>";
$opttipe.="<option value='IN'>".$_SESSION['lang']['penerimaan']."</option>";
// $opttipe.="<option value='SALES'>".$_SESSION['lang']['penjualan']."</option>";

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
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_transferproduk').'</span>');
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
					<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:100px;\">
				</td>
				<td>".$_SESSION['lang']['kodetangki']."</td>
				<td>:</td>		
				<td>
					<select id=kodetangkisch style=\"width:100px;\">'".$opttangki."'</select>
				</td>
			</tr>
			
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>		
				<td colspan=4>
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:130px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:130px;/>
			
				</td>
			</tr>
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
?>
<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php 
// echo"<div id=listdata style=display:none>";//buka list data
echo"<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
    echo "
    
            <table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <th  align=center>".$_SESSION['lang']['nourut']."</th>
                    <th  align=center>".$_SESSION['lang']['notransaksi']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."</th>
                    <th  align=center>".$_SESSION['lang']['unit']."</th>
                    <th  align=center>".$_SESSION['lang']['stok']." ".$_SESSION['lang']['pt']."</th>
                    <th  align=center>".$_SESSION['lang']['tipe']."</th>
                    <th  align=center>".$_SESSION['lang']['kodetangki']."</th>
                    <th  align=center>".$_SESSION['lang']['saldoawal']."</th>
                    <th  align=center>".$_SESSION['lang']['saldoakhir']."</th>
                    <th  align=center>".$_SESSION['lang']['jumlah']."</th>
                    <th  align=center>".$_SESSION['lang']['updateby']."</th>
                    <th  align=center>".$_SESSION['lang']['action']."</th>    
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
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php
echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX();

echo "<fieldset style=float:left>
<legend><b>Header</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\"></td>
	</tr>
	<tr>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal name=tanggal name=tanggal  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>	
	</tr>	
	<tr>
		<td>".$_SESSION['lang']['tipe']."</td>
		<td>:</td>		
		<td>
			<select id=tipe  style=\"width:155px;\">'".$opttipe."'</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggalmulai']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggalmulai name=tanggalmulai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:60px;/>
			<select id=jmmulai>".$jm."</select> : <select id=mnmulai>".$mnt."</select></td>
		</tr>
	<tr>
		
		<td>".$_SESSION['lang']['tanggalselesai']."</td>
		<td>:</td>
		<td><input onkeypress=\"return tanpa_kutip(event)\" type=text readonly class=myinputtext id=tanggalselesai name=tanggalselesai onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:60px;/>
			<select id=jmselesai>".$jm."</select> : <select id=mnselesai>".$mnt."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit  style=\"width:155px;\" >'".$optunit."'</select>
		</td>
	</tr>	
	
	<tr>
		<td>".$_SESSION['lang']['tangki']." </td>
		<td>:</td>		
		<td>
			<select id=kodetangki  style=\"width:155px;\">'".$opttangki."'</select>
		</td>
	</tr>	
	<tr>
		<td>".$_SESSION['lang']['tangki']." ".$_SESSION['lang']['tujuan']."</td>
		<td>:</td>		
		<td>
			<select id=kodetangkitujuan  style=\"width:155px;\">'".$opttangki."'</select>
		</td>
	</tr>	
	
	<tr>
		<td>".$_SESSION['lang']['stok']." ".$_SESSION['lang']['pt']."</td>
		<td>:</td>		
		<td>
			<select id=kodept  style=\"width:155px;\">'".$optptstok."'</select>
		</td>
	</tr>	
	
    <tr hidden>
		<td>".$_SESSION['lang']['NoKontrak']." TIDAK DIPAKAI</td>
		<td>:</td>		
		<td>
			<input type=text id=nokontrak size=20 disabled class=myinputtext style=\"width:150px;\">
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tombolnokontrak class=resicon onclick=carinokontrak('".$_SESSION['lang']['find']."',event)>
			<img src=images/box/close.gif title='".$_SESSION['lang']['cancel']."' id=tombolcancelnokontrak class=resicon onclick=cancelnokontrak('".$_SESSION['lang']['cancel']."',event)>
		</td>
	</tr>	
	 <tr>
		<td>".$_SESSION['lang']['noreferensi']."</td>
		<td>:</td>		
		<td>
			<input type=text id=noreferensi size=20 disabled class=myinputtext style=\"width:150px;\">
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tombolnoreferensi class=resicon onclick=carinoreferensi('".$_SESSION['lang']['find']."',event)>
			<img src=images/box/close.gif title='".$_SESSION['lang']['cancel']."' id=tombolcancelnoreferensi class=resicon onclick=cancelnoreferensi('".$_SESSION['lang']['cancel']."',event)>
		</td>
	</tr>	
	
	<tr>
		<td>".$_SESSION['lang']['total']."</td>
		<td>:</td>
		<td><input type=text id=jumlah  size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:150px;\"> Kg&nbsp;&nbsp;</td>							
		</td>
	</tr>
	
	
	<tr>	
		<td valign=top rowspan=3>".$_SESSION['lang']['keterangan']."</td> 
			<td valign=top rowspan=3>:</td>
			<td  rowspan=3><textarea rows='2'  id=keterangan type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:140px;\"></textarea>
			</td>
			
	</tr>
</table>
</fieldset>";/*

gettotal
*/

for($i=1;$i<=2;$i++){
	if($i==1){
		$keteranganjudul="Awal";
	}else{
		$keteranganjudul="Akhir";
	}
	echo "<fieldset style=float:left>
	<legend><b>".$keteranganjudul." ".$_SESSION['lang']['tangki']."</b></legend>
	<table cellspacing=1 border=0>
		
		
		<tr>
			<td>".$_SESSION['lang']['tinggi']."</td>
			<td>:</td>		
			<td><input type=text id=tinggi".$i." onblur=getvol(".$i.")  size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"> Cm</td>		
			
			<td>".$_SESSION['lang']['cpoffa']."</td>
			<td>:</td>		
			<td><input type=text  id=ffa".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>	
				
		</tr>
		<tr>
			<td>".$_SESSION['lang']['suhu']."</td>
			<td>:</td>		
			<td><input type=text id=suhu".$i." onblur=getvol(".$i.") size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"> &deg;</td>		
			
			<td>".$_SESSION['lang']['moisture']."</td>
			<td>:</td>		
			<td><input type=text  id=moisture".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jumlah']."</td>
			<td>:</td>		
			<td><input type=text id=jumlah".$i."  size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"> Kg</td>		
			<td>Dirt</td>
			<td>:</td>		
			<td><input type=text  id=dirt".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>
		
		<tr>
			<td valign=top rowspan=3>".$_SESSION['lang']['keterangan']."</td> 
			<td valign=top rowspan=3>:</td>
			<td  rowspan=3><textarea rows='2'  id=keterangan".$i." type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:140px;\"></textarea>
			</td>
			
			<td>Dobi</td>
			<td>:</td>		
			<td><input type=text  id=dobi".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>
		</tr>
		<tr>
			<td>Broken</td>
			<td>:</td>		
			<td>
				<input type=text  id=broken".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>
		</tr>
	</table>
	</fieldset>";
}


echo"<table cellspacing=1 border=0 width=100%>
		<tr>
			<td align=center><button  id=save class=mybutton onclick=save()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td>
			<input type=hidden id=method value='insert'>	
			</tr>
	</table>";

CLOSE_BOX();
echo"</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>