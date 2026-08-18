<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/sdm_5uangmakandanextrafood.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5uangmakandanextrafood').'</span>');

$optjenis=$optUnit= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optjeniscr=$optUnitcr= "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

#= option unit
$arrUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
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

$where.=" and kodeorganisasi in(".$listOrg.")";


$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 1=1 ".$where."";
$res=fetchData($str);
foreach ($res as $key => $bar){
    $optUnit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";    
    $optUnitcr.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";    
}

$namakary= "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str="select * from ".$dbname.".datakaryawan where 1=1 and (tanggalkeluar ='0000-00-00' or tanggalkeluar>= '".tanggalsystemn(tanggalnormal($_SESSION['org']['period']['start']))."') and lokasitugas in (".$listOrg.") order by namakaryawan";
$res=fetchData($str);
foreach ($res as $key => $bar){
    $namakary.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['lokasitugas']."</option>";    
}

$jenis= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$jeniscr= "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str="select * from ".$dbname.".sdm_ho_component where 1=1 and id in ('45','69')";
$res=fetchData($str);
foreach ($res as $key => $bar){
	$jenis.="<option value='".$bar['id']."'>".$bar['name']."</option>";    
	$jeniscr.="<option value='".$bar['id']."'>".$bar['name']."</option>";    
}



echo"
<br><fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
<table border=0 cellspacing=0>
	<tr>
		<td>".$_SESSION['lang']['tahun']." </td><td> : </td>
		<td><input type=text class=myinputtextnumber id=tahun value=".date('Y')."  style=width:150px maxlength=8 onkeypress='return angka_doang(event)' ></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td> : </td>
		<td><select style=width:155px id=unit>".$optUnit."</select><img id=unit onclick=z.elSearch('unit',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['jenis']."</td>
		<td> : </td>
		<td><select style=width:155px id=jenis>".$jenis."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['nama']."</td>
		<td> : </td>
		<td><select style=width:155px id=namakary>".$namakary."</select><img id=namakary onclick=z.elSearch('namakary',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['rp']." (Per Hari)</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=rpnya style=width:150px onkeypress='return angka_doang(event)' ></td>
	</tr>
  <input type=hidden id=method value='insert'>
  <tr>
  	<td><td>
  	<td><button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>

	<input type=hidden id=kompId /></td>
  </tr>
	 
</table>";

CLOSE_BOX();
OPEN_BOX();

echo "
<fieldset style=float:left><legend>Find</legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['tahun']."</td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber id=tahuncr value=".date('Y')."  style=width:50px maxlength=8 onkeypress='return angka_doang(event)' ></td>
		
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select style=width:155px id=unitcr>".$optUnitcr."</select></td>
		
		<td>".$_SESSION['lang']['nama']."</td>
		<td>:</td>
		<td><select style=width:155px id=namakarysc>".$namakary."</select><img id=namakarysc onclick=z.elSearch('namakarysc',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
		
		<td>".$_SESSION['lang']['jenis']."</td>
		<td>:</td>
		<td><select style=width:155px id=jeniscr>".$jeniscr."</select></td>
		
		<td><button class=mybutton onclick=loadData()>".$_SESSION['lang']['find']."</button></td>
		
	</tr>
</table>
</fieldset><div style=clear:both></div>
<div class='table-scroll'>
	<table class=sortable cellspacing=1 cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['tahun']."</th>
				<th align=center>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['nama']."</th>
				<th align=center>".$_SESSION['lang']['jenis']."</th>
				<th align=center>".$_SESSION['lang']['rp']."<br></th>
				<th style='text-align:center'>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
		<tfoot id='foothata'>
		</tfoot>
		</tbody>
	</table>
</div>";
CLOSE_BOX();
echo close_body();
?>