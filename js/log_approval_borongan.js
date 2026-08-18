function htmlbor(notransaksi,numRow,ev,tipe,jenis){
    param = "method=html&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis="+jenis;
        title="Data Detail";
        showDialog1(title,"<iframe frameborder=0 style='width:995px;min-height:400px'"+
        " src='kebun_slave_borongan.php?"+param+"'></iframe>",'1000','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}

function htmlbor(notransaksi,divisi,kodeorg,periode,numRow,ev,tipe){
		param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&divisi="+divisi+"&kodeorg="+kodeorg+"&periode="+periode;
        title=tipe;
        showDialog1(title,"<iframe frameborder=0 style='width:100%;min-height:400px'"+
        " src='kebun_slave_rkbx.php?"+param+"'></iframe>",'1300','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}


function htmlborrekap(nopengajuan,kodeorg,no,ev,tipe){
	width = '1000px';
    height = '500px';
    content = "<fieldset style=\"width:1000px;max-height:500px\">"
			  +"<div id=contview1 style=\"width:100%;max-height:500px;overflow:auto;\">"
			  +"</div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog2(title, content, width, height, ev);
	proses ='preview';
	
	param = 'method=rekap&notransaksi=' + nopengajuan;
	param += '&kodeorg=' + kodeorg;
	param += '&proses=' + proses;
	tujuan = 'kebun_slave_borongan_approval.php';
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

function detailData(notransaksi,numRow,ev,tipe,jenis){
    param = "method=html&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis="+jenis;
        title="Data Detail";
        showDialog5(title,"<iframe frameborder=0 style='width:995px;min-height:400px'"+
        " src='kebun_slave_borongan.php?"+param+"'></iframe>",'1000','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}

function loadfilesx(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_borongan_approval.php';
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
function prpopupborx(noPP, title, ev) {
	width = '260px';
	height = '';
	content = "<fieldset style='width:255px'><legend>" + noPP + "</legend><div id=prcontainer style='width:100%'></div></fieldset><input type=hidden id=datPP name=datPP value=" + noPP + " />";
	showDialog4(title, content, width, height, ev);
}
function getdatabor(id, kolom) {
	prpopupborx(id, 'Approval Form', 'event');
	notransaksi = id;
	met = 'get_form_approval';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'kebun_slave_borongan_approval.php';
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

function nextapprovalbor(tipe) {
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
	tujuan = 'kebun_slave_borongan_approval.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog4();
					getdetail('BOR');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function tolakbor(id, kolom) {
	prpopupborx(id, 'Rejection Form', 'event');
	notransaksi = id;
	met = 'tolak';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'kebun_slave_borongan_approval.php';
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

function inserttolakbor(klm) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	met = 'inserttolak';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_tolak').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom;
		tujuan = 'kebun_slave_borongan_approval.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						closeDialog4();
						getdetail('BOR');
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