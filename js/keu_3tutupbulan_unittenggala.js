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
                    alertify.alert("Informasi",con.responseText);
                } else {
                    //=== Success Response
                    alertify.alert("Informasi",'Proses tutup buku HO sukses');
                    logout();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    // if(confirm('Additional Closing Process this period for '+getValue('kodeorg')+
    //     '\n are you sure?')) {
    //     post_response_text('keu_slave_3tutupbulan_unittenggala.php?proses=tutupBuku', param, respon);
    // }

    alertify.confirm("Infomation","Close this period for "+getValue('kodeorg')+
        "\n are you sure?",
		function(){
			post_response_text('keu_slave_3tutupbulan_unittenggala.php?proses=tutupBuku', param, respon);
		},
		function(){
			return;
		}
	);
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