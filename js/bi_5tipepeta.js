function batal(){
	document.getElementById("id").value = '';
	document.getElementById("chkDasar").checked = true;
	checkChkTipe();
	document.getElementById("trdeskripsi").style.display = '';
	document.getElementById("trtipedokumen").style.display = 'none';
	document.getElementById("tipedokumen").selectedIndex = 0;
	document.getElementById("deskripsi").value = '';
	document.getElementById("tipefeature").selectedIndex = 0;
	document.getElementById("method").value = 'insert';
}

function simpan(){
	id = document.getElementById("id").value;
	chkDasar = document.getElementById('chkDasar').checked;
	chkPt = document.getElementById('chkPt').checked;
	chkKegiatan = document.getElementById('chkKegiatan').checked;
	tipefeature=document.getElementById('tipefeature').options[document.getElementById('tipefeature').selectedIndex].value;
	if(chkDasar == true){
		tipe = '0';
	}else if(chkPt == true){
		tipe = '1';
	}else if(chkKegiatan == true){
		tipe = '2';
	}else{
		tipe = '3';
	}
	deskripsi = document.getElementById("deskripsi").value;
	tipedokumen=document.getElementById('tipedokumen').options[document.getElementById('tipedokumen').selectedIndex].value;
	method = document.getElementById("method").value;
	
	param = "id="+id+"&tipe="+tipe+"&tipedokumen="+tipedokumen+"&deskripsi="+deskripsi+"&tipefeature="+tipefeature;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alert("Success");
                    loaddata();
					batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5tipepeta.php?proses='+method, param, respon);
}

function loaddata(){
	param = "";
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("container").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5tipepeta.php?proses=loaddata', param, respon);
}

function fillfield(id,tipe,deskripsi,tipefeature){
	document.getElementById("id").value = id;
	if(tipe == '2'){
		document.getElementById("chkKegiatan").checked = true;
		checkChkTipe();
	}else if(tipe == '1'){
		document.getElementById("chkPt").checked = true;
		checkChkTipe();
	}else{
		document.getElementById("chkDasar").checked = true;
		checkChkTipe();
	}
	document.getElementById("deskripsi").value = deskripsi;
	ltipefeature=document.getElementById('tipefeature');
    for(ard=0;ard<ltipefeature.length;ard++)
    {
        if(ltipefeature.options[ard].value==tipefeature)
            {
                ltipefeature.options[ard].selected=true;
            }
    }
	document.getElementById("method").value = 'update';
}

function deletefield(id){
	param = "id="+id+'&method=delete';
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alert("Success");
                    loaddata();
					batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Are you sure delete this item : '+id+'?'))
		post_response_text('bi_slave_5tipepeta.php?proses=delete', param, respon);
}

function checkChkTipe(){
	chkDasar = document.getElementById('chkDasar').checked;
	chkPt = document.getElementById('chkPt').checked;
	chkKegiatan = document.getElementById('chkKegiatan').checked;
	
	if(chkKegiatan == true){
		document.getElementById('trtipedokumen').style.display = '';
		document.getElementById('trdeskripsi').style.display = 'none';
	}else{
		document.getElementById('trtipedokumen').style.display = 'none';
		document.getElementById('trdeskripsi').style.display = '';
	}
}