function simpandetail(id,kodegolongan,tipe) {  
	param = '';
	if(id.checked==true){
		param += '&check=1';
	}else{
		param += '&check=0';
	}
	
	param += '&kodegolongan=' + kodegolongan + '&tipe=' + tipe + '&method=simpandetail';
	tujuan = 'sdm_slave_save_tipekaryawan.php';
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

function loaddata() {    
	param = 'method=loaddata';
	tujuan = 'sdm_slave_save_tipekaryawan.php';
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

function simpanTipeKar() {
    no = document.getElementById('no').value;
    kode = document.getElementById('kode').value;
    nama = document.getElementById('nama').value;
    aktif = document.getElementById('aktif').value;
    met = document.getElementById('method').value;
    // if(trim(kode)=='' || no=='')
    // {
    // alert('Code or No is empty');
    // document.getElementById('kode').focus();
    // }
    if (no == '') {
        alert('Code or No is empty');
        document.getElementById('kode').focus();
        return;
    } else {
        kode = trim(kode);
        no = trim(no);
        nama = trim(nama);
        param = 'kode=' + kode + '&nama=' + nama + '&method=' + met + '&no=' + no + '&aktif=' + aktif;
        tujuan = 'sdm_slave_save_tipekaryawan.php';
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
                    document.getElementById('container').innerHTML = con.responseText;
                    cancelTipeKar();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function fillField(no, kode, nama, aktif) {
    document.getElementById('no').value = no;
    document.getElementById('kode').value = kode;
    document.getElementById('kode').disabled = true;
    document.getElementById('nama').value = nama;
    document.getElementById('aktif').value = aktif;
    document.getElementById('method').value = 'update';
}

function cancelTipeKar() {
    document.getElementById('kode').disabled = false;
    document.getElementById('kode').value = '';
    document.getElementById('no').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('aktif').value = '1';
    document.getElementById('method').value = 'insert';
}
