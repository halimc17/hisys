function loaddata() {
	param = 'method=loaddata';
    tujuan = 'ubahpassword_slave.php';
    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
                }else{
					document.getElementById('output').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
	username=trim(document.getElementById('username').innerHTML);
	passwordlama=trim(document.getElementById('passwordlama').value);
	passwordbaru=trim(document.getElementById('passwordbaru').value);
	passwordbaruverifikasi=trim(document.getElementById('passwordbaruverifikasi').value);
	
	validate([
        ["passwordlama","Password lama harus diisi."],
        ["passwordbaru","Password baru harus diisi."],
        ["passwordbaruverifikasi","Ulangi password harus diisi."]
	]);
	
	param  = '';
	param += '&username=' + username;
	param += '&passwordlama=' + passwordlama;
	param += '&passwordbaru=' + passwordbaru;
	param += '&passwordbaruverifikasi=' + passwordbaruverifikasi;
	param += '&method=simpan';
	
	tujuan = 'ubahpassword_slave.php';
	
	if(passwordbaru==passwordbaruverifikasi){
		if(passwordbaru.length > 5){
			alertify.confirm("Anda yakin rubah password???",
				function(){
					post_response_text(tujuan, param, respog);
				},
				function(){
					return;
				}
			);
		}else{
			alertify.alert("Peringatan","Jumlah karakter password minimal 6.");
		}
	}else{
		alertify.alert("Peringatan","Password baru dan Ulangi Password baru tidak sama.");
	}
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert("Info","Berhasil Rubah Password, Silahkan login ulang.");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}