function preview(tipe) {
	kodept = document.getElementById('kodept');
	kodeptV = kodept.options[kodept.selectedIndex].value;
	kodeunit = document.getElementById('kodeunit');
	kodeunitV = kodeunit.options[kodeunit.selectedIndex].value;
	periode = document.getElementById('periode');
	periodeV = periode.options[periode.selectedIndex].value;
	tipelaporan = document.getElementById('tipelaporan');
	tipelaporanV = tipelaporan.options[tipelaporan.selectedIndex].value;
    proses = document.getElementById('proses').value;

    param = 'kodept=' + kodeptV + '&kodeunit=' + kodeunitV+ '&proses=preview';
    param += '&periode=' + periodeV;
    param += '&tipe=' + tipe;
    // param += '&tipelaporan=' + tipelaporanV;
    param += "&tipelaporan=''";
	tujuan = 'keu_2laporanasistensihutangpiutang_slave.php';

	if (kodeptV == '') {
		alertify.alert("Informasi",'Perusahaan Tidak Boleh Kosong!');
	} 

    if(periodeV == '') {
        alertify.alert("Informasi",'Periode Tidak Boleh Kosong!');
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

function excelDetail(unitinti,unitplasma,arrdata,tipelaporan,periode,tipedetail) {
	param = 'proses=detaildata';
    param += '&arrdata='+arrdata;
    param += '&unitinti='+unitinti;
    param += '&unitplasma='+unitplasma;
    param += '&tipelaporan='+tipelaporan;
    param += '&periode='+periode;
    param += '&tipe='+tipedetail;

	tujuan = 'keu_2laporanasistensihutangpiutang_slave.php';

	judul = 'Report Ms.Excel';
	ev='event';
	printFile(param, tujuan, judul, ev);
}

function detaildata(unitinti,unitplasma,arrdata,tipelaporan,periode,tipedetail) {
    param = 'proses=detaildata';
    param += '&arrdata='+arrdata;
    param += '&unitinti='+unitinti;
    param += '&unitplasma='+unitplasma;
    param += '&tipelaporan='+tipelaporan;
    param += '&periode='+periode;

	tujuan = 'keu_2laporanasistensihutangpiutang_slave.php';

	if(tipedetail=='excel'){
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

	tujuan = 'keu_2laporanasistensihutangpiutang_slave.php';
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

function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '300';
	height = '100';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}
