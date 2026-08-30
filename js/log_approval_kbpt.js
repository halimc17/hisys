function prpopup(noPP, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + noPP + "</legend><div id=prcontainer></div></fieldset><input type=hidden id=datPP name=datPP value=" + noPP + " />";
	showDialog1(title, content, width, height, ev);
}

function detailKoreksiKBPT(notransaksi) {
	param = 'method=koreksiTransaksi&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsjual_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup("Detail Koreksi", con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function getdataKBPT(id, kolom) {
	prpopup(id, 'Approval Form', 'event');
	notransaksi = id;
	met = 'get_form_approval_KBPT';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'pmn_slave_approval_kbpt.php';
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

function nextapprovalKBPT(tipe) {
	kolom = document.getElementById('kolom').value;
	comment = document.getElementById('comment_fr').value;
	notransaksi = document.getElementById('notransaksi').value;
	if (tipe != 'approved') {
		userid = trim(document.getElementById('user_id').options[document.getElementById('user_id').selectedIndex].value);
		if (comment == '' || userid == '') {
			alert('Please compleate the form !');
			return;
		}
	} else {
		if (comment == '') {
			alert('Please compleate the form !');
			return;
		}
	}
	document.getElementById('Ajukan').disabled = true;
	met = 'insert_nextapprovalKBPT';
	param = 'comment=' + comment + '&method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	if (tipe != 'approved') {
		param += '&userid=' + userid;
	}
	tujuan = 'pmn_slave_approval_kbpt.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog();
					getdetail('KBPT');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function tolakKBPT(id, kolom) {
	prpopup(id, 'Rejection Form', 'event');
	notransaksi = id;
	met = 'tolakKBPT';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'pmn_slave_approval_kbpt.php';
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

function revisiKBPT(id, kolom) {
	prpopup(id, 'Revisi Form', 'event');
	notransaksi = id;
	met = 'revisiKBPT';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'pmn_slave_approval_kbpt.php';
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

function insertrevisiKBPT(klm) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	met = 'insertrevisiKBPT';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_revisi').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom;
		tujuan = 'pmn_slave_approval_kbpt.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						closeDialog();
						getdetail('KBPT');
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

function inserttolakKBPT(klm) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	met = 'inserttolakKBPT';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_tolak').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom;
		tujuan = 'pmn_slave_approval_kbpt.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						closeDialog();
						getdetail('KBPT');
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
