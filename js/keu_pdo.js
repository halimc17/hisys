//########################################################
//#################  T A B   R E K A P  ##################
//########################################################
function pdfrekap() {
	fileTarget = 'keu_slave_pdo';
	var cont = document.getElementById('listrekap');
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	param = 'method=pdfrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	//alert(param);
	cont.innerHTML = "<iframe frameborder=0 style='width:100%;height:500px' src='" + fileTarget + ".php?" + param + "'></iframe>";
}
function excelrekap(tiperekap, ev) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	param = 'method=htmlexcelrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&tiperekap=' + tiperekap;
	tujuan = 'keu_slave_pdo.php';
	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul, ev);
}
function detailexcel(nopdo, unit, per, tiperekap, ev) {
	param = 'method=htmlexcelrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&tiperekap=' + tiperekap;
	tujuan = 'keu_slave_pdo.php';
	title = 'Report Ms.Excel';
	printFile(param, tujuan, title, ev);
}
function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '';
	height = '';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}
function htmlrekap(tiperekap) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	param = 'method=htmlexcelrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&tiperekap=' + tiperekap+'&dariRekap=1';
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listrekap').style.display = 'block';
					document.getElementById('listrekap').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//########################################################
//##############  T A B   I N C O M E  ###################
//########################################################

