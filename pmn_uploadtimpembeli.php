<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');

echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script>function submitFile(){
    if(confirm('Are you sure..?')){
    document.getElementById('frm').submit();
    }
}

function getform()
{
	// help_slave_detailbantuan.php?index=6&modul=Pengadaan
	ev='event';
    param = 'method=detailcomment';
    title="Data Detail";
     showDialog1(title,"<iframe frameborder=0 style='width:895px;height:395px'"+
    " src='help_slave_detailbantuan.php?index=626&modul=SDM'></iframe>",'900','400',ev);	
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}


</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>

<?
$arr="##listTransaksi##pilUn_1##unitId##method";
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pmn_uploadtimpembeli').'</span>');
echo"  <fieldset><legend>Form</legend>
                     <div id=uForm>
                     	<span id=sample><table>
						<tr><td>
						Catatan :</td></tr>
						
						<tr><td><td>1. Template file upload dapat di download <a href=tool_slave_getExample.php?form=TIMBANGANPEMBELI target=frame>disini.</a></td></td></tr>
						
						<tr><td><td>2. File type hanya support CSV.</td></td></tr>
						
						</table></span><br>
                                        
							<form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>	
							<input type=hidden name=jenisdata id=jenisdata value='TIMBANGANPEMBELI'>
							<input type=hidden name=MAX_FILE_SIZE value=1024000>
							File : <input name=filex type=file id=filex size=25 class=mybutton>
							Field separated by : <select name=pemisah>
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

CLOSE_BOX();
 
echo close_body();
?>