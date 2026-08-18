maxf 		= 0
sekarang 	= 1;

function gantidivisi() {
	kdOrg 	= document.getElementById('idKbn').value;

	param 	= 'kdOrg=' + kdOrg + '&proses=gantidivisi';
	tujuan 	= 'kebun_slave_buahrestan.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('divisi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}

function viewData() {
	kdOrg 	= document.getElementById('idKbn').value;
	tgl 	= document.getElementById('tglData').value;
	divisi 	= document.getElementById('divisi').value;


	if (tgl == '') {
		alert("Please Insert Date/Tanggal");
		return;
	}

	// param 	= 'kdOrg=' + kdOrg + '&tgl=' + tgl + '&intex=' + getValue('intex') + '&proses=getData';
	param 	= 'kdOrg=' + kdOrg + '&tgl=' + tgl + '&divisi=' + divisi + '&proses=getData';
	tujuan 	= 'kebun_slave_buahrestan.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					//alert(con.responseText);
					document.getElementById('result').style.display = 'block';
					document.getElementById('list_ganti').innerHTML = con.responseText;
					// document.getElementById('idKbn').disabled = true;
					//document.getElementById('tglData').disabled=true;
					//document.getElementById('dtl_pem').disabled=true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}


function cancelSave() {
	document.getElementById('list_ganti').innerHTML = '';
	document.getElementById('idKbn').disabled = false;
	document.getElementById('tglData').disabled = false;
	document.getElementById('dtl_pem').disabled = false;
	document.getElementById('divisi').value = '';
	document.getElementById('idKbn').value = '';
	document.getElementById('tglData').value = '';
	document.getElementById('result').style.display = 'none';
}
