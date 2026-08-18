function getkodebank() {
	param = 'method=getkodebank';
	tujuan = 'keu_slave_5daftarbank.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('kodebank').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showupload(ev, noinduk) {
    showformupload(ev);
    param = "";
    param += "noinduk=" + noinduk;
    param += '&method=showupload';
    tujuan = 'keu_slave_5daftarbank.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(noinduk);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showformupload(ev) {
    title = "UPLOAD FILES";
    width = 'auto';
    height = 'auto';
    content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
    document.getElementById('dynamic2').style.display = '';
}

function loadfiles(noinduk) {
    param = 'method=loadfiles&noinduk=' + noinduk;
    tujuan = 'keu_slave_5daftarbank.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
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

function deletefile(noinduk, namafile) {
    param = "method=deletefile";
    param += "&noinduk=" + noinduk;
    param += "&namafile=" + namafile;
    tujuan = 'keu_slave_5daftarbank.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    loadfiles(noinduk);
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
    var noinduk = document.getElementById('noupload').innerHTML;
    var pemisah = document.getElementById('pemisah').value;
    var formdata = new FormData();
    formdata.append("noinduk", noinduk);
    formdata.append("pemisah", pemisah);
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    if (getValue('upload') == "") {
        alertify.alert("Informasi","warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "keu_slave_5daftarbank.php?method=submitfile", true);
    busy_on();
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById("upload").value = "";
                    loadfiles(noinduk);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function nonaktif(kodebank,bank,status) {
	param = 'method=nonaktif&kodebank='+kodebank+'&bank='+bank+'&status='+status;
	tujuan = 'keu_slave_5daftarbank.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpan() {
	kodebank = trim(document.getElementById('kodebank').value);
	bank = trim(document.getElementById('bank').value);
    jumlah_hari = trim(document.getElementById('jumlah_hari').value);
	jumlah_hari2 = trim(document.getElementById('jumlah_hari2').value);
	inisial = trim(document.getElementById('inisial').value);
	method = document.getElementById('method').value;
	if (kodebank == '' || bank == '' ) {
		alertify.alert("Informasi",'Please complete the form');
		return;
	}
	param = 'kodebank=' + kodebank + '&bank=' + bank + '&jumlah_hari=' + jumlah_hari + '&jumlah_hari2=' + jumlah_hari2+ '&inisial=' + inisial;
	param += '&method=' + method;
	tujuan = 'keu_slave_5daftarbank.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					hapus();
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function hapussch() {
	document.getElementById('banksch').value='';
	loadData();
}
function hapus() {
	document.getElementById('method').value = 'insert';
	document.getElementById('kodebank').value = '';
	document.getElementById('bank').value = '';
    document.getElementById('jumlah_hari').value='';
	document.getElementById('jumlah_hari2').value='';
}
function loadData() {
	banksch = document.getElementById('banksch').value;
	param = 'banksch=' + banksch;
	param += '&method=loadData';
	tujuan = 'keu_slave_5daftarbank.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
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
function fillField(kodebank,bank,jumlah_hari,jumlah_hari2,inisial) {
	document.getElementById('inisial').value=inisial;
	document.getElementById('kodebank').value=kodebank;
	document.getElementById('bank').value=bank;
    document.getElementById('jumlah_hari').value=jumlah_hari;
	document.getElementById('jumlah_hari2').value=jumlah_hari2;
	document.getElementById('method').value = 'update';
}
