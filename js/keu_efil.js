function addfile(notransaksi,sourceid){
	uploadfile = document.getElementById("upload_"+sourceid);
	var file = uploadfile.files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", uploadfile.value);
	formdata.append("notransaksi", notransaksi);
	formdata.append("sourceid", sourceid);
	if (uploadfile.value == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "keu_slave_efill.php?method=uploadfile", true);
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
					document.getElementsByClassName("mybutton").disabled=false;
					alert('Uploaded Success.');
					document.getElementById("upload_"+sourceid).value = "";
					document.getElementById("bodyefil").innerHTML = "";
					document.getElementById("bodyefil").innerHTML = con.responseText;
					// loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deleteefil(notransaksi,idefill,namafile)
{
    param='method=deleteefil&idefill='+idefill+'&notransaksi='+notransaksi;
    tujuan='keu_slave_efill.php';
	
	if (confirm('Anda yakin hapus item/file ini : '+namafile+' ?')) {
		post_response_text(tujuan, param, respog);
	}      
    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Success");
					document.getElementById("bodyefil").innerHTML = "";
					document.getElementById("bodyefil").innerHTML = con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}

function detailefill(numRow,ev) {
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    
    viewefill(notransaksi,'hide',ev);
}