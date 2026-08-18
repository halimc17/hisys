<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src="js/kebun_5premibasis.js?v=<?php echo time(); ?>"></script>
<script language="javascript" src="js/zSelect2.js?v=<?php echo time(); ?>"></script>

<?php
if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$where=" and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}
$sAfd="select distinct indukblok, namaindukblok, SUBSTR(kodeorganisasi,1,6) as divisi from ".$dbname.".organisasi where 1=1 ".$where." and tipe in ('BLOK')";
$qAfd=$owlPDO->query($sAfd) or die(print " Gagal: ".PDOException::getMessage());
$qAfd->setFetchMode(PDO::FETCH_ASSOC);
$optAfd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rAfd=$qAfd->fetch()){
	$d=$rAfd['divisi'];
	if($d!=$n){			
		$optAfd.="<optgroup label='".getNamaOrg($d)."'>";
	}
	$optAfd.="<option value=".$rAfd['indukblok'].">".$rAfd['indukblok']." - ".$rAfd['namaindukblok']."</option>";	
	$n=$d;
	if($d!=$n){			
		$optAfd.="</optgroup>";
	}
}

$optHrKerja = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrHrKerja = getEnum($dbname,"kebun_5basispanen2","tipehari");
foreach ($arrHrKerja as $hari) {
	$optHrKerja .= "<option value='".$hari."'>".$hari."</option>";
}

$optTpBuah = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrTipeBuah = getEnum($dbname,"kebun_5basispanen2","tipebuah");
foreach ($arrTipeBuah as $buah) {
	$optTpBuah .= "<option value='".$buah."'>".$buah."</option>";
}

@$optthndr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select distinct tahun as periode from ".$dbname.".kebun_5basispanen2 order by tahun desc";
$res = fetchdata($str);
foreach($res as $bar){
	#$prd[$bar['periode']]=$bar['periode'];
	@$optthndr.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}

for($x=-2;$x<12;$x++){
	$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
	@$optthn.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
	$prd[date("Y-m",$dt)]=date("Y-m",$dt);
}
#print_r($prd);


OPEN_BOX('','<span class=judul>'.getMenu('kebun_5premibasis').'</span><br>');
echo"<fieldset>
     <legend style='font-weight:bold'>".$_SESSION['lang']['form']."</legend>
         <table><td valign=top>
		 <table>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
                <td>
					<select id='tahun' name='tahun' style='width:150px;'>".$optthn."</select>
				</td>
			
				<td>".$_SESSION['lang']['kodeblok']."</td>
				<td>:</td>
                <td>
					<select class=select2 id='afd' name='afd' style='width:150px;'>".$optAfd."</select>
				</td>
			</tr>
			<tr>
				<td>Tipe Hari</td>
				<td>:</td>
				<td><select id='tipehari' name='tipehari' style='width:150px;'>".$optHrKerja."</select></td>
                

				<td>Tipe Basis</td>
				<td>:</td>
				<td><select id='tipebuah' name='tipebuah' style='width:150px;'>".$optTpBuah."</select></td>
				
            </tr>
			<tr>
				<td>".$_SESSION['lang']['brondol']." (Rp/Kg)</td>
				<td>:</td>
				<td>
					<input type='text' id='brondol' class='myinputtextnumber' onkeypress=\"return angka_doang(event);\" maxlength='10' style=width:145px onkeyup=\"z.numberFormat('brondol',2);\">
				</td>

				<td>Basis (HA)</td>
				<td>:</td>
                <td>
					<input type='text' id='basisha' class='myinputtextnumber' onkeypress=\"return angka_doang(event);\" maxlength='10' style=width:145px onkeyup=\"z.numberFormat('basisha',2);\">
				</td>
            </tr>
			<tr>
                <td>".$_SESSION['lang']['norma']."</td>
				<td>:</td>
                <td><input type='text' id='basiskg' class='myinputtextnumber' onkeypress=\"return angka_doang(event);\" maxlength='10' style=width:145px value='0' onkeyup=\"z.numberFormat('basiskg',0);\" />
				</td>

				<td>".$_SESSION['lang']['premlebihbasis']." (Rp/Kg)</td>
				<td>:</td>
                <td>
					<input type='text' id='rplb1' class='myinputtextnumber' onkeypress=\"return angka_doang(event);\" maxlength='10' style=width:145px onkeyup=\"z.numberFormat('rplb1',2);\">
				</td>
            </tr>
		 <tr><td><td><td>    
		 <input type=hidden value=insert id=method>";
		 
		$arr="##afd##tahun##tipehari##tipebuah##rplb1##basiskg##basisha##brondol##tahun##method";
		echo"<button class=mybutton onclick=simpan('kebun_slave_5premibasis','".$arr."')>".$_SESSION['lang']['save']."</button>
		 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
		 </td></td></td></tr></table></fieldset>
		 
		 </td><td valign=top>
		 
		 <fieldset>
		 <legend style='font-weight:bold'>Copy</legend>
			<table>
			<tr>
				<td>Dari Periode</td>
				<td>:</td>
                <td>
					<select id='prdawal' style='width:100px;'>".$optthndr."</select>
				</td>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
                <td>
					<select id='unitcopy' style='width:100px;'>".$optAfd."</select>
				</td>
			</tr>
			
			<tr>
				<td>Ke Periode</td>
				<td>:</td>
                <td>
					<select id='prdakhir' style='width:100px;'>".$optthn."</select>
				</td>
				
				<!---
				<td>".$_SESSION['lang']['tahuntanam']."</td>
				<td>:</td>
				<td><select id='ttcopy' name='tt' style='width:100px;'>".$optTtx."</select></td>
				--->
			</tr>
			<tr><td colspan=2></td><td><button class=mybutton onclick=copy()>".$_SESSION['lang']['save']."</button></td></tr>
			</table>
		 
		 </fieldset>
		 </td></table>
		 <input type='hidden' id=hiddenz name=hiddenz />";
		 
		 
