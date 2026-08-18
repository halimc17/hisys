function preview(tipe) {
	kodept = document.getElementById('kodept');
	kodeptV = kodept.options[kodept.selectedIndex].value;
	kodeunit = document.getElementById('kodeunit');
	kodeunitV = kodeunit.options[kodeunit.selectedIndex].value;
	tahun = document.getElementById('tahun');
	tahunV = tahun.options[tahun.selectedIndex].value;
    proses = document.getElementById('proses').value;

    param = 'kodept=' + kodeptV + '&kodeunit=' + kodeunitV+ '&proses=preview';
    param += '&tahun=' + tahunV;
    param += '&tipe=' + tipe;
	tujuan = 'pmn_2realisasipenjualan_slave.php';

	if (kodeptV == '') {
		alertify.alert("Informasi",'Perusahaan Tidak Boleh Kosong!');
	} 

    if(tahunV == '') {
        alertify.alert("Informasi",'Tahun Tidak Boleh Kosong!');
    }
		
	if(tipe=='excel'){
		judul = 'Report Ms.Excel';
		ev='event';
		printFile(param, tujuan, judul, ev);
	} else {
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					if(tipe=='html'){
						document.getElementById('container').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getUnit(pt) {
	param = 'pt=' + pt + '&tipe=pa';
	tujuan = 'keu_slave_getUnit.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					document.getElementById('kodeunit').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detaildata(unitinti,unitplasma,tipelaporan) {
    param = 'proses=detaildata';
    param += '&unitinti='+unitinti;
    param += '&unitplasma='+unitplasma;
    param += '&tipelaporan='+tipelaporan;

	tujuan = 'pmn_2realisasipenjualan_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					alertify.popup('Detail Jurnal',con.responseText).set({'resizable':true, 'overflow':true}).resizeTo('80%','100%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailportal(unitinti,unitplasma) {
    param = 'proses=detailportal';
    param += '&unitinti='+unitinti;
    param += '&unitplasma='+unitplasma;

	tujuan = 'pmn_2realisasipenjualan_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					alertify.popup('Detail Jurnal',con.responseText).set({'resizable':true, 'overflow':true}).resizeTo('60%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}