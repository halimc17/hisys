function zPreviewd(){
	periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	komoditi=document.getElementById('komoditi').options[document.getElementById('komoditi').selectedIndex].value;
	
    param='periode='+periode+'&komoditi='+komoditi+'&proses=preview';
	tujuan='pmn_slave_2rekapdo.php';
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
