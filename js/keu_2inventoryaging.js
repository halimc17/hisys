function getunit(pt){
    param='pt='+pt+'&proses=getunit';
    tujuan='keu_slave_2inventoryaging.php';
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

function getgudang(unit){
    param='unit='+unit+'&proses=getgudang';
    tujuan='keu_slave_2inventoryaging.php';
    post_response_text(tujuan, param, respog);

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('gudang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}

function getklbarang(){
    gudang=document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
    
    param='gudang='+gudang+'&proses=getklbarang';
    tujuan='keu_slave_2inventoryaging.php';
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
                    document.getElementById('klbarang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function getKodeSub(){
    gudang=document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
    klbarang=document.getElementById('klbarang').options[document.getElementById('klbarang').selectedIndex].value;
    
    param='gudang='+gudang+'&klbarang='+klbarang+'&proses=getKodeSub';
    tujuan='keu_slave_2inventoryaging.php';
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
                    document.getElementById('klsubbarang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function getkodebarang(){
    gudang=document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
    klsubbarang=document.getElementById('klsubbarang').options[document.getElementById('klsubbarang').selectedIndex].value;
    
    param='gudang='+gudang+'&klsubbarang='+klsubbarang+'&proses=getkodebarang';
    tujuan='keu_slave_2inventoryaging.php';
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
                    document.getElementById('kdbarang').innerHTML=con.responseText;
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
    gudang  =document.getElementById('gudang').value;
    klbarang=document.getElementById('klbarang').value;
    kdbarang=document.getElementById('kdbarang').value;
    klsubbarang=document.getElementById('klsubbarang').value;
    tanggal =document.getElementById('tanggal').value;

    param='pt='+pt+'&unit='+unit+'&gudang='+gudang+'&klbarang='+klbarang+'&kdbarang='+kdbarang+'&klsubbarang='+klsubbarang+'&proses=preview'+'&tanggal='+tanggal;
    tujuan='keu_slave_2inventoryaging.php';
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
    gudang  =document.getElementById('gudang').value;
    klbarang=document.getElementById('klbarang').value;
    kdbarang=document.getElementById('kdbarang').value;
    klsubbarang=document.getElementById('klsubbarang').value;
    tanggal =document.getElementById('tanggal').value;

        
    judul='Report Ms.Excel';    
    param='pt='+pt+'&unit='+unit+'&gudang='+gudang+'&klbarang='+klbarang+'&kdbarang='+kdbarang+'&klsubbarang='+klsubbarang+'&proses=excel'+'&tanggal='+tanggal;
        
    printFile(param,tujuan,judul,ev)    
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);  
}