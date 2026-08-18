function preview(){
    tanggal1=trim(document.getElementById('tanggal1').value);
    tanggal2=trim(document.getElementById('tanggal2').value);
    kodept=trim(document.getElementById('kodept').value);
    kodebarang=trim(document.getElementById('kodebarang').value);
	tipe='html';
	param='method=preview'+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&tipe='+tipe;
	param += '&kodept=' + kodept + '&kodebarang=' + kodebarang;
	
	tujuan='pmn_2rekapkontrak_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {	
					document.getElementById('printContainer').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function excel(){
	
	tanggal1=trim(document.getElementById('tanggal1').value);
    tanggal2=trim(document.getElementById('tanggal2').value);
    kodept=trim(document.getElementById('kodept').value);
    kodebarang=trim(document.getElementById('kodebarang').value);
	tipe='excel';
	ev='event';
	param='method=preview'+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&tipe='+tipe;
	param += '&kodept=' + kodept + '&kodebarang=' + kodebarang;
	tujuan='pmn_2rekapkontrak_slave.php';
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev);	
}