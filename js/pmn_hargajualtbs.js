
function formajukan(notransaksi, page) {
	method = 'formajukan';
	param = '';
	param += '&notransaksi=' + notransaksi + '&page=' + page;
	param += '&method=' + method;
	tujuan = 'pmn_hargajualtbs_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('70%', '60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


// #= diubah menjadi persetujuan
function saveajukan(notransaksi, page, maxaproval) {
	param = '';
	method = 'saveajukan';
	strper = '';
	for (i = 1; i <= maxaproval; i++) {
		strper += '&persetujuan[' + i + ']=' + trim(document.getElementById('persetujuan' + i).value)
	}
	param += '&notransaksi=' + notransaksi;
	param += '&maxaproval=' + maxaproval;
	param += '&method=' + method;
	param += strper;
	tujuan = 'pmn_hargajualtbs_slave.php';
	alertify.confirm("Informasi", "Ajukan transaksi : " + notransaksi + " ???",
		function () {
			post_response_text(tujuan, param, respon);
		},
		function () {
			return;
		}
	);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					alertify.popup().destroy();
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function deleteht(notransaksi, kodecustomer, tahuntanam, page) {
	param = 'method=deleteht';
	param += '&kodecustomer=' + kodecustomer + '&tahuntanam=' + tahuntanam + '&notransaksi=' + notransaksi;
	tujuan = 'pmn_hargajualtbs_slave.php';
	alertify.confirm("Informasi", "Hapus transaksi : " + kodecustomer + " dan " + tahuntanam + " ???",
		function () {
			post_response_text(tujuan, param, respog);
		},
		function () {
			return;
		}
	);
	// post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function cancelht() {
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('kodecustomer').disabled = false;
	document.getElementById('tanggal').disabled = false;
	document.getElementById('tahuntanam').disabled = false;
	document.getElementById('notransaksi').value = '';
	document.getElementById('kodeorg').value = '';
	document.getElementById('kodecustomer').value = '';
	document.getElementById('tanggal').value = '';
	document.getElementById('tanggal2').value = '';
	document.getElementById('tahuntanam').value = '';
	document.getElementById('hargadisbun').value = '0';
	document.getElementById('harga').value = '0';
}

function cancelsch() {
	document.getElementById('kodecustomersch').value = '';
	document.getElementById('tanggalsch').value = '';
	document.getElementById('tanggal2sch').value = '';
	document.getElementById('kodeorgsch').value = '';
	loaddata(0)
}

function editht(notransaksi, kodecustomer, tahuntanam) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi + '&kodecustomer=' + kodecustomer + '&tahuntanam=' + tahuntanam;
	tujuan = 'pmn_hargajualtbs_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					ar = con.responseText.split("###");
					document.getElementById('notransaksi').value = ar[0];
					document.getElementById('kodeorg').value = ar[1];
					document.getElementById('kodeorg').disabled = true;
					document.getElementById('kodecustomer').value = ar[2];
					document.getElementById('kodecustomer').disabled = true;
					document.getElementById('tanggal').value = ar[3];
					document.getElementById('tanggal').disabled = true;
					document.getElementById('tanggal2').value = ar[4];
					document.getElementById('tahuntanam').value = ar[5];
					document.getElementById('tahuntanam').disabled = true;
					document.getElementById('hargadisbun').value = ar[6];
					document.getElementById('harga').value = ar[7];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function loaddata(num) {
	kodecustomer = document.getElementById('kodecustomersch').value;
	tanggal = document.getElementById('tanggalsch').value;
	tanggal2 = document.getElementById('tanggal2sch').value;
	kodeorg = document.getElementById('kodeorgsch').value;

	param = 'method=loaddata&page=' + num;
	param += '&kodecustomer=' + kodecustomer + '&tanggal=' + tanggal + '&tanggal2=' + tanggal2;
	param += '&kodeorg=' + kodeorg;
	// alert(param);
	tujuan = 'pmn_hargajualtbs_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					leftFixedTable();
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

function saveht(parameter) {
	method = 'saveht';
	tujuan = 'pmn_hargajualtbs_slave.php';
	var passP = parameter.split('###');
	var param = "";
	for (i = 1; i < passP.length; i++) {
		var tmp = document.getElementById(passP[i]);
		param += "&" + passP[i] + "=" + getValue(passP[i]);
	}
	param += '&method=' + method;
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alertify.alert('Informasi',con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('kodeorg').disabled = false;
					document.getElementById('kodecustomer').disabled = false;
					document.getElementById('tanggal').disabled = false;
					document.getElementById('tahuntanam').disabled = false;
					document.getElementById('notransaksi').value = '';

					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}