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
	tujuan = 'kebun_slave_mutuhancakpanen.php';
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
	tujuan = 'kebun_slave_mutuhancakpanen.php';
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
	tujuan = 'kebun_slave_mutuhancakpanen.php';
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
	tujuan = 'kebun_slave_mutuhancakpanen.php';
	if (confirm('Apakah Anda yakin ingin posting Data ???\nAksi ini hanya bisa dilakukan sekali.')) {
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

function unposting(nik, tgl, page) {
	param = 'method=unposting' + '&tgl2=' + tgl + '&nik=' + nik + '&page=' + page;
	tujuan = 'kebun_slave_mutuhancakpanen.php';
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
	tujuan = 'kebun_slave_mutuhancakpanen.php';
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
	param = 'method=detail';
	if (mode == 'edit') {
		param += '&nik=' + namamandor;
		param += '&tgl2=' + tgl
		param += '&mode='+ mode;
	} else {
		param += '&nik=' + namamandor + '&tgl=' + tgl;
	}
	tujuan = 'kebun_slave_mutuhancakpanen.php';
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

function savedetail(row,mode) {
	blokx = document.getElementById('blokx'+row).innerHTML;
	tglx = document.getElementById('tglx'+row).innerHTML;
	nikx = document.getElementById('nikx'+row).innerHTML;
	nikmandorx = document.getElementById('nikmandorx'+row).innerHTML;
	jjgbuahbesarx = document.getElementById('jjgbuahbesarx'+row).innerHTML;
	jjgbuahkecilx = document.getElementById('jjgbuahkecilx'+row).innerHTML;
	totaljjgx = document.getElementById('totaljjgx'+row).innerHTML;

	penalti1 = document.getElementById('penaltix1_'+row).value;
	penalti2 = document.getElementById('penaltix2_'+row).value;
	penalti3 = document.getElementById('penaltix3_'+row).value;
	penalti4 = document.getElementById('penaltix4_'+row).value;
	penalti5 = document.getElementById('penaltix5_'+row).value;
	penalti6 = document.getElementById('penaltix6_'+row).value;
	penalti7 = document.getElementById('penaltix7_'+row).value;
	penalti8 = document.getElementById('penaltix8_'+row).value;
	penalti9 = document.getElementById('penaltix9_'+row).value;
	penalti10 = document.getElementById('penaltix10_'+row).value;
	penalti11 = document.getElementById('penaltix11_'+row).value;
	penalti12 = document.getElementById('penaltix12_'+row).value;
	// penalti13 = document.getElementById('penaltix13_'+row).value;
	
	method = document.getElementById('method').value;

	param = 'kodeorg=' + blokx;
	param += '&nikpemanen=' + nikx;
	param += '&tgl2=' + tglx;
	param += '&nik=' + nikmandorx;
	param += '&totaljjg=' + totaljjgx;
	param += '&jjgbuahbesar=' + jjgbuahbesarx;
	param += '&jjgbuahkecil=' + jjgbuahkecilx;

	param += '&penalti1=' + penalti1;
	param += '&penalti2=' + penalti2;
	param += '&penalti3=' + penalti3;
	param += '&penalti4=' + penalti4;
	param += '&penalti5=' + penalti5;
	param += '&penalti6=' + penalti6;
	param += '&penalti7=' + penalti7;
	param += '&penalti8=' + penalti8;
	param += '&penalti9=' + penalti9;
	param += '&penalti10=' + penalti10;
	param += '&penalti11=' + penalti11;
	param += '&penalti12=' + penalti12;
	// param += '&penalti13=' + penalti13;

	if (mode == 'edit') {
		param += '&mode=' + mode;
	}
	param += '&method=' + method;
	tujuan = 'kebun_slave_mutuhancakpanen.php';
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
	param = 'method=deletedetail'; 
	param += '&kodeorg=' + kodeorg + '&tgl2=' + tgl + '&nikpemanen=' + nik;
	tujuan = 'kebun_slave_mutuhancakpanen.php';
	
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