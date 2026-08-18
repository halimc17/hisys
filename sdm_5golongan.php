<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript src='js/golongan.js?v=<?php echo time(); ?>'></script>
<?php


$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
foreach($arrstatus as $kei=>$fal){
	$optstatus.="<option value='".$kei."'>".$fal."</option>";
}  

include('master_mainMenu.php');
// OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pengaturangolongan']).'</span>');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5golongan').'<br></span>');

echo"<fieldset style='width:500px;'><table>
     <tr>
		<td>" . $_SESSION['lang']['levelcode'] . "</td>
		<td>:</td>
		<td><input type=text id=kodegolongan size=3 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
	</tr>
	 <tr><td>" . $_SESSION['lang']['levelname'] . "</td>
	 <td>:</td>
	 <td><input type=text id=namagolongan size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
     
	 <tr>
		<td>".$_SESSION['lang']['status']."</td>
		<td>:</td>
		<td><select id=aktif style=\"width:130px;\" >".$optstatus."</select></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td>
			 <input type=hidden id=method value='insert'>
			 <button class=mybutton onclick=simpanGolongan()>" . $_SESSION['lang']['save'] . "</button>
			 <button class=mybutton onclick=cancelGolongan()>" . $_SESSION['lang']['cancel'] . "</button>
		
		</td>
	 </table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();	 	 
// echo open_theme($_SESSION['lang']['availavel']);
echo"<div class='table-scroll'><table class=sortable cellspacing=1 cellpadding=7 style='width:100%;'  border=0>
		<thead>
		 <tr align=center class=rowcontent>
		 	<th>" . str_replace(".","<br>",$_SESSION['lang']['levelcode']). "</th>
			<th>" . $_SESSION['lang']['levelname'] . "</th>
			<th>" . $_SESSION['lang']['status'] . "</th>";
		 
		$str="select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by substr(tipe,1,1) asc";
		$res = fetchData($str);
		echo "<th align=center id=judul_1>GLOBAL<br>
				<input type='checkbox' onchange=simpandetail(this,'','GLOBAL');>
			</th>";

		$no=1;
		foreach($res as $bar){
			$no++;
			echo"<th align=center id=judul_".$no.">".$bar['tipe']."<br>
					<input type='checkbox' onchange=simpandetail(this,'','".$bar['tipe']."');>
				</th>";
		}
		echo"<th style='width:30px;align:center;z-index:99'>Action</th></tr>
		</thead>
		<tbody id=container><script>loaddata()</script>";
echo"</tbody><tfoot></tfoot></table></div>";
// echo close_theme();
CLOSE_BOX();
echo close_body();
?>