function gettgl(){
	tgl = document.getElementById('tglData').value;
	document.getElementById('tglData2').value=tgl;
}

function saveData(tipe) {
	kdOrg = document.getElementById('idKbn').value;
	tgl = document.getElementById('tglData').value;
	tgl2 = document.getElementById('tglData2').value;
	if (tgl == '') {
		alert("Please Insert Date/Tanggal");
		return;
	}
	if (tgl.substr(-7, 7) != tgl2.substr(-7, 7)) {
		alert("Start and End Date must be same month !!");
		return;
	}

	param = 'kdOrg=' + kdOrg + '&tgl=' + tgl + '&tipe=' + tipe;
	param += '&tgl2=' + tgl2;
	param += '&intex=' + getValue('intex')
	param += '&proses=getData'
	tujuan = 'kebun_slave_3AmbilKgTimbangan_v2.php';
	if(tipe!='excel'){
		post_response_text(tujuan, param, respon);
	}else{
		printnopopup(tujuan+'?'+param);
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					//alertify.alert(con.responseText);
					document.getElementById('result').style.display = 'block';
					document.getElementById('list_ganti').innerHTML = con.responseText;
					document.getElementById('idKbn').disabled = true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function postingbro() {
	kdOrg = document.getElementById('idKbn').value;
	tgl = document.getElementById('tglData').value;
	tgl2 = document.getElementById('tglData2').value;
	if (tgl == '') {
		alert("Please Insert Date/Tanggal");
		return;
	}
	if (tgl.substr(-7, 7) != tgl2.substr(-7, 7)) {
		alert("Start and End Date must be same month !!");
		return;
	}

	param = 'kdOrg=' + kdOrg + '&tgl=' + tgl;
	param += '&tgl2=' + tgl2;
	param += '&intex=' + getValue('intex')
	param += '&proses=postingbro'
	tujuan = 'kebun_slave_3AmbilKgTimbangan_v2.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					alertify.alert(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function cancelSave() {
	document.getElementById('list_ganti').innerHTML = '';
	document.getElementById('idKbn').disabled = false;
	document.getElementById('tglData').disabled = false;
	document.getElementById('tglData2').disabled = false;
	document.getElementById('dtl_pem').disabled = false;
	document.getElementById('dtl_pem2').disabled = false;
	document.getElementById('btnexcel').disabled = false;
	document.getElementById('idKbn').value = '';
	document.getElementById('tglData').value = '';
	document.getElementById('tglData2').value = '';
	document.getElementById('result').style.display = 'none';

}


function recektimbangan() {
	kdOrg = document.getElementById('idKbnx').value;
	tgl = document.getElementById('tglDatax').value;
	tgl2 = document.getElementById('tglData2x').value;
	if (tgl == '') {
		alert("Please Insert Date/Tanggal");
		return;
	}
	if (tgl.substr(-7, 7) != tgl2.substr(-7, 7)) {
		alert("Start and End Date must be same month !!");
		return;
	}

	param = 'kdOrg=' + kdOrg + '&tgl=' + tgl;
	param += '&tgl2=' + tgl2;
	param += '&proses=recektimbangan'
	tujuan = 'kebun_slave_3AmbilKgTimbangan_v2.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					document.getElementById('result').style.display = 'block';
					document.getElementById('list_ganti').innerHTML = con.responseText;
					document.getElementById('idKbnx').disabled = true;				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettglx(){
	tgl = document.getElementById('tglDatax').value;
	document.getElementById('tglData2x').value=tgl;
}


function cancelSavex() {
	document.getElementById('list_ganti').innerHTML = '';
	document.getElementById('idKbnx').disabled = false;
	document.getElementById('tglDatax').disabled = false;
	document.getElementById('tglData2x').disabled = false;
	document.getElementById('idKbnx').value = '';
	document.getElementById('tglDatax').value = '';
	document.getElementById('tglData2x').value = '';
	document.getElementById('result').style.display = 'none';

}