function getrekeningincome() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunincome = document.getElementById('noakunincome').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunincome;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbankincome').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function totalincome() {
	a = document.getElementById('fisikincome').value;
	b = document.getElementById('rupsatincome').value;
	if (a == '') {
		a = 0;
	}
	if (b == '') {
		b = 0;
	}
	c = parseFloat(a) * parseFloat(b);
	document.getElementById('totincome').innerHTML = parseFloat(c);
}
function batalincome() {
	document.getElementById('nourutincome').value = '';
	document.getElementById('akunincome').value = '';
	document.getElementById('ketincome').value = '';
	document.getElementById('satincome').value = '';
	document.getElementById('fisikincome').value = '';
	document.getElementById('rupsatincome').value = '';
	document.getElementById('noakunincome').value = '';
	document.getElementById('rekeningbankincome').value = '';
	document.getElementById('totincome').innerHTML = '';
	document.getElementById('methodincome').value = 'saveincome';
}
function editincome(nopdo, notranincome, nourutincome, akunincome, ketincome, satincome, fisikincome, rupsatincome, totincome, noakunincome, rekeningbankincome) {
	document.getElementById('nourutincome').value = nourutincome;
	document.getElementById('akunincome').value = akunincome;
	// document.getElementById('ketincome').value=ketincome;
	document.getElementById('satincome').value = satincome;
	document.getElementById('fisikincome').value = fisikincome;
	document.getElementById('rupsatincome').value = rupsatincome;
	document.getElementById('noakunincome').value = noakunincome;
	document.getElementById('rekeningbankincome').value = rekeningbankincome;
	document.getElementById('totincome').innerHTML = totincome;
	document.getElementById('methodincome').value = 'updateincome';
	getket('dana', ketincome);
}
function saveincome() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	nourutincome = document.getElementById('nourutincome').value;
	notranincome = document.getElementById('notranincome').value;
	akunincome = document.getElementById('akunincome').value;
	ketincome = document.getElementById('ketincome').value;
	satincome = document.getElementById('satincome').value;
	fisikincome = document.getElementById('fisikincome').value;
	rupsatincome = document.getElementById('rupsatincome').value;
	noakunincome = document.getElementById('noakunincome').value;
	rekeningbankincome = document.getElementById('rekeningbankincome').value;
	totincome = document.getElementById('totincome').innerHTML;
	method = document.getElementById('methodincome').value;
	if (akunincome == '' || ketincome == '' || fisikincome == '' || rupsatincome == '') {
		alert('Lengkapi Pengisian : No Akun, Keterangan, Fisik dan Rp/Sat.');
		return;
	}
	param = 'nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&akunincome=' + akunincome + '&ketincome=' + ketincome + '&satincome=' + satincome + '&notranincome=' + notranincome;
	param += '&fisikincome=' + fisikincome + '&rupsatincome=' + rupsatincome + '&totincome=' + totincome + '&nourutincome=' + nourutincome;
	param += '&method=' + method + '&noakunincome=' + noakunincome + '&rekeningbankincome=' + rekeningbankincome;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batalincome();
					listincome(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveincome2() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranincome2 = document.getElementById('notranincome2').value;
	noakunincome = document.getElementById('noakunincome').value;
	rekeningbankincome = document.getElementById('rekeningbankincome').value;
	method = document.getElementById('methodincome2').value;
	param = 'nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranincome2=' + notranincome2;
	param += '&noakunincome=' + noakunincome + '&rekeningbankincome=' + rekeningbankincome;
	param += '&method=' + method;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// batalincome();
					listincome(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detailincome(nopdo, unit, per) {
	check = document.getElementById('detailincome');
	if (check === 'undefined' || check === null) {
		document.getElementById('tabFRM1').style.display = 'none';
		listincome(nopdo, unit, per);
		return;
		// tabAction(document.getElementById('tabFRM1'),1,'FRM',7,'skyblue');
	}
	param = 'method=detailincome' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailincome').style.display = 'block';
					document.getElementById('detailincome').innerHTML = con.responseText;
					listincome(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listincome(nopdo, unit, per) {
	check = document.getElementById('listincome');
	if (check === 'undefined' || check === null) {
		getnotranbbm(nopdo, unit, per);
		return;
	}
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranincome = document.getElementById('notranincome').value;
	param = 'method=listincome' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranincome=' + notranincome;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listincome').style.display = 'block';
					document.getElementById('listincome').innerHTML = con.responseText;
					getnotranbbm(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//########################################################
//#################  T A B   P A D  ######################
//########################################################

function getrekeningpad() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunpad = document.getElementById('noakunpad').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunpad;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbankpad').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function datajumlahpad() {
	nopdo = document.getElementById('nopdo').value;
	notranpad = document.getElementById('notranpad').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	akunpad = document.getElementById('akunpad').value;
	noakunpad = document.getElementById('noakunpad').value;
	rekeningbankpad = document.getElementById('rekeningbankpad').value;
	if (akunpad == '') {
		alert('Warning : No.aruskas tidak boleh kosong.');
		return;
	}
	param = 'unit=' + unit + '&per=' + per + '&akunpad=' + akunpad + '&nopdo=' + nopdo + '&notranpad=' + notranpad;
	param += '&noakunpad=' + noakunpad + '&rekeningbankpad=' + rekeningbankpad+'&method=datajumlahpad';
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// data=con.responseText.split('###')
					// document.getElementById('totkas').value=data[0];
					// document.getElementById('akunkas').value=data[1];
					// document.getElementById('ketkas').value=data[2];
					// savekas();
					// batalpad();
					listpad(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function totalpad() {
	a = document.getElementById('fisikpad').value;
	b = document.getElementById('rupsatpad').value;
	if (a == '') {
		a = 0;
	}
	if (b == '') {
		b = 0;
	}
	c = parseFloat(a) * parseFloat(b);
	document.getElementById('totpad').innerHTML = parseFloat(c);
}
function deletepad(nopdo, notranpad, nourutpad) {
	param = 'method=deletepad' + '&nopdo=' + nopdo + '&notranpad=' + notranpad + '&nourutpad=' + nourutpad;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listpad(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batalpad() {
	document.getElementById('nourutpad').value = '';
	document.getElementById('akunpad').value = '';
	document.getElementById('ketpad').value = '';
	document.getElementById('satpad').value = '';
	document.getElementById('fisikpad').value = '';
	document.getElementById('rupsatpad').value = '';
	document.getElementById('totpad').value = '';
	document.getElementById('noakunpad').value = '';
	document.getElementById('rekeningbankpad').value = '';
	document.getElementById('methodpad').value = 'savepad';
}
function editpad(nopdo, notranpad, nourutpad, akunpad, ketpad, satpad, fisikpad, totpad, noakunpad, rekeningbankpad) {
	document.getElementById('nourutpad').value = nourutpad;
	document.getElementById('akunpad').value = akunpad;
	document.getElementById('ketpad').value = ketpad;
	document.getElementById('satpad').value = satpad;
	document.getElementById('fisikpad').value = fisikpad;
	document.getElementById('rupsatpad').value = rupsatpad;
	document.getElementById('totpad').value = totpad;
	document.getElementById('noakunpad').value = noakunpad;
	document.getElementById('rekeningbankpad').value = rekeningbankpad;
	document.getElementById('methodpad').value = 'updatepad';
	getket('pad', ketpad);
}
function savepad() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	nourutpad = document.getElementById('nourutpad').value;
	notranpad = document.getElementById('notranpad').value;
	noakunpad = document.getElementById('noakunpad').value;
	rekeningbankpad = document.getElementById('rekeningbankpad').value;
	akunpad = document.getElementById('akunpad').value;
	ketpad = document.getElementById('ketpad').value;
	// satpad=document.getElementById('satpad').value;
	// fisikpad=document.getElementById('fisikpad').value;
	// rupsatpad=document.getElementById('rupsatpad').value;
	totpad = document.getElementById('totpad').value;
	method = document.getElementById('methodpad').value;
	if (akunpad == '' || ketpad == '' || totpad == '') {
		alert('Lengkapi Pengisian');
		return;
	}
	param = 'nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&akunpad=' + akunpad + '&ketpad=' + ketpad + '&notranpad=' + notranpad;
	param += '&totpad=' + totpad + '&nourutpad=' + nourutpad;
	param += '&noakunpad=' + noakunpad + '&rekeningbankpad=' + rekeningbankpad;
	param += '&method=' + method;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batalpad();
					listpad(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listpad(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranpad = document.getElementById('notranpad').value;
	param = 'method=listpad' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranpad=' + notranpad;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listpad').style.display = 'block';
					document.getElementById('listpad').innerHTML = con.responseText;
					detailincome(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detailpad(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	param = 'method=detailpad' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailpad').style.display = 'block';
					document.getElementById('detailpad').innerHTML = con.responseText;
					listpad(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//########################################################
//#################  T A B   S P K  ######################
//########################################################
function batalspk() {
	document.getElementById('divisispk').value = '';
	document.getElementById('notranspk').value = '';
	document.getElementById('divisispk').disabled = false;
	document.getElementById('nospk').value = '';
	document.getElementById('kdsupspk').value = '';
	document.getElementById('nmsupspk').value = '';
	document.getElementById('kegspk').value = '';
	document.getElementById('tglspk1').value = '';
	document.getElementById('tglspk2').value = '';
	document.getElementById('blokspk').value = '';
	document.getElementById('satspk').value = '';
	document.getElementById('fisikspk').value = '';
	document.getElementById('hargaspk').value = '';
	document.getElementById('rptotspk').value = '';
	document.getElementById('nourutspk').value = '';
	document.getElementById('methodspk').value = 'savespk';
}
function editspk(divisispk, notranspk, nospk, kdsupspk, nmsupspk, kegspk, tglspk1, tglspk2, blokspk, satspk, fisikspk, hargaspk, rptotspk, nourutspk) {
	document.getElementById('divisispk').disabled = true;
	document.getElementById('divisispk').value = divisispk;
	document.getElementById('notranspk').value = notranspk;
	document.getElementById('nospk').value = nospk;
	document.getElementById('kdsupspk').value = kdsupspk;
	document.getElementById('nmsupspk').value = nmsupspk;
	document.getElementById('kegspk').value = kegspk;
	document.getElementById('tglspk1').value = tglspk1;
	document.getElementById('tglspk2').value = tglspk2;
	document.getElementById('blokspk').value = blokspk;
	document.getElementById('satspk').value = satspk;
	document.getElementById('fisikspk').value = fisikspk;
	document.getElementById('hargaspk').value = hargaspk;
	document.getElementById('rptotspk').value = rptotspk;
	document.getElementById('nourutspk').value = nourutspk;
	document.getElementById('methodspk').value = 'updatespk';
	getbloknotranspk(divisispk, blokspk);
}
function deletespk(nopdo, notranspk, nourutspk) {
	param = 'method=deletespk' + '&nopdo=' + nopdo + '&notranspk=' + notranspk + '&nourutspk=' + nourutspk;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listspk();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listspk(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	param = 'method=listspk' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listspk').style.display = 'block';
					document.getElementById('listspk').innerHTML = con.responseText;
					detailpad(nopdo, unit, per);
					//detailkas(nopdo,unit,per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function savespk() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	divisispk = document.getElementById('divisispk').value;
	notranspk = document.getElementById('notranspk').value;
	nospk = document.getElementById('nospk').value;
	kdsupspk = document.getElementById('kdsupspk').value;
	nmsupspk = document.getElementById('nmsupspk').value;
	kegspk = document.getElementById('kegspk').value;
	tglspk1 = document.getElementById('tglspk1').value;
	tglspk2 = document.getElementById('tglspk2').value;
	blokspk = document.getElementById('blokspk').value;
	satspk = document.getElementById('satspk').value;
	fisikspk = document.getElementById('fisikspk').value;
	hargaspk = document.getElementById('hargaspk').value;
	rptotspk = document.getElementById('rptotspk').value;
	method = document.getElementById('methodspk').value;
	nourutspk = document.getElementById('nourutspk').value;
	if (divisispk == '' || nospk == '' || kdsupspk == '' || kegspk == '' || tglspk1 == '' || tglspk2 == '' || blokspk == '' || satspk == '' || fisikspk == '' || hargaspk == '') {
		alert('Lengkapi Pengisian : Divisi, No SPK, Kegiatan, Tgl, Kontraktor, Blok, Sat, Fisik dan Harga.');
		return;
	}
	param = 'nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&divisispk=' + divisispk + '&notranspk=' + notranspk + '&nospk=' + nospk + '&kdsupspk=' + kdsupspk;
	param += '&nmsupspk=' + nmsupspk + '&kegspk=' + kegspk + '&tglspk1=' + tglspk1 + '&tglspk2=' + tglspk2;
	param += '&blokspk=' + blokspk + '&satspk=' + satspk + '&fisikspk=' + fisikspk;
	param += '&hargaspk=' + hargaspk + '&rptotspk=' + rptotspk;
	param += '&method=' + method + '&nourutspk=' + nourutspk;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (method == 'updatespk') {
						batalspk();
					} else {
						document.getElementById('blokspk').value = '';
						document.getElementById('fisikspk').value = '';
						document.getElementById('rptotspk').value = '';
						getbloknotranspk();
					}
					listspk();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function totalspk() {
	a = document.getElementById('fisikspk').value;
	b = document.getElementById('hargaspk').value;
	if (a == '') {
		a = 0;
	}
	if (b == '') {
		b = 0;
	}
	c = parseFloat(a) * parseFloat(b);
	document.getElementById('rptotspk').value = parseFloat(c);
}
/*
function detail(nopdo,unit,per,tiperekap,ev){
param = 'method=htmlexcelrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&tiperekap=' + tiperekap;
title="Data Detail";
showDialog1(title,"<iframe frameborder=0 style='width:845px;height:395px'"+
" src='keu_slave_pdo.php?"+param+"'></iframe>",'850','400',ev);
var dialog = document.getElementById('dynamic1');
dialog.style.top = '50px';
dialog.style.left = '15%';
}
 */
function movesupspk(kdsupspk, nmsupspk) {
	document.getElementById('kdsupspk').value = kdsupspk;
	document.getElementById('nmsupspk').value = nmsupspk;
	closeDialog2();
}
function carisupspk(title, ev) {
	content = "<div>";
	content += "<fieldset>Find Kontraktro :<input type=text id=textcarisupspk class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25>";
	content += "<button class=mybutton onclick=findsupspk()>Find</button>";
	content += " </fieldset>";
	content += "<div id=listsupspk style=\"height:270px;width:500px;overflow:scroll;\"></div></div>";
	title = title + ' Kontraktor :';
	width = '510';
	height = '310';
	showDialog2(title, content, width, height, ev);
}
function findsupspk() {
	textcarisupspk = trim(document.getElementById('textcarisupspk').value);
	param = 'method=carisupspklist' + '&textcarisupspk=' + textcarisupspk;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listsupspk').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getbloknotranspk(divisispk, blokspk) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	divisispk = document.getElementById('divisispk').value;
	notranspk = document.getElementById('notranspk').value;
	param = 'method=getbloknotranspk' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&divisispk=' + divisispk + '&notranspk=' + notranspk + '&blokspk=' + blokspk
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('notranspk').value = isdt[0];
					document.getElementById('blokspk').innerHTML = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//########################################################
//##############  T U T U P   S P K  #####################
//########################################################
//########################################################
//#################  T A B   B A P P  ####################
//########################################################

function getrekeningbapp() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunkasbapp = document.getElementById('noakunkasbapp').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunkasbapp;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbankbapp').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

maxfbapp = 0
	sekarangbapp = 1;
function saveallbapp(maxRowbapp) {
	maxfbapp = maxRowbapp;
	loopsavebapp(1, maxRowbapp);
}
function loopsavebapp(currRowbapp, maxRowbapp) {
	//
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranbapp = document.getElementById('notranbapp').value;
	divisibapp = document.getElementById('divisibapp').value;
	noakunkasbapp = document.getElementById('noakunkasbapp').value;
	rekeningbankbapp = document.getElementById('rekeningbankbapp').value;
	nobapp = document.getElementById('nobapp' + currRowbapp).innerHTML;
	supbapp = document.getElementById('supbapp' + currRowbapp).innerHTML;
	tglbapp = document.getElementById('tglbapp' + currRowbapp).innerHTML;
	nilbapp = document.getElementById('nilbapp' + currRowbapp).innerHTML;
	kasbapp = document.getElementById('kasbapp' + currRowbapp).innerHTML;
	sisabapp = document.getElementById('sisabapp' + currRowbapp).innerHTML;
	noakunbapp = document.getElementById('noakunbapp' + currRowbapp).value;
	nilbapp = remove_comma_var(nilbapp);
	kasbapp = remove_comma_var(kasbapp);
	sisabapp = remove_comma_var(sisabapp);
	if (document.getElementById('cekbapp' + currRowbapp).checked == true) {
		cekbapp = 1;
	} else {
		cekbapp = 0;
	}
	param = 'method=savebapp' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranbapp=' + notranbapp;
	param += '&nobapp=' + nobapp + '&supbapp=' + supbapp + '&tglbapp=' + tglbapp;
	param += '&nilbapp=' + nilbapp + '&noakunbapp=' + noakunbapp;
	param += '&kasbapp=' + kasbapp + '&sisabapp=' + sisabapp + '&cekbapp=' + cekbapp + '&divisibapp=' + divisibapp;
	param += '&noakunkasbapp=' + noakunkasbapp + '&rekeningbankbapp=' + rekeningbankbapp;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('rowbapp' + currRowbapp).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('rowbapp' + currRowbapp).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('rowbapp' + currRowbapp).style.display = 'none';
					currRowbapp += 1;
					sekarangbapp = currRowbapp;
					if (currRowbapp > maxRowbapp) {
						alert('Done');
						document.getElementById('detailbapp').style.display = 'none';
						// document.getElementById('notranbapp').value = '';
						document.getElementById('divisibapp').value = '';
						listbapp(nopdo, unit, per);
					} else {
						loopsavebapp(currRowbapp, maxRowbapp);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
				// document.getElementById('lanjut').style.display='';
				//unlockScreen();
			}
		}
	}
}
function cekallbapp() {
	drt = document.getElementById('cekallbapp');
	if (drt.checked == true) {
		chk = true;
	} else {
		chk = false;
	}
	var tbl = document.getElementById("contentdetailbapp");
	var row = tbl.rows.length;
	row = row - 1;
	for (i = 1; i <= row; i++) {
		document.getElementById('cekbapp' + i).checked = chk;
	}
}
function listbapp(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranbapp = document.getElementById('notranbapp').value;
	divisibapp = document.getElementById('divisibapp').value;
	param = 'method=listbapp' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&notranbapp=' + notranbapp + '&divisibapp=' + divisibapp;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listbapp').style.display = 'block';
					document.getElementById('listbapp').innerHTML = con.responseText;
					// listspk(nopdo,unit,per);
					//langsung ke pad
					detailpad(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnotranbapp(nopdo, unit, per) {
	splitnopdo = nopdo.split("/");
	notranbapp = splitnopdo[0] + '/' + unit + '/BAPP/001';
	document.getElementById('notranbapp').value = notranbapp;
	listbapp(nopdo, unit, per);
}

function detailbapp(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranbapp = document.getElementById('notranbapp').value;
	divisibapp = document.getElementById('divisibapp').value;
	// if (divisibapp == '') {
	// 	alert('Pilih Divisi terlebih dahulu.');
	// 	return;
	// }
	param = 'method=detailbapp' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&notranbapp=' + notranbapp + '&divisibapp=' + divisibapp;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailbapp').style.display = 'block';
					document.getElementById('detailbapp').innerHTML = con.responseText;
					// listbapp(nopdo,unit,per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editbapp(notranbapp, divisibapp,noakunkasbapp,rekeningbankbapp) {
	document.getElementById('notranbapp').value = notranbapp;
	document.getElementById('divisibapp').value = divisibapp;
	document.getElementById('noakunkasbapp').value = noakunkasbapp;
	document.getElementById('rekeningbankbapp').value = rekeningbankbapp;
	detailbapp();
	document.getElementById('listbapp').style.display = 'none';
}
function batalbapp() {
	document.getElementById('detailbapp').style.display = 'none';
	//document.getElementById('notranbapp').value = '';
	//document.getElementById('divisibapp').value = '';
	listbapp();
}
function deletebapp(nopdo, notranbapp, nourutbapp) {
	param = 'method=deletebapp' + '&nopdo=' + nopdo + '&notranbapp=' + notranbapp + '&nourutbapp=' + nourutbapp;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listbapp();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getnobapp() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranbapp = document.getElementById('notranbapp').value;
	divisibapp = document.getElementById('divisibapp').value;
	//if(divisibapp=='')
	//{
	//    alert('Divisi masih kosong');return;
	//}
	param = 'method=nobapp' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&notranbapp=' + notranbapp + '&divisibapp=' + divisibapp;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('notranbapp').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//########################################################
//###############  T A B   H U T A  N G  #################
//########################################################

function getrekeninghutang() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunkashutang = document.getElementById('noakunkashutang').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunkashutang;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbankhutang').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cekallhutang() {
	drt = document.getElementById('cekallhutang');
	if (drt.checked == true) {
		chk = true;
	} else {
		chk = false;
	}
	var tbl = document.getElementById("contentdetailhutang");
	var row = tbl.rows.length;
	row = row - 1;
	for (i = 1; i <= row; i++) {
		document.getElementById('cekhutang' + i).checked = chk;
	}
}
function edithutang(nopdo, noupah,noakunkashutang,rekeningbankhutang) {
	document.getElementById('noakunkashutang').value=noakunkashutang;
	document.getElementById('rekeningbankhutang').value=rekeningbankhutang;
	prevhutang(nopdo, noupah);
}
function batalhutang() {
	document.getElementById('detailhutang').style.display = 'none';
	listhutang();
}
function deletehutang(nopdo, notranhutang, nouruthutang) {
	param = 'method=deletehutang' + '&nopdo=' + nopdo + '&notranhutang=' + notranhutang + '&nouruthutang=' + nouruthutang;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listhutang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
maxfhutang = 0
	sekaranghutang = 1;
function saveallhutang(maxRowhutang) {
	maxfhutang = maxRowhutang;
	loopsavehutang(1, maxRowhutang);
}
function loopsavehutang(currRowhutang, maxRowhutang) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranhutang = document.getElementById('notranhutang').value;
	noakunkashutang = document.getElementById('noakunkashutang').value;
	rekeningbankhutang = document.getElementById('rekeningbankhutang').value;
	suphutang = document.getElementById('suphutang' + currRowhutang).innerHTML;
	pohutang = document.getElementById('pohutang' + currRowhutang).innerHTML;
	nilpohutang = document.getElementById('nilpohutang' + currRowhutang).innerHTML;
	ppnhutang = document.getElementById('ppnhutang' + currRowhutang).innerHTML;
	pphhutang = document.getElementById('pphhutang' + currRowhutang).innerHTML;
	kashutang = document.getElementById('kashutang' + currRowhutang).innerHTML;
	noakunhutang = document.getElementById('noakunhutang' + currRowhutang).value;
	sisahutang = document.getElementById('sisahutang' + currRowhutang).innerHTML;
	sisahutang = remove_comma_var(sisahutang);
	if (document.getElementById('cekhutang' + currRowhutang).checked == true) {
		cekhutang = 1;
	} else {
		cekhutang = 0;
	}
	param = 'method=savehutang' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranhutang=' + notranhutang + '&noakunhutang=' + noakunhutang;
	param += '&suphutang=' + suphutang + '&pohutang=' + pohutang + '&nilpohutang=' + nilpohutang + '&ppnhutang=' + ppnhutang;
	param += '&pphhutang=' + pphhutang + '&kashutang=' + kashutang + '&sisahutang=' + sisahutang + '&cekhutang=' + cekhutang;
	param += '&noakunkashutang=' + noakunkashutang + '&rekeningbankhutang=' + rekeningbankhutang;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('rowhutang' + currRowhutang).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('rowhutang' + currRowhutang).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('rowhutang' + currRowhutang).style.display = 'none';
					currRowhutang += 1;
					sekaranghutang = currRowhutang;
					if (currRowhutang > maxRowhutang) {
						alert('Done');
						batalhutang();
						listhutang(nopdo, unit, per);
						//document.location.reload();
						//document.getElementById('infoDisplay').innerHTML='';
					} else {
						loopsavehutang(currRowhutang, maxRowhutang);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
				// document.getElementById('lanjut').style.display='';
				//unlockScreen();
			}
		}
	}
}
function getnotrankas(nopdo, unit, per) {
	splitnopdo = nopdo.split("/");
	notrankas = splitnopdo[0] + '/' + unit + '/KAS/'+splitnopdo[3]+'/001';
	document.getElementById('notrankas').value = notrankas;
	listkas(nopdo, unit, per);
}
function getnotranhutang(nopdo, unit, per) {
	splitnopdo = nopdo.split("/");
	notranhutang = splitnopdo[0] + '/' + unit + '/HUTANG/'+splitnopdo[3]+'/001';
	document.getElementById('notranhutang').value = notranhutang;
	listhutang(nopdo, unit, per);
}
function prevhutang() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranhutang = document.getElementById('notranhutang').value;
	param = 'method=detailhutang' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranhutang=' + notranhutang;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailhutang').style.display = 'block';
					document.getElementById('detailhutang').innerHTML = con.responseText;
					listhutang(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function prevhutang() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranhutang = document.getElementById('notranhutang').value;
	param = 'method=detailhutang' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranhutang=' + notranhutang;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailhutang').style.display = 'block';
					document.getElementById('detailhutang').innerHTML = con.responseText;
					listhutang(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listhutang(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranhutang = document.getElementById('notranhutang').value;
	param = 'method=listhutang' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranhutang=' + notranhutang;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listhutang').style.display = 'block';
					document.getElementById('listhutang').innerHTML = con.responseText;
					getnotranbapp(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//########################################################
//########################################################
//########################################################
function getket(nmtab, ketkas) {
	if (nmtab == 'kas') {
		akunkas = document.getElementById('akunkas').value;
	}
	if (nmtab == 'dana') {
		akunkas = document.getElementById('akunincome').value;
	}
	if (nmtab == 'pad') {
		akunkas = document.getElementById('akunpad').value;
	}
	if (nmtab == 'lain') {
		akunkas = document.getElementById('akunlnn').value;
	}
	param = 'akunkas=' + akunkas + '&method=getket' + '&ketkas=' + ketkas;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (nmtab == 'kas') {
						document.getElementById('ketkas').innerHTML = con.responseText;
					}
					if (nmtab == 'dana') {
						document.getElementById('ketincome').innerHTML = con.responseText;
					}
					if (nmtab == 'pad') {
						document.getElementById('ketpad').innerHTML = con.responseText;
					}
					if (nmtab == 'lain') {
						document.getElementById('ketlnn').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//########################################################
//#################  T A B   K A S    ####################
//########################################################
function datajumlah() {
	nopdo = document.getElementById('nopdo').value;
	notrankas = document.getElementById('notrankas').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	bag = document.getElementById('bag').value;
	noakunkas = document.getElementById('noakunkas').value;
	if (noakunkas == '') {
		alert('Warning : No.akun tidak boleh kosong.');
		return;
	}
	param = 'unit=' + unit + '&per=' + per + '&noakunkas=' + noakunkas + '&nopdo=' + nopdo + '&notrankas=' + notrankas+ '&bag=' + bag;
	param += '&method=datajumlah';
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data=con.responseText.split('##');
					// document.getElementById('totkas').value=data[0];
					//document.getElementById('akunkas').innerHTML=data[0];
					document.getElementById('rekeningbank').innerHTML=data[1];
					// document.getElementById('ketkas').value=data[2];
					// savekas();
					// batalkas();
					listkas(nopdo, unit, per);
					if (data[2]!='') {
						alert(data[2]);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function totalkas() {
	a = document.getElementById('fisikkas').value;
	b = document.getElementById('rupsatkas').value;
	if (a == '') {
		a = 0;
	}
	if (b == '') {
		b = 0;
	}
	c = parseFloat(a) * parseFloat(b);
	document.getElementById('totkas').value = parseFloat(c);
}
function deletekas(nopdo, notrankas, nourutkas) {
	param = 'method=deletekas' + '&nopdo=' + nopdo + '&notrankas=' + notrankas + '&nourutkas=' + nourutkas;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listkas(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batalkas() {
	document.getElementById('nourutkas').value = '';
	document.getElementById('noakunkas').value = '';
	document.getElementById('detailupah').style.display = 'none';
}
function editkas(nopdo, notrankas, noakunkas, nourutkas, akunkas, ketkas, satkas, fisikkas, totkas) {
	document.getElementById('nourutkas').value = nourutkas;
	document.getElementById('noakunkas').value = noakunkas;
	// document.getElementById('noakunkas').disabled=true;
	document.getElementById('akunkas').value = akunkas;
	// document.getElementById('ketkas').value=ketkas;
	// document.getElementById('satkas').value=satkas;
	// document.getElementById('fisikkas').value=fisikkas;
	// document.getElementById('rupsatkas').value=rupsatkas;
	document.getElementById('totkas').value = totkas;
	document.getElementById('method').value = 'updatekas';
	getket('kas', ketkas);
}
function simpankas(maxRow) {
	maxf = maxRow;
	loopsavekas(1, maxRow);
}
function loopsavekas(currRow, maxRow) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notrankas = document.getElementById('notrankas').value;
	rekeningbank = document.getElementById('rekeningbank').value;
	notransaksix = document.getElementById('notransaksix_' + currRow).innerHTML;
	noakunkasx = document.getElementById('noakunkasx_' + currRow).innerHTML;
	noaruskasx = document.getElementById('noaruskasx_' + currRow).innerHTML;
	noakunbayarx = document.getElementById('noakunbayarx_' + currRow).innerHTML;
	ketkasx = document.getElementById('ketkasx_' + currRow).innerHTML;
	jumlahkasx = document.getElementById('jumlahkasx_' + currRow).innerHTML;
	checkedx=0;
	if(document.getElementById('kascheck_'+currRow).checked==true)
	{
	checkedx=1;
	}
	param = 'method=savekasx' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&notrankas=' + notrankas + '&notransaksix=' + notransaksix + '&noakunkasx=' + noakunkasx + '&noaruskasx=' + noaruskasx;
	param += '&noakunbayarx=' + noakunbayarx + '&ketkasx=' + ketkasx + '&jumlahkasx=' + jumlahkasx;
	param += '&checkedx=' + checkedx +'&rekeningbank=' + rekeningbank;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRow) {
						alert('Done');
						detailkas();
					} else {
						loopsavekas(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function savekas() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	nourutkas = document.getElementById('nourutkas').value;
	notrankas = document.getElementById('notrankas').value;
	noakunkas = document.getElementById('noakunkas').value;
	akunkas = document.getElementById('akunkas').value;
	ketkas = document.getElementById('ketkas').value;
	rekeningbank = document.getElementById('rekeningbank').value;
	// satkas=document.getElementById('satkas').value;
	// fisikkas=document.getElementById('fisikkas').value;
	// rupsatkas=document.getElementById('rupsatkas').value;
	totkas = document.getElementById('totkas').value;
	method = document.getElementById('method').value;
	param = 'nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&akunkas=' + akunkas + '&ketkas=' + ketkas + '&notrankas=' + notrankas;
	param += '&totkas=' + totkas + '&nourutkas=' + nourutkas + '&noakunkas=' + noakunkas;
	param += '&method=' + method;
	param += '&rekeningbank=' + rekeningbank;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//batalkas();
					listkas(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listkas(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notrankas = document.getElementById('notrankas').value;
	param = 'method=listkas' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notrankas=' + notrankas;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listkas').style.display = 'block';
					document.getElementById('listkas').innerHTML = con.responseText;
					getnotranhutang(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detailkas(nopdo, unit, per) {
	nopdo=document.getElementById('nopdo').value;
	unit=document.getElementById('unit').value;
	per=document.getElementById('per').value;
	bag=document.getElementById('bag').value;
	noakunkas=document.getElementById('noakunkas').value;
	param = 'method=detailkas' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per+  '&bag=' + bag+ '&noakunkas=' + noakunkas;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('detailkas').style.display = 'block';
					document.getElementById('detailkas').innerHTML = con.responseText;
					listkas(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function savekasdisetujui(nopdo, unit, per, notrankas, nourutkas) {
	kasdisetujui = document.getElementById('kasdisetujui_' + nourutkas).value;
	param = 'method=savekasdisetujui' + '&nopdo=' + nopdo + '&notrankas=' + notrankas + '&nourutkas=' + nourutkas + '&kasdisetujui=' + kasdisetujui;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listkas(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//########################################################
//#################  T A B   U P A H  ####################
//########################################################

function getrekeningupah() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunupah = document.getElementById('noakunupah').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunupah;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbankupah').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function listupah(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noupah = document.getElementById('noupah').value;
	divisiupah = document.getElementById('divisiupah').value;
	tkupah = document.getElementById('tkupah').value;
	//tglupah = document.getElementById('tglupah').value;
	param = 'method=listupah' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&noupah=' + noupah + '&tkupah=' + tkupah + '&divisiupah=' + divisiupah;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listupah').style.display = 'block';
					document.getElementById('listupah').innerHTML = con.responseText;
					//detailkas(nopdo, unit, per);
					getnotrankas(nopdo,unit,per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function savehead(nopdo, unit, per, bag) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	bag = document.getElementById('bag').value;
	//alert(bag);
	if (per == '') {
		alert('Periode masih kosong');
		return;
	}
	param = 'method=nopdo' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per+ '&bag=' + bag;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('savehead').disabled = true;
					document.getElementById('per').disabled = true;
					document.getElementById('bag').disabled = true;
					document.getElementById('unit').disabled = true;
					document.getElementById('nopdo').value = con.responseText;
					document.getElementById('detail').style.display = 'block';
					listupah(nopdo, unit, per); //ini nanti yg di ubah dibuat list2 untuk menampung load awal
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function prevupah() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noupah = document.getElementById('noupah').value;
	divisiupah = document.getElementById('divisiupah').value;
	tkupah = document.getElementById('tkupah').value;
	bag = document.getElementById('bag').value;
	if (tkupah == '' || divisiupah == '') {
		alert('Lengkapi Pengisian : Divisi, Tipe Karyawan dan Tanggal.');
		return;
	}
	param = 'method=noupah' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&noupah=' + noupah + '&tkupah=' + tkupah + '&bag=' + bag + '&divisiupah=' + divisiupah;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('noupah').value = con.responseText;
					detailupah();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editupah(nopdo, noupah, divisiupah, tkupah, tglupah, noakunupah, rekeningbankupah) {
	//document.getElementById('listdata').style.display = 'none';
	//document.getElementById('header').style.display = 'block';
	document.getElementById('noupah').value = noupah;
	document.getElementById('divisiupah').value = divisiupah;
	document.getElementById('tkupah').value = tkupah;
	//document.getElementById('tglupah').value = tglupah;
	document.getElementById('noakunupah').value = noakunupah;
	document.getElementById('rekeningbankupah').value = rekeningbankupah;
	detailupah();
}
function deleteupah(nopdo, noupah, nourutupah) {
	param = 'method=deleteupah' + '&nopdo=' + nopdo + '&noupah=' + noupah + '&nourutupah=' + nourutupah;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listupah(nopdo);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detailupah() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noupah = document.getElementById('noupah').value;
	divisiupah = document.getElementById('divisiupah').value;
	tkupah = document.getElementById('tkupah').value;
	bag = document.getElementById('bag').value;
	param = 'method=detailupah' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&noupah=' + noupah + '&tkupah=' + tkupah  + '&bag=' + bag  + '&divisiupah=' + divisiupah;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailupah').style.display = 'block';
					document.getElementById('detailupah').innerHTML = con.responseText;
					document.getElementById('divisiupah').disabled = true;
					document.getElementById('prevupah').disabled = true;
					document.getElementById('tkupah').disabled = true;
					listupah();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function totalhk(no) {
	a = document.getElementById('hkawal' + no).innerHTML;
	b = document.getElementById('hktengah' + no).value;
	if (a == '') {
		a = 0;
	}
	if (b == '') {
		b = 0;
	}
	c = parseFloat(a) + parseFloat(b);
	document.getElementById('hkakhir' + no).innerHTML = parseFloat(c);
}
function totalupah(no) {
	a = document.getElementById('upahawal' + no).innerHTML;
	b = document.getElementById('upahtengah' + no).value;
	if (a == '') {
		a = 0;
	}
	if (b == '') {
		b = 0;
	}
	c = parseFloat(a) + parseFloat(b);
	document.getElementById('upahakhir' + no).innerHTML = parseFloat(c);
}
function saveupah(row) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noupah = document.getElementById('noupah').value;
	divisiupah = document.getElementById('divisiupah').value;
	tkupah = document.getElementById('tkupah').value;
	noakunupah = document.getElementById('noakunupah').value;
	rekeningbankupah = document.getElementById('rekeningbankupah').value;
	comp = document.getElementById('comp' + row).value;
	hkawal = document.getElementById('hkawal' + row).innerHTML;
	upahawal = document.getElementById('upahawal' + row).innerHTML;
	orang = document.getElementById('orang' + row).innerHTML;
	param = 'method=saveupah' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&noupah=' + noupah + '&tkupah=' + tkupah + '&divisiupah=' + divisiupah;
	param += '&comp=' + comp + '&upahawal=' + upahawal;
	param += '&orang=' + orang + '&hkawal=' + hkawal;
	param += '&noakunupah=' + noakunupah + '&rekeningbankupah=' + rekeningbankupah;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('detailupah').innerHTML=con.responseText;
					detailupah();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
maxf = 0
	sekarang = 1;
function saveallupah(maxRow) {
	maxf = maxRow;
	loopsave(1, maxRow);
}
function loopsave(currRow, maxRow) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noupah = document.getElementById('noupah').value;
	divisiupah = document.getElementById('divisiupah').value;
	tkupah = document.getElementById('tkupah').value;
	//tglupah = document.getElementById('tglupah').value;
	noakunupah = document.getElementById('noakunupah').value;
	rekeningbankupah = document.getElementById('rekeningbankupah').value;
	comp = document.getElementById('comp' + currRow).value;
	upahawal = document.getElementById('upahawal' + currRow).innerHTML;
	hkawal = document.getElementById('hkawal' + currRow).innerHTML;
	orang = document.getElementById('orang' + currRow).innerHTML;
	param = 'method=saveupah' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&noupah=' + noupah + '&tkupah=' + tkupah + '&divisiupah=' + divisiupah;
	param += '&comp=' + comp + '&upahawal=' + upahawal + '&orang=' + orang + '&hkawal=' + hkawal;
	param += '&noakunupah=' + noakunupah + '&rekeningbankupah=' + rekeningbankupah;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRow) {
						alert('Done');
						batalupah();
					} else {
						loopsave(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batalupah() {
	document.getElementById('divisiupah').disabled = false;
	document.getElementById('prevupah').disabled = false;
	document.getElementById('tkupah').disabled = false;
	//document.getElementById('tglupah').disabled = false;
	document.getElementById('noakunupah').disabled = false;
	document.getElementById('rekeningbankupah').disabled = false;
	document.getElementById('detailupah').style.display = 'none';
	document.getElementById('listupah').style.display = 'block';
	noupah = document.getElementById('noupah').value = '';
	divisiupah = document.getElementById('divisiupah').value = '';
	tkupah = document.getElementById('tkupah').value = '';
	//tglupah = document.getElementById('tglupah').value = '';
	noakunupah = document.getElementById('noakunupah').value = '';
	rekeningbankupah = document.getElementById('rekeningbankupah').value = '';
	listupah();
}
function displaylist() {
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(num) {
	thnsch = document.getElementById('thnsch');
	thnsch = thnsch.options[thnsch.selectedIndex].value;
	param = 'method=loaddata&page=' + num;
	if (thnsch != '') {
		param += '&thnsch=' + thnsch;
	}
	tujuan = 'keu_slave_pdo.php';
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
function edit(nopdo, unit, per) {
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('nopdo').value = nopdo;
	document.getElementById('unit').value = unit;
	optper=document.getElementById('per');
    for(a=0;a<optper.length;a++){
        if(optper.options[a].value==per){
            optper.options[a].selected=true;
        }
    }
	savehead();
}
function cancel() {
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('nopdo').value = '';
	document.getElementById('per').value = '';
	document.getElementById('unit').value = '';
	document.getElementById('savehead').disabled = false;
	document.getElementById('per').disabled = false;
	document.getElementById('unit').disabled = false;
}
function deletehead(nopdo, unit, per) {
	param = 'method=deletehead' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
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
function posting(nopdo, unit, per) {
	param = 'method=posting' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
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

function form_ajukan(notransaksi, unit, numrow) {
	width = '300';
	height = '';
	content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&unit=' + unit + '&numrow=' + numrow;
	tujuan = 'keu_slave_pdo.php';
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
	numrow = document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					x = document.getElementById('tr_' + numrow);
					x.cells[13].innerHTML = '';
					x.cells[14].innerHTML = '';
					x.cells[15].innerHTML = '';
					alert('Sucses');
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function newdata() //indra
{
	document.getElementById('header').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	//document.getElementById('nopdo').value='';
	//document.getElementById('per').value='';
	cancel();
}
function detail(nopdo, unit, per, tiperekap, ev) {
	param = 'method=htmlexcelrekap' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&tiperekap=' + tiperekap;
	title = "Data Detail";
	showDialog1(title, "<iframe frameborder=0 style='width:845px;height:395px'" +
		" src='keu_slave_pdo.php?" + param + "'></iframe>", '850', '400', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
//####################################################
//#################  BEGIN BBM  ######################
//####################################################

function getrekeningbbm() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunbbm = document.getElementById('noakunbbm').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunbbm;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbankbbm').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnotranbbm(nopdo, unit, per) {
	splitnopdo = nopdo.split("/");
	notranbbm = splitnopdo[0] + '/' + unit + '/BBM/'+splitnopdo[3]+'/001';
	document.getElementById('notranbbm').value = notranbbm;
	listbbm(nopdo, unit, per);
}
function batalbbm() {
	document.getElementById('detailbbm').style.display = 'none';
	listbbm();
}
function prevbbm() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunbbm = document.getElementById('noakunbbm').value;
	rekeningbankbbm = document.getElementById('rekeningbankbbm').value;
	notranbbm = document.getElementById('notranbbm').value;
	param = 'method=detailbbm' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranbbm=' + notranbbm + '&noakunbbm=' + noakunbbm + '&rekeningbankbbm=' + rekeningbankbbm;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailbbm').style.display = 'block';
					document.getElementById('detailbbm').innerHTML = con.responseText;
					listbbm(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listbbm(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranbbm = document.getElementById('notranbbm').value;
	param = 'method=listbbm' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranbbm=' + notranbbm;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listbbm').style.display = 'block';
					document.getElementById('listbbm').innerHTML = con.responseText;
					getnotranio(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
maxfbbm = 0
	sekarangbbm = 1;
function saveallbbm(maxRowbbm) {
	maxfbbm = maxRowbbm;
	loopsavebbm(1, maxRowbbm);
}
function loopsavebbm(currRowbbm, maxRowbbm) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunbbm = document.getElementById('noakunbbm').value;
	rekeningbankbbm = document.getElementById('rekeningbankbbm').value;
	notranbbm = document.getElementById('notranbbm').value;
	notransaksibbm = document.getElementById('notransaksibbm' + currRowbbm).innerHTML;
	karyawanid = document.getElementById('karyawanid' + currRowbbm).innerHTML;
	jlhbbm = document.getElementById('jlhbbm' + currRowbbm).innerHTML;
	pembayaran = document.getElementById('pembayaran' + currRowbbm).innerHTML;
	jlhbbm = remove_comma_var(jlhbbm);
	pembayaran = remove_comma_var(pembayaran);
	if (document.getElementById('cekbbm' + currRowbbm).checked == true) {
		cekbbm = 1;
	} else {
		cekbbm = 0;
	}
	param = 'method=savebbm' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranbbm=' + notranbbm + '&noakunbbm=' + noakunbbm + '&rekeningbankbbm=' + rekeningbankbbm;
	param += '&notransaksibbm=' + notransaksibbm + '&karyawanid=' + karyawanid + '&jlhbbm=' + jlhbbm + '&pembayaran=' + pembayaran;
	param += '&cekbbm=' + cekbbm;
	param += '&currRowbbm=' + currRowbbm;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('rowbbm' + currRowbbm).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('rowbbm' + currRowbbm).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('rowbbm' + currRowbbm).style.display = 'none';
					currRowbbm += 1;
					sekarangbbm = currRowbbm;
					if (currRowbbm > maxRowbbm) {
						alert('Done');
						batalbbm();
						listbbm(nopdo, unit, per);
					} else {
						loopsavebbm(currRowbbm, maxRowbbm);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cekallbbm() {
	drt = document.getElementById('cekallbbm');
	if (drt.checked == true) {
		chk = true;
	} else {
		chk = false;
	}
	var tbl = document.getElementById("contentdetailbbm");
	var row = tbl.rows.length;
	row = row - 1;
	for (i = 1; i <= row; i++) {
		document.getElementById('cekbbm' + i).checked = chk;
	}
}
function editbbm(nopdo, nobbm, noakunbbm, rekeningbankbbm) {
	document.getElementById('noakunbbm').value=noakunbbm;
	document.getElementById('rekeningbankbbm').value=rekeningbankbbm;
	prevbbm(nopdo, nobbm);
}
function deletebbm(nopdo, notranbbm, nourutbbm) {
	param = 'method=deletebbm' + '&nopdo=' + nopdo + '&notranbbm=' + notranbbm + '&nourutbbm=' + nourutbbm;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listbbm();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//##################################################
//#################  END BBM  ######################
//##################################################
//################################################################
//#################  BEGIN IJIN OPERSIONAL  ######################
//################################################################
function getnotranio(nopdo, unit, per) {
	splitnopdo = nopdo.split("/");
	notranio = splitnopdo[0] + '/' + unit + '/IO/'+splitnopdo[3]+'/001';
	document.getElementById('notranio').value = notranio;
	listio(nopdo, unit, per);
}
function batalio() {
	document.getElementById('detailio').style.display = 'none';
	listio();
}

function getrekeningio(rekeningbankio) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunio = document.getElementById('noakunio').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunio + '&rekeningbank=' + rekeningbankio;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbankio').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previo() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunio = document.getElementById('noakunio').value;
	rekeningbankio = document.getElementById('rekeningbankio').value;
	notranio = document.getElementById('notranio').value;
	param = 'method=detailio' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranio=' + notranio + '&noakunio=' + noakunio + '&rekeningbankio=' + rekeningbankio;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailio').style.display = 'block';
					document.getElementById('detailio').innerHTML = con.responseText;
					listio(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listio(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranbbm = document.getElementById('notranbbm').value;
	param = 'method=listio' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranbbm=' + notranbbm;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listio').style.display = 'block';
					document.getElementById('listio').innerHTML = con.responseText;
					getnotranpjd(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
maxfio = 0
	sekarangio = 1;
function saveallio(maxRowio) {
	maxfio = maxRowio;
	loopsaveio(1, maxRowio);
}
function loopsaveio(currRowio, maxRowio) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunio = document.getElementById('noakunio').value;
	rekeningbankio = document.getElementById('rekeningbankio').value;
	notranio = document.getElementById('notranio').value;
	notransaksiio = document.getElementById('notransaksiio' + currRowio).innerHTML;
	kodevhc = document.getElementById('kodevhc' + currRowio).innerHTML;
	jenisbiaya = document.getElementById('jenisbiaya' + currRowio).innerHTML;
	biaya = document.getElementById('biaya' + currRowio).innerHTML;
	biaya = remove_comma_var(biaya);
	if (document.getElementById('cekio' + currRowio).checked == true) {
		cekio = 1;
	} else {
		cekio = 0;
	}
	param = 'method=saveio' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranio=' + notranio;
	param += '&notransaksiio=' + notransaksiio + '&kodevhc=' + kodevhc + '&jenisbiaya=' + jenisbiaya + '&biaya=' + biaya;
	param += '&cekio=' + cekio + '&noakunio=' + noakunio + '&rekeningbankio=' + rekeningbankio;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('rowio' + currRowio).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('rowio' + currRowio).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('rowio' + currRowio).style.display = 'none';
					currRowio += 1;
					sekarangio = currRowio;
					if (currRowio > maxRowio) {
						alert('Done');
						batalio();
						listio(nopdo, unit, per);
					} else {
						loopsaveio(currRowio, maxRowio);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cekallio() {
	drt = document.getElementById('cekallio');
	if (drt.checked == true) {
		chk = true;
	} else {
		chk = false;
	}
	var tbl = document.getElementById("contentdetailio");
	var row = tbl.rows.length;
	row = row - 1;
	for (i = 1; i <= row; i++) {
		document.getElementById('cekio' + i).checked = chk;
	}
}
function editio(nopdo, noio,noakunio,rekeningbankio) {
	document.getElementById('noakunio').value=noakunio;
	document.getElementById('rekeningbankio').value=rekeningbankio;
	previo(nopdo, noio);
}
function deleteio(nopdo, notranio, nourutio) {
	param = 'method=deleteio' + '&nopdo=' + nopdo + '&notranio=' + notranio + '&nourutio=' + nourutio;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listio();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//##############################################################
//#################  END IJIN OPERSIONAL  ######################
//##############################################################
//#################################################################
//#################  BEGIN PERJALANAN DINAS  ######################
//#################################################################
function getnotranpjd(nopdo, unit, per) {
	splitnopdo = nopdo.split("/");
	notranpjd = splitnopdo[0] + '/' + unit + '/PJD/'+splitnopdo[3]+'/001';
	document.getElementById('notranpjd').value = notranpjd;
	listpjd(nopdo, unit, per);
}

function getrekeningpjd(rekeningbankpjd) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunpjd = document.getElementById('noakunpjd').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunpjd + '&rekeningbank=' + rekeningbankpjd;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbankpjd').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanpjd() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	rekeningbankpjd = document.getElementById('rekeningbankpjd').value;
	noakunpjd = document.getElementById('noakunpjd').value;
	notranpjd = document.getElementById('notranpjd').value;
	unitpjd = document.getElementById('unitpjd').options[document.getElementById('unitpjd').selectedIndex].value
	totalpjd = remove_comma_var(document.getElementById('totalpjd').value);
	ketpjd = remove_comma_var(document.getElementById('ketpjd').value);
	method = document.getElementById('methodpjd').value;
	param = 'nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&notranpjd=' + notranpjd + '&unitpjd=' + unitpjd + '&totalpjd=' + totalpjd + '&ketpjd=' + ketpjd;
	param += '&rekeningbankpjd=' + rekeningbankpjd + '&noakunpjd=' + noakunpjd;
	param += '&method=' + method;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batalpjd();
					listpjd(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listpjd(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranpjd = document.getElementById('notranpjd').value;
	param = 'method=listpjd' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranpjd=' + notranpjd;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listpjd').style.display = 'block';
					document.getElementById('listpjd').innerHTML = con.responseText;
					detaillnn(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editpjd(notransaksi, total, keterangan,rekeningbankpjd,noakunpjd) {
	unit=document.getElementById('unit').value;
	document.getElementById('totalpjd').value = total;
	document.getElementById('ketpjd').value = keterangan;
	document.getElementById('noakunpjd').value = noakunpjd;
	document.getElementById('unitpjd').value = unit;
	document.getElementById('unitpjd').disabled = true;
	document.getElementById('methodpjd').value = 'updatepjd';
	getrekeningpjd(rekeningbankpjd);
}

function deletepjd(nopdo, notransaksi, urut) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	param = 'method=deletepjd' + '&nopdo=' + nopdo + '&notranpjd=' + notransaksi + '&urut=' + urut;
	tujuan = 'keu_slave_pdo.php';
	if (confirm('Delete ' + notranpjd + '\nThis transaction. are you sure?')) {
		post_response_text(tujuan, param, respon);
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listpjd(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batalpjd() {
	document.getElementById('totalpjd').value = '0';
	document.getElementById('ketpjd').value = '';
	document.getElementById('noakunpjd').value = '';
	document.getElementById('rekeningbankpjd').value = '';
	document.getElementById('unitpjd').disabled = false;
	document.getElementById('methodpjd').value = 'insertpjd';
}
//################################################################
//#################  T A B   L A I N N Y A  ######################
//################################################################

function getrekeninglnn() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	noakunlnn = document.getElementById('noakunlnn').value;
	param = 'method=getrekening' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&noakunpil=' + noakunlnn;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rekeningbanklnn').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function totallnn() {
	a = document.getElementById('fisiklnn').value;
	b = document.getElementById('rupsatlnn').value;
	if (a == '') {
		a = 0;
	}
	if (b == '') {
		b = 0;
	}
	c = parseFloat(a) * parseFloat(b);
	document.getElementById('totlnn').innerHTML = parseFloat(c);
}
function deletelnn(nopdo, notranlnn, nourutlnn) {
	param = 'method=deletelnn' + '&nopdo=' + nopdo + '&notranlnn=' + notranlnn + '&nourutlnn=' + nourutlnn;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					listlnn(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batallnn() {
	document.getElementById('nourutlnn').value = '';
	document.getElementById('akunlnn').value = '';
	document.getElementById('ketlnn').value = '';
	document.getElementById('satlnn').value = '';
	document.getElementById('fisiklnn').value = '';
	document.getElementById('rupsatlnn').value = '';
	document.getElementById('noakunlnn').value = '';
	document.getElementById('rekeningbanklnn').value = '';
	document.getElementById('totlnn').innerHTML = '';
	document.getElementById('methodlnn').value = 'savelnn';
}
function editlnn(nopdo, notranlnn, nourutlnn, akunlnn, ketlnn, satlnn, fisiklnn, rupsatlnn, totlnn, noakunlnn, rekeningbanklnn) {
	document.getElementById('nourutlnn').value = nourutlnn;
	document.getElementById('akunlnn').value = akunlnn;
	document.getElementById('ketlnn').value = ketlnn;
	document.getElementById('satlnn').value = satlnn;
	document.getElementById('fisiklnn').value = fisiklnn;
	document.getElementById('rupsatlnn').value = rupsatlnn;
	document.getElementById('noakunlnn').value = noakunlnn;
	document.getElementById('rekeningbanklnn').value = rekeningbanklnn;
	document.getElementById('totlnn').innerHTML = totlnn;
	document.getElementById('methodlnn').value = 'updatelnn';
	getket('lain', ketlnn);
}
function savelnn() {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	nourutlnn = document.getElementById('nourutlnn').value;
	notranlnn = document.getElementById('notranlnn').value;
	akunlnn = document.getElementById('akunlnn').value;
	ketlnn = document.getElementById('ketlnn').value;
	satlnn = document.getElementById('satlnn').value;
	fisiklnn = document.getElementById('fisiklnn').value;
	rupsatlnn = document.getElementById('rupsatlnn').value;
	noakunlnn = document.getElementById('noakunlnn').value;
	rekeningbanklnn = document.getElementById('rekeningbanklnn').value;
	totlnn = document.getElementById('totlnn').innerHTML;
	method = document.getElementById('methodlnn').value;
	if (akunlnn == '' || ketlnn == '' || fisiklnn == '' || rupsatlnn == '') {
		alert('Lengkapi Pengisian');
		return;
	}
	param = 'nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	param += '&akunlnn=' + akunlnn + '&ketlnn=' + ketlnn + '&satlnn=' + satlnn + '&notranlnn=' + notranlnn;
	param += '&fisiklnn=' + fisiklnn + '&rupsatlnn=' + rupsatlnn + '&totlnn=' + totlnn + '&nourutlnn=' + nourutlnn;
	param += '&noakunlnn=' + noakunlnn + '&rekeningbanklnn=' + rekeningbanklnn;
	param += '&method=' + method;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batallnn();
					listlnn(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function listlnn(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	notranlnn = document.getElementById('notranlnn').value;
	param = 'method=listlnn' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per + '&notranlnn=' + notranlnn;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listlainnya').style.display = 'block';
					document.getElementById('listlainnya').innerHTML = con.responseText;
					lockTombol(nopdo);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function lockTombol(nopdo) {
	param = 'method=lockTombol' + '&nopdo=' + nopdo;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (con.responseText=='HOLDING') {
						//document.getElementById('hutangTombol').style.display = 'block';
						document.getElementById('bappTombol').style.display = 'block';
					}else{
						//document.getElementById('hutangTombol').style.display = 'none';
						document.getElementById('bappTombol').style.display = 'none';
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function detaillnn(nopdo, unit, per) {
	nopdo = document.getElementById('nopdo').value;
	unit = document.getElementById('unit').value;
	per = document.getElementById('per').value;
	param = 'method=detaillnn' + '&nopdo=' + nopdo + '&unit=' + unit + '&per=' + per;
	tujuan = 'keu_slave_pdo.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detaillainnya').style.display = 'block';
					document.getElementById('detaillainnya').innerHTML = con.responseText;
					listlnn(nopdo, unit, per);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showupload(nopdo,notransaksi,urut,ev) {
    showformupload(ev);
    param = 'method=showupload'+'&nopdo='+nopdo+'&notransaksi='+notransaksi+'&nourut=' + urut;
	tujuan = 'keu_slave_pdo.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(nopdo,notransaksi,urut);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadfiles(nopdo,notransaksi,urut) {
    param = 'method=loadfiles&nopdo=' + nopdo + '&notransaksi=' + notransaksi + '&nourut=' + urut;
    tujuan = 'keu_slave_pdo.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfilestop') !== null) {
                        document.getElementById('listfilestop').innerHTML = con.responseText;
                    }
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('listfilesview') !== null) {
                        document.getElementById('listfilesview').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile(nopdo,notransaksi,urut) {
    var notransaksi = document.getElementById("notransupload").innerHTML;
    var nourutupload = document.getElementById("nourutupload").value;
    var nopdo = document.getElementById("nopdoupload").value;
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    formdata.append("notransaksi", notransaksi);
    formdata.append("nourut", nourutupload);
    formdata.append("nopdo", nopdo);
    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    document.getElementsByClassName("mybutton").disabled=true;
    busy_on();
    var con = createXMLHttpRequest();
    con.open("POST", "keu_slave_pdo.php?method=submitfile", true);
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
                    document.getElementsByClassName("mybutton").disabled=false;
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfiles(nopdo,notransaksi,urut);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(nopdo,notransaksi,urut,namafile) {
    param = 'method=deletefile&nopdo=' + nopdo + '&notransaksi=' + notransaksi + '&nourut=' + urut+'&namafile=' + namafile;
    tujuan = 'keu_slave_pdo.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(nopdo,notransaksi,urut);
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
                    alert(con.responseText);
                } else {}
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
    content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
    document.getElementById('dynamic2').style.display = '';
}

function lihatDetail(nopdo,tipe,ev) {
    title = "List Files";
    width = '';
    height = '';
    ev='';
    content = "<div id=contlist style='overflow:auto;width:auto;height:auto;' ></div>";
    showDialog2(title, content, width, height, ev);

    param = 'method=lihatDetail'+'&nopdo='+nopdo+'&tipe='+tipe;
	tujuan = 'keu_slave_pdo.php';
    post_response_text(tujuan, param, respog); 
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('contlist').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function detailrealisasi(unit,akunkas,tipe,per) {
    title = "Detail Realisasi";
    width = '';
    height = '';
    ev='';
    content = "<div id=contreal style='width:'500px';height:'300px';overflow:auto;'></div>";
    showDialog2(title, content, width, height, ev);

    param = 'method=detailrealisasi'+'&akunkas='+akunkas+'&tipe='+tipe+'&per='+per+'&unit='+unit;
	tujuan = 'keu_slave_pdo.php';
    post_response_text(tujuan, param, respog); 
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('contreal').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}