// JS khusus Laporan CSR (legal_2csr) - terpisah dari transaksi bansos
function getPageCSR() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	divsch = document.getElementById('divsch').value;
	tanggalmulai = document.getElementById('tanggalmulai').value;
	tanggalsampai = document.getElementById('tanggalsampai').value;
	katsrc = document.getElementById('katsrc').value;

	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (katsrc != '') {
		param += '&katsrc=' + katsrc;
	}
	param += '&tanggalmulai=' + tanggalmulai;
	param += '&tanggalsampai=' + tanggalsampai;
	param += '&tipe=html';

	tujuan = 'legal_slave_2csr.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//parameter filter daftar untuk tarikan Excel/PDF
function getFilterParamCSR() {
	var p = '';
	p += '&divsch=' + encodeURIComponent(document.getElementById('divsch').value);
	p += '&katsrc=' + encodeURIComponent(document.getElementById('katsrc').value);
	p += '&tanggalmulai=' + encodeURIComponent(document.getElementById('tanggalmulai').value);
	p += '&tanggalsampai=' + encodeURIComponent(document.getElementById('tanggalsampai').value);
	return p;
}

function tampilTarikanCSR(judul, tujuan) {
	alertify.popup(judul, "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>").set({ 'resizable': true, 'maximizable': true, 'overflow': false }).resizeTo('80%', '70%');
}

function exportExcelCSR() {
	tampilTarikanCSR('Report Ms.Excel', 'legal_slave_2csr.php?method=excellist' + getFilterParamCSR());
}

function exportPdfCSR() {
	tampilTarikanCSR('Report PDF', 'legal_slave_2csr.php?method=pdflist' + getFilterParamCSR());
}
