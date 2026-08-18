function zPreviewd(){
	tanggaldari=document.getElementById('tanggaldari').value;
	tanggalsampai=document.getElementById('tanggalsampai').value;
    param='tanggaldari='+tanggaldari+'&tanggalsampai='+tanggalsampai+'&proses=preview';
	tujuan='pmn_slave_2suratperintahpengiriman.php';
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
