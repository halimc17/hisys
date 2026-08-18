<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript src='js/pmn_hargabelitbs.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php

$optsupp=$optunit=$opttipesupplier= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optsuppsch=$optunitsch=$opttipesuppliersch= "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$arrtipesup=array('SUPPLIERTBSEXT'=>'SUPPLIERTBSEXT','SUPPLIERTBSKUD'=>'SUPPLIERTBSKUD','SUPPLIERTBSAFI'=>'SUPPLIERTBSAFI','SUPPLIERTBSINT'=>'SUPPLIERTBSINT');
foreach($arrtipesup as $key){
	$opttipesuppliersch.="<option value='".$key."'>".$key."</option>";
}

// $str = "SELECT kodeunit FROM " . $dbname . ".kebun_5namakud";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $res->fetch()) {
    // $arrkodeunit[$bar['kodeunit']]=$bar['kodeunit'];
// }


// $arrkodeunit=array("KBPE","SNPE","AA1E","AA2E");
// $str="select";

#= daftarkan parameter aplikasi unit kebun untuk beli tbs
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='SL' and kodeparameter='SLUNITBELI'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $bar=$res->fetch(); 		
$res=fetchdata($str);
foreach($res as $bar){
	$kodeunit=$bar['nilai'];
}	
$arrbpjs=explode(',',$kodeunit);
foreach($arrbpjs as $key){
	$arrunit[$key]=$key;
}

// echo"<pre>";
// print_r($arrunit);

$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where tipe='KEBUN' and inti='0' order by kodeorganisasi asc";
// echo $str;exit();
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
    $optunitsch.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}




// $str="select * from ".$dbname.".organisasi where tipe in ('KEBUN') ";
// $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $res->fetch()){
	// $nmorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
// }

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI','SUPPLIERTBSINT') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
}

// $str = "SELECT distinct(supplierid) as supplierid FROM " . $dbname . ".pmn_hargabelitbs";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $res->fetch()) {
	// if(strlen($bar['supplierid'])>6){
		  // $optsuppsch.="<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $nmsupplier[$bar['supplierid']] . "</option>";
	// }else{
		   // $optsuppsch.="<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $nmorganisasi[$bar['supplierid']] . "</option>";
	// }
// }

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

 $optaktif.="<option value='1'>".$_SESSION['lang']['ya']."</option>";
 $optaktif.="<option value='0'>".$_SESSION['lang']['tidak']."</option>";

$frm[0]='';
// $frm[1]='';
// $frm[2]='';

OPEN_BOX('','<span class=judul>'.getMenu('pmn_hargabelitbs').'</span>');


// $frm[0].="<fieldset>
//     <legend>".$_SESSION['lang']['form']."</legend>
// 	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
// 		<tr>
// 			<td>".$_SESSION['lang']['unit']."</td> 
// 			<td>:</td>
// 			<td><select id=kodeunitmaster onchange=getsupmaster(); style=\"width:155px;\">" . $optunit . "</select>
// 		</td>
// 		</tr>
// 		<tr>
// 			<td>Supplier</td> 
// 			<td>:</td>
// 			<td><select id=suppliermaster  style=\"width:155px;\">" . $optsupp . "</select>
// 				<img id='suppliermaster' onclick=z.elSearch('suppliermaster',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
// 			</td>
// 		</tr>
// 		<tr>
// 			<td>".$_SESSION['lang']['aktif']."</td> 
// 			<td>:</td>
// 			<td><select id=aktifmaster style=\"width:155px;\">" . $optaktif . "</select>
// 		</td>
// 		</tr>
// 		<tr>
// 			<td colspan=2></td>
// 			<td colspan=3>
// 				<button class=mybutton onclick=simpanmaster()>".$_SESSION['lang']['save']."</button>
// 				<button class=mybutton onclick=batalmaster()>".$_SESSION['lang']['cancel']."</button>
// 				<input type=hidden id=methodmaster value='insertmaster'>
// 			</td>
// 		</tr>
// 	</table>

// </fieldset>";


// $frm[0].="<fieldset>
//         <legend>".$_SESSION['lang']['list']."</legend>
// 			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
// 				<tr>
// 					<td>
// 						<fieldset>
// 							<legend>".$_SESSION['lang']['find']."</legend>
// 							<table>
// 								<tr>
// 									<td>".$_SESSION['lang']['unit']."</td>
// 									<td>:</td>
// 									<td><select id=kodeunitmastercari style=\"width:155px;\">".$optunitsch."</select></td> 
// 									<td>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['supplier']."</td>
// 									<td>:</td>
// 									<td><select id=tipemastercari style=\"width:155px;\">" . $opttipesuppliersch . "</select></td>
// 									<td><button class=mybutton onclick=loaddatamaster(0)>".$_SESSION['lang']['find']."</button>
// 										<button class=mybutton onclick=batalcarimaster()>".$_SESSION['lang']['cancel']."</button></td>
// 								</tr>
// 							</table>
// 						</fieldset>
// 					</td> 
// 				</tr>
// 			</table>
		
