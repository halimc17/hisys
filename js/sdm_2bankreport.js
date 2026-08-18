var fileTarget;
fileTarget='sdm_slave_2bankreport.php';

function getunit(){
    pt=document.getElementById('pt').value;
    param='pt='+pt;

    function respon(){
        if(con.readyState == 4){
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                      document.getElementById('unit').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'?method=getunit', param, respon);
}

function getkar(){
    pt=document.getElementById('pt').value;
    tipekar=document.getElementById('tipekar').value;
    param='pt='+pt+'&tipekar='+tipekar;

    function respon(){
        if(con.readyState == 4){
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    data = con.responseText.split("##");
                      document.getElementById('karyawan').innerHTML=data[0];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'?method=getkar', param, respon);
}

function loadLaporan(){
    
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    karyawan=document.getElementById('karyawan').value;
    tipekar=document.getElementById('tipekar').value;
  
    param = 'method=loadLaporan';
    param += '&pt=' + pt+'&unit=' + unit+'&periode=' + periode+'&karyawan=' + karyawan+'&tipekar=' + tipekar;
    tujuan = fileTarget;
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
    unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    karyawan=document.getElementById('karyawan').value;
    tanggal=document.getElementById('tanggal').value;
    sumberrek=document.getElementById('sumberrek').value;
    param += '&pt=' + pt+'&unit=' + unit+'&periode=' + periode+'&karyawan=' + karyawan+'&tanggal=' + tanggal+'&sumberrek=' + sumberrek+'&tipe=excel';
    tujuan = fileTarget;
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev)    
}


