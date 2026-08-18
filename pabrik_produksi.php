<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/pabrik_produksi.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<?
include('master_mainMenu.php');
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_produksi').'</span><br>');
//get org
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
$optorg='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data('kebun')>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:130px;' class='myinputtext' id='tglcr1' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
				</td>
				
				<td>" . $_SESSION['lang']['tanggalsampai'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:125px;' class='myinputtext' id='tglcr2' onmousemove='setCalendar(this.id)' onkeypress='return false'; readonly/>
				</td>
			</tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

echo "<div id=inputform style=display:none>";
OPEN_BOX();
echo "<fieldset>
        <legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td>
					<table>
					   <tr>
						 <td>
							".$_SESSION['lang']['unit']."
						 </td>
						 <td>:</td>
						 <td>
							<select id=kodeorg style=\"width:150px;\">".$optorg."</select>
						 </td>
					   
						 <td>".$_SESSION['lang']['tanggal']."</td>
						 <td>:</td>
						 <td><input type=text class=myinputtext onchange=\"getData();\" readonly id=tanggal style=\"width:145px;\" onmousemove=\"setCalendar(this.id);\" maxlength=10 onkeypress=\"return false;\">
						 </td>	
						 <td>		 
					 </tr>
					   <tr>
						 <td>
							".$_SESSION['lang']['saldoawal']." ".$_SESSION['lang']['tbs']." (Kg)
						 </td>
						 <td>:</td>
						 <td>
							<input type=text id=sisatbskemarin value=0 class=myinputtextnumber style=\"width:145px;\" onkeypress=\"return angka_doang(event);\" disabled>
							<input type=text id=sisatbskemarinnetto disabled hidden value=0 class=myinputtextnumber onblur=\"hitungSisanetto();\" size=10 onkeypress=\"return angka_doang(event);\">
						 </td>
					   
						 <td> ".$_SESSION['lang']['tbsmasuk']." (Kg)</td>
						 <td>:</td>
						 <td>
							<input type=text id=tbsmasuk value=0  class=myinputtextnumber  style=\"width:145px;\" onkeypress=\"return angka_doang(event);\" disabled>
							<input type=text id=tbsmasuknetto hidden disabled value=0  class=myinputtextnumber onblur=\"hitungSisanetto();\"  size=10 onkeypress=\"return angka_doang(event);\">
						 </td>	 		 
					 </tr>		
					 <tr>
						 <td>".$_SESSION['lang']['tbsdiolah']." (Kg)</td>
						 <td>:</td>
						 <td>
							<input type=text id=tbsdiolah value=0  class=myinputtextnumber onkeyup=\"hitungSisa();\" onclick=\"this.select();\"   style=\"width:145px;\" onkeyup=\"z.numberFormat('tbsdiolah');\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('tbsdiolah',0)\">
							<input type=text id=tbsdiolahnetto value=0 hidden  class=myinputtextnumber  size=10 onkeypress=\"return angka_doang(event);\">
						 </td>		 
					 
						 <td>".$_SESSION['lang']['saldoakhir']." ".$_SESSION['lang']['tbs']." (Kg)</td>
						 <td>:</td>
						 <td>
							<input type=text id=sisa disabled value=0 class=myinputtextnumber  maxlength=10  style=\"width:145px;\">
							<input type=text id=sisanetto disabled  hidden value=0 class=myinputtextnumber  maxlength=10 size=10>
						 </td>		 
					 </tr>
					 
					 <tr><td>Produksi ".$_SESSION['lang']['cpo']." (Kg)</td>
					 <td>:</td>
						 <td>
							<input type=text id=oercpo  value=0 onblur=\"periksaOERCPO(this);\" onkeyup=\"z.numberFormat('oercpo');\" class=myinputtextnumber maxlength=7  style=\"width:145px;\" onkeypress=\"return angka_doang(event);\" disabled>
						 </td>
					  
						<td>Produksi ".$_SESSION['lang']['kernel']." (Kg)</td>
						<td>:</td>
						<td>
							<input type=text id=oerpk   value=0 onblur=periksaOERPK(this) onkeyup=\"z.numberFormat('oerpk');\" class=myinputtextnumber maxlength=7  style=\"width:145px;\" onkeypress=\"return angka_doang(event);\" disabled></td>
					   <td hidden>".$_SESSION['lang']['cpo']." On System (Kg)</td>
						<td hidden>:</td>
						 <td hidden>
							<input type=text id=cpoonsistem  value=0 onkeyup=\"z.numberFormat('oercpo');\" onclick=\"this.select();\" class=myinputtextnumber maxlength=7 style=\"width:145px;\" onkeypress=\"return angka_doang(event);\">
						 </td>
					  </tr>		  
					  <tr>
							<td ></td>
							<td ></td>
							<td ></td>
					  
							<td>Loading Gudang ".$_SESSION['lang']['kernel']." (Kg)</td>
							<td>:</td>
							<td>
								<input type=text id=loadinggudang   value=0 onkeyup=\"z.numberFormat('loadinggudang');getKernel()\" class=myinputtextnumber maxlength=7  style=\"width:145px;\" onkeypress=\"return angka_doang(event);\"></td>
					   		<td hidden>".$_SESSION['lang']['cpo']." On System (Kg)</td>
							<td hidden></td>
						 	<td hidden></td>
					  </tr>

					   <tr hidden>
							<td>".$_SESSION['lang']['kernel']." (Kg)</td>
							<td>:</td>
						 <td>
							<input type=text id=oerpk   value=0 onblur=periksaOERPK(this) onkeyup=\"z.numberFormat('oerpk');\" class=myinputtextnumber maxlength=7  style=\"width:145px;\" onkeypress=\"return angka_doang(event);\">
						 </td>
					  </tr>
					  <tr>
						<td valign=top>".$_SESSION['lang']['keterangan']."</td>
						<td valign=top>:</td>
						<td colspan=6 valign=top><textarea onkeypress=\"return tanpa_kutip(event);\" id=keterangan style=\"width:400px;\" rows=6></textarea></td>	
						</tr>
					  <tr>		
					  <td></td>
					  
						<td></td>
						<td colspan=5 align=center>
						<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
						<input  id=method hidden value='insert'>
					<button id=batal class=mybutton onclick=bersihkanForm()>Batal</button>
						</td>
					  </tr>
					  
					  
					
					<tr hidden>
						 <td>
							".$_SESSION['lang']['jumlahlori']." ".$_SESSION['lang']['restan']." ".$_SESSION['lang']['hi']."
						 </td>
						 <td>
							<input type=text id=lorirestanhi  value=0 class=myinputtextnumber  maxlength=10 size=10 readonly> 
						 </td>		 
					 </tr>
					
					<tr hidden>
						 <td>
						   Cangkang
						 </td>
						 <td>
							<input type=text id=cangkang  value=0 class=myinputtextnumber  maxlength=10 size=10 readonly>Kg. 
						 </td>		 
					 </tr>
					 ";
						   echo" <tr hidden>
						 <td>% USB Before SSBC
						 </td>
						 <td>
							<input type=text id=usbbefore  value=0 class=myinputtextnumber  maxlength=10 size=10 >%
						 </td>		 
					 </tr>	  
							  <tr hidden>
						 <td>% USB After SSBC
						 </td>
						 <td>
							<input type=text id=usbafter  value=0 class=myinputtextnumber  maxlength=10 size=10 >% 
						 </td>		 
					 </tr>	
							  <tr hidden>
						 <td>% Oil Diluted Crude Oil
						 </td>
						 <td>
							<input type=text id=oildiluted  value=0 class=myinputtextnumber  maxlength=10 size=10 >%
						 </td>		 
					 </tr>	
							  <tr hidden>
						 <td>% Oil in underflow (CST)
						 </td>
						 <td>
							<input type=text id=oilin  value=0 class=myinputtextnumber  maxlength=10 size=10 >%
						 </td>		 
					 </tr>	
							  <tr hidden>
						 <td>% Oil in Heavy Phase - S/D
						 </td>
						 <td>
							<input type=text id=oilinheavy  value=0 class=myinputtextnumber  maxlength=10 size=10 >%
						 </td>		 
					 </tr>	
							  <tr hidden>
						 <td>CaCO3
						 </td>
						 <td>
							<input type=text id=caco  value=0 class=myinputtextnumber  maxlength=10 size=10 >Kg
						 </td>		 
					 </tr>	
				  </table>
			  </td>";
			  
	  echo"<td valign=top>  
  	<table>
		<tr>
		<td> 
		 <fieldset><legend>".$_SESSION['lang']['cpo']."</legend>
		 <table>
		 
		 <tr>
		     <td>
			    FFa
			 </td>
		     <td>
			    <input type=text id=ffacpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>			 
		 </tr>
		 <tr>
		     <td>
			    ".$_SESSION['lang']['kadarair']."
			 </td>
			 <td>
			    <input type=text id=kadaraircpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>
		 </tr>
		<tr>
		     <td>
			    ".$_SESSION['lang']['kotoran']."
			 </td>
		     <td>
			    <input type=text id=dirtcpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>
		 </tr>	
		 <tr hidden>
		     <td>
			    Dobi
			 </td>
		     <td>
			    <input type=text id=usbcpo  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>			 
		 </tr>		   	   
		</table>
		</fieldset>
		
		</td>
		</tr>
                
