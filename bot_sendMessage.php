<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('bot_sendMessage').'</span><br>');
?>
<script language=JavaScript1.2 src=js/generic.js?ver=1.4></script>
<script>
	function popupkirim(){
		param  = '';
		param += '&method=popupkirim';
		tujuan = 'bot_slave_sendMessage.php';
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						alertify.popup2("Kepada",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('60%','70%'); 
						listcari();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
	function listcari(){
		nama       = document.getElementById('nama').value;
		lokasi     = document.getElementById('lokasi').value;
		jabatan    = document.getElementById('jabatan').value;
		kepada    = document.getElementById('kepada').value;
		
		param  = '';
		param += '&nama=' + nama;
		param += '&lokasi=' + lokasi;
		param += '&jabatan=' + jabatan;
		param += '&kepada=' + kepada;
		param += '&method=listcari';
		
		tujuan = 'bot_slave_sendMessage.php';
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						document.getElementById("listcari").innerHTML=con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
	function adddata(){
		i = document.getElementsByName("mail[]");
		r = document.getElementsByName("nama[]");
		e = document.getElementsByName("check[]");
		data=dtnm=""; jlh=0;
		for(n=0;n<e.length;n++){
			if(e[n].checked==true){
				data+=i[n].innerHTML+",";
				dtnm+=r[n].innerHTML+",";
				jlh=jlh+1;
			}
		}
		
		document.getElementById('kepada').value = data.substr(0,data.length-1);
		document.getElementById('namatel').value = dtnm.substr(0,dtnm.length-1);
		document.getElementById('jlhkirim').innerHTML = jlh;
		alertify.popup2().destroy();
	}
	
	function clickall(){
		e = document.getElementsByName("check[]");
		h = document.getElementById('checkall');
		for(i=0;i<e.length;i++){
			if(e[i].disabled==false){			
				if(h.checked==true){
					e[i].checked=true;
				}else{
					e[i].checked=false;
				}
			}
		}
	}
	
	function kirimkan(){
		var kepada = document.getElementById('kepada').value;
		var subject= document.getElementById('subject').value;
		var message= document.getElementById('message').value;
		validate([
			["namatel","Kepada tidak boleh kosong."],
			["subject","Subject tidak boleh kosong."],
			["message","Message tidak boleh kosong."]
		]);
		
		var formdata = new FormData();
		
		var totalfiles = document.getElementById('files').files.length;
		if(totalfiles>5){
			alertify.alert("File terlalu banyak, maksimal hanya 5 file."); return;
		}
		
		for (var i = 0; i < totalfiles; i++) {
			formdata.append("file[]", document.getElementById('files').files[i]);
		}
		cektext(subject);
		cektext(message);
		
		formdata.append("fileupload", getValue('files'));
		formdata.append("kepada", kepada);	
		formdata.append("subject", subject);	
		formdata.append("message", message);	
		
		document.getElementById("preview").disabled=true;
		document.getElementById("namatel").disabled=true;
		document.getElementById("subject").disabled=true;
		document.getElementById("message").disabled=true;
		document.getElementById("preview").style.display="none";
		busy_on;
		var con = createXMLHttpRequest();
		con.open("POST", "bot_slave_sendMessage.php?method=kirimkan", true);
		con.onreadystatechange = eval(respon);
		con.send(formdata);
		function respon() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						alertify.alert("Pesan sudah dikirimkan.");
						document.getElementById("preview").style.display="";
						document.getElementById("preview").disabled=false;
						document.getElementById("namatel").disabled=false;
						document.getElementById("subject").disabled=false;
						document.getElementById("message").disabled=false;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
	
	function cektext(isi){
		txt = isi.toUpperCase();
		if (txt.lastIndexOf('<') > -1 || txt.lastIndexOf('>') > -1){
			alertify.alert("Info","Mohon tidak menggunakan symbol < atau >");
			throw Error('Stop!');
		}
	}
</script>
<?
echo"<center><fieldset style=align:center>
        
		<table border=0 style=width:100%><td align=center>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>To / Kepada : <label id=jlhkirim style=color:blue;></label></td>
                </tr>
				<tr>
                    <td><input id=kepada hidden placeholder=required class=myinputtext style=width:700px;height:30px></td>
                </tr>
				<tr>
					<td><input id=namatel placeholder=required onclick=popupkirim(); readonly class=myinputtext style=width:700px;height:30px></td>
                </tr>
				<tr>
                    <td>Subject / Subyek :</td>
                </tr>
				<tr>
                    <td><input id=subject placeholder=required class=myinputtext style=width:700px;height:30px></td>
                </tr>
				<tr>
                    <td>Message / Pesan :</td>
                </tr>
				<tr>
                    <td><textarea rows=20 placeholder=required maxlength=2064 id=message type='text' onkeypress='return tanpa_kutip(event)' style='width:680px;'></textarea></td>
                </tr>
				<tr>
                    <td>Attachment :</td>
                </tr>
				<tr>
                    <td><input id=files name=files[] type=file multiple></td>
                </tr>
				<tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align=center>
						<button onclick=kirimkan(); style=width:700px;height:30px class=mybutton name=preview id=preview>Send</button>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
				
            </table>
		</td></table>
</fieldset>
</center>";

CLOSE_BOX();
echo close_body();
?>