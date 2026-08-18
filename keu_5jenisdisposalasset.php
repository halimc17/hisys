<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_5jenisdisposalasset.js'></script>

<?php

$arrstatus=array('1' => 'Disposal','2' => 'Write-off');
$optJenis.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrJenis=getEnum($dbname,'keu_5jenisdisposalasset','jenis');
foreach($arrJenis as $kei=>$fal)
{
    $optJenis.="<option value='".$kei."'>".$arrstatus[$fal]."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_5jenisdisposalasset').'</span>');

echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['jenis']."</td> 
                    <td>:</td>
                    <td><select id=jenis style=width:200px; >".$optJenis."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['keterangan']."</td> 
                    <td>:</td>
                    <td><textarea id=ket onkeypress=return_tanpa_kutip(event); class=myinputtext style='width:200px;height:80px' ></textarea></td>
                </tr>
                <tr><td colspan=2></td>
                    <td colspan=3>
                        <button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=cancel()>Hapus</button>
                        <input type=hidden id=method value='insert'>
                        <input type=hidden id=id value=''>
                    </td>
                </tr>
        	</table></fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>