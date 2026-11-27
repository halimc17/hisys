// HKE (Hari Kerja Efektif) per PT: HKE per periode dan komponen dasar upah harian
var slaveHke = 'sdm_slave_5hkEfektif.php';

function DaysInMonth(y, m) {
    return new Date(y, m, 0).getDate();
}

// PT yang sedang dipilih di setup
function hkePT() {
    return document.getElementById('hkePT').value;
}

// isi nilai dropdown select2 lewat kode tanpa memicu onchange bawaan
function setSel2(id, nilai) {
    var el = document.getElementById(id);
    if (!el) {
        return;
    }
    el.value = nilai;
    if (window.jQuery && jQuery(el).data('select2')) {
        var h = el.onchange;
        el.onchange = null;
        jQuery(el).trigger('change.select2');
        el.onchange = h;
    }
}

// kirim ke slave; sukses(teks) dipanggil bila jawaban bukan pesan Warning/Error
function kirimHke(param, sukses) {
    post_response_text(slaveHke, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    sukses(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// koneksi ajax di aplikasi ini satu (con): permintaan kedua tidak boleh dikirim sebelum yang pertama selesai, jadi lanjut() dipanggil sesudahnya
// muat semua data PT terpilih: HKE, baris komponen, daftar pengaturan komponen, lalu keterangan status
function hkeMuat() {
    hkeBatal();
    kompBatal();
    hkeLoad(function () {
        kompForm(function () {
            kompLoad(function () {
                hkeInfo();
            });
        });
    });
}

function hkeInfo() {
    kirimHke('method=info&kodept=' + encodeURIComponent(hkePT()), function (t) {
        var p = t.split('|||');
        document.getElementById('hkeStatus').innerHTML = p[0];
        document.getElementById('kompBerlaku').innerHTML = (p.length > 1 ? p[1] : '');
        document.getElementById('hkeStatus').className = 'hkeBadge ' + (p.length > 4 ? p[4] : 'off');
        setSel2('hkeAktif', (p.length > 2 && p[2] == '1') ? '1' : '0');
        setSel2('hkeMulai', p.length > 3 ? p[3] : '');
        hkeIsiIngat(p);
    });
}

// pengingat HKE periode berikutnya (harus diinput sebelum tanggal 1); p[5] = tingkat (ok, warn, info), p[6] = isi
function hkeIsiIngat(p) {
    var kotak = document.getElementById('hkeIngat');
    if (!kotak) {
        return;
    }
    if (p.length > 6 && p[6] != '') {
        kotak.className = 'hkeIngat ' + p[5];
        kotak.innerHTML = p[6];
        kotak.style.display = 'block';
    } else {
        kotak.style.display = 'none';
    }
}

// segarkan hanya pengingat (dipakai setelah HKE disimpan atau dihapus; isian pengaktifan tidak disentuh)
function hkeIngatMuat() {
    kirimHke('method=info&kodept=' + encodeURIComponent(hkePT()), function (t) {
        hkeIsiIngat(t.split('|||'));
    });
}

// simpan pengaktifan: aktif atau tidak dan periode mulai berlaku
function hkeAktifSimpan() {
    var a = document.getElementById('hkeAktif').value;
    var m = document.getElementById('hkeMulai').value;
    if (a == '1' && m == '') {
        alert('Silakan memilih periode mulai berlaku.');
        return;
    }
    function kirimAktif() {
        kirimHke('method=aktifSimpan&kodept=' + encodeURIComponent(hkePT()) + '&aktif=' + encodeURIComponent(a) + '&mulai=' + encodeURIComponent(m), function () {
            hkeInfo();
            alert('Done.');
        });
    }
    if (a == '1') {
        // tampilkan dulu komponen dasar upah yang akan berlaku pada periode mulai, supaya terlihat sebelum disimpan
        kirimHke('method=komponenPada&kodept=' + encodeURIComponent(hkePT()) + '&periode=' + encodeURIComponent(m), function (komp) {
            if (confirm('Aktifkan HKE PT ' + hkePT() + ' mulai periode ' + m + '?' + String.fromCharCode(10) + String.fromCharCode(10) +
                'Dasar upah harian karyawan Bulanan: ' + komp + String.fromCharCode(10) +
                'Dibagi HKE periode masing-masing. Transaksi yang sudah tersimpan tidak berubah.')) {
                kirimAktif();
            }
        });
    } else {
        if (confirm('Nonaktifkan HKE PT ' + hkePT() + '? Transaksi baru kembali memakai pembagi 25.')) {
            kirimAktif();
        }
    }
}

function hkeLoad(lanjut) {
    kirimHke('method=hkeList&kodept=' + encodeURIComponent(hkePT()), function (t) {
        document.getElementById('hkeContainer').innerHTML = t;
        if (typeof lanjut === 'function') {
            lanjut();
        }
    });
}

// HKE = hari dalam bulan - hari Minggu - hari libur (dihitung ulang oleh server saat simpan)
function hkeHitung() {
    var p = document.getElementById('hkePeriode').value;
    if (p == '') {
        document.getElementById('hkeHke').value = '';
        return;
    }
    var tb = p.split('-');
    var hari = DaysInMonth(tb[0], tb[1]);
    var mgg = parseInt(document.getElementById('hkeMinggu').value || '0', 10);
    var lib = parseInt(document.getElementById('hkeLibur').value || '0', 10);
    document.getElementById('hkeHke').value = hari - mgg - lib;
}

function hkeSaranLoad() {
    var p = document.getElementById('hkePeriode').value;
    hkeHitung();
    if (p == '') {
        document.getElementById('hkeSaran').innerHTML = '';
        return;
    }
    kirimHke('method=hkeSaran&kodept=' + encodeURIComponent(hkePT()) + '&periode=' + encodeURIComponent(p), function (t) {
        document.getElementById('hkeSaran').innerHTML = t;
    });
}

function hkeTerap(mgg, lib) {
    document.getElementById('hkeMinggu').value = mgg;
    document.getElementById('hkeLibur').value = lib;
    hkeHitung();
}

function hkeIsi(periode, mgg, lib, catatan) {
    setSel2('hkePeriode', periode);
    document.getElementById('hkeMinggu').value = mgg;
    document.getElementById('hkeLibur').value = lib;
    document.getElementById('hkeCatatan').value = catatan;
    hkeHitung();
    hkeSaranLoad();
}

function hkeBatal() {
    setSel2('hkePeriode', '');
    document.getElementById('hkeMinggu').value = '';
    document.getElementById('hkeLibur').value = '';
    document.getElementById('hkeHke').value = '';
    document.getElementById('hkeCatatan').value = '';
    document.getElementById('hkeSaran').innerHTML = '';
}

function hkeSimpan() {
    var p = document.getElementById('hkePeriode').value;
    var mgg = document.getElementById('hkeMinggu').value;
    var lib = document.getElementById('hkeLibur').value;
    if (p == '') {
        alert('Silakan memilih periode.');
        return;
    }
    if (mgg == '' || lib == '') {
        alert('Hari Minggu dan Hari Libur wajib diisi (isi 0 bila tidak ada).');
        return;
    }
    if (!/^[0-9]{1,2}$/.test(mgg) || !/^[0-9]{1,2}$/.test(lib)) {
        alert('Hari Minggu dan Hari Libur harus berupa angka.');
        return;
    }
    if (parseInt(mgg, 10) > 6) {
        alert('Hari Minggu tidak boleh lebih dari 6.');
        return;
    }
    hkeHitung();
    var tb = p.split('-');
    var hke = parseInt(document.getElementById('hkeHke').value, 10);
    if (isNaN(hke) || hke < 1 || hke > DaysInMonth(tb[0], tb[1])) {
        alert('HKE hasil hitung (' + hke + ') tidak masuk akal. Periksa Hari Minggu dan Hari Libur.');
        return;
    }
    if (!confirm('Simpan HKE PT ' + hkePT() + ' periode ' + p + ' = ' + document.getElementById('hkeHke').value + ' hari?')) {
        return;
    }
    kirimHke('method=hkeSimpan&kodept=' + encodeURIComponent(hkePT()) + '&periode=' + encodeURIComponent(p) + '&hariminggu=' + encodeURIComponent(mgg) + '&harilibur=' + encodeURIComponent(lib) +
        '&catatan=' + encodeURIComponent(document.getElementById('hkeCatatan').value), function () {
        hkeLoad(hkeIngatMuat);
        hkeBatal();
        alert('Done.');
    });
}

function hkeHapus(periode) {
    if (!confirm('Hapus HKE PT ' + hkePT() + ' periode ' + periode + '?')) {
        return;
    }
    kirimHke('method=hkeHapus&kodept=' + encodeURIComponent(hkePT()) + '&periode=' + encodeURIComponent(periode), function () {
        hkeLoad(hkeIngatMuat);
        hkeBatal();
    });
}

// baris centang komponen untuk PT terpilih
function kompForm(lanjut) {
    kirimHke('method=kompForm&kodept=' + encodeURIComponent(hkePT()), function (t) {
        document.getElementById('kompForm').innerHTML = t;
        if (typeof lanjut === 'function') {
            lanjut();
        }
    });
}

// daftar karyawan yang memakai satu komponen (klik jumlah karyawan)
function kompKaryawan(idkomponen, ev) {
    kirimHke('method=kompKaryawan&kodept=' + encodeURIComponent(hkePT()) + '&idkomponen=' + encodeURIComponent(idkomponen), function (t) {
        showDialog1('Karyawan', t, '720', '420', ev);
    });
}

function kompLoad(lanjut) {
    kirimHke('method=kompList&kodept=' + encodeURIComponent(hkePT()), function (t) {
        document.getElementById('kompContainer').innerHTML = t;
        if (typeof lanjut === 'function') {
            lanjut();
        }
    });
}

function kompBatal() {
    setSel2('kompMulai', '');
    var cek = document.getElementsByClassName('kompChk');
    for (var i = 0; i < cek.length; i++) {
        cek[i].checked = (cek[i].value == '1');
    }
}

function kompIsi(mulai, daftar) {
    kompBatal();
    setSel2('kompMulai', mulai);
    var ids = ',' + daftar + ',';
    var cek = document.getElementsByClassName('kompChk');
    for (var i = 0; i < cek.length; i++) {
        cek[i].checked = (ids.indexOf(',' + cek[i].value + ',') > -1);
    }
}

function kompSimpan() {
    var mulai = document.getElementById('kompMulai').value;
    if (mulai == '') {
        alert('Silakan memilih periode mulai berlaku.');
        return;
    }
    var pilih = [];
    var cek = document.getElementsByClassName('kompChk');
    for (var i = 0; i < cek.length; i++) {
        if (cek[i].checked) {
            pilih.push(cek[i].value);
        }
    }
    if (!confirm('Simpan komponen dasar upah harian PT ' + hkePT() + ' mulai periode ' + mulai + ' (' + pilih.length + ' komponen)?')) {
        return;
    }
    kirimHke('method=kompSimpan&kodept=' + encodeURIComponent(hkePT()) + '&mulai=' + encodeURIComponent(mulai) + '&komponen=' + encodeURIComponent(pilih.join(',')), function () {
        kompLoad(hkeInfo);
        kompBatal();
        alert('Done.');
    });
}

function kompHapus(mulai) {
    if (!confirm('Hapus pengaturan komponen dasar upah PT ' + hkePT() + ' mulai periode ' + mulai + '?')) {
        return;
    }
    kirimHke('method=kompHapus&kodept=' + encodeURIComponent(hkePT()) + '&mulai=' + encodeURIComponent(mulai), function () {
        kompLoad(hkeInfo);
        kompBatal();
    });
}
