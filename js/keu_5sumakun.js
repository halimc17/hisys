function cancel(){
    document.getElementById('noakun').value='';
   
}

function batalx(){
    document.getElementById('noakunf').value='';
    document.getElementById('namaakunf').value='';
    loaddata(0);
   
}

function simpan(){
    proses = document.getElementById('proses').value;
    noakun = document.getElementById('noakun').value
   
    
    param='proses='+proses+'&noakun='+noakun;
    tujuan='keu_slave_5sumakun';
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

function cari(){
    noakun = document.getElementById('noakunf').value;
    namaakun = document.getElementById('namaakunf').value;
    param='proses=loaddata&page=0&noakun='+noakun+'&namaakun='+namaakun;
    tujuan='keu_slave_5sumakun';
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

function loaddata(num){
    noakun = document.getElementById('noakunf').value;
    namaakun = document.getElementById('namaakunf').value;
    param='proses=loaddata&page='+num+'&noakun='+noakun+'&namaakun='+namaakun;
    tujuan='keu_slave_5sumakun';
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
    tujuan='keu_slave_5sumakun';
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

