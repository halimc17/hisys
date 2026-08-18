<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_kwitansi').'</span>');
//print_r($_SESSION['temp']);
?>

<script language=javascript src='js/keu_kwitansi.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<!--deklarasi untuk option-->
<?php

$nmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$nmcustomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


$optunit=$optnokontrak=$optpt=$optttd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

// $optunit.="<option value=".$_SESSION['empl']['lokasitugas'].">".$_SESSION['empl']['lokasitugas']." - ".$nmorg[$_SESSION['empl']['lokasitugas']]."</option>";


$str="select * from ".$dbname.".user_orgdetail where namauser='".$_SESSION['standard']['username']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$nmorg[$bar['kodeorganisasi']]."</option>";
}


$str="select a.*,b.namakaryawan from ".$dbname.".pmn_5ttd a left join ".$dbname.".datakaryawan b on a.nama=b.karyawanid";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optttd.="<option value='".$bar['nama']."'>".$bar['namakaryawan']."</option>";
}

?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo"
				<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						".$_SESSION['lang']['notransaksi']." : 
						<input type=text id=notransaksisch nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:110px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
						
						&nbsp".$_SESSION['lang']['tanggal']." : 
						<input type=text placeholder=dd-mm-yyyy id=tglsch onmousemove='setCalendar(this.id)' readonly onkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
						
						&nbsp".$_SESSION['lang']['telahterimadari']." : 
						<input type=text  id=telahterimadarisch nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:100px;\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
						
						&nbsp".$_SESSION['lang']['keterangan']." : 
						<input type=text  id=keterangansch nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:150px;\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
						
						<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
						<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
					</td> 
				</tr>
			</table>
			";
echo"</fieldset></td>
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>

<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php echo"
<div id=listData style=display:block>";//buka list data
OPEN_BOX();
	echo "
	
		<div id=contain  style=display:block> 
                    <script>loaddata(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data

echo "<div id=headher style=display:none>";//buka diff
OPEN_BOX();//<td><select id=kdorg disabled style=\"width:150px;\"><option  value='".$kdor."'>".$nmor."</option></select></td>
echo "
<fieldset>
		<legend>".$_SESSION['lang']['formpermintaan']."</legend>
		<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td> 
			<td>:</td>
			<td><input type=text  id=notransaksi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" placeholder=\"auto generate\" disabled></td>
			
			<td>".$_SESSION['lang']['tanggal']."</td> 
			<td>:</td>
			<td><input type='text' class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:200px;' value='".date('d-m-Y')."' readonly/></td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td> 
			<td>:</td>
			<td><select id=kodeunit  onchange=getpt() style=\"width:205px;\">" . $optunit . "</select>
			</td>
			
			<td>".$_SESSION['lang']['pt']."</td> 
			<td>:</td>
			<td><select id=kodept style=\"width:205px;\">" . $optpt . "</select>
			</td>
			
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['NoKontrak']."</td>
			<td>:</td>
			<td><select style=\"width:180px;\" id=nokontrak  onchange=getterima();>" . $optnokontrak . "</select>
						<img id='nokontrak' onclick=z.elSearch('nokontrak',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
				
			<td>".$_SESSION['lang']['telahterimadari']."</td> 
			<td>:</td>
			<td><input type=text  id=telahterimadari nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext 	  style=\"width:200px;\" maxlength=255 >
			</td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jumlah']."</td> 
			<td>:</td>
			<td><input type=text onblur=getterima(); id=jumlah onkeyup=\"z.numberFormat('jumlah',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:200px;\" maxlength=100 ></td>



			<td>Kota <i>(dibuat di)</i></td>
			<td>:</td>		
			<td>
				<input type=text id=kota  onkeypress=\"return tanpa_kutip(event)\"  class=myinputtext style=\"width:200px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tandatangan']."</td> 
			<td>:</td>
			<td><select id=ttd style=\"width:205px;\">" . $optttd . "</select>
			</td>
			
		</tr>
		<tr>
			<td valign=top>".$_SESSION['lang']['keterangan']."</td> 
			<td valign=top>:</td>
			<td colspan=4><textarea type=text  id=keterangan nkeypress=\"return_tanpa_kutip(event);\" style=\"min-width:495px;\" maxlength=255 rows=3></textarea>
			</td>
		</tr>
		<tr>
		<td colspan=9 align=center>
			<button id=savehead class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			<button id=savehead class=mybutton onclick=add_new_data()>".$_SESSION['lang']['baru']."</button>
			<input type=hidden id=method value='insert'>
		</td>
		</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";




echo close_body();			
?>
    
