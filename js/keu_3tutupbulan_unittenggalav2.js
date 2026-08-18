/* tutupBuku
 * Fungsi untuk melakukan proses tutup buku bulanan
 */
function tutupBuku() {
    var param = "kodeorg="+getValue('kodeorg')+"&periode="+getValue('periode');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    alert('Proses tutup buku HO sukses');
                    logout();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(confirm('Additional Closing Process this period for '+getValue('kodeorg')+
        '\n are you sure?')) {
        post_response_text('keu_slave_3tutupbulan_unittenggalav2.php?proses=tutupBuku', param, respon);
    }
}