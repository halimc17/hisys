function bataltraining() {
    document.getElementById('method').value = 'insert';
    document.getElementById("kode").value = '';
    document.getElementById('kode').disabled = false;
    document.getElementById('jenistraining').value = '';
}

function loadData() {
    param = 'method=loaddata';
    tujuan = 'sdm_slave_5jenistraining.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                    bataltraining();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(kode, jenistraining,kelompok) {
    document.getElementById('kode').disabled = true;
    document.getElementById('kode').value = kode;
    document.getElementById('kelompok').value = kelompok;
    document.getElementById('jenistraining').value = jenistraining;
    document.getElementById('method').value = 'edit';
}

function simpantraining() {
	kode         = trim(document.getElementById('kode').value);
	jenistraining= trim(document.getElementById('jenistraining').value);
	kelompok     = trim(document.getElementById('kelompok').value);
	method       = trim(document.getElementById('method').value);

    param = 'kode=' + kode + '&jenistraining=' + jenistraining + '&method=' + method + '&kelompok=' + kelompok;
    tujuan = 'sdm_slave_5jenistraining.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                    bataltraining();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(kode) {
    param = 'kode=' + kode + '&method=delete';
    tujuan = 'sdm_slave_5jenistraining.php';
    if (confirm('Anda yakin hapus item ini?'))
        post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                    bataltraining();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function updateStatus(kode, stat) {

    param = 'method=updStatus' + '&kode=' + kode + '&status=' + stat;
    tujuan = 'sdm_slave_5jenistraining.php';
    if (stat == 1) {
        dert = "Anda yakin deactive item ini?";
    } else {
        dert = "Anda yakin active item ini?";
    }
    if (confirm(dert)) {
        post_response_text(tujuan, param, respog);
    } else {
        loadData();
        bataltraining();
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                    bataltraining();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}