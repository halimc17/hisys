<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('admin_runningtext').'</span><br>');
?>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});

$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
	$(this).closest(".select2-container").siblings('select:enabled').select2('open');
});
function showEdit(id,token,alamat,username){
	document.getElementById('id').value=id;	
	document.getElementById('text').value=token;	
	document.getElementById('status').value=username;	
	document.getElementById('lokasi').value=alamat;	
	
	setValue2('status',username);
	setValue2('lokasi',alamat);
}
function del(id){
	param  = 'proses=delete';
    param += "&id=" + id;
	if(confirm("Anda yakin ???")){		
		tujuan = 'admin_slave_runningtext.php';
		post_response_text(tujuan, param, respog);
	}
	

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
	id = document.getElementById('id').value;	
	text = document.getElementById('text').value;	
	status = document.getElementById('status').value;	
	lokasi = document.getElementById('lokasi').value;	
	param  = 'proses=simpan';
    param += "&id=" + id;
    param += "&text=" + text;
    param += "&status=" + status;
    param += "&lokasi=" + lokasi;
    tujuan = 'admin_slave_runningtext.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('id').value='';	
					loaddata();
					location.reload();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loaddata(){
	param  = 'proses=loaddata';
    tujuan = 'admin_slave_runningtext.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('printContainer').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getWebhook(token,alamat,action){
	param  = 'proses=getWebhook';
    param += "&token=" + token;
    param += "&alamat=" + alamat;
    param += "&action=" + action;
	if(confirm("Anda yakin ???")){		
		tujuan = 'admin_slave_runningtext.php';
		post_response_text(tujuan, param, respog);
	}
	

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
</script>
<script language=javascript src='js/zReport.js'></script>
<?
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
                    <td>Posisi / Lokasi</td>
                    <td>:</td>
                    <td colspan=2><select class=select2 id=lokasi >
							<option value='L'>Luar (Form Loggin)</option>
							<option value='D'>Dalam (Setelah Loggin)</option>
						</select>
					</td>
                </tr>
				<tr>
                    <td>Status</td>
                    <td>:</td>
                    <td colspan=2><select class=select2 id=status >
							<option value='1'>Aktif</option>
							<option value='0'>Non Aktif</option>
						</select>
					</td>
                </tr>
				<tr>
                    <td>Text</td>
                    <td>:</td>
                    <td><input id=text class=myinputtext style=width:500px></td>
                </tr>
                
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
					<input hidden id=id>
                    <button onclick=simpan(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['save'] . "</button>
                    </td>
                </tr>
                
				
            </table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"
	<div id='printContainer'><script>loaddata();</script></div>
";
CLOSE_BOX();
echo close_body();
?>