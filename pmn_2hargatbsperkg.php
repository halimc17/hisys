<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/pmn_2hargatbsperkg.js?v=<?php echo time(); ?>'></script>

<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul><b>'.getMenu('pmn_2hargatbsperkg').'</b></span><br>');

$optjam=$optmenit="<option value='00'>00</option>";
for($i=1;$i<=23;$i++){
	if(strlen($i)<2){
		$i="0".$i;
	}
	$optjam.="<option value=".$i.">".$i."</option>";
}

for($i=1;$i<=59;$i++){
	if(strlen($i)<2){
		$i="0".$i;
	}
	$optmenit.="<option value=".$i.">".$i."</option>";
}


//get existing period

$optpt=$optregional=$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','KEBUN') order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}


$str="select distinct(kodeunit) as kodeunit from ".$dbname.".pmn_5hargabelitbs";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value='".$bar['kodeunit']."'>".$namaorganisasi[$bar['kodeunit']]."</option>";
}
$optjenis.="<option value='KUD'>KUD dan EXT</option>";
$optjenis.="<option value='INT'>INTI dan AFILIASI</option>";

echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."  ".$_SESSION['lang']['rekap']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pabrik']."</td>
				<td>:</td>
				<td><select id=unitrekap style='width:184px;'>".$optunit."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['jenis']."</td>
				<td>:</td>
				<td><select id=jenisrekap style='width:184px;'>".$optjenis."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggalmulai']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext id=tanggal1rekap name=tanggal1rekap readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
					<select id=jam1rekap style=\"width:50px;\">" . $optjam . "</select>:<select id=menit1rekap style=\"width:50px;\">" . $optmenit . "</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggalselesai']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext id=tanggal2rekap name=tanggal2rekap readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
					<select id=jam2rekap style=\"width:50px;\">" . $optjam . "</select>:<select id=menit2rekap style=\"width:50px;\">" . $optmenit . "</select>
				</td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<tr>
				<td></td>
			</tr>
			
			<tr>
				<td></td>
				<td></td>
				<td colspan=3><button class=mybutton onclick=laporanrekap('html','rekap')>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=laporanrekap('excel','rekap')>".$_SESSION['lang']['excel']."</button>
				<button  class=mybutton onclick=laporanrekap('pdf','rekap')>".$_SESSION['lang']['pdf']."</button>
				<button class=mybutton onclick=cancelrekap()>".$_SESSION['lang']['cancel']."</button></td>
			</tr>
		</table>
    </fieldset>";
	

echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['detail']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pabrik']."</td>
				<td>:</td>
				<td><select id=unitdt style='width:180px;'>".$optunit."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggalmulai']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext id=tanggal1dt name=tanggal1dt readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
					<select id=jam1dt style=\"width:50px;\">" . $optjam . "</select>:<select id=menit1dt style=\"width:50px;\">" . $optmenit . "</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggalsampai']."</td>
				<td>:</td>
				<td>
					
					<input type=text class=myinputtext id=tanggal2dt name=tanggal2dt readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
					<select id=jam2dt style=\"width:50px;\">" . $optjam . "</select>:<select id=menit2dt style=\"width:50px;\">" . $optmenit . "</select>
				</td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<tr>
				<td></td>
			</tr>
			
			<tr>
				<td></td>
				<td></td>
				<td colspan=3><button class=mybutton onclick=laporandetail('html','detail')>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=laporandetail('excel','detail')>".$_SESSION['lang']['excel']."</button>
				<button class=mybutton onclick=laporandetail('pdf','detail')>".$_SESSION['lang']['pdf']."</button>
				<button class=mybutton onclick=canceldetail()>".$_SESSION['lang']['cancel']."</button></td>
			</tr>
		</table>
    </fieldset>";
	
	
CLOSE_BOX();
OPEN_BOX('','');
echo"
    <div id=container style='height:60vh;overflow:auto;'></div>";
CLOSE_BOX();
close_body();
?>