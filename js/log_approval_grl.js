function prpopup(noPP, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + noPP + "</legend><div id=prcontainer></div></fieldset><input type=hidden id=datPP name=datPP value=" + noPP + " />";
	showDialog1(title, content, width, height, ev);
}



function getdaftarmasy(idlahan) {
	title = "View";
	width = '950px';
	height = '';
	ev = 'event';
	content = "<div id=containerview style='overflow:auto;width:930px;height:auto;'></div>";
	showDialog5(title, content, width, height, ev);
	
	param = "";
	param += "idlahan=" + idlahan;
	param += '&method=getdaftarmasy';
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('containerview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getstatuslahan(idlahan) {
	title = "View";
	width = '1250px';
	height = '';
	ev = 'event';
	content = "<div id=containerview style='overflow:auto;width:1230px;height:auto;'></div>";
	showDialog5(title, content, width, height, ev);
	
	param = "";
	param += "idlahan=" + idlahan;
	param += '&method=getstatuslahan';
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('containerview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function htmlgrl(notransaksi,kodeorg, periode){
    width = '1250px';
	height = '';
	content = "<fieldset><div id=containerd style=\"width:1230px;max-height:500px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog2(title, content, width, height, ev);
	
    param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode+ '&notransaksi=' + notransaksi;
    tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('containerd').innerHTML = con.responseText;
					loadfiles(notransaksi);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdataGRL(id, kolom) {
	prpopup(id, 'Approval Form', 'event');
	notransaksi = id;
	met = 'get_form_approval';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'lgl_slave_approval_grl.php';
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

function nextapprovalGRL(tipe) {
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
	param = 'comment=' + comment + '&method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	if(tipe!='approved'){
		param += '&userid=' + userid;
	}
	tujuan = 'lgl_slave_approval_grl.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert(con.responseText);
					closeDialog();
					getdetail('GRL');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function tolakGRL(id, kolom) {
	prpopup(id, 'Rejection Form', 'event');
	notransaksi = id;
	met = 'tolak';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'lgl_slave_approval_grl.php';
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

function inserttolakGRL(klm) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	met = 'inserttolak';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_tolak').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom;
		tujuan = 'lgl_slave_approval_grl.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						closeDialog();
						getdetail('GRL');
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