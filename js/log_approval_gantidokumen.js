
function viewdetailgantidokumen(notransaksi){
	param  = '';
	param += '&notransaksi=' + notransaksi;
	param += '&method=viewdetail';
	
	tujuan = 'keu_slave_gantidokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					// alertify.popup('View Dokumen', "<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('35%','40%');
					
					// alertify.popup('View Dokumen', "<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}