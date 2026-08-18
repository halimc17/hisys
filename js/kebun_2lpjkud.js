function priview(){
	periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	namakud=document.getElementById('namakud').options[document.getElementById('namakud').selectedIndex].value;
    param='periode='+periode+'&namakud='+namakud+'&proses=preview';
	tujuan='kebun_slave_2lpjkud.php';
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
