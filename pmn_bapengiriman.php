<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
// include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>


<script language=javascript src='js/pmn_bapengiriman.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<!--deklarasi untuk option-->

<?php

$optbuyer=$optbarang=$opttipe=$opttangki=$optpt=$optpelayaran=$optkapal=$optponton=$optfranco="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
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
    $optunit="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$str = "select * from ".$dbname.".organisasi where tipe='PT'";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."'";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$select='';
	if($bar['kodetangki']=='ST01'){
		$select="selected";
	}
	$opttangki.="<option value='".$bar['kodetangki']."' ".$select.">[".$bar['komoditi']."]&nbsp;&nbsp;".$bar['keterangan']."</option>";
}

$str = "select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}

$opttipe.="<option value='OUT'>".$_SESSION['lang']['pengiriman']."</option>";
$opttipe.="<option value='IN'>".$_SESSION['lang']['penerimaan']."</option>";
$opttipe.="<option value='SALES'>".$_SESSION['lang']['penjualan']."</option>";

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



$str = "select * from ".$dbname.".pmn_5franco order by franco_name asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optfranco.="<option value='".$bar['id_franco']."'>".$bar['franco_name']."</option>";
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('TRANSPORTIR') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$optpelayaran.="<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}

$str="select * from ".$dbname.".pmn_5kapalponton";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['jenis']=='KPL'){
		$optkapal.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
	}
	if($bar['jenis']=='PNT'){
		$optponton.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
	}
}
?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pmn_bapengiriman').'</span>');
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
					<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:180px;\">
				</td>
				
				
				<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['bast']."</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>			
				</td>
				
				<td>".$_SESSION['lang']['NoKontrak']."</td>
				<td>:</td>		
				<td>
					<input type=text id=nokontraksch size=50 class=myinputtext style=\"width:180px;\">
				</td>
			</tr>
			
			<tr>
			<td>Pasang Selang</td>
			<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalpasang1sch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
					s/d
					<input type=text class=myinputtext id=tanggalpasang2sch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>			
				</td>
				
				<td>Mulai Pompa (CPO) / Muat (PK)</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalmuat1sch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
					s/d
					<input type=text class=myinputtext id=tanggalmuat2sch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>			
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
    
            <table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
                    <th  align=center>".$_SESSION['lang']['nourut']."</th>
                    <th  align=center>".$_SESSION['lang']['notransaksi']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."</th>
                    <th  align=center>".$_SESSION['lang']['unit']."</th>
                    <th  align=center>".$_SESSION['lang']['kodebarang']."</th>
                    <th  align=center>".$_SESSION['lang']['jumlah']."</th>
                    <th  align=center>".$_SESSION['lang']['updateby']."</th>    
                    <th  align=center colspan=5>".$_SESSION['lang']['action']."</th>    
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
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span>');
// echo "<fieldset style=float:left>
echo "<fieldset>

