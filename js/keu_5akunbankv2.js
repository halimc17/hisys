function nonaktif(pt,noakun,status) {
	param = 'method=nonaktif&pt='+pt+'&noakun='+noakun+'&status='+status;
	tujuan = 'keu_slave_5akunbankv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getinisialurut() {
	bank = trim(document.getElementById('bank').value);
	param = 'method=getinisialurut&bank='+bank;
	tujuan = 'keu_slave_5akunbankv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('inisialurut').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getbank(unit) {
	param = 'method=getbank&pt='+unit;
	tujuan = 'keu_slave_5akunbankv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('bank').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpan() {
	pt = trim(document.getElementById('pt').value);
	noakun = trim(document.getElementById('noakun').value);
	bank = trim(document.getElementById('bank').value);
	cabang = trim(document.getElementById('cabang').value);
	rek = trim(document.getElementById('rek').value);
	atasnama = trim(document.getElementById('atasnama').value);
	matauang = trim(document.getElementById('matauang').value);
	swift_code = trim(document.getElementById('swift_code').value);
	fungsi = document.getElementById('fungsi').value;
	email = document.getElementById('email').value;
	inisialurut = document.getElementById('inisialurut').value;
	method = document.getElementById('method').value;
	noakuncoa = document.getElementById('noakuncoa').value;
	if (pt == '' || cabang == '' || bank == '' || rek==''||atasnama==''||matauang==''||noakuncoa=='') {
		alertify.alert("Informasi",'Lengkapi pengisian (ID);Please complete the form (EN)');
		return;
	}
	param = 'pt=' + pt + '&noakun=' + noakun + '&bank=' + bank + '&rek=' + rek + '&method=' + method + '&fungsi=' + fungsi+ '&noakuncoa=' + noakuncoa;
	param += '&cabang=' + cabang + '&atasnama=' + atasnama + '&matauang=' + matauang + '&swift_code=' + swift_code + '&inisialurut=' + inisialurut+ '&email=' + email;
	tujuan = 'keu_slave_5akunbankv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					hapus();
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function hapussch() {
	document.getElementById('unitsch').value='';
	document.getElementById('banksch').value='';
	document.getElementById('reksch').value='';
	loadData();
}
function hapus() {
	document.getElementById('method').value = 'insert';
	document.getElementById('pt').disabled = false;
	document.getElementById('noakuncoa').value = '';
	document.getElementById('pt').value = '';
	document.getElementById('noakun').value = '';
	document.getElementById('bank').value = '';
	document.getElementById('rek').value = '';
	document.getElementById('pt').disabled = false;
	document.getElementById('noakun').disabled = false;
	document.getElementById('bank').disabled = false;
	document.getElementById('rek').disabled = false;
	document.getElementById('cabang').value='';
	document.getElementById('atasnama').value='';
	document.getElementById('matauang').value='';
	document.getElementById('inisialurut').value='';
	document.getElementById('swift_code').value='';
	document.getElementById('fungsi').value='';
	document.getElementById('email').value='';
}
function loadData() {
	unitsch = document.getElementById('unitsch').value;
	banksch = document.getElementById('banksch').value;
	reksch = document.getElementById('reksch').value;
	
	param = 'unitsch=' + unitsch;
	param += '&banksch=' + banksch;
	param += '&reksch=' + reksch;
	param += '&method=loadData';
	tujuan = 'keu_slave_5akunbankv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
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
function fillField(pt, noakun, bank,cabang,rek,atasnama,matauang,swift_code,fungsi,email,inisialurut,noakuncoa) {
	document.getElementById('noakuncoa').value = noakuncoa;
	document.getElementById('pt').value = pt;
	document.getElementById('noakun').value = noakun;
	document.getElementById('inisialurut').value = inisialurut;
	document.getElementById('bank').value = bank;
	document.getElementById('rek').value = rek;
	document.getElementById('pt').disabled = true;
	document.getElementById('noakun').disabled = true;
	// document.getElementById('bank').disabled = true;
	document.getElementById('rek').disabled = true;
	document.getElementById('cabang').value=cabang;
	document.getElementById('atasnama').value=atasnama;
	document.getElementById('matauang').value=matauang;
	document.getElementById('swift_code').value=swift_code;
	document.getElementById('fungsi').value=fungsi;
	document.getElementById('email').value=email;
	document.getElementById('method').value = 'update';
}
