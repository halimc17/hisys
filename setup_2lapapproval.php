<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
OPEN_BOX('','<span class=judul>'.getMenu('setup_2lapapproval').'</span><br>');
?>
<script language=javascript src='js/setup_2lapapproval.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?

$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(1) as $key => $val){
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


$opttipekary="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".setup_jenisapproval  order by nama asc";
$res = fetchData($str);
foreach($res as $bar){
    $opttipekary.="<option value='".$bar['jenis']."'>".$bar['jenis']." - ".$bar['nama']."</option>";
}

$val="##kodeorg##jenis";
echo"<table border=0><td style=vertical-align:top>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['kodeorg']."</td>
					<td>:</td>
					<td colspan=3><select class=select2 id=kodeorg style='width:163px;'>".$optorg."</select></td><td></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['jenis'] . "</td> 
					<td>:</td>
					<td colspan=3><select class=select2 id=jenis style=\"width:163px;\">".$opttipekary."</select></td>
                </tr>
				
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    
					<button onclick=\"postPreview('".$val."')\" class=mybutton>Preview</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</td><td style=vertical-align:top>";
echo"<div id=info></div>";
echo"</td></table>";

CLOSE_BOX();
OPEN_BOX();
// echo"<div id=tombolexport style=display:none>
		// <button onclick=\"exportTableToExcel('pvtTable')\" class=\"dt-button buttons-print\" >Excel</button>
		// <button onclick='ExportPdf()' class=\"dt-button buttons-print\">PDF</button>
		// <button id=download onclick='pdftosave()' class=\"dt-button buttons-print\">Send</button>
		
	// </div>";
	
echo"<div id='output' style=min-height:400px></div>";
CLOSE_BOX();
echo close_body();
?>