function release(versi,namaversi) {
	param = 'versi=' + versi + '&method=release';
	param += '&namaversi=' + namaversi;
	tujuan = 'tool_slave_uploadapk.php';
	if(confirm("Anda yakin ???")){
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					location.reload();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function submitfile() {
	var versi = document.getElementById("versi").value;
	var namaversi = document.getElementById("namaversi").value;
	var file = document.getElementById("upload").files[0];
	var updatelog = document.getElementById('updatelog').value;
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("updatelog", updatelog);
	formdata.append("versi", versi);
	formdata.append("namaversi", namaversi);
	if (versi == "") {
		alert("warning : Versi wajib diisi !");
		return false;
	}
	if (namaversi == "") {
		alert("warning : Nama Versi wajib diisi !");
		return false;
	}
	if (getValue('upload') == "") {
		if(confirm("File belum di pilih, apakah ingin tetap melanjutkan tanpa upload file ???")){
			if (updatelog == "") {
				alert("warning : Update Log masih kosong, proses dibatalkan !!!");
				return false;
			}
			var con = createXMLHttpRequest();
			document.getElementById('btnsubmit').disabled=true;
			busy_on();
			con.open("POST", "tool_slave_uploadapk.php?method=submitfile", true);
			con.onreadystatechange = eval(respon);
			con.send(formdata);			
		}
	}else{
		if(confirm("Upload file ???")){
			var con = createXMLHttpRequest();
			document.getElementById('btnsubmit').disabled=true;
			busy_on();
			con.open("POST", "tool_slave_uploadapk.php?method=submitfile", true);
			con.onreadystatechange = eval(respon);
			con.send(formdata);			
		}
	}
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
					document.getElementById("updatelog").value = "";
					document.getElementById("versi").value = "";
					document.getElementById("namaversi").value = "";
					location.reload();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}