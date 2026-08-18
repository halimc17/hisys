<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript src='js/pabrik_stokharianbulking.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<?php


#kodePt
$optpt='';
 $optpt.="<option value=''>Pilih Data</option>";
$sakun="select * from ".$dbname.".organisasi where tipe='PT'
        order by kodeorganisasi asc";
$qakun=$owlPDO->query($sakun) or die(print " Gagal: ".PDOException::getMessage());
$qakun->setFetchMode(PDO::FETCH_ASSOC);
while($rakun=$qakun->fetch())
    {
    $optpt.="<option value='".$rakun['kodeorganisasi']."'>".$rakun['namaorganisasi']."</option>";
}

#unit
$optunit='';
$sakun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='KSBW' order by kodeorganisasi asc";
$qakun=$owlPDO->query($sakun) or die(print " Gagal: ".PDOException::getMessage());
$qakun->setFetchMode(PDO::FETCH_ASSOC);
while($rakun=$qakun->fetch())
    {
    $optunit.="<option value='".$rakun['kodeorganisasi']."'>".$rakun['namaorganisasi']."</option>";
}

#tngki
$opttangki='';
 $opttangki.="<option value=''>Pilih Data</option>";
$sakun="select distinct(kodetangki) as kodetangki,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='KSBW' order by kodetangki asc";
$qakun=$owlPDO->query($sakun) or die(print " Gagal: ".PDOException::getMessage());
$qakun->setFetchMode(PDO::FETCH_ASSOC);
while($rakun=$qakun->fetch())
    {
    $opttangki.="<option value='".$rakun['kodetangki']."'>".$rakun['keterangan']."</option>";
}



OPEN_BOX('','<span class=judul>'.getMenu('pabrik_stokharianbulking').'</span></br></br>');

echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=loaddata(0)>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>


	 <td>
     <fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['notransaksi']." : <input type=text id=notransaksisch size=20  class=myinputtext style=\"width:100px;\"> &nbsp";			
            echo $_SESSION['lang']['nodo']." : <input style=\"width:100px;\" type=text onkeypress='enterkey(event,cariData)' id=txtsearch size=25 maxlength=30 class=myinputtext> &nbsp";
			echo $_SESSION['lang']['tanggal']." : <input type=text id=tanggalsch size=20 class=myinputtext onmousemove=setCalendar(this.id) style=\"width:75px;\" readonly> &nbsp";
			echo $_SESSION['lang']['pt']." : <select id=kodeptsch style=\"width:150px;\">'".$optpt."'</select> &nbsp";
			echo $_SESSION['lang']['tangki']." : <select id=kodetangkisch style=\"width:100px;\">'".$opttangki."'</select> &nbsp";
			echo"<button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button>&nbsp";
			echo"<button class=mybutton onclick=cancelsch() >".$_SESSION['lang']['cancel']."</button>";
echo"</fieldset>
    </td>

     </tr>
	 </table> "; 

CLOSE_BOX();


OPEN_BOX();
echo"<div id=formInput style=display:none;>";
echo "<fieldset style=float:left;>";
    echo "<legend>".$_SESSION['lang']['form']."</legend>";
       echo "<table border=0 cellpadding=1 cellspacing=1 style=width:100%;>
				<tr>
					
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>		
					<td>
						<input type=text id=notransaksi size=20  disabled class=myinputtext style=\"width:150px;\">
					</td>
				</tr>	
				<tr>	
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:150px;\" readonly/>
					</td>
				</tr>	
				<tr>	
					<td>".$_SESSION['lang']['kodept']."</td>
					<td>:</td>		
					<td>
						<select id=kodept style=\"width:150px;\" >'".$optpt."'</select>
					</td>
				</tr>	
				<tr>	
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>		
					<td>
						<select id=kodeunit style=\"width:150px;\">".$optunit."</select>
					</td>
					
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tangki']."</td>
					<td>:</td>		
					<td>
						<select id=kodetangki style=\"width:150px;\">".$opttangki."</select>
					</td>
				</tr>
				
				<tr>
					<td>".$_SESSION['lang']['kuantitas']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=kuantitas  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					Kg&nbsp;&nbsp;</td>
				</tr>
				
				<tr>
					<td>".$_SESSION['lang']['cpoffa']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=ffa  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					%&nbsp;&nbsp;</td>
				</tr>
				
				<tr>
					<td>".$_SESSION['lang']['moisture']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=moisture  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					%&nbsp;&nbsp;</td>
				</tr>
				
				
				<tr>
					<td>Dirt</td>
					<td>:</td>		
					<td>
						<input type=text  id=dirt  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					%&nbsp;&nbsp;</td>
				</tr>
				

				
				
					<td valign=top>".$_SESSION['lang']['keterangan']."</td> 
					<td valign=top>:</td>
					<td ><textarea rows='2'  id=keterangan type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:140px;\"></textarea>
					</td>
					
					
				
				</tr>
				
			<tr>
					<td valign=top>&nbsp;</td> 
					
				</tr>
				
				
					
                <tr><td colspan=9 align=center>
						<button class=mybutton onclick=save()>Simpan</button>
						<input type=hidden id=proses name=proses value=insert>
						<button class=mybutton onclick=cancel()>Hapus</button>
						
					</td>
                </tr>
				

        </table></fieldset></div>
		<input type=hidden id=method value='insert'>";
CLOSE_BOX();

OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
			echo"<div id=listData>";
			echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
			echo "<table class=sortable cellpadding=1 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['pt']."</td>
				<td align=center>".$_SESSION['lang']['unit']."</td>
				<td align=center>".$_SESSION['lang']['tangki']."</td>
				<td align=center>".$_SESSION['lang']['kuantitas']."</td>
				<td align=center>FFA</td>
				<td align=center>".$_SESSION['lang']['moisture']."</td>
				<td align=center>Dirt</td>
				<td align=center>".$_SESSION['lang']['ket']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center  style='width:60px'>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		echo "<tbody id=container>";
		echo "<script>loaddata(0)</script>";
		echo "</tbody>";
		echo "<tfoot id=footdata>";
		echo "</tfoot></table></fieldset></div>";


CLOSE_BOX();
echo close_body();					
?>