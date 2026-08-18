/* listPosting
 * Fungsi untuk men-generate list dari transaksi yang dapat di posting
 */
function listPosting() {
    var listPost = document.getElementById('listPosting');
    var param = "kodeorg="+getValue('kodeorg')+"&tanggal="+getValue('tanggal')+"&jenisData=hppolah";

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    listPost.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_3hppolah.php?proses=list', param, respon); 
}

function postHppOlah(row){
  var strDt;
    for(awl=1;awl<=row;awl++){
      var noakunAlk=document.getElementById('dtNoakunAlk_'+awl).innerHTML;
      var rupiahAlk=document.getElementById('dtRupiahAlk_'+awl).innerHTML;
        if(awl==1){
            strDt="&noakunAlk[]="+noakunAlk+"&rupiahAlk[]="+rupiahAlk;
        }else{
            strDt+="&noakunAlk[]="+noakunAlk+"&rupiahAlk[]="+rupiahAlk;
        }
    }
    var param = "kodeorg="+getValue('kodeorg')+"&tanggalDt="+getValue('tanggalDt_1')+'&saldoawalDt='+getValue('saldoawalDt');
    param+='&tbsOlah='+getValue('tbsOlah')+'&rpOlah='+getValue('rupiahOlah')+'&kgOlah='+getValue('tbsOlah');
    param+='&dtTbsAkhir='+getValue('dtTbsAkhir')+'&hargaRata='+getValue('hargaRata')+'&totalByRupiah='+getValue('totalByRupiah'); 
    param+=strDt;
    tujuan='keu_slave_3hppolah.php?proses=post';
   
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    alert('Done');
                    document.getElementById('listPosting').innerHTML="";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }     
    post_response_text(tujuan, param, respon);
}