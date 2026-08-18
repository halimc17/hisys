function batal() {
	document.getElementById('notransaksi').value = '';
	document.getElementById('notransaksi').disabled = true;
	document.getElementById('tgl').value = getdatenow();
	document.getElementById('tgl').disabled = false;
	document.getElementById('kodeorg').value = '';
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('terimadari').value = '';
	document.getElementById('jumlah').value = '';
	document.getElementById('diterimaoleh').value = '';
	document.getElementById('pembayaran').value = '';
	document.getElementById('find_tgl').value = '';
	document.getElementById('find_diterimaoleh').value = '';
	document.getElementById('find_notransaksi').value = '';
	document.getElementById('find_pembayaran').value = '';
	document.getElementById('method').value = 'insert';
	loaddata(0);
}

function loaddata(num) {
	find_tgl = document.getElementById('find_tgl').value;
	find_diterimaoleh = document.getElementById('find_diterimaoleh').value;
	find_notransaksi = document.getElementById('find_notransaksi').value;
	find_pembayaran = document.getElementById('find_pembayaran').value;
	param = 'method=loaddata';
	param += '&page=' + num + '&find_tgl=' + find_tgl + '&find_diterimaoleh=' + find_diterimaoleh + '&find_notransaksi=' + find_notransaksi + '&find_pembayaran=' + find_pembayaran;
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getsub() {
	tipeasset = document.getElementById('tipeasset').options[document.getElementById('tipeasset').selectedIndex].value;
	param = 'tipeasset=' + tipeasset + '&method=getsub';
	tujuan = 'keu_slave_2printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('subtipeasset').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function generateqrcode(jenis) {
	if(jenis=='asset'){
		tipeasset = document.getElementById('tipeasset').options[document.getElementById('tipeasset').selectedIndex].value;
		unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
		posisiasset = document.getElementById('posisiasset').options[document.getElementById('posisiasset').selectedIndex].value;
		subtipeasset = document.getElementById('subtipeasset').options[document.getElementById('subtipeasset').selectedIndex].value;
		param = 'tipeasset=' + tipeasset+'&unit=' + unit+'&posisiasset=' + posisiasset+'&subtipeasset=' + subtipeasset;
		if(unit==''){
			alert('Unit, Posisi Asset dan Tipe Asset wajib diisi !!!'); return;
		}
	} else if(jenis=='barang'){
		klbarang = document.getElementById('klbarang').options[document.getElementById('klbarang').selectedIndex].value;
		subklbarang = document.getElementById('subklbarang').options[document.getElementById('subklbarang').selectedIndex].value;
		kodebarang = document.getElementById('kodebarang').options[document.getElementById('kodebarang').selectedIndex].value;
		param = 'klbarang=' + klbarang+'&subklbarang=' + subklbarang+'&kodebarang=' + kodebarang;		
		if(klbarang==''||subklbarang==''){
			alert('Kelompok dan Sub Kelompok Barang wajib diisi !!!'); return;
		}
		
	}
	
	param += '&jenis=' + jenis;		
	param += '&method=generateqrcode';
	tujuan = 'keu_slave_2printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Done');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function asset(tipe) {
	var cont	  = document.getElementById('printContainer');
	unit          = document.getElementById('unit').value;
	posisiasset   = document.getElementById('posisiasset').value;
	tipeasset     = document.getElementById('tipeasset').value;
	subtipeasset  = document.getElementById('subtipeasset').value;
	if(unit==''){
		alert('Unit dan tipe asset wajib diisi !!!'); return;
	}
	fileTarget    = 'keu_slave_2printqrcode';
	param         = 'method=asset' + '&unit=' + unit + '&posisiasset=' + posisiasset + '&tipeasset=' + tipeasset+ '&subtipeasset=' + subtipeasset+'&tipe='+tipe;
	cont.innerHTML= "<iframe frameborder=0 style='width:100%;height:500px' src='" + fileTarget + ".php?" + param + "'></iframe>";
}

//====================================================================================//
// --- MASTER BARANG --- //
function barang(tipe) {
	var cont      = document.getElementById('printContainerBarang');
	klbarang      = document.getElementById('klbarang').options[document.getElementById('klbarang').selectedIndex].value;
	subklbarang   = document.getElementById('subklbarang').options[document.getElementById('subklbarang').selectedIndex].value;
	kodebarang    = document.getElementById('kodebarang').options[document.getElementById('kodebarang').selectedIndex].value;
	fileTarget    = 'keu_slave_2printqrcode';
	param         = 'method=barang' + '&klbarang=' + klbarang + '&subklbarang=' + subklbarang + '&kodebarang=' + kodebarang+'&tipe='+tipe;
	cont.innerHTML= "<iframe frameborder=0 style='width:100%;height:500px' src='" + fileTarget + ".php?" + param + "'></iframe>";
}

function getsubklbarang(klbarang) {
	param = 'klbarang=' + klbarang + '&method=getsubklbarang';
	tujuan = 'keu_slave_2printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('subklbarang').innerHTML = con.responseText;
					getkodebarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getkodebarang() {
	klbarang = document.getElementById('klbarang').value;
	subklbarang = document.getElementById('subklbarang').value;
	param = 'subklbarang=' + subklbarang + '&method=getkodebarang&klbarang='+klbarang;
	tujuan = 'keu_slave_2printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kodebarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
