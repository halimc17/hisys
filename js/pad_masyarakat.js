function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '400';
	height = '200';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function desaexcel(ev, tujuan) {
	unitbawah = document.getElementById('unitbawah');
	unitbawah = unitbawah.options[unitbawah.selectedIndex].value;

	method = 'excel';

	param = 'unitbawah=' + unitbawah + '&method=' + method;

	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul, ev)
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	idcari=document.getElementById('idcari').value;
	namacari=document.getElementById('namacari').value;
	alamatcari=document.getElementById('alamatcari').value;
	ktpcari=document.getElementById('ktpcari').value;
	param = 'method=loaddata';
	param += "&page=" + page;
	param += "&idcari=" + idcari;
	param += "&namacari=" + namacari;
	param += "&alamatcari=" + alamatcari;
	param += "&ktpcari=" + ktpcari;
	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					cancelJabatan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkecamatan() {
	kabupaten = document.getElementById('kabupaten');
	kabupaten = kabupaten.options[kabupaten.selectedIndex].value;
	param = 'kabupaten=' + kabupaten + '&method=getkecamatan';
	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('kecamatan').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdesa() {
	kecamatan = document.getElementById('kecamatan');
	kecamatan = kecamatan.options[kecamatan.selectedIndex].value;
	param = 'kecamatan=' + kecamatan + '&method=getdesa';
	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('desa').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gantikebun() {
	unitbawah = document.getElementById('unitbawah');
	unitbawah = unitbawah.options[unitbawah.selectedIndex].value;
	param = 'unitbawah=' + unitbawah + '&method=gantikebun';
	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanJabatan() {
	pid = document.getElementById('mid').value;
	nama = document.getElementById('nama').value
		alamat = document.getElementById('alamat').value
		desa = document.getElementById('desa');
	desa = desa.options[desa.selectedIndex].value;
	kecamatan = document.getElementById('kecamatan');
	kecamatan = kecamatan.options[kecamatan.selectedIndex].value;
	kabupaten = document.getElementById('kabupaten');
	kabupaten = kabupaten.options[kabupaten.selectedIndex].value;

	ktp = document.getElementById('ktp').value;
	hp = document.getElementById('hp').value;
	kodebank = document.getElementById('kodebank');
	kodebank = kodebank.options[kodebank.selectedIndex].value;
	namapemilikrek = document.getElementById('namapemilikrek').value;
	norek = document.getElementById('norek').value;
	met = document.getElementById('method').value;

	if (trim(nama) == '' || alamat == '' || desa == '') {
		alert('Nama,Alamat,Desa are oblogatory');
		document.getElementById('nama').focus();
	} else {

		param = 'pid=' + pid + '&nama=' + nama + '&method=' + met;
		param += '&alamat=' + alamat + '&kecamatan=' + kecamatan + '&desa=' + desa;
		param += '&kabupaten=' + kabupaten + '&ktp=' + ktp + '&hp=' + hp;
		param += '&kodebank=' + kodebank + '&namapemilikrek=' + namapemilikrek + '&norek=' + norek;
		tujuan = 'pad_slave_save_masyarakat.php';
		//alert(param);
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function deletedata(pid) {
	param = 'pid=' + pid + '&method=deletedata';
	if (confirm('Anda yakin hapus item ini?')) {
		post_response_text('pad_slave_save_masyarakat.php', param, respon);
	}

	function respon() {
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

function fillField(pid, nama, alamat, desa, kecamatan, kabupaten, ktp, hp, kodebank, namapemilikrek, norek) {
	//alert('kodebank='+kodebank);
	document.getElementById('mid').value = pid;
	document.getElementById('nama').value = nama;
	document.getElementById('alamat').value = alamat;

	x = document.getElementById('kabupaten');
	for (y = 0; y < x.length; y++) {
		if (x.options[y].value == kabupaten) {
			x.options[y].selected = true;
		}
	}
	x = document.getElementById('kecamatan');
	for (y = 0; y < x.length; y++) {
		if (x.options[y].value == kecamatan) {
			x.options[y].selected = true;
		}
	}

	document.getElementById('ktp').value = ktp;
	document.getElementById('hp').value = hp;
	x = document.getElementById('kodebank');
	for (y = 0; y < x.length; y++) {
		if (x.options[y].value == kodebank) {
			x.options[y].selected = true;
		}
	}
	document.getElementById('namapemilikrek').value = namapemilikrek;
	document.getElementById('norek').value = norek;
	document.getElementById('method').value = 'update';
	getdesa2(kecamatan, desa);
	showontop();
}

function getdesa2(kecamatan, desa) {
	param = 'kecamatan=' + kecamatan + '&desa=' + desa + '&method=getdesa2';
	tujuan = 'pad_slave_save_masyarakat.php';
	//console.log(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//console.log(con.responseText);
					document.getElementById('desa').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);

	pos = new Array();
	pos = getMouseP(ev);

	/*document.getElementById('dynamic2').style.top = pos[1]+'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) +'px';
	document.getElementById('dynamic2').style.display='';*/
}

function showupload(ev, jenis, pt, xxx, yyy) {
	showformupload(ev);
	param = "";
	param += "pt=" + pt;
	param += "&xxx=" + xxx;
	param += "&yyy=" + yyy;
	param += "&jenisupload=" + jenis;
	param += '&method=showupload';
	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);
	console.log(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(jenis, pt, xxx, yyy);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile(jenis) {
	var file = document.getElementById("upload").files[0];
	var pt = document.getElementById('ptupload').innerHTML;
	var xxx = document.getElementById('xxx').innerHTML;
	var yyy = document.getElementById('yyy').innerHTML;
	var formdata = new FormData();
	formdata.append("xxx", xxx);
	formdata.append("yyy", yyy);
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("pt", pt);
	formdata.append("jenisupload", jenis);
	formdata.append("kriteria", getValue('kriteria'));

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "pad_slave_save_masyarakat.php?method=submitfile", true);
	busy_on();
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(jenis, pt, xxx, yyy);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function changekriteria(id) {
	kriteriax = getValue('kriteriax_'+id);
	param = 'method=changekriteria&kriteriax='+kriteriax+'&id='+id;
	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);
	console.log(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewlistfile(jenis, pt, xxx, yyy) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog2(title, content, width, height, ev);

	param = 'method=viewlistfile&jenisupload=' + jenis + '&pt=' + pt + '&xxx=' + xxx + '&yyy=' + yyy;
	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewz').innerHTML = con.responseText;
					loadfiles(jenis, pt, xxx, yyy);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(jenis, pt, xxx, yyy) {
	param = 'method=loadfiles&jenisupload=' + jenis + '&pt=' + pt + '&xxx=' + xxx + '&yyy=' + yyy;
	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					console.log(con.responseText);
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(jenis, pt, xxx, yyy, namafile) {
	param = "method=deletefile";
	param += "&jenisupload=" + jenis;
	param += "&pt=" + pt;
	param += "&xxx=" + xxx;
	param += "&yyy=" + yyy;
	param += "&namafile=" + namafile;

	tujuan = 'pad_slave_save_masyarakat.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(jenis, pt, xxx, yyy);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancelJabatan() {
	document.getElementById('mid').value = '';
	document.getElementById('nama').value = '';
	document.getElementById('alamat').value = '';
	document.getElementById('desa').value = '';
	document.getElementById('kecamatan').value = '';
	document.getElementById('kabupaten').value = '';
	document.getElementById('ktp').value = '';
	document.getElementById('hp').value = '';
	document.getElementById('kodebank').value = '';
	document.getElementById('namapemilikrek').value = '';
	document.getElementById('norek').value = '';
	document.getElementById('method').value = 'insert';
	batalcari();
}

function batalcari(){
	document.getElementById('idcari').value='';
	document.getElementById('namacari').value='';
	document.getElementById('alamatcari').value='';
	document.getElementById('ktpcari').value='';
}

function deleteData(pid) {
	param = 'pid=' + pid + '&method=delete';
	tujuan = 'pad_slave_save_masyarakat.php';

	if (confirm('Are you sure..?')) {
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