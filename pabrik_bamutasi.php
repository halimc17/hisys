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

<script language=javascript src='js/pabrik_bamutasi.js?v=<?php echo time(); ?>'></script
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<!--deklarasi untuk option-->

<?php

$optbuyer=$optbarang=$opttipe=$opttangki=$optpt=$optunitreferensi=$opttransportasi=$optpelayaran=$optkapal=$opttipepengiriman=$optponton=$optfranco=$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".pmn_4customer  order by namacustomer asc";
$res=fetchdata($str);
foreach($res as $bar){
    $optbuyer.="<option value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
}

$unit=$_SESSION['empl']['lokasitugas'];
// $str = "select * from ".$dbname.".organisasi where kodeorganisasi='".$unit."'";
// $res=fetchdata($str);
// foreach($res as $bar){
    // @$optunit="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
// }


$arrunit=array();
$arrunit=getOrgDetail(13);
foreach($arrunit as $val=>$nama){
    $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
} 

$str = "select * from ".$dbname.".organisasi where kodeorganisasi!='".$unit."' and (tipe='PABRIK' or tipe='BULKING')";
$res=fetchdata($str);
foreach($res as $bar){
	
	$optunitreferensi.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";

}



$str = "select * from ".$dbname.".organisasi where tipe='PT'";
$res=fetchdata($str);
foreach($res as $bar){
   @$optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
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
$res=fetchdata($str);
foreach($res as $bar){
    $optbarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}

$opttipe.="<option value='OUT'>".$_SESSION['lang']['pengiriman']."</option>";
$opttipe.="<option value='IN'>".$_SESSION['lang']['penerimaan']."</option>";

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



$str = "select * from ".$dbname.".pmn_5franco order by franco_name asc";
$res=fetchdata($str);
foreach($res as $bar){
    $optfranco.="<option value='".$bar['id_franco']."'>".$bar['franco_name']."</option>";
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('TRANSPORTIR') order by a.namasupplier asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optpelayaran.="<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}

$str = "SELECT * FROM " . $dbname . ".pmn_5kapalponton";
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['jenis']=='KPL'){
		$optkapal.="<option value=" . $bar['kode'] . ">" . $bar['nama'] . "</option>";
	}
	
	if($bar['jenis']=='PNT'){
		$optponton.="<option value=" . $bar['kode'] . ">" . $bar['nama'] . "</option>";
	}
}


?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_bamutasi').'</span>');
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	echo"<table>";
	echo"<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>		
			<td>
				<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:150px;\">
			</td>
			
			<td>".$_SESSION['lang']['kodept']."</td>
			<td>:</td>		
			<td>
				<select id=kodeptsch  style=\"width:150px;\" >'".$optpt."'</select>
			</td>
			
			<td>".$_SESSION['lang']['komoditi']."</td>
			<td>:</td>		
			<td>
				<select id=kodebarangsch  style=\"width:150px;\">'".$optbarang."'</select>
			</td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>		
			<td>
				<select id=tipesch  style=\"width:150px;\" >'".$opttipe."'</select>
			</td>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>		
			<td>
				<select id=unitsch  style=\"width:150px;\" >'".$optunit."'</select>
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
			<td colspan=3><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button></td>
		</tr>";
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
    echo " <div class=table-scroll style='height:360px'>
            <table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
                    <th  align=center>".$_SESSION['lang']['nourut']."</th>
                    <th  align=center>".$_SESSION['lang']['notransaksi']."</th>
                    <th  align=center>".$_SESSION['lang']['tipe']."</th>
                    <th  align=center>".$_SESSION['lang']['nourut']." SIP</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."</th>
                    <th  align=center>".$_SESSION['lang']['unit']."</th>
                    <th  align=center>".$_SESSION['lang']['stok']." ".$_SESSION['lang']['pt']."</th>
                    <th  align=center>".$_SESSION['lang']['kodebarang']."</th>
                    <th  align=center>".$_SESSION['lang']['jumlah']."</th>
                    <th  align=center>".$_SESSION['lang']['noreferensi']."</th>
                    <th  align=center>".$_SESSION['lang']['updateby']."</th>
                    <th  align=center>".$_SESSION['lang']['action']."</th>    
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table></div>";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span>');
// echo "<fieldset style=float:left>
echo "<fieldset>

