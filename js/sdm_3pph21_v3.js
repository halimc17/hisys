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
    if (document.getElementById('sistem').value == '') {
        alert('Sistem gaji belum dipilih.');
        return false;
    }
    return true;
}

/** Teks aman untuk disisipkan ke HTML */
function escPph(t) {
    return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

/** Pesan dari server: buang awalan "Error," lalu escape, baris baru jadi <br> */
function pesanPph(t) {
    return escPph(String(t).replace(/^\s*(Error|Warning)\s*[,:]\s*/i, '')).replace(/\n/g, '<br>');
}

/** Notifikasi hasil dengan ikon dan judul. jenis: 'ok' | 'salah' | 'info'; isi = HTML */
function notifPph(jenis, judul, isi) {
    var w = { ok: ['#1a7f37', '#E3F4E8', '&#10003;'], salah: ['#b42318', '#FCE8E6', '&#10007;'], info: ['#2f6fb0', '#E4EEF9', 'i'] }[jenis];
    var html = "<div style='display:flex;gap:14px;align-items:flex-start;text-align:left'>"
        + "<span style='flex:0 0 36px;height:36px;line-height:36px;text-align:center;border-radius:50%;font-weight:bold;font-size:18px;background:" + w[1] + ";color:" + w[0] + "'>" + w[2] + "</span>"
        + "<div style='flex:1;font-size:13px;line-height:1.55;min-width:260px'>" + isi + "</div></div>";
    if (window.alertify && alertify.alert) {
        alertify.alert(judul, html);
    } else {
        alert(judul + '\n' + isi.replace(/<[^>]*>/g, ' '));
    }
}

/** Ringkasan hasil Proses PPh 21 (r = data dari server) */
function ringkasanProses(r) {
    var f = function (n) { return Number(n).toLocaleString('id-ID'); };
    var baris = function (label, nilai) {
        return "<tr><td style='padding:3px 18px 3px 0;color:#555'>" + label + "</td><td style='padding:3px 0;text-align:right;font-weight:bold'>" + nilai + "</td></tr>";
    };
    var h = "<div style='color:#555;font-size:12px;margin-bottom:6px'>" + escPph(r.unit) + " &middot; " + escPph(r.periode) + " &middot; Sistem gaji " + escPph(r.sistem) + "</div>";
    h += "<table style='border-collapse:collapse;width:100%'>";
    h += baris('Karyawan diproses', f(r.diproses));
    h += baris('Karyawan terkena PPh 21', f(r.terkena));
    h += baris('Total PPh 21 diproses', 'Rp ' + f(r.total));
    if (r.tunjangan != 0) {
        h += baris('Total tunjangan PPh 21', 'Rp ' + f(r.tunjangan));
    }
    if (r.lebih > 0) {
        h += "<tr><td style='padding:3px 18px 3px 0;color:#b42318'>Lebih bayar (tidak dipotong)</td><td style='padding:3px 0;text-align:right;font-weight:bold;color:#b42318'>Rp " + f(r.lebih) + "</td></tr>";
    }
    h += "</table>";
    h += r.rincian
        ? "<div style='margin-top:8px;font-size:12px;color:#1a7f37'>Rincian pendapatan tersimpan sebagai snapshot.</div>"
        : "<div style='margin-top:8px;font-size:12px;color:#8a5a00'>Rincian pendapatan belum tersimpan: tabel snapshot belum dibuat (jalankan sql_pph21_v3.sql).</div>";
    return h;
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
        + '&sistem=' + encodeURIComponent(document.getElementById('sistem').value)
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
        // kartu validasi tampil paling atas (di atas panel PJD), sisanya di area hasil
        var bagian = arr[0].split('@@VALIDASI@@');
        document.getElementById('validasiContainer').innerHTML = bagian.length > 1 ? bagian[0] : '';
        document.getElementById('printContainer').innerHTML = bagian.length > 1 ? bagian[1] : bagian[0];
        leftFixedTable();
        // unit PT PPP: panel upload dan panel PJD dimuat setelah tabel (permintaan dikirim satu per satu)
        muatPanelLain();
    }
}

/** True bila unit terpilih milik PT PPP (ditandai data-ppp di pilihan unit) */
function unitPpp() {
    var el = document.getElementById('unit');
    var opsi = el.options[el.selectedIndex];
    return opsi && opsi.getAttribute('data-ppp') == '1';
}

/** Parameter dasar (unit, periode, sistem gaji) untuk fitur penghasilan lain */
function paramDasarPph21() {
    return 'unit=' + encodeURIComponent(document.getElementById('unit').value)
        + '&per=' + encodeURIComponent(document.getElementById('per').value)
        + '&sistem=' + encodeURIComponent(document.getElementById('sistem').value);
}

