function gantiLokasitugas(tipe) {
	if(tipe=='kary'){
		lokasibaru = document.getElementById('tujuanbaru').value;		
		username = document.getElementById('username').value;
		namauser=document.getElementById('username').options[document.getElementById('username').selectedIndex].text;
		lokasiasal = document.getElementById('lokasiasal').value;
		param = 'username=' + username;
		param += '&lokasiasal=' + lokasiasal;
		param += '&namauser=' + namauser;
	}else{
		lokasibaru = document.getElementById('tjbaru').value;		
		param = "";
	}
	
	
    param += '&tjbaru=' + tjbaru + '&lokasibaru=' + lokasibaru;
    param += '&tipe=' + tipe; 
    tujuan = 'setup_slave_save_pindahLokasi.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alert(con.responseText);
					if(tipe=='ybs'){						
						parent.window.location = 'logout.php';
					}else{
						alert("User "+namauser+" silahkan login ulang.");
						document.getElementById('tujuanbaru').value='';		
						document.getElementById('username').value='';
						document.getElementById('lokasiasal').innerHTML='';
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getlokasiawal() {
    username = document.getElementById('username').value;
	namauser=document.getElementById('username').options[document.getElementById('username').selectedIndex].text;
    param = 'username=' + username;
	param+= '&method=getlokasiawal';
	param += '&namauser=' + namauser;
    tujuan = 'setup_slave_save_pindahLokasi.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('lokasiasal').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}