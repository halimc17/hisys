//============================================================
// LKPM — Laporan Kegiatan Penanaman Modal
//============================================================

$(document).ready(function () {
	if (typeof $.fn.select2 === 'function') {
		$('.select2').select2({dropdownAutoWidth: true, width: '230px'});
		$('<style>.select2-container--open .select2-dropdown{z-index:100000;}</style>').appendTo('head');
	}
});

function kgpnVal(id) {
	var el = document.getElementById(id);
	return el ? el.value : '';
}

//susun parameter filter
function kgpnFilterParam() {
	var p = '';
	p += '&ptsrc=' + encodeURIComponent(kgpnVal('ptsrc'));
	p += '&periodedari=' + encodeURIComponent(kgpnVal('periodedari'));
	p += '&periodesampai=' + encodeURIComponent(kgpnVal('periodesampai'));
	return p;
}

//muat pratinjau laporan
function loaddata(page) {
	var ptsrc = kgpnVal('ptsrc');
	if (ptsrc == '') {
		alert('PT wajib dipilih.');
		return;
	}
	if (kgpnVal('periodedari') == '' || kgpnVal('periodesampai') == '') {
		alert('Periode dari dan sampai wajib diisi.');
		return;
	}
	var param = 'method=loaddata' + kgpnFilterParam();
	post_response_text('legal_slave_2kgpnmodal.php', param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					return;
				}
				document.getElementById('printContainer0').innerHTML = con.responseText;
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//tarikan Excel
function exportExcelKGPN() {
	var ptsrc = kgpnVal('ptsrc');
	if (ptsrc == '') {
		alert('PT wajib dipilih.');
		return;
	}
	var url = 'legal_slave_2kgpnmodal.php?method=excellist' + kgpnFilterParam();
	window.open(url, '_blank');
}

//tarikan PDF
function exportPdfKGPN() {
	var ptsrc = kgpnVal('ptsrc');
	if (ptsrc == '') {
		alert('PT wajib dipilih.');
		return;
	}
	var url = 'legal_slave_2kgpnmodal.php?method=pdflist' + kgpnFilterParam();
	window.open(url, '_blank');
}
