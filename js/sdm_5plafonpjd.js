//set nilai select2 tanpa memicu onchange bawaan select (getunit/loadData)
//onchange inline dimatikan sebentar karena trigger jQuery ikut menjalankannya
function setSel(id, v) {
    var el = document.getElementById(id);
    var handler = el.onchange;
    el.onchange = null;
    //nilai yang belum ada di pilihan (mis. unit lama) ditambahkan dulu supaya tetap terpilih
    if (v !== '' && $('#' + id + ' option').filter(function () { return this.value == v; }).length == 0) {
        $('#' + id).append($('<option>').val(v).text(v));
    }
    $('#' + id).val(v).trigger('change.select2');
    el.onchange = handler;
}

$(document).ready(function () {
    $('.select2').select2({dropdownAutoWidth: true});
});

//field kunci terkunci saat edit (hanya rupiah yang boleh diubah)
var fieldKunci = ['pt', 'unit', 'regional', 'tipekaryawan', 'levelkaryawan', 'kodegolongan', 'jenis'];

function cancel() {
    for (var i = 0; i < fieldKunci.length; i++) {
        document.getElementById(fieldKunci[i]).disabled = false;
    }
    setSel('regional', '');
    setSel('levelkaryawan', '');
    setSel('kodegolongan', '');
    setSel('tipekaryawan', '');
    setSel('jenis', '');
    document.getElementById('rupiah').value = '0';
    document.getElementById('kode').value = '';
    //PT user non-HOLDING hanya satu pilihan dan tetap terpilih; selain itu dikosongkan
    var pt = document.getElementById('pt');
    if (pt.options[0].value == '') {
        setSel('pt', '');
    }
    setSel('unit', '');
    document.getElementById('method').value = 'insert';
}

function getunit() {
    var pt = document.getElementById('pt').value;
    if (pt == '') {
        return;
    }
    var param = 'pt=' + encodeURIComponent(pt) + '&method=getunit';
    var tujuan = 'sdm_slave_5plafonpjd.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('unit').innerHTML = trim(con.responseText);
                    $('#unit').trigger('change.select2');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData(num) {
    var param = 'method=loadData&page=' + (num ? num : 0);
    param += '&regional=' + encodeURIComponent(document.getElementById('regionalsch').value);
    param += '&tipekaryawan=' + encodeURIComponent(document.getElementById('tipekaryawansch').value);
    param += '&levelkaryawan=' + encodeURIComponent(document.getElementById('levelkaryawansch').value);
    param += '&kodegolongan=' + encodeURIComponent(document.getElementById('kodegolongansch').value);
    param += '&jenis=' + encodeURIComponent(document.getElementById('jenissch').value);
    param += '&pt=' + encodeURIComponent(document.getElementById('ptsch').value);
    param += '&unit=' + encodeURIComponent(document.getElementById('unitsch').value);

    var tujuan = 'sdm_slave_5plafonpjd.php';
    post_response_text(tujuan, param, respon);

    function respon() {
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

function getPage() {
    var pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    loadData(parseFloat(pg) - 1);
}

function fillfield(kode, pt, unit, regiontujuan, tipekaryawan, levelkaryawan, golongan, jenis, jumlah) {
    setSel('pt', pt);
    setSel('unit', unit);
    setSel('regional', regiontujuan);
    setSel('tipekaryawan', tipekaryawan);
    setSel('levelkaryawan', levelkaryawan);
    setSel('kodegolongan', golongan);
    setSel('jenis', jenis);
    document.getElementById('kode').value = kode;
    document.getElementById('rupiah').value = jumlah;
    if (jumlah !== '' && parseFloat(jumlah) != 0) {
        z.numberFormat('rupiah', 2);
    }
    document.getElementById('method').value = 'update';
    for (var i = 0; i < fieldKunci.length; i++) {
        document.getElementById(fieldKunci[i]).disabled = true;
    }
}

function deleteData(kode) {
    if (!confirm('Anda yakin hapus item ini?')) {
        return;
    }
    var param = 'kode=' + encodeURIComponent(kode) + '&method=delete';
    var tujuan = 'sdm_slave_5plafonpjd.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData(0);
                    cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function save() {
    var method = trim(document.getElementById('method').value);
    var pt = document.getElementById('pt').value;
    var unit = document.getElementById('unit').value;
    var regional = document.getElementById('regional').value;
    var jenis = document.getElementById('jenis').value;
    var rupiah = remove_comma_var(trim(document.getElementById('rupiah').value));

    if (method == 'insert' && (pt == '' || unit == '' || regional == '' || jenis == '')) {
        alert('Warning : PT, unit, regional tujuan, dan jenis wajib diisi.');
        return;
    }
    if (rupiah != '' && (isNaN(rupiah) || parseFloat(rupiah) < 0)) {
        alert('Warning : Nilai rupiah tidak valid, isi dengan angka tidak negatif.');
        return;
    }

    var param = 'method=' + method + '&kode=' + encodeURIComponent(document.getElementById('kode').value);
    param += '&pt=' + encodeURIComponent(pt);
    param += '&unit=' + encodeURIComponent(unit);
    param += '&regional=' + encodeURIComponent(regional);
    param += '&tipekaryawan=' + encodeURIComponent(document.getElementById('tipekaryawan').value);
    param += '&levelkaryawan=' + encodeURIComponent(document.getElementById('levelkaryawan').value);
    param += '&kodegolongan=' + encodeURIComponent(document.getElementById('kodegolongan').value);
    param += '&jenis=' + encodeURIComponent(jenis);
    param += '&rupiah=' + encodeURIComponent(rupiah);
    var tujuan = 'sdm_slave_5plafonpjd.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData(0);
                    cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
