<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript1.2 src='js/kebun_5namafee.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>
<?php
$_SESSION['fee']=array();
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
if(count($adm)>0 or $_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$where="";
	$wh="";
}else{
	$where = " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
	$wh = " and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
}

$sql = "SELECT * FROM " . $dbname . ".organisasi where 1=1 ".$where ." and tipe='KEBUN' order by kodeorganisasi asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
		$i="selected";
	}else{
		$i="";
	}
    $optunit.="<option value=" . $bar['kodeorganisasi'] . " ".$i.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$arrsts=array('1'=>'Aktif','0'=>'Non Aktif');
foreach($arrsts as $key => $val){
	$i="";
	if($key=='1'){
		$i="selected";
	}
    @$optsts.="<option value=" . $key. " ".$i.">" . $val. "</option>";
	
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5namafee').'</span><br>');
echo"
	<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td><select id=unit style=\"width:150px;\">" . $optunit . "</select></td>
				
					
					<td>".$_SESSION['lang']['nama']."</td>
					<td>:</td>
					<td><input id=nama onkeydown=\"upperCaseF(this)\" class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['alamat']."</td>
					<td>:</td>
					<td colspan=4><input onkeydown=\"upperCaseF(this)\" id=alamat class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:340px;\"></td>
				</tr>
				<tr>				
					
					<td>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td><select id=status style=\"width:150px;\">" . $optsts . "</select></td>
				
					<td colspan=3 align=center>
						<input id=method value='insert' type=hidden>
						<input id=id type=hidden>
						<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
						</td>
				</tr>
			</table>
		</fieldset>";
CLOSE_BOX();
?>
<?php
OPEN_BOX();
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['find']."</legend>
							".$_SESSION['lang']['unit']." : <select id=find_unit style=\"width:150px;\">" . $optunit . "</select>&nbsp;
							".$_SESSION['lang']['nama']." : 
							<input id=find_nama class=myinputtext id='id' onkeypress='enterkey(event,find)'>
							
							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
							
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>