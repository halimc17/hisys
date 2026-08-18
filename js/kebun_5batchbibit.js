function cancel(){
	document.getElementById('kode').disabled=false;
	document.getElementById('kode').value='';
	document.getElementById('nama').value='';
	document.getElementById('proses').value='insert';
}

function simpan(){
	proses  = document.getElementById('proses').value;
	kode    = document.getElementById('kode').value;
	nama    = document.getElementById('nama').value;
	
	param='proses='+proses+'&kode='+kode+'&nama='+nama;
	tujuan='kebun_slave_5batchbibit';
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

function loaddata(num){
	param='proses=loaddata&page='+num;
	tujuan='kebun_slave_5batchbibit';
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

function deletefield(kode,nama){
	param='proses=delete&kode='+kode+'&nama='+nama;
	tujuan='kebun_slave_5batchbibit';
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

function fillfield(kode,nama){
	document.getElementById('kode').disabled=true;
	document.getElementById('kode').value = kode;
	document.getElementById('nama').value = nama;
	document.getElementById('proses').value = 'edit';
}