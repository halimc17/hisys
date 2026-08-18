<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script type="text/javascript" src="js/sdm_3prosesfingerprint.js?v=<?php echo time(); ?>" /></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php

OPEN_BOX('','<span class=judul>'.getMenu('sdm_3prosesfingerprint').'</span><br>');
# === Header dan Pencarian data ===
$arrPos=array("0"=>"Not Posted","1"=>"Posted");
$optPos="<option value=''>Pilih Data</option>";
foreach($arrPos as $key => $val){
	$optPos.="<option value=".$key.">".$val."</option>";
}

$optorg="<option value=''>Pilih Data</option>";
foreach(getOrgDetail(1) as $key => $val){
	$d=getNamaOrg($key,'induk');
	if($d!=$n){			
		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
		
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}


$optDiv="<option value=''>Pilih Data</option>";
$optDiv.="<option value='kantor'>UMUM</option>";
foreach(getOrgDetail(29) as $key => $val){
	$d=getNamaOrg($key,'induk');
	if($d!=$n){			
		$optDiv.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
		$optDiv.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optDiv.="</optgroup>";
	}
}

echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data('kebun')>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
			   <td>".$_SESSION['lang']['kodeorganisasi']."</td><td>:</td>
			   <td><select class='select2' id=kodeorgsch onchange='loaddata()' style=\"width:153px;\">".$optorg."</select>
				
				<td>" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select class='select2' id=divsch onchange='loaddata()' style=\"width:153px;\">".$optDiv."</select>
			   
			   <td hidden>" . $_SESSION['lang']['namakaryawan'] . "</td> 
				<td hidden>:</td>
			   <td hidden><input type=text class=myinputtext id=namasch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:150px;\" onkeypress='enterkey(event,loaddata)' /> </td>
				
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext id=tanggalsrc  onmousemove='setCalendar(this.id)' onkeypress='return false;' nkeypress=\"return_tanpa_kutip(event);\" style=\"width:150px;\"  onkeypress='enterkey(event,loaddata)' /> </td>
				
				<td>" . $_SESSION['lang']['posting'] . "</td> 
				<td>:</td>
				<td><select class='select2' id=postingsrc onchange='loaddata()' style=\"width:153px;\">".$optPos."</select>
				</td>
			
			   
			</tr>";
echo"<tr>
		<td><td>
		<td>
			<button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
			<button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr>
</table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

echo"<div id=header  style=display:none>";
OPEN_BOX();
## GET UNIT
$optUnit='';
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optUnit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optUnit.="</optgroup>";
	}
}

##GET SUBUNIT
$optSubUnit="<option value='all'>Pilih Data</option>";
$optSubUnit.="<option value=''>".$unit." - UMUM</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' order by kodeorganisasi";
$res=fetchdata($str);
foreach($res as $val){
	$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

## GET PERIODE
$optPeriode="";
$str="select periode from ".$dbname.".sdm_5periodegaji group by periode order by periode desc";
$res=fetchdata($str);
foreach($res as $val){
	$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}

$opttipekar="";
$opttipekar="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif=1 order by no asc";
$res=fetchdata($str);
foreach($res as $val){
	$opttipekar.="<option value='".$val['id']."'>".$val['tipe']."</option>";
}

## FILTER REPORT ##
echo"<fieldset style=float:left><legend><b>Form</b></legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select style=width:200px class=select2 id='unit' onchange=\"getsubunit()\">".$optUnit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td>
				<select style=width:200px class=select2 id='subunit'>".$optSubUnit."</select>
			</td>
		</tr>
		<tr style=vertical-align:top>
			<td>".$_SESSION['lang']['tipekaryawan']."</td>
			<td>:</td>
			<td>
				<select style=width:200px multiple class=select2 id='tipekary'>".$opttipekar."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<input type='text' readonly=readonly class='myinputtext' id='tglawal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:85px;' >
				s/d
				<input type='text' readonly=readonly class='myinputtext' id='tglakhir' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:82px;' >
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button onclick=\"preview('html',event)\" class='mybutton'>".$_SESSION['lang']['preview']."</button>
				<button onclick=\"preview('excel',event)\" class='mybutton'>".$_SESSION['lang']['excel']."</button>
				<button onclick=\"cancel()\" class='mybutton'>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();

echo"</div>";

OPEN_BOX();
echo"<div id=listtransaksi style=display:none>
	<div id='both_report'>
		<div id='head_tableboth' align=right>
			<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
				<img title='Full Screen' class='zImgBtn' src='images/full-screen.png'>
			</a>
		</div>
		<div id='printContainer' style='overflow:auto;height:60vh'; class='table-scroll' ></div>
	</div>
</div>

<div id=listData style='overflow:auto;';><script>loaddata(0)</script></div>
";
CLOSE_BOX();

echo close_body();
?>