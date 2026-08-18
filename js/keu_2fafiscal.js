function getUnit(){
    pt=document.getElementById('pt').value;
    param='method=getunit'+'&pt='+pt;
    tujuan = 'keu_slave_2fafiscal.php';

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

function getPeriode(){
    unit=document.getElementById('unit').value;
    param='method=getperiode'+'&unit='+unit;
    tujuan='keu_slave_2fafiscal.php';
    post_response_text(tujuan, param, respog);          
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('periode').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}

function getsubtpasset(){
    tpAsset=document.getElementById('tpAsset').value; 
    param='method=getsubtpasset'+'&tpAsset='+tpAsset;
    tujuan='keu_slave_2fafiscal.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('subtpAsset').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }   
    } 
}

function getPreview(){
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    tpAsset=document.getElementById('tpAsset').value;
    subtpAsset=document.getElementById('subtpAsset').value;
    param='method=getlaporan'+'&proses=preview'+'&pt='+pt+'&unit='+unit+'&periode='+periode+'&tpAsset='+tpAsset+'&subtpAsset='+subtpAsset;
    tujuan = 'keu_slave_2fafiscal.php';
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
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    tpAsset=document.getElementById('tpAsset').value;
    subtpAsset=document.getElementById('subtpAsset').value;       
    judul='Report Ms.Excel';    
    param='method=getlaporan'+'&proses=excel'+'&pt='+pt+'&unit='+unit+'&periode='+periode+'&tpAsset='+tpAsset+'&subtpAsset='+subtpAsset;  
    printFile(param,tujuan,judul,ev)    
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);  
}