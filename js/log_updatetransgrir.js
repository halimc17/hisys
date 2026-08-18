function excel(){
	ev='event';
	kodeorg=document.getElementById('kodeorg').value;
	tahun  =document.getElementById('tahun').value;
	
	param = 'method=preview';
	param += '&kodeorg=' + kodeorg;
	param += '&tahun=' + tahun;
	param += '&jenis=excel';
	
	
    title='Excel';
    
	showDialog1(title,"<iframe frameborder=0 style='width:100%;min-height:400px'"+" src='log_slave_updatetransgrir.php?"+param+"'></iframe>",'900','400',ev);
	var dialog = document.getElementById('dynamic1');
}


function preview(jenis) {
	kodeorg=document.getElementById('kodeorg').value;
	tahun=document.getElementById('tahun').value;
	
	param = 'method=preview';
	param += '&kodeorg=' + kodeorg;
	param += '&tahun=' + tahun;
	param += '&jenis=html';
	
	tujuan = 'log_slave_updatetransgrir.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('output').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}