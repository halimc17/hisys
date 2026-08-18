function zPreviewd(){
	periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	lokasitugas=document.getElementById('lokasitugas').options[document.getElementById('lokasitugas').selectedIndex].value;
	namakaryawan=document.getElementById('namakaryawan').value;
    param='periode='+periode+'&lokasitugas='+lokasitugas+'&namakaryawan='+namakaryawan+'&proses=preview';
	tujuan='sdm_slave_2rekapperjalanandinas.php';
	post_response_text(tujuan, param, respon);
	
	function respon() {
        if (con.readyState == 4) {
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
