function displaylist() {
	document.getElementById('snotrans').value = '';
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	loaddata(0);
	cleardetail();
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function getpagelast() {
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	snotrans = document.getElementById('snotrans').value;
	param = 'method=loaddata&page=' + page;
	if (snotrans != '') {
		param += '&snotrans=' + snotrans;
	}
	tujuan = 'log_slave_spkv2.php';
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

function deletedata(notransaksi) {
	var param = "notransaksi=" + notransaksi;

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	if (confirm('Anda yakin hapus item ini???')) {
		post_response_text('log_slave_spkv2.php?method=delete', param, respon);
	}
}

function closespk(notransaksi) {
	var param = "notransaksi=" + notransaksi;

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	if (confirm('Anda yakin menutup item ini???')) {
		post_response_text('log_slave_spkv2.php?method=closespk', param, respon);
	}
}

function detailPDF(notransaksi, ev) {
	param = "proses=pdf&notransaksi=" + notransaksi;
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_spk_print_detailv2.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
	// showDialog5('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
		// " src='log_slave_spk_print_detailv2.php?" + param + "'></iframe>", '800', '400', ev);
	// var dialog = document.getElementById('dynamic5');
	// dialog.style.top = '80px';
	// dialog.style.left = '15%';
}

//##########
function addnewdata() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	cleardetail();
}

function savedetail() {
	notransaksi = trim(document.getElementById('notransaksi').value);
	nospk = trim(document.getElementById('nospk').value);

	param = 'notransaksi=' + notransaksi + '&nospk=' + nospk + '&method=savedetail';
	tujuan = 'log_slave_spkv2.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					(function smoothscroll() {
						var currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
						if (currentScroll > 0) {
							window.requestAnimationFrame(smoothscroll);
							window.scrollTo(0, currentScroll - (currentScroll / 5));
						}
					})();

					alert(con.responseText);
				} else {
					getpagelast();

					(function smoothscroll() {
						var currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
						if (currentScroll > 0) {
							window.requestAnimationFrame(smoothscroll);
							window.scrollTo(0, currentScroll - (currentScroll / 5));
						}
					})();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cleardetail() {
	document.getElementById('divresult').innerHTML = "";
	document.getElementById('notransaksi').value = "";
	document.getElementById('nospk').value = "";
}

function popuppencarian(title, content, ev) {
	width = 'auto';
	height = 'auto';
	showDialog1(title, content, width, height, ev);
}

function carinopengajuan() {
	txt = trim(document.getElementById('snopengajuan').value);
	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 1) {
		alert('Too short words');
	} else {
		param = 'txtfind=' + txt + '&method=carinopengajuan';
		tujuan = 'log_slave_spkv2.php';
		post_response_text(tujuan, param, respog);
	}
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

function setnopengajuan(notransaksi) {
	closeDialog();
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('nospk').value = notransaksi;
	document.getElementById('fieldresult').style.display = 'block';

	param = 'notransaksi=' + notransaksi + '&method=setnopengajuan';
	tujuan = 'log_slave_spkv2.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('divresult').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formajukan(title) {
	width = '';
	height = '';
	content = "<div id=containervoid ></div>";
	ev = 'event';
	showDialog2(title, content, width, height, ev);
}

function uploaddata(notransaksi) {
	title = "Upload Data";
	formajukan(title);
	param = 'method=uploaddata' + '&notransaksi=' + notransaksi;
	tujuan = 'log_slave_spkv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containervoid').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanupload(notransaksi) {
	var formdata = new FormData();
	var fileup = document.getElementById('fileupload1').files[0];
	formdata.append("fileup", fileup);
	formdata.append("fileupload", getValue('fileupload1'));

	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_spkv2.php?method=simpanupload&notransaksi=" + notransaksi, true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog2();
					setnopengajuan(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showfile(notransaksi) {
	title = "List File";
	formajukan(title);
	param = 'method=showfile' + '&notransaksi=' + notransaksi;
	tujuan = 'log_slave_spkv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containervoid').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//##########


function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
}
function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'log_slave_spkv2.php';
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