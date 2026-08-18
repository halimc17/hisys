function zPreviewd(){
	tanggaldari=document.getElementById('tanggaldari').value;
	tanggalsampai=document.getElementById('tanggalsampai').value;
    unit=document.getElementById('unit').value;
    param='tanggaldari='+tanggaldari+'&tanggalsampai='+tanggalsampai+'&unit='+unit+'&proses=preview';
	tujuan='pmn_slave_2stokharian.php';
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


function pdf(ev) {
    tanggaldari=document.getElementById('tanggaldari').value;
    tanggalsampai=document.getElementById('tanggalsampai').value;
    unit=document.getElementById('unit').value;
    param='tanggaldari='+tanggaldari+'&tanggalsampai='+tanggalsampai+'&unit='+unit+'&proses=pdf';
    showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
        " src='pmn_slave_2stokharian.php?" + param + "'></iframe>", '800', '400', ev);
}
