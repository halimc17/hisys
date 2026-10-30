/**
 * JS Laporan Summary Karyawan TK SIP DITJENBUN
 * pola: js/sdm_2summarykaryawan.js
 */

function getunit() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param = 'proses=getunit&pt=' + pt;
	tujuan = 'legal_slave_tksipditjenbun.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('unit').innerHTML = con.responseText;
					getperiode();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getperiode() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param = 'proses=getperiode&pt=' + pt + '&unit=' + unit;
	tujuan = 'legal_slave_tksipditjenbun.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('periode').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddata(tipeprint) {
	pt = document.getElementById('pt').value;
	unit = document.getElementById('unit').value;
	periode = document.getElementById('periode').value;
	status = document.getElementById('status').value;

	if (pt == '') {
		alertify.alert('Nama PT harus dipilih.');
		return false;
	}
	if (periode == '') {
		alertify.alert('Periode harus dipilih.');
		return false;
	}

	param = 'proses=' + tipeprint;
	param += '&pt=' + pt;
	param += '&unit=' + unit;
	param += '&periode=' + periode;
	param += '&status=' + status;
	tujuan = 'legal_slave_tksipditjenbun.php';

	if (tipeprint == 'excel') {
		printnopopup(tujuan + '?' + param);
	} else {
		post_response_text(tujuan, param, respog);

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						document.getElementById('printContainer0').innerHTML = con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}
