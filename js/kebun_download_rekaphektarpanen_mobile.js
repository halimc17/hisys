function displayList() {
	// document.getElementById('karyawansch').value = '';
	// document.getElementById('periodesch').value = '';
	document.getElementById('listData').style.display = 'block';
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

function loaddata(page) {
	// karyawansch = document.getElementById('karyawansch').value;
	// tglsch = document.getElementById('tglsch').value;
	periodesch = document.getElementById('periodesch').value;
	param = 'method=loaddata&page=' + page;
	if (periodesch != '') {
		param += '&periodesch=' + periodesch;
	}
	// if (tglsch != '') {
	// 	param += '&tglsch=' + tglsch;
	// }
	tujuan = 'kebun_slave_download_rekaphektarpanen_mobile.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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
function previewData(kodeorg, tgl, nikmandor) {
	param = 'method=html' + '&kodeorg=' + kodeorg + '&tgl2=' + tgl + '&nikmandor=' + nikmandor;
	tujuan = 'kebun_slave_download_rekaphektarpanen_mobile.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingMobileERP(kodeorg,tanggal,nikmandor) {
	param =  "kodeorg=" + kodeorg;
	param += "&tgl2=" + tanggal;
	param += "&nikmandor=" + nikmandor;
	param += "&proses=rekaphektarpanen";
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					loaddata(0);
					alertify.alert("Data Successfully Downloaded !");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	
	alertify.confirm("Info","Akan dilakukan download data untuk blok "+kodeorg+" di tanggal "+tanggal+" <br>Apakah Anda yakin ?",
		function(){
			post_response_text('kebun_slave_download_from_mobile.php', param, respon);
		},
		function(){
			return;
		}
	)
}