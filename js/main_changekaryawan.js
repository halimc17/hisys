function getUserForActivation() {
	x = trim(document.getElementById('uname').value);
	param = 'uname=' + x;
	param += '&method=finduser';
	if (x.length > 0)
		post_response_text('main_slave_changeemployee.php', param, respog);
	else {
		alert('Please fill username');
		document.getElementById('uname').focus();
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('result').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function validat(ev) {

	key = getKey(ev);
	if (key == 13)
		getUserForActivation();
	else
		return tanpa_kutip_dan_sepasi(ev);

}

function save(user) {
	uname = document.getElementById('uname'+user).innerHTML;
	newempl = document.getElementById('newempl'+user).value;
	param = 'uname=' + uname + '&newempl=' + newempl;
	//param += '&user=' + user;
	param += '&method=changeempl';
	
	if (confirm('Are you sure ...?')) {
		post_response_text('main_slave_changeemployee.php', param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('row' + uname).style.display = 'none';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}