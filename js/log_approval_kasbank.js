
function viewdetailkasbank(notransaksi,page){
	content= "<div id=formpost  style=\"height:100%;width:800px;\"></div>";
	title='Ajukan Persetujuan';
	height='';
	width='800';
	// showDialog4(title,content,width,height,'event');	
	formdetailkasbank(notransaksi,page);
} 

function formdetailkasbank(notransaksi,page){
	method = 'formajukan';
	param='';
	param += '&notransaksi=' + notransaksi + '&page=' + page;
	param += '&method=' + method;
	tujuan = 'keu_kasdanbank_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi',con.responseText);
                } else {
                   // document.getElementById('formpost').innerHTML=con.responseText;
				   //alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','80%'); 
				   alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
				   // loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
} 
 
 
 
 
function showhidehistorykasbank() {
	var row = document.getElementById('forminfohistorykasbank');
	if (row !== null) {
		if (row.style.display == '') {
			row.style.display = 'none';
		} else {
			row.style.display = '';
		}
	}
}