CLOSE_BOX();
OPEN_BOX();
//ISI UNTUK DAFTAR 
	if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$where=" and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}

$sAfd="select distinct indukblok, namaindukblok from ".$dbname.".organisasi where 1=1 ".$where." and tipe in ('BLOK')";
$qAfd=$owlPDO->query($sAfd) or die(print " Gagal: ".PDOException::getMessage());
$qAfd->setFetchMode(PDO::FETCH_ASSOC);
$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rAfd=$qAfd->fetch()){
	$optUnit.="<option value='".$rAfd['indukblok']."'>".$rAfd['indukblok']." - ".$rAfd['namaindukblok']."</option>";	
}

$optHrKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrHrKerja = getEnum($dbname,"kebun_5basispanen2","tipehari");
foreach ($arrHrKerja as $hari) {
	$optHrKerja .= "<option value='".$hari."'>".$hari."</option>";
}

$optTpBuah = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrTipeBuah = getEnum($dbname,"kebun_5basispanen2","tipebuah");
foreach ($arrTipeBuah as $buah) {
	$optTpBuah .= "<option value='".$buah."'>".$buah."</option>";
}

echo "<fieldset style=float:left>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table>
				<tr>
					<td>".$_SESSION['lang']['kodeblok']."</td>
					<td>:</td>
					<td>
						<select class='select2' id='unitsrc' style='width:150px;'>".$optUnit."</select>
					</td>
					
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td>
						<input type='text' id='tahunsrc' class='myinputtext' style=width:66px onkeypress='enterkey(event,loadData)'>
					</td>
					
					<td>Tipe Hari</td>
					<td>:</td>
					<td>
						<select class='select2' id='tipeharisrc' style='width:150px;'>".$optHrKerja."</select>
					</td>

					<td>Tipe Basis</td>
					<td>:</td>
					<td>
						<select class='select2' id='tipebuahsrc' style='width:150px;'>".$optTpBuah."</select>
					</td>
					
					<td><button class=mybutton onclick=loadData()>".$_SESSION['lang']['find']."</button></td>
					
				</tr>
                
			</table>
		</fieldset><div style=clear:both></div>
		<div id=container> 
			<script>loadData()</script>
		</div>
	";
CLOSE_BOX();
echo close_body();
?>
