function getnilaiclaim(parameter) {
	method = 'getnilaiclaim';
	tujuan = 'pmn_bast_slave.php';
	var passP = parameter.split('###');
	var param = "";
	for (i = 1; i < passP.length; i++) {
		param += "&" + passP[i] + "=" + getValue(passP[i]);
	}
	param += '&method=' + method;
	// alert(param);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alertify.alert('Informasi',con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					//kalau berhasil

					ar = con.responseText.split("###");
					document.getElementById('rpclaimffa').value = ar[0];
					document.getElementById('rpclaimmdani').value = ar[1];
					document.getElementById('rpclaimdirt').value = ar[2];
					document.getElementById('rpclaimmoisture').value = ar[3];
					document.getElementById('rpclaimimpurities').value = ar[4];
					document.getElementById('rpclaimbroken').value = ar[5];
					document.getElementById('rpclaimdobi').value = ar[6];
					document.getElementById('rpclaimlain').value = ar[7];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}



function displaylist(page) {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('notransaksisch').value = '';
	document.getElementById('nokontraksch').value = '';
	document.getElementById('tanggal1sch').value = '';
	document.getElementById('tanggal2sch').value = '';
	document.getElementById('tanggalbl1sch').value = '';
	document.getElementById('tanggalbl2sch').value = '';
	document.getElementById('kodebarangsch').value = '';
	document.getElementById('kodecustomersch').value = '';
	loaddata(page);
}


function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}


function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'pmn_bast_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('listdata').style.display = 'none';
					document.getElementById('header').style.display = 'block';
					document.getElementById('detail').style.display = 'block';
					ar = con.responseText.split("###");
					document.getElementById('notransaksi').value = ar[0];
					document.getElementById('kodept').value = ar[1];
					document.getElementById('kodecustomer').value = ar[2];
					document.getElementById('kodebarang').value = ar[3];
					document.getElementById('nokontrak').value = ar[4];
					document.getElementById('nokontrak').disabled = true;
					document.getElementById('tanggal').value = ar[5];
					document.getElementById('kota').value = ar[6];
					canceldt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function editdt(notransaksi, nourut) {
	param = 'method=geteditdt' + '&notransaksi=' + notransaksi + '&nourut=' + nourut;
	tujuan = 'pmn_bast_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					ar = con.responseText.split("###");
					document.getElementById('tanggalbl').value = ar[0];
					document.getElementById('kodetangki').value = ar[1];
					document.getElementById('jumlah').value = ar[2];
					document.getElementById('namakapal').value = ar[3];
					document.getElementById('namaponton').value = ar[4];
					document.getElementById('ffa').value = ar[5];
					document.getElementById('moisture').value = ar[6];
					document.getElementById('impurities').value = ar[7];
					document.getElementById('mdani').value = ar[8];
					document.getElementById('dirt').value = ar[9];
					document.getElementById('dobi').value = ar[10];
					document.getElementById('broken').value = ar[11];
					document.getElementById('nourut').value = ar[12];

					document.getElementById('rpkgclaimffa').value = ar[13];
					document.getElementById('rpkgclaimmdani').value = ar[14];
					document.getElementById('rpkgclaimdirt').value = ar[15];
					document.getElementById('rpkgclaimmoisture').value = ar[16];
					document.getElementById('rpkgclaimimpurities').value = ar[17];
					document.getElementById('rpkgclaimbroken').value = ar[18];
					document.getElementById('rpkgclaimdobi').value = ar[19];

					document.getElementById('rpclaimffa').value = ar[20];
					document.getElementById('rpclaimmdani').value = ar[21];
					document.getElementById('rpclaimdirt').value = ar[22];
					document.getElementById('rpclaimmoisture').value = ar[23];
					document.getElementById('rpclaimimpurities').value = ar[24];
					document.getElementById('rpclaimbroken').value = ar[25];
					document.getElementById('rpclaimdobi').value = ar[26];
					document.getElementById('lain').value = ar[27];
					document.getElementById('rpkgclaimlain').value = ar[28];
					document.getElementById('rpclaimlain').value = ar[29];
					document.getElementById('tanggalsmp').value = ar[30];
					document.getElementById('kgpembeli').value = ar[31];
					document.getElementById('jlhrit').value = ar[32];


					document.getElementById('methoddt').value = 'update';
					getkodetangki(ar[1], ar[31]);
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
	nokontrak = document.getElementById('nokontraksch').value;
	tanggal1 = document.getElementById('tanggal1sch').value;
	tanggal2 = document.getElementById('tanggal2sch').value;
	tanggalbl1 = document.getElementById('tanggalbl1sch').value;
	tanggalbl2 = document.getElementById('tanggalbl2sch').value;
	notransaksi = document.getElementById('notransaksisch').value;
	kodebarang = document.getElementById('kodebarangsch').value;
	kodecustomer = document.getElementById('kodecustomersch').value;
	param = 'method=loaddata&page=' + num;
	param += '&notransaksi=' + notransaksi + '&nokontrak=' + nokontrak;
	param += '&tanggal1=' + tanggal1 + '&tanggal2=' + tanggal2;
	param += '&tanggalbl1=' + tanggalbl1 + '&tanggalbl2=' + tanggalbl2;
	param += '&kodebarang=' + kodebarang + '&kodecustomer=' + kodecustomer;
	// alert(param);
	tujuan = 'pmn_bast_slave.php';
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



function newdata() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	cancelht();
}

