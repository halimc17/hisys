// PPh 21 Gaji Unit : tampilan, Excel, dan PDF

var urlSlaveGajiUnitPph21 = 'sdm_slave_2amprahgaji.php';

/** Validasi isian form: unit dan periode wajib dipilih */
function validasiFormGajiUnitPph21() {
    if (document.getElementById('unit').value == '') {
        alert('Unit belum dipilih.');
        return false;
    }
    if (document.getElementById('per').value == '') {
        alert('Periode belum dipilih.');
        return false;
    }
    return true;
}

/** Parameter filter yang dipakai tampilan, Excel, dan PDF */
function paramFormGajiUnitPph21(tipe) {
    return 'proses=preview'
        + '&unit=' + encodeURIComponent(document.getElementById('unit').value)
        + '&per=' + encodeURIComponent(document.getElementById('per').value)
        + '&tipekar=' + encodeURIComponent(document.getElementById('tipekar').value)
        + '&tipe=' + encodeURIComponent(tipe);
}

/** Tampilkan laporan di halaman */
function preview(tipe) {
    if (!validasiFormGajiUnitPph21()) {
        return;
    }
    post_response_text(urlSlaveGajiUnitPph21, paramFormGajiUnitPph21(tipe), respon);

    function respon() {
        if (con.readyState != 4) {
            return;
        }
        busy_off();
        if (con.status != 200) {
            error_catch(con.status);
            return;
        }
        if (!isSaveResponse(con.responseText)) {
            alert('ERROR TRANSACTION,\n' + con.responseText);
            return;
        }
        var arr = con.responseText.split('####');
        document.getElementById('printContainer').innerHTML = arr[0];
        leftFixedTable();
    }
}

/** Buka hasil tarikan di popup yang bisa diperbesar / di-resize */
function tampilTarikanGajiUnitPph21(judul, tujuan) {
    alertify
        .popup(judul, "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>")
        .set({ resizable: true, maximizable: true, overflow: false })
        .resizeTo('90%', '85%');
}

function excel() {
    if (!validasiFormGajiUnitPph21()) {
        return;
    }
    tampilTarikanGajiUnitPph21('Report Ms.Excel', urlSlaveGajiUnitPph21 + '?' + paramFormGajiUnitPph21('excel'));
}

function pdf() {
    if (!validasiFormGajiUnitPph21()) {
        return;
    }
    tampilTarikanGajiUnitPph21('Report PDF', urlSlaveGajiUnitPph21 + '?' + paramFormGajiUnitPph21('pdf'));
}
