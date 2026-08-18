<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('admin_setbot').'</span><br>');
?>
<script>
function showEdit(id,token,alamat,username){
	document.getElementById('id').value=id;	
	document.getElementById('token').value=token;	
	document.getElementById('username').value=username;	
	document.getElementById('alamat').value=alamat;	
}
function del(id){
	param  = 'proses=delete';
    param += "&id=" + id;
	if(confirm("Anda yakin ???")){		
		tujuan = 'admin_slave_setbot.php';
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
	token = document.getElementById('token').value;	
	alamat = document.getElementById('alamat').value;	
	username = document.getElementById('username').value;	
	param  = 'proses=simpan';
    param += "&id=" + id;
    param += "&token=" + token;
    param += "&alamat=" + alamat;
    param += "&username=" + username;
    tujuan = 'admin_slave_setbot.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('id').value='';	
					document.getElementById('token').value='';	
					document.getElementById('username').value='';	
					document.getElementById('alamat').value='';	
					loaddata();
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
    tujuan = 'admin_slave_setbot.php';
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
		tujuan = 'admin_slave_setbot.php';
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
                    <td>Username</td>
                    <td>:</td>
                    <td><input id=username class=myinputtext style=width:500px></td>
                </tr>
				<tr>
                    <td>Token</td>
                    <td>:</td>
                    <td><input id=token class=myinputtext style=width:500px></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td><input id=alamat class=myinputtext  style=width:500px></td>
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