function simpanJ() {
	kodeorg = document.getElementById('kodeorg');
	kodeorg = kodeorg.options[kodeorg.selectedIndex].value;
	metodepenggajian = document.getElementById('metodepenggajian');
	metodepenggajian = metodepenggajian.options[metodepenggajian.selectedIndex].value;

	periode = document.getElementById('periode').value;
	tanggalmulai = document.getElementById('tanggalmulai').value;
	tanggalsampai = document.getElementById('tanggalsampai').value;
	kg = document.getElementById('kg').value;
	harga = document.getElementById('harga').value;
	tutup = document.getElementById('tutup');
	if (tutup.checked == true)
		tutup = 1;
	else
		tutup = 0;
	natura = document.getElementById('natura');
	if (natura.checked == true)
		natura = 1;
	else
		natura = 0;
	met = document.getElementById('method').value;

	if (trim(kodeorg) == '' || periode == '' || tanggalmulai == '' || tanggalsampai == '') {
		alert('Each Field are obligatory');
		document.getElementById('kodeorg').focus();
	} else {
		param = 'kodeorg=' + kodeorg + '&metodepenggajian=' + metodepenggajian + '&method=' + met;
		param += '&periode=' + periode + '&tanggalmulai=' + tanggalmulai + '&tanggalsampai=' + tanggalsampai;
		param += '&tutup=' + tutup;
		param += '&natura=' + natura;
		param += '&kg=' + kg;
		param += '&harga=' + harga;

		tujuan = 'sdm_slave_save_5periodeGaji.php';
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
					//document.getElementById('container').innerHTML = con.responseText;
					loaddata();
					cancelJ();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function loaddata(){
	param = 'method=loaddata';
	tujuan = 'sdm_slave_save_5periodeGaji.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function bersihkanform(){
	document.getElementById('unitcari').value='';
	document.getElementById('periodecari').value='';
	document.getElementById('statcari').value='';
	cariData();
}
function cariData(){
	unit=document.getElementById('unitcari');
	unit=unit.options[unit.selectedIndex].value;
	periodecari=document.getElementById('periodecari');
	periodecari=periodecari.options[periodecari.selectedIndex].value;
	statcari=document.getElementById('statcari');
	statcari=statcari.options[statcari.selectedIndex].value;
	param = 'method=loaddata'+'&statcari='+statcari;
	param+='&unitcari='+unit+'&periodecari='+periodecari;

	tujuan = 'sdm_slave_save_5periodeGaji.php';
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





function fillField(kodeorg, jenisgaji, periode, tanggalmulai, tanggalsampai, sudahproses,kg,harga,natura) {
	jk = document.getElementById('kodeorg');
	for (x = 0; x < jk.length; x++) {
		if (jk.options[x].value == kodeorg) {
			jk.options[x].selected = true;
		}
	}
	document.getElementById('kodeorg').disabled = true;

	jk = document.getElementById('metodepenggajian');
	for (x = 0; x < jk.length; x++) {
		if (jk.options[x].value == jenisgaji) {
			jk.options[x].selected = true;
		}
	}
	document.getElementById('metodepenggajian').disabled = true;

	document.getElementById('periode').value = periode;
	document.getElementById('periode').disabled = true;
	if (sudahproses == '1')
		document.getElementById('tutup').checked = true;
	else
		document.getElementById('tutup').checked = false;

	if (natura == '1')
		document.getElementById('natura').checked = true;
	else
		document.getElementById('natura').checked = false;

	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalsampai').value = tanggalsampai;
	document.getElementById('kg').value = kg;
	document.getElementById('harga').value = harga;
	document.getElementById('method').value = 'update';
}

function cancelJ() {
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('metodepenggajian').disabled = false;
	document.getElementById('tutup').checked = false;
	document.getElementById('natura').checked = false;
	document.getElementById('periode').disabled = false;
	document.getElementById('periode').value = '';
	document.getElementById('tanggalmulai').value = '';
	document.getElementById('tanggalsampai').value = '';
	document.getElementById('kg').value = '';
	document.getElementById('harga').value = '';

	document.getElementById('method').value = 'insert';
}

function tutup(kodeorg,jenisgaji,periode) {
	param = 'kodeorg=' + kodeorg;
	param += '&jenisgaji=' + jenisgaji;
	param += '&periode=' + periode;
	param += '&method=tutup';
	tujuan = 'sdm_slave_save_5periodeGaji.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					cariData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function buka(kodeorg,jenisgaji,periode) {
	param = 'kodeorg=' + kodeorg;
	param += '&jenisgaji=' + jenisgaji;
	param += '&periode=' + periode;
	param += '&method=buka';
	tujuan = 'sdm_slave_save_5periodeGaji.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					cariData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}