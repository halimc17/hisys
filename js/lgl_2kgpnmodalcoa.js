//============================================================
// Master Mapping COA - LKPM Kegiatan Penanaman Modal
//============================================================

function coaVal(id) {
	var el = document.getElementById(id);
	return el ? el.value : '';
}

function coaSetVal(id, v) {
	var el = document.getElementById(id);
	if (el) {
		el.value = (v === null || v === undefined) ? '' : v;
	}
}

//cek apakah akun yang dipilih sudah terdaftar di mapping (lihat tanda [MT]/[MK])
function akunSudahTerdaftar() {
	var el = document.getElementById('noakun');
	if (!el || el.selectedIndex < 0) {
		return false;
	}
	var txt = el.options[el.selectedIndex].text;
	return txt.indexOf('[MT]') > -1 || txt.indexOf('[MK]') > -1;
}

//------------------------------------------------------------
// Simpan (insert / update)
//------------------------------------------------------------
function simpanCoa() {
	var noakun = coaVal('noakun');
	var jenis = coaVal('jenis');
	if (noakun == '') {
		alert('Nomor akun wajib dipilih.');
		return;
	}
	if (jenis == '') {
		alert('Jenis wajib dipilih (Modal Tetap / Modal Kerja).');
		return;
	}
	var met = coaVal('method');
	//guard duplikat: akun yang sudah terdaftar ditandai [MT]/[MK] di dropdown
	if (met == 'insert' && akunSudahTerdaftar()) {
		alert('Akun ' + noakun + ' sudah terdaftar di mapping. Gunakan tombol Ubah.');
		return;
	}
	var param = 'method=' + met;
	param += '&noakun=' + encodeURIComponent(noakun);
	param += '&jenis=' + encodeURIComponent(jenis);
	param += '&keterangan=' + encodeURIComponent(coaVal('keterangan'));
	param += '&aktif=' + encodeURIComponent(coaVal('aktif'));
	post_response_text('lgl_slave_2kgpnmodalcoa.php', param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					return;
				}
				alert(met == 'insert' ? 'Akun berhasil ditambahkan ke mapping.' : 'Mapping berhasil diubah.');
				cancelCoa();
				loaddata();
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//------------------------------------------------------------
// Isi form dari baris grid (mode ubah)
//------------------------------------------------------------
function fillField(noakun, jenis, keterangan, aktif) {
	coaSetVal('noakun', noakun);
	coaSetVal('jenis', jenis);
	coaSetVal('keterangan', keterangan);
	coaSetVal('aktif', aktif);
	document.getElementById('method').value = 'update';
	document.getElementById('noakun').focus();
}

//------------------------------------------------------------
// Batal / reset form
//------------------------------------------------------------
function cancelCoa() {
	coaSetVal('noakun', '');
	coaSetVal('jenis', '');
	coaSetVal('keterangan', '');
	coaSetVal('aktif', '1');
	document.getElementById('method').value = 'insert';
}

//------------------------------------------------------------
// Hapus akun dari mapping (tombol di form)
//------------------------------------------------------------
function hapusCoa() {
	var noakun = coaVal('noakun');
	if (noakun == '') {
		alert('Pilih akun yang akan dihapus.');
		return;
	}
	hapusBaris(noakun);
}

function hapusBaris(noakun) {
	if (!confirm('Hapus akun ' + noakun + ' dari mapping COA LKPM ?')) {
		return;
	}
	var param = 'method=delete&noakun=' + encodeURIComponent(noakun);
	post_response_text('lgl_slave_2kgpnmodalcoa.php', param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					return;
				}
				alert('Akun berhasil dihapus dari mapping.');
				cancelCoa();
				loaddata();
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//------------------------------------------------------------
// Aktifkan / nonaktifkan akun
//------------------------------------------------------------
function toggleBaris(noakun) {
	if (!confirm('Ubah status aktif akun ' + noakun + ' ?')) {
		return;
	}
	var param = 'method=toggle&noakun=' + encodeURIComponent(noakun);
	post_response_text('lgl_slave_2kgpnmodalcoa.php', param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					return;
				}
				loaddata();
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//------------------------------------------------------------
// Muat daftar mapping
//------------------------------------------------------------
function loaddata() {
	var param = 'method=loaddata';
	param += '&jenissrc=' + encodeURIComponent(coaVal('jenissrc'));
	param += '&aktifsrc=' + encodeURIComponent(coaVal('aktifsrc'));
	param += '&carisrc=' + encodeURIComponent(coaVal('carisrc'));
	post_response_text('lgl_slave_2kgpnmodalcoa.php', param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					return;
				}
				document.getElementById('container').innerHTML = con.responseText;
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
