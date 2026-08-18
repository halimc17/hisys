<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zPivot.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_salarylist2').'</span><br>');
?>
<script language=javascript src='js/sdm_salarylist2.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/report_pivottable.js?v=<?php echo time(); ?>'></script>
<?

$str="select DISTINCT periodegaji as prd from ".$dbname.".sdm_gaji order by periodegaji desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
}
$no=0;
$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($data as $thn => $vprd){
	$d=$thn;
	if($d!=$n){			
		$optprd.="<optgroup label='".$d."'>";
	}
	foreach($vprd as $prd){
		$optprd.="<option value=".$prd.">".$prd."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optprd.="</optgroup>";
	}
}
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

$stat=array('0'=>'NON AKTIF','1'=>'AKTIF');
$wh="";
if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
	$wh="and id in ('4')";
}
$opttipekary="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where 1=1 ".$wh." and id in (select distinct tipekaryawan as tipe from ".$dbname.".sdm_gaji_vw) order by aktif desc, no asc";
$res = fetchData($str);
foreach($res as $bar){
	$d=$bar['aktif'];
	if($d!=$n){			
		$opttipekary.="<optgroup label='".$stat[$d]."'>";
	}
    $opttipekary.="<option value='".$bar['id']."'>".$bar['tipe']."</option>";
	$n=$d;
	if($d!=$n){			
		$opttipekary.="</optgroup>";
	}
}
$arrtipe=array(
	'payroll'=>'Detail Gaji (Payroll)'
);
#$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrtipe as $key => $val){
	$opttipe.="<option value=".$key.">".$val."</option>";			
}

echo"<table border=0><td style=vertical-align:top>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td colspan=3><select id=gudang style='width:163px;'>".$optorg."</select></td><td></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['tipekaryawan'] . "</td> 
					<td>:</td>
					<td colspan=3><select id=tipekaryawan style=\"width:163px;\">".$opttipekary."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td> 
					<td>:</td>
					<td  colspan=3><select id=periode style=\"width:163px;\">".$optprd."</select></td>
					
                </tr>
				<tr hidden>
					<td>" . $_SESSION['lang']['jenis'] . "</td> 
					<td>:</td>
					<td colspan=3><select id=tipe onchange=sumber(this.value); style=\"width:163px;\">".$opttipe."</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=pivot(); class=mybutton>Preview</button>
					<a href='fileupload/Panduan_Pivot_Table.doc' download><button class=mybutton>Help</button></a>
					<button hidden onclick=\"data()\" class=mybutton>Data</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</td><td style=vertical-align:top>";
echo"<div id=info></div>";
echo"</td></table>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none>
		<button onclick=\"exportTableToExcel('pvtTable')\" class=\"dt-button buttons-print\" >Excel</button>
		<button hidden onclick='ExportPdf()' class=\"dt-button buttons-print\">PDF</button>
		<button hidden id=download onclick='pdftosave()' class=\"dt-button buttons-print\">Send</button>
		<div hidden id=namamenu>".getMenu('sdm_salarylist2','path')."</div>
	</div>";
	
echo"<div id='output' style=min-height:400px></div>";
CLOSE_BOX();
echo close_body();
?>