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
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/pmn_bast.js?v=<?php echo time(); ?>'></script>
<!--deklarasi untuk option-->
<?php

$optkodept=$optkodecust=$optkodebarang=$optnokontrak=$optkapal=$optponton=$optnokontrakref="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


$str="select * from ".$dbname.".organisasi where tipe='PT'";
$res=fetchdata($str);
foreach($res as $bar){
	$optkodept.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".pmn_4customer";
$res=fetchdata($str);
foreach($res as $bar){
	$optkodecust.="<option value='".$bar['kodecustomer']."'>".$bar['kodecustomer']." - ".$bar['namacustomer']."</option>";
}

$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res=fetchdata($str);
foreach($res as $bar){
	$optkodebarang.="<option value='".$bar['kodebarang']."'>".$bar['inisial']." - ".$bar['namabarang']."</option>";
}

$emodul='BASTSALES';
@$arrmodul = getmodulefil($emodul);
foreach($arrmodul as $key=>$val){
	@$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
}

$str="select * from ".$dbname.".pmn_5kapalponton";
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['jenis']=='KPL' || $bar['jenis']=='TRK'){
		$optkapal.="<option value=".$bar['kode'].">".$bar['nama']." - ".$bar['jenis']."</option>";
	}
	if($bar['jenis']=='PNT'){
		$optponton.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
	}
}

#<!--HEADER UNTUK BUAT BARU SAMA LIST-->

// echo"<div id=action_list>";//buka div


echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pmn_bast').'</span>');
echo"<table border=0>
     <tr >
	 <td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
		echo"<table>";
			echo"<tr>";
				echo"<td>No. BAST</td>";
				echo"<td>:</td>";
				echo"<td><input type='text' class='myinputtext' id='notransaksisch' name='notransaksisch' onkeypress='return tanpa_kutip(event)' style='width:150px;' maxlength='45' /></td>";
				
				echo"<td>".$_SESSION['lang']['customer']."</td>
				<td>:</td>		
				<td>
					<select id=kodecustomersch  style=\"width:161px;\">'".$optkodecust."'</select>
				</td>";
				
				
				echo"<td>".$_SESSION['lang']['tanggal']." BL</td>";
				echo"<td>:</td>";
				echo"<td><input type=text class=myinputtext id=tanggalbl1sch onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:65px;'  size=10 maxlength=10 readonly/> s/d <input type=text class=myinputtext id=tanggalbl2sch onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:65px;'  size=10 maxlength=10 readonly/></td>";
			echo"</tr>";
			echo"<tr>";
				echo"<td>".$_SESSION['lang']['NoKontrak']."</td>";
				echo"<td>:</td>";
				echo"<td><input type='text' class='myinputtext' id='nokontraksch' name='nokontraksch' onkeypress='return tanpa_kutip(event)' style='width:150px;'' maxlength='45' /></td>";
				
				echo"<td>".$_SESSION['lang']['komoditi']."</td>
					<td>:</td>		
					<td>
						<select id=kodebarangsch  style=\"width:154px;\">'".$optkodebarang."'</select>
					</td>";
				
				echo"<td>".$_SESSION['lang']['tanggal']." BAST</td>";
				echo"<td>:</td>";
				echo"<td><input type=text class=myinputtext id=tanggal1sch onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:65px;'  size=10 maxlength=10 readonly/> s/d <input type=text class=myinputtext id=tanggal2sch onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:65px;'  size=10 maxlength=10 readonly/></td>";
			echo"</tr>";
			echo"<tr>";
				echo"<td colspan=2></td>";
				echo"<td><button class=mybutton onclick=\"loaddata(0)\">" . $_SESSION['lang']['find'] . "</button></td>";
			echo"</tr>";
			  
		echo"</table>";
echo"</fieldset></td>";

