// JavaScript Document
//search kelompok pelanggan
function searchGruop(title, content, ev) {
	width = '500';
	height = '400';
	showDialog1(title, content, width, height, ev);
	//alert('asdasd');
}
function findGroup() {
	txt_grp = trim(document.getElementById('group_name').value);
	if (txt_grp == '') {
		alert('Text is obligatory');
	} else {
		param = 'txtfind_klp=' + txt_grp;
		tujuan = 'log_slave_get_grp_cus.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container_cari').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setGroup(kode, kelompok) {
	document.getElementById('nama_group').value = kelompok;
	document.getElementById('klcustomer_code').value = kode;
	closeDialog();
}
////search kelompok akun
function searchAkun(title, content, ev) {
	width = '500';
	height = '400';
	showDialog1(title, content, width, height, ev);
	//alert('asdasd');
}
function findAkun() {
	txt = trim(document.getElementById('no_akun').value);
	if (txt == '') {
		alertify.alert('Informasi', 'Text is obligatory');
	} else {
		param = 'txtfind=' + txt;
		tujuan = 'log_slave_get_grp_cus.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container_cari_akun').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setNoakun(no_akun, namaakun) {
	document.getElementById('nama_akun').value = namaakun;
	document.getElementById('akun_cust').value = no_akun;
	closeDialog();
}
////end dari search
////fungsi menghapus isi form-->reset
function batalPlgn() {
	document.getElementById('kode_cus').value = '';
	document.getElementById('inisial_cus').value = '';
	// document.getElementById('kode_cus').disabled=false;
	document.getElementById('klcustomer_code').value = '';
	document.getElementById('nama_group').value = '';
	document.getElementById('akun_cust').value = '';
	document.getElementById('nama_akun').value = '';
	document.getElementById('cust_nm').value = '';
	document.getElementById('kta').value = '';
	document.getElementById('tlp_cust').value = '';
	document.getElementById('kntk_person').value = '';
	document.getElementById('plafon_cus').value = '0';
	document.getElementById('n_hutang').value = '0';
	document.getElementById('toleransipenyusutan').value = '';
	document.getElementById('npwp_no').value = '';
	document.getElementById('npwp_alamat').value = '';
	document.getElementById('penandatangan').value = '';
	document.getElementById('jabatan').value = '';
	document.getElementById('seri_no').value = '';
	document.getElementById('almt').value = '';
	document.getElementById("penjualan").selectedIndex = "0";
	document.getElementById("statusinteks").selectedIndex = "0";
	document.getElementById('ketBerikat').value = '';
	document.getElementById('upload').value = '';
	document.getElementById('jenispph').value = '';
	document.getElementById('pphpersen').value = '';
	document.getElementById('carabayar').value = '';
	document.getElementById('jenispenghasilan').value = '';
	document.getElementById('ketBerikat').disabled = true;
	document.getElementById('method').value = 'insert';
	document.getElementById('chkBerikat').checked = false;
	document.getElementById('statusbebas').checked = false;
	chkKomoditi = document.getElementsByName('chkKomoditi[]');
	for (i = 0; i < chkKomoditi.length; i++) {
		chkKomoditi[i].checked = false;
	}
	loadKontakPerson();
}
function checkChkBerikat() {
	chkBerikat = document.getElementById('chkBerikat').checked;
	if (chkBerikat == true) {
		document.getElementById('ketBerikat').value = '';
		document.getElementById('ketBerikat').disabled = false;
		document.getElementById('statusbebas').disabled = true;
	} else {
		document.getElementById('ketBerikat').value = '';
		document.getElementById('ketBerikat').disabled = true;
		document.getElementById('statusbebas').disabled = false;
	}
}
function checkChkBebas() {
	statusbebas = document.getElementById('statusbebas').checked;
	if (statusbebas == true) {
		document.getElementById('chkBerikat').disabled = true;
	} else {
		document.getElementById('chkBerikat').disabled = false;
	}
}
function generatekode(namacustomer) {
	param = 'proses=generatekode&nama=' + namacustomer;
	tujuan = 'pmn_slave_5kontakperson.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('kode_cus').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
////simpan data
function simpanPlgn() {
	// alert('masukkk');
	chkKomoditi = document.getElementsByName('chkKomoditi[]');
	var vals = "";
	var countKomoditi = 0;
	for (var i = 0; i < chkKomoditi.length; i++) {
		if (chkKomoditi[i].checked) {
			vals += "," + chkKomoditi[i].value;
			countKomoditi = countKomoditi + 1;
		}
	}
	komoditi = vals.substring(1);
	if (document.getElementById('chkBerikat').checked == true) {
		berikat = 1;
	} else {
		berikat = 0;
	}
	if (document.getElementById('statusbebas').checked == true) {
		statusbebas = 1;
	} else {
		statusbebas = 0;
	}
	ketBerikat = trim(document.getElementById('ketBerikat').value);
	toleransipenyusutan = trim(document.getElementById('toleransipenyusutan').value);
	kodecustomer = trim(document.getElementById('kode_cus').value);
	inisialcustomer = trim(document.getElementById('inisial_cus').value);
	namacustomer = trim(document.getElementById('cust_nm').value);
	alamat = trim(document.getElementById('almt').value);
	kota = trim(document.getElementById('kta').value);
	telepon = trim(document.getElementById('tlp_cust').value);
	kontakperson = trim(document.getElementById('kntk_person').value);
	akun = trim(document.getElementById('akun_cust').value);
	plafon = trim(document.getElementById('plafon_cus').value);
	penjualan = trim(document.getElementById('penjualan').value);
	statusinteks = trim(document.getElementById('statusinteks').value);
	nilaihutang = trim(document.getElementById('n_hutang').value);
	npwp = trim(document.getElementById('npwp_no').value);
	npwpalamat = trim(document.getElementById('npwp_alamat').value);
	penandatangan = trim(document.getElementById('penandatangan').value);
	jabatan = trim(document.getElementById('jabatan').value);
	noseri = trim(document.getElementById('seri_no').value);
	klcustomer = trim(document.getElementById('klcustomer_code').value);
	pphpersen = trim(document.getElementById('pphpersen').value);
	jenispph = trim(document.getElementById('jenispph').value);
	carabayar = trim(document.getElementById('carabayar').value);
	jenispenghasilan = trim(document.getElementById('jenispenghasilan').value);

	method = document.getElementById('method').value;
	param = 'kodecustomer=' + kodecustomer + '&namacustomer=' + namacustomer + '&alamat=' + alamat + '&kota=' + kota + '&telepon=' + telepon + '&kontakperson=' + kontakperson;
	param += '&akun=' + akun + '&plafon=' + plafon + '&nilaihutang=' + nilaihutang + '&npwp=' + npwp + '&npwpalamat=' + npwpalamat + '&penandatangan=' + penandatangan + '&jabatan=' + jabatan + '&noseri=' + noseri + '&klcustomer=' + klcustomer + '&method=' + method;
	param += '&komoditi=' + komoditi + '&berikat=' + berikat + '&ketBerikat=' + ketBerikat + '&toleransipenyusutan=' + toleransipenyusutan + '&statusinteks=' + statusinteks;
	param += '&pphpersen=' + pphpersen + '&carabayar=' + carabayar + '&jenispph=' + jenispph + '&jenispenghasilan=' + jenispenghasilan + '&statusbebas=' + statusbebas + '&inisialcustomer=' + inisialcustomer;
	// alert(param);
	tujuan = 'log_slave_save_cust.php';
	//jenispenghasilan == '' ||carabayar == '' ||pphpersen == '' || jenispph == '' || 
	if (kodecustomer == '' || namacustomer == '' || alamat == '' || kota == '' || telepon == '' || penandatangan == '' || countKomoditi == 0) {
		alertify.alert('Informasi', 'Lengkapi Pengisian : Nama customer, Alamat, Kota, Telepon, Penandatangan, dan Komoditi');
	} else {
		// if (confirm('Anda yakin menyimpan data ??'))
		// 	post_response_text(tujuan, param, respog);

		alertify.confirm("Informasi", "Anda yakin menyimpan data ??",
			function () {
				post_response_text(tujuan, param, respog);
			},
			function () {
				return;
			}
		);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					//alert(con.responseText);
					savefile();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savefile() {
	// alert('masukk');
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.enctype = "multipart/form-data";
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("kodecustomer", getValue('kode_cus'));
	// var fileup = document.getElementById("fileupload").files[0];
	// formdata.append("file", file);
	// formdata.append("nopo", getValue('no_po'));
	// formdata.append("fileupload", getValue('fileupload'));
	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_save_cust.php?method=savefile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;

					savefilelegal();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savefilelegal() {
	// alert('masukk');
	var file = document.getElementById("uplegalitas").files[0];
	var formdata = new FormData();
	formdata.enctype = "multipart/form-data";
	formdata.append("file", file);
	formdata.append("fileupload", getValue('uplegalitas'));
	formdata.append("kodecustomer", getValue('kode_cus'));
	// var fileup = document.getElementById("fileupload").files[0];
	// formdata.append("file", file);
	// formdata.append("nopo", getValue('no_po'));
	// formdata.append("fileupload", getValue('fileupload'));
	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_save_cust.php?method=savefilelegal", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					batalPlgn();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function fillField(kodecustomer, inisialcustomer, namacustomer, alamat, kota, telepon, kontakperson, akun, plafon, nilaihutang, npwp, npwpalamat, penandatangan, jabatan, noseri, klcustomer, namaakun, kelompok, toleransipenyusutan, berikat, ketBerikat, hasilKomoditi, statusinteks, jenispph, pphpersen, carabayar, jenispenghasilan, statusbebas) {
	kode_cus = document.getElementById('kode_cus');
	kode_cus.value = kodecustomer;
	kode_cus.disabled = true;
	cust_nm = document.getElementById('cust_nm');
	cust_nm.value = namacustomer;
	inisial_cus = document.getElementById('inisial_cus');
	inisial_cus.value = inisialcustomer;
	almt = document.getElementById('almt');
	almt.value = alamat;
	kta = document.getElementById('kta');
	kta.value = kota;
	tlp_cust = document.getElementById('tlp_cust');
	tlp_cust.value = telepon;
	kntk_person = document.getElementById('kntk_person');
	kntk_person.value = kontakperson;
	akun_cust = document.getElementById('akun_cust');
	akun_cust.value = akun;
	plafon_cus = document.getElementById('plafon_cus');
	plafon_cus.value = plafon;
	n_hutang = document.getElementById('n_hutang');
	n_hutang.value = nilaihutang;
	npwp_no = document.getElementById('npwp_no');
	npwp_no.value = npwp;
	npwp_alamat = document.getElementById('npwp_alamat');
	npwp_alamat.value = npwpalamat;
	penandatanganx = document.getElementById('penandatangan');
	penandatanganx.value = penandatangan;
	jabatanx = document.getElementById('jabatan');
	jabatanx.value = jabatan;
	seri_no = document.getElementById('seri_no');
	seri_no.value = noseri;
	klcustomer_code = document.getElementById('klcustomer_code');
	klcustomer_code.value = klcustomer;
	nama_akun = document.getElementById('nama_akun');
	nama_akun.value = namaakun;
	nama_group = document.getElementById('nama_group');
	nama_group.value = kelompok;
	toleransi_penyusutan = document.getElementById('toleransipenyusutan');
	toleransi_penyusutan.value = toleransipenyusutan;
	pphpersen1 = document.getElementById('pphpersen');
	pphpersen1.value = pphpersen;
	jenispph1 = document.getElementById('jenispph');
	jenispph1.value = jenispph;
	carabayar1 = document.getElementById('carabayar');
	carabayar1.value = carabayar;
	jenispenghasilan1 = document.getElementById('jenispenghasilan');
	jenispenghasilan1.value = jenispenghasilan;
	ket_Berikat = document.getElementById('ketBerikat');
	ket_Berikat.value = ketBerikat;
	if (berikat == 1) {
		document.getElementById('chkBerikat').checked = true;
		document.getElementById('ketBerikat').disabled = false;
	} else {
		document.getElementById('chkBerikat').disabled = false;
		document.getElementById('ketBerikat').disabled = true;
	}
	chkKomoditi = document.getElementsByName('chkKomoditi[]');
	var myarray = hasilKomoditi.split(',');
	for (var i = 0; i < myarray.length; i++) {
		for (j = 0; j < chkKomoditi.length; j++) {
			if (chkKomoditi[j].value == myarray[i]) {
				chkKomoditi[j].checked = true;
			}
		}
		// alert(myarray[i]);
	}
	objstatusintext = document.getElementById('statusinteks');
	// kel=idsupplier.substring(0,4);
	for (x = 0; x < objstatusintext.length; x++) {
		if (objstatusintext.options[x].value == statusinteks) {
			objstatusintext.options[x].selected = true;
		}
	}
	objpenjualan = document.getElementById('penjualan');
	// kel=idsupplier.substring(0,4);
	for (x = 0; x < objpenjualan.length; x++) {
		if (objpenjualan.options[x].value == penjualan) {
			objpenjualan.options[x].selected = true;
		}
	}
	cat = 0;
	if (statusbebas == 1) {
		document.getElementById('statusbebas').checked = true;
	} else {
		document.getElementById('statusbebas').disabled = false;
	}

	document.getElementById('method').value = 'update';
	loadKontakPerson();
}
function delPlgn(kodecustomer) {
	param = 'kodecustomer=' + kodecustomer;
	param += '&method=delete';
	tujuan = 'log_slave_save_cust.php';
	// if (confirm('Deleting, Are you sure?'))
	// 	post_response_text(tujuan, param, respog);

	alertify.confirm('Informasi', 'Deleting, Are you sure?',
		function () {
			post_response_text(tujuan, param, respog);
		},
		function () {
			return;
		}
	);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					batalPlgn();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadKontakPerson() {
	kode_cus = document.getElementById('kode_cus').value;
	param = 'kode_cus=' + kode_cus + '&proses=loadKontakPerson';
	post_response_text('pmn_slave_5kontakperson.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('listKontakPerson').innerHTML = con.responseText;
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function loaddata() {
	namacustomer = document.getElementById('namacustomersch').value;
	param = 'method=loaddata';
	param += '&namacustomer=' + namacustomer;
	tujuan = 'log_slave_save_cust.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function addKontakPerson(id) {
	kode_cus = document.getElementById('kode_cus').value;
	nama = document.getElementById('nama_' + id).value;
	telepon = document.getElementById('telepon_' + id).value;
	email = document.getElementById('email_' + id).value;
	param = 'kode_cus=' + kode_cus + '&nama=' + nama + '&telepon=' + telepon + '&email=' + email + '&proses=addKontakPerson';
	post_response_text('pmn_slave_5kontakperson.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('kode_cus').disabled = true;
					document.getElementById('listKontakPerson').innerHTML = con.responseText;
					loadKontakPerson();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	// alert(id);
	// switchEditAdd(id,'detail');
	// addNewRow('detailBody',true);
}
function deleteKontakPerson(id) {
	param = 'idkontak=' + id + '&proses=deleteKontakPerson';
	post_response_text('pmn_slave_5kontakperson.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('listKontakPerson').innerHTML = con.responseText;
					loadKontakPerson();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formListPP(title, wdth, heig) {
	//closeDialog();
	width = '';
	height = '';
	if (wdth != '') {
		width = wdth;
	}
	if (heig != '') {
		height = heig;
	}
	content = "<div id=containerAkun></div>";
	ev = 'event';
	showDialog4(title, content, width, height, ev);
}
// Open Window - Author Atwal
function detaildt(customer_detail) {
	title = "Detail : " + customer_detail;
	width = '';
	height = '';
	formListPP(title, width, height);
	param = 'customer_detail=' + customer_detail;
	tujuan = 'pmn_5customerupload.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('containerAkun').innerHTML = con.responseText;
					loadfiles(customer_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detaildtlegal(customer_detail) {
	title = "Detail : " + customer_detail;
	width = '';
	height = '';
	formListPP(title, width, height);
	param = 'customer_detail=' + customer_detail;
	tujuan = 'pmn_5customerupload_legal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('containerAkun').innerHTML = con.responseText;
					loadfileslegal(customer_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailUnit(customer_detail) {
	width = '420';
	height = '';
	content = "<fieldset><div id=containerd style=\"width:400px;max-height:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog5(title, content, width, height, ev);
	param = 'method=detailUnit' + '&kodecustomer=' + customer_detail;
	tujuan = 'pmn_slave_5customer.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanunitcustomer() {
	customer = document.getElementById('customer').value;
	unit = document.getElementById('unit').value;
	keterangan = document.getElementById('keterangan').value;
	param = 'method=simpanunitcustomer' + '&kodecustomer=' + customer + '&kodeorg=' + unit + '&keterangan=' + keterangan;
	tujuan = 'pmn_slave_5customer.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					detailUnit(customer);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(kodecustomer) {
	param = 'method=loadfiles&kodecustomer=' + kodecustomer;
	tujuan = 'pmn_slave_5customer.php';
	// alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('containerAkundetail').innerHTML = con.responseText;
					// getPage();
					// detaildt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfileslegal(kodecustomer) {
	param = 'method=loadfiles&kodecustomer=' + kodecustomer;
	tujuan = 'pmn_slave_5customer_legal.php';
	// alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('containerAkundetail').innerHTML = con.responseText;
					// getPage();
					// detaildt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var kodecustomer = document.getElementById("kodecustomer").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("kodecustomer", kodecustomer);
	if (getValue('upload') == "") {
		alertify.alert("Informasi", "warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "pmn_slave_5customer.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(kodecustomer);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(id) {
	param = 'method=deletefile&id=' + id;
	alert(param);
	tujuan = 'pmn_slave_5customer.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					loadfiles(kodecustomer);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function downloadfile(path, filename) {
	param = 'path=' + path + '&filename=' + filename;
	tujuan = 'download.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else { }
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function showformupload(ev) {
	title = "UPLOAD FILES";
	width = 'auto';
	height = 'auto';
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showAkunPajak(customer_detail) {

	param = 'method=showformakunpajak';
	param += '&customer_detail=' + customer_detail;
	tujuan = 'log_slave_save_cust.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					// alertify.alert('Informasi',con.responseText);
					alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('50%', '30%');
					loaddatadetailpajak(customer_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savePajak() {

	customer_detail = document.getElementById('kodecustap').value
	noakun_detail = document.getElementById('noakunap').value
	persen_detail = document.getElementById('tarifap').value

	param = 'method=savePajak';
	param += '&customer_detail=' + customer_detail;
	param += '&noakun_detail=' + noakun_detail;
	param += '&persen_detail=' + persen_detail;

	tujuan = 'log_slave_save_cust.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					loaddatadetailpajak(customer_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedetailpajak(customer_detail, noakun_detail, persen_detail) {

	param = 'method=deletePajak';
	param += '&customer_detail=' + customer_detail;
	param += '&noakun_detail=' + noakun_detail;
	param += '&persen_detail=' + persen_detail;

	tujuan = 'log_slave_save_cust.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					loaddatadetailpajak(customer_detail);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetailpajak(cust) {

	param = 'method=loaddatadetailpajak';
	param += '&customer_detail=' + cust;
	tujuan = 'log_slave_save_cust.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('loaddatadetailpajak').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}