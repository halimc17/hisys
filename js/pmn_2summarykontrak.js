function cancel(){
	document.getElementById('kodebarang').value='';
	document.getElementById('tanggal1').value='';
	document.getElementById('tanggal2').value='';
	document.getElementById('container').innerHTML='';
}

function preview(tipe){
    kodebarang=document.getElementById('kodebarang').value;
    tanggal1=document.getElementById('tanggal1').value;
    tanggal2=document.getElementById('tanggal2').value;
	method='preview';
    param='kodebarang='+kodebarang+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&tipe='+tipe;
	param += '&method=' + method;
    tujuan='pmn_2summarykontrak_slave.php';
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
