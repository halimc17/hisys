function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '400';
	height = '200';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function desaexcel(ev, tujuan) {
	unitbawah = document.getElementById('unitbawah');
	unitbawah = unitbawah.options[unitbawah.selectedIndex].value;

	method = 'excel';

	param = 'unitbawah=' + unitbawah + '&method=' + method;

	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul, ev)
}

function gantikebun() {
	unitbawah = document.getElementById('unitbawah');
	unitbawah = unitbawah.options[unitbawah.selectedIndex].value;
	param = 'unitbawah=' + unitbawah + '&method=gantikebun';
	tujuan = 'pad_slave_save_desa.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkabupaten() {

	provinsi = document.getElementById('provinsi');
	provinsi = provinsi.options[provinsi.selectedIndex].value;
	param = 'provinsi=' + provinsi + '&method=getkabupaten';
	tujuan = 'pad_slave_save_desa.php';
	console.log(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('kabupaten').innerHTML = con.responseText;
					document.getElementById('kabupaten').disabled = false;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkecamatan() {

	kabupaten = document.getElementById('kabupaten');
	kabupaten = kabupaten.options[kabupaten.selectedIndex].value;
	param = 'kabupaten=' + kabupaten + '&method=getkecamatan';
	tujuan = 'pad_slave_save_desa.php';
	console.log(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('kecamatan').innerHTML = con.responseText;
					document.getElementById('kecamatan').disabled = false;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdesa() {

	kecamatan = document.getElementById('kecamatan');
	kecamatan = kecamatan.options[kecamatan.selectedIndex].value;
	param = 'kecamatan=' + kecamatan + '&method=getdesa';
	tujuan = 'pad_slave_save_desa.php';
	console.log(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('desa').innerHTML = con.responseText;
					document.getElementById('desa').disabled = false;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanJabatan() {
	// unitbawah = document.getElementById('unitbawah');
	// unitbawah = unitbawah.options[unitbawah.selectedIndex].value;
	unit = document.getElementById('unit');
	unit = unit.options[unit.selectedIndex].value;
	handil = document.getElementById('handil').value;
	namadesa = document.getElementById('desa').value;
	kecamatan = document.getElementById('kecamatan').value;
	kabupaten = document.getElementById('kabupaten').value;
	provinsi = document.getElementById('provinsi').value;
	met = document.getElementById('method').value;

	if (trim(namadesa) == '') {
		alert('Desa is empty');
		document.getElementById('desa').focus();
	} else {
		param = 'unit=' + unit + '&desa=' + namadesa + '&handil=' + handil + '&method=' + met;
		param += '&kecamatan=' + kecamatan + '&kabupaten=' + kabupaten + '&provinsi=' + provinsi;// + '&unitbawah=' + unitbawah;
		tujuan = 'pad_slave_save_desa.php';
		post_response_text(tujuan, param, respog);
		//alert(param);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					cancelJabatan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function fillField(kode, handil, provinsi, kabupaten, kecamatan, desa) {
	document.getElementById('handil').value = handil;
	document.getElementById('handil').disabled = true;
	document.getElementById('provinsi').value = provinsi;
	document.getElementById('kabupaten').value = kabupaten;
	document.getElementById('kecamatan').value = kecamatan;
	document.getElementById('desa').value = desa;
	x = document.getElementById('unit');
	for (y = 0; y < x.length; y++) {
		if (x.options[y].value == kode) {
			x.options[y].selected = true;
		}
	}
	document.getElementById('method').value = 'update';
}

function cancelJabatan() {
	document.getElementById('handil').value = '';
	document.getElementById('desa').disabled = false;
	document.getElementById('handil').disabled = false;
	document.getElementById('desa').value = '';
	document.getElementById('kecamatan').value = '';
	document.getElementById('kabupaten').value = '';
	document.getElementById('kecamatan').disabled = true;
	document.getElementById('kabupaten').disabled = true;
	document.getElementById('desa').disabled = true;
	document.getElementById('method').value = 'insert';
	loaddata();
}

function loaddata() {
	handilcari=document.getElementById('handilcari').value;
	desacari=document.getElementById('desacari').value;
	
	param = 'method=loaddata';
	
	param += "&handilcari=" + handilcari;
	param += "&desacari=" + desacari;
	
	tujuan = 'pad_slave_save_desa.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}