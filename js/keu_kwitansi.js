function add_new_data(){
    document.getElementById('headher').style.display='block';
    document.getElementById('listData').style.display='none';
	batal();
	document.getElementById('method').value='insert';
}


function displayList() {
	document.getElementById('listData').style.display='block';
	document.getElementById('headher').style.display='none';
	document.getElementById('tglsch').value='';
	document.getElementById('notransaksisch').value='';
	document.getElementById('telahterimadarisch').value='';
	document.getElementById('keterangansch').value='';
	loaddata();
}




function getterima(){
	nokontrak = document.getElementById('nokontrak').options[document.getElementById('nokontrak').selectedIndex].value;
	tgl = document.getElementById('tgl').value;
	jumlah = document.getElementById('jumlah').value;
	param = 'method=getterima';
	param += '&nokontrak=' + nokontrak+ '&tgl=' + tgl+ '&jumlah=' + jumlah;
	// alert(param);
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
				
					// alert(con.responseText);
					data = con.responseText.split("####");
					document.getElementById('telahterimadari').value = trim(data[0]);
					document.getElementById('keterangan').value = trim(data[1]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getpt(kodept,nokontrak){
	kodeunit = document.getElementById('kodeunit').options[document.getElementById('kodeunit').selectedIndex].value;
	param = 'method=getpt';
	param += '&kodeunit=' + kodeunit+ '&kodept=' + kodept+ '&nokontrak=' + nokontrak;
	// alert(param);
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					data = con.responseText.split("####");
					document.getElementById('kodept').innerHTML = trim(data[0]);
					document.getElementById('nokontrak').innerHTML = trim(data[1]);
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	
}


function batal() {
	document.getElementById('notransaksi').value = '';
	document.getElementById('notransaksi').disabled = true;
	document.getElementById('tgl').value = getdatenow();
	document.getElementById('tgl').disabled = false;
	document.getElementById('kodeunit').value = '';
	document.getElementById('kodeunit').disabled = false;
	document.getElementById('kodept').disabled = false;
	document.getElementById('telahterimadari').value = '';
	document.getElementById('jumlah').value = '';
	document.getElementById('keterangan').value = '';
	document.getElementById('tglsch').value = '';
	document.getElementById('telahterimadarisch').value = '';
	document.getElementById('notransaksisch').value = '';
	document.getElementById('keterangansch').value = '';
	document.getElementById('nokontrak').value = '';
	document.getElementById('kodept').value = '';
	document.getElementById('ttd').value = '';
	document.getElementById('method').value = 'insert';
	getpt();
	// loaddata(0);
}

function batalcari() {
	document.getElementById('tglsch').value = '';
	document.getElementById('telahterimadarisch').value = '';
	document.getElementById('notransaksisch').value = '';
	document.getElementById('keterangansch').value = '';
	loaddata();
}
function loaddata(num) {
	tglsch = document.getElementById('tglsch').value;
	telahterimadarisch = document.getElementById('telahterimadarisch').value;
	notransaksisch = document.getElementById('notransaksisch').value;
	keterangansch = document.getElementById('keterangansch').value;
	param = 'method=loaddata';
	param += '&page=' + num + '&tglsch=' + tglsch + '&telahterimadarisch=' + telahterimadarisch + '&notransaksisch=' + notransaksisch + '&keterangansch=' + keterangansch;
	// alert(param);
	tujuan = 'keu_slave_kwitansi.php';
	// alert(tujuan);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getNotransaksi() {
	kodeunit = document.getElementById('kodeunit').options[document.getElementById('kodeunit').selectedIndex].value;
	tgl = document.getElementById('tgl').value;
	param = 'tgl=' + tgl + '&kodeunit=' + kodeunit + '&method=getNotransaksi';
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('notransaksi').value = trim(data[0])
						document.getElementById('telahterimadari').value = trim(data[1])
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpan() {
	notransaksi = document.getElementById('notransaksi').value;
	tgl = document.getElementById('tgl').value;
	kodeunit = document.getElementById('kodeunit').options[document.getElementById('kodeunit').selectedIndex].value;
	kodept = document.getElementById('kodept').options[document.getElementById('kodept').selectedIndex].value;
	telahterimadari = document.getElementById('telahterimadari').value;
	jumlah = document.getElementById('jumlah').value;
	jumlah=remove_comma_var(jumlah);
	keterangan = document.getElementById('keterangan').value;
	method = document.getElementById('method').value;
	ttd = document.getElementById('ttd').value;
	kota = document.getElementById('kota').value;
	nokontrak = document.getElementById('nokontrak').value;
	if (tgl == '' || telahterimadari == '' || jumlah == '' || kodeunit == '' || keterangan == '') {
		alert('Field Was Empty');
		return false;
	}
	param = 'tgl=' + tgl + '&kodeunit=' + kodeunit + '&telahterimadari=' + telahterimadari + '&jumlah=' + jumlah + '&notransaksi=' + notransaksi + '&method=' + method + '&keterangan=' + keterangan;
	param += '&kodept=' + kodept + '&ttd=' + ttd + '&kota=' + kota + '&nokontrak=' + nokontrak;
	
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batal();
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function edit(notransaksi, nokontrak, kodeunit,kodept,jumlah,keterangan,tgl, telahterimadari, kota, ttd) {
	document.getElementById('listData').style.display='none';
	document.getElementById('headher').style.display='block';
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('notransaksi').disabled = true;
	document.getElementById('tgl').value = tgl;
	document.getElementById('tgl').disabled = true;
	document.getElementById('kodeunit').value = kodeunit;
	document.getElementById('kodeunit').disabled = true;
	
	document.getElementById('kodept').value = kodept;
	document.getElementById('kodept').disabled = true;
	
	document.getElementById('telahterimadari').value = telahterimadari;
	document.getElementById('nokontrak').value = nokontrak;
	document.getElementById('kota').value = kota;
	document.getElementById('jumlah').value = jumlah;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('ttd').value = ttd;
	document.getElementById('method').value = 'update';
	getpt(kodept,nokontrak);
}
function del(notransaksi) {
	param = 'method=delete' + '&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_kwitansi.php';
	if (confirm(' Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
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
function posting(notransaksi, numrow) {
	param = 'method=posting' + '&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_kwitansi.php';
	if (confirm('Anda yakin ingin memposting transaksi nomor : ' + notransaksi + ' ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					x = document.getElementById('tr_' + numrow);
					x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '';
					x.cells[11].innerHTML = '';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function unposting(notransaksi, numrow) {
	param = 'method=unposting' + '&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_kwitansi.php';
	if (confirm('Anda yakin ingin unposting transaksi nomor : ' + notransaksi + ' ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					x = document.getElementById('tr_' + numrow);
					x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '';
					x.cells[11].innerHTML = '';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formx() {
	width = '720';
	height = '';
	content = "<div id=containerd style=\"width:100%;max-height:700px;overflow:auto;\"></div>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
}
function view(notransaksi) {
	formx();
	param = 'method=view' + '&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function viewpdf(notransaksi) {
	if (notransaksi == 'x') {
		alert('Silahkan posting untuk melihat dalam bentuk PDF');
		return;
	}
	param = 'method=viewpdf' + '&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_kwitansi.php?' + param;
	//content = document.getElementById('test');
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	//showDialog5(title, content, width, height, 'event');
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}
function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
	document.getElementById('dynamic2').style.display = '';
}
function showupload(ev, notransaksi) {
	showformupload(ev);
	param = "";
	param += "notransaksi=" + notransaksi;
	param += '&method=showupload';
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function submitfile() {
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('noupload').innerHTML;
	var formdata = new FormData();
	formdata.append("notransaksi", notransaksi);
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "keu_slave_kwitansi.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					alert('Uploaded Success.');
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
function loadfiles(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic5').style.top = (pos[1] - 300) + 'px';
	document.getElementById('dynamic5').style.left = (pos[0] - 200) + 'px';
	document.getElementById('dynamic5').style.display = '';
}
function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'keu_slave_kwitansi.php';
		post_response_text(tujuan, param, respog);
	} else {
		alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		return;
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(notransaksi, namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
function viewlistfile(ev, notransaksi) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	title = "View";
	showDialog4(title, content, width, height, ev);
	param = 'method=viewlistfile&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewz').innerHTML = con.responseText;
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefileall(notransaksi) {
	param = 'method=deletefileall&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_kwitansi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('File sudah di hapus');
					loadData(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}