function simpanJ() {
	kodeorg = document.getElementById('kodeorg');
	kodeorg = kodeorg.options[kodeorg.selectedIndex].value;

	periode = document.getElementById('periode').value;
	tanggalmulai = document.getElementById('tanggalmulai').value;
	tanggalsampai = document.getElementById('tanggalsampai').value;

	met = document.getElementById('method').value;

	if (trim(kodeorg) == '' || periode == '' || tanggalmulai == '' || tanggalsampai == '') {
		alert('Each Field are obligatory');
		document.getElementById('kodeorg').focus();
	} else {
		param = 'kodeorg=' + kodeorg  + '&method=' + met;
		param += '&periode=' + periode + '&tanggalmulai=' + tanggalmulai + '&tanggalsampai=' + tanggalsampai;
		tujuan = 'sdm_slave_5periodegajikecil.php';
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
	tujuan = 'sdm_slave_5periodegajikecil.php';
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

	tujuan = 'sdm_slave_5periodegajikecil.php';
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





function fillField(kodeorg, periode, tanggalmulai, tanggalsampai) {
	jk = document.getElementById('kodeorg');
	for (x = 0; x < jk.length; x++) {
		if (jk.options[x].value == kodeorg) {
			jk.options[x].selected = true;
		}
	}
	document.getElementById('kodeorg').disabled = true;


	document.getElementById('periode').value = periode;
	document.getElementById('periode').disabled = true;
	

	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalsampai').value = tanggalsampai;
	document.getElementById('method').value = 'update';
}

function cancelJ() {
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('tutup').checked = false;
	document.getElementById('periode').disabled = false;
	document.getElementById('periode').value = '';
	document.getElementById('tanggalmulai').value = '';
	document.getElementById('tanggalsampai').value = '';
	document.getElementById('method').value = 'insert';
}

function tutup(kodeorg,periode) {
	param = 'kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&method=tutup';
	tujuan = 'sdm_slave_5periodegajikecil.php';
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


function buka(kodeorg,periode) {
	param = 'kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&method=buka';
	tujuan = 'sdm_slave_5periodegajikecil.php';
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