<tr>
		<td> 
		 <fieldset hidden><legend>".$_SESSION['lang']['cpo']." Loses</legend>
		 <table>
			

                    
		 <tr>
                    <td>Fruit In Empty Bunch</td>
                    <td>
                       <input type=text id=fruitineb  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\"> Kg
                    </td>
		  </tr>
		 <tr>
		     <td>Empty Bunch Stalk 
			 </td>
		     <td>
			    <input type=text id=ebstalk value=0    class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> %
			 </td>
		 </tr>	
		 <tr>
		     <td> Fibre From Press Cake
			 </td>
			 <td>
			    <input type=text id=fibre value=0  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> %   
			 </td>
		 </tr>	
		 <tr>
		     <td>Nut From Press Cake
			 </td>
		     <td>
			    <input type=text id=nut value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> %
			 </td>			 
		 </tr>	
                  <tr>
		     <td>Effluent
			 </td>
		     <td>
			    <input type=text id=effluent value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> % 
			 </td>			 
		 </tr>	
		 
		 <tr>
			 <td>Solid Decanter
				 </td>
				 <td>
					<input type=text id=soliddecanter value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> % 
				 </td>			 
			 </tr>	
        
		<tr><td colspan=3>
		
		
		<fieldset><legend>Hanya informasi (Tidak pengaruh CPO Loses)</legend><table>
			
			
			<tr>
			 <td>Condensate Sterilizer
				 </td>
				 <td>
					<input type=text id=condensatesterilizer value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> % 
				 </td>			 
			 </tr>	
			
			 <tr>
				 <td>Centrifuge
				 </td>
				 <td>
					<input type=text id=hydrocyclone  value=0  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> %
				 </td>			 
			 </tr></table></fieldset>
			</td>
			</tr>
		</table>
		</fieldset>
		
		</td>
		</tr>
		</table>	
    </td>
	<td valign=top>
  	<table>
		<tr>
		<td> 
		 <fieldset><legend>".$_SESSION['lang']['kernel']."</legend>
		 <table>	
		 <tr>
		     <td>
			    FFA
			 </td>
		     <td>
			    <input type=text id=ffapk  value=0 onblur=periksaPK(this)  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>			 
		 </tr>	
		  <tr>
		     <td>
			    ".$_SESSION['lang']['kadarair']."
			 </td>
			 <td>
			    <input type=text id=kadarairpk  value=0 onblur=periksaPK(this)  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>
		 </tr>
		 <tr>
		     <td>
			    ".$_SESSION['lang']['kotoran']."
			 </td>
		     <td>
			    <input type=text id=dirtpk  value=0 onblur=periksaPK(this)  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>
		 </tr>	
                 
		</table>
		</fieldset>
		
		</td>
		</tr>
                <tr>
		<td> 
		 <fieldset hidden><legend>".$_SESSION['lang']['kernel']." Loses</legend>
		 <table>
                 
                  <tr style=display:none>
                    <td>USB</td>
                    <td>
                       <input type=text id=usbpk  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\"> % 
                    </td>
		  </tr>

		 <tr><td>Fruit In Empty Bunch

			 </td>
			 <td>
			    <input type=text id=fruitinebker  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\"> Kg
			 </td>
		  </tr>
		 <tr>
		     <td>Fibre Cyclone
			 </td>
		     <td>
			    <input type=text id=cyclone  value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">  %
			 </td>
		 </tr>	
		 <tr>
		     <td>LTDS
			 </td>
			 <td>
			    <input type=text id=ltds  value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">  %
			 </td>
		 </tr>	
		 <tr>
		     <td>Claybath
			 </td>
		     <td>
			    <input type=text id=claybath  value=0  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> %
			 </td>			 
		 </tr>
		</table>
		</fieldset>
		</td>
		</tr>
		</table>	
	</td>
	</tr>	  
	</table>	
  </fieldset>
 ";
CLOSE_BOX();
echo "</div>";

echo "<div id=loaddataform style=display:block>";
OPEN_BOX();
echo "<div id=container  style=display:block> 
		<script>loaddata()</script>
	</div>";
CLOSE_BOX();
echo "</div>";

close_body();
?>