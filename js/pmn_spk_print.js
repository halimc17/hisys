function getpontonkapal(){
	
	
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
	document.getElementById('dynamic2').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(notransaksi,jenis){
	ev = 'event';
	showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	param+='&jenis='+jenis;
	tujuan='pmn_spk_upload_slave.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
                    document.getElementById('contUpload').innerHTML=con.responseText;
					loadfiles(notransaksi,jenis);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function submitfile() {
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksiupload').innerHTML;
	var jenis = trim(document.getElementById('jenisupload').innerHTML);

	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("jenis", jenis);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}

	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "pmn_spk_upload_slave.php?method=submitfile", true);
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
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,jenis);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(notransaksi,jenis) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	param+='&jenis='+jenis;
	tujuan = 'pmn_spk_upload_slave.php';
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
}
function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'pmn_spk_upload_slave.php';
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
	tujuan = 'pmn_spk_upload_slave.php';
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

function getkapalponton(namakapal,namaponton) {
	transportir=document.getElementById('transportir').value;
	param = "method=getkapalponton";
	param += "&namakapal=" + namakapal;
	param += "&transportir=" + transportir;
	param += "&namaponton=" + namaponton;
	// alert(param);
	tujuan = 'pmn_spk_upload_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					ar=con.responseText.split("####");
					// alert(ar);
					document.getElementById('namakapal').innerHTML = ar[0];
					document.getElementById('namaponton').innerHTML = ar[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



/*
----------------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------
*/


function printpdf(nokontrak,nospk,jenis,table,tujuan) {
	param = 'method=printpdf' + '&nokontrak=' + nokontrak+ '&nospk=' + nospk+ '&jenis=' + jenis+ '&table=' + table;
	tujuan = tujuan+'?' + param;
	// alert(tujuan);
	//content = document.getElementById('test');
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	// showDialog4(title, content, width, height, 'event');
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function printpdfnonsales(nospk,jenis,table,tujuan) {
	param = 'method=printpdf' + '&nospk=' + nospk+ '&jenis=' + jenis+ '&table=' + table;
	tujuan = tujuan+'?' + param;
	// alert(tujuan);
	//content = document.getElementById('test');
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog3(title, content, width, height, 'event');
}




function posting(nospk,jenis,table,tujuan) {
	param='method=posting';
	param+='&nospk='+nospk+'&table='+table+'&jenis='+jenis;
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}

function kembalispk(dest, nokontrak, kodept, tanggal, kodecustomer, kodebarang) {
	if (dest == 'BI') {
		window.open("main_bi.html", "OWLBI", "status=0,toolbar=0,resizable=1,status=0,location=no,menubar=0,directories=0");
	} else {
		dest = dest.replace(".php", "");
		dest = dest.replace(".html", "");
		dest = dest.replace(".phtml", "");
		dest = dest.replace(".php3", "");
		// window.location=dest+'.php?nokontrak='+nokontrak;
		window.location = dest + '.php?nokontrak=' + nokontrak + '&kodept=' + kodept + '&tanggal=' + tanggal + '&kodecustomer=' + kodecustomer + '&kodebarang=' + kodebarang;
	}
}


function kembalispknonsales(dest) {
	if(dest=='BI') {
		window.open("main_bi.html","OWLBI","status=0,toolbar=0,resizable=1,status=0,location=no,menubar=0,directories=0");       
	} else { 
		dest=dest.replace(".php","");
		dest=dest.replace(".html","");
		dest=dest.replace(".phtml","");
		dest=dest.replace(".php3","");
		window.location=dest+'.php';
	}
}