<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit  style=\"width:150px;\" >'".$optunit."'</select>
		</td>
		<td>".$_SESSION['lang']['komoditi']."</td>
		<td>:</td>		
		<td>
			<select id=kodebarang  style=\"width:150px;\">'".$optbarang."'</select>
		</td>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\"></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal name=tanggal name=tanggal  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>	
		
		<td>".$_SESSION['lang']['tanggalberangkat']."</td>
		<td>:</td>	
		<td><input type=text class=myinputtext placeholder='Tanggal Berangkat' id=tanggalberangkat name=tanggalberangkat readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			<select id=jmberangkat>".$jm."</select>:<select id=mnberangkat>".$mnt."</select></td>

		<td valign=top rowspan=3>".$_SESSION['lang']['keterangan']."</td> 
			<td valign=top rowspan=3>:</td>
			<td rowspan=3><textarea rows='3'  id=keteranganht type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:300px;\"></textarea></td>
	</tr>
	<tr>
	<td>".$_SESSION['lang']['pelabuhantujuan']."</td>
		<td>:</td>		
		<td>
			<select id=pelabuhantujuan style=\"width:150px;\">'".$optfranco."'</select>
		</td>	
		
		<td>".$_SESSION['lang']['transportir']."</td>
		<td>:</td>		
		<td>
			<select id=transportir style=\"width:150px;\">'".$optpelayaran."'</select>
			<img id='transportir' onclick=z.elSearch('transportir',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>	
	</tr>	
	<tr>	
		<td>".$_SESSION['lang']['namakapal']."</td>
		<td>:</td>		
		<td>
			
			<select id=namakapal style=\"width:150px;\">'".$optkapal."'</select>
		</td>		
		<td>".$_SESSION['lang']['namaponton']."</td>
		<td>:</td>		
		<td>
			<select id=namaponton style=\"width:150px;\">'".$optponton."'</select>
		</td>
	</tr>
	<tr>
		<td align=center colspan=9><button  id=saveht class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button>
		<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button></td>
	</tr>
	</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";


#- <!--UNTUK BUAT FORM INPUT HEADER-->

$border='0';
echo "<div id=detail style=display:none>";
// echo "<div id=detail style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
// echo "<fieldset style=float:left>
echo "<fieldset>
<legend><b>".$_SESSION['lang']['form']."</b></legend>
	<table cellspacing=1 border=".$border.">
		<tr>
			<td colspan=2>
				<table cellspacing=1 border=".$border.">
					<tr>
						<td>".$_SESSION['lang']['NoKontrak']."</td>
						<td>:</td>		
						<td>
							<input type=text id=nokontrak size=20 disabled class=myinputtext style=\"width:150px;\">
							<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tombolnokontrak class=resicon onclick=carinokontrak('".$_SESSION['lang']['find']."',event)>
							</td>
						<td>".$_SESSION['lang']['kodept']."</td>
						<td>:</td>		
						<td>
							<select id=kodept disabled style=\"width:150px;\">'".$optpt."'</select>
						<td>".$_SESSION['lang']['Pembeli']."</td>
						<td>:</td>		
						<td>
							<select id=kodecustomer disabled value='".@$kodecustomer."'  style=\"width:150px;\">'".$optbuyer."'</select>
						</td>
						<td>".$_SESSION['lang']['tangki']." </td>
						<td>:</td>		
						<td>
							<select id=kodetangki  style=\"width:150px;\">'".$opttangki."'</select>
						</td>
					</tr>
					<tr>
						<td>Pasang Selang</td>
						<td>:</td>
						<td><input type=text class=myinputtext placeholder='Tanggal' id=tanggalpasang1 name=tanggalpasang1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
							<select id=jmpasang1>".$jm."</select>:<select id=mnpasang1>".$mnt."</select></td>
						<td>s/d</td>
						<td>:</td>
							<td><input type=text class=myinputtext placeholder='Tanggal' id=tanggalpasang2 name=tanggalpasang2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
							<select id=jmpasang2>".$jm."</select>:<select id=mnpasang2>".$mnt."</select></td>
						
						<td valign=top rowspan=3>".$_SESSION['lang']['keterangan']."</td> 
							<td valign=top rowspan=3>:</td>
							<td rowspan=3 colspan=4><textarea rows='2'  id=keterangandt type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:339px;\"></textarea></td>
						</tr>
					<tr>
						<td>Mulai Pompa (CPO) / Muat (PK)</td>
						<td>:</td>
						<td><input type=text class=myinputtext placeholder='Tanggal' id=tanggalmuat1 name=tanggalmuat1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
							<select id=jmmuat1>".$jm."</select>:<select id=mnmuat1>".$mnt."</select></td>
						<td>s/d</td>
						<td>:</td>
							<td><input type=text class=myinputtext  placeholder='Tanggal'  id=tanggalmuat2 name=tanggalmuat2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
							<select id=jmmuat2>".$jm."</select>:<select id=mnmuat2>".$mnt."</select></td></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['total']."</td>
						<td>:</td>
						<td><input type=text id=jumlah  disabled size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:150px;\"> Kg&nbsp;&nbsp;</td>							
						
						<td>".$_SESSION['lang']['selisih']."</td>
						<td>:</td>
						<td><input type=text id=selisih onblur=gettotal() size=3 value=0 class=myinputtextnumber style=\"width:150px;\"> Kg&nbsp;&nbsp;</td>							
						
					</tr>
					
				</table>
			</td>
		</tr>";
		echo"<tr>";
			for($i=1;$i<=2;$i++){
				if($i==1){
					$keteranganjudul="Awal";
				}else{
					$keteranganjudul="Akhir";
				}
				echo "<td><fieldset style=float:left>
				<legend><b>".$keteranganjudul." ".$_SESSION['lang']['tangki']."</b></legend>
				<table cellspacing=1 border=0>
					<tr>
						<td>".$_SESSION['lang']['tinggi']."</td>
						<td>:</td>		
						<td><input type=text id=tinggi".$i." onblur=getvol(".$i.")  size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"> Cm&nbsp;&nbsp;</td>		
						
						<td>".$_SESSION['lang']['suhu']."</td>
						<td>:</td>		
						<td><input type=text id=suhu".$i." onblur=getvol(".$i.") size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"> &deg;&nbsp;&nbsp;</td>		
						
						
						<td>".$_SESSION['lang']['jumlah']."</td>
						<td>:</td>		
						<td><input type=text id=jumlah".$i." value=0 onblur=gettotal() size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"> Kg&nbsp;&nbsp;</td>		
						<td>".$_SESSION['lang']['cpoffa']."</td>
						<td>:</td>		
						<td><input type=text  id=ffa".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>					
					</tr>
					<tr>
						<td>".$_SESSION['lang']['moisture']."</td>
						<td>:</td>		
						<td><input type=text  id=moisture".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>						
						
						<td>Dirt</td>
						<td>:</td>		
						<td><input type=text  id=dirt".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>					
						
						<td>Dobi</td>
						<td>:</td>		
						<td><input type=text  id=dobi".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>
						<td>Broken</td>
						<td>:</td>		
						<td><input type=text  id=broken".$i."  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"> %</td>			
					</tr>
				</table>
				</fieldset></td>";
			}
		echo"</tr>";
	echo"<tr>
		<td align=center colspan=9><button  id=save class=mybutton onclick=savedt()>".$_SESSION['lang']['save']."</button>
		<button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button></td>
		<input type=hidden id=method value='insert'>	
	</tr>
	</table></fieldset>";
	echo"";
	// echo"<div id='listdatadetail'></div>";
	
	echo "
		<fieldset><legend>".$_SESSION['lang']['list']."</legend>
            <table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['NoKontrak']."</td>
                    <td  align=center>".$_SESSION['lang']['kodept']."</td>
                    <td  align=center>".$_SESSION['lang']['kodecustomer']."</td>
                    <td  align=center>".$_SESSION['lang']['kodetangki']."</td>
                    <td  align=center>".$_SESSION['lang']['jumlah']."</td>    
                    <td  align=center>".$_SESSION['lang']['action']."</td>    
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