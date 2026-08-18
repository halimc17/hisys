function cekpt() {
	if (document.getElementById("pt").value == "") {
		alertify.alert('Kode PT harus di pilih.');
	}
	if (document.getElementById("prd").value == "") {
		alertify.alert('Periode harus di pilih.');
	}
}
function showheader() {
	var tableheader = document.getElementById('tableheader');
	var showhead = document.getElementById('showhead');
	var tombolexport = document.getElementById('tombolexport');

	if (tableheader.style.display === 'none') {
		tableheader.style.display = 'block';
		showhead.innerHTML = 'Hide Filter';
		tombolexport.style.display = 'none';
	} else {
		tableheader.style.display = 'none';
		tombolexport.style.display = 'block';
		showhead.innerHTML = 'Show Filter';
	}
}