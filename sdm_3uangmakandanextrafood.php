<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript1.2 src='js/sdm_3uangmakandanextrafood.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
$_SESSION['harikary']=array();

$lstUnit=getOrgDetail(1);
$dtMul=0;
$listOrg="";
foreach($lstUnit as $row=>$isiDt){
    if(substr($row,0,5)=='Pilih'){
        continue;
    }
    if($dtMul==0){
        $listOrg="'".$row."'";
        $dtMul=1;
    }else{
        $listOrg.=",'".$row."'";
    }
}

$wh.= " and kodeorganisasi in (".$listOrg.")";
# Organisasi
$optorg = "<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$wh."";
$res=fetchData($str);
foreach($res as $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$val['kodeorganisasi']."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$val['kodeorganisasi']."'");
	$d=$induk[$val['kodeorganisasi']];
	if($d!=$n){			
		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
	
}

# Divisi
$optdiv = "<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
$res = fetchData($str);
foreach($res as $key => $val){
	$optdiv.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
}


# Periode
$optprd = "<option value=''></option>";
$str="select DISTINCT (periode) as prd from ".$dbname.".sdm_5periodegaji order by periode desc";
#and periode>='2020-07'
$res = fetchData($str);
foreach($res as $key => $val){
	$optprd.="<option value=".$val['prd'].">".$val['prd']."</option>";	
}

$optprdsch = "<option value=''></option>";
$str="select DISTINCT (periode) as prd from ".$dbname.".sdm_5periodegaji order by periode desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$optprdsch.="<option value=".$val['prd'].">".$val['prd']."</option>";	
}


$opttipex=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$opttipesch = "<option value=''></option>";
$opttipe = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select DISTINCT (tipekaryawan) as tipe from ".$dbname.".datakaryawan where lokasitugas in(".$listOrg.") and tipekaryawan in ('1','2','3') and tanggalkeluar='0000-00-00' order by tipe desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$opttipesch.="<option value=".$val['tipe'].">".$opttipex[$val['tipe']]."</option>";	
	$opttipe.="<option value=".$val['tipe'].">".$opttipex[$val['tipe']]."</option>";	
}

$optjenis= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str="select * from ".$dbname.".sdm_ho_component where 1=1 and id in ('45','69')";
$res=fetchData($str);
foreach ($res as $key => $bar){
	$optjenis.="<option value='".$bar['id']."'>".$bar['name']."</option>";    
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_3uangmakandanextrafood').'</span>','judul_header');	
# === Header dan Pencarian data ===
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
				<td>" . $_SESSION['lang']['kodeorg'] . "</td> 
				<td>:</td>
				<td><select id=kodeorgsch onchange='loaddata()' style=\"width:130px;\">".$optorg."</select></td>
				
				<td hidden>" . $_SESSION['lang']['tipekaryawan'] . "</td> 
				<td hidden>:</td>
				<td hidden><select id=tipesch onchange='loaddata()' style=\"width:130px;\">".$opttipesch."</select></td>
				
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=periodesch onchange='loaddata()' style=\"width:130px;\">".$optprdsch."</select>
				</td>
			
				
			</tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();

echo "
		<div  class='table-scroll' style=height:65vh>    
			<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:50%>
				<thead>
					<tr class=rowheader>
						<th align=center width=40px>" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center >" . $_SESSION['lang']['kodeorg'] . "</th>
						<th align=center >" . $_SESSION['lang']['periode'] . "</th>
						<th align=center >" . $_SESSION['lang']['tipekaryawan'] . "</th>
						<th align=center >" . $_SESSION['lang']['jenis'] . "</th>
						<th align=center >" . $_SESSION['lang']['rupiah'] . "</th>
						<th align=center >" . $_SESSION['lang']['updateby'] . "</th>
						<th align=center colspan='5'>" . $_SESSION['lang']['action'] . "</th>
				</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>
	 </fieldset>";
CLOSE_BOX();
echo "</div>";

# === Form header input data ===
echo "<div id=header style=display:none>";
OPEN_BOX('','','header_trans');
echo "<fieldset style=float:left;>
		<legend>Form</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>&nbsp;" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select style=\"width:150px;\" id=kodeorg>".$optorg."</select></td>
				
				<td>&nbsp;".$_SESSION['lang']['tipekaryawan'] . "</td> 
				<td>:</td>
				<td><select style=\"width:150px;\" id=tipekar>".$opttipe."</select></td>
				
				<td>&nbsp;".$_SESSION['lang']['jenis'] . "</td> 
				<td>:</td>
				<td><select style=\"width:150px;\" id=jenis>".$optjenis."</select></td>
				
				<td>&nbsp;".$_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select style=\"width:150px;\" id=periode>".$optprd."</select></td>
			</tr>	
			<tr>
				<td colspan=2></td>
				<td colspan=20>
					<button id=tomboldetail class=mybutton onclick=simpanheader()>" . $_SESSION['lang']['preview'] . "</button>
					<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
					<a href='fileupload/ekstrafooding.pdf' download>
					<button class=mybutton>Info</button>
					</a>
				</td>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=mode value='baru'>
			</tr>
		</table>
	</fieldset>";
echo"<div id=detail style=display:none;>";
echo"</div>";
CLOSE_BOX();
echo"</div>";

# === Form Detail Input Data ===
#OPEN_BOX();
#CLOSE_BOX();
echo close_body();
?>