echo"
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
// echo"<div id=listdata style=display:none>";//buka list data
echo"<div id=listdata style=display:block>";//buka list data
// OPEN_BOX();
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span><br>');
    echo "
    <div class=table-scroll style='height:65vh'>";//<th align=center>".$_SESSION['lang']['nourut']."</th>
           echo "<table cellpading=1 cellspacing=1 border=0 class=sortable >
            <thead>
                <tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center>".$_SESSION['lang']['notransaksi']."</th>
					 <th align=center>".$_SESSION['lang']['NoKontrak']."</th>
					<th align=center>".$_SESSION['lang']['tanggal']."<br>BAST</th>
					<th align=center>".$_SESSION['lang']['tanggal']."<br>BL</th>
                    <th align=center>".$_SESSION['lang']['kodept']."</th>
					<th align=center>".$_SESSION['lang']['customer']."</th>
                    <th align=center>".$_SESSION['lang']['komoditi']."</th>
                    <th align=center>".$_SESSION['lang']['kuantitas']."</th>
                    <th align=center>".$_SESSION['lang']['dibuat']."</th>
                    <th align=center>".$_SESSION['lang']['histori']."</th>
                    <th align=center colspan=6>".$_SESSION['lang']['action']."</th>
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
			<tfoot id=footData></tfoot>
             </table>
	</div>";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');

//jumlah dan kurs no 1 dan 2, agar mudah remove comma di js
$arrht="###notransaksi###nokontrak###kodept###kodecustomer###tanggal###kota";

echo "<fieldset>";
// echo "<fieldset style=float:left>";
echo "<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td>
			<input type=text id=notransaksi size=50 disabled class=myinputtext style=\"width:150px;\">
		</td>
		
		<td>".$_SESSION['lang']['kodecustomer']."</td>
		<td>:</td>		
		<td>
			<select id=kodecustomer  style=\"width:154px;\">'".$optkodecust."'</select>
		</td>
		
		<td>".$_SESSION['lang']['NoKontrak']."</td>
		<td>:</td>		
		<td>
			<input type=text id=nokontrak size=50 readonly onclick=getnokontrak() class=myinputtext style=\"width:150px;\">
		</td>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal name=tanggal onchange='getnobast()' style=\"width:150px;\" readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10>	
		</td>
	</tr>	
	<tr>	
		<td>".$_SESSION['lang']['kodept']."</td>
		<td>:</td>		
		<td>
			<select id=kodept  style=\"width:154px;\">'".$optkodept."'</select>
		</td>	
		
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>		
		<td>
			<select id=kodebarang  style=\"width:154px;\">'".$optkodebarang."'</select>
		</td>	
		
		<td>".$_SESSION['lang']['kota']."</td>
		<td>:</td>		
		<td>
			<input type=text id=kota size=50 class=myinputtext style=\"width:150px;\">
		</td>
		
	</tr>	
	
		

	<tr>
		<td align=center colspan=2></td>
		<td>
			<button class=mybutton onclick=saveht('".$arrht."')>".$_SESSION['lang']['save']."</button>&nbsp;
			<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>


	</table>
</fieldset>";

CLOSE_BOX();
echo"</div>";//<input type=hidden id=method value='insertht'>	


#- <!--UNTUK BUAT FORM INPUT HEADER-->
$arrdt="###notransaksi###kodept###kodecustomer###kodecustomer###kodebarang###nokontrak###tanggal###kota";
$arrdt.="###tanggalbl###tanggalsmp###ffa###kodetangki###mdani###dirt###jumlah###moisture###dobi###impurities###broken###methoddt###namakapal###namaponton###methoddt###nourut###lain";
$arrdt.="###rpkgclaimffa###rpkgclaimmdani###rpkgclaimdirt###rpkgclaimmoisture###rpkgclaimimpurities###rpkgclaimbroken###rpkgclaimdobi###rpkgclaimlain";
$arrdt.="###rpclaimffa###rpclaimmdani###rpclaimdirt###rpclaimmoisture###rpclaimimpurities###rpclaimbroken###rpclaimdobi###rpclaimlain###jlhrit###kgpembeli###catatan";
$border='0';
echo "<div id=detail style=display:none>";
// echo "<div id=detail style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span><br>');
$frm[0]='';
$frm[1]='';
$frm[2]='';

