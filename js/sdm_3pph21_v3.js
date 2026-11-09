// PPh Pasal 21 v3 : tampilkan perhitungan, proses simpan per karyawan, dan tarikan Excel / PDF

var urlSlavePph21 = 'sdm_slave_3pph21_v3.php';

/** Validasi isian form: unit dan periode wajib dipilih. Mengembalikan true bila lengkap. */
function validasiFormPph21() {
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

/** Parameter isian form yang dipakai preview, Excel, dan PDF */
function paramFormPph21(tipe) {
    var unit = document.getElementById('unit').value;
    var per = document.getElementById('per').value;
    var istjpph21 = document.getElementById('istjpph21').checked ? 1 : 0;
    var nilaitjpph21 = document.getElementById('nilaitjpph21').value;

    return 'proses=preview'
        + '&unit=' + encodeURIComponent(unit)
        + '&per=' + encodeURIComponent(per)
        + '&tipe=' + encodeURIComponent(tipe)
        + '&istjpph21=' + istjpph21
        + '&nilaitjpph21=' + encodeURIComponent(nilaitjpph21);
}

/** Tampilkan hasil perhitungan di halaman */
function preview(tipe) {
    if (!validasiFormPph21()) {
        return;
    }
    var param = paramFormPph21(tipe);

    post_response_text(urlSlavePph21, param, respon);

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

/** Aktifkan isian nilai tunjangan PPh 21 hanya jika dicentang */
function makeenabled() {
    document.getElementById('nilaitjpph21').disabled = !document.getElementById('istjpph21').checked;
}

/** Mulai proses simpan dari baris pertama sampai baris terakhir */
function prosespph(maxRow) {
    if (!validasiFormPph21()) {
        return;
    }
    looppph(1, maxRow);
}

/** Simpan PPh 21 satu karyawan (baris currRow), lalu lanjut ke baris berikutnya */
function looppph(currRow, maxRow) {
    var karyawanid = trim(document.getElementById('karyawanid' + currRow).innerHTML);
    var pph21x = trim(document.getElementById('pph21x' + currRow).innerHTML.replace(/,/g, ''));
    var unit = document.getElementById('unit').value;
    var per = document.getElementById('per').value;

    var param = 'proses=prosespph'
        + '&per=' + encodeURIComponent(per)
        + '&karyawanid=' + encodeURIComponent(karyawanid)
        + '&unit=' + encodeURIComponent(unit)
        + '&pph21x=' + encodeURIComponent(pph21x)
        + '&baris=' + currRow;

    post_response_text(urlSlavePph21, param, respog);

    function respog() {
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
            document.getElementById('row' + currRow).style.backgroundColor = 'red';
            unlockScreen();
            return;
        }

        // baris sukses disembunyikan, lanjut ke karyawan berikutnya
        document.getElementById('row' + currRow).style.display = 'none';
        if (currRow + 1 > maxRow) {
            alert('Done');
        } else {
            looppph(currRow + 1, maxRow);
        }
    }
}

/** Buka hasil tarikan di popup yang bisa diperbesar / di-resize */
function tampilTarikanPph21(judul, tujuan) {
    alertify
        .popup(judul, "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>")
        .set({ resizable: true, maximizable: true, overflow: false })
        .resizeTo('90%', '85%');
}

/** Tarik hasil perhitungan ke Excel */
function excel() {
    if (!validasiFormPph21()) {
        return;
    }
    tampilTarikanPph21('Report Ms.Excel', urlSlavePph21 + '?' + paramFormPph21('excel'));
}

/** Tarik hasil perhitungan ke PDF */
function pdf() {
    if (!validasiFormPph21()) {
        return;
    }
    tampilTarikanPph21('Report PDF', urlSlavePph21 + '?' + paramFormPph21('pdf'));
}
