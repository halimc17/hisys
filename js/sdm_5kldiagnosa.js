// JavaScript Document
function cancel(){
	document.getElementById('kodekelompok').value='';
	document.getElementById('kodekelompok').disabled=false;
	document.getElementById('deskripsi').value='';
	document.getElementById('method').value='insert';
}

function loadData(){
	param='method=loadData';
	tujuan='sdm_slave_5kldiagnosa';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(kodekelompok,deskripsi){
	document.getElementById('kodekelompok').value=kodekelompok;
	document.getElementById('kodekelompok').disabled=true;
	document.getElementById('deskripsi').value=deskripsi;
	document.getElementById('method').value='update';
}

function deleteData(kodekelompok){
	param='kodekelompok='+kodekelompok+'&method=delete';
	tujuan='sdm_slave_5kldiagnosa.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadData();
					cancel();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function save(){
	kodekelompok=trim(document.getElementById('kodekelompok').value);
	deskripsi=trim(document.getElementById('deskripsi').value);
	method=trim(document.getElementById('method').value);
	
	param='kodekelompok='+kodekelompok+'&deskripsi='+deskripsi+'&method='+method;
	tujuan='sdm_slave_5kldiagnosa.php';
	post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('container').innerHTML=con.responseText;
					cancel();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 
}