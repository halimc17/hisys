function tutupBuku() {
    var param = "kodeorg="+getValue('kodeorg')+"&periode="+getValue('periode');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	post_response_text('keu_slave_ceksebelumtutupbuku_ppp.php?proses=tutupBuku', param, respon);
}
function buatJurnalAutoTransit(kodeorg, periode, tipe) {
    var param = "kodeorg="+kodeorg+"&periode="+periode+"&tipe="+tipe;

    function responPreview() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    alertify.confirm("Konfirmasi", con.responseText, function () {
                        eksekusiJurnalAutoTransit(kodeorg, periode, tipe);
                    }, function () {});
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_ceksebelumtutupbuku_ppp.php?proses=previewJurnalAutoTransit', param, responPreview);
}

function eksekusiJurnalAutoTransit(kodeorg, periode, tipe) {
    var param = "kodeorg="+kodeorg+"&periode="+periode+"&tipe="+tipe;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                alertify.alert("Informasi", con.responseText);
                if (isSaveResponse(con.responseText)) {
                    tutupBuku();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_ceksebelumtutupbuku_ppp.php?proses=autoJurnalTransit', param, respon);
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