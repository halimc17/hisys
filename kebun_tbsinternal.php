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

<script language=javascript src='js/kebun_tbsinternal.js?v=<?php echo time(); ?>'></script>
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


$optbuyer=$optbarang=$opttipe=$opttangki=$optpt=$optpelayaran=$optkapal=$optponton=$optfranco="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');


$unit=$_SESSION['empl']['lokasitugas'];
$str = "select * from ".$dbname.".organisasi where tipe='PABRIK' and namaorganisasi not like '%BULKING%'";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   @$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


$str = "select * from ".$dbname.".organisasi where tipe='KEBUN' and inti=1";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   @$optkud.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('kebun_tbsinternal').'</span>');
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
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
   
            <table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
                    <th  align=center>".$_SESSION['lang']['nourut']."</th>
                    <th  align=center>".$_SESSION['lang']['notransaksi']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."</th>
					 <th  align=center>".$_SESSION['lang']['unit']."<br>".$_SESSION['lang']['pabrik']."</th>
                    <th  align=center>".$_SESSION['lang']['supplier']."</th>
                    <th  align=center>".$_SESSION['lang']['unit']."</th>
                    <th align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</th>
                    <th  align=center>".$_SESSION['lang']['kg']."</th>
                    <th  align=center>".$_SESSION['lang']['rp']."</th>
                    <th  align=center>".$_SESSION['lang']['rp']."<br>Pembulatan</th>
                    <th  align=center colspan=7>".$_SESSION['lang']['action']."</th>    
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
	
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit  style=\"width:155px;\" >'".$optunit."'</select>
		</td>
	
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi disabled class=myinputtext style=\"width:220px;\"></td>		
		
	
		
	</tr>
	<tr>
		<td>".$_SESSION['lang']['supplier']."</td>
		<td>:</td>		
		<td>
			<select id=divisi  style=\"width:155px;\" >'".$optkud."'</select>
		</td>
		<td valign=top rowspan=5>".$_SESSION['lang']['keterangan']."</td> 
			<td valign=top rowspan=5>:</td>
			<td  rowspan=5 valign=top><textarea rows='5' id=keteranganht type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\"></textarea></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dokumen']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal placeholder='Tanggal tbs' name=tanggal name=tanggal  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/></td>
	</tr>
	<tr>	
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."  ".$_SESSION['lang']['pabrik']." </td>
		<td>:</td>	
		<td>
			<input type=text class=myinputtext  placeholder='Tanggal tbs' id=tanggaltbs1 name=tanggaltbs1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
			s/d <input type=text class=myinputtext  placeholder='Tanggal tbs' id=tanggaltbs2 name=tanggaltbs2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['afiliasi']."</td>
		<td>:</td>		
		<td><input type=text id=noafiliasi size=20 disabled class=myinputtext style=\"width:150px;\"></td>		
		</td>
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
		<li>Tanggal TBS Pabrik mengikuti harga yang disetting oleh Finance RO</li>
		<li>Contoh Kasus : Finance RO membuat harga untuk tanggal 01-07-2020 sampai 04-07-2020<br>
				<table>
				<tr>
					<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dokumen']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext  readonly value='01-07-2020' style=width:61px;/></td>	
				</tr>
				<tr>	
					<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</td>
					<td>:</td>	
					<td>
						<input type=text class=myinputtext  readonly value='01-07-2020' style=width:61px;/>
						s/d
						<input type=text class=myinputtext  readonly value='04-07-2020' style=width:61px;/>
					</td>
				</tr>
				</table>
		</li>
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
    
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <th  align=center>".$_SESSION['lang']['nourut']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."<br>".$_SESSION['lang']['nospb']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."<br>".$_SESSION['lang']['pabrik']."</th>
                    <th  align=center>".$_SESSION['lang']['nospb']."</th>
                    <th  align=center>".$_SESSION['lang']['noTiket']."</th>
                    <th  align=center>".$_SESSION['lang']['kodevhc']."</th>
                    <th  align=center>".$_SESSION['lang']['kode']."<br>Hamparan</th>
                    <th  align=center>".$_SESSION['lang']['nama']."<br>Hamparan</th>
                    <th  align=center>".$_SESSION['lang']['tahuntanam']."</th>
                    <th  align=center>".$_SESSION['lang']['berat']."</th>    
                    <th  align=center>".$_SESSION['lang']['potongan']."</th>    
                    <th  align=center>".$_SESSION['lang']['beratBersih']."</th>    
                    <th  align=center>".$_SESSION['lang']['rpperkg']."</th>    
                    <th  align=center>".$_SESSION['lang']['total']."</th>     
                    <th  align=center>".$_SESSION['lang']['info']."</th>    
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