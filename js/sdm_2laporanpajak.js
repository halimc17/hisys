var fileTarget;
fileTarget='sdm_slave_2laporanpajak.php';

function loadLaporan(){
    
    pt=document.getElementById('pt').value;
    periode=document.getElementById('periode').value;
    pph0=document.getElementById('pph0').value;
    tipekar=document.getElementById('tipekar').value;
  
  
    param = 'method=loadLaporan';
    param += '&pt=' + pt+'&periode=' + periode+'&pph0=' + pph0+'&tipekar=' + tipekar;
    tujuan = 'sdm_slave_2laporanpajak.php';
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

function excel(ev){
    pt=document.getElementById('pt').value;
    periode=document.getElementById('periode').value;
    param += '&pt=' + pt+'&periode=' + periode+'&tipe=excel';
    tujuan = 'sdm_slave_2laporanpajak.php';
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev)    
}


