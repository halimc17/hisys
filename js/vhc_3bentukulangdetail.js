/* Traksi > Proses > Bentuk Ulang Detail Pekerjaan */

function paramBentukUlang(method) {
    var unit = document.getElementById('unit').value;
    var periode = document.getElementById('periode').value;
    if (unit == '') {
        alertify.alert('Info', 'Unit wajib dipilih.');
        return false;
    }
    if (periode == '') {
        alertify.alert('Info', 'Periode wajib dipilih.');
        return false;
    }
    return 'method=' + method + '&unit=' + encodeURIComponent(unit) + '&periode=' + encodeURIComponent(periode);
}

function kirimBentukUlang(param) {
    var tujuan = 'vhc_slave_3bentukulangdetail.php';
    document.getElementById('container').innerHTML = '';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info', con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cekDetail() {
    var param = paramBentukUlang('cek');
    if (param === false) return;
    kirimBentukUlang(param);
}

function prosesDetail() {
    var param = paramBentukUlang('proses');
    if (param === false) return;
    var unit = document.getElementById('unit').value;
    var periode = document.getElementById('periode').value;
    alertify.confirm('Konfirmasi', 'Bentuk ulang detail Pekerjaan Kendaraan unit ' + unit + ' periode ' + periode + '?<br>Pastikan database sudah dibackup.',
        function () {
            kirimBentukUlang(param);
        },
        function () {
        });
}

function batalDetail() {
    document.getElementById('container').innerHTML = '';
    setValue2('unit', null);
    setValue2('periode', null);
}
