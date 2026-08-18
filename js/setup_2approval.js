function loaddata() {
	crjenispersetujuan = document.getElementById('crjenispersetujuan').options[document.getElementById('crjenispersetujuan').selectedIndex].value;
	param = 'method=loaddata&crjenispersetujuan=' + crjenispersetujuan;
	tujuan = 'log_slave_approval.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}