/** Muat panel Penghasilan Lain (upload Excel; unit PT PPP), lalu lanjut memuat panel PJD */
function muatPanelLain() {
    var kotak = document.getElementById('lainContainer');
    if (!unitPpp()) {
        kotak.innerHTML = '';
        muatPanelPjd();
        return;
    }
    post_response_text(urlSlavePph21, 'proses=panellain&' + paramDasarPph21(), respon);

    function respon() {
        if (con.readyState != 4) {
            return;
        }
        busy_off();
        if (con.status == 200 && isSaveResponse(con.responseText)) {
            kotak.innerHTML = con.responseText;
        } else if (con.status == 200) {
            alert(con.responseText);
        }
        muatPanelPjd();
    }
}

/** Unduh template Excel penghasilan lain (daftar karyawan sudah terisi, tinggal isi angka) */
function unduhTemplateLain() {
    if (!validasiFormPph21()) {
        return;
    }
    var nama = 'Template_PenghasilanLain_' + document.getElementById('unit').value + '_' + document.getElementById('per').value + '_' + document.getElementById('sistem').value + '.xlsx';
    var x = new XMLHttpRequest();
    x.open('GET', urlSlavePph21 + '?proses=templatelain&' + paramDasarPph21(), true);
    x.responseType = 'blob';
    x.onload = function () {
        if (x.status == 200 && x.response.type.indexOf('spreadsheetml') >= 0) {
            var a = document.createElement('a');
            a.href = URL.createObjectURL(x.response);
            a.download = nama;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(function () { URL.revokeObjectURL(a.href); }, 2000);
        } else {
            // server mengirim pesan (bukan file): tampilkan sebagai teks
            var fr = new FileReader();
            fr.onload = function () { alert(fr.result); };
            fr.readAsText(x.response);
        }
    };
    x.onerror = function () { alert('Template gagal diunduh, coba lagi.'); };
    x.send();
}

/** Upload file Excel penghasilan lain, lalu segarkan tabel PPh 21 */
function uploadLain() {
    if (!validasiFormPph21()) {
        return;
    }
    var input = document.getElementById('fileLain');
    if (!input || input.files.length == 0) {
        alert('File Excel belum dipilih.');
        return;
    }
    var file = input.files[0];
    if (!/\.xlsx$/i.test(file.name)) {
        alert('Format file harus .xlsx (pakai template dari tombol Unduh Template).');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2 MB.');
        return;
    }
    if (!confirm('Upload ' + file.name + ' untuk unit ' + document.getElementById('unit').value + ' periode ' + document.getElementById('per').value + ' sistem gaji ' + document.getElementById('sistem').value + '?\nAngka karyawan yang ada di file akan menggantikan angka sebelumnya.')) {
        return;
    }
    var tombol = document.getElementById('btnUploadLain');
    tombol.disabled = true;
    tombol.innerHTML = 'Mengunggah...';

    var data = new FormData();
    data.append('proses', 'uploadlain');
    data.append('unit', document.getElementById('unit').value);
    data.append('per', document.getElementById('per').value);
    data.append('sistem', document.getElementById('sistem').value);
    data.append('file', file);

    var x = new XMLHttpRequest();
    x.open('POST', urlSlavePph21, true);
    x.onload = function () {
        if (x.status == 200 && isSaveResponse(x.responseText)) {
            notifPph('ok', 'Upload selesai', pesanPph(x.responseText));
            preview('previewawal');
        } else {
            tombol.disabled = false;
            tombol.innerHTML = 'Upload';
            notifPph('salah', 'Upload gagal', x.status == 200 ? pesanPph(x.responseText) : 'Kode kesalahan ' + x.status + '.');
        }
    };
    x.onerror = function () {
        tombol.disabled = false;
        tombol.innerHTML = 'Upload';
        alert('Upload gagal, periksa koneksi lalu coba lagi.');
    };
    x.send(data);
}

/** Hapus seluruh angka upload periode ini (sistem gaji terpilih) */
function hapusLain() {
    if (!validasiFormPph21()) {
        return;
    }
    if (!confirm('Hapus seluruh angka penghasilan lain yang diunggah untuk unit ' + document.getElementById('unit').value + ' periode ' + document.getElementById('per').value + ' sistem gaji ' + document.getElementById('sistem').value + '?\nTindakan ini tidak bisa dibatalkan.')) {
        return;
    }
    post_response_text(urlSlavePph21, 'proses=hapuslain&' + paramDasarPph21(), respon);

    function respon() {
        if (con.readyState != 4) {
            return;
        }
        busy_off();
        if (con.status != 200) {
            error_catch(con.status);
            return;
        }
        if (isSaveResponse(con.responseText)) {
            notifPph('ok', 'Data upload dihapus', pesanPph(con.responseText));
            preview('previewawal');
        } else {
            notifPph('salah', 'Hapus gagal', pesanPph(con.responseText));
        }
    }
}

