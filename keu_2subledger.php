<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/keu_2subledger.js?v=<?php echo time(); ?>'></script>

<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul><b>'.getMenu('keu_2subledger').'</b></span><br>');

//get existing period

$optpt=$optregional=$optnoakun=$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}


$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');


$str="select distinct noakun from ".$dbname.".keu_jurnaldt_vw where 
	  left(noakun,3) in (113,114,116,118,211,129) order by noakun asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optnoakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$nmakun[$bar['noakun']]."</option>";
}

echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td><select id=unit style='width:100px;'>".$optunit."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext id=tanggal1 name=tanggaldt1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:100px;/>
					s/d <input type=text class=myinputtext id=tanggal2 name=tanggaldt2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:100px;/>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['noakun']."</td>
				<td>:</td>
				<td><select id=noakun style='width:100px;'>".$optnoakun."</select></td>
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
				<td colspan=3><button class=mybutton onclick=laporan('html')>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=laporan('excel')>".$_SESSION['lang']['excel']."</button>
				<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
				<button hidden class=mybutton onclick=laporan('pdf')>".$_SESSION['lang']['pdf']."</button></td>
			</tr>
		</table>
    </fieldset>";
	
	
CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset>
     <legend>".$_SESSION['lang']['list']."</legend>
    <div id=container style='width:100%';height:330px;overflow:auto;'>
    </div></fieldset>";
CLOSE_BOX();
close_body();
?>