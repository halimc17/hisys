function cancel(){
	document.getElementById('unit').value='';
	document.getElementById('periode').value='';
	document.getElementById('container').innerHTML='';
}

function preview(tipe){
    unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
	method='preview';
    param='periode='+periode+'&unit='+unit+'&tipe='+tipe;
	param += '&method=' + method;
    tujuan='bulking_2monthlyreport_slave.php';
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}
	
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					if(tipe=='html'){
						document.getElementById('container').innerHTML=con.responseText;
						leftFixedTable();
					}
					// leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}


function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
