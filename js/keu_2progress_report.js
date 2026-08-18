function preview()
{
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    tgl=document.getElementById('tgl').value;
    tgl1=document.getElementById('tgl1').value;
    stts=document.getElementById('stts').value;
    param='proses=preview'+'&pt='+pt+'&unit='+unit+'&tgl='+tgl+'&tgl1='+tgl1+'&stts='+stts;
    tujuan='keu_slave_2progress_report.php';
    post_response_text(tujuan, param, respog);      
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('printContainer').innerHTML=con.responseText;                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }           
}


function getUnit(pt)
{ 
        pt=document.getElementById('pt').value;
        param='pt='+pt;
        tujuan='keu_slave_getUnit.php';
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

function datakeexcel(ev,tujuan)
{
        pt=document.getElementById('pt').value;
        unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
        tgl=document.getElementById('tgl').value;
        tgl1=document.getElementById('tgl1').value;
        stts=document.getElementById('stts').value;

        judul='Report Ms.Excel';  
       
        param='proses=excel'+'&pt='+pt+'&unit='+unit+'&tgl='+tgl+'&tgl1='+tgl1+'&stts='+stts;
       
       
        printFile(param,tujuan,judul,ev)  
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);  
}