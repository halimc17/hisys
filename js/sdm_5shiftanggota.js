function copyshift(baris, kolom, val) {
	maxtanggal = document.getElementById('maxtanggal').value;
	for (i = (parseFloat(kolom) + 1); i <= maxtanggal; i++) {
		if (document.getElementById("shift_" + baris + "_" + i) != undefined) {
			document.getElementById("shift_" + baris + "_" + i).value = val;
		}
	}
}
function copy() {
	dari = document.getElementById('periodedari').value;
	ke = document.getElementById('periodeke').value;
	kodeorg = document.getElementById('kodeorgcopy').value;

	param = 'method=copy';
	param += '&dari=' + dari;
	param += '&ke=' + ke;
	param += '&kodeorg=' + kodeorg;

	if (dari == ke) {
		alertify.alert("Periode dari dan periode tujuan tidak boleh sama"); return;
	}

	if (ke < dari) {
		alertify.alert("Periode tujuan tidak boleh lebih kecil dari periode dari/sumber."); return;
	}

	validate([
		["periodedari", "Periode dari tidak boleh kosong."],
		["periodeke", "Periode tujuan tidak boleh kosong."],
		["kodeorgcopy", "Kodeorg tidak boleh kosong"]
	]);

	tujuan = 'sdm_slave_5shiftanggota.php';
	if (confirm("Proses ini akan meng-copy data dari periode " + dari + " ke periode " + ke + " untuk Unit " + kodeorg + ", anda yakin ???")) {
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (trim(con.responseText) != '') {
						if (confirm("Data untuk periode " + ke + " Unit " + kodeorg + " sudah ada, click OK untuk mereplace data yg sudah ada.")) {
							prosescopy();
						}
					} else {
						prosescopy();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function prosescopy() {
	dari = document.getElementById('periodedari').value;
	ke = document.getElementById('periodeke').value;
	kodeorg = document.getElementById('kodeorgcopy').value;

	param = 'method=prosescopy';
	param += '&dari=' + dari;
	param += '&ke=' + ke;
	param += '&kodeorg=' + kodeorg;

	tujuan = 'sdm_slave_5shiftanggota.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alert("Done");
					getPage();
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


function html(ev, kodeorg, subbagian, periode) {
	param = 'method=html' + '&subbagian=' + subbagian + '&kodeorg=' + kodeorg + '&ev=' + ev;
	param += '&periode=' + periode;
	tujuan = 'sdm_slave_5shiftanggota.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (ev == 'excel') {
						printFile(param, tujuan, 'Judul', ev)
					} else {
						alertify.popuppdf().set({ 'resizable': true, 'maximizable': true, 'startMaximized': true, 'message': con.responseText }).resizeTo('80%', '70%').show();
					}

					//alertify.popuppdf().set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '600';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog2(title, content, width, height, ev);
}


function displayList() {
	document.getElementById('tahunsch').value = '';
	document.getElementById('kodeorgsch').value = '';

	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('contdetail').style.display = 'none';
	loaddata(0);
}
function edit(kodeorg, subbagian, periode) {
	// setValue2('kodeorg',kodeorg);
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('subbagian').value = subbagian;
	//getsubbagian('kodeorg',subbagian);

	setValue2('periode', periode);
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	loaddatadetail();
}

function del(kodeorg, subbagian, periode) {
	param = 'method=delete' + '&subbagian=' + subbagian + '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	tujuan = 'sdm_slave_5shiftanggota.php';
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
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function posting(kodeorg, subbagian, periode) {
	param = 'method=posting' + '&subbagian=' + subbagian + '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	tujuan = 'sdm_slave_5shiftanggota.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function unposting(kodeorg, subbagian, periode) {
	param = 'method=unposting' + '&subbagian=' + subbagian + '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	tujuan = 'sdm_slave_5shiftanggota.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanshift(maxrow, col) {
	if (maxrow == '' || maxrow == 0) {
		alertify.alert('Info', 'Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	alertify.confirm("Warning", "Simpan seluruhnya ?",
		function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
			simpan(1, maxrow, col);
		},
		function () {
			return;
		}
	);
}

function simpan(row, maxrow, col) {
	kodeorg = document.getElementById('kodeorg').value;
	subbagian = document.getElementById('subbagian').value;
	departemen = document.getElementById('departemen').value;
	periode = document.getElementById('periode').value;
	karyawanid = document.getElementById('karyawanid_' + row).value;

	param = 'method=insert';
	for (i = 1; i <= col; i++) {
		tgl = document.getElementById('tgl_' + i).value;
		param += '&tanggal[' + i + ']=' + tgl;

		shift = document.getElementById('shift_' + row + '_' + i).value;
		param += '&shift[' + i + ']=' + shift;
	}

	validate([
		["kodeorg", "Kode organisasi tidak boleh kosong"],
		["periode", "Periode tidak boleh kosong"]
	]);

	param += '&periode=' + periode + '&kodeorg=' + kodeorg;
	param += '&subbagian=' + subbagian + '&departemen=' + departemen + '&karyawanid=' + karyawanid;

	if (row != undefined) {
		document.getElementById('row' + row).style.backgroundColor = 'cyan';
	}

	tujuan = 'sdm_slave_5shiftanggota.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('row' + row).style.display = 'none';
					row += 1;
					if ((row > maxrow) || (maxrow == undefined)) {
						alertify.alert("Done");
						document.getElementById('detail').innerHTML = "";
					} else {
						simpan(row, maxrow, col);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	tahun = document.getElementById('tahunsch').value;
	kodeorg = document.getElementById('kodeorgsch').value;

	param = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'sdm_slave_5shiftanggota.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancel() {
	document.getElementById('contdetail').style.display = 'none';
	// document.getElementById('tahun').disabled = false;
	// document.getElementById('kodeorg').disabled = false;
	// document.getElementById('golongan').disabled = false;
	// document.getElementById('golongan').value = '';
	// document.getElementById('nilai').value = '';
	document.getElementById('method').value = 'insert';
}
function loaddatadetail() {
	kodeorg = document.getElementById('kodeorg').value;
	subbagian = document.getElementById('subbagian').value;
	departemen = document.getElementById('departemen').value;
	periode = document.getElementById('periode').value;
	jabatan = document.getElementById('jabatan').value;

	validate([
		["kodeorg", "Kode organisasi tidak boleh kosong"],
		["periode", "Periode tidak boleh kosong"]
	]);

	param = 'method=loaddatadetail';
	param += '&periode=' + periode + '&kodeorg=' + kodeorg;
	param += '&subbagian=' + subbagian + '&departemen=' + departemen + '&jabatan=' + jabatan;

	tujuan = 'sdm_slave_5shiftanggota.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contdetail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formupload() {
	kodeorg = document.getElementById('kodeorg').value;
	subbagian = document.getElementById('subbagian').value;
	departemen = document.getElementById('departemen').value;
	periode = document.getElementById('periode').value;

	param = 'method=formupload';
	param += '&periode=' + periode + '&kodeorg=' + kodeorg + '&subbagian=' + subbagian + '&departemen=' + departemen;
	tujuan = 'sdm_slave_5shiftanggota.php';
	judul = 'excel';
	ev = 'event';

	printnopopup(tujuan + "?" + param);
}

function getsubbagian(sumber, subbagian) {
	kodeorg = document.getElementById('kodeorg').value;

	param = 'method=getsubbagian';
	param += '&kodeorg=' + kodeorg;
	param += '&subbagian=' + subbagian;
	tujuan = 'sdm_slave_5shiftanggota.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					if (sumber == 'subbagian') {
						document.getElementById('departemen').innerHTML = data[1];
					} else {
						document.getElementById('subbagian').innerHTML = data[0];
						document.getElementById('departemen').innerHTML = data[1];
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
