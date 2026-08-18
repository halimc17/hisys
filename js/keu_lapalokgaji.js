function getunit(e) {
	param  = 'proses=getunit';
	param += '&kdpt=' + getValue('kdpt');
	
	tujuan = 'keu_lapalokgaji_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('kdorg').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function preview(tipe) {
	param  = 'proses='+tipe;
	param += '&kdorg=' + getValue('kdorg');
	param += '&periode=' + getValue('prd');
	param += '&periodesd=' + getValue('prdsd');
	// param += '&kdpt=' + getValue('kdpt');
	// param += '&digit=' + getValue('digit');
	
	tujuan = 'keu_lapalokgaji_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='excel'){
						e=tujuan+'?'+ param;
						printnopopup(e);
					}else{						
						document.getElementById('printContainer').innerHTML = con.responseText;
						leftFixedTable();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showbaris(name){
	namep = name.replace(" ",".");
	var p = $(".rowcontent."+namep);
	if(p[0].style.display=='none'){		
		var e = $("[class='rowcontent "+name+"']");			
		for (i=0; i<e.length; i++){
			e[i].style.display='';
		}
	}else{
		var e = $(".rowcontent."+namep);
		for (i=0; i<e.length; i++){
			e[i].style.display='none';
		}
	}
}
