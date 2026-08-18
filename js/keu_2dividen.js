function getUnit(obj) {
        
                    
        pt=document.getElementById('pt').value; 
                    
        param='method=getUnit';
        param+='&pt='+pt;
        tujuan = 'keu_slave_2dividen.php';
            if(pt=='') {
                    unit.disabled = true;
            } else {
                    post_response_text(tujuan, param, respog);
            }
            function respog(){
                    if (con.readyState == 4) {
                            if (con.status == 200) {
                                    busy_off();
                                    if (!isSaveResponse(con.responseText)) {
                                                    alert(con.responseText);
                                    } else {
                                            var unit = document.getElementById('unit');
                                                    unit.innerHTML = con.responseText;
                                                    unit.disabled = false;
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
                    }
            }
        }

function preview()

{ 

   
    unit=document.getElementById('unit').value;  
    pt=document.getElementById('pt').value;  
    tipe=document.getElementById('tipe').value;  
    notransaksi=document.getElementById('notransaksi').value;  
   
    
   
    met=document.getElementById('method').value;
    
       if (unit == '') {
        alert('Lengkapi Unit');
        return;
        }
        param='unit='+unit+'&tipe='+tipe+'&notransaksi='+notransaksi+'&method='+met;
        tujuan='keu_slave_2dividen.php';
        post_response_text(tujuan, param, callback);  
        
    
    function callback()
    {
              if(con.readyState==4)
              {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                        }
                        else {
                            document.getElementById('container').innerHTML=con.responseText;
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }


}

function detaildividen(notransaksi,tipe){
   
    title="Detail Dividen : "+notransaksi;
    width='';
    height='';
    formListPP(title,width,height);
    param='notransaksi='+notransaksi+'&tipe='+tipe;
    tujuan='keu_slave_2dividen.php';
    post_response_text(tujuan, param, respog);

    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else {
                   
                    loaddetail(notransaksi);

                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}


function formListPP(title,wdth,heig){
    width='';
    height='';
    if(wdth!=''){
        width=wdth;
    }
    if(heig!=''){
        height=heig;
    }
    
    content="<div id=containerdetail></div>";
    ev='event';
    showDialog4(title,content,width,height,ev);
}

function loaddetail (notransaksi) 
{
    param='method=loaddetail';
    param+='&notransaksi='+notransaksi;
    tujuan='keu_slave_2dividen.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('containerdetail').innerHTML=con.responseText;

                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function excel(ev, tujuan) {

    unit = document.getElementById('unit').value;
    tipe = document.getElementById('tipe').value;
    notransaksi=document.getElementById('notransaksi').value;
    if (unit == '') {
        alert('Lengkapi Unit');
        return;
        }
    judul = 'Report Ms.Excel';
    param = 'notransaksi=' + notransaksi+ '&unit=' + unit+'&tipe='+tipe+'&method=excel' ;
    printFile(param, tujuan, judul, ev);

}

function printFile(param,tujuan,title,ev)
{ 
   tujuan=tujuan+"?"+param;  
    width='600';
    height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);  
}

function cancel()

{

    document.getElementById('pt').value='';
    document.getElementById('unit').value='';
    document.getElementById('tipe').value='';
    document.getElementById('notransaksi').value='';
    }