<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
require_once('pmn_spk_nospk_slave.php');
?>

<script language=javascript src='js/kebun_tbsjual.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<!--deklarasi untuk option-->

<?php

$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikKar=makeOption($dbname,'datakaryawan','karyawanid,nik');

$optunit=$optcustomer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunitro=$optcustomer=$optnokontrak="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' and inti=1";
$res=fetchdata($str);
foreach($res as $bar){ 
	@$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
}

$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KANWIL' or tipe='HOLDING' and inti=1 and induk!=''";
$res=fetchdata($str);
foreach($res as $bar){ 
	@$optunitro.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
}

// $str = "select * from ".$dbname.".pmn_4customer";
// $res=fetchdata($str);
// foreach($res as $bar){ 
// 	@$optcustomer.="<option value='".$bar['kodecustomer']."'>".$bar['kodecustomer']."-".$bar['namacustomer']."</option>";
// }

$str = "select nokontrak,nokontrak_manual from ".$dbname.".pmn_kontrakjual where tanggalberlaku>=CURDATE() order by tanggalkontrak";
$res=fetchdata($str);
foreach($res as $bar){ 
	@$optnokontrak.="<option value='".$bar['nokontrak']."'>".$bar['nokontrak_manual']."</option>";
}

// HEADER UNTUK BUAT BARU SAMA LIST-->
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('kebun_tbsjual').'</span>');
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:90px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:90px;cursor:pointer;' onclick=displaylist()>
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
				
				<td>".$_SESSION['lang']['customer']."</td>
				<td>:</td>		
				<td>
					<select id=kodecustomersch  style=\"width:155px;\">'".$optcustomer."'</select>
				</td>
		
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



//UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER
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
                    <th  align=center>".$_SESSION['lang']['customer']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</th>
					 
                    <th  align=center>".$_SESSION['lang']['netto']."</th>
                    <th  align=center>".$_SESSION['lang']['keterangan']."</th>
                    <th  align=center>".$_SESSION['lang']['dibuat']."</th>
                    <th  align=center>".$_SESSION['lang']['posting']."</th>
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
		
		<td>No. Kontrak</td>
		<td>:</td>		
		<td>
			<select id=nokontrak  style=\"width:155px;\" onchange='getcust()'>".$optnokontrak."</select>
		</td>
		
		
		
		<td hidden>".$_SESSION['lang']['statusSortasi']."</td>
		<td hidden>:</td>		
		<td hidden>
			<select id=sortasi  style=\"width:155px;\">
				<option value=''>".$_SESSION['lang']['pilihdata']."</option>
				<option value='1'>".$_SESSION['lang']['ya']."</option>
				<option value='0' selected>".$_SESSION['lang']['tidak']."</option>
			</select>
		</td>		
		
	
				<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</td>
		<td>:</td>	
		<td>
			<input type=text class=myinputtext placeholder='Tanggal tbs' id=tanggaltbs1 name=tanggaltbs1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
			s/d <input type=text class=myinputtext  placeholder='Tanggal tbs' id=tanggaltbs2 name=tanggaltbs2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit  style=\"width:155px;\">".$optunit."</select>
		</td>
		
		<td>".$_SESSION['lang']['customer']."</td>
		<td>:</td>		
		<td>
			<select id=kodecustomer  style=\"width:155px;\"></select>
		</td>
		

			
	</tr>

	<tr>
		<td>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['invoice']."</td>
		<td>:</td>		
		<td>
			<select id=kodero  style=\"width:155px;\">".$optunitro."</select>
		</td>
	
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dokumen']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal  placeholder='Tanggal dokumen' name=tanggal name=tanggal  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/></td>
	
		
	</tr>

	<tr> 
		<td valign=top rowspan=4>".$_SESSION['lang']['keterangan']."</td> 
		<td valign=top rowspan=4>:</td>
		<td   valign=top rowspan=4><textarea rows='2' id=keteranganht type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\"></textarea></td>
		
	</tr>
	
	<tr>
		<td align=center colspan=9><button  id=saveht class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button>
	</tr>
	</table>
</fieldset>";//<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button></td>



CLOSE_BOX();
echo"</div>";

echo "<div id=detail style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
echo "<div id=listdatadt class='table-scroll' style='height:1080px;'></div>";
CLOSE_BOX();
echo"</div>";

/*
echo "<div id=detail style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
	echo "<fieldset style='width:1750px;height:200px;'>";
            echo "<legend><b>".$_SESSION['lang']['list']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
            <thead>
				<tr class=rowheader>
					 <td  align=center>".$_SESSION['lang']['nourut']."</td>
					 <td  align=center>".$_SESSION['lang']['noTiket']."</td>
					 <td  align=center>".$_SESSION['lang']['kodevhc']."</td>
					 <td  align=center>".$_SESSION['lang']['supir']."</td>
					 <td  align=center>".$_SESSION['lang']['tanggal']."<br>PKS</td>
					 <td  align=center>".$_SESSION['lang']['tanggal']."<br>SPB</td>
					 
					 <td  align=center>".$_SESSION['lang']['berat']." I<</td>
					 <td  align=center>".$_SESSION['lang']['berat']." II</td>
					 <td  align=center>".$_SESSION['lang']['berat']." TBS</td>
					 <td  align=center>".$_SESSION['lang']['potongan']."</td>
					 <td  align=center>".$_SESSION['lang']['netto']."</td> 
					 
					 <td  align=center>".$_SESSION['lang']['jjg']."</td> 
					 <td  align=center>".$_SESSION['lang']['bjr']."</td> 
					  <td  align=center>".$_SESSION['lang']['nospb']."</td>
					 <td  align=center>".$_SESSION['lang']['blok']."</td>   
					 <td  align=center>".$_SESSION['lang']['nama']."</td>   
					 <td  align=center>".$_SESSION['lang']['tahuntanam']."</td>   
					 <td  align=center>".$_SESSION['lang']['harga']."</td>   
					 <td  align=center>".$_SESSION['lang']['total']."</td>   
					 <td  align=center>*</td> 		
				</tr>
               
            </thead>
             <tbody id=listdatadt> 
             </tbody>
             </table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";
*/




echo close_body();		////<input type=hidden id=method value='insert'>	
?>