function cancel(){
	document.getElementById('container').innerHTML='';
	document.getElementById('nodokumenlama').value='';
	document.getElementById('nodokumenbaru').value='';
	document.getElementById('notransaksi').value='';
	alertify.popup().destroy();
}

function getnodok(){
	ev='event';
	title='Pencarian';
	// content='<div id=formpencariannodok></div>';
	width='';
	height='';
	param='method=getnodok';
	tujuan = 'keu_prosesgantidokumen_slave.php';
	post_response_text(tujuan, param, respon);
	function respon(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					// document.getElementById('formpencariannodok').innerHTML=con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('90%','90%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}



function findnodok(){
	notransaksi = trim(document.getElementById('notransaksisch').value);
	nodokumenlama = trim(document.getElementById('nodokumenlamasch').value);
	nodokumenbarusch = trim(document.getElementById('nodokumenbarusch').value);
	param = 'method=findnodok';
	param += '&notransaksi=' + notransaksi+'&nodokumenlamasch=' + nodokumenlamasch+'&nodokumenbarusch=' + nodokumenbarusch;
	tujuan = 'keu_prosesgantidokumen_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('formfindnodok').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function movefindnodok(notransaksi,nodokumenlama,nodokumenbaru){
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('nodokumenlama').value=nodokumenlama;
	document.getElementById('nodokumenbaru').value=nodokumenbaru;
	alertify.popup().destroy();
}

function preview(){
    nodokumenlama=trim(document.getElementById('nodokumenlama').value);
    nodokumenbaru=trim(document.getElementById('nodokumenbaru').value);
	notransaksi=trim(document.getElementById('notransaksi').value);
	method='preview';
    param='nodokumenlama='+nodokumenlama+'&nodokumenbaru='+nodokumenbaru+'&notransaksi='+notransaksi;
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
    notransaksi=document.getElementById('notransaksi').value;
	method='saveht';
	strparam='';
    param='nodokumenlama='+nodokumenlama+'&nodokumenbaru='+nodokumenbaru+'&notransaksi='+notransaksi+'&maxrow='+maxrow;
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
