function displayList() {
	document.getElementById('karyawansch').value = '';
	document.getElementById('tglsch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	karyawansch = document.getElementById('karyawansch').value;
	tglsch = document.getElementById('tglsch').value;
	param = 'method=loaddata&page=' + page;
	if (karyawansch != '') {
		param += '&karyawansch=' + karyawansch;
	}
	if (tglsch != '') {
		param += '&tglsch=' + tglsch;
	}
	tujuan = 'kebun_slave_hapanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}

function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('namamandor').disabled = false;
	document.getElementById('namamandor').value = '';
	document.getElementById('tgl').disabled = false;
	document.getElementById('tgl').value = '';
	
	setValue2('namamandor',null);
}

function html(nik, tgl) {
	param = 'method=html' + '&nik=' + nik + '&tgl2=' + tgl;
	tujuan = 'kebun_slave_hapanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getLuas() {
	blok = document.getElementById('blokx').value;

	param = 'method=getLuas';
	param += '&blok='+blok;
	tujuan = 'kebun_slave_hapanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('luasarestax').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edit(tgl, namamandor, mode) {
	document.getElementById('tgl').value = tgl;
	document.getElementById('namamandor').value = namamandor;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	if (mode == 'edit') {
		detail(tgl, namamandor,'edit');
	}else {
		detail();
	}
}

function deleteData(tgl, nik, page) {
	param = 'method=delete' + '&tgl2=' + tgl + '&nik=' + nik;
	tujuan = 'kebun_slave_hapanen.php';
	if (confirm('Apakah Anda yakin ingin menghapus Data ???\nAksi ini hanya bisa dilakukan sekali.')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(tgl, nik, page) {
	param = 'method=posting' + '&tgl2=' + tgl + '&nik=' + nik;
	tujuan = 'kebun_slave_hapanen.php';
	if (confirm('Apakah Anda yakin ingin posting Data ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(nikmandor, tgl, nik, kodeorg, page) {
	param = 'method=unposting' + '&tgl2=' + tgl + '&nik=' + nik + '&nikmandor=' + nikmandor + '&kodeorg=' + kodeorg;
	tujuan = 'kebun_slave_hapanen.php';
	if (confirm('Apakah Anda yakin ingin unposting Data ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetail(tgl, namamandor, mode) {
	document.getElementById('namamandor').disabled = true;
	document.getElementById('tgl').disabled = true;
	param = 'method=loaddatadetail';
	param += '&nik=' + namamandor;
	if (mode == 'edit') {
		param += '&tgl2=' + tgl
		param += '&mode='+mode;
	} else {
		param += '&tgl=' + tgl	
	}
	tujuan = 'kebun_slave_hapanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detail(tgledit, namamandoredit,mode) {
	if (tgledit == "" || tgledit == undefined) {
		tgl = document.getElementById('tgl').value;
	} else{
		tgl = tgledit;
	}

	if (namamandoredit == "" || namamandoredit == undefined) {
		namamandor = document.getElementById('namamandor').value;
	} else{
		namamandor = namamandoredit;
	}

	if (tgl == '' || namamandor == '') {
		alertify.alert('Lengkapi Pengisian');
		return;
	}
	document.getElementById('tomboldetail').disabled = true;

	param = 'method=detail';
	if (mode == 'edit') {
		param += '&nik=' + namamandor;
		param += '&tgl2=' + tgl
		param += '&mode='+ mode;
	} else {
		param += '&nik=' + namamandor + '&tgl=' + tgl;
	}
	tujuan = 'kebun_slave_hapanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					if (mode == 'edit') {
						loaddatadetail(tgl, namamandor, 'edit');
					} else {
						loaddatadetail(tgl, namamandor);
					}
					
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetail(mode) {
	blokx = document.getElementById('blokx').value;
	luasarestax = document.getElementById('luasarestax').value;
	tglx = document.getElementById('tgl').value;
	nikx = document.getElementById('nikx').value;
	nikmandorx = document.getElementById('namamandor').value;
	luaspnnx = document.getElementById('luaspnnx').value;
	method = document.getElementById('method').value;

	if (luaspnnx == "") {
		alertify.alert('Luas Panen Wajib di isi !');
		return;
	}
	if (parseFloat(luaspnnx) > parseFloat(luasarestax)) {
		alertify.alert('Luas panen tidak boleh lebih besar dari Luas Planted !');
		return;
	}
	param = 'kodeorg=' + blokx;
	param += '&nikpemanen=' + nikx;
	param += '&nik=' + nikmandorx;
	param += '&luaspnn=' + luaspnnx;
	if (mode == 'edit') {
		param += '&tgl2=' + tglx;
		param += '&mode=' + mode;
	} else {
		param += '&tgl=' + tglx;
	}
	param += '&method=' + method;
	tujuan = 'kebun_slave_hapanen.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (mode == 'edit') {
						detail(tglx,nikmandorx,'edit');
					} else {
						detail();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedetail(row,mode) {
	kodeorg = document.getElementById('kodeorgd'+row).innerHTML;
	tgl = document.getElementById('tgld'+row).innerHTML;
	nik = document.getElementById('nikd'+row).innerHTML;
	nikmandor = document.getElementById('nikmandord'+row).innerHTML;
	param = 'method=deletedetail'; 
	param += '&kodeorg=' + kodeorg + '&tgl2=' + tgl + '&nikpemanen=' + nik + '&nik=' + nikmandor;
	tujuan = 'kebun_slave_hapanen.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (mode != 'edit') {
						detail();
					} else {
						detail('','','edit');
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}