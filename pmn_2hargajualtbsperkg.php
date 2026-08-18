<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/pmn_2hargajualtbsperkg.js?v=<?php echo time(); ?>'></script>

<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul><b>'.getMenu('pmn_2hargajualtbsperkg').'</b></span><br>');

//get existing period

$optpt=$optregional=$optunit=$customer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}


$str="select distinct(kodeorg) as kodeorg from ".$dbname.".pmn_hargajualtbs";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value='".$bar['kodeorg']."'>".$namaorganisasi[$bar['kodeorg']]."</option>";
}

$str = "SELECT * FROM " . $dbname . ".pmn_4customer";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$customer.="<option value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
}

echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."  ".$_SESSION['lang']['rekap']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pabrik']."</td>
				<td>:</td>
				<td><select id=unitrekap style='width:180px;'>".$optunit."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['customer']."</td>
				<td>:</td>
				<td><select id=customerrekap style='width:180px;'>".$customer."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext id=tanggal1rekap name=tanggaldt1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/> s/d 
					<input type=text class=myinputtext id=tanggal2rekap name=tanggaldt2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
				</td>
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
				<td>".$_SESSION['lang']['customer']."</td>
				<td>:</td>
				<td><select id=customerdt style='width:180px;'>".$customer."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				
				<td>
					<input type=text class=myinputtext id=tanggal1dt name=tanggaldt1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/> s/d 
					<input type=text class=myinputtext id=tanggal2dt name=tanggaldt2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:75px;/>
				</td>
					
				
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