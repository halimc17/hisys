/**
 * JS Laporan Produksi Kebun SIP DITJENBUN
 * pola: js/legal_tksipditjenbun.js
 */

function loaddata(tipeprint) {
	periode = document.getElementById('periode').value;
	unit = document.getElementById('unit').value;
	intiplasma = document.getElementById('intiplasma').value;
	jenis = document.getElementById('jenis').value;

	if (periode == '') {
		alertify.alert('Periode harus dipilih.');
		return false;
	}
	if (jenis == '') {
		alertify.alert('Jenis produksi harus dipilih.');
		return false;
	}

	param = 'proses=' + tipeprint;
	param += '&periode=' + periode;
	param += '&unit=' + unit;
	param += '&intiplasma=' + intiplasma;
	param += '&jenis=' + jenis;
	tujuan = 'legal_slave_2lpsipditjenbun.php';

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

function batal() {
	document.getElementById('periode').value = '';
	document.getElementById('unit').value = '';
	document.getElementById('intiplasma').value = '';
	document.getElementById('jenis').value = '';
	document.getElementById('printContainer0').innerHTML = '';
}
