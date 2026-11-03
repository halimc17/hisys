// JavaScript Document - Daftar Penerimaan Barang
//isi pilihan (select2) lalu perbarui tampilannya
function setSelDPB(id, v) {
    var el = document.getElementById(id);
    el.value = v;
    if (window.jQuery) {
        jQuery(el).trigger('change.select2');
    }
}

//kembali ke tampilan awal (tahun sekarang, tanpa filter lain)
function displaylist() {
    var teks = ['txtNoTransaksi', 'txtsearch', 'txtsearch2', 'nmBrg', 'tgl_cari'];
    for (var i = 0; i < teks.length; i++) {
        document.getElementById(teks[i]).value = '';
    }
    setSelDPB('kdGdng', '');
    setSelDPB('kodesupplier', '');
    setSelDPB('statusposting', '');
    setSelDPB('filterTahun', String(new Date().getFullYear()));
    cariData();
}

//parameter filter yang dipakai list, paging, dan tarikan Excel/PDF
function getFilterParamDPB() {
    var p = 'txtNoTransaksi=' + encodeURIComponent(trim(document.getElementById('txtNoTransaksi').value));
    p += '&txtSearch=' + encodeURIComponent(trim(document.getElementById('txtsearch').value));
    p += '&nopp=' + encodeURIComponent(trim(document.getElementById('txtsearch2').value));
    p += '&nmBrg=' + encodeURIComponent(trim(document.getElementById('nmBrg').value));
    p += '&tglCari=' + encodeURIComponent(trim(document.getElementById('tgl_cari').value));
    p += '&filterTahun=' + encodeURIComponent(document.getElementById('filterTahun').value);
    p += '&kdGudang=' + encodeURIComponent(document.getElementById('kdGdng').value);
    p += '&kodesupplier=' + encodeURIComponent(document.getElementById('kodesupplier').value);
    p += '&statusposting=' + encodeURIComponent(document.getElementById('statusposting').value);
    return p;
}

//validasi di sisi browser (server memeriksa ulang)
function validasiFilterDPB() {
    var nopp = trim(document.getElementById('txtsearch2').value);
    if (nopp != '' && nopp.length < 3) {
        alert('Error: masukan nopp min 3 karakter');
        return false;
    }
    return true;
}

function tampilTarikanDPB(judul, tujuan) {
    alertify.popup(judul, "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable': true, 'maximizable': true, 'overflow': false}).resizeTo('80%', '70%');
}
function exportExcelDPB() {
    if (!validasiFilterDPB()) return;
    tampilTarikanDPB('Report Ms.Excel', 'log_slave_2penerimaan.php?method=excellist&' + getFilterParamDPB());
}
function exportPdfDPB() {
    if (!validasiFilterDPB()) return;
    tampilTarikanDPB('Report PDF', 'log_slave_2penerimaan.php?method=pdflist&' + getFilterParamDPB());
}

function kirimListDPB(page) {
    if (!validasiFilterDPB()) return;
    var param = 'method=list_new_data&' + getFilterParamDPB();
    if (page !== undefined) {
        param += '&page=' + page;
    }
    post_response_text('log_slave_2penerimaan.php', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contain').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function cariData() {
    kirimListDPB(0);
}
function cariPage(num) {
    kirimListDPB(num);
}

//rincian barang (kode, nama, satuan, qty) satu penerimaan dalam popup
function detailBarangDPB(notransaksi, ev) {
    post_response_text('log_slave_2penerimaan.php', 'method=detailbarang&notransaksi=' + encodeURIComponent(notransaksi), respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup('Detail Barang ' + notransaksi, con.responseText).set({'resizable': true, 'maximizable': true, 'overflow': false}).resizeTo('70%', '60%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//cetak BAPB (PDF) dalam popup yang bisa diperbesar/dikecilkan
function previewBapb(notransaksi, ev) {
    var tujuan = 'log_slave_print_bapb_pdf.php?notransaksi=' + encodeURIComponent(notransaksi);
    alertify.popuppdf("PDF", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "#zoom=page-width'></iframe>").set({'resizable': true, 'overflow': false}).resizeTo('90%', '90%');
}
