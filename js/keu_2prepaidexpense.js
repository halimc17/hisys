function getunit(pt){
    param='pt='+pt+'&proses=getunit';
    tujuan='keu_slave_2prepaidexpense.php';
    post_response_text(tujuan, param, respog);

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('unit').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}

function getLaporan(){
    pt      =document.getElementById('pt').value;
    unit    =document.getElementById('unit').value;
    tanggal=document.getElementById('tanggal').value;

    param='pt='+pt+'&unit='+unit+'&proses=preview'+'&tanggal='+tanggal;
    tujuan='keu_slave_2prepaidexpense.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
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

function getExcel(ev,tujuan){
    pt      =document.getElementById('pt').value;
    unit    =document.getElementById('unit').value;
    tanggal=document.getElementById('tanggal').value;
        
    judul='Report Ms.Excel';    
    param='pt='+pt+'&unit='+unit+'&proses=excel'+'&tanggal='+tanggal;
        
    printFile(param,tujuan,judul,ev)    
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);  
}