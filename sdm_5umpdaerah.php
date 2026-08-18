<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_5umpdaerah.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5umpdaerah').'</span>');

$optjenis=$optUnit= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

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
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".$listOrg.")";
$res=fetchData($str);
foreach ($res as $key => $bar){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$induk[$bar['kodeorganisasi']];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optUnit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";    
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
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
		<td><select style=width:155px id=unit>".$optUnit."</select><img id=unit onclick=z.elSearch('unit',event) class=resicon src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
	</tr><tr>
		<td>".$_SESSION['lang']['rp']."</td>
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

	<table class=sortable cellpadding=5 cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['tahun']."</th>
				<th align=center>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['rp']."<br></th>
				<th style='text-align:center'>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
		<tfoot id='footData'>
		</tfoot>
		</tbody>
	</table>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>