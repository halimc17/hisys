function excel(ev, tujuan) {
	unitexp = document.getElementById('unitexp').value;
	perexp = document.getElementById('perexp').value;
	if (unitexp == '' || perexp == '') {
		alert('Lengkapi unit dan periode.');
		return;
	}
	judul = 'Report Ms.Excel';
	param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
	printFile(param, tujuan, judul, ev);
}

function changetype() {
	if (document.getElementById('cek').checked == true) {
		document.getElementById('notarisx').type = 'text';
		document.getElementById('notarisx').value = '';
		document.getElementById('notaris').style.display = 'none';
		document.getElementById('notaris').value = '';
	} else {
		document.getElementById('notarisx').value = '';
		document.getElementById('notarisx').type = 'hidden';
		document.getElementById('notaris').value = '';
		document.getElementById('notaris').style.display = '';
	}
}

function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}

function viewexcel(pt, tipe,sumber) {
	ev = 'event';
	param = 'method=html' + '&pt=' + pt + '&tipe=' + tipe+ '&sumber=' + sumber;
	tujuan = 'lgl_slave_anggarandasar.php' + "?" + param;
	width = '';
	height = '';
	title = "Excel";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function html(pt, tipe,sumber) {
	width = '';
	height = '';
	content = "<fieldset><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog1(title, content, width, height, ev);

	param = 'method=html' + '&pt=' + pt + '&tipe=' + tipe+ '&sumber=' + sumber;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewx').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function displayList() {
	document.getElementById('divsch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detailakta').style.display = 'none';
	loaddata(0);
}

function edit(pt, jenis) {
	document.getElementById('jenis').value = jenis;
	document.getElementById('pt').value = pt;
	document.getElementById('pt').disabled = true;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	detailakta();
}

function editdetailakta(pt, jenisakta, nomorakta, tglakta, notaris, noskhakim, tglskhakim, kedudukan, alamat, modaldasar, modalsetor, kegusaha, bnri, tbnri, tglbnri, keterangan, cek) {
	if (cek == 1) {
		document.getElementById('cek').checked = true;
		document.getElementById('notarisx').type = 'text';
		document.getElementById('notarisx').value = notaris;
		document.getElementById('notaris').style.display = 'none';
		document.getElementById('notaris').value = '';

		document.getElementById('pt').value = pt;
		document.getElementById('jenisakta').value = jenisakta;
		document.getElementById('jenisakta').disabled = true;
		document.getElementById('nomorakta').value = nomorakta;
		document.getElementById('nomorakta').disabled = true;
		document.getElementById('tglakta').disabled = true;
		document.getElementById('tglakta').value = tglakta;
		document.getElementById('noskhakim').value = noskhakim;
		document.getElementById('tglskhakim').value = tglskhakim;
		document.getElementById('kedudukan').value = kedudukan;
		document.getElementById('alamat').value = alamat;
		document.getElementById('modaldasar').value = modaldasar;
		document.getElementById('modalsetor').value = modalsetor;
		document.getElementById('kegusaha').value = kegusaha;
		document.getElementById('bnri').value = bnri;
		document.getElementById('tbnri').value = tbnri;
		document.getElementById('tglbnri').value = tglbnri;
		document.getElementById('keterangan').value = keterangan;
	} else {
		document.getElementById('cek').checked = false;
		document.getElementById('notarisx').value = '';
		document.getElementById('notarisx').type = 'hidden';
		document.getElementById('notaris').value = notaris;
		document.getElementById('notaris').style.display = '';

		document.getElementById('pt').value = pt;
		document.getElementById('jenisakta').value = jenisakta;
		document.getElementById('jenisakta').disabled = true;
		document.getElementById('nomorakta').value = nomorakta;
		document.getElementById('nomorakta').disabled = true;
		document.getElementById('tglakta').disabled = true;
		document.getElementById('tglakta').value = tglakta;
		document.getElementById('noskhakim').value = noskhakim;
		document.getElementById('tglskhakim').value = tglskhakim;
		document.getElementById('kedudukan').value = kedudukan;
		document.getElementById('alamat').value = alamat;
		document.getElementById('modaldasar').value = modaldasar;
		document.getElementById('modalsetor').value = modalsetor;
		document.getElementById('kegusaha').value = kegusaha;
		document.getElementById('bnri').value = bnri;
		document.getElementById('tbnri').value = tbnri;
		document.getElementById('tglbnri').value = tglbnri;
		document.getElementById('keterangan').value = keterangan;
	}

	document.getElementById('method').value = 'updateakta';
}

function editdetailsaham(pt, noktasaham, namasaham, lembarsaham, nilaisaham, saham, noakta, tglsaham) {
	document.getElementById('pt').value = pt;
	//    document.getElementById('noktasaham').value=noktasaham;
	// document.getElementById('noktasaham').disabled=true;
	document.getElementById('namasaham').value = namasaham;
	document.getElementById('namasaham').disabled = true;
	document.getElementById('saham').value = saham;
	document.getElementById('lembarsaham').value = lembarsaham;
	document.getElementById('nilaisaham').value = nilaisaham;
	document.getElementById('noktasaham').value = noakta;
	document.getElementById('tglsaham').value = tglsaham;
	document.getElementById('tglsahamlama').value = tglsaham;
	document.getElementById('noktasaham').disabled = true;
	document.getElementById('methodsaham').value = 'updatesaham';
	gettglakta('saham', tglsaham);
}

function editdetailkom(pt, tahunkom, namakom, jabatankom, keterangankom, tglkom) {
	document.getElementById('pt').value = pt;
	document.getElementById('noakta').value = tahunkom;
	document.getElementById('noakta').disabled = true;
	document.getElementById('namakom').value = namakom;
	document.getElementById('namakom').disabled = true;
	document.getElementById('tglkom').value = tglkom;
	document.getElementById('tglkomlama').value = tglkom;
	document.getElementById('jabatankom').disabled = true;
	document.getElementById('jabatankom').value = jabatankom;
	document.getElementById('keterangankom').value = keterangankom;
	document.getElementById('methodkom').value = 'updatekom';
	gettglakta('pengurus', tglkom);
}

function deletedetail(jenis, pt, xxx, yyy, iii, tgl) {
	//akta => jenis, pt, xxx=jenisakta, yyy=nomorakta, iii='', tgl
	//saham => jenis, pt, xxx=tahun, yyy=nama, iii='', tgl
	//kom => jenis, pt, xxx=tahun, yyy=nama, iii=jabatan, tgl
	
	param = 'method=deletedetail';
	param += "&jenis=" + jenis;
	param += "&pt=" + pt;
	param += "&xxx=" + xxx;
	param += "&yyy=" + yyy;
	param += "&iii=" + iii;
	param += "&tgl=" + tgl;
	tujuan = 'lgl_slave_anggarandasar.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetailakta();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deldisabled() {
	alert('Silahkan hapus detail terlebih dahulu !');
	return;
}
function del(pt) {
	param = 'method=delete' + '&pt=' + pt;
	tujuan = 'lgl_slave_anggarandasar.php';
	if (confirm('Anda yakin ???')) {
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

function detailakta() {
	pt = document.getElementById('pt').value;
	jenis = document.getElementById('jenis').value;

	if (pt == '' || jenis == '') {
		alert('Lengkapi Pengisian');
		return;
	}
	param = 'method=detailakta';
	param += '&pt=' + pt + '&jenis=' + jenis;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailakta').style.display = 'block';
					document.getElementById('detailakta').innerHTML = con.responseText;
					loaddatadetailakta();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetailakta() {
	var notaris;
	if (document.getElementById('cek').checked == true) {
		notaris = document.getElementById('notarisx').value;
	} else {
		notaris = document.getElementById('notaris').value;
	}
	pt = document.getElementById('pt').value;
	jenis = document.getElementById('jenis').value;
	jenisakta = document.getElementById('jenisakta').value;
	nomorakta = document.getElementById('nomorakta').value;
	tglakta = document.getElementById('tglakta').value;
	noskhakim = document.getElementById('noskhakim').value;
	tglskhakim = document.getElementById('tglskhakim').value;
	kedudukan = document.getElementById('kedudukan').value;
	alamat = document.getElementById('alamat').value;
	modaldasar = document.getElementById('modaldasar').value;
	modalsetor = document.getElementById('modalsetor').value;
	kegusaha = document.getElementById('kegusaha').value;
	bnri = document.getElementById('bnri').value;
	tbnri = document.getElementById('tbnri').value;
	tglbnri = document.getElementById('tglbnri').value;
	keterangan = document.getElementById('keterangan').value;
	method = document.getElementById('method').value;

	if ((pt == '' || jenisakta == '' || nomorakta == '' || tglakta == '' || notaris == '')) {
		alert('Lengkapi Pengisian.');
		return;
	}

	param = 'pt=' + pt;
	param += '&jenis=' + jenis;
	param += '&jenisakta=' + jenisakta;
	param += '&nomorakta=' + nomorakta;
	param += '&tglakta=' + tglakta;
	param += '&notaris=' + notaris;
	param += '&noskhakim=' + noskhakim;
	param += '&tglskhakim=' + tglskhakim;
	param += '&kedudukan=' + kedudukan;
	param += '&alamat=' + alamat;
	param += '&modaldasar=' + modaldasar;
	param += '&modalsetor=' + modalsetor;
	param += '&kegusaha=' + kegusaha;
	param += '&bnri=' + bnri;
	param += '&tbnri=' + tbnri;
	param += '&tglbnri=' + tglbnri;
	param += '&keterangan=' + keterangan;

	param += '&method=' + method;

	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cleardetailakta();
					loaddatadetailakta(pt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetailsaham() {
	pt = document.getElementById('pt').value;
	//tahun=document.getElementById('tahun').value;
	namasaham = document.getElementById('namasaham').value;
	tglsaham = document.getElementById('tglsaham').value;
	tglsahamlama = document.getElementById('tglsahamlama').value;
	nomorakta = document.getElementById('noktasaham').value;
	nomorakta = document.getElementById('noktasaham').options[document.getElementById('noktasaham').selectedIndex].value;
	lembarsaham = document.getElementById('lembarsaham').value;
	nilaisaham = document.getElementById('nilaisaham').value;

	saham = document.getElementById('saham').value;
	method = document.getElementById('methodsaham').value;

	if ((pt == '' || namasaham == '' || modaldasar == '' || modalsetor == '' || saham == '' || nilaisaham == '' || lembarsaham == '' || nomorakta == '')) {
		alert('Lengkapi Pengisian.');
		return;
	}

	param = 'pt=' + pt;
	//param+='&tahun='+tahun;
	param += '&namasaham=' + namasaham;
	param += '&nomorakta=' + nomorakta;
	param += '&tglsaham=' + tglsaham;
	param += '&tglsahamlama=' + tglsahamlama;
	param += '&lembarsaham=' + lembarsaham;
	param += '&nilaisaham=' + nilaisaham;

	param += '&saham=' + saham;
	param += '&method=' + method;

	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cleardetailsaham();
					loaddatadetailsaham(pt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetailkom() {
	pt = document.getElementById('pt').value;
	noakta = document.getElementById('noakta').value;
	tglkom = document.getElementById('tglkom').value;
	tglkomlama = document.getElementById('tglkomlama').value;
	namakom = document.getElementById('namakom').value;
	jabatankom = document.getElementById('jabatankom').value;
	keterangankom = document.getElementById('keterangankom').value;
	method = document.getElementById('methodkom').value;

	if ((pt == '' || noakta == '' || namakom == '' || jabatankom == '')) {
		alert('Lengkapi Pengisian.');
		//return;
	}

	param = 'pt=' + pt;
	param += '&noakta=' + noakta;
	param += '&namakom=' + namakom;
	param += '&tglkom=' + tglkom;
	param += '&tglkomlama=' + tglkomlama;
	param += '&jabatankom=' + jabatankom;
	param += '&keterangankom=' + keterangankom;
	param += '&method=' + method;

	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cleardetailkom();
					loaddatadetailkom(pt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetailakta(pt) {
	document.getElementById('pt').disabled = true;
	pt = document.getElementById('pt').value;
	jenis = document.getElementById('jenis').value;

	param = 'method=loaddatadetailakta';
	param += '&pt=' + pt + '&jenis=' + jenis;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetailakta').innerHTML = con.responseText;
					loaddatadetailsaham(pt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetailsaham(pt) {
	document.getElementById('pt').disabled = true;
	pt = document.getElementById('pt').value;

	param = 'method=loaddatadetailsaham';
	param += '&pt=' + pt;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetailsaham').innerHTML = con.responseText;
					loaddatadetailkom(pt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetailkom(pt) {
	document.getElementById('pt').disabled = true;
	pt = document.getElementById('pt').value;

	param = 'method=loaddatadetailkom';
	param += '&pt=' + pt;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetailkom').innerHTML = con.responseText;
					getoptakta();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cleardetailakta() {
	document.getElementById('jenisakta').value = '';
	document.getElementById('jenisakta').disabled = false;
	document.getElementById('nomorakta').value = '';
	document.getElementById('nomorakta').disabled = false;
	document.getElementById('tglakta').disabled = false;
	document.getElementById('tglakta').value = '';
	document.getElementById('notaris').value = '';
	document.getElementById('notarisx').value = '';
	document.getElementById('noskhakim').value = '';
	document.getElementById('tglskhakim').value = '';
	document.getElementById('kedudukan').value = '';
	document.getElementById('alamat').value = '';
	document.getElementById('modaldasar').value = '';
	document.getElementById('modalsetor').value = '';
	document.getElementById('kegusaha').value = '';
	document.getElementById('bnri').value = '';
	document.getElementById('tbnri').value = '';
	document.getElementById('tglbnri').value = '';
	document.getElementById('keterangan').value = '';

	document.getElementById('method').value = 'insertakta';
}
function cleardetailsaham() {
	//document.getElementById('tahun').value='';
	//document.getElementById('tahun').disabled=false;
	document.getElementById('namasaham').value = '';
	document.getElementById('namasaham').disabled = false;
	document.getElementById('noktasaham').disabled = false;
	document.getElementById('tglsaham').disabled = false;
	document.getElementById('saham').value = '';
	document.getElementById('lembarsaham').value = '';
	document.getElementById('nilaisaham').value = '';
	document.getElementById('methodsaham').value = 'insertsaham';
}

function cleardetailkom() {
	document.getElementById('noakta').value = '';
	document.getElementById('noakta').disabled = false;
	document.getElementById('namakom').value = '';
	document.getElementById('namakom').disabled = false;
	document.getElementById('jabatankom').value = '';
	document.getElementById('jabatankom').disabled = false;
	document.getElementById('keterangankom').value = '';
	document.getElementById('methodkom').value = 'insertkom';
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	divsch = document.getElementById('divsch').value;
	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}

	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancel() {
	document.getElementById('detailakta').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('pt').disabled = false;
	document.getElementById('jenis').disabled = false;
	document.getElementById('pt').value = '';
	document.getElementById('jenis').value = '';
}

function getjenispt() {

	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param = 'pt=' + pt + '&method=getjenispt';

	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jenis').value = trim(con.responseText);
					// if(trim(con.responseText)!=''){
					// document.getElementById('jenis').disabled=true;
					// }else{
					// document.getElementById('jenis').disabled=false;
					// }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog2(title, content, width, height, ev);

	pos = new Array();
	pos = getMouseP(ev);

	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 200) + 'px';
	document.getElementById('dynamic2').style.display = '';

	// var dialog = document.getElementById('dynamic4');
	// dialog.style.top = '40%';

}

function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'lgl_slave_anggarandasar.php';
		post_response_text(tujuan, param, respog);
	} else {
		alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		return;
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML = con.responseText;
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

	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(ev, jenis, pt, xxx, yyy, iii) {
	showformupload(ev);
	param = "";
	param += "pt=" + pt;
	param += "&xxx=" + xxx;
	param += "&yyy=" + yyy;
	param += "&iii=" + iii;
	param += "&jenisupload=" + jenis;
	param += '&method=showupload';
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(jenis, pt, xxx, yyy, iii);
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

	if (jenis == 'kom') {
		var iii = document.getElementById('iii').innerHTML;
		formdata.append("iii", iii);
	}

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "lgl_slave_anggarandasar.php?method=submitfile", true);
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
					loadfiles(jenis, pt, xxx, yyy, iii);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewlistfile(jenis, pt, xxx, yyy, iii) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog4(title, content, width, height, ev);

	param = 'method=viewlistfile&jenisupload=' + jenis + '&pt=' + pt + '&xxx=' + xxx + '&yyy=' + yyy + '&iii=' + iii;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewz').innerHTML = con.responseText;
					loadfiles(jenis, pt, xxx, yyy, iii);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(jenis, pt, xxx, yyy, iii) {
	param = 'method=loadfiles&jenisupload=' + jenis + '&pt=' + pt + '&xxx=' + xxx + '&yyy=' + yyy + '&iii=' + iii;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
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

function deletefile(jenis, pt, xxx, yyy, iii, namafile) {
	param = "method=deletefile";
	param += "&jenisupload=" + jenis;
	param += "&pt=" + pt;
	param += "&xxx=" + xxx;
	param += "&yyy=" + yyy;
	param += "&iii=" + iii;
	param += "&namafile=" + namafile;

	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(jenis, pt, xxx, yyy, iii);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnilaisaham() {
	lembarsaham = document.getElementById('lembarsaham').value;
	nilaisaham = document.getElementById('nilaisaham').value;
	lembarsaham = remove_comma_var(lembarsaham);
	nilaisaham = remove_comma_var(nilaisaham);

	jumlah = parseFloat(lembarsaham) * parseFloat(nilaisaham);
	document.getElementById('saham').value = numberFormat(jumlah, 0);
}

function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	//Seperates the components of the number
	var components = (parseFloat(number).toFixed(digit)).split(".");
	//Comma-fies the first part
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	//Combines the two sections
	return components.join(".");
}

function getoptakta() {
	pt = document.getElementById('pt').value;
	param = "";
	param += "pt=" + pt;
	param += '&method=getoptakta';
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('noktasaham').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettglakta(jenis, tglx) {
	pt = document.getElementById('pt').value;
	if (jenis == 'saham') {
		noakta = document.getElementById('noktasaham').value;
	} else if (jenis == 'pengurus') {

		noakta = document.getElementById('noakta').value;
	}
	param = "";
	param += "pt=" + pt;
	param += "&noakta=" + noakta;
	param += "&tglaktax=" + tglx;
	param += '&method=gettglakta';
	tujuan = 'lgl_slave_anggarandasar.php';
	//alert(param);
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (jenis == 'saham') {
						document.getElementById('tglsaham').innerHTML = con.responseText;

					} else if (jenis == 'pengurus') {

						document.getElementById('tglkom').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function autodif(e) {
	var w = e.offsetWidth + "px";
	e.onkeyup = function (event) {
		autowidth(75, this.value, nomorakta)
	};
	e.onblur = function (event) {
		e.style.width = w;
	}
}
function autowidth(minwidth, value, idkolom) {
	panjang = value.length * 8;
	if (panjang > minwidth) {
		idkolom.style.width = panjang + 'px';
	}
}


function form_ajukan(pt, jenisakta,noakta,notransaksi, numrow,tanggalakta) {
	width = '350';
	height = '';
	content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:320px;max-height:150px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog5(title, content, width, height, ev);

	param = 'method=form_ajukan' + '&pt=' + pt + '&jenisakta=' + jenisakta + '&noakta=' + noakta + '&numrow=' + numrow;
	param += '&notransaksi=' + notransaksi;
	param += '&tanggalakta=' + tanggalakta;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan() {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	pt = document.getElementById('pt_aju').innerHTML;
	noakta = document.getElementById('noakta_aju').innerHTML;
	jenisakta = document.getElementById('jenisakta_aju').innerHTML;
	tanggalakta = document.getElementById('tanggalakta_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	param += '&pt=' + pt;
	param += '&noakta=' + noakta;
	param += '&jenisakta=' + jenisakta;
	param += '&tanggalakta=' + tanggalakta;

	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Sucses');
					loaddatadetailakta();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getstatuspersetujuan(notransaksi) {
	width = '650';
	height = '';
	content = "<fieldset><legend>Form</legend><div id=contview style=\"width:630px;max-height:350px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog4(title, content, width, height, ev);

	param = 'method=getstatuspersetujuan' + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_anggarandasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function frm_aju() {

	/* var tbl = document.getElementById("ppDetailTable");
	var row = tbl.rows.length;
	row=row-3;

	min=-1;
	if(row==min){
	alert('Please input the details');
	return;
	}
	else{*/
	if (confirm('Process submission ??')) {

		document.getElementById('header').style.display = 'none';
		document.getElementById('detailakta').style.display = 'none';
		document.getElementById('persetujuan').style.display = 'block';
		pt = document.getElementById('pt').value;
		jenis = document.getElementById('jenis').value;

		param = 'pt=' + pt + '&jenis=' + jenis + '&method=formPersetujuan';
		tujuan = 'lgl_slave_anggarandasar.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {

						document.getElementById('persetujuandata').innerHTML = con.responseText;

					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		post_response_text(tujuan, param, respog);
	} else {
		clear_all_data();
		displayList();
	}
	//}
}

function save_persetujuan() {
	pt = document.getElementById('pt').value;
	jenis = document.getElementById('jenis').value;
	kary = document.getElementById('karywn_id').value;

	if (kary == '') {
		alert('Please verify  your selection');
	} else {
		method = 'insert_persetujuan';
		param = 'pt=' + pt + '&jenis=' + jenis + '&usr_id=' + kary + '&method=' + method;

		tujuan = 'lgl_slave_anggarandasar.php';

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						//alert(con.responseText);
						/*document.getElementById('contain').innerHTML=con.responseText;
						displayList();*/
						loaddata();
						document.getElementById('persetujuan').style.display = 'none';
						document.getElementById('listData').style.display = 'block';

					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		//post_response_text(tujuan, param, respog);
		var answer = confirm('Are you sure?');
		if (answer) {
			post_response_text(tujuan, param, respog);
		} else {
			reset_data_setuju();
		}
	}

}