<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/kebun_2pusinganpanenv2.js?v=<?php echo time(); ?>'></script>

<?php

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2pusinganpanenv2').'</span><br>');

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

## GET PERIODE
$optPeriode="";
$str="select DATE_FORMAT(tanggal, '%Y-%m')  as periode  from ".$dbname.".kebun_pusingan group by periode order by periode desc";
$res=fetchdata($str);
foreach($res as $val){
	$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}

## FILTER REPORT ##
echo"
<fieldset style=float:left>
	<table cellspacing=1 cellpadding=2 height =50px>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='kdorg' style='width:246px'>".$optUnit."</select>
			</td>
		</tr>
        <td>" . $_SESSION['lang']['tanggal'] . "</td>
            <td>:</td>
            <td><input type='text' style='width:107px' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10'  readonly>
            s/d
            <input type='text' style='width:107px' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10'  readonly>
        </td>
		<tr>
			<td colspan=2></td>
			<td>
                <button onclick=\"preview('html',event)\" class='mybutton'>".$_SESSION['lang']['preview']."</button>
                <button onclick=\"preview('excel',event)\" class='mybutton'>".$_SESSION['lang']['excel']."</button>
                <button onclick=update() class=mybutton name=preview id=update>Update</button>
            </td>
		</tr>
	</table>
</fieldset>

<table class=sortable cellpadding=5 cellspacing=1 border=0>
	<thead>
        <tr>
            <th colspan=2>Jika data yang di tampilkan belum / tidak update silahkan click tombol Update dan tunggu sampai proses selesai kemudian click tombol Preview</th>
        </tr>
		<tr>
			<th>".$_SESSION['lang']['kode']."</th>
			<th>".$_SESSION['lang']['keterangan']."</th>
		<tr>

	</thead><tbody>";
    echo"<tr class=rowcontent>";
        echo "<td align =center>J</td>";
        echo "<td align =center>Janjang Panen</td>";
    echo"</tr>";
    echo"<tr class=rowcontent>";
        echo "<td align =center>h</td>";
        echo "<td align =center>HA Panen</td>";
    echo"</tr>";
echo"</tbody></table>";




CLOSE_BOX();

OPEN_BOX();
echo"<div  class='table-scroll' style='height:500px;overflow:auto;' id=printContainer></div>";
CLOSE_BOX();
echo close_body();
?>