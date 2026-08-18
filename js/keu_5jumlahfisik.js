function simpan() {
	jumlah_fisik = trim(document.getElementById('jumlah_fisik').value);
	method = document.getElementById('method').value;

	if (jumlah_fisik == '' || jumlah_fisik == 0 || jumlah_fisik == 0.00 ) {
		alertify.alert("Informasi",'Please complete the form');
		return;
	}
	param = 'jumlah_fisik=' + jumlah_fisik;
    
    if (method == 'update') {
        jumlahfisik_old = trim(document.getElementById('jumlahfisik_old').value);
        param += '&jumlah_fisik_old=' + jumlahfisik_old;
    }
    
    param += '&method=' + method;

	tujuan = 'keu_slave_5jumlahfisik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					hapus();
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function hapussch() {
	document.getElementById('banksch').value='';
	loadData();
}
function hapus() {
	document.getElementById('method').value = 'insert';
	document.getElementById('jumlah_fisik').value = '0.00';
	document.getElementById('jumlahfisik_old').value = '';
}
function loadData() {
	param = '';
	param += '&method=loadData';
	tujuan = 'keu_slave_5jumlahfisik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
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
function fillField(jumlah_fisik) {
	document.getElementById('jumlah_fisik').value=jumlah_fisik;
	document.getElementById('jumlahfisik_old').value=jumlah_fisik;
	document.getElementById('method').value = 'update';
}
