/* tutupBuku
 * Proses tutup buku bulanan unit.
 * Semua unit: periksa dulu persyaratan tutup buku (pengecekan yang sama dengan menu Cek Validasi Tutup Buku),
 * hanya kalau semua lolos baru proses tutup buku dijalankan.
 */
function tbEsc(teks) {
    return String(teks).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
// panel di tengah layar: progres, hasil pengecekan, lalu hasil proses
function tbPanel(judul, status, isi, tombol) {
    var p = document.getElementById('tbOverlay');
    if (!p) {
        p = document.createElement('div');
        p.setAttribute('id', 'tbOverlay');
        p.style.cssText = 'position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.45);z-index:100000;display:flex;align-items:center;justify-content:center;';
        document.body.appendChild(p);
    }
    p.style.display = 'flex';
    p.innerHTML = "<div style='background:#fff;flex:none;width:94vw;max-width:1100px;min-width:600px;max-height:88vh;overflow:auto;box-sizing:border-box;border-radius:6px;box-shadow:0 4px 24px rgba(0,0,0,0.4);font-size:13px;'>"
        + "<div style='padding:10px 14px;background:#3a5470;color:white;font-weight:bold;font-size:14px;'>" + tbEsc(judul) + "</div>"
        + "<div style='padding:14px;'>"
        + (status != '' ? "<div style='margin-bottom:8px;'>" + status + "</div>" : "")
        + (isi != '' ? "<div>" + isi + "</div>" : "")
        + (tombol != '' ? "<div style='text-align:right;margin-top:10px;'>" + tombol + "</div>" : "")
        + "</div></div>";
}
function tbTutupPanel() {
    var p = document.getElementById('tbOverlay');
    if (p) { p.style.display = 'none'; }
}
function tutupBuku() {
    var kodeorg = getValue('kodeorg');
    var periode = getValue('periode');
    var param = "kodeorg=" + kodeorg + "&periode=" + periode;

    alertify.confirm("Infomation", "Close this period for " + kodeorg + "\n are you sure?",
        function () {
            tbPeriksa(param, kodeorg, periode);
        },
        function () {
            return;
        }
    );
}
// tahap 1: periksa persyaratan tutup buku
function tbPeriksa(param, kodeorg, periode) {
    var judul = "Tutup buku " + kodeorg + " periode " + periode;
    tbPanel(judul, "<b>Tahap 1 dari 2</b> : memeriksa persyaratan tutup buku...<br><span style='font-size:11px;color:#555;'>Jangan tutup atau muat ulang halaman ini.</span>", '', '');

    function respon() {
        if (con.readyState == 4) {
            busy_off();
            if (con.status != 200) {
                tbTutupPanel();
                error_catch(con.status);
                return;
            }
            var hasil = con.responseText;
            if (hasil.indexOf('<!--CEK_LOLOS-->') > -1) {
                tbProses(param, judul, hasil);
            } else if (hasil.indexOf('<!--CEK_BELUM_LOLOS-->') > -1) {
                tbPanel(judul, "<b style='color:#d9362a;'>Tutup buku belum bisa dijalankan.</b> Selesaikan pengecekan yang belum lolos, lalu tekan Tutup Buku lagi.",
                    hasil, "<button class=mybutton onclick=\"tbTutupPanel();\">OK</button>");
            } else {
                tbTutupPanel();
                alertify.alert("Informasi", hasil);
            }
        }
    }
    post_response_text('keu_slave_ceksebelumtutupbuku_ppp.php?proses=tutupBuku', param + "&dariTutup=1", respon);
}
// tahap 2: proses tutup buku
function tbProses(param, judul, hasilCek) {
    tbPanel(judul, "<b>Tahap 2 dari 2</b> : "+(hasilCek != '' ? "semua pengecekan lolos, " : "")+"memproses tutup buku...<br><span style='font-size:11px;color:#555;'>Jangan tutup atau muat ulang halaman ini.</span>", hasilCek, '');

    function respon() {
        if (con.readyState == 4) {
            busy_off();
            if (con.status != 200) {
                tbTutupPanel();
                error_catch(con.status);
                return;
            }
            if (!isSaveResponse(con.responseText)) {
                var teks = con.responseText.replace(/\n/g, '<br>');
                tbPanel(judul, "<b style='color:#d9362a;'>Proses tutup buku belum berhasil.</b>", "<div style='padding:8px 10px;background:#fbeceb;border:1px solid #d9362a;'>" + teks + "</div>",
                    "<button class=mybutton onclick=\"tbTutupPanel();\">OK</button>");
            } else {
                tbPanel(judul, "<b style='color:green;'>&#10003; Proses Tutup Buku berhasil.</b><br>Setelah menekan OK anda akan keluar (logout) agar periode baru terbaca.", hasilCek,
                    "<button class=mybutton onclick=\"tbTutupPanel();logout();\">OK</button>");
            }
        }
    }
    post_response_text('keu_slave_3tutupbulan.php?proses=tutupBuku', param, respon);
}
function changeperiode(kodeorg) {
  param = 'kodeorg='+kodeorg.value;
  function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    document.getElementById('periode').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
      post_response_text('keu_slave_kwitansi.php?method=changeperiode', param, respon);
}