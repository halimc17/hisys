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

<script language=javascript src='js/pmn_batransport.js?v=<?php echo time(); ?>'></script>
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


$optunit=$opttipe=$optspk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

$str = "select * from ".$dbname.".organisasi where tipe='PABRIK' and namaorganisasi not like '%BULKING%'";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
  @$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$opttipe.="<option value='ipkd'>IPKD</option>";
$opttipe.="<option value='etc'>ETC</option>";
$opttipe.="<option value='sip'>SIP</option>";

?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pmn_batransport').'</span>');
echo"<table>
   <tr valign=middle>
	 <td align=center style='width:70px;cursor:pointer;' onclick=newdata()>
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
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:63px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:63px;/>			
				</td>
				
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
   <div class=table-scroll style='height:70vh'>
      <table cellpadding=5 cellspacing=1 border=0 class=sortable>
      <thead>
        <tr class=rowheader>
          <th align=center>".$_SESSION['lang']['nourut']."</th>
          <th align=center>".$_SESSION['lang']['notransaksi']."</th>
		  <th align=center>".$_SESSION['lang']['tipetransaksi']."</th>
          <th align=center>".$_SESSION['lang']['nospk']."</th>
          <th align=center>".$_SESSION['lang']['NoKontrak']."</th>
          <th align=center>".$_SESSION['lang']['transportir']."</th>
          <th align=center>".$_SESSION['lang']['tanggal']."</th>
		  <th align=center>".$_SESSION['lang']['unit']."<br>".$_SESSION['lang']['pabrik']."</th>
          <th align=center>".$_SESSION['lang']['beratBersih']."<br>".$_SESSION['lang']['kirim']."</th>
          <th align=center>".$_SESSION['lang']['beratBersih']."<br>".$_SESSION['lang']['diterima']."</th>
          <th align=center>".$_SESSION['lang']['selisih']."</th>
          <th align=center>".$_SESSION['lang']['jumlahrp']."</th>
          <th align=center>".$_SESSION['lang']['beratBersih']."<br>".$_SESSION['lang']['klaim']."</th>
          <th align=center>".$_SESSION['lang']['jumlahrp']."<br>".$_SESSION['lang']['klaim']."</th>
          <th align=center>".$_SESSION['lang']['keterangan']."</th>
          <th align=center>".$_SESSION['lang']['createby']."</th>
          <th align=center colspan=5>".$_SESSION['lang']['action']."</th>  
        </tr> 
      </thead>
       <tbody id=contain> 
        <script>loaddata(0)</script>
       </tbody>
      <tfoot id=footData>
       </tfoot>
       </table></div>
	";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');
// echo "<fieldset style=float:left>
echo "<fieldset>

<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\"></td>		
		
		<td>".$_SESSION['lang']['tipe']."</td>
		<td>:</td>		
		<td>
			<select id=tipe onchange=getnospk() style=\"width:155px;\" >'".$opttipe."'</select>
		</td>
		
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dokumen']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal placeholder='Tanggal Dokumen Berita Acara' name=tanggal name=tanggal readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:150px;/></td>
		
		
		
		<td valign=top>".$_SESSION['lang']['keterangan']."</td>	
		<td valign=top>:</td>
		<td colspan=3 rowspan=2  valign=top><textarea rows='2' id=keterangan placeholder='keterangan' type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:300px;\"></textarea>
		</td>
		
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit style=\"width:155px;\" onchange=getnospk()>'".$optunit."'</select>
		</td>
		
		<td>".$_SESSION['lang']['nospk']."</td>
		<td>:</td>		
		<td>
			<select id=nospk style=\"width:155px;\">'".$optspk."'</select>
		</td>
		
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kirim']."</td>
		<td>:</td>	
		<td>
			<input type=text class=myinputtext placeholder='Tanggal Mulai Kirim' id=tanggalkirim1 name=tanggalkirim1 readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:61px;/>
			s/d <input type=text class=myinputtext placeholder='Tanggal Sampai Kirim' id=tanggalkirim2 name=tanggalkirim2 readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:61px;/>
		</td>
	</tr>
	
	
	<tr>
		<td colspan=2></td>
		<td colspan=10><button id=saveht class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button><button id=cancelhtd class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button></td>
	</tr>
	</table>
</fieldset>";//<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button></td>



// echo "<fieldset style=float:left;width:500px;>

// <legend><b>".$_SESSION['lang']['info']."</b></legend>
	// <ol>
	
		// <li>HAHAHAHHA</li>
		
		// </ol>
// </fieldset>";

CLOSE_BOX();
echo"</div>";



$border='0';
echo "<div id=detail style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
	echo "
 
	  <div class=table-scroll style='height:45vh'>
      <table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
      <thead>
        <tr class=rowheader>";
					echo "<th align=center>".$_SESSION['lang']['nourut']."</th>";
					echo "<th align=center>".$_SESSION['lang']['nospk']."</th>";
					echo "<th align=center>".$_SESSION['lang']['komoditi']."</th>";
					echo "<th align=center>".$_SESSION['lang']['NoKontrak']."</th>";
					echo "<th align=center>".$_SESSION['lang']['transportir']."</th>";
					echo "<th align=center>".$_SESSION['lang']['noTiket']." ".$_SESSION['lang']['kirim']."</th>";
					echo "<th align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kirim']."</th>";
					echo "<th align=center>".$_SESSION['lang']['nopol']."</th>";
					echo "<th align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['kirim']."</th>";
					echo "<th align=center>".$_SESSION['lang']['noTiket']." ".$_SESSION['lang']['tujuan']."</th>";
					echo "<th align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."</th>";
					echo "<th align=center>Tonbag</th>";
					echo "<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."</th>";
					echo "<th align=center>".$_SESSION['lang']['selisih']."<br>(".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."-".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['kirim'].")</th>";
					
					echo "<th align=center>".$_SESSION['lang']['rpperkg']."</th>";
					echo "<th align=center>".$_SESSION['lang']['jumlahrp']."</th>";
					echo "<th align=center>".$_SESSION['lang']['toleransi']." (%)</th>";
					echo "<th align=center>".$_SESSION['lang']['toleransi']." (Kg)</th>";
					echo "<th align=center>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['klaim']."<br>(".$_SESSION['lang']['selisih']."-".$_SESSION['lang']['kg']."<br>".$_SESSION['lang']['klaim'].")</th>";
					echo "<th align=center>".$_SESSION['lang']['rpperkg']." ".$_SESSION['lang']['klaim']."</th>";
					echo "<th align=center>".$_SESSION['lang']['jumlahrp']." ".$_SESSION['lang']['klaim']."</th>";
					echo "<th align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['debet']."</th>";
        echo "</tr> 
      </thead>
       <tbody id=listdatadt> 
       </tbody>
       </table>";

CLOSE_BOX();
echo"</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>