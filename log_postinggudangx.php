<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/log_postinggudangx.js?v=<?php echo time(); ?>'></script>
<?php

$where = "";

$str="select distinct(a.kodeorganisasi) as kodeorganisasi, b.namaorganisasi, b.alokasi from ".$dbname.".user_orgdetail a left join ".$dbname.".organisasi b on a.kodeorganisasi=b.kodeorganisasi where length(b.kodeorganisasi)=4 and a.namauser='".$_SESSION['standard']['username']."' order by b.kodeorganisasi";
$res = fetchdata($str);
foreach ($res as $bar) {
	//$hasil[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
}
$hasil[$_SESSION['empl']['lokasitugas']]=$_SESSION['empl']['lokasitugas'];
if(count($hasil)>0){
	$where.= " and substr(kodeorganisasi,1,4) in ('".implode("','",$hasil)."')";
}



$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
$optorg2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".organisasi where 1=1 ".$where." order by induk asc ";
$res=fetchdata($str);
foreach($res as $bar){
	$d=$bar['induk'];
	if(substr($bar['tipe'],0,6)=='GUDANG'){		
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			$optorg2.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
			$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			$optorg2.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$n=$d;
		if($d!=$n){			
			$optorg.="</optgroup>";
			$optorg2.="</optgroup>";
		}
	}
	if(strlen($d)=='3'){		
		$datapt[$d]=$d;
	}
}

if($_SESSION['empl']['subbagian']!=""){	
	$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
	$str = "select * from ".$dbname.".kebun_5gudangtransaksi where 1=1 and afdeling ='".$_SESSION['empl']['subbagian']."' and status='1'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$optorg.="<option value=".$bar['kodegudang'].">".$bar['kodegudang']." - ".getNamaOrg($bar['kodegudang'])."</option>";
	}
}
foreach($datapt as $d){
	$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
	$optpt.="<option value=".$d.">".$d." - ".$nmorg[$d]."</option>";
}

$optgol="<option value=''>".$_SESSION['lang']['all']."</option>";
$datatipe = array('0','1','2','3','4','5','6','7');
foreach($datatipe as $d){
	$optgol.="<option value=".$d.">".getDetailTipeMutasi($d)."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('log_postinggudangx').'</span>');
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
		<a class='notification'>
		  <img class=delliconBig src=images/archive.png title='".$_SESSION['lang']['permintaanbaru']."'>
		  <span class='badge' id='countnotif' style='vertical-align:top;align:center;font-size:10px;color:white;background-color:red;border-radius: 5px 5px 5px 5px;'>0</span>
		</a>
		<br>".$_SESSION['lang']['posting']."
	</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
			<table>
				<tr>
					<td>" . $_SESSION['lang']['pt'] . "</td>
					<td>:</td>
					<td><select id=kodeorgsch onchange=getkodegudang(); style=\"width:150px;\">".$optpt."</select></td>
					
					<td>" . $_SESSION['lang']['momordok'] . "</td>
					<td>:</td>
					<td><input style=\"width:146px;\" class=myinputtext type=text id=nodoksch  nkeypress=\"return tanpa_kutip(event);\" onkeypress='enterkey(event,loaddata())'></td>
					
					<td>" . $_SESSION['lang']['asaltujuan'] . "</td>
					<td>:</td>
					<td><select id=asalsch onchange=loaddata(0); style=\"width:150px;\">" . $optorg2 . "</select></td>
					
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					<td>:</td>
					<td><input type=text  onchange=loaddata(0); class=myinputtext style='width:100px;' id=tanggalsch onmousemove=setCalendar(this.id); onkeypress=return false; maxlength=10 /></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['sloc'] . "</td>
					<td>:</td>
					<td><select id=kodegdgsch onchange=loaddata(0); style=\"width:150px;\">" . $optorg . "</select></td>
					
					<td>" . $_SESSION['lang']['nopo'] . "</td>
					<td>:</td>
					<td><input style=\"width:146px;\" id=noposch class=myinputtext nkeypress=\"return tanpa_kutip(event);\"  onkeypress='enterkey(event,loaddata())'></td>
					
					<td>" . $_SESSION['lang']['noreferensi'] . "</td>
					<td>:</td>
					<td><input style=\"width:146px;\" id=noreffsch class=myinputtext nkeypress=\"return tanpa_kutip(event);\"  onkeypress='enterkey(event,loaddata())'></td>
					
					<td>" . $_SESSION['lang']['tipe'] . "</td>
					<td>:</td>
					<td><select id=tipesch onchange=loaddata(0); style=\"width:105px;\">" . $optgol . "</select></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton id=tombolcari onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
					<button class=mybutton id=tombolbatalcari onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button></td>
				</tr>
			</table>
		</fieldset>
     </tr></table>";

echo "</div>";
CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData style=display:block>";
echo"<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center>No.</th>
			<th align=center>".$_SESSION['lang']['pt']."</th>
			<th align=center>".$_SESSION['lang']['sloc']."</th>
			<th align=center>".$_SESSION['lang']['tipe']."</th>
			<th align=center>".$_SESSION['lang']['momordok']."</th>
			<th align=center>".$_SESSION['lang']['tanggal']."</th>
			<th align=center>".$_SESSION['lang']['nopo']."</th>	
			<th align=center>".$_SESSION['lang']['supplier']."</th> 
			<th align=center>".$_SESSION['lang']['asaltujuan']."</th>
			<th align=center>".$_SESSION['lang']['noreferensi']."</th>			  
			<th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
			<th align=center>".$_SESSION['lang']['dipostingoleh']."</th>
			<th align=center>Action</th>
		</tr>
	</thead>
	
	<tbody id=contain><script>loaddata(0)</script></tbody>
	<tfoot id=footData></tfoot>
	</table>";
	
echo "</div>";

echo"<div id=contdetail style=display:none;></div>";

CLOSE_BOX();
echo close_body();
?>