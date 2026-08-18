function prpopuprkb(noPP, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + noPP + "</legend><div id=prcontainer></div></fieldset><input type=hidden id=datPP name=datPP value=" + noPP + " />";
	showDialog5(title, content, width, height, ev);
}


function htmlrkb(notransaksi,divisi,kodeorg,periode,numRow,ev,tipe){
		param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&divisi="+divisi+"&kodeorg="+kodeorg+"&periode="+periode;
        title=tipe;
        showDialog1(title,"<iframe frameborder=0 style='width:1200px;min-height:400px'"+
        " src='kebun_slave_rkbx.php?"+param+"'></iframe>",'1200','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}


function htmlrkbrekap(norkb,pt,kodeorg,periode,no,ev,tipe){
	width = '1000px';
    height = '500px';
    content = "<fieldset style=\"width:1200px;max-height:500px\">"
			  +"<div id=contview1 style=\"width:1200px;max-height:500px;overflow:auto;\">"
			  +"</div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog2(title, content, width, height, ev);
	proses ='preview';
	
	param = 'method=rekaprkb&notransaksi=' + norkb;
	param += '&pt=' + pt;
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&proses=' + proses;
	tujuan = 'kebun_slave_rkbx_approval.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview1').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function wew(notransaksi){
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_rkbx_approval.php';
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
	tujuan = 'kebun_slave_rkbx_approval.php';
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
function getdatarkb(id, kolom) {
	prpopuprkb(id, 'Approval Form', 'event');
	notransaksi = id;
	met = 'get_form_approval';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'kebun_slave_rkbx_approval.php';
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

function nextapprovalrkb(tipe) {
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
	tujuan = 'kebun_slave_rkbx_approval.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog();
					getdetail('RKB');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function tolakrkb(id, kolom) {
	prpopuprkb(id, 'Rejection Form', 'event');
	notransaksi = id;
	met = 'tolak';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'kebun_slave_rkbx_approval.php';
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

function inserttolakrkb(klm) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	met = 'inserttolak';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_tolak').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom;
		tujuan = 'kebun_slave_rkbx_approval.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						closeDialog();
						getdetail('RKB');
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