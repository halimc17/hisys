<?//@Copy nangkoelframework

require_once('master_validation.php');
include('lib/nangkoelib.php');

echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script>

var currentStatus = 'preview';

// Perbaikan fungsi: menggunakan else if dan menangkap parameter event
function updateTemplateLink(event) {
    var tipe = document.getElementById('tipe_dokumen').value;
    var linkElement = document.getElementById('downloadLink');

    if(tipe === '') {
        alert('Harap pilih jenis timbangan anda sebelum mendownload template!');
        // Menghentikan aksi default (download) jika tipe kosong
        if(event) event.preventDefault(); 
        return false;
    } else if(tipe === 'JUAL') { 
        linkElement.href = 'tool_slave_getExample.php?form=TIMBANGANJUAL2';
    } else {
        linkElement.href = 'tool_slave_getExample.php?form=TIMBANGANPEMBELI2';
    }
}

function submitFile() {
    var tipe = document.getElementById('tipe_dokumen').value;
    if(tipe == '') {
        alert('Harap pilih jenis timbangan anda!');
        return; 
    }

    if(document.getElementById('filex').value == '') {
        alert('Silahkan pilih file terlebih dahulu');
        return;
    }

    if(currentStatus === 'preview') {
        document.getElementById('frm').submit();
    } else {
        saveData();
    }
}

function toggleToSave() {
    currentStatus = 'save';
    var btn = document.getElementById('btnAction');
    if(btn) {
        btn.value = 'Simpan';
        btn.style.backgroundColor = 'green';
        btn.style.color = 'white';
    }
}

function toggleToPreview() {
    currentStatus = 'preview';
    var btn = document.getElementById('btnAction');
    if(btn) {
        btn.value = 'Preview Data';
        btn.style.backgroundColor = '';
        btn.style.color = '';
    }
}

function saveData() {
    if(confirm('Seluruh data sudah valid (OK). Simpan ke database sekarang?')) {
        var tipe = document.getElementById('tipe_dokumen').value;
        var param = 'jenisdata=TIMBANGANPEMBELI2&action=save&tipe_dokumen=' + tipe; 
        
        function respon() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    var res = con.responseText;
                
                    if(res.indexOf("Done") != -1) {
                        alert('Data Berhasil Disimpan');
                        window.location.reload(); 
                    } else {
                        alert('Gagal Simpan: ' + res);
                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
        
        busy_on();
        post_response_text('tool_slave_uploadData.php', param, respon);
    }
}

</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>

<?
$arr="##listTransaksi##pilUn_1##unitId##method";
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pmn_uploadtimbanganbeli').'</span>');
echo"  <fieldset><legend>Form</legend>
             <div id=uForm>
                <span id=sample><table>
                <tr><td colspan=2>Catatan :</td></tr>
                
                <tr><td>1.</td><td>Tentukan terlebih dahulu jenis dokumen upload.</td></tr>
                
                <tr><td>2.</td><td>Template file upload dapat di download <a id='downloadLink' href='#' onclick='updateTemplateLink(event)' target=frame>disini.</a></td></tr>
                
                <tr><td>3.</td><td>File type hanya support CSVS.</td></tr>
                </table></span><br>
                                        
                <form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>    
                    <input type=hidden name=jenisdata id=jenisdata value='TIMBANGANPEMBELI2'>
                    <input type=hidden name=MAX_FILE_SIZE value=1024000>
                    
                    Jenis Timbangan : 
                    <select name=tipe_dokumen id=tipe_dokumen  style='width:150px;' onchange='updateTemplateLink(event)'>
                        <option value=''>Pilih Data</option>
                        <option value='JUAL'>PENJUAL</option>
                        <option value='BELI'>PEMBELI</option>
                    </select>

                    File : <input name=filex type=file id=filex size=25 class=mybutton>
                    
                    Field separated by : <select name=pemisah>
                        <option value=','>, (comma)</option>
                        <option value=';'>; (semicolon)</option>
                        <option value=':'>: (two dots)</option>
                        <option value='/'>/ (devider)</option>
                    </select>
                    
                    <input type='button' id='btnAction' class='mybutton' value='Preview Data' onclick='submitFile()'>
                </form>

                <iframe frameborder=0 width=100% height=720x name=frame>";
                echo open_body();
                echo "</iframe>
             </div>
        </fieldset>";

CLOSE_BOX();
echo close_body();
?>