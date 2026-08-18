function simpan() {
	method = document.getElementById('method').value;
	divisi = document.getElementById('divisi').value;
	kodeorg = document.getElementById('kodeorg').value;
	blok = document.getElementById('blok').value;
	tgl = document.getElementById('tgl').value;
	adjust = document.getElementById('adjust').value;
	ket = document.getElementById('ket').value;
	restan = document.getElementById('restan').value;

	if (adjust == '' || adjust == 0 || blok == '' || tgl == '' || ket=='') {
		alert('Lengkapi pengisian');
		return;
	}
	param = 'kodeorg=' + kodeorg + '&divisi=' + divisi + '&blok=' + blok + '&tgl=' + tgl + '&adjust=' + adjust;
	param += '&method=' + method;
	param += '&ket=' + ket;
	param += '&restan=' + restan;
	tujuan = 'kebun_slave_5adjusmentrestant.php';
	if (confirm("Yakin ingin ???")) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Data sudah di update !!!');
					loadData();
					hapus();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hapus() {
	document.getElementById('divisi').value = '';
	document.getElementById('blok').value = '';
	document.getElementById('tgl').value = '';
	//document.getElementById('jjgpanen').value = '';
	document.getElementById('restan').value = '';
	document.getElementById('adjust').value = '';
	document.getElementById('kodeorg').value = '';
}

function loadData(num) {

	bloksrc = document.getElementById('bloksrc').value;
	tglsrc = document.getElementById('tglsrc').value;

	param = 'method=loadData';
	param += '&bloksrc=' + bloksrc;
	param += '&tglsrc=' + tglsrc;
	param += '&page=' + num;

	tujuan = 'kebun_slave_5adjusmentrestant.php';
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

function loadDataAll(num) {
	document.getElementById('tglsrc').value = '';
	document.getElementById('bloksrc').value = '';

	param = 'method=loadData';
	param += '&page=' + num;

	tujuan = 'kebun_slave_5adjusmentrestant.php';
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

function del(blok, seksi) {
	param = 'method=delete' + '&blok=' + blok + '&seksi=' + seksi;
	tujuan = 'kebun_slave_5adjusmentrestant.php';
	if (confirm("Delete data?")) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdivisi() {
	kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param = 'kodeorg=' + kodeorg + '&method=getdivisi';
	tujuan = 'kebun_slave_5adjusmentrestant.php';
	post_response_text(tujuan, param, respog);
	function respog() {
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

}

function getblok() {

	divisi = document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
	param = 'divisi=' + divisi + '&method=getblok';

	tujuan = 'kebun_slave_5adjusmentrestant.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert("masuk");
					document.getElementById('blok').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function gettahuntanam() {

	blok = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	param = 'blok=' + blok + '&method=gettahuntanam';

	tujuan = 'kebun_slave_5adjusmentrestant.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert("masuk");
					document.getElementById('tahuntanam').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getrestan() {
	blok = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	tgl = document.getElementById('tgl').value;
	param = 'blok=' + blok + '&tgl=' + tgl + '&method=getrestan';

	tujuan = 'kebun_slave_5adjusmentrestant.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("##");
					document.getElementById('restan').value = trim(isi[0]);
					//document.getElementById('jjgpanen').value = trim(isi[1]);

					if (parseFloat(trim(isi[0])) != 0) {
						document.getElementById('adjust').disabled = false;
					} else {
						document.getElementById('adjust').disabled = true;
						document.getElementById('adjust').value = 0;
					}

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}