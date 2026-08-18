function cekpt() {
	if (document.getElementById("pt").value == "") {
		alertify.alert('Kode PT harus di pilih.');
	}
}
function showheader() {
	var tableheader = document.getElementById('tableheader');
	var showhead = document.getElementById('showhead');
	var tombolexport = document.getElementById('tombolexport');

	if (tableheader.style.display === 'none') {
		tableheader.style.display = 'block';
		showhead.innerHTML = 'Hide Filter';
		tombolexport.style.display = 'none';
	} else {
		tableheader.style.display = 'none';
		tombolexport.style.display = 'block';
		showhead.innerHTML = 'Show Filter';
	}
}
function getdetail(pt, kdorg, tt, ip, divisi, prd, tipe, akun, keg, jenis, bi, real) {
	param = 'method=html';
	param += '&pt=' + pt;
	param += '&kdorg=' + kdorg;
	param += '&tt=' + tt;
	param += '&ip=' + ip;
	param += '&divisi=' + divisi;
	param += '&prd=' + prd;
	param += '&tipe=' + tipe;
	param += '&akun=' + akun;
	param += '&keg=' + keg;
	param += '&jenis=' + jenis;
	param += '&bi=' + bi;
	param += '&real=' + real;
	tujuan = 'kebun_slave_2taksasipanendma.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('80%', '70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdetailexcel(pt, kdorg, tt, ip, divisi, prd, tipe, akun, jenis, bi, real) {
	ev = 'event';
	param = 'method=excel';
	param += '&pt=' + pt;
	param += '&kdorg=' + kdorg;
	param += '&tt=' + tt;
	param += '&ip=' + ip;
	param += '&divisi=' + divisi;
	param += '&prd=' + prd;
	param += '&tipe=' + tipe;
	param += '&akun=' + akun;
	param += '&jenis=' + jenis;
	param += '&bi=' + bi;
	param += '&real=' + real;

	printnopopup("kebun_slave_2taksasipanendma.php?" + param);
}
function getAfdelingThnTnm(unit, idObjhsl, clearData, namalang) {
	kdorg = unit.options[unit.selectedIndex].value;
	param = 'kdorg=' + kdorg + '&proses=getAfdThnTnm';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isiField = clearData.split(",");
					for (i = 0; i < isiField.length; i++) {
						document.getElementById(isiField[i]).innerHTML = "<option value=''>" + namalang + "</option>";
					}
					hsl = idObjhsl.split(",");
					balikandata = con.responseText.split("####");
					document.getElementById(hsl[0]).innerHTML = balikandata[0];
					document.getElementById(hsl[1]).innerHTML = balikandata[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}