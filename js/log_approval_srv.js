function prpopup(noPP, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + noPP + "</legend><div id=prcontainer></div></fieldset><input type=hidden id=datPP name=datPP value=" + noPP + " />";
	showDialog1(title, content, width, height, ev);
}
/*function htmlspk(notransaksi, tipe) {
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}*/
function getdatasrv(id, kolom) {
	prpopup(id, 'Approval Form', 'event');
	notransaksi = id;
	met = 'get_form_approval';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'gis_slave_approval_srv.php';
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

function showupload(ev, notransaksi,posting) {
    showformupload(ev);
    param = "";
    param += "notransaksi=" + notransaksi+"&posting=" + posting;
    //alert(param);
    post_response_text('gis_slave_approval_srv.php?method=showupload', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfilesx(notransaksi,posting);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadfilesx(notransaksi,posting) {
    param = 'notransaksi=' + notransaksi+"&posting=" + posting;
    post_response_text('gis_slave_approval_srv.php?method=loadfiles', param, respog);
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

function deletefile(notransaksi, namafile) {
    param = "notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    post_response_text('gis_slave_approval_srv.php?method=deletefile', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfilesx(notransaksi);
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
    formdata.append("fileupload", document.getElementById("upload").value);
    alert(document.getElementById("upload").value);
    if (document.getElementById("upload").value == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "gis_slave_approval_srv.php?method=submitfile", true);
    busy_on();
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
                    loadfilesx(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function nextapprovalsrv(tipe) {
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
	tujuan = 'gis_slave_approval_srv.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert(con.responseText);
					closeDialog();
					getdetail('SRV');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function tolaksrv(id, kolom) {
	prpopup(id, 'Rejection Form', 'event');
	notransaksi = id;
	met = 'tolak';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'gis_slave_approval_srv.php';
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

function inserttolaksrv(klm) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	met = 'inserttolak';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_tolak').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom;
		tujuan = 'gis_slave_approval_srv.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						closeDialog();
						getdetail('SRV');
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