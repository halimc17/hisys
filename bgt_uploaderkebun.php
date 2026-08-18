<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/bgt_uploaderkebun.js'></script>
<?
 
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('ESTATE BUDGET UPLOADER').'</span>');

$frm[0]='';
$optUnit="<option value=''></option>";
$sUnit="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
        where tipe='KEBUN' and kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
        order by namaorganisasi asc";
$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
$qUnit->setFetchMode(PDO::FETCH_ASSOC);
while($rUnit=  $qUnit->fetch()){
    $optUnit.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['kodeorganisasi']."-".$rUnit['namaorganisasi']."</option>";
}
$frm[0].="<fieldset><legend>Choose data type:</legend>
    <table><tr><td>Data type</td><td>:</td><td><select  style=width:150px id=udatatype onclick=getFormUplaod(this.options[this.selectedIndex].value)>
    <option value=''>Please choose..</option>
    <option value='SDM'>SDM BUDGET</option>
    <option value='MATANDTOOL'>MATERIAL AND TOOL</option>
    <option value='VHC'>VHC ALLOCATION</option>
    <option value='KONTRAK'>CONTRACT</option>
    </select></td></tr>";

$frm[0].="</table>
   </table>
</fieldset>                              
<fieldset><legend>Form</legend>
<div id=uForm style='display:none'>
                    <span id=sample></span><br><br>
                    (File type support only CSV).
                   <form id=frm name=frm enctype=multipart/form-data method=post action=bgt_slave_uploadData.php target=frame>	
                   <input type=hidden name=jenisdata id=jenisdata value=''>
                   <input type=hidden name=MAX_FILE_SIZE value=1024000>
                   File:<input name=filex type=file id=filex size=25 class=mybutton>
                   Field separated by<select name=pemisah>
                   <option value=','>, (comma)</option>
                   <option value=';'>; (semicolon)</option>
                   <option value=':'>: (two dots)</option>
                   <option value='/'>/ (devider)</option>
                   </select>
                   <input type=button class=mybutton  value=".$_SESSION['lang']['save']." title='Submit this File' onclick=submitFile()>
               </form>

               <iframe frameborder=0 width=800px height=200px name=frame>
               </iframe>
</div>
</fieldset>";

#============================================================================================
$frm[1]='';
//if($_SESSION['empl']['bagian']=="IT"){
if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){    
    $frm[1].="<fieldset><legend>Choose data type:</legend>
                      <span>Data type:<select id=udatatype onclick=getFormUplaod(this.options[this.selectedIndex].value)>
                        <option value=''>Please choose..</option>
                        <option value='ACCBAL'>ACCBAL:Previous Accounting Balance</option>
                        <option value='JOURNAL'>JOURNAL:Journal regarding transaction history</option>
                        <option value='INV'>INV:Previuous inventory balance</option>
                        <option value='PO'>PO:Outstanding PO</option>
                        </select>
                     </fieldset>                               
                    <fieldset><legend>Form</legend>
                     <div id=uForm style='display:none'>
                     	
                        <span id=sample></span><br><br>
                        (File type support only CSV).
                       <form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>	
                       <input type=hidden name=jenisdata id=jenisdata value=''>
                       <input type=hidden name=MAX_FILE_SIZE value=1024000>
                       File:<input name=filex type=file id=filex size=25 class=mybutton>
                       Field separated by<select name=pemisah>
                       <option value=','>, (comma)</option>
                       <option value=';'>; (semicolon)</option>
                       <option value=':'>: (two dots)</option>
                       <option value='/'>/ (devider)</option>
                       </select>
                       <input type=button class=mybutton  value=".$_SESSION['lang']['save']." title='Submit this File' onclick=submitFile()>
                   </form>

                   <iframe frameborder=0 width=800px height=200px name=frame>
                   </iframe>
                     </div>
                    </fieldset>";
}
else{
   $frm[1]='Not authorized'; 
}
#============================================================================================
$hfrm[0]='Upload';
drawTab('FRM',$hfrm,$frm,200,900);
CLOSE_BOX();
echo close_body();
?>