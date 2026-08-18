function cancel(){
	document.getElementById('container').innerHTML='';
	document.getElementById('nodokumenlama').value='';
	document.getElementById('nodokumenbaru').value='';
	alertify.popup().destroy();
}



function preview(){
    nodokumenlama=trim(document.getElementById('nodokumenlama').value);
    nodokumenbaru=trim(document.getElementById('nodokumenbaru').value);
	method='preview';
    param='nodokumenlama='+nodokumenlama+'&nodokumenbaru='+nodokumenbaru;
	param += '&method=' + method;
    tujuan='keu_prosesgantidokumen_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('container').innerHTML=con.responseText;
					leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}


function saveht(maxrow){
    nodokumenlama=document.getElementById('nodokumenlama').value;
    nodokumenbaru=document.getElementById('nodokumenbaru').value;
	method='saveht';
	strparam='';
    param='nodokumenlama='+nodokumenlama+'&nodokumenbaru='+nodokumenbaru+'&maxrow='+maxrow;
	param += '&method=' + method;
	for (i = 1; i <= maxrow; i++) {
		strparam += '&sumber['+i+']='+trim(document.getElementById('sumber'+i).innerHTML);
		strparam += '&notransaksidokumen['+i+']='+trim(document.getElementById('notransaksidokumen'+i).innerHTML);
		strparam += '&keteranganlama['+i+']='+trim(document.getElementById('keteranganlama'+i).innerHTML);
		strparam += '&keteranganbaru['+i+']='+trim(document.getElementById('keteranganbaru'+i).innerHTML);
		strparam += '&tanggal['+i+']='+trim(document.getElementById('tanggal'+i).innerHTML);
	}
	param+=strparam;
	
    tujuan='keu_prosesgantidokumen_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alert('Done');
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