<legend><b>".$_SESSION['lang']['form']."</b></legend>
Note :<br> 1. Untuk kolom unit referensi berisikan :<br>
	- jika tipe transaksi keluar, maka kolom ini berisikan unit tujuan komoditi<br>
	- jika tipe transaksi masuk, maka kolom ini berisikan unit asal komoditi<br>
	- contoh : pengiriman Palm kernel SDKM (pabrik pt sdk) ke KSBW (bulking ksbw),<br> 
		maka saat buat transaksi keluar. unit : sdkm, unit ref : ksbw,
		saat transaksi masuk, unit : ksbw, unit ref : sdkm<br>
	2. Stok PT harus terisi PT dimana pemilik stok barang ini<br>	<br>	
		
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit  style=\"width:150px;\" >'".$optunit."'</select>
		</td>
		
		<td>".$_SESSION['lang']['unit']." Referensi</td>
		<td>:</td>		
		<td>
			<select id=unitreferensi  style=\"width:150px;\" >'".$optunitreferensi."'</select>
		</td>
		
		<td>".$_SESSION['lang']['stok']." ".$_SESSION['lang']['pt']."</td>
		<td>:</td>		
		<td>
			<select id=kodept  style=\"width:150px;\">'".$optpt."'</select>
		</td>
		
		<td>".$_SESSION['lang']['komoditi']."</td>
		<td>:</td>		
		<td>
			<select id=kodebarang  style=\"width:150px;\">'".$optbarang."'</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggalberangkat']."</td>
		<td>:</td>	
		<td><input type=text class=myinputtext placeholder='Tanggal Berangkat' id=tanggalberangkat name=tanggalberangkat readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			<select id=jmberangkat>".$jm."</select>:<select id=mnberangkat>".$mnt."</select></td>

	
		<td>".$_SESSION['lang']['tanggaltiba']."</td>
		<td>:</td>	
		<td><input type=text class=myinputtext placeholder='Tanggal Tiba' id=tanggaltiba name=tanggaltiba readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			<select id=jmtiba>".$jm."</select>:<select id=mntiba>".$mnt."</select></td>
	
		<td>".$_SESSION['lang']['tanggalmulai']." ".$_SESSION['lang']['bongkarmuat']."</td>
		<td>:</td>	
		<td><input type=text class=myinputtext placeholder='Tanggal mulai bongkar' id=tanggalbongkar1 name=tanggalbongkar1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			<select id=jmbongkar1>".$jm."</select>:<select id=mnbongkar1>".$mnt."</select></td>
	
		<td>".$_SESSION['lang']['tanggalselesai']." ".$_SESSION['lang']['bongkarmuat']."</td>
		<td>:</td>	
		<td><input type=text class=myinputtext placeholder='Tanggal selesai bongkar' id=tanggalbongkar2 name=tanggalbongkar2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			<select id=jmbongkar2>".$jm."</select>:<select id=mnbongkar2>".$mnt."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal name=tanggal name=tanggal  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>	
		
		<td>".$_SESSION['lang']['tipetransaksi']."</td>
		<td>:</td>		
		<td>
			<select id=tipe style=\"width:150px;\">'".$opttipe."'</select>
		</td>	
		
		<td>".$_SESSION['lang']['transportasi']."</td>
		<td>:</td>		
		<td>
			<select id=transportasi style=\"width:150px;\">'".$opttransportasi."'</select>
		</td>	
		
		<td>".$_SESSION['lang']['transportir']."</td>
		<td>:</td>		
		<td>
			<select id=transportir style=\"width:150px;\">'".$optpelayaran."'</select>
			<img id='transportir' onclick=z.elSearch('transportir',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>	
		
	</tr>
	
	 <tr>
		
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\"></td>	
		
		<td>".$_SESSION['lang']['noreferensi']."</td>
		<td>:</td>		
		<td>
			<input type=text id=noreferensi size=20 disabled class=myinputtext style=\"width:100px;\">
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tombolnoreferensi class=resicon onclick=carinoreferensi('".$_SESSION['lang']['find']."',event)>
			</td>
			
			<td>".$_SESSION['lang']['namakapal']."</td>
		<td>:</td>		
		<td>		<select id=namakapal style=\"width:150px;\">'".$optkapal."'</select>	</td>
		
		
		<td>".$_SESSION['lang']['namaponton']."</td>
		<td>:</td>		
		<td>
			<select id=namaponton style=\"width:150px;\">'".$optponton."'</select>
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
						<td>".$_SESSION['lang']['nosipb']."</td>
						<td>:</td>		
						<td>
							<input type=text id=nosip size=20 disabled class=myinputtext style=\"width:150px;\">
							<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tombolnosip class=resicon onclick=getsipb('".$_SESSION['lang']['find']."',event)>
						</td>
					
						<td>".$_SESSION['lang']['tangki']." </td>
						<td>:</td>		
						<td>
							<select id=kodetangki  style=\"width:150px;\">'".$opttangki."'</select>
						</td>
						
						<td valign=top rowspan=3>".$_SESSION['lang']['keterangan']."</td> 
							<td valign=top rowspan=3>:</td>
							<td rowspan=3 colspan=4><textarea rows='2'  id=keterangandt type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:339px;\"></textarea></td>
						</tr>
					</tr>
					
					<tr>
						<td>".$_SESSION['lang']['total']."</td>
						<td>:</td>
						<td><input type=text id=jumlah  size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:150px;\"> Kg&nbsp;&nbsp;</td>							
						</td>
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
						<td><input type=text id=jumlah".$i." onblur=gettotal() value=0  size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"> Kg&nbsp;&nbsp;</td>		
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
    <fieldset>
            <legend><b>".$_SESSION['lang']['list']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['nosipb']."</td>
                    <td  align=center>".$_SESSION['lang']['kodetangki']."</td>
                    <td  align=center>".$_SESSION['lang']['jumlah']."</td>    
                    <td  align=center>".$_SESSION['lang']['keterangan']."</td>
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