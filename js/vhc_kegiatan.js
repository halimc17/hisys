function getjenisvhc(){
	kelvhc = document.getElementById('kelvhc').value;
	methodx = document.getElementById('method').value;
	param = 'kelvhc=' + kelvhc + '&method=getjenisvhc';
	tujuan = 'vhc_slave_save_5jenisKegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jnsvhc').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddata() {
	param = 'method=loaddata';
	tujuan = 'vhc_slave_save_5jenisKegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
			
					document.getElementById('containerxxx').innerHTML = con.responseText;
					cancelKegiatan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(kodekegiatan) {
	param = 'method=delete';
	param += '&kodekegiatan=' + kodekegiatan;
	tujuan = 'vhc_slave_save_5jenisKegiatan.php';
	if(confirm("Anda yakin untuk menghapus !")){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getKode() {
	noakun = document.getElementById('noakun').options[document.getElementById('noakun').selectedIndex].value;
	methodx = document.getElementById('method').value;
	param = 'noakun=' + noakun + '&method=getKode&methodx=' + methodx;
	tujuan = 'vhc_slave_save_5jenisKegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(methodx!='update'){						
						document.getElementById('kodekegiatan').value = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function simpanKegiatan() {
	kodekegiatan = document.getElementById('kodekegiatan').value;
	namakegiatan = document.getElementById('namakegiatan').value;
	// satuan=satuan.options[satuan.selectedIndex].value;
	satuan = document.getElementById('satuan').options[document.getElementById('satuan').selectedIndex].value;
	noakun = document.getElementById('noakun');
	noakun = noakun.options[noakun.selectedIndex].value;
	tipe = document.getElementById('tipe');
	tipe = tipe.options[tipe.selectedIndex].value;
	met = document.getElementById('method').value;
	jnsvhc = document.getElementById('jnsvhc').value;
	kelvhc = document.getElementById('kelvhc').value;
	
	if(kelvhc=='GLOBAL' && jnsvhc!='GLOBAL'){
		alert("Jika Kelompok VHC : GLOBAL maka Jenis VHC harus : GLOBAL"); return;
	}
	
	if (namakegiatan == '' || satuan == '' || noakun == '') {
		alert('Nama Kegiatan, Satuan dan Akun wajib di isi.');
		document.getElementById('kodekegiatan').focus();
	} else {
		kodekegiatan = trim(kodekegiatan);
		namakegiatan = trim(namakegiatan);
		param = 'kodekegiatan=' + kodekegiatan + '&namakegiatan=' + namakegiatan + '&satuan=' + satuan + '&method=' + met + '&noakun=' + noakun + '&tipe=' + tipe;
		param += '&jnsvhc=' + jnsvhc;
		param += '&kelvhc=' + kelvhc;
		tujuan = 'vhc_slave_save_5jenisKegiatan.php';
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function fillField(kode, nama, satuan, noakun, tipe,kelvhc,jnsvhc,nmjnsvhc) {
	document.getElementById('kodekegiatan').value = kode;
	document.getElementById('kodekegiatan').disabled = true;
	document.getElementById('namakegiatan').value = nama;
	document.getElementById('kelvhc').value = kelvhc;
	//document.getElementById('jnsvhc').value = jnsvhc;
	
	document.getElementById('jnsvhc').innerHTML="<option value='"+ jnsvhc +"'>"+ nmjnsvhc +"</option>"
	
	
	x = document.getElementById('noakun');
	for (y = 0; y < x.length; y++) {
		if (x.options[y].value == noakun)
			x.options[y].selected = true;
	}

	y = document.getElementById('satuan').value = satuan;
	xy = document.getElementById('tipe');
	for (y = 0; y < xy.length; y++) {
		if (xy.options[y].value == tipe)
			xy.options[y].selected = true;

	}
	document.getElementById('method').value = 'update';
}

function cancelKegiatan() {
	document.getElementById('kodekegiatan').disabled = true;
	document.getElementById('kodekegiatan').value = '';
	document.getElementById('namakegiatan').value = '';
	document.getElementById('satuan').selectedIndex = 0;
	document.getElementById('noakun').selectedIndex = 0;
	document.getElementById('kelvhc').selectedIndex = 0;
	document.getElementById('jnsvhc').selectedIndex = 0;
	document.getElementById('method').value = 'insert';
}