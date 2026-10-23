// Menyamakan tampilan select2 dengan nilai select asli.
// Kode lama mengisi form dengan options[x].selected / .value tanpa memicu select2,
// jadi tampilan select2 tidak ikut berubah. Di sini disinkronkan berkala (tanpa memicu onchange aplikasi).
(function () {
	function sinkronSelect2() {
		if (document.hidden || typeof $ === 'undefined') {
			return;
		}
		$('select.select2').each(function () {
			var $el = $(this);
			var $box = $el.next('.select2-container');
			if (!$box.length) {
				return;
			}
			var $tampil = $box.find('.select2-selection__rendered');
			var tampil = $.trim($tampil.text());
			var opt = this.options[this.selectedIndex];
			var asli = opt ? $.trim(opt.text) : '';
			if (tampil !== asli) {
				// hanya ubah teks tampilan. Jangan memakai trigger('change.select2'): jQuery ikut menjalankan atribut onchange
				// (getEstateX, getDivisiX2, dan lain-lain) sehingga pilihan select lain dimuat ulang dan nilainya hilang.
				$tampil.text(asli).attr('title', asli);
			}
		});
	}
	setInterval(sinkronSelect2, 500);
})();