//         <div id=containermaster> 
//             <script>loaddatamaster(0)</script>
// 
//         </div>
//     </fieldset>";


$frm[0].="<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']." Input</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td> 
			<td>:</td>
			<td coslpan=2><select id=kodeunitharga  onchange=gettipesupplier() style=\"width:155px;\">" . $optunit . "</select></td>
			
			<td>".$_SESSION['lang']['tahuntanam']." / ".$_SESSION['lang']['grade']."</td> 
			<td>:</td>
			<td><input type=text  id=tahuntanamharga class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\" maxlength=100 ></td>

 		</tr>
		<tr>
			<td hidden>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['supplier']."</td> 
			<td hidden>:</td>
			<td hidden coslpan=2><select id=tipeharga style=\"width:155px;\">" . $opttipesupplier . "</select></td>
			
		<tr>
			<td>".$_SESSION['lang']['tanggalmulai']."</td> 
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' readonly=readonly id='tanggalharga' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:63px;' value='".date('d-m-Y')."' />
				<select id=jamharga style=\"width:40px;\">" . $optjam . "</select>:<select id=menitharga style=\"width:40px;\">" . $optmenit . "</select>
			</td>
			
			<td>".$_SESSION['lang']['harga']." Awal Realisasi</td> 
			<td>:</td>
			<td><input type=text id=awalrealisasiharga class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\" maxlength=100 ></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggalsampai']."</td> 
			<td>:</td>
			<td>
			<input type='text' class='myinputtext' readonly=readonly id='tanggalharga2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:63px;' value='".date('d-m-Y')."' />
			<select id=jamharga2 style=\"width:40px;\">" . $optjam . "</select>:<select id=menitharga2 style=\"width:40px;\">" . $optmenit . "</select>
			</td>
			
			<td>".$_SESSION['lang']['harga']." Awal Disbun</td> 
			<td>:</td>
			<td><input type=text  id=awaldisbunharga class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\" maxlength=100 ></td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=previewharga() id=buttonpreviewharga>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batalharga()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

// $frm[0].="<fieldset style=float:left>
//     <legend>".$_SESSION['lang']['form']." Copy Data</legend>
// 	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
// 		<tr>
// 			<td>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['awal']."</td> 
// 			<td>:</td>
// 			<td coslpan=2><select id=kodeunithargacopy onchange=gettipesuppliercopy()   style=\"width:155px;\">" . $optunit . "</select></td>
			
// 			<td>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['tujuan']."</td> 
// 			<td>:</td>
// 			<td coslpan=2><select id=kodeunitharga2copy  onchange=gettipesupplier2()  style=\"width:155px;\">" . $optunit . "</select></td>
// 		</tr>
// 		<tr>
// 			<td>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['supplier']." ".$_SESSION['lang']['awal']."</td> 
// 			<td>:</td>
// 			<td coslpan=2><select id=tipehargacopy style=\"width:155px;\">" . $opttipesupplier . "</select>
			
// 			<td>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['supplier']." ".$_SESSION['lang']['tujuan']."</td> 
// 			<td>:</td>
// 			<td coslpan=2><select id=tipeharga2copy style=\"width:155px;\">" . $opttipesupplier . "</select>
// 		</td>
// 		<tr>
// 			<td>".$_SESSION['lang']['tanggalmulai']." ".$_SESSION['lang']['awal']."</td> 
// 			<td>:</td>
// 			<td>
// 				<input type='text' class='myinputtext' readonly=readonly id='tanggalhargacopy' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:63px;'/>
// 				<select id=jamhargacopy style=\"width:40px;\">" . $optjam . "</select>:<select id=menithargacopy style=\"width:40px;\">" . $optmenit . "</select>
// 			</td>
// 			<td>".$_SESSION['lang']['tanggalmulai']." Berikutnya</td> 
// 			<td>:</td>
// 			<td>
// 				<input type='text' class='myinputtext' id='tanggalhargatujuancopy' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:63px;' readonly/>
// 				<select id=jamhargatujuancopy style=\"width:40px;\">" . $optjam . "</select>:<select id=menithargatujuancopy style=\"width:40px;\">" . $optmenit . "</select>
// 			</td>
// 		</tr>
// 			<tr>
// 			<td>".$_SESSION['lang']['tanggalsampai']." Awal</td> 
// 			<td>:</td>
// 			<td>
// 				<input type='text' class='myinputtext' readonly=readonly id='tanggalharga2copy' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:63px;'/>
// 				<select id=jamharga2copy style=\"width:40px;\">" . $optjam . "</select>:<select id=menitharga2copy style=\"width:40px;\">" . $optmenit . "</select>
// 			</td>
// 			<td>".$_SESSION['lang']['tanggalsampai']." Berikutnya</td> 
// 			<td>:</td>
// 			<td>
// 				<input type='text' class='myinputtext' id='tanggalhargatujuan2copy' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:63px;' readonly/>
// 				<select id=jamhargatujuan2copy style=\"width:40px;\">" . $optjam . "</select>:<select id=menithargatujuan2copy style=\"width:40px;\">" . $optmenit . "</select>
// 			</td>
// 		</tr>
// 		<tr>
// 			<td>".$_SESSION['lang']['tahuntanam']." / ".$_SESSION['lang']['grade']."</td>
// 			<td>:</td>
// 			<td coslpan=2>
			
