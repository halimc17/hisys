<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper().'Tren Produksi TBS </span>');
echo "<script language=javascript src=js/zTools.js></script>
      <script language=javascript src=js/zReport.js></script>";
require_once('lib/zSelect2.php');
echo "<script language=\"javascript\" src=\"js/zSelect2.js?ver=1\"></script>
      <script language=\"javascript\" src=\"js/Chart.js\"></script>";
?>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function batal() {
    document.getElementById('periode').value = '';
    document.getElementById('unitSelect').value = '';
    document.getElementById('unit').value = '';
    document.getElementById('intiplasma').value = '';
    document.getElementById('sumbernorma').value = 'TOPAZ'; 
    document.getElementById('printContainer').innerHTML = '';
}
function showheader(){
    if(document.getElementById('tableheader').style.display=="none"){        
        document.getElementById('tableheader').style.display="block";
        document.getElementById('showhead').innerHTML="Hide Filter";
        document.getElementById('tombolexport').style.display="none";
    }else{
        document.getElementById('tableheader').style.display="none";
        document.getElementById('tombolexport').style.display="block";
        document.getElementById('showhead').innerHTML="Show Filter";
    }   
}

function updateUnitId() {
    var select = document.getElementById('unitSelect');
    var selected = [];
    for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].selected && select.options[i].value != '') {
            selected.push(select.options[i].value);
        }
    }
    document.getElementById('unit').value = selected.join(',');
}
</script>
<?php
// --- PREPARE OPTIONS ---

// 1. Periode
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".pabrik_timbangan where tanggal!='' order by tanggal desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while($rTgl=$qTgl->fetch())
{
     $thn=explode("-", $rTgl['periode']);
   if($thn[1]=='12')
   {
   $optper.="<option value='".substr($rTgl['periode'],0,4)."'>".substr($rTgl['periode'],0,4)."</option>";
   }
   $optper.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

// 2. Unit
$optUniDt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUniDt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optUniDt.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optUniDt.="</optgroup>";
	}
}

// 3. Inti/Plasma
$intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
$intiplasma.="<option value='I'>Inti</option>";
$intiplasma.="<option value='P'>Plasma</option>";

// 4. Sumber Norma
$optNorma="<option value='TOPAZ'>Asian Agri (Topaz)</option>";
$optNorma.="<option value='SIMALUNGUN'>PPKS (Simalungun)</option>";
$optNorma.="<option value='SRIWIJAYA'>Sriwijaya SJ-5</option>";

// --- PARAMETER ARRAY ---
$arr="##periode##unit##intiplasma##sumbernorma";

?>

<div id="tableheader">
    <fieldset style="float:left;">
        <legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td><label><?php echo $_SESSION['lang']['unit']?></label></td>
                <td>:</td>
                <td><select multiple id="unitSelect" onchange="updateUnitId()" class="select2" style="width:200px;"><?php echo $optUniDt;?></select><input type="text" id="unit" style="display:none;" /></td>
            </tr>
            <tr>
                <td><label><?php echo $_SESSION['lang']['periode']?></label></td>
                <td>:</td>
                <td><select id="periode" class="select2" style="width:200px;"><?php echo $optper;?></select></td>
            </tr>
            <tr>
                <td><label><?php echo $_SESSION['lang']['intiplasma']?></label></td>
                <td>:</td>
                <td><select id="intiplasma" class="select2" style="width:200px;"><?php echo $intiplasma;?></select></td>
            </tr>
             <tr>
                <td><label>Sumber Norma</label></td>
                <td>:</td>
                <td><select id="sumbernorma" class="select2" style="width:200px;"><?php echo $optNorma;?></select></td>
            </tr>
            
            <tr>
                <td><label></label></td>
                <td></td>
                <td colspan="3" align="center">
                    <button onclick="zPreview('mr_slave_trenProduksiTbs','<?php echo $arr?>','printContainer');showheader();" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['preview']?></button>
                    <!-- Excel points to the specific Excel slave -->
                    <button onclick="zExcel(event,'mr_slave_trenProduksiTbs.php','<?php echo $arr?>')" class="mybutton" name="excel" id="excel"><?php echo $_SESSION['lang']['excel']?></button>
                    <button onclick="batal()" class="mybutton" name="batal" id="batal"><?php echo $_SESSION['lang']['cancel']?></button>
                </td>
            </tr>
        </table>
    </fieldset>
    <div style='clear:both'></div>
</div>

<?php 
CLOSE_BOX();
OPEN_BOX();
?>
<div id="tombolexport" style="display:none;">
    <table>
        <tr><td>
            <button onclick="showheader()" class="mybutton" id="showhead">Show Filter</button>
        </td></tr>
    </table>
</div> 
<?php
// Merged box
?>
<div id="printContainer" class="table-scroll" style="overflow:auto; height:73vh;">
</div>
<?php
CLOSE_BOX();
echo close_body();
?>