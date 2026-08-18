function cancel(){
    document.getElementById('noakun').value='';
   
}

function simpan(){
    proses = document.getElementById('proses').value;
    noakun = document.getElementById('noakun').value
   
    
    param='proses='+proses+'&noakun='+noakun;
    tujuan='setup_slave_sumakun';
    post_response_text(tujuan+'.php', param, respon);
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                    cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddata(num){
    param='proses=loaddata&page='+num;
    tujuan='setup_slave_sumakun';
    post_response_text(tujuan+'.php', param, respon);
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(noakun){
    param='proses=delete&noakun='+noakun;
    tujuan='setup_slave_sumakun';
    if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan+'.php', param, respon);
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

