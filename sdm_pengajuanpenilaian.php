<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zLib.php');
?>
<script language=javascript src='js/sdm_pengajuanpenilaian.js'></script>
<?php

//get optunit
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
if (substr($_SESSION['empl']['lokasitugas'], 2, 2) == 'HO') {
	$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
}else{
	$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}
$optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$wh=" and tanggalkeluar = '0000-00-00'";
if (substr($_SESSION['empl']['lokasitugas'], 2, 2) == 'HO') {
	$str = " select karyawanid,namakaryawan,lokasitugas,statuskaryawan from " . $dbname . ".datakaryawan
	where 1=1 ".$wh." order by namakaryawan";// Rubah ke Tipe karyawan PB keatas //  
}else{
	$str = " select karyawanid,namakaryawan,lokasitugas,statuskaryawan from " . $dbname . ".datakaryawan
	where lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "' ".$wh." order by namakaryawan";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$optkar.="<option value='" . $bar->karyawanid . "'>" . $bar->namakaryawan . " | " .$bar->statuskaryawan. " | " . $bar->lokasitugas . "</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_pengajuanpenilaian').'</span>');
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
			 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
			 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
			 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
	    echo "<table border=0><tr><td>".$_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class='myinputtext' id='tglevaluasicr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' readonly/></td>";
	    echo"<td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";
	    echo"</table>";
	    echo"</fieldset></td>
	 </tr>
	 </table>";

CLOSE_BOX();
?>
<!--Form Add Data-->
<div id="header" style='display:none'>
    <?php OPEN_BOX() ?>
    <fieldset style="float:left;">
        <legend><?php echo $_SESSION['lang']['header'] ?></legend>
        <table border="0" cellspacing="0">
			<tr>
			    <td><?echo $_SESSION['lang']['unit'];?></td>
			    <td> : </td>
			    <td><select style="width:180px"  id="unit" onchange="getkar();"><?echo $optunit;?></select></td>
	  		</tr>

	  		<tr>
			    <td><? echo $_SESSION['lang']['tanggal']." ".$_SESSION['lang']['evaluasi']; ?></td>
			    <td> : </td>
			    <td><input type="text" style="width:177px" value="<? echo date("d-m-Y");?>" class=myinputtext id="tglevaluasi" readonly size="12" maxlength="10" ></td>			    
			</tr>
	  		
			<tr>
			    <td><?echo $_SESSION['lang']['nama']." ".$_SESSION['lang']['karyawan'];?></td>
			    <td> : </td>
			    <td><select style="width:180px"  id="karyawan" ><?echo $optkar;?></select>
				<? echo "<img id='karyawan' onclick=z.elSearch('karyawan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>"; ?>
				</td>			    
			</tr>

			<tr>
	            <td colspan=2></td>
	            <td><button id=tomboldetail class=mybutton onclick=detail()><?php echo $_SESSION['lang']['save'] ?></button>
	            	<button id=batal class=mybutton onclick=cancel()><?php echo $_SESSION['lang']['cancel'] ?></button>
	            	</td>
			</tr>
		</table>
	</fieldset>
    <?php CLOSE_BOX() ?>
</div>

<?
echo"<div id=detail style=display:none>";
OPEN_BOX();

CLOSE_BOX();
echo"</div>";

echo"<div id=listData style='display:block'>";
OPEN_BOX();
echo"<fieldset>
		<legend><b>".$_SESSION['lang']['list']."</legend>
		    <table class=sortable cellspacing=1 cellspacing=1 border=0>
            <thead>
            <tr class=rowheader>    
                <td align=center>".$_SESSION['lang']['nourut']."</td>
                <td align=center>".$_SESSION['lang']['unit']."</td>
           		<td align=center>".$_SESSION['lang']['tanggalpengajuan']." ".$_SESSION['lang']['evaluasi']."</td>
           		<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
           		<td align=center>".$_SESSION['lang']['rekomendasi']."</td>
           		<td align=center>".$_SESSION['lang']['status']."</td>
                <td align=center>".$_SESSION['lang']['dbuat_oleh']."</td>
                <td align=center>".$_SESSION['lang']['action']."</td>
        	</thead>
        	<tbody id=container>
        	<script>loadData(0)</script>
			<tfoot id='footData'>
          </tfoot></tbody></table></fieldset>";
CLOSE_BOX();
echo "</div>";
echo close_body('');
?>