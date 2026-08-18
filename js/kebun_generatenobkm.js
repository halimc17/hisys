
function cariBast(num) {
	cariPt = document.getElementById('cariPt').value;
	cariStatus = document.getElementById('cariStatus').value;

	param = 'method=loadData&cariPt=' + cariPt + '&cariStatus=' + cariStatus;
	param += '&page=' + num;
	tujuan = 'kebun_slave_generatenobkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//displayList();

					document.getElementById('container').innerHTML = con.responseText;
					//loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan() {
	unit = document.getElementById('kdUnit').value;
	divisi = document.getElementById('divisi').value;
	periode = document.getElementById('periode').value;
	noawal = document.getElementById('noawal').value;
	noakhir = document.getElementById('noakhir').value;
	jumlah = document.getElementById('jumlah').value;
	subbagian = document.getElementById('subbagian').value;
	method = document.getElementById('method').value;
	
	if(subbagian != '' && subbagian!=divisi){
		if (confirm("Divisi yang anda masukkan tidak sama dengan lokasi tugas anda, Lanjutkan ???")){
		}else{
			return;
		}
	}
	
	if (unit == '' || divisi == '' || periode == '') {
		alert('Unit, Divisi dan Periode harus diisi.');
		return;
	}

	if (jumlah < 0) {
		alert('Jumlah tidak boleh kurang dari Nol.');
		return;
	}

	param = 'unit=' + unit + '&divisi=' + divisi + '&periode=' + periode + '&method=' + method + '&noawal=' + noawal + '&noakhir=' + noakhir + '&jumlah=' + jumlah;
	tujuan = 'kebun_slave_generatenobkm.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert("Data berhasil disimpan.");
					loadData();
					cancel();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancel() {
	document.getElementById('kdUnit').value = '';
	document.getElementById('divisi').value = '';
	document.getElementById('periode').value = '';
	document.getElementById('noawal').value = '';
	document.getElementById('noakhir').value = '';
	document.getElementById('jumlah').value = '';
	document.getElementById('method').value = 'insert';
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	cariBast(paged);
}

function loadData() {
	param = 'method=loadData';
	tujuan = 'kebun_slave_generatenobkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edit(id, pt, npwp, fakturawal, fakturakhir, jumlah) {
	document.getElementById('id').value = id;
	document.getElementById('pt').value = pt;
	document.getElementById('ptlama').value = pt;
	document.getElementById('npwp').innerHTML = "<option value='" + npwp + "'>" + npwp + "</option>"
	document.getElementById('fakturawal').value = fakturawal;
	document.getElementById('fakturawallama').value = fakturawal;
	document.getElementById('fakturakhir').value = fakturakhir;
	document.getElementById('fakturakhirlama').value = fakturakhir;
	document.getElementById('jumlah').value = jumlah;
	document.getElementById('method').value = 'update';
}

function del(unit, divisi, noawal, noakhir) {
	param = 'method=delete' + '&unit=' + unit + '&divisi=' + divisi + '&noawal=' + noawal + '&noakhir=' + noakhir;
	tujuan = 'kebun_slave_generatenobkm.php';
	if (confirm("Anda yakin menghapus item ini?"))
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getnoawal() {
	divisi = document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
	periode = document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	param = 'periode=' + periode + '&method=getnoawal';
	param += '&divisi=' + divisi;
	tujuan = 'kebun_slave_generatenobkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('noawal').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

// function getjumlah(){
// fakturawal=document.getElementById('fakturawal').value;
// fakturakhir=document.getElementById('fakturakhir').value;

// var pawal = fakturawal.substr(11,8);
// var pakhir = fakturakhir.substr(11,8);

// var jlh = (parseFloat(pakhir)-parseFloat(pawal))+1;
// if(jlh==0){
// jlh=1;
// }
// document.getElementById('jumlah').value=jlh;
// }

function getnoakhir() {
	noawal = document.getElementById('noawal').value;
	jumlah = document.getElementById('jumlah').value;
	if (jumlah == 0) {
		jumlah = 1;
	}
	var pawal = noawal.substr(14, 3);
	var nawal = noawal.substr(0, 14);

	var jlh = (parseFloat(pawal) + parseFloat(jumlah)) - 1;
	var jlh = pad_with_zeroes(jlh, 3);
	if (jlh > 999) {
		alert('Nomor terlalu besar');
		document.getElementById('jumlah').value = '';
		document.getElementById('noakhir').value = '';
		return;
	}

	document.getElementById('noakhir').value = nawal + jlh;
	if (document.getElementById('jumlah').value == 0) {
		document.getElementById('noakhir').value = noawal;
	}
}

function pad_with_zeroes(number, length) {

	var my_string = '' + number;
	while (my_string.length < length) {
		my_string = '0' + my_string;
	}

	return my_string;

}

function resetcari() {
	document.getElementById('cariPt').value = '';
	document.getElementById('cariStatus').value = '';
	cariBast();
}

function form() {
	width = '720';
	height = '';
	content = "<fieldset><div id=containerd style=\"width:700px;max-height:350px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail";
	showDialog2(title, content, width, height, ev);
}

function detail(id, tipe, ev) {
	form();
	param = 'method=detail' + '&id=' + id + '&tipe=' + tipe;
	tujuan = 'kebun_slave_generatenobkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailexcel(id, tipe, ev) {
	param = 'method=detail' + '&id=' + id + '&tipe=' + tipe;
	tujuan = 'kebun_slave_generatenobkm.php' + "?" + param;
	width = '600';
	height = '200';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog5('Detail Faktur', content, width, height, ev);
}

function posting(id, numrow) {
	param = 'method=posting' + '&id=' + id;
	tujuan = 'kebun_slave_generatenobkm.php';
	if (confirm('Transaksi yang sudah di posting tidak bisa di Unposting, anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					x = document.getElementById('tr_' + numrow);
					x.cells[6].innerHTML = 'Aktif';
					x.cells[8].innerHTML = '';
					x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height=30>';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getexp(nofaktur, numrow) {
	expid = document.getElementById('exp_' + numrow);
	if (expid.checked == true) {
		methodexp = 'exp';
	} else {
		methodexp = 'unexp';
	}
	param = 'method=' + methodexp + '&nofaktur=' + nofaktur;
	tujuan = 'kebun_slave_generatenobkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					x = document.getElementById('trdet_' + numrow);
					if (con.responseText == '1') {
						x.cells[6].innerHTML = '<font color=red>Expired</font>';
					} else {
						x.cells[6].innerHTML = '';
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function printpdf(unit, divisi, noawal, noakhir, ev) {
	param = "method=printpdf&unit=" + unit + "&divisi=" + divisi + "&noawal=" + noawal + "&noakhir=" + noakhir;
	showDialog1('Print PDF', "<iframe frameborder=0 style='width:995px;height:395px' src='kebun_slave_generatenobkm.php?" + param + "'></iframe>", '', '', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}