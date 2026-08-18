function loadLaporan(){
    pt=document.getElementById('pt').value;
    periode=document.getElementById('periode').value;
    param = 'method=loadLaporan';
    param += '&pt=' + pt+'&periode=' + periode;
    tujuan = 'sdm_slave_2rekapgaji.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist1').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pdf(ev) {
    pt=document.getElementById('pt').value;
    periode=document.getElementById('periode').value;
    param += '&pt=' + pt+'&periode=' + periode+'&tipe=pdf';
    showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='sdm_slave_2rekapgaji.php?" + param + "'></iframe>", '800', '400', ev);
}


