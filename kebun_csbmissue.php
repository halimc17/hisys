<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
?>
<script src='js/kebun_csbmissue.js?v=<?php echo time(); ?>'></script>
<?

$where="";

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$where = " and kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$_SESSION['empl']['kodeorganisasi']."')";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}


$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgx="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by induk, namaorganisasi asc ";
$res=fetchdata($str);
foreach($res as $bar){
	$d=$bar['induk'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
		$optorgx.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
	}
	if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){		
		$optorg.="<option value=".$bar['kodeorganisasi']." selected>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$optorgx.="<option value=".$bar['kodeorganisasi']." selected>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}else{
		$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$optorgx.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
	
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgx.="</optgroup>";
	}
}


$where="";
$where.=" and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
if($_SESSION['empl']['subbagian']!=''){
	$where.=" and kodeorganisasi like '".$_SESSION['empl']['subbagian']."%'";
}

$optdiv="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdivx="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPeriodex="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING') ".$where."";
$res = fetchData($str);
foreach($res as $bar){
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$nminduk[$bar['kodeorganisasi']];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optdiv.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optdivx.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$sel="";
	if($bar['kodeorganisasi']==$_SESSION['empl']['subbagian']){
		$sel="selected";
	}
	
	$optdiv.="<option value=".$bar['kodeorganisasi']." ".$sel.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$optdivx.="<option value=".$bar['kodeorganisasi']." ".$sel.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){
		$optdiv.="</optgroup>";
		$optdivx.="</optgroup>";
	}
}

for($x=0;$x<15;$x++){
	$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
	$optPeriode.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
	$optPeriodex.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}



OPEN_BOX('','<span class=judul>'.getMenu('kebun_csbmissue').'</span><br>');
echo"<div id=action_list>";
echo"<table border=0>
     <tr valign=middle>
	 <td align=center style='width:80px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:80px;cursor:pointer;display:none;' onclick=viewlist()>
	   <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['preview'] . "'><br>" . $_SESSION['lang']['preview'] . "</td>
	 <td>
	 
	 <td align=center style='width:80px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader ><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
				<td>" . $_SESSION['lang']['kodeorg'] . "</td> 
				<td>:</td>
				<td><select id=kodeorgsch onchange='loaddata()' style=\"width:135px;\">".$optorgx."</select></td>
				
				<td>" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select id=divisisch onchange='loaddata()' style=\"width:135px;\">".$optdivx."</select></td>
			</tr>
			<tr>	
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=periodesch onchange='loaddata()' style=\"width:135px;\">".$optPeriodex."</select></td>
			</tr>
			<tr>
				<td><td>
				<td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td>
				</td>
			</tr>
		</table>
	</fieldset>
	</td>
	<td>
         
		<fieldset id=duplicateform ><legend><b>Copy</b></legend> 
         <table>
			<tr>	
				<td>" . $_SESSION['lang']['dari'] . "</td> 
				<td>:</td>
				<td><select id=daricopy  style=\"width:135px;\">".$optPeriode."</select></td>
				
				<td>" . $_SESSION['lang']['ke'] . "</td> 
				<td>:</td>
				<td><select id=kecopy style=\"width:135px;\">".$optPeriode."</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select id=divisicopy style=\"width:135px;\">".$optdiv."</select></td>
			</tr>
			<tr>
				<td><td>
				<td><button class=mybutton onclick=copy(0)>Copy</button></td>
				</td>
			</tr>
		</table>
	</fieldset>
	</td>
	</tr></table> ";
echo "</div>";
CLOSE_BOX();

echo"<div id=listdata style=display:block>";
OPEN_BOX();
echo "<div id=contain ><script>loaddata(0)</script></div>

	 
			
		";
CLOSE_BOX();
echo "</div>";

$arr=array(
	"issues"=>"Key Issues",
	"pica"  =>"PICA"
);
foreach($arr as $res => $bar){
	@$optjns.="<option value=".$res.">".$bar."</option>";
}

# === Form header input data ===
echo "<div id=header style=display:none>";
OPEN_BOX('','','header_trans');
echo "<fieldset style=float:left>
		<legend>Header</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select onmousemove=hapuswarna(this.id); style=\"width:150px;\" id=kodeorg>" . $optorg . "</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select onmousemove=hapuswarna(this.id); style=\"width:150px;\" id=divisi>" . $optdiv . "</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select onmousemove=hapuswarna(this.id); style=\"width:150px;\" id=periode>" . $optPeriode . "</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['jenis'] . "</td> 
				<td>:</td>
				<td><select onmousemove=hapuswarna(this.id); style=\"width:150px;\" id=jenis>" . $optjns . "</select></td>
			</tr> 
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=previewdata()>" . $_SESSION['lang']['save'] . "</button>
					<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=mode value='baru'>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";

# === Form Detail Input Data ===
echo"<div id=detail_cont style=display:none>";
OPEN_BOX();
echo"<div id=detail></div>";
CLOSE_BOX();
echo"</div>";


echo close_body();
?>