// 			<input type=text  id=tahuntanamhargacopy onkeypress=\"return_tanpa_kutip(event);\"  placeholder='Seluruhnya' class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\" maxlength=100 ></td>
// 		</tr>
		
// 		<tr>
// 			<td colspan=2></td>
// 			<td colspan=4>
// 				<button class=mybutton onclick=prosescopy() id=buttonpreviewharga>".$_SESSION['lang']['proses']."</button>
// 				<button class=mybutton onclick=batalprosescopy()>".$_SESSION['lang']['cancel']."</button> 
// 		</tr>
// 	</table>
// </fieldset>";

$frm[0].="<div style=clear:both></div>
		<fieldset id=detaildataharga style=display:none>
        <legend>".$_SESSION['lang']['detail']."</legend>
        <div id=detailharga> 
        </div>
    </fieldset>";

$frm[0].="<div style=clear:both></div>
		<fieldset id=listdataharga style='display:block;'>
        <legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['find']."</legend>
								<table>
									<tr>
										<td>".$_SESSION['lang']['unit']."</td>
										<td>:</td>
										<td><select id=kodeunithargacari style=\"width:155px;\">".$optunitsch."</select></td>
										
										<td hidden>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['supplier']."</td>
										<td hidden>:</td>
										<td hidden><select id=tipehargacari style=\"width:155px;\">" . $opttipesuppliersch . "</select></td>
										
										<td>".$_SESSION['lang']['tahuntanam']." / ".$_SESSION['lang']['grade']."</td>
										<td>:</td>
										<td><input type=text  id=tahuntanamhargacari nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\" maxlength=100 ></td>
									
										<td>".$_SESSION['lang']['tanggal']."</td>
										<td>:</td>
										<td><input type='text' class='myinputtext' id='tanggalhargacari' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:150px;' readonly/></td>
									
										<td>
											<button class=mybutton onclick=loaddataharga(0)>".$_SESSION['lang']['find']."</button>
											<button class=mybutton onclick=batalcariharga()>".$_SESSION['lang']['cancel']."</button>
										</td>
										
									</tr>
								</table>
							
							
							
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=containerharga> 
           
        </div>
    </fieldset>";// <script>loaddataharga(0)</script>
	
	

// $frm[2].="<fieldset>
//     <legend>".$_SESSION['lang']['form']."</legend>
// 	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
// 	<tr>
// 		<td>" . $_SESSION['lang']['unit'] . "</td>
// 		<td>:</td>
// 		<td><select id=kodeunitgrade style=\"width:150px;\">" . $optunit . "</select></td>
// 	</tr>
// 	<tr>
// 		<td>" . $_SESSION['lang']['grade'] . "</td>
// 		<td>:</td>
// 		<td><input type=text maxlength=10  class=myinputtext id=kodegrade  style=\"width:145px;\" onkeypress=\"return_tanpa_kutip(event);\" style=\"width:116px;\"></td>
// 	</tr>
// 	<tr>
// 		<td>" . $_SESSION['lang']['batasbawah'] . "</td>
// 		<td>: >=</td>
// 		<td><input type=text maxlength=10 value=0 class=myinputtextnumber id=batasbawahgrade onkeydown=upperCaseF(this) size=26 onblur='change_number(this);' onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"></td>
// 	</tr>
// 	<tr>
// 		<td>" . $_SESSION['lang']['batasatas'] . "</td>
// 		<td>: <</td>
// 		<td><input type=text maxlength=10 value=0 class=myinputtextnumber id=batasatasgrade onkeydown=upperCaseF(this) size=26 onblur='change_number(this);' onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"></td>
// 	</tr>
// 		<tr>
// 			<td colspan=2></td>
// 			<td colspan=3>
// 				<button class=mybutton onclick=simpangrade()>".$_SESSION['lang']['save']."</button>
// 				<button class=mybutton onclick=batalgrade()>".$_SESSION['lang']['cancel']."</button>
// 				<input type=hidden id=methodgrade value='insertgrade'>
// 			</td>
// 		</tr>
// 	</table>

// </fieldset><div style=clear:both></div>";	


// $frm[2].="<fieldset>
//         <legend>".$_SESSION['lang']['list']."</legend>
//          <div id=containergrade> 
          
//         </div>
//     </fieldset>";
	

	### HEADER TAB ###
// $hfrm[0]=strtoupper('Rekap Supplier');
$hfrm[0]=strtoupper('Daftar Harga');
// $hfrm[2]=strtoupper('Grade External');


### HEADER TAB ###

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,'auto');	
	
CLOSE_BOX();
echo close_body();                  
?>