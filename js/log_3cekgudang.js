function prosesCek(idcont) {
    unit=document.getElementById('unitId');
    unit=unit.options[unit.selectedIndex].value;
    periode=document.getElementById('periodeId');
    periode=periode.options[periode.selectedIndex].value;
    param='unitId='+unit+'&proses=cekAwal'+'&periodeId='+periode;
    post_response_text('log_slave_3cekgudang.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // === Success Response
                    document.getElementById(idcont).innerHTML=con.responseText;
                 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function showDetail(title,ev){
    content="<fieldset><legend>"+title+"</legend><div id=contDetail ></div></fieldset>";
    width='';
    height='';
    showDialog1(title,content,width,height,ev); 
}
function updateData(noakun,prd,unit,ev){
    title="Data "+noakun;
    //showDetail(title,ev);
    param='noakun='+noakun+'&proses=cekAwal2'+'&periodeId='+prd+'&unitId='+unit;
    tujuan='log_slave_3cekgudang.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
                if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                        alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
						// document.getElementById('contDetail').innerHTML=con.responseText;
                    }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
        }	
     }
}