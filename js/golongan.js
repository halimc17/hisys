function simpandetail(id,kodegolongan,tipe) {  
	param = '';
	if(id.checked==true){
		param += '&check=1';
	}else{
		param += '&check=0';
	}
	
	param += '&kodegolongan=' + kodegolongan + '&tipe=' + tipe + '&method=simpandetail';
	tujuan = 'sdm_slave_save_golongan.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    //document.getElementById('container').innerHTML = con.responseText;
					if(kodegolongan==''){
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

function simpanGolongan() {
    kodegolongan = document.getElementById('kodegolongan').value;
    namagolongan = document.getElementById('namagolongan').value;
    aktif = document.getElementById('aktif').value;
    met = document.getElementById('method').value;
    if (trim(kodegolongan) == '') {
        alert('Code is empty');
        document.getElementById('kodegolongan').focus();
    } else {
        kodegolongan = trim(kodegolongan);
        namagolongan = trim(namagolongan);
        param = 'kodegolongan=' + kodegolongan + '&namagolongan=' + namagolongan + '&method=' + met + '&aktif=' + aktif;
        tujuan = 'sdm_slave_save_golongan.php';
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    loaddata();
                    cancelGolongan();
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
	tujuan = 'sdm_slave_save_golongan.php';
    
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
    document.getElementById('kodegolongan').value = kode;
    document.getElementById('kodegolongan').disabled = true;
    document.getElementById('namagolongan').value = nama;
    document.getElementById('aktif').value = aktif;
    document.getElementById('method').value = 'update';
}

function cancelGolongan() {
    document.getElementById('kodegolongan').disabled = false;
    document.getElementById('kodegolongan').value = '';
    document.getElementById('namagolongan').value = '';
    document.getElementById('aktif').value = '1';
    document.getElementById('method').value = 'insert';
}