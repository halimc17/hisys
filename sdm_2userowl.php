<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
function popup(username,jenis){
	param = 'username=' + username+'&proses='+jenis;
	// tujuan = 'sdm_slave_2userowlpopup.php' + "?" + param;
	// width = '1100';
	// height = '350';
	// ev = 'event';
	// content = "<fieldset style='height:96%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
	// showDialog1(jenis, content, width, height, ev);
	
	alertify.popup("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_2userowlpopup.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function setMapUserMenu(uname) {
    pos = getMouseP('event');
    param = 'username=' + uname;
    param += '&proses=role';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
                    // //alert(con.responseText);
                    // document.getElementById('contentmenu').innerHTML = con.responseText;
                    // document.getElementById('ctrmenu').style.display = '';
                    // document.getElementById('ctrmenu').style.top = pos[1] + 'px';
                    // document.getElementById('ctrmenu').style.left = pos[0] + 'px';
                    //rowobj.style.backgroundColor = '#E8F2FE'; //class standardrow color
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('sdm_slave_2userowlpopup.php', param, respog);
}
</script>
<?php
$lksiTugas = substr($_SESSION['empl']['lokasitugas'], 0, 4);
$optOrg = "";
$optOrg.="<option value=''>".$_SESSION['lang']['all']."</option>";
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    $sOrg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
			  and length(kodeorganisasi)=4 order by namaorganisasi";
} else {
    $sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "' order by kodeorganisasi asc";	
}

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['namaorganisasi'] . "</option>";
}
$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(1) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

$optStat="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStat.="<option value='1'>Online</option>";
$optStat.="<option value='0'>Offline</option>";

$arr = "##kodeorg##status";
?>

<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>

<link rel=stylesheet type='text/css' href='style/zTable.css'>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2userowl').'</span><br>');
?>
<div>
    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td><label><?php echo $_SESSION['lang']['unit'] ?></label></td><td>:</td>
                <td><select class='select2' id="kodeorg" name="kodeorg">
                <?php echo $optOrg ?></select></td>
            </tr>
			<tr>
                <td><label><?php echo $_SESSION['lang']['status'] ?></label></td><td>:</td>
                <td><select class='select2' id="status" name="status">
                <?php echo $optStat ?></select></td>
            </tr>
            <tr><td><td><td>
                    <button onclick="zPreview('sdm_slave_2userowl', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
                    <button onclick="zExcel(event, 'sdm_slave_2userowl.php', '<?php echo $arr ?>')" class="mybutton" name="excel" id="excel">Excel</button>
                </td></tr>
        </table>
    </fieldset>

<?php
CLOSE_BOX();
OPEN_BOX();
?>
	<div class='table-scroll' id='printContainer' style='overflow:auto;'></div>
<?php
CLOSE_BOX();
echo close_body();
?>