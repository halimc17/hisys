<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript1.2 src='js/budget_5hargabarang.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('budget_5hargabarang').'</span>');
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
     </tr>
     </table>"; 
CLOSE_BOX();
//ambil PT yang ada di masterbarangdt
$optpt='';
$str="select distinct kodeorg from ".$dbname.".log_5masterbarangdt  order by kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optpt.="<option value='".$bar->kodeorg."'>".$bar->kodeorg."</option>";	
}
//ambil kelompok barang 
$optkl='';
$str="select kode, kelompok from ".$dbname.".log_5klbarang  order by kode";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optkl.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->kelompok."</option>";	
}
//ambil regional 
$optreg='';
$str="select regional, nama from ".$dbname.".bgt_regional  order by regional";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optreg.="<option value='".$bar->regional."'>".$bar->regional." - ".$bar->nama."</option>";	
}
        
//form input
echo"<div id='frminput' style='display:none;'>";
OPEN_BOX('','');
    echo"<fieldset style=float:left><legend id=legendinput name=legendinput>New</legend>";
    echo"<table><tr>";
    echo "<tr><td>".$_SESSION['lang']['budgetyear']."</td><td><input onkeyup=\"resetcontainer();\" type=text id=tahunbudget size=4 maxlength=4 class=myinputtext onkeypress=\"return angka_doang(event);\"></td></tr>";
    echo "<tr><td>".$_SESSION['lang']['regional']."</td><td><select onchange=\"resetcontainer();\" id=regional style='width:150px'><option value=''>".$optreg."</select></td></tr>";
    echo "<tr><td>".$_SESSION['lang']['sumberHarga']."</td><td><select onchange=\"resetcontainer();\" id=sumberharga style='width:150px'><option value=''></option>".$optreg."</select></td></tr>";
    echo "<tr><td>".$_SESSION['lang']['kelompokbarang']."</td><td><select onchange=\"resetcontainer();\" id=kelompokbarang style='width:150px'><option value=''>".$optkl."</select></td></tr>";
    echo"<tr><td></td><td><button id= buttonproses class=mybutton onclick=tampolHarga()>".$_SESSION['lang']['proses']."</button>
        <input type=\"hidden\" id=\"hiddenprocess\" name=\"hiddenprocess\" value=\"\" />
        </td></tr></table>";
    echo"</fieldset>";

CLOSE_BOX(); 
OPEN_BOX();
echo"<div style=clear:both></div>
	<span id=printPanel></span>    
     <div id=container style='width:100%;height:359px;overflow:auto;'>
     </div>";
CLOSE_BOX(); 
echo"</div>";

//form list
echo"<div id='frmlist' style='display:none;'>";
OPEN_BOX();

echo"<fieldset style='float:left;' id=contlisthead><legend>".$_SESSION['lang']['list']."</legend>";
echo"<div style='min-height:100px;overflow:auto;'><table class=sortable cellspacing=1 border=0>	     
     <thead>
        <tr>
            <td align=center>".$_SESSION['lang']['nomor']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center>".$_SESSION['lang']['regional']."</td>
            <td align=center colspan=6>".$_SESSION['lang']['action']."</td>
	</tr>
     </thead>
     <tbody id=container3>
	 <script>displayList()</script>";
echo"</tbody>
     <tfoot>
     </tfoot>		 
     </table></div>";
echo"</fieldset>";

echo"<fieldset style='float:left;display:none' id=formaddbarang><legend>".$_SESSION['lang']['input']."</legend>";
    echo"<table><tr>";
    echo "<tr><td>".$_SESSION['lang']['budgetyear']."</td>
			  <td><input type=text id=tahunbudget1 size=4 maxlength=4 class=myinputtext onkeypress=\"return angka_doang(event);\"></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['regional']."</td>
			<td><select id=regional1 style='width:155px'><option value=''>".$optreg."</select></td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodebarang']."</td>
			<td><input type=text class=myinputtext readonly id=kodebarang1 name=kodebarang1 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:150px;/>
				<input type=\"image\" id=search1 class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;' class=dellicon title=".$_SESSION['lang']['find']." onclick=\"searchBrg(1,'".$_SESSION['lang']['findBrg']."','<fieldset><legend>".$_SESSION['lang']['findnoBrg']."</legend>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg(1)>Find</button></fieldset><div id=containerq></div><input type=hidden id=nomor name=nomor><input type=hidden id=regbrg value=0 /><input type=hidden id=thnbgtbrg  value=0 />',event)\";>
				<label id=namabarang1></label><label id=satuan1></label>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['hargasatuan']."</td>
			<td><input type=text id=hargasatuan1 size=20 maxlength=10 class=myinputtextnumber onkeypress=\"return angka_doang(event);\"></td>
		</tr>
		<tr>
			<td></td>
			<td><button disabled=true id=buttonedit class=mybutton onclick=editHarga()>".$_SESSION['lang']['save']."</button>
			<input type=\"hidden\" id=\"hiddenedit\" name=\"hiddenedit\" value=\"\" />
			</td>
		</tr>
		</table>";
echo"</fieldset><br>";


echo"<span id=printPanel2 style='display:none;'></span>
	 <div style=clear:both></div>
     <div style='width:100%;height:500px;overflow:auto;display:none;' id=contpreview>
	 <fieldset style=float:left><legend>Find</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['barang']."</td><td>:</td>
				<td><input type=text id=namabarangcari style=width:200px class=myinputtext></td>
				<td><button class=mybutton onclick=listHarga()>".$_SESSION['lang']['find']."</button></td>
				<td><button class=mybutton onclick=kembalikelist()>".$_SESSION['lang']['back']."</button></td>
				<td><input hidden id=tahuntemp><input hidden id=regtemp></td>
				
				
			</tr>
		</table>
	 
	 </fieldset>
	 <div style=clear:both></div>
     <fieldset><legend>List</legend>
	 <table class=sortable cellspacing=1 border=0 width=100%>
     <thead>
        <tr>
            <td align=center>".$_SESSION['lang']['nomor']."</td>
            <td align=center>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center>".$_SESSION['lang']['regional']."</td>
            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['sumberHarga']."</td>
            <td align=center>".$_SESSION['lang']['hargatahunlalu']."</td>
            <td align=center>".$_SESSION['lang']['varian']."</td>
            <td align=center>".$_SESSION['lang']['hargabudget']."</td>
            <td align=center>".$_SESSION['lang']['action']."</td>
	</tr>  
     </thead>
     <tbody id=container2>
     </tbody>
     <tfoot>
     </tfoot>		 
     </table></fieldset>
     </div>";
CLOSE_BOX();
echo"</div>";
close_body('');
?>