$frm[0].="<fieldset>";
$frm[0].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";

	$frm[0].="<table cellspacing=1 border=".$border.">
			<tr>
				<td>".$_SESSION['lang']['tanggal']." Dari </td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tanggalbl onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:150px;'  size=10 maxlength=10 readonly/></td>
			
				<td>FFA (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=ffa name=ffa onkeyup=getnilaiclaim('".$arrdt."'); value=0  onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaimffa onkeyup=getnilaiclaim('".$arrdt."'); name=rpkgclaimffa value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpclaimffa name=rpclaimffa value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>

				
			</tr>
			<tr>
				<td hidden>".$_SESSION['lang']['kodetangki']."</td>
				<td hidden>:</td>
				<td hidden><select id='kodetangki'  name='kodetangki' style='width:150px;'>".$optkodetangki."</select></td>  
                
				<td>" . $_SESSION['lang']['tanggal'] . " Sampai</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tanggalsmp onchange=getkodetangki() onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:150px;'  size=10 maxlength=10 readonly/></td>


				<td>Moisture (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=moisture name=moisture  onkeyup=getnilaiclaim('".$arrdt."'); value=0  onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaimmoisture name=rpkgclaimmoisture  onkeyup=getnilaiclaim('".$arrdt."'); value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpclaimmoisture name=rpclaimmoisture value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
					
			</tr>
			
			<tr>
				<td>".$_SESSION['lang']['jumlah']." kg Pabrik</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=jumlah name=Jumlah value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Impurities (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=impurities name=impurities  onkeyup=getnilaiclaim('".$arrdt."'); value=0  onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
			
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaimimpurities name=rpkgclaimimpurities  onkeyup=getnilaiclaim('".$arrdt."'); value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpclaimimpurities name=rpclaimimpurities value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
			</tr>
			
			<tr>

				<td>".$_SESSION['lang']['jumlah']." kg Pembeli</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=kgpembeli name=Jumlah value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td hidden>".$_SESSION['lang']['namakapal']."</td>
				<td hidden>:</td>		
				<td hidden><select id=namakapal style=\"width:150px;\">'".$optkapal."'</select></td>
				
				<td>M & I (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=mdani name=mdani  onkeyup=getnilaiclaim('".$arrdt."'); onkeyup=getnilaiclaim('".$arrdt."'); value=0  onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td> 
					
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaimmdani name=rpkgclaimmdani  onkeyup=getnilaiclaim('".$arrdt."'); onkeyup=getnilaiclaim('".$arrdt."'); value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpclaimmdani name=rpclaimmdani value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
			</tr>
			<tr>	
				
				<td hidden>".$_SESSION['lang']['namaponton']."</td>
				<td hidden>:</td>		
				<td hidden>
					<select id=namaponton style=\"width:150px;\">'".$optponton."'</select>
				</td>

				<td>".$_SESSION['lang']['jumlah']." Unit Pengangkut</td>
				<td>:</td>
				<td><input type=text class=myinputtextnumber id=jlhrit name=jlhrit value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  


				
				<td>Dirt (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=dirt name=dirt  onkeyup=getnilaiclaim('".$arrdt."'); value=0  onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
				
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaimdirt  onkeyup=getnilaiclaim('".$arrdt."'); name=rpkgclaimdirt value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpclaimdirt name=rpclaimdirt value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
			</tr>
			
			<tr>
				<td hidden>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['data']."</td>
				<td hidden>:</td>
				<td hidden><input type=text class=myinputtext id=tanggaldata  onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:150px;'  size=10 maxlength=10 readonly/></td>

 				<td></td>
				<td></td>
				<td></td>

				<td>Dobi (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=dobi name=dobi  onkeyup=getnilaiclaim('".$arrdt."'); value=0  onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
				
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaimdobi  onkeyup=getnilaiclaim('".$arrdt."'); name=rpkgclaimdobi value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpclaimdobi name=rpclaimdobi value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
			</tr>
			<tr> 
				<td></td>
				<td></td>
				<td></td>
				
				<td>Broken (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=broken name=broken  onkeyup=getnilaiclaim('".$arrdt."'); value=0  onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
				
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaimbroken name=rpkgclaimbroken onkeyup=getnilaiclaim('".$arrdt."'); value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpclaimbroken name=rpclaimbroken value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
			</tr>
			<tr>
				<td colspan=3 style=color:red></td>
				<td>".$_SESSION['lang']['lain']." (".$_SESSION['lang']['unit'].")</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=lain name=lain  onkeyup=getnilaiclaim('".$arrdt."'); value=0  onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
				
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaimlain name=rpkgclaimlain onkeyup=getnilaiclaim('".$arrdt."'); value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>  
				
				<td>Rp Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpclaimlain name=rpclaimlain value=0 onkeypress=return angka_doang(event) style=width:150px; maxlength=45 /></td>
			</tr>
			
			<tr hidden>
				<td colspan=6>
				methoddt<input  type=text id=methoddt value='insert' class=myinputtext style=\"width:150px;\">
				nourut<input type=text id=nourut readonly class=myinputtext style=\"width:150px;\">
			</tr>
			<tr>
				<td >".$_SESSION['lang']['catatan']."</td>
				<td >:</td>
				<td >
					<textarea type='text' class='myinputtext' id='catatan' name='catatan' style='width:250px;height:100px;' maxlength='255'></textarea>
				</td>

			</tr>
			
			<tr>
				<td align=center colspan=2></td>
				<td  colspan=9>
					<button class=mybutton onclick=savedt('".$arrdt."')>".$_SESSION['lang']['save']."</button>&nbsp;
					<button id=batal class=mybutton onclick=canceldt('".$arrdt."')>".$_SESSION['lang']['cancel']."</button>
					<button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button>
				</td>
			</tr>
		</table></fieldset>";//<input type=hidden id=methodht value='insertdt'>	
	
	// echo"<div id='listdatadetail'></div>";
	
	$frm[0].="
    <fieldset>
            <legend><b>".$_SESSION['lang']['list']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['tanggal']." BL</td> 
                    <td  align=center>".$_SESSION['lang']['tanggal']."<br>".$_SESSION['lang']['data']." </td> 
					<td  align=center>Jumlah Rit</td>
                    <td  align=center>".$_SESSION['lang']['jumlah']." kg Pabrik </td> 
                    <td  align=center>".$_SESSION['lang']['jumlah']." kg Pembeli</td> 
                   
                    <td  align=center colspan=2 rowspan=2>".$_SESSION['lang']['action']." </td> 
                </tr>  
  
				
            </thead>
             <tbody id=listdatadt> 
             </tbody>
             </table>
	</fieldset>";

$frm[1].="<fieldset>
		<legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['upload'] . "</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td>".$_SESSION['lang']['kriteria']."</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick='submitfile()'>Submit</button>
					<button class=mybutton onclick='loadfiles()'>Selesai</button>
				</td>
				
			</tr>
		</table></fieldset>";
$frm[1].="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
				<th align='center'>".$_SESSION['lang']['nourut']."</th>
				<th align='center'>File Type</th>
				<th align='center'>Kriteria</th>
				<th align='center'>Filename</th>
				<th align='center'>Action</th>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		</fieldset>";	
	
	$frm[2].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>";
	$arrdtnotransaksireferensi="###notransaksi###notransaksireferensi";
	$frm[2].="<table cellspacing=1 border=".$border.">
			<tr>
				<td>".$_SESSION['lang']['noreferensi']."</td>
				<td>:</td>
				<td><select id='notransaksireferensi'  name='notransaksireferensi' style='width:150px;'>".$optnokontrakref."</select></td>  
			</tr>
			<tr>
				<td align=center colspan=2></td>
				<td  colspan=2>
					<button class=mybutton onclick=savedtnotransaksireferensi('".$arrdtnotransaksireferensi."')>".$_SESSION['lang']['save']."</button>&nbsp;
					<button id=batal class=mybutton onclick=canceldtnotransaksireferensi()>".$_SESSION['lang']['cancel']."</button>
					<button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button>
				</td>
			</tr></table></fieldset>";		
	$frm[2].="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
				<td align='center'>".$_SESSION['lang']['notransaksi']."</td>
				<td align='center'>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody id='listreferensi'>
			</tbody>
		</table>
		</fieldset>";	
		
$hfrm[0]=strtoupper($_SESSION['lang']['transaksi']);
$hfrm[1]=strtoupper($_SESSION['lang']['file']);
$hfrm[2]=strtoupper($_SESSION['lang']['noreferensi']);
drawTab('FRM',$hfrm,$frm,100,'auto');   
CLOSE_BOX();
echo"</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>