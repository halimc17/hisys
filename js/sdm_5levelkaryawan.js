function simpandetail(id,kode,tipe) {  
	param = '';
	if(id.checked==true){
		param += '&check=1';
	}else{
		param += '&check=0';
	}
	
	param += '&kode=' + kode + '&tipe=' + tipe + '&method=simpandetail';
	tujuan = 'sdm_slave_5levelkaryawan.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					if(kode==''){
						loaddata();
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanDep() {
    kode = document.getElementById('kode').value;
    nama = document.getElementById('nama').value;
    aktif = document.getElementById('aktif').value;
    met = document.getElementById('method').value;
    if (trim(kode) == '') {
        alert('Code is empty');
        document.getElementById('kode').focus();
    } else {
        kode = trim(kode);
        nama = trim(nama);
        param = 'kode=' + kode + '&nama=' + nama + '&method=' + met + '&aktif=' + aktif;
        tujuan = 'sdm_slave_5levelkaryawan.php';
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddata();
                    cancelDep();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}


function loaddata() {    
	param = 'method=loaddata';
	tujuan = 'sdm_slave_5levelkaryawan.php';
	post_response_text(tujuan, param, respog);
    

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillField(kode, nama, aktif) {
    document.getElementById('kode').value = kode;
    document.getElementById('kode').disabled = true;
    document.getElementById('nama').value = nama;
    document.getElementById('aktif').value = aktif;
    document.getElementById('method').value = 'update';
}

function cancelDep() {
    document.getElementById('kode').disabled = false;
    document.getElementById('kode').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('aktif').value = '1';
    document.getElementById('method').value = 'insert';
}