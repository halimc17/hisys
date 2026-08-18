<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript1.2 src='js/kebun_rkbx.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
# Organisasi
$optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
$where='';
if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){			
	$where= " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' ".$where."";
$res = fetchData($str);
foreach($res as $key => $val){
	$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'",'2','0',true);

# Divisi
$optdiv = "<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6  and tipe in ('AFDELING','BIBITAN') and kodeorganisasi like '%".@key($optorg)."%'";
$res = fetchData($str);
foreach($res as $key => $val){
	$optdiv.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']."</option>";	
}

# Posting
$arrPos=array("0"=>"Not Posted","1"=>"Posted");
$optPos="<option value=''></option>";
foreach($arrPos as $key => $val){
	$optPos.="<option value=".$key.">".$val."</option>";
}

# Periode
$optprd = "<option value=''></option>";
$str="select DISTINCT (periode) as prd from ".$dbname.".kebun_rkbht order by periode desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$optprd.="<option value=".$val['prd'].">".$val['prd']."</option>";	
}

for($x=-2;$x<5;$x++){
	$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
	@$optPeriode.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_rkbx').'</span>');	
# === Header dan Pencarian data ===

$str = "select * from ".$dbname.".setup_parameterappl where kodeparameter='EDITRKB'"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
$arrdata=explode(',',$bar['nilai']);
foreach($arrdata as $key){
	$arrjab[]=$key;
}	

if(in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
	#bisa edit walau sudah setujui
	$jab='1';
}else{
	$jab='0';
}

echo"<input style=display:none value=".$jab." id=admin>";
echo"<input style=display:none value='0' id=statussetuju>";

echo"<div id=action_list><input style=display:none value=".@$statusawal." id=stsawal>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
			   <td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
			   <td><input type=text class=myinputtext id=notransaksisch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>

				<td hidden>" . $_SESSION['lang']['divisi'] . "</td> 
				<td hidden>:</td>
				<td hidden><select id=divsch onchange='loaddata()' style=\"width:130px;\">".$optdiv."</select></td>
				
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=periodesch onchange='loaddata()' style=\"width:130px;\">".$optprd."</select>
				</td>
				
				<td>" . $_SESSION['lang']['posting'] . "</td> 
				<td>:</td>
				<td><select id=postingsrc onchange='loaddata()' style=\"width:130px;\">".$optPos."</select>
				</td>
			
			   
			</tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();

#class='table-scroll'
echo "
		<div >    
			<table cellpadding=8 cellspacing=1 border=0 class=sortable >
				<thead>
					<tr class=rowheader>
						<th align=center width=40px>" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center >" . $_SESSION['lang']['notransaksi'] . "</th>
						<th align=center >" . $_SESSION['lang']['organisasi'] . "</th>
						<th align=center width=70px>" . $_SESSION['lang']['periode'] . "</th>
						<th align=center >" . $_SESSION['lang']['updateby'] . "</th>
						<th align=center >" . $_SESSION['lang']['status'] . "</th>
						<th align=center colspan=6>" . $_SESSION['lang']['action'] . "</th>
				</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>
	 ";
CLOSE_BOX();
echo "</div>";

# === Form header input data ===
echo "<div id=header style=display:none>";
OPEN_BOX('','','header_trans');
echo "<fieldset style=float:left>
		<legend>Header</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input id=notransaksi style='width:145px;' class='myinputtext' disabled/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select style=\"width:150px;\" id=kodeorg>" . $optorg . "</select>
					</td>
				
				<td>&nbsp;" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select style=\"width:150px;\" id=periode>" . $optPeriode . "</select></td>
			</tr> 
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=simpanheader()>" . $_SESSION['lang']['save'] . "</button>
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
echo"<div id=detail style=display:none>";
OPEN_BOX();
CLOSE_BOX();
echo"</div>";


echo"<div id=uploadpemel style=display:none>";
OPEN_BOX();
echo"<fieldset><legend>Form</legend><div id=viewuploadpemel style='overflow:auto;width:100%';></div></fieldset>";
CLOSE_BOX();
echo"</div>";

echo"<div id=uploadpemelmaterial style=display:none>";
OPEN_BOX();
echo"<fieldset><legend>Form</legend><div id=viewuploadpemelmaterial style='overflow:auto;width:100%';></div></fieldset>";
CLOSE_BOX();
echo"</div>";

echo close_body();
?>