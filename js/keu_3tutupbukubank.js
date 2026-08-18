function getperiode(kodeorg) {
	param = 'method=getperiode' + '&kodeorg=' + kodeorg;
	tujuan = 'keu_slave_3tutupbukubank.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('periode').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview(tampil) {
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	
	param = 'method=preview' + '&kodeorg=' + kodeorg + '&periode=' + periode+ '&tampil=' + tampil;
	tujuan = 'keu_slave_3tutupbukubank.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					if(tampil=='simpan'){
						document.getElementById('kodeorg').value='';
						document.getElementById('periode').value='';
						document.getElementById('container').innerHTML='';
						alertify.alert("Informasi",'Done');
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
