// JavaScript Document
function getKar() {
	kdUnit = document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	tpKary = document.getElementById('tpKary').options[document.getElementById('tpKary').selectedIndex].value;
	golongan = document.getElementById('golongan').options[document.getElementById('golongan').selectedIndex].value;
	idKomponen = document.getElementById('idKomponen').options[document.getElementById('idKomponen').selectedIndex].value;
	jabatan = document.getElementById('jabatan').options[document.getElementById('jabatan').selectedIndex].value;
	pilDt = document.getElementById('pilInp').options[document.getElementById('pilInp').selectedIndex].value;
	param = 'method=getKar' + '&kdUnit=' + kdUnit + '&tpKary=' + tpKary + '&golongan=' + golongan + '&idKomponen=' + idKomponen;
	param += '&pilDt=' + pilDt + '&jabatan=' + jabatan;
	tujuan = 'sdm_slave_5gajipokokho';
	post_response_text(tujuan + '.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('karyawanId').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveFranco(fileTarget, passParam) {
	var passP = passParam.split('##');
	var param = "";
	for (i = 1; i < passP.length; i++) {
		var tmp = document.getElementById(passP[i]);
		if (i == 1) {
			param += passP[i] + "=" + getValue(passP[i]);
		} else {
			param += "&" + passP[i] + "=" + getValue(passP[i]);
		}
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					loadData();
					//cancelIsi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	//
	//  alert(fileTarget+'.php?proses=preview', param, respon);
	post_response_text(fileTarget + '.php', param, respon);
}
//indra
function loadData() {
	kdUnitCr = document.getElementById('kdUnitCr').options[document.getElementById('kdUnitCr').selectedIndex].value;
	opt = document.getElementById('opttahun').options[document.getElementById('opttahun').selectedIndex].value;
	nmkar = document.getElementById('nmKar').value;
	tpKar = document.getElementById('tpKaryCr').options[document.getElementById('tpKaryCr').selectedIndex].value;
	idkomp = document.getElementById('idKomponenCr').options[document.getElementById('idKomponenCr').selectedIndex].value;
	idjabatan = document.getElementById('idjabatan').options[document.getElementById('idjabatan').selectedIndex].value;
	showhide = document.getElementById('showhide');
	if (showhide.checked == true) {
		showhide = 0;
	} else {
		showhide = 1;
	}
	param = 'method=loadData' + '&optThn=' + opt;
	if (nmkar != '') {
		param += '&namaKary=' + nmkar;
	}
	if (tpKar != '') {
		param += '&tpKaryCr=' + tpKar;
	}
	if (idkomp != '') {
		param += '&idKomponenCr=' + idkomp;
	}
	if (idjabatan != '') {
		param += '&idjabatan=' + idjabatan;
	}
	if (kdUnitCr != '') {
		param += '&kdUnitCr=' + kdUnitCr;
	}
	param += '&showhide=' + showhide;
	tujuan = 'sdm_slave_5gajipokokho';
	post_response_text(tujuan + '.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
function cariBast(num) {
	kdUnitCr = document.getElementById('kdUnitCr').options[document.getElementById('kdUnitCr').selectedIndex].value;
	opt = document.getElementById('opttahun').options[document.getElementById('opttahun').selectedIndex].value;
	nmkar = document.getElementById('nmKar').value;
	tpKar = document.getElementById('tpKaryCr').options[document.getElementById('tpKaryCr').selectedIndex].value;
	idkomp = document.getElementById('idKomponenCr').options[document.getElementById('idKomponenCr').selectedIndex].value;
	param = 'method=loadData' + '&optThn=' + opt;
	if (nmkar != '') {
		param += '&namaKary=' + nmkar;
	}
	if (tpKar != '') {
		param += '&tpKaryCr=' + tpKar;
	}
	if (idkomp != '') {
		param += '&idKomponenCr=' + idkomp;
	}
	if (kdUnitCr != '') {
		param += '&kdUnitCr=' + kdUnitCr;
	}
	param += '&page=' + num;
	// alert(param);
	tujuan = 'sdm_slave_5gajipokokho.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
function fillField(thn, karywnid, tpkary, kompongj, jmlh, kdUnit, namakar, golongan) {
	document.getElementById('karyawanId').innerHTML = "<option value='" + karywnid + "'>" + namakar + "</option>"
		document.getElementById('golongan').innerHTML = "<option value='" + golongan + "'>" + golongan + "</option>"
		document.getElementById('tpKary').value = tpkary;
	document.getElementById('idKomponen').value = kompongj;
	document.getElementById('thn').value = thn;
	document.getElementById('karyawanId').value = karywnid;
	document.getElementById('karyawanId').disabled = true;
	document.getElementById('kdUnit').disabled = true;
	document.getElementById('jmlhDt').value = jmlh;
	document.getElementById('thn').value = thn;
	document.getElementById('thn').disabled = true;
	document.getElementById('golongan').disabled = true;
	document.getElementById('idKomponen').disabled = true;
	document.getElementById('tpKary').disabled = true;
	document.getElementById('method').value = 'updateData';
	document.getElementById('pilInp').disabled = true;
	document.getElementById('pilInp').value = '0';
	document.getElementById('kdUnit').value = kdUnit;
}
function cancelIsi() {
	document.getElementById('method').value = 'insert';
	document.getElementById('thn').disabled = false;
	document.getElementById('idKomponen').disabled = false;
	document.getElementById('idKomponen').value = '';
	document.getElementById('karyawanId').value = '';
	document.getElementById('tpKary').disabled = false;
	document.getElementById('pilInp').disabled = false;
	document.getElementById('kdUnit').disabled = false;
	document.getElementById('golongan').disabled = false;
	document.getElementById('golongan').value = '';
	document.getElementById('jabatan').disabled = false;
	document.getElementById('karyawanId').disabled = false;
	document.getElementById('jmlhDt').disabled = false;
}
function displatList() {
	document.getElementById('nmKar').value = '';
	document.getElementById('tpKaryCr').value = '';
	document.getElementById('idKomponenCr').value = '';
	document.getElementById('idKomponen').value = '';
	document.getElementById('kdUnitCr').value = '';
	loadData();
}
function delData(thndt, karywnid, kompongj) {
	param = 'method=delData' + '&optThn=' + thndt;
	param += '&karyawanId=' + karywnid + '&idKomponen=' + kompongj;
	tujuan = 'sdm_slave_5gajipokokho.php';
	if (confirm("Delete, are you sure ?")) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert("Done");
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//dari originalnya
function loadGaji(tahun) {
	param = 'optThn=' + tahun;
	param += '&method=loadData';
	post_response_text('sdm_slave_5gajipokokho.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//eval(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					cancelIsi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function copyTahun() {
	tahun1 = document.getElementById('tahun1');
	tahun2 = document.getElementById('tahun2');
	tahun1 = tahun1.options[tahun1.selectedIndex].value;
	tahun2 = tahun2.options[tahun2.selectedIndex].value;
	kdUnit2 = document.getElementById('kdUnit2').value;
	param = 'tahun1=' + tahun1 + '&tahun2=' + tahun2 + '&kdUnit2=' + kdUnit2;
	if (tahun2 <= tahun1) {
		alert('Destination year must greater than the source');
	} else if (kdUnit2 == '') {
		alert('Unit Was Empty');
	} else {
		if (confirm('Data on the destination year will be replace ?')) {
			if (confirm('Are you sure..?')) {
				post_response_text('sdm_slave_copyGPho.php?', param, respon);
			}
		}
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Done');
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '';
	height = '';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}
function dataKeExcel(ev, kdUnitCr, opt, nmkar, tpKar, idkomp, idjabatan) {
	param = 'method=dataDetail' + '&thn=' + opt;
	if (nmkar != '') {
		param += '&namaKary=' + nmkar;
	}
	if (tpKar != '') {
		param += '&tpKaryCr=' + tpKar;
	}
	if (idkomp != '') {
		param += '&idKomponenCr=' + idkomp;
	}
	if (idjabatan != '') {
		param += '&idjabatan=' + idjabatan;
	}
	if (kdUnitCr != '') {
		param += '&kdUnitCr=' + kdUnitCr;
	}
	// thn=document.getElementById('opttahun').options[document.getElementById('opttahun').selectedIndex].value;
	tujuan = 'sdm_slave_5gajipokokho_excel.php';
	judul = 'List Data';
	printFile(param, tujuan, judul, ev)
}