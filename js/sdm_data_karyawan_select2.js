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
			var tampil = $.trim($box.find('.select2-selection__rendered').text());
			var opt = this.options[this.selectedIndex];
			var asli = opt ? $.trim(opt.text) : '';
			if (tampil !== asli) {
				$el.trigger('change.select2');
			}
		});
	}
	setInterval(sinkronSelect2, 500);
})();
