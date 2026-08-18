function cancel(){
	document.getElementById('kodebarang').value='';
	document.getElementById('container').innerHTML='';
}


function deletedt(kodebarang,pasal){
	method='deletedt';
    param='kodebarang='+kodebarang+'&pasal='+pasal;
	param += '&method=' + method;
    tujuan='pmn_5kontrakpanjang_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					preview('html');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}


function preview(tipe){
    kodebarang=document.getElementById('kodebarang').value;
	method='preview';
    param='kodebarang='+kodebarang+'&tipe='+tipe;
	param += '&method=' + method;
    tujuan='pmn_5kontrakpanjang_slave.php';
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



function simpan(no){
    kodebarang=document.getElementById('kodebarang').value;
    pasal=document.getElementById('pasal'+no).value;
    keterangan=document.getElementById('keterangan'+no).value;
	method='simpan';
    param='kodebarang='+kodebarang+'&pasal='+pasal+'&keterangan='+keterangan;
	param += '&method=' + method;
    tujuan='pmn_5kontrakpanjang_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					preview('html');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function update(no){
    kodebarang=document.getElementById('kodebarang').value;
    pasal=document.getElementById('pasal'+no).value;
    keterangan=document.getElementById('keterangan'+no).value;
	method='update';
    param='kodebarang='+kodebarang+'&pasal='+pasal+'&keterangan='+keterangan;
	param += '&method=' + method;
    tujuan='pmn_5kontrakpanjang_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					preview('html');
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
