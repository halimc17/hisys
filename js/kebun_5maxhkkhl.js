function btldendapanen() {
    document.getElementById('method').value = 'insert';
    document.getElementById("kd_org").selectedIndex = "0";
    document.getElementById('kd_org').disabled = false;
    // document.getElementById('nilai').value = '0';
    document.getElementById('status').value = '1';
}

function loadData() {
    param = 'method=loaddata';
    tujuan = 'kebun_slave_5maxhkkhl.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                    btldendapanen();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(kd_org, nilai, sts,tanggalberlaku,jenis,kecuali) {
    document.getElementById('kd_org').value = kd_org;
    document.getElementById('kd_org').disabled = true;
    document.getElementById('jenis').value = jenis;
    document.getElementById('nilai').value = nilai;
    document.getElementById('status').value = sts;
    document.getElementById('tanggalberlaku').value = tanggalberlaku;
    document.getElementById('method').value = 'edit';
	
	data = kecuali.split(",");
	$('#kecuali').val(data).trigger("change");
}

function simpan() {
    kodeorg = document.getElementById('kd_org').value;
    nilai = trim(document.getElementById('nilai').value);
    stts = trim(document.getElementById('status').value);
    jenis = trim(document.getElementById('jenis').value);
    tanggalberlaku = trim(document.getElementById('tanggalberlaku').value);
    method = trim(document.getElementById('method').value);
	
    param = 'kodeorg=' + kodeorg + '&stts=' + stts + '&nilai=' + nilai+'&tanggalberlaku=' + tanggalberlaku+'&method=' + method+'&jenis=' + jenis;
	param += '&kecuali=' + $('#kecuali').val();
    tujuan = 'kebun_slave_5maxhkkhl.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                    btldendapanen();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(kd_org) {
    param = 'kodeorg=' + kd_org + '&method=delete';
    tujuan = 'kebun_slave_5maxhkkhl.php';
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
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}