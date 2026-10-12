//set nilai select2 tanpa memicu onchange bawaan select (cariData di filter)
//onchange inline dimatikan sebentar karena trigger jQuery ikut menjalankannya
function setSel(id, v) {
	var el = document.getElementById(id);
	var handler = el.onchange;
	el.onchange = null;
	$('#' + id).val(v).trigger('change.select2');
	el.onchange = handler;
}

$(document).ready(function () {
	$('.select2').select2({dropdownAutoWidth: true});
});

function simpanJ() {
	var kodeorg = document.getElementById('kodeorg').value;
	var metodepenggajian = document.getElementById('metodepenggajian').value;
	var periode = document.getElementById('periode').value;
	var tanggalmulai = document.getElementById('tanggalmulai').value;
	var tanggalsampai = document.getElementById('tanggalsampai').value;
	var met = document.getElementById('method').value;

	if (trim(kodeorg) == '' || metodepenggajian == '' || periode == '' || tanggalmulai == '' || tanggalsampai == '') {
		alert('Warning : Unit kerja, metode penggajian, periode, tanggal mulai, dan tanggal sampai wajib diisi.');
		document.getElementById('kodeorg').focus();
		return;
	}

	var param = 'kodeorg=' + encodeURIComponent(kodeorg) + '&metodepenggajian=' + encodeURIComponent(metodepenggajian) + '&method=' + met;
	param += '&periode=' + encodeURIComponent(periode) + '&tanggalmulai=' + encodeURIComponent(tanggalmulai) + '&tanggalsampai=' + encodeURIComponent(tanggalsampai);
	var tujuan = 'sdm_slave_save_5periodeGaji.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cariData();
					cancelJ();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function loaddata(){
	var param = 'method=loaddata';
	var tujuan = 'sdm_slave_save_5periodeGaji.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function bersihkanform(){
	setSel('unitcari', '');
	setSel('periodecari', '');
	setSel('statcari', '');
	cariData();
}
function cariData(){
	var unit = document.getElementById('unitcari').value;
	var periodecari = document.getElementById('periodecari').value;
	var statcari = document.getElementById('statcari').value;
	var param = 'method=loaddata'+'&statcari='+encodeURIComponent(statcari);
	param+='&unitcari='+encodeURIComponent(unit)+'&periodecari='+encodeURIComponent(periodecari);

	var tujuan = 'sdm_slave_save_5periodeGaji.php';
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

function fillField(kodeorg, jenisgaji, periode, tanggalmulai, tanggalsampai) {
	setSel('kodeorg', kodeorg);
	document.getElementById('kodeorg').disabled = true;

	setSel('metodepenggajian', jenisgaji);
	document.getElementById('metodepenggajian').disabled = true;

	//periode lama bisa di luar 13 bulan pilihan form, tambahkan supaya tetap terpilih
	if ($('#periode option').filter(function () { return this.value == periode; }).length == 0) {
		$('#periode').append($('<option>').val(periode).text(periode.substr(5, 2) + '-' + periode.substr(0, 4)));
	}
	setSel('periode', periode);
	document.getElementById('periode').disabled = true;

	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalsampai').value = tanggalsampai;
	document.getElementById('method').value = 'update';
}

function cancelJ() {
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('metodepenggajian').disabled = false;
	document.getElementById('periode').disabled = false;
	setSel('periode', '');
	document.getElementById('tanggalmulai').value = '';
	document.getElementById('tanggalsampai').value = '';

	document.getElementById('method').value = 'insert';
}

//tutup, buka, dan hapus periode: kirim lalu muat ulang daftar sesuai filter
function aksiPeriode(method, kodeorg, jenisgaji, periode) {
	var param = 'kodeorg=' + encodeURIComponent(kodeorg);
	param += '&jenisgaji=' + encodeURIComponent(jenisgaji);
	param += '&periode=' + encodeURIComponent(periode);
	param += '&method=' + method;
	var tujuan = 'sdm_slave_save_5periodeGaji.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					cariData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tutup(kodeorg, jenisgaji, periode) {
	aksiPeriode('tutup', kodeorg, jenisgaji, periode);
}

function buka(kodeorg, jenisgaji, periode) {
	aksiPeriode('buka', kodeorg, jenisgaji, periode);
}

function hapus(kodeorg, jenisgaji, periode) {
	if (confirm('Hapus periode gaji ' + periode + ' unit ' + kodeorg + ' ?')) {
		aksiPeriode('delete', kodeorg, jenisgaji, periode);
	}
}
