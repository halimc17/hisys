function cariBast(num) {
	cariPt = document.getElementById('cariPt').value;
	cariStatus = document.getElementById('cariStatus').value;
	param = 'method=loadData&cariPt=' + cariPt + '&cariStatus=' + cariStatus;
	param += '&page=' + num;
	tujuan = 'keu_slave_faktur.php';
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
	id = document.getElementById('id').value;
	pt = document.getElementById('pt').value;
	ptlama = document.getElementById('ptlama').value;
	npwp = document.getElementById('npwp').value;
	fakturawal = document.getElementById('fakturawal').value;
	fakturawallama = document.getElementById('fakturawallama').value;
	fakturakhir = document.getElementById('fakturakhir').value;
	fakturakhirlama = document.getElementById('fakturakhirlama').value;
	jumlah = document.getElementById('jumlah').value;
	method = document.getElementById('method').value;
	var pawal = fakturawal.substr(0, 11);
	var pakhir = fakturakhir.substr(0, 11);
	var awal = fakturawal.length;
	var akhir = fakturakhir.length;
	if (fakturawal.substr(3, 1) != '-' ||  fakturawal.substr(6, 1) != '.') {
		alert('Format faktur salah, format yang benar 000-00.00000000');
		return;
	}
	if (pt == '' || npwp == '') {
		alert('PT dan NPWP harus diisi.');
		return;
	}
	if (awal < 15 || akhir < 15) {
		alert('Nomor faktur salah.');
		return;
	}
	if (pawal != pakhir) {
		alert('Nomor faktur salah.');
		return;
	}
	if (jumlah < 0) {
		alert('Jumlah faktur salah.');
		return;
	}
	param = 'id=' + id + '&pt=' + pt + '&fakturawal=' + fakturawal + '&method=' + method + '&fakturakhir=' + fakturakhir + '&npwp=' + npwp + '&jumlah=' + jumlah + '&ptlama=' + ptlama + '&fakturawallama=' + fakturawallama + '&fakturakhirlama=' + fakturakhirlama;
	tujuan = 'keu_slave_faktur.php';
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
	document.getElementById('id').value = '';
	document.getElementById('npwp').value = '';
	document.getElementById('pt').selectedIndex = '0';
	document.getElementById('fakturawal').value = '010.001-17.';
	document.getElementById('fakturawal').disabled = false;
	document.getElementById('fakturakhir').value = '010.001-17.';
	document.getElementById('fakturakhir').disabled = false;
	document.getElementById('jumlah').value = '';
	document.getElementById('jumlah').disabled = false;
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
	tujuan = 'keu_slave_faktur.php';
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
function del(id) {
	param = 'method=delete' + '&id=' + id;
	tujuan = 'keu_slave_faktur.php';
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
function getnpwp() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param = 'pt=' + pt + '&method=getnpwp';
	tujuan = 'keu_slave_faktur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('npwp').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getjumlah() {
	fakturawal = document.getElementById('fakturawal').value;
	fakturakhir = document.getElementById('fakturakhir').value;
	var pawal = fakturawal.substr(7, 8);
	var pakhir = fakturakhir.substr(7, 8);
	var jlh = (parseFloat(pakhir) - parseFloat(pawal)) + 1;
	if (jlh == 0) {
		jlh = 1;
	}
	document.getElementById('jumlah').value = jlh;
}
function getpakhir() {
	fakturawal = document.getElementById('fakturawal').value;
	jumlah = document.getElementById('jumlah').value;
	// 	if(jumlah==0){
	// 		jumlah=1;
	// 	}
	var pawal = fakturawal.substr(7, 8);
	var pakhir = fakturawal.substr(0, 7);
	// var jlh = (parseFloat(pawal)+parseFloat(jumlah))-1;
	// var jlh = pakhir+jlh;
	// document.getElementById('fakturakhir').value=jlh;
	// if(document.getElementById('jumlah').value==0){
	// 	document.getElementById('jumlah').value=jumlah;
	// }
	param = 'fakturawal=' + fakturawal + '&jumlah=' + jumlah;
	param += '&pawal=' + pawal + '&pakhir=' + pakhir + '&method=getAngka';
	tujuan = 'keu_slave_faktur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					dataKriman = con.responseText.split("####");
					document.getElementById('fakturakhir').value = dataKriman[0];
					document.getElementById('fakturawal').value = dataKriman[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
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
	tujuan = 'keu_slave_faktur.php';
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
	tujuan = 'keu_slave_faktur.php' + "?" + param;
	width = '600';
	height = '200';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog5('Detail Faktur', content, width, height, ev);
}
function posting(id, numrow) {
	param = 'method=posting' + '&id=' + id;
	tujuan = 'keu_slave_faktur.php';
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
	tujuan = 'keu_slave_faktur.php';
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