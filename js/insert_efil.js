function hapus(){
	document.getElementById('notransaksi').value='';
}


function simpan(){
    notransaksi=trim(document.getElementById('notransaksi').value);
	tipe=document.getElementById('tipe').options[document.getElementById('tipe').selectedIndex].value
    if(notransaksi==''){
		alert("Please complete form.");
		return false;
	}
	
	if(tipe=='k'){
		param='method=insertefill&notransaksi='+notransaksi;
		tujuan='keu_slave_efill.php';
	}
	
	if(tipe=='t'){
		param='method=insertefill&noinvoice='+notransaksi;
		tujuan='log_slave_efill.php';
	}
	
	if(tipe=='p'){
		param='method=insertefilltgh&noinvoice='+notransaksi;
		tujuan='log_slave_efill.php';
	}
	
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Done");
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}