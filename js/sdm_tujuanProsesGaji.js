//Cabang PPP Proses Gaji (Penggajian Harian, Bulanan, Kecil Harian, Kecil Bulanan).
//orgPPP (daftar unit milik PT PPP) dan filePPP (daftar file sdm_slave_*_ppp.php yang ada) diisi oleh halaman prosesnya.
//Unit PPP memakai nama_ppp.php bila file itu ada; unit lain tetap memakai file biasa (tidak berubah).
//Untuk memisahkan langkah lain: salin file slave-nya menjadi nama_ppp.php dan bungkus nama file di JS dengan tujuanProsesGaji().
function tujuanProsesGaji(file) {
    var q = '';
    var p = file.indexOf('?');
    if (p > -1) {
        q = file.substring(p);
        file = file.substring(0, p);
    }
    var org = '';
    var el = document.getElementById('kodeorg');
    if (el) {
        org = el.value;
    }
    if (typeof orgPPP !== 'undefined' && typeof filePPP !== 'undefined' && org != '' && orgPPP.indexOf(org) > -1) {
        var filePpp = file.replace(/\.php$/, '_ppp.php');
        if (filePPP.indexOf(filePpp) > -1) {
            return filePpp + q;
        }
    }
    return file + q;
}
