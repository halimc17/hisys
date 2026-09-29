//Neraca Saldo Periodik: preview, drilldown per bulan, export Excel/PDF

//kotak tabel dibuat memenuhi sisa layar ke bawah (bukan tinggi tetap 400px yang menyisakan area kosong
//di bawahnya bila layar lebih tinggi dari itu). Dihitung ulang saat load, resize, dan setelah data dimuat.
function nspIsiSisaLayar() {
	var el = document.getElementById('printContainer');
	if (!el) {
		return;
	}
	var sisa = window.innerHeight - el.getBoundingClientRect().top - 30;
	el.style.height = Math.max(sisa, 300) + 'px';
}
window.addEventListener('resize', nspIsiSisaLayar);
if (window.jQuery) {
	jQuery(document).ready(nspIsiSisaLayar);
} else {
	document.addEventListener('DOMContentLoaded', nspIsiSisaLayar);
}

//Regional dan Unit diisi ulang lewat AJAX (getReg/getUnit di js/keu_laporan.js) yang mengganti innerHTML select biasa;
//select2 tidak otomatis mengikuti perubahan itu, jadi diinisialisasi ulang begitu opsinya benar-benar berganti.
function nspJagaSelect2(id) {
	var el = document.getElementById(id);
	if (!el || el._nspObserved || !window.jQuery || !jQuery.fn.select2) {
		return;
	}
	el._nspObserved = true;
	new MutationObserver(function () {
		jQuery(el).select2('destroy');
		jQuery(el).select2({ dropdownAutoWidth: true });
	}).observe(el, { childList: true });
}
if (window.jQuery) {
	jQuery(document).ready(function () {
		nspJagaSelect2('regional');
		nspJagaSelect2('gudang');
	});
}

function paramNsp() {
	var p = '';
	p += '&pt=' + encodeURIComponent(document.getElementById('pt').value);
	p += '&regional=' + encodeURIComponent(document.getElementById('regional').value);
	p += '&gudang=' + encodeURIComponent(document.getElementById('gudang').value);
	p += '&tahun=' + encodeURIComponent(document.getElementById('tahun').value);
	p += '&akundari=' + encodeURIComponent(document.getElementById('akundari').value);
	p += '&akunsampai=' + encodeURIComponent(document.getElementById('akunsampai').value);
	p += '&revisi=' + encodeURIComponent(document.getElementById('revisi').value);
	p += '&tampilanId=' + encodeURIComponent(document.getElementById('tampilanId').value);
	return p;
}

function preview() {
	if (document.getElementById('pt').value == '') {
		alert('PT harus dipilih.');
		return;
	}
	if (document.getElementById('tahun').value == '') {
		alert('Tahun harus dipilih.');
		return;
	}
	var param = 'method=preview' + paramNsp();
	var tujuan = 'keu_slave_2neracasaldoperiodik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML = con.responseText;
					if (window.leftFixedTable) {
						leftFixedTable();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function lihatDetailNsp(noakun, bulan, ev) {
	var param = 'method=detailjurnal&noakun=' + encodeURIComponent(noakun) + '&bulan=' + encodeURIComponent(bulan);
	param += '&pt=' + encodeURIComponent(document.getElementById('nspPt').value);
	param += '&regional=' + encodeURIComponent(document.getElementById('nspRegional').value);
	param += '&gudang=' + encodeURIComponent(document.getElementById('nspGudang').value);
	param += '&tahun=' + encodeURIComponent(document.getElementById('nspTahun').value);
	param += '&revisi=' + encodeURIComponent(document.getElementById('nspRevisi').value);
	var tujuan = 'keu_slave_2neracasaldoperiodik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup2('Rincian Jurnal', con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('70%', '60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function excelNsp() {
	if (document.getElementById('pt').value == '' || document.getElementById('tahun').value == '') {
		alert('PT dan Tahun harus dipilih.');
		return;
	}
	var tujuan = 'keu_2neracasaldoperiodik_excel.php?' + paramNsp().substring(1);
	printnopopup(tujuan);
}

function pdfNsp() {
	if (document.getElementById('pt').value == '' || document.getElementById('tahun').value == '') {
		alert('PT dan Tahun harus dipilih.');
		return;
	}
	var tujuan = 'keu_2neracasaldoperiodik_pdf.php';
	alertify.popuppdf('title', "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + '?' + paramNsp().substring(1) + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('90%', '80%');
}

function batal() {
	document.getElementById('regional').value = '';
	document.getElementById('gudang').value = '';
	document.getElementById('akundari').value = '';
	document.getElementById('akunsampai').value = '';
	document.getElementById('tampilanId').value = '0';
	document.getElementById('printContainer').innerHTML = '';
	if (window.jQuery) {
		jQuery('#regional, #gudang, #akundari, #akunsampai, #tampilanId').trigger('change.select2');
	}
}
