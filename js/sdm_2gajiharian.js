function prosesgajiharian(val,container) {
	param = '';
	var e = val.split('##');
	for (i = 1; i < e.length; i++) {
		var tmp = document.getElementById(e[i]);
		if (i == 1) {
			param += e[i] + "=" + getValue(e[i]);
		} else {
			param += "&" + e[i] + "=" + getValue(e[i]);
		}
	}
	method = 'estgaji';
	param += '&method='+method;
	tipekar = document.getElementById('tipekar').value;
	if(tipekar != '1'){
		tujuan = 'sdm_slave_3prosesgjharian';
	}else{
		tujuan = 'sdm_slave_prosesgjbulanan';
	}
	
	post_response_text(tujuan+'.php',param,respon);
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

function loaddata() {
	tipekar = document.getElementById('tipekar').value;
	unit = document.getElementById('unit').value;
	divisi = document.getElementById('divisi').value;
	tgl1 = document.getElementById('tgl1').value;
	tgl2 = document.getElementById('tgl2').value;
	
	param = '';
	param += '&proses=preview';
	param += '&tipekar='+tipekar;
	param += '&unit='+unit;
	param += '&divisi='+divisi;
	param += '&tgl1='+tgl1;
	param += '&tgl2='+tgl2;
	
	tujuan = 'sdm_slave_2gajiharian_v2';
	
	post_response_text(tujuan+'.php',param,respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}