/**
 * @author repindra.ginting
 */
//=================================================sisi purchasing

function cancelsearch() {
	document.getElementById('txtNoakun').value = '';
	document.getElementById('txtsearch').value = '';
	document.getElementById('caristatusup').value = '';
	document.getElementById('caribadan').value = '';
	loadData(0);
}

function cancelsearchcalon() {
	document.getElementById('txtNoakuncalon').value = '';
	document.getElementById('txtsearchcalon').value = '';
	loadDatacalon(0);
}

function loadData(num) {
	let txtNoakun = trim(document.getElementById('txtNoakun').value);
	let txtsearch = trim(document.getElementById('txtsearch').value);
	let caristatusup = trim(document.getElementById('caristatusup').value);
	let caribadan = trim(document.getElementById('caribadan').value);

	param = 'method=loadData';
	param += '&page=' + num;
	param += '&txtsearch=' + txtsearch;
	param += '&txtNoakun=' + txtNoakun;
	param += '&caristatusup=' + caristatusup;
	param += '&caribadan=' + caribadan;
	tujuan = 'keu_slave_5reksupp.php';

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

function formListPP(title) {
	width = 900;
	height = '';
	content = "<fieldset style=width:98%><div id=containerData></div></fieldset>";
	ev = 'event';
	showDialog4(title, content, width, height, ev);
}

function detaildt(supplierid, namasupplier) {
	title = "Rincian " + namasupplier;
	formListPP(title);
	param = 'method=detaildt&supplierid=' + supplierid;
	tujuan = 'keu_slave_5reksupp.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerData').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancelAkun() {
	document.getElementById('rekening').value = '';
	document.getElementById('rekening').disabled = false;
	// document.getElementById('bank').value = '';
	document.getElementById('bank').disabled = false;
	document.getElementById('atasnama').value = '';
	document.getElementById('cabang').value = '';
	document.getElementById('kota').value = '';
	document.getElementById('negara').value = '';
	document.getElementById('matauang').value = '';
	document.getElementById('matauang').disabled = false;
	if (document.getElementById('def').checked == true) {
		document.getElementById('def').checked = false;
	}
	if (document.getElementById('statusbank').checked == true) {
		document.getElementById('statusbank').checked = false;
	}
	document.getElementById('methodAkun').value = 'insert';
	document.getElementById('id_supplier').disabled = true;
}

function saveAkun() {
	id_supplier = document.getElementById('idsupplier').value;
	nmsupp = document.getElementById('nmsupp').value;
	rekening = document.getElementById('rekening').value;
	bank = document.getElementById('bank').value;
	atasnama = document.getElementById('atasnama').value;
	cabang = document.getElementById('cabang').value;
	kota = document.getElementById('kota').value;
	negara = document.getElementById('negara').value;
	matauang = document.getElementById('matauang').value;
	def = document.getElementById('def');
	if (def.checked == true)
		def = 1;
	else
		def = 0;
	statusbank = document.getElementById('statusbank');
	if (statusbank.checked == true)
		statusbank = 1;
	else
		statusbank = 0;
	method = document.getElementById('methodAkun').value;
	if (id_supplier == '' || rekening == '' || bank == '' || atasnama == '') {
		alert('Field Was Empty');
		return;
	}
	param = 'id_supplier=' + id_supplier + '&rekening=' + rekening + '&bank=' + bank + '&atasnama=' + atasnama + '&cabang=' + cabang + '&kota=' + kota + '&negara=' + negara + '&matauang=' + matauang + '&method=' + method;
	param += '&def=' + def + '&statusbank=' + statusbank;

	tujuan = 'keu_slave_5reksupp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					detaildt(id_supplier,nmsupp);
					cancelAkun();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function editAkun(id_supplier, bank, rekening, atasnama, cabang, kota, negara, matauang, def, statusbank) {
	document.getElementById('idsupplier').value = id_supplier;
	document.getElementById('idsupplier').disabled = true;
	document.getElementById('bank').value = bank;
	document.getElementById('bank').disabled = true;
	document.getElementById('rekening').value = rekening;
	document.getElementById('rekening').disabled = true;
	document.getElementById('atasnama').value = atasnama;
	document.getElementById('cabang').value = cabang;
	document.getElementById('kota').value = kota;
	document.getElementById('negara').value = negara;
	document.getElementById('matauang').value = matauang;
	document.getElementById('matauang').disabled = true;
	if (def == '1')
		document.getElementById('def').checked = true;
	else
		document.getElementById('def').checked = false;
	if (statusbank == '1')
		document.getElementById('statusbank').checked = true;
	else
		document.getElementById('statusbank').checked = false;
	document.getElementById('methodAkun').value = 'update';
}
