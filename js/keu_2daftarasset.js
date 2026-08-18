
function getunit(){
    kdpt=document.getElementById('kdpt').value;
    param = 'method=getunit';
    param += '&kdpt=' + kdpt;
    tujuan = 'keu_2daftarasset_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('kdunit').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function getsubtipeasset(){
    kdtipeasset=document.getElementById('kdtipeasset').value;
    param = 'method=getsubtipeasset';
    param += '&kdtipeasset=' + kdtipeasset;
    tujuan = 'keu_2daftarasset_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('kdsubtipeasset').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}













function cancel(){
	closeDialog();
	document.getElementById('kdunit').value = '';
	document.getElementById('kdpt').value = '';
	document.getElementById('kdperiode').value = '';
	document.getElementById('kdtipeasset').value = '';
	document.getElementById('kdsubtipeasset').value = '';
	document.getElementById('printContainer').innerHTML = '';
}



function preview(){
	
    kdunit=document.getElementById('kdunit').value;
    kdpt=document.getElementById('kdpt').value;
    kdperiode=document.getElementById('kdperiode').value;
    kdtipeasset=trim(document.getElementById('kdtipeasset').value);
    kdsubtipeasset=document.getElementById('kdsubtipeasset').value;
	
	if(kdpt=='' || kdperiode==''){
		alert('Lengkapi pengisian');return;
	}	
	
    param = 'method=preview';
    param += '&kdunit=' + kdunit+'&kdperiode=' + kdperiode+'&kdtipeasset=' + kdtipeasset+'&kdpt=' + kdpt;
    param += '&kdsubtipeasset=' + kdsubtipeasset+'&tipe=html';
    tujuan = 'keu_2daftarasset_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('printContainer').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pdf(ev){
    kdunit=document.getElementById('kdunit').value;
    kdpt=document.getElementById('kdpt').value;
    kdperiode=document.getElementById('kdperiode').value;
    kdtipeasset=trim(document.getElementById('kdtipeasset').value);
    kdsubtipeasset=document.getElementById('kdsubtipeasset').value;
    param = 'method=preview';
    param += '&kdunit=' + kdunit+'&kdperiode=' + kdperiode+'&kdtipeasset=' + kdtipeasset+'&kdpt=' + kdpt;
    param += '&kdsubtipeasset=' + kdsubtipeasset+'&tipe=pdf';
    tujuan = 'keu_2daftarasset_slave.php';
    judul='Report PDF';        
    printFile(param,tujuan,judul,ev);	
}



function excel(ev){
    kdunit=document.getElementById('kdunit').value;
    kdpt=document.getElementById('kdpt').value;
    kdperiode=document.getElementById('kdperiode').value;
    kdtipeasset=trim(document.getElementById('kdtipeasset').value);
    kdsubtipeasset=document.getElementById('kdsubtipeasset').value;
    param = 'method=preview';
    param += '&kdunit=' + kdunit+'&kdperiode=' + kdperiode+'&kdtipeasset=' + kdtipeasset+'&kdpt=' + kdpt;
    param += '&kdsubtipeasset=' + kdsubtipeasset+'&tipe=excel';
    tujuan = 'keu_2daftarasset_slave.php';
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev);
}

 
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
