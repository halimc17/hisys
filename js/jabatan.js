function simpandetail(id,kodejabatan,tipe) {  
	param = '';
	if(id.checked==true){
		param += '&check=1';
	}else{
		param += '&check=0';
	}
	
	param += '&kodejabatan=' + kodejabatan + '&tipe=' + tipe + '&method=simpandetail';
	tujuan = 'sdm_slave_save_5jabatan.php';
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
					if(kodejabatan==''){
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

function simpanJabatan() {
    kodejabatan = document.getElementById('kodejabatan').value;
    namajabatan = document.getElementById('namajabatan').value;
    aktif = document.getElementById('aktif').value;
    met = document.getElementById('method').value;
    if (trim(kodejabatan) == '') {
        alert('Code is empty');
        document.getElementById('kodejabatan').focus();
    } else {
        kodejabatan = trim(kodejabatan);
        namajabatan = trim(namajabatan);
        param = 'kodejabatan=' + kodejabatan + '&namajabatan=' + namajabatan + '&method=' + met + '&aktif=' + aktif;
        tujuan = 'sdm_slave_save_5jabatan.php';
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
                    //document.getElementById('container').innerHTML = con.responseText;
					loaddata();
                    cancelJabatan();
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
	tujuan = 'sdm_slave_save_5jabatan.php';
    
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
    document.getElementById('kodejabatan').value = kode;
    document.getElementById('kodejabatan').disabled = true;
    document.getElementById('namajabatan').value = nama;
    document.getElementById('aktif').value = aktif;
    document.getElementById('method').value = 'update';
}

function cancelJabatan() {
    document.getElementById('kodejabatan').disabled = false;
    document.getElementById('kodejabatan').value = '';
    document.getElementById('namajabatan').value = '';
    document.getElementById('aktif').value = '1';
    document.getElementById('method').value = 'insert';
}