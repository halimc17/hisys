function getperiode(){
    unit=trim(document.getElementById('unit').value);
  
    param='unit='+unit+'&method=getperiode';
    tujuan='pajak_slave_vatinvatout.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data = con.responseText.split("####");
                    // document.getElementById('periode').innerHTML=data[0];
                    document.getElementById('npwp').innerHTML=data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}






function preview(){
	
	flag=document.getElementById('flag').value;
	unit=document.getElementById('unit').value;
    npwp=document.getElementById('npwp').value;
    tipe=trim(document.getElementById('tipe').value);
    tanggal1=trim(document.getElementById('tanggal1').value);
    tanggal2=trim(document.getElementById('tanggal2').value);
	tipelaporan='html';
	param='method=preview'+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&tipelaporan='+tipelaporan;
	param += '&unit=' + unit + '&npwp=' + npwp+ '&tipe=' + tipe+ '&flag=' + flag;
	tujuan='pajak_2vatinvatout_slave.php';
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
	tujuan='pajak_2vatinvatout_slave.php';
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev);	
}