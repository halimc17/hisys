<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('',"<span class=judul>".getMenu('sdm_uploadpotpph')."</span>");
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script>
tmblPilih='<?php echo $_SESSION['lang']['proses']?>';
canForm='<?php echo $_SESSION['lang']['done']?>';
</script>
<script language="javascript" src="js/sdm_uploadpotpph.js"></script>
<div id="action_list">
<?php

$whr=" where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $whr="where kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where char_length(kodeorganisasi)=4 and tipe<>'HOLDING')";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $whr="where kodeorganisasi in (select kodeorganisasi from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
}

$optOrg=$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sorg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi ".$whr." order by namaorganisasi asc";
$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
$qorg->setFetchMode(PDO::FETCH_ASSOC);
while($rorg=$qorg->fetch())
{   
    $optOrg.="<option value='".$rorg['kodeorganisasi']."'>".$rorg['namaorganisasi']."</option>";
}

echo"<div id=formUpload>";
echo"<fieldset style=float:left;><legend>Download Template</legend>
    <table style='width:100%;'>
        <tr>
            <td>".$_SESSION['lang']['kodeorganisasi']."</td>
            <td>:</td>
            <td>
                <select id=tmplkodeorg onchange='getperiode()'>".$optOrg."</select>
            </td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['periode']."</td>
            <td>:</td>
            <td>
            	<select id=tmplperiode >".$optperiode."</select>
            </td>
        </tr>
        <tr>
            <td colspan=6 style='text-align:center;padding-top:5px;'>";
            echo"<button tabindex=8 class=mybutton onclick=download()>Download</button>&nbsp";
            echo"<button tabindex=9 class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button>
            </td>
        </tr>
    </table>
</fieldset>"; 

echo"<fieldset style=float:left;><legend>Upload Data</legend>
    (File type support only CSV).<p>
    
    <form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>
    
        <input type=hidden name=jenisdata id=jenisdata value='sdmpotpph'>
        <input type=hidden name=MAX_FILE_SIZE value=1024000>
        File : <input name=filex type=file id=filex size=25 class=mybutton>
        
        <select name=pemisah style='display:none'>
            <option value=','>, (comma)</option>
            <option value=';'>; (semicolon)</option>
            <option value=':'>: (two dots)</option>
            <option value='/'>/ (devider)</option>
        </select>
        
        <input type=button class=mybutton  value='Upload Data' title='Submit this File' onclick=submitFile()>
        <br>
        <iframe frameborder=0 width=800px height=100px name=frame></iframe>
    </form>
</fieldset>";
 
echo"</div>";
CLOSE_BOX();
 
echo close_body();

?>