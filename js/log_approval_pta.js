function prpopup(noPP, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + noPP + "</legend><div id=prcontainer></div></fieldset><input type=hidden id=datPP name=datPP value=" + noPP + " />";
	showDialog1(title, content, width, height, ev);
}

function viewlistfile(pt, jenis, kodeijin, noijin) {
	
			width = '';
			height = '';
			content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
			ev = 'event';
			title = "View";
			showDialog4(title, content, width, height, ev);

			param = 'method=viewlistfile&jenis=' + jenis + '&pt=' + pt + '&kodeijin=' + kodeijin + '&noijin=' + noijin;
			tujuan = 'log_slave_approval_pta.php';
			post_response_text(tujuan, param, respog);
			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
						} else {
							document.getElementById('contviewz').innerHTML = con.responseText;
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}

	
	
}

function detaildatapta(notransaksi,tipe,ev,jenis) {
	// width = 1024;
	// height = 400;
	
	// content = "<fieldset style=width:98%><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "Preview";
	// showDialog4(title, content, width, height, ev);
	
	param = 'method=previewdata' + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_ptax.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailpdfpta(notransaksi,tipe,ev,jenis){
	param = 'method=previewdata' + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_ptax.php' + "?" + param;
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='bgt_slave_ptax.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
	// width = 1024;
	// height = 400;
	// ev = 'event';
	// title = "Preview";
	// content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1(title, content, width, height, ev);
}

function getdatapta(id, kolom,kodeapproval,kodeorg) {
	prpopup(id, 'Approval Form', 'event');
	notransaksi = id;
	met = 'get_form_approval';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom + '&kodeapproval=' + kodeapproval+ '&kodeorg=' + kodeorg;
	tujuan = 'log_slave_approval_pta.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
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

function nextapprovalpta(tipe) {
	kolom = document.getElementById('kolom').value;
	comment = document.getElementById('comment_fr').value;
	notransaksi = document.getElementById('notransaksi').value;
	kodeapproval = document.getElementById('kodeapproval').value;
	capital = document.getElementById('tipepta').value;
	if(tipe!='approved'){
		userid = trim(document.getElementById('user_id').options[document.getElementById('user_id').selectedIndex].value);
		if (comment == '' || userid == '') {
			alert('Nama karyawan dan catatan wajib diisi.');
			return;
		}		
	}else{
		if (comment == '') {
			alert('Catatan wajib diisi.');
			return;
		}
	}
	document.getElementById('Ajukan').disabled = true;
	met = met.value = 'insert_nextapproval';
	param = 'comment=' + comment + '&method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	if(tipe!='approved'){
		param += '&userid=' + userid;
	}
	param += '&kodeapproval=' + kodeapproval;
	tujuan = 'log_slave_approval_pta.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					alert("Done");
					closeDialog();
					getdetail(kodeapproval);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function tolakpta(id, kolom,kodeapproval,kodeorg) {
	prpopup(id, 'Rejection Form', 'event');
	notransaksi = id;
	met = 'tolak';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom + '&kodeapproval=' + kodeapproval+ '&kodeorg=' + kodeorg;
	tujuan = 'log_slave_approval_pta.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
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

function reconfirmpta(id, kolom,kodeapproval,kodeorg) {
	prpopup(id, 'Reconfirm Form', 'event');
	notransaksi = id;
	met = 'tolak';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom + '&kodeapproval=' + kodeapproval+ '&kodeorg=' + kodeorg;
	param += '&hasilapp=3';
	
	tujuan = 'log_slave_approval_pta.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
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

function inserttolakpta(klm,hasilapp) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	kodeapproval = document.getElementById('kodeapproval').value;
	met = 'inserttolak';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_tolak').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom+ '&kodeapproval=' + kodeapproval;
		param += '&hasilapp='+hasilapp;
		tujuan = 'log_slave_approval_pta.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						closeDialog();
						getdetail(kodeapproval);
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