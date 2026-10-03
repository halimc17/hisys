function bbJagaSelect2(id) {
	var el = document.getElementById(id);
	if (!el || el._bbObserved || !window.jQuery || !jQuery.fn.select2) {
		return;
	}
	el._bbObserved = true;
	new MutationObserver(function () {
		jQuery(el).select2('destroy');
		jQuery(el).select2({ dropdownAutoWidth: true });
	}).observe(el, { childList: true });
}
if (window.jQuery) {
	jQuery(document).ready(function () {
		bbJagaSelect2('regional');
		bbJagaSelect2('gudang');
	});
}

function getLaporanBukuBesarPdf() {
	pt = document.getElementById('pt');
	gudang = document.getElementById('gudang');
	periode = document.getElementById('periode');
	periode1 = document.getElementById('periode1');
	ptV = pt.options[pt.selectedIndex].value;
	gudangV = gudang.options[gudang.selectedIndex].value;
	periodeV = periode.options[periode.selectedIndex].value;
	periodeV1 = periode1.options[periode1.selectedIndex].value;
	revisi = document.getElementById('revisi');
	revisi = revisi.options[revisi.selectedIndex].value;

	regional = document.getElementById('regional');
	regional = regional.options[regional.selectedIndex].value;
	tampilanId = document.getElementById('tampilanId');
	tampilanId = tampilanId.options[tampilanId.selectedIndex].value;

	param = 'pt=' + ptV + '&gudang=' + gudangV + '&periode=' + periodeV + '&periode1=' + periodeV1 + '&revisi=' + revisi;
	param += '&regional=' + regional + '&tampilanId=' + tampilanId;

	akundari = document.getElementById('akundari');
	if (akundari) {
		param += '&akundari=' + akundari.value;
	}
	akunsampai = document.getElementById('akunsampai');
	if (akunsampai) {
		param += '&akunsampai=' + akunsampai.value;
	}

	if (ptV == '') {
		alertify.alert("Informasi", 'Field PT empty');
		return;
	}
	printFile(param, 'keu_laporanBukuBesar_pdf.php', 'Report PDF', 'event');
}

function bbBatal() {
	document.getElementById('regional').value = '';
	document.getElementById('gudang').value = '';
	document.getElementById('akundari').value = '';
	document.getElementById('akunsampai').value = '';
	document.getElementById('tampilanId').value = '0';
	document.getElementById('tampilkan').value = '1';
	document.getElementById('container').innerHTML = '';
	hideById('printPanel');
	if (window.jQuery) {
		jQuery('#regional, #gudang, #akundari, #akunsampai, #tampilanId, #tampilkan').trigger('change.select2');
	}
}
