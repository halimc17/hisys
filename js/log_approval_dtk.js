function prpopup(noPP, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + noPP + "</legend><div id=prcontainer></div></fieldset><input type=hidden id=datPP name=datPP value=" + noPP + " />";
	showDialog1(title, content, width, height, ev);
}
function htmldtk(notransaksi, tipe) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:98%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
	param = 'method=html' +'&tipe=' + tipe + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewx').innerHTML = con.responseText;
					wewspk(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewKaryawanhist(nourut,karid, nama, ev) {
	param = 'nourut=' + nourut + '&karyawanid=' + karid + '&namakaryawan=' + nama+ '&method=history';
	tujuan = 'sdm_slave_get_karyawan_preview.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					content = con.responseText;
					//display window
					title = nama;
					width = '100%';
					height = '400';
					showDialog2(title, content, width, height, ev);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function wewspk(notransaksi){
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
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
						// loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfilesx(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
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
						// loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdatadtk(id, kolom,jenispersetujuan) {
	prpopup(id, 'Approval Form', 'event');
	notransaksi = id;
	met = 'get_form_approvaldtk';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom+ '&jenispersetujuan=' + jenispersetujuan;
	tujuan = 'sdm_slave_approval_dtk.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (con.responseText == '') {
						document.getElementById('prcontainer').innerHTML = 'You are not registred in the list';
					} else {
						document.getElementById('prcontainer').innerHTML = "<input type=hidden id=kolom value=" + kolom + ">" + con.responseText;
						return con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function nextapprovaldtk(tipe,jenispersetujuan) {
	kolom = document.getElementById('kolom').value;
	comment = document.getElementById('comment_fr').value;
	notransaksi = document.getElementById('notransaksi').value;
	if(tipe!='approved'){
	userid = trim(document.getElementById('user_id').options[document.getElementById('user_id').selectedIndex].value);
		if (comment == '' || userid == '') {
			alert('Please compleate the form !');
			return;
		}		
	}else{
		if (comment == '') {
			alert('Please compleate the form !');
			return;
		}
	}
	document.getElementById('Ajukan').disabled = true;
	met = met.value = 'insert_nextapproval';
	param = 'comment=' + comment + '&method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom+ '&jenispersetujuan=' + jenispersetujuan;
	if(tipe!='approved'){
		param += '&userid=' + userid;
	}
	tujuan = 'sdm_slave_approval_dtk.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog();
					getdetail(jenispersetujuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function tolakdtk(id, kolom, jenispersetujuan) {
	prpopup(id, 'Rejection Form', 'event');
	notransaksi = id;
	met = 'tolak';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom+ '&jenispersetujuan=' + jenispersetujuan;
	tujuan = 'sdm_slave_approval_dtk.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('prcontainer').innerHTML = "<input type=hidden id=kolom value=" + kolom + ">" + con.responseText;
					return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function inserttolakspk(klm,jenispersetujuan) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	met = 'inserttolak';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_tolak').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom+ '&jenispersetujuan=' + jenispersetujuan;
		tujuan = 'sdm_slave_approval_dtk.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						closeDialog();
						getdetail(jenispersetujuan);
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		post_response_text(tujuan, param, respog);
	}
}