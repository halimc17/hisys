function getData(){
    getdt=document.getElementById('kdnilaidr');
    getdt=getdt.options[getdt.selectedIndex].value;
    if(getdt==''){
        document.getElementById('kdnilai').disabled=false;
        document.getElementById('nama').disabled=false; 
    }else{
        document.getElementById('kdnilai').value='';
        document.getElementById('nama').value='';
        document.getElementById('kdnilai').disabled=true;
        document.getElementById('nama').disabled=true;
    }
}

function simpan(){
    kdnilaidr=document.getElementById('kdnilaidr');
    kdnilaidr=kdnilaidr.options[kdnilaidr.selectedIndex].value;
    stationId=document.getElementById('stationId');
    stationId=stationId.options[stationId.selectedIndex].value;
    kdnilai=document.getElementById('kdnilai').value;
    nama=document.getElementById('nama').value;
    standarnilai=document.getElementById('standarnilai').value;
    method=document.getElementById('method').value;
    param='kdnilaidr='+kdnilaidr+'&kdnilai='+kdnilai+'&method='+method+'&stationId='+stationId+'&nama='+nama+'&standarnilai='+standarnilai;
    tujuan='pabrik_slave_5mr_parameter_nilai.php';
    post_response_text(tujuan, param, respog);      
     
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                    cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }   
}

function cancel(){
    document.getElementById('stationId').disabled=false;
    document.getElementById('kdnilai').disabled=false;
    document.getElementById('nama').disabled=false;
    document.getElementById('kdnilaidr').disabled=false;
    document.getElementById('kdnilai').value='';
    document.getElementById('nama').value='';
    document.getElementById('standarnilai').value='';
    document.getElementById("kdnilaidr").selectedIndex = "0";
    document.getElementById("stationId").selectedIndex = "0";
    document.getElementById("kdBrg").selectedIndex = "0";
    document.getElementById('method').value='insert';       
}

function loadData(num){

    param='method=loadData';
    param+='&page='+num;

    tujuan='pabrik_slave_5mr_parameter_nilai.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    //alert(con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
                    isdt = con.responseText.split("####");
                    document.getElementById('container').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
                busy_off();
          error_catch(con.status);
            }
        }   
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);  
}

function fillfield(stationId,kdnilai,nama,standarnilai){
    document.getElementById('kdnilai').value=kdnilai;
    document.getElementById('kdnilai').disabled=true;
    document.getElementById('nama').value=nama;
    document.getElementById('nama').disabled=true;
    document.getElementById('standarnilai').value=standarnilai;
    l=document.getElementById('kdnilaidr');
    for(a=0;a<l.length;a++){
        if(l.options[a].value==kdnilai)
            {
                l.options[a].selected=true;
            }
    }
    document.getElementById('kdnilaidr').disabled=true;
    l2=document.getElementById('stationId');
    for(a=0;a<l2.length;a++){
        if(l2.options[a].value==stationId)
            {
                l2.options[a].selected=true;
            }
    }
    document.getElementById('stationId').disabled=true;
    document.getElementById('method').value='update';
}