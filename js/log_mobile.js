function loadData(page, type) {
	karyawanCari 	= document.getElementById('karyawanCari').value;
	dariTanggal 	= document.getElementById('tgl_dari').value;
	sampaiTanggal 	= document.getElementById('tgl_sampai').value;
	if (dariTanggal != '' && sampaiTanggal != '') {
		tanggalValidasi(dariTanggal, sampaiTanggal);
	}

	param 			= 'method=loaddata' + '&pages=' + page + '&dariTanggal=' + dariTanggal + '&sampaiTanggal=' + sampaiTanggal + '&karyawanCari=' + karyawanCari + '&type=' + type;
	console.log(param);
	tujuan 			= 'log_slave_mobile.php';
	if (type == 'html') {
		post_response_text(tujuan, param, response);
		function response() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						console.log(con.responseText);
						data = con.responseText.split('####');
						document.getElementById('listData').innerHTML = data[0];
						document.getElementById('footerData').innerHTML = data[1];
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	} else if (type == 'pdf') {
		title 	= 'Report PDF';
        tujuan	= tujuan+"?"+param;  
		width 	= 1024;
		height 	= 400;
		content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>";
		showDialog1(title, content, width, height, 'event');
	} else if (type == 'excel') {
		title	= 'Report Ms.Excel';
        tujuan	= tujuan+"?"+param;  
		width	= '200';
		height	= '50';
		content	= "<iframe frameborder=0 src='"+tujuan+"'></iframe>";
		showDialog1(title, content, width, height, 'event'); 
	}
}

function batal(){
	document.getElementById('karyawanCari').value = '';
	document.getElementById('tgl_dari').value = '';
	document.getElementById('tgl_sampai').value = '';
	loadData(0, 'html');
}

function tanggalValidasi(dari, sampai){
	if (sampai < dari) {
		alert('Tanggal Dari Tidak Bisa Melebihi Tanggal Sampai');
		document.getElementById('tgl_dari').value = '';
		document.getElementById('tgl_sampai').value = '';
		loadData(0, 'html');
	}
}



