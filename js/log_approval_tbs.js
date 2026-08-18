function pdftbs(notransaksi,tujuan) {
	param = 'method=pdf' + '&notransaksi=' + notransaksi;
	// tujuan='kebun_tbskud_slave.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog1(title, content, width, height, 'event');
}

function pdftbs2(notransaksi,tujuan) {
	param = 'method=pdf2' + '&notransaksi=' + notransaksi;
	// tujuan='kebun_tbskud_slave.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}


function pdftimbangan(notransaksi,table,tujuan) {
	param = 'method=pdftimbangan' + '&notransaksi=' + notransaksi+ '&table=' + table;
	// tujuan='kebun_tbskud_slave.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}


function formajukanhargajualtbs(notransaksi,page){
	method = 'formajukan';
	param='';
	param += '&notransaksi=' + notransaksi + '&page=' + page;
	param += '&method=' + method;
	tujuan = 'pmn_hargajualtbs_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi',con.responseText);
                } else {
				   alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
} 
 