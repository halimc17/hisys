function batal() {
	document.getElementById('tipe').value = '';
	document.getElementById('tipe').disabled = false;
	document.getElementById('ptoptinduk').value = '';
	document.getElementById('ptoptinduk').hidden = false;
	document.getElementById('ptfreeinduk').value = '';
	document.getElementById('ptfreeinduk').hidden = true;
	document.getElementById('ptfreeinduk').disabled = false;
	document.getElementById('namaptfreeinduk').value = '';
	document.getElementById('namaptfreeinduk').hidden = true;
	document.getElementById('namaptfreeinduk').disabled = false;
	document.getElementById('ptopt').value = '';
	document.getElementById('ptopt').hidden = false;
	document.getElementById('ptfree').value = '';
	document.getElementById('ptfree').hidden = true;
	document.getElementById('ptfree').disabled = false;
	document.getElementById('namaptfree').value = '';
	document.getElementById('namaptfree').hidden = true;
	document.getElementById('namaptfree').disabled = false;
	document.getElementById('noakun').value = '';
	document.getElementById('find_tipe').value = '';
	document.getElementById('find_ptinduk').value = '';
	document.getElementById('find_pt').value = '';
	document.getElementById('method').value = 'insert';
}
function batalcari() {
	document.getElementById('find_tipe').value = '';
	document.getElementById('find_ptinduk').value = '';
	document.getElementById('find_pt').value = '';
	loaddata();
}
function loaddata(num) {
	find_tipe = document.getElementById('find_tipe').value;
	find_ptinduk = document.getElementById('find_ptinduk').value;
	find_pt = document.getElementById('find_pt').value;
	param = 'method=loaddata';
	param += '&page=' + num + '&find_tipe=' + find_tipe + '&find_ptinduk=' + find_ptinduk + '&find_pt=' + find_pt;
	tujuan = 'keu_slave_5organisasi.php';
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
function simpan() {
	tipe = document.getElementById('tipe').options[document.getElementById('tipe').selectedIndex].value;
	ptoptinduk = document.getElementById('ptoptinduk').options[document.getElementById('ptoptinduk').selectedIndex].value;
	ptfreeinduk = document.getElementById('ptfreeinduk').value;
	namaptfreeinduk = document.getElementById('namaptfreeinduk').value;
	ptopt = document.getElementById('ptopt').options[document.getElementById('ptopt').selectedIndex].value;
	ptfree = document.getElementById('ptfree').value;
	namaptfree = document.getElementById('namaptfree').value;
	noakun = document.getElementById('noakun').options[document.getElementById('noakun').selectedIndex].value;
	method = document.getElementById('method').value;
	param = 'tipe=' + tipe + '&ptoptinduk=' + ptoptinduk + '&ptfreeinduk=' + ptfreeinduk + '&ptopt=' + ptopt + '&ptfree=' + ptfree + '&noakun=' + noakun + '&method=' + method;
	param+= '&namaptfreeinduk='+namaptfreeinduk+'&namaptfree='+namaptfree;
	tujuan = 'keu_slave_5organisasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batal();
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function edit(tipe, ptinduk, pt, noakun) {
	document.getElementById('tipe').value = tipe;
	document.getElementById('tipe').disabled = true;
	document.getElementById('noakun').value = noakun;
	if (tipe == 'EKSTERNAL') {
		document.getElementById('ptfreeinduk').value = ptinduk;
		document.getElementById('ptfree').value = pt;
		document.getElementById('ptfree').disabled = true;
		document.getElementById('ptoptinduk').hidden = true;
		document.getElementById('ptoptinduk').value = '';
		document.getElementById('ptfreeinduk').hidden = false;
		document.getElementById('ptopt').hidden = true;
		document.getElementById('ptopt').value = '';
		document.getElementById('ptfree').hidden = false;
	} else {
		document.getElementById('ptoptinduk').value = ptinduk;
		document.getElementById('ptopt').value = pt;
		document.getElementById('ptopt').disabled = true;
		document.getElementById('ptoptinduk').hidden = false;
		document.getElementById('ptfreeinduk').hidden = true;
		document.getElementById('ptfreeinduk').value = '';
		document.getElementById('ptopt').hidden = false;
		document.getElementById('ptfree').hidden = true;
		document.getElementById('ptfree').value = '';
	}
	document.getElementById('method').value = 'update';
}
function del(kodeorg,induk,indukunit,unit) {
	param = 'method=delete';
	param += '&pt=' + kodeorg;
	param += '&unit=' + unit;
	param += '&unitinduk=' + indukunit;
	param += '&ptinduk=' + induk;
	tujuan = 'keu_slave_5organisasi.php';
	if (confirm(' Anda yakin ???')) {
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
function getPT() {
	tipe = document.getElementById('tipe').options[document.getElementById('tipe').selectedIndex].value;
	if (tipe == 'EKSTERNAL') {
		document.getElementById('ptoptinduk').hidden = true;
		document.getElementById('ptoptinduk').value = '';
		document.getElementById('ptfreeinduk').hidden = false;
		document.getElementById('namaptfreeinduk').hidden = false;
		document.getElementById('ptopt').hidden = true;
		document.getElementById('ptopt').value = '';
		document.getElementById('ptfree').hidden = false;
		document.getElementById('namaptfree').hidden = false;
	} else if (tipe == 'INTERNAL') {
		document.getElementById('ptoptinduk').hidden = false;
		document.getElementById('ptfreeinduk').hidden = true;
		document.getElementById('ptfreeinduk').value = '';
		document.getElementById('namaptfreeinduk').hidden = true;
		document.getElementById('namaptfreeinduk').value = '';
		document.getElementById('ptopt').hidden = false;
		document.getElementById('ptfree').hidden = true;
		document.getElementById('ptfree').value = '';
		document.getElementById('namaptfree').hidden = true;
		document.getElementById('namaptfree').value = '';
	} else {
		document.getElementById('ptoptinduk').hidden = false;
		document.getElementById('ptfreeinduk').hidden = true;
		document.getElementById('ptfreeinduk').value = '';
		document.getElementById('namaptfreeinduk').hidden = true;
		document.getElementById('namaptfreeinduk').value = '';
		document.getElementById('ptopt').hidden = false;
		document.getElementById('ptfree').hidden = true;
		document.getElementById('ptfree').value = '';
		document.getElementById('namaptfree').hidden = true;
		document.getElementById('namaptfree').value = '';
	}
}
function getkodeorg() {
	ptoptinduk = document.getElementById('ptoptinduk').options[document.getElementById('ptoptinduk').selectedIndex].value;
	param = 'ptoptinduk=' + ptoptinduk + '&method=getkodeorg';
	tujuan = 'keu_slave_5organisasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('ptopt').innerHTML = con.responseText;
					document.getElementById('ptopt').disabled = false;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}