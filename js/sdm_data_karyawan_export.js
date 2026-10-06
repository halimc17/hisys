function exportKaryawan(proses) {
	var v = function (id) {
		var el = document.getElementById(id);
		return el ? encodeURIComponent(el.value) : '';
	};
	var param = 'proses=' + proses;
	param += '&txtsearch=' + v('txtsearch');
	param += '&noktp=' + v('noktpsch');
	param += '&orgsearch=' + v('schorg');
	param += '&jabatansearch=' + v('schjabatan');
	param += '&tipesearch=' + v('schtipe');
	param += '&statussearch=' + v('schstatus');
	param += '&divisisearch=' + v('schdivisi');
	param += '&golongansearch=' + v('schgolongan');
	window.open('sdm_slave_export_datakaryawan.php?' + param, '_blank');
}