/** Muat panel Penghasilan Perjalanan Dinas (hanya baca; unit PT PPP, selain itu panel dikosongkan) */
function muatPanelPjd() {
    var kotak = document.getElementById('pjdContainer');
    if (!unitPpp()) {
        kotak.innerHTML = '';
        return;
    }
    var param = 'proses=panelpjd'
        + '&unit=' + encodeURIComponent(document.getElementById('unit').value)
        + '&per=' + encodeURIComponent(document.getElementById('per').value);

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
            alert(con.responseText);
            return;
        }
        kotak.innerHTML = con.responseText;
    }
}

/** Aktifkan isian nilai tunjangan PPh 21 hanya jika dicentang */
function makeenabled() {
    document.getElementById('nilaitjpph21').disabled = !document.getElementById('istjpph21').checked;
}

/**
 * Proses PPh 21 seluruh karyawan sekaligus: satu permintaan, dihitung ulang di server, disimpan dalam satu transaksi.
 * jumlah = jumlah karyawan di tabel (hanya untuk teks konfirmasi)
 */
function prosesPph(jumlah) {
    if (!validasiFormPph21()) {
        return;
    }
    var unit = document.getElementById('unit').value;
    var per = document.getElementById('per').value;
    var sistem = document.getElementById('sistem').value;
    if (!confirm('Proses PPh 21 unit ' + unit + ' periode ' + per + ' untuk ' + jumlah + ' karyawan sistem gaji ' + sistem + '?\nHasil proses sebelumnya untuk karyawan sistem gaji ini akan diganti.')) {
        return;
    }
    var tombol = document.getElementById('btnProsesPph');
    if (tombol) {
        tombol.disabled = true;
        tombol.innerHTML = 'Memproses...';
    }

    var param = 'proses=prosessemua'
        + '&per=' + encodeURIComponent(per)
        + '&unit=' + encodeURIComponent(unit)
        + '&sistem=' + encodeURIComponent(sistem);

    post_response_text(urlSlavePph21, param, respog);

    function respog() {
        if (con.readyState != 4) {
            return;
        }
        busy_off();
        if (con.status != 200) {
            if (tombol) {
                tombol.disabled = false;
            }
            error_catch(con.status);
            return;
        }
        if (!isSaveResponse(con.responseText)) {
            if (tombol) {
                tombol.disabled = false;
                tombol.innerHTML = 'Proses PPh 21 (' + jumlah + ' karyawan)';
            }
            notifPph('salah', 'Proses PPh 21 gagal', pesanPph(con.responseText));
            return;
        }
        var bagian = con.responseText.split('@@RINGKAS@@');
        var ringkas = null;
        try {
            ringkas = JSON.parse(bagian[1]);
        } catch (e) {
            ringkas = null;
        }
        notifPph('ok', 'Proses PPh 21 selesai', ringkas ? ringkasanProses(ringkas) : pesanPph(bagian[0]));
        preview('previewawal');   // segarkan: kolom Tersimpan dan ringkasan ikut diperbarui
    }
}

/** Tampilkan / sembunyikan kolom rincian komponen (pendapatan dan pengurang) */
function toggleRinci(tampil) {
    var tabel = document.getElementById('tabelpph21');
    if (!tabel) {
        return;
    }
    if (tampil) {
        tabel.className = tabel.className.replace(/\s*sembunyiRinci/g, '');
    } else if (tabel.className.indexOf('sembunyiRinci') < 0) {
        tabel.className += ' sembunyiRinci';
    }
    // grup PENGURANG PAJAK (Desember): selebar biaya jabatan saja, atau ditambah kolom rincian iuran bila rincian dibuka
    var grup = tabel.querySelectorAll('th.grupPengurang');
    for (var g = 0; g < grup.length; g++) {
        var dasar = parseInt(grup[g].getAttribute('data-dasar'), 10) || 1;
        var rinci = parseInt(grup[g].getAttribute('data-rinci'), 10) || 0;
        grup[g].colSpan = tampil ? dasar + rinci : dasar;
    }
    leftFixedTable();
}

/** Saring baris karyawan menurut NIK / nama yang diketik */
function cariKaryawan() {
    var kata = document.getElementById('cariKaryawan').value.toLowerCase().replace(/^\s+|\s+$/g, '');
    var baris = document.querySelectorAll('#tabelpph21 tbody tr[id^=row]');
    var tampil = 0;
    for (var i = 0; i < baris.length; i++) {
        var sel = baris[i].getElementsByTagName('td');
        // sel 0 = id tersembunyi, 1 = no, 2 = NIK, 3 = nama
        var teks = (sel[2].textContent + ' ' + sel[3].textContent).toLowerCase();
        var cocok = (kata == '' || teks.indexOf(kata) >= 0);
        baris[i].style.display = cocok ? '' : 'none';
        if (cocok) {
            tampil++;
        }
    }
    document.getElementById('jmlTampil').innerHTML = (kata == '') ? '' : tampil + ' dari ' + baris.length + ' karyawan';
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
