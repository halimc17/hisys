<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
	<script language=javascript1.2 src='js/tipekaryawan.js?v=<?php echo time(); ?>'></script>
<?php

include('master_mainMenu.php');

$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
foreach($arrstatus as $kei=>$fal){
	$optstatus.="<option value='".$kei."'>".$fal."</option>";
} 
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5tipekaryawan').'<br></span>');

echo"<fieldset style='float:left;'><table>
     <tr>
		<td>" . $_SESSION['lang']['nourut'] . "</td>
		<td>:</td>
		<td><input type=text id=no size=3  maxlength5 onkeypress=\"return angka_doang(event);\" class=myinputtext></td>
	</tr>
    
	<tr>
		<td hidden>" . $_SESSION['lang']['id'] . "</td>
		<td hidden>:</td>
		<td hidden><input type=text id=kode size=3  maxlength5 onkeypress=\"return angka_doang(event);\" class=myinputtext></td></tr>
	 
	 
	 <tr>
		<td>" . $_SESSION['lang']['tipekaryawan'] . "</td>
		<td>:</td>
		<td><input type=text id=nama size=45 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
	</tr>
	 <tr>
		<td>".$_SESSION['lang']['status']."</td>
		<td>:</td>
		<td><select id=aktif style=\"width:130px;\" >".$optstatus."</select></td>
	</tr>
    
	<tr><td></td><td></td><td>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanTipeKar()>" . $_SESSION['lang']['save'] . "</button>
	 <button class=mybutton onclick=cancelTipeKar()>" . $_SESSION['lang']['cancel'] . "</button>
	 </td></tr></table>
	 </fieldset><div style=clear:both></div>";
CLOSE_BOX();
OPEN_BOX();
// echo open_theme($_SESSION['lang']['list'] . ' ' . $_SESSION['lang']['tipekaryawan']);
echo"<div class='table-scroll'><table class=sortable cellspacing=1 cellpadding=7 style='width:100%;'  border=0>
	     <thead>
		 <tr align=center class=rowcontent>
			<th style='width:50px;'>" . $_SESSION['lang']['nourut'] . "</th>
			<th style='width:50px;' hidden>" . $_SESSION['lang']['id'] . "</th>
			<th>" . $_SESSION['lang']['tipekaryawan'] . "</th>
			<th>" . $_SESSION['lang']['aktif'] . "</th>";

		$str="select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by substr(tipe,1,1) asc";
		$res = fetchData($str);
		echo"<th align=center id=judul_1>GLOBAL<br>
				<input type='checkbox' onchange=simpandetail(this,'','GLOBAL');>
			</th>";
		$no=1;
		foreach($res as $bar){
			$no++;
			echo"<th align=center id=judul_".$no.">".$bar['tipe']."<br>
				<input type='checkbox' onchange=simpandetail(this,'','".$bar['tipe']."');></th>";
		}
		
		echo"<th style='width:30px;align:center;z-index:99'>Action</th></tr>
		 </tr>
		 </thead>
		 <tbody id=container><script>loaddata()</script>";
echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table></div>";

echo close_theme();
CLOSE_BOX();
echo close_body();
?>