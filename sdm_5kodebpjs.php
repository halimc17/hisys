<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_5kodebpjs.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5kodebpjs').'</span>');

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
    $optUnit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";    
}
echo"
<br><fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
<table border=0 cellspacing=0>
	<tr>
		<td>".$_SESSION['lang']['kodekelompok']." </td><td> : </td>
		<td><input type=text class=myinputtextnumber id=kodekelompok  style=width:150px maxlength=2 onkeypress='return angka_doang(event)' >
		<input type=hidden class=myinputtextnumber id=kodekelompokOld /></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['keterangan']."</td>
		<td> : </td>
		<td><input type=text class=myinputtext id=keterangan style=width:150px onkeypress='return tanpa_kutip(event)' ></td>
	</tr><tr>
		<td>".$_SESSION['lang']['status']."</td>
		<td> : </td>
		<td><input type=checkbox  id=status></td>
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
<fieldset>
	<legend><b>".$_SESSION['lang']['list']."</legend>
	<table class=sortable cellspacing=1 cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['kodekelompok']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['status']."<br></td>
				<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
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