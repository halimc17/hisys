// JavaScript Document

var fileTarget;
fileTarget='sdm_slave_5dayoff.php';

function savehk(fileTarget,passParam) 
{
    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    param+="&method=insert";
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } 
                else 
                {
                    loadData();
                    cancelIsi();
                    alert('Done.');
                }
            } 
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);
}

function deletehk(periode,karyawan)
{
    param='periode='+periode+'&karyawan='+karyawan+'&method=delete';
  
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } 
                else 
                {
                    loadData();
                    cancelIsi();
                }
            } 
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if(confirm('Hapus data periode '+periode+'?'))post_response_text(fileTarget, param, respon);
}

function loadData()
{
    param='method=loadData';
    tujuan='sdm_slave_5dayoff';
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

function cancelIsi()
{
    document.getElementById('periode').value='';
    document.getElementById('pt').value='';
    document.getElementById('unit').value='';
    document.getElementById('karyawan').value='';
    document.getElementById('jumlah').value='';
}

function DaysInMonth(y,m)
{
    return new Date(y,m,0).getDate();
}

function resetcontainer(){
    return;
}


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
    unit=document.getElementById('unit').value;
    param='unit='+unit;

    function respon(){
        if(con.readyState == 4){
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                      document.getElementById('karyawan').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'?method=getkar', param, respon);
}