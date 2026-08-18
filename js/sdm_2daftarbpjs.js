function cancel(){
	closeDialog();
	document.getElementById('unit').value = '';
	document.getElementById('periode').value = '';
	document.getElementById('tipekaryawan').value = '';
	document.getElementById('jenis').value = '';
	document.getElementById('printContainer').innerHTML = '';
}

function preview(){
	
    unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    tipekaryawan=document.getElementById('tipekaryawan').value;
    jenis=document.getElementById('jenis').value;
	if(unit=='' || periode=='' || jenis==''){
		alert('Lengkapi pengisian');return;
	}
    param = 'method=preview';
    param += '&unit=' + unit+'&periode=' + periode+'&tipekaryawan=' + tipekaryawan+'&jenis=' + jenis;
	tujuan = 'sdm_2daftarbpjs_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('printContainer').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pdf(ev){
	unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    tipekaryawan=document.getElementById('tipekaryawan').value;
    jenis=document.getElementById('jenis').value;
    param = 'method=preview';
    param += '&unit=' + unit+'&periode=' + periode+'&tipekaryawan=' + tipekaryawan;
    param += '&jenis=' + jenis+'&tipe=pdf';
    tujuan = 'sdm_2daftarbpjs_slave.php';
    judul='Report PDF';        
    printFile(param,tujuan,judul,ev)	
}

function excel1(ev){
	unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    tipekaryawan=document.getElementById('tipekaryawan').value;
    jenis=document.getElementById('jenis').value;
    param = 'method=preview';
    param += '&unit=' + unit+'&periode=' + periode+'&tipekaryawan=' + tipekaryawan;
    param += '&jenis=' + jenis+'&tipe=excel';
    tujuan = 'sdm_2daftarbpjs_slave.php';
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev)	
}