function cancelht() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('notransaksi').value = '';
	document.getElementById('nokontrak').value = '';
	document.getElementById('nokontrak').disabled = false;
	document.getElementById('kodept').value = '';
	document.getElementById('kodecustomer').value = '';
	document.getElementById('kodebarang').value = '';
	document.getElementById('tanggal').value = '';
	document.getElementById('kota').value = '';
	/*
	document.getElementById('detail').style.display = 'none';
	document.getElementById('notransaksi').value='';
	document.getElementById('nokontrak').value='030/IMT/PK/SDK/IX/21';
	document.getElementById('kodept').value='BPJ';
	document.getElementById('kodecustomer').value='IMT';
	document.getElementById('kodebarang').value='40000002';
	document.getElementById('tanggal').value='';
	document.getElementById('kota').value='';
	*/
	// document.getElementById('keterangan').value='';
}


function getkodetangki(kodetangki, jumlah = 0, jlhitem = 0, jlhkgpembeli = 0) {
	nokontrak = document.getElementById('nokontrak').value;
	tanggalbl = document.getElementById('tanggalbl').value;
	tanggalsmp = document.getElementById('tanggalsmp').value;
	kodept = document.getElementById('kodept').value;
	kodebarang = document.getElementById('kodebarang').value;
	param = 'method=getkodetangki';
	param += '&nokontrak=' + nokontrak + '&tanggalbl=' + tanggalbl + '&tanggalsmp=' + tanggalsmp + '&kodept=' + kodept + '&kodebarang=' + kodebarang + '&kodetangki=' + kodetangki;
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					data = con.responseText.split("###");
					document.getElementById('kodetangki').innerHTML = data[0];
					if (jumlah == 0) {
						if (data[1] > 0) {
							document.getElementById('jumlah').value = data[2];
							document.getElementById('kgpembeli').value = data[3];
							document.getElementById('detailwb').style.display = '';
						} else {
							document.getElementById('jumlah').value = jlhitem;
							document.getElementById('kgpembeli').value = jlhkgpembeli;
							document.getElementById('detailwb').style.display = 'none';
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getnokontrak() {
	param = 'method=getnokontrak';
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('90%', '85%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}




function findnokontrak() {
	kodebarang = document.getElementById('kodebarang').value;
	nokontrakfind = document.getElementById('nokontrakfind').value;
	kodept = document.getElementById('kodept').value;
	kodecustomer = document.getElementById('kodecustomer').value;
	param = 'method=findnokontrak';
	param += '&kodept=' + kodept + '&kodecustomer=' + kodecustomer + '&kodebarang=' + kodebarang + '&nokontrakfind=' + nokontrakfind;
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					leftFixedTable();
					document.getElementById('formnokontrak').innerHTML = con.responseText;
					// loaddatadt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function movenokontrak(nokontrak) {
	document.getElementById('nokontrak').value = nokontrak;
	alertify.popup().destroy();
}

function saveht() {
	document.getElementById('detail').style.display = 'block';
	document.getElementById('kodept').disabled = true;
	document.getElementById('kodecustomer').disabled = true;
	document.getElementById('kodebarang').disabled = true;
	document.getElementById('nokontrak').disabled = true;
}


function savedt(parameter) {
	method = 'savedt';
	tujuan = 'pmn_bast_slave.php';
	var passP = parameter.split('###');
	var param = "";
	for (i = 1; i < passP.length; i++) {
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
					canceldt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}


function canceldt() {
	document.getElementById('kodetangki').value = '';
	document.getElementById('tanggalbl').value = '';
	document.getElementById('tanggalsmp').value = '';
	document.getElementById('jumlah').value = '0';
	document.getElementById('kgpembeli').value = '0';

	document.getElementById('ffa').value = '';
	document.getElementById('mdani').value = '';
	document.getElementById('moisture').value = '';
	document.getElementById('impurities').value = '';
	document.getElementById('dirt').value = '';
	document.getElementById('dobi').value = '';
	document.getElementById('broken').value = '';
	document.getElementById('nourut').value = '';

	document.getElementById('rpkgclaimffa').value = '0';
	document.getElementById('rpkgclaimmdani').value = '0';
	document.getElementById('rpkgclaimdirt').value = '0';
	document.getElementById('rpkgclaimmoisture').value = '0';
	document.getElementById('rpkgclaimimpurities').value = '0';
	document.getElementById('rpkgclaimbroken').value = '0';
	document.getElementById('rpkgclaimdobi').value = '0';

	document.getElementById('rpclaimffa').value = '0';
	document.getElementById('rpclaimmdani').value = '0';
	document.getElementById('rpclaimdirt').value = '0';
	document.getElementById('rpclaimmoisture').value = '0';
	document.getElementById('rpclaimimpurities').value = '0';
	document.getElementById('rpclaimbroken').value = '0';
	document.getElementById('rpclaimdobi').value = '0';

	document.getElementById('lain').value = '0';
	document.getElementById('rpkgclaimlain').value = '0';
	document.getElementById('rpclaimlain').value = '0';
	document.getElementById('jlhrit').value = '0';

	document.getElementById('methoddt').value = 'insert';
	loaddatadt();
}



function loaddatadt() {
	notransaksi = document.getElementById('notransaksi').value;
	param = 'method=loaddatadt';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('listdatadt').innerHTML = con.responseText;
					loadfiles();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedt(notransaksi, nourut) {
	param = 'method=deletedt';
	param += '&notransaksi=' + notransaksi + '&nourut=' + nourut;
	tujuan = 'pmn_bast_slave.php';
	alertify.confirm("Informasi", "Hapus detail data???",
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
					canceldt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnobast() {
	kodept = document.getElementById('kodept').value;
	tanggal = document.getElementById('tanggal').value;
	notransaksi = document.getElementById('notransaksi').value;
	nokontrak = document.getElementById('nokontrak').value;
	kodebarang = document.getElementById('kodebarang').value;
	param = 'method=getnobast';
	param += '&kodept=' + kodept + '&tanggal=' + tanggal + '&kodebarang=' + kodebarang + '&nokontrak=' + nokontrak + '&notransaksi=' + notransaksi;
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('notransaksi').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function submitfile() {
	var notransaksi = document.getElementById("notransaksi").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("notransaksi", notransaksi);
	formdata.append("kriteriaefil", kriteriaefil);
	if (getValue('upload') == "") {
		alertify.alert("Informasi", "warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled = true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "pmn_bast_slave.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					//=== Success Response
					document.getElementsByClassName("mybutton").disabled = false;
					alertify.alert("Informasi", 'Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function deletefile(notransaksi, namafile) {
	param = 'method=deletefile&notransaksi=' + notransaksi + '&namafile=' + namafile;
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loadfiles() {
	notransaksi = document.getElementById('notransaksi').value;
	param = 'method=loadfiles&notransaksi=' + trim(notransaksi);
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {

					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					loaddatadtnotransaksireferensi();
					// loaddatadetail();
					// document.getElementById('listfiles').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}




function deletedtnotransaksireferensi(notransaksi, notransaksireferensi) {
	param = 'method=deletedtnotransaksireferensi';
	param += '&notransaksi=' + notransaksi + '&notransaksireferensi=' + notransaksireferensi;
	tujuan = 'pmn_bast_slave.php';
	alertify.confirm("Informasi", "Hapus detail data???",
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
					loaddatadtnotransaksireferensi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedtnotransaksireferensi(parameter) {
	method = 'savedtnotransaksireferensi';
	tujuan = 'pmn_bast_slave.php';
	var passP = parameter.split('###');
	var param = "";
	for (i = 1; i < passP.length; i++) {
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
					loaddatadtnotransaksireferensi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}



function loaddatadtnotransaksireferensi() {
	notransaksi = document.getElementById('notransaksi').value;
	notransaksireferensi = document.getElementById('notransaksireferensi').value;
	param = 'method=loaddatadtnotransaksireferensi';
	param += '&notransaksi=' + notransaksi + '&notransaksireferensi=' + notransaksireferensi;
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					data = con.responseText.split("###");

					document.getElementById('notransaksireferensi').innerHTML = data[1];
					document.getElementById('listreferensi').innerHTML = data[0];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function viewlistfile(notransaksi) {
	param = 'method=viewlistfile&notransaksi=' + trim(notransaksi);
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {

					if (document.getElementById('listfiles') !== null) {
						// document.getElementById('listfiles').innerHTML = con.responseText;
						alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('600px', '400px');
					}

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function pdf(notransaksi) {
	param = "method=pdf&notransaksi=" + notransaksi;
	alertify.popuppdf("title", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_bast_slave.php?" + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('90%', '80%');
}

function pdf2(notransaksi) {
	param = "method=pdf2&notransaksi=" + notransaksi;
	alertify.popuppdf("title", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_bast_slave.php?" + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('90%', '80%');
}

function posting(notransaksi, page) {
	method = 'posting';
	param = '';
	param += '&method=' + method + '&notransaksi=' + notransaksi + '&page=' + page;
	tujuan = 'pmn_bast_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('50%', '50%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveposting(page) {

	notransaksi = document.getElementById('notransaksiposting').value;
	tipe = document.getElementById('tipe').value;
	param = '';
	method = 'saveposting';
	param += '&notransaksi=' + notransaksi + '&tipe=' + tipe + '&page=' + page;
	param += '&method=' + method;
	tujuan = 'pmn_bast_slave.php';
	alertify.confirm("Informasi", "Posting transaksi : " + notransaksi + " ???",
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

function deleteht(notransaksi, page) {
	method = 'deleteht';
	param += '&notransaksi=' + notransaksi + '&method=' + method;
	//alert(param);
	tujuan = 'pmn_bast_slave.php';
	alertify.confirm("Informasi", "Anda yakin ingin menghapus data ini " + notransaksi + " ?",
		function () {
			post_response_text(tujuan, param, respog);
		},
		function () {
			return;
		}
	);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					//	alert(con.responseText);
					displayList(page);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}



function form_ajukan(notransaksi) {
	param = "method=form_ajukan" + "&notransaksi=" + notransaksi;
	tujuan = "pmn_bast_slave.php";
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('containeraju').innerHTML = con.responseText;
					alertify
						.popup("Approval", con.responseText)
						.set({ resizable: true, overflow: false })
						.resizeTo("400px", "300px");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function ajukan() {
	jumlahlevel = document.getElementById("numrow").value;
	kepada = "";
	for (var i = 1; i <= jumlahlevel; i++) {
		if (kepada == "") {
			kepada = document.getElementById("kepada" + i).value;
		} else {
			kepada += "###" + document.getElementById("kepada" + i).value;
		}
	}
	notransaksi = document.getElementById("notran_aju").innerHTML;
	jenispersetujuanx = document.getElementById("jenispersetujuanx").value;
	param =
		"method=ajukan" +
		"&notransaksi=" +
		notransaksi +
		"&kepada=" +
		kepada +
		"&jenispersetujuanx=" +
		jenispersetujuanx;
	if (kepada == "") {
		alert("Isikan nama penyetuju.");
		return;
	}


	tujuan = "pmn_bast_slave.php";

	alertify.confirm('Konfirmasi', 'Ajukan Transaksi ini ??', function () {
		post_response_text(tujuan, param, respog);
	}, function () {
		// Cancelled
	});
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
					closeDialog();
					alertify.popup().destroy();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


