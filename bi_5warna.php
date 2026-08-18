<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/bi_5warna.js'></script>

<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['warna']."(MAP REQUIREMENT)").'</span>');

$optTipe .= "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".bi_5tipepeta order by keterangan desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optTipe .= "<option value='".$bar['id_tipepeta']."'>".$bar['id_tipepeta']." - ".$bar['keterangan']."</option>";
}

echo"<br><fieldset style=float:left>
	<legend><b>".$_SESSION['lang']['setup']." ".$_SESSION['lang']['warna']."</b></legend>
	<table cellpading=1 cellspacing=1 border=0>
	
	<tr>
	   <td>".$_SESSION['lang']['tipe']."</td>
	   <td>:</td>
	   <td>
			<select id='tipe'>".$optTipe."</select>
		<!--<input type=text class=myinputtext id=tipe name=tipe onkeypress=\"return tanpa_kutip(event);\" style=\"width:120px;\" />-->
		 </td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['kode']." ".$_SESSION['lang']['warna']." Fill</td>
		<td>:</td>
		<td>
			<input disabled type=text class=myinputtext id=kodefill name=kode onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\" />
			<img  class=resicon src=images/color_fill.png style=position:relative;top:5px title='".$_SESSION['lang']['find']."' onclick=cariwarna('fill',event)>     	  
		</td>
	</tr>
	<tr>
		<td>Sample Warna Fill</td>
		<td>:</td>
		<td id=displaycolorfill style=\"width:120px;\" style=position:relative;top:5px title='".$_SESSION['lang']['find']."' onclick=cariwarna(event)></td>
	</tr>
	
	
	<tr>
		<td>".$_SESSION['lang']['kode']." ".$_SESSION['lang']['warna']." Line</td>
		<td>:</td>
		<td>
			<input disabled type=text class=myinputtext id=kodeline name=kodeline onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\" />
			<img  class=resicon src=images/color_fill.png style=position:relative;top:5px title='".$_SESSION['lang']['find']."' onclick=cariwarna('line',event)>     	  
		</td>
	</tr>
	<tr>
		<td>Sample Warna Line</td>
		<td>:</td>
		<td id=displaycolorline style=\"width:120px;\" style=position:relative;top:5px title='".$_SESSION['lang']['find']."' onclick=cariwarnaline(event)></td>
	</tr>
	
	<tr>
	   <td>Width Line</td>
	   <td>:</td>
	   <td>
		<input type=text class=myinputtext id=width name=width onkeypress=\"return tanpa_kutip(event);\" style=\"width:120px;\" />
		 </td>
	</tr>
	
    <tr><td></td><td></td><td>    
         <button class=mybutton onclick=save()>".$_SESSION['lang']['save']."</button>
         <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		 <input type=hidden id=method value='insert'>
	</td></tr>
	</table>	 
	</fieldset>";
CLOSE_BOX();



OPEN_BOX();

echo "
	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
               
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:40%>
                <thead>
                    <tr class=rowheader>
						<td  align=center>".$_SESSION['lang']['nourut']."</td>
						<td  align=center>".$_SESSION['lang']['tipe']."</td>
						<td  align=center>Fill</td>
                        <td  align=center>Line</td>
                        <td  align=center>Display Fill</td>    
						<td  align=center>Display Line</td>    
						<td  align=center>Width</td>    
						<td  align=center>".$_SESSION['lang']['action']."</td>
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
echo close_body();
?>