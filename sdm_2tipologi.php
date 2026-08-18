<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/sdm_2tipologi.js?v=<?php echo time(); ?>'></script>
<?


$optlok = $optjab = $optnilai = $opttahun = $optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdept = "<option value=''>&nbsp;</option>";

$str = "SELECT * FROM ".$dbname.".sdm_5departemen";
$res = fetchdata($str);
foreach($res as $val){
	$optdept .= "<option value='".$val['kode']."'>".$val['nama']."</option>";
}

$str = "SELECT * FROM ".$dbname.".sdm_5jabatan";
$res = fetchdata($str);
foreach($res as $val){
	$optjab .= "<option value='".$val['kodejabatan']."'>".$val['namajabatan']."</option>";
}

$str = "SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res = fetchdata($str);
foreach($res as $val){
	$optlok.= "<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
}


$str = "SELECT DISTINCT left(periode,4) as tahun FROM ".$dbname.".setup_periodeakuntansi ORDER BY periode DESC";
$res = fetchdata($str);
foreach($res as $val){
	$opttahun .= "<option value='".$val['tahun']."'>".$val['tahun']."</option>";
}

$whereKary.= " and tipekaryawan in ('1','0')";
// $whereKary.= " and lokasitugas in (".getOrgDetail(2).")";

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$wh = "";
	$whereKary.=" and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$wh.")";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$wh = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
	$whereKary.=" and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$wh.")";
} else {
	$whereKary.= " AND karyawanid='".$_SESSION['standard']['userid']."'";
}

$str = "SELECT * FROM ".$dbname.".datakaryawan where 1=1 ".$whereKary." ORDER BY lokasitugas, tipekaryawan, namakaryawan ASC";
$res = fetchdata($str);
foreach($res as $val){
	$d=$val['lokasitugas'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optkar.="<optgroup label='".$nmorg[$d]."'>";
	}
	$optkar .= "<option value='".$val['karyawanid']."'>".$val['nik']." - ".$val['namakaryawan']."</option>";
	$n=$d;
	if($d!=$n){
		$optkar.="</optgroup>";
	}
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_2tipologi').'</span>');

echo "<table>
   		<tr valign=middle>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=newdata()>
	  			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
	  		</td>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
	  			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
	  		</td>
	 		<td>

			<fieldset>
 			<legend id=legend>Find</legend>
	 			<table>
	 				<tr>
						<td>".$_SESSION['lang']['nama']."</td>
					  	<td>:</td>
					  	<td colspan=4><input class=myinputtext id=scnama style='width:150px;'></td>

	 					<td>".$_SESSION['lang']['departemen']."</td>
	 					<td>:</td>
					  	<td>
					  		<select id='scdept' class='select2' style='width:150px;'>
					  			".$optdept."
					  		</select>&nbsp;
					  	</td>

						<td hidden>".$_SESSION['lang']['tahun']." Penilaian</td>
					  	<td hidden>:</td>
					  	<td hidden>
					  		<select id='scthn' class='select2' style='width:100px;'>
					  			".$opttahun."
					  		</select>&nbsp;
					  	</td>
					</tr>
					<tr>
						<td></td><td></td>
				  		<td>
				  			<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
				  		</td>
					</tr>
					
				</table>
			</fieldset>

			</td>
		</tr>
	 </table>"; 

CLOSE_BOX();

echo "<div id=entry style=display:none>";
OPEN_BOX();

echo "<table style=margin-top:10px>";
	echo "<tr>";
		echo "<td valign=top>";
			echo "<fieldset><legend>Form</legend>";
			echo "<table border=0>";
			echo "<tr>
					<td>".$_SESSION['lang']['nama']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='nama' class='select2' style='width:150px;' onchange=\"getDept(this.value, 'dept');\">
				  			".$optkar."
				  		</select>&nbsp;
				  	</td>
					
					<td>".$_SESSION['lang']['jabatan']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='jabatan' class='select2' style='width:150px;' disabled>".$optjab."</select>&nbsp;
				  	</td>
				</tr>";
			echo "<tr>
					<td>".$_SESSION['lang']['lokasitugas']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='lokasitugas' class='select2' style='width:150px;' disabled>".$optlok."</select>&nbsp;
				  	</td>
					
					
					<td>".$_SESSION['lang']['departemen']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='dept' class='select2' style='width:150px;' disabled>".$optdept."</select>&nbsp;
				  	</td>
				</tr>
				<tr>
					<td>Tanggal</td>
				  	<td>:</td>
				  	<td><input type=text id=tglnilai class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:87px; value=\"".date("d-m-Y")."\">&nbsp;</td>
				</tr>";
			// echo "<tr>
					// <td valign=top>Kekuatan</td>
				  	// <td valign=top>:</td>
				  	// <td>
				  		// <textarea id=kekuatan rows=6 cols=30></textarea>&nbsp;
				  	// </td>

					// <td valign=top>Kelemahan</td>
				  	// <td valign=top>:</td>
				  	// <td colspan=4>
				  		// <textarea id=kelemahan rows=6 cols=32></textarea>&nbsp;
				  	// </td>
				// </tr>";
			echo "<tr>
					<td colspan=2></td>
					<td  colspan=20>
						<input type=hidden id=method value=insert>
						<input type=hidden id=id value=''>
						<button class=mybutton onclick=\"simpan();\">".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=\"reset();\">".$_SESSION['lang']['cancel']."</button>
						<!--<button class=mybutton onclick=\"loaddatadetail();\">Refresh</button>-->
					</td>
				</tr>";
			echo "</table>";
			echo "</fieldset>";
		echo "</td>";
	echo "</tr>";
echo "</table>";

CLOSE_BOX();
echo "</div>";

echo"<div id=listkriteria style=display:none>";
OPEN_BOX('','<span class=judul></span>');

echo "<div id=container class=freezetbl></div>";
// echo"<br>";

CLOSE_BOX();
echo"</div>";

#= buat data tersimpan
echo"<div id=loadpreview>";
OPEN_BOX('','<span class=judul></span>');

echo "<div id=listdata class='table-scroll'  style=height:65vh><script>loaddata(0);</script></div>";
echo"<br>";

CLOSE_BOX();
echo"</div>";
#= tutup data tersimpan

echo close_body();
?>