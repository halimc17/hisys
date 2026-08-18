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
					alert('ERROR TRANSACTION,\n' + con.responseText);
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

function getdivisi() {
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param = 'unit=' + unit +  '&method=getdivisi';
	tujuan = 'kebun_slave_5printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('divisi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdivisitph() {
	unit = document.getElementById('unittph').options[document.getElementById('unittph').selectedIndex].value;
	param = 'unit=' + unit +  '&method=getdivisi';
	tujuan = 'kebun_slave_5printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('divisitph').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkar() {
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	divisi = document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
	mandor = document.getElementById('mandor').options[document.getElementById('mandor').selectedIndex].value;
	param = 'unit=' + unit +  '&divisi=' + divisi +  '&method=getkar';
	param += '&mandor=' + mandor;
	tujuan = 'kebun_slave_5printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('karyawan').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getmandor() {
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	divisi = document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
	param = 'unit=' + unit +  '&divisi=' + divisi +  '&method=getmandor';
	tujuan = 'kebun_slave_5printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('mandor').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getblok() {
	unit = document.getElementById('unittph').options[document.getElementById('unittph').selectedIndex].value;
	divisi = document.getElementById('divisitph').options[document.getElementById('divisitph').selectedIndex].value;
	blok = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	param = 'unit=' + unit +  '&divisi=' + divisi + '&method=getblok';
	param += '&blok=' + blok; 
	tujuan = 'kebun_slave_5printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('blok').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettph() {
	unit = document.getElementById('unittph').options[document.getElementById('unittph').selectedIndex].value;
	divisi = document.getElementById('divisitph').options[document.getElementById('divisitph').selectedIndex].value;
	param = 'unit=' + unit +  '&divisi=' + divisi + '&method=gettph';
	tujuan = 'kebun_slave_5printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('tph').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function generateqrcode(jenis) {
	if(jenis=='karyawanx'){
		karyawan = document.getElementById('karyawan').options[document.getElementById('karyawan').selectedIndex].value;
		unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
		divisi = document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
		param = 'karyawan=' + karyawan+'&unit=' + unit+'&divisi=' + divisi;
		if(unit==''){
			alert('Unit wajib diisi !!!'); return;
		}
	} else if(jenis=='tphx'){
		unit = document.getElementById('unittph').options[document.getElementById('unittph').selectedIndex].value;
		divisi = document.getElementById('divisitph').options[document.getElementById('divisitph').selectedIndex].value;
		blok = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
		tph   = document.getElementById('tph').options[document.getElementById('tph').selectedIndex].value;
		param = 'tph=' + tph+'&unit=' + unit+'&divisi=' + divisi+'&blok=' + blok;
		if(divisi==''){
			alert('divisi wajib diisi !!!'); return;
		}
		
	}
	
	param += '&jenis=' + jenis;		
	param += '&method=generateqrcode';
	tujuan = 'kebun_slave_5printqrcode.php';
	//alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
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
function karyawanx(tipe) {
	var cont	  = document.getElementById('printContainer');
	unit          = document.getElementById('unit').value;
	divisi        = document.getElementById('divisi').value;
	karyawan  	  = document.getElementById('karyawan').value;
	fileTarget    = 'kebun_slave_5printqrcode';
	param         = 'method=karyawanx' + '&unit=' + unit + '&karyawan=' + karyawan+ '&tipe=' + tipe + '&divisi=' + divisi;
	cont.innerHTML= "<iframe frameborder=0 style='width:100%;height:500px' src='" + fileTarget + ".php?" + param + "'></iframe>";
}

//====================================================================================//
// --- MASTER BARANG --- //
function tphx(tipe) {
	busy_on();
	var cont      = document.getElementById('printContainerBarang');
	unit      = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	divisi      = document.getElementById('divisitph').options[document.getElementById('divisitph').selectedIndex].value;
	tph   = document.getElementById('tph').options[document.getElementById('tph').selectedIndex].value;
	blok   = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	if(divisi==''){
		busy_off();
		alert('divisi wajib diisi !'); return;
	}
	fileTarget    = 'kebun_slave_5printqrcode';
	param         = 'method=tphx' + '&divisi=' + divisi + '&tph=' + tph +'&tipe='+tipe+'&blok='+blok;
	cont.innerHTML= "<iframe frameborder=0 style='width:100%;height:500px' src='" + fileTarget + ".php?" + param + "'></iframe>";
	busy_off();
}

function tphx2(tipe) {
	busy_on();
	var cont      = document.getElementById('printContainerBarang');
	unit      = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	divisi      = document.getElementById('divisitph').options[document.getElementById('divisitph').selectedIndex].value;
	tph   = document.getElementById('tph').options[document.getElementById('tph').selectedIndex].value;
	blok   = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	lebar   = document.getElementById('lebar').value;
	tinggi   = document.getElementById('tinggi').value;
	orientation   = document.getElementById('orientation').value;
	maxkolom   = document.getElementById('max').value;
	ukkertas   = document.getElementById('ukkertas').value;
	
	if(divisi==''){
		busy_off();
		alert('divisi wajib diisi !'); 
		return;
	}else{		
		fileTarget    = 'kebun_slave_5printqrcode';
		param         = 'method=tphx2' + '&divisi=' + divisi + '&tph=' + tph +'&tipe='+tipe+'&blok='+blok;
		param         += '&lebar='+lebar+'&tinggi='+tinggi;
		param         += '&orientation='+orientation;
		param         += '&maxkolom='+maxkolom;
		param         += '&ukkertas='+ukkertas;
		cont.innerHTML= "<iframe frameborder=0 style='width:100%;height:500px' src='" + fileTarget + ".php?" + param + "'></iframe>";
	}
	busy_off();
}

function getsubklbarang(klbarang) {
	param = 'klbarang=' + klbarang + '&method=getsubklbarang';
	tujuan = 'kebun_slave_5printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
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
	tujuan = 'kebun_slave_5printqrcode.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
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
