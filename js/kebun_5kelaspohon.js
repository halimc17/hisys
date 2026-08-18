function cancel(){
	document.getElementById('kelaspohon').disabled=false;
	document.getElementById('basishari').value='0';
	document.getElementById('basisbulan').value='0';
	document.getElementById('kelaspohon').value='';
	document.getElementById('namakelas').value='';
	document.getElementById('proses').value='insert';
}

function simpankelas(){
	proses = document.getElementById('proses').value;
	kelaspohon = document.getElementById('kelaspohon').value;
	basishari = document.getElementById('basishari').value;
	basisbulan = document.getElementById('basisbulan').value;
	namakelas = document.getElementById('namakelas').value;
	
	param='proses='+proses+'&kelaspohon='+kelaspohon+'&basishari='+basishari+'&basisbulan='+basisbulan+'&namakelas='+namakelas;
	tujuan='kebun_slave_5kelaspohon';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('container').innerHTML=con.responseText;
					cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddata(){
	param='proses=loaddata';
	tujuan='kebun_slave_5kelaspohon';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
				  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(kelaspohon){
	param='proses=delete&kelaspohon='+kelaspohon;
	tujuan='kebun_slave_5kelaspohon';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
				  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(kelas,basishari,basisbulan,nama){
	document.getElementById('kelaspohon').disabled=true;
	document.getElementById('kelaspohon').value = kelas;
	document.getElementById('basishari').value = basishari;
	document.getElementById('basisbulan').value = basisbulan;
	document.getElementById('namakelas').value = nama;
	document.getElementById('proses').value = 'edit';
}